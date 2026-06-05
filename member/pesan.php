<?php
require_once '../includes/auth-check.php';
require_once '../config/database.php';
require_once '../config/functions.php';

$id_member = $_SESSION['id_user'];
$sukses    = false;
$pesanan_baru = [];
$errors    = [];

// ── Query layanan aktif dari DB ─────────────────────────────
$stmtLayanan = $pdo->query("SELECT * FROM layanan WHERE status = 'aktif' ORDER BY tarif_per_kg ASC");
$layanan_list = $stmtLayanan->fetchAll() ?: [];

// ── Query kecamatan dari info_website ───────────────────────
$stmtInfo = $pdo->query("SELECT kecamatan_dilayani, no_whatsapp FROM info_website LIMIT 1");
$info = $stmtInfo->fetch();
$kecamatan_list = [];
if ($info && !empty($info['kecamatan_dilayani'])) {
    $kecamatan_list = json_decode($info['kecamatan_dilayani'], true) ?: [];
}
$no_whatsapp_admin = $info['no_whatsapp'] ?? '';

// ── Handler POST ────────────────────────────────────────────
if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $id_layanan         = (int) ($_POST['layanan_id'] ?? 0);
    $opsi_pengantaran   = bersihkan($_POST['opsi_pengantaran'] ?? '');
    $kecamatan          = bersihkan($_POST['kecamatan'] ?? '');
    $alamat_pengantaran = bersihkan($_POST['alamat_pengantaran'] ?? '');
    $estimasi_berat     = isset($_POST['estimasi_berat']) && $_POST['estimasi_berat'] !== ''
                          ? (float) $_POST['estimasi_berat']
                          : null;
    $catatan_khusus     = bersihkan($_POST['catatan'] ?? '');

    // ── Validasi server-side ─────────────────────────────────
    $layananValid    = false;
    $layananTerpilih = null;
    foreach ($layanan_list as $l) {
        if ((int) $l['id'] === $id_layanan) {
            $layananValid    = true;
            $layananTerpilih = $l;
            break;
        }
    }
    if (!$layananValid) {
        $errors[] = 'Layanan tidak valid. Silakan pilih layanan yang tersedia.';
    }

    if (!in_array($opsi_pengantaran, ['ambil_sendiri', 'kurir'])) {
        $errors[] = 'Opsi pengantaran tidak valid.';
    }

    if ($opsi_pengantaran === 'kurir') {
        if (empty($kecamatan)) {
            $errors[] = 'Kecamatan wajib diisi untuk layanan kurir.';
        }
        if (empty($alamat_pengantaran)) {
            $errors[] = 'Alamat lengkap wajib diisi untuk layanan kurir.';
        }
    }

    // ── Proses jika tidak ada error ──────────────────────────
    if (empty($errors)) {

        $biaya_kurir  = ($opsi_pengantaran === 'kurir') ? 10000 : 0;
        $kode_pesanan = generateKodePesanan($pdo);

        $stmtInsert = $pdo->prepare("
            INSERT INTO pesanan (
                kode_pesanan, id_member, id_layanan,
                opsi_pengantaran, alamat_pengantaran, kecamatan,
                estimasi_berat, catatan_khusus, biaya_kurir,
                status_pesanan, status_pembayaran,
                berat_aktual, total_harga,
                sudah_dilihat_member, created_at, updated_at
            ) VALUES (
                ?, ?, ?,
                ?, ?, ?,
                ?, ?, ?,
                'menunggu_konfirmasi', 'belum_bayar',
                0.00, 0.00,
                0, NOW(), NOW()
            )
        ");
        $stmtInsert->execute([
            $kode_pesanan,
            $id_member,
            $id_layanan,
            $opsi_pengantaran,
            $opsi_pengantaran === 'kurir' ? $alamat_pengantaran : null,
            $opsi_pengantaran === 'kurir' ? $kecamatan : null,
            $estimasi_berat,
            !empty($catatan_khusus) ? $catatan_khusus : null,
            $biaya_kurir,
        ]);

        $id_pesanan_baru = $pdo->lastInsertId();

        $stmtRiwayat = $pdo->prepare("
            INSERT INTO riwayat_status (
                id_pesanan, status_lama, status_baru,
                dilakukan_oleh, changed_at
            ) VALUES (?, NULL, 'menunggu_konfirmasi', 'member', NOW())
        ");
        $stmtRiwayat->execute([$id_pesanan_baru]);

        $estimasi_biaya = null;
        if ($estimasi_berat !== null && $layananTerpilih) {
            $estimasi_biaya = ($estimasi_berat * $layananTerpilih['tarif_per_kg']) + $biaya_kurir;
        }

        $pesanan_baru = [
            'kode_pesanan'     => $kode_pesanan,
            'nama_layanan'     => $layananTerpilih['nama_layanan'],
            'opsi_pengantaran' => $opsi_pengantaran,
            'kecamatan'        => $kecamatan,
            'estimasi_biaya'   => $estimasi_biaya,
            'no_wa_admin'      => $no_whatsapp_admin,
        ];

        $sukses = true;
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Buat Pesanan - CleanCo</title>
    <link rel="stylesheet" href="../assets/css/style.css">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Bricolage+Grotesque:opsz,wght@12..96,200..800&display=swap" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=DM+Sans:ital,opsz,wght@0,9..40,100..1000;1,9..40,100..1000&display=swap" rel="stylesheet">
</head>
<body>

    <?php include '../includes/header-member.php'; ?>

    <section class="hero-form pesan-hero">

        <form id="formPesan" method="POST" action="pesan.php">

        <div class="konten-form konten-form-pesan">
            <h1 class="judul-form judul-form-kiri">Buat Pesanan Baru</h1>
            <p class="subjudul-pesan">Isi form di bawah untuk memulai pesanan laundry kamu.</p>

            <?php if (!empty($errors)): ?>
                <div class="pesan-error-box">
                    <?php foreach ($errors as $err): ?>
                        <p>⚠ <?= htmlspecialchars($err) ?></p>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>

            <!-- ── PILIH LAYANAN ── -->
            <div class="grup-input-form">
                <label class="label-form">Pilih Layanan</label>
                <div class="grid-pilih-layanan" id="gridLayanan">
                    <?php
                    $total_layanan = count($layanan_list);
                    foreach ($layanan_list as $i => $l):
                        $dipilih = ($total_layanan >= 2) ? ($i === 1) : ($i === 0);
                    ?>
                        <div class="kartu-pilih-layanan <?= $dipilih ? 'dipilih' : '' ?>"
                             data-id="<?= $l['id'] ?>"
                             data-nama="<?= htmlspecialchars($l['nama_layanan']) ?>"
                             data-tarif="<?= $l['tarif_per_kg'] ?>"
                             data-satuan="kg"
                             onclick="pilihLayanan(this)">
                            <div class="kartu-pilih-header <?= $dipilih ? 'kartu-pilih-header-biru' : '' ?>">
                                <?= htmlspecialchars($l['nama_layanan']) ?>
                            </div>
                            <div class="kartu-pilih-body">
                                <p class="kartu-pilih-tarif">
                                    Rp <?= number_format($l['tarif_per_kg'], 0, ',', '.') ?> / kg
                                </p>
                                <p class="kartu-pilih-durasi">
                                    <?= htmlspecialchars($l['deskripsi']) ?>
                                </p>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
                <input type="hidden" id="inputLayananId" name="layanan_id"
                       value="<?= $total_layanan >= 2 ? $layanan_list[1]['id'] : ($layanan_list[0]['id'] ?? '') ?>">
            </div>

            <!-- ── OPSI PENGANTARAN ── -->
            <div class="grup-input-form">
                <label class="label-form">Opsi Pengantaran</label>
                <div class="grid-opsi-pengantaran">

                    <label class="kartu-opsi-pengantaran">
                        <input type="radio" name="opsi_pengantaran"
                               value="ambil_sendiri"
                               onchange="gantiOpsiPengantaran('ambil_sendiri')">
                        <div class="kartu-opsi-isi">
                            <span class="kartu-opsi-ikon">🏬</span>
                            <span class="kartu-opsi-label">Ambil Sendiri</span>
                            <span class="kartu-opsi-biaya">Gratis</span>
                        </div>
                    </label>

                    <label class="kartu-opsi-pengantaran dipilih-opsi">
                        <input type="radio" name="opsi_pengantaran"
                               value="kurir"
                               checked
                               onchange="gantiOpsiPengantaran('kurir')">
                        <div class="kartu-opsi-isi">
                            <span class="kartu-opsi-ikon">🛵</span>
                            <span class="kartu-opsi-label">Kurir Laundry</span>
                            <span class="kartu-opsi-biaya">+ Rp 10.000</span>
                        </div>
                    </label>

                </div>
            </div>

            <!-- ── INFO KURIR ── -->
            <div class="info-kurir-wrapper" id="infoKurir">
                <p class="info-kurir-teks">
                    🛵 Kurir akan menghubungi kamu via WhatsApp sebelum menjemput.
                </p>
                <p class="info-kurir-teks">
                    📍 Layanan kurir tersedia untuk kecamatan:
                    <strong><?= htmlspecialchars(implode(', ', $kecamatan_list)) ?></strong>
                </p>
            </div>

            <!-- ── ALAMAT ── -->
            <div id="seksiAlamat">
                <div class="grup-input-form">
                    <label class="label-form">Kecamatan Tujuan</label>
                    <select class="input-form" id="inputKecamatan" name="kecamatan">
                        <option value="">-- Pilih Kecamatan --</option>
                        <?php foreach ($kecamatan_list as $kec): ?>
                            <option value="<?= htmlspecialchars($kec) ?>"
                                <?= (!$sukses && ($kecamatan ?? '') === $kec) ? 'selected' : '' ?>>
                                <?= htmlspecialchars($kec) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="grup-input-form">
                    <label class="label-form">Alamat Lengkap</label>
                    <input type="text" class="input-form"
                           id="inputAlamat" name="alamat_pengantaran"
                           placeholder="Jl. Nama Jalan, No. Rumah, Lingkungan..."
                           value="<?= !$sukses ? htmlspecialchars($alamat_pengantaran ?? '') : '' ?>">
                </div>
            </div>

            <!-- ── ESTIMASI BERAT ── -->
            <div class="grup-input-form">
                <label class="label-form">Estimasi Berat <span class="label-opsional">(opsional)</span></label>
                <div class="input-berat-wrapper">
                    <input type="number"
                           class="input-berat"
                           id="inputEstimasiBerat"
                           name="estimasi_berat"
                           placeholder="0"
                           min="0" step="0.1"
                           oninput="hitungEstimasi()"
                           value="<?= !$sukses && isset($estimasi_berat) ? $estimasi_berat : '' ?>"
                           style="width:110px;">
                    <span class="satuan-berat" style="color:white;">kg</span>
                </div>
            </div>

            <!-- ── ESTIMASI HARGA ── -->
            <div class="kotak-estimasi-harga" id="kotakEstimasi">
                <p class="estimasi-harga-teks" id="teksEstimasiHarga">
                    Harga akan dihitung admin setelah pakaian ditimbang.
                </p>
            </div>

            <!-- ── CATATAN KHUSUS ── -->
            <div class="grup-input-form">
                <label class="label-form">Catatan Khusus <span class="label-opsional">(opsional)</span></label>
                <textarea class="input-form input-textarea"
                          id="inputCatatan" name="catatan"
                          placeholder="cth: pisahkan baju putih, ada noda di bagian kerah..."><?= !$sukses ? htmlspecialchars($catatan_khusus ?? '') : '' ?></textarea>
            </div>

            <!-- ── TOMBOL SUBMIT ── -->
            <button type="button"
                    class="tombol-submit-form tombol-kirim-pesanan"
                    onclick="kirimPesanan(event)">
                Kirim Pesanan
            </button>

        </div>

        </form>

        <div class="bulat-atas"></div>
        <div class="bulat-ditengah"></div>
        <div class="bulat-besar"><h2>CleanCo</h2></div>

    </section>

    <!-- ── OVERLAY ── -->
    <div class="overlay-popup" id="overlayPopup" style="display:none;"></div>

    <!-- ── POP-UP SUKSES ── -->
    <div class="popup-sukses-pesanan" id="popupSukses" style="display:none;">

        <div class="popup-sukses-atas">
            <div class="popup-sukses-ikon">✓</div>
            <h2 class="popup-sukses-judul">Pesanan Berhasil Dibuat!</h2>
            <p class="popup-sukses-sub">Ringkasan pesanan kamu:</p>
        </div>

        <div class="popup-sukses-rincian">
            <div class="popup-rincian-baris">
                <span>Nomor Pesanan</span>
                <strong id="popupNoPesanan">—</strong>
            </div>
            <div class="popup-rincian-baris">
                <span>Layanan</span>
                <strong id="popupLayanan">—</strong>
            </div>
            <div class="popup-rincian-baris">
                <span>Pengantaran</span>
                <strong id="popupPengantaran">—</strong>
            </div>
            <div class="popup-rincian-baris">
                <span>Estimasi Biaya</span>
                <strong id="popupEstimasi">—</strong>
            </div>
        </div>

        <div class="popup-tombol-group" style="justify-content:center; gap:14px;">
            <a href="status.php" class="tombol-submit-form"
               style="text-decoration:none; text-align:center;">
                Lihat Status
            </a>
            <button class="tombol-batal-layanan"
                    onclick="pesanLagi()"
                    style="display:inline-block;">
                Pesan Lagi
            </button>
        </div>

    </div>

    <script>
    const sessionMember = {
        nama : '<?= htmlspecialchars($_SESSION['nama'] ?? 'Member') ?>',
        noHP : '<?= htmlspecialchars($_SESSION['no_hp'] ?? '') ?>',
        id   : <?= intval($id_member) ?>
    };

    <?php if ($sukses): ?>
    (function tampilkanPopupSukses() {
        const data = {
            kode      : '<?= htmlspecialchars($pesanan_baru['kode_pesanan']) ?>',
            layanan   : '<?= htmlspecialchars($pesanan_baru['nama_layanan']) ?>',
            opsi      : '<?= $pesanan_baru['opsi_pengantaran'] ?>',
            kecamatan : '<?= htmlspecialchars($pesanan_baru['kecamatan']) ?>',
            estimasi  : <?= $pesanan_baru['estimasi_biaya'] !== null ? $pesanan_baru['estimasi_biaya'] : 'null' ?>,
            noWa      : '<?= htmlspecialchars($pesanan_baru['no_wa_admin']) ?>'
        };

        const labelOpsi = data.opsi === 'kurir'
            ? 'Kurir ke ' + data.kecamatan
            : 'Ambil Sendiri';

        const pesanWa = encodeURIComponent(
            'Halo Admin, saya baru membuat pesanan laundry.\n' +
            'Kode Pesanan: ' + data.kode + '\n' +
            'Nama: ' + sessionMember.nama + '\n' +
            'Pengantaran: ' + labelOpsi
        );
        const noWaBersih = data.noWa.replace(/\D/g, '');
        if (noWaBersih) {
            window.open('https://wa.me/' + noWaBersih + '?text=' + pesanWa, '_blank');
        }

        document.getElementById('popupNoPesanan').textContent  = '#' + data.kode;
        document.getElementById('popupLayanan').textContent    = data.layanan;
        document.getElementById('popupPengantaran').textContent = labelOpsi;
        document.getElementById('popupEstimasi').textContent   = data.estimasi !== null
            ? 'Rp ' + data.estimasi.toLocaleString('id-ID') + ' (Estimasi, Belum Final)'
            : 'Dihitung admin setelah ditimbang';

        document.getElementById('overlayPopup').style.display = 'block';
        document.getElementById('popupSukses').style.display  = 'block';
    })();
    <?php endif; ?>

    function pesanLagi() {
        document.getElementById('overlayPopup').style.display = 'none';
        document.getElementById('popupSukses').style.display  = 'none';
    }
    </script>

    <script src="../assets/js/pesan-member.js"></script>
    <script src="../assets/js/kalkulasi-harga.js"></script>
    <script src="../assets/js/form-validation.js"></script>

    <?php include '../includes/footer.php'; ?>
</body>
</html>
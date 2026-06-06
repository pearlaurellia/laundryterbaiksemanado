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

        // FORMAT FEEDBACK UNTUK RESPONS AJAX MODAL
        $labelHarga = $estimasi_biaya !== null 
            ? 'Rp ' . number_format($estimasi_biaya, 0, ',', '.') . ' (Estimasi, Belum Final)' 
            : 'Dihitung admin setelah ditimbang';

        // DETEKSI AJAX INTERCEPTOR (Sesuai Fase 7.4)
        if (isset($_GET['action']) && $_GET['action'] === 'submit_ajax') {
            header('Content-Type: application/json');
            echo json_encode([
                'success' => true,
                'data' => [
                    'kode_pesanan' => $kode_pesanan,
                    'nama_layanan' => $layananTerpilih['nama_layanan'],
                    'opsi_pengantaran' => $opsi_pengantaran,
                    'kecamatan' => $kecamatan,
                    'total_estimasi' => $labelHarga
                ]
            ]);
            exit;
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
    } else {
        // Balasan error validasi jika dikirim lewat sistem AJAX
        if (isset($_GET['action']) && $_GET['action'] === 'submit_ajax') {
            header('Content-Type: application/json');
            echo json_encode([
                'success' => false,
                'message' => implode("\n", $errors)
            ]);
            exit;
        }
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

    <!-- HERO FORM -->
    <section class="hero-form" style="display: flex; align-items: center; justify-content: center; min-height: 100vh;">
        
        <!-- DEKORASI BULAT -->
        <div class="bulat-atas-form"></div>
        <div class="bulat-ditengah-form"></div>
        <div class="bulat-besar-form"><h2>CleanCo</h2></div>

        <div style="position: relative; z-index: 2; width: 100%; max-width: 700px; padding: 40px 20px;">
            
            <!-- HEADER -->
            <div style="text-align: center; margin-bottom: 40px;">
                <h1 style="font-family: 'Bricolage Grotesque', sans-serif; font-size: 2.2rem; color: white; margin: 0 0 8px; filter: drop-shadow(var(--shadow));">
                    Buat Pesanan Baru
                </h1>
                <p style="color: rgba(255,255,255,0.75); font-size: 1rem; margin: 0;">
                    Isi form di bawah untuk memulai pesanan laundry kamu.
                </p>
            </div>

            <form id="formPesan" method="POST" action="pesan.php">
                <div id="formPesanContainer"
                     data-sukses="<?= $sukses ? 'true' : 'false' ?>"
                     data-member-nama="<?= htmlspecialchars($_SESSION['nama'] ?? 'Member') ?>"
                     data-kode-pesanan="<?= htmlspecialchars($pesanan_baru['kode_pesanan'] ?? '') ?>"
                     data-nama-layanan="<?= htmlspecialchars($pesanan_baru['nama_layanan'] ?? '') ?>"
                     data-opsi-pengantaran="<?= htmlspecialchars($pesanan_baru['opsi_pengantaran'] ?? '') ?>"
                     data-kecamatan="<?= htmlspecialchars($pesanan_baru['kecamatan'] ?? '') ?>"
                     data-estimasi-biaya="<?= (isset($pesanan_baru['estimasi_biaya']) && $pesanan_baru['estimasi_biaya'] !== null) ? $pesanan_baru['estimasi_biaya'] : 'null' ?>"
                     data-no-wa-admin="<?= htmlspecialchars($no_whatsapp_admin) ?>">

                    <!-- ERROR BOX -->
                    <?php if (!empty($errors)): ?>
                        <div style="background: rgba(255,209,209,0.2); border: 1px solid rgba(255,209,209,0.5); border-radius: 0 12px 12px 0; border-left: 4px solid #f87171; padding: 14px 18px; margin-bottom: 24px;">
                            <?php foreach ($errors as $err): ?>
                                <p style="color: #fca5a5; margin: 4px 0; font-size: 0.9rem;">⚠ <?= htmlspecialchars($err) ?></p>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>

                    <!-- 1. PILIH LAYANAN -->
                    <div style="margin-bottom: 32px;">
                        <label style="color: white; font-size: 0.95rem; font-weight: 600; margin-bottom: 12px; display: block;">
                            Pilih Layanan
                        </label>
                        <div class="grid-pilih-layanan" id="gridLayanan">
                            <?php
                            $total_layanan = count($layanan_list);
                            $id_default = '';
                            foreach ($layanan_list as $i => $l):
                                $dipilih = ($total_layanan >= 2) ? ($i === 1) : ($i === 0);
                                if ($dipilih) { $id_default = $l['id']; }
                            ?>
                                <div class="kartu-pilih-layanan <?= $dipilih ? 'dipilih' : '' ?>"
                                     id="cardLayanan_<?= $l['id'] ?>"
                                     data-id="<?= $l['id'] ?>"
                                     data-nama="<?= htmlspecialchars($l['nama_layanan']) ?>"
                                     data-tarif="<?= $l['tarif_per_kg'] ?>"
                                     data-satuan="kg"
                                     onclick="pilihLayanan(this)"
                                     style="cursor: pointer;">
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
                        <input type="hidden" id="inputLayananId" name="layanan_id" value="<?= $id_default ?>">
                    </div>

                    <!-- 2. OPSI PENGANTARAN -->
                    <div style="margin-bottom: 24px;">
                        <label style="color: white; font-size: 0.95rem; font-weight: 600; margin-bottom: 12px; display: block;">
                            Opsi Pengantaran
                        </label>
                        <div class="grid-opsi-pengantaran">
                            <label class="kartu-opsi-pengantaran" onclick="gantiOpsiPengantaran('ambil_sendiri')" style="cursor: pointer;">
                                <input type="radio" name="opsi_pengantaran" value="ambil_sendiri" onchange="gantiOpsiPengantaran('ambil_sendiri')">
                                <div class="kartu-opsi-isi">
                                    <span class="kartu-opsi-ikon">🏬</span>
                                    <span class="kartu-opsi-label">Ambil Sendiri</span>
                                    <span class="kartu-opsi-biaya" style="color: #10b981;">Gratis</span>
                                </div>
                            </label>

                            <label class="kartu-opsi-pengantaran dipilih-opsi" onclick="gantiOpsiPengantaran('kurir')" style="cursor: pointer;">
                                <input type="radio" name="opsi_pengantaran" value="kurir" checked onchange="gantiOpsiPengantaran('kurir')">
                                <div class="kartu-opsi-isi">
                                    <span class="kartu-opsi-ikon">🛵</span>
                                    <span class="kartu-opsi-label">Kurir Laundry</span>
                                    <span class="kartu-opsi-biaya" style="color: #f59e0b;">+ Rp 10.000</span>
                                </div>
                            </label>
                        </div>
                    </div>

                    <!-- INFO KURIR -->
                    <div class="info-kurir-wrapper" id="infoKurir">
                        <p class="info-kurir-teks">
                            💡 Kurir akan menghubungi kamu via WhatsApp sebelum menjemput pakaian.
                        </p>
                        <p class="info-kurir-teks">
                            📍 Layanan kurir tersedia untuk kecamatan:
                            <strong><?= htmlspecialchars(implode(', ', $kecamatan_list)) ?></strong>
                        </p>
                    </div>

                    <!-- 3. ALAMAT (KURIR) -->
                    <div id="containerAlamat" style="margin-bottom: 8px;">
                        <div style="margin-bottom: 16px;">
                            <label style="color: white; font-size: 0.95rem; font-weight: 600; margin-bottom: 8px; display: block;">
                                Kecamatan Tujuan
                            </label>
                            <select id="inputKecamatan" name="kecamatan" 
                                    style="width: 100%; padding: 12px 20px; border: none; border-radius: 20px 0 20px 0; font-family: 'DM Sans', sans-serif; font-size: 0.95rem; color: #333; background: white; box-shadow: var(--shadow); outline: none; cursor: pointer;">
                                <option value="">-- Pilih Kecamatan --</option>
                                <?php foreach ($kecamatan_list as $kec): ?>
                                    <option value="<?= htmlspecialchars($kec) ?>"
                                        <?= (!$sukses && ($kecamatan ?? '') === $kec) ? 'selected' : '' ?>>
                                        <?= htmlspecialchars($kec) ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div style="margin-bottom: 16px;">
                            <label style="color: white; font-size: 0.95rem; font-weight: 600; margin-bottom: 8px; display: block;">
                                Alamat Lengkap
                            </label>
                            <input type="text" id="inputAlamat" name="alamat_pengantaran" 
                                   placeholder="Jl. Nama Jalan, No. Rumah, Lingkungan..."
                                   value="<?= !$sukses ? htmlspecialchars($alamat_pengantaran ?? '') : '' ?>"
                                   style="width: 100%; padding: 12px 20px; border: none; border-radius: 20px 0 20px 0; font-family: 'DM Sans', sans-serif; font-size: 0.95rem; color: #333; background: white; box-shadow: var(--shadow); outline: none;">
                        </div>
                    </div>

                    <!-- 4. ESTIMASI BERAT -->
                    <div style="margin-bottom: 24px;">
                        <label style="color: white; font-size: 0.95rem; font-weight: 600; margin-bottom: 8px; display: block;">
                            Estimasi Berat <span style="font-size: 0.8rem; color: rgba(255,255,255,0.5); font-weight: 400;">(opsional)</span>
                        </label>
                        <div style="display: flex; align-items: center; gap: 12px;">
                            <input type="number" id="inputEstimasiBerat" name="estimasi_berat" 
                                   placeholder="0" min="0" step="0.1"
                                   value="<?= !$sukses && isset($estimasi_berat) ? $estimasi_berat : '' ?>"
                                   style="width: 130px; padding: 12px 16px; border: none; border-radius: 20px 0 20px 0; font-family: 'DM Sans', sans-serif; font-size: 1rem; font-weight: 600; color: var(--birutua); background: white; box-shadow: var(--shadow); outline: none;">
                            <span style="color: white; font-size: 0.95rem;">kg</span>
                        </div>
                    </div>

                    <!-- 5. KOTAK ESTIMASI HARGA -->
                    <div id="kotakEstimasi" class="kotak-estimasi-harga" style="padding: 16px 20px; border-radius: 0 16px 16px 16px; margin-bottom: 24px;">
                        <p class="estimasi-harga-teks" id="teksEstimasiHarga" style="margin: 0;">
                            Harga akan dihitung admin setelah pakaian ditimbang.
                        </p>
                    </div>

                    <!-- 6. CATATAN KHUSUS -->
                    <div style="margin-bottom: 28px;">
                        <label style="color: white; font-size: 0.95rem; font-weight: 600; margin-bottom: 8px; display: block;">
                            Catatan Khusus <span style="font-size: 0.8rem; color: rgba(255,255,255,0.5); font-weight: 400;">(opsional)</span>
                        </label>
                        <textarea id="inputCatatan" name="catatan" 
                                  placeholder="cth: pisahkan baju putih, ada noda di bagian kerah..."
                                  style="width: 100%; min-height: 100px; padding: 14px 18px; border: none; border-radius: 20px 0 20px 0; font-family: 'DM Sans', sans-serif; font-size: 0.95rem; color: #333; background: white; box-shadow: var(--shadow); outline: none; resize: vertical;"><?= !$sukses ? htmlspecialchars($catatan_khusus ?? '') : '' ?></textarea>
                    </div>

                    <!-- 7. TOMBOL KIRIM -->
                    <button type="button" class="tombol-submit-form tombol-kirim-pesanan" 
                            onclick="kirimPesananForm(event)"
                            style="width: 100%; padding: 16px; font-size: 1.05rem; font-weight: 600; letter-spacing: 0.5px; background: white; color: var(--birutua);">
                        ✨ Kirim Pesanan
                    </button>

                </div>
            </form>
        </div>
    </section>

    <!-- POPUP SUKSES -->
    <div class="overlay-popup" id="overlayPopup" style="display:none;"></div>

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

        <div class="popup-tombol-group" style="justify-content:center; gap:12px; display:flex; flex-wrap:wrap; padding: 0 36px 28px;">
            <a id="tombolKonfirmasiWa"
               href="#"
               target="_blank"
               style="display:none; align-items:center; gap:8px; text-decoration:none;
                      padding:10px 18px; background:#25D366; color:white;
                      border-radius:8px; font-weight:600; font-size:0.9rem;">
                💬 Konfirmasi via WhatsApp
            </a>
            <a href="status.php"
               style="text-decoration:none; text-align:center;
                      padding:10px 18px; background:var(--birutua); color:white;
                      border-radius:8px; font-weight:600; font-size:0.9rem;">
                Lihat Status
            </a>
            <button onclick="pesanLagi()"
                    style="padding:10px 18px; cursor:pointer; border-radius:8px;
                           font-weight:600; font-size:0.9rem; border:1.5px solid #ccc;
                           background:white; color:#444;">
                Pesan Lagi
            </button>
        </div>
    </div>

    <script src="../assets/js/kalkulasi-harga.js"></script>
    <script src="../assets/js/form-validation.js"></script>
    <script src="../assets/js/pesan-member.js"></script>

    <?php include '../includes/footer.php'; ?>
</body>
</html>
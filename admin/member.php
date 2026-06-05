<?php
require_once '../config/session.php';
require_once '../config/database.php';
require_once '../config/functions.php';

if (!isset($_SESSION['id_user']) || $_SESSION['role'] !== 'admin') {
    redirect('../login.php');
}

// ── Ambil semua member dari database ────────────────────────
$stmt = $pdo->prepare("
    SELECT 
        u.*,
        COUNT(p.id) AS total_pesanan,
        SUM(CASE WHEN p.status_pesanan = 'selesai' THEN 1 ELSE 0 END) AS pesanan_selesai,
        SUM(CASE WHEN p.status_pesanan NOT IN ('selesai','dibatalkan') THEN 1 ELSE 0 END) AS pesanan_aktif,
        SUM(CASE WHEN p.status_pesanan = 'dibatalkan' THEN 1 ELSE 0 END) AS pesanan_batal,
        SUM(CASE WHEN p.status_pesanan = 'selesai' THEN p.total_harga ELSE 0 END) AS total_omzet
    FROM users u
    LEFT JOIN pesanan p ON p.id_member = u.id
    WHERE u.role = 'member'
    GROUP BY u.id
    ORDER BY u.created_at DESC
");
$stmt->execute();
$members = $stmt->fetchAll() ?: [];
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kelola Member - CleanCo Admin</title>
    <link rel="stylesheet" href="../assets/css/style.css">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Bricolage+Grotesque:opsz,wght@12..96,200..800&display=swap" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=DM+Sans:ital,opsz,wght@0,9..40,100..1000;1,9..40,100..1000&display=swap" rel="stylesheet">
    <script src="../assets/js/member-admin.js" defer></script>
</head>
<body>

    <?php include '../includes/header-admin.php'; ?>

    <section class="halaman-pesanan">

        <!-- ===================== SIDEBAR KIRI ===================== -->
        <div class="pesanan-sidebar">
            <h2 class="judul-sidebar">Daftar Member</h2>

            <div class="member-search-wrapper">
                <input
                    type="text"
                    class="input-form member-search"
                    id="inputCariMember"
                    placeholder="Cari nama member..."
                    oninput="cariMember(this.value)"
                >
            </div>

            <div class="grup-filter">
                <button class="tombol-filter aktif" onclick="filterMember('semua', this)">Semua</button>
                <button class="tombol-filter" onclick="filterMember('aktif', this)">Aktif</button>
                <button class="tombol-filter" onclick="filterMember('nonaktif', this)">Nonaktif</button>
            </div>

            <div class="list-pesanan" id="listMember">
                <?php if (empty($members)): ?>
                    <p style="color:#aaa; padding:20px; text-align:center;">
                        Belum ada member terdaftar.
                    </p>
                <?php else: ?>
                    <?php foreach ($members as $m): ?>
                        <?php
                            $status     = $m['status_akun']; // 'aktif' | 'nonaktif'
                            $badgeKelas = $status === 'aktif' ? 'badge-member-aktif' : 'badge-member-nonaktif';
                            $badgeLabel = $status === 'aktif' ? 'Aktif' : 'Nonaktif';
                            $tgl        = date('d-m-Y', strtotime($m['created_at']));
                        ?>
                        <div class="item-member"
                             data-id="<?= $m['id'] ?>"
                             data-status="<?= $status ?>"
                             data-nama="<?= strtolower(htmlspecialchars($m['nama'])) ?>"
                             onclick="bukaMember(<?= $m['id'] ?>, this)">
                            <div class="item-pesanan-atas">
                                <span class="badge-status-member <?= $badgeKelas ?>"><?= $badgeLabel ?></span>
                                <span class="item-pesanan-waktu">Bergabung <?= $tgl ?></span>
                            </div>
                            <p class="item-pesanan-nama"><?= htmlspecialchars($m['nama']) ?></p>
                            <p class="item-member-sub"><?= $m['total_pesanan'] ?> transaksi</p>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
        </div>

        <!-- ===================== DETAIL PANEL ===================== -->
        <div class="pesanan-detail" id="memberDetail">

            <div class="detail-kosong" id="detailKosong">
                <div style="width:80px;height:80px;border-radius:50%;
                            background:rgba(13,63,138,0.08);margin-bottom:20px;"></div>
                <p style="color:#aaa;">Pilih member untuk melihat detail</p>
            </div>

            <div class="detail-isi" id="detailIsi" style="display:none;">

                <div class="detail-header">
                    <div>
                        <h2 class="detail-nama" id="detailNama">—</h2>
                        <p class="detail-username" id="detailUsername">—</p>
                    </div>
                    <div class="detail-waktu-badge" id="detailTanggalBergabung">—</div>
                </div>

                <div class="detail-info-grid">
                    <div class="detail-info-blok">
                        <p class="detail-label">Nama Lengkap</p>
                        <p class="detail-nilai" id="detailNamaLengkap">—</p>
                    </div>
                    <div class="detail-info-blok">
                        <p class="detail-label">Email</p>
                        <p class="detail-nilai" id="detailEmail">—</p>
                    </div>
                    <div class="detail-info-blok">
                        <p class="detail-label">Nomor WhatsApp</p>
                        <p class="detail-nilai" id="detailNoHP">—</p>
                    </div>
                    <div class="detail-info-blok">
                        <p class="detail-label">Total Transaksi</p>
                        <p class="detail-nilai" id="detailTotalTransaksi">—</p>
                    </div>
                </div>

                <div class="detail-berat-biaya">
                    <div class="kartu-berat">
                        <p class="detail-label">Total Pesanan</p>
                        <p class="member-stat-angka" id="detailJmlPesanan">—</p>
                        <p class="member-stat-sub">pesanan tercatat</p>
                    </div>
                    <div class="kartu-biaya">
                        <p class="detail-label">Ringkasan Transaksi</p>
                        <p class="rincian-baris" id="detailPesananSelesai">Selesai : —</p>
                        <p class="rincian-baris" id="detailPesananAktif">Aktif : —</p>
                        <p class="rincian-baris" id="detailPesananBatal">Dibatalkan : —</p>
                        <p class="rincian-total" id="detailTotalOmzet">Total Nilai : Rp —</p>
                    </div>
                </div>

                <div class="detail-status-section" style="margin-bottom:20px;">
                    <p class="detail-label" style="margin-bottom:12px;">3 Pesanan Terakhir</p>
                    <div id="detailRiwayatSingkat"></div>
                </div>

                <div class="detail-status-section">
                    <p class="detail-label" style="margin-bottom:4px;">Status Akun Member</p>
                    <p class="status-aktif-teks" style="margin-bottom:14px;">
                        Status saat ini: <strong id="statusAkunTeks">—</strong>
                    </p>
                    <div style="display:flex; gap:12px; flex-wrap:wrap;">
                        <button class="tombol-aktifkan-member"
                                id="tombolAktifkan"
                                onclick="toggleStatusMember('aktif')"
                                style="display:none;">
                            ✓ Aktifkan Akun
                        </button>
                        <button class="tombol-nonaktif-member"
                                id="tombolNonaktif"
                                onclick="toggleStatusMember('nonaktif')">
                            ✕ Nonaktifkan Akun
                        </button>
                        <a class="tombol-wa-member"
                           id="tombolWA"
                           href="#"
                           target="_blank">
                            Hubungi via WhatsApp
                        </a>
                    </div>
                </div>

            </div>
        </div>

    </section>

    <!-- ===================== POP-UP KONFIRMASI ===================== -->
    <div class="overlay-popup" id="overlayPopup" style="display:none;"
         onclick="tutupPopup()"></div>

    <div class="popup-konfirmasi" id="popupKonfirmasi" style="display:none;">
        <h3 class="popup-judul" id="popupJudul">Nonaktifkan Akun?</h3>
        <p class="popup-teks" id="popupTeks">
            Member yang dinonaktifkan tidak dapat login ke sistem.
        </p>
        <div class="popup-tombol-group">
            <button class="popup-tombol-batal" onclick="tutupPopup()">Batal</button>
            <button class="popup-tombol-konfirm" id="popupTombolKonfirm"
                    onclick="konfirmasiToggle()">Ya, Lanjutkan</button>
        </div>
    </div>

    <!--
        Data member di-embed sebagai JSON untuk dipakai oleh member-admin.js
        tanpa perlu fetch API tambahan.
    -->
<?php
// 1. Jalankan semua logika PHP terlebih dahulu sebelum tag <script>
$riwayatMap = [];

$stmtR = $pdo->prepare("
    SELECT p.id_member, p.kode_pesanan, l.nama_layanan, p.total_harga, p.status_pesanan
    FROM pesanan p
    JOIN layanan l ON p.id_layanan = l.id
    WHERE p.id_member IN (
        SELECT id FROM users WHERE role = 'member'
    )
    ORDER BY p.created_at DESC
");
$stmtR->execute();

foreach ($stmtR->fetchAll() as $r) {
    if (!isset($riwayatMap[$r['id_member']]) || count($riwayatMap[$r['id_member']]) < 3) {
        $riwayatMap[$r['id_member']][] = [
            'kode'    => $r['kode_pesanan'],
            'layanan' => $r['nama_layanan'],
            'total'   => 'Rp ' . number_format($r['total_harga'], 0, ',', '.'),
            'status'  => $r['status_pesanan'],
        ];
    }
}

// Catatan: Pastikan variabel $members sudah didefinisikan sebelumnya di kodinganmu
// $members = ...;

// 2. Bentuk array datanya
$dataMemberArray = array_column(
    // WAJIB tambahkan `use ($riwayatMap)` agar variabel di luar bisa diakses di dalam function array_map
    array_map(function($m) use ($riwayatMap) { 
        return [
            'id'               => $m['id'],
            'nama'             => $m['nama'],
            'username'         => $m['email'], // tidak ada kolom username di DB
            'namaLengkap'      => $m['nama'],
            'email'            => $m['email'],
            'noHP'             => $m['no_hp'],
            'tanggalBergabung' => date('d-m-Y', strtotime($m['created_at'])),
            'status'           => $m['status_akun'],
            'jmlPesanan'       => (int)$m['total_pesanan'],
            'pesananSelesai'   => (int)$m['pesanan_selesai'],
            'pesananAktif'     => (int)$m['pesanan_aktif'],
            'pesananBatal'     => (int)$m['pesanan_batal'],
            'totalOmzet'       => (float)$m['total_omzet'],
            'riwayatSingkat'   => $riwayatMap[$m['id']] ?? [],
        ];
    }, $members),
    null,
    'id'
);
?>

<script>
    const dataMember = <?= json_encode($dataMemberArray, JSON_UNESCAPED_UNICODE) ?>;
    console.log(dataMember); // Opsional: Untuk mengecek hasilnya di inspect element browser
</script>

</body>
</html>
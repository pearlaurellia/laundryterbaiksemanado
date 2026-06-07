<?php
require_once '../includes/auth-check.php';
require_once '../config/database.php';
require_once '../config/functions.php';

$id_pesanan = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if ($id_pesanan <= 0) {
    redirect('status.php');
}

$stmtPesanan = $pdo->prepare("
    SELECT p.*, u.nama AS nama_member, u.no_hp,
           l.nama_layanan, l.tarif_per_kg, l.satuan
    FROM pesanan p
    JOIN users u   ON p.id_member  = u.id
    JOIN layanan l ON p.id_layanan = l.id
    WHERE p.id = ? AND p.id_member = ?
");
$stmtPesanan->execute([$id_pesanan, $_SESSION['id_user']]);
$p = $stmtPesanan->fetch();

if (!$p) {
    redirect('status.php');
}

$stmtRiwayat = $pdo->prepare("
    SELECT * FROM riwayat_status
    WHERE id_pesanan = ?
    ORDER BY changed_at ASC
");
$stmtRiwayat->execute([$id_pesanan]);
$riwayat = $stmtRiwayat->fetchAll() ?: [];

$status         = $p['status_pesanan'];
$opsi           = $p['opsi_pengantaran']; 
$sudahDitimbang = ($p['berat_aktual'] > 0);

$labelStatus = [
    'menunggu_konfirmasi' => 'Menunggu Konfirmasi',
    'dikonfirmasi'        => 'Dikonfirmasi',
    'sedang_dicuci'       => 'Sedang Dicuci',
    'siap_diambil'        => 'Siap Diambil',
    'sedang_diantar'      => 'Sedang Diantar',
    'selesai'             => 'Selesai & Lunas',
    'dibatalkan'          => 'Dibatalkan',
][$status] ?? $status;

$kelasStatus = [
    'menunggu_konfirmasi' => 'badge-status-baru',
    'dikonfirmasi'        => 'badge-status-dikonfirmasi',
    'sedang_dicuci'       => 'badge-status-diproses',
    'siap_diambil'        => 'badge-status-selesai',
    'sedang_diantar'      => 'badge-status-diproses',
    'selesai'             => 'badge-status-selesai',
    'dibatalkan'          => 'badge-status-batal',
][$status] ?? '';

$stepsKurir = [
    ['key' => 'menunggu_konfirmasi', 'label' => 'Menunggu'],
    ['key' => 'dikonfirmasi',        'label' => 'Dikonfirmasi'],
    ['key' => 'sedang_dicuci',       'label' => 'Sedang Dicuci'],
    ['key' => 'sedang_diantar',      'label' => 'Sedang Diantar'],
    ['key' => 'selesai',             'label' => 'Selesai'],
];
$stepsAmbil = [
    ['key' => 'menunggu_konfirmasi', 'label' => 'Menunggu'],
    ['key' => 'dikonfirmasi',        'label' => 'Dikonfirmasi'],
    ['key' => 'sedang_dicuci',       'label' => 'Sedang Dicuci'],
    ['key' => 'siap_diambil',        'label' => 'Siap Diambil'],
    ['key' => 'selesai',             'label' => 'Selesai'],
];
$steps    = ($opsi === 'kurir') ? $stepsKurir : $stepsAmbil;
$aktifIdx = 0;
foreach ($steps as $i => $step) {
    if ($step['key'] === $status) { $aktifIdx = $i; break; }
}

$labelTimeline = [
    'menunggu_konfirmasi' => 'Pesanan Dibuat',
    'dikonfirmasi'        => 'Pesanan Dikonfirmasi Admin',
    'sedang_dicuci'       => 'Sedang Dicuci',
    'siap_diambil'        => 'Siap Diambil — Datang ke Outlet',
    'sedang_diantar'      => 'Sedang Diantar ke Alamat Kamu',
    'selesai'             => 'Selesai & Lunas',
    'dibatalkan'          => 'Pesanan Dibatalkan',
];
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Detail Pesanan - Laundry 3J</title>
    <link rel="stylesheet" href="../assets/css/style.css">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Bricolage+Grotesque:opsz,wght@12..96,200..800&display=swap" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=DM+Sans:ital,opsz,wght@0,9..40,100..1000;1,9..40,100..1000&display=swap" rel="stylesheet">
</head>
<body>

    <?php include '../includes/header-member.php'; ?>

    <section class="detail-pesanan-section">

        <a href="status.php" class="tombol-kembali-member">← Kembali ke Status</a>

        <?php if ($status === 'siap_diambil'): ?>
            <div class="banner-status banner-hijau">
                <span class="banner-ikon">✓</span>
                <p>Cucian kamu siap diambil! Datang ke outlet dan bayar saat pengambilan.</p>
            </div>

        <?php elseif ($status === 'sedang_diantar'): ?>
            <div class="banner-status banner-biru">
                <span class="banner-ikon">🛵</span>
                <p>Cucian kamu sedang dalam perjalanan ke alamat kamu!</p>
            </div>

        <?php elseif ($status === 'dibatalkan'): ?>
            <div class="banner-status" style="background:#fff5f5; border:1px solid #f87171;">
                <span class="banner-ikon">✕</span>
                <p style="color:#D32F2F;">
                    Pesanan ini telah dibatalkan.
                    <?php if (!empty($p['alasan_pembatalan'])): ?>
                        <br><strong>Alasan:</strong> <?= htmlspecialchars($p['alasan_pembatalan']) ?>
                    <?php endif; ?>
                </p>
            </div>
        <?php endif; ?>

        <div class="detail-pesanan-header">
            <div>
                <h1 class="detail-pesanan-judul">
                    #<?= htmlspecialchars($p['kode_pesanan']) ?>
                </h1>
                <p class="detail-pesanan-sub">
                    <?= htmlspecialchars($p['nama_layanan']) ?> &middot; Dibuat
                    <?= date('H:i l, d-m-Y', strtotime($p['created_at'])) ?>
                </p>
            </div>
            <span class="badge-status <?= $kelasStatus ?>">
                <?= htmlspecialchars($labelStatus) ?>
            </span>
        </div>

        <div class="progress-bar-wrapper">
            <div class="progress-bar-track">
                <?php foreach ($steps as $i => $step):
                    $kelas = 'step-progress';
                    $isi   = $i + 1;
                    if ($i < $aktifIdx)        { $kelas .= ' step-selesai'; $isi = '✓'; }
                    elseif ($i === $aktifIdx)  { $kelas .= ' step-aktif'; }
                    $garis = ($i < count($steps) - 1)
                        ? '<div class="garis-progress ' . ($i < $aktifIdx ? 'garis-selesai' : '') . '"></div>'
                        : '';
                ?>
                    <div class="<?= $kelas ?>">
                        <div class="step-lingkaran"><?= $isi ?></div>
                        <p class="step-label"><?= htmlspecialchars($step['label']) ?></p>
                    </div>
                    <?= $garis ?>
                <?php endforeach; ?>
            </div>
        </div>

        <div class="detail-pesanan-grid">

            <div class="detail-pesanan-kiri">
                <div class="detail-info-grid" style="margin-bottom:20px;">

                    <div class="detail-info-blok">
                        <p class="detail-label">Layanan</p>
                        <p class="detail-nilai"><?= htmlspecialchars($p['nama_layanan']) ?></p>
                    </div>

                    <div class="detail-info-blok">
                        <p class="detail-label">Pengantaran</p>
                        <p class="detail-nilai">
                            <?= $opsi === 'kurir' ? 'Kurir Laundry' : 'Ambil Sendiri' ?>
                        </p>
                    </div>

                    <?php if ($opsi === 'kurir'): ?>
                    <div class="detail-info-blok">
                        <p class="detail-label">Kecamatan Tujuan</p>
                        <p class="detail-nilai">
                            <?= htmlspecialchars($p['kecamatan'] ?? '—') ?>
                        </p>
                    </div>
                    <div class="detail-info-blok">
                        <p class="detail-label">Alamat Lengkap</p>
                        <p class="detail-nilai">
                            <?= htmlspecialchars($p['alamat_pengantaran'] ?? '—') ?>
                        </p>
                    </div>
                    <?php endif; ?>

                    <div class="detail-info-blok">
                        <p class="detail-label">Estimasi Berat</p>
                        <p class="detail-nilai">
                            <?php if (!empty($p['estimasi_berat']) && $p['estimasi_berat'] > 0): ?>
                                <?= htmlspecialchars($p['estimasi_berat']) ?> <?= htmlspecialchars($p['satuan']) ?> (estimasi)
                            <?php else: ?>
                                Tidak diisi
                            <?php endif; ?>
                        </p>
                    </div>

                    <div class="detail-info-blok">
                        <p class="detail-label">Status Pembayaran</p>
                        <p class="detail-nilai"
                           style="font-weight:700; color:<?= $p['status_pembayaran'] === 'lunas' ? '#52c49c' : '#f59e0b' ?>;">
                            <?= $p['status_pembayaran'] === 'lunas' ? 'Lunas' : 'Belum Bayar' ?>
                        </p>
                    </div>

                </div>

                <div class="detail-catatan-wrapper">
                    <p class="detail-label">Catatan Kamu</p>
                    <p class="detail-catatan-isi">
                        <?= !empty($p['catatan_khusus'])
                            ? htmlspecialchars($p['catatan_khusus'])
                            : 'Tidak ada catatan.' ?>
                    </p>
                </div>
            </div>

            <div class="detail-pesanan-kanan">

                <?php if (!$sudahDitimbang): ?>
                <div class="kotak-menunggu-timbang">
                    <div class="menunggu-ikon">⚖️</div>
                    <p class="menunggu-judul">Menunggu Penimbangan Admin</p>
                    <p class="menunggu-sub">
                        Harga final akan muncul setelah admin menimbang pakaian kamu di outlet.
                    </p>
                </div>

                <?php else: ?>
                <?php
                    $biayaLayanan = $p['berat_aktual'] * $p['tarif_per_kg'];
                    $biayaKurir   = (float)($p['biaya_kurir'] ?? 0);
                    $totalHarga   = (float)$p['total_harga'];
                ?>
                <div class="kotak-harga-final">
                    <p class="detail-label" style="margin-bottom:12px;">Rincian Biaya Final</p>
                    <p class="rincian-baris">
                        <?= htmlspecialchars($p['nama_layanan']) ?>
                        (<?= $p['berat_aktual'] ?> <?= htmlspecialchars($p['satuan']) ?> &times;
                        <?= formatRupiah($p['tarif_per_kg']) ?>) :
                        <?= formatRupiah($biayaLayanan) ?>
                    </p>
                    <?php if ($opsi === 'kurir'): ?>
                    <p class="rincian-baris">
                        Kurir : <?= formatRupiah($biayaKurir) ?>
                    </p>
                    <?php endif; ?>
                    <p class="rincian-total">
                        Total : <?= formatRupiah($totalHarga) ?>
                        <span class="label-final">(Harga Final)</span>
                    </p>
                    <div class="berat-aktual-badge">
                        <span>⚖️ Berat Aktual</span>
                        <strong><?= $p['berat_aktual'] ?> <?= htmlspecialchars($p['satuan']) ?></strong>
                    </div>
                </div>
                <?php endif; ?>

                <div class="timeline-status">
                    <p class="detail-label" style="margin-bottom:14px;">Riwayat Status</p>
                    <div>
                        <?php if (empty($riwayat)): ?>
                            <p style="color:#aaa; font-size:0.9rem;">Belum ada riwayat status.</p>
                        <?php else: ?>
                            <?php foreach ($riwayat as $idx => $r):
                                $isLast    = ($idx === count($riwayat) - 1);
                                $kelasItem = 'timeline-item' . ($isLast ? ' timeline-item-aktif timeline-item-terakhir' : ' timeline-item-selesai');
                                $kelasDot  = 'timeline-dot' . ($isLast ? ' timeline-dot-aktif' : '');
                                $labelTl   = $labelTimeline[$r['status_baru']] ?? htmlspecialchars($r['status_baru']);
                                $waktuTl   = date('H:i, d M Y', strtotime($r['changed_at']));
                            ?>
                            <div class="<?= $kelasItem ?>"
                                 style="<?= $isLast ? 'border-left:none;' : '' ?>">
                                <div class="<?= $kelasDot ?>"></div>
                                <div class="timeline-konten">
                                    <p class="timeline-status-teks">
                                        <?= htmlspecialchars($labelTl) ?>
                                    </p>
                                    <p class="timeline-waktu"><?= $waktuTl ?></p>
                                </div>
                            </div>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </div>
                </div>

            </div>

        </div>

    </section>

    <?php include '../includes/footer.php'; ?>
</body>
</html>
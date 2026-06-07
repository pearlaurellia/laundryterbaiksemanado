<?php
require_once '../includes/auth-check.php';
require_once '../config/database.php';
require_once '../config/functions.php';

$stmtRiwayat = $pdo->prepare("
    SELECT p.*, l.nama_layanan
    FROM pesanan p
    JOIN layanan l ON p.id_layanan = l.id
    WHERE p.id_member = ?
      AND p.status_pesanan IN ('selesai', 'dibatalkan')
    ORDER BY p.updated_at DESC
");
$stmtRiwayat->execute([$_SESSION['id_user']]);
$riwayat = $stmtRiwayat->fetchAll() ?: [];
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Riwayat Pesanan - Laundry 3J</title>
    <link rel="stylesheet" href="../assets/css/style.css">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Bricolage+Grotesque:opsz,wght@12..96,200..800&display=swap" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=DM+Sans:ital,opsz,wght@0,9..40,100..1000;1,9..40,100..1000&display=swap" rel="stylesheet">
</head>
<body>

    <?php include '../includes/header-member.php'; ?>

    <section class="riwayat-section">
        <div class="riwayat-header">
            <h1 class="judul-overview-layanan">Riwayat Pesanan</h1>
            <p class="status-subjudul" style="text-align:center; margin-top:8px;">
                Arsip seluruh pesanan yang telah selesai atau dibatalkan.
            </p>
        </div>

        <?php if (empty($riwayat)): ?>
        <div class="status-kosong">
            <div class="status-kosong-ikon">📋</div>
            <h2 class="status-kosong-judul">Belum ada riwayat</h2>
            <p class="status-kosong-sub">
                Pesanan yang sudah selesai atau dibatalkan akan muncul di sini.
            </p>
            <a href="pesan.php" class="tombol-submit-form"
               style="text-decoration:none; display:inline-block; margin-top:10px;">
                Buat Pesanan Pertama
            </a>
        </div>

        <?php else: ?>

        <div class="grup-filter riwayat-filter" style="justify-content:center; margin-bottom:30px;">
            <button class="tombol-filter aktif"
                    onclick="filterRiwayat('semua', this)">
                Semua
            </button>
            <button class="tombol-filter"
                    onclick="filterRiwayat('selesai', this)">
                Selesai
            </button>
            <button class="tombol-filter"
                    onclick="filterRiwayat('dibatalkan', this)">
                Dibatalkan
            </button>
        </div>

        <div class="riwayat-list" id="riwayatList">
            <?php foreach ($riwayat as $r): ?>
            <div class="kartu-sejarah" data-status="<?= $r['status_pesanan'] ?>">
                <div class="sejarah-body">

                    <div class="grup-keterangan">
                        <?php if ($r['status_pesanan'] === 'selesai'): ?>
                            <span class="badge-biru" style="background:#d1fae5; color:#065f46;">Selesai</span>
                        <?php else: ?>
                            <span class="badge-biru" style="background:#fee2e2; color:#991b1b;">Dibatalkan</span>
                        <?php endif; ?>

                        <?php if ($r['status_pesanan'] === 'dibatalkan' && $r['dibatalkan_oleh'] === 'admin'): ?>
                            <span class="badge-biru" style="background:#fef3c7; color:#92400e;">Dibatalkan oleh Admin</span>
                        <?php endif; ?>

                        <span class="badge-biru">
                            <?= date('H:i l, d-m-Y', strtotime($r['created_at'])) ?>
                        </span>
                        <span class="badge-biru">
                            <?= htmlspecialchars($r['nama_layanan']) ?>
                        </span>
                        <span class="badge-biru">
                            <?= $r['opsi_pengantaran'] === 'kurir' ? 'Antar' : 'Pickup' ?>
                        </span>
                        <?php if ($r['berat_aktual'] > 0): ?>
                            <span class="badge-biru"><?= $r['berat_aktual'] ?> kg</span>
                        <?php endif; ?>
                    </div>

                    <?php if ($r['status_pesanan'] === 'selesai' && $r['berat_aktual'] > 0): ?>
                        <p>Total harga: <?= formatRupiah($r['total_harga']) ?></p>
                    <?php endif; ?>

                    <?php if ($r['status_pesanan'] === 'dibatalkan' && $r['dibatalkan_oleh'] === 'admin' && !empty($r['alasan_pembatalan'])): ?>
                        <p style="color:#991b1b; font-size:0.88rem; margin-top:4px;">
                            <strong>Alasan:</strong> <?= htmlspecialchars($r['alasan_pembatalan']) ?>
                        </p>
                    <?php endif; ?>

                    <a href="detail-pesanan.php?id=<?= $r['id'] ?>" class="tombol-detail">
                        Detail Pesanan
                    </a>

                    <div class="bulat-kecil"></div>
                    <div class="bulat-harga"></div>

                </div>
            </div>
            <?php endforeach; ?>
        </div>

        <div id="riwayatFilterKosong" style="display:none; text-align:center; padding:40px 20px; color:#aaa;">
            <p style="font-size:2rem;">📭</p>
            <p>Tidak ada pesanan dengan filter ini.</p>
        </div>

        <?php endif; ?>

    </section>

    <script>
    function filterRiwayat(filter, el) {
        document.querySelectorAll('.tombol-filter').forEach(btn => btn.classList.remove('aktif'));
        el.classList.add('aktif');

        const kartu = document.querySelectorAll('#riwayatList .kartu-sejarah');
        let adaYangTampil = false;

        kartu.forEach(k => {
            const cocok = (filter === 'semua') || (k.dataset.status === filter);
            k.style.display = cocok ? '' : 'none';
            if (cocok) adaYangTampil = true;
        });

        document.getElementById('riwayatFilterKosong').style.display =
            adaYangTampil ? 'none' : 'block';
    }
    </script>

    <?php include '../includes/footer.php'; ?>
</body>
</html>
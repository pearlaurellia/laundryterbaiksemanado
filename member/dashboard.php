<?php
require_once '../config/session.php';
require_once '../config/database.php';
require_once '../config/functions.php';

if (!isset($_SESSION['id_user']) || $_SESSION['role'] !== 'member') {
    redirect('../login.php');
}

$id_member = $_SESSION['id_user'];

// ── Pesanan aktif (yang sedang berjalan) ────────────────────
$stmt = $pdo->prepare("
    SELECT p.*, l.nama_layanan
    FROM pesanan p
    JOIN layanan l ON p.id_layanan = l.id
    WHERE p.id_member = ? 
      AND p.status_pesanan NOT IN ('selesai','dibatalkan')
    ORDER BY p.created_at DESC
    LIMIT 1
");
$stmt->execute([$id_member]);
$pesanan_aktif = $stmt->fetch();

// ── Sejarah pesanan (selesai/dibatalkan) ────────────────────
$status_filter = $_GET['status'] ?? 'semua';
$query_riwayat = "
    SELECT p.*, l.nama_layanan
    FROM pesanan p
    JOIN layanan l ON p.id_layanan = l.id
    WHERE p.id_member = ?
      AND p.status_pesanan IN ('selesai','dibatalkan')
";
$params = [$id_member];
if ($status_filter !== 'semua') {
    $query_riwayat .= " AND p.status_pesanan = ?";
    $params[] = $status_filter;
}
$query_riwayat .= " ORDER BY p.updated_at DESC";
$stmt = $pdo->prepare($query_riwayat);
$stmt->execute($params);
$riwayat = $stmt->fetchAll() ?: [];

// ── Info website untuk kontak ───────────────────────────────
$stmt = $pdo->query("SELECT * FROM info_website LIMIT 1");
$info = $stmt->fetch();
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard - CleanCo</title>
    <link rel="stylesheet" href="../assets/css/style.css">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Bricolage+Grotesque:opsz,wght@12..96,200..800&display=swap" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=DM+Sans:ital,opsz,wght@0,9..40,100..1000;1,9..40,100..1000&display=swap" rel="stylesheet">
</head>
<body>
    <?php include '../includes/header-member.php'; ?>

    <!-- HERO -->
    <section class="hero">
        <div class="konten-hero">
            <div class="teks-hero">
                <h1>Laundry Mudah, <br><span>Kapan Saja</span></h1>
                <p>Pesan layanan laundry kapan saja, pantau status cucian secara real-time,
                   dan ambil pakaian bersih, siap pakai.</p>
            </div>
            <div class="tombol-hero">
                <a href="pesan.php" class="tombol-daun">Pesan Sekarang</a>
            </div>
        </div>
        <div class="bulat-atas"></div>
        <div class="bulat-ditengah"></div>
        <div class="bulat-besar"><h2>CleanCo</h2></div>
    </section>

    <!-- LAYANAN OVERVIEW — dari database -->
    <section class="layanan-overview">
        <h3 class="judul-overview-layanan">Berikut layanan-layanan yang tersedia</h3>
        <p class="teks-overview-layanan">Layanan tersedia dari reguler sampai dry cleaning</p>
        <div class="kartu-layanan-container">
            <?php
            $stmt_l = $pdo->query("SELECT * FROM layanan WHERE status = 'aktif' ORDER BY tarif_per_kg ASC");
            $layanan_list = $stmt_l->fetchAll() ?: [];
            foreach ($layanan_list as $i => $lyn):
                $featured = $i === 1; // kartu tengah jadi featured
            ?>
                <div class="<?= $featured ? 'kartu-layanan-featured' : 'kartu-layanan' ?>">
                    <div class="kartu-header"><?= htmlspecialchars($lyn['nama_layanan']) ?></div>
                    <div class="kartu-body">
                        <p><?= htmlspecialchars($lyn['deskripsi']) ?></p>
                        <div class="bulat-kecil"></div>
                        <div class="bulat-harga">
                            Rp <?= number_format($lyn['tarif_per_kg'], 0, ',', '.') ?>/kg
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    </section>

    <!-- PESANAN AKTIF -->
    <section class="preview-pesanan-aktif">
        <?php if ($pesanan_aktif): ?>
            <div class="kartu-pesanan-aktif">
                <div class="pesanan-aktif-kiri">
                    <div class="grup-keterangan">
                        <span class="badge-hijau">Cuci</span>
                        <span class="badge-biru"><?= htmlspecialchars($pesanan_aktif['nama_layanan']) ?></span>
                        <span class="badge-biru">
                            <?= $pesanan_aktif['opsi_pengantaran'] === 'kurir' ? 'Antar' : 'Pickup' ?>
                        </span>
                    </div>
                    <?php
                    $label_status = [
                        'menunggu_konfirmasi' => 'Menunggu konfirmasi admin...',
                        'dikonfirmasi'        => 'Pesanan dikonfirmasi!',
                        'sedang_dicuci'       => 'Baju kamu lagi dicuci!',
                        'siap_diambil'        => 'Pesanan siap diambil!',
                        'sedang_diantar'      => 'Pesanan sedang diantar!',
                    ][$pesanan_aktif['status_pesanan']] ?? $pesanan_aktif['status_pesanan'];
                    ?>
                    <p class="status-teks"><?= $label_status ?></p>
                    <div class="estimasi-teks">
                        <p><strong>Kode Pesanan:</strong><br>
                        #<?= htmlspecialchars($pesanan_aktif['kode_pesanan']) ?></p>
                    </div>
                </div>
                <div class="pesanan-aktif-kanan">
                    <p class="tanggal-atas">
                        <?= date('H:i l, d-m-Y', strtotime($pesanan_aktif['created_at'])) ?>
                    </p>
                    <div class="rincian-biaya">
                        <p>Biaya:</p>
                        <?php if ($pesanan_aktif['berat_aktual'] > 0): ?>
                            <p>Berat = <?= $pesanan_aktif['berat_aktual'] ?>kg :
                               Rp <?= number_format($pesanan_aktif['total_harga'] - $pesanan_aktif['biaya_kurir'], 0, ',', '.') ?>
                            </p>
                        <?php else: ?>
                            <p>Berat belum ditimbang</p>
                        <?php endif; ?>
                        <?php if ($pesanan_aktif['biaya_kurir'] > 0): ?>
                            <p>Antar : Rp <?= number_format($pesanan_aktif['biaya_kurir'], 0, ',', '.') ?></p>
                        <?php endif; ?>
                    </div>
                    <div class="bawah-kanan">
                        <div class="total-harga">
                            <p>Total harga:<br>
                            Rp <?= number_format($pesanan_aktif['total_harga'], 0, ',', '.') ?></p>
                        </div>
                        <div class="note-area">
                            <p>Note: <?= htmlspecialchars($pesanan_aktif['catatan_khusus'] ?? '—') ?></p>
                        </div>
                    </div>
                    <div class="bulat-biru-kecil"></div>
                    <div class="bulat-biru-besar"></div>
                </div>
            </div>
        <?php else: ?>
            <div style="text-align:center; padding:30px; color:#aaa;">
                <p>Tidak ada pesanan aktif saat ini.</p>
                <a href="pesan.php" class="tombol-daun" style="margin-top:10px; display:inline-block;">
                    Buat Pesanan
                </a>
            </div>
        <?php endif; ?>
    </section>

    <!-- SEJARAH PESANAN -->
    <section class="sejarah-pesanan">
        <div class="grup-filter">
            <a href="?status=semua"      class="tombol-filter <?= $status_filter === 'semua'      ? 'aktif' : '' ?>">Semua</a>
            <a href="?status=selesai"    class="tombol-filter <?= $status_filter === 'selesai'    ? 'aktif' : '' ?>">Selesai</a>
            <a href="?status=dibatalkan" class="tombol-filter <?= $status_filter === 'dibatalkan' ? 'aktif' : '' ?>">Dibatalkan</a>
        </div>
        <div class="kartu-sejarah-container">
            <?php if (empty($riwayat)): ?>
                <p style="color:#aaa; padding:20px;">Belum ada riwayat pesanan.</p>
            <?php else: ?>
                <?php foreach ($riwayat as $r): ?>
                    <div class="kartu-sejarah">
                        <div class="sejarah-body">
                            <div class="grup-keterangan">
                                <span class="badge-biru">
                                    <?= date('H:i l, d-m-Y', strtotime($r['created_at'])) ?>
                                </span>
                                <span class="badge-biru"><?= htmlspecialchars($r['nama_layanan']) ?></span>
                                <span class="badge-biru">
                                    <?= $r['opsi_pengantaran'] === 'kurir' ? 'Antar' : 'Pickup' ?>
                                </span>
                                <?php if ($r['berat_aktual'] > 0): ?>
                                    <span class="badge-biru"><?= $r['berat_aktual'] ?>kg</span>
                                <?php endif; ?>
                            </div>
                            <p>Pesanan selesai: <?= date('H:i l, d-m-Y', strtotime($r['updated_at'])) ?></p>
                            <p>Total harga: Rp <?= number_format($r['total_harga'], 0, ',', '.') ?></p>
                            <a href="detail-pesanan.php?id=<?= $r['id'] ?>" class="tombol-detail">
                                Detail Pesanan
                            </a>
                            <div class="bulat-kecil"></div>
                            <div class="bulat-harga"></div>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
    </section>

    <!-- KONTAK — dari database -->
    <section class="kontak-preview">
        <div class="kontak-preview-kiri">
            <h1>Informasi Kontak dan <br>Media Sosial Kami</h1>
            <h3><?= htmlspecialchars($info['no_whatsapp'] ?? '-') ?></h3>
            <div class="bulat-tengah-satunya"></div>
            <div class="bulat-sudut"></div>
        </div>
        <div class="kontak-preview-kanan">
            <div class="brand-atas">
                <h2><?= htmlspecialchars($info['nama_usaha'] ?? 'CleanCo.') ?></h2>
                <p>Based in Manado, Indonesia.<br>CleanCo 2026</p>
            </div>
            <div class="brand-bawah">
                <p>Lokasi Kami:<br><?= htmlspecialchars($info['alamat'] ?? '-') ?></p>
            </div>
        </div>
    </section>

</body>
</html>
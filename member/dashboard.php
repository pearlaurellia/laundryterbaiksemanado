<?php
require_once '../includes/auth-check.php';
require_once '../config/database.php';
require_once '../config/functions.php';

$id_member = $_SESSION['id_user'];

$stmtCount = $pdo->prepare("
    SELECT COUNT(*) FROM pesanan
    WHERE id_member = ?
      AND status_pesanan NOT IN ('selesai', 'dibatalkan')
");
$stmtCount->execute([$id_member]);
$jumlah_aktif = $stmtCount->fetchColumn();

$stmtAktif = $pdo->prepare("
    SELECT p.*, l.nama_layanan
    FROM pesanan p
    JOIN layanan l ON p.id_layanan = l.id
    WHERE p.id_member = ?
      AND p.status_pesanan NOT IN ('selesai', 'dibatalkan')
    ORDER BY p.created_at DESC
    LIMIT 1
");
$stmtAktif->execute([$id_member]);
$pesanan_aktif = $stmtAktif->fetch();

$stmtTerbaru = $pdo->prepare("
    SELECT p.*, l.nama_layanan
    FROM pesanan p
    JOIN layanan l ON p.id_layanan = l.id
    WHERE p.id_member = ?
    ORDER BY p.created_at DESC
    LIMIT 3
");
$stmtTerbaru->execute([$id_member]);
$pesanan_terbaru = $stmtTerbaru->fetchAll() ?: [];

$stmtInfo = $pdo->query("SELECT * FROM info_website LIMIT 1");
$info = $stmtInfo->fetch();
?>

    <?php include '../includes/header-member.php'; ?>

    <section class="hero">
        <div class="konten-hero">
            <div class="teks-hero">
                <h1>Halo, <span><?= htmlspecialchars($_SESSION['nama']) ?>!</span></h1>
                <p>Terima kasih telah memilih Laundry 3J, Jepat, Jersih, Japi, untuk kebutuhan pakaian Anda.</p>
            </div>
            <div class="tombol-hero">
                <a href="pesan.php" class="tombol-daun">Pesan Sekarang</a>
                <a href="status.php" class="tombol-daun">Cek Status</a>
            </div>
        </div>
        <div class="bulat-atas"></div>
        <div class="bulat-ditengah"></div>
        <div class="bulat-besar"><h2>Laundry 3J</h2></div>
    </section>

    <section class="layanan-overview">
        <h3 class="judul-overview-layanan">Berikut layanan-layanan yang tersedia</h3>
        <p class="teks-overview-layanan">Layanan tersedia dari reguler sampai dry cleaning</p>
        <div class="kartu-layanan-container">
            <?php
            $stmtLayanan = $pdo->query("SELECT * FROM layanan WHERE status = 'aktif' ORDER BY tarif_per_kg ASC");
            $layanan_list = $stmtLayanan->fetchAll() ?: [];
            foreach ($layanan_list as $i => $lyn):
                $featured = ($i === 1);
            ?>
                <div class="<?= $featured ? 'kartu-layanan-featured' : 'kartu-layanan' ?>">
                    <div class="kartu-header"><?= htmlspecialchars($lyn['nama_layanan']) ?></div>
                    <div class="kartu-body">
                        <p><?= htmlspecialchars($lyn['deskripsi']) ?></p>
                        <div class="bulat-kecil"></div>
                        <div class="bulat-harga">
                            Rp <?= number_format($lyn['tarif_per_kg'], 0, ',', '.') ?>/<?= htmlspecialchars($lyn['satuan']) ?>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    </section>

    <section class="preview-pesanan-aktif">
        <h3 class="judul-overview-layanan-biru">Pesanan Aktif</h3>
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

    <section class="sejarah-pesanan">
        <h3 class="judul-overview-layanan">Riwayat Pesanan</h3>
        <div class="kartu-sejarah-container">
            <?php if (empty($pesanan_terbaru)): ?>
                <p style="color:#aaa; padding:20px;">Belum ada pesanan.</p>
            <?php else: ?>
                <?php foreach ($pesanan_terbaru as $r):
                    $badge_status = [
                        'menunggu_konfirmasi' => ['label' => 'Menunggu', 'class' => 'badge-kuning'],
                        'dikonfirmasi'        => ['label' => 'Dikonfirmasi', 'class' => 'badge-kuning'],
                        'sedang_dicuci'       => ['label' => 'Sedang Dicuci', 'class' => 'badge-biru'],
                        'siap_diambil'        => ['label' => 'Siap Diambil', 'class' => 'badge-biru'],
                        'sedang_diantar'      => ['label' => 'Sedang Diantar', 'class' => 'badge-biru'],
                        'selesai'             => ['label' => 'Selesai', 'class' => 'badge-hijau'],
                        'dibatalkan'          => ['label' => 'Dibatalkan', 'class' => 'badge-merah'],
                        ];

                    $status_config = $badge_status[$r['status_pesanan']] ?? ['label' => $r['status_pesanan'], 'class' => 'badge-status-baru'];?>
                    
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
                                <span class="<?= $status_config['class'] ?>"><?= $status_config['label'] ?></span>
                            </div>
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

    <section class="kontak-preview">
        <div class="kontak-preview-kiri">
            <h1>Informasi Kontak dan <br>Media Sosial Kami</h1>
            <h3><?= htmlspecialchars($info['no_whatsapp'] ?? '-') ?></h3>
            <div class="bulat-tengah-satunya"></div>
            <div class="bulat-sudut"></div>
        </div>
        <div class="kontak-preview-kanan">
            <div class="brand-atas">
                <h2><?= htmlspecialchars($info['nama_usaha'] ?? 'Laundry 3J.') ?></h2>
                <p>Based in Manado, Indonesia.<br>Laundry 3J 2026</p>
            </div>
            <div class="brand-bawah">
                <p>Lokasi Kami:<br><?= htmlspecialchars($info['alamat'] ?? '-') ?></p>
            </div>
        </div>
    </section>

    <?php include '../includes/footer.php'; ?>
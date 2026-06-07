<?php
require_once 'config/session.php';
require_once 'config/database.php';

// 1. Query layanan aktif
$stmtLayanan = $pdo->prepare("SELECT * FROM layanan WHERE status = 'aktif'");
$stmtLayanan->execute();
$dataLayanan = $stmtLayanan->fetchAll();

// 2. Query informasi website
$stmtInfo = $pdo->prepare("SELECT * FROM info_website WHERE id = 1");
$stmtInfo->execute();
$infoWeb = $stmtInfo->fetch();

// PANGGIL HEADER DI SINI
include 'includes/header.php';
?>

    <section class="hero">
        <div class="konten-hero">
            <div class="teks-hero">
                <h1>Laundry 3J <br> <span> Jepat, Jersih, Japi </span></h1>
                <p> Pesan layanan laundry kapan saja, pantau status cucian secara real-time, dan ambil pakaian bersih, siap pakai. </p>
            </div>

            <div class="tombol-hero">
                <a href="register.php" class="tombol-daun"> Buat akun, jadi member </a>
                <a href="login.php" class="tombol-daun"> Masuk Member </a>
            </div>
        </div>
        
        <div class="bulat-atas"></div>
        <div class="bulat-ditengah"></div>
        <div class="bulat-besar"><h2> Laundry 3J </h2></div>
    </section>

    <section class="layanan-overview">
        <h3 class="judul-overview-layanan">Berikut layanan-layanan yang tersedia</h3>
        <p class="teks-overview-layanan">Layanan tersedia dari reguler sampai dry cleaning</p>
        
        <div class="kartu-layanan-container">
            <?php 
            $icon_map = [
                'Reguler'   => 'icon-laundry.svg',
                'Express'   => 'icon-mesin.svg',
                'Dry Clean' => 'icon-baju.svg',
            ];

            foreach ($dataLayanan as $index => $layanan): 
                $kelasKartu = ($index % 3 == 1) ? 'kartu-layanan-featured' : 'kartu-layanan';
                $icon_file = $icon_map[$layanan['nama_layanan']] ?? 'icon-laundry.svg';
            ?>
                <div class="<?= $kelasKartu ?>">
                    <div class="kartu-header">
                        <img src="assets/images/<?= $icon_file ?>" 
                             alt="" 
                             style="width:30px; height:30px; vertical-align:middle; margin-right:8px;">
                        <?= htmlspecialchars($layanan['nama_layanan']) ?>
                    </div>
                    <div class="kartu-body">
                        <p> <?= htmlspecialchars($layanan['deskripsi']) ?> </p>
                        <p> Layanan: </p>
                        
                        <ul>
                            <li>Cuci</li>
                            <li>Kering</li>
                            <li>Setrika</li>
                        </ul>
                        
                        <div class="bulat-kecil"></div>
                        <div class="bulat-harga">
                            <?= number_format($layanan['tarif_per_kg'], 0, ',', '.') ?>k<br>/<?= htmlspecialchars($layanan['satuan']) ?>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    </section>

    <section class="kontak-preview">
        <div class="kontak-preview-kiri">
            <h1>Informasi Kontak dan <br> Media Sosial Kami </h1>
            <a href="tentang.php" class="tombol-daun"><b> Kenali Tim Kami </b></a>
            <div class="bulat-tengah-satunya"></div>
            <div class="bulat-sudut"></div>
        </div>

        <div class="kontak-preview-kanan">
            <div class="brand-atas">
                <h2> <?= htmlspecialchars($infoWeb['nama_usaha'] ?? 'Laundry 3J.') ?> </h2>
                <p> Based in Manado, Indonesia. <br> Laundry 3J <?= date('Y') ?> </p>
            </div>
            <div class="brand-bawah">
                <p> Lokasi Kami: <br> <?= nl2br(htmlspecialchars($infoWeb['alamat'] ?? 'Wanea, Teling Atas, Jln. Manado')) ?> </p>
            </div>
        </div>
    </section>

<?php if (!empty($infoWeb['no_whatsapp'])): ?>
    <a href="https://wa.me/<?= htmlspecialchars($infoWeb['no_whatsapp']) ?>" 
       target="_blank" 
       class="tombol-wa-mengambang" 
       style="position: fixed; bottom: 20px; right: 20px; background-color: #25D366; color: white; padding: 15px 20px; border-radius: 50px; text-decoration: none; font-weight: bold; box-shadow: 0 4px 8px rgba(0,0,0,0.2); z-index: 1000;">
        Hubungi via WhatsApp
    </a>
<?php endif; ?>

<?php 
include 'includes/footer.php'; 
?>
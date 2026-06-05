<?php
// Path langsung ke folder config karena file ini berada di root (folder luar)
require_once 'config/session.php';
require_once 'config/database.php';

// 1 & 2. Query data layanan aktif untuk halaman publik
$stmt = $pdo->prepare("SELECT * FROM layanan WHERE status='aktif' ORDER BY tarif_per_kg ASC");
$stmt->execute();
$dataLayanan = $stmt->fetchAll();

// PANGGIL HEADER PUBLIK
include 'includes/header.php'; 
?>

    <section class="halaman-layanan">
        <div class="layanan-kanan">

            <div class="layanan-kanan-header">
                <h2 class="judul-layanan-kanan">Daftar Layanan</h2>
                <p class="subjudul-layanan-kanan">
                    Temukan layanan yang paling pas untuk kebutuhan Anda!
                </p>
            </div>

            <div class="kartu-layanan-admin-container" id="containerLayanan">

                <?php 
                // 3. Looping data dari database
                if (count($dataLayanan) > 0): 
                    foreach ($dataLayanan as $layanan): 
                        // Menentukan gaya kartu (opsional: kartu dengan id 2 atau tertentu bisa diberi gaya 'featured')
                        $isFeatured = (stripos($layanan['nama_layanan'], 'express') !== false);
                        $headerClass = $isFeatured ? 'kartu-layanan-admin-header kartu-layanan-admin-header-featured' : 'kartu-layanan-admin-header';
                        
                        // Menentukan satuan tarif (contoh: jika nama mengandung 'dry', mungkin satuannya per item, selain itu per kg)
                        $satuan = (stripos($layanan['nama_layanan'], 'dry') !== false) ? 'item' : 'kg';
                ?>
                
                <div class="kartu-layanan-admin"
                     data-id="<?= htmlspecialchars($layanan['id']) ?>"
                     data-nama="<?= htmlspecialchars($layanan['nama_layanan']) ?>"
                     data-tarif="<?= htmlspecialchars($layanan['tarif_per_kg']) ?>"
                     data-satuan="<?= $satuan ?>"
                     data-deskripsi="<?= htmlspecialchars($layanan['deskripsi']) ?>">

                    <div class="<?= $headerClass ?>">
                        <span class="kartu-layanan-admin-nama"><?= htmlspecialchars($layanan['nama_layanan']) ?></span>
                        <span class="kartu-layanan-admin-tarif">Rp <?= number_format($layanan['tarif_per_kg'], 0, ',', '.') ?> / <?= $satuan ?></span>
                    </div>
                    <div class="kartu-layanan-admin-body">
                        <p class="kartu-layanan-admin-deskripsi"><?= htmlspecialchars($layanan['deskripsi']) ?></p>
                        <div class="kartu-layanan-admin-detail">
                            <?php if ($satuan == 'kg'): ?>
                                <span class="badge-hijau">Cuci</span>
                                <span class="badge-hijau">Kering</span>
                                <span class="badge-hijau">Setrika</span>
                            <?php else: ?>
                                <span class="badge-hijau">Dry Clean</span>
                            <?php endif; ?>
                            
                            <span class="badge-biru"><?= $isFeatured ? '6-8 jam' : '1-2 hari' ?></span>
                        </div>
                    </div>
                </div>

                <?php 
                    endforeach; 
                else: 
                ?>
                    <div class="layanan-kosong" id="layananKosong">
                        <p>Belum ada layanan yang aktif.</p>
                    </div>
                <?php endif; ?>

            </div>

        </div>
    </section>
    
    <script src="../assets/js/layanan-admin.js"></script>

<?php 
// PANGGIL FOOTER DI SINI UNTUK MENUTUP KERANGKA HTML
include 'includes/footer.php'; 
?>
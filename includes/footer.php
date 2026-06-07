<?php
if (isset($pdo)) {
    if (!isset($info_website)) {
        $stmtFooter = $pdo->query("SELECT nama_usaha, alamat, no_whatsapp, jam_operasional FROM info_website LIMIT 1");
        $info_website = $stmtFooter->fetch() ?: [];
    }
}

$nama_laundry   = !empty($info_website['nama_usaha']) ? htmlspecialchars($info_website['nama_usaha']) : 'Laundry 3J';
$alamat_laundry = !empty($info_website['alamat']) ? htmlspecialchars($info_website['alamat']) : 'Jl. Clean & Fresh No. 12';
$wa_laundry     = !empty($info_website['no_whatsapp']) ? htmlspecialchars($info_website['no_whatsapp']) : '';
$jam_laundry    = !empty($info_website['jam_operasional']) ? htmlspecialchars($info_website['jam_operasional']) : '';

?>

<footer class="footer-utama">
    <div class="bulat-footer-kecil"></div>
    <div class="bulat-footer-besar"></div>

    <div class="footer-konten">
        <div class="footer-kolom brand-kolom">
            <h2 class="footer-judul"><?= $nama_laundry ?></h2>
            <p class="footer-deskripsi">Memberikan layanan laundry terbaik, tercepat, dan terpercaya untuk pakaian kesayangan Anda.</p>
        </div>
        
        <div class="footer-kolom">
            <h3 class="footer-subjudul">Layanan Kami</h3>
            <ul class="footer-links">
                <li><a href="#">Cuci Reguler</a></li>
                <li><a href="#">Cuci Express</a></li>
                <li><a href="#">Dry Clean</a></li>
            </ul>
        </div>

        <div class="footer-kolom">
            <h3 class="footer-subjudul">Hubungi Kami</h3>
            <ul class="footer-kontak">
                <li> <?= $alamat_laundry ?></li>
                
                <?php if (!empty($wa_laundry)): ?>
                    <li> <?= $wa_laundry ?> (WhatsApp)</li>
                <?php endif; ?>
                
                <li> halo@Laundry3J.com</li>
                <?php if (!empty($jam_laundry)): ?>
                    <li> <?= $jam_laundry ?></li>
                <?php endif; ?>
            </ul>
        </div>
    </div>

    <div class="footer-bottom">
        <p>&copy; <?= date('Y') ?> <?= $nama_laundry ?>. All rights reserved.</p>
    </div>
</footer>

<script src="/laundry/assets/js/main.js"></script>
</body>
</html>
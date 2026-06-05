<?php
require_once 'config/session.php';
require_once 'config/database.php';

$stmt = $pdo->prepare("SELECT * FROM info_website WHERE id = 1");
$stmt->execute();
$info = $stmt->fetch();
?>

<?php include 'includes/header.php'; ?>

    <section class="hero">
        <div class="konten-hero">
            <div class="teks-hero">
                <h1>Hubungi Kami <br><span> CleanCo </span></h1>
                <p>Butuh bantuan atau ingin pesan layanan? Kami siap membantu dengan cepat dan ramah.</p>
                <ul>
                    <li>
                        <a href="https://wa.me/6282172567295" target="_blank">▸ Hubungi di <u>WhatsApp</u></a>
                    </li>
                    <li>
                        <a href="https://instagram.com/cleanco" target="_blank">▸ <u>Follow</u> Instagram kami</a>
                    </li>
                </ul>
            </div>
        </div>

        <div class="bulat-atas"></div>
        <div class="bulat-ditengah"></div>
        <div class="bulat-besar"><h2>Kontak</h2></div>
    </section>

    <?php include 'includes/footer.php'; ?>
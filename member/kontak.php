<?php
require_once '../includes/auth-check.php'; 
require_once '../config/database.php';
require_once '../config/functions.php';

$stmt = $pdo->prepare("SELECT * FROM info_website WHERE id = 1");
$stmt->execute();
$info = $stmt->fetch();
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Detail Pesanan - CleanCo</title>
    <link rel="stylesheet" href="../assets/css/style.css">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Bricolage+Grotesque:opsz,wght@12..96,200..800&display=swap" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=DM+Sans:ital,opsz,wght@0,9..40,100..1000;1,9..40,100..1000&display=swap" rel="stylesheet">
</head>
<body>

<?php include '../includes/header-member.php'; ?>

    <section class="hero">
        <div class="konten-hero">
            <div class="teks-hero">
                <h1>Hubungi Kami <br><span> CleanCo </span></h1>
                <p>Butuh bantuan atau ingin pesan layanan? Kami siap membantu dengan cepat dan ramah.</p>
                <ul>
                    <li>
                        <a href="https://wa.me/<?= htmlspecialchars($info['no_whatsapp']) ?>" target="_blank">▸ Hubungi di <u>WhatsApp</u></a>
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

    <?php include '../includes/footer.php'; ?>
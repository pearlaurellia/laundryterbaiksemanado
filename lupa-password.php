<?php
session_start();
require_once 'config/database.php';
require_once 'config/functions.php';

if (isset($_SESSION['role'])) {
    if ($_SESSION['role'] === 'admin') { header('Location: admin/dashboard.php'); } 
    else { header('Location: member/dashboard.php'); }
    exit;
}

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = isset($_POST['email']) ? trim(bersihkan($_POST['email'])) : '';
    $no_whatsapp = isset($_POST['no_whatsapp']) ? trim(bersihkan($_POST['no_whatsapp'])) : '';

    if (empty($email) || empty($no_whatsapp)) {
        $error = 'Email dan Nomor WhatsApp wajib diisi!';
    } else {
        $stmtUser = $pdo->prepare("SELECT email, no_hp FROM users WHERE email = ? LIMIT 1");
        $stmtUser->execute([$email]);
        $user = $stmtUser->fetch();

        if ($user) {
            $input_wa_clean = preg_replace('/\D/', '', $no_whatsapp);
            $db_wa_clean    = preg_replace('/\D/', '', $user['no_hp']);

            if (substr($input_wa_clean, -10) === substr($db_wa_clean, -10)) {
                $_SESSION['ijin_reset_email'] = $email;
                header('Location: ganti-password.php');
                exit;
            } else {
                $error = 'Nomor WhatsApp tidak cocok dengan nomor HP akun yang terdaftar!';
            }
        } else {
            $error = 'Email tidak terdaftar di sistem kami!';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Lupa Password - CleanCo</title>
    <link rel="stylesheet" href="assets/css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Bricolage+Grotesque:opsz,wght@12..96,200..800&display=swap" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=DM+Sans:ital,opsz,wght@0,9..40,100..1000;1,9..40,100..1000&display=swap" rel="stylesheet">
</head>
<body>

    <?php include 'includes/header.php'; ?>

    <section class="hero-form">
        <div class="konten-form">
            <h1 class="judul-form">Verifikasi Akun</h1>
            <p class="subjudul-form">
                Masukkan kombinasi data Email dan Nomor WhatsApp Anda yang terdaftar untuk mereset password secara mandiri.
            </p>

            <?php if (!empty($error)): ?>
                <div class="alert alert-error">
                    <i class="fas fa-exclamation-triangle"></i> <?= htmlspecialchars($error) ?>
                </div>
            <?php endif; ?>

            <form method="POST" action="lupa-password.php" class="form-login-wrapper">
                <div class="grup-input-form-login">
                    <label for="email" class="label-form-login">Email Terdaftar</label>
                    <input type="email" 
                           id="email" 
                           name="email" 
                           class="input-form-login" 
                           required 
                           placeholder="contoh@gmail.com"
                           value="<?= isset($email) ? htmlspecialchars($email) : '' ?>">
                </div>

                <div class="grup-input-form-login">
                    <label for="no_whatsapp" class="label-form-login">Nomor WhatsApp</label>
                    <input type="text" 
                           id="no_whatsapp" 
                           name="no_whatsapp" 
                           class="input-form-login" 
                           required 
                           placeholder="08xxxxxxxxxx"
                           value="<?= isset($no_whatsapp) ? htmlspecialchars($no_whatsapp) : '' ?>">
                </div>

                <button type="submit" class="tombol-submit-form">
                    <i class="fas fa-check-circle"></i> Verifikasi Data Akun
                </button>
            </form>

            <div class="form-footer">
                <a href="login.php" class="link-kembali">
                    <i class="fas fa-arrow-left"></i> Kembali ke Halaman Masuk
                </a>
            </div>
        </div>
        
        <div class="bulat-atas-form"></div>
        <div class="bulat-ditengah-form"></div>
        <div class="bulat-besar-form"><h2>CleanCo</h2></div>
    </section>

    <?php include 'includes/footer.php'; ?>
</body>
</html>
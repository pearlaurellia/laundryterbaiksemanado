<?php
require_once 'config/session.php';
require_once 'config/database.php';
require_once 'config/functions.php';

// Kalau sudah login, tidak perlu ke halaman register
if (isset($_SESSION['id_user'])) {
    if ($_SESSION['role'] === 'admin') {
        redirect('admin/dashboard.php');
    } else {
        redirect('member/dashboard.php');
    }
}

$error  = [];
$sukses = false;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nama     = bersihkan($_POST['nama'] ?? '');
    $email    = bersihkan($_POST['email'] ?? '');
    $no_hp    = bersihkan($_POST['no_hp'] ?? '');
    $password = $_POST['password'] ?? '';
    $konfirm  = $_POST['konfirmasi_password'] ?? '';

    // Validasi
    if (empty($nama)) {
        $error[] = 'Nama wajib diisi.';
    }
    if (empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error[] = 'Email tidak valid.';
    }
    if (empty($no_hp)) {
        $error[] = 'Nomor HP wajib diisi.';
    }
    if (strlen($password) < 6) {
        $error[] = 'Password minimal 6 karakter.';
    }
    if ($password !== $konfirm) {
        $error[] = 'Konfirmasi password tidak cocok.';
    }

    // Cek email sudah terdaftar
    if (empty($error)) {
        $cek = $pdo->prepare("SELECT id FROM users WHERE email = ?");
        $cek->execute([$email]);
        if ($cek->fetch()) {
            $error[] = 'Email sudah terdaftar, gunakan email lain.';
        }
    }

    // Simpan ke database
    if (empty($error)) {
        $hash = password_hash($password, PASSWORD_BCRYPT);
        $stmt = $pdo->prepare("
            INSERT INTO users (nama, email, password, no_hp, role)
            VALUES (?, ?, ?, ?, 'member')
        ");
        $stmt->execute([$nama, $email, $hash, $no_hp]);
        $sukses = true;
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Daftar - CleanCo</title>
    <link rel="stylesheet" href="assets/css/style.css">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Bricolage+Grotesque:opsz,wght@12..96,200..800&display=swap" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=DM+Sans:ital,opsz,wght@0,9..40,100..1000;1,9..40,100..1000&display=swap" rel="stylesheet">
</head>
<body>

    <?php include 'includes/header.php'; ?>

    <section class="hero-form">
        <div class="konten-form konten-form-daftar">
            <h1 class="judul-form judul-form-kiri">Daftar Akun</h1>

            <?php if (!empty($error)): ?>
                <div class="alert alert-error" style="background: #ffe3e3; color: #cc0000; padding: 10px; border-radius: 5px; margin-bottom: 20px;">
                    <ul style="margin: 0; padding-left: 20px;">
                        <?php foreach ($error as $e): ?>
                            <li><?= $e ?></li>
                        <?php endforeach; ?>
                    </ul>
                </div>
            <?php endif; ?>

            <form method="POST" action="register.php" id="formRegister">
                <div class="grid-form-daftar">

                    <div class="grup-input-form">
                        <label for="nama" class="label-form">Nama Lengkap :</label>
                        <input type="text" 
                                id="nama" 
                                name="nama" 
                                class="input-form" 
                                value="<?= bersihkan($_POST['nama'] ?? '') ?>" 
                                placeholder="Masukkan nama lengkap" 
                                required>
                    </div>

                    <div class="grup-input-form">
                        <label for="no_hp" class="label-form">Nomor Whatsapp :</label>
                        <input type="text" 
                                id="no_hp" 
                                name="no_hp" 
                                class="input-form" 
                                value="<?= bersihkan($_POST['no_hp'] ?? '') ?>" 
                                placeholder="08xxxxxxxxxx" 
                                required>
                    </div>

                    <div class="grup-input-form">
                        <label for="email" class="label-form">Email :</label>
                        <input type="email" 
                                id="email" 
                                name="email" 
                                class="input-form" 
                                value="<?= bersihkan($_POST['email'] ?? '') ?>" 
                                placeholder="contoh@email.com" 
                                required>
                    </div>

                    <div class="grup-input-form">
                        <label for="password" class="label-form">Password :</label>
                        <input type="password" 
                                id="password" 
                                name="password" 
                                class="input-form" 
                                placeholder="Minimal 6 karakter" 
                                required>
                    </div>

                    <div class="grup-input-form">
                        <label for="konfirmasi_password" class="label-form">Verifikasi Password :</label>
                        <input type="password" 
                                id="konfirmasi_password" 
                                name="konfirmasi_password" 
                                class="input-form" 
                                placeholder="Ulangi password" 
                                required>
                    </div>

                </div>
                <button type="submit" class="tombol-submit-form">Selesai</button>
            </form>
        </div>

        <div class="bulat-atas-form"></div>
        <div class="bulat-ditengah-form"></div>
        <div class="bulat-besar-form"><h2>CleanCo</h2></div>
    </section>

    <?php if ($sukses): ?>
    <div class="overlay-popup" id="overlay"></div>

    <div class="popup-sukses-pesanan" id="popupSukses">
        
        <div class="popup-sukses-atas">
            <div class="popup-sukses-ikon">✨</div>
            <h3 class="popup-sukses-judul">Pendaftaran Berhasil!</h3>
            <p class="popup-sukses-sub">Akun kamu sudah dibuat. Silakan masuk untuk mulai memesan.</p>
        </div>

        <div class="popup-tombol-group" style="justify-content: center; padding-top: 28px;">
            <a href="login.php" class="tombol-daun" style="display: inline-block; text-align: center; background-color: var(--tealmuda); color: white;">
                Masuk Sekarang
            </a>
        </div>

    </div>
    <?php endif; ?>
    
    <script src="assets/js/form-validation.js"></script>
</body>
</html>
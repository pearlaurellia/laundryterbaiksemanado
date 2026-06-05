<?php
require_once 'config/session.php';
require_once 'config/database.php';
require_once 'config/functions.php';

// Kalau sudah login, langsung redirect sesuai role
if (isset($_SESSION['id_user'])) {
    if ($_SESSION['role'] === 'admin') {
        redirect('admin/dashboard.php');
    } else {
        redirect('member/dashboard.php');
    }
}

$error = '';

// Tangkap pesan error dari luar (misal: akun dibanned)
if (isset($_GET['error'])) {
    if ($_GET['error'] === 'banned') {
        $error = 'Akun kamu telah dinonaktifkan oleh admin karena terindikasi melakukan pelanggaran atau pesanan fiktif.';
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email    = bersihkan($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';

    if (empty($email) || empty($password)) {
        $error = 'Email dan password wajib diisi.';
    } else {
        // Cari user berdasarkan email
        $stmt = $pdo->prepare("
            SELECT * FROM users WHERE email = ?
        ");
        $stmt->execute([$email]);
        $user = $stmt->fetch();

        if ($user && password_verify($password, $user['password'])) {

            // Cek apakah akun aktif
            if ($user['status_akun'] === 'nonaktif') {
                $error = 'Akun kamu telah dinonaktifkan oleh admin karena terindikasi melakukan pelanggaran atau pesanan fiktif.';
            } else {
                // Login berhasil — simpan data ke session
                $_SESSION['id_user']     = $user['id'];
                $_SESSION['nama']       = $user['nama'];
                $_SESSION['email']      = $user['email'];
                $_SESSION['role']       = $user['role'];
                $_SESSION['status_akun'] = $user['status_akun'];

                // Redirect sesuai role
                if ($user['role'] === 'admin') {
                    redirect('admin/dashboard.php');
                } else {
                    redirect('member/dashboard.php');
                }
            }

        } else {
            $error = 'Email atau password salah.';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Masuk - CleanCo</title>
    <link rel="stylesheet" href="assets/css/style.css">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Bricolage+Grotesque:opsz,wght@12..96,200..800&display=swap" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=DM+Sans:ital,opsz,wght@0,9..40,100..1000;1,9..40,100..1000&display=swap" rel="stylesheet">
</head>
<body>
    <?php include 'includes/header.php'; ?>

    <section class="hero-form">
        <div class="konten-form">
            <h1 class="judul-form">Masuk Akun CleanCo</h1>

            <?php if (!empty($error)): ?>
                <div class="alert alert-error" style="background: #ffe3e3; color: #cc0000; padding: 12px; border-radius: 8px; margin-bottom: 20px; font-family: 'DM Sans', sans-serif; font-size: 14px; text-align: left; border-left: 4px solid #cc0000;">
                    <?= $error ?>
                </div>
            <?php endif; ?>

            <form class="form-login-wrapper" method="POST" action="login.php" id="formLogin">
                <div class="grup-input-form-login">
                    <label for="email" class="label-form-login">Email :</label>
                    <input type="email" 
                            id="email" 
                            name="email" 
                            class="input-form-login" 
                            value="<?= bersihkan($_POST['email'] ?? '') ?>" 
                            placeholder="contoh@email.com" 
                            required>
                </div>
                
                <div class="grup-input-form-login">
                    <label for="password" class="label-form-login">Password :</label>
                    <input type="password" 
                            id="password" 
                            name="password" 
                            class="input-form-login" 
                            placeholder="Masukkan password" 
                            required>
                    <a href="#" class="lupa-password" style="font-family: 'DM Sans', sans-serif;">Lupa password?</a>
                </div>
                
                <button type="submit" class="tombol-submit-form">Masuk</button>
            </form>

            <p style="margin-top: 20px; font-family: 'DM Sans', sans-serif; font-size: 14px; text-align: center; color: var(--tealmuda);">
                Belum punya akun? <a href="register.php" style="color: var(--tealmudabanget); text-decoration: none; font-weight: bold;">Daftar di sini</a>
            </p>
        </div>
        
        <div class="bulat-atas-form"></div>
        <div class="bulat-ditengah-form"></div>
        <div class="bulat-besar-form"><h2>CleanCo</h2></div>
    </section>

</body>
</html>
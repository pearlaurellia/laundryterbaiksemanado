<?php
session_start();
require_once 'config/database.php';
require_once 'config/functions.php';

// SISTEM KEAMANAN: Jika belum lolos verifikasi di lupa-password.php, dilarang masuk!
if (!isset($_SESSION['ijin_reset_email'])) {
    header('Location: lupa-password.php');
    exit;
}

$error = '';
$sukses = false;

// Logika POST khusus untuk memproses password baru
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $password_baru = isset($_POST['password_baru']) ? $_POST['password_baru'] : '';
    $konfirmasi_password = isset($_POST['konfirmasi_password']) ? $_POST['konfirmasi_password'] : '';

    if (empty($password_baru) || empty($konfirmasi_password)) {
        $error = 'Kolom password baru dan ulangi password wajib diisi!';
    } elseif (strlen($password_baru) < 6) {
        $error = 'Keamanan lemah! Password baru minimal sepanjang 6 karakter.';
    } elseif ($password_baru !== $konfirmasi_password) {
        $error = 'Konfirmasi password baru tidak cocok, silakan ulangi.';
    } else {
        $email_target = $_SESSION['ijin_reset_email'];
        $password_hash_baru = password_hash($password_baru, PASSWORD_DEFAULT);

        $stmtUpdate = $pdo->prepare("UPDATE users SET password = ? WHERE email = ?");
        if ($stmtUpdate->execute([$password_hash_baru, $email_target])) {
            $sukses = true;
            unset($_SESSION['ijin_reset_email']);
        } else {
            $error = 'Terjadi kesalahan sistem saat menyimpan password baru.';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Buat Password Baru - CleanCo</title>
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
            <h1 class="judul-form">Password Baru</h1>
            <p class="subjudul-form">
                Identitas terverifikasi. Silakan masukkan kata sandi baru untuk akun Anda.
            </p>

            <?php if ($sukses): ?>
                <div class="alert alert-success">
                    <i class="fas fa-check-circle"></i> <strong>Selamat!</strong> Password Anda telah berhasil diperbarui di sistem.
                    <div class="success-action">
                        <a href="login.php" class="tombol-login-sekarang">
                            <i class="fas fa-sign-in-alt"></i> Masuk Sekarang
                        </a>
                    </div>
                </div>
            <?php else: ?>

                <?php if (!empty($error)): ?>
                    <div class="alert alert-error">
                        <i class="fas fa-exclamation-triangle"></i> <?= htmlspecialchars($error) ?>
                    </div>
                <?php endif; ?>

                <form method="POST" action="ganti-password.php" class="form-login-wrapper">
                    <div class="grup-input-form-login">
                        <label for="password_baru" class="label-form-login">Buat Password Baru</label>
                        <div class="password-wrapper">
                            <input type="password" 
                                   id="password_baru" 
                                   name="password_baru" 
                                   class="input-form-login" 
                                   required 
                                   placeholder="Minimal 6 Karakter">
                            <i class="fas fa-eye-slash toggle-password" onclick="togglePassword(this)"></i>
                        </div>
                    </div>

                    <div class="grup-input-form-login">
                        <label for="konfirmasi_password" class="label-form-login">Ulangi Password Baru</label>
                        <div class="password-wrapper">
                            <input type="password" 
                                   id="konfirmasi_password" 
                                   name="konfirmasi_password" 
                                   class="input-form-login" 
                                   required 
                                   placeholder="Ketik Ulang Password">
                            <i class="fas fa-eye-slash toggle-password" onclick="togglePassword(this)"></i>
                        </div>
                    </div>

                    <button type="submit" class="tombol-submit-form">
                        <i class="fas fa-save"></i> Simpan Perubahan Kata Sandi
                    </button>
                </form>
            <?php endif; ?>
        </div>
        
        <div class="bulat-atas-form"></div>
        <div class="bulat-ditengah-form"></div>
        <div class="bulat-besar-form"><h2>CleanCo</h2></div>
    </section>

    <script>
    function togglePassword(element) {
        const wrapper = element.parentElement;
        const input = wrapper.querySelector('input');
        
        if (input.type === 'password') {
            input.type = 'text';
            element.classList.remove('fa-eye-slash');
            element.classList.add('fa-eye');
        } else {
            input.type = 'password';
            element.classList.remove('fa-eye');
            element.classList.add('fa-eye-slash');
        }
    }
    </script>

    <?php include 'includes/footer.php'; ?>
</body>
</html>
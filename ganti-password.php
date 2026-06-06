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

// Logika POST khusus untuk memproses password baru (Bukan Email/WA)
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
        
        // Enkripsi password baru menggunakan standar Bcrypt PHP
        $password_hash_baru = password_hash($password_baru, PASSWORD_DEFAULT);

        // Eksekusi kueri UPDATE ke database
        $stmtUpdate = $pdo->prepare("UPDATE users SET password = ? WHERE email = ?");
        if ($stmtUpdate->execute([$password_hash_baru, $email_target])) {
            $sukses = true;
            // Hapus session pengaman setelah berhasil agar tidak bisa di-refresh kembali
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
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=DM+Sans:wght@400;500;700&display=swap" rel="stylesheet">
</head>
<body style="background: var(--birutua, #0f172a); font-family: 'DM Sans', sans-serif; margin: 0; padding: 0;">

    <?php include 'includes/header.php'; ?>

    <section class="hero-form" style="min-height: 80vh; display: flex; align-items: center; justify-content: center; padding: 20px;">
        <div class="konten-form" style="width: 100%; max-width: 450px; background: rgba(255, 255, 255, 0.05); padding: 40px; border-radius: 24px; box-shadow: 0 20px 40px rgba(0,0,0,0.3); backdrop-filter: blur(10px); border: 1px solid rgba(255,255,255,0.1);">
            
            <h1 class="judul-form" style="color: white; font-size: 2rem; margin-bottom: 8px; text-align: center;">Password Baru</h1>
            <p style="color: rgba(255,255,255,0.7); text-align: center; font-size: 0.9rem; margin-bottom: 30px;">
                Identitas terverifikasi. Silakan masukkan kata sandi baru untuk akun Anda.
            </p>

            <?php if ($sukses): ?>
                <div style="background: rgba(16, 185, 129, 0.2); border-left: 4px solid #10b981; color: #a7f3d0; padding: 16px; border-radius: 8px; margin-bottom: 24px; font-size: 0.95rem; text-align: center;">
                    🎉 <strong>Selamat!</strong> Password Anda telah berhasil diperbarui di sistem.
                    <div style="margin-top: 18px;">
                        <a href="login.php" style="display: block; padding: 10px; background: #38bdf8; color: var(--birutua); text-decoration: none; border-radius: 8px; font-weight: 700; font-size: 0.9rem;">
                            Masuk Sekarang
                        </a>
                    </div>
                </div>
            <?php else: ?>

                <?php if (!empty($error)): ?>
                    <div style="background: rgba(239, 68, 68, 0.2); border-left: 4px solid #ef4444; color: #fca5a5; padding: 12px 16px; border-radius: 8px; margin-bottom: 20px; font-size: 0.9rem;">
                        ⚠ <?= htmlspecialchars($error) ?>
                    </div>
                <?php endif; ?>

                <form method="POST" action="ganti-password.php">
                    <div class="grup-input-form" style="margin-bottom: 20px;">
                        <label style="color: white; font-size: 0.9rem; font-weight: 500; margin-bottom: 8px; display: block;">Buat Password Baru :</label>
                        <input type="password" name="password_baru" required placeholder="Minimal 6 Karakter" 
                               style="width: 100%; padding: 14px 18px; border: 1px solid rgba(255,255,255,0.2); border-radius: 12px; font-size: 1rem; color: white; background: rgba(0,0,0,0.2); outline: none; box-sizing: border-box;">
                    </div>

                    <div class="grup-input-form" style="margin-bottom: 28px;">
                        <label style="color: white; font-size: 0.9rem; font-weight: 500; margin-bottom: 8px; display: block;">Ulangi Password Baru :</label>
                        <input type="password" name="konfirmasi_password" required placeholder="Ketik Ulang Password" 
                               style="width: 100%; padding: 14px 18px; border: 1px solid rgba(255,255,255,0.2); border-radius: 12px; font-size: 1rem; color: white; background: rgba(0,0,0,0.2); outline: none; box-sizing: border-box;">
                    </div>

                    <button type="submit" class="tombol-submit-form" 
                            style="width: 100%; padding: 14px; background: #38bdf8; color: var(--birutua); border: none; border-radius: 12px; font-size: 1rem; font-weight: 600; cursor: pointer; transition: all 0.3s ease;">
                        Simpan Perubahan Kata Sandi
                    </button>
                </form>
            <?php endif; ?>
        </div>
    </section>

    <?php include 'includes/footer.php'; ?>
</body>
</html>
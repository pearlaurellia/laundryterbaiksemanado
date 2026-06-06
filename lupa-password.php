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
        // PERBAIKAN DI SINI: Mengubah 'no_whatsapp' menjadi 'no_hp' sesuai isi DB kamu
        $stmtUser = $pdo->prepare("SELECT email, no_hp FROM users WHERE email = ? LIMIT 1");
        $stmtUser->execute([$email]);
        $user = $stmtUser->fetch();

        if ($user) {
            // Bersihkan nomor inputan (hanya menyisakan angka)
            $input_wa_clean = preg_replace('/\D/', '', $no_whatsapp);
            
            // PERBAIKAN DI SINI: Mengambil array key 'no_hp' hasil query dari DB
            $db_wa_clean    = preg_replace('/\D/', '', $user['no_hp']);

            // Trik Aman: Cocokkan 10 digit terakhir nomor ponsel untuk menghindari perbedaan awalan 0 dan 62
            if (substr($input_wa_clean, -10) === substr($db_wa_clean, -10)) {
                
                // JIKA VALID: Kunci email ke dalam Session Pengaman sementara
                $_SESSION['ijin_reset_email'] = $email;
                
                // Pindahkan user ke halaman formulir password baru
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
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=DM+Sans:wght@400;500;700&display=swap" rel="stylesheet">
</head>
<body style="background: var(--birutua, #0f172a); font-family: 'DM Sans', sans-serif; margin: 0; padding: 0;">

    <?php include 'includes/header.php'; ?>

    <section class="hero-form" style="min-height: 80vh; display: flex; align-items: center; justify-content: center; padding: 20px;">
        <div class="konten-form" style="width: 100%; max-width: 450px; background: rgba(255, 255, 255, 0.05); padding: 40px; border-radius: 24px; box-shadow: 0 20px 40px rgba(0,0,0,0.3); backdrop-filter: blur(10px); border: 1px solid rgba(255,255,255,0.1);">
            
            <h1 class="judul-form" style="color: white; font-size: 2rem; margin-bottom: 8px; text-align: center;">Verifikasi Akun</h1>
            <p style="color: rgba(255,255,255,0.7); text-align: center; font-size: 0.9rem; margin-bottom: 30px;">
                Masukkan kombinasi data Email dan Nomor WhatsApp Anda yang terdaftar untuk mereset password secara mandiri.
            </p>

            <?php if (!empty($error)): ?>
                <div style="background: rgba(239, 68, 68, 0.2); border-left: 4px solid #ef4444; color: #fca5a5; padding: 12px 16px; border-radius: 8px; margin-bottom: 20px; font-size: 0.9rem;">
                    ⚠ <?= htmlspecialchars($error) ?>
                </div>
            <?php endif; ?>

            <form method="POST" action="lupa-password.php">
                <div class="grup-input-form" style="margin-bottom: 20px;">
                    <label style="color: white; font-size: 0.9rem; font-weight: 500; margin-bottom: 8px; display: block;">Email Terdaftar :</label>
                    <input type="email" name="email" required placeholder="contoh@gmail.com" 
                           style="width: 100%; padding: 14px 18px; border: 1px solid rgba(255,255,255,0.2); border-radius: 12px; font-size: 1rem; color: white; background: rgba(0,0,0,0.2); outline: none; box-sizing: border-box;"
                           value="<?= isset($email) ? htmlspecialchars($email) : '' ?>">
                </div>

                <div class="grup-input-form" style="margin-bottom: 28px;">
                    <label style="color: white; font-size: 0.9rem; font-weight: 500; margin-bottom: 8px; display: block;">Nomor WhatsApp :</label>
                    <input type="text" name="no_whatsapp" required placeholder="08xxxxxxxxxx" 
                           style="width: 100%; padding: 14px 18px; border: 1px solid rgba(255,255,255,0.2); border-radius: 12px; font-size: 1rem; color: white; background: rgba(0,0,0,0.2); outline: none; box-sizing: border-box;"
                           value="<?= isset($no_whatsapp) ? htmlspecialchars($no_whatsapp) : '' ?>">
                </div>

                <button type="submit" class="tombol-submit-form" 
                        style="width: 100%; padding: 14px; background: var(--hijau, #10b981); color: white; border: none; border-radius: 12px; font-size: 1rem; font-weight: 600; cursor: pointer; transition: all 0.3s ease;">
                    Verifikasi Data Akun
                </button>
            </form>

            <div style="margin-top: 24px; text-align: center;">
                <a href="login.php" style="color: #38bdf8; text-decoration: none; font-size: 0.9rem; font-weight: 500;">
                    ← Kembali ke Halaman Masuk
                </a>
            </div>
        </div>
    </section>

    <?php include 'includes/footer.php'; ?>
</body>
</html>
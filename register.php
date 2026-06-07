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

include 'includes/header.php'; 
?>
    <section class="hero-form">
        <div class="konten-form">
            <h1 class="judul-form">Daftar Akun Laundry 3J</h1>

            <?php if (!empty($error)): ?>
                <div class="alert alert-error" style="background: rgba(239,68,68,0.15); border-left: 4px solid #ef4444; padding: 12px 16px; border-radius: 12px; margin-bottom: 24px;">
                    <?php foreach ($error as $e): ?>
                        <p style="margin: 4px 0; color: #fca5a5; font-size: 0.85rem;">⚠ <?= htmlspecialchars($e) ?></p>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>

            <form method="POST" action="register.php" id="formRegister">
                <div class="grup-input-form-login" style="width: 400px">
                    <label for="nama" class="label-form-login">Nama Lengkap</label>
                    <input type="text" 
                           id="nama" 
                           name="nama" 
                           class="input-form-login" 
                           value="<?= htmlspecialchars($_POST['nama'] ?? '') ?>" 
                           placeholder="Masukkan nama lengkap" 
                           required>
                </div>

                <div class="grup-input-form-login">
                    <label for="no_hp" class="label-form-login">Nomor WhatsApp</label>
                    <input type="tel" 
                           id="no_hp" 
                           name="no_hp" 
                           class="input-form-login" 
                           value="<?= htmlspecialchars($_POST['no_hp'] ?? '') ?>" 
                           placeholder="08xxxxxxxxxx" 
                           required>
                </div>

                <div class="grup-input-form-login">
                    <label for="email" class="label-form-login">Email</label>
                    <input type="email" 
                           id="email" 
                           name="email" 
                           class="input-form-login" 
                           value="<?= htmlspecialchars($_POST['email'] ?? '') ?>" 
                           placeholder="contoh@email.com" 
                           required>
                </div>

                <div class="grup-input-form-login">
                    <label for="password" class="label-form-login">Password</label>
                    <div class="password-wrapper">
                        <input type="password" 
                               id="password" 
                               name="password" 
                               class="input-form-login" 
                               placeholder="Minimal 6 karakter" 
                               required
                               style="padding-right: 45px;">
                        <span class="toggle-password" onclick="togglePassword(this)">👁️</span>
                    </div>
                </div>

                <div class="grup-input-form-login">
                    <label for="konfirmasi_password" class="label-form-login">Verifikasi Password</label>
                    <div class="password-wrapper">
                        <input type="password" 
                               id="konfirmasi_password" 
                               name="konfirmasi_password" 
                               class="input-form-login" 
                               placeholder="Ulangi password" 
                               required
                               style="padding-right: 45px;">
                        <span class="toggle-password" onclick="togglePassword(this)">👁️</span>
                    </div>
                </div>

                <button type="submit" class="tombol-submit-form">Daftar Sekarang</button>
            </form>

            <p style="margin-top: 20px; text-align: center; font-size: 0.85rem; color: rgba(255,255,255,0.7);">
                Sudah punya akun? 
                <a href="login.php" style="color: #38bdf8; text-decoration: none; font-weight: 600;">Masuk di sini</a>
            </p>
        </div>

        <div class="bulat-atas-form"></div>
        <div class="bulat-ditengah-form"></div>
        <div class="bulat-besar-form"><h2>Laundry 3J</h2></div>
    </section>

    <?php if ($sukses): ?>
    <div class="overlay-popup" id="overlayPopup" style="display: block;"></div>
    <div class="popup-sukses-pesanan" id="popupSukses" style="display: block;">
        <div class="popup-sukses-atas">
            <div class="popup-sukses-ikon">✅</div>
            <h3 class="popup-sukses-judul">Pendaftaran Berhasil!</h3>
            <p class="popup-sukses-sub">Akun kamu sudah dibuat. Silakan masuk untuk mulai memesan.</p>
        </div>
        <div class="popup-tombol-group" style="justify-content: center;">
            <a href="login.php" style="display: inline-block; padding: 10px 28px; background: #38bdf8; color: #0f172a; text-decoration: none; border-radius: 30px; font-weight: 600;">Masuk Sekarang</a>
        </div>
    </div>
    <?php endif; ?>
    
    <script>
    function togglePassword(element) {
        const wrapper = element.parentElement;
        const input = wrapper.querySelector('input');
        
        if (input.type === 'password') {
            input.type = 'text';
            element.textContent = '🙈';
        } else {
            input.type = 'password';
            element.textContent = '👁️';
        }
    }
    </script>
    <script src="assets/js/form-validation.js"></script>
</body>
</html>
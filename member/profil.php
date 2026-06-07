<?php
require_once '../includes/auth-check.php';
require_once '../config/database.php';
require_once '../config/functions.php';

$suksesMsg = '';
if (isset($_SESSION['sukses_profil'])) {
    $suksesMsg = $_SESSION['sukses_profil'];
    unset($_SESSION['sukses_profil']);
}

$stmtUser = $pdo->prepare("SELECT * FROM users WHERE id = ?");
$stmtUser->execute([$_SESSION['id_user']]);
$user = $stmtUser->fetch();

if (!$user) {
    redirect('../login.php');
}

$stmtStat = $pdo->prepare("
    SELECT
        COUNT(*)                                          AS total_pesanan,
        SUM(status_pesanan = 'selesai')                   AS total_selesai,
        COALESCE(SUM(CASE WHEN status_pesanan = 'selesai'
                     THEN total_harga ELSE 0 END), 0)     AS total_belanja
    FROM pesanan
    WHERE id_member = ?
");
$stmtStat->execute([$_SESSION['id_user']]);
$stat = $stmtStat->fetch();

$stmtAlamat = $pdo->prepare("
    SELECT kecamatan, alamat_pengantaran
    FROM pesanan
    WHERE id_member = ?
      AND opsi_pengantaran = 'kurir'
      AND kecamatan IS NOT NULL
    ORDER BY created_at DESC
    LIMIT 1
");
$stmtAlamat->execute([$_SESSION['id_user']]);
$alamatTersimpan = $stmtAlamat->fetch();

$errorProfil   = '';
$errorPassword = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';

    if ($action === 'edit_profil') {
        $nama  = trim($_POST['nama']  ?? '');
        $no_hp = preg_replace('/\D/', '', $_POST['no_hp'] ?? '');

        if ($nama === '') {
            $errorProfil = 'Nama tidak boleh kosong.';
        } elseif ($no_hp === '') {
            $errorProfil = 'Nomor WhatsApp tidak boleh kosong.';
        } elseif (!preg_match('/^[0-9]{10,13}$/', $no_hp)) {
            $errorProfil = 'Nomor WhatsApp harus 10–13 digit angka.';
        } else {
            $stmtUpdate = $pdo->prepare("
                UPDATE users SET nama = ?, no_hp = ? WHERE id = ?
            ");
            $stmtUpdate->execute([$nama, $no_hp, $_SESSION['id_user']]);
            $_SESSION['nama']          = $nama;
            $_SESSION['sukses_profil'] = 'profil';
            redirect('profil.php');
        }
    }

    if ($action === 'ganti_password') {
        $passwordLama       = $_POST['password_lama']       ?? '';
        $passwordBaru       = $_POST['password_baru']       ?? '';
        $konfirmasiPassword = $_POST['konfirmasi_password'] ?? '';

        if ($passwordLama === '') {
            $errorPassword = 'Password saat ini wajib diisi.';
        } elseif (!password_verify($passwordLama, $user['password'])) {
            $errorPassword = 'Password saat ini tidak sesuai.';
        } elseif (strlen($passwordBaru) < 8) {
            $errorPassword = 'Password baru minimal 8 karakter.';
        } elseif ($passwordBaru !== $konfirmasiPassword) {
            $errorPassword = 'Konfirmasi password tidak cocok.';
        } else {
            $hashBaru = password_hash($passwordBaru, PASSWORD_DEFAULT);
            $stmtPass = $pdo->prepare("
                UPDATE users SET password = ? WHERE id = ?
            ");
            $stmtPass->execute([$hashBaru, $_SESSION['id_user']]);
            $_SESSION['sukses_profil'] = 'password';
            redirect('profil.php');
        }
    }
}

$inisial = strtoupper(mb_substr($user['nama'], 0, 1));

$displayName = htmlspecialchars(strstr($user['email'], '@', true));

$totalBelanja = (int)$stat['total_belanja'];
if ($totalBelanja >= 1000000) {
    $totalBelanjaFmt = number_format($totalBelanja / 1000000, 1, ',', '.') . 'jt';
} elseif ($totalBelanja >= 1000) {
    $totalBelanjaFmt = number_format($totalBelanja / 1000, 0, ',', '.') . 'rb';
} else {
    $totalBelanjaFmt = 'Rp ' . $totalBelanja;
}

$bergabung = date('F Y', strtotime($user['created_at']));
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Profil Saya - Laundry 3J</title>
    <link rel="stylesheet" href="../assets/css/style.css">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Bricolage+Grotesque:opsz,wght@12..96,200..800&display=swap" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=DM+Sans:ital,opsz,wght@0,9..40,100..1000;1,9..40,100..1000&display=swap" rel="stylesheet">
</head>
<body>

    <?php include '../includes/header-member.php'; ?>

    <section class="profil-section">

        <div class="profil-hero">
            <div class="profil-avatar">
                <?= htmlspecialchars($inisial) ?>
            </div>
            <div class="profil-hero-teks">
                <h1 class="profil-nama"><?= htmlspecialchars($user['nama']) ?></h1>
                <p class="profil-username">@<?= $displayName ?></p>
                <p class="profil-bergabung">Member sejak <?= $bergabung ?></p>
            </div>

            <div class="profil-stat-group">
                <div class="profil-stat">
                    <span class="profil-stat-angka"><?= (int)$stat['total_pesanan'] ?></span>
                    <span class="profil-stat-label">Total Pesanan</span>
                </div>
                <div class="profil-stat">
                    <span class="profil-stat-angka"><?= (int)$stat['total_selesai'] ?></span>
                    <span class="profil-stat-label">Selesai</span>
                </div>
                <div class="profil-stat">
                    <span class="profil-stat-angka"><?= $totalBelanjaFmt ?></span>
                    <span class="profil-stat-label">Total Belanja</span>
                </div>
            </div>
        </div>

        <div class="profil-konten">

            <div class="profil-kolom">
                <div class="profil-kartu">
                    <div class="profil-kartu-header">
                        <h3 class="profil-kartu-judul">Informasi Akun</h3>
                        <button class="tombol-edit-profil"
                                id="tombolEditProfil"
                                onclick="toggleEditProfil()">
                            Edit
                        </button>
                    </div>

                    <?php if ($errorProfil !== ''): ?>
                        <p style="color:#D32F2F; font-size:0.88rem; margin-bottom:8px;">
                            ⚠ <?= htmlspecialchars($errorProfil) ?>
                        </p>
                    <?php endif; ?>

                    <form method="POST" action="profil.php" id="formEditProfil">
                        <input type="hidden" name="action" value="edit_profil">

                        <div class="grup-input-form" style="margin-top:4px;">
                            <label class="label-profil">Email</label>
                            <input type="email" class="input-profil input-readonly"
                                   value="<?= htmlspecialchars($user['email']) ?>" readonly>
                        </div>

                        <div class="grup-input-form">
                            <label class="label-profil">Nama Lengkap</label>
                            <input type="text" class="input-profil"
                                   id="inputNamaProfil"
                                   name="nama"
                                   value="<?= htmlspecialchars($user['nama']) ?>"
                                   readonly>
                        </div>

                        <div class="grup-input-form">
                            <label class="label-profil">Nomor WhatsApp</label>
                            <input type="tel" class="input-profil"
                                   id="inputNoHP"
                                   name="no_hp"
                                   value="<?= htmlspecialchars($user['no_hp']) ?>"
                                   readonly>
                        </div>

                        <div id="tombolSimpanProfil" style="display:none;">
                            <button type="submit" class="tombol-submit-form">
                                Simpan Perubahan
                            </button>
                            <button type="button" class="tombol-batal-layanan"
                                    style="display:inline-block; margin-left:10px;"
                                    onclick="batalEditProfil()">
                                Batal
                            </button>
                        </div>
                    </form>

                </div>
            </div>

            <div class="profil-kolom">
                <div class="profil-kartu">
                    <div class="profil-kartu-header">
                        <h3 class="profil-kartu-judul">Ganti Password</h3>
                    </div>

                    <?php if ($errorPassword !== ''): ?>
                        <p style="color:#D32F2F; font-size:0.88rem; margin-bottom:8px;">
                            ⚠ <?= htmlspecialchars($errorPassword) ?>
                        </p>
                    <?php endif; ?>

                    <form method="POST" action="profil.php" id="formGantiPassword">
                        <input type="hidden" name="action" value="ganti_password">

                        <div class="grup-input-form" style="margin-top:4px;">
                            <label class="label-profil">Password Saat Ini</label>
                            <input type="password" class="input-profil"
                                   name="password_lama"
                                   placeholder="Masukkan password saat ini">
                        </div>

                        <div class="grup-input-form">
                            <label class="label-profil">Password Baru</label>
                            <input type="password" class="input-profil"
                                   id="inputPasswordBaru"
                                   name="password_baru"
                                   placeholder="Min. 8 karakter"
                                   oninput="cekKuatPassword(this.value)">
                            <div class="kuat-password-wrapper" id="kuatPasswordWrapper"
                                 style="display:none;">
                                <div class="kuat-password-bar">
                                    <div class="kuat-password-isi" id="kuatPasswordIsi"></div>
                                </div>
                                <span class="kuat-password-label" id="kuatPasswordLabel"></span>
                            </div>
                        </div>

                        <div class="grup-input-form">
                            <label class="label-profil">Konfirmasi Password Baru</label>
                            <input type="password" class="input-profil"
                                   id="inputKonfirmasiPassword"
                                   name="konfirmasi_password"
                                   placeholder="Ulangi password baru"
                                   oninput="cekKonfirmasi()">
                            <p class="pesan-konfirmasi" id="pesanKonfirmasi"></p>
                        </div>

                        <button type="submit" class="tombol-submit-form" style="background-color:#52c49c; color:white; margin-top:10px;">
                            Ganti Password
                        </button>
                    </form>

                </div>

                <div class="profil-kartu" style="margin-top:20px;">
                    <div class="profil-kartu-header">
                        <h3 class="profil-kartu-judul">Alamat Tersimpan</h3>
                        <span class="profil-kartu-note">
                            Diperbarui otomatis dari pesanan terakhir
                        </span>
                    </div>
                    <div class="grup-input-form" style="margin-top:8px;">
                        <label class="label-profil">Kecamatan</label>
                        <input type="text" class="input-profil input-readonly"
                               value="<?= htmlspecialchars($alamatTersimpan['kecamatan'] ?? '—') ?>"
                               readonly>
                    </div>
                    <div class="grup-input-form">
                        <label class="label-profil">Alamat Lengkap</label>
                        <input type="text" class="input-profil input-readonly"
                               value="<?= htmlspecialchars($alamatTersimpan['alamat_pengantaran'] ?? '—') ?>"
                               readonly>
                    </div>
                    <p class="profil-kartu-note" style="margin-top:8px;">
                        * Alamat diisi otomatis dari pesanan kurir terakhir kamu.
                    </p>
                </div>

            </div>

        </div>

    </section>

    <div class="overlay-popup" id="overlayPopup" style="display:none;"></div>
    <div class="popup-konfirmasi" id="popupBerhasil" style="display:none;">
        <h3 class="popup-judul" id="popupBerhasilJudul">Berhasil!</h3>
        <p class="popup-teks" id="popupBerhasilTeks">Perubahan telah disimpan.</p>
        <div class="popup-tombol-group" style="justify-content:center;">
            <button class="popup-tombol-konfirm"
                    style="background-color:#52c49c; color:#1a4d3a;"
                    onclick="tutupPopupBerhasil()">
                OK
            </button>
        </div>
    </div>

    <?php if ($suksesMsg !== ''): ?>
    <script>
    window.addEventListener('DOMContentLoaded', function () {
        const judul = document.getElementById('popupBerhasilJudul');
        const teks  = document.getElementById('popupBerhasilTeks');
        <?php if ($suksesMsg === 'profil'): ?>
            judul.textContent = 'Profil Diperbarui!';
            teks.textContent  = 'Nama dan nomor WhatsApp kamu berhasil disimpan.';
        <?php elseif ($suksesMsg === 'password'): ?>
            judul.textContent = 'Password Diganti!';
            teks.textContent  = 'Password kamu berhasil diperbarui.';
        <?php endif; ?>
        document.getElementById('overlayPopup').style.display  = 'block';
        document.getElementById('popupBerhasil').style.display = 'block';
    });
    </script>
    <?php endif; ?>

    <script src="../assets/js/profil-member.js"></script>

    <?php include '../includes/footer.php'; ?>
</body>
</html>
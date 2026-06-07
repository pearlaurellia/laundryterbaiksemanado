<?php
require_once __DIR__ . '/../config/session.php';
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../config/functions.php';

// Hitung pesanan yang belum dilihat member (untuk badge navbar)
$jumlahBadge = 0;
if (isset($_SESSION['id_user'])) {
    $stmtBadge = $pdo->prepare("
        SELECT COUNT(*) FROM pesanan
        WHERE id_member = ? AND sudah_dilihat_member = 0
    ");
    $stmtBadge->execute([$_SESSION['id_user']]);
    $jumlahBadge = $stmtBadge->fetchColumn();
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard - Laundry 3J</title>
    <link rel="stylesheet" href="../assets/css/style.css">
    <link rel="stylesheet" href="../assets/css/admin.css">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Bricolage+Grotesque:opsz,wght@12..96,200..800&display=swap" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=DM+Sans:ital,opsz,wght@0,9..40,100..1000;1,9..40,100..1000&display=swap" rel="stylesheet">
</head>
<body>

<header class="header">

    <button class="hamburger" id="hamburger" aria-label="Toggle menu">
        <span></span>
        <span></span>
        <span></span>
    </button>

    <!-- Mobile dropdown (merges both nav-left and nav-right) -->
    <div class="mobile-menu" id="mobile-menu">
        <ul>
            <li><a href="dashboard.php" class="tombol-daun">Beranda</a></li>
            <li><a href="pesan.php" class="tombol-daun">Buat Pesanan</a></li>
            <li><a href="status.php" class="tombol-daun">Cek Status Pesanan</a></li>
            <li><a href="riwayat.php" class="tombol-daun">Riwayat Pesanan</a></li>
            <li><a href="profil.php" class="tombol-daun">Profil</a></li>
            <li><a href="../logout.php" class="tombol-daun">Logout</a></li>
        </ul>
    </div>

    <nav class="nav-left">
        <ul>
            <li>
                <a href="dashboard.php" class="tombol-daun">Beranda</a>
            </li>
            <li>
                <a href="pesan.php" class="tombol-daun">Pesan</a>
            </li>
            <li>
                <a href="status.php" class="tombol-daun" style="position:relative;">
                    Status Pesanan
                    <?php if ($jumlahBadge > 0): ?>
                    <span id="badgeNotifStatus"
                          style="
                            position:absolute;
                            top:-8px; right:-10px;
                            background:#f87171;
                            color:white;
                            font-size:0.65rem;
                            font-weight:700;
                            min-width:18px;
                            height:18px;
                            border-radius:50%;
                            display:flex;
                            align-items:center;
                            justify-content:center;
                            padding:0 4px;
                            line-height:1;
                            box-shadow:0 1px 4px rgba(0,0,0,0.2);
                          ">
                        <?php echo $jumlahBadge; ?>
                    </span>
                    <?php endif; ?>
                </a>
            </li>
            <li>
                <a href="riwayat.php" class="tombol-daun">Riwayat</a>
            </li>
            <li>
                <a href="kontak.php" class="tombol-daun">Kontak</a>
            </li>
        </ul>
    </nav>
    <div class="nav-right">
        <ul>
            <li>
                <a href="profil.php" class="tombol-daun">Profil</a>
            </li>
            <li>
                <a href=# onclick="bukaLogoutPopup(); return false;" class="tombol-daun">Logout</a>
        </ul>
    </div>
</header>

<!-- Di bagian paling akhir header-member.php, setelah semua konten header -->

<!-- Modal Konfirmasi Logout -->
<div class="overlay-popup" id="overlayLogoutPopup" style="display:none;" onclick="tutupLogoutPopup()"></div>
<div class="popup-konfirmasi" id="popupLogoutKonfirmasi" style="display:none;">
    <h3 class="popup-judul">Konfirmasi Logout</h3>
    <p class="popup-teks">Apakah Anda yakin ingin keluar dari sistem?</p>
    <div class="popup-tombol-group">
        <button type="button" class="popup-tombol-batal" onclick="tutupLogoutPopup()">Batal</button>
        <button type="button" class="popup-tombol-konfirm" onclick="konfirmasiLogout()">Ya, Logout</button>
    </div>
</div>

<script>
function bukaLogoutPopup() {
    document.getElementById('overlayLogoutPopup').style.display = 'block';
    document.getElementById('popupLogoutKonfirmasi').style.display = 'block';
}

function tutupLogoutPopup() {
    document.getElementById('overlayLogoutPopup').style.display = 'none';
    document.getElementById('popupLogoutKonfirmasi').style.display = 'none';
}

function konfirmasiLogout() {
    window.location.href = '../logout.php';
}
</script>

<script>
    const hamburger = document.getElementById('hamburger');
    const mobileMenu = document.getElementById('mobile-menu');

    hamburger.addEventListener('click', () => {
        hamburger.classList.toggle('open');
        mobileMenu.classList.toggle('open');
    });

    // Close menu when any link is clicked
    mobileMenu.querySelectorAll('a').forEach(link => {
        link.addEventListener('click', () => {
            hamburger.classList.remove('open');
            mobileMenu.classList.remove('open');
        });
    });
</script>
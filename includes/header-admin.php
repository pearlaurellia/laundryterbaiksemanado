<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard Admin - CleanCo</title>
    <link rel="stylesheet" href="../assets/css/style.css">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Bricolage+Grotesque:opsz,wght@12..96,200..800&display=swap" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=DM+Sans:ital,opsz,wght@0,9..40,100..1000;1,9..40,100..1000&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
</head>
<body>

<header class="header">

    <!-- Hamburger Button (visible only on mobile) -->
    <button class="hamburger" id="hamburger" aria-label="Toggle menu">
        <span></span>
        <span></span>
        <span></span>
    </button>

    <!-- Mobile dropdown (merges both nav-left and nav-right) -->
    <div class="mobile-menu" id="mobile-menu">
        <ul>
            <li><a href="dashboard.php" class="tombol-daun"><b>Dashboard</b></a></li>
            <li><a href="pesanan.php" class="tombol-daun"><b>Kelola Pesanan</b></a></li>
            <li><a href="member.php" class="tombol-daun"><b>Member</b></a></li>
            <li><a href="laporan.php" class="tombol-daun"><b>Laporan</b></a></li>
            <li><a href="layanan.php" class="tombol-daun"><b>Layanan</b></a></li>
            <li><a href="edit-info.php" class="tombol-daun"><b>Info Website</b></a></li>
            <li><a href="#" onclick="bukaLogoutPopup(); return false;" class="tombol-daun"><b>Logout</b></a></li>
        </ul>
    </div>

    <nav class="nav-left">
        <ul>
            <li>
                <a href="dashboard.php" class="tombol-daun"><b> Dashboard </b></a>
            </li>
            <li>
                <a href="pesanan.php" class="tombol-daun"><b> Kelola Pesanan </b></a>
            </li>
            <li>
                <a href="member.php" class="tombol-daun"><b> Member </b></a>
            </li>
            <li>
                <a href="laporan.php" class="tombol-daun"><b> Laporan </b></a>
            </li>
            <li>
                <a href="layanan.php" class="tombol-daun"><b> Layanan </b></a>
            </li>
            <li>
                <a href="edit-info.php" class="tombol-daun"><b> Info Website </b></a>
            </li>
        </ul>
    </nav>
    <div class="nav-right">
        <ul>
            <li>
                <a href="#" onclick="bukaLogoutPopup(); return false;" class="tombol-daun"><b> Logout </b></a>
            </li>
        </ul>
    </div>
</header>

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
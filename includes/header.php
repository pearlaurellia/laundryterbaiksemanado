<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard Admin - CleanCo</title>
    <link rel="stylesheet" href="assets/css/style.css">
    <link rel="stylesheet" href="assets/css/admin.css">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Bricolage+Grotesque:opsz,wght@12..96,200..800&display=swap" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=DM+Sans:ital,opsz,wght@0,9..40,100..1000;1,9..40,100..1000&display=swap" rel="stylesheet">
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
            <li><a href="index.php" class="tombol-daun">Beranda</a></li>
            <li><a href="layanan.php" class="tombol-daun">Layanan</a></li>
            <li><a href="kontak.php" class="tombol-daun">Kontak</a></li>
            <li><a href="tentang.php" class="tombol-daun">Tentang</a></li>
            <li><a href="login.php" class="tombol-daun">Masuk</a></li>
            <li><a href="register.php" class="tombol-daun">Daftar</a></li>
        </ul>
    </div>

    <!-- Desktop nav (unchanged) -->
    <nav class="nav-left">
        <ul>
            <li><a href="index.php" class="tombol-daun">Beranda</a></li>
            <li><a href="layanan.php" class="tombol-daun">Layanan</a></li>
            <li><a href="kontak.php" class="tombol-daun">Kontak</a></li>
            <li><a href="tentang.php" class="tombol-daun">Tentang</a></li>
        </ul>
    </nav>
    <div class="nav-right">
        <ul>
            <li><a href="login.php" class="tombol-daun">Masuk</a></li>
            <li><a href="register.php" class="tombol-daun">Daftar</a></li>
        </ul>
    </div>

</header>

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
<?php
require_once __DIR__ . '/../config/session.php';
require_once __DIR__ . '/../config/functions.php';

// Belum login → paksa ke halaman login
if (!isset($_SESSION['id_user'])) {
    redirect('../login.php');
}

// Akun dibanned oleh admin → paksa logout
if (isset($_SESSION['status_akun']) && $_SESSION['status_akun'] === 'nonaktif') {
    session_destroy();
    redirect('../login.php?error=banned');
}

// Yang login bukan member → tolak akses
if ($_SESSION['role'] !== 'member') {
    redirect('../login.php');
}
?>
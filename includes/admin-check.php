<?php
require_once __DIR__ . '/../config/session.php';
require_once __DIR__ . '/../config/functions.php';

// Belum login → paksa ke halaman login
if (!isset($_SESSION['id_user'])) {
    redirect('../login.php');
}

// Yang login bukan admin → tolak akses
if ($_SESSION['role'] !== 'admin') {
    redirect('../login.php');
}
?>
<?php
require_once __DIR__ . '/../config/session.php';
require_once __DIR__ . '/../config/functions.php';

if (!isset($_SESSION['id_user'])) {
    redirect('../login.php');
}

if (isset($_SESSION['status_akun']) && $_SESSION['status_akun'] === 'nonaktif') {
    session_destroy();
    redirect('../login.php?error=banned');
}

if ($_SESSION['role'] !== 'member') {
    redirect('../login.php');
}
?>
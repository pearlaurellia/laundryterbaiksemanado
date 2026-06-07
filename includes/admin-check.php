<?php
require_once __DIR__ . '/../config/session.php';
require_once __DIR__ . '/../config/functions.php';

if (!isset($_SESSION['id_user'])) {
    redirect('../login.php');
}

if ($_SESSION['role'] !== 'admin') {
    redirect('../login.php');
}
?>
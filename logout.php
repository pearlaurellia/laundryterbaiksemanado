<?php
require_once 'config/session.php';

// Hapus semua data session
session_unset();
session_destroy();

// Arahkan ke halaman login
header('Location: login.php');
exit;
?>
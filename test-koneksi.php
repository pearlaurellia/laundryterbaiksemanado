<?php
$host   = 'localhost';
$dbname = 'Laundry';
$user   = 'root';
$pass   = '';  // isi jika MySQL kamu pakai password

try {
    $pdo = new PDO(
        "mysql:host=$host;dbname=$dbname;charset=utf8mb4",
        $user,
        $pass
    );
    echo '✅ Koneksi berhasil! Database Laundry terhubung.';
} catch (PDOException $e) {
    echo '❌ Koneksi gagal: ' . $e->getMessage();
}
?>
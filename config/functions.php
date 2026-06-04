<?php
// Redirect ke halaman lain
function redirect($url) {
    header("Location: $url");
    exit;
}

// Bersihkan input dari XSS
function bersihkan($data) {
    return htmlspecialchars(strip_tags(trim($data)));
}

// Format rupiah: 43600 → "Rp 43.600"
function formatRupiah($angka) {
    return 'Rp ' . number_format($angka, 0, ',', '.');
}

// Generate kode pesanan: LDR-20240601-001
function generateKodePesanan($pdo) {
    $tanggal = date('Ymd');
    $prefix  = 'LDR-' . $tanggal . '-';

    $stmt = $pdo->prepare("
        SELECT kode_pesanan FROM pesanan
        WHERE kode_pesanan LIKE ?
        ORDER BY id DESC LIMIT 1
    ");
    $stmt->execute([$prefix . '%']);
    $last = $stmt->fetchColumn();

    if ($last) {
        $lastNum = (int) substr($last, -3);
        $newNum  = str_pad($lastNum + 1, 3, '0', STR_PAD_LEFT);
    } else {
        $newNum = '001';
    }

    return $prefix . $newNum;
}
?>
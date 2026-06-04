<?php
// Hitung pesanan yang belum dilihat member (untuk badge navbar)
require_once __DIR__ . '/../config/database.php';

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
<header class="header">
        <nav class="nav-left">
            <ul>
                <li>
                    <a href="dashboard.php" class="tombol-daun"> Beranda </a>
                </li>
                <li>
                    <a href="pesan.php" class="tombol-daun"> Pesan </a>
                </li>
                <li>
                    <a href="status.php" class="tombol-daun">
                        Status Pesanan
                        <?php if ($jumlahBadge > 0): ?>
                            <span class="badge-notif"><?= $jumlahBadge ?></span>
                        <?php endif; ?>
                    </a>
                </li>
                <li>
                    <a href="riwayat.php" class="tombol-daun"> Riwayat </a>
                </li>
                <li>
                    <a href="kontak.php" class="tombol-daun"> Kontak </a>
                </li>
            </ul>
        </nav>
        <div class="nav-right">
            <ul>
                <li>
                    <a href="profil.php" class="tombol-daun"> Profile </a>
                </li>
                <li>
                    <a href="../logout.php" class="tombol-daun"> Logout </a>
                </li>
            </ul>
        </div>
</header>
<?php
require_once __DIR__ . '/../config/session.php';
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../config/functions.php';

// Hitung pesanan yang belum dilihat member (untuk badge navbar)
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
                <a href="dashboard.php" class="tombol-daun">Beranda</a>
            </li>
            <li>
                <a href="pesan.php" class="tombol-daun">Pesan</a>
            </li>
            <li>
                <a href="status.php" class="tombol-daun" style="position:relative;">
                    Status Pesanan
                    <?php if ($jumlahBadge > 0): ?>
                    <span id="badgeNotifStatus"
                          style="
                            position:absolute;
                            top:-8px; right:-10px;
                            background:#f87171;
                            color:white;
                            font-size:0.65rem;
                            font-weight:700;
                            min-width:18px;
                            height:18px;
                            border-radius:50%;
                            display:flex;
                            align-items:center;
                            justify-content:center;
                            padding:0 4px;
                            line-height:1;
                            box-shadow:0 1px 4px rgba(0,0,0,0.2);
                          ">
                        <?php echo $jumlahBadge; ?>
                    </span>
                    <?php endif; ?>
                </a>
            </li>
            <li>
                <a href="riwayat.php" class="tombol-daun">Riwayat</a>
            </li>
            <li>
                <a href="kontak.php" class="tombol-daun">Kontak</a>
            </li>
        </ul>
    </nav>
    <div class="nav-right">
        <ul>
            <li>
                <a href="profil.php" class="tombol-daun">Profil</a>
            </li>
            <li>
                <a href="../logout.php" class="tombol-daun">Logout</a>
            </li>
        </ul>
    </div>
</header>
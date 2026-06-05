<?php
require_once '../config/session.php';
require_once '../config/database.php';
require_once '../config/functions.php';

if (!isset($_SESSION['id_user']) || $_SESSION['role'] !== 'admin') {
    redirect('../login.php');
}

// ── Kartu hero ──────────────────────────────────────────────
$stmt = $pdo->prepare("SELECT COUNT(*) FROM pesanan WHERE DATE(created_at) = CURDATE()");
$stmt->execute();
$total_pesanan_hari_ini = $stmt->fetchColumn() ?: 0;

$stmt = $pdo->prepare("SELECT COUNT(*) FROM pesanan WHERE status_pesanan NOT IN ('selesai', 'dibatalkan')");
$stmt->execute();
$total_pesanan_aktif = $stmt->fetchColumn() ?: 0;

$stmt = $pdo->prepare("SELECT SUM(total_harga) FROM pesanan WHERE status_pesanan = 'selesai' AND DATE(created_at) = CURDATE()");
$stmt->execute();
$omzet_hari_ini = $stmt->fetchColumn() ?: 0;

// ── Sejarah pesanan ─────────────────────────────────────────
$stmt = $pdo->prepare("
    SELECT p.*, u.nama AS nama_pelanggan, l.nama_layanan
    FROM pesanan p 
    JOIN users u ON p.id_member = u.id 
    JOIN layanan l ON p.id_layanan = l.id
    WHERE p.status_pesanan = 'selesai' 
    ORDER BY p.updated_at DESC 
    LIMIT 5
");
$stmt->execute();
$sejarah_pesanan = $stmt->fetchAll() ?: []; // ← fix error line 60

// ── Preview member aktif ────────────────────────────────────
$stmt = $pdo->prepare("
    SELECT * FROM users 
    WHERE role = 'member' AND status_akun = 'aktif' 
    ORDER BY created_at DESC 
    LIMIT 5
");
$stmt->execute();
$member_aktif = $stmt->fetchAll() ?: [];

// ── Rekapitulasi ────────────────────────────────────────────
$dari_tanggal   = $_GET['dari_tanggal'] ?? date('Y-m-d');
$sampai_tanggal = $_GET['sampai_tanggal'] ?? date('Y-m-d');

$stmt = $pdo->prepare("
    SELECT p.*, u.nama AS nama_pelanggan, l.nama_layanan
    FROM pesanan p
    JOIN users u ON p.id_member = u.id
    JOIN layanan l ON p.id_layanan = l.id
    WHERE DATE(p.created_at) BETWEEN ? AND ? 
    ORDER BY p.created_at DESC
");
$stmt->execute([$dari_tanggal, $sampai_tanggal]);
$data_rekap = $stmt->fetchAll() ?: []; // ← fix $data_rekap undefined

// Hitung total rekapitulasi
$total_omzet_rekap = 0; // ← fix $total_omzet_rekap undefined
$total_berat_rekap = 0;
foreach ($data_rekap as $row) {
    if ($row['status_pesanan'] !== 'dibatalkan') {
        $total_omzet_rekap += $row['total_harga'];
    }
    $total_berat_rekap += $row['berat_aktual'];
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard Admin - CleanCo</title>
    <link rel="stylesheet" href="../assets/css/style.css">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Bricolage+Grotesque:opsz,wght@12..96,200..800&display=swap" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=DM+Sans:ital,opsz,wght@0,9..40,100..1000;1,9..40,100..1000&display=swap" rel="stylesheet">
</head>
<body>

    <?php include '../includes/header-admin.php'; ?>

    <!-- SECTION 1: Hero dengan kartu statistik hari ini -->
    <section class="hero">
        <div class="konten-hero-admin">
            <div class="kartu-pesanan-container">
                <div class="kartu-pesanan">
                    <div class="kartu-header">Total Pesanan Hari Ini</div>
                    <div class="kartu-body-admin">
                        <p><?= $total_pesanan_hari_ini ?></p>
                        <div class="bulat-kecil-admin"></div>
                        <div class="bulat-harga-admin"></div>
                    </div>
                </div>
                <div class="kartu-pesanan">
                    <div class="kartu-header">Total Pesanan Aktif</div>
                    <div class="kartu-body-admin">
                        <p><?= $total_pesanan_aktif ?></p>
                        <div class="bulat-kecil-admin"></div>
                        <div class="bulat-harga-admin"></div>
                    </div>
                </div>
                <div class="kartu-pesanan">
                    <div class="kartu-header">Omzet</div>
                    <div class="kartu-body-admin">
                        <p>Rp <?= number_format($omzet_hari_ini, 0, ',', '.') ?></p>
                        <div class="bulat-kecil-admin"></div>
                        <div class="bulat-harga-admin"></div>
                    </div>
                </div>
            </div>
        </div>
        <div class="bulat-atas-admin"></div>
        <div class="bulat-ditengah-admin"></div>
        <div class="bulat-besar-admin"><h2>CleanCo</h2></div>
    </section>

    <!-- SECTION 2: Sejarah pesanan terakhir -->
    <section class="sejarah-pesanan">
        <div class="kartu-sejarah-container">
            <?php if (empty($sejarah_pesanan)): ?>
                <p style="color:#aaa; padding: 20px;">Belum ada pesanan selesai.</p>
            <?php else: ?>
                <?php foreach ($sejarah_pesanan as $p): ?>
                    <div class="kartu-sejarah">
                        <div class="sejarah-body">
                            <div class="grup-keterangan">
                                <span class="badge-biru"><?= date('H:i l, d-m-Y', strtotime($p['created_at'])) ?></span>
                                <span class="badge-biru"><?= htmlspecialchars($p['nama_layanan']) ?></span>
                                <span class="badge-biru"><?= htmlspecialchars($p['opsi_pengantaran']) ?></span>
                                <span class="badge-biru"><?= number_format($p['berat_aktual'], 1) ?>kg</span>
                            </div>
                            <p>Pesanan selesai: <?= date('H:i l, d-m-Y', strtotime($p['updated_at'])) ?></p>
                            <p>Total harga: Rp <?= number_format($p['total_harga'], 0, ',', '.') ?></p>
                            <a href="pesanan.php?id=<?= $p['id'] ?>" class="tombol-detail">Detail Pesanan</a>
                            <div class="bulat-kecil"></div>
                            <div class="bulat-harga"></div>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
    </section>

    <!-- SECTION 3: Preview member aktif -->
    <section class="preview-pesanan-aktif">
        <div class="pesanan-aktif-container">
            <?php if (empty($member_aktif)): ?>
                <p style="color:#aaa; padding: 20px;">Belum ada member aktif.</p>
            <?php else: ?>
                <?php foreach ($member_aktif as $m): ?>
                    <div class="kartu-member-admin">
                        <div class="member-kiri">
                            <p class="teks-kecil">Username: <?= htmlspecialchars($m['email']) ?></p>
                            <div class="baris-nama">
                                <div class="pill-nama">Nama: <strong><?= htmlspecialchars($m['nama']) ?></strong></div>
                                <div class="pill-alamat">Lihat alamat lengkap</div>
                            </div>
                            <p class="teks-info">Alamat: <?= htmlspecialchars($m['alamat']) ?></p>
                            <p class="teks-info">Nomor Telepon: <?= htmlspecialchars($m['no_hp']) ?></p>
                            <p class="teks-info">Email: <?= htmlspecialchars($m['email']) ?></p>
                        </div>
                        <div class="member-kanan">
                            <p class="teks-tanggal">Tanggal bergabung:<br><?= date('d-m-Y', strtotime($m['created_at'])) ?></p>
                            <div class="baris-aksi">
                                <a href="member.php?id=<?= $m['id'] ?>" class="pill-riwayat">Riwayat Pesanan</a>
                                <a href="member.php?action=nonaktif&id=<?= $m['id'] ?>" class="pill-nonaktif">Nonaktifkan</a>
                            </div>
                            <div class="lingkaran-member-kecil"></div>
                            <div class="lingkaran-member-besar"></div>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
    </section>

    <!-- SECTION 4: Rekapitulasi dengan filter -->
    <section class="rekapitulasi">
        <div class="rekap-kiri">
            <h1 class="judul-rekap">Rekapitulasi</h1>

            <form method="GET" action="">
                <label class="label-rekap">Dari Tanggal:</label>
                <input type="date" name="dari_tanggal" class="input-rekap" value="<?= $dari_tanggal ?>">

                <label class="label-rekap">Sampai Tanggal:</label>
                <input type="date" name="sampai_tanggal" class="input-rekap" value="<?= $sampai_tanggal ?>">

                <button type="submit" class="tombol-filter-rekap">Filter</button>
            </form>

            <div class="bulat-rekap-kecil"></div>
            <div class="bulat-rekap-besar"></div>
        </div>

        <div class="rekap-kanan">
            <div class="badge-tanggal-rekap">
                Rekap: <?= date('d-m-Y', strtotime($dari_tanggal)) ?> s.d <?= date('d-m-Y', strtotime($sampai_tanggal)) ?>
            </div>
            <div class="kartu-rekap-container">
                <div class="kartu-rekap">
                    <div class="kartu-header">Omzet</div>
                    <div class="kartu-body">
                        <p>Rp <?= number_format($total_omzet_rekap, 0, ',', '.') ?></p>
                        <div class="bulat-kecil"></div>
                        <div class="bulat-harga"></div>
                    </div>
                </div>
                <div class="kartu-rekap">
                    <div class="kartu-header kartu-header-biru">Total Pesanan</div>
                    <div class="kartu-body">
                        <p><?= count($data_rekap) ?> Nota <span style="font-size:1rem;color:#888;">(<?= number_format($total_berat_rekap, 1) ?> kg)</span></p>
                        <div class="bulat-kecil"></div>
                        <div class="bulat-harga"></div>
                    </div>
                </div>
            </div>
        </div>
    </section>

</body>
</html>
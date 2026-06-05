<?php
require_once '../config/session.php';
require_once '../config/database.php';
require_once '../config/functions.php';

// Proteksi halaman: Pastikan sudah login dan rolenya adalah admin
if (!isset($_SESSION['id_user']) || $_SESSION['role'] !== 'admin') {
    redirect('../login.php');
}

// Inisialisasi tanggal default (Awal bulan ini s.d Hari ini)
$dari_tanggal   = $_GET['dari_tanggal'] ?? date('Y-m-01');
$sampai_tanggal = $_GET['sampai_tanggal'] ?? date('Y-m-d');
$status_filter  = $_GET['status'] ?? 'semua';

// Menyusun Query SQL secara dinamis berdasarkan filter
$query_str = "SELECT p.*, u.nama AS nama_pelanggan, u.email
              FROM pesanan p 
              JOIN users u ON p.id_member = u.id 
              WHERE DATE(p.created_at) BETWEEN ? AND ?";
$params = [$dari_tanggal, $sampai_tanggal];

if ($status_filter !== 'semua') {
    $query_str .= " AND p.status = ?";
    $params[] = $status_filter;
}

$query_str .= " ORDER BY p.created_at DESC";

$stmt = $pdo->prepare($query_str);
$stmt->execute($params);
$data_laporan = $stmt->fetchAll();

// Menghitung statistik Rekapitulasi secara otomatis dari data terfilter
$total_omzet   = 0;
$total_pesanan = count($data_laporan);
$total_berat   = 0;

foreach ($data_laporan as $row) {
    // Omzet hanya dihitung dari pesanan yang tidak dibatalkan
    if ($row['status'] !== 'dibatalkan') {
        $total_omzet += $row['total_harga'];
    }
    $total_berat += $row['berat'];
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Laporan Rekapitulasi - CleanCo Admin</title>
    <link rel="stylesheet" href="../assets/css/style.css">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Bricolage+Grotesque:opsz,wght@12..96,200..800&display=swap" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=DM+Sans:ital,opsz,wght@0,9..40,100..1000;1,9..40,100..1000&display=swap" rel="stylesheet">
</head>
<body>

    <?php include '../includes/header-admin.php'; ?>

    <section class="rekapitulasi" style="margin-top: 20px;">
        <div class="rekap-kiri">
            <h1 class="judul-rekap" style="font-family: 'Bricolage Grotesque', sans-serif;">Dashboard Admin</h1>
            <p style="color: rgba(255,255,255,0.8); font-size: 0.95rem; margin-top: -10px;">
                Memantau arus kas, jumlah distribusi beban kerja laundry, dan status transaksi CleanCo secara realtime.
            </p>
        </div>

        <div class="rekap-kanan">
            <div class="badge-tanggal-rekap">Periode: <?= date('d M Y', strtotime($dari_tanggal)) ?> - <?= date('d M Y', strtotime($sampai_tanggal)) ?></div>
            <div class="kartu-rekap-container">
                <div class="kartu-rekap">
                    <div class="kartu-header">Total Omzet</div>
                    <div class="kartu-body">
                        <p>Rp <?= number_format($total_omzet, 0, ',', '.') ?></p>
                    </div>
                </div>
                <div class="kartu-rekap">
                    <div class="kartu-header kartu-header-biru">Volume Pesanan</div>
                    <div class="kartu-body">
                        <p><?= $total_pesanan ?> Nota <span style="font-size: 1rem; color:#888;">(<?= number_format($total_berat, 1) ?> kg)</span></p>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <main class="area-laporan">
        
        <form method="GET" action="dashboard.php" class="form-filter-rekap">
            <div class="grup-filter-input">
                <label class="label-rekap" style="color: #1D3557; font-weight: 600;">Dari Tanggal :</label>
                <input type="date" name="dari_tanggal" class="input-rekap" value="<?= $dari_tanggal ?>" style="border: 1.5px solid #e0e0e0; width:100%;">
            </div>

            <div class="grup-filter-input">
                <label class="label-rekap" style="color: #1D3557; font-weight: 600;">Sampai Tanggal :</label>
                <input type="date" name="sampai_tanggal" class="input-rekap" value="<?= $sampai_tanggal ?>" style="border: 1.5px solid #e0e0e0; width:100%;">
            </div>

            <div class="grup-filter-input">
                <label class="label-rekap" style="color: #1D3557; font-weight: 600;">Status Transaksi :</label>
                <select name="status" class="select-rekap">
                    <option value="semua" <?= $status_filter === 'semua' ? 'selected' : '' ?>>Semua Status</option>
                    <option value="menunggu_konfirmasi" <?= $status_filter === 'menunggu_konfirmasi' ? 'selected' : '' ?>>Menunggu Konfirmasi</option>
                    <option value="dikonfirmasi" <?= $status_filter === 'dikonfirmasi' ? 'selected' : '' ?>>Dikonfirmasi</option>
                    <option value="sedang_dicuci" <?= $status_filter === 'sedang_dicuci' ? 'selected' : '' ?>>Sedang Diproses</option>
                    <option value="selesai" <?= $status_filter === 'selesai' ? 'selected' : '' ?>>Selesai</option>
                    <option value="dibatalkan" <?= $status_filter === 'dibatalkan' ? 'selected' : '' ?>>Dibatalkan</option>
                </select>
            </div>

            <div style="display: flex; gap: 10px;">
                <button type="submit" class="tombol-aksi-laporan btn-cari">Terapkan Filter</button>
                <button type="button" onclick="window.print()" class="tombol-aksi-laporan btn-cetak">🖨️ Cetak Laporan</button>
            </div>
        </form>

        <div class="tabel-wrapper">
            <table class="tabel-clean">
                <thead>
                    <tr>
                        <th>No.</th>
                        <th>Kode Nota</th>
                        <th>Tanggal Pesan</th>
                        <th>Pelanggan</th>
                        <th>Layanan / Paket</th>
                        <th>Berat</th>
                        <th>Total Bayar</th>
                        <th>Status</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($data_laporan)): ?>
                        <tr>
                            <td colspan="8" style="text-align: center; color: #aaa; padding: 40px;">
                                Transaksi tidak ditemukan pada range tanggal terpilih.
                            </td>
                        </tr>
                    <?php else: ?>
                        <?php $no = 1; foreach ($data_laporan as $laporan): ?>
                            <tr>
                                <td><?= $no++ ?></td>
                                <td style="font-weight: 700; color: #346E9E;">#<?= $laporan['kode_pesanan'] ?? 'CLN-' . $laporan['id'] ?></td>
                                <td><?= date('d-m-Y H:i', strtotime($laporan['created_at'])) ?></td>
                                <td>
                                    <strong><?= htmlspecialchars($laporan['nama_pelanggan']) ?></strong><br>
                                    <span style="font-size:0.8rem; color:#888;"><?= htmlspecialchars($laporan['email']) ?></span>
                                </td>
                                <td>
                                    <span style="text-transform: capitalize;"><?= htmlspecialchars($laporan['layanan']) ?></span> 
                                    <small style="color: #666;">(<?= htmlspecialchars($laporan['pengiriman']) ?>)</small>
                                </td>
                                <td><?= number_format($laporan['berat'], 1) ?> kg</td>
                                <td style="font-weight: 600; color: #1D3557;">Rp <?= number_format($laporan['total_harga'], 0, ',', '.') ?></td>
                                <td>
                                    <?php 
                                    $st = $laporan['status'];
                                    if ($st === 'menunggu_konfirmasi') echo '<span class="badge-status status-menunggu">Menunggu</span>';
                                    elseif ($st === 'selesai') echo '<span class="badge-status status-selesai">Selesai</span>';
                                    elseif ($st === 'dibatalkan') echo '<span class="badge-status status-batal">Batal</span>';
                                    else echo '<span class="badge-status status-proses">Diproses</span>';
                                    ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>

    </main>

</body>
</html>
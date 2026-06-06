<?php
// 1 & 2. Proteksi Akses Admin & Koneksi Database (Native PHP)
require_once '../config/session.php';
require_once '../config/database.php';
require_once '../config/functions.php';
require_once '../includes/admin-check.php';

// 3. Baca Filter Bulan & Tahun (Default: Masa Sekarang)
$bulanPilihan = $_GET['bulan'] ?? date('m');
$tahunPilihan = $_GET['tahun'] ?? date('Y');

try {
    // 4. Query Rekap Omzet per Hari (Hanya pesanan yang Lunas)
    $stmtHarian = $pdo->prepare("
        SELECT DATE(updated_at) AS tanggal,
               COUNT(*) AS jumlah_pesanan,
               SUM(total_harga) AS total_omzet
        FROM pesanan
        WHERE status_pembayaran = 'lunas'
          AND MONTH(updated_at) = ? 
          AND YEAR(updated_at) = ?
        GROUP BY DATE(updated_at) 
        ORDER BY tanggal ASC
    ");
    $stmtHarian->execute([$bulanPilihan, $tahunPilihan]);
    $rekapHarian = $stmtHarian->fetchAll();

    // 5. Query Rekap Omzet per Jenis Layanan
    $stmtLayanan = $pdo->prepare("
        SELECT l.nama_layanan,
               COUNT(p.id) AS jumlah,
               SUM(p.total_harga) AS omzet
        FROM pesanan p
        JOIN layanan l ON p.id_layanan = l.id
        WHERE p.status_pembayaran = 'lunas'
          AND MONTH(p.updated_at) = ? 
          AND YEAR(p.updated_at) = ?
        GROUP BY l.id
        ORDER BY omzet DESC
    ");
    $stmtLayanan->execute([$bulanPilihan, $tahunPilihan]);
    $rekapLayanan = $stmtLayanan->fetchAll();

    // 6. Hitung Akumulasi Total Keseluruhan untuk Kartu Ringkasan
    $totalOmzetKeseluruhan   = 0;
    $totalPesananKeseluruhan = 0;
    $maxOmzetHarian          = 1;

    foreach ($rekapHarian as $h) {
        $totalOmzetKeseluruhan   += $h['total_omzet'];
        $totalPesananKeseluruhan += $h['jumlah_pesanan'];
        if ($h['total_omzet'] > $maxOmzetHarian) {
            $maxOmzetHarian = $h['total_omzet'];
        }
    }

    $rataRataPerPesanan = ($totalPesananKeseluruhan > 0) ? ($totalOmzetKeseluruhan / $totalPesananKeseluruhan) : 0;

} catch (PDOException $e) {
    die("Gagal memuat laporan finansial: " . $e->getMessage());
}

// Helper nama bulan
$namaBulan = [
    '01' => 'Januari', '02' => 'Februari', '03' => 'Maret', '04' => 'April',
    '05' => 'Mei', '06' => 'Juni', '07' => 'Juli', '08' => 'Agustus',
    '09' => 'September', '10' => 'Oktober', '11' => 'November', '12' => 'Desember'
];

// Panggil header-admin
include '../includes/header-admin.php';
?>

<div class="halaman-pesanan" style="min-height: 100vh;">
    
    <!-- SIDEBAR - Tema Gradient sama dengan pesanan.php -->
    <div class="pesanan-sidebar">
        <h2 class="judul-sidebar">Filter Laporan</h2>
        
        <form method="GET" action="laporan.php" id="formFilterLaporan" style="position: relative; z-index: 2;">
            <div style="margin-bottom: 20px;">
                <label style="color: rgba(255,255,255,0.8); font-size: 0.8rem; text-transform: uppercase; letter-spacing: 0.05em; margin-bottom: 8px; display: block;">
                    📅 Pilih Periode
                </label>
                
                <select name="bulan" onchange="this.form.submit()" 
                        style="width: 100%; padding: 12px 16px; border: none; border-radius: 20px 0 20px 0; font-family: 'DM Sans', sans-serif; font-size: 0.9rem; color: #333; background: white; box-shadow: var(--shadow); outline: none; cursor: pointer; margin-bottom: 12px; appearance: none; background-image: url('data:image/svg+xml,%3Csvg xmlns=%22http://www.w3.org/2000/svg%22 width=%2212%22 height=%228%22 viewBox=%220 0 12 8%22%3E%3Cpath d=%22M1 1l5 5 5-5%22 stroke=%22%23888%22 stroke-width=%221.5%22 fill=%22none%22 stroke-linecap=%22round%22/%3E%3C/svg%3E'); background-repeat: no-repeat; background-position: right 16px center;">
                    <?php foreach ($namaBulan as $key => $val): ?>
                        <option value="<?= $key ?>" <?= $bulanPilihan === $key ? 'selected' : '' ?>>
                            <?= $val ?>
                        </option>
                    <?php endforeach; ?>
                </select>
                
                <select name="tahun" onchange="this.form.submit()" 
                        style="width: 100%; padding: 12px 16px; border: none; border-radius: 20px 0 20px 0; font-family: 'DM Sans', sans-serif; font-size: 0.9rem; color: #333; background: white; box-shadow: var(--shadow); outline: none; cursor: pointer; appearance: none; background-image: url('data:image/svg+xml,%3Csvg xmlns=%22http://www.w3.org/2000/svg%22 width=%2212%22 height=%228%22 viewBox=%220 0 12 8%22%3E%3Cpath d=%22M1 1l5 5 5-5%22 stroke=%22%23888%22 stroke-width=%221.5%22 fill=%22none%22 stroke-linecap=%22round%22/%3E%3C/svg%3E'); background-repeat: no-repeat; background-position: right 16px center;">
                    <?php for($t = 2024; $t <= date('Y'); $t++): ?>
                        <option value="<?= $t ?>" <?= $tahunPilihan == $t ? 'selected' : '' ?>>
                            <?= $t ?>
                        </option>
                    <?php endfor; ?>
                </select>
            </div>
        </form>

        <div style="border-top: 1px solid rgba(255,255,255,0.2); padding-top: 20px; position: relative; z-index: 2;">
            <label style="color: rgba(255,255,255,0.8); font-size: 0.8rem; text-transform: uppercase; letter-spacing: 0.05em; margin-bottom: 12px; display: block;">
                🖨️ Aksi Dokumen
            </label>
            <button onclick="window.print()" 
                    style="width: 100%; padding: 12px; background: white; color: var(--birutua); border: none; border-radius: 20px 0 20px 0; font-family: 'DM Sans', sans-serif; font-weight: 600; font-size: 0.9rem; cursor: pointer; box-shadow: var(--shadow);">
                🖨️ Cetak Laporan
            </button>
        </div>
    </div>

    <!-- KONTEN UTAMA -->
    <div class="pesanan-detail" style="padding: 30px 40px; overflow-y: auto;">
        
        <!-- HEADER -->
        <div style="margin-bottom: 32px;">
            <h2 style="font-family: 'Bricolage Grotesque', sans-serif; font-size: 1.8rem; color: var(--birutua); margin: 0 0 6px;">
                Laporan Keuangan
            </h2>
            <p style="color: #888; font-size: 0.9rem; margin: 0;">
                Periode: <?= $namaBulan[$bulanPilihan] ?> <?= $tahunPilihan ?>
            </p>
        </div>

        <!-- KARTU RINGKASAN (REUSE kartu-berat & kartu-biaya) -->
        <div class="detail-berat-biaya" style="margin-bottom: 28px;">
            <div class="kartu-berat" style="flex: 1; background: var(--tealmuda);">
                <p class="detail-label" style="color: #1a4d3a;">Total Omzet (Lunas)</p>
                <p style="font-family: 'Bricolage Grotesque', sans-serif; font-size: 1.8rem; font-weight: 700; color: #1a4d3a; margin: 8px 0 0;">
                    Rp <?= number_format($totalOmzetKeseluruhan, 0, ',', '.') ?>
                </p>
                <p class="member-stat-sub" style="color: #1a4d3a;">Bulan Terpilih</p>
            </div>
            <div class="kartu-biaya" style="flex: 1;">
                <p class="detail-label">Volume Pesanan</p>
                <p style="font-family: 'Bricolage Grotesque', sans-serif; font-size: 1.8rem; font-weight: 700; color: var(--birutua); margin: 8px 0 0;">
                    <?= $totalPesananKeseluruhan ?>
                </p>
                <p class="member-stat-sub">Transaksi Selesai</p>
            </div>
            <div class="kartu-biaya" style="flex: 1;">
                <p class="detail-label">Rata-rata per Pesanan</p>
                <p style="font-family: 'Bricolage Grotesque', sans-serif; font-size: 1.8rem; font-weight: 700; color: var(--birutua); margin: 8px 0 0;">
                    Rp <?= number_format($rataRataPerPesanan, 0, ',', '.') ?>
                </p>
                <p class="member-stat-sub">Nilai Rata-Rata Nota</p>
            </div>
        </div>

        <!-- GRAFIK BATANG (REUSE detail-status-section) -->
        <div class="detail-status-section" style="margin-bottom: 28px;">
            <p class="detail-label" style="margin-bottom: 16px; font-size: 0.9rem;">📊 Visualisasi Tren Pendapatan Harian</p>
            
            <?php if(empty($rekapHarian)): ?>
                <p style="text-align:center; color:#aaa; padding:20px 0;">Tidak ada aktivitas transaksi pada bulan ini.</p>
            <?php else: ?>
                <div style="display: flex; flex-direction: column; gap: 10px;">
                    <?php foreach($rekapHarian as $h): 
                        $persenBar = ($h['total_omzet'] / $maxOmzetHarian) * 100;
                    ?>
                        <div style="display: flex; align-items: center; gap: 12px; font-size: 0.85rem;">
                            <div style="width: 70px; font-weight: 600; color: var(--birutua); font-size: 0.8rem;">
                                <?= date('d M', strtotime($h['tanggal'])) ?>
                            </div>
                            <div style="flex: 1; background: #e8edf3; border-radius: 6px; height: 14px; overflow: hidden;">
                                <div style="background: var(--tealmuda); width: <?= $persenBar ?>%; height: 100%; border-radius: 6px; transition: width 0.5s;"></div>
                            </div>
                            <div style="width: 110px; text-align: right; font-weight: 700; color: var(--tealmuda); font-size: 0.85rem;">
                                Rp <?= number_format($h['total_omzet'], 0, ',', '.') ?>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>

        <!-- TABEL (REUSE detail-info-grid) -->
        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 20px;">
            
            <!-- Tabel Omzet Harian -->
            <div class="detail-info-grid" style="padding: 20px 24px;">
                <p class="detail-label" style="margin-bottom: 12px;">📅 Rincian Omzet Harian</p>
                <table style="width: 100%; border-collapse: collapse; font-size: 0.85rem;">
                    <thead>
                        <tr style="border-bottom: 2px solid #e0e0e0;">
                            <th style="padding: 10px 8px; text-align: left; color: #7a9ab5; font-weight: 600;">Tanggal</th>
                            <th style="padding: 10px 8px; text-align: center; color: #7a9ab5; font-weight: 600;">Pesanan</th>
                            <th style="padding: 10px 8px; text-align: right; color: #7a9ab5; font-weight: 600;">Omzet</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if(empty($rekapHarian)): ?>
                            <tr><td colspan="3" style="padding:20px; text-align:center; color:#aaa;">Data nihil.</td></tr>
                        <?php else: foreach($rekapHarian as $h): ?>
                            <tr style="border-bottom: 1px solid #f0f0f0;">
                                <td style="padding: 10px 8px; color: #333;"><?= date('d-m-Y', strtotime($h['tanggal'])) ?></td>
                                <td style="padding: 10px 8px; text-align: center; color: #555;"><?= $h['jumlah_pesanan'] ?></td>
                                <td style="padding: 10px 8px; text-align: right; font-weight: 600; color: var(--tealmuda);">Rp <?= number_format($h['total_omzet'], 0, ',', '.') ?></td>
                            </tr>
                        <?php endforeach; endif; ?>
                    </tbody>
                </table>
            </div>

            <!-- Tabel Distribusi Layanan -->
            <div class="detail-info-grid" style="padding: 20px 24px;">
                <p class="detail-label" style="margin-bottom: 12px;">👕 Distribusi per Layanan</p>
                <table style="width: 100%; border-collapse: collapse; font-size: 0.85rem;">
                    <thead>
                        <tr style="border-bottom: 2px solid #e0e0e0;">
                            <th style="padding: 10px 8px; text-align: left; color: #7a9ab5; font-weight: 600;">Layanan</th>
                            <th style="padding: 10px 8px; text-align: center; color: #7a9ab5; font-weight: 600;">Jumlah</th>
                            <th style="padding: 10px 8px; text-align: right; color: #7a9ab5; font-weight: 600;">Income</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if(empty($rekapLayanan)): ?>
                            <tr><td colspan="3" style="padding:20px; text-align:center; color:#aaa;">Data nihil.</td></tr>
                        <?php else: foreach($rekapLayanan as $l): ?>
                            <tr style="border-bottom: 1px solid #f0f0f0;">
                                <td style="padding: 10px 8px; font-weight: 600; color: var(--birutua);"><?= htmlspecialchars($l['nama_layanan']) ?></td>
                                <td style="padding: 10px 8px; text-align: center; color: #555;"><?= $l['jumlah'] ?>x</td>
                                <td style="padding: 10px 8px; text-align: right; font-weight: 600; color: var(--tealmuda);">Rp <?= number_format($l['omzet'], 0, ',', '.') ?></td>
                            </tr>
                        <?php endforeach; endif; ?>
                    </tbody>
                </table>
            </div>
        </div>

    </div>
</div>

<?php 
// Panggil footer untuk menutup tag body dan html secara valid
include '../includes/footer.php'; 
?>
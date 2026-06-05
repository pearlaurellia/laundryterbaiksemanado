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
    $maxOmzetHarian          = 1; // Untuk pembagi visual grafik batang native nanti

    foreach ($rekapHarian as $h) {
        $totalOmzetKeseluruhan   += $h['total_omzet'];
        $totalPesananKeseluruhan += $h['jumlah_pesanan'];
        if ($h['total_omzet'] > $maxOmzetHarian) {
            $maxOmzetHarian = $h['total_omzet'];
        }
    }

    // Hitung Nilai Rata-rata per Transaksi cucian
    $rataRataPerPesanan = ($totalPesananKeseluruhan > 0) ? ($totalOmzetKeseluruhan / $totalPesananKeseluruhan) : 0;

} catch (PDOException $e) {
    die("Gagal memuat laporan finansial: " . $e->getMessage());
}

// Panggil header-admin
include '../includes/header-admin.php';
?>

<div class="laporan-container" style="display: flex; gap: 30px; padding: 40px var(--padding-horizontal);">
    
    <<aside class="pesanan-sidebar" style="flex: 1; background: white; padding: 30px; border-radius: 20px; box-shadow: 0 4px 20px rgba(0,0,0,0.03); height: fit-content;">
        <h2 class="judul-sidebar" style="font-family: 'Bricolage Grotesque', sans-serif; margin-bottom: 20px; color: #0d3f8a !important;">Filter Laporan</h2>
        
        <form method="GET" action="laporan.php" id="formFilterLaporan">
            <div class="filter-group" style="margin-bottom: 20px;">
                <label class="filter-label" style="display:block; font-weight:600; margin-bottom:8px; color: #333 !important; font-family: 'DM Sans', sans-serif;">📅 Pilih Periode</label>
                
                <select name="bulan" class="filter-select" style="width:100%; padding:10px; margin-bottom:10px; border-radius:8px; border:1px solid #ccc; color: #333 !important; background-color: #fff !important; font-family: 'DM Sans', sans-serif; display: block;" onchange="this.form.submit()">
                    <?php
                    $namaBulan = [
                        '01' => 'Januari', '02' => 'Februari', '03' => 'Maret', '04' => 'April',
                        '05' => 'Mei', '06' => 'Juni', '07' => 'Juli', '08' => 'Agustus',
                        '09' => 'September', '10' => 'Oktober', '11' => 'November', '12' => 'Desember'
                    ];
                    foreach ($namaBulan as $key => $val): ?>
                        <option value="<?= $key ?>" <?= $bulanPilihan === $key ? 'selected' : '' ?> style="color: #333; background: #fff;"><?= $val ?></option>
                    <?php endforeach; ?>
                </select>
                
                <select name="tahun" class="filter-select" style="width:100%; padding:10px; border-radius:8px; border:1px solid #ccc; color: #333 !important; background-color: #fff !important; font-family: 'DM Sans', sans-serif; display: block;" onchange="this.form.submit()">
                    <?php for($t = 2024; $t <= date('Y'); $t++): ?>
                        <option value="<?= $t ?>" <?= $tahunPilihan == $t ? 'selected' : '' ?> style="color: #333; background: #fff;"><?= $t ?></option>
                    <?php endfor; ?>
                </select>
            </div>
        </form>

        <div class="filter-group" style="margin-top: 30px; border-top: 1px solid #eee; padding-top: 20px;">
            <label class="filter-label" style="display:block; font-weight:600; margin-bottom:12px; color: #333 !important; font-family: 'DM Sans', sans-serif;">🖨️ Aksi Dokumen</label>
            <button onclick="window.print()" class="tombol-filter-laporan" style="width:100%; background: var(--tealmuda); color:white; border:none; padding:12px; border-radius:10px; font-weight:bold; cursor:pointer; font-family: 'DM Sans', sans-serif;">
                🖨️ Cetak Laporan (PDF)
            </button>
        </div>
    </aside>

    <main class="laporan-kanan" style="flex: 3; display: flex; flex-direction: column; gap: 30px;">
        
        <div class="ringkasan-grid" style="display: grid; grid-template-columns: repeat(auto-fit, minmax(220px, 1fr)); gap: 20px;">
            <div class="kartu-ringkasan" style="background:white; padding:25px; border-radius:15px; box-shadow: 0 4px 15px rgba(0,0,0,0.02); border-left: 5px solid #22c55e;">
                <p class="ringkasan-judul" style="color:#888; font-size:0.9rem; margin-bottom:5px;">Total Omzet (Lunas)</p>
                <h3 class="ringkasan-nilai" style="font-size:1.8rem; font-weight:bold; color:var(--birutua);">Rp <?= number_format($totalOmzetKeseluruhan, 0, ',', '.') ?></h3>
                <p class="ringkasan-sub" style="font-size:0.8rem; color:#666;">Bulan Terpilih</p>
            </div>
            <div class="kartu-ringkasan" style="background:white; padding:25px; border-radius:15px; box-shadow: 0 4px 15px rgba(0,0,0,0.02); border-left: 5px solid #3b82f6;">
                <p class="ringkasan-judul" style="color:#888; font-size:0.9rem; margin-bottom:5px;">Volume Pesanan</p>
                <h3 class="ringkasan-nilai" style="font-size:1.8rem; font-weight:bold; color:var(--birutua);"><?= $totalPesananKeseluruhan ?></h3>
                <p class="ringkasan-sub" style="font-size:0.8rem; color:#666;">Transaksi Selesai</p>
            </div>
            <div class="kartu-ringkasan" style="background:white; padding:25px; border-radius:15px; box-shadow: 0 4px 15px rgba(0,0,0,0.02); border-left: 5px solid #eab308;">
                <p class="ringkasan-judul" style="color:#888; font-size:0.9rem; margin-bottom:5px;">Rata-rata per Pesanan</p>
                <h3 class="ringkasan-nilai" style="font-size:1.8rem; font-weight:bold; color:var(--birutua);">Rp <?= number_format($rataRataPerPesanan, 0, ',', '.') ?></h3>
                <p class="ringkasan-sub" style="font-size:0.8rem; color:#666;">Nilai Rata-Rata Nota</p>
            </div>
        </div>

        <div class="grafik-container" style="background:white; padding:30px; border-radius:20px; box-shadow:0 4px 15px rgba(0,0,0,0.02);">
            <div class="grafik-judul" style="font-weight:bold; margin-bottom:20px; font-family:'Bricolage Grotesque', sans-serif;">
                <span>📊 Visualisasi Tren Pendapatan Harian (Murni HTML/CSS)</span>
            </div>
            <div style="display: flex; flex-direction: column; gap: 12px;">
                <?php if(empty($rekapHarian)): ?>
                    <p style="text-align:center; color:#aaa; padding:20px 0;">Tidak ada aktivitas transaksi pada bulan ini.</p>
                <?php else: foreach($rekapHarian as $h): 
                    // Hitung persentase panjang batang secara matematis di PHP
                    $persenBar = ($h['total_omzet'] / $maxOmzetHarian) * 100;
                ?>
                    <div style="display: flex; align-items: center; gap: 15px; font-size: 0.85rem;">
                        <div style="width: 80px; font-weight: 600; color: var(--birutua);"><?= date('d M', strtotime($h['tanggal'])) ?></div>
                        <div style="flex: 1; background: #f3f4f6; border-radius: 6px; height: 16px; overflow: hidden;">
                            <div style="background: var(--tealmuda); width: <?= $persenBar ?>%; height: 100%; border-radius: 6px; transition: width 0.5s;"></div>
                        </div>
                        <div style="width: 100px; text-align: right; font-weight: bold; color: #22c55e;">Rp <?= number_format($h['total_omzet'], 0, ',', '.') ?></div>
                    </div>
                <?php endforeach; endif; ?>
            </div>
        </div>

        <div style="display: flex; gap: 20px; flex-wrap: wrap;">
            <div class="tabel-container" style="flex: 1; min-width: 320px; background:white; padding:25px; border-radius:20px; box-shadow:0 4px 15px rgba(0,0,0,0.02);">
                <div class="tabel-judul" style="font-weight:bold; margin-bottom:15px; font-family:'Bricolage Grotesque', sans-serif;">📅 Rincian Omzet Harian</div>
                <table style="width:100%; border-collapse:collapse; text-align:left; font-size:0.9rem;">
                    <thead>
                        <tr style="background:#f9fafb; border-bottom:1px solid #eee;">
                            <th style="padding:12px;">Tanggal</th>
                            <th style="padding:12px; text-align:center;">Pesanan</th>
                            <th style="padding:12px; text-align:right;">Omzet</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if(empty($rekapHarian)): ?>
                            <tr><td colspan="3" style="padding:20px; text-align:center; color:#aaa;">Data nihil.</td></tr>
                        <?php else: foreach($rekapHarian as $h): ?>
                            <tr style="border-bottom:1px solid #f3f4f6;">
                                <td style="padding:12px;"><?= date('d-m-Y', strtotime($h['tanggal'])) ?></td>
                                <td style="padding:12px; text-align:center;"><?= $h['jumlah_pesanan'] ?></td>
                                <td style="padding:12px; text-align:right; font-weight:600; color:var(--tealmuda);">Rp <?= number_format($h['total_omzet'], 0, ',', '.') ?></td>
                            </tr>
                        <?php endforeach; endif; ?>
                    </tbody>
                </table>
            </div>

            <div class="tabel-container" style="flex: 1; min-width: 320px; background:white; padding:25px; border-radius:20px; box-shadow:0 4px 15px rgba(0,0,0,0.02);">
                <div class="tabel-judul" style="font-weight:bold; margin-bottom:15px; font-family:'Bricolage Grotesque', sans-serif;">👕 Distribusi per Layanan</div>
                <table style="width:100%; border-collapse:collapse; text-align:left; font-size:0.9rem;">
                    <thead>
                        <tr style="background:#f9fafb; border-bottom:1px solid #eee;">
                            <th style="padding:12px;">Nama Layanan</th>
                            <th style="padding:12px; text-align:center;">Kuantitas</th>
                            <th style="padding:12px; text-align:right;">Total Income</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if(empty($rekapLayanan)): ?>
                            <tr><td colspan="3" style="padding:20px; text-align:center; color:#aaa;">Data nihil.</td></tr>
                        <?php else: foreach($rekapLayanan as $l): ?>
                            <tr style="border-bottom:1px solid #f3f4f6;">
                                <td style="padding:12px; font-weight:600; color:var(--birutua);"><?= htmlspecialchars($l['nama_layanan']) ?></td>
                                <td style="padding:12px; text-align:center;"><?= $l['jumlah'] ?>x ditransaksikan</td>
                                <td style="padding:12px; text-align:right; font-weight:600; color:#22c55e;">Rp <?= number_format($l['omzet'], 0, ',', '.') ?></td>
                            </tr>
                        <?php endforeach; endif; ?>
                    </tbody>
                </table>
            </div>
        </div>

    </main>
</div>

<?php 
// Panggil footer untuk menutup tag body dan html secara valid
include '../includes/footer.php'; 
?>
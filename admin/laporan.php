<?php include '../includes/header-admin.php'; ?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Laporan - CleanCo Admin</title>
    <link rel="stylesheet" href="../assets/css/style.css">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Bricolage+Grotesque:opsz,wght@12..96,200..800&display=swap" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=DM+Sans:ital,opsz,wght@0,9..40,100..1000;1,9..40,100..1000&display=swap" rel="stylesheet">
</head>
<body>

<div class="laporan-container">
    <!-- SIDEBAR FILTER -->
    <aside class="pesanan-sidebar">
        <h2 class="judul-sidebar">Laporan</h2>
        
        <div class="filter-group">
            <span class="filter-label">📅 Periode</span>
            <select id="filterBulan" class="filter-select">
                <option value="01">Januari</option>
                <option value="02">Februari</option>
                <option value="03">Maret</option>
                <option value="04">April</option>
                <option value="05">Mei</option>
                <option value="06">Juni</option>
                <option value="07">Juli</option>
                <option value="08">Agustus</option>
                <option value="09">September</option>
                <option value="10">Oktober</option>
                <option value="11">November</option>
                <option value="12" selected>Desember</option>
            </select>
            <select id="filterTahun" class="filter-select">
                <option value="2024">2024</option>
                <option value="2025">2025</option>
                <option value="2026" selected>2026</option>
            </select>
        </div>

        <div class="filter-group">
            <span class="filter-label">📆 Range Kustom</span>
            <input type="date" id="filterStartDate" class="filter-input" placeholder="Mulai">
            <input type="date" id="filterEndDate" class="filter-input" placeholder="Selesai">
            <button id="tombolTerapkanRange" class="tombol-filter-laporan">Terapkan Range</button>
        </div>

        <button id="tombolRefresh" class="tombol-filter-laporan">
            🔄 Refresh Data
        </button>

        <div class="filter-group" style="margin-top: 24px;">
            <span class="filter-label">📎 Ekspor Data</span>
            <button id="tombolExportExcel" class="tombol-export">
                📊 Ekspor Excel
            </button>
            <button id="tombolExportCSV" class="tombol-export">
                📄 Ekspor CSV
            </button>
        </div>

        <div class="bulat-sidebar"></div>
    </aside>

    <!-- KONTEN KANAN -->
    <main class="laporan-kanan">
        <!-- Kartu Ringkasan -->
        <div class="ringkasan-grid" id="ringkasanGrid">
            <div class="kartu-ringkasan">
                <p class="ringkasan-judul">Total Pendapatan</p>
                <h3 class="ringkasan-nilai" id="totalPendapatan">Rp 0</h3>
                <p class="ringkasan-sub" id="trendPendapatan">—</p>
            </div>
            <div class="kartu-ringkasan">
                <p class="ringkasan-judul">Total Pesanan</p>
                <h3 class="ringkasan-nilai" id="totalPesanan">0</h3>
                <p class="ringkasan-sub" id="trendPesanan">—</p>
            </div>
            <div class="kartu-ringkasan">
                <p class="ringkasan-judul">Pesanan Selesai</p>
                <h3 class="ringkasan-nilai" id="pesananSelesai">0</h3>
                <p class="ringkasan-sub" id="persenSelesai">—</p>
            </div>
            <div class="kartu-ringkasan">
                <p class="ringkasan-judul">Rata-rata per Pesanan</p>
                <h3 class="ringkasan-nilai" id="rataPesanan">Rp 0</h3>
                <p class="ringkasan-sub">Nilai rata-rata</p>
            </div>
        </div>

        <!-- Grafik Pendapatan -->
        <div class="grafik-container">
            <div class="grafik-judul">
                <span>📈 Tren Pendapatan (6 Bulan Terakhir)</span>
                <span style="font-size:0.75rem; color:#aaa;" id="grafikPeriodeLabel">Periode: Jul - Des 2026</span>
            </div>
            <div id="grafikContainer" class="grafik-chart">
                <canvas id="pendapatanChart" width="800" height="250" style="width:100%; height:250px;"></canvas>
            </div>
        </div>

        <!-- Tabel Daftar Pesanan -->
        <div class="tabel-container">
            <div class="tabel-judul">
                <span>📋 Daftar Pesanan</span>
            </div>
            <div class="tabel-filter">
                <button class="tabel-filter-btn aktif" data-status="semua">Semua</button>
                <button class="tabel-filter-btn" data-status="selesai">Selesai</button>
                <button class="tabel-filter-btn" data-status="dibatalkan">Dibatalkan</button>
                <button class="tabel-filter-btn" data-status="proses">Sedang Diproses</button>
            </div>
            <div id="tabelPesananWrapper">
                <div class="loading-state">
                    <div class="loading-spinner"></div>
                    <div>Memuat data pesanan...</div>
                </div>
            </div>
            <div class="pagination-container" id="paginationContainer" style="display: none;">
                <button id="prevPageBtn" class="pagination-btn">← Sebelumnya</button>
                <span id="pageInfo" class="page-info">Halaman 1</span>
                <button id="nextPageBtn" class="pagination-btn">Selanjutnya →</button>
            </div>
        </div>
    </main>
</div>

<script src="../assets/js/main.js"></script>
</body>
</html>
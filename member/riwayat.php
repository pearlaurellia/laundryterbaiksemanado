<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Riwayat Pesanan - CleanCo</title>
    <link rel="stylesheet" href="../assets/css/style.css">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Bricolage+Grotesque:opsz,wght@12..96,200..800&display=swap" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=DM+Sans:ital,opsz,wght@0,9..40,100..1000;1,9..40,100..1000&display=swap" rel="stylesheet">
</head>
<body>

    <?php include '../includes/header-member.php'; ?>


    <section class="riwayat-section">

        <div class="riwayat-header">
            <h1 class="status-judul">Riwayat Pesanan</h1>
            <p class="status-subjudul">
                Arsip seluruh pesanan yang telah selesai atau dibatalkan.
            </p>
        </div>

        <div class="grup-filter riwayat-filter" id="grupFilterRiwayat">
            <button class="tombol-filter aktif"
                    data-filter="semua"
                    onclick="filterRiwayat('semua', this)">
                Semua
            </button>
            <button class="tombol-filter"
                    data-filter="selesai"
                    onclick="filterRiwayat('selesai', this)">
                Selesai
            </button>
            <button class="tombol-filter"
                    data-filter="dibatalkan"
                    onclick="filterRiwayat('dibatalkan', this)">
                Dibatalkan
            </button>
        </div>

        <div class="riwayat-list" id="riwayatList">


            <!-- KARTU 1: Selesai, Kurir -->
            <div class="kartu-riwayat" data-filter="selesai">

                <div class="kartu-riwayat-kiri">
                    <div class="kartu-riwayat-atas">
                        <span class="badge-status badge-status-selesai">
                            Selesai & Lunas
                        </span>
                        <span class="kartu-riwayat-tanggal">
                            <!--
                                BACKEND: $p['updated_at'] diformat
                                sebagai tanggal selesai
                            -->
                            Selesai: Rabu, 04-12-2026
                        </span>
                    </div>

                    <h3 class="kartu-riwayat-kode">
                        <!-- BACKEND: $p['kode_pesanan'] -->
                        #LDR-0038
                    </h3>

                    <div class="kartu-riwayat-tags">
                        <!-- BACKEND: $p['nama_layanan'], $p['opsi_pengantaran'] -->
                        <span class="badge-hijau">Reguler</span>
                        <span class="badge-biru">Kurir</span>
                        <span class="badge-biru">Malalayang</span>
                    </div>
                </div>

                <div class="kartu-riwayat-kanan">
                    <div class="kartu-riwayat-detail">
                        <div class="riwayat-detail-baris">
                            <span class="riwayat-detail-label">Berat Aktual</span>
                            <!--
                                BACKEND: $p['berat_aktual'] . ' kg'
                            -->
                            <span class="riwayat-detail-nilai">3.0 kg</span>
                        </div>
                        <div class="riwayat-detail-baris">
                            <span class="riwayat-detail-label">Total Harga</span>
                            <!--
                                BACKEND: 'Rp ' . number_format($p['total_harga'], 0, ',', '.')
                            -->
                            <span class="riwayat-detail-nilai riwayat-total">
                                Rp 34.000
                            </span>
                        </div>
                        <div class="riwayat-detail-baris">
                            <span class="riwayat-detail-label">Pembayaran</span>
                            <span class="riwayat-detail-nilai"
                                  style="color:#52c49c; font-weight:700;">
                                Lunas
                            </span>
                        </div>
                    </div>

                    <a href="detail-pesanan.php?id=LDR-0038"
                       class="tombol-detail-status">
                        Lihat Detail
                    </a>
                </div>

            </div>


            <div class="kartu-riwayat" data-filter="selesai">

                <div class="kartu-riwayat-kiri">
                    <div class="kartu-riwayat-atas">
                        <span class="badge-status badge-status-selesai">
                            Selesai & Lunas
                        </span>
                        <span class="kartu-riwayat-tanggal">
                            Selesai: Selasa, 03-12-2026
                        </span>
                    </div>
                    <h3 class="kartu-riwayat-kode">#LDR-0031</h3>
                    <div class="kartu-riwayat-tags">
                        <span class="badge-hijau">Express</span>
                        <span class="badge-biru">Ambil Sendiri</span>
                    </div>
                </div>

                <div class="kartu-riwayat-kanan">
                    <div class="kartu-riwayat-detail">
                        <div class="riwayat-detail-baris">
                            <span class="riwayat-detail-label">Berat Aktual</span>
                            <span class="riwayat-detail-nilai">2.5 kg</span>
                        </div>
                        <div class="riwayat-detail-baris">
                            <span class="riwayat-detail-label">Total Harga</span>
                            <span class="riwayat-detail-nilai riwayat-total">
                                Rp 37.500
                            </span>
                        </div>
                        <div class="riwayat-detail-baris">
                            <span class="riwayat-detail-label">Pembayaran</span>
                            <span class="riwayat-detail-nilai"
                                  style="color:#52c49c; font-weight:700;">
                                Lunas
                            </span>
                        </div>
                    </div>
                    <a href="detail-pesanan.php?id=LDR-0031"
                       class="tombol-detail-status">
                        Lihat Detail
                    </a>
                </div>

            </div>


            <div class="kartu-riwayat kartu-riwayat-batal" data-filter="dibatalkan">

                <div class="kartu-riwayat-kiri">
                    <div class="kartu-riwayat-atas">

                        <span class="badge-status badge-status-batal">
                            Dibatalkan
                        </span>
                        <span class="kartu-riwayat-tanggal">
                            Dibatalkan: Senin, 01-12-2026
                        </span>
                    </div>
                    <h3 class="kartu-riwayat-kode">#LDR-0025</h3>
                    <div class="kartu-riwayat-tags">
                        <span class="badge-hijau">Reguler</span>
                        <span class="badge-biru">Kurir</span>
                        <span class="badge-biru">Wanea</span>
                    </div>
                </div>

                <div class="kartu-riwayat-kanan">
                    <div class="kartu-riwayat-detail">
                        <div class="riwayat-detail-baris">
                            <span class="riwayat-detail-label">Berat Aktual</span>
                            <!--
                                BACKEND: jika dibatalkan sebelum timbang,
                                tampilkan '—' bukan angka
                            -->
                            <span class="riwayat-detail-nilai">—</span>
                        </div>
                        <div class="riwayat-detail-baris">
                            <span class="riwayat-detail-label">Total Harga</span>
                            <span class="riwayat-detail-nilai">—</span>
                        </div>
                        <div class="riwayat-detail-baris">
                            <span class="riwayat-detail-label">Pembayaran</span>
                            <span class="riwayat-detail-nilai"
                                  style="color:#f87171; font-weight:700;">
                                Tidak Jadi
                            </span>
                        </div>
                    </div>
                    <a href="detail-pesanan.php?id=LDR-0025"
                       class="tombol-detail-status">
                        Lihat Detail
                    </a>
                </div>

            </div>
            <!-- /kartu 3 -->


        </div>

        <div class="status-kosong" id="riwayatKosong" style="display:none;">
            <div class="status-kosong-ikon">📋</div>
            <h2 class="status-kosong-judul">Belum ada riwayat</h2>
            <p class="status-kosong-sub">
                Pesanan yang sudah selesai atau dibatalkan akan muncul di sini.
            </p>
            <a href="pesan.php" class="tombol-submit-form"
               style="text-decoration:none; display:inline-block; margin-top:10px;">
                Buat Pesanan Pertama
            </a>
        </div>

    </section>

    <script src="../assets/js/main.js"></script>

</body>
</html>
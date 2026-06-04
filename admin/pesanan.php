<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pesanan - CleanCo Admin</title>
    <link rel="stylesheet" href="../assets/css/style.css">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Bricolage+Grotesque:opsz,wght@12..96,200..800&display=swap" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=DM+Sans:ital,opsz,wght@0,9..40,100..1000;1,9..40,100..1000&display=swap" rel="stylesheet">
    <script src="../assets/js/main.js"></script>
    <script src="../assets/js/status.refresh.js"></script>
    <script src="../assets/js/kalkulasi-harga.js"></script>
    <script src="../assets/js/form-validation.js"></script>
</head>
<body>

    <?php include '../includes/header-admin.php'; ?>
    <section class="halaman-pesanan">

        <div class="pesanan-sidebar">
            <h2 class="judul-sidebar">Daftar Pesanan</h2>

            <div class="grup-filter">
                <button class="tombol-filter aktif" onclick="filterPesanan('semua', this)">Semua</button>
                <button class="tombol-filter" onclick="filterPesanan('baru', this)">Baru</button>
                <button class="tombol-filter" onclick="filterPesanan('diproses', this)">Diproses</button>
                <button class="tombol-filter" onclick="filterPesanan('selesai', this)">Selesai</button>
            </div>

            <div class="list-pesanan" id="listPesanan">

                <div class="item-pesanan aktif-dipilih" data-status="baru" onclick="bukaPesanan(0, this)">
                    <div class="item-pesanan-atas">
                        <span class="badge-biru">Baru</span>
                        <span class="item-pesanan-waktu">10:00 Rabu, 04-12-2026</span>
                    </div>
                    <p class="item-pesanan-nama">Sasha Cantik</p>
                    <div class="item-pesanan-tags">
                        <span class="badge-hijau">Cuci</span>
                        <span class="badge-biru">Express</span>
                        <span class="badge-biru">Antar</span>
                    </div>
                </div>

                <div class="item-pesanan" data-status="diproses" onclick="bukaPesanan(1, this)">
                    <div class="item-pesanan-atas">
                        <span class="badge-biru" style="background-color:#f59e0b;">Diproses</span>
                        <span class="item-pesanan-waktu">08:30 Rabu, 04-12-2026</span>
                    </div>
                    <p class="item-pesanan-nama">Yoo Haram</p>
                    <div class="item-pesanan-tags">
                        <span class="badge-hijau">Cuci</span>
                        <span class="badge-biru">Reguler</span>
                        <span class="badge-biru">Pickup</span>
                    </div>
                </div>

                <div class="item-pesanan" data-status="selesai" onclick="bukaPesanan(2, this)">
                    <div class="item-pesanan-atas">
                        <span class="badge-biru" style="background-color:#52c49c; color:#1a4d3a;">Selesai</span>
                        <span class="item-pesanan-waktu">07:00 Rabu, 04-12-2026</span>
                    </div>
                    <p class="item-pesanan-nama">Karina AESPA</p>
                    <div class="item-pesanan-tags">
                        <span class="badge-hijau">Dry Cleaning</span>
                        <span class="badge-biru">Reguler</span>
                        <span class="badge-biru">Antar</span>
                    </div>
                </div>

                <div class="item-pesanan" data-status="baru" onclick="bukaPesanan(3, this)">
                    <div class="item-pesanan-atas">
                        <span class="badge-biru">Baru</span>
                        <span class="item-pesanan-waktu">06:15 Rabu, 04-12-2026</span>
                    </div>
                    <p class="item-pesanan-nama">Hannah Dodd</p>
                    <div class="item-pesanan-tags">
                        <span class="badge-hijau">Cuci</span>
                        <span class="badge-biru">Express</span>
                        <span class="badge-biru">Pickup</span>
                    </div>
                </div>

            </div>
        </div>

        <div class="pesanan-detail" id="pesananDetail">

            <div class="detail-kosong" id="detailKosong">
                <div class="bulat-ditengah" style="position:static; width:80px; height:80px; opacity:0.2;"></div>
                <p style="color:#aaa; margin-top:20px;">Pilih pesanan untuk melihat detail</p>
            </div>

            <div class="detail-isi" id="detailIsi" style="display:none;">

                <div class="detail-header">
                    <div>
                        <h2 class="detail-nama" id="detailNama">Ryan Liam</h2>
                        <p class="detail-username" id="detailUsername">@liam999</p>
                    </div>
                    <div class="detail-waktu-badge" id="detailWaktu">10:00 Rabu, 04-12-2026</div>
                </div>

                <div class="grup-keterangan" id="detailTags" style="margin-bottom: 20px;"></div>

                <div class="detail-info-grid">
                    <div class="detail-info-blok">
                        <p class="detail-label">Alamat Pengiriman</p>
                        <p class="detail-nilai" id="detailAlamat">Paal 4, Manado</p>
                    </div>
                    <div class="detail-info-blok">
                        <p class="detail-label">Nomor Telepon</p>
                        <p class="detail-nilai" id="detailTelpon">0834545827</p>
                    </div>
                    <div class="detail-info-blok">
                        <p class="detail-label">Layanan</p>
                        <p class="detail-nilai" id="detailLayanan">Express</p>
                    </div>
                    <div class="detail-info-blok">
                        <p class="detail-label">Pengiriman</p>
                        <p class="detail-nilai" id="detailPengiriman">Antar</p>
                    </div>
                </div>

                <div class="detail-berat-biaya">

                    <div class="kartu-berat">
                        <p class="detail-label">Berat (kg)</p>
                        <div class="input-berat-wrapper">
                            <input
                                type="number"
                                class="input-berat"
                                id="inputBerat"
                                placeholder="0"
                                min="0"
                                step="0.1"
                                oninput="hitungBiaya()"
                            >
                            <span class="satuan-berat">kg</span>
                        </div>
                    </div>

                    <div class="kartu-biaya">
                        <p class="detail-label">Rincian Biaya</p>
                        <p class="rincian-baris" id="rincianLayanan">Layanan : Rp 0</p>
                        <p class="rincian-baris" id="rincianKirim">Pengiriman : Rp 0</p>
                        <p class="rincian-total" id="rincianTotal">Total : Rp 0</p>
                    </div>

                </div>

                <div class="detail-info-blok" style="margin-bottom: 24px;">
                    <p class="detail-label">Catatan dari pelanggan</p>
                    <p class="detail-nilai" id="detailNote" style="font-style: italic; color: #888;">—</p>
                </div>

                <div class="detail-status-section">
                    <p class="detail-label">Update Status Pesanan</p>
                    <div class="tombol-status-group" id="tombolStatusGroup">
                        <button class="tombol-status" data-status="baru" onclick="updateStatus('baru')">Baru</button>
                        <button class="tombol-status" data-status="diproses" onclick="updateStatus('diproses')">Diproses</button>
                        <button class="tombol-status" data-status="selesai" onclick="updateStatus('selesai')">Selesai</button>
                    </div>
                    <p class="status-aktif-teks">Status saat ini: <strong id="statusAktifTeks">Baru</strong></p>
                </div>

            </div>
        </div>

    </section>

</body>
</html>
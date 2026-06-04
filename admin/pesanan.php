<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kelola Pesanan - CleanCo Admin</title>
    <link rel="stylesheet" href="../assets/css/style.css">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Bricolage+Grotesque:opsz,wght@12..96,200..800&display=swap" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=DM+Sans:ital,opsz,wght@0,9..40,100..1000;1,9..40,100..1000&display=swap" rel="stylesheet">

</head>
<body>

    <?php include '../includes/header-admin.php'; ?>

    <section class="halaman-pesanan" id="viewList">

        <div class="pesanan-sidebar">
            <h2 class="judul-sidebar">Daftar Pesanan</h2>

            <div class="grup-filter">
                <button class="tombol-filter aktif" onclick="filterPesanan('semua', this)">Semua</button>
                <button class="tombol-filter" onclick="filterPesanan('baru', this)">Baru</button>
                <button class="tombol-filter" onclick="filterPesanan('diproses', this)">Diproses</button>
                <button class="tombol-filter" onclick="filterPesanan('selesai', this)">Selesai</button>
            </div>

            <div class="list-pesanan" id="listPesanan">


                <div class="item-pesanan aktif-dipilih"
                     data-id="1"
                     data-status="baru"
                     onclick="bukaPesanan(1, this)">
                    <div class="item-pesanan-atas">
                        <span class="badge-status badge-status-baru">Baru</span>
                        <span class="item-pesanan-waktu">10:00 Rabu, 04-12-2026</span>
                    </div>
                    <p class="item-pesanan-nama">Ryan Liam</p>
                    <div class="item-pesanan-tags">
                        <span class="badge-hijau">Cuci</span>
                        <span class="badge-biru">Express</span>
                        <span class="badge-biru">Antar</span>
                    </div>
                </div>

                <div class="item-pesanan"
                     data-id="2"
                     data-status="diproses"
                     onclick="bukaPesanan(2, this)">
                    <div class="item-pesanan-atas">
                        <span class="badge-status badge-status-diproses">Diproses</span>
                        <span class="item-pesanan-waktu">08:30 Rabu, 04-12-2026</span>
                    </div>
                    <p class="item-pesanan-nama">Sinta Dewi</p>
                    <div class="item-pesanan-tags">
                        <span class="badge-hijau">Cuci</span>
                        <span class="badge-biru">Reguler</span>
                        <span class="badge-biru">Pickup</span>
                    </div>
                </div>

                <div class="item-pesanan"
                     data-id="3"
                     data-status="selesai"
                     onclick="bukaPesanan(3, this)">
                    <div class="item-pesanan-atas">
                        <span class="badge-status badge-status-selesai">Selesai</span>
                        <span class="item-pesanan-waktu">07:00 Rabu, 04-12-2026</span>
                    </div>
                    <p class="item-pesanan-nama">Budi Santoso</p>
                    <div class="item-pesanan-tags">
                        <span class="badge-hijau">Dry Cleaning</span>
                        <span class="badge-biru">Reguler</span>
                        <span class="badge-biru">Antar</span>
                    </div>
                </div>

                <div class="item-pesanan"
                     data-id="4"
                     data-status="baru"
                     onclick="bukaPesanan(4, this)">
                    <div class="item-pesanan-atas">
                        <span class="badge-status badge-status-baru">Baru</span>
                        <span class="item-pesanan-waktu">06:15 Rabu, 04-12-2026</span>
                    </div>
                    <p class="item-pesanan-nama">Mega Putri</p>
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
                <div style="width:80px;height:80px;border-radius:50%;background:rgba(13,63,138,0.08);margin-bottom:20px;"></div>
                <p style="color:#aaa;">Pilih pesanan untuk melihat detail</p>
            </div>

            <div class="detail-isi" id="detailIsi" style="display:none;">

                <button class="tombol-kembali" onclick="kembaliKeList()">← Kembali</button>

                <div class="detail-header">
                    <div>
                        <h2 class="detail-nama" id="detailNama">—</h2>
                        <p class="detail-username" id="detailUsername">—</p>
                    </div>
                    <div class="detail-waktu-badge" id="detailWaktu">—</div>
                </div>

                <div class="grup-keterangan" id="detailTags" style="margin-bottom:20px; flex-wrap:wrap;"></div>

                <div class="detail-info-grid">
                    <div class="detail-info-blok">
                        <p class="detail-label">Nama Lengkap</p>
                        <p class="detail-nilai" id="detailNamaLengkap">—</p>
                    </div>
                    <div class="detail-info-blok">
                        <p class="detail-label">Nomor Telepon</p>
                        <p class="detail-nilai" id="detailTelpon">—</p>
                    </div>
                    <div class="detail-info-blok">
                        <p class="detail-label">Alamat Lengkap</p>
                        <p class="detail-nilai" id="detailAlamat">—</p>
                    </div>
                    <div class="detail-info-blok">
                        <p class="detail-label">Kecamatan</p>
                        <p class="detail-nilai" id="detailKecamatan">—</p>
                    </div>
                    <div class="detail-info-blok">
                        <p class="detail-label">Layanan</p>
                        <p class="detail-nilai" id="detailLayanan">—</p>
                    </div>
                    <div class="detail-info-blok">
                        <p class="detail-label">Pengiriman</p>
                        <p class="detail-nilai" id="detailPengiriman">—</p>
                    </div>
                </div>

                <div class="detail-catatan-wrapper">
                    <p class="detail-label">Catatan dari Member</p>
                    <p class="detail-catatan-isi" id="detailNote">—</p>
                </div>

                <div class="detail-berat-biaya">

                    <div class="kartu-berat">
                        <p class="detail-label">Input Berat Aktual</p>
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
                        <p class="input-berat-hint">* Tarif dihitung otomatis</p>
                    </div>

                    <div class="kartu-biaya">
                        <p class="detail-label">Rincian Biaya</p>
                        <p class="rincian-baris" id="rincianLayanan">Layanan : Rp 0</p>
                        <p class="rincian-baris" id="rincianKirim">Pengiriman : Rp 0</p>
                        <p class="rincian-total" id="rincianTotal">Total : Rp 0</p>
                    </div>

                </div>
                <div class="detail-status-section">
                    <p class="detail-label" style="margin-bottom:12px;">Update Status Pesanan</p>
                    <div class="tombol-status-group" id="tombolStatusGroup">
                        <button class="tombol-status" data-status="baru"     onclick="updateStatus('baru')">Baru</button>
                        <button class="tombol-status" data-status="diproses" onclick="updateStatus('diproses')">Diproses</button>
                        <button class="tombol-status" data-status="selesai"  onclick="updateStatus('selesai')">Selesai</button>
                    </div>
                    <p class="status-aktif-teks">Status saat ini: <strong id="statusAktifTeks">—</strong></p>
                </div>

            </div>
        </div>
    </section>
    <script src="../assets/js/main.js"></script>
    <script src="../assets/js/status.refresh.js"></script>
    <script src="../assets/js/kalkulasi-harga.js"></script>
    <script src="../assets/js/form-validation.js"></script>
    
</body>
</html>
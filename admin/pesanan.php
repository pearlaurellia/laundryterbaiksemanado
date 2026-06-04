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
    <style>
        /* Kode pesanan sebagai penanda utama di list sidebar admin */
        .item-pesanan-kode {
            font-size: 1rem;
            font-weight: 700;
            color: #ffffff;
            background: #346E9E;
            margin: 4px 0 2px;
            letter-spacing: 0.02em;
            border-radius: 20px;
        }
        .item-pesanan-nama {
            font-size: 0.8rem;
            color: #ffffff;
            margin: 0 0 6px;
        }
    </style>
</head>
<body>

    <?php include '../includes/header-admin.php'; ?>

    <section class="halaman-pesanan" id="viewList">

        <div class="pesanan-sidebar">
            <h2 class="judul-sidebar">Daftar Pesanan</h2>

            <div class="grup-filter">
                <button class="tombol-filter aktif" onclick="filterPesanan('semua', this)">Semua</button>
                <button class="tombol-filter" onclick="filterPesanan('menunggu_konfirmasi', this)">Menunggu</button>
                <button class="tombol-filter" onclick="filterPesanan('dikonfirmasi', this)">Dikonfirmasi</button>
                <button class="tombol-filter" onclick="filterPesanan('sedang_dicuci', this)">Diproses</button>
                <button class="tombol-filter" onclick="filterPesanan('selesai', this)">Selesai</button>
            </div>

        <div class="list-pesanan" id="listPesanan">
        </div>

        </div><!-- /pesanan-sidebar -->

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
                    <p class="detail-label" style="margin-bottom:12px;">Aksi Pesanan</p>

                    <!-- Tombol aksi bertahap (diisi oleh status.refresh.js) -->
                    <div id="grupAksiAdmin" class="tombol-status-group"></div>

                    <p class="status-aktif-teks" style="margin-top:10px;">
                        Status saat ini: <strong id="statusAktifTeks">—</strong>
                    </p>

                    <!-- Batalkan (selalu tampil kecuali sudah selesai/dibatalkan) -->
                    <div style="margin-top:20px; padding-top:16px; border-top:1px solid rgba(13,63,138,0.1);">
                        <button
                            class="tombol-batalkan-status"
                            id="tombolBatalkanAdmin"
                            onclick="batalkanPesananAdmin(idAktif)"
                            style="display:none;">
                            Batalkan Pesanan Ini
                        </button>
                        <p class="status-aktif-teks"
                        id="infoSudahDibatalkan"
                        style="display:none; color:#f87171; font-weight:600;">
                            ✕ Pesanan ini sudah dibatalkan
                        </p>
                    </div>
                </div>

            </div>
        </div>
        <!-- Popup konfirmasi batal -->
        <div id="overlayBatalAdmin" class="overlay-popup"
            style="display:none;" onclick="tutupPopupBatalAdmin()"></div>

        <div id="popupBatalAdmin" class="popup-konfirmasi"
            style="display:none; width:440px; max-width:92vw;">

            <h3 class="popup-judul">Batalkan Pesanan?</h3>
            <p id="popupBatalAdminTeks" class="popup-teks"></p>

            <div style="margin-bottom:20px;">
                <p class="detail-label" style="margin-bottom:12px;">
                    Pilih Alasan Pembatalan
                </p>

                <div style="display:flex; flex-direction:column; gap:8px;">

                    <label class="kartu-alasan">
                        <input type="radio" name="alasanBatal"
                            value="Kuota laundry hari ini penuh, silakan pesan kembali besok.">
                        <div class="kartu-alasan-isi">
                            <span class="kartu-alasan-ikon">📦</span>
                            <div>
                                <p class="kartu-alasan-judul">Laundry Penuh</p>
                                <p class="kartu-alasan-sub">Kuota hari ini sudah penuh</p>
                            </div>
                        </div>
                    </label>

                    <label class="kartu-alasan">
                        <input type="radio" name="alasanBatal"
                            value="Pesanan terindikasi fiktif / pengguna tidak dapat dihubungi.">
                        <div class="kartu-alasan-isi">
                            <span class="kartu-alasan-ikon">⚠️</span>
                            <div>
                                <p class="kartu-alasan-judul">Pesanan Fiktif</p>
                                <p class="kartu-alasan-sub">Pengguna tidak dapat dihubungi</p>
                            </div>
                        </div>
                    </label>

                    <label class="kartu-alasan">
                        <input type="radio" name="alasanBatal"
                            value="Alamat pengantaran tidak valid atau di luar jangkauan kurir.">
                        <div class="kartu-alasan-isi">
                            <span class="kartu-alasan-ikon">📍</span>
                            <div>
                                <p class="kartu-alasan-judul">Alamat Tidak Valid</p>
                                <p class="kartu-alasan-sub">Di luar jangkauan atau tidak ditemukan</p>
                            </div>
                        </div>
                    </label>

                    <label class="kartu-alasan">
                        <input type="radio" name="alasanBatal" value="lainnya">
                        <div class="kartu-alasan-isi">
                            <span class="kartu-alasan-ikon">📝</span>
                            <div>
                                <p class="kartu-alasan-judul">Lainnya</p>
                                <p class="kartu-alasan-sub">Alasan lain dari admin</p>
                            </div>
                        </div>
                    </label>

                </div>

                <!-- Input teks: hanya muncul jika pilih "Lainnya" -->
                <div id="wrapperAlasanLainnya" style="display:none; margin-top:10px;">
                    <input type="text"
                        id="inputAlasanLainnya"
                        placeholder="Tulis alasan di sini..."
                        style="width:100%; padding:10px 14px; font-size:0.88rem;
                                border:1.5px solid #e0e0e0; border-radius:20px 0 20px 0;
                                font-family:'DM Sans',sans-serif; outline:none;">
                </div>
            </div>

            <div class="popup-tombol-group">
                <button class="popup-tombol-batal" onclick="tutupPopupBatalAdmin()">
                    Tidak
                </button>
                <button class="popup-tombol-konfirm" onclick="eksekusiBatalAdmin()">
                    Ya, Batalkan
                </button>
            </div>
        </div>

        <script src="../assets/js/main.js"></script>
        <script src="../assets/js/status.refresh.js"></script>
        <script src="../assets/js/kalkulasi-harga.js"></script>
        <script src="../assets/js/form-validation.js"></script>

        <script>
            // Toggle input "Lainnya"
            document.addEventListener('change', function(e) {
                if (e.target.name !== 'alasanBatal') return;
                const wrapper = document.getElementById('wrapperAlasanLainnya');
                wrapper.style.display = e.target.value === 'lainnya' ? 'block' : 'none';
            });

            // Render list saat halaman dimuat
            document.addEventListener('DOMContentLoaded', function() {
                renderListPesanan('semua');
            });
        </script>
</body>
</html>
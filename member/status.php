<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Status Pesanan - CleanCo</title>
    <link rel="stylesheet" href="../assets/css/style.css">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Bricolage+Grotesque:opsz,wght@12..96,200..800&display=swap" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=DM+Sans:ital,opsz,wght@0,9..40,100..1000;1,9..40,100..1000&display=swap" rel="stylesheet">
</head>
<body>

    <?php include '../includes/header-member.php'; ?>

    <!--
    ============================================================
     BACKEND OVERVIEW — member/status.php
     Query utama:
       SELECT p.*, l.nama_layanan, l.tarif_per_kg
       FROM pesanan p
       JOIN layanan l ON p.layanan_id = l.id
       WHERE p.member_id = $_SESSION['user_id']
         AND p.status_pesanan NOT IN ('selesai', 'dibatalkan')
       ORDER BY p.created_at DESC

     Jika tidak ada pesanan aktif → tampilkan .status-kosong
     Jika ada → ulangi .kartu-status-pesanan dengan foreach

     Untuk badge notifikasi navbar:
       SELECT COUNT(*) FROM pesanan
       WHERE member_id = ? AND status_updated_at > last_seen_at
       (simpan last_seen_at ke session saat halaman ini dibuka)
    ============================================================
    -->

    <section class="status-section">

        <div class="status-header">
            <h1 class="status-judul">Status Pesanan Aktif</h1>
            <p class="status-subjudul">
                Halaman ini otomatis diperbarui setiap 60 detik.
            </p>
        </div>

        <!-- =====================================================
             KARTU PESANAN AKTIF
             BACKEND: foreach($pesananAktif as $p) { ... }
        ====================================================== -->
        <div class="status-list" id="statusList">


            <!-- ─────────────────────────────────────────────
                 KARTU 1: Jalur KURIR, belum ditimbang
                 status_pesanan = 'menunggu'
                 berat_aktual   = 0
                 opsi           = 'kurir'
            ──────────────────────────────────────────────── -->
            <di class="kartu-status-pesanan"
                 data-id="LDR-0042"
                 data-status="menunggu"
                 data-opsi="kurir">

                <!-- Header kartu -->
                <div class="kartu-status-header">
                    <div class="kartu-status-header-kiri">
                        <!--
                            BACKEND:
                            $p['kode_pesanan'], $p['nama_layanan']
                            $p['created_at'] diformat ke waktu lokal
                        -->
                        <h3 class="kartu-status-kode">#LDR-0042</h3>
                        <p class="kartu-status-meta">
                            Express · Kurir · 10:00 Rabu, 04-12-2026
                        </p>
                    </div>
                    <div class="kartu-status-header-kanan">
                        <!--
                            BACKEND: class badge sesuai $p['status_pesanan']
                            menunggu      → badge-status-baru
                            sedang_dicuci / sedang_diantar → badge-status-diproses
                            siap_diambil  → badge-status-selesai
                        -->
                        <span class="badge-status badge-status-baru">
                            Menunggu Penjemputan
                        </span>
                    </div>
                </div>

                <!-- Progress bar — jalur KURIR -->
                <!--
                    BACKEND: tampilkan .progress-kurir jika
                    $p['opsi_pengantaran'] === 'kurir'
                    Step aktif ditentukan dari $p['status_pesanan']:
                      menunggu      → step 1 aktif
                      sedang_dicuci → step 2 aktif
                      sedang_diantar → step 3 aktif
                      selesai       → step 4 aktif
                -->
                <div class="progress-bar-wrapper progress-kurir">
                    <div class="progress-bar-track">

                        <div class="step-progress step-aktif">
                            <div class="step-lingkaran">1</div>
                            <p class="step-label">Menunggu</p>
                        </div>

                        <div class="garis-progress"></div>

                        <div class="step-progress">
                            <div class="step-lingkaran">2</div>
                            <p class="step-label">Sedang Dicuci</p>
                        </div>

                        <div class="garis-progress"></div>

                        <div class="step-progress">
                            <div class="step-lingkaran">3</div>
                            <p class="step-label">Sedang Diantar</p>
                        </div>

                        <div class="garis-progress"></div>

                        <div class="step-progress">
                            <div class="step-lingkaran">4</div>
                            <p class="step-label">Selesai</p>
                        </div>

                    </div>
                </div>

                <div class="kartu-status-body">
                    <div class="kotak-belum-timbang">
                        <span class="belum-timbang-ikon">⚖️</span>
                        <div>
                            <p class="belum-timbang-judul">
                                Menunggu Penimbangan Admin
                            </p>
                            <p class="belum-timbang-sub">
                                Harga final akan muncul setelah pakaian ditimbang.
                            </p>
                        </div>
                    </div>

                    <div class="kartu-status-info-kurir">
                        <span class="info-kurir-ikon">🛵</span>
                        <div>

                            <p class="info-kurir-label">Kurir ke Wanea</p>
                            <p class="info-kurir-alamat">
                                Jl. Paal 4 No. 12, Ling. III
                            </p>
                        </div>
                    </div>
                </div>

                <!-- Aksi kartu -->
                <div class="kartu-status-aksi">
                    <a href="detail-pesanan.php?id=LDR-0042"
                       class="tombol-detail-status">
                        Lihat Detail
                    </a>

                    <button class="tombol-batalkan-status"
                            onclick="konfirmasiBatal('LDR-0042', 'Express')">
                        Batalkan
                    </button>
                </div>

            </div>
            <div class="kartu-status-pesanan"
                 data-id="LDR-0038"
                 data-status="sedang_dicuci"
                 data-opsi="kurir">

                <div class="kartu-status-header">
                    <div class="kartu-status-header-kiri">
                        <h3 class="kartu-status-kode">#LDR-0038</h3>
                        <p class="kartu-status-meta">
                            Reguler · Kurir · 08:00 Rabu, 04-12-2026
                        </p>
                    </div>
                    <div class="kartu-status-header-kanan">
                        <span class="badge-status badge-status-diproses">
                            Sedang Dicuci
                        </span>
                    </div>
                </div>

                <!-- Progress bar — jalur KURIR, step 2 aktif -->
                <div class="progress-bar-wrapper progress-kurir">
                    <div class="progress-bar-track">

                        <div class="step-progress step-selesai">
                            <div class="step-lingkaran">✓</div>
                            <p class="step-label">Menunggu</p>
                        </div>

                        <div class="garis-progress garis-selesai"></div>

                        <div class="step-progress step-aktif">
                            <div class="step-lingkaran">2</div>
                            <p class="step-label">Sedang Dicuci</p>
                        </div>

                        <div class="garis-progress"></div>

                        <div class="step-progress">
                            <div class="step-lingkaran">3</div>
                            <p class="step-label">Sedang Diantar</p>
                        </div>

                        <div class="garis-progress"></div>

                        <div class="step-progress">
                            <div class="step-lingkaran">4</div>
                            <p class="step-label">Selesai</p>
                        </div>

                    </div>
                </div>


                <div class="kartu-status-body">
                    <div class="kotak-harga-final-status">
                        <div class="harga-final-baris">
                            <span class="harga-final-label">Berat Aktual</span>
                            <strong class="harga-final-nilai">3.0 kg</strong>
                        </div>
                        <div class="harga-final-baris">
                            <span class="harga-final-label">Total Harga</span>
                            <strong class="harga-final-nilai harga-final-besar">
                                Rp 34.000
                                <span class="label-final">(Final)</span>
                            </strong>
                        </div>
                    </div>

                    <div class="kartu-status-info-kurir">
                        <span class="info-kurir-ikon">🛵</span>
                        <div>
                            <p class="info-kurir-label">Kurir ke Malalayang</p>
                            <p class="info-kurir-alamat">
                                Jl. Bahu Lingkungan I No. 5
                            </p>
                        </div>
                    </div>
                </div>

                <div class="kartu-status-aksi">
                    <a href="detail-pesanan.php?id=LDR-0038"
                       class="tombol-detail-status">
                        Lihat Detail
                    </a>

                </div>

            </div>

            <div class="kartu-status-pesanan"
                 data-id="LDR-0031"
                 data-status="siap_diambil"
                 data-opsi="ambil_sendiri">

                <div class="kartu-status-header">
                    <div class="kartu-status-header-kiri">
                        <h3 class="kartu-status-kode">#LDR-0031</h3>
                        <p class="kartu-status-meta">
                            Express · Ambil Sendiri · 07:00 Selasa, 03-12-2026
                        </p>
                    </div>
                    <div class="kartu-status-header-kanan">
                        <span class="badge-status badge-status-selesai">
                            Siap Diambil
                        </span>
                    </div>
                </div>

                <div class="banner-status banner-hijau"
                     style="margin: 0 0 0 0; border-radius: 0;">
                    <span class="banner-ikon">✓</span>
                    <p>
                        Cucian kamu siap diambil!
                        Datang ke outlet dan bayar saat pengambilan.
                    </p>
                </div>

                <div class="progress-bar-wrapper progress-ambil-sendiri"
                     style="border-radius:0;">
                    <div class="progress-bar-track">

                        <div class="step-progress step-selesai">
                            <div class="step-lingkaran">✓</div>
                            <p class="step-label">Menunggu</p>
                        </div>

                        <div class="garis-progress garis-selesai"></div>

                        <div class="step-progress step-selesai">
                            <div class="step-lingkaran">✓</div>
                            <p class="step-label">Sedang Dicuci</p>
                        </div>

                        <div class="garis-progress garis-selesai"></div>

                        <div class="step-progress step-aktif">
                            <div class="step-lingkaran">3</div>
                            <p class="step-label">Siap Diambil</p>
                        </div>

                        <div class="garis-progress"></div>

                        <div class="step-progress">
                            <div class="step-lingkaran">4</div>
                            <p class="step-label">Selesai</p>
                        </div>

                    </div>
                </div>

                <div class="kartu-status-body">
                    <div class="kotak-harga-final-status">
                        <div class="harga-final-baris">
                            <span class="harga-final-label">Berat Aktual</span>
                            <strong class="harga-final-nilai">2.5 kg</strong>
                        </div>
                        <div class="harga-final-baris">
                            <span class="harga-final-label">Total Harga</span>
                            <strong class="harga-final-nilai harga-final-besar">
                                Rp 37.500
                                <span class="label-final">(Final)</span>
                            </strong>
                        </div>
                    </div>

                    <div class="kartu-status-info-kurir">
                        <span class="info-kurir-ikon">🏬</span>
                        <div>
                            <p class="info-kurir-label">Ambil di Outlet</p>
                            <p class="info-kurir-alamat">
                                Wanea, Teling Atas, Jln. Manado
                            </p>
                        </div>
                    </div>
                </div>

                <div class="kartu-status-aksi">
                    <a href="detail-pesanan.php?id=LDR-0031"
                       class="tombol-detail-status">
                        Lihat Detail
                    </a>
                </div>

            </div>


        </div>

        <div class="status-kosong" id="statusKosong" style="display:none;">
            <div class="status-kosong-ikon">🧺</div>
            <h2 class="status-kosong-judul">Tidak ada pesanan aktif</h2>
            <p class="status-kosong-sub">
                Semua pesanan kamu sudah selesai atau belum ada yang dipesan.
            </p>
            <a href="pesan.php" class="tombol-submit-form"
               style="text-decoration:none; display:inline-block; margin-top:10px;">
                Buat Pesanan Baru
            </a>
        </div>

    </section>


    <div class="overlay-popup" id="overlayPopup"
         style="display:none;" onclick="tutupPopupBatal()"></div>

    <div class="popup-konfirmasi" id="popupBatal" style="display:none;">
        <h3 class="popup-judul">Batalkan Pesanan?</h3>
        <p class="popup-teks" id="popupBatalTeks">
            Pesanan ini akan dibatalkan dan tidak dapat dikembalikan.
        </p>
        <div class="popup-tombol-group">
            <button class="popup-tombol-batal"
                    onclick="tutupPopupBatal()">
                Tidak
            </button>

            <button class="popup-tombol-konfirm"
                    id="tombolKonfirmasiYa"
                    style="background-color:#f87171; color:white;"
                    onclick="eksekusiBatal()">
                Ya, Batalkan
            </button>
        </div>
    </div>


    <script src="../assets/js/status_refresh.js"></script>

    <script>
        let idPesananAkanDibatal = null;

        function konfirmasiBatal(idPesanan, namaLayanan) {
            idPesananAkanDibatal = idPesanan;
            document.getElementById('popupBatalTeks').textContent =
                `Pesanan #${idPesanan} (${namaLayanan}) akan dibatalkan dan tidak dapat dikembalikan.`;
            document.getElementById('overlayPopup').style.display = 'block';
            document.getElementById('popupBatal').style.display   = 'block';
        }

        function tutupPopupBatal() {
            idPesananAkanDibatal = null;
            document.getElementById('overlayPopup').style.display = 'none';
            document.getElementById('popupBatal').style.display   = 'none';
        }

        function eksekusiBatal() {
            if (!idPesananAkanDibatal) return;

            const kartuEl = document.querySelector(
                `.kartu-status-pesanan[data-id="${idPesananAkanDibatal}"]`
            );

            if (kartuEl) {
                kartuEl.style.transition = 'opacity 0.3s, transform 0.3s';
                kartuEl.style.opacity    = '0';
                kartuEl.style.transform  = 'translateY(-10px)';
                setTimeout(() => {
                    kartuEl.remove();
                    cekKosong();
                }, 300);
            }

            tutupPopupBatal();
        }

        function cekKosong() {
            const ada = document.querySelectorAll('.kartu-status-pesanan').length > 0;
            document.getElementById('statusKosong').style.display = ada ? 'none' : 'flex';
        }

        startAutoRefresh();
    </script>

</body>
</html>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Detail Pesanan - CleanCo</title>
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
     BACKEND OVERVIEW — member/detail-pesanan.php
     GET: ?id=$kode_pesanan
     Query: SELECT p.*, l.nama_layanan, l.tarif_per_kg
            FROM pesanan p
            JOIN layanan l ON p.layanan_id = l.id
            WHERE p.id = ? AND p.member_id = $_SESSION['user_id']

     Variabel PHP yang dibutuhkan di halaman ini:
       $pesanan     → row data pesanan
       $sudahDitimbang → $pesanan['berat_aktual'] > 0
       $statusPesanan  → $pesanan['status_pesanan']
       $opsiKurir      → $pesanan['opsi_pengantaran'] === 'kurir'
    ============================================================
    -->

    <section class="detail-pesanan-section">

        <!-- Tombol kembali -->
        <a href="status.php" class="tombol-kembali-member">← Kembali ke Status</a>

        <!-- ── BANNER STATUS KONDISIONAL ── -->
        <!--
            BACKEND NOTE:
            Tampilkan banner yang sesuai berdasarkan $statusPesanan.
            Hanya satu banner yang muncul dalam satu waktu.
            Gunakan PHP if/elseif untuk mengontrol mana yang ditampilkan.
        -->

        <!-- Banner: Siap Diambil (status = 'siap_diambil') -->
        <div class="banner-status banner-hijau" id="bannerSiapDiambil">
            <span class="banner-ikon">✓</span>
            <p>Cucian kamu siap diambil! Datang ke outlet dan bayar saat pengambilan.</p>
        </div>

        <!-- Banner: Sedang Diantar (status = 'sedang_diantar') -->
        <div class="banner-status banner-biru" id="bannerSedangDiantar"
             style="display:none;">
            <span class="banner-ikon">🛵</span>
            <p>Cucian kamu sedang dalam perjalanan ke alamat kamu!</p>
        </div>

        <!-- ── HEADER PESANAN ── -->
        <div class="detail-pesanan-header">
            <div>
                <!--
                    BACKEND: <?= $pesanan['kode_pesanan'] ?>
                             <?= $pesanan['nama_layanan'] ?>
                -->
                <h1 class="detail-pesanan-judul">#LDR-0042</h1>
                <p class="detail-pesanan-sub">Express · Dibuat 10:00 Rabu, 04-12-2026</p>
            </div>
            <!--
                BACKEND: class badge sesuai status:
                menunggu → badge-status-baru
                sedang_dicuci / sedang_diantar → badge-status-diproses
                siap_diambil / selesai → badge-status-selesai
                dibatalkan → badge-status-batal
            -->
            <span class="badge-status badge-status-diproses">Sedang Dicuci</span>
        </div>

        <!-- ── PROGRESS BAR ── -->
        <!--
            BACKEND NOTE:
            Progress bar memiliki dua versi: ambil_sendiri dan kurir.
            Tampilkan yang sesuai berdasarkan $pesanan['opsi_pengantaran'].
            Step yang aktif ditentukan dari $statusPesanan.
            Gunakan PHP untuk menambahkan class 'step-aktif' dan 'step-selesai'
            pada .step-progress yang sesuai.
        -->

        <!-- Jalur Kurir -->
        <div class="progress-bar-wrapper" id="progressKurir">
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

        <!-- Jalur Ambil Sendiri (sembunyikan jika kurir) -->
        <div class="progress-bar-wrapper" id="progressAmbilSendiri"
             style="display:none;">
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
                    <p class="step-label">Siap Diambil</p>
                </div>

                <div class="garis-progress"></div>

                <div class="step-progress">
                    <div class="step-lingkaran">4</div>
                    <p class="step-label">Selesai</p>
                </div>

            </div>
        </div>

        <!-- ── INFO GRID ── -->
        <div class="detail-pesanan-grid">

            <!-- Kiri: Info pesanan -->
            <div class="detail-pesanan-kiri">

                <div class="detail-info-grid" style="margin-bottom:20px;">
                    <div class="detail-info-blok">
                        <p class="detail-label">Layanan</p>
                        <!-- BACKEND: <?= $pesanan['nama_layanan'] ?> -->
                        <p class="detail-nilai">Express</p>
                    </div>
                    <div class="detail-info-blok">
                        <p class="detail-label">Pengantaran</p>
                        <!-- BACKEND: Kurir / Ambil Sendiri -->
                        <p class="detail-nilai">Kurir Laundry</p>
                    </div>
                    <div class="detail-info-blok">
                        <p class="detail-label">Kecamatan Tujuan</p>
                        <!-- BACKEND: <?= $pesanan['kecamatan'] ?> -->
                        <p class="detail-nilai">Wanea</p>
                    </div>
                    <div class="detail-info-blok">
                        <p class="detail-label">Alamat Lengkap</p>
                        <!-- BACKEND: <?= $pesanan['alamat_pengantaran'] ?> -->
                        <p class="detail-nilai">Jl. Paal 4 No. 12, Ling. III</p>
                    </div>
                    <div class="detail-info-blok">
                        <p class="detail-label">Estimasi Berat</p>
                        <!-- BACKEND: $pesanan['estimasi_berat'] ?? 'Tidak diisi' -->
                        <p class="detail-nilai">2 kg (estimasi)</p>
                    </div>
                    <div class="detail-info-blok">
                        <p class="detail-label">Status Pembayaran</p>
                        <!-- BACKEND: Belum Bayar / Lunas -->
                        <p class="detail-nilai" style="color:#f59e0b; font-weight:700;">
                            Belum Bayar
                        </p>
                    </div>
                </div>

                <!-- Catatan member -->
                <div class="detail-catatan-wrapper">
                    <p class="detail-label">Catatan Kamu</p>
                    <!--
                        BACKEND: tampilkan $pesanan['catatan']
                        Jika kosong: "Tidak ada catatan."
                    -->
                    <p class="detail-catatan-isi">Tolong pisahkan baju putih.</p>
                </div>

            </div>

            <!-- Kanan: Berat & Biaya -->
            <div class="detail-pesanan-kanan">

                <!--
                    BACKEND NOTE:
                    Blok ini tampil KONDISIONAL:
                    - Jika $sudahDitimbang = false → tampilkan .kotak-menunggu-timbang
                    - Jika $sudahDitimbang = true  → tampilkan .kotak-harga-final
                -->

                <!-- Belum ditimbang -->
                <div class="kotak-menunggu-timbang" id="kotakMenunggu">
                    <div class="menunggu-ikon">⚖️</div>
                    <p class="menunggu-judul">Menunggu Penimbangan Admin</p>
                    <p class="menunggu-sub">
                        Harga final akan muncul setelah admin menimbang pakaian kamu di outlet.
                    </p>
                </div>

                <!-- Sudah ditimbang (sembunyikan jika belum) -->
                <div class="kotak-harga-final" id="kotakHargaFinal"
                     style="display:none;">
                    <p class="detail-label" style="margin-bottom:12px;">Rincian Biaya Final</p>
                    <!--
                        BACKEND:
                        $beratAktual = $pesanan['berat_aktual']
                        $tarifPaket  = $pesanan['tarif_per_kg']
                        $biayaKurir  = $pesanan['biaya_kurir']
                        $totalHarga  = $pesanan['total_harga']
                    -->
                    <p class="rincian-baris">Express (4.2 kg × Rp 15.000) : Rp 63.000</p>
                    <p class="rincian-baris">Kurir : Rp 10.000</p>
                    <p class="rincian-total">Total : Rp 73.000 <span class="label-final">(Harga Final)</span></p>

                    <div class="berat-aktual-badge">
                        <span>⚖️ Berat Aktual</span>
                        <strong>4.2 kg</strong>
                    </div>
                </div>

                <!-- Timeline riwayat status -->
                <!--
                    BACKEND NOTE:
                    Query: SELECT * FROM riwayat_status
                           WHERE pesanan_id = ? ORDER BY created_at ASC
                    Ulangi .timeline-item dengan foreach($riwayat as $r)
                -->
                <div class="timeline-status">
                    <p class="detail-label" style="margin-bottom:14px;">Riwayat Status</p>

                    <div class="timeline-item timeline-item-selesai">
                        <div class="timeline-dot"></div>
                        <div class="timeline-konten">
                            <p class="timeline-status-teks">Pesanan Dibuat</p>
                            <p class="timeline-waktu">10:00 — Rabu, 04-12-2026</p>
                        </div>
                    </div>

                    <div class="timeline-item timeline-item-selesai">
                        <div class="timeline-dot"></div>
                        <div class="timeline-konten">
                            <p class="timeline-status-teks">Pakaian Diterima Outlet</p>
                            <p class="timeline-waktu">11:30 — Rabu, 04-12-2026</p>
                        </div>
                    </div>

                    <div class="timeline-item timeline-item-aktif">
                        <div class="timeline-dot timeline-dot-aktif"></div>
                        <div class="timeline-konten">
                            <p class="timeline-status-teks">Sedang Dicuci</p>
                            <p class="timeline-waktu">12:00 — Rabu, 04-12-2026</p>
                        </div>
                    </div>

                    <div class="timeline-item">
                        <div class="timeline-dot timeline-dot-kosong"></div>
                        <div class="timeline-konten">
                            <p class="timeline-status-teks" style="color:#ccc;">
                                Sedang Diantar
                            </p>
                        </div>
                    </div>

                    <div class="timeline-item" style="border-left:none;">
                        <div class="timeline-dot timeline-dot-kosong"></div>
                        <div class="timeline-konten">
                            <p class="timeline-status-teks" style="color:#ccc;">Selesai</p>
                        </div>
                    </div>

                </div>

            </div>

        </div>

    </section>

</body>
</html>
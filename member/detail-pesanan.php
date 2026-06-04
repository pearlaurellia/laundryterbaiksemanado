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
       $pesanan        → row data pesanan
       $sudahDitimbang → $pesanan['berat_aktual'] > 0
       $statusPesanan  → $pesanan['status_pesanan']
       $opsiKurir      → $pesanan['opsi_pengantaran'] === 'kurir'
    ============================================================
    -->

    <section class="detail-pesanan-section" id="detailSection" style="display:none;">

        <!-- Tombol kembali -->
        <a href="status.php" class="tombol-kembali-member">← Kembali ke Status</a>

        <!-- ── BANNER STATUS KONDISIONAL ── -->
        <!-- Diisi oleh JS berdasarkan status pesanan di localStorage -->
        <div id="bannerContainer"></div>

        <!-- ── HEADER PESANAN ── -->
        <div class="detail-pesanan-header">
            <div>
                <h1 class="detail-pesanan-judul" id="headerKode">—</h1>
                <p class="detail-pesanan-sub" id="headerSub">—</p>
            </div>
            <span class="badge-status" id="headerBadge">—</span>
        </div>

        <!-- ── PROGRESS BAR ── -->
        <!-- Diisi oleh JS sesuai opsi pengantaran dan status saat ini -->
        <div class="progress-bar-wrapper" id="progressBarWrapper">
            <div class="progress-bar-track" id="progressBarTrack"></div>
        </div>

        <!-- ── INFO GRID ── -->
        <div class="detail-pesanan-grid">

            <!-- Kiri: Info pesanan -->
            <div class="detail-pesanan-kiri">
                <div class="detail-info-grid" style="margin-bottom:20px;">
                    <div class="detail-info-blok">
                        <p class="detail-label">Layanan</p>
                        <p class="detail-nilai" id="infoLayanan">—</p>
                    </div>
                    <div class="detail-info-blok">
                        <p class="detail-label">Pengantaran</p>
                        <p class="detail-nilai" id="infoPengantaran">—</p>
                    </div>
                    <div class="detail-info-blok" id="blokKecamatan">
                        <p class="detail-label">Kecamatan Tujuan</p>
                        <p class="detail-nilai" id="infoKecamatan">—</p>
                    </div>
                    <div class="detail-info-blok" id="blokAlamat">
                        <p class="detail-label">Alamat Lengkap</p>
                        <p class="detail-nilai" id="infoAlamat">—</p>
                    </div>
                    <div class="detail-info-blok">
                        <p class="detail-label">Estimasi Berat</p>
                        <p class="detail-nilai" id="infoEstimasiBerat">—</p>
                    </div>
                    <div class="detail-info-blok">
                        <p class="detail-label">Status Pembayaran</p>
                        <p class="detail-nilai" id="infoPembayaran">—</p>
                    </div>
                </div>

                <div class="detail-catatan-wrapper">
                    <p class="detail-label">Catatan Kamu</p>
                    <p class="detail-catatan-isi" id="infoCatatan">—</p>
                </div>
            </div>

            <!-- Kanan: Berat & Biaya + Timeline -->
            <div class="detail-pesanan-kanan">

                <!-- Belum ditimbang -->
                <div class="kotak-menunggu-timbang" id="kotakMenunggu" style="display:none;">
                    <div class="menunggu-ikon">⚖️</div>
                    <p class="menunggu-judul">Menunggu Penimbangan Admin</p>
                    <p class="menunggu-sub">
                        Harga final akan muncul setelah admin menimbang pakaian kamu di outlet.
                    </p>
                </div>

                <!-- Sudah ditimbang -->
                <div class="kotak-harga-final" id="kotakHargaFinal" style="display:none;">
                    <p class="detail-label" style="margin-bottom:12px;">Rincian Biaya Final</p>
                    <p class="rincian-baris" id="rincianLayananFinal">—</p>
                    <p class="rincian-baris" id="rincianKurirFinal">—</p>
                    <p class="rincian-total" id="rincianTotalFinal">—</p>
                    <div class="berat-aktual-badge">
                        <span>⚖️ Berat Aktual</span>
                        <strong id="beratAktualBadge">—</strong>
                    </div>
                </div>

                <!-- Timeline riwayat status -->
                <!--
                    BACKEND NOTE:
                    Query: SELECT * FROM riwayat_status
                           WHERE pesanan_id = ? ORDER BY created_at ASC
                    Ulangi .timeline-item dengan foreach($riwayat as $r)
                    Untuk sementara, timeline dibangun dari data localStorage.
                -->
                <div class="timeline-status" id="timelineContainer">
                    <p class="detail-label" style="margin-bottom:14px;">Riwayat Status</p>
                    <div id="timelineIsi"></div>
                </div>

            </div>

        </div>

    </section>

    <!-- Tampilan saat data tidak ditemukan -->
    <section class="detail-pesanan-section" id="sectionTidakDitemukan" style="display:none;">
        <a href="status.php" class="tombol-kembali-member">← Kembali ke Status</a>
        <div style="text-align:center; padding:60px 20px; color:#aaa;">
            <p style="font-size:3rem;">🔍</p>
            <h2 style="color:#555; margin-bottom:8px;">Pesanan tidak ditemukan</h2>
            <p>Kode pesanan ini tidak ada di riwayat kamu.</p>
        </div>
    </section>

    <script src="../assets/js/main.js"></script>

    <script>
    (function renderDetailPesanan() {

        // ── Ambil kode dari URL: ?id=LDR-20261204-3847 ──────────
        const params     = new URLSearchParams(window.location.search);
        const kodeDiUrl  = params.get('id') || '';
        const semua      = _muatData(); // dari main.js

        // Cari pesanan yang kodenya cocok
        const p = Object.values(semua).find(item =>
            item.kode === kodeDiUrl || item.kode === kodeDiUrl.replace('#', '')
        );

        if (!p) {
            document.getElementById('sectionTidakDitemukan').style.display = 'block';
            return;
        }

        document.getElementById('detailSection').style.display = 'block';

        const fmt = n => 'Rp ' + n.toLocaleString('id-ID');

        // ── LABEL & KELAS BADGE ──────────────────────────────────
        const labelStatus = {
            menunggu_konfirmasi : 'Menunggu Konfirmasi',
            dikonfirmasi        : 'Dikonfirmasi',
            sedang_dicuci       : 'Sedang Dicuci',
            siap_diambil        : 'Siap Diambil',
            sedang_diantar      : 'Sedang Diantar',
            selesai             : 'Selesai & Lunas',
            dibatalkan          : 'Dibatalkan'
        };
        const kelasStatus = {
            menunggu_konfirmasi : 'badge-status-baru',
            dikonfirmasi        : 'badge-status-dikonfirmasi',
            sedang_dicuci       : 'badge-status-diproses',
            siap_diambil        : 'badge-status-selesai',
            sedang_diantar      : 'badge-status-diproses',
            selesai             : 'badge-status-selesai',
            dibatalkan          : 'badge-status-batal'
        };

        // ── HEADER ───────────────────────────────────────────────
        document.getElementById('headerKode').textContent = '#' + p.kode;
        document.getElementById('headerSub').textContent  =
            p.layanan + ' · Dibuat ' + p.waktu;

        const badgeEl = document.getElementById('headerBadge');
        badgeEl.textContent  = labelStatus[p.status] || p.status;
        badgeEl.className    = 'badge-status ' + (kelasStatus[p.status] || '');

        // ── BANNER ───────────────────────────────────────────────
        const bannerEl = document.getElementById('bannerContainer');
        if (p.status === 'siap_diambil') {
            bannerEl.innerHTML = `
                <div class="banner-status banner-hijau">
                    <span class="banner-ikon">✓</span>
                    <p>Cucian kamu siap diambil! Datang ke outlet dan bayar saat pengambilan.</p>
                </div>`;
        } else if (p.status === 'sedang_diantar') {
            bannerEl.innerHTML = `
                <div class="banner-status banner-biru">
                    <span class="banner-ikon">🛵</span>
                    <p>Cucian kamu sedang dalam perjalanan ke alamat kamu!</p>
                </div>`;
        } else if (p.status === 'dibatalkan') {
            bannerEl.innerHTML = `
                <div class="banner-status" style="background:#fff5f5; border:1px solid #f87171;">
                    <span class="banner-ikon">✕</span>
                    <p style="color:#D32F2F;">Pesanan ini telah dibatalkan.
                        ${p.alasanBatal ? '<br><strong>Alasan:</strong> ' + p.alasanBatal : ''}
                    </p>
                </div>`;
        }

        // ── PROGRESS BAR ─────────────────────────────────────────
        // Dua jalur berbeda sesuai opsi pengantaran, 5 step masing-masing
        const stepsKurir = [
            { key: 'menunggu_konfirmasi', label: 'Menunggu' },
            { key: 'dikonfirmasi',        label: 'Dikonfirmasi' },
            { key: 'sedang_dicuci',       label: 'Sedang Dicuci' },
            { key: 'sedang_diantar',      label: 'Sedang Diantar' },
            { key: 'selesai',             label: 'Selesai' }
        ];
        const stepsAmbil = [
            { key: 'menunggu_konfirmasi', label: 'Menunggu' },
            { key: 'dikonfirmasi',        label: 'Dikonfirmasi' },
            { key: 'sedang_dicuci',       label: 'Sedang Dicuci' },
            { key: 'siap_diambil',        label: 'Siap Diambil' },
            { key: 'selesai',             label: 'Selesai' }
        ];
        const steps    = p.opsi === 'kurir' ? stepsKurir : stepsAmbil;
        const aktifIdx = steps.findIndex(s => s.key === p.status);
        const posAktif = aktifIdx >= 0 ? aktifIdx : 0;

        document.getElementById('progressBarTrack').innerHTML = steps.map((step, i) => {
            let kelas = 'step-progress';
            let isi   = i + 1;
            if (i < posAktif)        { kelas += ' step-selesai'; isi = '✓'; }
            else if (i === posAktif) { kelas += ' step-aktif'; }
            const garis = i < steps.length - 1
                ? `<div class="garis-progress ${i < posAktif ? 'garis-selesai' : ''}"></div>`
                : '';
            return `<div class="${kelas}">
                        <div class="step-lingkaran">${isi}</div>
                        <p class="step-label">${step.label}</p>
                    </div>${garis}`;
        }).join('');

        // ── INFO GRID ────────────────────────────────────────────
        document.getElementById('infoLayanan').textContent     = p.layanan || '—';
        document.getElementById('infoPengantaran').textContent =
            p.opsi === 'kurir' ? 'Kurir Laundry' : 'Ambil Sendiri';

        // Kecamatan & alamat: hanya tampil jika kurir
        if (p.opsi === 'kurir') {
            document.getElementById('infoKecamatan').textContent = p.kecamatan || '—';
            document.getElementById('infoAlamat').textContent    = p.alamat || '—';
        } else {
            document.getElementById('blokKecamatan').style.display = 'none';
            document.getElementById('blokAlamat').style.display    = 'none';
        }

        // Estimasi berat (opsional saat pesan)
        const estimasi = p.estimasiBerat || null;
        document.getElementById('infoEstimasiBerat').textContent =
            estimasi ? estimasi + ' kg (estimasi)' : 'Tidak diisi';

        // Status pembayaran
        const sudahLunas   = p.status === 'selesai';
        const pembayaranEl = document.getElementById('infoPembayaran');
        pembayaranEl.textContent  = sudahLunas ? 'Lunas' : 'Belum Bayar';
        pembayaranEl.style.color  = sudahLunas ? '#52c49c' : '#f59e0b';
        pembayaranEl.style.fontWeight = '700';

        // Catatan
        document.getElementById('infoCatatan').textContent =
            p.note || 'Tidak ada catatan.';

        // ── KOTAK BERAT & BIAYA ──────────────────────────────────
        if (p.berat && p.berat > 0) {
            const biayaLayanan = p.berat * p.tarifLayanan;
            const total        = biayaLayanan + (p.tarifKirim || 0);

            document.getElementById('rincianLayananFinal').textContent =
                `${p.layanan} (${p.berat} kg × ${fmt(p.tarifLayanan)}) : ${fmt(biayaLayanan)}`;
            document.getElementById('rincianKurirFinal').textContent =
                p.opsi === 'kurir' ? `Kurir : ${fmt(p.tarifKirim)}` : '';
            document.getElementById('rincianTotalFinal').innerHTML =
                `Total : ${fmt(total)} <span class="label-final">(Harga Final)</span>`;
            document.getElementById('beratAktualBadge').textContent = p.berat + ' kg';

            document.getElementById('kotakHargaFinal').style.display  = 'block';
            document.getElementById('kotakMenunggu').style.display    = 'none';
        } else {
            document.getElementById('kotakMenunggu').style.display    = 'block';
            document.getElementById('kotakHargaFinal').style.display  = 'none';
        }

        // ── TIMELINE ─────────────────────────────────────────────
        // Bangun dari data status yang ada di localStorage.
        // Backend nanti ganti ini dengan query riwayat_status dari DB.
        const urutanLabel = {
            menunggu_konfirmasi : 'Pesanan Dibuat',
            dikonfirmasi        : 'Pesanan Dikonfirmasi Admin',
            sedang_dicuci       : 'Sedang Dicuci',
            siap_diambil        : 'Siap Diambil — Datang ke Outlet',
            sedang_diantar      : 'Sedang Diantar ke Alamat Kamu',
            selesai             : 'Selesai & Lunas',
            dibatalkan          : 'Pesanan Dibatalkan'
        };

        // Urutan kronologis semua status
        const urutanKronoKurir  = ['menunggu_konfirmasi','dikonfirmasi','sedang_dicuci','sedang_diantar','selesai','dibatalkan'];
        const urutanKronoAmbil  = ['menunggu_konfirmasi','dikonfirmasi','sedang_dicuci','siap_diambil','selesai','dibatalkan'];
        const urutanKrono       = p.opsi === 'kurir' ? urutanKronoKurir : urutanKronoAmbil;

        const idxSaatIni = urutanKrono.indexOf(p.status);
        const timelineEl = document.getElementById('timelineIsi');

        timelineEl.innerHTML = urutanKrono.map((key, i) => {
            const sudahLewat = i < idxSaatIni;
            const iniAktif   = i === idxSaatIni;
            const belumTiba  = i > idxSaatIni;

            // Jangan tampilkan status yang tidak relevan setelah dibatalkan/selesai
            if (key === 'dibatalkan' && p.status !== 'dibatalkan') return '';
            if (key === 'selesai'    && p.status === 'dibatalkan') return '';

            let kelasItem = 'timeline-item';
            let kelasDot  = 'timeline-dot';
            let warnaLabel = '';

            if (sudahLewat)    { kelasItem += ' timeline-item-selesai'; }
            else if (iniAktif) { kelasItem += ' timeline-item-aktif'; kelasDot += ' timeline-dot-aktif'; }
            else               { kelasDot += ' timeline-dot-kosong'; warnaLabel = 'color:#ccc;'; }

            // Garis kiri: hilangkan pada item terakhir yang visible
            const isLast = (i === idxSaatIni && (p.status === 'selesai' || p.status === 'dibatalkan'))
                        || (i === urutanKrono.length - 1);
            if (isLast) kelasItem += ' timeline-item-terakhir';

            const waktuTeks = (sudahLewat || iniAktif) ? p.waktu : '';

            return `<div class="${kelasItem}" style="${isLast ? 'border-left:none;' : ''}">
                        <div class="${kelasDot}"></div>
                        <div class="timeline-konten">
                            <p class="timeline-status-teks" style="${warnaLabel}">
                                ${urutanLabel[key] || key}
                            </p>
                            ${waktuTeks ? `<p class="timeline-waktu">${waktuTeks}</p>` : ''}
                        </div>
                    </div>`;
        }).join('');

    })();
    </script>

</body>
</html>

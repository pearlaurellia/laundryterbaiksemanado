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

        <!-- POPUP NOTIFIKASI: Pesanan dibatalkan oleh admin -->
    <div class="overlay-popup" id="overlayNotifBatal" style="display:none;"></div>

    <div class="popup-konfirmasi" id="popupNotifBatal"
        style="display:none; text-align:center; padding:40px 36px;">

        <div style="width:60px; height:60px; border-radius:50%;
                    background:#FFD1D1; color:#D32F2F;
                    font-size:1.8rem; font-weight:700;
                    display:flex; align-items:center; justify-content:center;
                    margin:0 auto 16px;">
            ✕
        </div>

        <h3 class="popup-judul" style="text-align:center; color:#D32F2F;">
            Pesanan Dibatalkan
        </h3>

        <p class="popup-teks" id="popupNotifBatalTeks"
        style="text-align:center; margin-bottom:8px;">
            Salah satu pesanan kamu telah dibatalkan oleh admin.
        </p>

        <!-- Alasan dari admin -->
        <div id="popupNotifBatalAlasan"
            style="display:none; margin:0 0 20px;
                    background:#fff5f5; border-left:3px solid #f87171;
                    border-radius:0 8px 8px 0; padding:10px 14px;
                    text-align:left; font-size:0.88rem; color:#555;">
            <strong style="color:#D32F2F;">Alasan:</strong>
            <span id="popupNotifBatalAlasanTeks"></span>
        </div>

        <div style="display:flex; gap:12px; justify-content:center; flex-wrap:wrap;">
            <button class="tombol-submit-form"
                    onclick="tutupNotifBatal()"
                    style="margin-top:0; background:#f0f0f0; color:#555;">
                Mengerti
            </button>
            <a href="riwayat.php"
            class="tombol-submit-form"
            style="text-decoration:none; margin-top:0;">
                Lihat Riwayat
            </a>
        </div>
    </div>

    <script src="../assets/js/main.js"></script>
    <script src="../assets/js/status_refresh.js"></script>

    <script>
    (function renderStatusAktif() {
        const raw   = localStorage.getItem('cleanco_pesanan');
        const semua = raw ? JSON.parse(raw) : {};
        const list  = document.getElementById('statusList');
        const kosong = document.getElementById('statusKosong');
        list.innerHTML = '';

        // Pesanan aktif = belum selesai & belum dibatalkan
        const aktif = Object.values(semua).filter(p =>
            !['selesai', 'dibatalkan'].includes(p.statusMember)
        );

        if (aktif.length === 0) { kosong.style.display = 'flex'; return; }
        kosong.style.display = 'none';

        aktif.forEach(p => {
            const totalHarga = p.berat
                ? 'Rp ' + ((p.berat * p.tarifLayanan) + p.tarifKirim).toLocaleString('id-ID')
                : null;

            // ── Progress steps sesuai dokumen ──
            // Jalur kurir:         menunggu → dikonfirmasi → sedang_dicuci → sedang_diantar → selesai
            // Jalur ambil_sendiri: menunggu → dikonfirmasi → sedang_dicuci → siap_diambil   → selesai
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
            const steps = p.opsi === 'kurir' ? stepsKurir : stepsAmbil;

            const urutanStatus = [
                'menunggu_konfirmasi','dikonfirmasi',
                'sedang_dicuci','siap_diambil','sedang_diantar','selesai'
            ];
            const aktifIdx = steps.findIndex(s => s.key === p.statusMember);

            const stepsHTML = steps.map((step, i) => {
                let kelas = 'step-progress';
                let isi   = i + 1;
                const posAktif = aktifIdx >= 0 ? aktifIdx : 0;
                if (i < posAktif)       { kelas += ' step-selesai'; isi = '✓'; }
                else if (i === posAktif) { kelas += ' step-aktif'; }

                const garis = i < steps.length - 1
                    ? `<div class="garis-progress ${i < posAktif ? 'garis-selesai' : ''}"></div>`
                    : '';
                return `<div class="${kelas}">
                            <div class="step-lingkaran">${isi}</div>
                            <p class="step-label">${step.label}</p>
                        </div>${garis}`;
            }).join('');

            // Banner sesuai status
            let bannerHTML = '';
            if (p.statusMember === 'siap_diambil') {
                bannerHTML = `<div class="banner-status banner-hijau" style="margin:0;border-radius:0;">
                    <span class="banner-ikon">✓</span>
                    <p>Cucian kamu siap diambil! Datang ke outlet dan bayar saat pengambilan.</p>
                </div>`;
            } else if (p.statusMember === 'sedang_diantar') {
                bannerHTML = `<div class="banner-status banner-biru" style="margin:0;border-radius:0;">
                    <span class="banner-ikon">🛵</span>
                    <p>Cucian kamu sedang dalam perjalanan ke alamat kamu!</p>
                </div>`;
            }

            // Harga / menunggu timbang
            const hargaHTML = totalHarga ? `
                <div class="kotak-harga-final-status">
                    <div class="harga-final-baris">
                        <span class="harga-final-label">Berat Aktual</span>
                        <strong class="harga-final-nilai">${p.berat} kg</strong>
                    </div>
                    <div class="harga-final-baris">
                        <span class="harga-final-label">Total Harga</span>
                        <strong class="harga-final-nilai harga-final-besar">
                            ${totalHarga} <span class="label-final">(Final)</span>
                        </strong>
                    </div>
                </div>` : `
                <div class="kotak-belum-timbang">
                    <span class="belum-timbang-ikon">⚖️</span>
                    <div>
                        <p class="belum-timbang-judul">Menunggu Penimbangan Admin</p>
                        <p class="belum-timbang-sub">Harga final akan muncul setelah pakaian ditimbang.</p>
                    </div>
                </div>`;

            // Info lokasi
            const ikonLokasi  = p.opsi === 'kurir' ? '🛵' : '🏬';
            const labelLokasi = p.opsi === 'kurir' ? `Kurir ke ${p.kecamatan}` : 'Ambil di Outlet';
            const alamatLokasi = p.opsi === 'kurir' ? p.alamat : 'Wanea, Teling Atas, Jln. Manado';

            // Badge status member
            const badgeKelas = {
                menunggu_konfirmasi : 'badge-status-baru',
                dikonfirmasi        : 'badge-status-dikonfirmasi',
                sedang_dicuci       : 'badge-status-diproses',
                sedang_diantar      : 'badge-status-diproses',
                siap_diambil        : 'badge-status-selesai'
            };
            const badgeLabel = {
                menunggu_konfirmasi : 'Menunggu Konfirmasi',
                dikonfirmasi        : 'Dikonfirmasi',
                sedang_dicuci       : 'Sedang Dicuci',
                sedang_diantar      : 'Sedang Diantar',
                siap_diambil        : 'Siap Diambil'
            };

            // Tombol batalkan: HANYA saat menunggu_konfirmasi (dokumen: sebelum admin proses)
            const tombolBatalHTML = p.statusMember === 'menunggu_konfirmasi' ? `
                <button class="tombol-batalkan-status"
                        onclick="konfirmasiBatal('${p.kode}', '${p.layanan}', ${p.id})">
                    Batalkan
                </button>` : '';

            list.insertAdjacentHTML('beforeend', `
                <div class="kartu-status-pesanan"
                    data-id="${p.kode}"
                    data-status="${p.statusMember}"
                    data-opsi="${p.opsi}">
                    <div class="kartu-status-header">
                        <div class="kartu-status-header-kiri">
                            <h3 class="kartu-status-kode">#${p.kode}</h3>
                            <p class="kartu-status-meta">${p.metaWaktu}</p>
                        </div>
                        <div class="kartu-status-header-kanan">
                            <span class="badge-status ${badgeKelas[p.statusMember] || 'badge-status-baru'}">
                                ${badgeLabel[p.statusMember] || p.statusMember}
                            </span>
                        </div>
                    </div>
                    ${bannerHTML}
                    <div class="progress-bar-wrapper">
                        <div class="progress-bar-track">${stepsHTML}</div>
                    </div>
                    <div class="kartu-status-body">
                        ${hargaHTML}
                        <div class="kartu-status-info-kurir">
                            <span class="info-kurir-ikon">${ikonLokasi}</span>
                            <div>
                                <p class="info-kurir-label">${labelLokasi}</p>
                                <p class="info-kurir-alamat">${alamatLokasi}</p>
                            </div>
                        </div>
                    </div>
                    <div class="kartu-status-aksi">
                        <a href="detail-pesanan.php?id=${p.kode}"
                        class="tombol-detail-status">Lihat Detail</a>
                        ${tombolBatalHTML}
                    </div>
                </div>
            `);
        });
    })();

    // ── Batalkan dari member ────────────────────────────────────
    let idPesananAkanDibatal  = null;
    let idInternalAkanDibatal = null;

    function konfirmasiBatal(kodePesanan, namaLayanan, idInternal) {
        idPesananAkanDibatal  = kodePesanan;
        idInternalAkanDibatal = idInternal;
        document.getElementById('popupBatalTeks').textContent =
            `Pesanan #${kodePesanan} (${namaLayanan}) akan dibatalkan dan tidak dapat dikembalikan.`;
        document.getElementById('overlayPopup').style.display = 'block';
        document.getElementById('popupBatal').style.display   = 'block';
    }

    function tutupPopupBatal() {
        idPesananAkanDibatal  = null;
        idInternalAkanDibatal = null;
        document.getElementById('overlayPopup').style.display = 'none';
        document.getElementById('popupBatal').style.display   = 'none';
    }

    function eksekusiBatal() {
        if (!idPesananAkanDibatal) return;

        if (idInternalAkanDibatal !== null) {
            _updateStatusPesanan(idInternalAkanDibatal, 'dibatalkan', null, 'member');
        }

        const kartuEl = document.querySelector(
            `.kartu-status-pesanan[data-id="${idPesananAkanDibatal}"]`
        );
        if (kartuEl) {
            kartuEl.style.transition = 'opacity 0.3s, transform 0.3s';
            kartuEl.style.opacity    = '0';
            kartuEl.style.transform  = 'translateY(-10px)';
            setTimeout(() => { kartuEl.remove(); cekKosong(); }, 300);
        }

        tutupPopupBatal();
    }

    function cekKosong() {
        const ada = document.querySelectorAll('.kartu-status-pesanan').length > 0;
        document.getElementById('statusKosong').style.display = ada ? 'none' : 'flex';
    }

    startAutoRefresh();

    // ── Cek & tampilkan notifikasi pesanan dibatalkan admin ────
    (function cekNotifikasiBatal() {
        const notifIds = _ambilNotifikasiBatal();
        if (notifIds.length === 0) return;

        // Ambil data pesanan pertama yang dibatalkan admin
        const data = _muatData();
        const idStr = notifIds[0];
        const p = data[idStr];
        if (!p) { _hapusNotifikasiBatal(idStr); return; }

        // Isi popup
        document.getElementById('popupNotifBatalTeks').textContent =
            `Pesanan #${p.kode} (${p.layanan}) kamu telah dibatalkan oleh admin.`;

        const alasanEl = document.getElementById('popupNotifBatalAlasan');
        const alasanTeksEl = document.getElementById('popupNotifBatalAlasanTeks');
        if (p.alasanBatal) {
            alasanEl.style.display    = 'block';
            alasanTeksEl.textContent  = p.alasanBatal;
        } else {
            alasanEl.style.display    = 'none';
        }

        // Simpan id yang sedang ditampilkan untuk dihapus saat tutup
        window._notifBatalIdAktif = idStr;

        // Tampilkan popup
        document.getElementById('overlayNotifBatal').style.display = 'block';
        document.getElementById('popupNotifBatal').style.display   = 'block';
    })();

    function tutupNotifBatal() {
        document.getElementById('overlayNotifBatal').style.display = 'none';
        document.getElementById('popupNotifBatal').style.display   = 'none';

        // Hapus notifikasi dari queue
        if (window._notifBatalIdAktif) {
            _hapusNotifikasiBatal(window._notifBatalIdAktif);
            window._notifBatalIdAktif = null;
        }
    }
    </script>

</body>
</html>
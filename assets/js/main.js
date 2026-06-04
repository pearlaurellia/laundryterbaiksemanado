// ── DATA DUMMY & GLOBALS ────────────────────────────────────
const _dataPesananDefault = {
    1: {
        id: 1,
        nama: "Ryan Liam", username: "@liam999",
        waktu: "10:00 Rabu, 04-12-2026",
        namaLengkap: "Ryan Liam Santoso",
        alamat: "Jl. Paal 4 No. 12, Ling. III",
        kecamatan: "Wanea",
        telpon: "0834545827",
        layanan: "Express", pengiriman: "Antar",
        tarifLayanan: 15000, tarifKirim: 10000,
        tags: [{label:"Cuci",tipe:"hijau"},{label:"Express",tipe:"biru"},{label:"Antar",tipe:"biru"}],
        // status admin: menunggu_konfirmasi | dikonfirmasi | sedang_dicuci | siap_diambil | sedang_diantar | selesai | dibatalkan
        status: "menunggu_konfirmasi",
        statusMember: "menunggu_konfirmasi",
        berat: null,
        note: "Tolong pisahkan baju putih.",
        kode: "LDR-0042",
        opsi: "kurir",
        metaWaktu: "Express · Kurir · 10:00 Rabu, 04-12-2026",
        alasanBatal: null,
        dibatalkanOleh: null
    },
    2: {
        id: 2,
        nama: "Sinta Dewi", username: "@sintad",
        waktu: "08:30 Rabu, 04-12-2026",
        namaLengkap: "Sinta Dewi Rahayu",
        alamat: "Jl. Bahu Lingkungan I No. 5",
        kecamatan: "Malalayang",
        telpon: "0812345678",
        layanan: "Reguler", pengiriman: "Pickup",
        tarifLayanan: 8000, tarifKirim: 0,
        tags: [{label:"Cuci",tipe:"hijau"},{label:"Reguler",tipe:"biru"},{label:"Pickup",tipe:"biru"}],
        status: "sedang_dicuci",
        statusMember: "sedang_dicuci",
        berat: 3,
        note: null,
        kode: "LDR-0038",
        opsi: "kurir",
        metaWaktu: "Reguler · Kurir · 08:30 Rabu, 04-12-2026",
        alasanBatal: null,
        dibatalkanOleh: null
    },
    3: {
        id: 3,
        nama: "Budi Santoso", username: "@budis",
        waktu: "07:00 Rabu, 04-12-2026",
        namaLengkap: "Budi Santoso",
        alamat: "Jl. Tikala Ares No. 88",
        kecamatan: "Tikala",
        telpon: "0856789012",
        layanan: "Reguler", pengiriman: "Antar",
        tarifLayanan: 8000, tarifKirim: 5000,
        tags: [{label:"Dry Cleaning",tipe:"hijau"},{label:"Reguler",tipe:"biru"},{label:"Antar",tipe:"biru"}],
        status: "selesai",
        statusMember: "selesai",
        berat: 2,
        note: null,
        kode: "LDR-0031",
        opsi: "ambil_sendiri",
        metaWaktu: "Reguler · Ambil Sendiri · 07:00 Rabu, 04-12-2026",
        alasanBatal: null,
        dibatalkanOleh: null
    },
    4: {
        id: 4,
        nama: "Mega Putri", username: "@megap",
        waktu: "06:15 Rabu, 04-12-2026",
        namaLengkap: "Mega Putri Wulandari",
        alamat: "Komp. Malalayang Permai Blok C No.3",
        kecamatan: "Malalayang",
        telpon: "0878901234",
        layanan: "Express", pengiriman: "Pickup",
        tarifLayanan: 15000, tarifKirim: 0,
        tags: [{label:"Cuci",tipe:"hijau"},{label:"Express",tipe:"biru"},{label:"Pickup",tipe:"biru"}],
        status: "menunggu_konfirmasi",
        statusMember: "menunggu_konfirmasi",
        berat: null,
        note: null,
        kode: "LDR-0025",
        opsi: "kurir",
        metaWaktu: "Express · Kurir · 06:15 Rabu, 04-12-2026",
        alasanBatal: null,
        dibatalkanOleh: null
    }
};

// ── STORAGE HELPERS ────────────────────────────────────────
function _simpanData(data) {
    localStorage.setItem('cleanco_pesanan', JSON.stringify(data));
}

function _muatData() {
    const raw = localStorage.getItem('cleanco_pesanan');
    if (!raw) {
        _simpanData(_dataPesananDefault);
        return JSON.parse(JSON.stringify(_dataPesananDefault));
    }
    return JSON.parse(raw);
}

let dataPesanan = _muatData();

// ── STATUS MAP: admin status → statusMember ───────────────
// Sesuai dokumen: admin punya kontrol penuh atas tahapan
const _statusMap = {
    menunggu_konfirmasi : 'menunggu_konfirmasi',
    dikonfirmasi        : 'dikonfirmasi',
    sedang_dicuci       : 'sedang_dicuci',
    siap_diambil        : 'siap_diambil',
    sedang_diantar      : 'sedang_diantar',
    selesai             : 'selesai',
    dibatalkan          : 'dibatalkan'
};

// ── UPDATE STATUS ──────────────────────────────────────────
function _updateStatusPesanan(id, status, alasan, dibatalkanOleh) {
    dataPesanan = _muatData();
    if (!dataPesanan[id]) return;
    dataPesanan[id].status       = status;
    dataPesanan[id].statusMember = status;
    if (alasan)        dataPesanan[id].alasanBatal    = alasan;
    if (dibatalkanOleh) dataPesanan[id].dibatalkanOleh = dibatalkanOleh;
    _simpanData(dataPesanan);
}

// ── BUKA & TUTUP DETAIL (admin/pesanan.php) ────────────────
let idAktif = null;

function bukaPesanan(id, el) {
    dataPesanan = _muatData();
    idAktif = id;
    const p = dataPesanan[id];

    document.querySelectorAll('.item-pesanan').forEach(i => i.classList.remove('aktif-dipilih'));
    el.classList.add('aktif-dipilih');

    document.getElementById('detailKosong').style.display = 'none';
    document.getElementById('detailIsi').style.display    = 'block';

    document.getElementById('detailNama').textContent        = p.nama;
    document.getElementById('detailUsername').textContent    = p.username;
    document.getElementById('detailWaktu').textContent       = p.waktu;
    document.getElementById('detailNamaLengkap').textContent = p.namaLengkap;
    document.getElementById('detailAlamat').textContent      = p.alamat;
    document.getElementById('detailKecamatan').textContent   = p.kecamatan;
    document.getElementById('detailTelpon').textContent      = p.telpon;
    document.getElementById('detailLayanan').textContent     = p.layanan;
    document.getElementById('detailPengiriman').textContent  = p.pengiriman;
    document.getElementById('detailNote').textContent        = p.note || '— Tidak ada catatan —';
    document.getElementById('inputBerat').value              = p.berat || '';

    const tagsEl = document.getElementById('detailTags');
    tagsEl.innerHTML = p.tags.map(t =>
        `<span class="badge-${t.tipe}">${t.label}</span>`
    ).join('');

    setStatusUI(p.status);
    hitungBiaya();
}

function kembaliKeList() {
    document.getElementById('detailKosong').style.display = 'flex';
    document.getElementById('detailIsi').style.display    = 'none';
    document.querySelectorAll('.item-pesanan').forEach(i => i.classList.remove('aktif-dipilih'));
    idAktif = null;
}

// ── FILTER (admin/pesanan.php) ─────────────────────────────
function filterPesanan(status, btn) {
    document.querySelectorAll('.tombol-filter').forEach(b => b.classList.remove('aktif'));
    btn.classList.add('aktif');
    // Gunakan renderListPesanan agar data dari localStorage selalu fresh
    renderListPesanan(status);
}

// ── FILTER RIWAYAT (member/riwayat.php) ───────────────────
function filterRiwayat(filter, btn) {
    document.querySelectorAll('#grupFilterRiwayat .tombol-filter')
            .forEach(b => b.classList.remove('aktif'));
    btn.classList.add('aktif');

    document.querySelectorAll('.kartu-riwayat').forEach(item => {
        const cocok = filter === 'semua' || item.dataset.filter === filter;
        item.style.display = cocok ? 'flex' : 'none';
    });

    const adaYangTampil = [...document.querySelectorAll('.kartu-riwayat')]
        .some(item => item.style.display !== 'none');
    const kosongEl = document.getElementById('riwayatKosong');
    if (kosongEl) kosongEl.style.display = adaYangTampil ? 'none' : 'flex';
}

// ── BATALKAN DARI ADMIN ────────────────────────────────────
let _idAkanDibatalAdmin  = null;

function batalkanPesananAdmin(id) {
    _idAkanDibatalAdmin = id;
    dataPesanan = _muatData();
    const p = dataPesanan[id];

    document.getElementById('popupBatalAdminTeks').textContent =
        `Pesanan #${p.kode} (${p.layanan}) milik ${p.nama} akan dibatalkan.`;

    // Reset semua pilihan radio & sembunyikan input lainnya
    document.querySelectorAll('input[name="alasanBatal"]').forEach(r => r.checked = false);
    const wrapperLainnya = document.getElementById('wrapperAlasanLainnya');
    if (wrapperLainnya) wrapperLainnya.style.display = 'none';
    const inputLainnya = document.getElementById('inputAlasanLainnya');
    if (inputLainnya) inputLainnya.value = '';

    document.getElementById('overlayBatalAdmin').style.display = 'block';
    document.getElementById('popupBatalAdmin').style.display   = 'block';
}

function tutupPopupBatalAdmin() {
    _idAkanDibatalAdmin = null;
    document.getElementById('overlayBatalAdmin').style.display = 'none';
    document.getElementById('popupBatalAdmin').style.display   = 'none';
}

function eksekusiBatalAdmin() {
    if (!_idAkanDibatalAdmin) return;
    const id = _idAkanDibatalAdmin;

    // Ambil radio yang dipilih
    const radioTerpilih = document.querySelector('input[name="alasanBatal"]:checked');
    if (!radioTerpilih) {
        alert('Pilih alasan pembatalan terlebih dahulu.');
        return;
    }

    let alasanTeks = radioTerpilih.value;

    // Jika pilih "lainnya", ambil dari input teks
    if (alasanTeks === 'lainnya') {
        const inputLainnya = document.getElementById('inputAlasanLainnya').value.trim();
        alasanTeks = inputLainnya || 'Dibatalkan oleh admin.';
    }

    _updateStatusPesanan(id, 'dibatalkan', alasanTeks, 'admin');
    _simpanNotifikasiBatal(id);

    dataPesanan = _muatData();

    const itemEl = document.querySelector(`.item-pesanan[data-id="${id}"]`);
    if (itemEl) {
        itemEl.dataset.status = 'dibatalkan';
        const badgeEl = itemEl.querySelector('.badge-status');
        if (badgeEl) {
            badgeEl.className   = 'badge-status badge-status-batal';
            badgeEl.textContent = 'Dibatalkan';
        }
    }

    if (idAktif == id) setStatusUI('dibatalkan');
    tutupPopupBatalAdmin();
}

// ── RESET (dev helper) ─────────────────────────────────────
function resetData() {
    localStorage.removeItem('cleanco_pesanan');
    location.reload();
}

// ── NOTIFIKASI BATALKAN UNTUK MEMBER ──────────────────────
// Saat admin batalkan, simpan flag notifikasi ke localStorage
// member/status.php akan cek ini saat halaman dimuat

function _simpanNotifikasiBatal(id) {
    const raw  = localStorage.getItem('cleanco_notif_batal') || '[]';
    const list = JSON.parse(raw);
    if (!list.includes(String(id))) {
        list.push(String(id));
        localStorage.setItem('cleanco_notif_batal', JSON.stringify(list));
    }
}

function _ambilNotifikasiBatal() {
    const raw = localStorage.getItem('cleanco_notif_batal') || '[]';
    return JSON.parse(raw);
}

function _hapusNotifikasiBatal(id) {
    const raw  = localStorage.getItem('cleanco_notif_batal') || '[]';
    const list = JSON.parse(raw).filter(i => String(i) !== String(id));
    localStorage.setItem('cleanco_notif_batal', JSON.stringify(list));
}



(function renderRiwayat() {
    // Guard: jangan jalan kalau elemen tidak ada (halaman bukan riwayat.php)
    if (!document.getElementById('riwayatList')) return;

    const raw   = localStorage.getItem('cleanco_pesanan');
    const semua = raw ? JSON.parse(raw) : {};
    const list  = document.getElementById('riwayatList');
    const kosong = document.getElementById('riwayatKosong');
    list.innerHTML = '';

    const riwayat = Object.values(semua).filter(p =>
        ['selesai', 'dibatalkan'].includes(p.statusMember)
    );

    if (riwayat.length === 0) { kosong.style.display = 'flex'; return; }
    kosong.style.display = 'none';

    riwayat.forEach(p => {
        const selesai    = p.statusMember === 'selesai';
        const filterVal  = selesai ? 'selesai' : 'dibatalkan';
        const badgeKelas = selesai ? 'badge-status-selesai' : 'badge-status-batal';
        const badgeLabel = selesai ? 'Selesai & Lunas' : 'Dibatalkan';
        const tanggalLabel = selesai ? 'Selesai' : 'Dibatalkan';

        const totalHarga = (selesai && p.berat)
            ? 'Rp ' + ((p.berat * p.tarifLayanan) + p.tarifKirim).toLocaleString('id-ID')
            : '—';
        const beratTeks = p.berat ? p.berat + ' kg' : '—';
        const pembayaranWarna = selesai ? '#52c49c' : '#f87171';
        const pembayaranLabel = selesai ? 'Lunas' : 'Tidak Jadi';

        // Alasan batal — sesuai dokumen: tercatat di riwayat
        const alasanHTML = (!selesai && p.alasanBatal) ? `
            <div style="margin-top:8px; padding:8px 12px;
                        background:#fff5f5; border-left:3px solid #f87171;
                        border-radius:0 8px 8px 0; font-size:0.82rem; color:#555;">
                <strong style="color:#D32F2F;">Alasan:</strong> ${p.alasanBatal}
            </div>` : '';

        // Dibatalkan oleh siapa
        const olehHTML = (!selesai && p.dibatalkanOleh) ? `
            <div class="riwayat-detail-baris">
                <span class="riwayat-detail-label">Dibatalkan oleh</span>
                <span class="riwayat-detail-nilai" style="text-transform:capitalize;">
                    ${p.dibatalkanOleh}
                </span>
            </div>` : '';

        const tagsHTML = p.tags.map(t =>
            `<span class="badge-${t.tipe}">${t.label}</span>`
        ).join('');

        list.insertAdjacentHTML('beforeend', `
            <div class="kartu-riwayat ${selesai ? '' : 'kartu-riwayat-batal'}"
                 data-filter="${filterVal}">
                <div class="kartu-riwayat-kiri">
                    <div class="kartu-riwayat-atas">
                        <span class="badge-status ${badgeKelas}">${badgeLabel}</span>
                        <span class="kartu-riwayat-tanggal">
                            ${tanggalLabel}: ${p.waktu.split(' ').slice(1).join(' ')}
                        </span>
                    </div>
                    <h3 class="kartu-riwayat-kode">#${p.kode}</h3>
                    <div class="kartu-riwayat-tags">${tagsHTML}</div>
                    ${alasanHTML}
                </div>
                <div class="kartu-riwayat-kanan">
                    <div class="kartu-riwayat-detail">
                        <div class="riwayat-detail-baris">
                            <span class="riwayat-detail-label">Berat Aktual</span>
                            <span class="riwayat-detail-nilai">${beratTeks}</span>
                        </div>
                        <div class="riwayat-detail-baris">
                            <span class="riwayat-detail-label">Total Harga</span>
                            <span class="riwayat-detail-nilai riwayat-total">${totalHarga}</span>
                        </div>
                        <div class="riwayat-detail-baris">
                            <span class="riwayat-detail-label">Pembayaran</span>
                            <span class="riwayat-detail-nilai"
                                  style="color:${pembayaranWarna}; font-weight:700;">
                                ${pembayaranLabel}
                            </span>
                        </div>
                        ${olehHTML}
                    </div>
                    <a href="detail-pesanan.php?id=${p.kode}"
                       class="tombol-detail-status">Lihat Detail</a>
                </div>
            </div>
        `);
    });
})();


/* ========================================
   FILE: assets/js/laporan.js
   Halaman Laporan Admin - CleanCo Laundry
   ======================================== */

/**
 * BACKEND API ENDPOINTS YANG DIPERLUKAN:
 * 
 * 1. GET /api/laporan/ringkasan
 *    Query params: bulan, tahun (YYYY), start_date, end_date
 *    Response: {
 *      success: true,
 *      data: {
 *        total_pendapatan: number,
 *        total_pesanan: number,
 *        pesanan_selesai: number,
 *        pesanan_dibatalkan: number,
 *        pesanan_proses: number,
 *        rata_rata_per_pesanan: number,
 *        pendapatan_bulan_lalu: number,
 *        persen_selesai: number,
 *        persen_pertumbuhan: number
 *      }
 *    }
 * 
 * 2. GET /api/laporan/pendapatan-bulanan
 *    Query params: tahun
 *    Response: {
 *      success: true,
 *      data: [{ bulan: string, pendapatan: number, jumlah_pesanan: number }]
 *    }
 * 
 * 3. GET /api/laporan/pesanan
 *    Query params: bulan, tahun, status, start_date, end_date, page, limit
 *    Response: {
 *      success: true,
 *      data: {
 *        items: [{ id, kode, nama_customer, layanan, berat, total_harga, status, tanggal, no_wa }],
 *        total: number,
 *        page: number,
 *        total_pages: number
 *      }
 *    }
 * 
 * 4. GET /api/laporan/export
 *    Query params: tipe (excel/csv), bulan, tahun, start_date, end_date
 *    Returns file download
 */

// ========================================
// KONFIGURASI
// ========================================

const API_BASE_URL = '/api/laporan';

// State
let currentFilter = {
    bulan: '12',
    tahun: '2026',
    startDate: null,
    endDate: null,
    statusPesanan: 'semua'
};

let currentPage = 1;
let totalPages = 1;
let itemsPerPage = 20;

// Chart data cache
let chartDataCache = null;

// DOM Elements
let filterBulan, filterTahun, filterStartDate, filterEndDate;
let tombolTerapkanRange, tombolRefresh, tombolExportExcel, tombolExportCSV;
let tabelFilterBtns, prevPageBtn, nextPageBtn, pageInfoSpan;

// ========================================
// INISIALISASI
// ========================================

document.addEventListener('DOMContentLoaded', function() {
    // Initialize DOM references
    filterBulan = document.getElementById('filterBulan');
    filterTahun = document.getElementById('filterTahun');
    filterStartDate = document.getElementById('filterStartDate');
    filterEndDate = document.getElementById('filterEndDate');
    tombolTerapkanRange = document.getElementById('tombolTerapkanRange');
    tombolRefresh = document.getElementById('tombolRefresh');
    tombolExportExcel = document.getElementById('tombolExportExcel');
    tombolExportCSV = document.getElementById('tombolExportCSV');
    prevPageBtn = document.getElementById('prevPageBtn');
    nextPageBtn = document.getElementById('nextPageBtn');
    pageInfoSpan = document.getElementById('pageInfo');
    
    // Set default date range (last 6 months)
    setDefaultDates();
    
    // Attach event listeners
    attachEventListeners();
    
    // Load initial data
    loadAllData();
});

// ========================================
// EVENT LISTENERS
// ========================================

function attachEventListeners() {
    // Filter buttons
    if (tombolTerapkanRange) {
        tombolTerapkanRange.addEventListener('click', applyDateRange);
    }
    
    if (tombolRefresh) {
        tombolRefresh.addEventListener('click', refreshData);
    }
    
    if (tombolExportExcel) {
        tombolExportExcel.addEventListener('click', function() { exportData('excel'); });
    }
    
    if (tombolExportCSV) {
        tombolExportCSV.addEventListener('click', function() { exportData('csv'); });
    }
    
    // Table filter buttons
    tabelFilterBtns = document.querySelectorAll('.tabel-filter-btn');
    tabelFilterBtns.forEach(function(btn) {
        btn.addEventListener('click', function() {
            const status = this.getAttribute('data-status');
            filterTableByStatus(status);
        });
    });
    
    // Pagination buttons
    if (prevPageBtn) {
        prevPageBtn.addEventListener('click', goToPrevPage);
    }
    if (nextPageBtn) {
        nextPageBtn.addEventListener('click', goToNextPage);
    }
    
    // Month/Year change
    if (filterBulan) {
        filterBulan.addEventListener('change', function() {
            clearDateRange();
            loadAllData();
        });
    }
    if (filterTahun) {
        filterTahun.addEventListener('change', function() {
            clearDateRange();
            loadAllData();
        });
    }
}

// ========================================
// HELPER FUNCTIONS
// ========================================

function setDefaultDates() {
    const today = new Date();
    const sixMonthsAgo = new Date();
    sixMonthsAgo.setMonth(today.getMonth() - 5);
    
    // Set default tahun/bulan ke periode terbaru
    if (filterTahun) filterTahun.value = today.getFullYear().toString();
    if (filterBulan) {
        const bulan = String(today.getMonth() + 1).padStart(2, '0');
        filterBulan.value = bulan;
    }
}

function clearDateRange() {
    if (filterStartDate) filterStartDate.value = '';
    if (filterEndDate) filterEndDate.value = '';
    currentFilter.startDate = null;
    currentFilter.endDate = null;
}

function applyDateRange() {
    const startDate = filterStartDate ? filterStartDate.value : '';
    const endDate = filterEndDate ? filterEndDate.value : '';
    
    if (startDate && endDate) {
        currentFilter.startDate = startDate;
        currentFilter.endDate = endDate;
        currentFilter.bulan = null;
        currentFilter.tahun = null;
        currentPage = 1;
        loadAllData();
    } else {
        showToast('Pilih tanggal mulai dan selesai terlebih dahulu', 'error');
    }
}

function refreshData() {
    currentPage = 1;
    loadAllData();
    showToast('Data berhasil direfresh', 'success');
}

function filterTableByStatus(status) {
    // Update active button state
    tabelFilterBtns.forEach(function(btn) {
        if (btn.getAttribute('data-status') === status) {
            btn.classList.add('aktif');
        } else {
            btn.classList.remove('aktif');
        }
    });
    
    currentFilter.statusPesanan = status;
    currentPage = 1;
    loadTabelPesanan();
}

function goToPrevPage() {
    if (currentPage > 1) {
        currentPage--;
        loadTabelPesanan();
    }
}

function goToNextPage() {
    if (currentPage < totalPages) {
        currentPage++;
        loadTabelPesanan();
    }
}

function updatePaginationButtons() {
    if (prevPageBtn) {
        prevPageBtn.disabled = currentPage <= 1;
    }
    if (nextPageBtn) {
        nextPageBtn.disabled = currentPage >= totalPages;
    }
    if (pageInfoSpan) {
        pageInfoSpan.textContent = 'Halaman ' + currentPage + ' dari ' + totalPages;
    }
    
    const paginationContainer = document.getElementById('paginationContainer');
    if (paginationContainer) {
        paginationContainer.style.display = totalPages > 1 ? 'flex' : 'none';
    }
}

// ========================================
// API CALLS
// ========================================

async function loadAllData() {
    await Promise.all([
        loadRingkasan(),
        loadGrafikPendapatan(),
        loadTabelPesanan()
    ]);
}

async function loadRingkasan() {
    const ringkasanGrid = document.getElementById('ringkasanGrid');
    if (!ringkasanGrid) return;
    
    // Show loading skeleton
    showRingkasanLoading();
    
    let url = API_BASE_URL + '/ringkasan?';
    
    if (currentFilter.startDate && currentFilter.endDate) {
        url += 'start_date=' + currentFilter.startDate + '&end_date=' + currentFilter.endDate;
    } else if (currentFilter.bulan && currentFilter.tahun) {
        url += 'bulan=' + currentFilter.bulan + '&tahun=' + currentFilter.tahun;
    } else {
        url += 'tahun=' + (currentFilter.tahun || '2026');
    }
    
    try {
        const response = await fetch(url);
        const result = await response.json();
        
        if (result.success) {
            updateRingkasanUI(result.data);
        } else {
            throw new Error(result.message || 'Gagal mengambil data ringkasan');
        }
    } catch (error) {
        console.error('Error fetching ringkasan:', error);
        // Use dummy data for development/demo
        updateRingkasanUI(getDummyRingkasanData());
    }
}

function showRingkasanLoading() {
    const totalPendapatanEl = document.getElementById('totalPendapatan');
    const totalPesananEl = document.getElementById('totalPesanan');
    const pesananSelesaiEl = document.getElementById('pesananSelesai');
    const rataPesananEl = document.getElementById('rataPesanan');
    
    if (totalPendapatanEl) totalPendapatanEl.textContent = 'Rp ...';
    if (totalPesananEl) totalPesananEl.textContent = '...';
    if (pesananSelesaiEl) pesananSelesaiEl.textContent = '...';
    if (rataPesananEl) rataPesananEl.textContent = 'Rp ...';
}

function updateRingkasanUI(data) {
    const totalPendapatanEl = document.getElementById('totalPendapatan');
    const totalPesananEl = document.getElementById('totalPesanan');
    const pesananSelesaiEl = document.getElementById('pesananSelesai');
    const rataPesananEl = document.getElementById('rataPesanan');
    const trendPendapatanEl = document.getElementById('trendPendapatan');
    const trendPesananEl = document.getElementById('trendPesanan');
    const persenSelesaiEl = document.getElementById('persenSelesai');
    
    if (totalPendapatanEl) {
        totalPendapatanEl.textContent = formatRupiah(data.total_pendapatan || 0);
    }
    if (totalPesananEl) {
        totalPesananEl.textContent = formatNumber(data.total_pesanan || 0);
    }
    if (pesananSelesaiEl) {
        pesananSelesaiEl.textContent = formatNumber(data.pesanan_selesai || 0);
    }
    if (rataPesananEl) {
        rataPesananEl.textContent = formatRupiah(data.rata_rata_per_pesanan || 0);
    }
    
    // Trend pendapatan
    if (trendPendapatanEl && data.persen_pertumbuhan !== undefined) {
        const growth = data.persen_pertumbuhan;
        const growthText = (growth >= 0 ? '+' : '') + growth + '% dari bulan lalu';
        trendPendapatanEl.textContent = growthText;
        trendPendapatanEl.className = 'ringkasan-sub ' + (growth >= 0 ? 'ringkasan-sub-positif' : 'ringkasan-sub-negatif');
    }
    
    // Persentase selesai
    if (persenSelesaiEl && data.persen_selesai !== undefined) {
        persenSelesaiEl.textContent = data.persen_selesai + '% selesai';
    }
}

async function loadGrafikPendapatan() {
    const tahun = currentFilter.tahun || '2026';
    const url = API_BASE_URL + '/pendapatan-bulanan?tahun=' + tahun;
    
    try {
        const response = await fetch(url);
        const result = await response.json();
        
        if (result.success) {
            chartDataCache = result.data;
            drawChart(result.data);
            updateGrafikPeriodeLabel(tahun);
        } else {
            throw new Error(result.message || 'Gagal mengambil data grafik');
        }
    } catch (error) {
        console.error('Error fetching grafik:', error);
        // Use dummy data
        chartDataCache = getDummyGrafikData();
        drawChart(chartDataCache);
    }
}

function drawChart(data) {
    const canvas = document.getElementById('pendapatanChart');
    if (!canvas) return;
    
    const ctx = canvas.getContext('2d');
    const width = canvas.parentElement.clientWidth - 40;
    const height = 250;
    
    canvas.width = width;
    canvas.height = height;
    
    // Clear canvas
    ctx.clearRect(0, 0, width, height);
    
    if (!data || data.length === 0) {
        ctx.fillStyle = '#aaa';
        ctx.font = '14px "DM Sans", sans-serif';
        ctx.textAlign = 'center';
        ctx.fillText('Tidak ada data untuk ditampilkan', width / 2, height / 2);
        return;
    }
    
    // Extract data
    const labels = data.map(function(item) { return item.bulan; });
    const values = data.map(function(item) { return item.pendapatan || 0; });
    const maxValue = Math.max.apply(null, values) || 1;
    
    const padding = { top: 20, right: 30, bottom: 50, left: 60 };
    const chartWidth = width - padding.left - padding.right;
    const chartHeight = height - padding.top - padding.bottom;
    const barWidth = chartWidth / values.length * 0.7;
    const barSpacing = chartWidth / values.length;
    
    // Draw axes
    ctx.beginPath();
    ctx.strokeStyle = '#ccc';
    ctx.lineWidth = 1;
    
    // Y-axis
    ctx.moveTo(padding.left, padding.top);
    ctx.lineTo(padding.left, height - padding.bottom);
    // X-axis
    ctx.lineTo(width - padding.right, height - padding.bottom);
    ctx.stroke();
    
    // Draw Y-axis labels
    ctx.fillStyle = '#888';
    ctx.font = '11px "DM Sans", sans-serif';
    ctx.textAlign = 'right';
    
    const ySteps = 5;
    for (let i = 0; i <= ySteps; i++) {
        const value = (maxValue / ySteps) * i;
        const y = height - padding.bottom - (i / ySteps) * chartHeight;
        ctx.fillText(formatRupiahShort(value), padding.left - 5, y + 3);
        
        // Horizontal grid line
        ctx.beginPath();
        ctx.strokeStyle = '#eee';
        ctx.moveTo(padding.left, y);
        ctx.lineTo(width - padding.right, y);
        ctx.stroke();
    }
    
    // Draw bars and X-axis labels
    ctx.textAlign = 'center';
    ctx.fillStyle = '#4EC59D';
    
    for (let i = 0; i < values.length; i++) {
        const x = padding.left + i * barSpacing + (barSpacing - barWidth) / 2;
        const barHeight = (values[i] / maxValue) * chartHeight;
        const y = height - padding.bottom - barHeight;
        
        // Draw bar
        ctx.fillStyle = '#4EC59D';
        ctx.fillRect(x, y, barWidth, barHeight);
        
        // Draw bar value on top
        if (values[i] > 0) {
            ctx.fillStyle = '#0d3f8a';
            ctx.font = 'bold 10px "DM Sans", sans-serif';
            ctx.fillText(formatRupiahShort(values[i]), x + barWidth / 2, y - 3);
        }
        
        // X-axis label
        ctx.fillStyle = '#888';
        ctx.font = '11px "DM Sans", sans-serif';
        ctx.fillText(labels[i], x + barWidth / 2, height - padding.bottom + 15);
    }
    
    // Title
    ctx.fillStyle = '#0d3f8a';
    ctx.font = 'bold 12px "DM Sans", sans-serif';
    ctx.textAlign = 'center';
    ctx.fillText('Pendapatan per Bulan (Rp)', width / 2, padding.top - 5);
}

function updateGrafikPeriodeLabel(tahun) {
    const labelEl = document.getElementById('grafikPeriodeLabel');
    if (labelEl) {
        labelEl.textContent = 'Periode: Jan - Des ' + tahun;
    }
}

async function loadTabelPesanan() {
    const wrapper = document.getElementById('tabelPesananWrapper');
    if (!wrapper) return;
    
    // Show loading
    wrapper.innerHTML = `
        <div class="loading-state">
            <div class="loading-spinner"></div>
            <div>Memuat data pesanan...</div>
        </div>
    `;
    
    let url = API_BASE_URL + '/pesanan?page=' + currentPage + '&limit=' + itemsPerPage;
    
    if (currentFilter.startDate && currentFilter.endDate) {
        url += '&start_date=' + currentFilter.startDate + '&end_date=' + currentFilter.endDate;
    } else if (currentFilter.bulan && currentFilter.tahun) {
        url += '&bulan=' + currentFilter.bulan + '&tahun=' + currentFilter.tahun;
    }
    
    if (currentFilter.statusPesanan && currentFilter.statusPesanan !== 'semua') {
        let statusParam = currentFilter.statusPesanan;
        if (statusParam === 'proses') {
            statusParam = 'sedang_dicuci';
        }
        url += '&status=' + statusParam;
    }
    
    try {
        const response = await fetch(url);
        const result = await response.json();
        
        if (result.success) {
            totalPages = result.data.total_pages || 1;
            updatePaginationButtons();
            renderTabelPesanan(result.data.items || []);
        } else {
            throw new Error(result.message || 'Gagal mengambil data pesanan');
        }
    } catch (error) {
        console.error('Error fetching pesanan:', error);
        renderTabelPesanan(getDummyPesananData());
    }
}

function renderTabelPesanan(items) {
    const wrapper = document.getElementById('tabelPesananWrapper');
    if (!wrapper) return;
    
    if (!items || items.length === 0) {
        wrapper.innerHTML = `
            <div class="data-kosong">
                <div class="kosong-icon">📭</div>
                <div>Tidak ada data pesanan</div>
                <div style="font-size: 0.8rem; margin-top: 8px;">Coba ubah filter periode atau status</div>
            </div>
        `;
        return;
    }
    
    let tableHTML = `
        <table class="tabel-pesanan">
            <thead>
                <tr>
                    <th>Kode Pesanan</th>
                    <th>Customer</th>
                    <th>Layanan</th>
                    <th>Berat</th>
                    <th>Total Harga</th>
                    <th>Status</th>
                    <th>Tanggal</th>
                </tr>
            </thead>
            <tbody>
    `;
    
    for (let i = 0; i < items.length; i++) {
        const item = items[i];
        let statusBadgeClass = '';
        let statusText = '';
        
        switch (item.status) {
            case 'selesai':
                statusBadgeClass = 'badge-status-selesai';
                statusText = 'Selesai';
                break;
            case 'dibatalkan':
                statusBadgeClass = 'badge-status-dibatalkan';
                statusText = 'Dibatalkan';
                break;
            case 'sedang_dicuci':
                statusBadgeClass = 'badge-status-proses';
                statusText = 'Dicuci';
                break;
            case 'siap_diambil':
                statusBadgeClass = 'badge-status-proses';
                statusText = 'Siap Diambil';
                break;
            case 'sedang_diantar':
                statusBadgeClass = 'badge-status-proses';
                statusText = 'Diantar';
                break;
            default:
                statusBadgeClass = 'badge-status-menunggu';
                statusText = item.status || 'Menunggu';
        }
        
        tableHTML += `
            <tr>
                <td><strong>#${escapeHtml(item.kode || item.id)}</strong></td>
                <td>${escapeHtml(item.nama_customer || item.nama || '-')}</td>
                <td>${escapeHtml(item.layanan || '-')}</td>
                <td>${item.berat ? item.berat + ' kg' : '-'}</td>
                <td>${formatRupiah(item.total_harga || 0)}</td>
                <td><span class="${statusBadgeClass}">${statusText}</span></td>
                <td>${formatTanggal(item.tanggal || item.waktu)}</td>
            </tr>
        `;
    }
    
    tableHTML += `
            </tbody>
        </table>
    `;
    
    wrapper.innerHTML = tableHTML;
}

// ========================================
// EXPORT FUNCTIONS
// ========================================

async function exportData(tipe) {
    let url = API_BASE_URL + '/export?tipe=' + tipe;
    
    if (currentFilter.startDate && currentFilter.endDate) {
        url += '&start_date=' + currentFilter.startDate + '&end_date=' + currentFilter.endDate;
    } else if (currentFilter.bulan && currentFilter.tahun) {
        url += '&bulan=' + currentFilter.bulan + '&tahun=' + currentFilter.tahun;
    }
    
    try {
        window.location.href = url;
        showToast('Ekspor data ' + tipe.toUpperCase() + ' dimulai', 'success');
    } catch (error) {
        console.error('Error exporting data:', error);
        showToast('Gagal mengekspor data', 'error');
    }
}

// ========================================
// UTILITY FUNCTIONS
// ========================================

function formatRupiah(angka) {
    if (angka === undefined || angka === null) return 'Rp 0';
    return 'Rp ' + angka.toLocaleString('id-ID');
}

function formatRupiahShort(angka) {
    if (angka >= 1000000) {
        return 'Rp ' + (angka / 1000000).toFixed(1) + 'jt';
    }
    if (angka >= 1000) {
        return 'Rp ' + (angka / 1000).toFixed(0) + 'rb';
    }
    return 'Rp ' + angka;
}

function formatNumber(angka) {
    if (angka === undefined || angka === null) return '0';
    return angka.toLocaleString('id-ID');
}

function formatTanggal(tanggalStr) {
    if (!tanggalStr) return '-';
    try {
        const tanggal = new Date(tanggalStr);
        if (isNaN(tanggal.getTime())) return tanggalStr;
        return tanggal.getDate() + '/' + (tanggal.getMonth() + 1) + '/' + tanggal.getFullYear();
    } catch (e) {
        return tanggalStr;
    }
}

function escapeHtml(str) {
    if (!str) return '';
    return str
        .replace(/&/g, '&amp;')
        .replace(/</g, '&lt;')
        .replace(/>/g, '&gt;')
        .replace(/"/g, '&quot;')
        .replace(/'/g, '&#39;');
}

function showToast(pesan, type) {
    // Simple toast notification
    let toast = document.getElementById('toastNotification');
    if (!toast) {
        toast = document.createElement('div');
        toast.id = 'toastNotification';
        toast.style.cssText = `
            position: fixed;
            bottom: 20px;
            right: 20px;
            padding: 12px 24px;
            border-radius: 20px 0px 20px 20px;
            font-family: "DM Sans", sans-serif;
            font-size: 0.9rem;
            z-index: 1000;
            transition: opacity 0.3s;
            opacity: 0;
        `;
        document.body.appendChild(toast);
    }
    
    toast.style.backgroundColor = type === 'error' ? '#f87171' : '#52c49c';
    toast.style.color = type === 'error' ? 'white' : '#1a4d3a';
    toast.textContent = pesan;
    toast.style.opacity = '1';
    
    setTimeout(function() {
        toast.style.opacity = '0';
    }, 3000);
}

// ========================================
// DUMMY DATA (Untuk development)
// ========================================

function getDummyRingkasanData() {
    return {
        total_pendapatan: 28450000,
        total_pesanan: 142,
        pesanan_selesai: 128,
        pesanan_dibatalkan: 8,
        pesanan_proses: 6,
        rata_rata_per_pesanan: 200352,
        pendapatan_bulan_lalu: 26200000,
        persen_pertumbuhan: 8.6,
        persen_selesai: 90
    };
}

function getDummyGrafikData() {
    return [
        { bulan: 'Jul', pendapatan: 18500000, jumlah_pesanan: 92 },
        { bulan: 'Agu', pendapatan: 19200000, jumlah_pesanan: 96 },
        { bulan: 'Sep', pendapatan: 21000000, jumlah_pesanan: 105 },
        { bulan: 'Okt', pendapatan: 23500000, jumlah_pesanan: 118 },
        { bulan: 'Nov', pendapatan: 24800000, jumlah_pesanan: 124 },
        { bulan: 'Des', pendapatan: 28450000, jumlah_pesanan: 142 }
    ];
}

function getDummyPesananData() {
    return [
        { id: 'LDR-0042', kode: 'LDR-0042', nama_customer: 'Ryan Liam', layanan: 'Express', berat: 2.5, total_harga: 47500, status: 'selesai', tanggal: '2026-12-04' },
        { id: 'LDR-0038', kode: 'LDR-0038', nama_customer: 'Sinta Dewi', layanan: 'Reguler', berat: 3, total_harga: 34000, status: 'selesai', tanggal: '2026-12-04' },
        { id: 'LDR-0031', kode: 'LDR-0031', nama_customer: 'Budi Santoso', layanan: 'Dry Cleaning', berat: 1, total_harga: 35000, status: 'selesai', tanggal: '2026-12-03' },
        { id: 'LDR-0025', kode: 'LDR-0025', nama_customer: 'Mega Putri', layanan: 'Express', berat: null, total_harga: 0, status: 'menunggu_konfirmasi', tanggal: '2026-12-04' },
        { id: 'LDR-0019', kode: 'LDR-0019', nama_customer: 'Andi Wijaya', layanan: 'Reguler', berat: 4, total_harga: 42000, status: 'dibatalkan', tanggal: '2026-12-02' },
        { id: 'LDR-0015', kode: 'LDR-0015', nama_customer: 'Rina Cahyani', layanan: 'Express', berat: 2, total_harga: 40000, status: 'sedang_dicuci', tanggal: '2026-12-04' }
    ];
}
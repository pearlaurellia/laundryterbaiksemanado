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
    document.querySelectorAll('.item-pesanan').forEach(item => {
        item.style.display = (status === 'semua' || item.dataset.status === status)
            ? 'block' : 'none';
    });
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
        `Pesanan #${p.kode} (${p.layanan}) milik ${p.nama} akan dibatalkan. Masukkan alasan (opsional).`;
    document.getElementById('inputAlasanLainnya').value = '';
    document.getElementById('overlayBatalAdmin').style.display  = 'block';
    document.getElementById('popupBatalAdmin').style.display    = 'block';
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
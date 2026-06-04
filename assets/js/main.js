// ── DATA DUMMY & GLOBALS ────────────────────────────────────
const dataPesanan = {
    1: {
        nama: "Ryan Liam", username: "@liam999",
        waktu: "10:00 Rabu, 04-12-2026",
        namaLengkap: "Ryan Liam Santoso",
        alamat: "Jl. Paal 4 No. 12, Ling. III",
        kecamatan: "Wanea",
        telpon: "0834545827",
        layanan: "Express", pengiriman: "Antar",
        tarifLayanan: 15000, tarifKirim: 5000,
        tags: [{label:"Cuci",tipe:"hijau"},{label:"Express",tipe:"biru"},{label:"Antar",tipe:"biru"}],
        status: "baru", berat: null,
        note: "Tolong pisahkan baju putih."
    },
    2: {
        nama: "Sinta Dewi", username: "@sintad",
        waktu: "08:30 Rabu, 04-12-2026",
        namaLengkap: "Sinta Dewi Rahayu",
        alamat: "Jl. Bahu Lingkungan I No. 5",
        kecamatan: "Malalayang",
        telpon: "0812345678",
        layanan: "Reguler", pengiriman: "Pickup",
        tarifLayanan: 8000, tarifKirim: 0,
        tags: [{label:"Cuci",tipe:"hijau"},{label:"Reguler",tipe:"biru"},{label:"Pickup",tipe:"biru"}],
        status: "diproses", berat: 3,
        note: null
    },
    3: {
        nama: "Budi Santoso", username: "@budis",
        waktu: "07:00 Rabu, 04-12-2026",
        namaLengkap: "Budi Santoso",
        alamat: "Jl. Tikala Ares No. 88",
        kecamatan: "Tikala",
        telpon: "0856789012",
        layanan: "Reguler", pengiriman: "Antar",
        tarifLayanan: 8000, tarifKirim: 5000,
        tags: [{label:"Dry Cleaning",tipe:"hijau"},{label:"Reguler",tipe:"biru"},{label:"Antar",tipe:"biru"}],
        status: "selesai", berat: 2,
        note: null
    },
    4: {
        nama: "Mega Putri", username: "@megap",
        waktu: "06:15 Rabu, 04-12-2026",
        namaLengkap: "Mega Putri Wulandari",
        alamat: "Komp. Malalayang Permai Blok C No.3",
        kecamatan: "Malalayang",
        telpon: "0878901234",
        layanan: "Express", pengiriman: "Pickup",
        tarifLayanan: 15000, tarifKirim: 0,
        tags: [{label:"Cuci",tipe:"hijau"},{label:"Express",tipe:"biru"},{label:"Pickup",tipe:"biru"}],
        status: "baru", berat: null,
        note: null
    }
};

let idAktif = null;

// ── BUKA & TUTUP DETAIL ────────────────────────────────────
function bukaPesanan(id, el) {
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

    // Memanggil fungsi dari file JS lain
    setStatusUI(p.status);
    hitungBiaya();
}

function kembaliKeList() {
    document.getElementById('detailKosong').style.display = 'flex';
    document.getElementById('detailIsi').style.display    = 'none';
    document.querySelectorAll('.item-pesanan').forEach(i => i.classList.remove('aktif-dipilih'));
    idAktif = null;
}

// ── FILTER ────────────────────────────────────────────────
function filterPesanan(status, btn) {
    document.querySelectorAll('.tombol-filter').forEach(b => b.classList.remove('aktif'));
    btn.classList.add('aktif');
    document.querySelectorAll('.item-pesanan').forEach(item => {
        item.style.display = (status === 'semua' || item.dataset.status === status)
            ? 'block' : 'none';
    });
}
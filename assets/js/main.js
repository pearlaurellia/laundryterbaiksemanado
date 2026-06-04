const dataPesanan = [
    { nama: "Sasha Cantik", username: "@liam999", waktu: "10:00 Rabu, 04-12-2026", alamat: "Paal 4, Manado", telpon: "0834545827", layanan: "Express", pengiriman: "Antar", tags: ["Cuci", "Express", "Antar"], tagTypes: ["hijau", "biru", "biru"], status: "baru", berat: null, note: "Tolong pisahkan baju putih." },
    { nama: "Yoo Haram", username: "@yooHaram", waktu: "08:30 Rabu, 04-12-2026", alamat: "Bahu, Manado", telpon: "0812345678", layanan: "Reguler", pengiriman: "Pickup", tags: ["Cuci", "Reguler", "Pickup"], tagTypes: ["hijau", "biru", "biru"], status: "diproses", berat: 3, note: null },
    { nama: "Karina AESPA", username: "@karinaAESPA", waktu: "07:00 Rabu, 04-12-2026", alamat: "Tikala, Manado", telpon: "0856789012", layanan: "Reguler", pengiriman: "Antar", tags: ["Dry Cleaning", "Reguler", "Antar"], tagTypes: ["hijau", "biru", "biru"], status: "selesai", berat: 2, note: null },
    { nama: "Hannah Dodd", username: "@hannahDodd", waktu: "06:15 Rabu, 04-12-2026", alamat: "Malalayang, Manado", telpon: "0878901234", layanan: "Express", pengiriman: "Pickup", tags: ["Cuci", "Express", "Pickup"], tagTypes: ["hijau", "biru", "biru"], status: "baru", berat: null, note: null }
];

const hargaLayanan = { "Reguler": 8000, "Express": 15000, "Dry Cleaning": 25000 };
const hargaKirim   = { "Antar": 5000, "Pickup": 0 };
let indexAktif = 0;

function bukaPesanan(index, el) {
    indexAktif = index;
    const p = dataPesanan[index];
    
    document.querySelectorAll('.item-pesanan').forEach(i => i.classList.remove('aktif-dipilih'));
    el.classList.add('aktif-dipilih');
    
    document.getElementById('detailKosong').style.display = 'none';
    document.getElementById('detailIsi').style.display   = 'block';
    
    document.getElementById('detailNama').textContent     = p.nama;
    document.getElementById('detailUsername').textContent = p.username;
    document.getElementById('detailWaktu').textContent    = p.waktu;
    document.getElementById('detailAlamat').textContent   = p.alamat;
    document.getElementById('detailTelpon').textContent   = p.telpon;
    document.getElementById('detailLayanan').textContent  = p.layanan;
    document.getElementById('detailPengiriman').textContent = p.pengiriman;
    document.getElementById('detailNote').textContent     = p.note || '—';
    document.getElementById('inputBerat').value           = p.berat || '';
    
    const tagsEl = document.getElementById('detailTags');
    tagsEl.innerHTML = p.tags.map((t, i) =>
        `<span class="badge-${p.tagTypes[i]}">${t}</span>`
    ).join('');
    
    setStatusUI(p.status);
    hitungBiaya();
}

function filterPesanan(status, btn) {
    document.querySelectorAll('.tombol-filter').forEach(b => b.classList.remove('aktif'));
    btn.classList.add('aktif');
    document.querySelectorAll('.item-pesanan').forEach(item => {
        const cocok = status === 'semua' || item.dataset.status === status;
        item.style.display = cocok ? 'block' : 'none';
    });
}
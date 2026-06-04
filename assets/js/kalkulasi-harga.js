// ── HITUNG BIAYA ───────────────────────────────────────────
function hitungBiaya() {
    if (!idAktif) return;
    
    // Mengambil data dari main.js
    const p     = dataPesanan[idAktif];
    const berat = parseFloat(document.getElementById('inputBerat').value) || 0;
    
    dataPesanan[idAktif].berat = berat || null;

    const biayaLayanan = berat * p.tarifLayanan;
    const total        = biayaLayanan + p.tarifKirim;
    const fmt = n => 'Rp ' + n.toLocaleString('id-ID');

    document.getElementById('rincianLayanan').textContent =
        `${p.layanan} (${berat} kg × ${fmt(p.tarifLayanan)}) : ${fmt(biayaLayanan)}`;
        
    document.getElementById('rincianKirim').textContent =
        `Pengiriman (${p.pengiriman}) : ${fmt(p.tarifKirim)}`;
        
    document.getElementById('rincianTotal').textContent =
        `Total : ${fmt(total)}`;
}
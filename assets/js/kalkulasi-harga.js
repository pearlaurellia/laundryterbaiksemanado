function hitungBiaya() {
    const p     = dataPesanan[indexAktif];
    const berat = parseFloat(document.getElementById('inputBerat').value) || 0;
    
    dataPesanan[indexAktif].berat = berat || null;
    
    const tarifLayanan = hargaLayanan[p.layanan] || 0;
    const tarifKirim   = hargaKirim[p.pengiriman] || 0;
    const biayaLayanan = berat * tarifLayanan;
    const total        = biayaLayanan + tarifKirim;
    
    const fmt = n => 'Rp ' + n.toLocaleString('id-ID');
    document.getElementById('rincianLayanan').textContent = `${p.layanan} (${berat}kg × ${fmt(tarifLayanan)}) : ${fmt(biayaLayanan)}`;
    document.getElementById('rincianKirim').textContent   = `Pengiriman (${p.pengiriman}) : ${fmt(tarifKirim)}`;
    document.getElementById('rincianTotal').textContent   = `Total : ${fmt(total)}`;
}
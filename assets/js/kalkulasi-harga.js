const BIAYA_KURIR = 10000;

function hitungEstimasiAdmin(layananAktif, opsiPengantaran) {
    const berat      = parseFloat(document.getElementById('inputEstimasiBerat').value) || 0;
    const kotakEl    = document.getElementById('kotakEstimasi');
    const teksEl     = document.getElementById('teksEstimasiHarga');
    const biayaKurir = opsiPengantaran === 'kurir' ? BIAYA_KURIR : 0;

    if (berat <= 0) {
        kotakEl.classList.remove('kotak-estimasi-ada');
        teksEl.innerHTML = 'Harga akan dihitung admin setelah pakaian ditimbang.';
        return;
    }

    const fmt          = n => 'Rp ' + n.toLocaleString('id-ID');
    const biayaLayanan = berat * layananAktif.tarif;
    const total        = biayaLayanan + biayaKurir;

    kotakEl.classList.add('kotak-estimasi-ada');
    teksEl.innerHTML = `
        <span class="estimasi-label">Estimasi Biaya</span><br>
        ${layananAktif.nama} (${berat} kg × ${fmt(layananAktif.tarif)})
        = ${fmt(biayaLayanan)}<br>
        ${opsiPengantaran === 'kurir' ? `Kurir = ${fmt(biayaKurir)}<br>` : ''}
        <strong>Total Estimasi : ${fmt(total)}</strong>
        <span class="estimasi-belum-final">— Harga Belum Final</span>
    `;
}

function hitungBiaya() {
    if (typeof idAktif === 'undefined' || !idAktif) return;

    const p     = dataPesanan[idAktif];
    const berat = parseFloat(document.getElementById('inputBerat').value) || 0;
    const fmt   = n => 'Rp ' + n.toLocaleString('id-ID');

    dataPesanan[idAktif].berat = berat || null;

    const biayaLayanan = berat * p.tarifLayanan;
    const total        = biayaLayanan + p.tarifKirim;

    document.getElementById('rincianLayanan').textContent =
        `${p.layanan} (${berat} kg × ${fmt(p.tarifLayanan)}) : ${fmt(biayaLayanan)}`;
    document.getElementById('rincianKirim').textContent =
        `Pengiriman (${p.pengiriman}) : ${fmt(p.tarifKirim)}`;
    document.getElementById('rincianTotal').textContent =
        `Total : ${fmt(total)}`;
}
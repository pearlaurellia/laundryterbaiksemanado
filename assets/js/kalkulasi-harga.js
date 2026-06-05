/**
 * ============================================================
 * kalkulasi-harga.js — CleanCo Laundry
 * ============================================================
 */

'use strict';

function hitungBiaya() {
    if (typeof idAktif === 'undefined' || !idAktif) return;
    if (!dataPesanan || !dataPesanan[idAktif]) return;

    const p     = dataPesanan[idAktif];
    const berat = parseFloat(document.getElementById('inputBerat')?.value) || parseFloat(p.berat_aktual) || 0;
    const fmt   = n => 'Rp ' + (n || 0).toLocaleString('id-ID');

    // Sinkronisasi data lokal dari properti PHP database query
    dataPesanan[idAktif].berat_aktual = berat || null;

    const biayaLayanan = berat * (parseFloat(p.tarif_per_kg) || 0);
    const total        = biayaLayanan + (parseFloat(p.biaya_kurir) || 0);

    const rincianLayananEl = document.getElementById('rincianLayanan');
    const rincianKirimEl   = document.getElementById('rincianKirim');
    const rincianTotalEl   = document.getElementById('rincianTotal');

    if (rincianLayananEl) {
        rincianLayananEl.textContent =
            `${p.nama_layanan} (${berat} kg × ${fmt(p.tarif_per_kg)}) : ${fmt(biayaLayanan)}`;
    }
    if (rincianKirimEl) {
        const labelKirim = p.opsi_pengantaran === 'kurir' ? 'Kurir Antar-Jemput' : 'Ambil Mandiri';
        rincianKirimEl.textContent = `Pengiriman (${labelKirim}) : ${fmt(p.biaya_kurir)}`;
    }
    if (rincianTotalEl) {
        rincianTotalEl.textContent = `Total : ${fmt(total)}`;
    }
}

function hitungEstimasiAdmin(layananAktif, opsiPengantaran) {
    const beratEl  = document.getElementById('inputEstimasiBerat');
    const kotakEl  = document.getElementById('kotakEstimasi');
    const teksEl   = document.getElementById('teksEstimasiHarga');
    if (!beratEl || !kotakEl || !teksEl) return;

    const berat      = parseFloat(beratEl.value) || 0;
    const _BIAYA_KURIR = (typeof BIAYA_KURIR !== 'undefined') ? BIAYA_KURIR : 10000;
    const biayaKurir = opsiPengantaran === 'kurir' ? _BIAYA_KURIR : 0;
    const fmt        = n => 'Rp ' + n.toLocaleString('id-ID');

    if (berat <= 0) {
        kotakEl.classList.remove('kotak-estimasi-ada');
        teksEl.innerHTML = 'Harga akan dihitung admin setelah pakaian ditimbang.';
        return;
    }

    const biayaLayanan = berat * (layananAktif.tarif || 0);
    const total        = biayaLayanan + biayaKurir;

    kotakEl.classList.add('kotak-estimasi-ada');
    teksEl.innerHTML = `
        <span class="estimasi-label">Estimasi Biaya</span><br>
        ${layananAktif.nama} (${berat} kg × ${fmt(layananAktif.tarif)}) = ${fmt(biayaLayanan)}<br>
        ${opsiPengantaran === 'kurir' ? `Kurir = ${fmt(biayaKurir)}<br>` : ''}
        <strong>Total Estimasi : ${fmt(total)}</strong>
        <span class="estimasi-belum-final">— Harga Belum Final</span>
    `;
}
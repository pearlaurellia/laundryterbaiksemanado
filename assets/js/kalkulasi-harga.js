'use strict';

let tarifAktif = 0;
let namaLayananAktif = '';
let biayaKurir = 0;

document.addEventListener('DOMContentLoaded', () => {
    const selectLayanan = document.getElementById('inputLayananId');
    const radiosPengantaran = document.querySelectorAll('input[name="opsi_pengantaran"]');
    const inputBerat = document.getElementById('inputEstimasiBerat');

    if (selectLayanan) {
        selectLayanan.addEventListener('change', function() {
            const optionTerpilih = this.options[this.selectedIndex];
            
            if (optionTerpilih && this.value !== '') {
                tarifAktif = parseFloat(optionTerpilih.dataset.tarif) || 0;
                namaLayananAktif = optionTerpilih.dataset.nama || '';
            } else {
                tarifAktif = 0;
                namaLayananAktif = '';
            }
            hitungEstimasi();
        });
    }

    radiosPengantaran.forEach(radio => {
        radio.addEventListener('change', function() {
            const containerAlamat = document.getElementById('containerAlamat');
            const inputAlamat = document.getElementById('inputAlamat');
            const inputKecamatan = document.getElementById('inputKecamatan');

            if (this.value === 'kurir') {
                if (containerAlamat) containerAlamat.style.display = 'block';
                biayaKurir = 10000;
                
                if (inputAlamat) inputAlamat.setAttribute('required', 'required');
                if (inputKecamatan) inputKecamatan.setAttribute('required', 'required');
            } else {
                if (containerAlamat) containerAlamat.style.display = 'none';
                biayaKurir = 0;
                
                if (inputAlamat) {
                    inputAlamat.value = '';
                    inputAlamat.removeAttribute('required');
                }
                if (inputKecamatan) {
                    inputKecamatan.value = '';
                    inputKecamatan.removeAttribute('required');
                }
            }
            hitungEstimasi();
        });
    });

    if (inputBerat) {
        inputBerat.addEventListener('input', () => {
            hitungEstimasi();
        });
    }
});

function hitungEstimasi() {
    const inputBerat = document.getElementById('inputEstimasiBerat');
    const kotakEstimasi = document.getElementById('kotakEstimasi');
    const teksEstimasiHarga = document.getElementById('teksEstimasiHarga');

    if (!inputBerat || !kotakEstimasi || !teksEstimasiHarga) return;

    const berat = parseFloat(inputBerat.value) || 0;
    const fmt = n => 'Rp ' + n.toLocaleString('id-ID');

    if (berat <= 0 || tarifAktif === 0) {
        kotakEstimasi.classList.remove('kotak-estimasi-ada');
        kotakEstimasi.style.background = '#f9fafb';
        kotakEstimasi.style.borderColor = '#e5e7eb';
        
        teksEstimasiHarga.innerHTML = `
            <span style="color: #6b7280; font-style: italic; font-size: 0.9rem;">
                Harga total akhir akan dihitung oleh admin setelah pakaian ditimbang secara aktual di outlet.
            </span>
        `;
        return;
    }

    const biayaLayanan = berat * tarifAktif;
    const totalHargaEstimasi = biayaLayanan + biayaKurir;

    kotakEstimasi.classList.add('kotak-estimasi-ada');
    
    kotakEstimasi.style.background = '#f0fdf4'; 
    kotakEstimasi.style.borderColor = '#bbf7d0';

    teksEstimasiHarga.innerHTML = `
        <div style="font-family: 'DM Sans', sans-serif; font-size: 0.9rem; color: #1e293b; line-height: 1.6;">
            <strong style="color: #16a34a; font-size: 1rem; display: block; margin-bottom: 6px;">📋 Ringkasan Estimasi Biaya</strong>
            <span>Layanan: <strong>${namaLayananAktif}</strong> (${berat} kg × ${fmt(tarifAktif)}) = <strong>${fmt(biayaLayanan)}</strong></span><br>
            ${biayaKurir > 0 ? `<span>Biaya Kurir Antar-Jemput = <strong>${fmt(biayaKurir)}</strong></span><br>` : ''}
            <div style="margin-top: 8px; padding-top: 8px; border-top: 1px dashed #cdcde0; font-size: 1.05rem;">
                <strong>Total Pembayaran: <span style="color: #0d3f8a;">${fmt(totalHargaEstimasi)}</span></strong>
            </div>
            <small style="color: #ef4444; font-weight: bold; display: block; margin-top: 4px;">* Catatan: Harga belum final (menunggu timbangan resmi toko).</small>
        </div>
    `;
}
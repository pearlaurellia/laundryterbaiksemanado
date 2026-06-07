'use strict';

window.tarifAktif = 0;
window.namaLayananAktif = '';
window.biayaKurir = 0;

document.addEventListener('DOMContentLoaded', () => {
    const selectLayanan = document.getElementById('inputLayananId');
    const radiosPengantaran = document.querySelectorAll('input[name="opsi_pengantaran"]');
    const inputBerat = document.getElementById('inputEstimasiBerat');

    if (selectLayanan) {
        selectLayanan.addEventListener('change', function() {
            const optionTerpilih = this.options[this.selectedIndex];
            
            if (optionTerpilih && this.value !== '') {
                window.tarifAktif = parseFloat(optionTerpilih.dataset.tarif) || 0;
                window.namaLayananAktif = optionTerpilih.dataset.nama || '';
            } else {
                window.tarifAktif = 0;
                window.namaLayananAktif = '';
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
                window.biayaKurir = 10000;
                if (inputAlamat) inputAlamat.setAttribute('required', 'required');
                if (inputKecamatan) inputKecamatan.setAttribute('required', 'required');
            } else {
                if (containerAlamat) containerAlamat.style.display = 'none';
                window.biayaKurir = 0;
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

    const selectedCard = document.querySelector('.kartu-pilih-layanan.dipilih');
    if (selectedCard) {
        window.tarifAktif = parseFloat(selectedCard.dataset.tarif) || 0;
        window.namaLayananAktif = selectedCard.dataset.nama || '';
        window.satuanAktif = selectedCard.dataset.satuan || 'kg';
        const labelSatuan = document.getElementById('labelSatuan');
        if (labelSatuan) labelSatuan.textContent = window.satuanAktif;
    }
});

function hitungEstimasi() {
    const inputBerat = document.getElementById('inputEstimasiBerat');
    const kotakEstimasi = document.getElementById('kotakEstimasi');
    const teksEstimasiHarga = document.getElementById('teksEstimasiHarga');

    if (!inputBerat || !kotakEstimasi || !teksEstimasiHarga) return;

    const berat = parseFloat(inputBerat.value) || 0;
    const fmt = n => 'Rp ' + n.toLocaleString('id-ID');

    if (berat <= 0 || window.tarifAktif === 0) {
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

    const biayaLayanan = berat * window.tarifAktif;
    const totalHargaEstimasi = biayaLayanan + window.biayaKurir;

    kotakEstimasi.classList.add('kotak-estimasi-ada');
    kotakEstimasi.style.background = '#ffffff';
    kotakEstimasi.style.borderColor = '#52c49c';
    kotakEstimasi.style.borderWidth = "2px";

    teksEstimasiHarga.innerHTML = `
        <div style="font-family: 'DM Sans', sans-serif; font-size: 0.9rem; color: #7b7b7b; line-height: 1.6;">
            <strong style="font-family: 'Bricolage Grotesque', sans-serif; color: #7b7b7b; font-size: 0.9rem; display: block; margin-bottom: 6px;">Ringkasan Estimasi Biaya</strong>
            <span>Layanan: <strong>${window.namaLayananAktif}</strong> (${berat} ${window.satuanAktif || 'kg'} × ${fmt(window.tarifAktif)}) = <strong>${fmt(biayaLayanan)}</strong></span><br>
            ${window.biayaKurir > 0 ? `<span>Biaya Kurir Antar-Jemput = <strong>${fmt(window.biayaKurir)}</strong></span><br>` : ''}
            <div style= "font-size: 0.9rem;">
                <strong>Total Pembayaran: <span style="color: #52c49c;">${fmt(totalHargaEstimasi)}</span></strong>
            </div>
            <small style="color: #ef4444; font-weight: bold; display: block; margin-top: 4px;">* Catatan: Harga belum final (menunggu timbangan resmi toko).</small>
        </div>
    `;
}
/**
 * ============================================================
 * kalkulasi-harga.js — CleanCo Laundry
 * Digunakan di: member/pesan.php
 * Murni Native JavaScript (Tanpa Library/Framework)
 *
 * Mengelola kalkulasi estimasi harga di sisi klien secara real-time
 * dan mengatur visibilitas kolom alamat kurir antar-jemput.
 * ============================================================
 */

'use strict';

// ── STATE FORM INTERNAL MEMBER ──────────────────────────────
let tarifAktif = 0;
let namaLayananAktif = '';
let biayaKurir = 0;

/**
 * Menginisialisasi event listener form pemesanan saat dokumen selesai dimuat.
 * Menghubungkan element HTML secara langsung menggunakan pure native event.
 */
document.addEventListener('DOMContentLoaded', () => {
    const selectLayanan = document.getElementById('inputLayananId');
    const radiosPengantaran = document.querySelectorAll('input[name="opsi_pengantaran"]');
    const inputBerat = document.getElementById('inputEstimasiBerat');

    // 1. Event Listener: Mengamati Perubahan Dropdown Layanan
    if (selectLayanan) {
        selectLayanan.addEventListener('change', function() {
            // Mengambil data attribute dari option yang sedang dipilih oleh member
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

    // 2. Event Listener: Mengamati Perubahan Radio Opsi Pengantaran
    radiosPengantaran.forEach(radio => {
        radio.addEventListener('change', function() {
            const containerAlamat = document.getElementById('containerAlamat');
            const inputAlamat = document.getElementById('inputAlamat');
            const inputKecamatan = document.getElementById('inputKecamatan');

            if (this.value === 'kurir') {
                // Tampilkan kolom alamat & set biaya kurir standar Rp 10.000
                if (containerAlamat) containerAlamat.style.display = 'block';
                biayaKurir = 10000;
                
                // Set agar input alamat wajib diisi
                if (inputAlamat) inputAlamat.setAttribute('required', 'required');
                if (inputKecamatan) inputKecamatan.setAttribute('required', 'required');
            } else {
                // Sembunyikan kolom alamat & hilangkan biaya kurir
                if (containerAlamat) containerAlamat.style.display = 'none';
                biayaKurir = 0;
                
                // Bersihkan nilai dan hapus status required
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

    // 3. Event Listener: Mengamati Input Berat Estimasi secara Real-Time
    if (inputBerat) {
        inputBerat.addEventListener('input', () => {
            hitungEstimasi();
        });
    }
});

/**
 * Fungsi Inti: Menghitung nilai estimasi nota belanja dan melakukan manipulasi UI
 */
function hitungEstimasi() {
    const inputBerat = document.getElementById('inputEstimasiBerat');
    const kotakEstimasi = document.getElementById('kotakEstimasi');
    const teksEstimasiHarga = document.getElementById('teksEstimasiHarga');

    if (!inputBerat || !kotakEstimasi || !teksEstimasiHarga) return;

    const berat = parseFloat(inputBerat.value) || 0;
    const fmt = n => 'Rp ' + n.toLocaleString('id-ID');

    // Kondisi A: Jika berat kosong, bernilai 0, atau belum memilih layanan laundry
    if (berat <= 0 || tarifAktif === 0) {
        kotakEstimasi.classList.remove('kotak-estimasi-ada');
        // Reset style box ke posisi semula (abu-abu/soft)
        kotakEstimasi.style.background = '#f9fafb';
        kotakEstimasi.style.borderColor = '#e5e7eb';
        
        teksEstimasiHarga.innerHTML = `
            <span style="color: #6b7280; font-style: italic; font-size: 0.9rem;">
                Harga total akhir akan dihitung oleh admin setelah pakaian ditimbang secara aktual di outlet.
            </span>
        `;
        return;
    }

    // Kondisi B: Melakukan Operasi Matematika Finansial Klien
    const biayaLayanan = berat * tarifAktif;
    const totalHargaEstimasi = biayaLayanan + biayaKurir;

    // Hidupkan class CSS aktif untuk animasi box atau highlight
    kotakEstimasi.classList.add('kotak-estimasi-ada');
    
    // Beri sentuhan warna hijau/biru sukses murni lewat inline style agar aman
    kotakEstimasi.style.background = '#f0fdf4'; 
    kotakEstimasi.style.borderColor = '#bbf7d0';

    // Cetak rincian nota belanja murni HTML String
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
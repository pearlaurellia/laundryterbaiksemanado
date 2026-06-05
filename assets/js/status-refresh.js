/**
 * ============================================================
 * status-refresh.js — CleanCo Laundry
 * Digunakan di: member/status.php
 * Murni Native JavaScript (Tanpa Library/Framework)
 *
 * Mengelola hitung mundur penyegaran halaman otomatis secara live,
 * serta manajemen interaksi buka-tutup modal popup pembatalan
 * dan notifikasi penolakan dari pihak manajemen admin.
 * ============================================================
 */

'use strict';

// ── 1. AUTOMATIC LIVE MONITORING TIMER (60 DETIK) ──────────
document.addEventListener('DOMContentLoaded', () => {
    // Melakukan reload halaman berkala agar progress laundry selalu terupdate otomatis
    setInterval(() => {
        location.reload();
    }, 60000);
});


// ── 2. HANDLER MODAL POPUP KONFIRMASI PEMBATALAN MEMBER ─────
/**
 * Membuka jendela popup konfirmasi pembatalan pesanan di sisi pelanggan.
 * @param {number|string} idPesanan - ID baris database pesanan
 * @param {string} kodePesanan - Kode unik transaksi (Contoh: LDR-2026...)
 * @param {string} namaLayanan - Nama paket pencucian yang dipilih
 */
function konfirmasiBatal(idPesanan, kodePesanan, namaLayanan) {
    const teksBatal = document.getElementById('popupBatalTeks');
    const inputId = document.getElementById('inputIdPesananBatal');
    const overlay = document.getElementById('overlayPopup');
    const popup = document.getElementById('popupBatal');

    // Suntik informasi rincian nota target ke dalam modal dengan cetak tebal (bold)
    if (teksBatal) {
        teksBatal.innerHTML = `Pesanan <strong style="color: #ef4444;">#${kodePesanan}</strong> (${namaLayanan}) akan dibatalkan secara permanen dan tidak dapat dikembalikan.`;
    }
    
    // Pasang ID pesanan ke dalam hidden input form POST
    if (inputId) {
        inputId.value = idPesanan;
    }
    
    // Tampilkan elemen modal ke layar browser
    if (overlay) overlay.style.display = 'block';
    if (popup) popup.style.display = 'block';
}

/**
 * Menutup jendela popup konfirmasi pembatalan pesanan.
 */
function tutupPopupBatal() {
    const overlay = document.getElementById('overlayPopup');
    const popup = document.getElementById('popupBatal');
    
    if (overlay) overlay.style.display = 'none';
    if (popup) popup.style.display = 'none';
}


// ── 3. HANDLER CLOSE MODAL BANNER NOTIFIKASI ADMIN ──────────
/**
 * Menyembunyikan popup pemberitahuan pembatalan sepihak dari admin (jika ada).
 */
function tutupNotifBatal() {
    const overlay = document.getElementById('overlayNotifBatal');
    const popup = document.getElementById('popupNotifBatal');
    
    if (overlay) overlay.style.display = 'none';
    if (popup) popup.style.display = 'none';
}
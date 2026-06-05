/**
 * ============================================================
 * kalkulasi-harga.js — CleanCo Laundry
 * Digunakan di: admin/pesanan.php, member/pesan.php
 *
 * Berisi:
 *   - hitungBiaya()          → hitung dan tampilkan rincian biaya di panel admin
 *   - hitungEstimasiAdmin()  → versi admin (tidak dipakai di pesan.php)
 *
 * CATATAN:
 * - hitungBiaya() bergantung pada dataPesanan dan idAktif dari main.js
 * - Konstanta BIAYA_KURIR didefinisikan di pesan-member.js (untuk form member)
 *   dan di sini hanya digunakan sebagai fallback
 * ============================================================
 */

'use strict';

/**
 * Hitung dan render rincian biaya di panel detail admin (admin/pesanan.php).
 * Dipanggil oleh oninput pada #inputBerat dan setelah bukaPesanan().
 *
 * Membaca:
 *   - dataPesanan[idAktif].tarifLayanan : tarif per kg layanan
 *   - dataPesanan[idAktif].tarifKirim   : biaya kurir (0 jika ambil sendiri)
 *   - dataPesanan[idAktif].layanan      : nama layanan (untuk label)
 *   - dataPesanan[idAktif].pengiriman   : label pengiriman
 *
 * CATATAN BACKEND:
 * tarifLayanan dan tarifKirim sudah ada di objek pesanan yang di-return
 * oleh GET /api/pesanan — pastikan PHP menyertakan kedua field ini.
 */
function hitungBiaya() {
    if (typeof idAktif === 'undefined' || !idAktif) return;
    if (!dataPesanan || !dataPesanan[idAktif]) return;

    const p     = dataPesanan[idAktif];
    const berat = parseFloat(document.getElementById('inputBerat')?.value) || 0;
    const fmt   = n => 'Rp ' + (n || 0).toLocaleString('id-ID');

    // Update cache lokal (dipakai oleh prosesTimbang untuk dikirim ke server)
    dataPesanan[idAktif].berat = berat || null;

    const biayaLayanan = berat * (p.tarifLayanan || 0);
    const total        = biayaLayanan + (p.tarifKirim || 0);

    const rincianLayananEl = document.getElementById('rincianLayanan');
    const rincianKirimEl   = document.getElementById('rincianKirim');
    const rincianTotalEl   = document.getElementById('rincianTotal');

    if (rincianLayananEl) {
        rincianLayananEl.textContent =
            `${p.layanan} (${berat} kg × ${fmt(p.tarifLayanan)}) : ${fmt(biayaLayanan)}`;
    }
    if (rincianKirimEl) {
        rincianKirimEl.textContent =
            `Pengiriman (${p.pengiriman}) : ${fmt(p.tarifKirim)}`;
    }
    if (rincianTotalEl) {
        rincianTotalEl.textContent = `Total : ${fmt(total)}`;
    }
}


/**
 * Versi estimasi untuk form member (opsional — bisa juga pakai hitungEstimasi()
 * di pesan-member.js langsung). Disediakan di sini untuk konsistensi jika
 * kalkulasi-harga.js di-include di halaman lain.
 *
 * PARAMETER:
 *   layananAktif    : { nama, tarif }
 *   opsiPengantaran : 'kurir'|'ambil_sendiri'
 *
 * CATATAN BACKEND:
 * BIAYA_KURIR sebaiknya di-output dari DB oleh PHP — lihat pesan-member.js.
 */
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
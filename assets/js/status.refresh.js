/**
 * ============================================================
 * status_refresh.js — CleanCo Laundry
 * Digunakan di: admin/pesanan.php
 *
 * Berisi:
 *   - setStatusUI()       → render tombol aksi dan teks status
 *   - _renderTombolAksi() → buat HTML tombol sesuai tahap
 *   - prosesTimbang()     → validasi berat lalu update ke sedang_dicuci
 *   - updateStatus()      → kirim perubahan status ke server
 *   - startAutoRefresh()  → polling 60 detik (aktifkan saat backend siap)
 *
 * CATATAN:
 * File ini bergantung pada fungsi-fungsi dari main.js yang harus
 * di-include SEBELUM file ini:
 *   - dataPesanan, idAktif (state global)
 *   - muatDataPesanan(), renderListPesanan(), _updateStatusPesanan()
 *   - _labelStatus, _kelasStatus
 *   - batalkanPesananAdmin()
 *   - hitungBiaya() (dari kalkulasi-harga.js)
 * ============================================================
 */

'use strict';

// ── SET STATUS UI ──────────────────────────────────────────
/**
 * Update tampilan panel detail sesuai status pesanan aktif.
 * Dipanggil oleh bukaPesanan() dan updateStatus().
 */
function setStatusUI(status) {
    // Teks status aktif
    const teksEl = document.getElementById('statusAktifTeks');
    if (teksEl) teksEl.textContent = _labelStatus[status] || status;

    // Render tombol aksi bertahap
    const grupAksiEl = document.getElementById('grupAksiAdmin');
    if (grupAksiEl) {
        grupAksiEl.innerHTML = _renderTombolAksi(status);
    }

    // Tombol "Batalkan Pesanan Ini"
    // Hanya tampil jika pesanan masih aktif (bukan selesai/dibatalkan)
    const tombolBatal = document.getElementById('tombolBatalkanAdmin');
    const infoBatal   = document.getElementById('infoSudahDibatalkan');
    if (tombolBatal && infoBatal) {
        if (status === 'dibatalkan') {
            tombolBatal.style.display = 'none';
            infoBatal.style.display   = 'block';
        } else if (status === 'selesai') {
            tombolBatal.style.display = 'none';
            infoBatal.style.display   = 'none';
        } else {
            tombolBatal.style.display = 'inline-block';
            infoBatal.style.display   = 'none';
        }
    }
}


// ── RENDER TOMBOL AKSI ─────────────────────────────────────
/**
 * Kembalikan HTML tombol aksi sesuai status saat ini.
 * Alur status sesuai dokumen:
 *
 *   menunggu_konfirmasi
 *     ├── [Terima] → dikonfirmasi
 *     └── [Tolak]  → batalkan (popup)
 *   dikonfirmasi
 *     └── [Proses & Timbang] → sedang_dicuci  (perlu input berat)
 *   sedang_dicuci
 *     ├── kurir        → [Sedang Diantar]
 *     └── ambil_sendiri → [Siap Diambil]
 *   siap_diambil | sedang_diantar
 *     └── [Selesai & Lunas] → selesai
 *   selesai / dibatalkan → tidak ada aksi lanjutan
 */
function _renderTombolAksi(status) {
    if (status === 'menunggu_konfirmasi') {
        return `
            <button class="tombol-status tombol-status-terima"
                    onclick="updateStatus('dikonfirmasi')">
                ✓ Terima Pesanan
            </button>
            <button class="tombol-status tombol-status-tolak"
                    onclick="batalkanPesananAdmin(idAktif)">
                ✕ Tolak Pesanan
            </button>`;
    }

    if (status === 'dikonfirmasi') {
        return `
            <button class="tombol-status tombol-status-aktif"
                    data-status="dikonfirmasi" disabled>
                Dikonfirmasi
            </button>
            <button class="tombol-status"
                    data-status="sedang_dicuci"
                    onclick="prosesTimbang()">
                ⚖ Proses & Timbang
            </button>`;
    }

    if (status === 'sedang_dicuci') {
        const p              = dataPesanan[idAktif];
        const opsi           = p ? p.opsi : 'kurir';
        const berikutStatus  = opsi === 'kurir' ? 'sedang_diantar' : 'siap_diambil';
        const berikutLabel   = opsi === 'kurir' ? 'Sedang Diantar'  : 'Siap Diambil';
        return `
            <button class="tombol-status tombol-status-aktif"
                    data-status="sedang_dicuci" disabled>
                Sedang Dicuci
            </button>
            <button class="tombol-status"
                    data-status="${berikutStatus}"
                    onclick="updateStatus('${berikutStatus}')">
                → ${berikutLabel}
            </button>`;
    }

    if (status === 'siap_diambil' || status === 'sedang_diantar') {
        return `
            <button class="tombol-status tombol-status-aktif"
                    data-status="${status}" disabled>
                ${_labelStatus[status]}
            </button>
            <button class="tombol-status"
                    data-status="selesai"
                    onclick="updateStatus('selesai')">
                ✓ Selesai & Lunas
            </button>`;
    }

    if (status === 'selesai') {
        return `
            <button class="tombol-status tombol-status-aktif"
                    data-status="selesai" disabled>
                Selesai & Lunas
            </button>`;
    }

    if (status === 'dibatalkan') {
        return `
            <button class="tombol-status"
                    style="background:#FFD1D1; color:#D32F2F;" disabled>
                Dibatalkan
            </button>`;
    }

    return '';
}


// ── PROSES & TIMBANG ───────────────────────────────────────
/**
 * Validasi berat aktual lalu kirim update ke status sedang_dicuci.
 * Berat dikirim sekaligus ke server agar total_harga bisa dihitung.
 *
 * BACKEND: lihat _updateStatusPesanan() di main.js.
 * Server harus menyimpan berat_aktual dan menghitung:
 *   total_harga = (berat * tarif_per_kg) + biaya_kurir
 */
async function prosesTimbang() {
    const beratEl = document.getElementById('inputBerat');
    const berat   = parseFloat(beratEl ? beratEl.value : 0);

    if (!berat || berat <= 0) {
        alert('Masukkan berat aktual terlebih dahulu sebelum memproses.');
        if (beratEl) beratEl.focus();
        return;
    }

    if (!idAktif) return;

    // Kirim berat sekaligus dengan perubahan status
    await _updateStatusPesanan(idAktif, 'sedang_dicuci', null, null, berat);

    // Update cache lokal agar hitungBiaya() bisa langsung tampil
    if (dataPesanan[idAktif]) {
        dataPesanan[idAktif].berat  = berat;
        dataPesanan[idAktif].status = 'sedang_dicuci';
    }

    setStatusUI('sedang_dicuci');
    hitungBiaya();
    renderListPesanan('semua');
    _kembalikanFilterSemua();
}


// ── UPDATE STATUS ──────────────────────────────────────────
/**
 * Kirim perubahan status ke server lalu refresh seluruh UI.
 * Dipakai oleh tombol-tombol di _renderTombolAksi().
 *
 * BACKEND: lihat _updateStatusPesanan() di main.js.
 */
async function updateStatus(status) {
    if (!idAktif) return;

    await _updateStatusPesanan(idAktif, status, null, null);

    // Muat ulang data terbaru dari server
    await muatDataPesanan();

    setStatusUI(status);
    hitungBiaya();
    renderListPesanan('semua');
    _kembalikanFilterSemua();
}


// ── HELPER INTERNAL ────────────────────────────────────────
function _kembalikanFilterSemua() {
    document.querySelectorAll('.tombol-filter').forEach(b => b.classList.remove('aktif'));
    const btnSemua = document.querySelector('.tombol-filter');
    if (btnSemua) btnSemua.classList.add('aktif');
}


// ── AUTO-REFRESH ───────────────────────────────────────────
/**
 * Polling setiap 60 detik untuk menyinkronkan data terbaru dari server.
 *
 * BACKEND: aktifkan baris setInterval() di bawah setelah endpoint siap.
 * Saat ini dibiarkan kosong agar tidak ada request sia-sia ke server
 * yang belum ada endpoint-nya.
 *
 * Cara mengaktifkan:
 *   startAutoRefresh(); // panggil di DOMContentLoaded pada pesanan.php
 */
function startAutoRefresh() {
    // BACKEND TODO: aktifkan ini setelah endpoint /api/pesanan siap
    // setInterval(async () => {
    //     await muatDataPesanan();
    //     renderListPesanan('semua');
    //     // Jika ada pesanan yang sedang dibuka, refresh detail-nya juga
    //     if (idAktif && dataPesanan[idAktif]) {
    //         setStatusUI(dataPesanan[idAktif].status);
    //         hitungBiaya();
    //     }
    // }, 60000);
}
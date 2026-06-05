/**
 * ============================================================
 * status_refresh.js — CleanCo Laundry
 * Digunakan di: admin/pesanan.php
 * ============================================================
 */

'use strict';

// ── SET STATUS UI ──────────────────────────────────────────
function setStatusUI(status) {
    const teksEl = document.getElementById('statusAktifTeks');
    if (teksEl) teksEl.textContent = _labelStatus[status] || status.replace('_', ' ');

    const grupAksiEl = document.getElementById('grupAksiAdmin');
    if (grupAksiEl) {
        grupAksiEl.innerHTML = _renderTombolAksi(status);
    }

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
function _renderTombolAksi(status) {
    if (status === 'menunggu_konfirmasi') {
        return `
            <button class="tombol-status tombol-status-terima" onclick="updateStatus('konfirmasi')">
                ✓ Terima Pesanan
            </button>
            <button class="tombol-status tombol-status-tolak" onclick="batalkanPesananAdmin(idAktif)">
                ✕ Tolak Pesanan
            </button>`;
    }

    if (status === 'dikonfirmasi') {
        return `
            <button class="tombol-status tombol-status-aktif" data-status="dikonfirmasi" disabled>
                Dikonfirmasi
            </button>
            <button class="tombol-status" data-status="sedang_dicuci" onclick="prosesTimbang()">
                ⚖ Proses & Timbang
            </button>`;
    }

    if (status === 'sedang_dicuci') {
        const p             = dataPesanan[idAktif];
        const opsi          = p ? p.opsi_pengantaran : 'kurir'; // Diubah dari p.opsi ke p.opsi_pengantaran
        const berikutStatus = opsi === 'kurir' ? 'sedang_diantar' : 'siap_diambil';
        const berikutLabel  = opsi === 'kurir' ? 'Sedang Diantar'  : 'Siap Diambil';
        return `
            <button class="tombol-status tombol-status-aktif" data-status="sedang_dicuci" disabled>
                Sedang Dicuci
            </button>
            <button class="tombol-status" data-status="${berikutStatus}" onclick="updateStatus('${berikutStatus}')">
                → ${berikutLabel}
            </button>`;
    }

    if (status === 'siap_diambil' || status === 'sedang_diantar') {
        return `
            <button class="tombol-status tombol-status-aktif" data-status="${status}" disabled>
                ${_labelStatus[status] || status}
            </button>
            <button class="tombol-status" data-status="selesai" onclick="updateStatus('selesai')">
                ✓ Selesai & Lunas
            </button>`;
    }

    if (status === 'selesai') {
        return `
            <button class="tombol-status tombol-status-aktif" data-status="selesai" disabled>
                Selesai & Lunas
            </button>`;
    }

    if (status === 'dibatalkan') {
        return `
            <button class="tombol-status" style="background:#FFD1D1; color:#D32F2F;" disabled>
                Dibatalkan
            </button>`;
    }

    return '';
}

// ── PROSES & TIMBANG ───────────────────────────────────────
async function prosesTimbang() {
    const beratEl = document.getElementById('inputBerat');
    const berat   = parseFloat(beratEl ? beratEl.value : 0);

    if (!berat || berat <= 0) {
        alert('Masukkan berat aktual terlebih dahulu sebelum memproses.');
        if (beratEl) beratEl.focus();
        return;
    }

    if (!idAktif) return;

    // Kirim data kecocokan ke fungsi jembatan main.js
    await _updateStatusPesanan(idAktif, 'proses_timbang', berat);

    if (dataPesanan[idAktif]) {
        dataPesanan[idAktif].berat_aktual = berat;
        dataPesanan[idAktif].status_pesanan = 'sedang_dicuci';
    }

    setStatusUI('sedang_dicuci');
    hitungBiaya();
    renderListPesanan('semua');
    _kembalikanFilterSemua();
}

// ── UPDATE STATUS ──────────────────────────────────────────
async function updateStatus(status) {
    if (!idAktif) return;

    // Menangani router endpoint dari tombol terima ('dikonfirmasi') atau alur logistik lanjutan
    const actionType = status === 'dikonfirmasi' ? 'konfirmasi' : 'update_status';
    await _updateStatusPesanan(idAktif, actionType, null, status);

    await muatDataPesanan();

    // Kondisi UI lokal setelah refresh data
    const statusTerbaru = dataPesanan[idAktif] ? dataPesanan[idAktif].status_pesanan : status;
    setStatusUI(statusTerbaru);
    hitungBiaya();
    renderListPesanan('semua');
    _kembalikanFilterSemua();
}

function _kembalikanFilterSemua() {
    document.querySelectorAll('.tombol-filter').forEach(b => b.classList.remove('aktif'));
    const btnSemua = document.querySelector('.tombol-filter');
    if (btnSemua) btnSemua.classList.add('aktif');
}
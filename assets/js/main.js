/**
 * ============================================================
 * main.js — CleanCo Laundry
 * Core JS untuk admin/pesanan.php dan member/riwayat.php
 * Murni Native JavaScript (Tanpa Library/Framework)
 * ============================================================
 */

'use strict';

// ── GLOBAL STATE ─────────────────────────────────────────────
let dataPesanan = {};
let idAktif     = null; // ID pesanan yang sedang dibuka di panel detail

// ── LABEL & KELAS BADGE ───────────────────────────────────────
const _labelStatus = {
    menunggu_konfirmasi : 'Menunggu Konfirmasi',
    dikonfirmasi        : 'Dikonfirmasi',
    sedang_dicuci       : 'Sedang Dicuci',
    siap_diambil        : 'Siap Diambil',
    sedang_diantar      : 'Sedang Diantar',
    selesai             : 'Selesai & Lunas',
    dibatalkan          : 'Dibatalkan'
};

const _kelasStatus = {
    menunggu_konfirmasi : 'badge-status badge-status-baru',
    dikonfirmasi        : 'badge-status badge-status-dikonfirmasi',
    sedang_dicuci       : 'badge-status badge-status-diproses',
    siap_diambil        : 'badge-status badge-status-selesai',
    sedang_diantar      : 'badge-status badge-status-diproses',
    selesai             : 'badge-status badge-status-selesai',
    dibatalkan          : 'badge-status badge-status-batal'
};

// ── HELPER FORMAT CURRENCY ────────────────────────────────────
const _fmt = n => 'Rp ' + (n || 0).toLocaleString('id-ID');


// ============================================================
// DATA LOADING
// ============================================================

/**
 * Muat semua pesanan dari server via parameter action query.
 */
async function muatDataPesanan() {
    try {
        // PERBAIKAN: Mengubah /api/pesanan menjadi pesanan.php?action=ambil_semua
        const res  = await fetch('pesanan.php?action=ambil_semua');
        const json = await res.json();
        if (json.success) {
            dataPesanan = json.data;
        } else {
            console.error('muatDataPesanan: server error —', json.message);
        }
    } catch (err) {
        console.error('muatDataPesanan: fetch gagal —', err);
    }
}


// ============================================================
// UPDATE STATUS PESANAN
// ============================================================

/**
 * Kirim perubahan status ke server backend.
 */
async function _updateStatusPesanan(id, status, alasan, dibatalkanOleh, berat = null) {
    try {
        // PERBAIKAN: Mengubah jalur routing ke parameter file lokal pesanan.php
        const res = await fetch(`pesanan.php?action=update_status&id=${id}`, {
            method : 'POST',
            headers: { 'Content-Type': 'application/json' },
            body   : JSON.stringify({
                status,
                alasan,
                dibatalkan_oleh : dibatalkanOleh,
                berat
            })
        });
        const json = await res.json();
        if (!json.success) {
            console.error('_updateStatusPesanan: server error —', json.message);
            alert(json.message || 'Gagal memperbarui status pesanan.');
        }
    } catch (err) {
        console.error('_updateStatusPesanan: fetch gagal —', err);
    }
}


// ============================================================
// PANEL DETAIL PESANAN (admin/pesanan.php)
// ============================================================

/**
 * Buka detail pesanan di panel kanan.
 */
function bukaPesanan(id, el) {
    idAktif = id;
    const p = dataPesanan[id];
    if (!p) return;

    document.querySelectorAll('.item-pesanan').forEach(i => i.classList.remove('aktif-dipilih'));
    el.classList.add('aktif-dipilih');

    document.getElementById('detailKosong').style.display = 'none';
    document.getElementById('detailIsi').style.display    = 'block';

    document.getElementById('detailNama').textContent        = p.nama;
    document.getElementById('detailUsername').textContent    = p.username;
    document.getElementById('detailWaktu').textContent       = p.waktu;
    document.getElementById('detailNamaLengkap').textContent = p.namaLengkap;
    document.getElementById('detailAlamat').textContent      = p.alamat || '—';
    document.getElementById('detailKecamatan').textContent   = p.kecamatan || '—';
    document.getElementById('detailTelpon').textContent      = p.telpon;
    document.getElementById('detailLayanan').textContent     = p.layanan;
    document.getElementById('detailPengiriman').textContent  = p.pengiriman;
    document.getElementById('detailNote').textContent        = p.note || '— Tidak ada catatan —';
    document.getElementById('inputBerat').value              = p.berat || '';

    const tagsEl = document.getElementById('detailTags');
    tagsEl.innerHTML = (p.tags || []).map(t =>
        `<span class="badge-${t.tipe}">${t.label}</span>`
    ).join('');

    setStatusUI(p.status);
    hitungBiaya();
}

/**
 * Menyesuaikan tampilan tombol aksi berdasarkan status pesanan saat ini.
 */
function setStatusUI(status) {
    const areaTombolAksi = document.getElementById('areaTombolAksi');
    if (!areaTombolAksi) return;

    // Mengosongkan tombol aksi lama
    areaTombolAksi.innerHTML = '';

    // Logika Alur Kerja tombol admin dinamis sesuai state pesanan
    if (status === 'menunggu_konfirmasi') {
        areaTombolAksi.innerHTML = `
            <button onclick="ubahStatusLokal('dikonfirmasi')" class="tombol-terima" style="background:#10b981; color:white; border:none; padding:10px 20px; border-radius:8px; font-weight:bold; cursor:pointer;">✓ Konfirmasi Pesanan</button>
            <button onclick="batalkanPesananAdmin(idAktif)" class="tombol-tolak" style="background:#ef4444; color:white; border:none; padding:10px 20px; border-radius:8px; font-weight:bold; cursor:pointer; margin-left:10px;">✕ Batalkan</button>
        `;
    } else if (status === 'dikonfirmasi') {
        areaTombolAksi.innerHTML = `
            <button onclick="bukaModalTimbang()" class="tombol-proses" style="background:#3b82f6; color:white; border:none; padding:10px 20px; border-radius:8px; font-weight:bold; cursor:pointer;">⚖️ Input Berat & Cuci</button>
        `;
    } else if (status === 'sedang_dicuci') {
        areaTombolAksi.innerHTML = `
            <button onclick="ubahStatusLokal('siap_diambil')" class="tombol-proses" style="background:#10b981; color:white; border:none; padding:10px 20px; border-radius:8px; font-weight:bold; cursor:pointer;">🧺 Selesai Cuci (Siap)</button>
        `;
    } else if (status === 'siap_diambil') {
        const p = dataPesanan[idAktif];
        const labelTombol = (p && p.opsi === 'kurir') ? '🚀 Serahkan ke Kurir' : '🤝 Diambil Pelanggan (Selesai)';
        const statusTarget = (p && p.opsi === 'kurir') ? 'sedang_diantar' : 'selesai';
        
        areaTombolAksi.innerHTML = `
            <button onclick="ubahStatusLokal('${statusTarget}')" class="tombol-proses" style="background:#0d3f8a; color:white; border:none; padding:10px 20px; border-radius:8px; font-weight:bold; cursor:pointer;">${labelTombol}</button>
        `;
    } else if (status === 'sedang_diantar') {
        areaTombolAksi.innerHTML = `
            <button onclick="ubahStatusLokal('selesai')" class="tombol-proses" style="background:#10b981; color:white; border:none; padding:10px 20px; border-radius:8px; font-weight:bold; cursor:pointer;">🏁 Konfirmasi Diterima (Selesai)</button>
        `;
    } else {
        // Status Selesai atau Dibatalkan tidak menampilkan tombol aksi lagi
        areaTombolAksi.innerHTML = `<span style="color:#aaa; font-style:italic;">Tidak ada aksi lanjutan untuk pesanan ini.</span>`;
    }
}

/**
 * Menghitung dan merender rincian nota tagihan laundry secara real-time di sisi klien
 */
function hitungBiaya() {
    const p = dataPesanan[idAktif];
    if (!p) return;

    const berat = parseFloat(document.getElementById('inputBerat').value) || 0;
    const subtotalLayanan = berat * p.tarifLayanan;
    const totalSemua = subtotalLayanan + p.tarifKirim;

    if(document.getElementById('textSubtotalLayanan')) {
        document.getElementById('textSubtotalLayanan').textContent = _fmt(subtotalLayanan) + ` (${berat} kg x ${_fmt(p.tarifLayanan)})`;
        document.getElementById('textBiayaKurir').textContent = _fmt(p.tarifKirim);
        document.getElementById('textTotalHarga').textContent = _fmt(totalSemua);
    }
}

/**
 * Jembatan internal untuk memperbarui status pesanan dari tombol aksi
 */
async function ubahStatusLokal(statusBaru) {
    if (!idAktif) return;
    if (!confirm(`Ubah status pesanan ke: "${_labelStatus[statusBaru]}"?`)) return;

    await _updateStatusPesanan(idAktif, statusBaru, null, null);
    await muatDataPesanan();
    renderListPesanan('semua');
    setStatusUI(statusBaru);
}

/**
 * Batal panel detail ke posisi semula
 */
function kembaliKeList() {
    document.getElementById('detailKosong').style.display = 'flex';
    document.getElementById('detailIsi').style.display    = 'none';
    document.querySelectorAll('.item-pesanan').forEach(i => i.classList.remove('aktif-dipilih'));
    idAktif = null;
}


// ============================================================
// FILTER LIST PESANAN (admin/pesanan.php)
// ============================================================
function filterPesanan(status, btn) {
    document.querySelectorAll('.tombol-filter').forEach(b => b.classList.remove('aktif'));
    btn.classList.add('aktif');
    renderListPesanan(status);
}


// ============================================================
// FILTER RIWAYAT (member/riwayat.php)
// ============================================================
function filterRiwayat(filter, btn) {
    document.querySelectorAll('#grupFilterRiwayat .tombol-filter').forEach(b => b.classList.remove('aktif'));
    btn.classList.add('aktif');

    document.querySelectorAll('.kartu-riwayat').forEach(item => {
        const cocok = filter === 'semua' || item.dataset.filter === filter;
        item.style.display = cocok ? 'flex' : 'none';
    });

    const adaYangTampil = [...document.querySelectorAll('.kartu-riwayat')].some(item => item.style.display !== 'none');
    const kosongEl = document.getElementById('riwayatKosong');
    if (kosongEl) kosongEl.style.display = adaYangTampil ? 'none' : 'flex';
}


// ============================================================
// BATALKAN PESANAN — ADMIN (admin/pesanan.php)
// ============================================================
let _idAkanDibatalAdmin = null;

function batalkanPesananAdmin(id) {
    _idAkanDibatalAdmin = id;
    const p = dataPesanan[id];
    if (!p) return;

    document.getElementById('popupBatalAdminTeks').textContent = `Pesanan #${p.kode} (${p.layanan}) milik ${p.nama} akan dibatalkan.`;
    document.querySelectorAll('input[name="alasanBatal"]').forEach(r => r.checked = false);
    
    const inputLainnya = document.getElementById('inputAlasanLainnya');
    if (inputLainnya) inputLainnya.value = '';

    document.getElementById('overlayBatalAdmin').style.display = 'block';
    document.getElementById('popupBatalAdmin').style.display   = 'block';
}

function tutupPopupBatalAdmin() {
    _idAkanDibatalAdmin = null;
    document.getElementById('overlayBatalAdmin').style.display = 'none';
    document.getElementById('popupBatalAdmin').style.display   = 'none';
}

async function eksekusiBatalAdmin() {
    if (!_idAkanDibatalAdmin) return;
    const id = _idAkanDibatalAdmin;

    const radioTerpilih = document.querySelector('input[name="alasanBatal"]:checked');
    if (!radioTerpilih) {
        alert('Pilih alasan pembatalan terlebih dahulu.');
        return;
    }

    let alasanTeks = radioTerpilih.value;
    if (alasanTeks === 'lainnya') {
        const inputLainnya = document.getElementById('inputAlasanLainnya');
        alasanTeks = inputLainnya?.value.trim() || 'Dibatalkan oleh admin.';
    }

    await _updateStatusPesanan(id, 'dibatalkan', alasanTeks, 'admin');
    await muatDataPesanan();
    renderListPesanan('semua');

    if (idAktif == id) setStatusUI('dibatalkan');
    tutupPopupBatalAdmin();
}


// ============================================================
// RENDER LIST PESANAN (admin/pesanan.php — sidebar kiri)
// ============================================================
function renderListPesanan(filterStatus) {
    const listEl = document.getElementById('listPesanan');
    if (!listEl) return;

    listEl.innerHTML = '';

    const entri = Object.entries(dataPesanan)
        .filter(([, p]) => filterStatus === 'semua' || p.status === filterStatus)
        .sort(([idA], [idB]) => Number(idB) - Number(idA));

    if (entri.length === 0) {
        listEl.innerHTML = `<div style="padding:32px 16px; text-align:center; color:#aaa; font-size:0.9rem;">Tidak ada pesanan ditemukan.</div>`;
        return;
    }

    entri.forEach(([id, p]) => {
        const badgeKelas = _kelasStatus[p.status] || 'badge-status';
        const tagsHTML   = (p.tags || []).map(t => `<span class="badge-${t.tipe}">${t.label}</span>`).join('');

        listEl.insertAdjacentHTML('beforeend', `
            <div class="item-pesanan" data-id="${id}" data-status="${p.status}" onclick="bukaPesanan(${id}, this)">
                <div class="item-pesanan-atas">
                    <span class="${badgeKelas}">${_labelStatus[p.status] || p.status}</span>
                    <span class="item-pesanan-waktu">${p.waktu}</span>
                </div>
                <p class="item-pesanan-kode">#${p.kode}</p>
                <p class="item-pesanan-nama">${p.nama}</p>
                <div class="item-pesanan-tags">${tagsHTML}</div>
            </div>
        `);
    });
}


// ============================================================
// INISIALISASI HALAMAN
// ============================================================
document.addEventListener('DOMContentLoaded', async () => {
    if (document.getElementById('listPesanan')) {
        await muatDataPesanan();
        renderListPesanan('semua');
    }
});
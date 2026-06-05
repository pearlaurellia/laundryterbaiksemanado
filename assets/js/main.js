/**
 * ============================================================
 * main.js — CleanCo Laundry
 * Core JS untuk admin/pesanan.php dan member/riwayat.php
 *
 * BACKEND INTEGRATION NOTES:
 * Semua fungsi yang berinteraksi dengan data kini menggunakan
 * fetch() ke endpoint PHP. Tidak ada lagi localStorage atau
 * data dummy di file ini.
 *
 * ENDPOINT YANG DIBUTUHKAN (lihat komentar di tiap fungsi):
 *   GET  /api/pesanan                          → ambil semua pesanan (admin)
 *   POST /api/pesanan/:id/status               → update status pesanan
 *   GET  /api/member/riwayat                   → ambil riwayat pesanan member
 * ============================================================
 */

'use strict';

// ── GLOBAL STATE ─────────────────────────────────────────────
// dataPesanan: cache pesanan yang sedang ditampilkan di halaman admin.
// Diisi oleh muatDataPesanan() saat halaman load dan setelah setiap update.
let dataPesanan = {};
let idAktif     = null; // ID pesanan yang sedang dibuka di panel detail

// ── LABEL & KELAS BADGE ───────────────────────────────────────
// Dipakai bersama oleh renderListPesanan, bukaPesanan, dan riwayat.
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

// ── HELPER FORMAT ─────────────────────────────────────────────
const _fmt = n => 'Rp ' + (n || 0).toLocaleString('id-ID');


// ============================================================
// DATA LOADING
// ============================================================

/**
 * Muat semua pesanan dari server dan simpan ke dataPesanan.
 *
 * BACKEND:
 *   GET /api/pesanan
 *   Response JSON:
 *   {
 *     "success": true,
 *     "data": {
 *       "<id>": {
 *         "id": <int>,
 *         "kode": "LDR-20261204-3847",
 *         "nama": "Ryan Liam",
 *         "username": "@liam999",
 *         "namaLengkap": "Ryan Liam Santoso",
 *         "telpon": "0834545827",
 *         "alamat": "Jl. Paal 4 No.12",
 *         "kecamatan": "Wanea",
 *         "layanan": "Express",
 *         "pengiriman": "Antar",
 *         "tarifLayanan": 15000,
 *         "tarifKirim": 10000,
 *         "berat": null,           // float|null — null sebelum ditimbang
 *         "note": "...",           // string|null
 *         "opsi": "kurir",         // "kurir"|"ambil_sendiri"
 *         "status": "menunggu_konfirmasi",
 *         "waktu": "10:00 Rabu, 04-12-2026",
 *         "tags": [
 *           { "label": "Express", "tipe": "biru" },
 *           { "label": "Antar",   "tipe": "biru" }
 *         ],
 *         "alasanBatal": null,
 *         "dibatalkanOleh": null
 *       },
 *       ...
 *     }
 *   }
 *
 *   PHP contoh (admin/api/pesanan.php):
 *   $rows = $pdo->query("
 *       SELECT p.*, u.nama, u.username, u.no_hp AS telpon,
 *              l.nama_layanan AS layanan, l.tarif_per_kg AS tarifLayanan
 *       FROM pesanan p
 *       JOIN users u ON p.id_member = u.id
 *       JOIN layanan l ON p.id_layanan = l.id
 *       ORDER BY p.id DESC
 *   ")->fetchAll();
 *   // Petakan ke struktur objek berlabel ID lalu json_encode
 */
async function muatDataPesanan() {
    try {
        const res  = await fetch('/api/pesanan');
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
 * Kirim perubahan status ke server.
 *
 * BACKEND:
 *   POST /api/pesanan/:id/status
 *   Body JSON:
 *   {
 *     "status": "sedang_dicuci",
 *     "alasan": null,              // string|null — diisi saat dibatalkan
 *     "dibatalkan_oleh": null,     // "admin"|"member"|null
 *     "berat": 4.2                 // float|null — dikirim saat prosesTimbang()
 *   }
 *   Response JSON:
 *   { "success": true }
 *
 *   PHP contoh (admin/api/update-status.php):
 *   $id     = intval($_GET['id']);
 *   $body   = json_decode(file_get_contents('php://input'), true);
 *   $status = $body['status'];
 *   $pdo->prepare("
 *       UPDATE pesanan
 *       SET status_pesanan = ?, alasan_pembatalan = ?,
 *           dibatalkan_oleh = ?, berat_aktual = COALESCE(?, berat_aktual),
 *           total_harga = CASE
 *               WHEN ? IS NOT NULL THEN (? * tarif_per_kg_snapshot) + biaya_kurir
 *               ELSE total_harga END,
 *           updated_at = NOW()
 *       WHERE id = ?
 *   ")->execute([$status, $body['alasan'], $body['dibatalkan_oleh'],
 *                $body['berat'], $body['berat'], $body['berat'], $id]);
 *
 *   // Catat ke riwayat_status:
 *   $pdo->prepare("
 *       INSERT INTO riwayat_status (id_pesanan, status_lama, status_baru,
 *                                   dilakukan_oleh, keterangan)
 *       VALUES (?, ?, ?, 'admin', ?)
 *   ")->execute([$id, $statusLama, $status, $body['alasan']]);
 */
async function _updateStatusPesanan(id, status, alasan, dibatalkanOleh, berat = null) {
    try {
        const res = await fetch(`/api/pesanan/${id}/status`, {
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
 * Dipanggil dari onclick="bukaPesanan(id, this)" di tiap item list.
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
 * Kembali ke list (tutup panel detail).
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

/**
 * Filter list sidebar berdasarkan status.
 * Dipanggil dari onclick="filterPesanan('semua', this)" dst.
 */
function filterPesanan(status, btn) {
    document.querySelectorAll('.tombol-filter').forEach(b => b.classList.remove('aktif'));
    btn.classList.add('aktif');
    renderListPesanan(status);
}


// ============================================================
// FILTER RIWAYAT (member/riwayat.php)
// ============================================================

/**
 * Filter kartu riwayat di halaman member.
 * Dipanggil dari onclick="filterRiwayat('selesai', this)" dst.
 * Riwayat dirender oleh PHP — JS hanya toggle visibilitasnya.
 */
function filterRiwayat(filter, btn) {
    document.querySelectorAll('#grupFilterRiwayat .tombol-filter')
            .forEach(b => b.classList.remove('aktif'));
    btn.classList.add('aktif');

    document.querySelectorAll('.kartu-riwayat').forEach(item => {
        const cocok = filter === 'semua' || item.dataset.filter === filter;
        item.style.display = cocok ? 'flex' : 'none';
    });

    const adaYangTampil = [...document.querySelectorAll('.kartu-riwayat')]
        .some(item => item.style.display !== 'none');
    const kosongEl = document.getElementById('riwayatKosong');
    if (kosongEl) kosongEl.style.display = adaYangTampil ? 'none' : 'flex';
}


// ============================================================
// BATALKAN PESANAN — ADMIN (admin/pesanan.php)
// ============================================================

let _idAkanDibatalAdmin = null;

/**
 * Buka popup konfirmasi pembatalan.
 * id = ID pesanan di dataPesanan (key numerik dari server).
 */
function batalkanPesananAdmin(id) {
    _idAkanDibatalAdmin = id;
    const p = dataPesanan[id];
    if (!p) return;

    document.getElementById('popupBatalAdminTeks').textContent =
        `Pesanan #${p.kode} (${p.layanan}) milik ${p.nama} akan dibatalkan.`;

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

/**
 * Eksekusi pembatalan: kirim ke server lalu refresh list.
 *
 * BACKEND: lihat _updateStatusPesanan() di atas.
 * Pastikan backend juga:
 *   1. Menyimpan alasan_pembatalan di tabel pesanan.
 *   2. Mencatat ke riwayat_status dengan keterangan = alasan.
 *   3. Jika alasan = 'pesanan_fiktif', ubah status_akun member menjadi 'nonaktif'.
 */
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

    // Refresh data dan UI
    await muatDataPesanan();
    renderListPesanan('semua');

    // Jika pesanan yang dibatalkan sedang dibuka di panel detail, update UI-nya
    if (idAktif == id) setStatusUI('dibatalkan');

    tutupPopupBatalAdmin();
}


// ============================================================
// RENDER LIST PESANAN (admin/pesanan.php — sidebar kiri)
// ============================================================

/**
 * Render ulang list pesanan di sidebar.
 * Dipanggil setelah muatDataPesanan() dan setelah tiap update status.
 *
 * CATATAN BACKEND:
 * dataPesanan sudah berisi data terbaru dari server.
 * Tidak perlu fetch lagi di sini — cukup render dari cache.
 */
function renderListPesanan(filterStatus) {
    const listEl = document.getElementById('listPesanan');
    if (!listEl) return;

    listEl.innerHTML = '';

    // Urutkan: pesanan terbaru (id terbesar) di atas
    const entri = Object.entries(dataPesanan)
        .filter(([, p]) => filterStatus === 'semua' || p.status === filterStatus)
        .sort(([idA], [idB]) => Number(idB) - Number(idA));

    if (entri.length === 0) {
        listEl.innerHTML = `
            <div style="padding:32px 16px; text-align:center; color:#aaa; font-size:0.9rem;">
                Tidak ada pesanan ditemukan.
            </div>`;
        return;
    }

    entri.forEach(([id, p]) => {
        const badgeKelas = _kelasStatus[p.status] || 'badge-status';
        const tagsHTML   = (p.tags || []).map(t =>
            `<span class="badge-${t.tipe}">${t.label}</span>`
        ).join('');

        listEl.insertAdjacentHTML('beforeend', `
            <div class="item-pesanan"
                 data-id="${id}"
                 data-status="${p.status}"
                 onclick="bukaPesanan(${id}, this)">
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
    // Hanya jalan di halaman yang punya #listPesanan (admin/pesanan.php)
    if (document.getElementById('listPesanan')) {
        await muatDataPesanan();
        renderListPesanan('semua');
    }
});
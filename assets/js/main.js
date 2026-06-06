/**
 * ============================================================
 * main.js — CleanCo Laundry
 * Core JS untuk admin/pesanan.php
 * Murni Native JavaScript (Tanpa Library/Framework)
 * ============================================================
 */

'use strict';

let dataPesanan = {};
let idAktif = null; 

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

const _fmt = n => 'Rp ' + (n || 0).toLocaleString('id-ID');

// ============================================================
// DATA LOADING & SYNC MANAGEMENT
// ============================================================
async function muatDataPesanan() {
    try {
        const res  = await fetch('pesanan.php?action=ambil_semua');
        const json = await res.json();
        if (json.success) {
            dataPesanan = json.data;
        } else {
            console.error('muatDataPesanan Error:', json.message);
        }
    } catch (err) {
        console.error('Fetch data gagal:', err);
    }
}

async function _updateStatusPesanan(id, status, alasan, dibatalkanOleh, berat = null) {
    try {
        const res = await fetch(`pesanan.php?action=update_status&id=${id}`, {
            method : 'POST',
            headers: { 'Content-Type': 'application/json' },
            body   : JSON.stringify({
                status: status,
                alasan: alasan,
                dibatalkan_oleh : dibatalkanOleh,
                berat: berat
            })
        });
        const json = await res.json();
        if (!json.success) {
            alert(json.message || 'Gagal mengubah status pesanan.');
        }
    } catch (err) {
        console.error('Status save crashed:', err);
    }
}

// ============================================================
// BUKA DETAIL PESANAN PANEL KANAN
// ============================================================
async function bukaPesanan(id, el) {
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
    
    const elSatuan = document.getElementById('satuanBerat');
    if (elSatuan) elSatuan.textContent = p.satuan || 'kg';

    // Sinkronisasi isian angka timbangan berat
    const inputBerat = document.getElementById('inputBerat');
    if (inputBerat) inputBerat.value = p.berat || '';

    // Saklar buka-tutup box timbangan berat
    const blokBerat = document.getElementById('blokInputBerat');
    if (blokBerat) {
        blokBerat.style.display = (p.status === 'dikonfirmasi' || p.berat > 0) ? 'block' : 'none';
    }

    // Suntik komponen tombol aksi
    document.getElementById('statusAktifTeks').textContent = _labelStatus[p.status] || p.status;
    renderTombolAksiAdmin(p.status);
    hitungBiaya();
    
    // Muat riwayat timeline aktivitas asinkronus
    await muatTimelineKlien(id);
}

function renderTombolAksiAdmin(status) {
    const grupAksi = document.getElementById('grupAksiAdmin');
    const btnBatal = document.getElementById('tombolBatalkanAdmin');
    const txtBatal = document.getElementById('infoSudahDibatalkan');

    if (!grupAksi) return;
    grupAksi.innerHTML = '';

    // Manajemen visibilitas tombol pembatalan massal
    if (btnBatal && txtBatal) {
        btnBatal.style.display = (status !== 'dibatalkan' && status !== 'selesai') ? 'inline-block' : 'none';
        txtBatal.style.display = (status === 'dibatalkan') ? 'block' : 'none';
    }

    if (status === 'menunggu_konfirmasi') {
        grupAksi.innerHTML = `<button onclick="ubahStatusLokal('dikonfirmasi')" class="tombol-submit-form" style="background:#10b981; border:none; color:white; padding: 10px 20px; font-weight:bold; cursor:pointer; border-radius:8px;">✓ Terima & Konfirmasi Pesanan</button>`;
    } else if (status === 'dikonfirmasi') {
        grupAksi.innerHTML = `<button onclick="eksekusiTimbangSisiKlien()" class="tombol-submit-form" style="background:#3b82f6; border:none; color:white; padding: 10px 20px; font-weight:bold; cursor:pointer; border-radius:8px;">⚖️ Simpan Berat & Mulai Cuci</button>`;
    } else if (status === 'sedang_dicuci') {
        grupAksi.innerHTML = `<button onclick="ubahStatusLokal('siap_diambil')" class="tombol-submit-form" style="background:#f59e0b; border:none; color:white; padding: 10px 20px; font-weight:bold; cursor:pointer; border-radius:8px;">🧺 Selesai Cuci & Siap Diambil</button>`;
    } else if (status === 'siap_diambil') {
        const p = dataPesanan[idAktif];
        const statusTarget = (p && p.opsi === 'kurir') ? 'sedang_diantar' : 'selesai';
        const labelTombol = (p && p.opsi === 'kurir') ? '🚀 Serahkan Ke Kurir' : '🤝 Diambil Pelanggan (Selesai)';
        grupAksi.innerHTML = `<button onclick="ubahStatusLokal('${statusTarget}')" class="tombol-submit-form" style="background:#0d3f8a; border:none; color:white; padding: 10px 20px; font-weight:bold; cursor:pointer; border-radius:8px;">${labelTombol}</button>`;
    } else if (status === 'sedang_diantar') {
        grupAksi.innerHTML = `<button onclick="ubahStatusLokal('selesai')" class="tombol-submit-form" style="background:#10b981; border:none; color:white; padding: 10px 20px; font-weight:bold; cursor:pointer; border-radius:8px;">🏁 Konfirmasi Diterima (Selesai & Lunas)</button>`;
    } else {
        grupAksi.innerHTML = `<span style="color:#aaa; font-style:italic;">Pesanan selesai diproses.</span>`;
    }
}

// ============================================================
// HITUNG BIAYA RINCIAN NOTA REAL-TIME
// ============================================================
function hitungBiaya() {
    const p = dataPesanan[idAktif];
    if (!p) return;

    const inputBerat = document.getElementById('inputBerat');
    const berat = parseFloat(inputBerat ? inputBerat.value : 0) || 0;
    
    const subtotalLayanan = berat * p.tarifLayanan;
    const totalSemua = subtotalLayanan + p.tarifKirim;

    // FIX SELEKTOR: Menggunakan ID rincian bawaan element HTML kamu yang sah
    const rincianLayanan = document.getElementById('rincianLayanan');
    const rincianKirim = document.getElementById('rincianKirim');
    const rincianTotal = document.getElementById('rincianTotal');

    if (rincianLayanan) rincianLayanan.textContent = `Layanan : ${_fmt(subtotalLayanan)} (${berat} ${p.satuan || 'kg'} x ${_fmt(p.tarifLayanan)})`;
    if (rincianKirim) rincianKirim.textContent = `Pengiriman : ${_fmt(p.tarifKirim)}`;
    if (rincianTotal) rincianTotal.textContent = `Total : ${_fmt(totalSemua)}`;
}

async function eksekusiTimbangSisiKlien() {
    const beratValue = parseFloat(document.getElementById('inputBerat').value) || 0;
    if (beratValue <= 0) {
        alert('Wajib memasukkan angka berat timbangan aktual cucian terlebih dahulu.');
        return;
    }
    if (!confirm(`Simpan berat ${beratValue} kg dan masukkan pesanan ke proses pencucian?`)) return;

    await _updateStatusPesanan(idAktif, 'sedang_dicuci', null, null, beratValue);
    await segarkanUlangDataDashboard();
}

async function ubahStatusLokal(statusBaru) {
    if (!idAktif) return;
    if (!confirm(`Ubah status pesanan ke: "${_labelStatus[statusBaru]}"?`)) return;

    await _updateStatusPesanan(idAktif, statusBaru, null, null);
    await segarkanUlangDataDashboard();
}

async function segarkanUlangDataDashboard() {
    const backupId = idAktif;
    await muatDataPesanan();
    renderListPesanan('semua');
    
    if (backupId && dataPesanan[backupId]) {
        const cardTarget = document.querySelector(`.item-pesanan[data-id="${backupId}"]`);
        if (cardTarget) bukaPesanan(backupId, cardTarget);
    } else {
        kembaliKeList();
    }
}

function kembaliKeList() {
    document.getElementById('detailKosong').style.display = 'flex';
    document.getElementById('detailIsi').style.display    = 'none';
    document.querySelectorAll('.item-pesanan').forEach(i => i.classList.remove('aktif-dipilih'));
    idAktif = null;
}

// ============================================================
// TIMELINE LOADER MANAGEMENT
// ============================================================
async function muatTimelineKlien(id) {
    const box = document.getElementById('timelineKonten');
    if (!box) return;
    box.innerHTML = 'Memuat riwayat...';

    try {
        const res = await fetch(`pesanan.php?action=get_timeline&id=${id}`);
        const json = await res.json();
        if (json.success && json.data.length > 0) {
            box.innerHTML = json.data.map(r => `
                <div style="margin-bottom:8px; border-bottom:1px solid rgba(255,255,255,0.1); padding-bottom:4px;">
                    <span style="color:var(--tealmuda); font-weight:bold;">[${_labelStatus[r.status_baru] || r.status_baru}]</span> 
                    <small style="opacity:0.7; float:right;">${r.changed_at}</small>
                    <p style="margin:2px 0 0; font-size:0.8rem; opacity:0.9;">Oleh: ${r.dilakukan_oleh} — ${r.keterangan || ''}</p>
                </div>
            `).join('');
        } else {
            box.innerHTML = '<span style="font-style:italic; opacity:0.6;">Belum ada lini masa riwayat.</span>';
        }
    } catch (err) {
        box.innerHTML = 'Gagal memuat timeline.';
    }
}

// ============================================================
// FILTER LIST SIDEBAR PESANAN
// ============================================================
function filterPesanan(status, btn) {
    document.querySelectorAll('.tombol-filter').forEach(b => b.classList.remove('aktif'));
    btn.classList.add('aktif');
    renderListPesanan(status);
}

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
        listEl.insertAdjacentHTML('beforeend', `
            <div class="item-pesanan ${idAktif == id ? 'aktif-dipilih' : ''}" data-id="${id}" onclick="bukaPesanan(${id}, this)">
                <div class="item-pesanan-atas">
                    <span class="${badgeKelas}">${_labelStatus[p.status] || p.status}</span>
                    <span class="item-pesanan-waktu">${p.waktu}</span>
                </div>
                <p class="item-pesanan-kode">#${p.kode}</p>
                <p class="item-pesanan-nama">${p.nama}</p>
                <small style="color:rgba(255,255,255,0.6); font-size:0.8rem;">📦 ${p.layanan}</small>
            </div>
        `);
    });
}

// ============================================================
// BATALKAN PESANAN — ADMIN 
// ============================================================
function batalkanPesananAdmin(id) {
    const p = dataPesanan[id];
    if (!p) return;

    // Mengikat radio button 'lainnya' dengan input field kustom
    const radios = document.querySelectorAll('input[name="alasanBatal"]');
    const wrapperLainnya = document.getElementById('wrapperAlasanLainnya');
    
    radios.forEach(r => {
        r.addEventListener('change', () => {
            if (wrapperLainnya) wrapperLainnya.style.display = (r.value === 'lainnya') ? 'block' : 'none';
        });
    });

    document.getElementById('overlayBatalAdmin').style.display = 'block';
    document.getElementById('popupBatalAdmin').style.display   = 'block';
}

function tutupPopupBatalAdmin() {
    document.getElementById('overlayBatalAdmin').style.display = 'none';
    document.getElementById('popupBatalAdmin').style.display   = 'none';
}

async function eksekusiBatalAdmin() {
    const radioTerpilih = document.querySelector('input[name="alasanBatal"]:checked');
    if (!radioTerpilih) {
        alert('Pilih salah satu alasan pembatalan.');
        return;
    }

    let alasanTeks = radioTerpilih.value;
    if (alasanTeks === 'lainnya') {
        alasanTeks = document.getElementById('inputAlasanLainnya').value.trim() || 'Dibatalkan oleh admin.';
    }

    await _updateStatusPesanan(idAktif, 'dibatalkan', alasanTeks, 'admin');
    tutupPopupBatalAdmin();
    await segarkanUlangDataDashboard();
}

// INITIALIZATION LISTENER
document.addEventListener('DOMContentLoaded', async () => {
    if (typeof inisialisasiInteraksiGlobal === 'function') inisialisasiInteraksiGlobal();

    if (document.getElementById('listPesanan')) {
        await muatDataPesanan();
        renderListPesanan('semua');
    }
});
/**
 * ============================================================
 * layanan-admin.js — CleanCo Laundry
 * Digunakan di: admin/layanan.php
 * ============================================================
 */

'use strict';

let modeEdit    = false;
let idEditAktif = null;

// URL backend — semua action dikirim ke satu file yang sama
const URL_BACKEND = 'layanan.php';


// ── EDIT ──────────────────────────────────────────────────────
function editLayanan(kartuEl) {
    modeEdit    = true;
    idEditAktif = kartuEl.dataset.id;

    document.getElementById('judulFormLayanan').textContent    = 'Edit Layanan';
    document.getElementById('inputNamaLayanan').value          = kartuEl.dataset.nama;
    document.getElementById('inputTarifLayanan').value         = kartuEl.dataset.tarif;
    document.getElementById('inputSatuanLayanan').value        = kartuEl.dataset.satuan;
    document.getElementById('inputDeskripsiLayanan').value     = kartuEl.dataset.deskripsi;
    document.getElementById('inputDurasiLayanan').value        = kartuEl.dataset.durasi;
    document.getElementById('tombolBatal').style.display       = 'inline-block';

    document.querySelector('.layanan-sidebar')?.scrollIntoView({ behavior: 'smooth' });
}


// ── SIMPAN (TAMBAH / EDIT) ────────────────────────────────────
async function simpanLayanan() {
    const nama      = document.getElementById('inputNamaLayanan').value.trim();
    const tarif     = document.getElementById('inputTarifLayanan').value.trim();
    const satuan    = document.getElementById('inputSatuanLayanan').value;
    const deskripsi = document.getElementById('inputDeskripsiLayanan').value.trim();
    const durasi    = document.getElementById('inputDurasiLayanan').value.trim();

    // Validasi client-side
    if (!nama) {
        alert('Nama layanan wajib diisi.');
        document.getElementById('inputNamaLayanan').focus();
        return;
    }
    if (!tarif || isNaN(parseInt(tarif)) || parseInt(tarif) <= 0) {
        alert('Tarif harus berupa angka lebih dari 0.');
        document.getElementById('inputTarifLayanan').focus();
        return;
    }

    // ✅ Kirim sebagai FormData agar PHP bisa baca via $_POST
    const form = new FormData();
    form.append('action',    modeEdit ? 'edit' : 'tambah');
    form.append('nama',      nama);
    form.append('tarif',     parseInt(tarif));
    form.append('satuan',    satuan);
    form.append('deskripsi', deskripsi);
    form.append('durasi',    durasi);   // ✅ nama field sesuai kolom DB

    if (modeEdit && idEditAktif) {
        form.append('id', idEditAktif);
    }

    try {
        const res  = await fetch(URL_BACKEND, { method: 'POST', body: form });
        const json = await res.json();

        // ✅ Cek key 'sukses' sesuai response PHP
        if (!json.sukses) {
            alert(json.pesan || 'Gagal menyimpan layanan. Coba lagi.');
            return;
        }

        const tarifFmt = 'Rp ' + parseInt(tarif).toLocaleString('id-ID') + ' / ' + satuan;

        if (modeEdit && idEditAktif) {
            // ── Update kartu yang sudah ada di DOM ──
            const kartuEl = document.querySelector(
                `#containerLayanan .kartu-layanan-admin[data-id="${idEditAktif}"]`
            );
            if (kartuEl) {
                // Update data-* attribute
                kartuEl.dataset.nama      = nama;
                kartuEl.dataset.tarif     = tarif;
                kartuEl.dataset.satuan    = satuan;
                kartuEl.dataset.deskripsi = deskripsi;
                kartuEl.dataset.durasi    = durasi;

                // Update teks yang tampil di kartu
                kartuEl.querySelector('.kartu-layanan-admin-nama').textContent      = nama;
                kartuEl.querySelector('.kartu-layanan-admin-tarif').textContent     = tarifFmt;
                kartuEl.querySelector('.kartu-layanan-admin-deskripsi').textContent = deskripsi || '—';

                // Update badge durasi jika ada
                const badgeDurasi = kartuEl.querySelector('.kartu-layanan-admin-detail .badge-biru');
                if (badgeDurasi) badgeDurasi.textContent = durasi || '—';
            }

        } else {
            // ── Buat kartu baru dan tambahkan ke DOM ──
            const newId   = json.id;
            const kartuBaru = document.createElement('div');
            kartuBaru.className         = 'kartu-layanan-admin';
            kartuBaru.dataset.id        = newId;
            kartuBaru.dataset.nama      = nama;
            kartuBaru.dataset.tarif     = tarif;
            kartuBaru.dataset.satuan    = satuan;
            kartuBaru.dataset.deskripsi = deskripsi;
            kartuBaru.dataset.durasi    = durasi;

            kartuBaru.innerHTML = `
                <div class="kartu-layanan-admin-header">
                    <span class="kartu-layanan-admin-nama">${escHtml(nama)}</span>
                    <span class="kartu-layanan-admin-tarif">${tarifFmt}</span>
                </div>
                <div class="kartu-layanan-admin-body">
                    <p class="kartu-layanan-admin-deskripsi">${escHtml(deskripsi) || '—'}</p>
                    <div class="kartu-layanan-admin-detail">
                        <span class="badge-biru">${escHtml(durasi) || '—'}</span>
                    </div>
                </div>
                <div class="kartu-layanan-admin-aksi">
                    <button class="tombol-edit-layanan"
                            onclick="editLayanan(this.closest('.kartu-layanan-admin'))">
                        Edit
                    </button>
                    <button class="tombol-hapus-layanan"
                            onclick="hapusLayanan(this.closest('.kartu-layanan-admin'))">
                        Hapus
                    </button>
                </div>
            `;

            // Sisipkan sebelum elemen #layananKosong agar urutan rapi
            const container = document.getElementById('containerLayanan');
            const kosong    = document.getElementById('layananKosong');
            container.insertBefore(kartuBaru, kosong);
        }

    } catch (err) {
        console.error('simpanLayanan: fetch gagal —', err);
        alert('Koneksi bermasalah. Coba lagi.');
        return;
    }

    cekKosong();
    resetForm();
}


// ── HAPUS ─────────────────────────────────────────────────────
async function hapusLayanan(kartuEl) {
    const id   = kartuEl.dataset.id;
    const nama = kartuEl.dataset.nama;

    if (!confirm(`Hapus layanan "${nama}"?\nLayanan yang masih digunakan pesanan aktif tidak bisa dihapus.`)) return;

    // ✅ Kirim sebagai FormData
    const form = new FormData();
    form.append('action', 'hapus');
    form.append('id', id);

    try {
        // ✅ URL mengarah ke layanan.php bukan /api/
        const res  = await fetch(URL_BACKEND, { method: 'POST', body: form });
        const json = await res.json();

        // ✅ Cek key 'sukses' sesuai response PHP
        if (!json.sukses) {
            alert(json.pesan || 'Gagal menghapus layanan.');
            return;
        }

    } catch (err) {
        console.error('hapusLayanan: fetch gagal —', err);
        alert('Koneksi bermasalah. Coba lagi.');
        return;
    }

    // Animasi keluar lalu hapus dari DOM
    kartuEl.style.transition = 'opacity 0.3s, transform 0.3s';
    kartuEl.style.opacity    = '0';
    kartuEl.style.transform  = 'scale(0.95)';
    setTimeout(() => {
        kartuEl.remove();
        cekKosong();
    }, 300);
}


// ── RESET FORM ────────────────────────────────────────────────
function resetForm() {
    modeEdit    = false;
    idEditAktif = null;

    document.getElementById('judulFormLayanan').textContent    = 'Tambah Layanan';
    document.getElementById('inputNamaLayanan').value          = '';
    document.getElementById('inputTarifLayanan').value         = '';
    document.getElementById('inputSatuanLayanan').value        = 'kg';
    document.getElementById('inputDeskripsiLayanan').value     = '';
    document.getElementById('inputDurasiLayanan').value        = '';
    document.getElementById('tombolBatal').style.display       = 'none';
}


// ── CEK KOSONG ────────────────────────────────────────────────
function cekKosong() {
    // ✅ Hitung hanya kartu di dalam #containerLayanan
    const jumlah = document.querySelectorAll(
        '#containerLayanan .kartu-layanan-admin'
    ).length;
    document.getElementById('layananKosong').style.display = jumlah > 0 ? 'none' : 'block';
}


// ── HELPER: escape HTML untuk innerHTML yang aman ─────────────
// Mencegah XSS jika nama layanan mengandung karakter < > & " '
function escHtml(str) {
    if (!str) return '';
    return str
        .replace(/&/g, '&amp;')
        .replace(/</g, '&lt;')
        .replace(/>/g, '&gt;')
        .replace(/"/g, '&quot;')
        .replace(/'/g, '&#39;');
}
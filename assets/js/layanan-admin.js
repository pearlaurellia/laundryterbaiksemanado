/**
 * ============================================================
 * layanan-admin.js — CleanCo Laundry
 * Digunakan di: admin/layanan.php
 * Murni Native JavaScript (Tanpa Library/Framework)
 *
 * Berisi CRUD layanan via AJAX Fetch API Parameter Query String.
 * Mengelola manipulasi asinkronus struktur data layanan laundry.
 * ============================================================
 */

'use strict';

// State Global pengendali alur saklar Edit data
let modeEdit = false;
let idEditAktif = null;

// Jalankan inisialisasi sterilisasi tampilan awal layout sesaat setelah DOM siap
document.addEventListener('DOMContentLoaded', () => {
    cekKosong();
});

// ── 1. FUNGSI EDIT LAYANAN ──────────────────────────────────
/**
 * Membaca data-attributes kartu HTML lalu menyuntikkannya ke form sidebar.
 * @param {HTMLElement} kartuEl - Node element kartu .kartu-layanan-admin yang di-klik
 */
function editLayanan(kartuEl) {
    modeEdit = true;
    idEditAktif = kartuEl.dataset.id;

    const inputNama = document.getElementById('inputNamaLayanan');
    const inputTarif = document.getElementById('inputTarifLayanan');
    const inputSatuan = document.getElementById('inputSatuanLayanan');
    const inputDeskripsi = document.getElementById('inputDeskripsiLayanan');
    const inputDurasi = document.getElementById('inputDurasiLayanan');
    const judulForm = document.getElementById('judulFormLayanan');
    const btnBatal = document.getElementById('tombolBatal');

    if (!inputNama || !inputTarif || !inputSatuan || !inputDeskripsi || !inputDurasi) return;

    // Suntik nilai data menuju element form kontrol
    judulForm.textContent = 'Edit Paket Layanan';
    inputNama.value = kartuEl.dataset.nama || '';
    inputTarif.value = kartuEl.dataset.tarif || '';
    inputSatuan.value = kartuEl.dataset.satuan || 'kg';
    inputDeskripsi.value = kartuEl.dataset.deskripsi === '—' ? '' : (kartuEl.dataset.deskripsi || '');
    
    // Proteksi: Ambil angka murninya saja tanpa menyertakan string imbuhan "Hari"
    const durasiMentah = kartuEl.dataset.durasi || '';
    inputDurasi.value = durasiMentah.replace(/\D/g, ''); 
    
    if (btnBatal) btnBatal.style.display = 'inline-block';

    // Gulung halaman secara halus ke arah form jika sedang membuka lewat HP
    document.querySelector('.layanan-sidebar')?.scrollIntoView({ behavior: 'smooth' });
}

// ── 2. FUNGSI SIMPAN (TAMBAH / EDIT VIA FETCH API) ──────────
/**
 * Validasi form internal, menyusun kiriman JSON, lalu menembak asinkronus local endpoint.
 */
async function simpanLayanan() {
    const nama = document.getElementById('inputNamaLayanan')?.value.trim();
    const tarif = document.getElementById('inputTarifLayanan')?.value.trim();
    const satuan = document.getElementById('inputSatuanLayanan')?.value;
    const deskripsi = document.getElementById('inputDeskripsiLayanan')?.value.trim();
    const durasiInput = document.getElementById('inputDurasiLayanan')?.value.trim();

    // Validasi Sisi Klien
    if (!nama || !tarif || !durasiInput) {
        alert('Gagal memproses. Kolom nama, tarif paket, dan durasi operasional wajib diisi.');
        return;
    }
    
    const nominalTarif = parseInt(tarif);
    if (isNaN(nominalTarif) || nominalTarif <= 0) {
        alert('Nominal tarif pencucian diwajibkan angka bulat positif.');
        return;
    }

    const durasiHari = parseInt(durasiInput);
    const teksDurasiFinal = durasiHari + ' Hari';

    // Kompilasi Payload Objek JSON
    const payload = {
        nama_layanan: nama,
        tarif_per_kg: nominalTarif,
        satuan: satuan,
        deskripsi: deskripsi || '',
        estimasi_hari: teksDurasiFinal
    };

    try {
        let url = 'layanan.php?action=tambah';
        if (modeEdit && idEditAktif) {
            url = `layanan.php?action=edit&id=${idEditAktif}`;
        }

        const response = await fetch(url, {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify(payload)
        });

        const json = await response.json();

        if (json.success) {
            const dataKartu = {
                nama: nama,
                tarif: nominalTarif,
                satuan: satuan,
                deskripsi: deskripsi || '—',
                durasi: teksDurasiFinal
            };

            if (modeEdit && idEditAktif) {
                // Eksekusi pembaruan visual kartu lama
                updateKartu(idEditAktif, dataKartu);
                alert('Data paket layanan laundry berhasil diperbarui!');
            } else {
                // Eksekusi pencetakan kartu baru hasil auto-increment DB id
                renderKartu(json.id, dataKartu);
                alert('Paket layanan baru berhasil didaftarkan ke database toko!');
            }
            
            cekKosong();
            resetForm();
        } else {
            alert(json.message || 'Gagal merekam perubahan data menuju SQL Server.');
        }

    } catch (err) {
        console.error('simpanLayanan crashed:', err);
        alert('Terjadi kesalahan jaringan sistem local Apache server.');
    }
}

// ── 3. FUNGSI HAPUS (SOFT DELETE INTERFACE) ─────────────────
/**
 * Konfirmasi pembatalan status, hit Fetch POST hapus, dan animasikan penghapusan node DOM.
 * @param {HTMLElement} kartuEl - Node element kartu .kartu-layanan-admin
 */
async function hapusLayanan(kartuEl) {
    const id = kartuEl.dataset.id;
    const nama = kartuEl.dataset.nama;

    if (!confirm(`Apakah Anda yakin ingin menghapus paket layanan "${nama}"?\n\nSistem hanya akan mengubah status relasi menjadi non-aktif agar nota invoice pesanan lama pelanggan tidak pecah/rusak.`)) {
        return;
    }

    try {
        const response = await fetch(`layanan.php?action=hapus&id=${id}`, { 
            method: 'POST' 
        });
        const json = await response.json();

        if (json.success) {
            // Jalankan fungsi pelepasan objek node dari DOM dengan transisi halus
            hapusKartuDariDOM(id);
        } else {
            alert(json.message || 'Layanan ini dilarang dihapus karena sedang terikat transaksi aktif.');
        }
    } catch (err) {
        console.error('hapusLayanan crashed:', err);
        alert('Koneksi bermasalah. Periksa kestabilan MySQL XAMPP Control Panel.');
    }
}

// ── 4. DOM RENDERING ENGINE OPERATIONS (NATIVE INJECTION) ───

/**
 * Memasukkan / Append elemen kartu baru murni ke dalam wadah kontainer halaman.
 */
function renderKartu(id, data) {
    const container = document.getElementById('containerLayanan');
    if (!container) return;

    const kartuBaru = document.createElement('div');
    kartuBaru.className = 'kartu-layanan-admin';
    
    // Injeksi data attributes penampung state memori browser
    kartuBaru.dataset.id = id;
    kartuBaru.dataset.nama = data.nama;
    kartuBaru.dataset.tarif = data.tarif;
    kartuBaru.dataset.satuan = data.satuan;
    kartuBaru.dataset.deskripsi = data.deskripsi;
    kartuBaru.dataset.durasi = data.durasi;

    const formatUang = 'Rp ' + data.tarif.toLocaleString('id-ID') + ' / ' + data.satuan;
    const komponenBadge = (data.satuan === 'kg') 
        ? `<span class="badge-hijau">Cuci</span> <span class="badge-hijau">Kering</span> <span class="badge-hijau">Setrika</span>`
        : `<span class="badge-hijau">Dry Clean</span>`;

    kartuBaru.innerHTML = `
        <div class="kartu-layanan-admin-header">
            <span class="kartu-layanan-admin-nama">${data.nama}</span>
            <span class="kartu-layanan-admin-tarif">${formatUang}</span>
        </div>
        <div class="kartu-layanan-admin-body">
            <p class="kartu-layanan-admin-deskripsi">${data.deskripsi}</p>
            <div class="kartu-layanan-admin-detail">
                ${komponenBadge}
                <span class="badge-biru" style="background:#e0f2fe; color:#0369a1; padding: 2px 8px; border-radius: 4px; font-size: 0.8rem; font-weight: bold;">
                    ⏱ ${data.durasi}
                </span>
            </div>
        </div>
        <div class="kartu-layanan-admin-aksi" style="margin-top: 15px; display: flex; gap: 10px;">
            <button type="button" class="tombol-edit-layanan" style="cursor:pointer;" onclick="editLayanan(this.closest('.kartu-layanan-admin'))">Edit</button>
            <button type="button" class="tombol-hapus-layanan" style="cursor:pointer;" onclick="hapusLayanan(this.closest('.kartu-layanan-admin'))">Hapus</button>
        </div>
    `;
    
    container.appendChild(kartuBaru);
}

/**
 * Mencari id kartu target lalu menimpa isinya dengan data kiriman mutakhir server.
 */
function updateKartu(id, data) {
    const kartuEl = document.querySelector(`.kartu-layanan-admin[data-id="${id}"]`);
    if (!kartuEl) return;

    // Mutasi memori data-attributes element lokal browser
    kartuEl.dataset.nama = data.nama;
    kartuEl.dataset.tarif = data.tarif;
    kartuEl.dataset.satuan = data.satuan;
    kartuEl.dataset.deskripsi = data.deskripsi;
    kartuEl.dataset.durasi = data.durasi;

    const formatUang = 'Rp ' + data.tarif.toLocaleString('id-ID') + ' / ' + data.satuan;

    // Mutasi visual representasi interface
    const namaNode = kartuEl.querySelector('.kartu-layanan-admin-nama');
    const tarifNode = kartuEl.querySelector('.kartu-layanan-admin-tarif');
    const deskripsiNode = kartuEl.querySelector('.kartu-layanan-admin-deskripsi');
    
    if (namaNode) namaNode.textContent = data.nama;
    if (tarifNode) tarifNode.textContent = formatUang;
    if (deskripsiNode) deskripsiNode.textContent = data.deskripsi;
    
    // Perbarui visual teks estimasi durasi pada badge span paling bontot
    const detailSpans = kartuEl.querySelectorAll('.kartu-layanan-admin-detail span');
    if (detailSpans.length > 0) {
        detailSpans[detailSpans.length - 1].textContent = '⏱ ' + data.durasi;
    }
}

/**
 * Meluncurkan animasi fade out mengecil sebelum melepas elemen HTML murni dari DOM Tree.
 */
function hapusKartuDariDOM(id) {
    const kartuEl = document.querySelector(`.kartu-layanan-admin[data-id="${id}"]`);
    if (!kartuEl) return;

    // Trigger visual feedback transisi CSS Native inline
    kartuEl.style.transition = 'opacity 0.35s ease, transform 0.35s ease';
    kartuEl.style.opacity = '0';
    kartuEl.style.transform = 'scale(0.85)';
    
    // Beri jeda sejenak menunggu transisi CSS menutup sebelum nodenya dicopot
    setTimeout(() => {
        kartuEl.remove();
        cekKosong();
    }, 350);
}

// ── 5. CLEANER HELPERS UTILITIES ────────────────────────────

function resetForm() {
    modeEdit = false;
    idEditAktif = null;
    
    const judulForm = document.getElementById('judulFormLayanan');
    const btnBatal = document.getElementById('tombolBatal');
    const formElement = document.getElementById('formLayanan');

    if (judulForm) judulForm.textContent = 'Tambah Layanan Baru';
    if (btnBatal) btnBatal.style.display = 'none';
    
    // Kosongkan seluruh isian form kontrol bawaan browser
    if (formElement) {
        formElement.reset();
    } else {
        // Fallback jika pembungkus bukan tag <form>
        document.getElementById('inputNamaLayanan').value = '';
        document.getElementById('inputTarifLayanan').value = '';
        document.getElementById('inputSatuanLayanan').value = 'kg';
        document.getElementById('inputDeskripsiLayanan').value = '';
        document.getElementById('inputDurasiLayanan').value = '';
    }
}

function cekKosong() {
    const jumlahKartu = document.querySelectorAll('.kartu-layanan-admin').length;
    const infoKosongEl = document.getElementById('layananKosong');
    if (!infoKosongEl) return;
    
    infoKosongEl.style.display = (jumlahKartu > 0) ? 'none' : 'block';
}
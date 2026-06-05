/**
 * ============================================================
 * layanan-admin.js — CleanCo Laundry
 * Digunakan di: admin/layanan.php
 *
 * Berisi CRUD layanan via AJAX Fetch API:
 * - editLayanan()   → Mengisi form sidebar dengan data kartu yang dipilih
 * - simpanLayanan() → Mengirim data POST (Tambah/Edit) dalam bentuk JSON
 * - hapusLayanan()  → Mengirim data POST Soft-Delete ke server
 * - resetForm()     → Mengosongkan form & mengembalikan status ke "Tambah"
 * - cekKosong()     → Menampilkan/menyembunyikan teks "Belum ada layanan"
 * ============================================================
 */

'use strict';

// State Global untuk mengontrol alur Edit data
let modeEdit = false;
let idEditAktif = null;

// ── 1. FUNGSI EDIT LAYANAN ──────────────────────────────────
/**
 * Mengambil data dari atribut dataset kartu HTML lalu memasukkannya ke form.
 * @param {HTMLElement} kartuEl - Elemen kartu .kartu-layanan-admin yang diklik
 */
function editLayanan(kartuEl) {
    modeEdit = true;
    idEditAktif = kartuEl.dataset.id;

    // Mengisi nilai form berdasarkan data-attributes kartu
    document.getElementById('judulFormLayanan').textContent = 'Edit Layanan';
    document.getElementById('inputNamaLayanan').value = kartuEl.dataset.nama;
    document.getElementById('inputTarifLayanan').value = kartuEl.dataset.tarif;
    document.getElementById('inputSatuanLayanan').value = kartuEl.dataset.satuan;
    document.getElementById('inputDeskripsiLayanan').value = kartuEl.dataset.deskripsi;
    document.getElementById('inputDurasiLayanan').value = kartuEl.dataset.durasi;
    
    // Memunculkan tombol batal edit
    document.getElementById('tombolBatal').style.display = 'inline-block';

    // Scroll otomatis ke area form jika layar HP agar admin tidak bingung
    document.querySelector('.layanan-sidebar')?.scrollIntoView({ behavior: 'smooth' });
}

// ── 2. FUNGSI SIMPAN (TAMBAH / EDIT) ────────────────────────
/**
 * Melakukan validasi input di sisi klien, menyusun payload JSON,
 * lalu menembak endpoint PHP secara asinkronus (AJAX).
 */
async function simpanLayanan() {
    const nama = document.getElementById('inputNamaLayanan').value.trim();
    const tarif = document.getElementById('inputTarifLayanan').value.trim();
    const satuan = document.getElementById('inputSatuanLayanan').value;
    const deskripsi = document.getElementById('inputDeskripsiLayanan').value.trim();
    const durasi = document.getElementById('inputDurasiLayanan').value.trim();

    // Validasi Lapis Pertama (Client-side validation)
    if (!nama || !tarif || !durasi) {
        alert('Nama layanan, tarif, dan estimasi durasi wajib diisi.');
        return;
    }
    if (isNaN(parseInt(tarif)) || parseInt(tarif) <= 0) {
        alert('Tarif harus berupa angka positif yang valid.');
        return;
    }

    // Menyusun payload sesuai spesifikasi JSON backend
    const payload = {
        nama_layanan: nama,
        tarif_per_kg: parseInt(tarif),
        satuan: satuan,
        deskripsi: deskripsi,
        estimasi_hari: durasi
    };

    // Format Rupiah untuk manipulasi DOM (Contoh: Rp 8.000 / kg)
    const tarifFmt = 'Rp ' + parseInt(tarif).toLocaleString('id-ID') + ' / ' + satuan;

    try {
        let url = 'layanan.php?action=tambah';
        
        // Jika statusnya sedang mengedit, ubah target URL parameter
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
            if (modeEdit && idEditAktif) {
                // ── UPDATE DOM: MODE EDIT ──
                const kartuEl = document.querySelector(`.kartu-layanan-admin[data-id="${idEditAktif}"]`);
                if (kartuEl) {
                    // Update data attributes agar jika diedit lagi datanya sudah baru
                    kartuEl.dataset.nama = nama;
                    kartuEl.dataset.tarif = tarif;
                    kartuEl.dataset.satuan = satuan;
                    kartuEl.dataset.deskripsi = deskripsi;
                    kartuEl.dataset.durasi = durasi;

                    // Update visual teks kartu
                    kartuEl.querySelector('.kartu-layanan-admin-nama').childNodes[0].textContent = nama + ' ';
                    kartuEl.querySelector('.kartu-layanan-admin-tarif').textContent = tarifFmt;
                    kartuEl.querySelector('.kartu-layanan-admin-deskripsi').textContent = deskripsi || '—';
                    
                    // Update badge durasi (elemen span terakhir di detail)
                    const detailBadges = kartuEl.querySelectorAll('.kartu-layanan-admin-detail span');
                    if (detailBadges.length > 0) {
                        detailBadges[detailBadges.length - 1].textContent = durasi;
                    }
                }
                alert('Layanan berhasil diperbarui!');
            } else {
                // ── UPDATE DOM: MODE TAMBAH BARU ──
                const newId = json.id;
                const kartuBaru = document.createElement('div');
                kartuBaru.className = 'kartu-layanan-admin';
                kartuBaru.dataset.id = newId;
                kartuBaru.dataset.nama = nama;
                kartuBaru.dataset.tarif = tarif;
                kartuBaru.dataset.satuan = satuan;
                kartuBaru.dataset.deskripsi = deskripsi;
                kartuBaru.dataset.durasi = durasi;

                // Logika penentuan badge visual berdasarkan satuan
                const badgeKomponen = (satuan === 'kg') 
                    ? `<span class="badge-hijau">Cuci</span> <span class="badge-hijau">Kering</span> <span class="badge-hijau">Setrika</span>`
                    : `<span class="badge-hijau">Dry Clean</span>`;

                kartuBaru.innerHTML = `
                    <div class="kartu-layanan-admin-header">
                        <span class="kartu-layanan-admin-nama">${nama}</span>
                        <span class="kartu-layanan-admin-tarif">${tarifFmt}</span>
                    </div>
                    <div class="kartu-layanan-admin-body">
                        <p class="kartu-layanan-admin-deskripsi">${deskripsi || '—'}</p>
                        <div class="kartu-layanan-admin-detail">
                            ${badgeKomponen}
                            <span class="badge-biria" style="background:#e0f2fe; color:#0369a1; padding: 2px 8px; border-radius: 4px; font-size: 0.8rem; font-weight: bold;">
                                ${durasi}
                            </span>
                        </div>
                    </div>
                    <div class="kartu-layanan-admin-aksi">
                        <button class="tombol-edit-layanan" onclick="editLayanan(this.closest('.kartu-layanan-admin'))">Edit</button>
                        <button class="tombol-hapus-layanan" onclick="browseHapus(this)">Hapus</button>
                    </div>
                `;
                
                document.getElementById('containerLayanan').appendChild(kartuBaru);
                alert('Layanan baru berhasil ditambahkan!');
            }
            
            // Bersihkan form dan sinkronkan teks halaman kosong
            cekKosong();
            resetForm();
        } else {
            alert(json.message || 'Gagal menyimpan data ke server.');
        }

    } catch (err) {
        console.error('Proses simpanLayanan gagal:', err);
        alert('Terjadi gangguan jaringan atau error sistem server.');
    }
}

// Helper khusus untuk tombol hapus pada kartu baru yang di-append JS
function browseHapus(btnElement) {
    hapusLayanan(btnElement.closest('.kartu-layanan-admin'));
}

// ── 3. FUNGSI HAPUS (SOFT DELETE) ───────────────────────────
/**
 * Konfirmasi penghapusan, cek validitas relasi transaksi ke backend, 
 * lalu hapus kartu dari layar dengan efek animasi transisi halus.
 * @param {HTMLElement} kartuEl - Elemen kartu .kartu-layanan-admin
 */
async function hapusLayanan(kartuEl) {
    const id = kartuEl.dataset.id;
    const nama = kartuEl.dataset.nama;

    if (!confirm(`Hapus layanan "${nama}"? Sistem hanya akan menonaktifkannya agar riwayat pesanan lama member tidak rusak.`)) {
        return;
    }

    try {
        const response = await fetch(`layanan.php?action=hapus&id=${id}`, { 
            method: 'POST' 
        });
        const json = await response.json();

        if (json.success) {
            // Efek Animasi Fade Out & Shrink Scale via JavaScript Inline CSS
            kartuEl.style.transition = 'opacity 0.4s ease, transform 0.4s ease';
            kartuEl.style.opacity = '0';
            kartuEl.style.transform = 'scale(0.9)';
            
            // Tunggu animasi CSS selesai baru hapus node elemen dari DOM pohon HTML
            setTimeout(() => { 
                kartuEl.remove(); 
                cekKosong(); 
            }, 400);
        } else {
            // Menampilkan pesan gagal dari backend jika layanan masih dipakai pesanan aktif
            alert(json.message || 'Gagal menghapus layanan.');
        }
    } catch (err) {
        console.error('Proses hapusLayanan gagal:', err);
        alert('Koneksi bermasalah. Silakan periksa jaringan server Apache kamu.');
    }
}

// ── 4. FUNGSI RESET FORM ────────────────────────────────────
/**
 * Membersihkan seluruh isi input form dan mengembalikan title form ke default.
 */
function resetForm() {
    modeEdit = false;
    idEditAktif = null;
    
    document.getElementById('judulFormLayanan').textContent = 'Tambah Layanan';
    document.getElementById('inputNamaLayanan').value = '';
    document.getElementById('inputTarifLayanan').value = '';
    document.getElementById('inputSatuanLayanan').value = 'kg';
    document.getElementById('inputDeskripsiLayanan').value = '';
    document.getElementById('inputDurasiLayanan').value = '';
    
    // Sembunyikan kembali tombol batal
    document.getElementById('tombolBatal').style.display = 'none';
}

// ── 5. FUNGSI CEK KOSONG ────────────────────────────────────
/**
 * Memantau jumlah total komponen kartu layanan di layar secara berkala.
 */
function cekKosong() {
    const jumlahKartu = document.querySelectorAll('.kartu-layanan-admin').length;
    const infoKosongEl = document.getElementById('layananKosong');
    
    if (jumlahKartu > 0) {
        infoKosongEl.style.display = 'none';
    } else {
        infoKosongEl.style.display = 'block';
    }
}
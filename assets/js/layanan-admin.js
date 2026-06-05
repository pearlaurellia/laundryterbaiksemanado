/**
 * ============================================================
 * layanan-admin.js — CleanCo Laundry
 * Digunakan di: admin/layanan.php
 *
 * Berisi CRUD layanan:
 *   - editLayanan()   → isi form dengan data layanan yang dipilih
 *   - simpanLayanan() → POST tambah/edit ke server
 *   - hapusLayanan()  → DELETE ke server
 *   - resetForm()     → kosongkan form
 *   - cekKosong()     → tampilkan/sembunyikan state kosong
 *
 * CATATAN BACKEND:
 * Semua kartu .kartu-layanan-admin dirender oleh PHP dari DB.
 * JS hanya mengurus interaksi (buka form, kirim request, update DOM).
 * ============================================================
 */

'use strict';

let modeEdit    = false;
let idEditAktif = null;


// ── EDIT ──────────────────────────────────────────────────
/**
 * Isi form sidebar dengan data layanan yang diklik.
 * Kartu layanan harus punya data-* attribute yang dirender PHP:
 *
 *   <div class="kartu-layanan-admin"
 *        data-id="<?= $l['id'] ?>"
 *        data-nama="<?= htmlspecialchars($l['nama_layanan']) ?>"
 *        data-tarif="<?= $l['tarif_per_kg'] ?>"
 *        data-satuan="kg"
 *        data-deskripsi="<?= htmlspecialchars($l['deskripsi']) ?>"
 *        data-durasi="<?= htmlspecialchars($l['estimasi_hari']) ?>">
 */
function editLayanan(kartuEl) {
    modeEdit    = true;
    idEditAktif = kartuEl.dataset.id;

    document.getElementById('judulFormLayanan').textContent = 'Edit Layanan';
    document.getElementById('inputNamaLayanan').value       = kartuEl.dataset.nama;
    document.getElementById('inputTarifLayanan').value      = kartuEl.dataset.tarif;
    document.getElementById('inputSatuanLayanan').value     = kartuEl.dataset.satuan;
    document.getElementById('inputDeskripsiLayanan').value  = kartuEl.dataset.deskripsi;
    document.getElementById('inputDurasiLayanan').value     = kartuEl.dataset.durasi;
    document.getElementById('tombolBatal').style.display    = 'inline-block';

    document.querySelector('.layanan-sidebar')?.scrollIntoView({ behavior: 'smooth' });
}


// ── SIMPAN (TAMBAH / EDIT) ─────────────────────────────────
/**
 * Validasi lalu kirim POST ke server untuk tambah atau edit layanan.
 *
 * BACKEND:
 *   Tambah → POST /api/layanan/tambah
 *   Edit   → POST /api/layanan/:id/edit
 *   Body JSON:
 *   {
 *     "nama_layanan" : "Express",
 *     "tarif_per_kg" : 15000,
 *     "satuan"       : "kg",
 *     "deskripsi"    : "Selesai dalam 6-8 jam",
 *     "estimasi_hari": "1 hari"
 *   }
 *   Response JSON (tambah): { "success": true, "id": <int> }
 *   Response JSON (edit):   { "success": true }
 *
 *   PHP (admin/api/layanan.php):
 *   // Tambah:
 *   $pdo->prepare("INSERT INTO layanan (nama_layanan, tarif_per_kg, deskripsi, estimasi_hari)
 *                  VALUES (?, ?, ?, ?)")
 *       ->execute([$nama, $tarif, $deskripsi, $durasi]);
 *   $newId = $pdo->lastInsertId();
 *   echo json_encode(['success' => true, 'id' => $newId]);
 *
 *   // Edit:
 *   $pdo->prepare("UPDATE layanan SET nama_layanan=?, tarif_per_kg=?,
 *                  deskripsi=?, estimasi_hari=? WHERE id=?")
 *       ->execute([$nama, $tarif, $deskripsi, $durasi, $id]);
 *   echo json_encode(['success' => true]);
 */
async function simpanLayanan() {
    const nama      = document.getElementById('inputNamaLayanan').value.trim();
    const tarif     = document.getElementById('inputTarifLayanan').value.trim();
    const satuan    = document.getElementById('inputSatuanLayanan').value;
    const deskripsi = document.getElementById('inputDeskripsiLayanan').value.trim();
    const durasi    = document.getElementById('inputDurasiLayanan').value.trim();

    if (!nama || !tarif) {
        alert('Nama layanan dan tarif wajib diisi.');
        return;
    }
    if (isNaN(parseInt(tarif)) || parseInt(tarif) < 0) {
        alert('Tarif harus berupa angka positif.');
        return;
    }

    const payload = {
        nama_layanan  : nama,
        tarif_per_kg  : parseInt(tarif),
        satuan,
        deskripsi,
        estimasi_hari : durasi
    };

    const tarifFmt = 'Rp ' + parseInt(tarif).toLocaleString('id-ID') + ' / ' + satuan;

    try {
        let res, json;

        if (modeEdit && idEditAktif) {
            // ── Edit layanan yang ada ──
            res  = await fetch(`/api/layanan/${idEditAktif}/edit`, {
                method : 'POST',
                headers: { 'Content-Type': 'application/json' },
                body   : JSON.stringify(payload)
            });
            json = await res.json();

            if (json.success) {
                // Update kartu di DOM
                const kartuEl = document.querySelector(
                    `.kartu-layanan-admin[data-id="${idEditAktif}"]`
                );
                if (kartuEl) {
                    kartuEl.dataset.nama      = nama;
                    kartuEl.dataset.tarif     = tarif;
                    kartuEl.dataset.satuan    = satuan;
                    kartuEl.dataset.deskripsi = deskripsi;
                    kartuEl.dataset.durasi    = durasi;

                    kartuEl.querySelector('.kartu-layanan-admin-nama').textContent    = nama;
                    kartuEl.querySelector('.kartu-layanan-admin-tarif').textContent   = tarifFmt;
                    kartuEl.querySelector('.kartu-layanan-admin-deskripsi').textContent = deskripsi || '—';
                }
            }

        } else {
            // ── Tambah layanan baru ──
            res  = await fetch('/api/layanan/tambah', {
                method : 'POST',
                headers: { 'Content-Type': 'application/json' },
                body   : JSON.stringify(payload)
            });
            json = await res.json();

            if (json.success) {
                const newId  = json.id;
                const kartuBaru = document.createElement('div');
                kartuBaru.className       = 'kartu-layanan-admin';
                kartuBaru.dataset.id      = newId;
                kartuBaru.dataset.nama    = nama;
                kartuBaru.dataset.tarif   = tarif;
                kartuBaru.dataset.satuan  = satuan;
                kartuBaru.dataset.deskripsi = deskripsi;
                kartuBaru.dataset.durasi  = durasi;

                kartuBaru.innerHTML = `
                    <div class="kartu-layanan-admin-header">
                        <span class="kartu-layanan-admin-nama">${nama}</span>
                        <span class="kartu-layanan-admin-tarif">${tarifFmt}</span>
                    </div>
                    <div class="kartu-layanan-admin-body">
                        <p class="kartu-layanan-admin-deskripsi">${deskripsi || '—'}</p>
                        <div class="kartu-layanan-admin-detail">
                            <span class="badge-biru">${durasi || '—'}</span>
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
                document.getElementById('containerLayanan').appendChild(kartuBaru);
            }
        }

        if (!json.success) {
            console.error('simpanLayanan: server error —', json.message);
            alert('Gagal menyimpan layanan. Coba lagi.');
            return;
        }

    } catch (err) {
        console.error('simpanLayanan: fetch gagal —', err);
        alert('Koneksi bermasalah. Coba lagi.');
        return;
    }

    cekKosong();
    resetForm();
}


// ── HAPUS ─────────────────────────────────────────────────
/**
 * Konfirmasi lalu hapus layanan dari server dan DOM.
 *
 * BACKEND:
 *   POST /api/layanan/:id/hapus  (atau DELETE /api/layanan/:id)
 *   Response JSON: { "success": true }
 *
 *   PHP:
 *   // Pastikan tidak ada pesanan aktif yang menggunakan layanan ini
 *   // sebelum menghapus (atau gunakan soft-delete dengan kolom is_active = 0)
 *   $pdo->prepare("UPDATE layanan SET status = 'nonaktif' WHERE id = ?")
 *       ->execute([$id]);
 *   echo json_encode(['success' => true]);
 */
async function hapusLayanan(kartuEl) {
    const id   = kartuEl.dataset.id;
    const nama = kartuEl.dataset.nama;

    if (!confirm(`Hapus layanan "${nama}"? Tindakan ini tidak dapat dibatalkan.`)) return;

    try {
        const res  = await fetch(`/api/layanan/${id}/hapus`, { method: 'POST' });
        const json = await res.json();
        if (!json.success) {
            console.error('hapusLayanan: server error —', json.message);
            alert('Gagal menghapus layanan. Mungkin layanan ini masih digunakan oleh pesanan aktif.');
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
    setTimeout(() => { kartuEl.remove(); cekKosong(); }, 300);
}


// ── RESET FORM ────────────────────────────────────────────
function resetForm() {
    modeEdit    = false;
    idEditAktif = null;
    document.getElementById('judulFormLayanan').textContent = 'Tambah Layanan';
    document.getElementById('inputNamaLayanan').value       = '';
    document.getElementById('inputTarifLayanan').value      = '';
    document.getElementById('inputSatuanLayanan').value     = 'kg';
    document.getElementById('inputDeskripsiLayanan').value  = '';
    document.getElementById('inputDurasiLayanan').value     = '';
    document.getElementById('tombolBatal').style.display    = 'none';
}


// ── CEK KOSONG ────────────────────────────────────────────
function cekKosong() {
    const ada = document.querySelectorAll('.kartu-layanan-admin').length > 0;
    document.getElementById('layananKosong').style.display = ada ? 'none' : 'block';
}
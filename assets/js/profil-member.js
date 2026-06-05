/**
 * ============================================================
 * profil-member.js — CleanCo Laundry
 * Digunakan di: member/profil.php
 *
 * Berisi:
 *   - toggleEditProfil() / batalEditProfil() → mode edit nama & HP
 *   - simpanProfil()                         → POST update profil
 *   - cekKuatPassword()                      → indikator kekuatan password
 *   - cekKonfirmasi()                        → validasi konfirmasi password
 *   - gantiPassword()                        → POST ganti password
 * ============================================================
 */

'use strict';

let modeEditProfil = false;
let nilaiAsli      = {};


// ── TOGGLE EDIT PROFIL ────────────────────────────────────
function toggleEditProfil() {
    modeEditProfil = !modeEditProfil;
    const inputs   = ['inputNamaProfil', 'inputNoHP'];
    const tombolEl = document.getElementById('tombolEditProfil');
    const simpanEl = document.getElementById('tombolSimpanProfil');

    if (modeEditProfil) {
        inputs.forEach(id => {
            nilaiAsli[id] = document.getElementById(id).value;
            document.getElementById(id).removeAttribute('readonly');
            document.getElementById(id).classList.add('input-editable');
        });
        tombolEl.style.display = 'none';
        simpanEl.style.display = 'block';
    }
}

function batalEditProfil() {
    const inputs = ['inputNamaProfil', 'inputNoHP'];
    inputs.forEach(id => {
        document.getElementById(id).value = nilaiAsli[id];
        document.getElementById(id).setAttribute('readonly', true);
        document.getElementById(id).classList.remove('input-editable');
    });
    modeEditProfil = false;
    document.getElementById('tombolEditProfil').style.display  = 'inline-block';
    document.getElementById('tombolSimpanProfil').style.display = 'none';
}


// ── SIMPAN PROFIL ─────────────────────────────────────────
/**
 * BACKEND:
 *   POST /api/member/profil
 *   Body JSON: { "nama": "...", "no_hp": "08..." }
 *   Response: { "success": true }
 *
 *   PHP (member/api/simpan-profil.php):
 *   session_start();
 *   $id   = $_SESSION['user_id'];
 *   $body = json_decode(file_get_contents('php://input'), true);
 *   $pdo->prepare("UPDATE users SET nama = ?, no_hp = ? WHERE id = ?")
 *       ->execute([$body['nama'], $body['no_hp'], $id]);
 *   // Update session juga:
 *   $_SESSION['nama']  = $body['nama'];
 *   $_SESSION['no_hp'] = $body['no_hp'];
 *   echo json_encode(['success' => true]);
 */
async function simpanProfil() {
    const nama = document.getElementById('inputNamaProfil').value.trim();
    const noHP = document.getElementById('inputNoHP').value.trim();

    if (!nama) { alert('Nama tidak boleh kosong.'); return; }
    if (!/^[0-9]{10,13}$/.test(noHP)) {
        alert('Nomor WhatsApp harus berupa angka 10–13 digit.');
        return;
    }

    try {
        const res  = await fetch('/api/member/profil', {
            method : 'POST',
            headers: { 'Content-Type': 'application/json' },
            body   : JSON.stringify({ nama, no_hp: noHP })
        });
        const json = await res.json();
        if (!json.success) {
            alert('Gagal menyimpan profil. Coba lagi.');
            return;
        }
    } catch (err) {
        console.error('simpanProfil: fetch gagal —', err);
        alert('Koneksi bermasalah. Coba lagi.');
        return;
    }

    batalEditProfil();
    tampilPopupBerhasil('Profil Diperbarui', 'Nama dan nomor WhatsApp kamu berhasil disimpan.');
}


// ── INDIKATOR KEKUATAN PASSWORD ───────────────────────────
function cekKuatPassword(val) {
    const wrapperEl = document.getElementById('kuatPasswordWrapper');
    const isiEl     = document.getElementById('kuatPasswordIsi');
    const labelEl   = document.getElementById('kuatPasswordLabel');
    if (!wrapperEl) return;

    wrapperEl.style.display = val.length > 0 ? 'flex' : 'none';
    if (val.length === 0) return;

    let skor = 0;
    if (val.length >= 8)          skor++;
    if (/[A-Z]/.test(val))        skor++;
    if (/[0-9]/.test(val))        skor++;
    if (/[^A-Za-z0-9]/.test(val)) skor++;

    const level = ['', 'Lemah', 'Cukup', 'Kuat', 'Sangat Kuat'][skor];
    const warna = ['', '#f87171', '#f59e0b', '#52c49c', '#0d3f8a'][skor];
    const lebar = [0, 25, 50, 75, 100][skor];

    isiEl.style.width           = lebar + '%';
    isiEl.style.backgroundColor = warna;
    labelEl.textContent         = level;
    labelEl.style.color         = warna;
}


// ── VALIDASI KONFIRMASI PASSWORD ──────────────────────────
function cekKonfirmasi() {
    const baru    = document.getElementById('inputPasswordBaru').value;
    const konfirm = document.getElementById('inputKonfirmasiPassword').value;
    const pesanEl = document.getElementById('pesanKonfirmasi');
    if (!pesanEl) return;

    if (!konfirm) { pesanEl.textContent = ''; return; }

    if (baru === konfirm) {
        pesanEl.textContent = '✓ Password cocok';
        pesanEl.style.color = '#52c49c';
    } else {
        pesanEl.textContent = '✕ Password tidak cocok';
        pesanEl.style.color = '#f87171';
    }
}


// ── GANTI PASSWORD ────────────────────────────────────────
/**
 * BACKEND:
 *   POST /api/member/ganti-password
 *   Body JSON: { "password_lama": "...", "password_baru": "..." }
 *   Response: { "success": true }
 *             atau { "success": false, "message": "Password lama tidak sesuai." }
 *
 *   PHP (member/api/ganti-password.php):
 *   session_start();
 *   $id   = $_SESSION['user_id'];
 *   $body = json_decode(file_get_contents('php://input'), true);
 *
 *   // Ambil hash password dari DB
 *   $row  = $pdo->prepare("SELECT password FROM users WHERE id = ?");
 *   $row->execute([$id]);
 *   $user = $row->fetch();
 *
 *   if (!password_verify($body['password_lama'], $user['password'])) {
 *       echo json_encode(['success' => false, 'message' => 'Password lama tidak sesuai.']);
 *       exit;
 *   }
 *   $hash = password_hash($body['password_baru'], PASSWORD_BCRYPT);
 *   $pdo->prepare("UPDATE users SET password = ? WHERE id = ?")
 *       ->execute([$hash, $id]);
 *   echo json_encode(['success' => true]);
 */
async function gantiPassword() {
    const lama    = document.getElementById('inputPasswordLama').value;
    const baru    = document.getElementById('inputPasswordBaru').value;
    const konfirm = document.getElementById('inputKonfirmasiPassword').value;

    if (!lama)            { alert('Masukkan password saat ini.'); return; }
    if (baru.length < 8)  { alert('Password baru minimal 8 karakter.'); return; }
    if (baru !== konfirm) { alert('Konfirmasi password tidak cocok.'); return; }

    try {
        const res  = await fetch('/api/member/ganti-password', {
            method : 'POST',
            headers: { 'Content-Type': 'application/json' },
            body   : JSON.stringify({ password_lama: lama, password_baru: baru })
        });
        const json = await res.json();
        if (!json.success) {
            alert(json.message || 'Gagal mengganti password. Pastikan password lama benar.');
            return;
        }
    } catch (err) {
        console.error('gantiPassword: fetch gagal —', err);
        alert('Koneksi bermasalah. Coba lagi.');
        return;
    }

    // Reset semua field password
    document.getElementById('inputPasswordLama').value    = '';
    document.getElementById('inputPasswordBaru').value    = '';
    document.getElementById('inputKonfirmasiPassword').value = '';
    const kuatWrapper = document.getElementById('kuatPasswordWrapper');
    if (kuatWrapper) kuatWrapper.style.display = 'none';
    const pesanKonfirm = document.getElementById('pesanKonfirmasi');
    if (pesanKonfirm) pesanKonfirm.textContent = '';

    tampilPopupBerhasil(
        'Password Diperbarui',
        'Password kamu berhasil diganti. Gunakan password baru saat login berikutnya.'
    );
}


// ── POPUP BERHASIL ────────────────────────────────────────
function tampilPopupBerhasil(judul, teks) {
    document.getElementById('popupBerhasilJudul').textContent = judul;
    document.getElementById('popupBerhasilTeks').textContent  = teks;
    document.getElementById('overlayPopup').style.display     = 'block';
    document.getElementById('popupBerhasil').style.display    = 'block';
}

function tutupPopupBerhasil() {
    document.getElementById('overlayPopup').style.display  = 'none';
    document.getElementById('popupBerhasil').style.display = 'none';
}
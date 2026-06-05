/**
 * ============================================================
 * profil-member.js — CleanCo Laundry
 * Digunakan di: member/profil.php
 * Murni Native JavaScript (Tanpa Library/Framework)
 *
 * Mengelola interaksi halaman data diri pelanggan, validasi
 * real-time kekuatan kata sandi, serta pembaruan data secara
 * asynchronous (AJAX Fetch POST) bebas reload halaman.
 * ============================================================
 */

'use strict';

let modeEditProfil = false;
let nilaiAsli = {};

// ── INIT EVENT LISTENERS FOR LIVE REACTIVE VALIDATION ───────
document.addEventListener('DOMContentLoaded', () => {
    const inputPassBaru = document.getElementById('inputPasswordBaru');
    const inputKonfirm = document.getElementById('inputKonfirmasiPassword');

    // Pengikat Otomatis: Deteksi kekuatan password secara real-time saat diketik
    if (inputPassBaru) {
        inputPassBaru.addEventListener('input', function() {
            cekKuatPassword(this.value);
            if (inputKonfirm && inputKonfirm.value !== '') {
                cekKonfirmasi();
            }
        });
    }

    // Pengikat Otomatis: Deteksi kecocokan konfirmasi password saat diketik
    if (inputKonfirm) {
        inputKonfirm.addEventListener('input', () => {
            cekKonfirmasi();
        });
    }
});


// ── TOGGLE EDIT PROFIL DATA DIRI ───────────────────────────
function toggleEditProfil() {
    modeEditProfil = !modeEditProfil;
    const inputs = ['inputNamaProfil', 'inputNoHP'];
    const tombolEdit = document.getElementById('tombolEditProfil');
    const tombolSimpan = document.getElementById('tombolSimpanProfil');
    const tombolBatal = document.getElementById('tombolBatalProfil'); // Tombol penunjang jika ada di HTML

    if (modeEditProfil) {
        inputs.forEach(id => {
            const el = document.getElementById(id);
            if (el) {
                nilaiAsli[id] = el.value;
                el.removeAttribute('readonly');
                el.classList.add('input-editable');
                el.style.borderColor = '#0d3f8a'; // Beri highlight border biru tanda aktif
            }
        });
        
        if (tombolEdit) tombolEdit.style.display = 'none';
        if (tombolSimpan) tombolSimpan.style.display = 'inline-block';
        if (tombolBatal) tombolBatal.style.display = 'inline-block';
    }
}

function batalEditProfil() {
    const inputs = ['inputNamaProfil', 'inputNoHP'];
    
    inputs.forEach(id => {
        const el = document.getElementById(id);
        if (el && nilaiAsli[id] !== undefined) {
            el.value = nilaiAsli[id];
            el.setAttribute('readonly', true);
            el.classList.remove('input-editable');
            el.style.borderColor = ''; // Kembalikan warna border semula
        }
    });
    
    modeEditProfil = false;
    
    const tombolEdit = document.getElementById('tombolEditProfil');
    const tombolSimpan = document.getElementById('tombolSimpanProfil');
    const tombolBatal = document.getElementById('tombolBatalProfil');

    if (tombolEdit) tombolEdit.style.display = 'inline-block';
    if (tombolSimpan) tombolSimpan.style.display = 'none';
    if (tombolBatal) tombolBatal.style.display = 'none';
}


// ── UPDATE DATA PROFIL (AJAX FETCH POST) ────────────────────
async function simpanProfil() {
    const namaEl = document.getElementById('inputNamaProfil');
    const noHPEl = document.getElementById('inputNoHP');
    
    if (!namaEl || !noHPEl) return;

    const nama = namaEl.value.trim();
    const noHP = noHPEl.value.trim();

    // Validasi Sisi Klien
    if (!nama) { alert('Nama lengkap tidak boleh dibiarkan kosong.'); return; }
    if (!/^[0-9]{10,13}$/.test(noHP)) {
        alert('Nomor WhatsApp wajib berupa angka numerik sepanjang 10–13 digit.');
        return;
    }

    try {
        // PERBAIKAN ROUTING: Mengubah rute /api menjadi parameter query string file lokal
        const res = await fetch('profil.php?action=update_profil', {
            method : 'POST',
            headers: { 'Content-Type': 'application/json' },
            body   : JSON.stringify({ nama: nama, no_hp: noHP })
        });
        
        const json = await res.json();
        
        if (!json.success) {
            alert(json.message || 'Sistem gagal memperbarui data profil.');
            return;
        }
    } catch (err) {
        console.error('simpanProfil: fetch gagal —', err);
        alert('Koneksi Apache XAMPP bermasalah. Gagal menggapai database.');
        return;
    }

    batalEditProfil();
    tampilPopupBerhasil('Profil Diperbarui', 'Nama dan nomor WhatsApp identitas akun kamu berhasil disimpan.');
}


// ── REAL-TIME PASSWORD STRENGTH MONITOR ─────────────────────
function cekKuatPassword(val) {
    const wrapperEl = document.getElementById('kuatPasswordWrapper');
    const isiEl     = document.getElementById('kuatPasswordIsi');
    const labelEl   = document.getElementById('kuatPasswordLabel');
    if (!wrapperEl || !isiEl || !labelEl) return;

    wrapperEl.style.display = val.length > 0 ? 'flex' : 'none';
    if (val.length === 0) return;

    let skor = 0;
    if (val.length >= 8)           skor++; // Syarat panjang minimum
    if (/[A-Z]/.test(val))        skor++; // Syarat huruf besar
    if (/[0-9]/.test(val))        skor++; // Syarat angka
    if (/[^A-Za-z0-9]/.test(val)) skor++; // Syarat simbol karakter khusus

    const level = ['', 'Lemah sekali', 'Cukup Aman', 'Sangat Kuat', 'Sempurna'][skor];
    const warna = ['', '#ef4444', '#f59e0b', '#10b981', '#0d3f8a'][skor];
    const lebar = [0, 25, 50, 75, 100][skor];

    isiEl.style.width = lebar + '%';
    isiEl.style.backgroundColor = warna;
    labelEl.textContent = level;
    labelEl.style.color = warna;
}


// ── REAL-TIME PASSWORDS MATCH CHECKER ───────────────────────
function cekKonfirmasi() {
    const baru = document.getElementById('inputPasswordBaru')?.value || '';
    const konfirm = document.getElementById('inputKonfirmasiPassword')?.value || '';
    const pesanEl = document.getElementById('pesanKonfirmasi');
    if (!pesanEl) return;

    if (!konfirm) { pesanEl.textContent = ''; return; }

    if (baru === konfirm) {
        pesanEl.textContent = '✓ Rumusan kata sandi baru telah serasi';
        pesanEl.style.color = '#10b981';
    } else {
        pesanEl.textContent = '✕ Konfirmasi kata sandi tidak cocok';
        pesanEl.style.color = '#ef4444';
    }
}


// ── GANTI PASSWORD AKUN (AJAX FETCH POST) ───────────────────
async function gantiPassword() {
    const lamaEl = document.getElementById('inputPasswordLama');
    const baruEl = document.getElementById('inputPasswordBaru');
    const konfirmEl = document.getElementById('inputKonfirmasiPassword');

    if (!lamaEl || !baruEl || !konfirmEl) return;

    const lama = lamaEl.value;
    const baru = baruEl.value;
    const konfirm = konfirmEl.value;

    if (!lama) { alert('Sebutkan kata sandi lama Anda saat ini.'); return; }
    if (baru.length < 8) { alert('Kata sandi baru diwajibkan minimal sepanjang 8 karakter.'); return; }
    if (baru !== konfirm) { alert('Proses dibatalkan, konfirmasi kata sandi baru belum cocok.'); return; }

    try {
        // PERBAIKAN ROUTING: Mengubah rute /api menjadi parameter query string file lokal
        const res = await fetch('profil.php?action=update_password', {
            method : 'POST',
            headers: { 'Content-Type': 'application/json' },
            body   : JSON.stringify({ password_lama: lama, password_baru: baru })
        });
        
        const json = await res.json();
        
        if (!json.success) {
            alert(json.message || 'Gagal memperbarui kata sandi. Pastikan password lama tepat.');
            return;
        }
    } catch (err) {
        console.error('gantiPassword: fetch gagal —', err);
        alert('Gagal berkomunikasi dengan database server.');
        return;
    }

    // Sterilisasi massal kolom inputan password pasca pembaruan data sukses
    lamaEl.value = '';
    baruEl.value = '';
    konfirmEl.value = '';
    
    const kuatWrapper = document.getElementById('kuatPasswordWrapper');
    if (kuatWrapper) kuatWrapper.style.display = 'none';
    
    const pesanKonfirm = document.getElementById('pesanKonfirmasi');
    if (pesanKonfirm) pesanKonfirm.textContent = '';

    tampilPopupBerhasil(
        'Password Diperbarui',
        'Kata sandi akun CleanCo kamu berhasil diganti. Pergunakan sandi baru ini pada login berikutnya.'
    );
}


// ── SYSTEM MODAL POPUP DISPLAY MANAGEMENT ───────────────────
function tampilPopupBerhasil(judul, teks) {
    const jdlEl = document.getElementById('popupBerhasilJudul');
    const txtEl = document.getElementById('popupBerhasilTeks');
    const ovrEl = document.getElementById('overlayPopup');
    const popEl = document.getElementById('popupBerhasil');

    if (jdlEl) jdlEl.textContent = judul;
    if (txtEl) txtEl.textContent = teks;
    if (ovrEl) ovrEl.style.display = 'block';
    if (popEl) popEl.style.display = 'block';
}

function tutupPopupBerhasil() {
    const ovrEl = document.getElementById('overlayPopup');
    const popEl = document.getElementById('popupBerhasil');
    
    if (ovrEl) ovrEl.style.display = 'none';
    if (popEl) popEl.style.display = 'none';
}
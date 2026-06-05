/**
 * ============================================================
 * form-validation.js — CleanCo Laundry
 * Digunakan di: semua halaman yang punya form
 *
 * Fungsi validasi client-side murni — tidak ada data dummy.
 * Tidak perlu perubahan untuk integrasi backend.
 *
 * CATATAN BACKEND:
 * Validasi di sini adalah validasi SISI KLIEN (UX cepat).
 * Backend PHP WAJIB melakukan validasi ulang secara independen
 * — jangan hanya mengandalkan validasi JS ini.
 * ============================================================
 */

'use strict';

// ── FORM PESAN (member/pesan.php) ──────────────────────────
/**
 * BACKEND: dipanggil sebelum fetch POST di kirimPesanan() (pesan-member.js).
 * Sudah terintegrasi — tidak perlu diubah.
 */
function validasiFormPesan(opsiPengantaran) {
    const layananId = document.getElementById('inputLayananId').value;
    if (!layananId) {
        tampilError('Pilih jenis layanan terlebih dahulu.');
        return false;
    }

    if (opsiPengantaran === 'kurir') {
        const kecamatan = document.getElementById('inputKecamatan').value;
        const alamat    = document.getElementById('inputAlamat').value.trim();
        if (!kecamatan) {
            tampilError('Pilih kecamatan tujuan terlebih dahulu.');
            return false;
        }
        if (!alamat) {
            tampilError('Masukkan alamat lengkap tujuan pengantaran.');
            return false;
        }
    }
    return true;
}


// ── FORM LOGIN (login.php) ──────────────────────────────────
/**
 * BACKEND:
 *   Form login POST ke login.php (atau /api/login).
 *   PHP memvalidasi ulang email/password dan membuat session.
 *   Jika gagal, set $_SESSION['login_error'] lalu redirect kembali.
 *   Tampilkan error dari PHP via #pesanError yang di-render server-side.
 */
function validasiFormLogin() {
    const email    = document.getElementById('inputEmail')?.value.trim();
    const password = document.getElementById('inputPassword')?.value;

    if (!email) {
        tampilError('Email tidak boleh kosong.');
        return false;
    }
    if (!formatEmailValid(email)) {
        tampilError('Format email tidak valid.');
        return false;
    }
    if (!password) {
        tampilError('Password tidak boleh kosong.');
        return false;
    }
    return true;
}


// ── FORM REGISTER (register.php) ───────────────────────────
/**
 * BACKEND:
 *   Form register POST ke register.php (atau /api/register).
 *   PHP harus:
 *     1. Validasi ulang semua field
 *     2. Cek duplikasi username dan email (SELECT COUNT(*) ... )
 *     3. Hash password dengan password_hash($pass, PASSWORD_BCRYPT)
 *     4. INSERT INTO users (...) VALUES (...)
 *     5. Set session lalu redirect ke member/dashboard.php
 *        ATAU tampilkan popup sukses di halaman yang sama
 */
function validasiFormRegister() {
    const username     = document.getElementById('inputUsername')?.value.trim();
    const namaDepan    = document.getElementById('inputNamaDepan')?.value.trim();
    const noWA         = document.getElementById('inputNoWA')?.value.trim();
    const email        = document.getElementById('inputEmail')?.value.trim();
    const password     = document.getElementById('inputPassword')?.value;
    const verifikasi   = document.getElementById('inputVerifikasiPassword')?.value;

    if (!username) {
        tampilError('Username tidak boleh kosong.'); return false;
    }
    if (username.length < 4) {
        tampilError('Username minimal 4 karakter.'); return false;
    }
    if (!namaDepan) {
        tampilError('Nama depan tidak boleh kosong.'); return false;
    }
    if (!noWA || !/^[0-9]{10,13}$/.test(noWA)) {
        tampilError('Nomor WhatsApp harus berupa angka 10–13 digit.'); return false;
    }
    if (!email || !formatEmailValid(email)) {
        tampilError('Format email tidak valid.'); return false;
    }
    if (!password || password.length < 8) {
        tampilError('Password minimal 8 karakter.'); return false;
    }
    if (password !== verifikasi) {
        tampilError('Konfirmasi password tidak cocok.'); return false;
    }
    return true;
}


// ── FORM PROFIL (member/profil.php) ────────────────────────
/**
 * BACKEND: lihat simpanProfil() di profil-member.js.
 */
function validasiFormProfil() {
    const nama = document.getElementById('inputNamaProfil')?.value.trim();
    const noHP = document.getElementById('inputNoHP')?.value.trim();

    if (!nama) {
        tampilError('Nama tidak boleh kosong.'); return false;
    }
    if (!noHP || !/^[0-9]{10,13}$/.test(noHP)) {
        tampilError('Nomor WhatsApp harus berupa angka 10–13 digit.'); return false;
    }
    return true;
}


// ── HELPERS ────────────────────────────────────────────────
function formatEmailValid(email) {
    return /^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(email);
}

/**
 * Tampilkan pesan error di elemen #pesanError.
 * Elemen ini harus ada di HTML dan tersembunyi by default:
 *   <p id="pesanError" style="display:none;" class="pesan-error"></p>
 */
function tampilError(pesan) {
    const el = document.getElementById('pesanError');
    if (el) {
        el.textContent   = pesan;
        el.style.display = 'block';
        setTimeout(() => { el.style.display = 'none'; }, 4000);
    } else {
        alert(pesan);
    }
}


// ── EDIT INFO WEBSITE (admin/edit-info.php) ─────────────────

const nilaiAsliInfo = {};

// Mapping seksi → daftar id input
const inputPerSeksi = {
    kontak : ['inputWaAdmin', 'inputEmailAdmin'],
    jam    : ['inputJamBuka', 'inputJamTutup', 'inputHariOperasional', 'inputCatatanJam'],
    alamat : ['inputNamaOutlet', 'inputAlamatOutlet', 'inputKecamatanOutlet', 'inputMapsOutlet'],
    kurir  : ['inputBiayaKurir', 'inputCatatanKurir']
};

function toggleEditSeksi(seksi) {
    nilaiAsliInfo[seksi] = {};
    inputPerSeksi[seksi].forEach(id => {
        const el = document.getElementById(id);
        if (el) {
            nilaiAsliInfo[seksi][id] = el.value;
            el.removeAttribute('readonly');
            el.classList.remove('input-readonly');
            el.classList.add('input-editable');
        }
    });

    if (seksi === 'kurir') {
        document.getElementById('kecamatanPills').style.display      = 'none';
        document.getElementById('kecamatanCheckboxes').style.display = 'flex';
    }

    document.getElementById('tombolEdit' + _capitalize(seksi)).style.display = 'none';
    document.getElementById('simpan'     + _capitalize(seksi)).style.display = 'block';
}

function batalEditSeksi(seksi) {
    if (nilaiAsliInfo[seksi]) {
        inputPerSeksi[seksi].forEach(id => {
            const el = document.getElementById(id);
            if (el) {
                el.value = nilaiAsliInfo[seksi][id];
                el.setAttribute('readonly', true);
                el.classList.add('input-readonly');
                el.classList.remove('input-editable');
            }
        });
    }

    if (seksi === 'kurir') {
        document.getElementById('kecamatanPills').style.display      = 'flex';
        document.getElementById('kecamatanCheckboxes').style.display = 'none';
    }

    document.getElementById('tombolEdit' + _capitalize(seksi)).style.display = 'inline-block';
    document.getElementById('simpan'     + _capitalize(seksi)).style.display = 'none';
}

/**
 * Validasi lalu POST perubahan info website ke server.
 *
 * BACKEND:
 *   POST /api/info-website/simpan?seksi=kontak  (atau action=simpan_kontak)
 *   Body JSON: { field: value, ... }
 *   Response: { "success": true }
 *
 *   PHP (admin/api/edit-info.php):
 *   $seksi = $_GET['seksi'];
 *   $body  = json_decode(file_get_contents('php://input'), true);
 *   // UPDATE info_website SET ... WHERE id = 1
 */
async function simpanSeksi(seksi) {
    // ── Validasi client-side per seksi ──
    if (seksi === 'kontak') {
        const wa = document.getElementById('inputWaAdmin').value.trim();
        if (!/^62[0-9]{9,12}$/.test(wa)) {
            tampilError('Format nomor WA tidak valid. Gunakan format 628xxx tanpa tanda +.');
            return;
        }
        const email = document.getElementById('inputEmailAdmin').value.trim();
        if (!formatEmailValid(email)) {
            tampilError('Format email tidak valid.');
            return;
        }
    }

    if (seksi === 'jam') {
        const buka  = document.getElementById('inputJamBuka').value;
        const tutup = document.getElementById('inputJamTutup').value;
        if (!buka || !tutup) {
            tampilError('Jam buka dan tutup harus diisi.');
            return;
        }
        if (buka >= tutup) {
            tampilError('Jam buka harus lebih awal dari jam tutup.');
            return;
        }
    }

    if (seksi === 'alamat') {
        const alamat = document.getElementById('inputAlamatOutlet').value.trim();
        if (!alamat) {
            tampilError('Alamat outlet tidak boleh kosong.');
            return;
        }
    }

    if (seksi === 'kurir') {
        const biaya = document.getElementById('inputBiayaKurir').value;
        if (!biaya || parseInt(biaya) < 0) {
            tampilError('Biaya kurir tidak valid.');
            return;
        }
        const checked = document.querySelectorAll(
            '#kecamatanCheckboxes input[type="checkbox"]:checked'
        );
        if (checked.length === 0) {
            tampilError('Pilih minimal satu kecamatan yang dilayani.');
            return;
        }
        // Update tampilan pills
        const pillsEl = document.getElementById('kecamatanPills');
        pillsEl.innerHTML = [...checked]
            .map(c => `<span class="pill-kecamatan">${c.value}</span>`)
            .join('');
    }

    // ── Kumpulkan payload dari input di seksi ini ──
    const payload = {};
    inputPerSeksi[seksi].forEach(id => {
        const el = document.getElementById(id);
        if (el) payload[id] = el.value;
    });

    // Tambahkan kecamatan (array) jika seksi kurir
    if (seksi === 'kurir') {
        const checked = document.querySelectorAll(
            '#kecamatanCheckboxes input[type="checkbox"]:checked'
        );
        payload['kecamatan_dilayani'] = [...checked].map(c => c.value);
    }

    // ── POST ke server ──
    try {
        const res  = await fetch(`/api/info-website/simpan?seksi=${seksi}`, {
            method : 'POST',
            headers: { 'Content-Type': 'application/json' },
            body   : JSON.stringify(payload)
        });
        const json = await res.json();
        if (!json.success) {
            console.error('simpanSeksi: server error —', json.message);
            tampilError('Gagal menyimpan. Coba lagi.');
            return;
        }
    } catch (err) {
        console.error('simpanSeksi: fetch gagal —', err);
        tampilError('Koneksi bermasalah. Coba lagi.');
        return;
    }

    batalEditSeksi(seksi);
    tampilPopupBerhasil(
        'Berhasil Disimpan!',
        'Perubahan sudah aktif dan langsung memengaruhi halaman terkait.'
    );
}

function aktifkanNav(el) {
    document.querySelectorAll('.edit-info-nav-item')
            .forEach(a => a.classList.remove('aktif-nav'));
    el.classList.add('aktif-nav');
}

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

function _capitalize(str) {
    return str.charAt(0).toUpperCase() + str.slice(1);
}
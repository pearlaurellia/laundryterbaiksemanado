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

function validasiFormRegister() {
    const username = document.getElementById('inputUsername')?.value.trim();
    const namaDepan = document.getElementById('inputNamaDepan')?.value.trim();
    const namaBelakang = document.getElementById('inputNamaBelakang')?.value.trim();
    const noWA     = document.getElementById('inputNoWA')?.value.trim();
    const email    = document.getElementById('inputEmail')?.value.trim();
    const password = document.getElementById('inputPassword')?.value;
    const verifikasi = document.getElementById('inputVerifikasiPassword')?.value;

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

function formatEmailValid(email) {
    return /^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(email);
}

function tampilError(pesan) {
    const el = document.getElementById('pesanError');
    if (el) {
        el.textContent    = pesan;
        el.style.display  = 'block';
        setTimeout(() => { el.style.display = 'none'; }, 4000);
    } else {
        alert(pesan);
    }
}

// ── EDIT INFO WEBSITE (admin/edit-info.php) ──────────────────

// State per seksi: simpan nilai asli untuk tombol batal
const nilaiAsliInfo = {};

// Mapping seksi ke daftar input ID-nya
const inputPerSeksi = {
    kontak : ['inputWaAdmin', 'inputEmailAdmin'],
    jam    : ['inputJamBuka', 'inputJamTutup',
              'inputHariOperasional', 'inputCatatanJam'],
    alamat : ['inputNamaOutlet', 'inputAlamatOutlet',
              'inputKecamatanOutlet', 'inputMapsOutlet'],
    kurir  : ['inputBiayaKurir', 'inputCatatanKurir']
};

function toggleEditSeksi(seksi) {
    // Simpan nilai asli
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

    // Untuk seksi kurir: tampilkan checkboxes, sembunyikan pills
    if (seksi === 'kurir') {
        document.getElementById('kecamatanPills').style.display      = 'none';
        document.getElementById('kecamatanCheckboxes').style.display = 'flex';
    }

    document.getElementById('tombolEdit' + capitalize(seksi)).style.display = 'none';
    document.getElementById('simpan'     + capitalize(seksi)).style.display = 'block';
}

function batalEditSeksi(seksi) {
    // Kembalikan nilai asli
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

    // Untuk seksi kurir: tampilkan kembali pills
    if (seksi === 'kurir') {
        document.getElementById('kecamatanPills').style.display      = 'flex';
        document.getElementById('kecamatanCheckboxes').style.display = 'none';
    }

    document.getElementById('tombolEdit' + capitalize(seksi)).style.display = 'inline-block';
    document.getElementById('simpan'     + capitalize(seksi)).style.display = 'none';
}

function simpanSeksi(seksi) {
    // Validasi per seksi
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
        // Update pills tampilan
        const pillsEl = document.getElementById('kecamatanPills');
        pillsEl.innerHTML = [...checked]
            .map(c => `<span class="pill-kecamatan">${c.value}</span>`)
            .join('');
    }

    // Di sini backend POST ke ?action=simpan_$seksi
    batalEditSeksi(seksi);
    tampilPopupBerhasil(
        'Berhasil Disimpan!',
        'Perubahan sudah aktif dan langsung memengaruhi halaman terkait.'
    );
}

// Navigasi sidebar
function aktifkanNav(el) {
    document.querySelectorAll('.edit-info-nav-item')
            .forEach(a => a.classList.remove('aktif-nav'));
    el.classList.add('aktif-nav');
}

// Pop-up berhasil (reuse dari profil-member.js pattern)
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

// Helper
function capitalize(str) {
    return str.charAt(0).toUpperCase() + str.slice(1);
}
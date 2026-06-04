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
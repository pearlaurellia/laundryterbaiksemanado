'use strict';

const nilaiAsliInfo = {};

const inputPerSeksi = {
    kontak : ['inputWaAdmin', 'inputEmailAdmin'],
    jam    : ['inputJamBuka', 'inputJamTutup', 'inputHariOperasional', 'inputCatatanJam'],
    alamat : ['inputNamaOutlet', 'inputAlamatOutlet', 'inputKecamatanOutlet', 'inputMapsOutlet'],
    kurir  : ['inputBiayaKurir', 'inputCatatanKurir']
};

document.addEventListener('DOMContentLoaded', () => {
    const inputsMintaValidasi = document.querySelectorAll(
        '#inputEmail, #inputPassword, #inputUsername, #inputNamaDepan, #inputNoWA, #inputNamaProfil, #inputNoHP'
    );

    inputsMintaValidasi.forEach(input => {
        input.addEventListener('blur', () => {
            validasiInputTunggal(input);
        });
        
        input.addEventListener('input', () => {
            bersihkanErrorInput(input);
        });
    });
});

function validasiInputTunggal(input) {
    const id = input.id;
    const nilai = input.value.trim();

    if (!nilai) {
        if (id === 'inputVerifikasiPassword') return;
        tampilErrorDiBawahInput(input, 'Field ini tidak boleh kosong.');
        return;
    }

    if (id === 'inputEmail' && !formatEmailValid(nilai)) {
        tampilErrorDiBawahInput(input, 'Format alamat email tidak valid.');
    } 
    else if (id === 'inputUsername' && nilai.length < 4) {
        tampilErrorDiBawahInput(input, 'Username minimal harus 4 karakter.');
    } 
    else if ((id === 'inputNoWA' || id === 'inputNoHP') && !/^[0-9]{10,13}$/.test(nilai)) {
        tampilErrorDiBawahInput(input, 'Nomor harus berupa angka 10–13 digit.');
    } 
    else if (id === 'inputPassword' && nilai.length < 8) {
        tampilErrorDiBawahInput(input, 'Password minimal harus 8 karakter.');
    } 
    else if (id === 'inputVerifikasiPassword') {
        const passUtama = document.getElementById('inputPassword').value;
        if (nilai !== passUtama) {
            tampilErrorDiBawahInput(input, 'Konfirmasi password tidak cocok.');
        }
    }
}

function validasiFormPesan(opsiPengantaran) {
    const layananId = document.getElementById('inputLayananId').value;
    if (!layananId) {
        tampilErrorGlobal('Pilih jenis layanan terlebih dahulu.');
        return false;
    }

    if (opsiPengantaran === 'kurir') {
        const kecamatan = document.getElementById('inputKecamatan').value;
        const alamat    = document.getElementById('inputAlamat').value.trim();
        if (!kecamatan) {
            tampilErrorGlobal('Pilih kecamatan tujuan terlebih dahulu.');
            return false;
        }
        if (!alamat) {
            tampilErrorGlobal('Masukkan alamat lengkap tujuan pengantaran.');
            return false;
        }
    }
    return true;
}

function validasiFormLogin() {
    let statusValid = true;
    const emailEl = document.getElementById('inputEmail');
    const passEl = document.getElementById('inputPassword');

    if (!emailEl?.value.trim()) {
        tampilErrorDiBawahInput(emailEl, 'Email tidak boleh kosong.');
        statusValid = false;
    }
    if (!passEl?.value) {
        tampilErrorDiBawahInput(passEl, 'Password tidak boleh kosong.');
        statusValid = false;
    }

    return statusValid;
}

function validasiFormRegister() {
    let statusValid = true;
    
    const fields = [
        { el: document.getElementById('inputUsername'), msg: 'Username wajib diisi.' },
        { el: document.getElementById('inputNamaDepan'), msg: 'Nama depan wajib diisi.' },
        { el: document.getElementById('inputNoWA'), msg: 'Nomor WhatsApp wajib diisi.' },
        { el: document.getElementById('inputEmail'), msg: 'Email wajib diisi.' },
        { el: document.getElementById('inputPassword'), msg: 'Password wajib diisi.' },
        { el: document.getElementById('inputVerifikasiPassword'), msg: 'Konfirmasi password wajib diisi.' }
    ];

    fields.forEach(field => {
        if (!field.el?.value.trim()) {
            tampilErrorDiBawahInput(field.el, field.msg);
            statusValid = false;
        }
    });

    if (!statusValid) return false;

    const password = document.getElementById('inputPassword').value;
    const verifikasi = document.getElementById('inputVerifikasiPassword').value;

    if (password.length < 8) {
        tampilErrorDiBawahInput(document.getElementById('inputPassword'), 'Password minimal 8 karakter.');
        return false;
    }
    if (password !== verifikasi) {
        tampilErrorDiBawahInput(document.getElementById('inputVerifikasiPassword'), 'Konfirmasi password tidak cocok.');
        return false;
    }

    return true;
}

function validasiFormProfil() {
    let statusValid = true;
    const namaEl = document.getElementById('inputNamaProfil');
    const noHPEl = document.getElementById('inputNoHP');

    if (!namaEl?.value.trim()) {
        tampilErrorDiBawahInput(namaEl, 'Nama tidak boleh kosong.');
        statusValid = false;
    }
    if (!noHPEl?.value.trim()) {
        tampilErrorDiBawahInput(noHPEl, 'Nomor HP tidak boleh kosong.');
        statusValid = false;
    }

    return statusValid;
}

function formatEmailValid(email) {
    return /^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(email);
}

function tampilErrorDiBawahInput(inputEl, pesan) {
    if (!inputEl) return;
    
    let errorEl = inputEl.parentNode.querySelector('.error-message-inline');
    
    if (!errorEl) {
        errorEl = document.createElement('span');
        errorEl.className = 'error-message-inline';
        errorEl.style.color = '#ef4444';
        errorEl.style.fontSize = '0.8rem';
        errorEl.style.display = 'block';
        errorEl.style.marginTop = '4px';
        errorEl.style.fontWeight = '600';
        inputEl.parentNode.insertBefore(errorEl, inputEl.nextSibling);
    }
    
    errorEl.textContent = pesan;
    inputEl.style.borderColor = '#ef4444';
}

function bersihkanErrorInput(inputEl) {
    if (!inputEl) return;
    const errorEl = inputEl.parentNode.querySelector('.error-message-inline');
    if (errorEl) {
        errorEl.remove();
    }
    inputEl.style.borderColor = ''; 
}

function tampilErrorGlobal(pesan) {
    const el = document.getElementById('pesanError');
    if (el) {
        el.textContent = pesan;
        el.style.display = 'block';
        setTimeout(() => { el.style.display = 'none'; }, 4000);
    } else {
        alert(pesan);
    }
}

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
        const pils = document.getElementById('kecamatanPills');
        const boxs = document.getElementById('kecamatanCheckboxes');
        if(pils) pils.style.display = 'none';
        if(boxs) boxs.style.display = 'flex';
    }

    const btnEdit = document.getElementById('tombolEdit' + _capitalize(seksi));
    const btnSimpan = document.getElementById('simpan' + _capitalize(seksi));
    if(btnEdit) btnEdit.style.display = 'none';
    if(btnSimpan) btnSimpan.style.display = 'block';
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
                bersihkanErrorInput(el);
            }
        });
    }

    if (seksi === 'kurir') {
        const pils = document.getElementById('kecamatanPills');
        const boxs = document.getElementById('kecamatanCheckboxes');
        if(pils) pils.style.display = 'flex';
        if(boxs) boxs.style.display = 'none';
    }

    const btnEdit = document.getElementById('tombolEdit' + _capitalize(seksi));
    const btnSimpan = document.getElementById('simpan' + _capitalize(seksi));
    if(btnEdit) btnEdit.style.display = 'inline-block';
    if(btnSimpan) btnSimpan.style.display = 'none';
}

async function simpanSeksi(seksi) {
    if (seksi === 'kontak') {
        const waEl = document.getElementById('inputWaAdmin');
        const emailEl = document.getElementById('inputEmailAdmin');
        
        if (!/^62[0-9]{9,12}$/.test(waEl.value.trim())) {
            tampilErrorDiBawahInput(waEl, 'Gunakan format 628xxx tanpa tanda +.');
            return;
        }
        if (!formatEmailValid(emailEl.value.trim())) {
            tampilErrorDiBawahInput(emailEl, 'Format alamat email outlet tidak valid.');
            return;
        }
    }

    if (seksi === 'jam') {
        const bukaEl = document.getElementById('inputJamBuka');
        const tutupEl = document.getElementById('inputJamTutup');
        if (!bukaEl.value || !tutupEl.value) {
            tampilErrorGlobal('Jam operasional buka dan tutup wajib ditentukan.');
            return;
        }
        if (bukaEl.value >= tutupEl.value) {
            tampilErrorDiBawahInput(bukaEl, 'Jam buka harus lebih awal.');
            return;
        }
    }

    if (seksi === 'alamat') {
        const alamatEl = document.getElementById('inputAlamatOutlet');
        if (!alamatEl.value.trim()) {
            tampilErrorDiBawahInput(alamatEl, 'Alamat fisik outlet tidak boleh kosong.');
            return;
        }
    }

    if (seksi === 'kurir') {
        const biayaEl = document.getElementById('inputBiayaKurir');
        if (!biayaEl.value || parseInt(biayaEl.value) < 0) {
            tampilErrorDiBawahInput(biayaEl, 'Nominal biaya kurir pengantaran tidak valid.');
            return;
        }
        const checked = document.querySelectorAll('#kecamatanCheckboxes input[type="checkbox"]:checked');
        if (checked.length === 0) {
            tampilErrorGlobal('Pilih minimal satu kecamatan cakupan kurir.');
            return;
        }
        
        const pillsEl = document.getElementById('kecamatanPills');
        if (pillsEl) {
            pillsEl.innerHTML = [...checked].map(c => `<span class="pill-kecamatan">${c.value}</span>`).join('');
        }
    }

    const payload = {};
    inputPerSeksi[seksi].forEach(id => {
        const el = document.getElementById(id);
        if (el) payload[id] = el.value;
    });

    if (seksi === 'kurir') {
        const checked = document.querySelectorAll('#kecamatanCheckboxes input[type="checkbox"]:checked');
        payload['kecamatan_dilayani'] = [...checked].map(c => c.value);
    }

    try {
        const targetAction = (seksi === 'kurir') ? 'simpan_kurir' : 'simpan_' + seksi;
        const res = await fetch(`edit-info.php?action=${targetAction}`, {
            method : 'POST',
            headers: { 'Content-Type': 'application/json' },
            body   : JSON.stringify(payload)
        });
        const json = await res.json();
        
        if (!json.success) {
            console.error('simpanSeksi: server error —', json.message);
            tampilErrorGlobal(json.message || 'Gagal menyimpan data ke database.');
            return;
        }
    } catch (err) {
        console.error('simpanSeksi: fetch gagal —', err);
        tampilErrorGlobal('Koneksi database bermasalah. Silakan periksa Apache local server.');
        return;
    }

    batalEditSeksi(seksi);
    tampilPopupBerhasil(
        'Berhasil Disimpan!',
        'Perubahan data profil usaha CleanCo telah aktif di database.'
    );
}

function aktifkanNav(el) {
    document.querySelectorAll('.edit-info-nav-item').forEach(a => a.classList.remove('aktif-nav'));
    if(el) el.classList.add('aktif-nav');
}

function tampilPopupBerhasil(judul, teks) {
    const jdlEl = document.getElementById('popupBerhasilJudul');
    const txtEl = document.getElementById('popupBerhasilTeks');
    const ovrEl = document.getElementById('overlayPopup');
    const popEl = document.getElementById('popupBerhasil');

    if(jdlEl) jdlEl.textContent = judul;
    if(txtEl) txtEl.textContent = teks;
    if(ovrEl) ovrEl.style.display = 'block';
    if(popEl) popEl.style.display = 'block';
}

function tutupPopupBerhasil() {
    const ovrEl = document.getElementById('overlayPopup');
    const popEl = document.getElementById('popupBerhasil');
    if(ovrEl) ovrEl.style.display = 'none';
    if(popEl) popEl.style.display = 'none';
}

function _capitalize(str) {
    return str.charAt(0).toUpperCase() + str.slice(1);
}
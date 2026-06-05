/**
 * assets/js/pesan-member.js
 * Tugas: validasi client-side saja.
 * Submit diserahkan ke form HTML biasa → PHP handler.
 */

function kirimPesanan(event) {
    event.preventDefault();

    const errors = [];

    // Cek layanan dipilih
    const layananId = document.getElementById('inputLayananId').value;
    if (!layananId) {
        errors.push('Pilih layanan terlebih dahulu.');
    }

    // Cek opsi pengantaran
    const opsiDipilih = document.querySelector('input[name="opsi_pengantaran"]:checked');
    if (!opsiDipilih) {
        errors.push('Pilih opsi pengantaran terlebih dahulu.');
    }

    // Jika kurir, cek kecamatan dan alamat
    if (opsiDipilih && opsiDipilih.value === 'kurir') {
        const kecamatan = document.getElementById('inputKecamatan').value.trim();
        const alamat    = document.getElementById('inputAlamat').value.trim();

        if (!kecamatan) {
            errors.push('Pilih kecamatan tujuan.');
        }
        if (!alamat) {
            errors.push('Isi alamat lengkap tujuan.');
        }
    }

    // Tampilkan error jika ada
    let errorBox = document.querySelector('.pesan-error-box');
    if (errors.length > 0) {
        if (errorBox) {
            errorBox.innerHTML      = errors.map(e => `<p>⚠ ${e}</p>`).join('');
            errorBox.style.display  = 'block';
        } else {
            alert(errors.join('\n'));
        }
        window.scrollTo({ top: 0, behavior: 'smooth' });
        return;
    }

    // Sembunyikan error box jika valid
    if (errorBox) errorBox.style.display = 'none';

    // Submit form normal ke PHP
    document.getElementById('formPesan').submit();
}
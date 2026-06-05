/**
 * ============================================================
 * pesan-member.js — CleanCo Laundry
 * Ditujukan untuk: member/pesan.php
 * Murni Native JavaScript (Tanpa Library/Framework)
 * ============================================================
 */

'use strict';

// Scope global variabel window agar terbaca oleh kalkulasi-harga.js
window.tarifAktif = 0;
window.namaLayananAktif = '';
window.biayaKurir = 0;

document.addEventListener('DOMContentLoaded', () => {
    const formPesan = document.getElementById('formPesan');
    const container = document.getElementById('formPesanContainer');
    if (!formPesan || !container) return;

    // 1. Ambil Data Kondisi Awal Kartu Pilihan Default
    const kartuAktif = document.querySelector('.kartu-pilih-layanan.dipilih');
    if (kartuAktif) {
        window.tarifAktif = parseFloat(kartuAktif.dataset.tarif) || 0;
        window.namaLayananAktif = kartuAktif.dataset.nama || '';
    }

    const radioOpsi = document.querySelector('input[name="opsi_pengantaran"]:checked');
    if (radioOpsi) {
        window.biayaKurir = (radioOpsi.value === 'kurir') ? 10000 : 0;
    }

    if (typeof hitungEstimasi === 'function') hitungEstimasi();

    // 2. INTERCEPTOR EVENT SUBMIT FORM (Sesuai Aturan Fase 7.4)
    formPesan.addEventListener('submit', async (e) => {
        e.preventDefault();

        const radioTerpilih = document.querySelector('input[name="opsi_pengantaran"]:checked');
        const opsi = radioTerpilih ? radioTerpilih.value : 'ambil_sendiri';

        if (typeof validasiFormPesan === 'function') {
            if (!validasiFormPesan(opsi)) return;
        }

        const memberNama = container.dataset.memberNama || 'Member';
        const noWaAdmin  = container.dataset.noWaAdmin  || '';
        const kecamatan  = document.getElementById('inputKecamatan')
                           ? document.getElementById('inputKecamatan').value
                           : '';
        const labelOpsi  = opsi === 'kurir'
                           ? 'Kurir ke ' + kecamatan
                           : 'Ambil Sendiri';

        const noWaBersih = noWaAdmin.replace(/\D/g, '');

        // Langkah Krusial: Buka WhatsApp SEBELUM fetch (Bebas Popup Blocker)
        if (noWaBersih) {
            const pesanWa = encodeURIComponent(
                'Halo Admin CleanCo! 👋\n' +
                'Saya baru saja membuat pesanan baru:\n' +
                '• Nama        : ' + memberNama + '\n' +
                '• Layanan     : ' + window.namaLayananAktif + '\n' +
                '• Pengantaran : ' + labelOpsi + '\n' +
                'Mohon konfirmasinya. Terima kasih!'
            );
            window.open('https://wa.me/' + noWaBersih + '?text=' + pesanWa, '_blank');
        } else {
            console.warn('Nomor WhatsApp admin tidak ditemukan. Cek data-no-wa-admin di formPesanContainer.');
        }

        // Kirim data form ke backend via Fetch POST
        try {
            const formData = new FormData(formPesan);

            const response = await fetch('pesan.php?action=submit_ajax', {
                method: 'POST',
                body: formData
            });

            const result = await response.json();

            if (result.success) {
                document.getElementById('popupNoPesanan').textContent   = '#' + result.data.kode_pesanan;
                document.getElementById('popupLayanan').textContent     = result.data.nama_layanan;
                document.getElementById('popupPengantaran').textContent = result.data.opsi_pengantaran === 'kurir'
                                                                          ? 'Kurir Antar-Jemput'
                                                                          : 'Ambil Mandiri';
                document.getElementById('popupEstimasi').textContent    = result.data.total_estimasi;

                document.getElementById('overlayPopup').style.display = 'block';
                document.getElementById('popupSukses').style.display  = 'block';
            } else {
                alert(result.message || 'Gagal menyimpan pesanan ke database.');
            }

        } catch (err) {
            console.error('Fetch POST Error:', err);
            alert('Terjadi kendala jaringan saat mengirim data pesanan.');
        }
    });
});

/**
 * Global Function: Mengurusi perpindahan kelas aktif pada komponen kartu
 */
function pilihLayanan(el) {
    document.querySelectorAll('.kartu-pilih-layanan').forEach(k => k.classList.remove('dipilih'));
    document.querySelectorAll('.kartu-pilih-header').forEach(h => h.classList.remove('kartu-pilih-header-biru'));

    el.classList.add('dipilih');
    el.querySelector('.kartu-pilih-header').classList.add('kartu-pilih-header-biru');

    document.getElementById('inputLayananId').value = el.dataset.id;

    window.tarifAktif = parseFloat(el.dataset.tarif) || 0;
    window.namaLayananAktif = el.dataset.nama || '';
    if (typeof hitungEstimasi === 'function') hitungEstimasi();
}

/**
 * Global Function: Mengurusi visibilitas form isian alamat
 */
function gantiOpsiPengantaran(opsi) {
    document.querySelectorAll('.kartu-opsi-pengantaran').forEach(lbl => lbl.classList.remove('dipilih-opsi'));

    const radioTarget = document.querySelector(`input[name="opsi_pengantaran"][value="${opsi}"]`);
    if (radioTarget) {
        radioTarget.checked = true;
        radioTarget.closest('.kartu-opsi-pengantaran').classList.add('dipilih-opsi');
    }

    const infoKurir       = document.getElementById('infoKurir');
    const containerAlamat = document.getElementById('containerAlamat');

    if (opsi === 'kurir') {
        if (infoKurir)       infoKurir.style.display       = 'block';
        if (containerAlamat) containerAlamat.style.display = 'block';
        window.biayaKurir = 10000;
    } else {
        if (infoKurir)       infoKurir.style.display       = 'none';
        if (containerAlamat) containerAlamat.style.display = 'none';
        window.biayaKurir = 0;
    }

    if (typeof hitungEstimasi === 'function') hitungEstimasi();
}

/**
 * Global Function: Jembatan pemicu klik tombol kustom form
 */
function kirimPesananForm(e) {
    const form = document.getElementById('formPesan');
    if (form) {
        form.dispatchEvent(new Event('submit', { cancelable: true, bubbles: true }));
    }
}

/**
 * Global Function: Mengosongkan total isi data form saat klik Pesan Lagi
 */
function pesanLagi() {
    const formPesan = document.getElementById('formPesan');
    if (formPesan) {
        formPesan.reset();
        gantiOpsiPengantaran('kurir');
        if (typeof hitungEstimasi === 'function') hitungEstimasi();
    }
    document.getElementById('overlayPopup').style.display = 'none';
    document.getElementById('popupSukses').style.display  = 'none';
}
        let layananAktif    = { id: 2, nama: 'Express', tarif: 15000, satuan: 'kg' };
        let opsiPengantaran = 'kurir';
        const BIAYA_KURIR   = 10000;

        const WA_ADMIN      = '6281234567890';

        function pilihLayanan(el) {
            document.querySelectorAll('.kartu-pilih-layanan')
                    .forEach(k => k.classList.remove('dipilih'));
            el.classList.add('dipilih');

            layananAktif = {
                id    : el.dataset.id,
                nama  : el.dataset.nama,
                tarif : parseInt(el.dataset.tarif),
                satuan: el.dataset.satuan
            };
            document.getElementById('inputLayananId').value = layananAktif.id;
            hitungEstimasi();
        }

        function gantiOpsiPengantaran(opsi) {
            opsiPengantaran = opsi;

            document.querySelectorAll('.kartu-opsi-pengantaran')
                    .forEach(k => k.classList.remove('dipilih-opsi'));
            const radio = document.querySelector(`input[value="${opsi}"]`);
            if (radio) radio.closest('.kartu-opsi-pengantaran').classList.add('dipilih-opsi');

            const tampilKurir = opsi === 'kurir';
            document.getElementById('infoKurir').style.display   = tampilKurir ? 'block' : 'none';
            document.getElementById('seksiAlamat').style.display = tampilKurir ? 'block' : 'none';

            if (!tampilKurir) {
                document.getElementById('inputKecamatan').value = '';
                document.getElementById('inputAlamat').value    = '';
            }
            hitungEstimasi();
        }

        function hitungEstimasi() {
            const berat    = parseFloat(document.getElementById('inputEstimasiBerat').value) || 0;
            const kotakEl  = document.getElementById('kotakEstimasi');
            const teksEl   = document.getElementById('teksEstimasiHarga');
            const biayaKurir = opsiPengantaran === 'kurir' ? BIAYA_KURIR : 0;

            if (berat <= 0) {
                kotakEl.classList.remove('kotak-estimasi-ada');
                teksEl.innerHTML =
                    'Harga akan dihitung admin setelah pakaian ditimbang.';
                return;
            }

            const biayaLayanan = berat * layananAktif.tarif;
            const total        = biayaLayanan + biayaKurir;
            const fmt = n => 'Rp ' + n.toLocaleString('id-ID');

            kotakEl.classList.add('kotak-estimasi-ada');
            teksEl.innerHTML = `
                <span class="estimasi-label">Estimasi Biaya</span><br>
                ${layananAktif.nama} (${berat} kg × ${fmt(layananAktif.tarif)})
                = ${fmt(biayaLayanan)}<br>
                ${opsiPengantaran === 'kurir' ? `Kurir = ${fmt(biayaKurir)}<br>` : ''}
                <strong>Total Estimasi : ${fmt(total)}</strong>
                <span class="estimasi-belum-final">— Harga Belum Final</span>
            `;
        }

        function kirimPesanan(e) {
            e.preventDefault();

            // Validasi wajib
            if (opsiPengantaran === 'kurir') {
                if (!document.getElementById('inputKecamatan').value) {
                    alert('Pilih kecamatan tujuan terlebih dahulu.');
                    return;
                }
                if (!document.getElementById('inputAlamat').value.trim()) {
                    alert('Masukkan alamat lengkap tujuan pengantaran.');
                    return;
                }
            }

            const kecamatan = document.getElementById('inputKecamatan').value;
            const pesan     = encodeURIComponent(
                `Halo Admin CleanCo! 🧺\n\n` +
                `Saya baru saja membuat pesanan baru:\n` +
                `• ID Pesanan  : #LDR-${Date.now().toString().slice(-4)}\n` +
                `• Layanan     : ${layananAktif.nama}\n` +
                `• Pengantaran : ${opsiPengantaran === 'kurir' ? 'Kurir' : 'Ambil Sendiri'}\n` +
                (opsiPengantaran === 'kurir' ? `• Kecamatan   : ${kecamatan}\n` : '') +
                `\nMohon konfirmasinya. Terima kasih!`
            );
            window.open(`https://wa.me/${WA_ADMIN}?text=${pesan}`, '_blank');

            const noPesanan   = '#LDR-' + Date.now().toString().slice(-4);
            const berat        = parseFloat(document.getElementById('inputEstimasiBerat').value) || 0;
            const biayaKurir   = opsiPengantaran === 'kurir' ? BIAYA_KURIR : 0;
            const totalEstimasi= berat > 0
                ? 'Rp ' + (berat * layananAktif.tarif + biayaKurir).toLocaleString('id-ID')
                : 'Akan dihitung setelah ditimbang';

            document.getElementById('popupNoPesanan').textContent   = noPesanan;
            document.getElementById('popupLayanan').textContent     = layananAktif.nama;
            document.getElementById('popupPengantaran').textContent =
                opsiPengantaran === 'kurir'
                    ? 'Kurir Laundry — ' + kecamatan
                    : 'Ambil Sendiri ke Outlet';
            document.getElementById('popupEstimasi').textContent    = totalEstimasi;

            document.getElementById('overlayPopup').style.display      = 'block';
            document.getElementById('popupSukses').style.display       = 'block';
        }

        function pesanLagi() {
            document.getElementById('overlayPopup').style.display = 'none';
            document.getElementById('popupSukses').style.display  = 'none';
            document.getElementById('inputEstimasiBerat').value   = '';
            document.getElementById('inputCatatan').value         = '';
            document.getElementById('inputKecamatan').value       = '';
            document.getElementById('inputAlamat').value          = '';
            hitungEstimasi();
        }

        // Init
        gantiOpsiPengantaran('kurir');

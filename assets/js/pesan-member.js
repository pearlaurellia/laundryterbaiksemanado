let layananAktif    = { id: 2, nama: 'Express', tarif: 15000, satuan: 'kg' };
        let opsiPengantaran = 'kurir';
        const BIAYA_KURIR   = 10000;

        const WA_ADMIN      = '6282172567295';

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

            // ── Buat kode pesanan SEKALI di sini, pakai ulang ke WA & localStorage ──
            // Format: LDR-YYYYMMDD-XXXX (tanggal + 4 digit acak) → unik & terbaca
            const now       = new Date();
            const tglStr    = now.getFullYear().toString() +
                              String(now.getMonth() + 1).padStart(2, '0') +
                              String(now.getDate()).padStart(2, '0');
            const acak      = Math.floor(1000 + Math.random() * 9000); // 4 digit acak
            const kodePesanan = `LDR-${tglStr}-${acak}`;               // contoh: LDR-20261204-3847
            const noPesanan   = '#' + kodePesanan;

            const pesan = encodeURIComponent(
                `Halo Admin CleanCo! 🧺\n\n` +
                `Saya baru saja membuat pesanan baru:\n` +
                `• ID Pesanan  : ${noPesanan}\n` +
                `• Layanan     : ${layananAktif.nama}\n` +
                `• Pengantaran : ${opsiPengantaran === 'kurir' ? 'Kurir' : 'Ambil Sendiri'}\n` +
                (opsiPengantaran === 'kurir' ? `• Kecamatan   : ${kecamatan}\n` : '') +
                `\nMohon konfirmasinya. Terima kasih!`
            );
            window.open(`https://wa.me/${WA_ADMIN}?text=${pesan}`, '_blank');
            const berat        = parseFloat(document.getElementById('inputEstimasiBerat').value) || 0;
            const biayaKurir   = opsiPengantaran === 'kurir' ? BIAYA_KURIR : 0;
            const totalEstimasi= berat > 0
                ? 'Rp ' + (berat * layananAktif.tarif + biayaKurir).toLocaleString('id-ID')
                : 'Akan dihitung setelah ditimbang';

            // ── Simpan pesanan baru ke localStorage ──────────────────
            const rawStorage    = localStorage.getItem('cleanco_pesanan');
            const dataTersimpan = rawStorage ? JSON.parse(rawStorage) : {};
            const idBaru         = now.getTime(); // pakai timestamp yang sama dengan kode
            const kodeBersih     = kodePesanan; // sudah tanpa #
            const alamatInput    = opsiPengantaran === 'kurir'
                ? (document.getElementById('inputAlamat')?.value.trim() || '')
                : '';
            const kecamatanInput = opsiPengantaran === 'kurir' ? kecamatan : '';

            // Ambil nama dari session PHP yang di-output ke JS global sessionMember
            // Fallback ke 'Member' kalau sessionMember belum tersedia (dev mode)
            const sesi = (typeof sessionMember !== 'undefined') ? sessionMember : {
                nama: 'Member', username: '@member', namaLengkap: '', noHP: '', id: 0
            };

            dataTersimpan[idBaru] = {
                id           : idBaru,
                kode         : kodeBersih,
                nama         : sesi.nama,
                username     : sesi.username,
                waktu        : new Date().toLocaleString('id-ID'),
                namaLengkap  : sesi.namaLengkap,
                alamat       : alamatInput,
                kecamatan    : kecamatanInput,
                telpon       : sesi.noHP,
                layanan      : layananAktif.nama,
                pengiriman   : opsiPengantaran === 'kurir' ? 'Antar' : 'Pickup',
                tarifLayanan : layananAktif.tarif,
                tarifKirim   : opsiPengantaran === 'kurir' ? BIAYA_KURIR : 0,
                tags         : [
                    { label: 'Cuci', tipe: 'hijau' },
                    { label: layananAktif.nama, tipe: 'biru' },
                    { label: opsiPengantaran === 'kurir' ? 'Antar' : 'Pickup', tipe: 'biru' }
                ],
                status       : 'menunggu_konfirmasi',
                statusMember : 'menunggu_konfirmasi',
                berat        : null,
                note         : document.getElementById('inputCatatan')?.value.trim() || null,
                opsi         : opsiPengantaran,
                metaWaktu    : layananAktif.nama + ' · ' +
                               (opsiPengantaran === 'kurir' ? 'Kurir' : 'Ambil Sendiri') +
                               ' · ' + new Date().toLocaleString('id-ID'),
                alasanBatal  : null,
                dibatalkanOleh: null
            };
            localStorage.setItem('cleanco_pesanan', JSON.stringify(dataTersimpan));
            // ─────────────────────────────────────────────────────────

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
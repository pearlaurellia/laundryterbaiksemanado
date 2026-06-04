
        const dataMember = {
            1: {
                nama: "Ryan Liam",
                username: "@liam999",
                namaLengkap: "Ryan Liam Santoso",
                email: "ryanl9@gmail.com",
                noHP: "0834545827",
                alamat: "Jl. Paal 4 No. 12, Ling. III",
                kecamatan: "Wanea",
                tanggalBergabung: "10-04-2025",
                status: "aktif",
                jmlPesanan: 5,
                pesananSelesai: 4,
                pesananAktif: 1,
                pesananBatal: 0,
                totalOmzet: 185000,
                riwayatSingkat: [
                    { kode: "#LDR-001", layanan: "Express", status: "Selesai",   total: "Rp 35.000" },
                    { kode: "#LDR-002", layanan: "Reguler", status: "Selesai",   total: "Rp 24.000" },
                    { kode: "#LDR-003", layanan: "Express", status: "Aktif",     total: "Rp 40.000" }
                ]
            },
            2: {
                nama: "Sinta Dewi",
                username: "@sintad",
                namaLengkap: "Sinta Dewi Rahayu",
                email: "sintad@gmail.com",
                noHP: "0812345678",
                alamat: "Jl. Bahu Lingkungan I No. 5",
                kecamatan: "Malalayang",
                tanggalBergabung: "22-03-2025",
                status: "aktif",
                jmlPesanan: 3,
                pesananSelesai: 3,
                pesananAktif: 0,
                pesananBatal: 0,
                totalOmzet: 72000,
                riwayatSingkat: [
                    { kode: "#LDR-010", layanan: "Reguler",     status: "Selesai", total: "Rp 24.000" },
                    { kode: "#LDR-011", layanan: "Express",     status: "Selesai", total: "Rp 30.000" },
                    { kode: "#LDR-012", layanan: "Dry Cleaning", status: "Selesai", total: "Rp 18.000" }
                ]
            },
            3: {
                nama: "Budi Santoso",
                username: "@budis",
                namaLengkap: "Budi Santoso",
                email: "budis@gmail.com",
                noHP: "0856789012",
                alamat: "Jl. Tikala Ares No. 88",
                kecamatan: "Tikala",
                tanggalBergabung: "01-01-2025",
                status: "nonaktif",
                jmlPesanan: 8,
                pesananSelesai: 7,
                pesananAktif: 0,
                pesananBatal: 1,
                totalOmzet: 340000,
                riwayatSingkat: [
                    { kode: "#LDR-020", layanan: "Reguler", status: "Selesai",    total: "Rp 40.000" },
                    { kode: "#LDR-021", layanan: "Express", status: "Selesai",    total: "Rp 60.000" },
                    { kode: "#LDR-022", layanan: "Reguler", status: "Dibatalkan", total: "—" }
                ]
            },
            4: {
                nama: "Mega Putri",
                username: "@megap",
                namaLengkap: "Mega Putri Wulandari",
                email: "megap@gmail.com",
                noHP: "0878901234",
                alamat: "Komp. Malalayang Permai Blok C No.3",
                kecamatan: "Malalayang",
                tanggalBergabung: "15-05-2025",
                status: "aktif",
                jmlPesanan: 1,
                pesananSelesai: 0,
                pesananAktif: 1,
                pesananBatal: 0,
                totalOmzet: 0,
                riwayatSingkat: [
                    { kode: "#LDR-030", layanan: "Express", status: "Aktif", total: "Rp 45.000" }
                ]
            },
            5: {
                nama: "Pearl Nafeesa",
                username: "@pearlnafeesa",
                namaLengkap: "Pearl Nafeesa",
                email: "hxn.aurelle@gmail.com",
                noHP: "08115723906",
                alamat: "Jl. Sea No. 1",
                kecamatan: "Wanea",
                tanggalBergabung: "02-06-2025",
                status: "aktif",
                jmlPesanan: 2,
                pesananSelesai: 2,
                pesananAktif: 0,
                pesananBatal: 0,
                totalOmzet: 55000,
                riwayatSingkat: [
                    { kode: "#LDR-040", layanan: "Reguler", status: "Selesai", total: "Rp 24.000" },
                    { kode: "#LDR-041", layanan: "Express", status: "Selesai", total: "Rp 31.000" }
                ]
            }
        };

        let idAktif         = null;
        let aksiToggleSaat  = null; // 'aktif' | 'nonaktif'

        // ── BUKA DETAIL ──────────────────────────────────────────────
        function bukaMember(id, el) {
            idAktif = id;
            const m = dataMember[id];

            document.querySelectorAll('.item-member').forEach(i => i.classList.remove('aktif-dipilih'));
            el.classList.add('aktif-dipilih');

            document.getElementById('detailKosong').style.display = 'none';
            document.getElementById('detailIsi').style.display    = 'block';

            // Isi data dasar
            document.getElementById('detailNama').textContent            = m.nama;
            document.getElementById('detailUsername').textContent        = m.username;
            document.getElementById('detailTanggalBergabung').textContent = 'Bergabung ' + m.tanggalBergabung;
            document.getElementById('detailNamaLengkap').textContent     = m.namaLengkap;
            document.getElementById('detailEmail').textContent           = m.email;
            document.getElementById('detailNoHP').textContent            = m.noHP;
            document.getElementById('detailAlamat').textContent          = m.alamat;
            document.getElementById('detailKecamatan').textContent       = m.kecamatan;
            document.getElementById('detailTotalTransaksi').textContent  = m.jmlPesanan + ' pesanan';

            // Statistik
            document.getElementById('detailJmlPesanan').textContent     = m.jmlPesanan;
            document.getElementById('detailPesananSelesai').textContent = 'Selesai & Lunas : ' + m.pesananSelesai;
            document.getElementById('detailPesananAktif').textContent   = 'Sedang Aktif : '   + m.pesananAktif;
            document.getElementById('detailPesananBatal').textContent   = 'Dibatalkan : '      + m.pesananBatal;
            document.getElementById('detailTotalOmzet').textContent     =
                'Total Nilai Transaksi : Rp ' + m.totalOmzet.toLocaleString('id-ID');

            // Riwayat singkat
            const riwayatEl = document.getElementById('detailRiwayatSingkat');
            if (m.riwayatSingkat.length === 0) {
                riwayatEl.innerHTML = '<p style="color:#aaa;font-size:0.9rem;">Belum ada pesanan.</p>';
            } else {
                riwayatEl.innerHTML = m.riwayatSingkat.map(r => {
                    const warna = r.status === 'Selesai'    ? '#52c49c'
                                : r.status === 'Aktif'      ? '#3b82f6'
                                : r.status === 'Dibatalkan' ? '#f87171'
                                : '#aaa';
                    return `
                        <div class="baris-riwayat-singkat">
                            <span class="riwayat-kode">${r.kode}</span>
                            <span class="riwayat-layanan">${r.layanan}</span>
                            <span class="riwayat-total">${r.total}</span>
                            <span class="riwayat-status-badge"
                                  style="background-color:${warna}20;color:${warna};">
                                ${r.status}
                            </span>
                        </div>`;
                }).join('');
            }

            // Tombol WA
            document.getElementById('tombolWA').href =
                'https://wa.me/62' + m.noHP.replace(/^0/, '');

            // Status tombol
            setStatusAkunUI(m.status);
        }

        // ── SET UI STATUS ────────────────────────────────────────────
        function setStatusAkunUI(status) {
            const teksEl      = document.getElementById('statusAkunTeks');
            const tombolAktif = document.getElementById('tombolAktifkan');
            const tombolNon   = document.getElementById('tombolNonaktif');

            if (status === 'aktif') {
                teksEl.textContent          = 'Aktif';
                teksEl.style.color          = '#52c49c';
                tombolAktif.style.display   = 'none';
                tombolNon.style.display     = 'inline-block';
            } else {
                teksEl.textContent          = 'Nonaktif';
                teksEl.style.color          = '#f87171';
                tombolAktif.style.display   = 'inline-block';
                tombolNon.style.display     = 'none';
            }
        }

        // ── TOGGLE STATUS (dengan pop-up konfirmasi) ─────────────────
        function toggleStatusMember(aksi) {
            aksiToggleSaat = aksi;
            const m = dataMember[idAktif];

            document.getElementById('popupJudul').textContent =
                aksi === 'nonaktif' ? 'Nonaktifkan Akun?' : 'Aktifkan Kembali Akun?';
            document.getElementById('popupTeks').textContent =
                aksi === 'nonaktif'
                    ? `${m.nama} tidak akan bisa login setelah dinonaktifkan.`
                    : `${m.nama} akan bisa login kembali ke sistem.`;

            const tombolKonfirm = document.getElementById('popupTombolKonfirm');
            if (aksi === 'nonaktif') {
                tombolKonfirm.style.backgroundColor = '#f87171';
                tombolKonfirm.style.color           = 'white';
            } else {
                tombolKonfirm.style.backgroundColor = '#52c49c';
                tombolKonfirm.style.color           = '#1a4d3a';
            }

            document.getElementById('overlayPopup').style.display   = 'block';
            document.getElementById('popupKonfirmasi').style.display = 'block';
        }

        function konfirmasiToggle() {
            if (!idAktif || !aksiToggleSaat) return;

            // Update data dummy (di backend: POST ke ?action=toggle_status)
            dataMember[idAktif].status = aksiToggleSaat;

            // Update badge di sidebar
            const itemEl   = document.querySelector(`.item-member[data-id="${idAktif}"]`);
            const badgeEl  = itemEl.querySelector('.badge-status-member');
            if (aksiToggleSaat === 'aktif') {
                badgeEl.textContent = 'Aktif';
                badgeEl.className   = 'badge-status-member badge-member-aktif';
                itemEl.dataset.status = 'aktif';
            } else {
                badgeEl.textContent = 'Nonaktif';
                badgeEl.className   = 'badge-status-member badge-member-nonaktif';
                itemEl.dataset.status = 'nonaktif';
            }

            setStatusAkunUI(aksiToggleSaat);
            tutupPopup();
        }

        function tutupPopup() {
            document.getElementById('overlayPopup').style.display    = 'none';
            document.getElementById('popupKonfirmasi').style.display = 'none';
            aksiToggleSaat = null;
        }

        // ── FILTER ───────────────────────────────────────────────────
        function filterMember(status, btn) {
            document.querySelectorAll('.tombol-filter').forEach(b => b.classList.remove('aktif'));
            btn.classList.add('aktif');
            document.querySelectorAll('.item-member').forEach(item => {
                const cocok = status === 'semua' || item.dataset.status === status;
                item.style.display = cocok ? 'block' : 'none';
            });
        }

        // ── SEARCH ───────────────────────────────────────────────────
        function cariMember(query) {
            const q = query.toLowerCase().trim();
            document.querySelectorAll('.item-member').forEach(item => {
                const nama = item.dataset.nama || '';
                item.style.display = nama.includes(q) ? 'block' : 'none';
            });
        }
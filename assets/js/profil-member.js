        let modeEditProfil = false;
        let nilaiAsli      = {};

        // ── TOGGLE EDIT PROFIL ───────────────────────────────────────
        function toggleEditProfil() {
            modeEditProfil = !modeEditProfil;
            const inputs   = ['inputNamaProfil', 'inputNoHP'];
            const tombolEl = document.getElementById('tombolEditProfil');
            const simpanEl = document.getElementById('tombolSimpanProfil');

            if (modeEditProfil) {
                // Simpan nilai asli untuk batal
                inputs.forEach(id => {
                    nilaiAsli[id] = document.getElementById(id).value;
                    document.getElementById(id).removeAttribute('readonly');
                    document.getElementById(id).classList.add('input-editable');
                });
                tombolEl.style.display = 'none';
                simpanEl.style.display = 'block';
            }
        }

        function batalEditProfil() {
            const inputs = ['inputNamaProfil', 'inputNoHP'];
            inputs.forEach(id => {
                document.getElementById(id).value = nilaiAsli[id];
                document.getElementById(id).setAttribute('readonly', true);
                document.getElementById(id).classList.remove('input-editable');
            });
            modeEditProfil = false;
            document.getElementById('tombolEditProfil').style.display    = 'inline-block';
            document.getElementById('tombolSimpanProfil').style.display = 'none';
        }

        function simpanProfil() {
            const nama = document.getElementById('inputNamaProfil').value.trim();
            const noHP = document.getElementById('inputNoHP').value.trim();
            if (!nama) { alert('Nama tidak boleh kosong.'); return; }
            if (!/^[0-9]{10,13}$/.test(noHP)) {
                alert('Nomor WhatsApp harus berupa angka 10–13 digit.');
                return;
            }
            // Di sini backend POST dan simpan ke DB
            batalEditProfil();
            tampilPopupBerhasil('Profil Diperbarui', 'Nama dan nomor WhatsApp kamu berhasil disimpan.');
        }

        // ── GANTI PASSWORD ───────────────────────────────────────────
        function cekKuatPassword(val) {
            const wrapperEl = document.getElementById('kuatPasswordWrapper');
            const isiEl     = document.getElementById('kuatPasswordIsi');
            const labelEl   = document.getElementById('kuatPasswordLabel');
            wrapperEl.style.display = val.length > 0 ? 'flex' : 'none';
            if (val.length === 0) return;

            let skor = 0;
            if (val.length >= 8)              skor++;
            if (/[A-Z]/.test(val))            skor++;
            if (/[0-9]/.test(val))            skor++;
            if (/[^A-Za-z0-9]/.test(val))     skor++;

            const level  = ['', 'Lemah', 'Cukup', 'Kuat', 'Sangat Kuat'][skor];
            const warna  = ['', '#f87171', '#f59e0b', '#52c49c', '#0d3f8a'][skor];
            const lebar  = [0, 25, 50, 75, 100][skor];

            isiEl.style.width           = lebar + '%';
            isiEl.style.backgroundColor = warna;
            labelEl.textContent         = level;
            labelEl.style.color         = warna;
        }

        function cekKonfirmasi() {
            const baru    = document.getElementById('inputPasswordBaru').value;
            const konfirm = document.getElementById('inputKonfirmasiPassword').value;
            const pesanEl = document.getElementById('pesanKonfirmasi');
            if (!konfirm) { pesanEl.textContent = ''; return; }
            if (baru === konfirm) {
                pesanEl.textContent = '✓ Password cocok';
                pesanEl.style.color = '#52c49c';
            } else {
                pesanEl.textContent = '✕ Password tidak cocok';
                pesanEl.style.color = '#f87171';
            }
        }

        function gantiPassword() {
            const lama    = document.getElementById('inputPasswordLama').value;
            const baru    = document.getElementById('inputPasswordBaru').value;
            const konfirm = document.getElementById('inputKonfirmasiPassword').value;
            if (!lama)            { alert('Masukkan password saat ini.'); return; }
            if (baru.length < 8)  { alert('Password baru minimal 8 karakter.'); return; }
            if (baru !== konfirm) { alert('Konfirmasi password tidak cocok.'); return; }
            // Di sini backend POST dan verifikasi ke DB
            document.getElementById('inputPasswordLama').value    = '';
            document.getElementById('inputPasswordBaru').value    = '';
            document.getElementById('inputKonfirmasiPassword').value = '';
            document.getElementById('kuatPasswordWrapper').style.display = 'none';
            document.getElementById('pesanKonfirmasi').textContent = '';
            tampilPopupBerhasil('Password Diperbarui', 'Password kamu berhasil diganti. Gunakan password baru saat login berikutnya.');
        }

        // ── POP-UP BERHASIL ──────────────────────────────────────────
        function tampilPopupBerhasil(judul, teks) {
            document.getElementById('popupBerhasilJudul').textContent = judul;
            document.getElementById('popupBerhasilTeks').textContent  = teks;
            document.getElementById('overlayPopup').style.display     = 'block';
            document.getElementById('popupBerhasil').style.display    = 'block';
        }

        function tutupPopupBerhasil() {
            document.getElementById('overlayPopup').style.display = 'none';
            document.getElementById('popupBerhasil').style.display = 'none';
        }
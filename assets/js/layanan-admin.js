        let modeEdit   = false;
        let idEditAktif = null;

        function editLayanan(kartuEl) {
            modeEdit    = true;
            idEditAktif = kartuEl.dataset.id;

            document.getElementById('judulFormLayanan').textContent  = 'Edit Layanan';
            document.getElementById('inputNamaLayanan').value        = kartuEl.dataset.nama;
            document.getElementById('inputTarifLayanan').value       = kartuEl.dataset.tarif;
            document.getElementById('inputSatuanLayanan').value      = kartuEl.dataset.satuan;
            document.getElementById('inputDeskripsiLayanan').value   = kartuEl.dataset.deskripsi;
            document.getElementById('inputDurasiLayanan').value      = kartuEl.dataset.durasi;

            document.getElementById('tombolBatal').style.display = 'inline-block';

            document.querySelector('.layanan-sidebar').scrollIntoView({ behavior: 'smooth' });
        }

        function simpanLayanan() {
            const nama      = document.getElementById('inputNamaLayanan').value.trim();
            const tarif     = document.getElementById('inputTarifLayanan').value.trim();
            const satuan    = document.getElementById('inputSatuanLayanan').value;
            const deskripsi = document.getElementById('inputDeskripsiLayanan').value.trim();
            const durasi    = document.getElementById('inputDurasiLayanan').value.trim();

            if (!nama || !tarif) {
                alert('Nama layanan dan tarif wajib diisi.');
                return;
            }

            const tarifFmt = 'Rp ' + parseInt(tarif).toLocaleString('id-ID') + ' / ' + satuan;

            if (modeEdit && idEditAktif) {
                const kartuEl = document.querySelector(`.kartu-layanan-admin[data-id="${idEditAktif}"]`);
                if (kartuEl) {
                    kartuEl.dataset.nama      = nama;
                    kartuEl.dataset.tarif     = tarif;
                    kartuEl.dataset.satuan    = satuan;
                    kartuEl.dataset.deskripsi = deskripsi;
                    kartuEl.dataset.durasi    = durasi;

                    kartuEl.querySelector('.kartu-layanan-admin-nama').textContent    = nama;
                    kartuEl.querySelector('.kartu-layanan-admin-tarif').textContent   = tarifFmt;
                    kartuEl.querySelector('.kartu-layanan-admin-deskripsi').textContent = deskripsi;
                }
            } else {
                const newId = Date.now();
                const kartuBaru = document.createElement('div');
                kartuBaru.className = 'kartu-layanan-admin';
                kartuBaru.dataset.id        = newId;
                kartuBaru.dataset.nama      = nama;
                kartuBaru.dataset.tarif     = tarif;
                kartuBaru.dataset.satuan    = satuan;
                kartuBaru.dataset.deskripsi = deskripsi;
                kartuBaru.dataset.durasi    = durasi;

                kartuBaru.innerHTML = `
                    <div class="kartu-layanan-admin-header">
                        <span class="kartu-layanan-admin-nama">${nama}</span>
                        <span class="kartu-layanan-admin-tarif">${tarifFmt}</span>
                    </div>
                    <div class="kartu-layanan-admin-body">
                        <p class="kartu-layanan-admin-deskripsi">${deskripsi || '—'}</p>
                        <div class="kartu-layanan-admin-detail">
                            <span class="badge-biru">${durasi || '—'}</span>
                        </div>
                    </div>
                    <div class="kartu-layanan-admin-aksi">
                        <button class="tombol-edit-layanan"
                                onclick="editLayanan(this.closest('.kartu-layanan-admin'))">Edit</button>
                        <button class="tombol-hapus-layanan"
                                onclick="hapusLayanan(this.closest('.kartu-layanan-admin'))">Hapus</button>
                    </div>
                `;
                document.getElementById('containerLayanan').appendChild(kartuBaru);
            }

            cekKosong();
            resetForm();
        }

        function hapusLayanan(kartuEl) {
            const nama = kartuEl.dataset.nama;
            if (!confirm(`Hapus layanan "${nama}"? Tindakan ini tidak dapat dibatalkan.`)) return;
            kartuEl.style.transition = 'opacity 0.3s, transform 0.3s';
            kartuEl.style.opacity    = '0';
            kartuEl.style.transform  = 'scale(0.95)';
            setTimeout(() => { kartuEl.remove(); cekKosong(); }, 300);
        }

        function resetForm() {
            modeEdit    = false;
            idEditAktif = null;
            document.getElementById('judulFormLayanan').textContent = 'Tambah Layanan';
            document.getElementById('inputNamaLayanan').value       = '';
            document.getElementById('inputTarifLayanan').value      = '';
            document.getElementById('inputSatuanLayanan').value     = 'kg';
            document.getElementById('inputDeskripsiLayanan').value  = '';
            document.getElementById('inputDurasiLayanan').value     = '';
            document.getElementById('tombolBatal').style.display    = 'none';
        }

        function cekKosong() {
            const ada = document.querySelectorAll('.kartu-layanan-admin').length > 0;
            document.getElementById('layananKosong').style.display = ada ? 'none' : 'block';
        }

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kelola Layanan - CleanCo Admin</title>
    <link rel="stylesheet" href="../assets/css/style.css">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Bricolage+Grotesque:opsz,wght@12..96,200..800&display=swap" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=DM+Sans:ital,opsz,wght@0,9..40,100..1000;1,9..40,100..1000&display=swap" rel="stylesheet">
    <script src="../assets/js/layanan-admin.js"></script>
</body>
</html>

    <?php include '../includes/header-admin.php'; ?>

    <section class="halaman-layanan">

        <div class="layanan-sidebar">

            <h2 class="judul-sidebar" id="judulFormLayanan">Tambah Layanan</h2>
            <div class="form-layanan" id="formLayanan">

                <div class="grup-input-form">
                    <label class="label-form">Nama Layanan</label>
                    <input type="text"
                           class="input-form"
                           id="inputNamaLayanan"
                           name="nama"
                           placeholder="cth: Reguler, Express...">
                </div>

                <div class="grup-input-form">
                    <label class="label-form">Tarif</label>
                    <div class="input-tarif-wrapper">
                        <span class="prefix-tarif">Rp</span>
                        <input type="number"
                               class="input-form input-tarif"
                               id="inputTarifLayanan"
                               name="tarif"
                               placeholder="0"
                               min="0"
                               step="500">
                    </div>
                </div>

                <div class="grup-input-form">
                    <label class="label-form">Satuan</label>
                    <select class="input-form" id="inputSatuanLayanan" name="satuan">
                        <option value="kg">per Kg</option>
                        <option value="item">per Item</option>
                    </select>
                </div>

                <div class="grup-input-form">
                    <label class="label-form">Deskripsi Singkat</label>
                    <input type="text"
                           class="input-form"
                           id="inputDeskripsiLayanan"
                           name="deskripsi"
                           placeholder="cth: Paket lengkap dan terjangkau">
                </div>

                <div class="grup-input-form">
                    <label class="label-form">Estimasi Durasi</label>
                    <input type="text"
                           class="input-form"
                           id="inputDurasiLayanan"
                           name="durasi"
                           placeholder="cth: 1-2 hari, 6-8 jam">
                </div>

                <div class="tombol-form-layanan">
                    <button class="tombol-submit-form" id="tombolSimpan" onclick="simpanLayanan()">
                        Simpan
                    </button>
                    <button class="tombol-batal-layanan" id="tombolBatal"
                            onclick="resetForm()" style="display:none;">
                        Batal
                    </button>
                </div>

            </div>
        </div>

        <div class="layanan-kanan">

            <div class="layanan-kanan-header">
                <h2 class="judul-layanan-kanan">Daftar Layanan</h2>
                <p class="subjudul-layanan-kanan">
                    Perubahan di sini otomatis memengaruhi halaman publik dan form pemesanan member.
                </p>
            </div>

            <div class="kartu-layanan-admin-container" id="containerLayanan">

                <div class="kartu-layanan-admin"
                     data-id="1"
                     data-nama="Reguler"
                     data-tarif="8000"
                     data-satuan="kg"
                     data-deskripsi="Paket lengkap dan terjangkau"
                     data-durasi="1-2 hari">

                    <div class="kartu-layanan-admin-header">
                        <span class="kartu-layanan-admin-nama">Reguler</span>
                        <span class="kartu-layanan-admin-tarif">Rp 8.000 / kg</span>
                    </div>

                    <div class="kartu-layanan-admin-body">
                        <p class="kartu-layanan-admin-deskripsi">Paket lengkap dan terjangkau</p>
                        <div class="kartu-layanan-admin-detail">
                            <span class="badge-hijau">Cuci</span>
                            <span class="badge-hijau">Kering</span>
                            <span class="badge-hijau">Setrika</span>
                            <span class="badge-biru">1-2 hari</span>
                        </div>
                    </div>

                    <div class="kartu-layanan-admin-aksi">
                        <button class="tombol-edit-layanan"
                                onclick="editLayanan(this.closest('.kartu-layanan-admin'))">
                            Edit
                        </button>

                        <button class="tombol-hapus-layanan"
                                onclick="hapusLayanan(this.closest('.kartu-layanan-admin'))">
                            Hapus
                        </button>
                    </div>

                </div>

                <div class="kartu-layanan-admin"
                     data-id="2"
                     data-nama="Express"
                     data-tarif="15000"
                     data-satuan="kg"
                     data-deskripsi="Paket cepat selesai di hari yang sama"
                     data-durasi="6-8 jam">

                    <div class="kartu-layanan-admin-header kartu-layanan-admin-header-featured">
                        <span class="kartu-layanan-admin-nama">Express</span>
                        <span class="kartu-layanan-admin-tarif">Rp 15.000 / kg</span>
                    </div>

                    <div class="kartu-layanan-admin-body">
                        <p class="kartu-layanan-admin-deskripsi">Paket cepat selesai di hari yang sama</p>
                        <div class="kartu-layanan-admin-detail">
                            <span class="badge-hijau">Cuci</span>
                            <span class="badge-hijau">Kering</span>
                            <span class="badge-hijau">Setrika</span>
                            <span class="badge-biru">6-8 jam</span>
                        </div>
                    </div>

                    <div class="kartu-layanan-admin-aksi">
                        <button class="tombol-edit-layanan"
                                onclick="editLayanan(this.closest('.kartu-layanan-admin'))">
                            Edit
                        </button>
                        <button class="tombol-hapus-layanan"
                                onclick="hapusLayanan(this.closest('.kartu-layanan-admin'))">
                            Hapus
                        </button>
                    </div>

                </div>

                <div class="kartu-layanan-admin"
                     data-id="3"
                     data-nama="Dry Cleaning"
                     data-tarif="25000"
                     data-satuan="item"
                     data-deskripsi="Perawatan khusus pakaian formal"
                     data-durasi="1-2 hari">

                    <div class="kartu-layanan-admin-header">
                        <span class="kartu-layanan-admin-nama">Dry Cleaning</span>
                        <span class="kartu-layanan-admin-tarif">Rp 25.000 / item</span>
                    </div>

                    <div class="kartu-layanan-admin-body">
                        <p class="kartu-layanan-admin-deskripsi">Perawatan khusus pakaian formal</p>
                        <div class="kartu-layanan-admin-detail">
                            <span class="badge-hijau">Dry Clean</span>
                            <span class="badge-biru">1-2 hari</span>
                        </div>
                    </div>

                    <div class="kartu-layanan-admin-aksi">
                        <button class="tombol-edit-layanan"
                                onclick="editLayanan(this.closest('.kartu-layanan-admin'))">
                            Edit
                        </button>
                        <button class="tombol-hapus-layanan"
                                onclick="hapusLayanan(this.closest('.kartu-layanan-admin'))">
                            Hapus
                        </button>
                    </div>

                </div>

            </div>

            <div class="layanan-kosong" id="layananKosong" style="display:none;">
                <p>Belum ada layanan. Tambah layanan baru di panel kiri.</p>
            </div>

        </div>

    </section>

</body>
</html>
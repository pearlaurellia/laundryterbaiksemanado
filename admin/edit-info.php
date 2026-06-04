<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Info Website - CleanCo Admin</title>
    <link rel="stylesheet" href="../assets/css/style.css">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Bricolage+Grotesque:opsz,wght@12..96,200..800&display=swap" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=DM+Sans:ital,opsz,wght@0,9..40,100..1000;1,9..40,100..1000&display=swap" rel="stylesheet">
</head>
<body>

    <?php include '../includes/header-admin.php'; ?>

    <!--
    ============================================================
     BACKEND OVERVIEW — admin/edit-info.php
     Tabel DB: info_website (id, key, value, updated_at)
     Kolom key yang dibutuhkan:
       wa_admin, jam_buka, jam_tutup, alamat_outlet,
       kecamatan_layanan (JSON array), catatan_kurir

     GET  → tampilkan semua value dari tabel info_website
     POST ?action=simpan_kontak   → UPDATE wa_admin
     POST ?action=simpan_jam      → UPDATE jam_buka, jam_tutup
     POST ?action=simpan_alamat   → UPDATE alamat_outlet
     POST ?action=simpan_kurir    → UPDATE kecamatan_layanan,
                                           catatan_kurir

     Semua perubahan langsung memengaruhi:
       - index.php (nomor WA click-to-chat)
       - kontak.php (alamat, jam, WA)
       - member/pesan.php (daftar kecamatan dropdown)
    ============================================================
    -->

    <section class="halaman-layanan">

        <!-- ── SIDEBAR KIRI ── -->
        <div class="layanan-sidebar edit-info-sidebar">

            <h2 class="judul-sidebar">Edit Info Website</h2>
            <p class="edit-info-sidebar-sub">
                Perubahan di sini langsung memengaruhi halaman publik dan
                form pemesanan member.
            </p>

            <!-- Navigasi antar seksi -->
            <nav class="edit-info-nav">
                <a href="#seksiKontak"  class="edit-info-nav-item aktif-nav"
                   onclick="aktifkanNav(this)">
                    📞 Kontak
                </a>
                <a href="#seksiJam"     class="edit-info-nav-item"
                   onclick="aktifkanNav(this)">
                    🕐 Jam Operasional
                </a>
                <a href="#seksiAlamat"  class="edit-info-nav-item"
                   onclick="aktifkanNav(this)">
                    📍 Alamat Outlet
                </a>
                <a href="#seksiKurir"   class="edit-info-nav-item"
                   onclick="aktifkanNav(this)">
                    🛵 Layanan Kurir
                </a>
            </nav>

            <!-- Dampak perubahan -->
            <div class="edit-info-dampak">
                <p class="edit-info-dampak-judul">Halaman yang terpengaruh:</p>
                <ul class="edit-info-dampak-list">
                    <li>index.php</li>
                    <li>kontak.php</li>
                    <li>member/pesan.php</li>
                </ul>
            </div>

            <div class="bulat-rekap-kecil"></div>
            <div class="bulat-rekap-besar"></div>
        </div>

        <!-- ── KANAN : FORM SEKSI ── -->
        <div class="layanan-kanan edit-info-kanan">


            <!-- ════════════════════════════════
                 SEKSI 1 : KONTAK
            ════════════════════════════════ -->
            <div class="edit-info-seksi" id="seksiKontak">

                <div class="edit-info-seksi-header">
                    <div>
                        <h2 class="judul-layanan-kanan">Informasi Kontak</h2>
                        <p class="subjudul-layanan-kanan">
                            Nomor WA ini digunakan sebagai tujuan
                            click-to-chat otomatis saat member memesan.
                        </p>
                    </div>
                    <button class="tombol-edit-profil"
                            id="tombolEditKontak"
                            onclick="toggleEditSeksi('kontak')">
                        Edit
                    </button>
                </div>

                <!--
                    BACKEND:
                    value="<?= htmlspecialchars($info['wa_admin']) ?>"
                    value="<?= htmlspecialchars($info['email_admin']) ?>"
                    POST ke edit-info.php?action=simpan_kontak
                -->
                <div class="edit-info-form" id="formKontak">

                    <div class="grup-input-form">
                        <label class="label-profil">
                            Nomor WhatsApp Admin (format: 628xxx)
                        </label>
                        <input type="text"
                               class="input-profil input-readonly"
                               id="inputWaAdmin"
                               name="wa_admin"
                               value="6281234567890"
                               readonly>
                        <p class="edit-info-hint">
                            * Format tanpa tanda + dan tanpa spasi.
                            Contoh: 628123456789
                        </p>
                    </div>

                    <div class="grup-input-form">
                        <label class="label-profil">Email Bisnis</label>
                        <input type="email"
                               class="input-profil input-readonly"
                               id="inputEmailAdmin"
                               name="email_admin"
                               value="cleanco@gmail.com"
                               readonly>
                    </div>

                    <div class="edit-info-tombol-simpan"
                         id="simpanKontak" style="display:none;">
                        <button class="tombol-submit-form"
                                onclick="simpanSeksi('kontak')">
                            Simpan Kontak
                        </button>
                        <button class="tombol-batal-layanan"
                                style="display:inline-block;"
                                onclick="batalEditSeksi('kontak')">
                            Batal
                        </button>
                    </div>

                </div>

            </div>
            <!-- /seksi kontak -->


            <div class="edit-info-divider"></div>


            <!-- ════════════════════════════════
                 SEKSI 2 : JAM OPERASIONAL
            ════════════════════════════════ -->
            <div class="edit-info-seksi" id="seksiJam">

                <div class="edit-info-seksi-header">
                    <div>
                        <h2 class="judul-layanan-kanan">Jam Operasional</h2>
                        <p class="subjudul-layanan-kanan">
                            Ditampilkan di halaman kontak.php.
                        </p>
                    </div>
                    <button class="tombol-edit-profil"
                            id="tombolEditJam"
                            onclick="toggleEditSeksi('jam')">
                        Edit
                    </button>
                </div>

                <!--
                    BACKEND:
                    value="<?= $info['jam_buka'] ?>"
                    value="<?= $info['jam_tutup'] ?>"
                    value="<?= $info['hari_operasional'] ?>"
                    POST ke edit-info.php?action=simpan_jam
                -->
                <div class="edit-info-form" id="formJam">

                    <div class="edit-info-form-grid">
                        <div class="grup-input-form">
                            <label class="label-profil">Jam Buka</label>
                            <input type="time"
                                   class="input-profil input-readonly"
                                   id="inputJamBuka"
                                   name="jam_buka"
                                   value="08:00"
                                   readonly>
                        </div>
                        <div class="grup-input-form">
                            <label class="label-profil">Jam Tutup</label>
                            <input type="time"
                                   class="input-profil input-readonly"
                                   id="inputJamTutup"
                                   name="jam_tutup"
                                   value="21:00"
                                   readonly>
                        </div>
                    </div>

                    <div class="grup-input-form">
                        <label class="label-profil">Hari Operasional</label>
                        <input type="text"
                               class="input-profil input-readonly"
                               id="inputHariOperasional"
                               name="hari_operasional"
                               value="Senin – Sabtu"
                               readonly>
                    </div>

                    <div class="grup-input-form">
                        <label class="label-profil">
                            Catatan Jam (opsional)
                        </label>
                        <input type="text"
                               class="input-profil input-readonly"
                               id="inputCatatanJam"
                               name="catatan_jam"
                               value="Minggu & hari libur nasional tutup"
                               readonly>
                    </div>

                    <div class="edit-info-tombol-simpan"
                         id="simpanJam" style="display:none;">
                        <button class="tombol-submit-form"
                                onclick="simpanSeksi('jam')">
                            Simpan Jam
                        </button>
                        <button class="tombol-batal-layanan"
                                style="display:inline-block;"
                                onclick="batalEditSeksi('jam')">
                            Batal
                        </button>
                    </div>

                </div>

            </div>
            <!-- /seksi jam -->


            <div class="edit-info-divider"></div>


            <!-- ════════════════════════════════
                 SEKSI 3 : ALAMAT OUTLET
            ════════════════════════════════ -->
            <div class="edit-info-seksi" id="seksiAlamat">

                <div class="edit-info-seksi-header">
                    <div>
                        <h2 class="judul-layanan-kanan">Alamat Outlet</h2>
                        <p class="subjudul-layanan-kanan">
                            Ditampilkan di kontak.php dan sebagai
                            tujuan untuk member yang ambil sendiri.
                        </p>
                    </div>
                    <button class="tombol-edit-profil"
                            id="tombolEditAlamat"
                            onclick="toggleEditSeksi('alamat')">
                        Edit
                    </button>
                </div>

                <!--
                    BACKEND:
                    value="<?= $info['nama_outlet'] ?>"
                    value="<?= $info['alamat_outlet'] ?>"
                    value="<?= $info['kecamatan_outlet'] ?>"
                    POST ke edit-info.php?action=simpan_alamat
                -->
                <div class="edit-info-form" id="formAlamat">

                    <div class="grup-input-form">
                        <label class="label-profil">Nama Outlet</label>
                        <input type="text"
                               class="input-profil input-readonly"
                               id="inputNamaOutlet"
                               name="nama_outlet"
                               value="CleanCo Laundry"
                               readonly>
                    </div>

                    <div class="grup-input-form">
                        <label class="label-profil">Alamat Lengkap</label>
                        <input type="text"
                               class="input-profil input-readonly"
                               id="inputAlamatOutlet"
                               name="alamat_outlet"
                               value="Wanea, Teling Atas, Jln. Manado"
                               readonly>
                    </div>

                    <div class="grup-input-form">
                        <label class="label-profil">Kecamatan</label>
                        <input type="text"
                               class="input-profil input-readonly"
                               id="inputKecamatanOutlet"
                               name="kecamatan_outlet"
                               value="Wanea"
                               readonly>
                    </div>

                    <div class="grup-input-form">
                        <label class="label-profil">
                            Link Google Maps (opsional)
                        </label>
                        <input type="url"
                               class="input-profil input-readonly"
                               id="inputMapsOutlet"
                               name="maps_outlet"
                               value="https://maps.google.com/?q=..."
                               readonly>
                        <p class="edit-info-hint">
                            * Ditampilkan sebagai tombol "Lihat di Maps"
                            di kontak.php
                        </p>
                    </div>

                    <div class="edit-info-tombol-simpan"
                         id="simpanAlamat" style="display:none;">
                        <button class="tombol-submit-form"
                                onclick="simpanSeksi('alamat')">
                            Simpan Alamat
                        </button>
                        <button class="tombol-batal-layanan"
                                style="display:inline-block;"
                                onclick="batalEditSeksi('alamat')">
                            Batal
                        </button>
                    </div>

                </div>

            </div>
            <!-- /seksi alamat -->


            <div class="edit-info-divider"></div>


            <!-- ════════════════════════════════
                 SEKSI 4 : LAYANAN KURIR
            ════════════════════════════════ -->
            <div class="edit-info-seksi" id="seksiKurir">

                <div class="edit-info-seksi-header">
                    <div>
                        <h2 class="judul-layanan-kanan">Layanan Kurir</h2>
                        <p class="subjudul-layanan-kanan">
                            Kecamatan yang aktif muncul sebagai pilihan
                            dropdown di form pesanan member.
                        </p>
                    </div>
                    <button class="tombol-edit-profil"
                            id="tombolEditKurir"
                            onclick="toggleEditSeksi('kurir')">
                        Edit
                    </button>
                </div>

                <!--
                    BACKEND:
                    Kecamatan disimpan sebagai JSON array di DB:
                    ["Wanea","Malalayang","Tikala", ...]
                    Saat ditampilkan, loop dan buat checkbox per kecamatan.
                    POST ke edit-info.php?action=simpan_kurir
                    Field: kecamatan[] (array checkbox), biaya_kurir, catatan_kurir
                -->
                <div class="edit-info-form" id="formKurir">

                    <div class="grup-input-form">
                        <label class="label-profil">
                            Biaya Kurir Flat (Rp)
                        </label>
                        <input type="number"
                               class="input-profil input-readonly"
                               id="inputBiayaKurir"
                               name="biaya_kurir"
                               value="10000"
                               min="0" step="500"
                               readonly>
                        <p class="edit-info-hint">
                            * Biaya ini berlaku sama untuk semua
                            kecamatan yang dilayani.
                        </p>
                    </div>

                    <!-- Kecamatan yang dilayani -->
                    <div class="grup-input-form">
                        <label class="label-profil">
                            Kecamatan yang Dilayani
                        </label>
                        <!--
                            BACKEND NOTE:
                            Saat mode edit, render sebagai checkbox.
                            Saat mode readonly, render sebagai pills.
                            Contoh PHP untuk mode readonly:
                            foreach($kecamatan as $k) {
                              echo '<span class="pill-kecamatan">' . $k . '</span>';
                            }
                        -->
                        <div class="kecamatan-pills" id="kecamatanPills">
                            <span class="pill-kecamatan">Wanea</span>
                            <span class="pill-kecamatan">Malalayang</span>
                            <span class="pill-kecamatan">Tikala</span>
                            <span class="pill-kecamatan">Mapanget</span>
                            <span class="pill-kecamatan">Tuminting</span>
                            <span class="pill-kecamatan">Bunaken</span>
                            <span class="pill-kecamatan">Wenang</span>
                            <span class="pill-kecamatan">Paal Dua</span>
                            <span class="pill-kecamatan">Singkil</span>
                            <span class="pill-kecamatan">Sario</span>
                        </div>

                        <!-- Checkbox muncul saat mode edit -->
                        <div class="kecamatan-checkboxes"
                             id="kecamatanCheckboxes"
                             style="display:none;">
                            <!--
                                BACKEND: foreach semua kecamatan kota,
                                centang yang sudah ada di DB.
                            -->
                            <label class="checkbox-kecamatan">
                                <input type="checkbox"
                                       name="kecamatan[]"
                                       value="Wanea" checked> Wanea
                            </label>
                            <label class="checkbox-kecamatan">
                                <input type="checkbox"
                                       name="kecamatan[]"
                                       value="Malalayang" checked> Malalayang
                            </label>
                            <label class="checkbox-kecamatan">
                                <input type="checkbox"
                                       name="kecamatan[]"
                                       value="Tikala" checked> Tikala
                            </label>
                            <label class="checkbox-kecamatan">
                                <input type="checkbox"
                                       name="kecamatan[]"
                                       value="Mapanget" checked> Mapanget
                            </label>
                            <label class="checkbox-kecamatan">
                                <input type="checkbox"
                                       name="kecamatan[]"
                                       value="Tuminting" checked> Tuminting
                            </label>
                            <label class="checkbox-kecamatan">
                                <input type="checkbox"
                                       name="kecamatan[]"
                                       value="Bunaken" checked> Bunaken
                            </label>
                            <label class="checkbox-kecamatan">
                                <input type="checkbox"
                                       name="kecamatan[]"
                                       value="Wenang" checked> Wenang
                            </label>
                            <label class="checkbox-kecamatan">
                                <input type="checkbox"
                                       name="kecamatan[]"
                                       value="Paal Dua" checked> Paal Dua
                            </label>
                            <label class="checkbox-kecamatan">
                                <input type="checkbox"
                                       name="kecamatan[]"
                                       value="Singkil" checked> Singkil
                            </label>
                            <label class="checkbox-kecamatan">
                                <input type="checkbox"
                                       name="kecamatan[]"
                                       value="Sario" checked> Sario
                            </label>
                            <label class="checkbox-kecamatan">
                                <input type="checkbox"
                                       name="kecamatan[]"
                                       value="Paal Empat"> Paal Empat
                            </label>
                            <label class="checkbox-kecamatan">
                                <input type="checkbox"
                                       name="kecamatan[]"
                                       value="Molas"> Molas
                            </label>
                        </div>
                    </div>

                    <div class="grup-input-form">
                        <label class="label-profil">
                            Catatan untuk Member
                        </label>
                        <input type="text"
                               class="input-profil input-readonly"
                               id="inputCatatanKurir"
                               name="catatan_kurir"
                               value="Kurir akan menghubungi kamu via WhatsApp sebelum menjemput"
                               readonly>
                        <p class="edit-info-hint">
                            * Teks ini muncul di form pesanan member
                            saat memilih opsi kurir.
                        </p>
                    </div>

                    <div class="edit-info-tombol-simpan"
                         id="simpanKurir" style="display:none;">
                        <button class="tombol-submit-form"
                                onclick="simpanSeksi('kurir')">
                            Simpan Pengaturan Kurir
                        </button>
                        <button class="tombol-batal-layanan"
                                style="display:inline-block;"
                                onclick="batalEditSeksi('kurir')">
                            Batal
                        </button>
                    </div>

                </div>

            </div>
            <!-- /seksi kurir -->


        </div>
        <!-- /edit-info-kanan -->

    </section>


    <!-- Pop-up berhasil simpan -->
    <div class="overlay-popup" id="overlayPopup" style="display:none;"></div>
    <div class="popup-konfirmasi" id="popupBerhasil" style="display:none;">
        <h3 class="popup-judul" id="popupBerhasilJudul">Berhasil Disimpan!</h3>
        <p class="popup-teks" id="popupBerhasilTeks">
            Perubahan telah disimpan dan langsung aktif.
        </p>
        <div class="popup-tombol-group" style="justify-content:center;">
            <button class="popup-tombol-konfirm"
                    style="background-color:#52c49c; color:#1a4d3a;"
                    onclick="tutupPopupBerhasil()">
                OK
            </button>
        </div>
    </div>

    <script src="../assets/js/form-validation.js"></script>

</body>
</html>
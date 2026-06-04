<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Profil Saya - CleanCo</title>
    <link rel="stylesheet" href="../assets/css/style.css">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Bricolage+Grotesque:opsz,wght@12..96,200..800&display=swap" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=DM+Sans:ital,opsz,wght@0,9..40,100..1000;1,9..40,100..1000&display=swap" rel="stylesheet">
</head>
<body>

    <?php include '../includes/header-member.php'; ?>

    <!--
    ============================================================
     BACKEND OVERVIEW — member/profil.php
     GET  → tampilkan data profil dari $_SESSION['user_id']
     POST ?action=edit_profil → UPDATE users SET nama=?, no_hp=? WHERE id=?
     POST ?action=ganti_password → UPDATE users SET password=? WHERE id=?
                                   (validasi: password_lama cocok dulu)
    ============================================================
    -->

    <section class="profil-section">

        <!-- ── HEADER PROFIL ── -->
        <div class="profil-hero">
            <div class="profil-avatar">
                <!--
                    BACKEND: Ambil inisial dari nama member.
                    <?= strtoupper(substr($user['nama'], 0, 1)) ?>
                -->
                R
            </div>
            <div class="profil-hero-teks">
                <!--
                    BACKEND: <?= htmlspecialchars($user['nama']) ?>
                             <?= htmlspecialchars($user['username']) ?>
                             <?= $user['created_at'] ?>
                -->
                <h1 class="profil-nama">Ryan Liam</h1>
                <p class="profil-username">@liam999</p>
                <p class="profil-bergabung">Member sejak April 2025</p>
            </div>
            <!-- Stat singkat -->
            <div class="profil-stat-group">
                <div class="profil-stat">
                    <!-- BACKEND: COUNT pesanan member ini -->
                    <span class="profil-stat-angka">5</span>
                    <span class="profil-stat-label">Total Pesanan</span>
                </div>
                <div class="profil-stat">
                    <!-- BACKEND: COUNT pesanan status = selesai -->
                    <span class="profil-stat-angka">4</span>
                    <span class="profil-stat-label">Selesai</span>
                </div>
                <div class="profil-stat">
                    <!-- BACKEND: SUM total_harga status = lunas -->
                    <span class="profil-stat-angka">185rb</span>
                    <span class="profil-stat-label">Total Belanja</span>
                </div>
            </div>

            <!-- Dekorasi lingkaran -->
            <div class="bulat-atas" style="top:20%; right:3%;"></div>
            <div class="bulat-ditengah" style="bottom:10%; right:15%;"></div>
            <div class="bulat-besar" style="right:-20px; bottom:-70px;">
                <h2>CleanCo</h2>
            </div>
        </div>

        <!-- ── KONTEN BAWAH ── -->
        <div class="profil-konten">

            <!-- KOLOM KIRI: Form edit info -->
            <div class="profil-kolom">
                <div class="profil-kartu">
                    <div class="profil-kartu-header">
                        <h3 class="profil-kartu-judul">Informasi Akun</h3>
                        <button class="tombol-edit-profil"
                                id="tombolEditProfil"
                                onclick="toggleEditProfil()">
                            Edit
                        </button>
                    </div>

                    <!--
                        BACKEND NOTE:
                        Form ini POST ke profil.php?action=edit_profil
                        Field: nama, no_hp
                        Backend UPDATE users SET nama=?, no_hp=? WHERE id=?
                        Validasi: no_hp hanya angka, panjang 10-13 digit
                    -->
                    <div class="grup-input-form" style="margin-top:4px;">
                        <label class="label-profil">Username</label>
                        <!--
                            BACKEND: value="<?= $user['username'] ?>"
                            Username tidak bisa diubah (readonly selalu)
                        -->
                        <input type="text" class="input-profil input-readonly"
                               value="@liam999" readonly>
                    </div>

                    <div class="grup-input-form">
                        <label class="label-profil">Nama Lengkap</label>
                        <!--
                            BACKEND: value="<?= htmlspecialchars($user['nama']) ?>"
                        -->
                        <input type="text" class="input-profil"
                               id="inputNamaProfil"
                               name="nama"
                               value="Ryan Liam Santoso"
                               readonly>
                    </div>

                    <div class="grup-input-form">
                        <label class="label-profil">Email</label>
                        <!-- Email tidak bisa diubah (identitas login) -->
                        <input type="email" class="input-profil input-readonly"
                               value="ryanl9@gmail.com" readonly>
                    </div>

                    <div class="grup-input-form">
                        <label class="label-profil">Nomor WhatsApp</label>
                        <!--
                            BACKEND: value="<?= $user['no_hp'] ?>"
                        -->
                        <input type="tel" class="input-profil"
                               id="inputNoHP"
                               name="no_hp"
                               value="0834545827"
                               readonly>
                    </div>

                    <!-- Tombol simpan (tersembunyi saat tidak edit) -->
                    <div id="tombolSimpanProfil" style="display:none;">
                        <button class="tombol-submit-form"
                                onclick="simpanProfil()">
                            Simpan Perubahan
                        </button>
                        <button class="tombol-batal-layanan"
                                style="display:inline-block; margin-left:10px;"
                                onclick="batalEditProfil()">
                            Batal
                        </button>
                    </div>

                </div>
            </div>

            <!-- KOLOM KANAN: Ganti password -->
            <div class="profil-kolom">
                <div class="profil-kartu">
                    <div class="profil-kartu-header">
                        <h3 class="profil-kartu-judul">Ganti Password</h3>
                    </div>

                    <!--
                        BACKEND NOTE:
                        Form POST ke profil.php?action=ganti_password
                        Field: password_lama, password_baru, konfirmasi_password
                        Backend:
                          1. Verifikasi password_lama cocok dengan hash di DB
                          2. Validasi password_baru == konfirmasi_password
                          3. Validasi panjang min 8 karakter
                          4. UPDATE users SET password = password_hash(password_baru)
                             WHERE id = $_SESSION['user_id']
                    -->

                    <div class="grup-input-form" style="margin-top:4px;">
                        <label class="label-profil">Password Saat Ini</label>
                        <input type="password" class="input-profil"
                               id="inputPasswordLama"
                               name="password_lama"
                               placeholder="Masukkan password saat ini">
                    </div>

                    <div class="grup-input-form">
                        <label class="label-profil">Password Baru</label>
                        <input type="password" class="input-profil"
                               id="inputPasswordBaru"
                               name="password_baru"
                               placeholder="Min. 8 karakter"
                               oninput="cekKuatPassword(this.value)">
                        <!-- Indikator kuat password -->
                        <div class="kuat-password-wrapper" id="kuatPasswordWrapper"
                             style="display:none;">
                            <div class="kuat-password-bar">
                                <div class="kuat-password-isi" id="kuatPasswordIsi"></div>
                            </div>
                            <span class="kuat-password-label" id="kuatPasswordLabel"></span>
                        </div>
                    </div>

                    <div class="grup-input-form">
                        <label class="label-profil">Konfirmasi Password Baru</label>
                        <input type="password" class="input-profil"
                               id="inputKonfirmasiPassword"
                               name="konfirmasi_password"
                               placeholder="Ulangi password baru"
                               oninput="cekKonfirmasi()">
                        <p class="pesan-konfirmasi" id="pesanKonfirmasi"></p>
                    </div>

                    <button class="tombol-submit-form"
                            onclick="gantiPassword()">
                        Ganti Password
                    </button>

                </div>

                <!-- Info alamat (readonly) -->
                <div class="profil-kartu" style="margin-top:20px;">
                    <div class="profil-kartu-header">
                        <h3 class="profil-kartu-judul">Alamat Tersimpan</h3>
                        <span class="profil-kartu-note">
                            Diperbarui otomatis dari pesanan terakhir
                        </span>
                    </div>
                    <div class="grup-input-form" style="margin-top:8px;">
                        <label class="label-profil">Kecamatan</label>
                        <!-- BACKEND: <?= $user['kecamatan'] ?? '—' ?> -->
                        <input type="text" class="input-profil input-readonly"
                               value="Wanea" readonly>
                    </div>
                    <div class="grup-input-form">
                        <label class="label-profil">Alamat Lengkap</label>
                        <!-- BACKEND: <?= $user['alamat'] ?? '—' ?> -->
                        <input type="text" class="input-profil input-readonly"
                               value="Jl. Paal 4 No. 12, Ling. III" readonly>
                    </div>
                    <p class="profil-kartu-note" style="margin-top:8px;">
                        * Alamat diisi otomatis dari pesanan kurir terakhir kamu.
                    </p>
                </div>

            </div>

        </div>

    </section>

    <!-- Pop-up berhasil simpan profil -->
    <div class="overlay-popup" id="overlayPopup" style="display:none;"></div>
    <div class="popup-konfirmasi" id="popupBerhasil" style="display:none;">
        <h3 class="popup-judul" id="popupBerhasilJudul">Berhasil!</h3>
        <p class="popup-teks" id="popupBerhasilTeks">Perubahan telah disimpan.</p>
        <div class="popup-tombol-group" style="justify-content:center;">
            <button class="popup-tombol-konfirm"
                    style="background-color:#52c49c; color:#1a4d3a;"
                    onclick="tutupPopupBerhasil()">
                OK
            </button>
        </div>
    </div>

<script src="../assets/js/profil-member.js"></script>

</body>
</html>
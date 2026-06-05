<?php
require_once '../config/session.php';
require_once '../config/database.php';
require_once '../config/functions.php';

// Proteksi halaman: Pastikan sudah login dan rolenya adalah admin
if (!isset($_SESSION['id_user']) || $_SESSION['role'] !== 'admin') {
    redirect('../login.php');
}

// PROSES BACKEND: Menerima POST Request untuk Update Data (Menggunakan PDO)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_GET['action'])) {
    $action = $_GET['action'];
    $success = false;

    if ($action === 'simpan_kontak') {
        $wa_admin = $_POST['wa_admin'];
        $email_admin = $_POST['email_admin'];
        
        $stmt1 = $pdo->prepare("UPDATE info_website SET value = ? WHERE `key` = 'wa_admin'");
        $stmt1->execute([$wa_admin]);
        
        $stmt2 = $pdo->prepare("UPDATE info_website SET value = ? WHERE `key` = 'email_admin'");
        $stmt2->execute([$email_admin]);
        $success = true;
    } 
    elseif ($action === 'simpan_jam') {
        $jam_buka = $_POST['jam_buka'];
        $jam_tutup = $_POST['jam_tutup'];
        $hari_operasional = $_POST['hari_operasional'];
        $catatan_jam = $_POST['catatan_jam'];

        $pdo->prepare("UPDATE info_website SET value = ? WHERE `key` = 'jam_buka'")->execute([$jam_buka]);
        $pdo->prepare("UPDATE info_website SET value = ? WHERE `key` = 'jam_tutup'")->execute([$jam_tutup]);
        $pdo->prepare("UPDATE info_website SET value = ? WHERE `key` = 'hari_operasional'")->execute([$hari_operasional]);
        $pdo->prepare("UPDATE info_website SET value = ? WHERE `key` = 'catatan_jam'")->execute([$catatan_jam]);
        $success = true;
    } 
    elseif ($action === 'simpan_alamat') {
        $nama_outlet = $_POST['nama_outlet'];
        $alamat_outlet = $_POST['alamat_outlet'];
        $kecamatan_outlet = $_POST['kecamatan_outlet'];
        $maps_outlet = $_POST['maps_outlet'];

        $pdo->prepare("UPDATE info_website SET value = ? WHERE `key` = 'nama_outlet'")->execute([$nama_outlet]);
        $pdo->prepare("UPDATE info_website SET value = ? WHERE `key` = 'alamat_outlet'")->execute([$alamat_outlet]);
        $pdo->prepare("UPDATE info_website SET value = ? WHERE `key` = 'kecamatan_outlet'")->execute([$kecamatan_outlet]);
        $pdo->prepare("UPDATE info_website SET value = ? WHERE `key` = 'maps_outlet'")->execute([$maps_outlet]);
        $success = true;
    } 
    elseif ($action === 'simpan_kurir') {
        $biaya_kurir = $_POST['biaya_kurir'];
        $catatan_kurir = $_POST['catatan_kurir'];
        
        $kecamatan_array = isset($_POST['kecamatan']) ? $_POST['kecamatan'] : [];
        $kecamatan_json = json_encode($kecamatan_array);

        $pdo->prepare("UPDATE info_website SET value = ? WHERE `key` = 'biaya_kurir'")->execute([$biaya_kurir]);
        $pdo->prepare("UPDATE info_website SET value = ? WHERE `key` = 'catatan_kurir'")->execute([$catatan_kurir]);
        $pdo->prepare("UPDATE info_website SET value = ? WHERE `key` = 'kecamatan_layanan'")->execute([$kecamatan_json]);
        $success = true;
    }

    if ($success) {
        header("Location: edit-info.php?status=sukses");
        exit;
    }
}

// FRONTEND GET DATA: Ambil data dengan PDO ke array asosiatif
$info = [];
$query_ambil = $pdo->query("SELECT * FROM info_website");
while ($row = $query_ambil->fetch()) {
    $info[$row['key']] = $row['value'];
}

$kecamatan_aktif = isset($info['kecamatan_layanan']) ? json_decode($info['kecamatan_layanan'], true) : [];
if (!is_array($kecamatan_aktif)) {
    $kecamatan_aktif = [];
}
?>
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

    <section class="halaman-layanan">

        <div class="layanan-sidebar edit-info-sidebar">
            <h2 class="judul-sidebar">Edit Info Website</h2>
            <p class="edit-info-sidebar-sub">
                Perubahan di sini langsung memengaruhi halaman publik dan form pemesanan member.
            </p>

            <nav class="edit-info-nav">
                <a href="#seksiKontak" class="edit-info-nav-item aktif-nav" onclick="aktifkanNav(this)">📞 Kontak</a>
                <a href="#seksiJam" class="edit-info-nav-item" onclick="aktifkanNav(this)">🕐 Jam Operasional</a>
                <a href="#seksiAlamat" class="edit-info-nav-item" onclick="aktifkanNav(this)">📍 Alamat Outlet</a>
                <a href="#seksiKurir" class="edit-info-nav-item" onclick="aktifkanNav(this)">🛵 Layanan Kurir</a>
            </nav>

            <div class="edit-info-dampak">
                <p class="edit-info-dampak-judul">Halaman yang terpengaruh:</p>
                <ul class="edit-info-dampak-list">
                    <li>index.php</li>
                    <li>kontak.php</li>
                    <li>member/pesan.php</li>
                </ul>
            </div>
        </div>

        <div class="layanan-kanan edit-info-kanan">

            <div class="edit-info-seksi" id="seksiKontak">
                <div class="edit-info-seksi-header">
                    <div>
                        <h2 class="judul-layanan-kanan">Informasi Kontak</h2>
                        <p class="subjudul-layanan-kanan">Nomor WA ini digunakan sebagai tujuan click-to-chat otomatis saat member memesan.</p>
                    </div>
                    <button class="tombol-edit-profil" id="tombolEditKontak" onclick="toggleEditSeksi('kontak')">Edit</button>
                </div>

                <form method="POST" action="edit-info.php?action=simpan_kontak" class="edit-info-form" id="formKontak">
                    <div class="grup-input-form">
                        <label class="label-profil">Nomor WhatsApp Admin (format: 628xxx)</label>
                        <input type="text" class="input-profil input-readonly" id="inputWaAdmin" name="wa_admin" value="<?= htmlspecialchars($info['wa_admin'] ?? '') ?>" readonly required>
                        <p class="edit-info-hint">* Format tanpa tanda + dan tanpa spasi. Contoh: 628123456789</p>
                    </div>

                    <div class="grup-input-form">
                        <label class="label-profil">Email Bisnis</label>
                        <input type="email" class="input-profil input-readonly" id="inputEmailAdmin" name="email_admin" value="<?= htmlspecialchars($info['email_admin'] ?? '') ?>" readonly required>
                    </div>

                    <div class="edit-info-tombol-simpan" id="simpanKontak" style="display:none;">
                        <button type="button" class="tombol-submit-form" onclick="simpanSeksi('kontak')">Simpan Kontak</button>
                        <button type="button" class="tombol-batal-layanan" style="display:inline-block;" onclick="batalEditSeksi('kontak')">Batal</button>
                    </div>
                </form>
            </div>

            <div class="edit-info-divider"></div>

            <div class="edit-info-seksi" id="seksiJam">
                <div class="edit-info-seksi-header">
                    <div>
                        <h2 class="judul-layanan-kanan">Jam Operasional</h2>
                        <p class="subjudul-layanan-kanan">Ditampilkan di halaman kontak.php.</p>
                    </div>
                    <button class="tombol-edit-profil" id="tombolEditJam" onclick="toggleEditSeksi('jam')">Edit</button>
                </div>

                <form method="POST" action="edit-info.php?action=simpan_jam" class="edit-info-form" id="formJam">
                    <div class="edit-info-form-grid">
                        <div class="grup-input-form">
                            <label class="label-profil">Jam Buka</label>
                            <input type="time" class="input-profil input-readonly" id="inputJamBuka" name="jam_buka" value="<?= htmlspecialchars($info['jam_buka'] ?? '08:00') ?>" readonly required>
                        </div>
                        <div class="grup-input-form">
                            <label class="label-profil">Jam Tutup</label>
                            <input type="time" class="input-profil input-readonly" id="inputJamTutup" name="jam_tutup" value="<?= htmlspecialchars($info['jam_tutup'] ?? '21:00') ?>" readonly required>
                        </div>
                    </div>

                    <div class="grup-input-form">
                        <label class="label-profil">Hari Operasional</label>
                        <input type="text" class="input-profil input-readonly" id="inputHariOperasional" name="hari_operasional" value="<?= htmlspecialchars($info['hari_operasional'] ?? 'Senin – Sabtu') ?>" readonly required>
                    </div>

                    <div class="grup-input-form">
                        <label class="label-profil">Catatan Jam (opsional)</label>
                        <input type="text" class="input-profil input-readonly" id="inputCatatanJam" name="catatan_jam" value="<?= htmlspecialchars($info['catatan_jam'] ?? '') ?>" readonly>
                    </div>

                    <div class="edit-info-tombol-simpan" id="simpanJam" style="display:none;">
                        <button type="button" class="tombol-submit-form" onclick="simpanSeksi('jam')">Simpan Jam</button>
                        <button type="button" class="tombol-batal-layanan" style="display:inline-block;" onclick="batalEditSeksi('jam')">Batal</button>
                    </div>
                </form>
            </div>

            <div class="edit-info-divider"></div>

            <div class="edit-info-seksi" id="seksiAlamat">
                <div class="edit-info-seksi-header">
                    <div>
                        <h2 class="judul-layanan-kanan">Alamat Outlet</h2>
                        <p class="subjudul-layanan-kanan">Ditampilkan di kontak.php dan sebagai tujuan untuk member yang ambil sendiri.</p>
                    </div>
                    <button class="tombol-edit-profil" id="tombolEditAlamat" onclick="toggleEditSeksi('alamat')">Edit</button>
                </div>

                <form method="POST" action="edit-info.php?action=simpan_alamat" class="edit-info-form" id="formAlamat">
                    <div class="grup-input-form">
                        <label class="label-profil">Nama Outlet</label>
                        <input type="text" class="input-profil input-readonly" id="inputNamaOutlet" name="nama_outlet" value="<?= htmlspecialchars($info['nama_outlet'] ?? '') ?>" readonly required>
                    </div>

                    <div class="grup-input-form">
                        <label class="label-profil">Alamat Lengkap</label>
                        <input type="text" class="input-profil input-readonly" id="inputAlamatOutlet" name="alamat_outlet" value="<?= htmlspecialchars($info['alamat_outlet'] ?? '') ?>" readonly required>
                    </div>

                    <div class="grup-input-form">
                        <label class="label-profil">Kecamatan</label>
                        <input type="text" class="input-profil input-readonly" id="inputKecamatanOutlet" name="kecamatan_outlet" value="<?= htmlspecialchars($info['kecamatan_outlet'] ?? '') ?>" readonly required>
                    </div>

                    <div class="grup-input-form">
                        <label class="label-profil">Link Google Maps (opsional)</label>
                        <input type="url" class="input-profil input-readonly" id="inputMapsOutlet" name="maps_outlet" value="<?= htmlspecialchars($info['maps_outlet'] ?? '') ?>" readonly>
                    </div>

                    <div class="edit-info-tombol-simpan" id="simpanAlamat" style="display:none;">
                        <button type="button" class="tombol-submit-form" onclick="simpanSeksi('alamat')">Simpan Alamat</button>
                        <button type="button" class="tombol-batal-layanan" style="display:inline-block;" onclick="batalEditSeksi('alamat')">Batal</button>
                    </div>
                </form>
            </div>

            <div class="edit-info-divider"></div>

            <div class="edit-info-seksi" id="seksiKurir">
                <div class="edit-info-seksi-header">
                    <div>
                        <h2 class="judul-layanan-kanan">Layanan Kurir</h2>
                        <p class="subjudul-layanan-kanan">Kecamatan yang aktif muncul sebagai pilihan dropdown di form pesanan member.</p>
                    </div>
                    <button class="tombol-edit-profil" id="tombolEditKurir" onclick="toggleEditSeksi('kurir')">Edit</button>
                </div>

                <form method="POST" action="edit-info.php?action=simpan_kurir" class="edit-info-form" id="formKurir">
                    <div class="grup-input-form">
                        <label class="label-profil">Biaya Kurir Flat (Rp)</label>
                        <input type="number" class="input-profil input-readonly" id="inputBiayaKurir" name="biaya_kurir" value="<?= htmlspecialchars($info['biaya_kurir'] ?? '0') ?>" min="0" step="500" readonly required>
                    </div>

                    <div class="grup-input-form">
                        <label class="label-profil">Kecamatan yang Dilayani</label>
                        <div class="kecamatan-pills" id="kecamatanPills">
                            <?php foreach ($kecamatan_aktif as $kec): ?>
                                <span class="pill-kecamatan"><?= htmlspecialchars($kec) ?></span>
                            <?php endforeach; ?>
                            <?php if (empty($kecamatan_aktif)): ?>
                                <span class="pill-kecamatan" style="background:#fee2e2; color:#dc2626;">Belum ada wilayah aktif</span>
                            <?php endif; ?>
                        </div>

                        <div class="kecamatan-checkboxes" id="kecamatanCheckboxes" style="display:none;">
                            <?php
                            $list_semua_kecamatan = ["Wanea", "Malalayang", "Tikala", "Mapanget", "Tuminting", "Bunaken", "Wenang", "Paal Dua", "Singkil", "Sario", "Paal Empat", "Molas"];
                            foreach ($list_semua_kecamatan as $kec):
                                $tercentang = in_array($kec, $kecamatan_aktif) ? 'checked' : '';
                            ?>
                                <label class="checkbox-kecamatan">
                                    <input type="checkbox" name="kecamatan[]" value="<?= $kec ?>" <?= $tercentang ?>> <?= $kec ?>
                                </label>
                            <?php endforeach; ?>
                        </div>
                    </div>

                    <div class="grup-input-form">
                        <label class="label-profil">Catatan untuk Member</label>
                        <input type="text" class="input-profil input-readonly" id="inputCatatanKurir" name="catatan_kurir" value="<?= htmlspecialchars($info['catatan_kurir'] ?? '') ?>" readonly>
                    </div>

                    <div class="edit-info-tombol-simpan" id="simpanKurir" style="display:none;">
                        <button type="button" class="tombol-submit-form" onclick="simpanSeksi('kurir')">Simpan Pengaturan Kurir</button>
                        <button type="button" class="tombol-batal-layanan" style="display:inline-block;" onclick="batalEditSeksi('kurir')">Batal</button>
                    </div>
                </form>
            </div>

        </div>
    </section>

    <div class="overlay-popup" id="overlayPopup" style="display:none;"></div>
    <div class="popup-konfirmasi" id="popupBerhasil" style="display:none;">
        <h3 class="popup-judul" id="popupBerhasilJudul">Berhasil Disimpan!</h3>
        <p class="popup-teks" id="popupBerhasilTeks">Perubahan telah disimpan dan langsung aktif.</p>
        <div class="popup-tombol-group" style="justify-content:center;">
            <button class="popup-tombol-konfirm" style="background-color:#52c49c; color:#1a4d3a;" id="btnPopupOk">OK</button>
        </div>
    </div>

    <script src="../assets/js/form-validation.js"></script>
    
    <script src="js/main.js"></script>

</body>
</html>
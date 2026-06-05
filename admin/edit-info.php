<?php
// 1. Hubungkan ke database (Sesuaikan path file koneksi database proyekmu jika berbeda)
require_once '../config/session.php';
require_once '../config/database.php';
require_once '../config/functions.php';

// Mulai session jika belum berjalan untuk kebutuhan fitur flash message / pop-up sukses
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// 2. PROSES BACKEND: Menerima POST Request untuk Update Data
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_GET['action'])) {
    $action = $_GET['action'];
    $success = false;

    if ($action === 'simpan_kontak') {
        $wa_admin = mysqli_real_escape_string($koneksi, $_POST['wa_admin']);
        $email_admin = mysqli_real_escape_string($koneksi, $_POST['email_admin']);
        
        mysqli_query($koneksi, "UPDATE info_website SET value='$wa_admin' WHERE `key`='wa_admin'");
        mysqli_query($koneksi, "UPDATE info_website SET value='$email_admin' WHERE `key`='email_admin'");
        $success = true;
    } 
    elseif ($action === 'simpan_jam') {
        $jam_buka = mysqli_real_escape_string($koneksi, $_POST['jam_buka']);
        $jam_tutup = mysqli_real_escape_string($koneksi, $_POST['jam_tutup']);
        $hari_operasional = mysqli_real_escape_string($koneksi, $_POST['hari_operasional']);
        $catatan_jam = mysqli_real_escape_string($koneksi, $_POST['catatan_jam']);

        mysqli_query($koneksi, "UPDATE info_website SET value='$jam_buka' WHERE `key`='jam_buka'");
        mysqli_query($koneksi, "UPDATE info_website SET value='$jam_tutup' WHERE `key`='jam_tutup'");
        mysqli_query($koneksi, "UPDATE info_website SET value='$hari_operasional' WHERE `key`='hari_operasional'");
        mysqli_query($koneksi, "UPDATE info_website SET value='$catatan_jam' WHERE `key`='catatan_jam'");
        $success = true;
    } 
    elseif ($action === 'simpan_alamat') {
        $nama_outlet = mysqli_real_escape_string($koneksi, $_POST['nama_outlet']);
        $alamat_outlet = mysqli_real_escape_string($koneksi, $_POST['alamat_outlet']);
        $kecamatan_outlet = mysqli_real_escape_string($koneksi, $_POST['kecamatan_outlet']);
        $maps_outlet = mysqli_real_escape_string($koneksi, $_POST['maps_outlet']);

        mysqli_query($koneksi, "UPDATE info_website SET value='$nama_outlet' WHERE `key`='nama_outlet'");
        mysqli_query($koneksi, "UPDATE info_website SET value='$alamat_outlet' WHERE `key`='alamat_outlet'");
        mysqli_query($koneksi, "UPDATE info_website SET value='$kecamatan_outlet' WHERE `key`='kecamatan_outlet'");
        mysqli_query($koneksi, "UPDATE info_website SET value='$maps_outlet' WHERE `key`='maps_outlet'");
        $success = true;
    } 
    elseif ($action === 'simpan_kurir') {
        $biaya_kurir = mysqli_real_escape_string($koneksi, $_POST['biaya_kurir']);
        $catatan_kurir = mysqli_real_escape_string($koneksi, $_POST['catatan_kurir']);
        
        // Ambil data array kecamatan dari checkbox, lalu konversi menjadi format JSON string
        $kecamatan_array = isset($_POST['kecamatan']) ? $_POST['kecamatan'] : [];
        $kecamatan_json = mysqli_real_escape_string($koneksi, json_encode($kecamatan_array));

        mysqli_query($koneksi, "UPDATE info_website SET value='$biaya_kurir' WHERE `key`='biaya_kurir'");
        mysqli_query($koneksi, "UPDATE info_website SET value='$catatan_kurir' WHERE `key`='catatan_kurir'");
        mysqli_query($koneksi, "UPDATE info_website SET value='$kecamatan_json' WHERE `key`='kecamatan_layanan'");
        $success = true;
    }

    if ($success) {
        // Refresh halaman dengan melempar parameter status sukses agar pop-up menyala
        header("Location: edit-info.php?status=sukses");
        exit;
    }
}

// 3. FRONTEND GET DATA: Ambil seluruh data dari tabel info_website ke dalam array asosiatif
$info = [];
$query_ambil = mysqli_query($koneksi, "SELECT * FROM info_website");
while ($row = mysqli_fetch_assoc($query_ambil)) {
    $info[$row['key']] = $row['value'];
}

// Pecah kembali string JSON kecamatan dari DB menjadi array PHP asli
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
                Perubahan di sini langsung memengaruhi halaman publik dan
                form pemesanan member.
            </p>

            <nav class="edit-info-nav">
                <a href="#seksiKontak" class="edit-info-nav-item aktif-nav" onclick="aktifkanNav(this)">
                    📞 Kontak
                </a>
                <a href="#seksiJam" class="edit-info-nav-item" onclick="aktifkanNav(this)">
                    🕐 Jam Operasional
                </a>
                <a href="#seksiAlamat" class="edit-info-nav-item" onclick="aktifkanNav(this)">
                    📍 Alamat Outlet
                </a>
                <a href="#seksiKurir" class="edit-info-nav-item" onclick="aktifkanNav(this)">
                    🛵 Layanan Kurir
                </a>
            </nav>

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

        <div class="layanan-kanan edit-info-kanan">

            <div class="edit-info-seksi" id="seksiKontak">
                <div class="edit-info-seksi-header">
                    <div>
                        <h2 class="judul-layanan-kanan">Informasi Kontak</h2>
                        <p class="subjudul-layanan-kanan">
                            Nomor WA ini digunakan sebagai tujuan click-to-chat otomatis saat member memesan.
                        </p>
                    </div>
                    <button class="tombol-edit-profil" id="tombolEditKontak" onclick="toggleEditSeksi('kontak')">
                        Edit
                    </button>
                </div>

                <form method="POST" action="edit-info.php?action=simpan_kontak" class="edit-info-form" id="formKontak">
                    <div class="grup-input-form">
                        <label class="label-profil">Nomor WhatsApp Admin (format: 628xxx)</label>
                        <input type="text" class="input-profil input-readonly" id="inputWaAdmin" name="wa_admin" value="<?= htmlspecialchars($info['wa_admin'] ?? '') ?>" readonly required>
                        <p class="edit-info-hint">
                            * Format tanpa tanda + dan tanpa spasi. Contoh: 628123456789
                        </p>
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
                    <button class="tombol-edit-profil" id="tombolEditJam" onclick="toggleEditSeksi('jam')">
                        Edit
                    </button>
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
                        <p class="subjudul-layanan-kanan">
                            Ditampilkan di kontak.php dan sebagai tujuan untuk member yang ambil sendiri.
                        </p>
                    </div>
                    <button class="tombol-edit-profil" id="tombolEditAlamat" onclick="toggleEditSeksi('alamat')">
                        Edit
                    </button>
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
                        <p class="edit-info-hint">* Ditampilkan sebagai tombol "Lihat di Maps" di kontak.php</p>
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
                        <p class="subjudul-layanan-kanan">
                            Kecamatan yang aktif muncul sebagai pilihan dropdown di form pesanan member.
                        </p>
                    </div>
                    <button class="tombol-edit-profil" id="tombolEditKurir" onclick="toggleEditSeksi('kurir')">
                        Edit
                    </button>
                </div>

                <form method="POST" action="edit-info.php?action=simpan_kurir" class="edit-info-form" id="formKurir">
                    <div class="grup-input-form">
                        <label class="label-profil">Biaya Kurir Flat (Rp)</label>
                        <input type="number" class="input-profil input-readonly" id="inputBiayaKurir" name="biaya_kurir" value="<?= htmlspecialchars($info['biaya_kurir'] ?? '0') ?>" min="0" step="500" readonly required>
                        <p class="edit-info-hint">* Biaya ini berlaku sama untuk semua kecamatan yang dilayani.</p>
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
                        <p class="edit-info-hint">* Teks ini muncul di form pesanan member saat memilih opsi kurir.</p>
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
        <p class="popup-teks" id="popupBerhasilTeks">
            Perubahan telah disimpan dan langsung aktif.
        </p>
        <div class="popup-tombol-group" style="justify-content:center;">
            <button class="popup-tombol-konfirm" style="background-color:#52c49c; color:#1a4d3a;" onclick="tutupPopupBerhasil()">
                OK
            </button>
        </div>
    </div>

    <script src="../assets/js/form-validation.js"></script>

    <script>
        // Fungsi untuk mengaktifkan mode Edit pada seksi terpilih
        function toggleEditSeksi(seksi) {
            const formId = 'form' + seksi.charAt(0).toUpperCase() + seksi.slice(1);
            const form = document.getElementById(formId);
            
            // Lepas status readonly dan hapus class styling abu-abu
            const inputs = form.querySelectorAll('input, select, textarea');
            inputs.forEach(input => {
                input.removeAttribute('readonly');
                input.classList.remove('input-readonly');
            });
            
            // Munculkan container tombol Simpan & Batal
            const tombolSimpanId = 'simpan' + seksi.charAt(0).toUpperCase() + seksi.slice(1);
            document.getElementById(tombolSimpanId).style.display = 'flex';
            
            // Khusus Seksi Kurir: Sembunyikan Text Pills, Munculkan Checkbox Pilihan
            if (seksi === 'kurir') {
                document.getElementById('kecamatanPills').style.display = 'none';
                document.getElementById('kecamatanCheckboxes').style.display = 'grid';
            }
        }

        // Fungsi Membatalkan Pengeditan (Kembali ke mode semula dan reset data asal)
        function batalEditSeksi(seksi) {
            const formId = 'form' + seksi.charAt(0).toUpperCase() + seksi.slice(1);
            const form = document.getElementById(formId);
            
            form.reset(); // Kembalikan nilai ke kondisi database semula sebelum diubah user
            
            const inputs = form.querySelectorAll('input, select, textarea');
            inputs.forEach(input => {
                input.setAttribute('readonly', true);
                input.classList.add('input-readonly');
            });
            
            const tombolSimpanId = 'simpan' + seksi.charAt(0).toUpperCase() + seksi.slice(1);
            document.getElementById(tombolSimpanId).style.display = 'none';
            
            if (seksi === 'kurir') {
                document.getElementById('kecamatanPills').style.display = 'flex';
                document.getElementById('kecamatanCheckboxes').style.display = 'none';
            }
        }

        // Fungsi Submit Form secara aman saat klik simpan
        function simpanSeksi(seksi) {
            const formId = 'form' + seksi.charAt(0).toUpperCase() + seksi.slice(1);
            document.getElementById(formId).submit();
        }

        // Switch class penanda menu navigasi aktif di bagian sidebar
        function aktifkanNav(element) {
            const items = document.querySelectorAll('.edit-info-nav-item');
            items.forEach(item => item.classList.remove('aktif-nav'));
            element.classList.add('aktif-nav');
        }

        // Fungsi Menutup Pop-up Sukses dan membersihkan string URL parameter (?status=sukses)
        function tutupPopupBerhasil() {
            document.getElementById('overlayPopup').style.display = 'none';
            document.getElementById('popupBerhasil').style.display = 'none';
            window.history.replaceState({}, document.title, window.location.pathname);
        }

        // Otomatis deteksi parameter URL jika status sukses melempar nilai balik dari backend PHP
        document.addEventListener("DOMContentLoaded", function() {
            const urlParams = new URLSearchParams(window.location.search);
            if (urlParams.get('status') === 'sukses') {
                document.getElementById('overlayPopup').style.display = 'block';
                document.getElementById('popupBerhasil').style.display = 'block';
            }
        });
    </script>
</body>
</html>
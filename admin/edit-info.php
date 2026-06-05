<?php
require_once '../config/session.php';
require_once '../config/database.php';
require_once '../config/functions.php';

if (!isset($_SESSION['id_user']) || $_SESSION['role'] !== 'admin') {
    redirect('../login.php');
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_GET['action'])) {
    $action = $_GET['action'];

    if ($action === 'simpan_kontak') {
        $stmt = $pdo->prepare("UPDATE info_website SET no_whatsapp = ?, no_telepon = ? WHERE id = 1");
        $stmt->execute([trim($_POST['no_whatsapp']), trim($_POST['no_telepon'])]);
    }
    elseif ($action === 'simpan_jam') {
        $stmt = $pdo->prepare("UPDATE info_website SET jam_operasional = ? WHERE id = 1");
        $stmt->execute([trim($_POST['jam_operasional'])]);
    }
    elseif ($action === 'simpan_alamat') {
        $stmt = $pdo->prepare("UPDATE info_website SET nama_usaha = ?, alamat = ? WHERE id = 1");
        $stmt->execute([trim($_POST['nama_usaha']), trim($_POST['alamat'])]);
    }
    elseif ($action === 'simpan_kecamatan') {
        $kecamatan_array = isset($_POST['kecamatan']) ? $_POST['kecamatan'] : [];
        $stmt = $pdo->prepare("UPDATE info_website SET kecamatan_dilayani = ? WHERE id = 1");
        $stmt->execute([json_encode($kecamatan_array, JSON_UNESCAPED_UNICODE)]);
    }

    header("Location: edit-info.php?status=sukses");
    exit;
}

$stmt = $pdo->query("SELECT * FROM info_website WHERE id = 1");
$info = $stmt->fetch();

$kecamatan_aktif = [];
if (!empty($info['kecamatan_dilayani'])) {
    $decoded = json_decode($info['kecamatan_dilayani'], true);
    $kecamatan_aktif = is_array($decoded) ? $decoded : [];
}

$semua_kecamatan = ['Wenang','Wanea','Tikala','Mapanget','Tuminting','Singkil','Bunaken','Malalayang','Sario','Paal Dua'];
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

<?php if (isset($_GET['status']) && $_GET['status'] === 'sukses'): ?>
    <div class="notif-sukses">✓ Perubahan berhasil disimpan.</div>
<?php endif; ?>

<div class="edit-info-page">

    <h1 class="edit-info-judul">Info Website</h1>
    <p class="edit-info-sub">
        Perubahan langsung memengaruhi halaman publik dan form pemesanan member.
    </p>

    <!-- ── KARTU 1: KONTAK ── -->
    <div class="edit-info-kartu">
        <div class="edit-info-kartu-header">
            <div>
                <h2 class="edit-info-kartu-judul">📞 Kontak</h2>
                <p class="edit-info-kartu-sub">Nomor WhatsApp dan telepon yang tampil di halaman publik.</p>
            </div>
            <button class="tombol-edit-info" id="btnEditKontak"
                    onclick="bukaEdit('kontak')">Edit</button>
        </div>

        <!-- Tampilan -->
        <div id="viewKontak" class="edit-info-grid">
            <div class="edit-info-field">
                <span class="edit-info-label">Nomor WhatsApp</span>
                <p class="edit-info-nilai"><?= htmlspecialchars($info['no_whatsapp'] ?? '—') ?></p>
            </div>
            <div class="edit-info-field">
                <span class="edit-info-label">Nomor Telepon</span>
                <p class="edit-info-nilai"><?= htmlspecialchars($info['no_telepon'] ?? '—') ?></p>
            </div>
        </div>

        <!-- Form edit -->
        <form method="POST" action="edit-info.php?action=simpan_kontak"
              id="formKontak" style="display:none;">
            <div class="edit-info-grid">
                <div class="edit-info-field">
                    <label class="edit-info-label" for="inputWA">Nomor WhatsApp</label>
                    <input type="text" id="inputWA" name="no_whatsapp"
                           class="edit-info-input"
                           value="<?= htmlspecialchars($info['no_whatsapp'] ?? '') ?>"
                           placeholder="cth: 6281234567890">
                    <span class="edit-info-hint">Format: 62xxx tanpa tanda +</span>
                </div>
                <div class="edit-info-field">
                    <label class="edit-info-label" for="inputTelp">Nomor Telepon</label>
                    <input type="text" id="inputTelp" name="no_telepon"
                           class="edit-info-input"
                           value="<?= htmlspecialchars($info['no_telepon'] ?? '') ?>"
                           placeholder="cth: 08123456789">
                </div>
            </div>
            <div class="edit-info-aksi">
                <button type="submit" class="tombol-simpan-info">Simpan</button>
                <button type="button" class="tombol-batal-info"
                        onclick="tutupEdit('kontak')">Batal</button>
            </div>
        </form>
    </div>

    <!-- ── KARTU 2: JAM OPERASIONAL ── -->
    <div class="edit-info-kartu">
        <div class="edit-info-kartu-header">
            <div>
                <h2 class="edit-info-kartu-judul">🕐 Jam Operasional</h2>
                <p class="edit-info-kartu-sub">Tampil di halaman beranda dan info kontak.</p>
            </div>
            <button class="tombol-edit-info" id="btnEditJam"
                    onclick="bukaEdit('jam')">Edit</button>
        </div>

        <div id="viewJam" class="edit-info-grid edit-info-grid-full">
            <div class="edit-info-field">
                <span class="edit-info-label">Jam Operasional</span>
                <p class="edit-info-nilai"><?= htmlspecialchars($info['jam_operasional'] ?? '—') ?></p>
            </div>
        </div>

        <form method="POST" action="edit-info.php?action=simpan_jam"
              id="formJam" style="display:none;">
            <div class="edit-info-grid edit-info-grid-full">
                <div class="edit-info-field">
                    <label class="edit-info-label" for="inputJam">Jam Operasional</label>
                    <input type="text" id="inputJam" name="jam_operasional"
                           class="edit-info-input"
                           value="<?= htmlspecialchars($info['jam_operasional'] ?? '') ?>"
                           placeholder="cth: Senin - Sabtu: 08.00 - 17.00 WITA">
                    <span class="edit-info-hint">Tulis lengkap dalam satu baris.</span>
                </div>
            </div>
            <div class="edit-info-aksi">
                <button type="submit" class="tombol-simpan-info">Simpan</button>
                <button type="button" class="tombol-batal-info"
                        onclick="tutupEdit('jam')">Batal</button>
            </div>
        </form>
    </div>

    <!-- ── KARTU 3: NAMA & ALAMAT ── -->
    <div class="edit-info-kartu">
        <div class="edit-info-kartu-header">
            <div>
                <h2 class="edit-info-kartu-judul">📍 Nama Usaha & Alamat</h2>
                <p class="edit-info-kartu-sub">Tampil di footer dan halaman kontak.</p>
            </div>
            <button class="tombol-edit-info" id="btnEditAlamat"
                    onclick="bukaEdit('alamat')">Edit</button>
        </div>

        <div id="viewAlamat" class="edit-info-grid">
            <div class="edit-info-field">
                <span class="edit-info-label">Nama Usaha</span>
                <p class="edit-info-nilai"><?= htmlspecialchars($info['nama_usaha'] ?? '—') ?></p>
            </div>
            <div class="edit-info-field">
                <span class="edit-info-label">Alamat</span>
                <p class="edit-info-nilai"><?= htmlspecialchars($info['alamat'] ?? '—') ?></p>
            </div>
        </div>

        <form method="POST" action="edit-info.php?action=simpan_alamat"
              id="formAlamat" style="display:none;">
            <div class="edit-info-grid">
                <div class="edit-info-field">
                    <label class="edit-info-label" for="inputNama">Nama Usaha</label>
                    <input type="text" id="inputNama" name="nama_usaha"
                           class="edit-info-input"
                           value="<?= htmlspecialchars($info['nama_usaha'] ?? '') ?>"
                           placeholder="cth: CleanCo Laundry">
                </div>
                <div class="edit-info-field">
                    <label class="edit-info-label" for="inputAlamat">Alamat Lengkap</label>
                    <input type="text" id="inputAlamat" name="alamat"
                           class="edit-info-input"
                           value="<?= htmlspecialchars($info['alamat'] ?? '') ?>"
                           placeholder="cth: Jl. Mawar No. 10, Manado">
                </div>
            </div>
            <div class="edit-info-aksi">
                <button type="submit" class="tombol-simpan-info">Simpan</button>
                <button type="button" class="tombol-batal-info"
                        onclick="tutupEdit('alamat')">Batal</button>
            </div>
        </form>
    </div>

    <!-- ── KARTU 4: KECAMATAN ── -->
    <div class="edit-info-kartu">
        <div class="edit-info-kartu-header">
            <div>
                <h2 class="edit-info-kartu-judul">🗺️ Kecamatan yang Dilayani</h2>
                <p class="edit-info-kartu-sub">Menentukan pilihan kecamatan di form pemesanan kurir.</p>
            </div>
            <button class="tombol-edit-info" id="btnEditKecamatan"
                    onclick="bukaEdit('kecamatan')">Edit</button>
        </div>

        <div id="viewKecamatan" class="kecamatan-grid">
            <?php if (empty($kecamatan_aktif)): ?>
                <p style="color:#aaa; font-size:0.9rem;">Belum ada kecamatan dipilih.</p>
            <?php else: ?>
                <?php foreach ($kecamatan_aktif as $kec): ?>
                    <span class="pill-kec"><?= htmlspecialchars($kec) ?></span>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>

        <form method="POST" action="edit-info.php?action=simpan_kecamatan"
              id="formKecamatan" style="display:none;">
            <div class="kecamatan-grid" style="margin-bottom:20px;">
                <?php foreach ($semua_kecamatan as $kec): ?>
                    <label class="checkbox-kec">
                        <input type="checkbox" name="kecamatan[]" value="<?= $kec ?>"
                               <?= in_array($kec, $kecamatan_aktif) ? 'checked' : '' ?>>
                        <?= $kec ?>
                    </label>
                <?php endforeach; ?>
            </div>
            <div class="edit-info-aksi">
                <button type="submit" class="tombol-simpan-info">Simpan</button>
                <button type="button" class="tombol-batal-info"
                        onclick="tutupEdit('kecamatan')">Batal</button>
            </div>
        </form>
    </div>

</div>

<script>
const _seksiMap = {
    kontak:     { form: 'formKontak',     view: 'viewKontak',     btn: 'btnEditKontak'     },
    jam:        { form: 'formJam',        view: 'viewJam',        btn: 'btnEditJam'        },
    alamat:     { form: 'formAlamat',     view: 'viewAlamat',     btn: 'btnEditAlamat'     },
    kecamatan:  { form: 'formKecamatan',  view: 'viewKecamatan',  btn: 'btnEditKecamatan'  },
};

function bukaEdit(seksi) {
    const s = _seksiMap[seksi];
    document.getElementById(s.form).style.display = 'block';
    document.getElementById(s.view).style.display = 'none';
    document.getElementById(s.btn).style.display  = 'none';
}

function tutupEdit(seksi) {
    const s = _seksiMap[seksi];
    document.getElementById(s.form).style.display = 'none';
    document.getElementById(s.view).style.display = '';
    document.getElementById(s.btn).style.display  = '';
}
</script>

</body>
</html>
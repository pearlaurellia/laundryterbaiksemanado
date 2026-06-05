<?php
// 1. Inisialisasi Sistem Keamanan & Database
require_once '../config/session.php';
require_once '../config/database.php';
require_once '../config/functions.php';

// Proteksi berlapis halaman admin
if (!isset($_SESSION['id_user']) || $_SESSION['role'] !== 'admin') {
    redirect('../login.php');
}

// ============================================================
// [POST] LOGIKA BACKEND HANDLER - PROSES UPDATE DATA PER SEKSI
// ============================================================
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
    elseif ($action === 'simpan_kurir') {
        $kecamatan_array = isset($_POST['kecamatan']) ? $_POST['kecamatan'] : [];
        $stmt = $pdo->prepare("UPDATE info_website SET kecamatan_dilayani = ? WHERE id = 1");
        $stmt->execute([json_encode($kecamatan_array, JSON_UNESCAPED_UNICODE)]);
    }

    header("Location: edit-info.php?status=sukses");
    exit;
}

// ============================================================
// [GET] AMBIL DATA PROFIL WEBSITE DARI DATABASE
// ============================================================
$stmt = $pdo->query("SELECT * FROM info_website WHERE id = 1");
$info = $stmt->fetch();

$kecamatan_aktif = [];
if (!empty($info['kecamatan_dilayani'])) {
    $decoded = json_decode($info['kecamatan_dilayani'], true);
    $kecamatan_aktif = is_array($decoded) ? $decoded : [];
}

$semua_kecamatan = ['Wenang','Wanea','Tikala','Mapanget','Tuminting','Singkil','Bunaken','Malalayang','Sario','Paal Dua'];

include '../includes/header-admin.php'; 
?>

<?php if (isset($_GET['status']) && $_GET['status'] === 'sukses'): ?>
    <div class="notif-sukses" style="background: #d1fae5; color: #065f46; padding: 15px; border-radius: 10px; margin: 20px var(--padding-horizontal); font-weight: bold; border-left: 5px solid #10b981;">
        ✓ Perubahan info website berhasil disimpan ke database.
    </div>
<?php endif; ?>

<div class="edit-info-page" style="padding: 20px var(--padding-horizontal);">

    <h1 class="edit-info-judul" style="font-family: 'Bricolage Grotesque', sans-serif; color: var(--birutua); margin-bottom: 5px;">Info Website</h1>
    <p class="edit-info-sub" style="color: #666; margin-bottom: 30px;">
        Perubahan langsung memengaruhi halaman publik dan form pemesanan member.
    </p>

    <div class="edit-info-kartu" style="background: white; padding: 30px; border-radius: 20px; box-shadow: 0 4px 15px rgba(0,0,0,0.02); margin-bottom: 25px;">
        <div class="edit-info-kartu-header" style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px;">
            <div>
                <h2 class="edit-info-kartu-judul" style="font-family: 'Bricolage Grotesque', sans-serif; font-size: 1.4rem; color: var(--birutua);">📞 Kontak</h2>
                <p class="edit-info-kartu-sub" style="color: #888; font-size: 0.9rem;">Nomor WhatsApp dan telepon yang tampil di halaman publik.</p>
            </div>
            <button type="button" class="tombol-edit-info" id="btnEditKontak" onclick="bukaEdit('kontak')" style="background: #e0f2fe; color: #0369a1; border: none; padding: 8px 20px; border-radius: 8px; font-weight: bold; cursor: pointer;">Edit</button>
        </div>

        <div id="viewKontak" class="edit-info-grid">
            <div class="edit-info-field">
                <span class="edit-info-label" style="color: #999; font-size: 0.85rem; display: block; margin-bottom: 5px;">Nomor WhatsApp</span>
                <p class="edit-info-nilai" style="font-weight: 600; color: #333;"><?= htmlspecialchars($info['no_whatsapp'] ?? '—') ?></p>
            </div>
            <div class="edit-info-field">
                <span class="edit-info-label" style="color: #999; font-size: 0.85rem; display: block; margin-bottom: 5px;">Nomor Telepon</span>
                <p class="edit-info-nilai" style="font-weight: 600; color: #333;"><?= htmlspecialchars($info['no_telepon'] ?? '—') ?></p>
            </div>
        </div>

        <form method="POST" action="edit-info.php?action=simpan_kontak" id="formKontak" style="display:none;">
            <div class="edit-info-grid">
                <div class="edit-info-field">
                    <label class="edit-info-label" for="inputWA">Nomor WhatsApp</label>
                    <input type="text" id="inputWA" name="no_whatsapp" class="edit-info-input" value="<?= htmlspecialchars($info['no_whatsapp'] ?? '') ?>" placeholder="cth: 6281234567890">
                    <span class="edit-info-hint" style="font-size:0.75rem; color:#aaa; display:block; margin-top:4px;">Format: 62xxx tanpa tanda +</span>
                </div>
                <div class="edit-info-field">
                    <label class="edit-info-label" for="inputTelp">Nomor Telepon</label>
                    <input type="text" id="inputTelp" name="no_telepon" class="edit-info-input" value="<?= htmlspecialchars($info['no_telepon'] ?? '') ?>" placeholder="cth: 08123456789">
                </div>
            </div>
            <div class="edit-info-aksi" style="margin-top: 20px; display: flex; gap: 10px;">
                <button type="submit" class="tombol-simpan-info" style="background: var(--tealmuda); color: white; border: none; padding: 10px 25px; border-radius: 8px; font-weight: bold; cursor: pointer;">Simpan</button>
                <button type="button" class="tombol-batal-info" onclick="tutupEdit('kontak')" style="background: #f3f4f6; color: #4b5563; border: none; padding: 10px 25px; border-radius: 8px; font-weight: bold; cursor: pointer;">Batal</button>
            </div>
        </form>
    </div>

    <div class="edit-info-kartu" style="background: white; padding: 30px; border-radius: 20px; box-shadow: 0 4px 15px rgba(0,0,0,0.02); margin-bottom: 25px;">
        <div class="edit-info-kartu-header" style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px;">
            <div>
                <h2 class="edit-info-kartu-judul" style="font-family: 'Bricolage Grotesque', sans-serif; font-size: 1.4rem; color: var(--birutua);">🕐 Jam Operasional</h2>
                <p class="edit-info-kartu-sub" style="color: #888; font-size: 0.9rem;">Tampil di halaman beranda dan info kontak.</p>
            </div>
            <button type="button" class="tombol-edit-info" id="btnEditJam" onclick="bukaEdit('jam')" style="background: #e0f2fe; color: #0369a1; border: none; padding: 8px 20px; border-radius: 8px; font-weight: bold; cursor: pointer;">Edit</button>
        </div>

        <div id="viewJam" class="edit-info-grid edit-info-grid-full">
            <div class="edit-info-field">
                <span class="edit-info-label" style="color: #999; font-size: 0.85rem; display: block; margin-bottom: 5px;">Jam Operasional</span>
                <p class="edit-info-nilai" style="font-weight: 600; color: #333;"><?= htmlspecialchars($info['jam_operasional'] ?? '—') ?></p>
            </div>
        </div>

        <form method="POST" action="edit-info.php?action=simpan_jam" id="formJam" style="display:none;">
            <div class="edit-info-grid edit-info-grid-full">
                <div class="edit-info-field">
                    <label class="edit-info-label" for="inputJam">Jam Operasional</label>
                    <input type="text" id="inputJam" name="jam_operasional" class="edit-info-input" value="<?= htmlspecialchars($info['jam_operasional'] ?? '') ?>" placeholder="cth: Senin - Sabtu: 08.00 - 17.00 WITA" style="width: 100%;">
                    <span class="edit-info-hint" style="font-size:0.75rem; color:#aaa; display:block; margin-top:4px;">Tulis lengkap dalam satu baris.</span>
                </div>
            </div>
            <div class="edit-info-aksi" style="margin-top: 20px; display: flex; gap: 10px;">
                <button type="submit" class="tombol-simpan-info" style="background: var(--tealmuda); color: white; border: none; padding: 10px 25px; border-radius: 8px; font-weight: bold; cursor: pointer;">Simpan</button>
                <button type="button" class="tombol-batal-info" onclick="tutupEdit('jam')" style="background: #f3f4f6; color: #4b5563; border: none; padding: 10px 25px; border-radius: 8px; font-weight: bold; cursor: pointer;">Batal</button>
            </div>
        </form>
    </div>

    <div class="edit-info-kartu" style="background: white; padding: 30px; border-radius: 20px; box-shadow: 0 4px 15px rgba(0,0,0,0.02); margin-bottom: 25px;">
        <div class="edit-info-kartu-header" style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px;">
            <div>
                <h2 class="edit-info-kartu-judul" style="font-family: 'Bricolage Grotesque', sans-serif; font-size: 1.4rem; color: var(--birutua);">📍 Nama Usaha & Alamat</h2>
                <p class="edit-info-kartu-sub" style="color: #888; font-size: 0.9rem;">Tampil di footer dan halaman kontak.</p>
            </div>
            <button type="button" class="tombol-edit-info" id="btnEditAlamat" onclick="bukaEdit('alamat')" style="background: #e0f2fe; color: #0369a1; border: none; padding: 8px 20px; border-radius: 8px; font-weight: bold; cursor: pointer;">Edit</button>
        </div>

        <div id="viewAlamat" class="edit-info-grid">
            <div class="edit-info-field">
                <span class="edit-info-label" style="color: #999; font-size: 0.85rem; display: block; margin-bottom: 5px;">Nama Usaha</span>
                <p class="edit-info-nilai" style="font-weight: 600; color: #333;"><?= htmlspecialchars($info['nama_usaha'] ?? '—') ?></p>
            </div>
            <div class="edit-info-field">
                <span class="edit-info-label" style="color: #999; font-size: 0.85rem; display: block; margin-bottom: 5px;">Alamat</span>
                <p class="edit-info-nilai" style="font-weight: 600; color: #333;"><?= htmlspecialchars($info['alamat'] ?? '—') ?></p>
            </div>
        </div>

        <form method="POST" action="edit-info.php?action=simpan_alamat" id="formAlamat" style="display:none;">
            <div class="edit-info-grid">
                <div class="edit-info-field">
                    <label class="edit-info-label" for="inputNama">Nama Usaha</label>
                    <input type="text" id="inputNama" name="nama_usaha" class="edit-info-input" value="<?= htmlspecialchars($info['nama_usaha'] ?? '') ?>" placeholder="cth: CleanCo Laundry">
                </div>
                <div class="edit-info-field">
                    <label class="edit-info-label" for="inputAlamat">Alamat Lengkap</label>
                    <input type="text" id="inputAlamat" name="alamat" class="edit-info-input" value="<?= htmlspecialchars($info['alamat'] ?? '') ?>" placeholder="cth: Jl. Mawar No. 10, Manado">
                </div>
            </div>
            <div class="edit-info-aksi" style="margin-top: 20px; display: flex; gap: 10px;">
                <button type="submit" class="tombol-simpan-info" style="background: var(--tealmuda); color: white; border: none; padding: 10px 25px; border-radius: 8px; font-weight: bold; cursor: pointer;">Simpan</button>
                <button type="button" class="tombol-batal-info" onclick="tutupEdit('alamat')" style="background: #f3f4f6; color: #4b5563; border: none; padding: 10px 25px; border-radius: 8px; font-weight: bold; cursor: pointer;">Batal</button>
            </div>
        </form>
    </div>

    <div class="edit-info-kartu" style="background: white; padding: 30px; border-radius: 20px; box-shadow: 0 4px 15px rgba(0,0,0,0.02); margin-bottom: 25px;">
        <div class="edit-info-kartu-header" style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px;">
            <div>
                <h2 class="edit-info-kartu-judul" style="font-family: 'Bricolage Grotesque', sans-serif; font-size: 1.4rem; color: var(--birutua);">🗺️ Kecamatan yang Dilayani</h2>
                <p class="edit-info-kartu-sub" style="color: #888; font-size: 0.9rem;">Menentukan pilihan kecamatan di form pemesanan kurir.</p>
            </div>
            <button type="button" class="tombol-edit-info" id="btnEditKecamatan" onclick="bukaEdit('kecamatan')" style="background: #e0f2fe; color: #0369a1; border: none; padding: 8px 20px; border-radius: 8px; font-weight: bold; cursor: pointer;">Edit</button>
        </div>

        <div id="viewKecamatan" class="kecamatan-grid" style="display: flex; gap: 10px; flex-wrap: wrap;">
            <?php if (empty($kecamatan_aktif)): ?>
                <p style="color:#aaa; font-size:0.9rem;">Belum ada kecamatan dipilih.</p>
            <?php else: ?>
                <?php foreach ($kecamatan_aktif as $kec): ?>
                    <span class="pill-kec" style="background: #f0fdf4; color: #16a34a; padding: 6px 16px; border-radius: 20px; font-size: 0.85rem; font-weight: 600; border: 1px solid #bbf7d0;"><?= htmlspecialchars($kec) ?></span>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>

        <form method="POST" action="edit-info.php?action=simpan_kurir" id="formKecamatan" style="display:none;">
            <div class="kecamatan-grid" style="margin-bottom:20px; display: grid; grid-template-columns: repeat(auto-fill, minmax(140px, 1fr)); gap: 12px;">
                <?php foreach ($semua_kecamatan as $kec): ?>
                    <label class="checkbox-kec" style="display: flex; align-items: center; gap: 8px; cursor: pointer; font-size: 0.95rem;">
                        <input type="checkbox" name="kecamatan[]" value="<?= $kec ?>" <?= in_array($kec, $kecamatan_aktif) ? 'checked' : '' ?>>
                        <?= $kec ?>
                    </label>
                <?php endforeach; ?>
            </div>
            <div class="edit-info-aksi" style="display: flex; gap: 10px;">
                <button type="submit" class="tombol-simpan-info" style="background: var(--tealmuda); color: white; border: none; padding: 10px 25px; border-radius: 8px; font-weight: bold; cursor: pointer;">Simpan</button>
                <button type="button" class="tombol-batal-info" onclick="tutupEdit('kecamatan')" style="background: #f3f4f6; color: #4b5563; border: none; padding: 10px 25px; border-radius: 8px; font-weight: bold; cursor: pointer;">Batal</button>
            </div>
        </form>
    </div>

</div>

<script src="../assets/js/info-admin.js"></script>

<?php 
include '../includes/footer.php'; 
?>
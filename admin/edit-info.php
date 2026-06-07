<?php
require_once '../config/session.php';
require_once '../config/database.php';
require_once '../config/functions.php';

// Proteksi berlapis halaman admin
if (!isset($_SESSION['id_user']) || $_SESSION['role'] !== 'admin') {
    redirect('../login.php');
}

// [POST] LOGIKA BACKEND HANDLER - PROSES UPDATE DATA PER SEKSI
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_GET['action'])) {
    $action = $_GET['action'];

    if ($action === 'simpan_kontak') {
        $stmt = $pdo->prepare("UPDATE info_website SET no_whatsapp = ? WHERE id = 1");
        $stmt->execute([trim($_POST['no_whatsapp'])]);
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

// [GET] AMBIL DATA PROFIL WEBSITE DARI DATABASE
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

<!-- HERO SECTION -->
<section class="hero-form" style="min-height: auto; padding: 100px 80px 60px;">
    
    <!-- DEKORASI -->
    <div class="bulat-atas-form" style="right: 8%; top: 25%;"></div>
    <div class="bulat-ditengah-form" style="right: 25%; bottom: 30%;"></div>
    <div class="bulat-besar-form" style="right: -30px; bottom: -80px;"><h2>Laundry 3J</h2></div>

    <div style="position: relative; z-index: 2; width: 100%; max-width: 900px; margin: 0 auto;">
        
        <!-- HEADER -->
        <div style="text-align: center; margin-bottom: 40px;">
            <h1 style="font-family: 'Bricolage Grotesque', sans-serif; font-size: 2.5rem; color: white; margin: 0 0 8px; filter: drop-shadow(var(--shadow));">
                Info Website
            </h1>
            <p style="color: rgba(255,255,255,0.75); font-size: 1.1rem; margin: 0;">
                Kelola informasi kontak, alamat, dan layanan yang tampil di halaman publik
            </p>
        </div>

        <!-- NOTIFIKASI SUKSES -->
        <?php if (isset($_GET['status']) && $_GET['status'] === 'sukses'): ?>
            <div style="background: rgba(209, 250, 229, 0.2); border: 1px solid rgba(16, 185, 129, 0.4); border-left: 4px solid #10b981; border-radius: 0 12px 12px 0; padding: 14px 18px; margin-bottom: 24px; color: #6ee7b7; font-weight: 500; font-size: 0.9rem;">
                ✓ Perubahan info website berhasil disimpan ke database.
            </div>
        <?php endif; ?>

        <!-- KONTAK -->
        <div style="background: white; border-radius: 0 20px 20px 20px; padding: 28px 32px; box-shadow: var(--shadow); margin-bottom: 20px;">
            <div style="display: flex; justify-content: space-between; align-items: flex-start; margin-bottom: 20px; padding-bottom: 16px; border-bottom: 2px solid #f0f0f0;">
                <div>
                    <h2 style="font-family: 'Bricolage Grotesque', sans-serif; font-size: 1.2rem; color: var(--birutua); margin: 0 0 4px;"> Kontak</h2>
                    <p style="color: #888; font-size: 0.82rem; margin: 0;">Nomor WhatsApp yang tampil di halaman publik</p>
                </div>
                <button type="button" id="btnEditKontak" onclick="bukaEdit('kontak')" 
                        style="padding: 8px 20px; background: #DDEEFF; color: var(--birutua); border: none; border-radius: 20px 0 20px 0; font-family: 'DM Sans', sans-serif; font-weight: 600; font-size: 0.85rem; cursor: pointer; box-shadow: var(--shadow);">
                    Edit
                </button>
            </div>

            <!-- View Mode -->
            <div id="viewKontak">
                <div style="margin-bottom: 8px;">
                    <span style="font-size: 0.78rem; color: #999; text-transform: uppercase; letter-spacing: 0.05em;">Nomor WhatsApp</span>
                    <p style="font-size: 1rem; color: #333; font-weight: 500; margin: 4px 0 0;"><?= htmlspecialchars($info['no_whatsapp'] ?? '—') ?></p>
                </div>
            </div>

            <!-- Edit Mode -->
            <form method="POST" action="edit-info.php?action=simpan_kontak" id="formKontak" style="display: none;">
                <div style="margin-bottom: 16px;">
                    <label style="font-size: 0.78rem; font-weight: 700; color: var(--birutua); text-transform: uppercase; letter-spacing: 0.06em; display: block; margin-bottom: 6px;">Nomor WhatsApp</label>
                    <input type="text" name="no_whatsapp" value="<?= htmlspecialchars($info['no_whatsapp'] ?? '') ?>" 
                           placeholder="cth: 6281234567890"
                           style="width: 100%; padding: 12px 16px; border: 2px solid #d0d8e8; border-radius: 20px 0 20px 0; font-family: 'DM Sans', sans-serif; font-size: 0.95rem; outline: none; transition: border-color 0.2s;">
                    <span style="font-size: 0.75rem; color: #999; display: block; margin-top: 4px;">Format: 62xxx tanpa tanda +</span>
                </div>
                <div style="display: flex; gap: 12px;">
                    <button type="submit" style="padding: 10px 28px; background: var(--tealmuda); color: #1a4d3a; border: none; border-radius: 20px 0 20px 0; font-family: 'DM Sans', sans-serif; font-weight: 600; cursor: pointer; box-shadow: var(--shadow);">Simpan</button>
                    <button type="button" onclick="tutupEdit('kontak')" style="padding: 10px 28px; background: #f0f0f0; color: #555; border: none; border-radius: 20px 0 20px 0; font-family: 'DM Sans', sans-serif; font-weight: 500; cursor: pointer;">Batal</button>
                </div>
            </form>
        </div>

        <!-- JAM OPERASIONAL -->
        <div style="background: white; border-radius: 0 20px 20px 20px; padding: 28px 32px; box-shadow: var(--shadow); margin-bottom: 20px;">
            <div style="display: flex; justify-content: space-between; align-items: flex-start; margin-bottom: 20px; padding-bottom: 16px; border-bottom: 2px solid #f0f0f0;">
                <div>
                    <h2 style="font-family: 'Bricolage Grotesque', sans-serif; font-size: 1.2rem; color: var(--birutua); margin: 0 0 4px;">Jam Operasional</h2>
                    <p style="color: #888; font-size: 0.82rem; margin: 0;">Tampil di halaman beranda dan info kontak</p>
                </div>
                <button type="button" id="btnEditJam" onclick="bukaEdit('jam')" 
                        style="padding: 8px 20px; background: #DDEEFF; color: var(--birutua); border: none; border-radius: 20px 0 20px 0; font-family: 'DM Sans', sans-serif; font-weight: 600; font-size: 0.85rem; cursor: pointer; box-shadow: var(--shadow);">
                    Edit
                </button>
            </div>

            <div id="viewJam">
                <span style="font-size: 0.78rem; color: #999; text-transform: uppercase; letter-spacing: 0.05em;">Jam Operasional</span>
                <p style="font-size: 1rem; color: #333; font-weight: 500; margin: 4px 0 0;"><?= htmlspecialchars($info['jam_operasional'] ?? '—') ?></p>
            </div>

            <form method="POST" action="edit-info.php?action=simpan_jam" id="formJam" style="display: none;">
                <div style="margin-bottom: 16px;">
                    <label style="font-size: 0.78rem; font-weight: 700; color: var(--birutua); text-transform: uppercase; letter-spacing: 0.06em; display: block; margin-bottom: 6px;">Jam Operasional</label>
                    <input type="text" name="jam_operasional" value="<?= htmlspecialchars($info['jam_operasional'] ?? '') ?>" 
                           placeholder="cth: Senin - Sabtu: 08.00 - 17.00 WITA"
                           style="width: 100%; padding: 12px 16px; border: 2px solid #d0d8e8; border-radius: 20px 0 20px 0; font-family: 'DM Sans', sans-serif; font-size: 0.95rem; outline: none;">
                </div>
                <div style="display: flex; gap: 12px;">
                    <button type="submit" style="padding: 10px 28px; background: var(--tealmuda); color: #1a4d3a; border: none; border-radius: 20px 0 20px 0; font-family: 'DM Sans', sans-serif; font-weight: 600; cursor: pointer; box-shadow: var(--shadow);">Simpan</button>
                    <button type="button" onclick="tutupEdit('jam')" style="padding: 10px 28px; background: #f0f0f0; color: #555; border: none; border-radius: 20px 0 20px 0; font-family: 'DM Sans', sans-serif; font-weight: 500; cursor: pointer;">Batal</button>
                </div>
            </form>
        </div>

        <!-- NAMA USAHA & ALAMAT -->
        <div style="background: white; border-radius: 0 20px 20px 20px; padding: 28px 32px; box-shadow: var(--shadow); margin-bottom: 20px;">
            <div style="display: flex; justify-content: space-between; align-items: flex-start; margin-bottom: 20px; padding-bottom: 16px; border-bottom: 2px solid #f0f0f0;">
                <div>
                    <h2 style="font-family: 'Bricolage Grotesque', sans-serif; font-size: 1.2rem; color: var(--birutua); margin: 0 0 4px;">Nama Usaha & Alamat</h2>
                    <p style="color: #888; font-size: 0.82rem; margin: 0;">Tampil di footer dan halaman kontak</p>
                </div>
                <button type="button" id="btnEditAlamat" onclick="bukaEdit('alamat')" 
                        style="padding: 8px 20px; background: #DDEEFF; color: var(--birutua); border: none; border-radius: 20px 0 20px 0; font-family: 'DM Sans', sans-serif; font-weight: 600; font-size: 0.85rem; cursor: pointer; box-shadow: var(--shadow);">
                    Edit
                </button>
            </div>

            <div id="viewAlamat" style="display: grid; grid-template-columns: 1fr 1fr; gap: 20px;">
                <div>
                    <span style="font-size: 0.78rem; color: #999; text-transform: uppercase; letter-spacing: 0.05em;">Nama Usaha</span>
                    <p style="font-size: 1rem; color: #333; font-weight: 500; margin: 4px 0 0;"><?= htmlspecialchars($info['nama_usaha'] ?? '—') ?></p>
                </div>
                <div>
                    <span style="font-size: 0.78rem; color: #999; text-transform: uppercase; letter-spacing: 0.05em;">Alamat</span>
                    <p style="font-size: 1rem; color: #333; font-weight: 500; margin: 4px 0 0;"><?= htmlspecialchars($info['alamat'] ?? '—') ?></p>
                </div>
            </div>

            <form method="POST" action="edit-info.php?action=simpan_alamat" id="formAlamat" style="display: none;">
                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 16px; margin-bottom: 16px;">
                    <div>
                        <label style="font-size: 0.78rem; font-weight: 700; color: var(--birutua); text-transform: uppercase; letter-spacing: 0.06em; display: block; margin-bottom: 6px;">Nama Usaha</label>
                        <input type="text" name="nama_usaha" value="<?= htmlspecialchars($info['nama_usaha'] ?? '') ?>" 
                               placeholder="cth: Laundry 3J Laundry"
                               style="width: 100%; padding: 12px 16px; border: 2px solid #d0d8e8; border-radius: 20px 0 20px 0; font-family: 'DM Sans', sans-serif; font-size: 0.95rem; outline: none;">
                    </div>
                    <div>
                        <label style="font-size: 0.78rem; font-weight: 700; color: var(--birutua); text-transform: uppercase; letter-spacing: 0.06em; display: block; margin-bottom: 6px;">Alamat Lengkap</label>
                        <input type="text" name="alamat" value="<?= htmlspecialchars($info['alamat'] ?? '') ?>" 
                               placeholder="cth: Jl. Mawar No. 10, Manado"
                               style="width: 100%; padding: 12px 16px; border: 2px solid #d0d8e8; border-radius: 20px 0 20px 0; font-family: 'DM Sans', sans-serif; font-size: 0.95rem; outline: none;">
                    </div>
                </div>
                <div style="display: flex; gap: 12px;">
                    <button type="submit" style="padding: 10px 28px; background: var(--tealmuda); color: #1a4d3a; border: none; border-radius: 20px 0 20px 0; font-family: 'DM Sans', sans-serif; font-weight: 600; cursor: pointer; box-shadow: var(--shadow);">Simpan</button>
                    <button type="button" onclick="tutupEdit('alamat')" style="padding: 10px 28px; background: #f0f0f0; color: #555; border: none; border-radius: 20px 0 20px 0; font-family: 'DM Sans', sans-serif; font-weight: 500; cursor: pointer;">Batal</button>
                </div>
            </form>
        </div>

        <!-- KECAMATAN -->
        <div style="background: white; border-radius: 0 20px 20px 20px; padding: 28px 32px; box-shadow: var(--shadow); margin-bottom: 20px;">
            <div style="display: flex; justify-content: space-between; align-items: flex-start; margin-bottom: 20px; padding-bottom: 16px; border-bottom: 2px solid #f0f0f0;">
                <div>
                    <h2 style="font-family: 'Bricolage Grotesque', sans-serif; font-size: 1.2rem; color: var(--birutua); margin: 0 0 4px;">Kecamatan yang Dilayani</h2>
                    <p style="color: #888; font-size: 0.82rem; margin: 0;">Menentukan pilihan kecamatan di form pemesanan kurir</p>
                </div>
                <button type="button" id="btnEditKecamatan" onclick="bukaEdit('kecamatan')" 
                        style="padding: 8px 20px; background: #DDEEFF; color: var(--birutua); border: none; border-radius: 20px 0 20px 0; font-family: 'DM Sans', sans-serif; font-weight: 600; font-size: 0.85rem; cursor: pointer; box-shadow: var(--shadow);">
                    Edit
                </button>
            </div>

            <div id="viewKecamatan" style="display: flex; gap: 8px; flex-wrap: wrap;">
                <?php if (empty($kecamatan_aktif)): ?>
                    <p style="color: #aaa; font-size: 0.9rem;">Belum ada kecamatan dipilih.</p>
                <?php else: ?>
                    <?php foreach ($kecamatan_aktif as $kec): ?>
                        <span style="background: #DDEEFF; color: var(--birutua); padding: 6px 18px; border-radius: 20px; font-size: 0.85rem; font-weight: 500;"><?= htmlspecialchars($kec) ?></span>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>

            <form method="POST" action="edit-info.php?action=simpan_kurir" id="formKecamatan" style="display: none;">
                <div style="display: grid; grid-template-columns: repeat(auto-fill, minmax(150px, 1fr)); gap: 10px; margin-bottom: 20px;">
                    <?php foreach ($semua_kecamatan as $kec): ?>
                        <label style="display: flex; align-items: center; gap: 8px; background: #f4f7fb; padding: 10px 14px; border-radius: 20px; font-size: 0.88rem; color: #333; cursor: pointer; transition: background-color 0.2s; border: 2px solid transparent;">
                            <input type="checkbox" name="kecamatan[]" value="<?= $kec ?>" <?= in_array($kec, $kecamatan_aktif) ? 'checked' : '' ?> style="width: 16px; height: 16px; accent-color: var(--birutua); cursor: pointer;">
                            <?= $kec ?>
                        </label>
                    <?php endforeach; ?>
                </div>
                <div style="display: flex; gap: 12px;">
                    <button type="submit" style="padding: 10px 28px; background: var(--tealmuda); color: #1a4d3a; border: none; border-radius: 20px 0 20px 0; font-family: 'DM Sans', sans-serif; font-weight: 600; cursor: pointer; box-shadow: var(--shadow);">Simpan</button>
                    <button type="button" onclick="tutupEdit('kecamatan')" style="padding: 10px 28px; background: #f0f0f0; color: #555; border: none; border-radius: 20px 0 20px 0; font-family: 'DM Sans', sans-serif; font-weight: 500; cursor: pointer;">Batal</button>
                </div>
            </form>
        </div>

    </div>
</section>

<script src="../assets/js/info-admin.js"></script>

<?php 
include '../includes/footer.php'; 
?>
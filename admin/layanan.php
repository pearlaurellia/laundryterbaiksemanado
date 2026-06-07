<?php
// 1. Inisialisasi Keamanan & Koneksi Database
require_once '../config/session.php';
require_once '../config/database.php';
require_once '../config/functions.php';
require_once '../includes/admin-check.php'; // Proteksi halaman admin

// ============================================================
// [POST] LOGIKA BACKEND HANDLER - MERESPONS AJAX DARI JS
// ============================================================
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_GET['action'])) {
    header('Content-Type: application/json');
    
    // Membaca kiriman JSON raw body dari fetch JavaScript
    $rawInput = file_get_contents('php://input');
    $input = json_decode($rawInput, true);
    $action = $_GET['action'];

    try {
        // --- AKSI: TAMBAH LAYANAN ---
        if ($action === 'tambah') {
            $nama      = bersihkan($input['nama_layanan'] ?? '');
            $tarif     = filter_var($input['tarif_per_kg'] ?? 0, FILTER_VALIDATE_INT);
            $satuan    = bersihkan($input['satuan'] ?? 'kg');
            $deskripsi = bersihkan($input['deskripsi'] ?? '');
            $durasi    = bersihkan($input['estimasi_hari'] ?? '');

            if (empty($nama) || $tarif === false || $tarif <= 0) {
                echo json_encode(['success' => false, 'message' => 'Nama dan tarif wajib diisi dengan benar.']);
                exit;
            }

            $stmt = $pdo->prepare("INSERT INTO layanan (nama_layanan, tarif_per_kg, satuan, deskripsi, durasi, status) VALUES (?, ?, ?, ?, ?, 'aktif')");
            $stmt->execute([$nama, $tarif, $satuan, $deskripsi, $durasi]);
            
            echo json_encode(['success' => true, 'id' => $pdo->lastInsertId()]);
            exit;
        }

        // --- AKSI: EDIT LAYANAN ---
        if ($action === 'edit' && isset($_GET['id'])) {
            $id        = filter_var($_GET['id'], FILTER_VALIDATE_INT);
            $nama      = bersihkan($input['nama_layanan'] ?? '');
            $tarif     = filter_var($input['tarif_per_kg'] ?? 0, FILTER_VALIDATE_INT);
            $satuan    = bersihkan($input['satuan'] ?? 'kg');
            $deskripsi = bersihkan($input['deskripsi'] ?? '');
            $durasi    = bersihkan($input['estimasi_hari'] ?? '');

            if (!$id || empty($nama) || $tarif === false || $tarif <= 0) {
                echo json_encode(['success' => false, 'message' => 'Validasi gagal, data tidak valid.']);
                exit;
            }

            $stmt = $pdo->prepare("UPDATE layanan SET nama_layanan = ?, tarif_per_kg = ?, satuan = ?, deskripsi = ?, durasi = ? WHERE id = ?");
            $stmt->execute([$nama, $tarif, $satuan, $deskripsi, $durasi, $id]);
            
            echo json_encode(['success' => true]);
            exit;
        }

        // --- AKSI: HAPUS (SOFT DELETE) ---
        if ($action === 'hapus' && isset($_GET['id'])) {
            $id = filter_var($_GET['id'], FILTER_VALIDATE_INT);

            // Batasan Alur Kerja: Cek apakah layanan masih digunakan di pesanan aktif
            $cekPesanan = $pdo->prepare("SELECT COUNT(*) FROM pesanan WHERE id_layanan = ? AND status_pesanan NOT IN ('selesai', 'dibatalkan')");
            $cekPesanan->execute([$id]);
            if ($cekPesanan->fetchColumn() > 0) {
                echo json_encode(['success' => false, 'message' => 'Layanan gagal dihapus karena masih digunakan dalam antrean pesanan aktif.']);
                exit;
            }

            // Soft-delete: Cukup ubah status menjadi nonaktif
            $stmt = $pdo->prepare("UPDATE layanan SET status = 'nonaktif' WHERE id = ?");
            $stmt->execute([$id]);
            
            echo json_encode(['success' => true]);
            exit;
        }

    } catch (PDOException $e) {
        echo json_encode(['success' => false, 'message' => 'Database error: ' . $e->getMessage()]);
        exit;
    }
}

// ============================================================
// [GET] LOGIKA TAMPILAN - MENGAMBIL DATA UNTUK RENDER HALAMAN
// ============================================================
// Admin dapat melihat seluruh layanan (baik yang aktif maupun nonaktif)
$stmt = $pdo->prepare("SELECT * FROM layanan ORDER BY status ASC, id ASC");
$stmt->execute();
$allLayanan = $stmt->fetchAll();

// Panggil header-admin (Menangani kerangka HTML awal, CSS, dan open body)
include '../includes/header-admin.php';
?>

    <section class="halaman-pesanan">

        <!-- SIDEBAR - Tema sama dengan pesanan.php -->
        <div class="pesanan-sidebar">
            <h2 class="judul-sidebar" id="judulFormLayanan">Tambah Layanan</h2>

            <div class="form-layanan" id="formLayanan" style="display: flex; flex-direction: column; gap: 4px; position: relative; z-index: 2;">
                
                <div class="grup-input-form" style="width: 100%; margin-bottom: 12px;">
                    <label class="label-form" style="color: white; font-size: 0.85rem; margin-top: 0;">Nama Layanan</label>
                    <input type="text" class="input-form input-form-sidebar" id="inputNamaLayanan" placeholder="cth: Reguler, Express..." style="width: 100%; margin: 0;">
                </div>

                <div class="grup-input-form" style="width: 100%; margin-bottom: 12px;">
                    <label class="label-form" style="color: white; font-size: 0.85rem; margin-top: 0;">Tarif</label>
                    <div class="input-tarif-wrapper" style="display: flex; align-items: center; background: white; border-radius: 20px 0 20px 0; box-shadow: var(--shadow); overflow: hidden;">
                        <span class="prefix-tarif" style="padding: 0 12px; color: #888; font-weight: 500;">Rp</span>
                        <input type="number" class="input-tarif" id="inputTarifLayanan" placeholder="0" min="0" step="500" style="flex: 1; padding: 12px 16px; border: none; border-left: 1px solid #eee; font-family: 'DM Sans', sans-serif; font-size: 0.95rem; outline: none;">
                    </div>
                </div>

                <div class="grup-input-form" style="width: 100%; margin-bottom: 12px;">
                    <label class="label-form" style="color: white; font-size: 0.85rem; margin-top: 0;">Satuan</label>
                    <select class="input-form input-form-sidebar" id="inputSatuanLayanan" style="width: 100%; margin: 0; appearance: none; background-image: url('data:image/svg+xml,%3Csvg xmlns=%22http://www.w3.org/2000/svg%22 width=%2212%22 height=%228%22 viewBox=%220 0 12 8%22%3E%3Cpath d=%22M1 1l5 5 5-5%22 stroke=%22%23888%22 stroke-width=%221.5%22 fill=%22none%22 stroke-linecap=%22round%22/%3E%3C/svg%3E'); background-repeat: no-repeat; background-position: right 16px center; cursor: pointer;">
                        <option value="kg">per Kg</option>
                        <option value="item">per Item</option>
                    </select>
                </div>

                <div class="grup-input-form" style="width: 100%; margin-bottom: 12px;">
                    <label class="label-form" style="color: white; font-size: 0.85rem; margin-top: 0;">Deskripsi Singkat</label>
                    <input type="text" class="input-form input-form-sidebar" id="inputDeskripsiLayanan" placeholder="cth: Paket lengkap dan terjangkau" style="width: 100%; margin: 0;">
                </div>

                <div class="grup-input-form" style="width: 100%; margin-bottom: 16px;">
                    <label class="label-form" style="color: white; font-size: 0.85rem; margin-top: 0;">Estimasi Durasi</label>
                    <input type="text" class="input-form input-form-sidebar" id="inputDurasiLayanan" placeholder="cth: 1-2 hari, 6-8 jam" style="width: 100%; margin: 0;">
                </div>

                <div class="tombol-form-layanan" style="display: flex; gap: 12px; margin-top: 8px; position: relative; z-index: 2;">
                    <button type="button" class="tombol-submit-form" id="tombolSimpan" onclick="simpanLayanan()" style="flex: 1; padding: 12px; font-size: 0.9rem; background: white; color: var(--birutua); border: none; border-radius: 20px 0 20px 0; font-weight: 600; cursor: pointer; box-shadow: var(--shadow);">Simpan</button>
                    <button type="button" class="tombol-batal-layanan" id="tombolBatal" onclick="resetForm()" style="display:none; padding: 12px 20px; font-size: 0.9rem; background: rgba(255,255,255,0.2); color: white; border: 1px solid rgba(255,255,255,0.4); border-radius: 20px 0 20px 0; cursor: pointer;">Batal</button>
                </div>

            </div>
        </div>

        <!-- KANAN - Daftar Layanan -->
        <div class="layanan-kanan">
            <div class="layanan-kanan-header">
                <h2 class="judul-layanan-kanan">Daftar Layanan</h2>
                <p class="subjudul-layanan-kanan">Perubahan di sini otomatis memengaruhi halaman publik dan form pemesanan member.</p>
            </div>

            <div class="kartu-layanan-admin-container" id="containerLayanan">
                <?php foreach ($allLayanan as $l): 
                    $isNonaktif = ($l['status'] === 'nonaktif');
                ?>
                    <div class="kartu-layanan-admin" 
                         style="<?= $isNonaktif ? 'opacity: 0.6; background: #e5e7eb;' : '' ?>"
                         data-id="<?= $l['id'] ?>"
                         data-nama="<?= htmlspecialchars($l['nama_layanan']) ?>"
                         data-tarif="<?= (int)$l['tarif_per_kg'] ?>"
                         data-satuan="<?= htmlspecialchars($l['satuan']) ?>"
                         data-deskripsi="<?= htmlspecialchars($l['deskripsi']) ?>"
                         data-durasi="<?= htmlspecialchars($l['durasi'] ?? '') ?>">

                        <div class="kartu-layanan-admin-header" style="<?= $isNonaktif ? 'background: #d1d5db;' : '' ?>">
                            <span class="kartu-layanan-admin-nama">
                                <?= htmlspecialchars($l['nama_layanan']) ?> 
                                <?php if ($isNonaktif): ?>
                                    <small style="color:#ef4444; font-weight:600;">(Nonaktif)</small>
                                <?php endif; ?>
                            </span>
                            <span class="kartu-layanan-admin-tarif">Rp <?= number_format($l['tarif_per_kg'], 0, ',', '.') ?> / <?= htmlspecialchars($l['satuan']) ?></span>
                        </div>

                        <div class="kartu-layanan-admin-body">
                            <p class="kartu-layanan-admin-deskripsi"><?= htmlspecialchars($l['deskripsi'] ?: '-') ?></p>
                            <div class="kartu-layanan-admin-detail">
                                <?php if ($l['satuan'] === 'kg'): ?>
                                    <span class="badge-hijau">Cuci</span>
                                    <span class="badge-hijau">Kering</span>
                                    <span class="badge-hijau">Setrika</span>
                                <?php else: ?>
                                    <span class="badge-hijau">Dry Clean</span>
                                <?php endif; ?>
                                <span class="badge-biru">
                                    <?= htmlspecialchars($l['durasi'] ?: '-') ?>
                                </span>
                            </div>
                        </div>

                        <div class="kartu-layanan-admin-aksi">
                            <button class="tombol-edit-layanan" onclick="editLayanan(this.closest('.kartu-layanan-admin'))">Edit</button>
                            <?php if (!$isNonaktif): ?>
                                <button class="tombol-hapus-layanan" onclick="hapusLayanan(this.closest('.kartu-layanan-admin'))">Hapus</button>
                            <?php endif; ?>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>

            <div class="layanan-kosong" id="layananKosong" style="display: <?= count($allLayanan) === 0 ? 'block' : 'none' ?>;">
                <p>Belum ada layanan. Tambah layanan baru di panel kiri.</p>
            </div>
        </div>

    </section>

    <script src="../assets/js/layanan-admin.js"></script>

<?php 
// Panggil footer untuk menutup tag body dan html secara valid
include '../includes/footer.php'; 
?>
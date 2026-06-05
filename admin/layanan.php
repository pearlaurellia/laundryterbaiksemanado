<?php
require_once '../config/session.php';
require_once '../config/database.php';
require_once '../config/functions.php';

// 2. BACKEND HANDLER: Memproses Request API dari Fetch JavaScript
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Membaca payload JSON dari JavaScript
    $inputRaw = file_get_contents('php://input');
    $data = json_decode($inputRaw, true);

    if ($data && isset($data['action'])) {
        header('Content-Type: application/json');
        
        $action = $data['action'];
        $nama = isset($data['nama_layanan']) ? trim($data['nama_layanan']) : '';
        $tarif = isset($data['tarif_per_kg']) ? intval($data['tarif_per_kg']) : 0;
        $satuan = isset($data['satuan']) ? trim($data['satuan']) : 'kg';
        $deskripsi = isset($data['deskripsi']) ? trim($data['deskripsi']) : '';
        $durasi = isset($data['durasi']) ? trim($data['durasi']) : '';
        $id = isset($data['id']) ? intval($data['id']) : 0;

        // --- ACTION: TAMBAH ---
        if ($action === 'tambah') {
            if (empty($nama) || $tarif <= 0) {
                echo json_encode(['sukses' => false, 'pesan' => 'Nama dan tarif valid wajib diisi.']);
                exit;
            }
            try {
                $stmt = $pdo->prepare("INSERT INTO layanan (nama_layanan, tarif_per_kg, satuan, deskripsi, durasi, status) VALUES (?, ?, ?, ?, ?, 'aktif')");
                $stmt->execute([$nama, $tarif, $satuan, $deskripsi, $durasi]);
                echo json_encode(['sukses' => true, 'id' => $pdo->lastInsertId()]);
            } catch (Exception $e) {
                echo json_encode(['sukses' => false, 'pesan' => 'Gagal menyimpan database: ' . $e->getMessage()]);
            }
            exit;
        }

        // --- ACTION: EDIT ---
        if ($action === 'edit') {
            if (empty($id) || empty($nama) || $tarif <= 0) {
                echo json_encode(['sukses' => false, 'pesan' => 'Data tidak lengkap atau tarif tidak valid.']);
                exit;
            }
            try {
                $stmt = $pdo->prepare("UPDATE layanan SET nama_layanan = ?, tarif_per_kg = ?, satuan = ?, deskripsi = ?, durasi = ? WHERE id = ?");
                $stmt->execute([$nama, $tarif, $satuan, $deskripsi, $durasi, $id]);
                echo json_encode(['sukses' => true]);
            } catch (Exception $e) {
                echo json_encode(['sukses' => false, 'pesan' => 'Gagal memperbarui database.']);
            }
            exit;
        }

        // --- ACTION: HAPUS (SOFT DELETE) ---
        if ($action === 'hapus') {
            if (empty($id)) {
                echo json_encode(['sukses' => false, 'pesan' => 'ID tidak valid.']);
                exit;
            }
            try {
                // Validasi: Cek apakah layanan sedang dipakai di pesanan aktif
                $stmtCek = $pdo->prepare("SELECT COUNT(*) FROM pesanan WHERE id_layanan = ? AND status_pesanan NOT IN ('selesai', 'dibatalkan')");
                $stmtCek->execute([$id]);
                $masihDipakai = $stmtCek->fetchColumn();

                if ($masihDipakai > 0) {
                    echo json_encode(['sukses' => false, 'pesan' => 'Layanan gagal dinonaktifkan karena sedang digunakan oleh pesanan aktif berjalan.']);
                    exit;
                }

                // Jalankan Soft Delete jika aman
                $stmtHapus = $pdo->prepare("UPDATE layanan SET status = 'nonaktif' WHERE id = ?");
                $stmtHapus->execute([$id]);
                echo json_encode(['sukses' => true]);
            } catch (Exception $e) {
                echo json_encode(['sukses' => false, 'pesan' => 'Gagal menghapus data.']);
            }
            exit;
        }
    }
}

// 3. FRONTEND DATA FETCHING: Ambil semua data layanan yang berstatus aktif
$query = $pdo->query("SELECT * FROM layanan WHERE status = 'aktif' ORDER BY id ASC");
$daftarLayanan = $query->fetchAll();
?>

    <?php include '../includes/header-admin.php'; ?>

    <section class="halaman-layanan">

        <div class="layanan-sidebar">
            <h2 class="judul-sidebar" id="judulFormLayanan">Tambah Layanan</h2>
            <div class="form-layanan" id="formLayanan">

                <div class="grup-input-form">
                    <label class="label-form">Nama Layanan</label>
                    <input type="text" class="input-form input-form-sidebar" id="inputNamaLayanan" placeholder="cth: Reguler, Express...">
                </div>

                <div class="grup-input-form">
                    <label class="label-form">Tarif</label>
                    <div class="input-tarif-wrapper">
                        <span class="prefix-tarif">Rp</span>
                        <input type="number" class="input-form input-tarif" id="inputTarifLayanan" placeholder="0" min="0" step="500">
                    </div>
                </div>

                <div class="grup-input-form">
                    <label class="label-form">Satuan</label>
                    <select class="input-form input-form-sidebar" id="inputSatuanLayanan">
                        <option value="kg">per Kg</option>
                        <option value="item">per Item</option>
                    </select>
                </div>

                <div class="grup-input-form">
                    <label class="label-form">Deskripsi Singkat</label>
                    <input type="text" class="input-form input-form-sidebar" id="inputDeskripsiLayanan" placeholder="cth: Paket lengkap dan terjangkau">
                </div>

                <div class="grup-input-form">
                    <label class="label-form">Estimasi Durasi</label>
                    <input type="text" class="input-form input-form-sidebar" id="inputDurasiLayanan" placeholder="cth: 1-2 hari, 6-8 jam">
                </div>

                <div class="tombol-form-layanan">
                    <button class="tombol-submit-form" id="tombolSimpan" onclick="simpanLayanan()">Simpan</button>
                    <button class="tombol-batal-layanan" id="tombolBatal" onclick="resetForm()" style="display:none;">Batal</button>
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
                <?php if (!empty($daftarLayanan)): ?>
                    <?php foreach ($daftarLayanan as $l): ?>
                        <div class="kartu-layanan-admin"
                             data-id="<?= $l['id'] ?>"
                             data-nama="<?= htmlspecialchars($l['nama_layanan']) ?>"
                             data-tarif="<?= $l['tarif_per_kg'] ?>"
                             data-satuan="<?= htmlspecialchars($l['satuan']) ?>"
                             data-deskripsi="<?= htmlspecialchars($l['deskripsi']) ?>"
                             data-durasi="<?= htmlspecialchars($l['durasi']) ?>">

                            <div class="kartu-layanan-admin-header">
                                <span class="kartu-layanan-admin-nama"><?= htmlspecialchars($l['nama_layanan']) ?></span>
                                <span class="kartu-layanan-admin-tarif">Rp <?= number_format($l['tarif_per_kg'], 0, ',', '.') ?> / <?= htmlspecialchars($l['satuan']) ?></span>
                            </div>

                            <div class="kartu-layanan-admin-body">
                                <p class="kartu-layanan-admin-deskripsi"><?= htmlspecialchars($l['deskripsi'] ?: '—') ?></p>
                                <div class="kartu-layanan-admin-detail">
                                    <span class="badge-hijau">Cuci</span>
                                    <span class="badge-biru"><?= htmlspecialchars($l['durasi'] ?: '—') ?></span>
                                </div>
                            </div>

                            <div class="kartu-layanan-admin-aksi">
                                <button class="tombol-edit-layanan" onclick="editLayanan(this.closest('.kartu-layanan-admin'))">Edit</button>
                                <button class="tombol-hapus-layanan" onclick="hapusLayanan(this.closest('.kartu-layanan-admin'))">Hapus</button>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>

            <div class="layanan-kosong" id="layananKosong" style="display: <?= empty($daftarLayanan) ? 'block' : 'none' ?>;">
                <p>Belum ada layanan. Tambah layanan baru di panel kiri.</p>
            </div>
        </div>

    </section>

    <script src="../assets/js/layanan-admin.js"></script>
    <?php include '../includes/footer.php'; ?>
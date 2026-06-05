<?php
require_once '../config/session.php';
require_once '../config/database.php';
require_once '../config/functions.php';
require_once '../includes/admin-check.php';

// =========================================================================
// [BACKEND API HANDLER] - Menangani AJAX Fetch Request (POST & GET)
// =========================================================================

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    header('Content-Type: application/json');
    $input = json_decode(file_get_contents('php://input'), true) ?? $_POST;
    $action = $input['action'] ?? '';
    $id_pesanan = $input['id'] ?? null;

    if (!$id_pesanan) {
        echo json_encode(['sukses' => false, 'pesan' => 'ID Pesanan tidak valid.']);
        exit;
    }

    try {
        switch ($action) {
            case 'konfirmasi':
                $pdo->beginTransaction();
                $stmt = $pdo->prepare("SELECT status_pesanan FROM pesanan WHERE id = ?");
                $stmt->execute([$id_pesanan]);
                $status_lama = $stmt->fetchColumn();

                $stmt = $pdo->prepare("UPDATE pesanan SET status_pesanan = 'dikonfirmasi', sudah_dilihat_member = 0 WHERE id = ? AND status_pesanan = 'menunggu_konfirmasi'");
                $stmt->execute([$id_pesanan]);

                if ($stmt->rowCount() > 0) {
                    $stmt = $pdo->prepare("INSERT INTO riwayat_status (id_pesanan, status_lama, status_baru, dilakukan_oleh, keterangan) VALUES (?, ?, 'dikonfirmasi', 'admin', 'Pesanan telah dikonfirmasi oleh Admin')");
                    $stmt->execute([$id_pesanan, $status_lama]);
                    
                    $pdo->commit();
                    echo json_encode(['sukses' => true, 'pesan' => 'Pesanan berhasil dikonfirmasi.']);
                } else {
                    $pdo->rollBack();
                    echo json_encode(['sukses' => false, 'pesan' => 'Gagal konfirmasi atau status sudah berubah.']);
                }
                exit;

            case 'proses_timbang':
                $berat_aktual = floatval($input['berat_aktual'] ?? 0);
                if ($berat_aktual <= 0) {
                    echo json_encode(['sukses' => false, 'pesan' => 'Berat harus lebih dari 0 kg.']);
                    exit;
                }

                $pdo->beginTransaction();
                $stmt = $pdo->prepare("SELECT l.tarif_per_kg, p.biaya_kurir, p.status_pesanan FROM pesanan p JOIN layanan l ON p.id_layanan = l.id WHERE p.id = ?");
                $stmt->execute([$id_pesanan]);
                $data = $stmt->fetch();

                if (!$data || $data['status_pesanan'] !== 'dikonfirmasi') {
                    $pdo->rollBack();
                    echo json_encode(['sukses' => false, 'pesan' => 'Status pesanan tidak valid untuk ditimbang.']);
                    exit;
                }

                $total_harga = ($berat_aktual * $data['tarif_per_kg']) + $data['biaya_kurir'];

                $stmt = $pdo->prepare("UPDATE pesanan SET berat_aktual = ?, total_harga = ?, status_pesanan = 'sedang_dicuci', sudah_dilihat_member = 0 WHERE id = ?");
                $stmt->execute([$berat_aktual, $total_harga, $id_pesanan]);

                $stmt = $pdo->prepare("INSERT INTO riwayat_status (id_pesanan, status_lama, status_baru, dilakukan_oleh, keterangan) VALUES (?, 'dikonfirmasi', 'sedang_dicuci', 'admin', 'Pakaian telah ditimbang. Proses cuci dimulai.')");
                $stmt->execute([$id_pesanan]);

                $pdo->commit();
                echo json_encode(['sukses' => true, 'total_harga' => $total_harga, 'pesan' => 'Berat disimpan, pakaian sedang dicuci.']);
                exit;

            case 'update_status':
                $status_baru = $input['status_baru'] ?? '';
                $whitelist = ['siap_diambil', 'sedang_diantar', 'selesai'];

                if (!in_array($status_baru, $whitelist)) {
                    echo json_encode(['sukses' => false, 'pesan' => 'Perubahan status tidak diizinkan.']);
                    exit;
                }

                $pdo->beginTransaction();
                $stmt = $pdo->prepare("SELECT status_pesanan FROM pesanan WHERE id = ?");
                $stmt->execute([$id_pesanan]);
                $status_lama = $stmt->fetchColumn();

                $status_bayar_sql = ($status_baru === 'selesai') ? ", status_pembayaran = 'lunas'" : "";

                $stmt = $pdo->prepare("UPDATE pesanan SET status_pesanan = ? $status_bayar_sql, sudah_dilihat_member = 0 WHERE id = ?");
                $stmt->execute([$status_baru, $id_pesanan]);

                $stmt = $pdo->prepare("INSERT INTO riwayat_status (id_pesanan, status_lama, status_baru, dilakukan_oleh, keterangan) VALUES (?, ?, ?, 'admin', 'Status diperbarui oleh admin.')");
                $stmt->execute([$id_pesanan, $status_lama, $status_baru]);

                $pdo->commit();
                echo json_encode(['sukses' => true, 'pesan' => 'Status berhasil diperbarui.']);
                exit;

            case 'batalkan':
                $alasan = $input['alasan'] ?? 'Dibatalkan oleh admin.';
                $tandai_fiktif = intval($input['tandai_fiktif'] ?? 0);

                $pdo->beginTransaction();
                $stmt = $pdo->prepare("SELECT id_member, status_pesanan FROM pesanan WHERE id = ?");
                $stmt->execute([$id_pesanan]);
                $pesanan = $stmt->fetch();

                if (!$pesanan) {
                    $pdo->rollBack();
                    echo json_encode(['sukses' => false, 'pesan' => 'Pesanan tidak ditemukan.']);
                    exit;
                }

                $stmt = $pdo->prepare("UPDATE pesanan SET status_pesanan = 'dibatalkan', alasan_pembatalan = ?, dibatalkan_oleh = 'admin', sudah_dilihat_member = 0 WHERE id = ?");
                $stmt->execute([$alasan, $id_pesanan]);

                $stmt = $pdo->prepare("INSERT INTO riwayat_status (id_pesanan, status_lama, status_baru, dilakukan_oleh, keterangan) VALUES (?, ?, 'dibatalkan', 'admin', ?)");
                $stmt->execute([$id_pesanan, $pesanan['status_pesanan'], $alasan]);

                if ($tandai_fiktif === 1) {
                    $stmt = $pdo->prepare("UPDATE users SET status_akun = 'nonaktif', alasan_banned = ? WHERE id = ?");
                    $stmt->execute([$alasan, $pesanan['id_member']]);
                }

                $pdo->commit();
                echo json_encode(['sukses' => true, 'pesan' => 'Pesanan dibatalkan.']);
                exit;
        }
    } catch (Exception $e) {
        if ($pdo->inTransaction()) $pdo->rollBack();
        echo json_encode(['sukses' => false, 'pesan' => 'Error: ' . $e->getMessage()]);
        exit;
    }
}

if (isset($_GET['action'])) {
    header('Content-Type: application/json');
    if ($_GET['action'] === 'get_list') {
        $filter = $_GET['status'] ?? 'semua';
        $sql = "SELECT p.*, u.nama, l.nama_layanan FROM pesanan p 
                JOIN users u ON p.id_member = u.id 
                JOIN layanan l ON p.id_layanan = l.id";
        if ($filter !== 'semua') $sql .= " WHERE p.status_pesanan = :status";
        $sql .= " ORDER BY p.created_at DESC";
        
        $stmt = $pdo->prepare($sql);
        if ($filter !== 'semua') $stmt->bindValue(':status', $filter);
        $stmt->execute();
        echo json_encode($stmt->fetchAll() ?: []);
        exit;
    }

    if ($_GET['action'] === 'get_detail' && isset($_GET['id'])) {
        $stmt = $pdo->prepare("SELECT p.*, u.nama, u.email, u.no_hp, l.nama_layanan, l.tarif_per_kg 
                               FROM pesanan p 
                               JOIN users u ON p.id_member = u.id 
                               JOIN layanan l ON p.id_layanan = l.id 
                               WHERE p.id = ?");
        $stmt->execute([$_GET['id']]);
        $detail = $stmt->fetch();

        if ($detail) {
            $stmtR = $pdo->prepare("SELECT * FROM riwayat_status WHERE id_pesanan = ? ORDER BY changed_at ASC");
            $stmtR->execute([$_GET['id']]);
            $detail['riwayat'] = $stmtR->fetchAll() ?: [];
            echo json_encode(['sukses' => true, 'data' => $detail]);
        } else {
            echo json_encode(['sukses' => false]);
        }
        exit;
    }
}

?>
    <?php include '../includes/header-admin.php'; ?>

    <section class="halaman-pesanan" id="viewList">
        <div class="pesanan-sidebar">
            <h2 class="judul-sidebar">Daftar Pesanan</h2>
            <div class="grup-filter">
                <button class="tombol-filter aktif" onclick="filterPesanan('semua', this)">Semua</button>
                <button class="tombol-filter" onclick="filterPesanan('menunggu_konfirmasi', this)">Menunggu</button>
                <button class="tombol-filter" onclick="filterPesanan('dikonfirmasi', this)">Dikonfirmasi</button>
                <button class="tombol-filter" onclick="filterPesanan('sedang_dicuci', this)">Diproses</button>
                <button class="tombol-filter" onclick="filterPesanan('selesai', this)">Selesai</button>
            </div>
            <div class="list-pesanan" id="listPesanan"></div>
        </div>

        <div class="pesanan-detail" id="pesananDetail">
            <div class="detail-kosong" id="detailKosong">
                <div style="width:80px;height:80px;border-radius:50%;background:rgba(13,63,138,0.08);margin-bottom:20px;"></div>
                <p style="color:#aaa;">Pilih pesanan untuk melihat detail</p>
            </div>

            <div class="detail-isi" id="detailIsi" style="display:none;">
                <button class="tombol-kembali" onclick="kembaliKeList()">← Kembali</button>

                <div class="detail-header">
                    <div>
                        <h2 class="detail-nama" id="detailNama">—</h2>
                        <p class="detail-username" id="detailUsername">—</p>
                    </div>
                    <div class="detail-waktu-badge" id="detailWaktu">—</div>
                </div>

                <div class="grup-keterangan" id="detailTags" style="margin-bottom:20px; flex-wrap:wrap; display:flex; gap:5px;"></div>

                <div class="detail-info-grid">
                    <div class="detail-info-blok"><p class="detail-label">Nama Lengkap</p><p class="detail-nilai" id="detailNamaLengkap">—</p></div>
                    <div class="detail-info-blok"><p class="detail-label">Nomor Telepon</p><p class="detail-nilai" id="detailTelpon">—</p></div>
                    <div class="detail-info-blok"><p class="detail-label">Alamat Lengkap</p><p class="detail-nilai" id="detailAlamat">—</p></div>
                    <div class="detail-info-blok"><p class="detail-label">Kecamatan</p><p class="detail-nilai" id="detailKecamatan">—</p></div>
                    <div class="detail-info-blok"><p class="detail-label">Layanan</p><p class="detail-nilai" id="detailLayanan">—</p></div>
                    <div class="detail-info-blok"><p class="detail-label">Pengiriman</p><p class="detail-nilai" id="detailPengiriman">—</p></div>
                </div>

                <div class="detail-catatan-wrapper">
                    <p class="detail-label">Catatan dari Member</p>
                    <p class="detail-catatan-isi" id="detailNote">—</p>
                </div>

                <div class="detail-berat-biaya">
                    <div class="kartu-berat" id="blokInputBerat" style="display:none;">
                        <p class="detail-label">Input Berat Aktual</p>
                        <div class="input-berat-wrapper">
                            <input type="number" class="input-berat" id="inputBerat" placeholder="0" min="0" step="0.1" oninput="hitungBiaya()">
                            <span class="satuan-berat">kg</span>
                        </div>
                        <p class="input-berat-hint">* Tarif dihitung otomatis</p>
                    </div>

                    <div class="kartu-biaya">
                        <p class="detail-label">Rincian Biaya</p>
                        <p class="rincian-baris" id="rincianLayanan">Layanan : Rp 0</p>
                        <p class="rincian-baris" id="rincianKirim">Pengiriman : Rp 0</p>
                        <p class="rincian-total" id="rincianTotal">Total : Rp 0</p>
                    </div>
                </div>

                <div class="detail-status-section">
                    <p class="detail-label" style="margin-bottom:12px;">Aksi Pesanan</p>
                    <div id="grupAksiAdmin" class="tombol-status-group"></div>
                    <p class="status-aktif-teks" style="margin-top:10px;">
                        Status saat ini: <strong id="statusAktifTeks">—</strong>
                    </p>

                    <div style="margin-top:20px; padding-top:16px; border-top:1px solid rgba(13,63,138,0.1);">
                        <button class="tombol-batalkan-status" id="tombolBatalkanAdmin" onclick="bukaPopupBatalAdmin()" style="display:none;">
                            Batalkan Pesanan Ini
                        </button>
                        <p class="status-aktif-teks" id="infoSudahDibatalkan" style="display:none; color:#f87171; font-weight:600;">
                            ✕ Pesanan ini sudah dibatalkan
                        </p>
                    </div>
                </div>

                <div class="timeline-box">
                    <p class="detail-label">Timeline Riwayat Status</p>
                    <div id="timelineKonten" style="font-size:0.85rem; line-height:1.6;"></div>
                </div>
            </div>
        </div>

        <div id="overlayBatalAdmin" class="overlay-popup" style="display:none;" onclick="tutupPopupBatalAdmin()"></div>
        <div id="popupBatalAdmin" class="popup-konfirmasi" style="display:none; width:440px; max-width:92vw;">
            <h3 class="popup-judul">Batalkan Pesanan?</h3>
            <div style="margin-bottom:20px;">
                <p class="detail-label" style="margin-bottom:12px;">Pilih Alasan Pembatalan</p>
                <div style="display:flex; flex-direction:column; gap:8px;">
                    <label class="kartu-alasan"><input type="radio" name="alasanBatal" value="Kuota laundry hari ini penuh, silakan pesan kembali besok." checked> 📦 Laundry Penuh</label>
                    <label class="kartu-alasan"><input type="radio" name="alasanBatal" value="Pesanan terindikasi fiktif / pengguna tidak dapat dihubungi."> ⚠️ Pesanan Fiktif</label>
                    <label class="kartu-alasan"><input type="radio" name="alasanBatal" value="Alamat pengantaran tidak valid atau di luar jangkauan kurir."> 📍 Alamat Tidak Valid</label>
                    <label class="kartu-alasan"><input type="radio" name="alasanBatal" value="lainnya"> 📝 Lainnya</label>
                </div>
                <div id="wrapperAlasanLainnya" style="display:none; margin-top:10px;">
                    <input type="text" id="inputAlasanLainnya" placeholder="Tulis alasan di sini..." style="width:100%; padding:10px; border:1px solid #e0e0e0; border-radius:10px;">
                </div>
            </div>
            <div class="popup-tombol-group">
                <button class="popup-tombol-batal" onclick="tutupPopupBatalAdmin()">Tidak</button>
                <button class="popup-tombol-konfirm" onclick="eksekusiBatalAdmin()">Ya, Batalkan</button>
            </div>
        </div>
    </section>

    <script src="../assets/js/main.js"></script>
    <script src="../assets/js/status.refresh.js"></script>
    <script src="../assets/js/kalkulasi-harga.js"></script>
    <script src="../assets/js/form-validation.js"></script>

        <?php include '../includes/footer.php'; ?>
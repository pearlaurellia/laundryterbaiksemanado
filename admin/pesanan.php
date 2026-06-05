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
    
    // Sinkronisasi: Ambil ID dari URL Query Parameter sesuai dengan Fetch URL main.js
    $action = $_GET['action'] ?? '';
    $id_pesanan = (int)($_GET['id'] ?? 0);

    $input = json_decode(file_get_contents('php://input'), true) ?: [];
    $status_target = $input['status'] ?? ''; // Menyesuaikan nama parameter payload main.js
    $alasan = $input['alasan'] ?? 'Dibatalkan oleh admin.';
    $berat_aktual = isset($input['berat']) ? floatval($input['berat']) : 0;

    if (!$id_pesanan) {
        echo json_encode(['success' => false, 'message' => 'ID Pesanan tidak valid atau tidak ditemukan.']);
        exit;
    }

    try {
        if ($action === 'update_status') {
            $pdo->beginTransaction();
            
            // Ambil status lama untuk keperluan pencatatan riwayat_status
            $stmt = $pdo->prepare("SELECT status_pesanan FROM pesanan WHERE id = ?");
            $stmt->execute([$id_pesanan]);
            $status_lama = $stmt->fetchColumn();

            // KONDISI 1: ADMIN KONFIRMASI TERIMA PESANAN BARU
            if ($status_target === 'dikonfirmasi') {
                $stmt = $pdo->prepare("UPDATE pesanan SET status_pesanan = 'dikonfirmasi', sudah_dilihat_member = 0 WHERE id = ?");
                $stmt->execute([$id_pesanan]);

                $stmt = $pdo->prepare("INSERT INTO riwayat_status (id_pesanan, status_lama, status_baru, dilakukan_oleh, keterangan) VALUES (?, ?, 'dikonfirmasi', 'admin', 'Pesanan telah dikonfirmasi oleh Admin')");
                $stmt->execute([$id_pesanan, $status_lama]);
                
            // KONDISI 2: ADMIN KLIK SIMPAN BERAT & MULAI CUCI
            } elseif ($status_target === 'sedang_dicuci') {
                if ($berat_aktual <= 0) {
                    echo json_encode(['success' => false, 'message' => 'Berat harus lebih dari 0 kg.']);
                    exit;
                }

                $stmt = $pdo->prepare("SELECT l.tarif_per_kg, p.biaya_kurir FROM pesanan p JOIN layanan l ON p.id_layanan = l.id WHERE p.id = ?");
                $stmt->execute([$id_pesanan]);
                $data = $stmt->fetch();

                $total_harga = ($berat_aktual * $data['tarif_per_kg']) + $data['biaya_kurir'];

                $stmt = $pdo->prepare("UPDATE pesanan SET berat_aktual = ?, total_harga = ?, status_pesanan = 'sedang_dicuci', sudah_dilihat_member = 0 WHERE id = ?");
                $stmt->execute([$berat_aktual, $total_harga, $id_pesanan]);

                $stmt = $pdo->prepare("INSERT INTO riwayat_status (id_pesanan, status_lama, status_baru, dilakukan_oleh, keterangan) VALUES (?, ?, 'sedang_dicuci', 'admin', 'Pakaian telah ditimbang. Proses cuci dimulai.')");
                $stmt->execute([$id_pesanan, $status_lama]);

            // KONDISI 3: ADMIN MEMBATALKAN PESANAN MEMBER
            } elseif ($status_target === 'dibatalkan') {
                $stmt = $pdo->prepare("UPDATE pesanan SET status_pesanan = 'dibatalkan', alasan_pembatalan = ?, dibatalkan_oleh = 'admin', sudah_dilihat_member = 0 WHERE id = ?");
                $stmt->execute([$alasan, $id_pesanan]);

                $stmt = $pdo->prepare("INSERT INTO riwayat_status (id_pesanan, status_lama, status_baru, dilakukan_oleh, keterangan) VALUES (?, ?, 'dibatalkan', 'admin', ?)");
                $stmt->execute([$id_pesanan, $status_lama, $alasan]);

            // KONDISI 4: PERUBAHAN LOGISTIK LANJUTAN (SIAP DIAMPIL, DIANTAR, SELESAI)
            } else {
                $status_bayar_sql = ($status_target === 'selesai') ? ", status_pembayaran = 'lunas'" : "";
                $stmt = $pdo->prepare("UPDATE pesanan SET status_pesanan = ? $status_bayar_sql, sudah_dilihat_member = 0 WHERE id = ?");
                $stmt->execute([$status_target, $id_pesanan]);

                $stmt = $pdo->prepare("INSERT INTO riwayat_status (id_pesanan, status_lama, status_baru, dilakukan_oleh, keterangan) VALUES (?, ?, ?, 'admin', 'Status diperbarui oleh admin.')");
                $stmt->execute([$id_pesanan, $status_lama, $status_target]);
            }

            $pdo->commit();
            echo json_encode(['success' => true, 'message' => 'Status berhasil diperbarui.']);
            exit;
        }
    } catch (Exception $e) {
        if ($pdo->inTransaction()) $pdo->rollBack();
        echo json_encode(['success' => false, 'message' => 'Error: ' . $e->getMessage()]);
        exit;
    }
}

// Handler GET Requests (Sinkronisasi Endpoint Jaringan)
if (isset($_GET['action'])) {
    header('Content-Type: application/json');

    // Menjawab permintaan pemuatan database massal dari main.js
    // Menjawab permintaan pemuatan database massal dari main.js
    if ($_GET['action'] === 'ambil_semua') {
        // PERBAIKAN: Kolom u.username dihapus dari SELECT karena tidak ada di DB kamu
        $stmt = $pdo->query("
            SELECT p.*, u.nama, u.email, u.no_hp, l.nama_layanan, l.tarif_per_kg 
            FROM pesanan p 
            JOIN users u ON p.id_member = u.id 
            JOIN layanan l ON p.id_layanan = l.id
            ORDER BY p.created_at DESC
        ");
        $data = $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];
        
        $pesanan_format = [];
        foreach ($data as $p) {
            $pesanan_format[$p['id']] = [
                'id' => $p['id'],
                'kode' => $p['kode_pesanan'],
                'nama' => $p['nama'],
                // SINKRONISASI: Jadikan data email sebagai pengganti username di halaman admin
                'username' => $p['email'], 
                'waktu' => date('d M, H:i', strtotime($p['created_at'])),
                'status' => $p['status_pesanan'],
                'namaLengkap' => $p['nama'],
                'alamat' => $p['alamat_pengantaran'],
                'kecamatan' => $p['kecamatan'],
                'telpon' => $p['no_hp'],
                'layanan' => $p['nama_layanan'],
                'tarifLayanan' => (float)$p['tarif_per_kg'],
                'pengiriman' => $p['opsi_pengantaran'] === 'kurir' ? 'Kurir Laundry' : 'Ambil Sendiri',
                'tarifKirim' => (float)$p['biaya_kurir'],
                'note' => $p['catatan_khusus'],
                'berat' => $p['berat_aktual'] > 0 ? (float)$p['berat_aktual'] : null,
                'opsi' => $p['opsi_pengantaran'],
                'tags' => [] 
            ];
        }
        echo json_encode(['success' => true, 'data' => $pesanan_format]);
        exit;
    }
    
    // Mengambil riwayat aktivitas khusus untuk mencetak timeline pesanan aktif
    if ($_GET['action'] === 'get_timeline' && isset($_GET['id'])) {
        $stmtR = $pdo->prepare("SELECT * FROM riwayat_status WHERE id_pesanan = ? ORDER BY changed_at DESC");
        $stmtR->execute([$_GET['id']]);
        $riwayat = $stmtR->fetchAll(PDO::FETCH_ASSOC) ?: [];
        echo json_encode(['success' => true, 'data' => $riwayat]);
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
                <div class="kartu-berat" id="blokInputBerat" style="display:none; margin-bottom: 15px;">
                    <p class="detail-label" style="color: #0d3f8a; font-weight: bold;">Input Berat Aktual Cucian</p>
                    <div class="input-berat-wrapper" style="display: flex; align-items: center; gap: 8px;">
                        <input type="number" class="input-berat" id="inputBerat" placeholder="0" min="0" step="0.1" oninput="hitungBiaya()" style="padding: 8px; border-radius: 6px; border: 1px solid #ccc; width: 100px;">
                        <span class="satuan-berat" style="color: white; font-weight: bold;">kg</span>
                    </div>
                    <p class="input-berat-hint" style="font-size: 0.8rem; margin-top: 4px; opacity: 0.8;">* Rincian tagihan dihitung otomatis</p>
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
                    <button class="tombol-batalkan-status" id="tombolBatalkanAdmin" onclick="batalkanPesananAdmin(idAktif)" style="display:none;">
                        Batalkan Pesanan Ini
                    </button>
                    <p class="status-aktif-teks" id="infoSudahDibatalkan" style="display:none; color:#f87171; font-weight:600;">
                        ✕ Pesanan ini sudah dibatalkan
                    </p>
                </div>
            </div>

            <div class="timeline-box">
                <p class="detail-label">Timeline Riwayat Status</p>
                <div id="timelineKonten" style="font-size:0.85rem; line-height:1.6; color: white;"></div>
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
<?php include '../includes/footer.php'; ?>
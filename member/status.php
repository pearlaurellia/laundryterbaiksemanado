<?php
require_once '../includes/auth-check.php';
require_once '../config/database.php';
require_once '../config/functions.php';

// PERBAIKAN 1: Auto-Fallback agar tidak peduli apakah session kelompokmu bernama id_user atau user_id
$id_member = $_SESSION['id_user'];

// ── Handler POST: batalkan pesanan oleh member ───────────────
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'batalkan') {

    $id_pesanan = (int) ($_POST['id_pesanan'] ?? 0);

    // Validasi: pesanan milik member ini dan masih menunggu konfirmasi
    $stmtCek = $pdo->prepare("
        SELECT id FROM pesanan
        WHERE id = ? AND id_member = ? AND status_pesanan = 'menunggu_konfirmasi'
    ");
    $stmtCek->execute([$id_pesanan, $id_member]);
    $valid = $stmtCek->fetch();

    if ($valid) {
        // UPDATE status pesanan
        $stmtBatal = $pdo->prepare("
            UPDATE pesanan
            SET status_pesanan      = 'dibatalkan',
                dibatalkan_oleh     = 'member',
                sudah_dilihat_member = 0,
                updated_at          = NOW()
            WHERE id = ?
        ");
        $stmtBatal->execute([$id_pesanan]);

        // INSERT riwayat_status
        $stmtRiwayat = $pdo->prepare("
            INSERT INTO riwayat_status (
                id_pesanan, status_lama, status_baru,
                dilakukan_oleh, keterangan, changed_at
            ) VALUES (?, 'menunggu_konfirmasi', 'dibatalkan', 'member', 'Pesanan dibatalkan oleh Pelanggan', NOW())
        ");
        $stmtRiwayat->execute([$id_pesanan]);
    }

    // Redirect dan hentikan script agar tidak bocor ke bawah
    redirect('status.php');
    exit;
}

// ── Cek notifikasi pesanan dibatalkan admin ──────────────────
$stmtNotif = $pdo->prepare("
    SELECT p.*, l.nama_layanan
    FROM pesanan p
    JOIN layanan l ON p.id_layanan = l.id
    WHERE p.id_member = ?
      AND p.status_pesanan = 'dibatalkan'
      AND p.dibatalkan_oleh = 'admin'
      AND p.sudah_dilihat_member = 0
    ORDER BY p.updated_at DESC
    LIMIT 1
");
$stmtNotif->execute([$id_member]);
$notif_batal = $stmtNotif->fetch();

if ($notif_batal) {
    $stmtResetNotif = $pdo->prepare("
        UPDATE pesanan SET sudah_dilihat_member = 1 WHERE id = ?
    ");
    $stmtResetNotif->execute([$notif_batal['id']]);
}

// ── Reset badge: tandai pesanan aktif lainnya sudah dilihat ─────────
$stmtReset = $pdo->prepare("
    UPDATE pesanan
    SET sudah_dilihat_member = 1
    WHERE id_member = ?
      AND NOT (
          status_pesanan  = 'dibatalkan'
          AND dibatalkan_oleh      = 'admin'
          AND sudah_dilihat_member = 0
      )
");
$stmtReset->execute([$id_member]);

// ── Query pesanan aktif dari DB ──────────────────────────────
$stmtAktif = $pdo->prepare("
    SELECT p.*, l.nama_layanan, l.tarif_per_kg, l.satuan
    FROM pesanan p
    JOIN layanan l ON p.id_layanan = l.id
    WHERE p.id_member = ?
      AND p.status_pesanan NOT IN ('selesai', 'dibatalkan')
    ORDER BY p.created_at DESC
");
$stmtAktif->execute([$id_member]);
$pesanan_aktif = $stmtAktif->fetchAll() ?: [];

// Helper badge UI
$label_status = [
    'menunggu_konfirmasi' => 'Menunggu konfirmasi admin...',
    'dikonfirmasi'        => 'Pesanan dikonfirmasi!',
    'sedang_dicuci'       => 'Baju kamu lagi dicuci!',
    'sedang_diantar'      => 'Pesanan sedang diantar!',
    'siap_diambil'        => 'Pesanan siap diambil!',
];
$kelas_badge = [
    'menunggu_konfirmasi' => 'badge-status-baru',
    'dikonfirmasi'        => 'badge-status-dikonfirmasi',
    'sedang_dicuci'       => 'badge-status-diproses',
    'sedang_diantar'      => 'badge-status-diproses',
    'siap_diambil'        => 'badge-status-selesai',
];

$steps_kurir = [
    ['key' => 'menunggu_konfirmasi', 'label' => 'Menunggu'],
    ['key' => 'dikonfirmasi',        'label' => 'Dikonfirmasi'],
    ['key' => 'sedang_dicuci',       'label' => 'Sedang Dicuci'],
    ['key' => 'sedang_diantar',      'label' => 'Sedang Diantar'],
    ['key' => 'selesai',             'label' => 'Selesai'],
];
$steps_ambil = [
    ['key' => 'menunggu_konfirmasi', 'label' => 'Menunggu'],
    ['key' => 'dikonfirmasi',        'label' => 'Dikonfirmasi'],
    ['key' => 'sedang_dicuci',       'label' => 'Sedang Dicuci'],
    ['key' => 'siap_diambil',        'label' => 'Siap Diambil'],
    ['key' => 'selesai',             'label' => 'Selesai'],
];
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Status Pesanan - Laundry 3J</title>
    <link rel="stylesheet" href="../assets/css/style.css">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Bricolage+Grotesque:opsz,wght@12..96,200..800&display=swap" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=DM+Sans:ital,opsz,wght@0,9..40,100..1000;1,9..40,100..1000&display=swap" rel="stylesheet">
</head>
<body>

    <?php include '../includes/header-member.php'; ?>

    <!-- HERO STATUS - Background Biru Gradient -->
    <section class="preview-pesanan-aktif" style="min-height: 100vh; padding: 40px 100px;">
        
        <!-- HEADER -->
        <div style="text-align: center; margin-bottom: 40px;">
            <h1 style="font-family: 'Bricolage Grotesque', sans-serif; font-size: 2.2rem; color: white; margin: 0 0 8px; filter: drop-shadow(var(--shadow));">
                Status Pesanan Aktif
            </h1>
            <p style="color: rgba(255,255,255,0.7); font-size: 1rem; margin: 0;">
                Pantau status cucian kamu secara real-time. Halaman ini diperbarui otomatis.
            </p>
        </div>

        <?php if (empty($pesanan_aktif)): ?>
            <!-- STATE KOSONG -->
            <div style="text-align:center; padding:80px 20px; color: white;">
                <div style="font-size: 5rem; margin-bottom: 20px;">🧺</div>
                <h2 style="font-family: 'Bricolage Grotesque', sans-serif; font-size: 1.8rem; margin-bottom: 12px; filter: drop-shadow(var(--shadow));">Tidak ada pesanan aktif</h2>
                <p style="color: rgba(255,255,255,0.7); font-size: 1rem; margin-bottom: 24px;">Semua pesanan kamu sudah selesai atau belum ada yang dipesan.</p>
                <a href="pesan.php" class="tombol-daun" style="display: inline-block; text-decoration: none; font-size: 1rem;">
                    Buat Pesanan Baru
                </a>
            </div>
        <?php else: ?>
            <?php foreach ($pesanan_aktif as $p): 
                $status   = $p['status_pesanan'];
                $is_kurir = ($p['opsi_pengantaran'] === 'kurir');
                $label    = $label_status[$status] ?? $p['status_pesanan'];
            ?>
                <!-- KARTU PESANAN AKTIF - Model dari index.php -->
                <div class="kartu-pesanan-aktif" style="margin-bottom: 24px;">
                    
                    <!-- KIRI - Status & Progress -->
                    <div class="pesanan-aktif-kiri">
                        <div class="grup-keterangan">
                            <span class="badge-hijau"><?= $is_kurir ? 'Antar' : 'Pickup' ?></span>
                            <span class="badge-biru"><?= htmlspecialchars($p['nama_layanan']) ?></span>
                        </div>
                        <p class="status-teks"><?= $label ?></p>
                        <div class="estimasi-teks">
                            <p><strong>Kode Pesanan:</strong><br>
                            #<?= htmlspecialchars($p['kode_pesanan']) ?></p>
                            <?php if ($is_kurir): ?>
                                <p style="margin-top: 8px;"><strong>Kecamatan:</strong><br>
                                <?= htmlspecialchars($p['kecamatan']) ?></p>
                            <?php endif; ?>
                        </div>
                    </div>

                    <!-- KANAN - Detail & Biaya -->
                    <div class="pesanan-aktif-kanan">
                        <p class="tanggal-atas">
                            <?= date('H:i l, d-m-Y', strtotime($p['created_at'])) ?>
                        </p>
                        
                        <!-- Rincian Biaya -->
                        <div class="rincian-biaya">
                            <p>Biaya:</p>
                            <?php if ($p['berat_aktual'] > 0): ?>
                                <p>Berat = <?= number_format($p['berat_aktual'], 2) ?> <?= htmlspecialchars($p['satuan']) ?> :
                                   Rp <?= number_format($p['total_harga'] - $p['biaya_kurir'], 0, ',', '.') ?>
                                </p>
                            <?php else: ?>
                                <p>Berat belum ditimbang</p>
                            <?php endif; ?>
                            <?php if ($p['biaya_kurir'] > 0): ?>
                                <p>Antar : Rp <?= number_format($p['biaya_kurir'], 0, ',', '.') ?></p>
                            <?php endif; ?>
                        </div>

                        <!-- Total & Note -->
                        <div class="bawah-kanan">
                            <div class="total-harga">
                                <p>Total harga:<br>
                                Rp <?= number_format($p['total_harga'], 0, ',', '.') ?></p>
                            </div>
                            <div class="note-area">
                                <p>Note: <?= htmlspecialchars($p['catatan_khusus'] ?? '-') ?></p>
                            </div>
                        </div>

                        <!-- AKSI -->
                        <div style="display: flex; gap: 12px; margin-top: 20px; position: relative; z-index: 2;">
                            <a href="detail-pesanan.php?id=<?= $p['id'] ?>" class="tombol-detail" style="text-decoration: none;">
                                Detail Pesanan
                            </a>
                            <?php if ($status === 'menunggu_konfirmasi'): ?>
                                <button class="tombol-detail" 
                                        style="background-color: #FFD1D1; color: #D32F2F; border: none; cursor: pointer;"
                                        onclick="konfirmasiBatal(<?= (int)$p['id'] ?>, '<?= htmlspecialchars($p['kode_pesanan'], ENT_QUOTES) ?>', '<?= htmlspecialchars($p['nama_layanan'], ENT_QUOTES) ?>')">
                                    Batalkan
                                </button>
                            <?php endif; ?>
                        </div>

                        <!-- Dekorasi Bulat -->
                        <div class="bulat-biru-kecil"></div>
                        <div class="bulat-biru-besar"></div>
                    </div>
                </div>
            <?php endforeach; ?>
        <?php endif; ?>
    </section>

    <!-- Popup Konfirmasi Batalkan -->
    <div id="overlayPopup" class="overlay-popup" style="display: none;" onclick="tutupPopupBatal()"></div>
    <div id="popupBatal" class="popup-konfirmasi" style="display: none;">
        <h3 class="popup-judul">Batalkan Pesanan?</h3>
        <p class="popup-teks" id="popupBatalTeks">Pesanan ini akan dibatalkan dan tidak dapat dikembalikan.</p>
        <div class="popup-tombol-group">
            <button class="popup-tombol-batal" onclick="tutupPopupBatal()">Tidak, Kembali</button>
            <form method="POST" action="status.php" style="display: inline;">
                <input type="hidden" name="action" value="batalkan">
                <input type="hidden" name="id_pesanan" id="inputIdPesananBatal" value="">
                <button type="submit" class="popup-tombol-konfirm">Ya, Batalkan</button>
            </form>
        </div>
    </div>

    <!-- Popup Notifikasi Dibatalkan Admin -->
    <?php if ($notif_batal): ?>
    <div id="overlayNotifBatal" class="overlay-popup" style="display: block;"></div>
    <div id="popupNotifBatal" class="popup-konfirmasi" style="display: block; text-align: center;">
        <div style="width: 60px; height: 60px; border-radius: 50%; background: #FFD1D1; color: #D32F2F; font-size: 1.8rem; font-weight: 700; display: flex; align-items: center; justify-content: center; margin: 0 auto 16px;">✕</div>
        <h3 class="popup-judul" style="color: #D32F2F;">Pesanan Dibatalkan</h3>
        <p class="popup-teks">
            Pesanan <strong>#<?= htmlspecialchars($notif_batal['kode_pesanan']) ?></strong><br>
            (<?= htmlspecialchars($notif_batal['nama_layanan']) ?>)<br>
            telah dibatalkan oleh admin.
        </p>
        <?php if (!empty($notif_batal['alasan_pembatalan'])): ?>
            <div style="margin: 16px 0; background: #FFF5F5; border-left: 3px solid #F87171; border-radius: 0 8px 8px 0; padding: 12px 16px; text-align: left;">
                <strong style="color: #D32F2F;">Alasan Pembatalan:</strong><br>
                <?= htmlspecialchars($notif_batal['alasan_pembatalan']) ?>
            </div>
        <?php endif; ?>
        <div class="popup-tombol-group" style="justify-content: center;">
            <button class="popup-tombol-batal" onclick="tutupNotifBatal()">Mengerti</button>
            <a href="riwayat.php" class="popup-tombol-konfirm" style="text-decoration: none; background-color: var(--birutua);">Lihat Riwayat</a>
        </div>
    </div>
    <?php endif; ?>

    <script>
    // Popup konfirmasi batalkan pesanan
    function konfirmasiBatal(id, kode, namaLayanan) {
        document.getElementById('popupBatalTeks').innerHTML = 
            'Pesanan <strong>#' + kode + '</strong> (' + namaLayanan + ') akan dibatalkan dan tidak dapat dikembalikan.';
        document.getElementById('inputIdPesananBatal').value = id;
        document.getElementById('overlayPopup').style.display = 'block';
        document.getElementById('popupBatal').style.display = 'block';
    }

    function tutupPopupBatal() {
        document.getElementById('overlayPopup').style.display = 'none';
        document.getElementById('popupBatal').style.display = 'none';
    }

    // Popup notifikasi pesanan dibatalkan admin
    function tutupNotifBatal() {
        document.getElementById('overlayNotifBatal').style.display = 'none';
        document.getElementById('popupNotifBatal').style.display = 'none';
    }

    // Tutup popup jika klik overlay
    document.addEventListener('DOMContentLoaded', function() {
        var overlay = document.getElementById('overlayPopup');
        if (overlay) {
            overlay.addEventListener('click', tutupPopupBatal);
        }
        var overlayNotif = document.getElementById('overlayNotifBatal');
        if (overlayNotif) {
            overlayNotif.addEventListener('click', tutupNotifBatal);
        }
    });
    </script>

    <?php include '../includes/footer.php'; ?>
</body>
</html>
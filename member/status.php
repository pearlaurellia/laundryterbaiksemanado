<?php
require_once '../includes/auth-check.php';
require_once '../config/database.php';
require_once '../config/functions.php';

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
                dilakukan_oleh, changed_at
            ) VALUES (?, 'menunggu_konfirmasi', 'dibatalkan', 'member', NOW())
        ");
        $stmtRiwayat->execute([$id_pesanan]);
    }

    // Redirect agar tidak resubmit saat refresh
    redirect('status.php');
}

// ── Cek notifikasi pesanan dibatalkan admin ──────────────────
// PERBAIKAN: Dipindahkan ke atas agar dibaca browser SEBELUM flag di-reset massal
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

// Jika ada notif pembatalan dari admin, reset flag data tersebut setelah diamankan komponen
if ($notif_batal) {
    $stmtResetNotif = $pdo->prepare("
        UPDATE pesanan SET sudah_dilihat_member = 1 WHERE id = ?
    ");
    $stmtResetNotif->execute([$notif_batal['id']]);
}

// ── Reset badge: tandai pesanan aktif lainnya sudah dilihat ─────────
// PERBAIKAN: Hanya me-reset pesanan yang bukan pembatalan admin agar tidak saling tabrakan
$stmtReset = $pdo->prepare("
    UPDATE pesanan
    SET sudah_dilihat_member = 1
    WHERE id_member = ? AND status_pesanan != 'dibatalkan'
");
$stmtReset->execute([$id_member]);

// ── Query pesanan aktif dari DB ──────────────────────────────
$stmtAktif = $pdo->prepare("
    SELECT p.*, l.nama_layanan, l.tarif_per_kg
    FROM pesanan p
    JOIN layanan l ON p.id_layanan = l.id
    WHERE p.id_member = ?
      AND p.status_pesanan NOT IN ('selesai', 'dibatalkan')
    ORDER BY p.created_at DESC
");
$stmtAktif->execute([$id_member]);
$pesanan_aktif = $stmtAktif->fetchAll() ?: [];

// ── Helper: label dan kelas badge status ─────────────────────
$label_status = [
    'menunggu_konfirmasi' => 'Menunggu Konfirmasi',
    'dikonfirmasi'        => 'Dikonfirmasi',
    'sedang_dicuci'       => 'Sedang Dicuci',
    'sedang_diantar'      => 'Sedang Diantar',
    'siap_diambil'        => 'Siap Diambil',
];
$kelas_badge = [
    'menunggu_konfirmasi' => 'badge-status-baru',
    'dikonfirmasi'        => 'badge-status-dikonfirmasi',
    'sedang_dicuci'       => 'badge-status-diproses',
    'sedang_diantar'      => 'badge-status-diproses',
    'siap_diambil'        => 'badge-status-selesai',
];

// ── Helper: steps progress bar ───────────────────────────────
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
    <title>Status Pesanan - CleanCo</title>
    <link rel="stylesheet" href="../assets/css/style.css">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Bricolage+Grotesque:opsz,wght@12..96,200..800&display=swap" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=DM+Sans:ital,opsz,wght=0,9..40,100..1000;1,9..40,100..1000&display=swap" rel="stylesheet">
</head>
<body>

    <?php include '../includes/header-member.php'; ?>

    <section class="status-section">

        <div class="status-header">
            <h1 class="status-judul">Status Pesanan Aktif</h1>
            <p class="status-subjudul">
                Halaman ini otomatis diperbarui setiap 60 detik.
            </p>
        </div>

        <div class="status-list">

            <?php if (empty($pesanan_aktif)): ?>
                <div class="status-kosong">
                    <div class="status-kosong-ikon">🧺</div>
                    <h2 class="status-kosong-judul">Tidak ada pesanan aktif</h2>
                    <p class="status-kosong-sub">
                        Semua pesanan kamu sudah selesai atau belum ada yang dipesan.
                    </p>
                    <a href="pesan.php" class="tombol-submit-form" style="text-decoration:none; display:inline-block; margin-top:10px;">
                        Buat Pesanan Baru
                    </a>
                </div>

            <?php else: ?>

                <?php foreach ($pesanan_aktif as $p):

                    $status   = $p['status_pesanan'];
                    $is_kurir = ($p['opsi_pengantaran'] === 'kurir');
                    $steps    = $is_kurir ? $steps_kurir : $steps_ambil;

                    // Cari index step aktif
                    $aktif_idx = 0;
                    foreach ($steps as $idx => $step) {
                        if ($step['key'] === $status) {
                            $aktif_idx = $idx;
                            break;
                        }
                    }
                ?>

                    <div class="kartu-status-pesanan">

                        <div class="kartu-status-header">
                            <div class="kartu-status-header-kiri">
                                <h3 class="kartu-status-kode">#<?= htmlspecialchars($p['kode_pesanan']) ?></h3>
                                <p class="kartu-status-meta">
                                    <?= htmlspecialchars($p['nama_layanan']) ?> ·
                                    <?= date('d M Y, H:i', strtotime($p['created_at'])) ?>
                                </p>
                            </div>
                            <div class="kartu-status-header-kanan">
                                <span class="badge-status <?= $kelas_badge[$status] ?? 'badge-status-baru' ?>">
                                    <?= $label_status[$status] ?? $status ?>
                                </span>
                            </div>
                        </div>

                        <?php if ($status === 'siap_diambil'): ?>
                            <div class="banner-status banner-hijau" style="margin:0; border-radius:0;">
                                <span class="banner-ikon">✓</span>
                                <p>Cucian kamu siap diambil! Datang ke outlet dan bayar saat pengambilan.</p>
                            </div>
                        <?php elseif ($status === 'sedang_diantar'): ?>
                            <div class="banner-status banner-biru" style="margin:0; border-radius:0;">
                                <span class="banner-ikon">🛵</span>
                                <p>Cucian kamu sedang dalam perjalanan ke alamat kamu!</p>
                            </div>
                        <?php endif; ?>

                        <div class="progress-bar-wrapper">
                            <div class="progress-bar-track">
                                <?php foreach ($steps as $idx => $step):
                                    if ($idx < $aktif_idx) {
                                        $kelas_step = 'step-progress step-selesai';
                                        $isi_step   = '✓';
                                    } elseif ($idx === $aktif_idx) {
                                        $kelas_step = 'step-progress step-aktif';
                                        $isi_step   = $idx + 1;
                                    } else {
                                        $kelas_step = 'step-progress';
                                        $isi_step   = $idx + 1;
                                    }
                                    $ada_garis = ($idx < count($steps) - 1);
                                ?>
                                    <div class="<?= $kelas_step ?>">
                                        <div class="step-lingkaran"><?= $isi_step ?></div>
                                        <p class="step-label"><?= $step['label'] ?></p>
                                    </div>
                                    <?php if ($ada_garis): ?>
                                        <div class="garis-progress <?= $idx < $aktif_idx ? 'garis-selesai' : '' ?>"></div>
                                    <?php endif; ?>
                                <?php endforeach; ?>
                            </div>
                        </div>

                        <div class="kartu-status-body">

                            <?php if ($p['berat_aktual'] > 0): ?>
                                <div class="kotak-harga-final-status">
                                    <div class="harga-final-baris">
                                        <span class="harga-final-label">Berat Aktual</span>
                                        <strong class="harga-final-nilai"><?= $p['berat_aktual'] ?> kg</strong>
                                    </div>
                                    <div class="harga-final-baris">
                                        <span class="harga-final-label">Total Harga</span>
                                        <strong class="harga-final-nilai harga-final-besar">
                                            <?= formatRupiah($p['total_harga']) ?>
                                            <span class="label-final">(Final)</span>
                                        </strong>
                                    </div>
                                </div>
                            <?php else: ?>
                                <div class="kotak-belum-timbang">
                                    <span class="belum-timbang-ikon">⚖️</span>
                                    <div>
                                        <p class="belum-timbang-judul">Menunggu Penimbangan Admin</p>
                                        <p class="belum-timbang-sub">Harga final akan muncul setelah pakaian ditimbang.</p>
                                    </div>
                                </div>
                            <?php endif; ?>

                            <div class="kartu-status-info-kurir">
                                <span class="info-kurir-ikon"><?= $is_kurir ? '🛵' : '🏬' ?></span>
                                <div>
                                    <p class="info-kurir-label">
                                        <?= $is_kurir ? 'Kurir ke ' . htmlspecialchars($p['kecamatan']) : 'Ambil di Outlet' ?>
                                    </p>
                                    <p class="info-kurir-alamat">
                                        <?= $is_kurir ? htmlspecialchars($p['alamat_pengantaran']) : '—' ?>
                                    </p>
                                </div>
                            </div>

                        </div>

                        <div class="kartu-status-aksi">
                            <a href="detail-pesanan.php?id=<?= $p['id'] ?>" class="tombol-detail-status">Lihat Detail</a>

                            <?php if ($status === 'menunggu_konfirmasi'): ?>
                                <button class="tombol-batalkan-status" onclick="konfirmasiBatal(<?= $p['id'] ?>, '<?= htmlspecialchars($p['kode_pesanan']) ?>', '<?= htmlspecialchars($p['nama_layanan']) ?>')">
                                    Batalkan
                                </button>
                            <?php endif; ?>
                        </div>

                    </div>

                <?php endforeach; ?>

            <?php endif; ?>

        </div>

    </section>

    <div class="overlay-popup" id="overlayPopup" style="display:none;" onclick="tutupPopupBatal()"></div>

    <div class="popup-konfirmasi" id="popupBatal" style="display:none;">
        <h3 class="popup-judul">Batalkan Pesanan?</h3>
        <p class="popup-teks" id="popupBatalTeks">
            Pesanan ini akan dibatalkan dan tidak dapat dikembalikan.
        </p>
        <div class="popup-tombol-group">
            <button class="popup-tombol-batal" onclick="tutupPopupBatal()">
                Tidak
            </button>
            <form id="formBatalkan" method="POST" action="status.php" style="display:inline;">
                <input type="hidden" name="action" value="batalkan">
                <input type="hidden" name="id_pesanan" id="inputIdPesananBatal" value="">
                <button type="submit" class="popup-tombol-konfirm" style="background-color:#f87171; color:white;">
                    Ya, Batalkan
                </button>
            </form>
        </div>
    </div>

    <?php if ($notif_batal): ?>
    <div class="overlay-popup" id="overlayNotifBatal" style="display:block;"></div>
    <div class="popup-konfirmasi" id="popupNotifBatal" style="display:block; text-align:center; padding:40px 36px;">

        <div style="width:60px; height:60px; border-radius:50%; background:#FFD1D1; color:#D32F2F; font-size:1.8rem; font-weight:700; display:flex; align-items:center; justify-content:center; margin:0 auto 16px;">
            ✕
        </div>

        <h3 class="popup-judul" style="text-align:center; color:#D32F2F;">
            Pesanan Dibatalkan
        </h3>

        <p class="popup-teks" style="text-align:center; margin-bottom:8px;">
            Pesanan <strong>#<?= htmlspecialchars($notif_batal['kode_pesanan']) ?></strong>
            (<?= htmlspecialchars($notif_batal['nama_layanan']) ?>)
            kamu telah dibatalkan oleh admin.
        </p>

        <?php if (!empty($notif_batal['alasan_pembatalan'])): ?>
            <div style="margin:0 0 20px; background:#fff5f5; border-left:3px solid #f87171; border-radius:0 8px 8px 0; padding:10px 14px; text-align:left; font-size:0.88rem; color:#555;">
                <strong style="color:#D32F2F;">Alasan:</strong>
                <?= htmlspecialchars($notif_batal['alasan_pembatalan']) ?>
            </div>
        <?php endif; ?>

        <div style="display:flex; gap:12px; justify-content:center; flex-wrap:wrap;">
            <button class="tombol-submit-form" onclick="tutupNotifBatal()" style="margin-top:0; background:#f0f0f0; color:#555;">
                Mengerti
            </button>
            <a href="riwayat.php" class="tombol-submit-form" style="text-decoration:none; margin-top:0;">
                Lihat Riwayat
            </a>
        </div>
    </div>
    <?php endif; ?>

    <script src="../assets/js/main.js"></script>
    <script src="../assets/js/status-refresh.js"></script>

    <?php include '../includes/footer.php'; ?>
</body>
</html>
<?php
require_once '../config/session.php';
require_once '../config/database.php';
require_once '../config/functions.php';

// Memanfaatkan file proteksi admin yang sudah ada di struktur folder
require_once '../includes/admin-check.php'; 

// ── Kartu hero ──────────────────────────────────────────────
// 1. Total Pesanan Hari Ini (Berdasarkan pesanan masuk baru)
$stmt = $pdo->prepare("SELECT COUNT(*) FROM pesanan WHERE DATE(created_at) = CURDATE()");
$stmt->execute();
$total_pesanan_hari_ini = $stmt->fetchColumn() ?: 0;

// 2. Total Pesanan Aktif (Masih dalam proses pengerjaan)
$stmt = $pdo->prepare("SELECT COUNT(*) FROM pesanan WHERE status_pesanan NOT IN ('selesai', 'dibatalkan')");
$stmt->execute();
$total_pesanan_aktif = $stmt->fetchColumn() ?: 0;

// 3. Omzet Hari Ini (Uang riil masuk hari ini dari transaksi yang LUNAS)
$stmt = $pdo->prepare("SELECT SUM(total_harga) FROM pesanan WHERE status_pembayaran = 'lunas' AND DATE(updated_at) = CURDATE()");
$stmt->execute();
$omzet_hari_ini = $stmt->fetchColumn() ?: 0;

// ── Sejarah pesanan (5 Terakhir Selesai) ───────────────────────
$stmt = $pdo->prepare("
    SELECT p.*, u.nama AS nama_pelanggan, l.nama_layanan
    FROM pesanan p 
    JOIN users u ON p.id_member = u.id 
    JOIN layanan l ON p.id_layanan = l.id
    WHERE p.status_pesanan = 'selesai' 
    ORDER BY p.updated_at DESC 
    LIMIT 5
");
$stmt->execute();
$sejarah_pesanan = $stmt->fetchAll() ?: [];

// ── Preview member aktif (5 Terakhir Bergabung) ───────────────
$stmt = $pdo->prepare("
    SELECT * FROM users 
    WHERE role = 'member' AND status_akun = 'aktif' 
    ORDER BY created_at DESC 
    LIMIT 5
");
$stmt->execute();
$member_aktif = $stmt->fetchAll() ?: [];

// ── Rekapitulasi ────────────────────────────────────────────
$dari_tanggal   = $_GET['dari_tanggal'] ?? date('Y-m-d');
$sampai_tanggal = $_GET['sampai_tanggal'] ?? date('Y-m-d');

// Mengubah filter berdasarkan updated_at agar performa rekap finansial akurat
$stmt = $pdo->prepare("
    SELECT p.*, u.nama AS nama_pelanggan, l.nama_layanan
    FROM pesanan p
    JOIN users u ON p.id_member = u.id
    JOIN layanan l ON p.id_layanan = l.id
    WHERE DATE(p.updated_at) BETWEEN ? AND ? 
    ORDER BY p.updated_at DESC
");
$stmt->execute([$dari_tanggal, $sampai_tanggal]);
$data_rekap = $stmt->fetchAll() ?: [];

// Hitung total rekapitulasi secara presisi
$total_omzet_rekap = 0; 
$total_berat_rekap = 0;

foreach ($data_rekap as $row) {
    // Omzet HANYA bertambah jika pesanan memang sudah dibayar (Lunas)
    if ($row['status_pembayaran'] === 'lunas') {
        $total_omzet_rekap += $row['total_harga'];
    }
    // Berat aktual dihitung dari pesanan yang tidak dibatalkan
    if ($row['status_pesanan'] !== 'dibatalkan') {
        $total_berat_rekap += $row['berat_aktual'];
    }
}
?>

    <?php include '../includes/header-admin.php'; ?>

    <section class="hero">
        <div class="konten-hero-admin">
            <div class="kartu-pesanan-container">
                <div class="kartu-pesanan">
                    <div class="kartu-header">Total Pesanan Hari Ini</div>
                    <div class="kartu-body-admin">
                        <p><?= $total_pesanan_hari_ini ?></p>
                        <div class="bulat-kecil-admin"></div>
                        <div class="bulat-harga-admin"></div>
                    </div>
                </div>
                <div class="kartu-pesanan">
                    <div class="kartu-header">Total Pesanan Aktif</div>
                    <div class="kartu-body-admin">
                        <p><?= $total_pesanan_aktif ?></p>
                        <div class="bulat-kecil-admin"></div>
                        <div class="bulat-harga-admin"></div>
                    </div>
                </div>
                <div class="kartu-pesanan">
                    <div class="kartu-header">Omzet</div>
                    <div class="kartu-body-admin">
                        <p>Rp <?= number_format($omzet_hari_ini, 0, ',', '.') ?></p>
                        <div class="bulat-kecil-admin"></div>
                        <div class="bulat-harga-admin"></div>
                    </div>
                </div>
            </div>
        </div>
        <div class="bulat-atas-admin"></div>
        <div class="bulat-ditengah-admin"></div>
        <div class="bulat-besar-admin"><h2>Laundry 3J</h2></div>
    </section>

    <section class="sejarah-pesanan">
        <h3 class="judul-overview-layanan">Riwayat Pesanan</h3>
        <div class="kartu-sejarah-container">
            <?php if (empty($sejarah_pesanan)): ?>
                <p style="color:#aaa; padding: 20px;">Belum ada pesanan selesai.</p>
            <?php else: ?>
            <?php foreach ($sejarah_pesanan as $p): ?>
            <div class="kartu-sejarah" style="min-height:150px; position: relative; overflow: hidden;">
                <div class="kartu-header">
                    <?= htmlspecialchars($p['nama_pelanggan']) ?>
                </div>
                <div class="sejarah-body">
                    <div class="grup-keterangan">
                        <span class="badge-biru"><?= date('H:i, d-m-Y', strtotime($p['created_at'])) ?></span>
                        <span class="badge-biru"><?= htmlspecialchars($p['nama_layanan']) ?></span>
                        <span class="badge-hijau">
                            <?= $p['opsi_pengantaran'] === 'kurir' ? 'Kurir' : 'Ambil Sendiri' ?>
                        </span>
                        <span class="badge-biru"><?= number_format($p['berat_aktual'], 1) ?> kg</span>
                    </div>
                    <p class="status-teks">Selesai: <?= date('H:i, d-m-Y', strtotime($p['updated_at'])) ?></p>
                    <p class="status-teks">Total: <strong>Rp <?= number_format($p['total_harga'], 0, ',', '.') ?></strong></p>
                    <a href="pesanan.php?id=<?= $p['id'] ?>" class="tombol-detail">Detail Pesanan</a>
                </div>
                <div class="bulat-kecil"></div>
                <div class="bulat-harga"></div>
            </div>
            <?php endforeach; ?>
            <?php endif; ?>
        </div>
    </section>

    <!-- SECTION 3: Preview member aktif -->
    <section class="preview-pesanan-aktif">
        <div class="pesanan-aktif-container">
            <?php if (empty($member_aktif)): ?>
                <p style="color:#aaa; padding: 20px;">Belum ada member aktif.</p>
            <?php else: ?>
                <?php foreach ($member_aktif as $m): ?>
                    <div class="kartu-member-admin">
                        <div class="member-kiri">
                            <p class="teks-kecil">Username: <?= htmlspecialchars($m['email']) ?></p>
                            <div class="baris-nama">
                                <div class="pill-nama">Nama: <strong><?= htmlspecialchars($m['nama']) ?></strong></div>
                                <div class="pill-alamat">Lihat alamat lengkap</div>
                            </div>
                            <p class="teks-info">Nomor Telepon: <?= htmlspecialchars($m['no_hp']) ?></p>
                            <p class="teks-info">Email: <?= htmlspecialchars($m['email']) ?></p>
                        </div>
                        <div class="member-kanan">
                            <p class="teks-tanggal">Tanggal bergabung:<br><?= date('d-m-Y', strtotime($m['created_at'])) ?></p>
                            <div class="baris-aksi">
                                <a href="member.php?id=<?= $m['id'] ?>" class="pill-riwayat">Riwayat Pesanan</a>
                                <a href="member.php?action=nonaktif&id=<?= $m['id'] ?>" class="pill-nonaktif">Nonaktifkan</a>
                            </div>
                            <div class="lingkaran-member-kecil"></div>
                            <div class="lingkaran-member-besar"></div>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
    </section>

    <!-- SECTION 4: Rekapitulasi dengan filter -->
    <section class="rekapitulasi">
        <div class="rekap-kiri">
            <h1 class="judul-rekap">Rekapitulasi</h1>

            <form method="GET" action="">
                <label class="label-rekap">Dari Tanggal:</label>
                <input type="date" name="dari_tanggal" class="input-rekap" value="<?= $dari_tanggal ?>">

                <label class="label-rekap">Sampai Tanggal:</label>
                <input type="date" name="sampai_tanggal" class="input-rekap" value="<?= $sampai_tanggal ?>">

                <button type="submit" class="tombol-filter-rekap">Filter</button>
            </form>

            <div class="bulat-rekap-kecil"></div>
            <div class="bulat-rekap-besar"></div>
        </div>

        <div class="rekap-kanan">
            <div class="badge-tanggal-rekap">
                Rekap: <?= date('d-m-Y', strtotime($dari_tanggal)) ?> s.d <?= date('d-m-Y', strtotime($sampai_tanggal)) ?>
            </div>
            <div class="kartu-rekap-container">
                <div class="kartu-rekap">
                    <div class="kartu-header">Omzet</div>
                    <div class="kartu-body">
                        <p>Rp <?= number_format($total_omzet_rekap, 0, ',', '.') ?></p>
                        <div class="bulat-kecil"></div>
                        <div class="bulat-harga"></div>
                    </div>
                </div>
                <div class="kartu-rekap">
                    <div class="kartu-header kartu-header-biru">Total Pesanan</div>
                    <div class="kartu-body">
                        <p><?= count($data_rekap) ?> Nota <span style="font-size:1rem;color:#888;">(<?= number_format($total_berat_rekap, 1) ?> kg)</span></p>
                        <div class="bulat-kecil"></div>
                        <div class="bulat-harga"></div>
                    </div>
                </div>
            </div>
        </div>
    </section>
    
    <?php include '../includes/footer.php'; ?>
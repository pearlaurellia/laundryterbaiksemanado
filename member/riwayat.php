<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Riwayat Pesanan - CleanCo</title>
    <link rel="stylesheet" href="../assets/css/style.css">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Bricolage+Grotesque:opsz,wght@12..96,200..800&display=swap" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=DM+Sans:ital,opsz,wght@0,9..40,100..1000;1,9..40,100..1000&display=swap" rel="stylesheet">
</head>
<body>

    <?php include '../includes/header-member.php'; ?>


    <section class="riwayat-section">

        <div class="riwayat-header">
            <h1 class="status-judul">Riwayat Pesanan</h1>
            <p class="status-subjudul">
                Arsip seluruh pesanan yang telah selesai atau dibatalkan.
            </p>
        </div>

        <div class="grup-filter riwayat-filter" id="grupFilterRiwayat">
            <button class="tombol-filter aktif"
                    data-filter="semua"
                    onclick="filterRiwayat('semua', this)">
                Semua
            </button>
            <button class="tombol-filter"
                    data-filter="selesai"
                    onclick="filterRiwayat('selesai', this)">
                Selesai
            </button>
            <button class="tombol-filter"
                    data-filter="dibatalkan"
                    onclick="filterRiwayat('dibatalkan', this)">
                Dibatalkan
            </button>
        </div>

        <div class="riwayat-list" id="riwayatList">
        </div>

        <div class="status-kosong" id="riwayatKosong" style="display:none;">
            <div class="status-kosong-ikon">📋</div>
            <h2 class="status-kosong-judul">Belum ada riwayat</h2>
            <p class="status-kosong-sub">
                Pesanan yang sudah selesai atau dibatalkan akan muncul di sini.
            </p>
            <a href="pesan.php" class="tombol-submit-form"
               style="text-decoration:none; display:inline-block; margin-top:10px;">
                Buat Pesanan Pertama
            </a>
        </div>

    </section>

    <script src="../assets/js/main.js"></script>

</body>
</html>
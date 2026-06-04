<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Buat Pesanan - CleanCo</title>
    <link rel="stylesheet" href="../assets/css/style.css">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Bricolage+Grotesque:opsz,wght@12..96,200..800&display=swap" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=DM+Sans:ital,opsz,wght@0,9..40,100..1000;1,9..40,100..1000&display=swap" rel="stylesheet">
</head>
<body>

    <?php include '../includes/header-member.php'; ?>



    <section class="hero-form pesan-hero">

        <div class="konten-form konten-form-pesan">
            <h1 class="judul-form judul-form-kiri">Buat Pesanan Baru</h1>
            <p class="subjudul-pesan">Isi form di bawah untuk memulai pesanan laundry kamu.</p>

            <!-- ── PILIH LAYANAN ── -->
            <!--
                BACKEND NOTE:
                Isi .kartu-pilih-layanan dengan PHP foreach($layanan as $l).
                Gunakan data-id, data-tarif untuk JS kalkulasi harga.
                Tarif ditarik dari tabel layanan yang dikelola admin.
            -->
            <div class="grup-input-form">
                <label class="label-form">Pilih Layanan</label>
                <div class="grid-pilih-layanan" id="gridLayanan">

                    <div class="kartu-pilih-layanan"
                         data-id="1"
                         data-nama="Reguler"
                         data-tarif="8000"
                         data-satuan="kg"
                         onclick="pilihLayanan(this)">
                        <div class="kartu-pilih-header">Reguler</div>
                        <div class="kartu-pilih-body">
                            <p class="kartu-pilih-tarif">Rp 8.000 / kg</p>
                            <p class="kartu-pilih-durasi">1–2 hari</p>
                        </div>
                    </div>

                    <div class="kartu-pilih-layanan dipilih"
                        data-id="2"
                        data-nama="Express"
                        data-tarif="15000"
                        data-satuan="kg"
                        onclick="pilihLayanan(this)">
                        <div class="kartu-pilih-header kartu-pilih-header-biru">Express</div>
                        <div class="kartu-pilih-body">
                            <p class="kartu-pilih-tarif">Rp 15.000 / kg</p>
                            <p class="kartu-pilih-durasi">6–8 jam</p>
                        </div>
                    </div>

                    <div class="kartu-pilih-layanan"
                        data-id="3"
                        data-nama="Dry Cleaning"
                        data-tarif="25000"
                        data-satuan="item"
                        onclick="pilihLayanan(this)">
                        <div class="kartu-pilih-header">Dry Cleaning</div>
                        <div class="kartu-pilih-body">
                            <p class="kartu-pilih-tarif">Rp 25.000 / item</p>
                            <p class="kartu-pilih-durasi">1–2 hari</p>
                        </div>
                    </div>

                </div>
                <input type="hidden" id="inputLayananId" name="layanan_id" value="2">
            </div>

            <!-- ── OPSI PENGANTARAN ── -->
            <!--
                BACKEND NOTE:
                Nilai yang dikirim: opsi_pengantaran = 'ambil_sendiri' | 'kurir'
                Jika kurir: biaya_kurir = 10000, kecamatan & alamat wajib ada
                Jika ambil sendiri: biaya_kurir = 0
            -->
            <div class="grup-input-form">
                <label class="label-form">Opsi Pengantaran</label>
                <div class="grid-opsi-pengantaran">

                    <label class="kartu-opsi-pengantaran">
                        <input type="radio" name="opsi_pengantaran"
                            value="ambil_sendiri"
                            onchange="gantiOpsiPengantaran('ambil_sendiri')">
                        <div class="kartu-opsi-isi">
                            <span class="kartu-opsi-ikon">🏬</span>
                            <span class="kartu-opsi-label">Ambil Sendiri</span>
                            <span class="kartu-opsi-biaya">Gratis</span>
                        </div>
                    </label>

                    <label class="kartu-opsi-pengantaran dipilih-opsi">
                        <input type="radio" name="opsi_pengantaran"
                            value="kurir"
                            checked
                            onchange="gantiOpsiPengantaran('kurir')">
                        <div class="kartu-opsi-isi">
                            <span class="kartu-opsi-ikon">🛵</span>
                            <span class="kartu-opsi-label">Kurir Laundry</span>
                            <span class="kartu-opsi-biaya">+ Rp 10.000</span>
                        </div>
                    </label>

                </div>
            </div>

            <!-- ── INFO KURIR (muncul saat kurir dipilih) ── -->
            <div class="info-kurir-wrapper" id="infoKurir">
                <p class="info-kurir-teks">
                    🛵 Kurir akan menghubungi kamu via WhatsApp sebelum menjemput.
                </p>
                <p class="info-kurir-teks">
                    📍 Layanan kurir tersedia untuk kecamatan:
                    <strong>Wanea, Malalayang, Tikala, Mapanget, Tuminting, Bunaken, Wenang, Paal Dua, Singkil, Sario.</strong>
                </p>
            </div>

            <!-- ── ALAMAT (muncul saat kurir dipilih) ── -->
            <div id="seksiAlamat">
                <div class="grup-input-form">
                    <label class="label-form">Kecamatan Tujuan</label>
                    <!--
                        BACKEND NOTE:
                        Isi option ini dengan kecamatan yang dilayani.
                        Simpan data kecamatan di DB atau config PHP.
                    -->
                    <select class="input-form" id="inputKecamatan" name="kecamatan">
                        <option value="">-- Pilih Kecamatan --</option>
                        <option value="Wanea">Wanea</option>
                        <option value="Malalayang">Malalayang</option>
                    </select>
                </div>
                <div class="grup-input-form">
                    <label class="label-form">Alamat Lengkap</label>
                    <input type="text" class="input-form"
                        id="inputAlamat" name="alamat_pengantaran"
                        placeholder="Jl. Nama Jalan, No. Rumah, Lingkungan...">
                </div>
            </div>

            <!-- ── ESTIMASI BERAT ── -->
            <div class="grup-input-form">
                <label class="label-form">Estimasi Berat <span class="label-opsional">(opsional)</span></label>
                <div class="input-berat-wrapper">
                    <input type="number"
                           class="input-berat"
                           id="inputEstimasiBerat"
                           name="estimasi_berat"
                           placeholder="0"
                           min="0" step="0.1"
                           oninput="hitungEstimasi()"
                           style="width:110px;">
                    <span class="satuan-berat" style="color:white;">kg</span>
                </div>
            </div>

            <!-- ── ESTIMASI HARGA (hasil kalkulasi JS) ── -->
            <div class="kotak-estimasi-harga" id="kotakEstimasi">
                <p class="estimasi-harga-teks" id="teksEstimasiHarga">
                    Harga akan dihitung admin setelah pakaian ditimbang.
                </p>
            </div>

            <!-- ── CATATAN KHUSUS ── -->
            <div class="grup-input-form">
                <label class="label-form">Catatan Khusus <span class="label-opsional">(opsional)</span></label>
                <textarea class="input-form input-textarea"
                    id="inputCatatan" name="catatan"
                    placeholder="cth: pisahkan baju putih, ada noda di bagian kerah..."></textarea>
            </div>

            <!-- ── TOMBOL SUBMIT ── -->
            <!--
                BACKEND NOTE:
                window.open() WhatsApp dipanggil di JS event listener submit,
                SEBELUM form dikirim ke PHP. Bukan setelah redirect.
                Ini mencegah browser memblokir popup.
            -->
            <button class="tombol-submit-form tombol-kirim-pesanan"
                    onclick="kirimPesanan(event)">
                Kirim Pesanan
            </button>

        </div>

        <div class="bulat-atas"></div>
        <div class="bulat-ditengah"></div>
        <div class="bulat-besar"><h2>CleanCo</h2></div>

    </section>

    <!-- ── POP-UP SUKSES ── -->
    <div class="overlay-popup" id="overlayPopup" style="display:none;"></div>
    <div class="popup-sukses-pesanan" id="popupSukses" style="display:none;">

        <div class="popup-sukses-atas">
            <div class="popup-sukses-ikon">✓</div>
            <h2 class="popup-sukses-judul">Pesanan Berhasil Dibuat!</h2>
            <p class="popup-sukses-sub">Ringkasan pesanan kamu:</p>
        </div>

        <div class="popup-sukses-rincian">
            <div class="popup-rincian-baris">
                <span>Nomor Pesanan</span>
                <strong id="popupNoPesanan">—</strong>
            </div>
            <div class="popup-rincian-baris">
                <span>Layanan</span>
                <strong id="popupLayanan">—</strong>
            </div>
            <div class="popup-rincian-baris">
                <span>Pengantaran</span>
                <strong id="popupPengantaran">—</strong>
            </div>
            <div class="popup-rincian-baris">
                <span>Estimasi Biaya</span>
                <strong id="popupEstimasi">—</strong>
            </div>
        </div>

        <div class="popup-tombol-group" style="justify-content:center; gap:14px;">
            <a href="status.php" class="tombol-submit-form"
               style="text-decoration:none; text-align:center;">
                Lihat Status
            </a>
            <button class="tombol-batal-layanan"
                    onclick="pesanLagi()"
                    style="display:inline-block;">
                Pesan Lagi
            </button>
        </div>

    </div>

    <script>
        // Data session member — di-output oleh PHP
        // Nanti backend isi dengan: $_SESSION['nama'], $_SESSION['username'], dst.
        const sessionMember = {
            nama        : '<?= htmlspecialchars($_SESSION["nama"] ?? "Member") ?>',
            username    : '<?= htmlspecialchars($_SESSION["username"] ?? "@member") ?>',
            namaLengkap : '<?= htmlspecialchars($_SESSION["nama_lengkap"] ?? $_SESSION["nama"] ?? "") ?>',
            noHP        : '<?= htmlspecialchars($_SESSION["no_hp"] ?? "") ?>',
            id          : <?= intval($_SESSION["user_id"] ?? 0) ?>
        };
    </script>
    <script src="../assets/js/pesan-member.js"></script>
    <script src="../assets/js/kalkulasi-harga.js"></script>
    <script src="../assets/js/form-validation.js"></script>
</body>
</html>
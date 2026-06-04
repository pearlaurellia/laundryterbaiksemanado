<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kelola Member - CleanCo Admin</title>
    <link rel="stylesheet" href="../assets/css/style.css">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Bricolage+Grotesque:opsz,wght@12..96,200..800&display=swap" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=DM+Sans:ital,opsz,wght@0,9..40,100..1000;1,9..40,100..1000&display=swap" rel="stylesheet">
    <script src="../assets/js/member-admin.js"></script>
</head>
<body>

    <?php include '../includes/header-admin.php'; ?>

    <!--
    ============================================================
     BACKEND OVERVIEW — admin/member.php
     Tabel DB: users (id, nama, email, no_hp, alamat, role,
                       is_active, created_at)
     Query utama: SELECT * FROM users WHERE role='member'
                  ORDER BY created_at DESC
     Action yang dibutuhkan backend:
       POST ?action=toggle_status → UPDATE is_active by id
                                    (1 → 0 atau 0 → 1)
    ============================================================
    -->

    <section class="halaman-pesanan">

        <!-- ===================== SIDEBAR KIRI ===================== -->
        <div class="pesanan-sidebar">
            <h2 class="judul-sidebar">Daftar Member</h2>

            <!-- Search -->
            <div class="member-search-wrapper">
                <input
                    type="text"
                    class="input-form member-search"
                    id="inputCariMember"
                    placeholder="Cari nama atau username..."
                    oninput="cariMember(this.value)"
                >
            </div>

            <!-- Filter status -->
            <!--
                BACKEND NOTE:
                Filter ini bisa dikirim via GET ?status=aktif / nonaktif / semua
                Gunakan $_GET['status'] untuk WHERE is_active = 1 / 0
            -->
            <div class="grup-filter">
                <button class="tombol-filter aktif" onclick="filterMember('semua', this)">Semua</button>
                <button class="tombol-filter" onclick="filterMember('aktif', this)">Aktif</button>
                <button class="tombol-filter" onclick="filterMember('nonaktif', this)">Nonaktif</button>
            </div>

            <!-- List member -->
            <!--
                BACKEND NOTE:
                Ulangi .item-member ini dengan PHP foreach($members as $m).
                data-id      = $m['id']
                data-status  = $m['is_active'] == 1 ? 'aktif' : 'nonaktif'
                data-nama    = $m['nama']  (untuk search JS)
            -->
            <div class="list-pesanan" id="listMember">

                <div class="item-member aktif-dipilih"
                     data-id="1"
                     data-status="aktif"
                     data-nama="ryan liam"
                     onclick="bukaMember(1, this)">
                    <div class="item-pesanan-atas">
                        <span class="badge-status-member badge-member-aktif">Aktif</span>
                        <span class="item-pesanan-waktu">Bergabung 10-04-2025</span>
                    </div>
                    <p class="item-pesanan-nama">Ryan Liam</p>
                    <p class="item-member-sub">@liam999 · 5 transaksi</p>
                </div>

                <div class="item-member"
                     data-id="2"
                     data-status="aktif"
                     data-nama="sinta dewi"
                     onclick="bukaMember(2, this)">
                    <div class="item-pesanan-atas">
                        <span class="badge-status-member badge-member-aktif">Aktif</span>
                        <span class="item-pesanan-waktu">Bergabung 22-03-2025</span>
                    </div>
                    <p class="item-pesanan-nama">Sinta Dewi</p>
                    <p class="item-member-sub">@sintad · 3 transaksi</p>
                </div>

                <div class="item-member"
                     data-id="3"
                     data-status="nonaktif"
                     data-nama="budi santoso"
                     onclick="bukaMember(3, this)">
                    <div class="item-pesanan-atas">
                        <span class="badge-status-member badge-member-nonaktif">Nonaktif</span>
                        <span class="item-pesanan-waktu">Bergabung 01-01-2025</span>
                    </div>
                    <p class="item-pesanan-nama">Budi Santoso</p>
                    <p class="item-member-sub">@budis · 8 transaksi</p>
                </div>

                <div class="item-member"
                     data-id="4"
                     data-status="aktif"
                     data-nama="mega putri"
                     onclick="bukaMember(4, this)">
                    <div class="item-pesanan-atas">
                        <span class="badge-status-member badge-member-aktif">Aktif</span>
                        <span class="item-pesanan-waktu">Bergabung 15-05-2025</span>
                    </div>
                    <p class="item-pesanan-nama">Mega Putri</p>
                    <p class="item-member-sub">@megap · 1 transaksi</p>
                </div>

                <div class="item-member"
                     data-id="5"
                     data-status="aktif"
                     data-nama="pearl nafeesa"
                     onclick="bukaMember(5, this)">
                    <div class="item-pesanan-atas">
                        <span class="badge-status-member badge-member-aktif">Aktif</span>
                        <span class="item-pesanan-waktu">Bergabung 02-06-2025</span>
                    </div>
                    <p class="item-pesanan-nama">Pearl Nafeesa</p>
                    <p class="item-member-sub">@pearlnafeesa · 2 transaksi</p>
                </div>

            </div>
            <!-- /listMember -->

        </div>
        <!-- /pesanan-sidebar -->

        <!-- ===================== DETAIL PANEL ===================== -->
        <div class="pesanan-detail" id="memberDetail">

            <!-- State kosong -->
            <div class="detail-kosong" id="detailKosong">
                <div style="width:80px;height:80px;border-radius:50%;
                            background:rgba(13,63,138,0.08);margin-bottom:20px;"></div>
                <p style="color:#aaa;">Pilih member untuk melihat detail</p>
            </div>

            <!-- State detail aktif -->
            <div class="detail-isi" id="detailIsi" style="display:none;">

                <!-- HEADER -->
                <div class="detail-header">
                    <div>
                        <h2 class="detail-nama" id="detailNama">—</h2>
                        <p class="detail-username" id="detailUsername">—</p>
                    </div>
                    <div class="detail-waktu-badge" id="detailTanggalBergabung">—</div>
                </div>

                <!-- INFO GRID -->
                <!--
                    BACKEND NOTE:
                    Semua nilai di bawah diambil dari row member terpilih.
                    Gunakan htmlspecialchars() untuk semua output string.
                -->
                <div class="detail-info-grid">
                    <div class="detail-info-blok">
                        <p class="detail-label">Nama Lengkap</p>
                        <p class="detail-nilai" id="detailNamaLengkap">—</p>
                    </div>
                    <div class="detail-info-blok">
                        <p class="detail-label">Email</p>
                        <p class="detail-nilai" id="detailEmail">—</p>
                    </div>
                    <div class="detail-info-blok">
                        <p class="detail-label">Nomor WhatsApp</p>
                        <p class="detail-nilai" id="detailNoHP">—</p>
                    </div>
                    <div class="detail-info-blok">
                        <p class="detail-label">Alamat</p>
                        <p class="detail-nilai" id="detailAlamat">—</p>
                    </div>
                    <div class="detail-info-blok">
                        <p class="detail-label">Kecamatan</p>
                        <p class="detail-nilai" id="detailKecamatan">—</p>
                    </div>
                    <div class="detail-info-blok">
                        <p class="detail-label">Total Transaksi</p>
                        <p class="detail-nilai" id="detailTotalTransaksi">—</p>
                    </div>
                </div>

                <!-- STATISTIK MEMBER -->
                <div class="detail-berat-biaya">

                    <div class="kartu-berat">
                        <p class="detail-label">Total Pesanan</p>
                        <p class="member-stat-angka" id="detailJmlPesanan">—</p>
                        <p class="member-stat-sub">pesanan tercatat</p>
                    </div>

                    <div class="kartu-biaya">
                        <p class="detail-label">Ringkasan Transaksi</p>
                        <p class="rincian-baris" id="detailPesananSelesai">Selesai : —</p>
                        <p class="rincian-baris" id="detailPesananAktif">Aktif : —</p>
                        <p class="rincian-baris" id="detailPesananBatal">Dibatalkan : —</p>
                        <p class="rincian-total" id="detailTotalOmzet">Total Nilai : Rp —</p>
                    </div>

                </div>

                <!-- RIWAYAT PESANAN SINGKAT -->
                <!--
                    BACKEND NOTE:
                    Query: SELECT * FROM pesanan
                           WHERE member_id = $id
                           ORDER BY created_at DESC
                           LIMIT 3
                    Tampilkan 3 pesanan terakhir member ini sebagai preview.
                -->
                <div class="detail-status-section" style="margin-bottom:20px;">
                    <p class="detail-label" style="margin-bottom:12px;">
                        3 Pesanan Terakhir
                    </p>
                    <div id="detailRiwayatSingkat">
                        <!-- Diisi JS / PHP loop -->
                    </div>
                </div>

                <!-- TOGGLE STATUS AKUN -->
                <!--
                    BACKEND NOTE:
                    Tombol ini idealnya submit form POST ke member.php?action=toggle_status
                    dengan field hidden: id = $member['id']
                    Backend: UPDATE users SET is_active = NOT is_active WHERE id = ?
                    Jika is_active = 0, member yang coba login akan ditolak di auth-check.php
                    dengan pesan: "Akun kamu telah dinonaktifkan. Hubungi admin."

                    Contoh form untuk backend:
                    <form method="POST" action="member.php?action=toggle_status"
                          onsubmit="return konfirmasiToggle()">
                        <input type="hidden" name="id" value="<?= $member['id'] ?>">
                        <button type="submit" class="tombol-nonaktif-member">
                            Nonaktifkan Akun
                        </button>
                    </form>
                -->
                <div class="detail-status-section">
                    <p class="detail-label" style="margin-bottom:4px;">
                        Status Akun Member
                    </p>
                    <p class="status-aktif-teks" style="margin-bottom:14px;">
                        Status saat ini:
                        <strong id="statusAkunTeks">—</strong>
                    </p>

                    <div style="display:flex; gap:12px; flex-wrap:wrap;">
                        <button class="tombol-aktifkan-member"
                                id="tombolAktifkan"
                                onclick="toggleStatusMember('aktif')"
                                style="display:none;">
                            ✓ Aktifkan Akun
                        </button>
                        <button class="tombol-nonaktif-member"
                                id="tombolNonaktif"
                                onclick="toggleStatusMember('nonaktif')">
                            ✕ Nonaktifkan Akun
                        </button>
                        <a class="tombol-wa-member"
                           id="tombolWA"
                           href="#"
                           target="_blank">
                            Hubungi via WhatsApp
                        </a>
                    </div>

                </div>

            </div>
            <!-- /detail-isi -->

        </div>
        <!-- /pesanan-detail -->

    </section>

    <!-- ===================== POP-UP KONFIRMASI ===================== -->
    <!--
        Pop-up muncul sebelum toggle status dieksekusi.
        Mencegah admin tidak sengaja menonaktifkan member.
    -->
    <div class="overlay-popup" id="overlayPopup" style="display:none;"
         onclick="tutupPopup()"></div>

    <div class="popup-konfirmasi" id="popupKonfirmasi" style="display:none;">
        <h3 class="popup-judul" id="popupJudul">Nonaktifkan Akun?</h3>
        <p class="popup-teks" id="popupTeks">
            Member yang dinonaktifkan tidak dapat login ke sistem.
        </p>
        <div class="popup-tombol-group">
            <button class="popup-tombol-batal" onclick="tutupPopup()">Batal</button>
            <button class="popup-tombol-konfirm" id="popupTombolKonfirm"
                    onclick="konfirmasiToggle()">Ya, Lanjutkan</button>
        </div>
    </div>

</body>
</html>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Document</title>
    <link rel="stylesheet" href="../assets/css/style.css">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Bricolage+Grotesque:opsz,wght@12..96,200..800&display=swap" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=DM+Sans:ital,opsz,wght@0,9..40,100..1000;1,9..40,100..1000&display=swap" rel="stylesheet">
</head>

<body>
    <?php include '../includes/header-member.php'; ?>
    <section class="hero">
        <div class="konten-hero">
            <div class="teks-hero">
                <h1>Laundry Mudah, <br> <span> Kapan Saja </span></h1>
                <p> Pesan layanan laundry kapan saja, pantau status cucian secara real-time,
                    dan ambil pakaian bersih, siap pakai.
                </p>
            </div>

            <div class="tombol-hero">
                <a href="pesan.php" class="tombol-daun"> Pesan Sekarang </a>
            </div>
        </div>
        
        <div class="bulat-atas"></div>
        <div class="bulat-ditengah"></div>
        <div class="bulat-besar"><h2> CleanCo </h2></div>
    </section>

    <section class="layanan-overview">
        <h3 class="judul-overview-layanan">
            Berikut layanan-layanan yang tersedia
        </h3>
        <p class="teks-overview-layanan">
            Layanan tersedia dari reguler sampai dry cleaning
        </p>
        <div class="kartu-layanan-container">
            <div class="kartu-layanan">
                <div class="kartu-header"> Reguler </div>
                <div class="kartu-body">
                    <p> Paket lengkap dan terjangkau. </p>
                    <p> Layanan: </p>
                    <ul>
                        <li>Cuci</li>
                        <li>Kering</li>
                        <li>Setrika</li>
                        <li>Durasi 1-2 hari</li>
                    </ul>
                    <div class="bulat-kecil"></div>
                    <div class="bulat-harga">8k/kg</div>
                </div>
            </div>

            <div class="kartu-layanan-featured">
                <div class="kartu-header"> Express </div>
                <div class="kartu-body">
                    <p><b> Paket cepat selesai di hari yang sama. </b></p>
                    <p> Layanan: </p>
                    <ul>
                        <li>Cuci</li>
                        <li>Kering</li>
                        <li>Setrika</li>
                        <li>Durasi 6-8 jam</li>
                    </ul>
                    <div class="bulat-kecil"></div>
                    <div class="bulat-harga">15k/kg</div>
                </div>
            </div>

            <div class="kartu-layanan">
                <div class="kartu-header"> Dry Cleaning </div>
                <div class="kartu-body">
                    <p> Perawatan khusus pakaian khusus dan formal. </p>
                    <p> Layanan: </p>
                    <ul>
                        <li>Dry Cleaning</li>
                        <li>Durasi 1-2 hari</li>
                    </ul>
                    <div class="bulat-kecil"></div>
                    <div class="bulat-harga">20k-40k<br>/item</div>
                </div>
            </div>
        </div>
    </section>

    <section class="preview-pesanan-aktif">
        <div class="kartu-pesanan-aktif">
            <div class="pesanan-aktif-kiri">
                <div class="grup-keterangan">
                    <span class="badge-hijau">Cuci</span>
                    <span class="badge-biru">Express</span>
                    <span class="badge-biru">Antar</span>
                </div>
                <p class="status-teks">Baju kamu lagi dicuci!</p>
                <div class="estimasi-teks">
                    <p><strong>Estimasi pesanan selesai :</strong><br>
                    17:00 Rabu, 04-12-2026</p>
                </div>
            </div>
            <div class="pesanan-aktif-kanan">
                <p class="tanggal-atas">10:00 Rabu, 04-12-2026</p>
                <div class="rincian-biaya">
                    <p>Biaya :</p>
                    <p>Berat = 2Kg : Rp 35,000</p>
                    <p>Antar : Rp 5,000</p>
                </div>
                <div class="bawah-kanan">
                    <div class="total-harga">
                    <p>Total harga :<br>Rp 35,000</p>
                    </div>
                    <div class="note-area">
                    <p>Note :</p>
                    </div>
                </div>
                <div class="bulat-biru-kecil"></div>
                <div class="bulat-biru-besar"></div>
            </div>
        </div>
    </section>       
    
    <section class="sejarah-pesanan">
        <div class="grup-filter">
            <a href="pesanan.php?status=semua" class="tombol-filter aktif">Semua</a>
            <a href="pesanan.php?status=baru" class="tombol-filter">Baru</a>
            <a href="pesanan.php?status=proses" class="tombol-filter">Diproses</a>
            <a href="pesanan.php?status=selesai" class="tombol-filter">Selesai</a>
        </div>
        <div class="kartu-sejarah-container">
            <div class="kartu-sejarah">
                <div class="sejarah-body">
                    <div class="grup-keterangan">
                        <span class="badge-biru">11:00 Senin, 04-09-2026</span>
                        <span class="badge-biru">Reguler</span>
                        <span class="badge-biru">Pickup</span>
                        <span class="badge-biru">3kg</span>
                    </div>
                    <p> Pesanan selesai : 01:00 Selasa, 04-10-2026 </p>
                    <p> Total harga : Rp 24,000 </p>
                    <div class="tombol-detail"> Detail Pesanan </div>
                    <div class="bulat-kecil"></div>
                    <div class="bulat-harga"></div>
                </div>
            </div>
            <div class="kartu-sejarah">
                <div class="sejarah-body">
                    <div class="grup-keterangan">
                        <span class="badge-biru">11:00 Senin, 04-09-2026</span>
                        <span class="badge-biru">Reguler</span>
                        <span class="badge-biru">Pickup</span>
                        <span class="badge-biru">3kg</span>
                    </div>
                    <p> Pesanan selesai : 01:00 Selasa, 04-10-2026 </p>
                    <p> Total harga : Rp 24,000 </p>
                    <div class="tombol-detail"> Detail Pesanan </div>
                    <div class="bulat-kecil"></div>
                    <div class="bulat-harga"></div>
                </div>
            </div>
        </div>
    </section>

    <section class="kontak-preview">
        <div class="kontak-preview-kiri">
            <h1>Informasi Kontak dan <br> Media Sosial Kami </h1>
            <h3> cleanco@gmail.com </h3>
            <div class="bulat-tengah-satunya"></div>
            <div class="bulat-sudut"></div>
        </div>

        <div class="kontak-preview-kanan">
            <div class="brand-atas">
                <h2> CleanCo. </h2>
                <p> Based in Manado, Indonesia. <br> CleanCo 2026 </p>
            </div>
            <div class="brand-bawah">
                <p> Lokasi Kami: <br> Wanea, Teling Atas, Jln. Manado </p>
            </div>
        </div>
    </section>

</body>
</html>
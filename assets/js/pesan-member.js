/**
 * ============================================================
 * pesan-member.js — CleanCo Laundry
 * Digunakan di: member/pesan.php
 *
 * Berisi logika form pemesanan:
 *   - pilihLayanan()          → pilih kartu layanan
 *   - gantiOpsiPengantaran()  → toggle kurir/ambil sendiri
 *   - hitungEstimasi()        → kalkulasi harga real-time
 *   - kirimPesanan()          → validasi → POST ke server → WA → popup sukses
 *   - pesanLagi()             → reset form
 *
 * CATATAN BACKEND:
 * File ini bergantung pada variabel global sessionMember yang
 * di-output oleh PHP di pesan.php sebelum script ini diload:
 *
 *   <script>
 *   const sessionMember = {
 *       nama        : '<?= htmlspecialchars($_SESSION["nama"]) ?>',
 *       username    : '<?= htmlspecialchars($_SESSION["username"]) ?>',
 *       namaLengkap : '<?= htmlspecialchars($_SESSION["nama_lengkap"] ?? $_SESSION["nama"]) ?>',
 *       noHP        : '<?= htmlspecialchars($_SESSION["no_hp"]) ?>',
 *       id          : <?= intval($_SESSION["user_id"]) ?>
 *   };
 *   </script>
 *   <script src="../assets/js/pesan-member.js"></script>
 * ============================================================
 */

'use strict';

// ── STATE ─────────────────────────────────────────────────
// Default layanan yang dipilih saat halaman pertama dibuka.
// Nilai ini di-override oleh pilihLayanan() saat member klik kartu layanan.
// CATATAN: layananAktif.id harus cocok dengan id di tabel `layanan` di DB.
let layananAktif = {
    id    : null,      // BACKEND: diisi dari PHP via data-id pada kartu layanan
    nama  : '',
    tarif : 0,
    satuan: 'kg'
};

let opsiPengantaran = 'kurir'; // 'kurir' | 'ambil_sendiri'

/**
 * BACKEND: BIAYA_KURIR sebaiknya di-output dari DB oleh PHP,
 * bukan di-hardcode di sini.
 *
 * Cara: di pesan.php, tambahkan:
 *   const BIAYA_KURIR = <?= intval($infoWebsite['biaya_kurir']) ?>;
 * sebelum script ini diload, lalu hapus deklarasi di bawah.
 *
 * Untuk sementara hardcode 10000 sesuai konfigurasi awal.
 */
const BIAYA_KURIR = (typeof window.BIAYA_KURIR !== 'undefined')
    ? window.BIAYA_KURIR
    : 10000;

/**
 * BACKEND: WA_ADMIN sebaiknya di-output dari DB oleh PHP.
 *
 * Cara: di pesan.php, tambahkan:
 *   const WA_ADMIN = '<?= $infoWebsite["no_telepon"] ?>';
 * sebelum script ini diload, lalu hapus deklarasi di bawah.
 */
const WA_ADMIN = (typeof window.WA_ADMIN !== 'undefined')
    ? window.WA_ADMIN
    : '6282172567295';

// Fallback session jika PHP belum inject sessionMember
const _sesi = (typeof sessionMember !== 'undefined') ? sessionMember : {
    nama: 'Member', username: '@member', namaLengkap: '', noHP: '', id: 0
};


// ── PILIH LAYANAN ──────────────────────────────────────────
/**
 * Dipanggil oleh onclick="pilihLayanan(this)" pada tiap kartu layanan.
 * Kartu layanan dirender oleh PHP dari tabel `layanan` di DB:
 *
 *   <div class="kartu-pilih-layanan"
 *        data-id="<?= $l['id'] ?>"
 *        data-nama="<?= $l['nama_layanan'] ?>"
 *        data-tarif="<?= $l['tarif_per_kg'] ?>"
 *        data-satuan="kg"
 *        onclick="pilihLayanan(this)">
 */
function pilihLayanan(el) {
    document.querySelectorAll('.kartu-pilih-layanan')
            .forEach(k => k.classList.remove('dipilih'));
    el.classList.add('dipilih');

    layananAktif = {
        id    : el.dataset.id,
        nama  : el.dataset.nama,
        tarif : parseInt(el.dataset.tarif),
        satuan: el.dataset.satuan
    };
    document.getElementById('inputLayananId').value = layananAktif.id;
    hitungEstimasi();
}


// ── OPSI PENGANTARAN ───────────────────────────────────────
/**
 * Toggle tampilan kolom alamat/kecamatan dan update state.
 * Dipanggil oleh onchange="gantiOpsiPengantaran('kurir')" dst.
 */
function gantiOpsiPengantaran(opsi) {
    opsiPengantaran = opsi;

    document.querySelectorAll('.kartu-opsi-pengantaran')
            .forEach(k => k.classList.remove('dipilih-opsi'));
    const radio = document.querySelector(`input[value="${opsi}"]`);
    if (radio) radio.closest('.kartu-opsi-pengantaran').classList.add('dipilih-opsi');

    const tampilKurir = opsi === 'kurir';
    document.getElementById('infoKurir').style.display   = tampilKurir ? 'block' : 'none';
    document.getElementById('seksiAlamat').style.display = tampilKurir ? 'block' : 'none';

    if (!tampilKurir) {
        document.getElementById('inputKecamatan').value = '';
        document.getElementById('inputAlamat').value    = '';
    }
    hitungEstimasi();
}


// ── KALKULASI ESTIMASI HARGA ───────────────────────────────
/**
 * Hitung dan tampilkan estimasi harga secara real-time.
 * Dipanggil oleh oninput pada inputEstimasiBerat dan setiap perubahan opsi.
 *
 * Ini hanya estimasi — harga final ditentukan admin setelah penimbangan.
 */
function hitungEstimasi() {
    const berat      = parseFloat(document.getElementById('inputEstimasiBerat').value) || 0;
    const kotakEl    = document.getElementById('kotakEstimasi');
    const teksEl     = document.getElementById('teksEstimasiHarga');
    const biayaKurir = opsiPengantaran === 'kurir' ? BIAYA_KURIR : 0;
    const fmt        = n => 'Rp ' + n.toLocaleString('id-ID');

    if (berat <= 0 || !layananAktif.tarif) {
        kotakEl.classList.remove('kotak-estimasi-ada');
        teksEl.innerHTML = 'Harga akan dihitung admin setelah pakaian ditimbang.';
        return;
    }

    const biayaLayanan = berat * layananAktif.tarif;
    const total        = biayaLayanan + biayaKurir;

    kotakEl.classList.add('kotak-estimasi-ada');
    teksEl.innerHTML = `
        <span class="estimasi-label">Estimasi Biaya</span><br>
        ${layananAktif.nama} (${berat} kg × ${fmt(layananAktif.tarif)}) = ${fmt(biayaLayanan)}<br>
        ${opsiPengantaran === 'kurir' ? `Kurir = ${fmt(biayaKurir)}<br>` : ''}
        <strong>Total Estimasi : ${fmt(total)}</strong>
        <span class="estimasi-belum-final">— Harga Belum Final</span>
    `;
}


// ── KIRIM PESANAN ──────────────────────────────────────────
/**
 * Validasi form → POST ke server → buka WA admin → tampil popup sukses.
 *
 * BACKEND:
 *   POST /api/pesanan/buat  (atau proses-pesanan.php)
 *   Body JSON:
 *   {
 *     "layanan_id"       : <int>,
 *     "opsi_pengantaran" : "kurir"|"ambil_sendiri",
 *     "kecamatan"        : "Wanea"|"" ,
 *     "alamat"           : "Jl. ...",
 *     "estimasi_berat"   : 2.5|null,
 *     "catatan"          : "...|null"
 *   }
 *   Response JSON:
 *   {
 *     "success"  : true,
 *     "kode"     : "LDR-20261204-3847",  ← kode dari server/DB
 *     "id"       : 42
 *   }
 *
 *   PHP (proses-pesanan.php):
 *   session_start();
 *   $body = json_decode(file_get_contents('php://input'), true);
 *   // Buat kode unik: 'LDR-' . date('Ymd') . '-' . rand(1000, 9999)
 *   // INSERT INTO pesanan (...) VALUES (...)
 *   // INSERT INTO riwayat_status (...) — status awal: menunggu_konfirmasi
 *   // echo json_encode(['success' => true, 'kode' => $kode, 'id' => $id]);
 *
 * CATATAN WINDOW.OPEN():
 * window.open() dipanggil SEBELUM await fetch() karena browser
 * hanya mengizinkan popup dari user gesture langsung (klik tombol).
 * Jika dipanggil setelah await, browser akan memblokirnya.
 */
async function kirimPesanan(e) {
    e.preventDefault();

    // ── Validasi client-side ────────────────────────────
    if (!layananAktif.id) {
        alert('Pilih jenis layanan terlebih dahulu.');
        return;
    }
    if (opsiPengantaran === 'kurir') {
        if (!document.getElementById('inputKecamatan').value) {
            alert('Pilih kecamatan tujuan terlebih dahulu.');
            return;
        }
        if (!document.getElementById('inputAlamat').value.trim()) {
            alert('Masukkan alamat lengkap tujuan pengantaran.');
            return;
        }
    }

    const kecamatan    = document.getElementById('inputKecamatan').value;
    const alamat       = document.getElementById('inputAlamat')?.value.trim() || '';
    const estimasiBerat = parseFloat(document.getElementById('inputEstimasiBerat').value) || null;
    const catatan      = document.getElementById('inputCatatan')?.value.trim() || null;
    const biayaKurir   = opsiPengantaran === 'kurir' ? BIAYA_KURIR : 0;

    // ── Buka WA admin DULU (sinkron, sebelum await) ─────
    // Format pesan WA menggunakan placeholder kode sementara.
    // Kode final dari server akan ditampilkan di popup sukses.
    const pesanWA = encodeURIComponent(
        `Halo Admin CleanCo! 🧺\n\n` +
        `Saya baru saja membuat pesanan baru:\n` +
        `• Nama        : ${_sesi.nama}\n` +
        `• Layanan     : ${layananAktif.nama}\n` +
        `• Pengantaran : ${opsiPengantaran === 'kurir' ? 'Kurir' : 'Ambil Sendiri'}\n` +
        (opsiPengantaran === 'kurir' ? `• Kecamatan   : ${kecamatan}\n` : '') +
        `\nMohon konfirmasinya. Terima kasih!`
    );
    window.open(`https://wa.me/${WA_ADMIN}?text=${pesanWA}`, '_blank');

    // ── POST ke server ───────────────────────────────────
    let kodeServer = null;
    try {
        const res  = await fetch('/api/pesanan/buat', {
            method : 'POST',
            headers: { 'Content-Type': 'application/json' },
            body   : JSON.stringify({
                layanan_id       : layananAktif.id,
                opsi_pengantaran : opsiPengantaran,
                kecamatan        : opsiPengantaran === 'kurir' ? kecamatan : '',
                alamat           : opsiPengantaran === 'kurir' ? alamat    : '',
                estimasi_berat   : estimasiBerat,
                catatan
            })
        });
        const json = await res.json();
        if (json.success) {
            kodeServer = json.kode; // kode resmi dari DB
        } else {
            console.error('kirimPesanan: server error —', json.message);
        }
    } catch (err) {
        console.error('kirimPesanan: fetch gagal —', err);
    }

    // ── Tampilkan popup sukses ───────────────────────────
    const noPesanan    = kodeServer ? '#' + kodeServer : '— (cek WhatsApp)';
    const totalEstimasi = estimasiBerat
        ? 'Rp ' + (estimasiBerat * layananAktif.tarif + biayaKurir).toLocaleString('id-ID')
        : 'Akan dihitung setelah ditimbang';

    document.getElementById('popupNoPesanan').textContent   = noPesanan;
    document.getElementById('popupLayanan').textContent     = layananAktif.nama;
    document.getElementById('popupPengantaran').textContent =
        opsiPengantaran === 'kurir'
            ? 'Kurir Laundry — ' + kecamatan
            : 'Ambil Sendiri ke Outlet';
    document.getElementById('popupEstimasi').textContent    = totalEstimasi;

    document.getElementById('overlayPopup').style.display  = 'block';
    document.getElementById('popupSukses').style.display   = 'block';
}


// ── PESAN LAGI ─────────────────────────────────────────────
/**
 * Tutup popup sukses dan reset form untuk pesanan baru.
 */
function pesanLagi() {
    document.getElementById('overlayPopup').style.display  = 'none';
    document.getElementById('popupSukses').style.display   = 'none';
    document.getElementById('inputEstimasiBerat').value    = '';
    document.getElementById('inputCatatan').value          = '';
    document.getElementById('inputKecamatan').value        = '';
    document.getElementById('inputAlamat').value           = '';
    hitungEstimasi();
}


// ── INIT ───────────────────────────────────────────────────
// Default opsi pengantaran saat halaman pertama dibuka.
// Kartu layanan yang dipilih default ditangani oleh PHP lewat class 'dipilih'
// dan attribute hidden input #inputLayananId di pesan.php.
gantiOpsiPengantaran('kurir');
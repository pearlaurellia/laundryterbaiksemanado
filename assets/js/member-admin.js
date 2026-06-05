/**
 * ============================================================
 * member-admin.js — CleanCo Laundry
 * Digunakan di: admin/member.php
 *
 * CATATAN BACKEND:
 * Data member tidak lagi dari objek dummy di JS.
 * Dua pendekatan yang bisa dipilih:
 *
 * PENDEKATAN A — PHP render HTML, JS hanya interaksi (DISARANKAN):
 *   PHP melakukan query dan merender seluruh list .item-member
 *   dan semua data di panel detail langsung sebagai HTML.
 *   JS hanya mengurus:
 *     - Buka/tutup panel detail
 *     - Popup konfirmasi toggle status
 *     - Filter dan search di DOM yang sudah ada
 *
 * PENDEKATAN B — JS fetch dari API:
 *   Tambahkan endpoint GET /api/member/:id dan isi fungsi
 *   bukaMember() dengan fetch, lalu isi elemen detail dari response.
 *
 * File ini menggunakan Pendekatan A.
 * dataMember dibaca dari data-* attribute pada elemen HTML
 * yang di-render oleh PHP, bukan dari objek JS.
 * ============================================================
 */

'use strict';

let idAktifMember  = null;
let aksiToggleSaat = null; // 'aktif' | 'nonaktif'


// ── BUKA DETAIL MEMBER ────────────────────────────────────
/**
 * Buka panel detail member saat item di sidebar diklik.
 * Data diambil dari data-* attribute pada elemen .item-member
 * yang di-render oleh PHP.
 *
 * PHP harus merender tiap item seperti ini:
 *   <div class="item-member"
 *        data-id="<?= $m['id'] ?>"
 *        data-nama="<?= htmlspecialchars($m['nama']) ?>"
 *        data-status="<?= $m['status_akun'] ?>"
 *        onclick="bukaMember(<?= $m['id'] ?>, this)">
 *     ...
 *   </div>
 *
 * Dan panel detail sudah dirender oleh PHP dengan id-id elemen
 * yang sesuai (detailNama, detailEmail, dst).
 *
 * ALTERNATIF — jika ingin full JS:
 *   Hapus konten PHP dari panel detail, lalu fetch di sini:
 *   const res = await fetch('/api/member/' + id);
 *   const { data: m } = await res.json();
 *   // lalu isi elemen seperti biasa
 */
function bukaMember(id, el) {
    idAktifMember = id;

    document.querySelectorAll('.item-member').forEach(i => i.classList.remove('aktif-dipilih'));
    el.classList.add('aktif-dipilih');

    document.getElementById('detailKosong').style.display = 'none';
    document.getElementById('detailIsi').style.display    = 'block';

    // ── Baca data dari attribute HTML yang di-render PHP ──
    // Jika PHP sudah render panel detail langsung (bukan via JS),
    // bagian ini tidak perlu — panel detail sudah terisi.
    //
    // Jika PHP memakai pendekatan "satu panel detail kosong yang diisi JS",
    // PHP perlu menaruh data di data-* attribute item-member,
    // dan JS mengisi panel dari sana. Contoh:
    //
    // const m = {
    //     nama             : el.dataset.nama,
    //     username         : el.dataset.username,
    //     namaLengkap      : el.dataset.namaLengkap,
    //     email            : el.dataset.email,
    //     noHP             : el.dataset.noHp,
    //     alamat           : el.dataset.alamat,
    //     kecamatan        : el.dataset.kecamatan,
    //     tanggalBergabung : el.dataset.tanggalBergabung,
    //     status           : el.dataset.status,
    //     jmlPesanan       : el.dataset.jmlPesanan,
    //     pesananSelesai   : el.dataset.pesananSelesai,
    //     pesananAktif     : el.dataset.pesananAktif,
    //     pesananBatal     : el.dataset.pesananBatal,
    //     totalOmzet       : el.dataset.totalOmzet
    // };
    //
    // document.getElementById('detailNama').textContent = m.nama;
    // ... dan seterusnya

    // Status akun — diambil dari data-status attribute
    const status = el.dataset.status || 'aktif';
    setStatusAkunUI(status);
}


// ── SET UI STATUS AKUN ────────────────────────────────────
function setStatusAkunUI(status) {
    const teksEl      = document.getElementById('statusAkunTeks');
    const tombolAktif = document.getElementById('tombolAktifkan');
    const tombolNon   = document.getElementById('tombolNonaktif');

    if (status === 'aktif') {
        teksEl.textContent        = 'Aktif';
        teksEl.style.color        = '#52c49c';
        tombolAktif.style.display = 'none';
        tombolNon.style.display   = 'inline-block';
    } else {
        teksEl.textContent        = 'Nonaktif';
        teksEl.style.color        = '#f87171';
        tombolAktif.style.display = 'inline-block';
        tombolNon.style.display   = 'none';
    }
}


// ── TOGGLE STATUS AKUN ────────────────────────────────────
/**
 * Buka popup konfirmasi untuk aktifkan/nonaktifkan akun member.
 */
function toggleStatusMember(aksi) {
    aksiToggleSaat = aksi;

    // Ambil nama dari panel detail yang sudah terisi
    const namaTampil = document.getElementById('detailNama')?.textContent || 'Member ini';

    document.getElementById('popupJudul').textContent =
        aksi === 'nonaktif' ? 'Nonaktifkan Akun?' : 'Aktifkan Kembali Akun?';
    document.getElementById('popupTeks').textContent =
        aksi === 'nonaktif'
            ? `${namaTampil} tidak akan bisa login setelah dinonaktifkan.`
            : `${namaTampil} akan bisa login kembali ke sistem.`;

    const tombolKonfirm = document.getElementById('popupTombolKonfirm');
    if (tombolKonfirm) {
        tombolKonfirm.style.backgroundColor = aksi === 'nonaktif' ? '#f87171' : '#52c49c';
        tombolKonfirm.style.color           = aksi === 'nonaktif' ? 'white'   : '#1a4d3a';
    }

    document.getElementById('overlayPopup').style.display   = 'block';
    document.getElementById('popupKonfirmasi').style.display = 'block';
}

/**
 * Eksekusi toggle status: kirim ke server lalu update UI.
 *
 * BACKEND:
 *   POST /api/member/:id/toggle-status
 *   Body JSON: { "status": "aktif"|"nonaktif" }
 *   Response JSON: { "success": true }
 *
 *   PHP (admin/api/toggle-member.php):
 *   $id     = intval($_GET['id']);
 *   $body   = json_decode(file_get_contents('php://input'), true);
 *   $status = $body['status'] === 'aktif' ? 'aktif' : 'nonaktif';
 *   $pdo->prepare("UPDATE users SET status_akun = ? WHERE id = ? AND role = 'member'")
 *       ->execute([$status, $id]);
 *   echo json_encode(['success' => true]);
 */
async function konfirmasiToggle() {
    if (!idAktifMember || !aksiToggleSaat) return;

    try {
        const res  = await fetch(`/api/member/${idAktifMember}/toggle-status`, {
            method : 'POST',
            headers: { 'Content-Type': 'application/json' },
            body   : JSON.stringify({ status: aksiToggleSaat })
        });
        const json = await res.json();
        if (!json.success) {
            console.error('konfirmasiToggle: server error —', json.message);
            tutupPopup();
            return;
        }
    } catch (err) {
        console.error('konfirmasiToggle: fetch gagal —', err);
        tutupPopup();
        return;
    }

    // Update badge di sidebar
    const itemEl  = document.querySelector(`.item-member[data-id="${idAktifMember}"]`);
    const badgeEl = itemEl?.querySelector('.badge-status-member');
    if (itemEl && badgeEl) {
        if (aksiToggleSaat === 'aktif') {
            badgeEl.textContent   = 'Aktif';
            badgeEl.className     = 'badge-status-member badge-member-aktif';
            itemEl.dataset.status = 'aktif';
        } else {
            badgeEl.textContent   = 'Nonaktif';
            badgeEl.className     = 'badge-status-member badge-member-nonaktif';
            itemEl.dataset.status = 'nonaktif';
        }
    }

    setStatusAkunUI(aksiToggleSaat);
    tutupPopup();
}

function tutupPopup() {
    document.getElementById('overlayPopup').style.display    = 'none';
    document.getElementById('popupKonfirmasi').style.display = 'none';
    aksiToggleSaat = null;
}


// ── FILTER ────────────────────────────────────────────────
/**
 * Filter list member berdasarkan status akun.
 * Bekerja pada elemen DOM yang sudah dirender PHP.
 */
function filterMember(status, btn) {
    document.querySelectorAll('.tombol-filter').forEach(b => b.classList.remove('aktif'));
    btn.classList.add('aktif');
    document.querySelectorAll('.item-member').forEach(item => {
        const cocok = status === 'semua' || item.dataset.status === status;
        item.style.display = cocok ? 'block' : 'none';
    });
}


// ── SEARCH ────────────────────────────────────────────────
/**
 * Cari member berdasarkan nama.
 * Bekerja pada elemen DOM — tidak perlu fetch ke server untuk use case ini.
 * Jika jumlah member sangat besar, pertimbangkan debounced fetch ke API.
 */
function cariMember(query) {
    const q = query.toLowerCase().trim();
    document.querySelectorAll('.item-member').forEach(item => {
        const nama = (item.dataset.nama || '').toLowerCase();
        item.style.display = nama.includes(q) ? 'block' : 'none';
    });
}
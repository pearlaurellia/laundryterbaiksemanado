'use strict';

let idAktif = null;
let aksiToggleSaat = null;

function bukaMember(id, el) {
    idAktif = id;
    const m = dataMember[id]; 
    if (!m) return;

    document.querySelectorAll('.item-member').forEach(i => i.classList.remove('aktif-dipilih'));
    el.classList.add('aktif-dipilih');

    document.getElementById('detailKosong').style.display = 'none';
    document.getElementById('detailIsi').style.display    = 'block';

    document.getElementById('detailNama').textContent             = m.nama;
    document.getElementById('detailUsername').textContent         = m.email;
    document.getElementById('detailTanggalBergabung').textContent = 'Bergabung ' + m.tanggalBergabung;
    document.getElementById('detailNamaLengkap').textContent      = m.namaLengkap;
    document.getElementById('detailEmail').textContent            = m.email;
    document.getElementById('detailNoHP').textContent             = m.noHP;
    document.getElementById('detailTotalTransaksi').textContent   = m.jmlPesanan + ' pesanan';

    document.getElementById('detailJmlPesanan').textContent     = m.jmlPesanan;
    document.getElementById('detailPesananSelesai').textContent = 'Selesai & Lunas : ' + m.pesananSelesai;
    document.getElementById('detailPesananAktif').textContent   = 'Sedang Aktif : '    + m.pesananAktif;
    document.getElementById('detailPesananBatal').textContent   = 'Dibatalkan : '       + m.pesananBatal;
    document.getElementById('detailTotalOmzet').textContent     = 'Total Nilai : Rp ' + Number(m.totalOmzet).toLocaleString('id-ID');

    // Render list 3 pesanan terakhir secara dinamis
    const riwayatEl = document.getElementById('detailRiwayatSingkat');
    if (!m.riwayatSingkat || m.riwayatSingkat.length === 0) {
        riwayatEl.innerHTML = '<p style="color:#aaa;font-size:0.9rem;">Belum ada pesanan.</p>';
    } else {
        riwayatEl.innerHTML = m.riwayatSingkat.map(r => {
            const warna = r.status === 'selesai' ? '#52c49c'
                        : r.status === 'dikonfirmasi' || r.status === 'sedang_dicuci' ? '#3b82f6'
                        : r.status === 'dibatalkan' ? '#f87171' : '#aaa';
            
            const labelStatus = {
                selesai: 'Selesai', dikonfirmasi: 'Dikonfirmasi',
                sedang_dicuci: 'Dicuci', siap_diambil: 'Siap Diambil',
                sedang_diantar: 'Diantar', dibatalkan: 'Dibatalkan',
                menunggu_konfirmasi: 'Menunggu'
            }[r.status] || r.status;

            return `
                <div class="baris-riwayat-singkat" style="display:flex; justify-content:space-between; margin-bottom:8px; background:#f9fafb; padding:10px; border-radius:8px;">
                    <span class="riwayat-kode" style="font-weight:bold;">#${r.kode}</span>
                    <span class="riwayat-layanan">${r.layanan}</span>
                    <span class="riwayat-total" style="color:var(--tealmuda); font-weight:600;">${r.total}</span>
                    <span class="riwayat-status-badge" style="background:${warna}20; color:${warna}; padding:2px 8px; border-radius:4px; font-size:0.8rem; font-weight:bold;">
                        ${labelStatus}
                    </span>
                </div>`;
        }).join('');
    }

    // Normalisasi format link WhatsApp Gateway Indonesia (62)
    document.getElementById('tombolWA').href = 'https://wa.me/62' + m.noHP.replace(/^0/, '');
    setStatusAkunUI(m.status);
}

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
        teksEl.textContent        = 'Nonaktif (Banned)';
        teksEl.style.color        = '#f87171';
        tombolAktif.style.display = 'inline-block';
        tombolNon.style.display   = 'none';
    }
}

function toggleStatusMember(aksi) {
    aksiToggleSaat = aksi;
    const m = dataMember[idAktif];
    if (!m) return;

    document.getElementById('popupJudul').textContent = aksi === 'nonaktif' ? 'Nonaktifkan Akun?' : 'Aktifkan Kembali Akun?';
    document.getElementById('popupTeks').textContent = aksi === 'nonaktif'
        ? `${m.nama} tidak akan bisa login atau membuat pesanan fiktif setelah dinonaktifkan.`
        : `${m.nama} akan dipulihkan hak akses loginnya ke dalam sistem.`;

    const tombolKonfirm = document.getElementById('popupTombolKonfirm');
    tombolKonfirm.style.backgroundColor = aksi === 'nonaktif' ? '#f87171' : '#52c49c';
    tombolKonfirm.style.color           = aksi === 'nonaktif' ? 'white'   : '#1a4d3a';

    document.getElementById('overlayPopup').style.display    = 'block';
    document.getElementById('popupKonfirmasi').style.display = 'block';
}

// PERBAIKAN: Validasi ketat membaca kiriman respons balik server database
async function konfirmasiToggle() {
    if (!idAktif || !aksiToggleSaat) return;

    try {
        const response = await fetch(`member.php?action=toggle_status`, {
            method: 'POST',
            headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
            body: `id=${idAktif}&status=${aksiToggleSaat}`
        });

        const json = await response.json();

        if (json.success) {
            // Sinkronisasi data lokal state runtime
            dataMember[idAktif].status = aksiToggleSaat;

            // Mutasi komponen visual di sidebar tanpa refresh
            const itemEl  = document.querySelector(`.item-member[data-id="${idAktif}"]`);
            const badgeEl = itemEl?.querySelector('.badge-status-member');
            if (badgeEl) {
                badgeEl.textContent = aksiToggleSaat === 'aktif' ? 'Aktif' : 'Nonaktif';
                badgeEl.className   = aksiToggleSaat === 'aktif'
                    ? 'badge-status-member badge-member-aktif'
                    : 'badge-status-member badge-member-nonaktif';
                itemEl.dataset.status = aksiToggleSaat;
            }

            setStatusAkunUI(aksiToggleSaat);
            alert(`Status akun member berhasil diubah menjadi ${aksiToggleSaat}!`);
        } else {
            alert(json.message || 'Gagal mengubah status akun member.');
        }
    } catch (err) {
        console.error('Fetch error:', err);
        alert('Gangguan koneksi Apache server lokal.');
    } finally {
        tutupPopup();
    }
}

function tutupPopup() {
    document.getElementById('overlayPopup').style.display    = 'none';
    document.getElementById('popupKonfirmasi').style.display = 'none';
    aksiToggleSaat = null;
}

function filterMember(status, btn) {
    document.querySelectorAll('.tombol-filter').forEach(b => b.classList.remove('aktif'));
    btn.classList.add('aktif');
    document.querySelectorAll('.item-member').forEach(item => {
        const cocok = status === 'semua' || item.dataset.status === status;
        item.style.display = cocok ? 'block' : 'none';
    });
}

function cariMember(query) {
    const q = query.toLowerCase().trim();
    document.querySelectorAll('.item-member').forEach(item => {
        item.style.display = item.dataset.nama.includes(q) ? 'block' : 'none';
    });
}
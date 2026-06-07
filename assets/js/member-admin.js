'use strict';

let idAktif = null;
let aksiToggleSaat = null;


function bukaMember(id, el) {
    idAktif = id;
    
    if (typeof dataMember === 'undefined' || !dataMember[id]) return;
    const m = dataMember[id]; 

    document.querySelectorAll('.item-member').forEach(item => item.classList.remove('aktif-dipilih'));
    el.classList.add('aktif-dipilih');

    const detailKosong = document.getElementById('detailKosong');
    const detailIsi = document.getElementById('detailIsi');
    if (detailKosong) detailKosong.style.display = 'none';
    if (detailIsi) detailIsi.style.display = 'block';

    document.getElementById('detailNama').textContent = m.nama || '—';
    document.getElementById('detailUsername').textContent = m.email || '—';
    document.getElementById('detailTanggalBergabung').textContent = m.tanggalBergabung ? 'Bergabung ' + m.tanggalBergabung : '—';
    document.getElementById('detailNamaLengkap').textContent = m.namaLengkap || '—';
    document.getElementById('detailEmail').textContent = m.email || '—';
    document.getElementById('detailNoHP').textContent = m.noHP || '—';
    document.getElementById('detailTotalTransaksi').textContent = (m.jmlPesanan || 0) + ' pesanan';

    document.getElementById('detailJmlPesanan').textContent = m.jmlPesanan || 0;
    document.getElementById('detailPesananSelesai').textContent = 'Selesai & Lunas : ' + (m.pesananSelesai || 0);
    document.getElementById('detailPesananAktif').textContent = 'Sedang Aktif : ' + (m.pesananAktif || 0);
    document.getElementById('detailPesananBatal').textContent = 'Dibatalkan : ' + (m.pesananBatal || 0);
    document.getElementById('detailTotalOmzet').textContent = 'Total Nilai : Rp ' + Number(m.totalOmzet || 0).toLocaleString('id-ID');

    const riwayatEl = document.getElementById('detailRiwayatSingkat');
    if (riwayatEl) {
        if (!m.riwayatSingkat || m.riwayatSingkat.length === 0) {
            riwayatEl.innerHTML = '<p style="color:#aaa; font-size:0.9rem; font-style:italic; padding: 5px 0;">Belum ada riwayat pesanan.</p>';
        } else {
            riwayatEl.innerHTML = m.riwayatSingkat.map(r => {
                const warna = r.status === 'selesai' ? '#52c49c'
                            : (r.status === 'dikonfirmasi' || r.status === 'sedang_dicuci' || r.status === 'siap_diambil' || r.status === 'sedang_diantar') ? '#3b82f6'
                            : r.status === 'dibatalkan' ? '#ef4444' : '#6b7280';
                
                const labelStatus = {
                    selesai: 'Selesai', 
                    dikonfirmasi: 'Dikonfirmasi',
                    sedang_dicuci: 'Dicuci', 
                    siap_diambil: 'Siap Diambil',
                    sedang_diantar: 'Diantar', 
                    dibatalkan: 'Dibatalkan',
                    menunggu_konfirmasi: 'Menunggu'
                }[r.status] || r.status;

                return `
                    <div class="baris-riwayat-singkat" style="display:flex; align-items:center; justify-content:space-between; margin-bottom:8px; background:#f9fafb; padding:10px; border-radius:8px; border: 1px solid #e5e7eb;">
                        <span class="riwayat-kode" style="font-weight:bold; color:#1e293b;">#${r.kode}</span>
                        <span class="riwayat-layanan" style="color:#475569; font-size:0.9rem;">${r.layanan}</span>
                        <span class="riwayat-total" style="color:#0d3f8a; font-weight:600;">${r.total}</span>
                        <span class="riwayat-status-badge" style="background:${warna}15; color:${warna}; padding:3px 8px; border-radius:4px; font-size:0.75rem; font-weight:bold; border: 1px solid ${warna}30;">
                            ${labelStatus}
                        </span>
                    </div>`;
            }).join('');
        }
    }

    const tombolWA = document.getElementById('tombolWA');
    if (tombolWA && m.noHP) {
        tombolWA.href = 'https://wa.me/62' + m.noHP.replace(/\D/g, '').replace(/^62/, '').replace(/^0/, '');
    }
    
    setStatusAkunUI(m.status);
}

function setStatusAkunUI(status) {
    const teksEl = document.getElementById('statusAkunTeks');
    const tombolAktif = document.getElementById('tombolAktifkan');
    const tombolNon = document.getElementById('tombolNonaktif');

    if (!teksEl || !tombolAktif || !tombolNon) return;

    if (status === 'aktif') {
        teksEl.textContent = 'Aktif';
        teksEl.style.color = '#52c49c';
        tombolAktif.style.display = 'none';
        tombolNon.style.display = 'inline-block';
    } else {
        teksEl.textContent = 'Nonaktif (Suspended)';
        teksEl.style.color = '#ef4444';
        tombolAktif.style.display = 'inline-block';
        tombolNon.style.display = 'none';
    }
}

function toggleStatusMember(aksi) {
    if (idAktif === null || typeof dataMember === 'undefined' || !dataMember[idAktif]) return;
    
    aksiToggleSaat = aksi;
    const m = dataMember[idAktif];

    const popupJudul = document.getElementById('popupJudul');
    const popupTeks = document.getElementById('popupTeks');
    const tombolKonfirm = document.getElementById('popupTombolKonfirm');

    if (popupJudul) {
        popupJudul.textContent = aksi === 'nonaktif' ? 'Nonaktifkan Akun Pelanggan?' : 'Aktifkan Kembali Akun?';
    }
    if (popupTeks) {
        popupTeks.innerHTML = aksi === 'nonaktif'
            ? `Akun atas nama <strong>${m.nama}</strong> tidak akan diberikan izin hak akses login ataupun memicu pesanan fiktif baru setelah dibekukan.`
            : `Hak konektivitas login akun atas nama <strong>${m.nama}</strong> akan dipulihkan sepenuhnya ke dalam sistem CleanCo.`;
    }

    if (tombolKonfirm) {
        tombolKonfirm.style.backgroundColor = aksi === 'nonaktif' ? '#ef4444' : '#52c49c';
        tombolKonfirm.style.color = aksi === 'nonaktif' ? '#ffffff' : '#1a4d3a';
    }

    document.getElementById('overlayPopup').style.display = 'block';
    document.getElementById('popupKonfirmasi').style.display = 'block';
}

async function konfirmasiToggle() {
    if (!idAktif || !aksiToggleSaat) return;

    try {
        const response = await fetch('member.php?action=toggle_status', {
            method: 'POST',
            headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
            body: `id=${encodeURIComponent(idAktif)}&status=${encodeURIComponent(aksiToggleSaat)}`
        });

        const json = await response.json();

        if (json.success) {
            dataMember[idAktif].status = aksiToggleSaat;

            const itemEl = document.querySelector(`.item-member[data-id="${idAktif}"]`);
            if (itemEl) {
                itemEl.dataset.status = aksiToggleSaat;
                const badgeEl = itemEl.querySelector('.badge-status-member');
                if (badgeEl) {
                    badgeEl.textContent = aksiToggleSaat === 'aktif' ? 'Aktif' : 'Nonaktif';
                    badgeEl.className = aksiToggleSaat === 'aktif'
                        ? 'badge-status-member badge-member-aktif'
                        : 'badge-status-member badge-member-nonaktif';
                }
            }

            setStatusAkunUI(aksiToggleSaat);
            alert(`Sistem Berhasil! Status akun member telah disesuaikan menjadi: ${aksiToggleSaat}.`);
        } else {
            alert(json.message || 'Gagal merubah hak akses status member.');
        }
    } catch (err) {
        console.error('konfirmasiToggle crashed:', err);
        alert('Koneksi terputus. Gagal meraih Apache local database server.');
    } finally {
        tutupPopup();
    }
}

function tutupPopup() {
    document.getElementById('overlayPopup').style.display = 'none';
    document.getElementById('popupKonfirmasi').style.display = 'none';
    aksiToggleSaat = null;
}

function filterMember(status, btn) {
    document.querySelectorAll('.tombol-filter').forEach(b => b.classList.remove('aktif'));
    btn.classList.add('aktif');
    
    const inputCari = document.getElementById('inputCariMember');
    if (inputCari) inputCari.value = '';

    document.querySelectorAll('.item-member').forEach(item => {
        const cocok = status === 'semua' || item.dataset.status === status;
        item.style.display = cocok ? 'block' : 'none';
    });
}


function cariMember(query) {
    const q = query.toLowerCase().trim();
    
    document.querySelectorAll('.tombol-filter').forEach(b => b.classList.remove('aktif'));
    const btnSemua = document.querySelector('.tombol-filter[onclick*="semua"]');
    if (btnSemua) btnSemua.classList.add('aktif');

    document.querySelectorAll('.item-member').forEach(item => {
        const namaMentah = item.dataset.nama || '';
        const namaMember = namaMentah.toLowerCase();
        
        item.style.display = namaMember.includes(q) ? 'block' : 'none';
    });
}
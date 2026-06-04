// ── LABEL & KELAS BADGE PER STATUS ────────────────────────
const _labelStatus = {
    menunggu_konfirmasi : 'Menunggu Konfirmasi',
    dikonfirmasi        : 'Dikonfirmasi',
    sedang_dicuci       : 'Sedang Dicuci',
    siap_diambil        : 'Siap Diambil',
    sedang_diantar      : 'Sedang Diantar',
    selesai             : 'Selesai',
    dibatalkan          : 'Dibatalkan'
};

const _kelasStatus = {
    menunggu_konfirmasi : 'badge-status badge-status-baru',
    dikonfirmasi        : 'badge-status badge-status-dikonfirmasi',
    sedang_dicuci       : 'badge-status badge-status-diproses',
    siap_diambil        : 'badge-status badge-status-selesai',
    sedang_diantar      : 'badge-status badge-status-diproses',
    selesai             : 'badge-status badge-status-selesai',
    dibatalkan          : 'badge-status badge-status-batal'
};

// ── SET STATUS UI (admin/pesanan.php) ──────────────────────
function setStatusUI(status) {
    // Highlight tombol aktif
    document.querySelectorAll('.tombol-status').forEach(btn => {
        btn.classList.remove('tombol-status-aktif');
    });
    const activeBtn = document.querySelector(`.tombol-status[data-status="${status}"]`);
    if (activeBtn) activeBtn.classList.add('tombol-status-aktif');

    // Teks status aktif
    const teksEl = document.getElementById('statusAktifTeks');
    if (teksEl) teksEl.textContent = _labelStatus[status] || status;

    // ── Grup tombol aksi bertahap ──
    // Sesuai dokumen: admin terima/tolak dulu, baru bisa timbang
    const grupAksiEl = document.getElementById('grupAksiAdmin');
    if (grupAksiEl) {
        grupAksiEl.innerHTML = _renderTombolAksi(status);
    }

    // Tombol batalkan: hanya tampil jika belum dibatalkan
    const tombolBatal = document.getElementById('tombolBatalkanAdmin');
    const infoBatal   = document.getElementById('infoSudahDibatalkan');
    if (tombolBatal && infoBatal) {
        if (status === 'dibatalkan') {
            tombolBatal.style.display = 'none';
            infoBatal.style.display   = 'block';
        } else if (status === 'selesai') {
            tombolBatal.style.display = 'none';
            infoBatal.style.display   = 'none';
        } else {
            tombolBatal.style.display = 'inline-block';
            infoBatal.style.display   = 'none';
        }
    }
}

// ── RENDER TOMBOL AKSI SESUAI STATUS ──────────────────────
// Dokumen: status harus bergerak tahap demi tahap
function _renderTombolAksi(status) {
    // Tahap 1.5: belum konfirmasi → tampilkan Terima + Tolak
    if (status === 'menunggu_konfirmasi') {
        return `
            <button class="tombol-status tombol-status-terima"
                    onclick="updateStatus('dikonfirmasi')">
                ✓ Terima Pesanan
            </button>
            <button class="tombol-status tombol-status-tolak"
                    onclick="batalkanPesananAdmin(idAktif)">
                ✕ Tolak Pesanan
            </button>`;
    }

    // Tahap 2: sudah dikonfirmasi → tampilkan Proses & Timbang
    if (status === 'dikonfirmasi') {
        return `
            <button class="tombol-status tombol-status-aktif"
                    data-status="dikonfirmasi"
                    disabled>
                Dikonfirmasi
            </button>
            <button class="tombol-status"
                    data-status="sedang_dicuci"
                    onclick="prosesTimbang()">
                ⚖ Proses & Timbang
            </button>`;
    }

    // Tahap 3+: update status tahap demi tahap
    // Dokumen: jalur ambil_sendiri → siap_diambil, jalur kurir → sedang_diantar
    if (status === 'sedang_dicuci') {
        dataPesanan = _muatData();
        const p   = dataPesanan[idAktif];
        const opsi = p ? p.opsi : 'kurir';
        const berikutStatus = opsi === 'kurir' ? 'sedang_diantar' : 'siap_diambil';
        const berikutLabel  = opsi === 'kurir' ? 'Sedang Diantar' : 'Siap Diambil';
        return `
            <button class="tombol-status tombol-status-aktif"
                    data-status="sedang_dicuci" disabled>
                Sedang Dicuci
            </button>
            <button class="tombol-status"
                    data-status="${berikutStatus}"
                    onclick="updateStatus('${berikutStatus}')">
                → ${berikutLabel}
            </button>`;
    }

    if (status === 'siap_diambil' || status === 'sedang_diantar') {
        return `
            <button class="tombol-status tombol-status-aktif"
                    data-status="${status}" disabled>
                ${_labelStatus[status]}
            </button>
            <button class="tombol-status"
                    data-status="selesai"
                    onclick="updateStatus('selesai')">
                ✓ Selesai & Lunas
            </button>`;
    }

    if (status === 'selesai') {
        return `<button class="tombol-status tombol-status-aktif"
                        data-status="selesai" disabled>
                    Selesai & Lunas
                </button>`;
    }

    if (status === 'dibatalkan') {
        return `<button class="tombol-status"
                        style="background:#FFD1D1;color:#D32F2F;" disabled>
                    Dibatalkan
                </button>`;
    }

    return '';
}

// ── PROSES & TIMBANG (hanya dari status dikonfirmasi) ──────
function prosesTimbang() {
    const beratEl = document.getElementById('inputBerat');
    const berat   = parseFloat(beratEl ? beratEl.value : 0);
    if (!berat || berat <= 0) {
        alert('Masukkan berat aktual terlebih dahulu sebelum memproses.');
        if (beratEl) beratEl.focus();
        return;
    }

    // Simpan berat lalu update status ke sedang_dicuci
    if (idAktif) {
        dataPesanan = _muatData();
        dataPesanan[idAktif].berat = berat;
        _simpanData(dataPesanan);
        updateStatus('sedang_dicuci');
    }
}

// ── UPDATE STATUS ──────────────────────────────────────────
function updateStatus(status) {
    if (!idAktif) return;

    _updateStatusPesanan(idAktif, status, null, null);
    dataPesanan = _muatData();

    setStatusUI(status);
    hitungBiaya();

    // Re-render list supaya badge dan urutan di sidebar sinkron
    renderListPesanan('semua');

    // Kembalikan tombol filter ke "Semua"
    document.querySelectorAll('.tombol-filter').forEach(b => b.classList.remove('aktif'));
    const btnSemua = document.querySelector('.tombol-filter');
    if (btnSemua) btnSemua.classList.add('aktif');
}
// ── RENDER LIST PESANAN ───────────────────────────────────────
function renderListPesanan(filterStatus) {
    dataPesanan = _muatData();
    const listEl = document.getElementById('listPesanan');
    if (!listEl) return;
    
    listEl.innerHTML = '';

    // Urutkan: pesanan terbaru (id terbesar) tampil di atas
    const entri = Object.entries(dataPesanan)
        .filter(([id, p]) => filterStatus === 'semua' || p.status === filterStatus)
        .sort(([idA], [idB]) => Number(idB) - Number(idA));
    
    entri.forEach(([id, p]) => {
        const badgeKelas = _kelasStatus[p.status] || 'badge-status';
        const tagsHTML   = p.tags.map(t => `<span class="badge-${t.tipe}">${t.label}</span>`).join('');
        
        const itemHTML = `
            <div class="item-pesanan"
                data-id="${id}"
                data-status="${p.status}"
                onclick="bukaPesanan(${id}, this)">
                <div class="item-pesanan-atas">
                    <span class="badge-status ${badgeKelas}">${_labelStatus[p.status] || p.status}</span>
                    <span class="item-pesanan-waktu">${p.waktu}</span>
                </div>
                <p class="item-pesanan-kode">#${p.kode}</p>
                <p class="item-pesanan-nama">${p.nama}</p>
                <div class="item-pesanan-tags">${tagsHTML}</div>
            </div>
        `;
        listEl.insertAdjacentHTML('beforeend', itemHTML);
    });
}
// ── AUTO-REFRESH PLACEHOLDER ───────────────────────────────
// Backend nanti: setInterval(() => location.reload(), 60000)
function startAutoRefresh() {}
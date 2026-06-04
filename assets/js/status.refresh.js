function startAutoRefresh() {
    setInterval(function () {
        location.reload();
    }, 60000);

    console.log('[status-refresh] Auto-refresh aktif: setiap 60 detik.');
}

function updateStatus(status) {
    if (typeof idAktif === 'undefined' || !idAktif) return;

    dataPesanan[idAktif].status = status;
    setStatusUI(status);

    const itemEl  = document.querySelector(`.item-pesanan[data-id="${idAktif}"]`);
    if (!itemEl) return;

    const badgeEl = itemEl.querySelector('.badge-status');
    if (!badgeEl) return;

    const labelMap = { baru: 'Baru', diproses: 'Diproses', selesai: 'Selesai' };
    badgeEl.className   = `badge-status badge-status-${status}`;
    badgeEl.textContent = labelMap[status] || status;
    itemEl.dataset.status = status;
}

function setStatusUI(status) {
    document.querySelectorAll('.tombol-status').forEach(btn => {
        btn.classList.toggle('tombol-status-aktif', btn.dataset.status === status);
    });

    const labelMap = { baru: 'Baru', diproses: 'Diproses', selesai: 'Selesai' };
    const teksEl   = document.getElementById('statusAktifTeks');
    if (teksEl) teksEl.textContent = labelMap[status] || '—';
}
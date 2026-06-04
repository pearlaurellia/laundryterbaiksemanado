// ── UPDATE STATUS ──────────────────────────────────────────
function updateStatus(status) {
    if (!idAktif) return;
    
    // Mengubah data di main.js
    dataPesanan[idAktif].status = status;
    setStatusUI(status);

    // Update badge di sidebar
    const itemEl  = document.querySelector(`.item-pesanan[data-id="${idAktif}"]`);
    const badgeEl = itemEl.querySelector('.badge-status');
    
    badgeEl.className = `badge-status badge-status-${status}`;
    const labelMap = { baru:'Baru', diproses:'Diproses', selesai:'Selesai' };
    badgeEl.textContent = labelMap[status];
    itemEl.dataset.status = status;
}

function setStatusUI(status) {
    document.querySelectorAll('.tombol-status').forEach(btn => {
        btn.classList.toggle('tombol-status-aktif', btn.dataset.status === status);
    });
    
    const labelMap = { baru:'Baru', diproses:'Diproses', selesai:'Selesai' };
    document.getElementById('statusAktifTeks').textContent = labelMap[status] || '—';
}
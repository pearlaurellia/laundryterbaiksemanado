function updateStatus(status) {
    dataPesanan[indexAktif].status = status;
    setStatusUI(status);
    
    const items = document.querySelectorAll('.item-pesanan');
    const badgeEl = items[indexAktif].querySelector('.badge-biru');
    const labelMap = { baru: 'Baru', diproses: 'Diproses', selesai: 'Selesai' };
    const warnMap  = {
        baru:     '#3b82f6',
        diproses: '#f59e0b',
        selesai:  '#52c49c'
    };
    
    badgeEl.textContent = labelMap[status];
    badgeEl.style.backgroundColor = warnMap[status];
    badgeEl.style.color = status === 'selesai' ? '#1a4d3a' : 'white';
    
    items[indexAktif].dataset.status = status;
}

function setStatusUI(status) {
    document.querySelectorAll('.tombol-status').forEach(btn => {
        btn.classList.toggle('tombol-status-aktif', btn.dataset.status === status);
    });
    const labelMap = { baru: 'Baru', diproses: 'Diproses', selesai: 'Selesai' };
    document.getElementById('statusAktifTeks').textContent = labelMap[status];
}
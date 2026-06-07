'use strict';

document.addEventListener('DOMContentLoaded', () => {
    setInterval(() => {
        location.reload();
    }, 60000);
});


function konfirmasiBatal(idPesanan, kodePesanan, namaLayanan) {
    const teksBatal = document.getElementById('popupBatalTeks');
    const inputId = document.getElementById('inputIdPesananBatal');
    const overlay = document.getElementById('overlayPopup');
    const popup = document.getElementById('popupBatal');

    if (teksBatal) {
        teksBatal.innerHTML = `Pesanan <strong style="color: #ef4444;">#${kodePesanan}</strong> (${namaLayanan}) akan dibatalkan secara permanen dan tidak dapat dikembalikan.`;
    }
    
    if (inputId) {
        inputId.value = idPesanan;
    }
    
    if (overlay) overlay.style.display = 'block';
    if (popup) popup.style.display = 'block';
}


function tutupPopupBatal() {
    const overlay = document.getElementById('overlayPopup');
    const popup = document.getElementById('popupBatal');
    
    if (overlay) overlay.style.display = 'none';
    if (popup) popup.style.display = 'none';
}

function tutupNotifBatal() {
    const overlay = document.getElementById('overlayNotifBatal');
    const popup = document.getElementById('popupNotifBatal');
    
    if (overlay) overlay.style.display = 'none';
    if (popup) popup.style.display = 'none';
}
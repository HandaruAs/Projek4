// Tutup modal saat klik tombol "Mengerti"
document.addEventListener('DOMContentLoaded', function () {
    const closeBtn = document.querySelector('.btn-close-modal');
    if (closeBtn) {
        closeBtn.addEventListener('click', function () {
            const modal = document.getElementById('warningModal');
            if (modal) modal.remove();
        });
    }
});
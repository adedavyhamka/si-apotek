// SI-APOTEK — script global
document.addEventListener('DOMContentLoaded', function () {
    // Toggle sidebar (mobile)
    var toggle = document.getElementById('sidebarToggle');
    var sidebar = document.getElementById('sidebar');
    var overlay = document.getElementById('sidebarOverlay');
    if (toggle && sidebar && overlay) {
        var closeSidebar = function () {
            sidebar.classList.remove('open');
            overlay.classList.remove('show');
        };
        toggle.addEventListener('click', function () {
            sidebar.classList.toggle('open');
            overlay.classList.toggle('show');
        });
        overlay.addEventListener('click', closeSidebar);
    }

    // Konfirmasi hapus (data-confirm)
    document.querySelectorAll('[data-confirm]').forEach(function (el) {
        el.addEventListener('click', function (ev) {
            var msg = el.getAttribute('data-confirm') || 'Yakin ingin menghapus data ini?';
            if (!window.confirm(msg)) {
                ev.preventDefault();
            }
        });
    });

    // Auto-hide alert setelah 4 detik
    document.querySelectorAll('.alert-dismissible').forEach(function (el) {
        setTimeout(function () {
            var b = bootstrap && bootstrap.Alert ? bootstrap.Alert.getOrCreateInstance(el) : null;
            if (b) { b.close(); }
        }, 4000);
    });
});

// Persiste la pestaña activa en location.hash para que sobreviva refrescos.
// Aplica a TODOS los Bootstrap 5 tabs del sistema sin tocar cada vista.
(function () {
    function activarPorHash() {
        var hash = window.location.hash;
        if (!hash || hash.length < 2) return;
        try {
            var trigger = document.querySelector(
                '[data-bs-toggle="tab"][data-bs-target="' + hash + '"]'
            );
            if (trigger && typeof bootstrap !== 'undefined' && bootstrap.Tab) {
                bootstrap.Tab.getOrCreateInstance(trigger).show();
            }
        } catch (e) { /* hash invalido como selector */ }
    }

    document.addEventListener('DOMContentLoaded', function () {
        activarPorHash();

        document.querySelectorAll('[data-bs-toggle="tab"]').forEach(function (btn) {
            btn.addEventListener('shown.bs.tab', function (ev) {
                var target = ev.target.getAttribute('data-bs-target');
                if (target && target.indexOf('#') === 0) {
                    history.replaceState(null, '', window.location.pathname + window.location.search + target);
                }
            });
        });
    });

    // Si el usuario manualmente cambia el hash (back/forward), reactivar
    window.addEventListener('hashchange', activarPorHash);
})();

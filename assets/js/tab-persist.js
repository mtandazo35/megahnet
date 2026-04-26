// Persiste la pestaña activa en location.hash y la activa SIN parpadeo
// (intercambia .active/.show directamente en el DOM antes del primer paint).
(function () {
    function activarPorHash() {
        var hash = window.location.hash;
        if (!hash || hash.length < 2) {
            document.documentElement.classList.remove('tab-pending');
            return;
        }
        var targetId = hash.substring(1);
        var btn  = document.querySelector('[data-bs-toggle="tab"][data-bs-target="#' + targetId + '"]');
        var pane = document.getElementById(targetId);
        if (!btn || !pane) {
            document.documentElement.classList.remove('tab-pending');
            return;
        }

        var nav     = btn.closest('.nav-tabs') || btn.closest('.nav') || document.body;
        var content = pane.parentElement;

        // Quitar active/show del tab default
        nav.querySelectorAll('.nav-link.active').forEach(function (b) {
            b.classList.remove('active');
            b.setAttribute('aria-selected', 'false');
        });
        content.querySelectorAll('.tab-pane.active, .tab-pane.show').forEach(function (p) {
            p.classList.remove('active', 'show');
        });

        // Activar el correcto
        btn.classList.add('active');
        btn.setAttribute('aria-selected', 'true');
        pane.classList.add('active', 'show');

        document.documentElement.classList.remove('tab-pending');
    }

    function attachListeners() {
        document.querySelectorAll('[data-bs-toggle="tab"]').forEach(function (btn) {
            btn.addEventListener('shown.bs.tab', function (ev) {
                var target = ev.target.getAttribute('data-bs-target');
                if (target && target.indexOf('#') === 0) {
                    history.replaceState(null, '', window.location.pathname + window.location.search + target);
                }
            });
        });
    }

    // Ejecutar lo antes posible. El script va al final del footer asi que el DOM
    // ya esta parseado, pero verificamos por seguridad.
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', function () {
            activarPorHash();
            attachListeners();
        });
    } else {
        activarPorHash();
        attachListeners();
    }

    window.addEventListener('hashchange', activarPorHash);

    // Fallback: si por alguna razon no se quito tab-pending (timeout 1s), lo quitamos
    setTimeout(function () {
        document.documentElement.classList.remove('tab-pending');
    }, 1000);
})();

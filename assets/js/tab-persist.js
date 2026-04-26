// Persiste la pestaña activa en location.hash y la activa SIN parpadeo.
// El swap de clases ocurre apenas se ejecuta el script (antes del primer
// paint visible), pero el evento shown.bs.tab se dispara en DOMContentLoaded
// para que los handlers de modulos (creditos.js, etc.) ya esten registrados.
(function () {
    var pendingBtn = null; // boton activado por hash, para disparar evento despues

    function swapClasses() {
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

        nav.querySelectorAll('.nav-link.active').forEach(function (b) {
            b.classList.remove('active');
            b.setAttribute('aria-selected', 'false');
        });
        content.querySelectorAll('.tab-pane.active, .tab-pane.show').forEach(function (p) {
            p.classList.remove('active', 'show');
        });

        btn.classList.add('active');
        btn.setAttribute('aria-selected', 'true');
        pane.classList.add('active', 'show');

        document.documentElement.classList.remove('tab-pending');
        pendingBtn = btn;
    }

    function dispatchShown() {
        if (!pendingBtn) return;
        try {
            // jQuery primero (forma usada en codigo legacy del proyecto)
            if (window.jQuery) {
                window.jQuery(pendingBtn).trigger('shown.bs.tab');
            }
            // Native CustomEvent por si algun listener usa addEventListener
            pendingBtn.dispatchEvent(new Event('shown.bs.tab', { bubbles: true }));
        } catch (e) { /* noop */ }
        pendingBtn = null;
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

    // 1) Swap inmediato (sin esperar) para evitar parpadeo
    swapClasses();

    // 2) Disparar el evento + listeners cuando el DOM y todos los scripts
    //    de modulo hayan corrido
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', function () {
            dispatchShown();
            attachListeners();
        });
    } else {
        // DOM ya parseado: esperamos un tick para que scripts subsecuentes
        // (cargados despues de este en el footer) registren sus handlers
        setTimeout(function () {
            dispatchShown();
            attachListeners();
        }, 0);
    }

    // 3) Soporte para back/forward del navegador
    window.addEventListener('hashchange', function () {
        swapClasses();
        dispatchShown();
    });

    // Fallback de seguridad: si por alguna razon no se quito tab-pending
    setTimeout(function () {
        document.documentElement.classList.remove('tab-pending');
    }, 1000);
})();

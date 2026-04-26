/* MHN PJAX simple: navega sin recargar el documento. Reemplaza solo el
 * contenido principal (.page-content) y actualiza title + history.
 * Sidebar y topbar permanecen estables (no se repinta) => sin flash.
 *
 * Diseno defensivo: si algo falla, hace fallback a navegacion normal.
 *
 * Cobertura:
 *  - Click en links del mismo origen (excepto target=_blank, mailto, etc.)
 *  - Boton atras del browser (popstate)
 *  - Soporta DataTables (destroy antes del swap)
 *  - Soporta scripts inline del modulo nuevo
 *  - Patches DOMContentLoaded para que cb de modulos se ejecuten tras swap
 *
 * IMPORTANTE: los modales BS5 viven dentro del .page-content en este sistema,
 * asi que el reemplazo de innerHTML los trae automaticamente. NO se clonan
 * modales por separado (eso era el bug que rompia los listeners).
 * BS5 con data-bs-toggle/dismiss funciona via event delegation, asi que los
 * botones siguen funcionando aunque sean nodos nuevos.
 */
(function () {
    'use strict';
    if (!window.fetch || !window.history.pushState || !window.DOMParser) return;

    var ORIGIN = location.origin;
    var pjaxRunning = false;

    function isPjaxLink(a) {
        if (!a) return false;
        var href = a.getAttribute('href') || '';
        if (!href || href === '#' || href.charAt(0) === '#') return false;
        if (href.indexOf('javascript:') === 0) return false;
        if (href.indexOf('mailto:') === 0 || href.indexOf('tel:') === 0) return false;
        if (a.target && a.target !== '_self') return false;
        if (a.hasAttribute('download')) return false;
        if (a.hasAttribute('data-bs-toggle')) return false;
        if (a.hasAttribute('data-no-pjax')) return false;
        try {
            var u = new URL(href, location.href);
            if (u.origin !== ORIGIN) return false;
            if (/\.(pdf|xlsx|xls|csv|jpg|jpeg|png|gif|zip|p12|pfx)(\?|$)/i.test(u.pathname)) return false;
            if (/\/(listar|listarInactivos|registrar|registrarExcel|eliminar|restaurar|editar|buscar|validar|cerrar|anular|enviar)/i.test(u.pathname)) return false;
        } catch (e) { return false; }
        return true;
    }

    // Patch document.addEventListener: durante PJAX, los DOMContentLoaded
    // de scripts cargados via PJAX disparan inmediatamente (en microtask).
    var origDocAddListener = document.addEventListener.bind(document);
    document.addEventListener = function (type, cb, opts) {
        if (type === 'DOMContentLoaded' && pjaxRunning && typeof cb === 'function') {
            Promise.resolve().then(function () {
                try { cb(new Event('DOMContentLoaded')); }
                catch (e) { console.error('[pjax] DCL cb error:', e); }
            });
            return;
        }
        return origDocAddListener(type, cb, opts);
    };

    function destroyDataTables(scope) {
        if (!window.jQuery || !$.fn || !$.fn.DataTable) return;
        try {
            scope.querySelectorAll('table.dataTable').forEach(function (t) {
                if ($.fn.DataTable.isDataTable(t)) {
                    try { $(t).DataTable().destroy(); } catch (e) {}
                }
            });
        } catch (e) {}
    }

    function closeOpenModals() {
        // Cerrar y disposeear todos los modales abiertos.
        // Importante: usar getInstance + dispose. Si se hace getOrCreateInstance,
        // se crea instancia para nodos viejos y rompe.
        document.querySelectorAll('.modal.show').forEach(function (m) {
            try {
                var inst = (typeof bootstrap !== 'undefined' && bootstrap.Modal)
                    ? bootstrap.Modal.getInstance(m) : null;
                if (inst) inst.hide();
            } catch (e) {}
        });
        document.querySelectorAll('.modal-backdrop').forEach(function (b) { b.remove(); });
        document.body.classList.remove('modal-open');
        document.body.style.overflow = '';
        document.body.style.paddingRight = '';
    }

    // Re-ejecutar los <script> dentro de un container (innerHTML no los ejecuta)
    function executeInlineScripts(container) {
        var scripts = Array.from(container.querySelectorAll('script'));
        var promises = [];
        scripts.forEach(function (oldScript) {
            var newScript = document.createElement('script');
            Array.from(oldScript.attributes).forEach(function (a) { newScript.setAttribute(a.name, a.value); });
            if (oldScript.src) {
                promises.push(new Promise(function (res) {
                    newScript.onload = newScript.onerror = function () { res(); };
                }));
                newScript.async = false;
            } else {
                newScript.textContent = oldScript.textContent;
            }
            oldScript.parentNode.replaceChild(newScript, oldScript);
        });
        return Promise.all(promises);
    }

    // Reemplazar scripts de modulo (modulos/X.js, validacion.js, busqueda.js)
    // que se cargan al final del body.
    function reloadModuleScripts(newDoc) {
        var keys = ['/assets/js/modulos/', '/assets/js/validacion.js', '/assets/js/busqueda.js'];
        document.querySelectorAll('body > script[src]').forEach(function (s) {
            var src = s.getAttribute('src') || '';
            if (keys.some(function (k) { return src.indexOf(k) !== -1; })) s.remove();
        });
        // Inline nombreKey (cargado al final del body por el footer)
        document.querySelectorAll('body > script:not([src])').forEach(function (s) {
            var t = s.textContent || '';
            if (t.indexOf('nombreKey') !== -1 && t.length < 200) s.remove();
        });

        var newScripts = Array.from(newDoc.querySelectorAll('body > script'));
        var promises = [];
        newScripts.forEach(function (s) {
            var src = s.getAttribute('src') || '';
            if (src && keys.some(function (k) { return src.indexOf(k) !== -1; })) {
                var ns = document.createElement('script');
                Array.from(s.attributes).forEach(function (a) { ns.setAttribute(a.name, a.value); });
                document.body.appendChild(ns);
                promises.push(new Promise(function (res) {
                    ns.onload = ns.onerror = function () { res(); };
                }));
            } else if (!src) {
                var t = s.textContent || '';
                if (t.indexOf('nombreKey') !== -1 && t.length < 200) {
                    var ns2 = document.createElement('script');
                    ns2.textContent = t;
                    document.body.appendChild(ns2);
                }
            }
        });
        return Promise.all(promises);
    }

    function updateSidebarActive(url) {
        try {
            var u = new URL(url, location.href);
            var path = u.pathname.replace(/^\/+/, '').replace(/\/+$/, '');
            var rel = path || 'admin';
            document.querySelectorAll('#menu li').forEach(function (li) {
                li.classList.remove('mm-active');
                if (li.classList.contains('has-sub')) {
                    var sub = li.querySelector(':scope > ul');
                    if (sub) sub.classList.remove('mm-show');
                }
            });
            document.querySelectorAll('#menu li[data-url]').forEach(function (li) {
                var d = li.getAttribute('data-url');
                if (rel === d || rel.indexOf(d + '/') === 0) {
                    li.classList.add('mm-active');
                    var p = li.closest('ul');
                    while (p && p.id !== 'menu') {
                        var pl = p.closest('li.has-sub');
                        if (pl) { pl.classList.add('mm-active'); p.classList.add('mm-show'); }
                        p = pl ? pl.closest('ul') : null;
                    }
                }
            });
        } catch (e) {}
    }

    function navigate(url, push) {
        if (pjaxRunning) return;
        pjaxRunning = true;
        document.body.classList.add('mhn-navigating');

        return fetch(url, {
            credentials: 'same-origin',
            headers: { 'X-Requested-With': 'pjax', 'Accept': 'text/html' }
        })
        .then(function (res) {
            if (res.redirected) { location.href = res.url; throw 'redirected'; }
            if (!res.ok) throw new Error('HTTP ' + res.status);
            var ct = res.headers.get('content-type') || '';
            if (ct.indexOf('text/html') === -1) { location.href = url; throw 'non-html'; }
            return res.text();
        })
        .then(function (html) {
            var newDoc = new DOMParser().parseFromString(html, 'text/html');
            var newPC = newDoc.querySelector('.page-content');
            if (!newPC) { location.href = url; throw 'no-page-content'; }

            var oldPC = document.querySelector('.page-content');
            if (oldPC) destroyDataTables(oldPC);
            closeOpenModals();

            if (newDoc.title) document.title = newDoc.title;

            // Reemplazar TODO el contenido del page-content (incluye modales y scripts inline)
            if (oldPC) oldPC.innerHTML = newPC.innerHTML;

            if (push !== false) history.pushState({ pjax: true }, '', url);
            updateSidebarActive(url);

            // Re-ejecutar scripts inline (modales handlers, listeners) + scripts de modulo
            return executeInlineScripts(oldPC).then(function () {
                return reloadModuleScripts(newDoc);
            });
        })
        .then(function () {
            pjaxRunning = false;
            document.body.classList.remove('mhn-navigating');
            window.scrollTo(0, 0);
            document.dispatchEvent(new CustomEvent('mhn:pjax:loaded', { detail: { url: url } }));
        })
        .catch(function (err) {
            pjaxRunning = false;
            document.body.classList.remove('mhn-navigating');
            if (err === 'redirected' || err === 'non-html' || err === 'no-page-content') return;
            console.warn('[pjax] fallback a navegacion normal:', err);
            location.href = url;
        });
    }

    document.addEventListener('click', function (e) {
        if (e.defaultPrevented) return;
        if (e.ctrlKey || e.metaKey || e.shiftKey || e.altKey) return;
        if (e.button !== 0) return;
        var a = e.target.closest('a[href]');
        if (!isPjaxLink(a)) return;
        e.preventDefault();
        var u = new URL(a.getAttribute('href'), location.href);
        navigate(u.href, true);
    }, false);

    window.addEventListener('popstate', function () {
        navigate(location.href, false);
    });

    if (history.state === null) {
        history.replaceState({ pjax: true, initial: true }, '');
    }
})();

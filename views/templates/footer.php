</div>
</div>
<!--end page wrapper -->
<!--start overlay-->
<div class="overlay toggle-icon"></div>
<!--end overlay-->
<!--Start Back To Top Button-->
<a href="javaScript:;" class="back-to-top"><i class='bx bxs-up-arrow-alt'></i></a>
<!--End Back To Top Button-->
<footer class="page-footer">
    <p class="mb-0">Copyright © <?php echo date('Y'); ?>. All right reserved.</p>
</footer>
</div>
<!--end wrapper-->

<!-- Bootstrap JS -->
<!--plugins-->
<script src="<?php echo BASE_URL; ?>assets/js/bootstrap.bundle.min.js"></script>
<script src="<?php echo BASE_URL; ?>assets/js/jquery.min.js"></script>
<script src="<?php echo BASE_URL; ?>assets/plugins/simplebar/js/simplebar.min.js"></script>
<script>
// Sidebar scroll persist.
// SimpleBar wrappea asíncronamente con requestAnimationFrame — incluso
// después de DOMContentLoaded el .simplebar-content-wrapper puede no
// estar todavía. Por eso poleamos con rAF hasta que aparezca (máx 30
// frames ~500ms). Apenas existe, restauramos scrollTop y enganchamos
// el listener de save.
(function(){
    // Forzar init de SimpleBar AHORA, sin esperar a DOMContentLoaded.
    // Sin esto, datatables/ckeditor bloquean el thread y SimpleBar no
    // wrappea hasta despues de varios cientos de ms.
    try {
        var w = document.querySelector('.sidebar-wrapper');
        if (w && typeof SimpleBar !== 'undefined' && !w.querySelector('.simplebar-content-wrapper')) {
            new SimpleBar(w);
        }
    } catch(e){}
    var KEY = 'mhn_sidebar_scroll_v2';
    var saveTimer = null;
    var attached = false;

    function attach(scroller) {
        if (attached) return;
        attached = true;

        // 1) Restaurar scroll guardado
        try {
            var t = sessionStorage.getItem(KEY);
            if (t !== null) scroller.scrollTop = parseInt(t, 10) || 0;
        } catch(e){}

        // 2) Listener throttled — guarda mientras el usuario scrollea
        function saveNow(){
            try { sessionStorage.setItem(KEY, String(scroller.scrollTop)); } catch(e){}
        }
        function saveThrottled(){
            if (saveTimer) return;
            saveTimer = setTimeout(function(){ saveTimer = null; saveNow(); }, 120);
        }
        scroller.addEventListener('scroll', saveThrottled, { passive: true });
        window.addEventListener('pagehide', saveNow);
    }

    function tryAttach() {
        var scroller = document.querySelector('.sidebar-wrapper .simplebar-content-wrapper');
        if (scroller) { attach(scroller); return true; }
        return false;
    }

    // Intento inmediato (por si SimpleBar ya wrappeo)
    if (!tryAttach()) {
        var wrapper = document.querySelector('.sidebar-wrapper');
        if (wrapper && typeof MutationObserver !== 'undefined') {
            // MutationObserver: dispara en microtask en cuanto SimpleBar
            // inserta .simplebar-content-wrapper. Mucho mas rapido que rAF.
            var obs = new MutationObserver(function(){
                if (tryAttach()) obs.disconnect();
            });
            obs.observe(wrapper, { childList: true, subtree: true });
            // Fallback rAF por si MO no dispara (edge cases)
            var deadline = performance.now() + 1500;
            (function tick(){
                if (attached) return;
                if (tryAttach()) { obs.disconnect(); return; }
                if (performance.now() < deadline) requestAnimationFrame(tick);
                else obs.disconnect();
            })();
        }
    }
})();
</script>
<script src="<?php echo BASE_URL; ?>assets/plugins/metismenu/js/metisMenu.min.js"></script>
<script src="<?php echo BASE_URL; ?>assets/plugins/perfect-scrollbar/js/perfect-scrollbar.js"></script>
<script src="<?php echo BASE_URL; ?>assets/plugins/chartjs/js/Chart.min.js"></script>
<script src="<?php echo BASE_URL; ?>assets/plugins/chartjs/js/Chart.extension.js"></script>

<!--app JS-->
<script src="<?php echo BASE_URL; ?>assets/js/app.js"></script>
<script src="<?php echo BASE_URL; ?>assets/js/all.min.js"></script>
<script src="<?php echo BASE_URL; ?>assets/DataTables/datatables.min.js"></script>
<!-- DataTables: ColReorder + RowReorder para drag & drop + sort visual + state save -->
<link href="https://cdn.datatables.net/colreorder/1.7.0/css/colReorder.dataTables.min.css" rel="stylesheet">
<link href="https://cdn.datatables.net/rowreorder/1.4.1/css/rowReorder.dataTables.min.css" rel="stylesheet">
<script src="https://cdn.datatables.net/colreorder/1.7.0/js/dataTables.colReorder.min.js"></script>
<script src="https://cdn.datatables.net/rowreorder/1.4.1/js/dataTables.rowReorder.min.js"></script>
<script>
(function(){
    if (typeof $ === 'undefined' || !$.fn.dataTable) return;

    $.extend(true, $.fn.dataTable.defaults, {
        ordering: true,
        colReorder: true,
        stateSave: true,
        stateDuration: -1, // -1 = nunca expira (persiste en localStorage)

        // Guarda en localStorage con clave estable por id de tabla,
        // asi sobrevive cambios de pagina y cierres de sesion.
        stateSaveCallback: function (settings, data) {
            try {
                var key = 'DT_state_v4_' + (settings.sTableId || 'default');
                localStorage.setItem(key, JSON.stringify(data));
                if (window.__mhnDtDebug) console.log('[DT save]', key, data);
            } catch (e) { console.error('[DT save error]', e); }
        },
        stateLoadCallback: function (settings) {
            try {
                var key = 'DT_state_v4_' + (settings.sTableId || 'default');
                var raw = localStorage.getItem(key);
                if (window.__mhnDtDebug) console.log('[DT load]', key, raw ? 'FOUND' : 'EMPTY');
                return raw ? JSON.parse(raw) : null;
            } catch (e) { console.error('[DT load error]', e); return null; }
        },


        language: {
            searchPlaceholder: 'Buscar...',
            search: '',
            lengthMenu: 'Mostrar _MENU_',
            info: '_START_-_END_ de _TOTAL_',
            infoEmpty: '0 registros',
            paginate: { previous: '\u2039', next: '\u203a' },
            emptyTable: 'Sin datos',
            zeroRecords: 'Sin coincidencias'
        }
    });
})();
</script>

<script src="<?php echo BASE_URL; ?>assets/js/botones-perzonalizados.js?v=<?php echo function_exists('asset_v') ? asset_v('assets/js/botones-perzonalizados.js') : ''; ?>"></script>
<script src="<?php echo BASE_URL; ?>assets/js/sweetalert2.all.min.js"></script>
<script src="<?php echo BASE_URL; ?>assets/js/ckeditor.js"></script>
<script src="<?php echo BASE_URL; ?>assets/js/funciones.js?v=<?php echo function_exists('asset_v') ? asset_v('assets/js/funciones.js') : ''; ?>"></script>
<?php /* PJAX deshabilitado definitivamente: los modulos legacy declaran let/const
       a nivel top en sus archivos .js. Al re-cargarlos via PJAX tiran SyntaxError
       (Identifier 'X' has already been declared) y se rompen los listeners.
       Para reactivar habria que envolver cada modulo en un IIFE.
<script src="<?php echo BASE_URL; ?>assets/js/mhn-pjax.js?v=<?php echo function_exists('asset_v') ? asset_v('assets/js/mhn-pjax.js') : ''; ?>"></script>
*/ ?>
<script src="<?php echo BASE_URL; ?>assets/js/tab-persist.js"></script>
<script src="<?php echo BASE_URL; ?>assets/js/jquery-ui.min.js"></script>
<!-- Column resize: handles para arrastrar bordes de columna + persistencia en localStorage.
     Funciona con TODAS las DataTables (escucha 'init.dt' global). Anchos guardados por
     nombre de columna, así sobreviven al colReorder. -->
<script src="<?php echo BASE_URL; ?>assets/js/datatables-colresize.js?v=<?php echo function_exists('asset_v') ? asset_v('assets/js/datatables-colresize.js') : ''; ?>"></script>
<script src="<?php echo BASE_URL; ?>assets/js/datatables-savestate.js?v=<?php echo function_exists('asset_v') ? asset_v('assets/js/datatables-savestate.js') : ''; ?>"></script>
<script src="<?php echo BASE_URL; ?>assets/plugins/fullcalendar/js/main.min.js"></script>
<script src="<?php echo BASE_URL; ?>assets/js/es.js"></script>



<script>
    const base_url = '<?php echo BASE_URL; ?>';
</script>
<?php if (!empty($data['busqueda'])) { ?>
    <script>
        const nombreKey = '<?php echo $data['carrito']; ?>';
    </script>
    <script src="<?php echo BASE_URL . 'assets/js/' . $data['busqueda']; ?>?v=<?php echo function_exists('asset_v') ? asset_v('assets/js/' . $data['busqueda']) : ''; ?>"></script>
<?php } ?>
<?php if (!empty($data['script'])) { ?>
    <script src="<?php echo BASE_URL . 'assets/js/modulos/' . $data['script']; ?>?v=<?php echo function_exists('asset_v') ? asset_v('assets/js/modulos/' . $data['script']) : ''; ?>"></script>
<?php } ?>
<?php if (!empty($data['validacion'])) { ?>
    <script src="<?php echo BASE_URL . 'assets/js/' . $data['validacion']; ?>?v=<?php echo function_exists('asset_v') ? asset_v('assets/js/' . $data['validacion']) : ''; ?>"></script>
<?php } ?>
</body>

</html>
<script>
// PJAX piloto: intercepta clicks en a[data-pjax] y reemplaza solo
// .page-content sin recargar header/sidebar. Re-ejecuta scripts y
// actualiza titulo + URL via pushState.
(function(){
    if (typeof window.fetch === "undefined") return;
    var TARGET = ".page-content";

    function execScripts(container){
        // Re-ejecuta <script> inline y carga <script src> del nuevo HTML
        var scripts = container.querySelectorAll("script");
        scripts.forEach(function(old){
            var s = document.createElement("script");
            for (var i=0; i<old.attributes.length; i++) {
                s.setAttribute(old.attributes[i].name, old.attributes[i].value);
            }
            s.text = old.textContent;
            old.parentNode.replaceChild(s, old);
        });
    }

    function load(url, push){
        var current = document.querySelector(TARGET);
        if (!current) { window.location.href = url; return; }
        document.body.style.cursor = "progress";
        fetch(url, { headers: { "X-PJAX": "1" }, credentials: "same-origin" })
            .then(function(r){ if (!r.ok) throw new Error("HTTP "+r.status); return r.text(); })
            .then(function(html){
                var doc = new DOMParser().parseFromString(html, "text/html");
                var fresh = doc.querySelector(TARGET);
                if (!fresh) { window.location.href = url; return; }
                // Reemplaza contenido
                current.innerHTML = fresh.innerHTML;
                // Actualiza titulo
                if (doc.title) document.title = doc.title;
                // Re-ejecuta scripts del contenido nuevo
                execScripts(current);
                // pushState
                if (push) history.pushState({pjax:true}, "", url);
                // Marca activo en sidebar
                document.querySelectorAll("#menu li.mm-active").forEach(function(li){ li.classList.remove("mm-active"); });
                document.querySelectorAll(".mhn-clicked").forEach(function(el){ el.classList.remove("mhn-clicked"); });
                document.documentElement.classList.remove("mhn-navigating");
                document.querySelectorAll("#menu a").forEach(function(a){
                    if (a.href === url) a.parentElement.classList.add("mm-active");
                });
                window.scrollTo(0, 0);
            })
            .catch(function(e){ console.warn("PJAX fallo, recargando:", e); window.location.href = url; })
            .finally(function(){ document.body.style.cursor = ""; });
    }

    document.addEventListener("click", function(e){
        var a = e.target.closest && e.target.closest("a[data-pjax]");
        if (!a) return;
        if (e.metaKey || e.ctrlKey || e.shiftKey || e.button !== 0) return;
        e.preventDefault(); a.blur();
        load(a.href, true);
    });

    window.addEventListener("popstate", function(e){
        if (e.state && e.state.pjax) load(location.href, false);
    });
})();
</script>

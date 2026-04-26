/* ============================================================================
 * Column Resize para todas las DataTables del proyecto.
 *
 * Qué hace:
 *   - Agrega un "handle" invisible (cursor col-resize) en el borde derecho
 *     de cada <th>. Al arrastrar, redimensiona la columna en vivo.
 *   - Guarda los anchos en localStorage por nombre de columna (estable
 *     incluso si el usuario reordena con colReorder).
 *   - Restaura los anchos guardados cuando la tabla termina de inicializar.
 *
 * Funciona con cualquier DataTable que se inicialice DESPUÉS de cargar este
 * script (escucha el evento global 'init.dt').
 *
 * Storage key: DT_widths_v1_<tableId>  (no choca con DT_state_v4_* del state save)
 * ============================================================================ */
(function () {
    if (typeof $ === 'undefined' || !$.fn.dataTable) return;

    var STORAGE_PREFIX = 'DT_widths_v1_';
    var MIN_WIDTH = 60;   // px — ancho mínimo de columna
    var HANDLE_W  = 6;    // px — ancho del handle de drag

    // Estilos del handle (inyectados una sola vez)
    if (!document.getElementById('mhn-colresize-style')) {
        var style = document.createElement('style');
        style.id = 'mhn-colresize-style';
        style.textContent = [
            '.dataTable thead th { position: relative; }',
            '.mhn-col-resize {',
            '  position: absolute;',
            '  top: 0; right: 0; bottom: 0;',
            '  width: ' + HANDLE_W + 'px;',
            '  cursor: col-resize;',
            '  user-select: none;',
            '  z-index: 5;',
            '  background: transparent;',
            '  transition: background .15s ease;',
            '}',
            '.mhn-col-resize:hover, .mhn-col-resize.mhn-resizing {',
            '  background: rgba(37,99,235,.35);',
            '}',
            '.mhn-col-resizing, .mhn-col-resizing * {',
            '  cursor: col-resize !important;',
            '  user-select: none !important;',
            '}'
        ].join('\n');
        document.head.appendChild(style);
    }

    function colKey(api, columnIdx) {
        // Usar nombre de columna estable: prefer data prop, fallback al texto del header
        var col = api.column(columnIdx);
        var dataSrc = '';
        try { dataSrc = col.dataSrc(); } catch (e) {}
        if (dataSrc) return String(dataSrc);
        var th = col.header();
        return th ? (th.textContent || '').trim().replace(/\s+/g, '_') : ('col_' + columnIdx);
    }

    function loadWidths(tableId) {
        try {
            var raw = localStorage.getItem(STORAGE_PREFIX + tableId);
            return raw ? JSON.parse(raw) : {};
        } catch (e) { return {}; }
    }

    function saveWidths(tableId, widths) {
        try {
            localStorage.setItem(STORAGE_PREFIX + tableId, JSON.stringify(widths));
        } catch (e) { /* localStorage lleno o bloqueado */ }
    }

    function applyWidths(api, widths) {
        api.columns().every(function (idx) {
            var key = colKey(api, idx);
            if (widths[key]) {
                var th = $(this.header());
                var px = parseInt(widths[key], 10);
                if (px >= MIN_WIDTH) {
                    th.css({ width: px + 'px', minWidth: px + 'px', maxWidth: px + 'px' });
                }
            }
        });
        // Decirle a DataTables que recalcule layout interno
        try { api.columns.adjust(); } catch (e) {}
    }

    function attachResize(api) {
        var tableId = api.settings()[0].sTableId;
        if (!tableId) return;
        if (api.settings()[0]._mhnColResizeAttached) return;
        api.settings()[0]._mhnColResizeAttached = true;

        // 1) Restaurar anchos guardados
        var widths = loadWidths(tableId);
        applyWidths(api, widths);

        // 2) Agregar handles a cada th
        var $headers = $(api.table().header()).find('th');
        $headers.each(function (visualIdx) {
            var $th = $(this);
            // Evitar duplicar handle si la tabla se re-renderiza
            if ($th.find('.mhn-col-resize').length) return;
            var $handle = $('<div class="mhn-col-resize" title="Arrastra para redimensionar"></div>');
            $th.append($handle);

            $handle.on('mousedown.mhnResize', function (e) {
                if (e.button !== 0) return;
                e.preventDefault();
                e.stopPropagation();

                var startX = e.pageX;
                var startW = $th.outerWidth();
                $handle.addClass('mhn-resizing');
                $('body').addClass('mhn-col-resizing');

                $(document).on('mousemove.mhnResize', function (ev) {
                    var newW = Math.max(MIN_WIDTH, startW + (ev.pageX - startX));
                    $th.css({ width: newW + 'px', minWidth: newW + 'px', maxWidth: newW + 'px' });
                });

                $(document).on('mouseup.mhnResize', function () {
                    $(document).off('.mhnResize');
                    $handle.removeClass('mhn-resizing');
                    $('body').removeClass('mhn-col-resizing');

                    // Guardar el ancho final por columna (key estable)
                    var finalWidths = loadWidths(tableId);
                    var col = api.column(visualIdx);
                    if (col) {
                        var key = colKey(api, visualIdx);
                        finalWidths[key] = $th.outerWidth();
                        saveWidths(tableId, finalWidths);
                    }
                    try { api.columns.adjust(); } catch (e) {}
                });
            });

            // Click en el handle no debe disparar el sort de la columna
            $handle.on('click.mhnResize', function (e) {
                e.stopPropagation();
            });
        });
    }

    // Engancharse a CADA DataTable que se inicialice
    $(document).on('init.dt', function (e, settings) {
        try {
            var api = new $.fn.dataTable.Api(settings);
            attachResize(api);
        } catch (err) { /* tabla no compatible, ignorar */ }
    });
})();

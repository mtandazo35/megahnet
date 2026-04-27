/* ============================================================================
 * Boton "Guardar diseno de tabla" para todas las DataTables.
 *
 * Que hace:
 *   - Escucha el evento global init.dt (se dispara cuando cualquier DataTable
 *     termina de inicializar).
 *   - Inserta un boton "Guardar diseno" en la barra superior de la tabla
 *     (junto a Mostrar/Excel/PDF/etc).
 *   - Al click: fuerza state.save() + saveWidths + muestra toast verde.
 *
 * Storage:
 *   - Reusa el state save de DataTables (DT_state_v4_<tableId>) y los anchos
 *     de columna (DT_widths_v1_<tableId>).
 *   - No se necesita endpoint de backend; todo en localStorage.
 *
 * Restaurar a defaults:
 *   - Boton "Resetear diseno" tambien anadido (icono escoba) que limpia el
 *     state guardado de esa tabla y recarga.
 * ============================================================================ */
(function () {
    if (typeof $ === 'undefined' || !$.fn.dataTable) return;

    function injectButtons(api) {
        var settings = api.settings()[0];
        var tableId = settings.sTableId;
        if (!tableId) return;
        if (settings._mhnSaveBtnAttached) return;
        settings._mhnSaveBtnAttached = true;

        // Buscar el contenedor de la barra superior (donde van los botones DT)
        // DT crea un wrapper id="<tableId>_wrapper" con un .dt-buttons o .dataTables_length
        var wrap = document.getElementById(tableId + '_wrapper');
        if (!wrap) return;

        // Crear el grupo de botones nuestro
        var group = document.createElement('div');
        group.className = 'btn-group btn-group-sm mhn-dt-savegroup ms-2';
        group.style.cssText = 'vertical-align: middle; margin-bottom: .35rem;';
        group.innerHTML =
            '<button type="button" class="btn btn-outline-success mhn-dt-save" title="Guardar diseno (orden de columnas, sort, paginacion, busqueda, anchos)">' +
            '<i class="bx bx-save"></i> Guardar diseno' +
            '</button>' +
            '<button type="button" class="btn btn-outline-secondary mhn-dt-reset" title="Restaurar diseno por defecto">' +
            '<i class="bx bx-eraser"></i>' +
            '</button>';

        // Insertar despues del primer .dt-buttons o al inicio del wrapper
        var anchor = wrap.querySelector('.dt-buttons') || wrap.querySelector('.dataTables_length');
        if (anchor) {
            anchor.parentNode.insertBefore(group, anchor.nextSibling);
        } else {
            wrap.insertBefore(group, wrap.firstChild);
        }

        // Click: Guardar
        group.querySelector('.mhn-dt-save').addEventListener('click', function () {
            try {
                api.state.save();
                if (typeof Swal !== 'undefined') {
                    Swal.fire({
                        icon: 'success',
                        title: 'Diseno guardado',
                        text: 'Orden, paginacion y anchos guardados.',
                        timer: 1500,
                        showConfirmButton: false
                    });
                } else {
                    alert('Diseno guardado');
                }
            } catch (e) {
                alert('Error al guardar: ' + e.message);
            }
        });

        // Click: Resetear
        group.querySelector('.mhn-dt-reset').addEventListener('click', function () {
            var doIt = function () {
                try {
                    localStorage.removeItem('DT_state_v4_' + tableId);
                    localStorage.removeItem('DT_widths_v1_' + tableId);
                } catch (e) {}
                window.location.reload();
            };
            if (typeof Swal !== 'undefined') {
                Swal.fire({
                    icon: 'question',
                    title: 'Resetear diseno?',
                    text: 'Volvera al orden y tamanos por defecto.',
                    showCancelButton: true,
                    confirmButtonText: 'Si, resetear',
                    cancelButtonText: 'Cancelar'
                }).then(function (r) {
                    if (r.isConfirmed) doIt();
                });
            } else {
                if (confirm('Resetear diseno de tabla?')) doIt();
            }
        });
    }

    // Hook global: cuando cualquier DataTable termina de inicializar
    $(document).on('init.dt', function (e, settings) {
        var api = new $.fn.dataTable.Api(settings);
        // Esperar un tick para que la barra de botones existente este montada
        setTimeout(function () { injectButtons(api); }, 100);
    });
})();

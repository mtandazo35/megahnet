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
            } catch (e) { /* localStorage lleno o bloqueado */ }
        },
        stateLoadCallback: function (settings) {
            try {
                var key = 'DT_state_v4_' + (settings.sTableId || 'default');
                var raw = localStorage.getItem(key);
                return raw ? JSON.parse(raw) : null;
            } catch (e) { return null; }
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
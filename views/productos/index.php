<?php include_once 'views/templates/header.php'; ?>

<div class="page-header d-flex justify-content-between align-items-center mb-3 flex-wrap gap-2" id="page-header-modern">
    <div>
        <h4 class="mb-0 fw-semibold"><i class="bx bx-package text-primary me-1"></i>Productos</h4>
        <small class="text-muted">Catálogo de productos y servicios</small>
    </div>
    <div class="d-flex flex-wrap gap-2">
        <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#modalProducto" id="btnAbrirNuevoProducto">
            <i class="bx bx-plus me-1"></i>Nuevo producto
        </button>
        <button type="button" class="btn btn-outline-primary" data-bs-toggle="modal" data-bs-target="#modalCargarProductos">
            <i class="bx bx-cloud-upload me-1"></i>Cargar plantilla
        </button>
        <a href="<?php echo BASE_URL . 'productos/reportePdf'; ?>" target="_blank" class="btn btn-outline-secondary">
            <i class="bx bxs-file-pdf me-1"></i>Reporte PDF
        </a>
        <button type="button" class="btn btn-outline-secondary" data-bs-toggle="modal" data-bs-target="#modalInactivos">
            <i class="bx bx-trash me-1"></i>Inactivos
        </button>
    </div>
</div>

<div class="card border-0 shadow-sm">
    <div class="card-body">
        <?php $tituloListado='Listado de Productos'; $iconoListado='bx-package'; include 'views/templates/listado_titulo.php'; ?>
        <div class="table-responsive">
            <table class="table table-hover nowrap" id="tblProductos" style="width: 100%;">
                <thead class="table-light">
                    <tr>
                        <th>Acciones</th>
                        <th>Descripción</th>
                        <th>Código</th>
                        <th class="text-end">P. Compra</th>
                        <th class="text-end">P. Venta</th>
                        <th class="text-center">Stock</th>
                        <th>Categoría</th>
                        <th>Foto</th>
                    </tr>
                </thead>
                <tbody></tbody>
            </table>
        </div>
    </div>
</div>

<!-- ============ MODAL: NUEVO / EDITAR PRODUCTO ============ -->
<div class="modal fade" id="modalProducto" tabindex="-1" aria-labelledby="modalProductoLabel" aria-hidden="true">
    <div class="modal-dialog modal-xl modal-dialog-centered modal-dialog-scrollable">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title fw-semibold" id="modalProductoLabel"><i class="bx bx-package text-primary me-1"></i>Datos del producto</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
            </div>
            <form id="formulario" autocomplete="off">
                <div class="modal-body">
                    <input type="hidden" id="id" name="id">
                    <input type="hidden" id="foto_actual" name="foto_actual">
                    <div class="row g-3">
                        <div class="col-md-4">
                            <label class="form-label small mb-1" for="codigo">Código <span class="text-danger">*</span></label>
                            <div class="input-group">
                                <span class="input-group-text"><i class="bx bx-barcode"></i></span>
                                <input class="form-control" type="text" name="codigo" id="codigo" placeholder="Barcode">
                            </div>
                            <span id="errorCodigo" class="text-danger small"></span>
                        </div>
                        <div class="col-md-8">
                            <label class="form-label small mb-1" for="nombre">Nombre <span class="text-danger">*</span></label>
                            <div class="input-group">
                                <span class="input-group-text"><i class="bx bx-purchase-tag-alt"></i></span>
                                <input class="form-control" type="text" name="nombre" id="nombre" placeholder="Nombre del producto">
                            </div>
                            <span id="errorNombre" class="text-danger small"></span>
                        </div>

                        <div class="col-md-4">
                            <label class="form-label small mb-1" for="precio_compra">Precio Compra</label>
                            <div class="input-group">
                                <span class="input-group-text">$</span>
                                <input class="form-control" type="number" step="0.01" min="0" name="precio_compra" id="precio_compra" placeholder="0.00" onkeypress="validarNumeroYDecimal(event)">
                            </div>
                            <span id="errorCompra" class="text-danger small"></span>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label small mb-1" for="precio_venta">Precio Venta <span class="text-danger">*</span></label>
                            <div class="input-group">
                                <span class="input-group-text">$</span>
                                <input class="form-control" type="number" step="0.01" min="0.01" name="precio_venta" id="precio_venta" placeholder="0.00" onkeypress="validarNumeroYDecimal(event)">
                            </div>
                            <span id="errorVenta" class="text-danger small"></span>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label small mb-1" for="id_iva">Valor Tributario <span class="text-danger">*</span></label>
                            <select id="id_iva" class="form-select" name="id_iva">
                                <option value="">Seleccionar</option>
                                <option value="15">Con IVA 15%</option>
                                <option value="0">Sin IVA 0%</option>
                            </select>
                            <span id="errorIva" class="text-danger small"></span>
                        </div>

                        <div class="col-md-6">
                            <label class="form-label small mb-1" for="id_categoria">Categoría <span class="text-danger">*</span></label>
                            <select id="id_categoria" class="form-select" name="id_categoria">
                                <option value="">Seleccionar</option>
                                <?php foreach ($data['categorias'] as $categoria) { ?>
                                    <option value="<?php echo htmlspecialchars($categoria['id'], ENT_QUOTES, 'UTF-8'); ?>"><?php echo htmlspecialchars($categoria['categoria'], ENT_QUOTES, 'UTF-8'); ?></option>
                                <?php } ?>
                            </select>
                            <span id="errorCategoria" class="text-danger small"></span>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label small mb-1" for="foto">Foto (opcional)</label>
                            <input id="foto" class="form-control" type="file" name="foto" accept="image/*">
                        </div>

                        <div class="col-12">
                            <div id="containerPreview" class="text-center" style="margin-top: 8px;"></div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-light" id="btnNuevo"><i class="bx bx-eraser me-1"></i>Limpiar</button>
                    <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-primary" id="btnAccion"><i class="bx bx-save me-1"></i>Registrar</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- ============ MODAL: CARGAR DESDE EXCEL ============ -->
<div class="modal fade" id="modalCargarProductos" tabindex="-1" aria-labelledby="modalCargarProductosLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title fw-semibold" id="modalCargarProductosLabel"><i class="bx bx-cloud-upload text-primary me-1"></i>Cargar productos desde Excel</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
            </div>
            <div class="modal-body">
                <?php
                $tituloCarga      = 'Sube tu archivo de productos';
                $descripcionCarga = 'Selecciona un archivo <strong>.xlsx</strong> con los productos a registrar.';
                include 'views/templates/cargar_excel.php';
                ?>
            </div>
        </div>
    </div>
</div>

<!-- ============ MODAL: PRODUCTOS INACTIVOS ============ -->
<div class="modal fade" id="modalInactivos" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-xl modal-dialog-centered modal-dialog-scrollable">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title fw-semibold"><i class="bx bx-trash text-danger me-1"></i>Productos inactivos</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="table-responsive">
                    <table class="table table-hover nowrap" id="tblProductosInactivos" style="width: 100%;">
                        <thead class="table-light"><tr>
                            <th>Descripción</th><th>Código</th><th>P. Compra</th><th>P. Venta</th>
                            <th>Stock</th><th>Categoría</th><th>Foto</th><th>Acciones</th>
                        </tr></thead>
                        <tbody></tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
(function () {
    var modalEl = document.getElementById('modalInactivos');
    if (!modalEl) return;
    var dtInactivos, initialized = false;
    modalEl.addEventListener('show.bs.modal', function () {
        if (initialized) { if (dtInactivos) dtInactivos.ajax.reload(null, false); return; }
        initialized = true;
        dtInactivos = $('#tblProductosInactivos').DataTable({
            deferRender: true, pageLength: 10,
            ajax: { url: base_url + 'productos/listarInactivos', dataSrc: '' },
            columns: [
                { data: 'descripcion' }, { data: 'codigo' }, { data: 'precio_compra' },
                { data: 'precio_venta' }, { data: 'cantidad' }, { data: 'categoria' },
                { data: 'foto' }, { data: 'acciones' }
            ],
            language: { url: base_url + 'assets/js/espanol.json' },
            responsive: true, order: [[0, 'asc']]
        });
    });
    window.restaurarProducto = function (id) {
        if (!dtInactivos) return;
        restaurarRegistros(base_url + 'productos/restaurar/' + id, dtInactivos);
    };
    document.addEventListener('mhn:restauradoOk', function () {
        if (typeof tblProductos !== 'undefined' && tblProductos) {
            tblProductos.ajax.reload(null, false);
        }
    });
})();
</script>

<script>
document.addEventListener('DOMContentLoaded', function () {
    var modal = document.getElementById('modalProducto');
    var modalCargar = document.getElementById('modalCargarProductos');
    var form  = document.getElementById('formulario');
    var formCargar = document.getElementById('cargarDatosExcel');
    var btnAccion = document.getElementById('btnAccion');

    if (form && modal) form.addEventListener('mhn:registroOk', function () {
        bootstrap.Modal.getOrCreateInstance(modal).hide();
    });
    if (formCargar && modalCargar) formCargar.addEventListener('mhn:registroOk', function () {
        bootstrap.Modal.getOrCreateInstance(modalCargar).hide();
    });

    var btnAbrir = document.getElementById('btnAbrirNuevoProducto');
    if (btnAbrir && form && btnAccion) btnAbrir.addEventListener('click', function () {
        form.reset();
        document.getElementById('id').value = '';
        document.getElementById('foto_actual').value = '';
        var preview = document.getElementById('containerPreview'); if (preview) preview.innerHTML = '';
        btnAccion.textContent = 'Registrar';
        ['errorCodigo','errorNombre','errorIva','errorCompra','errorVenta','errorCategoria']
            .forEach(function(id){ var el=document.getElementById(id); if (el) el.textContent=''; });
    });
});
window.abrirModalProducto = function () {
    var el = document.getElementById('modalProducto');
    if (el) bootstrap.Modal.getOrCreateInstance(el).show();
};
</script>

<?php include_once 'views/templates/footer.php'; ?>

<?php include_once 'views/templates/header.php'; ?>

<div class="page-header d-flex justify-content-between align-items-center mb-3 flex-wrap gap-2" id="page-header-modern">
    <div>
        <h4 class="mb-0 fw-semibold"><i class="bx bx-ruler text-primary me-1"></i>Medidas</h4>
        <small class="text-muted">Unidades de medida de productos</small>
    </div>
    <div class="d-flex flex-wrap gap-2">
        <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#modalMedida" id="btnAbrirNuevoMedida">
            <i class="bx bx-plus me-1"></i>Nueva medida
        </button>
        <button type="button" class="btn btn-outline-secondary" data-bs-toggle="modal" data-bs-target="#modalInactivos">
            <i class="bx bx-trash me-1"></i>Inactivos
        </button>
    </div>
</div>

<div class="card border-0 shadow-sm">
    <div class="card-body">
        <?php $tituloListado='Listado de Medidas'; $iconoListado='bx-ruler'; include 'views/templates/listado_titulo.php'; ?>
        <div class="table-responsive">
            <table class="table table-hover nowrap" id="tblMedidas" style="width: 100%;">
                <thead class="table-light">
                    <tr>
                        <th>Nombre</th>
                        <th>Nombre Corto</th>
                        <th>Acciones</th>
                    </tr>
                </thead>
                <tbody></tbody>
            </table>
        </div>
    </div>
</div>

<div class="modal fade" id="modalMedida" tabindex="-1" aria-labelledby="modalMedidaLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title fw-semibold" id="modalMedidaLabel"><i class="bx bx-ruler text-primary me-1"></i>Datos de la medida</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
            </div>
            <form id="formulario" autocomplete="off">
                <div class="modal-body">
                    <input type="hidden" id="id" name="id">
                    <div class="row g-3">
                        <div class="col-md-7">
                            <label class="form-label small mb-1" for="nombre">Nombre <span class="text-danger">*</span></label>
                            <div class="input-group">
                                <span class="input-group-text"><i class="bx bx-ruler"></i></span>
                                <input type="text" id="nombre" name="nombre" class="form-control" placeholder="ej. Kilogramo">
                            </div>
                            <span id="errorNombre" class="text-danger small"></span>
                        </div>
                        <div class="col-md-5">
                            <label class="form-label small mb-1" for="nombre_corto">Nombre Corto <span class="text-danger">*</span></label>
                            <div class="input-group">
                                <span class="input-group-text"><i class="bx bx-text"></i></span>
                                <input type="text" id="nombre_corto" name="nombre_corto" class="form-control" placeholder="ej. KG">
                            </div>
                            <span id="errorNombreCorto" class="text-danger small"></span>
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

<!-- ============ MODAL: MEDIDAS INACTIVAS ============ -->
<div class="modal fade" id="modalInactivos" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered modal-dialog-scrollable">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title fw-semibold"><i class="bx bx-trash text-danger me-1"></i>Medidas inactivas</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="table-responsive">
                    <table class="table table-hover nowrap" id="tblMedidasInactivos" style="width: 100%;">
                        <thead class="table-light"><tr><th>Nombre</th><th>Nombre Corto</th><th>Acciones</th></tr></thead>
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
        dtInactivos = $('#tblMedidasInactivos').DataTable({
            deferRender: true, pageLength: 10,
            ajax: { url: base_url + 'medidas/listarInactivos', dataSrc: '' },
            columns: [{ data: 'medida' }, { data: 'nombre_corto' }, { data: 'acciones' }],
            language: { url: base_url + 'assets/js/espanol.json' },
            responsive: true, order: [[0, 'asc']]
        });
    });
    window.restaurarMedida = function (id) {
        if (!dtInactivos) return;
        restaurarRegistros(base_url + 'medidas/restaurar/' + id, dtInactivos);
    };
    document.addEventListener('mhn:restauradoOk', function () {
        if (typeof tblMedidas !== 'undefined' && tblMedidas) {
            tblMedidas.ajax.reload(null, false);
        }
    });
})();
</script>

<script>
document.addEventListener('DOMContentLoaded', function () {
    var modal = document.getElementById('modalMedida');
    var form  = document.getElementById('formulario');
    var btnAccion = document.getElementById('btnAccion');
    if (form && modal) form.addEventListener('mhn:registroOk', function () {
        bootstrap.Modal.getOrCreateInstance(modal).hide();
    });
    var btnAbrir = document.getElementById('btnAbrirNuevoMedida');
    if (btnAbrir && form && btnAccion) btnAbrir.addEventListener('click', function () {
        form.reset();
        document.getElementById('id').value = '';
        btnAccion.textContent = 'Registrar';
        ['errorNombre','errorNombreCorto'].forEach(function(id){ var el=document.getElementById(id); if (el) el.textContent=''; });
    });
});
window.abrirModalMedida = function () {
    var el = document.getElementById('modalMedida');
    if (el) bootstrap.Modal.getOrCreateInstance(el).show();
};
</script>

<?php include_once 'views/templates/footer.php'; ?>

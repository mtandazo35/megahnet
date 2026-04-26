<?php include_once 'views/templates/header.php'; ?>

<div class="page-header d-flex justify-content-between align-items-center mb-3 flex-wrap gap-2" id="page-header-modern">
    <div>
        <h4 class="mb-0 fw-semibold"><i class="bx bx-headphone text-primary me-1"></i>Casos</h4>
        <small class="text-muted">Soporte técnico y registro de casos</small>
    </div>
    <div class="d-flex flex-wrap gap-2">
        <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#modalCaso" id="btnAbrirNuevoCaso">
            <i class="bx bx-plus me-1"></i>Nuevo caso
        </button>
    </div>
</div>

<div class="card border-0 shadow-sm">
    <div class="card-body">
        <?php $tituloListado='Historial de Casos'; $iconoListado='bx-history'; include 'views/templates/listado_titulo.php'; ?>

        <div class="filtros-fechas mb-2">
            <div class="form-group">
                <label for="desde">Desde</label>
                <input id="desde" class="form-control" type="date">
            </div>
            <div class="form-group">
                <label for="hasta">Hasta</label>
                <input id="hasta" class="form-control" type="date">
            </div>
        </div>

        <div class="table-responsive">
            <table class="table table-hover align-middle nowrap" id="tblHistorial" style="width: 100%;">
                <thead class="table-light">
                    <tr>
                        <th>Cliente</th>
                        <th>Fecha y Hora</th>
                        <th>Dirección</th>
                        <th>Coordenada</th>
                        <th>Problema Reportado</th>
                        <th>Responsable</th>
                        <th>Estado</th>
                        <th>Acciones</th>
                    </tr>
                </thead>
                <tbody></tbody>
            </table>
        </div>
    </div>
</div>

<!-- ============ MODAL: NUEVO / EDITAR CASO ============ -->
<div class="modal fade" id="modalCaso" tabindex="-1" aria-labelledby="modalCasoLabel" aria-hidden="true">
    <div class="modal-dialog modal-xl modal-dialog-centered modal-dialog-scrollable">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title fw-semibold" id="modalCasoLabel"><i class="bx bx-headphone text-primary me-1"></i>Datos del caso</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
            </div>
            <div class="modal-body">
                <input type="hidden" id="id" name="id">

                <h6 class="fw-semibold mb-2"><i class="bx bx-user me-1 text-primary"></i>Cliente</h6>
                <div class="row g-3 mb-2">
                    <div class="col-md-6">
                        <input type="hidden" id="idContrato">
                        <label class="form-label small mb-1" for="buscarCliente">Buscar Cliente <span class="text-danger">*</span></label>
                        <div class="input-group">
                            <span class="input-group-text"><i class="bx bx-search"></i></span>
                            <input class="form-control" type="text" id="buscarCliente" placeholder="Buscar Cliente">
                        </div>
                        <span class="text-danger fw-bold small" id="errorCliente"></span>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label small mb-1" for="direccionContrato">Dirección <span class="text-danger">*</span></label>
                        <div class="input-group">
                            <span class="input-group-text"><i class="bx bx-map"></i></span>
                            <input class="form-control" type="text" id="direccionContrato" placeholder="Dirección" disabled>
                        </div>
                        <span id="errorDireccion" class="text-danger small"></span>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label small mb-1" for="coordenadaContrato">Coordenada <span class="text-danger">*</span></label>
                        <div class="input-group">
                            <span class="input-group-text"><i class="bx bx-current-location"></i></span>
                            <input class="form-control" type="text" id="coordenadaContrato" placeholder="Coordenada" disabled>
                        </div>
                        <span id="errorCoordenada" class="text-danger small"></span>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label small mb-1" for="comentarioContrato">Comentario</label>
                        <div class="input-group">
                            <span class="input-group-text"><i class="bx bx-comment-detail"></i></span>
                            <input class="form-control" type="text" id="comentarioContrato" placeholder="Comentario" disabled>
                        </div>
                    </div>
                </div>

                <hr class="my-3">
                <h6 class="fw-semibold mb-2"><i class="bx bx-task me-1 text-primary"></i>Detalle del caso</h6>
                <div class="row g-3 mb-2">
                    <div class="col-md-4">
                        <label class="form-label small mb-1" for="problemaReportado">Problema Reportado <span class="text-danger">*</span></label>
                        <div class="input-group">
                            <span class="input-group-text"><i class="bx bx-error-circle"></i></span>
                            <input class="form-control" type="text" id="problemaReportado" placeholder="Problema Reportado">
                        </div>
                        <span id="errorProblemaReportado" class="text-danger small"></span>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label small mb-1" for="trabajoRealizado">Trabajo Realizado</label>
                        <div class="input-group">
                            <span class="input-group-text"><i class="bx bx-wrench"></i></span>
                            <input class="form-control" type="text" id="trabajoRealizado" placeholder="Trabajo Realizado">
                        </div>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label small mb-1" for="observacion">Observación</label>
                        <div class="input-group">
                            <span class="input-group-text"><i class="bx bx-note"></i></span>
                            <input class="form-control" type="text" id="observacion" placeholder="Observación">
                        </div>
                    </div>
                </div>

                <hr class="my-3">
                <h6 class="fw-semibold mb-2"><i class="bx bx-group me-1 text-primary"></i>Asignación</h6>
                <div class="row g-3">
                    <div class="col-md-4">
                        <input type="hidden" id="idGrupoTrabajo">
                        <label class="form-label small mb-1" for="buscarGrupoTrabajo">Buscar Grupo de Trabajo <span class="text-danger">*</span></label>
                        <div class="input-group">
                            <span class="input-group-text"><i class="bx bx-search"></i></span>
                            <input class="form-control" type="text" id="buscarGrupoTrabajo" placeholder="Buscar Grupo de Trabajo">
                        </div>
                        <span class="text-danger fw-bold small" id="errorGrupoTrabajo"></span>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label small mb-1" for="responsableGrupoTrabajo">Responsable</label>
                        <div class="input-group">
                            <span class="input-group-text"><i class="bx bx-user-circle"></i></span>
                            <input class="form-control" type="text" id="responsableGrupoTrabajo" placeholder="Responsable" disabled>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label small mb-1" for="estado">Estado</label>
                        <select id="estado" class="form-select">
                            <option value="INGRESADO">INGRESADO</option>
                            <option value="EN PROCESO">EN PROCESO</option>
                            <option value="FINALIZADO">FINALIZADO</option>
                        </select>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label small mb-1">Usuario</label>
                        <div class="input-group">
                            <span class="input-group-text"><i class="bx bx-user"></i></span>
                            <input class="form-control" type="text" value="<?php echo htmlspecialchars($_SESSION['nombre_usuario'] ?? '', ENT_QUOTES, 'UTF-8'); ?>" placeholder="Usuario" disabled>
                        </div>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancelar</button>
                <button type="button" class="btn btn-primary" id="btnAccion">
                    <i class="bx bx-check-circle me-1"></i>Completar
                </button>
            </div>
        </div>
    </div>
</div>

<script>
// Reset del form al abrir modal en modo "Nuevo" + helper para abrir desde editar.
document.addEventListener('DOMContentLoaded', function () {
    var modalCaso = document.getElementById('modalCaso');
    var btnAbrir  = document.getElementById('btnAbrirNuevoCaso');
    var btnAccion = document.getElementById('btnAccion');

    if (btnAbrir && modalCaso && btnAccion) {
        btnAbrir.addEventListener('click', function () {
            var ids = ['id','idContrato','buscarCliente','direccionContrato','coordenadaContrato',
                       'comentarioContrato','problemaReportado','trabajoRealizado','observacion',
                       'idGrupoTrabajo','buscarGrupoTrabajo','responsableGrupoTrabajo'];
            ids.forEach(function (id) { var el = document.getElementById(id); if (el) el.value = ''; });
            var sel = document.getElementById('estado'); if (sel) sel.value = 'INGRESADO';
            ['errorCliente','errorGrupoTrabajo','errorCoordenada','errorDireccion','errorProblemaReportado']
                .forEach(function (id) { var el = document.getElementById(id); if (el) el.textContent = ''; });
            btnAccion.textContent = 'Completar';
        });
    }
});
window.abrirModalCaso = function () {
    var el = document.getElementById('modalCaso');
    if (el) bootstrap.Modal.getOrCreateInstance(el).show();
};
</script>

<?php include_once 'views/templates/footer.php'; ?>

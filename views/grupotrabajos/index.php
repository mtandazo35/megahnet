<?php include_once 'views/templates/header.php'; ?>

<div class="page-header d-flex justify-content-between align-items-center mb-3 flex-wrap gap-2" id="page-header-modern">
    <div>
        <h4 class="mb-0 fw-semibold"><i class="bx bx-group text-primary me-1"></i>Grupos de Trabajo</h4>
        <small class="text-muted">Equipos asignados a casos técnicos</small>
    </div>
    <div class="d-flex flex-wrap gap-2">
        <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#modalGrupoTrabajo" id="btnAbrirNuevoGrupoTrabajo">
            <i class="bx bx-plus me-1"></i>Nuevo grupo
        </button>
        <a href="<?php echo BASE_URL . 'grupotrabajos/inactivos'; ?>" class="btn btn-outline-secondary">
            <i class="bx bx-trash me-1"></i>Inactivos
        </a>
    </div>
</div>

<div class="card border-0 shadow-sm">
    <div class="card-body">
        <?php $tituloListado='Grupos de Trabajo'; $iconoListado='bx-group'; include 'views/templates/listado_titulo.php'; ?>
        <div class="table-responsive">
            <table class="table table-hover align-middle nowrap" id="tblGrupoTrabajos" style="width: 100%;">
                <thead class="table-light">
                    <tr>
                        <th>Responsable</th>
                        <th>Descripción</th>
                        <th>Observación</th>
                        <th>Empleados</th>
                        <th>Acciones</th>
                    </tr>
                </thead>
                <tbody></tbody>
            </table>
        </div>
    </div>
</div>

<div class="modal fade" id="modalGrupoTrabajo" tabindex="-1" aria-labelledby="modalGrupoTrabajoLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title fw-semibold" id="modalGrupoTrabajoLabel"><i class="bx bx-group text-primary me-1"></i>Datos del grupo de trabajo</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
            </div>
            <form id="formulario" autocomplete="off">
                <div class="modal-body">
                    <input type="hidden" id="id" name="id">
                    <div class="row g-3">
                        <div class="col-md-12">
                            <input type="hidden" id="idUsuario" name="idUsuario">
                            <label class="form-label small mb-1" for="buscarUsuario">Responsable <span class="text-danger">*</span></label>
                            <div class="input-group">
                                <span class="input-group-text"><i class="bx bx-search"></i></span>
                                <input class="form-control" type="text" id="buscarUsuario" placeholder="Buscar usuario responsable">
                            </div>
                            <span class="text-danger fw-bold small" id="errorUsuario"></span>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label small mb-1" for="descripcion">Descripción <span class="text-danger">*</span></label>
                            <div class="input-group">
                                <span class="input-group-text"><i class="bx bx-text"></i></span>
                                <input class="form-control" type="text" name="descripcion" id="descripcion" placeholder="Descripción del grupo">
                            </div>
                            <span id="errorDescripcion" class="text-danger small"></span>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label small mb-1" for="observacion">Observación <span class="text-danger">*</span></label>
                            <div class="input-group">
                                <span class="input-group-text"><i class="bx bx-note"></i></span>
                                <input id="observacion" type="text" class="form-control" name="observacion" placeholder="Observación">
                            </div>
                            <span id="errorObservacion" class="text-danger small"></span>
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

<script>
document.addEventListener('DOMContentLoaded', function () {
    var modal = document.getElementById('modalGrupoTrabajo');
    var form  = document.getElementById('formulario');
    var btnAccion = document.getElementById('btnAccion');
    if (form && modal) form.addEventListener('mhn:registroOk', function () {
        bootstrap.Modal.getOrCreateInstance(modal).hide();
    });
    var btnAbrir = document.getElementById('btnAbrirNuevoGrupoTrabajo');
    if (btnAbrir && form && btnAccion) btnAbrir.addEventListener('click', function () {
        form.reset();
        document.getElementById('id').value = '';
        document.getElementById('idUsuario').value = '';
        btnAccion.textContent = 'Registrar';
        ['errorUsuario','errorDescripcion','errorObservacion'].forEach(function(id){ var el=document.getElementById(id); if (el) el.textContent=''; });
    });
});
window.abrirModalGrupoTrabajo = function () {
    var el = document.getElementById('modalGrupoTrabajo');
    if (el) bootstrap.Modal.getOrCreateInstance(el).show();
};
</script>

<?php include_once 'views/templates/footer.php'; ?>

<?php include_once 'views/templates/header.php'; ?>

<div class="page-header d-flex justify-content-between align-items-center mb-3 flex-wrap gap-2" id="page-header-modern">
    <div>
        <h4 class="mb-0 fw-semibold"><i class="bx bx-user-circle text-primary me-1"></i>Usuarios</h4>
        <small class="text-muted">Cuentas del sistema y permisos</small>
    </div>
    <div class="d-flex flex-wrap gap-2">
        <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#modalUsuario" id="btnAbrirNuevoUsuario">
            <i class="bx bx-user-plus me-1"></i>Nuevo usuario
        </button>
        <button type="button" class="btn btn-outline-secondary" data-bs-toggle="modal" data-bs-target="#modalInactivos">
            <i class="bx bx-trash me-1"></i>Inactivos
        </button>
    </div>
</div>

<div class="card border-0 shadow-sm">
    <div class="card-body">
        <?php $tituloListado='Listado de Usuarios'; $iconoListado='bx-user-circle'; include 'views/templates/listado_titulo.php'; ?>
        <div class="table-responsive">
            <table class="table table-hover nowrap" id="tblUsuarios" style="width: 100%;">
                <thead class="table-light">
                    <tr>
                        <th>Nombres</th>
                        <th>Correo</th>
                        <th>Teléfono</th>
                        <th>Dirección</th>
                        <th>Rol</th>
                        <th>Grupo Trabajo</th>
                        <th>Acciones</th>
                    </tr>
                </thead>
                <tbody></tbody>
            </table>
        </div>
    </div>
</div>

<!-- ============ MODAL: NUEVO / EDITAR USUARIO ============ -->
<div class="modal fade" id="modalUsuario" tabindex="-1" aria-labelledby="modalUsuarioLabel" aria-hidden="true">
    <div class="modal-dialog modal-xl modal-dialog-centered modal-dialog-scrollable">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title fw-semibold" id="modalUsuarioLabel"><i class="bx bx-user-plus text-primary me-1"></i>Datos del usuario</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
            </div>
            <form id="formulario" autocomplete="off">
                <div class="modal-body">
                    <input type="hidden" id="id" name="id">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label small mb-1" for="nombres">Nombres <span class="text-danger">*</span></label>
                            <div class="input-group">
                                <span class="input-group-text"><i class="bx bx-user"></i></span>
                                <input type="text" id="nombres" name="nombres" class="form-control" placeholder="Nombres completos">
                            </div>
                            <span id="errorNombre" class="text-danger small"></span>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label small mb-1" for="apellidos">Apellidos <span class="text-danger">*</span></label>
                            <div class="input-group">
                                <span class="input-group-text"><i class="bx bx-user"></i></span>
                                <input type="text" id="apellidos" name="apellidos" class="form-control" placeholder="Apellidos completos">
                            </div>
                            <span id="errorApellido" class="text-danger small"></span>
                        </div>

                        <div class="col-md-6">
                            <label class="form-label small mb-1" for="correo">Correo electrónico <span class="text-danger">*</span></label>
                            <div class="input-group">
                                <span class="input-group-text"><i class="bx bx-envelope"></i></span>
                                <input type="email" id="correo" name="correo" class="form-control" placeholder="usuario@correo.com">
                            </div>
                            <span id="errorCorreo" class="text-danger small"></span>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label small mb-1" for="telefono">Teléfono <span class="text-danger">*</span></label>
                            <div class="input-group">
                                <span class="input-group-text"><i class="bx bx-phone"></i></span>
                                <input type="text" id="telefono" name="telefono" class="form-control" placeholder="Teléfono / Celular">
                            </div>
                            <span id="errorTelefono" class="text-danger small"></span>
                        </div>

                        <div class="col-12">
                            <label class="form-label small mb-1" for="direccion">Dirección <span class="text-danger">*</span></label>
                            <div class="input-group">
                                <span class="input-group-text"><i class="bx bx-map"></i></span>
                                <input type="text" id="direccion" name="direccion" class="form-control" placeholder="Dirección">
                            </div>
                            <span id="errorDireccion" class="text-danger small"></span>
                        </div>

                        <div class="col-md-4">
                            <label class="form-label small mb-1" for="clave">Contraseña <span class="text-danger">*</span></label>
                            <div class="input-group" id="show_hide_clave_usr">
                                <span class="input-group-text"><i class="bx bx-lock"></i></span>
                                <input type="password" id="clave" name="clave" class="form-control" placeholder="Contraseña" autocomplete="new-password">
                                <a href="javascript:;" class="input-group-text" aria-label="Mostrar/ocultar" style="cursor:pointer;"><i class="bx bx-hide"></i></a>
                            </div>
                            <span id="errorClave" class="text-danger small"></span>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label small mb-1" for="rol">Rol <span class="text-danger">*</span></label>
                            <select class="form-select" id="rol" name="rol">
                                <option value="" selected>Seleccionar</option>
                                <option value="1">Administrador</option>
                                <option value="2">Secretario(a)</option>
                                <option value="3">Técnico</option>
                            </select>
                            <span id="errorRol" class="text-danger small"></span>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label small mb-1" for="grupotrabajo">Grupo de Trabajo <span class="text-danger">*</span></label>
                            <select class="form-select" id="grupotrabajo" name="grupotrabajo">
                                <option value="" selected>Seleccionar</option>
                                <?php foreach ($data['grupotrabajos'] as $g) { ?>
                                    <option value="<?php echo htmlspecialchars($g['id'], ENT_QUOTES, 'UTF-8'); ?>"><?php echo htmlspecialchars($g['descripcion'], ENT_QUOTES, 'UTF-8'); ?></option>
                                <?php } ?>
                            </select>
                            <span id="errorGrupoTrabajo" class="text-danger small"></span>
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

<!-- ============ MODAL: USUARIOS INACTIVOS ============ -->
<div class="modal fade" id="modalInactivos" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-xl modal-dialog-centered modal-dialog-scrollable">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title fw-semibold"><i class="bx bx-trash text-danger me-1"></i>Usuarios inactivos</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="table-responsive">
                    <table class="table table-hover nowrap" id="tblUsuariosInactivos" style="width:100%;">
                        <thead class="table-light"><tr>
                            <th>Nombres</th><th>Correo</th><th>Teléfono</th><th>Dirección</th><th>Rol</th><th>Acciones</th>
                        </tr></thead>
                        <tbody></tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function () {
    var modal = document.getElementById('modalUsuario');
    var form  = document.getElementById('formulario');
    var btnAccion = document.getElementById('btnAccion');
    if (form && modal) form.addEventListener('mhn:registroOk', function () {
        bootstrap.Modal.getOrCreateInstance(modal).hide();
    });
    var btnAbrir = document.getElementById('btnAbrirNuevoUsuario');
    if (btnAbrir && form && btnAccion) btnAbrir.addEventListener('click', function () {
        form.reset();
        document.getElementById('id').value = '';
        var clv = document.getElementById('clave'); if (clv) clv.removeAttribute('readonly');
        btnAccion.textContent = 'Registrar';
        ['errorNombre','errorApellido','errorCorreo','errorTelefono','errorDireccion','errorClave','errorRol','errorGrupoTrabajo']
            .forEach(function(id){ var el=document.getElementById(id); if (el) el.textContent=''; });
    });
    // Show/hide para Contrasena
    var trigger = document.querySelector('#show_hide_clave_usr a');
    if (trigger) trigger.addEventListener('click', function (e) {
        e.preventDefault();
        var input = document.querySelector('#show_hide_clave_usr input');
        var icon  = document.querySelector('#show_hide_clave_usr a i');
        if (!input || !icon) return;
        if (input.type === 'password') { input.type = 'text';  icon.classList.remove('bx-hide'); icon.classList.add('bx-show'); }
        else                            { input.type = 'password'; icon.classList.remove('bx-show'); icon.classList.add('bx-hide'); }
    });
});
window.abrirModalUsuario = function () {
    var el = document.getElementById('modalUsuario');
    if (el) bootstrap.Modal.getOrCreateInstance(el).show();
};

// Modal de inactivos: lazy init + funcion restaurarUsuario
(function () {
    var modalEl = document.getElementById('modalInactivos');
    if (!modalEl) return;
    var dtInactivos, initialized = false;
    modalEl.addEventListener('show.bs.modal', function () {
        if (initialized) { if (dtInactivos) dtInactivos.ajax.reload(null, false); return; }
        initialized = true;
        dtInactivos = $('#tblUsuariosInactivos').DataTable({
            deferRender: true, pageLength: 10,
            ajax: { url: base_url + 'usuarios/listarInactivos', dataSrc: '' },
            columns: [
                { data: 'nombres' }, { data: 'correo' }, { data: 'telefono' },
                { data: 'direccion' }, { data: 'rol' }, { data: 'acciones' }
            ],
            language: { url: base_url + 'assets/js/espanol.json' },
            responsive: true, order: [[0, 'asc']]
        });
    });
    window.restaurarUsuario = function (id) {
        if (!dtInactivos) return;
        restaurarRegistros(base_url + 'usuarios/restaurar/' + id, dtInactivos);
    };
    document.addEventListener('mhn:restauradoOk', function () {
        if (typeof tblUsuarios !== 'undefined' && tblUsuarios) {
            tblUsuarios.ajax.reload(null, false);
        }
    });
})();
</script>

<?php include_once 'views/templates/footer.php'; ?>

<?php include_once 'views/templates/header.php'; ?>

<div class="page-header d-flex justify-content-between align-items-center mb-3 flex-wrap gap-2" id="page-header-modern">
    <div>
        <h4 class="mb-0 fw-semibold"><i class="bx bx-store text-primary me-1"></i>Proveedores</h4>
        <small class="text-muted">Listado y registro de proveedores</small>
    </div>
    <div class="d-flex flex-wrap gap-2">
        <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#modalProveedor" id="btnAbrirNuevoProveedor">
            <i class="bx bx-plus me-1"></i>Nuevo proveedor
        </button>
        <a href="<?php echo BASE_URL . 'proveedor/inactivos'; ?>" class="btn btn-outline-secondary">
            <i class="bx bx-trash me-1"></i>Inactivos
        </a>
    </div>
</div>

<div class="card border-0 shadow-sm">
    <div class="card-body">
        <?php $tituloListado='Listado de Proveedores'; $iconoListado='bx-store'; include 'views/templates/listado_titulo.php'; ?>
        <div class="table-responsive">
            <table class="table table-hover align-middle nowrap" id="tblProveedores" style="width: 100%;">
                <thead class="table-light">
                    <tr>
                        <th>Nombre</th>
                        <th>RUC</th>
                        <th>Teléfono</th>
                        <th>Correo</th>
                        <th>Dirección</th>
                        <th>Acciones</th>
                    </tr>
                </thead>
                <tbody></tbody>
            </table>
        </div>
    </div>
</div>

<!-- ============ MODAL: NUEVO / EDITAR PROVEEDOR ============ -->
<div class="modal fade" id="modalProveedor" tabindex="-1" aria-labelledby="modalProveedorLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title fw-semibold" id="modalProveedorLabel"><i class="bx bx-store text-primary me-1"></i>Datos del proveedor</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
            </div>
            <form id="formulario" autocomplete="off">
                <div class="modal-body">
                    <input type="hidden" id="id" name="id">
                    <div class="row g-3">
                        <div class="col-md-4">
                            <label class="form-label small mb-1" for="ruc">RUC <span class="text-danger">*</span></label>
                            <div class="input-group">
                                <span class="input-group-text"><i class="bx bx-id-card"></i></span>
                                <input class="form-control" type="number" name="ruc" id="ruc"
                                       placeholder="N° RUC" onkeypress="validarNumero(event)">
                            </div>
                            <span id="errorRuc" class="text-danger small"></span>
                        </div>
                        <div class="col-md-8">
                            <label class="form-label small mb-1" for="nombre">Nombre <span class="text-danger">*</span></label>
                            <div class="input-group">
                                <span class="input-group-text"><i class="bx bx-building"></i></span>
                                <input class="form-control" type="text" name="nombre" id="nombre"
                                       placeholder="Nombre del proveedor" onkeypress="validarLetras(event)">
                            </div>
                            <span id="errorNombre" class="text-danger small"></span>
                        </div>

                        <div class="col-md-6">
                            <label class="form-label small mb-1" for="telefono">Teléfono</label>
                            <div class="input-group">
                                <span class="input-group-text"><i class="bx bx-phone"></i></span>
                                <input class="form-control" type="number" name="telefono" id="telefono"
                                       placeholder="Teléfono" onkeypress="validarNumero(event)">
                            </div>
                            <span id="errorTelefono" class="text-danger small"></span>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label small mb-1" for="correo">Correo electrónico</label>
                            <div class="input-group">
                                <span class="input-group-text"><i class="bx bx-envelope"></i></span>
                                <input class="form-control" type="email" name="correo" id="correo"
                                       placeholder="proveedor@correo.com">
                            </div>
                            <span id="errorCorreo" class="text-danger small"></span>
                        </div>

                        <div class="col-12">
                            <label class="form-label small mb-1" for="direccion">Dirección <span class="text-danger">*</span></label>
                            <textarea id="direccion" class="form-control" name="direccion" rows="3" placeholder="Dirección"></textarea>
                            <span id="errorDireccion" class="text-danger small"></span>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-light" id="btnNuevo">
                        <i class="bx bx-eraser me-1"></i>Limpiar
                    </button>
                    <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-primary" id="btnAccion">
                        <i class="bx bx-save me-1"></i>Registrar
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
// Cerrar modal tras registro exitoso (evento mhn:registroOk emitido por insertarRegistros).
document.addEventListener('DOMContentLoaded', function () {
    var modalProveedor = document.getElementById('modalProveedor');
    var formulario     = document.getElementById('formulario');
    var btnAccion      = document.getElementById('btnAccion');

    if (formulario && modalProveedor) {
        formulario.addEventListener('mhn:registroOk', function () {
            bootstrap.Modal.getOrCreateInstance(modalProveedor).hide();
        });
    }
    var btnAbrir = document.getElementById('btnAbrirNuevoProveedor');
    if (btnAbrir && formulario && btnAccion) {
        btnAbrir.addEventListener('click', function () {
            formulario.reset();
            document.getElementById('id').value = '';
            btnAccion.textContent = 'Registrar';
            ['errorRuc','errorNombre','errorTelefono','errorCorreo','errorDireccion']
                .forEach(function(id){ var el = document.getElementById(id); if (el) el.textContent = ''; });
        });
    }
});
window.abrirModalProveedor = function () {
    var el = document.getElementById('modalProveedor');
    if (el) bootstrap.Modal.getOrCreateInstance(el).show();
};
</script>

<?php include_once 'views/templates/footer.php'; ?>

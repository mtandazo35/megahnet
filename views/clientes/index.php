<?php include_once 'views/templates/header.php'; ?>

<div class="page-header d-flex justify-content-between align-items-center mb-3 flex-wrap gap-2" id="page-header-modern">
    <div>
        <h4 class="mb-0 fw-semibold"><i class="bx bx-user text-primary me-1"></i>Clientes</h4>
        <small class="text-muted">Listado, registro y carga masiva de clientes</small>
    </div>
    <div class="d-flex flex-wrap gap-2">
        <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#modalCliente" id="btnAbrirNuevoCliente">
            <i class="bx bx-user-plus me-1"></i>Nuevo cliente
        </button>
        <button type="button" class="btn btn-outline-primary" data-bs-toggle="modal" data-bs-target="#modalCargarClientes">
            <i class="bx bx-cloud-upload me-1"></i>Cargar plantilla
        </button>
        <a href="<?php echo BASE_URL . 'clientes/inactivos'; ?>" class="btn btn-outline-secondary">
            <i class="bx bx-trash me-1"></i>Inactivos
        </a>
    </div>
</div>

<div class="card border-0 shadow-sm">
    <div class="card-body">
        <?php $tituloListado='Listado de Clientes'; $iconoListado='bx-user'; include 'views/templates/listado_titulo.php'; ?>
        <div class="table-responsive">
            <table class="table table-hover align-middle nowrap" id="tblClientes" style="width: 100%;">
                <thead class="table-light">
                    <tr>
                        <th>Acciones</th>
                        <th>Razón Social</th>
                        <th>N° Identidad</th>
                        <th>Identidad</th>
                        <th>Teléfono</th>
                        <th>Correo</th>
                        <th>Dirección</th>
                    </tr>
                </thead>
                <tbody></tbody>
            </table>
        </div>
    </div>
</div>

<!-- ============ MODAL: NUEVO / EDITAR CLIENTE ============ -->
<div class="modal fade" id="modalCliente" tabindex="-1" aria-labelledby="modalClienteLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title fw-semibold" id="modalClienteLabel"><i class="bx bx-user-plus text-primary me-1"></i>Datos del cliente</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
            </div>
            <form id="formulario" autocomplete="off">
                <div class="modal-body">
                    <input type="hidden" id="id" name="id">
                    <div class="row g-3">
                        <div class="col-md-3">
                            <label class="form-label small mb-1" for="identidad">Tipo Identidad <span class="text-danger">*</span></label>
                            <select id="identidad" class="form-select" name="identidad">
                                <option value="">Seleccionar</option>
                                <option value="CEDULA">CÉDULA</option>
                                <option value="RUC">RÚC</option>
                            </select>
                            <span id="errorIdentidad" class="text-danger small"></span>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label small mb-1" for="num_identidad">Cédula / RUC <span class="text-danger">*</span></label>
                            <div class="input-group">
                                <span class="input-group-text"><i class="bx bx-id-card"></i></span>
                                <input class="form-control" type="number" name="num_identidad" id="num_identidad"
                                       placeholder="N° Identidad" onkeypress="validarNumero(event)">
                            </div>
                            <span id="errorNum_identidad" class="text-danger small"></span>
                        </div>
                        <div class="col-md-5">
                            <label class="form-label small mb-1" for="nombre">Razón Social <span class="text-danger">*</span></label>
                            <div class="input-group">
                                <span class="input-group-text"><i class="bx bx-building"></i></span>
                                <input class="form-control" type="text" name="nombre" id="nombre"
                                       placeholder="Nombre o razón social" onkeypress="validarLetras(event)">
                            </div>
                            <span id="errorNombre" class="text-danger small"></span>
                        </div>

                        <div class="col-md-6">
                            <label class="form-label small mb-1" for="telefono">Teléfono</label>
                            <div class="input-group">
                                <span class="input-group-text"><i class="bx bx-phone"></i></span>
                                <input class="form-control" type="number" name="telefono" id="telefono"
                                       placeholder="Teléfono / Celular" onkeypress="validarNumero(event)">
                            </div>
                            <span id="errorTelefono" class="text-danger small"></span>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label small mb-1" for="correo">Correo electrónico</label>
                            <div class="input-group">
                                <span class="input-group-text"><i class="bx bx-envelope"></i></span>
                                <input class="form-control" type="text" name="correo" id="correo"
                                       placeholder="cliente@correo.com">
                            </div>
                            <span id="errorCorreo" class="text-danger small"></span>
                        </div>

                        <div class="col-12">
                            <label class="form-label small mb-1" for="direccion">Dirección <span class="text-danger">*</span></label>
                            <div class="input-group">
                                <span class="input-group-text"><i class="bx bx-map"></i></span>
                                <input id="direccion" type="text" class="form-control" name="direccion" placeholder="Dirección">
                            </div>
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

<!-- ============ MODAL: CARGAR DESDE EXCEL ============ -->
<div class="modal fade" id="modalCargarClientes" tabindex="-1" aria-labelledby="modalCargarClientesLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title fw-semibold" id="modalCargarClientesLabel"><i class="bx bx-cloud-upload text-primary me-1"></i>Cargar clientes desde Excel</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
            </div>
            <div class="modal-body">
                <?php
                $tituloCarga      = 'Sube tu archivo de clientes';
                $descripcionCarga = 'Selecciona un archivo <strong>.xlsx</strong> con los clientes a registrar.';
                include 'views/templates/cargar_excel.php';
                ?>
            </div>
        </div>
    </div>
</div>

<script>
// Cerrar modales tras registro exitoso (evento mhn:registroOk emitido por insertarRegistros).
document.addEventListener('DOMContentLoaded', function () {
    var modalCliente   = document.getElementById('modalCliente');
    var modalCargar    = document.getElementById('modalCargarClientes');
    var formulario     = document.getElementById('formulario');
    var formCargar     = document.getElementById('cargarDatosExcel');
    var btnAccion      = document.getElementById('btnAccion');

    if (formulario && modalCliente) {
        formulario.addEventListener('mhn:registroOk', function () {
            bootstrap.Modal.getOrCreateInstance(modalCliente).hide();
        });
    }
    if (formCargar && modalCargar) {
        formCargar.addEventListener('mhn:registroOk', function () {
            bootstrap.Modal.getOrCreateInstance(modalCargar).hide();
        });
    }
    // Reset del modal al abrirlo via boton "Nuevo cliente" (no en edicion)
    var btnAbrir = document.getElementById('btnAbrirNuevoCliente');
    if (btnAbrir && formulario && btnAccion) {
        btnAbrir.addEventListener('click', function () {
            formulario.reset();
            document.getElementById('id').value = '';
            btnAccion.textContent = 'Registrar';
            // limpiar mensajes de error
            ['errorIdentidad','errorNum_identidad','errorNombre','errorTelefono','errorCorreo','errorDireccion']
                .forEach(function(id){ var el = document.getElementById(id); if (el) el.textContent = ''; });
        });
    }
});
// Wrapper para que editarCliente() del clientes.js abra el modal en lugar del tab antiguo.
// (firstTab.show() es no-op; necesitamos abrir el modal explicitamente.)
window.abrirModalCliente = function () {
    var el = document.getElementById('modalCliente');
    if (el) bootstrap.Modal.getOrCreateInstance(el).show();
};
</script>

<?php include_once 'views/templates/footer.php'; ?>

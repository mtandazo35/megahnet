<?php include_once 'views/templates/header.php'; ?>

<div class="page-header d-flex justify-content-between align-items-center mb-3 flex-wrap gap-2">
    <div>
        <h4 class="mb-0 fw-semibold"><i class="bx bx-store-alt text-primary me-1"></i>Sucursales</h4>
        <small class="text-muted">Establecimientos y puntos de emisión por sucursal</small>
    </div>
    <div class="d-flex flex-wrap gap-2">
        <button type="button" class="btn btn-primary" id="btnAbrirNueva" data-bs-toggle="modal" data-bs-target="#modalSucursal">
            <i class="bx bx-plus me-1"></i>Nueva sucursal
        </button>
        <button type="button" class="btn btn-outline-secondary" data-bs-toggle="modal" data-bs-target="#modalInactivos">
            <i class="bx bx-trash me-1"></i>Inactivos
        </button>
    </div>
</div>

<div class="card border-0 shadow-sm">
    <div class="card-body">
        <?php $tituloListado='Listado de Sucursales'; $iconoListado='bx-store-alt'; include 'views/templates/listado_titulo.php'; ?>
        <div class="table-responsive">
            <table class="table table-hover nowrap" id="tblSucursales" style="width:100%;">
                <thead class="table-light">
                    <tr>
                        <th>Nombre</th>
                        <th>Dirección</th>
                        <th class="text-center">Estab. - Pto. Emi.</th>
                        <th class="text-center">Ambiente</th>
                        <th class="text-center">Acciones</th>
                    </tr>
                </thead>
                <tbody></tbody>
            </table>
        </div>
    </div>
</div>

<!-- ================= Modal Nueva / Editar Sucursal ================= -->
<div id="modalSucursal" class="modal fade" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered modal-dialog-scrollable">
        <div class="modal-content border-0 shadow-lg">
            <div class="modal-header text-white" style="background: linear-gradient(135deg,#2563eb,#1e40af); border-radius: calc(0.5rem - 1px) calc(0.5rem - 1px) 0 0;">
                <h5 class="modal-title"><i class="bx bx-store-alt me-1"></i><span id="modalSucursalTitulo">Nueva Sucursal</span></h5>
                <button class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>

            <form id="formularioSucursal" autocomplete="off">
                <div class="modal-body">
                    <input type="hidden" id="suc_id" name="id" value="0">

                    <!-- Datos del establecimiento -->
                    <div class="form-section">
                        <div class="form-section-title"><i class="bx bx-building"></i>Datos del Establecimiento</div>
                        <div class="row g-3">
                            <div class="col-md-7">
                                <label class="form-label">Nombre del establecimiento <span class="text-danger">*</span></label>
                                <div class="input-group">
                                    <span class="input-group-text"><i class="fas fa-store"></i></span>
                                    <input type="text" id="suc_nombre" name="nombre" class="form-control" placeholder="Ej. Mi Empresa S.A. - Matriz">
                                </div>
                                <span id="errorSucNombre" class="text-danger small"></span>
                            </div>
                            <div class="col-md-5">
                                <label class="form-label">Dirección</label>
                                <div class="input-group">
                                    <span class="input-group-text"><i class="fas fa-home"></i></span>
                                    <input type="text" id="suc_direccion" name="direccion" class="form-control" placeholder="Ej. Av. Amazonas 123, Quito">
                                </div>
                            </div>
                            <div class="col-md-3">
                                <label class="form-label">N° Establecimiento <span class="text-danger">*</span></label>
                                <div class="input-group">
                                    <span class="input-group-text">001</span>
                                    <input type="number" id="suc_establecimiento" name="establecimiento" class="form-control" min="1" max="999" placeholder="1">
                                </div>
                                <small class="text-muted">3 dígitos (001-999)</small>
                            </div>
                            <div class="col-md-3">
                                <label class="form-label">N° Punto de Emisión <span class="text-danger">*</span></label>
                                <div class="input-group">
                                    <span class="input-group-text">001</span>
                                    <input type="number" id="suc_puntoemi" name="puntoemi" class="form-control" min="1" max="999" placeholder="1">
                                </div>
                                <small class="text-muted">3 dígitos (001-999)</small>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Ambiente <span class="text-danger">*</span></label>
                                <div class="input-group">
                                    <span class="input-group-text"><i class="fas fa-server"></i></span>
                                    <select id="suc_ambiente" name="ambiente" class="form-select">
                                        <option value="PRUEBAS">PRUEBAS (no fiscal)</option>
                                        <option value="PRODUCCION">PRODUCCIÓN (fiscal)</option>
                                    </select>
                                </div>
                                <small class="text-muted">PRODUCCIÓN emite comprobantes válidos al SRI.</small>
                            </div>
                        </div>
                    </div>

                    <!-- Secuenciales en producción -->
                    <div class="form-section">
                        <div class="form-section-title"><i class="bx bx-list-ol"></i>Secuenciales en Producción</div>
                        <div class="row g-3">
                            <div class="col-md-4">
                                <label class="form-label">N° Secuencial Factura</label>
                                <div class="input-group">
                                    <span class="input-group-text"><i class="fas fa-receipt"></i></span>
                                    <input type="number" id="suc_sec_factura" name="sec_factura" class="form-control" min="1" value="1">
                                </div>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">N° Secuencial Nota de Crédito</label>
                                <div class="input-group">
                                    <span class="input-group-text"><i class="fas fa-undo"></i></span>
                                    <input type="number" id="suc_sec_notacredito" name="sec_notacredito" class="form-control" min="1" value="1">
                                </div>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">N° Secuencial Recibo</label>
                                <div class="input-group">
                                    <span class="input-group-text"><i class="fas fa-file-alt"></i></span>
                                    <input type="number" id="suc_sec_recibo" name="sec_recibo" class="form-control" min="1" value="1">
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Secuenciales en pruebas -->
                    <div class="form-section">
                        <div class="form-section-title"><i class="bx bx-test-tube"></i>Secuenciales en Pruebas</div>
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="form-label">N° Secuencial Factura (Pruebas)</label>
                                <div class="input-group">
                                    <span class="input-group-text"><i class="fas fa-receipt"></i></span>
                                    <input type="number" id="suc_sec_factura_pruebas" name="sec_factura_pruebas" class="form-control" min="1" value="1">
                                </div>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">N° Secuencial Nota de Crédito (Pruebas)</label>
                                <div class="input-group">
                                    <span class="input-group-text"><i class="fas fa-undo"></i></span>
                                    <input type="number" id="suc_sec_notacredito_pruebas" name="sec_notacredito_pruebas" class="form-control" min="1" value="1">
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="modal-footer">
                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-primary" id="btnAccionSucursal"><i class="bx bx-save me-1"></i>Guardar</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- ================= Modal Inactivos ================= -->
<div id="modalInactivos" class="modal fade" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content border-0 shadow-lg">
            <div class="modal-header text-white" style="background: linear-gradient(135deg,#6b7280,#374151); border-radius: calc(0.5rem - 1px) calc(0.5rem - 1px) 0 0;">
                <h5 class="modal-title"><i class="bx bx-trash me-1"></i>Sucursales Inactivas</h5>
                <button class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="table-responsive">
                    <table class="table table-hover" id="tblSucursalesInactivos" style="width:100%;">
                        <thead class="table-light">
                            <tr>
                                <th>Nombre</th>
                                <th>Estab. - Pto. Emi.</th>
                                <th class="text-center">Acción</th>
                            </tr>
                        </thead>
                        <tbody></tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include_once 'views/templates/footer.php'; ?>

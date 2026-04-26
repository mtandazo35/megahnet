<?php include_once 'views/templates/header.php'; ?>

<div class="page-header d-flex justify-content-between align-items-center mb-3 flex-wrap gap-2" id="page-header-modern">
    <div>
        <h4 class="mb-0 fw-semibold"><i class="bx bx-user text-primary me-1"></i>Clientes</h4>
        <small class="text-muted">Registra, consulta y carga clientes</small>
    </div>
    <div class="d-flex gap-2">
        <a href="<?php echo BASE_URL . 'clientes/inactivos'; ?>" class="btn btn-outline-secondary btn-sm">
            <i class="bx bx-trash me-1"></i>Inactivos
        </a>
    </div>
</div>

<div class="card border-0 shadow-sm">
    <div class="card-body">
        <ul class="nav nav-tabs nav-primary mb-3" role="tablist" id="nav-tab">
            <li class="nav-item" role="presentation">
                <button class="nav-link active" id="nav-nuevo-tab" data-bs-toggle="tab" data-bs-target="#nav-nuevo" type="button" role="tab" aria-controls="nav-nuevo" aria-selected="true">
                    <i class="bx bx-user-plus me-1"></i>Nuevo Cliente
                </button>
            </li>
            <li class="nav-item" role="presentation">
                <button class="nav-link" id="nav-clientes-tab" data-bs-toggle="tab" data-bs-target="#nav-clientes" type="button" role="tab" aria-controls="nav-clientes" aria-selected="false">
                    <i class="bx bx-user me-1"></i>Clientes
                </button>
            </li>
            <li class="nav-item" role="presentation">
                <button class="nav-link" id="nav-cargar-tab" data-bs-toggle="tab" data-bs-target="#nav-cargar" type="button" role="tab" aria-controls="nav-cargar" aria-selected="false">
                    <i class="bx bx-spreadsheet me-1"></i>Cargar Clientes
                </button>
            </li>
        </ul>

        <div class="tab-content" id="nav-tabContent">

            <!-- ============ NUEVO CLIENTE ============ -->
            <div class="tab-pane fade show active" id="nav-nuevo" role="tabpanel" aria-labelledby="nav-nuevo-tab" tabindex="0">
                <?php $tituloListado='Datos del cliente'; $iconoListado='bx-user-plus'; include 'views/templates/listado_titulo.php'; ?>
                <form id="formulario" autocomplete="off">
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

                    <hr class="my-4">
                    <div class="d-flex flex-wrap gap-2 justify-content-end">
                        <button class="btn btn-light" type="button" id="btnNuevo">
                            <i class="bx bx-eraser me-1"></i>Limpiar
                        </button>
                        <button class="btn btn-primary" type="submit" id="btnAccion">
                            <i class="bx bx-save me-1"></i>Registrar
                        </button>
                    </div>
                </form>
            </div>

            <!-- ============ CLIENTES ============ -->
            <div class="tab-pane fade" id="nav-clientes" role="tabpanel" aria-labelledby="nav-clientes-tab" tabindex="0">
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

            <!-- ============ CARGAR CLIENTES ============ -->
            <div class="tab-pane fade" id="nav-cargar" role="tabpanel" aria-labelledby="nav-cargar-tab" tabindex="0">
                <?php
                $tituloCarga      = 'Cargar clientes desde Excel';
                $descripcionCarga = 'Sube un archivo <strong>.xlsx</strong> con los clientes a registrar.';
                include 'views/templates/cargar_excel.php';
                ?>
            </div>

        </div>
    </div>
</div>

<?php include_once 'views/templates/footer.php'; ?>

<?php include_once 'views/templates/header.php'; ?>

<div class="card">
    <div class="card-body">
        <div class="d-flex align-items-center">
            <div></div>
            <div class="dropdown ms-auto">
                <a class="dropdown-toggle dropdown-toggle-nocaret" href="#" data-bs-toggle="dropdown"><i
                        class='bx bx-dots-horizontal-rounded font-22 text-option'></i>
                </a>
                <ul class="dropdown-menu">
                    <li><a class="dropdown-item" href="<?php echo BASE_URL . 'clientes/inactivos'; ?>"><i
                                class="fas fa-trash text-danger"></i> Inactivos</a>
                    </li>
                </ul>
            </div>
        </div>
        <nav>
            <div class="nav nav-tabs" id="nav-tab" role="tablist">
                <button class="nav-link active" id="nav-clientes-tab" data-bs-toggle="tab"
                    data-bs-target="#nav-clientes" type="button" role="tab" aria-controls="nav-clientes"
                    aria-selected="true">Clientes</button>
                    <button class="nav-link" id="nav-cargar-tab" data-bs-toggle="tab" data-bs-target="#nav-cargar"
                    type="button" role="tab" aria-controls="nav-cargar" aria-selected="false">Cargar Clientes</button>
                <button class="nav-link" id="nav-nuevo-tab" data-bs-toggle="tab" data-bs-target="#nav-nuevo"
                    type="button" role="tab" aria-controls="nav-nuevo" aria-selected="false">Nuevo</button>
                    
            </div>
        </nav>
        <div class="tab-content" id="nav-tabContent">
            <div class="tab-pane fade show active mt-2" id="nav-clientes" role="tabpanel"
                aria-labelledby="nav-clientes-tab" tabindex="0">
                <h5 class="card-title text-center"><i class="fas fa-users"></i> Listado de Clientes</h5>
                <hr>
                <div class="table-responsive">
                    <table class="table table-bordered table-striped table-hover align-middle nowrap" id="tblClientes"
                        style="width: 100%;">
                        <thead>
                            <tr>
							<th></th>
                            <th>Razón Social</th>
                            <th>N° Identidad</th>

                                <th>Identidad</th>
                                <th>Teléfono</th>
                                <th>Correo</th>
                                <th>Dirección</th>
                                
                            </tr>
                        </thead>

                        <tbody>
                        </tbody>
                    </table>
                </div>
            </div>
            <div class="tab-pane fade p-3" id="nav-nuevo" role="tabpanel" aria-labelledby="nav-nuevo-tab" tabindex="0">
                <form id="formulario" autocomplete="off">
                    <input type="hidden" id="id" name="id">
                    <div class="row mb-3">
                        <div class="col-md-2 mb-3">
                            <div class="form-group">
                                <label for="identidad">Tipo Identidad <span class="text-danger">*</span></label>
                                <select id="identidad" class="form-control" name="identidad">
                                    <option value="">SELECCIONAR</option>
                                    <option value="CEDULA">CÉDULA</option>
                                    <option value="RUC">RÚC</option>
                                </select>
                            </div>
                            <span id="errorIdentidad" class="text-danger"></span>
                        </div>
                        <div class="col-md-4 mb-3">
                            <label for="num_identidad">Cédula / Rúc <span class="text-danger">*</span></label>
                            <div class="input-group">
                                <span class="input-group-text"><i class="fas fa-list"></i></span>
                                <input class="form-control" type="number" name="num_identidad" id="num_identidad"
                                    placeholder="N° Identidad" onkeypress="validarNumero(event)">
                            </div>
                            <span id="errorNum_identidad" class="text-danger"></span>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="nombre">Razón Social <span class="text-danger">*</span></label>
                            <div class="input-group">
                                <span class="input-group-text"><i class="fas fa-list"></i></span>
                                <input class="form-control" type="text" name="nombre" id="nombre"
                                    placeholder="Razón Social" onkeypress="validarLetras(event)">
                            </div>
                            <span id="errorNombre" class="text-danger"></span>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="telefono">Teléfono</label>
                            <div class="input-group">
                                <span class="input-group-text"><i class="fas fa-phone"></i></span>
                                <input class="form-control" type="number" name="telefono" id="telefono"
                                    placeholder="Telefono / Célular" onkeypress="validarNumero(event)">
                            </div>
                            <span id="errorTelefono" class="text-danger"></span>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="correo">Correo Electrónico</label>
                            <div class="input-group">
                                <span class="input-group-text"><i class="fas fa-envelope"></i></span>
                                <input class="form-control" type="text" name="correo" id="correo"
                                    placeholder="Correo Electrónico">
                            </div>
                            <span id="errorCorreo" class="text-danger"></span>
                        </div>
                        <div class="col-md-12 mb-3">
                            <div class="form-group">
                                <label for="direccion">Dirreción <span class="text-danger">*</span></label>
                                <input id="direccion" type="text" class="form-control" name="direccion"
                                    placeholder="Dirección"></input>
                            </div>
                            <span id="errorDireccion" class="text-danger"></span>
                        </div>

                    </div>
                    <div class="text-end">
                        <button class="btn btn-danger" type="button" id="btnNuevo">Nuevo</button>
                        <button class="btn btn-primary" type="submit" id="btnAccion">Registrar</button>
                    </div>
                </form>
            </div>

            <div class="tab-pane fade p-3" id="nav-cargar" role="tabpanel"  >
                <form id="cargarDatosExcel" action="" method="post" enctype="multipart/form-data">
                    <div class="row mb-3">
                        <div class="col-md-12 mb-3">
                           <input class="form-control" type="file" name="excel" id="excel">
                        </div>
                        <span id="errorExcel" class="text-danger"></span>


                    </div>
                    <div class="text-end">
                        <button class="btn btn-primary" type="submit" id="btnCargar">Registrar</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<?php include_once 'views/templates/footer.php'; ?>
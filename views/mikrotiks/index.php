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
                    <li><a class="dropdown-item" href="<?php echo BASE_URL . 'mikrotiks/inactivos'; ?>"><i
                                class="fas fa-trash text-danger"></i> Inactivos</a>
                    </li>
                </ul>
            </div>
        </div>
        <nav>
            <div class="nav nav-tabs" id="nav-tab" role="tablist">
                <button class="nav-link active" id="nav-mikrotiks-tab" data-bs-toggle="tab"
                    data-bs-target="#nav-mikrotiks" type="button" role="tab" aria-controls="nav-mikrotiks"
                    aria-selected="true">Mikrotiks</button>
                <button class="nav-link" id="nav-nuevo-tab" data-bs-toggle="tab" data-bs-target="#nav-nuevo"
                    type="button" role="tab" aria-controls="nav-nuevo" aria-selected="false">Nuevo</button>

            </div>
        </nav>
        <div class="tab-content" id="nav-tabContent">
            <div class="tab-pane fade show active mt-2" id="nav-mikrotiks" role="tabpanel"
                aria-labelledby="nav-mikrotiks-tab" tabindex="0">
                <?php $tituloListado='Listado de Mikrotiks'; $iconoListado='bx-server'; include 'views/templates/listado_titulo.php'; ?>
                <div class="table-responsive">
                    <table class="table table-bordered table-striped table-hover align-middle nowrap" id="tblMikrotiks"
                        style="width: 100%;">
                        <thead>
                            <tr>
							 <th></th>
                                <th>Nombre</th>
                                <th>Ip Publica</th>
                                <th>Usuario</th>
                                <th>Clave</th>
                                <th>Puerto</th>
                               
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
                        <div class="col-md-6 mb-3">
                            <label for="nombre">Nombre Mikrotik <span class="text-danger">*</span></label>
                            <div class="input-group">
                                <span class="input-group-text"><i class="fas fa-list"></i></span>
                                <input class="form-control" type="text" name="nombre" id="nombre"
                                    placeholder="Nombre Mikrotik">
                            </div>
                            <span id="errorNombre" class="text-danger"></span>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="ip">Ip Publica <span class="text-danger">*</span></label>
                            <div class="input-group">
                                <span class="input-group-text"><i class="fas fa-list"></i></span>
                                <input class="form-control" type="text" name="ip" id="ip"
                                    placeholder="Ip Publica - Mikrotik">
                            </div>
                            <span id="errorIp" class="text-danger"></span>
                        </div>
                        <div class="col-md-4 mb-3">
                            <label for="usuario">Usuario Mikrotik</label>
                            <div class="input-group">
                                <span class="input-group-text"><i class="fa fa-user" aria-hidden="true"></i></span>
                                <input class="form-control" type="text" name="usuario" id="usuario"
                                    placeholder="Usuario Mikrotik">
                            </div>
                            <span id="errorUsuario" class="text-danger"></span>
                        </div>
                        <div class="col-md-4 mb-3">
                            <label for="clave">Clave Mikrotik</label>
                            <div class="input-group">
                                <span class="input-group-text"><i class="fas fa-lock"></i></span>
                                <input class="form-control" type="password" name="clave" id="clave"
                                    placeholder="Clave Mikrotik">
                            </div>
                            <span id="errorClave" class="text-danger"></span>
                        </div>
                         <div class="col-md-4 mb-3">
                            <label for="puerto">Puerto Mikrotik</label>
                            <div class="input-group">
                                <span class="input-group-text"><i class="fa fa-external-link-square" aria-hidden="true"></i></span>
                                <input class="form-control" type="text" name="puerto" id="puerto"
                                    placeholder="Puerto Mikrotik">
                            </div>
                            <span id="errorPuerto" class="text-danger"></span>
                        </div>
                       

                    </div>
                    <div class="text-end">
                        <button class="btn btn-danger" type="button" id="btnNuevo">Nuevo</button>
                        <button class="btn btn-primary" type="submit" id="btnAccion">Registrar</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<?php include_once 'views/templates/footer.php'; ?>
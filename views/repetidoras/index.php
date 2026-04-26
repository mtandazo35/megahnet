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
                    aria-selected="true">Repetidoras</button>                   
                <button class="nav-link" id="nav-nuevo-tab" data-bs-toggle="tab" data-bs-target="#nav-nuevo"
                    type="button" role="tab" aria-controls="nav-nuevo" aria-selected="false">Nuevo</button>
                    
            </div>
        </nav>
        <div class="tab-content" id="nav-tabContent">
            <div class="tab-pane fade show active mt-2" id="nav-clientes" role="tabpanel"
                aria-labelledby="nav-clientes-tab" tabindex="0">
                <h5 class="card-title text-center"><i class="fas fa-users"></i> Listado de Repetidoras</h5>
                <hr>
                <div class="table-responsive">
                    <table class="table table-bordered table-striped table-hover align-middle nowrap" id="tblRepetidoras"
                        style="width: 100%;">
                        <thead>
                            <tr>
                            <th>Ssid</th>

                            <th>Marca</th>

                                <th>Ip</th>
                                <th>Canal</th>
                                <th>Seguridad</th>
                                <th>Frecuencia</th>
                                <th></th>
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
                      
                        <div class="col-md-4 mb-3">
                            <label for="marca">Marca <span class="text-danger">*</span></label>
                            <div class="input-group">
                                <span class="input-group-text"><i class="fas fa-list"></i></span>
                                <input class="form-control" type="text" name="marca" id="marca"
                                    placeholder="Marca">
                            </div>
                            <span id="errorMarca" class="text-danger"></span>
                        </div>
                        <div class="col-md-4 mb-3">
                            <label for="ssid">Ssid <span class="text-danger">*</span></label>
                            <div class="input-group">
                                <span class="input-group-text"><i class="fas fa-list"></i></span>
                                <input class="form-control" type="text" name="ssid" id="ssid"
                                    placeholder="Ssid">
                            </div>
                            <span id="errorSsid" class="text-danger"></span>
                        </div>
                        <div class="col-md-4 mb-3">
                            <label for="ip">Ip</label>
                            <div class="input-group">
                                <span class="input-group-text"><i class="fas fa-phone"></i></span>
                                <input class="form-control" type="text" name="ip" id="ip"
                                    placeholder="Ip">
                            </div>
                            <span id="errorIp" class="text-danger"></span>
                        </div>
                        <div class="col-md-4 mb-3">
                            <label for="canal">Canal</label>
                            <div class="input-group">
                                <span class="input-group-text"><i class="fas fa-envelope"></i></span>
                                <input class="form-control" type="text" name="canal" id="canal"
                                    placeholder="Canal">
                            </div>
                            <span id="errorCanal" class="text-danger"></span>
                        </div>
                        <div class="col-md-4 mb-3">
                            <div class="form-group">
                                <label for="seguridad">Seguridad <span class="text-danger">*</span></label>
                                <input id="seguridad" type="text" class="form-control" name="seguridad"
                                    placeholder="Seguridad"></input>
                            </div>
                            <span id="errorSeguridad" class="text-danger"></span>
                        </div>
                        <div class="col-md-4 mb-3">
                            <div class="form-group">
                                <label for="frecuencia">Frecuencia <span class="text-danger">*</span></label>
                                <input id="frecuencia" type="text" class="form-control" name="frecuencia"
                                    placeholder="Frecuencia"></input>
                            </div>
                            <span id="errorFrecuencia" class="text-danger"></span>
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
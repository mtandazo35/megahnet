<?php include_once 'views/templates/header.php'; ?>

<div class="card">
    <div class="card-body">
        <div class="d-flex align-items-center">
            <div></div>
            <div class="dropdown ms-auto">
                <a class="dropdown-toggle dropdown-toggle-nocaret" href="#" data-bs-toggle="dropdown"><i class='bx bx-dots-horizontal-rounded font-22 text-option'></i>
                </a>
                <ul class="dropdown-menu">
                    <li><a class="dropdown-item" href="<?php echo BASE_URL . 'grupotrabajos/inactivos'; ?>"><i class="fas fa-trash text-danger"></i> Inactivos</a>
                    </li>
                </ul>
            </div>
        </div>
        <nav>
            <div class="nav nav-tabs" id="nav-tab" role="tablist">
                <button class="nav-link active" id="nav-grupotrabajos-tab" data-bs-toggle="tab" data-bs-target="#nav-grupotrabajos" type="button" role="tab" aria-controls="nav-grupotrabajos" aria-selected="true">Grupo Trabajos</button>
                <button class="nav-link" id="nav-nuevo-tab" data-bs-toggle="tab" data-bs-target="#nav-nuevo" type="button" role="tab" aria-controls="nav-nuevo" aria-selected="false">Nuevo</button>
            </div>
        </nav>
        <div class="tab-content" id="nav-tabContent">
            <div class="tab-pane fade show active mt-2" id="nav-grupotrabajos" role="tabpanel" aria-labelledby="nav-grupotrabajos-tab" tabindex="0">
                <?php $tituloListado='Grupos de Trabajo'; $iconoListado='bx-group'; include 'views/templates/listado_titulo.php'; ?>
                <div class="table-responsive">
                    <table class="table table-bordered table-striped table-hover align-middle nowrap" id="tblGrupoTrabajos" style="width: 100%;">
                        <thead>
                            <tr>
                                <th>Responsable</th>
                                <th>Descripcion</th>
                                <th>Observacion</th>
                                <th>Empleados</th>
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
                    <div class="row justify-content-between">

                        <div class="col-md-3">
                            <div>
                                <label>Buscar Usuario</label>
                                <div class="input-group mb-2">
                                    <input type="hidden" id="idUsuario" name="idUsuario">
                                    <span class="input-group-text"><i class="fas fa-search"></i></span>
                                    <input class="form-control" type="text" id="buscarUsuario" placeholder="Buscar Usuario">
                                </div>
                                <span class="text-danger fw-bold mb-2" id="errorUsuario"></span>
                            </div>
                        </div>                  
                       

                    </div>
                    <div class="row mb-3">
                        <div class="col-md-6 mb-3">
                            <label for="descripcion">Descripción <span class="text-danger">*</span></label>
                            <div class="input-group">
                                <span class="input-group-text"><i class="fas fa-list"></i></span>
                                <input class="form-control" type="text" name="descripcion" id="descripcion" placeholder="Descrición">
                            </div>
                            <span id="errorDescripcion" class="text-danger"></span>
                        </div>





                        <div class="col-md-6 mb-3">
                            <div class="form-group">
                                <label for="observacion">Observación <span class="text-danger">*</span></label>
                                <input id="observacion" type="text" class="form-control" name="observacion" placeholder="Observación"></input>
                            </div>
                            <span id="errorObservacion" class="text-danger"></span>
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
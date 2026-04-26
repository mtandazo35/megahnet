<?php include_once 'views/templates/header.php'; ?>

<div class="card">
    <div class="card-body">

        <nav>
            <div class="nav nav-tabs" id="nav-tab" role="tablist">
                <button class="nav-link active" id="nav-historial-tab" data-bs-toggle="tab"
                    data-bs-target="#nav-historial" type="button" role="tab" aria-controls="nav-historial"
                    aria-selected="true">historial</button>
                <button class="nav-link" id="nav-casos-tab" data-bs-toggle="tab" data-bs-target="#nav-casos"
                    type="button" role="tab" aria-controls="nav-casos" aria-selected="false">Nuevo</button>
                <!--- <button class="nav-link active" id="nav-contratos-tab" data-bs-toggle="tab" data-bs-target="#nav-contratos" type="button" role="tab" aria-controls="nav-contratos" aria-selected="true">Contratos</button>
                <button class="nav-link" id="nav-historial-tab" data-bs-toggle="tab" data-bs-target="#nav-historial" type="button" role="tab" aria-controls="nav-historial" aria-selected="false">Historial</button>--->
            </div>
        </nav>
        <div class="tab-content" id="nav-tabContent">
            <div class="tab-pane fade show active p-3" id="nav-historial" role="tabpanel"
                aria-labelledby="nav-historial-tab" tabindex="0">
                <div class="d-flex justify-content-center mb-3">
                    <div class="form-group">
                        <label for="desde">Desde</label>
                        <input id="desde" class="form-control" type="date">
                    </div>
                    <div class="form-group">
                        <label for="hasta">Hasta</label>
                        <input id="hasta" class="form-control" type="date">
                    </div>
                </div>
                <div class="table-responsive">
                    <table class="table table-bordered table-striped table-hover align-middle nowrap" id="tblHistorial"
                        style="width: 100%;">
                        <thead>
                            <tr>
                            <th>Cliente</th>
                                <th>Fecha y Hora</th>
                                <th>Dirección</th>
                                <th>Coordenada</th>
                                <th>Problema Reportado</th>  
                                <th>Responsable</th>                               
                                <th>Estado</th>                       

                                <th></th>
                            </tr>
                        </thead>

                        <tbody>
                        </tbody>
                    </table>
                </div>
            </div>
            <div class=" tab-pane fade p-3" id="nav-casos" role="tabpanel" aria-labelledby="nav-casos-tab" tabindex="0">
                <h5 class="card-title text-center"><i class="fas fa-list-alt"></i> Nuevo Casos</h5>
                <hr>
                <div class="row justify-content-between">

                    <div class="col-md-3">
                        <div>
                            <input type="hidden" id="id" name="id">
                            <label>Buscar Cliente</label>
                            <div class="input-group mb-2">
                                <input type="hidden" id="idContrato">
                                <span class="input-group-text"><i class="fas fa-search"></i></span>
                                <input class="form-control" type="text" id="buscarCliente" placeholder="Buscar Cliente">
                            </div>
                            <span class="text-danger fw-bold mb-2" id="errorCliente"></span>
                        </div>
                    </div>
                    <div class="col-md-3">

                        <label>Dirección <span class="text-danger">*</span></label>
                        <div class="input-group mb-2">
                            <span class="input-group-text"><i class="fas fa-dollar-sign"></i></span>
                            <input class="form-control" type="text" id="direccionContrato" placeholder="Direcion" disabled>
                        </div>
                        <span id="errorDireccion" class="text-danger"></span>

                    </div>
                    <div class="col-md-3">

                        <label>Coordenada <span class="text-danger">*</span></label>
                        <div class="input-group mb-4">
                            <span class="input-group-text"><i class="fas fa-phone"></i></span>
                            <input class="form-control" type="text" id="coordenadaContrato" placeholder="Coordenada" disabled>
                        </div>
                        <span id="errorCoordenada" class="text-danger"></span>

                    </div>
                    <div class="col-md-3">

                        <label>Comentario <span class="text-danger">*</span></label>
                        <div class="input-group mb-4">
                            <span class="input-group-text"><i class="fas fa-phone"></i></span>
                            <input class="form-control" type="text" id="comentarioContrato" placeholder="Comentario" disabled>
                        </div>

                    </div>





                    <div class="col-md-4">

                        <label>Problema Reportado <span class="text-danger">*</span></label>
                        <div class="input-group mb-4">
                            <span class="input-group-text"><i class="fas fa-phone"></i></span>
                            <input class="form-control" type="text" id="problemaReportado"
                                placeholder="Problema Reportado">
                        </div>
                        <span id="errorProblemaReportado" class="text-danger"></span>


                    </div>
                    <div class="col-md-4">

                        <label>Trabajo Realizado</label>
                        <div class="input-group mb-4">
                            <span class="input-group-text"><i class="fas fa-phone"></i></span>
                            <input class="form-control" type="text" id="trabajoRealizado"
                                placeholder="Trabajo Realizado">
                        </div>

                    </div>
                    <div class="col-md-4">

                        <label>Observación </label>
                        <div class="input-group mb-4">
                            <span class="input-group-text"><i class="fas fa-phone"></i></span>
                            <input class="form-control" type="text" id="observacion" placeholder="Observación">
                        </div>

                    </div>
                </div>

                <!-- table productos -->



                <hr>

                <div class="row justify-content-between">
                    <div class="col-md-4">
                        <div>
                            <label>Buscar Grupo Trabajo</label>
                            <div class="input-group mb-2">
                                <input type="hidden" id="idGrupoTrabajo">
                                <span class="input-group-text"><i class="fas fa-search"></i></span>
                                <input class="form-control" type="text" id="buscarGrupoTrabajo" placeholder="Buscar Grupo Trabajo">
                            </div>
                            <span class="text-danger fw-bold mb-2" id="errorGrupoTrabajo"></span>
                        </div>

                        <label>Responsable</label>
                        <div class="input-group mb-2">
                            <span class="input-group-text"><i class="fas fa-phone"></i></span>
                            <input class="form-control" type="text" id="responsableGrupoTrabajo" placeholder="Responsable"
                                disabled>
                        </div>

                       
                    </div>

                    <div class="col-md-4">
                        <label>Usuario</label>
                        <div class="input-group mb-2">
                            <span class="input-group-text"><i class="fas fa-user"></i></span>
                            <input class="form-control" type="text" value="<?php echo $_SESSION['nombre_usuario']; ?>"
                                placeholder="Usuario" disabled>
                        </div>
                    </div>

                    <div class="col-md-4">
                        <div class="form-group mb-2">
                            <label for="estado">Estado</label>
                            <select id="estado" class="form-control">
                                <option value="INGRESADO">INGRESADO</option>
                                <option value="EN PROCESO">EN PROCESO</option>
                                <option value="FINALIZADO">FINALIZADO</option>
                            </select>
                        </div>



                        <div class="d-grid">
                            <button class="btn btn-primary" type="button" id="btnAccion">Completar</button>
                        </div>
                    </div>
                </div>
            </div>

        </div>
    </div>
</div>

<?php include_once 'views/templates/footer.php'; ?>
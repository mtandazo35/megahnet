<?php include_once 'views/templates/header.php'; 

?>

<div class="card">

    <div class="card-body ">
        <div class="d-flex align-items-center">
            <div>

            </div>
            <div class="dropdown ms-auto">
                <a class="dropdown-toggle dropdown-toggle-nocaret" href="#" data-bs-toggle="dropdown"><i class='bx bx-dots-horizontal-rounded font-22 text-option'></i>
                </a>
                <ul class="dropdown-menu">
                    <li><a class="dropdown-item" href="<?php echo BASE_URL . 'usuarios/inactivos'; ?>"><i class="fas fa-trash text-danger"></i> Inactivos</a>
                    </li>

                </ul>
            </div>
        </div>
        <nav>
            <div class="nav nav-tabs" id="nav-tab" role="tablist">
                <button class="nav-link active" id="nav-usuarios-tab" data-bs-toggle="tab" data-bs-target="#nav-usuarios" type="button" role="tab" aria-controls="nav-usuarios" aria-selected="true">Usuarios</button>
                <button class="nav-link" id="nav-nuevo-tab" data-bs-toggle="tab" data-bs-target="#nav-nuevo" type="button" role="tab" aria-controls="nav-nuevo" aria-selected="false">Nuevo</button>
            </div>
        </nav>
        <div class="tab-content" id="nav-tabContent">
            <div class="tab-pane fade show active mt-2" id="nav-usuarios" role="tabpanel" aria-labelledby="nav-usuarios-tab" tabindex="0">
                <?php $tituloListado='Listado de Usuarios'; $iconoListado='bx-user-circle'; include 'views/templates/listado_titulo.php'; ?>
                <div class="table-responsive">
                    <table class="table table-bordered table-striped table-hover nowrap" id="tblUsuarios" style="width: 100%;">
                        <thead class="thead-light">
                            <tr>
                                <th>Nombres</th>
                                <th>Correo</th>
                                <th>Telefono</th>
                                <th>Direccion</th>
                                <th>Rol</th>
                                <th>Grupo Trabajo</th>
                                <th></th>
                            </tr>
                        </thead>
                        <tbody>

                        </tbody>
                    </table>
                </div>
            </div>
            <div class="tab-pane fade " id="nav-nuevo" role="tabpanel" aria-labelledby="nav-nuevo-tab" tabindex="0">

                <form class="p-4" id="formulario" autocomplete="off">
                    <input type="hidden" id="id" name="id">
                    <div class="row">
                        <div class="col-lg-4 col-sm-6 mb-2">
                            <label>Nombres <span class="text-danger">*</span></label>
                            <div class="input-group">
                                <span class="input-group-text"><i class="fas fa-list"></i></span>
                                <input type="text" id="nombres" name="nombres" class="form-control" placeholder="Nombres Completos">
                            </div>
                            <span id="errorNombre" class="text-danger"></span>
                        </div>
                        <div class="col-lg-4 col-sm-6 mb-2">
                            <label>Apellidos <span class="text-danger">*</span></label>
                            <div class="input-group">
                                <span class="input-group-text"><i class="fas fa-list-alt"></i></span>
                                <input type="text" id="apellidos" name="apellidos" class="form-control" placeholder="Apellidos Completos">
                            </div>
                            <span id="errorApellido" class="text-danger"></span>

                        </div>
                        <div class="col-lg-4 col-sm-6 mb-2">
                            <label>Correo Electrónico <span class="text-danger">*</span></label>
                            <div class="input-group">
                                <span class="input-group-text"><i class="fas fa-envelope"></i></span>
                                <input type="email" id="correo" name="correo" class="form-control" placeholder="Correo Electrónico">
                            </div>
                            <span id="errorCorreo" class="text-danger"></span>

                        </div>
                        <div class="col-lg-4 col-sm-6 mb-2">
                            <label>Teléfono <span class="text-danger">*</span></label>
                            <div class="input-group">
                                <span class="input-group-text"><i class="fas fa-phone"></i></span>
                                <input type="text" id="telefono" name="telefono" class="form-control" placeholder="Telófono / Celular">
                            </div>
                            <span id="errorTelefono" class="text-danger"></span>

                        </div>
                        <div class="col-lg-8 col-sm-6 mb-2">
                            <label>Dirección <span class="text-danger">*</span></label>
                            <div class="input-group">
                                <span class="input-group-text"><i class="fas fa-home"></i></span>
                                <input type="text" id="direccion" name="direccion" class="form-control" placeholder="Dirección">
                            </div>
                            <span id="errorDireccion" class="text-danger"></span>

                        </div>
                        <div class="col-lg-4 col-sm-6 mb-2">
                            <label>Contraseña <span class="text-danger">*</span></label>
                            <div class="input-group">
                                <span class="input-group-text"><i class="fas fa-lock"></i></span>
                                <input type="password" id="clave" name="clave" class="form-control" placeholder="Contraseña de Acceso">
                            </div>
                            <span id="errorClave" class="text-danger"></span>

                        </div>
                        <div class="col-lg-4 col-sm-6 mb-2">
                            <label>Rol <span class="text-danger">*</span></label>
                            <div class="input-group">
                                <label class="input-group-text" for="rol"><i class="fas fa-id-card"></i></label>
                                <select class="form-select" id="rol" name="rol">
                                    <option value="" selected>Seleccionar</option>
                                    <option value="1">Administrador</option>
                                    <option value="2">Secretario(a)</option>
                                    <option value="3">Tecnico</option>

                                </select>


                            </div>
                            <span id="errorRol" class="text-danger"></span>

                        </div>
                        <div class="col-lg-4 col-sm-6 mb-2">
                            <label>Grupo Trabajo <span class="text-danger">*</span></label>
                            <div class="input-group">
                                <label class="input-group-text" for="grupotrabajo"><i class="fas fa-id-card"></i></label>
                               
                                <select class="form-select" id="grupotrabajo" name="grupotrabajo">
                                    <option value="" selected>Seleccionar</option>
                                    <?php  foreach ($data['grupotrabajos'] as $grupotrabajos) { ?>
                                        <option value="<?php echo $grupotrabajos['id'];?>"><?php echo $grupotrabajos['descripcion'];?></option>

                                   <?php }  ?>
                                  
                                </select>

                            </div>
                            <span id="errorGrupoTrabajo" class="text-danger"></span>

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
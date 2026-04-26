<?php include_once 'views/templates/header.php'; ?>

<!-- Header con título y acciones -->
<div class="page-header d-flex justify-content-between align-items-center mb-3 flex-wrap gap-2">
  <div>
    <h4 class="mb-0 fw-semibold"><i class="bx bx-file text-primary me-1"></i>Contratos</h4>
    <small class="text-muted">Administración y seguimiento de contratos activos</small>
  </div>
  <div class="d-flex gap-2 flex-wrap">
    <button class="btn btn-light border" id="nuevoPagoFactura"><i class="bx bx-dollar-circle"></i> Facturar</button>
    <a class="btn btn-light border" href="<?= BASE_URL.'contratos/inactivos' ?>"><i class="bx bx-trash"></i> Inactivos</a>
    <a class="btn btn-light border" href="<?= BASE_URL.'contratos/reporteExcel' ?>"><i class="bx bxs-file-export text-success"></i> Excel</a>
    <button class="btn btn-primary" onclick="document.getElementById('nav-contratos-tab').click()"><i class="bx bx-plus"></i> Nuevo contrato</button>
  </div>
</div>

<div class="card radius-10 border-0 shadow-sm">
    <div class="card-body">

        <nav>
            <div class="nav nav-tabs nav-tabs-modern" id="nav-tab" role="tablist">
                <button class="nav-link active" id="nav-historial-tab" data-bs-toggle="tab"
                    data-bs-target="#nav-historial" type="button" role="tab" aria-controls="nav-historial"
                    aria-selected="true"><i class="bx bx-list-ul me-1"></i>Historial</button>
                <button class="nav-link" id="nav-cargar-tab" data-bs-toggle="tab" data-bs-target="#nav-cargar"
                    type="button" role="tab" aria-controls="nav-cargar" aria-selected="false"><i class="bx bx-upload me-1"></i>Cargar Contratos</button>
                <button class="nav-link" id="nav-contratosSuspender-tab" data-bs-toggle="tab"
                    data-bs-target="#nav-contratosSuspender" type="button" role="tab"
                    aria-controls="nav-contratosSuspender" aria-selected="false"><i class="bx bx-error-circle text-warning me-1"></i>Por Suspender</button>
                <button class="nav-link" id="nav-contratos-tab" data-bs-toggle="tab" data-bs-target="#nav-contratos"
                    type="button" role="tab" aria-controls="nav-contratos" aria-selected="false"><i class="bx bx-plus-circle me-1"></i>Nuevo</button>
            </div>
        </nav>

        <div class="tab-content" id="nav-tabContent">
            <div class="tab-pane fade show active p-3" id="nav-historial" role="tabpanel"
                aria-labelledby="nav-historial-tab" tabindex="0">

                <div class="table-responsive">
                    <table class="table table-bordered table-striped table-hover align-middle nowrap" id="tblHistorial"
                        style="width: 100%;">
                        <thead>
                            <tr>
                                <th></th>
                                <th>Cliente</th>
                                <th>Ip Usuario</th>
                                <th>Repetidora</th>
                                <th>Ap</th>
                                <th>Deuda Total</th>
                                <th>Abonos del mes</th>
                                <th>Mensual</th>
                                <th>Telefono Cliente</th>
                                <th>Comentario</th>
                                <th>Tributario</th>

                            </tr>
                        </thead>

                        <tbody>
                        </tbody>
                    </table>
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
                </div>
            </div>
            <div class=" tab-pane fade p-3" id="nav-contratos" role="tabpanel" aria-labelledby="nav-contratos-tab"
                tabindex="0">
                <h5 class="card-title text-center"><i class="fas fa-list-alt"></i> Nuevo Contrato</h5>
                <hr>
                <div class="row justify-content-between">

                    <input type="hidden" id="id" name="id">
                    <div class="col-md-12">
                        <div class="form-group">
                            <label for="idZonas">Zona <span class="text-danger">*</span></label>
                            <select id="idZonas" class="form-control" name="idZonas" onchange="seleccionarip()">
                                <option value="">Seleccionar</option>
                                <?php foreach ($data['zonas'] as $zona) {

                                ?>
                                <option value="<?php echo $zona['id']; ?>">
                                    <?php echo $zona['descripcion']; ?> </option>
                                <?php } ?>
                            </select>
                        </div>

                    </div>

                    <div class="col-md-4">
                        <label>Ip Usuario <span class="text-danger">*</span></label>
                        <div class="input-group mb-4">
                            <span class="input-group-text"><i class="fas fa-phone"></i></span>
                            <input type="hidden" name="origenIp" id="origenIp">
                            <input type="hidden" name="idIpAnuladas" id="idIpAnuladas">
                            <input type="hidden" name="idIp" id="idIp">
                            <input class="form-control" type="text" id="ipUsuario" placeholder="Ip Usuario">
                        </div>

                    </div>

                    <div class="col-md-4">
                        <div class="form-group">
                            <label for="repetidora">Repetidoras <span class="text-danger">*</span></label>
                            <select id="repetidora" class="form-control" name="repetidora" onchange="seleccionaripRepetidora()">
                                <option value="">Seleccionar</option>
                                <?php foreach ($data['repetidoras'] as $repetidoras) {

                                ?>
                                <option value="<?php echo $repetidoras['ssid']; ?>">
                                    <?php echo $repetidoras['ssid']; ?> </option>
                                <?php } ?>
                            </select>
                        </div>




                    </div>


                    <!--- <div class="col-md-4">

                        <label>Repetidora <span class="text-danger">*</span></label>
                        <div class="input-group mb-4">
                            <span class="input-group-text"><i class="fas fa-phone"></i></span>
                            <input class="form-control" type="text" id="repetidora" placeholder="Repetidora">
                        </div>
                        <span id="errorRepetidora" class="text-danger"></span>

                    </div>--->
                    <div class="col-md-4">

                        <label>Ap <span class="text-danger">*</span></label>
                        <div class="input-group mb-4">
                            <span class="input-group-text"><i class="fas fa-phone"></i></span>
                            <input class="form-control" type="text" id="ap" placeholder="Ap">
                        </div>
                        <span id="errorAp" class="text-danger"></span>

                    </div>
                    <div class="col-md-3">

                        <label>Coordenada <span class="text-danger">*</span></label>
                        <div class="input-group mb-4">
                            <span class="input-group-text"><i class="fas fa-phone"></i></span>
                            <input class="form-control" type="text" id="coordenada" placeholder="Coordenada">
                        </div>
                        <span id="errorCoordenada" class="text-danger"></span>

                    </div>

                    <div class="col-md-3">

                        <label>Dirección <span class="text-danger">*</span></label>
                        <div class="input-group mb-2">
                            <span class="input-group-text"><i class="fas fa-dollar-sign"></i></span>
                            <input class="form-control" type="text" id="direccion" placeholder="Direcion">
                        </div>
                        <span id="errorDireccion" class="text-danger"></span>

                    </div>
                     <div class="col-md-3">

                        <label>Ciudad <span class="text-danger">*</span></label>
                        <div class="input-group mb-2">
                            <span class="input-group-text"><i class="fas fa-dollar-sign"></i></span>
                            <input class="form-control" type="text" id="ciudad" placeholder="Ciudad">
                        </div>
                        <span id="errorCiudad" class="text-danger"></span>

                    </div>
                    <div class="col-md-3">

                        <label>Comentario</label>
                        <div class="input-group mb-2">
                            <span class="input-group-text"><i class="fas fa-dollar-sign"></i></span>
                            <input class="form-control" type="text" id="comentario" placeholder="Comentario">
                        </div>
                    </div>
                    <div class="col-md-2 mb-">
                        <div class="form-group">
                            <label for="medio">Medio<span class="text-danger">*</span></label>
                            <select id="medio" class="form-control" name="medio">
                                <option value="">SELECCIONAR</option>
                                <option value="INALAMBRICO">INALAMBRICO</option>
                                <option value="FIBRA">FIBRA</option>
                            </select>
                        </div>
                        <span id="errorMedio" class="text-danger"></span>
                    </div>
                    <div class="col-md-2 mb-3">
                        <div class="form-group">
                            <label for="comparticion">Compartición<span class="text-danger">*</span></label>
                            <select id="comparticion" class="form-control" name="comparticion">
                                <option value="">SELECCIONAR</option>
                                <option value="1:1">1:1</option>
                                <option value="2:1">2:1</option>
                                <option value="4:1">4:1</option>
                                <option value="8:1">8:1</option>
                            </select>
                        </div>
                        <span id="errorComparticion" class="text-danger"></span>
                    </div>
                    <div class="col-md-2">

                        <label>Ancho de Banda</label>
                        <div class="input-group mb-2">
                            <span class="input-group-text"><i class="fas fa-dollar-sign"></i></span>
                            <input class="form-control" type="text" id="anchoBanda" placeholder="Ancho de Banda">
                        </div>
                        <span id="errorAnchoBanda" class="text-danger"></span>

                    </div>
                    <div class="col-md-2 mb-3">
                        <div class="form-group">
                            <label for="discapacidad">Discapacidad</label>
                            <select id="discapacidad" class="form-control" name="tipoBanco">
                                <option value="NO">NO</option>
                                <option value="SI">SI</option>
                            </select>
                        </div>
                    </div>
                    <div class="col-md-2 mb-3">
                        <div class="form-group">
                            <label for="idMikrotik">Mikrotik <span class="text-danger">*</span></label>
                            <select id="idMikrotik" class="form-control" name="idMikrotik">
                                <option value="">Seleccionar</option>
                                <?php foreach ($data['mikrotiks'] as $mikrotik) {

                                ?>
                                <option value="<?php echo $mikrotik['id']; ?>">
                                    <?php echo $mikrotik['nombre']; ?> </option>
                                <?php } ?>
                            </select>
                        </div>




                    </div>

                    <div class="col-lg-2 col-sm-2 mb-2" style="display: flex;">
                        <div class="form-check form-switch" style="margin: auto;">
                            <input class="form-check-input" type="checkbox" role="switch" id="chelectronica"
                                name="chelectronica" value="0">
                            <label class="form-check-label" for="flexSwitchCheckChecked">Facturacion Electrónica</label>
                        </div>


                    </div>
                </div>
                <div class="row mb-2">
                    <div class="col-md-6">
                        <div class="btn-group btn-group-toggle mb-2" data-toggle="buttons">
                            <label class="btn btn-primary">
                                <input type="radio" id="barcode" checked name="buscarProducto"><i
                                    style="padding-left:5px;" class="fas fa-barcode"></i> Servisios
                            </label>
                            <label class="btn btn-info text-white">
                                <input type="radio" id="nombre" name="buscarProducto"><i style="padding-left:5px;"
                                    class="fas fa-list"></i> Productos
                            </label>
                        </div>
                    </div>

                </div>
                <div class="col-md-6" style="margin: auto; display:none;">
                    <div id="containerBuscador " class="btn-group btn-group-toggle mb-2" data-toggle="buttons">
                        <label class="btn btn-primary">
                            <input type="radio" id="renta" name="buscarProducto"><i style="padding-left: 5px;"
                                class="fas fa-barcode"></i> Renta
                        </label>

                    </div>
                </div>
                <div class="input-group mb-2" id="containerRenta" style="margin: auto; display:none;">
                    <span class="input-group-text"><i class="fas fa-search"></i></span>
                    <input class="form-control" type="text" id="buscarRenta" placeholder="Buscar Renta"
                        autocomplete="off">
                </div>
                <div class="col-md-6" style="margin: auto; display:none;">
                    <div class=" form-group btn-group btn-group-toggle " data-toggle="buttons">
                        <label class="btn btn-info text-white">
                            <input type="radio" id="nombreTipoPago" name="buscarProducto"><i style="padding-left:5px;"
                                class="fas fa-list"></i> Tipo Pago
                        </label>
                    </div>
                </div>

                <!-- input para buscar codigo -->
                <div class="input-group mb-2" id="containerCodigo">
                    <span class="input-group-text"><i class="fas fa-search"></i></span>
                    <input class="form-control" type="text" id="buscarProductoNombre" placeholder="Buscar Servicio"
                        autocomplete="off">
                </div>

                <!-- input para buscar nombre -->
                <div class="input-group d-none mb-2" id="containerNombre">
                    <span class="input-group-text"><i class="fas fa-search"></i></span>
                    <input class="form-control" type="text" id="buscarProductoCodigo" placeholder="Buscar Producto"
                        autocomplete="off">
                </div>


                <!-- input para buscar nombre -->
                <div class="input-group d-none mb-2" id="containerNombreTipoPago" style="display:none;">
                    <span class="input-group-text"><i class="fas fa-search"></i></span>
                    <input class="form-control" type="text" id="buscarTipoPagoNombre" placeholder="Buscar Tipo Pagos"
                        autocomplete="off">
                </div>
                <!-- table productos -->
                <div class="input-group mb-2" id="containerRenta" style="display:none;">
                    <span class="input-group-text"><i class="fas fa-search"></i></span>
                    <input class="form-control" type="text" id="buscarRenta" placeholder="Buscar Renta"
                        autocomplete="off">
                </div>
                <div class="table-responsive">
                    <table class="table table-bordered table-striped table-hover align-middle" id="tblNuevoContrato"
                        style="width: 100%;">
                        <thead>
                            <tr>
                                <th>Producto</th>
                                <th>Precio</th>
                                <th>SubTotal</th>
                                <th></th>
                            </tr>
                        </thead>
                        <tbody>
                        </tbody>
                    </table>
                </div>

                <hr>

                <div class="row justify-content-between">
                    <div class="col-md-4">

                        <?php if ($data['clienteNuevo'] > 0) { ?>


                        <div>
                            <label>Buscar Cliente</label>
                            <div class="input-group mb-2">
                                <input type="hidden" id="idCliente" value="<?= $data['clienteNuevo']['id'] ?>">
                                <span class="input-group-text"><i class="fas fa-search"></i></span>
                                <input class="form-control" type="text" id="buscarCliente" placeholder="Buscar Cliente"
                                    value="<?= $data['clienteNuevo']['nombre'] ?>">
                            </div>
                            <span class="text-danger fw-bold mb-2" id="errorCliente"></span>
                        </div>

                        <label>Telefono</label>
                        <div class="input-group mb-2">
                            <span class="input-group-text"><i class="fas fa-phone"></i></span>
                            <input class="form-control" type="text" id="telefonoCliente" placeholder="Telefono"
                                value="<?= $data['clienteNuevo']['telefono'] ?>" disabled>
                        </div>
                        <label>Direccion</label>
                        <div class="input-group mb-2">
                            <span class="input-group-text"><i class="fa-solid fa-envelope"></i></span>
                            <input class="form-control" type="text" id="direccionCliente" placeholder="Dirección"
                                value="<?= $data['clienteNuevo']['direccion'] ?>" disabled>
                        </div>




                        <?php } else {  ?>

                        <div>
                            <label>Buscar Cliente</label>
                            <div class="input-group mb-2">
                                <input type="hidden" id="idCliente">
                                <span class="input-group-text"><i class="fas fa-search"></i></span>
                                <input class="form-control" type="text" id="buscarCliente" placeholder="Buscar Cliente">
                            </div>
                            <span class="text-danger fw-bold mb-2" id="errorCliente"></span>
                        </div>
                        <label>Telefono</label>
                        <div class="input-group mb-2">
                            <span class="input-group-text"><i class="fas fa-phone"></i></span>
                            <input class="form-control" type="text" id="telefonoCliente" placeholder="Telefono"
                                disabled>
                        </div>

                        <label>Direccion</label>
                        <div class="input-group mb-2">
                            <span class="input-group-text"><i class="fa-solid fa-envelope"></i></span>
                            <input class="form-control" type="text" id="direccionCliente" placeholder="Dirección"
                                disabled>
                        </div>
                        <?php } ?>


                    </div>

                    <div class="col-md-4">
                        <label>Vendedor</label>
                        <div class="input-group mb-2">
                            <span class="input-group-text"><i class="fas fa-user"></i></span>
                            <input class="form-control" type="text" value="<?php echo $_SESSION['nombre_usuario']; ?>"
                                placeholder="Vendedor" disabled>
                        </div>


                        <label>Total a Pagar</label>
                        <div class="input-group mb-2">
                            <span class="input-group-text"><i class="fas fa-dollar-sign"></i></span>
                            <input class="form-control" type="text" id="totalPagar" placeholder="Total Pagar" disabled>
                        </div>
                        <div class="d-grid">
                            <button class="btn btn-primary" type="button" id="btnAccion">Completar</button>
                        </div>
                    </div>


                </div>
            </div>
            <div class="tab-pane fade p-3" id="nav-cargar" role="tabpanel">
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


            <div class="tab-pane fade p-3" id="nav-contratosSuspender" role="tabpanel"
                aria-labelledby="nav-contratosSuspender-tab" tabindex="0">

                <div class="table-responsive">
                    <table class="table table-bordered table-striped table-hover align-middle nowrap"
                        id="tblcontratosSuspender" style="width: 100%;">
                        <thead>
                            <tr>
                                <th></th>
                                <th>Cliente</th>
                                <th>Deuda Total</th>
                                <th>Ip Usuario</th>
                                <th>Telefono Cliente</th>

                            </tr>
                        </thead>

                        <tbody>
                        </tbody>
                    </table>
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
                </div>
            </div>

        </div>
    </div>
</div>

<div id="modalFacturarContratos" class="modal fade" role="dialog" aria-labelledby="my-modal-title" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Facturar Contratos</h5>
                <button class="btn-close" data-bs-dismiss="modal" aria-label="Close">
                </button>
            </div>
            <div class="modal-body">
                <div class="row">
                    <div class="col-md-12">
                        <label>Buscar Cliente</label>
                        <div class="input-group mb-2">
                            <input type="hidden" id="idContrato">
                            <span class="input-group-text"><i class="fas fa-search"></i></span>
                            <input class="form-control" type="text" id="buscarContrato" placeholder="Buscar Contrato">

                        </div>
                        <span class="text-danger fw-bold" id="errorBuscarContrato"></span>
                    </div>
                    <div class="col-md-12">
                        <label>Dirección Contrato</label>
                        <div class="input-group mb-2">
                            <span class="input-group-text"><i class="fas fa-phone"></i></span>
                            <input class="form-control" type="text" id="direccionContrato"
                                placeholder="Dirección Contrato" disabled>
                        </div>
                    </div>

                    <div class="col-md-12 mb-2">
                        <label class="form-label fw-semibold mb-2">Meses a facturar</label>
                        <div class="meses-grid" id="containerMeses">
                            <label class="mes-chip"><input type="checkbox" name="chEnero"      id="chEnero"      value="0" onclick="calcularEnero();"><span>ENERO</span></label>
                            <label class="mes-chip"><input type="checkbox" name="chFebrero"    id="chFebrero"    value="0" onclick="calcularFebrero();"><span>FEBRERO</span></label>
                            <label class="mes-chip"><input type="checkbox" name="chMarzo"      id="chMarzo"      value="0" onclick="calcularMarzo();"><span>MARZO</span></label>
                            <label class="mes-chip"><input type="checkbox" name="chAbril"      id="chAbril"      value="0" onclick="calcularAbril();"><span>ABRIL</span></label>
                            <label class="mes-chip"><input type="checkbox" name="chMayo"       id="chMayo"       value="0" onclick="calcularMayo();"><span>MAYO</span></label>
                            <label class="mes-chip"><input type="checkbox" name="chJunio"      id="chJunio"      value="0" onclick="calcularJunio();"><span>JUNIO</span></label>
                            <label class="mes-chip"><input type="checkbox" name="chJulio"      id="chJulio"      value="0" onclick="calcularJulio();"><span>JULIO</span></label>
                            <label class="mes-chip"><input type="checkbox" name="chAgosto"     id="chAgosto"     value="0" onclick="calcularAgosto();"><span>AGOSTO</span></label>
                            <label class="mes-chip"><input type="checkbox" name="chSeptiembre" id="chSeptiembre" value="0" onclick="calcularSeptiembre();"><span>SEPTIEMBRE</span></label>
                            <label class="mes-chip"><input type="checkbox" name="chOctubre"    id="chOctubre"    value="0" onclick="calcularOctubre();"><span>OCTUBRE</span></label>
                            <label class="mes-chip"><input type="checkbox" name="chNoviembre"  id="chNoviembre"  value="0" onclick="calcularNoviembre();"><span>NOVIEMBRE</span></label>
                            <label class="mes-chip"><input type="checkbox" name="chDiciembre"  id="chDiciembre"  value="0" onclick="calcularDiciembre();"><span>DICIEMBRE</span></label>
                        </div>
                    </div>


                    <div class="col-md-4 mb-2">
                        <label>Valor Contrato</label>
                        <div class="input-group">
                            <span class="input-group-text"><i class="fas fa-dollar-sign"></i></span>
                            <input class="form-control" type="text" id="valorContrato" readonly>
                        </div>
                    </div>
                    <div class="col-md-4 mb-2">
                        <label>Valor a Facturar</label>
                        <div class="input-group">
                            <span class="input-group-text"><i class="fas fa-dollar-sign"></i></span>
                            <input class="form-control" type="text" id="valorFacturar" readonly>
                        </div>
                    </div>
                    <div class="form-group mb-2">
                        <label for="tipopago">Metodo</label>
                        <select id="tipopago" class="form-control" name="tipopago">
                            <?php foreach ($data['tipoPago'] as $tipoPago) {               ?>

                            <option value="<?php echo $tipoPago['nombre']; ?>"><?php echo $tipoPago['nombre']; ?>
                            </option>
                            <?php  }?>
                        </select>
                    </div>


                </div>
                <div class="d-grid">
                    <button class="btn btn-primary" type="button" id="btnFacturarContratos">Facturar</button>
                </div>
            </div>
        </div>
    </div>
</div>

<div id="modalVerVista" class="modal fade" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content border-0 shadow-lg">
            <div class="modal-header text-white" style="background: linear-gradient(135deg,#2563eb,#1e40af); border-radius: calc(0.5rem - 1px) calc(0.5rem - 1px) 0 0;">
                <div class="d-flex align-items-center gap-2">
                    <i class="bx bx-file fs-4"></i>
                    <div>
                        <h5 class="modal-title mb-0">Vista Contrato</h5>
                        <small class="opacity-75">ID <span id="modalId">—</span> · <span id="modalFecha">—</span></small>
                    </div>
                </div>
                <button class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body p-4">

                <!-- Cliente + Servicio -->
                <div class="cv-section">
                    <div class="cv-section-title"><i class="bx bx-user-circle"></i>Cliente &amp; Servicio</div>
                    <div class="row g-3">
                        <div class="col-md-6">
                            <div class="cv-field">
                                <div class="cv-label"><i class="bx bx-user"></i>Cliente</div>
                                <div class="cv-value fw-semibold" id="modalCliente">—</div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="cv-field">
                                <div class="cv-label"><i class="bx bx-package"></i>Servicio</div>
                                <div class="cv-value" id="modalServicio">—</div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="cv-field">
                                <div class="cv-label"><i class="bx bx-dollar"></i>Valor del servicio</div>
                                <div class="cv-value text-primary fw-bold" id="modalValor">0</div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="cv-field">
                                <div class="cv-label"><i class="bx bx-shuffle"></i>Medio</div>
                                <div class="cv-value" id="modalMedio">—</div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Red -->
                <div class="cv-section">
                    <div class="cv-section-title"><i class="bx bx-wifi"></i>Configuración de red</div>
                    <div class="row g-3">
                        <div class="col-md-6">
                            <div class="cv-field">
                                <div class="cv-label"><i class="bx bx-desktop"></i>IP Usuario</div>
                                <div class="cv-value"><code class="cv-ip" id="modalIpUsuario">—</code></div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="cv-field">
                                <div class="cv-label"><i class="bx bx-broadcast"></i>Repetidora</div>
                                <div class="cv-value" id="modalRepetidora">—</div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="cv-field">
                                <div class="cv-label"><i class="bx bx-network-chart"></i>AP</div>
                                <div class="cv-value" id="modalAp">—</div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="cv-field">
                                <div class="cv-label"><i class="bx bx-share-alt"></i>Tipo Compartición</div>
                                <div class="cv-value" id="modalComparticion">—</div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Ubicación -->
                <div class="cv-section">
                    <div class="cv-section-title"><i class="bx bx-map"></i>Ubicación</div>
                    <div class="row g-3">
                        <div class="col-md-6">
                            <div class="cv-field">
                                <div class="cv-label"><i class="bx bx-map-pin"></i>Dirección</div>
                                <div class="cv-value" id="modalDireccionContrato">—</div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="cv-field">
                                <div class="cv-label"><i class="bx bx-current-location"></i>Coordenadas</div>
                                <div class="cv-value d-flex align-items-center gap-2">
                                    <code id="modalCoordenada" style="font-size:12px">—</code>
                                    <a id="modalMapLink" href="#" target="_blank" class="cv-map-btn" title="Abrir en Google Maps" style="display:none;"><i class="bx bx-map"></i></a>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Facturación -->
                <div class="cv-section">
                    <div class="cv-section-title"><i class="bx bx-receipt"></i>Facturación &amp; Banco</div>
                    <div class="row g-3">
                        <div class="col-md-4">
                            <div class="cv-field">
                                <div class="cv-label"><i class="bx bx-purchase-tag"></i>Tributario</div>
                                <div class="cv-value"><span class="badge bg-light text-dark border" id="modalTributario">—</span></div>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="cv-field">
                                <div class="cv-label"><i class="bx bx-bank"></i>Tipo Banco</div>
                                <div class="cv-value" id="modalTipoBanco">—</div>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="cv-field">
                                <div class="cv-label"><i class="bx bx-credit-card-alt"></i>Cuenta</div>
                                <div class="cv-value" id="modalCuentaBanco">—</div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Comentario -->
                <div class="cv-section" id="cvComentarioWrap">
                    <div class="cv-section-title"><i class="bx bx-comment-detail"></i>Comentario</div>
                    <div class="cv-field">
                        <div class="cv-value cv-comment" id="modalComentario">—</div>
                    </div>
                </div>

                <!-- Deuda -->
                <div class="cv-section cv-deuda-section">
                    <div class="cv-section-title"><i class="bx bx-dollar-circle"></i>Estado de deuda</div>
                    <div class="row g-3">
                        <div class="col-md-6">
                            <div class="cv-deuda-card cv-deuda-contrato">
                                <small class="text-muted">Deuda del contrato</small>
                                <h4 class="mb-0" id="modalDeudaContrato">0</h4>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="cv-deuda-card cv-deuda-total">
                                <small class="text-muted">Deuda total acumulada</small>
                                <h4 class="mb-0" id="modalDeudaTotal">0</h4>
                            </div>
                        </div>
                    </div>
                </div>

            </div>
            <div class="modal-footer bg-light">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal"><i class="bx bx-x"></i> Cerrar</button>
            </div>
        </div>
    </div>
</div>

<style>
/* === Modal Vista Contrato === */
#modalVerVista .cv-section {
    margin-bottom: 22px;
    padding-bottom: 18px;
    border-bottom: 1px dashed rgba(0,0,0,.08);
}
#modalVerVista .cv-section:last-child {
    border-bottom: 0;
    margin-bottom: 0;
    padding-bottom: 0;
}
#modalVerVista .cv-section-title {
    display: flex;
    align-items: center;
    gap: 8px;
    font-size: 12px;
    font-weight: 700;
    text-transform: uppercase;
    letter-spacing: 0.5px;
    color: #2563eb;
    margin-bottom: 12px;
}
#modalVerVista .cv-section-title i { font-size: 18px; }
#modalVerVista .cv-field {
    display: flex;
    flex-direction: column;
    gap: 2px;
}
#modalVerVista .cv-label {
    display: flex;
    align-items: center;
    gap: 6px;
    font-size: 11px;
    color: #6b7280;
    text-transform: uppercase;
    letter-spacing: .5px;
    font-weight: 600;
}
#modalVerVista .cv-label i { font-size: 14px; color: #9ca3af; }
#modalVerVista .cv-value {
    font-size: 14px;
    color: #111827;
    min-height: 22px;
}
#modalVerVista .cv-value:empty::before,
#modalVerVista .cv-value:has(> span:empty)::after {
    content: "—";
    color: #9ca3af;
}
#modalVerVista .cv-ip {
    background: #eef2ff;
    color: #4338ca;
    padding: 3px 8px;
    border-radius: 6px;
    font-size: 13px;
}
#modalVerVista .cv-map-btn {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    width: 28px; height: 28px;
    background: #10b981;
    color: #fff;
    border-radius: 6px;
    text-decoration: none;
    transition: transform .1s;
}
#modalVerVista .cv-map-btn:hover { transform: scale(1.05); background: #059669; color: #fff; }
#modalVerVista .cv-comment {
    background: #fef3c7;
    border-left: 3px solid #f59e0b;
    padding: 10px 12px;
    border-radius: 4px;
    font-style: italic;
    color: #78350f;
    min-height: auto;
}
#modalVerVista .cv-deuda-card {
    padding: 14px 18px;
    border-radius: 12px;
    text-align: center;
    border: 1px solid #e5e7eb;
}
#modalVerVista .cv-deuda-contrato { background: linear-gradient(135deg,#fef3c7,#fde68a); }
#modalVerVista .cv-deuda-contrato h4 { color: #b45309; }
#modalVerVista .cv-deuda-total    { background: linear-gradient(135deg,#fee2e2,#fecaca); }
#modalVerVista .cv-deuda-total h4 { color: #991b1b; }

#modalVerVista .modal-content { border-radius: 10px; overflow: hidden; }
</style>

<script>
// Al abrir el modal, armar el link de Google Maps con las coordenadas
(function(){
  const mapBtn = document.getElementById('modalMapLink');
  const coord = document.getElementById('modalCoordenada');
  if (!mapBtn || !coord) return;
  const obs = new MutationObserver(() => {
    const txt = (coord.textContent || '').trim();
    if (txt && txt !== '—' && /[\-0-9.]+/.test(txt)) {
      mapBtn.href = 'https://www.google.com/maps?q=' + encodeURIComponent(txt);
      mapBtn.style.display = 'inline-flex';
    } else {
      mapBtn.style.display = 'none';
    }
  });
  obs.observe(coord, { childList: true, characterData: true, subtree: true });
})();
</script>


<!-- Modal de Bootstrap -->
<div class="modal fade" id="modalUpdateComentario" role="dialog" tabindex="-1" aria-labelledby="miModalLabel"
    aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="miModalLabel">Actualizar Comentario Contrato</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <p id="contenidoModal">Comentario</p>

                <input type="text" class="form-control" name="updateComentarioContrato" id="updateComentarioContrato">
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cerrar</button>
                <button type="button" class="btn btn-primary" id="enviarDatos">Actualizar</button>
            </div>
        </div>
    </div>
</div>

<?php include_once 'views/templates/footer.php'; ?>

<style>
/* ===== Contratos page refresh ===== */
.nav-tabs-modern {
  border-bottom: 2px solid #e5e7eb;
  gap: 4px;
  padding: 0 4px;
}
.nav-tabs-modern .nav-link {
  border: 0;
  color: #6b7280;
  font-weight: 500;
  padding: 12px 16px;
  border-radius: 8px 8px 0 0;
  transition: background .15s, color .15s;
  position: relative;
}
.nav-tabs-modern .nav-link:hover { background: #f3f5fa; color: #2563eb; }
.nav-tabs-modern .nav-link.active {
  background: transparent;
  color: #2563eb;
  font-weight: 600;
}
.nav-tabs-modern .nav-link.active::after {
  content: '';
  position: absolute;
  left: 0; right: 0; bottom: -2px;
  height: 3px;
  background: #2563eb;
  border-radius: 2px 2px 0 0;
}

/* Selector "Mostrar N registros" visible y bonito */
.dataTables_wrapper .dataTables_length {
  margin-bottom: 10px;
}
.dataTables_wrapper .dataTables_length label {
  display: flex;
  align-items: center;
  gap: 6px;
  color: #4b5563;
  font-weight: 500;
}
.dataTables_wrapper .dataTables_length select,
.dataTables_length select {
  width: auto !important;
  min-width: 70px;
  padding: 4px 24px 4px 10px !important;
  border: 1px solid #d1d5db !important;
  border-radius: 6px !important;
  background: #fff !important;
  color: #111827 !important;
  font-size: 13px !important;
  font-weight: 500;
  appearance: auto;
  height: 30px;
}
.dataTables_wrapper .dataTables_filter {
  margin-bottom: 10px;
}
.dataTables_wrapper .dataTables_filter label {
  display: flex;
  align-items: center;
  gap: 6px;
  color: #4b5563;
  font-weight: 500;
}
.dataTables_wrapper .dataTables_filter input {
  border-radius: 20px !important;
  border: 1px solid #d1d5db !important;
  padding: 4px 14px 4px 30px !important;
  min-width: 220px;
  height: 30px;
  background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='14' height='14' fill='%23999' viewBox='0 0 16 16'%3E%3Cpath d='M11.742 10.344a6.5 6.5 0 1 0-1.397 1.398h-.001c.03.04.062.078.098.115l3.85 3.85a1 1 0 0 0 1.415-1.414l-3.85-3.85a1.007 1.007 0 0 0-.115-.1zM12 6.5a5.5 5.5 0 1 1-11 0 5.5 5.5 0 0 1 11 0z'/%3E%3C/svg%3E");
  background-repeat: no-repeat;
  background-position: 10px center;
}

/* Tabla */
#tblHistorial thead th,
#tblcontratosSuspender thead th,
#tblCargar thead th {
  background: #f9fafb;
  color: #374151;
  font-weight: 600;
  text-transform: uppercase;
  font-size: 11px;
  letter-spacing: .4px;
  border-bottom: 2px solid #e5e7eb;
  padding: 10px 8px;
}
#tblHistorial tbody tr:hover,
#tblcontratosSuspender tbody tr:hover,
#tblCargar tbody tr:hover { background: #f9fafb; }

/* DataTable info & pagination */
.dataTables_wrapper .dataTables_info { color: #6b7280; font-size: 12px; }
.dataTables_wrapper .paginate_button {
  border-radius: 6px !important;
  margin: 0 2px !important;
  border: 1px solid #e5e7eb !important;
  color: #4b5563 !important;
}
.dataTables_wrapper .paginate_button.current,
.dataTables_wrapper .paginate_button.current:hover {
  background: #2563eb !important;
  border-color: #2563eb !important;
  color: #fff !important;
}
.dataTables_wrapper .paginate_button:hover {
  background: #f3f5fa !important;
  color: #2563eb !important;
}

.page-header h4 { color: #111827; }


/* === Action cells === */
.action-cell { display: inline-flex; gap: 4px; align-items: center; }
.action-btn {
    width: 30px; height: 30px;
    border: 0;
    border-radius: 7px;
    display: inline-flex; align-items: center; justify-content: center;
    cursor: pointer;
    font-size: 16px;
    transition: background .12s, transform .1s, box-shadow .12s;
    background: #f3f4f6;
    color: #4b5563;
    padding: 0;
}
.action-btn:hover { transform: translateY(-1px); box-shadow: 0 2px 6px rgba(0,0,0,.1); }
.action-btn.action-view    { background: #dbeafe; color: #1d4ed8; }
.action-btn.action-view:hover { background: #2563eb; color: #fff; }
.action-btn.action-edit    { background: #fef3c7; color: #b45309; }
.action-btn.action-edit:hover { background: #f59e0b; color: #fff; }
.action-btn.action-comment { background: #dcfce7; color: #166534; }
.action-btn.action-comment:hover { background: #22c55e; color: #fff; }
.action-btn.action-cobro   { background: #cffafe; color: #0e7490; }
.action-btn.action-cobro:hover { background: #06b6d4; color: #fff; }
.action-btn.action-suspend { background: #fee2e2; color: #b91c1c; }
.action-btn.action-suspend:hover { background: #ef4444; color: #fff; }
.action-btn.action-activate { background: #dcfce7; color: #166534; }
.action-btn.action-activate:hover { background: #16a34a; color: #fff; }


.action-btn.action-more    { background: #e5e7eb; color: #4b5563; }
.action-btn.action-more:hover { background: #6b7280; color: #fff; }
.dropdown-menu .dropdown-item i { font-size: 16px; vertical-align: middle; }

/* Deuda badge */
.deuda-badge {
    display: inline-block;
    padding: 3px 10px;
    border-radius: 12px;
    font-weight: 600;
    font-size: 12px;
    min-width: 70px;
    text-align: center;
}
.deuda-cero { background: #f3f4f6 !important; color: #6b7280 !important; }
.deuda-pendiente { background: #fee2e2; color: #b91c1c; }
.deuda-ok { background: #dcfce7; color: #166534; }

/* IP link chip */
.ip-link {
    display: inline-block;
    background: #eff6ff;
    color: #1d4ed8 !important;
    padding: 2px 8px;
    border-radius: 6px;
    font-family: ui-monospace, monospace;
    font-size: 12px;
    text-decoration: none !important;
}
.ip-link:hover { background: #dbeafe; text-decoration: none !important; }

/* Badge subtle */
.bg-success-subtle { background: rgba(34,197,94,.12) !important; }
.text-success { color: #16a34a !important; }
.bg-warning-subtle { background: rgba(245,158,11,.15) !important; }
.text-warning { color: #d97706 !important; }

/* ===== Modal Facturar Contratos: grid de meses ===== */
.meses-grid {
    display: grid;
    grid-template-columns: repeat(3, 1fr);
    gap: 8px;
    padding: 12px;
    border: 1px solid #e5e7eb;
    border-radius: 8px;
    background: #f9fafb;
}
.mes-chip {
    display: flex;
    align-items: center;
    gap: 8px;
    padding: 8px 10px;
    border: 1px solid #e5e7eb;
    border-radius: 6px;
    background: #fff;
    cursor: pointer;
    font-size: 13px;
    font-weight: 500;
    color: #111827;
    user-select: none;
    transition: all .12s;
    margin: 0;
}
.mes-chip:hover { border-color: #2563eb; background: #eff6ff; }
.mes-chip input[type="checkbox"] {
    width: 16px;
    height: 16px;
    flex-shrink: 0;
    cursor: pointer;
    accent-color: #2563eb;
    margin: 0;
}
.mes-chip input[type="checkbox"]:checked + span { color: #2563eb; font-weight: 600; }
.mes-chip:has(input:checked) { border-color: #2563eb; background: #dbeafe; }
.mes-chip span { line-height: 1; }

@media (max-width: 480px) {
    .meses-grid { grid-template-columns: repeat(2, 1fr); }
}

</style>


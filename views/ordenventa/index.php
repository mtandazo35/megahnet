<?php include_once 'views/templates/header.php'; ?>
<div class="page-header d-flex justify-content-between align-items-center mb-3 flex-wrap gap-2" id="page-header-modern">
  <div>
    <h4 class="mb-0 fw-semibold"><i class="bx bx-package text-primary me-1"></i>Órdenes de Venta</h4>
    <small class="text-muted">Gestión de órdenes de venta y facturación</small>
  </div>
</div>


<div class="card">
    <div class="card-body">
        <nav>
            <div class="nav nav-tabs" id="nav-tab" role="tablist">
                <button class="nav-link active" id="nav-orden_venta-tab" data-bs-toggle="tab" data-bs-target="#nav-orden_venta" type="button" role="tab" aria-controls="nav-orden_venta" aria-selected="true">Orden Venta</button>
                <button class="nav-link" id="nav-cargar-tab" data-bs-toggle="tab" data-bs-target="#nav-cargar" type="button" role="tab" aria-controls="nav-cargar" aria-selected="false">Cargar Orden Venta</button>

                <button class="nav-link" id="nav-historial-tab" data-bs-toggle="tab" data-bs-target="#nav-historial" type="button" role="tab" aria-controls="nav-historial" aria-selected="false">Historial</button>
            </div>
        </nav>
        <div class="tab-content" id="nav-tabContent">
            <div class="tab-pane fade show active p-3" id="nav-orden_venta" role="tabpanel" aria-labelledby="nav-orden_venta-tab" tabindex="0">
                <h5 class="card-title text-center"><i class="fas fa-list-alt"></i> Nueva Orden Venta</h5>
                <hr>
                <div class="row mb-2">
                    <div class="col-md-6">
                        <div class="btn-group btn-group-toggle mb-2" data-toggle="buttons">
                            <label class="btn btn-primary">
                                <input type="radio" id="barcode" checked name="buscarProducto"><i style="padding-left:5px;" class="fas fa-barcode"></i> Servicios
                            </label>
                            <label class="btn btn-info text-white">
                                <input type="radio" id="nombre" name="buscarProducto"><i style="padding-left:5px;" class="fas fa-list"></i> Productos
                            </label>
                        </div>
                    </div>

                </div>
                <div class="col-md-6" style="margin: auto; display:none;">
                            <div id="containerBuscador " class="btn-group btn-group-toggle mb-2" data-toggle="buttons">
                                <label class="btn btn-primary">
                                    <input type="radio" id="renta"  name="buscarProducto"><i style="padding-left: 5px;" class="fas fa-barcode"></i> Renta
                                </label>
                               
                            </div>
                        </div>
                        <div class="input-group mb-2" id="containerRenta" style="margin: auto; display:none;">
                        <span class="input-group-text"><i class="fas fa-search"></i></span>
                        <input class="form-control" type="text" id="buscarRenta" placeholder="Buscar Renta" autocomplete="off">
                    </div>

                <div class="col-md-6" style="margin: auto; display:none;">
                    <div class=" form-group btn-group btn-group-toggle " data-toggle="buttons">
                        <label class="btn btn-info text-white">
                            <input type="radio" id="nombreTipoPago" name="buscarProducto"><i style="padding-left:5px;" class="fas fa-list"></i> Tipo Pago
                        </label>
                    </div>
                </div>
   <!-- input para buscar codigo -->
   <div class="input-group mb-2" id="containerCodigo">
                    <span class="input-group-text"><i class="fas fa-search"></i></span>
                    <input class="form-control" type="text" id="buscarProductoNombre" placeholder="Buscar Servicio" autocomplete="off">
                </div>

                <!-- input para buscar nombre -->
                <div class="input-group d-none mb-2" id="containerNombre">
                    <span class="input-group-text"><i class="fas fa-search"></i></span>
                    <input class="form-control" type="text" id="buscarProductoCodigo" placeholder="Buscar Producto" autocomplete="off">
                </div>
                <!-- input para buscar nombre -->
                <div class="input-group d-none mb-2" id="containerNombreTipoPago" style="display:none;">
                    <span class="input-group-text"><i class="fas fa-search"></i></span>
                    <input class="form-control" type="text" id="buscarTipoPagoNombre" placeholder="Buscar Tipo Pagos" autocomplete="off">
                </div>
                <!-- table productos -->

                <div class="table-responsive">
                    <table class="table table-bordered table-striped table-hover align-middle" id="tblNuevaOrdenVenta" style="width: 100%;">
                        <thead>
                            <tr>
                                <th>Producto</th>
                                <th>Precio</th>
                                <th>Cantidad</th>
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
                            <input class="form-control" type="text" id="telefonoCliente" placeholder="Telefono" disabled>
                        </div>

                        <label>Dirección</label>
                        <ul class="list-group">
                            <li class="list-group-item" id="direccionCliente"><i class="fas fa-home"></i></li>
                        </ul>
                    </div>

                    <div class="col-md-4">
                        <label>Vendedor</label>
                        <div class="input-group mb-2">
                            <span class="input-group-text"><i class="fas fa-user"></i></span>
                            <input class="form-control" type="text" value="<?php echo $_SESSION['nombre_usuario']; ?>" placeholder="Vendedor" disabled>
                        </div>
                        <label>Descuento</label>
                        <div class="input-group mb-2">
                            <span class="input-group-text"><i class="fas fa-dollar-sign"></i></span>
                            <input class="form-control" type="text" id="descuento" placeholder="Descuento">
                        </div>

                        <label>Total a Pagar</label>
                        <div class="input-group mb-2">
                            <span class="input-group-text"><i class="fas fa-dollar-sign"></i></span>
                            <input class="form-control" type="text" id="totalPagar" placeholder="Total Pagar" disabled>
                        </div>
                    </div>

                    <div class="col-md-4">
                        <div class="form-group mb-2">
                            <label for="metodo">Metodo</label>
                            <select id="metodo" class="form-control">
							                                <option value="CREDITO">CREDITO</option>

                                <option value="CONTADO">CONTADO</option>
                            </select>
                        </div>
                        <div class="form-group mb-2">
                                <label for="tipopago">Metodo</label>
                                <select id="tipopago" class="form-control" name="tipopago">
                                    <?php foreach ($data['tipoPago'] as $tipoPago) {

                                    ?>

                                        <option value="<?php echo $tipoPago['nombre']; ?>"><?php echo $tipoPago['nombre']; ?>
                                        </option>
                                    <?php } ?>
                                </select>
                            </div>
                        <div class="d-grid">
                            <button class="btn btn-primary" type="button" id="btnAccion">Completar</button>
                        </div>
                    </div>
                </div>
            </div>
            <div class="tab-pane fade p-3" id="nav-historial" role="tabpanel" aria-labelledby="nav-historial-tab" tabindex="0">
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
                    <table class="table table-bordered table-striped table-hover align-middle nowrap" id="tblHistorial" style="width: 100%;">
                        <thead>
                            <tr>
							 <th></th>
                            <th>Cliente</th>
                                <th>Fecha</th>
                                <th>Orden Venta</th>
                                <th>Total</th>
                                <th>Metodo</th>
                                <th>Estado</th>

                               
                            </tr>
                        </thead>

                        <tbody>
                        </tbody>
                    </table>
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
        </div>
    </div>
</div>

<?php include_once 'views/templates/footer.php'; ?>
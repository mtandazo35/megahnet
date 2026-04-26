<?php include_once 'views/templates/header.php'; ?>

<div class="card">
    <div class="card-body">
        <nav>
            <div class="nav nav-tabs" id="nav-tab" role="tablist">
                <button class="nav-link active" id="nav-cotizaciones-tab" data-bs-toggle="tab" data-bs-target="#nav-cotizaciones" type="button" role="tab" aria-controls="nav-cotizaciones" aria-selected="true">Cotizaciones</button>
                <button class="nav-link" id="nav-historial-tab" data-bs-toggle="tab" data-bs-target="#nav-historial" type="button" role="tab" aria-controls="nav-historial" aria-selected="false">Historial</button>
            </div>
        </nav>
        <div class="tab-content" id="nav-tabContent">
            <div class="tab-pane fade show active p-3" id="nav-cotizaciones" role="tabpanel" aria-labelledby="nav-cotizaciones-tab" tabindex="0">

                <!-- ============ 1. DATOS DEL CLIENTE ============ -->
                <div class="form-section">
                    <div class="form-section-title"><i class="fas fa-user-circle"></i> Datos del Cliente</div>
                    <div class="row g-2">
                        <div class="col-md-4">
                            <label class="form-section-label">Buscar Cliente <span class="text-danger">*</span></label>
                            <div class="input-group input-group-sm">
                                <input type="hidden" id="idCliente">
                                <span class="input-group-text"><i class="fas fa-search"></i></span>
                                <input class="form-control" type="text" id="buscarCliente" placeholder="Buscar Cliente">
                            </div>
                            <span class="text-danger fw-bold" id="errorCliente"></span>
                        </div>
                        <div class="col-md-4">
                            <label class="form-section-label">Telefono</label>
                            <div class="input-group input-group-sm">
                                <span class="input-group-text"><i class="fas fa-phone"></i></span>
                                <input class="form-control" type="text" id="telefonoCliente" placeholder="Telefono" disabled>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <label class="form-section-label">Direccion</label>
                            <div class="input-group input-group-sm">
                                <span class="input-group-text"><i class="fas fa-home"></i></span>
                                <div class="form-control form-display empty-placeholder" id="direccionCliente" data-placeholder="Direccion"></div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- ============ 2. SERVICIOS / PRODUCTOS ============ -->
                <div class="form-section">
                    <div class="form-section-title"><i class="fas fa-shopping-cart"></i> Servicios y Productos</div>

                    <div class="btn-group btn-group-sm mb-2" data-toggle="buttons">
                        <label class="btn btn-primary">
                            <input type="radio" id="barcode" checked name="buscarProducto"><i style="padding-left:5px;" class="fas fa-barcode"></i> Servicios
                        </label>
                        <label class="btn btn-info text-white">
                            <input type="radio" id="nombre" name="buscarProducto"><i style="padding-left:5px;" class="fas fa-list"></i> Productos
                        </label>
                    </div>

                    <div class="col-md-6" style="margin: auto; display:none;">
                        <div id="containerBuscador" class="btn-group btn-group-toggle mb-2" data-toggle="buttons">
                            <label class="btn btn-primary">
                                <input type="radio" id="renta" name="buscarProducto"><i style="padding-left: 5px;" class="fas fa-barcode"></i> Renta
                            </label>
                        </div>
                    </div>
                    <div class="input-group mb-2" id="containerRenta" style="margin: auto; display:none;">
                        <span class="input-group-text"><i class="fas fa-search"></i></span>
                        <input class="form-control" type="text" id="buscarRenta" placeholder="Buscar Renta" autocomplete="off">
                    </div>
                    <div class="col-md-6" style="margin: auto; display:none;">
                        <div class="form-group btn-group btn-group-toggle" data-toggle="buttons">
                            <label class="btn btn-info text-white">
                                <input type="radio" id="nombreTipoPago" name="buscarProducto"><i style="padding-left:5px;" class="fas fa-list"></i> Tipo Pago
                            </label>
                        </div>
                    </div>
                    <div class="input-group d-none mb-2" id="containerNombreTipoPago" style="display:none;">
                        <span class="input-group-text"><i class="fas fa-search"></i></span>
                        <input class="form-control" type="text" id="buscarTipoPagoNombre" placeholder="Buscar Tipo Pagos" autocomplete="off">
                    </div>

                    <div class="input-group mb-2" id="containerCodigo">
                        <span class="input-group-text"><i class="fas fa-search"></i></span>
                        <input class="form-control" type="text" id="buscarProductoNombre" placeholder="Buscar Servicio" autocomplete="off">
                    </div>
                    <div class="input-group d-none mb-2" id="containerNombre">
                        <span class="input-group-text"><i class="fas fa-search"></i></span>
                        <input class="form-control" type="text" id="buscarProductoCodigo" placeholder="Buscar Producto" autocomplete="off">
                    </div>

                    <div class="table-responsive">
                        <table class="table table-bordered table-striped table-hover align-middle" id="tblNuevaCotizacion" style="width: 100%;">
                            <thead>
                                <tr>
                                    <th>Producto</th>
                                    <th>Precio</th>
                                    <th>Cantidad</th>
                                    <th>SubTotal</th>
                                    <th></th>
                                </tr>
                            </thead>
                            <tbody></tbody>
                        </table>
                    </div>
                </div>

                <!-- ============ 3. RESUMEN Y CONFIRMACION ============ -->
                <div class="form-section form-section-resumen">
                    <div class="form-section-title"><i class="fas fa-file-invoice-dollar"></i> Resumen de la Cotización</div>
                    <div class="row g-2">
                        <div class="col-md-3">
                            <label class="form-section-label">Vendedor</label>
                            <div class="input-group input-group-sm">
                                <span class="input-group-text"><i class="fas fa-user"></i></span>
                                <input class="form-control" type="text" value="<?php echo $_SESSION['nombre_usuario']; ?>" disabled>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <label class="form-section-label">Descuento</label>
                            <div class="input-group input-group-sm">
                                <span class="input-group-text"><i class="fas fa-percentage"></i></span>
                                <input class="form-control" type="text" id="descuento" placeholder="0">
                            </div>
                        </div>
                        <div class="col-md-2">
                            <label class="form-section-label">Metodo</label>
                            <select id="metodo" class="form-select form-select-sm">
                                <option value="CONTADO">CONTADO</option>
                                <option value="CREDITO">CREDITO</option>
                            </select>
                        </div>
                        <div class="col-md-2">
                            <label class="form-section-label">Validez</label>
                            <select id="validez" class="form-select form-select-sm">
                                <option value="5 DIAS">5 DIAS</option>
                                <option value="10 DIAS">10 DIAS</option>
                                <option value="15 DIAS">15 DIAS</option>
                                <option value="20 DIAS">20 DIAS</option>
                                <option value="30 DIAS">30 DIAS</option>
                            </select>
                        </div>
                        <div class="col-md-2">
                            <label class="form-section-label">Total a Pagar</label>
                            <div class="input-group input-group-sm">
                                <span class="input-group-text"><i class="fas fa-dollar-sign"></i></span>
                                <input class="form-control fw-bold" type="text" id="totalPagar" placeholder="0.00" disabled>
                            </div>
                        </div>
                        <div class="col-12 mt-2">
                            <button class="btn btn-primary w-100" type="button" id="btnAccion"><i class="fas fa-check-circle me-1"></i>Completar Cotización</button>
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
                            <th>Cliente</th>

                                <th>Fecha</th>
                                <th>Hora</th>
                                <th>Total</th>
                                <th>Validez</th>
                                <th>Metodo</th>
                                <th></th>
                            </tr>
                        </thead>

                        <tbody>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include_once 'views/templates/footer.php'; ?>
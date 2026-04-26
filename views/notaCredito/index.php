<?php include_once 'views/templates/header.php';
// Vigencia de firma electronica (manejo defensivo: ver views/ventas/index.php).
$firmaFinalRaw       = isset($data['empresa']['firmafinal']) ? trim((string)$data['empresa']['firmafinal']) : '';
$firmaFinalSegundos  = ($firmaFinalRaw !== '') ? strtotime($firmaFinalRaw) : false;
$fechaInicialSegundos = time();
$dias = ($firmaFinalSegundos !== false)
    ? (int) floor(($firmaFinalSegundos - $fechaInicialSegundos) / 86400)
    : 0;
//echo "La diferencia entre la fecha : " . $fechaInicial . " y " . $fechaFinal . " es de: " . round($dias, 0, PHP_ROUND_HALF_UP)  . " dias." ;

//Resultado de los dias de diferencia entre dos fechas


?>
<div class="page-header d-flex justify-content-between align-items-center mb-3 flex-wrap gap-2" id="page-header-modern">
  <div>
    <h4 class="mb-0 fw-semibold"><i class="bx bx-minus-circle text-primary me-1"></i>Notas de Crédito</h4>
    <small class="text-muted">Registro y emisión de notas de crédito</small>
  </div>
</div>


<?php if ($dias > 0) {  ?>


    <?php if ($dias <= 5) { ?>

        <div class="alert alert-danger" role="alert">
            <span> SI NO RENOVÁ SU FIRMA ELECTRÓNICA, LA SECCIÓN DE NOTAS DE CREDITOS SE BLOQUEARA POR SU SEGURIDAD FALTANDO 0
                DÍA,
                <?= round($dias, 0, PHP_ROUND_HALF_UP)  . " DÍAS RESTANTE." ?></span>
        </div>
    <?php } else if ($dias <=   15) { ?>

        <div class="alert alert-warning" role="alert">
            <span>SU FIRMA ELECTRÓNICA ESTÁ MUY CERCA DE CADUCAR, POR FAVOR
                RENOVAR,<?= round($dias, 0, PHP_ROUND_HALF_UP)  . " DÍAS RESTANTE." ?></span>
        </div>
    <?php } else if ($dias <= 30) { ?>

        <div class="alert alert-success" role="alert">
            <span> SU FIRMA ELECTRÓNICA ESTA POR CADUCAR, POR FAVOR ANTICIPE SU RENOVACIÓN,
                <?= round($dias, 0, PHP_ROUND_HALF_UP)  . " DÍAS RESTANTE." ?></span>
        </div>

    <?php } ?>

    <div class="card">
        <div class="card-body">
            <nav>
                <div class="nav nav-tabs" id="nav-tab" role="tablist">


                        <button class="nav-link active" id="nav-notaCreditos-tab" data-bs-toggle="tab" data-bs-target="#nav-notaCreditos" type="button" role="tab" aria-controls="nav-notaCreditos" aria-selected="true">Nota Creditos</button>


                        <button class="nav-link" id="nav-historial-tab" data-bs-toggle="tab" data-bs-target="#nav-historial" type="button" role="tab" aria-controls="nav-historial" aria-selected="false">Nota Creditos
                            Electronica</button>


                </div>
            </nav>
            <div class="tab-content" id="nav-tabContent">
                <div class="tab-pane fade show active p-3" id="nav-notaCreditos" role="tabpanel" aria-labelledby="nav-notaCreditos-tab" tabindex="0">
                    <h5 class="card-title text-center"><i class="fas fa-file-invoice-dollar"></i> Nota de Crédito</h5>
                    <hr>
                    <div class="row">

                        <div class="col-md-5 mb-2 ms-auto ">

                            <strong>Nota Credito</strong>
                            <div class="input-group">
                                <span class="input-group-text">Serie</span>

                                <input class="form-control" type="text" value="<?php echo $data['empresa']['establecimiento'] . '-' . $data['empresa']['puntoemi'] . '-' . $data['serieelectronica'][0]; ?>" disabled>
                            </div>

                        </div>
                    </div>

                    <div class="row mb-3">
                        <div class="col-md-7">
                            <label>Buscar Clave de Acceso</label>
                            <div class="input-group mb-2">
                                <input class="form-control" type="text" id="buscarFacturaNC" placeholder="Ingrese número de factura o clave de acceso">
                                <button class="btn btn-secondary" type="button" id="btnBuscarFacturaNC"><i class="fas fa-search"></i></button>
                                <input type="hidden" name="orden_no" id="orden_no">
                                <input type="hidden" name="idCliente" id="idCliente">

                            </div>
                            <span class="text-danger fw-bold mb-2" id="errorFactura"></span>

                        </div>

                        <div class="col-md-5">
                            <label>Motivo de la Nota de Crédito</label>

                            <select class="form-select" name="motivoNotaCredito" id="motivoNotaCredito">
                                <option value="DEVOLUCION DE PRODUCTO" selected>DEVOLUCION DE PRODUCTO</option>
                                <option value="DEVOLUCION TOTAL DE FACTURA">DEVOLUCION TOTAL DE FACTURA</option>

                            </select>

                        </div>
                    </div>

                    <div id="datosFacturaNC">
                        <div class="row mb-2">
                            <div class="col-md-4">
                                <label>Cliente</label>
                                <input class="form-control" type="text" id="clienteNC" disabled>
                            </div>
                            <div class="col-md-3">
                                <label>Serie Factura</label>
                                <input class="form-control" type="text" id="serieFactura" disabled>
                            </div>
                            <div class="col-md-3">
                                <label>Identificación</label>
                                <input class="form-control" type="text" id="identificacionNC" disabled>
                            </div>
                            <div class="col-md-2">
                                <label>Fecha</label>
                                <input class="form-control" type="text" id="fechaFacturaNC" disabled>
                            </div>
                        </div>

                        <div class="col-md-6">
                            <div class="btn-group btn-group-toggle mb-2" data-toggle="buttons">
                                <label class="btn btn-primary">
                                    <input type="radio" id="barcodeNC" checked name="buscarProductoNC"><i style="padding-left: 5px;" class="fas fa-barcode"></i> Barcode
                                </label>
                                <label class="btn btn-info text-white">
                                    <input type="radio" id="nombreNC" name="buscarProductoNC"><i style="padding-left: 5px;" class="fas fa-list"></i> Nombre
                                </label>
                            </div>


                            <div style="display: none;">
                                <div class="btn-group btn-group-toggle mb-2" data-toggle="buttons">
                                    <label class="btn btn-primary">
                                        <input type="radio" id="barcode" checked name="buscarProducto"><i style="padding-left: 5px;" class="fas fa-barcode"></i> Barcode
                                    </label>
                                    <label class="btn btn-info text-white">
                                        <input type="radio" id="nombre" name="buscarProducto"><i style="padding-left: 5px;" class="fas fa-list"></i> Nombre
                                    </label>
                                </div>
                                <div class="col-md-6" style="margin: auto; ">
                                    <div class=" form-group btn-group btn-group-toggle " data-toggle="buttons">
                                        <label class="btn btn-info text-white">
                                            <input type="radio" id="nombreTipoPago" name="buscarProducto"><i style="padding-left:5px;" class="fas fa-list"></i> Tipo Pago
                                        </label>
                                    </div>
                                </div>
                                <div class="col-md-6" style="margin: auto; ">
                                    <div id="containerBuscador " class="btn-group btn-group-toggle mb-2" data-toggle="buttons">
                                        <label class="btn btn-primary">
                                            <input type="radio" id="renta" name="buscarProducto"><i style="padding-left: 5px;" class="fas fa-barcode"></i> Renta
                                        </label>

                                    </div>
                                </div>
                            </div>


                        </div>


                        <!-- input para buscar codigo -->
                        <div class="input-group mb-2" id="containerCodigoNC">
                            <span class="input-group-text"><i class="fas fa-search"></i></span>
                            <input class="form-control" type="text" id="buscarProductoCodigoNC" placeholder="Ingrese Barcode - Enter NC" autocomplete="off">
                        </div>

                        <!-- input para buscar nombre -->
                        <div class="input-group d-none mb-2" id="containerNombreNC">
                            <span class="input-group-text"><i class="fas fa-search"></i></span>
                            <input class="form-control" type="text" id="buscarProductoNombreNC" placeholder="Buscar Producto NC" autocomplete="off">
                        </div>

                        <div style="display:none;">
                            <!-- input para buscar codigo -->
                            <div class="input-group mb-2" id="containerCodigo">
                                <span class="input-group-text"><i class="fas fa-search"></i></span>
                                <input class="form-control" type="text" id="buscarProductoCodigo" placeholder="Ingrese Barcode - Enter" autocomplete="off">
                            </div>

                            <!-- input para buscar nombre -->
                            <div class="input-group d-none mb-2" id="containerNombre">
                                <span class="input-group-text"><i class="fas fa-search"></i></span>
                                <input class="form-control" type="text" id="buscarProductoNombre" placeholder="Buscar Producto" autocomplete="off">
                            </div>



                            <!-- input para buscar nombre -->
                            <div class="input-group d-none mb-2" id="containerNombreTipoPago">
                                <span class="input-group-text"><i class="fas fa-search"></i></span>
                                <input class="form-control" type="text" id="buscarTipoPagoNombre" placeholder="Buscar Tipo Pagos" autocomplete="off">
                            </div>

                            <div class="input-group mb-2" id="containerRenta">
                                <span class="input-group-text"><i class="fas fa-search"></i></span>
                                <input class="form-control" type="text" id="buscarRenta" placeholder="Buscar Renta" autocomplete="off">
                            </div>
                        </div>


                        <!-- table productos -->

                        <div class="table-responsive">
                            <table class="table  table-bordered table-striped table-hover align-middle" id="tblNuevaNotaCredito" style="width: 100%;">
                                <thead>
                                    <tr>
                                        <th>Producto</th>
                                        <th>Precio</th>
                                        <th>Cantidad</th>
                                        <th>% Descuento</th>
                                        <th>SubTotal</th>
                                        <th></th>
                                    </tr>
                                </thead>
                                <tbody>
                                </tbody>
                            </table>
                        </div>


                        <div class="row justify-content-end">
                            <div class="col-md-4">
                                <label>Total a Modificar</label>
                                <input class="form-control" type="text" id="totalNotaCredito" disabled>
                            </div>
                        </div>

                        <div class="d-grid mt-3">
                            <button class="btn btn-primary" id="btnAccion"><i class="fas fa-file-signature"></i>
                                Emitir Nota de Crédito</button>
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
                                    <th>Nota Credito</th>
                                    <th>Fecha</th>
                                    <th>Clave Accesso</th>
                                    <th>Estado</th>
                                    <th>Total</th>
                                    <th>Sri</th>
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


<?php } else {
    $nombreModulo = 'notas de credito';
    include 'views/templates/firma_bloqueo.php';
} ?>


<?php include_once 'views/templates/footer.php'; ?>
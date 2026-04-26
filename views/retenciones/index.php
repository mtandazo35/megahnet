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



<?php if ($dias > 0) {  ?>


    <?php if ($dias <= 5) { ?>

        <div class="alert alert-danger" role="alert">
            <span> SI NO RENOVÁ SU FIRMA ELECTRÓNICA, LA SECCIÓN DE VENTAS SE BLOQUEARA POR SU SEGURIDAD FALTANDO 0 DÍA, <?= round($dias, 0, PHP_ROUND_HALF_UP)  . " DÍAS RESTANTE." ?></span>
        </div>
    <?php } else if ($dias <=   15) { ?>

        <div class="alert alert-warning" role="alert">
            <span>SU FIRMA ELECTRÓNICA ESTÁ MUY CERCA DE CADUCAR, POR FAVOR RENOVAR,<?= round($dias, 0, PHP_ROUND_HALF_UP)  . " DÍAS RESTANTE." ?></span>
        </div>
    <?php } else if ($dias <= 30) { ?>

        <div class="alert alert-success" role="alert">
            <span> SU FIRMA ELECTRÓNICA ESTA POR CADUCAR, POR FAVOR ANTICIPE SU RENOVACIÓN, <?= round($dias, 0, PHP_ROUND_HALF_UP)  . " DÍAS RESTANTE." ?></span>
        </div>

    <?php } ?>

    <div class="card">
        <div class="card-body">
            <nav>
                <div class="nav nav-tabs" id="nav-tab" role="tablist">
                    <button class="nav-link active" id="nav-ventas-tab" data-bs-toggle="tab" data-bs-target="#nav-ventas" type="button" role="tab" aria-controls="nav-ventas" aria-selected="true">Retenciones</button>

                    <button class="nav-link" id="nav-historial-tab" data-bs-toggle="tab" data-bs-target="#nav-historial" type="button" role="tab" aria-controls="nav-historial" aria-selected="false">Retenciones Electronica</button>


                </div>
            </nav>
            <div class="tab-content" id="nav-tabContent">
                <div class="tab-pane fade show active p-3" id="nav-ventas" role="tabpanel" aria-labelledby="nav-ventas-tab" tabindex="0">
                    <h5 class="card-title text-center"><i class="fas fa-cash-register"></i> Nueva Retención</h5>
                    <hr>
                    <div class="row mb-2">

                    <div class="row ">

                    <div class="col-md-4">

                            <label>Fecha Emision Factura</label>
                            <div class="input-group mb-2">
                                <span class="input-group-text"><i class="fas fa-user"></i></span>
                                <input class="form-control" type="text" id="fechaEmisionFactura"  placeholder="Ej. 01/01/2025" >
                            </div>
                            </div>

                           
                            <div class="col-md-4">

                            <label>Numero Comprobante</label>
                            <div class="input-group mb-2">
                                <span class="input-group-text"><i class="fas fa-dollar-sign"></i></span>
                                <input class="form-control" type="text" id="numeroComprobante" placeholder="Ej. 001001000000001" >
                            </div>
                            </div>

                            <div class="col-md-4">

                            <label>Valor Factura</label>
                            <div class="input-group mb-2">
                                <span class="input-group-text"><i class="fas fa-dollar-sign"></i></span>
                                <input type="hidden" name="totalPagarHidden" id="totalPagarHidden">
                                <input class="form-control" type="text" id="totalFactura" onkeyup=" mostrarRetenciones()" placeholder="Valor Factura" >
                            </div>
                            
                            </div>

                        </div>
                        <div class="col-md-6">
                            <div id="containerBuscador " class="btn-group btn-group-toggle mb-2" data-toggle="buttons">
                                <label class="btn btn-primary">
                                    <input type="radio" id="renta" checked name="buscarProducto"><i style="padding-left: 5px;" class="fas fa-barcode"></i> Renta
                                </label>
                               
                            </div>
                        </div>
                        <div class="col-md-1" style=" display:none;" >
                            <div id="containerBuscador " style=" display:none;" class="btn-group btn-group-toggle mb-2" data-toggle="buttons">
                                <label class="btn btn-primary">
                                    <input type="radio" id="barcode" name="buscarProducto"><i style="padding-left: 5px;" class="fas fa-barcode"></i> Productos
                                </label>
                                <label class="btn btn-info text-white">
                                    <input type="radio" id="nombre" name="buscarProducto"><i style="padding-left: 5px;" class="fas fa-list"></i> Servicios
                                </label>
                                <div class="col-md-1" style="margin: auto; display:none;">
                            <div class=" form-group btn-group btn-group-toggle " data-toggle="buttons">
                                <label class="btn btn-info text-white">
                                    <input type="radio" id="nombreTipoPago" name="buscarProducto"><i style="padding-left:5px;" class="fas fa-list"></i> Tipo Pago
                                </label>
                            </div>
                        </div>
                            </div>
                        </div>
                       

                        <div class="col-md-3 mb-2">

                            <?php if ($data['empresa']['facturaelectronica'] == 1) {  ?>
                                <strong>Comprobante Retención</strong>
                                <div class="input-group">
                                    <span class="input-group-text">Serie</span>

                                    <input class="form-control" type="text" value="<?php echo $data['empresa']['establecimiento'] . '-' . $data['empresa']['puntoemi'] . '-' . $data['serieRetenciones'][0]; ?>" disabled>
                                </div>
                            <?php }  ?>

                        </div>

                    </div>


                    <!-- input para buscar codigo -->
                    <div class="input-group mb-2" id="containerRenta">
                        <span class="input-group-text"><i class="fas fa-search"></i></span>
                        <input class="form-control" type="text" id="buscarRenta" placeholder="Buscar Renta" autocomplete="off">
                    </div>

                 



                    <!-- input para buscar codigo -->
                    <div class="input-group mb-2" id="containerCodigo" style="display:none;">
                        <span class="input-group-text"><i class="fas fa-search"></i></span>
                        <input class="form-control" type="text" id="buscarProductoCodigo" placeholder="Ingrese Barcode - Enter" autocomplete="off">
                    </div>

                    <!-- input para buscar nombre -->
                    <div class="input-group d-none mb-2" id="containerNombre" style="display:none;">
                        <span class="input-group-text"><i class="fas fa-search"></i></span>
                        <input class="form-control" type="text" id="buscarProductoNombre" placeholder="Buscar Producto" autocomplete="off">
                    </div>

                    <!-- input para buscar nombre -->
                    <div class="input-group d-none mb-2" id="containerNombreTipoPago" style="display:none;">
                        <span class="input-group-text"><i class="fas fa-search"></i></span>
                        <input class="form-control" type="text" id="buscarTipoPagoNombre" placeholder="Buscar Tipo Pagos" autocomplete="off">
                    </div>
                    <!-- table productos -->

                    <div class="table-responsive">
                        <table class="table  table-bordered table-striped table-hover align-middle" id="tblNuevaRetencion" style="width: 100%;">
                            <thead>
                                <tr>
                                    <th>Tipo Impuesto</th>
                                    <th>Codigo Retencion</th>
                                    <th>Base Imponible Retención</th>
                                    <th>Valor Retenido</th>
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
                                <label>Buscar Proveedor</label>
                                <div class="input-group mb-2">
                                <input type="hidden" id="idProveedor">
                                    <span class="input-group-text"><i class="fas fa-search"></i></span>
                                    <input class="form-control" type="text" id="buscarProveedor" placeholder="Buscar Proveedor">
                                </div>
                                <span class="text-danger fw-bold mb-2" id="errorProveedor"></span>
                            </div>
                            <label>Telefono</label>
                            <div class="input-group mb-2">
                                <span class="input-group-text"><i class="fas fa-phone"></i></span>
                                <input class="form-control" type="text" id="telefonoProveedor" placeholder="Telefono" disabled>
                            </div>

                            <label>Correo Electrónico</label>
                            <div class="input-group mb-2">
                                <span class="input-group-text"><i class="fa-solid fa-envelope"></i></span>
                                <input class="form-control" type="text" id="correoProveedor" placeholder="Correo Electronico" disabled>
                            </div>
                        </div>

               

                        <div class="col-md-4">
                            <label>Vendedor</label>
                            <div class="input-group mb-2">
                                <span class="input-group-text"><i class="fas fa-user"></i></span>
                                <input class="form-control" type="text" value="<?php echo $_SESSION['nombre_usuario']; ?>" placeholder="Vendedor" disabled>
                            </div>

                           

                            <label>Total a Pagar</label>
                            <div class="input-group mb-2">
                                <span class="input-group-text"><i class="fas fa-dollar-sign"></i></span>
                                <input type="hidden" name="totalPagarHidden" id="totalPagarHidden">
                                <input class="form-control" type="text" id="totalPagar" placeholder="Total Pagar" disabled>
                            </div>


                            <div class="d-grid">
                                <button class="btn btn-primary" type="button" id="btnAccion">Completar</button>
                            </div>

                        </div>
                    </div>
                </div>

                <div class="tab-pane fade p-3" id="nav-historial" role="tabpanel" aria-labelledby="nav-historial-tab" tabindex="0">
                    <div class="filtros-fechas mb-2">
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
                                    <th>Retencion</th>
                                    <th>Clave Accesso</th>
                                    <th>Estado</th>
                                    <th>Total Factura</th>
                                    <th>Numero Factura</th>

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
    $nombreModulo = 'retenciones';
    include 'views/templates/firma_bloqueo.php';
} ?>





<?php include_once 'views/templates/footer.php'; ?>

<script>




</script>
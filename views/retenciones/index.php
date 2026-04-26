<?php include_once 'views/templates/header.php';
// Declaramos nuestras fechas inicial y final
$fechaInicial = date('Y-m-d');
$fechaFinal = date($data['empresa']['firmafinal']);



//echo $cantidadDocumento;

// Las convertimos a segundos
$fechaInicialSegundos = strtotime($fechaInicial);
$fechaFinalSegundos = strtotime($fechaFinal);

// Hacemos las operaciones para calcular los dias entre las dos fechas y mostramos el resultado
$dias = ($fechaFinalSegundos - $fechaInicialSegundos) / 86400;
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


<?php } else { ?>

    <div class="error-404 d-flex align-items-center justify-content-center">
        <div class="container">
            <div class="card py-5">
                <div class="row g-0">
                    <div class="col col-xl-5">
                        <div class="card-body p-4">
                            <h1 class="display-1"><span class="text-primary">4</span><span class="text-danger">0</span><span class="text-success">4</span></h1>
                            <h4 class="font-weight-bold display-4">Bloqueo Temporal</h4>
                            <p>La Sección de Retenciones ha sido suspendido de forma temporal!
                                <br>Debido a la caducidad de su firma electrónica!
                                <br>Para solucionarlo, Contactar con soporte Técnico para la renovación de su firma!
                                <br>Caso contrario no podrá ejercer sus ventas!
                            </p>
                            <div class="mt-5">
                                <a href="<?php echo BASE_URL . 'admin'; ?>" class="btn btn-primary btn-lg px-md-5 radius-30">Regresar</a>
                            </div>
                        </div>
                    </div>
                    <div class="col-xl-7">
                        <img src="https://cdn.searchenginejournal.com/wp-content/uploads/2019/03/shutterstock_1338315902.png" class="img-fluid" alt="">
                    </div>
                </div>

                <!--end row-->
            </div>
        </div>
    </div>
<?php } ?>





<?php include_once 'views/templates/footer.php'; ?>

<script>




</script>
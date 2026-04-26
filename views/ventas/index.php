<?php include_once 'views/templates/header.php';
// Declaramos nuestras fechas inicial y final
$fechaInicial = date('Y-m-d');
$fechaFinal = date($data['empresa']['firmafinal']);



$limiteDocumento = $data['empresa']['cantidaddocumento'];
$cantidadDocumento = $data['cantidadDocumento'][0]['cantidad'];
$totalDocumento = $limiteDocumento - $cantidadDocumento;
//echo $cantidadDocumento;

// Las convertimos a segundos
$fechaInicialSegundos = strtotime($fechaInicial);
$fechaFinalSegundos = strtotime($fechaFinal);

// Hacemos las operaciones para calcular los dias entre las dos fechas y mostramos el resultado
$dias = ($fechaFinalSegundos - $fechaInicialSegundos) / 86400;
//echo "La diferencia entre la fecha : " . $fechaInicial . " y " . $fechaFinal . " es de: " . round($dias, 0, PHP_ROUND_HALF_UP)  . " dias." ;

//Resultado de los dias de diferencia entre dos fechas


?>
<div class="page-header d-flex justify-content-between align-items-center mb-3 flex-wrap gap-2" id="page-header-modern">
  <div>
    <h4 class="mb-0 fw-semibold"><i class="bx bx-receipt text-primary me-1"></i>Facturas</h4>
    <small class="text-muted">Facturación electrónica y física</small>
  </div>
</div>


<?php if ($cantidadDocumento < $limiteDocumento) { ?>

    <div class="alert alert-success" role="alert">
        <span> TOTAL DE DOCUMENTOS RESTANTE: <?= $limiteDocumento - $cantidadDocumento ?></span>
    </div>

<?php } else {  ?>

    <div class="alert alert-warning" role="alert">
        <span>SUS DOCUMENTOS ELECTRÓNICOS ESTÁN EN 0, CONTACTE CON SOPORTE PARA ADQUIRIR MÁS DOCUMENTOS TELF: <?= CONTACTO ?></span>
    </div>



<?php  } ?>

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
                    <button class="nav-link active" id="nav-ventas-tab" data-bs-toggle="tab" data-bs-target="#nav-ventas" type="button" role="tab" aria-controls="nav-ventas" aria-selected="true">Ventas</button>

                    <button class="nav-link" id="nav-historial-tab" data-bs-toggle="tab" data-bs-target="#nav-historial" type="button" role="tab" aria-controls="nav-historial" aria-selected="false">Factura Electronica</button>

                    <button class="nav-link" id="nav-historialfisica-tab" data-bs-toggle="tab" data-bs-target="#nav-historialfisica" type="button" role="tab" aria-controls="nav-historialfisica" aria-selected="false">Factura Fisica</button>

                </div>
            </nav>
            <script>
            // Sincroniza tab activo desde localStorage ANTES de que se pinte el tab-content
            (function(){
                try {
                    var t = localStorage.getItem('venta_active_tab');
                    if (!t) return;
                    var btn = document.querySelector('button[data-bs-target="' + t + '"]');
                    if (!btn) return;
                    document.querySelectorAll('#nav-tab .nav-link.active').forEach(b => { b.classList.remove('active'); b.setAttribute('aria-selected','false'); });
                    btn.classList.add('active');
                    btn.setAttribute('aria-selected','true');
                    // También marcar el pane correcto antes de renderizar
                    document.addEventListener('DOMContentLoaded', function(){
                        document.querySelectorAll('#nav-tabContent .tab-pane').forEach(p => { p.classList.remove('active','show'); });
                        var pane = document.querySelector(t);
                        if (pane) pane.classList.add('active','show');
                    }, { once: true });
                } catch(e) {}
            })();
            </script>
            <div class="tab-content" id="nav-tabContent">
                <div class="tab-pane fade show active p-3" id="nav-ventas" role="tabpanel" aria-labelledby="nav-ventas-tab" tabindex="0">
                    <h5 class="card-title text-center"><i class="fas fa-cash-register"></i> Nueva Venta</h5>
                    <hr>
                    <div class="row mb-2">

                    

                        <div class="col-md-6">
                            <div id="containerBuscador" class="btn-group btn-group-toggle mb-2" data-toggle="buttons">
                                <label class="btn btn-primary">
                                    <input type="radio" id="barcode" checked name="buscarProducto"><i style="padding-left: 5px;" class="fas fa-barcode"></i> Servicios
                                </label>
                                <label class="btn btn-info text-white">
                                    <input type="radio" id="nombre" name="buscarProducto"><i style="padding-left: 5px;" class="fas fa-list"></i> Prodcutos
                                </label>
                            </div>
                        </div>
                        <div class="col-md-6" style="margin: auto; display:none;">
                        <div class=" form-group btn-group btn-group-toggle " data-toggle="buttons">                          
                            <label class="btn btn-info text-white">
                                <input type="radio"  id="nombreTipoPago" name="buscarProducto"><i style="padding-left:5px;" class="fas fa-list"></i> Tipo Pago
                            </label>
                        </div>
                    </div>
                    <div class="col-md-6" style="margin: auto; display:none;">
                            <div id="containerBuscador " class="btn-group btn-group-toggle mb-2" data-toggle="buttons">
                                <label class="btn btn-primary">
                                    <input type="radio" id="renta"  name="buscarProducto"><i style="padding-left: 5px;" class="fas fa-barcode"></i> Renta
                                </label>
                               
                            </div>
                        </div>
                       

                        <div class="col-md-3 mb-2">

                            <?php if ($data['empresa']['facturaelectronica'] == 1) {  ?>
                                <strong>Facturación Electrónica</strong>
                                <div class="input-group">
                                    <span class="input-group-text">Serie</span>

                                    <input class="form-control" type="text" value="<?php echo $data['empresa']['establecimiento'] . '-' . $data['empresa']['puntoemi'] . '-' . $data['serieelectronica'][0]; ?>" disabled>
                                </div>
                            <?php } else { ?>
                                <strong>Facturación Fisica</strong>
                                <div class="input-group">
                                    <span class="input-group-text">Serie</span>

                                    <input class="form-control" type="text" value="<?php echo $data['serie'][0]; ?>" disabled>
                                </div>
                            <?php  } ?>
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

                <div class="input-group mb-2" id="containerRenta" style="display:none;">
                        <span class="input-group-text"><i class="fas fa-search"></i></span>
                        <input class="form-control" type="text" id="buscarRenta" placeholder="Buscar Renta" autocomplete="off">
                    </div>

                    <!-- table productos -->

                    <div class="table-responsive">
                        <table class="table  table-bordered table-striped table-hover align-middle" id="tblNuevaVenta" style="width: 100%;">
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

                    <div class="row g-3 venta-resumen">

                        <!-- Columna 1: Cliente -->
                        <div class="col-12 col-md-6 col-xl-4">
                            <div class="card h-100 border-0 shadow-sm">
                                <div class="card-header bg-light fw-semibold py-2"><i class="bx bx-user-circle text-primary me-1"></i>Cliente</div>
                                <div class="card-body">
                                    <?php if ($limiteDocumento != $cantidadDocumento) { ?>
                                        <label class="form-label small mb-1">Buscar cliente</label>
                                        <div class="input-group mb-2">
                                            <input type="hidden" id="idCliente" value="1">
                                            <span class="input-group-text"><i class="fas fa-search"></i></span>
                                            <input class="form-control" type="text" id="buscarCliente" placeholder="Nombre / RUC">
                                        </div>
                                        <span class="text-danger fw-semibold small mb-2 d-block" id="errorCliente"></span>
                                    <?php } ?>
                                    <label class="form-label small mb-1">Telefono</label>
                                    <div class="input-group mb-2">
                                        <span class="input-group-text"><i class="fas fa-phone"></i></span>
                                        <input class="form-control" type="text" id="telefonoCliente" placeholder="Telefono" disabled>
                                    </div>
                                    <label class="form-label small mb-1">Correo electronico</label>
                                    <div class="input-group mb-0">
                                        <span class="input-group-text"><i class="fa-solid fa-envelope"></i></span>
                                        <input class="form-control" type="text" id="correoCliente" placeholder="correo@dominio.com" disabled>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Columna 2: Resumen / totales -->
                        <div class="col-12 col-md-6 col-xl-4">
                            <div class="card h-100 border-0 shadow-sm">
                                <div class="card-header bg-light fw-semibold py-2"><i class="bx bx-calculator text-primary me-1"></i>Resumen</div>
                                <div class="card-body">
                                    <label class="form-label small mb-1">Vendedor</label>
                                    <div class="input-group mb-2">
                                        <span class="input-group-text"><i class="fas fa-user"></i></span>
                                        <input class="form-control" type="text" value="<?php echo $_SESSION['nombre_usuario']; ?>" disabled>
                                    </div>
                                    <label class="form-label small mb-1">Descuento</label>
                                    <div class="input-group mb-2">
                                        <span class="input-group-text"><i class="fas fa-dollar-sign"></i></span>
                                        <input class="form-control" type="text" id="descuento" onkeyup="calcularDescuento();" placeholder="0.00">
                                    </div>
                                    <label class="form-label small mb-1 fw-semibold">Total a pagar</label>
                                    <div class="input-group mb-0">
                                        <span class="input-group-text bg-primary text-white"><i class="fas fa-dollar-sign"></i></span>
                                        <input type="hidden" name="totalPagarHidden" id="totalPagarHidden">
                                        <input class="form-control fw-bold fs-5" type="text" id="totalPagar" placeholder="0.00" disabled>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Columna 3: Pago y accion -->
                        <div class="col-12 col-xl-4">
                            <div class="card h-100 border-0 shadow-sm">
                                <div class="card-header bg-light fw-semibold py-2"><i class="bx bx-credit-card text-primary me-1"></i>Forma de pago</div>
                                <div class="card-body d-flex flex-column">
                                    <label class="form-label small mb-1" for="metodo">Condicion</label>
                                    <select id="metodo" class="form-select mb-2">
                                        <option value="CREDITO">CREDITO</option>
                                        <option value="CONTADO">CONTADO</option>
                                    </select>

                                    <label class="form-label small mb-1" for="tipopago">Tipo de pago</label>
                                    <select id="tipopago" class="form-select mb-2" name="tipopago">
                                        <?php foreach ($data['tipoPago'] as $tipoPago) { ?>
                                            <option value="<?php echo $tipoPago['nombre']; ?>"><?php echo $tipoPago['nombre']; ?></option>
                                        <?php } ?>
                                    </select>

                                    <div id="pp-button" class="mb-2"></div>

                                    <div class="mt-auto d-grid">
                                        <button class="btn btn-primary btn-lg" type="button" id="btnAccion"><i class="bx bx-check-circle me-1"></i>Completar venta</button>
                                    </div>
                                </div>
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
                        <table class="table table-bordered table-striped table-hover align-middle nowrap" id="tblHistorialFE" style="width: 100%;">
                            <thead>
                                <tr>
								<th></th>
									<th>Cliente</th>
                                    <th>Fecha</th>
                                    <th>Factura</th>                                    
                                    <th>Clave Accesso</th>
                                    <th>Estado</th>
                                    <th>Total</th>
                                    <th>Sri</th>
                                    
                                </tr>
                            </thead>

                            <tbody>
                            </tbody>
                        </table>

                    </div>
                </div>
                <div class="tab-pane fade p-3" id="nav-historialfisica" role="tabpanel" aria-labelledby="nav-historialfisica-tab" tabindex="0">
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
                        <table class="table table-bordered table-striped table-hover align-middle nowrap" id="tblHistorialfisica" style="width: 100%;">
                            <thead>
                                <tr>
								  <th></th>
                                    <th>Fecha</th>
                                    <th>Hora</th>
                                    <th>Total</th>
                                    <th>Cliente</th>
                                    <th>Serie</th>
                                    <th>Metodo</th>
                                    <th>Estado</th>
                                  
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
                            <p>La Sección de ventas ha sido suspendido de forma temporal!
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
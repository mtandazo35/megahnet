<?php include_once 'views/templates/header.php'; ?>
<div class="page-header d-flex justify-content-between align-items-center mb-3 flex-wrap gap-2" id="page-header-modern">
    <div>
        <h4 class="mb-0 fw-semibold"><i class="bx bx-package text-primary me-1"></i>Órdenes de Venta</h4>
        <small class="text-muted">Crea, registra y consulta órdenes de venta</small>
    </div>
</div>

<div class="card border-0 shadow-sm">
    <div class="card-body">
        <ul class="nav nav-tabs nav-primary mb-3" role="tablist" id="nav-tab">
            <li class="nav-item" role="presentation">
                <button class="nav-link active" id="nav-orden_venta-tab" data-bs-toggle="tab" data-bs-target="#nav-orden_venta" type="button" role="tab" aria-controls="nav-orden_venta" aria-selected="true">
                    <i class="bx bx-cart-add me-1"></i>Nueva Orden
                </button>
            </li>
            <li class="nav-item" role="presentation">
                <button class="nav-link" id="nav-cargar-tab" data-bs-toggle="tab" data-bs-target="#nav-cargar" type="button" role="tab" aria-controls="nav-cargar" aria-selected="false">
                    <i class="bx bx-spreadsheet me-1"></i>Cargar desde Excel
                </button>
            </li>
            <li class="nav-item" role="presentation">
                <button class="nav-link" id="nav-historial-tab" data-bs-toggle="tab" data-bs-target="#nav-historial" type="button" role="tab" aria-controls="nav-historial" aria-selected="false">
                    <i class="bx bx-history me-1"></i>Historial
                </button>
            </li>
        </ul>

        <div class="tab-content" id="nav-tabContent">

            <!-- ============ NUEVA ORDEN ============ -->
            <div class="tab-pane fade show active" id="nav-orden_venta" role="tabpanel" aria-labelledby="nav-orden_venta-tab" tabindex="0">
                <div class="row g-3">
                    <!-- Columna izquierda: busqueda + carrito -->
                    <div class="col-lg-8">
                        <div class="card border-0 bg-light-subtle h-100">
                            <div class="card-body">
                                <div class="d-flex flex-wrap align-items-center justify-content-between gap-2 mb-3">
                                    <h6 class="mb-0 fw-semibold"><i class="bx bx-search-alt me-1 text-primary"></i>Buscar productos</h6>
                                    <div class="btn-group" role="group" aria-label="Tipo de busqueda">
                                        <input type="radio" class="btn-check" id="barcode" name="buscarProducto" checked>
                                        <label class="btn btn-outline-primary btn-sm" for="barcode"><i class="bx bx-barcode me-1"></i>Servicios</label>
                                        <input type="radio" class="btn-check" id="nombre" name="buscarProducto">
                                        <label class="btn btn-outline-primary btn-sm" for="nombre"><i class="bx bx-list-ul me-1"></i>Productos</label>
                                    </div>
                                </div>

                                <!-- Toggles ocultos (compatibilidad con flujos legacy) -->
                                <div class="d-none">
                                    <input type="radio" id="renta" name="buscarProducto">
                                    <input type="radio" id="nombreTipoPago" name="buscarProducto">
                                </div>
                                <div class="input-group d-none mb-2" id="containerRenta">
                                    <span class="input-group-text"><i class="bx bx-search"></i></span>
                                    <input class="form-control" type="text" id="buscarRenta" placeholder="Buscar Renta" autocomplete="off">
                                </div>
                                <div class="input-group d-none mb-2" id="containerNombreTipoPago">
                                    <span class="input-group-text"><i class="bx bx-search"></i></span>
                                    <input class="form-control" type="text" id="buscarTipoPagoNombre" placeholder="Buscar Tipo Pagos" autocomplete="off">
                                </div>

                                <div class="input-group mb-2" id="containerCodigo">
                                    <span class="input-group-text"><i class="bx bx-search"></i></span>
                                    <input class="form-control" type="text" id="buscarProductoNombre" placeholder="Escribe para buscar un servicio…" autocomplete="off">
                                </div>
                                <div class="input-group d-none mb-2" id="containerNombre">
                                    <span class="input-group-text"><i class="bx bx-search"></i></span>
                                    <input class="form-control" type="text" id="buscarProductoCodigo" placeholder="Escribe para buscar un producto…" autocomplete="off">
                                </div>

                                <div class="table-responsive mt-3">
                                    <table class="table table-hover align-middle mb-0" id="tblNuevaOrdenVenta" style="width:100%;">
                                        <thead class="table-light">
                                            <tr>
                                                <th>Producto</th>
                                                <th class="text-end" style="width:130px;">Precio</th>
                                                <th class="text-center" style="width:110px;">Cantidad</th>
                                                <th class="text-end" style="width:130px;">Subtotal</th>
                                                <th style="width:60px;"></th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <!-- Estado vacio: el JS reemplaza este tbody cuando hay productos -->
                                        </tbody>
                                    </table>
                                </div>
                                <div id="carritoVacioHint" class="text-center text-muted small py-3">
                                    <i class="bx bx-cart bx-sm d-block mb-1"></i>
                                    Aún no hay productos. Busca y agrega uno arriba.
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Columna derecha: cliente + totales -->
                    <div class="col-lg-4">
                        <div class="card border-0 bg-light-subtle">
                            <div class="card-body">
                                <h6 class="fw-semibold mb-3"><i class="bx bx-user me-1 text-primary"></i>Datos del cliente</h6>

                                <label class="form-label small mb-1">Buscar cliente</label>
                                <div class="input-group mb-1">
                                    <input type="hidden" id="idCliente">
                                    <span class="input-group-text"><i class="bx bx-search"></i></span>
                                    <input class="form-control" type="text" id="buscarCliente" placeholder="Nombre o cédula del cliente">
                                </div>
                                <div class="text-danger fw-bold small mb-2" id="errorCliente"></div>

                                <label class="form-label small mb-1">Teléfono</label>
                                <div class="input-group mb-2">
                                    <span class="input-group-text"><i class="bx bx-phone"></i></span>
                                    <input class="form-control" type="text" id="telefonoCliente" placeholder="—" disabled>
                                </div>

                                <label class="form-label small mb-1">Dirección</label>
                                <div class="border rounded px-2 py-2 mb-3 small text-muted" id="direccionCliente" style="min-height:38px;">
                                    <i class="bx bx-home me-1"></i>—
                                </div>

                                <h6 class="fw-semibold mb-2 mt-3"><i class="bx bx-receipt me-1 text-primary"></i>Resumen</h6>

                                <label class="form-label small mb-1">Vendedor</label>
                                <div class="input-group mb-2">
                                    <span class="input-group-text"><i class="bx bx-user-circle"></i></span>
                                    <input class="form-control" type="text" value="<?php echo htmlspecialchars($_SESSION['nombre_usuario'] ?? '', ENT_QUOTES, 'UTF-8'); ?>" disabled>
                                </div>

                                <label class="form-label small mb-1">Descuento</label>
                                <div class="input-group mb-2">
                                    <span class="input-group-text">$</span>
                                    <input class="form-control" type="number" min="0" step="0.01" id="descuento" placeholder="0.00">
                                </div>

                                <label class="form-label small mb-1">Total a pagar</label>
                                <div class="input-group mb-3">
                                    <span class="input-group-text bg-primary text-white">$</span>
                                    <input class="form-control fw-bold fs-5 text-end" type="text" id="totalPagar" value="0.00" disabled>
                                </div>

                                <label class="form-label small mb-1">Método</label>
                                <select id="metodo" class="form-select mb-2">
                                    <option value="CREDITO">Crédito</option>
                                    <option value="CONTADO">Contado</option>
                                </select>

                                <label class="form-label small mb-1">Forma de pago</label>
                                <select id="tipopago" class="form-select mb-3" name="tipopago">
                                    <?php foreach ($data['tipoPago'] as $tipoPago) { ?>
                                        <option value="<?php echo htmlspecialchars($tipoPago['nombre'], ENT_QUOTES, 'UTF-8'); ?>"><?php echo htmlspecialchars($tipoPago['nombre'], ENT_QUOTES, 'UTF-8'); ?></option>
                                    <?php } ?>
                                </select>

                                <div class="d-grid">
                                    <button class="btn btn-primary btn-lg" type="button" id="btnAccion">
                                        <i class="bx bx-check-circle me-1"></i>Completar orden
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- ============ HISTORIAL ============ -->
            <div class="tab-pane fade" id="nav-historial" role="tabpanel" aria-labelledby="nav-historial-tab" tabindex="0">
                <div class="row g-2 mb-3 align-items-end">
                    <div class="col-sm-3">
                        <label class="form-label small mb-1" for="desde">Desde</label>
                        <input id="desde" class="form-control form-control-sm" type="date">
                    </div>
                    <div class="col-sm-3">
                        <label class="form-label small mb-1" for="hasta">Hasta</label>
                        <input id="hasta" class="form-control form-control-sm" type="date">
                    </div>
                </div>
                <div class="table-responsive">
                    <table class="table table-hover align-middle nowrap" id="tblHistorial" style="width:100%;">
                        <thead class="table-light">
                            <tr>
                                <th>Acciones</th>
                                <th>Cliente</th>
                                <th>Fecha</th>
                                <th>Orden Venta</th>
                                <th class="text-end">Total</th>
                                <th>Método</th>
                                <th>Estado</th>
                            </tr>
                        </thead>
                        <tbody></tbody>
                    </table>
                </div>
            </div>

            <!-- ============ CARGAR EXCEL ============ -->
            <div class="tab-pane fade" id="nav-cargar" role="tabpanel" aria-labelledby="nav-cargar-tab" tabindex="0">
                <?php
                $tituloCarga      = 'Cargar órdenes desde Excel';
                $descripcionCarga = 'Sube un archivo <strong>.xlsx</strong> con las órdenes a registrar.';
                include 'views/templates/cargar_excel.php';
                ?>
            </div>

        </div>
    </div>
</div>

<script>
// Ocultar/mostrar el hint de carrito vacio segun el estado del tbody (no toca logica de JS de ordenventa).
(function(){
    var tbody = document.querySelector('#tblNuevaOrdenVenta tbody');
    var hint  = document.getElementById('carritoVacioHint');
    if (!tbody || !hint) return;
    var sync = function(){ hint.style.display = tbody.children.length === 0 ? '' : 'none'; };
    sync();
    new MutationObserver(sync).observe(tbody, { childList: true });
})();
</script>
<?php include_once 'views/templates/footer.php'; ?>

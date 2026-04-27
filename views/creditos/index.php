<?php include_once 'views/templates/header.php'; ?>

<div class="card">
    <div class="card-body">
        <div class="d-flex align-items-center">
            <div></div>
            <!---    <div class="dropdown ms-auto">
                <a class="dropdown-toggle dropdown-toggle-nocaret" href="#" data-bs-toggle="dropdown"><i class='bx bx-dots-horizontal-rounded font-22 text-option'></i>
                </a>
              <ul class="dropdown-menu">
                    <li><a class="dropdown-item" href="#" id="nuevoAbono"><i class="fas fa-dollar-sign"></i> Abonos</a>
                    </li> 
                </ul>
            </div>--->
        </div>
        <nav>
            <div class="nav nav-tabs" id="nav-tab" role="tablist">
                <button class="nav-link active" id="nav-creditos-tab" data-bs-toggle="tab" data-bs-target="#nav-creditos" type="button" role="tab" aria-controls="nav-creditos" aria-selected="true">Creditos</button>
                <button class="nav-link" id="nav-abonos-tab" data-bs-toggle="tab" data-bs-target="#nav-abonos" type="button" role="tab" aria-controls="nav-abonos" aria-selected="false">Abonos</button>
                <button class="nav-link" id="nav-completados-tab" data-bs-toggle="tab" data-bs-target="#nav-completados" type="button" role="tab" aria-controls="nav-completados" aria-selected="false">Completados</button>

            </div>
        </nav>

        <div class="tab-content" id="nav-tabContent">
            <div class="tab-pane fade show active mt-2" id="nav-creditos" role="tabpanel" aria-labelledby="nav-creditos-tab" tabindex="0">
                <?php $tituloListado='Listado de Créditos'; $iconoListado='bx-credit-card'; include 'views/templates/listado_titulo.php'; ?>

                <?php if ($_SESSION['rol'] == 3) { ?>
                    <div class="container" style="display: none;">
                        <button class="btn btn-primary" type="submit" id="AbonarFacturas">Cancelar Varios Creditos</button>
                        <button class="btn btn-primary" style="margin: 5px;" type="submit" id="nuevoAbono">Abono Individual</button>
                    </div>

                <?php   } else { ?>

                    <button class="btn btn-primary" type="submit" id="AbonarFacturas">Cancelar Varios Creditos</button>
                    <button class="btn btn-primary" style="margin: 5px;" type="submit" id="nuevoAbono">Abono Individual</button>

                <?php } ?>





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
                                <th>Check</th>
                                <th>Monto</th>
                                <th>Abonado</th>
                                <th>Restante</th>
                                <th>Estado</th>
                                <th>N° Electronica</th>
                                <th>N° Orden Venta</th>
                                <th></th>
                            <th>Notificar</th></tr>
                        </thead>

                        <tbody>
                        </tbody>
                    </table>

                </div>
            </div>
            <div class="tab-pane fade p-3" id="nav-abonos" role="tabpanel" aria-labelledby="nav-abonos-tab" tabindex="0">

                <div class="table-responsive">
                    <table class="table table-bordered table-striped table-hover align-middle nowrap" id="tblAbonos" style="width: 100%;">
                        <thead>
                            <tr>
                            <th>Cliente</th>
                                <th>Fecha</th>
                                <th>Monto</th>
                                <th>N° Credito</th>
                                <th></th>

                            </tr>
                        </thead>

                        <tbody>
                        </tbody>
                    </table>
                </div>
            </div>


            <div class="tab-pane fade show mt-2" id="nav-completados" role="tabpanel" aria-labelledby="nav-creditos-tab" tabindex="0">
                <?php $tituloListado='Créditos completados'; $iconoListado='bx-check-circle'; include 'views/templates/listado_titulo.php'; ?>
                <div class="table-responsive">
                    <table class="table table-bordered table-striped table-hover align-middle nowrap" id="tblCompletados" style="width: 100%;">
                        <thead>
                            <tr>
                                <th>Cliente</th>
                                <th>Monto</th>
                                <th>Abonado</th>
                                <th>Estado</th>
                                <th>N° Electronica</th>
                                <th>N° Orden Venta</th>
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
<div id="modalAbono" class="modal fade" role="dialog" aria-labelledby="my-modal-title" aria-hidden="true">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Agregar Abono</h5>
                <button class="btn-close" data-bs-dismiss="modal" aria-label="Close">
                </button>
            </div>
            <div class="modal-body">
                <div class="row">
                    <div class="col-md-12">
                        <label>Buscar Cliente</label>
                        <div class="input-group mb-2">
                            <input type="hidden" id="idCredito">
                            <span class="input-group-text"><i class="fas fa-search"></i></span>
                            <input class="form-control" type="text" id="buscarCliente" placeholder="Buscar Cliente">
                        </div>
                        <span class="text-danger fw-bold" id="errorCliente"></span>
                    </div>
                    <div class="col-md-12">
                        <label>Telefono</label>
                        <div class="input-group mb-2">
                            <span class="input-group-text"><i class="fas fa-phone"></i></span>
                            <input class="form-control" type="text" id="telefonoCliente" placeholder="Telefono" disabled>
                        </div>
                    </div>
                    <div class="col-md-12">
                        <label>Dirección</label>
                        <div class="input-group mb-2">
                            <span class="input-group-text"><i class="fas fa-home"></i></span>
                            <div class="form-control direccion-display" id="direccionCliente"></div>
                        </div>
                    </div>
                    <div class="col-md-4 mb-2">
                        <label>Abonado</label>
                        <div class="input-group">
                            <span class="input-group-text"><i class="fas fa-dollar-sign"></i></span>
                            <input class="form-control" type="text" id="abonado" readonly>
                        </div>
                    </div>
                    <div class="col-md-4 mb-2">
                        <label>Restante</label>
                        <div class="input-group">
                            <span class="input-group-text"><i class="fas fa-dollar-sign"></i></span>
                            <input class="form-control" type="text" id="restante" readonly>
                        </div>
                    </div>
                    <div class="col-md-4 mb-2">
                        <div class="form-group">
                            <label for="fecha">Fecha Venta</label>
                            <input id="fecha" class="form-control" type="text" placeholder="Fecha Venta" readonly>
                        </div>
                    </div>
                    <div class="col-md-4 mb-2">
                        <div class="form-group">
                            <label for="monto_total">Monto Total</label>
                            <input id="monto_total" class="form-control" type="text" placeholder="Monto Total" readonly>
                        </div>
                    </div>

                    <!-- Select dropdown: tipos de pago al lado del Monto Total -->
                    <div class="col-md-8 mb-2">
                        <label class="form-label mb-1" for="selectTipoPago">Tipo Pago <span class="text-danger">*</span></label>
                        <select class="form-select" id="selectTipoPago">
                            <option value="">Seleccionar</option>
                            <option value="EFECTIVO">EFECTIVO</option>
                            <option value="TRANSFERENCIA">TRANSFERENCIA</option>
                            <option value="DEPOSITOS">DEPOSITOS</option>
                            <option value="CHEQUE">CHEQUE</option>
                            <option value="RETENCIONES">RETENCIONES</option>
                        </select>
                    </div>

                    <!-- table productos -->

                    <div class="table-responsive" style="max-height:280px; overflow-y:auto;">
                        <table class="table table-bordered table-striped table-hover align-middle" id="tblNuevaTipoPago" style="width: 100%;">
                            <thead>
                                <tr>
                                    <th>Descripcion</th>
                                    <th>Codigo Comprobante</th>
                                    <th>Valor</th>
                                    <th></th>
                                </tr>
                            </thead>
                            <tbody>
                            </tbody>
                        </table>
                    </div>


                    <div class="col-md-5 mb-2" style="margin-left:auto">
                        <div class="form-group">
                            <label for="total">Total Abono</label>
                            <input id="total" name="total" class="form-control" type="text" placeholder="Abono Total" disabled>
                        </div>
                    </div>

                </div>
                <div class="d-grid">
                    <button class="btn btn-primary" type="button" id="btnAccion">Abonar</button>
                </div>
            </div>
        </div>
    </div>
</div>

<div id="modalVariosAbonos" class="modal fade" role="dialog" aria-labelledby="my-modal-title" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Varios Abono</h5>
                <button class="btn-close" data-bs-dismiss="modal" aria-label="Close">
                </button>
            </div>
            <div class="modal-body">
                <div class="row">

                    <div class="col-md-12 mb-2">
                        <div class="form-group">
                            <label for="contratos">Contratos</label>
                            <input id="contratos" class="form-control" type="text" placeholder="Contratos" readonly>
                        </div>
                    </div>
                    <div class="col-md-4 mb-2">
                        <div class="form-group">
                            <label for="monto_total_varios">Monto Total</label>
                            <input id="monto_total_varios" class="form-control" type="text" placeholder="Monto Total Varios" readonly>
                        </div>
                    </div>
                    <div class="col-md-4 mb-2">
                        <div class="form-group">
                            <label for="monto_abonar_varios">Abonar</label>
                            <input id="monto_abonar_varios" class="form-control" type="number" step="0.01" min="0.01" placeholder="Monto Abonar Varios">
                        </div>
                    </div>



                    <div class="col-md-4 mb-3">
                        <div class="form-group">
                            <label for="tipoPagoVarios">Tipo Pago <span class="text-danger">*</span></label>
                            <select id="tipoPagoVarios" class="form-control" name="tipoPagoVarios">
                                <option value="">Seleccionar</option>
                                <option value="EFECTIVO">EFECTIVO</option>
                                <option value="TRANSFERENCIA">TRANSFERENCIA</option>
                                <option value="DEPOSITOS">DEPOSITOS</option>
                                <option value="CHEQUE">CHEQUE</option>
                                <option value="RETENCIONES">RETENCIONES</option>


                            </select>
                        </div>
                        <span id="errorTipoPagoVarios" class="text-danger"></span>
                    </div>
                    <div class="col-md-12 mb-2">
                        <div class="form-group">
                            <label for="codigoPagoVarios">Codigo Comprobante</label>
                            <input id="codigoPagoVarios" name="codigoPagoVarios" class="form-control" type="text" placeholder="Codigo Comprobante / Validar">
                        </div>
                    </div>

                </div>
                <div class="d-grid">
                    <button class="btn btn-primary" type="button" id="btnAccionVarios">Abonar</button>
                </div>
            </div>
        </div>
    </div>
</div>
<style>
/* Direccion como display-only (innerHTML se setea desde JS) */
.direccion-display {
    background-color: #e9ecef;
    color: #495057;
    min-height: 38px;
    display: flex;
    align-items: center;
    cursor: default;
}
.direccion-display:empty::before {
    content: 'Dirección';
    color: #adb5bd;
}
</style>

<script>
// Modal "Agregar Abono": al seleccionar un tipo de pago en el dropdown
// (mismas opciones EFECTIVO/TRANSFERENCIA/DEPOSITOS/CHEQUE/RETENCIONES que
// el modal "Varios Abono"), se agrega al carrito via agregarTipoPago()
// y se refresca la tabla #tblNuevaTipoPago.
// El select MANTIENE la opcion seleccionada (mismo comportamiento que Varios Abono).
document.addEventListener('DOMContentLoaded', function () {
    var sel = document.getElementById('selectTipoPago');
    if (!sel) return;

    sel.addEventListener('change', function () {
        var nombre = sel.value;
        if (!nombre) return;
        if (typeof agregarTipoPago === 'function') {
            // id = nombre (string) — el backend usa el nombre, no un id numerico
            agregarTipoPago(nombre, nombre, 0, '');
        }
        // No reseteamos el select: la opcion elegida queda visible.
        // Si el usuario quiere agregar otro tipo, simplemente cambia la seleccion.
    });
});
</script>

<?php include_once 'views/templates/footer.php'; ?>
<?php include_once 'views/templates/header.php'; ?>

<div class="container-fluid">
    <div class="page-header d-flex justify-content-between align-items-center mb-3">
        <div>
            <h4 class="mb-0 fw-semibold"><i class="bx bx-receipt text-primary me-1"></i>Dashboard SRI</h4>
            <small class="text-muted">Estado de comprobantes electrónicos</small>
        </div>
    </div>

    <!-- KPIs con estilo moderno -->
    <div class="row row-cols-2 row-cols-md-3 row-cols-xl-6 g-3 mb-4">
        <div class="col">
            <div class="sri-kpi sri-kpi-pendientes" onclick="filtrar(0)">
                <div class="sri-kpi-icon"><i class="bx bx-time-five"></i></div>
                <div class="sri-kpi-label">Pendientes</div>
                <div class="sri-kpi-value"><?= $data['resumen']['pendientes']; ?></div>
            </div>
        </div>
        <div class="col">
            <div class="sri-kpi sri-kpi-autorizadas" onclick="filtrar(1)">
                <div class="sri-kpi-icon"><i class="bx bx-check-circle"></i></div>
                <div class="sri-kpi-label">Autorizadas</div>
                <div class="sri-kpi-value"><?= $data['resumen']['autorizadas']; ?></div>
            </div>
        </div>
        <div class="col">
            <div class="sri-kpi sri-kpi-rechazadas" onclick="filtrar(2)">
                <div class="sri-kpi-icon"><i class="bx bx-x-circle"></i></div>
                <div class="sri-kpi-label">Rechazadas</div>
                <div class="sri-kpi-value"><?= $data['resumen']['rechazadas']; ?></div>
            </div>
        </div>
        <div class="col">
            <div class="sri-kpi sri-kpi-reintentables">
                <div class="sri-kpi-icon"><i class="bx bx-refresh"></i></div>
                <div class="sri-kpi-label">Reintentables</div>
                <div class="sri-kpi-value"><?= $data['resumen']['reintentables']; ?></div>
            </div>
        </div>
        <div class="col">
            <div class="sri-kpi sri-kpi-bloqueadas">
                <div class="sri-kpi-icon"><i class="bx bx-lock-alt"></i></div>
                <div class="sri-kpi-label">Bloqueadas</div>
                <div class="sri-kpi-value"><?= $data['resumen']['bloqueadas']; ?></div>
            </div>
        </div>
        <div class="col">
            <div class="sri-kpi sri-kpi-correos">
                <div class="sri-kpi-icon"><i class="bx bx-envelope"></i></div>
                <div class="sri-kpi-label">Correos pend.</div>
                <div class="sri-kpi-value"><?= $data['resumen']['correos_pendientes']; ?></div>
            </div>
        </div>
    </div>

    <!-- FILTROS -->
    <div class="sri-filter-bar mb-3">
        <button class="sri-filter" onclick="filtrar(null)"><i class="bx bx-list-ul"></i>Todos</button>
        <button class="sri-filter sri-filter-pendientes" onclick="filtrar(0)"><i class="bx bx-time-five"></i>Pendientes</button>
        <button class="sri-filter sri-filter-autorizadas" onclick="filtrar(1)"><i class="bx bx-check-circle"></i>Autorizadas</button>
        <button class="sri-filter sri-filter-rechazadas" onclick="filtrar(2)"><i class="bx bx-x-circle"></i>Rechazadas</button>
    </div>

    <!-- TABLA -->
    <div class="card radius-10">
        <div class="card-body">
            <div class="table-responsive">
                <table class="table align-middle" id="tblSri">
                    <thead>
                        <tr>
                            <th>Factura</th>
                            <th>Cliente</th>
                            <th>Fecha</th>
                            <th>Total</th>
                            <th>Estado</th>
                            <th>Intentos</th>
                            <th>Correo</th>
                            <th>Acciones</th>
                        </tr>
                    </thead>
                    <tbody></tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<style>
/* ===== SRI Dashboard KPIs ===== */
.sri-kpi {
    background: #fff;
    border: 1px solid rgba(0,0,0,.06);
    border-radius: 14px;
    padding: 18px;
    text-align: center;
    cursor: pointer;
    transition: transform .15s, box-shadow .15s;
    position: relative;
    overflow: hidden;
    min-height: 130px;
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
}
.sri-kpi::before {
    content: '';
    position: absolute;
    top: 0; left: 0; right: 0;
    height: 4px;
}
.sri-kpi:hover { transform: translateY(-3px); box-shadow: 0 10px 30px rgba(0,0,0,.08); }

.sri-kpi-icon {
    width: 44px; height: 44px;
    border-radius: 12px;
    display: flex; align-items: center; justify-content: center;
    font-size: 22px;
    margin-bottom: 6px;
}
.sri-kpi-label {
    font-size: 11px;
    text-transform: uppercase;
    letter-spacing: .6px;
    font-weight: 700;
    color: #6b7280;
}
.sri-kpi-value {
    font-size: 30px;
    font-weight: 800;
    line-height: 1.1;
    margin-top: 2px;
    color: #111827;
}

/* Colores por estado */
.sri-kpi-pendientes::before    { background: linear-gradient(90deg,#f59e0b,#fbbf24); }
.sri-kpi-pendientes .sri-kpi-icon { background: rgba(245,158,11,.14); color: #d97706; }

.sri-kpi-autorizadas::before   { background: linear-gradient(90deg,#16a34a,#22c55e); }
.sri-kpi-autorizadas .sri-kpi-icon { background: rgba(22,163,74,.14); color: #15803d; }

.sri-kpi-rechazadas::before    { background: linear-gradient(90deg,#6b7280,#4b5563); }
.sri-kpi-rechazadas .sri-kpi-icon { background: rgba(107,114,128,.14); color: #4b5563; }

.sri-kpi-reintentables::before { background: linear-gradient(90deg,#0ea5e9,#0284c7); }
.sri-kpi-reintentables .sri-kpi-icon { background: rgba(14,165,233,.14); color: #0369a1; }

.sri-kpi-bloqueadas::before    { background: linear-gradient(90deg,#dc2626,#ef4444); }
.sri-kpi-bloqueadas .sri-kpi-icon { background: rgba(220,38,38,.14); color: #b91c1c; }

.sri-kpi-correos::before       { background: linear-gradient(90deg,#6366f1,#4f46e5); }
.sri-kpi-correos .sri-kpi-icon { background: rgba(99,102,241,.14); color: #4338ca; }

/* ===== Filter bar ===== */
.sri-filter-bar {
    display: flex;
    gap: 6px;
    flex-wrap: wrap;
}
.sri-filter {
    display: inline-flex;
    align-items: center;
    gap: 6px;
    padding: 7px 14px;
    font-size: 13px;
    font-weight: 500;
    background: #fff;
    color: #4b5563;
    border: 1px solid #e5e7eb;
    border-radius: 20px;
    cursor: pointer;
    transition: all .15s;
}
.sri-filter:hover { background: #f3f5fa; color: #111827; border-color: #d1d5db; }
.sri-filter i { font-size: 15px; }
.sri-filter-pendientes:hover  { background: #fef3c7; color: #92400e; border-color: #fcd34d; }
.sri-filter-autorizadas:hover { background: #dcfce7; color: #166534; border-color: #86efac; }
.sri-filter-rechazadas:hover  { background: #fee2e2; color: #991b1b; border-color: #fca5a5; }
</style>


<!-- MODAL ERROR -->
<div class="modal fade" id="modalError2" tabindex="-1">
    <div class="modal-dialog modal-xl modal-dialog-scrollable">
        <div class="modal-content">

            <div class="modal-header bg-warning text-white">
                <h5 class="modal-title">
                    <i class="fas fa-exclamation-triangle"></i> Respuesta del SRI
                </h5>
                <button class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>

            <div class="modal-body">

                <!-- RESUMEN -->
                <div class="alert alert-warning">
                    <strong>Documento con errores devueltos por el SRI</strong>
                </div>

                <!-- RESPUESTA -->
                <pre id="errorSri" class="bg-light p-3 rounded" style="white-space: pre-wrap; font-size: 13px;"></pre>

            </div>

            <div class="modal-footer">
                <button class="btn btn-outline-primary" onclick="copiarErrorSri()">
                    <i class="fas fa-copy"></i> Copiar
                </button>
                <button class="btn btn-secondary" data-bs-dismiss="modal">
                    Cerrar
                </button>
            </div>

        </div>
    </div>
</div>

<div class="modal fade" id="modalError" tabindex="-1">
    <div class="modal-dialog modal-lg modal-dialog-scrollable">
        <div class="modal-content">

            <div class="modal-header bg-danger text-white">
                <h5 class="modal-title">
                    <i class="fas fa-file-invoice"></i> Error SRI
                </h5>
                <button class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>

            <div class="modal-body">

                <div id="bloqueParseado" style="display:none">

                    <div class="alert alert-danger d-flex align-items-center">
                        <i class="fas fa-exclamation-triangle fa-2x me-2"></i>
                        <div>
                            <strong>Error devuelto por el SRI</strong><br>
                            <span id="sriMensaje"></span>
                        </div>
                    </div>

                    <div class="row mb-3">
                        <div class="col-md-4">
                            <strong>Código</strong><br>
                            <span class="badge bg-danger" id="sriCodigo"></span>
                        </div>
                        <div class="col-md-4">
                            <strong>Estado</strong><br>
                            <span class="badge bg-warning" id="sriEstado"></span>
                        </div>
                        <div class="col-md-4">
                            <strong>Tipo</strong><br>
                            <span class="badge bg-dark" id="sriTipo"></span>
                        </div>
                    </div>

                    <div class="alert alert-info">
                        <i class="fas fa-tools"></i>
                        <strong>Sugerencia:</strong>
                        <span id="sriSugerencia"></span>
                    </div>

                    <button class="btn btn-outline-dark btn-sm mt-2" onclick="toggleRaw()">
                        <i class="fas fa-bug"></i> Ver detalle técnico (SRI)
                    </button>

                </div>

                <hr>

                <pre id="errorSriRaw" class="bg-dark text-light p-3 rounded mt-2"
                    style="display:none; max-height:300px; overflow:auto; font-size:13px">
</pre>


                <pre id="errorSriRaw" class="bg-light p-3 rounded" style="display:none; font-size:13px"></pre>


                <pre id="errorSriRaw" class="bg-light p-3 rounded" style="display:none; font-size:13px"></pre>

            </div>

            <div class="modal-footer">
                  <button type="button" class="btn btn-outline-primary" onclick="copiarErrorSri()">
                    <i class="fas fa-copy"></i> Copiar texto
                </button>
                <button class="btn btn-secondary" data-bs-dismiss="modal">Cerrar</button>
            </div>

        </div>
    </div>
</div>


<?php include_once 'views/templates/footer.php'; ?>
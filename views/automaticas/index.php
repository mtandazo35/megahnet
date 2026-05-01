<?php include_once 'views/templates/header.php';

?>


<div class="card">
    <?php if ($data['estadoCorteF']['total'] == 0) { ?>

        <div class="card-body">
            <div class="d-flex align-items-center">
                <div></div>
                <div class="dropdown ms-auto">
                    <!--- <a class="dropdown-toggle dropdown-toggle-nocaret" href="#" data-bs-toggle="dropdown"><i class='bx bx-dots-horizontal-rounded font-22 text-option'></i>
                </a>
                <ul class="dropdown-menu">
                    <li><a class="dropdown-item" href="#" id="btnFacturasAutomaticas"><i class="fas fa-trash text-danger"></i> Facturas Automáticas</a>
                    </li>
                </ul>--->
            </div>
        </div>
        <h5 class="card-title text-center">Facturas Automáticas</h5>
        <hr>


        <div class="table-responsive">
            <table class="table table-bordered table-striped table-hover align-middle nowrap" id="tblAutomaticas"
                style="width: 100%;">
                <thead>
                    <tr>

                        <th>Cliente</th>
                        <th>Total</th>
                        <th>Tributario</th>
                        <th>Estado</th>
                        <th>Emisión</th>
                        <th># Contrato</th>
                        <th>Fecha</th>
                    </tr>
                </thead>

                <tbody>
                </tbody>
            </table>
        </div>
        <div class="d-grid">
            <button class="btn btn-primary" type="button" id="btnAccion">Completar</button>
        </div>


    </div>



    <?php } else { ?>



                    <div class="corte-cerrado-wrap">
                    <div class="card corte-cerrado-card">
                        <div class="card-body text-center py-5 px-4">
                            <div class="corte-cerrado-icon mb-3">
                                <i class='bx bxs-check-shield'></i>
                            </div>
                            <h3 class="fw-bold mb-2 text-dark">Corte de Facturas ya procesado</h3>
                            <p class="text-muted mb-4">
                                El corte del periodo <strong><?= date('m-Y') ?></strong> ya fue cerrado.<br>
                                Volverá a estar disponible a partir del
                                <strong><?= date('m-Y', strtotime('first day of next month')) ?></strong>.
                            </p>
                            <div class="corte-cerrado-pills d-flex justify-content-center gap-2 flex-wrap mb-4">
                                <span class="corte-pill corte-pill-done"><i class='bx bx-calendar-check'></i> Procesado <?= date('m-Y') ?></span>
                                <span class="corte-pill corte-pill-next"><i class='bx bx-calendar'></i> Próximo <?= date('m-Y', strtotime('first day of next month')) ?></span>
                            </div>
                            <a href="<?php echo BASE_URL . 'admin'; ?>" class="btn btn-primary px-4">
                                <i class='bx bx-arrow-back me-1'></i>Regresar al tablero
                            </a>
                        </div>
                    </div>
                </div>

                <style>
                .corte-cerrado-wrap { max-width: 640px; margin: 40px auto; padding: 0 16px; }
                .corte-cerrado-card { border: none; border-radius: 16px; box-shadow: 0 8px 24px rgba(0,0,0,.06); }
                .corte-cerrado-icon {
                    width: 96px; height: 96px; margin: 0 auto;
                    border-radius: 50%;
                    background: linear-gradient(135deg,#d1fae5 0%,#a7f3d0 100%);
                    display: flex; align-items: center; justify-content: center;
                    font-size: 48px; color: #059669;
                }
                .corte-pill {
                    display: inline-flex; align-items: center; gap: 6px;
                    padding: 7px 14px; border-radius: 999px;
                    font-size: 13px; font-weight: 600; white-space: nowrap;
                }
                .corte-pill i { font-size: 16px; }
                .corte-pill-done { background: rgba(16,185,129,.12); color: #047857; }
                .corte-pill-next { background: rgba(99,102,241,.12); color: #4338ca; }
                </style>




    <?php } ?>




</div>

</div>







<?php include_once 'views/templates/footer.php'; ?>
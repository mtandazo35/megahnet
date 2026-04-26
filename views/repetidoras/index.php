<?php include_once 'views/templates/header.php'; ?>

<div class="page-header d-flex justify-content-between align-items-center mb-3 flex-wrap gap-2" id="page-header-modern">
    <div>
        <h4 class="mb-0 fw-semibold"><i class="bx bx-broadcast text-primary me-1"></i>Repetidoras</h4>
        <small class="text-muted">Antenas y repetidoras de la red</small>
    </div>
    <div class="d-flex flex-wrap gap-2">
        <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#modalRepetidora" id="btnAbrirNuevoRepetidora">
            <i class="bx bx-plus me-1"></i>Nueva repetidora
        </button>
        <a href="<?php echo BASE_URL . 'clientes/inactivos'; ?>" class="btn btn-outline-secondary">
            <i class="bx bx-trash me-1"></i>Inactivos
        </a>
    </div>
</div>

<div class="card border-0 shadow-sm">
    <div class="card-body">
        <?php $tituloListado='Listado de Repetidoras'; $iconoListado='bx-broadcast'; include 'views/templates/listado_titulo.php'; ?>
        <div class="table-responsive">
            <table class="table table-hover align-middle nowrap" id="tblRepetidoras" style="width: 100%;">
                <thead class="table-light">
                    <tr>
                        <th>SSID</th>
                        <th>Marca</th>
                        <th>IP</th>
                        <th>Canal</th>
                        <th>Seguridad</th>
                        <th>Frecuencia</th>
                        <th>Acciones</th>
                    </tr>
                </thead>
                <tbody></tbody>
            </table>
        </div>
    </div>
</div>

<div class="modal fade" id="modalRepetidora" tabindex="-1" aria-labelledby="modalRepetidoraLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title fw-semibold" id="modalRepetidoraLabel"><i class="bx bx-broadcast text-primary me-1"></i>Datos de la repetidora</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
            </div>
            <form id="formulario" autocomplete="off">
                <div class="modal-body">
                    <input type="hidden" id="id" name="id">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label small mb-1" for="marca">Marca <span class="text-danger">*</span></label>
                            <div class="input-group">
                                <span class="input-group-text"><i class="bx bx-purchase-tag"></i></span>
                                <input class="form-control" type="text" name="marca" id="marca" placeholder="Marca">
                            </div>
                            <span id="errorMarca" class="text-danger small"></span>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label small mb-1" for="ssid">SSID <span class="text-danger">*</span></label>
                            <div class="input-group">
                                <span class="input-group-text"><i class="bx bx-wifi"></i></span>
                                <input class="form-control" type="text" name="ssid" id="ssid" placeholder="Nombre de red">
                            </div>
                            <span id="errorSsid" class="text-danger small"></span>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label small mb-1" for="ip">IP</label>
                            <div class="input-group">
                                <span class="input-group-text"><i class="bx bx-globe"></i></span>
                                <input class="form-control" type="text" name="ip" id="ip" placeholder="IP">
                            </div>
                            <span id="errorIp" class="text-danger small"></span>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label small mb-1" for="canal">Canal</label>
                            <div class="input-group">
                                <span class="input-group-text"><i class="bx bx-broadcast"></i></span>
                                <input class="form-control" type="text" name="canal" id="canal" placeholder="Canal">
                            </div>
                            <span id="errorCanal" class="text-danger small"></span>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label small mb-1" for="frecuencia">Frecuencia <span class="text-danger">*</span></label>
                            <div class="input-group">
                                <span class="input-group-text"><i class="bx bx-pulse"></i></span>
                                <input id="frecuencia" type="text" class="form-control" name="frecuencia" placeholder="Frecuencia">
                            </div>
                            <span id="errorFrecuencia" class="text-danger small"></span>
                        </div>
                        <div class="col-md-12">
                            <label class="form-label small mb-1" for="seguridad">Seguridad <span class="text-danger">*</span></label>
                            <div class="input-group">
                                <span class="input-group-text"><i class="bx bx-shield"></i></span>
                                <input id="seguridad" type="text" class="form-control" name="seguridad" placeholder="WPA2 / WPA3 / Open">
                            </div>
                            <span id="errorSeguridad" class="text-danger small"></span>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-light" id="btnNuevo"><i class="bx bx-eraser me-1"></i>Limpiar</button>
                    <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-primary" id="btnAccion"><i class="bx bx-save me-1"></i>Registrar</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function () {
    var modal = document.getElementById('modalRepetidora');
    var form  = document.getElementById('formulario');
    var btnAccion = document.getElementById('btnAccion');
    if (form && modal) form.addEventListener('mhn:registroOk', function () {
        bootstrap.Modal.getOrCreateInstance(modal).hide();
    });
    var btnAbrir = document.getElementById('btnAbrirNuevoRepetidora');
    if (btnAbrir && form && btnAccion) btnAbrir.addEventListener('click', function () {
        form.reset();
        document.getElementById('id').value = '';
        btnAccion.textContent = 'Registrar';
        ['errorMarca','errorSsid','errorIp','errorCanal','errorSeguridad','errorFrecuencia']
            .forEach(function(id){ var el=document.getElementById(id); if (el) el.textContent=''; });
    });
});
window.abrirModalRepetidora = function () {
    var el = document.getElementById('modalRepetidora');
    if (el) bootstrap.Modal.getOrCreateInstance(el).show();
};
</script>

<?php include_once 'views/templates/footer.php'; ?>

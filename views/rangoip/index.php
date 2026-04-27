<?php include_once 'views/templates/header.php'; ?>

<div class="page-header d-flex justify-content-between align-items-center mb-3 flex-wrap gap-2" id="page-header-modern">
    <div>
        <h4 class="mb-0 fw-semibold"><i class="bx bx-network-chart text-primary me-1"></i>Rango de IP</h4>
        <small class="text-muted">Redes IP y rangos asignados a zonas</small>
    </div>
    <div class="d-flex flex-wrap gap-2">
        <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#modalRangoIp" id="btnAbrirNuevoRangoIp">
            <i class="bx bx-plus me-1"></i>Nuevo rango
        </button>
    </div>
</div>

<div class="card border-0 shadow-sm">
    <div class="card-body">
        <?php $tituloListado='Listado de IP'; $iconoListado='bx-network-chart'; include 'views/templates/listado_titulo.php'; ?>
        <div class="table-responsive">
            <table class="table table-hover nowrap" id="tblIp" style="width: 100%;">
                <thead class="table-light">
                    <tr>
                        <th>ID</th>
                        <th>Red (CIDR)</th>
                        <th>Gateway</th>
                        <th>Final</th>
                        <th>Última utilizada</th>
                        <th>Disponibles</th>
                        <th>Zona</th>
                        <th>Acciones</th>
                    </tr>
                </thead>
                <tbody></tbody>
            </table>
        </div>
    </div>
</div>

<div class="modal fade" id="modalRangoIp" tabindex="-1" aria-labelledby="modalRangoIpLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title fw-semibold" id="modalRangoIpLabel"><i class="bx bx-network-chart text-primary me-1"></i>Datos del rango de IP</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
            </div>
            <form id="formulario" autocomplete="off">
                <div class="modal-body">
                    <input type="hidden" id="id" name="id">
                    <input type="hidden" name="red" id="red">
                    <input type="hidden" name="final" id="final">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label small mb-1" for="zona">Zona <span class="text-danger">*</span></label>
                            <select id="zona" class="form-select" name="zona">
                                <option value="">Seleccionar</option>
                                <?php foreach ($data['zona'] as $zona) { ?>
                                    <option value="<?php echo htmlspecialchars($zona['id'], ENT_QUOTES, 'UTF-8'); ?>"><?php echo htmlspecialchars($zona['descripcion'], ENT_QUOTES, 'UTF-8'); ?></option>
                                <?php } ?>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label small mb-1" for="id_mikrotik">Mikrotik <span class="text-muted fw-normal">(opcional)</span></label>
                            <select id="id_mikrotik" class="form-select" name="id_mikrotik">
                                <option value="">Seleccionar</option>
                                <?php foreach (($data['mikrotiks'] ?? []) as $mk): ?>
                                    <option value="<?php echo (int)$mk['id']; ?>"><?php echo htmlspecialchars($mk['nombre']); ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label small mb-1" for="redCidr">Red (CIDR) <span class="text-danger">*</span></label>
                            <div class="input-group">
                                <span class="input-group-text"><i class="bx bx-network-chart"></i></span>
                                <input class="form-control" type="text" id="redCidr" placeholder="Ej: 172.20.0.0/24">
                            </div>
                            <span id="errorRed" class="text-danger small"></span>
                            <small class="text-muted">Indica la red en notación CIDR.</small>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label small mb-1" for="gateway">IP Gateway <span class="text-danger">*</span></label>
                            <div class="input-group">
                                <span class="input-group-text"><i class="bx bx-router"></i></span>
                                <input class="form-control" type="text" name="gateway" id="gateway" placeholder="Ej: 172.20.0.1">
                            </div>
                            <span id="errorGateway" class="text-danger small"></span>
                            <small class="text-muted">El gateway no se asigna a clientes.</small>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label small mb-1" for="finalDisplay">IP Final (calculado)</label>
                            <div class="input-group">
                                <span class="input-group-text"><i class="bx bx-flag"></i></span>
                                <input class="form-control bg-light" type="text" id="finalDisplay" placeholder="se calcula del CIDR" readonly>
                            </div>
                            <span id="errorFinal" class="text-danger small"></span>
                            <small class="text-muted" id="rangoInfo"></small>
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
    var modal = document.getElementById('modalRangoIp');
    var form  = document.getElementById('formulario');
    var btnAccion = document.getElementById('btnAccion');
    if (form && modal) form.addEventListener('mhn:registroOk', function () {
        bootstrap.Modal.getOrCreateInstance(modal).hide();
    });
    var btnAbrir = document.getElementById('btnAbrirNuevoRangoIp');
    if (btnAbrir && form && btnAccion) btnAbrir.addEventListener('click', function () {
        form.reset();
        document.getElementById('id').value = '';
        btnAccion.textContent = 'Registrar';
        ['errorRed','errorGateway','errorFinal'].forEach(function(id){ var el=document.getElementById(id); if (el) el.textContent=''; });
    });
});
window.abrirModalRangoIp = function () {
    var el = document.getElementById('modalRangoIp');
    if (el) bootstrap.Modal.getOrCreateInstance(el).show();
};
</script>

<?php include_once 'views/templates/footer.php'; ?>

<?php include_once 'views/templates/header.php'; ?>

<div class="card">
    <div class="card-body">
        <div class="d-flex align-items-center">
            <div></div>

        </div>
        <nav>
            <div class="nav nav-tabs" id="nav-tab" role="tablist">
                <button class="nav-link active" id="nav-ip-tab" data-bs-toggle="tab" data-bs-target="#nav-ip" type="button" role="tab" aria-controls="nav-ip" aria-selected="true">Rango de Ip</button>
                <button class="nav-link" id="nav-nuevo-tab" data-bs-toggle="tab" data-bs-target="#nav-nuevo" type="button" role="tab" aria-controls="nav-nuevo" aria-selected="false">Nuevo</button>
            </div>
        </nav>
        <div class="tab-content" id="nav-tabContent">
            <div class="tab-pane fade show active mt-2" id="nav-ip" role="tabpanel" aria-labelledby="nav-ip-tab" tabindex="0">
                <h5 class="card-title text-center"><i class="fas fa-tags"></i> Listado de Ip</h5>
                <hr>
                <div class="table-responsive">
                    <table class="table table-bordered table-striped table-hover nowrap" id="tblIp" style="width: 100%;">
                        <thead>
                            <tr>
                                <th>Id</th>
                                <th>Red (CIDR)</th>
                                <th>Gateway</th>
                                <th>Final</th>
                                <th>Última utilizada</th>
                                <th>Disponibles</th>
                                <th>Zona</th>

                                <th></th>
                            </tr>
                        </thead>

                        <tbody>
                        </tbody>
                    </table>
                </div>
            </div>
            <div class="tab-pane fade p-3" id="nav-nuevo" role="tabpanel" aria-labelledby="nav-nuevo-tab" tabindex="0">
                <form id="formulario" autocomplete="off">
                    <input type="hidden" id="id" name="id">
                    <div class="row mb-3">
                        <div class="col-md-12">

                            <div class="form-group">
                                <label for="zona">Zona <span class="text-danger">*</span></label>
                                <select id="zona" class="form-control" name="zona">
                                    <option value="">Seleccionar</option>
                                    <?php foreach ($data['zona'] as $zona) {

                                    ?>

                                        <option value="<?php echo $zona['id']; ?>"><?php echo $zona['descripcion']; ?>
                                        </option>
                                    <?php } ?>
                                </select>
                            </div>
                        </div>

                        <input type="hidden" name="red" id="red">
                        <input type="hidden" name="final" id="final">
                        <div class="col-md-4">
                            <label for="redCidr">Red (CIDR) <span class="text-danger">*</span></label>
                            <div class="input-group">
                                <span class="input-group-text"><i class="fas fa-network-wired"></i></span>
                                <input class="form-control" type="text" id="redCidr" placeholder="Ej: 172.20.0.0/24">
                            </div>
                            <span id="errorRed" class="text-danger"></span>
                            <small class="text-muted">Indica la red en notacion CIDR.</small>
                        </div>
                        <div class="col-md-4">
                            <label for="gateway">Ip Gateway <span class="text-danger">*</span></label>
                            <div class="input-group">
                                <span class="input-group-text"><i class="fas fa-route"></i></span>
                                <input class="form-control" type="text" name="gateway" id="gateway" placeholder="Ej: 172.20.0.1">
                            </div>
                            <span id="errorGateway" class="text-danger"></span>
                            <small class="text-muted">El gateway no se asigna a clientes.</small>
                        </div>
                        <div class="col-md-4">
                            <label for="finalDisplay">Ip Final (calculado)</label>
                            <div class="input-group">
                                <span class="input-group-text"><i class="fas fa-flag-checkered"></i></span>
                                <input class="form-control bg-light" type="text" id="finalDisplay" placeholder="se calcula del CIDR" readonly>
                            </div>
                            <span id="errorFinal" class="text-danger"></span>
                            <small class="text-muted" id="rangoInfo"></small>
                        </div>
                    </div>

                    <div class="text-end">
                        <button class="btn btn-danger" type="button" id="btnNuevo">Nuevo</button>
                        <button class="btn btn-primary" type="submit" id="btnAccion">Registrar</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<?php include_once 'views/templates/footer.php'; ?>
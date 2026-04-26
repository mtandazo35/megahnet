<?php include_once 'views/templates/header.php'; ?>
<style>
    #foto {
        display: none;
    }

    .circle {
        display: inline-block;
        border-radius: 50%;
        width: 100px;
        height: 100px;
        background-color: red;
        cursor: pointer;
    }
</style>


<div class="card">
    <div class="card-body">
        <h5 class="card-title text-center">Datos de la Empresa</h5>
        <hr>
        <form class="p-4" id="formulario" autocomplete="off" enctype="multipart/form-data">
            <input type="hidden" id="id" name="id" value="<?php echo $data['empresa']['id']; ?>">
            <div class="row">
                <div class="col-lg-4 col-sm-6 mb-2">
                    <label>Ruc <span class="text-danger">*</span></label>
                    <div class="input-group">
                        <span class="input-group-text"><i class="fas fa-id-card"></i></span>
                        <input type="text" id="ruc" name="ruc" class="form-control" value="<?php echo $data['empresa']['ruc']; ?>" placeholder="Ruc">
                    </div>
                    <span id="errorRuc" class="text-danger"></span>
                </div>
                <div class="col-lg-4 col-sm-6 mb-2">
                    <label>Nombre Empresarial <span class="text-danger">*</span></label>
                    <div class="input-group">
                        <span class="input-group-text"><i class="fas fa-list"></i></span>
                        <input type="text" id="nombre" name="nombre" class="form-control" value="<?php echo $data['empresa']['nombre']; ?>" placeholder="Nombre">
                    </div>
                    <span id="errorNombre" class="text-danger"></span>
                </div>
                <div class="col-lg-4 col-sm-6 mb-2">
                    <label>Razón Social <span class="text-danger">*</span></label>
                    <div class="input-group">
                        <span class="input-group-text"><i class="fas fa-list"></i></span>
                        <input type="text" id="razon" name="razon" class="form-control" value="<?php echo $data['empresa']['razon_social']; ?>" placeholder="Razón Social">
                    </div>
                    <span id="errorRazon" class="text-danger"></span>
                </div>
                <div class="col-lg-4 col-sm-6 mb-2">
                    <label>Teléfono <span class="text-danger">*</span></label>
                    <div class="input-group">
                        <span class="input-group-text"><i class="fas fa-phone"></i></span>
                        <input type="number" id="telefono" name="telefono" class="form-control" value="<?php echo $data['empresa']['telefono']; ?>" placeholder="Teléfono">
                    </div>
                    <span id="errorTelefono" class="text-danger"></span>
                </div>
                <div class="col-lg-4 col-sm-6 mb-2">
                    <label>Correo Electrónico <span class="text-danger">*</span></label>
                    <div class="input-group">
                        <span class="input-group-text"><i class="fas fa-envelope"></i></span>
                        <input type="email" id="correo" name="correo" class="form-control" value="<?php echo $data['empresa']['correo']; ?>" placeholder="Correo Electrónico">
                    </div>
                    <span id="errorCorreo" class="text-danger"></span>
                </div>
                <div class="col-lg-3 col-sm-6 mb-2">
                    <label>Impuesto Iva <span class="text-danger">*</span></label>
                    <div class="input-group">
                        <span class="input-group-text"><i class="fa-solid fa-percent"></i></span>
                        <input type="number" id="impuesto" name="impuesto" class="form-control" value="<?php echo $data['empresa']['impuesto']; ?>" placeholder="Impuesto">
                    </div>
                    <span id="errorImpuesto" class="text-danger"></span>

                </div>
                <div class="col-lg-6 col-sm-6 mb-2">
                    <label>Dirección <span class="text-danger">*</span></label>
                    <div class="input-group">
                        <span class="input-group-text"><i class="fas fa-home"></i></span>
                        <input type="text" id="direccion" name="direccion" class="form-control" value="<?php echo $data['empresa']['direccion']; ?>" placeholder="Dirección">
                    </div>
                    <span id="errorDireccion" class="text-danger"></span>
                </div>

                <div class="col-lg-6 col-sm-6 mb-2">
                    <div class="form-group">
                        <label for="mensaje">Mensaje (Opcional)</label>
                        <textarea id="mensaje" class="form-control" name="mensaje" rows="3" placeholder="Mensaje de Agradecimiento"><?php echo $data['empresa']['mensaje']; ?></textarea>
                    </div>
                </div>

                <div class="col-lg-3 col-sm-6 mb-2" style="margin-top: -20px;">
                    <div class="form-group text-center">
                        <label for="foto" style="font-size: 100px;"><i class="fa-solid fa-upload"></i></label>
                        <input id="foto" class="form-control" type="file" name="foto">
                    </div>
                    <input type="hidden" name="foto_actual" id="foto_actual" value="<?php echo  $data['empresa']['img']; ?>">
                    <input type="hidden" name="foto_remove" id="foto_remove" value="<?php echo  $data['empresa']['img']; ?>">
                    <div id="containerPreview" class="text-center">

                        <?php
                        $file = 'assets/images/Logo.jpg';
                        $exists = is_file($file);
                        if ($exists) { ?>

                            <img class="img-thumbnail" src="<?php echo  BASE_URL . 'assets/images/Logo.jpg'; ?>" alt="LOGO_JPG" width="50%">

                        <?php } else { ?>

                            <img class="img-thumbnail" src="<?php echo  BASE_URL . 'assets/images/imgfac/Logo2.jpg'; ?>" alt="LOGO_JPG" width="50%">

                        <?php    }
                        ?>
                        <button class="btn btn-danger" style="width: 50%; margin-top: 5px;" type="button" onclick="deleteImg()"><i class="fas fa-trash"></i></button>
                    </div>
                </div>
            </div>
            <h5 class="card-title text-center">Datos Factura Electrónica</h5>

            <hr>
            <div class="row">
                <div class="col-lg-3 col-sm-6 mb-2">
                    <label>Total Items <span class="text-danger">*</span></label>
                    <div class="input-group">
                        <span class="input-group-text"><i class="fas fa-id-card"></i></span>
                        <input type="text" id="totalitems" name="totalitems" class="form-control" value="<?php echo $data['empresa']['totalitems']; ?>" placeholder="Total Items Factura">
                    </div>
                    <span id="erroritems" class="text-danger"></span>
                </div>
                <div class="col-lg-3 col-sm-6 mb-2">
                    <label>Establecimiento <span class="text-danger">*</span></label>
                    <div class="input-group">
                        <span class="input-group-text"><i class="fas fa-list"></i></span>
                        <input type="text" id="establecimiento" name="establecimiento" class="form-control" value="<?php echo $data['empresa']['establecimiento']; ?>" placeholder="Establecimiento">
                    </div>
                    <span id="errorEstablecimiento" class="text-danger"></span>
                </div>
                <div class="col-lg-3 col-sm-6 mb-2">
                    <label>Punto Emisión <span class="text-danger">*</span></label>
                    <div class="input-group">
                        <span class="input-group-text"><i class="fas fa-list"></i></span>
                        <input type="text" id="emision" name="emision" class="form-control" value="<?php echo $data['empresa']['puntoemi']; ?>" placeholder="Punto Emision">
                    </div>
                    <span id="errorEmision" class="text-danger"></span>
                </div>
                <div class="col-lg-3 col-sm-6 mb-2">
                    <label>Contabilidad <span class="text-danger">*</span></label>
                    <div class="input-group">
                        <span class="input-group-text"><i class="fas fa-phone"></i></span>
                        <input type="text" id="contabilidad" name="contabilidad" class="form-control" value="<?php echo $data['empresa']['contabilidad']; ?>" placeholder="Contabilidad">
                    </div>
                    <span id="errorContabilidad" class="text-danger"></span>
                </div>

                <?php if ($data['id_usuario'] == 1) { ?>
                    <div class="col-lg-3 col-sm-6 mb-2">
                        <label>Firma Inicio <span class="text-danger">*</span></label>
                        <div class="input-group">
                            <span class="input-group-text"><i class="fas fa-signature"></i></span>
                            <input type="text" id="firmainicio" name="firmainicio" class="form-control" value="<?php echo $data['empresa']['firmainicio']; ?>" placeholder="Ejem. 2023-01-01">
                        </div>
                        <span id="errorFirmainicio" class="text-danger"></span>
                    </div>
                    <div class="col-lg-3 col-sm-6 mb-2">
                        <label>Firma Final <span class="text-danger">*</span></label>
                        <div class="input-group">
                            <span class="input-group-text"><i class="fas fa-signature"></i>
                            </span>
                            <input type="text" id="firmafinal" name="firmafinal" class="form-control" value="<?php echo $data['empresa']['firmafinal']; ?>" placeholder="Ejem. 2023-01-01">
                        </div>
                        <span id="errorFirmafinal" class="text-danger"></span>
                    </div>
                    <div class="col-lg-3 col-sm-6 mb-2">
                        <label>Documento <span class="text-danger">*</span></label>
                        <div class="input-group">
                            <span class="input-group-text"><i class="fas fa-file-invoice"></i>
                            </span>
                            <input type="text" id="cantidaddocumento" name="cantidaddocumento" class="form-control" value="<?php echo $data['empresa']['cantidaddocumento']; ?>" placeholder="Cantidad Documento">
                        </div>
                        <span id="errorCantidaddocumento" class="text-danger"></span>
                    </div>

                <?php } ?>




                <div class="col-12">
                    <hr><h6 class="text-primary fw-semibold mb-3"><i class="bx bx-shield-alt-2 me-1"></i>Firma electrónica (SRI)</h6>
                </div>
                <div class="col-lg-6 mb-2">
                    <label>Archivo de firma (.p12) <small class="text-muted">— solo si vas a actualizar</small></label>
                    <div class="input-group">
                        <span class="input-group-text"><i class="fas fa-file-shield"></i></span>
                        <input type="file" id="firma_p12" name="firma_p12" class="form-control" accept=".p12,.pfx">
                    </div>
                    <?php $existe_firma = file_exists(__DIR__ . '/../../facturaelectronica/public/archivos/token/FIRMA.p12'); ?>
                    <div id="firmaEstadoBox" class="mt-2 p-2 border rounded <?php echo $existe_firma ? 'border-success bg-light' : 'border-danger bg-light'; ?>" style="font-size:0.85em;">
                        <?php if ($existe_firma) { ?>
                            <div><i class="bx bx-check-circle text-success"></i> <strong>Firma cargada</strong> (FIRMA.p12) <span class="badge bg-secondary" id="firmaEstadoBadge">Verificando…</span></div>
                            <div id="firmaEstadoDetalle" class="mt-1 text-muted">Cargando datos del certificado…</div>
                        <?php } else { ?>
                            <div><i class="bx bx-x-circle text-danger"></i> <strong>No hay firma cargada</strong> — sube un archivo .p12 para emitir facturas electronicas.</div>
                        <?php } ?>
                    </div>
                </div>
                <div class="col-lg-6 mb-2">
                    <label>Contraseña de la firma <span class="text-muted small">(dejar vacío para no cambiar)</span></label>
                    <div class="input-group">
                        <span class="input-group-text"><i class="fas fa-key"></i></span>
                        <input type="password" id="firma_password" name="firma_password" class="form-control" placeholder="••••••••">
                        <button type="button" id="btnVerificarFirma" class="btn btn-outline-primary"><i class="bx bx-check-shield"></i> Verificar</button>
                    </div>
                    <small class="text-muted">Pulsa Verificar para confirmar que la clave guardada/escrita es correcta y ver la vigencia del certificado.</small>
                    <span id="errorFirmaPassword" class="text-danger"></span>
                </div>

                <div class="col-lg-3 col-sm-6 mb-2" style="display: flex;">
                    <?php if ($data['empresa']['facturaelectronica'] == 1) {  ?>
                        <div class="form-check form-switch" style="margin: auto;">
                            <input class="form-check-input" type="checkbox" role="switch" id="chelectronica" name="chelectronica" value="1" checked>
                            <label class="form-check-label" for="flexSwitchCheckChecked">Facturacion Electrónica</label>
                        </div>
                    <?php } else {   ?>
                        <div class="form-check form-switch" style="margin: auto;">
                            <input class="form-check-input" type="checkbox" role="switch" value="1" id="chelectronica" name="chelectronica">
                            <label class="form-check-label" for="flexSwitchCheckChecked">Facturacion Electrónica</label>
                        </div>
                    <?php  } ?>

                </div>

            </div>
            <div class="text-end">
                <button class="btn btn-primary" type="submit" id="btnAccion">Actualizar</button>
            </div>
        </form>
    </div>
</div>


<?php include_once 'views/templates/footer.php'; ?>
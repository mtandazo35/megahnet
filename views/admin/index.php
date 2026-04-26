<?php include_once 'views/templates/header.php'; ?>

<style>
    /* ===== Configuración: estilos locales del módulo ===== */
    #foto { display: none; }

    /* Card-uploader: zona de arrastre/clic para el logo */
    .logo-uploader {
        position: relative;
        border: 2px dashed #cbd5e1;
        border-radius: 10px;
        background: #f8fafc;
        padding: 18px 12px;
        text-align: center;
        cursor: pointer;
        transition: border-color .15s ease, background .15s ease;
        min-height: 220px;
        display: flex;
        flex-direction: column;
        align-items: center;
        justify-content: center;
    }
    .logo-uploader:hover { border-color: #2563eb; background: #eff6ff; }
    .logo-uploader .lu-icon {
        font-size: 38px;
        color: #2563eb;
        line-height: 1;
        margin-bottom: 8px;
    }
    .logo-uploader .lu-title {
        font-weight: 600;
        color: #111827;
        font-size: 14px;
    }
    .logo-uploader .lu-hint {
        color: #6b7280;
        font-size: 12px;
        margin-top: 2px;
    }
    .logo-uploader.has-image {
        padding: 12px;
        background: #fff;
        border-style: solid;
        border-color: #e5e7eb;
    }
    .logo-uploader.has-image .logo-preview-img {
        max-width: 100%;
        max-height: 160px;
        object-fit: contain;
        border-radius: 6px;
    }
    .logo-actions {
        margin-top: 10px;
        display: flex;
        gap: 8px;
        justify-content: center;
        width: 100%;
    }
    .logo-actions .btn { font-size: 12px; padding: 4px 10px; }

    /* Switch grande */
    .config-switch {
        background: #fff;
        border: 1px solid #e5e7eb;
        border-radius: 8px;
        padding: 12px 14px;
        display: flex;
        align-items: center;
        gap: 12px;
    }
    .config-switch .form-check-input {
        width: 42px;
        height: 22px;
        margin-top: 0;
        cursor: pointer;
    }
    .config-switch label { cursor: pointer; margin: 0; font-weight: 500; }

    /* Estado de firma SRI */
    #firmaEstadoBox {
        font-size: 12.5px;
        border-radius: 6px;
        padding: 10px 12px;
    }
    #firmaEstadoBox.border-success { background: #f0fdf4; }
    #firmaEstadoBox.border-danger  { background: #fef2f2; }

    /* CKEditor del mensaje: que no exceda alto del card */
    .ck.ck-editor__main > .ck-editor__editable { min-height: 120px; }
</style>

<div class="page-header d-flex justify-content-between align-items-center mb-3 flex-wrap gap-2">
    <div>
        <h4 class="mb-0 fw-semibold"><i class="bx bx-cog text-primary me-1"></i>Configuración del Sistema</h4>
        <small class="text-muted">Datos de la empresa, factura electrónica y firma SRI</small>
    </div>
    <div class="d-flex flex-wrap gap-2">
        <button type="button" class="btn btn-primary" id="btnGuardarTop" form="formulario">
            <i class="bx bx-save me-1"></i>Guardar cambios
        </button>
    </div>
</div>

<form id="formulario" autocomplete="off" enctype="multipart/form-data">
    <input type="hidden" id="id" name="id" value="<?php echo htmlspecialchars($data['empresa']['id'] ?? '', ENT_QUOTES); ?>">

    <!-- ===== SECCION 1: DATOS DE LA EMPRESA ===== -->
    <div class="form-section">
        <div class="form-section-title"><i class="bx bx-buildings"></i>Datos de la Empresa</div>

        <div class="row g-3">
            <!-- Columna izquierda: campos -->
            <div class="col-lg-9">
                <div class="row g-3">
                    <div class="col-md-4">
                        <label class="form-label">Ruc <span class="text-danger">*</span></label>
                        <div class="input-group">
                            <span class="input-group-text"><i class="fas fa-id-card"></i></span>
                            <input type="text" id="ruc" name="ruc" class="form-control" value="<?php echo htmlspecialchars($data['empresa']['ruc'] ?? '', ENT_QUOTES); ?>" placeholder="Ruc">
                        </div>
                        <span id="errorRuc" class="text-danger small"></span>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Nombre Empresarial <span class="text-danger">*</span></label>
                        <div class="input-group">
                            <span class="input-group-text"><i class="fas fa-building"></i></span>
                            <input type="text" id="nombre" name="nombre" class="form-control" value="<?php echo htmlspecialchars($data['empresa']['nombre'] ?? '', ENT_QUOTES); ?>" placeholder="Nombre">
                        </div>
                        <span id="errorNombre" class="text-danger small"></span>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Razón Social <span class="text-danger">*</span></label>
                        <div class="input-group">
                            <span class="input-group-text"><i class="fas fa-file-signature"></i></span>
                            <input type="text" id="razon" name="razon" class="form-control" value="<?php echo htmlspecialchars($data['empresa']['razon_social'] ?? '', ENT_QUOTES); ?>" placeholder="Razón Social">
                        </div>
                        <span id="errorRazon" class="text-danger small"></span>
                    </div>

                    <div class="col-md-4">
                        <label class="form-label">Teléfono <span class="text-danger">*</span></label>
                        <div class="input-group">
                            <span class="input-group-text"><i class="fas fa-phone"></i></span>
                            <input type="number" id="telefono" name="telefono" class="form-control" value="<?php echo htmlspecialchars($data['empresa']['telefono'] ?? '', ENT_QUOTES); ?>" placeholder="Teléfono">
                        </div>
                        <span id="errorTelefono" class="text-danger small"></span>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Correo Electrónico <span class="text-danger">*</span></label>
                        <div class="input-group">
                            <span class="input-group-text"><i class="fas fa-envelope"></i></span>
                            <input type="email" id="correo" name="correo" class="form-control" value="<?php echo htmlspecialchars($data['empresa']['correo'] ?? '', ENT_QUOTES); ?>" placeholder="Correo Electrónico">
                        </div>
                        <span id="errorCorreo" class="text-danger small"></span>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Impuesto IVA <span class="text-danger">*</span></label>
                        <div class="input-group">
                            <span class="input-group-text"><i class="fas fa-percent"></i></span>
                            <input type="number" id="impuesto" name="impuesto" class="form-control" value="<?php echo htmlspecialchars($data['empresa']['impuesto'] ?? '', ENT_QUOTES); ?>" placeholder="Impuesto">
                        </div>
                        <span id="errorImpuesto" class="text-danger small"></span>
                    </div>

                    <div class="col-md-12">
                        <label class="form-label">Dirección <span class="text-danger">*</span></label>
                        <div class="input-group">
                            <span class="input-group-text"><i class="fas fa-home"></i></span>
                            <input type="text" id="direccion" name="direccion" class="form-control" value="<?php echo htmlspecialchars($data['empresa']['direccion'] ?? '', ENT_QUOTES); ?>" placeholder="Dirección">
                        </div>
                        <span id="errorDireccion" class="text-danger small"></span>
                    </div>

                    <div class="col-md-12">
                        <label class="form-label" for="mensaje">Mensaje (Opcional)</label>
                        <textarea id="mensaje" class="form-control" name="mensaje" rows="3" placeholder="Mensaje de Agradecimiento"><?php echo $data['empresa']['mensaje'] ?? ''; ?></textarea>
                    </div>
                </div>
            </div>

            <!-- Columna derecha: uploader del logo -->
            <div class="col-lg-3">
                <label class="form-label d-block">Logo de la Empresa</label>
                <label for="foto" class="logo-uploader<?php
                    $file = 'assets/images/Logo.jpg';
                    echo is_file($file) ? ' has-image' : '';
                ?>" id="logoUploader">
                    <div id="containerPreview">
                        <?php
                        $file = 'assets/images/Logo.jpg';
                        if (is_file($file)) { ?>
                            <img class="logo-preview-img" src="<?php echo BASE_URL . 'assets/images/Logo.jpg'; ?>" alt="Logo">
                        <?php } else { ?>
                            <div class="lu-icon"><i class="bx bx-cloud-upload"></i></div>
                            <div class="lu-title">Subir logo</div>
                            <div class="lu-hint">JPG/JPEG · Click o arrastra aquí</div>
                        <?php } ?>
                    </div>
                </label>
                <div class="logo-actions">
                    <label for="foto" class="btn btn-sm btn-outline-primary"><i class="bx bx-image-add me-1"></i>Cambiar</label>
                    <button type="button" class="btn btn-sm btn-outline-danger" onclick="deleteImg()"><i class="bx bx-trash me-1"></i>Quitar</button>
                </div>
                <input id="foto" class="form-control" type="file" name="foto" accept=".jpg,.jpeg,image/jpeg">
                <input type="hidden" name="foto_actual" id="foto_actual" value="<?php echo htmlspecialchars($data['empresa']['img'] ?? '', ENT_QUOTES); ?>">
                <input type="hidden" name="foto_remove" id="foto_remove" value="<?php echo htmlspecialchars($data['empresa']['img'] ?? '', ENT_QUOTES); ?>">
            </div>
        </div>
    </div>

    <!-- ===== SECCION 2: DATOS FACTURA ELECTRONICA ===== -->
    <div class="form-section">
        <div class="form-section-title"><i class="bx bx-receipt"></i>Datos de Factura Electrónica</div>

        <div class="row g-3">
            <div class="col-md-3">
                <label class="form-label">Total Items <span class="text-danger">*</span></label>
                <div class="input-group">
                    <span class="input-group-text"><i class="fas fa-list-ol"></i></span>
                    <input type="text" id="totalitems" name="totalitems" class="form-control" value="<?php echo htmlspecialchars($data['empresa']['totalitems'] ?? '', ENT_QUOTES); ?>" placeholder="Total Items Factura">
                </div>
                <span id="erroritems" class="text-danger small"></span>
            </div>
            <div class="col-md-3">
                <label class="form-label">Establecimiento <span class="text-danger">*</span></label>
                <div class="input-group">
                    <span class="input-group-text"><i class="fas fa-store"></i></span>
                    <input type="text" id="establecimiento" name="establecimiento" class="form-control" value="<?php echo htmlspecialchars($data['empresa']['establecimiento'] ?? '', ENT_QUOTES); ?>" placeholder="Establecimiento">
                </div>
                <span id="errorEstablecimiento" class="text-danger small"></span>
            </div>
            <div class="col-md-3">
                <label class="form-label">Punto Emisión <span class="text-danger">*</span></label>
                <div class="input-group">
                    <span class="input-group-text"><i class="fas fa-paper-plane"></i></span>
                    <input type="text" id="emision" name="emision" class="form-control" value="<?php echo htmlspecialchars($data['empresa']['puntoemi'] ?? '', ENT_QUOTES); ?>" placeholder="Punto Emisión">
                </div>
                <span id="errorEmision" class="text-danger small"></span>
            </div>
            <div class="col-md-3">
                <label class="form-label">Contabilidad <span class="text-danger">*</span></label>
                <div class="input-group">
                    <span class="input-group-text"><i class="fas fa-calculator"></i></span>
                    <input type="text" id="contabilidad" name="contabilidad" class="form-control" value="<?php echo htmlspecialchars($data['empresa']['contabilidad'] ?? '', ENT_QUOTES); ?>" placeholder="Contabilidad">
                </div>
                <span id="errorContabilidad" class="text-danger small"></span>
            </div>

            <?php if (($data['id_usuario'] ?? 0) == 1) { ?>
                <div class="col-md-4">
                    <label class="form-label">Firma Inicio <span class="text-danger">*</span></label>
                    <div class="input-group">
                        <span class="input-group-text"><i class="fas fa-signature"></i></span>
                        <input type="text" id="firmainicio" name="firmainicio" class="form-control" value="<?php echo htmlspecialchars($data['empresa']['firmainicio'] ?? '', ENT_QUOTES); ?>" placeholder="Ej. 2023-01-01">
                    </div>
                    <span id="errorFirmainicio" class="text-danger small"></span>
                </div>
                <div class="col-md-4">
                    <label class="form-label">Firma Final <span class="text-danger">*</span></label>
                    <div class="input-group">
                        <span class="input-group-text"><i class="fas fa-signature"></i></span>
                        <input type="text" id="firmafinal" name="firmafinal" class="form-control" value="<?php echo htmlspecialchars($data['empresa']['firmafinal'] ?? '', ENT_QUOTES); ?>" placeholder="Ej. 2023-01-01">
                    </div>
                    <span id="errorFirmafinal" class="text-danger small"></span>
                </div>
                <div class="col-md-4">
                    <label class="form-label">Documento <span class="text-danger">*</span></label>
                    <div class="input-group">
                        <span class="input-group-text"><i class="fas fa-file-invoice"></i></span>
                        <input type="text" id="cantidaddocumento" name="cantidaddocumento" class="form-control" value="<?php echo htmlspecialchars($data['empresa']['cantidaddocumento'] ?? '', ENT_QUOTES); ?>" placeholder="Cantidad Documento">
                    </div>
                    <span id="errorCantidaddocumento" class="text-danger small"></span>
                </div>
            <?php } ?>
        </div>
    </div>

    <!-- ===== SECCION 3: FIRMA ELECTRONICA SRI ===== -->
    <div class="form-section">
        <div class="form-section-title"><i class="bx bx-shield-alt-2"></i>Firma Electrónica (SRI)</div>

        <div class="row g-3">
            <div class="col-lg-6">
                <label class="form-label">Archivo de firma (.p12) <small class="text-muted">— solo si vas a actualizar</small></label>
                <div class="input-group">
                    <span class="input-group-text"><i class="fas fa-file-shield"></i></span>
                    <input type="file" id="firma_p12" name="firma_p12" class="form-control" accept=".p12,.pfx">
                </div>
                <?php $existe_firma = file_exists(__DIR__ . '/../../facturaelectronica/public/archivos/token/FIRMA.p12'); ?>
                <div id="firmaEstadoBox" class="mt-2 border rounded <?php echo $existe_firma ? 'border-success' : 'border-danger'; ?>">
                    <?php if ($existe_firma) { ?>
                        <div><i class="bx bx-check-circle text-success"></i> <strong>Firma cargada</strong> (FIRMA.p12) <span class="badge bg-secondary" id="firmaEstadoBadge">Verificando…</span></div>
                        <div id="firmaEstadoDetalle" class="mt-1 text-muted">Cargando datos del certificado…</div>
                    <?php } else { ?>
                        <div><i class="bx bx-x-circle text-danger"></i> <strong>No hay firma cargada</strong> — sube un archivo .p12 para emitir facturas electrónicas.</div>
                    <?php } ?>
                </div>
            </div>
            <div class="col-lg-6">
                <label class="form-label">Contraseña de la firma <span class="text-muted small">(dejar vacío para no cambiar)</span></label>
                <div class="input-group">
                    <span class="input-group-text"><i class="fas fa-key"></i></span>
                    <input type="password" id="firma_password" name="firma_password" class="form-control" placeholder="••••••••">
                    <button type="button" id="btnVerificarFirma" class="btn btn-outline-primary"><i class="bx bx-check-shield me-1"></i>Verificar</button>
                </div>
                <small class="text-muted">Pulsa Verificar para confirmar que la clave es correcta y ver la vigencia del certificado.</small>
                <span id="errorFirmaPassword" class="text-danger small d-block"></span>

                <div class="config-switch mt-3">
                    <?php $isOn = (($data['empresa']['facturaelectronica'] ?? 0) == 1); ?>
                    <input class="form-check-input" type="checkbox" role="switch" id="chelectronica" name="chelectronica" value="1" <?php echo $isOn ? 'checked' : ''; ?>>
                    <label for="chelectronica">
                        <span class="d-block fw-semibold">Facturación Electrónica activa</span>
                        <small class="text-muted">Si está apagado, el sistema emite Órdenes de Venta en lugar de facturas SRI.</small>
                    </label>
                </div>
            </div>
        </div>
    </div>

    <!-- ===== FOOTER: Guardar ===== -->
    <div class="d-flex justify-content-end mb-4">
        <button class="btn btn-primary px-4" type="submit" id="btnAccion">
            <i class="bx bx-save me-1"></i>Actualizar
        </button>
    </div>
</form>


<?php include_once 'views/templates/footer.php'; ?>

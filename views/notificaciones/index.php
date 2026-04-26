<?php include_once 'views/templates/header.php'; ?>

<div class="page-header d-flex justify-content-between align-items-center mb-3">
  <div>
    <h4 class="mb-1 fw-semibold"><i class="bx bx-bell text-primary me-1"></i>Notificaciones</h4>
    <small class="text-muted">Historial de alertas, configuracion y plantillas de mensajes</small>
  </div>
  <div>
    <button class="btn btn-outline-info" id="btnProbarAlerta" type="button"><i class="bx bx-paper-plane"></i> Enviar prueba</button>
    <button class="btn btn-outline-danger" id="btnVaciarHist" type="button"><i class="bx bx-trash"></i> Vaciar historial</button>
  </div>
</div>

<ul class="nav nav-tabs mb-3" id="tabsNotif" role="tablist">
  <li class="nav-item"><button class="nav-link active" data-bs-toggle="tab" data-bs-target="#nav-historial" type="button">Historial</button></li>
  <li class="nav-item"><button class="nav-link" data-bs-toggle="tab" data-bs-target="#nav-config" type="button">Configuracion</button></li>
  <li class="nav-item"><button class="nav-link" data-bs-toggle="tab" data-bs-target="#nav-plantillas" type="button">Plantillas</button></li>
  <li class="nav-item"><button class="nav-link" data-bs-toggle="tab" data-bs-target="#nav-whatsapp" type="button"><i class="bx bxl-whatsapp text-success"></i> WhatsApp</button></li>
</ul>

<div class="tab-content">
  <!-- HISTORIAL -->
  <div class="tab-pane fade show active" id="nav-historial">
    <div class="card"><div class="card-body">
      <table class="table table-bordered table-striped table-hover align-middle" id="tblNotif" style="width:100%;">
        <thead><tr>
          <th>Fecha</th><th>Tipo</th><th>Asunto</th><th>Destino</th><th>Estado</th><th>Cuerpo</th><th></th>
        </tr></thead>
        <tbody></tbody>
      </table>
    </div></div>
  </div>

  <!-- CONFIGURACION -->
  <div class="tab-pane fade" id="nav-config">
    <div class="card"><div class="card-body">
      <h6 class="text-primary fw-semibold mb-3"><i class="bx bx-cog me-1"></i>Personalizacion</h6>
      <div class="mb-3">
        <label class="form-label fw-semibold">Destinatarios (uno por linea, o separados por coma)</label>
        <textarea id="cfg_destinatarios" class="form-control" rows="3" placeholder="admin@empresa.com&#10;contador@empresa.com"><?php echo htmlspecialchars(implode("\n", $data['config']['destinatarios'] ?? [])); ?></textarea>
        <small class="text-muted">Si esta vacio, se usa el correo de USER_SMTP por defecto.</small>
      </div>
      <div class="mb-3">
        <label class="form-label fw-semibold">Rate-limit (segundos entre alertas del mismo tipo)</label>
        <input type="number" id="cfg_rate_limit" class="form-control" min="0" max="86400" value="<?php echo (int)($data['config']['rate_limit_segs'] ?? 3600); ?>">
        <small class="text-muted">Por defecto 3600 (1 hora). Pone 0 para desactivar el rate-limit.</small>
      </div>

      </div>
      <div class="mb-3">
        <label class="form-label fw-semibold">Tipos de alerta activos</label>
        <div class="row" id="cfg_tipos">
          <?php foreach (($data['config']['tipos_activos'] ?? []) as $tipo => $on): ?>
            <div class="col-md-6 mb-1">
              <div class="form-check form-switch">
                <input class="form-check-input cfg-tipo" type="checkbox" data-tipo="<?php echo htmlspecialchars($tipo); ?>" <?php echo $on ? 'checked' : ''; ?>>
                <label class="form-check-label"><?php echo htmlspecialchars($tipo); ?></label>
              </div>
            </div>
          <?php endforeach; ?>
        </div>
      </div>
      <div class="text-end mt-3">
        <button class="btn btn-primary" id="btnGuardarCfg" type="button"><i class="bx bx-save"></i> Guardar configuracion</button>
      </div>
    </div></div>
  </div>

  <!-- PLANTILLAS -->
  <div class="tab-pane fade" id="nav-plantillas">
    <div class="row">
      <div class="col-lg-3 mb-3">
        <div class="card"><div class="card-body p-2">
          <h6 class="fw-semibold mb-2 px-1">Plantillas</h6>
          <div class="list-group" id="plantilla-lista">
            <?php foreach ($data['plantillas'] as $key => $p): ?>
              <button type="button" class="list-group-item list-group-item-action plantilla-item" data-key="<?php echo htmlspecialchars($key); ?>">
                <div class="fw-semibold" style="font-size:0.85em;"><?php echo htmlspecialchars($key); ?></div>
                <small class="text-muted"><?php echo htmlspecialchars($p['descripcion'] ?? ''); ?></small>
              </button>
            <?php endforeach; ?>
          </div>
        </div></div>
      </div>
      <div class="col-lg-9">
        <div class="card"><div class="card-body">
          <h6 class="text-primary fw-semibold mb-3"><i class="bx bx-edit-alt me-1"></i>Editor de plantilla <small class="text-muted" id="plantilla-key">— selecciona una a la izquierda</small></h6>

          <div class="mb-2">
            <label class="form-label fw-semibold">Descripcion</label>
            <input type="text" id="pl_descripcion" class="form-control" placeholder="Descripcion corta de cuando se usa esta plantilla">
          </div>

          <div class="mb-2">
            <label class="form-label fw-semibold">Asunto / Titulo (opcional)</label>
            <input type="text" id="pl_asunto" class="form-control" placeholder="Solo para emails. WhatsApp lo ignora.">
          </div>

          <div class="mb-2">
            <label class="form-label fw-semibold">Cuerpo</label>
            <textarea id="pl_cuerpo" class="form-control" rows="14" style="font-family:monospace;font-size:0.9em;"></textarea>
            <small class="text-muted">Para WhatsApp, usa <code>*texto*</code> para negrita, <code>_texto_</code> para italica, <code>\n</code> es salto de linea.</small>
          </div>

          <div class="d-flex justify-content-between mt-3">
            <button class="btn btn-outline-secondary" id="btnPreviewPlantilla" type="button"><i class="bx bx-show"></i> Previsualizar</button>
            <button class="btn btn-primary" id="btnGuardarPlantilla" type="button"><i class="bx bx-save"></i> Guardar plantilla</button>
          </div>

          <div class="mt-4 pt-3 border-top">
            <h6 class="fw-semibold mb-2">Placeholders disponibles</h6>
            <div class="row" style="font-size:0.85em;">
              <div class="col-md-6">
                <div class="fw-semibold text-primary">Empresa</div>
                <code>{{empresa_nombre}}</code> · <code>{{empresa_ruc}}</code> · <code>{{empresa_correo}}</code><br>
                <code>{{empresa_telefono}}</code> · <code>{{empresa_direccion}}</code> · <code>{{empresa_razon_social}}</code><br>
                <code>{{empresa_cuentas}}</code> · <code>{{empresa_titular_cuenta}}</code>
              </div>
              <div class="col-md-6">
                <div class="fw-semibold text-success">Cliente</div>
                <code>{{cliente_nombre}}</code> · <code>{{cliente_saldo}}</code> · <code>{{cliente_telefono}}</code><br>
                <div class="fw-semibold text-info mt-2">Servicio / Caso</div>
                <code>{{servicio_meses}}</code> · <code>{{caso_problema}}</code> · <code>{{caso_estado}}</code> · <code>{{caso_trabajo}}</code>
              </div>
            </div>
            <small class="text-muted d-block mt-2">Click en cualquier placeholder para insertarlo en el cuerpo.</small>
          </div>
        </div></div>
      </div>
    </div>
  </div>
</div>

  <!-- WHATSAPP -->
  <div class="tab-pane fade" id="nav-whatsapp">
    <div class="row">
      <div class="col-lg-6 mb-3">
        <div class="card h-100">
          <div class="card-body text-center">
            <h6 class="text-success fw-semibold mb-3"><i class="bx bxl-whatsapp me-1"></i>Vincular WhatsApp de la empresa</h6>
            <div id="wa-status-area" class="mb-3">
              <span class="badge bg-secondary" id="wa-status-badge">Sin sesion</span>
              <div class="mt-2"><small class="text-muted">Numero vinculado:</small> <span id="wa-phone" class="fw-semibold">-</span></div>
              <div><small class="text-muted">Sesion:</small> <span id="wa-sess-name">-</span></div>
            </div>
            <div class="d-flex gap-2 justify-content-center flex-wrap">
              <button class="btn btn-success" id="btnVincularWa" type="button"><i class="bx bxl-whatsapp me-1"></i>Vincular WhatsApp</button>
              <button class="btn btn-outline-danger d-none" id="btnCerrarWa" type="button"><i class="bx bx-power-off me-1"></i>Cerrar sesion</button>
            </div>
            <small class="text-muted d-block mt-3">Solo necesitas escanear UNA vez. La sesion queda guardada y se reconecta sola.</small>
          </div>
        </div>
      </div>
      <div class="col-lg-6 mb-3">
        <div class="card h-100"><div class="card-body">
          <h6 class="text-primary fw-semibold mb-2"><i class="bx bx-cog me-1"></i>Configuracion API</h6>
          <div class="mb-2">
            <label class="form-label fw-semibold mb-1">URL de la API</label>
            <input type="text" id="wa_base_url" class="form-control form-control-sm" value="<?php echo htmlspecialchars($data['config']['wa_api']['base_url'] ?? ''); ?>" placeholder="http://131.196.14.35:3005">
            <small class="text-muted">Endpoint del servicio NestJS+Baileys.</small>
          </div>
          <div class="mb-2">
            <label class="form-label fw-semibold mb-1">Telefonos para alertas administrativas</label>
            <textarea id="wa_phones_alerta" class="form-control form-control-sm" rows="3" placeholder="0991234567&#10;0987654321"><?php echo htmlspecialchars(implode("\n", $data['config']['wa_api']['phones_alerta'] ?? [])); ?></textarea>
            <small class="text-muted">Uno por linea. Se les enviara WhatsApp cuando ocurra una alerta tipo error/factura rechazada.</small>
          </div>
          <div class="text-end">
            <button class="btn btn-primary btn-sm" id="btnGuardarWaCfg" type="button"><i class="bx bx-save"></i> Guardar</button>
          </div>
          <hr>
          <h6 class="text-info fw-semibold mb-2 mt-3"><i class="bx bx-paper-plane me-1"></i>Probar envio</h6>
          <div class="input-group input-group-sm">
            <input type="text" id="wa_test_number" class="form-control" placeholder="0991234567">
            <button class="btn btn-info" id="btnProbarWa" type="button">Enviar prueba</button>
          </div>
          <small class="text-muted">Envia "Prueba desde sistema" al numero indicado.</small>
        </div></div>
      </div>
    </div>
  </div>

  <!-- Modal QR WhatsApp -->
  <div class="modal fade" id="modalQrWa" tabindex="-1" data-bs-backdrop="static">
    <div class="modal-dialog modal-dialog-centered">
      <div class="modal-content">
        <div class="modal-header bg-success text-white">
          <h5 class="modal-title"><i class="bx bxl-whatsapp me-1"></i>Escanea con WhatsApp</h5>
          <button class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
        </div>
        <div class="modal-body text-center">
          <p class="mb-2"><b>Pasos:</b></p>
          <ol class="text-start" style="font-size:0.9em;">
            <li>Abre WhatsApp en tu telefono</li>
            <li>Toca <b>Mas opciones</b> &rarr; <b>Dispositivos vinculados</b></li>
            <li>Toca <b>Vincular un dispositivo</b></li>
            <li>Escanea el codigo</li>
          </ol>
          <div id="wa-qr-wrap" class="my-3">
            <div class="spinner-border text-success" role="status"><span class="visually-hidden">Cargando QR...</span></div>
            <div class="text-muted mt-2" id="wa-qr-msg">Generando QR...</div>
          </div>
          <small class="text-muted">El QR se actualiza solo cada ~7s mientras este modal este abierto.</small>
        </div>
      </div>
    </div>
  </div>

  <!-- Modal detalle historial -->
<div class="modal fade" id="modalDetalleNotif" tabindex="-1">
  <div class="modal-dialog modal-lg"><div class="modal-content">
    <div class="modal-header"><h5 class="modal-title">Detalle de notificacion</h5><button class="btn-close" data-bs-dismiss="modal"></button></div>
    <div class="modal-body" id="detalleNotifBody"></div>
  </div></div>
</div>

<!-- Modal preview plantilla -->
<div class="modal fade" id="modalPreviewPlantilla" tabindex="-1">
  <div class="modal-dialog modal-lg"><div class="modal-content">
    <div class="modal-header"><h5 class="modal-title">Previsualizar plantilla</h5><button class="btn-close" data-bs-dismiss="modal"></button></div>
    <div class="modal-body">
      <div class="mb-2"><b>Asunto:</b> <span id="prev_asunto"></span></div>
      <div class="border rounded p-3" style="background:#f6f6f6;font-family:'Segoe UI',sans-serif;white-space:pre-wrap;" id="prev_cuerpo"></div>
      <small class="text-muted d-block mt-2">Vista previa con datos demo. En real, los <code>{{...}}</code> se reemplazan con valores de la empresa y cliente.</small>
    </div>
  </div></div>
</div>


<!-- Modal Probar notificacion -->
<div class="modal fade" id="modalProbarNotif" tabindex="-1">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title"><i class="bx bx-paper-plane me-1"></i>Enviar notificacion de prueba</h5>
        <button class="btn-close" data-bs-dismiss="modal"></button>
      </div>
      <div class="modal-body">
        <div class="alert alert-info" style="font-size:0.85em;">
          Si dejas vacio, se usa la configuracion guardada.
        </div>
        <div class="mb-3">
          <label class="form-label fw-semibold">Email destinatario (opcional)</label>
          <input type="email" id="prueba_email" class="form-control" placeholder="ej. tu@correo.com">
        </div>
      </div>
      <div class="modal-footer">
        <button class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
        <button class="btn btn-primary" id="btnEnviarPrueba" type="button"><i class="bx bx-paper-plane me-1"></i>Enviar prueba</button>
      </div>
    </div>
  </div>
</div>

<?php include_once 'views/templates/footer.php'; ?>

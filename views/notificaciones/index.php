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

    <style>
      .cfg-section .card-header { background: transparent; border-bottom: 1px solid #eef0f4; padding: .9rem 1.25rem; }
      .cfg-section .card-header h6 { margin: 0; font-size: .95rem; letter-spacing: .2px; }
      .cfg-section .card-header .small { color: #6b7280; font-weight: 400; font-size: .78rem; }
      .cfg-section .card-body { padding: 1.25rem; }
      .cfg-help { font-size: .78rem; color: #6b7280; margin-top: .25rem; display: block; }
      .cfg-tipo-item {
        display: flex; align-items: center; gap: .65rem;
        padding: .55rem .75rem; border: 1px solid #eef0f4; border-radius: 10px;
        background: #fafbfd; transition: background .15s, border-color .15s;
      }
      .cfg-tipo-item:hover { background: #f3f5fa; border-color: #dbe2ee; }
      .cfg-tipo-item .form-check { margin: 0; padding-left: 0; min-width: 0; flex: 1; }
      .cfg-tipo-item .form-check-input { float: none; margin: 0; }
      .cfg-tipo-item .form-check-label { font-size: .85rem; color: #1f2937; cursor: pointer; user-select: none; }
      .cfg-tipo-item .tipo-icon { font-size: 18px; color: #6b7280; line-height: 1; }
      .cfg-tipo-item.is-on { border-color: #bfdbfe; background: #eff6ff; }
      .cfg-tipo-item.is-on .tipo-icon { color: #2563eb; }
      .cfg-tipo-item.is-on .form-check-label { color: #1e40af; font-weight: 500; }
      .cfg-savebar {
        position: sticky; bottom: 0; z-index: 5;
        background: linear-gradient(180deg, rgba(248,250,253,0) 0%, #f8fafd 40%);
        padding: 1rem 0 .25rem; margin-top: 1rem;
      }
    </style>

    <div class="card cfg-section mb-3">
      <div class="card-header d-flex align-items-center">
        <i class="bx bx-envelope text-primary me-2" style="font-size:18px;"></i>
        <h6 class="fw-semibold text-dark">Entrega <span class="small ms-1">— a quien y con que frecuencia</span></h6>
      </div>
      <div class="card-body">
        <div class="row g-3">
          <div class="col-lg-7">
            <label class="form-label fw-semibold mb-1" for="cfg_destinatarios">Destinatarios</label>
            <textarea id="cfg_destinatarios" class="form-control" rows="4" placeholder="admin@empresa.com&#10;contador@empresa.com"><?php echo htmlspecialchars(implode("\n", $data['config']['destinatarios'] ?? [])); ?></textarea>
            <small class="cfg-help">Uno por linea o separados por coma. Si esta vacio se usa <code>USER_SMTP</code> por defecto.</small>
          </div>
          <div class="col-lg-5">
            <label class="form-label fw-semibold mb-1" for="cfg_rate_limit">Rate-limit (segundos)</label>
            <div class="input-group">
              <input type="number" id="cfg_rate_limit" class="form-control" min="0" max="86400" value="<?php echo (int)($data['config']['rate_limit_segs'] ?? 3600); ?>">
              <span class="input-group-text">seg</span>
            </div>
            <small class="cfg-help">Pausa minima entre alertas del mismo tipo. <b>3600</b> = 1 hora. <b>0</b> = sin limite.</small>
          </div>
        </div>
      </div>
    </div>

    <div class="card cfg-section">
      <div class="card-header d-flex align-items-center justify-content-between">
        <div class="d-flex align-items-center">
          <i class="bx bx-bell text-primary me-2" style="font-size:18px;"></i>
          <h6 class="fw-semibold text-dark">Tipos de alerta <span class="small ms-1">— activa o desactiva cada categoria</span></h6>
        </div>
        <div class="d-flex gap-1">
          <button type="button" class="btn btn-sm btn-light border" id="cfgTiposAll"><i class="bx bx-check-double"></i> Todos</button>
          <button type="button" class="btn btn-sm btn-light border" id="cfgTiposNone"><i class="bx bx-x"></i> Ninguno</button>
        </div>
      </div>
      <div class="card-body">
        <div class="row g-2" id="cfg_tipos">
          <?php
          $iconMap = [
              'VENCIDA' => 'bx-error-circle',
              'POR_VENCER' => 'bx-time-five',
              'NO_EXISTE' => 'bx-file-blank',
              'CLAVE' => 'bx-key',
              'LECTURA' => 'bx-error',
              'VIGENCIA' => 'bx-calendar-x',
              'CORREO' => 'bx-envelope',
              'CLIENTE' => 'bx-user',
              'FIRMA' => 'bx-pen',
          ];
          foreach (($data['config']['tipos_activos'] ?? []) as $tipo => $on):
              $icon = 'bx-bell';
              foreach ($iconMap as $kw => $ic) { if (strpos($tipo, $kw) !== false) { $icon = $ic; break; } }
              $id = 'cfgTipo_' . preg_replace('/[^a-z0-9]/i', '_', $tipo);
          ?>
            <div class="col-md-6 col-xl-4">
              <div class="cfg-tipo-item <?php echo $on ? 'is-on' : ''; ?>">
                <i class="bx <?php echo $icon; ?> tipo-icon"></i>
                <div class="form-check form-switch">
                  <input class="form-check-input cfg-tipo" type="checkbox" id="<?php echo $id; ?>" data-tipo="<?php echo htmlspecialchars($tipo); ?>" <?php echo $on ? 'checked' : ''; ?>>
                  <label class="form-check-label" for="<?php echo $id; ?>"><?php echo htmlspecialchars($tipo); ?></label>
                </div>
              </div>
            </div>
          <?php endforeach; ?>
        </div>
      </div>
    </div>

    <div class="cfg-savebar d-flex justify-content-end">
      <button class="btn btn-primary px-4" id="btnGuardarCfg" type="button">
        <i class="bx bx-save me-1"></i> Guardar configuracion
      </button>
    </div>

    <script>
      document.addEventListener('change', function(e){
        var t = e.target;
        if (!t.classList || !t.classList.contains('cfg-tipo')) return;
        var box = t.closest('.cfg-tipo-item');
        if (box) box.classList.toggle('is-on', t.checked);
      });
      var allBtn = document.getElementById('cfgTiposAll');
      var noneBtn = document.getElementById('cfgTiposNone');
      function setAll(v){
        document.querySelectorAll('#cfg_tipos .cfg-tipo').forEach(function(cb){
          cb.checked = v;
          var box = cb.closest('.cfg-tipo-item');
          if (box) box.classList.toggle('is-on', v);
        });
      }
      if (allBtn) allBtn.addEventListener('click', function(){ setAll(true); });
      if (noneBtn) noneBtn.addEventListener('click', function(){ setAll(false); });
    </script>
  </div>

  <!-- PLANTILLAS -->
  <div class="tab-pane fade" id="nav-plantillas">

    <style>
      .pl-list .list-group-item { border: 0; border-bottom: 1px solid #f1f3f7; padding: .55rem .75rem; }
      .pl-list .list-group-item:last-child { border-bottom: 0; }
      .pl-list .list-group-item .pl-key { font-size: .8rem; font-weight: 600; color: #1f2937; }
      .pl-list .list-group-item .pl-desc { font-size: .72rem; color: #6b7280; line-height: 1.25; }
      .pl-list .list-group-item.active { background: #eff6ff !important; color: inherit !important; }
      .pl-list .list-group-item.active .pl-key { color: #1e40af; }
      .pl-card .card-body { padding: 1rem 1.15rem; }
      .pl-card label.form-label { margin-bottom: .25rem; font-size: .82rem; }
      .pl-card .form-control { font-size: .88rem; }
      .pl-card #pl_cuerpo { font-family: ui-monospace, SFMono-Regular, Menlo, Consolas, monospace; font-size: .85rem; line-height: 1.45; }
      .pl-help { font-size: .75rem; color: #6b7280; margin-top: .25rem; display: block; }
      .pl-actions { gap: .5rem; }
      .pl-ph-card .card-body { padding: .75rem 1rem; }
      .pl-ph-group { margin-bottom: .35rem; }
      .pl-ph-title { font-size: .72rem; font-weight: 700; text-transform: uppercase; letter-spacing: .4px; margin-bottom: .25rem; }
      .pl-ph-chip {
        display: inline-block; font-family: ui-monospace, Menlo, monospace; font-size: .72rem;
        background: #f3f5fa; color: #374151; border: 1px solid #e5e7eb; border-radius: 6px;
        padding: .12rem .4rem; margin: .1rem .15rem .1rem 0; cursor: pointer; user-select: none;
        transition: background .12s, border-color .12s, color .12s;
      }
      .pl-ph-chip:hover { background: #dbeafe; border-color: #93c5fd; color: #1d4ed8; }
      .pl-ph-empresa { border-left: 3px solid #3b82f6; padding-left: .5rem; }
      .pl-ph-cliente { border-left: 3px solid #10b981; padding-left: .5rem; }
      .pl-ph-servicio { border-left: 3px solid #06b6d4; padding-left: .5rem; }
    </style>

    <div class="row g-3">
      <div class="col-lg-3">
        <div class="card pl-card">
          <div class="card-body p-0">
            <div class="px-3 pt-2 pb-1 d-flex align-items-center justify-content-between">
              <h6 class="fw-semibold mb-0" style="font-size:.85rem;"><i class="bx bx-list-ul text-primary me-1"></i>Plantillas</h6>
              <span class="badge bg-light text-muted border" style="font-size:.7rem;"><?php echo count($data['plantillas'] ?? []); ?></span>
            </div>
            <div class="list-group list-group-flush pl-list" id="plantilla-lista">
              <?php foreach ($data['plantillas'] as $key => $p): ?>
                <button type="button" class="list-group-item list-group-item-action plantilla-item" data-key="<?php echo htmlspecialchars($key); ?>">
                  <div class="pl-key"><?php echo htmlspecialchars($key); ?></div>
                  <div class="pl-desc"><?php echo htmlspecialchars($p['descripcion'] ?? ''); ?></div>
                </button>
              <?php endforeach; ?>
            </div>
          </div>
        </div>
      </div>

      <div class="col-lg-9">
        <div class="card pl-card mb-2">
          <div class="card-body">
            <div class="d-flex align-items-center justify-content-between mb-2">
              <h6 class="text-primary fw-semibold mb-0"><i class="bx bx-edit-alt me-1"></i>Editor <small class="text-muted ms-1" id="plantilla-key">— selecciona una a la izquierda</small></h6>
              <div class="d-flex pl-actions">
                <button class="btn btn-sm btn-outline-secondary" id="btnPreviewPlantilla" type="button"><i class="bx bx-show me-1"></i>Previsualizar</button>
                <button class="btn btn-sm btn-primary" id="btnGuardarPlantilla" type="button"><i class="bx bx-save me-1"></i>Guardar</button>
              </div>
            </div>

            <div class="row g-2">
              <div class="col-md-5">
                <label class="form-label fw-semibold" for="pl_descripcion">Descripcion</label>
                <input type="text" id="pl_descripcion" class="form-control form-control-sm" placeholder="Cuando se usa esta plantilla">
              </div>
              <div class="col-md-7">
                <label class="form-label fw-semibold" for="pl_asunto">Asunto / Titulo <span class="text-muted fw-normal">(solo email)</span></label>
                <input type="text" id="pl_asunto" class="form-control form-control-sm" placeholder="WhatsApp lo ignora">
              </div>
            </div>

            <div class="mt-2">
              <label class="form-label fw-semibold" for="pl_cuerpo">Cuerpo</label>
              <textarea id="pl_cuerpo" class="form-control" rows="9"></textarea>
              <small class="pl-help">WhatsApp: <code>*texto*</code> negrita · <code>_texto_</code> italica · <code>\n</code> salto de linea.</small>
            </div>
          </div>
        </div>

        <div class="card pl-ph-card">
          <div class="card-body">
            <div class="d-flex align-items-center justify-content-between mb-1">
              <span class="fw-semibold" style="font-size:.82rem;"><i class="bx bx-code-curly text-primary me-1"></i>Placeholders <span class="text-muted fw-normal">— click para insertar</span></span>
            </div>
            <div class="row g-2">
              <div class="col-md-5 pl-ph-group pl-ph-empresa">
                <div class="pl-ph-title text-primary">Empresa</div>
                <span class="pl-ph-chip">{{empresa_nombre}}</span><span class="pl-ph-chip">{{empresa_ruc}}</span><span class="pl-ph-chip">{{empresa_correo}}</span><span class="pl-ph-chip">{{empresa_telefono}}</span><span class="pl-ph-chip">{{empresa_direccion}}</span><span class="pl-ph-chip">{{empresa_razon_social}}</span><span class="pl-ph-chip">{{empresa_cuentas}}</span><span class="pl-ph-chip">{{empresa_titular_cuenta}}</span>
              </div>
              <div class="col-md-4 pl-ph-group pl-ph-cliente">
                <div class="pl-ph-title text-success">Cliente</div>
                <span class="pl-ph-chip">{{cliente_nombre}}</span><span class="pl-ph-chip">{{cliente_saldo}}</span><span class="pl-ph-chip">{{cliente_telefono}}</span>
              </div>
              <div class="col-md-3 pl-ph-group pl-ph-servicio">
                <div class="pl-ph-title text-info">Servicio / Caso</div>
                <span class="pl-ph-chip">{{servicio_meses}}</span><span class="pl-ph-chip">{{caso_problema}}</span><span class="pl-ph-chip">{{caso_estado}}</span><span class="pl-ph-chip">{{caso_trabajo}}</span>
              </div>
            </div>
          </div>
        </div>
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

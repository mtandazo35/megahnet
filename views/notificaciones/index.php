<?php include_once 'views/templates/header.php'; ?>

<div class="notif-header d-flex justify-content-between align-items-center mb-3">
  <div class="d-flex align-items-center">
    <div class="notif-header-icon"><i class="bx bx-bell"></i></div>
    <div class="ms-3">
      <h4 class="mb-0 fw-semibold notif-header-title">Notificaciones</h4>
      <small class="text-muted">Historial de alertas, configuracion y plantillas de mensajes</small>
    </div>
  </div>
  <div class="d-flex gap-2">
    <button class="btn btn-sm btn-outline-info notif-action-btn" id="btnProbarAlerta" type="button"><i class="bx bx-paper-plane me-1"></i>Enviar prueba</button>
    <button class="btn btn-sm btn-outline-danger notif-action-btn" id="btnVaciarHist" type="button"><i class="bx bx-trash me-1"></i>Vaciar historial</button>
  </div>
</div>

<ul class="nav notif-tabs mb-3" id="tabsNotif" role="tablist">
  <li class="nav-item"><button class="nav-link active" data-bs-toggle="tab" data-bs-target="#nav-historial" type="button"><i class="bx bx-history me-1"></i>Historial</button></li>
  <li class="nav-item"><button class="nav-link" data-bs-toggle="tab" data-bs-target="#nav-config" type="button"><i class="bx bx-cog me-1"></i>Configuracion</button></li>
  <li class="nav-item"><button class="nav-link" data-bs-toggle="tab" data-bs-target="#nav-plantillas" type="button"><i class="bx bx-file me-1"></i>Plantillas</button></li>
  <li class="nav-item"><button class="nav-link" data-bs-toggle="tab" data-bs-target="#nav-correo" type="button"><i class="bx bx-envelope me-1"></i>Correo</button></li>
  <li class="nav-item"><button class="nav-link" data-bs-toggle="tab" data-bs-target="#nav-whatsapp" type="button"><i class="bx bxl-whatsapp me-1"></i>WhatsApp</button></li>
</ul>

<style>
  /* === Polish global de Notificaciones === */
  .notif-header-icon {
    width: 44px; height: 44px;
    display: inline-flex; align-items: center; justify-content: center;
    background: linear-gradient(135deg, #2563eb 0%, #3b82f6 100%);
    color: #fff; border-radius: 12px; font-size: 22px;
    box-shadow: 0 4px 10px rgba(37,99,235,.25);
  }
  .notif-header-title { font-size: 1.35rem; color: #111827; letter-spacing: -.01em; }
  .notif-action-btn { font-weight: 500; border-radius: 8px; }

  /* Tabs estilo pill */
  .notif-tabs {
    background: #f3f5fa;
    border-radius: 12px;
    padding: 4px;
    gap: 2px;
    border: 1px solid #e5e7eb;
  }
  .notif-tabs .nav-link {
    color: #4b5563;
    border: 0 !important;
    background: transparent;
    border-radius: 9px !important;
    padding: .5rem .9rem;
    font-size: .87rem;
    font-weight: 500;
    transition: background .15s, color .15s, box-shadow .15s;
  }
  .notif-tabs .nav-link:hover { color: #2563eb; background: rgba(255,255,255,.7); }
  .notif-tabs .nav-link.active {
    color: #2563eb !important;
    background: #fff !important;
    box-shadow: 0 2px 6px rgba(0,0,0,.06);
    font-weight: 600;
  }
  .notif-tabs .nav-link i { font-size: 1.05rem; vertical-align: -2px; }

  /* Cards con shadow sutil y border-radius mayor */
  #nav-historial .card,
  #nav-config .card,
  #nav-plantillas .card,
  #nav-correo .card,
  #nav-whatsapp .card {
    border: 1px solid #e9ecef !important;
    border-radius: 12px !important;
    box-shadow: 0 1px 3px rgba(0,0,0,.04);
    background: #fff;
  }

  /* Tablas mas modernas */
  #nav-historial .table {
    border: 0 !important;
    margin: 0;
  }
  #nav-historial .table thead th {
    border-top: 0 !important;
    border-bottom: 1px solid #e5e7eb !important;
    background: transparent;
    color: #6b7280;
    font-size: .72rem;
    font-weight: 700;
    text-transform: uppercase;
    letter-spacing: .4px;
    padding: .85rem .75rem;
  }
  #nav-historial .table tbody td {
    border-color: #f1f3f7 !important;
    padding: .75rem;
    font-size: .87rem;
    color: #374151;
    vertical-align: middle;
  }
  #nav-historial .table tbody tr:hover td { background: #f9fafb; }
  #nav-historial .table .badge { font-weight: 500; padding: .35em .65em; border-radius: 6px; }

  /* WhatsApp tab: hero card mas vivo */
  #nav-whatsapp .card { padding: 0; }
  #wa-status-area { padding: 1.25rem; border-radius: 10px; background: #f9fafb; margin-bottom: 1rem; }
  #wa-status-badge { font-size: .75rem; padding: .35em .85em; border-radius: 999px; font-weight: 600; letter-spacing: .3px; }
  #wa-status-badge.bg-success::before {
    content: ""; display: inline-block; width: 7px; height: 7px;
    border-radius: 50%; background: #fff; margin-right: 6px; vertical-align: 1px;
    animation: wa-pulse 1.8s ease-in-out infinite;
  }
  @keyframes wa-pulse {
    0%, 100% { opacity: 1; }
    50% { opacity: .35; }
  }
  #wa-phone { font-size: 1.05rem; color: #111827; letter-spacing: .02em; }
  #wa-sess-name { color: #6b7280; font-size: .8rem; font-family: ui-monospace, Menlo, monospace; }
  #btnVincularWa, #btnCerrarWa { border-radius: 10px; padding: .6rem 1.1rem; font-weight: 500; }
  #btnVincularWa { box-shadow: 0 4px 12px rgba(34,197,94,.25); }

  /* Inputs mas finos en toda la pagina */
  #nav-config .form-control,
  #nav-config .form-select,
  #nav-correo .form-control,
  #nav-correo .form-select,
  #nav-whatsapp .form-control,
  #nav-whatsapp .form-select {
    border-color: #e5e7eb;
    border-radius: 8px;
    transition: border-color .12s, box-shadow .12s;
  }
  #nav-config .form-control:focus,
  #nav-config .form-select:focus,
  #nav-correo .form-control:focus,
  #nav-correo .form-select:focus,
  #nav-whatsapp .form-control:focus,
  #nav-whatsapp .form-select:focus {
    border-color: #93c5fd;
    box-shadow: 0 0 0 3px rgba(37,99,235,.12);
  }

  /* Botones primarios con shadow sutil */
  #nav-config .btn-primary,
  #nav-correo .btn-primary,
  #nav-plantillas .btn-primary {
    border-radius: 8px;
    box-shadow: 0 2px 6px rgba(37,99,235,.2);
    font-weight: 500;
  }
  #nav-config .btn-primary:hover,
  #nav-correo .btn-primary:hover,
  #nav-plantillas .btn-primary:hover {
    box-shadow: 0 4px 10px rgba(37,99,235,.3);
    transform: translateY(-1px);
  }

  /* Modales mas compactos en notificaciones */
  #nav-historial .modal .modal-dialog,
  #nav-config .modal .modal-dialog,
  #nav-plantillas .modal .modal-dialog,
  #nav-correo .modal .modal-dialog,
  #nav-whatsapp .modal .modal-dialog,
  #modalDetalleNotif .modal-dialog,
  #modalPreviewPlantilla .modal-dialog,
  #modalProbarNotif .modal-dialog,
  #modalQrWa .modal-dialog {
    max-width: 480px;
  }
  #modalDetalleNotif .modal-body,
  #modalPreviewPlantilla .modal-body,
  #modalProbarNotif .modal-body {
    padding: 1rem 1.25rem;
    font-size: .9rem;
  }
  #modalDetalleNotif .modal-header,
  #modalPreviewPlantilla .modal-header,
  #modalProbarNotif .modal-header,
  #modalQrWa .modal-header {
    padding: .65rem 1rem;
  }
  #modalDetalleNotif .modal-title,
  #modalPreviewPlantilla .modal-title,
  #modalProbarNotif .modal-title,
  #modalQrWa .modal-title { font-size: 1rem; }

  /* SweetAlert2 mas compacto */
  .swal2-popup { width: 380px !important; padding: 1.25rem 1rem !important; font-size: .9rem !important; }
  .swal2-title { font-size: 1.05rem !important; padding: .25rem 0 .5rem !important; }
  .swal2-html-container { font-size: .85rem !important; margin: .25rem 0 0 !important; }
  .swal2-icon { width: 48px !important; height: 48px !important; margin: .5rem auto !important; }
  .swal2-icon .swal2-icon-content { font-size: 1.6rem !important; }
  .swal2-actions { margin-top: .8rem !important; gap: .35rem !important; }
  .swal2-styled { padding: .35rem .9rem !important; font-size: .85rem !important; }
</style>

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
            <textarea id="cfg_destinatarios" class="form-control" rows="2" style="resize:vertical;min-height:42px;" placeholder="admin@empresa.com&#10;contador@empresa.com"><?php echo htmlspecialchars(implode("\n", $data['config']['destinatarios'] ?? [])); ?></textarea>
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
      /* Layout: textarea grande para llenar espacio, page-wrapper natural */
      body:has(#nav-plantillas.show.active) .page-wrapper,
      body:has(#nav-plantillas.active) .page-wrapper {
        height: auto !important;
        min-height: auto !important;
      }
      body:has(#nav-plantillas.show.active) .page-content,
      body:has(#nav-plantillas.active) .page-content {
        padding-bottom: 0 !important;
      }
      #nav-plantillas .row > [class*="col-"] { display: flex; flex-direction: column; }
      .pl-list-card { flex: 0 0 auto; }
      .pl-list-card > .card-body { padding: 0 !important; }
      .pl-card { flex: 1 1 auto; display: flex; flex-direction: column; }
      .pl-card .card-body { display: flex; flex-direction: column; }
      #pl_cuerpo { flex: 1 1 auto; min-height: 320px; resize: vertical; }
      .pl-ph-card { flex: 0 0 auto; }
      /* Visual */
      .pl-list .list-group-item { border: 0; border-bottom: 1px solid #f1f3f7; padding: .55rem .75rem; }
      .pl-list .list-group-item:last-child { border-bottom: 0; }
      .pl-list .list-group-item .pl-key { font-size: .8rem; font-weight: 600; color: #1f2937; }
      .pl-list .list-group-item .pl-desc { font-size: .72rem; color: #6b7280; line-height: 1.25; }
      .pl-list .list-group-item.active { background: #eff6ff !important; color: inherit !important; }
      .pl-list .list-group-item.active .pl-key { color: #1e40af; }
      .pl-card .card-body { padding: 1rem 1.25rem 1.15rem; }
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
        <div class="card pl-list-card">
          <div class="card-body">
            <div class="px-3 pt-2 pb-1 d-flex align-items-center justify-content-between" style="flex:0 0 auto;">
              <h6 class="fw-semibold mb-0" style="font-size:.85rem;"><i class="bx bx-list-ul text-primary me-1"></i>Plantillas <span class="badge bg-light text-muted border ms-1" id="plantillaCount" style="font-size:.7rem;"><?php echo count($data['plantillas'] ?? []); ?></span></h6>
              <button type="button" class="btn btn-sm btn-primary" id="btnNuevaPlantilla" title="Nueva plantilla" style="padding:.15rem .5rem;font-size:.75rem;">
                <i class="bx bx-plus"></i> Nueva
              </button>
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

      <div class="col-lg-7">
        <div class="card pl-card mb-2">
          <div class="card-body">
            <div class="d-flex align-items-center justify-content-between mb-3 pb-2 border-bottom">
              <h6 class="text-primary fw-semibold mb-0"><i class="bx bx-edit-alt me-1"></i>Editor <small class="text-muted ms-1" id="plantilla-key">— selecciona una a la izquierda</small></h6>
              <div class="d-flex gap-2">
                <button class="btn btn-sm btn-outline-secondary" id="btnPreviewPlantilla" type="button"><i class="bx bx-show me-1"></i>Previsualizar</button>
                <button class="btn btn-sm btn-primary" id="btnGuardarPlantilla" type="button"><i class="bx bx-save me-1"></i>Guardar</button>
              </div>
            </div>

            <div class="row g-3 mb-3">
              <div class="col-md-5">
                <label class="form-label fw-semibold mb-1" for="pl_descripcion">Descripcion</label>
                <input type="text" id="pl_descripcion" class="form-control form-control-sm" placeholder="Cuando se usa esta plantilla">
              </div>
              <div class="col-md-7">
                <label class="form-label fw-semibold mb-1" for="pl_asunto">Asunto / Titulo <span class="text-muted fw-normal">(solo email)</span></label>
                <input type="text" id="pl_asunto" class="form-control form-control-sm" placeholder="WhatsApp lo ignora">
              </div>
            </div>

            <div>
              <label class="form-label fw-semibold mb-1" for="pl_cuerpo">Cuerpo</label>
              <textarea id="pl_cuerpo" class="form-control" rows="10"></textarea>
              <small class="pl-help mt-1">WhatsApp: <code>*texto*</code> negrita · <code>_texto_</code> italica · <code>\n</code> salto de linea.</small>
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

  <!-- CORREO (SMTP) -->
  <div class="tab-pane fade" id="nav-correo">
    <style>
      /* Aplica el mismo patron de Configuracion (cfg-section) */
      .smtp-card .card-header { background: transparent; border-bottom: 1px solid #eef0f4; padding: .9rem 1.25rem; }
      .smtp-card .card-header h6 { margin: 0; font-size: .95rem; letter-spacing: .2px; }
      .smtp-card .card-header .small { color: #6b7280; font-weight: 400; font-size: .78rem; }
      .smtp-card .card-body { padding: 1.25rem; }
      .smtp-card .form-label { margin-bottom: .25rem; }
      .smtp-pass-wrap { position: relative; }
      .smtp-pass-toggle { position: absolute; right: .65rem; top: 50%; transform: translateY(-50%); cursor: pointer; color: #6b7280; font-size: 18px; }
      .smtp-pass-toggle:hover { color: #2563eb; }
      #smtp_password { padding-right: 2.25rem; }
    </style>

    <?php $smtp = $data['config']['smtp'] ?? []; ?>

    <div class="card cfg-section smtp-card mb-3">
      <div class="card-header d-flex align-items-center justify-content-between">
        <div class="d-flex align-items-center">
          <i class="bx bx-server text-primary me-2" style="font-size:18px;"></i>
          <h6 class="fw-semibold text-dark">Servidor SMTP <span class="small ms-1">— credenciales para enviar los correos</span></h6>
        </div>
        <div class="dropdown">
          <button class="btn btn-sm btn-light border dropdown-toggle" type="button" data-bs-toggle="dropdown" aria-expanded="false">
            <i class="bx bx-list-plus me-1"></i>Cargar preset
          </button>
          <ul class="dropdown-menu dropdown-menu-end" style="min-width: 240px;">
            <li><h6 class="dropdown-header"><i class="bx bx-buildings me-1"></i>Corporativo</h6></li>
            <li><a class="dropdown-item smtp-preset" href="#" data-preset="cpanel"><i class="bx bx-server me-2 text-primary"></i>cPanel / WHM <small class="text-muted d-block ps-4">mail.tu-dominio.com (auto)</small></a></li>
            <li><a class="dropdown-item smtp-preset" href="#" data-preset="m365"><i class="bx bxl-microsoft me-2 text-primary"></i>Microsoft 365 <small class="text-muted d-block ps-4">smtp.office365.com:587</small></a></li>
            <li><a class="dropdown-item smtp-preset" href="#" data-preset="hostinger"><i class="bx bx-cloud me-2 text-warning"></i>Hostinger <small class="text-muted d-block ps-4">smtp.hostinger.com:465</small></a></li>
            <li><hr class="dropdown-divider"></li>
            <li><h6 class="dropdown-header"><i class="bx bx-user me-1"></i>Publicos</h6></li>
            <li><a class="dropdown-item smtp-preset" href="#" data-preset="gmail"><i class="bx bxl-google me-2 text-danger"></i>Gmail <small class="text-muted d-block ps-4">smtp.gmail.com:465</small></a></li>
            <li><a class="dropdown-item smtp-preset" href="#" data-preset="outlook"><i class="bx bxl-microsoft me-2 text-info"></i>Outlook <small class="text-muted d-block ps-4">smtp.office365.com:587</small></a></li>
            <li><a class="dropdown-item smtp-preset" href="#" data-preset="zoho"><i class="bx bx-mail-send me-2 text-warning"></i>Zoho <small class="text-muted d-block ps-4">smtp.zoho.com:465</small></a></li>
            <li><hr class="dropdown-divider"></li>
            <li><h6 class="dropdown-header"><i class="bx bx-paper-plane me-1"></i>Transaccionales</h6></li>
            <li><a class="dropdown-item smtp-preset" href="#" data-preset="sendgrid"><i class="bx bx-send me-2 text-success"></i>SendGrid <small class="text-muted d-block ps-4">smtp.sendgrid.net:587</small></a></li>
            <li><a class="dropdown-item smtp-preset" href="#" data-preset="mailgun"><i class="bx bx-broadcast me-2 text-success"></i>Mailgun <small class="text-muted d-block ps-4">smtp.mailgun.org:587</small></a></li>
            <li><hr class="dropdown-divider"></li>
            <li><a class="dropdown-item text-danger smtp-preset" href="#" data-preset="clear"><i class="bx bx-eraser me-2"></i>Limpiar campos</a></li>
          </ul>
        </div>
      </div>
      <div class="card-body">
        <div class="row g-3">
          <div class="col-md-7">
            <label class="form-label fw-semibold mb-1" for="smtp_host">Host</label>
            <input type="text" id="smtp_host" class="form-control" placeholder="smtp.gmail.com"
              value="<?php echo htmlspecialchars($smtp['host'] ?? ''); ?>">
            <small class="cfg-help">Servidor SMTP del proveedor de correo.</small>
          </div>
          <div class="col-md-3">
            <label class="form-label fw-semibold mb-1" for="smtp_port">Puerto</label>
            <input type="number" id="smtp_port" class="form-control" min="1" max="65535"
              value="<?php echo (int)($smtp['port'] ?? 465); ?>">
            <small class="cfg-help">465 SSL · 587 TLS</small>
          </div>
          <div class="col-md-2">
            <label class="form-label fw-semibold mb-1" for="smtp_secure">Encriptacion</label>
            <select id="smtp_secure" class="form-select">
              <option value="1" <?php echo (int)($smtp['secure'] ?? 1) === 1 ? 'selected' : ''; ?>>SSL</option>
              <option value="0" <?php echo (int)($smtp['secure'] ?? 1) === 0 ? 'selected' : ''; ?>>TLS</option>
            </select>
          </div>

          <div class="col-md-6">
            <label class="form-label fw-semibold mb-1" for="smtp_user">Usuario / Email</label>
            <input type="email" id="smtp_user" class="form-control" placeholder="cuenta@gmail.com"
              value="<?php echo htmlspecialchars($smtp['user'] ?? ''); ?>">
            <small class="cfg-help">La cuenta que se autentica en el SMTP.</small>
          </div>
          <div class="col-md-6">
            <label class="form-label fw-semibold mb-1" for="smtp_password">Contrasena</label>
            <div class="smtp-pass-wrap">
              <input type="password" id="smtp_password" class="form-control" autocomplete="new-password"
                value="<?php echo !empty($smtp['password']) ? '********' : ''; ?>"
                placeholder="<?php echo !empty($smtp['password']) ? 'Deja con asteriscos para mantener' : 'App password o contrasena'; ?>">
              <i class="bx bx-show smtp-pass-toggle" id="smtpPassToggle" title="Mostrar/ocultar"></i>
            </div>
            <small class="cfg-help">Para Gmail usa una <a href="https://myaccount.google.com/apppasswords" target="_blank" rel="noopener">App password</a>.</small>
          </div>

          <div class="col-md-6">
            <label class="form-label fw-semibold mb-1" for="smtp_from_name">Nombre remitente</label>
            <input type="text" id="smtp_from_name" class="form-control" placeholder="Sistema MAAT"
              value="<?php echo htmlspecialchars($smtp['from_name'] ?? ''); ?>">
            <small class="cfg-help">Aparece como nombre del remitente. Si vacio usa <code>TITLE</code>.</small>
          </div>
          <div class="col-md-6">
            <label class="form-label fw-semibold mb-1" for="smtp_from_email">Email "From" <span class="text-muted fw-normal">(opcional)</span></label>
            <input type="email" id="smtp_from_email" class="form-control" placeholder="(usa el usuario por defecto)"
              value="<?php echo htmlspecialchars($smtp['from_email'] ?? ''); ?>">
            <small class="cfg-help">Solo si quieres un email distinto al usuario SMTP.</small>
          </div>
        </div>
      </div>
    </div>

    <div class="card cfg-section smtp-card">
      <div class="card-header d-flex align-items-center">
        <i class="bx bx-paper-plane text-info me-2" style="font-size:18px;"></i>
        <h6 class="fw-semibold text-dark">Probar envio <span class="small ms-1">— sin necesidad de guardar antes</span></h6>
      </div>
      <div class="card-body">
        <div class="row g-2 align-items-end">
          <div class="col-md-8">
            <label class="form-label fw-semibold mb-1" for="smtp_test_email">Email destino</label>
            <input type="email" id="smtp_test_email" class="form-control" placeholder="tu@correo.com">
            <small class="cfg-help">Usa los valores actuales del formulario para enviar el correo de prueba.</small>
          </div>
          <div class="col-md-4 d-flex justify-content-end">
            <button class="btn btn-outline-info w-100" id="btnProbarSmtp" type="button"><i class="bx bx-paper-plane me-1"></i>Probar conexion</button>
          </div>
        </div>
      </div>
    </div>

    <div class="cfg-savebar d-flex justify-content-end">
      <button class="btn btn-primary px-4" id="btnGuardarSmtp" type="button">
        <i class="bx bx-save me-1"></i>Guardar configuracion
      </button>
    </div>

    <script>
      (function(){
        var presets = {
          // Publicos
          gmail:    { host: 'smtp.gmail.com',       port: 465, secure: 1 },
          outlook:  { host: 'smtp.office365.com',   port: 587, secure: 0 },
          zoho:     { host: 'smtp.zoho.com',        port: 465, secure: 1 },
          // Corporativos / hosting
          m365:     { host: 'smtp.office365.com',   port: 587, secure: 0 },
          hostinger:{ host: 'smtp.hostinger.com',   port: 465, secure: 1 },
          // Servicios transaccionales
          sendgrid: { host: 'smtp.sendgrid.net',    port: 587, secure: 0 },
          mailgun:  { host: 'smtp.mailgun.org',     port: 587, secure: 0 }
        };
        document.querySelectorAll('.smtp-preset').forEach(function(b){
          b.addEventListener('click', function(e){
            e.preventDefault();
            var key = b.dataset.preset;
            // Especiales: cPanel deduce host del dominio del usuario; clear vacia todo
            if (key === 'cpanel') {
              var user = (document.getElementById('smtp_user').value || '').trim();
              var host = 'mail.tu-dominio.com';
              if (user.indexOf('@') > 0) {
                host = 'mail.' + user.split('@')[1];
              }
              document.getElementById('smtp_host').value = host;
              document.getElementById('smtp_port').value = 465;
              document.getElementById('smtp_secure').value = '1';
              return;
            }
            if (key === 'clear') {
              document.getElementById('smtp_host').value = '';
              document.getElementById('smtp_port').value = '';
              document.getElementById('smtp_user').value = '';
              document.getElementById('smtp_password').value = '';
              document.getElementById('smtp_from_name').value = '';
              document.getElementById('smtp_from_email').value = '';
              return;
            }
            var p = presets[key];
            if (!p) return;
            document.getElementById('smtp_host').value = p.host;
            document.getElementById('smtp_port').value = p.port;
            document.getElementById('smtp_secure').value = String(p.secure);
          });
        });

        var pass = document.getElementById('smtp_password');
        var toggle = document.getElementById('smtpPassToggle');
        if (toggle && pass) {
          toggle.addEventListener('click', function(){
            if (pass.type === 'password') { pass.type = 'text'; toggle.classList.replace('bx-show','bx-hide'); }
            else { pass.type = 'password'; toggle.classList.replace('bx-hide','bx-show'); }
          });
        }
        // Si el usuario hace click en el campo y solo tiene ********, lo limpia
        if (pass) {
          pass.addEventListener('focus', function(){
            if (pass.value === '********') pass.value = '';
          });
        }

        function buildPayload(){
          return {
            host:       document.getElementById('smtp_host').value.trim(),
            port:       parseInt(document.getElementById('smtp_port').value, 10) || 465,
            secure:     parseInt(document.getElementById('smtp_secure').value, 10),
            user:       document.getElementById('smtp_user').value.trim(),
            password:   document.getElementById('smtp_password').value,
            from_name:  document.getElementById('smtp_from_name').value.trim(),
            from_email: document.getElementById('smtp_from_email').value.trim()
          };
        }

        var saveBtn = document.getElementById('btnGuardarSmtp');
        if (saveBtn) saveBtn.addEventListener('click', function(){
          saveBtn.disabled = true; saveBtn.innerHTML = '<i class="bx bx-loader bx-spin me-1"></i>Guardando...';
          fetch(base_url + 'notificaciones/guardarConfig', {
            method: 'POST', credentials: 'same-origin',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ smtp: buildPayload() })
          }).then(function(r){ return r.json(); }).then(function(d){
            if (d.ok) {
              if (typeof Swal !== 'undefined') Swal.fire({icon:'success', title:'SMTP guardado', timer:1800, showConfirmButton:false});
              else alert('SMTP guardado');
              if (pass && pass.value && pass.value !== '********') pass.value = '********';
            } else {
              alert('Error: ' + (d.msg || 'desconocido'));
            }
          }).catch(function(e){ alert('Error de red: ' + e.message); })
            .finally(function(){ saveBtn.disabled = false; saveBtn.innerHTML = '<i class="bx bx-save me-1"></i>Guardar SMTP'; });
        });

        var testBtn = document.getElementById('btnProbarSmtp');
        if (testBtn) testBtn.addEventListener('click', function(){
          var dest = document.getElementById('smtp_test_email').value.trim();
          if (!dest) { alert('Pon un email destino para la prueba'); return; }
          testBtn.disabled = true; testBtn.innerHTML = '<i class="bx bx-loader bx-spin me-1"></i>Enviando...';
          fetch(base_url + 'notificaciones/probarSmtp', {
            method: 'POST', credentials: 'same-origin',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ smtp: buildPayload(), test_email: dest })
          }).then(function(r){ return r.json(); }).then(function(d){
            if (d.ok) {
              if (typeof Swal !== 'undefined') Swal.fire({icon:'success', title:'Correo enviado', text:'Revisa la bandeja de ' + dest, timer:3000});
              else alert('Correo enviado a ' + dest);
            } else {
              if (typeof Swal !== 'undefined') Swal.fire({icon:'error', title:'No se pudo enviar', text: d.error || 'Revisa host/usuario/contrasena'});
              else alert('No se pudo enviar: ' + (d.error || 'revisa los datos'));
            }
          }).catch(function(e){ alert('Error de red: ' + e.message); })
            .finally(function(){ testBtn.disabled = false; testBtn.innerHTML = '<i class="bx bx-paper-plane me-1"></i>Probar conexion'; });
        });
      })();
    </script>
  </div>

  <!-- WHATSAPP -->
  <div class="tab-pane fade" id="nav-whatsapp">
    <div class="row">
      <div class="col-lg-6 mb-3">
        <div class="card h-100">
          <div class="card-body">
            <div class="text-center">
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
            <hr>
            <h6 class="text-info fw-semibold mb-2 mt-3"><i class="bx bx-paper-plane me-1"></i>Probar envio</h6>
            <form autocomplete="off" onsubmit="return false;">
              <div class="input-group input-group-sm">
                <input type="text" id="wa_test_number" class="form-control" placeholder="0991234567" autocomplete="off">
                <button class="btn btn-info" id="btnProbarWa" type="button">Enviar prueba</button>
              </div>
            </form>
            <small class="text-muted">Envia "Prueba desde sistema" al numero indicado.</small>
          </div>
        </div>
      </div>
      <div class="col-lg-6 mb-3">
        <div class="card h-100"><div class="card-body">
          <h6 class="text-primary fw-semibold mb-2"><i class="bx bx-cog me-1"></i>Configuracion API</h6>
          <div class="mb-2">
            <label class="form-label fw-semibold mb-1">Telefonos para alertas administrativas</label>
            <textarea id="wa_phones_alerta" class="form-control form-control-sm" rows="3" placeholder="0991234567&#10;0987654321"><?php echo htmlspecialchars(implode("\n", $data['config']['wa_api']['phones_alerta'] ?? [])); ?></textarea>
            <small class="text-muted">Uno por linea. Se les enviara WhatsApp cuando ocurra una alerta tipo error/factura rechazada.</small>
          </div>
          <div class="text-end">
            <button class="btn btn-primary btn-sm" id="btnGuardarWaCfg" type="button"><i class="bx bx-save"></i> Guardar</button>
          </div>
        </div></div>
      </div>
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
  <div class="modal-dialog"><div class="modal-content">
    <div class="modal-header"><h5 class="modal-title">Detalle de notificacion</h5><button class="btn-close" data-bs-dismiss="modal"></button></div>
    <div class="modal-body" id="detalleNotifBody"></div>
  </div></div>
</div>

<!-- Modal preview plantilla -->
<div class="modal fade" id="modalPreviewPlantilla" tabindex="-1">
  <div class="modal-dialog"><div class="modal-content">
    <div class="modal-header"><h5 class="modal-title">Previsualizar plantilla</h5><button class="btn-close" data-bs-dismiss="modal"></button></div>
    <div class="modal-body">
      <div class="mb-2"><b>Asunto:</b> <span id="prev_asunto"></span></div>
      <div class="border rounded p-3" style="background:#f6f6f6;font-family:'Segoe UI',sans-serif;white-space:pre-wrap;" id="prev_cuerpo"></div>
      <small class="text-muted d-block mt-2">Vista previa con datos demo. En real, los <code>{{...}}</code> se reemplazan con valores de la empresa y cliente.</small>
    </div>
  </div></div>
</div>


<!-- Modal Nueva plantilla -->
<div class="modal fade" id="modalNuevaPlantilla" tabindex="-1">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title"><i class="bx bx-plus-circle me-1 text-primary"></i>Nueva plantilla</h5>
        <button class="btn-close" data-bs-dismiss="modal"></button>
      </div>
      <div class="modal-body">
        <div class="mb-3">
          <label class="form-label fw-semibold mb-1" for="np_key">Identificador (key)</label>
          <input type="text" id="np_key" class="form-control" placeholder="ej. recordatorio_vencimiento" autocomplete="off">
          <small class="text-muted">Solo letras, numeros y guion bajo. Se usa internamente para invocar la plantilla.</small>
        </div>
        <div class="mb-3">
          <label class="form-label fw-semibold mb-1" for="np_desc">Descripcion</label>
          <input type="text" id="np_desc" class="form-control" placeholder="Cuando se usa esta plantilla">
        </div>
        <div class="alert alert-light border" style="font-size:.8rem;">
          <i class="bx bx-info-circle text-info me-1"></i>
          Despues podras editar el asunto y el cuerpo en el editor.
        </div>
      </div>
      <div class="modal-footer">
        <button class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
        <button class="btn btn-primary" id="btnCrearPlantilla" type="button"><i class="bx bx-check me-1"></i>Crear</button>
      </div>
    </div>
  </div>
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

<script>
(function(){
  function init() {
    var modalEl = document.getElementById('modalNuevaPlantilla');
    var btnOpen = document.getElementById('btnNuevaPlantilla');
    var btnCrear = document.getElementById('btnCrearPlantilla');
    var inpKey = document.getElementById('np_key');
    var inpDesc = document.getElementById('np_desc');
    if (!modalEl || !btnOpen || !btnCrear || !inpKey || !inpDesc) return;
    if (btnOpen.dataset.bound === '1') return;
    btnOpen.dataset.bound = '1';

    inpKey.addEventListener('input', function(){
      var v = this.value.toLowerCase().replace(/[^a-z0-9_]/g, '_').replace(/_+/g, '_');
      if (v !== this.value) { var pos = this.selectionStart; this.value = v; this.setSelectionRange(pos, pos); }
    });

    btnOpen.addEventListener('click', function(){
      inpKey.value = ''; inpDesc.value = '';
      var modal = bootstrap.Modal.getOrCreateInstance(modalEl);
      modal.show();
      setTimeout(function(){ inpKey.focus(); }, 250);
    });

    btnCrear.addEventListener('click', function(){
      var key = (inpKey.value || '').trim();
      var desc = (inpDesc.value || '').trim();
      if (!key) {
        if (typeof Swal !== 'undefined') Swal.fire({icon:'warning', title:'Falta el identificador', text:'Escribe un nombre para la plantilla'});
        else alert('Escribe un nombre para la plantilla');
        return;
      }
      if (document.querySelector('.plantilla-item[data-key="' + key + '"]')) {
        if (typeof Swal !== 'undefined') Swal.fire({icon:'error', title:'Ya existe', text:'Ya existe una plantilla con ese identificador'});
        else alert('Ya existe una plantilla con ese identificador');
        return;
      }
      btnCrear.disabled = true;
      btnCrear.innerHTML = '<i class="bx bx-loader bx-spin me-1"></i>Creando...';
      fetch(base_url + 'notificaciones/guardarPlantilla', {
        method: 'POST', credentials: 'same-origin',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ key: key, descripcion: desc, asunto: '', cuerpo: '' })
      }).then(function(r){ return r.json(); }).then(function(d){
        if (!d.ok) {
          if (typeof Swal !== 'undefined') Swal.fire({icon:'error', title:'Error', text: d.msg || 'No se pudo crear'});
          else alert(d.msg || 'No se pudo crear');
          return;
        }
        var lista = document.getElementById('plantilla-lista');
        var btn = document.createElement('button');
        btn.type = 'button';
        btn.className = 'list-group-item list-group-item-action plantilla-item';
        btn.dataset.key = key;
        btn.innerHTML = '<div class="pl-key">' + key + '</div><div class="pl-desc">' + (desc || '') + '</div>';
        btn.addEventListener('click', function(){
          document.querySelectorAll('.plantilla-item').forEach(function(b){ b.classList.remove('active'); });
          btn.classList.add('active');
          window.plantillaActual = key;
          document.querySelector('#plantilla-key').textContent = '— ' + key;
          document.querySelector('#pl_descripcion').value = desc || '';
          document.querySelector('#pl_asunto').value = '';
          document.querySelector('#pl_cuerpo').value = '';
        });
        lista.appendChild(btn);
        var cnt = document.getElementById('plantillaCount');
        if (cnt) cnt.textContent = String((parseInt(cnt.textContent, 10) || 0) + 1);
        bootstrap.Modal.getOrCreateInstance(modalEl).hide();
        btn.click();
        if (typeof Swal !== 'undefined') Swal.fire({icon:'success', title:'Plantilla creada', timer:1500, showConfirmButton:false});
      }).catch(function(e){ alert('Error: ' + e.message); })
        .finally(function(){
          btnCrear.disabled = false;
          btnCrear.innerHTML = '<i class="bx bx-check me-1"></i>Crear';
        });
    });

    inpKey.addEventListener('keypress', function(e){ if (e.key === 'Enter') { e.preventDefault(); inpDesc.focus(); } });
    inpDesc.addEventListener('keypress', function(e){ if (e.key === 'Enter') { e.preventDefault(); btnCrear.click(); } });
  }
  if (document.readyState === 'loading') document.addEventListener('DOMContentLoaded', init);
  else init();
})();
</script>

<?php include_once 'views/templates/footer.php'; ?>

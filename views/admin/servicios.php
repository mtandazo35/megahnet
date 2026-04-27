<?php include_once 'views/templates/header.php'; ?>

<style>
  .svc-page { max-width: 980px; }
  .svc-header-icon {
    width: 44px; height: 44px;
    display: inline-flex; align-items: center; justify-content: center;
    background: linear-gradient(135deg, #2563eb 0%, #3b82f6 100%);
    color: #fff; border-radius: 12px; font-size: 22px;
    box-shadow: 0 4px 10px rgba(37,99,235,.25);
  }
  .svc-card { border: 1px solid #e9ecef; border-radius: 12px; box-shadow: 0 1px 3px rgba(0,0,0,.04); margin-bottom: 1rem; }
  .svc-card .card-header {
    background: transparent; border-bottom: 1px solid #eef0f4;
    padding: .85rem 1.25rem; display: flex; align-items: center; gap: .75rem;
  }
  .svc-icon {
    width: 36px; height: 36px; border-radius: 10px;
    display: inline-flex; align-items: center; justify-content: center;
    color: #fff; font-size: 18px;
  }
  .svc-icon.svc-whatsapp { background: linear-gradient(135deg, #10b981 0%, #059669 100%); }
  .svc-icon.svc-sri { background: linear-gradient(135deg, #ef4444 0%, #dc2626 100%); }
  .svc-name { font-weight: 600; font-size: .95rem; color: #1f2937; }
  .svc-desc { font-size: .76rem; color: #6b7280; }
  .svc-status { margin-left: auto; font-size: .75rem; padding: .2em .7em; border-radius: 999px; font-weight: 500; }
  .svc-status.is-on { background: #dcfce7; color: #166534; }
  .svc-status.is-off { background: #fee2e2; color: #991b1b; }
  .svc-status.is-unknown { background: #f3f4f6; color: #4b5563; }
  .svc-status .dot { display: inline-block; width: 6px; height: 6px; border-radius: 50%; margin-right: 4px; vertical-align: 1px; }
  .svc-status.is-on .dot { background: #16a34a; }
  .svc-status.is-off .dot { background: #dc2626; }
  .svc-status.is-unknown .dot { background: #9ca3af; }
  .svc-card .card-body { padding: 1.25rem; }
  .svc-help { font-size: .76rem; color: #6b7280; margin-top: .25rem; display: block; }
  .svc-saveline { display: flex; justify-content: flex-end; gap: .5rem; align-items: center; margin-top: 1rem; }
</style>

<div class="svc-page">

  <div class="d-flex align-items-center mb-3">
    <div class="svc-header-icon"><i class="bx bx-server"></i></div>
    <div class="ms-3">
      <h4 class="mb-0 fw-semibold">Servicios externos</h4>
      <small class="text-muted">URLs y status de cada servicio que el sistema consume. Editable por instalacion.</small>
    </div>
  </div>

  <?php
  $svc = $data['servicios'] ?? [];
  $waCfg = $svc['whatsapp_api'] ?? [];
  $waUrl = $waCfg['base_url'] ?? '';
  if (empty($waUrl) && !empty($data['legacy_wa_url'])) $waUrl = $data['legacy_wa_url'];
  $waEnabled = isset($waCfg['enabled']) ? (bool)$waCfg['enabled'] : true;
  ?>

  <!-- WhatsApp API -->
  <div class="card svc-card" data-svc="whatsapp_api">
    <div class="card-header">
      <div class="svc-icon svc-whatsapp"><i class="bx bxl-whatsapp"></i></div>
      <div>
        <div class="svc-name">WhatsApp API</div>
        <div class="svc-desc">Servicio NestJS+Baileys para enviar mensajes WhatsApp</div>
      </div>
      <span class="svc-status is-unknown" id="status_whatsapp_api"><span class="dot"></span>Sin probar</span>
    </div>
    <div class="card-body">
      <div class="row g-3 align-items-end">
        <div class="col-md-7">
          <label class="form-label fw-semibold mb-1" for="svc_wa_url">URL base</label>
          <input type="text" id="svc_wa_url" class="form-control svc-url" data-key="whatsapp_api"
            placeholder="http://127.0.0.1:3005" value="<?php echo htmlspecialchars($waUrl); ?>">
          <small class="svc-help">Ejemplos: <code>http://127.0.0.1:3005</code> (mismo VPS) o <code>https://wa.midominio.com</code> (externo).</small>
        </div>
        <div class="col-md-2">
          <label class="form-label fw-semibold mb-1">Estado</label>
          <div class="form-check form-switch">
            <input class="form-check-input svc-enabled" type="checkbox" id="svc_wa_enabled" data-key="whatsapp_api" <?php echo $waEnabled ? 'checked' : ''; ?>>
            <label class="form-check-label" for="svc_wa_enabled" style="font-size:.85rem;">Activo</label>
          </div>
        </div>
        <div class="col-md-3 d-flex justify-content-end">
          <button class="btn btn-outline-info svc-test" type="button" data-key="whatsapp_api" data-type="whatsapp_api">
            <i class="bx bx-pulse me-1"></i>Probar conexion
          </button>
        </div>
      </div>
    </div>
  </div>

  <!-- Placeholder para futuros servicios (SRI, SMS, pasarelas, etc.) -->
  <div class="card svc-card" style="border-style:dashed; background:#fafbfd; opacity:.85;">
    <div class="card-body text-center py-3">
      <i class="bx bx-plus-circle text-muted" style="font-size:24px;"></i>
      <div class="text-muted mt-1" style="font-size:.85rem;">Mas servicios proximamente (SRI custom, SMS gateway, pasarela bancaria, etc.)</div>
    </div>
  </div>

  <div class="svc-saveline">
    <span id="svcStatus" class="text-muted" style="font-size:.82rem;"></span>
    <button class="btn btn-primary px-4" id="btnGuardarSvc" type="button">
      <i class="bx bx-save me-1"></i>Guardar cambios
    </button>
  </div>
</div>

<script>
(function(){
  function setStatus(key, level, text) {
    var el = document.getElementById('status_' + key);
    if (!el) return;
    el.className = 'svc-status is-' + level;
    el.innerHTML = '<span class="dot"></span>' + text;
  }

  function buildPayload() {
    var out = {};
    document.querySelectorAll('.svc-card[data-svc]').forEach(function(card){
      var key = card.dataset.svc;
      var url = card.querySelector('.svc-url[data-key="' + key + '"]');
      var en  = card.querySelector('.svc-enabled[data-key="' + key + '"]');
      out[key] = {
        base_url: url ? url.value.trim() : '',
        enabled:  en ? en.checked : false,
      };
    });
    return out;
  }

  document.querySelectorAll('.svc-test').forEach(function(btn){
    btn.addEventListener('click', function(){
      var key = btn.dataset.key;
      var type = btn.dataset.type;
      var url = document.querySelector('.svc-url[data-key="' + key + '"]').value.trim();
      if (!url) { setStatus(key, 'off', 'Falta URL'); return; }
      setStatus(key, 'unknown', 'Probando...');
      btn.disabled = true; var prev = btn.innerHTML; btn.innerHTML = '<i class="bx bx-loader bx-spin me-1"></i>Probando...';
      fetch(base_url + 'admin/probarServicio', {
        method: 'POST', credentials: 'same-origin',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ url: url, type: type })
      }).then(function(r){ return r.json(); }).then(function(d){
        if (d.ok) {
          setStatus(key, 'on', 'OK · ' + d.time_ms + 'ms · HTTP ' + d.http);
        } else {
          setStatus(key, 'off', d.msg || ('Fallo · HTTP ' + (d.http || 0)));
        }
      }).catch(function(e){
        setStatus(key, 'off', 'Error red: ' + e.message);
      }).finally(function(){
        btn.disabled = false; btn.innerHTML = prev;
      });
    });
  });

  document.getElementById('btnGuardarSvc').addEventListener('click', function(){
    var btn = this;
    btn.disabled = true; btn.innerHTML = '<i class="bx bx-loader bx-spin me-1"></i>Guardando...';
    fetch(base_url + 'admin/guardarServicios', {
      method: 'POST', credentials: 'same-origin',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify({ servicios: buildPayload() })
    }).then(function(r){ return r.json(); }).then(function(d){
      if (d.ok) {
        if (typeof Swal !== 'undefined') Swal.fire({icon:'success', title:'Servicios guardados', timer:1800, showConfirmButton:false});
        else alert('Guardado');
      } else { alert('Error: ' + (d.msg || '')); }
    }).catch(function(e){ alert('Error: ' + e.message); })
      .finally(function(){
        btn.disabled = false; btn.innerHTML = '<i class="bx bx-save me-1"></i>Guardar cambios';
      });
  });

  // Auto-probar al cargar si hay URL
  var initial = document.getElementById('svc_wa_url');
  if (initial && initial.value.trim()) {
    document.querySelector('.svc-test[data-key="whatsapp_api"]').click();
  }
})();
</script>

<?php include_once 'views/templates/footer.php'; ?>

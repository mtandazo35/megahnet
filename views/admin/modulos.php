<?php include_once 'views/templates/header.php'; ?>

<style>
  .mod-page { max-width: 980px; }
  .mod-header-icon {
    width: 44px; height: 44px;
    display: inline-flex; align-items: center; justify-content: center;
    background: linear-gradient(135deg, #2563eb 0%, #3b82f6 100%);
    color: #fff; border-radius: 12px; font-size: 22px;
    box-shadow: 0 4px 10px rgba(37,99,235,.25);
  }
  .mod-section { margin-bottom: 1rem; }
  .mod-section .card-header {
    background: transparent; border-bottom: 1px solid #eef0f4;
    padding: .85rem 1.25rem; display: flex; align-items: center; justify-content: space-between;
  }
  .mod-section .card-header h6 { margin: 0; font-size: .95rem; }
  .mod-section .card-body { padding: 1rem 1.25rem; }
  .mod-row {
    display: flex; align-items: center; justify-content: space-between;
    padding: .55rem .75rem; border: 1px solid #eef0f4; border-radius: 10px;
    background: #fafbfd; transition: background .12s, border-color .12s;
    margin-bottom: .35rem;
  }
  .mod-row:hover { background: #f3f5fa; border-color: #dbe2ee; }
  .mod-row .mod-label { display: flex; align-items: center; gap: .65rem; min-width: 0; }
  .mod-row .mod-label i { font-size: 18px; color: #6b7280; }
  .mod-row .mod-name { font-size: .87rem; font-weight: 500; color: #1f2937; }
  .mod-row .mod-key { font-family: ui-monospace, Menlo, monospace; font-size: .7rem; color: #9ca3af; margin-left: .35rem; }
  .mod-row.is-off { background: #fef2f2; border-color: #fecaca; opacity: .85; }
  .mod-row.is-off .mod-name { color: #991b1b; text-decoration: line-through; }
  .mod-savebar {
    position: sticky; bottom: 0; z-index: 5;
    background: linear-gradient(180deg, rgba(248,250,253,0) 0%, #f8fafd 40%);
    padding: 1rem 0 .25rem; margin-top: 1rem;
  }
  .mod-counter { font-size: .78rem; color: #6b7280; margin-left: .5rem; }
</style>

<div class="mod-page">

  <div class="d-flex align-items-center mb-3">
    <div class="mod-header-icon"><i class="bx bx-grid-alt"></i></div>
    <div class="ms-3">
      <h4 class="mb-0 fw-semibold">Modulos del sistema</h4>
      <small class="text-muted">Oculta del menu lateral las funciones que no usas. No borra nada — solo las esconde.</small>
    </div>
    <div class="ms-auto d-flex gap-1">
      <button type="button" class="btn btn-sm btn-light border" id="btnModAll"><i class="bx bx-check-double"></i> Mostrar todos</button>
      <button type="button" class="btn btn-sm btn-light border" id="btnModNone"><i class="bx bx-x"></i> Ocultar todos</button>
    </div>
  </div>

  <?php
  // Estructura: [seccion => [ [key, label, icon] , ... ] ]
  $secciones = [
      'Principal' => [
          ['admin', 'Tablero', 'bx-home-alt'],
      ],
      'Operaciones' => [
          ['contratos', 'Contratos', 'bx-file'],
          ['creditos', 'Administrar Creditos', 'bx-dollar-circle'],
          ['cotizaciones', 'Cotizaciones', 'bx-clipboard'],
      ],
      'Ventas' => [
          ['sridashboard', 'SRI Dashboard', 'bx-bar-chart-alt-2'],
          ['factura', 'Facturas', 'bx-receipt'],
          ['automaticas/index', 'Cerrar Corte F', 'bx-check-circle'],
          ['notacredito', 'Nota Credito', 'bx-minus-circle'],
          ['ordenventa', 'Orden Venta', 'bx-package'],
          ['automaticas/indexOrdenVenta', 'Cerrar Corte OV', 'bx-check-double'],
      ],
      'Gestion Compra' => [
          ['proveedor', 'Proveedores', 'bx-store'],
          ['compras', 'Compras', 'bx-cart'],
          ['retenciones', 'Retenciones', 'bx-receipt'],
      ],
      'Clientes & Cajas' => [
          ['clientes', 'Clientes', 'bx-group'],
          ['cajas', 'Cajas', 'bx-wallet'],
          ['casos', 'Casos', 'bx-support'],
          ['mikrotiks', 'Mikrotik', 'bx-server'],
      ],
      'Inventario / Mantenimiento' => [
          ['categorias', 'Categorias', 'bx-category'],
          ['productos', 'Productos', 'bx-cube'],
          ['inventarios', 'Inventario & Kardex', 'bx-list-check'],
          ['zonas', 'Zonas', 'bx-map'],
          ['rangoip', 'Rango IP', 'bx-network-chart'],
          ['grupotrabajos', 'Grupos de Trabajo', 'bx-sitemap'],
          ['repetidoras', 'Repetidoras', 'bx-broadcast'],
      ],
      'Sistema' => [
          ['usuarios', 'Usuarios', 'bx-user'],
          ['admin/datos', 'Configuracion', 'bx-buildings'],
          ['admin/contrato', 'Modelo de Contrato', 'bx-file-blank'],
          ['admin/servicios', 'Servicios externos', 'bx-server'],
          ['admin/roles', 'Roles de usuarios', 'bx-id-card'],
          ['sucursales', 'Sucursales', 'bx-store-alt'],
          ['admin/logs', 'Log de Acceso', 'bx-history'],
          ['admin/respaldos', 'Respaldos BD', 'bx-cloud-download'],
          ['notificaciones', 'Notificaciones', 'bx-bell'],
      ],
  ];
  $ocultos = $data['ocultos'] ?? [];
  $ocultosSet = array_flip($ocultos);
  ?>

  <?php foreach ($secciones as $titulo => $items): ?>
    <div class="card mod-section">
      <div class="card-header">
        <h6 class="fw-semibold"><i class="bx bx-folder text-primary me-2"></i><?php echo htmlspecialchars($titulo); ?></h6>
        <span class="mod-counter">
          <span class="mod-section-active"><?php echo count(array_filter($items, fn($it) => !isset($ocultosSet[$it[0]]))); ?></span>
          / <?php echo count($items); ?> visibles
        </span>
      </div>
      <div class="card-body">
        <div class="row g-2">
          <?php foreach ($items as $it): list($key, $label, $icon) = $it; $isOff = isset($ocultosSet[$key]); ?>
            <div class="col-md-6">
              <div class="mod-row<?php echo $isOff ? ' is-off' : ''; ?>" data-key="<?php echo htmlspecialchars($key); ?>">
                <div class="mod-label">
                  <i class="bx <?php echo htmlspecialchars($icon); ?>"></i>
                  <span class="mod-name"><?php echo htmlspecialchars($label); ?></span>
                  <span class="mod-key"><?php echo htmlspecialchars($key); ?></span>
                </div>
                <div class="form-check form-switch m-0">
                  <input class="form-check-input mod-toggle" type="checkbox"
                    data-key="<?php echo htmlspecialchars($key); ?>"
                    <?php echo $isOff ? '' : 'checked'; ?>>
                </div>
              </div>
            </div>
          <?php endforeach; ?>
        </div>
      </div>
    </div>
  <?php endforeach; ?>

  <div class="mod-savebar d-flex justify-content-end align-items-center gap-2">
    <span id="modStatus" class="text-muted" style="font-size:.82rem;"></span>
    <button class="btn btn-primary px-4" id="btnGuardarMod" type="button">
      <i class="bx bx-save me-1"></i>Guardar cambios
    </button>
  </div>
</div>

<script>
(function(){
  function getOcultos() {
    return Array.from(document.querySelectorAll('.mod-toggle:not(:checked)')).map(c => c.dataset.key);
  }
  function refreshCounters() {
    document.querySelectorAll('.mod-section').forEach(sec => {
      var total = sec.querySelectorAll('.mod-toggle').length;
      var on = sec.querySelectorAll('.mod-toggle:checked').length;
      var span = sec.querySelector('.mod-section-active');
      if (span) span.textContent = on;
    });
  }
  document.querySelectorAll('.mod-toggle').forEach(cb => {
    cb.addEventListener('change', function(){
      var row = cb.closest('.mod-row');
      if (row) row.classList.toggle('is-off', !cb.checked);
      refreshCounters();
    });
  });
  document.getElementById('btnModAll').addEventListener('click', function(){
    document.querySelectorAll('.mod-toggle').forEach(cb => { cb.checked = true; cb.dispatchEvent(new Event('change')); });
  });
  document.getElementById('btnModNone').addEventListener('click', function(){
    document.querySelectorAll('.mod-toggle').forEach(cb => { cb.checked = false; cb.dispatchEvent(new Event('change')); });
  });
  document.getElementById('btnGuardarMod').addEventListener('click', function(){
    var btn = this;
    btn.disabled = true; btn.innerHTML = '<i class="bx bx-loader bx-spin me-1"></i>Guardando...';
    fetch(base_url + 'admin/guardarModulos', {
      method: 'POST', credentials: 'same-origin',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify({ ocultos: getOcultos() })
    }).then(r => r.json()).then(d => {
      if (d.ok) {
        if (typeof Swal !== 'undefined') Swal.fire({icon:'success', title:'Guardado', text: d.count + ' modulo(s) ocultos. Recarga para ver el sidebar actualizado.', timer: 3000});
        else alert('Guardado. Recarga para ver el sidebar.');
      } else {
        alert('Error: ' + (d.msg || 'desconocido'));
      }
    }).catch(e => alert('Error de red: ' + e.message))
      .finally(() => {
        btn.disabled = false; btn.innerHTML = '<i class="bx bx-save me-1"></i>Guardar cambios';
      });
  });
})();
</script>

<?php include_once 'views/templates/footer.php'; ?>

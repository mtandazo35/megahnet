<?php include_once 'views/templates/header.php'; ?>

<style>
  .roles-page { max-width: 1180px; }
  .roles-header-icon {
    width: 44px; height: 44px;
    display: inline-flex; align-items: center; justify-content: center;
    background: linear-gradient(135deg, #2563eb 0%, #3b82f6 100%);
    color: #fff; border-radius: 12px; font-size: 22px;
    box-shadow: 0 4px 10px rgba(37,99,235,.25);
  }
  .role-card { border: 0; border-radius: 14px; box-shadow: 0 1px 4px rgba(0,0,0,.06); overflow: hidden; }
  .role-card .role-card-head {
    padding: 1rem 1.15rem; display: flex; align-items: center; gap: .75rem;
    color: #fff; font-weight: 600; letter-spacing: .2px;
  }
  .role-card-head .role-icon { font-size: 28px; opacity: .95; }
  .role-card-head .role-name { font-size: 1.05rem; }
  .role-card-head .role-count { margin-left: auto; background: rgba(255,255,255,.22); padding: .15rem .6rem; border-radius: 999px; font-size: .82rem; }
  .role-card.role-1 .role-card-head { background: linear-gradient(135deg, #2563eb 0%, #4f46e5 100%); }
  .role-card.role-2 .role-card-head { background: linear-gradient(135deg, #f59e0b 0%, #f97316 100%); }
  .role-card.role-3 .role-card-head { background: linear-gradient(135deg, #10b981 0%, #059669 100%); }
  .role-card .role-body { padding: 0; }
  .role-user-row {
    display: flex; align-items: center; gap: .75rem;
    padding: .65rem 1rem; border-bottom: 1px solid #f1f3f7;
    transition: background .12s;
  }
  .role-user-row:last-child { border-bottom: 0; }
  .role-user-row:hover { background: #f9fafb; }
  .role-user-row.is-off { opacity: .55; }
  .role-user-avatar {
    width: 32px; height: 32px; border-radius: 50%;
    display: inline-flex; align-items: center; justify-content: center;
    color: #fff; font-weight: 600; font-size: .82rem;
    background: linear-gradient(135deg, #6366f1 0%, #8b5cf6 100%);
    flex: 0 0 auto;
  }
  .role-user-info { flex: 1; min-width: 0; }
  .role-user-name { font-size: .87rem; font-weight: 500; color: #1f2937; line-height: 1.2; }
  .role-user-mail { font-size: .73rem; color: #6b7280; word-break: break-all; }
  .role-user-row .form-select { font-size: .78rem; padding: .25rem 1.5rem .25rem .5rem; width: 130px; }
  .role-empty {
    text-align: center; padding: 2rem 1rem; color: #9ca3af; font-size: .85rem;
  }
  .role-empty i { font-size: 32px; display: block; margin-bottom: .35rem; opacity: .5; }

  .perms-card { border-radius: 12px; border: 1px solid #e9ecef; }
  .perms-table { margin: 0; }
  .perms-table th { font-size: .72rem; text-transform: uppercase; letter-spacing: .4px; color: #6b7280; background: #f9fafb; font-weight: 700; }
  .perms-table td { font-size: .85rem; vertical-align: middle; }
  .perms-table .perm-yes { color: #059669; }
  .perms-table .perm-no { color: #dc2626; }
</style>

<div class="roles-page">

  <div class="d-flex align-items-center mb-3">
    <div class="roles-header-icon"><i class="bx bx-id-card"></i></div>
    <div class="ms-3">
      <h4 class="mb-0 fw-semibold">Roles de usuarios</h4>
      <small class="text-muted">Visualiza y cambia el rol de cada cuenta. Cambios surten efecto en el proximo login del usuario.</small>
    </div>
  </div>

  <?php
  $rolesMeta = [
      1 => ['nombre' => 'Administrador',  'icon' => 'bx-shield-quarter', 'desc' => 'Acceso total al sistema. Gestiona usuarios, configuracion, respaldos y modulos.'],
      2 => ['nombre' => 'Secretario(a)',  'icon' => 'bx-edit',           'desc' => 'Acceso a operaciones de venta y atencion. Restringido en proveedores y administracion.'],
      3 => ['nombre' => 'Tecnico',        'icon' => 'bx-wrench',         'desc' => 'Acceso enfocado a contratos, casos y mantenimiento. Sin acceso a creditos ni administracion.'],
  ];
  $usuarios = $data['usuarios'] ?? [];
  $porRol = [1 => [], 2 => [], 3 => []];
  foreach ($usuarios as $u) {
      $r = (int)$u['rol'];
      if (isset($porRol[$r])) $porRol[$r][] = $u;
  }
  ?>

  <div class="row g-3 mb-4">
    <?php foreach ($rolesMeta as $rolId => $meta): ?>
      <div class="col-lg-4 col-md-6">
        <div class="card role-card role-<?php echo $rolId; ?>">
          <div class="role-card-head">
            <i class="bx <?php echo htmlspecialchars($meta['icon']); ?> role-icon"></i>
            <div>
              <div class="role-name"><?php echo htmlspecialchars($meta['nombre']); ?></div>
              <small class="opacity-75" style="font-size:.72rem;font-weight:400;"><?php echo htmlspecialchars($meta['desc']); ?></small>
            </div>
            <span class="role-count" title="Usuarios"><?php echo count($porRol[$rolId]); ?></span>
            <?php if ($rolId !== 1): ?>
              <button type="button" class="btn btn-sm btn-light ms-2 btn-edit-perms"
                data-rol="<?php echo $rolId; ?>" data-rolname="<?php echo htmlspecialchars($meta['nombre']); ?>"
                title="Editar permisos del rol" style="padding:.2rem .55rem;">
                <i class="bx bx-cog"></i>
              </button>
            <?php endif; ?>
          </div>
          <div class="role-body">
            <?php if (empty($porRol[$rolId])): ?>
              <div class="role-empty">
                <i class="bx bx-user-x"></i>
                Sin usuarios en este rol
              </div>
            <?php else: ?>
              <?php foreach ($porRol[$rolId] as $u):
                $iniciales = strtoupper(substr(trim($u['nombres']), 0, 2));
              ?>
                <div class="role-user-row<?php echo (int)$u['estado'] === 0 ? ' is-off' : ''; ?>" data-id="<?php echo (int)$u['id']; ?>">
                  <div class="role-user-avatar"><?php echo htmlspecialchars($iniciales ?: '?'); ?></div>
                  <div class="role-user-info">
                    <div class="role-user-name">
                      <?php echo htmlspecialchars(trim($u['nombres'])); ?>
                      <?php if ((int)$u['estado'] === 0): ?><span class="badge bg-secondary ms-1" style="font-size:.62rem;">Inactivo</span><?php endif; ?>
                    </div>
                    <div class="role-user-mail"><?php echo htmlspecialchars($u['correo']); ?></div>
                  </div>
                  <select class="form-select form-select-sm role-change-sel" data-id="<?php echo (int)$u['id']; ?>" data-current="<?php echo $rolId; ?>">
                    <option value="1" <?php echo $rolId === 1 ? 'selected' : ''; ?>>Administrador</option>
                    <option value="2" <?php echo $rolId === 2 ? 'selected' : ''; ?>>Secretario(a)</option>
                    <option value="3" <?php echo $rolId === 3 ? 'selected' : ''; ?>>Tecnico</option>
                  </select>
                </div>
              <?php endforeach; ?>
            <?php endif; ?>
          </div>
        </div>
      </div>
    <?php endforeach; ?>
  </div>

  <div class="card perms-card">
    <div class="card-body">
      <h6 class="fw-semibold text-dark mb-2"><i class="bx bx-list-check text-primary me-1"></i>Resumen de permisos por rol</h6>
      <p class="text-muted" style="font-size:.78rem;">Tabla informativa basada en el codigo actual del sistema (verificaciones <code>$_SESSION[\'rol\']</code>).</p>
      <div class="table-responsive">
        <table class="table perms-table">
          <thead>
            <tr>
              <th>Funcion</th>
              <th class="text-center">Administrador</th>
              <th class="text-center">Secretario(a)</th>
              <th class="text-center">Tecnico</th>
            </tr>
          </thead>
          <tbody>
            <?php
            $perms = [
                ['Configuracion empresa (datos)',   true, false, true],
                ['Listado de usuarios',              true, false, true],
                ['Modulos del sistema',              true, false, false],
                ['Roles de usuarios',                true, false, false],
                ['Respaldos BD',                     true, true,  true],
                ['Logs de acceso',                   true, false, true],
                ['Contratos',                        true, true,  false],
                ['Creditos',                         true, true,  false],
                ['Proveedores / Compras',            true, false, true],
                ['Notificaciones',                   true, true,  true],
                ['Cajas / Ventas',                   true, true,  true],
                ['Mantenimiento (productos, zonas, etc)', true, true, true],
            ];
            foreach ($perms as $p): ?>
              <tr>
                <td><?php echo htmlspecialchars($p[0]); ?></td>
                <td class="text-center"><?php echo $p[1] ? '<i class="bx bx-check perm-yes" style="font-size:18px;"></i>' : '<i class="bx bx-x perm-no" style="font-size:18px;"></i>'; ?></td>
                <td class="text-center"><?php echo $p[2] ? '<i class="bx bx-check perm-yes" style="font-size:18px;"></i>' : '<i class="bx bx-x perm-no" style="font-size:18px;"></i>'; ?></td>
                <td class="text-center"><?php echo $p[3] ? '<i class="bx bx-check perm-yes" style="font-size:18px;"></i>' : '<i class="bx bx-x perm-no" style="font-size:18px;"></i>'; ?></td>
              </tr>
            <?php endforeach; ?>
          </tbody>
        </table>
      </div>
    </div>
  </div>
</div>

<script>
(function(){
  document.querySelectorAll('.role-change-sel').forEach(sel => {
    sel.addEventListener('change', function(){
      var id = parseInt(this.dataset.id, 10);
      var prev = parseInt(this.dataset.current, 10);
      var nuevo = parseInt(this.value, 10);
      if (prev === nuevo) return;
      var nombres = { 1: 'Administrador', 2: 'Secretario(a)', 3: 'Tecnico' };
      var fnDo = function(){
        sel.disabled = true;
        fetch(base_url + 'admin/cambiarRolUsuario', {
          method: 'POST', credentials: 'same-origin',
          headers: { 'Content-Type': 'application/json' },
          body: JSON.stringify({ id: id, rol: nuevo })
        }).then(r => r.json()).then(d => {
          if (d.ok) {
            if (typeof Swal !== 'undefined') Swal.fire({icon:'success', title:'Rol actualizado', text:'El usuario ahora es ' + nombres[nuevo], timer: 2000, showConfirmButton: false});
            sel.dataset.current = nuevo;
            // Recargar despues de un pequeno delay para reagrupar visualmente
            setTimeout(function(){ window.location.reload(); }, 800);
          } else {
            sel.value = prev;
            if (typeof Swal !== 'undefined') Swal.fire({icon:'error', title:'No se pudo cambiar', text: d.msg || ''});
            else alert('Error: ' + (d.msg || ''));
          }
        }).catch(e => {
          sel.value = prev;
          alert('Error: ' + e.message);
        }).finally(() => { sel.disabled = false; });
      };
      if (typeof Swal !== 'undefined') {
        Swal.fire({
          icon: 'question',
          title: 'Cambiar rol?',
          text: 'El usuario pasara a ser ' + nombres[nuevo] + '.',
          showCancelButton: true, confirmButtonText: 'Si, cambiar', cancelButtonText: 'Cancelar'
        }).then(function(r){
          if (r.isConfirmed) fnDo();
          else sel.value = prev;
        });
      } else {
        if (confirm('Cambiar a ' + nombres[nuevo] + '?')) fnDo();
        else sel.value = prev;
      }
    });
  });
})();
</script>


<!-- Modal Editar permisos de rol -->
<div class="modal fade" id="modalPermsRol" tabindex="-1">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title"><i class="bx bx-cog me-1 text-primary"></i>Permisos: <span id="permsRolName"></span></h5>
        <button class="btn-close" data-bs-dismiss="modal"></button>
      </div>
      <div class="modal-body" style="max-height:60vh;overflow-y:auto;">
        <p class="text-muted mb-2" style="font-size:.78rem;">Marca los modulos que este rol VERA en el menu lateral. Los desmarcados quedan ocultos para ese rol.</p>
        <div id="permsList"></div>
      </div>
      <div class="modal-footer">
        <button class="btn btn-light border" type="button" id="btnPermsAll"><i class="bx bx-check-double"></i> Todos</button>
        <button class="btn btn-light border" type="button" id="btnPermsNone"><i class="bx bx-x"></i> Ninguno</button>
        <button class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
        <button class="btn btn-primary" type="button" id="btnGuardarPermsRol"><i class="bx bx-save me-1"></i>Guardar</button>
      </div>
    </div>
  </div>
</div>

<script>
(function(){
  // Catalogo de modulos (agrupado por seccion)
  var SECCIONES = <?php echo json_encode([
      'Principal' => [
          ['admin', 'Tablero'],
      ],
      'Operaciones' => [
          ['contratos', 'Contratos'],
          ['creditos', 'Administrar Creditos'],
          ['cotizaciones', 'Cotizaciones'],
      ],
      'Ventas' => [
          ['sridashboard', 'SRI Dashboard'],
          ['factura', 'Facturas'],
          ['automaticas/index', 'Cerrar Corte F'],
          ['notacredito', 'Nota Credito'],
          ['ordenventa', 'Orden Venta'],
          ['automaticas/indexOrdenVenta', 'Cerrar Corte OV'],
      ],
      'Gestion Compra' => [
          ['proveedor', 'Proveedores'],
          ['compras', 'Compras'],
          ['retenciones', 'Retenciones'],
      ],
      'Clientes & Cajas' => [
          ['clientes', 'Clientes'],
          ['cajas', 'Cajas'],
          ['casos', 'Casos'],
          ['mikrotiks', 'Mikrotik'],
      ],
      'Inventario / Mantenimiento' => [
          ['categorias', 'Categorias'],
          ['productos', 'Productos'],
          ['inventarios', 'Inventario & Kardex'],
          ['zonas', 'Zonas'],
          ['rangoip', 'Rango IP'],
          ['grupotrabajos', 'Grupos de Trabajo'],
          ['repetidoras', 'Repetidoras'],
      ],
      'Administracion' => [
          ['usuarios', 'Usuarios'],
          ['admin/datos', 'Configuracion'],
          ['admin/roles', 'Roles de usuarios'],
          ['sucursales', 'Sucursales'],
          ['admin/logs', 'Log de Acceso'],
          ['admin/respaldos', 'Respaldos BD'],
          ['notificaciones', 'Notificaciones'],
      ],
  ]); ?>;

  var OCULTOS_POR_ROL = <?php echo json_encode($data['ocultos_por_rol'] ?? []); ?>;
  var modalEl = document.getElementById('modalPermsRol');
  var listEl = document.getElementById('permsList');
  var nameEl = document.getElementById('permsRolName');
  var rolActual = null;

  function renderList(rolId) {
    var ocultos = OCULTOS_POR_ROL[String(rolId)] || [];
    var ocultosSet = {};
    ocultos.forEach(function(k){ ocultosSet[k] = true; });
    var html = '';
    Object.keys(SECCIONES).forEach(function(sec){
      html += '<div class="mb-2"><div class="fw-semibold text-primary mb-1" style="font-size:.78rem;">' + sec + '</div>';
      SECCIONES[sec].forEach(function(it){
        var key = it[0], label = it[1];
        var checked = ocultosSet[key] ? '' : 'checked';
        html += '<div class="form-check form-switch ms-2">';
        html += '<input class="form-check-input perm-toggle" type="checkbox" id="perm_' + key.replace(/\W/g,'_') + '" data-key="' + key + '" ' + checked + '>';
        html += '<label class="form-check-label" for="perm_' + key.replace(/\W/g,'_') + '" style="font-size:.85rem;">' + label + ' <small class="text-muted ms-1" style="font-family:ui-monospace;">' + key + '</small></label>';
        html += '</div>';
      });
      html += '</div>';
    });
    listEl.innerHTML = html;
  }

  document.querySelectorAll('.btn-edit-perms').forEach(function(b){
    b.addEventListener('click', function(){
      rolActual = parseInt(b.dataset.rol, 10);
      nameEl.textContent = b.dataset.rolname;
      renderList(rolActual);
      bootstrap.Modal.getOrCreateInstance(modalEl).show();
    });
  });

  document.getElementById('btnPermsAll').addEventListener('click', function(){
    listEl.querySelectorAll('.perm-toggle').forEach(function(cb){ cb.checked = true; });
  });
  document.getElementById('btnPermsNone').addEventListener('click', function(){
    listEl.querySelectorAll('.perm-toggle').forEach(function(cb){ cb.checked = false; });
  });

  document.getElementById('btnGuardarPermsRol').addEventListener('click', function(){
    if (!rolActual) return;
    var ocultos = Array.from(listEl.querySelectorAll('.perm-toggle:not(:checked)')).map(function(cb){ return cb.dataset.key; });
    var btn = this;
    btn.disabled = true; btn.innerHTML = '<i class="bx bx-loader bx-spin me-1"></i>Guardando...';
    fetch(base_url + 'admin/guardarPermisosRol', {
      method: 'POST', credentials: 'same-origin',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify({ rol: rolActual, ocultos: ocultos })
    }).then(function(r){ return r.json(); }).then(function(d){
      if (d.ok) {
        OCULTOS_POR_ROL[String(rolActual)] = ocultos;
        bootstrap.Modal.getOrCreateInstance(modalEl).hide();
        if (typeof Swal !== 'undefined') Swal.fire({icon:'success', title:'Permisos guardados', text: ocultos.length + ' modulo(s) ocultos para este rol', timer: 2200});
        else alert('Permisos guardados');
      } else {
        alert('Error: ' + (d.msg || ''));
      }
    }).catch(function(e){ alert('Error: ' + e.message); })
      .finally(function(){
        btn.disabled = false; btn.innerHTML = '<i class="bx bx-save me-1"></i>Guardar';
      });
  });
})();
</script>

<?php include_once 'views/templates/footer.php'; ?>

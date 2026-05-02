<?php include_once 'views/templates/header.php'; ?>

<!-- Hero del tablero -->
<div class="page-header d-flex justify-content-between align-items-center mb-3">
  <div>
    <h4 class="mb-1 fw-semibold">Panel de Control</h4>
    <small class="text-muted"><?= (function(){$dias=['Domingo','Lunes','Martes','Miercoles','Jueves','Viernes','Sabado'];$meses=['Enero','Febrero','Marzo','Abril','Mayo','Junio','Julio','Agosto','Septiembre','Octubre','Noviembre','Diciembre'];return $dias[(int)date('w')].', '.date('d').' de '.$meses[(int)date('n')-1].' de '.date('Y');})() ?></small>
  </div>
  <div class="text-end">
    <div class="badge bg-light text-dark border px-3 py-2">
      <i class="bx bx-user-circle me-1"></i><?= $_SESSION['nombre_usuario'] ?? 'Usuario' ?>
      <span class="text-muted ms-2">· <?= date('H:i') ?></span>
    </div>
  </div>
</div>

<!-- ============ KPIs Cobranza ============ -->
<?php
  $cobMes      = (float)($data['cobradoMes']['total'] ?? 0);
  $cobMesQty   = (int)($data['cobradoMes']['cantidad'] ?? 0);
  $pendiente   = (float)($data['pendienteCobro']['pendiente'] ?? 0);
  $totalMonto  = (float)($data['pendienteCobro']['total_monto'] ?? 0);
  $cantCred    = (int)($data['pendienteCobro']['cantidad_creditos'] ?? 0);
  $pendFact    = (float)($data['pendienteCobro']['pendiente_facturas'] ?? 0);
  $pendRec     = (float)($data['pendienteCobro']['pendiente_recibos']  ?? 0);
  $cantFact    = (int)($data['pendienteCobro']['cant_facturas'] ?? 0);
  $cantRec     = (int)($data['pendienteCobro']['cant_recibos']  ?? 0);
  $pctCobrado  = $totalMonto > 0 ? round(($totalMonto - $pendiente) * 100 / $totalMonto, 1) : 0;
  $mesActual   = (function(){
      $meses = ['Enero','Febrero','Marzo','Abril','Mayo','Junio','Julio','Agosto','Septiembre','Octubre','Noviembre','Diciembre'];
      return $meses[(int)date('n') - 1] . ' ' . date('Y');
  })();
?>
<div class="row row-cols-1 row-cols-md-2 g-3 mb-3">
  <div class="col">
    <div class="kpi-card kpi-cobro">
      <div class="kpi-body">
        <div class="kpi-icon"><i class="bx bx-dollar-circle"></i></div>
        <div class="flex-grow-1">
          <small class="kpi-label">Cobrado en <?= $mesActual ?></small>
          <h3 class="kpi-value text-success">$<?= number_format($cobMes, 2) ?></h3>
          <small class="text-muted"><?= $cobMesQty ?> abono<?= $cobMesQty == 1 ? '' : 's' ?> registrado<?= $cobMesQty == 1 ? '' : 's' ?> este mes</small>
        </div>
      </div>
    </div>
  </div>
  <div class="col">
    <div class="kpi-card kpi-pendiente">
      <div class="kpi-body">
        <div class="kpi-icon"><i class="bx bx-time-five"></i></div>
        <div class="flex-grow-1">
          <small class="kpi-label">Pendiente por cobrar (mes)</small>
          <h3 class="kpi-value text-danger">$<?= number_format($pendiente, 2) ?></h3>
          <div class="row g-2 mt-2">
            <div class="col-6">
              <div class="pendiente-mini pendiente-recibo">
                <div class="pendiente-mini-icon"><i class="bx bx-receipt"></i></div>
                <div class="pendiente-mini-body">
                  <small class="pendiente-mini-label">Recibos</small>
                  <div class="pendiente-mini-value">$<?= number_format($pendRec, 2) ?></div>
                  <small class="pendiente-mini-count"><?= $cantRec ?> credito<?= $cantRec==1?'':'s' ?></small>
                </div>
              </div>
            </div>
            <div class="col-6">
              <div class="pendiente-mini pendiente-factura">
                <div class="pendiente-mini-icon"><i class="bx bx-file"></i></div>
                <div class="pendiente-mini-body">
                  <small class="pendiente-mini-label">Facturas</small>
                  <div class="pendiente-mini-value">$<?= number_format($pendFact, 2) ?></div>
                  <small class="pendiente-mini-count"><?= $cantFact ?> credito<?= $cantFact==1?'':'s' ?></small>
                </div>
              </div>
            </div>
          </div>
          <div class="progress mt-2" style="height:6px;">
            <div class="progress-bar bg-success" style="width:<?= max(0, min(100, $pctCobrado)) ?>%"></div>
          </div>
          <small class="text-muted"><?= $cantCred ?> credito<?= $cantCred == 1 ? '' : 's' ?> activo<?= $cantCred == 1 ? '' : 's' ?> · <?= $pctCobrado ?>% cobrado del total ($<?= number_format($totalMonto, 2) ?>)</small>
        </div>
      </div>
    </div>
  </div>
</div>

<!-- ============ KPIs ============ -->
<div class="row row-cols-1 row-cols-md-2 row-cols-xl-4 g-3 mb-3">
  <div class="col">
    <div class="kpi-card kpi-primary">
      <div class="kpi-body">
        <div class="kpi-icon"><i class="bx bx-group"></i></div>
        <div class="flex-grow-1">
          <small class="kpi-label">Clientes</small>
          <h3 class="kpi-value"><?= number_format($data['clientes']['total'] ?? 0) ?></h3>
          <a href="<?= BASE_URL.'clientes' ?>" class="kpi-link">Ver todos <i class="bx bx-right-arrow-alt"></i></a>
        </div>
      </div>
    </div>
  </div>
  <div class="col">
    <div class="kpi-card kpi-success">
      <div class="kpi-body">
        <div class="kpi-icon"><i class="bx bx-check-shield"></i></div>
        <div class="flex-grow-1">
          <small class="kpi-label">Contratos activos</small>
          <h3 class="kpi-value"><?= number_format($data['contratosActivos']['total'] ?? 0) ?></h3>
          <a href="<?= BASE_URL.'contratos' ?>" class="kpi-link">Gestionar <i class="bx bx-right-arrow-alt"></i></a>
        </div>
      </div>
    </div>
  </div>
  <div class="col">
    <div class="kpi-card kpi-warning">
      <div class="kpi-body">
        <div class="kpi-icon"><i class="bx bx-dollar-circle"></i></div>
        <div class="flex-grow-1">
          <small class="kpi-label">Créditos pendientes</small>
          <h3 class="kpi-value"><?= number_format($data['creditos']['total'] ?? 0) ?></h3>
          <a href="<?= BASE_URL.'creditos' ?>" class="kpi-link">Administrar <i class="bx bx-right-arrow-alt"></i></a>
        </div>
      </div>
    </div>
  </div>
  <div class="col">
    <div class="kpi-card kpi-danger">
      <div class="kpi-body">
        <div class="kpi-icon"><i class="bx bx-error-circle"></i></div>
        <div class="flex-grow-1">
          <small class="kpi-label">Por suspender</small>
          <h3 class="kpi-value"><?= number_format($data['contratosPorSuspender'][0]['total'] ?? 0) ?></h3>
          <a href="<?= BASE_URL.'contratos' ?>" class="kpi-link">Revisar <i class="bx bx-right-arrow-alt"></i></a>
        </div>
      </div>
    </div>
  </div>
</div>

<!-- ============ Segunda fila: suspendidos, productos, proveedores, zonas ============ -->
<div class="row row-cols-2 row-cols-xl-4 g-3 mb-4">
  <div class="col">
    <div class="mini-card">
      <i class="bx bx-x-circle text-secondary"></i>
      <div><small>Suspendidos</small><div class="fw-bold"><?= number_format($data['contratosSuspendidos']['total'] ?? 0) ?></div></div>
    </div>
  </div>
  <div class="col">
    <div class="mini-card">
      <i class="bx bx-cube text-info"></i>
      <div><small>Productos</small><div class="fw-bold"><?= number_format($data['productos']['total'] ?? 0) ?></div></div>
    </div>
  </div>
  <div class="col">
    <div class="mini-card">
      <i class="bx bx-store text-primary"></i>
      <div><small>Proveedores</small><div class="fw-bold"><?= number_format($data['proveedores']['total'] ?? 0) ?></div></div>
    </div>
  </div>
  <div class="col">
    <div class="mini-card">
      <i class="bx bx-map text-warning"></i>
      <div><small>Zonas</small><div class="fw-bold"><?= number_format($data['zonas']['total'] ?? 0) ?></div></div>
    </div>
  </div>
</div>


<!-- ============ RESUMEN FINANCIERO DEL MES (desglose) ============ -->
<?php
  $cobrosD     = $data['cobrosDesglose']      ?? [];
  $retencionesD = $data['retencionesDesglose'] ?? [];
  $facturacionD = $data['facturacionDesglose'] ?? [];
  $egresosD    = $data['egresosDesglose']     ?? [];

  $totFacturacion = array_sum(array_column($facturacionD, 'total'));
  $totCobros      = array_sum(array_column($cobrosD,      'total'));
  $totRetenciones = array_sum(array_column($retencionesD, 'total'));
  $totEgresos     = array_sum(array_column($egresosD,     'total'));
  $saldoNeto      = $totCobros - $totEgresos;

  $fmt = function($v){ return '$' . number_format((float)$v, 2); };
?>
<div id="resumen-financiero" class="card radius-10 mb-4">
  <div class="card-body">
    <h6 class="mb-3 fw-semibold"><i class="bx bx-pie-chart-alt-2 text-primary me-1"></i>Resumen financiero de <?= $mesActual ?></h6>

    <div class="row g-3">
      <!-- Facturacion emitida -->
      <div class="col-lg-6 col-xl-3">
        <div class="border rounded p-3 h-100" style="background:#f0f9ff;">
          <div class="d-flex justify-content-between align-items-center mb-1">
            <strong class="text-primary"><i class="bx bx-receipt me-1"></i>Facturación</strong>
            <span class="badge bg-primary"><?= $fmt($totFacturacion) ?></span>
          </div>
          <small class="text-muted d-block mb-2">Valores con IVA incluido</small>
          <?php if (!empty($facturacionD)): ?>
            <table class="table table-sm mb-0" style="font-size:.78rem;">
              <tbody>
              <?php foreach ($facturacionD as $f): ?>
                <tr>
                  <td class="text-muted"><?= htmlspecialchars($f['origen'] . ' (' . $f['metodo'] . ')') ?></td>
                  <td class="text-end fw-semibold"><?= $fmt($f['total']) ?></td>
                </tr>
              <?php endforeach; ?>
              </tbody>
            </table>
          <?php else: ?>
            <small class="text-muted">Sin facturación este mes</small>
          <?php endif; ?>
        </div>
      </div>

      <!-- Cobros recibidos -->
      <div class="col-lg-6 col-xl-3">
        <div class="border rounded p-3 h-100" style="background:#f0fdf4;">
          <div class="d-flex justify-content-between align-items-center mb-2">
            <strong class="text-success"><i class="bx bx-dollar-circle me-1"></i>Cobros</strong>
            <span class="badge bg-success"><?= $fmt($totCobros) ?></span>
          </div>
          <?php if (!empty($cobrosD)): ?>
            <table class="table table-sm mb-0" style="font-size:.78rem;">
              <tbody>
              <?php foreach ($cobrosD as $c): ?>
                <tr>
                  <td class="text-muted"><?= htmlspecialchars($c['tipo_pago']) ?>
                    <small class="text-muted">(<?= (int)$c['cantidad'] ?>)</small>
                  </td>
                  <td class="text-end fw-semibold"><?= $fmt($c['total']) ?></td>
                </tr>
              <?php endforeach; ?>
              </tbody>
            </table>
          <?php else: ?>
            <small class="text-muted">Sin cobros este mes</small>
          <?php endif; ?>
        </div>
      </div>

      <!-- Retenciones -->
      <div class="col-lg-6 col-xl-3">
        <div class="border rounded p-3 h-100" style="background:#fefce8;">
          <div class="mb-2">
            <strong class="text-warning"><i class="bx bx-shield-alt-2 me-1"></i>Retenciones</strong>
            <small class="text-muted d-block">Solo informativo — no afecta el saldo</small>
          </div>
          <?php if (!empty($retencionesD)): ?>
            <table class="table table-sm mb-0" style="font-size:.78rem;">
              <tbody>
              <?php foreach ($retencionesD as $r): ?>
                <tr>
                  <td class="text-muted"><?= htmlspecialchars($r['tipo']) ?>
                    <small class="text-muted">(<?= (int)$r['cantidad'] ?> ret.)</small>
                  </td>
                  <td class="text-end fw-semibold"><?= $fmt($r['total']) ?></td>
                </tr>
              <?php endforeach; ?>
              <tr class="border-top">
                <td class="text-muted"><small>Retenciones recibidas</small></td>
                <td class="text-end"><small><?= $fmt(array_sum(array_column($retencionesD, 'base'))) ?></small></td>
              </tr>
              </tbody>
            </table>
          <?php else: ?>
            <small class="text-muted">Sin retenciones este mes</small>
          <?php endif; ?>
        </div>
      </div>

      <!-- Egresos -->
      <div class="col-lg-6 col-xl-3">
        <div class="border rounded p-3 h-100" style="background:#fef2f2;">
          <div class="d-flex justify-content-between align-items-center mb-2">
            <strong class="text-danger"><i class="bx bx-trending-down me-1"></i>Egresos</strong>
            <span class="badge bg-danger"><?= $fmt($totEgresos) ?></span>
          </div>
          <?php if (!empty($egresosD)): ?>
            <table class="table table-sm mb-0" style="font-size:.78rem;">
              <tbody>
              <?php foreach ($egresosD as $e): ?>
                <tr>
                  <td class="text-muted"><?= htmlspecialchars($e['concepto']) ?>
                    <small class="text-muted">(<?= (int)$e['cantidad'] ?>)</small>
                  </td>
                  <td class="text-end fw-semibold"><?= $fmt($e['total']) ?></td>
                </tr>
              <?php endforeach; ?>
              </tbody>
            </table>
          <?php else: ?>
            <small class="text-muted">Sin egresos este mes</small>
          <?php endif; ?>
        </div>
      </div>
    </div>

    <!-- SALDO NETO -->
    <div class="row g-3 mt-2">
      <div class="col-12">
        <div class="d-flex flex-wrap align-items-center justify-content-between p-3 rounded" style="background: linear-gradient(90deg, #f1f5f9 0%, #fff 100%); border-left: 4px solid <?= $saldoNeto >= 0 ? '#10b981' : '#ef4444' ?>;">
          <div>
            <small class="text-muted d-block">Saldo neto del mes (cobros − egresos)</small>
            <h4 class="mb-0 <?= $saldoNeto >= 0 ? 'text-success' : 'text-danger' ?> fw-bold"><?= $fmt($saldoNeto) ?></h4>
          </div>
          <div class="text-end" style="font-size:.82rem;">
            <div><span class="text-muted">Cobros:</span> <span class="text-success fw-semibold"><?= $fmt($totCobros) ?></span></div>
            <div><span class="text-muted">Egresos:</span> <span class="text-danger fw-semibold"><?= $fmt($totEgresos) ?></span></div>
          </div>
        </div>
      </div>
    </div>
  </div>
</div>

<!-- ============ Graficos + Alertas ============ -->
<div class="row g-3 mb-4">
  <div class="col-lg-7">
    <div class="card radius-10 h-100">
      <div class="card-body">
        <div class="d-flex justify-content-between align-items-center mb-2">
          <h6 class="mb-0 fw-semibold"><i class="bx bx-line-chart text-primary me-1"></i>Ventas vs Compras</h6>
          <select id="anio" class="form-select form-select-sm w-auto" onchange="comparacion()">
            <?php for ($y = 2022; $y <= date('Y'); $y++): ?>
              <option <?= $y == date('Y') ? 'selected' : '' ?>><?= $y ?></option>
            <?php endfor; ?>
          </select>
        </div>
        <div style="height:260px;"><canvas id="comparacion"></canvas></div>
        <div class="row row-cols-2 text-center border-top mt-3 pt-3">
          <div><small class="text-muted">Total Ventas</small><h5 class="mb-0 text-success" id="totalVentas">$0</h5></div>
          <div><small class="text-muted">Total Compras</small><h5 class="mb-0 text-warning" id="totalCompras">$0</h5></div>
        </div>
      </div>
    </div>
  </div>
  <div class="col-lg-5">
    <div class="card radius-10 h-100">
      <div class="card-body">
        <h6 class="mb-3 fw-semibold"><i class="bx bx-trophy text-warning me-1"></i>Top productos</h6>
        <?php if (!empty($data['top'])): ?>
          <?php foreach ($data['top'] as $i => $t): ?>
          <div class="d-flex align-items-center mb-2 pb-2 <?= $i < count($data['top'])-1 ? 'border-bottom' : '' ?>">
            <div class="rank-badge rank-<?= $i+1 ?>"><?= $i+1 ?></div>
            <div class="flex-grow-1 ms-2">
              <div class="fw-medium"><?= htmlspecialchars($t['descripcion'] ?? '—', ENT_QUOTES) ?></div>
              <small class="text-muted"><?= $t['ventas'] ?? 0 ?> ventas</small>
            </div>
            <span class="badge rounded-pill bg-primary-subtle text-primary fs-6"><?= $t['ventas'] ?? 0 ?></span>
          </div>
          <?php endforeach; ?>
        <?php else: ?>
          <div class="text-center text-muted py-4">Sin datos de productos</div>
        <?php endif; ?>
      </div>
    </div>
  </div>
</div>

<!-- ============ Casos ============ -->
<div class="row g-3">
  <div class="col-lg-6">
    <div class="card radius-10">
      <div class="card-body">
        <div class="d-flex justify-content-between align-items-center mb-3">
          <h6 class="mb-0 fw-semibold"><i class="bx bx-bell text-info me-1"></i>Casos Ingresados</h6>
          <span class="badge bg-info-subtle text-info"><?= count($data['casosIngresado'] ?? []) ?></span>
        </div>
        <?php if (!empty($data['casosIngresado'])): ?>
          <div class="case-list">
          <?php foreach (array_slice($data['casosIngresado'], 0, 8) as $c): ?>
            <div class="case-item">
              <div class="case-avatar bg-info-subtle text-info"><?= strtoupper(substr($c['cliente'] ?? '?', 0, 1)) ?></div>
              <div class="flex-grow-1 ms-2">
                <div class="d-flex justify-content-between">
                  <strong><?= htmlspecialchars($c['cliente'] ?? '—') ?></strong>
                  <small class="text-muted"><?= htmlspecialchars($c['fecha'] ?? '') ?></small>
                </div>
                <div class="text-muted small text-truncate" style="max-width:360px;">
                  <i class="bx bx-phone"></i> <?= $c['telefonoCliente'] ?? '—' ?>
                  · <i class="bx bx-map"></i> <?= htmlspecialchars($c['direccionContrato'] ?? '—') ?>
                </div>
                <?php if (!empty($c['problema_reportado'])): ?>
                  <div class="small mt-1"><span class="badge bg-light text-dark border">Reportado</span> <?= htmlspecialchars($c['problema_reportado']) ?></div>
                <?php endif; ?>
              </div>
            </div>
          <?php endforeach; ?>
          </div>
        <?php else: ?>
          <div class="text-center text-muted py-4"><i class="bx bx-check-double fs-3"></i><div>Sin casos ingresados</div></div>
        <?php endif; ?>
      </div>
    </div>
  </div>
  <div class="col-lg-6">
    <div class="card radius-10">
      <div class="card-body">
        <div class="d-flex justify-content-between align-items-center mb-3">
          <h6 class="mb-0 fw-semibold"><i class="bx bx-loader-circle text-warning me-1"></i>Casos en proceso</h6>
          <span class="badge bg-warning-subtle text-warning"><?= count($data['casosProceso'] ?? []) ?></span>
        </div>
        <?php if (!empty($data['casosProceso'])): ?>
          <div class="case-list">
          <?php foreach (array_slice($data['casosProceso'], 0, 8) as $c): ?>
            <div class="case-item">
              <div class="case-avatar bg-warning-subtle text-warning"><?= strtoupper(substr($c['cliente'] ?? '?', 0, 1)) ?></div>
              <div class="flex-grow-1 ms-2">
                <div class="d-flex justify-content-between">
                  <strong><?= htmlspecialchars($c['cliente'] ?? '—') ?></strong>
                  <small class="text-muted"><?= htmlspecialchars($c['fecha'] ?? '') ?></small>
                </div>
                <div class="text-muted small text-truncate" style="max-width:360px;">
                  <i class="bx bx-user"></i> <?= htmlspecialchars($c['responsable'] ?? '—') ?>
                  · <i class="bx bx-phone"></i> <?= $c['telefonoCliente'] ?? '—' ?>
                </div>
                <?php if (!empty($c['trabajo_realizado'])): ?>
                  <div class="small mt-1"><span class="badge bg-warning-subtle text-warning border">Trabajo</span> <?= htmlspecialchars(substr($c['trabajo_realizado'], 0, 80)) ?></div>
                <?php endif; ?>
              </div>
            </div>
          <?php endforeach; ?>
          </div>
        <?php else: ?>
          <div class="text-center text-muted py-4"><i class="bx bx-check-circle fs-3"></i><div>Sin casos en proceso</div></div>
        <?php endif; ?>
      </div>
    </div>
  </div>
</div>

<style>
/* ===== Tablero moderno ===== */
.kpi-card {
  background: #fff;
  border-radius: 14px;
  padding: 18px;
  border: 1px solid rgba(0,0,0,.05);
  box-shadow: 0 1px 2px rgba(0,0,0,.03);
  transition: transform .15s, box-shadow .15s;
  position: relative;
  overflow: hidden;
  height: 100%;
}
.kpi-card::before {
  content: '';
  position: absolute;
  top: 0; left: 0;
  width: 4px; height: 100%;
}
.kpi-card:hover { transform: translateY(-2px); box-shadow: 0 8px 20px rgba(0,0,0,.06); }
.kpi-primary::before { background: #2563eb; }
.kpi-success::before { background: #10b981; }
.kpi-warning::before { background: #f59e0b; }
.kpi-danger::before  { background: #ef4444; }
.kpi-cobro::before     { background: #10b981; }
.kpi-pendiente::before { background: #ef4444; }
.kpi-cobro     .kpi-icon { background: rgba(16,185,129,.12); color: #10b981; }
.kpi-pendiente .kpi-icon { background: rgba(239,68,68,.12);  color: #ef4444; }

/* Mini cards de desglose dentro del card 'Pendiente por cobrar' */
.pendiente-mini {
  display: flex; align-items: center; gap: 12px;
  padding: 12px 14px;
  border-radius: 10px;
  background: #ffffff;
  border: 1px solid #e5e7eb;
  box-shadow: 0 1px 3px rgba(0,0,0,.04);
  transition: transform .15s ease, box-shadow .15s ease;
  height: 100%;
}
.pendiente-mini:hover { transform: translateY(-1px); box-shadow: 0 3px 10px rgba(0,0,0,.08); }
.pendiente-mini-icon {
  width: 42px; height: 42px;
  display: flex; align-items: center; justify-content: center;
  border-radius: 10px;
  font-size: 22px;
  flex-shrink: 0;
}
.pendiente-recibo .pendiente-mini-icon { background: rgba(13,202,240,.14); color: #0891b2; }
.pendiente-factura .pendiente-mini-icon { background: rgba(13,110,253,.14); color: #2563eb; }
.pendiente-mini-body { flex: 1; min-width: 0; }
.pendiente-mini-label {
  display: block;
  text-transform: uppercase;
  font-size: 11px;
  letter-spacing: .4px;
  color: #6b7280;
  font-weight: 600;
  margin-bottom: 2px;
}
.pendiente-mini-value {
  font-size: 20px;
  font-weight: 700;
  color: #111827;
  line-height: 1.1;
}
.pendiente-mini-count {
  display: block;
  color: #9ca3af;
  font-size: 11px;
  margin-top: 2px;
}

.kpi-body { display: flex; align-items: center; gap: 14px; }
.kpi-icon {
  width: 52px; height: 52px;
  border-radius: 12px;
  display: flex; align-items: center; justify-content: center;
  font-size: 26px;
  flex-shrink: 0;
}
.kpi-primary .kpi-icon { background: rgba(37,99,235,.1); color: #2563eb; }
.kpi-success .kpi-icon { background: rgba(16,185,129,.12); color: #10b981; }
.kpi-warning .kpi-icon { background: rgba(245,158,11,.12); color: #f59e0b; }
.kpi-danger  .kpi-icon { background: rgba(239,68,68,.12); color: #ef4444; }

.kpi-label { color: #6b7280; font-size: 12px; text-transform: uppercase; letter-spacing: .5px; font-weight: 600; }
.kpi-value { margin: 4px 0; font-size: 26px; font-weight: 700; color: #111827; line-height: 1; }
.kpi-link  { font-size: 12px; color: #6b7280; text-decoration: none; }
.kpi-link:hover { color: #2563eb; }

.mini-card {
  display: flex;
  align-items: center;
  gap: 12px;
  background: #fff;
  border: 1px solid rgba(0,0,0,.06);
  border-radius: 10px;
  padding: 12px 14px;
  height: 100%;
}
.mini-card > i { font-size: 24px; }
.mini-card small { color: #6b7280; font-size: 11px; text-transform: uppercase; letter-spacing: .5px; }
.mini-card .fw-bold { font-size: 18px; color: #111827; }

.rank-badge {
  width: 28px; height: 28px;
  border-radius: 50%;
  display: flex; align-items: center; justify-content: center;
  font-size: 13px; font-weight: 700; color: #fff;
}
.rank-1 { background: linear-gradient(135deg,#fbbf24,#f59e0b); }
.rank-2 { background: linear-gradient(135deg,#cbd5e1,#94a3b8); }
.rank-3 { background: linear-gradient(135deg,#d97706,#b45309); }
.rank-4, .rank-5 { background: #e5e7eb; color: #6b7280; }

.case-list { display: flex; flex-direction: column; gap: 10px; max-height: 430px; overflow-y: auto; }
.case-item {
  display: flex;
  align-items: flex-start;
  padding: 10px;
  border-radius: 8px;
  transition: background .12s;
}
.case-item:hover { background: #f9fafb; }
.case-avatar {
  width: 36px; height: 36px;
  border-radius: 10px;
  display: flex; align-items: center; justify-content: center;
  font-weight: 700; font-size: 14px;
  flex-shrink: 0;
}

.bg-info-subtle { background: rgba(14,165,233,.1) !important; }
.text-info { color: #0ea5e9 !important; }
.bg-warning-subtle { background: rgba(245,158,11,.12) !important; }
.text-warning { color: #f59e0b !important; }
.bg-primary-subtle { background: rgba(37,99,235,.1) !important; }

@media (max-width: 768px) {
  .kpi-value { font-size: 22px; }
  .kpi-icon { width: 42px; height: 42px; font-size: 22px; }
}
</style>

<?php include_once 'views/templates/footer.php'; ?>

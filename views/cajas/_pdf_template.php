<?php
// Template PDF compartido entre reporte.php y reporteActual.php (DRY).
// Variables esperadas en scope:
//   $tituloDoc        - titulo del documento (ej. "Reporte de caja")
//   $numeroCaja       - id de la caja (o null si es actual sin cerrar)
//   $isActual         - bool: true si es reporte de caja activa
//   $usuarioActual    - nombre del usuario (solo si $isActual)
//   $empresa          - array con nombre, ruc, telefono, direccion, mensaje
//   $mov              - array con: inicial, ingresos, egresos, gastos, saldo  (todos formatted)
//   $tp               - array con: efectivo, bancarisado, saldoEfectivo
//   $gastos           - array de gastos [descripcion, monto, fecha]

// Logo embebido en base64 (Dompdf no resuelve URLs externas confiablemente).
$_logoPath = ROOT_PATH . '/assets/images/' . (is_file(ROOT_PATH . '/assets/images/Logo.jpg') ? 'Logo.jpg' : (is_file(ROOT_PATH . '/assets/images/logo.png') ? 'logo.png' : 'logoedessi.png'));
$_logoSrc  = '';
if (is_file($_logoPath)) {
    $_logoData = @file_get_contents($_logoPath);
    if ($_logoData !== false) {
        $_logoSrc = 'data:image/png;base64,' . base64_encode($_logoData);
    }
}

$_e = function ($s) { return htmlspecialchars((string)$s, ENT_QUOTES, 'UTF-8'); };
?>
<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<title><?php echo $_e($tituloDoc); ?></title>
<style>
    @page { size: A4; margin: 28mm 18mm; }
    * { box-sizing: border-box; }
    body {
        font-family: DejaVu Sans, sans-serif;
        font-size: 11px;
        line-height: 1.5;
        color: #1f2937;
        margin: 0;
    }
    .header {
        width: 100%;
        border-bottom: 2px solid #2563eb;
        padding-bottom: 14px;
        margin-bottom: 22px;
    }
    .header table { width: 100%; border-collapse: collapse; }
    .header td { vertical-align: top; padding: 0; }
    .header .logo-cell { width: 110px; padding-right: 10px; }
    .header .logo-cell img { max-width: 100px; max-height: 70px; }
    .header .empresa-cell { padding-left: 4px; padding-right: 12px; }
    .header .empresa-cell .nombre { font-size: 16px; font-weight: bold; color: #111827; margin: 0 0 6px 0; }
    .header .empresa-cell p { margin: 2px 0; font-size: 11px; color: #4b5563; line-height: 1.4; }
    .header .doc-cell { width: 200px; text-align: right; }
    .header .doc-cell .badge {
        display: inline-block;
        background: #2563eb;
        color: #fff;
        padding: 6px 14px;
        font-size: 12px;
        font-weight: bold;
        border-radius: 5px;
        letter-spacing: .5px;
    }
    .header .doc-cell .doc-num {
        font-size: 18px;
        font-weight: bold;
        color: #111827;
        margin: 8px 0 0 0;
    }
    .header .doc-cell .doc-meta { font-size: 10px; color: #6b7280; margin: 4px 0 0 0; line-height: 1.4; }
    .header .doc-cell .actual-pill {
        display: inline-block;
        background: #16a34a;
        color: #fff;
        padding: 3px 10px;
        font-size: 10px;
        font-weight: bold;
        border-radius: 12px;
        text-transform: uppercase;
        letter-spacing: .3px;
    }

    h2.section {
        background: #f3f4f6;
        color: #111827;
        font-size: 13px;
        font-weight: bold;
        padding: 9px 12px;
        margin: 22px 0 10px 0;
        border-left: 4px solid #2563eb;
        text-transform: uppercase;
        letter-spacing: .8px;
    }

    table.dt {
        width: 100%;
        border-collapse: collapse;
        margin-top: 0;
        font-size: 11px;
    }
    table.dt thead th {
        background: #2563eb;
        color: #fff;
        font-weight: bold;
        text-align: center;
        padding: 9px 10px;
        border: 1px solid #1d4ed8;
        line-height: 1.3;
    }
    table.dt tbody td {
        border: 1px solid #e5e7eb;
        padding: 8px 10px;
        text-align: center;
        line-height: 1.4;
    }
    table.dt tbody tr:nth-child(odd) td { background: #f9fafb; }
    table.dt .num {
        font-family: DejaVu Sans Mono, monospace;
        text-align: right;
        white-space: nowrap;
    }
    table.dt .col-saldo {
        background: #ecfdf5 !important;
        color: #065f46;
        font-weight: bold;
    }
    .empty {
        background: #f9fafb;
        color: #9ca3af;
        text-align: center;
        padding: 18px;
        border: 1px dashed #d1d5db;
        font-style: italic;
        font-size: 11px;
        margin: 4px 0;
    }
    .footer-msg {
        margin-top: 22px;
        padding: 12px 14px;
        background: #fffbeb;
        border-left: 4px solid #f59e0b;
        font-size: 11px;
        color: #92400e;
        line-height: 1.5;
    }
    .gen-meta {
        margin-top: 18px;
        text-align: right;
        font-size: 9px;
        color: #9ca3af;
    }
</style>
</head>
<body>

<div class="header">
    <table>
        <tr>
            <td class="logo-cell">
                <?php if ($_logoSrc !== '') { ?>
                    <img src="<?php echo $_logoSrc; ?>" alt="">
                <?php } ?>
            </td>
            <td class="empresa-cell">
                <p class="nombre"><?php echo $_e($empresa['nombre'] ?? ''); ?></p>
                <p>RUC: <?php echo $_e($empresa['ruc'] ?? ''); ?></p>
                <p>Teléfono: <?php echo $_e($empresa['telefono'] ?? ''); ?></p>
                <p>Dirección: <?php echo $_e(trim(html_entity_decode(strip_tags((string)($empresa['direccion'] ?? '')), ENT_QUOTES | ENT_HTML5, 'UTF-8'))); ?></p>
            </td>
            <td class="doc-cell">
                <span class="badge">REPORTE DE CAJA</span>
                <?php if ($isActual) { ?>
                    <p class="doc-num">— ACTUAL —</p>
                    <p class="doc-meta"><?php echo $_e($usuarioActual ?? ''); ?></p>
                    <p class="doc-meta"><span class="actual-pill">EN CURSO</span></p>
                <?php } else { ?>
                    <p class="doc-num">N° <?php echo $_e($numeroCaja); ?></p>
                <?php } ?>
                <p class="doc-meta">Generado: <?php echo date('d/m/Y H:i'); ?></p>
            </td>
        </tr>
    </table>
</div>

<h2 class="section">Resumen de movimientos</h2>
<table class="dt">
    <thead>
        <tr>
            <th>Monto Inicial</th>
            <th>Ingresos</th>
            <th>Egresos</th>
            <th>Gastos</th>
            <th>Saldo</th>
        </tr>
    </thead>
    <tbody>
        <tr>
            <td class="num">$ <?php echo $_e($mov['inicial']); ?></td>
            <td class="num">$ <?php echo $_e($mov['ingresos']); ?></td>
            <td class="num">$ <?php echo $_e($mov['egresos']); ?></td>
            <td class="num">$ <?php echo $_e($mov['gastos']); ?></td>
            <td class="num col-saldo">$ <?php echo $_e($mov['saldo']); ?></td>
        </tr>
    </tbody>
</table>

<h2 class="section">Tipos de pago</h2>
<table class="dt">
    <thead>
        <tr>
            <th>Efectivo</th>
            <th>Bancarizado</th>
            <th>Saldo Efectivo</th>
        </tr>
    </thead>
    <tbody>
        <tr>
            <td class="num">$ <?php echo $_e($tp['efectivo']); ?></td>
            <td class="num">$ <?php echo $_e($tp['bancarisado']); ?></td>
            <td class="num col-saldo">$ <?php echo $_e($tp['saldoEfectivo']); ?></td>
        </tr>
    </tbody>
</table>

<h2 class="section">Historial de gastos</h2>
<?php if (empty($gastos)) { ?>
    <div class="empty">No hay gastos registrados en esta caja.</div>
<?php } else { ?>
<table class="dt">
    <thead>
        <tr>
            <th style="width:50%">Descripción</th>
            <th style="width:20%">Monto</th>
            <th style="width:30%">Fecha</th>
        </tr>
    </thead>
    <tbody>
        <?php foreach ($gastos as $g) { ?>
        <tr>
            <td style="text-align:left"><?php echo $_e($g['descripcion'] ?? ''); ?></td>
            <td class="num">$ <?php echo $_e($g['monto'] ?? '0.00'); ?></td>
            <td><?php echo $_e($g['fecha'] ?? ''); ?></td>
        </tr>
        <?php } ?>
    </tbody>
</table>
<?php } ?>

<?php if (!empty($empresa['mensaje'])) { ?>
    <div class="footer-msg">
        <?php echo $empresa['mensaje']; ?>
    </div>
<?php } ?>

<div class="gen-meta">
    Generado automáticamente por <?php echo $_e(defined('TITLE') ? TITLE : 'Sistema'); ?>
</div>

</body>
</html>

<?php
// Logo embebido en base64 (Dompdf no resuelve URLs externas confiablemente).
$_logoSrc = '';
foreach (['logo.png', 'Logo.jpg', 'logo.jpg'] as $_logoName) {
    $_lp = ROOT_PATH . '/assets/images/' . $_logoName;
    if (is_file($_lp)) {
        $_data = @file_get_contents($_lp);
        if ($_data !== false) {
            $_ext = strtolower(pathinfo($_logoName, PATHINFO_EXTENSION));
            $_mime = $_ext === 'png' ? 'image/png' : 'image/jpeg';
            $_logoSrc = 'data:' . $_mime . ';base64,' . base64_encode($_data);
            break;
        }
    }
}
$_e = function ($s) { return htmlspecialchars((string)$s, ENT_QUOTES, 'UTF-8'); };

$productos = $data['productos'] ?? [];
$empresa   = $data['empresa']   ?? [];

// Totales para el resumen al pie
$totalStock = 0;
$totalVentas = 0;
$totalValorInv = 0;
foreach ($productos as $p) {
    $totalStock    += (int)($p['cantidad'] ?? 0);
    $totalVentas   += (int)($p['ventas']   ?? 0);
    $totalValorInv += ((float)($p['precio_compra'] ?? 0)) * ((int)($p['cantidad'] ?? 0));
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<title><?php echo $_e($data['title'] ?? 'Reporte de Productos'); ?></title>
<style>
    @page { margin: 22mm 14mm; }
    * { box-sizing: border-box; }
    body { font-family: DejaVu Sans, sans-serif; font-size: 10px; color: #1f2937; margin: 0; }

    /* Header */
    .header { width: 100%; border-bottom: 2px solid #2563eb; padding-bottom: 10px; margin-bottom: 14px; }
    .header table { width: 100%; border-collapse: collapse; }
    .header td { vertical-align: top; padding: 0; }
    .header .logo-cell { width: 90px; }
    .header .logo-cell img { max-width: 80px; max-height: 60px; }
    .header .empresa-cell { padding-left: 8px; }
    .header .empresa-cell .nombre { font-size: 14px; font-weight: bold; color: #111827; margin: 0 0 3px 0; }
    .header .empresa-cell p { margin: 1px 0; font-size: 10px; color: #4b5563; }
    .header .doc-cell { width: 180px; text-align: right; }
    .header .doc-cell .badge {
        display: inline-block;
        background: #2563eb;
        color: #fff;
        padding: 4px 12px;
        font-size: 12px;
        font-weight: bold;
        border-radius: 4px;
        letter-spacing: .5px;
    }
    .header .doc-cell .doc-meta { font-size: 10px; color: #6b7280; margin: 6px 0 0 0; }

    /* Section title */
    h2.section {
        background: #f3f4f6;
        color: #111827;
        font-size: 12px;
        font-weight: bold;
        padding: 6px 10px;
        margin: 0 0 0 0;
        border-left: 3px solid #2563eb;
        text-transform: uppercase;
        letter-spacing: .5px;
    }

    /* Tabla de productos */
    table.dt {
        width: 100%;
        border-collapse: collapse;
        margin: 0;
        font-size: 10px;
    }
    table.dt thead th {
        background: #2563eb;
        color: #fff;
        font-weight: bold;
        padding: 6px 8px;
        border: 1px solid #1d4ed8;
        text-align: center;
    }
    table.dt tbody td {
        border: 1px solid #e5e7eb;
        padding: 5px 8px;
    }
    table.dt tbody tr:nth-child(odd) td { background: #f9fafb; }
    table.dt .num { text-align: right; font-family: DejaVu Sans Mono, monospace; }
    table.dt .ctr { text-align: center; }
    table.dt tfoot td {
        background: #ecfdf5;
        color: #065f46;
        font-weight: bold;
        padding: 6px 8px;
        border: 1px solid #a7f3d0;
    }

    .empty {
        background: #f9fafb;
        color: #9ca3af;
        text-align: center;
        padding: 14px;
        border: 1px dashed #d1d5db;
        font-style: italic;
    }

    .gen-meta { margin-top: 14px; text-align: right; font-size: 9px; color: #9ca3af; }
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
                <p class="nombre"><?php echo $_e(strtoupper($empresa['nombre'] ?? '')); ?></p>
                <?php if (!empty($empresa['razon_social'])) { ?>
                    <p><?php echo $_e($empresa['razon_social']); ?></p>
                <?php } ?>
                <?php if (!empty($empresa['direccion'])) { ?>
                    <p><?php echo $_e($empresa['direccion']); ?></p>
                <?php } ?>
                <?php if (!empty($empresa['ruc'])) { ?>
                    <p>RUC: <?php echo $_e($empresa['ruc']); ?></p>
                <?php } ?>
                <?php if (!empty($empresa['telefono'])) { ?>
                    <p>Teléfono: <?php echo $_e($empresa['telefono']); ?></p>
                <?php } ?>
                <?php if (!empty($empresa['correo'])) { ?>
                    <p>Email: <?php echo $_e($empresa['correo']); ?></p>
                <?php } ?>
            </td>
            <td class="doc-cell">
                <span class="badge">REPORTE DE PRODUCTOS</span>
                <p class="doc-meta">Fecha: <?php echo date('d/m/Y'); ?></p>
                <p class="doc-meta">Hora: <?php echo date('H:i:s'); ?></p>
                <p class="doc-meta">Total: <strong><?php echo count($productos); ?></strong> productos</p>
            </td>
        </tr>
    </table>
</div>

<h2 class="section">Detalle de productos</h2>

<?php if (empty($productos)) { ?>
    <div class="empty">No hay productos registrados.</div>
<?php } else { ?>
<table class="dt">
    <thead>
        <tr>
            <th style="width:6%">#</th>
            <th style="width:14%">Código</th>
            <th>Descripción</th>
            <th style="width:12%">P. Compra</th>
            <th style="width:12%">P. Venta</th>
            <th style="width:9%">Stock</th>
            <th style="width:9%">Ventas</th>
        </tr>
    </thead>
    <tbody>
        <?php $i = 1; foreach ($productos as $p) { ?>
        <tr>
            <td class="ctr"><?php echo $i++; ?></td>
            <td><?php echo $_e($p['codigo'] ?? ''); ?></td>
            <td><?php echo $_e($p['descripcion'] ?? ''); ?></td>
            <td class="num">$ <?php echo number_format((float)($p['precio_compra'] ?? 0), 2); ?></td>
            <td class="num">$ <?php echo number_format((float)($p['precio_venta'] ?? 0), 2); ?></td>
            <td class="ctr"><?php echo (int)($p['cantidad'] ?? 0); ?></td>
            <td class="ctr"><?php echo (int)($p['ventas'] ?? 0); ?></td>
        </tr>
        <?php } ?>
    </tbody>
    <tfoot>
        <tr>
            <td colspan="3" style="text-align:right">TOTALES</td>
            <td class="num">Valor inv: $ <?php echo number_format($totalValorInv, 2); ?></td>
            <td></td>
            <td class="ctr"><?php echo $totalStock; ?></td>
            <td class="ctr"><?php echo $totalVentas; ?></td>
        </tr>
    </tfoot>
</table>
<?php } ?>

<div class="gen-meta">
    Generado por <?php echo $_e(defined('TITLE') ? TITLE : 'Sistema'); ?> el <?php echo date('d/m/Y H:i'); ?>
</div>

</body>
</html>

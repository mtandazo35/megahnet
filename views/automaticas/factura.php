<?php
// Reporte PDF: Cierre de corte automatico (facturas + ordenes de venta).
$emp  = $data['empresa'] ?? [];

$pdfTitulo    = 'CIERRE DE CORTE';
$pdfSubtitulo = 'Facturas y órdenes';
$pdfMeta = [
    ['label' => 'Total Facturas', 'value' => '$ ' . number_format((float)($data['totalFacturas'] ?? 0), 2)],
    ['label' => 'Total Órdenes',  'value' => '$ ' . number_format((float)($data['totalOrdenes']  ?? 0), 2)],
];

$_e = function ($s) { return htmlspecialchars((string)$s, ENT_QUOTES, 'UTF-8'); };
$infoFacturas = !empty($data['infoFacturas']) ? json_decode($data['infoFacturas'], true) : [];
$infoOrdenes  = !empty($data['infoOrdenes'])  ? json_decode($data['infoOrdenes'],  true) : [];
if (!is_array($infoFacturas)) $infoFacturas = [];
if (!is_array($infoOrdenes))  $infoOrdenes  = [];

$totalFacturas = 0;
foreach ($infoFacturas as $r) { $totalFacturas += (float)($r['totalfactura'] ?? 0); }
$totalOrdenes  = 0;
foreach ($infoOrdenes  as $r) { $totalOrdenes  += (float)($r['total'] ?? 0); }

ob_start();
?>
<h2 class="section">Detalle de facturas</h2>
<?php if (empty($infoFacturas)) { ?>
    <div class="empty">Sin facturas en el corte.</div>
<?php } else { ?>
<table class="dt">
    <thead>
        <tr>
            <th style="width:15%">Factura</th>
            <th>Cliente</th>
            <th style="width:18%">Total</th>
        </tr>
    </thead>
    <tbody>
        <?php foreach ($infoFacturas as $row) { ?>
        <tr>
            <td class="ctr"><?php echo $_e($row['orden_no'] ?? ''); ?></td>
            <td><?php echo $_e($row['cliente'] ?? ''); ?></td>
            <td class="num">$ <?php echo number_format((float)($row['totalfactura'] ?? 0), 2, '.', ','); ?></td>
        </tr>
        <?php } ?>
    </tbody>
    <tfoot>
        <tr>
            <td colspan="2" style="text-align:right">TOTAL FACTURAS</td>
            <td class="num">$ <?php echo number_format($totalFacturas, 2); ?></td>
        </tr>
    </tfoot>
</table>
<?php } ?>

<h2 class="section">Detalle de órdenes de venta</h2>
<?php if (empty($infoOrdenes)) { ?>
    <div class="empty">Sin órdenes de venta en el corte.</div>
<?php } else { ?>
<table class="dt">
    <thead>
        <tr>
            <th style="width:15%">Orden Venta</th>
            <th>Cliente</th>
            <th style="width:18%">Total</th>
        </tr>
    </thead>
    <tbody>
        <?php foreach ($infoOrdenes as $row) { ?>
        <tr>
            <td class="ctr"><?php echo $_e($row['serie'] ?? ''); ?></td>
            <td><?php echo $_e($row['nombre'] ?? ''); ?></td>
            <td class="num">$ <?php echo number_format((float)($row['total'] ?? 0), 2, '.', ','); ?></td>
        </tr>
        <?php } ?>
    </tbody>
    <tfoot>
        <tr>
            <td colspan="2" style="text-align:right">TOTAL ÓRDENES</td>
            <td class="num">$ <?php echo number_format($totalOrdenes, 2); ?></td>
        </tr>
    </tfoot>
</table>
<?php }
$pdfContenido = ob_get_clean();
$empresa = $emp;
include __DIR__ . '/../templates/_pdf_layout.php';

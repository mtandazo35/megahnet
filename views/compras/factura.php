<?php
// Reporte PDF: Compra (registro interno con detalle de productos).
$compra = $data['compra'] ?? [];
$emp    = $data['empresa'] ?? [];

$pdfTitulo    = 'COMPRA';
$pdfSubtitulo = 'N° ' . ($compra['serie'] ?? '');
$pdfMeta = [
    ['label' => 'Fecha', 'value' => $compra['fecha'] ?? ''],
    ['label' => 'Hora',  'value' => $compra['hora']  ?? ''],
];

$_e = function ($s) { return htmlspecialchars((string)$s, ENT_QUOTES, 'UTF-8'); };
$ivaFactor = defined('CONCAT') ? CONCAT : '1.';
$iva = (float)($emp['impuesto'] ?? 0);
$productos = !empty($compra['productos']) ? json_decode($compra['productos'], true) : [];
if (!is_array($productos)) $productos = [];

// Totales (manteniendo logica original)
$subtotaldoce  = 0;  $subtotaldocev = 0;
$subtotalcero  = 0;
foreach ($productos as $row) {
    $cant = (float)($row['cantidad'] ?? 0);
    $pre  = (float)($row['precio'] ?? 0);
    if ((int)($row['iva_producto'] ?? 0) == (int)$iva) {
        $pv = round($pre / (float)($ivaFactor . $iva), 4);
        $pt = round($pv * $cant, 4);
        $subtotaldocev += $pt;
    } else {
        $subtotalcero += round($pre * $cant, 4);
    }
}
$subtotaldoce = round($subtotaldocev, 2);
$subtotalcero = round($subtotalcero, 2);
$descuento    = 0;
$subtotal     = round($subtotaldoce + $subtotalcero, 2);
$impuesto     = round($subtotaldocev * ($iva / 100), 2);
$total        = round(($subtotal + $impuesto) - $descuento, 2);

ob_start();
?>
<h2 class="section">Datos del proveedor</h2>
<table class="dt" style="margin-top:6px">
    <tr>
        <td style="width:25%; background:#f9fafb"><strong>RUC:</strong></td>
        <td style="width:25%"><?php echo $_e($compra['ruc'] ?? ''); ?></td>
        <td style="width:25%; background:#f9fafb"><strong>Razón social:</strong></td>
        <td style="width:25%"><?php echo $_e($compra['nombre'] ?? ''); ?></td>
    </tr>
    <tr>
        <td style="background:#f9fafb"><strong>Teléfono:</strong></td>
        <td><?php echo $_e($compra['telefono'] ?? ''); ?></td>
        <td style="background:#f9fafb"><strong>Dirección:</strong></td>
        <td><?php echo $_e(trim(html_entity_decode(strip_tags((string)($compra['direccion'] ?? '')), ENT_QUOTES | ENT_HTML5, 'UTF-8'))); ?></td>
    </tr>
</table>

<h2 class="section">Detalle de productos</h2>
<?php if (empty($productos)) { ?>
    <div class="empty">Sin productos.</div>
<?php } else { ?>
<table class="dt">
    <thead>
        <tr>
            <th style="width:8%">Cant.</th>
            <th>Descripción</th>
            <th style="width:18%">P. Unitario</th>
            <th style="width:18%">P. Total</th>
        </tr>
    </thead>
    <tbody>
        <?php foreach ($productos as $row) {
            $cant = (float)($row['cantidad'] ?? 0);
            $pre  = (float)($row['precio'] ?? 0);
            if ((int)($row['iva_producto'] ?? 0) == (int)$iva) {
                $pv = round($pre / (float)($ivaFactor . $iva), 4);
                $pt = round($pv * $cant, 4);
            } else {
                $pv = round($pre, 4);
                $pt = round($pre * $cant, 4);
            }
        ?>
        <tr>
            <td class="ctr"><?php echo $cant; ?></td>
            <td><?php echo $_e($row['nombre'] ?? ''); ?></td>
            <td class="num"><?php echo number_format($pv, 4, '.', ','); ?></td>
            <td class="num"><?php echo number_format($pt, 2, '.', ','); ?></td>
        </tr>
        <?php } ?>
    </tbody>
    <tfoot>
        <tr>
            <td colspan="3" style="text-align:right">SUBTOTAL <?php echo (int)$iva; ?>%</td>
            <td class="num">$ <?php echo number_format($subtotaldoce, 2); ?></td>
        </tr>
        <tr>
            <td colspan="3" style="text-align:right">SUBTOTAL 0%</td>
            <td class="num">$ <?php echo number_format($subtotalcero, 2); ?></td>
        </tr>
        <tr>
            <td colspan="3" style="text-align:right">DESCUENTO</td>
            <td class="num">$ <?php echo number_format($descuento, 2); ?></td>
        </tr>
        <tr>
            <td colspan="3" style="text-align:right">SUBTOTAL</td>
            <td class="num">$ <?php echo number_format($subtotal, 2); ?></td>
        </tr>
        <tr>
            <td colspan="3" style="text-align:right">IVA (<?php echo (int)$iva; ?>%)</td>
            <td class="num">$ <?php echo number_format($impuesto, 2); ?></td>
        </tr>
        <tr>
            <td colspan="3" style="text-align:right; font-size: 13px">TOTAL</td>
            <td class="num" style="font-size: 13px">$ <?php echo number_format($total, 2); ?></td>
        </tr>
    </tfoot>
</table>
<?php }
$pdfContenido = ob_get_clean();
$empresa = $emp;
include __DIR__ . '/../templates/_pdf_layout.php';

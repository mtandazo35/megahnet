<?php
// Reporte PDF: Cotizacion con detalle de productos y datos bancarios.
$cot  = $data['cotizacion'] ?? [];
$emp  = $data['empresa']    ?? [];

$pdfTitulo    = 'COTIZACIÓN';
$pdfSubtitulo = 'N° ' . ($cot['id'] ?? '');
$pdfMeta = [
    ['label' => 'Fecha', 'value' => $cot['fecha'] ?? ''],
    ['label' => 'Hora',  'value' => $cot['hora']  ?? ''],
];

$_e = function ($s) { return htmlspecialchars((string)$s, ENT_QUOTES, 'UTF-8'); };
$ivaFactor = defined('CONCAT') ? CONCAT : '1.';
$iva = (float)($emp['impuesto'] ?? 0);
$productos = !empty($cot['productos']) ? json_decode($cot['productos'], true) : [];
if (!is_array($productos)) $productos = [];

$subtotaldocev = 0; $subtotalcero = 0;
foreach ($productos as $row) {
    $cant = (float)($row['cantidad'] ?? 0);
    $pre  = (float)($row['precio'] ?? 0);
    if ((float)($row['iva_producto'] ?? 0) == $iva) {
        $pv = round($pre / (float)($ivaFactor . $iva), 4);
        $pt = round($pv * $cant, 2);
        $subtotaldocev += $pt;
    } else {
        $subtotalcero += round($pre * $cant, 4);
    }
}
$subtotaldoce = round($subtotaldocev, 2);
$subtotalcero = round($subtotalcero, 2);
$descuento    = round((float)($cot['descuento'] ?? 0), 2);
$subtotal     = round($subtotaldoce + $subtotalcero, 2);
$impuesto     = round($subtotaldocev * ($iva / 100), 2);
$total        = round(($subtotal + $impuesto) - $descuento, 2);

ob_start();
?>
<h2 class="section">Datos del cliente</h2>
<table class="dt" style="margin-top:6px">
    <tr>
        <td style="width:25%; background:#f9fafb"><strong><?php echo $_e($cot['identidad'] ?? 'CI/RUC'); ?>:</strong></td>
        <td style="width:25%"><?php echo $_e($cot['num_identidad'] ?? ''); ?></td>
        <td style="width:25%; background:#f9fafb"><strong>Razón social:</strong></td>
        <td style="width:25%"><?php echo $_e($cot['nombre'] ?? ''); ?></td>
    </tr>
    <tr>
        <td style="background:#f9fafb"><strong>Teléfono:</strong></td>
        <td><?php echo $_e($cot['telefono'] ?? ''); ?></td>
        <td style="background:#f9fafb"><strong>Dirección:</strong></td>
        <td><?php echo $_e($cot['direccion'] ?? ''); ?></td>
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
            if ((float)($row['iva_producto'] ?? 0) == $iva) {
                $pv = round($pre / (float)($ivaFactor . $iva), 4);
                $pt = round($pv * $cant, 2);
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
            <td colspan="3" style="text-align:right; font-size:13px">TOTAL</td>
            <td class="num" style="font-size:13px">$ <?php echo number_format($total, 2); ?></td>
        </tr>
    </tfoot>
</table>
<?php } ?>

<div style="margin-top:16px; padding:12px; background:#f0f9ff; border-left: 4px solid #0ea5e9; font-size:11px; line-height:1.6;">
    <strong>Datos para pago:</strong><br>
    Cotización válida por <strong><?php echo $_e($cot['validez'] ?? '5'); ?></strong> días laborales. Los precios se sujetan a cambios.
</div>
<?php
$pdfContenido = ob_get_clean();
$empresa = $emp;
include __DIR__ . '/../templates/_pdf_layout.php';

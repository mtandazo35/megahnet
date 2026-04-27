<?php
// Reporte PDF: Contrato con detalle de productos y datos del servicio (red).
$con  = $data['contrato'] ?? [];
$emp  = $data['empresa']  ?? [];

$pdfTitulo    = 'CONTRATO';
$pdfSubtitulo = 'N° ' . ($con['id'] ?? '');
$pdfMeta = [
    ['label' => 'Fecha', 'value' => $con['fecha'] ?? ''],
    ['label' => 'Hora',  'value' => $con['hora']  ?? ''],
];

$_e = function ($s) { return htmlspecialchars((string)$s, ENT_QUOTES, 'UTF-8'); };
$ivaFactor = defined('CONCAT') ? CONCAT : '1.';
$iva = (float)($emp['impuesto'] ?? 0);
$productos = !empty($con['productos']) ? json_decode($con['productos'], true) : [];
if (!is_array($productos)) $productos = [];

$subtotaldocev = 0; $subtotalcero = 0;
foreach ($productos as $row) {
    $cant = (float)($row['cantidad'] ?? 0);
    $pre  = (float)($row['precio'] ?? 0);
    if ((float)($row['iva_producto'] ?? 0) == $iva) {
        $pv = round($pre / (float)($ivaFactor . $iva), 4);
        $pt = round($pv * $cant, 4);
        $subtotaldocev += $pt;
    } else {
        $subtotalcero += round($pre * $cant, 4);
    }
}
$subtotaldoce = round($subtotaldocev, 2);
$subtotalcero = round($subtotalcero, 2);
$subtotal     = round($subtotaldoce + $subtotalcero, 2);
$impuesto     = round($subtotaldocev * ($iva / 100), 2);
$total        = round($subtotal + $impuesto, 2);

ob_start();
?>
<h2 class="section">Datos del cliente</h2>
<table class="dt" style="margin-top:6px">
    <tr>
        <td style="width:25%; background:#f9fafb"><strong><?php echo $_e($con['identidad'] ?? 'CI/RUC'); ?>:</strong></td>
        <td style="width:25%"><?php echo $_e($con['num_identidad'] ?? ''); ?></td>
        <td style="width:25%; background:#f9fafb"><strong>Razón social:</strong></td>
        <td style="width:25%"><?php echo $_e($con['nombre'] ?? ''); ?></td>
    </tr>
    <tr>
        <td style="background:#f9fafb"><strong>Teléfono:</strong></td>
        <td><?php echo $_e($con['telefono'] ?? ''); ?></td>
        <td style="background:#f9fafb"><strong>Dirección:</strong></td>
        <td><?php echo $_e(trim(html_entity_decode(strip_tags((string)($con['direccionCliente'] ?? '')), ENT_QUOTES | ENT_HTML5, 'UTF-8'))); ?></td>
    </tr>
</table>

<h2 class="section">Datos del servicio</h2>
<table class="dt" style="margin-top:6px">
    <tr>
        <td style="width:18%; background:#f9fafb"><strong>IP Usuario:</strong></td>
        <td style="width:15%"><?php echo $_e($con['ip_usuario'] ?? ''); ?></td>
        <td style="width:18%; background:#f9fafb"><strong>Repetidora:</strong></td>
        <td style="width:15%"><?php echo $_e($con['repetidora'] ?? ''); ?></td>
        <td style="width:8%; background:#f9fafb"><strong>AP:</strong></td>
        <td style="width:auto"><?php echo $_e($con['ap'] ?? ''); ?></td>
    </tr>
    <tr>
        <td style="background:#f9fafb"><strong>Medio TR/TX:</strong></td>
        <td><?php echo $_e($con['medio'] ?? ''); ?></td>
        <td style="background:#f9fafb"><strong>Compartición:</strong></td>
        <td><?php echo $_e($con['comparticion'] ?? ''); ?></td>
        <td style="background:#f9fafb"><strong>Coord:</strong></td>
        <td><?php echo $_e($con['coordenada'] ?? ''); ?></td>
    </tr>
    <tr>
        <td style="background:#f9fafb"><strong>Dirección:</strong></td>
        <td colspan="2"><?php echo $_e(trim(html_entity_decode(strip_tags((string)($con['direccion'] ?? '')), ENT_QUOTES | ENT_HTML5, 'UTF-8'))); ?></td>
        <td style="background:#f9fafb"><strong>Comentario:</strong></td>
        <td colspan="2"><?php echo $_e($con['comentario'] ?? ''); ?></td>
    </tr>
</table>

<h2 class="section">Detalle de productos</h2>
<?php if (empty($productos)) { ?>
    <div class="empty">Sin productos.</div>
<?php } else { ?>
<table class="dt">
    <thead>
        <tr>
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
                $pt = round($pv * $cant, 4);
            } else {
                $pv = round($pre, 4);
                $pt = round($pre * $cant, 4);
            }
        ?>
        <tr>
            <td><?php echo $_e($row['nombre'] ?? ''); ?></td>
            <td class="num"><?php echo number_format($pv, 4, '.', ','); ?></td>
            <td class="num"><?php echo number_format($pt, 2, '.', ','); ?></td>
        </tr>
        <?php } ?>
    </tbody>
    <tfoot>
        <tr>
            <td colspan="2" style="text-align:right">SUBTOTAL <?php echo (int)$iva; ?>%</td>
            <td class="num">$ <?php echo number_format($subtotaldoce, 2); ?></td>
        </tr>
        <tr>
            <td colspan="2" style="text-align:right">SUBTOTAL 0%</td>
            <td class="num">$ <?php echo number_format($subtotalcero, 2); ?></td>
        </tr>
        <tr>
            <td colspan="2" style="text-align:right">SUBTOTAL</td>
            <td class="num">$ <?php echo number_format($subtotal, 2); ?></td>
        </tr>
        <tr>
            <td colspan="2" style="text-align:right">IVA (<?php echo (int)$iva; ?>%)</td>
            <td class="num">$ <?php echo number_format($impuesto, 2); ?></td>
        </tr>
        <tr>
            <td colspan="2" style="text-align:right; font-size:13px">TOTAL</td>
            <td class="num" style="font-size:13px">$ <?php echo number_format($total, 2); ?></td>
        </tr>
    </tfoot>
</table>
<?php }
$pdfContenido = ob_get_clean();
$empresa = $emp;
include __DIR__ . '/../templates/_pdf_layout.php';

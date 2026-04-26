<?php
// Reporte PDF: Retencion (mismo formato que factura, datos en $data['venta']).
$venta = $data['venta']   ?? [];
$emp   = $data['empresa'] ?? [];

$pdfTitulo    = 'RETENCIÓN';
$pdfSubtitulo = 'N° ' . ($venta['serie'] ?? '');
$pdfMeta = [
    ['label' => 'Fecha', 'value' => $venta['fecha'] ?? ''],
    ['label' => 'Hora',  'value' => $venta['hora']  ?? ''],
];

$_e = function ($s) { return htmlspecialchars((string)$s, ENT_QUOTES, 'UTF-8'); };
$productos = !empty($venta['productos']) ? json_decode($venta['productos'], true) : [];
if (!is_array($productos)) $productos = [];

$totalRaw  = (float)($venta['total'] ?? 0);
$descuento = (float)($venta['descuento'] ?? 0);
$subTotal  = $totalRaw / 1.12;
$igv       = $totalRaw - $subTotal;
$totalSD   = $totalRaw - $descuento;
$totalCD   = $totalRaw;
$anulada   = (int)($venta['estado'] ?? 1) === 0;

ob_start();
?>
<h2 class="section">Datos del cliente</h2>
<table class="dt" style="margin-top:6px">
    <tr>
        <td style="width:25%; background:#f9fafb"><strong><?php echo $_e($venta['identidad'] ?? 'CI/RUC'); ?>:</strong></td>
        <td style="width:25%"><?php echo $_e($venta['num_identidad'] ?? ''); ?></td>
        <td style="width:25%; background:#f9fafb"><strong>Nombre:</strong></td>
        <td style="width:25%"><?php echo $_e($venta['nombre'] ?? ''); ?></td>
    </tr>
    <tr>
        <td style="background:#f9fafb"><strong>Teléfono:</strong></td>
        <td><?php echo $_e($venta['telefono'] ?? ''); ?></td>
        <td style="background:#f9fafb"><strong>Dirección:</strong></td>
        <td><?php echo $_e($venta['direccion'] ?? ''); ?></td>
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
            <th style="width:18%">Subtotal</th>
        </tr>
    </thead>
    <tbody>
        <?php foreach ($productos as $p) {
            $cant = (float)($p['cantidad'] ?? 0);
            $pre  = (float)($p['precio'] ?? 0);
        ?>
        <tr>
            <td class="ctr"><?php echo $cant; ?></td>
            <td><?php echo $_e($p['nombre'] ?? ''); ?></td>
            <td class="num">$ <?php echo number_format($pre, 2); ?></td>
            <td class="num">$ <?php echo number_format($cant * $pre, 2); ?></td>
        </tr>
        <?php } ?>
    </tbody>
    <tfoot>
        <tr>
            <td colspan="3" style="text-align:right">SUBTOTAL</td>
            <td class="num">$ <?php echo number_format($subTotal, 2); ?></td>
        </tr>
        <tr>
            <td colspan="3" style="text-align:right">IVA 12%</td>
            <td class="num">$ <?php echo number_format($igv, 2); ?></td>
        </tr>
        <tr>
            <td colspan="3" style="text-align:right">Total con descuento</td>
            <td class="num">$ <?php echo number_format($totalSD, 2); ?></td>
        </tr>
        <tr>
            <td colspan="3" style="text-align:right; font-size:13px">TOTAL</td>
            <td class="num" style="font-size:13px">$ <?php echo number_format($totalCD, 2); ?></td>
        </tr>
    </tfoot>
</table>
<?php } ?>

<div style="margin-top:14px; padding:8px 12px; background:#f0f9ff; border-left: 4px solid #0ea5e9; font-size:12px;">
    <strong>Método de pago:</strong> <?php echo $_e($venta['metodo'] ?? ''); ?>
</div>

<?php if ($anulada) { ?>
<div style="margin-top:14px; padding:14px; background:#fee2e2; color:#991b1b; text-align:center; font-size:18px; font-weight:bold; letter-spacing:2px; border-radius:6px;">
    RETENCIÓN ANULADA
</div>
<?php }
$pdfContenido = ob_get_clean();
$empresa = $emp;
include __DIR__ . '/../templates/_pdf_layout.php';

<?php
// Reporte PDF: Credito (factura electronica) con detalle SRI.
// Estructura: $data['creditoE'] es array de items (productos), [0] tiene cabecera.
$cab = isset($data['creditoE'][0]) ? $data['creditoE'][0] : [];
$pdfTitulo    = 'CRÉDITO ELECTRÓNICO';
$pdfSubtitulo = 'N° ' . ($cab['id'] ?? '');
$pdfMeta = [
    ['label' => 'Fecha', 'value' => $cab['fecha'] ?? ''],
    ['label' => 'Hora',  'value' => $cab['hora']  ?? ''],
];

$_e = function ($s) { return htmlspecialchars((string)$s, ENT_QUOTES, 'UTF-8'); };
$ivaFactor = defined('CONCAT') ? CONCAT : '1.';
$iva = $data['empresa']['impuesto'] ?? 0;
$abonos = $data['abonos'] ?? [];
$abonado = 0;
foreach ($abonos as $a) { $abonado += (float)($a['abono'] ?? 0); }
$monto = (float)($cab['monto'] ?? 0);
$restante = $monto - $abonado;

ob_start();
?>
<h2 class="section">Datos del cliente</h2>
<table class="dt" style="margin-top:6px">
    <tr>
        <td style="width:25%; background:#f9fafb"><strong>Cédula/RUC:</strong></td>
        <td style="width:25%"><?php echo $_e($cab['num_identidad'] ?? ''); ?></td>
        <td style="width:25%; background:#f9fafb"><strong>Razón social:</strong></td>
        <td style="width:25%"><?php echo $_e($cab['nombre'] ?? ''); ?></td>
    </tr>
    <tr>
        <td style="background:#f9fafb"><strong>Teléfono:</strong></td>
        <td><?php echo $_e($cab['telefono'] ?? ''); ?></td>
        <td style="background:#f9fafb"><strong>Dirección:</strong></td>
        <td><?php echo $_e($cab['direccion'] ?? ''); ?></td>
    </tr>
</table>

<h2 class="section">Detalle de productos</h2>
<?php if (empty($data['creditoE'])) { ?>
    <div class="empty">Sin productos.</div>
<?php } else { ?>
<table class="dt">
    <thead>
        <tr>
            <th style="width:8%">Cant.</th>
            <th>Descripción</th>
            <th style="width:18%">P. Unit.</th>
            <th style="width:18%">Total</th>
        </tr>
    </thead>
    <tbody>
        <?php foreach ($data['creditoE'] as $producto) {
            // Mantener calculo original: precio_u * (1.IVA), cantidad * total
            $cant = (float)(is_array($producto['cantidad']) ? $producto['cantidad'][0] : $producto['cantidad']);
            $preU = (float)$producto['precio_u'] * (float)($ivaFactor . $iva);
        ?>
        <tr>
            <td class="ctr"><?php echo $cant; ?></td>
            <td><?php echo $_e($producto['item'] ?? ''); ?></td>
            <td class="num">$ <?php echo number_format($preU, 2); ?></td>
            <td class="num">$ <?php echo number_format($cant * $preU, 2); ?></td>
        </tr>
        <?php } ?>
    </tbody>
    <tfoot>
        <tr>
            <td colspan="3" style="text-align:right">MONTO TOTAL</td>
            <td class="num">$ <?php echo number_format($monto, 2); ?></td>
        </tr>
    </tfoot>
</table>
<?php } ?>

<h2 class="section">Detalle de abonos</h2>
<?php if (empty($abonos)) { ?>
    <div class="empty">Sin abonos registrados.</div>
<?php } else { ?>
<table class="dt">
    <thead>
        <tr>
            <th style="width:18%">Fecha</th>
            <th style="width:22%">N° Comprobante</th>
            <th style="width:20%">Tipo de pago</th>
            <th style="width:20%">Abono</th>
        </tr>
    </thead>
    <tbody>
        <?php foreach ($abonos as $a) { ?>
        <tr>
            <td class="ctr"><?php echo $_e($a['fecha'] ?? ''); ?></td>
            <td><?php echo $_e($a['codigo_pago'] ?? ''); ?></td>
            <td><?php echo $_e($a['tipo_pago'] ?? ''); ?></td>
            <td class="num">$ <?php echo number_format((float)($a['abono'] ?? 0), 2); ?></td>
        </tr>
        <?php } ?>
    </tbody>
    <tfoot>
        <tr>
            <td colspan="3" style="text-align:right">ABONADO</td>
            <td class="num">$ <?php echo number_format($abonado, 2); ?></td>
        </tr>
        <tr>
            <td colspan="3" style="text-align:right">RESTANTE</td>
            <td class="num">$ <?php echo number_format(max($restante, 0), 2); ?></td>
        </tr>
    </tfoot>
</table>
<?php } ?>

<div style="margin-top:18px; padding:10px; text-align:center; border-radius:6px;
    background:<?php echo (int)($cab['estado'] ?? 1) === 0 ? '#d1fae5' : '#fef3c7'; ?>;
    color:<?php echo (int)($cab['estado'] ?? 1) === 0 ? '#065f46' : '#92400e'; ?>;
    font-size:14px; font-weight:bold; letter-spacing:1px;">
    <?php echo (int)($cab['estado'] ?? 1) === 0 ? 'CRÉDITO FINALIZADO' : 'CRÉDITO PENDIENTE'; ?>
</div>
<?php
$pdfContenido = ob_get_clean();
$empresa = $data['empresa'] ?? [];
include __DIR__ . '/../templates/_pdf_layout.php';

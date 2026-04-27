<?php
// Reporte PDF: Credito (factura fisica) con detalle de productos y abonos.
$pdfTitulo    = 'CRÉDITO';
$pdfSubtitulo = 'N° ' . ($data['credito']['id'] ?? '');
$pdfMeta = [
    ['label' => 'Fecha', 'value' => $data['credito']['fecha'] ?? ''],
    ['label' => 'Hora',  'value' => $data['credito']['hora']  ?? ''],
];

$_e = function ($s) { return htmlspecialchars((string)$s, ENT_QUOTES, 'UTF-8'); };
$cli = $data['credito'] ?? [];
$productos = !empty($cli['productos']) ? json_decode($cli['productos'], true) : [];
$abonos = $data['abonos'] ?? [];

$abonado = 0;
foreach ($abonos as $a) { $abonado += (float)($a['abono'] ?? 0); }
$monto = (float)($cli['monto'] ?? 0);
$restante = $monto - $abonado;

ob_start();
?>
<h2 class="section">Datos del cliente</h2>
<table class="dt" style="margin-top:6px">
    <tr>
        <td style="width:25%; background:#f9fafb"><strong><?php echo $_e($cli['identidad'] ?? 'CI/RUC'); ?>:</strong></td>
        <td style="width:25%"><?php echo $_e($cli['num_identidad'] ?? ''); ?></td>
        <td style="width:25%; background:#f9fafb"><strong>Razón social:</strong></td>
        <td style="width:25%"><?php echo $_e($cli['nombre'] ?? ''); ?></td>
    </tr>
    <tr>
        <td style="background:#f9fafb"><strong>Teléfono:</strong></td>
        <td><?php echo $_e($cli['telefono'] ?? ''); ?></td>
        <td style="background:#f9fafb"><strong>Dirección:</strong></td>
        <td><?php echo $_e(trim(html_entity_decode(strip_tags((string)($cli['direccion'] ?? '')), ENT_QUOTES | ENT_HTML5, 'UTF-8'))); ?></td>
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
            <th style="width:18%">P. Unit.</th>
            <th style="width:18%">Total</th>
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
            <td class="num"><?php echo $restante > 0 ? '$ ' . number_format($restante, 2) : '$ 0.00'; ?></td>
        </tr>
    </tfoot>
</table>
<?php } ?>

<div style="margin-top:18px; padding:10px; text-align:center; border-radius:6px;
    background:<?php echo (int)($cli['estado'] ?? 1) === 0 ? '#d1fae5' : '#fef3c7'; ?>;
    color:<?php echo (int)($cli['estado'] ?? 1) === 0 ? '#065f46' : '#92400e'; ?>;
    font-size:14px; font-weight:bold; letter-spacing:1px;">
    <?php echo (int)($cli['estado'] ?? 1) === 0 ? 'CRÉDITO FINALIZADO' : 'CRÉDITO PENDIENTE'; ?>
</div>
<?php
$pdfContenido = ob_get_clean();
$empresa = $data['empresa'] ?? [];
include __DIR__ . '/../templates/_pdf_layout.php';

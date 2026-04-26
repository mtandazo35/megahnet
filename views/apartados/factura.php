<?php
// Reporte PDF: Apartado con detalle de productos.
$ap   = $data['apartado'] ?? [];
$emp  = $data['empresa']  ?? [];

$pdfTitulo    = 'APARTADO';
$pdfSubtitulo = 'N° ' . ($ap['id'] ?? '');
$pdfMeta = [
    ['label' => 'Fecha', 'value' => $ap['fecha_apartado'] ?? ''],
];

$_e = function ($s) { return htmlspecialchars((string)$s, ENT_QUOTES, 'UTF-8'); };
$productos = !empty($ap['productos']) ? json_decode($ap['productos'], true) : [];
if (!is_array($productos)) $productos = [];
$total = (float)($ap['total'] ?? 0);
$entregado = (int)($ap['estado'] ?? 1) === 0;

ob_start();
?>
<h2 class="section">Datos del cliente</h2>
<table class="dt" style="margin-top:6px">
    <tr>
        <td style="width:25%; background:#f9fafb"><strong><?php echo $_e($ap['identidad'] ?? 'CI/RUC'); ?>:</strong></td>
        <td style="width:25%"><?php echo $_e($ap['num_identidad'] ?? ''); ?></td>
        <td style="width:25%; background:#f9fafb"><strong>Razón social:</strong></td>
        <td style="width:25%"><?php echo $_e($ap['nombre'] ?? ''); ?></td>
    </tr>
    <tr>
        <td style="background:#f9fafb"><strong>Teléfono:</strong></td>
        <td><?php echo $_e($ap['telefono'] ?? ''); ?></td>
        <td style="background:#f9fafb"><strong>Dirección:</strong></td>
        <td><?php echo $_e($ap['direccion'] ?? ''); ?></td>
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
            <td colspan="3" style="text-align:right; font-size:13px">TOTAL</td>
            <td class="num" style="font-size:13px">$ <?php echo number_format($total, 2); ?></td>
        </tr>
    </tfoot>
</table>
<?php } ?>

<div style="margin-top:18px; padding:10px; text-align:center; border-radius:6px;
    background:<?php echo $entregado ? '#d1fae5' : '#fef3c7'; ?>;
    color:<?php echo $entregado ? '#065f46' : '#92400e'; ?>;
    font-size:14px; font-weight:bold; letter-spacing:1px;">
    <?php echo $entregado ? 'PRODUCTOS ENTREGADOS' : 'PRODUCTOS POR RECOGER'; ?>
</div>
<?php
$pdfContenido = ob_get_clean();
$empresa = $emp;
include __DIR__ . '/../templates/_pdf_layout.php';

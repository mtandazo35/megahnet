<?php
// Reporte PDF: Inventario de movimientos - usa el layout comun.
$pdfTitulo    = 'INVENTARIO';
$pdfSubtitulo = 'Movimientos';
$pdfMeta = [
    ['label' => 'Usuario', 'value' => $data['usuario'] ?? ''],
];

$_e = function ($s) { return htmlspecialchars((string)$s, ENT_QUOTES, 'UTF-8'); };
$inventario = $data['inventario'] ?? [];

ob_start();
?>
<h2 class="section">Detalle de movimientos</h2>
<?php if (empty($inventario)) { ?>
    <div class="empty">No hay movimientos registrados.</div>
<?php } else { ?>
<table class="dt">
    <thead>
        <tr>
            <th style="width:6%">#</th>
            <th>Producto</th>
            <th style="width:18%">Movimiento</th>
            <th style="width:18%">Fecha y Hora</th>
            <th style="width:13%">Cantidad</th>
        </tr>
    </thead>
    <tbody>
        <?php $i=1; foreach ($inventario as $inv) { ?>
        <tr>
            <td class="ctr"><?php echo $i++; ?></td>
            <td><?php echo $_e($inv['descripcion'] ?? ''); ?></td>
            <td class="ctr"><?php echo $_e($inv['movimiento'] ?? ''); ?></td>
            <td class="ctr"><?php echo $_e($inv['fecha'] ?? ''); ?></td>
            <td class="ctr col-saldo"><?php echo $_e($inv['cantidad'] ?? 0); ?></td>
        </tr>
        <?php } ?>
    </tbody>
</table>
<?php }
$pdfContenido = ob_get_clean();
$empresa = $data['empresa'] ?? [];
include __DIR__ . '/../templates/_pdf_layout.php';

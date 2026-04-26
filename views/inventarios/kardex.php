<?php
// Reporte PDF: Kardex (entradas y salidas) - usa el layout comun.
$pdfTitulo    = 'KARDEX';
$pdfSubtitulo = 'Entradas y Salidas';
$pdfMeta = [
    ['label' => 'Usuario', 'value' => $data['usuario'] ?? ''],
];

$_e = function ($s) { return htmlspecialchars((string)$s, ENT_QUOTES, 'UTF-8'); };
$kardex = $data['kardex'] ?? [];

ob_start();
?>
<h2 class="section">Detalle de movimientos</h2>
<?php if (empty($kardex)) { ?>
    <div class="empty">No hay movimientos registrados.</div>
<?php } else { ?>
<table class="dt">
    <thead>
        <tr>
            <th style="width:6%">#</th>
            <th>Producto</th>
            <th style="width:11%">Entradas</th>
            <th style="width:11%">Salidas</th>
            <th style="width:18%">Fecha y Hora</th>
            <th style="width:13%">Stock Actual</th>
        </tr>
    </thead>
    <tbody>
        <?php $i=1; foreach ($kardex as $k) { ?>
        <tr>
            <td class="ctr"><?php echo $i++; ?></td>
            <td><?php echo $_e($k['descripcion'] ?? ''); ?></td>
            <td class="ctr"><?php echo (int)($k['entrada'] ?? 0); ?></td>
            <td class="ctr"><?php echo (int)($k['salida'] ?? 0); ?></td>
            <td class="ctr"><?php echo $_e($k['fecha'] ?? ''); ?></td>
            <td class="ctr col-saldo"><?php echo (int)($k['stock_actual'] ?? 0); ?></td>
        </tr>
        <?php } ?>
    </tbody>
</table>
<?php }
$pdfContenido = ob_get_clean();
$empresa = $data['empresa'] ?? [];
include __DIR__ . '/../templates/_pdf_layout.php';

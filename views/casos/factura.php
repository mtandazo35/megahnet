<?php
// Reporte PDF: Caso tecnico con detalle del problema y grupo asignado.
$caso = $data['caso']    ?? [];
$emp  = $data['empresa'] ?? [];
$grupo = $data['grupoTrabajo'] ?? [];

$pdfTitulo    = 'CASO TÉCNICO';
$pdfSubtitulo = 'N° ' . ($caso['id'] ?? '');
$pdfMeta = [
    ['label' => 'Fecha', 'value' => $caso['fecha'] ?? ''],
    ['label' => 'Hora',  'value' => $caso['hora']  ?? ''],
];

$_e = function ($s) { return htmlspecialchars((string)$s, ENT_QUOTES, 'UTF-8'); };

ob_start();
?>
<h2 class="section">Datos del cliente</h2>
<table class="dt" style="margin-top:6px">
    <tr>
        <td style="width:18%; background:#f9fafb"><strong><?php echo $_e($caso['identidad'] ?? 'CI/RUC'); ?>:</strong></td>
        <td style="width:32%"><?php echo $_e($caso['num_identidad'] ?? ''); ?></td>
        <td style="width:18%; background:#f9fafb"><strong>Razón social:</strong></td>
        <td style="width:32%"><?php echo $_e($caso['nombre'] ?? ''); ?></td>
    </tr>
    <tr>
        <td style="background:#f9fafb"><strong>Teléfono:</strong></td>
        <td><?php echo $_e($caso['telefono'] ?? ''); ?></td>
        <td style="background:#f9fafb"><strong>Coordenada:</strong></td>
        <td><?php echo $_e($caso['coordenada'] ?? ''); ?></td>
    </tr>
    <tr>
        <td style="background:#f9fafb"><strong>Dirección:</strong></td>
        <td colspan="3"><?php echo $_e($caso['direccion'] ?? ''); ?></td>
    </tr>
</table>

<h2 class="section">Detalle del caso</h2>
<table class="dt">
    <thead>
        <tr>
            <th>Problema Reportado</th>
            <th>Trabajo Realizado</th>
            <th>Observación</th>
        </tr>
    </thead>
    <tbody>
        <tr>
            <td style="text-align:left"><?php echo $_e($caso['problema_reportado'] ?? ''); ?></td>
            <td style="text-align:left"><?php echo $_e($caso['trabajo_realizado']  ?? '—'); ?></td>
            <td style="text-align:left"><?php echo $_e($caso['observacion']        ?? '—'); ?></td>
        </tr>
    </tbody>
</table>

<h2 class="section">Grupo de trabajo asignado</h2>
<?php if (empty($grupo)) { ?>
    <div class="empty">Sin asignación.</div>
<?php } else { ?>
<table class="dt">
    <thead>
        <tr>
            <th style="width:50%">Empleado</th>
            <th style="width:50%">Zona</th>
        </tr>
    </thead>
    <tbody>
        <?php foreach ($grupo as $row) { ?>
        <tr>
            <td style="text-align:left"><?php echo $_e($row['empleado'] ?? ''); ?></td>
            <td style="text-align:left"><?php echo $_e($row['descripcion'] ?? ''); ?></td>
        </tr>
        <?php } ?>
    </tbody>
</table>
<?php }
$pdfContenido = ob_get_clean();
$empresa = $emp;
include __DIR__ . '/../templates/_pdf_layout.php';

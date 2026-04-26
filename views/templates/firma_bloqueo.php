<?php
// Pantalla de bloqueo por firma electronica.
// Reusable desde: ventas/index, notaCredito/index, retenciones/index, etc.
// Espera en scope: $data['empresa']  (puede no traer 'firmafinal').
// Opcional: $nombreModulo (texto para el mensaje, ej "facturas", "notas de credito").

$nombreModulo = isset($nombreModulo) && $nombreModulo !== '' ? $nombreModulo : 'comprobantes electronicos';

$firmaPath   = __DIR__ . '/../../facturaelectronica/public/archivos/token/FIRMA.p12';
$firmaExiste = is_file($firmaPath) && filesize($firmaPath) > 0;

$firmaFinalRaw = isset($data['empresa']['firmafinal']) ? trim((string)$data['empresa']['firmafinal']) : '';
$firmaFinalTs  = ($firmaFinalRaw !== '') ? strtotime($firmaFinalRaw) : false;

if (!$firmaExiste) {
    $titulo  = 'Configura tu firma electrónica';
    $detalle = 'Aún no se ha cargado el archivo .p12. Sube tu firma para emitir ' . htmlspecialchars($nombreModulo) . ' del SRI.';
    $accion  = ['url' => BASE_URL . 'admin/datos', 'texto' => 'Subir firma .p12', 'icono' => 'bx bx-upload'];
} elseif ($firmaFinalRaw === '' || $firmaFinalTs === false) {
    $titulo  = 'Falta configurar la vigencia de la firma';
    $detalle = 'La firma .p12 está cargada pero su fecha de vencimiento no está configurada. Ingresa la fecha en el panel de configuración.';
    $accion  = ['url' => BASE_URL . 'admin/datos', 'texto' => 'Configurar firma', 'icono' => 'bx bx-cog'];
} else {
    $fechaTxt = date('d/m/Y', $firmaFinalTs);
    $titulo   = 'Firma electrónica caducada';
    $detalle  = 'Tu firma electrónica venció el <strong>' . htmlspecialchars($fechaTxt) . '</strong>. Renuévala para continuar emitiendo ' . htmlspecialchars($nombreModulo) . '.';
    $accion   = ['url' => BASE_URL . 'admin/datos', 'texto' => 'Renovar firma', 'icono' => 'bx bx-refresh'];
}
?>
<div class="d-flex align-items-center justify-content-center py-4">
    <div class="card border-0 shadow-sm" style="max-width: 720px; width: 100%;">
        <div class="card-body p-4 p-md-5 text-center">
            <div class="mb-3">
                <span class="bg-danger-subtle text-danger rounded-circle d-inline-flex align-items-center justify-content-center" style="width:96px; height:96px;">
                    <i class="bx bx-shield-x" style="font-size:56px;"></i>
                </span>
            </div>
            <h3 class="fw-semibold mb-2"><?php echo $titulo; ?></h3>
            <p class="text-muted mb-4"><?php echo $detalle; ?></p>
            <div class="d-flex flex-wrap gap-2 justify-content-center">
                <a href="<?php echo $accion['url']; ?>" class="btn btn-primary px-4">
                    <i class="<?php echo $accion['icono']; ?> me-1"></i><?php echo $accion['texto']; ?>
                </a>
                <a href="<?php echo BASE_URL; ?>admin" class="btn btn-light px-4">
                    <i class="bx bx-arrow-back me-1"></i>Regresar al panel
                </a>
            </div>
            <?php if (defined('CONTACTO') && CONTACTO !== '0000000000' && CONTACTO !== '') { ?>
                <p class="text-muted small mt-4 mb-0">¿Necesitas ayuda? Soporte: <strong><?php echo htmlspecialchars(CONTACTO); ?></strong></p>
            <?php } ?>
        </div>
    </div>
</div>

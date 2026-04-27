<?php
/* ============================================================================
 * Background worker: genera PDF + envia email de una orden de venta recien creada.
 * Invocado por OrdenVenta::registrarOrdenVenta() via exec() para no bloquear
 * la respuesta HTTP.
 *
 * Uso: php orden_post_create.php <idOrden>
 * ============================================================================ */

if (PHP_SAPI !== 'cli') { http_response_code(403); exit('CLI only'); }
if ($argc < 2) { exit("usage: php orden_post_create.php <idOrden>\n"); }

$idOrden = (int)$argv[1];
if ($idOrden <= 0) { exit("invalid id\n"); }

chdir(__DIR__ . '/..');
require_once 'config/Config.php';
require_once 'config/Helpers.php';
require_once 'config/app/Autoload.php';

// Cargar el controller para reusar ordenVentaPDF
require_once 'controllers/OrdenVenta.php';
$_SESSION['id_usuario'] = 1; // para que el constructor del controller no redirija
session_id('cli_' . $idOrden);

try {
    $ctrl = new OrdenVenta();
    $ctrl->ordenVentaPDF('facturas', $idOrden);
} catch (\Throwable $e) {
    error_log('[bg orden ' . $idOrden . '] PDF falla: ' . $e->getMessage());
}

try {
    $modelOrd = new OrdenVentaModel();
    $modelAdmin = new AdminModel();
    $empresa = $modelAdmin->getEmpresa();
    $row = $modelOrd->getOrdenVenta($idOrden);
    if ($row && function_exists('sendEmailOrden')) {
        $dataInfo = [
            'ruc'             => $row['num_identidad'] ?? '',
            'email'           => $row['correo'] ?? '',
            'fecha'           => $row['fecha'] ?? '',
            'totalfactura'    => $row['total'] ?? '',
            'cliente'         => $row['nombre'] ?? '',
            'empresa'         => $empresa['nombre'] ?? '',
            'factura'         => $idOrden,
            'enviroment'      => defined('ENVIROMENT') ? ENVIROMENT : '',
            'emailremitente'  => $empresa['correo'] ?? '',
            'establecimiento' => $empresa['establecimiento'] ?? '',
            'puntoemi'        => $empresa['puntoemi'] ?? '',
            'tipo'            => 'orden',
            'asunto'          => 'Adjuntamos Comprobante'
        ];
        sendEmailOrden($dataInfo, 'email_facturaelectronica');
    }
} catch (\Throwable $e) {
    error_log('[bg orden ' . $idOrden . '] Email falla: ' . $e->getMessage());
}


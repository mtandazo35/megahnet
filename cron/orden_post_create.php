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


// === Envio opcional por WhatsApp (parametro $argv[2]=1) ===
$enviarWa = (isset($argv[2]) && $argv[2] === '1');
if ($enviarWa) {
    try {
        // Telefono del cliente desde la BD
        $pdo = new PDO('mysql:host=' . HOSTT . ';dbname=' . DBNAME . ';charset=utf8mb4', USER, defined('PASSWORD') ? PASSWORD : '',
            [PDO::ATTR_ERRMODE => PDO::ERRMODE_SILENT]);
        $st = $pdo->prepare("SELECT cl.telefono FROM orden_venta ov INNER JOIN clientes cl ON cl.id = ov.id_cliente WHERE ov.id = ? LIMIT 1");
        $st->execute([$idOrden]);
        $r = $st->fetch(PDO::FETCH_ASSOC);
        $tel = preg_replace('/[^0-9]/', '', (string)($r['telefono'] ?? ''));

        if (!empty($tel)) {
            if (strlen($tel) === 10 && $tel[0] === '0') $tel = '593' . substr($tel, 1);
            elseif (strlen($tel) === 9) $tel = '593' . $tel;

            // Esperar a que el PDF este listo
            $pdfPath = ROOT_PATH . '/facturaelectronica/public/archivos/facturables/Facturable_' . $idOrden . '.pdf';
            $tries = 0; while (!file_exists($pdfPath) && $tries < 10) { sleep(1); $tries++; }

            // URL publica del PDF
            $base = defined('BASE_URL') ? rtrim(BASE_URL, '/') : '';
            $pdfUrl = $base . '/facturaelectronica/public/archivos/facturables/Facturable_' . $idOrden . '.pdf';

            // Resolver URL + sesion del WhatsApp API
            $waBase = '';
            if (function_exists('servicioConfig')) {
                $svc = servicioConfig('whatsapp_api');
                $waBase = $svc['base_url'] ?? '';
            }
            if (empty($waBase)) {
                $f = ROOT_PATH . '/storage/alertas-config.json';
                if (file_exists($f)) {
                    $j = @json_decode(@file_get_contents($f), true);
                    $waBase = $j['wa_api']['base_url'] ?? '';
                }
            }
            if (empty($waBase)) $waBase = 'http://127.0.0.1:3005';
            $waBase = rtrim($waBase, '/');

            $sessionId = '';
            $f = ROOT_PATH . '/storage/alertas-config.json';
            if (file_exists($f)) {
                $j = @json_decode(@file_get_contents($f), true);
                $sessionId = $j['wa_api']['session_id'] ?? '';
            }
            if (!empty($sessionId)) {
                $payload = [
                    'sessionId' => $sessionId,
                    'number'    => $tel,
                    'url'       => $pdfUrl,
                    'fileName'  => 'OrdenVenta_' . $idOrden . '.pdf',
                    'caption'   => 'Adjunto orden de venta #' . $idOrden,
                ];
                $ch = curl_init($waBase . '/api/whatsapp/send-media/url');
                curl_setopt_array($ch, [
                    CURLOPT_RETURNTRANSFER => true,
                    CURLOPT_POST => true,
                    CURLOPT_POSTFIELDS => json_encode($payload),
                    CURLOPT_HTTPHEADER => ['Content-Type: application/json'],
                    CURLOPT_TIMEOUT => 30,
                    CURLOPT_SSL_VERIFYPEER => false,
                ]);
                $resp = curl_exec($ch);
                $code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
                curl_close($ch);
                error_log('[bg orden ' . $idOrden . '] WA send HTTP ' . $code);
            } else {
                error_log('[bg orden ' . $idOrden . '] WA: sin session_id, skip');
            }
        }
    } catch (\Throwable $e) {
        error_log('[bg orden ' . $idOrden . '] WA falla: ' . $e->getMessage());
    }
}

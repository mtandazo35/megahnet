<?php
/**
 * CRON: verificacion automatica de conexion a MikroTiks
 *
 * Recorre todos los MikroTiks activos y prueba conexion a cada uno.
 * Actualiza estado_conexion (online/offline), ultima_verificacion y ultimo_error
 * en la tabla mikrotik. Pensado para ejecutarse cada hora desde crontab.
 *
 * Uso:
 *   # Cada hora en punto:
 *   0 * * * * php /var/www/html/megahnet/cron/cron_verificar_mikrotiks.php
 */

if (php_sapi_name() !== 'cli') {
    http_response_code(403);
    echo "Solo CLI\n";
    exit(1);
}

require_once dirname(__DIR__) . '/config/Config.php';
require_once dirname(__DIR__) . '/config/Helpers.php';
require_once dirname(__DIR__) . '/config/app/Autoload.php';
require_once dirname(__DIR__) . '/libraries/mikrotik/routeros_api.class.php';

$model = new MikrotiksModel();
$lista = $model->getMikrotiksParaVerificar();

$resumen = ['total' => count($lista), 'online' => 0, 'offline' => 0];
$tInicio = microtime(true);

foreach ($lista as $row) {
    $ip       = $row['ip'];
    $usuario  = $row['usuario'];
    $puerto   = (int)($row['puerto'] ?: 8728);

    // Decriptar clave
    $bin   = base64_decode($row['clave']);
    $ivLen = openssl_cipher_iv_length(METODOASIC);
    $iv    = substr($bin, 0, $ivLen);
    $enc   = substr($bin, $ivLen);
    $clave = (string)openssl_decrypt($enc, METODOASIC, KEY, 0, $iv);

    $API = new RouterosAPI();
    $API->port    = $puerto;
    $API->timeout = 4;

    $t0 = microtime(true);
    $ok = @$API->connect($ip, $usuario, $clave);
    $latency = (int)round((microtime(true) - $t0) * 1000);

    $estado  = ($ok && $API->connected) ? 'online' : 'offline';
    $error   = ($estado === 'offline') ? "Timeout/credenciales (puerto $puerto, ${latency}ms)" : null;

    $model->actualizarEstadoConexion((int)$row['id'], $estado, $error);

    if ($estado === 'online') {
        $resumen['online']++;
        try { $API->disconnect(); } catch (\Throwable $e) {}
    } else {
        $resumen['offline']++;
    }

    echo sprintf("[%s] %s (%s) -> %s (%dms)\n",
        date('H:i:s'), $row['nombre'], $ip, strtoupper($estado), $latency);
}

$dur = round((microtime(true) - $tInicio) * 1000);
echo sprintf("\n[%s] === RESUMEN: %d total, %d online, %d offline (%dms) ===\n",
    date('Y-m-d H:i:s'), $resumen['total'], $resumen['online'], $resumen['offline'], $dur);

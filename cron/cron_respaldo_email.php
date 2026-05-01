<?php
// ===============================
// CRON: respaldo automatico de BD por correo
// Programado para correr 2x al dia (8:00 y 22:00 hora local).
// ===============================

date_default_timezone_set('America/Guayaquil');

ini_set('display_errors', 0);
error_reporting(E_ALL);

define('BASE_PATH', dirname(__DIR__));

require_once BASE_PATH . '/config/Config.php';
require_once BASE_PATH . '/config/Helpers.php';

$logDir  = BASE_PATH . '/storage';
if (!is_dir($logDir)) { @mkdir($logDir, 0755, true); }
$logFile = $logDir . '/cron_respaldo_email.log';

$logear = function ($nivel, $msg) use ($logFile) {
    $linea = '[' . date('Y-m-d H:i:s') . '] [' . $nivel . '] ' . $msg . PHP_EOL;
    @file_put_contents($logFile, $linea, FILE_APPEND | LOCK_EX);
};

// Destinatarios (puede ser uno o varios separados por coma)
$destinatarios = ['megahred@gmail.com'];

try {
    $logear('INFO', 'Iniciando respaldo automatico');
    $info = respaldoBD_generar();
    $logear('INFO', 'Respaldo generado: ' . $info['nombre'] . ' (' . $info['tamanio'] . ')');

    $asunto  = '[' . (defined('TITLE') ? TITLE : DBNAME) . '] Respaldo automatico ' . date('Y-m-d H:i');
    $mensaje = 'Respaldo generado automaticamente por cron en ' . date('Y-m-d H:i:s') . '.';
    $res = respaldoBD_enviar_email($info['nombre'], $destinatarios, $asunto, $mensaje);

    if (!empty($res['ok'])) {
        $logear('INFO', 'Correo enviado a ' . $res['destinos'] . ' destinatario(s)');
    } else {
        $logear('ERROR', 'Envio retorno fallo: ' . json_encode($res));
    }
} catch (Throwable $e) {
    $logear('ERROR', 'Excepcion: ' . $e->getMessage());
    fwrite(STDERR, 'cron_respaldo_email FALLO: ' . $e->getMessage() . PHP_EOL);
    exit(1);
}

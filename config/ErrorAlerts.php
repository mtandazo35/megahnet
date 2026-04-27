<?php
// =============================================================================
// ErrorAlerts — captura global de fallos PHP y los manda a la bitacora unica
// (storage/alertas.jsonl), notifica al admin (email + WhatsApp via Helpers).
// Tambien expone registrarFalla() para llamar manualmente desde catch blocks.
// =============================================================================

if (!function_exists('alertaLogJsonl')) {
    function alertaLogJsonl($tipo, $asunto, $cuerpo, $extra = []) {
        $logDir = __DIR__ . '/../storage';
        if (!is_dir($logDir)) { @mkdir($logDir, 0755, true); }
        $logFile = $logDir . '/alertas.jsonl';
        $rec = array_merge([
            'ts'     => date('Y-m-d H:i:s'),
            'tipo'   => $tipo,
            'asunto' => $asunto,
            'cuerpo' => $cuerpo,
            'status' => ['ok' => false, 'log_only' => true],
        ], $extra);
        @file_put_contents($logFile, json_encode($rec, JSON_UNESCAPED_UNICODE) . PHP_EOL, FILE_APPEND | LOCK_EX);
    }
}

if (!function_exists('alertaDebeNotificar')) {
    // Throttle: por firma (tipo+asunto), max 1 alerta cada 300s. Devuelve true si toca notificar.
    function alertaDebeNotificar($tipo, $asunto) {
        $f = __DIR__ . '/../storage/_alert_dedup.json';
        $sig = md5($tipo . '|' . $asunto);
        $now = time();
        $data = file_exists($f) ? @json_decode(@file_get_contents($f), true) : [];
        if (!is_array($data)) $data = [];
        // limpiar entradas viejas (> 1h)
        foreach ($data as $k => $ts) { if ($now - (int)$ts > 3600) unset($data[$k]); }
        if (isset($data[$sig]) && ($now - (int)$data[$sig]) < 300) {
            // throttle: registra el ts pero no notifica
            return false;
        }
        $data[$sig] = $now;
        @file_put_contents($f, json_encode($data), LOCK_EX);
        return true;
    }
}

if (!function_exists('registrarFalla')) {
    /**
     * Registra una falla en la bitacora y opcionalmente notifica al admin.
     * Llamar desde catch blocks: registrarFalla('SRI_FALLO', 'Texto corto', 'Detalle largo o exception->getMessage()', ['ctx' => ...]);
     */
    function registrarFalla($tipo, $asunto, $cuerpoTxt = '', $contexto = []) {
        $cuerpo = '<p><b>' . htmlspecialchars($asunto) . '</b></p>';
        if ($cuerpoTxt !== '') $cuerpo .= '<pre style="background:#f8f9fa;padding:8px;border-radius:4px;font-size:.85em;white-space:pre-wrap;">' . htmlspecialchars($cuerpoTxt) . '</pre>';
        if (!empty($contexto)) $cuerpo .= '<p><small>Contexto: ' . htmlspecialchars(json_encode($contexto, JSON_UNESCAPED_UNICODE)) . '</small></p>';

        // Siempre log al jsonl
        alertaLogJsonl($tipo, $asunto, $cuerpo, ['contexto' => $contexto]);

        // Notificar via canal email/WA solo si pasa el throttle y la funcion existe
        if (alertaDebeNotificar($tipo, $asunto) && function_exists('enviarAlertaAdmin')) {
            try { @enviarAlertaAdmin($tipo, $asunto, $cuerpo); } catch (\Throwable $e) { /* no-op */ }
        }
    }
}

if (!function_exists('alertaContextoRequest')) {
    function alertaContextoRequest() {
        return [
            'url'    => ($_SERVER['REQUEST_METHOD'] ?? 'GET') . ' ' . ($_SERVER['REQUEST_URI'] ?? ''),
            'ip'     => $_SERVER['REMOTE_ADDR'] ?? '',
            'user'   => $_SESSION['nombre_usuario'] ?? ($_SESSION['id_usuario'] ?? 'anon'),
            'ua'     => substr((string)($_SERVER['HTTP_USER_AGENT'] ?? ''), 0, 120),
        ];
    }
}

// ============================================================================
// Handlers globales
// ============================================================================
set_exception_handler(function ($e) {
    registrarFalla(
        'EXCEPCION_PHP',
        'Excepcion no capturada: ' . get_class($e),
        $e->getMessage() . "\n\n" . $e->getFile() . ':' . $e->getLine() . "\n\n" . $e->getTraceAsString(),
        alertaContextoRequest()
    );
    // Si estamos respondiendo JSON al cliente, devolver algo util
    if (!headers_sent()) {
        @header('Content-Type: application/json; charset=utf-8', true, 500);
        echo json_encode(['type' => 'error', 'msg' => 'Error interno del servidor. El admin fue notificado.']);
    }
});

set_error_handler(function ($errno, $errstr, $errfile, $errline) {
    // Solo notificamos errores severos. Warnings/notices se ignoran (PHP los pone en log de Apache).
    $severe = E_ERROR | E_PARSE | E_CORE_ERROR | E_COMPILE_ERROR | E_USER_ERROR | E_RECOVERABLE_ERROR;
    if (!($errno & $severe)) return false; // dejar que PHP lo maneje normal
    registrarFalla(
        'ERROR_PHP',
        'Error PHP severo',
        '[' . $errno . '] ' . $errstr . "\n" . $errfile . ':' . $errline,
        alertaContextoRequest()
    );
    return false; // no suprimir el error nativo
});

register_shutdown_function(function () {
    $err = error_get_last();
    if (!$err) return;
    $fatales = [E_ERROR, E_PARSE, E_CORE_ERROR, E_COMPILE_ERROR, E_USER_ERROR, E_RECOVERABLE_ERROR];
    if (!in_array($err['type'], $fatales, true)) return;
    registrarFalla(
        'FATAL_PHP',
        'Fatal: ' . substr($err['message'], 0, 80),
        $err['message'] . "\n" . ($err['file'] ?? '') . ':' . ($err['line'] ?? ''),
        alertaContextoRequest()
    );
});

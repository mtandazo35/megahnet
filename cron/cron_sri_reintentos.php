<?php
/**
 * CRON 3 – REINTENTOS SRI (máx 2)
 * Compatible PHP 7.4
 */

date_default_timezone_set('America/Guayaquil');

// 🔒 Lock
$lockFile = __DIR__ . '/cron_sri_reintentos.lock';
if (file_exists($lockFile)) {
    echo "⛔ CRON REINTENTOS YA EN EJECUCIÓN\n";
    exit;
}
file_put_contents($lockFile, time());

try {

    define('ROOT_PATH', realpath(__DIR__ . '/..'));

    require_once ROOT_PATH . '/config/Config.php';
    require_once ROOT_PATH . '/facturaelectronica/lib2/config.php';
    require_once ROOT_PATH . '/facturaelectronica/lib2/functions.php';
    require_once ROOT_PATH . '/facturaelectronica/src/validacionComprobante.php';
    require_once ROOT_PATH . '/facturaelectronica/src/autorizacionComprobante.php';

    $db = db_open();
    echo "🔁 CRON REINTENTOS SRI INICIADO\n";

    /**
     * FACTURAS RECHAZADAS
     * - estado_proceso = 2
     * - sri_enviado = 1
     * - intentos_sri < 2
     */
    $sql = "
        SELECT id, orden_no, claveacceso, intentos_sri
        FROM datos_cabecera_electronica
        WHERE estado_proceso = 2
        AND sri_enviado = 1
        AND intentos_sri < 25
        LIMIT 25
    ";
    $facturas = $db->query($sql)->fetchAll(PDO::FETCH_ASSOC);

    echo "📄 Facturas a reintentar: " . count($facturas) . "\n";

    $validar = new validacionComprobante();
    $autorizar = new autorizacionComprobante();

    foreach ($facturas as $factura) {

        $id = $factura['id'];
        $orden = $factura['orden_no'];
        $claveAcceso = $factura['claveacceso'];
        $intentos = $factura['intentos_sri'] + 1;

        echo "➡️ Reintentando factura {$orden} (Intento {$intentos})\n";

        /**
         * 1️⃣ VALIDACIÓN
         */
        $validacion = $validar->validar_comprobante($claveAcceso, FACTURA);

        if (!isset($validacion['estado']) || $validacion['estado'] !== 'RECIBIDA') {

            $db->prepare("
                UPDATE datos_cabecera_electronica
                SET intentos_sri = ?, mensaje_sri = ?
                WHERE id = ?
            ")->execute([
                $intentos,
                json_encode($validacion, JSON_UNESCAPED_UNICODE),
                $id
            ]);

            echo "❌ Sigue DEVUELTA EN VALIDACIÓN\n";
            continue;
        }

        /**
         * 2️⃣ AUTORIZACIÓN
         */
        sleep(2);
        $autorizacion = $autorizar->autorizacion_comprobante($claveAcceso, FACTURA);

        if (
            !isset($autorizacion['autorizaciones']['autorizacion']['estado']) ||
            $autorizacion['autorizaciones']['autorizacion']['estado'] !== 'AUTORIZADO'
        ) {

            $db->prepare("
                UPDATE datos_cabecera_electronica
                SET intentos_sri = ?, mensaje_sri = ?
                WHERE id = ?
            ")->execute([
                $intentos,
                json_encode($autorizacion, JSON_UNESCAPED_UNICODE),
                $id
            ]);

            echo "❌ NO AUTORIZADA\n";
            continue;
        }

        /**
         * 3️⃣ AUTORIZADA ✔
         */
        echo "✔ AUTORIZADA EN REINTENTO\n";

        $db->prepare("
            UPDATE datos_cabecera_electronica
            SET estado_proceso = 1,
                intentos_sri = ?,
                mensaje_sri = NULL
            WHERE id = ?
        ")->execute([$intentos, $id]);
    }

    echo "🏁 CRON REINTENTOS FINALIZADO\n";

} catch (Exception $e) {

    echo "🔥 ERROR CRON REINTENTOS: " . $e->getMessage() . "\n";

} finally {

    unlink($lockFile);
}

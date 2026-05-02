<?php

/**
 * CRON 2 – FLUJO COMPLETO SRI
 * Compatible PHP 7.4
 */

date_default_timezone_set('America/Guayaquil');

// 🔒 Lock
$lockFile = __DIR__ . '/cron_sri.lock';
if (file_exists($lockFile)) {
    echo "⛔ CRON YA EN EJECUCIÓN\n";
    exit;
}
file_put_contents($lockFile, time());

try {

    define('ROOT_PATH', realpath(__DIR__ . '/..'));



    require_once ROOT_PATH . '/config/Config.php';
    require_once ROOT_PATH . '/facturaelectronica/lib2/config.php';
    require_once ROOT_PATH . '/facturaelectronica/lib2/functions.php';
    require_once ROOT_PATH . '/facturaelectronica/envio_xml.php';
    require_once ROOT_PATH . '/facturaelectronica/src/validacionComprobante.php';
    require_once ROOT_PATH . '/facturaelectronica/src/autorizacionComprobante.php';
    require_once ROOT_PATH . '/config/cron_services.php';

    $db = db_open();
    echo "🚀 CRON SRI INICIADO\n";

    /**
     * SOLO FACTURAS NUEVAS
     * - estado_proceso = 0 (solo guardada)
     * - sri_enviado = 0 (no enviada)
     */
    $sql = "
        SELECT id, orden_no
        FROM datos_cabecera_electronica
        WHERE estado_proceso = 0
        AND sri_enviado = 0
        AND fecha >= DATE_FORMAT(CURDATE(), '%Y-%m-01')
        AND fecha <  DATE_FORMAT(DATE_ADD(CURDATE(), INTERVAL 1 MONTH), '%Y-%m-01')
        LIMIT 25
    ";
    $facturas = $db->query($sql)->fetchAll(PDO::FETCH_ASSOC);

    echo "📄 Facturas encontradas: " . count($facturas) . "\n";

    $enviarXML = new enviarXML();
    $validar = new validacionComprobante();
    $autorizar = new autorizacionComprobante();

    foreach ($facturas as $factura) {

        $orden = $factura['orden_no'];
        echo "➡️ Procesando factura: {$orden}\n";

        /**
         * 1️⃣ GENERAR + ENVIAR XML
         */
        $claveAcceso = $enviarXML->envioXML($orden, FACTURA);

        if (!$claveAcceso) {
            echo "❌ Error al generar XML\n";
            continue;
        }

        // Guardar clave acceso + marcar enviado
        $upd = $db->prepare("
            UPDATE datos_cabecera_electronica
            SET claveacceso = ?, sri_enviado = 1
            WHERE id = ?
        ");
        $upd->execute([$claveAcceso, $factura['id']]);

        /**
         * 2️⃣ VALIDAR EN SRI
         */
        $validacion = $validar->validar_comprobante($claveAcceso, FACTURA);

        if (!isset($validacion['estado']) || $validacion['estado'] !== 'RECIBIDA') {
            echo "❌ RECHAZADA EN VALIDACIÓN\n";

            $db->prepare("
                UPDATE datos_cabecera_electronica
                SET estado_proceso = 2
                WHERE id = ?
            ")->execute([$factura['id']]);

            continue;
        }

        /**
         * 3️⃣ AUTORIZAR
         */
        sleep(2);
        $autorizacion = $autorizar->autorizacion_comprobante($claveAcceso, FACTURA);

        if (
            !isset($autorizacion['autorizaciones']['autorizacion']['estado']) ||
            $autorizacion['autorizaciones']['autorizacion']['estado'] !== 'AUTORIZADO'
        ) {
            echo "❌ NO AUTORIZADA\n";

            $db->prepare("
                UPDATE datos_cabecera_electronica
                SET estado_proceso = 2
                WHERE id = ?
            ")->execute([$factura['id']]);

            continue;
        }

        /**
         * 4️⃣ AUTORIZADA ✔
         */
        echo "✔ AUTORIZADA\n";

        $db->prepare("
            UPDATE datos_cabecera_electronica
            SET estado_proceso = 1
            WHERE id = ?
        ")->execute([$factura['id']]);

        /**
         * 5️⃣ ENVÍO DE CORREO
         */
        $facturaDB = $db->query("
            SELECT * FROM datos_cabecera_electronica
            WHERE id = {$factura['id']}
        ")->fetch(PDO::FETCH_ASSOC);

        if ($facturaDB && $facturaDB['correo_enviado'] == 0) {

            $empresa = $db->query("SELECT * FROM configuracion LIMIT 1")->fetch(PDO::FETCH_ASSOC);
            // $facturaElectronica = $this->model->getFacturaElectronica($claveAcceso);



            $dataInfo = array(
                'ruc' => $facturaDB['ruc'],
                'email' => $facturaDB['correo'],
                'fecha' => $facturaDB['fecha'],
                'totalfactura' => $facturaDB['totalfactura'],
                'cliente' => $facturaDB['cliente'],
                'claveAcceso' => $claveAcceso,
                'empresa' => $empresa['nombre'],
                'factura' => str_pad($facturaDB['orden_no'], 9, '0', STR_PAD_LEFT),
                'enviroment' => ENVIROMENT,
                'emailremitente' => $empresa['correo'],
                'establecimiento' => $empresa['establecimiento'],
                'puntoemi' => $empresa['puntoemi'],
                'tipo' => 'factura',
                'asunto' => 'Adjuntamos Comprobante Electronico'
            );
            $envio = sendEmailAutomatias($dataInfo);


            /* sendEmailAutomatias([
                 'email' => $facturaDB['correo'],
                 'cliente' => $facturaDB['cliente'],
                 'claveAcceso' => $claveAcceso,
                 'empresa' => $empresa['nombre'],
                 'factura' => $orden,
                 'tipo' => 'factura'
             ]);*/
            if ($envio) {
                $db->prepare("
                UPDATE datos_cabecera_electronica
                SET correo_enviado = 1
                WHERE id = ?
            ")->execute([$factura['id']]);
            }
        }
    }

    echo "🏁 CRON FINALIZADO\n";
} catch (Exception $e) {
    echo "🔥 ERROR: " . $e->getMessage() . "\n";
} finally {
    unlink($lockFile);
}

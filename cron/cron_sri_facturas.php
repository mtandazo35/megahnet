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
    // Filtro anti-zombies v2 (2026-06-05):
    //  - Exige detalle (sin lineas no se puede emitir).
    //  - Si NO hay gemela autorizada por ruc+fecha+total -> procesar.
    //  - Si SI hay gemela -> procesar solo si el cliente aun tiene cupo
    //    (autorizadas_del_cliente_esa_fecha < contratos_facturables_del_cliente).
    //    Esto distingue duplicado real (cupo lleno) de contrato distinto (varios contratos del mismo cliente).
    $sql = "
        SELECT dce.id, dce.orden_no
        FROM datos_cabecera_electronica dce
        WHERE estado_proceso = 0
        AND sri_enviado = 0
        AND fecha >= DATE_FORMAT(CURDATE(), '%Y-%m-01')
        AND fecha <  DATE_FORMAT(DATE_ADD(CURDATE(), INTERVAL 1 MONTH), '%Y-%m-01')
        AND EXISTS (SELECT 1 FROM detalle_factura_electronica dfe WHERE dfe.orden_no = dce.orden_no)
        AND (
          NOT EXISTS (
            SELECT 1 FROM datos_cabecera_electronica dce_auth
            INNER JOIN respuesta_sri rs_auth ON rs_auth.claveAcceso = dce_auth.claveacceso AND rs_auth.estado='AUTORIZADO'
            WHERE dce_auth.ruc = dce.ruc AND dce_auth.fecha = dce.fecha
              AND dce_auth.totalfactura = dce.totalfactura AND dce_auth.id != dce.id
          )
          OR (
            (SELECT COUNT(*) FROM datos_cabecera_electronica d2
             INNER JOIN respuesta_sri r2 ON r2.claveAcceso = d2.claveacceso AND r2.estado = 'AUTORIZADO'
             WHERE d2.id_cliente = dce.id_cliente AND d2.fecha = dce.fecha)
            <
            (SELECT COUNT(*) FROM contratos c
             WHERE c.id_cliente = dce.id_cliente AND c.estado = 1 AND c.factura = 1)
          )
        )
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
         * 3️⃣ AUTORIZAR (con reintento contra race condition SRI)
         */
        $autorizacion = null;
        foreach ([3, 5, 8, 12] as $wait) {
            sleep($wait);
            $autorizacion = $autorizar->autorizacion_comprobante($claveAcceso, FACTURA);
            $numComp = $autorizacion['numeroComprobantes'] ?? 0;
            $estAut = $autorizacion['autorizaciones']['autorizacion']['estado'] ?? null;
            if ($numComp > 0 && $estAut === 'AUTORIZADO') break;
        }

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

        // Envio de correo: solo si hay direccion valida. El cron_envio_correo.php
        // se encarga del reintento — aqui no debe romper el flujo del SRI.
        $emailDest = trim((string)($facturaDB['correo'] ?? ''));
        if ($facturaDB && $facturaDB['correo_enviado'] == 0 && filter_var($emailDest, FILTER_VALIDATE_EMAIL)) {
            try {

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

            } catch (\Throwable $eMail) {
                // Si el correo falla (PHPMailer, SMTP, etc.) NO debe romper el cron.
                // El cron_envio_correo.php lo reintenta despues.
                echo "  ⚠️ correo fallo (no bloquea): " . $eMail->getMessage() . "\n";
                error_log("[cron_sri_facturas correo] id={$factura['id']} : " . $eMail->getMessage());
            }
        }
    }

    echo "🏁 CRON FINALIZADO\n";
} catch (\Throwable $e) {
    // Throwable cubre Exception y Error (TypeError, etc.) — antes solo Exception
    // dejaba escapar errores fatales (PHP 7+) y rompia el script sin log claro.
    echo "🔥 ERROR: " . $e->getMessage() . "\n";
} finally {
    if (file_exists($lockFile)) @unlink($lockFile);
}

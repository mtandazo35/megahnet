<?php
date_default_timezone_set('America/Guayaquil');
define('ROOT_PATH', realpath(__DIR__ . '/..'));

require_once ROOT_PATH . '/config/Config.php';
require_once ROOT_PATH . '/facturaelectronica/lib2/config.php';
require_once ROOT_PATH . '/facturaelectronica/lib2/functions.php';
require_once ROOT_PATH . '/facturaelectronica/envio_xml.php';
require_once ROOT_PATH . '/facturaelectronica/src/validacionComprobante.php';
require_once ROOT_PATH . '/facturaelectronica/src/autorizacionComprobante.php';
require_once ROOT_PATH . '/config/cron_services.php';

$db = db_open();
if (!$db) { echo "ERROR: no DB\n"; exit(1); }

$ids = [11768, 12051, 12110, 12111, 12120, 12162, 12172, 12202];

$enviarXML = new enviarXML();
$validar   = new validacionComprobante();
$autorizar = new autorizacionComprobante();

$resumen = [];

foreach ($ids as $id) {
    $stmt = $db->prepare("SELECT id, orden_no, cliente FROM datos_cabecera_electronica WHERE id = ?");
    $stmt->execute([$id]);
    $factura = $stmt->fetch(PDO::FETCH_ASSOC);
    if (!$factura) { echo "[$id] NO ENCONTRADA\n\n"; $resumen[$id]='NO_ENCONTRADA'; continue; }

    $orden  = $factura['orden_no'];
    $cli    = substr($factura['cliente'],0,30);
    echo ">>> [id=$id orden=$orden] $cli\n";

    $claveAcceso = $enviarXML->envioXML($orden, FACTURA);
    if (!$claveAcceso) { echo "  XML FAIL\n\n"; $resumen[$id]='XML_FAIL'; continue; }
    echo "  clave: $claveAcceso\n";

    $db->prepare("UPDATE datos_cabecera_electronica SET claveacceso=?, sri_enviado=1 WHERE id=?")
       ->execute([$claveAcceso, $factura['id']]);

    $validacion = $validar->validar_comprobante($claveAcceso, FACTURA);
    $estVal = $validacion['estado'] ?? 'SIN_ESTADO';
    echo "  validacion: $estVal\n";

    if ($estVal !== 'RECIBIDA') {
        $msg = json_encode($validacion);
        $db->prepare("UPDATE datos_cabecera_electronica SET estado_proceso=2, mensaje_sri=? WHERE id=?")
           ->execute([substr($msg,0,500), $factura['id']]);
        echo "  RECHAZADA EN VALIDACION\n\n";
        $resumen[$id]='RECHAZADA_VAL';
        continue;
    }

    $autorizacion = null;
    foreach ([3, 5, 8, 12] as $wait) {
        sleep($wait);
        $autorizacion = $autorizar->autorizacion_comprobante($claveAcceso, FACTURA);
        $numComp = $autorizacion['numeroComprobantes'] ?? 0;
        $estAut  = $autorizacion['autorizaciones']['autorizacion']['estado'] ?? null;
        echo "  intento(wait=$wait): num=$numComp est=$estAut\n";
        if ($numComp > 0 && $estAut === 'AUTORIZADO') break;
    }

    $estado = $autorizacion['autorizaciones']['autorizacion']['estado'] ?? '';
    if ($estado !== 'AUTORIZADO') {
        $msg = '';
        if (isset($autorizacion['autorizaciones']['autorizacion']['mensajes']['mensaje'])) {
            $mens = $autorizacion['autorizaciones']['autorizacion']['mensajes']['mensaje'];
            if (isset($mens[0])) $mens = $mens[0];
            $msg = ($mens['mensaje'] ?? '') . ' | ' . ($mens['informacionAdicional'] ?? '');
        }
        echo "  NO AUTORIZADA: $msg\n\n";
        $db->prepare("UPDATE datos_cabecera_electronica SET estado_proceso=2, mensaje_sri=? WHERE id=?")
           ->execute([substr($msg,0,500), $factura['id']]);
        $resumen[$id]='NO_AUTORIZADA';
        continue;
    }

    echo "  AUTORIZADO\n\n";
    $db->prepare("UPDATE datos_cabecera_electronica SET estado=1, estado_proceso=1 WHERE id=?")
       ->execute([$factura['id']]);
    $resumen[$id]='AUTORIZADO';
}

echo "\n=== RESUMEN ===\n";
foreach ($resumen as $id => $r) echo "  $id : $r\n";

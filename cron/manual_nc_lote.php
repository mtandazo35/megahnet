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

$factura_ids = isset($argv[1]) ? array_map('intval', explode(',', $argv[1])) : [11610];
$motivo = 'ANULACION POR DUPLICIDAD DE COMPROBANTE EN LOTE DE FACTURACION 2026-06-01';

$enviarXML = new enviarXML();
$validar   = new validacionComprobante();
$autorizar = new autorizacionComprobante();

$resumen = [];

foreach ($factura_ids as $fid) {
    echo "===== Factura $fid =====\n";

    $stmt = $db->prepare("SELECT * FROM datos_cabecera_electronica WHERE id=?");
    $stmt->execute([$fid]);
    $f = $stmt->fetch(PDO::FETCH_ASSOC);
    if (!$f) { echo "  NO ENCONTRADA\n\n"; $resumen[$fid]='NO_FACT'; continue; }

    $stmt = $db->prepare("SELECT * FROM detalle_factura_electronica WHERE orden_no=?");
    $stmt->execute([$f['orden_no']]);
    $detalles = $stmt->fetchAll(PDO::FETCH_ASSOC);
    if (!$detalles) { echo "  sin detalle\n\n"; $resumen[$fid]='SIN_DETALLE'; continue; }

    $emp = $db->query("SELECT * FROM configuracion LIMIT 1")->fetch(PDO::FETCH_ASSOC);

    $stmt = $db->prepare("SELECT * FROM clientes WHERE id=?");
    $stmt->execute([$f['id_cliente']]);
    $cli = $stmt->fetch(PDO::FETCH_ASSOC);
    if (!$cli) { echo "  cliente no encontrado\n\n"; $resumen[$fid]='SIN_CLI'; continue; }

    if ($cli['id'] == 1)                     $tipoIdent = 7;
    else if ($cli['identidad'] == 'CEDULA')  $tipoIdent = 5;
    else                                     $tipoIdent = 4;

    // Para los casos donde el SRI exige valorModificacion CON IVA (caso 11632
    // - Zamora, configuracion fiscal especial), permitir override por env var
    // USE_TOTAL_CON_IVA=1 que toma el totalfactura en lugar de la suma base.
    if (getenv('USE_TOTAL_CON_IVA') === '1') {
        $total = round((float)$f['totalfactura'], 2);
    } else {
        $total = 0;
        foreach ($detalles as $d) $total += (float)$d['total'];
        $total = round($total, 2);
    }

    // SRI requiere formato XXX-XXX-XXXXXXXXX (establecimiento-puntoEmi-secuencial)
    $serieFacturaSri = $f['establecimiento'] . '-' . $f['punto_emi'] . '-' . str_pad($f['secuencial'], 9, '0', STR_PAD_LEFT);

    // INSERT cabecera con orden_no=0 temporal; despues UPDATE con id real.
    // El envioXML JOIN exige id == orden_no.
    $sqlCab = "INSERT INTO nota_credito_cabecera (orden_no, fecha, fecha_factura, serie_factura, cliente, ruc_cliente, tipo_identificacion, id_cliente, claveacceso_factura, motivo, establecimiento, punto_emi, secuencial, obligado, ambiente, total_modificar, total_descuento, id_usuario, id_empresa) VALUES (?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?)";
    $stmt = $db->prepare($sqlCab);
    $stmt->execute([
        0,
        date('Y-m-d'),
        $f['fecha'],
        $serieFacturaSri,
        $cli['nombre'],
        $cli['num_identidad'],
        $tipoIdent,
        $f['id_cliente'],
        $f['claveacceso'],
        $motivo,
        $emp['establecimiento'],
        $emp['puntoemi'],
        '000000000',
        $emp['contabilidad'],
        AMBIENTE,
        $total,
        0,
        4,
        $emp['id']
    ]);

    $ncId = (int)$db->lastInsertId();
    $numSerie = $ncId;
    $serieElectronica = str_pad($ncId, 9, '0', STR_PAD_LEFT);

    $db->prepare("UPDATE nota_credito_cabecera SET orden_no=?, secuencial=? WHERE id=?")
       ->execute([$ncId, $serieElectronica, $ncId]);

    echo "  NC#$ncId (sec=$serieElectronica) cli=" . substr($cli['nombre'],0,30) . " ruc=" . $cli['num_identidad'] . " total=$total\n";

    foreach ($detalles as $d) {
        $sqlDet = "INSERT INTO nota_credito_detalle (orden_no, cantidad, descripcion, precio_u, precio_pvp, total, iva, codproducto, descuento, id_producto) VALUES (?,?,?,?,?,?,?,?,?,?)";
        $stmt = $db->prepare($sqlDet);
        $stmt->execute([
            $ncId,
            $d['cantidad'],
            $d['item'],
            $d['precio_u'],
            $d['precio_pvp'] ?? $d['precio_u'],
            $d['total'],
            $d['iva'],
            $d['codproducto'],
            0,
            $d['id_producto']
        ]);
    }

    echo "  cabecera+detalle insertados\n";

    try {
        $claveAcceso = $enviarXML->envioXML($numSerie, NOTACREDITO);
        if (!$claveAcceso) { echo "  XML FAIL\n\n"; $resumen[$fid]='XML_FAIL'; continue; }
        echo "  clave NC: $claveAcceso\n";

        $db->prepare("UPDATE nota_credito_cabecera SET claveacceso=? WHERE orden_no=?")
           ->execute([$claveAcceso, $numSerie]);

        $resVal = $validar->validar_comprobante($claveAcceso, NOTACREDITO);
        $estVal = $resVal['estado'] ?? 'SIN_ESTADO';
        echo "  validacion: $estVal\n";
        if ($estVal !== 'RECIBIDA') {
            echo "  RECHAZADA EN VALIDACION: " . substr(json_encode($resVal),0,300) . "\n\n";
            $resumen[$fid]='RECHAZADA_VAL';
            continue;
        }

        $resAut = null;
        foreach ([3,5,8,12] as $wait) {
            sleep($wait);
            $resAut = $autorizar->autorizacion_comprobante($claveAcceso, NOTACREDITO);
            $numComp = $resAut['numeroComprobantes'] ?? 0;
            $estAut = $resAut['autorizaciones']['autorizacion']['estado'] ?? null;
            echo "    wait=$wait num=$numComp est=$estAut\n";
            if ($numComp > 0 && $estAut === 'AUTORIZADO') break;
        }

        $estado = $resAut['autorizaciones']['autorizacion']['estado'] ?? '';
        if ($estado === 'AUTORIZADO') {
            echo "  AUTORIZADO\n\n";
            $resumen[$fid]='AUTORIZADO';
        } else {
            $msg = '';
            if (isset($resAut['autorizaciones']['autorizacion']['mensajes']['mensaje'])) {
                $m = $resAut['autorizaciones']['autorizacion']['mensajes']['mensaje'];
                if (isset($m[0])) $m = $m[0];
                $msg = ($m['mensaje'] ?? '') . ' | ' . ($m['informacionAdicional'] ?? '');
            }
            echo "  NO AUTORIZADA: $msg\n\n";
            $resumen[$fid]='NO_AUTORIZADA:' . substr($msg,0,200);
        }

    } catch (\Throwable $e) {
        echo "  ERROR SRI: " . $e->getMessage() . "\n\n";
        $resumen[$fid]='EXCEPTION:' . substr($e->getMessage(),0,150);
    }
}

echo "\n========== RESUMEN ==========\n";
foreach ($resumen as $id => $r) echo "  $id : $r\n";

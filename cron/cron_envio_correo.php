<?php
/**
 * CRON 3
 * Envio de correo de facturas AUTORIZADAS
 */

require_once dirname(__DIR__) . '/config/Config.php';
require_once dirname(__DIR__) . '/config/Helpers.php';
require_once dirname(__DIR__) . '/config/app/Autoload.php';
include_once dirname(__DIR__) . '/config/ServicesSri.php';

$conexion = new Conexion();
$model    = new AutomaticasModel();
$empresa  = $model->getEmpresa();

$facturas = $model->getFacturasParaCorreo();

if (empty($facturas)) {
    // Ojo: aqui NO se sale. Mas abajo va el WhatsApp de pago, que es
    // independiente del correo; un exit() dejaria ese envio sin ejecutar.
    echo "CRON 3: No hay facturas pendientes de envio por correo.\n";
} else {

echo 'CRON 3 inicio. Lote: ' . count($facturas) . "\n";
$ok = 0; $skipNoMail = 0; $skipNoFile = 0; $fail = 0;

foreach ($facturas as $factura) {

    $claveAcceso = $factura['claveacceso'];
    $ordenNo     = $factura['orden_no'];

    $emailDest = trim((string)($factura['correo'] ?: ($factura['correo_cliente'] ?? '')));
    if (!filter_var($emailDest, FILTER_VALIDATE_EMAIL)) {
        $skipNoMail++;
        continue;
    }

    $pdf = ROOT_PATH . '/facturaelectronica/public/archivos/ride/' . $claveAcceso . '.pdf';
    $xml = ROOT_PATH . '/facturaelectronica/public/archivos/autorizados/' . $claveAcceso . '.xml';
    if (!file_exists($pdf) || !file_exists($xml)) {
        $skipNoFile++;
        continue;
    }

    $dataInfo = [
        'ruc'             => $factura['ruc'],
        'email'           => $emailDest,
        'fecha'           => $factura['fecha'],
        'totalfactura'    => $factura['totalfactura'],
        'cliente'         => $factura['cliente'],
        'claveAcceso'     => $claveAcceso,
        'empresa'         => $empresa['nombre'],
        'factura'         => $factura['secuencial'],
        'enviroment'      => defined('ENVIROMENT') ? ENVIROMENT : 2,
        'emailremitente'  => $empresa['correo'],
        'establecimiento' => $factura['establecimiento'],
        'puntoemi'        => $factura['punto_emi'],
        'tipo'            => 'factura',
        'asunto'          => 'Adjuntamos su Comprobante Electronico',
    ];

    if (sendEmailAutomatias($dataInfo) === true) {
        $ok++;
    } else {
        $fail++;
    }
}

echo "CRON 3 fin. OK=$ok sin-email=$skipNoMail sin-archivo=$skipNoFile fail=$fail\n";
}

// ---------------------------------------------------------------------------
// WhatsApp de "GRACIAS POR SU PAGO" diferido
//
// Al cobrar desde "Facturar Contratos" el mensaje solo se enviaba si el SRI
// autorizaba la factura en ese mismo instante. Cuando responde
// "EN PROCESAMIENTO" (habitual), el envio se saltaba y nadie lo reintentaba:
// el cliente pagaba y no recibia confirmacion.
//
// Aqui se envia a las facturas de COBRO MANUAL que ya quedaron autorizadas y
// siguen con whatsapp_enviado = 0. La facturacion automatica mensual nunca
// entra: sus facturas nacen con el valor por defecto 1, porque factura a todos
// los clientes hayan pagado o no y seria absurdo agradecerles un pago.
// ---------------------------------------------------------------------------

/** Recupera los meses cobrados desde la descripcion del detalle ("... SEPTIEMBRE-OCTUBRE-"). */
function mhn_mesesDeDescripcion($descripcion)
{
    $meses = 'ENERO|FEBRERO|MARZO|ABRIL|MAYO|JUNIO|JULIO|AGOSTO|SEPTIEMBRE|OCTUBRE|NOVIEMBRE|DICIEMBRE';
    if (preg_match_all('/(' . $meses . ')/', strtoupper((string)$descripcion), $m) && !empty($m[1])) {
        return implode('-', array_values(array_unique($m[1]))) . '-';
    }
    return '';
}

$pendientesWa = $model->getFacturasParaWhatsapp();

if (empty($pendientesWa)) {
    echo "WhatsApp de pago: no hay pendientes.\n";
} else {
    echo 'WhatsApp de pago. Lote: ' . count($pendientesWa) . "\n";
    $waOk = 0; $waSinTel = 0; $waFail = 0;

    foreach ($pendientesWa as $f) {
        $ordenNo = $f['orden_no'];
        $tel     = trim((string)($f['telefono'] ?? ''));

        // Sin telefono no hay nada que enviar: se marca resuelto para no
        // reintentarlo en cada pasada del cron.
        if ($tel === '') {
            $model->marcarWhatsappEnviado($ordenNo);
            $waSinTel++;
            continue;
        }

        // Mismo texto que el envio inmediato, para que el cliente reciba
        // siempre el mismo mensaje sin importar cuando autorizo el SRI.
        $meses = mhn_mesesDeDescripcion($f['descripcion'] ?? '');
        $texto = "Buen dia estimado/a cliente\n*" . ($empresa['nombre'] ?? '') . "*\n*GRACIAS POR SU PAGO*\n\n"
               . "Su saldo a la fecha es: $0.00\n";
        if ($meses !== '') {
            $texto .= "Incluido *SERVICIO " . $meses . "*\n";
        }
        $texto .= '*' . ($f['cliente'] ?? '') . '*';

        $res = function_exists('enviarWhatsappTexto') ? enviarWhatsappTexto($tel, $texto) : ['ok' => false];
        if (!empty($res['ok'])) {
            $model->marcarWhatsappEnviado($ordenNo);
            $waOk++;
        } else {
            // Se deja pendiente a proposito: el cron lo reintenta mientras la
            // factura siga dentro de la ventana de 3 dias.
            $waFail++;
            error_log('WhatsApp pago orden ' . $ordenNo . ' fallo: ' . ($res['msg'] ?? 'sin detalle'));
        }
    }

    echo "WhatsApp de pago fin. OK=$waOk sin-telefono=$waSinTel fallo=$waFail\n";
}

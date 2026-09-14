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
    exit("CRON 3: No hay facturas pendientes de envio.\n");
}

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

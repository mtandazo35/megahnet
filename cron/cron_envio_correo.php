<?php
/**
 * CRON 3
 * Envío de correo electrónico de facturas AUTORIZADAS
 * PHP 7.4.26
 */

// ===============================
// 1️⃣ CARGA DEL SISTEMA (MVC)
// ===============================
require_once dirname(__DIR__) . '/config/Config.php';
require_once dirname(__DIR__) . '/config/Helpers.php';
require_once dirname(__DIR__) . '/config/app/Autoload.php';
include_once dirname(__DIR__) . '/config/ServicesSri.php';


// ===============================
// 2️⃣ INSTANCIAS
// ===============================
$conexion = new Conexion();
$model    = new AutomaticasModel();

// Datos de la empresa (helper existente)
 $empresa= $model->getEmpresa();

// ===============================
// 3️⃣ FACTURAS LISTAS PARA ENVÍO
// estado_proceso = 1  → AUTORIZADA
// sri_enviado = 1     → YA PASÓ CRON 2
// correo_enviado = 0 → PENDIENTE DE CORREO
// ===============================
$facturas = $model->getFacturasParaCorreo();

if (empty($facturas)) {
    exit("CRON 3: No existen facturas pendientes de envío.\n");
}

// ===============================
// 4️⃣ PROCESO DE ENVÍO DE CORREO
// ===============================
foreach ($facturas as $factura) {

    $claveAcceso          = $factura['clave_acceso'];
    $numSerieElectronica  = $factura['num_serie'];

    // Obtener información completa de la factura
    $facturaElectronica = $model->getFacturaElectronica($claveAcceso);

    // Validación básica
    if (empty($facturaElectronica) || empty($facturaElectronica['correo'])) {
        continue;
    }

    // ===============================
    // 5️⃣ ARMADO DEL EMAIL
    // ===============================
    $dataInfo = array(
        'ruc'              => $facturaElectronica['ruc'],
        'email'            => $facturaElectronica['correo'],
        'fecha'            => $facturaElectronica['fecha'],
        'totalfactura'     => $facturaElectronica['totalfactura'],
        'cliente'          => $facturaElectronica['cliente'],
        'claveAcceso'      => $claveAcceso,
        'empresa'          => $empresa['nombre'],
        'factura'          => $factura['serie'],
        'enviroment'       => ENVIROMENT,
        'emailremitente'   => $empresa['correo'],
        'establecimiento'  => $empresa['establecimiento'],
        'puntoemi'         => $empresa['puntoemi'],
        'tipo'             => 'factura',
        'asunto'           => 'Adjuntamos su Comprobante Electrónico'
    );

    // ===============================
    // 6️⃣ ENVÍO DEL CORREO
    // ===============================
    $envio = sendEmailAutomatias($dataInfo);

    // ===============================
    // 7️⃣ MARCAR COMO ENVIADO
    // ===============================
    if ($envio === true) {
        $model->marcarCorreoEnviado($numSerieElectronica);
    }
}

echo "CRON 3: Correos procesados correctamente.\n";

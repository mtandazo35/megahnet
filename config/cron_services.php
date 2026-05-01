<?php


define('ROOT_PATH', realpath(__DIR__ . '/..'));//ENVIO DE FACTURA ELECTRONICA
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;
use PHPMailer\PHPMailer\SMTP;




    require_once ROOT_PATH . '/config/Config.php';

require ROOT_PATH . '/vendor/autoload.php';
require ROOT_PATH . '/libraries/phpmailer/Exception.php';
require ROOT_PATH . '/libraries/phpmailer/PHPMailer.php';
require ROOT_PATH . '/libraries/phpmailer/SMTP.php';


//Envio de correos
function sendEmailAutomatias($data)
{
    // print_r($data); exit;
    $claveAcceso = $data['claveAcceso'];
    $Empresa = $data['empresa'];
    //$enviroment = 2;
    //$idpedido= $data['pedido']['orden']['idpedido'];    

    if ($data['tipo'] == 'factura') {
        $ArchivoPDF = ROOT_PATH . "/facturaelectronica/public/archivos/ride/" . $claveAcceso . ".pdf";
        $ArchivoXML = ROOT_PATH . "/facturaelectronica/public/archivos/autorizados/" . $claveAcceso . ".xml";
    } else {
        $ArchivoPDF = ROOT_PATH . "/facturaelectronica/public/archivos/Retenciones/ride/" . $claveAcceso . ".pdf";
        $ArchivoXML = ROOT_PATH . "/facturaelectronica/public/archivos/Retenciones/autorizados/" . $claveAcceso . ".xml";
    }
    $cuerpo = 'BIENVENIDO A ' . $Empresa . ' ESTIMADO(A) ' . $data['cliente'] . ' ,HEMOS EMITIDO EL COMPROBANTE ELECTRONICO: FACTURA Nro. ' . $data['establecimiento'] . '-' . $data['puntoemi'] . '-' . $data['factura'] . ' ,FECHA AUTORIZADA: ' . $data['fecha'] . ' , TOTAL DE LA FACTURA ' . $data['totalfactura'];
    $res = enviarCorreoSMTP(
        $data['email'],
        $data['asunto'],
        $cuerpo,
        [
            'from_name'   => 'Factura Electronica - ' . $Empresa,
            'bcc'         => !empty($data['emailCopia']) ? $data['emailCopia'] : [],
            'attachments' => [$ArchivoPDF, $ArchivoXML],
        ]
    );
    return !empty($res['ok']);
}

?>
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
    //Create an instance; passing `true` enables exceptions
    $mail = new PHPMailer(true);


    try {
        //Server settings
        $mail->SMTPDebug = 0;                      //Enable verbose debug output
        $mail->isSMTP();                                            //Send using SMTP
        $mail->Host = HOST_SMTP;                     //Set the SMTP server to send through
        $mail->SMTPAuth = true;                                   //Enable SMTP authentication
        $mail->Username = USER_SMTP;                     //SMTP username
        $mail->Password = CLAVE_SMTP;                               //SMTP password
        if (SECURE_SMTP == 1) {
            $mail->SMTPSecure = PHPMailer::ENCRYPTION_SMTPS;            //Enable implicit TLS encryption

        } else {
            $mail->SMTPSecure = 'STARTTLS';            //Enable implicit TLS encryption
        }
        $mail->Port = PUERTO_SMTP;                                    //TCP port to connect to; use 587 if you have set `SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS`

        //Recipients
        $mail->setFrom(USER_SMTP, 'Factura Electronica - ' . $Empresa);
        $mail->addAddress($data['email']);     //Add a recipient
        if (!empty($data['emailCopia'])) {
            $mail->addBCC($data['emailCopia']);
        }

        //Content
        $mail->isHTML(true);                                  //Set email format to HTML
        $mail->Subject = $data['asunto'];
        $mail->Body = 'BIENVENIDO A ' . $Empresa . ' ESTIMADO(A) ' . $data['cliente'] . ' ,HEMOS EMITIDO EL COMPROBANTE ELECTRONICO: FACTURA Nro. ' . $data['establecimiento'] . '-' . $data['puntoemi'] . '-' . $data['factura'] . ' ,FECHA AUTORIZADA: ' . $data['fecha'] . ' , TOTAL DE LA FACTURA ' . $data['totalfactura'];
        $mail->addAttachment($ArchivoPDF);
        $mail->addAttachment($ArchivoXML);
        $mail->send();
        return true;
    } catch (Exception $e) {
        return false;
    }

}

?>
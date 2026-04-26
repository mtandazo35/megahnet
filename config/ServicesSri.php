<?php
if (!defined('ROOT_PATH')) define('ROOT_PATH', realpath(__DIR__ . '/..'));//ENVIO DE FACTURA ELECTRONICA
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;
use PHPMailer\PHPMailer\SMTP;
use Dompdf\Dompdf;







require ROOT_PATH . '/vendor/autoload.php';
require ROOT_PATH . '/libraries/phpmailer/Exception.php';
require ROOT_PATH . '/libraries/phpmailer/PHPMailer.php';
require ROOT_PATH . '/libraries/phpmailer/SMTP.php';


//SERVICIOS SRI 

include_once ROOT_PATH . '/facturaelectronica/lib2/config.php';
include_once ROOT_PATH . '/facturaelectronica/lib2/functions.php';
include_once ROOT_PATH . '/facturaelectronica/acciones.php';

include_once ROOT_PATH . '/facturaelectronica/envio_xml.php';
include_once ROOT_PATH . '/facturaelectronica/src/validacionComprobante.php';
include_once ROOT_PATH . '/facturaelectronica/src/autorizacionComprobante.php';

/*
include_once('facturaelectronica/lib2/config.php');
include_once('facturaelectronica/lib2/functions.php');
include_once('facturaelectronica/acciones.php');

include_once('facturaelectronica/envio_xml.php');
include_once('facturaelectronica/src/validacionComprobante.php');
include_once('facturaelectronica/src/autorizacionComprobante.php');*/


//Envio de correos
function sendEmail($data, $template, $vista)
{
    // print_r($data); exit;
    $claveAcceso = $data['claveAcceso'];
    $Empresa = $data['empresa'];
    $emailRemitente = $data['emailremitente'];
    $enviroment = $data['enviroment'];
    //$enviroment = 2;
    //$idpedido= $data['pedido']['orden']['idpedido'];    

    if ($data['tipo'] == 'factura') {
        $ArchivoPDF = "facturaelectronica/public/archivos/ride/" . $claveAcceso . ".pdf";
        $ArchivoXML = "facturaelectronica/public/archivos/autorizados/" . $claveAcceso . ".xml";
    } else {
        $ArchivoPDF = "facturaelectronica/public/archivos/Retenciones/ride/" . $claveAcceso . ".pdf";
        $ArchivoXML = "facturaelectronica/public/archivos/Retenciones/autorizados/" . $claveAcceso . ".xml";
    }


    //$nombreDelDocumento = "Assets/pdfordencompra/OrdenCompra_".$idpedido.".pdf";
    //$Url= $rutaGuardado.''.$nombreDelDocumento;
    //dep($rutaGuardado); exit; 
    //print_r($Archivo); exit;
    /* if (!file_exists($Url)) {
         exit("El archivo $Url no existe");
     }*/

    if ($enviroment == 1) {

        $asunto = $data['asunto'];
        $emailDestino = $data['email'];
        //$empresa = NOMBRE_REMITENTE;
        //$remitente = EMAIL_REMITENTE;

        $emailCopia = !empty($data['emailCopia']) ? $data['emailCopia'] : "";

        //ENVIO DE CORREO
        $de = "MIME-Version: 1.0\r\n"; //configuracion del correo
        $de .= "Content-type: text/html; charset=UTF-8\r\n";
        $de .= "From: {$Empresa} <{$emailRemitente}>\r\n";
        $de .= "Bcc: $emailCopia\r\n";
        ob_start(); //se carga los archivos 
        //require_once("Views/Template/Email/".$template.".php");
        $mensaje = ob_get_clean();

        // Fix migracion: usar PHPMailer SMTP en vez de mail() nativo (no pierde attachments)
        try {
            $mail = new PHPMailer(true);
            $mail->isSMTP();
            $mail->Host       = HOST_SMTP;
            $mail->SMTPAuth   = true;
            $mail->Username   = USER_SMTP;
            $mail->Password   = CLAVE_SMTP;
            $mail->SMTPSecure = (SECURE_SMTP == 1) ? PHPMailer::ENCRYPTION_SMTPS : PHPMailer::ENCRYPTION_STARTTLS;
            $mail->Port       = PUERTO_SMTP;
            $mail->CharSet    = "UTF-8";
            $mail->setFrom(USER_SMTP, "Factura Electronica - " . $Empresa);
            $mail->addAddress($emailDestino);
            if (!empty($emailCopia)) { $mail->addBCC($emailCopia); }
            $mail->isHTML(true);
            $mail->Subject = $asunto;
            $mail->Body = $mensaje;
            if (!empty($claveAcceso)) {
                if (!empty($ArchivoPDF) && file_exists($ArchivoPDF)) $mail->addAttachment($ArchivoPDF);
                if (!empty($ArchivoXML) && file_exists($ArchivoXML)) $mail->addAttachment($ArchivoXML);
            }
            $mail->send();
            return true;
        } catch (\Throwable $e) {
            error_log("sendEmail enviroment==1 fallo: " . $e->getMessage());
            return false;
        }
    } else {
        //Create an instance; passing `true` enables exceptions
        $mail = new PHPMailer(true);
        ob_start();
        require_once("views/" . $vista . "/" . $template . ".php");
        $mensaje = ob_get_clean();
        $dompdf = new Dompdf();
        $dompdf->loadHtml($mensaje);
        //Renderiza el archivo primero
        $dompdf->render();

        //Guardalo en una variable
        $output = $dompdf->output();
        //file_put_contents($rutaGuardado.$nombreDelDocumento,$output);

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
                $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;        //Enable implicit TLS encryption
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
            $mail->Body = $mensaje;
            $mail->addAttachment($ArchivoPDF);
            $mail->addAttachment($ArchivoXML);
            $mail->send();
            return true;
        } catch (Exception $e) {
            return false;
        }
    }
}

//Envio de correos
function sendEmailCotizacion($data, $template, $vista)
{
    // print_r($data); exit;
    $Empresa = $data['empresa'];
    $emailRemitente = $data['emailremitente'];
    $enviroment = $data['enviroment'];
    //$enviroment = 2;
    //$idpedido= $data['pedido']['orden']['idpedido'];    

    $ArchivoPDF = "facturaelectronica/public/archivos/Cotizaciones/Cotizacion_" . $data['cotizacion'] . ".pdf";


    //Create an instance; passing `true` enables exceptions
    $mail = new PHPMailer(true);
    ob_start();
    require_once("views/" . $vista . "/" . $template . ".php");
    $mensaje = ob_get_clean();
    $dompdf = new Dompdf();
    $dompdf->loadHtml($mensaje);
    //Renderiza el archivo primero
    $dompdf->render();

    //Guardalo en una variable
    $output = $dompdf->output();
    //file_put_contents($rutaGuardado.$nombreDelDocumento,$output);

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
        $mail->setFrom(USER_SMTP, 'Cotizacion - ' . $Empresa);
        $mail->addAddress($data['email']);     //Add a recipient
        if (!empty($data['emailCopia'])) {
            $mail->addBCC($data['emailCopia']);
        }

        //Content
        $mail->isHTML(true);                                  //Set email format to HTML
        $mail->Subject = $data['asunto'];
        $mail->Body = $mensaje;
        $mail->addAttachment($ArchivoPDF);
        $mail->send();
        return true;
    } catch (Exception $e) {
        return false;
    }

}

//Envio de correos
function sendEmailAutomatias($data)
{
    // print_r($data); exit;
    $claveAcceso = $data['claveAcceso'];
    $Empresa = $data['empresa'];
    //$enviroment = 2;
    //$idpedido= $data['pedido']['orden']['idpedido'];    

    if ($data['tipo'] == 'factura') {
        $ArchivoPDF = "facturaelectronica/public/archivos/ride/" . $claveAcceso . ".pdf";
        $ArchivoXML = "facturaelectronica/public/archivos/autorizados/" . $claveAcceso . ".xml";
    } else {
        $ArchivoPDF = "facturaelectronica/public/archivos/Retenciones/ride/" . $claveAcceso . ".pdf";
        $ArchivoXML = "facturaelectronica/public/archivos/Retenciones/autorizados/" . $claveAcceso . ".xml";
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


function sendEmailOrden($data, $template)
{


    // print_r($data); exit;
    //   $claveAcceso = $data['claveAcceso'];
    $Empresa = $data['empresa'];
    //$enviroment = 2;
    $ArchivoPDF = "facturaelectronica/public/archivos/facturables/Facturable_" . $data['factura'] . ".pdf";

    //Create an instance; passing `true` enables exceptions
    $mail = new PHPMailer(true);
    ob_start();
    require_once("views/automaticas/" . $template . ".php");
    $mensaje = ob_get_clean();
    $dompdf = new Dompdf();
    $dompdf->loadHtml($mensaje);
    //Renderiza el archivo primero
    $dompdf->render();

    //Guardalo en una variable
    $output = $dompdf->output();

    //file_put_contents($rutaGuardado.$nombreDelDocumento,$output);

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
        $mail->setFrom(USER_SMTP, 'Comprobante - ' . $Empresa);
        $mail->addAddress($data['email']);     //Add a recipient
        if (!empty($data['emailCopia'])) {
            $mail->addBCC($data['emailCopia']);
        }

        //Content
        $mail->isHTML(true);                                  //Set email format to HTML
        $mail->Subject = $data['asunto'];
        $mail->Body = $mensaje;
        $mail->addAttachment($ArchivoPDF);
        //  $mail->addAttachment($ArchivoXML);
        $mail->send();
        return true;
    } catch (Exception $e) {
        return false;
    }
}



function sendEmailOrdenAutomaticas($data)
{


    // print_r($data); exit;
    //   $claveAcceso = $data['claveAcceso'];
    $Empresa = $data['empresa'];
    //$idpedido= $data['pedido']['orden']['idpedido'];    
    $ArchivoPDF = "facturaelectronica/public/archivos/facturables/Facturable_" . $data['factura'] . ".pdf";

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
        $mail->setFrom(USER_SMTP, 'Comprobante - ' . $Empresa);
        $mail->addAddress($data['email']);     //Add a recipient
        if (!empty($data['emailCopia'])) {
            $mail->addBCC($data['emailCopia']);
        }
        //Content
        $mail->isHTML(true);                                  //Set email format to HTML
        $mail->Subject = $data['asunto'];
        $mail->Body = 'BIENVENIDO A ' . $Empresa . ' ESTIMADO(A) ' . $data['cliente'] . ' ,HEMOS EMITIDO EL COMPROBANTE: FACTURABLE Nro. ' . $data['establecimiento'] . '-' . $data['puntoemi'] . '-' . $data['factura'] . ' ,FECHA AUTORIZADA: ' . $data['fecha'] . ' , TOTAL DEL COMPROBANTE ' . $data['totalfactura'];
        $mail->addAttachment($ArchivoPDF);
        //  $mail->addAttachment($ArchivoXML);
        $mail->send();
        //  $mail->clearAddresses(); // Limpiar la lista de destinatarios para el siguiente envío
        // return true;
    } catch (Exception $e) {
        //  return false;
    }
}

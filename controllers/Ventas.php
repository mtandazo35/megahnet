<?php
require 'vendor/autoload.php';
use Mike42\Escpos\Printer;
use Mike42\Escpos\EscposImage;

use Dompdf\Dompdf;

class Ventas extends Controller
{
    private function cargarSri() { static $loaded=false; if (!$loaded) { include_once __DIR__ . '/../config/ServicesSri.php'; $loaded=true; } }

    private function verificarFirmaActiva()
    {
        if (!defined('FCPATH')) {
            define('FCPATH', __DIR__ . '/../facturaelectronica/');
        }
        $tokenPath = FCPATH . 'public/archivos/token/FIRMA.p12';
        $resultado = null;

        if (!file_exists($tokenPath) || filesize($tokenPath) <= 0) {
            $resultado = ['ok' => false, 'tipo' => 'NO_EXISTE', 'msg' => 'NO HAY FIRMA ELECTRONICA CARGADA. Suba la firma .p12 en Configuracion.'];
        } else {
            if (!defined('PASS')) {
                @include_once FCPATH . 'app/configuration.php';
            }
            $pass = defined('PASS') ? PASS : null;
            if (empty($pass)) {
                $resultado = ['ok' => false, 'tipo' => 'SIN_CLAVE', 'msg' => 'CONTRASENA DE LA FIRMA ELECTRONICA NO CONFIGURADA.'];
            } else {
                $bin = @file_get_contents($tokenPath);
                if ($bin === false) {
                    $resultado = ['ok' => false, 'tipo' => 'LECTURA_FALLO', 'msg' => 'NO SE PUDO LEER LA FIRMA ELECTRONICA.'];
                } else {
                    $certs = null;
                    if (!@openssl_pkcs12_read($bin, $certs, $pass)) {
                        $resultado = ['ok' => false, 'tipo' => 'CLAVE_INCORRECTA', 'msg' => 'FIRMA ELECTRONICA INVALIDA: contrasena incorrecta o archivo danado.'];
                    } else {
                        $parsed = @openssl_x509_parse($certs['cert']);
                        if (empty($parsed['validTo_time_t'])) {
                            $resultado = ['ok' => false, 'tipo' => 'SIN_VIGENCIA', 'msg' => 'NO SE PUDO LEER LA FECHA DE VENCIMIENTO DE LA FIRMA.'];
                        } else if (time() > $parsed['validTo_time_t']) {
                            $resultado = ['ok' => false, 'tipo' => 'VENCIDA', 'msg' => 'FIRMA ELECTRONICA VENCIDA EL ' . date('d/m/Y', $parsed['validTo_time_t']) . '. Renueve antes de facturar.', 'validTo' => $parsed['validTo_time_t']];
                        } else {
                            $diasRestantes = (int) floor(($parsed['validTo_time_t'] - time()) / 86400);
                            // Aviso de "por vencer" tambien dispara alerta (no bloquea)
                            if ($diasRestantes <= 30) {
                                $this->avisarFirma('POR_VENCER', 'Firma por vencer en ' . $diasRestantes . ' dias', '<p>La firma electronica vence el <b>' . date('d/m/Y', $parsed['validTo_time_t']) . '</b> (en ' . $diasRestantes . ' dias). Renuevela antes para no interrumpir la facturacion.</p>');
                            }
                            $resultado = ['ok' => true, 'validTo' => $parsed['validTo_time_t'], 'diasRestantes' => $diasRestantes];
                        }
                    }
                }
            }
        }

        // Si firma invalida: alertar al admin (rate-limited a 1 vez por hora)
        if (!$resultado['ok']) {
            $cuerpo = '<p><b>No se puede emitir facturas electronicas</b> porque la firma del SRI tiene un problema.</p>'
                . '<p><b>Tipo de problema:</b> ' . htmlspecialchars($resultado['tipo']) . '</p>'
                . '<p><b>Mensaje:</b> ' . htmlspecialchars($resultado['msg']) . '</p>'
                . '<p>Accion: ingresa a <code>/admin/datos</code>, sube el archivo .p12 actualizado y/o ingresa la contrasena correcta. Pulsa <b>Verificar</b> para confirmar.</p>';
            $this->avisarFirma($resultado['tipo'], $resultado['msg'], $cuerpo);
        }

        return $resultado;
    }

    /**
     * Envia alerta admin de firma con rate-limit de 1 hora (file-based).
     * No bloquea el flujo si SMTP falla.
     */
    private function avisarFirma($tipo, $asunto, $cuerpo)
    {
        try {
            $lockFile = sys_get_temp_dir() . '/firma-alert-' . md5(__DIR__ . '|' . $tipo) . '.txt';
            $lastSent = @file_exists($lockFile) ? (int) @file_get_contents($lockFile) : 0;
            if (time() - $lastSent < 3600) {
                return; // ya alertado en la ultima hora
            }
            if (function_exists('enviarAlertaAdmin')) {
                enviarAlertaAdmin('FIRMA_' . $tipo, $asunto, $cuerpo);
                @file_put_contents($lockFile, (string)time());
            }
        } catch (\Throwable $e) {
            error_log('avisarFirma fallo: ' . $e->getMessage());
        }
    }


    private $id_usuario;
    public function __construct()
    {
        session_start();

        //exit;
        parent::__construct();
        if (empty($_SESSION['id_usuario'])) {
            header('Location: ' . BASE_URL);
            exit;
        }


        $this->id_usuario = $_SESSION['id_usuario'];
    }
    public function index()
    {

        $data['title'] = 'Ventas';
        $data['script'] = 'ventas.js';
        $data['busqueda'] = 'busqueda.js';
        $data['carrito'] = 'posVenta';
        $data['empresa'] =  $this->model->getEmpresa();
        $data['cantidadDocumento'] =  $this->model->cantidadDocumento(date('Y-m'));

        $data['tipoPago'] =  $this->model->TipoPago(1);


        $resultSerie = $this->model->getSerie();
        $serie = ($resultSerie['total'] == null) ? 1 : $resultSerie['total'] + 1; // factura fisica
        $data['serie'] = $this->generate_numbers($serie, 1, 9);



        $resultSerieElectronica = $this->model->getSerieElectronica();
        $serieElectronica = ($resultSerieElectronica['total'] == null) ? 1 : $resultSerieElectronica['total'] + 1; // factura electronica
        $data['serieelectronica'] = $this->generate_numbers($serieElectronica, 1, 9);
        $this->views->getView('ventas', 'index', $data);
    }




    public function registrarVenta()
    {
        $this->cargarSri();
        $json = file_get_contents('php://input');
        $datos = json_decode($json, true);
        $array['productos'] = array();
        $empresa = $this->model->getEmpresa();
        $total = 0;

        if (!empty($datos['productos'])) {
            $fecha = date('Y-m-d');
            $hora = date('H:i:s');
            $metodo = $datos['metodo'];

            $estado = ($metodo == 'CREDITO') ? 2 : 1;
            $tipoPago = $datos['tipoPago'];

            //print_r(  $this->id_usuario);exit;
            //print_r($datos['productos']['0']['cantidad']);            

            $resultSerie = $this->model->getSerie();
            $numSerie = ($resultSerie['total'] == null) ? 1 : $resultSerie['total'] + 1;
            $serie = $this->generate_numbers($numSerie, 1, 9);

            $resultSerieElectronica = $this->model->getSerieElectronica();
            $numSerieElectronica = ($resultSerieElectronica['total'] == null) ? 1 : $resultSerieElectronica['total'] + 1;
            $serieElectronica = $this->generate_numbers($numSerieElectronica, 1, 9);
            $total12 = 0;
            $total0 = 0;
            $descuento = (!empty($datos['descuento'])) ? $datos['descuento'] : 0;
            $idCliente = $datos['idCliente'];
            //print_r( $descuento); exit;
            $datosCliente = $this->model->getCliente($idCliente);

            if ($datosCliente['id'] == 1) {
                $tipoIdentificacion = 7;
            } else if ($datosCliente['identidad'] == 'CEDULA') {
                $tipoIdentificacion = 5;
            } else {
                $tipoIdentificacion = 4;
            }
            //echo $direccion; exit;
            if (empty($idCliente)) {
                $res = array('msg' => 'EL CLIENTE ES REQUERIDO', 'type' => 'warning');
            } else if (empty($metodo)) {
                $res = array('msg' => 'EL METODO ES REQUERIDO', 'type' => 'warning');
            } else {
                $verifcarCaja = $this->model->getCaja($this->id_usuario);
                if (empty($verifcarCaja['monto_inicial'])) {
                    $res = array('msg' => 'LA CAJA ESTA CERRADA', 'type' => 'warning');
                } else {

                    if ($empresa['facturaelectronica'] == 0) {
                        foreach ($datos['productos'] as $producto) {
                            $result = $this->model->getProducto($producto['id']);
                            $data['id'] = $result['id'];
                            $data['nombre'] = $result['descripcion'];
                            $data['precio'] = $producto['precio']; //result es lo que trae de la tabla producto y producto es lo que trae de la tabla modificada de ventas
                            $data['cantidad'] = $producto['cantidad'];
                            $data['iva_producto'] = $result['iva'];
                            $data['codigobarra'] = $result['codigo'];
                            $subTotal = $producto['precio'] * $producto['cantidad'];
                            array_push($array['productos'], $data);
                            $total += $subTotal;
                        }
                        $datosProductos = json_encode($array['productos'], JSON_UNESCAPED_UNICODE);
                        $venta = $this->model->registrarVenta($datosProductos, $total, $fecha, $hora, $metodo, $descuento, $serie[0], $idCliente, $this->id_usuario);
                        if ($venta > 0) {
                            foreach ($datos['productos'] as $producto) {
                                $result = $this->model->getProducto($producto['id']);
                                //actualizar stock
                                if ($result['id_categoria'] == 1) {
                                    $nuevaCantidad = $result['cantidad'];
                                    $totalVentas = $result['ventas'] + $producto['cantidad'];
                                    $this->model->actualizarStock($nuevaCantidad, $totalVentas, $result['id']);

                                }else{
                                    $nuevaCantidad = $result['cantidad'] - $producto['cantidad'];
                                    $totalVentas = $result['ventas'] + $producto['cantidad'];
                                    $this->model->actualizarStock($nuevaCantidad, $totalVentas, $result['id']);
                                }

                                $movimiento = 'Venta N°: ' . $venta;
                                $cantidad = $producto['cantidad'];
                                $this->model->registrarMovimiento($movimiento, 'salida', $cantidad, $nuevaCantidad, $producto['id'], $this->id_usuario);
                            }
                            if ($metodo == 'CREDITO') {
                                $monto = $total - $descuento;
                                $this->model->registrarCredito($monto, $fecha, $hora, $venta, null);
                            }
                          /*  if ($datos['impresion']) {
                                $this->impresionDirecta($venta);
                            }*/
                            $res = array('msg' => 'VENTA GENERADA EXITOSAMENTE', 'type' => 'success', 'idVenta' => $venta, 'factura' => 'fisica');
                        } else {
                            $res = array('msg' => 'ERROR AL GENERAR VENTA', 'type' => 'error');
                        }
                    } else {
                        $descuento12 = 0;
                        $descuento0 = 0;
                        $total12 = 0;
                        $total0 = 0;
                        foreach ($datos['productos'] as $totalEncabezado) {
                            $result = $this->model->getProducto($totalEncabezado['id']);
                            // print_r($result); exit;

                            if ($result['iva'] == 0) {
                                $subTotal0 = $totalEncabezado['precio'] * $totalEncabezado['cantidad'];
                                $total0 += $subTotal0;
                                $descuento0 = round(($total0 * $descuento) / 100, 2);
                            } else {
                                $subTotal12 = $totalEncabezado['precio'] * $totalEncabezado['cantidad'];
                                $total12 += $subTotal12;
                                $descuento12 = round((($total12 / (CONCAT . $empresa['impuesto']))  * $descuento) / 100, 2); //el descuento se calcula con el impuesto que esta en la configuracion
                            }
                        }
                        $total = $total12 + $total0;
                        $totalDescuento = $descuento12 + $descuento0;
                        //print_r($subTotal12);

                        $firmaCheck = $this->verificarFirmaActiva();
                        if (!$firmaCheck['ok']) {
                            $res = array('msg' => $firmaCheck['msg'], 'type' => 'warning');
                        } else if ($total < MONTO_MINIMO_FACTURA) {
                            $res = array('msg' => 'EL MONTO MINIMO PARA FACTURAR ES ' . MONEDA . number_format(MONTO_MINIMO_FACTURA, 2), 'type' => 'warning');
                        } else if ($total > 50 && $idCliente == 1) {
                            $res = array('msg' => 'LAS VENTAS SUPERIOR A $50 REQUIERE DATOS CLIENTE', 'type' => 'warning');
                        } else {

                            $ventaEncabezado = $this->model->registrarEncabezado(
                                $fecha,
                                $numSerieElectronica,
                                $datosCliente['nombre'],
                                $datosCliente['direccion'],
                                $datosCliente['telefono'],
                                $datosCliente['num_identidad'],
                                $tipoIdentificacion,
                                $datosCliente['correo'],
                                $empresa['establecimiento'],
                                $empresa['puntoemi'],
                                $empresa['ruc'],
                                AMBIENTE,
                                $empresa['razon_social'],
                                $empresa['nombre'],
                                $numSerieElectronica,
                                $empresa['direccion'],
                                $empresa['contabilidad'],
                                $totalDescuento,
                                $total,
                                $tipoPago,
                                $estado,
                                $metodo,
                                $this->id_usuario,
                                $idCliente
                            );
                            if ($ventaEncabezado > 0) {
                                foreach ($datos['productos'] as $producto) {
                                    $result = $this->model->getProducto($producto['id']);
                                    $codigo = $result['codigo'];
                                    $descripcion = $producto['nombre']; //$result['descripcion']; es para el nombre original que van en la factura
                                    $iva = $result['iva'];
                                    $precio = $producto['precio']; //result es lo que trae de la tabla producto y producto es lo que trae de la tabla modificada de ventas
                                    if ($result['iva'] == 0) {
                                        $precio_siniva = $precio;
                                    } else {
                                        $precio_siniva = round($precio / (CONCAT .  $empresa['impuesto']), 4); //LO DIVIDIDO PARA EL PRECIO ES CON EL IVA DEL CONFIGURACION

                                    }
                                    // print_r($precio_siniva);
                                    $cantidad = $producto['cantidad'];
                                    $subTotal = round($precio_siniva * $producto['cantidad'], 4);
                                    $descuentoDetalle = round(($subTotal * $descuento) / 100, 2);
                                    // print_r($subTotal);
                                    //array_push($array['productos'], $data);
                                    // $total += $subTotal;
                                    $ventaDetalle =  $this->model->registrarDetalle($numSerieElectronica, $cantidad, $descripcion, $precio_siniva, $subTotal, $iva, $codigo, $descuentoDetalle,$precio,$descuento, $producto['id']);
                                }
                                if ($ventaDetalle > 0) {
                                    foreach ($datos['productos'] as $producto) {
                                        $result = $this->model->getProducto($producto['id']);
                                        //actualizar stock

                                        if ($result['id_categoria'] == 1) {
                                            $nuevaCantidad = $result['cantidad'];
                                            $totalVentas = $result['ventas'] + $producto['cantidad'];
                                            $this->model->actualizarStock($nuevaCantidad, $totalVentas, $result['id']);

                                        }else{
                                            $nuevaCantidad = $result['cantidad'] - $producto['cantidad'];
                                            $totalVentas = $result['ventas'] + $producto['cantidad'];
                                            $this->model->actualizarStock($nuevaCantidad, $totalVentas, $result['id']);
                                        }



                                        $movimiento = 'Venta Electronica N°: ' . $ventaDetalle;
                                        $cantidad = $producto['cantidad'];
                                        $this->model->registrarMovimiento($movimiento, 'salida', $cantidad, $nuevaCantidad, $producto['id'], $this->id_usuario);
                                    }
                                    if ($metodo == 'CREDITO') {
                                        $monto = $total - $descuento;
                                        $this->model->registrarCredito($monto, $fecha, $hora, null, $ventaEncabezado);
                                    }
                                    /*if ($datos['impresion']) {
                                        $this->impresionDirecta($ventaDetalle);
                                    }*/
                                    // $result_respuesta=array();

                                    // SRI try/catch ventas: si falla el SOAP/SRI, no rompemos la respuesta
                                    try {
                                        // SRI try/catch: tolerante a fallos SOAP/SRI
                                        try {
                                            $enviarXML = new enviarXML();
                                            $claveAcceso = $enviarXML->envioXML($numSerieElectronica, FACTURA);
                                            $validacionComprobante = new validacionComprobante();
                                            $validacion = $validacionComprobante->validar_comprobante($claveAcceso, FACTURA);
                                            $autorizacionComprobante = new autorizacionComprobante();
                                            $autorizacion = $autorizacionComprobante->autorizacion_comprobante($claveAcceso, FACTURA);
                                        } catch (\Throwable $e) {
                                            error_log('SRI fallo en controllers/Ventas.php: ' . $e->getMessage());
                                            $autorizacion = ['numeroComprobantes' => 1, 'autorizaciones' => ['autorizacion' => ['estado' => 'NO_AUTORIZADO']]];
                                            $claveAcceso = $claveAcceso ?? '';
                                        }
                                        $this->model->actualizarClaveAccesso($claveAcceso, $numSerieElectronica);
                                    } catch (\Throwable $e) {
                                        error_log('SRI ventas fallo: ' . $e->getMessage());
                                        $autorizacion = ['numeroComprobantes' => 1, 'autorizaciones' => ['autorizacion' => ['estado' => 'NO_AUTORIZADO']]];
                                        $claveAcceso = '';
                                        $sriFailed = true;
                                    }

                                    //$result = mysqli_num_rows($update_clave);

                                        if (!empty($sriFailed)) {
                                            // SRI no respondio; la factura quedo registrada localmente
                                            $res = array('msg' => 'FACTURA REGISTRADA. SRI no respondio, reintentar desde Factura Electronica.', 'type' => 'warning', 'idVenta' => $numSerieElectronica, 'factura' => 'electronica');
                                        } else if ($autorizacion['numeroComprobantes'] == 0) {
                                            try { $this->envioSriElectronica($numSerieElectronica); } catch (\Throwable $e) {
                                                error_log('Reintento SRI ventas fallo: ' . $e->getMessage());
                                                $res = array('msg' => 'FACTURA REGISTRADA. SRI rechazo el comprobante, revisar.', 'type' => 'warning', 'idVenta' => $numSerieElectronica, 'factura' => 'electronica');
                                            }
                                        }else{
                                            if ($autorizacion['autorizaciones']['autorizacion']['estado'] == 'AUTORIZADO') {
                                                $facturaElectronica = $this->model->getFacturaElectronica($claveAcceso);
        
                                                $dataInfo = array(
                                                    'ruc' => $facturaElectronica['ruc'],
                                                    'email' => $facturaElectronica['correo'],
                                                    'fecha' => $facturaElectronica['fecha'],
                                                    'totalfactura' => $facturaElectronica['totalfactura'],
                                                    'cliente' => $facturaElectronica['cliente'],
                                                    'claveAcceso' => $claveAcceso,
                                                    'empresa' => $empresa['nombre'],
                                                    'factura' => $serieElectronica[0],
                                                    'enviroment' => ENVIROMENT,
                                                    'emailremitente' => $empresa['correo'],
                                                    'establecimiento' => $empresa['establecimiento'],
                                                    'puntoemi' => $empresa['puntoemi'],
                                                    'tipo' => 'factura',
                                                    'asunto' => 'Adjuntamos Comprobante Electronico'
                                                );
                                                $res = array('msg' => 'FACTURA ELECTRONICA GENERADA EXITOSAMENTE', 'type' => 'success', 'ClaveAcceso' => $claveAcceso, 'factura' => 'electronica', 'idVenta' => $numSerieElectronica);
                                                // Alerta admin: cliente sin correo registrado
                                                if (empty($dataInfo['email']) || !filter_var($dataInfo['email'], FILTER_VALIDATE_EMAIL)) {
                                                    try {
                                                        $cuerpo = '<p>El comprobante <b>' . htmlspecialchars($dataInfo['factura'] ?? '') . '</b> fue <b>AUTORIZADO</b> por el SRI pero <b>no se pudo enviar al cliente</b> porque no tiene correo registrado.</p>'
                                                            . '<table border=0 cellpadding=4 style="border-collapse:collapse;">'
                                                            . '<tr><td><b>Cliente:</b></td><td>' . htmlspecialchars($dataInfo['cliente'] ?? '') . '</td></tr>'
                                                            . '<tr><td><b>RUC/Cedula:</b></td><td>' . htmlspecialchars($dataInfo['ruc'] ?? '') . '</td></tr>'
                                                            . '<tr><td><b>Total:</b></td><td>$' . number_format((float)($dataInfo['totalfactura'] ?? 0), 2) . '</td></tr>'
                                                            . '<tr><td><b>Clave acceso:</b></td><td><code>' . htmlspecialchars($dataInfo['claveAcceso'] ?? '') . '</code></td></tr>'
                                                            . '<tr><td><b>Fecha:</b></td><td>' . htmlspecialchars($dataInfo['fecha'] ?? '') . '</td></tr>'
                                                            . '</table>'
                                                            . '<p>Por favor entrega manualmente el RIDE/XML al cliente o solicita su correo electronico.</p>';
                                                        enviarAlertaAdmin('CLIENTE_SIN_CORREO', 'Cliente sin correo - Factura ' . ($dataInfo['factura'] ?? ''), $cuerpo);
                                                    } catch (\Throwable $e) { error_log('Alerta sin correo fallo: ' . $e->getMessage()); }
                                                }
                                                sendEmail($dataInfo, 'email_facturaelectronica','ventas');
                                            } else {
                                                $msgSri = 'ERROR EN LA FACTURA';
                                                $estadoSri = $autorizacion['autorizaciones']['autorizacion']['estado'] ?? '';
                                                $mens = $autorizacion['autorizaciones']['autorizacion']['mensajes']['mensaje'] ?? null;
                                                if ($mens) {
                                                    $lista = isset($mens['identificador']) ? [$mens] : (is_array($mens) ? $mens : []);
                                                    $detalles = [];
                                                    foreach ($lista as $m) {
                                                        if (is_array($m)) {
                                                            $detalles[] = trim(($m['mensaje'] ?? '') . ' ' . ($m['informacionAdicional'] ?? ''));
                                                        }
                                                    }
                                                    if ($detalles) $msgSri .= ' (' . $estadoSri . '): ' . implode(' | ', $detalles);
                                                } else if ($estadoSri) {
                                                    $msgSri .= ' (' . $estadoSri . ')';
                                                }
                                                error_log('SRI rechazo en ' . __FILE__ . ': clave=' . ($claveAcceso ?? '') . ' estado=' . $estadoSri . ' detalle=' . json_encode($mens, JSON_UNESCAPED_UNICODE));
                                                $res = array('msg' => $msgSri, 'type' => 'error');
                                            }

                                        }                                  
                                    //print_r($dataInfo); exit;

                                } else {
                                    $this->model->deleteFactura('datos_cabecera_electronica', 'orden_no', $numSerieElectronica);
                                    $this->model->resetFactura('datos_cabecera_electronica');
                                    $res = array('msg' => 'ERROR AL GENERAR LA FACTURA ELECTRONICA DETALLE ', 'type' => 'error');
                                }
                            } else {
                                $this->model->deleteFactura('datos_cabecera_electronica', 'orden_no', $numSerieElectronica);
                                $this->model->resetFactura('datos_cabecera_electronica');
                                $res = array('msg' => 'ERROR AL GENERAR FACURA ELECTRONICA ENCABEZADO', 'type' => 'error');
                            }
                        }
                    }
                }
            }
        } else {
            $res = array('msg' => 'CARRITO VACIO', 'type' => 'warning');
        }
        if (ob_get_level()) { @ob_end_clean(); }
        header('Content-Type: application/json; charset=utf-8');
        echo json_encode($res, JSON_UNESCAPED_UNICODE);
        die();
    }


    public function reporte($datos)
    {
        $this->cargarSri();
        ob_start();
        $array = explode(',', $datos);
        $tipo = $array[0];
        $idVenta = $array[1];

        $data['empresa'] = $this->model->getEmpresa();
        $data['venta'] = $this->model->getVenta($idVenta);
        $data['title'] = 'Venta';
        if (empty($data['venta'])) {
            echo 'Pagina no Encontrada';
            exit;
        }
        $this->views->getView('ventas', $tipo, $data);
        $html = ob_get_clean();
        $dompdf = new Dompdf();
        $options = $dompdf->getOptions();
        $options->set('isJavascriptEnabled', true);
        $options->set('isRemoteEnabled', true);
        $dompdf->setOptions($options);
        $dompdf->loadHtml($html);

        if ($tipo == 'ticked') {
            $dompdf->setPaper(array(0, 0, 255, 800), 'portrait');
        } else {
            $dompdf->setPaper('A4', 'vertical');
        }

        // Render the HTML as PDF
        $dompdf->render();

        // Output the generated PDF to Browser
        $dompdf->stream('FacturaImp.pdf', array('Attachment' => false));
    }

    public function facturaTicked($datos)
    {
        $this->cargarSri();
        ob_start();
        $array = explode(',', $datos);
        $claveAcceso = $array[0];
        $idVenta = $array[1];


        $data['empresa'] = $this->model->getEmpresa();
        $data['factura'] = $this->model->getFacturaElectronica($claveAcceso);
        $data['result_detalle'] = $this->model->getFacturaElectronicaDetalle($idVenta);

        if (empty($data['factura'])) {
            echo 'Pagina no Encontrada';
            exit;
        }
        $this->views->getView('ventas', 'facturaticket', $data);
        $html = ob_get_clean();
        $dompdf = new Dompdf();
        $options = $dompdf->getOptions();
        $options->set('isJavascriptEnabled', true);
        $options->set('isRemoteEnabled', true);
        $dompdf->setOptions($options);
        $dompdf->loadHtml($html);

        $dompdf->setPaper(array(0, 0, 255, 800), 'portrait');

        // Render the HTML as PDF
        $dompdf->render();

        // Output the generated PDF to Browser
        $dompdf->stream('Factura.pdf', array('Attachment' => false));
    }

    public function listar()
    {
        $data = $this->model->getVentas();
        for ($i = 0; $i < count($data); $i++) {
            if ($data[$i]['estado'] == 1) {
                $data[$i]['acciones'] = '<div>
                <a class="btn btn-danger" href="#" onclick="verReporte(' . $data[$i]['id'] . ')"><i class="fas fa-file-pdf"></i></a>
                <a class="btn btn-danger" href="#" onclick="anularVenta(' . $data[$i]['id'] . ')"><i class="fas fa-trash"></i></a>
                </div>';
                $data[$i]['estado'] = '<div><span class="badge bg-success">PAGADA</span></div>';
            } else {
                $data[$i]['acciones'] = '<div>
                
                <a class="btn btn-danger" href="#" onclick="verReporte(' . $data[$i]['id'] . ')"><i class="fas fa-file-pdf"></i></a>
                </div>';
                $data[$i]['estado'] = '<div><span class="badge bg-danger">ANULADA</span></div>';
            }
        }
        echo json_encode($data);
        die();
    }
    public function listarElectronica()
    {
        $data = $this->model->getVentasElectronica();
        for ($i = 0; $i < count($data); $i++) {
			
			
			
			
			$ArchivoPDF = "facturaelectronica/public/archivos/ride/" . $data[$i]['claveacceso'] . ".pdf";
            $ArchivoXML = "facturaelectronica/public/archivos/autorizados/" . $data[$i]['claveacceso'] . ".xml";



			if (file_exists($ArchivoPDF) === false ||  file_exists($ArchivoXML) === false) {
                // return array('error' => true, 'mensaje' => 'documento generado no existe');
                $data[$i]['acciones'] = '<div>
               ' . $btnReenviarSri . '
                                   <a class="btn btn-danger" href="#" onclick="anularVentaElectronica(' . $data[$i]['orden_no'] . ')"><i class="fas fa-trash"></i></a>

               </div>';
            } else {

			
			
            if (($data[$i]['autorizacion'] == 'AUTORIZADO' && $data[$i]['estado'] == 1) || ($data[$i]['autorizacion'] == 'AUTORIZADO' && $data[$i]['estado'] == 2)) {
                $data[$i]['acciones'] = '<div>
                <a class="btn btn-danger" href="' . BASE_URL . 'facturaelectronica/public/archivos/ride/' . $data[$i]['claveacceso'] . '.pdf' . '" target="_blank" title="FACTURA"><i class="fas fa-file-pdf"></i></a>
                <a class="btn btn-info" href="' . BASE_URL . 'facturaelectronica/public/archivos/autorizados/' . $data[$i]['claveacceso'] . '.xml' . '" target="_blank" title="XML"><i class="fa-solid fa-file-arrow-down text-white"></i></a>
                <a class="btn btn-success" href="#" onclick="envioCorreoElectronica(' . $data[$i]['orden_no'] . ')"><i class="fa-solid fa-envelope"></i></a>
                <a class="btn btn-danger" href="#" onclick="anularVentaElectronica(' . $data[$i]['orden_no'] . ')"><i class="fas fa-trash"></i></a>
                </div>';
            } else if ($data[$i]['autorizacion'] == 'NO AUTORIZADO') {
                $data[$i]['acciones'] = '<div>
                ' . $btnReenviarSri . '
                                <a class="btn btn-danger" href="#" onclick="anularVentaElectronica(' . $data[$i]['orden_no'] . ')"><i class="fas fa-trash"></i></a>

                </div>';
            } else if ($data[$i]['autorizacion'] == 'DEVUELTA') {
                $data[$i]['acciones'] = '<div>
                ' . $btnReenviarSri . '
                <a class="btn btn-danger" href="#" onclick="anularVentaElectronica(' . $data[$i]['orden_no'] . ')"><i class="fas fa-trash"></i></a>

                </div>';
            } else if ($data[$i]['autorizacion'] == 'EN PROCESO') {
                $data[$i]['acciones'] = '<div>
                ' . $btnReenviarSri . '
                 <a class="btn btn-danger" href="#" onclick="anularVentaElectronica(' . $data[$i]['orden_no'] . ')"><i class="fas fa-trash"></i></a>

                </div>';
            } else {
                $data[$i]['acciones'] = '<div>
                <a class="btn btn-info" href="' . BASE_URL . 'ventas/facturaTicked/' . $data[$i]['claveacceso'] . '/' . $data[$i]['orden_no'] . '" target="_blank"><i class="fa-solid fa-file-arrow-down text-white"></i></a>
                ' . $btnReenviarSri . '
                <a class="btn btn-danger" href="#" onclick="anularVentaElectronica(' . $data[$i]['orden_no'] . ')"><i class="fas fa-trash"></i></a>

                </div>';
            }

			}


            if ($data[$i]['autorizacion'] == 'AUTORIZADO') {
                $data[$i]['autorizacion'] = '<div><span class="badge bg-success">AUTORIZADO</span></div>';
            } else if ($data[$i]['autorizacion'] == 'NO AUTORIZADO') {
                $data[$i]['autorizacion'] = '<div><span class="badge bg-danger">NO AUTORIZADO</span></div>';
            } else if ($data[$i]['autorizacion'] == 'DEVUELTA') {
                $data[$i]['autorizacion'] =  '<div><span class="badge bg-warning">DEVUELTA</span></div>';
            } else {
                $data[$i]['autorizacion'] =  '<div><span class="badge bg-info">EN PROCESO</span></div>';
            }



            if ($data[$i]['estado'] == 1) {
                $data[$i]['estado'] = '<div style="text-align: center;"><span class="badge bg-success">PAGADA</span></div>';
            } else if ($data[$i]['estado'] == 2) {
                $data[$i]['estado'] = '<div style="text-align: center;"><span class="badge bg-warning">PENDIENTE</span></div>';
            } else if ($data[$i]['estado'] == 4) {
                $data[$i]['estado'] = '<div style="text-align: center;"><span class="badge bg-danger">NOTA CREDITO</span></div>';
            } else {
                $data[$i]['estado'] = '<div style="text-align: center;"><span class="badge bg-danger">ANULADA</span></div>';
            }

            $data[$i]['factura'] = $this->generate_numbers($data[$i]['orden_no'], 1, 9);

            // Estado de envio de correo al cliente
            $correoRaw = trim((string)($data[$i]['correo'] ?? ''));
            $tieneTexto = $correoRaw !== '';
            $emailValido = $tieneTexto && filter_var($correoRaw, FILTER_VALIDATE_EMAIL);
            $correoEstado = (int)($data[$i]['correo_enviado'] ?? 0);
            if (!$tieneTexto) {
                $data[$i]['correoBadge'] = '<span class="badge bg-secondary" title="Cliente sin email registrado">SIN CORREO</span>';
            } else if (!$emailValido) {
                $data[$i]['correoBadge'] = '<span class="badge bg-danger" title="Email mal escrito en BD: ' . htmlspecialchars($correoRaw) . '">EMAIL INVÁLIDO</span>';
            } else if ($correoEstado === 1) {
                $data[$i]['correoBadge'] = '<span class="badge bg-success" title="Enviado a ' . htmlspecialchars($correoRaw) . '">ENVIADO</span>';
            } else if ($correoEstado === 2) {
                $data[$i]['correoBadge'] = '<span class="badge bg-dark" title="Archivos RIDE/XML no existen en disco para esta factura">ARCHIVOS PERDIDOS</span>';
            } else {
                $data[$i]['correoBadge'] = '<span class="badge bg-warning text-dark" title="Pendiente de envio a ' . htmlspecialchars($correoRaw) . '">NO ENVIADO</span>';
            }

            // Badge DUPLICADA: si el cliente tiene mas facturas autorizadas en el mes
            // que contratos activos, y esta fila no es la primera del mes (facturas_previas_mes >= contratos),
            // marcarla como duplicada.
            $prev   = (int)($data[$i]['facturas_previas_mes'] ?? 0);
            $contrs = (int)($data[$i]['contratos_activos']    ?? 0);
            $esDuplicada = $contrs > 0 && $prev >= $contrs;
            if ($esDuplicada) {
                $data[$i]['duplicadaBadge'] = '<span class="badge bg-danger" title="Esta factura excede el numero de contratos activos del cliente — sugerida para Nota de Credito">DUPLICADA</span>';
                // Inyectar boton 'Emitir NC' en acciones (si la factura esta autorizada)
                if (strpos((string)$data[$i]['acciones'], 'fas fa-trash') !== false && strpos((string)$data[$i]['acciones'], 'envioCorreoElectronica') !== false) {
                    $btnNC = '<a class="btn btn-warning btn-sm" href="' . BASE_URL . 'notaCredito/index?cliente=' . (int)$data[$i]['id_cliente'] . '&claveAcceso=' . urlencode($data[$i]['claveacceso']) . '" target="_blank" title="Emitir Nota de Credito (factura duplicada)"><i class="fa-solid fa-file-circle-minus text-white"></i></a>';
                    $data[$i]['acciones'] = str_replace('</div>', $btnNC . '</div>', $data[$i]['acciones']);
                }
            } else {
                $data[$i]['duplicadaBadge'] = '<span class="badge bg-light text-muted">—</span>';
            }
        }
        echo json_encode($data, JSON_UNESCAPED_UNICODE);
        die();
    }

    /**
     * Reenvio masivo al SRI: para cada factura no autorizada del mes, llama a
     * envioSriElectronica que firma + valida + autoriza. Procesa en lote pequeno
     * para no chocar con timeout del proxy.
     */
    public function reenviarSriMasivo()
    {
        $this->cargarSri();
        @set_time_limit(0);
        @ini_set('memory_limit', '512M');
        $limite = isset($_GET['limite']) ? max(1, min(50, (int)$_GET['limite'])) : 5;
        $pendientes = $this->model->getSriPendientes($limite);
        $ok = 0;
        $fallidos = [];
        foreach ($pendientes as $row) {
            $ordenNo = $row['orden_no'];
            $claveAcceso = '';
            try {
                $enviarXML = new enviarXML();
                $claveAcceso = $enviarXML->envioXML($ordenNo, FACTURA);
                $validacionComprobante = new validacionComprobante();
                $validacion = $validacionComprobante->validar_comprobante($claveAcceso, FACTURA);
                $autorizacionComprobante = new autorizacionComprobante();
                $autorizacion = $autorizacionComprobante->autorizacion_comprobante($claveAcceso, FACTURA);
                $this->model->actualizarClaveAccesso($claveAcceso, $ordenNo);
                $estado = $autorizacion['autorizaciones']['autorizacion']['estado'] ?? 'SIN_RESPUESTA';
                if ($estado === 'AUTORIZADO') {
                    $ok++;
                } else {
                    $mens = $autorizacion['autorizaciones']['autorizacion']['mensajes']['mensaje'] ?? null;
                    $detalle = '';
                    if ($mens) {
                        $lista = isset($mens['identificador']) ? [$mens] : (is_array($mens) ? $mens : []);
                        $tmp = [];
                        foreach ($lista as $m) if (is_array($m)) $tmp[] = trim(($m['mensaje'] ?? '') . ' ' . ($m['informacionAdicional'] ?? ''));
                        $detalle = implode(' | ', $tmp);
                    }
                    $est = $this->model->getVentasElectronicaUnica($ordenNo);
                    $fallidos[] = [
                        'orden_no' => $ordenNo,
                        'cliente'  => $est[0]['cliente'] ?? '',
                        'error'    => $estado . ($detalle ? ': ' . $detalle : ''),
                    ];
                }
            } catch (Throwable $e) {
                error_log('reenviarSriMasivo orden=' . $ordenNo . ' fallo: ' . $e->getMessage());
                $fallidos[] = ['orden_no' => $ordenNo, 'cliente' => '', 'error' => $e->getMessage()];
            }
        }
        header('Content-Type: application/json');
        echo json_encode([
            'procesadas' => $ok,
            'fallidas'   => count($fallidos),
            'fallidos'   => $fallidos,
            'lote'       => count($pendientes),
        ], JSON_UNESCAPED_UNICODE);
        die();
    }


    /** Cuenta facturas no AUTORIZADAS del mes (badge). */
    public function contarPendientesSri()
    {
        $pdo = new \PDO('mysql:host=' . HOSTT . ';dbname=' . DBNAME . ';charset=utf8mb4', USER, PASSWORD);
        $stmt = $pdo->query("SELECT COUNT(DISTINCT dce.id) FROM datos_cabecera_electronica dce
            LEFT JOIN respuesta_sri rs ON rs.claveAcceso = dce.claveacceso
            LEFT JOIN datos_cabecera_electronica dce_auth
              ON dce_auth.ruc = dce.ruc AND dce_auth.fecha = dce.fecha
             AND dce_auth.totalfactura = dce.totalfactura AND dce_auth.id != dce.id
            LEFT JOIN respuesta_sri rs_auth ON rs_auth.claveAcceso = dce_auth.claveacceso AND rs_auth.estado = 'AUTORIZADO'
            WHERE dce.fecha >= DATE_FORMAT(CURDATE(), '%Y-%m-01')
              AND dce.fecha <  DATE_FORMAT(DATE_ADD(CURDATE(), INTERVAL 1 MONTH), '%Y-%m-01')
              AND (rs.estado IS NULL OR rs.estado != 'AUTORIZADO')
              AND rs_auth.id IS NULL");
        header('Content-Type: application/json');
        echo json_encode(['pendientes' => (int)$stmt->fetchColumn()]);
        die();
    }

    public function anular($idVenta)
    {
        if (isset($_GET) && is_numeric($idVenta)) {
            $data = $this->model->anular($idVenta);
            if ($data == 1) {
                $resultVenta = $this->model->getVenta($idVenta);
                $ventaProducto = json_decode($resultVenta['productos'], true);
                foreach ($ventaProducto as $producto) {
                    $result = $this->model->getProducto($producto['id']);
                    $nuevaCantidad = $result['cantidad'] + $producto['cantidad'];
                    $totalVentas = $result['ventas'] - $producto['cantidad'];

                    $this->model->actualizarStock($nuevaCantidad, $totalVentas, $producto['id']);
                    //movimientos
                    $movimiento = 'Devolución Venta N°: ' . $idVenta;
                    $this->model->registrarMovimiento($movimiento, 'entrada', $producto['cantidad'], $nuevaCantidad, $producto['id'], $this->id_usuario);
                }
                if ($resultVenta['metodo'] == 'CREDITO') {
                    $this->model->anularCredito($idVenta, 'fisico');
                }
                $res = array('msg' => 'VENTA ANULADO EXITOSAMENTE', 'type' => 'success');
            } else {
                $res = array('msg' => 'ERROR AL ANULAR', 'type' => 'error');
            }
        } else {
            $res = array('msg' => 'ERROR DESCONOCIDO', 'type' => 'error');
        }
        echo json_encode($res);
        die();
    }
    public function anularElectronica($idVenta)
    {
        if (isset($_GET) && is_numeric($idVenta)) {
            $data = $this->model->anularElectronica($idVenta);
            if ($data == 1) {
                $resultVenta = $this->model->getVentaElectronica($idVenta);
                //print_r($resultVenta); exit;
                foreach ($resultVenta as $producto) {
                    $result = $this->model->getProducto($producto['id']);
                    $nuevaCantidad = $result['cantidad'] + $producto['cantidad'];
                    $totalVentas = $result['ventas'] - $producto['cantidad'];

                    $this->model->actualizarStock($nuevaCantidad, $totalVentas, $producto['id']);
                    //movimientos
                    $movimiento = 'Devolución Venta Electronica N°: ' . $idVenta;
                    $this->model->registrarMovimiento($movimiento, 'entrada', $producto['cantidad'], $nuevaCantidad, $producto['id'], $this->id_usuario);
                }
                if ($resultVenta['0']['metodo'] == 'CREDITO') {
                    $this->model->anularCredito($idVenta, 'electronico');
                }
                $res = array('msg' => 'FACTURA ELECTRONICA ANULADA EXITOSAMENTE', 'type' => 'success');
            } else {
                $res = array('msg' => 'ERROR AL ANULAR FACTURA ELECTRONICA', 'type' => 'error');
            }
        } else {
            $res = array('msg' => 'ERROR DESCONOCIDO', 'type' => 'error');
        }
        echo json_encode($res);
        die();
    }

    public function impresionDirecta($idVenta)
    {
        // Linux: impresion termica directa via WindowsPrintConnector no soportada.
        // Si se requiere POS impresion en Linux, migrar a CupsPrintConnector + CUPS daemon.
        error_log("impresionDirecta deshabilitada en Linux: idVenta=" . $idVenta);
        return false;
    }

    public function verificarStock($idProducto)
    {
        $data = $this->model->getProducto($idProducto);
        echo json_encode($data);
        die();
    }

    //REENVIO DE FACTURA ELECTRONICA
    public function envioFacturaElectronica($idVenta)
    {
        $this->cargarSri();
        $empresa = $this->model->getEmpresa();

        $facturaElectronica = $this->model->getVentaElectronica($idVenta);

        $claveAcceso = $facturaElectronica['0']['claveacceso'];
        //print_r($facturaElectronica); exit;
        $filePDF = 'facturaelectronica/public/archivos/ride/' . $claveAcceso . '.pdf';
        $fileXML = 'facturaelectronica/public/archivos/autorizados/' . $claveAcceso . '.xml';

        if (file_exists($filePDF) && file_exists($fileXML)) {
            $serieElectronica = $this->generate_numbers($facturaElectronica['0']['orden_no'], 1, 9);

            $dataInfo = array(
                'ruc' => $facturaElectronica['0']['ruc'],
                'email' => $facturaElectronica['0']['correo'],
                'fecha' => $facturaElectronica['0']['fecha'],
                'totalfactura' => $facturaElectronica['0']['totalfactura'],
                'cliente' => $facturaElectronica['0']['cliente'],
                'claveAcceso' => $claveAcceso,
                'empresa' => $empresa['nombre'],
                'factura' => $serieElectronica[0],
                'enviroment' => ENVIROMENT,
                'emailremitente' => $empresa['correo'],
                'establecimiento' => $empresa['establecimiento'],
                'puntoemi' => $empresa['puntoemi'],
                'tipo' => 'factura',
                'asunto' => 'Adjuntamos Comprobante Electronico'
            );
            $res = array('msg' => 'FACTURA ELECTRONICA ENVIADA EXITOSAMENTE', 'type' => 'success');
            // Alerta admin: cliente sin correo registrado
            if (empty($dataInfo['email']) || !filter_var($dataInfo['email'], FILTER_VALIDATE_EMAIL)) {
                try {
                    $cuerpo = '<p>El comprobante <b>' . htmlspecialchars($dataInfo['factura'] ?? '') . '</b> fue <b>AUTORIZADO</b> por el SRI pero <b>no se pudo enviar al cliente</b> porque no tiene correo registrado.</p>'
                        . '<table border=0 cellpadding=4 style="border-collapse:collapse;">'
                        . '<tr><td><b>Cliente:</b></td><td>' . htmlspecialchars($dataInfo['cliente'] ?? '') . '</td></tr>'
                        . '<tr><td><b>RUC/Cedula:</b></td><td>' . htmlspecialchars($dataInfo['ruc'] ?? '') . '</td></tr>'
                        . '<tr><td><b>Total:</b></td><td>$' . number_format((float)($dataInfo['totalfactura'] ?? 0), 2) . '</td></tr>'
                        . '<tr><td><b>Clave acceso:</b></td><td><code>' . htmlspecialchars($dataInfo['claveAcceso'] ?? '') . '</code></td></tr>'
                        . '<tr><td><b>Fecha:</b></td><td>' . htmlspecialchars($dataInfo['fecha'] ?? '') . '</td></tr>'
                        . '</table>'
                        . '<p>Por favor entrega manualmente el RIDE/XML al cliente o solicita su correo electronico.</p>';
                    enviarAlertaAdmin('CLIENTE_SIN_CORREO', 'Cliente sin correo - Factura ' . ($dataInfo['factura'] ?? ''), $cuerpo);
                } catch (\Throwable $e) { error_log('Alerta sin correo fallo: ' . $e->getMessage()); }
            }
            sendEmail($dataInfo, 'email_facturaelectronica','ventas');
        } else {
            $res = array('msg' => 'ERROR AL ENVIAR FACTURA ELECTRONICA', 'type' => 'error');
        }


        echo json_encode($res);
        die();
    }

    //REENVIO DE FACTURA ELECTRONICA AL SRI
    public function envioSriElectronica($idVenta)
    {
        $this->cargarSri();
        $res = array('msg' => 'NO SE PUDO PROCESAR LA FACTURA. Reintentar desde Factura Electronica.', 'type' => 'warning', 'idVenta' => $idVenta);
        $empresa = $this->model->getEmpresa();

        $facturaElectronica = $this->model->getVentaElectronica($idVenta);
        //$claveAcceso= $facturaElectronica['0']['claveacceso'];


        // SRI try/catch: tolerante a fallos SOAP/SRI
        try {
            $enviarXML = new enviarXML();
            $claveAcceso = $enviarXML->envioXML($idVenta, FACTURA);
            $validacionComprobante = new validacionComprobante();
            $validacion = $validacionComprobante->validar_comprobante($claveAcceso, FACTURA);
            $autorizacionComprobante = new autorizacionComprobante();
            $autorizacion = $autorizacionComprobante->autorizacion_comprobante($claveAcceso, FACTURA);
        } catch (\Throwable $e) {
            error_log('SRI fallo en controllers/Ventas.php: ' . $e->getMessage());
            $autorizacion = ['numeroComprobantes' => 1, 'autorizaciones' => ['autorizacion' => ['estado' => 'NO_AUTORIZADO']]];
            $claveAcceso = $claveAcceso ?? '';
        }
        //  print_r($autorizacion['autorizaciones']['autorizacion']['estado']); exit;
        // $data['claveAcceso'] = $claveAcceso;
        $this->model->actualizarClaveAccesso($claveAcceso, $idVenta);
       // print_r($autorizacion['autorizaciones']); exit;
        $estadoVal  = isset($validacion['estado']) ? $validacion['estado'] : '';
        $numComp    = isset($autorizacion['numeroComprobantes']) ? intval($autorizacion['numeroComprobantes']) : 0;
        $mensajeSri = isset($validacion['comprobantes']['comprobante']['mensajes']['mensaje']['mensaje'])
                        ? $validacion['comprobantes']['comprobante']['mensajes']['mensaje']['mensaje'] : '';
        $msgUpper = mb_strtoupper((string)$mensajeSri, 'UTF-8');
        $yaRegistrada = (strpos($msgUpper, 'REGISTRADA') !== false || strpos($msgUpper, 'YA EXIST') !== false);

        if ($yaRegistrada) {
            // SRI ya tiene este comprobante. Confirmar via autorizacion y reportar OK al cliente.
            $estadoAutoriz = isset($autorizacion['autorizaciones']['autorizacion']['estado'])
                              ? $autorizacion['autorizaciones']['autorizacion']['estado'] : '';
            if ($estadoAutoriz === 'AUTORIZADO') {
                $facturaElectronica = $this->model->getFacturaElectronica($claveAcceso);
                $res = array(
                    'msg' => 'FACTURA YA AUTORIZADA POR EL SRI (estaba registrada)',
                    'type' => 'success',
                    'factura' => 'electronica',
                    'ClaveAcceso' => $claveAcceso,
                    'idVenta' => $idVenta,
                );
            } else {
                $res = array('msg' => 'SRI ya tenia el comprobante registrado pero estado: ' . ($estadoAutoriz ?: 'desconocido'), 'type' => 'warning', 'idVenta' => $idVenta);
            }
        } else if ($estadoVal === 'DEVUELTA'){
            $res = array('msg' => $mensajeSri ?: 'SRI: comprobante DEVUELTO', 'type' => 'error');
        }else if ($numComp === 0){
            $res = array('msg' => $mensajeSri ?: 'SRI: sin numero de comprobante (no autorizado)', 'type' => 'error');
        }else {

            $facturaElectronica = $this->model->getFacturaElectronica($claveAcceso);
            $serieElectronica = $this->generate_numbers($idVenta, 1, 9);

            $dataInfo = array(
                'ruc' => $facturaElectronica['ruc'],
                'email' => $facturaElectronica['correo'],
                'fecha' => $facturaElectronica['fecha'],
                'totalfactura' => $facturaElectronica['totalfactura'],
                'cliente' => $facturaElectronica['cliente'],
                'claveAcceso' => $claveAcceso,
                'empresa' => $empresa['nombre'],
                'factura' => $serieElectronica[0],
                'enviroment' => ENVIROMENT,
                'emailremitente' => $empresa['correo'],
                'establecimiento' => $empresa['establecimiento'],
                'puntoemi' => $empresa['puntoemi'],
                'tipo' => 'factura',
                'asunto' => 'Adjuntamos Comprobante Electronico'
            );
            $res = array('msg' => 'FACTURA ELECTRONICA GENERADA EXITOSAMENTE REENVIO AL SRI', 'type' => 'success');
            // Alerta admin: cliente sin correo registrado
            if (empty($dataInfo['email']) || !filter_var($dataInfo['email'], FILTER_VALIDATE_EMAIL)) {
                try {
                    $cuerpo = '<p>El comprobante <b>' . htmlspecialchars($dataInfo['factura'] ?? '') . '</b> fue <b>AUTORIZADO</b> por el SRI pero <b>no se pudo enviar al cliente</b> porque no tiene correo registrado.</p>'
                        . '<table border=0 cellpadding=4 style="border-collapse:collapse;">'
                        . '<tr><td><b>Cliente:</b></td><td>' . htmlspecialchars($dataInfo['cliente'] ?? '') . '</td></tr>'
                        . '<tr><td><b>RUC/Cedula:</b></td><td>' . htmlspecialchars($dataInfo['ruc'] ?? '') . '</td></tr>'
                        . '<tr><td><b>Total:</b></td><td>$' . number_format((float)($dataInfo['totalfactura'] ?? 0), 2) . '</td></tr>'
                        . '<tr><td><b>Clave acceso:</b></td><td><code>' . htmlspecialchars($dataInfo['claveAcceso'] ?? '') . '</code></td></tr>'
                        . '<tr><td><b>Fecha:</b></td><td>' . htmlspecialchars($dataInfo['fecha'] ?? '') . '</td></tr>'
                        . '</table>'
                        . '<p>Por favor entrega manualmente el RIDE/XML al cliente o solicita su correo electronico.</p>';
                    enviarAlertaAdmin('CLIENTE_SIN_CORREO', 'Cliente sin correo - Factura ' . ($dataInfo['factura'] ?? ''), $cuerpo);
                } catch (\Throwable $e) { error_log('Alerta sin correo fallo: ' . $e->getMessage()); }
            }
            try { sendEmail($dataInfo, 'email_facturaelectronica','ventas'); } catch (\Throwable $e) { error_log('Email factura fallo: '.$e->getMessage()); }
        }


        if (ob_get_level()) { @ob_end_clean(); }
        header('Content-Type: application/json; charset=utf-8');
        echo json_encode($res, JSON_UNESCAPED_UNICODE);
        die();
    }

    function generate_numbers($start, $count, $digits)
    {
        $result = array();
        for ($n = $start; $n < $start + $count; $n++) {
            $result[] = str_pad($n, $digits, "0", STR_PAD_LEFT);
        }
        return $result;
    }

    /**
     * Reenvio masivo del correo (RIDE+XML) a clientes con correo_enviado=0.
     * Procesa en lote pequeno para no chocar con timeout del proxy.
     * Acepta ?limite=20 (default 20, max 100). Loop desde el frontend.
     */
    public function reenviarCorreoMasivo()
    {
        $this->cargarSri();
        @set_time_limit(0);
        @ini_set('memory_limit', '512M');
        $limite = isset($_GET['limite']) ? (int)$_GET['limite'] : 20;
        $pendientes = $this->model->getCorreosPendientes($limite);
        $empresa = $this->model->getEmpresa();

        $ok = 0;
        $fallidos = [];

        foreach ($pendientes as $row) {
            try {
                $claveAcceso = $row['claveacceso'] ?? '';
                $pdfPath = 'facturaelectronica/public/archivos/ride/' . $claveAcceso . '.pdf';
                $xmlPath = 'facturaelectronica/public/archivos/autorizados/' . $claveAcceso . '.xml';
                if (!file_exists($pdfPath) || !file_exists($xmlPath)) {
                    $fallidos[] = ['orden_no' => $row['orden_no'], 'cliente' => $row['cliente'], 'error' => 'Archivos RIDE/XML no encontrados'];
                    // Marcar como 2 (=archivos perdidos, no se puede reenviar) para no reintentar
                    try {
                        $pdoMark = new \PDO('mysql:host=' . HOSTT . ';dbname=' . DBNAME . ';charset=utf8mb4', USER, PASSWORD);
                        $pdoMark->prepare('UPDATE datos_cabecera_electronica SET correo_enviado=2 WHERE orden_no=?')->execute([$row['orden_no']]);
                    } catch (\Throwable $e) {}
                    continue;
                }
                $dataInfo = [
                    'ruc'             => $row['ruc'],
                    'email'           => $row['correo'],
                    'fecha'           => $row['fecha'],
                    'totalfactura'    => $row['totalfactura'],
                    'cliente'         => $row['cliente'],
                    'claveAcceso'     => $claveAcceso,
                    'empresa'         => $empresa['nombre'],
                    'factura'         => str_pad($row['orden_no'], 9, '0', STR_PAD_LEFT),
                    'enviroment'      => ENVIROMENT,
                    'emailremitente'  => $empresa['correo'],
                    'establecimiento' => $row['establecimiento'],
                    'puntoemi'        => $row['punto_emi'],
                    'tipo'            => 'factura',
                    'asunto'          => 'Adjuntamos Comprobante Electronico',
                ];
                $res = sendEmail($dataInfo, 'email_facturaelectronica', 'ventas');
                if ($res === true) {
                    $this->model->marcarCorreoEnviado($row['orden_no']);
                    $ok++;
                } else {
                    $fallidos[] = ['orden_no' => $row['orden_no'], 'cliente' => $row['cliente'], 'error' => 'sendEmail retorno false'];
                }
            } catch (\Throwable $e) {
                $fallidos[] = ['orden_no' => $row['orden_no'] ?? '?', 'cliente' => $row['cliente'] ?? '', 'error' => $e->getMessage()];
            }
        }

        header('Content-Type: application/json');
        echo json_encode([
            'procesadas' => $ok,
            'fallidas'   => count($fallidos),
            'fallidos'   => $fallidos,
            'lote'       => count($pendientes),
        ], JSON_UNESCAPED_UNICODE);
        die();
    }

    /** Cuenta facturas con correo pendiente de envio (para badge en boton). */
    public function contarPendientesCorreo()
    {
        $pdo = new \PDO('mysql:host=' . HOSTT . ';dbname=' . DBNAME . ';charset=utf8mb4', USER, PASSWORD);
        $stmt = $pdo->query("SELECT COUNT(*) FROM datos_cabecera_electronica dce
            INNER JOIN respuesta_sri rs ON rs.claveAcceso = dce.claveacceso
            WHERE rs.estado='AUTORIZADO' AND dce.correo_enviado=0 AND dce.correo IS NOT NULL AND dce.correo!=''
              AND dce.correo REGEXP '^[A-Za-z0-9._%+-]+@[A-Za-z0-9.-]+\.[A-Za-z]{2,}$'");
        $count = (int)$stmt->fetchColumn();
        header('Content-Type: application/json');
        echo json_encode(['pendientes' => $count]);
        die();
    }
}

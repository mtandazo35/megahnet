<?php
require 'vendor/autoload.php';
use Mike42\Escpos\Printer;
use Mike42\Escpos\EscposImage;

use Dompdf\Dompdf;

class Retenciones extends Controller
{
    private function cargarSri() { static $loaded=false; if (!$loaded) { include_once __DIR__ . '/../config/ServicesSri.php'; $loaded=true; } }


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

        $data['title'] = 'Retenciones';
        $data['script'] = 'retenciones.js';
        $data['busqueda'] = 'busqueda.js';
        $data['carrito'] = 'posRetencion';
        $data['empresa'] =  $this->model->getEmpresa();
        //$data['cantidadDocumento'] =  $this->model->cantidadDocumento(date('Y-m'));

     



        $resultSerieRetenciones = $this->model->getSerieRetenciones();
        $serieRetenciones = ($resultSerieRetenciones['total'] == null) ? 1 : $resultSerieRetenciones['total'] + 1; // factura electronica
        $data['serieRetenciones'] = $this->generate_numbers($serieRetenciones, 1, 9);
        $this->views->getView('retenciones', 'index', $data);
    }




    public function registrarRetencion()
    {
        $this->cargarSri();
        $res = array('msg' => 'ERROR DESCONOCIDO', 'type' => 'error');
        $json = file_get_contents('php://input');
        $datos = json_decode($json, true);
        $array['retenciones'] = array();
        $empresa = $this->model->getEmpresa();
        $total = 0;
        if (!empty($datos['retenciones'])) {
            $fecha = date('Y-m-d');
            $hora = date('H:i:s');
            $periodoFiscal= date('m/Y');
            $fechaEmision = $datos['fechaEmision'];
            $numeroComprobante = $datos['numeroComprobante'];
            $totalFactura = $datos['totalFactura'];
            //$baseImponible = $datos['retenciones']['baseImponible'];
            //print_r(  $this->id_usuario);exit;
           // print_r($datos);       exit;

            $resultSerieRetenciones = $this->model->getSerieRetenciones();
            $numSerieRetenciones = ($resultSerieRetenciones['total'] == null) ? 1 : $resultSerieRetenciones['total'] + 1; // factura electronica
             $serieRetenciones = $this->generate_numbers($numSerieRetenciones, 1, 9);
            //print_r($serieRetenciones);exit;
            $idProveedor = $datos['idProveedor'];
            $datosProveedor = $this->model->getProveedor($idProveedor);
            $valorRetenido=0;
            $totalRetenido=0;
            if (strlen($datosProveedor['ruc']) == 13) {
                $tipoIdentificacion = 4;
            } else {
                $tipoIdentificacion = 5;
            }

            if(strlen($numeroComprobante) < 15 || strlen($numeroComprobante) > 15){
                $res = array('msg' => 'EL NUMERO DE COMPROBANTE DEBE SER IGUAL AH 15', 'type' => 'warning');

            }else if (strlen($fechaEmision) < 10 || strlen($fechaEmision) < 10) {
                $res = array('msg' => 'LA FECHA ES INCORRECTA, SE ADMITE 10 LETRAS', 'type' => 'warning');
            }
            else if (empty($idProveedor)) {
                $res = array('msg' => 'EL PROVEEDOR ES REQUERIDO', 'type' => 'warning');
            } else if (empty($fechaEmision)) {
                $res = array('msg' => 'LA FECHA EMISION ES REQUERIDO', 'type' => 'warning');
            } else if (empty($totalFactura)) {
                $res = array('msg' => 'EL TOTAL FACTURA ES REQUERIDO', 'type' => 'warning');
            } 
            else {                                   
                        //print_r($totalRetenido);
                                               $ventaEncabezado = $this->model->registrarEncabezado(
                                $fecha,
                                $datosProveedor['nombre'],
                                $datosProveedor['direccion'],
                                $datosProveedor['telefono'],
                                $datosProveedor['ruc'],
                                $tipoIdentificacion,
                                $datosProveedor['correo'],
                                $empresa['establecimiento'],
                                PTOEMISIONRETENCION,
                                $empresa['ruc'],
                                AMBIENTE,
                                $empresa['razon_social'],
                                $empresa['nombre'],
                                1,
                                $serieRetenciones[0],
                                $empresa['direccion'],
                                $empresa['contabilidad'],
                                $fechaEmision,   
                                $numeroComprobante,                 
                                $periodoFiscal,
                                $totalFactura,                                      
                                $this->id_usuario
                            );
                            if ($ventaEncabezado > 0) {

                                foreach ($datos['retenciones'] as $totalRetenciones) {
                                    $result = $this->model->getRetencion($totalRetenciones['id']);
                                   // print_r($result); exit;
                                $valorRetenido = number_format(($totalRetenciones['baseImponible'] * $result['porcentajeretencion'])/100,2,'.',',');
                                  
                                $totalRetenido +=  $valorRetenido;

                                $ventaDetalle =  $this->model->registrarDetalle($totalRetenciones['baseImponible'], $result['tipo'], $result['porcentajeretencion'], $valorRetenido,$result['codigo'],$ventaEncabezado);

                                }

                                if ($ventaDetalle > 0) {
                                   
                                    //print_r($ventaDetalle);  exit;                  
                                              /*if ($datos['impresion']) {
                                        $this->impresionDirecta($ventaDetalle);
                                    }*/
                                    // $result_respuesta=array();

                                    // SRI try/catch: aunque falle el envio externo, devolvemos JSON
                                    try {
                                        // SRI try/catch: tolerante a fallos SOAP/SRI
                                        try {
                                            $enviarXML = new enviarXML();
                                            $claveAcceso = $enviarXML->envioXML($numSerieRetenciones, RETENCION);
                                            $validacionComprobante = new validacionComprobante();
                                            $validacion = $validacionComprobante->validar_comprobante($claveAcceso, RETENCION);
                                            $autorizacionComprobante = new autorizacionComprobante();
                                            $autorizacion = $autorizacionComprobante->autorizacion_comprobante($claveAcceso, RETENCION);
                                        } catch (\Throwable $e) {
                                            error_log('SRI fallo en controllers/Retenciones.php: ' . $e->getMessage());
                                            $autorizacion = ['numeroComprobantes' => 1, 'autorizaciones' => ['autorizacion' => ['estado' => 'NO_AUTORIZADO']]];
                                            $claveAcceso = $claveAcceso ?? '';
                                        }
                                        $this->model->actualizarClaveAccesso($claveAcceso, $numSerieRetenciones);
                                    } catch (\Throwable $e) {
                                        error_log('SRI retencion fallo: ' . $e->getMessage());
                                        $autorizacion = ['autorizaciones' => ['autorizacion' => ['estado' => 'NO_AUTORIZADO']]];
                                        $claveAcceso = '';
                                    }

                                    if (isset($autorizacion['autorizaciones']['autorizacion']['estado']) && $autorizacion['autorizaciones']['autorizacion']['estado'] == 'AUTORIZADO') {

                                        $retencionElectronica = $this->model->getRetencionElectronicaCA($claveAcceso);
                                        // $serieElectronica = $this->generate_numbers($idRetencion, 1, 9);
                             
                                         $dataInfo = array(
                                             'ruc' => $retencionElectronica['ruc'],
                                             'email' => $retencionElectronica['correo'],
                                             'fecha' => $retencionElectronica['fecha'],
                                             'totalfactura' => $retencionElectronica['totalFactura'],
                                             'proveedor' => $retencionElectronica['cliente'],
                                             'claveAcceso' => $claveAcceso,
                                             'empresa' => $empresa['nombre'],
                                             'retencion' => $retencionElectronica['secuencial'],
                                             'enviroment' => ENVIROMENT,
                                             'emailremitente' => $empresa['correo'],
                                             'establecimiento' => $empresa['establecimiento'],
                                             'puntoemi' => PTOEMISIONRETENCION,
                                             'tipo' => 'retencion',

                                             'asunto' => 'Adjuntamos Comprobante Electronico'
                                         );
                                        $res = array('msg' => 'RETENCION EMITIDA EXITOSAMENTE', 'type' => 'success', 'ClaveAcceso' => $claveAcceso, 'idRetencion' => $numSerieRetenciones);
                                       try { sendEmail($dataInfo, 'email_retencionelectronica','retenciones'); } catch (\Throwable $e) { error_log('Email retencion fallo: ' . $e->getMessage()); }
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
                                    //print_r($dataInfo); exit;



                                } else {
                                    $res = array('msg' => 'ERROR AL GENERAR LA RETENCION ELECTRONICA DETALLE ', 'type' => 'error');
                                }
                            } else {
                                $res = array('msg' => 'ERROR AL GENERAR RETENCION ELECTRONICA ENCABEZADO', 'type' => 'error');
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

    

    
    public function listarRetenciones()
    {
        $data = $this->model->getRetencionesElectronica();
        for ($i = 0; $i < count($data); $i++) {
            if ($data[$i]['autorizacion'] == 'AUTORIZADO' && $data[$i]['estado'] == 1) {
                $data[$i]['acciones'] = '<div>
                <a class="btn btn-danger" href="' . BASE_URL . 'facturaelectronica/public/archivos/Retenciones/ride/' . $data[$i]['claveAcceso'] . '.pdf' . '" target="_blank" title="FACTURA"><i class="fas fa-file-pdf"></i></a>
                <a class="btn btn-success" href="#" onclick="envioCorreoRetencion(' . $data[$i]['id'] . ')"><i class="fa-solid fa-envelope"></i></a>
                </div>';
            } else if ($data[$i]['autorizacion'] == 'AUTORIZADO') {
                $data[$i]['acciones'] = '<div>
                <a class="btn btn-warning" href="#" onclick="envioSriRetencion(' . $data[$i]['id'] . ')"><i class="fa-solid fa-paper-plane text-white"></i></a>
                </div>';
            } else if ($data[$i]['autorizacion'] == 'DEVUELTA') {
                $data[$i]['acciones'] = '<div>
                <a class="btn btn-warning" href="#" onclick="envioSriRetencion(' . $data[$i]['id'] . ')"><i class="fa-solid fa-paper-plane text-white"></i></a>
                </div>';
            } else if ($data[$i]['autorizacion'] == 'EN PROCESO') {
                $data[$i]['acciones'] = '<div>
                <a class="btn btn-warning" href="#" onclick="envioSriRetencion(' . $data[$i]['id'] . ')"><i class="fa-solid fa-paper-plane text-white"></i></a>
                </div>';
            } else {
                $data[$i]['acciones'] = '<div>
                <a class="btn btn-warning" href="#" onclick="envioSriRetencion(' . $data[$i]['id'] . ')"><i class="fa-solid fa-paper-plane text-white"></i></a>

                </div>';
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
                $data[$i]['estado'] = '<div style="text-align: center;"><span class="badge bg-success">PROCESADA</span></div>';
            } else {
                $data[$i]['estado'] = '<div style="text-align: center;"><span class="badge bg-danger">ANULADA</span></div>';
            }

            $data[$i]['retencion'] = $this->generate_numbers($data[$i]['id'], 1, 9);
        }
        echo json_encode($data, JSON_UNESCAPED_UNICODE);
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


    //REENVIO DE FACTURA ELECTRONICA
    public function envioRetencionElectronica($idRetencion)
    {
        $this->cargarSri();
        $empresa = $this->model->getEmpresa();

        $retencionElectronica = $this->model->getRetencionElectronica($idRetencion);
       // print_r($retencionElectronica); exit;

        $claveAcceso = $retencionElectronica['0']['claveAcceso'];
        $filePDF = 'facturaelectronica/public/archivos/Retenciones/ride/' . $claveAcceso . '.pdf';
        $fileXML = 'facturaelectronica/public/archivos/Retenciones/autorizados/' . $claveAcceso . '.xml';

        if (file_exists($filePDF) && file_exists($fileXML)) {
           // $serieElectronica = $this->generate_numbers($retencionElectronica['0']['id'], 1, 9);

            $dataInfo = array(
                'ruc' => $retencionElectronica['0']['ruc'],
                'email' => $retencionElectronica['0']['correo'],
                'fecha' => $retencionElectronica['0']['fecha'],
                'totalfactura' => $retencionElectronica['0']['totalFactura'],
                'proveedor' => $retencionElectronica['0']['cliente'],
                'claveAcceso' => $claveAcceso,
                'empresa' => $empresa['nombre'],
                'retencion' => $retencionElectronica['0']['secuencial'],
                'enviroment' => ENVIROMENT,
                'emailremitente' => $empresa['correo'],
                'establecimiento' => $empresa['establecimiento'],
                'puntoemi' => PTOEMISIONRETENCION,
                'tipo' => 'retencion',
                'asunto' => 'Adjuntamos Comprobante Electronico'
            );
            $res = array('msg' => 'RETENCION ELECTRONICA ENVIADA EXITOSAMENTE', 'type' => 'success');
            sendEmail($dataInfo, 'email_retencionelectronica','retenciones');
        } else {
            $res = array('msg' => 'ERROR AL ENVIAR RETENCION ELECTRONICA, EL RIDE Y EL XML NO EXISTEN', 'type' => 'error');
        }


        echo json_encode($res);
        die();
    }

    //REENVIO DE FACTURA ELECTRONICA AL SRI
    public function envioSriElectronica($idRetencion)
    {
        $this->cargarSri();
        $empresa = $this->model->getEmpresa();

        $retencionElectronica = $this->model->getRetencionElectronica($idRetencion);
        //$claveAcceso= $facturaElectronica['0']['claveacceso'];


        // SRI try/catch: tolerante a fallos SOAP/SRI
        try {
            $enviarXML = new enviarXML();
            $claveAcceso = $enviarXML->envioXML($idRetencion, RETENCION);
            $validacionComprobante = new validacionComprobante();
            $validacion = $validacionComprobante->validar_comprobante($claveAcceso, RETENCION);
            $autorizacionComprobante = new autorizacionComprobante();
            $autorizacion = $autorizacionComprobante->autorizacion_comprobante($claveAcceso, RETENCION);
        } catch (\Throwable $e) {
            error_log('SRI fallo en controllers/Retenciones.php: ' . $e->getMessage());
            $autorizacion = ['numeroComprobantes' => 1, 'autorizaciones' => ['autorizacion' => ['estado' => 'NO_AUTORIZADO']]];
            $claveAcceso = $claveAcceso ?? '';
        }
        //  print_r($autorizacion['autorizaciones']['autorizacion']['estado']); exit;
        // $data['claveAcceso'] = $claveAcceso;
        $this->model->actualizarClaveAccesso($claveAcceso, $idRetencion);

        //$result = mysqli_num_rows($update_clave);
        if ($autorizacion['autorizaciones']['autorizacion']['estado'] == 'AUTORIZADO') {

            $retencionElectronica = $this->model->getRetencionElectronicaCA($claveAcceso);
           // $serieElectronica = $this->generate_numbers($idRetencion, 1, 9);

            $dataInfo = array(
                'ruc' => $retencionElectronica['ruc'],
                'email' => $retencionElectronica['correo'],
                'fecha' => $retencionElectronica['fecha'],
                'totalfactura' => $retencionElectronica['totalFactura'],
                'proveedor' => $retencionElectronica['cliente'],
                'claveAcceso' => $claveAcceso,
                'empresa' => $empresa['nombre'],
                'retencion' => $retencionElectronica['secuencial'],
                'enviroment' => ENVIROMENT,
                'emailremitente' => $empresa['correo'],
                'establecimiento' => $empresa['establecimiento'],
                'puntoemi' => PTOEMISIONRETENCION,
                'tipo' => 'retencion',
                'asunto' => 'Adjuntamos Comprobante Electronico'
            );
            $res = array('msg' => 'RETENCION ELECTRONICA GENERADA EXITOSAMENTE REENVIO AL SRI', 'type' => 'success');
            sendEmail($dataInfo, 'email_retencionelectronica','retenciones');
        } else {
            $res = array('msg' => 'ERROR EN LA RETENCION REENVIO SRI, CONTACTE CON SOPORTE', 'type' => 'error');
        }


        echo json_encode($res, JSON_UNESCAPED_UNICODE);
        die();
    }


    public function buscarPorComprobante()
    {
        header('Content-Type: application/json; charset=utf-8');
        if (empty($_SESSION['id_usuario'])) { echo json_encode(['ok'=>false]); exit; }
        $raw   = trim((string)($_GET['numero'] ?? ''));
        $clean = preg_replace('/[^0-9]/', '', $raw);
        if ($clean === '') { echo json_encode(['ok'=>false, 'msg'=>'sin numero']); exit; }

        // Variantes para tolerar formatos: 15 digitos (3+3+9), solo secuencial 9, solo 8, ltrim ceros
        $variants = [$raw, $clean];
        if (strlen($clean) >= 9) {
            $variants[] = substr($clean, -9);
            $variants[] = ltrim(substr($clean, -9), '0');
        }
        if (strlen($clean) >= 8) $variants[] = substr($clean, -8);
        $variants = array_values(array_unique(array_filter($variants, function($v){ return $v !== ''; })));

        $compra = $this->model->buscarCompraPorComprobante($variants, $clean);
        if (empty($compra)) { echo json_encode(['ok'=>false, 'msg'=>'compra no encontrada']); exit; }

        // Recalcular total real desde JSON de productos INCLUYENDO IVA por producto.
        // El precio en el JSON se guarda sin IVA; aqui sumamos sub * (1+iva/100) para que
        // el "Valor Factura" coincida con el total real (con IVA) del comprobante.
        $totalReal = 0;
        $prods = !empty($compra['productos']) ? json_decode($compra['productos'], true) : [];
        if (is_array($prods)) {
            foreach ($prods as $p) {
                $cant = (float)($p['cantidad'] ?? 0);
                $prec = (float)($p['precio']   ?? 0);
                $ivaP = (float)($p['iva_producto'] ?? 0);
                $sub  = $cant * $prec;
                $totalReal += ($ivaP > 0) ? round($sub * (1 + $ivaP / 100), 2) : $sub;
            }
        }
        if ($totalReal <= 0) $totalReal = (float)$compra['total'];

        echo json_encode([
            'ok'              => true,
            'idProveedor'     => $compra['id_proveedor'],
            'nombreProveedor' => $compra['nombre'],
            'telefono'        => $compra['telefono'] ?? '',
            'correo'          => $compra['correo'] ?? '',
            'direccion'       => $compra['direccion'] ?? '',
            'ruc'             => $compra['ruc'] ?? '',
            'fechaEmision'    => date('d/m/Y', strtotime($compra['fecha'])),
            'totalFactura'    => number_format($totalReal, 2, '.', ''),
            'serieCompra'     => $compra['serie'],
            'idCompra'        => $compra['id'],
        ], JSON_UNESCAPED_UNICODE);
        exit;
    }


    // ====== CRUD de codigos de retencion ======
    public function listarCodigos()
    {
        header('Content-Type: application/json; charset=utf-8');
        if (empty($_SESSION['id_usuario'])) { echo json_encode([]); exit; }
        echo json_encode($this->model->listarCodigosRetencion(), JSON_UNESCAPED_UNICODE);
        exit;
    }

    public function guardarCodigo()
    {
        header('Content-Type: application/json; charset=utf-8');
        if (empty($_SESSION['id_usuario'])) { echo json_encode(['type'=>'error','msg'=>'Sesion expirada']); exit; }
        $body = json_decode(file_get_contents('php://input'), true) ?: $_POST;
        $id          = (int)($body['id'] ?? 0);
        $tipo        = trim((string)($body['tipo'] ?? ''));
        $codigo      = trim((string)($body['codigo'] ?? ''));
        $porcentaje  = trim((string)($body['porcentaje'] ?? ''));
        $descripcion = trim((string)($body['descripcion'] ?? ''));
        $estado      = (int)($body['estado'] ?? 1);

        if ($tipo === '' || $codigo === '' || $porcentaje === '') {
            echo json_encode(['type'=>'warning','msg'=>'Tipo, codigo y porcentaje son requeridos']);
            exit;
        }
        // Validar porcentaje numerico (acepta decimales)
        if (!is_numeric(str_replace(',', '.', $porcentaje))) {
            echo json_encode(['type'=>'warning','msg'=>'El porcentaje debe ser numerico']);
            exit;
        }

        if ($id > 0) {
            $ok = $this->model->actualizarCodigoRetencion($id, $tipo, $codigo, $porcentaje, $descripcion, $estado);
            echo json_encode($ok ? ['type'=>'success','msg'=>'Codigo actualizado','id'=>$id]
                                 : ['type'=>'error','msg'=>'No se pudo actualizar']);
        } else {
            $newId = $this->model->insertarCodigoRetencion($tipo, $codigo, $porcentaje, $descripcion);
            echo json_encode($newId ? ['type'=>'success','msg'=>'Codigo creado','id'=>(int)$newId]
                                    : ['type'=>'error','msg'=>'No se pudo crear']);
        }
        exit;
    }

    public function eliminarCodigo($id = 0)
    {
        header('Content-Type: application/json; charset=utf-8');
        if (empty($_SESSION['id_usuario'])) { echo json_encode(['type'=>'error']); exit; }
        $ok = $this->model->eliminarCodigoRetencion((int)$id);
        echo json_encode($ok ? ['type'=>'success','msg'=>'Codigo desactivado']
                             : ['type'=>'error','msg'=>'No se pudo eliminar']);
        exit;
    }

    function generate_numbers($start, $count, $digits)
    {
        $result = array();
        for ($n = $start; $n < $start + $count; $n++) {
            $result[] = str_pad($n, $digits, "0", STR_PAD_LEFT);
        }
        return $result;
    }
}

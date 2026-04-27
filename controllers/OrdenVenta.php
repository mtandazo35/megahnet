<?php
require 'vendor/autoload.php';
use PhpOffice\PhpSpreadsheet\IOFactory;

use Dompdf\Dompdf;

class OrdenVenta extends Controller
{
    private function cargarSri() { static $loaded=false; if (!$loaded) { include_once __DIR__ . '/../config/ServicesSri.php'; $loaded=true; } }

    private $id_usuario;

    public function __construct()
    {
        parent::__construct();
        session_start();
        if (empty($_SESSION['id_usuario'])) {
            header('Location: ' . BASE_URL);
            exit;
        }
        $this->id_usuario = $_SESSION['id_usuario'];
    }
    public function index()
    {
        $data['title'] = 'Orden Venta';
        $data['script'] = 'ordenventa.js';
        $data['busqueda'] = 'busqueda.js';
        $data['carrito'] = 'posOrdenVenta';
        $data['tipoPago'] =  $this->model->TipoPago(1);

        $this->views->getView('ordenventa', 'index', $data);
    }

    public function registrarExcel()
    {

        //  print_r($_FILES['excel']); exit;
        $datosExcel = $_FILES['excel'];
        if ($datosExcel['size'] > 0) {
            //    $direccion = strClean($_POST['direccion']);
            //   print_r($chelectronica); exit;
            if ($datosExcel['type'] != 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet') {
                $res = array('msg' => 'SELECCIONES UN ARCHIVO EXCEL', 'type' => 'error');
            } else {

                $archivoContent = $datosExcel['tmp_name'];
                importarExcel($archivoContent);
            }
        } else {
            $res = array('msg' => 'ERROR DESCONOCIDO', 'type' => 'error');
            echo json_encode($res, JSON_UNESCAPED_UNICODE);
        }
        die();
    }

    public function registrarOrdenVenta()
    {
        $json = file_get_contents('php://input');
        $datos = json_decode($json, true);
        $array['productos'] = array();
        $total = 0;
        $empresa = $this->model->getEmpresa();

        $verifcarCaja = $this->model->getCaja($this->id_usuario);
        if (empty($verifcarCaja['monto_inicial'])) {
            $res = array('msg' => 'LA CAJA ESTA CERRADA', 'type' => 'warning');
        } else {



        // print_r($datos); exit;
        if (!empty($datos['productos'])) {



            $resultSerie = $this->model->getSerie();
            $numSerie = ($resultSerie['total'] == null) ? 1 : $resultSerie['total'] + 1;
            $serie = $this->generate_numbers($numSerie, 1, 9);



            $fecha = date('Y-m-d');
            $hora = date('H:i:s');
            $metodo = $datos['metodo'];
            $descuento = (!empty($datos['descuento'])) ? $datos['descuento'] : 0;
            $estado = ($datos['metodo'] == 'CREDITO') ? 2 : 1;
            $tipoPago = $datos['tipoPago'];

            $idCliente = $datos['idCliente'];
            if (empty($idCliente)) {
                $res = array('msg' => 'EL CLIENTE ES REQUERIDO', 'type' => 'warning');
            } else if (empty($metodo)) {
                $res = array('msg' => 'EL METODO ES REQUERIDO', 'type' => 'warning');
            } else {
                foreach ($datos['productos'] as $producto) {
                    $result = $this->model->getProducto($producto['id']);
                    $data['id'] = $result['id'];
                    $data['nombre'] = $producto['nombre'];
                    $data['precio'] = $producto['precio'];
                    $data['cantidad'] = $producto['cantidad'];
                    $data['iva_producto'] = $result['iva'];

                    $subTotal = $producto['precio'] * $producto['cantidad'];
                    array_push($array['productos'], $data);
                    $total += $subTotal;
                }
                $datosProductos = json_encode($array['productos']);
                $ordenventa = $this->model->registrarOrdenVenta($datosProductos, $total, $fecha, $hora, $metodo, $descuento, $serie[0], $estado, $idCliente, $this->id_usuario,$tipoPago);
                if ($ordenventa > 0) {
                    foreach ($datos['productos'] as $producto) {
                        $result = $this->model->getProducto($producto['id']);
                        //actualizar stock

                        if ($result['id_categoria'] == 1) {
                            $nuevaCantidad = $result['cantidad'];
                            $totalOdenVentas = $result['ventas'] + $producto['cantidad'];
                            $this->model->actualizarStock($nuevaCantidad, $totalOdenVentas, $result['id']);
                        } else {
                            $nuevaCantidad = $result['cantidad'] - $producto['cantidad'];
                            $totalOdenVentas = $result['ventas'] + $producto['cantidad'];
                            $this->model->actualizarStock($nuevaCantidad, $totalOdenVentas, $result['id']);
                        }



                        $movimiento = 'Orden Venta N°: ' . $ordenventa;
                        $cantidad = $producto['cantidad'];
                        $this->model->registrarMovimiento($movimiento, 'salida', $cantidad, $nuevaCantidad, $producto['id'], $this->id_usuario);
                    }
                    if ($metodo == 'CREDITO') {
                        $monto = $total - $descuento;
                        $this->model->registrarCredito($monto, $fecha, $hora, null, null, $ordenventa);
                    }
                    // La orden ya esta persistida; pase lo que pase con PDF/email,
                    // la respuesta debe reportar exito.
                    $res = array('msg' => 'ORDEN VENTA EMITIDA EXITOSAMENTE', 'type' => 'success', 'idOrdenVenta' => $ordenventa);

                    // PDF y email diferidos a post-response (ver bloque al final)
                    $deferOrdenId = $ordenventa;
                    $deferEmpresaNombre = $empresa['nombre'];
                    $deferEmpresaCorreo = $empresa['correo'];
                    $deferEstablecimiento = $empresa['establecimiento'];
                    $deferPuntoemi = $empresa['puntoemi'];

                } else {
                    $res = array('msg' => 'ERROR AL GENERAR ORDEN VENTA', 'type' => 'error');
                }
            }
        } else {
            $res = array('msg' => 'CARRITO VACIO', 'type' => 'warning');
        }

    }
        if (ob_get_level()) { @ob_end_clean(); }
        header('Content-Type: application/json; charset=utf-8');
        $jsonOut = json_encode($res);
        // Enviar respuesta YA al cliente y continuar tareas pesadas en background
        header('Content-Length: ' . strlen($jsonOut));
        echo $jsonOut;
        if (function_exists('fastcgi_finish_request')) {
            @fastcgi_finish_request();
        } elseif (function_exists('flush')) {
            @flush();
        }
        // Tareas diferidas: solo si la orden se creo bien
        if (!empty($deferOrdenId)) {
            try { $this->ordenVentaPDF('facturas', $deferOrdenId); } catch (\Throwable $e) { error_log('PDF orden falla: ' . $e->getMessage()); }
            try {
                $getordenVenta = $this->model->getOrdenVenta($deferOrdenId);
                if ($getordenVenta) {
                    $dataInfo = array(
                        'ruc'             => $getordenVenta['num_identidad'],
                        'email'           => $getordenVenta['correo'],
                        'fecha'           => $getordenVenta['fecha'],
                        'totalfactura'    => $getordenVenta['total'],
                        'cliente'         => $getordenVenta['nombre'],
                        'empresa'         => $deferEmpresaNombre,
                        'factura'         => $deferOrdenId,
                        'enviroment'      => ENVIROMENT,
                        'emailremitente'  => $deferEmpresaCorreo,
                        'establecimiento' => $deferEstablecimiento,
                        'puntoemi'        => $deferPuntoemi,
                        'tipo'            => 'orden',
                        'asunto'          => 'Adjuntamos Comprobante'
                    );
                    sendEmailOrden($dataInfo, 'email_facturaelectronica');
                }
            } catch (\Throwable $e) { error_log('Email orden falla: ' . $e->getMessage()); }
        }
        die();
    }

    public function reporte($datos)
    {
        $this->cargarSri();
        ob_start();
        $array = explode(',', $datos);
        $tipo = $array[0];
        $idOrdenVenta = $array[1];

        $data['title'] = 'Orden Venta';
        $data['empresa'] = $this->model->getEmpresa();
        $data['ordenventa'] = $this->model->getOrdenVenta($idOrdenVenta);
        //print_r( $data['cotizacion']); exit;
        if (empty($data['ordenventa'])) {
            echo 'Pagina no Encontrada';
            exit;
        }
        $this->views->getView('ordenventa', $tipo, $data);
        $html = ob_get_clean();
        $dompdf = new Dompdf();
        $options = $dompdf->getOptions();
        $options->set('isJavascriptEnabled', true);
        $options->set('isRemoteEnabled', true);
        $dompdf->setOptions($options);
        $dompdf->loadHtml($html);

        if ($tipo == 'ticked') {
            $dompdf->setPaper(array(0, 0, 225, 500), 'portrait');
        } else {
            $dompdf->setPaper('A4', 'vertical');
        }

        // Render the HTML as PDF
        $dompdf->render();

        // Output the generated PDF to Browser
        $dompdf->stream('OrdenVenta_' . $idOrdenVenta . '.pdf', array('Attachment' => false));
    }


    public function ordenVentaPDF($tipo, $idOrdenVenta)
    {
        $this->cargarSri();
        ob_start();
        // $array = explode(',', $datos);
        //$tipo = $array[0];
        //$idOrdenVenta = $array[1];
        $rutaGuardado = 'facturaelectronica/public/archivos/facturables/';

        $nombreArchivo = 'Facturable' . '_' . $idOrdenVenta . '.pdf';

        $data['title'] = 'Orden Venta';
        $data['empresa'] = $this->model->getEmpresa();
        $data['ordenventa'] = $this->model->getOrdenVenta($idOrdenVenta);
        //print_r( $data['cotizacion']); exit;
        if (empty($data['ordenventa'])) {
            echo 'Pagina no Encontrada';
            exit;
        }
        $this->views->getView('automaticas', $tipo, $data);
        $html = ob_get_clean();
        $dompdf = new Dompdf();
        $options = $dompdf->getOptions();
        $options->set('isJavascriptEnabled', true);
        $options->set('isRemoteEnabled', true);
        $dompdf->setOptions($options);
        $dompdf->loadHtml($html);

        if ($tipo == 'ticked') {
            $dompdf->setPaper(array(0, 0, 225, 500), 'portrait');
        } else {
            $dompdf->setPaper('A4', 'vertical');
        }



        $dompdf->render();
        $output = $dompdf->output();
        file_put_contents($rutaGuardado . $nombreArchivo, $output);
        // Output the generated PDF to Browser
        //$dompdf->stream('Facturable' . $idOrdenVenta . '.pdf', array('Attachment' => false));
    }

    public function enviarPorWhatsApp($idOrden = 0)
    {
        header('Content-Type: application/json; charset=utf-8');
        if (empty($_SESSION['id_usuario'])) { echo json_encode(['ok'=>false,'msg'=>'No autorizado']); exit; }
        $idOrden = (int)$idOrden;
        if ($idOrden <= 0) { echo json_encode(['ok'=>false,'msg'=>'ID invalido']); exit; }

        $body = json_decode(file_get_contents('php://input'), true) ?: [];
        $tel  = preg_replace('/[^0-9]/', '', (string)($body['telefono'] ?? ''));

        // Si no se especifica, intentar tomar del cliente
        if (empty($tel)) {
            try {
                $pdo = new PDO('mysql:host=' . HOSTT . ';dbname=' . DBNAME . ';charset=utf8mb4', USER, PASSWORD,
                    [PDO::ATTR_ERRMODE => PDO::ERRMODE_SILENT]);
                $st = $pdo->prepare("SELECT cl.telefono FROM orden_venta ov INNER JOIN clientes cl ON cl.id = ov.id_cliente WHERE ov.id = ? LIMIT 1");
                $st->execute([$idOrden]);
                $r = $st->fetch(PDO::FETCH_ASSOC);
                $tel = preg_replace('/[^0-9]/', '', (string)($r['telefono'] ?? ''));
            } catch (\Throwable $e) {}
        }
        if (empty($tel)) { echo json_encode(['ok'=>false,'msg'=>'Sin telefono']); exit; }
        if (strlen($tel) === 10 && $tel[0] === '0') $tel = '593' . substr($tel, 1);
        elseif (strlen($tel) === 9) $tel = '593' . $tel;

        // Verificar PDF en disco
        $pdfPath = ROOT_PATH . '/facturaelectronica/public/archivos/facturables/Facturable_' . $idOrden . '.pdf';
        if (!file_exists($pdfPath)) {
            // Generarlo si no existe
            try { $this->ordenVentaPDF('facturas', $idOrden); } catch (\Throwable $e) {}
        }
        if (!file_exists($pdfPath)) { echo json_encode(['ok'=>false,'msg'=>'No se pudo generar el PDF']); exit; }

        // URL publica del PDF (la WA API la descarga directamente)
        $base = defined('BASE_URL') ? rtrim(BASE_URL, '/') : '';
        $pdfUrl = $base . '/facturaelectronica/public/archivos/facturables/Facturable_' . $idOrden . '.pdf';

        // WhatsApp API base + sesion
        $waBase = '';
        if (function_exists('servicioConfig')) {
            $svc = servicioConfig('whatsapp_api');
            $waBase = $svc['base_url'] ?? '';
        }
        if (empty($waBase)) {
            $f = ROOT_PATH . '/storage/alertas-config.json';
            if (file_exists($f)) {
                $j = @json_decode(@file_get_contents($f), true);
                $waBase = $j['wa_api']['base_url'] ?? '';
            }
        }
        if (empty($waBase)) $waBase = 'http://127.0.0.1:3005';
        $waBase = rtrim($waBase, '/');

        $sessionId = '';
        $f = ROOT_PATH . '/storage/alertas-config.json';
        if (file_exists($f)) {
            $j = @json_decode(@file_get_contents($f), true);
            $sessionId = $j['wa_api']['session_id'] ?? '';
        }
        if (empty($sessionId)) { echo json_encode(['ok'=>false,'msg'=>'No hay sesion WhatsApp vinculada']); exit; }

        $caption = isset($body['mensaje']) ? (string)$body['mensaje'] : ('Adjunto orden de venta #' . $idOrden);

        $payload = [
            'sessionId' => $sessionId,
            'number'    => $tel,
            'url'       => $pdfUrl,
            'fileName'  => 'OrdenVenta_' . $idOrden . '.pdf',
            'caption'   => $caption,
        ];
        $ch = curl_init($waBase . '/api/whatsapp/send-media/url');
        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_POST => true,
            CURLOPT_POSTFIELDS => json_encode($payload),
            CURLOPT_HTTPHEADER => ['Content-Type: application/json'],
            CURLOPT_TIMEOUT => 30,
            CURLOPT_CONNECTTIMEOUT => 5,
            CURLOPT_SSL_VERIFYPEER => false,
        ]);
        $resp = curl_exec($ch);
        $code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $err  = curl_error($ch);
        curl_close($ch);

        echo json_encode([
            'ok' => ($code >= 200 && $code < 300),
            'http' => $code,
            'telefono' => $tel,
            'error' => $err ?: null,
        ]);
        exit;
    }

    public function listar()
    {
        $data = $this->model->getOrdenVentas();
        for ($i = 0; $i < count($data); $i++) {
            if ($data[$i]['estado'] == 1 || $data[$i]['estado'] == 2) {
                $data[$i]['acciones'] = '<div>
                <a class="btn btn-danger" href="#" onclick="verReporte(' . $data[$i]['id'] . ')"><i class="fas fa-file-pdf"></i></a>
                <a class="btn btn-success" href="#" onclick="envioCorreoOrdenVenta(' . $data[$i]['id'] . ')"><i class="fa-solid fa-envelope"></i></a>
                <a class="btn btn-danger" href="#" onclick="anularOrdenVenta(' . $data[$i]['id'] . ')"><i class="fas fa-trash"></i></a>
                </div>';
            } else {
                $data[$i]['acciones'] = '<div>
                
                <a class="btn btn-danger" href="#" onclick="verReporte(' . $data[$i]['id'] . ')"><i class="fas fa-file-pdf"></i></a>
                </div>';
            }

            if ($data[$i]['estado'] == 1) {
                $data[$i]['estado'] = '<div style="text-align: center;"><span class="badge bg-success">PAGADA</span></div>';
            } else if ($data[$i]['estado'] == 2) {
                $data[$i]['estado'] = '<div style="text-align: center;"><span class="badge bg-warning">PENDIENTE</span></div>';
            } else {
                $data[$i]['estado'] = '<div style="text-align: center;"><span class="badge bg-danger">ANULADA</span></div>';
            }
        }
        echo json_encode($data, JSON_UNESCAPED_UNICODE);
        die();
    }
      //REENVIO DE FACTURA ELECTRONICA
      public function envioOrdenVenta($idOrden)
      {
        $this->cargarSri();
          $empresa = $this->model->getEmpresa();
  
          $ordenVenta = $this->model->getOrdenVenta($idOrden);
  
       //$idOrden = $ordenVenta['claveacceso'];
          //print_r($ordenVenta); exit;
          $filePDF = 'facturaelectronica/public/archivos/facturables/Facturable_' . $idOrden . '.pdf';
  
          if (file_exists($filePDF)) {
              $serieOrden = $this->generate_numbers($ordenVenta['id'], 1, 9);
  
              $dataInfo = array(
                  'ruc' => $ordenVenta['num_identidad'],
                  'email' => $ordenVenta['correo'],
                  'fecha' => $ordenVenta['fecha'],
                  'totalfactura' => $ordenVenta['total'],
                  'cliente' => $ordenVenta['nombre'],
                  'empresa' => $empresa['nombre'],
                  'factura' => $idOrden,
                  'enviroment' => ENVIROMENT,
                  'emailremitente' => $empresa['correo'],
                  'establecimiento' => $empresa['establecimiento'],
                  'puntoemi' => $empresa['puntoemi'],
                  'tipo' => 'orden',
                  'asunto' => 'Adjuntamos Comprobante'
              );
              $res = array('msg' => 'ORDEN VENTA ENVIADA EXITOSAMENTE', 'type' => 'success');
              sendEmailOrden($dataInfo, 'email_facturaelectronica');
          } else {
              $res = array('msg' => 'ERROR AL ENVIAR ORDEN VENTA, EL PDF NO EXISTE', 'type' => 'error');
          }
  
  
          echo json_encode($res);
          die();
      }
    public function anular($idOrdenVenta)
    {
        if (isset($_GET) && is_numeric($idOrdenVenta)) {
            $data = $this->model->anular($idOrdenVenta);
            if ($data == 1) {
                $resultOrdenVenta = $this->model->getOrdenVenta($idOrdenVenta);
                $ordenVentaProducto = json_decode($resultOrdenVenta['productos'], true);
                foreach ($ordenVentaProducto as $producto) {
                    $result = $this->model->getProducto($producto['id']);
                    $nuevaCantidad = $result['cantidad'] + $producto['cantidad'];
                    $totalOrdenVenta = $result['ventas'] - $producto['cantidad'];

                    $this->model->actualizarStock($nuevaCantidad, $totalOrdenVenta, $producto['id']);
                    //movimientos
                    $movimiento = 'Devolución OrdenVenta N°: ' . $idOrdenVenta;
                    $this->model->registrarMovimiento($movimiento, 'entrada', $producto['cantidad'], $nuevaCantidad, $producto['id'], $this->id_usuario);
                }
                if ($resultOrdenVenta['metodo'] == 'CREDITO') {
                    $this->model->anularCredito($idOrdenVenta, 'ordenVenta');
                }
                $res = array('msg' => 'ORDEN VENTA ANULADO EXITOSAMENTE', 'type' => 'success');
            } else {
                $res = array('msg' => 'ERROR AL ANULAR', 'type' => 'error');
            }
        } else {
            $res = array('msg' => 'ERROR DESCONOCIDO', 'type' => 'error');
        }
        echo json_encode($res);
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
}

function importarExcel($archivoExcel)
{
    $fecha = date('Y-m-d');
    $hora = date('H:i:s');
    $ordenVenta = new OrdenVentaModel;

    $documento = IOFactory::load($archivoExcel);

    
   $serie =0;

    $HojaExcel = $documento->getSheet(0);
    $FilaDeHojaExcel = $HojaExcel->getHighestDataRow();
    for ($fila = 2; $fila <= $FilaDeHojaExcel; $fila++) {
        $productos = $HojaExcel->getCellByColumnAndRow(1, $fila);
        $total = $HojaExcel->getCellByColumnAndRow(2, $fila);
        $metodo = $HojaExcel->getCellByColumnAndRow(3, $fila);
        $descuento = $HojaExcel->getCellByColumnAndRow(4, $fila);
        $estado = $HojaExcel->getCellByColumnAndRow(5, $fila);
        $idCliente = $HojaExcel->getCellByColumnAndRow(6, $fila);
        $idUsuario = $HojaExcel->getCellByColumnAndRow(7, $fila);



        //  echo $total.' '.$descuento. "<br>"; exit;

        $ordenventa = $ordenVenta->registrarOrdenVenta($productos, $total, $fecha, $hora, $metodo, $descuento, $serie, $estado, $idCliente, $idUsuario);
                if ($ordenventa > 0) {                       
                                    
                    if ($metodo == 'CREDITO') {
                        $monto =  $total;
                        $ordenVenta->registrarCredito($monto, $fecha, $hora, null, null, $ordenventa);
                    }
                    /* if ($datos['impresion']) {
                        $this->impresionDirecta($ordenventa);
                    }*/
                    $res = array('msg' => 'ORDEN VENTA GENERADA EXITOSAMENTE', 'type' => 'success');
                } else {
                    $res = array('msg' => 'ERROR AL GENERAR ORDEN VENTA', 'type' => 'error');
                }
        // echo json_encode($res, JSON_UNESCAPED_UNICODE);

        // echo $tipo_documento.' '.$cedula.' '.$nombre. "<br>";
    }
    echo json_encode($res, JSON_UNESCAPED_UNICODE);
}

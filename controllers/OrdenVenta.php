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
                        // Solo descontar stock y registrar movimiento si es PRODUCTO FISICO
                        // (id_categoria != 1). Los SERVICIOS (id_categoria=1) no afectan kardex.

                        if ($result['id_categoria'] == 1) {
                            // Servicio: no toca stock ni kardex, solo cuenta venta
                            $totalOdenVentas = $result['ventas'] + $producto['cantidad'];
                            $this->model->actualizarStock($result['cantidad'], $totalOdenVentas, $result['id']);
                        } else {
                            // Producto fisico: descuenta stock y registra movimiento
                            $nuevaCantidad = $result['cantidad'] - $producto['cantidad'];
                            $totalOdenVentas = $result['ventas'] + $producto['cantidad'];
                            $this->model->actualizarStock($nuevaCantidad, $totalOdenVentas, $result['id']);
                            $movimiento = 'Orden Venta N°: ' . $ordenventa;
                            $cantidad = $producto['cantidad'];
                            $this->model->registrarMovimiento($movimiento, 'salida', $cantidad, $nuevaCantidad, $producto['id'], $this->id_usuario);
                        }
                    }
                    if ($metodo == 'CREDITO') {
                        $monto = $total - $descuento;
                        // Si el cliente tiene UN solo contrato vigente, enlazar el credito a ese contrato
                        // para que el abono aparezca en "Abonos del mes" del listado de Contratos.
                        $idContratoLink = $this->model->getContratoUnicoActivo($idCliente);
                        $this->model->registrarCredito($monto, $fecha, $hora, null, null, $ordenventa, $idContratoLink);
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
        // Tareas pesadas (PDF + email + WhatsApp opcional) en background via exec
        if (!empty($deferOrdenId)) {
            $bgScript = ROOT_PATH . '/cron/orden_post_create.php';
            if (file_exists($bgScript)) {
                // Detectar si el cliente pidio enviar por WhatsApp
                $rawIn = file_get_contents('php://input');
                $payloadIn = json_decode($rawIn, true) ?: [];
                $enviarWa = !empty($payloadIn['enviar_wa']) ? '1' : '0';
                $php = '/usr/bin/php';
                $cmd = $php . ' ' . escapeshellarg($bgScript)
                     . ' ' . (int)$deferOrdenId
                     . ' ' . $enviarWa
                     . ' > /dev/null 2>&1 &';
                @exec($cmd);
            }
        }
        if (ob_get_level()) { @ob_end_clean(); }
        header('Content-Type: application/json; charset=utf-8');
        echo json_encode($res);
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

    public function listar()
    {
        // ============================================================
        // Endpoint server-side de DataTables (Historial Orden Venta).
        // Acepta los parametros estandar de DataTables (draw, start, length,
        // search[value], order[0][column], order[0][dir]) ademas de los
        // filtros propios de la vista (desde, hasta).
        // Responde con el formato estandar:
        //   { draw, recordsTotal, recordsFiltered, data: [...] }
        // ============================================================
        $src = !empty($_POST) ? $_POST : $_GET;

        // draw: DataTables exige eco numerico para anti-CSRF
        $draw = isset($src['draw']) ? (int)$src['draw'] : 0;

        // start / length con clamp
        $start  = isset($src['start'])  ? (int)$src['start']  : 0;
        $length = isset($src['length']) ? (int)$src['length'] : 10;
        if ($start  < 0)   $start  = 0;
        if ($length < 1)   $length = 10;
        if ($length > 100) $length = 100;

        // search[value]
        $search = '';
        if (isset($src['search']) && is_array($src['search']) && isset($src['search']['value'])) {
            $search = trim((string)$src['search']['value']);
        }

        // order[0][column] + order[0][dir]
        $orderColIdx = 0;
        $orderDir    = 'DESC';
        if (isset($src['order']) && is_array($src['order']) && isset($src['order'][0])) {
            if (isset($src['order'][0]['column'])) {
                $orderColIdx = (int)$src['order'][0]['column'];
            }
            if (isset($src['order'][0]['dir'])) {
                $dirRaw = strtoupper((string)$src['order'][0]['dir']);
                if ($dirRaw === 'ASC' || $dirRaw === 'DESC') $orderDir = $dirRaw;
            }
        }
        // Mapeo indice DataTables -> columna SQL (lista blanca)
        $columnasValidas = [
            0 => null,        // acciones (no ordenable)
            1 => 'cl.nombre', // nombre
            2 => 'ov.fecha',  // fecha (campo real, no el CONCAT)
            3 => 'ov.id',     // serie (es el id numerico)
            4 => 'ov.total',
            5 => 'ov.metodo',
            6 => 'ov.estado',
        ];
        $orderCol = isset($columnasValidas[$orderColIdx]) && $columnasValidas[$orderColIdx] !== null
            ? $columnasValidas[$orderColIdx]
            : 'ov.id';

        // Filtros de fecha de la vista
        $desde = isset($src['desde']) ? trim((string)$src['desde']) : null;
        $hasta = isset($src['hasta']) ? trim((string)$src['hasta']) : null;

        $result = $this->model->getOrdenVentasServerSide([
            'desde'     => $desde,
            'hasta'     => $hasta,
            'search'    => $search,
            'order_col' => $orderCol,
            'order_dir' => $orderDir,
            'start'     => $start,
            'length'    => $length,
        ]);

        $data = isset($result['rows']) && is_array($result['rows']) ? $result['rows'] : [];
        // Mantener la transformacion HTML existente (acciones, badges, fallback corrupto)
        for ($i = 0; $i < count($data); $i++) {
            // Fallback: si el nombre del cliente esta corrupto (empieza con [ o {),
            // mostrar un placeholder identificable con el id_cliente.
            $nombreActual = $data[$i]['nombre'] ?? '';
            $prim = strlen($nombreActual) > 0 ? $nombreActual[0] : '';
            if ($prim === '[' || $prim === '{') {
                $data[$i]['nombre'] = '⚠ CLIENTE CORRUPTO id=' . ($data[$i]['id_cliente'] ?? '?') . ' — revisar';
            }
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

        $response = [
            'draw'            => $draw,
            'recordsTotal'    => isset($result['total'])    ? (int)$result['total']    : 0,
            'recordsFiltered' => isset($result['filtered']) ? (int)$result['filtered'] : 0,
            'data'            => $data,
        ];
        header('Content-Type: application/json; charset=utf-8');
        echo json_encode($response, JSON_UNESCAPED_UNICODE);
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
                        $idContratoLink = $ordenVenta->getContratoUnicoActivo($idCliente);
                        $ordenVenta->registrarCredito($monto, $fecha, $hora, null, null, $ordenventa, $idContratoLink);
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

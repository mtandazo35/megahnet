<?php
require 'vendor/autoload.php';
use Dompdf\Dompdf;
class Cotizaciones extends Controller{
    private function cargarSri() { static $loaded=false; if (!$loaded) { include_once __DIR__ . '/../config/ServicesSri.php'; $loaded=true; } }

    public function __construct() {
        parent::__construct();
        session_start();
        if (empty($_SESSION['id_usuario'])) {
            header('Location: ' . BASE_URL);
            exit;
        }
    }
    public function index()
    {
        $data['title'] = 'Cotizaciones';
        $data['script'] = 'cotizaciones.js';
        $data['busqueda'] = 'busqueda.js';
        $data['carrito'] = 'posCotizaciones';
        $this->views->getView('cotizaciones', 'index', $data);
    }
    public function registrarCotizacion()
    {
        $json = file_get_contents('php://input');
        $datos = json_decode($json, true);
        $array['productos'] = array();
        $empresa = $this->model->getEmpresa();

        $total = 0;
        if (!empty($datos['productos'])) {
            $fecha = date('Y-m-d');
            $hora = date('H:i:s');
            $metodo = $datos['metodo'];
            $validez = $datos['validez'];
            $descuento = (!empty($datos['descuento'])) ? $datos['descuento'] : 0;
            $idCliente = $datos['idCliente'];
            if (empty($idCliente)) {
                $res = array('msg' => 'EL CLIENTE ES REQUERIDO', 'type' => 'warning');
            } else if (empty($metodo)) {
                $res = array('msg' => 'EL METODO ES REQUERIDO', 'type' => 'warning');
            } else if (empty($validez)) {
                $res = array('msg' => 'LA VALIDEZ ES REQUERIDO', 'type' => 'warning');
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
                $cotizacion = $this->model->registrarCotizacion($datosProductos, $total, $fecha, $hora, $metodo, $validez, $descuento, $idCliente);
                $this->cotizacionPDF('factura', $cotizacion);

                $InfoCotizacion = $this->model->getCotizacion($cotizacion);

                                        $dataInfo = array(
                                            'ruc' => $InfoCotizacion['num_identidad'],
                                            'email' => $InfoCotizacion['correo'],
                                            'fecha' => $InfoCotizacion['fecha'],
                                            'totalcotizacion' => $InfoCotizacion['total'],
                                            'cliente' => $InfoCotizacion['nombre'],
                                            'empresa' => $empresa['nombre'],
                                            'cotizacion' => $cotizacion,
                                            'enviroment' => ENVIROMENT,
                                            'emailremitente' => $empresa['correo'],
                                            'establecimiento' => $empresa['establecimiento'],
                                            'puntoemi' => $empresa['puntoemi'],
                                            'tipo' => 'cotizacion',
                                            'asunto' => 'Adjuntamos Comprobante'
                                        );
               
               
                if ($cotizacion > 0) {
                    $res = array('msg' => 'COTIZACIÓN GENERADA EXITOSAMENTE', 'type' => 'success', 'idCotizacion' => $cotizacion);
                    sendEmailCotizacion($dataInfo, 'email_cotizacion','cotizaciones');

                } else {
                    $res = array('msg' => 'ERROR AL GENERAR LA COTIZACIÓN', 'type' => 'error');
                }
            }
        } else {
            $res = array('msg' => 'CARRITO VACIO', 'type' => 'warning');
        }
        echo json_encode($res);
        die();
    }

    public function reporte($datos)
    {
        $this->cargarSri();
        ob_start();
        $array = explode(',', $datos);
        $tipo = $array[0];
        $idCotizacion = $array[1];

        $data['title'] = 'Cotización';
        $data['empresa'] = $this->model->getEmpresa();
        $data['cotizacion'] = $this->model->getCotizacion($idCotizacion);
        //print_r( $data['cotizacion']); exit;
        if (empty($data['cotizacion'])) {
            echo 'Pagina no Encontrada';
            exit;
        }
        $this->views->getView('cotizaciones', $tipo, $data);
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
        $dompdf->stream('Cotizacion_'.$idCotizacion.'.pdf', array('Attachment' => false));
    }

    public function listar()
    {
        $data = $this->model->getCotizaciones();
        for ($i=0; $i < count($data); $i++) { 
            $data[$i]['acciones'] = '<a class="btn btn-danger" href="#" onclick="verReporte(' . $data[$i]['id'] . ')"><i class="fas fa-file-pdf"></i></a>';   
        }
        echo json_encode($data, JSON_UNESCAPED_UNICODE);
        die();
    }
    public function cotizacionPDF($tipo, $idCotizacion)
    {
        $this->cargarSri();
        ob_start();
        // $array = explode(',', $datos);
        //$tipo = $array[0];
        //$idOrdenVenta = $array[1];
        $rutaGuardado = 'facturaelectronica/public/archivos/Cotizaciones/';

        $nombreArchivo = 'Cotizacion' . '_' . $idCotizacion . '.pdf';

        $data['title'] = 'Cotizacion';
        $data['empresa'] = $this->model->getEmpresa();
        $data['cotizacion'] = $this->model->getCotizacion($idCotizacion);
        //print_r( $data['cotizacion']); exit;
        if (empty($data['cotizacion'])) {
            echo 'Pagina no Encontrada';
            exit;
        }
        $this->views->getView('cotizaciones', $tipo, $data);
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
}
?>
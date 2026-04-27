<?php
require 'vendor/autoload.php';

use Dompdf\Dompdf;

class Compras extends Controller
{
    private $id_usuario, $caja;
    public function __construct()
    {
        parent::__construct();
        //session_start();
        require_once 'controllers/Cajas.php';
        $this->caja = new Cajas();
        if (empty($_SESSION['id_usuario'])) {
            header('Location: ' . BASE_URL);
            exit;
        }
        $this->id_usuario = $_SESSION['id_usuario'];
    }
    public function index()
    {
        $data['title'] = 'Compras';
        $data['script'] = 'compras.js';
        $data['busqueda'] = 'busqueda.js';
        $data['carrito'] = 'posCompra';
        $this->views->getView('compras', 'index', $data);
    }
    public function registrarCompra()
    {
        $json = file_get_contents('php://input');
        $datos = json_decode($json, true);
        $array['productos'] = array();
        $total = 0;
        if (!empty($datos['productos'])) {
            $indice = $datos['serie'];
            $numberSerie = $this->generate_numbers($indice, 1, 8);
            $fecha = date('Y-m-d');
            $hora = date('H:i:s');
            $serie = trim($numberSerie[0]);
            $idproveedor = $datos['idProveedor'];
            if (empty($idproveedor)) {
                $res = array('msg' => 'EL PROVEEDOR ES REQUERIDO', 'type' => 'warning');
            } else if (empty($serie)) {
                $res = array('msg' => 'LA SERIE ES REQUERIDO', 'type' => 'warning');
            } else {
                $saldo = $this->caja->getDatos();
                foreach ($datos['productos'] as $producto) {
                    $result = $this->model->getProducto($producto['id']);
                    $data['id'] = $result['id'];
                    $data['nombre'] = $producto['nombre'];
                    $data['precio'] = $producto['precio'];
                    $data['cantidad'] = $producto['cantidad'];
                    $data['iva_producto'] = $result['iva'];
                    $subTotal = $result['precio_compra'] * $producto['cantidad'];
                    array_push($array['productos'], $data);
                    $total += $subTotal;
                }
                if ($saldo['saldo'] >= $total) { // esta validacion es para registrar una compra, se necesita tener saldo en la caja para realizar comprar de cantidades alta
                    $datosProductos = json_encode($array['productos'], JSON_UNESCAPED_UNICODE);
                    $compra = $this->model->registrarCompra($datosProductos, $total, $fecha, $hora, $serie, $idproveedor, $this->id_usuario);
                    if ($compra > 0) {
                        foreach ($datos['productos'] as $producto) {
                            $result = $this->model->getProducto($producto['id']);
                            //actualizar stock
                            $nuevaCantidad = $result['cantidad'] + $producto['cantidad'];
                            $this->model->actualizarStock($nuevaCantidad, $result['id']);
                            $movimiento = 'Compra N°: ' . $compra;
                            $this->model->registrarMovimiento($movimiento, 'entrada', $producto['cantidad'], $nuevaCantidad, $producto['id'], $this->id_usuario);
                        }
                        $res = array('msg' => 'COMPRA GENERADA', 'type' => 'success', 'idCompra' => $compra);
                    } else {
                        $res = array('msg' => 'ERROR AL CREAR COMPRA', 'type' => 'error');
                    }
                } else {
                    $res = array('msg' => 'SALDO DISPONIBLE: ' . MONEDA . $saldo['saldo'], 'type' => 'warning');
                }
            }
        } else {
            $res = array('msg' => 'CARRITO VACIO', 'type' => 'warning');
        }
        echo json_encode($res, JSON_UNESCAPED_UNICODE);
        die();
    }

    public function reporte($datos)
    {
        ob_start();

        $array = explode(',', $datos);
        $tipo = $array[0];
        $idCompra = $array[1];

        $data['title'] = 'Compra';
        $data['empresa'] = $this->model->getEmpresa();
        $data['compra'] = $this->model->getCompra($idCompra);
        if (empty($data['compra'])) {
            echo 'Pagina no Encontrada';
            exit;
        }
        $this->views->getView('compras', $tipo, $data);
        $html = ob_get_clean();
        $dompdf = new Dompdf();
        $options = $dompdf->getOptions();
        $options->set('isJavascriptEnabled', true);
        $options->set('isRemoteEnabled', true);
        $dompdf->setOptions($options);
        $dompdf->loadHtml($html);

        if ($tipo == 'ticked') {
            $dompdf->setPaper(array(0, 0, 225, 800), 'portrait');
        } else {
            $dompdf->setPaper('A4', 'vertical');
        }

        // Render the HTML as PDF
        $dompdf->render();

        // Output the generated PDF to Browser
        $dompdf->stream('ticked.pdf', array('Attachment' => false));
    }

    public function listar()
    {
        $data = $this->model->getCompras();
        for ($i = 0; $i < count($data); $i++) {
            // Recalcular total real desde el JSON de productos (el campo total en BD puede estar mal)
            $totalReal = 0;
            $cantidadTotal = 0;
            $prods = !empty($data[$i]['productos']) ? json_decode($data[$i]['productos'], true) : [];
            if (is_array($prods)) {
                foreach ($prods as $p) {
                    $cant  = (float)($p['cantidad'] ?? 0);
                    $prec  = (float)($p['precio']   ?? 0);
                    $totalReal     += $cant * $prec;
                    $cantidadTotal += $cant;
                }
            }
            // Si recalculo dio mas que el guardado, usar el real; sino conservar el de BD
            if ($totalReal > 0) $data[$i]['total'] = number_format($totalReal, 2);
            $data[$i]['cantidad_total'] = (int)$cantidadTotal;

            if ($data[$i]['estado'] == 1) {
                $data[$i]['acciones'] = '<div>
                <a class="btn btn-danger" href="#" onclick="verReporte(' . $data[$i]['id'] . ')"><i class="fas fa-file-pdf"></i></a>
                <a class="btn btn-danger" href="#" onclick="anularCompra(' . $data[$i]['id'] . ')"><i class="fas fa-trash text-white"></i></a>
                </div>';
            }else{
                $data[$i]['acciones'] = '<div>
                <span class="badge bg-info">Anulado</span>
                <a class="btn btn-danger" href="#" onclick="verReporte(' . $data[$i]['id'] . ')"><i class="fas fa-file-pdf"></i></a>
                </div>';
            }
            
        }
        echo json_encode($data);
        die();
    }

    public function anular($idCompra)
    {
        if (isset($_GET) && is_numeric($idCompra)) {
            $data = $this->model->anular($idCompra);
            if ($data == 1) {
                $resultCompra = $this->model->getCompra($idCompra);
                $compraProducto = json_decode($resultCompra['productos'], true);
                foreach ($compraProducto as $producto) {
                    $result = $this->model->getProducto($producto['id']);
                    $nuevaCantidad = $result['cantidad'] - $producto['cantidad'];
                    $this->model->actualizarStock($nuevaCantidad, $producto['id']);
              //movimientos
              $movimiento = 'Devolución Compra N°: ' . $idCompra;
              $this->model->registrarMovimiento($movimiento, 'salida', $producto['cantidad'], $nuevaCantidad, $producto['id'], $this->id_usuario);
                }
                $res = array('msg' => 'COMPRA ANULADO', 'type' => 'success');
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

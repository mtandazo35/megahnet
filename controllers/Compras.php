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
            // Sanitizar serie: aceptar solo digitos para evitar loop infinito en generate_numbers
            $indice = (int) preg_replace('/[^0-9]/', '', (string)($datos['serie'] ?? ''));
            $fecha = date('Y-m-d');
            $hora = date('H:i:s');
            $serie = ($indice > 0) ? trim($this->generate_numbers($indice, 1, 8)[0]) : '';
            $idproveedor = $datos['idProveedor'];
            if (empty($idproveedor)) {
                $res = array('msg' => 'EL PROVEEDOR ES REQUERIDO', 'type' => 'warning');
            } else if (empty($serie)) {
                $res = array('msg' => 'LA SERIE DEBE CONTENER NUMEROS', 'type' => 'warning');
            } else {
                $saldo = $this->caja->getDatos();
                foreach ($datos['productos'] as $producto) {
                    $result = $this->model->getProducto($producto['id']);
                    // Redondear precio a 2 decimales para que coincida con lo que el user ve en pantalla
                    // (ej: listaCarrito puede traer "7.5304" pero el input muestra "7.53" via number_format).
                    $precio2 = round((float)($producto['precio'] ?? 0), 2);
                    $data['id'] = $result['id'];
                    $data['nombre'] = $producto['nombre'];
                    $data['precio'] = number_format($precio2, 2, '.', '');
                    $data['cantidad'] = $producto['cantidad'];
                    $data['iva_producto'] = $result['iva'];
                    $subTotal = $precio2 * (float)$producto['cantidad'];
                    $ivaProd = (float)($result['iva'] ?? 0);
                    $subTotalConIva = ($ivaProd > 0)
                        ? round($subTotal * (1 + $ivaProd / 100), 2)
                        : $subTotal;
                    array_push($array['productos'], $data);
                    $total += $subTotalConIva;
                }
                // Saldo de caja: ya NO bloquea la compra (decision de negocio).
                // Si el saldo no alcanza, se registra de todos modos pero se anota una advertencia
                // en el bitacora de alertas para auditoria.
                $datosProductos = json_encode($array['productos'], JSON_UNESCAPED_UNICODE);
                $compra = $this->model->registrarCompra($datosProductos, $total, $fecha, $hora, $serie, $idproveedor, $this->id_usuario);
                if ($compra > 0) {
                    foreach ($datos['productos'] as $producto) {
                        $result = $this->model->getProducto($producto['id']);
                        $nuevaCantidad = $result['cantidad'] + $producto['cantidad'];
                        $this->model->actualizarStock($nuevaCantidad, $result['id']);
                        $movimiento = 'Compra N°: ' . $compra;
                        $this->model->registrarMovimiento($movimiento, 'entrada', $producto['cantidad'], $nuevaCantidad, $producto['id'], $this->id_usuario);
                    }
                    if ($saldo['saldo'] < $total && function_exists('registrarFalla')) {
                        registrarFalla('COMPRA_SIN_SALDO',
                            'Compra N° ' . $compra . ' registrada con saldo insuficiente',
                            'Saldo de caja: ' . MONEDA . $saldo['saldo'] . ' / Total compra: ' . MONEDA . number_format($total, 2),
                            ['idCompra' => $compra, 'idProveedor' => $idproveedor, 'serie' => $serie]
                        );
                    }
                    $res = array('msg' => 'COMPRA GENERADA', 'type' => 'success', 'idCompra' => $compra);
                } else {
                    $res = array('msg' => 'ERROR AL CREAR COMPRA', 'type' => 'error');
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
            // Recalcular total real desde el JSON de productos (incluye IVA si el producto es gravado)
            $totalReal = 0;
            $cantidadTotal = 0;
            $prods = !empty($data[$i]['productos']) ? json_decode($data[$i]['productos'], true) : [];
            if (is_array($prods)) {
                foreach ($prods as $p) {
                    $cant   = (float)($p['cantidad'] ?? 0);
                    $prec   = (float)($p['precio']   ?? 0);
                    $ivaP   = (float)($p['iva_producto'] ?? 0);
                    $sub    = $cant * $prec;
                    $totalReal     += ($ivaP > 0) ? round($sub * (1 + $ivaP/100), 2) : $sub;
                    $cantidadTotal += $cant;
                }
            }
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

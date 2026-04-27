<?php
require 'vendor/autoload.php';

use Dompdf\Dompdf;

class Inventarios extends Controller
{
    private $id_usuario;
    public function __construct()
    {
        parent::__construct();
        session_start();
        if (empty($_SESSION['id_usuario'])) {
            header('Location: ' . BASE_URL);
            exit;
        }
        if ($_SESSION['rol'] == 2) {
            header('Location: ' . BASE_URL . 'admin/permisos');
            exit;
        }
        $this->id_usuario = $_SESSION['id_usuario'];
        session_write_close(); // libera lock — read-only en adelante
    }
    public function index()
    {
        $data['title'] = 'Inventarios';
        $data['script'] = 'inventarios.js';
        $this->views->getView('inventarios', 'index', $data);
    }

    public function listarMovimientos($datos)
    {
        if (empty($datos)) {
            $data = $this->model->getMovimientos($this->id_usuario);
        } else {
            $array = explode('-', $datos);
            $anio = $array[0];
            $mes = $array[1];
            $data = $this->model->getMovimientosMes($anio, $mes, $this->id_usuario);
        }
        echo json_encode($data, JSON_UNESCAPED_UNICODE);
        die();
    }
    public function reporte($datos)
    {
        $data['empresa'] = $this->model->getEmpresa();
        $data['usuario'] = $_SESSION['nombre_usuario'];
        $data['title'] = 'Inventario';

        if (empty($datos)) {
            $data['inventario'] = $this->model->getMovimientos($this->id_usuario);
        } else {
            $array = explode('-', $datos);
            $anio = $array[0];
            $mes = $array[1];
            $data['inventario'] = $this->model->getMovimientosMes($anio, $mes, $this->id_usuario);
        }
        ob_start();
        $this->views->getView('inventarios', 'reporte', $data);
        $html = ob_get_clean();
        $dompdf = new Dompdf();
        $options = $dompdf->getOptions();
        $options->set('isJavascriptEnabled', true);
        $options->set('isRemoteEnabled', true);
        $dompdf->setOptions($options);
        $dompdf->loadHtml($html);

        $dompdf->setPaper('A4', 'vertical');

        // Render the HTML as PDF
        $dompdf->render();

        // Output the generated PDF to Browser
        $dompdf->stream('reporte.pdf', array('Attachment' => false));
    }

    public function procesarAjuste()
    {
        $json = file_get_contents('php://input');
        $datos = json_decode($json, true);
        if (empty($datos['idProducto'])) {
            $res = array('msg' => 'EL PRODUCTO ES REQUERIDO', 'type' => 'warning');
        } else if (empty($datos['cantidad'])) {
            $res = array('msg' => 'LA CANTIDAD ES REQUERIDO', 'type' => 'warning');
        } else {
            if (is_numeric($datos['idProducto']) && is_numeric($datos['cantidad'])) {
                $idProducto = $datos['idProducto'];
                $cantidad = $datos['cantidad'];
                $producto = $this->model->getProducto($idProducto);
                $nuevaCantidad = $producto['cantidad'] + $cantidad;
                $data = $this->model->procesarAjuste($nuevaCantidad, $idProducto);
                if ($data == 1) {
                    //movimientos
                    $cantidadInventario = abs($cantidad);
                    $accion = ($cantidad > 0) ? 'entrada' : 'salida';
                    $movimiento = 'Ajuste de Inventario: ' . $accion;
                    $this->model->registrarMovimiento($movimiento, $accion, $cantidadInventario, $nuevaCantidad, $idProducto, $this->id_usuario);
                    $res = array('msg' => 'STOCK DEL PRODUCTO AJUSTADO EXITOSAMENTE', 'type' => 'success');
                } else {
                    $res = array('msg' => 'ERROR EN EL AJUSTE', 'type' => 'error');
                }
            }
        }
        echo json_encode($res);
        die();
    }


    public function listarKardex($idProducto = 0)
    {
        header('Content-Type: application/json; charset=utf-8');
        if (empty($_SESSION['id_usuario'])) { echo json_encode([]); exit; }
        $idProducto = (int)$idProducto;
        if ($idProducto <= 0) { echo json_encode([]); exit; }
        $rows = $this->model->getKardex($idProducto, $this->id_usuario);
        $out = [];
        foreach ($rows as $r) {
            $accion = strtolower((string)($r['accion'] ?? ''));
            $cant   = (int)($r['cantidad'] ?? 0);
            $entrada = ($accion === 'entrada') ? $cant : 0;
            $salida  = ($accion === 'salida')  ? $cant : 0;
            $accionBadge = ($accion === 'entrada')
                ? '<span class="badge bg-success">Entrada</span>'
                : '<span class="badge bg-danger">Salida</span>';
            $out[] = [
                'fecha'        => $r['fecha'],
                'movimiento'   => $r['movimiento'],
                'accion'       => $accionBadge,
                'entrada'      => $entrada,
                'salida'       => $salida,
                'stock_actual' => (int)($r['stock_actual'] ?? 0),
            ];
        }
        echo json_encode($out, JSON_UNESCAPED_UNICODE);
        exit;
    }

    //#### Kardex
    public function kardex($idProducto)
    {
        $data['usuario'] = $_SESSION['nombre_usuario'];
        $data['title'] = 'Inventario';

        $data['empresa'] = $this->model->getEmpresa();
        $data['kardex'] = $this->model->getKardex($idProducto, $this->id_usuario);
        for ($i = 0; $i < count($data['kardex']); $i++) {
            $data['kardex'][$i]['entrada'] = 0;
            $data['kardex'][$i]['salida'] = 0;
            if ($data['kardex'][$i]['accion'] == 'salida') {
                $data['kardex'][$i]['salida'] = $data['kardex'][$i]['cantidad'];
            } else {
                $data['kardex'][$i]['entrada'] = $data['kardex'][$i]['cantidad'];
            }
        }
        ob_start();
        $this->views->getView('inventarios', 'kardex', $data);
        $html = ob_get_clean();
        $dompdf = new Dompdf();
        $options = $dompdf->getOptions();
        $options->set('isJavascriptEnabled', true);
        $options->set('isRemoteEnabled', true);
        $dompdf->setOptions($options);
        $dompdf->loadHtml($html);

        $dompdf->setPaper('A4', 'vertical');

        // Render the HTML as PDF
        $dompdf->render();

        // Output the generated PDF to Browser
        $dompdf->stream('Kardex.pdf', array('Attachment' => false));
    }
}

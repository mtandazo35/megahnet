<?php
class Sucursales extends Controller
{
    public function __construct()
    {
        parent::__construct();
        session_start();
        if (empty($_SESSION['id_usuario'])) {
            header('Location: ' . BASE_URL);
            exit;
        }
        // Solo administradores
        if (!isset($_SESSION['rol']) || (int)$_SESSION['rol'] !== 1) {
            header('Location: ' . BASE_URL . 'admin');
            exit;
        }
    }

    public function index()
    {
        $data['title']  = 'Sucursales';
        $data['script'] = 'sucursales.js';
        $this->views->getView('sucursales', 'index', $data);
    }

    public function listar()
    {
        $rows = $this->model->getSucursales(1);
        foreach ($rows as $i => $r) {
            $rows[$i]['estab_punto'] = htmlspecialchars($r['establecimiento']) . ' - ' . htmlspecialchars($r['puntoemi']);
            $rows[$i]['ambiente_badge'] = $r['ambiente'] === 'PRODUCCION'
                ? '<span class="badge bg-success">PRODUCCIÓN</span>'
                : '<span class="badge bg-warning text-dark">PRUEBAS</span>';
            $rows[$i]['acciones'] = '<div class="d-flex gap-1">'
                . '<button class="btn btn-sm btn-info" type="button" onclick="editarSucursal(' . (int)$r['id'] . ')"><i class="fas fa-edit text-white"></i></button>'
                . '<button class="btn btn-sm btn-danger" type="button" onclick="eliminarSucursal(' . (int)$r['id'] . ')"><i class="fas fa-trash"></i></button>'
                . '</div>';
        }
        echo json_encode($rows, JSON_UNESCAPED_UNICODE);
        die();
    }

    public function listarInactivos()
    {
        $rows = $this->model->getSucursales(0);
        foreach ($rows as $i => $r) {
            $rows[$i]['estab_punto'] = htmlspecialchars($r['establecimiento']) . ' - ' . htmlspecialchars($r['puntoemi']);
            $rows[$i]['acciones'] = '<button class="btn btn-sm btn-success" type="button" onclick="restaurarSucursal(' . (int)$r['id'] . ')"><i class="fas fa-check-circle"></i></button>';
        }
        echo json_encode($rows, JSON_UNESCAPED_UNICODE);
        die();
    }

    public function editar($id)
    {
        $row = $this->model->editar((int)$id);
        echo json_encode($row, JSON_UNESCAPED_UNICODE);
        die();
    }

    public function registrar()
    {
        if (!isset($_POST['nombre'])) { echo json_encode(['msg'=>'DATOS INCOMPLETOS','type'=>'warning']); die(); }

        $id = (int)($_POST['id'] ?? 0);
        $d = [
            'nombre'                   => strClean($_POST['nombre'] ?? ''),
            'direccion'                => strClean($_POST['direccion'] ?? ''),
            'establecimiento'          => str_pad(preg_replace('/\D/', '', $_POST['establecimiento'] ?? '1'), 3, '0', STR_PAD_LEFT),
            'puntoemi'                 => str_pad(preg_replace('/\D/', '', $_POST['puntoemi'] ?? '1'), 3, '0', STR_PAD_LEFT),
            'sec_factura'              => max(1, (int)($_POST['sec_factura']             ?? 1)),
            'sec_factura_pruebas'      => max(1, (int)($_POST['sec_factura_pruebas']     ?? 1)),
            'sec_notacredito'          => max(1, (int)($_POST['sec_notacredito']         ?? 1)),
            'sec_notacredito_pruebas'  => max(1, (int)($_POST['sec_notacredito_pruebas'] ?? 1)),
            'sec_recibo'               => max(1, (int)($_POST['sec_recibo']              ?? 1)),
            'ambiente'                 => (($_POST['ambiente'] ?? 'PRUEBAS') === 'PRODUCCION') ? 'PRODUCCION' : 'PRUEBAS',
        ];

        if ($d['nombre'] === '') {
            echo json_encode(['msg'=>'EL NOMBRE DEL ESTABLECIMIENTO ES REQUERIDO','type'=>'warning']); die();
        }
        if (!preg_match('/^\d{3}$/', $d['establecimiento']) || !preg_match('/^\d{3}$/', $d['puntoemi'])) {
            echo json_encode(['msg'=>'ESTABLECIMIENTO Y PUNTO DE EMISIÓN DEBEN SER 3 DÍGITOS (001-999)','type'=>'warning']); die();
        }

        $existe = $this->model->getValidarUnique($d['establecimiento'], $d['puntoemi'], $id);
        if (!empty($existe)) {
            echo json_encode(['msg'=>'YA EXISTE UNA SUCURSAL CON ESE ESTABLECIMIENTO + PUNTO DE EMISIÓN','type'=>'warning']); die();
        }

        if ($id === 0) {
            $r = $this->model->registrar($d);
            $res = $r > 0
                ? ['msg'=>'SUCURSAL REGISTRADA EXITOSAMENTE','type'=>'success']
                : ['msg'=>'ERROR AL REGISTRAR','type'=>'error'];
        } else {
            $r = $this->model->actualizar($d, $id);
            $res = $r >= 0
                ? ['msg'=>'SUCURSAL ACTUALIZADA EXITOSAMENTE','type'=>'success']
                : ['msg'=>'ERROR AL ACTUALIZAR','type'=>'error'];
        }
        echo json_encode($res, JSON_UNESCAPED_UNICODE);
        die();
    }

    public function eliminar($id)
    {
        if (!is_numeric($id)) { echo json_encode(['msg'=>'ERROR','type'=>'error']); die(); }
        $r = $this->model->eliminar(0, (int)$id);
        $res = $r == 1
            ? ['msg'=>'SUCURSAL ELIMINADA EXITOSAMENTE','type'=>'success']
            : ['msg'=>'ERROR AL ELIMINAR','type'=>'error'];
        echo json_encode($res, JSON_UNESCAPED_UNICODE);
        die();
    }

    public function restaurar($id)
    {
        if (!is_numeric($id)) { echo json_encode(['msg'=>'ERROR','type'=>'error']); die(); }
        $r = $this->model->eliminar(1, (int)$id);
        $res = $r == 1
            ? ['msg'=>'SUCURSAL RESTAURADA EXITOSAMENTE','type'=>'success']
            : ['msg'=>'ERROR AL RESTAURAR','type'=>'error'];
        echo json_encode($res, JSON_UNESCAPED_UNICODE);
        die();
    }

    public function inactivos()
    {
        $data['title']  = 'Sucursales Inactivos';
        $data['script'] = 'sucursales-inactivos.js';
        $this->views->getView('sucursales', 'inactivos', $data);
    }
}

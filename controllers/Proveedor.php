<?php
class Proveedor extends Controller
{
    public function __construct()
    {
        parent::__construct();
        session_start();
        if (empty($_SESSION['id_usuario'])) {
            header('Location: ' . BASE_URL);
            exit;
        }
    }
    public function index()
    {
        if ($_SESSION['rol'] == 2) {
            header('Location: ' . BASE_URL . 'admin/permisos');
            exit;
        }
        $data['title'] = 'Proveedores';
        $data['script'] = 'proveedor.js';
        $data['validacion'] = 'validacion.js';

        $this->views->getView('proveedor', 'index', $data);
    }
    public function listar()
    {
        if ($_SESSION['rol'] == 2) {
            header('Location: ' . BASE_URL . 'admin/permisos');
            exit;
        }
        $data = $this->model->getProveedores(1);
        for ($i = 0; $i < count($data); $i++) {
            $data[$i]['acciones'] = '<div>
            <button class="btn btn-info" type="button" onclick="editarProveedor(' . $data[$i]['id'] . ')"><i class="fas fa-edit text-white"></i></button>
            <button class="btn btn-danger" type="button" onclick="eliminarProveedor(' . $data[$i]['id'] . ')"><i class="fas fa-trash"></i></button>
            </div>';
        }
        echo json_encode($data, JSON_UNESCAPED_UNICODE);
        die();
    }
    public function registrar()
    {
        if ($_SESSION['rol'] == 2) {
            header('Location: ' . BASE_URL . 'admin/permisos');
            exit;
        }
        if (isset($_POST['ruc']) && isset($_POST['nombre'])) {
            $id = strClean($_POST['id']);
            $ruc = trim(strClean($_POST['ruc']));
            $nombre = trim(strClean($_POST['nombre']));
            $telefono = trim(strClean($_POST['telefono']));
            $correo = trim(strClean($_POST['correo']));
            $direccion = strClean($_POST['direccion']);
            if (empty($ruc)) {
                $res = array('msg' => 'EL RUC ES REQUERIDO', 'type' => 'warning');
            } else if (empty($nombre)) {
                $res = array('msg' => 'EL NOMBRE ES REQUERIDO', 'type' => 'warning');
            } else if (empty($direccion)) {
                $res = array('msg' => 'LA DIRECCION ES REQUERIDO', 'type' => 'warning');
            } else {
                if ($id == '') {
                    $verificarRuc = $this->model->getValidar('ruc', $ruc, 'registrar', 0);
                    if (empty($verificarRuc)) {
                        $data = $this->model->registrar(
                            $ruc,
                            $nombre,
                            $telefono,
                            $correo,
                            $direccion
                        );
                        if ($data > 0) {
                            $res = array('msg' => 'PROVEEDOR REGISTRADO EXITOSAMENTE', 'type' => 'success');
                        } else {
                            $res = array('msg' => 'ERROR AL REGISTRAR', 'type' => 'error');
                        }
                    } else {
                        $res = array('msg' => 'EL RUC DEBE SER UNICO', 'type' => 'warning');
                    }
                } else {
                    $verificarRuc = $this->model->getValidar('ruc', $ruc, 'actualizar', $id);
                    if (empty($verificarRuc)) {
                        $data = $this->model->actualizar(
                            $ruc,
                            $nombre,
                            $telefono,
                            $correo,
                            $direccion,
                            $id
                        );
                        if ($data > 0) {
                            $res = array('msg' => 'PROVEEDOR ACTUALIZADO EXITOSAMENTE', 'type' => 'success');
                        } else {
                            $res = array('msg' => 'ERROR AL ACTUALIZAR', 'type' => 'error');
                        }
                    } else {
                        $res = array('msg' => 'EL RUC DEBE SER UNICO', 'type' => 'warning');
                    }
                }
            }
        } else {
            $res = array('msg' => 'ERROR DESCONOCIDO', 'type' => 'error');
        }
        echo json_encode($res, JSON_UNESCAPED_UNICODE);
        die();
    }

    public function eliminar($idProveedor)
    {
        if ($_SESSION['rol'] == 2) {
            header('Location: ' . BASE_URL . 'admin/permisos');
            exit;
        }
        if (isset($_GET) && is_numeric($idProveedor)) {
            $data = $this->model->eliminar(0, $idProveedor);
            if ($data == 1) {
                $res = array('msg' => 'PROVEEDOR ELIMINADO EXITOSAMENTE', 'type' => 'success');
            } else {
                $res = array('msg' => 'ERROR AL ELIMINAR', 'type' => 'error');
            }
        } else {
            $res = array('msg' => 'ERROR DESCONOCIDO', 'type' => 'error');
        }
        echo json_encode($res, JSON_UNESCAPED_UNICODE);
        die();
    }

    public function editar($idProveedor)
    {
        if ($_SESSION['rol'] == 2) {
            header('Location: ' . BASE_URL . 'admin/permisos');
            exit;
        }
        $data = $this->model->editar($idProveedor);
        echo json_encode($data, JSON_UNESCAPED_UNICODE);
        die();
    }
    public function inactivos()
    {
        if ($_SESSION['rol'] == 2) {
            header('Location: ' . BASE_URL . 'admin/permisos');
            exit;
        }
        $data['title'] = 'Proveedores Inactivos';
        $data['script'] = 'proveedor-inactivos.js';
        $this->views->getView('proveedor', 'inactivos', $data);
    }
    public function listarInactivos()
    {
        if ($_SESSION['rol'] == 2) {
            header('Location: ' . BASE_URL . 'admin/permisos');
            exit;
        }
        $data = $this->model->getProveedores(0);
        for ($i = 0; $i < count($data); $i++) {
            $data[$i]['acciones'] = '<div>
            <button class="btn btn-success" type="button" onclick="restaurarProveedor(' . $data[$i]['id'] . ')"><i class="fas fa-check-circle"></i></button>
            </div>';
        }
        echo json_encode($data, JSON_UNESCAPED_UNICODE);
        die();
    }
    public function restaurar($idProveedor)
    {
        if ($_SESSION['rol'] == 2) {
            header('Location: ' . BASE_URL . 'admin/permisos');
            exit;
        }
        if (isset($_GET) && is_numeric($idProveedor)) {
            $data = $this->model->eliminar(1, $idProveedor);
            if ($data == 1) {
                $res = array('msg' => 'PROVEEDOR RESTAURADO EXITOSAMENTE', 'type' => 'success');
            } else {
                $res = array('msg' => 'ERROR RESTUARAR', 'type' => 'error');
            }
        } else {
            $res = array('msg' => 'ERROR DESCONOCIDO', 'type' => 'error');
        }
        echo json_encode($res, JSON_UNESCAPED_UNICODE);
        die();
    }

    //buscar proveedores para la compra
    public function buscar()
    {
        $array = array();
        $valor = $_GET['term'];
        $data = $this->model->buscarPorNombre($valor);
        foreach ($data as $row) {
            $result['id'] = $row['id'];
            $result['label'] = $row['nombre'];
            $result['telefono'] = $row['telefono'];
            $result['direccion'] = $row['direccion'];
            $result['correo'] = $row['correo'];

            array_push($array, $result);
        }
        echo json_encode($array, JSON_UNESCAPED_UNICODE);
        die();
    }
}

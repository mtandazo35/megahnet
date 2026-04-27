<?php
class Zonas extends Controller
{
    public function __construct()
    {
        parent::__construct();
        session_start();
        if (empty($_SESSION['id_usuario'])) {
            header('Location: ' . BASE_URL);
            exit;
        }
        session_write_close(); // libera lock — read-only en adelante
    }
    public function index()
    {
        $data['title'] = 'Zonas';
        $data['script'] = 'zonas.js';
        $data['mikrotiks'] = $this->model->getMikrotiks();
        $this->views->getView('zonas', 'index', $data);
    }
    public function listar()
    {
        $data = $this->model->getZonas(1);
        for ($i = 0; $i < count($data); $i++) {
            $data[$i]['mikrotik_nombre'] = !empty($data[$i]['mikrotik_nombre'])
                ? '<span class="badge bg-light text-dark border">' . htmlspecialchars($data[$i]['mikrotik_nombre']) . '</span>'
                : '<span class="text-muted small">—</span>';
            $data[$i]['acciones'] = '<div>
            <button class="btn btn-info" type="button" onclick="editarZonas(' . $data[$i]['id'] . ')"><i class="fas fa-edit text-white"></i></button>
            <button class="btn btn-danger" type="button" onclick="eliminarZonas(' . $data[$i]['id'] . ')"><i class="fas fa-trash"></i></button>
            </div>';
        }
        echo json_encode($data, JSON_UNESCAPED_UNICODE);
        die();
    }
    public function registrar()
    {
        if (isset($_POST['nombre'])) {
            $zonas = strClean($_POST['nombre']);
            $id = strClean($_POST['id']);
            if (empty($zonas)) {
                $res = array('msg' => 'LA DESCRIPCION ES REQUERIDO', 'type' => 'warning');
            } else {
                if ($id == '') {
                    $verificar = $this->model->getValidar('descripcion', $zonas, 'registrar', 0);
                    if (empty($verificar)) {
                        $idMik = isset($_POST['id_mikrotik']) ? trim($_POST['id_mikrotik']) : null;
                        $data = $this->model->registrar($zonas, $idMik);
                        if ($data > 0) {
                            $res = array('msg' => 'LA ZONA REGISTRADO EXITOSAMENTE', 'type' => 'success');
                        } else {
                            $res = array('msg' => 'ERROR AL REGISTRAR', 'type' => 'error');
                        }
                    } else {
                        $res = array('msg' => 'LA ZONA YA EXISTE', 'type' => 'warning');
                    }
                } else {
                    $verificar = $this->model->getValidar('descripcion', $zonas, 'actualizar', $id);
                    if (empty($verificar)) {
                        $idMik = isset($_POST['id_mikrotik']) ? trim($_POST['id_mikrotik']) : null;
                        $data = $this->model->actualizar($zonas, $id, $idMik);
                        if ($data > 0) {
                            $res = array('msg' => 'ZONA ACTUALIZADO EXITOSAMENTE', 'type' => 'success');
                        } else {
                            $res = array('msg' => 'ERROR AL ACTUALIZAR', 'type' => 'error');
                        }
                    } else {
                        $res = array('msg' => 'LA ZONA YA EXISTE', 'type' => 'warning');
                    }
                }
            }
        }
        echo json_encode($res, JSON_UNESCAPED_UNICODE);
        die();
    }

    public function eliminar($idZonas)
    {
        if (isset($_GET) && is_numeric($idZonas)) {
            $data = $this->model->eliminar(0, $idZonas);
            if ($data == 1) {
                $res = array('msg' => 'ZONA ELIMINADO EXITOSAMENTE', 'type' => 'success');
            } else {
                $res = array('msg' => 'ERROR AL ELIMINAR', 'type' => 'error');
            }
        } else {
            $res = array('msg' => 'ERROR DESCONOCIDO', 'type' => 'error');
        }
        echo json_encode($res, JSON_UNESCAPED_UNICODE);
        die();
    }

    public function editar($idZonas)
    {
        $data = $this->model->editar($idZonas);
        echo json_encode($data, JSON_UNESCAPED_UNICODE);
        die();
    }

    public function inactivos()
    {
        $data['title'] = 'Zonas Inactivos';
        $data['script'] = 'zonas-inactivos.js';
        $this->views->getView('zonas', 'inactivos', $data);
    }

    public function listarInactivos()
    {
        $data = $this->model->getZonas(0);
        for ($i = 0; $i < count($data); $i++) {
            $data[$i]['acciones'] = '<div>
            <button class="btn btn-success" type="button" onclick="restaurarZonas(' . $data[$i]['id'] . ')"><i class="fas fa-check-circle"></i></button>
            </div>';
        }
        echo json_encode($data, JSON_UNESCAPED_UNICODE);
        die();
    }

    public function restaurar($idZonas)
    {
        if (isset($_GET) && is_numeric($idZonas)) {
            $data = $this->model->eliminar(1, $idZonas);
            if ($data == 1) {
                $res = array('msg' => 'ZONA RESTAURADO EXITOSAMENTE', 'type' => 'success');
            } else {
                $res = array('msg' => 'ERROR AL RESTURAR', 'type' => 'error');
            }
        } else {
            $res = array('msg' => 'ERROR DESCONOCIDO', 'type' => 'error');
        }
        echo json_encode($res, JSON_UNESCAPED_UNICODE);
        die();
    }
}

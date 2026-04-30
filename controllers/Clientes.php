<?php
require 'vendor/autoload.php';

use PhpOffice\PhpSpreadsheet\IOFactory;

class Clientes extends Controller
{

    public function __construct()
    {

        session_start();

        parent::__construct();
        if (empty($_SESSION['id_usuario'])) {
            header('Location: ' . BASE_URL);
            exit;
        }
    }
    public function index()
    {
        $data['title'] = 'Clientes';
        $data['script'] = 'clientes.js';
        $data['validacion'] = 'validacion.js';

        $data['cliente'] = $this->model->getClientes(1);
        $this->views->getView('clientes', 'index', $data);
    }
    public function listar()
    {
        $data = $this->model->getClientes(1);
        for ($i = 0; $i < count($data); $i++) {
            $data[$i]['acciones'] = '<div>
            <button class="btn btn-info" type="button" onclick="editarCliente(' . $data[$i]['id'] . ')"><i class="fas fa-edit text-white"></i></button>
            <button class="btn btn-danger" type="button" onclick="eliminarCliente(' . $data[$i]['id'] . ')"><i class="fas fa-trash"></i></button>
            </div>';
        }

        echo json_encode($data, JSON_UNESCAPED_UNICODE);
        die();
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
    public function registrar()
    {
        if (isset($_POST['identidad']) && isset($_POST['num_identidad'])) {
            $id = strClean($_POST['id']);
            $identidad = strClean($_POST['identidad']);
            $num_identidad = trim(strClean($_POST['num_identidad']));
            $nombre = trim(strClean($_POST['nombre']));
            $telefono = trim(empty($_POST['telefono']) ? null : strClean($_POST['telefono']));
            $correo = trim((empty($_POST['correo'])) ? null : strClean($_POST['correo']));
            $direccion = trim(strClean($_POST['direccion']));
            $lentIdentidad = strlen($num_identidad);
            // print_r($lentIdentidad); exit;

            if ($identidad == 'CEDULA' && $lentIdentidad != 10) {
                $res = array('msg' => 'LA CEDULA DEBE TENER 10 DIGITOS', 'type' => 'warning');
            } else if ($identidad == 'RUC' && $lentIdentidad != 13) {
                $res = array('msg' => 'EL RUC DEBE TENER 13 DIGITOS', 'type' => 'warning');
            } else if (empty($identidad)) {
                $res = array('msg' => 'EL TIPO DE IDENTIDAD ES REQUERIDO', 'type' => 'warning');
            } else if (empty($num_identidad)) {
                $res = array('msg' => 'LA CÉDULA / RÚC ES REQUERIDO', 'type' => 'warning');
            } else if (empty($nombre)) {
                $res = array('msg' => 'LA RAZÓN SOCIAL ES REQUERIDO', 'type' => 'warning');
            } else if (empty($direccion)) {
                $res = array('msg' => 'LA DIRECCION ES REQUERIDO', 'type' => 'warning');
            } else {
                if ($id == '') {
                    $verificarIdentidad = $this->model->getValidar('num_identidad', $num_identidad, 'registrar', 0);
                    if (empty($verificarIdentidad)) {
                        if ($correo != null) {
                            /* $verificarCorreo = $this->model->getValidar('correo', $correo, 'registrar', 0);
                        if (!empty($verificarCorreo)) {
                        $res = array('msg' => 'EL CORREO DEBE SER UNICO', 'type' => 'warning');
                        echo json_encode($res, JSON_UNESCAPED_UNICODE);
                        die();
                        }*/
                        }
                        $data = $this->model->registrar(
                            $identidad,
                            $num_identidad,
                            $nombre,
                            $telefono,
                            $correo,
                            $direccion,
                            1
                        );
                        if ($data > 0) {

                            $res = array('msg' => 'CLIENTE REGISTRADO EXITOSAMENTE', 'type' => 'success');
                        } else {
                            $res = array('msg' => 'ERROR AL REGISTRAR', 'type' => 'error');
                        }
                    } else {
                        $ex = $verificarIdentidad;
                        $estadoTxt = (isset($ex['estado']) && $ex['estado'] == 1) ? 'ACTIVO' : 'INACTIVO';
                        $res = array('msg' => 'YA EXISTE CLIENTE CON ESA CEDULA/RUC: ' . ($ex['nombre'] ?? '') . ' (id ' . ($ex['id'] ?? '?') . ', ' . $estadoTxt . ')', 'type' => 'warning');
                    }
                } else {
                    $verificarIdentidad = $this->model->getValidar('num_identidad', $num_identidad, 'actualizar', $id);
                    if (empty($verificarIdentidad)) {
                        if ($correo != null) {
                            /* $verificarCorreo = $this->model->getValidar('correo', $correo, 'actualizar', $id);
                        if (!empty($verificarCorreo)) {
                        $res = array('msg' => 'EL CORREO DEBE SER UNICO', 'type' => 'warning');
                        echo json_encode($res, JSON_UNESCAPED_UNICODE);
                        die();
                        }*/
                        }
                      
                            $data = $this->model->actualizar(
                                $identidad,
                                $num_identidad,
                                $nombre,
                                $telefono,
                                $correo,
                                $direccion,
                                $id
                            );
                            if ($data > 0) {
                                $res = array('msg' => 'CLIENTE ACTUALIZADO EXITOSAMENTE', 'type' => 'success');
                            } else {
                                $res = array('msg' => 'ERROR AL ACTUALIZAR', 'type' => 'error');
                            }
                        
                    } else {
                        $ex = $verificarIdentidad;
                        $estadoTxt = (isset($ex['estado']) && $ex['estado'] == 1) ? 'ACTIVO' : 'INACTIVO';
                        $res = array('msg' => 'OTRA CUENTA YA TIENE ESA CEDULA/RUC: ' . ($ex['nombre'] ?? '') . ' (id ' . ($ex['id'] ?? '?') . ', ' . $estadoTxt . ')', 'type' => 'warning');
                    }
                }
            }
        } else {
            $res = array('msg' => 'ERROR DESCONOCIDO', 'type' => 'error');
        }
       // $respaldoBD = respaldoBD();
        echo json_encode($res, JSON_UNESCAPED_UNICODE);
        die();
    }

    public function eliminar($idCliente)
    {
        if (isset($_GET) && is_numeric($idCliente)) {
            $data = $this->model->eliminar(0, $idCliente);
            if ($data > 0) {
                $res = array('msg' => 'CLIENTE ELIMINADO EXITOSAMENTE', 'type' => 'success');
            } else {
                $res = array('msg' => 'ERROR AL ELIMINAR', 'type' => 'error');
            }
        } else {
            $res = array('msg' => 'ERROR DESCONOCIDO', 'type' => 'error');
        }
        echo json_encode($res, JSON_UNESCAPED_UNICODE);
        die();
    }

    public function editar($idCliente)
    {
        $data = $this->model->editar($idCliente);
        echo json_encode($data, JSON_UNESCAPED_UNICODE);
        die();
    }

    public function inactivos()
    {
        $data['title'] = 'Clientes  Inactivos';
        $data['script'] = 'clientes-inactivos.js';
        $this->views->getView('clientes', 'inactivos', $data);
    }
    public function listarInactivos()
    {
        $data = $this->model->getClientes(0);
        for ($i = 0; $i < count($data); $i++) {
            $id = (int)$data[$i]['id'];
            $data[$i]['acciones'] = '<div class="d-flex gap-1">'
                . '<button class="btn btn-success btn-sm" type="button" title="Restaurar" onclick="restaurarCliente(' . $id . ')"><i class="fas fa-check-circle"></i></button>'
                . '<button class="btn btn-danger btn-sm" type="button" title="Eliminar permanentemente" onclick="eliminarClientePermanente(' . $id . ')"><i class="fas fa-trash"></i></button>'
                . '</div>';
        }
        echo json_encode($data, JSON_UNESCAPED_UNICODE);
        die();
    }

    /** DELETE definitivo del cliente — solo si no tiene contratos. */
    public function eliminarPermanente($idCliente)
    {
        if (empty($_SESSION['id_usuario'])) {
            echo json_encode(['msg' => 'NO AUTENTICADO', 'type' => 'error']);
            die();
        }
        if (!is_numeric($idCliente)) {
            echo json_encode(['msg' => 'ID INVALIDO', 'type' => 'error']);
            die();
        }
        $idCliente = (int)$idCliente;

        $totalContratos = $this->model->contarContratosTotales($idCliente);
        if ($totalContratos > 0) {
            $res = ['msg' => 'NO SE PUEDE ELIMINAR: el cliente tiene ' . $totalContratos . ' contrato(s) asociado(s).', 'type' => 'warning'];
            echo json_encode($res, JSON_UNESCAPED_UNICODE);
            die();
        }
        $r = $this->model->eliminarPermanente($idCliente);
        $res = $r >= 0
            ? ['msg' => 'CLIENTE ELIMINADO PERMANENTEMENTE', 'type' => 'success']
            : ['msg' => 'ERROR AL ELIMINAR', 'type' => 'error'];
        echo json_encode($res, JSON_UNESCAPED_UNICODE);
        die();
    }
    public function restaurar($idCliente)
    {
        if (isset($_GET) && is_numeric($idCliente)) {
            $data = $this->model->eliminar(1, $idCliente);
            if ($data > 0) {
                $res = array('msg' => 'CLIENTE RESTAURADO EXITOSAMENTE', 'type' => 'success');
            } else {
                $res = array('msg' => 'ERROR AL RESTAURAR', 'type' => 'error');
            }
        } else {
            $res = array('msg' => 'ERROR DESCONOCIDO', 'type' => 'error');
        }
        echo json_encode($res, JSON_UNESCAPED_UNICODE);
        die();
    }
    //buscar clientes para la venta
    public function buscar()
    {
        $array = array();
        $valor = strClean($_GET['term']);
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

function importarExcel($archivoExcel)
{
    $clientes = new ClientesModel;

    $documento = IOFactory::load($archivoExcel);

    $HojaExcel = $documento->getSheet(0);
    $FilaDeHojaExcel = $HojaExcel->getHighestDataRow();

    for ($fila = 2; $fila <= $FilaDeHojaExcel; $fila++) {
        $identidad = $HojaExcel->getCellByColumnAndRow(1, $fila);
        $num_identidad = $HojaExcel->getCellByColumnAndRow(2, $fila);
        $nombre = $HojaExcel->getCellByColumnAndRow(3, $fila);
        $telefono = $HojaExcel->getCellByColumnAndRow(4, $fila);
        $correo = $HojaExcel->getCellByColumnAndRow(5, $fila);
        $direccion = $HojaExcel->getCellByColumnAndRow(6, $fila);

        $verificarIdentidad = $clientes->getValidar('num_identidad', $num_identidad, 'registrar', 0);
        if (empty($verificarIdentidad)) {
            // print_r($verificarIdentidad); exit;
            $correo = (empty($correo)) ? null : strClean($correo);

            if ($correo != null) {
                $verificarCorreo = $clientes->getValidar('correo', $correo, 'registrar', 0);
                if (!empty($verificarCorreo)) {
                    $res = array('msg' => 'EL CORREO DEBE SER UNICO DEL CLIENTE ' . $num_identidad . ' ' . $nombre, 'type' => 'warning');
                    echo json_encode($res, JSON_UNESCAPED_UNICODE);
                    die();
                }
            }
            $data = $clientes->registrar(
                $identidad,
                $num_identidad,
                $nombre,
                $telefono,
                $correo,
                $direccion,
                1
            );
            if ($data > 0) {
                $res = array('msg' => 'CLIENTE REGISTRADO EXITOSAMENTE', 'type' => 'success');
            } else {
                $res = array('msg' => 'ERROR AL REGISTRAR', 'type' => 'error');
            }
        } else {
            $res = array('msg' => 'LA CEDÚLA / RÚC DEBE SER UNICO DEL CLIENTE ' . $num_identidad . ' ' . $nombre, 'type' => 'warning');
        }
        // echo $tipo_documento.' '.$cedula.' '.$nombre. "<br>";
    }
    echo json_encode($res, JSON_UNESCAPED_UNICODE);
}

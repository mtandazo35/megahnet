<?php
require 'vendor/autoload.php';

use PhpOffice\PhpSpreadsheet\IOFactory;




class Repetidoras extends Controller
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
        $data['title'] = 'Repetidoras';
        $data['script'] = 'repetidoras.js';
        $data['repetidoras'] = $this->model->getRepetidoras(1);
        $this->views->getView('repetidoras', 'index', $data);
    }
    public function listar()
    {
        $data = $this->model->getRepetidoras(1);
        for ($i = 0; $i < count($data); $i++) {
            $data[$i]['acciones'] = '<div>
            <button class="btn btn-info" type="button" onclick="editarRepetidoras(' . $data[$i]['id'] . ')"><i class="fas fa-edit text-white"></i></button>
            <button class="btn btn-danger" type="button" onclick="eliminarRepetidora(' . $data[$i]['id'] . ')"><i class="fas fa-trash"></i></button>
            </div>';

            $data[$i]['ip'] = '
            <a class="" target="_blank" href="http://' . $data[$i]['ip'] . '" >' . $data[$i]['ip'] . '</a>

            ';
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
        if (isset($_POST['marca']) && isset($_POST['ip'])) {
            $id = strClean($_POST['id']);
            $marca = trim(strClean($_POST['marca']));
            $ssid = trim(strClean($_POST['ssid']));
            $ip = trim(strClean($_POST['ip']));
            $canal = trim(strClean($_POST['canal']));
            $seguridad = trim(strClean($_POST['seguridad']));
            $frecuencia = trim(strClean($_POST['frecuencia']));
            //   print_r($chelectronica); exit;
            if (empty($marca)) {
                $res = array('msg' => 'LA MARCA ES REQUERIDO', 'type' => 'warning');
            } else if (empty($ssid)) {
                $res = array('msg' => 'EL SSID ES REQUERIDO', 'type' => 'warning');
            } else if (empty($ip)) {
                $res = array('msg' => 'LA IP ES REQUERIDO', 'type' => 'warning');
            } else if (empty($canal)) {
                $res = array('msg' => 'EL CANAL ES REQUERIDO', 'type' => 'warning');
            } else {
                if ($id == '') {
                    $verificarIdentidad = $this->model->getValidar('ssid', $ssid, 'registrar', 0);
                    if (empty($verificarIdentidad)) {
                       
                        $data = $this->model->registrar(
                            $marca,
                            $ssid,
                            $ip,
                            $canal,
                            $seguridad,
                            $frecuencia
                            
                        );
                        if ($data > 0) {
                            $res = array('msg' => 'REPETIDORA REGISTRADO EXITOSAMENTE', 'type' => 'success');
                        } else {
                            $res = array('msg' => 'ERROR AL REGISTRAR', 'type' => 'error');
                        }
                    } else {
                     $res = array('msg' => 'EL NOMBRE DE LA REPETIDORA DEBE SER UNICO', 'type' => 'warning');
                   }
                } else {
                    $verificarIdentidad = $this->model->getValidar('ssid', $ssid, 'actualizar', $id);
                   if (empty($verificarIdentidad)) {
                       
                        $data = $this->model->actualizar(
                            $marca,
                            $ssid,
                            $ip,
                            $canal,
                            $seguridad,
                            $frecuencia,
                            $id
                        );
                        if ($data > 0) {
                            $res = array('msg' => 'REPETIDORA ACTUALIZADO EXITOSAMENTE', 'type' => 'success');
                        } else {
                            $res = array('msg' => 'ERROR AL ACTUALIZAR', 'type' => 'error');
                        }
                    } else {
                       $res = array('msg' => 'EL NOMBRE DE LA REPETIDORA DEBE SER UNICO', 'type' => 'warning');
                   }
                }
            }
        } else {
            $res = array('msg' => 'ERROR DESCONOCIDO', 'type' => 'error');
        }
        echo json_encode($res, JSON_UNESCAPED_UNICODE);
        die();
    }

    public function eliminar($idRepetidora)
    {
        if (isset($_GET) && is_numeric($idRepetidora)) {
            $data = $this->model->eliminar(0, $idRepetidora);
            if ($data > 0) {
                $res = array('msg' => 'REPETIDORA ELIMINADO EXITOSAMENTE', 'type' => 'success');
            } else {
                $res = array('msg' => 'ERROR AL ELIMINAR', 'type' => 'error');
            }
        } else {
            $res = array('msg' => 'ERROR DESCONOCIDO', 'type' => 'error');
        }
        echo json_encode($res, JSON_UNESCAPED_UNICODE);
        die();
    }

    public function editar($idRepetidora)
    {
        $data = $this->model->editar($idRepetidora);
        echo json_encode($data, JSON_UNESCAPED_UNICODE);
        die();
    }

   
   
}


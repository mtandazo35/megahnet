<?php
class GrupoTrabajos extends Controller
{
    private $id_usuario;

    public function __construct()
    {

        session_start();
        parent::__construct();
        if (empty($_SESSION['id_usuario'])) {
            header('Location: ' . BASE_URL);
            exit;
        }
        $this->id_usuario = $_SESSION['id_usuario'];
        session_write_close(); // libera lock — read-only en adelante
    }
    public function index()
    {
        $data['title'] = 'Grupo Trabajos';
        $data['script'] = 'grupotrabajos.js';
        $this->views->getView('grupotrabajos', 'index', $data);
    }
    public function listar()
    {
        $data = $this->model->getGrupoTrabajos(1);
        for ($i = 0; $i < count($data); $i++) {

            $data[$i]['acciones'] = '<div>
            <button class="btn btn-info" type="button" onclick="editarGrupoTrabajos(' . $data[$i]['id'] . ')"><i class="fas fa-edit text-white"></i></button>
            <button class="btn btn-danger" type="button" onclick="eliminarGrupoTrabajos(' . $data[$i]['id'] . ')"><i class="fas fa-trash"></i></button>
            </div>';
            $data[$i]['responsable'] = '<span class="">'.$data[$i]['nombre'].''.$data[$i]['apellido'].'</span>';
            $dataCount = $this->model->getUsuariosCount($data[$i]['id']);
          // print_r($dataCount);
            if ($dataCount['cantidad'] > 0) {
                $data[$i]['empleados'] = '<span style="justify-content: center;
                display: flex;
                width: 50%;
                margin: auto;" class="badge bg-success">'.$dataCount['cantidad'].'</span>';
            }  else{
                $data[$i]['empleados'] = '<span style="justify-content: center;
                display: flex;
                width: 50%;
                margin: auto;" class="badge bg-success">0</span>';
            }
        }
       // print_r($data);
        echo json_encode($data, JSON_UNESCAPED_UNICODE);
        die();
    }
    public function registrar()
    {
        if (isset($_POST['descripcion']) ) {
            $id = strClean($_POST['id']);
            $observacion = trim((strClean($_POST['observacion'])) == '') ? '' : trim(strClean($_POST['observacion'])) ;
            $descripcion = trim(strClean($_POST['descripcion']));
            $idUsuario = strClean($_POST['idUsuario']);
//print_r($_POST); exit;
            if (empty($descripcion)) {
                $res = array('msg' => 'LA DESCRIPCIÓN ES REQUERIDO', 'type' => 'warning');
            } else {
                if ($id == '') {
                    //$verificarIdentidad = $this->model->getValidar('num_identidad', $num_identidad, 'registrar', 0);
                       
                        $data = $this->model->registrar(
                            $idUsuario,
                            $descripcion,
                            $observacion
                           
                        );
                        if ($data > 0) {
                            $res = array('msg' => 'GRUPO DE TRABAJO REGISTRADO EXITOSAMENTE', 'type' => 'success');
                        } else {
                            $res = array('msg' => 'ERROR AL REGISTRAR', 'type' => 'error');
                        }
                  
                } else {
                  
                        $data = $this->model->actualizar(
                            $idUsuario,
                            $descripcion,
                            $observacion,
                            $id
                        );
                        if ($data > 0) {
                            $res = array('msg' => 'GRUPO TRABAJO ACTUALIZADO EXITOSAMENTE', 'type' => 'success');
                        } else {
                            $res = array('msg' => 'ERROR AL ACTUALIZAR', 'type' => 'error');
                        }
                  
                }
            }
        } else {
            $res = array('msg' => 'ERROR DESCONOCIDO', 'type' => 'error');
        }
        echo json_encode($res, JSON_UNESCAPED_UNICODE);
        die();
    }

    public function eliminar($idGrupoTrabajo)
    {
        if (isset($_GET) && is_numeric($idGrupoTrabajo)) {
            $data = $this->model->eliminar(0, $idGrupoTrabajo);
            if ($data > 0) {
                $res = array('msg' => 'GRUPO TRABAJO ELIMINADO EXITOSAMENTE', 'type' => 'success');
            } else {
                $res = array('msg' => 'ERROR AL ELIMINAR', 'type' => 'error');
            }
        } else {
            $res = array('msg' => 'ERROR DESCONOCIDO', 'type' => 'error');
        }
        echo json_encode($res, JSON_UNESCAPED_UNICODE);
        die();
    }

    public function editar($idGrupoTrabajos)
    {
        $data = $this->model->editar($idGrupoTrabajos);
        echo json_encode($data, JSON_UNESCAPED_UNICODE);
        die();
    }

    public function inactivos()
    {
        $data['title'] = 'Grupo Trabajo Inactivos';
        $data['script'] = 'grupotrabajos-inactivos.js';
        $this->views->getView('grupotrabajos', 'inactivos', $data);
    }
    public function listarInactivos()
    {
        $data = $this->model->getGrupoTrabajos(0);
        for ($i = 0; $i < count($data); $i++) {
            $data[$i]['acciones'] = '<div>
            <button class="btn btn-success" type="button" onclick="restaurarGrupoTrabajo(' . $data[$i]['id'] . ')"><i class="fas fa-check-circle"></i></button>
            </div>';
        }
        echo json_encode($data, JSON_UNESCAPED_UNICODE);
        die();
    }
    public function restaurar($idGrupoTrabajos)
    {
        if (isset($_GET) && is_numeric($idGrupoTrabajos)) {
            $data = $this->model->eliminar(1, $idGrupoTrabajos);
            if ($data > 0) {
                $res = array('msg' => 'GRUPO TRABAJOS RESTAURADO EXITOSAMENTE', 'type' => 'success');
            } else {
                $res = array('msg' => 'ERROR AL RESTAURAR', 'type' => 'error');
            }
        } else {
            $res = array('msg' => 'ERROR DESCONOCIDO', 'type' => 'error');
        }
        echo json_encode($res, JSON_UNESCAPED_UNICODE);
        die();
    }
       //buscar contratos para el usuario
       public function buscar()
       {
           $array = array();
           $valor = strClean($_GET['term']);
           $data = $this->model->buscarPorNombre($valor);
           foreach ($data as $row) {
               $result['id'] = $row['id'];
               $result['label'] = $row['id'] . ' ' . $row['responsable'] ;
               $result['responsable'] = $row['responsable'];
             
   
   
               array_push($array, $result);
           }
           echo json_encode($array, JSON_UNESCAPED_UNICODE);
           die();
       }
}

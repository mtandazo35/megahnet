<?php
require 'vendor/autoload.php';

use Dompdf\Dompdf;

class Casos extends Controller
{
    private $id_usuario;
    private $rol_usuario;

    public function __construct()
    {
        parent::__construct();
        session_start();
        if (empty($_SESSION['id_usuario'])) {
            header('Location: ' . BASE_URL);
            exit;
        }

        $this->id_usuario = $_SESSION['id_usuario'];
        $this->rol_usuario = $_SESSION['rol'];

    }
    public function index()
    {
        $data['title'] = 'Casos';
        $data['script'] = 'casos.js';
        $data['carrito'] = 'posCasos';
        $this->views->getView('casos', 'index', $data);
    }
    public function registrarCasos()
    {
        $json = file_get_contents('php://input');
        $datos = json_decode($json, true);
        $total = 0;
        //  print_r($datos); exit;
        if (!empty($datos)) {
            $fecha = date('Y-m-d');
            $hora = date('H:i:s');

            $id = $datos['id'];
            $idContrato = $datos['idContrato'];
            $idGrupoTrabajo = $datos['idGrupoTrabajo'];
            $trabajoRealizado = trim($datos['trabajoRealizado']);
            $problemaReportado = trim($datos['problemaReportado']);
            $observacion = trim($datos['observacion']);
            $estado = $datos['estado'];

            $getClientes = $this->model->getInfoCliente($idContrato);
            //$tipoBanco = $datos['tipoBanco'];
            //$cuentaBancaria = $datos['cuentaBancaria'];
            if (empty($idContrato)) {
                $res = array('msg' => 'EL CONTRATO ES REQUERIDO', 'type' => 'warning');
            } else if (empty($idGrupoTrabajo)) {
                $res = array('msg' => 'EL GRUPO TRABAJO ES REQUERIDO', 'type' => 'warning');
            } else if (empty($problemaReportado)) {
                $res = array('msg' => 'EL PROBLEMA AH REPORTAR ES REQUERIDO', 'type' => 'warning');
            } else {
                if ($id == '') {

                    $caso = $this->model->registrarCaso(
                        $fecha,
                        $hora,
                        $idContrato,
                        $this->id_usuario,
                        $idGrupoTrabajo,
                        $problemaReportado,
                        $estado
                    );
                    if ($caso > 0) {

                        $resWathsapp = 'https://web.whatsapp.com/send?text=Buen Dia! Se ah creado un nuevo caso en estado:(INGRESADO), problema reportado:('.$problemaReportado.'), https://www.google.com.ar/maps/place/  &phone=+593'.$getClientes['telefono'].'&abid=+593'.$getClientes['telefono'].'';
                        $res = array('msg' => 'CASO GENERADO EXITOSAMENTE', 'type' => 'success', 'idCaso' => $caso, 'whatsapp' => $resWathsapp);


                    } else {
                        $res = array('msg' => 'ERROR AL GENERAR EL CASO', 'type' => 'error');
                    }
                } else {

                    $contrato = $this->model->actualizarCaso(

                        $idGrupoTrabajo,
                        $problemaReportado,
                        $trabajoRealizado,
                        $observacion,
                        $estado,
                        $id
                    );
                    if ($contrato > 0) {

                        $res = array('msg' => 'CASO ACTUALIZADO EXITOSAMENTE', 'type' => 'success', 'idCaso' => $id);
                        $resWathsapp = 'https://web.whatsapp.com/send?text=Buen Dia! Su caso se ah actualizado en estado:('.$estado.'), problema reportado:('.$problemaReportado.'), Trabajo Realizado:('.$problemaReportado.') &phone=+593'.$getClientes['telefono'].'&abid=+593'.$getClientes['telefono'].'';

                        // print_r($id);exit;
                    } else {
                        $res = array('msg' => 'ERROR AL ACTUALIZAR EL CASO', 'type' => 'error');
                    }
                }
            }
        } else {
            $res = array('msg' => 'NO EXISTEN DATOS', 'type' => 'warning');
        }
        echo json_encode($res);
        die();
    }

    public function reporte($datos)
    {
        ob_start();
        $array = explode(',', $datos);
        $tipo = $array[0];
        $idCaso = $array[1];

        $data['title'] = 'Casos';
        $data['empresa'] = $this->model->getEmpresa();
        $data['caso'] = $this->model->getCaso($idCaso);
        $data['grupoTrabajo'] = $this->model->getGrupoAsignado($idCaso);

        // print_r( $data['contrato']); exit;
        if (empty($data['caso'])) {
            echo 'Pagina no Encontrada';
            exit;
        }
        $this->views->getView('casos', $tipo, $data);
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
        $dompdf->stream('Caso' . $idCaso . '.pdf', array('Attachment' => false));
    }

    public function listar()
    {
        $data = $this->model->getCasos($this->rol_usuario,$this->id_usuario);
        // $producto = json_decode($data['productos']);

        for ($i = 0; $i < count($data); $i++) {
            $data[$i]['acciones'] = '<a class="btn btn-danger" href="#" onclick="verReporte(' . $data[$i]['id'] . ')"><i class="fas fa-file-pdf"></i></a>
            <a class="btn btn-warning" href="#" onclick="Editar(' . $data[$i]['id'] . ')"><i class="fas fa-edit text-white"></i></a>
            ';
            //print_r($data[$i]['grupo_asignado']); exit;

            $responsable = $this->model->getresponsable($data[$i]['grupo_asignado']);

            $data[$i]['responsable'] = '<span class="">'.$responsable[0]['responsable'].'</span>';

            //print_r( $responsable); exit;

           // $productos = json_decode($data[$i]['productos'], true);

           
          


          
        }
        echo json_encode($data, JSON_UNESCAPED_UNICODE);
        die();
    }
    
    public function editar($idCaso)
    {
        $data = $this->model->editar($idCaso);
       // $data['producto'] = json_decode($data['productos']);
        //print_r($data['producto']); exit;
        echo json_encode($data, JSON_UNESCAPED_UNICODE);
        die();
    }


    //buscar contratos para el caso
    public function buscar()
    {
        $array = array();
        $valor = strClean($_GET['term']);
        $data = $this->model->buscarPorNombre($valor);
        foreach ($data as $row) {
            $result['id'] = $row['idContrato'];
            $result['label'] = $row['idContrato'] . ' ' . $row['nombre'] . ' ' . $row['direccion'];
            $result['direccion'] = $row['direccion'];
            $result['coordenada'] = $row['coordenada'];
            $result['comentario'] = $row['comentario'];


            array_push($array, $result);
        }
        echo json_encode($array, JSON_UNESCAPED_UNICODE);
        die();
    }
    public function buscarGrupoTrabajo()
    {
        $array = array();
        $valor = strClean($_GET['term']);
        $data = $this->model->buscarPorNombreGrupoTrabajo($valor);
        foreach ($data as $row) {
            $result['id'] = $row['id'];
            $result['label'] = $row['id'] . ' ' . $row['descripcion'] . ' ' . $row['responsable'];
            $result['descripcion'] = $row['descripcion'];
            $result['responsable'] = $row['responsable'];


            array_push($array, $result);
        }
        echo json_encode($array, JSON_UNESCAPED_UNICODE);
        die();
    }
}

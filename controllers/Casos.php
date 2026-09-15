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
                        // WA se envia desde notificarCaso (preview+plantilla con empresa de BD).
                        $res = array(
                            'msg'             => 'CASO GENERADO EXITOSAMENTE',
                            'type'            => 'success',
                            'idCaso'          => $caso,
                            'estado'          => $estado,
                            'telefonoCliente' => $getClientes['telefono'] ?? '',
                        );
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
                        // WA se envia desde notificarCaso (preview+plantilla con empresa de BD).
                        $res = array(
                            'msg'             => 'CASO ACTUALIZADO EXITOSAMENTE',
                            'type'            => 'success',
                            'idCaso'          => $id,
                            'estado'          => $estado,
                            'telefonoCliente' => $getClientes['telefono'] ?? '',
                        );
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

    public function notificarCaso($idCaso = 0)
    {
        header('Content-Type: application/json; charset=utf-8');
        if (empty($_SESSION['id_usuario'])) { echo json_encode(['ok'=>false,'msg'=>'No autorizado']); exit; }
        $idCaso = (int)$idCaso;
        if ($idCaso <= 0) { echo json_encode(['ok'=>false,'msg'=>'ID invalido']); exit; }

        $caso = $this->model->getCaso($idCaso);
        if (!$caso) { echo json_encode(['ok'=>false,'msg'=>'Caso no encontrado']); exit; }

        $tel = preg_replace('/[^0-9]/', '', (string)($caso['telefono'] ?? ''));
        if (empty($tel)) { echo json_encode(['ok'=>false,'msg'=>'Cliente sin telefono']); exit; }
        if (strlen($tel) === 10 && $tel[0] === '0') $tel = '593' . substr($tel, 1);
        elseif (strlen($tel) === 9) $tel = '593' . $tel;

        $bodyIn = json_decode(file_get_contents('php://input'), true) ?: [];
        $estado = strtoupper(trim((string)($caso['estado'] ?? '')));
        $tipoForzado = $_GET['tipo'] ?? ($bodyIn['tipo'] ?? '');
        if ($tipoForzado === 'creado') {
            $plantillaKey = 'whatsapp_caso_creado';
        } elseif ($tipoForzado === 'actualizado') {
            $plantillaKey = 'whatsapp_caso_actualizado';
        } else {
            $plantillaKey = ($estado === 'INGRESADO') ? 'whatsapp_caso_creado' : 'whatsapp_caso_actualizado';
        }

        $vars = [
            'cliente_nombre'   => trim($caso['nombre'] ?? ''),
            'cliente_telefono' => $tel,
            'caso_id'          => $idCaso,
            'caso_estado'      => $estado,
            'caso_problema'    => trim($caso['problema_reportado'] ?? ''),
            'caso_trabajo'     => trim($caso['trabajo_realizado'] ?? ''),
        ];

        $mensajeCustom = isset($bodyIn['mensaje']) ? trim((string)$bodyIn['mensaje']) : '';
        if ($mensajeCustom !== '') {
            $cuerpo = $mensajeCustom;
        } else {
            $cuerpo = '';
            if (function_exists('renderPlantilla')) {
                $tpl = renderPlantilla($plantillaKey, $vars);
                $cuerpo = is_array($tpl) ? ($tpl['cuerpo'] ?? '') : '';
            }
            if (empty($cuerpo)) {
                $cuerpo = ($plantillaKey === 'whatsapp_caso_creado')
                    ? "Buen Dia! Se ha creado un nuevo caso\nEstado: INGRESADO\nProblema: " . $vars['caso_problema']
                    : "Buen Dia! Su caso se ha actualizado\nEstado: " . $vars['caso_estado'] . "\nProblema: " . $vars['caso_problema'] . "\nTrabajo Realizado: " . $vars['caso_trabajo'];
            }
        }

        $waBase = '';
        if (function_exists('servicioConfig')) {
            $svc = servicioConfig('whatsapp_api');
            $waBase = $svc['base_url'] ?? '';
        }
        if (empty($waBase)) {
            $f = ROOT_PATH . '/storage/alertas-config.json';
            if (file_exists($f)) {
                $j = @json_decode(@file_get_contents($f), true);
                $waBase = $j['wa_api']['base_url'] ?? '';
            }
        }
        if (empty($waBase)) $waBase = 'http://127.0.0.1:3005';
        $waBase = rtrim($waBase, '/');

        $sessionId = '';
        $f = ROOT_PATH . '/storage/alertas-config.json';
        if (file_exists($f)) {
            $j = @json_decode(@file_get_contents($f), true);
            $sessionId = $j['wa_api']['session_id'] ?? '';
        }
        if (empty($sessionId)) { echo json_encode(['ok'=>false,'msg'=>'No hay sesion WhatsApp vinculada']); exit; }

        if (!empty($_GET['preview']) || !empty($_POST['preview'])) {
            echo json_encode([
                'ok'=>true, 'preview'=>true,
                'mensaje'=>$cuerpo,
                'telefono'=>$tel,
                'tipo'=>($plantillaKey === 'whatsapp_caso_creado' ? 'creado' : 'actualizado'),
                'plantilla_key'=>$plantillaKey,
            ], JSON_UNESCAPED_UNICODE);
            exit;
        }

        $payload = [
            'sessionId' => $sessionId,
            'number'    => $tel,
            'message'   => $cuerpo,
        ];
        $ch = curl_init($waBase . '/api/whatsapp/send?fastMode=true');
        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_POST => true,
            CURLOPT_POSTFIELDS => json_encode($payload),
            CURLOPT_HTTPHEADER => ['Content-Type: application/json'],
            CURLOPT_TIMEOUT => 15,
            CURLOPT_CONNECTTIMEOUT => 5,
            CURLOPT_SSL_VERIFYPEER => false,
        ]);
        $resp = curl_exec($ch);
        $code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);
        // El WA API responde HTTP 201 con {"status":"ok","message":"Mensaje enviado a ..."}
        // El check antiguo (code===200 && success) siempre fallaba aunque el mensaje SI llegaba.
        // Aceptamos cualquier 2xx, igual que Creditos.php / Helpers / Notificaciones.
        $j = @json_decode($resp, true);
        $ok = ($code >= 200 && $code < 300);
        if ($ok) {
            echo json_encode(['ok'=>true,'telefono'=>$tel,'http'=>$code], JSON_UNESCAPED_UNICODE);
        } else {
            echo json_encode(['ok'=>false,'msg'=>'No se pudo enviar','http'=>$code,'resp'=>$resp], JSON_UNESCAPED_UNICODE);
        }
        exit;
    }


    //buscar contratos para el caso
    public function buscar()
    {
        $array = array();
        $valor = trim(strClean($_GET['term'] ?? ''));
        // Termino muy corto: no consultar (evita devolver toda la tabla)
        if (mb_strlen($valor) < 2) {
            echo json_encode($array);
            die();
        }
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
        $valor = trim(strClean($_GET['term'] ?? ''));
        // Termino muy corto: no consultar (evita devolver toda la tabla)
        if (mb_strlen($valor) < 2) {
            echo json_encode($array);
            die();
        }
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

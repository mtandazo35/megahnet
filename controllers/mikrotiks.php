<?php
require 'vendor/autoload.php';

//use PhpOffice\PhpSpreadsheet\IOFactory;

class Mikrotiks extends Controller
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
        $data['title'] = 'Mikrotiks';
        $data['script'] = 'mikrotiks.js';
        $data['validacion'] = 'validacion.js';

        $data['mikrotik'] = $this->model->getMikrotiks(1);
        $this->views->getView('mikrotiks', 'index', $data);
    }
    public function listar()
    {
        $data = $this->model->getMikrotiks(1);
        for ($i = 0; $i < count($data); $i++) {
            $data[$i]['acciones'] = '<div>
            <button class="btn btn-info" type="button" onclick="editarMikrotik(' . $data[$i]['id'] . ')"><i class="fas fa-edit text-white"></i></button>
            <button class="btn btn-danger" type="button" onclick="eliminarMikrotik(' . $data[$i]['id'] . ')"><i class="fas fa-trash"></i></button>
            </div>';


        }


        echo json_encode($data, JSON_UNESCAPED_UNICODE);
        die();
    }

    public function registrar()
    {
        if (isset($_POST['nombre']) && isset($_POST['ip']) && isset($_POST['usuario'])) {
            $id = strClean($_POST['id']);
            $nombre = trim(strClean($_POST['nombre']));
            $ip = trim(strClean($_POST['ip']));
            $usuario = trim(strClean($_POST['usuario']));
            $clave = $_POST['clave'];
            $puerto = trim((strClean($_POST['puerto'])));

        

            //print_r($clave); exit;

            if (empty($nombre)) {
                $res = array('msg' => 'EL NOMBRE DEL MIKROTIK ES REQUERIDO', 'type' => 'warning');
            } else if (empty($ip)) {
                $res = array('msg' => 'LA IP DEL MIKROTIK ES REQUERIDO', 'type' => 'warning');
            } else if (empty($usuario)) {
                $res = array('msg' => 'EL USUARIO ES REQUERIDO', 'type' => 'warning');
            } else if (empty($clave)) {
                $res = array('msg' => 'LA CLAVE DEL MIKROTIK ES REQUERIDO', 'type' => 'warning');
            } else if (empty($puerto)) {
                $res = array('msg' => 'EL PUERTO ES REQUERIDO', 'type' => 'warning');
            } else {
                if ($id == '') {
                    $verificarIp = $this->model->getValidar('ip', $ip, 'registrar', 0);
                    if (empty($verificarIp)) {

                        $data = $this->model->registrar(
                            $nombre,
                            $ip,
                            $usuario,
                            $clave,
                            $puerto

                        );
                        if ($data > 0) {
                            $res = array('msg' => 'MIKROTIK REGISTRADO EXITOSAMENTE', 'type' => 'success');
                        } else {
                            $res = array('msg' => 'ERROR AL REGISTRAR', 'type' => 'error');
                        }
                    } else {
                        $res = array('msg' => 'LA IP DEL MIKROTIK DEBE SER UNICO', 'type' => 'warning');
                    }
                } else {
                    $verificarIp = $this->model->getValidar('ip', $ip, 'actualizar', $id);
                    if (empty($verificarIp)) {

                        $data = $this->model->actualizar(
                            $nombre,
                            $ip,
                            $usuario,
                            $clave,
                            $puerto,
                            $id
                        );
                        if ($data > 0) {
                            $res = array('msg' => 'MIKROTIK ACTUALIZADO EXITOSAMENTE', 'type' => 'success');
                        } else {
                            $res = array('msg' => 'ERROR AL ACTUALIZAR', 'type' => 'error');
                        }
                    } else {
                        $res = array('msg' => 'LA IP DEL MIKROTIK DEBE SER UNICO', 'type' => 'warning');
                    }
                }
            }
        } else {
            $res = array('msg' => 'ERROR DESCONOCIDO', 'type' => 'error');
        }
        echo json_encode($res, JSON_UNESCAPED_UNICODE);
        die();
    }

    public function eliminar($idMikrotik)
    {
        if (isset($_GET) && is_numeric($idMikrotik)) {
            $data = $this->model->eliminar(0, $idMikrotik);
            if ($data > 0) {
                $res = array('msg' => 'MIKROTIK ELIMINADO EXITOSAMENTE', 'type' => 'success');
            } else {
                $res = array('msg' => 'ERROR AL ELIMINAR', 'type' => 'error');
            }
        } else {
            $res = array('msg' => 'ERROR DESCONOCIDO', 'type' => 'error');
        }
        echo json_encode($res, JSON_UNESCAPED_UNICODE);
        die();
    }

    public function editar($idMikrotik)
    {
        $data = $this->model->editar($idMikrotik);

     
        echo json_encode($data, JSON_UNESCAPED_UNICODE);
        die();
    }
    public function habilitarConexion($idMikrotik)
    {

        //  print_r($_SESSION);
        $data = $this->model->editar($idMikrotik);

        $claveEncriptada = base64_decode($data['clave']);
        $iv_dec = substr($claveEncriptada, 0, openssl_cipher_iv_length(METODOASIC));
        $encrypted_dec = substr($claveEncriptada, openssl_cipher_iv_length(METODOASIC));
        $decrypted = openssl_decrypt($encrypted_dec, METODOASIC, KEY, 0, $iv_dec);




        require 'libraries/mikrotik/routeros_api.class.php';
        if (session_status() === PHP_SESSION_NONE) session_start();

        $API = new RouterosAPI();

        // Obtener datos del MikroTik
        $routerId = $data['id'];          // ID único en tu base de datos
        $ip       = $data['ip'];
        $username = $data['usuario'];
        $password = $decrypted;
        $puerto   = $data['puerto'] ?? 8728;

        $API->port = $puerto;

        // Inicializar estructura de routers si no existe
        if (!isset($_SESSION['mikrotik']) || !is_array($_SESSION['mikrotik'])) {
            $_SESSION['mikrotik'] = [];
        }

        // Validar si ya hay una conexión activa con ese router
        if (isset($_SESSION['mikrotik'][$routerId])) {
            $res = [
                'msg'     => "YA EXISTE UNA CONEXIÓN ACTIVA PARA EL ROUTER ID $username",
                'type'    => 'info',
                'ip'      => $ip,
                'usuario' => $username
            ];
        } else {
            // Intentar conexión
            if ($API->connect($ip, $username, $password)) {
                if ($API->connected) {
                    // Guardar sesión
                    $_SESSION['mikrotik'][$routerId] = [
                        'id' => $routerId,
                        'ip'     => $ip,
                        'user'   => $username,
                        'pass'   => $password,
                        'puerto'   => $puerto

                    ];

                    // Registrar en tu modelo
                    $this->model->actualizarSesion('CONEXION EXITOSA', $routerId);

                    $res = [
                        'msg'     => 'CONEXIÓN ESTABLECIDA CORRECTAMENTE',
                        'ip'      => $ip,
                        'usuario' => $username,
                        'type'    => 'success'
                    ];
                } else {
                    $res = ['msg' => 'NO SE PUDO ESTABLECER CONEXIÓN (CERRADA)', 'type' => 'error'];
                }
            } else {
                $this->model->actualizarSesion('CONEXION FALLIDA', $routerId);
                $res = ['msg' => 'CONEXION FALLIDA', 'type' => 'error'];
            }
        }

        echo json_encode($res, JSON_UNESCAPED_UNICODE);


        die();
    }
    public function desabilitarConexion($idMikrotik)
    {
        $data = $this->model->editar($idMikrotik);
        if (session_status() === PHP_SESSION_NONE) session_start();

        $routerId = $data['id'] ?? null;

        if (!$routerId) {


            $res = array(
                'msg' => 'ID de router no proporcionado',
                'type' => 'warning'
            );
            exit;
        }

        // Verificar si hay una sesión activa y corresponde al mismo ID
        if (isset($_SESSION['mikrotik']) && is_array($_SESSION['mikrotik']) && isset($_SESSION['mikrotik'][$routerId])) {

            $this->model->actualizarSesion(NULL, $routerId);

            // Eliminar sesión
            unset($_SESSION['mikrotik'][$routerId]);
            $res = array(
                'msg' => "Router ID $routerId desconectado correctamente",
                'type' => 'success'

            );
        } else {
            $res = array(
                'msg' => 'No hay una conexión activa para el Router ID $routerId',
                'type' => 'info'

            );
        }
        echo json_encode($res, JSON_UNESCAPED_UNICODE);
        die();
    }
    public function inactivos()
    {
        $data['title'] = 'Mikrotiks  Inactivos';
        $data['script'] = 'mikrotiks-inactivos.js';
        $this->views->getView('mikrotiks', 'inactivos', $data);
    }
    public function listarInactivos()
    {
        $data = $this->model->getMikrotiks(0);
        for ($i = 0; $i < count($data); $i++) {
            $data[$i]['acciones'] = '<div>
            <button class="btn btn-success" type="button" onclick="restaurarMikrotik(' . $data[$i]['id'] . ')"><i class="fas fa-check-circle"></i></button>
            </div>';
        }
        echo json_encode($data, JSON_UNESCAPED_UNICODE);
        die();
    }
    public function restaurar($idMikrotik)
    {
        if (isset($_GET) && is_numeric($idMikrotik)) {
            $data = $this->model->eliminar(1, $idMikrotik);
            if ($data > 0) {
                $res = array('msg' => 'MIKROTIK RESTAURADO EXITOSAMENTE', 'type' => 'success');
            } else {
                $res = array('msg' => 'ERROR AL RESTAURAR', 'type' => 'error');
            }
        } else {
            $res = array('msg' => 'ERROR DESCONOCIDO', 'type' => 'error');
        }
        echo json_encode($res, JSON_UNESCAPED_UNICODE);
        die();
    }
}

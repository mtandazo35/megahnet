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
            // Badge de estado de conexion
            $estado = $data[$i]['estado_conexion'] ?? 'unknown';
            $ult    = $data[$i]['ultima_verificacion'] ?? null;
            $err    = $data[$i]['ultimo_error'] ?? '';
            $tip    = $ult ? 'Verificado: ' . $ult : 'Sin verificar';
            if ($estado === 'offline' && $err !== '') $tip .= "\n" . $err;
            $tip = htmlspecialchars($tip, ENT_QUOTES, 'UTF-8');

            if ($estado === 'online') {
                $badge = '<span class="badge rounded-pill bg-success" title="' . $tip . '"><i class="bx bx-check-circle"></i> Conectado</span>';
            } elseif ($estado === 'offline') {
                $badge = '<span class="badge rounded-pill bg-danger" title="' . $tip . '"><i class="bx bx-x-circle"></i> Sin conexión</span>';
            } else {
                $badge = '<span class="badge rounded-pill bg-secondary" title="' . $tip . '"><i class="bx bx-question-mark"></i> Desconocido</span>';
            }
            $data[$i]['estado_badge'] = $badge;

            $data[$i]['acciones'] = '<div class="d-flex gap-1">'
                . '<button class="btn btn-sm btn-outline-success" type="button" title="Verificar conexion" onclick="verificarConexionMikrotik(' . $data[$i]['id'] . ')"><i class="bx bx-plug"></i></button>'
                . '<button class="btn btn-sm btn-info" type="button" title="Editar" onclick="editarMikrotik(' . $data[$i]['id'] . ')"><i class="fas fa-edit text-white"></i></button>'
                . '<button class="btn btn-sm btn-danger" type="button" title="Eliminar" onclick="eliminarMikrotik(' . $data[$i]['id'] . ')"><i class="fas fa-trash"></i></button>'
                . '</div>';
        }
        echo json_encode($data, JSON_UNESCAPED_UNICODE);
        die();
    }

    /**
     * Verifica un MikroTik especifico (por id de BD) y actualiza su estado.
     * Devuelve JSON con resultado.
     */
    public function verificarConexion($idMikrotik)
    {
        if (empty($idMikrotik) || !is_numeric($idMikrotik)) {
            echo json_encode(['msg' => 'ID INVALIDO', 'type' => 'error'], JSON_UNESCAPED_UNICODE);
            die();
        }
        $row = $this->model->editar((int)$idMikrotik);
        if (empty($row)) {
            echo json_encode(['msg' => 'MIKROTIK NO ENCONTRADO', 'type' => 'error'], JSON_UNESCAPED_UNICODE);
            die();
        }
        $resultado = $this->_probarConexionRouter(
            $row['ip'], $row['usuario'], $row['clave'], (int)($row['puerto'] ?: 8728), true
        );
        // Actualizar BD
        $this->model->actualizarEstadoConexion(
            (int)$idMikrotik,
            $resultado['ok'] ? 'online' : 'offline',
            $resultado['ok'] ? null : ($resultado['error'] ?? null)
        );
        echo json_encode([
            'msg'    => $resultado['msg'],
            'type'   => $resultado['ok'] ? 'success' : 'error',
            'estado' => $resultado['ok'] ? 'online' : 'offline',
            'info'   => $resultado['info'] ?? null,
        ], JSON_UNESCAPED_UNICODE);
        die();
    }

    /**
     * Verifica TODOS los MikroTiks activos. Pensado para cron.
     * Acepta acceso por CLI o con header X-Cron-Secret (definido en .env).
     */
    public function verificarTodos()
    {
        // Permitir CLI sin sesion
        $isCli = (php_sapi_name() === 'cli');
        // Permitir HTTP con secret
        $hdr   = $_SERVER['HTTP_X_CRON_SECRET'] ?? ($_GET['secret'] ?? '');
        $cronSecret = getenv('CRON_SECRET') ?: '';
        $autorizado = $isCli || ($cronSecret !== '' && hash_equals($cronSecret, $hdr));
        if (!$autorizado && empty($_SESSION['rol'])) {
            http_response_code(403);
            echo "Forbidden\n";
            die();
        }

        $list = $this->model->getMikrotiksParaVerificar();
        $resumen = ['total' => count($list), 'online' => 0, 'offline' => 0, 'detalle' => []];
        foreach ($list as $row) {
            $r = $this->_probarConexionRouter(
                $row['ip'], $row['usuario'], $row['clave'], (int)($row['puerto'] ?: 8728), true
            );
            $estado = $r['ok'] ? 'online' : 'offline';
            $this->model->actualizarEstadoConexion(
                (int)$row['id'], $estado, $r['ok'] ? null : ($r['error'] ?? null)
            );
            $resumen[$estado]++;
            $resumen['detalle'][] = [
                'id' => $row['id'], 'nombre' => $row['nombre'], 'ip' => $row['ip'],
                'estado' => $estado, 'msg' => $r['msg']
            ];
        }
        if ($isCli) {
            echo "[" . date('Y-m-d H:i:s') . "] Verificacion masiva: "
                . $resumen['total'] . " total, "
                . $resumen['online'] . " online, "
                . $resumen['offline'] . " offline\n";
        } else {
            echo json_encode($resumen, JSON_UNESCAPED_UNICODE);
        }
        die();
    }

    /**
     * Helper interno: prueba conexion al router. La clave puede venir
     * encriptada (asume base64+openssl_decrypt si llega encriptada).
     */
    private function _probarConexionRouter($ip, $usuario, $claveRaw, $puerto, $claveEncriptada = false)
    {
        require_once 'libraries/mikrotik/routeros_api.class.php';

        // Decriptar si viene de BD
        $clave = $claveRaw;
        if ($claveEncriptada) {
            $bin   = base64_decode($claveRaw);
            $ivLen = openssl_cipher_iv_length(METODOASIC);
            $iv    = substr($bin, 0, $ivLen);
            $enc   = substr($bin, $ivLen);
            $clave = (string)openssl_decrypt($enc, METODOASIC, KEY, 0, $iv);
        }

        $API = new RouterosAPI();
        $API->port    = $puerto > 0 ? $puerto : 8728;
        $API->timeout = 4;

        $t0 = microtime(true);
        $ok = @$API->connect($ip, $usuario, $clave);
        $latency = (int)round((microtime(true) - $t0) * 1000);

        if (!$ok || !$API->connected) {
            return [
                'ok'      => false,
                'msg'     => 'No se pudo conectar (timeout o credenciales).',
                'error'   => 'CONNECT_FAILED',
                'latency' => $latency,
            ];
        }

        $identidad = ''; $version = ''; $boardName = '';
        try {
            $idRes = $API->comm('/system/identity/print');
            if (is_array($idRes) && isset($idRes[0]['name'])) $identidad = $idRes[0]['name'];
            $rRes = $API->comm('/system/resource/print');
            if (is_array($rRes) && isset($rRes[0])) {
                $version   = $rRes[0]['version']    ?? '';
                $boardName = $rRes[0]['board-name'] ?? '';
            }
        } catch (\Throwable $e) { /* ignore */ }

        $API->disconnect();

        $partes = [];
        if ($identidad !== '') $partes[] = 'Identidad: ' . $identidad;
        if ($boardName !== '') $partes[] = 'Board: '     . $boardName;
        if ($version   !== '') $partes[] = 'RouterOS: '  . $version;
        $partes[] = $latency . ' ms';

        return [
            'ok'      => true,
            'msg'     => 'CONEXIÓN EXITOSA — ' . implode(' · ', $partes),
            'latency' => $latency,
            'info'    => [
                'identidad' => $identidad,
                'version'   => $version,
                'board'     => $boardName,
                'latency'   => $latency,
            ],
        ];
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
    /**
     * Probar conexion al MikroTik con credenciales del form (sin guardar).
     * Usado por el boton "Probar conexion" del modal y por la verificacion
     * automatica al hacer click en Registrar.
     */
    public function probarConexion()
    {
        if ($_SESSION['rol'] == 2) {
            echo json_encode(['msg' => 'SIN PERMISOS', 'type' => 'warning'], JSON_UNESCAPED_UNICODE);
            die();
        }
        $ip       = trim((string)($_POST['ip']      ?? ''));
        $usuario  = trim((string)($_POST['usuario'] ?? ''));
        $clave    = (string)($_POST['clave']   ?? '');
        $puerto   = (int)   ($_POST['puerto']  ?? 8728);
        if ($puerto <= 0) $puerto = 8728;

        // Si la clave viene vacia (modo edicion sin cambiar pass), recuperar de BD
        $claveEnc = false;
        if ($clave === '' && !empty($_POST['id'])) {
            $row = $this->model->editar((int)$_POST['id']);
            if (!empty($row['clave'])) {
                $clave = $row['clave'];
                $claveEnc = true;
            }
        }
        if ($ip === '' || $usuario === '') {
            echo json_encode(['msg' => 'IP Y USUARIO SON REQUERIDOS', 'type' => 'warning'], JSON_UNESCAPED_UNICODE);
            die();
        }

        $r = $this->_probarConexionRouter($ip, $usuario, $clave, $puerto, $claveEnc);
        echo json_encode([
            'msg'  => $r['msg'],
            'type' => $r['ok'] ? 'success' : 'error',
            'info' => $r['info'] ?? null,
        ], JSON_UNESCAPED_UNICODE);
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

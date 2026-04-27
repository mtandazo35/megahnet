<?php
class Notificaciones extends Controller
{
    private $id_usuario;

    public function __construct()
    {
        parent::__construct();
        session_start();
        if (empty($_SESSION['id_usuario'])) { header('Location: ' . BASE_URL); exit; }
        $this->id_usuario = $_SESSION['id_usuario'];
    }

    private function logFile() { return ROOT_PATH . '/storage/alertas.jsonl'; }
    private function configFile() { return ROOT_PATH . '/storage/alertas-config.json'; }
    private function plantillasFile() { return ROOT_PATH . '/storage/plantillas.json'; }

    public function index()
    {
        $data['title'] = 'Notificaciones';
        $data['script'] = 'notificaciones.js';
        $data['config'] = $this->cargarConfig();
        $data['plantillas'] = $this->cargarPlantillas();
        $this->views->getView('notificaciones', 'index', $data);
    }

    public function listar()
    {
        header('Content-Type: application/json; charset=utf-8');
        $f = $this->logFile();
        if (!file_exists($f)) { echo json_encode([]); return; }
        $lineas = @file($f, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES) ?: [];
        $items = [];
        foreach (array_reverse($lineas) as $i => $line) {
            $r = @json_decode($line, true);
            if (!is_array($r)) continue;
            $cuerpoCorto = strip_tags($r['cuerpo'] ?? '');
            $cuerpoCorto = trim(preg_replace('/\s+/', ' ', $cuerpoCorto));
            if (mb_strlen($cuerpoCorto) > 200) $cuerpoCorto = mb_substr($cuerpoCorto, 0, 200) . '...';
            $statusBadge = '';
            switch ($r['status'] ?? '') {
                case 'OK':              $statusBadge = '<span class="badge bg-success">ENVIADO</span>'; break;
                case 'FAIL':            $statusBadge = '<span class="badge bg-danger">FALLO</span>'; break;
                case 'NO_DESTINO':      $statusBadge = '<span class="badge bg-warning text-dark">SIN DESTINATARIO</span>'; break;
                case 'NO_PHPMAILER':    $statusBadge = '<span class="badge bg-secondary">SIN PHPMAILER</span>'; break;
                case 'TIPO_DESACTIVADO':$statusBadge = '<span class="badge bg-secondary">TIPO OFF</span>'; break;
                default:                $statusBadge = '<span class="badge bg-secondary">' . htmlspecialchars($r['status'] ?? '?') . '</span>';
            }
            $items[] = [
                'ts'      => $r['ts'] ?? '',
                'tipo'    => '<span class="badge bg-info">' . htmlspecialchars($r['tipo'] ?? '') . '</span>',
                'asunto'  => htmlspecialchars($r['asunto'] ?? ''),
                'destino' => htmlspecialchars($r['destino'] ?? ''),
                'status'  => $statusBadge,
                'cuerpo'  => htmlspecialchars($cuerpoCorto),
                'idx'     => count($lineas) - 1 - $i,
            ];
        }
        echo json_encode($items, JSON_UNESCAPED_UNICODE);
    }

    public function ver($idx = 0)
    {
        header('Content-Type: application/json; charset=utf-8');
        $f = $this->logFile();
        if (!file_exists($f)) { echo json_encode(['ok' => false]); return; }
        $lineas = @file($f, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES) ?: [];
        $idx = (int)$idx;
        if ($idx < 0 || $idx >= count($lineas)) { echo json_encode(['ok' => false]); return; }
        $r = @json_decode($lineas[$idx], true);
        if (!is_array($r)) { echo json_encode(['ok' => false]); return; }
        echo json_encode(['ok' => true, 'data' => $r], JSON_UNESCAPED_UNICODE);
    }

    public function vaciar()
    {
        if (($_SESSION['rol'] ?? 0) != 1) { header('Content-Type: application/json'); echo json_encode(['ok'=>false,'msg'=>'No autorizado']); return; }
        $f = $this->logFile();
        if (file_exists($f)) @unlink($f);
        header('Content-Type: application/json');
        echo json_encode(['ok' => true]);
    }

    private function cargarConfig()
    {
        $f = $this->configFile();
        $defaults = [
            'destinatarios'      => [defined('USER_SMTP') ? USER_SMTP : ''],

            'tipos_activos'   => ['FIRMA_VENCIDA' => true, 'FIRMA_POR_VENCER' => true, 'FIRMA_NO_EXISTE' => true, 'FIRMA_CLAVE_INCORRECTA' => true, 'FIRMA_LECTURA_FALLO' => true, 'FIRMA_SIN_VIGENCIA' => true, 'FIRMA_SIN_CLAVE' => true, 'CLIENTE_SIN_CORREO' => true],
            'rate_limit_segs' => 3600,
            'wa_api' => [
                'base_url'     => 'http://127.0.0.1:3005',
                'session_uuid' => '',
                'session_id'   => '',
                'session_name' => '',
                'phone'        => '',
                'last_status'  => '',
                'phones_alerta'=> [],
            ],
            'smtp' => [
                'host'       => '',
                'port'       => 465,
                'secure'     => 1,
                'user'       => '',
                'password'   => '',
                'from_name'  => '',
                'from_email' => '',
            ],
        ];
        if (!file_exists($f)) return $defaults;
        $arr = @json_decode(@file_get_contents($f), true);
        if (!is_array($arr)) return $defaults;
        return array_merge($defaults, $arr);
    }

    public function guardarConfig()
    {
        if (($_SESSION['rol'] ?? 0) != 1) { header('Content-Type: application/json'); echo json_encode(['ok'=>false,'msg'=>'No autorizado']); return; }
        $body = json_decode(file_get_contents('php://input'), true);
        if (!is_array($body)) { header('Content-Type: application/json'); echo json_encode(['ok'=>false,'msg'=>'Body invalido']); return; }
        $cfg = $this->cargarConfig();
        if (isset($body['destinatarios'])) {
            $emails = array_filter(array_map('trim', is_array($body['destinatarios']) ? $body['destinatarios'] : explode(',', $body['destinatarios'])));
            $cfg['destinatarios'] = array_values(array_filter($emails, fn($e) => filter_var($e, FILTER_VALIDATE_EMAIL)));
        }
        if (isset($body['tipos_activos']) && is_array($body['tipos_activos'])) {
            foreach ($body['tipos_activos'] as $k => $v) $cfg['tipos_activos'][$k] = (bool)$v;
        }
        if (isset($body['rate_limit_segs'])) $cfg['rate_limit_segs'] = max(0, (int)$body['rate_limit_segs']);
        if (isset($body['wa_api']) && is_array($body['wa_api'])) {
            if (isset($body['wa_api']['base_url'])) $cfg['wa_api']['base_url'] = rtrim(trim((string)$body['wa_api']['base_url']), '/');
            if (isset($body['wa_api']['phones_alerta']) && is_array($body['wa_api']['phones_alerta'])) {
                $cfg['wa_api']['phones_alerta'] = array_values(array_filter(array_map(function($s){ return preg_replace('/[^0-9]/', '', (string)$s); }, $body['wa_api']['phones_alerta'])));
            }
        }
        if (isset($body['smtp']) && is_array($body['smtp'])) {
            $sIn = $body['smtp'];
            if (isset($sIn['host']))       $cfg['smtp']['host']       = trim((string)$sIn['host']);
            if (isset($sIn['port']))       $cfg['smtp']['port']       = max(1, min(65535, (int)$sIn['port']));
            if (isset($sIn['secure']))     $cfg['smtp']['secure']     = ((int)$sIn['secure'] === 1) ? 1 : 0;
            if (isset($sIn['user']))       $cfg['smtp']['user']       = trim((string)$sIn['user']);
            // Password: si llega vacia o es el placeholder mascarado, no la sobreescribimos
            if (isset($sIn['password']) && $sIn['password'] !== '' && $sIn['password'] !== '********') {
                $cfg['smtp']['password'] = (string)$sIn['password'];
            }
            if (isset($sIn['from_name']))  $cfg['smtp']['from_name']  = trim((string)$sIn['from_name']);
            if (isset($sIn['from_email'])) $cfg['smtp']['from_email'] = trim((string)$sIn['from_email']);
        }

        $dir = ROOT_PATH . '/storage';
        if (!is_dir($dir)) @mkdir($dir, 0755, true);
        @file_put_contents($this->configFile(), json_encode($cfg, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
        header('Content-Type: application/json');
        echo json_encode(['ok' => true, 'config' => $cfg]);
    }

    public function probar()
    {
        header('Content-Type: application/json; charset=utf-8');
        if (($_SESSION['rol'] ?? 0) != 1) { echo json_encode(['ok'=>false,'msg'=>'No autorizado']); return; }
        $body = json_decode(file_get_contents('php://input'), true) ?: [];
        $emailOverride = trim((string)($body['email'] ?? ''));

        // Si no se especifica nada, usar la config guardada
        if (empty($emailOverride)) {
            if (!function_exists('enviarAlertaAdmin')) { echo json_encode(['ok'=>false,'msg'=>'enviarAlertaAdmin no disponible']); return; }
            $ok = enviarAlertaAdmin('PRUEBA', 'Notificacion de prueba - {{empresa_nombre}}', '<p>Esta es una notificacion de prueba enviada manualmente desde <b>{{empresa_nombre}}</b> ({{empresa_ruc}}).</p><p>Hora: ' . date('Y-m-d H:i:s') . '</p>');
            echo json_encode(['ok' => (bool)$ok, 'modo' => 'config']);
            return;
        }

        // Backup config + apply override
        $f = $this->configFile();
        $original = file_exists($f) ? @file_get_contents($f) : null;
        $cfg = $this->cargarConfig();
        if (!empty($emailOverride) && filter_var($emailOverride, FILTER_VALIDATE_EMAIL)) {
            $cfg['destinatarios'] = [$emailOverride];
        }
        $cfg['tipos_activos']['PRUEBA'] = true;
        $cfg['rate_limit_segs'] = 0;
        $dir = ROOT_PATH . '/storage';
        if (!is_dir($dir)) @mkdir($dir, 0755, true);
        @file_put_contents($f, json_encode($cfg, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));

        $ok = false;
        try {
            if (function_exists('enviarAlertaAdmin')) {
                $ok = (bool)enviarAlertaAdmin('PRUEBA', 'Notificacion de prueba - {{empresa_nombre}}', '<p>Esta es una notificacion de prueba enviada manualmente desde <b>{{empresa_nombre}}</b> ({{empresa_ruc}}).</p><p>Hora: ' . date('Y-m-d H:i:s') . '</p>');
            }
        } finally {
            // Restaurar config original
            if ($original !== null) {
                @file_put_contents($f, $original);
            } else {
                @unlink($f);
            }
        }

        echo json_encode(['ok' => $ok, 'modo' => 'override', 'destino_email' => $emailOverride], JSON_UNESCAPED_UNICODE);
    }

    /**
     * Prueba la configuracion SMTP enviando un correo de prueba sin guardar
     * los cambios definitivamente. Acepta los mismos campos que guardarConfig
     * bajo "smtp" + "test_email" (destinatario del test).
     */
    public function probarSmtp()
    {
        header('Content-Type: application/json; charset=utf-8');
        if (($_SESSION['rol'] ?? 0) != 1) { echo json_encode(['ok'=>false,'msg'=>'No autorizado']); return; }
        $body = json_decode(file_get_contents('php://input'), true) ?: [];
        $sIn  = is_array($body['smtp'] ?? null) ? $body['smtp'] : [];
        $testEmail = trim((string)($body['test_email'] ?? ''));
        if (!filter_var($testEmail, FILTER_VALIDATE_EMAIL)) {
            echo json_encode(['ok'=>false,'msg'=>'Email de prueba invalido']); return;
        }

        // Backup de la config actual
        $f = $this->configFile();
        $original = file_exists($f) ? @file_get_contents($f) : null;
        $cfg = $this->cargarConfig();

        // Aplicar overrides SMTP en memoria + guardar al disco temporalmente
        if (isset($sIn['host']))       $cfg['smtp']['host']       = trim((string)$sIn['host']);
        if (isset($sIn['port']))       $cfg['smtp']['port']       = max(1, min(65535, (int)$sIn['port']));
        if (isset($sIn['secure']))     $cfg['smtp']['secure']     = ((int)$sIn['secure'] === 1) ? 1 : 0;
        if (isset($sIn['user']))       $cfg['smtp']['user']       = trim((string)$sIn['user']);
        if (isset($sIn['password']) && $sIn['password'] !== '' && $sIn['password'] !== '********') {
            $cfg['smtp']['password'] = (string)$sIn['password'];
        }
        if (isset($sIn['from_name']))  $cfg['smtp']['from_name']  = trim((string)$sIn['from_name']);
        if (isset($sIn['from_email'])) $cfg['smtp']['from_email'] = trim((string)$sIn['from_email']);

        // Forzar destinatario unico del test + bypass rate-limit + tipo PRUEBA activo
        $cfg['destinatarios'] = [$testEmail];
        $cfg['tipos_activos']['PRUEBA'] = true;
        $cfg['rate_limit_segs'] = 0;
        // Desactivar canal WhatsApp para no enviar duplicado en la prueba
        if (isset($cfg['wa_api'])) $cfg['wa_api']['session_id'] = '';

        $dir = ROOT_PATH . '/storage';
        if (!is_dir($dir)) @mkdir($dir, 0755, true);
        @file_put_contents($f, json_encode($cfg, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));

        $ok = false; $err = '';
        try {
            if (function_exists('enviarAlertaAdmin')) {
                $ok = (bool)enviarAlertaAdmin('PRUEBA', 'Prueba de configuracion SMTP - {{empresa_nombre}}',
                    '<p>Si recibes este mensaje la configuracion SMTP funciona correctamente.</p>'
                    . '<p>Hora: ' . date('Y-m-d H:i:s') . '</p>');
            } else { $err = 'enviarAlertaAdmin no disponible'; }
        } catch (\Throwable $e) { $err = $e->getMessage(); }
        finally {
            if ($original !== null) @file_put_contents($f, $original);
            else @unlink($f);
        }

        echo json_encode(['ok' => $ok, 'destino' => $testEmail, 'error' => $err], JSON_UNESCAPED_UNICODE);
    }

    /** ============== WHATSAPP API ============== */

    private function waBaseUrl()
    {
        // 1) Preferir servicios.json (gestion centralizada en /admin/servicios)
        if (function_exists('servicioConfig')) {
            $svc = servicioConfig('whatsapp_api');
            if (!empty($svc['base_url'])) return rtrim($svc['base_url'], '/');
        }
        // 2) Fallback: campo legacy en alertas-config.json
        $cfg = $this->cargarConfig();
        return rtrim($cfg['wa_api']['base_url'] ?? '', '/');
    }

    private function waCall($path, $method = 'GET', $payload = null)
    {
        $base = $this->waBaseUrl();
        if (empty($base)) return ['ok' => false, 'http' => 0, 'msg' => 'base_url no configurada'];
        $url = $base . $path;
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_TIMEOUT, 30);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json', 'Accept: application/json']);
        $method = strtoupper($method);
        if (in_array($method, ['POST','PUT','PATCH','DELETE'])) {
            curl_setopt($ch, CURLOPT_CUSTOMREQUEST, $method);
            if ($payload !== null) {
                curl_setopt($ch, CURLOPT_POSTFIELDS, is_array($payload) ? json_encode($payload, JSON_UNESCAPED_UNICODE) : $payload);
            }
        }
        $resp = curl_exec($ch);
        $code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $err  = curl_error($ch);
        curl_close($ch);
        $data = json_decode($resp, true);
        return ['ok' => ($code >= 200 && $code < 300), 'http' => $code, 'data' => $data, 'raw' => is_string($resp) ? mb_substr($resp, 0, 4000) : '', 'error' => $err];
    }

    public function waCrearSesion()
    {
        header('Content-Type: application/json; charset=utf-8');
        if (($_SESSION['rol'] ?? 0) != 1) { echo json_encode(['ok'=>false,'msg'=>'No autorizado']); return; }
        $body = json_decode(file_get_contents('php://input'), true) ?: [];
        $titulo = defined('TITLE') ? TITLE : (defined('DBNAME') ? DBNAME : 'sesion');
        $name = trim((string)($body['name'] ?? ($titulo . '-' . date('YmdHis'))));
        $r = $this->waCall('/api/session', 'POST', ['nameSession' => strtoupper($name)]);
        if ($r['ok'] && is_array($r['data']) && !empty($r['data']['sessionId'])) {
            $cfg = $this->cargarConfig();
            $cfg['wa_api']['session_uuid'] = $r['data']['id'] ?? '';
            $cfg['wa_api']['session_id']   = $r['data']['sessionId'];
            $cfg['wa_api']['session_name'] = $r['data']['nameSession'] ?? $name;
            $cfg['wa_api']['last_status']  = $r['data']['status'] ?? 'creada';
            $cfg['wa_api']['phone']        = $r['data']['numberSession'] ?? '';
            @file_put_contents($this->configFile(), json_encode($cfg, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
            $r['session_id'] = $r['data']['sessionId'];
        }
        echo json_encode($r, JSON_UNESCAPED_UNICODE);
    }

    public function waObtenerQr()
    {
        header('Content-Type: application/json; charset=utf-8');
        $cfg = $this->cargarConfig();
        $sid = $cfg['wa_api']['session_id'] ?? '';
        if (empty($sid)) { echo json_encode(['ok'=>false,'msg'=>'No hay sesion creada. Crea una primero.']); return; }
        $r = $this->waCall('/api/whatsapp/qr/' . urlencode($sid), 'GET');
        $qrImage = '';
        if ($r['ok'] && is_array($r['data'])) {
            if (!empty($r['data']['qr']) && strpos($r['data']['qr'], 'data:image') === 0) {
                $qrImage = $r['data']['qr'];
            }
        }
        $r['qr_image'] = $qrImage;
        $r['status_msg'] = $r['data']['status'] ?? '';
        echo json_encode($r, JSON_UNESCAPED_UNICODE);
    }

    public function waEstadoSesion()
    {
        header('Content-Type: application/json; charset=utf-8');
        $cfg = $this->cargarConfig();
        $sid = $cfg['wa_api']['session_id'] ?? '';
        if (empty($sid)) { echo json_encode(['ok'=>true,'estado'=>'sin_sesion']); return; }

        // 1) status-sessions para conocer el estado conexion
        $rState = $this->waCall('/api/whatsapp/status-sessions', 'GET');
        $estado = 'desconocido';
        if ($rState['ok'] && is_array($rState['data'])) {
            foreach ($rState['data'] as $s) {
                if (!is_array($s)) continue;
                if (($s['sessionId'] ?? '') === $sid) {
                    $estado = $s['state'] ?? $s['status'] ?? 'desconocido';
                    break;
                }
            }
        }

        // 2) /session/{sid}/info para extraer phone si esta vinculado
        $phone = $cfg['wa_api']['phone'] ?? '';
        if ($estado === 'open' || $estado === 'connected' || empty($phone)) {
            $rInfo = $this->waCall('/api/whatsapp/session/' . urlencode($sid) . '/info', 'GET');
            if ($rInfo['ok'] && is_array($rInfo['data'])) {
                $flat = [];
                $walker = function ($arr, &$flat) use (&$walker) {
                    foreach ($arr as $k => $v) {
                        if (is_array($v)) $walker($v, $flat);
                        else $flat[$k] = $v;
                    }
                };
                $walker($rInfo['data'], $flat);
                foreach (['phone','number','msisdn','wa_id','jid','user','phoneNumber','wid','numberSession'] as $k) {
                    if (!empty($flat[$k]) && is_scalar($flat[$k])) {
                        $cand = preg_replace('/[^0-9]/', '', (string)$flat[$k]);
                        if (strlen($cand) >= 8) { $phone = $cand; break; }
                    }
                }
            }
        }

        $cfg['wa_api']['last_status'] = $estado;
        if (!empty($phone)) $cfg['wa_api']['phone'] = $phone;
        @file_put_contents($this->configFile(), json_encode($cfg, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));

        echo json_encode(['ok' => true, 'estado' => $estado, 'phone' => $phone, 'session_id' => $sid], JSON_UNESCAPED_UNICODE);
    }

    public function waCerrarSesion()
    {
        header('Content-Type: application/json; charset=utf-8');
        if (($_SESSION['rol'] ?? 0) != 1) { echo json_encode(['ok'=>false,'msg'=>'No autorizado']); return; }
        $cfg = $this->cargarConfig();
        $sid  = $cfg['wa_api']['session_id'] ?? '';
        $uuid = $cfg['wa_api']['session_uuid'] ?? '';
        if (empty($sid)) { echo json_encode(['ok'=>true,'msg'=>'No hay sesion activa']); return; }

        // 1) Logout WhatsApp
        $r1 = $this->waCall('/api/whatsapp/session/' . urlencode($sid), 'DELETE');
        // 2) Borrar registro de session DB (opcional)
        if (!empty($uuid)) {
            $this->waCall('/api/session/' . urlencode($uuid), 'DELETE');
        }
        // Limpiar config local
        $cfg['wa_api']['session_id']   = '';
        $cfg['wa_api']['session_uuid'] = '';
        $cfg['wa_api']['session_name'] = '';
        $cfg['wa_api']['phone']        = '';
        $cfg['wa_api']['last_status']  = 'cerrada';
        @file_put_contents($this->configFile(), json_encode($cfg, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
        echo json_encode(['ok' => true, 'logout' => $r1], JSON_UNESCAPED_UNICODE);
    }

    public function waEnviarPrueba()
    {
        header('Content-Type: application/json; charset=utf-8');
        if (($_SESSION['rol'] ?? 0) != 1) { echo json_encode(['ok'=>false,'msg'=>'No autorizado']); return; }
        $body = json_decode(file_get_contents('php://input'), true) ?: [];
        $tel = preg_replace('/[^0-9]/', '', trim((string)($body['number'] ?? '')));
        $msg = (string)($body['message'] ?? 'Prueba desde sistema');
        if (empty($tel)) { echo json_encode(['ok'=>false,'msg'=>'Numero requerido']); return; }
        $cfg = $this->cargarConfig();
        $sid = $cfg['wa_api']['session_id'] ?? '';
        if (empty($sid)) { echo json_encode(['ok'=>false,'msg'=>'No hay sesion vinculada']); return; }
        // Normalizar Ecuador
        if (strlen($tel) === 10 && $tel[0] === '0') $tel = '593' . substr($tel, 1);
        elseif (strlen($tel) < 11) $tel = '593' . $tel;
        $r = $this->waCall('/api/whatsapp/send?fastMode=true', 'POST', [
            'sessionId' => $sid,
            'number'    => $tel,
            'message'   => $msg,
        ]);
        echo json_encode($r, JSON_UNESCAPED_UNICODE);
    }

    /** ============== PLANTILLAS DE MENSAJES ============== */

    private function plantillasDefault()
    {
        return [
            'whatsapp_recordatorio' => [
                'descripcion' => 'WhatsApp - Recordatorio de pago al cliente',
                'asunto'      => 'RECORDATORIO DE PAGO',
                'cuerpo'      => "SALUDOS ESTIMADO USUARIO\n          *{{empresa_nombre}}*\n\n*RECORDATORIO DE PAGO*\n\n{{cliente_nombre}}\n  Su saldo pendiente:\n\n            *\${{cliente_saldo}}*\n\nCUENTAS DE PAGOS\n{{empresa_cuentas}}\nA NOMBRE DE {{empresa_titular_cuenta}}\n\nenviar foto del deposito\n\n*EVITE LA SUSPENSION DEL SERVICIO*\n\nEste es un mensaje circular\nsi ya pago, haga caso omiso\n\n          GRACIAS",
            ],
            'whatsapp_suspension' => [
                'descripcion' => 'WhatsApp - Aviso de suspension por falta de pago',
                'asunto'      => 'SUSPENDIDO POR PAGO',
                'cuerpo'      => "Buen dia estimado/a cliente\n          *{{empresa_nombre}}*\n   *SUSPENDIDO POR PAGO*\n\n  su saldo a la fecha es:\n            \$*{{cliente_saldo}}*\n*{{cliente_nombre}}*",
            ],
            'whatsapp_pago_recibido' => [
                'descripcion' => 'WhatsApp - Confirmacion de pago recibido',
                'asunto'      => 'GRACIAS POR SU PAGO',
                'cuerpo'      => "Buen dia estimado/a cliente\n          *{{empresa_nombre}}*\n   *GRACIAS POR SU PAGO*\n\n  su saldo a la fecha es:\n            \${{cliente_saldo}}\nincluido *SERVICIO {{servicio_meses}}*\n*{{cliente_nombre}}*",
            ],
            'whatsapp_caso_creado' => [
                'descripcion' => 'WhatsApp - Caso de soporte creado',
                'asunto'      => 'NUEVO CASO',
                'cuerpo'      => "Buen Dia! Se ha creado un nuevo caso\nEstado: INGRESADO\nProblema: {{caso_problema}}",
            ],
            'whatsapp_caso_actualizado' => [
                'descripcion' => 'WhatsApp - Caso actualizado',
                'asunto'      => 'CASO ACTUALIZADO',
                'cuerpo'      => "Buen Dia! Su caso se ha actualizado\nEstado: {{caso_estado}}\nProblema: {{caso_problema}}\nTrabajo Realizado: {{caso_trabajo}}",
            ],
            'config_cuentas_pago' => [
                'descripcion' => 'Cuentas de pago (un texto multilinea, se inyecta en {{empresa_cuentas}})',
                'asunto'      => '',
                'cuerpo'      => "AHORROS PICHINCHA 4989349100\nAHORROS GUAYAQUIL 30591823\nCORRIENTE PRODUBANCO 2120015818\nAHORROS PACIFICO 1061455946",
            ],
            'config_titular_cuenta' => [
                'descripcion' => 'Titular de las cuentas (se inyecta en {{empresa_titular_cuenta}})',
                'asunto'      => '',
                'cuerpo'      => 'JUAN VICENTE BRAVO ENCARNACION',
            ],
        ];
    }

    private function cargarPlantillas()
    {
        $f = $this->plantillasFile();
        $defs = $this->plantillasDefault();
        if (!file_exists($f)) {
            // Persistir defaults en disco para que renderPlantilla() (helper global) los encuentre
            $dir = ROOT_PATH . '/storage';
            if (!is_dir($dir)) @mkdir($dir, 0755, true);
            @file_put_contents($f, json_encode($defs, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
            return $defs;
        }
        $arr = @json_decode(@file_get_contents($f), true);
        if (!is_array($arr)) return $defs;
        // mezclar: plantillas guardadas + nuevas que no existian (y persistir si hubo cambios)
        $cambio = false;
        foreach ($defs as $k => $v) {
            if (!isset($arr[$k])) { $arr[$k] = $v; $cambio = true; }
        }
        if ($cambio) {
            @file_put_contents($f, json_encode($arr, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
        }
        return $arr;
    }

    public function listarPlantillas()
    {
        header('Content-Type: application/json; charset=utf-8');
        echo json_encode($this->cargarPlantillas(), JSON_UNESCAPED_UNICODE);
    }

    public function guardarPlantilla()
    {
        if (($_SESSION['rol'] ?? 0) != 1) { header('Content-Type: application/json'); echo json_encode(['ok'=>false,'msg'=>'No autorizado']); return; }
        $body = json_decode(file_get_contents('php://input'), true);
        if (!is_array($body) || empty($body['key'])) { header('Content-Type: application/json'); echo json_encode(['ok'=>false,'msg'=>'Datos invalidos']); return; }
        $plant = $this->cargarPlantillas();
        $key = preg_replace('/[^a-z0-9_]/i', '', (string)$body['key']);
        if (empty($key)) { header('Content-Type: application/json'); echo json_encode(['ok'=>false,'msg'=>'Key invalido']); return; }
        if (!isset($plant[$key])) $plant[$key] = ['descripcion' => '', 'asunto' => '', 'cuerpo' => ''];
        if (isset($body['descripcion'])) $plant[$key]['descripcion'] = (string)$body['descripcion'];
        if (isset($body['asunto']))      $plant[$key]['asunto']      = (string)$body['asunto'];
        if (isset($body['cuerpo']))      $plant[$key]['cuerpo']      = (string)$body['cuerpo'];
        $dir = ROOT_PATH . '/storage';
        if (!is_dir($dir)) @mkdir($dir, 0755, true);
        @file_put_contents($this->plantillasFile(), json_encode($plant, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
        header('Content-Type: application/json');
        echo json_encode(['ok' => true]);
    }

    public function previsualizar()
    {
        header('Content-Type: application/json; charset=utf-8');
        $body = json_decode(file_get_contents('php://input'), true);
        if (!is_array($body)) { echo json_encode(['ok'=>false]); return; }
        $cuerpo = $body['cuerpo'] ?? '';
        $asunto = $body['asunto'] ?? '';
        // Datos demo
        $vars = [
            'empresa_nombre' => defined('TITLE') ? TITLE : 'Empresa',
            'empresa_ruc'    => '1234567890001',
            'empresa_correo' => 'info@empresa.com',
            'empresa_telefono' => '0999999999',
            'empresa_direccion' => 'Direccion de la empresa',
            'empresa_razon_social' => 'EMPRESA S.A.',
            'cliente_nombre' => 'JUAN PEREZ EJEMPLO',
            'cliente_saldo'  => '25.00',
            'cliente_telefono' => '0991234567',
            'servicio_meses' => 'ABRIL',
            'caso_problema'  => 'Sin internet en zona X',
            'caso_estado'    => 'EN PROCESO',
            'caso_trabajo'   => 'Reemplazo de cable',
        ];
        $plant = $this->cargarPlantillas();
        if (isset($plant['config_cuentas_pago']))   $vars['empresa_cuentas']        = $plant['config_cuentas_pago']['cuerpo'];
        if (isset($plant['config_titular_cuenta'])) $vars['empresa_titular_cuenta'] = $plant['config_titular_cuenta']['cuerpo'];
        foreach ($vars as $k => $v) {
            $asunto = str_replace('{{' . $k . '}}', (string)$v, $asunto);
            $cuerpo = str_replace('{{' . $k . '}}', (string)$v, $cuerpo);
        }
        echo json_encode(['ok' => true, 'asunto' => $asunto, 'cuerpo' => $cuerpo], JSON_UNESCAPED_UNICODE);
    }
}

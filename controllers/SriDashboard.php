<?php
class SriDashboard extends Controller
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

    /* =========================
       VISTA DASHBOARD
       ========================= */
    public function index()
    {
        $data['title'] = 'Dashboard SRI';
        $data['script'] = 'sridashboard.js';
        $data['resumen'] = $this->model->getResumen();
        $this->views->getView('sri', 'dashboard', $data);
    }

    /* =========================
       LISTADO AJAX
       ========================= */
   public function listar()
{
    $estado = null;

    if (isset($_GET['estado']) && $_GET['estado'] !== '') {
        $estado = (int) $_GET['estado'];
    }

    $data = $this->model->getDocumentos($estado);

    for ($i = 0; $i < count($data); $i++) {

        /* ===== ESTADO ===== */
        if ($data[$i]['estado_proceso'] == 1) {
            $data[$i]['estado'] = '<span class="badge bg-success">AUTORIZADA</span>';
        } elseif ($data[$i]['estado_proceso'] == 0) {
            $data[$i]['estado'] = '<span class="badge bg-warning">PENDIENTE</span>';
        } else {
            if ($data[$i]['intentos_sri'] >= 20) {
                $data[$i]['estado'] = '<span class="badge bg-danger">BLOQUEADA</span>';
            } else {
                $data[$i]['estado'] = '<span class="badge bg-secondary">RECHAZADA</span>';
            }
        }

        /* ===== CORREO ===== */
        $data[$i]['correo'] = $data[$i]['correo_enviado']
            ? '<span class="badge bg-success">ENVIADO</span>'
            : '<span class="badge bg-warning">PENDIENTE</span>';

        /* ===== ACCIONES ===== */
        $acciones = '';

        if ($data[$i]['estado_proceso'] == 2) {
            $acciones .= '<button class="btn btn-danger btn-sm" onclick="verError(' . $data[$i]['id'] . ')">
                            <i class="fas fa-eye"></i>
                          </button> ';
        }

        if ($data[$i]['estado_proceso'] == 2 && $data[$i]['intentos_sri'] < 20) {
            $acciones .= '<button class="btn btn-info btn-sm" onclick="reintentar(' . $data[$i]['id'] . ')">
                            <i class="fas fa-sync"></i>
                          </button>';
        }

        $data[$i]['acciones'] = $acciones;
    }

    echo json_encode($data, JSON_UNESCAPED_UNICODE);
    die();
}


    /* =========================
       VER ERROR SRI
       ========================= */
    public function error2($id)
    {
        $data = $this->model->getErrorSri($id);
        echo json_encode($data, JSON_UNESCAPED_UNICODE);
        die();
    }

public function error($id)
{
    $row = $this->model->getErrorSri($id);

    if (!$row || empty($row['mensaje_sri'])) {
        echo json_encode([
            'ok' => false,
            'raw' => 'SIN DETALLE'
        ]);
        die();
    }

    $raw = trim($row['mensaje_sri']);

    // Valores por defecto
    $codigo = 'N/A';
    $mensajeTxt = 'Error no identificado';
    $info = '';
    $tipo = 'ERROR';

    // 🔎 Extraer identificador
    if (preg_match('/\[identificador\]\s*=>\s*(\d+)/', $raw, $m)) {
        $codigo = $m[1];
    }

    // 🔎 Extraer mensaje REAL (más específico)
    if (preg_match('/\[mensaje\]\s*=>\s*([^\r\n]+)/', $raw, $m)) {
        $mensajeTxt = trim($m[1]);
    }

    // 🔎 Información adicional
    if (preg_match('/\[informacionAdicional\]\s*=>\s*([^\r\n]+)/', $raw, $m)) {
        $info = trim($m[1]);
    }

    // 🔎 Tipo
    if (preg_match('/\[tipo\]\s*=>\s*(\w+)/', $raw, $m)) {
        $tipo = $m[1];
    }

    echo json_encode([
        'ok' => true,
        'estado' => 'DEVUELTA',
        'codigo' => $codigo,
        'mensaje' => $mensajeTxt,
        'info' => $info,
        'tipo' => $tipo,
        'sugerencia' => $this->sugerirCorreccion($mensajeTxt),
        'raw' => $raw // 🔥🔥🔥 SIEMPRE
    ], JSON_UNESCAPED_UNICODE);

    die();
}



    /* =========================
       REINTENTAR MANUAL
       ========================= */
    public function reintentar($id)
    {
        if (is_numeric($id)) {
            $data = $this->model->reintentar($id);
            if ($data == 1) {
                $res = ['msg' => 'DOCUMENTO ENCOLADO PARA REINTENTO', 'type' => 'success'];
            } else {
                $res = ['msg' => 'ERROR AL REINTENTAR', 'type' => 'error'];
            }
        } else {
            $res = ['msg' => 'ID NO VÁLIDO', 'type' => 'error'];
        }

        echo json_encode($res, JSON_UNESCAPED_UNICODE);
        die();
    }
private function sugerirCorreccion($mensaje)
{
    $mensaje = strtoupper($mensaje);

    if (strpos($mensaje, 'ESTRUCTURA XML') !== false) {
        return 'Revise el XML generado: etiquetas mal cerradas, caracteres especiales (&, <, >), o nodos faltantes.';
    }

    if (strpos($mensaje, 'CLAVE DE ACCESO') !== false) {
        return 'La factura ya fue enviada. Verifique duplicidad o regenere la clave.';
    }

    if (strpos($mensaje, 'SECUENCIAL') !== false) {
        return 'El secuencial está duplicado o fuera de rango.';
    }

    return 'Revise el XML antes de reenviar al SRI.';
}


}


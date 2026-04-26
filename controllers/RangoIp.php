<?php
class RangoIp extends Controller
{
    private function inferCidrFromRedFinal($red, $final)
    {
        $r = ip2long($red); $f = ip2long($final);
        if ($r === false || $f === false) return $red;
        for ($m = 8; $m <= 30; $m++) {
            $bits = 32 - $m;
            $maskInt = ((-1 << $bits) & 0xFFFFFFFF);
            $network = $r & $maskInt;
            $broadcast = ($network | (~$maskInt & 0xFFFFFFFF)) & 0xFFFFFFFF;
            $lastUsable = $broadcast - 1;
            $firstUsable = $network + 1;
            if ($lastUsable === $f && ($network === $r || $firstUsable === $r)) {
                return long2ip($network) . '/' . $m;
            }
        }
        return $red;  // sin CIDR exacto: devolver IP tal cual
    }


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
        $data['title'] = 'Rango Ip';
        $data['script'] = 'rangoip.js';
        $data['zona'] = $this->model->zona(1);

        $this->views->getView('rangoip', 'index', $data);
    }
    public function listar()
    {


        $data = $this->model->getIp();
		//print_r( $data); exit;
        for ($i = 0; $i < count($data); $i++) {

            $finalLong  = ip2long($data[$i]['final']);
            $ultimaLong = ip2long($data[$i]['ultima']);
            $disponibles = ($finalLong !== false && $ultimaLong !== false && $finalLong > $ultimaLong)
                ? ($finalLong - $ultimaLong)
                : 0;
            $data[$i]['redCidr'] = $this->inferCidrFromRedFinal($data[$i]['red'], $data[$i]['final']);

            $data[$i]['acciones'] = '
            <div> <button class="btn btn-info" type="button" onclick="editarRangoIp(' . $data[$i]['id'] . ')"><i class="fas fa-edit text-white"></i></button>
            </div>
            ';
            /* <button class="btn btn-danger" type="button" onclick="eliminarIp(' . $data[$i]['id'] . ')"><i class="fas fa-trash"></i></button> */

            if ($data[$i]['estado'] == 1) {
                $data[$i]['estado'] = '<div style="text-align: center;"><span class="badge bg-success">DISPONIBLE</span></div>';
            } else if ($data[$i]['estado'] == 2) {
                $data[$i]['estado'] = '<div style="text-align: center;"><span class="badge bg-warning">OCUPADA</span></div>';
            } else {
                $data[$i]['estado'] = '<div style="text-align: center;"><span class="badge bg-danger">SIN SERVICIO</span></div>';
            }


            $data[$i]['disponibles'] = $disponibles;
        }
        echo json_encode($data, JSON_UNESCAPED_UNICODE);
        die();
    }
    public function registrar()
    {
        $res = array('msg' => 'ERROR DESCONOCIDO', 'type' => 'error');
        if (isset($_POST['red']) && isset($_POST['final'])) {
            $red     = strClean($_POST['red']);
            $gateway = isset($_POST['gateway']) ? strClean($_POST['gateway']) : $red;
            $final   = strClean($_POST['final']);
            $zona    = strClean($_POST['zona']);
            $id      = strClean($_POST['id']);

            // Validacion IPv4
            if (!filter_var($red, FILTER_VALIDATE_IP, FILTER_FLAG_IPV4)) {
                $res = array('msg' => 'IP RED invalida', 'type' => 'warning');
            } else if (!filter_var($gateway, FILTER_VALIDATE_IP, FILTER_FLAG_IPV4)) {
                $res = array('msg' => 'IP GATEWAY invalida', 'type' => 'warning');
            } else if (!filter_var($final, FILTER_VALIDATE_IP, FILTER_FLAG_IPV4)) {
                $res = array('msg' => 'IP FINAL invalida', 'type' => 'warning');
            } else {
                $r = ip2long($red); $g = ip2long($gateway); $f = ip2long($final);
                if ($r >= $f) {
                    $res = array('msg' => 'FINAL debe ser mayor que RED', 'type' => 'warning');
                } else if ($g < $r || $g > $f) {
                    $res = array('msg' => 'GATEWAY debe estar entre RED y FINAL', 'type' => 'warning');
                } else if ($id == '') {
                    // Verifica que el gateway no este en uso por algun contrato
                    if ($this->model->contarContratosConIp($gateway) > 0) {
                        $res = array('msg' => 'EL GATEWAY YA ESTA EN USO POR UN CLIENTE. Liberalo primero.', 'type' => 'warning');
                    } else {
                        $verificar = $this->model->getValidar('red', $red, 'registrar', 0);
                        if (empty($verificar)) {
                            $data = $this->model->registrar($red, $final, $red, $zona, $gateway);
                            $res = $data > 0
                                ? array('msg' => 'IP REGISTRADA EXITOSAMENTE', 'type' => 'success')
                                : array('msg' => 'ERROR AL REGISTRAR', 'type' => 'error');
                        } else {
                            $res = array('msg' => 'LA IP YA EXISTE', 'type' => 'warning');
                        }
                    }
                } else {
                    // Edicion: si tiene clientes, solo permitir cambiar gateway
                    $original = $this->model->editar($id);
                    $hasClientes = !empty($original)
                        ? $this->model->contarClientesEnRango($original['red'], $original['final'])
                        : 0;

                    if ($hasClientes > 0 && ($original['red'] !== $red || $original['final'] !== $final)) {
                        $res = array('msg' => 'EL RANGO TIENE CLIENTES ACTIVOS. SOLO PUEDES CAMBIAR EL GATEWAY.', 'type' => 'warning');
                    } else if ($this->model->contarContratosConIp($gateway) > 0) {
                        $res = array('msg' => 'EL GATEWAY YA ESTA EN USO POR UN CLIENTE. Liberalo primero.', 'type' => 'warning');
                    } else {
                        $verificar = $this->model->getValidar('red', $red, 'actualizar', $id);
                        if (empty($verificar)) {
                            $data = $this->model->actualizar($red, $final, $id, $zona, $gateway);
                            $res = $data > 0
                                ? array('msg' => 'IP ACTUALIZADA EXITOSAMENTE', 'type' => 'success')
                                : array('msg' => 'ERROR AL ACTUALIZAR', 'type' => 'error');
                        } else {
                            $res = array('msg' => 'LA IP YA EXISTE', 'type' => 'warning');
                        }
                    }
                }
            }
        }
        echo json_encode($res, JSON_UNESCAPED_UNICODE);
        die();
    }

    public function eliminar($idIp)
    {
        if (isset($_GET) && is_numeric($idIp)) {
            $data = $this->model->eliminar(0, $idIp);
            if ($data == 1) {
                $res = array('msg' => 'IP ELIMINADO EXITOSAMENTE', 'type' => 'success');
            } else {
                $res = array('msg' => 'ERROR AL ELIMINAR', 'type' => 'error');
            }
        } else {
            $res = array('msg' => 'ERROR DESCONOCIDO', 'type' => 'error');
        }
        echo json_encode($res, JSON_UNESCAPED_UNICODE);
        die();
    }

    public function editar($idIp)
    {
        $data = $this->model->editar($idIp);
        if (!empty($data['red']) && !empty($data['final'])) {
            $data['total_clientes'] = $this->model->contarClientesEnRango($data['red'], $data['final']);
        } else {
            $data['total_clientes'] = 0;
        }
        echo json_encode($data, JSON_UNESCAPED_UNICODE);
        die();
    }

    public function inactivos()
    {
        $data['title'] = 'Categorias Inactivos';
        $data['script'] = 'categorias-inactivos.js';
        $this->views->getView('categorias', 'inactivos', $data);
    }

    public function listarInactivos()
    {
        $data = $this->model->getCategorias(0);
        for ($i = 0; $i < count($data); $i++) {
            $data[$i]['acciones'] = '<div>
            <button class="btn btn-success" type="button" onclick="restaurarCategoria(' . $data[$i]['id'] . ')"><i class="fas fa-check-circle"></i></button>
            </div>';
        }
        echo json_encode($data, JSON_UNESCAPED_UNICODE);
        die();
    }

    public function restaurar($idCategoria)
    {
        if (isset($_GET) && is_numeric($idCategoria)) {
            $data = $this->model->eliminar(1, $idCategoria);
            if ($data == 1) {
                $res = array('msg' => 'CATEGORIA RESTAURADO EXITOSAMENTE', 'type' => 'success');
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

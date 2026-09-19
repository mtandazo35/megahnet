<?php
require 'vendor/autoload.php';

use Dompdf\Dompdf;

class Creditos extends Controller
{
    private $id_usuario;
    public function __construct()
    {
        parent::__construct();
        session_start();
        if (empty($_SESSION['id_usuario'])) {
            header('Location: ' . BASE_URL);
            exit;
        }
        $this->id_usuario = $_SESSION['id_usuario'];
    }
    public function index()
    {
        $data['script'] = 'creditos.js';
        $data['title'] = 'Administrar Creditos';
        $data['busqueda'] = 'busqueda.js';
        $data['carrito'] = 'posTipoPago';
        $this->views->getView('creditos', 'index', $data);
    }
    /*      public function listar()
    {
        $start  = isset($_POST['start']) ? intval($_POST['start']) : 0;
        $length = isset($_POST['length']) ? intval($_POST['length']) : 25;
        $search = isset($_POST['search']['value']) ? trim($_POST['search']['value']) : '';
        $draw   = isset($_POST['draw']) ? intval($_POST['draw']) : 1;

        $total = $this->model->contarCreditosConAbonos('', 1);
        $filtered = $search ? $this->model->contarCreditosConAbonos($search, 1) : $total;
        $creditos = $this->model->getCreditosConAbonosPaginado($start, $length, $search, 1);

        foreach ($creditos as &$c) {
            $restante = $c['monto'] - $c['abonado'];
            $c['monto'] = number_format($c['monto'], 2);
            $c['abonado'] = number_format($c['abonado'], 2);
            $c['restante'] = number_format($restante, 2);
            $c['venta'] = $c['id_venta'] ? 'N°: ' . $c['id_venta'] : '';
            $c['electronica'] = $c['id_electronica'] ? 'N°: ' . $c['id_electronica'] : '';
            $c['ordenventa'] = $c['id_orden_venta'] ? 'N°: ' . $c['id_orden_venta'] : '';
            $c['acciones'] = '<a class="btn btn-danger btn-sm" href="' . BASE_URL . 'creditos/reporte/' . $c['id'] . '" target="_blank" title="Reporte PDF"><i class="fas fa-file-pdf"></i></a>';
            $tieneTelefono = !empty($c['telefono_cliente']);
            if ($c['estado'] == 1) {
                $c['estado'] = '<span class="badge bg-warning">PENDIENTE</span>';
                $c['ch'] = '<div class="btn-group-toggle" data-toggle="buttons"><label class="btn btn-primary"><input type="checkbox" id="' . $c['id'] . '" value="' . $restante . '"></label></div>';
                $c['notif'] = $tieneTelefono
                    ? '<button class="btn btn-warning btn-sm btn-notif-credito" data-id="' . $c['id'] . '" data-tipo="pendiente" title="Notificar pago pendiente al cliente"><i class="bx bx-bell"></i> Pendiente</button>'
                    : '<span class="text-muted small" title="Sin telefono">—</span>';
            } else if ($c['estado'] == 2) {
                $c['estado'] = '<span class="badge bg-danger">ANULADO</span>';
                $c['ch'] = '';
                $c['notif'] = '';
            } else {
                $c['estado'] = '<span class="badge bg-success">COMPLETADO</span>';
                $c['ch'] = '';
                $c['notif'] = $tieneTelefono
                    ? '<button class="btn btn-success btn-sm btn-notif-credito" data-id="' . $c['id'] . '" data-tipo="pagado" title="Confirmar al cliente que su pago fue recibido"><i class="bx bx-check-circle"></i> Pagado</button>'
                    : '<span class="text-muted small" title="Sin telefono">—</span>';
            }
        }

        echo json_encode([
            'draw' => $draw,
            'recordsTotal' => $total,
            'recordsFiltered' => $filtered,
            'data' => $creditos,
        ], JSON_UNESCAPED_UNICODE);
        die();
    }
*/

    public function listar()
    {
        $start  = isset($_POST['start']) ? intval($_POST['start']) : 0;
        $length = isset($_POST['length']) ? intval($_POST['length']) : 25;
        $search = isset($_POST['search']['value']) ? trim($_POST['search']['value']) : '';
        $draw   = isset($_POST['draw']) ? intval($_POST['draw']) : 1;

        $total    = $this->model->contarCreditosConAbonos('', 1);
        $filtered = $search ? $this->model->contarCreditosConAbonos($search, 1) : $total;
        $creditos = $this->model->getCreditosConAbonosPaginado($start, $length, $search, 1);

        foreach ($creditos as &$c) {
            $restante = $c['monto'] - $c['abonado'];
            $c['monto']    = number_format($c['monto'], 2);
            $c['abonado']  = number_format($c['abonado'], 2);
            $c['restante'] = number_format($restante, 2);
            $c['venta']       = $c['id_venta']        ? 'N°: ' . $c['id_venta']        : '';
            $c['electronica'] = $c['id_electronica']  ? 'N°: ' . $c['id_electronica']  : '';
            $c['ordenventa']  = $c['id_orden_venta']  ? 'N°: ' . $c['id_orden_venta']  : '';
            $c['acciones'] = '<a class="btn btn-danger btn-sm" href="' . BASE_URL . 'creditos/reporte/' . $c['id'] . '" target="_blank" title="Reporte PDF"><i class="fas fa-file-pdf"></i></a>';
            $tieneTelefono = !empty($c['telefono_cliente']);
            if ($c['estado'] == 1) {
                $c['estado'] = '<span class="badge bg-warning">PENDIENTE</span>';
                $c['ch'] = '<div class="btn-group-toggle" data-toggle="buttons"><label class="btn btn-primary"><input type="checkbox" id="' . $c['id'] . '" value="' . $restante . '"></label></div>';
                $c['notif'] = $tieneTelefono
                    ? '<button class="btn btn-warning btn-sm btn-notif-credito" data-id="' . $c['id'] . '" data-tipo="pendiente" title="Notificar pago pendiente al cliente"><i class="bx bx-bell"></i> Pendiente</button>'
                    : '<span class="text-muted small" title="Sin telefono">—</span>';
            } else if ($c['estado'] == 2) {
                $c['estado'] = '<span class="badge bg-danger">ANULADO</span>';
                $c['ch'] = '';
                $c['notif'] = '';
            } else {
                $c['estado'] = '<span class="badge bg-success">COMPLETADO</span>';
                $c['ch'] = '';
                $c['notif'] = $tieneTelefono
                    ? '<button class="btn btn-success btn-sm btn-notif-credito" data-id="' . $c['id'] . '" data-tipo="pagado" title="Confirmar al cliente que su pago fue recibido"><i class="bx bx-check-circle"></i> Pagado</button>'
                    : '<span class="text-muted small" title="Sin telefono">—</span>';
            }
        }

        echo json_encode([
            'draw'            => $draw,
            'recordsTotal'    => $total,
            'recordsFiltered' => $filtered,
            'data'            => $creditos,
        ], JSON_UNESCAPED_UNICODE);
        die();
    }



    /*     public function listarCompletados()
    {
        $start  = isset($_POST['start']) ? intval($_POST['start']) : 0;
        $length = isset($_POST['length']) ? intval($_POST['length']) : 25;
        $search = isset($_POST['search']['value']) ? trim($_POST['search']['value']) : '';
        $draw   = isset($_POST['draw']) ? intval($_POST['draw']) : 1;

        $total = $this->model->contarCreditosConAbonos('', 0);
        $filtered = $search ? $this->model->contarCreditosConAbonos($search, 0) : $total;
        $creditos = $this->model->getCreditosConAbonosPaginado($start, $length, $search, 0);

        foreach ($creditos as &$c) {
            $c['monto'] = number_format($c['monto'], 2);
            $c['abonado'] = number_format($c['abonado'], 2);
            $c['electronica'] = $c['id_electronica'] ? 'N°: ' . $c['id_electronica'] : '';
            $c['ordenventa'] = $c['id_orden_venta'] ? 'N°: ' . $c['id_orden_venta'] : '';
            $c['acciones'] = '<a class="btn btn-danger" href="' . BASE_URL . 'creditos/reporte/' . $c['id'] . '" target="_blank"><i class="fas fa-file-pdf"></i></a>';
            $c['estado'] = '<span class="badge bg-success">COMPLETADO</span>';
        }

        echo json_encode([
            'draw' => $draw,
            'recordsTotal' => $total,
            'recordsFiltered' => $filtered,
            'data' => $creditos,
        ], JSON_UNESCAPED_UNICODE);
        die();
    }
*/

    public function listarCompletados()
    {
        $start  = isset($_POST['start']) ? intval($_POST['start']) : 0;
        $length = isset($_POST['length']) ? intval($_POST['length']) : 25;
        $search = isset($_POST['search']['value']) ? trim($_POST['search']['value']) : '';
        $draw   = isset($_POST['draw']) ? intval($_POST['draw']) : 1;

        $total    = $this->model->contarCreditosConAbonos('', 0);
        $filtered = $search ? $this->model->contarCreditosConAbonos($search, 0) : $total;
        $creditos = $this->model->getCreditosConAbonosPaginado($start, $length, $search, 0);

        foreach ($creditos as &$c) {
            $c['monto']   = number_format($c['monto'], 2);
            $c['abonado'] = number_format($c['abonado'], 2);
            $c['electronica'] = $c['id_electronica'] ? 'N°: ' . $c['id_electronica'] : '';
            $c['ordenventa']  = $c['id_orden_venta'] ? 'N°: ' . $c['id_orden_venta'] : '';
            $c['acciones'] = '<a class="btn btn-danger" href="' . BASE_URL . 'creditos/reporte/' . $c['id'] . '" target="_blank"><i class="fas fa-file-pdf"></i></a>';
            $c['estado'] = '<span class="badge bg-success">COMPLETADO</span>';
        }

        echo json_encode([
            'draw'            => $draw,
            'recordsTotal'    => $total,
            'recordsFiltered' => $filtered,
            'data'            => $creditos,
        ], JSON_UNESCAPED_UNICODE);
        die();
    }

    /* public function buscar()
    {
        $array = array();
        $valor = strClean($_GET['term']);
        // $datafisico = $this->model->buscarPorNombre($valor);
        $dataElectronico = $this->model->buscarPorNombreElectronico($valor);
        $dataOrdenVenta = $this->model->buscarPorNombreOrdenVenta($valor);

        $data = array_merge($dataElectronico, $dataOrdenVenta);
        // print_r($data);
        foreach ($data as $row) {
            $resultAbono = $this->model->getAbono($row['id']);
            $abonado = ($resultAbono['total'] == null) ? 0 : $resultAbono['total'];
            //calcular restante  (monto - abono)
            $restante = $row['monto'] - $abonado;
            $result['monto'] = $row['monto'];
            $result['abonado'] = $abonado;
            $result['restante'] = $restante;
            $result['fecha'] = $row['fecha'];
            $result['id'] = $row['id'];
            $result['label'] = $row['nombre'] . ' FAC-' . $row['factura'];
            $result['telefono'] = $row['telefono'];
            $result['direccion'] = $row['direccion'];
            $result['anticipos'] = (isset($row['anticipos'])) ? $row['anticipos'] : 0;
            array_push($array, $result);
        }
        echo json_encode($array, JSON_UNESCAPED_UNICODE);
        die();
    }*/

    public function buscar()
    {
        $array = [];
        $valor = trim(strClean($_GET['term'] ?? ''));
        // Termino muy corto: no consultar (evita devolver toda la tabla)
        if (mb_strlen($valor) < 2) {
            echo json_encode($array);
            die();
        }

        $dataElectronico = $this->model->buscarPorNombreElectronico($valor);
        $dataOrdenVenta = $this->model->buscarPorNombreOrdenVenta($valor);

        // Unificamos resultados
        $data = array_merge($dataElectronico, $dataOrdenVenta);

        foreach ($data as $row) {
            $restante = $row['monto'] - $row['abonado'];

            $result = [
                'id'        => $row['id'],
                'monto'     => $row['monto'],
                'abonado'   => $row['abonado'],
                'restante'  => $restante,
                'fecha'     => $row['fecha'],
                'label'     => $row['nombre'] . ' FAC-' . $row['factura'],
                'telefono'  => $row['telefono'],
                'direccion' => $row['direccion'],
                'anticipos' => isset($row['anticipos']) ? $row['anticipos'] : 0,
            ];

            $array[] = $result;
        }

        echo json_encode($array, JSON_UNESCAPED_UNICODE);
        die();
    }


    /* public function registrarAbono()
    {
        $fecha = date('Y-m-d');
        $hora = date('H:i:s');
        $fechas = $fecha . ' ' . $hora;
        $json = file_get_contents('php://input');
        $datos = json_decode($json, true);
        $mesActualLetra = MESES[date('n')];
        $codigoPago = '';
        $getValidar = '';
        $anticipo = 0;
        $total = 0;
        $verifcarCaja = $this->model->getCaja($this->id_usuario);
        if (empty($verifcarCaja)) { // caja abierta = existe fila (getCaja ya filtra estado=1); monto_inicial 0.00 es valido y empty(0.00) daba falso "cerrada"
            $res = array('msg' => 'LA CAJA ESTA CERRADA', 'type' => 'warning');
        } else {
            if (!empty($datos)) {
                $idCredito = $datos['idCredito'];
                $restante = $datos['restante'];
                $total = $datos['total'];
                // print_r($datos); exit;

                $dataElectronico = ($this->model->getCreditoElectronica($idCredito) == '') ? '' : $this->model->getCreditoElectronica($idCredito);
                // $dataElectronico = $this->model->getCreditoElectronica($idCredito);
                $dataOrdenVenta = ($this->model->getCreditoOrdenVenta($idCredito) == '') ? '' : $this->model->getCreditoOrdenVenta($idCredito);
                // $dataOrdenVenta = $this->model->getCreditoOrdenVenta($idCredito);
                // print_r($dataElectronico); exit;

                if (!empty($dataElectronico)) {
                    $dataElectronicos = $dataElectronico[0];

                    $datosCliente = $dataElectronicos;
                } else {

                    $datosCliente = $dataOrdenVenta;
                }


                $idCliente = $datosCliente['idCliente'];

                foreach ($datos['tipoPagos'] as $tipoPagos) {

                    $codigoPago = (empty($tipoPagos['codigoComprobante'])) ? '' : $tipoPagos['codigoComprobante'];

                    // Validacion: tipo_pago obligatorio en cada abono
                    if (empty(trim((string)($tipoPagos['nombre'] ?? '')))) {
                        $res = array('msg' => 'CADA ABONO DEBE TENER UN TIPO DE PAGO SELECCIONADO', 'type' => 'error');
                        echo json_encode($res);
                        die();
                    }

                    if (isset($codigoPago)) {
                        if (!empty($codigoPago)) {
                            $getValidar = $this->model->getValidar($codigoPago);
                        }
                        $validadorCredito = ($getValidar == null || $getValidar == '') ? 0 : $getValidar['id_credito'];

                        if ($getValidar > 0) {
                            $res = array('msg' => 'EL NUMERO DE DOCUMNETO YA SE ENCUENTRA REGISTRADO EN EL CREDITO #' . $validadorCredito, 'type' => 'error');
                            echo json_encode($res);
                            die();
                        }
                    }

                    if ($tipoPagos['nombre'] == 'ANTICIPOS' && $tipoPagos['precio'] >= $restante) {
                        $data = $this->model->registrarAbono($restante, $fechas, $idCredito, $this->id_usuario, $codigoPago, $tipoPagos['nombre'], $idCliente);
                    } else {
                        $data = $this->model->registrarAbono($tipoPagos['precio'], $fechas, $idCredito, $this->id_usuario, $codigoPago, $tipoPagos['nombre'], $idCliente);
                    }
                }

                if ($total > $restante) {
                    $anticipo = $total - $restante;
                    //print_r($anticipo); exit;

                    $getUpdateAnticipo = $this->model->updateAnticipos($anticipo, $idCliente);
                    // $data = $this->model->registrarAbono($restante, $idCredito, $this->id_usuario, $codigoPago, $tipoPagos['nombre']);
                } else {
                    $getUpdateAnticipo = $this->model->updateAnticipos(0, $idCliente);
                }

                $getCreditoInfos = $this->model->getCreditoInfo($idCredito);

                if ($getCreditoInfos['id_electronica'] != null || $getCreditoInfos['id_electronica'] != '') {
                    $getDatos = $this->model->getIdCliente($getCreditoInfos['id_electronica']);
                    $getInfoClientes['telefono'] = ($getDatos['telefono'] == null) ? '' : $getDatos['telefono'];
                    $dataElectronico = $this->model->buscarPorNombreElectronicoMonto($getDatos['nombre']);
                    $dataOrdenVenta = $this->model->buscarPorNombreOrdenVentaMonto($getDatos['idCliente']);
                    $datosValores = array_merge($dataElectronico, $dataOrdenVenta);
                    // print_r($datosValores); exit;

                } else {
                    $getDatos = $this->model->buscarPorNombreOrdenVenta($getCreditoInfos['id_orden_venta']);
                    $getInfoClientes['telefono'] = ($getDatos[0]['telefono'] == null) ? '' : $getDatos[0]['telefono'];
                    $dataElectronico = $this->model->buscarPorNombreElectronicoMonto($getDatos[0]['nombre']);
                    $dataOrdenVenta = $this->model->buscarPorNombreOrdenVentaMonto($getDatos[0]['idCliente']);
                    $datosValores = array_merge($dataElectronico, $dataOrdenVenta);
                    // print_r($getDatos); exit;

                }

                $abonado = 0;
                $deudaRestante = 0;


                //print_r($datos['tipoPagos']); exit;

                $deudaTotal = 0;
                if (!empty($datosValores)) {

                    for ($i = 0; $i < count($datosValores); $i++) {
                        $resultAbono = $this->model->getAbono($datosValores[$i]['id']);
                        $abonado += ($resultAbono['total'] == null) ? 0 : $resultAbono['total'];
                        //calcular restante  (monto - abono)
                        $deudaTotal += $datosValores[$i]['monto'];

                        //array_push($data, $result);

                    }
                    $restante = $deudaTotal - $abonado;

                    $data2['abonado'] = $abonado;
                    $data2['restante'] = $restante;
                    //print_r($restante ); exit;

                } else {
                    $data2['abonado'] = $abonado;
                    $data2['restante'] = $restante;
                }
                if ($data > 0) {
                    $tel = $getInfoClientes['telefono'] ?? '';
                    if ($tel === '') {
                        $resWathsapp = null;
                    } else {
                        // Mensaje plano para enviar via API WhatsApp
                        $textoWa = "Buen dia estimado cliente *MEGAHNET*\n"
                                 . "*GRACIAS POR SU PAGO*\n\n"
                                 . "Su saldo a la fecha es: $" . $restante . "\n"
                                 . "Incluido *SERVICIO " . $mesActualLetra . "*\n"
                                 . "*" . $datosCliente['nombre'] . "*";
                        $waOk = false;
                        if (function_exists('enviarWhatsappTexto')) {
                            $apiRes = enviarWhatsappTexto($tel, $textoWa);
                            $waOk = !empty($apiRes['ok']);
                        }
                        $resWathsapp = $waOk ? null
                                     : ('https://web.whatsapp.com/send?phone=593' . $tel
                                        . '&text=' . rawurlencode($textoWa));
                    }

                    $res = array('msg' => 'ABONO REGISTRADO EXITOSAMENTE', 'type' => 'success', 'whatsapp' => $resWathsapp, 'idCredito' => isset($idCredito) ? $idCredito : (isset($idcredito) ? $idcredito : 0), 'telefonoCliente' => $getInfoClientes['telefono'] ?? '');
                } else {
                    $res = array('msg' => 'ERROR AL REGISTRAR', 'type' => 'error');
                }
                // }
            } else {
                $res = array('msg' => 'TODO LOS CAMPOS SON REQUERIDO', 'type' => 'warning');
            }
        }
        //  print_r($res);
        echo json_encode($res);
        die();
    }*/

    public function registrarAbono()
    {
        $fecha = date('Y-m-d');
        $hora = date('H:i:s');
        $fechas = $fecha . ' ' . $hora;
        $json = file_get_contents('php://input');
        $datos = json_decode($json, true);
        $mesActualLetra = MESES[date('n')];
        $codigoPago = '';
        $getValidar = '';
        $anticipo = 0;
        $total = 0;
        $deudaP = 0;

        $verifcarCaja = $this->model->getCaja($this->id_usuario);
        if (empty($verifcarCaja)) { // caja abierta = existe fila (getCaja ya filtra estado=1); monto_inicial 0.00 es valido y empty(0.00) daba falso "cerrada"
            $res = array('msg' => 'LA CAJA ESTA CERRADA', 'type' => 'warning');
        } else {
            if (!empty($datos)) {
                $idCredito = $datos['idCredito'];
                $restante = $datos['restante'];
                $total = $datos['total'];


                $dataElectronico = ($this->model->getCreditoElectronica($idCredito) == '') ? '' : $this->model->getCreditoElectronica($idCredito);
                $dataOrdenVenta = ($this->model->getCreditoOrdenVenta($idCredito) == '') ? '' : $this->model->getCreditoOrdenVenta($idCredito);
                $getCreditoInfos = $this->model->getCreditoInfo($idCredito);
                if (!empty($dataElectronico)) {
                    $dataElectronicos = $dataElectronico[0];
                    $datosCliente = $dataElectronicos;
                } else {
                    $datosCliente = $dataOrdenVenta;
                }

                // Las dos consultas de arriba traen el detalle de la factura y el
                // producto del catalogo, cosas que aqui no se usan para nada: de
                // $datosCliente solo sale el idCliente. Si el producto ya no esta
                // en el catalogo, esas consultas no devuelven nada y el abono se
                // quedaba bloqueado aunque el cliente estuviera identificado (solo
                // dejaba cobrar el total, que va por otro camino mas simple).
                // Se vuelve a buscar sin esas condiciones de mas.
                if (!is_array($datosCliente) || empty($datosCliente['idCliente'])) {
                    $datosCliente = $this->model->getIdClienteDeCredito($idCredito);
                }

                // Solo si ni la factura electronica ni la orden de venta llevan a un
                // cliente: sin esta guarda el acceso siguiente lanzaba un TypeError y
                // la peticion moria sin responder ("No se pudo enviar / HTTP ?").
                if (!is_array($datosCliente) || empty($datosCliente['idCliente'])) {
                    $res = array(
                        'msg'  => 'NO SE PUDO IDENTIFICAR AL CLIENTE DE ESTE CREDITO. VUELVA A BUSCARLO Y SELECCIONELO DE LA LISTA.',
                        'type' => 'error'
                    );
                    echo json_encode($res);
                    die();
                }

                $idCliente = $datosCliente['idCliente'];

                if ($total > $restante) {
                    $anticipo = $total - $restante;
                    $this->model->updateAnticipos($anticipo, $idCliente);
                } else {
                    $this->model->updateAnticipos(0, $idCliente);
                }
                foreach ($datos['tipoPagos'] as $tipoPagos) {
                    $codigoPago = (empty($tipoPagos['codigoComprobante'])) ? '' : $tipoPagos['codigoComprobante'];

                    // Validacion: tipo_pago obligatorio en cada abono
                    if (empty(trim((string)($tipoPagos['nombre'] ?? '')))) {
                        $res = array('msg' => 'CADA ABONO DEBE TENER UN TIPO DE PAGO SELECCIONADO', 'type' => 'error');
                        echo json_encode($res);
                        die();
                    }

                    if (isset($codigoPago)) {
                        if (!empty($codigoPago)) {
                            $getValidar = $this->model->getValidar($codigoPago);
                        }
                        $validadorCredito = ($getValidar == null || $getValidar == '') ? 0 : $getValidar['id_credito'];

                        if ($getValidar > 0) {
                            $res = array('msg' => 'EL NUMERO DE DOCUMNETO YA SE ENCUENTRA REGISTRADO EN EL CREDITO #' . $validadorCredito, 'type' => 'error');
                            echo json_encode($res);
                            die();
                        }
                    }

                    if ($tipoPagos['nombre'] == 'ANTICIPOS' && $tipoPagos['precio'] >= $restante) {
                        $data = $this->model->registrarAbono($restante, $fechas, $idCredito, $this->id_usuario, $codigoPago, $tipoPagos['nombre'], $idCliente,  $anticipo);
                    } else {
                        $data = $this->model->registrarAbono($tipoPagos['precio'], $fechas, $idCredito, $this->id_usuario, $codigoPago, $tipoPagos['nombre'], $idCliente,  $anticipo);
                    }
                }
                $deudaP =  $restante - $total;

                //para saber la deuda total para el envio por whatsapp
                if ($getCreditoInfos['id_electronica'] != null || $getCreditoInfos['id_electronica'] != '') {
                    $getDatos = $this->model->getIdCliente($getCreditoInfos['id_electronica']);
                    $getInfoClientes['telefono'] = ($getDatos['telefono'] == null) ? '' : $getDatos['telefono'];

                    $dataElectronico = $this->model->buscarPorNombreElectronicoMonto($getDatos['nombre']);
                    $dataOrdenVenta = $this->model->buscarPorNombreOrdenVentaMonto($idCliente);
                    $datosValores = array_merge($dataElectronico, $dataOrdenVenta);
                } else {
                    $getDatos = $this->model->buscarPorNombreOrdenVenta($getCreditoInfos['id_orden_venta']);
                    $getInfoClientes['telefono'] = ($getDatos[0]['telefono'] == null) ? '' : $getDatos[0]['telefono'];
                    $dataElectronico = $this->model->buscarPorNombreElectronicoMonto($getDatos[0]['nombre']);
                    $dataOrdenVenta = $this->model->buscarPorNombreOrdenVentaMonto($idCliente);
                    $datosValores = array_merge($dataElectronico, $dataOrdenVenta);
                }
                $abonado = 0;
                $deudaTotal = 0;
                if (!empty($datosValores)) {
                    for ($i = 0; $i < count($datosValores); $i++) {
                        $resultAbono = $this->model->getAbono($datosValores[$i]['id']);
                        $abonado += ($resultAbono['total'] == null) ? 0 : $resultAbono['total'];
                        $deudaTotal += $datosValores[$i]['monto'];
                    }
                    $restante = $deudaTotal - $abonado;
                }


                if (($deudaP <= 0) && $getCreditoInfos['estado'] == 1) {
                    //	print_r($deudaP);
                    //	print_r($getCreditoInfos['estado']); exit;


                    $this->model->actualizarCredito(0, $idCredito);
                    if (!empty($getCreditoInfos['id_electronica'])) {
                        $this->model->actualizarFacturaElectronica(1, $getCreditoInfos['id_electronica']);
                    }
                    if (!empty($getCreditoInfos['id_orden_venta'])) {
                        $this->model->actualizarFacturaOrdenVenta(1, $getCreditoInfos['id_orden_venta']);
                    }
                }
                //    $creditoInfo = $this->model->getCreditoInfo($idCredito);


                if ($data > 0) {
                    // WA se envia desde notificarCliente (plantilla con empresa de BD).
                    $res = array('msg' => 'ABONO REGISTRADO EXITOSAMENTE', 'type' => 'success', 'whatsapp' => null, 'idCredito' => isset($idCredito) ? $idCredito : (isset($idcredito) ? $idcredito : 0), 'telefonoCliente' => $getInfoClientes['telefono'] ?? '');
                } else {
                    $res = array('msg' => 'ERROR AL REGISTRAR', 'type' => 'error');
                }
            } else {
                $res = array('msg' => 'TODO LOS CAMPOS SON REQUERIDO', 'type' => 'warning');
            }
        }

        echo json_encode($res);
        die();
    }

    public function registrarAbonoVarios()
    {
        $fecha = date('Y-m-d');
        $hora = date('H:i:s');
        $fechas = $fecha . ' ' . $hora;
        $getValidar = null;
        //exit;
        $json = file_get_contents('php://input');
        $datos = json_decode($json, true);
        $codigoPago = $datos['codigoPagoVarios'];
        $tipoPago = $datos['tipoPagoVarios'];
        // print_r(count($datos['variosCreditos']));
        $cantidad = count($datos['variosCreditos']);
        $cantidadValores = count($datos['variosValores']);
        $totalAbonado = $datos['abonarVarios'];
        $mesActualLetra = MESES[date('n')];

        //$saldo = 0;
        //$total = 0;
        $abonado = 0;
        $restante = 0;
        //  $monto = 0;

        $verifcarCaja = $this->model->getCaja($this->id_usuario);
        if (empty($verifcarCaja)) { // caja abierta = existe fila (getCaja ya filtra estado=1); monto_inicial 0.00 es valido y empty(0.00) daba falso "cerrada"
            $res = array('msg' => 'LA CAJA ESTA CERRADA', 'type' => 'warning');
        } else {
            //  print_r($getCreditoInfos); exit;
            $getCreditoInfos = $this->model->getCreditoInfo($datos['variosCreditos'][0]);

            if ($getCreditoInfos['id_electronica'] != null || $getCreditoInfos['id_electronica'] != '') {
                $getDatos = $this->model->getIdCliente($getCreditoInfos['id_electronica']);
                $getInfoClientes['telefono'] = ($getDatos['telefono'] == null) ? '' : $getDatos['telefono'];
                $dataElectronico = $this->model->buscarPorNombreElectronicoMonto($getDatos['nombre']);
                $dataOrdenVenta = $this->model->buscarPorNombreOrdenVentaMonto($getDatos['idCliente']);
                $datosValores = array_merge($dataElectronico, $dataOrdenVenta);
            } else {
                $getDatos = $this->model->buscarPorNombreOrdenVenta($getCreditoInfos['id_orden_venta']);
                $getInfoClientes['telefono'] = ($getDatos[0]['telefono'] == null) ? '' : $getDatos[0]['telefono'];
                $dataElectronico = $this->model->buscarPorNombreElectronicoMonto($getDatos[0]['nombre']);
                $dataOrdenVenta = $this->model->buscarPorNombreOrdenVentaMonto($getDatos[0]['idCliente']);
                $datosValores = array_merge($dataElectronico, $dataOrdenVenta);
            }

            if (isset($getDatos['idCliente'])) {
                $idCliente = $getDatos['idCliente'];
                $nombreCliente = $getDatos['nombre'];
            } else {
                $idCliente = $getDatos[0]['idCliente'];
                $nombreCliente = $getDatos[0]['nombre'];
            }

            $codigoPago = (empty($codigoPago)) ? '' : $codigoPago;

            // Validacion: tipo_pago obligatorio (varios)
            if (empty(trim((string)$tipoPago))) {
                $res = array('msg' => 'EL TIPO DE PAGO ES OBLIGATORIO', 'type' => 'error');
                echo json_encode($res);
                die();
            }

            if (isset($codigoPago)) {

                if (!empty($codigoPago)) {

                    $getValidar = $this->model->getValidar($codigoPago);
                }
                $validadorCredito = ($getValidar == null || $getValidar == '') ? 0 : $getValidar['id_credito'];

                if ($getValidar > 0) {
                    $res = array('msg' => 'EL NUMERO DE DOCUMNETO YA SE ENCUENTRA REGISTRADO EN EL CREDITO #' . $validadorCredito, 'type' => 'error');
                    echo json_encode($res);
                    die();
                }
            }


            for ($i = 0; $i <= ($cantidad - 1); $i++) {

                $idCredito = $datos['variosCreditos'][$i];
                $monto = $datos['variosValores'][$i];
                $data = $this->model->registrarAbono($monto, $fechas, $idCredito, $this->id_usuario, $codigoPago, $tipoPago, $idCliente, 0);

                $getCreditoInfo = $this->model->getCreditoInfo($idCredito);

                if ($getCreditoInfo['estado'] == 1) {

                    $this->model->actualizarCredito(0, $idCredito);
                    if (!empty($getCreditoInfo['id_electronica'])) {
                        $this->model->actualizarFacturaElectronica(1, $getCreditoInfo['id_electronica']);
                    }
                    if (!empty($getCreditoInfo['id_orden_venta'])) {
                        $this->model->actualizarFacturaOrdenVenta(1, $getCreditoInfo['id_orden_venta']);
                    }
                }


                //echo json_encode($res);
            }
            if (!empty($datos)) {
                // print_r($datosValores); exit;
                $deudaTotal = 0;
                if (!empty($datosValores)) {

                    for ($i = 0; $i < count($datosValores); $i++) {
                        $resultAbono = $this->model->getAbono($datosValores[$i]['id']);
                        $abonado += ($resultAbono['total'] == null) ? 0 : $resultAbono['total'];
                        //calcular restante  (monto - abono)
                        $deudaTotal += $datosValores[$i]['monto'];
                        //array_push($data, $result);
                    }
                    $restante = $deudaTotal - $abonado;
                    $data2['abonado'] = $abonado;
                    $data2['restante'] = $restante;
                    // $restante = $restante - $abonado;
                    //   print_r($restante); exit;
                } else {

                    $data2['abonado'] = $abonado;
                    $data2['restante'] = $restante;
                }
                if ($data > 0) {
                    // WA se envia desde notificarCliente (plantilla con empresa de BD).
                    $res = array('msg' => 'ABONO REGISTRADO EXITOSAMENTE', 'type' => 'success', 'whatsapp' => null, 'idCredito' => isset($idCredito) ? $idCredito : (isset($idcredito) ? $idcredito : 0), 'telefonoCliente' => $getInfoClientes['telefono'] ?? '');
                } else {
                    $res = array('msg' => 'ERROR AL REGISTRAR', 'type' => 'error');
                }
                //  $tipoPago = strClean($datos['tipoPago']);
                // $codigoPago = strClean($datos['codigoPago']);

                // print_r($datos); exit;

                // print_r($validadorCredito); exit;

            } else {
                $res = array('msg' => 'TODO LOS CAMPOS SON REQUERIDO', 'type' => 'warning');
            }
        }
        echo json_encode($res);
        die();
    }
    public function buscarClienteCreditos()
    {
        header('Content-Type: application/json; charset=utf-8');
        if (empty($_SESSION['id_usuario'])) { echo json_encode([]); exit; }
        $term = trim((string)($_GET['term'] ?? ''));
        if (mb_strlen($term) < 2) { echo json_encode([]); exit; }
        $like = '%' . $term . '%';
        try {
            $pdo = new PDO('mysql:host=' . HOSTT . ';dbname=' . DBNAME . ';charset=utf8mb4', USER, PASSWORD,
                [PDO::ATTR_ERRMODE => PDO::ERRMODE_SILENT]);
            // Buscar clientes con creditos pendientes (orden_venta o electronica).
            // Para electronica, dce.id_cliente solo existe desde 2026-01; en
            // facturas anteriores cae a un match por nombre (LIKE).
            $sql = "SELECT DISTINCT cl.id, cl.nombre, cl.num_identidad, cl.telefono,
                    (
                      (SELECT COUNT(*) FROM creditos cr2 INNER JOIN orden_venta ov2 ON ov2.id = cr2.id_orden_venta
                         WHERE ov2.id_cliente = cl.id AND cr2.estado = 1)
                      +
                      (SELECT COUNT(*) FROM creditos cr3 INNER JOIN datos_cabecera_electronica dce3 ON dce3.orden_no = cr3.id_electronica
                         WHERE cr3.estado = 1
                           AND (dce3.id_cliente = cl.id OR (dce3.id_cliente IS NULL AND dce3.cliente = cl.nombre)))
                    ) AS pendientes
                FROM clientes cl
                WHERE (cl.nombre LIKE ? OR cl.num_identidad LIKE ?)
                  AND cl.estado = 1
                  AND (
                      EXISTS (
                          SELECT 1 FROM creditos cr INNER JOIN orden_venta ov ON ov.id = cr.id_orden_venta
                          WHERE ov.id_cliente = cl.id AND cr.estado = 1
                      )
                      OR EXISTS (
                          SELECT 1 FROM creditos cre INNER JOIN datos_cabecera_electronica dce ON dce.orden_no = cre.id_electronica
                          WHERE cre.estado = 1
                            AND (dce.id_cliente = cl.id OR (dce.id_cliente IS NULL AND dce.cliente = cl.nombre))
                      )
                  )
                LIMIT 12";
            $st = $pdo->prepare($sql);
            $st->execute([$like, $like]);
            $rows = $st->fetchAll(PDO::FETCH_ASSOC);
            $out = [];
            foreach ($rows as $r) {
                $out[] = [
                    'value' => $r['id'],
                    'label' => $r['nombre'] . ' [' . $r['num_identidad'] . '] - ' . $r['pendientes'] . ' pendiente(s)',
                    'id'    => $r['id'],
                    'nombre'=> $r['nombre'],
                    'telefono' => $r['telefono'],
                ];
            }
            echo json_encode($out, JSON_UNESCAPED_UNICODE);
        } catch (\Throwable $e) {
            echo json_encode([]);
        }
        exit;
    }

    public function listarCreditosCliente($idCliente = 0)
    {
        header('Content-Type: application/json; charset=utf-8');
        if (empty($_SESSION['id_usuario'])) { echo json_encode([]); exit; }
        $idCliente = (int)$idCliente;
        if ($idCliente <= 0) { echo json_encode([]); exit; }
        try {
            $pdo = new PDO('mysql:host=' . HOSTT . ';dbname=' . DBNAME . ';charset=utf8mb4', USER, PASSWORD,
                [PDO::ATTR_ERRMODE => PDO::ERRMODE_SILENT]);
            // Cargar creditos pendientes del cliente desde orden_venta Y facturacion electronica.
            // Para electronicas, dce.id_cliente solo existe desde 2026-01; antes era NULL y
            // se ata por nombre. Se necesita el nombre real del cliente para el LIKE/equal.
            $stN = $pdo->prepare("SELECT nombre FROM clientes WHERE id = ?");
            $stN->execute([$idCliente]);
            $nombreCli = (string)($stN->fetchColumn() ?: '');

            $sql = "SELECT cr.id, cr.monto, cr.id_orden_venta, cr.id_electronica, cr.fecha,
                       IFNULL((SELECT SUM(a.abono) FROM abonos a WHERE a.id_credito = cr.id), 0) AS abonado
                FROM creditos cr
                INNER JOIN orden_venta ov ON ov.id = cr.id_orden_venta
                WHERE ov.id_cliente = ? AND cr.estado = 1
                UNION
                SELECT cr.id, cr.monto, cr.id_orden_venta, cr.id_electronica, cr.fecha,
                       IFNULL((SELECT SUM(a.abono) FROM abonos a WHERE a.id_credito = cr.id), 0) AS abonado
                FROM creditos cr
                INNER JOIN datos_cabecera_electronica dce ON dce.orden_no = cr.id_electronica
                WHERE cr.estado = 1
                  AND (dce.id_cliente = ? OR (dce.id_cliente IS NULL AND dce.cliente = ?))
                ORDER BY fecha ASC";
            $st = $pdo->prepare($sql);
            $st->execute([$idCliente, $idCliente, $nombreCli]);
            echo json_encode($st->fetchAll(PDO::FETCH_ASSOC), JSON_UNESCAPED_UNICODE);
        } catch (\Throwable $e) {
            echo json_encode([]);
        }
        exit;
    }

    public function notificarCliente($idCredito = 0)
    {
        header('Content-Type: application/json; charset=utf-8');
        if (empty($_SESSION['id_usuario'])) { echo json_encode(['ok'=>false,'msg'=>'No autorizado']); exit; }
        $idCredito = (int)$idCredito;
        if ($idCredito <= 0) { echo json_encode(['ok'=>false,'msg'=>'ID invalido']); exit; }

        // Cargar datos del credito + cliente
        $row = $this->model->getCreditosConAbonosPaginado(0, 1, '', 1);
        // El metodo de arriba filtra por estado=1, no sirve para arbitrario.
        // Hacemos query directa por id:
        try {
            $pdo = new PDO('mysql:host=' . HOSTT . ';dbname=' . DBNAME . ';charset=utf8mb4', USER, PASSWORD,
                [PDO::ATTR_ERRMODE => PDO::ERRMODE_SILENT]);
            $st = $pdo->prepare("SELECT cr.*,
                CASE WHEN cr.id_electronica IS NOT NULL THEN dce.cliente
                     WHEN cr.id_orden_venta IS NOT NULL THEN cl.nombre ELSE '' END AS nombre,
                CASE WHEN cr.id_electronica IS NOT NULL THEN dce.telefono
                     WHEN cr.id_orden_venta IS NOT NULL THEN cl.telefono ELSE '' END AS telefono,
                IFNULL((SELECT SUM(a.abono) FROM abonos a WHERE a.id_credito = cr.id), 0) AS abonado
                FROM creditos cr
                LEFT JOIN datos_cabecera_electronica dce ON cr.id_electronica = dce.orden_no
                LEFT JOIN orden_venta ov ON cr.id_orden_venta = ov.id
                LEFT JOIN clientes cl ON ov.id_cliente = cl.id
                WHERE cr.id = ? LIMIT 1");
            $st->execute([$idCredito]);
            $r = $st->fetch(PDO::FETCH_ASSOC);
        } catch (\Throwable $e) {
            echo json_encode(['ok'=>false,'msg'=>'Error BD: '.$e->getMessage()]); exit;
        }
        if (!$r) { echo json_encode(['ok'=>false,'msg'=>'Credito no encontrado']); exit; }

        $tel = preg_replace('/[^0-9]/', '', (string)($r['telefono'] ?? ''));
        if (empty($tel)) { echo json_encode(['ok'=>false,'msg'=>'Cliente sin telefono']); exit; }
        // Normalizar a +593 si es local
        if (strlen($tel) === 10 && $tel[0] === '0') $tel = '593' . substr($tel, 1);
        elseif (strlen($tel) === 9) $tel = '593' . $tel;

        $estado = (int)$r['estado'];
        $monto    = (float)$r['monto'];
        $abonado  = (float)$r['abonado'];
        $restante = $monto - $abonado;

        // Calcular SALDO TOTAL REAL del cliente: suma de restantes de TODOS sus creditos pendientes
        // Esto es lo que el cliente realmente debe en este momento.
        $saldoTotalCliente = $restante;
        try {
            // Identificar al cliente segun el origen del credito
            $clienteIdentidad = null;
            if (!empty($r['id_orden_venta'])) {
                $stCli = $pdo->prepare("SELECT cl.id FROM orden_venta ov INNER JOIN clientes cl ON cl.id = ov.id_cliente WHERE ov.id = ? LIMIT 1");
                $stCli->execute([$r['id_orden_venta']]);
                $cliRow = $stCli->fetch(PDO::FETCH_ASSOC);
                if ($cliRow) $clienteIdentidad = ['tipo'=>'idCliente', 'val'=>$cliRow['id']];
            } elseif (!empty($r['id_electronica'])) {
                $stCli = $pdo->prepare("SELECT ruc FROM datos_cabecera_electronica WHERE orden_no = ? LIMIT 1");
                $stCli->execute([$r['id_electronica']]);
                $cliRow = $stCli->fetch(PDO::FETCH_ASSOC);
                if ($cliRow) $clienteIdentidad = ['tipo'=>'ruc', 'val'=>$cliRow['ruc']];
            }
            if ($clienteIdentidad) {
                if ($clienteIdentidad['tipo'] === 'idCliente') {
                    $sql = "SELECT SUM(cr.monto - IFNULL((SELECT SUM(a.abono) FROM abonos a WHERE a.id_credito = cr.id), 0)) AS saldo_total
                            FROM creditos cr
                            INNER JOIN orden_venta ov ON ov.id = cr.id_orden_venta
                            WHERE ov.id_cliente = ? AND cr.estado = 1";
                    $stT = $pdo->prepare($sql);
                    $stT->execute([$clienteIdentidad['val']]);
                } else {
                    $sql = "SELECT SUM(cr.monto - IFNULL((SELECT SUM(a.abono) FROM abonos a WHERE a.id_credito = cr.id), 0)) AS saldo_total
                            FROM creditos cr
                            INNER JOIN datos_cabecera_electronica dce ON dce.orden_no = cr.id_electronica
                            WHERE dce.ruc = ? AND cr.estado = 1";
                    $stT = $pdo->prepare($sql);
                    $stT->execute([$clienteIdentidad['val']]);
                }
                $rt = $stT->fetch(PDO::FETCH_ASSOC);
                if ($rt && $rt['saldo_total'] !== null) {
                    $saldoTotalCliente = (float)$rt['saldo_total'];
                }

                // Restar anticipos historicos: clientes.anticipos guarda el saldo a favor
                // acumulado cuando el cliente paga de mas. Sin esto, el mensaje pierde
                // ese anticipo y muestra 0 en lugar de "a favor".
                if ($clienteIdentidad['tipo'] === 'idCliente') {
                    $stA = $pdo->prepare("SELECT IFNULL(anticipos,0) AS anticipos FROM clientes WHERE id = ? LIMIT 1");
                    $stA->execute([$clienteIdentidad['val']]);
                } else {
                    $stA = $pdo->prepare("SELECT IFNULL(anticipos,0) AS anticipos FROM clientes WHERE num_identidad = ? LIMIT 1");
                    $stA->execute([$clienteIdentidad['val']]);
                }
                $rtA = $stA->fetch(PDO::FETCH_ASSOC);
                if ($rtA) {
                    $saldoTotalCliente -= (float)$rtA['anticipos'];
                }
            }
        } catch (\Throwable $e) { /* fallback al restante de este credito */ }

        // Leer body para soportar tipo override y mensaje custom
        $bodyIn = json_decode(file_get_contents('php://input'), true) ?: [];
        // Decidir plantilla segun estado, pero permitir override via ?tipo=pagado/pendiente
        $tipoForzado = $_GET['tipo'] ?? ($bodyIn['tipo'] ?? '');
        if ($tipoForzado === 'pagado') {
            $plantillaKey = 'whatsapp_pago_recibido';
        } elseif ($tipoForzado === 'pendiente') {
            $plantillaKey = 'whatsapp_recordatorio';
        } else {
            $plantillaKey = ($estado === 1) ? 'whatsapp_recordatorio' : (($estado === 0 || $estado === 3) ? 'whatsapp_pago_recibido' : null);
        }
        if (!$plantillaKey) { echo json_encode(['ok'=>false,'msg'=>'Estado del credito no notificable']); exit; }

        // Mes (en espanol, mayusculas) del servicio cubierto por este credito.
        // Se deriva de cr.fecha; si falta, fallback a la fecha actual.
        // Usar el mes EN CURSO (no la fecha del credito) — el cliente puede estar
        // pagando atrasos de meses anteriores, pero el mensaje confirma el saldo a la fecha de hoy.
        $mesesEs = ['ENERO','FEBRERO','MARZO','ABRIL','MAYO','JUNIO','JULIO','AGOSTO','SEPTIEMBRE','OCTUBRE','NOVIEMBRE','DICIEMBRE'];
        $tsServ = time();
        $servicioMesesTxt = $mesesEs[(int)date('n', $tsServ) - 1] . ' ' . date('Y', $tsServ);

        // Renderizar plantilla con SALDO TOTAL REAL del cliente.
        // Saldo > 0  : el cliente debe (pendiente)
        // Saldo == 0 : esta en cero
        // Saldo < 0  : pago de mas -> saldo a favor (anticipo)
        if ($saldoTotalCliente < -0.001) {
            $clienteSaldoStr = number_format(abs($saldoTotalCliente), 2) . ' a favor';
        } else {
            $clienteSaldoStr = number_format(max(0, $saldoTotalCliente), 2);
        }
        $vars = [
            'cliente_nombre'   => trim($r['nombre'] ?? ''),
            'cliente_saldo'    => $clienteSaldoStr,
            'cliente_telefono' => $tel,
            'servicio_meses'   => $servicioMesesTxt,
        ];
        // Si el frontend envia un mensaje custom (editado por el usuario), usarlo
        $mensajeCustom = isset($bodyIn['mensaje']) ? trim((string)$bodyIn['mensaje']) : '';
        if ($mensajeCustom !== '') {
            $cuerpo = $mensajeCustom;
        } else {
            if (function_exists('renderPlantilla')) {
                $tpl = renderPlantilla($plantillaKey, $vars);
                $cuerpo = is_array($tpl) ? ($tpl['cuerpo'] ?? '') : '';
            } else {
                $cuerpo = '';
            }
            if (empty($cuerpo)) {
                $cuerpo = ($estado === 1)
                    ? '*Recordatorio de pago*' . PHP_EOL . PHP_EOL . 'Estimado/a ' . $vars['cliente_nombre'] . ', tienes un saldo pendiente de $' . $vars['cliente_saldo'] . '.'
                    : '*Pago recibido*' . PHP_EOL . PHP_EOL . 'Estimado/a ' . $vars['cliente_nombre'] . ', confirmamos la recepcion de tu pago. Gracias!';
            }
        }

        // Resolver URL del WhatsApp API (servicios.json -> alertas-config.json -> default)
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

        // Buscar session_id activa
        $sessionId = '';
        $f = ROOT_PATH . '/storage/alertas-config.json';
        if (file_exists($f)) {
            $j = @json_decode(@file_get_contents($f), true);
            $sessionId = $j['wa_api']['session_id'] ?? '';
        }
        if (empty($sessionId)) { echo json_encode(['ok'=>false,'msg'=>'No hay sesion WhatsApp vinculada']); exit; }

        // Modo preview: solo devolver el mensaje renderizado
        if (!empty($_GET['preview']) || !empty($_POST['preview'])) {
            echo json_encode([
                'ok'=>true, 'preview'=>true,
                'mensaje'=>$cuerpo,
                'telefono'=>$tel,
                'tipo'=>($plantillaKey === 'whatsapp_pago_recibido' ? 'pagado' : 'pendiente'),
                'plantilla_key'=>$plantillaKey,
            ], JSON_UNESCAPED_UNICODE);
            exit;
        }

        // Enviar via WhatsApp API
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
        $err  = curl_error($ch);
        curl_close($ch);

        $ok = ($code >= 200 && $code < 300);
        echo json_encode([
            'ok' => $ok,
            'tipo' => ($estado === 1) ? 'pendiente' : 'pagado',
            'telefono' => $tel,
            'http' => $code,
            'error' => $err ?: null,
        ]);
        exit;
    }

    public function reporte($idCredito)
    {
        ob_start();
        $data['title'] = 'Reporte';
        $data['empresa'] = $this->model->getEmpresa();
        $data['credito'] = $this->model->getCredito($idCredito);
        $data['creditoE'] = $this->model->getCreditoElectronica($idCredito);
        $data['creditoOV'] = $this->model->getCreditoOrdenVenta($idCredito);

        $data['abonos'] = $this->model->getAbonos($idCredito);

        //print_r($data['creditoE']);exit;

        if (empty($data['credito']) && empty($data['creditoE']) && empty($data['creditoOV'])) {
            echo 'Pagina no Encontrada';
            exit;
        }

        if (!empty($data['credito'])) {
            $this->views->getView('creditos', 'reporte', $data);
        } else if (!empty($data['creditoE'])) {
            $this->views->getView('creditos', 'reporteElectronico', $data);
        } else {
            $this->views->getView('creditos', 'reporteOrdenVenta', $data);
        }

        $html = ob_get_clean();
        $dompdf = new Dompdf();
        $options = $dompdf->getOptions();
        $options->set('isJavascriptEnabled', true);
        $options->set('isRemoteEnabled', true);
        $dompdf->setOptions($options);
        $dompdf->loadHtml($html);

        $dompdf->setPaper('A4', 'vertical');

        // Render the HTML as PDF
        $dompdf->render();

        // Output the generated PDF to Browser
        $dompdf->stream('reporte.pdf', array('Attachment' => false));
    }

        public function listarAbonos()
    {
        $start  = isset($_POST['start']) ? intval($_POST['start']) : 0;
        $length = isset($_POST['length']) ? intval($_POST['length']) : 25;
        $search = isset($_POST['search']['value']) ? trim($_POST['search']['value']) : '';
        $draw   = isset($_POST['draw']) ? intval($_POST['draw']) : 1;

        $total = $this->model->contarAbonosUnificados('');
        $filtered = $search ? $this->model->contarAbonosUnificados($search) : $total;
        $data = $this->model->getAbonosUnificadosPaginado($start, $length, $search);

        foreach ($data as &$d) {
            $d['credito'] = 'N°: ' . $d['id_credito'];
            // Badge para tipo_pago: color segun el tipo
            $tp = strtoupper(trim((string)($d['tipo_pago'] ?? '')));
            $colors = [
                'EFECTIVO'      => 'success',
                'TRANSFERENCIA' => 'primary',
                'DEPOSITOS'     => 'info',
                'CHEQUE'        => 'warning',
                'ANTICIPOS'     => 'secondary',
                'RETENCIONES'   => 'dark',
            ];
            $color = $colors[$tp] ?? 'light text-dark';
            $codigo = trim((string)($d['codigo_pago'] ?? ''));
            $extra = $codigo !== '' ? '<small class="text-muted d-block" style="font-size:.7rem;">' . htmlspecialchars($codigo) . '</small>' : '';
            $d['tipo_pago'] = '<span class="badge bg-' . $color . '">' . htmlspecialchars($tp) . '</span>' . $extra;

            if ($_SESSION['rol'] == 3) {
                $d['acciones'] = '';
            } else {
                $d['acciones'] = '<div><button class="btn btn-danger" type="button" onclick="eliminarAbono(' . $d['id'] . ')"><i class="fas fa-trash"></i></button></div>';
            }
        }

        echo json_encode([
            'draw' => $draw,
            'recordsTotal' => $total,
            'recordsFiltered' => $filtered,
            'data' => $data,
        ], JSON_UNESCAPED_UNICODE);
        die();
    }

    public function eliminarAbono($idAbono)
    {
        $anticipo = 0;
        if (isset($_GET) && is_numeric($idAbono)) {
            $dataAbono = $this->model->getAbonoEliminar($idAbono);

            $dataInfoCredito = $this->model->getCreditoInfo($dataAbono['id_credito']);
            if ($dataInfoCredito['estado'] == 0) {
                $dataInfoCredito = $this->model->updateEstadoCredito(1, $dataAbono['id_credito']);
            }

            if ($dataAbono['tipo_pago'] == 'ANTICIPOS') {
                $getDataCliente = $this->model->getCliente($dataAbono['id_cliente']);
                $anticipo = $getDataCliente['anticipos'] + $dataAbono['abono'];

                // print_r($anticipoCliente);exit;

                $getValidar = $this->model->updateAnticipos($anticipo, $dataAbono['id_cliente']);
            } else {
                $getDataCliente = $this->model->getCliente($dataAbono['id_cliente']);
                $anticipo = $getDataCliente['anticipos'] - $dataAbono['anticipo'];

                $getValidar = $this->model->updateAnticipos($anticipo, $dataAbono['id_cliente']);
            }

            $data = $this->model->eliminarAbono($idAbono);
            if ($data == 1) {
                $res = array('msg' => 'ABONO ELIMINADO EXITOSAMENTE', 'type' => 'success');
            } else {
                $res = array('msg' => 'ERROR AL ELIMINAR', 'type' => 'error');
            }
        } else {
            $res = array('msg' => 'ERROR DESCONOCIDO', 'type' => 'error');
        }
        echo json_encode($res, JSON_UNESCAPED_UNICODE);
        die();
    }
}

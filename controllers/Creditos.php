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
            $c['acciones'] = '<a class="btn btn-danger" href="' . BASE_URL . 'creditos/reporte/' . $c['id'] . '" target="_blank"><i class="fas fa-file-pdf"></i></a>';
            if ($c['estado'] == 1) {
                $c['estado'] = '<span class="badge bg-warning">PENDIENTE</span>';
                $c['ch'] = '<div class="btn-group-toggle" data-toggle="buttons"><label class="btn btn-primary"><input type="checkbox" id="' . $c['id'] . '" value="' . $restante . '"></label></div>';
            } else if ($c['estado'] == 2) {
                $c['estado'] = '<span class="badge bg-danger">ANULADO</span>';
                $c['ch'] = '';
            } else {
                $c['estado'] = '<span class="badge bg-success">COMPLETADO</span>';
                $c['ch'] = '';
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
        $valor = strClean($_GET['term']);

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
        if (empty($verifcarCaja['monto_inicial'])) {
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
                    if ($getInfoClientes['telefono'] == '') {

                        $resWathsapp = null;
                    } else {
                        // $resWathsapp = "https://wa.me/593{$getInfoClientes['telefono']}?text=Buen Dia estimado cliente *MEGAHNET*! Su abono es de: ' . '$' . $total . ', *Su saldo a favor es de: ' . '$' . $anticipo . '*    Su deuda Total es de:' . '$' . $restante . ', INCLUIDO SERVICIO DE *' . $mesActualLetra . '*";
                        //$resWathsapp = 'https://web.whatsapp.com/send?text=Buen Dia estimado cliente *MEGAHNET*! Su deuda Total es de:' . '$' . $restante . ', INCLUIDO SERVICIO DE *' . $mesActualLetra . '*    &phone=+593' . $getInfoClientes['telefono'] . '&abid=+593' . $getInfoClientes['telefono'] . '';.$restante.'
                        $resWathsapp = 'https://web.whatsapp.com/send?phone=593' . $getInfoClientes['telefono'] . '&text=Buen%20d%C3%ADa%20estimado%2Fa%20cliente%0A%20%20%20%20%20%20%20%20%20%20*MEGAHNET*%0A%20%20%20*GRACIAS%20POR%20SU%20PAGO*%0A%0A%20%20su%20saldo%20a%20la%20fecha%20es%3A%0A%20%20%20%20%20%20%20%20%20%20%20%20%24' . $restante . '%0Aincluido%20*SERVICIO%20' . $mesActualLetra . '*%0A*' . $datosCliente['nombre'] . '*';
                    }

                    $res = array('msg' => 'ABONO REGISTRADO EXITOSAMENTE', 'type' => 'success', 'whatsapp' => $resWathsapp);
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
        if (empty($verifcarCaja['monto_inicial'])) {
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

                $idCliente = $datosCliente['idCliente'];

                if ($total > $restante) {
                    $anticipo = $total - $restante;
                    $this->model->updateAnticipos($anticipo, $idCliente);
                } else {
                    $this->model->updateAnticipos(0, $idCliente);
                }
                foreach ($datos['tipoPagos'] as $tipoPagos) {
                    $codigoPago = (empty($tipoPagos['codigoComprobante'])) ? '' : $tipoPagos['codigoComprobante'];

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
                    if ($getInfoClientes['telefono'] == '') {
                        $resWathsapp = null;
                    } else {
                        $resWathsapp = 'https://web.whatsapp.com/send?phone=593' . $getInfoClientes['telefono'] . '&text=Buen%20d%C3%ADa%20estimado%2Fa%20cliente%0A*MEGAHNET*%0A*GRACIAS%20POR%20SU%20PAGO*%0A%0ASu%20saldo%20a%20la%20fecha%20es%3A%20$' . $restante . '%0AIncluido%20servicio%20de%20*' . $mesActualLetra . '*%0A*' . $datosCliente['nombre'] . '*';
                    }

                    $res = array('msg' => 'ABONO REGISTRADO EXITOSAMENTE', 'type' => 'success', 'whatsapp' => $resWathsapp);
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
        if (empty($verifcarCaja['monto_inicial'])) {
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
                    if ($getInfoClientes['telefono'] == '') {

                        $resWathsapp = null;
                    } else {
                        //$resWathsapp = 'https://web.whatsapp.com/send?text=Buen Dia estimado cliente *MEGAHNET*! Su deuda Total es de: ' . '$ ' . $restante .', INCLUIDO SERVICIO DE *' . $mesActualLetra . '* &phone=+593' . $getInfoClientes['telefono'] . '&abid=+593' . $getInfoClientes['telefono'] . '';
                        $resWathsapp = 'https://web.whatsapp.com/send?phone=593' . $getInfoClientes['telefono'] . '&text=Buen%20d%C3%ADa%20estimado%2Fa%20cliente%0A%20%20%20%20%20%20%20%20%20%20*MEGAHNET*%0A%20%20%20*GRACIAS%20POR%20SU%20PAGO*%0A%0A%20%20su%20saldo%20a%20la%20fecha%20es%3A%0A%20%20%20%20%20%20%20%20%20%20%20%20%24' . $restante . '%0Aincluido%20*SERVICIO%20' . $mesActualLetra . '*%0A*' . $nombreCliente . '*';
                    }

                    $res = array('msg' => 'ABONO REGISTRADO EXITOSAMENTE', 'type' => 'success', 'whatsapp' => $resWathsapp);
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

        // Decidir plantilla segun estado
        $plantillaKey = ($estado === 1) ? 'whatsapp_recordatorio' : (($estado === 0 || $estado === 3) ? 'whatsapp_pago_recibido' : null);
        if (!$plantillaKey) { echo json_encode(['ok'=>false,'msg'=>'Estado del credito no notificable']); exit; }

        // Renderizar plantilla
        $vars = [
            'cliente_nombre'   => trim($r['nombre'] ?? ''),
            'cliente_saldo'    => number_format($restante, 2),
            'cliente_telefono' => $tel,
            'servicio_meses'   => '',
        ];
        if (function_exists('renderPlantilla')) {
            $tpl = renderPlantilla($plantillaKey, $vars);
            $cuerpo = is_array($tpl) ? ($tpl['cuerpo'] ?? '') : '';
        } else {
            $cuerpo = '';
        }
        if (empty($cuerpo)) {
            // Fallback simple
            $cuerpo = ($estado === 1)
                ? '*Recordatorio de pago*' . PHP_EOL . PHP_EOL . 'Estimado/a ' . $vars['cliente_nombre'] . ', tienes un saldo pendiente de $' . $vars['cliente_saldo'] . '.'
                : '*Pago recibido*' . PHP_EOL . PHP_EOL . 'Estimado/a ' . $vars['cliente_nombre'] . ', confirmamos la recepcion de tu pago. Gracias!';
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

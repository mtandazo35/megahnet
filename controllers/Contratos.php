<?php
require 'vendor/autoload.php';

use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Style\Color;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpWord\TemplateProcessor;

class Contratos extends Controller
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
        $data['title']      = 'Contratos';
        $data['script']     = 'contratos.js';
        $data['validacion'] = 'validacion.js';

        $data['busqueda'] = 'busqueda.js';
        $data['carrito']  = 'posContratos';

        $data['clienteNuevo'] = $this->model->clienteNuevo(1);
        //$data['ipNueva'] = $this->model->ipNueva(null);
        $data['zonas']       = $this->model->zonas(1);
        $data['repetidoras'] = $this->model->getRepetidoras(1);
        $data['tipoPago']    = $this->model->tipoPago();
        $data['mikrotiks'] = $this->model->getMikrotiks(1);

        //$data['ipNuevaAnuladas'] = $this->model->ipNuevaAnuladas(null);

        //print_r( $data['ipNueva']); exit;
        $this->views->getView('contratos', 'index', $data);
    }
    public function registrarExcel()
    {

        if ($_SESSION['rol'] == 3) {
            $res = ['msg' => 'ERROR EN EL PROCESO', 'type' => 'error'];
            echo json_encode($res, JSON_UNESCAPED_UNICODE);
            die();
        } else {
            //  print_r($_FILES['excel']); exit;
            $datosExcel = $_FILES['excel'];
            if ($datosExcel['size'] > 0) {
                //    $direccion = strClean($_POST['direccion']);
                //   print_r($chelectronica); exit;
                if ($datosExcel['type'] != 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet') {
                    $res = ['msg' => 'SELECCIONES UN ARCHIVO EXCEL', 'type' => 'error'];
                } else {

                    $archivoContent = $datosExcel['tmp_name'];
                    importarExcel($archivoContent);
                }
            } else {
                $res = ['msg' => 'ERROR DESCONOCIDO', 'type' => 'error'];
                echo json_encode($res, JSON_UNESCAPED_UNICODE);
            }
        }

        die();
    }
    public function registrarContratos()
    {
        $pendingMikrotik = null; // se ejecuta despues del response
        $json = file_get_contents('php://input');
        $datos = json_decode($json, true);
        $array['productos'] = [];
        //$arraySimpleQueues = array();

        $total = 0;

        if ($_SESSION['rol'] == 3) {
            $res = ['msg' => 'ERROR EN EL PROCESO', 'type' => 'error'];
            echo json_encode($res, JSON_UNESCAPED_UNICODE);
            die();
        } else {
            // print_r($datos); exit;
            //print_r($datos); exit;
            if (!empty($datos['productos'])) {
                $fecha = date('Y-m-d');
                $hora = date('H:i:s');
                $id = $datos['id'];
                $idIp = (empty($datos['idIp'])) ? null : $datos['idIp'];

                //  $idIp = $datos['idIp'];

                /* $data['ip'] = $this->model->ipNueva($idIp,null);

            $array1 = explode('.', $data['ip']['ultima']);
            $cat1 = $array1[0];
            $cat2 = $array1[1];
            $cat3 = $array1[2];
            $cat4 = $array1[3];


            $ipUsuario = $cat1 . '.' . $cat2 . '.' . $cat3 . '.' . ($cat4 + 1);*/
                $origenIp = $datos['origenIp'];
                $idIpAnuladas = $datos['idIpAnuladas'];
                $idZona = trim(strClean($datos['idZona']));

                $ipUsuario = trim(strClean($datos['ipUsuario']));
                $repetidora = $datos['repetidora'];
                $ap = trim(strClean($datos['ap']));
                $coordenada = trim(strClean($datos['coordenada']));
                $direccion = trim(strClean($datos['direccion']));
                $comentario = trim(strClean($datos['comentario']));
                $medio = $datos['medio'];
                $comparticion = $datos['comparticion'];
                $anchoBanda = $datos['anchoBanda'];
                $discapacidad = trim(strClean($datos['discapacidad']));
                $idMikrotik = $datos['idMikrotik'];
                $ciudad = $datos['ciudad'];

                $chelectronica = (strClean(isset($datos['chelectronica']))) ? $datos['chelectronica'] : 0;
                //$tipoBanco = $datos['tipoBanco'];
                //$cuentaBancaria = $datos['cuentaBancaria'];
                //print_r($ipUsuario); exit;
                $idCliente = $datos['idCliente'];

                /*$datos['productos'] = [
                'idZona' =>  $idZona,
                'idIP' =>   $idIp

                    ];*/
                //print_r($idIp); exit;

                //print_r($idZona); exit;
                if (empty($idCliente)) {
                    $res = ['msg' => 'EL CLIENTE ES REQUERIDO', 'type' => 'warning'];
                } else if (empty($ipUsuario)) {
                    $res = ['msg' => 'LA IP USUARIO ES REQUERIDO', 'type' => 'warning'];
                } else if (empty($repetidora)) {
                    $res = ['msg' => 'LA REPETIDORA ES REQUERIDO', 'type' => 'warning'];
                } else if (empty($ap)) {
                    $res = ['msg' => 'LA AP ES REQUERIDO', 'type' => 'warning'];
                } else if (empty($coordenada)) {
                    $res = ['msg' => 'LA COORDENADA ES REQUERIDO', 'type' => 'warning'];
                } else if (empty($direccion)) {
                    $res = ['msg' => 'LA DIRECCION ES REQUERIDO', 'type' => 'warning'];
                } else if (empty($medio)) {
                    $res = ['msg' => 'EL MEDIO TX/RX ES REQUERIDO', 'type' => 'warning'];
                } else if (empty($comparticion)) {
                    $res = ['msg' => 'LA COMPARTICIÓN ES REQUERIDO', 'type' => 'warning'];
                } else if (empty($anchoBanda)) {
                    $res = ['msg' => 'EL ANCHO DE BANDA ES REQUERIDO', 'type' => 'warning'];
                } else if (empty($ciudad)) {
                    $res = ['msg' => 'LA CIUDAD ES REQUERIDO', 'type' => 'warning'];
                } else {
                    if ($id == '') {
                        foreach ($datos['productos'] as $producto) {
                            $result = $this->model->getProducto($producto['id']);
                            $data['id'] = $result['id'];
                            $data['nombre'] = $producto['nombre'];
                            $data['precio'] = $producto['precio'];
                            $data['cantidad'] = $producto['cantidad'];
                            $data['iva_producto'] = $result['iva'];
                            $data['id_categoria'] = $result['id_categoria'];
                            $data['stockActual'] = $result['cantidad'];
                            $data['idZona'] = $idZona;
                            $data['idIp'] = $idIp;

                            $subTotal = $producto['precio'] * $producto['cantidad'];
                            array_push($array['productos'], $data);
                            $total += $subTotal;
                        }
                        $datosProductos = json_encode($array['productos']);
                        //print_r($datosProductos); exit;
                        $contrato = $this->model->registrarContrato(
                            $fecha,
                            $hora,
                            $idCliente,
                            $this->id_usuario,
                            $datosProductos,
                            $total,
                            $ipUsuario,
                            $repetidora,
                            $ap,
                            $coordenada,
                            $direccion,
                            $comentario,
                            $medio,
                            $comparticion,
                            $chelectronica,
                            $anchoBanda,
                            $discapacidad,
                            $idMikrotik,
                            $ciudad

                        );
                        $updateGenerarContrato = $this->model->updateGenerarContrato(0, $idCliente);
                        if ($idIp != null) {
                            $updateIp = $this->model->updateIp($ipUsuario, $idIp);
                        }

                        if ($origenIp == 'ANULADAS') {
                            $DeleteIp = $this->model->registrarIpAnuladas('ELIMINAR', $ipUsuario, null, $idIpAnuladas);
                        }

                        $mes_facturar = $this->model->registrarMesFacturar(0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, $contrato, 1);
                        $getCliente = $this->model->getCliente($idCliente);

                        $getMikrotik = $this->model->getMikrotik($idMikrotik);
                        $arraySimpleQueues = ['nombre' => $getCliente['nombre'], 'ip_usuario' => $ipUsuario, 'velocidad' => $datos['anchoBanda'], 'mikrotik' => $getMikrotik];
                        $pendingMikrotik = ['accion' => 'agregar', 'datos' => $arraySimpleQueues];

                        //testQueueAdd();
                        if ($contrato > 0) {
                            $res = ['msg' => 'CONTRATO GENERADO EXITOSAMENTE', 'type' => 'success', 'idContrato' => $contrato];
                        } else {
                            $res = ['msg' => 'ERROR AL GENERAR EL CONTRATO', 'type' => 'error'];
                        }
                    } else {
                        foreach ($datos['productos'] as $producto) {
                            $result = $this->model->getProducto($producto['id']);
                            $data['id'] = $result['id'];
                            $data['nombre'] = $producto['nombre'];
                            $data['precio'] = $producto['precio'];
                            $data['cantidad'] = $producto['cantidad'];
                            $data['iva_producto'] = $result['iva'];
                            $data['id_categoria'] = $result['id_categoria'];
                            $data['stockActual'] = $result['cantidad'];
                            $data['idZona'] = $idZona;
                            $data['idIp'] = $idIp;
                            $subTotal = $producto['precio'] * $producto['cantidad'];
                            array_push($array['productos'], $data);
                            $total += $subTotal;
                        }

                        $getIpVieja = $this->model->getIpUsuario($id);
                        //$getIpViejaMikrotik = $this->model->getIpUsuario($id);
                        $getMikrotikViejo = $this->model->getMikrotik($getIpVieja['id_mikrotik']);
                        //	print_r( $getMikrotikViejo);exit;

                        $datosProductos = json_encode($array['productos']);
                        $contrato = $this->model->actualizarContrato(
                            $fecha,
                            $hora,
                            $idCliente,
                            $this->id_usuario,
                            $datosProductos,
                            $total,
                            $ipUsuario,
                            $repetidora,
                            $ap,
                            $coordenada,
                            $direccion,
                            $comentario,
                            $medio,
                            $comparticion,
                            $chelectronica,
                            $anchoBanda,
                            $discapacidad,
                            $idMikrotik,
                            $ciudad,
                            $id
                        );
                        $getCliente = $this->model->getCliente($idCliente);

                        $getMikrotik = $this->model->getMikrotik($idMikrotik);
                        $arraySimpleQueues = ['nombre' => $getCliente['nombre'], 'ip_usuario' => $ipUsuario, 'velocidad' => $datos['anchoBanda'], 'ipVieja' => $getIpVieja['ip_usuario'], 'mikrotik' => $getMikrotik, 'mikrotikViejo' => $getMikrotikViejo];
                        $pendingMikrotik = ['accion' => 'actualizar', 'datos' => $arraySimpleQueues];

                        if ($contrato > 0) {

                            $res = ['msg' => 'CONTRATO ACTUALIZADO EXITOSAMENTE', 'type' => 'success', 'idContrato' => $id];
                            // print_r($id);exit;
                        } else {
                            $res = ['msg' => 'ERROR AL ACTUALIZAR EL CONTRATO', 'type' => 'error'];
                        }
                    }
                }
            } else {
                $res = ['msg' => 'CARRITO VACIO', 'type' => 'warning'];
            }
        }

        // Enviar respuesta JSON al cliente PRIMERO; despues sincronizamos con Mikrotik
        if (ob_get_level()) { @ob_end_clean(); }
        header('Content-Type: application/json; charset=utf-8');
        echo json_encode($res);
        if (function_exists('fastcgi_finish_request')) {
            @fastcgi_finish_request();
        } else {
            @flush();
        }

        // Mikrotik en background (no bloquea el toast del cliente)
        if (!empty($pendingMikrotik)) {
            try {
                if ($pendingMikrotik['accion'] === 'agregar') {
                    agregarSimpleQueue($pendingMikrotik['datos']);
                } else if ($pendingMikrotik['accion'] === 'actualizar') {
                    actualizarSimpleQueue($pendingMikrotik['datos']);
                }
            } catch (\Throwable $e) {
                error_log('Mikrotik queue fallo: ' . $e->getMessage());
            }
        }
        die();
    }
    /*  public function reporte($datos)
    {

                // Ruta del archivo Word
                $archivoWord = __DIR__ . '/../assets/images/CONTRATO.docx';

                if (!file_exists($archivoWord)) {
                    die('❌ El archivo Word NO existe: ' . realpath($archivoWord));
                }

                // Cargar el documento
                $phpWord = IOFactory::load($archivoWord);

                // Guardar como HTML
                $htmlFile = 'documento.html';
                $writer = IOFactory::createWriter($phpWord, 'HTML');
                $writer->save($htmlFile);

                echo "Documento convertido a HTML correctamente";


                exit;

        ob_start();
        $array      = explode(',', $datos);
        $tipo       = $array[0];
        $idContrato = $array[1];

        $data['title']         = 'Contratos';
        $data['empresa']       = $this->model->getEmpresa();
        $data['contrato']      = $this->model->getContrato($idContrato);
        $data['serieContrato'] = $this->generate_numbers($idContrato, 1, 9);

        //print_r( $data['contrato']); exit;
        if (empty($data['contrato'])) {
            echo 'Pagina no Encontrada';
            exit;
        }
        $this->views->getView('contratos', $tipo, $data);
        $html    = ob_get_clean();
        $dompdf  = new Dompdf();
        $options = $dompdf->getOptions();
        $options->set('isJavascriptEnabled', true);
        $options->set('isRemoteEnabled', true);
        $dompdf->setOptions($options);
        $dompdf->loadHtml($html);

        if ($tipo == 'ticked') {
            $dompdf->setPaper([0, 0, 225, 500], 'portrait');
        } else {
            $dompdf->setPaper('A4', 'vertical');
        }

        // Render the HTML as PDF
        $dompdf->render();

        // Output the generated PDF to Browser
        $dompdf->stream('Contrato' . $idContrato . '.pdf', ['Attachment' => false]);
    }*/

    public function reporte($datos)
    {
        $array      = explode(',', $datos);
        $idContrato = $array[1];

        $contrato = $this->model->getContrato($idContrato);

        // 📌 Plantilla Word

        $plantilla = __DIR__ . '/../assets/docs/CONTRATO.docx';

        if (! file_exists($plantilla)) {
            die('Plantilla Word no encontrada');
        }

        // 📌 Nombre de archivo
        $nombre      = 'contrato_' . $idContrato . '_' . time() . '.docx';
        $rutaGuardar = __DIR__ . '/../assets/storage/contratos/' . $nombre;

        try {
            /* ==================================================
         * 1️⃣ GENERAR WORD
         * ================================================== */

            //DATOS DEL CLIENTE 
            $template = new TemplateProcessor($plantilla);
            $template->setValue('cliente', $contrato['nombre']);
            $template->setValue('cedula', $contrato['num_identidad']);
            $template->setValue('correo', $contrato['correo']);
            $template->setValue('telefono', $contrato['telefono']);
            $template->setValue('direccionCliente', $contrato['direccionCliente']);

            //DATOS DEL CONTRATO
            $template->setValue('direccionServicio', $contrato['direccion']);
            $template->setValue('provinvia', 'LOS RIOS');
            $template->setValue('ciudad', 'QUEVEDO');
            $template->setValue('canton', 'QUEVEDO');
            $template->setValue('parroquia', '7 DE OCTUBRE');
            $template->setValue('discapacidad', 'NO');

            $template->setValue('medio', $contrato['medio']);
            $template->setValue('anchoBanda', $contrato['ancho_banda']);
            $template->setValue('total', $contrato['total']);

            $template->setValue('comparticion', $contrato['comparticion']);
            $template->setValue('ipUsuario', $contrato['ip_usuario']);


            $template->saveAs($rutaGuardar);

            /* ==================================================
         * 2️⃣ DESCARGA AUTOMÁTICA
         * ================================================== */
            if (! file_exists($rutaGuardar)) {
                throw new Exception('Error al generar el Word');
            }

            header('Content-Description: File Transfer');
            header('Content-Type: application/vnd.openxmlformats-officedocument.wordprocessingml.document');
            header('Content-Disposition: attachment; filename="' . basename($nombre) . '"');
            header('Content-Length: ' . filesize($rutaGuardar));
            header('Pragma: public');
            header('Cache-Control: must-revalidate');

            readfile($rutaGuardar);
            exit;
        } catch (Exception $e) {
            echo 'Error: ' . $e->getMessage();
            exit;
        }
    }

    public function listar()
    {
        $estado = 1;
        $start  = isset($_POST['start']) ? intval($_POST['start']) : 0;
        $length = isset($_POST['length']) ? intval($_POST['length']) : 10;
        $search = isset($_POST['search']['value']) ? trim($_POST['search']['value']) : '';
        $draw   = isset($_POST['draw']) ? intval($_POST['draw']) : 1;

        // Obtener datos paginados desde el modelo
        $data = $this->model->getContratosPaginado($start, $length, $search);
        //print_r($data); exit;
        if (! is_array($data)) {
            $data = [];
        }

        for ($i = 0; $i < count($data); $i++) {

            if ($_SESSION['rol'] == 3) {
                $data[$i]['acciones'] = '';
            } else {
                $id = $data[$i]['id'];
                if ($data[$i]['estado'] == 1) {
                    $suspenderVisible = '<button class="action-btn action-suspend" title="Suspender servicio" onclick="suspenderContrato('.$id.')"><i class="bx bx-user-x"></i></button>';
                } else {
                    $suspenderVisible = '<button class="action-btn action-activate" title="Activar servicio" onclick="restaurarContrato('.$id.')"><i class="bx bx-check-circle"></i></button>';
                }

                $data[$i]['acciones'] = '
                <div class="action-cell">
                    <button class="action-btn action-view" title="Ver contrato" onclick="verVista('.$id.')"><i class="bx bx-show"></i></button>
                    <button class="action-btn action-edit" title="Editar" onclick="Editar('.$id.')"><i class="bx bx-pencil"></i></button>
                    <button class="action-btn action-cobro" title="Enviar cobro (SMS)" onclick="enviarMsm('.$id.')"><i class="bx bx-paper-plane"></i></button>
                    '.$suspenderVisible.'
                    <div class="dropdown">
                      <button class="action-btn action-more" type="button" data-bs-toggle="dropdown" aria-expanded="false" title="Más acciones"><i class="bx bx-dots-horizontal-rounded"></i></button>
                      <ul class="dropdown-menu dropdown-menu-end shadow-sm">
                        <li><a class="dropdown-item" href="#" onclick="updateComentario('.$id.');return false;"><i class="bx bx-message-dots text-success me-2"></i>Comentario</a></li>
                        <li><a class="dropdown-item" href="#" onclick="verReporte('.$id.');return false;"><i class="bx bxs-file-pdf text-danger me-2"></i>Ver PDF</a></li>
                        <li><hr class="dropdown-divider"></li>
                        <li><a class="dropdown-item text-danger" href="#" onclick="eliminarContrato('.$id.');return false;"><i class="bx bx-trash me-2"></i>Eliminar contrato</a></li>
                      </ul>
                    </div>
                </div>';

            }

            // Links a IPs
            $data[$i]['ipUsuario'] = '<a target="_blank" href="http://' . $data[$i]['ip_usuario'] . '" class="ip-link">' . $data[$i]['ip_usuario'] . '</a>';
            $data[$i]['Ap']        = '<a target="_blank" href="http://' . $data[$i]['ap'] . '" class="ip-link">' . $data[$i]['ap'] . '</a>';

            // Cálculo de deuda total
            $totalDeuda = 0;
            $abonado    = 0;
            $dataElectronico = $this->model->buscarPorNombreElectronico($data[$i]['nombre']);
            $dataOrdenVenta  = $this->model->buscarPorNombreOrdenVenta($data[$i]['id_cliente']);
            $datos           = array_merge($dataElectronico, $dataOrdenVenta);
            if (! empty($datos)) {
                foreach ($datos as $dato) {
                    $abono = $this->model->getAbono($dato['id']);
                    $abonado += ($abono['total'] == null) ? 0 : $abono['total'];
                    $totalDeuda += isset($dato['monto']) ? $dato['monto'] : 0;
                }
            }
            $deudaNum = $totalDeuda - $abonado;
            $deudaClass = $deudaNum > 0 ? 'deuda-pendiente' : 'deuda-ok';
            $data[$i]['deudaTotal'] = '<span class="deuda-badge ' . $deudaClass . '">$' . number_format($deudaNum, 2, '.', ',') . '</span>';
            // Abonos del mes en curso
            $abonosMesNum = isset($data[$i]['abonos_mes']) ? floatval($data[$i]['abonos_mes']) : 0;
            $abonosMesClass = $abonosMesNum > 0 ? 'deuda-ok' : 'deuda-cero';
            $data[$i]['abonosMes'] = '<span class="deuda-badge ' . $abonosMesClass . '">$' . number_format($abonosMesNum, 2, '.', ',') . '</span>';

            if ($data[$i]['factura'] == 1) {
                $data[$i]['tributario'] = '<span class="badge bg-success-subtle text-success fw-semibold">FACTURA</span>';
            } else {
                $data[$i]['tributario'] = '<span class="badge bg-warning-subtle text-warning fw-semibold">ORDEN VENTA</span>';
            }
        }

        // Totales para DataTables
        $recordsTotal    = $this->model->contarContratos(2);
        $recordsFiltered = $this->model->contarContratosFiltrado($search);

        // Respuesta final
        echo json_encode([
            'draw'            => $draw,
            'recordsTotal'    => $recordsTotal,
            'recordsFiltered' => $recordsFiltered,
            'data'            => $data,
        ], JSON_UNESCAPED_UNICODE);
        die();
    }

        public function listarContratosSuspender()
    {
        $startP  = isset($_POST['start']) ? intval($_POST['start']) : 0;
        $lengthP = isset($_POST['length']) ? intval($_POST['length']) : 25;
        $search  = isset($_POST['search']['value']) ? trim($_POST['search']['value']) : '';
        $draw    = isset($_POST['draw']) ? intval($_POST['draw']) : 1;

        $total    = $this->model->contarContratosSuspender('', CONTRATOSPORSUSPENDER);
        $filtered = $search ? $this->model->contarContratosSuspender($search, CONTRATOSPORSUSPENDER) : $total;
        $data     = $this->model->getContratosSuspenderPaginado($startP, $lengthP, $search, CONTRATOSPORSUSPENDER);

        for ($i = 0; $i < count($data); $i++) {
            if ($_SESSION['rol'] == 3) {
                $data[$i]['acciones'] = ' ';
            } else {
                $data[$i]['acciones'] = '<div>
                    <a class="btn btn-warning" href="#" title="ENVIAR MSM" onclick="enviarMsm(' . $data[$i]['id'] . ')"><i class="fas fa-paper-plane" style="color: #ffffff;"></i></a>
                    <button class="btn btn-warning" type="button" title="SUSPENDER CONTRATO" onclick="suspenderContrato(' . $data[$i]['id'] . ')"><i class="fa-solid fa-users-slash" style="color: #ffffff;"></i></button>
                </div>';
            }
            $data[$i]['deudaTotal'] = number_format($data[$i]['total'] * 1, 2);
        }

        echo json_encode([
            'draw' => $draw,
            'recordsTotal' => $total,
            'recordsFiltered' => $filtered,
            'data' => $data,
        ], JSON_UNESCAPED_UNICODE);
        die();
    }

    public function eliminar($idContrato)
    {
        if (isset($_GET) && is_numeric($idContrato)) {
            $data = $this->model->eliminar(2, $idContrato);

            $editar      = $this->model->editar($idContrato);
            $productos   = json_decode($editar['productos'], true);
            $getIp       = $this->model->getContrato($idContrato);
            $getMikrotik = $this->model->getMikrotik($getIp['id_mikrotik']);
            eliminarIpFirewall($getIp, $getMikrotik);
            //  print_r($getIp);exit;

            $data = $this->model->registrarIpAnuladas('REGISTRAR', $editar['ip_usuario'], $productos[0]['idZona'], $productos[0]['idIp']);

            if ($data > 0) {
                $res = ['msg' => 'CONTRATO ELIMINADO EXITOSAMENTE', 'type' => 'success'];
            } else {
                $res = ['msg' => 'ERROR AL ELIMINAR', 'type' => 'error'];
            }
        } else {
            $res = ['msg' => 'ERROR DESCONOCIDO', 'type' => 'error'];
        }
        echo json_encode($res, JSON_UNESCAPED_UNICODE);
        die();
    }
    public function suspender($idContrato)
    {
        if (isset($_GET) && is_numeric($idContrato)) {
            $data  = $this->model->eliminar(0, $idContrato);
            $getIp = $this->model->getContrato($idContrato);

            $getMikrotik = $this->model->getMikrotik($getIp['id_mikrotik']);

            bloquearIp($getIp, $getMikrotik);
            $dataElectronico = $this->model->buscarPorNombreElectronicoMonto($getIp['nombre']);
            $dataOrdenVenta  = $this->model->buscarPorNombreOrdenVentaMonto($getIp['id_cliente']);
            $datosValores    = array_merge($dataElectronico, $dataOrdenVenta);

            $abonado  = 0;
            $restante = 0;
            //print_r($datos['tipoPagos']); exit;

            $deudaTotal = 0;
            if (! empty($datosValores)) {

                for ($i = 0; $i < count($datosValores); $i++) {
                    $resultAbono = $this->model->getAbono($datosValores[$i]['id']);
                    $abonado += ($resultAbono['total'] == null) ? 0 : $resultAbono['total'];
                    //calcular restante  (monto - abono)
                    $deudaTotal += $datosValores[$i]['monto'];
                }
                $restante = $deudaTotal - $abonado;

                $data2['abonado']  = $abonado;
                $data2['restante'] = $restante;
                //print_r($restante ); exit;

            } else {
                $data2['abonado']  = $abonado;
                $data2['restante'] = $restante;
            }

            //  print_r($getIp);          exit;

            if ($getIp['telefono'] == '') {

                $resWathsapp = null;
            } else {
                // $resWathsapp = "https://wa.me/593{$getInfoClientes['telefono']}?text=Buen Dia estimado cliente *MEGAHNET*! Su abono es de: ' . '$' . $total . ', *Su saldo a favor es de: ' . '$' . $anticipo . '*    Su deuda Total es de:' . '$' . $restante . ', INCLUIDO SERVICIO DE *' . $mesActualLetra . '*";
                //$resWathsapp = 'https://web.whatsapp.com/send?text=Buen Dia estimado cliente *MEGAHNET*! Su deuda Total es de:' . '$' . $restante . ', INCLUIDO SERVICIO DE *' . $mesActualLetra . '*    &phone=+593' . $getInfoClientes['telefono'] . '&abid=+593' . $getInfoClientes['telefono'] . '';.$restante.'
                $resWathsapp = 'https://web.whatsapp.com/send?phone=593' . $getIp['telefono'] . '&text=Buen%20d%C3%ADa%20estimado%2Fa%20cliente%0A%20%20%20%20%20%20%20%20%20%20*MEGAHNET*%0A%20%20%20*SUSPENDIDO%20POR%20PAGO*%0A%0A%20%20su%20saldo%20a%20la%20fecha%20es%3A%0A%20%20%20%20%20%20%20%20%20%20%20%20%24*' . $restante . '* . %0A*' . $getIp['nombre'] . '*';
            }

            if ($data > 0) {
                $res = ['msg' => 'CONTRATO SUSPENDIDO EXITOSAMENTE', 'type' => 'success', 'whatsapp' => $resWathsapp];
            } else {
                $res = ['msg' => 'ERROR AL SUSPENDER', 'type' => 'error'];
            }
        } else {
            $res = ['msg' => 'ERROR DESCONOCIDO', 'type' => 'error'];
        }
        echo json_encode($res, JSON_UNESCAPED_UNICODE);
        die();
    }
    public function editar($idContrato)
    {
        $data             = $this->model->editar($idContrato);
        $data['producto'] = json_decode($data['productos']);
        $idCliente        = $data['id_cliente'];
        $cliente          = $data['nombre'];

        $datafisico      = $this->model->buscarPorNombre($idCliente);
        $dataElectronico = $this->model->buscarPorNombreElectronico($cliente);
        $dataOrdenVenta  = $this->model->buscarPorNombreOrdenVenta($idCliente);

        $datos = array_merge($datafisico, $dataElectronico, $dataOrdenVenta);
        // print_r($datos);  exit;
        $abonado            = 0;
        $restante           = 0;
        $resultMontoCredito = (empty($this->model->getCredito($idContrato))) ? 0 : $this->model->getCredito($idContrato);

        if ($resultMontoCredito == null) {
            $AbonoCredito = 0;
        } else {
            $resultAbonoCredito = (empty($this->model->getAbono($resultMontoCredito['id']))) ? 0 : $this->model->getAbono($resultMontoCredito['id']);
            $AbonoCredito       = (empty($resultAbonoCredito['total'])) ? 0 : $resultAbonoCredito['total'];
        }
        // print_r($AbonoCredito);  exit;

        $totalDeuda = 0;

        $MontoCredito = (empty($resultMontoCredito['monto'])) ? 0 : $resultMontoCredito['monto'];

        // print_r($resultAbonoCredito);  exit;

        //  $data[$i]['deudaContrato'] = $MontoCredito - $AbonoCredito;

        //print_r( $resultMontoCredito); exit;
        if (! empty($datos)) {

            for ($i = 0; $i < count($datos); $i++) {
                $resultAbono = $this->model->getAbono($datos[$i]['id']);
                $abonado += ($resultAbono['total'] == null) ? 0 : $resultAbono['total'];
                //calcular restante  (monto - abono)
                $totalDeuda += $datos[$i]['monto'];
                $restante += $datos[$i]['monto'] - $abonado;
                $data['abonado']  = $abonado;
                $data['restante'] = $restante;
                //$data['totalDeuda'] += $datos[$i]['monto'];

                //array_push($data, $result);

            }
        } else {
            $data['abonado']  = $abonado;
            $data['restante'] = $restante;
        }
        $data['deudaContrato'] = $MontoCredito - $AbonoCredito;
        //$getDeudaTotal = $this->model->getDeudaTotal($idCliente);

        $data['deudaTotal'] = $totalDeuda - $abonado;

        //print_r($data['deudaTotal']); exit;

        echo json_encode($data, JSON_UNESCAPED_UNICODE);
        die();
    }

    public function enviarMsm($idContrato)
    {
        $data             = $this->model->enviarMSM($idContrato);
        $data['producto'] = json_decode($data['productos']);
        $idCliente        = $data['id_cliente'];
        $cliente          = $data['nombre'];

        $datafisico      = $this->model->buscarPorNombre($idCliente);
        $dataElectronico = $this->model->buscarPorNombreElectronico($cliente);
        $dataOrdenVenta  = $this->model->buscarPorNombreOrdenVenta($idCliente);

        $datos = array_merge($datafisico, $dataElectronico, $dataOrdenVenta);
        // print_r($datos);  exit;
        $abonado            = 0;
        $restante           = 0;
        $resultMontoCredito = (empty($this->model->getCredito($idContrato))) ? 0 : $this->model->getCredito($idContrato);

        if ($resultMontoCredito == null) {
            $AbonoCredito = 0;
        } else {
            $resultAbonoCredito = (empty($this->model->getAbono($resultMontoCredito['id']))) ? 0 : $this->model->getAbono($resultMontoCredito['id']);
            $AbonoCredito       = (empty($resultAbonoCredito['total'])) ? 0 : $resultAbonoCredito['total'];
        }
        // print_r($AbonoCredito);  exit;

        $totalDeuda = 0;

        $MontoCredito = (empty($resultMontoCredito['monto'])) ? 0 : $resultMontoCredito['monto'];

        // print_r($resultAbonoCredito);  exit;

        //  $data[$i]['deudaContrato'] = $MontoCredito - $AbonoCredito;

        //print_r( $data); exit;

        if (! empty($datos)) {

            for ($i = 0; $i < count($datos); $i++) {
                $resultAbono = $this->model->getAbono($datos[$i]['id']);
                $abonado += ($resultAbono['total'] == null) ? 0 : $resultAbono['total'];
                //calcular restante  (monto - abono)
                $totalDeuda += $datos[$i]['monto'];
                $restante += $datos[$i]['monto'] - $abonado;
                $data['abonado']  = $abonado;
                $data['restante'] = $restante;
                //$data['totalDeuda'] += $datos[$i]['monto'];

                //array_push($data, $result);

            }
        } else {
            $data['abonado']  = $abonado;
            $data['restante'] = $restante;
        }
        $data['deudaContrato'] = $MontoCredito - $AbonoCredito;
        //$getDeudaTotal = $this->model->getDeudaTotal($idCliente);

        $data['deudaTotal'] = $totalDeuda - $abonado;

        if (empty($data['telefono'])) {
            $data['resWhatsapp'] = null;
            $data['ok']          = false;
            $data['msg']         = 'El cliente no tiene telefono registrado';
        } else {
            // Mensaje en texto plano (no URL-encoded). Asteriscos = negrita en WhatsApp.
            $mensaje =
                "SALUDOS ESTIMADO USUARIO\n          *MEGAHNET*\n\n*RECORDATORIO DE PAGO*\n\n*"
                . $data['nombre'] . "*\n  SU SALDO PENDIENTE\n\n            *$"
                . $data['deudaTotal'] . "*\n\nCUENTA CORRIENTE\nPICHINCHA 2100159721\nGUAYAQUIL 8737835\nPRODUBANCO 02120010727\nA NOMBRE DE GOBRAVCORP\n\nenviar foto del deposito\n\n*EVITE LA SUSPENSION DEL SERVICIO*\n\n*Este es un mensaje circular*\nsi ya pago, haga caso omiso\n\n          *GRACIAS*";

            // Envio via WA API (sustituye el window.open(wa.me) anterior)
            $resApi = enviarWhatsappTexto($data['telefono'], $mensaje);
            $data['ok']   = !empty($resApi['ok']);
            $data['msg']  = $resApi['msg'] ?? null;
            $data['http'] = $resApi['http'] ?? 0;
            // Mantener URL como fallback por si el JS quiere ofrecer abrir WhatsApp Web manualmente
            $data['resWhatsapp'] = 'https://web.whatsapp.com/send?phone=' . ($resApi['tel'] ?? ('593' . $data['telefono']))
                . '&text=' . rawurlencode($mensaje);
        }

        echo json_encode($data, JSON_UNESCAPED_UNICODE);
        die();
    }

    public function buscar()
    {
        // print_r($data); exit;
        foreach ($data as $row) {
            $resultAbono = $this->model->getAbono($row['id']);
            $abonado     = ($resultAbono['total'] == null) ? 0 : $resultAbono['total'];
            //calcular restante  (monto - abono)
            $restante            = $row['monto'] - $abonado;
            $result['monto']     = $row['monto'];
            $result['abonado']   = $abonado;
            $result['restante']  = $restante;
            $result['fecha']     = $row['fecha'];
            $result['id']        = $row['id'];
            $result['label']     = $row['nombre'] . ' ' . $row['factura'];
            $result['telefono']  = $row['telefono'];
            $result['direccion'] = $row['direccion'];
            array_push($array, $result);
        }
        echo json_encode($array, JSON_UNESCAPED_UNICODE);
        die();
    }

    public function buscarip($idZona)
    {

        $array = [];

        $ipnueva    = $this->model->ipNueva(null, $idZona);
        $ipanuladas = $this->model->ipNuevaAnuladas($idZona);

        if ($ipanuladas > 0) {
            $data['ip']     = ($ipanuladas == '' || $ipanuladas == null) ? 0 : $ipanuladas;
            $data['origen'] = 'ANULADAS';
        } else {
            $data['ip']     = $ipnueva;
            $data['origen'] = 'NUEVA';

            // Calcula la siguiente IP usando aritmetica de 32 bits.
            // Salta el gateway para que nunca se asigne a un cliente.
            $ultimaLong  = ip2long($data['ip']['ultima']);
            $finalLong   = ip2long($data['ip']['final']);
            $gatewayLong = isset($data['ip']['gateway']) && $data['ip']['gateway']
                            ? ip2long($data['ip']['gateway'])
                            : null;

            $next = $ultimaLong + 1;
            if ($gatewayLong !== null && $next === $gatewayLong) {
                $next++;
            }
            if ($finalLong !== false && $next > $finalLong) {
                $data['ipUsuario'] = '';   // sin IP disponible en el rango
            } else {
                $data['ipUsuario'] = long2ip($next);
            }
        }
        /* if($data['ipNuevaAnuladas'] > 0 ){
         //   print_r($data['ipNuevaAnuladas']); 


        }else{
            echo 'NO HAY IP ANULADAS';
        }*/
        array_push($array, $data);

        // print_r($array); exit;
        echo json_encode($array, JSON_UNESCAPED_UNICODE);
        die();
    }
    public function buscarRepetidora()
    {

        $repetidora = $_GET['valor'];

        $data = $this->model->getRepetidora(1, $repetidora);

        // print_r($data); exit;
        echo json_encode($data, JSON_UNESCAPED_UNICODE);
        die();
    }
    public function inactivos()
    {
        $data['title']  = 'Contratos  Inactivos';
        $data['script'] = 'contratos-inactivos.js';
        $this->views->getView('contratos', 'inactivos', $data);
    }
    public function zonasPorMikrotik($idMikrotik = 0)
    {
        header('Content-Type: application/json');
        $idMikrotik = (int)$idMikrotik;
        if ($idMikrotik <= 0) { echo json_encode([]); exit; }
        echo json_encode($this->model->zonasPorMikrotik($idMikrotik), JSON_UNESCAPED_UNICODE);
        exit;
    }
    public function repetidorasPorMikrotik($idMikrotik = 0)
    {
        header('Content-Type: application/json');
        $idMikrotik = (int)$idMikrotik;
        if ($idMikrotik <= 0) { echo json_encode([]); exit; }
        echo json_encode($this->model->repetidorasPorMikrotik($idMikrotik), JSON_UNESCAPED_UNICODE);
        exit;
    }

    public function listarInactivos()
    {
        $data = $this->model->getContratos(0);
        //print_r($data); exit;
        for ($i = 0; $i < count($data); $i++) {
            $data[$i]['acciones'] = '<div>
            <button class="btn btn-success" type="button" onclick="restaurarContrato(' . $data[$i]['id'] . ')"><i class="fas fa-check-circle"></i></button>
            </div> ';

            /* $productos = json_decode($data[$i]['productos'], true);
           $data[$i]['servicio'] = '<span style="justify-content: center;
             display: flex;
             margin: auto;" class="badge bg-success">' . $productos[0]['nombre'] . '</span>';*/

            if ($data[$i]['factura'] == 1) {
                $data[$i]['tributario'] = '<span style="justify-content: center;
                display: flex;
                margin: auto;" class="badge bg-success">FACTURA</span>';
            } else {
                $data[$i]['tributario'] = '<span style="justify-content: center;
                display: flex;
                margin: auto;" class="badge bg-warning">ORDEN VENTA</span>';
            }
        }
        echo json_encode($data, JSON_UNESCAPED_UNICODE);
        die();
    }
    public function restaurar($idContrato)
    {
        if (isset($_GET) && is_numeric($idContrato)) {

            $data  = $this->model->eliminar(1, $idContrato);
            $getIp = $this->model->getContrato($idContrato);

            $getMikrotik = $this->model->getMikrotik($getIp['id_mikrotik']);

            habilitarIp($getIp, $getMikrotik);

            if ($getIp['telefono'] == '') {

                $resWathsapp = null;
            } else {
                // $resWathsapp = "https://wa.me/593{$getInfoClientes['telefono']}?text=Buen Dia estimado cliente *MEGAHNET*! Su abono es de: ' . '$' . $total . ', *Su saldo a favor es de: ' . '$' . $anticipo . '*    Su deuda Total es de:' . '$' . $restante . ', INCLUIDO SERVICIO DE *' . $mesActualLetra . '*";
                //$resWathsapp = 'https://web.whatsapp.com/send?text=Buen Dia estimado cliente *MEGAHNET*! Su deuda Total es de:' . '$' . $restante . ', INCLUIDO SERVICIO DE *' . $mesActualLetra . '*    &phone=+593' . $getInfoClientes['telefono'] . '&abid=+593' . $getInfoClientes['telefono'] . '';.$restante.'
                $resWathsapp = 'https://web.whatsapp.com/send?phone=593' . $getIp['telefono'] . '&text=Buen%20d%C3%ADa%20estimado%2Fa%20cliente%0A%20%20%20%20%20%20%20%20%20%20*MEGAHNET*%0A%20%20%20*SERVICIO%20ACTIVADO*%0A%0A%20%20%0A*' . $getIp['nombre'] . '*';
            }

            if ($data > 0) {
                $res = ['msg' => 'CONTRATO RESTAURADO EXITOSAMENTE', 'type' => 'success', 'whatsapp' => $resWathsapp];
            } else {
                $res = ['msg' => 'ERROR AL RESTAURAR', 'type' => 'error'];
            }
        } else {
            $res = ['msg' => 'ERROR DESCONOCIDO', 'type' => 'error'];
        }
        echo json_encode($res, JSON_UNESCAPED_UNICODE);
        die();
    }

    public function updateComentario()
    {

        $comentario = $_POST['comentario'];
        $idContrato = $_POST['idContrato'];

        $data = $this->model->updateComentario($idContrato, $comentario);

        if ($data > 0) {
            $res = ['msg' => 'COMENTARIO ACTUALIZADO EXITOSAMENTE', 'type' => 'success'];
        } else {
            $res = ['msg' => 'ERROR AL ACTUALIZAR EL COMENTARIO', 'type' => 'error'];
        }

        echo json_encode($res, JSON_UNESCAPED_UNICODE);
        die();
    }

    public function buscarContrato()
    {
        $array = [];
        $valor = strClean($_GET['term']);
        $data  = $this->model->buscarPorNombreContrato($valor);

        // $data = array_merge($datafisico, $dataElectronico,$dataOrdenVenta);
        // print_r($data); exit;
        foreach ($data as $row) {
            //calcular restante  (monto - abono)
            $result['id']    = $row['id'];
            $result['label'] = $row['nombre'] . ' ' . $row['id'];

            $result['enero']      = $row['enero'];
            $result['febrero']    = $row['febrero'];
            $result['marzo']      = $row['marzo'];
            $result['abril']      = $row['abril'];
            $result['mayo']       = $row['mayo'];
            $result['junio']      = $row['junio'];
            $result['julio']      = $row['julio'];
            $result['agosto']     = $row['agosto'];
            $result['septiembre'] = $row['septiembre'];
            $result['octubre']    = $row['octubre'];
            $result['noviembre']  = $row['noviembre'];
            $result['diciembre']  = $row['diciembre'];
            $result['total']      = $row['total'];

            $result['direccionContrato'] = $row['direccion'];
            array_push($array, $result);
        }
        echo json_encode($array, JSON_UNESCAPED_UNICODE);
        die();
    }
    public function generate_numbers($start, $count, $digits)
    {
        $result = [];
        for ($n = $start; $n < $start + $count; $n++) {
            $result[] = str_pad($n, $digits, '0', STR_PAD_LEFT);
        }
        return $result;
    }
    public function reporteExcel()
    {
        $spreadsheet = new Spreadsheet();

        $spreadsheet->getProperties()
            ->setCreator($_SESSION['nombre_usuario'])
            ->setTitle("Listado de Productos");

        $spreadsheet->setActiveSheetIndex(0);

        $hojaActiva = $spreadsheet->getActiveSheet();
        $hojaActiva->getColumnDimension('A')->setWidth(50);
        $hojaActiva->getColumnDimension('B')->setWidth(25);
        $hojaActiva->getColumnDimension('C')->setWidth(30);
        $hojaActiva->getColumnDimension('D')->setWidth(20);
        $hojaActiva->getColumnDimension('E')->setWidth(20);
        $hojaActiva->getColumnDimension('F')->setWidth(15);
        $hojaActiva->getColumnDimension('G')->setWidth(15);
        $hojaActiva->getColumnDimension('H')->setWidth(15);
        $hojaActiva->getColumnDimension('I')->setWidth(35);

        $spreadsheet->getActiveSheet()->getStyle('A1:I1')->getFill()
            ->setFillType(Fill::FILL_SOLID)
            ->getStartColor()->setARGB('008cff');

        $spreadsheet->getActiveSheet()->getStyle('A1:I1')
            ->getFont()->getColor()->setARGB(Color::COLOR_WHITE);

        $hojaActiva->getStyle('1')->getAlignment()->setWrapText(true);
        $hojaActiva->getStyle('A1:I1')->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);
        $hojaActiva->getStyle('A1:I1')->getAlignment()->setVertical(\PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_CENTER);

        $hojaActiva->setCellValue('A1', 'NOMBRE CLIENTE');
        $hojaActiva->setCellValue('B1', 'CIUDAD');
        $hojaActiva->setCellValue('C1', 'DIRECCION CLIENTE');
        $hojaActiva->setCellValue('D1', 'MEDIO TX/RX');
        $hojaActiva->setCellValue('E1', 'TELEFONOS CLIENTE');
        $hojaActiva->setCellValue('F1', 'ANCHO DE BANDA');
        $hojaActiva->setCellValue('G1', 'COMPRESION');
        $hojaActiva->setCellValue('H1', 'TARIFA');
        $hojaActiva->setCellValue('I1', 'NOMBRE DEL PLAN');

        $fila = 2;
        $contratos = $this->model->getContratosExcel(1, 1);
        foreach ($contratos as $contrato) {

            $nombrePlan = json_decode($contrato['productos'], true);
            // $preciosiniva = number_format($nombrePlan[0]['precio'] / (1 + (CONCAT . $empresa['impuesto'])/100),2);

            $precioConIVA = round((float)($nombrePlan[0]['precio'] ?? 0), 2); // Ejemplo: 1234.56

            // Porcentaje de IVA
            $ivaPorcentaje = 15;

            // Calcular el precio sin IVA
            $precioSinIVA = $precioConIVA / (1 + ($ivaPorcentaje / 100));

            // Asignar el precio sin IVA a la celda H$Fila
            // $hojaActiva->setCellValue('H' . $fila, round($precioSinIVA, 2));

            $hojaActiva->setCellValue('A' . $fila, $contrato['nombre']);
            $hojaActiva->setCellValue('B' . $fila, $contrato['ciudad']);
            $hojaActiva->setCellValue('C' . $fila, $contrato['direccionCliente']);
            $hojaActiva->setCellValue('D' . $fila, $contrato['medio']);
            $hojaActiva->setCellValue('E' . $fila, $contrato['telefonoCliente']);
            $hojaActiva->setCellValue('F' . $fila, '70M');
            $hojaActiva->setCellValue('G' . $fila, $contrato['comparticion']);
            // $hojaActiva->setCellValue('G' . $fila, round($precioSinIVA,2));
            $hojaActiva->setCellValue('H' . $fila, $precioSinIVA);

            $hojaActiva->setCellValue('I' . $fila, $nombrePlan[0]['nombre']);

            $fila++;
        }

        //Generar archivo Excel
        header('Content-Type: application/vnd.ms-excel');
        header('Content-Disposition: attachment;filename="Reporte Abonados Gobravcorp.xlsx"');
        $writer = IOFactory::createWriter($spreadsheet, 'Xlsx');
        $writer->save('php://output');
    }
}
function importarExcel($archivoExcel)
{
    $fecha     = date('Y-m-d');
    $hora      = date('H:i:s');
    $contratos = new ContratosModel;

    $documento = IOFactory::load($archivoExcel);

    $HojaExcel       = $documento->getSheet(0);
    $FilaDeHojaExcel = $HojaExcel->getHighestDataRow();
    for ($fila = 2; $fila <= $FilaDeHojaExcel; $fila++) {
        $idClientes     = $HojaExcel->getCellByColumnAndRow(1, $fila);
        $id_usuarios    = $HojaExcel->getCellByColumnAndRow(2, $fila);
        $datosProductos = $HojaExcel->getCellByColumnAndRow(3, $fila);
        $total          = $HojaExcel->getCellByColumnAndRow(4, $fila);
        $ipUsuario      = $HojaExcel->getCellByColumnAndRow(5, $fila);
        $repetidora     = $HojaExcel->getCellByColumnAndRow(6, $fila);

        $ap             = $HojaExcel->getCellByColumnAndRow(7, $fila);
        $coordenada     = $HojaExcel->getCellByColumnAndRow(8, $fila);
        $direccion      = $HojaExcel->getCellByColumnAndRow(9, $fila);
        $comentario     = $HojaExcel->getCellByColumnAndRow(10, $fila);
        $medio          = $HojaExcel->getCellByColumnAndRow(11, $fila);
        $comparticion   = $HojaExcel->getCellByColumnAndRow(12, $fila);
        $idIp           = $HojaExcel->getCellByColumnAndRow(13, $fila);
        $chelectronica  = $HojaExcel->getCellByColumnAndRow(14, $fila);
        $tipoBanco      = $HojaExcel->getCellByColumnAndRow(15, $fila);
        $cuentaBancaria = $HojaExcel->getCellByColumnAndRow(16, $fila);

        //  echo $idClientes.' '.$datosProductos.' '.$ipUsuario. "<br>"; exit;

        $contrato = $contratos->registrarContrato(
            $fecha,
            $hora,
            $idClientes,
            $id_usuarios,
            $datosProductos,
            $total,
            $ipUsuario,
            $repetidora,
            $ap,
            $coordenada,
            $direccion,
            $comentario,
            $medio,
            $comparticion,
            $tipoBanco,
            $cuentaBancaria,
            $chelectronica
        );
        $updateGenerarContrato = $contratos->updateGenerarContrato(0, $idClientes);
        $updateIp              = $contratos->updateIp($ipUsuario, $idIp);

        $mes_facturar = $contratos->registrarMesFacturar(0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, $contrato, 1);

        //echo $contrato; exit;
        //  echo $idClientes.' '.$datosProductos.' '.$ipUsuario. "<br>"; exit;

        if ($contrato > 0) {
            $res = ['msg' => 'CONTRATO GENERADO EXITOSAMENTE', 'type' => 'success'];
        } else {
            $res = ['msg' => 'ERROR AL GENERAR EL CONTRATO', 'type' => 'error'];
        }
        // echo json_encode($res, JSON_UNESCAPED_UNICODE);

        // echo $tipo_documento.' '.$cedula.' '.$nombre. "<br>";
    }
    echo json_encode($res, JSON_UNESCAPED_UNICODE);
}

function bloquearIp($data, $getMikrotik)
{
    //print_r($_SESSION);
    require 'libraries/mikrotik/routeros_api.class.php';

    //print_r($data); exit;
    $targetIp = $data['ip_usuario'];
    $cliente  = $data['nombre'];

    if (! $targetIp) {
        $res = ['msg' => 'FALTA LA IP DEL OBJETIVO', 'type' => 'error'];
        echo json_encode($res, JSON_UNESCAPED_UNICODE);

        //echo json_encode(['error' => 'Falta la IP objetivo']);
        exit;
        
    }

    $API       = new RouterosAPI();
    $API->port = $getMikrotik['puerto'] ?? 8728;

    if ($API->connect($getMikrotik['ip'], $getMikrotik['usuario'], $getMikrotik['clave'])) {

        $API->comm("/ip/firewall/address-list/add", [
            "list"    => "XXXPAGO",
            "address" => $targetIp,
            "comment" => $cliente,
        ]);
        // $res = ['msg' => 'IP BLOQUEADA', 'type' => 'success'];

    } else {
        $res = ['msg' => 'FALLO AL BLOQUEAR IP', 'type' => 'error'];
        echo json_encode($res, JSON_UNESCAPED_UNICODE);
        exit;
    }
}

function habilitarIp($data, $getMikrotik)
{

    require 'libraries/mikrotik/routeros_api.class.php';

    $targetIp = $data['ip_usuario'] ?? null;
    //$cliente  = $data['nombre'] ?? 'CLIENTE';

    if (! $targetIp) {
        $res = ['msg' => 'FALTA LA IP DEL OBJETIVO', 'type' => 'error'];
        echo json_encode($res, JSON_UNESCAPED_UNICODE);
        exit;
    }

    $API       = new RouterosAPI();
    $API->port = $getMikrotik['puerto'] ?? 8728;

    if ($API->connect($getMikrotik['ip'], $getMikrotik['usuario'], $getMikrotik['clave'])) {
        // Buscar si la IP está en la address list
        $list = $API->comm("/ip/firewall/address-list/print", [
            "?address" => $targetIp,
            "?list"    => "XXXPAGO",
        ]);

        if (! empty($list)) {

            $idAddress = $list[0][".id"];
            $API->comm("/ip/firewall/address-list/remove", [
                ".id" => $idAddress,
            ]);
        } else {

            $res = ['msg' => 'NO ESTA BLOQUEADO', 'type' => 'warning'];
            echo json_encode($res, JSON_UNESCAPED_UNICODE);
            exit;
        }

        $API->disconnect();
    } else {

        exit;
    }
}

function agregarSimpleQueue1($data)
{
    require 'libraries/mikrotik/routeros_api.class.php';

    $targetIp      = $data['ip_usuario'];
    $cliente       = $data['nombre'];
    $uploadLimit   = $data['velocidad']; // Ej: 512k, 1M, etc.
    $downloadLimit = $data['velocidad']; // Ej: 2M, 5M, etc.

    if (! $targetIp || ! $cliente || ! $uploadLimit) {
        echo json_encode(['msg' => 'FALTAN DATOS', 'type' => 'error'], JSON_UNESCAPED_UNICODE);
        exit;
    }

    if (! isset($_SESSION['mikrotik']) || ! is_array($_SESSION['mikrotik']) || empty($_SESSION['mikrotik'])) {
        echo json_encode(['msg' => 'NO HAY MIKROTIKS CONECTADOS', 'type' => 'warning'], JSON_UNESCAPED_UNICODE);
        exit;
    }

    foreach ($_SESSION['mikrotik'] as $routerId => $router) {
        $API       = new RouterosAPI();
        $API->port = $router['puerto'] ?? 8728;

        if ($API->connect($router['ip'], $router['user'], $router['pass'])) {
            $API->comm("/queue/simple/add", [
                "name"      => $cliente,
                "target"    => $targetIp,
                "max-limit" => "{$uploadLimit}/{$downloadLimit}", // Ej: 1M/5M
                "comment" => $cliente . "_" . $targetIp,
            ]);
        } else {
            echo json_encode(['msg' => 'FALLO AL CONECTAR A MIKROTIK', 'type' => 'error'], JSON_UNESCAPED_UNICODE);
            exit;
        }
    }
}

function agregarSimpleQueue($data)
{
    require 'libraries/mikrotik/routeros_api.class.php';

    $targetIp      = $data['ip_usuario'];
    $cliente       = $data['nombre'];
    $uploadLimit   = $data['velocidad'] . 'M'; // Ej: 512k, 1M
    $downloadLimit = $data['velocidad'] . 'M'; // Ej: 2M, 5M
    $priority      = '5/5';                    // Ej: 2M, 5M
    $uLimitaT      = ($data['velocidad'] / 2); // Ej: 512k, 1M
    $dLimitaT      = ($data['velocidad'] / 2); // Ej: 2M, 5M

    $arrayMikrotik   = $data['mikrotik'];
    $ipMikrotik      = $arrayMikrotik['ip'];
    $usuarioMikrotik = $arrayMikrotik['usuario'];
    $claveMikrotik   = $arrayMikrotik['clave'];

    // print_r($arrayMikrotik);exit;

    $uploadLimitaT   = $uLimitaT . 'M'; // Ej: 512k, 1M
    $downloadLimitaT = $dLimitaT . 'M'; // Ej: 2M, 5M

    //print_r($uLimitaT); exit;
    if (! $targetIp || ! $cliente || ! $uploadLimit || ! $downloadLimit) {
        echo json_encode(['msg' => 'FALTAN DATOS', 'type' => 'error'], JSON_UNESCAPED_UNICODE);
        exit;
    }

    // Asegurar que el target tenga formato correcto (con /32)
    if (strpos($targetIp, '/') === false) {
        $targetIp .= '/32';
    }

    $API       = new RouterosAPI();
    $API->port = $arrayMikrotik['puerto'] ?? 8728;

    if ($API->connect($ipMikrotik, $usuarioMikrotik, $claveMikrotik)) {

        // Verificamos si ya existe una queue con ese nombre o IP
        $existingQueue = $API->comm("/queue/simple/print", [
            "?target" => $targetIp,
        ]);

        if (! empty($existingQueue)) {

            echo json_encode(['msg' => 'YA EXISTE LA IP REGISTRADA EN QUEUES CON EL CLIENTE ' . $cliente, 'type' => 'warning'], JSON_UNESCAPED_UNICODE);
            exit;
            /*  $resultados[] = [
                    'routerId' => $routerId,
                    'ip' => $router['ip'],
                ];*/
            //return;
            //continue;
        }
        $response = $API->comm("/queue/simple/add", [
            "name"      => $cliente . $targetIp,
            "target"    => $targetIp,
            "max-limit" => "{$uploadLimit}/{$downloadLimit}",
            "limit-at" => "{$uploadLimitaT}/{$downloadLimitaT}",
            "priority" => $priority,

        ]);

        if ($response) {
            $API->comm("/ip/firewall/address-list/add", [
                "list"    => "NAVEGABLE",
                "address" => $targetIp,
                "comment" => $cliente,
            ]);
        }

        if (isset($response['!trap'])) {

            echo json_encode(['msg' => $response['!trap'][0]['message'] ?? 'Error desconocido', 'type' => 'ERROR'], JSON_UNESCAPED_UNICODE);
            exit;
        } else {
            // Agregado exitosamente
            /*$resultados[] = [
                    'routerId' => $routerId,
                    'ip' => $router['ip'],
                    'status' => 'AGREGADO'
                ];
                echo json_encode(['msg' => '', 'type' => 'ERROR'], JSON_UNESCAPED_UNICODE);
                exit;*/
        }
    } else {

        echo json_encode(['msg' => 'FALLO DE LA CONEXIÓN CON LA IP DEL MIKROTIK ' . $ipMikrotik, 'type' => 'ERROR'], JSON_UNESCAPED_UNICODE);
        exit;
    }
}

function actualizarSimpleQueue($data)
{

    $targetIp = $data['ip_usuario'];
    $cliente  = $data['nombre'];
    $ipVieja  = $data['ipVieja'];

    $uploadLimit   = $data['velocidad'] . 'M'; // Ej: 512k, 1M, etc.
    $downloadLimit = $data['velocidad'] . 'M'; // Ej: 2M, 5M, etc.
    $priority      = 5;                        // Ej: 2M, 5
    $uLimitaT      = $data['velocidad'] / 2;   // Ej: 512k, 1M
    $dLimitaT      = $data['velocidad'] / 2;   // Ej: 2M, 5M

    $arrayMikrotik   = $data['mikrotik'];
    $ipMikrotik      = $arrayMikrotik['ip'];
    $usuarioMikrotik = $arrayMikrotik['usuario'];
    $claveMikrotik   = $arrayMikrotik['clave'];

    $mikrotikViejo        = $data['mikrotikViejo'];
    $ipMikrotikViejo      = $mikrotikViejo['ip'];
    $usuarioMikrotikViejo = $mikrotikViejo['usuario'];
    $claveMikrotikViejo   = $mikrotikViejo['clave'];

    $uploadLimitaT   = $uLimitaT . 'M'; // Ej: 512k, 1M
    $downloadLimitaT = $dLimitaT . 'M'; // Ej: 2M, 5M

    require 'libraries/mikrotik/routeros_api.class.php';
    $API       = new RouterosAPI();
    $API->port = $arrayMikrotik['puerto'] ?? 8728;
    if (! $targetIp || ! $cliente || ! $uploadLimit || ! $downloadLimit) {
        echo json_encode(['msg' => 'FALTAN DATOS', 'type' => 'error'], JSON_UNESCAPED_UNICODE);
        exit;
    }

    if ($ipMikrotik == $ipMikrotikViejo) {

        //  print_r('verdadero'); exit;
        if ($API->connect($ipMikrotik, $usuarioMikrotik, $claveMikrotik)) {

            // Asegurar que el target tenga formato correcto (con /32)
            if (strpos($ipVieja, '/') === false) {
                $ipVieja .= '/32';
            }
            $queues = $API->comm("/queue/simple/print", [
                "?target" => $ipVieja,
            ]);

            $listN = $API->comm("/ip/firewall/address-list/print", [
                "?address" => $data['ipVieja'],
                "?list"    => "NAVEGABLE",
            ]);

            if (empty($listN)) {
                $API->comm("/ip/firewall/address-list/add", [
                    "list"    => "NAVEGABLE",
                    "address" => $targetIp,
                    "comment" => $cliente,
                ]);
            } else {
                $listNId = $listN[0]['.id'];

                // Ahora puedes editarla, por ejemplo cambiando el list o el comentario
                $API->comm("/ip/firewall/address-list/set", [
                    ".id"     => $listNId,
                    "address" => $targetIp,
                    "comment" => $cliente,
                ]);
            }

            if (! empty($queues)) {
                // Si encuentra, actualiza
                $queueId = $queues[0]['.id'];

                $API->comm("/queue/simple/set", [
                    ".id"       => $queueId,
                    "target"    => $targetIp,
                    "max-limit" => "{$uploadLimit}/{$downloadLimit}",
                    "limit-at" => "{$uploadLimitaT}/{$downloadLimitaT}",
                    "priority" => $priority,

                ]);
            } else {

                // Asegurar que el target tenga formato correcto (con /32)
                if (strpos($targetIp, '/') === false) {
                    $targetIp .= '/32';
                }

                $API->comm("/queue/simple/add", [
                    "name"      => $cliente . $targetIp,
                    "target"    => $targetIp,
                    "max-limit" => "{$uploadLimit}/{$downloadLimit}",

                ]);
            }
        } else {
            echo json_encode(['msg' => 'FALLO DE LA CONEXIÓN CON LA IP DEL MIKROTIK ' . $ipMikrotik, 'type' => 'error'], JSON_UNESCAPED_UNICODE);
            exit;
        }
    } else {
        //print_r('falso'); exit;
        if ($API->connect($ipMikrotik, $usuarioMikrotik, $claveMikrotik)) {

            // Asegurar que el target tenga formato correcto (con /32)
            if (strpos($ipVieja, '/') === false) {
                $ipVieja .= '/32';
            }

            // Verificamos si ya existe una queue con ese nombre o IP
            /*  $existingQueue = $API->comm("/queue/simple/print", [
                "?target" => $targetIp.'/32'
            ]);

            if (!empty($existingQueue)) {

                echo json_encode(['msg' => 'YA EXISTE LA IP REGISTRADA EN QUEUES CON EL CLIENTE ' . $cliente, 'type' => 'warning'], JSON_UNESCAPED_UNICODE);
                exit;
            }*/

            $response = $API->comm("/queue/simple/add", [
                "name"      => $cliente . $targetIp,
                "target"    => $targetIp . '/32',
                "max-limit" => "{$uploadLimit}/{$downloadLimit}",
                "limit-at" => "{$uploadLimitaT}/{$downloadLimitaT}",
                "priority" => $priority,

            ]);

            if ($response) {
                $API->comm("/ip/firewall/address-list/add", [
                    "list"    => "NAVEGABLE",
                    "address" => $targetIp,
                    "comment" => $cliente,
                ]);
            }
            if (isset($response['!trap'])) {

                echo json_encode(['msg' => $response['!trap'][0]['message'] ?? 'Error desconocido', 'type' => 'ERROR'], JSON_UNESCAPED_UNICODE);
                exit;
            } else {
            }

            $API->disconnect();
        } else {
            $API->disconnect();
            echo json_encode(['msg' => 'FALLO DE LA CONEXIÓN CON LA IP DEL MIKROTIK ' . $ipMikrotik, 'type' => 'error'], JSON_UNESCAPED_UNICODE);
            exit;
        }

        $API->port = $arrayMikrotik['puerto'] ?? 8728;
        if ($API->connect($ipMikrotikViejo, $usuarioMikrotikViejo, $claveMikrotikViejo)) {

            // Buscar si la IP está en la address list
            $list = $API->comm("/ip/firewall/address-list/print", [
                "?address" => $data['ipVieja'],
                "?list"    => "NAVEGABLE",
            ]);

            // Verificamos todas las queues
            //$allQueues = $API->comm("/queue/simple/print");

            // Imprimir el resultado para ver la estructura de la respuesta
            //print_r($allQueues); // Ver qué datos recibes

            // Verificamos si ya existe una queue con ese nombre o IP
            $existingQueue = $API->comm("/queue/simple/print", [
                "?target" => $ipVieja,
            ]);

            //print_r($existingQueue); 

            if (! empty($list)) {
                $idAddress = $list[0][".id"];
                $API->comm("/ip/firewall/address-list/remove", [
                    ".id" => $idAddress,
                ]);
            } else {

                $res = ['msg' => 'NO ESTA BLOQUEADO EN NAVEGABLE', 'type' => 'warning'];
                echo json_encode($res, JSON_UNESCAPED_UNICODE);
                exit;
            }

            if (! empty($existingQueue)) {

                $idAddressQ = $existingQueue[0][".id"];
                $API->comm("/queue/simple/remove", [
                    ".id" => $idAddressQ,
                ]);
            } else {

                $res = ['msg' => 'NO ESTA BLOQUEADO EN QUEUES', 'type' => 'warning'];
                echo json_encode($res, JSON_UNESCAPED_UNICODE);
                exit;
            }
        }
    }

    // echo json_encode(['msg' => 'RESULTADO DE LA ACTUALIZACIÓN', 'type' => 'success', 'resultados' => $resultados], JSON_UNESCAPED_UNICODE);
}

function eliminarIpFirewall($data, $getMikrotik)
{

    require 'libraries/mikrotik/routeros_api.class.php';

    $targetIp = $data['ip_usuario'] ?? null;
    //$cliente  = $data['nombre'] ?? 'CLIENTE';

    if (! $targetIp) {
        $res = ['msg' => 'FALTA LA IP DEL OBJETIVO', 'type' => 'error'];
        echo json_encode($res, JSON_UNESCAPED_UNICODE);
        exit;
    }

    // Asegurar que el target tenga formato correcto (con /32)
    if (strpos($targetIp, '/') === false) {
        $targetIp .= '/32';
    }

    $API       = new RouterosAPI();
    $API->port = $getMikrotik['puerto'] ?? 8728;

    if ($API->connect($getMikrotik['ip'], $getMikrotik['usuario'], $getMikrotik['clave'])) {
        // Buscar si la IP está en la address list
        $list = $API->comm("/ip/firewall/address-list/print", [
            "?address" => $data['ip_usuario'],
            "?list"    => "NAVEGABLE",
        ]);

        // Verificamos si ya existe una queue con ese nombre o IP
        $existingQueue = $API->comm("/queue/simple/print", [
            "?target" => $targetIp,
        ]);

        if (! empty($list)) {
            $idAddress = $list[0][".id"];
            $API->comm("/ip/firewall/address-list/remove", [
                ".id" => $idAddress,
            ]);
        } else {

            $res = ['msg' => 'NO ESTA BLOQUEADO EN NAVEGABLE', 'type' => 'warning'];
            echo json_encode($res, JSON_UNESCAPED_UNICODE);
            exit;
        }

        if (! empty($existingQueue)) {

            $idAddressQ = $existingQueue[0][".id"];
            $API->comm("/queue/simple/remove", [
                ".id" => $idAddressQ,
            ]);
        } else {

            $res = ['msg' => 'NO ESTA BLOQUEADO EN QUEUES', 'type' => 'warning'];
            echo json_encode($res, JSON_UNESCAPED_UNICODE);
            exit;
        }

        if (! empty($list)) {

            $idAddress = $list[0][".id"];
            $API->comm("/ip/firewall/address-list/remove", [
                ".id" => $idAddress,
            ]);
        } else {

            $res = ['msg' => 'NO ESTA BLOQUEADO', 'type' => 'warning'];
            echo json_encode($res, JSON_UNESCAPED_UNICODE);
            exit;
        }

        $API->disconnect();
    } else {

        exit;
    }
}

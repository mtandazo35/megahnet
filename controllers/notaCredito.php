<?php
require 'vendor/autoload.php';
use Mike42\Escpos\Printer;
use Mike42\Escpos\EscposImage;

use Dompdf\Dompdf;

class notaCredito extends Controller
{
    private function cargarSri() { static $loaded=false; if (!$loaded) { include_once __DIR__ . '/../config/ServicesSri.php'; $loaded=true; } }

    private $id_usuario;
    public function __construct()
    {
        session_start();

        //exit;
        parent::__construct();
        if (empty($_SESSION['id_usuario'])) {
            header('Location: ' . BASE_URL);
            exit;
        }
        $this->id_usuario = $_SESSION['id_usuario'];

    }
    public function index()
    {
        $data['title'] = 'Nota Credito';
        $data['script'] = 'notaCredito.js';
        $data['busqueda'] = 'busqueda.js';
        $data['carrito'] = 'posNotaCredito';
        $data['empresa'] = $this->model->getEmpresa();
        $resultSerieElectronica = $this->model->getSerieElectronica();
        $serieElectronica = ($resultSerieElectronica['total'] == null) ? 1 : $resultSerieElectronica['total'] + 1; // factura electronica
        $data['serieelectronica'] = $this->generate_numbers($serieElectronica, 1, 9);
        $this->views->getView('notaCredito', 'index', $data);
    }

    public function registrarNotaCredito()
    {

        $this->cargarSri();
        $json = file_get_contents('php://input');
        $datos = json_decode($json, true);
        $array['productos'] = array();
        $empresa = $this->model->getEmpresa();
        $total = 0;

        // print_r($datos);
        if (!empty($datos['productos'])) {
            $fecha = date('Y-m-d');
            $hora = date('H:i:s');
            $idCliente = $datos['idCliente'];
            $no_factura = $datos['no_factura'];
            $fechaFactura = $datos['fechaFactura'];
            $serieFactura = $datos['serieFactura'];
            $claveAccessoFactura = $datos['claveAccesoFactura'];
            $motivoNotaCredito = $datos['motivoNotaCredito'];

            $resultSerieElectronica = $this->model->getSerieElectronica();
            $numSerieElectronica = ($resultSerieElectronica['total'] == null) ? 1 : $resultSerieElectronica['total'] + 1;
            $serieElectronica = $this->generate_numbers($numSerieElectronica, 1, 9);

            $total12 = 0;
            $total0 = 0;
            $totalDescuento = 0;



            $datosCliente = $this->model->getCliente($idCliente);

            if ($datosCliente['id'] == 1) {
                $tipoIdentificacion = 7;
            } else if ($datosCliente['identidad'] == 'CEDULA') {
                $tipoIdentificacion = 5;
            } else {
                $tipoIdentificacion = 4;
            }
            //echo $direccion; exit;
            if (empty($idCliente)) {
                $res = array('msg' => 'EL CLIENTE ES REQUERIDO', 'type' => 'warning');
            } else {

                $descuento12 = 0;
                $descuento0 = 0;
                $descuentoP2 = 0;
                $total12 = 0;
                $total0 = 0;
                foreach ($datos['productos'] as $totalEncabezado) {
                    //  $descuento = (!empty($totalEncabezado['descuento'])) ? $totalEncabezado['descuento'] : 0;

                    // $result = $this->model->getProducto($totalEncabezado['id']);
                    // print_r($result); exit;

                    if ($totalEncabezado['iva'] == 0) {
                        $subTotal0 = $totalEncabezado['precio_pvp'] * $totalEncabezado['cantidad'];
                        $descuento0 += round($totalEncabezado['descuento'], 2);
                        $total0 += $subTotal0;
                    } else {
                        $subTotal12 = $totalEncabezado['precio_pvp'] * $totalEncabezado['cantidad'];

                        if ($totalEncabezado['por_descuento'] != 0) {
                            $descuento12 += round((number_format($subTotal12 / (CONCAT . $empresa['impuesto']), 2, '.', '') * $totalEncabezado['por_descuento']) / 100, 2); //el descuento se calcula con el impuesto que esta en la configuracion
                            $descuentoP2 += round(($subTotal12 * $totalEncabezado['por_descuento']) / 100, 2);
                        }

                        $total12 += $subTotal12;
                    }
                }
                $totalDescuento = $descuento12 + $descuento0;
                $totalDescuentoP = $descuentoP2 + $descuento0;
                $total = ($total12 + $total0) - $totalDescuentoP;


                //  print_r($serieElectronica[0]);exit;



                $notaCreditoEncabezado = $this->model->registrarEncabezado(
                    $numSerieElectronica,
                    $fecha,
                    $fechaFactura,
                    $serieFactura,
                    $datosCliente['nombre'],
                    $datosCliente['num_identidad'],
                    $tipoIdentificacion,
                    $idCliente,
                    $claveAccessoFactura,
                    $motivoNotaCredito,
                    $empresa['establecimiento'],
                    $empresa['puntoemi'],
                    $serieElectronica[0],
                    $empresa['contabilidad'],
                    AMBIENTE,
                    $total,
                    $totalDescuento,
                    $this->id_usuario,
                    $empresa['id']
                );
                if ($notaCreditoEncabezado > 0) {
                    foreach ($datos['productos'] as $producto) {
                        $descuento = (!empty($producto['por_descuento'])) ? $producto['por_descuento'] : 0;
                        $result = $this->model->getProducto($producto['id']);
                        $codigo = $result['codigo'];
                        $descripcion = strtoupper(strClean($producto['item'])); //$result['descripcion']; es para el nombre original que van en la factura
                        $precio_pvp = $producto['precio_pvp'];
                        $iva = $producto['iva'];
                        $precio = $producto['precio_pvp']; //result es lo que trae de la tabla producto y producto es lo que trae de la tabla modificada de ventas
                        if ($producto['iva'] == 0) {
                            $precio_siniva = $precio;
                        } else {
                            $precio_siniva = round($precio / (CONCAT . $empresa['impuesto']), 2); //LO DIVIDIDO PARA EL PRECIO ES CON EL IVA DEL CONFIGURACION

                        }
                        // print_r($precio_siniva);
                        $cantidad = $producto['cantidad'];
                        $subTotal = round($precio_siniva * $producto['cantidad'], 4);
                        $descuentoDetalle = round(($subTotal * $descuento) / 100, 2);
                        // print_r($subTotal);
                        //array_push($array['productos'], $data);
                        // $total += $subTotal;
                        $notaCreditoDetalle = $this->model->registrarDetalle($numSerieElectronica, $cantidad, $descripcion, $precio_siniva, $precio_pvp, $subTotal, $iva, $codigo, $descuentoDetalle, $producto['id']);
                    }
                    if ($notaCreditoDetalle > 0) {
                        foreach ($datos['productos'] as $producto) {
                            $result = $this->model->getProducto($producto['id']);
                            //actualizar stock
                            if ($result['id_categoria'] == 1) {
                                $nuevaCantidad = $result['cantidad'];
                                $totalVentas = $result['ventas'] + $producto['cantidad'];
                                $this->model->actualizarStock($nuevaCantidad, $totalVentas, $result['id']);
                            } else {
                                $nuevaCantidad = $result['cantidad'] + $producto['cantidad'];
                                $totalVentas = $result['ventas'] - $producto['cantidad'];
                                $this->model->actualizarStock($nuevaCantidad, $totalVentas, $result['id']);
                            }


                            $movimiento = 'Nota Credito Electronica N°: ' . $notaCreditoDetalle;
                            $cantidad = $producto['cantidad'];
                            $this->model->registrarMovimiento($movimiento, 'entrada', $cantidad, $nuevaCantidad, $producto['id'], $this->id_usuario);
                        }
                        /* if ($metodo == 'CREDITO') {
                                $monto = $total;
                                $this->model->registrarCredito($monto, $fecha, $hora, null, $ventaEncabezado, $idCliente);
                            }*/
                        /*if ($datos['impresion']) {
                                    $this->impresionDirecta($ventaDetalle);
                                }*/
                        //  exit;


  // === CONTROL POST-FACTURA SEGÚN SU ESTADO ===

        // Obtener factura original
        $factOriginal = $this->model->getFacturaCabecera($no_factura); // nueva función en el modelo
        $estadoFactura = (int) $factOriginal['estado'];
        $totalFactura = (float) $factOriginal['totalfactura'];
        $ordenFactura = (int) $factOriginal['orden_no'];

        $valorNC = round($total, 2); // ya calculado arriba

        // === ESCENARIO: FACTURA A CRÉDITO (estado = 2) ===
        if ($estadoFactura === 2) {

            // Obtener crédito asociado
            $credito = $this->model->getCreditoByFactura($ordenFactura);
            $idCredito = $credito['id'] ?? 0;
            $saldoCredito = (float) ($credito['monto'] ?? 0);

            // === NOTA DE CRÉDITO TOTAL (<= saldo crédito) ===
            if ($valorNC >= $saldoCredito) {

                // 1) Marcar factura anulada por NC
                $this->model->marcarFacturaAnuladaNC($ordenFactura);

                // 2) Cambiar estado del crédito = 4 (anulado por NC) //marcarCreditoAnuladoNC
                if ($idCredito) {
                    $this->model->anularCreditoPorNotaCredito($idCredito);
                }

                // NO se debe registrar abono
            }

            // === NOTA DE CRÉDITO PARCIAL (< saldo crédito) ===
            else {

                // Registrar abono de tipo NOTA CREDITO
                if ($idCredito) {
                    $this->model->registrarAbonoNC(
                        $idCredito,
                        $idCliente,
                        $valorNC,
                        $this->id_usuario
                    );

                    // recalcular saldo o finalizar si llegó a cero
                    $this->model->actualizarCreditoPendiente($idCredito);
                }
            }
        }


        // === ESCENARIO: FACTURA CONTADO (estado = 1) ===
        // Solo aplica cuando la NC es TOTAL
        if ($estadoFactura === 1) {

            if ($valorNC >= $totalFactura) {

                // anular factura completamente
                $this->model->marcarFacturaAnuladaNC($ordenFactura);

                // No existe crédito → No se registra nada más
            }
        }




                        // SRI try/catch: tolerante a fallos SOAP/SRI
                        try {
                            $enviarXML = new enviarXML();
                            $claveAcceso = $enviarXML->envioXML($numSerieElectronica, NOTACREDITO);
                            $validacionComprobante = new validacionComprobante();
                            $validacion = $validacionComprobante->validar_comprobante($claveAcceso, NOTACREDITO);
                            $autorizacionComprobante = new autorizacionComprobante();
                            $autorizacion = $autorizacionComprobante->autorizacion_comprobante($claveAcceso, NOTACREDITO);
                        } catch (\Throwable $e) {
                            error_log('SRI fallo en controllers/notaCredito.php: ' . $e->getMessage());
                            $autorizacion = ['numeroComprobantes' => 1, 'autorizaciones' => ['autorizacion' => ['estado' => 'NO_AUTORIZADO']]];
                            $claveAcceso = $claveAcceso ?? '';
                        }
                        //  print_r($autorizacion['autorizaciones']['autorizacion']['estado']); exit;
                        // $data['claveAcceso'] = $claveAcceso;
                        $this->model->actualizarClaveAccesso($claveAcceso, $numSerieElectronica);

                        //$result = mysqli_num_rows($update_clave);
                        if ($autorizacion['numeroComprobantes'] == '0') {
                            $this->envioSriElectronica($numSerieElectronica);
                        } else if ($autorizacion['autorizaciones']['autorizacion']['estado'] == 'AUTORIZADO') {

                            $notaCreditoElectronica = $this->model->getDatosNotaCreditoElectronica($claveAcceso);

                            $dataInfo = array(
                                'ruc' => $notaCreditoElectronica['ruc_cliente'],
                                'email' => $notaCreditoElectronica['correo'],
                                'fecha' => $notaCreditoElectronica['fecha'],
                                'total_modificar' => $notaCreditoElectronica['total_modificar'],
                                'cliente' => $notaCreditoElectronica['cliente'],
                                'claveAcceso' => $claveAcceso,
                                'empresa' => $empresa['nombre'],
                                'notaCredito' => $notaCreditoElectronica['secuencial'],
                                'enviroment' => ENVIROMENT,
                                'emailremitente' => $empresa['correo'],
                                'establecimiento' => $empresa['establecimiento'],
                                'puntoemi' => $empresa['puntoemi'],
                                'tipo' => 'notaCredito',
                                'asunto' => 'Adjuntamos Comprobante Electronico'
                            );
                            $res = array('msg' => 'NOTA CREDITO ELECTRONICA GENERADA EXITOSAMENTE', 'type' => 'success', 'ClaveAcceso' => $claveAcceso, 'factura' => 'electronica', 'idVenta' => $numSerieElectronica);
                            sendEmail($dataInfo, 'email_notacreditoelectronica', 'notaCredito');
                        } else {
                            $res = array('msg' => 'ERROR LA NOTA CREDITO, CONTACTE CON SOPORTE', 'type' => 'error');
                        }
                        //print_r($dataInfo); exit;



                    } else {
                        $this->model->deleteFactura('datos_cabecera_electronica', 'orden_no', $numSerieElectronica);
                        $this->model->resetFactura('datos_cabecera_electronica');
                        $res = array('msg' => 'ERROR AL GENERAR LA FACTURA ELECTRONICA DETALLE ', 'type' => 'error');
                    }
                } else {
                    $this->model->deleteFactura('datos_cabecera_electronica', 'orden_no', $numSerieElectronica);
                    $this->model->resetFactura('datos_cabecera_electronica');
                    $res = array('msg' => 'ERROR AL GENERAR FACURA ELECTRONICA ENCABEZADO', 'type' => 'error');
                }

            }
        } else {
            $res = array('msg' => 'CARRITO VACIO', 'type' => 'warning');
        }
        echo json_encode($res, JSON_UNESCAPED_UNICODE);
        die();
    }


    public function listarElectronica()
    {

        $data = $this->model->getNotaCreditosElectronica();
        for ($i = 0; $i < count($data); $i++) {
            $btnEdit = '';
            $btnDelete = '';
            $btnView = '';
            $ArchivoPDF = "facturaelectronica/public/archivos/notaCreditos/ride/" . $data[$i]['claveacceso'] . ".pdf";
            $ArchivoXML = "facturaelectronica/public/archivos/notaCreditos/autorizados/" . $data[$i]['claveacceso'] . ".xml";

            if (file_exists($ArchivoPDF) === false || file_exists($ArchivoXML) === false) {
                // return array('error' => true, 'mensaje' => 'documento generado no existe');

                $btnEdit = '<a class="btn btn-warning" href="#" onclick="envioSriElectronica(' . $data[$i]['orden_no'] . ')"><i class="fa-solid fa-paper-plane text-white"></i></a>';

            } else {
                if ($data[$i]['autorizacion'] == 'AUTORIZADO' && $data[$i]['estado'] == 1) {
                    //<a class="btn btn-info" href="' . BASE_URL . 'notaCreditos/notaCreditosTicked/' . $data[$i]['claveacceso'] . '/' . $data[$i]['orden_no'] . '" target="_blank"><i class="fa-solid fa-file-arrow-down text-white"></i></a>
                    $btnView = '<a class="btn btn-danger" href="' . BASE_URL . 'facturaelectronica/public/archivos/notaCreditos/ride/' . $data[$i]['claveacceso'] . '.pdf' . '.pdf" target="_blank" title="FACTURA"><i class="fas fa-file-pdf"></i></a>
                        ';

                    $btnEdit = '<a class="btn btn-success" href="#" onclick="envioCorreoElectronica(' . $data[$i]['orden_no'] . ')"><i class="fa-solid fa-envelope"></i></a>';

                } else if ($data[$i]['autorizacion'] == 'NO AUTORIZADO') {

                    $btnEdit = '<a class="btn btn-warning" href="#" onclick="envioSriElectronica(' . $data[$i]['orden_no'] . ')"><i class="fa-solid fa-paper-plane text-white"></i></a>';

                } else if ($data[$i]['autorizacion'] == 'DEVUELTA') {
                    $btnEdit = '<a class="btn btn-warning" href="#" onclick="envioSriElectronica(' . $data[$i]['orden_no'] . ')"><i class="fa-solid fa-paper-plane text-white"></i></a>';

                } else if ($data[$i]['autorizacion'] == 'EN PROCESO') {
                    $btnEdit = '<a class="btn btn-warning" href="#" onclick="envioSriElectronica(' . $data[$i]['orden_no'] . ')"><i class="fa-solid fa-paper-plane text-white"></i></a>';

                } else {

                    $btnView = '<a class="btn btn-info" href="' . BASE_URL . 'notaCreditos/notaCreditosTicked/' . $data[$i]['claveacceso'] . '/' . $data[$i]['orden_no'] . '" target="_blank"><i class="fa-solid fa-file-arrow-down text-white"></i></a>';

                }
            }
            $data[$i]['acciones'] = '<div class="text-center">' . $btnView . ' ' . $btnEdit . ' ' . $btnDelete . '</div>';

            if ($data[$i]['autorizacion'] == 'AUTORIZADO') {
                $data[$i]['autorizacion'] = '<div><span class="badge bg-success">AUTORIZADO</span></div>';
            } else if ($data[$i]['autorizacion'] == 'NO AUTORIZADO') {
                $data[$i]['autorizacion'] = '<div><span class="badge bg-danger">NO AUTORIZADO</span></div>';
            } else if ($data[$i]['autorizacion'] == 'DEVUELTA') {
                $data[$i]['autorizacion'] = '<div><span class="badge bg-warning">DEVUELTA</span></div>';
            } else {
                $data[$i]['autorizacion'] = '<div><span class="badge bg-info">EN PROCESO</span></div>';
            }


            if ($data[$i]['estado'] == 1) {
                $data[$i]['estado'] = '<div style="text-align: center;"><span class="badge bg-success">PAGADA</span></div>';
            } else if ($data[$i]['estado'] == 2) {
                $data[$i]['estado'] = '<div style="text-align: center;"><span class="badge bg-warning">PENDIENTE</span></div>';
            } else {
                $data[$i]['estado'] = '<div style="text-align: center;"><span class="badge bg-danger">ANULADA</span></div>';
            }


            //  print_r( $data[$i]['factura']);
        }
        echo json_encode($data, JSON_UNESCAPED_UNICODE);
        die();
    }

    //buscar factura
    public function buscar()
    {

        $valor = strClean($_POST['term']);
        $array = array();

        // Buscar factura por clave de acceso o número
        $factura = $this->model->buscarPorClaveAcceso($valor);

        if (!empty($factura)) {
            // Obtener detalle de la factura
            $detalle = $this->model->getFacturaDetalle($factura['orden_no']);
            $serie = $this->generate_numbers($factura['secuencial'], 1, 9);

            $result = array(
                'id' => $factura['orden_no'],
                'claveAcceso' => $factura['claveacceso'],
                'serieFactura' => $factura['establecimiento'] . '-' . $factura['punto_emi'] . '-' . $serie[0],
                'cliente' => $factura['cliente'],
                'idCliente' => $factura['id_cliente'],
                'fechaFactura' => $factura['fecha'],
                'rucCliente' => $factura['ruc'],
                'detalle' => $detalle
            );

            $array[] = $result;
        }

        echo json_encode($array, JSON_UNESCAPED_UNICODE);
        die();
    }



    //mostrar productos desde localStorage
    public function mostrarDatos()
    {

        $json = file_get_contents('php://input');
        $datos = json_decode($json, true);
        $array['productos'] = array();
        $empresa = $this->model->getEmpresa();
        $totalVenta = 0;
        //  print_r($datos); exit;
        $descuentoProducto = 0;
        if (!empty($datos)) {
            foreach ($datos as $producto) {
                //  print_r($producto); exit;
                // $result = $this->model->getFacturaDetalle($producto['id']);
                $data['id'] = $producto['id'];
                $data['orden_no'] = $producto['orden_no'];

                $data['codProducto'] = $producto['codProducto'];
                $data['nombre'] = $producto['item']; //$result['descripcion']; eso es el dato de la tabla prodcuto, nombre original con $result es nombre original de $producto es nombre modificado
                $data['descuento'] = number_format((empty($producto['por_descuento'])) ? 0 : $producto['por_descuento'], 2, '.', '');

                if ($producto['iva'] = $empresa['impuesto']) {

                    $data['precio_venta'] = number_format((empty($producto['precio_pvp'])) ? 0 : $producto['precio_pvp'], 2, '.', '');
                } else {

                    $data['precio_venta'] = number_format((empty($producto['precio_pvp'])) ? 0 : $producto['precio_pvp'], 2, '.', '');
                }

                $descuentoProducto = (empty($data['descuento'])) ? 0 : ((($data['precio_venta'] * $producto['cantidad']) * $data['descuento']) / 100);

                //   print_r($producto['precio_u'] - $producto['descuento']);
                $data['cantidad'] = $producto['cantidad'];
                $data['iva'] = $producto['iva'];
                // $data['descuento'] = $producto['descuento'];

                // $descuentoProducto =  (empty($producto['descuento'])) ? 0 : ((($data['precio_venta'] * $producto['cantidad'] ) * $producto['descuento'] )/100) ;     
                $subTotalVenta = ($data['precio_venta'] * $producto['cantidad']) - $descuentoProducto;
                $data['subTotalVenta'] = number_format($subTotalVenta, 2);
                array_push($array['productos'], $data);
                $totalVenta += $subTotalVenta;
            }
        }
        $array['totalVenta'] = number_format($totalVenta, 2);
        $array['totalVentaHidden'] = $totalVenta;

        echo json_encode($array, JSON_UNESCAPED_UNICODE);
        die();
    }


    public function buscarPorNombre()
    {

        $array = array();
        $valor = $_GET['term'];
        $orden_no = $_GET['orden_no'];
        //  print_r($_GET);exit;
        $data = $this->model->buscarPorNombre($valor, $orden_no);
        // print_r($data);exit;
        foreach ($data as $row) {
            $result['id_producto'] = $row['id_producto'];
            $result['label'] = $row['item'];
            $result['cantidad'] = $row['cantidad'];
            $result['codproducto'] = $row['codproducto'];
            $result['orden_no'] = $row['orden_no'];
            $result['item'] = $row['item'];
            $result['precio_u'] = $row['precio_u'];
            $result['total'] = $row['total'];
            $result['descuento'] = $row['descuento'];
            $result['iva'] = $row['iva'];
            $result['por_descuento'] = $row['por_descuento'];


            array_push($array, $result);
        }
        echo json_encode($array, JSON_UNESCAPED_UNICODE);
        die();
    }

    public function verificarStock($data)
    {
        $array = explode(',', $data);
        $idProducto = $array[0];
        $orden_no = $array[1];

        $data = $this->model->getStockNC($idProducto, $orden_no);
        echo json_encode($data);
        die();
    }

    //REENVIO DE FACTURA ELECTRONICA AL SRI
    public function envioSriElectronica($idVenta)
    {
        $this->cargarSri();

        $empresa = $this->model->getEmpresa();

        $facturaElectronica = $this->model->getnotaCreditoElectronica($idVenta);
        //$claveAcceso= $facturaElectronica['0']['claveacceso'];


        // SRI try/catch: tolerante a fallos SOAP/SRI
        try {
            $enviarXML = new enviarXML();
            $claveAcceso = $enviarXML->envioXML($idVenta, NOTACREDITO);
            $validacionComprobante = new validacionComprobante();
            $validacion = $validacionComprobante->validar_comprobante($claveAcceso, NOTACREDITO);
            $autorizacionComprobante = new autorizacionComprobante();
            $autorizacion = $autorizacionComprobante->autorizacion_comprobante($claveAcceso, NOTACREDITO);
        } catch (\Throwable $e) {
            error_log('SRI fallo en controllers/notaCredito.php: ' . $e->getMessage());
            $autorizacion = ['numeroComprobantes' => 1, 'autorizaciones' => ['autorizacion' => ['estado' => 'NO_AUTORIZADO']]];
            $claveAcceso = $claveAcceso ?? '';
        }
        //  print_r($autorizacion['autorizaciones']['autorizacion']['estado']); exit;
        // $data['claveAcceso'] = $claveAcceso;
        $this->model->actualizarClaveAccesso($claveAcceso, $idVenta);
        // print_r($autorizacion['autorizaciones']); exit;
        if ($validacion['estado'] == 'DEVUELTA') {
            $res = array('msg' => $validacion['comprobantes']['comprobante']['mensajes']['mensaje']['mensaje'], 'type' => 'error');
        } else if ($autorizacion['numeroComprobantes'] == 0) {
            $res = array('msg' => $validacion['comprobantes']['comprobante']['mensajes']['mensaje']['mensaje'], 'type' => 'error');
        } else {

            $notaCreditoElectronica = $this->model->getDatosNotaCreditoElectronica($claveAcceso);

            $dataInfo = array(
                'ruc' => $notaCreditoElectronica['ruc_cliente'],
                'email' => $notaCreditoElectronica['correo'],
                'fecha' => $notaCreditoElectronica['fecha'],
                'total_modificar' => $notaCreditoElectronica['total_modificar'],
                'cliente' => $notaCreditoElectronica['cliente'],
                'claveAcceso' => $claveAcceso,
                'empresa' => $empresa['nombre'],
                'notaCredito' => $notaCreditoElectronica['secuencial'],
                'enviroment' => ENVIROMENT,
                'emailremitente' => $empresa['correo'],
                'establecimiento' => $empresa['establecimiento'],
                'puntoemi' => $empresa['puntoemi'],
                'tipo' => 'notaCredito',
                'asunto' => 'Adjuntamos Comprobante Electronico'
            );
            $res = array('msg' => 'NOTA CREDITO ELECTRONICA GENERADA EXITOSAMENTE REENVIO AL SRI', 'type' => 'success');
            sendEmail($dataInfo, 'email_notacreditoelectronica', 'notaCredito');
        }


        echo json_encode($res, JSON_UNESCAPED_UNICODE);
        die();
    }








    public function buscarFactura()
    {

        $clave = $_POST['clave'];
        //print_r($clave ); exit;
        $data = $this->model->getFactura($clave);
        //print_r($data); exit;
        if (!empty($data)) {
            $detalle = $this->model->getFacturaDetalle($data['orden_no']);
            $data['detalle'] = $detalle;
            //print_r($detalle); exit;
            echo json_encode($data, JSON_UNESCAPED_UNICODE);
        } else {
            echo json_encode(['msg' => 'Factura no encontrada', 'type' => 'warning']);
        }
    }





    function generate_numbers($start, $count, $digits)
    {
        $result = array();
        for ($n = $start; $n < $start + $count; $n++) {
            $result[] = str_pad($n, $digits, "0", STR_PAD_LEFT);
        }
        return $result;
    }
}

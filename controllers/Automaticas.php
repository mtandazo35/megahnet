<?php
require 'vendor/autoload.php';
use Mike42\Escpos\Printer;
use Mike42\Escpos\EscposImage;

use Dompdf\Dompdf;

class Automaticas extends Controller
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
        $fechaCorteF = date('m-Y');

        $data['title'] = 'Automaticas Facturas';
        $data['script'] = 'automaticas.js';

        //$data[ 'busqueda' ] = 'busqueda.js';
        //$data[ 'carrito' ] = 'posVenta';
        $data['empresa'] = $this->model->getEmpresa();
        $data['estadoCorteF'] = $this->model->estadoCorte($fechaCorteF, 'FACTURA');

        // $data[ 'cantidadDocumento' ] =  $this->model->cantidadDocumento( date( 'Y-m' ) );
        $resultSerieElectronica = $this->model->getSerieElectronica();
        // factura fisica
        $serieElectronica = ($resultSerieElectronica['total'] == null) ? 1 : $resultSerieElectronica['total'] + 1;
        // factura electronica
        $data['serieelectronica'] = $this->generate_numbers($serieElectronica, 1, 9);
        $this->views->getView('automaticas', 'index', $data);
    }
    public function indexOrdenVenta()
    {
        $this->cargarSri();
        $fechaCorteO = date('m-Y');

        $data['title'] = 'Automaticas Orden Venta';
        $data['script'] = 'automaticasOrdenVenta.js';

        //$data[ 'busqueda' ] = 'busqueda.js';
        //$data[ 'carrito' ] = 'posVenta';
        $data['empresa'] = $this->model->getEmpresa();
        $data['estadoCorteO'] = $this->model->estadoCorte($fechaCorteO, 'ORDENVENTA');
        // $data[ 'cantidadDocumento' ] =  $this->model->cantidadDocumento( date( 'Y-m' ) );
        $resultSerieElectronica = $this->model->getSerieElectronica();
        // factura fisica
        $serieElectronica = ($resultSerieElectronica['total'] == null) ? 1 : $resultSerieElectronica['total'] + 1;
        // factura electronica
        $data['serieelectronica'] = $this->generate_numbers($serieElectronica, 1, 9);
        $this->views->getView('automaticas', 'indexOrdenVenta', $data);
    }
       public function registrarVentaAutomatico($factura)
    {
        $this->cargarSri();
        // El proceso puede iterar cientos de contratos; aumentar timeouts.
        @set_time_limit(0);
        @ini_set('memory_limit', '512M');

        $valorFactura = ($factura == 2) ? 0 : 1 ;

        $campo = ($valorFactura == 1) ? 'FACTURA' : 'ORDENVENTA' ;
       // print_r($factura); exit;
        $fechaCorte = date('m-Y');
        $fechas = date('Y-m-d');
        $horas = date('H:i:s');
        $datosnumOrden = '';
        $datosFacturasElectronicas = '';
        $mesActualLetra = MESES[date('n')];
        $mesAnteriorLetra=MESES[date('n') - 1];


        $empresa = $this->model->getEmpresa();
        $getContratosFacturar = $this->model->getContratosFacturar($mesActualLetra, 1,$valorFactura);
        //$getProductos = $this->model->getProductoAutomatico(1);
        // print_r($getContratosFacturar);exit;
        // $idpro =  $getProducto[ 'id' ];
        $productos = array();
        $descuento = 0;
        $countFacturas = 0;
        $countOrdenVentas = 0;
        $array['numOrdenVenta'] = array();
        $array['numFacturasElectronica'] = array();
        //echo $fechamovimiento =  date('F');


        //echo date('c');
        // $fechamovimiento =  "2018-06-05";

        $fallidos = []; // acumular contratos que fallaron sin abortar el batch
        foreach ($getContratosFacturar as $datosContrato) {
            try {

            $fecha = date('Y-m-d');
            $hora = date('H:i:s');
            $metodo = 'CREDITO';
            $estado = 2;
            $facturacontrato = $datosContrato['factura'];
            $array['productos'] = array();

            // print_r($datosContrato); exit;

            $resultSerieOrdenVenta = $this->model->getSerieOrdenVenta();
            $numserieOrdenVenta = ($resultSerieOrdenVenta['total'] == null) ? 1 : $resultSerieOrdenVenta['total'] + 1; // factura fisica
            $serieOrdenVenta = $this->generate_numbers($numserieOrdenVenta, 1, 9);

           // $resultSerie = $this->model->getSerie();
            //$serie = ($resultSerie['total'] == null) ? 1 : $resultSerie['total'] + 1; // factura fisica
           // $data['serie'] = $this->generate_numbers($serie, 1, 9);

            // print_r($serie); exit;

            $resultSerieElectronica = $this->model->getSerieElectronica();
            $numSerieElectronica = ($resultSerieElectronica['total'] == null) ? 1 : $resultSerieElectronica['total'] + 1;
            $serieElectronica = $this->generate_numbers($numSerieElectronica, 1, 9);
            $total12 = 0;
            $total0 = 0;
            $subTotal = 0;
            $total = 0;
            $subTotal = 0;
            $idCliente = $datosContrato['idCliente'];
            $idContrato = $datosContrato['id'];
            $getCliente = $this->model->getCliente($idCliente);
            $descuento12 = 0;
            $descuento0 = 0;
            if ($getCliente['id'] == 1) {
                $tipoIdentificacion = 7;
            } else if ($getCliente['identidad'] == 'CEDULA') {
                $tipoIdentificacion = 5;
            } else {
                $tipoIdentificacion = 4;
            }

            if ($facturacontrato == 1) {

                $productos = json_decode($datosContrato['productos'], true);

                foreach ($productos as $totalEncabezado) {
                    //print_r( $totalEncabezado );exit;
                    //print_r($totalEncabezado); exit;
                    //$result = $this->model->getProductoAutomatico( $totalEncabezado[ 'id' ] );
                    //  print_r( $result );

                    if ($totalEncabezado['iva_producto'] == 0) {
                        //$subTotal0 = $totalEncabezado[ 'precio' ] * $totalEncabezado[ 'cantidad' ];
                        $subTotal0 = $totalEncabezado['precio'];
                        $total0 += $subTotal0;
                        $descuento0 = round(($total0 * $descuento) / 100, 2);
                    } else {
                        //$subTotal12 = $totalEncabezado[ 'precio' ] * $totalEncabezado[ 'cantidad' ];
                        $subTotal12 = $totalEncabezado['precio'];
                        $total12 += $subTotal12;
                        $descuento12 = round((($total12 / (CONCAT . $empresa['impuesto']))  * $descuento) / 100, 2);
                        //el descuento se calcula con el impuesto que esta en la configuracion
                    }
                }
                $total = $total12 + $total0;
                $totalDescuento = $descuento12 + $descuento0;


                $ventaEncabezado = $this->model->registrarEncabezado(
                    $fecha,
                    $numSerieElectronica,
                    trim($getCliente['nombre']),
                    trim($getCliente['direccion']),
                    trim($getCliente['telefono']),
                    trim($getCliente['num_identidad']),
                    $tipoIdentificacion,
                    $getCliente['correo'],
                    $empresa['establecimiento'],
                    $empresa['puntoemi'],
                    $empresa['ruc'],
                    AMBIENTE,
                    $empresa['razon_social'],
                    $empresa['nombre'],
                    $numSerieElectronica,
                    $empresa['direccion'],
                    $empresa['contabilidad'],
                    $totalDescuento,
                    $total,
                    '',
                    $estado,
                    $metodo,
                    $this->id_usuario
                );
                if ($ventaEncabezado > 0) {
                    foreach ($productos as $producto) {

                        $result = $this->model->getProducto($producto['id']);
                        //print_r( $result );         exit;
                        $codigo = $result['codigo'];
                        $descripcion = $producto['nombre'] . CONCEPTOMES . $mesActualLetra;
                        //$result[ 'descripcion' ];
                        $iva = $result['iva'];
                        // $precio = $producto[ 'precio_venta' ];
                        //result es lo que trae de la tabla producto y producto es lo que trae de la tabla modificada de ventas
                        $precio = $producto['precio'];
                        //result es lo que trae de la tabla producto y producto es lo que trae de la tabla modificada de ventas
                        if ($iva == 0) {
                            $precio_siniva = $precio;
                        } else {
                            $precio_siniva = round($precio / (CONCAT .  $empresa['impuesto']), 4);
                            //LO DIVIDIDO PARA EL PRECIO ES CON EL IVA DEL CONFIGURACION
                        }
                        $cantidad = 1;
                        //$descuento=0;
                        $subTotal = round($precio_siniva * $cantidad, 4);
                        $descuentoDetalle = round(($subTotal * $descuento) / 100, 2);
                        $ventaDetalle =  $this->model->registrarDetalle($numSerieElectronica, $cantidad, $descripcion, $precio_siniva, $subTotal, $iva, $codigo, $descuentoDetalle);
                    }
                    if ($ventaDetalle > 0) {
                        foreach ($productos as $producto) {
                            $result = $this->model->getProducto($producto['id']);


                            if ($result['id_categoria'] == 1) {
                                $nuevaCantidad = $result['cantidad'];
                                $totalVentas = $result['ventas'] + $producto['cantidad'];
                                $this->model->actualizarStock($nuevaCantidad, $totalVentas, $result['id']);
                            } else {
                                $nuevaCantidad = $result['cantidad'] - $cantidad;
                                $totalVentas = $result['ventas'] + $cantidad;
                                $this->model->actualizarStock($nuevaCantidad, $totalVentas, $result['id']);
                            }


                            $movimiento = 'Venta Electronica N°: ' . $ventaDetalle;
                            // $cantidad = $producto[ 'cantidad' ];
                            $cantidad = 1;
                            $this->model->registrarMovimiento($movimiento, 'salida', $cantidad, $nuevaCantidad, $result['id'], $this->id_usuario);
                        }
                        if ($metodo == 'CREDITO') {
                            $monto = $total - $descuento;
                            $this->model->registrarCredito($monto, $fecha, $hora, null, $ventaEncabezado, null, $idContrato);
                        }

                        $this->model->actualizarMesContratoF($mesActualLetra, 1, $idContrato, 'UNO');

                        array_push($array['numFacturasElectronica'], $ventaEncabezado);

                        // SRI try/catch automaticas: si falla SOAP, no rompe la respuesta JSON
                        try {
                            $enviarXML = new enviarXML();
                            $claveAcceso = $enviarXML->envioXML($numSerieElectronica, FACTURA);
                            $validacionComprobante = new validacionComprobante();
                            $validacion = $validacionComprobante->validar_comprobante($claveAcceso, FACTURA);
                            $autorizacionComprobante = new autorizacionComprobante();
                            $autorizacion = $autorizacionComprobante->autorizacion_comprobante($claveAcceso, FACTURA);
                        } catch (\Throwable $e) {
                            error_log("SRI automaticas fallo: " . $e->getMessage());
                            $autorizacion = ["numeroComprobantes" => 1, "autorizaciones" => ["autorizacion" => ["estado" => "NO_AUTORIZADO"]]];
                            $claveAcceso = $claveAcceso ?? "";
                        }
                        // $data[ 'claveAcceso' ] = $claveAcceso;
                        $this->model->actualizarClaveAccesso($claveAcceso, $numSerieElectronica);
                        //$result = mysqli_num_rows( $update_clave );

                        if ($autorizacion['numeroComprobantes'] == 0) {
                            $this->envioSriElectronica($numSerieElectronica);
                        }else{  
						if ($autorizacion['autorizaciones']['autorizacion']['estado'] == 'AUTORIZADO') {

                            $facturaElectronica = $this->model->getFacturaElectronica($claveAcceso);

                            $dataInfo = array(
                                'ruc' => $facturaElectronica['ruc'],
                                'email' => $facturaElectronica['correo'],
                                'fecha' => $facturaElectronica['fecha'],
                                'totalfactura' => $facturaElectronica['totalfactura'],
                                'cliente' => $facturaElectronica['cliente'],
                                'claveAcceso' => $claveAcceso,
                                'empresa' => $empresa['nombre'],
                                'factura' => $serieElectronica[0],
                                'enviroment' => ENVIROMENT,
                                'emailremitente' => $empresa['correo'],
                                'establecimiento' => $empresa['establecimiento'],
                                'puntoemi' => $empresa['puntoemi'],
                                'tipo' => 'factura',
                                'asunto' => 'Adjuntamos Comprobante Electronico'
                            );
                            // $res = array('msg' => 'FACTURA ELECTRONICA GENERADA EXITOSAMENTE', 'type' => 'success', 'ClaveAcceso' => $claveAcceso, 'factura' => 'electronica', 'idVenta' => $numSerieElectronica);
                            sendEmailAutomatias($dataInfo);
                        } 

                    }

                        //print_r( $dataInfo );
                        //  exit;
                    } else {
                        $this->model->deleteFactura('datos_cabecera_electronica', 'orden_no', $numSerieElectronica);
                        $this->model->resetFactura('datos_cabecera_electronica');
                        // $res = array('msg' => 'ERROR AL GENERAR LA FACTURA ELECTRONICA DETALLE ', 'type' => 'error');
                    }
                } else {
                    $this->model->deleteFactura('datos_cabecera_electronica', 'orden_no', $numSerieElectronica);
                    $this->model->resetFactura('datos_cabecera_electronica');
                    // $res = array('msg' => 'ERROR AL GENERAR FACURA ELECTRONICA ENCABEZADO', 'type' => 'error');
                }

                //die();
                $countFacturas += 1;
            } else {

                $productos = json_decode($datosContrato['productos'], true);

                foreach ($productos as $producto) {
                    $result = $this->model->getProducto($producto['id']);
                    $dataOrden['id'] = $result['id'];
                    $dataOrden['nombre'] = $producto['nombre'] . $mesActualLetra;
                    $dataOrden['precio'] = $producto['precio']; //result es lo que trae de la tabla producto y producto es lo que trae de la tabla modificada de ventas
                    $dataOrden['cantidad'] = $producto['cantidad'];
                    $dataOrden['iva_producto'] = $result['iva'];
                    $dataOrden['codigobarra'] = $result['codigo'];
                    $subTotal = $producto['precio'] * $producto['cantidad'];
                    array_push($array['productos'], $dataOrden);
                    $total += $subTotal;
                    //  print_r($total); exit;
                }
                $datosProductos = json_encode($array['productos'], JSON_UNESCAPED_UNICODE);
                $ordenVenta = $this->model->registrarOrdenVenta($datosProductos, $total, $fecha, $hora, $metodo, $descuento, $serieOrdenVenta[0], $estado, $idCliente, $this->id_usuario,'');
                if ($ordenVenta > 0) {
                    foreach ($productos as $producto) {
                        $result = $this->model->getProducto($producto['id']);
                        //actualizar stock
                        $idProducto =   $result['id'];
                        //  print_r($result); exit;
                        if ($result['id_categoria'] == 1) {
                            $nuevaCantidad = $result['cantidad'];
                            $totalVentas = $result['ventas'] + $producto['cantidad'];
                            $this->model->actualizarStock($nuevaCantidad, $totalVentas, $result['id']);
                        } else {
                            $nuevaCantidad = $result['cantidad'] - $producto['cantidad'];
                            $totalVentas = $result['ventas'] + $producto['cantidad'];
                            $this->model->actualizarStock($nuevaCantidad, $totalVentas, $result['id']);
                        }


                        $movimiento = 'Orden Venta N°: ' . $ordenVenta;
                        $cantidad = $producto['cantidad'];
                        $this->model->registrarMovimiento($movimiento, 'salida', $cantidad, $nuevaCantidad, $producto['id'], $this->id_usuario);
                    }
                    if ($metodo == 'CREDITO') {
                        $monto = $total - $descuento;
                        $this->model->registrarCredito($monto, $fecha, $hora, null, null, $ordenVenta, $idContrato);
                    }
                    $this->model->actualizarMesContratoF($mesActualLetra, 1, $idContrato, 'UNO');

                   array_push($array['numOrdenVenta'], $ordenVenta);

                    if ($idProducto != 11) {
                        $this->reporte('facturas', $ordenVenta);
                        $ordenEnvio = $this->model->getOrdenVenta($ordenVenta);
                        $dataInfo = array(
                            'ruc' => $ordenEnvio['num_identidad'],
                            'email' => $ordenEnvio['correo'],
                            'fecha' => $ordenEnvio['fecha'],
                            'totalfactura' => $ordenEnvio['total'],
                            'cliente' => $ordenEnvio['nombre'],
                            'empresa' => $empresa['nombre'],
                            'factura' => $ordenVenta,
                            'enviroment' => ENVIROMENT,
                            'emailremitente' => $empresa['correo'],
                            'establecimiento' => $empresa['establecimiento'],
                            'puntoemi' => $empresa['puntoemi'],
                            'asunto' => 'Adjuntamos Comprobante'
                        );
                        // $res = array('msg' => 'FACTURA ELECTRONICA GENERADA EXITOSAMENTE', 'type' => 'success', 'ClaveAcceso' => $claveAcceso, 'factura' => 'electronica', 'idVenta' => $numSerieElectronica);
                        sendEmailOrdenAutomaticas($dataInfo);
                    }
                    $this->model->actualizarMesContratoF($mesAnteriorLetra, 0, $idContrato, 'UNO');

                   // $array['numOrdenVenta'] = $ordenVenta;

                    //die();
                    $countOrdenVentas += 1;
                    //$res = array('msg' => 'ORDEN VENTA GENERADA EXITOSAMENTE', 'type' => 'success', 'idVenta' => $ordenVenta, 'factura' => 'fisica');
                } 

                 
            }
            } catch (\Throwable $eFor) {
                $fallidos[] = [
                    'idContrato' => $datosContrato['id']     ?? '?',
                    'cliente'    => $datosContrato['nombre'] ?? '',
                    'error'      => $eFor->getMessage(),
                ];
                $logDir = __DIR__ . '/../storage';
                if (!is_dir($logDir)) { @mkdir($logDir, 0755, true); }
                @file_put_contents(
                    $logDir . '/automaticas_facturacion.log',
                    '[' . date('Y-m-d H:i:s') . '] contrato=' . ($datosContrato['id'] ?? '?') . ' cliente=' . ($datosContrato['nombre'] ?? '') . ' error=' . $eFor->getMessage() . PHP_EOL,
                    FILE_APPEND | LOCK_EX
                );
                continue;
            }
        }
        // $datosOrdenVenta = json_encode($array['numOrdenVenta'], JSON_UNESCAPED_UNICODE);
        $datosnumOrden = json_encode($array['numOrdenVenta'], JSON_UNESCAPED_UNICODE);
       // $datosFacturasElectronicas = json_encode($array['numFacturasElectronica'], JSON_UNESCAPED_UNICODE);

        // print_r($datosnumOrden); 
        //print_r($datosFacturasElectronicas); 
       // $this->model->actualizarMesContratoF($mesActualLetra, 0, $idContrato, 'TODOS');
        //$this->model->actualizarEstadoCorte(0);
        $this->model->actualizarCorte($fechaCorte, $fechas.' '.$horas, $campo);


        $totalProc  = $countFacturas + $countOrdenVentas;
        $nFallidos  = count($fallidos);
        $resMsg     = $totalProc . ' procesados (' . $countFacturas . ' facturas + ' . $countOrdenVentas . ' ordenes)';
        if ($nFallidos > 0) {
            $resMsg .= '. ' . $nFallidos . ' contrato(s) fallaron — revisar storage/automaticas_facturacion.log';
            $resType = 'warning';
        } else {
            $resType = 'success';
        }
        $res = array(
            'msg'                  => $resMsg,
            'type'                 => $resType,
            'OrdenesVenta'         => $datosnumOrden,
            'FacturasElectronicas' => $datosFacturasElectronicas,
            'totalProcesados'      => $totalProc,
            'totalFallidos'        => $nFallidos,
            'fallidos'             => $fallidos,
        );
        //exit;
        echo json_encode($res, JSON_UNESCAPED_UNICODE);
    }
   /* public function registrarVentaAutomatico($factura)
    {

        $valorFactura = ($factura == 2) ? 0 : 1;
     //print_r($valorFactura); exit;

       // $campo = ($valorFactura == 1) ? 'FACTURA' : 'ORDENVENTA';
        // print_r($factura); exit;
        $fechaCorte = date('m-Y');
        $fechas = date('Y-m-d');
        $horas = date('H:i:s');
        $mesActualLetra = MESES[date('n')];


        $empresa = $this->model->getEmpresa();
        $getContratosFacturar = $this->model->getContratosFacturar($mesActualLetra, 1, $valorFactura);
        // print_r($getContratosFacturar);exit;
        // $idpro =  $getProducto[ 'id' ];
        $productos = array();
        $descuento = 0;
      
      
        foreach ($getContratosFacturar as $datosContrato) {

            $fecha = date('Y-m-d');
            $hora = date('H:i:s');
            $metodo = 'CREDITO';
            $estado = 2;
            $array['productos'] = array();

           
            $resultSerieElectronica = $this->model->getSerieElectronica();
            $numSerieElectronica = ($resultSerieElectronica['total'] == null) ? 1 : $resultSerieElectronica['total'] + 1;
            $serieElectronica = $this->generate_numbers($numSerieElectronica, 1, 9);
            $total12 = 0;
            $total0 = 0;
            $subTotal = 0;
            $total = 0;
            $subTotal = 0;
            $idCliente = $datosContrato['idCliente'];
            $idContrato = $datosContrato['id'];
            $getCliente = $this->model->getCliente($idCliente);
            $descuento12 = 0;
            $descuento0 = 0;
            if ($getCliente['id'] == 1) {
                $tipoIdentificacion = 7;
            } else if ($getCliente['identidad'] == 'CEDULA') {
                $tipoIdentificacion = 5;
            } else {
                $tipoIdentificacion = 4;
            }

                $productos = json_decode($datosContrato['productos'], true);

                foreach ($productos as $totalEncabezado) {
                    //print_r( $totalEncabezado );exit;
                    //print_r($totalEncabezado); exit;
                    //$result = $this->model->getProductoAutomatico( $totalEncabezado[ 'id' ] );
                    //  print_r( $result );

                    if ($totalEncabezado['iva_producto'] == 0) {
                        //$subTotal0 = $totalEncabezado[ 'precio' ] * $totalEncabezado[ 'cantidad' ];
                        $subTotal0 = $totalEncabezado['precio'];
                        $total0 += $subTotal0;
                        $descuento0 = round(($total0 * $descuento) / 100, 2);
                    } else {
                        //$subTotal12 = $totalEncabezado[ 'precio' ] * $totalEncabezado[ 'cantidad' ];
                        $subTotal12 = $totalEncabezado['precio'];
                        $total12 += $subTotal12;
                        $descuento12 = round((($total12 / (CONCAT . $empresa['impuesto'])) * $descuento) / 100, 2);
                        //el descuento se calcula con el impuesto que esta en la configuracion
                    }
                }
                $total = $total12 + $total0;
                $totalDescuento = $descuento12 + $descuento0;


                $ventaEncabezado = $this->model->registrarEncabezado(
                    $fecha,
                    $numSerieElectronica,
                    trim($getCliente['nombre']),
                    trim($getCliente['direccion']),
                    trim($getCliente['telefono']),
                    trim($getCliente['num_identidad']),
                    $tipoIdentificacion,
                    $getCliente['correo'],
                    $empresa['establecimiento'],
                    $empresa['puntoemi'],
                    $empresa['ruc'],
                    AMBIENTE,
                    $empresa['razon_social'],
                    $empresa['nombre'],
                    $numSerieElectronica,
                    $empresa['direccion'],
                    $empresa['contabilidad'],
                    $totalDescuento,
                    $total,
                    '',
                    $estado,
                    $metodo,
                    $this->id_usuario
                );
                if ($ventaEncabezado > 0) {
                    foreach ($productos as $producto) {

                        $result = $this->model->getProducto($producto['id']);
                        //print_r( $result );         exit;
                        $codigo = $result['codigo'];
                        $descripcion = $producto['nombre'] . CONCEPTOMES . $mesActualLetra;
                        //$result[ 'descripcion' ];
                        $iva = $result['iva'];
                        // $precio = $producto[ 'precio_venta' ];
                        //result es lo que trae de la tabla producto y producto es lo que trae de la tabla modificada de ventas
                        $precio = $producto['precio'];
                        //result es lo que trae de la tabla producto y producto es lo que trae de la tabla modificada de ventas
                        if ($iva == 0) {
                            $precio_siniva = $precio;
                        } else {
                            $precio_siniva = round($precio / (CONCAT . $empresa['impuesto']), 4);
                            //LO DIVIDIDO PARA EL PRECIO ES CON EL IVA DEL CONFIGURACION
                        }
                        $cantidad = 1;
                        //$descuento=0;
                        $subTotal = round($precio_siniva * $cantidad, 4);
                        $descuentoDetalle = round(($subTotal * $descuento) / 100, 2);
                        $ventaDetalle = $this->model->registrarDetalle($numSerieElectronica, $cantidad, $descripcion, $precio_siniva, $subTotal, $iva, $codigo, $descuentoDetalle);
                    }
                    if ($ventaDetalle > 0) {
                        foreach ($productos as $producto) {
                            $result = $this->model->getProducto($producto['id']);


                            if ($result['id_categoria'] == 1) {
                                $nuevaCantidad = $result['cantidad'];
                                $totalVentas = $result['ventas'] + $producto['cantidad'];
                                $this->model->actualizarStock($nuevaCantidad, $totalVentas, $result['id']);
                            } else {
                                $nuevaCantidad = $result['cantidad'] - $cantidad;
                                $totalVentas = $result['ventas'] + $cantidad;
                                $this->model->actualizarStock($nuevaCantidad, $totalVentas, $result['id']);
                            }


                            $movimiento = 'Venta Electronica N°: ' . $ventaDetalle;
                            // $cantidad = $producto[ 'cantidad' ];
                            $cantidad = 1;
                            $this->model->registrarMovimiento($movimiento, 'salida', $cantidad, $nuevaCantidad, $result['id'], $this->id_usuario);
                        }
                        if ($metodo == 'CREDITO') {
                            $monto = $total - $descuento;
                            $this->model->registrarCredito($monto, $fecha, $hora, null, $ventaEncabezado, null, $idContrato);
                        }

                        $this->model->actualizarMesContratoF($mesActualLetra, 1, $idContrato, 'UNO');

                        array_push($array['numFacturasElectronica'], $ventaEncabezado);

                        // SRI try/catch automaticas: si falla SOAP, no rompe la respuesta JSON
                        try {
                            $enviarXML = new enviarXML();
                            $claveAcceso = $enviarXML->envioXML($numSerieElectronica, FACTURA);
                            $validacionComprobante = new validacionComprobante();
                            $validacion = $validacionComprobante->validar_comprobante($claveAcceso, FACTURA);
                            $autorizacionComprobante = new autorizacionComprobante();
                            $autorizacion = $autorizacionComprobante->autorizacion_comprobante($claveAcceso, FACTURA);
                        } catch (\Throwable $e) {
                            error_log("SRI automaticas fallo: " . $e->getMessage());
                            $autorizacion = ["numeroComprobantes" => 1, "autorizaciones" => ["autorizacion" => ["estado" => "NO_AUTORIZADO"]]];
                            $claveAcceso = $claveAcceso ?? "";
                        }
                        // $data[ 'claveAcceso' ] = $claveAcceso;
                        $this->model->actualizarClaveAccesso($claveAcceso, $numSerieElectronica);
                        //$result = mysqli_num_rows( $update_clave );

                        if ($autorizacion['numeroComprobantes'] == 0) {
                            $this->envioSriElectronica($numSerieElectronica);
                        } else {
                            if ($autorizacion['autorizaciones']['autorizacion']['estado'] == 'AUTORIZADO') {

                                $facturaElectronica = $this->model->getFacturaElectronica($claveAcceso);

                                $dataInfo = array(
                                    'ruc' => $facturaElectronica['ruc'],
                                    'email' => $facturaElectronica['correo'],
                                    'fecha' => $facturaElectronica['fecha'],
                                    'totalfactura' => $facturaElectronica['totalfactura'],
                                    'cliente' => $facturaElectronica['cliente'],
                                    'claveAcceso' => $claveAcceso,
                                    'empresa' => $empresa['nombre'],
                                    'factura' => $serieElectronica[0],
                                    'enviroment' => ENVIROMENT,
                                    'emailremitente' => $empresa['correo'],
                                    'establecimiento' => $empresa['establecimiento'],
                                    'puntoemi' => $empresa['puntoemi'],
                                    'tipo' => 'factura',
                                    'asunto' => 'Adjuntamos Comprobante Electronico'
                                );
                                // $res = array('msg' => 'FACTURA ELECTRONICA GENERADA EXITOSAMENTE', 'type' => 'success', 'ClaveAcceso' => $claveAcceso, 'factura' => 'electronica', 'idVenta' => $numSerieElectronica);
                                sendEmailAutomatias($dataInfo);
                            }

                        }

                        //print_r( $dataInfo );
                        //  exit;
                    } else {
                        $this->model->deleteFactura('datos_cabecera_electronica', 'orden_no', $numSerieElectronica);
                        $this->model->resetFactura('datos_cabecera_electronica');
                        // $res = array('msg' => 'ERROR AL GENERAR LA FACTURA ELECTRONICA DETALLE ', 'type' => 'error');
                    }
                } else {
                    $this->model->deleteFactura('datos_cabecera_electronica', 'orden_no', $numSerieElectronica);
                    $this->model->resetFactura('datos_cabecera_electronica');
                    // $res = array('msg' => 'ERROR AL GENERAR FACURA ELECTRONICA ENCABEZADO', 'type' => 'error');
                }

                //die();
            
        }

        $this->model->actualizarMesContratoF($mesActualLetra, 0, $idContrato, 'TODOS');
        $this->model->actualizarCorte($fechaCorte, $fechas . ' ' . $horas, 'FACTURA');

        $res = array('msg' => 'FACTURADO' ,'type' => 'success');
        //exit;
        echo json_encode($res, JSON_UNESCAPED_UNICODE);
    }*/

    public function registrarOrdenVentaAutomatico($factura)
    {

        $this->cargarSri();
        $valorFactura = ($factura == 2) ? 0 : 1;

        $campo = ($valorFactura == 1) ? 'FACTURA' : 'ORDENVENTA';
        // print_r($factura); exit;
        $fechaCorte = date('m-Y');
        $fechas = date('Y-m-d');
        $horas = date('H:i:s');
        $datosnumOrden = '';
        $datosFacturasElectronicas = '';
        $mesActualLetra = MESES[date('n')];


        $empresa = $this->model->getEmpresa();
        $getContratosFacturar = $this->model->getContratosFacturar($mesActualLetra, 1, $valorFactura);
        //$getProductos = $this->model->getProductoAutomatico(1);
        // print_r($getContratosFacturar);exit;
        // $idpro =  $getProducto[ 'id' ];
        $productos = array();
        $descuento = 0;
        $countFacturas = 0;
        $countOrdenVentas = 0;
        $array['numOrdenVenta'] = array();
        $array['numFacturasElectronica'] = array();
        //echo $fechamovimiento =  date('F');


        //echo date('c');
        // $fechamovimiento =  "2018-06-05";

        foreach ($getContratosFacturar as $datosContrato) {

            $fecha = date('Y-m-d');
            $hora = date('H:i:s');
            $metodo = 'CREDITO';
            $estado = 2;
            $facturacontrato = $datosContrato['factura'];
            $array['productos'] = array();

            // print_r($datosContrato); exit;

            $resultSerieOrdenVenta = $this->model->getSerieOrdenVenta();
            $numserieOrdenVenta = ($resultSerieOrdenVenta['total'] == null) ? 1 : $resultSerieOrdenVenta['total'] + 1; // factura fisica
            $serieOrdenVenta = $this->generate_numbers($numserieOrdenVenta, 1, 9);

            $resultSerie = $this->model->getSerie();
            $serie = ($resultSerie['total'] == null) ? 1 : $resultSerie['total'] + 1; // factura fisica
            $data['serie'] = $this->generate_numbers($serie, 1, 9);

            // print_r($serie); exit;

            $resultSerieElectronica = $this->model->getSerieElectronica();
            $numSerieElectronica = ($resultSerieElectronica['total'] == null) ? 1 : $resultSerieElectronica['total'] + 1;
            $serieElectronica = $this->generate_numbers($numSerieElectronica, 1, 9);
            $total12 = 0;
            $total0 = 0;
            $subTotal = 0;
            $total = 0;
            $subTotal = 0;
            $idCliente = $datosContrato['idCliente'];
            $idContrato = $datosContrato['id'];
            $getCliente = $this->model->getCliente($idCliente);
            $descuento12 = 0;
            $descuento0 = 0;
            if ($getCliente['id'] == 1) {
                $tipoIdentificacion = 7;
            } else if ($getCliente['identidad'] == 'CEDULA') {
                $tipoIdentificacion = 5;
            } else {
                $tipoIdentificacion = 4;
            }

            if ($facturacontrato == 1) {

                $productos = json_decode($datosContrato['productos'], true);

                foreach ($productos as $totalEncabezado) {
                    //print_r( $totalEncabezado );exit;
                    //print_r($totalEncabezado); exit;
                    //$result = $this->model->getProductoAutomatico( $totalEncabezado[ 'id' ] );
                    //  print_r( $result );

                    if ($totalEncabezado['iva_producto'] == 0) {
                        //$subTotal0 = $totalEncabezado[ 'precio' ] * $totalEncabezado[ 'cantidad' ];
                        $subTotal0 = $totalEncabezado['precio'];
                        $total0 += $subTotal0;
                        $descuento0 = round(($total0 * $descuento) / 100, 2);
                    } else {
                        //$subTotal12 = $totalEncabezado[ 'precio' ] * $totalEncabezado[ 'cantidad' ];
                        $subTotal12 = $totalEncabezado['precio'];
                        $total12 += $subTotal12;
                        $descuento12 = round((($total12 / (CONCAT . $empresa['impuesto'])) * $descuento) / 100, 2);
                        //el descuento se calcula con el impuesto que esta en la configuracion
                    }
                }
                $total = $total12 + $total0;
                $totalDescuento = $descuento12 + $descuento0;


                $ventaEncabezado = $this->model->registrarEncabezado(
                    $fecha,
                    $numSerieElectronica,
                    trim($getCliente['nombre']),
                    trim($getCliente['direccion']),
                    trim($getCliente['telefono']),
                    trim($getCliente['num_identidad']),
                    $tipoIdentificacion,
                    $getCliente['correo'],
                    $empresa['establecimiento'],
                    $empresa['puntoemi'],
                    $empresa['ruc'],
                    AMBIENTE,
                    $empresa['razon_social'],
                    $empresa['nombre'],
                    $numSerieElectronica,
                    $empresa['direccion'],
                    $empresa['contabilidad'],
                    $totalDescuento,
                    $total,
                    '',
                    $estado,
                    $metodo,
                    $this->id_usuario
                );
                if ($ventaEncabezado > 0) {
                    foreach ($productos as $producto) {

                        $result = $this->model->getProducto($producto['id']);
                        //print_r( $result );         exit;
                        $codigo = $result['codigo'];
                        $descripcion = $producto['nombre'] . CONCEPTOMES . $mesActualLetra;
                        //$result[ 'descripcion' ];
                        $iva = $result['iva'];
                        // $precio = $producto[ 'precio_venta' ];
                        //result es lo que trae de la tabla producto y producto es lo que trae de la tabla modificada de ventas
                        $precio = $producto['precio'];
                        //result es lo que trae de la tabla producto y producto es lo que trae de la tabla modificada de ventas
                        if ($iva == 0) {
                            $precio_siniva = $precio;
                        } else {
                            $precio_siniva = round($precio / (CONCAT . $empresa['impuesto']), 4);
                            //LO DIVIDIDO PARA EL PRECIO ES CON EL IVA DEL CONFIGURACION
                        }
                        $cantidad = 1;
                        //$descuento=0;
                        $subTotal = round($precio_siniva * $cantidad, 4);
                        $descuentoDetalle = round(($subTotal * $descuento) / 100, 2);
                        $ventaDetalle = $this->model->registrarDetalle($numSerieElectronica, $cantidad, $descripcion, $precio_siniva, $subTotal, $iva, $codigo, $descuentoDetalle);
                    }
                    if ($ventaDetalle > 0) {
                        foreach ($productos as $producto) {
                            $result = $this->model->getProducto($producto['id']);


                            if ($result['id_categoria'] == 1) {
                                $nuevaCantidad = $result['cantidad'];
                                $totalVentas = $result['ventas'] + $producto['cantidad'];
                                $this->model->actualizarStock($nuevaCantidad, $totalVentas, $result['id']);
                            } else {
                                $nuevaCantidad = $result['cantidad'] - $cantidad;
                                $totalVentas = $result['ventas'] + $cantidad;
                                $this->model->actualizarStock($nuevaCantidad, $totalVentas, $result['id']);
                            }


                            $movimiento = 'Venta Electronica N°: ' . $ventaDetalle;
                            // $cantidad = $producto[ 'cantidad' ];
                            $cantidad = 1;
                            $this->model->registrarMovimiento($movimiento, 'salida', $cantidad, $nuevaCantidad, $result['id'], $this->id_usuario);
                        }
                        if ($metodo == 'CREDITO') {
                            $monto = $total - $descuento;
                            $this->model->registrarCredito($monto, $fecha, $hora, null, $ventaEncabezado, null, $idContrato);
                        }

                        $this->model->actualizarMesContratoF($mesActualLetra, 1, $idContrato, 'UNO');

                        array_push($array['numFacturasElectronica'], $ventaEncabezado);

                        // SRI try/catch automaticas: si falla SOAP, no rompe la respuesta JSON
                        try {
                            $enviarXML = new enviarXML();
                            $claveAcceso = $enviarXML->envioXML($numSerieElectronica, FACTURA);
                            $validacionComprobante = new validacionComprobante();
                            $validacion = $validacionComprobante->validar_comprobante($claveAcceso, FACTURA);
                            $autorizacionComprobante = new autorizacionComprobante();
                            $autorizacion = $autorizacionComprobante->autorizacion_comprobante($claveAcceso, FACTURA);
                        } catch (\Throwable $e) {
                            error_log("SRI automaticas fallo: " . $e->getMessage());
                            $autorizacion = ["numeroComprobantes" => 1, "autorizaciones" => ["autorizacion" => ["estado" => "NO_AUTORIZADO"]]];
                            $claveAcceso = $claveAcceso ?? "";
                        }
                        // $data[ 'claveAcceso' ] = $claveAcceso;
                        $this->model->actualizarClaveAccesso($claveAcceso, $numSerieElectronica);
                        //$result = mysqli_num_rows( $update_clave );

                        if ($autorizacion['numeroComprobantes'] == 0) {
                            $this->envioSriElectronica($numSerieElectronica);
                        } else {
                            if ($autorizacion['autorizaciones']['autorizacion']['estado'] == 'AUTORIZADO') {

                                $facturaElectronica = $this->model->getFacturaElectronica($claveAcceso);

                                $dataInfo = array(
                                    'ruc' => $facturaElectronica['ruc'],
                                    'email' => $facturaElectronica['correo'],
                                    'fecha' => $facturaElectronica['fecha'],
                                    'totalfactura' => $facturaElectronica['totalfactura'],
                                    'cliente' => $facturaElectronica['cliente'],
                                    'claveAcceso' => $claveAcceso,
                                    'empresa' => $empresa['nombre'],
                                    'factura' => $serieElectronica[0],
                                    'enviroment' => ENVIROMENT,
                                    'emailremitente' => $empresa['correo'],
                                    'establecimiento' => $empresa['establecimiento'],
                                    'puntoemi' => $empresa['puntoemi'],
                                    'tipo' => 'factura',
                                    'asunto' => 'Adjuntamos Comprobante Electronico'
                                );
                                // $res = array('msg' => 'FACTURA ELECTRONICA GENERADA EXITOSAMENTE', 'type' => 'success', 'ClaveAcceso' => $claveAcceso, 'factura' => 'electronica', 'idVenta' => $numSerieElectronica);
                                sendEmailAutomatias($dataInfo);
                            }

                        }

                        //print_r( $dataInfo );
                        //  exit;
                    } else {
                        $this->model->deleteFactura('datos_cabecera_electronica', 'orden_no', $numSerieElectronica);
                        $this->model->resetFactura('datos_cabecera_electronica');
                        // $res = array('msg' => 'ERROR AL GENERAR LA FACTURA ELECTRONICA DETALLE ', 'type' => 'error');
                    }
                } else {
                    $this->model->deleteFactura('datos_cabecera_electronica', 'orden_no', $numSerieElectronica);
                    $this->model->resetFactura('datos_cabecera_electronica');
                    // $res = array('msg' => 'ERROR AL GENERAR FACURA ELECTRONICA ENCABEZADO', 'type' => 'error');
                }

                //die();
                $countFacturas += 1;
            } else {

                $productos = json_decode($datosContrato['productos'], true);

                foreach ($productos as $producto) {
                    $result = $this->model->getProducto($producto['id']);
                    $dataOrden['id'] = $result['id'];
                    $dataOrden['nombre'] = $producto['nombre'] . CONCEPTOMES . $mesActualLetra;
                    $dataOrden['precio'] = $producto['precio']; //result es lo que trae de la tabla producto y producto es lo que trae de la tabla modificada de ventas
                    $dataOrden['cantidad'] = $producto['cantidad'];
                    $dataOrden['iva_producto'] = $result['iva'];
                    $dataOrden['codigobarra'] = $result['codigo'];
                    $subTotal = $producto['precio'] * $producto['cantidad'];
                    array_push($array['productos'], $dataOrden);
                    $total += $subTotal;
                    //  print_r($total); exit;
                }
                $datosProductos = json_encode($array['productos'], JSON_UNESCAPED_UNICODE);
                $ordenVenta = $this->model->registrarOrdenVenta($datosProductos, $total, $fecha, $hora, $metodo, $descuento, $serieOrdenVenta[0], $estado, $idCliente, $this->id_usuario, '');
                if ($ordenVenta > 0) {
                    foreach ($productos as $producto) {
                        $result = $this->model->getProducto($producto['id']);
                        //actualizar stock
                        $idProducto = $result['id'];
                        //  print_r($result); exit;
                        if ($result['id_categoria'] == 1) {
                            $nuevaCantidad = $result['cantidad'];
                            $totalVentas = $result['ventas'] + $producto['cantidad'];
                            $this->model->actualizarStock($nuevaCantidad, $totalVentas, $result['id']);
                        } else {
                            $nuevaCantidad = $result['cantidad'] - $producto['cantidad'];
                            $totalVentas = $result['ventas'] + $producto['cantidad'];
                            $this->model->actualizarStock($nuevaCantidad, $totalVentas, $result['id']);
                        }


                        $movimiento = 'Orden Venta N°: ' . $ordenVenta;
                        $cantidad = $producto['cantidad'];
                        $this->model->registrarMovimiento($movimiento, 'salida', $cantidad, $nuevaCantidad, $producto['id'], $this->id_usuario);
                    }
                    if ($metodo == 'CREDITO') {
                        $monto = $total - $descuento;
                        $this->model->registrarCredito($monto, $fecha, $hora, null, null, $ordenVenta, $idContrato);
                    }
                    $this->model->actualizarMesContratoF($mesActualLetra, 1, $idContrato, 'UNO');

                    array_push($array['numOrdenVenta'], $ordenVenta);

                    if ($idProducto != 11) {
                        $this->reporte('facturas', $ordenVenta);
                        $ordenEnvio = $this->model->getOrdenVenta($ordenVenta);
                        $dataInfo = array(
                            'ruc' => $ordenEnvio['num_identidad'],
                            'email' => $ordenEnvio['correo'],
                            'fecha' => $ordenEnvio['fecha'],
                            'totalfactura' => $ordenEnvio['total'],
                            'cliente' => $ordenEnvio['nombre'],
                            'empresa' => $empresa['nombre'],
                            'factura' => $ordenVenta,
                            'enviroment' => ENVIROMENT,
                            'emailremitente' => $empresa['correo'],
                            'establecimiento' => $empresa['establecimiento'],
                            'puntoemi' => $empresa['puntoemi'],
                            'asunto' => 'Adjuntamos Comprobante'
                        );
                        // $res = array('msg' => 'FACTURA ELECTRONICA GENERADA EXITOSAMENTE', 'type' => 'success', 'ClaveAcceso' => $claveAcceso, 'factura' => 'electronica', 'idVenta' => $numSerieElectronica);
                        sendEmailOrdenAutomaticas($dataInfo);
                    }

                    // $array['numOrdenVenta'] = $ordenVenta;

                    //die();
                    $countOrdenVentas += 1;
                    //$res = array('msg' => 'ORDEN VENTA GENERADA EXITOSAMENTE', 'type' => 'success', 'idVenta' => $ordenVenta, 'factura' => 'fisica');
                }


            }
        }
        // $datosOrdenVenta = json_encode($array['numOrdenVenta'], JSON_UNESCAPED_UNICODE);
        $datosnumOrden = json_encode($array['numOrdenVenta'], JSON_UNESCAPED_UNICODE);
        $datosFacturasElectronicas = json_encode($array['numFacturasElectronica'], JSON_UNESCAPED_UNICODE);

        // print_r($datosnumOrden); 
        //print_r($datosFacturasElectronicas); 
        $this->model->actualizarMesContratoF($mesActualLetra, 0, $idContrato, 'TODOS');
        //$this->model->actualizarEstadoCorte(0);
        $this->model->actualizarCorte($fechaCorte, $fechas . ' ' . $horas, $campo);


        $res = array('msg' => $countFacturas . ' FACTURAS GENERADAS EXITOSAMENTE Y ' . $countOrdenVentas . ' ORDEN VENTA GENRADAS EXITOSAMENTE', 'type' => 'success', 'OrdenesVenta' => $datosnumOrden, 'FacturasElectronicas' => $datosFacturasElectronicas);
        //exit;
        echo json_encode($res, JSON_UNESCAPED_UNICODE);
    }
    public function facturarContrato()
    {

        $this->cargarSri();
        $json = file_get_contents('php://input');
        $datos = json_decode($json, true);
        $array['productos'] = array();
        $total = 0;


        $mesFacturar = date('m');
        $datosnumOrden = '';
        $datosFacturasElectronicas = '';
        $mesActualLetra = MESES[date('n')];

        $idContrato = $datos['idContrato'];
        $totalFacturar = $datos['total'];
        $tipoPago = $datos['tipoPago'];


        //print_r($datos); exit;
        $arrayMeses = array('ENERO' => $datos['enero'], 'FEBRERO' => $datos['febrero'], 'MARZO' => $datos['marzo'], 'ABRIL' => $datos['abril'], 'MAYO' => $datos['mayo'], 'JUNIO' => $datos['junio'], 'JULIO' => $datos['julio'], 'AGOSTO' => $datos['agosto'], 'SEPTIEMBRE' => $datos['septiembre'], 'OCTUBRE' => $datos['octubre'], 'NOVIEMBRE' => $datos['noviembre'], 'DICIEMBRE' => $datos['diciembre']);

        $mesesSeleccionado = '';
        foreach ($arrayMeses as $mes => $valor) {
            if ($valor == 1) {
                $mesesSeleccionado .= $mes . '-';
            }

        }

        $enero = ($datos['enero'] == 0) ? 0 : 1;
        $febrero = ($datos['febrero'] == 0) ? 0 : 1;
        $marzo = ($datos['marzo'] == 0) ? 0 : 1;
        $abril = ($datos['abril'] == 0) ? 0 : 1;
        $mayo = ($datos['mayo'] == 0) ? 0 : 1;
        $junio = ($datos['junio'] == 0) ? 0 : 1;
        $julio = ($datos['julio'] == 0) ? 0 : 1;
        $agosto = ($datos['agosto'] == 0) ? 0 : 1;
        $septiembre = ($datos['septiembre'] == 0) ? 0 : 1;
        $octubre = ($datos['octubre'] == 0) ? 0 : 1;
        $noviembre = ($datos['noviembre'] == 0) ? 0 : 1;
        $diciembre = ($datos['diciembre'] == 0) ? 0 : 1;
        //   print_r($mesesSeleccionado); exit;

        $empresa = $this->model->getEmpresa();
        $datosContrato = $this->model->getContratoFacturar($idContrato, 1);
        //$getProductos = $this->model->getProductoAutomatico(1);
        //  print_r($datosContrato);exit;
        // $idpro =  $getProducto[ 'id' ];
        $productos = array();
        $descuento = 0;
        $countFacturas = 0;
        $countOrdenVentas = 0;
        $array['numOrdenVenta'] = array();
        $array['numFacturasElectronica'] = array();
        //echo $fechamovimiento =  date('F');

        //  print_r($datos); exit;
        //echo date('c');
        // $fechamovimiento =  "2018-06-05";

        // print_r($datosContrato); exit;

        $fecha = date('Y-m-d');
        $hora = date('H:i:s');
        $metodo = 'CONTADO';
        $estado = 1;
        $facturacontrato = $datosContrato['factura'];
        $array['productos'] = array();

        // print_r($datos); exit;

        $resultSerieOrdenVenta = $this->model->getSerieOrdenVenta();
        $numserieOrdenVenta = ($resultSerieOrdenVenta['total'] == null) ? 1 : $resultSerieOrdenVenta['total'] + 1; // factura fisica
        $serieOrdenVenta = $this->generate_numbers($numserieOrdenVenta, 1, 9);

        //$resultSerie = $this->model->getSerie();
       // $serie = ($resultSerie['total'] == null) ? 1 : $resultSerie['total'] + 1; // factura fisica
       // $data['serie'] = $this->generate_numbers($serie, 1, 9);

        // print_r($serie); exit;

        $resultSerieElectronica = $this->model->getSerieElectronica();
        $numSerieElectronica = ($resultSerieElectronica['total'] == null) ? 1 : $resultSerieElectronica['total'] + 1;
        $serieElectronica = $this->generate_numbers($numSerieElectronica, 1, 9);
        $total12 = 0;
        $total0 = 0;
        $subTotal = 0;
        $total = 0;
        $subTotal = 0;
        $idCliente = $datosContrato['idCliente'];
        $idContrato = $datosContrato['id'];
        $getCliente = $this->model->getCliente($idCliente);
        $descuento12 = 0;
        $descuento0 = 0;
        if ($getCliente['id'] == 1) {
            $tipoIdentificacion = 7;
        } else if ($getCliente['identidad'] == 'CEDULA') {
            $tipoIdentificacion = 5;
        } else {
            $tipoIdentificacion = 4;
        }
        $total = $totalFacturar;

        if ($facturacontrato == 1) {

            $productos = json_decode($datosContrato['productos'], true);
            /*
                foreach ($productos as $totalEncabezado) {
                    //print_r( $totalEncabezado );exit;
                    //print_r($totalEncabezado); exit;
                    //$result = $this->model->getProductoAutomatico( $totalEncabezado[ 'id' ] );
                    //  print_r( $result );

                    if ($totalEncabezado['iva_producto'] == 0) {
                        //$subTotal0 = $totalEncabezado[ 'precio' ] * $totalEncabezado[ 'cantidad' ];
                        $subTotal0 = $totalEncabezado['precio'];
                        $total0 += $subTotal0;
                        $descuento0 = round(($total0 * $descuento) / 100, 2);
                    } else {
                        //$subTotal12 = $totalEncabezado[ 'precio' ] * $totalEncabezado[ 'cantidad' ];
                        $subTotal12 = $totalEncabezado['precio'];
                        $total12 += $subTotal12;
                        $descuento12 = round((($total12 / (CONCAT . $empresa['impuesto']))  * $descuento) / 100, 2);
                        //el descuento se calcula con el impuesto que esta en la configuracion
                    }
                }*/
            //$total = $total12 + $total0;
            //$totalDescuento = $descuento12 + $descuento0;
            $totalDescuento = 0;

            $ventaEncabezado = $this->model->registrarEncabezado(
                $fecha,
                $numSerieElectronica,
                $getCliente['nombre'],
                $getCliente['direccion'],
                $getCliente['telefono'],
                $getCliente['num_identidad'],
                $tipoIdentificacion,
                $getCliente['correo'],
                $empresa['establecimiento'],
                $empresa['puntoemi'],
                $empresa['ruc'],
                AMBIENTE,
                $empresa['razon_social'],
                $empresa['nombre'],
                $numSerieElectronica,
                $empresa['direccion'],
                $empresa['contabilidad'],
                $totalDescuento,
                $total,
                $tipoPago,
                $estado,
                $metodo,
                $this->id_usuario,
                $idCliente
            );
            if ($ventaEncabezado > 0) {
                foreach ($productos as $producto) {

                    $result = $this->model->getProducto($producto['id']);
                    //print_r( $result );         exit;
                    $codigo = $result['codigo'];
                    $descripcion = $producto['nombre'] . CONCEPTOMES . $mesesSeleccionado;
                    //$result[ 'descripcion' ];
                    $iva = $result['iva'];
                    // $precio = $producto[ 'precio_venta' ];
                    //result es lo que trae de la tabla producto y producto es lo que trae de la tabla modificada de ventas
                    // $precio = $producto['precio'];
                    $precio = $totalFacturar;
                    //result es lo que trae de la tabla producto y producto es lo que trae de la tabla modificada de ventas


                    if ($iva == 0) {
                        $precio_siniva = $precio;
                    } else {
                        $precio_siniva = round($precio / (CONCAT . $empresa['impuesto']), 4);
                        //LO DIVIDIDO PARA EL PRECIO ES CON EL IVA DEL CONFIGURACION
                    }



                    $cantidad = 1;
                    //$descuento=0;
                    $subTotal = round($precio_siniva * $cantidad, 4);
                    $descuentoDetalle = round(($subTotal * $descuento) / 100, 2);
                    $ventaDetalle = $this->model->registrarDetalle($numSerieElectronica, $cantidad, $descripcion, $precio_siniva, $subTotal, $iva, $codigo, $descuentoDetalle,$totalFacturar,0,$producto['id']);
                }
                if ($ventaDetalle > 0) {
                    foreach ($productos as $producto) {
                        $result = $this->model->getProducto($producto['id']);

                        if ($result['id_categoria'] == 1) {
                            $nuevaCantidad = $result['cantidad'];
                            $totalVentas = $result['ventas'] + $producto['cantidad'];
                            $this->model->actualizarStock($nuevaCantidad, $totalVentas, $result['id']);
                        } else {
                            $nuevaCantidad = $result['cantidad'] - $cantidad;
                            $totalVentas = $result['ventas'] + $cantidad;
                            $this->model->actualizarStock($nuevaCantidad, $totalVentas, $result['id']);
                        }


                        $movimiento = 'Venta Electronica N°: ' . $ventaDetalle;
                        // $cantidad = $producto[ 'cantidad' ];
                        $cantidad = 1;
                        $this->model->registrarMovimiento($movimiento, 'salida', $cantidad, $nuevaCantidad, $result['id'], $this->id_usuario);
                    }
                    /*  if ($metodo == 'CREDITO') {
                          $monto = $total - $descuento;
                          $this->model->registrarCredito($monto, $fecha, $hora, null, $ventaEncabezado, null, $idContrato);
                      }*/

                    $this->model->actualizarMesContrato($enero, $febrero, $marzo, $abril, $mayo, $junio, $julio, $agosto, $septiembre, $octubre, $noviembre, $diciembre, $idContrato);
                    array_push($array['numFacturasElectronica'], $ventaEncabezado);

                    // SRI try/catch automaticas: si falla SOAP, no rompe la respuesta JSON
                    try {
                        $enviarXML = new enviarXML();
                        $claveAcceso = $enviarXML->envioXML($numSerieElectronica, FACTURA);
                        $validacionComprobante = new validacionComprobante();
                        $validacion = $validacionComprobante->validar_comprobante($claveAcceso, FACTURA);
                        $autorizacionComprobante = new autorizacionComprobante();
                        $autorizacion = $autorizacionComprobante->autorizacion_comprobante($claveAcceso, FACTURA);
                    } catch (\Throwable $e) {
                        error_log("SRI automaticas fallo: " . $e->getMessage());
                        $autorizacion = ["numeroComprobantes" => 1, "autorizaciones" => ["autorizacion" => ["estado" => "NO_AUTORIZADO"]]];
                        $claveAcceso = $claveAcceso ?? "";
                    }
                    //  print_r( $autorizacion[ 'autorizaciones' ][ 'autorizacion' ][ 'estado' ] );          exit;
                    // $data[ 'claveAcceso' ] = $claveAcceso;
                    $this->model->actualizarClaveAccesso($claveAcceso, $numSerieElectronica);

                    //$result = mysqli_num_rows( $update_clave );
                    if ($autorizacion['autorizaciones']['autorizacion']['estado'] == 'AUTORIZADO') {

                        $facturaElectronica = $this->model->getFacturaElectronica($claveAcceso);

                        $dataInfo = array(
                            'ruc' => $facturaElectronica['ruc'],
                            'email' => $facturaElectronica['correo'],
                            'fecha' => $facturaElectronica['fecha'],
                            'totalfactura' => $facturaElectronica['totalfactura'],
                            'cliente' => $facturaElectronica['cliente'],
                            'claveAcceso' => $claveAcceso,
                            'empresa' => $empresa['nombre'],
                            'factura' => $serieElectronica[0],
                            'enviroment' => ENVIROMENT,
                            'emailremitente' => $empresa['correo'],
                            'establecimiento' => $empresa['establecimiento'],
                            'puntoemi' => $empresa['puntoemi'],
                            'tipo' => 'factura',
                            'asunto' => 'Adjuntamos Comprobante Electronico'
                        );

                        if ($facturaElectronica['telefono'] == '') {

                            $resWathsapp = null;
                        } else {
                            //$resWathsapp = 'https://web.whatsapp.com/send?text=Buen Dia estimado cliente *MEGAHNET*! Su deuda Total es de: ' . '$ ' . $restante .', INCLUIDO SERVICIO DE *' . $mesActualLetra . '* &phone=+593' . $getInfoClientes['telefono'] . '&abid=+593' . $getInfoClientes['telefono'] . '';
                            $resWathsapp = 'https://web.whatsapp.com/send?phone=593' . $facturaElectronica['telefono'] . '&text=Buen%20d%C3%ADa%20estimado%2Fa%20cliente%0A%20%20%20%20%20%20%20%20%20%20*MEGAHNET*%0A%20%20%20*GRACIAS%20POR%20SU%20PAGO*%0A%0A%20%20su%20saldo%20a%20la%20fecha%20es%3A%0A%20%20%20%20%20%20%20%20%20%20%20%20%240.00%0Aincluido%20*SERVICIO%20' . $mesesSeleccionado . '*%0A*' . $facturaElectronica['cliente'] . '*';
                        }
                        $res = array('msg' => 'FACTURA ELECTRONICA GENERADA EXITOSAMENTE', 'type' => 'success', 'ClaveAcceso' => $claveAcceso, 'factura' => 'electronica', 'idVenta' => $numSerieElectronica, 'whatsapp' => $resWathsapp);
                        // Alerta admin: cliente sin correo registrado
                        if (empty($dataInfo['email']) || !filter_var($dataInfo['email'], FILTER_VALIDATE_EMAIL)) {
                            try {
                                $cuerpo = '<p>El comprobante <b>' . htmlspecialchars($dataInfo['factura'] ?? '') . '</b> fue <b>AUTORIZADO</b> por el SRI pero <b>no se pudo enviar al cliente</b> porque no tiene correo registrado.</p>'
                                    . '<table border=0 cellpadding=4 style="border-collapse:collapse;">'
                                    . '<tr><td><b>Cliente:</b></td><td>' . htmlspecialchars($dataInfo['cliente'] ?? '') . '</td></tr>'
                                    . '<tr><td><b>RUC/Cedula:</b></td><td>' . htmlspecialchars($dataInfo['ruc'] ?? '') . '</td></tr>'
                                    . '<tr><td><b>Total:</b></td><td>$' . number_format((float)($dataInfo['totalfactura'] ?? 0), 2) . '</td></tr>'
                                    . '<tr><td><b>Clave acceso:</b></td><td><code>' . htmlspecialchars($dataInfo['claveAcceso'] ?? '') . '</code></td></tr>'
                                    . '<tr><td><b>Fecha:</b></td><td>' . htmlspecialchars($dataInfo['fecha'] ?? '') . '</td></tr>'
                                    . '</table>'
                                    . '<p>Por favor entrega manualmente el RIDE/XML al cliente o solicita su correo electronico.</p>';
                                enviarAlertaAdmin('CLIENTE_SIN_CORREO', 'Cliente sin correo - Factura ' . ($dataInfo['factura'] ?? ''), $cuerpo);
                            } catch (\Throwable $e) { error_log('Alerta sin correo fallo: ' . $e->getMessage()); }
                        }
                        try { sendEmail($dataInfo, 'email_facturaelectronica', 'ventas'); } catch (\Throwable $e) { error_log('sendEmail fallo: ' . $e->getMessage()); }
                    } else {
                        $msgSri = 'ERROR EN LA FACTURA';
                                                $estadoSri = $autorizacion['autorizaciones']['autorizacion']['estado'] ?? '';
                                                $mens = $autorizacion['autorizaciones']['autorizacion']['mensajes']['mensaje'] ?? null;
                                                if ($mens) {
                                                    $lista = isset($mens['identificador']) ? [$mens] : (is_array($mens) ? $mens : []);
                                                    $detalles = [];
                                                    foreach ($lista as $m) {
                                                        if (is_array($m)) {
                                                            $detalles[] = trim(($m['mensaje'] ?? '') . ' ' . ($m['informacionAdicional'] ?? ''));
                                                        }
                                                    }
                                                    if ($detalles) $msgSri .= ' (' . $estadoSri . '): ' . implode(' | ', $detalles);
                                                } else if ($estadoSri) {
                                                    $msgSri .= ' (' . $estadoSri . ')';
                                                }
                                                error_log('SRI rechazo en ' . __FILE__ . ': clave=' . ($claveAcceso ?? '') . ' estado=' . $estadoSri . ' detalle=' . json_encode($mens, JSON_UNESCAPED_UNICODE));
                                                $res = array('msg' => $msgSri, 'type' => 'error');
                    }
                    //print_r( $dataInfo );
                    //  exit;
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

            //die();
            // $countFacturas += 1;
        } else {

            $productos = json_decode($datosContrato['productos'], true);

            foreach ($productos as $producto) {
                $result = $this->model->getProducto($producto['id']);
                $dataOrden['id'] = $result['id'];
                $dataOrden['nombre'] = $producto['nombre'] . CONCEPTOMES . $mesesSeleccionado;
                $dataOrden['precio'] = $totalFacturar; //result es lo que trae de la tabla producto y producto es lo que trae de la tabla modificada de ventas
                $dataOrden['cantidad'] = $producto['cantidad'];
                $dataOrden['iva_producto'] = $result['iva'];
                $dataOrden['codigobarra'] = $result['codigo'];
                array_push($array['productos'], $dataOrden);

            }
            //  print_r($array['productos']); exit;

            $datosProductos = json_encode($array['productos'], JSON_UNESCAPED_UNICODE);
            $ordenVenta = $this->model->registrarOrdenVenta($datosProductos, $total, $fecha, $hora, $metodo, $descuento, $serieOrdenVenta[0], $estado, $idCliente, $this->id_usuario, $tipoPago);
            if ($ordenVenta > 0) {
                foreach ($productos as $producto) {
                    $result = $this->model->getProducto($producto['id']);
                    //actualizar stock

                    if ($result['id_categoria'] == 1) {
                        $nuevaCantidad = $result['cantidad'];
                        $totalOrdenVenta = $result['ventas'] + $producto['cantidad'];
                        $this->model->actualizarStock($nuevaCantidad, $totalOrdenVenta, $result['id']);
                    } else {
                        $nuevaCantidad = $result['cantidad'] - $producto['cantidad'];
                        $totalOrdenVenta = $result['ventas'] + $producto['cantidad'];
                        $this->model->actualizarStock($nuevaCantidad, $totalOrdenVenta, $result['id']);
                    }




                    $movimiento = 'Orden Venta N°: ' . $ordenVenta;
                    $cantidad = $producto['cantidad'];
                    $this->model->registrarMovimiento($movimiento, 'salida', $cantidad, $nuevaCantidad, $producto['id'], $this->id_usuario);
                }
                /*  if ($metodo == 'CREDITO') {
                      $monto = $total - $descuento;
                      $this->model->registrarCredito($monto, $fecha, $hora, null, null, $ordenVenta, $idContrato);
                  }*/
                $this->model->actualizarMesContrato($enero, $febrero, $marzo, $abril, $mayo, $junio, $julio, $agosto, $septiembre, $octubre, $noviembre, $diciembre, $idContrato);
                array_push($array['numOrdenVenta'], $ordenVenta);

                $this->ordenVentaPDF('facturas', $ordenVenta);

                $getordenVenta = $this->model->getOrdenVenta($ordenVenta);
                $dataInfo = array(
                    'ruc' => $getordenVenta['num_identidad'],
                    'email' => $getordenVenta['correo'],
                    'fecha' => $getordenVenta['fecha'],
                    'totalfactura' => $getordenVenta['total'],
                    'cliente' => $getordenVenta['nombre'],
                    'empresa' => $empresa['nombre'],
                    'factura' => $ordenVenta,
                    'enviroment' => ENVIROMENT,
                    'emailremitente' => $empresa['correo'],
                    'establecimiento' => $empresa['establecimiento'],
                    'puntoemi' => $empresa['puntoemi'],
                    'tipo' => 'orden',
                    'asunto' => 'Adjuntamos Comprobante'
                );

                if ($getordenVenta['telefono'] == '') {

                    $resWathsapp = null;
                } else {
                    //$resWathsapp = 'https://web.whatsapp.com/send?text=Buen Dia estimado cliente *MEGAHNET*! Su deuda Total es de: ' . '$ ' . $restante .', INCLUIDO SERVICIO DE *' . $mesActualLetra . '* &phone=+593' . $getInfoClientes['telefono'] . '&abid=+593' . $getInfoClientes['telefono'] . '';
                    $resWathsapp = 'https://web.whatsapp.com/send?phone=593' . $getordenVenta['telefono'] . '&text=Buen%20d%C3%ADa%20estimado%2Fa%20cliente%0A%20%20%20%20%20%20%20%20%20%20*MEGAHNET*%0A%20%20%20*GRACIAS%20POR%20SU%20PAGO*%0A%0A%20%20su%20saldo%20a%20la%20fecha%20es%3A%0A%20%20%20%20%20%20%20%20%20%20%20%20%240.00%0Aincluido%20*SERVICIO%20' . $mesesSeleccionado . '*%0A*' . $getordenVenta['nombre'] . '*';
                }

                $res = array('msg' => 'ORDEN VENTA GENERADA EXITOSAMENTE', 'type' => 'success', 'idVenta' => $ordenVenta, 'factura' => 'ordenVenta', 'whatsapp' => $resWathsapp);
                sendEmailOrden($dataInfo, 'email_facturaelectronica');

            } else {
                $res = array('msg' => 'ERROR AL GENERAR ORDEN VENTA', 'type' => 'error');
            }

            //$array['numOrdenVenta'] = $ordenVenta

            //die();
            // $countOrdenVentas += 1;
        }

        // $datosOrdenVenta = json_encode($array['numOrdenVenta'], JSON_UNESCAPED_UNICODE);
        // $datosnumOrden = json_encode($array['numOrdenVenta'], JSON_UNESCAPED_UNICODE);
        // $datosFacturasElectronicas = json_encode($array['numFacturasElectronica'], JSON_UNESCAPED_UNICODE);

        // print_r($datosnumOrden); 
        //print_r($datosFacturasElectronicas); 


        //$res = array('msg' => 'FACTURA GENERADA EXITOSAMENTE', 'type' => 'success', 'Ordene' => $datosnumOrden, 'Factura' => $datosFacturasElectronicas);
        //exit;
        echo json_encode($res, JSON_UNESCAPED_UNICODE);
    }

    public function reporte($tipo, $idOrdenVenta)
    {
        $this->cargarSri();
        ob_start();
        // $array = explode(',', $datos);
        //$tipo = $array[0];
        //$idOrdenVenta = $array[1];
        $rutaGuardado = 'facturaelectronica/public/archivos/facturables/';

        $nombreArchivo = 'Facturable' . '_' . $idOrdenVenta . '.pdf';

        $data['title'] = 'Orden Venta';
        $data['empresa'] = $this->model->getEmpresa();
        $data['ordenventa'] = $this->model->getOrdenVenta($idOrdenVenta);
        //print_r( $data['cotizacion']); exit;
        if (empty($data['ordenventa'])) {
            echo 'Pagina no Encontrada';
            exit;
        }
        $this->views->getView('automaticas', $tipo, $data);
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



        $dompdf->render();
        $output = $dompdf->output();
        file_put_contents($rutaGuardado . $nombreArchivo, $output);
        // Output the generated PDF to Browser
        //$dompdf->stream('Facturable' . $idOrdenVenta . '.pdf', array('Attachment' => false));
    }

    public function facturaTicked($datos)
    {
        $this->cargarSri();
        ob_start();
        $array = explode(',', $datos);
        $claveAcceso = $array[0];
        $idVenta = $array[1];

        $data['empresa'] = $this->model->getEmpresa();
        $data['factura'] = $this->model->getFacturaElectronica($claveAcceso);
        $data['result_detalle'] = $this->model->getFacturaElectronicaDetalle($idVenta);

        if (empty($data['factura'])) {
            echo 'Pagina no Encontrada';
            exit;
        }
        $this->views->getView('ventas', 'facturaticket', $data);
        $html = ob_get_clean();
        $dompdf = new Dompdf();
        $options = $dompdf->getOptions();
        $options->set('isJavascriptEnabled', true);
        $options->set('isRemoteEnabled', true);
        $dompdf->setOptions($options);
        $dompdf->loadHtml($html);

        $dompdf->setPaper(array(0, 0, 255, 800), 'portrait');

        // Render the HTML as PDF
        $dompdf->render();

        // Output the generated PDF to Browser
        $dompdf->stream('Factura.pdf', array('Attachment' => false));
    }

    public function ReporteContratos($datos)
    {
        $this->cargarSri();
        ob_start();
        // $array = explode(',', $datos);
        //$datosOrdenVentas = $array[0];
        //$datosFacturasElectronicas = $array[1];
        $ordenes = json_decode($_GET['orden']);
        $facturas = json_decode($_GET['facturas']);
        // print_r(  $ordenes); exit;
        $totalOrdenes = 0;
        $totalFacturas = 0;
        $array['datosFacturas'] = array();
        $array['datosOrdenes'] = array();
        $datosInfoOrdenes = '';
        $datosInfoFacturas = '';
        // print_r($ordenes); exit;

        foreach ($ordenes as $ordene) {
            $getInfoOrden = $this->model->getDatosImprimirOrdenes($ordene);
            array_push($array['datosOrdenes'], $getInfoOrden);
            $datosInfoOrdenes = json_encode($array['datosOrdenes'], JSON_UNESCAPED_UNICODE);

            $getDatosOrden = $this->model->getDatosOrdenVenta($ordene);
            $totalOrdenes += number_format($getDatosOrden['total'], 2, '.', ',');
        }

        foreach ($facturas as $factura) {
            //  print_r($ordene); exit;
            $getInfoFacturas = $this->model->getDatosImprimirFacturas($factura);
            array_push($array['datosFacturas'], $getInfoFacturas);
            $datosInfoFacturas = json_encode($array['datosFacturas'], JSON_UNESCAPED_UNICODE);

            $getDatosfacturas = $this->model->getDatosFacturas($factura);
            // print_r($getDatosfacturas); exit;

            $totalFacturas += number_format($getDatosfacturas['total'], 2, '.', ',');
        }

        // print_r($totalOrdenes); 
        //print_r($totalFacturas);


        $data['empresa'] = $this->model->getEmpresa();
        $data['totalOrdenes'] = $totalOrdenes;
        $data['totalFacturas'] = $totalFacturas;
        $data['idOrdenes'] = $ordenes;
        $data['idFacturas'] = $facturas;

        $data['infoOrdenes'] = $datosInfoOrdenes;
        $data['infoFacturas'] = $datosInfoFacturas;

        if (empty($data['idOrdenes']) && empty($data['idFacturas'])) {
            echo 'Pagina no Encontrada';
            exit;
        }
        $this->views->getView('automaticas', 'factura', $data);
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
        $dompdf->stream('Reportes.pdf', array('Attachment' => false));
    }

    public function listarElectronica()
    {
        $mesFacturar = date('m');
        $mesActualLetra = MESES[date('n')];

        // print_r($mesActualLetra);   exit;
        //echo date("m", strtotime("-1 months")); exit;
        $data = $this->model->getContratosFacturar($mesActualLetra, 1, 1);
        //$getClientes = $this->model->getClientes( $fecha );
        //$data['fecha'] = date('d/m/Y');
        //$getClientes[ 'tributario' ] = '';
        //print_r($data );
        //exit;
        for ($i = 0; $i < count($data); $i++) {

            if ($data[$i]['estado'] == 1) {
                $data[$i]['estado'] = '<div style="text-align: center;"><span class="badge bg-success">ACTIVO</span></div>';
            } else if ($data[$i]['estado'] == 2) {
                $data[$i]['estado'] = '<div style="text-align: center;"><span class="badge bg-warning">PENDIENTE</span></div>';
            }

            if ($data[$i]['factura'] == 1) {
                $data[$i]['tributario'] = '<span style="justify-content: center;
                display: flex;
                margin: auto;" class="badge bg-success">FACTURA</span>';
            } else {
                $data[$i]['tributario'] = '<span style="justify-content: center;
                display: flex;
                margin: auto;" class="badge bg-warning">ORDEN VENTA</span>';
            }


            $data[$i]['fecha'] = date('d-m-Y');

            //$data[ $i ][ 'factura' ] = $this->generate_numbers( $data[ $i ][ 'orden_no' ], 1, 9 );
        }
        echo json_encode($data, JSON_UNESCAPED_UNICODE);
        die();
    }


    public function listarOrdenVenta()
    {
        $mesFacturar = date('m');
        $mesActualLetra = MESES[date('n')];

        // print_r($mesActualLetra);   exit;
        //echo date("m", strtotime("-1 months")); exit;
        $data = $this->model->getContratosFacturar($mesActualLetra, 1, 0);
        //$getClientes = $this->model->getClientes( $fecha );
        //$data['fecha'] = date('d/m/Y');
        //$getClientes[ 'tributario' ] = '';
        //print_r($data );
        //exit;
        for ($i = 0; $i < count($data); $i++) {

            if ($data[$i]['estado'] == 1) {
                $data[$i]['estado'] = '<div style="text-align: center;"><span class="badge bg-success">ACTIVO</span></div>';
            } else if ($data[$i]['estado'] == 2) {
                $data[$i]['estado'] = '<div style="text-align: center;"><span class="badge bg-warning">PENDIENTE</span></div>';
            }

            if ($data[$i]['factura'] == 1) {
                $data[$i]['tributario'] = '<span style="justify-content: center;
                display: flex;
                margin: auto;" class="badge bg-success">FACTURA</span>';
            } else {
                $data[$i]['tributario'] = '<span style="justify-content: center;
                display: flex;
                margin: auto;" class="badge bg-warning">ORDEN VENTA</span>';
            }


            $data[$i]['fecha'] = date('d-m-Y');

            //$data[ $i ][ 'factura' ] = $this->generate_numbers( $data[ $i ][ 'orden_no' ], 1, 9 );
        }
        echo json_encode($data, JSON_UNESCAPED_UNICODE);
        die();
    }


    //REENVIO DE FACTURA ELECTRONICA

    public function envioFacturaElectronica($idVenta)
    {
        $this->cargarSri();
        $empresa = $this->model->getEmpresa();

        $facturaElectronica = $this->model->getVentaElectronica($idVenta);

        $claveAcceso = $facturaElectronica['0']['claveacceso'];
        //print_r( $facturaElectronica );        exit;
        $filePDF = 'facturaelectronica/public/archivos/ride/' . $claveAcceso . '.pdf';
        $fileXML = 'facturaelectronica/public/archivos/autorizados/' . $claveAcceso . '.xml';

        if (file_exists($filePDF) && file_exists($fileXML)) {
            $serieElectronica = $this->generate_numbers($facturaElectronica['0']['orden_no'], 1, 9);

            $dataInfo = array(
                'ruc' => $facturaElectronica['0']['ruc'],
                'email' => $facturaElectronica['0']['correo'],
                'fecha' => $facturaElectronica['0']['fecha'],
                'totalfactura' => $facturaElectronica['0']['totalfactura'],
                'cliente' => $facturaElectronica['0']['cliente'],
                'claveAcceso' => $claveAcceso,
                'empresa' => $empresa['nombre'],
                'factura' => $serieElectronica[0],
                'enviroment' => ENVIROMENT,
                'emailremitente' => $empresa['correo'],
                'establecimiento' => $empresa['establecimiento'],
                'puntoemi' => $empresa['puntoemi'],
                'tipo' => 'factura',
                'asunto' => 'Adjuntamos Comprobante Electronico'
            );
            $res = array('msg' => 'FACTURA ELECTRONICA ENVIADA EXITOSAMENTE', 'type' => 'success');
            // Alerta admin: cliente sin correo registrado
            if (empty($dataInfo['email']) || !filter_var($dataInfo['email'], FILTER_VALIDATE_EMAIL)) {
                try {
                    $cuerpo = '<p>El comprobante <b>' . htmlspecialchars($dataInfo['factura'] ?? '') . '</b> fue <b>AUTORIZADO</b> por el SRI pero <b>no se pudo enviar al cliente</b> porque no tiene correo registrado.</p>'
                        . '<table border=0 cellpadding=4 style="border-collapse:collapse;">'
                        . '<tr><td><b>Cliente:</b></td><td>' . htmlspecialchars($dataInfo['cliente'] ?? '') . '</td></tr>'
                        . '<tr><td><b>RUC/Cedula:</b></td><td>' . htmlspecialchars($dataInfo['ruc'] ?? '') . '</td></tr>'
                        . '<tr><td><b>Total:</b></td><td>$' . number_format((float)($dataInfo['totalfactura'] ?? 0), 2) . '</td></tr>'
                        . '<tr><td><b>Clave acceso:</b></td><td><code>' . htmlspecialchars($dataInfo['claveAcceso'] ?? '') . '</code></td></tr>'
                        . '<tr><td><b>Fecha:</b></td><td>' . htmlspecialchars($dataInfo['fecha'] ?? '') . '</td></tr>'
                        . '</table>'
                        . '<p>Por favor entrega manualmente el RIDE/XML al cliente o solicita su correo electronico.</p>';
                    enviarAlertaAdmin('CLIENTE_SIN_CORREO', 'Cliente sin correo - Factura ' . ($dataInfo['factura'] ?? ''), $cuerpo);
                } catch (\Throwable $e) { error_log('Alerta sin correo fallo: ' . $e->getMessage()); }
            }
            try { sendEmail($dataInfo, 'email_facturaelectronica', 'ventas'); } catch (\Throwable $e) { error_log('sendEmail fallo: ' . $e->getMessage()); }
        } else {
            $res = array('msg' => 'ERROR AL ENVIAR FACTURA ELECTRONICA', 'type' => 'error');
        }

        echo json_encode($res);
        die();
    }

    //REENVIO DE FACTURA ELECTRONICA AL SRI

    public function envioSriElectronica($idVenta)
    {
        $this->cargarSri();
        $empresa = $this->model->getEmpresa();

        $facturaElectronica = $this->model->getVentaElectronica($idVenta);
        //$claveAcceso = $facturaElectronica[ '0' ][ 'claveacceso' ];

        $enviarXML = new enviarXML();
        $claveAcceso = $enviarXML->envioXML($idVenta, FACTURA);
        // print_r( $claveAcceso );    exit;

        $validacionComprobante = new validacionComprobante();
        $validacion = $validacionComprobante->validar_comprobante($claveAcceso, FACTURA);
        //print_r( $validacion );    

        $autorizacionComprobante = new autorizacionComprobante();
        $autorizacion = $autorizacionComprobante->autorizacion_comprobante($claveAcceso, FACTURA);
        //  print_r( $autorizacion[ 'autorizaciones' ][ 'autorizacion' ][ 'estado' ] );      exit;
        // $data[ 'claveAcceso' ] = $claveAcceso;
        $this->model->actualizarClaveAccesso($claveAcceso, $idVenta);

        //$result = mysqli_num_rows( $update_clave );
        if ($autorizacion['autorizaciones']['autorizacion']['estado'] == 'AUTORIZADO') {

            $facturaElectronica = $this->model->getFacturaElectronica($claveAcceso);
            $serieElectronica = $this->generate_numbers($idVenta, 1, 9);

            $dataInfo = array(
                'ruc' => $facturaElectronica['ruc'],
                'email' => $facturaElectronica['correo'],
                'fecha' => $facturaElectronica['fecha'],
                'totalfactura' => $facturaElectronica['totalfactura'],
                'cliente' => $facturaElectronica['cliente'],
                'claveAcceso' => $claveAcceso,
                'empresa' => $empresa['nombre'],
                'factura' => $serieElectronica[0],
                'enviroment' => ENVIROMENT,
                'emailremitente' => $empresa['correo'],
                'establecimiento' => $empresa['establecimiento'],
                'puntoemi' => $empresa['puntoemi'],
                'tipo' => 'factura',
                'asunto' => 'Adjuntamos Comprobante Electronico'
            );
            // $res = array('msg' => 'FACTURA ELECTRONICA GENERADA EXITOSAMENTE REENVIO AL SRI', 'type' => 'success');
            // Alerta admin: cliente sin correo registrado
            if (empty($dataInfo['email']) || !filter_var($dataInfo['email'], FILTER_VALIDATE_EMAIL)) {
                try {
                    $cuerpo = '<p>El comprobante <b>' . htmlspecialchars($dataInfo['factura'] ?? '') . '</b> fue <b>AUTORIZADO</b> por el SRI pero <b>no se pudo enviar al cliente</b> porque no tiene correo registrado.</p>'
                        . '<table border=0 cellpadding=4 style="border-collapse:collapse;">'
                        . '<tr><td><b>Cliente:</b></td><td>' . htmlspecialchars($dataInfo['cliente'] ?? '') . '</td></tr>'
                        . '<tr><td><b>RUC/Cedula:</b></td><td>' . htmlspecialchars($dataInfo['ruc'] ?? '') . '</td></tr>'
                        . '<tr><td><b>Total:</b></td><td>$' . number_format((float)($dataInfo['totalfactura'] ?? 0), 2) . '</td></tr>'
                        . '<tr><td><b>Clave acceso:</b></td><td><code>' . htmlspecialchars($dataInfo['claveAcceso'] ?? '') . '</code></td></tr>'
                        . '<tr><td><b>Fecha:</b></td><td>' . htmlspecialchars($dataInfo['fecha'] ?? '') . '</td></tr>'
                        . '</table>'
                        . '<p>Por favor entrega manualmente el RIDE/XML al cliente o solicita su correo electronico.</p>';
                    enviarAlertaAdmin('CLIENTE_SIN_CORREO', 'Cliente sin correo - Factura ' . ($dataInfo['factura'] ?? ''), $cuerpo);
                } catch (\Throwable $e) { error_log('Alerta sin correo fallo: ' . $e->getMessage()); }
            }
            try { sendEmail($dataInfo, 'email_facturaelectronica', 'ventas'); } catch (\Throwable $e) { error_log('sendEmail fallo: ' . $e->getMessage()); }
        }

        //  echo json_encode($res, JSON_UNESCAPED_UNICODE);
        // die();
    }
    public function ordenVentaPDF($tipo, $idOrdenVenta)
    {
        $this->cargarSri();
        ob_start();
        // $array = explode(',', $datos);
        //$tipo = $array[0];
        //$idOrdenVenta = $array[1];
        $rutaGuardado = 'facturaelectronica/public/archivos/facturables/';

        $nombreArchivo = 'Facturable' . '_' . $idOrdenVenta . '.pdf';

        $data['title'] = 'Orden Venta';
        $data['empresa'] = $this->model->getEmpresa();
        $data['ordenventa'] = $this->model->getOrdenVenta($idOrdenVenta);
        //print_r( $data['cotizacion']); exit;
        if (empty($data['ordenventa'])) {
            echo 'Pagina no Encontrada';
            exit;
        }
        $this->views->getView('automaticas', $tipo, $data);
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



        $dompdf->render();
        $output = $dompdf->output();
        file_put_contents($rutaGuardado . $nombreArchivo, $output);
        // Output the generated PDF to Browser
        //$dompdf->stream('Facturable' . $idOrdenVenta . '.pdf', array('Attachment' => false));
    }
    function generate_numbers($start, $count, $digits)
    {
        $result = array();
        for ($n = $start; $n < $start + $count; $n++) {
            $result[] = str_pad($n, $digits, '0', STR_PAD_LEFT);
        }
        return $result;
    }
}

<?php

// ===============================
// BOOTSTRAP DEL SISTEMA (CRON)
// ===============================

date_default_timezone_set('America/Guayaquil');

ini_set('display_errors', 1);
error_reporting(E_ALL);

define('BASE_PATH', dirname(__DIR__));

require_once BASE_PATH . '/config/Config.php';
require_once BASE_PATH . '/config/Helpers.php';
require_once BASE_PATH . '/config/app/Autoload.php';

$model = new AutomaticasModel();

$fecha = date('Y-m-d');
$hora = date('H:i:s');
$mesNumero = (int) date('n');

$meses = [
    1 => 'enero',
    2 => 'febrero',
    3 => 'marzo',
    4 => 'abril',
    5 => 'mayo',
    6 => 'junio',
    7 => 'julio',
    8 => 'agosto',
    9 => 'septiembre',
    10 => 'octubre',
    11 => 'noviembre',
    12 => 'diciembre'
];

$mesActual = $meses[$mesNumero];
// Obtener mes anterior
$mesAnteriorNumero = $mesNumero - 1;
if ($mesAnteriorNumero == 0) {
    $mesAnteriorNumero = 12; // enero → diciembre
}

$mesAnterior = $meses[$mesAnteriorNumero];

$limite = 25; // 🔴 IMPORTANTE: procesa en bloques pequeños

echo "CRON FACTURACIÓN – Mes: {$mesActual}\n";
/* ===============================
   OBTENER CONTRATOS A FACTURAR
================================ */
$contratos = $model->getContratosFacturar($mesActual, 1, 1);
echo "Contratos encontrados: " . count($contratos) . PHP_EOL;
if (empty($contratos)) {
    echo "No hay contratos pendientes.\n";
    exit;
}

foreach ($contratos as $contrato) {

    try {



        $idContrato = $contrato['id'];
        $idCliente = $contrato['idCliente'];
        $total = $contrato['total'];
        $productos = json_decode($contrato['productos'], true);

        $empresa = $model->getEmpresa();

        /* ===============================
           DATOS CLIENTE
        ================================ */
        $cliente = $model->getCliente($idCliente);
        if ($cliente['id'] == 1) {
            $tipoIdentificacion = 7;
        } else if ($cliente['identidad'] == 'CEDULA') {
            $tipoIdentificacion = 5;
        } else {
            $tipoIdentificacion = 4;
        }
        // echo $idCliente; break;
        /* ===============================
           SERIE
        ================================ */
        // $serieOV = $model->getSerieOrdenVenta()['total'] + 1;
        $serieFE = $model->getSerieElectronica()['total'] + 1;

        $resultSerieElectronica = $model->getSerieElectronica();
        $numSerieElectronica = ($resultSerieElectronica['total'] == null) ? 1 : $resultSerieElectronica['total'] + 1;
        $serieElectronica = str_pad($numSerieElectronica, 9, '0', STR_PAD_LEFT);
        //            str_pad($serieFE, 9, '0', STR_PAD_LEFT),

        /* ===============================
           REGISTRAR ENCABEZADO ELECTRÓNICO
        ================================ */
        $ventaElectronica = $model->registrarEncabezado(
            $fecha,
            $numSerieElectronica,
            trim($cliente['nombre']),
            trim($cliente['direccion']),
            trim($cliente['telefono']),
            trim($cliente['num_identidad']),
            $tipoIdentificacion,
            $cliente['correo'],
            $empresa['establecimiento'],
            $empresa['puntoemi'],
            $empresa['ruc'],
            AMBIENTE,
            $empresa['razon_social'],
            $empresa['nombre'],
            $numSerieElectronica,
            $empresa['direccion'],
            $empresa['contabilidad'],
            0,
            $total,
            '',
            2, // 🔴 pendiente SRI
            'CREDITO',
            4,
            $idCliente
        );

        /* ===============================
           DETALLE + STOCK + MOVIMIENTOS
        ================================ */
        foreach ($productos as $producto) {

            $result   = $model->getProducto($producto['id']);
            $cantidad = $producto['cantidad'];

            $ivaPorcentaje = $result['iva'];
            $factorIVA     = 1 + ($ivaPorcentaje / 100);

            // Precio unitario SIN IVA
            if ($ivaPorcentaje > 0) {
                $precioSinIva = round($producto['precio'] / $factorIVA, 6);

                // Subtotal SIN IVA
                $subtotalSinIva = round($precioSinIva * $cantidad, 6);

                // IVA
                $valorIva = round($subtotalSinIva * ($ivaPorcentaje / 100), 6);

                // Total CON IVA
               // $totalConIva = round($subtotalSinIva + $valorIva, 6);
            } else {
                $precioSinIva = $producto['precio']; // no se divide

                  // Subtotal SIN IVA
                $subtotalSinIva = round($precioSinIva * $cantidad, 6);
            }

            // Registrar detalle
            $model->registrarDetalle(
                $numSerieElectronica,
                $cantidad,
                $producto['nombre'] . $mesActual,
                $precioSinIva,
                $subtotalSinIva,
                $result['iva'],
                $result['codigo'],
                0,
                $producto['precio'],
                0,
                $result['id']
            );

            // Actualizar stock
            if ($result['id_categoria'] == 1) {
                $nuevaCantidad = $result['cantidad'];
                $totalVentas   = $result['ventas'] + $cantidad;
            } else {
                $nuevaCantidad = $result['cantidad'] - $cantidad;
                $totalVentas   = $result['ventas'] + $cantidad;
            }

            $model->actualizarStock($nuevaCantidad, $totalVentas, $result['id']);

            // Movimiento inventario
            $movimiento = 'Venta Electrónica N° ' . $numSerieElectronica;
            $model->registrarMovimiento(
                $movimiento,
                'salida',
                $cantidad,
                $nuevaCantidad,
                $result['id'],
                4
            );
        }


        /* ===============================
           CRÉDITO
        ================================ */
        $model->registrarCredito(
            $total,
            $fecha,
            $hora,
            null,
            $ventaElectronica,
            null,
            $idContrato
        );

        /* ===============================
           MARCAR MES FACTURADO
        ================================ */
        $model->actualizarMesContratoF($mesActual, 1, $idContrato, 'UNO');
        $model->actualizarMesContratoAnterior($mesAnterior, 0, $idContrato, 'UNO');

        echo "Contrato {$idContrato} facturado correctamente\n";
    } catch (Exception $e) {

        error_log(
            date('Y-m-d H:i:s') .
                " ERROR CONTRATO {$idContrato}: " .
                $e->getMessage() . PHP_EOL,
            3,
            __DIR__ . '/cron_error.log'
        );
    }
}

echo "CRON FINALIZADO\n";

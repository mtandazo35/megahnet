<?php
/**
 * CRON ORDEN VENTA
 * Genera ordenes de venta + creditos (recibos) para los contratos con factura=0
 * del mes en curso que aun no tienen mes_facturar.<mes>=1.
 * Sin envio de SRI ni email inline.
 */

date_default_timezone_set('America/Guayaquil');
ini_set('display_errors', 1);
error_reporting(E_ALL);

define('BASE_PATH', dirname(__DIR__));

require_once BASE_PATH . '/config/Config.php';
require_once BASE_PATH . '/config/Helpers.php';
require_once BASE_PATH . '/config/app/Autoload.php';

$model = new AutomaticasModel();

$fecha = date('Y-m-d');
$hora  = date('H:i:s');

$meses = [
    1 => 'enero', 2 => 'febrero', 3 => 'marzo', 4 => 'abril',
    5 => 'mayo', 6 => 'junio', 7 => 'julio', 8 => 'agosto',
    9 => 'septiembre', 10 => 'octubre', 11 => 'noviembre', 12 => 'diciembre',
];
$mesActual = $meses[(int) date('n')];

echo "CRON ORDEN VENTA - Mes: {$mesActual}\n";

$contratos = $model->getContratosFacturar($mesActual, 1, 0, (int)($argv[1] ?? 0));
echo "Contratos encontrados: " . count($contratos) . PHP_EOL;
if (empty($contratos)) {
    exit("No hay contratos pendientes.\n");
}

$ok = 0; $fail = 0;

foreach ($contratos as $contrato) {
    try {
        $idContrato = $contrato['id'];
        $idCliente  = $contrato['idCliente'];
        $productos  = json_decode($contrato['productos'], true);
        if (!is_array($productos) || empty($productos)) {
            throw new Exception('productos JSON vacio o invalido');
        }

        $resultSerie = $model->getSerieOrdenVenta();
        $numSerieOV  = ($resultSerie['total'] == null) ? 1 : $resultSerie['total'] + 1;
        $serieOV     = str_pad($numSerieOV, 9, '0', STR_PAD_LEFT);

        $total = 0;
        $arrayProductos = [];
        foreach ($productos as $p) {
            $r = $model->getProducto($p['id']);
            $cantidad = $p['cantidad'];
            $precio   = $p['precio'];
            $arrayProductos[] = [
                'id'           => $r['id'],
                'nombre'       => $p['nombre'] . $mesActual,
                'precio'       => $precio,
                'cantidad'     => $cantidad,
                'iva_producto' => $r['iva'],
                'codigobarra'  => $r['codigo'],
            ];
            $total += $precio * $cantidad;
        }
        $datosProductos = json_encode($arrayProductos, JSON_UNESCAPED_UNICODE);

        $ordenVenta = $model->registrarOrdenVenta(
            $datosProductos, $total, $fecha, $hora,
            'CREDITO', 0, $serieOV, 2, $idCliente, 4, ''
        );

        if (!($ordenVenta > 0)) {
            throw new Exception('registrarOrdenVenta devolvio falsy');
        }

        foreach ($productos as $p) {
            $r = $model->getProducto($p['id']);
            $cantidad = $p['cantidad'];
            if ($r['id_categoria'] == 1) {
                $nuevaCantidad = $r['cantidad'];
            } else {
                $nuevaCantidad = $r['cantidad'] - $cantidad;
            }
            $totalVentas = $r['ventas'] + $cantidad;
            $model->actualizarStock($nuevaCantidad, $totalVentas, $r['id']);

            $movimiento = 'Orden Venta N: ' . $ordenVenta;
            $model->registrarMovimiento($movimiento, 'salida', $cantidad, $nuevaCantidad, $r['id'], 4);
        }

        $model->registrarCredito($total, $fecha, $hora, null, null, $ordenVenta, $idContrato);

        $model->actualizarMesContratoF($mesActual, 1, $idContrato, 'UNO');

        $ok++;
        echo "Contrato {$idContrato} -> OV {$ordenVenta}\n";
    } catch (Exception $e) {
        $fail++;
        error_log(
            date('Y-m-d H:i:s') . " ERROR CONTRATO {$idContrato}: " . $e->getMessage() . PHP_EOL,
            3,
            BASE_PATH . '/storage/cron_orden_venta_error.log'
        );
        echo "Contrato {$idContrato} FAIL: " . $e->getMessage() . "\n";
    }
}

echo "CRON ORDEN VENTA FIN. OK={$ok} FAIL={$fail}\n";

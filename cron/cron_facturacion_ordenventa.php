<?php
// ===============================
// CRON: ORDEN DE VENTA AUTOMATICA (factura=0)
// Espejo de cron_facturacion_automaticas.php pero para contratos sin facturacion electronica.
// ===============================

date_default_timezone_set('America/Guayaquil');
ini_set('display_errors', 1);
error_reporting(E_ALL);

define('BASE_PATH', dirname(__DIR__));
require_once BASE_PATH . '/config/Config.php';
require_once BASE_PATH . '/config/Helpers.php';
require_once BASE_PATH . '/config/app/Autoload.php';

// === LOCK GLOBAL (distinto al del cron de FACTURAS y al del controller HTTP) ===
$lockDir = BASE_PATH . '/storage';
if (!is_dir($lockDir)) @mkdir($lockDir, 0755, true);
$lockFile = $lockDir . '/cron_facturacion_ordenventa.lock';
$lockHandle = @fopen($lockFile, 'c');
if (!$lockHandle || !@flock($lockHandle, LOCK_EX | LOCK_NB)) {
    echo "[" . date('Y-m-d H:i:s') . "] Otro proceso de cron_facturacion_ordenventa ya esta en curso. Saliendo.\n";
    exit(0);
}
register_shutdown_function(function() use ($lockHandle, $lockFile) {
    if ($lockHandle) { @flock($lockHandle, LOCK_UN); @fclose($lockHandle); }
    @unlink($lockFile);
});

$model = new AutomaticasModel();

$mesNumero = (int) date('n');
$meses = [1=>'enero',2=>'febrero',3=>'marzo',4=>'abril',5=>'mayo',6=>'junio',
          7=>'julio',8=>'agosto',9=>'septiembre',10=>'octubre',11=>'noviembre',12=>'diciembre'];
$mesActual = $meses[$mesNumero];
$mesAnteriorNumero = ($mesNumero == 1) ? 12 : $mesNumero - 1;
$mesAnterior = $meses[$mesAnteriorNumero];

$fechaCorte = date('m-Y');
$idUsuarioSistema = 4; // MEGAHNET (mismo user que usa el cron de facturas SRI)

echo "[" . date('Y-m-d H:i:s') . "] CRON ORDEN VENTA - Mes: {$mesActual}\n";

$contratos = $model->getContratosFacturar($mesActual, 1, 0, (int)($argv[1] ?? 0)); // argv[1] = limite opcional de contratos por corrida
$total = count($contratos);
echo "Contratos encontrados (factura=0): {$total}\n";
if (empty($contratos)) {
    echo "No hay contratos pendientes.\n";
    exit(0);
}

$procesados = 0;
$fallidos = [];

foreach ($contratos as $datosContrato) {
    $idContrato = $datosContrato['id'] ?? null;
    try {
        $fecha = date('Y-m-d');
        $hora = date('H:i:s');
        $metodo = 'CREDITO';
        $estado = 2; // a credito
        $descuento = 0;
        $totalContrato = 0;
        $productosCalculados = [];

        $resultSerieOV = $model->getSerieOrdenVenta();
        $numSerieOV = ($resultSerieOV['total'] == null) ? 1 : $resultSerieOV['total'] + 1;
        // generate_numbers existe en Controller, replicamos zero-pad simple
        $serieOrdenVenta = str_pad($numSerieOV, 9, '0', STR_PAD_LEFT);

        $idCliente = $datosContrato['idCliente'] ?? $datosContrato['id_cliente'];
        $productos = json_decode($datosContrato['productos'], true);
        if (!is_array($productos) || empty($productos)) {
            throw new Exception("productos JSON vacio o invalido");
        }

        foreach ($productos as $producto) {
            $result = $model->getProducto($producto['id']);
            $cant = isset($producto['cantidad']) ? (int)$producto['cantidad'] : 1;
            $precio = isset($producto['precio']) ? (float)$producto['precio'] : 0;
            $productosCalculados[] = [
                'id' => $result['id'],
                'nombre' => $producto['nombre'] . $mesActual,
                'precio' => $precio,
                'cantidad' => $cant,
                'iva_producto' => $result['iva'],
                'codigobarra' => $result['codigo'],
            ];
            $totalContrato += $precio * $cant;
        }

        $datosProductos = json_encode($productosCalculados, JSON_UNESCAPED_UNICODE);
        $ordenVenta = $model->registrarOrdenVenta(
            $datosProductos, $totalContrato, $fecha, $hora, $metodo, $descuento,
            $serieOrdenVenta, $estado, $idCliente, $idUsuarioSistema, ''
        );

        if (!$ordenVenta || $ordenVenta <= 0) {
            throw new Exception("registrarOrdenVenta retorno valor invalido");
        }

        // Stock y movimientos
        foreach ($productos as $producto) {
            $result = $model->getProducto($producto['id']);
            $cant = isset($producto['cantidad']) ? (int)$producto['cantidad'] : 1;
            if ($result['id_categoria'] == 1) {
                $nuevaCantidad = $result['cantidad'];
                $totalVentas = $result['ventas'] + $cant;
            } else {
                $nuevaCantidad = $result['cantidad'] - $cant;
                $totalVentas = $result['ventas'] + $cant;
            }
            $model->actualizarStock($nuevaCantidad, $totalVentas, $result['id']);
            $model->registrarMovimiento('Orden Venta N°: ' . $ordenVenta, 'salida', $cant, $nuevaCantidad, $producto['id'], $idUsuarioSistema);
        }

        // Credito
        $monto = $totalContrato - $descuento;
        $model->registrarCredito($monto, $fecha, $hora, null, null, $ordenVenta, $idContrato);

        // Marcar mes y resetear anterior
        $model->actualizarMesContratoF($mesActual, 1, $idContrato, 'UNO');
        $model->actualizarMesContratoAnterior($mesAnterior, 0, $idContrato, 'UNO');

        $procesados++;
        echo "Contrato {$idContrato} -> OV {$ordenVenta} ({$monto})\n";

    } catch (Throwable $e) {
        $fallidos[] = ['idContrato' => $idContrato, 'error' => $e->getMessage()];
        error_log(
            date('Y-m-d H:i:s') . " ERROR OV CONTRATO " . ($idContrato ?? '?') . ": " . $e->getMessage() . PHP_EOL,
            3, __DIR__ . '/cron_ordenventa_error.log'
        );
    }
}

// Cerrar corte solo si proceso al menos 1
if ($procesados > 0) {
    // Verificar que NO existe ya un corte ORDENVENTA cerrado este mes (evita duplicar fila)
    $estadoCorteActual = $model->estadoCorte($fechaCorte, 'ORDENVENTA');
    if (empty($estadoCorteActual) || ($estadoCorteActual['estado'] ?? 0) != 1) {
        $model->actualizarCorte($fechaCorte, date('Y-m-d H:i:s'), 'ORDENVENTA');
    }
}

echo "[" . date('Y-m-d H:i:s') . "] CRON ORDEN VENTA FINALIZADO. Procesados: {$procesados} de {$total}. Fallidos: " . count($fallidos) . "\n";
if (!empty($fallidos)) {
    foreach ($fallidos as $f) echo "  FALLO contrato " . ($f['idContrato'] ?? '?') . ": " . $f['error'] . "\n";
}

<?php
// Reporte PDF de la caja activa (apertura sin cierre). La estructura de datos
// del controller para "actual" es anidada (Datos, tipoPago).
$tituloDoc      = $data['title'] ?? 'Reporte de caja actual';
$numeroCaja     = $data['idCaja'] ?? '';
$isActual       = !empty($data['actual']);
$usuarioActual  = $_SESSION['nombre_usuario'] ?? '';
$empresa        = $data['empresa'] ?? [];

$_m   = $data['movimientos']['Datos']     ?? [];
$_tp  = $data['movimientos']['tipoPago']  ?? [];
$mov = [
    'inicial'  => $_m['inicialDecimal']   ?? '0.00',
    'ingresos' => $_m['ingresosDecimal']  ?? '0.00',
    'egresos'  => $_m['egresosDecimal']   ?? '0.00',
    'gastos'   => $_m['gastosDecimal']    ?? '0.00',
    'saldo'    => $_m['saldoDecimal']     ?? '0.00',
];
$tp = [
    'efectivo'      => $_tp['efectivoDecimal']    ?? '0.00',
    'bancarisado'   => $_tp['bancarisadoDecimal'] ?? '0.00',
    'saldoEfectivo' => number_format(
        ((float)str_replace(',', '', $_tp['efectivoDecimal'] ?? 0))
            - ((float)str_replace(',', '', $_m['gastosDecimal'] ?? 0)),
        2, '.', ','
    ),
];
$gastos = $data['movimientos']['historialGastos'] ?? [];

include __DIR__ . '/_pdf_template.php';

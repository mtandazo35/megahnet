<?php
// Reporte PDF de una caja cerrada (historial). Mapea $data del controller
// al template compartido views/cajas/_pdf_template.php
$tituloDoc      = $data['title'] ?? 'Reporte de caja';
$numeroCaja     = $data['idCaja'] ?? '';
$isActual       = !empty($data['actual']);
$usuarioActual  = $_SESSION['nombre_usuario'] ?? '';
$empresa        = $data['empresa'] ?? [];

$_m = $data['movimientos'] ?? [];
$mov = [
    'inicial'  => $_m['inicialDecimal']   ?? '0.00',
    'ingresos' => $_m['ingresosDecimal']  ?? '0.00',
    'egresos'  => $_m['egresosDecimal']   ?? '0.00',
    'gastos'   => $_m['gastosDecimal']    ?? '0.00',
    'saldo'    => $_m['saldoDecimal']     ?? '0.00',
];
$tp = [
    'efectivo'      => $_m['efectivoDecimal']     ?? '0.00',
    'bancarisado'   => $_m['bancarisadoDecimal']  ?? '0.00',
    'saldoEfectivo' => number_format(
        ((float)str_replace(',', '', $_m['efectivoDecimal'] ?? 0))
            - ((float)str_replace(',', '', $_m['gastosDecimal'] ?? 0)),
        2, '.', ','
    ),
];
$gastos = $_m['historialGastos'] ?? [];

include __DIR__ . '/_pdf_template.php';

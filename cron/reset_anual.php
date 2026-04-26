<?php
require_once dirname(__DIR__) . '/config/config.php';
require_once dirname(__DIR__) . '/config/database.php';
require_once dirname(__DIR__) . '/models/AutomaticasModel.php';

$model = new AutomaticasModel();

$anioActual = date('Y');

/**
 * VERIFICAR SI YA SE RESETEÓ ESTE AÑO
 */
$verificado = $model->estadoCorte("01-01-$anioActual", "RESET_MES");

if ($verificado['total'] == 0) {

    $meses = [
        'enero','febrero','marzo','abril','mayo','junio',
        'julio','agosto','septiembre','octubre','noviembre','diciembre'
    ];

    foreach ($meses as $mes) {
        $model->actualizarMesContratoF($mes, 0, null, 'TODOS');
    }

    $model->actualizarCorte(
        date('Y-m-d'),
        "01-01-$anioActual",
        'RESET_MES'
    );

    echo "✔ Reset anual ejecutado\n";
}

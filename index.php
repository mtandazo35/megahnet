<?php
require_once 'config/Config.php';
require_once 'config/Helpers.php';
require_once 'config/ErrorAlerts.php';
//require_once 'config/cacheWarmer.php';




$ruta = (!empty($_GET['url'])) ? $_GET['url'] : 'principal/index';
$array = explode('/', $ruta);
// alias factura -> ventas (URL renombrada manteniendo controlador interno)
if (isset($array[0]) && strtolower($array[0]) === 'factura') { $array[0] = 'ventas'; }

// ---------------------------------------------------------------------------
// Modo mantenimiento (lo activa /usr/local/sbin/megahnet-update con un flag).
// Una sola comprobacion is_file() por request: si no hay flag, coste ~0.
// ---------------------------------------------------------------------------
$__mantFlag = ROOT_PATH . '/storage/update/maintenance.flag';
if (is_file($__mantFlag)) {
    // Rutas que NUNCA se bloquean: son las que usa el propio panel de
    // actualizacion para seguir el progreso y poder revertir.
    $__rutaMant = strtolower($array[0] . '/' . ((isset($array[1]) && $array[1] !== '') ? $array[1] : 'index'));
    $__mantLibres = array(
        'admin/actualizacion',
        'admin/actualizacionestado',
        'admin/actualizaciondisponible',
        'admin/actualizar',
        'admin/revertir',
    );
    if (!in_array($__rutaMant, $__mantLibres, true)) {
        // Anti-bloqueo permanente: si el actualizador murio sin limpiar el
        // flag, a los 30 minutos se ignora (y se intenta borrar).
        $__mantEdad = time() - (int)@filemtime($__mantFlag);
        if ($__mantEdad >= 0 && $__mantEdad < 1800) {
            $__mantMotivo = 'actualizacion';
            $__mantJson = @json_decode((string)@file_get_contents($__mantFlag), true);
            if (is_array($__mantJson) && !empty($__mantJson['motivo'])) {
                $__mantMotivo = (string)$__mantJson['motivo'];
            }
            header('HTTP/1.1 503 Service Unavailable', true, 503);
            header('Retry-After: 120');
            header('Cache-Control: no-store');
            require ROOT_PATH . '/views/templates/mantenimiento.php';
            exit;
        }
        @unlink($__mantFlag);
    }
}
$controller = ucfirst($array[0]);
$metodo = 'index';
$parametro = '';

 //print_r($array[0]); exit;
//exit;



if (!empty($array[1])) {
    if ($array[1] != '') {
        $metodo = $array[1];
    }
}
if (!empty($array[2])) {
    if ($array[2] != '') {
        for ($i=2; $i < count($array); $i++) { 
            $parametro .= $array[$i] . ',';
        }
        $parametro = trim($parametro, ',');
    }
}
require_once 'config/app/Autoload.php';
$dirControllorer = 'controllers/' . $controller . '.php';

// Fallback case-insensitive: si el filename exacto no existe, busca un match
// case-insensitive en el directorio. Permite eliminar los duplicados de
// controladores que existian solo por diferencias de mayusculas/minusculas.
if (!file_exists($dirControllorer)) {
    static $controllersIdx = null;
    if ($controllersIdx === null) {
        $controllersIdx = [];
        foreach (glob('controllers/*.php') as $f) {
            $controllersIdx[strtolower(basename($f))] = $f;
        }
    }
    $needle = strtolower($controller . '.php');
    if (isset($controllersIdx[$needle])) {
        $dirControllorer = $controllersIdx[$needle];
    }
}

if (file_exists($dirControllorer)) {
    require_once $dirControllorer;
    $controller = new $controller();
    if (method_exists($controller, $metodo)) {
       $controller->$metodo($parametro);
    }else{
        header('Location: ' . BASE_URL . 'principal/errors');
    }
}else{
    header('Location: ' . BASE_URL . 'principal/errors');
}
?>

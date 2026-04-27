<?php
require_once 'config/Config.php';
require_once 'config/Helpers.php';
//require_once 'config/cacheWarmer.php';




$ruta = (!empty($_GET['url'])) ? $_GET['url'] : 'principal/index';
$array = explode('/', $ruta);
// alias factura -> ventas (URL renombrada manteniendo controlador interno)
if (isset($array[0]) && strtolower($array[0]) === 'factura') { $array[0] = 'ventas'; }
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
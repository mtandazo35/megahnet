<?php
// Polyfill PHP 8.2+: utf8_encode/decode removed
if (!function_exists('utf8_encode')) {
    function utf8_encode($s) { return mb_convert_encoding((string)$s, 'UTF-8', 'ISO-8859-1'); }
}
if (!function_exists('utf8_decode')) {
    function utf8_decode($s) { return mb_convert_encoding((string)$s, 'ISO-8859-1', 'UTF-8'); }
}

spl_autoload_register(function ($class) {

    $basePath = dirname(__DIR__, 2); // apunta a /megahnet

    // Mapeo explicito de clases del flujo SRI (envio + validacion + autorizacion)
    // El nombre de la clase no coincide con el nombre del archivo, por eso van mapeadas.
    $faMap = [
        'enviarXML'              => '/facturaelectronica/envio_xml.php',
        'validacionComprobante'  => '/facturaelectronica/src/validacionComprobante.php',
        'autorizacionComprobante'=> '/facturaelectronica/src/autorizacionComprobante.php',
    ];
    if (isset($faMap[$class])) {
        $f = $basePath . $faMap[$class];
        if (file_exists($f)) { require_once $f; return; }
    }

    $paths = [
        $basePath . '/config/app/' . $class . '.php',
        $basePath . '/models/' . $class . '.php',
        $basePath . '/controllers/' . $class . '.php',
    ];

    foreach ($paths as $file) {
        if (file_exists($file)) {
            require_once $file;
            return;
        }
    }
});

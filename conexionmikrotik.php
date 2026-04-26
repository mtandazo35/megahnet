<?php
$ip       = getenv('MIKROTIK_IP')   ?: '192.168.88.1:9090';
$username = getenv('MIKROTIK_USER') ?: 'admin';
$password = getenv('MIKROTIK_PASS') ?: '';
$puerto   = getenv('MIKROTIK_PORT') ?: '8728';

include_once("routeros_api.class.php");

$API = new RouterosAPI();
$API->debug = true;
$API->port  = $puerto;

if ($API->connect($ip, $username, $password)) {
    print '<div class="alert alert-primary" role="alert">CONEXION EXITOSA</div>';
} else {
    print '<div class="alert alert-danger" role="alert">CONEXION RECHAZADA</div>';
}

<?php 
$ip="172.31.0.1";
$username="CORTE";
$password="CORTE";
$puerto="8728";

include_once("routeros_api.class.php");

$API = new RouterosAPI();
$API->debug=true;
$API->port=$puerto;

  if ($API->connect($ip, $username, $password)) {
      print '<div class="alert alert-primary" role="alert">CONEXIÓN EXITOSA</div>';
  } else {
      print '<div class="alert alert-danger" role="alert">CONEXIÓN RECHAZADA</div>';
  }


?>
<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Asumiendo que estas variables están definidas correctamente
// $API = ...; // Tu lógica de conexión API
// $ip = ...; 
// $username = ...; 
// $password = ...;
require_once("conexion.php");

if ($API->connect($ip, $username, $password)) {
    $targetIp = "192.168.40.101"; // Reemplaza con la IP que deseas permitir

    // Obtener la regla que bloquea el tráfico de la IP específica
    $rules = $API->comm("/ip/firewall/filter/print", [
        "?src-address" => $targetIp,
        "?action" => "drop"
    ]);

    if (!empty($rules)) {
        // Suponiendo que solo queremos eliminar la primera coincidencia
        $ruleId = $rules[0]['.id'];
        $API->comm("/ip/firewall/filter/remove", [
            ".id" => $ruleId
        ]);
        echo json_encode(["success" => "El acceso a Internet para {$targetIp} ha sido restaurado."]);
    } else {
        echo json_encode(["error" => "No se encontró ninguna regla de bloqueo para {$targetIp}."]);
    }
} else {
    echo json_encode(["error" => "CONEXION RECHAZADA"]);
}
?>

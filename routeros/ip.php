<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Assuming these variables are defined properly
// $API = ...; // Your API connection logic
// $ip = ...; 
// $username = ...; 
// $password = ...;
require_once("conexion.php");

if ($API->connect($ip, $username, $password)) {
    $targetIp = "192.168.40.101"; // Replace with the IP you want to block

    // Add a firewall rule to drop traffic from the specified IP
    $API->comm("/ip/firewall/filter/add", [
        "chain" => "forward",
        "src-address" => $targetIp,
        "action" => "drop",
        "comment" => "Block internet access for " . $targetIp
    ]);

    echo json_encode(["success" => "Internet access for {$targetIp} has been blocked."]);
} else {
    echo json_encode(["error" => "CONEXION RECHAZADA"]);
}
?>

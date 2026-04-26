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
    // Fetch the list of queues
    $getQueues = $API->comm("/queue/simple/print");
    $response = [];

    foreach ($getQueues as $queue) {
        $response[] = $queue; // Add each queue to the response array
    }

    header('Content-Type: application/json'); // Set content type for JSON response
    echo json_encode($response); // Return the response as JSON
} else {
    header('Content-Type: application/json'); // Set content type for error response
    echo json_encode(["error" => "CONEXION RECHAZADA"]); // Return error as JSON
}
?>

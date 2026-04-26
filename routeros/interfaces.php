<?php

require_once("conexion.php");




if ($API->connect($ip, $username, $password)) {
    $getInterfaces = $API->comm("/queue/simple/print");
    $response = []; // Initialize response as an array

    foreach ($getInterfaces as $interface) {
        $response[] = $interface; // Add each interface to the response array
    }

    header('Content-Type: application/json'); // Set content type for JSON response
    echo json_encode($response); // Return the response as JSON
} else {
    header('Content-Type: application/json'); // Set content type for error response
    echo json_encode(["error" => "CONEXION RECHAZADA"]); // Return error as JSON
}
?>



<?php
//Obtener parametros de la URL enviados por PayPhone
$transaccion = $_GET["id"];
$client = $_GET["clientTransactionId"];


//Preparar JSON de llamada
$data_array = array(
    "id" => (int) $transaccion,
    "clientTxId" => $client);

$data = json_encode($data_array);

//Iniciar Llamada
$curl = curl_init();
curl_setopt($curl, CURLOPT_URL, "https://pay.payphonetodoesposible.com/api/button/V2/Confirm");
curl_setopt($curl, CURLOPT_POST, 1);
curl_setopt($curl, CURLOPT_POSTFIELDS, $data);
curl_setopt_array($curl, array(
CURLOPT_HTTPHEADER => array(
"Authorization: Bearer u0BkuWe0Icd0w-i-BmtyAEGZMgYK0aTtZ0sbMsC0_w8DCfTY5NLH8SKwjd9O4rflBD9kW-ldz9nql_BCLPlgKb5HWfMgkvtq5GBHLS9uYpWmO8Dry5HOP68Yr-9RyAAOTZ6YNqk34X2cNij-2nH0U8u8w5QYdPYKOHkRfG5ZgIXqP0u7o8h-ba2eBvcONLXT42m1IdCZbslY2aCYqb_gK2NADKSzZYSvWO-RKnBnFY-FwtkKIAflfouDW4gk4AKTwb8XLRZj_K6OsY1uL8Y1F5FbusfbpiefjjeLORToJH9-pDa-U7u4aQ8c2SJdAjXQeX-wWQBlaue-RZfJJy0A33WLb7I", "Content-Type:application/json"),
));
curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
$result = curl_exec($curl);
curl_close($curl);

//En la variable result obtienes todos los parámetros de respuesta
echo $result;





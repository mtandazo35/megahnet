<?php
include_once 'views/templates/header.php';
$transaccion = $_GET["id"];
$client = $_GET["clientTransactionId"];

?>

<script>
    var transaccion = <?php echo $transaccion ?>;
    var client = '<?php echo $client ?>';
    var parametros = {
        id: transaccion,
        clientTxId: client
    };

    $.ajax({
        data: parametros,
        url: 'https://pay.payphonetodoesposible.com/api/button/Confirm',
        type: 'POST',
        beforeSend: function(xhr) {
            xhr.setRequestHeader('Authorization',
                "Bearer u0BkuWe0Icd0w-i-BmtyAEGZMgYK0aTtZ0sbMsC0_w8DCfTY5NLH8SKwjd9O4rflBD9kW-ldz9nql_BCLPlgKb5HWfMgkvtq5GBHLS9uYpWmO8Dry5HOP68Yr-9RyAAOTZ6YNqk34X2cNij-2nH0U8u8w5QYdPYKOHkRfG5ZgIXqP0u7o8h-ba2eBvcONLXT42m1IdCZbslY2aCYqb_gK2NADKSzZYSvWO-RKnBnFY-FwtkKIAflfouDW4gk4AKTwb8XLRZj_K6OsY1uL8Y1F5FbusfbpiefjjeLORToJH9-pDa-U7u4aQ8c2SJdAjXQeX-wWQBlaue-RZfJJy0A33WLb7I"
            )
        },
        success: function Confirmacion(respuesta) {

            var estado = respuesta.transactionStatus;
            alert(respuesta.transactionStatus);
            alert(respuesta.authorizationCode);
            if (respuesta.transactionStatus == "Approved") {
                alert("Pago" + respuesta.transactionId + "recibido, estado" + respuesta
                    .transactionStatus);
            }
           // document.getElementById("result").innerHTML = estado;

            let inttipopago = 2;
            let base_url = '<?php echo BASE_URL; ?>';

            let request = window.XMLHttpRequest ?
                new XMLHttpRequest() :
                new ActiveXObject("Microsoft.XMLHTTP");
            let ajaxUrl = base_url + '/payphone/procesarVenta';


            let formData = new FormData();

            formData.append('inttipopago', inttipopago);
            formData.append('datapayphone', JSON.stringify(respuesta));
            request.open("POST", ajaxUrl, true);
            request.send(formData);
            request.onreadystatechange = function() {
                if (request.readyState != 4) return;
                if (request.status == 200) {
                    let objData = JSON.parse(request.responseText);
                    if (objData.status) {
                        window.location = base_url + "/tienda/confirmarpedido/";
                    } else {
                        swal("", objData.msg, "error");
                    }
                }
            }
        },
        error: function(mensajeerror) {
            alert("Error en la llamada:" + mensajeerror.Message);
        }
    });
</script>
<?php include_once 'views/templates/footer.php'; ?>
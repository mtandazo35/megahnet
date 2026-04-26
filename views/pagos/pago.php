<?php include_once 'views/templates/header.php';
$clientTransId = tokenPayPhone();


$numFactura = $_GET['numFactura'];
$totalFactura = $_GET['valor'] * 100;

?>



<script src="https://pay.payphonetodoesposible.com/api/button/js?appId=xFNOkt3ZoU2JLQCNdaDSzQ"></script>


<script>
    window.onload = function() {
        payphone.Button({

            //token obtenido desde la consola de developer
            token: "u0BkuWe0Icd0w-i-BmtyAEGZMgYK0aTtZ0sbMsC0_w8DCfTY5NLH8SKwjd9O4rflBD9kW-ldz9nql_BCLPlgKb5HWfMgkvtq5GBHLS9uYpWmO8Dry5HOP68Yr-9RyAAOTZ6YNqk34X2cNij-2nH0U8u8w5QYdPYKOHkRfG5ZgIXqP0u7o8h-ba2eBvcONLXT42m1IdCZbslY2aCYqb_gK2NADKSzZYSvWO-RKnBnFY-FwtkKIAflfouDW4gk4AKTwb8XLRZj_K6OsY1uL8Y1F5FbusfbpiefjjeLORToJH9-pDa-U7u4aQ8c2SJdAjXQeX-wWQBlaue-RZfJJy0A33WLb7I",

            //PARÁMETROS DE CONFIGURACIÓN
            btnHorizontal: true,
            btnCard: true,

            createOrder: function(actions) {
                //Se ingresan los datos de la transaccion ej. monto, impuestos, etc

                return actions.prepare({

                    amount: <?= $totalFactura; ?>,
                    amountWithoutTax: <?= $totalFactura; ?>,
                    currency: "USD",
                    clientTransactionId: "<?= $clientTransId ?>",
                    lang: "es"

                }).then(function(paramlog) {
                    console.log(paramlog);
                    return paramlog;
                }).catch(function(paramlog2) {
                    console.log(paramlog2);
                    return paramlog2;
                });
            },

            onComplete: function(model, actions) {
                //Se confirma el pago realizado
                actions.confirm({
                    id: model.id,
                    clientTxId: model.clientTxId

                }).then(function(value) {

                    //EN ESTA SECCIÓN SE RECIBE LA RESPUESTA Y SE MUESTRA AL USUARIO


                    /*   if (value.transactionStatus == "Approved") {
                           alert("Pago de pagos" + value.transactionId + "recibido, estado" + value
                               .transactionStatus);
                       }*/



                }).catch(function(err) {
                    console.log(err);
                });
            }
        }).render("#pp-button");
    }
</script>


<div class="row justify-content-between">

    <h2 style="text-align: center;font-family: georgia, palatino, serif; color: #000000;">FORMAS DE PAGOS ELECTRONICOS
    </h2>


    <h3 class="woodmart-title-container title wd-font-weight- wd-fontsize-xl"><span style="font-family: georgia, palatino, serif; color: #000000;">OPCIONES DE PAGO ONLINE</span></h3>
    <div style="padding-bottom: 20px;" class="col-md-6">
        <span style="font-family: georgia, palatino, serif; color: #000000; font-size: 18pt;">PAYPHONE</span>
        <img style="width: auto;display: flex; margin: auto;" src="http://localhost:8080/pos/assets/images/pagoselectronicos/payphoneseguro.png" alt="">
        <div style="margin: auto;
  display: flex;
  justify-content: center;" id="pp-button"></div>

    </div>
    <div style="padding-bottom: 20px;" class="col-md-6">
        <span style="font-family: georgia, palatino, serif; color: #000000; font-size: 18pt;">PAYPAL</span>
        <img style="width: 500px;
  display: flex;
  margin: auto;
  height: auto;" src="http://localhost:8080/pos/assets/images/pagoselectronicos/paypal2.png" alt="">
        <p style="text-align: justify;"><span style="font-family: georgia, palatino, serif;">PayPal es un método de pago
                en línea que lo sigue a donde quiera que vaya.</span></p>
        <p style="text-align: justify;"><span style="font-family: georgia, palatino, serif;">Paga como quieras. Vincule
                sus tarjetas de crédito o debito a su billetera digital de PayPal y, cuando desee pagar, simplemente
                inicie sesión con su nombre de usuario y contraseña y elija cuál desea usar.</span></p>
        <h2 class="" style="text-align: center;"><span style="color: #000000;"><strong><span style="font-family: georgia, palatino, serif;">PROXIMAMENTE</span></strong></span></h2>
    </div>

    <hr>
    <h5 style="text-align: center;"><span style="font-family: georgia, palatino, serif;">PAGA CON CONFIANZA
            MEDIANTE</span></h5>
    <h2 class="" style="text-align: center;"><span style="color: #000000;"><strong><span style="font-family: georgia, palatino, serif;">TRANSFERENCIA BANCARIA</span></strong></span></h2>
    <p style="text-align: center;"><span style="font-family: georgia, palatino, serif;">A continuación te dejamos la
            lista de cuentas bancarias oficiales de nuestra empresa.</span></p>
    <div class="col-md-3 ">
        <div style="background: none;" class="card" style="max-width: 540px;">
            <div class="row no-gutters">
                <div class="col-md-4 border-warning">
                    <img style="height: 170px;display: flex;
  margin: auto; padding: 10px;" src="http://localhost:8080/pos/assets/images/pagoselectronicos/Pichincha.svg" alt="...">
                </div>
                <div class="col-md-8 border-warning">
                    <div style="text-align: center;" class="card-body ">
                        <h5 style="margin-bottom: auto;" class="card-title">CUENTA CORRIENTE</h5>
                        <p style="margin-bottom: auto; color: black;" class="card-text"> <strong> BANCO PICHINCHA
                            </strong></p>
                        <p style="margin-bottom: auto; color: black;" class="card-text"> <strong> #2100210466</strong>
                        </p>

                        <p style="margin-bottom: auto;" class="card-text"><small class="text-muted">HIDALGO FERNANDEZ
                                MARIO </small></p>
                        <p style="margin-bottom: auto;" class="card-text"><small class="text-muted">C.I.: 1206773036
                            </small></p>
                        <p style="margin-bottom: auto;" class="card-text"><small class="text-muted">EDESSISTORE@HOTMAIL.COM </small></p>

                    </div>
                </div>
            </div>
        </div>

    </div>

    <div class="col-md-3 ">
        <div style="background: none;" class="card" style="max-width: 540px;">
            <div class="row no-gutters">
                <div class="col-md-4 ">
                    <img style="height: 170px;display: flex;
  margin: auto; padding: 10px;" src="http://localhost:8080/pos/assets/images/pagoselectronicos/Pacifico.svg" alt="...">
                </div>
                <div class="col-md-8 ">
                    <div style="text-align: center;" class="card-body ">
                        <h5 style="margin-bottom: auto;" class="card-title">CUENTA AHORROS</h5>
                        <p style="margin-bottom: auto; color: black;" class="card-text"> <strong> BANCO PACIFICO
                            </strong></p>
                        <p style="margin-bottom: auto; color: black;" class="card-text"> <strong> #1051406950</strong>
                        </p>

                        <p style="margin-bottom: auto;" class="card-text"><small class="text-muted">HIDALGO FERNANDEZ
                                MARIO </small></p>
                        <p style="margin-bottom: auto;" class="card-text"><small class="text-muted">C.I.: 1206773036
                            </small></p>
                        <p style="margin-bottom: auto;" class="card-text"><small class="text-muted">EDESSISTORE@HOTMAIL.COM </small></p>

                    </div>
                </div>
            </div>
        </div>

    </div>

    <div class="col-md-3 ">
        <div style="background: none;" class="card" style="max-width: 540px;">
            <div class="row no-gutters">
                <div class="col-md-4 ">
                    <img style="height: 170px;display: flex;
  margin: auto; padding: 10px;" class="" src="http://localhost:8080/pos/assets/images/pagoselectronicos/Bolivariano.svg" alt="...">
                </div>
                <div style="text-align: center;" class="col-md-8">
                    <div style="text-align: center;" class="card-body ">
                        <h5 style="margin-bottom: auto;" class="card-title">CUENTA AHORROS</h5>
                        <p style="margin-bottom: auto; color: black;" class="card-text"> <strong> BANCO BOLIVARIANO
                            </strong></p>
                        <p style="margin-bottom: auto; color: black;" class="card-text"> <strong> #2001254831</strong>
                        </p>

                        <p style="margin-bottom: auto;" class="card-text"><small class="text-muted">HIDALGO FERNANDEZ
                                MARIO </small></p>
                        <p style="margin-bottom: auto;" class="card-text"><small class="text-muted">C.I.: 1206773036
                            </small></p>
                        <p style="margin-bottom: auto;" class="card-text"><small class="text-muted">EDESSISTORE@HOTMAIL.COM </small></p>

                    </div>
                </div>
            </div>
        </div>

    </div>

    <div class="col-md-3 ">
        <div style="background: none;" class="card" style="max-width: 540px;">
            <div class="row no-gutters">
                <div class="col-md-4 ">
                    <img style=" height: 170px;display: flex;
  margin: auto; padding: 10px;" src="http://localhost:8080/pos/assets/images/pagoselectronicos/Produbanco.svg" alt="...">
                </div>
                <div class="col-md-8 ">
                    <div style="text-align: center;" class="card-body ">
                        <h5 style="margin-bottom: auto;" class="card-title">CUENTA AHORROS</h5>
                        <p style="margin-bottom: auto; color: black;" class="card-text"> <strong> BANCO PRODUBANCO
                            </strong></p>
                        <p style="margin-bottom: auto; color: black;" class="card-text"> <strong> #12120169466</strong>
                        </p>

                        <p style="margin-bottom: auto;" class="card-text"><small class="text-muted">HIDALGO FERNANDEZ
                                MARIO </small></p>
                        <p style="margin-bottom: auto;" class="card-text"><small class="text-muted">C.I.: 1206773036
                            </small></p>
                        <p style="margin-bottom: auto;" class="card-text"><small class="text-muted">EDESSISTORE@HOTMAIL.COM </small></p>

                    </div>
                </div>
            </div>
        </div>

    </div>






</div>




<?php include_once 'views/templates/footer.php'; ?>

<script>
    /*  var parametros = {
        amount: "100",
        amountWithoutTax: "100",
        clientTransactionID: "p10",
        responseUrl: "http://localhost:8080/pos/payphone",
        cancellationUrl: "http://localhost:8080/pos/payphone"
    };

    $.ajax({
        data: parametros,
        url: 'https://pay.payphonetodoesposible.com/api/button/Prepare',
        type: 'POST',
        beforeSend: function(xhr) {
            xhr.setRequestHeader('Authorization', "Bearer u0BkuWe0Icd0w-i-BmtyAEGZMgYK0aTtZ0sbMsC0_w8DCfTY5NLH8SKwjd9O4rflBD9kW-ldz9nql_BCLPlgKb5HWfMgkvtq5GBHLS9uYpWmO8Dry5HOP68Yr-9RyAAOTZ6YNqk34X2cNij-2nH0U8u8w5QYdPYKOHkRfG5ZgIXqP0u7o8h-ba2eBvcONLXT42m1IdCZbslY2aCYqb_gK2NADKSzZYSvWO-RKnBnFY-FwtkKIAflfouDW4gk4AKTwb8XLRZj_K6OsY1uL8Y1F5FbusfbpiefjjeLORToJH9-pDa-U7u4aQ8c2SJdAjXQeX-wWQBlaue-RZfJJy0A33WLb7I")
        },
        success: function SolicitarPago(respuesta) {
         
            location.href = respuesta.payWithCard;
            
        },
        error: function(mensajeerror) {
            alert("Error en la llamada:" + mensajeerror.Message);
        }
    });*/
</script>
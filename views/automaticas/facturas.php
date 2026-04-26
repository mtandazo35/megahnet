<?php
$subtotaldoce     = 0;
$subtotaldocev     = 0;
$subtotalcero     = 0;
$descuento     = 0;
$subtotal     = 0;
$iva          = 0;
$impuesto     = 0;
$tl_sniva   = 0;
$total         = 0;
global $factura, $result_detalle, $result_config, $anulada;
//print_r($configuracion); 

?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $data['title']; ?></title>
    <style>
        @import url('fonts/BrixSansRegular.css');
        @import url('fonts/BrixSansBlack.css');

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        p,
        label,
        span,
        table {
            font-family: 'BrixSansRegular';
            font-size: 9pt;
        }

        .h2 {
            font-family: 'BrixSansBlack';
            font-size: 16pt;
        }

        .h3 {
            font-family: 'BrixSansBlack';
            font-size: 12pt;
            display: block;
            background: #0a4661;
            color: #FFF;
            text-align: center;
            padding: 3px;
            margin-bottom: 5px;

        }

        #page_pdf {
            width: 95%;
            margin: 15px 10px 10px auto;
        }

        #factura_head,
        #factura_cliente,
        #factura_detalle {
            width: 95%;
            margin-bottom: 10px;
        }

        .info_empresa {
            width: 50%;
            text-align: center;
        }

        .info_factura {
            width: 25%;
        }

        .info_cliente {
            width: 95%;
        }

        .datos_cliente {
            width: 95%;
        }

        .datos_cliente tr td {
            width: 50%;
        }

        .datos_cliente {
            padding: 10px 10px 0 10px;
        }

        .datos_cliente label {
            width: 75px;
            display: inline-block;
        }

        .datos_cliente p {
            display: inline-block;
        }

        .textright {
            text-align: right;
        }

        .textleft {
            text-align: left;
        }

        .textcenter {
            text-align: center;
        }

        .round {
            border-radius: 10px;
            border: 1px solid #0a4661;
            overflow: hidden;
            padding-bottom: 15px;
        }

        .round p {
            padding: 0 15px;
        }

        #factura_detalle {
            border-collapse: collapse;
        }

        #factura_detalle thead th {
            background: #058167;
            color: #FFF;
            padding: 5px;
        }

        #detalle_productos tr:nth-child(even) {
            background: #ededed;
        }

        #detalle_totales span {
            font-family: 'BrixSansBlack';
        }

        .nota {
            font-size: 8pt;
        }

        .label_gracias {
            font-family: verdana;
            font-weight: bold;
            font-style: italic;
            text-align: center;
            margin-top: 20px;
        }

        .anulada {
            position: absolute;
            left: 50%;
            top: 50%;
            transform: translateX(-50%) translateY(-50%);
        }

        b {
            font-weight: bold !important;

        }

        .enc {
            font-weight: bold !important;
            font-style: italic;
        }

        img {
            margin: 18px 10px 10px 10px;
            width: 200px;
            height: 130px;
            position: fixed;
            z-index: -150;
            padding-left: 25px;
        }
    </style>
</head>

<body>

    <div id="page_pdf">
    <img src="<?php echo BASE_URL . 'assets/images/Logo.jpg'; ?>" alt="">

        <table id="factura_head">
            <tr>
                
                <td class="logo_factura">

                </td>
                <td class="info_empresa">
                    <?php
                    if ($data['empresa'] > 0) {
                        $iva = $data['empresa']['impuesto'];
                    ?>
                        <div>
                            <span class="h2"><?php echo strtoupper($data['empresa']['nombre']); ?></span>
                            <p><?php echo $data['empresa']['razon_social']; ?></p>
                            <p><?php echo $data['empresa']['direccion']; ?></p>
                            <p>Ruc: <?php echo $data['empresa']['ruc']; ?></p>
                            <p>Teléfono: <?php echo $data['empresa']['telefono']; ?></p>
                            <p>Email: <?php echo $data['empresa']['correo']; ?></p>
                        </div>
                    <?php
                    }
                    ?>
                </td>
                <td class="info_factura">
                    <div class="round">
                        <span class="h3 enc"> Facturable</span>
                        <p>N°: <strong><?php echo $data['ordenventa']['serie']; ?></strong></p>
                        <p>Fecha: <?php echo $data['ordenventa']['fecha']; ?></p>
                        <p>Hora: <?php echo $data['ordenventa']['hora']; ?></p>
                        <p>Responsable: <?php echo $data['ordenventa']['responsable']; ?></p>

                    </div>
                </td>
            </tr>
        </table>
        <table id="factura_cliente">
            <tr>
                <td class="info_cliente">
                    <div class="round">
                        <span class="h3 enc">Cliente </span>
                        <table class="datos_cliente">
                            <tr>
                                <td>
                                <strong><?php echo $data['ordenventa']['identidad'] ?>: </strong>
                <p><?php echo $data['ordenventa']['num_identidad'] ?></p>
                                </td>
                                <td>
                                    <strong>Razon Social: </strong>
                                    <p><?php echo $data['ordenventa']['nombre'] ?></p>
                                </td>
                            </tr>
                            <tr>
                                <td>
                                    <strong>Teléfono: </strong>
                                    <p><?php echo $data['ordenventa']['telefono'] ?></p>
                                </td>
                                <td>
                                    <strong>Dirección: </strong>
                                    <p><?php echo $data['ordenventa']['direccion'] ?></p>
                                </td>
                            </tr>
                        </table>
                    </div>
                </td>

            </tr>
        </table>

        <table id="factura_detalle">
            <thead>
                <tr>
                    <th width="50px">Cant.</th>
                    <th class="textleft">Descripción</th>
                    <th class="textright" width="150px">Precio Unitario.</th>
                    <th class="textright" width="150px"> Precio Total</th>
                </tr>
            </thead>
            <tbody id="detalle_productos">

                <?php
                $productos = json_decode($data['ordenventa']['productos'], true);

                if ($productos > 0) {

                    foreach ($productos as $row) {
                        //$codproducto = $row['id'];

                        /* $query_iva = mysqli_query($conection, "SELECT iva FROM producto WHERE idproducto= $codproducto ");
                        $result_iva = mysqli_fetch_array($query_iva);*/
                ?>
                        <tr>
                            <td class="textcenter"><?php echo $row['cantidad']; ?></td>
                            <td><?php echo $row['nombre']; ?></td>

                            <?php if ($row['iva_producto'] == $iva) {
                                $pv = round($row['precio'] / (CONCAT . $iva), 4);
                                $pt = round($pv * $row['cantidad'], 4);

                            ?>
                                <td class="textright"><?php echo number_format($pv, 4, '.', ','); ?></td>
                                <td class="textright"><?php echo number_format($pt, 2, '.', ','); ?></td>
                            <?php
                            } else { ?>

                                <td class="textright"><?php echo number_format(round($row['precio'], 4), 4, '.', ','); ?></td>
                                <td class="textright">
                                    <?php echo number_format(round($row['precio'] * $row['cantidad'], 4), 2, '.', ','); ?></td>
                        </tr>
            <?php }


                            if ($row['iva_producto'] == $iva) {
                                $precio_total = round(($pv / (CONCAT . $iva)), 4);
                                $subtotaldocev = round($subtotaldocev + $pt, 4);
                                //print_r($subtotaldoce); exit;
                            } else {

                                $precio_total = $row['precio'] *  $row['cantidad'];
                                $subtotalcero = round($subtotalcero + $precio_total, 4);
                            }
                        }
                    }

                    $subtotaldoce = round($subtotaldocev, 2);

                    $descuento =    round($data['ordenventa']['descuento'], 2);
                    $subtotal = round($subtotaldoce + $subtotalcero, 2);
                    $impuesto     = round($subtotaldocev  * ($iva / 100), 2);
                    //$tl_sniva 	= round($subtotaldoce - $impuesto, 2);



                    $total         = round(($subtotal + $impuesto) - $descuento, 2);
            ?>

            </tbody>


            <tfoot id="detalle_totales">

                <tr>
                    <td colspan="3" class="textright"><span><b> SUBTOTAL <?= $iva?>%<b></span></td>
                    <td class="textright"><span><?php echo number_format($subtotaldoce, 2); ?></span></td>
                </tr>
                <tr>
                    <td colspan="3" class="textright"><span><b>SUBTOTAL 0%<b></span></td>
                    <td class="textright"><span><?php echo number_format($subtotalcero, 2); ?></span></td>
                </tr>
                <tr>
                    <td colspan="3" class="textright"><span><b>DESCUENTO<b></span></td>
                    <td class="textright"><span><?php echo number_format($descuento, 2); ?></span></td>
                </tr>
                <tr>
                    <td colspan="3" class="textright"><span><b>SUBTOTAL<b></span></td>
                    <td class="textright"><span><?php echo number_format($subtotal, 2); ?></span></td>
                </tr>
                <tr>
                    <td colspan="3" class="textright"><span><b>IVA (<?php echo $iva; ?> %)<b></span></td>
                    <td class="textright"><span><?php echo number_format($impuesto, 2); ?></span></td>
                </tr>
                <tr>
                    <td colspan="3" class="textright"><span><b>TOTAL<b> </span></td>
                    <td class="textright"><span><?php echo number_format($total, 2); ?></span></td>
                </tr>
            </tfoot>
        </table>
        <p class="nota">PAGOS A NOMBRE DE: <strong> GOBRAVCORP </strong></p>
            <p class="nota">RUC: <strong> 1291737931001 </strong></p>
            <p class="nota">CHEQUE O TRANSFERENCIA</p>
            <p class="nota">CTA CTE PICHINCHA: <strong> 2100159721 </strong></p>
            <p class="nota">CTA CTE GUAYAQUIL: <strong> 8737835 </strong></p>
        <br>
        <div>
            <p class="nota">Si usted tiene preguntas sobre esta Orden Venta, <br>pongase en contacto con nombre, teléfono
                y Email.</p>
            <h4 class="label_gracias"><span><?php echo $data['empresa']['mensaje']; ?></span></h4>
        </div>
        <div>
            <?php if ($data['ordenventa']['estado'] == 0){
                
                ?>
            <p  ><span style="font-family: verdana;
            font-weight: bold;
            font-style: italic;
            text-align: center !important;
            font-size: 30px !important;
            margin-top: 10px;
            display: flex;
            ">DOCUMENTO ANULADO</span></p>
<img class="" style="width:400px; height: 400px; position: fixed; z-index:-150; display:block; margin-left:200px;  margin-top:100px;" src="<?php echo BASE_URL . 'assets/images/anulado.png'; ?>" alt="">

<?php  }?>
           
        </div>
    </div>
</body>

</html>





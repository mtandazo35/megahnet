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
$cons = 0;
global $result_detalle, $result_config, $anulada;
//print_r($data['venta']); exit;
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport"
        content="width=device-width, user-scalable=no, initial-scale=1.0, maximum-scale=1.0, minimum-scale=1.0">
    <title></title>
    <style>
    /*arriba- izquierda -abajo- derecha*/
    * {
        margin: 10px 5px 0px 0px;

        font-size: 11px;
        font-family: Tahoma;
    }

    .fac {
        padding-left: 260px;
        padding-top: 0px;

    }

    .nom {
        padding-left: 110px;
        padding-top: 70px;


    }


    .fec {
        padding-left: 175px;
    }

    .ced {
        padding-left: 128px;
        padding-top: 0px;
    }

    .tel {
        padding-left: 200px;

        padding-top: 0px;
    }

    .dir {
        padding-left: 128px;
        padding-top: -0px;
        padding-bottom: 10px;
    }

    /*estilo para el detalle producto */
    .can {
        padding-left: 55px;

    }

    .des {
        padding-left: 25px;
    }

    .pvp {
        padding-left: 300px;
    }

    .pvt {
        padding-left: 30px;
        /*maximo 670px*/
    }

    /*estilo para el detalle totales */
    .subdoce {

        padding-left: 690px;
        padding-top: -50px;
        /*maximo 62px*/
    }

    .subcero {

        padding-left: 690px;
        padding-top: -10px;
    }

    .desc {

        padding-left: 690px;
        padding-top: -10px;
    }

    .sub {

        padding-left: 690px;
        padding-top: -10px;
    }

    .iva {

        padding-left: 690px;
        padding-top: -10px;
    }

    .tot {

        padding-left: 690px;
        padding-top: -10px;
    }

    .h4 {
        font-size: 12px;
        font-family: Arial;
    }

    .br {
        padding-top: 6px;

    }
    </style>

</head>

<body>
    <?php 
    
    if($data['venta']['estado'] == 0){
        $anulada = '<img class="anulada" style="width:300px; position: fixed; z-index:-150; display:block; margin-left:200px;  margin-top:90px;" src="'.BASE_URL.'assets/images/anulado.png" alt="Anulada">';
    }
    
    echo $anulada; ?>
    <div id="page_pdf">

        <table>
            <tr>
                <td class="nom h4"><?php echo $data['venta']['nombre']; ?></td>

            </tr>
            <tr>
                <td class="fec h4"><?php echo $data['venta']['fecha'], ' ', $data['venta']['hora']; ?></td>
                <td class="fac h4"><?php echo 'ControlInt:' . ' ' . $data['venta']['serie']; ?></td>

            </tr>

            <tr>
                <td class="ced h4"><?php echo $data['venta']['num_identidad']; ?></td>

            </tr>
            <tr>
                <td class="dir h4"><?php echo $data['venta']['direccion']; ?></td>
                <td class="tel h4"><?php echo $data['venta']['telefono']; ?></td>

            </tr>

        </table>



        <table id="factura_detalle">
            <thead>

            </thead>
            <tbody id="detalle_productos">

                <?php

                $productos = json_decode($data['venta']['productos'], true);
                $iva = $data['empresa']['impuesto'];

                if ($productos > 0) {

                    foreach ($productos as $producto) {
                        $cons = +$cons + 1;    ?>
                <tr>
                    <td class="can h4"><?php echo $producto['cantidad']; ?></td>

                    <td class="des h4"> <?php echo $producto['nombre'] . '(' . $producto['codigobarra'] . ')'; ?></td>

                    <?php if ($producto['iva_producto'] == $iva) {
                                $pv = round($producto['precio'] / (CONCAT . $iva), 4);
                                $pt = round($pv * $producto['cantidad'], 4);

                            ?>

                    <td class="pvp h4"><?php echo number_format($pv, 4, '.', '.'); ?></td>

                    <td class="pvt h4"><?php echo number_format($pt, 4, '.', '.'); ?></td>
                </tr>
                <?php
                            } else { ?>


                <td class="pvp h4"><?php echo number_format(round($producto['precio'], 4), 4, '.', '.'); ?></td>

                <td class="pvt h4">
                    <?php echo number_format(round($producto['precio'] * $producto['cantidad'], 4), 4, '.', '.'); ?>
                </td>
                <?php }


                            //print_r($result_iva); exit;
                            if ($producto['iva_producto'] == $iva) {
                                $precio_total = round(($pt / (CONCAT . $iva)), 4);
                                $subtotaldocev = round($subtotaldocev + $pt, 4);
                            } else {

                                $precio_total = $producto['precio'] *  $producto['cantidad'];
                                $subtotalcero = round($subtotalcero + $precio_total, 4);
                            }
                        }
                    }
                    while ($cons < $data['empresa']['totalitems']) : ?>
                <tr>
                    <td class="can h4"><?php echo "-" ?></td>
                </tr>
                <?php $cons++;
                    endwhile;

                    $subtotaldoce = round($subtotaldocev, 2);

                    $descuento = 0; // round($data['venta']['descuento'], 2);
                    $subtotal = round($subtotaldoce + $subtotalcero, 2);
                    $impuesto     = round($subtotaldocev  * ($iva / 100), 2);
                    //$tl_sniva 	= round($subtotaldoce - $impuesto, 2);



                    $total         = round(($subtotal + $impuesto) - $descuento, 2);
                    //print_r($iva); exit;
            ?>

            </tbody>

        </table>


        <table>
            <thead>
                <tr>
                    <td class="textright subdoce h4"><span><?php echo number_format($subtotaldoce, 2); ?></span></td>
                </tr>
                <tr>
                    <td class="textright subcero h4"><span><?php echo number_format($subtotalcero, 2); ?></span></td>
                </tr>
                <tr>
                    <td class="textright desc h4"><span><?php echo number_format($descuento, 2); ?></span></td>
                </tr>
                <tr>
                    <td class="textright sub h4"><span><?php echo number_format($subtotal, 2); ?></span></td>
                </tr>
                <tr>
                    <td class="textright iva h4"><span><?php echo number_format($impuesto, 2); ?></span></td>
                </tr>
                <tr>
                    <td class="textright tot h4"><span><?php echo number_format($total, 2); ?></span></td>
                </tr>
            </thead>          
        </table>


    </div>

</body>

</html>
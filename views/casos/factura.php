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
                        <span class="h3 enc"> Caso </span>
                        <p>N°: <strong><?php echo $data['caso']['id']; ?></strong></p>
                        <p>Fecha: <?php echo $data['caso']['fecha']; ?></p>
                        <p>Hora: <?php echo $data['caso']['hora']; ?></p>
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
                                    <strong><?php echo $data['caso']['identidad'] ?>: </strong>
                                    <p><?php echo $data['caso']['num_identidad'] ?></p>
                                </td>
                                <td>
                                    <strong>Razon Social: </strong>
                                    <p><?php echo $data['caso']['nombre'] ?></p>
                                </td>
                                <td>
                                    <strong>Teléfono: </strong>
                                    <p><?php echo $data['caso']['telefono'] ?></p>
                                </td>
                            </tr>
                            <tr>
                               
                                <td>
                                    <strong>Dirección: </strong>
                                    <p><?php echo $data['caso']['direccion'] ?></p>
                                </td>
                                <td>
                                    <strong>Coordenada: </strong>
                                    <p><?php echo $data['caso']['coordenada'] ?></p>
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
                    <th class="textleft" >Problema Reportado</th>
                    <th class="textleft">Trabajo Realizado</th>
                    <th class="textright" width="150px">Observacion</th>
                </tr>
            </thead>
            <tbody id="detalle_productos">


                <tr>
                    <td class="textleft"><?php echo $data['caso']['problema_reportado']; ?></td>
                    <td class="textleft"><?php echo $data['caso']['trabajo_realizado']; ?></td>
                    <td class="textleft"><?php echo $data['caso']['observacion']; ?></td>

                </tr>


            </tbody>
         
            
        </table>
        <table id="factura_detalle">
                <thead>
                    <tr>
                        <th width="textleft">Empleado</th>
                        <th class="textleft">Zona</th>

                    </tr>
                </thead>
                <tbody id="detalle_productos">

                    <?php
                    $info = $data['grupoTrabajo'];

                    if ($info > 0) {

                        foreach ($info as $row) {
                            //$codproducto = $row['id'];

                            /* $query_iva = mysqli_query($conection, "SELECT iva FROM producto WHERE idproducto= $codproducto ");
                        $result_iva = mysqli_fetch_array($query_iva);*/
                    ?>
                            <tr>
                                <td class="textleft"><?php echo $row['empleado']; ?></td>
                                <td class="textleft"><?php echo $row['descripcion']; ?></td>



                            </tr>
                    <?php


                        }
                    }


                    ?>

                </tbody>



            </table>
        <div>

        </div>
        <br>
        <div>

            <h4 class="label_gracias"><span><?php echo $data['empresa']['mensaje']; ?></span></h4>
        </div>

    </div>
</body>

</html>
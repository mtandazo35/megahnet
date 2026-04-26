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
                        <span class="h3 enc"> Reporte </span>

                        <p>Fecha: <?php echo date('Y-m-d'); ?></p>
                        <p>Hora: <?php echo date('H:i:s'); ?></p>
                    </div>
                </td>
            </tr>
        </table>
        <table id="factura_cliente">
            <tr>
                <td class="info_cliente">
                    <div class="round">
                        <span class="h3 enc">Valores Facturados </span>
                        <table class="datos_cliente">
                            <tr>

                                <td>
                                    <strong>Total Facturas: </strong>
                                    <p><?php echo '$' . number_format($data['totalFacturas'], 2, '.', ',')  ?></p>
                                </td>
                                <td>
                                    <strong>total ordenes: </strong>
                                    <p><?php echo '$' . number_format($data['totalOrdenes'], 2, '.', ',') ?></p>
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
                    <th width="100px">Factura</th>
                    <th class="textleft">Cliente</th>
                    <th class="textright" width="150px"> Precio </th>
                </tr>
            </thead>
            <tbody id="detalle_productos">

                <?php

                //  print_r($data['infoOrdenes']);exit;
                $infoFacturas = json_decode($data['infoFacturas'], true);

                if ($infoFacturas > 0) {

                    foreach ($infoFacturas as $row) {
                        //$codproducto = $row['id'];

                        /* $query_iva = mysqli_query($conection, "SELECT iva FROM producto WHERE idproducto= $codproducto ");
                        $result_iva = mysqli_fetch_array($query_iva);*/
                ?>
                        <tr>
                            <td class="textcenter"><?php echo $row['orden_no']; ?></td>
                            <td><?php echo $row['cliente']; ?></td>


                            <td class="textright"><?php echo number_format($row['totalfactura'], 2, '.', ','); ?></td>
                    <?php

                    }
                }

                    ?>

            </tbody>
        </table>
        <table id="factura_detalle">
            <thead>
                <tr>
                    <th width="100px">Orden Venta</th>
                    <th class="textleft">Cliente</th>
                    <th class="textright" width="150px"> Precio </th>
                </tr>
            </thead>
            <tbody id="detalle_productos">

                <?php

                //  print_r($data['infoOrdenes']);exit;
                $infoOrdenes = json_decode($data['infoOrdenes'], true);

                if ($infoOrdenes > 0) {

                    foreach ($infoOrdenes as $row) {
                        //$codproducto = $row['id'];

                        /* $query_iva = mysqli_query($conection, "SELECT iva FROM producto WHERE idproducto= $codproducto ");
                        $result_iva = mysqli_fetch_array($query_iva);*/
                ?>
                        <tr>
                            <td class="textcenter"><?php echo $row['serie']; ?></td>
                            <td><?php echo $row['nombre']; ?></td>


                            <td class="textright"><?php echo number_format($row['total'], 2, '.', ','); ?></td>
                    <?php

                    }
                }

                    ?>

            </tbody>
        </table>        
       
        <div>
           
            <h4 class="label_gracias"><span><?php echo $data['empresa']['mensaje']; ?></span></h4>
        </div>

    </div>
</body>

</html>
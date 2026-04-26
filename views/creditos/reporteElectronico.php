
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
        .title{
    text-align: center;
    font-size: 16px;
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
        .mensaje{
    margin-top: 10px;
    font-size: 13px;
    text-align: center;
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
            margin: 15px 10px 10px auto;
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
            <table id="factura_head">
                <tr>
                    <div>
                        <img src="<?php echo BASE_URL . 'assets/images/Logo.jpg'; ?>" alt="">
                    </div>
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
                            <span class="h3 enc"> Credito </span>
                            <p>N°: <strong><?php echo $data['creditoE'][0]['id']; ?></strong></p>
                            <p>Fecha: <?php echo $data['creditoE'][0]['fecha']; ?></p>
                            <p>Hora: <?php echo $data['creditoE'][0]['hora']; ?></p>
                        </div>
                    </td>
                </tr>
            </table>
            <table id="factura_cliente">
                <tr>
                    <td class="info_cliente">
                        <div class="round">
                            <span class="h3 enc">Datos del Cliente </span>
                            <table class="datos_cliente">
                                <tr>
                                    <td>
                                        <strong>Cédula/Ruc: </strong>
                                        <p><?php echo $data['creditoE'][0]['num_identidad'] ?></p>
                                    </td>
                                    <td>
                                        <strong>Razon Social: </strong>
                                        <p><?php echo $data['creditoE'][0]['nombre'] ?></p>
                                    </td>
                                </tr>
                                <tr>
                                    <td>
                                        <strong>Teléfono: </strong>
                                        <p><?php echo $data['creditoE'][0]['telefono'] ?></p>
                                    </td>
                                    <td>
                                        <strong>Dirección: </strong>
                                        <p><?php echo $data['creditoE'][0]['direccion'] ?></p>
                                    </td>
                                </tr>
                            </table>
                        </div>
                    </td>

                </tr>
            </table>
            <h5 class="title">Detalle de los Productos</h5>

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
                   $productos = $data['creditoE'];

                   // print_r($data['creditoE']);

                    foreach ($data['creditoE'] as $producto) {
                    //print_r($producto); exit;
                    
                    ?>
                    <tr>
                        <td class="textcenter"><?php echo $producto['cantidad'][0]; ?></td>
                        <td><?php echo $producto['item']; ?></td>
                        <td class="textright"><?php echo number_format($producto['precio_u']*  (CONCAT . $iva), 2); ?></td>
                        <td class="textright"><?php echo number_format($producto['cantidad'] * ($producto['precio_u'] * (CONCAT . $iva))   , 2); ?></td>
                    </tr>

                    <?php  }  ?>
                    <tr class="total">
                        <td class="textright" colspan="3">Monto</td>
                        <td class="textright"><?php echo number_format($data['creditoE'][0]['monto'], 2); ?></td>
                    </tr>
                </tbody>

            </table>
            <h5 class="title">Detalle de los Abonos</h5>

            <table id="factura_detalle">
                <thead>
                    <tr>
                        <th >Fecha</th>
                        <th>Abono</th>
                    </tr>
                </thead>
                <tbody id="detalle_productos">

                    <?php
                    $abonado = 0;
                    foreach ($data['abonos'] as $abono) {
                        $abonado += $abono['abono'];

                    ?>
                    <tr>
                    <td class="textleft"><?php echo $abono['fecha'].' <strong>NUMERO COMPROBANTE </strong> '.$abono['codigo_pago'].' <strong> TIPO DE PAGO </strong>'.$abono['tipo_pago']; ?></td>
                        <td class="textright"><?php echo number_format($abono['abono'], 2); ?></td>
                    </tr>
                    <?php } ?>
                    <tr class="total">
                        <td class="textright">Abonado</td>
                        <td class="textright"><?php echo number_format($abonado, 2); ?></td>
                    </tr>
                    <tr class="total">
                        <td class="textright">Restante</td>
                        <td class="textright"><?php echo number_format($data['creditoE']['0']['monto'] -  $abonado, 2); ?>
                        </td>
                    </tr>
                </tbody>

            </table>

           


            <div class="mensaje">
                <?php echo $data['empresa']['mensaje']; ?>
                <?php if ($data['creditoE']['0']['estado'] == 0) { ?>
                <h1>CREDITO FINALIZADO</h1>
                <?php } else { ?>
                <h1>CREDITO PENDIENTE</h1>
                <?php } ?>
            </div>

        </div>
    </body>

    </html>
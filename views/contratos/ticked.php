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
    <link rel="stylesheet" href="<?php echo BASE_URL . 'assets/css/ticked.css'; ?>">
</head>

<body>
    <div class="datos-empresa">
    <?php   $iva = $data['empresa']['impuesto'];?>

        <p><?php echo $data['empresa']['nombre']; ?></p>
        <p><?php echo $data['empresa']['razon_social']; ?></p>

        <p><?php echo $data['empresa']['telefono']; ?></p>
        <p><?php echo $data['empresa']['direccion']; ?></p>
    </div>
    <h2 class="title">Cotización Ticked</h2>

    <h5 class="title">Datos del Cliente</h5>
    <div class="datos-info">
    <p><strong> Cotización N°: </strong><?php echo $data['cotizacion']['id']; ?></p>
        <p><strong> Fecha y Hora: </strong><?php echo $data['cotizacion']['fecha'].' '.$data['cotizacion']['hora']; ?></p>
        <p><strong><?php echo $data['cotizacion']['identidad']; ?>: </strong> <?php echo $data['cotizacion']['num_identidad']; ?></p>
        <p><strong>Nombre: </strong> <?php echo $data['cotizacion']['nombre']; ?></p>
        <p><strong>Teléfono: </strong> <?php echo $data['cotizacion']['telefono']; ?></p>
    </div>
    <h5 class="title">Detalle de los Productos</h5>
    <table>
        <thead>
            <tr>
                <th>Cant</th>
                <th>Descripción</th>
                <th>Precio</th>
                <th>SubTotal</th>
            </tr>
        </thead>
        <tbody >
            <?php
            $productos = json_decode($data['cotizacion']['productos'], true);
            foreach ($productos as $producto) { ?>
                <tr>
                    <td><?php echo $producto['cantidad']; ?></td>
                    <td><?php echo $producto['nombre']; ?></td>
                   
                    <?php if ($producto['iva_producto'] == $iva) {
                                $pv = round($producto['precio'] / (CONCAT.$iva), 4);
                                $pt = round($pv * $producto['cantidad'], 4);

                            ?>
                                <td class="textright"><?php echo number_format($pv ,4,'.',','); ?></td>
                                <td class="textright"><?php echo number_format($pt ,2,'.',','); ?></td>
                            <?php
                            } else { ?>

                                <td class="textright"><?php echo number_format(round($producto['precio'], 4) ,4,'.',','); ?></td>
                                <td class="textright"><?php echo number_format(round($producto['precio'] * $producto['cantidad'], 4) ,2,'.',','); ?></td>
                        </tr>
            <?php }


                            if ($producto['iva_producto'] == $iva) {
                                $precio_total = round(($pv / (CONCAT.$iva)), 4);
                                $subtotaldocev = round($subtotaldocev + $pt, 4);
                                //print_r($subtotaldoce); exit;
                            } else {

                                $precio_total = $producto['precio'] *  $producto['cantidad'];
                                $subtotalcero = round($subtotalcero + $precio_total, 4);
                            }
                        }                    

                    $subtotaldoce = round($subtotaldocev, 2);

                    $descuento =  0; //  round($factura['descuento'], 2);
                    $subtotal = round($subtotaldoce + $subtotalcero, 2);
                    $impuesto     = round($subtotaldocev  * ($iva / 100), 2);
                    //$tl_sniva 	= round($subtotaldoce - $impuesto, 2);



                    $total         = round(($subtotal + $impuesto) - $descuento, 2);
            ?>
            
             </tbody>
             <tfoot id="detalle_totales">

<tr>
    
        <td colspan="3" class="textright"><b> SUBTOTAL <?= $iva?>%<b></td>
        <td class="textright"><span><?php echo number_format($subtotaldoce, 2); ?></span></td>
    </tr>
    <tr >
        <td  colspan="3" class="textright"><b>SUBTOTAL 0%<b></td>
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
    <div class="mensaje">
        <h4>Validez: <?php echo $data['cotizacion']['validez'] ?></h4>
        <h4>Metodo: <?php echo $data['cotizacion']['metodo'] ?></h4>
        <?php echo $data['empresa']['mensaje']; ?>
    </div>

</body>

</html>
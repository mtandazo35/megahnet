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
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $data['title']; ?></title>
    <link rel="stylesheet" href="<?php echo BASE_URL . 'assets/css/ticked.css'; ?>">
</head>

<body>
    <div class="datos-empresa">
        <p><?php echo $data['empresa']['nombre']; ?></p>
        <p><?php echo $data['empresa']['razon_social']; ?></p>
        <p><?php echo $data['empresa']['telefono']; ?></p>
        <p><?php echo $data['empresa']['direccion']; ?></p>
        <p><?php echo $data['empresa']['correo']; ?></p>

    </div>
    <h2 class="title">Factura Fisica</h2>

    <h5 class="title">Datos del Cliente</h5>
    <div class="datos-info">
    <p><strong>Serie: </strong> <?php echo $data['venta']['serie']; ?></p>

        <p><strong><?php echo $data['venta']['identidad']; ?>: </strong> <?php echo $data['venta']['num_identidad']; ?></p>
        <p><strong>Nombre: </strong> <?php echo $data['venta']['nombre']; ?></p>
        <p><strong>Teléfono: </strong> <?php echo $data['venta']['telefono']; ?></p>
    </div>
    <h5 class="title">Detalle de los Productos</h5>
    <table>
        <thead>
            <tr>
                <th>Cant</th>
                <th>Descripción</th>
                <th>Prec. Uni.</th>
                <th>Prec. Tot</th>
            </tr>
        </thead>
        <tbody>
            <?php
            $productos = json_decode($data['venta']['productos'], true);
            $iva = $data['empresa']['impuesto'];

            foreach ($productos as $producto) { ?>
                <tr>
                    <td><?php echo $producto['cantidad']; ?></td>
                    <td><?php echo $producto['nombre']; ?></td>
                    <?php if ($producto['iva_producto'] == 12) {
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
                    if ($producto['iva_producto'] == 12) {
                        $precio_total = round(($pt / (CONCAT . $iva)), 4);
                        $subtotaldocev = round($subtotaldocev + $pt, 4);
                    } else {

                        $precio_total = $producto['precio'] *  $producto['cantidad'];
                        $subtotalcero = round($subtotalcero + $precio_total, 4);
                    }

                    $subtotaldoce = round($subtotaldocev, 2);

                    $descuento = 0; // round($data['venta']['descuento'], 2);
                    $subtotal = round($subtotaldoce + $subtotalcero, 2);
                    $impuesto     = round($subtotaldocev  * ($iva / 100), 2);
                    //$tl_sniva 	= round($subtotaldoce - $impuesto, 2);



                    $total         = round(($subtotal + $impuesto) - $descuento, 2);
                    //print_r($iva); exit;
            ?>

            </tr>
        <?php } ?>
        <tr>
            <td class="text-right" colspan="3">SubTotal <?php echo $iva; ?>%</td>
            <td class="text-right"><?php echo number_format($subtotaldoce, 2); ?></td>
        </tr>
        <tr>
            <td  class="text-right" colspan="3">SubTotal 0%</td>
            <td  class="text-right"><?php echo number_format( $subtotalcero, 2); ?></td>
        </tr>
        <tr>
            <td class="text-right" colspan="3">Descuento</td>
            <td class="text-right"><?php echo number_format($data['venta']['descuento'], 2); ?></td>
        </tr>
        <tr>
            <td class="text-right" colspan="3">subTotal</td>
            <td class="text-right"><?php echo number_format($subtotal , 2); ?></td>
        </tr>
        <tr>
            <td class="text-right" colspan="3">iva</td>
            <td class="text-right"><?php echo number_format($impuesto , 2); ?></td>
        </tr>
        <tr>
            <td class="text-right" colspan="3">Total </td>
            <td class="text-right"><?php echo number_format($data['venta']['total'] - $data['venta']['descuento'], 2); ?></td>
        </tr>
        </tbody>
    </table>
    <div class="mensaje">
        <h4><?php echo $data['venta']['metodo'] ?></h4>
        <?php echo $data['empresa']['mensaje']; ?>
        <?php if ($data['venta']['estado'] == 0) { ?>
            <h1>Venta Anulado</h1>
        <?php } ?>
    </div>

</body>

</html>
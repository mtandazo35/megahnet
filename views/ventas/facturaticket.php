<?php



//print_r($data['factura']); exit;

$subtotaldoce 	= 0;
$subtotaldocev 	= 0;
$subtotalcero 	= 0;
$descuento 	= 0;
$subtotal 	= 0;
$iva 	 	= 0;
$impuesto 	= 0;
$tl_sniva   = 0;
$total 		= 0;
//print_r($data['empresa'];); 
?>
<!DOCTYPE html>
<html lang="en">


<head>
	<meta charset="UTF-8">
	<meta http-equiv="X-UA-Compatible" content="IE=edge">
	<meta name="viewport" content="width=device-width, user-scalable=no, initial-scale=1.0, maximum-scale=1.0, minimum-scale=1.0">
	<title></title>

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
			font-family: Arial;
			font-size: 9pt;
		}

		.h2 {
			font-family: Arial;
			font-size: 16pt;
		}

		.h3 {
			font-family: Arial;
			font-size: 12pt;
			display: block;

			color: #000;
			text-align: center;
			padding: 3px;
			margin-bottom: 5px;

		}

		#page_pdf {
			width: 100%;

			margin: 15px 30px 10px auto;
		}

		#factura_head,
		#factura_cliente,
		#factura_detalle {
			width: 100%;
			margin-bottom: 10px;
		}

		.info_empresa {
			width: 100%;

			text-align: center;
		}

		.info_factura {
			width: 100%;
			text-align: center;
		}

		.info_cliente {
			width: 100%;
			padding-left: 20px;

		}

		.datos_cliente {

			width: 85%;

		}

		.datos_cliente tr td {
			width: 85%;
		}

		.datos_cliente label {

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


		#factura_detalle {
			border-collapse: collapse;
			width: 90%;
			font-size: 8pt;
		}

		#factura_detalle thead th {
			width: 10%;
			color: #000;
			padding: 5px;
		}

		#detalle_productos tr:nth-child(even) {
			background: #ededed;
		}

		#detalle_totales span {
			font-family: Arial;
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
			width: 100%;
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


		.hr {
			width: 125% !important;
		}


		.footer {
			width: 100%;
			padding-left: 20px;
		}
	</style>
</head>

<body>
	<div id="page_pdf">
		<table id="factura_head">
			<tr>

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
							<p>Obligado a llevar Contabilidad: <?php echo $data['empresa']['contabilidad']; ?></p>
						</div>
						<br>
						<hr class="hr">
						<div>
							<span class="h3 enc">Datos Factura Electrónica </span>
							<?php
							$numero = $data['factura']['orden_no'];
							$nofactura = str_pad($numero, 9, "0", STR_PAD_LEFT);
							?>
							<p>No. Factura: <strong><?php echo $data['empresa']['establecimiento'] . '-' . $data['empresa']['puntoemi'] . '-' . $nofactura; ?></strong></p>
							<p>Fecha Autorizada: <?php echo  $data['factura']['fecha']; ?></p>
						</div>

					<?php
					}
					?>
				</td>

			</tr>
		</table>

		<hr class="hr">
		<table id="factura_cliente">
			<tr>
				<td class="info_cliente">

					<span class="h3 enc">Cliente </span>


					<p>Razon Social: <?php echo $data['factura']['cliente']; ?></p>
					<p>Cedula/Ruc:<?php echo $data['factura']['ruc']; ?></p>
					<p>Teléfono:<?php echo $data['factura']['telefono']; ?></p>
					<p>Dirección:<?php echo $data['factura']['direccion']; ?></p>
				</td>

			</tr>
		</table>
		<hr class="hr">
		<table id="factura_detalle">
			<thead>
				<tr>
					<th width="50px">Cant.</th>
					<th  class="textleft">Des.</th>
					<th class="textright" width="150px">Precio Uni.</th>
					<th class="textright" width="150px"> Precio Tot.</th>
				</tr>
			</thead>
			<tbody id="detalle_productos">

				<?php

				if ($data['result_detalle'] > 0) {


					foreach ($data['result_detalle'] as $row) {
						# code...

						//while ($row = mysqli_fetch_array($data['result_detalle'])) {
						// $codproducto= $row['idproducto'];
						// $query_iva = mysqli_query($conection, "SELECT iva FROM producto WHERE idproducto= $codproducto ");
						//$result_iva = mysqli_fetch_array($query_iva);
				?>
						<tr>
							<td width="60px"  class="textcenter"><?php echo $row['cantidad']; ?></td>
							<td class="textleft" width="150px"><?php echo $row['item']; ?></td>


							<?php if ($row['iva'] == $iva) {
								$pv = round($row['precio_u'], 4);
								$pt = round($pv * $row['cantidad'], 4);

							?>
								<td class="textright" ><?php echo number_format($pv,4,'.',','); ?></td>
								<td class="textright" ><?php echo number_format($pt,4,'.',','); ?></td>
							<?php
							} else { ?>

								<td class="textright"><?php echo number_format(round($row['precio_u'], 4),4,'.',','); ?></td>
								<td class="textright"><?php echo number_format(round($row['total'], 4),4,'.',','); ?></td>
						</tr>
			<?php }



							//print_r($result_iva); exit;
							if ($row['iva'] == $iva) {
								//precio_total = round(($row['precio_u'] / 1.12), 2);
								$subtotaldocev = round($subtotaldocev + $pt, 4);
								//print_r($subtotaldoce); exit;
							} else {

								$precio_total = $row['total'];
								$subtotalcero = round($subtotalcero + $precio_total, 2);
							}
						}
					}

					$subtotaldoce = round($subtotaldocev, 4);

					$descuento = round($data['factura']['totaldescuento'], 2);
					$subtotal = round($subtotaldoce + $subtotalcero, 4);
					$impuesto 	= number_format(round($subtotaldocev  * ($iva / 100), 2),2,'.','');
					//$tl_sniva 	= round($subtotaldoce - $impuesto, 2);			


					$total 		= round(($subtotal + $impuesto) - $descuento, 2);
			?>

			</tbody>


			<tfoot id="detalle_totales">

				<tr>
					<td colspan="3" class="textright" style="padding-right: 20px;"><span><b> SUBTOTAL 12%<b></span></td>
					<td class="textright"><span><?php echo number_format($subtotaldoce, 2); ?></span></td>
				</tr>
				<tr>
					<td colspan="3" class="textright" style="padding-right: 20px;"><span><b>SUBTOTAL 0%<b></span></td>
					<td class="textright"><span><?php echo number_format($subtotalcero, 2); ?></span></td>
				</tr>
				<tr>
					<td colspan="3" class="textright" style="padding-right: 20px;"><span><b>DESCUENTO<b></span></td>
					<td class="textright"><span><?php echo number_format($descuento, 2); ?></span></td>
				</tr>
				<tr>
					<td colspan="3" class="textright" style="padding-right: 20px;"><span><b>SUBTOTAL<b></span></td>
					<td class="textright"><span><?php echo number_format($subtotal, 2); ?></span></td>
				</tr>
				<tr>
					<td colspan="3" class="textright" style="padding-right: 20px;"><span><b>IVA (<?php echo $iva; ?> %)<b></span></td>
					<td class="textright"><span><?php echo number_format($impuesto, 2); ?></span></td>
				</tr>
				<tr>
					<td colspan="3" class="textright" style="padding-right: 20px;"><span><b>TOTAL<b> </span></td>
					<td class="textright"><span><?php echo number_format($total, 2); ?></span></td>
				</tr>

			</tfoot>

		</table>
		<div class="footer textcenter">
			<p class="nota">COPIA CLIENTE </p>
		</div>
		<div class="footer textleft">
			<p class="nota">CAMBIOS Y DEVOLUCIONES, MAXIMO 2 DIAS A PARTIR DE LA FECHA DE COMPRA</p>
		</div>
		<div class="footer textleft">
			<p style="width:100%;font-size:12px; " >Clave Acceso: <?= $data['factura']['claveacceso']; ?></p>

		</div>
		<div class="footer textcenter">
			<h4 class="label_gracias">¡Gracias por su visita!</h4>
		</div>



	</div>

</body>

</html>
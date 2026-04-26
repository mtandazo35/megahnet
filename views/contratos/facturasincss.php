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
//print_r($data['contrato']);exit; 

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
		text-align: justify;
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
        width: 97%;
        margin: 15px 10px 10px auto;
    }

    #factura_head,
    #factura_cliente,
    #factura_detalle {
        width: 100%;
    }

    .info_empresa {
        width: 50%;
        text-align: center;
    }

    .info_factura {
        width: 25%;
    }

    .info_cliente {
        width: 100%;
    }

    .datos_cliente {
        width: 95%;
    }

    .datos_cliente tr td {
        width: auto !important;

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
		padding-right: 10px;
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

        <table id="factura_head">
            <td class="info_empresa">
                <?php
                if ($data['empresa'] > 0) {
                    $iva = $data['empresa']['impuesto'];
                    $productos = json_decode($data['contrato']['productos'], true);

                    $array = explode('-', $data['contrato']['fecha']);
                    $year = $array[0];
                    $mes = $array[1];
                    $dia = $array[2];
                    $mesLetra = MESES[$mes - 0];
                    
                }
                ?>
            </td>
            <tr>
                <h1 class="textcenter">SERVICIO DE ACCESO A INTERNET</h1>
                <div style="padding-left: 10px; padding-top: 10px">
                          

                          <span>Fecha: <?= $dia ?> de <?= $mesLetra ?> del
                              año <?= $year ?></span>
                              <strong style="padding-left: 400px;">Contrato: <?php  echo $data['serieContrato'][0] ?>
                              </strong>

                      </div>



            </tr>
        </table>

        <table id="factura_cliente">
            <tr>
                <td class="info_cliente">
                    <div class="round">
                        <div style="padding-left: 10px;">
                            <strong>Red de Acceso: </strong>
                        </div>
						
						<?php if($data['contrato']['medio'] == 'INALAMBRICO') {    ?>
						
						 <div style="padding-left: 10px; ">
						
						
                            <span>Inalámbrico:  (<strong> X </strong>)</span>

                            <span style="padding-left: 100px;">Fibra Óptica:</span>

                            <span style="padding-left: 100px;">Otros: </span>
                        </div>
						
						
						<?php  } ?>
						
						
						<?php if($data['contrato']['medio'] == 'FIBRA') {    ?>
						
						 <div style="padding-left: 10px; ">
						
						
                            <span>Inalámbrico:  </span>

                            <span style="padding-left: 100px;">Fibra Óptica:  (<strong> X </strong>)</span>

                            <span style="padding-left: 100px;">Otros: </span>
                        </div>
						
						
						<?php  } ?>
						
                       

                        <div style="padding-left: 10px;">
                            <strong>Tipo de Cuenta: </strong>
                        </div>


                        <div style="padding-left: 10px;  padding-top: 0px">
                            <span>Residencial: </span>
                            <span style="padding-left: 90px; ">Coorporativo: </span>
                            <span style="padding-left: 90px; ">Cibercafé: </span>
                            <span style="padding-left: 90px; ">Otros tipos (<strong> X </strong>): </span>

                        </div>



                        <div style="padding-left: 10px; padding-top: 10px">
                            <strong>Nombre del Plan y Velocidad: (<?php echo $data['contrato']['ancho_banda']?>M Mbps) </strong>
                        </div>
                        <div style="padding-left: 10px;  padding-top: 0px">
                            <span>Plan:</span>
                            <?php 
                            echo $productos[0]['nombre']; ?>
                        </div>


                        <div style="padding-left: 10px; padding-top: 10px">
                            <strong>Nivel de compartición: </strong> <?php echo $data['contrato']['comparticion'] ?>
                        </div>


                        <div style="padding-left: 10px; padding-top: 10px">
                            <strong>EL contrato incluye permanencia mínima:</strong>
                            <span style="padding-left: 20px; ">SI: </span>
                            <span style="padding-left: 30px; ">NO: (<strong> X </strong>) </span>
                            <span style="padding-left: 30px; ">TIEMPO: </span>
                            <span style="padding-left: 5px; ">INDETERMINADO </span>
                        </div>

                        <div style="padding-left: 10px; padding-top: 10px">
                            <strong>Beneficios por permanencia mínima:</strong>
                        </div>
                        <div style="padding-left: 10px; padding-top: 0px">
                            <strong>Servicios adicionales que ofrece:</strong>
                        </div>




                        <div style="padding-left: 10px; padding-top: 10px">
                            <strong>Cuentas de correo electrónico:</strong>
                            <span style="padding-left: 15px; ">SI: </span>
                            <span style="padding-left: 15px; ">NO: (<strong> X </strong>) </span>
                            <span style="padding-left: 50px; ">NUMERO DE CUENTAS: </span>
                            <span style="padding-left: 50px; ">OTROS: </span>
                            <span style="padding-left: 15px; ">SI: </span>
                            <span style="padding-left: 15px; ">NO: (<strong> X </strong>) </span>

                        </div>



                        <div style="padding-left: 10px; padding-top: 10px">
                            <strong>Tarifas:</strong>
                        </div>
                        <div style="padding-left: 10px; padding-top: 0px">
                            <span>Valores a pagar una sola vez: Valor instalación:
                                $0</span>
                            <span style="padding-left: 125px; ">Plazo para instalar / activar el servicio: 24 Horas
                            </span>


                        </div>
                        <div style="padding-left: 10px; padding-top: 0px">
                            <span>Valores pago mensual: <strong> $USD </strong></span>
                           <strong> <?php echo number_format($productos[0]['precio'], 2, '.', ','); ?></strong>

                            <span style="padding-left: 167px; ">Detalle otros valores: $USD 0
                            </span>


                        </div>


                        <div style="padding-left: 10px; padding-top: 15px">
                            <span>Sitio Web para consulta de tarifas: www.gobravcorp.com </span>

                            <span style="padding-left: 80px; ">Sitio Web consulta calidad de servicio:
                                www.gobravcorp.com
                            </span>
                        </div>
                        <div style="padding-left: 10px; padding-top: 0px">
                            <span>Notas: * Las tarifas incluyen impuestos de ley </span>
                        </div>

							<div>
						<img style="width: 150px; height:55px; padding-top: 360px; padding-left: 110px;" src="<?php echo BASE_URL . 'assets/images/firma.png'; ?>  " alt="">

							</div>

                        <div style="padding-left: 100px; padding-top: 40px">
                            <span>................................................</span>

                            <span
                                style="padding-left: 80px; ">............................................................</span>
                        </div>

                        <div style="padding-left: 140px; padding-top: 0px">
                            <span>GOBRAVCORP S.A. </span>

                            <span style="padding-left: 145px; ">ABONADO / SUSCRIPTOR</span>
                        </div>


                        <div style="padding-left: 70px; padding-top: 0px">
                            <span>CONTRATO DE PRESTACION DE SERVICIOS DE VALOR AGREGADO DE ACCESO A INTERNET</span>

                        </div>
                    </div>
                </td>
            </tr>
        </table>

        <table id="factura_cliente">
            <tr>
                <td class="info_cliente">
                    <div class="round">
                        <div style="padding-left: 10px; ">
                            <span> 1) PRIMERA.- </span>


                        </div>
                        <div style="padding-left: 10px; padding-top: 15px">

                            <span >Lugar: <?= CIUDAD ?> </span>
                            <span  style="padding-left: 50px; ">Fecha: <?= $dia ?> de <?= $mesLetra ?> del
                            año <?= $year ?></span>

                        </div>

                        <div style="padding-left: 10px; padding-top: 10px">
                            <strong>Datos del Prestador:</strong>
                        </div>
                        <div style="padding-left: 10px; padding-top: 0px">
                            <span>Nombre/Razón Social:</span>
                            <?php echo $data['empresa']['razon_social'] ?>


                        </div>
                        <div style="padding-left: 10px; padding-top: 0px">
                            <span>Nombre Comercial: </span>
                            <?php echo $data['empresa']['nombre'] ?>


                        </div>
                        <div style="padding-left: 10px; padding-top: 0px">
                            <span>Dirección: </span>
                            <?php echo $data['empresa']['direccion'] ?>


                        </div>
                        <div style="padding-left: 10px; padding-top: 0px">
                            <span>Provincia: </span>
                            <?= PROVINCIA ?>

                            <span style="padding-left: 20px;">Ciudad: </span>
                            <?= CIUDAD ?>

                            <span style="padding-left: 20px;">Cantón: </span>
                            <?= CANTON ?>

                            <span style="padding-left: 20px;">Parroquia: </span>
                            <?= PARROQUIA ?>

                        </div>

                        <div style="padding-left: 10px; padding-top: 0px">
                            <span>No. Teléfono:</span>
                            <?php echo $data['empresa']['telefono'] ?>

                            <span style="padding-left: 20px;">Ruc: </span>
                            <?php echo $data['empresa']['ruc'] ?>

                            <span style="padding-left: 20px;">Correo Electrónico: </span>
                            <?php echo $data['empresa']['correo'] ?>


                        </div>




                        <div style="padding-left: 10px; padding-top: 10px">
                            <strong>Datos del Abonado/Suscritor:</strong>
                        </div>
                        <div style="padding-left: 10px; padding-top: 0px">
                            <span>Nombre/Razón Social:</span>
                           <strong> <?php echo $data['contrato']['nombre'] ?> </strong>


                        </div>
                        <div style="padding-left: 10px; padding-top: 0px">
                            <span>Cedula/ Ruc: </span>
                            <?php echo $data['contrato']['num_identidad'] ?>

                            <span style="padding-left: 15px;">Correo Electrónico: </span>
                            <?php echo $data['contrato']['correo'] ?>

                            <span style="padding-left: 15px;">Dirección: </span>
                            <?php echo $data['contrato']['direccionCliente'] ?>
                        </div>

                       

                        <div style="padding-left: 10px; padding-top: 0px">
                            <span>Dirección donde será prestado el servicio:</span>
                            <?= $data['contrato']['direccion']  ?>

                            <span style="padding-left: 20px;">Teléfono: </span>
                            <?= $data['contrato']['telefono']  ?>


                        </div>

                        <div style="padding-left: 10px; padding-top: 0px">
                            <span>¿El abonado es de la tercera edad o discapacidad?</span>

						<?php if($data['contrato']['discapacidad'] == 'SI') {    ?>
						
						<span style="padding-left: 10px;">SI: (<strong> X </strong>) </span>
                            <span style="padding-left: 10px;">NO:  </span>
                            <span style="padding-left: 10px;">(En caso afirmativo, aplica tarifa preferencial de acuerdo
                                al plan del prestador) </span>
						
						
						<?php  } ?>

<?php if($data['contrato']['discapacidad'] == 'NO') {    ?>
						
						<span style="padding-left: 10px;">SI:  </span>
                            <span style="padding-left: 10px;">NO: (<strong> X </strong>) </span>
                            <span style="padding-left: 10px;">(En caso afirmativo, aplica tarifa preferencial de acuerdo
                                al plan del prestador) </span>
						
						
						<?php  } ?>


                            
                        </div>



                        <div style="padding-left: 10px; padding-top: 1px">
                            <span>2) SEGUNDA.- Objeto: El prestador del servicio se compromete a proporcionar al
                                abonado/suscritor el/ los siguiente(s) servicio(s), para lo cual el prestador dispone de
                                los correspondientes
                                títulos habilitantes otorgados por la ARCOTEL, de conformidad con el ordenamiento
                                jurídico vigente:</span>

                        </div>

                        <div style="padding-left: 10px; padding-top: 1px">
                            <span> Móvil Avanzado (SMA)</span>

                        </div>
                        <div style="padding-left: 10px; padding-top: 0px">
                            <span>Telefonía fija</span>

                        </div>
                        <div style="padding-left: 10px; padding-top: 0px">
                            <span>Valor Agregado</span>

                        </div>
                        <div style="padding-left: 10px; padding-top: 0px">
                            <span>Acceso a Internet </span>
                            <span style="padding-left: 50px;">(<strong> X </strong>) </span>

                        </div>
                        <div style="padding-left: 10px; padding-top: 0px">
                            <span>Audio y video por Suscripción</span>

                        </div>
                        <div style="padding-left: 10px; padding-top: 1px">
                            <span>Las condiciones de /los servicio(S) que el abonado va a contratar se encuentra
                                detallada en el ANEXO N1, el cual forma parte integrante del presente contrato.</span>

                        </div>
						   <div style="padding-left: 10px; padding-top: 1px">
                            <span> 3) TERCERA.- Vigencia del contrato: El presente contrato tendrá una duración
                                de……24……. meses y entrara en vigencia a partir de la fecha de instalación y prestación
                                efectiva del
                                servicio. La fecha inicial considerada para la facturación para cada uno de los
                                servicios contratados debe ser la de la activación de servicio. Las partes se
                                comprometen a respetar el
                                plazo de vigencia pactado, sin perjuicio de que si el abonado/ suscriptor puede darlo
                                por terminado únicamente, en cualquier tiempo, previa notificación física o electrónica,
                                con por lo
                                menos 15 días de anticipación, conforme lo dispuesto en las Leyes Orgánicas de
                                Telecomunicaciones y de Defensa del Consumidor y sin que para ello este obligado a
                                cancelar multas o
                                recargos de valores de ninguna naturaleza.
                                El abonado acepta la renovación automática sucesiva del contrato en las mismas
                                condiciones de este contrato, independientemente a su derecho a terminar la relación
                                contractual
                                conforme la legislación aplicable, o solicitar en cualquier tiempo, con hasta quince
                                (15) días de antelación a la fecha de renovación, su decisión de no renovación: </span>


                        </div>
						<div style="padding-left: 280px; padding-top: 5px">
                            <span>SI</span>
                            <span style="padding-left: 20px; ">(<strong> X </strong>) </span>

                            <span style="padding-left: 80px; ">NO </span>
                        </div>


                        <div style="padding-left: 10px; padding-top: 1px">
                            <span> 4) CUARTA.- Permanencia Mínima:</span>
                        </div>


                        <div style="padding-left: 10px; padding-top: 0px">
                            <span> El abonado se acoge de permanencia mínima de………12 meses………en la prestación del
                                servicio contratado </span>
                            <span>SI</span>
                            <span style="padding-left: 15px; ">  <strong> (<strong> X </strong>) </strong> </span>

                            <span style="padding-left: 40px; ">NO </span>
                        </div>
						  <div style="padding-left: 10px; padding-top: 1px">
                            <span> La permanencia mínima se acuerda, sin perjuicio de que el abonado /suscriptor
                                conforme lo determina la Ley Orgánica de Telecomunicaciones, pueda dar por terminado el
                                contrato en
                                forma unilateral y anticipada, y en cualquier tiempo, previa notificación por medios
                                físicos o electrónicos al prestador, con por lo menos quince (15) días de anticipación,
                                para cuyo efecto
                                deberá proceder a cancelar los servicios efectivamente prestados y la devolución de
                                <strong> LOS
                                    EQUIPOS RECIBIDOS QUE SON PROPIEDAD DE <?= $data['empresa']['nombre'] ?> </strong>,
                                hasta la terminación
                                del contrato.</span>
                        </div>
						
                        <div style="padding-left: 10px; padding-top: 15px">
                            <strong> *En caso de no cumplir con el tiempo establecido deberá cancelar el valor de
                                $130</strong>
                        </div>
                    </div>
                </td>
            </tr>
        </table>

        <table id="factura_cliente">
            <tr>
                <td class="info_cliente">
                    <div class="round">
                     
                        

                      



                        <div style="padding-left: 10px; padding-top: 15px">
                            <span> 5) QUINTA.- Tafira y forma de pago: Las tarifas o valores mensuales a ser cancelados
                                por cada uno de los servicios contratados por el abonado estará determinada en la fecha
                                de cada
                                servicio, que constan en el Anexo 1 y el pago se realizada, de la siguiente
                                forma:</span>
                        </div>
                        <div style="padding-left: 10px; padding-top: 15px">
                            <span> Pago directo en cajas del prestador del servicio</span>
                            <span style="padding-left: 52px;"> SI</span>
                            <span style="padding-left: 50px;"> NO</span>

                        </div>
                        <div style="padding-left: 10px; padding-top: 0px">
                            <span> Débito automático cuenta de ahorro o corriente</span>
                            <span style="padding-left: 50px;"> SI</span>
                            <span style="padding-left: 50px;"> NO</span>

                        </div>

                        <div style="padding-left: 10px; padding-top: 0px">
                            <span> Pago en ventanilla de locales autorizados</span>
                            <span style="padding-left: 80px;"> SI</span>
                            <span style="padding-left: 50px;"> NO</span>

                        </div>

                        <div style="padding-left: 10px; padding-top: 0px">
                            <span> Debito con tarjeta de crédito</span>
                            <span style="padding-left: 140px;"> SI</span>
                            <span style="padding-left: 50px;"> NO</span>

                        </div>

                        <div style="padding-left: 10px; padding-top: 0px">
                            <span>Transferencia vía medios electrónicos</span>
                            <span style="padding-left: 95px;"> SI</span>
                            <span style="padding-left: 5px;"> (<strong> X </strong>)</span>

                            <span style="padding-left: 30px;"> NO</span>

                        </div>


                        <div style="padding-left: 10px; padding-top: 0px">
                            <span>La tarifa correspondiente al servicio contratado y efectivamente prestado, estará
                                dentro de los techos tarifarios señalados por la ARCOTEL y en los títulos habilitantes
                                correspondientes,
                                en caso de que se establezcan, de conformidad con el ordenamiento jurídico vigente. En
                                caso de que el abonado o suscritor desee cambiar su modalidad de pago a otra de las
                                disponibles,
                                deberá comunicarlo al prestador del servicio (<?= $data['empresa']['nombre'] ?>), con
                                quince días de anticipación. El prestador del servicio <?= $data['empresa']['nombre'] ?>
                                luego de haber sido comunicado,
                                instrumentara la nueva forma de pago.</span>

                        </div>

                        <div style="padding-left: 10px; padding-top: 10px">
                            <span>6) SEXTA.- Compra Arriendo de Equipos: Los equipos que se entrega al abonado o
                                suscriptor son de propiedad de GOBRAVCORP S.A. como lo dice en el anexo 1 solo se cobra
                                costo
                                de instalación. <strong> EN EL MOMENTO QUE EL ABONADO O SUSCRIPTOR DESEE DAR POR
                                    TERMINADO EL CONTRATO ESTÁ EN LA OBLIGACIÓN DE REALIZAR LA DEVOLUCIÓN
                                    DE LOS EQUIPOS.</strong></span>


                        </div>

                        <div style="padding-left: 10px; padding-top: 10px">
                            <span>7) SEPTIMA.- Uso de Información personal: Los datos personales que los usuarios
                                proporcionen a los prestadores de servicios del régimen general de telecomunicaciones,
                                no podrán
                                ser usados para la promoción comercial de servicios o productos, inclusive de la propia
                                operadora, salvo autorización y consentimiento expreso del abonado suscriptor, lo
                                autorice
                                mediante medios físicos o electrónicos como está conforme lo dispuesto en el artículo
                                121 del Reglamento General a la ley Organiza de Telecomunicaciones.</span>
                        </div>




                        <div style="padding-left: 10px; padding-top: 10px">
                            <span>8) OCTAVA. - Reclamos y soporte técnico: el abonado/ cliente podrá requerir soporte
                                técnico o presentar reclamos al prestador de servicio
                                (<?= $data['empresa']['nombre'] ?>) a través de los siguientes
                                medios o puntos:</span>
                        </div>

                        <div style="padding-left: 10px; padding-top: 10px">
                            <span>Medios Electrónicos</span>
                            <span style="padding-left: 65px;">(web. www.gobravcorp.com )</span>

                        </div>

                        <div style="padding-left: 10px; padding-top: 0px">
                            <span>Oficina de atención al usuario</span>
                            <span style="padding-left: 20px;">(<?= $data['empresa']['direccion'] ?>)</span>

                        </div>
                        <div style="padding-left: 10px; padding-top: 0px">
                            <span>Horarios de atención de Lunes a Viernes </span>
                            <span style="padding-left: 20px;"> (de 8:30 a 18:30) sábados y domingos (9:00 a
                                13:00)</span>

                        </div>
                        <div style="padding-left: 10px; padding-top: 0px">
                            <span>Teléfono:</span>
                            <span style="padding-left: 20px;">055-000-545</span>
                            <span style="padding-left: 40px;"><?= $data['empresa']['telefono'] ?></span>

                        </div>
                        <div style="padding-left: 10px; padding-top: 10px">
                            <span>Para la atención de reclamos NO resueltos por el prestador, el abonado también podrá
                                presentar sus denuncias y reclamos ante la agencia de Regulación y Control de las
                                Telecomunicaciones (ARCOTEL) por cualquiera de los siguientes canales de
                                atención.</span>
                        </div>

                        <div style="padding-left: 10px; padding-top: 10px">
                            <span>Atención presencial:</span>
                            <span style="padding-left: 25px;">Oficinas de las Coordinaciones Zonales de la
                                ARCOTEL</span>

                        </div>

                        <div style="padding-left: 10px; padding-top: 0px">
                            <span>Pbx-Matriz</span>
                            <span style="padding-left: 70px;">593-02-2947800</span>

                        </div>
                        <div style="padding-left: 10px; padding-top: 0px">
                            <span>Call Center </span>
                            <span style="padding-left: 62px;"> (1800-567567)</span>

                        </div>
                        <div style="padding-left: 10px; padding-top: 0px">
                            <span>Web Site:</span>
                            <span style="padding-left: 72px;"> Http://reclamoconsumidor.arcotel.gob.ec/osTicket</span>

                        </div>

                        <div style="padding-left: 10px; padding-top: 10px">
                            <span>9) NOVENA. - Normativa Aplicable: En la prestación del servicio, se entienden
                                incluidos todos los derechos y obligaciones de los abonados /suscriptores, establecidos
                                en las normas
                                jurídicas aplicables, así como también los derechos y obligaciones de los prestadores de
                                servicios de telecomunicaciones y/o servicios de radiodifusión por suscripción,
                                dispuestos en el
                                marco regulatorio.</span>
                        </div>
                        <div style="padding-left: 10px; padding-top: 10px">
                            <span>10) DECIMA. - Controversias: Las diferencias que serían de la ejecución del presente
                                contrato, podrán ser resueltas por mutuo acuerdo entre las partes, sin perjuicio de que
                                correspondan.
                                el abonado o suscriptor acuda con su reclamo, queja o denuncia, ante las autoridades
                                administrativas que correspondan. De no llegarse a una solución, cualquiera de las
                                partes podrá
                                acudir ante los jueces competentes. No obstante, lo indicado, las partes pueden pactar
                                adicionalmente, someter sus controversias ante un centro de mediación o arbitraje, se
                                así lo
                                deciden expresamente, en cuyo caso el abonado/suscriptor deberá señalarlo en forma
                                expresa. El abonado, en caso de conflicto, acepta someterse a la mediación o arbitraje
                                (puede significar costos en los que debe incurrir el abonado/suscriptor-no implica a
                                empresas publicas prestadoras de servicios de telecomunicaciones)</span>
                        </div>
                        <div style="padding-left: 10px; padding-top: 10px">
                            <span>Firma de aceptación-sujeción a arbitraje: __________________________</span>
                            <span style="padding-left: 10px;">SI </span>
                            <span style="padding-left: 5px;">(<strong> X </strong>) </span>

                            <span style="padding-left: 20px;">NO </span>

                        </div>
                        <div style="padding-left: 10px; padding-top: 10px">
                            <span>11) DECIMA PRIMERA. – Causales y mecanismos de terminación del contrato.- Los
                                firmantes de este contrato se acogen a lo dispuesto en el artículo 4 numeral 14) de la
                                Norma Técnica
                                que Regula las condiciones Generales de los Contratos de adhesión, del contrato
                                negociado con clientes, y del empadronamiento de abonados y clientes.</span>
                        </div>
						 <div style="padding-left: 10px; padding-top: 10px">
                            <span>11) DECIMA SEGUNDA. - Anexos: Es parte integrante del presente contrato el Anexo 1 que
                                contiene las (condiciones particulares del Servicio), así como los demás anexos y
                                documentos
                                que se incorporen de conformidad con el ordenamiento jurídico.</span>
                        </div>

                        <div style="padding-left: 10px; padding-top: 1px">
                            <span>12) DECIMA TERCERA. - Notificaciones y Domicilio: Las notificaciones que corresponda,
                                serán entregadas
                                en el domicilio de cada una de las partes señalado en la clausura primera del
                                presente contrato. Cualquier cambio de domicilio debe ser comunicado por escrito a la
                                otra parte en un
                                plazo de 10 días, a partir del día siguiente en que el cambio se efectué.</span>
                        </div>
                        <div style="padding-left: 10px; padding-top: 10px">
                            <span>13) DECIMA CUARTA. - Empaquetamiento de Servicios:</span>
                        </div>

                        <div style="padding-left: 10px; padding-top: 10px">
                            <span>La contratación incluye empaquetamiento de servicios</span>
                            <span style="padding-left: 10px;">SI </span>

                            <span style="padding-left: 20px;">NO </span>
                            <span style="padding-left: 5px;">(<strong> X </strong>) </span>

                        </div>

                        <div style="padding-left: 10px; padding-top: 10px">
                            <span>Especificar los servicios del paquete y los beneficios para cada uno, incluyendo las
                                tarifas
                                aplicables:</span>
                            <span style="padding-left: 40px;">IP PRIVADA NATEADA </span>



                        </div>

                        <div style="padding-left: 10px; padding-top: 10px">
                            <span>El abonado acepta el presente contrato con sus términos y condiciones y demás
                                documentos anexos para
                                lo cual deja constancia de lo anterior y firman junto con
                                <?= $data['empresa']['nombre'] ?>
                                en tres ejemplares</span>

                        </div>
                        <div style="padding-left: 10px; padding-top: 10px">
                          

                            <span>del mismo tenor en la ciudad de <?= CIUDAD ?> a los <?= $dia ?> días del mes de
                                <?= $mesLetra ?> del
                                año <?= $year ?></span>

                        </div>
	<div>
						<img style="width: 150px; height:55px; padding-top: 960px; padding-left: 120px;" src="<?php echo BASE_URL . 'assets/images/firma.png'; ?>  " alt="">

							</div>
                        <div style="padding-left: 100px; padding-top: 60px">
                            <span>..................................................................</span>

                            <span
                                style="padding-left: 80px; ">................................................................</span>
                        </div>

                        <div style="padding-left: 140px; padding-top: 0px">
                            <span>GOBRAVCORP S.A. </span>

                            <span style="padding-left: 145px; ">ABONADO / SUSCRIPTOR</span>
                        </div>
                    </div>
                </td>

            </tr>
        </table>


      





    </div>
</body>

</html>
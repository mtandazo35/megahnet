<?php 
	class Payphone extends Controller{
		public function __construct()
		{
			parent::__construct();
			//session_start();
		}

		public function index()
		{
			$data['title'] = 'Payphone';
				//$data['page_tag'] = NOMBRE_EMPRESA;
			//	$data['page_title'] = NOMBRE_EMPRESA." - ".$pageContent['titulo'];
				//$data['page_name'] = $pageContent['titulo'];
				//$data['page'] = $pageContent;
				$this->views->getView('payphone','index',$data);   
			

		}
		public function procesarVenta()
		{
			print_r($_POST);
			var_dump($_POST);
			
			if($_POST){
				//dep($_SESSION['userData']);
					$idtransaccionpayphone = "";
				$datospayphone = null;
				$arrPayphone= null;			
				$tipopagoid = intval($_POST['inttipopago']);				
				$status = "Pendiente";
					
					
					
					
					if(!empty($_POST['datapayphone'])){
						//print_r($_POST['datapayphone']);
						//echo "payphone"; exit;
						$jsonPayphone = $_POST['datapayphone'];
						$objPayphone = json_decode($jsonPayphone);
						$arrPayphone = json_decode($jsonPayphone,JSON_UNESCAPED_UNICODE);
						$status = "Pendiente";
						print_r($arrPayphone); exit;
						if(is_object($objPayphone)){
							$datospayphone = $jsonPayphone;
							$idtransaccionpayphone = $arrPayphone['transactionId']; //recibimos el id de la transaccion de paypal mediante json convertido en objeto buscamos en cada array 
							if($arrPayphone['transactionStatus'] == "Approved"){
								$totalPayphone = formatMoney($arrPayphone['amount'] / 100);
								if($monto == $totalPayphone){
									$status = "Pendiente";
								}
								//dep($idtransaccionpayphone);
							//	dep($arrPayphone['transactionStatus']);
								//dep($totalPayphone); exit;
								//Crear pedido
								$request_pedido = $this->insertPedido($idtransaccionpaypal, 
																	$datospaypal, 
																	$personaid,
																	$costo_envio,
																	$monto, 
																	$tipopagoid,
																	$direccionenvio, 
																	$status,
																	$descuento,
																	$idtransaccionpayphone, 
																	$datospayphone);
								if($request_pedido > 0 ){
									//Insertamos detalle
									foreach ($_SESSION['arrCarrito'] as $producto) {
										$productoid = $producto['idproducto'];
										$precio = $producto['precio'];
										$cantidad = $producto['cantidad'];
										$this->insertDetalle($request_pedido,$productoid,$precio,$cantidad);
									}
									$infoOrden = $this->getPedido($request_pedido);
									$dataEmailOrden = array('asunto' => "Se ha Creado la Orden No.".$request_pedido,
													'email' => $correo_cli, 
													'emailCopia' => EMAIL_PEDIDO,
													'pedido' => $infoOrden );

									//sendEmail($dataEmailOrden,'email_notificacion_orden');

									$orden = openssl_encrypt($request_pedido, METHODENCRIPT, KEY);
									$transaccion = openssl_encrypt($idtransaccionpayphone, METHODENCRIPT, KEY);
									$arrResponse = array("status" => true, 
													"orden" => $orden, 
													"transaccion" =>$transaccion,
													"msg" => 'Pedido Realizado'
												);
									$_SESSION['dataorden'] = $arrResponse;
									unset($_SESSION['arrCarrito']);
									session_regenerate_id(true);
								}else{
									$arrResponse = array("status" => false, "msg" => 'No es posible procesar el pedido.');
								}
							}else{
								$arrResponse = array("status" => false, "msg" => 'No es posible completar el pago con PayPhone.');
							}
						}else{
							$arrResponse = array("status" => false, "msg" => 'Hubo un error en la transacción.');
						}
						
						//dep($objPayphone['cardBrand']); exit;
					}
					
				
			}else{
				$arrResponse = array("status" => false, "msg" => 'No es posible procesar el pedido.');
			}

			echo json_encode($arrResponse,JSON_UNESCAPED_UNICODE);
			die(); 
			
		}
		


	}

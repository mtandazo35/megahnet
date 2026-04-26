<?php
class Pagos extends Controller
{
	public function __construct()
	{
		parent::__construct();
		//session_start();
		/*if (empty($_SESSION['id_usuario'])) {
				header('Location: ' . BASE_URL);
				exit;
			}
			$this->id_usuario = $_SESSION['id_usuario'];*/
	}

	public function index()
	{

		$data['title'] = 'Pagos Electronicos';
		$data['script'] = 'pa gos.js';
		//$data['script'] = 'index.js';
		$this->views->getView('pagos', 'index', $data);
	}
	public function pago()
	{
			
		$data['title'] = 'Pagos Electronicos';
	
		$this->views->getView('pagos', 'pago', $data);
	}
	
	public function listarFactura($busqueda)
    {

      //  print_r($da); exit;
        $data = $this->model->getFactura($busqueda);
       
        for ($i = 0; $i < count($data); $i++) {
          /*  if ($data[$i]['estado'] == 'ENTREGA') {
                $data[$i]['acciones'] = '<div>
                <a class="btn btn-success" href="#" onclick="verReporte(' . $data[$i]['num_orden'] . ')"><i class="fas fa-file-pdf"></i></a>
                </div>';
                $data[$i]['estado'] = '<div><span class="badge bg-success">ENTREGA</span></div>';
            } else if ($data[$i]['estado'] == 'COMPLETADO') {
                $data[$i]['acciones'] = '<div>
                
                <a class="btn btn-success" href="#" onclick="verReporte(' . $data[$i]['num_orden'] . ')"><i class="fas fa-file-pdf"></i></a>
                </div>';
                $data[$i]['estado'] = '<div><span class="badge bg-warning">COMPLETADO</span></div>';
            } else if ($data[$i]['estado'] == 'VERIFICACION') {
                $data[$i]['acciones'] = '<div>
                
                <a class="btn btn-success" href="#" onclick="verReporte(' . $data[$i]['num_orden'] . ')"><i class="fas fa-file-pdf"></i></a>
                </div>';
                $data[$i]['estado'] = '<div><span class="badge bg-warning">VERIFICACION</span></div>';
            } else if ($data[$i]['estado'] == 'REPARACION') {
                $data[$i]['acciones'] = '<div>
                
                <a class="btn btn-success" href="#" onclick="verReporte(' . $data[$i]['num_orden'] . ')"><i class="fas fa-file-pdf"></i></a>
                </div>';
                $data[$i]['estado'] = '<div><span class="badge bg-warning">REPARACION</span></div>';
            }
            else if ($data[$i]['estado'] == 'DIAGNOSTICO') {
                $data[$i]['acciones'] = '<div>
                
                <a class="btn btn-success" href="#" onclick="verReporte(' . $data[$i]['num_orden'] . ')"><i class="fas fa-file-pdf"></i></a>
                </div>';
                $data[$i]['estado'] = '<div><span class="badge bg-warning">DIAGNOSTICO</span></div>';
            }
            
            
            else{*/
                $data[$i]['acciones'] = '<div>
                
                <a class="btn btn-warning btn-sm" style="color: #fff;" href="' . BASE_URL . 'pagos/pago?numFactura='. $data[$i]['orden_no'] .'&valor='.$data[$i]['totalfactura'].'"><i class="fab fa-amazon-pay" aria-hidden="true" ></i></a>
                </div>';
                $data[$i]['estado'] = '<div><span class="badge bg-danger">PENDIENTE</span> </div>';
            //}
        }
        echo json_encode($data);
        die();
    }
  

}
?>

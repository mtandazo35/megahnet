<?php
require 'vendor/autoload.php';

use Dompdf\Dompdf;
require 'vendor/autoload.php';

use PhpOffice\PhpSpreadsheet\IOFactory;
/*use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Style\Color;
use PhpOffice\PhpSpreadsheet\Style\Fill;*/

class Productos extends Controller
{

    public function __construct()
    {
        session_start();
        if (empty($_SESSION['id_usuario'])) {
            header('Location: ' . BASE_URL);
            exit;
        }
        // Liberar lock de sesion PHP inmediatamente — este controller no escribe
        // a $_SESSION, asi que otros requests del mismo usuario pueden correr en
        // paralelo en vez de serializarse detras del file lock.
        session_write_close();
        parent::__construct();
    }
    public function index()
    {
        $data['title'] = 'Productos';
        $data['script'] = 'productos.js';
        $data['validacion'] = 'validacion.js';

        $data['medidas'] = $this->model->getDatos('medidas');
        $data['categorias'] = $this->model->getDatos('categorias');
        $this->views->getView('productos', 'index', $data);
    }
    public function listar()
    {
        $data = $this->model->getProductos(1);
        for ($i = 0; $i < count($data); $i++) {
            $foto = ($data[$i]['foto'] == null) ? BASE_URL . 'assets/images/productos/default.png' :  BASE_URL . $data[$i]['foto'];
            $data[$i]['imagen'] = '<img class="img-thumbnail" src="' . $foto . '" width="50">';
            $data[$i]['acciones'] = '<div>
            <button class="btn btn-info" type="button" onclick="editarProducto(' . $data[$i]['id'] . ')"><i class="fas fa-edit text-white"></i></button>
            <a class="btn btn-white" type="button" href="' . BASE_URL . 'productos/codigoBarcode/' . $data[$i]['id'] . '" target="_blank" ><i class="fa-solid fa-barcode"></i></a>
            <button class="btn btn-danger" type="button" onclick="eliminarProducto(' . $data[$i]['id'] . ')"><i class="fas fa-trash"></i></button>
            </div>';
        }
        echo json_encode($data, JSON_UNESCAPED_UNICODE);
        die();
    }
    public function registrarExcel()
    {

        //  print_r($_FILES['excel']); exit;
        $datosExcel = $_FILES['excel'];
        if ($datosExcel['size'] > 0) {
            //    $direccion = strClean($_POST['direccion']);
            //   print_r($chelectronica); exit;
            if ($datosExcel['type'] != 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet') {
                $res = array('msg' => 'SELECCIONES UN ARCHIVO EXCEL', 'type' => 'error');
            } else {

                $archivoContent = $datosExcel['tmp_name'];
                importarExcel($archivoContent);
             
               
            }
        } else {
            $res = array('msg' => 'ERROR DESCONOCIDO', 'type' => 'error');
            echo json_encode($res, JSON_UNESCAPED_UNICODE);

        }
        die();
    }
    public function registrar()
    {
        // print_r($_POST); exit;
        if (isset($_POST['codigo']) && isset($_POST['nombre'])) {
            $id = strClean($_POST['id']);
            $codigo = trim(strClean($_POST['codigo']));
            $nombre = trim(strClean($_POST['nombre']));
            $precio_compra = trim(empty($_POST['precio_compra'] )) ? 0 : trim($_POST['precio_compra']);
            //$precio_compra = strClean($_POST['precio_compra']);
            $precio_venta = trim($_POST['precio_venta']);
            //$id_medida = strClean($_POST['id_medida']);
            $id_categoria = strClean($_POST['id_categoria']);
            $fotoActual = strClean($_POST['foto_actual']);
            $iva = strClean($_POST['id_iva']);
            $foto = $_FILES['foto'];
            $name = $foto['name'];
            $tmp = $foto['tmp_name'];
            //print_r($foto);
            //exit;

            $destino = null;
            if (!empty($name)) {
                $fecha = date('YmdHis');
                $destino = 'assets/images/productos/' . $fecha . '.jpg';
            } else if (!empty($fotoActual) && empty($name)) {
                $destino = $fotoActual;
            }

            if (empty($codigo)) {
                $res = array('msg' => 'EL CODIGO ES REQUERIDO', 'type' => 'warning');
            } else if (empty($nombre)) {
                $res = array('msg' => 'EL NOMBRE ES REQUERIDO', 'type' => 'warning');
            } else if (empty($precio_venta)) {
                $res = array('msg' => 'EL PRECIO VENTA ES REQUERIDO', 'type' => 'warning');
            } else if (empty($id_categoria)) {
                $res = array('msg' => 'LA CATEGORIA ES REQUERIDO', 'type' => 'warning');
            } else {
                if ($id == '') {
                    $verificar = $this->model->getValidar('codigo', $codigo, 'registrar', 0);
                    if (empty($verificar)) {
                        $data = $this->model->registrar(
                            $codigo,
                            $nombre,
                            $precio_compra,
                            $precio_venta,
                            $id_categoria,
                            $destino,
                            $iva
                        );
                        if ($data > 0) {
                            if (!empty($name)) {
                                move_uploaded_file($tmp, $destino);
                            }
                            $res = array('msg' => 'PRODUCTO REGISTRADO EXITOSAMENTE', 'type' => 'success');
                        } else {
                            $res = array('msg' => 'ERROR AL REGISTRAR', 'type' => 'error');
                        }
                    } else {
                        $res = array('msg' => 'EL CODIGO DEBE SER ÚNICO', 'type' => 'warning');
                    }
                } else {
                    $verificar = $this->model->getValidar('codigo', $codigo, 'actualizar', $id);
                    if (empty($verificar)) {
                        $data = $this->model->actualizar(
                            $codigo,
                            $nombre,
                            $precio_compra,
                            $precio_venta,
                            // $id_medida,
                            $id_categoria,
                            $destino,
                            $iva,
                            $id
                        );
                        if ($data > 0) {
                            if (!empty($name)) {
                                move_uploaded_file($tmp, $destino);
                            }
                            $res = array('msg' => 'PRODUCTO ACTUALIZADO EXITOSAMENTE', 'type' => 'success');
                        } else {
                            $res = array('msg' => 'ERROR AL ACTUALIZAR', 'type' => 'error');
                        }
                    } else {
                        $res = array('msg' => 'LA CODIGO DEBE SER ÚNICO', 'type' => 'warning');
                    }
                }
            }
        } else {
            $res = array('msg' => 'ERROR DESCONOCIDO', 'type' => 'error');
        }
        echo json_encode($res, JSON_UNESCAPED_UNICODE);
        die();
    }

    public function eliminar($idProducto)
    {
        if (isset($_GET) && is_numeric($idProducto)) {
            $data = $this->model->eliminar(0, $idProducto);
            if ($data == 1) {
                $res = array('msg' => 'PRODUCTO ELIMINADO EXITOSAMENTE', 'type' => 'success');
            } else {
                $res = array('msg' => 'ERROR AL ELIMINAR', 'type' => 'error');
            }
        } else {
            $res = array('msg' => 'ERROR DESCONOCIDO', 'type' => 'error');
        }
        echo json_encode($res, JSON_UNESCAPED_UNICODE);
        die();
    }

    public function editar($idProducto)
    {
        $data = $this->model->editar($idProducto);
        echo json_encode($data, JSON_UNESCAPED_UNICODE);
        die();
    }

    public function inactivos()
    {
        $data['title'] = 'Productos Inactivos';
        $data['script'] = 'productos-inactivos.js';
        $this->views->getView('productos', 'inactivos', $data);
    }

    public function listarInactivos()
    {
        $data = $this->model->getProductos(0);
        for ($i = 0; $i < count($data); $i++) {
            $foto = ($data[$i]['foto'] == null) ? BASE_URL . 'assets/images/productos/default.png' : BASE_URL . $data[$i]['foto'];
            $data[$i]['imagen'] = '<img class="img-thumbnail" src="' . $foto . '" width="50">';
            $data[$i]['acciones'] = '<div>
            <button class="btn btn-success" type="button" onclick="restaurarProducto(' . $data[$i]['id'] . ')"><i class="fas fa-check-circle"></i></button>
            </div>';
        }
        echo json_encode($data, JSON_UNESCAPED_UNICODE);
        die();
    }

    public function restaurar($idProducto)
    {
        if (isset($_GET) && is_numeric($idProducto)) {
            $data = $this->model->eliminar(1, $idProducto);
            if ($data == 1) {
                $res = array('msg' => 'PRODUCTO RESTAURADO', 'type' => 'success');
            } else {
                $res = array('msg' => 'ERROR AL RESTAURAR', 'type' => 'error');
            }
        } else {
            $res = array('msg' => 'ERROR DESCONOCIDO', 'type' => 'error');
        }
        echo json_encode($res);
        die();
    }
    //buscar Productos por codigo
   /* public function buscarPorCodigo($valor)
    {
            
    
       $array = array('estado' => false, 'datos' => '');
        $data = $this->model->buscarPorCodigo($valor);
        if (!empty($data)) {
            $array['estado'] = true;
            $array['datos'] = $data;
        }
        echo json_encode($array, JSON_UNESCAPED_UNICODE);
        die();
    }*/
    //buscar Productos por nombre
    public function buscarPorCodigo()
    {
        $array = array();
        $valor = $_GET['term'];
        $data = $this->model->buscarPorCodigo($valor);
        foreach ($data as $row) {
            $result['id'] = $row['id'];
            $result['label'] = $row['descripcion'];
            $result['stock'] = $row['cantidad'];
            $result['precio_venta'] = $row['precio_venta'];
            $result['precio_compra'] = $row['precio_compra'];
            $result['id_categoria'] = $row['id_categoria'];


            array_push($array, $result);
        }
        echo json_encode($array, JSON_UNESCAPED_UNICODE);
        die();
    }
    public function buscarPorNombre()
    {
        $array = array();
        $valor = $_GET['term'];
        $data = $this->model->buscarPorNombre($valor);
        foreach ($data as $row) {
            $result['id'] = $row['id'];
            $result['label'] = $row['descripcion'];
            $result['stock'] = $row['cantidad'];
            $result['precio_venta'] = $row['precio_venta'];
            $result['precio_compra'] = $row['precio_compra'];
            $result['id_categoria'] = $row['id_categoria'];


            array_push($array, $result);
        }
        echo json_encode($array, JSON_UNESCAPED_UNICODE);
        die();
    }


    public function buscarPorNombreTipoPago()
    {
        $array = array();
        $valor = $_GET['term'];
        $data = $this->model->buscarPorNombreTipoPago($valor);
        foreach ($data as $row) {
            $result['id'] = $row['id'];
            $result['label'] = $row['nombre'];     
            $result['precio'] = 0;     

            array_push($array, $result);
        }
        echo json_encode($array, JSON_UNESCAPED_UNICODE);
        die();
    }

    public function buscarRetencion()
    {
        $array = array();
        $valor = $_GET['term'];
        $data = $this->model->buscarPorNombreRetencion($valor);
        foreach ($data as $row) {
            $result['id'] = $row['id'];
            $result['tipo'] = $row['tipo'];     
            $result['label'] = $row['tipo'] .' - '. $row['codigo'] .' - '. $row['descripcion'];     
            $result['codigo'] = $row['codigo'];     
            $result['porcentajeretencion'] = $row['porcentajeretencion'];     
            $result['descripcion'] = $row['descripcion'];     


            array_push($array, $result);
        }
        echo json_encode($array, JSON_UNESCAPED_UNICODE);
        die();
    }
    //mostrar productos desde localStorage
    public function mostrarDatos()
    {
        $json = file_get_contents('php://input');
        $datos = json_decode($json, true);
        $array['productos'] = array();
        $totalCompra = 0;       // CON IVA (lo que el user paga realmente)
        $totalCompraSinIva = 0; // sin IVA (solo informativo)
        $totalVenta = 0;
        if (!empty($datos)) {
            foreach ($datos as $producto) {
                $result = $this->model->editar($producto['id']);
                $data['id'] = $result['id'];
                $data['nombre'] = $producto['nombre'];
                $data['precio_compra']  =  number_format((empty($producto['precio'])) ? 0 : $producto['precio'], 2, '.', '');
                $data['precio_venta'] = number_format((empty($producto['precio'])) ? 0 : $producto['precio'], 2, '.', '');
                $data['cantidad'] = $producto['cantidad'];
                $subTotalCompra =  $data['precio_compra'] * $producto['cantidad'];
                $subTotalVenta = $data['precio_venta'] * $producto['cantidad'];
                // Aplicar IVA por producto si su iva > 0 (15%, 12%, etc segun configuracion)
                $ivaProd = (float)($result['iva'] ?? 0);
                $subTotalConIva = ($ivaProd > 0)
                    ? round($subTotalCompra * (1 + $ivaProd / 100), 2)
                    : $subTotalCompra;
                $data['subTotalCompra'] = number_format($subTotalCompra, 2); // sin IVA en la tabla
                $data['subTotalVenta'] = number_format($subTotalVenta, 2);
                array_push($array['productos'], $data);
                $totalCompra += $subTotalConIva;
                $totalCompraSinIva += $subTotalCompra;
                $totalVenta += $subTotalVenta;
            }
        }
        $array['totalCompra'] = number_format($totalCompra, 2);             // con IVA -> Total a Pagar
        $array['totalCompraSinIva'] = number_format($totalCompraSinIva, 2); // sin IVA -> referencia
        $array['totalVenta'] = number_format($totalVenta, 2);
        $array['totalVentaHidden'] = $totalVenta;

        echo json_encode($array, JSON_UNESCAPED_UNICODE);
        die();
    }
    public function mostrarDatosTipoPago()
    {
        $json = file_get_contents('php://input');
        $datos = json_decode($json, true);
        $array['tipoPago'] = array();
       $total =0;
       //print_r($datos); exit;
        if (!empty($datos)) {
            foreach ($datos as $tipoPago) {
                //print_r($producto); exit;
                $result = $this->model->BuscarTipoPago($tipoPago['id']);
                $data['id'] = $result['id'];
                $data['nombre'] = $tipoPago['nombre']; //$result['descripcion']; eso es el dato de la tabla prodcuto, nombre original con $result es nombre original de $producto es nombre modificado
                $data['codigoComprobante'] =  (empty($tipoPago['codigoComprobante'])) ? '' : $tipoPago['codigoComprobante'];
                $data['precio']  =  (empty($tipoPago['precio'])) ? 0 : ($tipoPago['precio']);
                $data['disabled'] = ($data['id'] == 6) ? 'disabled' : '' ;
                $data['none'] = ($data['id'] == 6) ? 'style="display: none;"' : '' ;

                array_push($array['tipoPago'], $data);
                $total +=  $data['precio'];
            }
        }
       $array['total'] = ($total);

        echo json_encode($array, JSON_UNESCAPED_UNICODE);
        die();
    }

    public function mostrarDatosRetenciones()
    {
        $json = file_get_contents('php://input');
        $datos = json_decode($json, true);
        $array['Retenciones'] = array();
       $total =0;
       //print_r($datos); exit;
        if (!empty($datos)) {
            foreach ($datos as $retencion) {
                //print_r($producto); exit;
                $result = $this->model->BuscarRetencion($retencion['id']);
                $data['id'] = $result['id'];
                $data['descripcion'] = $result['descripcion']; //$result['descripcion']; eso es el dato de la tabla prodcuto, nombre original con $result es nombre original de $producto es nombre modificado
                $data['baseImponible'] =  (empty($retencion['baseImponible'])) ? '' : $retencion['baseImponible'];
                $data['valorRetenido']  =  number_format((empty($retencion['baseImponible'])) ? 0 : (($retencion['baseImponible'] * $result['porcentajeretencion'])/100), 2, '.', ',');
                $data['tipo'] = $result['tipo']; 

                $data['codigo'] = $result['codigo'];
               // $data['totalRetenido'] += $data['valorRetenido'] ;

               $total +=  $data['valorRetenido'];
               array_push($array['Retenciones'], $data);

            }
        }
     $array['totalRetenido'] = number_format($total , 2 , '.',',');

        echo json_encode($array, JSON_UNESCAPED_UNICODE);
        die();
    }
    /* public function reporteExcel()
    {
        $spreadsheet = new Spreadsheet();

        $spreadsheet->getProperties()
            ->setCreator($_SESSION['nombre_usuario'])
            ->setTitle("Listado de Productos");

        $spreadsheet->setActiveSheetIndex(0);

        $hojaActiva = $spreadsheet->getActiveSheet();
        $hojaActiva->getColumnDimension('A')->setWidth(50);
        $hojaActiva->getColumnDimension('B')->setWidth(10);
        $hojaActiva->getColumnDimension('C')->setWidth(20);
        $hojaActiva->getColumnDimension('D')->setWidth(20);
        $hojaActiva->getColumnDimension('E')->setWidth(30);

        $spreadsheet->getActiveSheet()->getStyle('A1:E1')->getFill()
            ->setFillType(Fill::FILL_SOLID)
            ->getStartColor()->setARGB('008cff');

        $spreadsheet->getActiveSheet()->getStyle('A1:E1')
            ->getFont()->getColor()->setARGB(Color::COLOR_WHITE);

        $hojaActiva->setCellValue('A1', 'Producto');
        $hojaActiva->setCellValue('B1', 'Cantidad');
        $hojaActiva->setCellValue('C1', 'Precio Compra');
        $hojaActiva->setCellValue('D1', 'Precio Venta');
        $hojaActiva->setCellValue('E1', 'Categoria');

        $fila = 2;
        $productos = $this->model->getProductos(1);
        foreach ($productos as $producto) {
            $hojaActiva->setCellValue('A' . $fila, $producto['descripcion']);
            $hojaActiva->setCellValue('B' . $fila, $producto['cantidad']);
            $hojaActiva->setCellValue('C' . $fila, $producto['precio_compra']);
            $hojaActiva->setCellValue('D' . $fila, $producto['precio_venta']);
            $hojaActiva->setCellValue('E' . $fila, $producto['categoria']);
            $fila++;
        }

        //Generar archivo Excel
        header('Content-Type: application/vnd.ms-excel');
        header('Content-Disposition: attachment;filename="productos.xlsx"');
        $writer = IOFactory::createWriter($spreadsheet, 'Xlsx');
        $writer->save('php://output');
    }*/

    public function reportePdf()
    {
        ob_start();
        $data['title'] = 'Listado de Productos';
        $data['empresa'] = $this->model->getEmpresa();
        $data['productos'] = $this->model->getProductos(1);
        $this->views->getView('reportes', 'reportesPdf', $data);
        $html = ob_get_clean();
        $dompdf = new Dompdf();
        $options = $dompdf->getOptions();
        $options->set('isJavascriptEnabled', true);
        $options->set('isRemoteEnabled', true);
        $dompdf->setOptions($options);
        $dompdf->loadHtml($html);

        $dompdf->setPaper('A4', 'vertical');

        // Render the HTML as PDF
        $dompdf->render();

        // Output the generated PDF to Browser
        $dompdf->stream('reporte.pdf', array('Attachment' => false));
    }

    /*  public function generarBarcode()
    {
        //$redColor = [255, 0, 0];
        $data['productos'] = $this->model->getProductos(1);
        $generator = new Picqer\Barcode\BarcodeGeneratorPNG();
        $ruta = 'assets/images/barcode/';
        foreach ($data['productos'] as $producto) {
            file_put_contents($ruta . $producto['id']. '.png', $generator->getBarcode($producto['codigo'], $generator::TYPE_CODE_128, 3, 50));
        }
        ob_start();
        $data['title'] = 'Barcode';
        $this->views->getView('reportes', 'barcode', $data);
        $html = ob_get_clean();
        $dompdf = new Dompdf();
        $options = $dompdf->getOptions();
        $options->set('isJavascriptEnabled', true);
        $options->set('isRemoteEnabled', true);
        $dompdf->setOptions($options);
        $dompdf->loadHtml($html);

        $dompdf->setPaper('A4', 'vertical');

        // Render the HTML as PDF
        $dompdf->render();

        // Output the generated PDF to Browser
        $dompdf->stream('reporte.pdf', array('Attachment' => false));
    }*/


    public function codigoBarcode($idProducto)
    {
        //$redColor = [255, 0, 0];
        $data['productos'] = $this->model->codigoBarra($idProducto);
        $generator = new Picqer\Barcode\BarcodeGeneratorPNG();
        $ruta = 'assets/images/barcode/';
        //foreach ($data['productos'] as $producto) {
        file_put_contents($ruta . $data['productos']['codigo'] . '.png', $generator->getBarcode($data['productos']['codigo'], $generator::TYPE_CODE_128));
        //  }
        
        ob_start();
        $data['title'] = 'Barcode';
        $this->views->getView('reportes', 'barcodeProducto', $data);
        $html = ob_get_clean();
        $dompdf = new Dompdf();
        $options = $dompdf->getOptions();
        $options->set('isJavascriptEnabled', true);
        $options->set('isRemoteEnabled', true);
        $dompdf->setOptions($options);
        $dompdf->loadHtml($html);

        //$dompdf->setPaper('A4', 'vertical');
        $dompdf->setPaper(array(0, 0, 225, 500), 'portrait');

        // Render the HTML as PDF
        $dompdf->render();

        // Output the generated PDF to Browser
        $dompdf->stream('reporte.pdf', array('Attachment' => false));
        //$res = array('msg' => 'IMPRESIÓN EXITOSA', 'type' => 'success');

    }
}
function importarExcel($archivoExcel)
{
    $productos = new ProductosModel;

   $documento = IOFactory::load($archivoExcel);

   $HojaExcel = $documento->getSheet(0);
   $FilaDeHojaExcel = $HojaExcel->getHighestDataRow();
   $destino = null;

   for ($fila=2; $fila <= $FilaDeHojaExcel ; $fila++) {
    $codigo = $HojaExcel->getCellByColumnAndRow(1,$fila);
    $nombre = $HojaExcel->getCellByColumnAndRow(2,$fila);
    $precio_compra = $HojaExcel->getCellByColumnAndRow(3,$fila);
    $precio_venta = $HojaExcel->getCellByColumnAndRow(4,$fila);
    $iva = $HojaExcel->getCellByColumnAndRow(5,$fila);
    $id_categoria = $HojaExcel->getCellByColumnAndRow(6,$fila);
   // echo $id_categoria.' '.$id_categoria.' '.$id_categoria. "<br>";exit;

    $verificar = $productos->getValidar('codigo', $codigo, 'registrar', 0);
    if (empty($verificar)) {
        $data = $productos->registrar(
            $codigo,
            $nombre,
            $precio_compra,
            $precio_venta,
            $id_categoria,
            $destino,
            $iva
        );
        if ($data > 0) {           
            $res = array('msg' => 'PRODUCTO REGISTRADO EXITOSAMENTE', 'type' => 'success');
        } else {
            $res = array('msg' => 'ERROR AL REGISTRAR', 'type' => 'error');
        }
    } else {
        $res = array('msg' => 'EL CODIGO DEBE SER ÚNICO', 'type' => 'warning');
    }
    //echo $precio_compra.' '.$precio_venta.' '.$precio_compra. "<br>";
   }
  echo json_encode($res, JSON_UNESCAPED_UNICODE);

}
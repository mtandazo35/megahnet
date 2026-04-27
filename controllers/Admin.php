<?php
require 'vendor/autoload.php';

use Dompdf\Dompdf;
/*use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Style\Color;
use PhpOffice\PhpSpreadsheet\Style\Fill;*/

class Admin extends Controller
{
    private $id_usuario;

    public function __construct()
    {
        parent::__construct();
        session_start();
        if (empty($_SESSION['id_usuario'])) {
            header('Location: ' . BASE_URL);
            exit;
        }
        $this->id_usuario = $_SESSION['id_usuario'];
    }
    //resportes graficos
    public function index()
    {
        $data['title'] = 'Panel Administrativo';
        $data['script'] = 'index.js';
        $data['usuarios'] = $this->model->getTotales('usuarios',1);		
        $data['clientes'] = $this->model->getTotales('clientes',1);
        $data['contratosActivos'] = $this->model->getTotales('contratos',1);
        $data['contratosSuspendidos'] = $this->model->getTotales('contratos',0);
        $data['creditos'] = $this->model->getTotales('creditos',1);

		$data['zonas'] = $this->model->getTotales('zonas',1);
        
        $data['repetidoras'] = $this->model->getRepetidoras(1);
        $data['repetidora'] = $this->model->getRepetidora(1);
           // print_r($data['repetidora'][0]); exit;
        $data['proveedores'] = $this->model->getTotales('proveedor',1);
        $data['productos'] = $this->model->getTotales('productos',1);
        //$data['creditos'] = $this->model->getTotales('datos_cabecera_electronica',2);
        $data['top'] = $this->model->topProductos(5);
        $data['nuevos'] = $this->model->nuevosProductos(5);

        $data['casosIngresado'] = $this->model->getCasos('INGRESADO');
        $data['casosProceso'] = $this->model->getCasos('EN PROCESO');

        $data['contratosPorSuspender'] = $this->model->getContratosPorSuspender(CONTRATOSPORSUSPENDER);

        // Cobranza: lo cobrado en el mes actual y el saldo pendiente total
        $data['cobradoMes']     = $this->model->getCobradoMes(date('Y-m'));
        $data['cobradoPorMes']  = $this->model->getCobradoPorMes(date('Y'));
        $data['pendienteCobro'] = $this->model->getPendienteCobro();

        $this->views->getView('admin', 'home', $data);
    }
    //datos de la empresa
    public function datos()
    {
        if ($_SESSION['rol'] == 2) {
            header('Location: ' . BASE_URL . 'admin/permisos');
            exit;
        }
        $data['title'] = 'Datos de la Empresa';
        $data['script'] = 'admin.js';
        $data['empresa'] = $this->model->getDatos();
        $data['id_usuario'] = $_SESSION['id_usuario'];
        $this->views->getView('admin', 'index', $data);
    }
    public function respaldo()
    {
        if (empty($_SESSION['id_usuario'])) { header('Location: ' . BASE_URL); exit; }
        respaldoBD_descargar();
    }

    public function listarRespaldos()
    {
        if (empty($_SESSION['id_usuario'])) { header('Location: ' . BASE_URL); exit; }
        header('Content-Type: application/json');
        echo json_encode(respaldoBD_listar());
        exit;
    }

    //Actualizar datos de la empresa
    public function modificar()
    {
        // print_r($_FILES); exit;
        if ($_SESSION['rol'] == 2) {
            header('Location: ' . BASE_URL . 'admin/permisos');
            exit;
        }
        if (isset($_POST)) {
            $id = strClean($_POST['id']);
            $ruc = strClean($_POST['ruc']);
            $nombre = strClean($_POST['nombre']);
            $razon = strClean($_POST['razon']);
            $telefono = strClean($_POST['telefono']);
            $correo = strClean($_POST['correo']);
            $direccion = strClean($_POST['direccion']);
            $impuesto = strClean($_POST['impuesto']);
            $mensaje = strClean($_POST['mensaje']);
            $totalitems = strClean($_POST['totalitems']);
            $establecimiento = strClean($_POST['establecimiento']);
            $emision = strClean($_POST['emision']);
            $contabilidad = strClean($_POST['contabilidad']);
            $firmainicio      = isset($_POST['firmainicio'])      ? strClean($_POST['firmainicio'])      : null;
            $firmafinal       = isset($_POST['firmafinal'])       ? strClean($_POST['firmafinal'])       : null;
            $cantidaddocumento= isset($_POST['cantidaddocumento'])? strClean($_POST['cantidaddocumento']): null;
            // Si no llegaron (usuario sin permiso de admin), conservar valores existentes
            if ($firmainicio === null || $firmafinal === null || $cantidaddocumento === null) {
                $__cur = $this->model->getEmpresa();
                if ($firmainicio === null)       $firmainicio       = $__cur['firmainicio']       ?? '';
                if ($firmafinal === null)        $firmafinal        = $__cur['firmafinal']        ?? '';
                if ($cantidaddocumento === null) $cantidaddocumento = $__cur['cantidaddocumento'] ?? '';
            }
            $chelectronica = (strClean(isset($_POST['chelectronica']))) ?  $_POST['chelectronica'] : 0;
            //$logo = $_FILES['foto'];
            $id = strClean($_POST['id']);


            $imgLogo =     $_POST['foto_actual'];
            $imgRemove =     $_POST['foto_remove'];
            $foto = $_FILES['foto'];

            $nombre_foto = $foto['name'];
            $tipo_foto = $foto['type'];
            $urltemp_foto = $foto['tmp_name'];

            //$upd = '';
            if ($nombre_foto != '') {
                $destino = 'assets/images/';
                $img_nombre = 'Logo';
                $imgLogo = $img_nombre . '.jpg';
                $src = $destino . $imgLogo;
            } else {
                if ($_POST['foto_actual'] != $_POST['foto_remove'])
                    $imgLogo = 'Logo2.jpg';
            }

            // Logo de Facturación (segundo uploader): se guarda separado en LogoFactura.jpg.
            // No bloquea la persistencia de la empresa: si falla, solo emite warning.
            $logoFacturaWarning = null;
            $logoFacturaUploadOk = false;
            $logoFacturaRemove = isset($_POST['foto_factura_remove']) && $_POST['foto_factura_remove'] === '1';
            if (!empty($_FILES['foto_factura']['tmp_name']) && is_uploaded_file($_FILES['foto_factura']['tmp_name'])) {
                $ff = $_FILES['foto_factura'];
                if ($ff['size'] > 500 * 1024) {
                    $logoFacturaWarning = 'EL LOGO DE FACTURACIÓN EXCEDE 500 KB';
                } else if (!in_array($ff['type'], ['image/jpg','image/jpeg'])) {
                    $logoFacturaWarning = 'LOGO DE FACTURACIÓN: SOLO JPG/JPEG';
                } else {
                    $logoFacturaUploadOk = true;
                }
            }



            /*  if (!empty($logo['name'])) {
                $img = 'Logo.jpg';
                $directorio = 'assets/images/Logo.jpg';
                move_uploaded_file($logo['tmp_name'], $directorio);
            } else {
                $img = 'Logo2.jpg';
                $directorio = 'assets/images/Logo.jpg';
                unlink($directorio);
            }*/

            if (empty($ruc)) {
                $res = array('msg' => 'EL RUC ES REQUERIDO', 'type' => 'warning');
            } else if (empty($nombre)) {
                $res = array('msg' => 'EL NOMBRE ES REQUERIDO', 'type' => 'warning');
            } else if (empty($razon)) {
                $res = array('msg' => 'LA RAZON SOCIAL ES REQUERIDO', 'type' => 'warning');
            } else if (empty($telefono)) {
                $res = array('msg' => 'EL TELEFONO ES REQUERIDO', 'type' => 'warning');
            } else if (empty($correo)) {
                $res = array('msg' => 'EL CORREO ES REQUERIDO', 'type' => 'warning');
            } else if (empty($direccion)) {
                $res = array('msg' => 'LA DIRECCION ES REQUERIDO', 'type' => 'warning');
            } else if (empty($impuesto)) {
                $res = array('msg' => 'EL IMPUESTO ES REQUERIDO', 'type' => 'warning');
            } else if (empty($totalitems)) {
                $res = array('msg' => 'EL TOTAL DE ITEMS ES REQUERIDO', 'type' => 'warning');
            } else if (empty($establecimiento)) {
                $res = array('msg' => 'EL ESTABLECIMIENTO ES REQUERIDO', 'type' => 'warning');
            } else if (empty($emision)) {
                $res = array('msg' => 'EL PUNTO DE EMISION ES REQUERIDO', 'type' => 'warning');
            } else if (empty($contabilidad)) {
                $res = array('msg' => 'LA FECHA FIRMA INICIO ES REQUERIDO', 'type' => 'warning');
            }   else if (empty($firmainicio)) {
                $res = array('msg' => 'LA FECHA FIRMA FINAL ES REQUERIDO', 'type' => 'warning');
            }  else if (empty($firmafinal)) {
                $res = array('msg' => 'LA CONTABILIDAD ES REQUERIDO', 'type' => 'warning');
            }else if (empty($cantidaddocumento)) {
                $res = array('msg' => 'LA CANTIDAD DOCUMENTO ES REQUERIDO', 'type' => 'warning');
            }
            
            
            else {
                $data = $this->model->actualizar(
                    $ruc,
                    $nombre,
                    $razon,
                    $telefono,
                    $correo,
                    $direccion,
                    $impuesto,
                    $mensaje,
                    $totalitems,
                    $establecimiento,
                    $emision,
                    $contabilidad,
                    $firmainicio,
                    $firmafinal,
                    $cantidaddocumento,
                    $chelectronica,
                    $imgLogo,
                    $id
                );
                // Firma electronica: validar y persistir aun si actualizar() retorno 0 (sin cambios)
                $tokenDir = __DIR__ . '/../facturaelectronica/public/archivos/token';
                if (!is_dir($tokenDir)) { @mkdir($tokenDir, 0755, true); }
                $tokenPathFinal = $tokenDir . '/FIRMA.p12';
                $tieneArchivoNuevo = !empty($_FILES['firma_p12']['tmp_name']) && is_uploaded_file($_FILES['firma_p12']['tmp_name']);
                $tienePassNueva    = !empty($_POST['firma_password']);
                $firmaWarning = null;

                if ($tieneArchivoNuevo || $tienePassNueva) {
                    // Resolver archivo a probar
                    $tmpProbe = $tieneArchivoNuevo ? $_FILES['firma_p12']['tmp_name'] : $tokenPathFinal;
                    if ($tieneArchivoNuevo) {
                        $ext = strtolower(pathinfo($_FILES['firma_p12']['name'], PATHINFO_EXTENSION));
                        if (!in_array($ext, ['p12','pfx'])) {
                            $firmaWarning = 'EXTENSION DE FIRMA INVALIDA: solo .p12 o .pfx';
                        }
                    }
                    if ($firmaWarning === null && !file_exists($tmpProbe)) {
                        $firmaWarning = 'NO HAY ARCHIVO DE FIRMA PARA VALIDAR';
                    }
                    // Resolver contrasena a probar (la nueva si se proveyo, si no la actual de BD)
                    $passProbe = null;
                    if ($firmaWarning === null) {
                        if ($tienePassNueva) {
                            $passProbe = (string)$_POST['firma_password'];
                        } else {
                            $row = $this->model->getEmpresa();
                            if (!empty($row['firma_password'])) {
                                $passProbe = base64_decode($row['firma_password'], true);
                                if ($passProbe === false) { $passProbe = null; }
                            }
                            if (empty($passProbe) && defined('PASS')) { $passProbe = PASS; }
                        }
                        if (empty($passProbe)) {
                            $firmaWarning = 'CONTRASENA DE FIRMA REQUERIDA PARA VALIDAR';
                        }
                    }
                    // Validar pkcs12_read + vigencia
                    if ($firmaWarning === null) {
                        $bin = @file_get_contents($tmpProbe);
                        $certs = null;
                        if ($bin === false || !@openssl_pkcs12_read($bin, $certs, $passProbe)) {
                            $firmaWarning = 'FIRMA INVALIDA: contrasena incorrecta o archivo danado';
                        } else {
                            $parsed = @openssl_x509_parse($certs['cert']);
                            if (empty($parsed['validTo_time_t'])) {
                                $firmaWarning = 'NO SE PUDO LEER LA FECHA DE VENCIMIENTO DE LA FIRMA';
                            } else if (time() > $parsed['validTo_time_t']) {
                                $firmaWarning = 'FIRMA VENCIDA EL ' . date('d/m/Y', $parsed['validTo_time_t']);
                            }
                        }
                    }
                    // Persistir solo si valido
                    if ($firmaWarning === null) {
                        if ($tieneArchivoNuevo) {
                            @move_uploaded_file($_FILES['firma_p12']['tmp_name'], $tokenPathFinal);
                            @chmod($tokenPathFinal, 0644);
                        }
                        if ($tienePassNueva) {
                            $this->model->actualizarFirmaPassword(base64_encode($_POST['firma_password']));
                        }
                    }
                }

                // Persistencia del Logo de Facturación (independiente del UPDATE de la BD)
                $logoFacturaPath = 'assets/images/LogoFactura.jpg';
                if ($logoFacturaUploadOk) {
                    @move_uploaded_file($_FILES['foto_factura']['tmp_name'], $logoFacturaPath);
                    @chmod($logoFacturaPath, 0644);
                }
                if ($logoFacturaRemove && file_exists($logoFacturaPath)) {
                    @unlink($logoFacturaPath);
                }

                if ($data == 1) {
                    $imgDelete = 'assets/images/Logo.jpg';
                    if (file_exists($imgDelete) && $imgRemove !='Logo.jpg') {
                        unlink('assets/images/Logo.jpg');
                    }
                    if ($nombre_foto != '') {
                        move_uploaded_file($urltemp_foto, $src);
                    }
                    if ($firmaWarning !== null) {
                        $res = array('msg' => 'DATOS ACTUALIZADOS pero ' . $firmaWarning, 'type' => 'warning');
                    } else if ($logoFacturaWarning !== null) {
                        $res = array('msg' => 'DATOS ACTUALIZADOS pero ' . $logoFacturaWarning, 'type' => 'warning');
                    } else {
                        $res = array('msg' => 'DATOS ACTUALIZADO EXITOSAMENTE', 'type' => 'success');
                    }
                } else if ($firmaWarning === null && ($tieneArchivoNuevo || $tienePassNueva)) {
                    // Si solo se actualizo la firma y validacion paso
                    $res = array('msg' => 'FIRMA ACTUALIZADA EXITOSAMENTE', 'type' => 'success');
                } else if ($firmaWarning !== null) {
                    $res = array('msg' => $firmaWarning, 'type' => 'warning');
                } else if ($logoFacturaUploadOk || $logoFacturaRemove) {
                    // Solo se actualizó el logo de facturación
                    $res = array('msg' => 'LOGO DE FACTURACIÓN ACTUALIZADO', 'type' => 'success');
                } else if ($logoFacturaWarning !== null) {
                    $res = array('msg' => $logoFacturaWarning, 'type' => 'warning');
                } else {
                    $res = array('msg' => 'ERROR AL ACTUALIZAR', 'type' => 'error');
                }
            }
        } else {
            $res = array('msg' => 'ERROR DESCONOCIDO', 'type' => 'error');
        }
        echo json_encode($res, JSON_UNESCAPED_UNICODE);
        die();
    }

    //reporte graficos
    public function comparacion($anio)
    {
        $desde = $anio . '-01-01';
        $hasta = $anio . '-12-31';

        $dataE = $this->model->calcularVentasComprasElectronica($desde, $hasta, $this->id_usuario);
        $dataF = $this->model->calcularVentasCompras('ventas', $desde, $hasta, $this->id_usuario);
        if ($dataE == true || $dataF == true) {
            for ($i = 0; $i < count($dataE); $i++) {
                $ene =  $dataE['ene'] + $dataF['ene'];
                $feb =  $dataE['feb'] + $dataF['feb'];
                $mar =  $dataE['mar'] + $dataF['mar'];
                $abr =  $dataE['abr'] + $dataF['abr'];
                $may =  $dataE['may'] + $dataF['may'];
                $jun =  $dataE['jun'] + $dataF['jun'];
                $jul =  $dataE['jul'] + $dataF['jul'];
                $ago =  $dataE['ago'] + $dataF['ago'];
                $sep =  $dataE['sep'] + $dataF['sep'];
                $oct =  $dataE['oct'] + $dataF['oct'];
                $nov =  $dataE['nov'] + $dataF['nov'];
                $dic =  $dataE['dic'] + $dataF['dic'];
            }
            $data['venta'] = array(
                'ene' => $ene, 'feb' => $feb, 'mar' => $mar, 'abr' => $abr, 'may' => $may, 'jun' => $jun,
                'jul' => $jul, 'ago' => $ago, 'sep' => $sep, 'oct' => $oct, 'nov' => $nov, 'dic' => $dic
            );
        }
        $data['compra'] = $this->model->calcularVentasCompras('compras', $desde, $hasta, $this->id_usuario);

        $dataTE = $this->model->totalVentasElectronica($desde, $hasta, $this->id_usuario);
        $dataTF = $this->model->totalVentasCompras('ventas', $desde, $hasta, $this->id_usuario);

        $data['totalVentas']['total'] = $dataTE['total'] + $dataTF['total'];
        $data['totalCompras'] = $this->model->totalVentasCompras('compras', $desde, $hasta, $this->id_usuario);

        echo json_encode($data);
        die();
    }

    public function topProductos()
    {
        $data = $this->model->topProductos(5);
        echo json_encode($data);
        die();
    }

    public function gastos($anio)
    {
        $desde = $anio . '-01-01';
        $hasta = $anio . '-12-31';

        $data = $this->model->calcularGatos($desde, $hasta, $this->id_usuario);
        echo json_encode($data);
        die();
    }

    public function minimosProductos()
    {
        $data = $this->model->minimosProductos();
        echo json_encode($data);
        die();
    }

    //PDF - EXCEL de top productos
    public function topProductosPdf()
    {
        ob_start();
        $data['title'] = 'Top Productos';
        $data['empresa'] = $this->model->getEmpresa();
        $data['productos'] = $this->model->topProductos(20);
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

    /*public function topProductosExcel()
     {
         $spreadsheet = new Spreadsheet();
 
         $spreadsheet->getProperties()
             ->setCreator($_SESSION['nombre_usuario'])
             ->setTitle("Top Productos");
 
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
         $productos = $this->model->topProductos(20);
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
         header('Content-Disposition: attachment;filename="topProductos.xlsx"');
         $writer = IOFactory::createWriter($spreadsheet, 'Xlsx');
         $writer->save('php://output');
     }*/

    //PDF - EXCEL de stock minimo
    public function stockMinimoPdf()
    {
        ob_start();
        $data['title'] = 'Stock Mínimo';
        $data['empresa'] = $this->model->getEmpresa();
        $data['productos'] = $this->model->minimosProductosPDF();
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

    /*public function stockMinimoExcel()
     {
         $spreadsheet = new Spreadsheet();
 
         $spreadsheet->getProperties()
             ->setCreator($_SESSION['nombre_usuario'])
             ->setTitle("Productos con Stock Mínimo");
 
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
         $productos = $this->model->minimosProductosPDF();
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
         header('Content-Disposition: attachment;filename="stockMinimo.xlsx"');
         $writer = IOFactory::createWriter($spreadsheet, 'Xlsx');
         $writer->save('php://output');
     }*/

    //pdf - Excel de productos recientes
    public function recientesPdf()
    {
        ob_start();
        $data['title'] = 'Productos Recientes';
        $data['empresa'] = $this->model->getEmpresa();
        $data['productos'] = $this->model->nuevosProductos(20);
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

    /*public function recientesExcel()
     {
         $spreadsheet = new Spreadsheet();
 
         $spreadsheet->getProperties()
             ->setCreator($_SESSION['nombre_usuario'])
             ->setTitle("Productos Recientes");
 
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
         $productos = $this->model->nuevosProductos(20);
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
         header('Content-Disposition: attachment;filename="productosRecientes.xlsx"');
         $writer = IOFactory::createWriter($spreadsheet, 'Xlsx');
         $writer->save('php://output');
     }*/

    //logs de acceso
    public function logs()
    {
        if ($_SESSION['rol'] == 2) {
            header('Location: ' . BASE_URL . 'admin/permisos');
            exit;
        }
        $data['title'] = 'Logs de Acceso';
        $data['script'] = 'logs.js';
        $this->views->getView('admin', 'logs', $data);
    }

    public function verificarFirma()
    {
        if (!defined('FCPATH')) {
            define('FCPATH', __DIR__ . '/../facturaelectronica/');
        }
        $tokenPath = FCPATH . 'public/archivos/token/FIRMA.p12';
        $res = ['ok' => false];

        if (!file_exists($tokenPath) || filesize($tokenPath) <= 0) {
            $res['msg'] = 'NO HAY FIRMA CARGADA';
            echo json_encode($res); die();
        }

        // Resolver clave: usar la del POST si vino, si no la guardada en BD
        $pass = null;
        if (!empty($_POST['firma_password'])) {
            $pass = (string)$_POST['firma_password'];
        } else {
            $row = $this->model->getEmpresa();
            if (!empty($row['firma_password'])) {
                $dec = base64_decode($row['firma_password'], true);
                if ($dec !== false) { $pass = $dec; }
            }
            if (empty($pass)) {
                @include_once FCPATH . 'app/configuration.php';
                if (defined('PASS')) { $pass = PASS; }
            }
        }

        if (empty($pass)) {
            $res['msg'] = 'CONTRASENA NO CONFIGURADA. Ingresela y reintente.';
            echo json_encode($res); die();
        }

        $bin = @file_get_contents($tokenPath);
        $certs = null;
        if ($bin === false || !@openssl_pkcs12_read($bin, $certs, $pass)) {
            $res['msg'] = 'FIRMA NO SE PUDO LEER: contrasena incorrecta o archivo danado.';
            echo json_encode($res); die();
        }

        $parsed = @openssl_x509_parse($certs['cert']);
        $validFromTs = $parsed['validFrom_time_t'] ?? null;
        $validToTs   = $parsed['validTo_time_t']   ?? null;
        if (empty($validToTs)) {
            $res['msg'] = 'NO SE PUDO LEER LA VIGENCIA DEL CERTIFICADO';
            echo json_encode($res); die();
        }

        $now = time();
        $diasRestantes = (int) floor(($validToTs - $now) / 86400);
        if ($now > $validToTs) {
            $estado = 'VENCIDA';
        } else if ($diasRestantes <= 30) {
            $estado = 'POR VENCER';
        } else {
            $estado = 'VIGENTE';
        }

        $subject = $parsed['subject'] ?? [];
        $clavePersistida = !empty($_POST['firma_password']) ? false : true; // true si vino de BD

        $res = [
            'ok'              => true,
            'estado'          => $estado,
            'titular'         => $subject['CN']           ?? '',
            'identificacion'  => $subject['serialNumber'] ?? '',
            'organizacion'    => $subject['O']            ?? '',
            'validFrom'       => $validFromTs ? date('d/m/Y', $validFromTs) : '',
            'validTo'         => date('d/m/Y', $validToTs),
            'diasRestantes'   => $diasRestantes,
            'archivoBytes'    => filesize($tokenPath),
            'claveDesdeBD'    => $clavePersistida,
        ];
        echo json_encode($res, JSON_UNESCAPED_UNICODE);
        die();
    }

    public function listarLogs()
    {
        if ($_SESSION['rol'] == 2) {
            header('Location: ' . BASE_URL . 'admin/permisos');
            exit;
        }
        $data = $this->model->listarLogs();
        echo json_encode($data, JSON_UNESCAPED_UNICODE);
        die();
    }

    public function limpiraDatos()
    {
        if ($_SESSION['rol'] == 2) {
            header('Location: ' . BASE_URL . 'admin/permisos');
            exit;
        }
        $data = $this->model->limpiraDatos();
        if (empty($data)) {
            $res = array('msg' => 'DATOS LIMPIADO POR COMPLETO', 'type' => 'success');
        } else {
            $res = array('msg' => 'ERROR AL ELIMINAR DATOS', 'type' => 'error');
        }
        echo json_encode($res);
        die();
    }

    public function modulos()
    {
        if (empty($_SESSION['id_usuario']) || ($_SESSION['rol'] ?? 0) != 1) {
            header('Location: ' . BASE_URL); exit;
        }
        $data['title']    = 'Modulos del sistema';
        $data['ocultos']  = function_exists('modulosOcultos') ? modulosOcultos() : [];
        $this->views->getView('admin', 'modulos', $data);
    }

    public function guardarModulos()
    {
        header('Content-Type: application/json');
        if (empty($_SESSION['id_usuario']) || ($_SESSION['rol'] ?? 0) != 1) {
            echo json_encode(['ok'=>false,'msg'=>'No autorizado']); exit;
        }
        $body = json_decode(file_get_contents('php://input'), true);
        $ocultos = (is_array($body) && isset($body['ocultos']) && is_array($body['ocultos']))
            ? array_values(array_unique(array_filter(array_map('strval', $body['ocultos']))))
            : [];
        $dir = ROOT_PATH . '/storage';
        if (!is_dir($dir)) @mkdir($dir, 0755, true);
        @file_put_contents($dir . '/modulos.json', json_encode(['ocultos' => $ocultos], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
        echo json_encode(['ok'=>true, 'count'=>count($ocultos)]);
    }

    public function roles()
    {
        if (empty($_SESSION['id_usuario']) || ($_SESSION['rol'] ?? 0) != 1) {
            header('Location: ' . BASE_URL); exit;
        }
        $data['title'] = 'Roles de usuarios';
        // Cargar usuarios agrupados por rol
        try {
            $pdo = new PDO('mysql:host=' . HOSTT . ';dbname=' . DBNAME . ';charset=utf8mb4', USER, PASSWORD,
                [PDO::ATTR_ERRMODE => PDO::ERRMODE_SILENT]);
            $rs = $pdo->query("SELECT id, CONCAT(nombre,' ',apellido) AS nombres, correo, rol, estado FROM usuarios ORDER BY rol, nombre");
            $usuarios = $rs ? $rs->fetchAll(PDO::FETCH_ASSOC) : [];
        } catch (\Throwable $e) { $usuarios = []; }
        $data['usuarios'] = $usuarios;
        $this->views->getView('admin', 'roles', $data);
    }

    public function cambiarRolUsuario()
    {
        header('Content-Type: application/json');
        if (empty($_SESSION['id_usuario']) || ($_SESSION['rol'] ?? 0) != 1) {
            echo json_encode(['ok'=>false,'msg'=>'No autorizado']); exit;
        }
        $body = json_decode(file_get_contents('php://input'), true);
        $id  = (int)($body['id'] ?? 0);
        $rol = (int)($body['rol'] ?? 0);
        if ($id <= 0 || !in_array($rol, [1,2,3], true)) {
            echo json_encode(['ok'=>false,'msg'=>'Datos invalidos']); exit;
        }
        // Proteccion: no degradar al ultimo administrador
        if ($rol !== 1) {
            try {
                $pdo = new PDO('mysql:host=' . HOSTT . ';dbname=' . DBNAME . ';charset=utf8mb4', USER, PASSWORD,
                    [PDO::ATTR_ERRMODE => PDO::ERRMODE_SILENT]);
                $rs = $pdo->prepare("SELECT COUNT(*) AS n FROM usuarios WHERE rol = 1 AND estado = 1 AND id != ?");
                $rs->execute([$id]);
                $rest = (int)($rs->fetch(PDO::FETCH_ASSOC)['n'] ?? 0);
                if ($rest === 0) {
                    echo json_encode(['ok'=>false,'msg'=>'No puedes degradar al unico administrador activo']); exit;
                }
            } catch (\Throwable $e) { /* sigue */ }
        }
        try {
            $pdo = new PDO('mysql:host=' . HOSTT . ';dbname=' . DBNAME . ';charset=utf8mb4', USER, PASSWORD,
                [PDO::ATTR_ERRMODE => PDO::ERRMODE_SILENT]);
            $u = $pdo->prepare("UPDATE usuarios SET rol = ? WHERE id = ?");
            $u->execute([$rol, $id]);
            echo json_encode(['ok'=>true]);
        } catch (\Throwable $e) {
            echo json_encode(['ok'=>false,'msg'=>$e->getMessage()]);
        }
    }

    public function permisos()
    {
        $data['title'] = 'Permisos';
        $this->views->getView('admin', 'permisos', $data);
    }

    public function respaldos()
    {
        if (empty($_SESSION['id_usuario'])) { header('Location: ' . BASE_URL); exit; }
        $data['title'] = 'Respaldos de BD';
        $this->views->getView('admin', 'respaldos', $data);
    }

    public function generarRespaldo()
    {
        header('Content-Type: application/json');
        if (empty($_SESSION['id_usuario'])) { echo json_encode(['ok'=>false]); exit; }
        try {
            $info = respaldoBD_generar();
            echo json_encode(array_merge(['ok'=>true], $info));
        } catch (\Throwable $e) {
            echo json_encode(['ok'=>false, 'error'=>$e->getMessage()]);
        }
        exit;
    }

    /** Lista de tablas que se pueden vaciar (whitelist desde el helper). */
    public function tablasBorrables()
    {
        header('Content-Type: application/json');
        if (empty($_SESSION['id_usuario']) || ($_SESSION['rol'] ?? 0) != 1) {
            echo json_encode(['ok'=>false, 'error'=>'No autorizado']); exit;
        }
        echo json_encode(['ok'=>true, 'tablas'=>respaldoBD_tablasBorrables()]);
        exit;
    }

    /**
     * DESTRUCTIVO. Borra datos de tablas seleccionadas. Crea backup previo
     * automático para rollback. Requiere rol=1 (admin) y confirmación literal
     * "BORRAR DATOS" en el body.
     */
    public function borrarDatos()
    {
        header('Content-Type: application/json');
        if (empty($_SESSION['id_usuario']) || ($_SESSION['rol'] ?? 0) != 1) {
            echo json_encode(['ok'=>false, 'error'=>'Solo administradores']); exit;
        }
        $json = file_get_contents('php://input');
        $datos = json_decode($json, true) ?: [];

        if (($datos['confirm'] ?? '') !== 'BORRAR DATOS') {
            echo json_encode(['ok'=>false, 'error'=>'Confirmación incorrecta — escribe "BORRAR DATOS"']); exit;
        }
        $tablas = $datos['tablas'] ?? [];
        if (!is_array($tablas) || empty($tablas)) {
            echo json_encode(['ok'=>false, 'error'=>'Selecciona al menos una tabla']); exit;
        }
        try {
            $r = respaldoBD_borrarDatos($tablas, (int)$_SESSION['id_usuario']);
            echo json_encode($r);
        } catch (\Throwable $e) {
            echo json_encode(['ok'=>false, 'error'=>$e->getMessage()]);
        }
        exit;
    }

    public function descargarRespaldo($nombre = '')
    {
        if (empty($_SESSION['id_usuario'])) { header('Location: ' . BASE_URL); exit; }
        respaldoBD_descargar_archivo($nombre);
    }

    public function eliminarRespaldo($nombre = '')
    {
        header('Content-Type: application/json');
        if (empty($_SESSION['id_usuario'])) { echo json_encode(['ok'=>false]); exit; }
        $ok = respaldoBD_eliminar($nombre);
        echo json_encode(['ok'=>$ok]);
        exit;
    }

    public function restaurarRespaldo($nombre = '')
    {
        header('Content-Type: application/json');
        if (empty($_SESSION['id_usuario'])) { echo json_encode(['ok'=>false,'error'=>'No autorizado']); exit; }
        try {
            $r = respaldoBD_restaurar($nombre);
            echo json_encode($r);
        } catch (\Throwable $e) {
            echo json_encode(['ok'=>false, 'error'=>$e->getMessage()]);
        }
        exit;
    }

    public function subirRespaldo()
    {
        header('Content-Type: application/json');
        if (empty($_SESSION['id_usuario'])) { echo json_encode(['ok'=>false,'error'=>'No autorizado']); exit; }
        try {
            if (empty($_FILES['archivo'])) throw new Exception('Falta archivo');
            $r = respaldoBD_subir($_FILES['archivo']);
            echo json_encode(array_merge(['ok'=>true], $r));
        } catch (\Throwable $e) {
            echo json_encode(['ok'=>false, 'error'=>$e->getMessage()]);
        }
        exit;
    }

    public function enviarRespaldoEmail()
    {
        header('Content-Type: application/json');
        if (empty($_SESSION['id_usuario'])) { echo json_encode(['ok'=>false,'error'=>'No autorizado']); exit; }
        try {
            $nombre = $_POST['nombre'] ?? '';
            $destinos = array_filter(array_map('trim', explode(',', $_POST['destinos'] ?? '')));
            if (!$destinos) throw new Exception('Sin destinatarios');
            $r = respaldoBD_enviar_email($nombre, $destinos, $_POST['asunto'] ?? null, $_POST['mensaje'] ?? null);
            echo json_encode($r);
        } catch (\Throwable $e) {
            echo json_encode(['ok'=>false, 'error'=>$e->getMessage()]);
        }
        exit;
    }
}

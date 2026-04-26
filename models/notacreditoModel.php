<?php
class notaCreditoModel extends Query
{
    public function __construct()
    {
        parent::__construct();
    }
    public function getProducto($idProducto)
    {
        $sql = "SELECT * FROM productos WHERE id = $idProducto";
        return $this->select($sql);
    }

    public function getSerieElectronica()
    {
        $sql = "SELECT MAX(id) AS total FROM nota_credito_cabecera";
        return $this->select($sql);
    }

    public function buscarPorClaveAcceso($claveAcceso)
    {
        $sql = "SELECT id,fecha,orden_no,cliente,ruc,id_cliente,tipo_identificacion,establecimiento,punto_emi,obligado,totalfactura,claveacceso,secuencial FROM datos_cabecera_electronica 
        WHERE claveacceso = '$claveAcceso'";
        return $this->select($sql);
    }
    public function getFacturaDetalle($orden_no)
    {
        $sql = "SELECT orden_no,cantidad,item,precio_u,total,iva,codproducto,descuento,precio_pvp,por_descuento,id_producto FROM detalle_factura_electronica WHERE orden_no = '$orden_no'";
        return $this->selectAll($sql);
    }
    public function getStockNC($idProducto, $orden_no)
    {
        $sql = "SELECT cantidad FROM detalle_factura_electronica WHERE orden_no = '$orden_no' AND id_producto = '$idProducto'";
        return $this->select($sql);
    }

    public function buscarPorNombre($valor, $orden_no)
    {
        $sql = "SELECT orden_no,cantidad,item,precio_u,total,iva,codproducto,descuento,precio_pvp,por_descuento,id_producto FROM detalle_factura_electronica WHERE item LIKE '%" . $valor . "%' AND orden_no = '$orden_no' LIMIT 10";
        return $this->selectAll($sql);
    }
    public function getCliente($idCliente)
    {
        $sql = "SELECT * FROM clientes WHERE id = $idCliente";
        return $this->select($sql);
    }
    public function actualizarStock($cantidad, $ventas, $idProducto)
    {
        $sql = "UPDATE productos SET cantidad = ? , ventas = ? WHERE id = ?";
        $array = array($cantidad, $ventas, $idProducto);
        return $this->save($sql, $array);
    }

    //movimiento
    public function registrarMovimiento($movimiento, $accion, $cantidad, $stockActual, $idProducto, $id_usuario)
    {
        $sql = "INSERT INTO inventario (movimiento, accion, cantidad, stock_actual, id_producto, id_usuario) VALUES (?,?,?,?,?,?)";
        $array = array($movimiento, $accion, $cantidad, $stockActual, $idProducto, $id_usuario);
        return $this->insertar($sql, $array);
    }

    public function registrarEncabezado(
        $numSerieElectronica,
        $fecha,
        $fechaFactura,
        $serieFactura,
        $clienteNombre,
        $clienteIdentidad,
        $tipoIdentificacion,
        $idCliente,
        $claveAccessoFactura,
        $motivoNotaCredito,
        $empresaEstablecimiento,
        $empresaPuntoemi,
        $serieElectronica,
        $empresaContabilidad,
        $ambiente,
        $total,
        $totalDescuento,
        $idUsuario,
        $idEmpresa
    ) {
        $sql = "INSERT INTO nota_credito_cabecera (orden_no, fecha, fecha_factura,serie_factura,cliente, ruc_cliente,tipo_identificacion,
         id_cliente,claveacceso_factura,motivo,establecimiento,punto_emi,secuencial,obligado,ambiente,total_modificar,total_descuento,id_usuario,id_empresa) VALUES (?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?)";
        $array = array(
            $numSerieElectronica,
            $fecha,
            $fechaFactura,
            $serieFactura,
            $clienteNombre,
            $clienteIdentidad,
            $tipoIdentificacion,
            $idCliente,
            $claveAccessoFactura,
            $motivoNotaCredito,
            $empresaEstablecimiento,
            $empresaPuntoemi,
            $serieElectronica,
            $empresaContabilidad,
            $ambiente,
            $total,
            $totalDescuento,
            $idUsuario,
            $idEmpresa
        );
        return $this->insertar($sql, $array);
    }
    public function registrarDetalle($numSerieElectronica, $cantidad, $descripcion, $precio_siniva,$precio_pvp, $subTotal, $iva, $codigo, $descuentoDetalle, $idProducto)
    {
        $sql = "INSERT INTO nota_credito_detalle (orden_no, cantidad, descripcion, precio_u,precio_pvp,total, iva,codproducto,descuento,id_producto) VALUES (?,?,?,?,?,?,?,?,?,?)";
        $array = array($numSerieElectronica, $cantidad, $descripcion, $precio_siniva,$precio_pvp, $subTotal, $iva, $codigo, $descuentoDetalle, $idProducto);
        return $this->insertar($sql, $array);
    }


 public function actualizarClaveAccesso($claveAccesso, $idVenta)
    {
        $sql = "UPDATE nota_credito_cabecera SET claveacceso = ? WHERE orden_no = ?";
        $array = array($claveAccesso, $idVenta);
        return $this->save($sql, $array);
    }


public function getNotaCreditosElectronica()
    {
        $sql = "SELECT ncc.fecha,ncc.orden_no,ncc.cliente,ncc.estado,ncc.total_modificar,ncc.secuencial, ncc.claveacceso, rs.estado as autorizacion FROM nota_credito_cabecera ncc
         INNER JOIN respuesta_sri rs ON rs.claveAcceso = ncc.claveacceso ORDER BY ncc.fecha DESC LIMIT 1000";
        return $this->selectAll($sql);
    }


public function getnotaCreditoElectronica($idVenta)
    {
        $sql = "SELECT ncc.fecha,ncc.orden_no,ncc.ruc_cliente,ncc.cliente,ncc.estado,ncc.total_modificar,ncc.claveacceso,ncd.cantidad,ncd.codproducto,p.id,c.correo FROM nota_credito_cabecera ncc
        INNER JOIN nota_credito_detalle ncd ON ncd.orden_no=ncc.orden_no 
        INNER JOIN productos p ON p.codigo=ncd.codproducto
        INNER JOIN clientes c ON c.id=ncc.id_cliente
        WHERE ncc.orden_no = $idVenta";
        return $this->selectAll($sql);
    }


 public function getDatosNotaCreditoElectronica($claveAccesso)
    {

        $sql = "SELECT ncc.*,cl.correo FROM nota_credito_cabecera ncc INNER JOIN clientes cl ON cl.id=ncc.id_cliente WHERE ncc.claveacceso = '$claveAccesso'";
        return $this->select($sql);
    }






    public function getNotasCredito()
    {
        $sql = "SELECT nc.*, dc.cliente FROM nota_credito nc 
                INNER JOIN datos_cabecera_electronica dc ON nc.claveacceso = dc.claveacceso";
        return $this->selectAll($sql);
    }

    public function anularNota($idNota)
    {
        $sql = "UPDATE nota_credito SET estado = 0 WHERE id = $idNota";
        return $this->save($sql);
    }
    public function getEmpresa()
    {
        $sql = "SELECT * FROM configuracion";
        return $this->select($sql);
    }



    //NOTA CREDITO
     public function getFacturaCabecera($orden_no)
    {
        $sql = "SELECT * FROM datos_cabecera_electronica WHERE orden_no = ?";
        return $this->select($sql, [$orden_no]);
    }
    public function getCreditoByFactura($orden_no)
    {
        $sql = "SELECT * FROM creditos WHERE id_electronica = ? AND estado = 1";
        return $this->select($sql, [$orden_no]);
    }
     public function registrarAbonoNC($idCredito, $idCliente, $valorNC, $idUsuario)
    {
        $sql = "INSERT INTO abonos (abono, fecha, id_credito, id_usuario, codigo_pago, tipo_pago, id_cliente)
            VALUES (?, NOW(), ?, ?, 'NC', 'NOTA CREDITO', ?)";
        $arr = [$valorNC, $idCredito, $idUsuario, $idCliente];
        return $this->insertar($sql, $arr);
    }
    public function marcarFacturaAnuladaNC($orden_no)
    {
        $sql = "UPDATE datos_cabecera_electronica SET estado = 4 WHERE orden_no = ?";
        return $this->save($sql, [$orden_no]);
    }
     public function anularCreditoPorNotaCredito($idCredito)
    {
        // cuando la NC es total, el crédito debe quedar en 0 y estado cancelado// , monto = 0
        $sql = "UPDATE creditos SET estado = 4 WHERE id = ?";
        return $this->save($sql, [$idCredito]);
    }
    public function actualizarCreditoPendiente($idCredito)
    {
        // Total abonado
        $sql = "SELECT SUM(abono) AS total FROM abonos WHERE id_credito = ?";
        $abonos = $this->select($sql, [$idCredito]);
        $totalAbonos = (float) ($abonos['total'] ?? 0);

        // Monto total del crédito
        $sql2 = "SELECT monto FROM creditos WHERE id = ?";
        $credito = $this->select($sql2, [$idCredito]);
        $montoCredito = (float) $credito['monto'];

        // Si saldo llega a cero → cancelar crédito
        if ($totalAbonos >= $montoCredito) {
            $sqlUpdate = "UPDATE creditos SET estado = 0 WHERE id = ?";
            return $this->save($sqlUpdate, [$idCredito]);
        }

        return true;
    }

}

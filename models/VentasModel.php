<?php
class VentasModel extends Query
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

    public function registrarVenta($productos, $total, $fecha, $hora, $metodo, $descuento, $serie, $idCliente, $idusuario)
    {
        $sql = "INSERT INTO ventas (productos, total, fecha, hora, metodo, descuento, serie, id_cliente, id_usuario) VALUES (?,?,?,?,?,?,?,?,?)";
        $array = array($productos, $total, $fecha, $hora, $metodo, $descuento, $serie, $idCliente, $idusuario);
        return $this->insertar($sql, $array);
    }

    public function actualizarStock($cantidad, $ventas, $idProducto)
    {
        $sql = "UPDATE productos SET cantidad = ? , ventas = ? WHERE id = ?";
        $array = array($cantidad, $ventas, $idProducto);
        return $this->save($sql, $array);
    }
    public function registrarCredito($monto, $fecha, $hora, $idVenta, $idElectronica)
    {
        $sql = "INSERT INTO creditos (monto, fecha, hora, id_venta, id_electronica) VALUES (?,?,?,?,?)";
        $array = array($monto, $fecha, $hora, $idVenta, $idElectronica);
        return $this->insertar($sql, $array);
    }
    public function getEmpresa()
    {
        $sql = "SELECT * FROM configuracion";
        return $this->select($sql);
    }

    public function getVenta($idVenta)
    {
        $sql = "SELECT v.*, c.identidad, c.num_identidad, c.nombre, c.telefono, c.direccion FROM ventas v INNER JOIN clientes c ON v.id_cliente = c.id WHERE v.id = $idVenta";
        return $this->select($sql);
    }
    public function getVentaElectronica($idVenta)
    {
        $sql = "SELECT dce.cliente,dce.fecha,dce.orden_no,dce.ruc,dce.estado,dce.totalfactura,dce.claveacceso,dfe.cantidad,dfe.codproducto,p.id,dce.metodo,c.correo FROM datos_cabecera_electronica dce
        INNER JOIN detalle_factura_electronica dfe ON dfe.orden_no=dce.orden_no 
        INNER JOIN productos p ON p.codigo=dfe.codproducto
        INNER JOIN clientes c ON c.num_identidad=dce.ruc
        WHERE dce.orden_no = $idVenta";
        return $this->selectAll($sql);
    }

    public function getVentas()
    {
        $sql = "SELECT v.*, c.nombre FROM ventas v INNER JOIN clientes c ON v.id_cliente = c.id";
        return $this->selectAll($sql);
    }

    public function getVentasElectronica()
    {
        // LEFT JOIN: mostrar ventas aunque aun no tengan respuesta del SRI
        // Mostrar solo: facturas AUTORIZADAS + facturas pendientes que NO tienen
        // una version gemela ya autorizada (mismo ruc+fecha+total). Asi se ocultan
        // las zombies que quedaron de reintentos previos donde la generacion creaba
        // una fila nueva en cada intento en vez de actualizar la existente.
        $sql = "SELECT dce.fecha, TIME_FORMAT(rs.createdAt, '%H:%i:%s') AS hora,
                       dce.orden_no, dce.cliente, dce.estado, dce.totalfactura, dce.claveacceso,
                       dce.correo, dce.correo_enviado,
                       COALESCE(rs.estado, 'PENDIENTE') AS autorizacion
                FROM datos_cabecera_electronica dce
                LEFT JOIN respuesta_sri rs ON rs.claveAcceso = dce.claveacceso
                LEFT JOIN datos_cabecera_electronica dce_auth
                  ON dce_auth.ruc = dce.ruc
                 AND dce_auth.fecha = dce.fecha
                 AND dce_auth.totalfactura = dce.totalfactura
                 AND dce_auth.id != dce.id
                LEFT JOIN respuesta_sri rs_auth
                  ON rs_auth.claveAcceso = dce_auth.claveacceso AND rs_auth.estado = 'AUTORIZADO'
                WHERE dce.fecha >= DATE_FORMAT(CURDATE(), '%Y-%m-01')
                  AND dce.fecha <  DATE_FORMAT(DATE_ADD(CURDATE(), INTERVAL 1 MONTH), '%Y-%m-01')
                  AND (rs.estado = 'AUTORIZADO' OR rs_auth.id IS NULL)
                GROUP BY dce.id
                ORDER BY dce.id DESC";
        return $this->selectAll($sql);
    }

    public function anular($idVenta)
    {
        $sql = "UPDATE ventas SET estado = ? WHERE id = ?";
        $array = array(0, $idVenta);
        return $this->save($sql, $array);
    }
    public function anularElectronica($idVenta)
    {
        $sql = "UPDATE datos_cabecera_electronica SET estado = ? WHERE orden_no = ?";
        $array = array(0, $idVenta);
        return $this->save($sql, $array);
    }
    public function anularCredito($idVenta, $tabla)
    {
        if ($tabla == 'fisico') {
            $sql = "UPDATE creditos SET estado = ? WHERE id_venta = ?";
            $array = array(2, $idVenta);
            return $this->save($sql, $array);
        } else {
            $sql = "UPDATE creditos SET estado = ? WHERE id_electronica = ?";
            $array = array(2, $idVenta);
            return $this->save($sql, $array);
        }

    }

    public function getSerie()
    {
        $sql = "SELECT MAX(id) AS total FROM ventas";
        return $this->select($sql);
    }
    public function getSerieElectronica()
    {
        $sql = "SELECT MAX(id) AS total FROM datos_cabecera_electronica";
        return $this->select($sql);
    }
    //movimiento
    public function registrarMovimiento($movimiento, $accion, $cantidad, $stockActual, $idProducto, $id_usuario)
    {
        $sql = "INSERT INTO inventario (movimiento, accion, cantidad, stock_actual, id_producto, id_usuario) VALUES (?,?,?,?,?,?)";
        $array = array($movimiento, $accion, $cantidad, $stockActual, $idProducto, $id_usuario);
        return $this->insertar($sql, $array);
    }

    public function getCaja($id_usuario)
    {
        $sql = "SELECT * FROM cajas WHERE estado = 1 AND id_usuario = $id_usuario";
        return $this->select($sql);
    }

    //registro de facturacion Electronica

    public function registrarEncabezado(
        $fecha,
        $numSerieElectronica,
        $clienteNombre,
        $clienteDireccion,
        $clienteTelefono,
        $clienteRuc,
        $tipoIdentificacion,
        $clienteCorreo,
        $empresaEstablecimiento,
        $empresaPuntoemi,
        $empresaRuc,
        $ambiente,
        $empresaRazon,
        $empresaNombre,
        $secuencial,
        $empresaDireccion,
        $empresaObligado,
        $descuento,
        $total,
        $tipoPago,
        $estado,
        $metodo,
        $idusuario,
        $idCliente
    ) {
        $sql = "INSERT INTO datos_cabecera_electronica (fecha, orden_no, cliente, direccion,telefono, ruc,tipo_identificacion,
         correo,establecimiento,punto_emi,ruc_empresa,ambiente,razon_social,nombre_comercial,secuencial,
         direccion_matriz,obligado,totaldescuento,totalfactura,tipopago,estado,metodo,id_usuario,id_cliente) VALUES (?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?)";
        $array = array(
            $fecha,
            $numSerieElectronica,
            $clienteNombre,
            $clienteDireccion,
            $clienteTelefono,
            $clienteRuc,
            $tipoIdentificacion,
            $clienteCorreo,
            $empresaEstablecimiento,
            $empresaPuntoemi,
            $empresaRuc,
            $ambiente,
            $empresaRazon,
            $empresaNombre,
            $secuencial,
            $empresaDireccion,
            $empresaObligado,
            $descuento,
            $total,
            $tipoPago,
            $estado,
            $metodo,
            $idusuario,
            $idCliente
        );
        return $this->insertar($sql, $array);
    }
    public function registrarDetalle($numSerieElectronica, $cantidad, $descripcion, $precio, $total, $iva, $codigo, $descuentoDetalle, $precio_pvp, $descuento, $idProducto)
    {
        $sql = "INSERT INTO detalle_factura_electronica (orden_no, cantidad, item, precio_u,total, iva,codproducto,descuento,precio_pvp,por_descuento,id_producto) VALUES (?,?,?,?,?,?,?,?,?,?,?)";
        $array = array($numSerieElectronica, $cantidad, $descripcion, $precio, $total, $iva, $codigo, $descuentoDetalle, $precio_pvp, $descuento, $idProducto);
        return $this->insertar($sql, $array);
    }
    public function actualizarClaveAccesso($claveAccesso, $idVenta)
    {
        $sql = "UPDATE datos_cabecera_electronica SET claveacceso = ?, estado_proceso = 1, sri_enviado = 1, correo_enviado = 1 WHERE orden_no = ?";
        $array = array($claveAccesso, $idVenta);
        return $this->save($sql, $array);
    }

    public function getFacturaElectronica($claveAccesso)
    {

        $sql = "SELECT * FROM datos_cabecera_electronica WHERE claveacceso = '$claveAccesso'";
        return $this->select($sql);
    }
    public function getFacturaElectronicaDetalle($orden_no)
    {
        $sql = "SELECT orden_no, cantidad, item, precio_u, total, iva, codproducto 
        FROM detalle_factura_electronica WHERE orden_no = $orden_no";
        return $this->selectAll($sql);
    }

    public function deleteFactura($tabla, $campo, $orden_no)
    {
        $sql = "DELETE FROM $tabla WHERE $campo = $orden_no";
        return $this->select($sql);
    }
    public function resetFactura($campo)
    {
        $sql = "ALTER TABLE $campo AUTO_INCREMENT=1";
        return $this->select($sql);
    }
    //datos cliente factura electronica
    public function getCliente($idCliente)
    {
        $sql = "SELECT * FROM clientes WHERE id = $idCliente";
        return $this->select($sql);
    }
    public function getClientes()
    {
        $sql = "SELECT * FROM clientes";
        return $this->selectAll($sql);
    }
    public function cantidadDocumento($fechaActual)
    {
        $sql = "SELECT COUNT(id) AS cantidad FROM datos_cabecera_electronica WHERE fecha LIKE '$fechaActual%'";
        return $this->selectAll($sql);
    }
    public function tipoPago()
    {
        $sql = "SELECT * FROM tipo_pago";
        return $this->selectAll($sql);
    }

    /** Facturas autorizadas con email valido y correo_enviado=0, listas para reenviar. */
    public function getCorreosPendientes($limite = 20)
    {
        $limite = max(1, min(100, (int)$limite));
        $sql = "SELECT dce.orden_no, dce.cliente, dce.correo, dce.claveacceso, dce.fecha, dce.totalfactura, dce.ruc, dce.establecimiento, dce.punto_emi
                FROM datos_cabecera_electronica dce
                INNER JOIN respuesta_sri rs ON rs.claveAcceso = dce.claveacceso
                WHERE rs.estado = 'AUTORIZADO' AND dce.correo_enviado = 0
                  AND dce.correo IS NOT NULL AND dce.correo != ''
                  AND dce.correo REGEXP '^[A-Za-z0-9._%+-]+@[A-Za-z0-9.-]+\.[A-Za-z]{2,}$'
                ORDER BY dce.id DESC
                LIMIT $limite";
        return $this->selectAll($sql);
    }

    public function marcarCorreoEnviado($ordenNo)
    {
        $sql = "UPDATE datos_cabecera_electronica SET correo_enviado = 1 WHERE orden_no = ?";
        return $this->save($sql, [$ordenNo]);
    }
}

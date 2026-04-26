<?php
class OrdenVentaModel extends Query
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
    public function registrarOrdenVenta($productos, $total, $fecha, $hora, $metodo, $descuento, $serie, $estado, $idCliente, $idUsuario,$tipoPago)
    {
        $sql = "INSERT INTO orden_venta (productos, total, fecha, hora, metodo, descuento,serie,estado, id_cliente,id_usuario,tipopago) VALUES (?,?,?,?,?,?,?,?,?,?,?)";
        $array = array($productos, $total, $fecha, $hora, $metodo, $descuento, $serie, $estado, $idCliente, $idUsuario,$tipoPago);
        return $this->insertar($sql, $array);
    }
    public function getEmpresa()
    {
        $sql = "SELECT * FROM configuracion";
        return $this->select($sql);
    }
    public function getOrdenVenta($idOrdenVenta)
    {
        $sql = "SELECT ov.id,ov.productos,ov.total,ov.fecha,ov.hora,ov.descuento,ov.metodo,ov.serie,ov.estado, cl.identidad, cl.num_identidad,cl.correo, cl.nombre, cl.telefono, cl.direccion,CONCAT(u.nombre,' ',u.apellido) AS responsable FROM orden_venta ov 
        INNER JOIN usuarios u ON u.id=ov.id_usuario
        INNER JOIN clientes cl ON ov.id_cliente = cl.id WHERE ov.id = $idOrdenVenta";
        return $this->select($sql);
    }

    public function getOrdenVentas()
    {
        $sql = "SELECT ov.id,ov.productos,ov.total,CONCAT(ov.fecha,' ',ov.hora) AS fecha,ov.metodo,ov.serie,cl.nombre,ov.estado FROM orden_venta ov INNER JOIN clientes cl ON ov.id_cliente = cl.id";
        return $this->selectAll($sql);
    }


    public function getSerie()
    {
        $sql = "SELECT MAX(id) AS total FROM orden_venta";
        return $this->select($sql);
    }
    public function anular($idOrdenVenta)
    {
        $sql = "UPDATE orden_venta SET estado = ? WHERE id = ?";
        $array = array(0, $idOrdenVenta);
        return $this->save($sql, $array);
    }
    public function actualizarStock($cantidad, $ventas, $idProducto)
    {
        $sql = "UPDATE productos SET cantidad = ? , ventas = ? WHERE id = ?";
        $array = array($cantidad, $ventas, $idProducto);
        return $this->save($sql, $array);
    }
    public function registrarMovimiento($movimiento, $accion, $cantidad, $stockActual, $idProducto, $id_usuario)
    {
        $sql = "INSERT INTO inventario (movimiento, accion, cantidad, stock_actual, id_producto, id_usuario) VALUES (?,?,?,?,?,?)";
        $array = array($movimiento, $accion, $cantidad, $stockActual, $idProducto, $id_usuario);
        return $this->insertar($sql, $array);
    }
    public function anularCredito($idVenta, $tabla)
    {
        if ($tabla == 'fisico') {
            $sql = "UPDATE creditos SET estado = ? WHERE id_venta = ?";
            $array = array(2, $idVenta);
            return $this->save($sql, $array);
        } else if ($tabla == 'ordenVenta') {
            $sql = "UPDATE creditos SET estado = ? WHERE id_orden_venta = ?";
            $array = array(2, $idVenta);
            return $this->save($sql, $array);
        } else {
            $sql = "UPDATE creditos SET estado = ? WHERE id_electronica = ?";
            $array = array(2, $idVenta);
            return $this->save($sql, $array);
        }
    }
    public function registrarCredito($monto, $fecha, $hora, $idVenta, $idElectronica,$id_orden_venta)
    {
        $sql = "INSERT INTO creditos (monto, fecha, hora, id_venta, id_electronica,id_orden_venta) VALUES (?,?,?,?,?,?)";
        $array = array($monto, $fecha, $hora, $idVenta, $idElectronica,$id_orden_venta);
        return $this->insertar($sql, $array);
    }
    public function tipoPago()
    {
        $sql = "SELECT * FROM tipo_pago";
        return $this->selectAll($sql);
    }
    public function getCaja($id_usuario)
    {
        $sql = "SELECT * FROM cajas WHERE estado = 1 AND id_usuario = $id_usuario";
        return $this->select($sql);
    }
}

<?php
class CajasModel extends Query
{
    public function __construct()
    {
        parent::__construct();
    }
    public function abrirCaja($monto, $fecha_apertura, $id_usuario)
    {
        $sql = "INSERT INTO cajas (monto_inicial, fecha_apertura, id_usuario) VALUES (?,?,?)";
        $array = array($monto, $fecha_apertura, $id_usuario);
        return $this->insertar($sql, $array);
    }
    public function getCaja($id_usuario)
    {
        $sql = "SELECT * FROM cajas WHERE estado = 1 AND id_usuario = $id_usuario";
        return $this->select($sql);
    }

    public function getCajas()
    {
        $sql = "SELECT c.*, u.nombre FROM cajas c INNER JOIN usuarios u ON c.id_usuario = u.id";
        return $this->selectAll($sql);
    }

    public function registraGasto($monto, $descripcion, $destino, $id_usuario,$idCaja)
    {
        $sql = "INSERT INTO gastos (monto, descripcion, foto, id_usuario,id_caja) VALUES (?,?,?,?,?)";
        $array = array($monto, $descripcion, $destino, $id_usuario,$idCaja);
        return $this->insertar($sql, $array);
    }
    public function getGastos($idUsuario)
    {
        $sql = "SELECT * FROM gastos WHERE id_usuario = $idUsuario AND apertura = 1";
        return $this->selectAll($sql);
    }
    public function getHistorialGastos($idCaja)
    {
        $sql = "SELECT * FROM gastos WHERE id_caja = $idCaja";
        return $this->selectAll($sql);
    }
    public function getEmpresa()
    {
        $sql = "SELECT * FROM configuracion";
        return $this->select($sql);
    }
    //####### movimientos
    public function getVentas($campo, $id_usuario)
    {
        $sql = "SELECT SUM($campo) AS total FROM ventas WHERE metodo = 'CONTADO' AND estado = 1 AND apertura = 1 AND id_usuario = $id_usuario";
        return $this->select($sql);
    }
    public function getVentasElectronicas($campo, $id_usuario)
    {
        $sql = "SELECT SUM($campo) AS total FROM datos_cabecera_electronica WHERE metodo = 'CONTADO' AND estado = 1 AND apertura = 1 AND id_usuario = $id_usuario";
        return $this->select($sql);
    }
    public function getTPElectronica($campo, $id_usuario,$tipopago)
    {
        $sql = "SELECT SUM($campo) AS total FROM datos_cabecera_electronica WHERE metodo = 'CONTADO' AND estado = 1 AND apertura = 1 AND id_usuario = $id_usuario AND tipopago = '$tipopago'";
        return $this->select($sql);
    }
    public function getOrdenVentas($campo, $id_usuario)
    {
        $sql = "SELECT SUM($campo) AS total FROM orden_venta WHERE metodo = 'CONTADO' AND estado = 1 AND apertura = 1 AND id_usuario = $id_usuario";
        return $this->select($sql);
    }

    public function getTPOrdenVentas($campo, $id_usuario,$tipopago)
    {
        $sql = "SELECT SUM($campo) AS total FROM orden_venta WHERE metodo = 'CONTADO' AND estado = 1 AND apertura = 1 AND id_usuario = $id_usuario AND tipopago = '$tipopago'";
        return $this->select($sql);
    }
    public function getApartados($id_usuario)
    {
        $sql = "SELECT SUM(d.monto) AS total FROM detalle_apartado d INNER JOIN apartados a ON d.id_apartado = a.id WHERE d.apertura = 1 AND a.id_usuario = $id_usuario";
        return $this->select($sql);
    }
    public function getAbonos($id_usuario)
    {
        $sql = "SELECT SUM(a.abono) AS total FROM abonos a INNER JOIN creditos c ON a.id_credito = c.id INNER JOIN ventas v ON c.id_venta = v.id WHERE a.apertura = 1 AND a.id_usuario = $id_usuario";
        return $this->select($sql);
    }

    public function getTPAbonos($id_usuario,$tipopago)
    {
        $sql = "SELECT SUM(abono) AS total FROM abonos  WHERE apertura = 1 AND id_usuario = $id_usuario AND tipo_pago = '$tipopago'";
        return $this->select($sql);
    }
    public function getAbonosElectronicas($id_usuario)
    {
        $sql = "SELECT SUM(a.abono) AS total FROM abonos a INNER JOIN creditos c ON a.id_credito = c.id INNER JOIN datos_cabecera_electronica dce ON c.id_electronica = dce.orden_no WHERE a.apertura = 1 AND a.id_usuario = $id_usuario AND a.tipo_pago != 'ANTICIPOS' AND a.tipo_pago != 'RETENCIONES' AND a.tipo_pago != 'VARIOS'";
        return $this->select($sql);
    }
    public function getAbonosOrdenVentas($id_usuario)
    {
        $sql = "SELECT SUM(a.abono) AS total FROM abonos a INNER JOIN creditos c ON a.id_credito = c.id INNER JOIN orden_venta ov ON c.id_orden_venta = ov.id WHERE a.apertura = 1 AND a.id_usuario = $id_usuario AND a.tipo_pago != 'ANTICIPOS' AND a.tipo_pago != 'RETENCIONES' AND a.tipo_pago != 'VARIOS'";
        return $this->select($sql);
    }
    public function getCompras($id_usuario)
    {
        $sql = "SELECT SUM(total) AS total FROM compras WHERE estado = 1 AND apertura = 1 AND id_usuario = $id_usuario";
        return $this->select($sql);
    }
    public function getTotalGastos($id_usuario)
    {
        $sql = "SELECT SUM(monto) AS total FROM gastos WHERE apertura = 1 AND id_usuario = $id_usuario";
        return $this->select($sql);
    }

    public function getTotalVentas($id_usuario)
    {
        $sql = "SELECT COUNT(*) AS total FROM ventas WHERE apertura = 1 AND id_usuario = $id_usuario";
        return $this->select($sql);
    }
    public function getTotalVentasElectronica($id_usuario)
    {
        $sql = "SELECT COUNT(*) AS total FROM datos_cabecera_electronica WHERE apertura = 1 AND id_usuario = $id_usuario";
        return $this->select($sql);
    }

    //cerrar caja
    public function cerrarCaja($fecha_cierre, $montoFinal, $totalVentas, $egresos, $gastos,$efectivo,$bancarisado, $id_usuario)
    {
        $sql = "UPDATE cajas SET fecha_cierre=?, monto_final=?, total_ventas=?, egresos=?, gastos=?,efectivo=?,bancarisado=?, estado=? WHERE estado = ? AND id_usuario = ?";
        $array = array($fecha_cierre, $montoFinal, $totalVentas, $egresos, $gastos,$efectivo,$bancarisado, 0, 1, $id_usuario);
        return $this->save($sql, $array);
    }
    public function actualizarApertura($table, $id_usuario)
    {
        $sql = "UPDATE $table SET apertura = ? WHERE id_usuario = ?";
        $array = array(0, $id_usuario);
        return $this->save($sql, $array);
    }

    public function getHistorialCajas($idCaja)
    {
        $sql = "SELECT * FROM cajas WHERE id = $idCaja";
        return $this->select($sql);
    }
}

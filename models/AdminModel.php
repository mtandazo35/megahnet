<?php
class AdminModel extends Query
{
    public function __construct()
    {
        parent::__construct();
    }
    public function getDatos()
    {
        $sql = "SELECT * FROM configuracion";
        return $this->select($sql);
    }
    public function actualizar(
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
        $img,
        $id
    ) {
        $sql = "UPDATE configuracion SET ruc=? ,nombre=?, razon_social=?, telefono=? ,correo=?,direccion=?, 
        impuesto=?,mensaje=?,totalitems=?,establecimiento=?,puntoemi=?,contabilidad=?,firmainicio=?,firmafinal=?,cantidaddocumento=?,facturaelectronica=?,img=? WHERE id=?";
        $array = array(
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
            $img,
            $id
        );
        return $this->save($sql, $array);
    }

    public function getTotales($table, $estado)
    {
        $sql = "SELECT COUNT(*) AS total FROM $table WHERE estado = $estado";
        return $this->select($sql);
    }
    public function getRepetidoras($estado)
    {
        $sql = "SELECT COUNT(DISTINCT repetidora) AS total FROM contratos WHERE estado = $estado";
        return $this->select($sql);
    }
    public function getRepetidora($estado)
    {
        $sql = "SELECT DISTINCT repetidora AS nombre FROM contratos WHERE estado = $estado";
        return $this->selectAll($sql);
    }

    public function getCasos($estado)
    {
        $sql = "SELECT c.id,CONCAT(c.fecha,' ',c.hora) AS fecha,c.problema_reportado,c.trabajo_realizado,c.observacion,c.estado,cl.nombre AS cliente, cl.telefono AS telefonoCliente,ct.direccion AS direccionContrato,ct.medio, CONCAT(u.nombre,' ',u.apellido) AS responsable FROM casos c
INNER JOIN contratos ct ON ct.id=c.id_contrato
INNER JOIN clientes cl ON cl.id=ct.id_cliente
INNER JOIN grupo_trabajo gt ON gt.id=c.grupo_asignado
INNER JOIN usuarios u ON u.id=gt.id_responsable
WHERE c.estado = '$estado' LIMIT 0,10 ";
        return $this->selectAll($sql);
    }
    public function getContratosPorSuspender($estado)
    {
        $sql = "SELECT COUNT(*) AS total FROM ( SELECT c.id FROM creditos cr
INNER JOIN contratos c ON c.id=cr.id_contrato
INNER JOIN clientes cl ON cl.id=c.id_cliente
WHERE cr.estado = 1
GROUP BY c.id
HAVING COUNT(*) > $estado) AS subconsulta";
        return $this->selectAll($sql);
    }
    public function calcularVentasCompras($table, $desde, $hasta, $id_usuario)
    {
        $sql = "SELECT SUM(IF(MONTH(fecha) = 1, total, 0)) AS ene,
        SUM(IF(MONTH(fecha) = 2, total, 0)) AS feb,
        SUM(IF(MONTH(fecha) = 3, total, 0)) AS mar,
        SUM(IF(MONTH(fecha) = 4, total, 0)) AS abr,
        SUM(IF(MONTH(fecha) = 5, total, 0)) AS may,
        SUM(IF(MONTH(fecha) = 6, total, 0)) AS jun,
        SUM(IF(MONTH(fecha) = 7, total, 0)) AS jul,
        SUM(IF(MONTH(fecha) = 8, total, 0)) AS ago,
        SUM(IF(MONTH(fecha) = 9, total, 0)) AS sep,
        SUM(IF(MONTH(fecha) = 10, total, 0)) AS oct,
        SUM(IF(MONTH(fecha) = 11, total, 0)) AS nov,
        SUM(IF(MONTH(fecha) = 12, total, 0)) AS dic 
        FROM $table WHERE fecha BETWEEN '$desde' AND '$hasta' AND estado=1"; //AND id_usuario = $id_usuario por cada usuario
        return $this->select($sql);
    }
    public function calcularVentasComprasElectronica($desde, $hasta, $id_usuario)
    {
        $sql = "SELECT SUM(IF(MONTH(fecha) = 1, totalfactura, 0)) AS ene,
        SUM(IF(MONTH(fecha) = 2, totalfactura, 0)) AS feb,
        SUM(IF(MONTH(fecha) = 3, totalfactura, 0)) AS mar,
        SUM(IF(MONTH(fecha) = 4, totalfactura, 0)) AS abr,
        SUM(IF(MONTH(fecha) = 5, totalfactura, 0)) AS may,
        SUM(IF(MONTH(fecha) = 6, totalfactura, 0)) AS jun,
        SUM(IF(MONTH(fecha) = 7, totalfactura, 0)) AS jul,
        SUM(IF(MONTH(fecha) = 8, totalfactura, 0)) AS ago,
        SUM(IF(MONTH(fecha) = 9, totalfactura, 0)) AS sep,
        SUM(IF(MONTH(fecha) = 10, totalfactura, 0)) AS oct,
        SUM(IF(MONTH(fecha) = 11, totalfactura, 0)) AS nov,
        SUM(IF(MONTH(fecha) = 12, totalfactura, 0)) AS dic 
        FROM datos_cabecera_electronica WHERE fecha BETWEEN '$desde' AND '$hasta'"; //AND id_usuario = $id_usuario por cada usuario
        return $this->select($sql);
    }

    public function totalVentasCompras($table, $desde, $hasta, $id_usuario)
    {
        $sql = "SELECT COUNT(*) AS total FROM $table WHERE fecha BETWEEN '$desde' AND '$hasta' AND estado = 1"; //AND id_usuario = $id_usuario por cada usuario
        return $this->select($sql);
    }
    public function totalVentasElectronica($desde, $hasta, $id_usuario)
    {
        $sql = "SELECT COUNT(*) AS total FROM datos_cabecera_electronica WHERE fecha BETWEEN '$desde' AND '$hasta' AND estado = 1"; //AND id_usuario = $id_usuario por cada usuario
        return $this->select($sql);
    }

    public function topProductos($cantidad)
    {
        // Top productos REALES: cuenta salidas en inventario (descuento de stock)
        $cantidad = intval($cantidad);
        $sql = "SELECT p.*, c.categoria,
                       COALESCE(s.total_ventas, 0) AS ventas_reales
                FROM productos p
                INNER JOIN categorias c ON p.id_categoria = c.id
                LEFT JOIN (
                    SELECT id_producto, SUM(cantidad) AS total_ventas
                    FROM inventario
                    WHERE accion = 'salida'
                    GROUP BY id_producto
                ) s ON s.id_producto = p.id
                WHERE p.estado = 1
                ORDER BY ventas_reales DESC, p.ventas DESC
                LIMIT $cantidad";
        $rows = $this->selectAll($sql);
        if (is_array($rows)) {
            foreach ($rows as &$r) {
                if (isset($r['ventas_reales'])) {
                    $r['ventas'] = intval($r['ventas_reales']);
                }
            }
        }
        return $rows;
    }

    public function nuevosProductos($cantidad)
    {
        $sql = "SELECT p.*, c.categoria FROM productos p INNER JOIN categorias c ON p.id_categoria = c.id ORDER BY p.id DESC LIMIT $cantidad";
        return $this->selectAll($sql);
    }

    public function calcularGatos($desde, $hasta, $id_usuario)
    {
        $sql = "SELECT SUM(IF(MONTH(fecha) = 1, monto, 0)) AS ene,
        SUM(IF(MONTH(fecha) = 2, monto, 0)) AS feb,
        SUM(IF(MONTH(fecha) = 3, monto, 0)) AS mar,
        SUM(IF(MONTH(fecha) = 4, monto, 0)) AS abr,
        SUM(IF(MONTH(fecha) = 5, monto, 0)) AS may,
        SUM(IF(MONTH(fecha) = 6, monto, 0)) AS jun,
        SUM(IF(MONTH(fecha) = 7, monto, 0)) AS jul,
        SUM(IF(MONTH(fecha) = 8, monto, 0)) AS ago,
        SUM(IF(MONTH(fecha) = 9, monto, 0)) AS sep,
        SUM(IF(MONTH(fecha) = 10, monto, 0)) AS oct,
        SUM(IF(MONTH(fecha) = 11, monto, 0)) AS nov,
        SUM(IF(MONTH(fecha) = 12, monto, 0)) AS dic 
        FROM gastos WHERE fecha BETWEEN '$desde' AND '$hasta' AND id_usuario = $id_usuario";
        return $this->select($sql);
    }

    public function minimosProductos()
    {
        $sql = "SELECT descripcion, cantidad FROM productos WHERE cantidad < 15 LIMIT 5";
        return $this->selectAll($sql);
    }

    //reporte pdf
    public function minimosProductosPDF()
    {
        $sql = "SELECT p.*, c.categoria FROM productos p INNER JOIN categorias c ON p.id_categoria = c.id WHERE p.cantidad < 15";
        return $this->selectAll($sql);
    }

    public function getEmpresa()
    {
        $sql = "SELECT * FROM configuracion";
        return $this->select($sql);
    }

    public function listarLogs()
    {
        $sql = "SELECT * FROM acceso";
        return $this->selectAll($sql);
    }

    public function limpiraDatos()
    {
        $sql = "TRUNCATE acceso";
        return $this->select($sql);
    }
   

    public function actualizarFirmaPassword($passwordEncoded)
    {
        $sql = "UPDATE configuracion SET firma_password = ? WHERE id = 1";
        return $this->save($sql, [$passwordEncoded]);
    }

}
?>

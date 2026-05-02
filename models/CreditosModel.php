<?php
class CreditosModel extends Query
{
    public function __construct()
    {
        parent::__construct();
    }
    public function getCreditos()
    {
        $sql = "SELECT cr.*, cl.nombre FROM creditos cr INNER JOIN ventas v ON cr.id_venta = v.id INNER JOIN clientes cl ON v.id_cliente = cl.id";
        return $this->selectAll($sql);
    }
 /*   public function getCreditosElectronico($estado)
    {
        $sql = "SELECT cr.*,dce.cliente as nombre FROM creditos cr INNER JOIN datos_cabecera_electronica dce ON cr.id_electronica= dce.orden_no WHERE cr.estado = $estado";
        return $this->selectAll($sql);
    }*/
   /* public function getCreditosOrdenVenta($estado)
    {
        $sql = "SELECT cr.*,cl.nombre FROM creditos cr INNER JOIN orden_venta ov ON cr.id_orden_venta= ov.id INNER JOIN clientes cl ON ov.id_cliente = cl.id WHERE cr.estado= $estado";
        return $this->selectAll($sql);
    }*/
    public function getAbono($idCredito)
    {
        $sql = "SELECT SUM(abono) AS total FROM abonos WHERE id_credito = $idCredito";
        return $this->select($sql);
    }

    public function getCreditosConAbonos($estado = null)
{
    $where = is_null($estado) ? '' : "WHERE cr.estado = $estado";

    $sql = "SELECT 
                cr.*, 
                CASE 
                    WHEN cr.id_electronica IS NOT NULL THEN dce.cliente
                    WHEN cr.id_orden_venta IS NOT NULL THEN cl.nombre
                    ELSE ''
                END AS nombre,
                IFNULL(SUM(a.abono), 0) AS abonado
            FROM creditos cr
            LEFT JOIN abonos a ON a.id_credito = cr.id
            LEFT JOIN datos_cabecera_electronica dce ON cr.id_electronica = dce.orden_no
            LEFT JOIN orden_venta ov ON cr.id_orden_venta = ov.id
            LEFT JOIN clientes cl ON ov.id_cliente = cl.id
            $where
            GROUP BY cr.id";
    
    return $this->selectAll($sql);
}


    public function buscarPorNombre($valor)
    {
        $sql = "SELECT cr.*, cl.nombre, cl.telefono, cl.direccion,v.id AS factura, cl.anticipos FROM creditos cr 
        INNER JOIN ventas v ON cr.id_venta = v.id 
        INNER JOIN clientes cl ON v.id_cliente = cl.id
         WHERE (cl.nombre LIKE '%" . $valor . "%' OR v.id LIKE '%" . $valor . "%' ) AND cr.estado = 1 LIMIT 10 ";
        return $this->selectAll($sql);
    }
    /* public function buscarPorNombreElectronico($valor)
    {
        $sql = "SELECT 
                cr.id, cr.monto, cr.fecha, cr.hora, cr.estado,
                dce.cliente AS nombre, dce.telefono, dce.direccion, dce.secuencial AS factura,
                cl.anticipos
            FROM creditos cr
            INNER JOIN datos_cabecera_electronica dce ON dce.secuencial = cr.id_electronica
            INNER JOIN clientes cl ON cl.num_identidad = dce.ruc
            WHERE MATCH(dce.cliente, dce.secuencial) AGAINST (:valor IN BOOLEAN MODE)
              AND cr.estado = 1
            LIMIT 5";

        // Para búsquedas tipo "empieza con" agregamos * al final
        $params = [':valor' => $valor . '*'];
        return $this->selectAllPrepared($sql, $params);
    }*/
    public function buscarPorNombreElectronico($valor)
    {
        $sql = "SELECT 
                cr.id, 
                cr.monto, 
                cr.fecha, 
                cr.estado,
                dce.cliente AS nombre, 
                dce.telefono, 
                dce.direccion, 
                dce.secuencial AS factura,
                cl.anticipos,
                IFNULL(SUM(ab.abono), 0) AS abonado
            FROM creditos cr
            INNER JOIN datos_cabecera_electronica dce ON dce.secuencial = cr.id_electronica
            INNER JOIN clientes cl ON cl.num_identidad = dce.ruc
            LEFT JOIN abonos ab ON ab.id_credito = cr.id
            WHERE MATCH(dce.cliente, dce.secuencial) AGAINST (:valor IN BOOLEAN MODE)
              AND cr.estado = 1
            GROUP BY cr.id
            LIMIT 5";

        $params = [':valor' => $valor . '*'];
        return $this->selectAllPrepared($sql, $params);
    }



    /*  public function buscarPorNombreOrdenVenta($valor)
    {
        $sql = "SELECT cr.*, cl.id AS idCliente, cl.nombre, cl.telefono, cl.direccion,ov.id AS factura, cl.anticipos FROM creditos cr 
		INNER JOIN orden_venta ov ON cr.id_orden_venta = ov.id 
		INNER JOIN clientes cl ON ov.id_cliente = cl.id 
		WHERE (cl.nombre LIKE '" . $valor . "%' OR ov.id LIKE '" . $valor . "%') AND cr.estado = 1 LIMIT 10 ";
        return $this->selectAll($sql);
    }*/
public function buscarPorNombreOrdenVenta($valor)
{
    $sql = "SELECT 
                cr.id,
                cr.monto,
                cr.fecha,
                cr.estado,
                cl.nombre,
				cl.id as idCliente,
                cl.telefono,
                cl.direccion,
                ov.id AS factura,
                cl.anticipos,
                IFNULL(SUM(ab.abono), 0) AS abonado
            FROM creditos cr
            INNER JOIN orden_venta ov ON cr.id_orden_venta = ov.id 
            INNER JOIN clientes cl ON ov.id_cliente = cl.id 
            LEFT JOIN abonos ab ON ab.id_credito = cr.id
            WHERE (cl.nombre LIKE :valor OR ov.id LIKE :valor)
              AND cr.estado = 1
            GROUP BY cr.id
            LIMIT 5";
    
    $params = [':valor' => $valor . '%'];
    return $this->selectAllPrepared($sql, $params);
}


    public function getIdCliente($valor)
    {
        $sql = "SELECT cl.id AS idCliente, dce.cliente as nombre, cl.telefono FROM creditos cr 
INNER JOIN datos_cabecera_electronica dce ON dce.orden_no=cr.id_electronica 
INNER JOIN clientes cl ON cl.num_identidad=dce.ruc
WHERE dce.orden_no = $valor AND cr.estado = 1";
        return $this->select($sql);
    }



    public function buscarPorNombreMonto($idCliente)
    {
        $sql = "SELECT cr.id, cr.monto FROM creditos cr INNER JOIN ventas v ON cr.id_venta = v.id INNER JOIN clientes cl ON v.id_cliente = cl.id WHERE cl.id = $idCliente AND cr.estado = 1";
        return $this->selectAll($sql);
    }
    public function buscarPorNombreElectronicoMonto($idCliente)
    {
        $sql = "SELECT cr.id, cr.monto FROM creditos cr INNER JOIN datos_cabecera_electronica dce ON dce.orden_no=cr.id_electronica WHERE dce.cliente LIKE '%" . $idCliente . "%' AND cr.estado = 1 ";
        return $this->selectAll($sql);
    }
    public function buscarPorNombreOrdenVentaMonto($idCliente)
    {
        $sql = "SELECT cr.id, cr.monto FROM creditos cr INNER JOIN orden_venta ov ON cr.id_orden_venta = ov.id INNER JOIN clientes cl ON ov.id_cliente = cl.id WHERE cl.id = $idCliente AND cr.estado = 1";
        return $this->selectAll($sql);
    }



    public function registrarAbono($monto, $fecha, $idCredito, $id_usuario, $codigoPago, $tipoPago, $idCliente,$anticipo)
    {
        // Validacion estricta: tipo_pago es obligatorio. Sin esto el cierre de
        // caja queda descuadrado (ingresos != efectivo + bancarizado).
        $tp = trim((string)$tipoPago);
        if ($tp === '') {
            throw new InvalidArgumentException('TIPO_PAGO_REQUERIDO');
        }
        $sql = "INSERT INTO abonos (abono,fecha, id_credito,id_usuario,codigo_pago,tipo_pago,id_cliente,anticipo) VALUES (?,?,?,?,?,?,?,?)";
        $array = array($monto, $fecha, $idCredito, $id_usuario, $codigoPago, $tp, $idCliente,$anticipo);
        return $this->insertar($sql, $array);
    }
    public function getCredito($idCredito)
    {
        $sql = "SELECT cr.*, v.productos,cl.id AS idCliente, cl.identidad, cl.num_identidad, cl.nombre, cl.telefono, cl.direccion FROM creditos cr 

        INNER JOIN ventas v ON cr.id_venta = v.id
         INNER JOIN clientes cl ON v.id_cliente = cl.id 
         WHERE cr.id = $idCredito";
        return $this->select($sql);
    }
    public function getCreditoElectronica($idCredito)
    {
        $sql = "SELECT cr.*, dfe.item,dfe.precio_u,dfe.cantidad,dce.ruc as num_identidad, dce.cliente as nombre, dce.telefono ,dce.direccion,p.precio_venta as precio,cl.id AS idCliente FROM creditos cr
        INNER JOIN datos_cabecera_electronica dce ON cr.id_electronica = dce.orden_no
        INNER JOIN clientes cl ON cl.num_identidad=dce.ruc
        INNER JOIN detalle_factura_electronica dfe ON cr.id_electronica = dfe.orden_no
        INNER JOIN productos p ON p.codigo = dfe.codproducto
        WHERE cr.id = $idCredito";
        return $this->selectAll($sql);
    }
    public function getCreditoOrdenVenta($idCredito)
    {
        $sql = "SELECT cr.*, ov.productos, cl.identidad, cl.num_identidad, cl.nombre, cl.telefono, cl.direccion,cl.id AS idCliente FROM creditos cr 
        INNER JOIN orden_venta ov ON cr.id_orden_venta = ov.id
         INNER JOIN clientes cl ON ov.id_cliente = cl.id 
         WHERE cr.id = $idCredito";
        return $this->select($sql);
    }
    public function actualizarCredito($estado, $idCredito)
    {
        $sql = "UPDATE creditos SET estado = ? WHERE id = ?";
        $array = array($estado, $idCredito);
        return $this->save($sql, $array);
    }
    public function actualizarFacturaElectronica($estado, $idVenta)
    {
        $sql = "UPDATE datos_cabecera_electronica SET estado = ? WHERE orden_no = ?";
        $array = array($estado, $idVenta);
        return $this->save($sql, $array);
    }
    public function actualizarFacturaOrdenVenta($estado, $idVenta)
    {
        $sql = "UPDATE orden_venta SET estado = ? WHERE id = ?";
        $array = array($estado, $idVenta);
        return $this->save($sql, $array);
    }

    public function getAbonos($idCredito)
    {
        $sql = "SELECT * FROM abonos WHERE id_credito = $idCredito";
        return $this->selectAll($sql);
    }

    public function getHistorialAbonos()
    {
        $sql = "SELECT * FROM abonos";
        return $this->selectAll($sql);
    }
    public function getHistorialAbonosOrdenVenta()
    {
        $sql = "SELECT a.id,a.abono,a.fecha,a.id_credito,cl.nombre AS cliente FROM abonos a
INNER JOIN creditos cr ON cr.id=a.id_credito
INNER JOIN orden_venta ov ON ov.id=cr.id_orden_venta
INNER JOIN clientes cl ON cl.id=ov.id_cliente";
        return $this->selectAll($sql);
    }
    public function getHistorialAbonosFacturasElectronicas()
    {
        $sql = "SELECT a.id,a.abono,a.fecha,a.id_credito,dce.cliente FROM abonos a
INNER JOIN creditos cr ON cr.id=a.id_credito
INNER JOIN datos_cabecera_electronica dce ON dce.id=cr.id_electronica";
        return $this->selectAll($sql);
    }
    public function getValidar($codigoPago)
    {
        $sql = "SELECT * FROM abonos WHERE codigo_pago = $codigoPago";
        return $this->select($sql);
    }
    public function getEmpresa()
    {
        $sql = "SELECT * FROM configuracion";
        return $this->select($sql);
    }

    public function getCreditoInfo($idCredito)
    {
        $sql = "SELECT  cr.* FROM creditos cr 
         WHERE cr.id = $idCredito";
        return $this->select($sql);
    }

    public function updateAnticipos($anticipo, $idCliente)
    {
        $sql = "UPDATE clientes SET anticipos = ? WHERE id = ?";
        $array = array($anticipo, $idCliente);
        return $this->save($sql, $array);
    }
    public function eliminarAbono($idAbono)
    {
        $sql = "DELETE FROM abonos WHERE id = ?";
        $array = array($idAbono);
        return $this->save($sql, $array);
    }
    public function getAbonoEliminar($idAbono)
    {
        $sql = "SELECT * FROM abonos WHERE id = $idAbono";
        return $this->select($sql);
    }
    public function updateEstadoCredito($estado, $idCredito)
    {
        $sql = "UPDATE creditos SET estado = ? WHERE id = ?";
        $array = array($estado, $idCredito);
        return $this->save($sql, $array);
    }
    public function getCaja($id_usuario)
    {
        $sql = "SELECT * FROM cajas WHERE estado = 1 AND id_usuario = $id_usuario";
        return $this->select($sql);
    }
    public function getCliente($idCliente)
    {
        $sql = "SELECT * FROM clientes WHERE estado = 1 AND id = $idCliente";
        return $this->select($sql);
    }

    public function getCreditosConAbonosPaginado($start, $length, $search, $estado)
    {
        $params = [];
        $sql = "SELECT
                    cr.*,
                    CASE
                        WHEN cr.id_electronica IS NOT NULL THEN dce.cliente
                        WHEN cr.id_orden_venta IS NOT NULL THEN cl.nombre
                        ELSE ''
                    END AS nombre,
                    CASE
                        WHEN cr.id_electronica IS NOT NULL THEN dce.telefono
                        WHEN cr.id_orden_venta IS NOT NULL THEN cl.telefono
                        ELSE ''
                    END AS telefono_cliente,
                    IFNULL((SELECT SUM(a.abono) FROM abonos a WHERE a.id_credito = cr.id), 0) AS abonado
                FROM creditos cr
                LEFT JOIN datos_cabecera_electronica dce ON cr.id_electronica = dce.orden_no
                LEFT JOIN orden_venta ov ON cr.id_orden_venta = ov.id
                LEFT JOIN clientes cl ON ov.id_cliente = cl.id
                WHERE cr.estado = ?";
        $params[] = $estado;
        $sql .= buildSearchClause($search, ["LOWER(COALESCE(dce.cliente, cl.nombre, ''))", 'CAST(cr.id AS CHAR)'], $params, ' AND ');
        $start = max(0, intval($start));
        $length = (intval($length) > 0 && intval($length) <= 200) ? intval($length) : 25;
        $sql .= " ORDER BY cr.fecha DESC, cr.id DESC LIMIT $start, $length";
        return $this->select2($sql, $params);
    }

    public function contarCreditosConAbonos($search, $estado)
    {
        $params = [$estado];
        $sql = "SELECT COUNT(*) AS c FROM creditos cr
                LEFT JOIN datos_cabecera_electronica dce ON cr.id_electronica = dce.orden_no
                LEFT JOIN orden_venta ov ON cr.id_orden_venta = ov.id
                LEFT JOIN clientes cl ON ov.id_cliente = cl.id
                WHERE cr.estado = ?";
        $sql .= buildSearchClause($search, ["LOWER(COALESCE(dce.cliente, cl.nombre, ''))", 'CAST(cr.id AS CHAR)'], $params, ' AND ');
        $r = $this->select2($sql, $params);
        return $r ? intval($r[0]['c']) : 0;
    }

    public function getAbonosUnificadosPaginado($start, $length, $search)
    {
        $start = max(0, intval($start));
        $length = (intval($length) > 0 && intval($length) <= 200) ? intval($length) : 25;
        $params = [];
        $sql = "SELECT * FROM (
                  SELECT a.id, a.id_credito, a.abono, a.fecha,
                         COALESCE(NULLIF(a.tipo_pago,''), 'SIN ESPECIFICAR') AS tipo_pago,
                         a.codigo_pago,
                         COALESCE(NULLIF(cl.nombre,''), NULLIF(dce.cliente,''), '') AS cliente,
                         dce.secuencial AS factura
                  FROM abonos a
                  INNER JOIN creditos cr ON cr.id = a.id_credito
                  LEFT JOIN datos_cabecera_electronica dce ON cr.id_electronica = dce.orden_no
                  LEFT JOIN orden_venta ov ON cr.id_orden_venta = ov.id
                  LEFT JOIN clientes cl ON ov.id_cliente = cl.id
                ) u";
        $sql .= buildSearchClause($search, ["LOWER(u.cliente)", 'CAST(u.id AS CHAR)'], $params, ' WHERE ');
        $sql .= " ORDER BY u.fecha DESC, u.id DESC LIMIT $start, $length";
        return $this->select2($sql, $params);
    }

    public function contarAbonosUnificados($search)
    {
        $params = [];
        $sql = "SELECT COUNT(*) AS c FROM abonos a
                INNER JOIN creditos cr ON cr.id = a.id_credito
                LEFT JOIN datos_cabecera_electronica dce ON cr.id_electronica = dce.orden_no
                LEFT JOIN orden_venta ov ON cr.id_orden_venta = ov.id
                LEFT JOIN clientes cl ON ov.id_cliente = cl.id";
        $sql .= buildSearchClause($search, ["LOWER(COALESCE(cl.nombre, dce.cliente, ''))", 'CAST(a.id AS CHAR)'], $params, ' WHERE ');
        $r = $this->select2($sql, $params);
        return $r ? intval($r[0]['c']) : 0;
    }

}

<?php
class ContratosModel extends Query
{
    public function __construct()
    {
        parent::__construct();
    }
    public function getProducto($idProducto)
    {
        $sql = "SELECT * FROM productos WHERE (id = '$idProducto' OR codigo='$idProducto')";
        return $this->select($sql);
    }
    public function registrarContrato($fecha, $hora, $idCliente, $idUsuario, $datosProductos, $total, $ipUsuario, $repetidora, $ap, $coordenada, $direccion, $comentario, $medio, $comparticion, $chelectronica, $anchoBanda, $discapacidad, $idMikrotik,$ciudad)
    {
        $sql = "INSERT INTO contratos (fecha,hora,id_cliente,id_usuario,productos, total,ip_usuario,repetidora,ap,coordenada,direccion,comentario,medio,comparticion,factura,ancho_banda,discapacidad,id_mikrotik,ciudad) VALUES (?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?)";
        $array = array($fecha, $hora, $idCliente, $idUsuario, $datosProductos, $total, $ipUsuario, $repetidora, $ap, $coordenada, $direccion, $comentario, $medio, $comparticion, $chelectronica, $anchoBanda, $discapacidad, $idMikrotik,$ciudad);
        return $this->insertar($sql, $array);
    }
    public function getContratoPorIp($ipUsuario, $idExcluir = 0)
    {
        $sql = "SELECT c.id, cl.nombre AS nombre_cliente
                FROM contratos c
                INNER JOIN clientes cl ON cl.id = c.id_cliente
                WHERE c.ip_usuario = ? AND c.estado = 1 AND c.id != ?";
        return $this->select($sql, [$ipUsuario, $idExcluir]);
    }

    public function actualizarContrato($fecha, $hora, $idCliente, $idUsuario, $datosProductos, $total, $ipUsuario, $repetidora, $ap, $coordenada, $direccion, $comentario, $medio, $comparticion, $chelectronica, $anchoBanda, $discapacidad, $idMikrotik,$ciudad, $id)
    {
        $sql = "UPDATE contratos SET fecha=?,hora=?,id_cliente=?,id_usuario=?,productos=?, total=?,ip_usuario=?,repetidora=?,ap=?,coordenada=?,direccion=?,comentario=?,medio=?,comparticion=?,factura=?,ancho_banda=?,discapacidad=?,id_mikrotik=?,ciudad=? WHERE id=?";
        $array = array($fecha, $hora, $idCliente, $idUsuario, $datosProductos, $total, $ipUsuario, $repetidora, $ap, $coordenada, $direccion, $comentario, $medio, $comparticion, $chelectronica, $anchoBanda, $discapacidad, $idMikrotik,$ciudad, $id);
        return $this->save($sql, $array);
    }
    public function eliminar($estado, $idContrato)
    {
        $sql = "UPDATE contratos SET estado = ? WHERE id = ?";
        $array = array($estado, $idContrato);
        return $this->save($sql, $array);
    }
    public function getEmpresa()
    {
        $sql = "SELECT * FROM configuracion";
        return $this->select($sql);
    }
    public function getContrato($idContrato)
    {
        $sql = "SELECT c.*, cl.identidad, cl.num_identidad, cl.nombre, cl.telefono, cl.direccion AS direccionCliente,cl.correo,c.id_mikrotik FROM contratos c INNER JOIN clientes cl ON c.id_cliente = cl.id WHERE c.id = $idContrato";
        return $this->select($sql);
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


    public function getContratos($estado)
    {
        $sql = "SELECT c.id,CONCAT(c.fecha,' ',c.hora) AS fecha,c.total,c.direccion,c.comentario,c.ip_usuario,c.repetidora,c.ap,c.medio,c.comparticion,cl.nombre, cl.direccion AS direccionCliente,cl.telefono AS telefonoCliente, c.factura,cl.id AS idCliente FROM contratos c INNER JOIN clientes cl ON cl.id=c.id_cliente WHERE c.estado = $estado";
        return $this->selectAll($sql);
    }
    public function getContratosExcel($estado, $factura)
    {
        $sql = "SELECT c.id,CONCAT(c.fecha,' ',c.hora) AS fecha,c.productos,c.total,c.direccion,c.comentario,c.ip_usuario,c.repetidora,c.ap,c.medio,c.comparticion,cl.nombre, cl.direccion AS direccionCliente,cl.telefono AS telefonoCliente, c.factura,cl.id AS idCliente,c.ciudad FROM contratos c INNER JOIN clientes cl ON cl.id=c.id_cliente WHERE c.estado = $estado AND c.factura = $factura";
        return $this->selectAll($sql);
    }
    public function getContratosSuspender($cantidad)
    {
        //WHERE cr.estado = 1

        $sql = "SELECT cr.id_contrato AS id,c.ip_usuario,cl.id AS id_cliente, cl.nombre,cl.telefono AS telefonoCliente,c.repetidora,c.ap, COUNT(*) AS total FROM creditos cr
INNER JOIN contratos c ON c.id=cr.id_contrato
INNER JOIN clientes cl ON cl.id=c.id_cliente
WHERE cr.estado = 1 AND c.estado = 1
GROUP BY c.id
HAVING COUNT(*) > $cantidad";
        return $this->selectAll($sql);
    }
    public function editar($idContrato)
    {
        $sql = "SELECT c.*,CONCAT(c.fecha,' ',c.hora) AS fecha, cl.identidad, cl.num_identidad, cl.nombre,c.id_cliente, cl.telefono, cl.direccion AS direccionCliente,c.comentario,c.id_mikrotik FROM contratos c 
        INNER JOIN clientes cl ON c.id_cliente = cl.id 
        WHERE c.id = $idContrato";
        return $this->select($sql);
    }

    public function enviarMSM($idContrato)
    {
        $sql = "SELECT c.*,CONCAT(c.fecha,' ',c.hora) AS fecha, cl.identidad, cl.num_identidad, cl.nombre,c.id_cliente, cl.telefono, cl.direccion AS direccionCliente,c.comentario FROM contratos c 
        INNER JOIN clientes cl ON c.id_cliente = cl.id 
        WHERE c.id = $idContrato";
        return $this->select($sql);
    }
    public function clienteNuevo($generarContrato)
    {
        $sql = "SELECT * FROM clientes WHERE generar_contrato = $generarContrato";
        return $this->select($sql);
    }

    public function getCliente($idCliente)
    {
        $sql = "SELECT * FROM clientes WHERE id = '$idCliente'";
        return $this->select($sql);
    }
    public function getIpUsuario($idcontrato)
    {
        $sql = "SELECT ip_usuario,id_mikrotik FROM contratos WHERE id = '$idcontrato'";
        return $this->select($sql);
    }
    public function ipNueva($id, $idZona)
    {

        if ($id == null) {
            $sql = "SELECT i.id,i.ultima,i.red,i.final,i.gateway,z.descripcion,z.id AS idZona FROM ip i INNER JOIN zonas z ON z.id=i.id_zona WHERE z.id= $idZona";
            return $this->select($sql);
        } else {
            $sql = "SELECT i.id,i.ultima,i.red,i.final,i.gateway,z.descripcion,z.id AS idZona FROM ip i INNER JOIN zonas z ON z.id=i.id_zona WHERE i.id= $id";
            return $this->select($sql);
        }
    }
    public function zonas($estado)
    {
        $sql = "SELECT * FROM zonas WHERE estado = $estado";
        return $this->selectAll($sql);
    }
    public function ipNuevaAnuladas($id)
    {

        if ($id == null) {
            $sql = " SELECT * FROM ip_anuladas ORDER BY INET_ATON(ip) ASC LIMIT 0,1";
            return $this->selectAll($sql);
        } else {
            $sql = "SELECT * FROM ip_anuladas WHERE id_zona = $id ORDER BY INET_ATON(ip) ASC LIMIT 0,1";
            return $this->select($sql);
        }
    }
    public function updateGenerarContrato($generarContrato, $id)
    {
        $sql = "UPDATE clientes SET generar_contrato=? WHERE id=?";
        $array = array($generarContrato, $id);
        return $this->save($sql, $array);
    }
    public function updateIp($ultima, $id)
    {
        $sql = "UPDATE ip SET ultima=? WHERE id=?";
        $array = array($ultima, $id);
        return $this->save($sql, $array);
    }

    public function registrarMesFacturar($enero, $febrero, $marzo, $abril, $mayo, $junio, $julio, $agosto, $septiembre, $octubre, $noviembre, $diciembre, $idContrato, $estado)
    {
        $sql = "INSERT INTO mes_facturar (enero,febrero,marzo,abril,mayo, junio,julio,agosto,septiembre,octubre,noviembre,diciembre,id_contrato,estado) VALUES (?,?,?,?,?,?,?,?,?,?,?,?,?,?)";
        $array = array($enero, $febrero, $marzo, $abril, $mayo, $junio, $julio, $agosto, $septiembre, $octubre, $noviembre, $diciembre, $idContrato, $estado);
        return $this->insertar($sql, $array);
    }
    public function buscarPorNombreContrato($valor)
    {
        $sql = "SELECT c.id, c.id_cliente,c.coordenada, c.direccion,c.comentario,cl.nombre,c.productos,c.total,mf.enero,mf.febrero,mf.marzo,mf.abril,mf.mayo,mf.junio,mf.julio,mf.agosto,mf.septiembre,mf.octubre,mf.noviembre,mf.diciembre FROM contratos c INNER JOIN clientes cl ON cl.id=c.id_cliente INNER JOIN mes_facturar mf ON mf.id_contrato=c.id WHERE (cl.nombre LIKE '%" . $valor . "%' OR cl.num_identidad LIKE '%" . $valor . "%') AND c.estado = 1 LIMIT 10 ";
        return $this->selectAll($sql);
    }
    public function registrarIpAnuladas($campo, $ip, $idZona, $idIp)
    {

        if ($campo == 'REGISTRAR') {
            $sql = "INSERT INTO ip_anuladas (ip,id_zona,id_ip) VALUES (?,?,?)";
            $array = array($ip, $idZona, $idIp);
            return $this->insertar($sql, $array);
        } else {
            $sql = "DELETE FROM ip_anuladas WHERE id = ?";
            $array = array($idIp);
            return $this->save($sql, $array);
        }
    }

    public function buscarPorNombre($idCliente)
    {
        $sql = "SELECT cr.id, cr.monto FROM creditos cr INNER JOIN ventas v ON cr.id_venta = v.id INNER JOIN clientes cl ON v.id_cliente = cl.id WHERE cl.id = $idCliente AND cr.estado = 1";
        return $this->selectAll($sql);
    }
    public function buscarPorNombreElectronico($idCliente)
    {
        $sql = "SELECT cr.id, cr.monto FROM creditos cr 
        INNER JOIN datos_cabecera_electronica dce ON dce.orden_no=cr.id_electronica 
        WHERE dce.cliente LIKE '%" . $idCliente . "%' AND cr.estado = 1";
        return $this->selectAll($sql);
    }
    public function buscarPorNombreOrdenVenta($idCliente)
    {
        $sql = "SELECT cr.id, cr.monto FROM creditos cr INNER JOIN orden_venta ov ON cr.id_orden_venta = ov.id INNER JOIN clientes cl ON ov.id_cliente = cl.id WHERE cl.id = $idCliente AND cr.estado = 1";
        return $this->selectAll($sql);
    }
    public function getAbono($idCredito)
    {
        $sql = "SELECT SUM(abono) AS total FROM abonos WHERE id_credito = $idCredito";
        return $this->select($sql);
    }
    public function getCredito($idContrato)
    {
        $sql = "SELECT id, monto FROM creditos WHERE id_contrato = $idContrato";
        return $this->select($sql);
    }
    public function getRepetidoras($estado)
    {
        $sql = "SELECT * FROM repetidoras WHERE estado = $estado ORDER BY ssid";
        return $this->selectAll($sql);
    }
    public function getRepetidora($estado, $ssid)
    {
        $sql = "SELECT * FROM repetidoras WHERE ssid= '$ssid' AND estado = $estado";
        return $this->select($sql);
    }
    public function getMikrotiks($estado)
    {
        $sql = "SELECT * FROM mikrotik WHERE estado = $estado ORDER BY nombre";
        return $this->selectAll($sql);
    }
    public function getMikrotik($id)
    {
        $sql = "SELECT * FROM mikrotik WHERE id = $id";
        return $this->select($sql);
    }
    public function tipoPago()
    {
        $sql = "SELECT * FROM tipo_pago";
        return $this->selectAll($sql);
    }
public function contarContratos($estado )
{
    $params = [];
    $sql = "SELECT COUNT(*) as total FROM contratos";

    if (is_numeric($estado)) {
        $sql .= " WHERE estado != ?";
        $params[] = $estado;
    }

    $result = $this->select2($sql, $params);

    return isset($result[0]['total']) ? intval($result[0]['total']) : 0;
}



    public function getContratosPaginado($start, $length, $search = '')
{
    $params = [];

    $sql = "SELECT c.*, cl.id AS idCliente, cl.nombre, cl.telefono AS telefonoCliente, cl.direccion AS direccionCliente,
            (
                SELECT IFNULL(SUM(a.abono), 0)
                FROM abonos a
                INNER JOIN creditos cr ON cr.id = a.id_credito
                WHERE cr.id_contrato = c.id
                  AND a.fecha LIKE CONCAT(DATE_FORMAT(NOW(), '%Y-%m'), '%')
            ) AS abonos_mes
            FROM contratos c
            INNER JOIN clientes cl ON cl.id = c.id_cliente
            WHERE c.estado != 2";

    $sql .= buildSearchClause($search, ['LOWER(cl.nombre)', 'LOWER(c.ip_usuario)'], $params, " AND ");

    // Sanitiza paginación
    $start = max(0, intval($start));
    $length = intval($length);
    // length=-1 (DataTables "Todos") -> sin limite efectivo
    if ($length === -1) {
        $sql .= " ORDER BY c.fecha DESC";
    } else {
        if ($length <= 0) $length = 10;
        $sql .= " ORDER BY c.fecha DESC LIMIT $start, $length";
    }

    return $this->select2($sql, $params);
}

 public function contarContratosFiltrado($search = '')
{
    $params = [];

    $sql = "SELECT COUNT(*) as total
            FROM contratos c
            INNER JOIN clientes cl ON cl.id = c.id_cliente
            WHERE c.estado != 2";

    $sql .= buildSearchClause($search, ['LOWER(cl.nombre)', 'LOWER(c.ip_usuario)'], $params, " AND ");

    $result = $this->select2($sql, $params);

    return isset($result[0]['total']) ? intval($result[0]['total']) : 0;
}

    public function updateComentario($idContrato, $comentario)
    {
        $sql = "UPDATE contratos SET comentario=? WHERE id=?";
        $array = array($comentario, $idContrato);
        return $this->save($sql, $array);
    }


    public function getContratosSuspenderPaginado($start, $length, $search, $cantidad)
    {
        $params = [];
        $where = "WHERE cr.estado = 1 AND c.estado = 1";
        $where .= buildSearchClause($search, ['LOWER(cl.nombre)', 'LOWER(c.ip_usuario)'], $params, " AND ");
        $start = max(0, intval($start));
        $length = (intval($length) > 0 && intval($length) <= 200) ? intval($length) : 25;
        $cantidad = intval($cantidad);

        $sql = "SELECT cr.id_contrato AS id, c.ip_usuario, cl.id AS id_cliente, cl.nombre,
                       cl.telefono AS telefonoCliente, c.repetidora, c.ap, COUNT(*) AS total
                FROM creditos cr
                INNER JOIN contratos c ON c.id = cr.id_contrato
                INNER JOIN clientes cl ON cl.id = c.id_cliente
                $where
                GROUP BY c.id
                HAVING COUNT(*) > $cantidad
                ORDER BY total DESC";
        $length = intval($length);
        if ($length !== -1) {
            if ($length <= 0) $length = 25;
            $sql .= " LIMIT $start, $length";
        }
        return $this->select2($sql, $params);
    }

    public function contarContratosSuspender($search, $cantidad)
    {
        $params = [];
        $where = "WHERE cr.estado = 1 AND c.estado = 1";
        $where .= buildSearchClause($search, ['LOWER(cl.nombre)', 'LOWER(c.ip_usuario)'], $params, " AND ");
        $cantidad = intval($cantidad);
        $sql = "SELECT COUNT(*) AS c FROM (
                    SELECT c.id FROM creditos cr
                    INNER JOIN contratos c ON c.id = cr.id_contrato
                    INNER JOIN clientes cl ON cl.id = c.id_cliente
                    $where
                    GROUP BY c.id
                    HAVING COUNT(*) > $cantidad
                ) t";
        $r = $this->select2($sql, $params);
        return $r ? intval($r[0]['c']) : 0;
    }


    public function zonasPorMikrotik($idMikrotik)
    {
        $sql = "SELECT id, descripcion FROM zonas WHERE estado = 1 AND id_mikrotik = ? ORDER BY descripcion";
        return $this->select2($sql, [$idMikrotik]);
    }
    public function repetidorasPorMikrotik($idMikrotik)
    {
        $sql = "SELECT id, ssid FROM repetidoras WHERE estado = 1 AND id_mikrotik = ? ORDER BY ssid";
        return $this->select2($sql, [$idMikrotik]);
    }

}
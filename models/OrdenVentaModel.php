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

    public function getOrdenVentas($desde = null, $hasta = null)
    {
        $sql = "SELECT ov.id,ov.id_cliente,ov.productos,ov.total,CONCAT(ov.fecha,' ',ov.hora) AS fecha,ov.metodo,ov.serie,cl.nombre,ov.estado FROM orden_venta ov INNER JOIN clientes cl ON ov.id_cliente = cl.id WHERE 1=1";
        $params = [];
        if (!empty($desde) && DateTime::createFromFormat('Y-m-d', $desde) !== false) {
            $sql .= " AND ov.fecha >= ?";
            $params[] = $desde;
        }
        if (!empty($hasta) && DateTime::createFromFormat('Y-m-d', $hasta) !== false) {
            $sql .= " AND ov.fecha <= ?";
            $params[] = $hasta;
        }
        $sql .= " ORDER BY ov.id DESC";
        return $this->selectAll($sql, $params);
    }

    /**
     * Paginacion server-side para DataTables.
     * Recibe array con: desde, hasta, search, order_col, order_dir, start, length.
     * Retorna: ['rows' => [...], 'total' => N, 'filtered' => N].
     *  - total: COUNT(*) de orden_venta sin ningun filtro (recordsTotal)
     *  - filtered: COUNT(*) con los mismos WHERE pero sin LIMIT (recordsFiltered)
     *  - rows: SELECT con JOIN + WHERE + ORDER BY + LIMIT/OFFSET
     * Usa bindings (?) para todos los valores. La columna de ORDER BY y la
     * direccion se validan contra una lista blanca: NUNCA se interpola lo que
     * viene del cliente directo en el SQL.
     */
    public function getOrdenVentasServerSide($params)
    {
        $desde     = isset($params['desde'])     ? $params['desde']     : null;
        $hasta     = isset($params['hasta'])     ? $params['hasta']     : null;
        $search    = isset($params['search'])    ? trim((string)$params['search']) : '';
        $orderCol  = isset($params['order_col']) ? $params['order_col'] : 'ov.id';
        $orderDir  = isset($params['order_dir']) ? strtoupper($params['order_dir']) : 'DESC';
        $start     = isset($params['start'])     ? (int)$params['start']  : 0;
        $length    = isset($params['length'])    ? (int)$params['length'] : 10;

        // Lista blanca de columnas validas para ORDER BY
        $columnasValidas = [
            'cl.nombre', 'ov.fecha', 'ov.id', 'ov.total', 'ov.metodo', 'ov.estado',
        ];
        if (!in_array($orderCol, $columnasValidas, true)) {
            $orderCol = 'ov.id';
        }
        if ($orderDir !== 'ASC' && $orderDir !== 'DESC') {
            $orderDir = 'DESC';
        }
        if ($start  < 0)   $start  = 0;
        if ($length < 1)   $length = 10;
        if ($length > 100) $length = 100;

        // WHERE compartido entre el COUNT(filtered) y la query de rows
        $where  = " WHERE 1=1";
        $bind   = [];
        if (!empty($desde) && DateTime::createFromFormat('Y-m-d', $desde) !== false) {
            $where .= " AND ov.fecha >= ?";
            $bind[] = $desde;
        }
        if (!empty($hasta) && DateTime::createFromFormat('Y-m-d', $hasta) !== false) {
            $where .= " AND ov.fecha <= ?";
            $bind[] = $hasta;
        }
        if ($search !== '') {
            $where .= " AND (cl.nombre LIKE ? OR ov.serie LIKE ? OR ov.metodo LIKE ?)";
            $like   = '%' . $search . '%';
            $bind[] = $like;
            $bind[] = $like;
            $bind[] = $like;
        }

        // recordsTotal: COUNT(*) global (sin filtros)
        $sqlTotal = "SELECT COUNT(*) AS c FROM orden_venta";
        $rowTotal = $this->select($sqlTotal, []);
        $total    = is_array($rowTotal) && isset($rowTotal['c']) ? (int)$rowTotal['c'] : 0;

        // recordsFiltered: COUNT(*) con los mismos WHERE
        $sqlFiltered = "SELECT COUNT(*) AS c FROM orden_venta ov INNER JOIN clientes cl ON ov.id_cliente = cl.id" . $where;
        $rowFiltered = $this->select($sqlFiltered, $bind);
        $filtered    = is_array($rowFiltered) && isset($rowFiltered['c']) ? (int)$rowFiltered['c'] : 0;

        // Rows: SELECT principal con ORDER BY y LIMIT/OFFSET.
        // $orderCol y $orderDir ya fueron validados arriba contra lista blanca,
        // por eso se pueden interpolar de forma segura. $start/$length son INT.
        $sqlRows = "SELECT ov.id,ov.id_cliente,ov.productos,ov.total,CONCAT(ov.fecha,' ',ov.hora) AS fecha,ov.metodo,ov.serie,cl.nombre,ov.estado FROM orden_venta ov INNER JOIN clientes cl ON ov.id_cliente = cl.id"
                . $where
                . " ORDER BY " . $orderCol . " " . $orderDir
                . " LIMIT " . $length . " OFFSET " . $start;
        $rows = $this->selectAll($sqlRows, $bind);
        if (!is_array($rows)) $rows = [];

        return [
            'rows'     => $rows,
            'total'    => $total,
            'filtered' => $filtered,
        ];
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
    public function registrarCredito($monto, $fecha, $hora, $idVenta, $idElectronica, $id_orden_venta, $id_contrato = null)
    {
        $sql = "INSERT INTO creditos (monto, fecha, hora, id_venta, id_electronica, id_orden_venta, id_contrato) VALUES (?,?,?,?,?,?,?)";
        $array = array($monto, $fecha, $hora, $idVenta, $idElectronica, $id_orden_venta, $id_contrato);
        return $this->insertar($sql, $array);
    }

    /**
     * Devuelve el id del unico contrato vigente del cliente (estado != 2).
     * Si tiene 0 o mas de 1 contrato vigente devuelve null para evitar atribuir abonos a contrato equivocado.
     */
    public function getContratoUnicoActivo($idCliente)
    {
        $sql = "SELECT id FROM contratos WHERE id_cliente = ? AND estado != 2 LIMIT 2";
        $rows = $this->selectAll($sql, [$idCliente]);
        return (is_array($rows) && count($rows) === 1) ? (int)$rows[0]['id'] : null;
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

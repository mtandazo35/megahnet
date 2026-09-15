<?php
class ClientesModel extends Query{
    public function __construct() {
        parent::__construct();
    }
    public function getClientes($estado)
    {
        $sql = "SELECT * FROM clientes WHERE estado = $estado";
        return $this->selectAll($sql);
    }

    // Columnas sobre las que busca el listado de clientes (DataTables serverSide).
    // buildSearchClause pasa el termino a minusculas, por eso LOWER() en las de texto.
    private static $busquedaClientes = ['LOWER(nombre)', 'num_identidad', 'telefono', 'LOWER(correo)', 'LOWER(direccion)'];

    // Lista blanca de columnas por las que se permite ordenar desde DataTables.
    private static $ordenClientes = ['id', 'nombre', 'num_identidad', 'identidad', 'telefono', 'correo', 'direccion'];

    /** Cuenta clientes por estado; con $search aplica el mismo filtro que getClientesPaginado. */
    public function contarClientes($search, $estado)
    {
        $params = [(int)$estado];
        $sql = "SELECT COUNT(*) AS c FROM clientes WHERE estado = ?";
        $sql .= buildSearchClause($search, self::$busquedaClientes, $params, ' AND ');
        $r = $this->select2($sql, $params);
        return $r ? intval($r[0]['c']) : 0;
    }

    /**
     * Listado paginado para DataTables serverSide (activos e inactivos).
     * $ordenCol y $ordenDir vienen de DataTables y se validan contra la lista blanca;
     * el resto va con parametros ligados. LIMIT saneado: start >= 0, length 1..100 (default 10).
     */
    public function getClientesPaginado($start, $length, $search, $estado, $ordenCol = 'nombre', $ordenDir = 'asc')
    {
        $params = [(int)$estado];
        $sql = "SELECT * FROM clientes WHERE estado = ?";
        $sql .= buildSearchClause($search, self::$busquedaClientes, $params, ' AND ');
        $start  = max(0, intval($start));
        $length = (intval($length) > 0 && intval($length) <= 100) ? intval($length) : 10;
        $ordenCol = in_array($ordenCol, self::$ordenClientes, true) ? $ordenCol : 'nombre';
        $ordenDir = (strtolower((string)$ordenDir) === 'desc') ? 'DESC' : 'ASC';
        $sql .= " ORDER BY $ordenCol $ordenDir, id ASC LIMIT $start, $length";
        return $this->select2($sql, $params);
    }
    public function registrar($identidad, $num_identidad, $nombre,
    $telefono, $correo, $direccion, $generarContrato)
    {
        $sql = "INSERT INTO clientes (identidad, num_identidad, nombre, telefono, correo, direccion,generar_contrato) VALUES (?,?,?,?,?,?,?)";
        $array = array($identidad, $num_identidad, $nombre,
        $telefono, $correo, $direccion, $generarContrato);
        return $this->insertar($sql, $array);
    }

    public function getValidar($campo, $valor, $accion, $id)
    {
        if ($accion == 'registrar' && $id == 0) {
            $sql = "SELECT id, nombre, num_identidad, estado FROM clientes WHERE $campo = '$valor'";
        }else{
            $sql = "SELECT id, nombre, num_identidad, estado FROM clientes WHERE $campo = '$valor' AND id != $id";
        }
        return $this->select($sql);
    }

    public function eliminar($estado, $idCliente)
    {
        $sql = "UPDATE clientes SET estado = ? WHERE id = ?";
        $array = array($estado, $idCliente);
        return $this->save($sql, $array);
    }
    public function editar($idCliente)
    {
        $sql = "SELECT * FROM clientes WHERE id = $idCliente";
        return $this->select($sql);
    }

    public function actualizar($identidad, $num_identidad, $nombre,
    $telefono, $correo, $direccion, $id)
    {
        $sql = "UPDATE clientes SET identidad=?, num_identidad=?, nombre=?, telefono=?, correo=?, direccion=? WHERE id=?";
        $array = array($identidad, $num_identidad, $nombre,
        $telefono, $correo, $direccion, $id);
        return $this->save($sql, $array);
    }
    public function buscarPorNombre($valor)
    {
        $sql = "SELECT id, nombre, telefono, direccion, correo FROM clientes WHERE (nombre LIKE ? OR num_identidad LIKE ?) AND estado = 1 LIMIT 10";
        $like = '%' . $valor . '%';
        return $this->selectAll($sql, [$like, $like]);
    }

    /** Cuenta contratos vigentes (estado != 2) — los anulados no bloquean el DELETE definitivo del cliente. */
    public function contarContratosTotales($idCliente)
    {
        $sql = "SELECT COUNT(*) AS total FROM contratos WHERE id_cliente = ? AND estado != 2";
        $r = $this->select($sql, [$idCliente]);
        return isset($r['total']) ? (int)$r['total'] : 0;
    }

    /**
     * Cuenta TODAS las dependencias historicas del cliente (orden_venta,
     * facturas electronicas, NCs, apartados, cotizaciones). Tienen FK a
     * clientes.id y bloquean el DELETE definitivo (preservacion historico).
     */
    public function contarDependenciasHistoricas($idCliente)
    {
        $sql = "SELECT
                  (SELECT COUNT(*) FROM orden_venta WHERE id_cliente = ?) AS ordenes,
                  (SELECT COUNT(*) FROM datos_cabecera_electronica WHERE id_cliente = ?) AS facturas,
                  (SELECT COUNT(*) FROM nota_credito_cabecera WHERE id_cliente = ?) AS notas_credito,
                  (SELECT COUNT(*) FROM apartados WHERE id_cliente = ?) AS apartados,
                  (SELECT COUNT(*) FROM cotizaciones WHERE id_cliente = ?) AS cotizaciones";
        $r = $this->select($sql, [$idCliente, $idCliente, $idCliente, $idCliente, $idCliente]);
        return $r ?: [];
    }

    /** DELETE definitivo (no recuperable). Usar solo si no tiene dependencias historicas. */
    public function eliminarPermanente($id)
    {
        $sql = "DELETE FROM clientes WHERE id = ?";
        return $this->save($sql, [$id]);
    }

    /**
     * Inventario de TODOS los registros del cliente (para el prompt de confirmacion
     * "este cliente tiene X, ¿borrar todo?"). Cuenta contratos (todos los estados),
     * ordenes de venta, facturas SRI, NCs, apartados, cotizaciones, ventas, creditos, abonos.
     */
    public function contarHistoricoTotal($id)
    {
        $id = (int)$id;
        $sql = "SELECT
            (SELECT COUNT(*) FROM contratos WHERE id_cliente=$id) AS contratos,
            (SELECT COUNT(*) FROM orden_venta WHERE id_cliente=$id) AS ordenes,
            (SELECT COUNT(*) FROM datos_cabecera_electronica WHERE id_cliente=$id) AS facturas,
            (SELECT COUNT(*) FROM nota_credito_cabecera WHERE id_cliente=$id) AS notas_credito,
            (SELECT COUNT(*) FROM apartados WHERE id_cliente=$id) AS apartados,
            (SELECT COUNT(*) FROM cotizaciones WHERE id_cliente=$id) AS cotizaciones,
            (SELECT COUNT(*) FROM ventas WHERE id_cliente=$id) AS ventas,
            (SELECT COUNT(*) FROM creditos WHERE id_contrato IN (SELECT id FROM contratos WHERE id_cliente=$id)
                 OR id_orden_venta IN (SELECT id FROM orden_venta WHERE id_cliente=$id)
                 OR id_electronica IN (SELECT id FROM datos_cabecera_electronica WHERE id_cliente=$id)
                 OR id_venta IN (SELECT id FROM ventas WHERE id_cliente=$id)) AS creditos,
            (SELECT COUNT(*) FROM abonos WHERE id_cliente=$id) AS abonos";
        return $this->select($sql) ?: [];
    }

    /**
     * Purga total del cliente y TODOS sus registros asociados (cascada).
     * 1) Respalda todas las filas afectadas a JSON en RUTARESPALDOBD antes de borrar.
     * 2) Borra en una transaccion (FOREIGN_KEY_CHECKS=0) en orden hijo->padre.
     * Devuelve array de conteos borrados por tabla + 'backup' con la ruta del respaldo.
     * Lanza excepcion (y hace rollback) si algo falla -> el controller lo captura.
     */
    public function eliminarClienteCascada($id)
    {
        $id  = (int)$id;
        $con = (new Conexion())->conectar();

        // Subquery-fuente reutilizables (siempre resuelven contra padres aun presentes).
        $creditosSrc = "id_contrato IN (SELECT id FROM contratos WHERE id_cliente=$id)
             OR id_orden_venta IN (SELECT id FROM orden_venta WHERE id_cliente=$id)
             OR id_electronica IN (SELECT id FROM datos_cabecera_electronica WHERE id_cliente=$id)
             OR id_venta IN (SELECT id FROM ventas WHERE id_cliente=$id)";

        // 1) Respaldo de filas afectadas (por si hay que restaurar).
        $backupTables = [
            'clientes'                    => "SELECT * FROM clientes WHERE id=$id",
            'contratos'                   => "SELECT * FROM contratos WHERE id_cliente=$id",
            'orden_venta'                 => "SELECT * FROM orden_venta WHERE id_cliente=$id",
            'datos_cabecera_electronica'  => "SELECT * FROM datos_cabecera_electronica WHERE id_cliente=$id",
            'nota_credito_cabecera'       => "SELECT * FROM nota_credito_cabecera WHERE id_cliente=$id",
            'apartados'                   => "SELECT * FROM apartados WHERE id_cliente=$id",
            'cotizaciones'                => "SELECT * FROM cotizaciones WHERE id_cliente=$id",
            'ventas'                      => "SELECT * FROM ventas WHERE id_cliente=$id",
            'creditos'                    => "SELECT * FROM creditos WHERE $creditosSrc",
            'abonos'                      => "SELECT * FROM abonos WHERE id_cliente=$id OR id_credito IN (SELECT id FROM creditos WHERE $creditosSrc)",
            'casos'                       => "SELECT * FROM casos WHERE id_contrato IN (SELECT id FROM contratos WHERE id_cliente=$id)",
            'mes_facturar'                => "SELECT * FROM mes_facturar WHERE id_contrato IN (SELECT id FROM contratos WHERE id_cliente=$id)",
            'detalle_factura_electronica' => "SELECT * FROM detalle_factura_electronica WHERE orden_no IN (SELECT id FROM datos_cabecera_electronica WHERE id_cliente=$id)",
            'nota_credito_detalle'        => "SELECT * FROM nota_credito_detalle WHERE orden_no IN (SELECT orden_no FROM nota_credito_cabecera WHERE id_cliente=$id)",
            'detalle_apartado'            => "SELECT * FROM detalle_apartado WHERE id_apartado IN (SELECT id FROM apartados WHERE id_cliente=$id)",
            'respuesta_sri'               => "SELECT * FROM respuesta_sri WHERE claveAcceso IN (SELECT claveacceso FROM datos_cabecera_electronica WHERE id_cliente=$id) OR claveAcceso IN (SELECT claveacceso FROM nota_credito_cabecera WHERE id_cliente=$id)",
        ];
        $backup = [];
        foreach ($backupTables as $t => $q) {
            $st = $con->query($q);
            $backup[$t] = $st ? $st->fetchAll(PDO::FETCH_ASSOC) : [];
        }
        $dir = defined('RUTARESPALDOBD') ? RUTARESPALDOBD : '/var/backups/megahnet';
        if (!is_dir($dir)) { @mkdir($dir, 0750, true); }
        $file = rtrim($dir, '/') . '/purga-cliente-' . $id . '-' . date('YmdHis') . '.json';
        $json = json_encode($backup, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
        // Si no se puede respaldar, ABORTAR antes de borrar (el borrado es irreversible).
        if ($json === false || @file_put_contents($file, $json) === false) {
            throw new \RuntimeException("No se pudo escribir el respaldo en $file; borrado abortado.");
        }

        // 2) Borrado transaccional, hijo -> padre (subqueries resuelven contra padres vivos).
        $deletes = [
            'abonos'                      => "DELETE FROM abonos WHERE id_cliente=$id OR id_credito IN (SELECT id FROM creditos WHERE $creditosSrc)",
            'creditos'                    => "DELETE FROM creditos WHERE $creditosSrc",
            'casos'                       => "DELETE FROM casos WHERE id_contrato IN (SELECT id FROM contratos WHERE id_cliente=$id)",
            'mes_facturar'                => "DELETE FROM mes_facturar WHERE id_contrato IN (SELECT id FROM contratos WHERE id_cliente=$id)",
            'detalle_factura_electronica' => "DELETE FROM detalle_factura_electronica WHERE orden_no IN (SELECT id FROM datos_cabecera_electronica WHERE id_cliente=$id)",
            'respuesta_sri'               => "DELETE FROM respuesta_sri WHERE claveAcceso IN (SELECT claveacceso FROM datos_cabecera_electronica WHERE id_cliente=$id) OR claveAcceso IN (SELECT claveacceso FROM nota_credito_cabecera WHERE id_cliente=$id)",
            'nota_credito_detalle'        => "DELETE FROM nota_credito_detalle WHERE orden_no IN (SELECT orden_no FROM nota_credito_cabecera WHERE id_cliente=$id)",
            'detalle_apartado'            => "DELETE FROM detalle_apartado WHERE id_apartado IN (SELECT id FROM apartados WHERE id_cliente=$id)",
            'nota_credito_cabecera'       => "DELETE FROM nota_credito_cabecera WHERE id_cliente=$id",
            'datos_cabecera_electronica'  => "DELETE FROM datos_cabecera_electronica WHERE id_cliente=$id",
            'apartados'                   => "DELETE FROM apartados WHERE id_cliente=$id",
            'cotizaciones'                => "DELETE FROM cotizaciones WHERE id_cliente=$id",
            'orden_venta'                 => "DELETE FROM orden_venta WHERE id_cliente=$id",
            'ventas'                      => "DELETE FROM ventas WHERE id_cliente=$id",
            'contratos'                   => "DELETE FROM contratos WHERE id_cliente=$id",
            'clientes'                    => "DELETE FROM clientes WHERE id=$id",
        ];

        $con->beginTransaction();
        try {
            $con->exec("SET FOREIGN_KEY_CHECKS=0");
            $counts = [];
            foreach ($deletes as $label => $sql) {
                $counts[$label] = $con->exec($sql);
            }
            $con->exec("SET FOREIGN_KEY_CHECKS=1");
            $con->commit();
            $counts['backup'] = $file;
            return $counts;
        } catch (\Throwable $e) {
            $con->rollBack();
            try { $con->exec("SET FOREIGN_KEY_CHECKS=1"); } catch (\Throwable $e2) {}
            throw $e;
        }
    }
}

?>
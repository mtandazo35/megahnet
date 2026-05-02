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
    public function getContratosPorTipoFactura($factura)
    {
        // factura=1 -> factura electronica (facturable). factura=0 -> orden de venta.
        $factura = (int)$factura;
        $sql = "SELECT COUNT(*) AS total FROM contratos WHERE estado = 1 AND factura = $factura";
        return $this->select($sql);
    }
    public function getNotasCreditoMes($yyyymm)
    {
        $sql = "SELECT COUNT(*) AS cantidad,
                       COALESCE(SUM(total_modificar), 0) AS total
                FROM nota_credito_cabecera
                WHERE LEFT(fecha, 7) = ? AND estado = 1";
        return $this->select($sql, [$yyyymm]);
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

    /**
     * Suma de abonos cobrados en un mes (formato 'YYYY-MM').
     * abonos.fecha es TEXT pero se almacena como 'YYYY-MM-DD ...', asi que
     * comparar el prefijo de 7 caracteres es seguro.
     */
    public function getCobradoMes($yyyymm)
    {
        // Solo cobros reales: excluye RETENCIONES (retencion tributaria, no ingreso)
        // y ANTICIPOS (saldos a favor ya cobrados antes que ahora se aplican).
        $sql = "SELECT COALESCE(SUM(abono), 0) AS total,
                       COUNT(*) AS cantidad
                FROM abonos
                WHERE LEFT(fecha, 7) = ?
                  AND UPPER(TRIM(IFNULL(tipo_pago, ''))) NOT IN ('RETENCIONES','ANTICIPOS')";
        return $this->select($sql, [$yyyymm]);
    }

    /**
     * Suma de abonos cobrados en un anio (formato 'YYYY'), agrupados por mes,
     * para alimentar un grafico de evolucion mensual.
     */
    public function getCobradoPorMes($yyyy)
    {
        // Solo cobros reales (mismo criterio que getCobradoMes).
        $sql = "SELECT LEFT(fecha, 7) AS mes,
                       COALESCE(SUM(abono), 0) AS total
                FROM abonos
                WHERE LEFT(fecha, 4) = ?
                  AND UPPER(TRIM(IFNULL(tipo_pago, ''))) NOT IN ('RETENCIONES','ANTICIPOS')
                GROUP BY mes
                ORDER BY mes";
        return $this->selectAll($sql, [$yyyy]);
    }

    /**
     * Saldo pendiente de cobro del MES EN CURSO: suma de (monto - abonado)
     * sobre creditos activos cuya fecha cae dentro del mes actual.
     */
    public function getPendienteCobro()
    {
        // Total + desglose por tipo de origen del credito (factura electronica vs orden venta).
        // id_electronica != NULL => Factura. id_orden_venta != NULL => Recibo (orden venta).
        $sql = "SELECT
                  COALESCE(SUM(c.monto), 0) AS total_monto,
                  COALESCE(SUM(IFNULL(a.pagado, 0)), 0) AS total_pagado,
                  COALESCE(SUM(c.monto), 0) - COALESCE(SUM(IFNULL(a.pagado, 0)), 0) AS pendiente,
                  COUNT(c.id) AS cantidad_creditos,
                  COALESCE(SUM(CASE WHEN c.id_electronica  IS NOT NULL THEN c.monto - IFNULL(a.pagado, 0) ELSE 0 END), 0) AS pendiente_facturas,
                  COALESCE(SUM(CASE WHEN c.id_orden_venta IS NOT NULL THEN c.monto - IFNULL(a.pagado, 0) ELSE 0 END), 0) AS pendiente_recibos,
                  SUM(CASE WHEN c.id_electronica  IS NOT NULL THEN 1 ELSE 0 END) AS cant_facturas,
                  SUM(CASE WHEN c.id_orden_venta IS NOT NULL THEN 1 ELSE 0 END) AS cant_recibos
                FROM creditos c
                LEFT JOIN (
                    SELECT id_credito, SUM(abono) AS pagado
                    FROM abonos
                    GROUP BY id_credito
                ) a ON a.id_credito = c.id
                WHERE c.estado = 1
                  AND c.fecha >= DATE_FORMAT(CURDATE(), '%Y-%m-01')
                  AND c.fecha <  DATE_FORMAT(DATE_ADD(CURDATE(), INTERVAL 1 MONTH), '%Y-%m-01')";
        return $this->select($sql);
    }


    /**
     * Cobros del mes desglosados por tipo de pago (efectivo, transferencia, deposito, etc).
     */
    public function getCobrosDesglose($yyyymm)
    {
        // Excluye solo RETENCIONES (van al cuadro Retenciones). Incluye ANTICIPOS
        // porque el documento de requerimientos lo lista en el desglose.
        // Orden FIJO (segun documento): TRANSFERENCIA, DEPOSITOS, ANTICIPOS, EFECTIVO, resto.
        $sql = "SELECT
                    COALESCE(NULLIF(TRIM(tipo_pago), ''), 'SIN ESPECIFICAR') AS tipo_pago,
                    COUNT(*)            AS cantidad,
                    COALESCE(SUM(abono), 0) AS total
                FROM abonos
                WHERE LEFT(fecha, 7) = ?
                  AND UPPER(TRIM(IFNULL(tipo_pago, ''))) <> 'RETENCIONES'
                GROUP BY tipo_pago
                ORDER BY CASE UPPER(TRIM(IFNULL(tipo_pago,'')))
                            WHEN 'TRANSFERENCIA' THEN 1
                            WHEN 'DEPOSITOS'     THEN 2
                            WHEN 'ANTICIPOS'     THEN 3
                            WHEN 'EFECTIVO'      THEN 4
                            ELSE 99 END,
                         total DESC";
        return $this->selectAll($sql, [$yyyymm]);
    }

    /**
     * Retenciones emitidas en el mes, agrupadas por tipo de impuesto (IVA, Renta).
     * Solo cuenta retenciones con encabezado activo (retencion.estado = 1).
     */
    public function getRetencionesDesglose($yyyymm)
    {
        $sql = "SELECT
                    rd.tipo_impuesto AS tipo,
                    COUNT(DISTINCT r.id) AS cantidad,
                    COALESCE(SUM(rd.valor_retenido), 0) AS total,
                    COALESCE(SUM(rd.base_imponible), 0) AS base
                FROM retencion_detalle rd
                INNER JOIN retencion r ON r.id = rd.id_retencion
                WHERE LEFT(r.fecha, 7) = ? AND r.estado = 1
                GROUP BY rd.tipo_impuesto
                ORDER BY total DESC";
        return $this->selectAll($sql, [$yyyymm]);
    }

    /**
     * Facturacion del mes desglosada por origen y metodo, TODOS los valores CON IVA:
     *  - ventas.total: campo crudo (asumimos con IVA, igual que electronica)
     *  - datos_cabecera_electronica.totalfactura: ya esta con IVA
     *  - orden_venta: se recalcula desde JSON aplicando IVA por producto
     */
    public function getFacturacionDesglose($yyyymm)
    {
        // Ventas y electronica: SQL agregado simple
        $sql = "SELECT 'Ventas fisicas' AS origen, metodo,
                    COUNT(*) AS cantidad, COALESCE(SUM(total), 0) AS total
                FROM ventas
                WHERE LEFT(fecha, 7) = ? AND estado = 1
                GROUP BY metodo

                UNION ALL

                SELECT 'Ventas facturas' AS origen, metodo,
                    COUNT(*) AS cantidad, COALESCE(SUM(totalfactura), 0) AS total
                FROM datos_cabecera_electronica
                WHERE LEFT(fecha, 7) = ? AND estado = 1
                GROUP BY metodo";
        $rows = $this->selectAll($sql, [$yyyymm, $yyyymm]);

        // Orden de Venta: recalcular con IVA desde el JSON (orden_venta.total es sin IVA)
        $sqlOv = "SELECT metodo, productos FROM orden_venta WHERE LEFT(fecha, 7) = ? AND estado = 1";
        $ovRows = $this->selectAll($sqlOv, [$yyyymm]);
        $ovAgg = []; // metodo => ['cantidad'=>n, 'total'=>x]
        foreach ($ovRows as $ov) {
            $met = $ov['metodo'] ?? 'CONTADO';
            $tot = 0;
            $prods = !empty($ov['productos']) ? json_decode($ov['productos'], true) : [];
            if (is_array($prods)) {
                foreach ($prods as $p) {
                    $cant = (float)($p['cantidad'] ?? 0);
                    $prec = (float)($p['precio']   ?? 0);
                    $iva  = (float)($p['iva_producto'] ?? 0);
                    $sub  = $cant * $prec;
                    $tot += ($iva > 0) ? round($sub * (1 + $iva/100), 2) : $sub;
                }
            }
            if (!isset($ovAgg[$met])) $ovAgg[$met] = ['cantidad' => 0, 'total' => 0];
            $ovAgg[$met]['cantidad']++;
            $ovAgg[$met]['total'] += $tot;
        }
        foreach ($ovAgg as $met => $agg) {
            $rows[] = [
                'origen'   => 'Ventas ordenes de venta',
                'metodo'   => $met,
                'cantidad' => $agg['cantidad'],
                'total'    => round($agg['total'], 2),
            ];
        }

        // Orden FIJO segun documento: facturas, ordenes de venta, fisicas
        $rank = [
            'Ventas facturas'         => 1,
            'Ventas ordenes de venta' => 2,
            'Ventas fisicas'          => 3,
        ];
        usort($rows, function($a, $b) use ($rank) {
            $ra = $rank[$a['origen']] ?? 99;
            $rb = $rank[$b['origen']] ?? 99;
            if ($ra !== $rb) return $ra <=> $rb;
            return ((float)$b['total']) <=> ((float)$a['total']);
        });
        return $rows;
    }

    /**
     * Egresos del mes: compras + gastos.
     */
    public function getEgresosDesglose($yyyymm)
    {
        $sql = "SELECT 'Compras' AS concepto,
                    COUNT(*) AS cantidad, COALESCE(SUM(total), 0) AS total
                FROM compras
                WHERE LEFT(fecha, 7) = ? AND estado = 1

                UNION ALL

                SELECT 'Gastos varios' AS concepto,
                    COUNT(*) AS cantidad, COALESCE(SUM(monto), 0) AS total
                FROM gastos
                WHERE LEFT(fecha, 7) = ?";
        return $this->selectAll($sql, [$yyyymm, $yyyymm]);
    }

}
?>

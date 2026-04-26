<?php
require_once 'app/conexion.php';

function calentarCache()
{
    $inicio = microtime(true); //  Inicio

    try {
        $pdo = (new Conexion())->conectar();

        $consultas = [
            // Precarga básica de LIKE (físico)
            "SELECT c.id,CONCAT(c.fecha,' ',c.hora) AS fecha,c.productos,c.total,c.direccion,c.comentario,c.ip_usuario,c.repetidora,c.ap,c.medio,c.comparticion,cl.nombre, cl.direccion AS direccionCliente,cl.telefono AS telefonoCliente, c.factura,cl.id AS idCliente 
            FROM contratos c 
            INNER JOIN clientes cl ON cl.id=c.id_cliente
             WHERE c.estado = 1",

            // Precarga búsqueda en clientes
            "SELECT num_identidad FROM clientes WHERE num_identidad LIKE '1%' LIMIT 10000",

            // Precarga índice FULLTEXT (electrónica)
            "SELECT id FROM datos_cabecera_electronica 
             WHERE MATCH(cliente, secuencial) AGAINST ('a*' IN BOOLEAN MODE) 
             LIMIT 10000",

            // Precarga combinación con créditos
            "SELECT 
                cr.id, cr.monto, cr.fecha, cr.hora, cr.estado,
                dce.cliente AS nombre, dce.telefono, dce.direccion, dce.secuencial AS factura,
                cl.anticipos
            FROM creditos cr
            INNER JOIN datos_cabecera_electronica dce ON dce.secuencial = cr.id_electronica
            INNER JOIN clientes cl ON cl.num_identidad = dce.ruc
            WHERE MATCH(dce.cliente, dce.secuencial) AGAINST ('a*' IN BOOLEAN MODE)
              AND cr.estado = 1
            LIMIT 10000"
        ];

        foreach ($consultas as $sql) {
            $stmt = $pdo->prepare($sql);
            $stmt->execute();
        }
    } catch (PDOException $e) {
        error_log('[Cache Warmer Error] ' . $e->getMessage());
    }

    $fin = microtime(true);
    $tiempo = round(($fin - $inicio) * 1000, 2); // en milisegundos
    error_log("[CacheWarmer] Precarga completada de cacheWarmer en {$tiempo} ms");
}

calentarCache();
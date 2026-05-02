<?php
/**
 * REPARACION: detalle_factura_electronica para facturas que tienen cabecera pero no detalle.
 *
 * Origen del bug: durante un periodo el flujo de facturacion automatica
 * (registrarVentaAutomatico) llamaba a registrarDetalle con 8 args en vez de
 * los 11 esperados (corregido en commit 65f1d50). Las facturas creadas durante
 * ese intervalo quedaron con cabecera en datos_cabecera_electronica pero sin
 * filas correspondientes en detalle_factura_electronica. El cron SRI no podia
 * generar el XML porque el detalle estaba vacio.
 *
 * Este script reconstruye el detalle parseando contratos.productos (JSON) del
 * contrato match (mismo cliente RUC + mismo total). Idempotente: solo procesa
 * facturas del mes en curso que estan SIN detalle.
 *
 * USO: php cron/repair_detalle_factura_electronica.php
 *
 * Compatible PHP 7.4
 */
date_default_timezone_set('America/Guayaquil');
define('ROOT_PATH', realpath(__DIR__ . '/..'));
require_once ROOT_PATH . '/config/Config.php';

$pdo = new PDO('mysql:host=' . HOSTT . ';dbname=' . DBNAME . ';charset=utf8mb4', USER, defined('PASSWORD') ? PASSWORD : '');
$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

$facturas = $pdo->query("
  SELECT dce.id, dce.orden_no, dce.totalfactura, dce.ruc
  FROM datos_cabecera_electronica dce
  LEFT JOIN (SELECT DISTINCT orden_no FROM detalle_factura_electronica) dfe ON dfe.orden_no = dce.orden_no
  WHERE dce.fecha >= DATE_FORMAT(CURDATE(), '%Y-%m-01')
    AND dce.fecha <  DATE_FORMAT(DATE_ADD(CURDATE(), INTERVAL 1 MONTH), '%Y-%m-01')
    AND dfe.orden_no IS NULL
")->fetchAll(PDO::FETCH_ASSOC);

echo 'Facturas a reparar: ' . count($facturas) . PHP_EOL;
$ok = 0; $fail = 0;
foreach ($facturas as $f) {
    $st = $pdo->prepare("
        SELECT cont.productos FROM contratos cont
        INNER JOIN clientes cl ON cl.id = cont.id_cliente
        WHERE cl.num_identidad = ? AND cont.estado = 1 AND cont.factura = 1 AND cont.total = ?
        LIMIT 1
    ");
    $st->execute([$f['ruc'], $f['totalfactura']]);
    $contrato = $st->fetch(PDO::FETCH_ASSOC);
    if (!$contrato || empty($contrato['productos'])) {
        echo "  SKIP orden=" . $f['orden_no'] . ' -- sin contrato match' . PHP_EOL;
        $fail++;
        continue;
    }
    $productos = json_decode($contrato['productos'], true);
    if (!is_array($productos)) {
        echo "  SKIP orden=" . $f['orden_no'] . ' -- JSON invalido' . PHP_EOL;
        $fail++;
        continue;
    }
    $insStmt = $pdo->prepare("
      INSERT INTO detalle_factura_electronica
      (orden_no, cantidad, item, precio_u, total, iva, codproducto, descuento, precio_pvp, por_descuento, id_producto)
      VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
    ");
    try {
        $pdo->beginTransaction();
        foreach ($productos as $p) {
            $iva = (int)($p['iva_producto'] ?? 0);
            $cantidad = (int)($p['cantidad'] ?? 1);
            $precio_pvp = (float)($p['precio'] ?? 0);
            $precio_u = $iva > 0 ? round($precio_pvp / (1 + $iva/100), 4) : $precio_pvp;
            $total = round($precio_u * $cantidad, 4);
            $pst = $pdo->prepare("SELECT codigo FROM productos WHERE id = ? LIMIT 1");
            $pst->execute([$p['id']]);
            $cod = $pst->fetchColumn() ?: '';
            $insStmt->execute([
                $f['orden_no'], $cantidad, $p['nombre'] ?? '', $precio_u, $total,
                $iva, $cod, 0, $precio_pvp, 0, (int)($p['id'] ?? 0)
            ]);
        }
        $pdo->commit();
        $ok++;
        echo "  OK orden=" . $f['orden_no'] . ' (' . count($productos) . ' productos)' . PHP_EOL;
    } catch (Throwable $e) {
        $pdo->rollBack();
        $fail++;
        echo "  FAIL orden=" . $f['orden_no'] . ' -- ' . $e->getMessage() . PHP_EOL;
    }
}
echo PHP_EOL . "Reparadas: $ok | Fallidas: $fail" . PHP_EOL;

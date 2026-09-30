<?php
/**
 * Prueba de la validacion de comprobantes repetidos.
 *
 * Extrae la consulta REAL de AutomaticasModel::getComprobanteUsado() y la
 * ejecuta con sentencias preparadas contra una base temporal con datos de
 * juguete, para comprobar que:
 *   - encuentra el numero repetido en los tres sitios (factura, orden, abono)
 *   - un numero libre no da falso positivo
 *   - un texto con comilla no rompe nada (por eso va parametrizada)
 *
 * Uso (en el servidor, con una base temporal ya creada):
 *   php tests/comprobante_repetido.test.php <base_temporal>
 */

$baseTemporal = $argv[1] ?? 'prueba_comp';
$rutaModelo   = __DIR__ . '/../models/AutomaticasModel.php';

// --- la consulta que se prueba sale del propio modelo, no de una copia ------
$fuente = file_get_contents($rutaModelo);
if (!preg_match('/function getComprobanteUsado.*?\$sql = "(.*?)";/s', $fuente, $m)) {
    fwrite(STDERR, "No se encontro getComprobanteUsado en el modelo\n");
    exit(2);
}
$sql = $m[1];

$pdo = new PDO("mysql:host=localhost;dbname=$baseTemporal;charset=utf8mb4", 'root', '', [
    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
]);

// --- datos de juguete -------------------------------------------------------
// Base de juguete: se apagan las claves foraneas para no tener que copiar medio
// esquema (clientes, usuarios...) solo para probar una consulta de busqueda.
$pdo->exec('SET FOREIGN_KEY_CHECKS=0');
$pdo->exec("INSERT INTO datos_cabecera_electronica
    (fecha, orden_no, cliente, secuencial, metodo, id_usuario, id_cliente, codigo_pago)
    VALUES ('2026-09-30', 90001, 'CLIENTE PRUEBA', '001-001-000090001', 'TRANSFERENCIA', 1, 1, 'TRF-111')");
$pdo->exec("INSERT INTO orden_venta
    (productos, total, fecha, hora, metodo, descuento, serie, estado, id_usuario, id_cliente, codigo_pago)
    VALUES ('[]', 10.00, '2026-09-30', '10:00:00', 'DEPOSITO', 0, 'OV-500', 1, 1, 1, 'DEP-222')");
$pdo->exec("INSERT INTO creditos (monto, fecha, hora, estado) VALUES (20.00, '2026-09-30', '10:00:00', 1)");
$idCredito = $pdo->lastInsertId();
$pdo->exec("INSERT INTO abonos (abono, fecha, id_credito, codigo_pago, tipo_pago)
    VALUES (20.00, '2026-09-30', $idCredito, 'ABN-333', 'TRANSFERENCIA')");

// --- casos ------------------------------------------------------------------
$casos = [
    ['TRF-111',       'FACTURA',        'repetido en una factura'],
    ['DEP-222',       'ORDEN DE VENTA', 'repetido en una orden de venta'],
    ['ABN-333',       'ABONO',          'repetido en un abono'],
    ['NUEVO-999',     null,             'numero libre'],
    ["O'BRIEN-1",     null,             'texto con comilla (no debe romper)'],
    ['ABC123',        null,             'numero con letras (no debe romper)'],
];

$ok = 0; $mal = 0;
foreach ($casos as [$codigo, $esperado, $titulo]) {
    try {
        $st = $pdo->prepare($sql);
        $st->execute([$codigo, $codigo, $codigo]);
        $fila = $st->fetch(PDO::FETCH_ASSOC);
        $origen = $fila ? $fila['origen'] : null;

        if ($origen === $esperado) {
            printf("  OK    %-38s %s\n", $titulo, $origen ? "-> $origen {$fila['referencia']}" : '-> libre');
            $ok++;
        } else {
            printf("  FALLA %-38s esperaba %s y dio %s\n", $titulo,
                   var_export($esperado, true), var_export($origen, true));
            $mal++;
        }
    } catch (Throwable $e) {
        printf("  FALLA %-38s excepcion: %s\n", $titulo, $e->getMessage());
        $mal++;
    }
}

echo "\n$ok correctas, $mal fallidas\n";
exit($mal ? 1 : 0);

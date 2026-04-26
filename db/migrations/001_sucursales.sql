-- Migration: crear tabla sucursales
-- Cada empresa puede tener varias sucursales con su propio establecimiento,
-- punto de emisión y secuenciales independientes para Factura, Nota de Crédito
-- y Recibo (en producción y pruebas por separado).
-- Aplicar con: mysql -u <user> -p <db> < 001_sucursales.sql

CREATE TABLE IF NOT EXISTS `sucursales` (
  `id` bigint(20) NOT NULL AUTO_INCREMENT,
  `nombre` varchar(120) NOT NULL,
  `direccion` varchar(255) DEFAULT NULL,
  `establecimiento` varchar(3) NOT NULL DEFAULT '001',
  `puntoemi` varchar(3) NOT NULL DEFAULT '001',
  `sec_factura` int(11) NOT NULL DEFAULT 1,
  `sec_factura_pruebas` int(11) NOT NULL DEFAULT 1,
  `sec_notacredito` int(11) NOT NULL DEFAULT 1,
  `sec_notacredito_pruebas` int(11) NOT NULL DEFAULT 1,
  `sec_recibo` int(11) NOT NULL DEFAULT 1,
  `ambiente` enum('PRODUCCION','PRUEBAS') NOT NULL DEFAULT 'PRUEBAS',
  `estado` tinyint(1) NOT NULL DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `uniq_estab_punto` (`establecimiento`,`puntoemi`),
  KEY `idx_estado` (`estado`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_swedish_ci;

-- Si la tabla queda vacía, sembrar la sucursal principal con los datos
-- actuales de configuracion. Solo se inserta si NO existe ya una fila.
INSERT INTO `sucursales` (`nombre`, `direccion`, `establecimiento`, `puntoemi`, `ambiente`)
SELECT
    COALESCE(c.nombre, 'Sucursal Principal'),
    COALESCE(c.direccion, ''),
    LPAD(COALESCE(c.establecimiento, '1'), 3, '0'),
    LPAD(COALESCE(c.puntoemi, '1'), 3, '0'),
    'PRUEBAS'
FROM `configuracion` c
WHERE NOT EXISTS (SELECT 1 FROM `sucursales`)
LIMIT 1;

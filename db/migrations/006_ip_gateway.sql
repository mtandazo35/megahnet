-- 006_ip_gateway.sql
-- Anade la columna gateway a la tabla ip y la inicializa con el valor de red
-- para registros donde quede NULL (rangos preexistentes en deploys que no la tenian).
-- Idempotente: usa IF NOT EXISTS y solo actualiza filas con gateway vacio/NULL.

ALTER TABLE ip ADD COLUMN IF NOT EXISTS gateway varchar(50) NULL AFTER red;
UPDATE ip SET gateway = red WHERE gateway IS NULL OR gateway = '';

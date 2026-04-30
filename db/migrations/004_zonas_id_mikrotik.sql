-- 004_zonas_id_mikrotik.sql
-- Anade la columna id_mikrotik a zonas (FK suave a la tabla mikrotik).
-- Idempotente: usa IF NOT EXISTS para no fallar al re-ejecutar.

ALTER TABLE zonas ADD COLUMN IF NOT EXISTS id_mikrotik INT NULL;

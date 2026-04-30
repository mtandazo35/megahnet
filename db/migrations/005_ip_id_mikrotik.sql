-- 005_ip_id_mikrotik.sql
-- Anade la columna id_mikrotik a ip (FK suave a la tabla mikrotik).
-- Idempotente: usa IF NOT EXISTS para no fallar al re-ejecutar.

ALTER TABLE ip ADD COLUMN IF NOT EXISTS id_mikrotik INT NULL;

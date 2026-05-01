-- 007_configuracion_localizacion.sql
-- Anade provincia, canton y parroquia a configuracion para que dejen
-- de estar hardcoded en el generador de contratos.
-- Idempotente. Defaults = valores que estaban hardcoded.

ALTER TABLE configuracion ADD COLUMN IF NOT EXISTS provincia varchar(60) NULL;
ALTER TABLE configuracion ADD COLUMN IF NOT EXISTS canton    varchar(60) NULL;
ALTER TABLE configuracion ADD COLUMN IF NOT EXISTS parroquia varchar(60) NULL;

UPDATE configuracion SET provincia = 'LOS RIOS'   WHERE provincia IS NULL OR provincia = '';
UPDATE configuracion SET canton    = 'QUEVEDO'    WHERE canton    IS NULL OR canton    = '';
UPDATE configuracion SET parroquia = '7 DE OCTUBRE' WHERE parroquia IS NULL OR parroquia = '';

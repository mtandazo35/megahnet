-- 003_mikrotik_estado_conexion.sql
-- Anade columnas para que MikrotiksModel::actualizarEstadoConexion() funcione.
-- estado_conexion: 'online' | 'offline' | 'unknown' (fallback del controller).
-- ultima_verificacion: timestamp de la ultima vez que se intento conectar.
-- ultimo_error: mensaje de error si fallo la conexion.
-- Idempotente: usa IF NOT EXISTS.

ALTER TABLE mikrotik ADD COLUMN IF NOT EXISTS estado_conexion VARCHAR(20) NULL DEFAULT 'unknown';
ALTER TABLE mikrotik ADD COLUMN IF NOT EXISTS ultima_verificacion DATETIME NULL;
ALTER TABLE mikrotik ADD COLUMN IF NOT EXISTS ultimo_error TEXT NULL;

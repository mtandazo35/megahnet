-- Seed inicial: grupo de trabajo + usuario administrador por defecto.
--
-- Solo se ejecuta cuando la tabla `usuarios` esta vacia (lo controla install.sh).
--
-- Credenciales por defecto:
--   admin@admin.com / 12345678
--
-- IMPORTANTE: cambiar la clave inmediatamente al primer login.
-- El hash bcrypt se genera en install.sh para evitar dejarlo aqui hardcodeado.

INSERT INTO grupo_trabajo (id, id_responsable, descripcion, estado)
VALUES (1, 1, 'GENERAL', 1)
ON DUPLICATE KEY UPDATE descripcion = VALUES(descripcion);

-- El placeholder __HASH__ lo reemplaza install.sh con un bcrypt fresco.
INSERT INTO usuarios (nombre, apellido, correo, perfil, clave, rol, estado, id_grupo_trabajo)
VALUES ('Admin', 'Sistema', 'admin@admin.com', 'ADMINISTRADOR', '__HASH__', 1, 1, 1);

-- Seed inicial: datos ficticios de empresa para que un install fresco arranque
-- sin tener que rellenar nada. El usuario despues edita desde Sistema > Configuracion.
--
-- Solo se inserta si la tabla configuracion esta vacia (controlado por install.sh).

INSERT INTO configuracion (
    id, ruc, nombre, razon_social, telefono, correo, direccion,
    impuesto, mensaje,
    totalitems, establecimiento, puntoemi, contabilidad,
    facturaelectronica, img,
    firmainicio, firmafinal, firma_password,
    cantidaddocumento
) VALUES (
    1, '0000000000001', 'EMPRESA DEMO', 'EMPRESA DEMO S.A.', '0000000000', 'demo@empresa.com', 'Direccion de la empresa',
    15, 'Gracias por su preferencia',
    0, '001', '001', 'NO',
    0, NULL,
    NULL, NULL, NULL,
    50
);

-- Sucursal principal (estab+punto = 001-001) si esta vacia.
-- La migracion 001_sucursales ya hace su propio INSERT WHERE NOT EXISTS leyendo de configuracion,
-- asi que aqui no es necesario duplicarlo.

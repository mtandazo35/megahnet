-- 009 — Numero de comprobante al facturar un contrato
--
-- Al facturar desde Contratos el pago queda registrado solo como metodo
-- (TRANSFERENCIA, DEPOSITO...), sin el numero del documento. El unico sitio
-- donde ya se guardaba ese dato era abonos.codigo_pago, del modal de abonos.
-- Se anade la misma idea a los dos comprobantes que puede generar el facturado:
-- la factura electronica y la orden de venta.
--
-- El campo es OPCIONAL: no bloquea facturar si viene vacio. El indice es para
-- poder avisar rapido cuando un numero ya se uso antes.
--
-- Reversible:
--   ALTER TABLE datos_cabecera_electronica DROP COLUMN codigo_pago;
--   ALTER TABLE orden_venta               DROP COLUMN codigo_pago;

ALTER TABLE `datos_cabecera_electronica`
    ADD COLUMN IF NOT EXISTS `codigo_pago` VARCHAR(60) DEFAULT NULL AFTER `tipopago`;

ALTER TABLE `datos_cabecera_electronica`
    ADD INDEX IF NOT EXISTS `idx_codigo_pago` (`codigo_pago`);

ALTER TABLE `orden_venta`
    ADD COLUMN IF NOT EXISTS `codigo_pago` VARCHAR(60) DEFAULT NULL AFTER `tipopago`;

ALTER TABLE `orden_venta`
    ADD INDEX IF NOT EXISTS `idx_codigo_pago` (`codigo_pago`);

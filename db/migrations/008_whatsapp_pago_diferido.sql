-- ---------------------------------------------------------------------------
-- WhatsApp de "GRACIAS POR SU PAGO" diferido.
--
-- Problema: ese mensaje solo se enviaba si el SRI autorizaba la factura en el
-- mismo instante del pago. Cuando el SRI responde "EN PROCESAMIENTO" (habitual),
-- el codigo saltaba el envio, y el cron que autoriza la factura despues nunca
-- lo reenviaba: el cliente pagaba y no recibia confirmacion.
--
-- El valor por defecto es 1 ("ya resuelto, no enviar nada") a proposito:
--   * las facturas existentes quedan en 1 -> NUNCA se envia nada retroactivo;
--   * la facturacion automatica mensual tambien queda en 1 -> no se agradece
--     un pago que no ocurrio (factura a todos los clientes, hayan pagado o no);
--   * solo el cobro manual desde "Facturar Contratos" pone 0 para pedir el envio.
-- ---------------------------------------------------------------------------

ALTER TABLE datos_cabecera_electronica
    ADD COLUMN IF NOT EXISTS whatsapp_enviado tinyint(4) NOT NULL DEFAULT 1;

-- Red de seguridad: cualquier fila preexistente queda marcada como resuelta.
UPDATE datos_cabecera_electronica SET whatsapp_enviado = 1 WHERE whatsapp_enviado <> 1;

-- El cron busca por estas columnas; el indice lo mantiene barato.
ALTER TABLE datos_cabecera_electronica
    ADD INDEX IF NOT EXISTS idx_wa_pendiente (whatsapp_enviado, estado_proceso, sri_enviado);

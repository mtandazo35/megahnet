# megahnet — como se trabaja en este repositorio

Sistema de facturacion para proveedores de internet. PHP 8.4 con un MVC propio (sin
framework), MariaDB 11.8, Apache con mod_php. **Siete inquilinos**, cada uno en su
maquina `172.22.22.11` … `.17`, con su propia base de datos y su `.env`.

## Reglas permanentes

- **En los servidores no se cambia nada por iniciativa propia.** Se leen (consultas
  SELECT, logs, `php -l` por entrada estandar). Todo cambio nace en el repositorio.
- **Antes de subir cambios a una maquina, respaldo.** Base de datos y codigo a
  `/root/backups/`, verificados, y solo entonces se despliega.
- **Siempre hay punto de retorno.** Una etiqueta `rollback-pre-<trabajo>` publicada
  antes de empezar, y la reversion escrita (para migraciones, el `DROP` dentro del
  propio archivo).
- **Analisis primero.** Se audita, se propone, se espera aprobacion. No se aplica un
  cambio porque "parece obvio".
- **Finales de linea LF.** `.gitattributes` lo normaliza; varios archivos estan en CRLF
  en disco, asi que al parchear hay que leerlos y escribirlos con cuidado.
- **Commits sin co-autor.** Nada de lineas generadas automaticamente.

## Trabajo con orquestador y especialistas

El agente principal **orquesta**: reparte, revisa y solo el aplica. Los especialistas
(`.claude/agents/`) **analizan y proponen**; ninguno edita archivos ni escribe en los
servidores.

| Especialista | Area |
|---|---|
| `analista-backend` | controladores, modelos y helpers PHP |
| `analista-frontend` | vistas, JavaScript, modales, DataTables |
| `analista-datos` | esquema, consultas, migraciones y su reversion |
| `analista-sri` | facturacion electronica: XML, firma, RIDE, SRI |
| `revisor-cambios` | revision adversarial antes de aplicar y antes de desplegar |

### El orden, siempre el mismo

1. **Encargo.** El orquestador delimita el problema y reparte: a cada especialista, su
   area y el mismo sintoma concreto. Si el trabajo toca una sola area, va un solo
   especialista; no se reparte por repartir.
2. **Diagnostico.** Los especialistas devuelven causa raiz, alcance, propuesta con
   anclas exactas, riesgo, como probarlo y rollback. **Nadie ha tocado nada todavia.**
3. **Revision.** El orquestador junta las propuestas, busca choques (dos que tocan el
   mismo archivo, firmas que se pisan) y se las pasa a `revisor-cambios`, que intenta
   tumbarlas. Si el veredicto no es "listo para aplicar", se vuelve al paso 2.
4. **Aplicacion.** **Solo el orquestador escribe.** Un unico pase, con todo lo aprobado;
   nada de arreglar-probar-arreglar encadenado.
5. **Prueba.** Pruebas de comportamiento en `tests/`, que deben **fallar con el codigo
   anterior**; `php -l` por entrada estandar contra un servidor; las migraciones en una
   base temporal que se elimina al terminar.
6. **Revision final.** `revisor-cambios` mira el cambio ya escrito, no la promesa.
7. **Despliegue.** Etiqueta de retorno, respaldo, paquete de git, `merge --ff-only`,
   migracion, y verificacion real: web responde, sintaxis correcta, el archivo servido
   contiene el cambio, y la consulta o el flujo tocado funciona de verdad.

### Lo que un especialista no hace nunca

- Editar archivos, commitear o desplegar.
- Escribir en un servidor: nada de UPDATE, ALTER, reinicios ni `install.sh`.
- Salirse de su encargo. Si ve algo grave fuera de su area, **lo reporta**; no lo arregla.

## Cosas de este sistema que cuestan caro aprender

- **Hay bastante codigo muerto.** Bloques comentados y copias antiguas (las tres
  `FacturaSRI.php`, el `registrarCredito` comentado en el facturado de contratos).
  Comprueba siempre que lo que lees es lo que se ejecuta.
- **Los errores fatales y los de JavaScript** no estan en el log de Apache: van a
  `storage/alertas.jsonl`.
- **El instalador tocaba las carpetas del contenedor de WhatsApp** y lo dejaba en bucle
  de reinicios; ya esta corregido, pero `SKIP_WA=1` **no** protege de eso.
- **Un error visible no es la causa del sintoma.** Confirma que pantalla y que accion
  fallan antes de tocar; ya paso revertir un despliegue entero por confundirlos.
- **Cuidado con lo que se dispara solo**: crons de correo y de WhatsApp, reintentos al
  SRI. Un valor por defecto mal elegido puede escribir a todos los clientes del
  historial de golpe.

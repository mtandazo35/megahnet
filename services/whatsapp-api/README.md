# WhatsApp API (NestJS + Baileys)

Servicio independiente que expone una API REST para enviar mensajes y archivos
por WhatsApp usando [Baileys](https://github.com/WhiskeySockets/Baileys).
Pensado para que el sistema MegahNet (PHP) lo consuma vía HTTP.

## Stack

- NestJS 10
- Baileys (cliente WhatsApp Web no oficial)
- PostgreSQL 16
- Redis 7 (cola Bull para limpieza/envíos diferidos)
- Empaquetado en Docker compose, reverse proxy por Apache

## Estructura

```
services/whatsapp-api/
├── src/                  Código NestJS
├── Dockerfile            Multi-stage build
├── docker-compose.yml    api + postgres + redis (red interna)
├── apache-vhost.conf     Vhost para wa-api.sincosto.xyz -> 127.0.0.1:3005
├── deploy.sh             Instalador idempotente (un solo comando)
├── .env.example          Plantilla de variables
└── README.md
```

## Deploy en el VPS (un solo comando)

```bash
cd /var/www/html/megahnet/services/whatsapp-api
sudo bash deploy.sh
```

Lo que hace:

1. Instala Docker + compose plugin si faltan.
2. Habilita `mod_proxy` / `mod_proxy_http` en Apache.
3. Crea / actualiza el vhost `wa-api.sincosto.xyz`.
4. Genera `.env` con `POSTGRES_PASSWORD` y `JWT_SECRET` aleatorios si no existe.
5. Levanta `postgres`, `redis` y `api` con `docker compose up -d --build`.
6. Espera a que la API responda en `127.0.0.1:3005`.

URL pública final: **https://wa-api.sincosto.xyz/api**

## Endpoints principales

| Método | Ruta                                | Descripción                       |
|--------|-------------------------------------|-----------------------------------|
| POST   | `/api/user`                         | Crear usuario                     |
| POST   | `/api/user/login`                   | Login (devuelve JWT)              |
| POST   | `/api/session`                      | Crear sesión de WhatsApp          |
| GET    | `/api/whatsapp/qr/:sessionId`       | Obtener QR para vincular          |
| POST   | `/api/whatsapp/send`                | Enviar mensaje de texto           |
| POST   | `/api/whatsapp/send-media/url`      | Enviar archivo desde URL          |
| POST   | `/api/whatsapp/send-media/file`     | Enviar archivos (multipart)       |
| GET    | `/api/whatsapp/status-sessions`     | Estado de todas las sesiones      |
| DELETE | `/api/whatsapp/session/:sessionId`  | Cerrar sesión / logout            |

## Operación

```bash
# Ver logs en vivo
docker compose -p whatsapp-api logs -f api

# Reiniciar solo la API
docker compose -p whatsapp-api restart api

# Bajar todo
docker compose -p whatsapp-api down

# Re-deploy tras git pull
sudo bash deploy.sh
```

## Variables de entorno

Ver `.env.example`. Las críticas:

- `POSTGRES_PASSWORD` — la genera `deploy.sh` la primera vez.
- `JWT_SECRET` — idem.
- `DB_SYNC=true` — TypeORM crea las tablas al arrancar. Apagar después de
  primer boot estable si se quiere control manual.

## Persistencia

Volúmenes Docker persistentes:
- `pgdata` — base de datos Postgres.
- `redisdata` — cola Bull.

Bind mounts en el host (sobreviven a `docker compose down -v`):
- `./sesiones/` — credenciales de cada sesión de WhatsApp (no perder, sino toca re-escanear QR).
- `./static/` — archivos temporales de envío.
- `./backups/` — respaldos de BD generados por el cron interno.

## Desarrollo local

```bash
npm install
cp .env.example .env
# editar .env apuntando a postgres/redis locales
npm run start:dev
```

## Notas

- La API NO valida JWT en los controllers (no hay `@UseGuards`). El token solo
  identifica al usuario en el login. Si se expone públicamente sin firewall,
  agregar guard global o restringir por IP en el vhost de Apache.
- Cloudflare termina HTTPS; el server escucha HTTP plano en `:80`.

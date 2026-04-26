<?php
// Mini cargador de .env (sin dependencias externas). Lee config/../.env y
// expone las variables via getenv() para que las constantes definan abajo.
$__envFile = dirname(__DIR__) . '/.env';
if (file_exists($__envFile)) {
    foreach (file($__envFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES) as $__line) {
        if (!isset($__line[0]) || $__line[0] === '#' || strpos($__line, '=') === false) continue;
        list($__k, $__v) = explode('=', $__line, 2);
        $__k = trim($__k); $__v = trim($__v);
        if ($__k === '') continue;
        if (strlen($__v) >= 2 && ($__v[0] === '"' || $__v[0] === "'") && $__v[strlen($__v) - 1] === $__v[0]) {
            $__v = substr($__v, 1, -1);
        }
        if (getenv($__k) === false) putenv("$__k=$__v");
    }
}
unset($__envFile, $__line, $__k, $__v);

/**
 * Configuracion principal del sistema.
 *
 * Para entornos productivos / multi-empresa: definir las constantes via env-var
 * (`getenv('DB_PASSWORD')`) o cargar un archivo `config/local.php` (gitignored).
 * Aqui se dejan placeholders seguros para que el repo no exponga secretos.
 */

define("ROOT_PATH", dirname(__DIR__));

// URL publica del sistema.
// Por defecto se autodetecta del request actual (scheme + host + subdir),
// soportando Cloudflare y proxies inversos. El .env solo se usa como
// override explicito (util para cron/CLI donde no hay request HTTP).
$__envBase = trim((string)getenv('BASE_URL'));
$__envBaseIsPlaceholder = ($__envBase === '' || $__envBase === 'http://localhost/megahnet/');

if (!$__envBaseIsPlaceholder) {
    define('BASE_URL', rtrim($__envBase, '/') . '/');
} elseif (!empty($_SERVER['HTTP_HOST'])) {
    // Scheme: HTTPS directo, X-Forwarded-Proto (proxies), CF-Visitor (Cloudflare), puerto 443
    $__https = false;
    if (!empty($_SERVER['HTTPS']) && strtolower((string)$_SERVER['HTTPS']) !== 'off') {
        $__https = true;
    } elseif (!empty($_SERVER['HTTP_X_FORWARDED_PROTO']) && strtolower((string)$_SERVER['HTTP_X_FORWARDED_PROTO']) === 'https') {
        $__https = true;
    } elseif (!empty($_SERVER['HTTP_CF_VISITOR']) && strpos((string)$_SERVER['HTTP_CF_VISITOR'], '"scheme":"https"') !== false) {
        $__https = true;
    } elseif (!empty($_SERVER['SERVER_PORT']) && (int)$_SERVER['SERVER_PORT'] === 443) {
        $__https = true;
    }
    // Host: validar formato para evitar host-header injection
    $__host = (string)$_SERVER['HTTP_HOST'];
    if (!preg_match('/^[A-Za-z0-9\._\-\[\]:]+$/', $__host)) {
        $__host = 'localhost';
    }
    // Subdir: si la app vive bajo /sub/, BASE_URL debe incluirlo
    $__path = '/';
    if (!empty($_SERVER['SCRIPT_NAME'])) {
        $__dir = str_replace('\\', '/', dirname((string)$_SERVER['SCRIPT_NAME']));
        $__path = ($__dir === '/' || $__dir === '.' || $__dir === '') ? '/' : $__dir . '/';
    }
    define('BASE_URL', ($__https ? 'https' : 'http') . '://' . $__host . $__path);
} else {
    // CLI/cron sin request: usar .env si existe, fallback inocuo
    define('BASE_URL', $__envBase !== '' ? rtrim($__envBase, '/') . '/' : 'http://localhost/');
}
unset($__envBase, $__envBaseIsPlaceholder);

// Base de datos
define('HOSTT',    getenv('DB_HOST') ?: 'localhost');
define('USER',     getenv('DB_USER') ?: 'root');
define('PASSWORD', getenv('DB_PASSWORD') ?: '');
define('DBNAME',   getenv('DB_NAME') ?: 'sistema');
define('CHARSET',  'charset=utf8');

// Identidad de la empresa
define('TITLE', getenv('APP_TITLE') ?: 'MEGAHNET');
define('RUTARESPALDOBD', getenv('RUTARESPALDOBD') ?: '/var/backups/megahnet');
define('CANTIDADBD', 7);

define('CONCAT', '1.');

define('ENVIROMENT', (int)(getenv('ENVIROMENT') ?: 0)); // 0 LOCAL Y 1 HOST

// AMBIENTE SRI: 1=PRUEBAS, 2=PRODUCCION
$__ambFile = __DIR__ . '/.sri-ambiente';
$__ambVal = (file_exists($__ambFile)) ? (int)trim(@file_get_contents($__ambFile)) : 1;
if (!in_array($__ambVal, [1, 2], true)) $__ambVal = 1;
define('AMBIENTE', $__ambVal);

define('CIUDAD',    getenv('CIUDAD')    ?: 'QUEVEDO');
define('PROVINCIA', getenv('PROVINCIA') ?: 'LOS RIOS');
define('CANTON',    getenv('CANTON')    ?: 'QUEVEDO');
define('PARROQUIA', getenv('PARROQUIA') ?: 'GUAYACAN');

define('CONTRATOSPORSUSPENDER', 2);

// TIPOS DE COMPROBANTE SRI
define('FACTURA', 1);
define('NOTACREDITO', 4);
define('NOTADEBITO', 5);
define('GUIAREMISION', 6);
define('RETENCION', 7);
define('PTOEMISIONRETENCION', '002');
define('MONTO_MINIMO_FACTURA', 1.00);

define('MONEDA', '$ ');
define('MESES', array(1 => 'ENERO', 'FEBRERO', 'MARZO', 'ABRIL', 'MAYO', 'JUNIO',
'JULIO', 'AGOSTO', 'SEPTIEMBRE', 'OCTUBRE', 'NOVIEMBRE', 'DICIEMBRE'));

define('CONCEPTOMES', ' ');

// Email de alertas administrativas (errores facturacion, clientes sin correo, etc)
define("CORREO_ALERTAS", getenv('CORREO_ALERTAS') ?: '');

// SMTP
define('USER_SMTP',   getenv('USER_SMTP')   ?: 'tu-correo@gmail.com');
define('CLAVE_SMTP',  getenv('CLAVE_SMTP')  ?: 'CHANGEME');
define('HOST_SMTP',   getenv('HOST_SMTP')   ?: 'smtp.gmail.com');
define('PUERTO_SMTP', (int)(getenv('PUERTO_SMTP') ?: 465));
define('SECURE_SMTP', (int)(getenv('SECURE_SMTP') ?: 1));

// IMPRESION DIRECTA
define('NOMBRE_IMPRESORA', getenv('NOMBRE_IMPRESORA') ?: 'POS-58-Series');

// SOPORTE TECNICO
define('CONTACTO', getenv('CONTACTO') ?: '0000000000');

// CIFRADO interno
define('KEY', getenv('CRYPT_KEY') ?: 'CHANGEME-cambiar-por-llave-aleatoria');
define('METODOASIC', 'AES-256-CBC');
define('IV', openssl_random_pseudo_bytes(openssl_cipher_iv_length('AES-256-CBC')));

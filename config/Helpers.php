<?php
function strClean($cadena)
{
    $string = preg_replace(['/\s+/','/^\s|\s$/'],[' ',''], $cadena);
    $string = trim($string);
    $string = stripslashes($string);
    $string = str_ireplace('<script>', '', $string);
    $string = str_ireplace('</script>', '', $string);
    $string = str_ireplace('<script type=>', '', $string);
    $string = str_ireplace('<script src>', '', $string);
    $string = str_ireplace('SELECT * FROM', '', $string);
    $string = str_ireplace('DELETE FROM', '', $string);
    $string = str_ireplace('INSERT INTO', '', $string);
    $string = str_ireplace('SELECT COUNT(*) FROM', '', $string);
    $string = str_ireplace('DROP TABLE', '', $string);
    $string = str_ireplace("OR '1'='1", '', $string);
    $string = str_ireplace('OR ´1´=´1', '', $string);
    $string = str_ireplace('IS NULL', '', $string);
    $string = str_ireplace('LIKE "', '', $string);
    $string = str_ireplace("LIKE '", '', $string);
    $string = str_ireplace('LIKE ´', '', $string);
    $string = str_ireplace('OR "a"="a', '', $string);
    $string = str_ireplace("OR 'a'='a", '', $string);
    $string = str_ireplace('OR ´a´=´a', '', $string);
    $string = str_ireplace('--', '', $string);
    $string = str_ireplace('^', '', $string);
    $string = str_ireplace('[', '', $string);
    $string = str_ireplace(']', '', $string);
    $string = str_ireplace('==', '', $string);
    return $string;
}
function tokenPayPhone(){
    $r1 = bin2hex(random_bytes(2));
    $r2 = bin2hex(random_bytes(2));
    $r3 = bin2hex(random_bytes(2));
    $r4 = bin2hex(random_bytes(2));
    $tokenPayPhone = $r1.''.$r2.''.$r3.''.$r4;
    return $tokenPayPhone;
}
// Respaldo de BD que corre en Linux + Windows
// Detecta mysqldump, comprime con gzip, envia al browser para descarga
// Mantiene copia en /var/backups/<DBNAME> con rotacion

function respaldoBD_descargar() {
    // Localizar mysqldump
    $candidatos = [
        "/usr/bin/mysqldump",
        "/usr/local/bin/mysqldump",
        "C:/wamp64/bin/mysql/mysql9.1.0/bin/mysqldump.exe",
        "C:/xampp/mysql/bin/mysqldump.exe",
    ];
    $mysqldump = null;
    foreach ($candidatos as $c) {
        if (file_exists($c)) { $mysqldump = $c; break; }
    }
    if (!$mysqldump) {
        $which = trim((string)@shell_exec("which mysqldump 2>/dev/null"));
        if ($which && file_exists($which)) $mysqldump = $which;
    }
    if (!$mysqldump) die("ERROR: mysqldump no encontrado en el sistema.");

    // Directorio de respaldos persistentes
    $isWindows = stripos(PHP_OS, "WIN") === 0;
    $rutaDestino = $isWindows ? (defined("RUTARESPALDOBD") ? RUTARESPALDOBD : "C:\respaldo") : "/var/backups/" . DBNAME;
    if (!is_dir($rutaDestino)) @mkdir($rutaDestino, 0755, true);

    $fecha = date("Y-m-d_H-i-s");
    $nombreBase = DBNAME . "_" . $fecha . ".sql.gz";
    $rutaArchivo = rtrim($rutaDestino, "/\\") . DIRECTORY_SEPARATOR . $nombreBase;

    // Comando: mysqldump ... | gzip > archivo
    $cmd = sprintf(
        "%s --single-transaction --quick --routines --triggers --events --default-character-set=utf8mb4 -h %s -u %s %s %s | gzip -9 > %s",
        escapeshellarg($mysqldump),
        escapeshellarg(HOSTT),
        escapeshellarg(USER),
        PASSWORD ? "-p" . escapeshellarg(PASSWORD) : "",
        escapeshellarg(DBNAME),
        escapeshellarg($rutaArchivo)
    );
    if ($isWindows) {
        // Windows no tiene gzip por defecto; hacer .sql sin comprimir
        $rutaArchivo = preg_replace("/\.sql\.gz$/", ".sql", $rutaArchivo);
        $nombreBase = basename($rutaArchivo);
        $cmd = sprintf(
            "\"%s\" --single-transaction --quick --routines --triggers --events --default-character-set=utf8mb4 -h %s -u %s %s %s > \"%s\"",
            $mysqldump, HOSTT, USER, PASSWORD ? "-p" . PASSWORD : "", DBNAME, $rutaArchivo
        );
    }

    $ret = 0; $out = [];
    exec($cmd . " 2>&1", $out, $ret);
    if ($ret !== 0 || !file_exists($rutaArchivo) || filesize($rutaArchivo) < 100) {
        die("ERROR en mysqldump (codigo $ret): " . implode("\n", $out));
    }

    // Rotacion: conservar los ultimos 10
    $max = defined("CANTIDADBD") ? CANTIDADBD : 10;
    $archivos = glob(rtrim($rutaDestino, "/\\") . DIRECTORY_SEPARATOR . DBNAME . "_*");
    if (count($archivos) > $max) {
        usort($archivos, fn($a,$b) => filemtime($a) - filemtime($b));
        $sobran = count($archivos) - $max;
        for ($i = 0; $i < $sobran; $i++) @unlink($archivos[$i]);
    }

    // Enviar al navegador
    while (ob_get_level() > 0) ob_end_clean();
    header("Content-Type: " . ($isWindows ? "application/sql" : "application/gzip"));
    header("Content-Disposition: attachment; filename=\"$nombreBase\"");
    header("Content-Length: " . filesize($rutaArchivo));
    header("Cache-Control: no-cache, no-store, must-revalidate");
    header("Pragma: no-cache");
    readfile($rutaArchivo);
    exit;
}

// Listado de respaldos existentes (para UI)
function respaldoBD_listar() {
    $isWindows = stripos(PHP_OS, "WIN") === 0;
    $rutaDestino = $isWindows ? (defined("RUTARESPALDOBD") ? RUTARESPALDOBD : "C:\respaldo") : "/var/backups/" . DBNAME;
    if (!is_dir($rutaDestino)) return [];
    $archivos = glob(rtrim($rutaDestino, "/\\") . DIRECTORY_SEPARATOR . DBNAME . "_*");
    usort($archivos, fn($a,$b) => filemtime($b) - filemtime($a));
    $out = [];
    foreach ($archivos as $a) {
        $out[] = [
            "nombre" => basename($a),
            "tamanio" => filesize($a),
            "fecha" => date("Y-m-d H:i:s", filemtime($a)),
        ];
    }
    return $out;
}
// Generar respaldo SIN descargar (retorna info para UI)
function respaldoBD_generar() {
    $candidatos = ["/usr/bin/mysqldump","/usr/local/bin/mysqldump","C:/wamp64/bin/mysql/mysql9.1.0/bin/mysqldump.exe","C:/xampp/mysql/bin/mysqldump.exe"];
    $mysqldump = null;
    foreach ($candidatos as $c) if (file_exists($c)) { $mysqldump = $c; break; }
    if (!$mysqldump) { $w = trim((string)@shell_exec("which mysqldump 2>/dev/null")); if ($w && file_exists($w)) $mysqldump = $w; }
    if (!$mysqldump) throw new Exception("mysqldump no encontrado");

    $isWindows = stripos(PHP_OS, "WIN") === 0;
    $ruta = $isWindows ? (defined("RUTARESPALDOBD") ? RUTARESPALDOBD : "C:\respaldo") : "/var/backups/" . DBNAME;
    if (!is_dir($ruta)) @mkdir($ruta, 0755, true);
    if (!is_writable($ruta)) throw new Exception("Sin permiso de escritura: $ruta");

    $fecha = date("Y-m-d_H-i-s");
    $ext = $isWindows ? ".sql" : ".sql.gz";
    $nombre = DBNAME . "_" . $fecha . $ext;
    $archivo = rtrim($ruta, "/\\") . DIRECTORY_SEPARATOR . $nombre;

    $passArg = PASSWORD ? "-p" . escapeshellarg(PASSWORD) : "";
    if ($isWindows) {
        $cmd = sprintf(
            "\"%s\" --single-transaction --quick --routines --triggers --events --default-character-set=utf8mb4 -h %s -u %s %s %s > \"%s\"",
            $mysqldump, HOSTT, USER, PASSWORD ? "-p" . PASSWORD : "", DBNAME, $archivo
        );
    } else {
        $cmd = sprintf(
            "%s --single-transaction --quick --routines --triggers --events --default-character-set=utf8mb4 -h %s -u %s %s %s | gzip -9 > %s",
            escapeshellarg($mysqldump), escapeshellarg(HOSTT), escapeshellarg(USER), $passArg, escapeshellarg(DBNAME), escapeshellarg($archivo)
        );
    }
    $ret = 0; $out = [];
    exec($cmd . " 2>&1", $out, $ret);
    if ($ret !== 0 || !file_exists($archivo) || filesize($archivo) < 100) {
        @unlink($archivo);
        throw new Exception("mysqldump fallo (codigo $ret): " . implode("\n", $out));
    }

    // Rotacion
    $max = defined("CANTIDADBD") ? CANTIDADBD : 10;
    $arcs = glob(rtrim($ruta, "/\\") . DIRECTORY_SEPARATOR . DBNAME . "_*");
    if (count($arcs) > $max) {
        usort($arcs, fn($a,$b) => filemtime($a) - filemtime($b));
        for ($i=0; $i < count($arcs)-$max; $i++) @unlink($arcs[$i]);
    }
    $sz = filesize($archivo);
    return ["nombre"=>$nombre, "tamanio"=>$sz < 1024*1024 ? round($sz/1024,1)." KB" : round($sz/1024/1024,2)." MB"];
}

// Descargar un archivo existente por nombre
function respaldoBD_descargar_archivo($nombre) {
    // Validar nombre para evitar path traversal
    if (!preg_match("/^" . preg_quote(DBNAME,"/") . "_[0-9\-_]+\.sql(\.gz)?$/", $nombre)) { http_response_code(400); die("Nombre invalido"); }
    $isWindows = stripos(PHP_OS, "WIN") === 0;
    $ruta = $isWindows ? (defined("RUTARESPALDOBD") ? RUTARESPALDOBD : "C:\respaldo") : "/var/backups/" . DBNAME;
    $archivo = rtrim($ruta, "/\\") . DIRECTORY_SEPARATOR . $nombre;
    if (!file_exists($archivo)) { http_response_code(404); die("No existe"); }
    while (ob_get_level() > 0) ob_end_clean();
    header("Content-Type: " . (str_ends_with($nombre, ".gz") ? "application/gzip" : "application/sql"));
    header("Content-Disposition: attachment; filename=\"$nombre\"");
    header("Content-Length: " . filesize($archivo));
    header("Cache-Control: no-cache");
    readfile($archivo);
    exit;
}

// Eliminar un archivo por nombre
function respaldoBD_eliminar($nombre) {
    if (!preg_match("/^" . preg_quote(DBNAME,"/") . "_[0-9\-_]+\.sql(\.gz)?$/", $nombre)) return false;
    $isWindows = stripos(PHP_OS, "WIN") === 0;
    $ruta = $isWindows ? (defined("RUTARESPALDOBD") ? RUTARESPALDOBD : "C:\respaldo") : "/var/backups/" . DBNAME;
    $archivo = rtrim($ruta, "/\\") . DIRECTORY_SEPARATOR . $nombre;
    return file_exists($archivo) && @unlink($archivo);
}


// Restaurar desde un .sql o .sql.gz existente (con pre-dump de seguridad)
function respaldoBD_restaurar($nombre) {
    if (!preg_match("/^" . preg_quote(DBNAME,"/") . "_[A-Za-z0-9_\-\.]+\.sql(\.gz)?$/", $nombre))
        throw new Exception("Nombre invalido");
    $isWindows = stripos(PHP_OS, "WIN") === 0;
    $ruta = $isWindows ? (defined("RUTARESPALDOBD") ? RUTARESPALDOBD : "C:\\respaldo") : "/var/backups/" . DBNAME;
    $archivo = rtrim($ruta, "/\\\\") . DIRECTORY_SEPARATOR . $nombre;
    if (!file_exists($archivo)) throw new Exception("Archivo no existe");

    // Pre-dump de seguridad automatico antes de restaurar
    $pre = respaldoBD_generar();

    // Localizar mysql
    $candidatos = ["/usr/bin/mysql","/usr/local/bin/mysql","C:/wamp64/bin/mysql/mysql9.1.0/bin/mysql.exe"];
    $mysql = null;
    foreach ($candidatos as $c) if (file_exists($c)) { $mysql = $c; break; }
    if (!$mysql) { $w = trim((string)@shell_exec("which mysql 2>/dev/null")); if ($w && file_exists($w)) $mysql = $w; }
    if (!$mysql) throw new Exception("cliente mysql no encontrado");

    $passArg = PASSWORD ? "-p" . escapeshellarg(PASSWORD) : "";
    $esGz = str_ends_with(strtolower($nombre), ".gz");
    if ($isWindows) {
        if ($esGz) throw new Exception("Restore de .gz no soportado en Windows sin gunzip. Descomprima primero.");
        $cmd = sprintf("\"%s\" -h %s -u %s %s %s < \"%s\"", $mysql, HOSTT, USER, PASSWORD ? "-p".PASSWORD : "", DBNAME, $archivo);
    } else {
        if ($esGz) {
            $cmd = sprintf("zcat %s | %s -h %s -u %s %s %s",
                escapeshellarg($archivo), escapeshellarg($mysql),
                escapeshellarg(HOSTT), escapeshellarg(USER), $passArg, escapeshellarg(DBNAME));
        } else {
            $cmd = sprintf("%s -h %s -u %s %s %s < %s",
                escapeshellarg($mysql), escapeshellarg(HOSTT), escapeshellarg(USER),
                $passArg, escapeshellarg(DBNAME), escapeshellarg($archivo));
        }
    }

    $out = []; $ret = 0;
    exec($cmd . " 2>&1", $out, $ret);
    if ($ret !== 0) {
        // Intentar rollback
        try { respaldoBD_restaurar_sin_predump($pre["nombre"]); } catch (Exception $_e) {}
        throw new Exception("Restore fallo (codigo $ret): " . substr(implode(" | ", $out), 0, 500));
    }
    return ["ok"=>true, "archivo"=>$nombre, "backup_previo"=>$pre["nombre"]];
}

// Version interna sin pre-dump (para el rollback)
function respaldoBD_restaurar_sin_predump($nombre) {
    $isWindows = stripos(PHP_OS, "WIN") === 0;
    $ruta = $isWindows ? (defined("RUTARESPALDOBD") ? RUTARESPALDOBD : "C:\\respaldo") : "/var/backups/" . DBNAME;
    $archivo = rtrim($ruta, "/\\\\") . DIRECTORY_SEPARATOR . $nombre;
    if (!file_exists($archivo)) throw new Exception("Archivo no existe");
    $mysql = "/usr/bin/mysql";
    $passArg = PASSWORD ? "-p" . escapeshellarg(PASSWORD) : "";
    $esGz = str_ends_with(strtolower($nombre), ".gz");
    $cmd = $esGz
        ? sprintf("zcat %s | %s -h %s -u %s %s %s",
            escapeshellarg($archivo), escapeshellarg($mysql),
            escapeshellarg(HOSTT), escapeshellarg(USER), $passArg, escapeshellarg(DBNAME))
        : sprintf("%s -h %s -u %s %s %s < %s",
            escapeshellarg($mysql), escapeshellarg(HOSTT), escapeshellarg(USER),
            $passArg, escapeshellarg(DBNAME), escapeshellarg($archivo));
    exec($cmd . " 2>&1", $out, $ret);
    return $ret === 0;
}

/**
 * Lista de tablas "borrables" (datos transaccionales + configuracion empresa).
 * NO incluye: tipo_pago, tipo_comprobante, codigos_retencion (catalogos SRI).
 * usuarios: borrable pero la funcion preserva administradores (rol=1).
 */
function respaldoBD_tablasBorrables() {
    return [
        'abonos','acceso','apartados','cajas','casos','categorias','clientes',
        'compras','configuracion','contratos','cotizaciones','creditos','datos_cabecera_electronica',
        'detalle_apartado','detalle_factura_electronica','estado_corte','gastos',
        'grupo_trabajo','inventario','ip','ip_anuladas','medidas','mes_facturar',
        'mikrotik','nota_credito_cabecera','nota_credito_detalle','orden_venta',
        'productos','proveedor','repetidoras','respuesta_sri','retencion',
        'retencion_detalle','sucursales','ventas','zonas',
    ];
}

/**
 * Borra datos de tablas seleccionadas. SIEMPRE crea un backup previo automático
 * para rollback. Preserva el usuario actual logueado en la tabla usuarios si
 * 'usuarios' viene en la lista.
 *
 * @param array $tablas    Tablas a truncar. Filtradas contra la whitelist.
 * @param int   $userKeep  ID de usuario a preservar en tabla 'usuarios' (si aplica).
 * @return array  ['ok'=>bool, 'backup_previo'=>str, 'tablas_eliminadas'=>array, 'errores'=>array]
 */
function respaldoBD_borrarDatos($tablas, $userKeep) {
    if (!is_array($tablas) || empty($tablas)) {
        throw new Exception('Lista de tablas vacía');
    }
    $userKeep = (int)$userKeep;
    if ($userKeep <= 0) {
        throw new Exception('Usuario admin requerido para preservar');
    }

    // 1) Backup previo automático (rollback safety)
    $pre = respaldoBD_generar();

    // 2) Filtrar contra whitelist — nadie borra fuera de aquí
    $whitelist = array_flip(respaldoBD_tablasBorrables());
    $aProcesar = [];
    foreach ($tablas as $t) {
        $t = preg_replace('/[^a-z0-9_]/i', '', (string)$t);
        if ($t === '') continue;
        if (isset($whitelist[$t])) $aProcesar[] = $t;
    }
    if (empty($aProcesar)) {
        return ['ok'=>true, 'backup_previo'=>$pre['nombre'], 'tablas_eliminadas'=>[], 'errores'=>['Ninguna tabla válida']];
    }

    // 3) Truncate con FK checks deshabilitados (evita errores por foreign keys)
    $eliminadas = [];
    $errores = [];
    try {
        $pdo = new PDO(
            'mysql:host=' . HOSTT . ';dbname=' . DBNAME . ';' . CHARSET,
            USER, PASSWORD,
            [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
        );
        $pdo->exec('SET FOREIGN_KEY_CHECKS = 0');

        foreach ($aProcesar as $t) {
            try {
                if ($t === 'usuarios') {
                    // Caso especial: NO truncar — preservar TODOS los administradores
                    // (rol=1) ademas del usuario actual logueado. Asi un wipe nunca
                    // deja el sistema sin admins, aunque el operador no sea el unico.
                    $stmt = $pdo->prepare("DELETE FROM `usuarios` WHERE id != ? AND rol != 1");
                    $stmt->execute([$userKeep]);
                    $eliminadas[] = 'usuarios (excepto administradores)';
                } else {
                    $pdo->exec("TRUNCATE TABLE `$t`");
                    $eliminadas[] = $t;
                }
            } catch (\Throwable $e) {
                $errores[] = "$t: " . $e->getMessage();
            }
        }

        $pdo->exec('SET FOREIGN_KEY_CHECKS = 1');
    } catch (\Throwable $e) {
        throw new Exception('Error de conexión BD: ' . $e->getMessage());
    }

    return [
        'ok' => true,
        'backup_previo' => $pre['nombre'],
        'tablas_eliminadas' => $eliminadas,
        'errores' => $errores,
    ];
}

// Subir un respaldo desde el navegador y guardarlo en el directorio
function respaldoBD_subir($file) {
    if (!isset($file["tmp_name"]) || $file["error"] !== 0)
        throw new Exception("Error al subir el archivo (codigo " . ($file["error"] ?? "?") . ")");

    $nombreOriginal = basename($file["name"]);
    if (!preg_match("/\.sql(\.gz)?\$/i", $nombreOriginal))
        throw new Exception("Solo archivos .sql o .sql.gz");

    // Validar que el archivo mencione la DB correcta (proteccion cruzada)
    $tmp = $file["tmp_name"];
    $esGz = (bool)preg_match("/\.gz\$/i", $nombreOriginal);
    $muestra = "";
    if ($esGz) {
        $muestra = (string)@shell_exec("zcat " . escapeshellarg($tmp) . " 2>/dev/null | head -c 4000");
    } else {
        $fh = fopen($tmp, "r");
        if ($fh) { $muestra = fread($fh, 4000); fclose($fh); }
    }
    // Buscar indicios de la base correcta (USE DBNAME o CREATE DATABASE DBNAME)
    $db = DBNAME;
    $matchDb = (stripos($muestra, "USE \x60$db\x60") !== false)
            || (stripos($muestra, "USE $db") !== false)
            || (stripos($muestra, "Database: $db") !== false)
            || (stripos($muestra, "CREATE DATABASE") !== false && stripos($muestra, $db) !== false);
    // Si el dump no contiene referencia clara a la DB actual, dejamos que continue igual
    // pero marcamos para que el usuario confirme
    $requiereConfirmacion = !$matchDb;

    // Si confirmacion = skip (se puede forzar con flag)
    if ($requiereConfirmacion && empty($_POST["confirmar_cross_db"]))
        throw new Exception("El archivo no parece ser de la base " . $db . ". Si estas seguro, reintenta con confirmar_cross_db=1");

    $ext = $esGz ? ".sql.gz" : ".sql";
    $nombre = DBNAME . "_uploaded_" . date("Y-m-d_H-i-s") . $ext;
    $isWindows = stripos(PHP_OS, "WIN") === 0;
    $ruta = $isWindows ? (defined("RUTARESPALDOBD") ? RUTARESPALDOBD : "C:\\respaldo") : "/var/backups/" . DBNAME;
    if (!is_dir($ruta)) @mkdir($ruta, 0755, true);
    $dest = rtrim($ruta, "/\\\\") . DIRECTORY_SEPARATOR . $nombre;

    if (!move_uploaded_file($tmp, $dest)) throw new Exception("No se pudo guardar el archivo en el servidor");
    @chmod($dest, 0644);

    return ["nombre"=>$nombre, "tamanio"=>filesize($dest)];
}

// Enviar un respaldo por correo usando PHPMailer (ya presente en /libraries)
function respaldoBD_enviar_email($nombre, $destinatarios, $asunto = null, $mensaje = null) {
    if (!preg_match("/^" . preg_quote(DBNAME,"/") . "_[A-Za-z0-9_\-\.]+\.sql(\.gz)?$/", $nombre))
        throw new Exception("Nombre invalido");
    $isWindows = stripos(PHP_OS, "WIN") === 0;
    $ruta = $isWindows ? (defined("RUTARESPALDOBD") ? RUTARESPALDOBD : "C:\\respaldo") : "/var/backups/" . DBNAME;
    $archivo = rtrim($ruta, "/\\\\") . DIRECTORY_SEPARATOR . $nombre;
    if (!file_exists($archivo)) throw new Exception("Archivo no existe");

    if (!class_exists("PHPMailer\\PHPMailer\\PHPMailer", false)) {
        require_once __DIR__ . "/../libraries/phpmailer/Exception.php";
        require_once __DIR__ . "/../libraries/phpmailer/PHPMailer.php";
        require_once __DIR__ . "/../libraries/phpmailer/SMTP.php";
    }
    $mail = new PHPMailer\PHPMailer\PHPMailer(true);
    try {
        $mail->isSMTP();
        $mail->Host       = defined("HOST_SMTP") ? HOST_SMTP : "smtp.gmail.com";
        $mail->SMTPAuth   = true;
        $mail->Username   = defined("USER_SMTP") ? USER_SMTP : "";
        $mail->Password   = defined("CLAVE_SMTP") ? CLAVE_SMTP : (defined("PASSWORD_SMTP") ? PASSWORD_SMTP : "");
$mail->SMTPSecure = (defined("SECURE_SMTP") && SECURE_SMTP == 1) ? PHPMailer\PHPMailer\PHPMailer::ENCRYPTION_SMTPS : PHPMailer\PHPMailer\PHPMailer::ENCRYPTION_STARTTLS;
        $mail->Port       = defined("PUERTO_SMTP") ? PUERTO_SMTP : (defined("PORT_SMTP") ? PORT_SMTP : 587);
        $mail->CharSet    = "UTF-8";
        $mail->Timeout    = 30;

        $from = $mail->Username ?: "noreply@" . DBNAME . ".local";
        $mail->setFrom($from, (defined("TITLE") ? TITLE : DBNAME) . " Respaldos");
        foreach ((array)$destinatarios as $d) {
            $d = trim($d);
            if (filter_var($d, FILTER_VALIDATE_EMAIL)) $mail->addAddress($d);
        }
        if (count($mail->getAllRecipientAddresses()) === 0) throw new Exception("No hay destinatarios validos");

        $mail->Subject = $asunto ?: ("[" . (defined("TITLE") ? TITLE : DBNAME) . "] Respaldo " . date("Y-m-d H:i"));
        $szKB = round(filesize($archivo)/1024);
        $mail->Body = ($mensaje ?: "Adjunto respaldo de la base de datos " . DBNAME) .
            "\n\nArchivo: $nombre\nTamaño: {$szKB} KB\nGenerado en: " . date("Y-m-d H:i:s");
        $mail->addAttachment($archivo, $nombre);

        $mail->send();
        return ["ok"=>true, "destinos"=>count($mail->getAllRecipientAddresses())];
    } catch (Exception $e) {
        throw new Exception("Envio fallo: " . $mail->ErrorInfo ?: $e->getMessage());
    }
}

if (!function_exists('buildSearchClause')) {
function buildSearchClause($search, array $columns, array &$params, $prefix = ' WHERE ')
{
    $search = trim((string)$search);
    if ($search === '' || empty($columns)) return '';
    $like = '%' . strtolower($search) . '%';
    $clauses = [];
    foreach ($columns as $col) {
        $clauses[] = "$col LIKE ?";
        $params[] = $like;
    }
    return $prefix . '(' . implode(' OR ', $clauses) . ')';
}
}


/**
 * Envia alerta administrativa via SMTP (mismo SMTP de respaldos).
 * Usado para: cliente sin correo, factura rechazada, firma vencida, etc.
 *
 * @param string $tipo     Tipo de alerta (p.ej. "CLIENTE_SIN_CORREO")
 * @param string $asunto   Asunto del email
 * @param string $cuerpo   Cuerpo del email (HTML simple permitido)
 * @return bool            true si se envio, false en caso contrario (silencioso)
 */
if (!function_exists('enviarAlertaAdmin')) {
/**
 * Devuelve la configuracion SMTP efectiva: si /storage/alertas-config.json
 * tiene valores no vacios bajo la clave "smtp", esos ganan; si no, fallback
 * a las constantes del .env (HOST_SMTP, USER_SMTP, etc).
 */
if (!function_exists('notifSmtpSettings')) {
function notifSmtpSettings()
{
    $cfg = null;
    $f = (defined('ROOT_PATH') ? ROOT_PATH : (__DIR__ . '/..')) . '/storage/alertas-config.json';
    if (file_exists($f)) {
        $cfg = @json_decode(@file_get_contents($f), true);
    }
    $smtp = (is_array($cfg) && isset($cfg['smtp']) && is_array($cfg['smtp'])) ? $cfg['smtp'] : [];

    $pick = function($key, $envConst, $default) use ($smtp) {
        if (isset($smtp[$key]) && trim((string)$smtp[$key]) !== '') return $smtp[$key];
        return defined($envConst) ? constant($envConst) : $default;
    };
    $pickInt = function($key, $envConst, $default) use ($smtp) {
        if (isset($smtp[$key]) && (string)$smtp[$key] !== '') return (int)$smtp[$key];
        return defined($envConst) ? (int)constant($envConst) : (int)$default;
    };

    return [
        'host'     => $pick('host', 'HOST_SMTP', 'smtp.gmail.com'),
        'port'     => $pickInt('port', 'PUERTO_SMTP', 465),
        'secure'   => $pickInt('secure', 'SECURE_SMTP', 1),
        'user'     => $pick('user', 'USER_SMTP', ''),
        'password' => $pick('password', 'CLAVE_SMTP', ''),
        'from_name'=> isset($smtp['from_name']) && trim((string)$smtp['from_name']) !== ''
                       ? $smtp['from_name']
                       : (defined('TITLE') ? TITLE : 'Sistema'),
        'from_email'=> isset($smtp['from_email']) && trim((string)$smtp['from_email']) !== ''
                       ? $smtp['from_email']
                       : null,
    ];
}
}

function enviarAlertaAdmin($tipo, $asunto, $cuerpo)
{
    // === Log JSONL para historico de notificaciones ===
    $logDir = __DIR__ . '/../storage';
    if (!is_dir($logDir)) { @mkdir($logDir, 0755, true); }
    $logFile = $logDir . '/alertas.jsonl';
    $logEntry = function ($status, $extra = []) use (&$logFile, &$tipo, &$asunto, &$cuerpo) {
        $rec = array_merge([
            'ts'      => date('Y-m-d H:i:s'),
            'tipo'    => $tipo,
            'asunto'  => $asunto,
            'cuerpo'  => $cuerpo,
            'status'  => $status,
        ], $extra);
        @file_put_contents($logFile, json_encode($rec, JSON_UNESCAPED_UNICODE) . PHP_EOL, FILE_APPEND | LOCK_EX);
    };

    // === Cargar datos de empresa para personalizacion ===
    $empresa = ['nombre' => defined('TITLE') ? TITLE : (defined('DBNAME') ? DBNAME : 'Sistema'), 'ruc' => '', 'correo' => '', 'telefono' => '', 'direccion' => '', 'razon_social' => ''];
    try {
        if (defined('HOSTT') && defined('DBNAME') && defined('USER')) {
            $__pdo = new PDO('mysql:host=' . HOSTT . ';dbname=' . DBNAME . ';charset=utf8mb4', USER, defined('PASSWORD') ? PASSWORD : '', [PDO::ATTR_ERRMODE => PDO::ERRMODE_SILENT, PDO::ATTR_TIMEOUT => 3]);
            $__row = $__pdo->query('SELECT nombre, ruc, correo, telefono, direccion, razon_social FROM configuracion WHERE id = 1 LIMIT 1');
            if ($__row && ($__r = $__row->fetch(PDO::FETCH_ASSOC))) {
                foreach ($__r as $k => $v) { if (!empty($v)) $empresa[$k] = $v; }
            }
        }
    } catch (\Throwable $e) { /* fallback al default */ }

    // Reemplazar placeholders {{empresa_X}} en asunto y cuerpo
    foreach (['nombre','ruc','correo','telefono','direccion','razon_social'] as $k) {
        $asunto = str_replace('{{empresa_' . $k . '}}', $empresa[$k] ?? '', $asunto);
        $cuerpo = str_replace('{{empresa_' . $k . '}}', $empresa[$k] ?? '', $cuerpo);
    }

    // Cuerpo limpio (sin HTML) para canal WhatsApp
    $cuerpoTextoWA = trim(preg_replace('/\n{3,}/', "\n\n", strip_tags(html_entity_decode($cuerpo, ENT_QUOTES | ENT_HTML5, 'UTF-8'))));
    $headerWA = '*' . ($empresa['razon_social'] ?: $empresa['nombre']) . '*' . PHP_EOL
        . 'RUC: ' . $empresa['ruc'] . PHP_EOL . PHP_EOL;

    // Header HTML para email
    $header = '<div style="background:#f8f9fa;border-left:4px solid #0d6efd;padding:10px 14px;margin-bottom:14px;font-family:Arial,sans-serif;">'
        . '<div style="font-size:1.1em;font-weight:bold;color:#0d6efd;">' . htmlspecialchars($empresa['razon_social'] ?: $empresa['nombre']) . '</div>'
        . '<div style="font-size:0.85em;color:#6c757d;">RUC: ' . htmlspecialchars($empresa['ruc']) . ' | ' . htmlspecialchars($empresa['correo']) . '</div>'
        . '</div>';
    $cuerpo = $header . $cuerpo;
    // Cargar configuracion de notificaciones (destinatarios + tipos_activos)
    $cfgFile = __DIR__ . '/../storage/alertas-config.json';
    $cfg = file_exists($cfgFile) ? @json_decode(@file_get_contents($cfgFile), true) : null;
    $destinatarios = (is_array($cfg) && !empty($cfg['destinatarios'])) ? $cfg['destinatarios'] : [];
    if (empty($destinatarios)) {
        $fallback = defined('CORREO_ALERTAS') && !empty(CORREO_ALERTAS) ? CORREO_ALERTAS : (defined('USER_SMTP') ? USER_SMTP : '');
        if (!empty($fallback)) $destinatarios = [$fallback];
    }
    // ===== Canal WhatsApp via API REST (api-whats-app NestJS + Baileys) =====
    $waApiCfg = $cfg['wa_api'] ?? null;
    if (is_array($waApiCfg) && !empty($waApiCfg['base_url']) && !empty($waApiCfg['session_id'])) {
        // Recipients de WhatsApp = destinatarios telefonicos (lista en cfg si la creamos algun dia,
        //  por ahora reusa los emails como hint y nada mas — solo dispara si admin escribio numeros)
        $waPhones = $waApiCfg['phones_alerta'] ?? [];
        if (!is_array($waPhones)) $waPhones = [];
        if (!empty($waPhones)) {
            $cuerpoTexto = trim(preg_replace('/\n{3,}/', "\n\n", strip_tags(html_entity_decode($cuerpo, ENT_QUOTES | ENT_HTML5, 'UTF-8'))));
            $msgWa = '*' . $asunto . '*' . PHP_EOL . PHP_EOL . $cuerpoTexto;
            $sendUrl = rtrim($waApiCfg['base_url'], '/') . '/api/whatsapp/send?fastMode=true';
            foreach ($waPhones as $tel) {
                $tel = preg_replace('/[^0-9]/', '', (string)$tel);
                if (empty($tel)) continue;
                if (strlen($tel) === 10 && $tel[0] === '0') $tel = '593' . substr($tel, 1);
                elseif (strlen($tel) < 11) $tel = '593' . $tel;
                $ch = @curl_init();
                if ($ch) {
                    @curl_setopt($ch, CURLOPT_URL, $sendUrl);
                    @curl_setopt($ch, CURLOPT_POST, true);
                    @curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode([
                        'sessionId' => $waApiCfg['session_id'],
                        'number'    => $tel,
                        'message'   => $msgWa,
                    ], JSON_UNESCAPED_UNICODE));
                    @curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json', 'Accept: application/json']);
                    @curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
                    @curl_setopt($ch, CURLOPT_TIMEOUT, 15);
                    @curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
                    @curl_exec($ch);
                    @curl_close($ch);
                }
            }
        }
    }
    if (empty($destinatarios)) { $logEntry('NO_DESTINO'); return false; }
    // Verificar que el tipo este habilitado en config
    if (is_array($cfg) && isset($cfg['tipos_activos'][$tipo]) && $cfg['tipos_activos'][$tipo] === false) {
        $logEntry('TIPO_DESACTIVADO', ['destinos' => $destinatarios]);
        return false;
    }
    $destino = implode(', ', $destinatarios);

    if (!class_exists('PHPMailer\\PHPMailer\\PHPMailer', false)) {
        @require_once __DIR__ . '/../libraries/phpmailer/Exception.php';
        @require_once __DIR__ . '/../libraries/phpmailer/PHPMailer.php';
        @require_once __DIR__ . '/../libraries/phpmailer/SMTP.php';
    }
    if (!class_exists('PHPMailer\\PHPMailer\\PHPMailer', false)) { $logEntry('NO_PHPMAILER'); return false; }

    try {
        $smtpCfg = notifSmtpSettings();
        $mail = new \PHPMailer\PHPMailer\PHPMailer(true);
        $mail->isSMTP();
        $mail->Host       = $smtpCfg['host'];
        $mail->SMTPAuth   = true;
        $mail->Username   = $smtpCfg['user'];
        $mail->Password   = $smtpCfg['password'];
        $mail->SMTPSecure = ((int)$smtpCfg['secure'] === 1)
            ? \PHPMailer\PHPMailer\PHPMailer::ENCRYPTION_SMTPS
            : \PHPMailer\PHPMailer\PHPMailer::ENCRYPTION_STARTTLS;
        $mail->Port       = (int)$smtpCfg['port'];
        $mail->CharSet    = 'UTF-8';
        $mail->Timeout    = 15;
        // TLS relajado para tolerar hosts con certs intermedios faltantes / FPM
        $mail->SMTPOptions = ['ssl' => ['verify_peer' => false, 'verify_peer_name' => false, 'allow_self_signed' => true]];
        $mail->SMTPKeepAlive = false;

        $from = $smtpCfg['from_email'] ?: ($mail->Username ?: ('noreply@' . (defined('DBNAME') ? DBNAME : 'sistema') . '.local'));
        $titulo = defined('TITLE') ? TITLE : (defined('DBNAME') ? DBNAME : 'Sistema');
        $mail->setFrom($from, $smtpCfg['from_name'] ?: ($titulo . ' Alertas'));
        foreach ((array)$destinatarios as $d) {
            $d = trim($d);
            if (filter_var($d, FILTER_VALIDATE_EMAIL)) $mail->addAddress($d);
        }
        $mail->isHTML(true);
        $mail->Subject = '[' . $titulo . '][ALERTA ' . $tipo . '] ' . $asunto;
        $mail->Body = '<div style="font-family:Arial,sans-serif;font-size:14px;color:#333;">'
            . '<h3 style="color:#d35400;">[' . $titulo . '] Alerta administrativa</h3>'
            . '<p><b>Tipo:</b> ' . htmlspecialchars($tipo) . '</p>'
            . '<p><b>Fecha:</b> ' . date('Y-m-d H:i:s') . '</p>'
            . '<hr><div>' . $cuerpo . '</div>'
            . '<hr><small style="color:#888;">Este es un mensaje automatico generado por el sistema. No responder.</small>'
            . '</div>';
        $mail->send();
        $logEntry('OK', ['destino' => $destino]);
        return true;
    } catch (\Throwable $e) {
        error_log('enviarAlertaAdmin fallo: ' . $e->getMessage());
        $logEntry('FAIL', ['error' => $e->getMessage()]);
        return false;
    }
}
}


/**
 * Renderiza una plantilla guardada en /storage/plantillas.json reemplazando placeholders.
 *
 * Uso:
 *   $msg = renderPlantilla('whatsapp_recordatorio', [
 *       'cliente_nombre' => 'JUAN PEREZ',
 *       'cliente_saldo'  => '25.00',
 *   ]);
 *
 * Los placeholders {{empresa_X}} se rellenan automaticamente desde la BD.
 * Devuelve ['asunto' => '...', 'cuerpo' => '...'] o null si la plantilla no existe.
 */
if (!function_exists('renderPlantilla')) {
function renderPlantilla($key, array $vars = [])
{
    $f = (defined('ROOT_PATH') ? ROOT_PATH : (__DIR__ . '/..')) . '/storage/plantillas.json';
    if (!file_exists($f)) return null;
    $arr = @json_decode(@file_get_contents($f), true);
    if (!is_array($arr) || !isset($arr[$key])) return null;
    $asunto = $arr[$key]['asunto'] ?? '';
    $cuerpo = $arr[$key]['cuerpo'] ?? '';

    // Cargar datos empresa
    $empresa = ['nombre' => defined('TITLE') ? TITLE : (defined('DBNAME') ? DBNAME : ''), 'ruc' => '', 'correo' => '', 'telefono' => '', 'direccion' => '', 'razon_social' => ''];
    try {
        if (defined('HOSTT') && defined('DBNAME') && defined('USER')) {
            $pdo = new PDO('mysql:host=' . HOSTT . ';dbname=' . DBNAME . ';charset=utf8mb4', USER, defined('PASSWORD') ? PASSWORD : '', [PDO::ATTR_ERRMODE => PDO::ERRMODE_SILENT, PDO::ATTR_TIMEOUT => 3]);
            $row = $pdo->query('SELECT nombre, ruc, correo, telefono, direccion, razon_social FROM configuracion WHERE id = 1 LIMIT 1');
            if ($row && ($r = $row->fetch(PDO::FETCH_ASSOC))) {
                foreach ($r as $k => $v) { if (!empty($v)) $empresa[$k] = $v; }
            }
        }
    } catch (\Throwable $e) { /* fallback */ }

    // empresa_*
    foreach ($empresa as $k => $v) $vars['empresa_' . $k] = $v;

    // empresa_cuentas y empresa_titular_cuenta vienen de plantillas especiales
    if (!isset($vars['empresa_cuentas']) && isset($arr['config_cuentas_pago']))
        $vars['empresa_cuentas'] = $arr['config_cuentas_pago']['cuerpo'] ?? '';
    if (!isset($vars['empresa_titular_cuenta']) && isset($arr['config_titular_cuenta']))
        $vars['empresa_titular_cuenta'] = $arr['config_titular_cuenta']['cuerpo'] ?? '';

    foreach ($vars as $k => $v) {
        $asunto = str_replace('{{' . $k . '}}', (string)$v, $asunto);
        $cuerpo = str_replace('{{' . $k . '}}', (string)$v, $cuerpo);
    }
    return ['asunto' => $asunto, 'cuerpo' => $cuerpo];
}
}

/**
 * Construye URL de WhatsApp Web para enviar mensaje a un telefono usando una plantilla.
 *
 * @param string $key       Clave de plantilla (ej. 'whatsapp_recordatorio')
 * @param string $telefono  Numero sin codigo de pais (ej. '0991234567')
 * @param array  $vars      Variables para la plantilla
 * @param string $codpais   Codigo pais default 593 (Ecuador)
 * @return string|null      URL completa o null si plantilla no existe
 */
if (!function_exists('whatsappLinkPlantilla')) {
function whatsappLinkPlantilla($key, $telefono, array $vars = [], $codpais = '593')
{
    $r = renderPlantilla($key, $vars);
    if (!$r) return null;
    $tel = preg_replace('/[^0-9]/', '', (string)$telefono);
    if (empty($tel)) return null;
    if (strlen($tel) === 10 && $tel[0] === '0') $tel = $codpais . substr($tel, 1);
    elseif (strlen($tel) === 9) $tel = $codpais . $tel;
    return 'https://wa.me/' . $tel . '?text=' . rawurlencode($r['cuerpo']);
}
}

if (!function_exists('moduloActivo')) {
function moduloActivo($key)
{
    static $cache = null;
    if ($cache === null) {
        $f = (defined('ROOT_PATH') ? ROOT_PATH : (__DIR__ . '/..')) . '/storage/modulos.json';
        $cache = [];
        if (file_exists($f)) {
            $j = @json_decode(@file_get_contents($f), true);
            if (is_array($j) && isset($j['ocultos']) && is_array($j['ocultos'])) {
                $cache = array_flip($j['ocultos']);
            }
        }
    }
    return !isset($cache[$key]);
}
}

if (!function_exists('modulosOcultos')) {
function modulosOcultos()
{
    $f = (defined('ROOT_PATH') ? ROOT_PATH : (__DIR__ . '/..')) . '/storage/modulos.json';
    if (!file_exists($f)) return [];
    $j = @json_decode(@file_get_contents($f), true);
    return (is_array($j) && isset($j['ocultos']) && is_array($j['ocultos'])) ? $j['ocultos'] : [];
}
}

if (!function_exists('permisosRolesCargar')) {
function permisosRolesCargar()
{
    static $cache = null;
    if ($cache !== null) return $cache;
    $f = (defined('ROOT_PATH') ? ROOT_PATH : (__DIR__ . '/..')) . '/storage/roles_permisos.json';
    $cache = ['ocultos_por_rol' => []];
    if (file_exists($f)) {
        $j = @json_decode(@file_get_contents($f), true);
        if (is_array($j) && isset($j['ocultos_por_rol']) && is_array($j['ocultos_por_rol'])) {
            $cache['ocultos_por_rol'] = $j['ocultos_por_rol'];
        }
    }
    return $cache;
}
}

if (!function_exists('moduloOcultoParaRol')) {
function moduloOcultoParaRol($key, $rol)
{
    $rol = (int)$rol;
    if ($rol === 1) return false; // admin siempre ve todo
    $p = permisosRolesCargar();
    $list = $p['ocultos_por_rol'][(string)$rol] ?? [];
    return in_array($key, $list, true);
}
}

if (!function_exists('moduloVisible')) {
function moduloVisible($key, $rol = null)
{
    if ($rol === null) $rol = (int)($_SESSION['rol'] ?? 0);
    if (function_exists('moduloActivo') && !moduloActivo($key)) return false;
    if (function_exists('moduloOcultoParaRol') && moduloOcultoParaRol($key, $rol)) return false;
    return true;
}
}


?>

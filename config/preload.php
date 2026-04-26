<?php
// Preload de core framework
$files = [
    "/var/www/html/megahnet/config/Config.php",
    "/var/www/html/megahnet/config/Helpers.php",
    "/var/www/html/megahnet/config/app/Autoload.php",
    "/var/www/html/megahnet/config/app/Controller.php",
    "/var/www/html/megahnet/config/app/Query.php",
    "/var/www/html/megahnet/config/app/Views.php",
    "/var/www/html/megahnet/config/app/Conexion.php",
];
foreach ($files as $f) {
    if (file_exists($f)) {
        require_once $f;
    }
}

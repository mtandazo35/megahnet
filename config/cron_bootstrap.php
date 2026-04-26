<?php

// Evitar timeouts en procesos largos
set_time_limit(0);
ini_set('memory_limit', '512M');

// Cargar configuración principal
require_once __DIR__ . '/Config.php';
require_once __DIR__ . '/Helpers.php';

// Autoload MVC (models, controllers, libs)
require_once __DIR__ . '/app/Autoload.php';

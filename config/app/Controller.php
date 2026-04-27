<?php
#[AllowDynamicProperties]
class Controller{
    public function __construct() {
        $this->views = new Views();
        $this->cargarModel();
    }
    public function cargarModel()
    {
        $model = get_class($this).'Model';
        $ruta = 'models/' . $model . '.php';
        if (!file_exists($ruta)) {
            // Fallback case-insensitive (Linux): buscar por basename en lowercase
            static $idx = null;
            if ($idx === null) {
                $idx = [];
                foreach (glob('models/*.php') as $f) { $idx[strtolower(basename($f))] = $f; }
            }
            $needle = strtolower($model . '.php');
            if (isset($idx[$needle])) $ruta = $idx[$needle];
        }
        if (file_exists($ruta)) {
            require_once $ruta;
            $this->model = new $model();
        }
    }
}

?>
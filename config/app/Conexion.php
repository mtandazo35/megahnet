<?php

class Conexion{
    private $conect;
    
    public function __construct() {
        //print_r($_POST);
        $pdo = "mysql:host=" . HOSTT . ";dbname=" . DBNAME . ";" . CHARSET;
        try {
            $this->conect = new PDO($pdo, USER, PASSWORD);
            $this->conect->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        } catch (PDOException $e) {
            echo 'Error en la conexion: ' . $e->getMessage();
        }
    }
    public function conectar()
    {
        return $this->conect;
    }
}

?>
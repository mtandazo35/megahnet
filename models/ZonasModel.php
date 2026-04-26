<?php
class ZonasModel extends Query{
    public function __construct() {
        parent::__construct();
    }
    public function getZonas($estado)
    {
        $sql = "SELECT * FROM zonas WHERE estado = $estado";
        return $this->selectAll($sql);
    }
    public function registrar($zonas)
    {
        $sql = "INSERT INTO zonas (descripcion) VALUES (?)";
        $array = array($zonas);
        return $this->insertar($sql, $array);
    }
    public function getValidar($campo, $valor, $accion, $id)
    {
        if ($accion == 'registrar' && $id == 0) {
            $sql = "SELECT id FROM zonas WHERE $campo = '$valor'";
        }else{
            $sql = "SELECT id FROM zonas WHERE $campo = '$valor' AND id != $id";
        }
        return $this->select($sql);
    }

    public function eliminar($estado, $idZonas)
    {
        $sql = "UPDATE zonas SET estado = ? WHERE id = ?";
        $array = array($estado, $idZonas);
        return $this->save($sql, $array);
    }
    public function editar($idZonas)
    {
        $sql = "SELECT * FROM zonas WHERE id = $idZonas";
        return $this->select($sql);
    }

    public function actualizar($zonas, $id)
    {
        $sql = "UPDATE zonas SET descripcion = ? WHERE id = ?";
        $array = array($zonas, $id);
        return $this->save($sql, $array);
    }
}

?>
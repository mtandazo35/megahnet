<?php
class ZonasModel extends Query{
    public function __construct() {
        parent::__construct();
    }
    public function getZonas($estado)
    {
        $sql = "SELECT z.*, m.nombre AS mikrotik_nombre
                FROM zonas z
                LEFT JOIN mikrotik m ON m.id = z.id_mikrotik
                WHERE z.estado = $estado";
        return $this->selectAll($sql);
    }
    public function getMikrotiks()
    {
        $sql = "SELECT id, nombre FROM mikrotik WHERE estado = 1 ORDER BY nombre";
        return $this->selectAll($sql);
    }
    public function registrar($zonas, $idMikrotik = null)
    {
        $sql = "INSERT INTO zonas (descripcion, id_mikrotik) VALUES (?, ?)";
        $array = array($zonas, $idMikrotik !== '' ? $idMikrotik : null);
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
    public function actualizar($zonas, $id, $idMikrotik = null)
    {
        $sql = "UPDATE zonas SET descripcion = ?, id_mikrotik = ? WHERE id = ?";
        $array = array($zonas, $idMikrotik !== '' ? $idMikrotik : null, $id);
        return $this->save($sql, $array);
    }
}
?>

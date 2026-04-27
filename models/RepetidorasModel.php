<?php
class RepetidorasModel extends Query{
    public function __construct() {
        parent::__construct();
    }
    public function getRepetidoras($estado)
    {
        $sql = "SELECT r.*, m.nombre AS mikrotik_nombre
                FROM repetidoras r
                LEFT JOIN mikrotik m ON m.id = r.id_mikrotik
                WHERE r.estado = $estado";
        return $this->selectAll($sql);
    }
    public function getMikrotiks()
    {
        $sql = "SELECT id, nombre FROM mikrotik WHERE estado = 1 ORDER BY nombre";
        return $this->selectAll($sql);
    }
    public function registrar($marca,$ssid,$ip,$canal,$seguridad,$frecuencia,$idMikrotik=null)
    {
        $sql = "INSERT INTO repetidoras (marca,ssid,ip,canal,seguridad,frecuencia,id_mikrotik) VALUES (?,?,?,?,?,?,?)";
        $array = array($marca,$ssid,$ip,$canal,$seguridad,$frecuencia, $idMikrotik !== '' ? $idMikrotik : null);
        return $this->insertar($sql, $array);
    }
    public function getValidar($campo, $valor, $accion, $id)
    {
        if ($accion == 'registrar' && $id == 0) {
            $sql = "SELECT id FROM repetidoras WHERE $campo = '$valor'";
        }else{
            $sql = "SELECT id FROM repetidoras WHERE $campo = '$valor' AND id != $id";
        }
        return $this->select($sql);
    }
    public function eliminar($estado, $idRepetidora)
    {
        $sql = "UPDATE repetidoras SET estado = ? WHERE id = ?";
        $array = array($estado, $idRepetidora);
        return $this->save($sql, $array);
    }
    public function editar($idRepetidora)
    {
        $sql = "SELECT * FROM repetidoras WHERE id = $idRepetidora";
        return $this->select($sql);
    }
    public function actualizar($marca,$ssid,$ip,$canal,$seguridad,$frecuencia, $id, $idMikrotik=null)
    {
        $sql = "UPDATE repetidoras SET marca=?, ssid=?, ip=?, canal=?, seguridad=?,frecuencia=?,id_mikrotik=? WHERE id=?";
        $array = array($marca,$ssid,$ip,$canal,$seguridad,$frecuencia, $idMikrotik !== '' ? $idMikrotik : null, $id);
        return $this->save($sql, $array);
    }
    public function buscarPorNombre($valor)
    {
        $sql = "SELECT id, nombre, telefono, direccion, correo FROM clientes WHERE (nombre LIKE ? OR num_identidad LIKE ?) AND estado = 1 LIMIT 10";
        $like = '%' . $valor . '%';
        return $this->selectAll($sql, [$like, $like]);
    }
}
?>

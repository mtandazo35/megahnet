<?php
class RepetidorasModel extends Query{
    public function __construct() {
        parent::__construct();
    }
    public function getRepetidoras($estado)
    {
        $sql = "SELECT * FROM repetidoras WHERE estado = $estado";
        return $this->selectAll($sql);
    }
    public function registrar($marca,$ssid,$ip,$canal,$seguridad,$frecuencia)
        {
        $sql = "INSERT INTO repetidoras (marca,ssid,ip,canal,seguridad,frecuencia) VALUES (?,?,?,?,?,?)";
        $array = array($marca,$ssid,$ip,$canal,$seguridad,$frecuencia);
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

    public function actualizar($marca,$ssid,$ip,$canal,$seguridad,$frecuencia, $id)
    {
        $sql = "UPDATE repetidoras SET marca=?, ssid=?, ip=?, canal=?, seguridad=?,frecuencia=? WHERE id=?";
        $array = array($marca,$ssid,$ip,$canal,$seguridad,$frecuencia, $id);
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
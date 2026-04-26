<?php
class Query extends Conexion
{
    private $pdo, $con;

    public function __construct()
    {
        $this->pdo = new Conexion();
        $this->con = $this->pdo->conectar();
    }

    // === Devuelve un solo registro ===
    public function select($sql, $array = [])
    {
        $result = $this->con->prepare($sql);
        $result->execute($array);
        return $result->fetch(PDO::FETCH_ASSOC);
    }

    // === Devuelve varios registros ===
    public function selectAll($sql, $array = [])
    {
        $result = $this->con->prepare($sql);
        $result->execute($array);
        return $result->fetchAll(PDO::FETCH_ASSOC);
    }

    // === Inserta un registro y devuelve el ID ===
    public function insertar($sql, $array)
    {
        $result = $this->con->prepare($sql);
        $data = $result->execute($array);
        if ($data) {
            $res = $this->con->lastInsertId();
        } else {
            $res = 0;
        }
        return $res;
    }

    // === Ejecuta UPDATE o DELETE ===
    public function save($sql, $array)
    {
        $result = $this->con->prepare($sql);
        $data = $result->execute($array);
        if ($data) {
            $res = 1;
        } else {
            $res = 0;
        }
        return $res;
    }
    public function select2($sql, $params = [])
    {
        try {
            $stmt = $this->con->prepare($sql);
            $stmt->execute($params);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            die("ERROR EN SELECT: " . $e->getMessage());
        }
    }
       public function selectAllPrepared($sql, $params)
    {
        $result = $this->con->prepare($sql);
        $result->execute($params);
        return $result->fetchAll(PDO::FETCH_ASSOC);
    }
}
?>
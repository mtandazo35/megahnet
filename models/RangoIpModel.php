<?php
class RangoIpModel extends Query{
    public function __construct() {
        parent::__construct();
    }
    public function getIp()
    {
        $sql = "SELECT i.id,i.ultima,i.red,i.gateway,i.final,
                z.descripcion AS zona, m.nombre AS mikrotik_nombre, i.id_mikrotik, i.estado
                FROM ip i
                INNER JOIN zonas z ON z.id=i.id_zona
                LEFT JOIN mikrotik m ON m.id = i.id_mikrotik WHERE i.estado = 1";
        return $this->selectAll($sql);
    }
    public function getMikrotiks()
    {
        $sql = "SELECT id, nombre FROM mikrotik WHERE estado = 1 ORDER BY nombre";
        return $this->selectAll($sql);
    }
    public function registrar($red,$final,$ultima,$zona,$gateway=null,$idMikrotik=null)
    {
        if ($gateway === null) $gateway = $red;
        $sql = "INSERT INTO ip (red,gateway,final,ultima,id_zona,id_mikrotik) VALUES (?,?,?,?,?,?)";
        $array = array($red,$gateway,$final,$ultima,$zona, $idMikrotik !== '' ? $idMikrotik : null);
        return $this->insertar($sql, $array);
    }
    public function getValidar($campo, $valor, $accion, $id)
    {
        if ($accion == 'registrar' && $id == 0) {
            $sql = "SELECT id FROM ip WHERE $campo = '$valor'";
        }else{
            $sql = "SELECT id FROM ip WHERE $campo = '$valor' AND id != $id";
        }
        return $this->select($sql);
    }
    public function eliminar($estado, $idIp)
    {
        $sql = "UPDATE ip SET estado = ? WHERE id = ?";
        $array = array($estado, $idIp);
        return $this->save($sql, $array);
    }
    public function editar($idIp)
    {
        $sql = "SELECT * FROM ip WHERE id = $idIp";
        return $this->select($sql);
    }
    public function zona($estado)
    {
        $sql = "SELECT * FROM zonas WHERE estado = $estado";
        return $this->selectAll($sql);
    }
    public function actualizar($red,$final, $id,$zona,$gateway=null,$idMikrotik=null)
    {
        if ($gateway === null) $gateway = $red;
        $sql = "UPDATE ip SET red = ?,gateway=?,final=?,id_zona=?,id_mikrotik=? WHERE id = ?";
        $array = array($red,$gateway,$final,$zona, $idMikrotik !== '' ? $idMikrotik : null, $id);
        return $this->save($sql, $array);
    }
    public function contarClientesEnRango($red, $final)
    {
        $sql = "SELECT COUNT(*) AS c FROM contratos
                WHERE estado = 1 AND ip_usuario IS NOT NULL AND ip_usuario != ''
                AND INET_ATON(ip_usuario) BETWEEN INET_ATON(?) AND INET_ATON(?)";
        $r = $this->select2($sql, [$red, $final]);
        return $r ? intval($r[0]['c']) : 0;
    }
    public function contarContratosConIp($ip)
    {
        $sql = "SELECT COUNT(*) AS c FROM contratos WHERE estado = 1 AND ip_usuario = ?";
        $r = $this->select2($sql, [$ip]);
        return $r ? intval($r[0]['c']) : 0;
    }
}
?>

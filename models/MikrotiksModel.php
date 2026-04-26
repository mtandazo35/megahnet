<?php
class MikrotiksModel extends Query{
    public function __construct() {
        parent::__construct();
    }
    public function getMikrotiks($estado)
    {
        $sql = "SELECT * FROM mikrotik WHERE estado = $estado";
        return $this->selectAll($sql);
    }
    public function registrar($nombre, $ip, $usuario,
    $clave, $puerto)
    {
        $sql = "INSERT INTO mikrotik (nombre, ip,usuario,clave, puerto) VALUES (?,?,?,?,?)";
        $array = array($nombre, $ip, $usuario,
    $clave, $puerto);
        return $this->insertar($sql, $array);
    }

    public function getValidar($campo, $valor, $accion, $id)
    {
        if ($accion == 'registrar' && $id == 0) {
            $sql = "SELECT id FROM mikrotik WHERE $campo = '$valor'";
        }else{
            $sql = "SELECT id FROM mikrotik WHERE $campo = '$valor' AND id != $id";
        }
        return $this->select($sql);
    }

 public function actualizar($nombre, $ip, $usuario,
    $clave, $puerto, $id)
    {
        $sql = "UPDATE mikrotik SET nombre=?, ip=?, usuario=?, clave=?, puerto=? WHERE id=?";
        $array = array($nombre, $ip, $usuario,
    $clave, $puerto, $id);
        return $this->save($sql, $array);
    }
    public function actualizarSesion($sesion, $id)
    {
        $sql = "UPDATE mikrotik SET sesion=? WHERE id=?";
        $array = array($sesion, $id);
        return $this->save($sql, $array);
    }

 public function eliminar($estado, $idMikrotik)
    {
        $sql = "UPDATE mikrotik SET estado = ? WHERE id = ?";
        $array = array($estado, $idMikrotik);
        return $this->save($sql, $array);
    }
  public function editar($idMikrotik)
    {
        $sql = "SELECT * FROM mikrotik WHERE id = $idMikrotik";
        return $this->select($sql);
    }

    /** Actualiza el estado de conexion (online/offline) y timestamp */
    public function actualizarEstadoConexion($id, $estado, $error = null)
    {
        $sql = "UPDATE mikrotik
                SET estado_conexion = ?, ultima_verificacion = NOW(), ultimo_error = ?
                WHERE id = ?";
        return $this->save($sql, [$estado, $error, $id]);
    }

    /** Lista todos los activos (para cron de verificacion masiva) */
    public function getMikrotiksParaVerificar()
    {
        $sql = "SELECT id, nombre, ip, usuario, clave, puerto FROM mikrotik WHERE estado = 1";
        return $this->selectAll($sql);
    }

    /** Cuenta contratos activos asociados a este Mikrotik. */
    public function contarContratosActivos($idMikrotik)
    {
        $sql = "SELECT COUNT(*) AS total
                FROM contratos
                WHERE id_mikrotik = ? AND estado = 1";
        $r = $this->select($sql, [$idMikrotik]);
        return isset($r['total']) ? (int)$r['total'] : 0;
    }

    /** Cuenta TODOS los contratos asociados (activos + inactivos). */
    public function contarContratosTotales($idMikrotik)
    {
        $sql = "SELECT COUNT(*) AS total FROM contratos WHERE id_mikrotik = ?";
        $r = $this->select($sql, [$idMikrotik]);
        return isset($r['total']) ? (int)$r['total'] : 0;
    }

    /** DELETE definitivo (no recuperable). */
    public function eliminarPermanente($id)
    {
        $sql = "DELETE FROM mikrotik WHERE id = ?";
        return $this->save($sql, [$id]);
    }
}

?>
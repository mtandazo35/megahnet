<?php
class GrupoTrabajosModel extends Query{
    public function __construct() {
        parent::__construct();
    }
    public function getGrupoTrabajos($estado)
    {
        $sql = "SELECT gt.id, gt.descripcion,gt.observacion,u.nombre,u.apellido,u.estado,u.vinculo FROM grupo_trabajo gt 
        INNER JOIN usuarios u ON u.id = gt.id_responsable 
        WHERE gt.estado = $estado";
        return $this->selectAll($sql);
    }
    public function getUsuariosCount($id)
    {
        $sql = "SELECT COUNT(*) as cantidad FROM usuarios WHERE id_grupo_trabajo = $id";
        return $this->select($sql);
    }
    public function registrar($id_responsable,$descripcion, $observacion)
    {
        $sql = "INSERT INTO grupo_trabajo (id_responsable,descripcion, observacion) VALUES (?,?,?)";
        $array = array($id_responsable,$descripcion, $observacion);
        return $this->insertar($sql, $array);
    }



    public function eliminar($estado, $idGrupoTrabajos)
    {
        $sql = "UPDATE grupo_trabajo SET estado = ? WHERE id = ?";
        $array = array($estado, $idGrupoTrabajos);
        return $this->save($sql, $array);
    }
    public function editar($idGrupoTrabajos)
    {
        $sql = "SELECT gt.id,gt.descripcion,gt.id_responsable,gt.observacion,CONCAT(u.nombre,' ',u.apellido) AS responsable FROM grupo_trabajo gt INNER JOIN usuarios u ON u.id=gt.id_responsable WHERE gt.id = $idGrupoTrabajos";
        return $this->select($sql);
    }

    public function actualizar($id_responsable,$descripcion, $observacion, $id)
    {
        $sql = "UPDATE grupo_trabajo SET id_responsable=?, descripcion=?, observacion=? WHERE id=?";
        $array = array($id_responsable,$descripcion, $observacion,$id);
        return $this->save($sql, $array);
    }
    public function buscarPorNombre($valor)
    {
        $sql = "SELECT u.id,CONCAT(u.nombre,' ',u.apellido) AS responsable FROM usuarios u
        WHERE u.nombre LIKE '%".$valor."%' OR u.apellido LIKE '%".$valor."%' AND u.estado = 1";
        return $this->selectAll($sql);
    }
}

?>
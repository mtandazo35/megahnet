<?php
class CasosModel extends Query
{
    public function __construct()
    {
        parent::__construct();
    }

    public function buscarPorNombre($valor)
    {
        $sql = "SELECT c.id AS idContrato, c.id_cliente,c.coordenada, c.direccion,c.comentario,cl.nombre FROM contratos c
        INNER JOIN clientes cl ON cl.id=c.id_cliente WHERE cl.nombre LIKE '%" . $valor . "%' OR cl.num_identidad LIKE '%" . $valor . "%' AND c.estado = 1";
        return $this->selectAll($sql);
    }

    public function buscarPorNombreGrupoTrabajo($valor)
    {
        $sql = "SELECT gt.id,gt.descripcion, CONCAT(u.nombre,' ',u.apellido) AS responsable FROM grupo_trabajo gt 
        INNER JOIN usuarios u ON u.id=gt.id_responsable WHERE gt.descripcion LIKE '%" . $valor . "%' OR u.nombre LIKE '%" . $valor . "%' OR u.apellido LIKE '%" . $valor . "%' AND gt.estado = 1";
        return $this->selectAll($sql);
    }

    public function registrarCaso($fecha, $hora, $idContrato, $idUsuario, $grupoAsignado, $problemaReportado, $estado)
    {
        $sql = "INSERT INTO casos (fecha, hora, id_contrato, id_usuario, grupo_asignado,problema_reportado,estado) VALUES (?,?,?,?,?,?,?)";
        $array = array($fecha, $hora, $idContrato, $idUsuario, $grupoAsignado, $problemaReportado, $estado);
        return $this->insertar($sql, $array);
    }
    public function actualizarCaso(
        $idGrupoTrabajo,
        $problemaReportado,
        $trabajoRealizado,
        $observacion,
        $estado,
        $id
    ) {
        $sql = "UPDATE casos SET grupo_asignado=?, problema_reportado=?, trabajo_realizado=?, observacion=?,estado=? WHERE id=?";
        $array = array(
            $idGrupoTrabajo,
            $problemaReportado,
            $trabajoRealizado,
            $observacion,
            $estado,
            $id
        );
        return $this->save($sql, $array);
    }
    public function getCaso($id)
    {
        $sql = "SELECT c.id,c.fecha,c.hora,c.problema_reportado,c.trabajo_realizado,c.observacion,c.estado,ct.coordenada,ct.direccion,cl.identidad, cl.nombre,cl.num_identidad,cl.telefono,c.grupo_asignado,gt.descripcion,CONCAT(u.nombre,' ',u.apellido) AS usuario FROM casos c INNER JOIN contratos ct ON ct.id=c.id_contrato INNER JOIN clientes cl ON cl.id=ct.id_cliente INNER JOIN grupo_trabajo gt ON gt.id=c.grupo_asignado INNER JOIN usuarios u ON u.id=c.id_usuario WHERE c.id = $id";
        return $this->select($sql);
    }
    public function getCasos($rol, $idUsuario)
    {

        if ($rol == 1) {
            $sql = "SELECT c.id,CONCAT(c.fecha,' ',c.hora) AS fecha,c.problema_reportado,c.trabajo_realizado,c.observacion,c.estado,ct.coordenada,ct.direccion,cl.identidad, cl.nombre,cl.num_identidad,cl.telefono,c.grupo_asignado,gt.descripcion,CONCAT(u.nombre,' ',u.apellido) AS usuario FROM casos c INNER JOIN contratos ct ON ct.id=c.id_contrato INNER JOIN clientes cl ON cl.id=ct.id_cliente INNER JOIN grupo_trabajo gt ON gt.id=c.grupo_asignado INNER JOIN usuarios u ON u.id=c.id_usuario WHERE c.estado != 'FINALIZADO'";
            return $this->selectAll($sql);
        } else {
            $sql = "SELECT c.id,CONCAT(c.fecha,' ',c.hora) AS fecha,c.problema_reportado,c.trabajo_realizado,c.observacion,c.estado,ct.coordenada,ct.direccion,cl.identidad, cl.nombre,cl.num_identidad,cl.telefono,c.grupo_asignado,gt.descripcion,CONCAT(u.nombre,' ',u.apellido) AS usuario FROM casos c INNER JOIN contratos ct ON ct.id=c.id_contrato INNER JOIN clientes cl ON cl.id=ct.id_cliente INNER JOIN grupo_trabajo gt ON gt.id=c.grupo_asignado INNER JOIN usuarios u ON u.id=c.id_usuario
    WHERE gt.id_responsable = $idUsuario AND c.estado != 'FINALIZADO'";
            return $this->selectAll($sql);
        }
    }
    public function getGrupoAsignado($id)
    {
        $sql = "SELECT CONCAT(u.nombre,' ',u.apellido) AS empleado, gt.descripcion FROM casos c INNER JOIN grupo_trabajo gt ON gt.id= c.grupo_asignado INNER JOIN usuarios u ON u.id_grupo_trabajo=c.grupo_asignado WHERE c.id = $id";
        return $this->selectAll($sql);
    }
    public function getEmpresa()
    {
        $sql = "SELECT * FROM configuracion";
        return $this->select($sql);
    }
    public function getInfoCliente($idContrato)
    {
        $sql = "SELECT cl.telefono FROM contratos c INNER JOIN clientes cl ON cl.id=c.id_cliente WHERE c.id = $idContrato";
        return $this->select($sql);
    }
    public function getResponsable($idGrupoTrabajo)
    {
        $sql = "SELECT gt.id, gt.descripcion,gt.observacion,CONCAT(u.nombre,' ',u.apellido) AS responsable,u.estado,u.vinculo FROM grupo_trabajo gt INNER JOIN usuarios u ON u.id = gt.id_responsable WHERE gt.id = $idGrupoTrabajo";
        return $this->selectAll($sql);
    }




    public function eliminar($estado, $idCliente)
    {
        $sql = "UPDATE clientes SET estado = ? WHERE id = ?";
        $array = array($estado, $idCliente);
        return $this->save($sql, $array);
    }
    public function editar($idCaso)
    {
        $sql = "SELECT c.id,c.fecha,c.hora,c.problema_reportado,c.trabajo_realizado,c.observacion,c.estado,ct.id AS idContrato, ct.coordenada,ct.direccion,ct.comentario,cl.identidad, cl.nombre,cl.num_identidad,cl.telefono,c.grupo_asignado,gt.descripcion,CONCAT(u.nombre,' ',u.apellido) AS responsable FROM casos c 
        INNER JOIN contratos ct ON ct.id=c.id_contrato INNER JOIN clientes cl ON cl.id=ct.id_cliente
         INNER JOIN grupo_trabajo gt ON gt.id=c.grupo_asignado 
         INNER JOIN usuarios u ON u.id=gt.id_responsable WHERE c.id =$idCaso";
        return $this->select($sql);
    }
}

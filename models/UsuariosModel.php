<?php
class UsuariosModel extends Query
{
    public function __construct()
    {
        parent::__construct();
    }
    public function getUsuarios($estado)
    {
        $sql = "SELECT u.id, CONCAT(u.nombre, ' ', u.apellido) AS nombres, u.correo, u.clave, u.telefono, u.direccion, u.rol,u.vinculo, gt.descripcion FROM usuarios u
        INNER JOIN grupo_trabajo gt ON gt.id = u.id_grupo_trabajo
        WHERE u.estado = $estado";
        return $this->selectAll($sql);
    }
    public function getGrupoTrabajos($estado)
    {
        $sql = "SELECT id, descripcion FROM grupo_trabajo WHERE estado = $estado";
        return $this->selectAll($sql);
    }
    public function registrar($nombres, $apellidos, $correo, $telefono, $direccion, $clave, $rol,$grupotrabajos)
    {
        $sql = "INSERT INTO usuarios(nombre,apellido,correo,telefono,direccion,clave,rol,id_grupo_trabajo) VALUES (?,?,?,?,?,?,?,?)";
        $array = array($nombres, $apellidos, $correo, $telefono, $direccion, $clave, $rol,$grupotrabajos);
        return  $this->insertar($sql, $array);
    }
    public function actualizar($nombres, $apellidos, $correo, $telefono, $direccion,$clave, $rol,$grupotrabajos, $id)
    {
        $sql = "UPDATE usuarios SET nombre = ?, apellido = ?, correo = ?, telefono = ?, direccion = ?,clave=?, rol = ?,id_grupo_trabajo=? WHERE id = ?";
        $array = array($nombres, $apellidos, $correo, $telefono, $direccion,$clave, $rol,$grupotrabajos, $id);
        return  $this->save($sql, $array);
    }
    public function getValidar($campo, $valor, $accion, $id)
    {
        if ($accion == 'registrar' && $id == 0) {
            $sql = "SELECT id,correo,telefono FROM  usuarios WHERE $campo = '$valor'";
        } else {
            $sql = "SELECT id,correo,telefono FROM  usuarios WHERE $campo = '$valor' AND id != $id";
        }
        return $this->select($sql);
    }
    public function eliminar($estado, $id)
    {
        $sql = "UPDATE usuarios SET estado = ? WHERE id = ?";
        $array = array($estado, $id);
        return $this->save($sql, $array);
    }
    public function editar($id)
    {
        $sql = "SELECT u.id,u.nombre,u.perfil,u.fecha, u.apellido, u.correo, u.clave, u.telefono, u.direccion, u.rol,u.vinculo,u.id_grupo_trabajo FROM usuarios u
        WHERE u.id = $id";
        return $this->select($sql);
    }

    public function modificarDatos($nombre,$apellidos,$correo,$telefono,$direccion,$clave, $perfil,$id
    ) {
        $sql = "UPDATE usuarios SET nombre=?, apellido=?, correo=?, telefono=?, direccion=?, clave=?, perfil=? WHERE id=?";
        $array = array($nombre, $apellidos, $correo, $telefono, $direccion, $clave, $perfil, $id);
        return $this->save($sql, $array);
    }

    //registrar log
    public function registrarAcceso($evento, $ip, $detalle)
    {
        $sql = "INSERT INTO acceso (evento, ip, detalle) VALUES (?,?,?)";
        $array = array($evento, $ip, $detalle);
        return $this->insertar($sql, $array);
    }
}

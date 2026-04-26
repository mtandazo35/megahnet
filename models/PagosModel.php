<?php
class PagosModel extends Query
{
    public function __construct()
    {
        parent::__construct();
    }
    

    /*public function getFacturas()
    {
        $sql = "SELECT o.num_orden,o.fecha,o.estado,o.codigo,cl.identidad,cl.num_identidad,cl.nombre as cliente,cl.telefono,cl.correo,cl.direccion FROM orden o INNER JOIN clientes cl ON cl.id=o.id_cliente";
        return $this->selectAll($sql);
    }
    public function getEmpresa()
    {
        $sql = "SELECT * FROM configuracion";
        return $this->select($sql);
    }*/
   
    public function getFactura($busqueda)
    {
        $sql = "SELECT dce.fecha,dce.orden_no,dce.ruc,dce.cliente,dce.totalfactura,dce.metodo,dce.tipopago,dce.claveacceso,dce.estado FROM datos_cabecera_electronica dce WHERE (dce.ruc LIKE '%$busqueda%' OR dce.orden_no LIKE '%$busqueda%') AND dce.tipopago =2";
        return $this->selectAll($sql);
    }
    /*
    public function getOrdenUser($busqueda)
    {
        $sql = "SELECT o.num_orden,o.fecha,o.estado,o.codigo,cl.identidad,cl.num_identidad,cl.nombre as cliente,cl.telefono,cl.correo,cl.direccion,u.nombre as usernombre,u.apellido as userapellido FROM orden o INNER JOIN clientes cl ON cl.id=o.id_cliente INNER JOIN usuarios u ON u.id=o.id_usuario WHERE o.num_orden LIKE '%$busqueda%' OR o.codigo LIKE '%$busqueda%' ";
        return $this->select($sql);
    }
    public function getDetalle($numOrden)
    {
        $sql = "SELECT * FROM orden_detalle WHERE num_ordenenc = $numOrden";
        return $this->selectAll($sql);
    }

   
    public function getSerie()
    {
        $sql = "SELECT MAX(id) AS total FROM orden";
        return $this->select($sql);
    }
    public function gettotalOrden()
    {
        $sql = "SELECT MAX(orden) AS totalOrden FROM orden WHERE estado = 'INGRESO'";
        return $this->select($sql);
    }*/

   
}

<?php
class ProductosModel extends Query
{
    public function __construct()
    {
        parent::__construct();
    }
    public function getProductos($estado)
    {
        $sql = "SELECT p.*, c.categoria FROM productos p 
        INNER JOIN categorias c ON p.id_categoria = c.id 
        WHERE p.estado = $estado";
        return $this->selectAll($sql);
    }

    public function getDatos($table)
    {
        $sql = "SELECT * FROM $table WHERE estado = 1";
        return $this->selectAll($sql);
    }

    public function registrar(
        $codigo,
        $nombre,
        $precio_compra,
        $precio_venta,
        $id_categoria,
        $foto,
        $iva
    ) {
        $sql = "INSERT INTO productos (codigo, descripcion, precio_compra, precio_venta, id_categoria, foto, iva) VALUES (?,?,?,?,?,?,?)";
        $array = array(
            $codigo, $nombre, $precio_compra, $precio_venta,$id_categoria, $foto, $iva
        );
        return $this->insertar($sql, $array);
    }

    public function getValidar($campo, $valor, $accion, $id)
    {
        if ($accion == 'registrar' && $id == 0) {
            $sql = "SELECT id FROM productos WHERE $campo = '$valor'";
        } else {
            $sql = "SELECT id FROM productos WHERE $campo = '$valor' AND id != $id";
        }
        return $this->select($sql);
    }

    public function eliminar($estado, $idProducto)
    {
        $sql = "UPDATE productos SET estado = ? WHERE id = ?";
        $array = array($estado, $idProducto);
        return $this->save($sql, $array);
    }

    public function editar($idProducto)
    {
        $sql = "SELECT * FROM productos WHERE (id = '$idProducto' OR codigo='$idProducto')";
        return $this->select($sql);
    }

    public function actualizar(
        $codigo,
        $nombre,
        $precio_compra,
        $precio_venta,
        $id_categoria,
        $foto,
        $iva,
        $id
    ) {
        $sql = "UPDATE productos SET codigo=?, descripcion=?, precio_compra=?, precio_venta=?, id_categoria=?, foto=? , iva=? WHERE id=?";
        $array = array(
            $codigo, $nombre, $precio_compra, $precio_venta,
            $id_categoria, $foto, $iva, $id
        );
        return $this->save($sql, $array);
    }

    public function buscarPorCodigo($valor)
    {
        $sql = "SELECT id,descripcion,cantidad,precio_compra,precio_venta,id_categoria FROM productos WHERE descripcion LIKE '%" . $valor . "%' AND id_categoria = 2 AND estado = 1 LIMIT 10";
        return $this->selectAll($sql);
    }

    public function buscarPorNombre($valor)
    {
        $sql = "SELECT id, descripcion,cantidad,precio_compra,precio_venta,id_categoria FROM productos WHERE descripcion LIKE '%" . $valor . "%' AND id_categoria = 1 AND estado = 1 LIMIT 10";
        return $this->selectAll($sql);
    }
    /**
     * Busca SOLO productos fisicos (id_categoria=2) por nombre.
     * Para autocomplete del modulo Inventario, donde no aplican los servicios
     * (planes recurrentes). El nombre buscarPorNombre original esta dedicado
     * a servicios y no se puede tocar sin romper otros modulos (POS).
     */
    public function buscarPorNombreInventario($valor)
    {
        $sql = "SELECT id, descripcion, cantidad, precio_compra, precio_venta, id_categoria
                FROM productos
                WHERE descripcion LIKE '%" . $valor . "%'
                  AND id_categoria = 2
                  AND estado = 1
                LIMIT 10";
        return $this->selectAll($sql);
    }
    public function buscarPorNombreTipoPago($valor)
    {
        $sql = "SELECT * FROM tipo_pago WHERE nombre LIKE '%" . $valor . "%' AND estado = 1 LIMIT 10";
        return $this->selectAll($sql);
    }
    public function buscarPorNombreRetencion($valor)
    {
        $sql = "SELECT * FROM codigos_retencion WHERE (tipo LIKE '%" . $valor . "%' OR descripcion LIKE '%" . $valor . "%' OR codigo LIKE '%" . $valor . "%') AND estado = 1 LIMIT 10";
        return $this->selectAll($sql);
    }
    public function getEmpresa()
    {
        $sql = "SELECT * FROM configuracion";
        return $this->select($sql);
    }
    public function codigoBarra($valor)
    {
        $sql = "SELECT id,codigo,descripcion,precio_venta FROM productos WHERE id = $valor";
        return $this->select($sql);
    }
    public function BuscarTipoPago($idTipoPago)
    {
        $sql = "SELECT * FROM tipo_pago WHERE id = $idTipoPago";
        return $this->select($sql);
    }

    public function BuscarRetencion($idTipoRetencion)
    {
        $sql = "SELECT * FROM codigos_retencion WHERE id = $idTipoRetencion";
        return $this->select($sql);
    }
}

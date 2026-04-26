<?php
class SucursalesModel extends Query
{
    public function __construct()
    {
        parent::__construct();
    }

    public function getSucursales($estado)
    {
        $sql = "SELECT * FROM sucursales WHERE estado = ? ORDER BY establecimiento, puntoemi";
        return $this->selectAll($sql, [$estado]);
    }

    public function editar($id)
    {
        $sql = "SELECT * FROM sucursales WHERE id = ?";
        return $this->select($sql, [$id]);
    }

    /** Verifica que la combinación establecimiento+puntoemi sea única */
    public function getValidarUnique($establecimiento, $puntoemi, $id = 0)
    {
        if ($id > 0) {
            $sql = "SELECT id FROM sucursales WHERE establecimiento = ? AND puntoemi = ? AND id != ?";
            return $this->select($sql, [$establecimiento, $puntoemi, $id]);
        }
        $sql = "SELECT id FROM sucursales WHERE establecimiento = ? AND puntoemi = ?";
        return $this->select($sql, [$establecimiento, $puntoemi]);
    }

    public function registrar($d)
    {
        $sql = "INSERT INTO sucursales
                (nombre, direccion, establecimiento, puntoemi,
                 sec_factura, sec_factura_pruebas, sec_notacredito, sec_notacredito_pruebas,
                 sec_recibo, ambiente)
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
        $params = [
            $d['nombre'], $d['direccion'], $d['establecimiento'], $d['puntoemi'],
            $d['sec_factura'], $d['sec_factura_pruebas'], $d['sec_notacredito'], $d['sec_notacredito_pruebas'],
            $d['sec_recibo'], $d['ambiente']
        ];
        return $this->insertar($sql, $params);
    }

    public function actualizar($d, $id)
    {
        $sql = "UPDATE sucursales SET
                  nombre = ?, direccion = ?, establecimiento = ?, puntoemi = ?,
                  sec_factura = ?, sec_factura_pruebas = ?, sec_notacredito = ?, sec_notacredito_pruebas = ?,
                  sec_recibo = ?, ambiente = ?
                WHERE id = ?";
        $params = [
            $d['nombre'], $d['direccion'], $d['establecimiento'], $d['puntoemi'],
            $d['sec_factura'], $d['sec_factura_pruebas'], $d['sec_notacredito'], $d['sec_notacredito_pruebas'],
            $d['sec_recibo'], $d['ambiente'],
            $id
        ];
        return $this->save($sql, $params);
    }

    public function eliminar($estado, $id)
    {
        $sql = "UPDATE sucursales SET estado = ? WHERE id = ?";
        return $this->save($sql, [$estado, $id]);
    }
}

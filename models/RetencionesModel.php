<?php
class RetencionesModel extends Query
{
    public function __construct()
    {
        parent::__construct();
    }
    public function getRetencion($idRetencion)
    {
        $sql = "SELECT * FROM codigos_retencion WHERE id = $idRetencion";
        return $this->select($sql);
    }
    
    public function getEmpresa()
    {
        $sql = "SELECT * FROM configuracion";
        return $this->select($sql);
    }

    
    public function getRetencionElectronica($idRetencion)
    {
        $sql = "SELECT r.cliente,r.fecha,r.id,r.ruc,r.estado,r.totalFactura,r.claveAcceso,p.correo,r.secuencial FROM retencion r
        INNER JOIN proveedor p ON p.ruc=r.ruc
        WHERE r.id = $idRetencion";
        return $this->selectAll($sql);
    }

   

    public function getRetencionesElectronica()
    {
        $sql = "SELECT r.id, r.fecha,r.cliente,r.numFactura,r.totalFactura,r.claveAcceso, rs.estado as autorizacion,r.estado FROM retencion r
         INNER JOIN respuesta_sri rs ON rs.claveAcceso = r.claveAcceso";
        return $this->selectAll($sql);
    }

 
    public function anularElectronica($idVenta)
    {
        $sql = "UPDATE datos_cabecera_electronica SET estado = ? WHERE orden_no = ?";
        $array = array(0, $idVenta);
        return $this->save($sql, $array);
    }
    


    public function getSerieRetenciones() //ya esta
    {
        $sql = "SELECT MAX(id) AS total FROM retencion";
        return $this->select($sql);
    }
    //movimiento
   

    public function getCaja($id_usuario)
    {
        $sql = "SELECT * FROM cajas WHERE estado = 1 AND id_usuario = $id_usuario";
        return $this->select($sql);
    }

    //registro de facturacion Electronica

    public function registrarEncabezado(
        $fecha,        
        $proveedorNombre,
        $proveedorDireccion,
        $proveedorTelefono,
        $proveedorRuc,
        $tipoIdentificacion,
        $proveedorCorreo,
        $empresaEstablecimiento,
        $empresaPuntoemi,
        $empresaRuc,
        $ambiente,
        $empresaRazon,
        $empresaNombre,
        $tipoEmision,
        $secuencial,
        $empresaDireccion,
        $empresaObligado,
        $fechaEmision,
        $numFactura,
        $peridoFiscal,
        $totalFactura,       
        $idusuario
    ) {
        $sql = "INSERT INTO retencion (fecha,cliente, direccion,telefono, ruc,tipo_identificacion,
         correo,establecimiento,punto_emi,ruc_empresa,ambiente,razon_social,nombre_comercial,tipoEmision,secuencial,
         direccion_matriz,obligado,fechaEmision,numFactura,periodoFiscal,totalFactura,id_usuario) VALUES (?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?)";
        $array = array(
            $fecha,        
        $proveedorNombre,
        $proveedorDireccion,
        $proveedorTelefono,
        $proveedorRuc,
        $tipoIdentificacion,
        $proveedorCorreo,
        $empresaEstablecimiento,
        $empresaPuntoemi,
        $empresaRuc,
        $ambiente,
        $empresaRazon,
        $empresaNombre,
        $tipoEmision,
        $secuencial,
        $empresaDireccion,
        $empresaObligado,
        $fechaEmision,
        $numFactura,

        $peridoFiscal,
        $totalFactura,
        $idusuario
        );
        return $this->insertar($sql, $array);
    }
    public function registrarDetalle($baseImponible, $tipoImpuesto, $porcentajeRetencion, $valorRetenido,$codigoRetencion, $idRetencion)
    {
        $sql = "INSERT INTO retencion_detalle (base_imponible,tipo_impuesto,porcentaje_retencion, valor_retenido,codigo_retencion,id_retencion) VALUES (?,?,?,?,?,?)";
        $array = array($baseImponible, $tipoImpuesto, $porcentajeRetencion, $valorRetenido,$codigoRetencion, $idRetencion);
        return $this->insertar($sql, $array);
    }
    public function actualizarClaveAccesso($claveAccesso, $idRetencion)
    {
        $sql = "UPDATE retencion SET claveAcceso = ? WHERE id = ?";
        $array = array($claveAccesso, $idRetencion);
        return $this->save($sql, $array);
    }
    public function getRetencionElectronicaCA($claveAccesso)
    {

        $sql = "SELECT * FROM retencion WHERE claveAcceso = '$claveAccesso'";
        return $this->select($sql);
    }
    public function getFacturaElectronicaDetalle($orden_no)
    {
        $sql = "SELECT orden_no, cantidad, item, precio_u, total, iva, codproducto 
        FROM detalle_factura_electronica WHERE orden_no = $orden_no";
        return $this->selectAll($sql);
    }

   
  
    //datos cliente factura electronica
    public function getProveedor($idProveedor) //ya esta
    {
        $sql = "SELECT * FROM proveedor WHERE id = $idProveedor";
        return $this->select($sql);
    }
    
  
    public function buscarCompraPorComprobante(array $variants, $cleanFull)
    {
        if (empty($variants)) return null;
        $placeholders = implode(',', array_fill(0, count($variants), '?'));
        $sql = "SELECT c.id, c.serie, c.total, c.fecha, c.productos, c.id_proveedor,
                       p.nombre, p.telefono, p.correo, p.direccion, p.ruc
                FROM compras c
                INNER JOIN proveedor p ON p.id = c.id_proveedor
                WHERE c.estado = 1 AND (
                    c.serie IN ($placeholders)
                    OR ? LIKE CONCAT('%', c.serie)
                )
                ORDER BY c.id DESC LIMIT 1";
        $params = array_merge($variants, [$cleanFull]);
        return $this->select($sql, $params);
    }
}

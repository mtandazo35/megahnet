<?php
class SriDashboardModel extends Query
{
    public function __construct()
    {
        parent::__construct();
    }

    /* =========================
       RESUMEN GENERAL DASHBOARD
       ========================= */
    public function getResumen()
    {
        $sql = "SELECT
                    SUM(estado_proceso = 0) AS pendientes,
                    SUM(estado_proceso = 1) AS autorizadas,
                    SUM(estado_proceso = 2) AS rechazadas,
                    SUM(estado_proceso = 2 AND intentos_sri < 20) AS reintentables,
                    SUM(estado_proceso = 2 AND intentos_sri >= 20) AS bloqueadas,
                    SUM(estado_proceso = 1 AND correo_enviado = 0) AS correos_pendientes
                FROM datos_cabecera_electronica";
        return $this->select($sql);
    }

    /* =========================
       LISTADO PRINCIPAL
       ========================= */
    public function getDocumentos($estado = null)
    {
        $where = '';
        if ($estado !== null) {
            $where = "WHERE estado_proceso = $estado";
        }

        $sql = "SELECT
                    id,
                    orden_no,
                    cliente,
                    fecha,
                    totalfactura,
                    estado_proceso,
                    intentos_sri,
                    correo_enviado
                FROM datos_cabecera_electronica
                $where
                ORDER BY fecha DESC
                LIMIT 200";
        return $this->selectAll($sql);
    }

    /* =========================
       SOLO BLOQUEADAS
       ========================= */
    public function getBloqueadas()
    {
        $sql = "SELECT
                    id,
                    orden_no,
                    cliente,
                    fecha,
                    totalfactura,
                    intentos_sri
                FROM datos_cabecera_electronica
                WHERE estado_proceso = 2 AND intentos_sri >= 20
                ORDER BY fecha DESC";
        return $this->selectAll($sql);
    }

    /* =========================
       DETALLE ERROR SRI
       ========================= */
    public function getErrorSri($id)
    {
        $sql = "SELECT mensaje_sri
                FROM datos_cabecera_electronica
                WHERE id = $id";
        return $this->select($sql);
    }

    /* =========================
       REINTENTAR DOCUMENTO
       ========================= */
    public function reintentar($id)
    {
        $sql = "UPDATE datos_cabecera_electronica
                SET estado_proceso = 0
                WHERE id = ?";
        return $this->save($sql, [$id]);
    }
}

/*M!999999\- enable the sandbox mode */ 

/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;
/*!40103 SET @OLD_TIME_ZONE=@@TIME_ZONE */;
/*!40103 SET TIME_ZONE='+00:00' */;
/*!40014 SET @OLD_UNIQUE_CHECKS=@@UNIQUE_CHECKS, UNIQUE_CHECKS=0 */;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;
/*M!100616 SET @OLD_NOTE_VERBOSITY=@@NOTE_VERBOSITY, NOTE_VERBOSITY=0 */;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `abonos` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `abono` decimal(10,2) NOT NULL,
  `fecha` text NOT NULL,
  `apertura` int(11) NOT NULL DEFAULT 1,
  `id_credito` int(11) NOT NULL,
  `id_usuario` int(11) DEFAULT NULL,
  `codigo_pago` text DEFAULT NULL,
  `tipo_pago` varchar(50) DEFAULT NULL,
  `id_cliente` int(11) DEFAULT NULL,
  `anticipo` decimal(10,2) NOT NULL DEFAULT 0.00,
  PRIMARY KEY (`id`),
  KEY `id_credito` (`id_credito`),
  CONSTRAINT `abonos_ibfk_1` FOREIGN KEY (`id_credito`) REFERENCES `creditos` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=25550 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_swedish_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `acceso` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `evento` varchar(50) NOT NULL,
  `ip` varchar(50) NOT NULL,
  `detalle` mediumtext NOT NULL,
  `fecha` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=1751 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_swedish_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `apartados` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `productos` longtext NOT NULL,
  `fecha_create` date NOT NULL,
  `fecha_apartado` datetime NOT NULL,
  `fecha_retiro` datetime NOT NULL,
  `abono` decimal(10,2) NOT NULL,
  `total` decimal(10,2) NOT NULL,
  `color` varchar(15) NOT NULL,
  `estado` int(11) NOT NULL DEFAULT 1,
  `id_cliente` int(11) NOT NULL,
  `id_usuario` int(11) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `id_cliente` (`id_cliente`),
  CONSTRAINT `apartados_ibfk_1` FOREIGN KEY (`id_cliente`) REFERENCES `clientes` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_swedish_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `cajas` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `monto_inicial` decimal(10,2) NOT NULL,
  `fecha_apertura` date NOT NULL,
  `fecha_cierre` date DEFAULT NULL,
  `monto_final` decimal(10,2) DEFAULT NULL,
  `total_ventas` int(11) DEFAULT NULL,
  `egresos` decimal(10,2) DEFAULT NULL,
  `gastos` decimal(10,2) DEFAULT NULL,
  `efectivo` decimal(10,2) DEFAULT NULL,
  `bancarisado` decimal(10,2) DEFAULT NULL,
  `estado` int(11) NOT NULL DEFAULT 1,
  `id_usuario` int(11) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `id_usuario` (`id_usuario`),
  CONSTRAINT `cajas_ibfk_1` FOREIGN KEY (`id_usuario`) REFERENCES `usuarios` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=751 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_swedish_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `casos` (
  `id` bigint(20) NOT NULL AUTO_INCREMENT,
  `fecha` date NOT NULL,
  `hora` time NOT NULL,
  `id_contrato` bigint(20) NOT NULL,
  `id_usuario` int(11) NOT NULL,
  `grupo_asignado` int(11) NOT NULL,
  `problema_reportado` text DEFAULT NULL,
  `trabajo_realizado` text DEFAULT NULL,
  `observacion` varchar(250) DEFAULT NULL,
  `estado` varchar(25) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `id_contrato` (`id_contrato`),
  CONSTRAINT `casos_ibfk_1` FOREIGN KEY (`id_contrato`) REFERENCES `contratos` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=10 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_swedish_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `categorias` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `categoria` varchar(100) NOT NULL,
  `fecha` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `estado` int(11) NOT NULL DEFAULT 1,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_swedish_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `clientes` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `identidad` varchar(50) NOT NULL,
  `num_identidad` varchar(15) NOT NULL,
  `nombre` varchar(255) NOT NULL,
  `telefono` varchar(10) DEFAULT NULL,
  `correo` varchar(50) DEFAULT NULL,
  `direccion` varchar(255) NOT NULL,
  `fecha` timestamp NOT NULL DEFAULT current_timestamp(),
  `estado` int(11) NOT NULL DEFAULT 1,
  `fecha_corte` varchar(10) DEFAULT NULL,
  `generar_contrato` int(11) DEFAULT NULL,
  `observacion` varchar(255) DEFAULT NULL,
  `anticipos` decimal(10,2) DEFAULT 0.00,
  PRIMARY KEY (`id`),
  KEY `idx_num_identidad` (`num_identidad`),
  FULLTEXT KEY `idxfull_num_identidad` (`num_identidad`)
) ENGINE=InnoDB AUTO_INCREMENT=1461 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_swedish_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `codigos_retencion` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `tipo` varchar(255) NOT NULL,
  `codigo` varchar(255) NOT NULL,
  `porcentajeretencion` varchar(255) NOT NULL,
  `descripcion` varchar(255) NOT NULL,
  `estado` int(11) DEFAULT 1,
  PRIMARY KEY (`id`) USING BTREE
) ENGINE=InnoDB AUTO_INCREMENT=27 DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_unicode_ci ROW_FORMAT=COMPACT;
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `compras` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `productos` longtext NOT NULL,
  `total` decimal(10,2) NOT NULL,
  `fecha` date NOT NULL,
  `hora` time NOT NULL,
  `serie` varchar(20) NOT NULL,
  `estado` int(11) NOT NULL DEFAULT 1,
  `apertura` int(11) NOT NULL DEFAULT 1,
  `id_proveedor` int(11) NOT NULL,
  `id_usuario` int(11) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `id_proveedor` (`id_proveedor`),
  KEY `id_usuario` (`id_usuario`),
  CONSTRAINT `compras_ibfk_1` FOREIGN KEY (`id_proveedor`) REFERENCES `proveedor` (`id`),
  CONSTRAINT `compras_ibfk_2` FOREIGN KEY (`id_usuario`) REFERENCES `usuarios` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_swedish_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `configuracion` (
  `id` bigint(20) NOT NULL AUTO_INCREMENT,
  `ruc` varchar(13) DEFAULT NULL,
  `nombre` varchar(100) DEFAULT NULL,
  `razon_social` varchar(100) DEFAULT NULL,
  `telefono` varchar(50) DEFAULT NULL,
  `correo` varchar(100) DEFAULT NULL,
  `direccion` varchar(300) DEFAULT NULL,
  `impuesto` int(11) DEFAULT NULL,
  `mensaje` mediumtext DEFAULT NULL,
  `totalitems` int(11) NOT NULL,
  `establecimiento` varchar(10) NOT NULL,
  `puntoemi` varchar(10) NOT NULL,
  `contabilidad` varchar(10) NOT NULL,
  `facturaelectronica` int(11) DEFAULT NULL,
  `img` varchar(25) DEFAULT NULL,
  `firmainicio` date DEFAULT NULL,
  `firmafinal` date DEFAULT NULL,
  `firma_password` varchar(255) DEFAULT NULL,
  `cantidaddocumento` int(11) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_swedish_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `contratos` (
  `id` bigint(20) NOT NULL AUTO_INCREMENT,
  `fecha` date NOT NULL,
  `hora` time NOT NULL,
  `id_cliente` int(11) NOT NULL,
  `id_usuario` int(11) NOT NULL,
  `productos` mediumtext NOT NULL,
  `total` decimal(10,2) NOT NULL,
  `ip_usuario` varchar(100) NOT NULL,
  `repetidora` varchar(100) NOT NULL,
  `ap` varchar(100) NOT NULL,
  `coordenada` mediumtext DEFAULT NULL,
  `direccion` mediumtext DEFAULT NULL,
  `comentario` varchar(255) DEFAULT NULL,
  `medio` varchar(100) DEFAULT NULL,
  `comparticion` mediumtext DEFAULT NULL,
  `estado` int(11) NOT NULL DEFAULT 1,
  `factura` int(11) DEFAULT 0,
  `tipo_banco` text DEFAULT NULL,
  `cuenta_banco` text DEFAULT NULL,
  `ancho_banda` varchar(20) DEFAULT NULL,
  `discapacidad` varchar(5) DEFAULT NULL,
  `id_mikrotik` int(11) DEFAULT NULL,
  `ciudad` varchar(25) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `id_cliente` (`id_cliente`),
  KEY `id_usuario` (`id_usuario`),
  KEY `id_mikrotik` (`id_mikrotik`),
  CONSTRAINT `contratos_ibfk_1` FOREIGN KEY (`id_usuario`) REFERENCES `usuarios` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `contratos_ibfk_2` FOREIGN KEY (`id_cliente`) REFERENCES `clientes` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=1510 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_swedish_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `cotizaciones` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `productos` longtext NOT NULL,
  `total` decimal(10,2) NOT NULL,
  `fecha` date NOT NULL,
  `hora` time NOT NULL,
  `metodo` varchar(20) NOT NULL,
  `validez` varchar(30) NOT NULL,
  `descuento` decimal(10,2) NOT NULL DEFAULT 0.00,
  `id_cliente` int(11) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `id_cliente` (`id_cliente`),
  CONSTRAINT `cotizaciones_ibfk_1` FOREIGN KEY (`id_cliente`) REFERENCES `clientes` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=24 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_swedish_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `creditos` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `monto` decimal(10,2) NOT NULL,
  `fecha` date NOT NULL,
  `hora` time NOT NULL,
  `estado` int(11) NOT NULL DEFAULT 1,
  `id_venta` int(11) DEFAULT NULL,
  `id_electronica` int(11) DEFAULT NULL,
  `id_orden_venta` int(11) DEFAULT NULL,
  `id_contrato` int(11) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `id_venta` (`id_venta`),
  KEY `id_electronica` (`id_electronica`),
  KEY `id_orden_venta` (`id_orden_venta`),
  KEY `idx_id_electronica` (`id_electronica`),
  KEY `idx_estado` (`estado`),
  CONSTRAINT `creditos_ibfk_1` FOREIGN KEY (`id_venta`) REFERENCES `ventas` (`id`),
  CONSTRAINT `creditos_ibfk_2` FOREIGN KEY (`id_electronica`) REFERENCES `datos_cabecera_electronica` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `creditos_ibfk_3` FOREIGN KEY (`id_orden_venta`) REFERENCES `orden_venta` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=26561 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_swedish_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `datos_cabecera_electronica` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `fecha` date DEFAULT NULL,
  `orden_no` int(11) DEFAULT NULL,
  `cliente` varchar(75) DEFAULT NULL,
  `direccion` varchar(250) DEFAULT NULL,
  `telefono` varchar(10) DEFAULT NULL,
  `ruc` varchar(20) DEFAULT NULL,
  `tipo_identificacion` int(11) DEFAULT NULL,
  `correo` varchar(75) DEFAULT NULL,
  `establecimiento` varchar(45) DEFAULT NULL,
  `punto_emi` varchar(45) DEFAULT NULL,
  `ruc_empresa` varchar(45) DEFAULT NULL,
  `ambiente` varchar(45) DEFAULT NULL,
  `razon_social` varchar(75) DEFAULT NULL,
  `nombre_comercial` varchar(75) DEFAULT NULL,
  `secuencial` varchar(25) DEFAULT NULL,
  `direccion_matriz` varchar(255) DEFAULT NULL,
  `obligado` varchar(2) DEFAULT NULL,
  `estado` int(11) NOT NULL DEFAULT 1,
  `totaldescuento` decimal(10,2) DEFAULT NULL,
  `totalfactura` decimal(10,2) DEFAULT NULL,
  `claveacceso` varchar(75) DEFAULT NULL,
  `tipopago` varchar(25) DEFAULT NULL,
  `metodo` varchar(15) NOT NULL,
  `apertura` int(11) NOT NULL DEFAULT 1,
  `id_usuario` int(11) NOT NULL,
  `id_cliente` int(11) DEFAULT NULL,
  `estado_proceso` tinyint(4) DEFAULT 0 COMMENT '0=pendiente,1=procesado,2=error',
  `sri_enviado` tinyint(4) DEFAULT 0 COMMENT '0=no enviado,1=autorizado,2=rechazado',
  `correo_enviado` tinyint(4) DEFAULT 0 COMMENT '0=no enviado,1=enviado',
  `intentos_sri` int(11) DEFAULT 0,
  `mensaje_sri` text DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `idx_cliente` (`cliente`),
  KEY `idx_orden_no` (`orden_no`),
  KEY `idx_ruc` (`ruc`),
  FULLTEXT KEY `idx_fulltext_cliente_orden_no` (`cliente`),
  FULLTEXT KEY `ft_cliente_orden` (`cliente`,`secuencial`)
) ENGINE=InnoDB AUTO_INCREMENT=10279 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_swedish_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `detalle_apartado` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `monto` decimal(10,2) NOT NULL,
  `fecha` timestamp NOT NULL DEFAULT current_timestamp(),
  `apertura` int(11) NOT NULL DEFAULT 1,
  `id_apartado` int(11) NOT NULL,
  `id_usuario` int(11) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_swedish_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `detalle_factura_electronica` (
  `id_tabla` int(11) NOT NULL AUTO_INCREMENT,
  `orden_no` int(11) DEFAULT NULL,
  `cantidad` int(11) DEFAULT NULL,
  `item` varchar(250) DEFAULT NULL,
  `precio_u` decimal(19,4) DEFAULT NULL,
  `total` decimal(19,4) DEFAULT NULL,
  `iva` decimal(10,0) DEFAULT 0,
  `ice` decimal(10,0) DEFAULT 0,
  `irbpnr` decimal(10,0) DEFAULT 0,
  `codigo_ice` varchar(50) DEFAULT '3',
  `codigoPorcentaje_ice` varchar(50) DEFAULT '0',
  `baseImponible_ice` decimal(10,2) DEFAULT 0.00,
  `tarifa_ice` decimal(10,2) DEFAULT 0.00,
  `valor_ice` decimal(10,2) DEFAULT 0.00,
  `codigo_irbpnr` varchar(50) DEFAULT '5',
  `codigoPorcentaje_irbpnr` varchar(50) DEFAULT '0',
  `tarifa_irbpnr` decimal(10,2) DEFAULT 0.00,
  `baseImponible_irbpnr` decimal(10,2) DEFAULT 0.00,
  `valor_irbpnr` decimal(10,2) DEFAULT 0.00,
  `codproducto` varchar(30) DEFAULT NULL,
  `descuento` decimal(10,2) DEFAULT NULL,
  `precio_pvp` decimal(10,2) DEFAULT NULL,
  `por_descuento` decimal(10,2) DEFAULT NULL,
  `id_producto` int(11) DEFAULT NULL,
  PRIMARY KEY (`id_tabla`),
  KEY `orden_no` (`orden_no`),
  CONSTRAINT `detalle_factura_electronica_ibfk_1` FOREIGN KEY (`orden_no`) REFERENCES `datos_cabecera_electronica` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=10364 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_swedish_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `estado_corte` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `fecha` varchar(20) DEFAULT NULL,
  `fecha_corte` varchar(25) DEFAULT NULL,
  `nombre` varchar(25) DEFAULT NULL,
  `estado` int(11) DEFAULT 1,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=42 DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_swedish_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `gastos` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `monto` decimal(10,2) NOT NULL,
  `descripcion` varchar(255) NOT NULL,
  `foto` varchar(100) DEFAULT NULL,
  `fecha` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `apertura` int(11) NOT NULL DEFAULT 1,
  `id_usuario` int(11) NOT NULL,
  `id_caja` int(11) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `id_usuario` (`id_usuario`),
  KEY `id_caja` (`id_caja`),
  CONSTRAINT `gastos_ibfk_1` FOREIGN KEY (`id_usuario`) REFERENCES `usuarios` (`id`),
  CONSTRAINT `gastos_ibfk_2` FOREIGN KEY (`id_caja`) REFERENCES `cajas` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=100 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_swedish_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `grupo_trabajo` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `id_responsable` int(11) NOT NULL,
  `descripcion` varchar(255) DEFAULT NULL,
  `observacion` varchar(255) DEFAULT NULL,
  `estado` int(11) NOT NULL DEFAULT 1,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=6 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_swedish_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `inventario` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `movimiento` varchar(100) NOT NULL,
  `accion` varchar(20) NOT NULL,
  `cantidad` int(11) NOT NULL,
  `stock_actual` int(11) NOT NULL,
  `fecha` timestamp NOT NULL DEFAULT current_timestamp(),
  `id_producto` int(11) NOT NULL,
  `id_usuario` int(11) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `id_producto` (`id_producto`),
  KEY `id_usuario` (`id_usuario`)
) ENGINE=InnoDB AUTO_INCREMENT=30651 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_swedish_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `ip` (
  `id` bigint(20) NOT NULL AUTO_INCREMENT,
  `red` varchar(50) NOT NULL,
  `gateway` varchar(50) DEFAULT NULL,
  `final` text NOT NULL,
  `ultima` text NOT NULL,
  `estado` int(11) NOT NULL DEFAULT 1,
  `id_zona` bigint(20) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `id_zona` (`id_zona`) USING BTREE,
  CONSTRAINT `ip_ibfk_1` FOREIGN KEY (`id_zona`) REFERENCES `zonas` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=21 DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_swedish_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `ip_anuladas` (
  `id` bigint(20) NOT NULL AUTO_INCREMENT,
  `ip` text CHARACTER SET utf8mb4 COLLATE utf8mb4_swedish_ci NOT NULL,
  `id_zona` bigint(20) NOT NULL,
  `id_ip` bigint(20) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `id_zona` (`id_zona`),
  KEY `id_ip` (`id_ip`),
  CONSTRAINT `ip_anuladas_ibfk_1` FOREIGN KEY (`id_ip`) REFERENCES `ip` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `ip_anuladas_ibfk_2` FOREIGN KEY (`id_zona`) REFERENCES `zonas` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=229 DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_swedish_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `medidas` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `medida` varchar(100) NOT NULL,
  `nombre_corto` varchar(10) NOT NULL,
  `fecha` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `estado` int(11) NOT NULL DEFAULT 1,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_swedish_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `mes_facturar` (
  `id` bigint(20) NOT NULL AUTO_INCREMENT,
  `enero` int(11) DEFAULT NULL,
  `febrero` int(11) DEFAULT NULL,
  `marzo` int(11) DEFAULT NULL,
  `abril` int(11) DEFAULT NULL,
  `mayo` int(11) DEFAULT NULL,
  `junio` int(11) DEFAULT NULL,
  `julio` int(11) DEFAULT NULL,
  `agosto` int(11) DEFAULT NULL,
  `septiembre` int(11) DEFAULT NULL,
  `octubre` int(11) DEFAULT NULL,
  `noviembre` int(11) DEFAULT NULL,
  `diciembre` int(11) DEFAULT NULL,
  `id_contrato` bigint(20) NOT NULL,
  `estado` int(11) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `id_contrato` (`id_contrato`),
  CONSTRAINT `mes_facturar_ibfk_1` FOREIGN KEY (`id_contrato`) REFERENCES `contratos` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=1528 DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_swedish_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `mikrotik` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `nombre` varchar(100) NOT NULL,
  `ip` varchar(25) NOT NULL,
  `usuario` varchar(100) NOT NULL,
  `clave` text NOT NULL,
  `puerto` varchar(10) NOT NULL,
  `estado` int(11) NOT NULL DEFAULT 1,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=6 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_swedish_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `nota_credito_cabecera` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `orden_no` int(11) DEFAULT NULL,
  `fecha` date DEFAULT NULL,
  `fecha_factura` date NOT NULL,
  `serie_factura` varchar(30) NOT NULL,
  `cliente` varchar(100) DEFAULT NULL,
  `ruc_cliente` varchar(20) NOT NULL,
  `tipo_identificacion` int(11) NOT NULL,
  `id_cliente` int(11) DEFAULT NULL,
  `claveacceso` varchar(50) DEFAULT NULL,
  `claveacceso_factura` varchar(50) DEFAULT NULL,
  `motivo` varchar(255) DEFAULT NULL,
  `establecimiento` varchar(10) NOT NULL,
  `punto_emi` varchar(10) NOT NULL,
  `secuencial` varchar(20) NOT NULL,
  `obligado` varchar(5) NOT NULL,
  `ambiente` int(11) NOT NULL,
  `total_modificar` decimal(10,2) DEFAULT NULL,
  `total_descuento` decimal(10,2) NOT NULL,
  `estado` int(11) DEFAULT 1,
  `id_usuario` int(11) NOT NULL,
  `id_empresa` bigint(20) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `id_empresa` (`id_empresa`) USING BTREE,
  KEY `id_cliente` (`id_cliente`) USING BTREE,
  KEY `id_usuario` (`id_usuario`),
  CONSTRAINT `nota_credito_cabecera_ibfk_1` FOREIGN KEY (`id_cliente`) REFERENCES `clientes` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=1276 DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `nota_credito_detalle` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `orden_no` int(11) DEFAULT NULL,
  `cantidad` int(11) DEFAULT NULL,
  `descripcion` text DEFAULT NULL,
  `precio_u` decimal(10,4) DEFAULT NULL,
  `precio_pvp` decimal(10,4) NOT NULL,
  `total` decimal(10,4) DEFAULT NULL,
  `iva` int(11) DEFAULT NULL,
  `codproducto` varchar(50) DEFAULT NULL,
  `descuento` decimal(10,2) DEFAULT NULL,
  `id_producto` int(11) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `orden_no` (`orden_no`),
  KEY `id_producto` (`id_producto`)
) ENGINE=InnoDB AUTO_INCREMENT=1286 DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `orden_venta` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `productos` longtext NOT NULL,
  `total` decimal(10,2) NOT NULL,
  `fecha` date NOT NULL,
  `hora` time NOT NULL,
  `metodo` varchar(15) NOT NULL,
  `descuento` decimal(10,2) NOT NULL,
  `serie` varchar(20) NOT NULL,
  `estado` int(11) NOT NULL DEFAULT 1,
  `apertura` int(11) NOT NULL DEFAULT 1,
  `id_cliente` int(11) NOT NULL,
  `id_usuario` int(11) NOT NULL,
  `tipopago` varchar(25) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `id_cliente` (`id_cliente`),
  KEY `id_usuario` (`id_usuario`),
  CONSTRAINT `orden_venta_ibfk_1` FOREIGN KEY (`id_cliente`) REFERENCES `clientes` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `orden_venta_ibfk_2` FOREIGN KEY (`id_usuario`) REFERENCES `usuarios` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=16791 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_swedish_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `productos` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `codigo` varchar(30) NOT NULL,
  `descripcion` mediumtext NOT NULL,
  `precio_compra` decimal(10,2) DEFAULT NULL,
  `precio_venta` decimal(10,2) NOT NULL,
  `cantidad` int(11) NOT NULL DEFAULT 0,
  `foto` varchar(100) DEFAULT NULL,
  `iva` varchar(5) NOT NULL,
  `estado` int(11) NOT NULL DEFAULT 1,
  `fecha` timestamp NOT NULL DEFAULT current_timestamp(),
  `ventas` int(11) NOT NULL DEFAULT 0,
  `id_medida` int(11) DEFAULT NULL,
  `id_categoria` int(11) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `id_categoria` (`id_categoria`),
  KEY `id_medida` (`id_medida`),
  CONSTRAINT `productos_ibfk_1` FOREIGN KEY (`id_categoria`) REFERENCES `categorias` (`id`),
  CONSTRAINT `productos_ibfk_2` FOREIGN KEY (`id_medida`) REFERENCES `medidas` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=33 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_swedish_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `proveedor` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `ruc` varchar(15) NOT NULL,
  `nombre` varchar(255) NOT NULL,
  `telefono` varchar(50) NOT NULL,
  `correo` varchar(100) NOT NULL,
  `direccion` varchar(255) NOT NULL,
  `fecha` timestamp NOT NULL DEFAULT current_timestamp(),
  `estado` int(11) NOT NULL DEFAULT 1,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=41 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_swedish_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `repetidoras` (
  `id` bigint(20) NOT NULL AUTO_INCREMENT,
  `marca` text DEFAULT NULL,
  `ssid` text DEFAULT NULL,
  `ip` text DEFAULT NULL,
  `canal` text DEFAULT NULL,
  `seguridad` text DEFAULT NULL,
  `frecuencia` text DEFAULT NULL,
  `estado` int(11) NOT NULL DEFAULT 1,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=154 DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `respuesta_sri` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `id_comprobante` int(11) DEFAULT NULL,
  `claveAcceso` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_swedish_ci DEFAULT NULL,
  `validarComprobante` text DEFAULT NULL,
  `autorizarComprobante` text CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `estado` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_swedish_ci DEFAULT NULL,
  `updatedAt` datetime DEFAULT NULL,
  `createdAt` datetime NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `claveAcceso` (`claveAcceso`)
) ENGINE=InnoDB AUTO_INCREMENT=11748 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `retencion` (
  `id` bigint(20) NOT NULL AUTO_INCREMENT,
  `fecha` date DEFAULT NULL,
  `cliente` varchar(75) DEFAULT NULL,
  `direccion` varchar(255) DEFAULT NULL,
  `telefono` varchar(10) DEFAULT NULL,
  `ruc` varchar(15) DEFAULT NULL,
  `tipo_identificacion` int(11) DEFAULT NULL,
  `correo` varchar(75) DEFAULT NULL,
  `establecimiento` varchar(45) DEFAULT NULL,
  `punto_emi` varchar(45) DEFAULT NULL,
  `ruc_empresa` varchar(15) DEFAULT NULL,
  `ambiente` varchar(5) NOT NULL,
  `razon_social` varchar(75) DEFAULT NULL,
  `nombre_comercial` varchar(75) DEFAULT NULL,
  `tipoEmision` varchar(100) NOT NULL,
  `secuencial` varchar(9) NOT NULL,
  `direccion_matriz` varchar(255) DEFAULT NULL,
  `obligado` varchar(5) DEFAULT NULL,
  `fechaEmision` varchar(25) DEFAULT NULL,
  `numFactura` varchar(25) DEFAULT NULL,
  `periodoFiscal` varchar(200) NOT NULL,
  `totalFactura` decimal(10,2) DEFAULT NULL,
  `claveAcceso` varchar(49) DEFAULT NULL,
  `estado` varchar(100) NOT NULL DEFAULT '1',
  `id_usuario` int(11) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=250 DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `retencion_detalle` (
  `id` bigint(20) NOT NULL AUTO_INCREMENT,
  `base_imponible` decimal(10,2) DEFAULT NULL,
  `tipo_impuesto` varchar(25) DEFAULT NULL,
  `porcentaje_retencion` decimal(10,2) DEFAULT NULL,
  `valor_retenido` decimal(10,2) DEFAULT NULL,
  `codigo_retencion` varchar(15) DEFAULT NULL,
  `id_retencion` bigint(20) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `id_retencion` (`id_retencion`),
  CONSTRAINT `retencion_detalle_ibfk_1` FOREIGN KEY (`id_retencion`) REFERENCES `retencion` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=422 DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `tipo_comprobante` (
  `id_comprobante` int(11) NOT NULL AUTO_INCREMENT,
  `valor` varchar(50) DEFAULT NULL,
  PRIMARY KEY (`id_comprobante`)
) ENGINE=InnoDB AUTO_INCREMENT=8 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_swedish_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `tipo_pago` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `nombre` text DEFAULT NULL,
  `estado` int(11) DEFAULT 1,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=8 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `usuarios` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `nombre` varchar(100) NOT NULL,
  `apellido` varchar(100) NOT NULL,
  `correo` varchar(100) NOT NULL,
  `telefono` varchar(10) DEFAULT NULL,
  `direccion` varchar(255) DEFAULT NULL,
  `perfil` varchar(100) DEFAULT NULL,
  `clave` varchar(200) NOT NULL,
  `token` varchar(100) DEFAULT NULL,
  `fecha` timestamp NOT NULL DEFAULT current_timestamp(),
  `estado` int(11) NOT NULL DEFAULT 1,
  `rol` int(11) NOT NULL,
  `vinculo` varchar(25) DEFAULT 'DISPONIBLE',
  `id_grupo_trabajo` int(11) DEFAULT 1,
  PRIMARY KEY (`id`),
  KEY `id_grupo_trabajo` (`id_grupo_trabajo`),
  CONSTRAINT `usuarios_ibfk_1` FOREIGN KEY (`id_grupo_trabajo`) REFERENCES `grupo_trabajo` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=10 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_swedish_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `ventas` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `productos` longtext NOT NULL,
  `total` decimal(10,2) NOT NULL,
  `fecha` date NOT NULL,
  `hora` time NOT NULL,
  `metodo` varchar(15) NOT NULL,
  `descuento` decimal(10,2) NOT NULL DEFAULT 0.00,
  `serie` varchar(20) NOT NULL,
  `estado` int(11) NOT NULL DEFAULT 1,
  `apertura` int(11) NOT NULL DEFAULT 1,
  `id_cliente` int(11) NOT NULL,
  `id_usuario` int(11) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `id_cliente` (`id_cliente`),
  KEY `id_usuario` (`id_usuario`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_swedish_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `zonas` (
  `id` bigint(20) NOT NULL AUTO_INCREMENT,
  `descripcion` varchar(255) NOT NULL,
  `observacion` varchar(255) DEFAULT NULL,
  `estado` int(11) NOT NULL DEFAULT 1,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=21 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_swedish_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40103 SET TIME_ZONE=@OLD_TIME_ZONE */;

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
/*M!100616 SET NOTE_VERBOSITY=@OLD_NOTE_VERBOSITY */;


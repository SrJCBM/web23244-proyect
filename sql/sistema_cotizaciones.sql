-- phpMyAdmin SQL Dump
-- version 4.9.1
-- https://www.phpmyadmin.net/
--
-- Host: localhost
-- Generation Time: Aug 12, 2025 at 12:11 AM
-- Server version: 8.0.17
-- PHP Version: 7.3.10

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET AUTOCOMMIT = 0;
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `sistema_cotizaciones`
--
CREATE DATABASE IF NOT EXISTS `sistema_cotizaciones` DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci;
USE `sistema_cotizaciones`;
-- --------------------------------------------------------

--
-- Table structure for table `auditoria`
--

CREATE TABLE `auditoria` (
  `id_audit` bigint(20) UNSIGNED NOT NULL,
  `fecha` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `actor_id` int(11) DEFAULT NULL,
  `accion` varchar(32) NOT NULL,
  `entidad` varchar(32) NOT NULL,
  `entidad_id` bigint(20) DEFAULT NULL,
  `detalle` json DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- --------------------------------------------------------

--
-- Table structure for table `aud_accesos`
--

CREATE TABLE `aud_accesos` (
  `id_acceso` bigint(20) UNSIGNED NOT NULL,
  `id_usuario` int(11) DEFAULT NULL,
  `username_intentado` varchar(150) DEFAULT NULL,
  `ip` varchar(45) NOT NULL,
  `user_agent` varchar(255) NOT NULL,
  `inicio` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `fin` datetime DEFAULT NULL,
  `exito` tinyint(1) NOT NULL,
  `motivo` varchar(50) DEFAULT NULL,
  `sesion_id` varchar(128) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `aud_accesos`
--

INSERT INTO `aud_accesos` (`id_acceso`, `id_usuario`, `username_intentado`, `ip`, `user_agent`, `inicio`, `fin`, `exito`, `motivo`, `sesion_id`) VALUES
(1, NULL, 'root', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/139.0.0.0 Safari/537.36', '2025-08-11 19:01:03', NULL, 0, 'usuario_no_encontrado', '6318s41sui7ne03v4g4n850uo8'),
(2, 6, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/139.0.0.0 Safari/537.36', '2025-08-11 19:01:07', '2025-08-11 19:08:03', 1, 'logout', 'mso01jitk61mnl96lrkpds106l'),
(3, 1, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/139.0.0.0 Safari/537.36', '2025-08-11 19:08:09', '2025-08-11 19:08:16', 1, 'logout', 'lrsicsck5d7us88qe2pq5vv9g3'),
(4, 6, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/139.0.0.0 Safari/537.36', '2025-08-11 19:08:20', NULL, 1, 'login', 'skhli141f5eitfgbom1qeedi46');

-- --------------------------------------------------------

--
-- Table structure for table `clientes`
--

CREATE TABLE `clientes` (
  `id_cliente` int(11) NOT NULL,
  `nombre_comercial` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `persona_contacto` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `correo` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `telefono` varchar(20) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `direccion` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `categoria` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `estado` enum('activo','inactivo') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT 'activo',
  `id_usuario` int(11) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `clientes`
--

INSERT INTO `clientes` (`id_cliente`, `nombre_comercial`, `persona_contacto`, `correo`, `telefono`, `direccion`, `categoria`, `estado`, `id_usuario`, `created_at`) VALUES
(1, 'ElectroHome S.A.', 'Carlos Pérez', 'cliente1@gmail.com', '0988776655', 'Av. Naciones Unidas, Quito', 'distribuidor', 'activo', 4, '2025-08-04 17:32:14'),
(2, 'Muebles Modernos', 'Andrea López', 'cliente2@gmail.com', '0999988877', 'Av. Shyris, Quito', 'retail', 'activo', 5, '2025-08-04 17:32:14');

-- --------------------------------------------------------

--
-- Table structure for table `cotizaciones`
--

CREATE TABLE `cotizaciones` (
  `id_cotizacion` int(11) NOT NULL,
  `id_usuario` int(11) DEFAULT NULL,
  `id_cliente` int(11) DEFAULT NULL,
  `fecha_emision` datetime DEFAULT CURRENT_TIMESTAMP,
  `total` decimal(10,2) DEFAULT NULL,
  `estado` enum('activa','aceptada','rechazada','cancelada') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'activa',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `cotizaciones`
--

INSERT INTO `cotizaciones` (`id_cotizacion`, `id_usuario`, `id_cliente`, `fecha_emision`, `total`, `estado`, `created_at`) VALUES
(1, 4, 1, '2025-08-04 00:00:00', '1169.99', 'activa', '2025-08-04 05:00:00'),
(2, 5, 2, '2025-08-04 00:00:00', '140.00', 'activa', '2025-08-04 05:00:00');

-- --------------------------------------------------------

--
-- Table structure for table `detalle_cotizacion`
--

CREATE TABLE `detalle_cotizacion` (
  `id_detalle` int(11) NOT NULL,
  `id_cotizacion` int(11) NOT NULL,
  `id_producto` int(11) NOT NULL,
  `cantidad` int(11) NOT NULL,
  `precio_unitario` decimal(10,2) NOT NULL DEFAULT '0.00',
  `extras_json` json DEFAULT NULL,
  `extras_total` decimal(10,2) NOT NULL DEFAULT '0.00',
  `subtotal` decimal(10,2) NOT NULL DEFAULT '0.00'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `empresas_proveedoras`
--

CREATE TABLE `empresas_proveedoras` (
  `id_empresa` int(11) NOT NULL,
  `nombre` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `ruc` varchar(20) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `direccion` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `correo_contacto` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `telefono` varchar(20) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `estado` enum('activa','inactiva') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT 'activa',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `id_usuario` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `empresas_proveedoras`
--

INSERT INTO `empresas_proveedoras` (`id_empresa`, `nombre`, `ruc`, `direccion`, `correo_contacto`, `telefono`, `estado`, `created_at`, `id_usuario`) VALUES
(1, 'ElectroQuito S.A.', '1790011223001', 'Av. Amazonas y Colón, Quito', 'ventas@electroquito.ec', '0998877665', 'activa', '2025-07-31 17:07:27', 2),
(2, 'HogarTech S.A.', '1790055544002', 'Av. 6 de Diciembre y Orellana, Quito', 'info@hogartech.com', '0981122334', 'activa', '2025-08-04 17:32:14', 3);

-- --------------------------------------------------------

--
-- Table structure for table `productos`
--

CREATE TABLE `productos` (
  `id_producto` int(11) NOT NULL,
  `nombre` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `descripcion` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `caracteristicas` json DEFAULT NULL,
  `marca` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `modelo` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `precio_base` decimal(10,2) DEFAULT NULL,
  `stock` int(11) DEFAULT NULL,
  `id_empresa` int(11) DEFAULT NULL,
  `estado` enum('activo','inactivo') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT 'activo',
  `fecha_creacion` date DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `productos`
--

INSERT INTO `productos` (`id_producto`, `nombre`, `descripcion`, `caracteristicas`, `marca`, `modelo`, `precio_base`, `stock`, `id_empresa`, `estado`, `fecha_creacion`, `created_at`) VALUES
(1, 'Cocina Indurama 4 quemadores', 'Cocina a gas con encendido eléctrico y horno amplio', '[{\"tipo\": \"\", \"nombre\": \"Resistencia\", \"precio\": 0, \"descripcion\": \"Genera calor\"}]', 'Indurama', 'CI-4000', '380.00', 12, 1, 'activo', '2025-07-31', '2025-07-31 17:09:04'),
(2, 'Refrigeradora Haceb 300L', 'Tecnología No Frost, eficiencia energética A+', '[{\"tipo\": \"\", \"nombre\": \"Placa electrónica\", \"precio\": 0, \"descripcion\": \"Circuito de control del sistema\"}, {\"tipo\": \"\", \"nombre\": \"Termostato\", \"precio\": 0, \"descripcion\": \"Regula la temperatura\"}]', 'Haceb', 'RFH300', '720.00', 6, 1, 'activo', '2025-07-31', '2025-07-31 17:09:04'),
(3, 'Licuadora Oster Clásica', 'Motor potente con vaso de vidrio resistente', '[{\"tipo\": \"\", \"nombre\": \"Motor\", \"precio\": 0, \"descripcion\": \"Genera potencia mecánica\"}, {\"tipo\": \"\", \"nombre\": \"Tapa de vidrio\", \"precio\": 0, \"descripcion\": \"Cubre el vaso\"}]', 'Oster', '465-42R', '89.99', 20, 1, 'activo', '2025-07-31', '2025-07-31 17:09:04'),
(4, 'Microondas LG MH6535', 'Smart Inverter, 1000W', '[]', 'LG', 'MH6535', '140.00', 15, 2, 'activo', '2025-07-31', '2025-08-04 17:32:14'),
(5, 'Lavadora Samsung WA13', 'Carga superior, 13kg', '[]', 'Samsung', 'WA13T5260', '450.00', 8, 2, 'activo', '2025-07-31', '2025-08-04 17:32:14'),
(6, 'Televisor Sony Bravia 55\"', 'Pantalla 4K UHD, HDR, Smart TV con Android TV', '[{\"nombre\": \"Control remoto\", \"precio\": 0}, {\"nombre\": \"Base metálica\", \"precio\": 0}, {\"nombre\": \"Cable HDMI\", \"precio\": 0}]', 'Sony', 'XBR55X805H', '750.00', 10, 2, 'activo', '2025-08-09', '2025-08-09 16:07:19'),
(7, 'Refrigerador LG InstaView', 'Refrigerador de dos puertas con panel InstaView, dispensador de agua y sistema de enfriamiento lineal.', '[{\"nombre\": \"Control de temperatura\", \"precio\": 0}, {\"nombre\": \"Dispensador de hielo\", \"precio\": 0}, {\"nombre\": \"Luz LED interior\", \"precio\": 0}]', 'LG', 'GR-X257CSAV', '1299.99', 5, 2, 'activo', '2025-08-09', '2025-08-09 16:56:02'),
(8, 'Refrigerador Samsung No Frost 320L', 'Refrigerador de dos puertas, sistema No Frost, eficiencia energética A+, bajo consumo y compartimentos ajustables.', '[{\"nombre\": \"Control de temperatura digital\", \"precio\": 0}, {\"nombre\": \"Luz LED interior\", \"precio\": 0}, {\"nombre\": \"Bandejas de vidrio templado\", \"precio\": 0}]', 'Samsung', 'RT32K503JS8/PE', '559.99', 15, 2, 'activo', '2025-08-09', '2025-08-09 17:17:59');

-- --------------------------------------------------------

--
-- Table structure for table `roles`
--

CREATE TABLE `roles` (
  `id_rol` int(11) NOT NULL,
  `nombre_rol` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `descripcion` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `roles`
--

INSERT INTO `roles` (`id_rol`, `nombre_rol`, `descripcion`) VALUES
(1, 'Administrador General', 'Acceso completo'),
(2, 'Vendedor / Cotizador', 'Genera y gestiona proformas'),
(3, 'Cliente', 'Cliente registrado'),
(4, 'Auditor', 'Visualiza historial y logs'),
(5, 'Supervisor de Productos y Proveedores', 'Gestiona catálogo y proveedores'),
(6, 'Analista Comercial', 'Consulta reportes y estadísticas');

-- --------------------------------------------------------

--
-- Table structure for table `usuarios`
--

CREATE TABLE `usuarios` (
  `id_usuario` int(11) NOT NULL,
  `nombre_completo` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `correo` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `contraseña` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `nickname` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `fecha_nacimiento` date DEFAULT NULL,
  `id_rol` int(11) DEFAULT NULL,
  `estado` enum('activo','inactivo') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT 'activo',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `usuarios`
--

INSERT INTO `usuarios` (`id_usuario`, `nombre_completo`, `correo`, `contraseña`, `nickname`, `fecha_nacimiento`, `id_rol`, `estado`, `created_at`) VALUES
(1, 'Administrador General', 'admin@sistema.com', '$2y$10$RDUqPX.onV.oMNnbUkztDudGCjzcNCoktvL60OsetTsWwOY9eZSS6', 'admin', '1990-01-01', 1, 'activo', '2025-08-01 02:45:35'),
(2, 'Proveedor ElectroQuito', 'ventas@electroquito.ec', '$2y$10$tlribCGXCamL.JLLEt18kO/D/cTEki8FWcMf26ZI0zz4ngMECcDy2', 'electro', '1985-05-10', 2, 'activo', '2025-08-04 17:32:14'),
(3, 'Proveedor HogarTech', 'info@hogartech.com', '$2y$10$tlribCGXCamL.JLLEt18kO/D/cTEki8FWcMf26ZI0zz4ngMECcDy2', 'hogar', '1982-03-08', 2, 'activo', '2025-08-04 17:32:14'),
(4, 'Cliente Uno', 'cliente1@gmail.com', '$2y$10$RbhJoeFsRgvsRCFj7zVDm.oQqZrRGkS42amlMjzpeir3Ua9g.tPs.', 'cliente1', '2000-07-01', 3, 'activo', '2025-08-04 17:32:14'),
(5, 'Cliente Dos', 'cliente2@gmail.com', '$2y$10$RbhJoeFsRgvsRCFj7zVDm.oQqZrRGkS42amlMjzpeir3Ua9g.tPs.', 'cliente2', '1998-10-15', 3, 'activo', '2025-08-04 17:32:14'),
(6, ' Auditor Interno', 'auditor@sistema.com', '$2y$10$vFz3INcLEJbtwb7xo6ZMQe3JMFqgMV/igdBGsn.YBEOVr38bnXHV.', 'auditor', '1980-06-10', 4, 'activo', '2025-08-10 20:43:11'),
(7, 'Supervisor Productos', 'supervisor@sistema.com', '$2y$10$QDBSl/uEO/AiH23Dtgq5SuIUxZCHmHQmYQaInd14QBNe6tk1xuhI2', 'supervisor', '1988-07-10', 5, 'activo', '2025-08-10 20:45:25'),
(8, 'Analista Comercial', 'analista@sistema.com', '$2y$10$rtFdK0..gZ2PEKPCjdliO.qmVfx5xpRxu1lLbxR7qbR3pr4xbKXPS', 'analista', '1995-12-05', 6, 'activo', '2025-08-10 20:47:01');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `auditoria`
--
ALTER TABLE `auditoria`
  ADD PRIMARY KEY (`id_audit`),
  ADD KEY `idx_entidad` (`entidad`,`entidad_id`),
  ADD KEY `idx_actor` (`actor_id`,`fecha`),
  ADD KEY `idx_auditoria_auth` (`entidad`,`accion`,`fecha`);

--
-- Indexes for table `aud_accesos`
--
ALTER TABLE `aud_accesos`
  ADD PRIMARY KEY (`id_acceso`),
  ADD KEY `idx_usuario_inicio` (`id_usuario`,`inicio`),
  ADD KEY `idx_sesion` (`sesion_id`),
  ADD KEY `idx_exito_inicio` (`exito`,`inicio`);

--
-- Indexes for table `clientes`
--
ALTER TABLE `clientes`
  ADD PRIMARY KEY (`id_cliente`),
  ADD KEY `id_usuario` (`id_usuario`);

--
-- Indexes for table `cotizaciones`
--
ALTER TABLE `cotizaciones`
  ADD PRIMARY KEY (`id_cotizacion`),
  ADD KEY `id_usuario` (`id_usuario`),
  ADD KEY `id_cliente` (`id_cliente`);

--
-- Indexes for table `detalle_cotizacion`
--
ALTER TABLE `detalle_cotizacion`
  ADD PRIMARY KEY (`id_detalle`),
  ADD KEY `id_producto` (`id_producto`),
  ADD KEY `idx_detalle_cot` (`id_cotizacion`),
  ADD KEY `idx_detalle_prod` (`id_producto`);

--
-- Indexes for table `empresas_proveedoras`
--
ALTER TABLE `empresas_proveedoras`
  ADD PRIMARY KEY (`id_empresa`),
  ADD UNIQUE KEY `id_usuario` (`id_usuario`);

--
-- Indexes for table `productos`
--
ALTER TABLE `productos`
  ADD PRIMARY KEY (`id_producto`),
  ADD KEY `id_empresa` (`id_empresa`);

--
-- Indexes for table `roles`
--
ALTER TABLE `roles`
  ADD PRIMARY KEY (`id_rol`);

--
-- Indexes for table `usuarios`
--
ALTER TABLE `usuarios`
  ADD PRIMARY KEY (`id_usuario`),
  ADD UNIQUE KEY `correo` (`correo`),
  ADD UNIQUE KEY `nickname` (`nickname`),
  ADD KEY `id_rol` (`id_rol`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `auditoria`
--
ALTER TABLE `auditoria`
  MODIFY `id_audit` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `aud_accesos`
--
ALTER TABLE `aud_accesos`
  MODIFY `id_acceso` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `clientes`
--
ALTER TABLE `clientes`
  MODIFY `id_cliente` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `cotizaciones`
--
ALTER TABLE `cotizaciones`
  MODIFY `id_cotizacion` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `detalle_cotizacion`
--
ALTER TABLE `detalle_cotizacion`
  MODIFY `id_detalle` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `empresas_proveedoras`
--
ALTER TABLE `empresas_proveedoras`
  MODIFY `id_empresa` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `productos`
--
ALTER TABLE `productos`
  MODIFY `id_producto` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- AUTO_INCREMENT for table `roles`
--
ALTER TABLE `roles`
  MODIFY `id_rol` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT for table `usuarios`
--
ALTER TABLE `usuarios`
  MODIFY `id_usuario` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `aud_accesos`
--
ALTER TABLE `aud_accesos`
  ADD CONSTRAINT `fk_accesos_usuario` FOREIGN KEY (`id_usuario`) REFERENCES `usuarios` (`id_usuario`) ON DELETE SET NULL ON UPDATE CASCADE;

--
-- Constraints for table `clientes`
--
ALTER TABLE `clientes`
  ADD CONSTRAINT `clientes_ibfk_1` FOREIGN KEY (`id_usuario`) REFERENCES `usuarios` (`id_usuario`);

--
-- Constraints for table `cotizaciones`
--
ALTER TABLE `cotizaciones`
  ADD CONSTRAINT `cotizaciones_ibfk_1` FOREIGN KEY (`id_usuario`) REFERENCES `usuarios` (`id_usuario`),
  ADD CONSTRAINT `cotizaciones_ibfk_2` FOREIGN KEY (`id_cliente`) REFERENCES `clientes` (`id_cliente`);

--
-- Constraints for table `detalle_cotizacion`
--
ALTER TABLE `detalle_cotizacion`
  ADD CONSTRAINT `fk_detalle_cotizacion` FOREIGN KEY (`id_cotizacion`) REFERENCES `cotizaciones` (`id_cotizacion`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_detalle_producto` FOREIGN KEY (`id_producto`) REFERENCES `productos` (`id_producto`) ON DELETE RESTRICT;

--
-- Constraints for table `empresas_proveedoras`
--
ALTER TABLE `empresas_proveedoras`
  ADD CONSTRAINT `fk_empresa_usuario` FOREIGN KEY (`id_usuario`) REFERENCES `usuarios` (`id_usuario`) ON DELETE CASCADE;

--
-- Constraints for table `productos`
--
ALTER TABLE `productos`
  ADD CONSTRAINT `productos_ibfk_1` FOREIGN KEY (`id_empresa`) REFERENCES `empresas_proveedoras` (`id_empresa`);

--
-- Constraints for table `usuarios`
--
ALTER TABLE `usuarios`
  ADD CONSTRAINT `usuarios_ibfk_1` FOREIGN KEY (`id_rol`) REFERENCES `roles` (`id_rol`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;

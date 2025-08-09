-- phpMyAdmin SQL Dump
-- version 4.9.1
-- https://www.phpmyadmin.net/
--
-- Servidor: localhost
-- Tiempo de generación: 09-08-2025 a las 17:53:31
-- Versión del servidor: 8.0.17
-- Versión de PHP: 7.3.10

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET AUTOCOMMIT = 0;
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Base de datos: `sistema_cotizaciones`
--
CREATE DATABASE IF NOT EXISTS `sistema_cotizaciones` DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci;
USE `sistema_cotizaciones`;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `clientes`
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
-- Volcado de datos para la tabla `clientes`
--

INSERT INTO `clientes` (`id_cliente`, `nombre_comercial`, `persona_contacto`, `correo`, `telefono`, `direccion`, `categoria`, `estado`, `id_usuario`, `created_at`) VALUES
(1, 'ElectroHome S.A.', 'Carlos Pérez', 'cliente1@gmail.com', '0988776655', 'Av. Naciones Unidas, Quito', 'distribuidor', 'activo', 4, '2025-08-04 17:32:14'),
(2, 'Muebles Modernos', 'Andrea López', 'cliente2@gmail.com', '0999988877', 'Av. Shyris, Quito', 'retail', 'activo', 5, '2025-08-04 17:32:14');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `cotizaciones`
--

CREATE TABLE `cotizaciones` (
  `id_cotizacion` int(11) NOT NULL,
  `id_usuario` int(11) DEFAULT NULL,
  `id_cliente` int(11) DEFAULT NULL,
  `fecha_emision` datetime DEFAULT CURRENT_TIMESTAMP,
  `total` decimal(10,2) DEFAULT NULL,
  `estado` enum('activa','aceptada','rechazada','cancelada') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'activa',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `cotizaciones`
--

INSERT INTO `cotizaciones` (`id_cotizacion`, `id_usuario`, `id_cliente`, `fecha_emision`, `total`, `estado`, `created_at`) VALUES
(1, 4, 1, '2025-08-04 00:00:00', '1169.99', 'activa', '2025-08-04 05:00:00'),
(2, 5, 2, '2025-08-04 00:00:00', '140.00', 'activa', '2025-08-04 05:00:00');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `detalle_cotizacion`
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
-- Estructura de tabla para la tabla `empresas_proveedoras`
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
-- Volcado de datos para la tabla `empresas_proveedoras`
--

INSERT INTO `empresas_proveedoras` (`id_empresa`, `nombre`, `ruc`, `direccion`, `correo_contacto`, `telefono`, `estado`, `created_at`, `id_usuario`) VALUES
(1, 'ElectroQuito S.A.', '1790011223001', 'Av. Amazonas y Colón, Quito', 'ventas@electroquito.ec', '0998877665', 'activa', '2025-07-31 17:07:27', 2),
(2, 'HogarTech S.A.', '1790055544002', 'Av. 6 de Diciembre y Orellana, Quito', 'info@hogartech.com', '0981122334', 'activa', '2025-08-04 17:32:14', 3);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `productos`
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
-- Volcado de datos para la tabla `productos`
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
-- Estructura de tabla para la tabla `roles`
--

CREATE TABLE `roles` (
  `id_rol` int(11) NOT NULL,
  `nombre_rol` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `descripcion` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `roles`
--

INSERT INTO `roles` (`id_rol`, `nombre_rol`, `descripcion`) VALUES
(1, 'administrador', 'Acceso completo'),
(2, 'proveedor', 'Gestiona productos'),
(3, 'cliente', 'Cliente registrado');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `usuarios`
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
-- Volcado de datos para la tabla `usuarios`
--

INSERT INTO `usuarios` (`id_usuario`, `nombre_completo`, `correo`, `contraseña`, `nickname`, `fecha_nacimiento`, `id_rol`, `estado`, `created_at`) VALUES
(1, 'Administrador General', 'admin@sistema.com', '$2y$10$RDUqPX.onV.oMNnbUkztDudGCjzcNCoktvL60OsetTsWwOY9eZSS6', 'admin', '1990-01-01', 1, 'activo', '2025-08-01 02:45:35'),
(2, 'Proveedor ElectroQuito', 'ventas@electroquito.ec', '$2y$10$tlribCGXCamL.JLLEt18kO/D/cTEki8FWcMf26ZI0zz4ngMECcDy2', 'electro', '1985-05-10', 2, 'activo', '2025-08-04 17:32:14'),
(3, 'Proveedor HogarTech', 'info@hogartech.com', '$2y$10$tlribCGXCamL.JLLEt18kO/D/cTEki8FWcMf26ZI0zz4ngMECcDy2', 'hogar', '1982-03-08', 2, 'activo', '2025-08-04 17:32:14'),
(4, 'Cliente Uno', 'cliente1@gmail.com', '$2y$10$RbhJoeFsRgvsRCFj7zVDm.oQqZrRGkS42amlMjzpeir3Ua9g.tPs.', 'cliente1', '2000-07-01', 3, 'activo', '2025-08-04 17:32:14'),
(5, 'Cliente Dos', 'cliente2@gmail.com', '$2y$10$RbhJoeFsRgvsRCFj7zVDm.oQqZrRGkS42amlMjzpeir3Ua9g.tPs.', 'cliente2', '1998-10-15', 3, 'activo', '2025-08-04 17:32:14');

--
-- Índices para tablas volcadas
--

--
-- Indices de la tabla `clientes`
--
ALTER TABLE `clientes`
  ADD PRIMARY KEY (`id_cliente`),
  ADD KEY `id_usuario` (`id_usuario`);

--
-- Indices de la tabla `cotizaciones`
--
ALTER TABLE `cotizaciones`
  ADD PRIMARY KEY (`id_cotizacion`),
  ADD KEY `id_usuario` (`id_usuario`),
  ADD KEY `id_cliente` (`id_cliente`);

--
-- Indices de la tabla `detalle_cotizacion`
--
ALTER TABLE `detalle_cotizacion`
  ADD PRIMARY KEY (`id_detalle`),
  ADD KEY `id_producto` (`id_producto`),
  ADD KEY `idx_detalle_cot` (`id_cotizacion`),
  ADD KEY `idx_detalle_prod` (`id_producto`);

--
-- Indices de la tabla `empresas_proveedoras`
--
ALTER TABLE `empresas_proveedoras`
  ADD PRIMARY KEY (`id_empresa`),
  ADD UNIQUE KEY `id_usuario` (`id_usuario`);

--
-- Indices de la tabla `productos`
--
ALTER TABLE `productos`
  ADD PRIMARY KEY (`id_producto`),
  ADD KEY `id_empresa` (`id_empresa`);

--
-- Indices de la tabla `roles`
--
ALTER TABLE `roles`
  ADD PRIMARY KEY (`id_rol`);

--
-- Indices de la tabla `usuarios`
--
ALTER TABLE `usuarios`
  ADD PRIMARY KEY (`id_usuario`),
  ADD UNIQUE KEY `correo` (`correo`),
  ADD UNIQUE KEY `nickname` (`nickname`),
  ADD KEY `id_rol` (`id_rol`);

--
-- AUTO_INCREMENT de las tablas volcadas
--

--
-- AUTO_INCREMENT de la tabla `clientes`
--
ALTER TABLE `clientes`
  MODIFY `id_cliente` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT de la tabla `cotizaciones`
--
ALTER TABLE `cotizaciones`
  MODIFY `id_cotizacion` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT de la tabla `detalle_cotizacion`
--
ALTER TABLE `detalle_cotizacion`
  MODIFY `id_detalle` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `empresas_proveedoras`
--
ALTER TABLE `empresas_proveedoras`
  MODIFY `id_empresa` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT de la tabla `productos`
--
ALTER TABLE `productos`
  MODIFY `id_producto` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- AUTO_INCREMENT de la tabla `roles`
--
ALTER TABLE `roles`
  MODIFY `id_rol` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT de la tabla `usuarios`
--
ALTER TABLE `usuarios`
  MODIFY `id_usuario` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- Restricciones para tablas volcadas
--

--
-- Filtros para la tabla `clientes`
--
ALTER TABLE `clientes`
  ADD CONSTRAINT `clientes_ibfk_1` FOREIGN KEY (`id_usuario`) REFERENCES `usuarios` (`id_usuario`);

--
-- Filtros para la tabla `cotizaciones`
--
ALTER TABLE `cotizaciones`
  ADD CONSTRAINT `cotizaciones_ibfk_1` FOREIGN KEY (`id_usuario`) REFERENCES `usuarios` (`id_usuario`),
  ADD CONSTRAINT `cotizaciones_ibfk_2` FOREIGN KEY (`id_cliente`) REFERENCES `clientes` (`id_cliente`);

--
-- Filtros para la tabla `detalle_cotizacion`
--
ALTER TABLE `detalle_cotizacion`
  ADD CONSTRAINT `fk_detalle_cotizacion` FOREIGN KEY (`id_cotizacion`) REFERENCES `cotizaciones` (`id_cotizacion`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_detalle_producto` FOREIGN KEY (`id_producto`) REFERENCES `productos` (`id_producto`) ON DELETE RESTRICT;

--
-- Filtros para la tabla `empresas_proveedoras`
--
ALTER TABLE `empresas_proveedoras`
  ADD CONSTRAINT `fk_empresa_usuario` FOREIGN KEY (`id_usuario`) REFERENCES `usuarios` (`id_usuario`) ON DELETE CASCADE;

--
-- Filtros para la tabla `productos`
--
ALTER TABLE `productos`
  ADD CONSTRAINT `productos_ibfk_1` FOREIGN KEY (`id_empresa`) REFERENCES `empresas_proveedoras` (`id_empresa`);

--
-- Filtros para la tabla `usuarios`
--
ALTER TABLE `usuarios`
  ADD CONSTRAINT `usuarios_ibfk_1` FOREIGN KEY (`id_rol`) REFERENCES `roles` (`id_rol`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;

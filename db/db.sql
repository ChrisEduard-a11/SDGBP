-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Servidor: 127.0.0.1
-- Tiempo de generación: 14-03-2026 a las 18:35:17
-- Versión del servidor: 10.4.32-MariaDB
-- Versión de PHP: 8.2.12

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Base de datos: `if0_38581055_sys_inv`
--

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `bienes`
--

CREATE TABLE `bienes` (
  `id` int(11) NOT NULL,
  `nombre` varchar(255) NOT NULL,
  `descripcion` text NOT NULL,
  `serial` varchar(20) DEFAULT NULL,
  `categoria_id` int(11) NOT NULL,
  `codigo` varchar(20) DEFAULT NULL,
  `fecha_adquisicion` date NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_spanish2_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `bitacora`
--

CREATE TABLE `bitacora` (
  `id` int(11) NOT NULL,
  `ip` varchar(100) DEFAULT NULL,
  `system_info` varchar(255) DEFAULT NULL,
  `fecha` datetime DEFAULT NULL,
  `accion` varchar(20) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_spanish2_ci;

--
-- Volcado de datos para la tabla `bitacora`
--

INSERT INTO `bitacora` (`id`, `ip`, `system_info`, `fecha`, `accion`) VALUES
(1, '127.0.0.1', 'Windows 10 - User: Administrador ', '2026-03-14 11:35:51', 'Inicio de Sesión'),
(2, '::1', 'Windows 10 - User: Administrador ', '2026-03-14 11:37:26', 'Inicio de Sesión'),
(3, '::1', 'Windows 10 - User: Administrador ', '2026-03-14 11:41:45', 'Aprobar Usuario'),
(4, '::1', 'Windows 10 - User: Administrador ', '2026-03-14 11:48:15', 'Eliminación Múltiple'),
(5, '::1', 'Windows 10 - User: Administrador ', '2026-03-14 11:48:33', 'Eliminación Múltiple'),
(6, '::1', 'Windows 10 - User: Administrador ', '2026-03-14 11:48:49', 'Eliminación Múltiple'),
(7, '::1', 'Windows 10 - User: Administrador ', '2026-03-14 11:49:05', 'Eliminación Múltiple'),
(8, '::1', 'Windows 10 - User: Administrador ', '2026-03-14 11:49:17', 'Eliminación Múltiple'),
(9, '::1', 'Windows 10 - User: Administrador ', '2026-03-14 11:49:58', 'Eliminación Múltiple'),
(10, '::1', 'Windows 10 - User: Administrador ', '2026-03-14 11:50:25', 'Eliminación Múltiple'),
(11, '::1', 'Windows 10 - User: ', '2026-03-14 13:13:52', 'Cambio de Contraseña'),
(12, '::1', 'Windows 10 - User: Administrador ', '2026-03-14 13:14:07', 'Inicio de Sesión'),
(13, '::1', 'Windows 10 - User: Administrador ', '2026-03-14 13:16:06', 'Inicio de Sesión'),
(14, '::1', 'Windows 10 - User: Administrador ', '2026-03-14 13:19:36', 'Inicio de Sesión'),
(15, '::1', 'Windows 10 - User: Administrador ', '2026-03-14 13:20:46', 'Cambio de Contraseña'),
(16, '::1', 'Windows 10 - User: Administrador ', '2026-03-14 13:20:53', 'Inicio de Sesión');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `categorias`
--

CREATE TABLE `categorias` (
  `id` int(11) NOT NULL,
  `nombre` varchar(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_spanish2_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `categorias_productos`
--

CREATE TABLE `categorias_productos` (
  `id` int(11) NOT NULL,
  `nombre` varchar(255) NOT NULL,
  `created_at` timestamp NULL DEFAULT current_timestamp()
) ENGINE=MyISAM DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `cliente`
--

CREATE TABLE `cliente` (
  `id_cliente` int(11) NOT NULL,
  `nombre` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `pagos`
--

CREATE TABLE `pagos` (
  `id` int(11) NOT NULL,
  `nombre_cliente` varchar(255) NOT NULL,
  `monto` decimal(10,2) NOT NULL,
  `metodo_pago` varchar(255) NOT NULL,
  `descripcion` text DEFAULT NULL,
  `referencia` varchar(50) NOT NULL,
  `fecha_pago` datetime NOT NULL,
  `estado` enum('pendiente','aprobado','rechazado') DEFAULT 'pendiente',
  `des_rechazo` text DEFAULT NULL,
  `fecha_registro` timestamp NOT NULL DEFAULT current_timestamp(),
  `saldo_resultante` decimal(10,2) NOT NULL DEFAULT 0.00,
  `tipo` enum('Ingreso','Egreso') NOT NULL DEFAULT 'Ingreso',
  `cliente` varchar(255) NOT NULL,
  `usuario_aprobador` varchar(255) DEFAULT NULL,
  `pago_origen_id` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_spanish2_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `pagos_productos`
--

CREATE TABLE `pagos_productos` (
  `id` int(11) NOT NULL,
  `nombre_comprador` varchar(255) NOT NULL,
  `correo_comprador` varchar(100) DEFAULT NULL,
  `telefono_comprador` varchar(100) DEFAULT NULL,
  `monto` decimal(10,2) NOT NULL,
  `monto_bs_pago` decimal(10,2) NOT NULL,
  `metodo_pago` varchar(50) NOT NULL,
  `banco` varchar(100) DEFAULT NULL,
  `fecha_pago` date NOT NULL,
  `telefono` varchar(20) DEFAULT NULL,
  `cedula` varchar(20) DEFAULT NULL,
  `numero_cuenta` varchar(50) DEFAULT NULL,
  `referencia` varchar(50) DEFAULT NULL,
  `monto_bs` decimal(10,2) NOT NULL
) ENGINE=MyISAM DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `productos`
--

CREATE TABLE `productos` (
  `id` int(11) NOT NULL,
  `nombre` varchar(255) NOT NULL,
  `descripcion` text DEFAULT NULL,
  `precio` decimal(10,2) NOT NULL,
  `stock` int(11) NOT NULL,
  `imagen` varchar(255) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  `categoria_productos_id` int(11) NOT NULL
) ENGINE=MyISAM DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `recuperacion`
--

CREATE TABLE `recuperacion` (
  `id` int(11) NOT NULL,
  `correo` varchar(255) DEFAULT NULL,
  `token` varchar(255) DEFAULT NULL,
  `expira` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_spanish2_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `usuario`
--

CREATE TABLE `usuario` (
  `id_usuario` int(11) NOT NULL,
  `nombre` varchar(100) DEFAULT NULL,
  `nacionalidad` varchar(10) DEFAULT NULL,
  `cedula` varchar(20) DEFAULT NULL,
  `usuario` varchar(100) NOT NULL,
  `correo` varchar(100) DEFAULT NULL,
  `clave` varchar(100) NOT NULL,
  `pregunta` varchar(50) DEFAULT NULL,
  `respuesta` varchar(50) DEFAULT NULL,
  `pregunta2` varchar(50) DEFAULT NULL,
  `respuesta2` varchar(50) DEFAULT NULL,
  `foto` varchar(255) DEFAULT NULL,
  `tipos` varchar(50) NOT NULL,
  `ultima_conexion` datetime DEFAULT NULL,
  `aprobado` tinyint(1) DEFAULT 0,
  `intentos` int(11) DEFAULT 0,
  `token` varchar(100) DEFAULT NULL,
  `sesion_activa` tinyint(1) DEFAULT 0,
  `ultima_actividad` timestamp NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `saldo` decimal(10,2) NOT NULL DEFAULT 0.00,
  `saldo_apertura` varchar(255) NOT NULL,
  `session_token` varchar(255) DEFAULT NULL,
  `bloqueado` tinyint(1) DEFAULT 0,
  `fecha_cambio_clave` date DEFAULT current_timestamp(),
  `codigo_verificacion` varchar(6) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

--
-- Volcado de datos para la tabla `usuario`
--

INSERT INTO `usuario` (`id_usuario`, `nombre`, `nacionalidad`, `cedula`, `usuario`, `correo`, `clave`, `pregunta`, `respuesta`, `pregunta2`, `respuesta2`, `foto`, `tipos`, `ultima_conexion`, `aprobado`, `intentos`, `token`, `sesion_activa`, `ultima_actividad`, `saldo`, `saldo_apertura`, `session_token`, `bloqueado`, `fecha_cambio_clave`, `codigo_verificacion`) VALUES
(8, 'Administrador ', 'V-', '28651982', 'Admin20', 'cristianarcaya2003@gmail.com', '13e4ea0c958bcd48398780d3d9f078ad13a16352', '¿Comida favorita?', 'ea67522e225a0806044913b453fcb8de4892740e', '¿Película favorita?', 'ccec0fb5b94371c1694d9419ac0039557e0b194d', '../img/fotos_perfil/1765071969_default_profile.png', 'admin', '2026-03-14 13:20:53', 1, 0, NULL, 0, '2026-03-14 17:20:53', 0.00, '', 'df532a6358adcdc5ac1198696ffa8e8d', 0, '2026-03-14', NULL),
(21, 'Cristian Eduardo Arcaya Colina', 'V-', '28651980', 'Chriseduard20', 'cristianarcaya2003@gmail.com', '13e4ea0c958bcd48398780d3d9f078ad13a16352', '¿Nombre de mi mascota?', '728d6c2a4fcb733102954da2feb5235fa66b4c78', '¿Color Preferido?', '5bdda331cf92266cb13c74a8558f2eb28451d5af', '../img/fotos_perfil/1765462511_1759692780_default_profile.png', 'cont', '2026-03-13 13:09:52', 1, 0, NULL, 0, '2026-03-13 20:37:41', 0.00, '', 'f23e8a1f99f8b1514b68f9a2afcb757e', 0, '2026-03-14', NULL),
(85, 'Upu C', 'G-', '20006598', 'Upu20', 'cristianarcaya2003@gmail.com', '13e4ea0c958bcd48398780d3d9f078ad13a16352', '¿Color Preferido?', '5bdda331cf92266cb13c74a8558f2eb28451d5af', '¿Comida favorita?', 'ea67522e225a0806044913b453fcb8de4892740e', '../img/default_profile.png', 'upu', NULL, 1, 0, NULL, 0, '2026-03-14 15:41:45', 0.00, '', NULL, 0, '2026-03-14', NULL);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `usuario_pagos`
--

CREATE TABLE `usuario_pagos` (
  `id` int(11) NOT NULL,
  `usuario_id` int(11) DEFAULT NULL,
  `pago_id` int(11) DEFAULT NULL,
  `bitacora_id` int(11) DEFAULT NULL,
  `cliente_id` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_spanish2_ci;

--
-- Volcado de datos para la tabla `usuario_pagos`
--

INSERT INTO `usuario_pagos` (`id`, `usuario_id`, `pago_id`, `bitacora_id`, `cliente_id`) VALUES
(1, 8, NULL, 1, NULL),
(2, 8, NULL, 2, NULL),
(3, 8, NULL, 3, NULL),
(4, 8, NULL, 4, NULL),
(5, 8, NULL, 5, NULL),
(6, 8, NULL, 6, NULL),
(7, 8, NULL, 7, NULL),
(8, 8, NULL, 8, NULL),
(9, 8, NULL, 9, NULL),
(10, 8, NULL, 10, NULL),
(11, 8, NULL, 11, NULL),
(12, 8, NULL, 12, NULL),
(13, 8, NULL, 13, NULL),
(14, 8, NULL, 14, NULL),
(15, 8, NULL, 15, NULL),
(16, 8, NULL, 16, NULL);

--
-- Índices para tablas volcadas
--

--
-- Indices de la tabla `bienes`
--
ALTER TABLE `bienes`
  ADD PRIMARY KEY (`id`),
  ADD KEY `fk_categoria_id` (`categoria_id`);

--
-- Indices de la tabla `bitacora`
--
ALTER TABLE `bitacora`
  ADD PRIMARY KEY (`id`);

--
-- Indices de la tabla `categorias`
--
ALTER TABLE `categorias`
  ADD PRIMARY KEY (`id`);

--
-- Indices de la tabla `categorias_productos`
--
ALTER TABLE `categorias_productos`
  ADD PRIMARY KEY (`id`);

--
-- Indices de la tabla `cliente`
--
ALTER TABLE `cliente`
  ADD PRIMARY KEY (`id_cliente`);

--
-- Indices de la tabla `pagos`
--
ALTER TABLE `pagos`
  ADD PRIMARY KEY (`id`);

--
-- Indices de la tabla `pagos_productos`
--
ALTER TABLE `pagos_productos`
  ADD PRIMARY KEY (`id`);

--
-- Indices de la tabla `productos`
--
ALTER TABLE `productos`
  ADD PRIMARY KEY (`id`),
  ADD KEY `fk_categoria_producto` (`categoria_productos_id`);

--
-- Indices de la tabla `recuperacion`
--
ALTER TABLE `recuperacion`
  ADD PRIMARY KEY (`id`);

--
-- Indices de la tabla `usuario`
--
ALTER TABLE `usuario`
  ADD PRIMARY KEY (`id_usuario`),
  ADD UNIQUE KEY `cedula` (`cedula`);

--
-- Indices de la tabla `usuario_pagos`
--
ALTER TABLE `usuario_pagos`
  ADD PRIMARY KEY (`id`),
  ADD KEY `usuario_id` (`usuario_id`),
  ADD KEY `pago_id` (`pago_id`),
  ADD KEY `bitacora_id` (`bitacora_id`),
  ADD KEY `cliente_id` (`cliente_id`);

--
-- AUTO_INCREMENT de las tablas volcadas
--

--
-- AUTO_INCREMENT de la tabla `bienes`
--
ALTER TABLE `bienes`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `bitacora`
--
ALTER TABLE `bitacora`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=17;

--
-- AUTO_INCREMENT de la tabla `categorias`
--
ALTER TABLE `categorias`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `categorias_productos`
--
ALTER TABLE `categorias_productos`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `cliente`
--
ALTER TABLE `cliente`
  MODIFY `id_cliente` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `pagos`
--
ALTER TABLE `pagos`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `pagos_productos`
--
ALTER TABLE `pagos_productos`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `productos`
--
ALTER TABLE `productos`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `recuperacion`
--
ALTER TABLE `recuperacion`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `usuario`
--
ALTER TABLE `usuario`
  MODIFY `id_usuario` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=86;

--
-- AUTO_INCREMENT de la tabla `usuario_pagos`
--
ALTER TABLE `usuario_pagos`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=17;

--
-- Restricciones para tablas volcadas
--

--
-- Filtros para la tabla `bienes`
--
ALTER TABLE `bienes`
  ADD CONSTRAINT `fk_categoria_id` FOREIGN KEY (`categoria_id`) REFERENCES `categorias` (`id`) ON DELETE CASCADE;

--
-- Filtros para la tabla `usuario_pagos`
--
ALTER TABLE `usuario_pagos`
  ADD CONSTRAINT `fk_cliente` FOREIGN KEY (`cliente_id`) REFERENCES `cliente` (`id_cliente`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `usuario_pagos_ibfk_1` FOREIGN KEY (`usuario_id`) REFERENCES `usuario` (`id_usuario`) ON DELETE CASCADE,
  ADD CONSTRAINT `usuario_pagos_ibfk_2` FOREIGN KEY (`pago_id`) REFERENCES `pagos` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `usuario_pagos_ibfk_3` FOREIGN KEY (`bitacora_id`) REFERENCES `bitacora` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;

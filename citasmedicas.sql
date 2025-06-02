-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Servidor: localhost:3306
-- Tiempo de generación: 02-06-2025 a las 03:40:06
-- Versión del servidor: 8.0.30
-- Versión de PHP: 8.1.10

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Base de datos: `citasmedicas`
--

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `accesos`
--

CREATE TABLE `accesos` (
  `id_accesos` int NOT NULL,
  `usuario_id` int NOT NULL,
  `modulo` varchar(50) DEFAULT NULL,
  `accion` varchar(100) DEFAULT NULL,
  `fecha` datetime DEFAULT CURRENT_TIMESTAMP,
  `activo` tinyint(1) DEFAULT '1'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `cancelaciones`
--

CREATE TABLE `cancelaciones` (
  `id_cancelaciones` int NOT NULL,
  `cita_id` int NOT NULL,
  `motivo` text NOT NULL,
  `fecha_cancelacion` datetime DEFAULT CURRENT_TIMESTAMP,
  `activo` tinyint(1) DEFAULT '1'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `citas`
--

CREATE TABLE `citas` (
  `id_citas` int NOT NULL,
  `paciente_id` int NOT NULL,
  `medico_id` int NOT NULL,
  `fecha` datetime NOT NULL,
  `estado_id` int NOT NULL,
  `motivo` text,
  `es_urgente` tinyint(1) DEFAULT '0',
  `fecha_creacion` datetime DEFAULT CURRENT_TIMESTAMP,
  `activo` tinyint(1) DEFAULT '1'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `configuraciones`
--

CREATE TABLE `configuraciones` (
  `id_configuraciones` int NOT NULL,
  `usuario_id` int NOT NULL,
  `notificacion_email` tinyint(1) DEFAULT '1',
  `notificacion_sms` tinyint(1) DEFAULT '0'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `documentos_medicos`
--

CREATE TABLE `documentos_medicos` (
  `id_documentos_medicos` int NOT NULL,
  `paciente_id` int NOT NULL,
  `cita_id` int DEFAULT NULL,
  `nombre_archivo` varchar(100) DEFAULT NULL,
  `url` text,
  `fecha_subida` datetime DEFAULT CURRENT_TIMESTAMP,
  `activo` tinyint(1) DEFAULT '1'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `especialidades`
--

CREATE TABLE `especialidades` (
  `id_especialidades` int NOT NULL,
  `nombre` varchar(100) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `estado_cita`
--

CREATE TABLE `estado_cita` (
  `id_estado_cita` int NOT NULL,
  `descripcion` varchar(50) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `examenes`
--

CREATE TABLE `examenes` (
  `id_examenes` int NOT NULL,
  `paciente_id` int NOT NULL,
  `cita_id` int DEFAULT NULL,
  `tipo_examen` varchar(100) DEFAULT NULL,
  `resultado` text,
  `fecha` datetime DEFAULT CURRENT_TIMESTAMP,
  `activo` tinyint(1) DEFAULT '1'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `historial_medico`
--

CREATE TABLE `historial_medico` (
  `id_historial_medico` int NOT NULL,
  `cita_id` int NOT NULL,
  `diagnostico` text,
  `tratamiento` text,
  `activo` tinyint(1) DEFAULT '1'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `medicos`
--

CREATE TABLE `medicos` (
  `id_medicos` int NOT NULL,
  `usuario_id` int NOT NULL,
  `especialidad_id` int NOT NULL,
  `activo` tinyint(1) DEFAULT '1'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `multifactor_tokens`
--

CREATE TABLE `multifactor_tokens` (
  `id_multifactor_tokens` int NOT NULL,
  `usuario_id` int NOT NULL,
  `token` varchar(6) NOT NULL,
  `fecha_envio` datetime DEFAULT CURRENT_TIMESTAMP,
  `expirado` tinyint(1) DEFAULT '0'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Volcado de datos para la tabla `multifactor_tokens`
--

INSERT INTO `multifactor_tokens` (`id_multifactor_tokens`, `usuario_id`, `token`, `fecha_envio`, `expirado`) VALUES
(1, 2, '488179', '2025-05-18 23:06:56', 1),
(2, 3, '863320', '2025-05-18 23:20:12', 0),
(3, 3, '972615', '2025-05-18 23:20:19', 0),
(4, 3, '941062', '2025-05-18 23:20:57', 0),
(5, 3, '952992', '2025-05-18 23:21:03', 0),
(6, 3, '559603', '2025-05-18 23:21:14', 0),
(7, 3, '255279', '2025-05-18 23:23:06', 0),
(8, 3, '828815', '2025-05-18 23:23:10', 0),
(9, 3, '893762', '2025-05-18 23:23:19', 0),
(10, 3, '948142', '2025-05-18 23:28:05', 0),
(11, 3, '450978', '2025-05-18 23:28:16', 0),
(12, 3, '907816', '2025-05-18 23:28:38', 0),
(13, 2, '214000', '2025-05-18 23:28:45', 0),
(14, 3, '561324', '2025-05-18 23:36:41', 0),
(15, 2, '758320', '2025-05-18 23:40:58', 0),
(16, 2, '814120', '2025-05-18 23:43:00', 1),
(17, 3, '900420', '2025-05-18 23:43:29', 1),
(18, 3, '558957', '2025-05-19 17:12:21', 0),
(19, 2, '941216', '2025-05-19 17:17:44', 0),
(20, 2, '363871', '2025-05-19 18:01:54', 1),
(21, 2, '566232', '2025-05-27 21:58:27', 1),
(22, 2, '139236', '2025-05-27 22:27:28', 1),
(23, 2, '311928', '2025-05-27 23:58:24', 1),
(24, 2, '920316', '2025-05-29 20:51:02', 1),
(25, 2, '228568', '2025-05-29 22:50:05', 1),
(26, 2, '317369', '2025-05-30 00:23:49', 1),
(27, 3, '120060', '2025-06-01 20:31:56', 0),
(28, 3, '250007', '2025-06-01 20:31:59', 1),
(29, 2, '106678', '2025-06-01 20:34:20', 1),
(30, 2, '881763', '2025-06-01 20:43:28', 1),
(31, 2, '731848', '2025-06-01 21:19:00', 1);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `notificaciones`
--

CREATE TABLE `notificaciones` (
  `id_notificaciones` int NOT NULL,
  `usuario_id` int NOT NULL,
  `mensaje` text NOT NULL,
  `tipo` varchar(50) DEFAULT NULL,
  `enviado` tinyint(1) DEFAULT '0',
  `fecha_envio` datetime DEFAULT CURRENT_TIMESTAMP,
  `activo` tinyint(1) DEFAULT '1'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `pacientes`
--

CREATE TABLE `pacientes` (
  `id_pacientes` int NOT NULL,
  `usuario_id` int NOT NULL,
  `fecha_nacimiento` date DEFAULT NULL,
  `grupo_sanguineo` varchar(5) DEFAULT NULL,
  `activo` tinyint(1) DEFAULT '1'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Volcado de datos para la tabla `pacientes`
--

INSERT INTO `pacientes` (`id_pacientes`, `usuario_id`, `fecha_nacimiento`, `grupo_sanguineo`, `activo`) VALUES
(1, 3, '2001-12-30', 'A+', 1),
(5, 7, '2018-01-01', 'AB-', 1);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `recetas_medicas`
--

CREATE TABLE `recetas_medicas` (
  `id_recetas_medicas` int NOT NULL,
  `cita_id` int NOT NULL,
  `contenido` text,
  `fecha_emision` datetime DEFAULT CURRENT_TIMESTAMP,
  `activo` tinyint(1) DEFAULT '1'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `roles`
--

CREATE TABLE `roles` (
  `id_roles` int NOT NULL,
  `nombre` varchar(50) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Volcado de datos para la tabla `roles`
--

INSERT INTO `roles` (`id_roles`, `nombre`) VALUES
(1, 'Administrador'),
(2, 'Médico'),
(3, 'Paciente');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `usuarios`
--

CREATE TABLE `usuarios` (
  `id_usuarios` int NOT NULL,
  `nombre_completo` varchar(100) NOT NULL,
  `correo` varchar(100) NOT NULL,
  `telefono` varchar(20) DEFAULT NULL,
  `direccion` text,
  `password` varchar(255) NOT NULL,
  `rol_id` int NOT NULL,
  `activo` tinyint(1) DEFAULT '1',
  `fecha_registro` datetime DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Volcado de datos para la tabla `usuarios`
--

INSERT INTO `usuarios` (`id_usuarios`, `nombre_completo`, `correo`, `telefono`, `direccion`, `password`, `rol_id`, `activo`, `fecha_registro`) VALUES
(2, 'Kevin Edgardo Sánchez Martínez', 'kevinedgardosanchezmartinez@gmail.com', '71234561', 'km13', '$2y$10$gdbntFq0eDn7oh5xK52rm.M4dbCtmY4zb9xtDmEOz/D.rFpe/.Rjq', 1, 1, '2025-05-18 22:26:27'),
(3, 'Christopher Enrique López Castro', 'chrispherenrique111@gmail.com', '12345678', '25 de noviembre UPES', '$2y$10$kwexaneB5Z40vVvfMdsYS.1jywQ4JL.jzJ1i9IuFXx7NQw0ZBwtEu', 3, 1, '2025-05-18 23:15:33'),
(7, 'Juan José Quintanilla Vanegas', 'chrispherenrique@gmail.com', '71234569', 'km12', '$2y$10$nM4tYQb.FlT0cN0TsLOMI.rGQ0ojAlcm7yILOhBPljZ1f8UqTy0sa', 3, 1, '2025-06-01 21:20:10');

--
-- Índices para tablas volcadas
--

--
-- Indices de la tabla `accesos`
--
ALTER TABLE `accesos`
  ADD PRIMARY KEY (`id_accesos`),
  ADD KEY `usuario_id` (`usuario_id`);

--
-- Indices de la tabla `cancelaciones`
--
ALTER TABLE `cancelaciones`
  ADD PRIMARY KEY (`id_cancelaciones`),
  ADD KEY `cita_id` (`cita_id`);

--
-- Indices de la tabla `citas`
--
ALTER TABLE `citas`
  ADD PRIMARY KEY (`id_citas`),
  ADD KEY `paciente_id` (`paciente_id`),
  ADD KEY `medico_id` (`medico_id`),
  ADD KEY `estado_id` (`estado_id`);

--
-- Indices de la tabla `configuraciones`
--
ALTER TABLE `configuraciones`
  ADD PRIMARY KEY (`id_configuraciones`),
  ADD UNIQUE KEY `usuario_id` (`usuario_id`);

--
-- Indices de la tabla `documentos_medicos`
--
ALTER TABLE `documentos_medicos`
  ADD PRIMARY KEY (`id_documentos_medicos`),
  ADD KEY `paciente_id` (`paciente_id`),
  ADD KEY `cita_id` (`cita_id`);

--
-- Indices de la tabla `especialidades`
--
ALTER TABLE `especialidades`
  ADD PRIMARY KEY (`id_especialidades`),
  ADD UNIQUE KEY `nombre` (`nombre`);

--
-- Indices de la tabla `estado_cita`
--
ALTER TABLE `estado_cita`
  ADD PRIMARY KEY (`id_estado_cita`),
  ADD UNIQUE KEY `descripcion` (`descripcion`);

--
-- Indices de la tabla `examenes`
--
ALTER TABLE `examenes`
  ADD PRIMARY KEY (`id_examenes`),
  ADD KEY `paciente_id` (`paciente_id`),
  ADD KEY `cita_id` (`cita_id`);

--
-- Indices de la tabla `historial_medico`
--
ALTER TABLE `historial_medico`
  ADD PRIMARY KEY (`id_historial_medico`),
  ADD UNIQUE KEY `cita_id` (`cita_id`);

--
-- Indices de la tabla `medicos`
--
ALTER TABLE `medicos`
  ADD PRIMARY KEY (`id_medicos`),
  ADD UNIQUE KEY `usuario_id` (`usuario_id`),
  ADD KEY `especialidad_id` (`especialidad_id`);

--
-- Indices de la tabla `multifactor_tokens`
--
ALTER TABLE `multifactor_tokens`
  ADD PRIMARY KEY (`id_multifactor_tokens`),
  ADD KEY `usuario_id` (`usuario_id`);

--
-- Indices de la tabla `notificaciones`
--
ALTER TABLE `notificaciones`
  ADD PRIMARY KEY (`id_notificaciones`),
  ADD KEY `usuario_id` (`usuario_id`);

--
-- Indices de la tabla `pacientes`
--
ALTER TABLE `pacientes`
  ADD PRIMARY KEY (`id_pacientes`),
  ADD UNIQUE KEY `usuario_id` (`usuario_id`);

--
-- Indices de la tabla `recetas_medicas`
--
ALTER TABLE `recetas_medicas`
  ADD PRIMARY KEY (`id_recetas_medicas`),
  ADD KEY `cita_id` (`cita_id`);

--
-- Indices de la tabla `roles`
--
ALTER TABLE `roles`
  ADD PRIMARY KEY (`id_roles`),
  ADD UNIQUE KEY `nombre` (`nombre`);

--
-- Indices de la tabla `usuarios`
--
ALTER TABLE `usuarios`
  ADD PRIMARY KEY (`id_usuarios`),
  ADD UNIQUE KEY `correo` (`correo`),
  ADD KEY `rol_id` (`rol_id`);

--
-- AUTO_INCREMENT de las tablas volcadas
--

--
-- AUTO_INCREMENT de la tabla `accesos`
--
ALTER TABLE `accesos`
  MODIFY `id_accesos` int NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `cancelaciones`
--
ALTER TABLE `cancelaciones`
  MODIFY `id_cancelaciones` int NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `citas`
--
ALTER TABLE `citas`
  MODIFY `id_citas` int NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `configuraciones`
--
ALTER TABLE `configuraciones`
  MODIFY `id_configuraciones` int NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `documentos_medicos`
--
ALTER TABLE `documentos_medicos`
  MODIFY `id_documentos_medicos` int NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `especialidades`
--
ALTER TABLE `especialidades`
  MODIFY `id_especialidades` int NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `estado_cita`
--
ALTER TABLE `estado_cita`
  MODIFY `id_estado_cita` int NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `examenes`
--
ALTER TABLE `examenes`
  MODIFY `id_examenes` int NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `historial_medico`
--
ALTER TABLE `historial_medico`
  MODIFY `id_historial_medico` int NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `medicos`
--
ALTER TABLE `medicos`
  MODIFY `id_medicos` int NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `multifactor_tokens`
--
ALTER TABLE `multifactor_tokens`
  MODIFY `id_multifactor_tokens` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=32;

--
-- AUTO_INCREMENT de la tabla `notificaciones`
--
ALTER TABLE `notificaciones`
  MODIFY `id_notificaciones` int NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `pacientes`
--
ALTER TABLE `pacientes`
  MODIFY `id_pacientes` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT de la tabla `recetas_medicas`
--
ALTER TABLE `recetas_medicas`
  MODIFY `id_recetas_medicas` int NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `roles`
--
ALTER TABLE `roles`
  MODIFY `id_roles` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT de la tabla `usuarios`
--
ALTER TABLE `usuarios`
  MODIFY `id_usuarios` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- Restricciones para tablas volcadas
--

--
-- Filtros para la tabla `accesos`
--
ALTER TABLE `accesos`
  ADD CONSTRAINT `accesos_ibfk_1` FOREIGN KEY (`usuario_id`) REFERENCES `usuarios` (`id_usuarios`);

--
-- Filtros para la tabla `cancelaciones`
--
ALTER TABLE `cancelaciones`
  ADD CONSTRAINT `cancelaciones_ibfk_1` FOREIGN KEY (`cita_id`) REFERENCES `citas` (`id_citas`);

--
-- Filtros para la tabla `citas`
--
ALTER TABLE `citas`
  ADD CONSTRAINT `citas_ibfk_1` FOREIGN KEY (`paciente_id`) REFERENCES `pacientes` (`id_pacientes`),
  ADD CONSTRAINT `citas_ibfk_2` FOREIGN KEY (`medico_id`) REFERENCES `medicos` (`id_medicos`),
  ADD CONSTRAINT `citas_ibfk_3` FOREIGN KEY (`estado_id`) REFERENCES `estado_cita` (`id_estado_cita`);

--
-- Filtros para la tabla `configuraciones`
--
ALTER TABLE `configuraciones`
  ADD CONSTRAINT `configuraciones_ibfk_1` FOREIGN KEY (`usuario_id`) REFERENCES `usuarios` (`id_usuarios`);

--
-- Filtros para la tabla `documentos_medicos`
--
ALTER TABLE `documentos_medicos`
  ADD CONSTRAINT `documentos_medicos_ibfk_1` FOREIGN KEY (`paciente_id`) REFERENCES `pacientes` (`id_pacientes`),
  ADD CONSTRAINT `documentos_medicos_ibfk_2` FOREIGN KEY (`cita_id`) REFERENCES `citas` (`id_citas`);

--
-- Filtros para la tabla `examenes`
--
ALTER TABLE `examenes`
  ADD CONSTRAINT `examenes_ibfk_1` FOREIGN KEY (`paciente_id`) REFERENCES `pacientes` (`id_pacientes`),
  ADD CONSTRAINT `examenes_ibfk_2` FOREIGN KEY (`cita_id`) REFERENCES `citas` (`id_citas`);

--
-- Filtros para la tabla `historial_medico`
--
ALTER TABLE `historial_medico`
  ADD CONSTRAINT `historial_medico_ibfk_1` FOREIGN KEY (`cita_id`) REFERENCES `citas` (`id_citas`);

--
-- Filtros para la tabla `medicos`
--
ALTER TABLE `medicos`
  ADD CONSTRAINT `medicos_ibfk_1` FOREIGN KEY (`usuario_id`) REFERENCES `usuarios` (`id_usuarios`),
  ADD CONSTRAINT `medicos_ibfk_2` FOREIGN KEY (`especialidad_id`) REFERENCES `especialidades` (`id_especialidades`);

--
-- Filtros para la tabla `multifactor_tokens`
--
ALTER TABLE `multifactor_tokens`
  ADD CONSTRAINT `multifactor_tokens_ibfk_1` FOREIGN KEY (`usuario_id`) REFERENCES `usuarios` (`id_usuarios`);

--
-- Filtros para la tabla `notificaciones`
--
ALTER TABLE `notificaciones`
  ADD CONSTRAINT `notificaciones_ibfk_1` FOREIGN KEY (`usuario_id`) REFERENCES `usuarios` (`id_usuarios`);

--
-- Filtros para la tabla `pacientes`
--
ALTER TABLE `pacientes`
  ADD CONSTRAINT `pacientes_ibfk_1` FOREIGN KEY (`usuario_id`) REFERENCES `usuarios` (`id_usuarios`);

--
-- Filtros para la tabla `recetas_medicas`
--
ALTER TABLE `recetas_medicas`
  ADD CONSTRAINT `recetas_medicas_ibfk_1` FOREIGN KEY (`cita_id`) REFERENCES `citas` (`id_citas`);

--
-- Filtros para la tabla `usuarios`
--
ALTER TABLE `usuarios`
  ADD CONSTRAINT `usuarios_ibfk_1` FOREIGN KEY (`rol_id`) REFERENCES `roles` (`id_roles`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;

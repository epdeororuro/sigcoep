-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Servidor: 127.0.0.1
-- Tiempo de generación: 05-03-2026 a las 19:01:37
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
-- Base de datos: `sigcoep`
--

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `correspondencia`
--

CREATE TABLE `correspondencia` (
  `id` int(11) NOT NULL,
  `hojaruta` varchar(255) NOT NULL,
  `remitente_id` int(11) DEFAULT NULL,
  `remitente_externo` varchar(255) DEFAULT NULL,
  `idfuncionario_enturno` int(11) DEFAULT NULL,
  `tipo_remitente` enum('interno','externo') NOT NULL DEFAULT 'externo',
  `remitente` varchar(255) NOT NULL,
  `referencia` text NOT NULL,
  `fojas` int(11) NOT NULL,
  `fecha` timestamp NOT NULL DEFAULT current_timestamp(),
  `estado` varchar(255) NOT NULL,
  `actualizado_en` timestamp NULL DEFAULT NULL,
  `eliminado_en` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `correspondencia`
--

INSERT INTO `correspondencia` (`id`, `hojaruta`, `remitente_id`, `remitente_externo`, `idfuncionario_enturno`, `tipo_remitente`, `remitente`, `referencia`, `fojas`, `fecha`, `estado`, `actualizado_en`, `eliminado_en`) VALUES
(1, '01/2025', NULL, NULL, NULL, 'externo', 'Nelia Alejo', 'Solicitud dde Revision de camaras', 1, '2026-02-27 20:37:45', 'Derivado', '2026-02-27 20:45:59', NULL),
(2, '02/2025', 9, NULL, NULL, 'externo', 'David Ticona Cabrera', 'mantenimiento de CPU', 1, '2026-02-27 20:45:25', 'Derivado', '2026-02-27 20:46:17', NULL),
(3, '03/2025', 5, NULL, NULL, 'externo', 'Maricruz Sara Mamani Nieto', 'Solicitud de Mantenimiento de CPU', 1, '2026-03-03 17:31:03', 'Iniciado', '2026-03-03 17:32:30', NULL),
(4, '04/2025', 5, NULL, NULL, 'externo', 'Maricruz Sara Mamani Nieto', 'Mantenemiento de CPU', 1, '2026-03-03 17:59:59', 'Iniciado', '2026-03-03 18:15:21', NULL),
(5, '05/2025', 12, NULL, NULL, 'interno', 'Marina Alegre -', 'Informe Mensual Area de Cobranzas', 3, '2026-03-05 16:02:00', 'Derivado', '2026-03-05 17:56:35', NULL),
(6, '06/2025', 12, NULL, NULL, 'interno', 'Marina Alegre -', 'Cobranzas Reportes', 4, '2026-03-05 16:28:16', 'Registrado', NULL, NULL);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `derivacion`
--

CREATE TABLE `derivacion` (
  `id` int(11) NOT NULL,
  `id_correspondencia` int(11) NOT NULL,
  `id_funcionario` int(11) NOT NULL,
  `fecha_derivacion` timestamp NOT NULL DEFAULT current_timestamp(),
  `instruccion_adicional` text DEFAULT NULL,
  `fojas` int(11) NOT NULL,
  `caracter` varchar(25) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `derivacion`
--

INSERT INTO `derivacion` (`id`, `id_correspondencia`, `id_funcionario`, `fecha_derivacion`, `instruccion_adicional`, `fojas`, `caracter`) VALUES
(1, 1, 2, '2026-02-27 20:38:48', 'Para su atención', 1, 'Para conocimiento'),
(2, 2, 2, '2026-02-27 20:45:27', 'Para su atención', 1, 'Para conocimiento'),
(3, 1, 6, '2026-02-27 20:45:59', 'Para su atencion', 1, 'Urgente'),
(4, 2, 14, '2026-02-27 20:46:17', 'Para su consideracion', 2, 'Para conocimiento'),
(5, 3, 2, '2026-03-03 17:32:30', 'Para su atención', 1, 'Para conocimiento'),
(6, 4, 2, '2026-03-03 18:15:21', 'Para su atención', 1, 'Para conocimiento'),
(7, 5, 2, '2026-03-05 16:02:04', 'Para su atención', 3, 'Para conocimiento'),
(8, 5, 6, '2026-03-05 17:56:35', 'Para su atención', 1, 'Urgente');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `funcionario`
--

CREATE TABLE `funcionario` (
  `id` int(11) NOT NULL,
  `ci` int(11) NOT NULL,
  `nombre` varchar(100) NOT NULL,
  `paterno` varchar(60) NOT NULL,
  `materno` varchar(60) NOT NULL,
  `usuario` varchar(50) NOT NULL,
  `password` varchar(255) NOT NULL,
  `rol` varchar(25) DEFAULT NULL,
  `id_puesto` int(11) NOT NULL,
  `estado` varchar(12) NOT NULL,
  `creado_en` timestamp NOT NULL DEFAULT current_timestamp(),
  `actualizado_en` timestamp NOT NULL DEFAULT current_timestamp(),
  `eliminado_en` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `funcionario`
--

INSERT INTO `funcionario` (`id`, `ci`, `nombre`, `paterno`, `materno`, `usuario`, `password`, `rol`, `id_puesto`, `estado`, `creado_en`, `actualizado_en`, `eliminado_en`) VALUES
(1, 123456789, 'Superadmin', ' ', ' ', 'admin', '$2y$10$29Br5HbBSerZ6YYH6ekGvOyfs4rFkJpRHET9fUbGnHG7zSXbkuWOG', 'Administrador', 19, 'Activo', '2026-02-26 18:56:07', '2026-02-26 18:56:07', '2026-02-26 18:56:07'),
(2, 7343846, 'Elizabeth', 'Martinez', 'Achacollo', 'emartinez', '$2y$10$H05dXZb8DvWcIMMmkkG4ZurvPN4aSTa7fvTgZl6zFPebxwN86jo4G', 'Gerente', 1, 'Activo', '2026-02-26 20:38:37', '2026-02-28 01:36:32', '2026-02-26 20:38:37'),
(3, 7200300, 'Mirian', 'Rada', 'Lopez', 'mrada', '$2y$10$XyCaXqajRZ2YS19R5iRrye2eXe3N5WLiseeXjheI1x3cRc243hn62', 'Administrativo', 2, 'Activo', '2026-02-26 20:39:16', '2026-02-28 01:36:49', '2026-02-26 20:39:16'),
(4, 3, 'Carmen Marisol', 'Rufino', 'Segovia', 'crufino', '$2y$10$ghzc1cBlL8SJMnkCtrbST.68uVN7YL022gYpjXR8NzHReNJ4kyWBO', 'Administrativo', 3, 'Activo', '2026-02-26 20:40:56', '2026-02-26 20:40:56', '2026-02-26 20:40:56'),
(5, 4, 'Maricruz Sara', 'Mamani', 'Nieto', 'mmamani', '$2y$10$VUnrf.kHPNQqY560692WZu/C.egBgjsgUBxCHr2mN8niIlC2fnk6C', 'Secretaria', 4, 'Activo', '2026-02-26 20:41:32', '2026-02-27 01:55:11', '2026-02-26 20:41:32'),
(6, 5, 'Belinda', 'Perez', 'Ayma', 'bperez', '$2y$10$vpT863tXlFBaxu6QqkbBRuUvmVPWHYhg6S/2Y.3qvOscI5hQgv0Tq', 'Administrativo', 5, 'Activo', '2026-02-26 20:41:55', '2026-02-26 20:41:55', '2026-02-26 20:41:55'),
(7, 6, 'Erwin Jorge', 'Gonzales', 'Rioja', 'egonzales', '$2y$10$mK0dXSaXW3jN5QNI0PYVl.zRksRh5rblowDgMrJKWcMOj.dWOxlbq', 'Administrativo', 7, 'Activo', '2026-02-26 20:42:23', '2026-02-26 20:42:23', '2026-02-26 20:42:23'),
(8, 7, 'Carlos G. ', 'Rodriguez', 'Rocha', 'crodriguez', '$2y$10$3ak7hO4TYOHbEU4x8mL5o.ij5PIG3uuVLlcmPbZ5ENKwiyd.vdQs2', 'Administrativo', 6, 'Activo', '2026-02-26 20:42:46', '2026-02-26 20:42:46', '2026-02-26 20:42:46'),
(9, 8, 'David', 'Ticona', 'Cabrera', 'dticona', '$2y$10$Z1DQSNnA85NyImbsfdj2NOWnwoUPA5Ldm0Ptot1YyaMYu91ko7WaK', 'Administrativo', 16, 'Activo', '2026-02-26 20:44:20', '2026-02-26 20:44:20', '2026-02-26 20:44:20'),
(10, 9, 'Jeanneth Angelica', 'Chambi', 'Chinche', 'jchambi', '$2y$10$Xmm3G4fcPsI5/BSsnENjuOblpYLHfy1Xg31NemLPo1IvclO3PRowG', 'Administrativo', 8, 'Activo', '2026-02-26 20:44:34', '2026-02-26 20:44:34', '2026-02-26 20:44:34'),
(11, 10, 'Guadalupe', 'Gutierrez', 'Mamani', 'ggutierrez', '$2y$10$iZO8AosadOY83UP54dpu.useU.nIljFU9p1N44zhcgZvy8x5L.Mwu', 'Administrativo', 10, 'Activo', '2026-02-26 20:45:23', '2026-02-26 20:45:23', '2026-02-26 20:45:23'),
(12, 11, 'Marina', 'Alegre', '-', 'malegre', '$2y$10$LDVRbHpu5FaDLkrcTYswfeejRhGk8PxWHuQ8sexiGeQVfjFfORN.2', 'Administrativo', 13, 'Activo', '2026-02-26 20:45:57', '2026-02-26 20:45:57', '2026-02-26 20:45:57'),
(13, 12, 'Maria', 'Colque', 'Rivera', 'mcolque', '$2y$10$3h/fkkiEjkcNywuJr0ltFemTpEaYAp.Y2qLbm1xehqmtkBWEAMn0e', 'Administrativo', 9, 'Activo', '2026-02-26 20:47:04', '2026-02-26 20:47:04', '2026-02-26 20:47:04'),
(14, 7403044, 'Reynaldo Jesus', 'Flores', 'Jaillita', 'rflores', '$2y$10$tGXRqOwXUGP7XYSS5a/yUepmSsuGeRDqd4w3NJ4rrM77LyMuRESPC', 'Administrativo', 15, 'Activo', '2026-02-26 20:47:26', '2026-02-26 20:47:26', '2026-02-26 20:47:26'),
(15, 14, 'Milton Jose', 'Torrez', 'Alegre', 'mtorrez', '$2y$10$QGfyme45IF.G8w873H7cTeIBzE4gWWH2MiBg3FQcb7XKi6XNh7lQ2', 'Administrativo', 11, 'Activo', '2026-02-26 20:47:57', '2026-02-26 20:47:57', '2026-02-26 20:47:57'),
(16, 15, 'Marina Ana', 'Alejandro', 'Ayala', 'malejandro', '$2y$10$CEbLpulfeju6Dn5/p0y6nO8dNDaLoij06o/y801BI8Ntjk6YKwe9K', 'Administrativo', 12, 'Activo', '2026-02-26 20:48:17', '2026-02-26 20:48:17', '2026-02-26 20:48:17'),
(17, 16, 'Scarleth', '-', '-', 's-', '$2y$10$eZhHnZgyRE7tvD9WTr1GN.tDLb4ZuU3mM3wpEGMbBXieccf2Kzv1a', 'Administrativo', 17, 'Activo', '2026-02-26 20:49:01', '2026-02-26 20:49:01', '2026-02-26 20:49:01'),
(18, 17, 'Jorge', 'Quillaguaman', '-', 'jquillaguaman', '$2y$10$2DVs9Kc42iAeqkZKkw9ai.2mk73dj1mb4vCKd7lsLoHw3YFEFR8R.', 'Administrativo', 18, 'Activo', '2026-02-26 20:49:23', '2026-02-26 20:49:23', '2026-02-26 20:49:23');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `puesto`
--

CREATE TABLE `puesto` (
  `id` int(11) NOT NULL,
  `descripcion` varchar(70) NOT NULL,
  `sigla` varchar(25) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `puesto`
--

INSERT INTO `puesto` (`id`, `descripcion`, `sigla`) VALUES
(1, 'Gerencia General', 'GERENCIA GENERAL'),
(2, 'Asesoría Legal', 'ASESORIA LEGAL'),
(3, 'Auditoria Interna', 'UNIDAD DE AUDITORIA INT.'),
(4, 'Secretaria Ejecutiva', 'SEC. EJE.'),
(5, 'Jefatura Dpto. de Administración y Finanzas', 'JDAF'),
(6, 'Jefatura Dpto. de Operaciones Hotel Terminal', 'JDOHT'),
(7, 'Jefatura Dpto. de Operaciones Terminal', 'JDOT'),
(8, 'Encargado de Contabilidad', 'AREA CONTABILIDAD'),
(9, 'Profesional I de Recursos Humanos y Normas', 'RR.HH.'),
(10, 'Profesional I de Activos Fijos/Almacenes y Archivos', 'ACT.FIJ.-ALM-ARC'),
(11, 'Profesional I de Contrataciones y Provisión de Bs. y Ss.', 'CONTRATACIONES'),
(12, 'Profesional I de Tesoreria', 'TESORERIA'),
(13, 'Profesional I de Cobranzas', 'COBRANZAS'),
(14, 'Profesional I de Planificación y Presupuestos', 'PRESUPUESTOS'),
(15, 'Profesional I de Sistemas Informáticos y Redes', 'SISTEMAS'),
(16, 'Encargado Área de Mantenimiento y Reparación', 'AREA MANTENIMIENTO'),
(17, 'Recepcionista 1', 'RECEPCION HOTEL'),
(18, 'Recaudador de Valores 1', 'AUXILIAR JDTO'),
(19, 'Administrador del Sistema', 'ADM');

--
-- Índices para tablas volcadas
--

--
-- Indices de la tabla `correspondencia`
--
ALTER TABLE `correspondencia`
  ADD PRIMARY KEY (`id`),
  ADD KEY `fk_remitente_funcionario` (`remitente_id`),
  ADD KEY `fk_funcionario_enturno` (`idfuncionario_enturno`);

--
-- Indices de la tabla `derivacion`
--
ALTER TABLE `derivacion`
  ADD PRIMARY KEY (`id`),
  ADD KEY `correspondencia_id` (`id_correspondencia`),
  ADD KEY `usuario_id` (`id_funcionario`);

--
-- Indices de la tabla `funcionario`
--
ALTER TABLE `funcionario`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `usuario` (`usuario`),
  ADD KEY `puesto_id` (`id_puesto`);

--
-- Indices de la tabla `puesto`
--
ALTER TABLE `puesto`
  ADD PRIMARY KEY (`id`);

--
-- AUTO_INCREMENT de las tablas volcadas
--

--
-- AUTO_INCREMENT de la tabla `correspondencia`
--
ALTER TABLE `correspondencia`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT de la tabla `derivacion`
--
ALTER TABLE `derivacion`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- AUTO_INCREMENT de la tabla `funcionario`
--
ALTER TABLE `funcionario`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=19;

--
-- AUTO_INCREMENT de la tabla `puesto`
--
ALTER TABLE `puesto`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=20;

--
-- Restricciones para tablas volcadas
--

--
-- Filtros para la tabla `correspondencia`
--
ALTER TABLE `correspondencia`
  ADD CONSTRAINT `fk_funcionario_enturno` FOREIGN KEY (`idfuncionario_enturno`) REFERENCES `funcionario` (`id`),
  ADD CONSTRAINT `fk_remitente_funcionario` FOREIGN KEY (`remitente_id`) REFERENCES `funcionario` (`id`);

--
-- Filtros para la tabla `derivacion`
--
ALTER TABLE `derivacion`
  ADD CONSTRAINT `derivacion_ibfk_1` FOREIGN KEY (`id_correspondencia`) REFERENCES `correspondencia` (`id`),
  ADD CONSTRAINT `derivacion_ibfk_2` FOREIGN KEY (`id_funcionario`) REFERENCES `funcionario` (`id`);

--
-- Filtros para la tabla `funcionario`
--
ALTER TABLE `funcionario`
  ADD CONSTRAINT `fk_funcionario_puesto` FOREIGN KEY (`id_puesto`) REFERENCES `puesto` (`id`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;

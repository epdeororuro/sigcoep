-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Servidor: 127.0.0.1
-- Tiempo de generación: 17-04-2026 a las 18:57:16
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
-- Estructura de tabla para la tabla `area`
--

CREATE TABLE `area` (
  `id` int(11) NOT NULL,
  `nombre` varchar(255) NOT NULL,
  `jefe_puesto_id` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `area`
--

INSERT INTO `area` (`id`, `nombre`, `jefe_puesto_id`) VALUES
(1, 'Gerencia General', 1),
(2, 'Jefatura Departamento de Administracion y Finanzas', 5),
(3, 'Jefatura Departamento de Operaciones Hotel Terminal', 6),
(4, 'Jefatura Departamento de Operaciones Terminal', 7);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `correspondencia`
--

CREATE TABLE `correspondencia` (
  `id` int(11) NOT NULL,
  `hojaruta` varchar(255) DEFAULT NULL,
  `remitente_id` int(11) DEFAULT NULL,
  `remitente_externo` varchar(255) DEFAULT NULL,
  `idfuncionario_enturno` int(11) DEFAULT NULL,
  `tipo_remitente` enum('interno','externo') DEFAULT NULL,
  `remitente` varchar(255) DEFAULT NULL,
  `referencia` text DEFAULT NULL,
  `fojas` int(11) DEFAULT NULL,
  `anexo` varchar(255) NOT NULL,
  `fecha_registro` timestamp NOT NULL DEFAULT current_timestamp(),
  `fecha_conclusion` timestamp NULL DEFAULT NULL,
  `foto` varchar(255) DEFAULT NULL,
  `estado` varchar(255) DEFAULT NULL,
  `actualizado_en` timestamp NOT NULL DEFAULT current_timestamp(),
  `eliminado_en` timestamp NULL DEFAULT NULL,
  `agrupado_en` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `correspondencia`
--

INSERT INTO `correspondencia` (`id`, `hojaruta`, `remitente_id`, `remitente_externo`, `idfuncionario_enturno`, `tipo_remitente`, `remitente`, `referencia`, `fojas`, `anexo`, `fecha_registro`, `fecha_conclusion`, `foto`, `estado`, `actualizado_en`, `eliminado_en`, `agrupado_en`) VALUES
(1, '1/2026', 9, NULL, 2, 'interno', 'David Ticona Cabrera', 'Mantenimiento de CPU', 1, '', '2026-04-17 16:23:53', NULL, '2026/1-2026.png', 'Iniciado', '2026-04-17 16:23:58', NULL, NULL);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `derivacion`
--

CREATE TABLE `derivacion` (
  `id` int(11) NOT NULL,
  `id_correspondencia` int(11) NOT NULL,
  `id_funcionario` int(11) NOT NULL,
  `fecha_derivacion` timestamp NOT NULL DEFAULT current_timestamp(),
  `fecha_entrega_derivacion` timestamp NULL DEFAULT NULL,
  `instruccion_adicional` text DEFAULT NULL,
  `fojas` varchar(255) DEFAULT NULL,
  `anexo` varchar(255) DEFAULT NULL,
  `caracter` varchar(25) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `derivacion`
--

INSERT INTO `derivacion` (`id`, `id_correspondencia`, `id_funcionario`, `fecha_derivacion`, `fecha_entrega_derivacion`, `instruccion_adicional`, `fojas`, `anexo`, `caracter`) VALUES
(1, 1, 2, '2026-04-17 16:23:58', NULL, 'Para su atención', '1', NULL, 'Para conocimiento');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `derivacion_grupo`
--

CREATE TABLE `derivacion_grupo` (
  `id` int(11) NOT NULL,
  `correspondencia_id` int(11) NOT NULL,
  `creado_por` int(11) NOT NULL,
  `responsable_id` int(11) NOT NULL,
  `fecha_creacion` datetime NOT NULL DEFAULT current_timestamp(),
  `fecha_limite` datetime DEFAULT NULL,
  `estado` enum('activo','en_proceso','consolidado','cerrado') DEFAULT 'activo'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `derivacion_grupo_detalle`
--

CREATE TABLE `derivacion_grupo_detalle` (
  `id` int(11) NOT NULL,
  `derivacion_grupo_id` int(11) NOT NULL,
  `funcionario_id` int(11) NOT NULL,
  `es_principal` tinyint(1) DEFAULT 0,
  `estado` enum('pendiente','enviado','aprobado','rechazado') DEFAULT 'pendiente',
  `fecha_respuesta` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

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
  `contrasenia` varchar(255) NOT NULL,
  `rol` varchar(25) DEFAULT NULL,
  `id_puesto` int(11) NOT NULL,
  `id_area` int(11) DEFAULT NULL,
  `estado` varchar(12) NOT NULL,
  `creado_en` timestamp NOT NULL DEFAULT current_timestamp(),
  `actualizado_en` timestamp NOT NULL DEFAULT current_timestamp(),
  `eliminado_en` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `funcionario`
--

INSERT INTO `funcionario` (`id`, `ci`, `nombre`, `paterno`, `materno`, `usuario`, `password`, `contrasenia`, `rol`, `id_puesto`, `id_area`, `estado`, `creado_en`, `actualizado_en`, `eliminado_en`) VALUES
(1, 123456789, 'Superadmin', ' ', ' ', 'admin', '$2y$10$29Br5HbBSerZ6YYH6ekGvOyfs4rFkJpRHET9fUbGnHG7zSXbkuWOG', '123456789', 'Administrador', 19, NULL, 'Activo', '2026-02-26 22:56:07', '2026-03-06 05:25:07', '2026-02-26 22:56:07'),
(2, 7343846, 'Elizabeth', 'Martinez', 'Achacollo', 'emartinez', '$2y$10$H05dXZb8DvWcIMMmkkG4ZurvPN4aSTa7fvTgZl6zFPebxwN86jo4G', '7343846', 'Gerente', 1, NULL, 'Activo', '2026-02-27 00:38:37', '2026-02-28 05:36:32', '2026-02-27 00:38:37'),
(3, 7200300, 'Mirian', 'Rada', 'Lopez', 'mrada', '$2y$10$pDn5NQVkE8hGRMc7qseH/.OLYcuCQCzwu.svmM0icxjE2uN9WZ5cu', '5067188', 'Administrativo', 2, 1, 'Activo', '2026-02-27 00:39:16', '2026-03-10 04:37:08', '2026-02-27 00:39:16'),
(4, 3544712, 'Carmen Marisol', 'Rufino', 'Segovia', 'crufino', '$2y$10$bdDF93ECe1JCt8PPymzuZelsoa.T6R5aKt5j2KTIiGoTKroJWOjlu', '3544712', 'Administrativo', 3, 1, 'Activo', '2026-02-27 00:40:56', '2026-03-06 05:29:08', '2026-02-27 00:40:56'),
(5, 5778923, 'Maricruz Sara', 'Mamani', 'Nieto', 'mmamani', '$2y$10$nX3nj7RQ49ie9eb.UIeFr.SpylO0NLRh5toDSepa/WHm0OXmGTMsS', '5778923', 'Administrativo', 4, 1, 'Activo', '2026-02-27 00:41:32', '2026-03-10 05:02:52', '2026-02-27 00:41:32'),
(6, 4058090, 'Belinda', 'Perez', 'Ayma', 'bperez', '$2y$10$pI2mgOqOa8CNOlhknSBoEujEAPl2Ydun7GOUJ0u4n8/bzWZzpc0eW', '4058090', 'Administrativo', 5, 1, 'Activo', '2026-02-27 00:41:55', '2026-03-06 05:56:41', '2026-02-27 00:41:55'),
(7, 7260666, 'Erwin Jorge', 'Gonzales', 'Rioja', 'egonzales', '$2y$10$vOp3UWdnIdm7wVCRXcHnIekGysrhSdFa0MmOxL3o5l861svrk/y5.', '7260666', 'Administrativo', 7, 1, 'Activo', '2026-02-27 00:42:23', '2026-03-06 05:57:04', '2026-02-27 00:42:23'),
(8, 5732101, 'Carlos G. ', 'Rodriguez', 'Rocha', 'crodriguez', '$2y$10$f1waFtdvwLtu.75fCvikZ..PZed/tMBTXdzpzRBcD.CJuG7mU1iLW', '5732101', 'Administrativo', 6, 1, 'Activo', '2026-02-27 00:42:46', '2026-03-06 05:40:54', '2026-02-27 00:42:46'),
(9, 7423343, 'David', 'Ticona', 'Cabrera', 'dticona', '$2y$10$l2u8Jzfvesd3XyvmUH61u.SMT6ftzanbjGc/igjbHTw8TnUjeeWMe', '7423343', 'Administrativo', 16, 1, 'Activo', '2026-02-27 00:44:20', '2026-03-06 05:57:32', '2026-02-27 00:44:20'),
(10, 7270861, 'Jeanneth Angelica', 'Chambi', 'Chinche', 'jchambi', '$2y$10$RQHIsO/Exx.HS0.BPZX5MOx6RnP5VscEMBAK2DUQYVBxlSRkcGj0q', '7270861', 'Administrativo', 8, 2, 'Activo', '2026-02-27 00:44:34', '2026-03-06 05:29:39', '2026-02-27 00:44:34'),
(11, 13857686, 'Guadalupe', 'Gutierrez', 'Mamani', 'ggutierrez', '$2y$10$JE8eYoGXgF5Fax9FOuxL..LV3c/iDuDI.niwUTrfoUIwdkTUOVXN.', '13857686', 'Administrativo', 10, 2, 'Activo', '2026-02-27 00:45:23', '2026-03-06 05:44:46', '2026-03-06 00:55:33'),
(12, 5755448, 'Marina', 'Alegre', 'Mamani', 'malegre', '$2y$10$vX7ZrTcnhqkARc2VA766luM8AXxb/fGyubNLnUyWPyVaVOttZiskS', '5755448', 'Administrativo', 13, 2, 'Activo', '2026-02-27 00:45:57', '2026-03-06 05:58:00', '2026-02-27 00:45:57'),
(13, 7307898, 'Maria Lizeth', 'Colque', 'Rivera', 'mcolque', '$2y$10$/LxJmuqcS34qDcJedXRCe.nAlU5wARdrWAKRvAHRnV89zJmdpTzR6', '73007898', 'Administrativo', 9, NULL, 'Activo', '2026-02-27 00:47:04', '2026-03-06 05:58:26', '2026-02-27 00:47:04'),
(14, 7403044, 'Reynaldo Jesus', 'Flores', 'Jaillita', 'rflores', '$2y$10$igxATlsz5.CDcbRJrWIAqubDhq9lGgKGmVVU39Frxv3kJUyiI89rW', '7403044', 'Administrativo', 15, NULL, 'Activo', '2026-02-27 00:47:26', '2026-03-10 05:02:28', '2026-02-27 00:47:26'),
(15, 7292221, 'Milton Jose', 'Torrez', 'Alegre', 'mtorrez', '$2y$10$hDv4SpqzrnQqPhE4sGahJu0N30GQG48AcAyrIwRlNEqaee8/b0G.S', '7292221', 'Administrativo', 11, NULL, 'Activo', '2026-02-27 00:47:57', '2026-03-06 05:36:54', '2026-02-27 00:47:57'),
(16, 7376273, 'Marina Ana', 'Alejandro', 'Ayala', 'malejandro', '$2y$10$jmEogWqLr1LPVNvsxVWKAu3703YKbHBu10dao3fdBd1098TnWhhBG', '7376273', 'Administrativo', 12, NULL, 'Activo', '2026-02-27 00:48:17', '2026-03-06 05:30:51', '2026-02-27 00:48:17'),
(17, 4069420, 'Scarleth Shirley', 'Encinas', 'Colque ', 's-', '$2y$10$pm401iIhuE448FnFw7AYYebspudJBLOD8E8pG2qAokPvYSF6KDaci', '4069420', 'Administrativo', 17, NULL, 'Activo', '2026-02-27 00:49:01', '2026-03-06 05:45:52', '2026-02-27 00:49:01'),
(18, 4060082, 'Jorge', 'Quillaguaman', '-', 'jquillaguaman', '$2y$10$/gvCssPtY82XuQRSHJRW3.IISXeoJEVub51PCNwQ5zNV38iJ/4miC', '4060082', 'Administrativo', 18, NULL, 'Activo', '2026-02-27 00:49:23', '2026-03-06 05:44:20', '2026-02-27 00:49:23'),
(19, 123456, 'Ventanilla/Recepción', 'Unica', 'EPDEOR', 'vunica', '$2y$10$dGoIwThTAXGuQaegsSGaAelM5H04dLyRg3LihjPL.BLecvOx6qdeS', '123456', 'Secretaria', 19, NULL, 'Activo', '2026-03-06 22:10:05', '2026-03-06 22:10:05', '2026-03-06 22:10:05'),
(20, 987654, 'Archivista', 'Central', 'EPDEOR', 'acentral', '$2y$10$9NzNigIqVtMwutmweoX2LeuGZ3JhQf80iCxrjikFFFr/jiCyGJ9bu', '987654', 'Archivista Central', 10, NULL, 'Activo', '2026-04-01 17:53:02', '2026-04-01 17:53:02', NULL);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `historial_impresiones`
--

CREATE TABLE `historial_impresiones` (
  `id` int(11) NOT NULL,
  `id_funcionario` int(11) NOT NULL,
  `id_derivacion` int(11) NOT NULL,
  `numero_historial` int(11) NOT NULL,
  `fecha_creacion` datetime NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `informes`
--

CREATE TABLE `informes` (
  `id` int(11) NOT NULL,
  `derivacion_grupo_detalle_id` int(11) NOT NULL,
  `contenido` text DEFAULT NULL,
  `archivo_adjunto` varchar(255) DEFAULT NULL,
  `observaciones` text DEFAULT NULL,
  `estado` enum('enviado','aprobado','rechazado') DEFAULT 'enviado',
  `fecha` datetime NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `puesto`
--

CREATE TABLE `puesto` (
  `id` int(11) NOT NULL,
  `descripcion` varchar(70) DEFAULT NULL,
  `sigla` varchar(25) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `puesto`
--

INSERT INTO `puesto` (`id`, `descripcion`, `sigla`) VALUES
(1, 'Gerencia General', 'GERENCIA GENERAL'),
(2, 'Asesorí­a Legal', 'ASESORIA LEGAL'),
(3, 'Auditoria Interna', 'UNIDAD DE AUDITORIA INT.'),
(4, 'Secretaria Ejecutiva', 'SEC. EJE.'),
(5, 'Jefe Dpto. de Administración y Finanzas', 'JDAF'),
(6, 'Jefe Dpto. de Operaciones Hotel Terminal', 'JDOHT'),
(7, 'Jefe Dpto. de Operaciones Terminal', 'JDOT'),
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
(19, 'Administrador del Sistema', 'ADM'),
(20, 'Responsable de Proceso de Contratacion Menor', 'RPA'),
(21, 'Responsable de Proceso de Contratacion Mayor', 'RPC');

--
-- Índices para tablas volcadas
--

--
-- Indices de la tabla `area`
--
ALTER TABLE `area`
  ADD PRIMARY KEY (`id`),
  ADD KEY `fk_area_puesto` (`jefe_puesto_id`);

--
-- Indices de la tabla `correspondencia`
--
ALTER TABLE `correspondencia`
  ADD PRIMARY KEY (`id`),
  ADD KEY `remitente_id` (`remitente_id`),
  ADD KEY `idfuncionario_enturno` (`idfuncionario_enturno`),
  ADD KEY `fk_correspondencia_agrupado_en` (`agrupado_en`);

--
-- Indices de la tabla `derivacion`
--
ALTER TABLE `derivacion`
  ADD PRIMARY KEY (`id`),
  ADD KEY `id_correspondencia` (`id_correspondencia`),
  ADD KEY `id_funcionario` (`id_funcionario`);

--
-- Indices de la tabla `derivacion_grupo`
--
ALTER TABLE `derivacion_grupo`
  ADD PRIMARY KEY (`id`),
  ADD KEY `fk_grupo_correspondencia` (`correspondencia_id`),
  ADD KEY `fk_grupo_creador` (`creado_por`),
  ADD KEY `fk_grupo_responsable` (`responsable_id`);

--
-- Indices de la tabla `derivacion_grupo_detalle`
--
ALTER TABLE `derivacion_grupo_detalle`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `unq_grupo_funcionario` (`derivacion_grupo_id`,`funcionario_id`),
  ADD KEY `fk_detalle_grupo` (`derivacion_grupo_id`),
  ADD KEY `fk_detalle_funcionario` (`funcionario_id`);

--
-- Indices de la tabla `funcionario`
--
ALTER TABLE `funcionario`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `usuario` (`usuario`),
  ADD KEY `puesto_id` (`id_puesto`),
  ADD KEY `area_id` (`id_area`);

--
-- Indices de la tabla `historial_impresiones`
--
ALTER TABLE `historial_impresiones`
  ADD PRIMARY KEY (`id`),
  ADD KEY `id_funcionario` (`id_funcionario`),
  ADD KEY `id_derivacion` (`id_derivacion`);

--
-- Indices de la tabla `informes`
--
ALTER TABLE `informes`
  ADD PRIMARY KEY (`id`),
  ADD KEY `fk_informe_detalle` (`derivacion_grupo_detalle_id`);

--
-- Indices de la tabla `puesto`
--
ALTER TABLE `puesto`
  ADD PRIMARY KEY (`id`);

--
-- AUTO_INCREMENT de las tablas volcadas
--

--
-- AUTO_INCREMENT de la tabla `area`
--
ALTER TABLE `area`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT de la tabla `correspondencia`
--
ALTER TABLE `correspondencia`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT de la tabla `derivacion`
--
ALTER TABLE `derivacion`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT de la tabla `derivacion_grupo`
--
ALTER TABLE `derivacion_grupo`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `derivacion_grupo_detalle`
--
ALTER TABLE `derivacion_grupo_detalle`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `funcionario`
--
ALTER TABLE `funcionario`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=21;

--
-- AUTO_INCREMENT de la tabla `historial_impresiones`
--
ALTER TABLE `historial_impresiones`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `informes`
--
ALTER TABLE `informes`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `puesto`
--
ALTER TABLE `puesto`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=22;

--
-- Restricciones para tablas volcadas
--

--
-- Filtros para la tabla `area`
--
ALTER TABLE `area`
  ADD CONSTRAINT `fk_area_puesto` FOREIGN KEY (`jefe_puesto_id`) REFERENCES `puesto` (`id`) ON DELETE SET NULL ON UPDATE CASCADE;

--
-- Filtros para la tabla `correspondencia`
--
ALTER TABLE `correspondencia`
  ADD CONSTRAINT `fk_correspondencia_agrupado_en` FOREIGN KEY (`agrupado_en`) REFERENCES `correspondencia` (`id`) ON DELETE SET NULL ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_correspondencia_funcionario_remitente` FOREIGN KEY (`remitente_id`) REFERENCES `funcionario` (`id`),
  ADD CONSTRAINT `fk_correspondencia_funcionario_turno` FOREIGN KEY (`idfuncionario_enturno`) REFERENCES `funcionario` (`id`);

--
-- Filtros para la tabla `derivacion`
--
ALTER TABLE `derivacion`
  ADD CONSTRAINT `fk_derivacion_correspondencia` FOREIGN KEY (`id_correspondencia`) REFERENCES `correspondencia` (`id`),
  ADD CONSTRAINT `fk_derivacion_funcionario` FOREIGN KEY (`id_funcionario`) REFERENCES `funcionario` (`id`);

--
-- Filtros para la tabla `derivacion_grupo`
--
ALTER TABLE `derivacion_grupo`
  ADD CONSTRAINT `fk_grupo_correspondencia` FOREIGN KEY (`correspondencia_id`) REFERENCES `correspondencia` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_grupo_creador` FOREIGN KEY (`creado_por`) REFERENCES `funcionario` (`id`),
  ADD CONSTRAINT `fk_grupo_responsable` FOREIGN KEY (`responsable_id`) REFERENCES `funcionario` (`id`);

--
-- Filtros para la tabla `derivacion_grupo_detalle`
--
ALTER TABLE `derivacion_grupo_detalle`
  ADD CONSTRAINT `fk_detalle_funcionario` FOREIGN KEY (`funcionario_id`) REFERENCES `funcionario` (`id`),
  ADD CONSTRAINT `fk_detalle_grupo` FOREIGN KEY (`derivacion_grupo_id`) REFERENCES `derivacion_grupo` (`id`) ON DELETE CASCADE;

--
-- Filtros para la tabla `funcionario`
--
ALTER TABLE `funcionario`
  ADD CONSTRAINT `fk_funcionario_area` FOREIGN KEY (`id_area`) REFERENCES `area` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `fk_funcionario_puesto` FOREIGN KEY (`id_puesto`) REFERENCES `puesto` (`id`);

--
-- Filtros para la tabla `historial_impresiones`
--
ALTER TABLE `historial_impresiones`
  ADD CONSTRAINT `historial_impresiones_ibfk_1` FOREIGN KEY (`id_funcionario`) REFERENCES `funcionario` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `historial_impresiones_ibfk_2` FOREIGN KEY (`id_derivacion`) REFERENCES `derivacion` (`id`) ON DELETE CASCADE;

--
-- Filtros para la tabla `informes`
--
ALTER TABLE `informes`
  ADD CONSTRAINT `fk_informe_detalle` FOREIGN KEY (`derivacion_grupo_detalle_id`) REFERENCES `derivacion_grupo_detalle` (`id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;

-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Servidor: 127.0.0.1
-- Tiempo de generación: 06-03-2026 a las 19:21:13
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
  `fojas` varchar(255) NOT NULL,
  `fecha` timestamp NOT NULL DEFAULT current_timestamp(),
  `estado` varchar(255) NOT NULL,
  `actualizado_en` timestamp NULL DEFAULT NULL,
  `eliminado_en` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `correspondencia`
--

INSERT INTO `correspondencia` (`id`, `hojaruta`, `remitente_id`, `remitente_externo`, `idfuncionario_enturno`, `tipo_remitente`, `remitente`, `referencia`, `fojas`, `fecha`, `estado`, `actualizado_en`, `eliminado_en`) VALUES
(1, '001/2026', 14, NULL, 11, 'interno', 'Reynaldo Jesus Flores Jaillita', 'Solicitud de vaciado de Biometrico', '1', '2026-03-05 18:05:29', 'Derivado', '2026-03-05 20:54:44', NULL),
(2, '002/2026', 15, NULL, 6, 'interno', 'Milton Jose Torrez Alegre', 'Contrataciones pagos', '2', '2026-03-05 18:33:11', 'Derivado', '2026-03-05 19:19:48', NULL),
(3, '256/2026', 13, NULL, NULL, 'interno', 'Maria Lizeth Colque Rivera', 'Solicitud de Pago a la Gestora Publica de Seguridad Social de Largo Plazo Enero 2026', '10', '2026-03-06 15:30:59', 'Anulado', '2026-03-06 20:32:04', '2026-03-06 20:32:11'),
(4, '256/2026', 13, NULL, 6, 'interno', 'Maria Lizeth Colque Rivera', 'Solicitud de Pago a la Gestora Publica de Seguridad Social de Largo Plazo mes de Enero 2026', '6', '2026-03-06 15:33:22', 'Derivado', '2026-03-06 15:48:19', NULL),
(5, '07/2026', 5, NULL, 5, 'interno', 'Maricruz Sara Mamani Nieto', 'Solicitud Inicio de Proceso de Servicio de Fotocopia 2026', '6', '2026-03-06 15:52:45', 'Derivado', '2026-03-06 15:53:56', NULL),
(6, '', NULL, 'Delia Acero', NULL, 'externo', '', 'Reservado', '', '2026-03-06 17:36:41', 'Registrado', '2026-03-06 23:12:31', NULL),
(7, '289/2026', 10, NULL, NULL, 'interno', 'Jeanneth Angelica Chambi Chinche', '-\r\n', '1', '2026-03-06 17:47:43', 'Registrado', NULL, NULL);

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
  `fojas` varchar(255) NOT NULL,
  `caracter` varchar(25) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `derivacion`
--

INSERT INTO `derivacion` (`id`, `id_correspondencia`, `id_funcionario`, `fecha_derivacion`, `instruccion_adicional`, `fojas`, `caracter`) VALUES
(1, 1, 2, '2026-03-05 18:05:50', 'Para su atención', '1', 'Para conocimiento'),
(2, 1, 6, '2026-03-05 18:07:08', 'Para conocimiento y prosecucion, conforme a las formalidades que corresponda.', '1', 'Para conocimiento'),
(3, 2, 2, '2026-03-05 19:09:52', 'Para su atencion', '2', 'Para conocimiento'),
(4, 2, 7, '2026-03-05 19:13:22', 'Para su conocimiento de contratos', '2', 'Preparar Respuesta'),
(5, 2, 6, '2026-03-05 19:19:48', 'Para su atencion y conocimiento', '5', 'Para conocimiento'),
(6, 1, 14, '2026-03-05 20:52:43', 'Conforme a proveido numero 2 proceda de acuerdo y precision segun su informe', '1', 'Preparar Informe'),
(7, 1, 15, '2026-03-05 20:53:45', 'Para conocimiento', '2', 'Urgente'),
(8, 1, 11, '2026-03-05 20:54:43', 'Para su archivo', '6', 'Archivo'),
(9, 4, 2, '2026-03-06 15:33:36', 'Para su atención', '6', 'Para conocimiento'),
(10, 4, 6, '2026-03-06 15:37:00', 'Para su atención segun normativa vigente', '0', 'Procesar'),
(11, 4, 10, '2026-03-06 15:40:26', 'Para su atencion', '0', 'Procesar'),
(12, 4, 16, '2026-03-06 15:41:49', 'remito en atención a proveido 3', '2', 'Procesar'),
(13, 4, 6, '2026-03-06 15:43:12', 'remito priorización', '0', 'Procesar'),
(14, 4, 2, '2026-03-06 15:44:38', 'remito preventivo 20 para pago', '0', 'Procesar'),
(15, 4, 6, '2026-03-06 15:48:19', 'se remite proceso adjunto comprobante de pago', '1', 'Procesar'),
(16, 5, 2, '2026-03-06 15:52:52', 'Para su atención', '6', 'Para conocimiento'),
(17, 5, 5, '2026-03-06 15:53:56', 'Subsanar Observaciones', '6', 'Procesar');

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
(1, 123456789, 'Superadmin', ' ', ' ', 'admin', '$2y$10$29Br5HbBSerZ6YYH6ekGvOyfs4rFkJpRHET9fUbGnHG7zSXbkuWOG', 'Administrador', 19, 'Activo', '2026-02-26 18:56:07', '2026-03-06 01:25:07', '2026-02-26 18:56:07'),
(2, 7343846, 'Elizabeth', 'Martinez', 'Achacollo', 'emartinez', '$2y$10$H05dXZb8DvWcIMMmkkG4ZurvPN4aSTa7fvTgZl6zFPebxwN86jo4G', 'Gerente', 1, 'Activo', '2026-02-26 20:38:37', '2026-02-28 01:36:32', '2026-02-26 20:38:37'),
(3, 7200300, 'Mirian', 'Rada', 'Lopez', 'mrada', '$2y$10$XyCaXqajRZ2YS19R5iRrye2eXe3N5WLiseeXjheI1x3cRc243hn62', 'Administrativo', 2, 'Activo', '2026-02-26 20:39:16', '2026-02-28 01:36:49', '2026-02-26 20:39:16'),
(4, 3544712, 'Carmen Marisol', 'Rufino', 'Segovia', 'crufino', '$2y$10$bdDF93ECe1JCt8PPymzuZelsoa.T6R5aKt5j2KTIiGoTKroJWOjlu', 'Administrativo', 3, 'Activo', '2026-02-26 20:40:56', '2026-03-06 01:29:08', '2026-02-26 20:40:56'),
(5, 5778923, 'Maricruz Sara', 'Mamani', 'Nieto', 'mmamani', '$2y$10$r3SozgqCBlrg.tmMu/J/Ue4ioeccXMPs2zUph8ErZzCnyUDmDE35G', 'Administrativo', 4, 'Activo', '2026-02-26 20:41:32', '2026-03-06 23:11:27', '2026-02-26 20:41:32'),
(6, 4058090, 'Belinda', 'Perez', 'Ayma', 'bperez', '$2y$10$pI2mgOqOa8CNOlhknSBoEujEAPl2Ydun7GOUJ0u4n8/bzWZzpc0eW', 'Administrativo', 5, 'Activo', '2026-02-26 20:41:55', '2026-03-06 01:56:41', '2026-02-26 20:41:55'),
(7, 7260666, 'Erwin Jorge', 'Gonzales', 'Rioja', 'egonzales', '$2y$10$vOp3UWdnIdm7wVCRXcHnIekGysrhSdFa0MmOxL3o5l861svrk/y5.', 'Administrativo', 7, 'Activo', '2026-02-26 20:42:23', '2026-03-06 01:57:04', '2026-02-26 20:42:23'),
(8, 5732101, 'Carlos G. ', 'Rodriguez', 'Rocha', 'crodriguez', '$2y$10$f1waFtdvwLtu.75fCvikZ..PZed/tMBTXdzpzRBcD.CJuG7mU1iLW', 'Administrativo', 6, 'Activo', '2026-02-26 20:42:46', '2026-03-06 01:40:54', '2026-02-26 20:42:46'),
(9, 7423343, 'David', 'Ticona', 'Cabrera', 'dticona', '$2y$10$l2u8Jzfvesd3XyvmUH61u.SMT6ftzanbjGc/igjbHTw8TnUjeeWMe', 'Administrativo', 16, 'Activo', '2026-02-26 20:44:20', '2026-03-06 01:57:32', '2026-02-26 20:44:20'),
(10, 7270861, 'Jeanneth Angelica', 'Chambi', 'Chinche', 'jchambi', '$2y$10$RQHIsO/Exx.HS0.BPZX5MOx6RnP5VscEMBAK2DUQYVBxlSRkcGj0q', 'Administrativo', 8, 'Activo', '2026-02-26 20:44:34', '2026-03-06 01:29:39', '2026-02-26 20:44:34'),
(11, 13857686, 'Guadalupe', 'Gutierrez', 'Mamani', 'ggutierrez', '$2y$10$JE8eYoGXgF5Fax9FOuxL..LV3c/iDuDI.niwUTrfoUIwdkTUOVXN.', 'Administrativo', 10, 'Activo', '2026-02-26 20:45:23', '2026-03-06 01:44:46', '2026-03-05 20:55:33'),
(12, 5755448, 'Marina', 'Alegre', 'Mamani', 'malegre', '$2y$10$vX7ZrTcnhqkARc2VA766luM8AXxb/fGyubNLnUyWPyVaVOttZiskS', 'Administrativo', 13, 'Activo', '2026-02-26 20:45:57', '2026-03-06 01:58:00', '2026-02-26 20:45:57'),
(13, 7307898, 'Maria Lizeth', 'Colque', 'Rivera', 'mcolque', '$2y$10$/LxJmuqcS34qDcJedXRCe.nAlU5wARdrWAKRvAHRnV89zJmdpTzR6', 'Administrativo', 9, 'Activo', '2026-02-26 20:47:04', '2026-03-06 01:58:26', '2026-02-26 20:47:04'),
(14, 7403044, 'Reynaldo Jesus', 'Flores', 'Jaillita', 'rflores', '$2y$10$tGXRqOwXUGP7XYSS5a/yUepmSsuGeRDqd4w3NJ4rrM77LyMuRESPC', 'Administrativo', 15, 'Activo', '2026-02-26 20:47:26', '2026-02-26 20:47:26', '2026-02-26 20:47:26'),
(15, 7292221, 'Milton Jose', 'Torrez', 'Alegre', 'mtorrez', '$2y$10$hDv4SpqzrnQqPhE4sGahJu0N30GQG48AcAyrIwRlNEqaee8/b0G.S', 'Administrativo', 11, 'Activo', '2026-02-26 20:47:57', '2026-03-06 01:36:54', '2026-02-26 20:47:57'),
(16, 7376273, 'Marina Ana', 'Alejandro', 'Ayala', 'malejandro', '$2y$10$jmEogWqLr1LPVNvsxVWKAu3703YKbHBu10dao3fdBd1098TnWhhBG', 'Administrativo', 12, 'Activo', '2026-02-26 20:48:17', '2026-03-06 01:30:51', '2026-02-26 20:48:17'),
(17, 4069420, 'Scarleth Shirley', 'Encinas', 'Colque ', 's-', '$2y$10$pm401iIhuE448FnFw7AYYebspudJBLOD8E8pG2qAokPvYSF6KDaci', 'Administrativo', 17, 'Activo', '2026-02-26 20:49:01', '2026-03-06 01:45:52', '2026-02-26 20:49:01'),
(18, 4060082, 'Jorge', 'Quillaguaman', '-', 'jquillaguaman', '$2y$10$/gvCssPtY82XuQRSHJRW3.IISXeoJEVub51PCNwQ5zNV38iJ/4miC', 'Administrativo', 18, 'Activo', '2026-02-26 20:49:23', '2026-03-06 01:44:20', '2026-02-26 20:49:23'),
(19, 123456, 'Ventanilla/Recepción', 'Unica', 'EPDEOR', 'vunica', '$2y$10$dGoIwThTAXGuQaegsSGaAelM5H04dLyRg3LihjPL.BLecvOx6qdeS', 'Secretaria', 19, 'Activo', '2026-03-06 18:10:05', '2026-03-06 18:10:05', '2026-03-06 18:10:05');

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
(2, 'Asesorí­a Legal', 'ASESORIA LEGAL'),
(3, 'Auditoria Interna', 'UNIDAD DE AUDITORIA INT.'),
(4, 'Secretaria Ejecutiva', 'SEC. EJE.'),
(5, 'Jefatura Dpto. de Administración y Finanzas', 'JDAF'),
(6, 'Jefatura Dpto. de Operaciones Hotel Terminal', 'JDOHT'),
(7, 'Jefatura Dpto. de Operaciones Terminal', 'JDOT'),
(8, 'Encargado de Contabilidad', 'AREA CONTABILIDAD'),
(9, 'Profesional I de Recursos Humanos y Normas', 'RR.HH.'),
(10, 'Profesional I de Activos Fijos/Almacenes y Archivos', 'ACT.FIJ.-ALM-ARC'),
(11, 'Profesional I de Contrataciones y ProvisiÃƒÂ³n de Bs. y Ss.', 'CONTRATACIONES'),
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
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- AUTO_INCREMENT de la tabla `derivacion`
--
ALTER TABLE `derivacion`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=18;

--
-- AUTO_INCREMENT de la tabla `funcionario`
--
ALTER TABLE `funcionario`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=20;

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

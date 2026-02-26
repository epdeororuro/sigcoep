-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Servidor: 127.0.0.1
-- Tiempo de generaciÃ³n: 26-02-2026 a las 21:08:59
-- VersiÃ³n del servidor: 10.4.32-MariaDB
-- VersiÃ³n de PHP: 8.2.12

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
  `remitente` varchar(255) NOT NULL,
  `referencia` text NOT NULL,
  `fojas` int(11) NOT NULL,
  `fecha` timestamp NOT NULL DEFAULT current_timestamp(),
  `estado` varchar(255) NOT NULL,
  `actualizado_en` timestamp NULL DEFAULT NULL,
  `eliminado_en` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

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
  `rol` varchar(25) NOT NULL,
  `id_puesto` int(11) NOT NULL,
  `estado` varchar(12) NOT NULL DEFAULT 'Activo',
  `creado_en` timestamp NOT NULL DEFAULT current_timestamp(),
  `actualizado_en` timestamp NOT NULL DEFAULT current_timestamp(),
  `eliminado_en` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `funcionario`
--

INSERT INTO `funcionario` (`id`, `ci`, `nombre`, `paterno`, `materno`, `usuario`, `password`, `rol`, `id_puesto`, `estado`, `creado_en`, `actualizado_en`, `eliminado_en`) VALUES
(1, 123456789, 'Superadmin', ' ', ' ', 'admin', '$2y$10$29Br5HbBSerZ6YYH6ekGvOyfs4rFkJpRHET9fUbGnHG7zSXbkuWOG', 'Administrador', 1, 'Activo', '2026-02-26 18:56:07', '2026-02-26 18:56:07', '2026-02-26 18:56:07');

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
(2, 'AsesorÃ­a Legal', 'ASESORIA LEGAL'),
(3, 'Auditoria Interna', 'UNIDAD DE AUDITORIA INT.'),
(4, 'Secretaria Ejecutiva', 'SEC. EJE.'),
(5, 'Jefatura Dpto. de AdministraciÃ³n y Finanzas', 'JDAF'),
(6, 'Jefatura Dpto. de Operaciones Hotel Terminal', 'JDOHT'),
(7, 'Jefatura Dpto. de Operaciones Terminal', 'JDOT'),
(8, 'Encargado de Contabilidad', 'AREA CONTABILIDAD'),
(9, 'Profesional I de Recursos Humanos y Normas', 'RR.HH.'),
(10, 'Profesional I de Activos Fijos/Almacenes y Archivos', 'ACT.FIJ.-ALM-ARC'),
(11, 'Profesional I de Contrataciones y ProvisiÃ³n de Bs. y Ss.', 'CONTRATACIONES'),
(12, 'Profesional I de Tesoreria', 'TESORERIA'),
(13, 'Profesional I de Cobranzas', 'COBRANZAS'),
(14, 'Profesional I de PlanificaciÃ³n y Presupuestos', 'PRESUPUESTOS'),
(15, 'Profesional I de Sistemas InformÃ¡ticos y Redes', 'SISTEMAS'),
(16, 'Encargado Ãrea de Mantenimiento y ReparaciÃ³n', 'AREA MANTENIMIENTO'),
(17, 'Recepcionista 1', 'RECEPCION HOTEL'),
(18, 'Recaudador de Valores 1', 'AUXILIAR JDTO');

--
-- Ãndices para tablas volcadas
--

--
-- Indices de la tabla `correspondencia`
--
ALTER TABLE `correspondencia`
  ADD PRIMARY KEY (`id`);

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
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT de la tabla `derivacion`
--
ALTER TABLE `derivacion`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT de la tabla `funcionario`
--
ALTER TABLE `funcionario`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT de la tabla `puesto`
--
ALTER TABLE `puesto`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=19;

--
-- Restricciones para tablas volcadas
--

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

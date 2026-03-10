-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Servidor: 127.0.0.1
-- Tiempo de generación: 10-03-2026 a las 20:32:28
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
(19, 'Administrador del Sistema', 'ADM'),
(20, 'Responsable de Proceso de Contratacion Menor', 'RPA'),
(21, 'Responsable de Proceso de Contratacion Mayor', 'RPC');

--
-- Índices para tablas volcadas
--

--
-- Indices de la tabla `puesto`
--
ALTER TABLE `puesto`
  ADD PRIMARY KEY (`id`);

--
-- AUTO_INCREMENT de las tablas volcadas
--

--
-- AUTO_INCREMENT de la tabla `puesto`
--
ALTER TABLE `puesto`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=22;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;

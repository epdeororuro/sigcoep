-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Servidor: 127.0.0.1
-- Tiempo de generación: 10-03-2026 a las 20:32:12
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
  `fojas` int(255) NOT NULL,
  `anexo` varchar(255) NOT NULL,
  `fecha` timestamp NOT NULL DEFAULT current_timestamp(),
  `foto` varchar(255) NOT NULL,
  `estado` varchar(255) NOT NULL,
  `actualizado_en` timestamp NULL DEFAULT NULL,
  `eliminado_en` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `correspondencia`
--

INSERT INTO `correspondencia` (`id`, `hojaruta`, `remitente_id`, `remitente_externo`, `idfuncionario_enturno`, `tipo_remitente`, `remitente`, `referencia`, `fojas`, `anexo`, `fecha`, `foto`, `estado`, `actualizado_en`, `eliminado_en`) VALUES
(1, '001/2026', 14, NULL, 11, 'interno', 'Reynaldo Jesus Flores Jaillita', 'Solicitud de vaciado de Biometrico', 1, '', '2026-03-05 18:05:29', '', 'Derivado', '2026-03-05 20:54:44', NULL),
(2, '002/2026', 3, NULL, 6, 'interno', 'Mirian Rada Lopez', 'Contrataciones pagos', 2, '', '2026-03-05 18:33:11', '', 'Derivado', '2026-03-07 00:21:22', NULL),
(3, '256/2026', 13, NULL, NULL, 'interno', 'Maria Lizeth Colque Rivera', 'Solicitud de Pago a la Gestora Publica de Seguridad Social de Largo Plazo Enero 2026', 10, '', '2026-03-06 15:30:59', '', 'Anulado', '2026-03-06 20:32:04', '2026-03-06 20:32:11'),
(4, '256/2026', 13, NULL, 6, 'interno', 'Maria Lizeth Colque Rivera', 'Solicitud de Pago a la Gestora Publica de Seguridad Social de Largo Plazo mes de Enero 2026', 6, '', '2026-03-06 15:33:22', '', 'Derivado', '2026-03-06 15:48:19', NULL),
(5, '07/2026', 5, NULL, 14, 'interno', 'Maricruz Sara Mamani Nieto', 'Solicitud Inicio de Proceso de Servicio de Fotocopia 2026', 6, '', '2026-03-06 15:52:45', '', 'Derivado', '2026-03-09 20:29:28', NULL),
(6, '', NULL, 'Delia Acero', NULL, 'externo', '', 'Reservado', 0, '', '2026-03-06 17:36:41', '', 'Registrado', '2026-03-06 23:12:31', NULL),
(7, '289/2026', 10, NULL, NULL, 'interno', 'Jeanneth Angelica Chambi Chinche', '-\r\n', 1, '', '2026-03-06 17:47:43', '', 'Registrado', NULL, NULL),
(8, '8/2026', 9, NULL, NULL, 'interno', 'David Ticona Cabrera', 'cualuier', 1, '', '2026-03-09 16:22:43', 'corr_69aef3d3e58409.93243873.png', 'Registrado', NULL, NULL);

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
-- AUTO_INCREMENT de las tablas volcadas
--

--
-- AUTO_INCREMENT de la tabla `correspondencia`
--
ALTER TABLE `correspondencia`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- Restricciones para tablas volcadas
--

--
-- Filtros para la tabla `correspondencia`
--
ALTER TABLE `correspondencia`
  ADD CONSTRAINT `fk_funcionario_enturno` FOREIGN KEY (`idfuncionario_enturno`) REFERENCES `funcionario` (`id`),
  ADD CONSTRAINT `fk_remitente_funcionario` FOREIGN KEY (`remitente_id`) REFERENCES `funcionario` (`id`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;

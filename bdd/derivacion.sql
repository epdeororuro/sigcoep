-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Servidor: 127.0.0.1
-- Tiempo de generación: 10-03-2026 a las 21:44:15
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
-- Estructura de tabla para la tabla `derivacion`
--

CREATE TABLE `derivacion` (
  `id` int(11) NOT NULL,
  `id_correspondencia` int(11) NOT NULL,
  `id_funcionario` int(11) NOT NULL,
  `fecha_derivacion` timestamp NOT NULL DEFAULT current_timestamp(),
  `fecha_entrega_derivacion` timestamp NOT NULL DEFAULT current_timestamp(),
  `instruccion_adicional` text DEFAULT NULL,
  `fojas` varchar(255) NOT NULL,
  `anexo` varchar(255) NOT NULL,
  `caracter` varchar(25) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `derivacion`
--

INSERT INTO `derivacion` (`id`, `id_correspondencia`, `id_funcionario`, `fecha_derivacion`, `fecha_entrega_derivacion`, `instruccion_adicional`, `fojas`, `anexo`, `caracter`) VALUES
(1, 1, 2, '2026-03-05 18:05:50', '2026-03-10 19:36:30', 'Para su atención', '1', '', 'Para conocimiento'),
(2, 1, 6, '2026-03-05 18:07:08', '2026-03-10 19:36:30', 'Para conocimiento y prosecucion, conforme a las formalidades que corresponda.', '1', '', 'Para conocimiento'),
(3, 2, 2, '2026-03-05 19:09:52', '2026-03-10 19:36:30', 'Para su atencion', '2', '', 'Para conocimiento'),
(4, 2, 7, '2026-03-05 19:13:22', '2026-03-10 19:36:30', 'Para su conocimiento de contratos', '2', '', 'Preparar Respuesta'),
(5, 2, 6, '2026-03-05 19:19:48', '2026-03-10 19:36:30', 'Para su atencion y conocimiento', '5', '', 'Para conocimiento'),
(6, 1, 14, '2026-03-05 20:52:43', '2026-03-10 19:36:30', 'Conforme a proveido numero 2 proceda de acuerdo y precision segun su informe', '1', '', 'Preparar Informe'),
(7, 1, 15, '2026-03-05 20:53:45', '2026-03-10 19:36:30', 'Para conocimiento', '2', '', 'Urgente'),
(8, 1, 11, '2026-03-05 20:54:43', '2026-03-10 19:36:30', 'Para su archivo', '6', '', 'Archivo'),
(9, 4, 2, '2026-03-06 15:33:36', '2026-03-10 19:36:30', 'Para su atención', '6', '', 'Para conocimiento'),
(10, 4, 6, '2026-03-06 15:37:00', '2026-03-10 19:36:30', 'Para su atención segun normativa vigente', '0', '', 'Procesar'),
(11, 4, 10, '2026-03-06 15:40:26', '2026-03-10 19:36:30', 'Para su atencion', '0', '', 'Procesar'),
(12, 4, 16, '2026-03-06 15:41:49', '2026-03-10 19:36:30', 'remito en atención a proveido 3', '2', '', 'Procesar'),
(13, 4, 6, '2026-03-06 15:43:12', '2026-03-10 19:36:30', 'remito priorización', '0', '', 'Procesar'),
(14, 4, 2, '2026-03-06 15:44:38', '2026-03-10 19:36:30', 'remito preventivo 20 para pago', '0', '', 'Procesar'),
(15, 4, 6, '2026-03-06 15:48:19', '2026-03-10 19:36:30', 'se remite proceso adjunto comprobante de pago', '1', '', 'Procesar'),
(16, 5, 2, '2026-03-06 15:52:52', '2026-03-10 19:36:30', 'Para su atención', '6', '', 'Para conocimiento'),
(17, 5, 5, '2026-03-06 15:53:56', '2026-03-10 19:36:30', 'Subsanar Observaciones', '6', '', 'Procesar'),
(18, 5, 14, '2026-03-09 20:26:31', '2026-03-10 19:36:30', 'Para su conocimiento', '0', '', 'Preparar Respuesta'),
(19, 5, 14, '2026-03-09 20:29:27', '2026-03-10 19:36:30', 'Ampliación por motivos de baja medica', '1', '', 'Para conocimiento');

--
-- Índices para tablas volcadas
--

--
-- Indices de la tabla `derivacion`
--
ALTER TABLE `derivacion`
  ADD PRIMARY KEY (`id`),
  ADD KEY `correspondencia_id` (`id_correspondencia`),
  ADD KEY `usuario_id` (`id_funcionario`);

--
-- AUTO_INCREMENT de las tablas volcadas
--

--
-- AUTO_INCREMENT de la tabla `derivacion`
--
ALTER TABLE `derivacion`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=20;

--
-- Restricciones para tablas volcadas
--

--
-- Filtros para la tabla `derivacion`
--
ALTER TABLE `derivacion`
  ADD CONSTRAINT `derivacion_ibfk_1` FOREIGN KEY (`id_correspondencia`) REFERENCES `correspondencia` (`id`),
  ADD CONSTRAINT `derivacion_ibfk_2` FOREIGN KEY (`id_funcionario`) REFERENCES `funcionario` (`id`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;

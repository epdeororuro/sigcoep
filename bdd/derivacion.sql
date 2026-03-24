-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Servidor: 127.0.0.1
-- Tiempo de generaciÃ³n: 24-03-2026 a las 17:28:53
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
(1, 1, 2, '2026-03-21 15:02:46', NULL, 'Para su atenciÃ³n', '2', NULL, 'Para conocimiento'),
(2, 1, 6, '2026-03-21 15:15:12', '2026-03-21 15:16:30', 'Para su conocimiento', '0', NULL, 'Para conocimiento'),
(3, 1, 14, '2026-03-21 15:20:25', '2026-03-21 15:26:04', 'Para su atenciÃ³n al mantenimiento', '2', NULL, 'Procesar'),
(4, 1, 2, '2026-03-21 15:34:38', '2026-03-21 15:52:10', 'Para su conocimiento del informe tecnico', '3', NULL, 'Para conocimiento'),
(5, 1, 14, '2026-03-21 15:55:32', '2026-03-21 16:01:05', 'Para considerar adquisicion mediante proceso de adquisicion', '0', NULL, 'Procesar'),
(6, 1, 6, '2026-03-21 16:02:58', '2026-03-21 16:04:36', 'Se solicita certificacion presupuestaria.', '0', NULL, 'Procesar');

--
-- Ãndices para tablas volcadas
--

--
-- Indices de la tabla `derivacion`
--
ALTER TABLE `derivacion`
  ADD PRIMARY KEY (`id`),
  ADD KEY `id_correspondencia` (`id_correspondencia`),
  ADD KEY `id_funcionario` (`id_funcionario`);

--
-- AUTO_INCREMENT de las tablas volcadas
--

--
-- AUTO_INCREMENT de la tabla `derivacion`
--
ALTER TABLE `derivacion`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- Restricciones para tablas volcadas
--

--
-- Filtros para la tabla `derivacion`
--
ALTER TABLE `derivacion`
  ADD CONSTRAINT `fk_derivacion_correspondencia` FOREIGN KEY (`id_correspondencia`) REFERENCES `correspondencia` (`id`),
  ADD CONSTRAINT `fk_derivacion_funcionario` FOREIGN KEY (`id_funcionario`) REFERENCES `funcionario` (`id`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;

-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Servidor: 127.0.0.1
-- Tiempo de generación: 10-03-2026 a las 20:32:23
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
  `estado` varchar(12) NOT NULL,
  `creado_en` timestamp NOT NULL DEFAULT current_timestamp(),
  `actualizado_en` timestamp NOT NULL DEFAULT current_timestamp(),
  `eliminado_en` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `funcionario`
--

INSERT INTO `funcionario` (`id`, `ci`, `nombre`, `paterno`, `materno`, `usuario`, `password`, `contrasenia`, `rol`, `id_puesto`, `estado`, `creado_en`, `actualizado_en`, `eliminado_en`) VALUES
(1, 123456789, 'Superadmin', ' ', ' ', 'admin', '$2y$10$29Br5HbBSerZ6YYH6ekGvOyfs4rFkJpRHET9fUbGnHG7zSXbkuWOG', '123456789', 'Administrador', 19, 'Activo', '2026-02-26 18:56:07', '2026-03-06 01:25:07', '2026-02-26 18:56:07'),
(2, 7343846, 'Elizabeth', 'Martinez', 'Achacollo', 'emartinez', '$2y$10$H05dXZb8DvWcIMMmkkG4ZurvPN4aSTa7fvTgZl6zFPebxwN86jo4G', '7343846', 'Gerente', 1, 'Activo', '2026-02-26 20:38:37', '2026-02-28 01:36:32', '2026-02-26 20:38:37'),
(3, 7200300, 'Mirian', 'Rada', 'Lopez', 'mrada', '$2y$10$pDn5NQVkE8hGRMc7qseH/.OLYcuCQCzwu.svmM0icxjE2uN9WZ5cu', '5067188', 'Administrativo', 2, 'Activo', '2026-02-26 20:39:16', '2026-03-10 00:37:08', '2026-02-26 20:39:16'),
(4, 3544712, 'Carmen Marisol', 'Rufino', 'Segovia', 'crufino', '$2y$10$bdDF93ECe1JCt8PPymzuZelsoa.T6R5aKt5j2KTIiGoTKroJWOjlu', '3544712', 'Administrativo', 3, 'Activo', '2026-02-26 20:40:56', '2026-03-06 01:29:08', '2026-02-26 20:40:56'),
(5, 5778923, 'Maricruz Sara', 'Mamani', 'Nieto', 'mmamani', '$2y$10$nX3nj7RQ49ie9eb.UIeFr.SpylO0NLRh5toDSepa/WHm0OXmGTMsS', '5778923', 'Administrativo', 4, 'Activo', '2026-02-26 20:41:32', '2026-03-10 01:02:52', '2026-02-26 20:41:32'),
(6, 4058090, 'Belinda', 'Perez', 'Ayma', 'bperez', '$2y$10$pI2mgOqOa8CNOlhknSBoEujEAPl2Ydun7GOUJ0u4n8/bzWZzpc0eW', '4058090', 'Administrativo', 5, 'Activo', '2026-02-26 20:41:55', '2026-03-06 01:56:41', '2026-02-26 20:41:55'),
(7, 7260666, 'Erwin Jorge', 'Gonzales', 'Rioja', 'egonzales', '$2y$10$vOp3UWdnIdm7wVCRXcHnIekGysrhSdFa0MmOxL3o5l861svrk/y5.', '7260666', 'Administrativo', 7, 'Activo', '2026-02-26 20:42:23', '2026-03-06 01:57:04', '2026-02-26 20:42:23'),
(8, 5732101, 'Carlos G. ', 'Rodriguez', 'Rocha', 'crodriguez', '$2y$10$f1waFtdvwLtu.75fCvikZ..PZed/tMBTXdzpzRBcD.CJuG7mU1iLW', '5732101', 'Administrativo', 6, 'Activo', '2026-02-26 20:42:46', '2026-03-06 01:40:54', '2026-02-26 20:42:46'),
(9, 7423343, 'David', 'Ticona', 'Cabrera', 'dticona', '$2y$10$l2u8Jzfvesd3XyvmUH61u.SMT6ftzanbjGc/igjbHTw8TnUjeeWMe', '7423343', 'Administrativo', 16, 'Activo', '2026-02-26 20:44:20', '2026-03-06 01:57:32', '2026-02-26 20:44:20'),
(10, 7270861, 'Jeanneth Angelica', 'Chambi', 'Chinche', 'jchambi', '$2y$10$RQHIsO/Exx.HS0.BPZX5MOx6RnP5VscEMBAK2DUQYVBxlSRkcGj0q', '7270861', 'Administrativo', 8, 'Activo', '2026-02-26 20:44:34', '2026-03-06 01:29:39', '2026-02-26 20:44:34'),
(11, 13857686, 'Guadalupe', 'Gutierrez', 'Mamani', 'ggutierrez', '$2y$10$JE8eYoGXgF5Fax9FOuxL..LV3c/iDuDI.niwUTrfoUIwdkTUOVXN.', '13857686', 'Administrativo', 10, 'Activo', '2026-02-26 20:45:23', '2026-03-06 01:44:46', '2026-03-05 20:55:33'),
(12, 5755448, 'Marina', 'Alegre', 'Mamani', 'malegre', '$2y$10$vX7ZrTcnhqkARc2VA766luM8AXxb/fGyubNLnUyWPyVaVOttZiskS', '5755448', 'Administrativo', 13, 'Activo', '2026-02-26 20:45:57', '2026-03-06 01:58:00', '2026-02-26 20:45:57'),
(13, 7307898, 'Maria Lizeth', 'Colque', 'Rivera', 'mcolque', '$2y$10$/LxJmuqcS34qDcJedXRCe.nAlU5wARdrWAKRvAHRnV89zJmdpTzR6', '73007898', 'Administrativo', 9, 'Activo', '2026-02-26 20:47:04', '2026-03-06 01:58:26', '2026-02-26 20:47:04'),
(14, 7403044, 'Reynaldo Jesus', 'Flores', 'Jaillita', 'rflores', '$2y$10$igxATlsz5.CDcbRJrWIAqubDhq9lGgKGmVVU39Frxv3kJUyiI89rW', '7403044', 'Administrativo', 15, 'Activo', '2026-02-26 20:47:26', '2026-03-10 01:02:28', '2026-02-26 20:47:26'),
(15, 7292221, 'Milton Jose', 'Torrez', 'Alegre', 'mtorrez', '$2y$10$hDv4SpqzrnQqPhE4sGahJu0N30GQG48AcAyrIwRlNEqaee8/b0G.S', '7292221', 'Administrativo', 11, 'Activo', '2026-02-26 20:47:57', '2026-03-06 01:36:54', '2026-02-26 20:47:57'),
(16, 7376273, 'Marina Ana', 'Alejandro', 'Ayala', 'malejandro', '$2y$10$jmEogWqLr1LPVNvsxVWKAu3703YKbHBu10dao3fdBd1098TnWhhBG', '7376273', 'Administrativo', 12, 'Activo', '2026-02-26 20:48:17', '2026-03-06 01:30:51', '2026-02-26 20:48:17'),
(17, 4069420, 'Scarleth Shirley', 'Encinas', 'Colque ', 's-', '$2y$10$pm401iIhuE448FnFw7AYYebspudJBLOD8E8pG2qAokPvYSF6KDaci', '4069420', 'Administrativo', 17, 'Activo', '2026-02-26 20:49:01', '2026-03-06 01:45:52', '2026-02-26 20:49:01'),
(18, 4060082, 'Jorge', 'Quillaguaman', '-', 'jquillaguaman', '$2y$10$/gvCssPtY82XuQRSHJRW3.IISXeoJEVub51PCNwQ5zNV38iJ/4miC', '4060082', 'Administrativo', 18, 'Activo', '2026-02-26 20:49:23', '2026-03-06 01:44:20', '2026-02-26 20:49:23'),
(19, 123456, 'Ventanilla/Recepción', 'Unica', 'EPDEOR', 'vunica', '$2y$10$dGoIwThTAXGuQaegsSGaAelM5H04dLyRg3LihjPL.BLecvOx6qdeS', '123456', 'Secretaria', 19, 'Activo', '2026-03-06 18:10:05', '2026-03-06 18:10:05', '2026-03-06 18:10:05');

--
-- Índices para tablas volcadas
--

--
-- Indices de la tabla `funcionario`
--
ALTER TABLE `funcionario`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `usuario` (`usuario`),
  ADD KEY `puesto_id` (`id_puesto`);

--
-- AUTO_INCREMENT de las tablas volcadas
--

--
-- AUTO_INCREMENT de la tabla `funcionario`
--
ALTER TABLE `funcionario`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=20;

--
-- Restricciones para tablas volcadas
--

--
-- Filtros para la tabla `funcionario`
--
ALTER TABLE `funcionario`
  ADD CONSTRAINT `fk_funcionario_puesto` FOREIGN KEY (`id_puesto`) REFERENCES `puesto` (`id`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;

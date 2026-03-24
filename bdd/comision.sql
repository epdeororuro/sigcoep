-- Estructura para la tabla `comision`
CREATE TABLE IF NOT EXISTS `comision` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `nombre` varchar(255) NOT NULL,
  `descripcion` text DEFAULT NULL,
  `responsable_id` int(11) NOT NULL,
  `estado` varchar(15) NOT NULL DEFAULT 'Activo',
  `creado_en` timestamp NOT NULL DEFAULT current_timestamp(),
  `actualizado_en` timestamp NULL DEFAULT NULL,
  `eliminado_en` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `fk_comision_responsable` (`responsable_id`),
  CONSTRAINT `fk_comision_responsable` FOREIGN KEY (`responsable_id`) REFERENCES `funcionario` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Estructura para la tabla pivote `comision_miembro` (Integrantes)
CREATE TABLE IF NOT EXISTS `comision_miembro` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `comision_id` int(11) NOT NULL,
  `funcionario_id` int(11) NOT NULL,
  PRIMARY KEY (`id`),
  CONSTRAINT `fk_cm_comision` FOREIGN KEY (`comision_id`) REFERENCES `comision` (`id`) ON DELETE CASCADE,
  CONSTRAINT `fk_cm_funcionario` FOREIGN KEY (`funcionario_id`) REFERENCES `funcionario` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
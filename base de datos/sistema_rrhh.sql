-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Feb 23, 2026 at 04:15 PM
-- Server version: 10.4.32-MariaDB
-- PHP Version: 8.0.30

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `sistema_rrhh`
--

-- --------------------------------------------------------

--
-- Table structure for table `catalogo_areas`
--

CREATE TABLE `catalogo_areas` (
  `id` int(11) NOT NULL,
  `nombre` varchar(100) NOT NULL,
  `descripcion` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `catalogo_areas`
--

INSERT INTO `catalogo_areas` (`id`, `nombre`, `descripcion`) VALUES
(1, 'Registros médicos', NULL),
(2, 'Jefatura', NULL),
(3, 'Admisión', NULL),
(4, 'Estadística', NULL),
(5, 'Cuantitativa', NULL),
(6, 'Consulta Externa', NULL),
(7, 'Archivo', NULL),
(8, 'Secretaria', NULL);

-- --------------------------------------------------------

--
-- Table structure for table `catalogo_cargos`
--

CREATE TABLE `catalogo_cargos` (
  `id` int(11) NOT NULL,
  `nombre` varchar(100) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `catalogo_cargos`
--

INSERT INTO `catalogo_cargos` (`id`, `nombre`) VALUES
(1, 'Jefe del departamento'),
(2, 'Sub Jefe '),
(3, 'Sub Jefe / Estadistica'),
(4, 'Auxiliar de Registros Médicos'),
(5, 'Encargada de Estadistica');

-- --------------------------------------------------------

--
-- Table structure for table `catalogo_puestos_nominales`
--

CREATE TABLE `catalogo_puestos_nominales` (
  `id` int(11) NOT NULL,
  `codigo` varchar(20) DEFAULT NULL,
  `nombre` varchar(100) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `catalogo_puestos_nominales`
--

INSERT INTO `catalogo_puestos_nominales` (`id`, `codigo`, `nombre`) VALUES
(1, '011', 'Oficinista I'),
(2, '011', 'Oficinista II'),
(3, '011', 'Oficinista III'),
(4, '011', 'Oficinista IV'),
(5, '189', 'Contratista - Servicios Técnicos'),
(6, '011', 'Operativo III resguardo y Vigilancia');

-- --------------------------------------------------------

--
-- Table structure for table `catalogo_renglones`
--

CREATE TABLE `catalogo_renglones` (
  `id` int(11) NOT NULL,
  `codigo` varchar(10) NOT NULL,
  `descripcion` varchar(100) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `catalogo_renglones`
--

INSERT INTO `catalogo_renglones` (`id`, `codigo`, `descripcion`) VALUES
(1, '011', 'Personal Permanente'),
(2, '189', 'Personal por Contrato'),
(3, '182', 'Servicios Técnicos');

-- --------------------------------------------------------

--
-- Table structure for table `empleados`
--

CREATE TABLE `empleados` (
  `id` int(11) NOT NULL,
  `dpi` varchar(15) NOT NULL,
  `nit` varchar(15) DEFAULT NULL,
  `nombres` varchar(100) NOT NULL,
  `apellidos` varchar(100) NOT NULL,
  `fecha_nacimiento` date NOT NULL,
  `genero` enum('M','F') NOT NULL,
  `tipo_sangre` varchar(5) DEFAULT NULL,
  `telefono` varchar(20) NOT NULL,
  `correo_electronico` varchar(100) DEFAULT NULL,
  `direccion` text NOT NULL,
  `departamento` varchar(100) DEFAULT NULL,
  `municipio` varchar(100) DEFAULT NULL,
  `contacto_emergencia_nombre` varchar(100) DEFAULT NULL,
  `contacto_emergencia_telefono` varchar(20) DEFAULT NULL,
  `contacto_emergencia_nombre2` varchar(100) DEFAULT NULL,
  `contacto_emergencia_telefono2` varchar(100) DEFAULT NULL,
  `fecha_inicio_labores` date NOT NULL,
  `fecha_ultimo_ascenso` date DEFAULT NULL,
  `id_renglon` int(11) NOT NULL,
  `id_area` int(11) NOT NULL,
  `id_puesto_nominal` int(11) NOT NULL,
  `id_puesto_funcional` int(11) NOT NULL,
  `foto_perfil` varchar(255) DEFAULT NULL,
  `estado` enum('Activo','Suspendido','Vacaciones','Baja') DEFAULT 'Activo',
  `creado_por` varchar(50) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `empleados`
--

INSERT INTO `empleados` (`id`, `dpi`, `nit`, `nombres`, `apellidos`, `fecha_nacimiento`, `genero`, `tipo_sangre`, `telefono`, `correo_electronico`, `direccion`, `departamento`, `municipio`, `contacto_emergencia_nombre`, `contacto_emergencia_telefono`, `contacto_emergencia_nombre2`, `contacto_emergencia_telefono2`, `fecha_inicio_labores`, `fecha_ultimo_ascenso`, `id_renglon`, `id_area`, `id_puesto_nominal`, `id_puesto_funcional`, `foto_perfil`, `estado`, `creado_por`, `created_at`, `updated_at`) VALUES
(3, '3154965681302', '104520558', 'Victor Emmanuel', 'Martínez Chay', '2001-01-03', 'M', 'A+', '32375187', 'emmanuelchay13@gmail.com', 'zaculeu central zona 9', 'HUEHUETENANGO', 'HUEHUETENANGO', 'Aura Chay', '31719818', 'Priscila Martínez', '45117937', '2025-10-01', '2026-02-19', 2, 1, 5, 4, '', 'Activo', 'Jefatura Principal', '2026-02-09 17:22:01', '2026-02-20 18:22:20'),
(5, '1958796781302', '52921271', 'ABEL YUDINI', 'SANTOS GARCÍA', '1987-11-28', 'M', 'O+', '33290879', NULL, 'Vista Hermosa Zona 5 Chiantla, Huehuetenango', NULL, NULL, '', NULL, NULL, NULL, '2026-02-12', NULL, 2, 3, 5, 4, 'empleado_5_1771859401.jpeg', 'Activo', NULL, '2026-02-12 15:07:36', '2026-02-23 15:10:01'),
(7, '2903723641301', '17441714', 'Milton', 'Claudio', '1973-09-02', 'M', 'O+', '44647579', 'mclaudiovelasquez@gmail.com', '8a Avenida Corral Chiquito zona 8', NULL, NULL, 'LESLY SOSA', '42167641', NULL, NULL, '1994-11-02', NULL, 1, 2, 2, 1, '', 'Activo', NULL, '2026-02-12 16:13:13', '2026-02-12 16:15:53'),
(8, '1771035741301', '53670779', 'ALLAN MAUCELIO', 'LÓPEZ CASTILLO', '1989-07-06', 'M', 'O+', '57021778', 'almaloca_1789@hotmail.com', '7a avenida 1-39 zona 8', 'HUEHUETENANGO', 'HUEHUETENANGO', 'CELIA ANAYANCY TELLO ESCOBEDO', '42245496', NULL, NULL, '2016-07-23', '2026-02-27', 1, 4, 1, 3, 'empleado_8_1771858927.jpeg', 'Activo', 'Jefatura Principal', '2026-02-18 18:21:35', '2026-02-23 15:02:07');

-- --------------------------------------------------------

--
-- Table structure for table `empleado_asistencia`
--

CREATE TABLE `empleado_asistencia` (
  `id` int(11) NOT NULL,
  `empleado_id` int(11) NOT NULL,
  `tipo` enum('Vacaciones','Día Libre','Descanso','Falta Injustificada','Permiso con Goce','Permiso sin Goce','Cambio de Turno','Incapacidad') NOT NULL,
  `fecha_inicio` date NOT NULL,
  `fecha_fin` date NOT NULL,
  `comentario` text DEFAULT NULL,
  `creado_por` varchar(50) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `empleado_asistencia`
--

INSERT INTO `empleado_asistencia` (`id`, `empleado_id`, `tipo`, `fecha_inicio`, `fecha_fin`, `comentario`, `creado_por`, `created_at`) VALUES
(2, 3, 'Día Libre', '2026-01-05', '2026-01-05', 'Cumpleaños 3 de enero\r\n', NULL, '2026-02-09 21:20:16'),
(3, 3, 'Vacaciones', '2026-02-06', '2026-01-07', '', NULL, '2026-02-10 15:25:13'),
(4, 3, 'Vacaciones', '2026-02-12', '2026-02-12', 'Cambio de turno', NULL, '2026-02-12 15:55:24');

-- --------------------------------------------------------

--
-- Table structure for table `empleado_sanciones`
--

CREATE TABLE `empleado_sanciones` (
  `id` int(11) NOT NULL,
  `empleado_id` int(11) NOT NULL,
  `tipo` enum('Llamada de Atención','Reporte','Acta Administrativa','Sanción','Felicitación') NOT NULL,
  `descripcion` text DEFAULT NULL,
  `fecha` date NOT NULL,
  `creado_por` varchar(50) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `historial_accesos`
--

CREATE TABLE `historial_accesos` (
  `id` int(11) NOT NULL,
  `usuario_id` int(11) NOT NULL,
  `fecha_ingreso` timestamp NOT NULL DEFAULT current_timestamp(),
  `ip_acceso` varchar(45) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `historial_accesos`
--

INSERT INTO `historial_accesos` (`id`, `usuario_id`, `fecha_ingreso`, `ip_acceso`) VALUES
(3, 1, '2026-02-19 18:30:33', '::1'),
(4, 1, '2026-02-20 14:15:02', '::1'),
(5, 1, '2026-02-20 20:38:16', '::1'),
(6, 1, '2026-02-20 20:44:35', '::1'),
(7, 1, '2026-02-20 20:47:58', '::1'),
(8, 1, '2026-02-20 20:53:10', '::1'),
(9, 1, '2026-02-20 20:53:35', '::1'),
(10, 1, '2026-02-20 20:58:35', '::1'),
(11, 1, '2026-02-20 21:29:48', '::1'),
(12, 1, '2026-02-23 13:49:22', '::1'),
(13, 1, '2026-02-23 14:55:05', '::1');

-- --------------------------------------------------------

--
-- Table structure for table `historial_ausencias`
--

CREATE TABLE `historial_ausencias` (
  `id` int(11) NOT NULL,
  `id_empleado` int(11) NOT NULL,
  `tipo` enum('Vacaciones','Permiso con Goce','Permiso sin Goce','Enfermedad','IGSS') NOT NULL,
  `fecha_inicio` date NOT NULL,
  `fecha_fin` date NOT NULL,
  `observaciones` text DEFAULT NULL,
  `creado_por` varchar(50) DEFAULT NULL,
  `estado_solicitud` enum('Pendiente','Aprobado','Rechazado') DEFAULT 'Pendiente'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `historial_cambios_turno`
--

CREATE TABLE `historial_cambios_turno` (
  `id` int(11) NOT NULL,
  `id_empleado` int(11) NOT NULL,
  `turno_anterior` varchar(50) NOT NULL,
  `turno_nuevo` varchar(50) NOT NULL,
  `fecha_cambio` date NOT NULL,
  `motivo` text DEFAULT NULL,
  `creado_por` varchar(50) DEFAULT NULL,
  `autorizado_por` varchar(100) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `historial_laboral`
--

CREATE TABLE `historial_laboral` (
  `id` int(11) NOT NULL,
  `empleado_id` int(11) NOT NULL,
  `puesto_anterior_id` int(11) DEFAULT NULL,
  `renglon_anterior_id` int(11) DEFAULT NULL,
  `area_anterior_id` int(11) DEFAULT NULL,
  `fecha_inicio_puesto` date DEFAULT NULL,
  `fecha_fin_puesto` date DEFAULT NULL,
  `motivo_cambio` varchar(255) DEFAULT NULL,
  `usuario_que_registro` int(11) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `historial_reportes`
--

CREATE TABLE `historial_reportes` (
  `id` int(11) NOT NULL,
  `id_empleado` int(11) NOT NULL,
  `tipo` enum('Amonestación Verbal','Amonestación Escrita','Suspensión','Felicitación') NOT NULL,
  `motivo` text NOT NULL,
  `fecha_incidente` date NOT NULL,
  `archivo_adjunto` varchar(255) DEFAULT NULL,
  `creado_por` varchar(50) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `historial_reportes`
--

INSERT INTO `historial_reportes` (`id`, `id_empleado`, `tipo`, `motivo`, `fecha_incidente`, `archivo_adjunto`, `creado_por`, `created_at`) VALUES
(1, 3, 'Felicitación', 'felicitación\r\n', '2026-02-09', '3_1770667218_8F16B0BD-6B31-4756-A49B-320A1B07F8F9 (1).pdf', NULL, '2026-02-09 20:00:18');

-- --------------------------------------------------------

--
-- Table structure for table `usuarios`
--

CREATE TABLE `usuarios` (
  `id` int(11) NOT NULL,
  `nombre_usuario` varchar(50) NOT NULL,
  `password` varchar(255) NOT NULL,
  `nombre_completo` varchar(100) NOT NULL,
  `rol` enum('Administrador','RRHH','Consulta') NOT NULL DEFAULT 'RRHH',
  `estado` enum('Activo','Inactivo') DEFAULT 'Activo',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `usuarios`
--

INSERT INTO `usuarios` (`id`, `nombre_usuario`, `password`, `nombre_completo`, `rol`, `estado`, `created_at`) VALUES
(1, 'JEFATURA', '$2y$10$sGzcj9y9jmFHvQdLsL3yauZooHhpUS4oDCQ1b2jhtqVom1EARrvLi', 'Jefatura Principal', 'Administrador', 'Activo', '2026-02-18 17:41:08');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `catalogo_areas`
--
ALTER TABLE `catalogo_areas`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `catalogo_cargos`
--
ALTER TABLE `catalogo_cargos`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `catalogo_puestos_nominales`
--
ALTER TABLE `catalogo_puestos_nominales`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `catalogo_renglones`
--
ALTER TABLE `catalogo_renglones`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `codigo` (`codigo`);

--
-- Indexes for table `empleados`
--
ALTER TABLE `empleados`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `dpi` (`dpi`),
  ADD KEY `id_renglon` (`id_renglon`),
  ADD KEY `id_area` (`id_area`),
  ADD KEY `id_puesto_nominal` (`id_puesto_nominal`),
  ADD KEY `id_puesto_funcional` (`id_puesto_funcional`);

--
-- Indexes for table `empleado_asistencia`
--
ALTER TABLE `empleado_asistencia`
  ADD PRIMARY KEY (`id`),
  ADD KEY `empleado_id` (`empleado_id`);

--
-- Indexes for table `empleado_sanciones`
--
ALTER TABLE `empleado_sanciones`
  ADD PRIMARY KEY (`id`),
  ADD KEY `empleado_id` (`empleado_id`);

--
-- Indexes for table `historial_accesos`
--
ALTER TABLE `historial_accesos`
  ADD PRIMARY KEY (`id`),
  ADD KEY `usuario_id` (`usuario_id`);

--
-- Indexes for table `historial_ausencias`
--
ALTER TABLE `historial_ausencias`
  ADD PRIMARY KEY (`id`),
  ADD KEY `id_empleado` (`id_empleado`);

--
-- Indexes for table `historial_cambios_turno`
--
ALTER TABLE `historial_cambios_turno`
  ADD PRIMARY KEY (`id`),
  ADD KEY `id_empleado` (`id_empleado`);

--
-- Indexes for table `historial_laboral`
--
ALTER TABLE `historial_laboral`
  ADD PRIMARY KEY (`id`),
  ADD KEY `empleado_id` (`empleado_id`);

--
-- Indexes for table `historial_reportes`
--
ALTER TABLE `historial_reportes`
  ADD PRIMARY KEY (`id`),
  ADD KEY `id_empleado` (`id_empleado`);

--
-- Indexes for table `usuarios`
--
ALTER TABLE `usuarios`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `nombre_usuario` (`nombre_usuario`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `catalogo_areas`
--
ALTER TABLE `catalogo_areas`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- AUTO_INCREMENT for table `catalogo_cargos`
--
ALTER TABLE `catalogo_cargos`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `catalogo_puestos_nominales`
--
ALTER TABLE `catalogo_puestos_nominales`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT for table `catalogo_renglones`
--
ALTER TABLE `catalogo_renglones`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `empleados`
--
ALTER TABLE `empleados`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- AUTO_INCREMENT for table `empleado_asistencia`
--
ALTER TABLE `empleado_asistencia`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `empleado_sanciones`
--
ALTER TABLE `empleado_sanciones`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `historial_accesos`
--
ALTER TABLE `historial_accesos`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=14;

--
-- AUTO_INCREMENT for table `historial_ausencias`
--
ALTER TABLE `historial_ausencias`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `historial_cambios_turno`
--
ALTER TABLE `historial_cambios_turno`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `historial_laboral`
--
ALTER TABLE `historial_laboral`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `historial_reportes`
--
ALTER TABLE `historial_reportes`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `usuarios`
--
ALTER TABLE `usuarios`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `empleados`
--
ALTER TABLE `empleados`
  ADD CONSTRAINT `empleados_ibfk_1` FOREIGN KEY (`id_renglon`) REFERENCES `catalogo_renglones` (`id`),
  ADD CONSTRAINT `empleados_ibfk_2` FOREIGN KEY (`id_area`) REFERENCES `catalogo_areas` (`id`),
  ADD CONSTRAINT `empleados_ibfk_3` FOREIGN KEY (`id_puesto_nominal`) REFERENCES `catalogo_puestos_nominales` (`id`),
  ADD CONSTRAINT `empleados_ibfk_4` FOREIGN KEY (`id_puesto_funcional`) REFERENCES `catalogo_cargos` (`id`);

--
-- Constraints for table `empleado_asistencia`
--
ALTER TABLE `empleado_asistencia`
  ADD CONSTRAINT `empleado_asistencia_ibfk_1` FOREIGN KEY (`empleado_id`) REFERENCES `empleados` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `empleado_sanciones`
--
ALTER TABLE `empleado_sanciones`
  ADD CONSTRAINT `empleado_sanciones_ibfk_1` FOREIGN KEY (`empleado_id`) REFERENCES `empleados` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `historial_accesos`
--
ALTER TABLE `historial_accesos`
  ADD CONSTRAINT `historial_accesos_ibfk_1` FOREIGN KEY (`usuario_id`) REFERENCES `usuarios` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `historial_ausencias`
--
ALTER TABLE `historial_ausencias`
  ADD CONSTRAINT `historial_ausencias_ibfk_1` FOREIGN KEY (`id_empleado`) REFERENCES `empleados` (`id`);

--
-- Constraints for table `historial_cambios_turno`
--
ALTER TABLE `historial_cambios_turno`
  ADD CONSTRAINT `historial_cambios_turno_ibfk_1` FOREIGN KEY (`id_empleado`) REFERENCES `empleados` (`id`);

--
-- Constraints for table `historial_laboral`
--
ALTER TABLE `historial_laboral`
  ADD CONSTRAINT `historial_laboral_ibfk_1` FOREIGN KEY (`empleado_id`) REFERENCES `empleados` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `historial_reportes`
--
ALTER TABLE `historial_reportes`
  ADD CONSTRAINT `historial_reportes_ibfk_1` FOREIGN KEY (`id_empleado`) REFERENCES `empleados` (`id`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;

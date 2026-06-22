-- phpMyAdmin SQL Dump
-- version 5.2.2
-- https://www.phpmyadmin.net/
--
-- Servidor: localhost:3306
-- Tiempo de generación: 20-06-2026 a las 23:08:35
-- Versión del servidor: 11.4.10-MariaDB-cll-lve
-- Versión de PHP: 8.4.21

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Base de datos: `malkoni_online`
--

-- --------------------------------------------------------

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `Direcciones`
--

CREATE TABLE `Direcciones` (
  `id` int(11) NOT NULL,
  `domicilio` varchar(255) DEFAULT NULL,
  `barrio` varchar(255) DEFAULT NULL,
  `cp` varchar(255) DEFAULT NULL,
  `observaciones` text DEFAULT NULL,
  `id_pais` int(10) DEFAULT NULL,
  `id_provincia` int(10) DEFAULT NULL,
  `id_localidad` int(10) DEFAULT NULL,
  `id_empresa` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_general_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `Empresas`
--

CREATE TABLE `Empresas` (
  `id` int(11) NOT NULL,
  `cod_cliente` varchar(40) DEFAULT NULL,
  `razon_social` varchar(255) DEFAULT NULL,
  `cuit` varchar(40) DEFAULT NULL,
  `dni` varchar(20) DEFAULT NULL,
  `observacion` text DEFAULT NULL,
  `CodCondIVA` varchar(5) DEFAULT NULL,
  `num_tel` varchar(80) DEFAULT NULL,
  `email` varchar(255) DEFAULT NULL,
  `estado` tinyint(2) DEFAULT NULL,
  `fecha_inicial` date DEFAULT NULL,
  `fecha_alta` date DEFAULT NULL,
  `fecha_ult_contacto` date DEFAULT NULL,
  `validado` tinyint(2) DEFAULT NULL,
  `validacion_token` varchar(64) DEFAULT NULL,
  `baja` tinyint(2) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_general_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `empresas_personas`
--

CREATE TABLE `empresas_personas` (
  `id` int(10) UNSIGNED NOT NULL,
  `empresa_id` int(11) NOT NULL,
  `persona_id` int(11) NOT NULL,
  `estado` tinyint(3) UNSIGNED NOT NULL DEFAULT 1,
  `fecha_alta` datetime NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;


-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `Localidades`
--

CREATE TABLE `Localidades` (
  `id` int(10) NOT NULL,
  `nombre` varchar(20) NOT NULL,
  `id_provincia` int(10) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_general_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `Paises`
--

CREATE TABLE `Paises` (
  `id` int(11) NOT NULL,
  `nombre` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_general_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `Personas`
--

CREATE TABLE `Personas` (
  `id` int(11) NOT NULL,
  `nombre` varchar(255) DEFAULT NULL,
  `apellido` varchar(255) DEFAULT NULL,
  `genero` varchar(15) DEFAULT NULL,
  `dni` varchar(20) DEFAULT NULL,
  `email` varchar(255) DEFAULT NULL,
  `num_tel` bigint(20) DEFAULT NULL,
  `pass` varchar(255) DEFAULT NULL,
  `id_empresa` int(11) DEFAULT NULL,
  `rol` int(2) DEFAULT NULL,
  `estado_persona` int(3) DEFAULT NULL,
  `token_OPT` varchar(20) DEFAULT NULL,
  `reset_token` varchar(64) DEFAULT NULL,
  `validacion_token` varchar(64) DEFAULT NULL,
  `empresa_activa_id` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_general_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `Provincias`
--

CREATE TABLE `Provincias` (
  `id` int(11) NOT NULL,
  `nombre` varchar(255) DEFAULT NULL,
  `id_pais` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_general_ci;

--
-- Índices para tablas volcadas
--

--
-- Indices de la tabla `Direcciones`
--
ALTER TABLE `Direcciones`
  ADD PRIMARY KEY (`id`),
  ADD KEY `id_provincia` (`id_provincia`),
  ADD KEY `fk_direcciones_empresa` (`id_empresa`),
  ADD KEY `id_localidad` (`id_localidad`),
  ADD KEY `id_pais` (`id_pais`);

--
-- Indices de la tabla `Empresas`
--
ALTER TABLE `Empresas`
  ADD PRIMARY KEY (`id`);

--
-- Indices de la tabla `empresas_personas`
--
ALTER TABLE `empresas_personas`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uniq_empresa_persona` (`empresa_id`,`persona_id`),
  ADD KEY `idx_empresa_id` (`empresa_id`),
  ADD KEY `idx_persona_id` (`persona_id`);

--
-- Indices de la tabla `Localidades`
--
ALTER TABLE `Localidades`
  ADD PRIMARY KEY (`id`),
  ADD KEY `id_provincia` (`id_provincia`);

--
-- Indices de la tabla `Paises`
--
ALTER TABLE `Paises`
  ADD PRIMARY KEY (`id`);

--
-- Indices de la tabla `Personas`
--
ALTER TABLE `Personas`
  ADD PRIMARY KEY (`id`),
  ADD KEY `id_empresa` (`id_empresa`),
  ADD KEY `idx_empresa_activa_id` (`empresa_activa_id`);

--
-- Indices de la tabla `Provincias`
--
ALTER TABLE `Provincias`
  ADD PRIMARY KEY (`id`),
  ADD KEY `id_pais` (`id_pais`);

--
-- AUTO_INCREMENT de las tablas volcadas
--

--
-- AUTO_INCREMENT de la tabla `Direcciones`
--
ALTER TABLE `Direcciones`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `Empresas`
--
ALTER TABLE `Empresas`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `empresas_personas`
--
ALTER TABLE `empresas_personas`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `Localidades`
--
ALTER TABLE `Localidades`
  MODIFY `id` int(10) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `Paises`
--
ALTER TABLE `Paises`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `Personas`
--
ALTER TABLE `Personas`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `Provincias`
--
ALTER TABLE `Provincias`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- Restricciones para tablas volcadas
--

--
-- Filtros para la tabla `Direcciones`
--
ALTER TABLE `Direcciones`
  ADD CONSTRAINT `Direcciones_ibfk_1` FOREIGN KEY (`id_provincia`) REFERENCES `Provincias` (`id`),
  ADD CONSTRAINT `Direcciones_ibfk_2` FOREIGN KEY (`id_localidad`) REFERENCES `Localidades` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `Direcciones_ibfk_3` FOREIGN KEY (`id_pais`) REFERENCES `Paises` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_direcciones_empresa` FOREIGN KEY (`id_empresa`) REFERENCES `Empresas` (`id`) ON DELETE SET NULL ON UPDATE CASCADE;

--
-- Filtros para la tabla `empresas_personas`
--
ALTER TABLE `empresas_personas`
  ADD CONSTRAINT `fk_empresas_personas_empresa` FOREIGN KEY (`empresa_id`) REFERENCES `Empresas` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_empresas_personas_persona` FOREIGN KEY (`persona_id`) REFERENCES `Personas` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Filtros para la tabla `Localidades`
--
ALTER TABLE `Localidades`
  ADD CONSTRAINT `Localidades_ibfk_1` FOREIGN KEY (`id_provincia`) REFERENCES `Provincias` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Filtros para la tabla `Personas`
--
ALTER TABLE `Personas`
  ADD CONSTRAINT `Personas_ibfk_1` FOREIGN KEY (`id_empresa`) REFERENCES `Empresas` (`id`),
  ADD CONSTRAINT `fk_personas_empresa_activa` FOREIGN KEY (`empresa_activa_id`) REFERENCES `Empresas` (`id`) ON DELETE SET NULL ON UPDATE CASCADE;

--
-- Filtros para la tabla `Provincias`
--
ALTER TABLE `Provincias`
  ADD CONSTRAINT `Provincias_ibfk_1` FOREIGN KEY (`id_pais`) REFERENCES `Paises` (`id`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;

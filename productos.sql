-- phpMyAdmin SQL Dump
-- version 4.9.11
-- https://www.phpmyadmin.net/
--
-- Servidor: localhost
-- Tiempo de generación: 29-05-2025 a las 20:19:00
-- Versión del servidor: 8.0.35
-- Versión de PHP: 7.4.33

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET AUTOCOMMIT = 0;
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Base de datos: `c2830289_pena`
--

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `productos`
--

CREATE TABLE `productos` (
  `id` int NOT NULL,
  `nombre` varchar(100) NOT NULL,
  `categoria` enum('comidas','bebidas','Alcoholes') CHARACTER SET utf8mb3 COLLATE utf8mb3_general_ci NOT NULL,
  `precio` int NOT NULL,
  `disponible` enum('si','no') NOT NULL DEFAULT 'si'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3;

--
-- Volcado de datos para la tabla `productos`
--

INSERT INTO `productos` (`id`, `nombre`, `categoria`, `precio`, `disponible`) VALUES
(1, 'Lomito Italiano', 'comidas', 4500, 'si'),
(2, 'Coca Cola Express', 'bebidas', 800, 'si'),
(3, 'Coca Cola Zero Express', 'bebidas', 800, 'si'),
(4, 'Sprite Express', 'bebidas', 800, 'si'),
(5, 'Fanta Express', 'bebidas', 800, 'si'),
(6, 'Lata de Cerveza', 'Alcoholes', 1500, 'si'),
(7, 'Vaso Vino Navegado', 'Alcoholes', 1000, 'si'),
(8, 'Vaso Vodka Tonica', 'Alcoholes', 3000, 'si'),
(9, 'Vaso Ron Habana', 'Alcoholes', 3000, 'si'),
(10, 'Vaso Pisco Mistral', 'Alcoholes', 3000, 'si'),
(11, 'Vaso Vodka Naranja', 'Alcoholes', 3000, 'si'),
(12, 'Vaso Pisco Sour', 'Alcoholes', 3000, 'si'),
(13, 'Papas Mongo Familiar 4 Personas', 'comidas', 8000, 'si'),
(14, 'Papas Mongo para 2 Personas', 'comidas', 4000, 'si'),
(15, 'Empanada de Pino', 'comidas', 2500, 'si'),
(16, 'Lomito Solo', 'comidas', 4500, 'si'),
(17, 'Salchipapa Individual', 'comidas', 3000, 'si'),
(18, 'Vaso de TE', 'bebidas', 600, 'si'),
(19, 'Vaso de Cafe', 'bebidas', 600, 'si'),
(20, 'Botella de Vino', 'Alcoholes', 8000, 'si');

--
-- Índices para tablas volcadas
--

--
-- Indices de la tabla `productos`
--
ALTER TABLE `productos`
  ADD PRIMARY KEY (`id`);

--
-- AUTO_INCREMENT de las tablas volcadas
--

--
-- AUTO_INCREMENT de la tabla `productos`
--
ALTER TABLE `productos`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=21;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;

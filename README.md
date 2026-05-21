-- Base de datos: `hotel`
--

-- --------------------------------------------------------
CREATE DATABASE 'hotel'
--
-- Estructura de tabla para la tabla `habitaciones`
--

CREATE TABLE `habitaciones` (
  `id` int(11) NOT NULL,
  `numero` varchar(10) DEFAULT NULL,
  `clase` enum('estandar','suite','deluxe') DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `habitaciones`
--

INSERT INTO `habitaciones` (`id`, `numero`, `clase`) VALUES
(1, '101', 'estandar'),
(2, '102', 'estandar'),
(3, '103', 'estandar'),
(4, '104', 'estandar'),
(5, '105', 'estandar'),
(6, '106', 'estandar'),
(7, '107', 'estandar'),
(8, '108', 'estandar'),
(9, '201', 'estandar'),
(10, '202', 'estandar'),
(11, '203', 'estandar'),
(12, '204', 'estandar'),
(13, '205', 'estandar'),
(14, '206', 'estandar'),
(15, '207', 'estandar'),
(16, '208', 'estandar'),
(17, '301', 'suite'),
(18, '302', 'suite'),
(19, '303', 'suite'),
(20, '304', 'suite'),
(21, '305', 'suite'),
(22, '306', 'suite'),
(23, '307', 'suite'),
(24, '308', 'suite'),
(25, '401', 'suite'),
(26, '402', 'suite'),
(27, '403', 'suite'),
(28, '404', 'suite'),
(29, '405', 'suite'),
(30, '406', 'suite'),
(31, '407', 'suite'),
(32, '408', 'suite'),
(33, '501', 'deluxe'),
(34, '502', 'deluxe'),
(35, '503', 'deluxe'),
(36, '504', 'deluxe'),
(37, '505', 'deluxe'),
(38, '506', 'deluxe'),
(39, '507', 'deluxe'),
(40, '508', 'deluxe');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `reservas`
--

CREATE TABLE `reservas` (
  `id` int(11) NOT NULL,
  `usuario_id` int(11) DEFAULT NULL,
  `habitacion` varchar(10) DEFAULT NULL,
  `fecha_inicio` date DEFAULT NULL,
  `fecha_fin` date DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `reservas`
--

INSERT INTO `reservas` (`id`, `usuario_id`, `habitacion`, `fecha_inicio`, `fecha_fin`) VALUES
(1, 2, '503', '2026-05-22', '2026-05-23'),
(2, 3, '102', '2026-05-22', '2026-05-24');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `usuarios`
--

CREATE TABLE `usuarios` (
  `id` int(11) NOT NULL,
  `nombre` varchar(100) DEFAULT NULL,
  `email` varchar(100) DEFAULT NULL,
  `password` varchar(255) DEFAULT NULL,
  `cedula` varchar(20) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `usuarios`
--

INSERT INTO `usuarios` (`id`, `nombre`, `email`, `password`, `cedula`) VALUES
(1, 'Jeam Pierre', 'pierremorales@yopmail.com', '$2y$10$ACt0lOmmK9CqSbVkr55U6emu8aLCo7Qh6gk8jXkRwNmBAzMxpAcCO', '1055755003'),
(2, 'Jeam', 'pierremorales@gmail.com', '$2y$10$DElmZpT1Dk5dRFd2RqzveOLtv1OY77hnlRil.W9Tv9tvcC3BSpcnK', '123456789'),
(3, 'Carlos', 'chivitaaaa2008@gmail.com', '$2y$10$1IVPCXAUgxBy/YNQmXY3xObZ8lI0Spl7gge6xdZ9y149cQc1tttJm', '1109383489');

--
-- Índices para tablas volcadas
--

--
-- Indices de la tabla `habitaciones`
--
ALTER TABLE `habitaciones`
  ADD PRIMARY KEY (`id`);

--
-- Indices de la tabla `reservas`
--
ALTER TABLE `reservas`
  ADD PRIMARY KEY (`id`),
  ADD KEY `usuario_id` (`usuario_id`);

--
-- Indices de la tabla `usuarios`
--
ALTER TABLE `usuarios`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `email` (`email`),
  ADD UNIQUE KEY `cedula` (`cedula`);

--
-- AUTO_INCREMENT de las tablas volcadas
--

--
-- AUTO_INCREMENT de la tabla `habitaciones`
--
ALTER TABLE `habitaciones`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=41;

--
-- AUTO_INCREMENT de la tabla `reservas`
--
ALTER TABLE `reservas`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT de la tabla `usuarios`
--
ALTER TABLE `usuarios`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- Restricciones para tablas volcadas
--

--
-- Filtros para la tabla `reservas`
--
ALTER TABLE `reservas`
  ADD CONSTRAINT `reservas_ibfk_1` FOREIGN KEY (`usuario_id`) REFERENCES `usuarios` (`id`);
COMMIT;

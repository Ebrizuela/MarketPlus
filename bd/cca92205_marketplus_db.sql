-- phpMyAdmin SQL Dump
-- version 5.2.3
-- https://www.phpmyadmin.net/
--
-- Servidor: localhost:3306
-- Tiempo de generación: 16-11-2026 a las 01:18:51
-- Versión del servidor: 10.6.17-MariaDB-cll-lve
-- Versión de PHP: 8.1.34

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Base de datos: `cca92205_marketplus_db`
--

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `auditoria`
--

CREATE TABLE `auditoria` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `usuario_id` int(11) DEFAULT NULL,
  `accion` varchar(100) NOT NULL,
  `detalles` text DEFAULT NULL,
  `ip` varchar(45) DEFAULT NULL,
  `user_agent` varchar(255) DEFAULT NULL,
  `fecha` datetime NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

--
-- Volcado de datos para la tabla `auditoria`
--

INSERT INTO `auditoria` (`id`, `usuario_id`, `accion`, `detalles`, `ip`, `user_agent`, `fecha`) VALUES
(1, NULL, 'actualizar_supervisor', '{\"supervisor_id\":17,\"anterior\":{\"nombre\":\"Juan\",\"apellido\":\"Pérez\",\"correo\":\"juan.perez@sistemaventas.cl\",\"id_rol\":2,\"rol\":\"Supervisor\"},\"nuevo\":{\"nombre\":\"Mario\",\"apellido\":\"Pérez\",\"correo\":\"juan.perez@sistemaventas.cl\",\"id_rol\":2,\"rol\":\"Supervisor\"}}', '179.60.92.241', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/154.0.0.0 Safari/537.36 OPR/138.0.0.0', '2026-11-03 11:47:43'),
(2, NULL, 'actualizar_supervisor', '{\"supervisor_id\":2,\"anterior\":{\"nombre\":\"Tomas\",\"apellido\":\"Gaete\",\"correo\":\"tomas.gaete03@gmail.com\",\"id_rol\":2,\"rol\":\"Supervisor\"},\"nuevo\":{\"nombre\":\"Tomas\",\"apellido\":\"Gaete Alarcon\",\"correo\":\"tomas.gaete03@gmail.com\",\"id_rol\":2,\"rol\":\"Supervisor\"}}', '186.11.84.250', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/157.0.0.0 Safari/537.36', '2026-11-08 21:23:19'),
(3, NULL, 'actualizar_supervisor', '{\"supervisor_id\":2,\"anterior\":{\"nombre\":\"Tomas\",\"apellido\":\"Gaete Alarcon\",\"correo\":\"tomas.gaete03@gmail.com\",\"id_rol\":2,\"rol\":\"Supervisor\"},\"nuevo\":{\"nombre\":\"Tomass\",\"apellido\":\"Gaete Alarcon\",\"correo\":\"tomas.gaete03@gmail.com\",\"id_rol\":2,\"rol\":\"Supervisor\"}}', '186.11.84.250', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/157.0.0.0 Safari/537.36 Edg/157.0.0.0', '2026-11-08 22:41:45'),
(4, NULL, 'actualizar_supervisor', '{\"supervisor_id\":2,\"anterior\":{\"nombre\":\"Tomass\",\"apellido\":\"Gaete Alarcon\",\"correo\":\"tomas.gaete03@gmail.com\",\"id_rol\":2,\"rol\":\"Supervisor\"},\"nuevo\":{\"nombre\":\"Paolo\",\"apellido\":\"Maldini\",\"correo\":\"rayo95.tg@gmail.com\",\"id_rol\":2,\"rol\":\"Supervisor\"}}', '179.60.89.129', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/156.0.0.0 Safari/537.36', '2026-11-08 23:10:42'),
(5, NULL, 'actualizar_supervisor', '{\"supervisor_id\":2,\"anterior\":{\"nombre\":\"Paolo\",\"apellido\":\"Maldini\",\"correo\":\"rayo95.tg@gmail.com\",\"id_rol\":2,\"rol\":\"Supervisor\"},\"nuevo\":{\"nombre\":\"Tomas\",\"apellido\":\"Gaete\",\"correo\":\"tomas.gaete03@gmail.com\",\"id_rol\":2,\"rol\":\"Supervisor\"}}', '179.60.89.129', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/156.0.0.0 Safari/537.36', '2026-11-08 23:11:42'),
(6, NULL, 'actualizar_supervisor', '{\"supervisor_id\":23,\"anterior\":{\"nombre\":\"Paolo\",\"apellido\":\"Maldini\",\"correo\":\"rayo95.tg@gmail.com\",\"id_rol\":2,\"rol\":\"Supervisor\"},\"nuevo\":{\"nombre\":\"Paolo\",\"apellido\":\"Maldini\",\"correo\":\"rayo95.tg@gmail.com\",\"id_rol\":3,\"rol\":\"Vendedor\"}}', '179.60.89.129', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/156.0.0.0 Safari/537.36', '2026-11-09 00:15:59'),
(7, NULL, 'actualizar_supervisor', '{\"supervisor_id\":20,\"anterior\":{\"nombre\":\"Carlos\",\"apellido\":\"hernández\",\"correo\":\"carlos.hernadez02@gmail.com\",\"id_rol\":2,\"rol\":\"Supervisor\"},\"nuevo\":{\"nombre\":\"Carlos\",\"apellido\":\"Hernandez\",\"correo\":\"carlos.hernadez02@gmail.com\",\"id_rol\":2,\"rol\":\"Supervisor\"}}', '179.238.35.206', 'curl/8.13.0', '2026-11-09 06:00:12'),
(8, NULL, 'actualizar_supervisor', '{\"supervisor_id\":20,\"anterior\":{\"nombre\":\"Carlos\",\"apellido\":\"Hernandez\",\"correo\":\"carlos.hernadez02@gmail.com\",\"id_rol\":2,\"rol\":\"Supervisor\"},\"nuevo\":{\"nombre\":\"Carlos\",\"apellido\":\"Hernandez\",\"correo\":\"carlos.hernadez02@gmail.com\",\"id_rol\":2,\"rol\":\"Supervisor\"}}', '179.238.35.206', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Cypress/15.21.1 Chrome/138.0.7204.251 Electron/37.6.0 Safari/537.36', '2026-11-09 06:07:26'),
(9, NULL, 'actualizar_supervisor', '{\"supervisor_id\":20,\"anterior\":{\"nombre\":\"Carlos\",\"apellido\":\"Hernandez\",\"correo\":\"carlos.hernadez02@gmail.com\",\"id_rol\":2,\"rol\":\"Supervisor\"},\"nuevo\":{\"nombre\":\"Carlos\",\"apellido\":\"Hernandez\",\"correo\":\"carlos.hernadez02@gmail.com\",\"id_rol\":2,\"rol\":\"Supervisor\"}}', '179.238.35.206', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Cypress/15.21.1 Chrome/138.0.7204.251 Electron/37.6.0 Safari/537.36', '2026-11-09 06:09:33'),
(10, NULL, 'actualizar_supervisor', '{\"supervisor_id\":20,\"anterior\":{\"nombre\":\"Carlos\",\"apellido\":\"Hernandez\",\"correo\":\"carlos.hernadez02@gmail.com\",\"id_rol\":2,\"rol\":\"Supervisor\"},\"nuevo\":{\"nombre\":\"Carlos\",\"apellido\":\"Hernandez\",\"correo\":\"carlos.hernadez02@gmail.com\",\"id_rol\":2,\"rol\":\"Supervisor\"}}', '179.238.35.206', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/157.0.0.0 Safari/537.36 Edg/157.0.0.0', '2026-11-09 14:13:17'),
(11, NULL, 'actualizar_supervisor', '{\"supervisor_id\":20,\"anterior\":{\"nombre\":\"Carlos\",\"apellido\":\"Hernandez\",\"correo\":\"carlos.hernadez02@gmail.com\",\"id_rol\":2,\"rol\":\"Supervisor\"},\"nuevo\":{\"nombre\":\"Carlos\",\"apellido\":\"Hernandez\",\"correo\":\"carlos.hernadez02@gmail.com\",\"id_rol\":2,\"rol\":\"Supervisor\"}}', '179.238.35.206', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/157.0.0.0 Safari/537.36 Edg/157.0.0.0', '2026-11-09 14:20:25'),
(12, NULL, 'actualizar_supervisor', '{\"supervisor_id\":20,\"anterior\":{\"nombre\":\"Carlos\",\"apellido\":\"Hernandez\",\"correo\":\"carlos.hernadez02@gmail.com\",\"id_rol\":2,\"rol\":\"Supervisor\"},\"nuevo\":{\"nombre\":\"Carlos\",\"apellido\":\"Hernandez\",\"correo\":\"carlos.hernadez02@gmail.com\",\"id_rol\":2,\"rol\":\"Supervisor\"}}', '179.238.35.206', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/157.0.0.0 Safari/537.36 Edg/157.0.0.0', '2026-11-09 14:23:21'),
(13, NULL, 'actualizar_supervisor', '{\"supervisor_id\":2,\"anterior\":{\"nombre\":\"Tomas\",\"apellido\":\"Gaete\",\"correo\":\"tomas.gaete03@gmail.com\",\"id_rol\":2,\"rol\":\"Supervisor\"},\"nuevo\":{\"nombre\":\"Tomas\",\"apellido\":\"Gaete\",\"correo\":\"tomas.gaete03@gmail.com\",\"id_rol\":3,\"rol\":\"Vendedor\"}}', '191.116.4.219', 'Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/156.0.0.0 Safari/537.36', '2026-11-10 21:47:21');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `Auditoria_Usuarios`
--

CREATE TABLE `Auditoria_Usuarios` (
  `id_auditoria` int(11) NOT NULL,
  `id_usuario_afectado` int(11) NOT NULL,
  `accion` varchar(50) NOT NULL,
  `realizado_por` varchar(100) NOT NULL,
  `fecha_hora` datetime DEFAULT current_timestamp(),
  `detalles` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `auditoria_usuarios`
--

CREATE TABLE `auditoria_usuarios` (
  `id_auditoria` int(11) NOT NULL,
  `id_usuario_afectado` int(11) NOT NULL,
  `accion` varchar(50) NOT NULL,
  `realizado_por` varchar(100) NOT NULL,
  `fecha_hora` datetime DEFAULT current_timestamp(),
  `detalles` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

--
-- Volcado de datos para la tabla `auditoria_usuarios`
--

INSERT INTO `auditoria_usuarios` (`id_auditoria`, `id_usuario_afectado`, `accion`, `realizado_por`, `fecha_hora`, `detalles`) VALUES
(1, 12, 'ELIMINACION_LOGICA', 'nibhernandez25@cftsa.cl', '2026-09-29 19:29:40', 'Baja lógica de supervisor manteniendo histórico de ventas'),
(2, 2, 'ELIMINACION_LOGICA', 'Administrador@sistemaventas.cl', '2026-11-05 16:27:47', 'Baja lógica de supervisor; se conserva el historial de ventas.'),
(3, 15, 'ELIMINACION_LOGICA', 'Administrador@sistemaventas.cl', '2026-11-08 21:15:54', 'Baja lógica de supervisor; se conserva el historial de ventas.');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `Categorias`
--

CREATE TABLE `Categorias` (
  `id_categorias` int(11) NOT NULL,
  `nombre` varchar(255) NOT NULL,
  `descripcion` varchar(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `Categorias`
--

INSERT INTO `Categorias` (`id_categorias`, `nombre`, `descripcion`) VALUES
(1, 'Tecnología', 'Dispositivos, hardware, accesorios y solucion'),
(2, 'Software y Licencias', 'Programas, sistemas informáticos y licencias de uso corporativo para la gestión de negocios.'),
(3, 'Línea Blanca y Electrodomésticos', 'Línea Blanca y Electrodomésticos para el hogar y la oficina, incluyendo equipos de climatización, refrigeración y línea blanca menor.'),
(4, 'Herramientas y Ferretería', 'Artículos, herramientas manuales, eléctricas y accesorios para trabajos de construcción, mantención y reparación general.'),
(5, 'Muebles y Oficina', 'Mobiliario ergonómico, escritorios, estanterías y equipamiento completo para espacios de trabajo y oficinas.'),
(6, 'Seguridad y Vigilancia', 'Sistemas de cámaras de circuito cerrado, sensores, alarmas y accesorios de protección perimetral e informática.'),
(7, 'Insumos de Aseo y Limpieza', 'Productos químicos, herramientas de limpieza y artículos de higiene institucional para mantener espacios seguros y ordenados.');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `Clientes`
--

CREATE TABLE `Clientes` (
  `id_cliente` int(11) NOT NULL,
  `nombre` varchar(45) NOT NULL,
  `apellidos` varchar(45) NOT NULL,
  `rut` varchar(13) NOT NULL,
  `correo` varchar(45) NOT NULL,
  `telefono` varchar(45) NOT NULL,
  `clave` varchar(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `Clientes`
--

INSERT INTO `Clientes` (`id_cliente`, `nombre`, `apellidos`, `rut`, `correo`, `telefono`, `clave`) VALUES
(1, 'nilson', 'solano', '20.767.341-2', 'nilson.solano5@gmail.com', '+56935628504', '$2y$12$ACGloUs1K6zW4Xzesp8Ai.1zV0sWlon9I45mKTflaRfYdYpuMTdau'),
(2, 'Michael', 'Jackson', '23.421.123-5', 'pruebas2@gmail.com', '+56935635643', '$2y$12$HhI5prJcm4AOzpXVa.ehb./rkklfZx01Z7AdsRzaqeoKFFueABXEG'),
(3, 'nilson', 'solano', '20.689.096-7', 'pruebas3@gmail.com', '+569 52232864', '$2y$12$0TgkcaJc1UluMhyezKDwP.pBMyVDVzQIw4RHoxsGtDMP33eYrXRo6'),
(4, 'Juan Villalobos', 'Viillalobos Suazo', '21.446.565-5', 'Jvillalobos25@cftsa.cl', '9 49441854', '$2y$12$u3rJznDS8Iw18G4It7CHYegAKtVsphW1FQ4TEaIVSWFQnpH29lowG'),
(5, 'Juan', 'Villalobos', '214465655', 'Jvillalobos25@cftsa.cl', '984867767', '$2y$12$AKKNj01QJevvmzyqQOzvEOdZnzSevVM4sp.xrgzg..BDzUNg.f9sS');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `Detalle_Ventas`
--

CREATE TABLE `Detalle_Ventas` (
  `id_detalle` int(11) NOT NULL,
  `id_ventas` int(11) NOT NULL,
  `id_producto` int(11) NOT NULL,
  `cantidad` int(11) NOT NULL,
  `precio_unitario` decimal(10,2) NOT NULL,
  `subtotal` decimal(10,2) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `Detalle_Ventas`
--

INSERT INTO `Detalle_Ventas` (`id_detalle`, `id_ventas`, `id_producto`, `cantidad`, `precio_unitario`, `subtotal`) VALUES
(5, 5, 67, 1, 370.00, 370.00),
(6, 7, 68, 1, 699999.00, 699999.00),
(7, 8, 64, 1, 820000.00, 820000.00),
(8, 9, 63, 2, 339991.00, 679982.00),
(9, 9, 62, 1, 700000.00, 700000.00),
(10, 9, 64, 1, 820000.00, 820000.00),
(11, 10, 64, 2, 820000.00, 1640000.00),
(12, 11, 73, 1, 100000.00, 100000.00),
(13, 12, 76, 5, 719.00, 3595.00),
(14, 12, 73, 12, 100000.00, 1200000.00),
(15, 13, 74, 1, 700000.00, 700000.00),
(16, 14, 73, 8, 100000.00, 800000.00),
(17, 15, 80, 1, 529990.00, 529990.00),
(18, 16, 76, 1, 719.00, 719.00),
(19, 17, 74, 1, 700000.00, 700000.00),
(20, 18, 80, 1, 529990.00, 529990.00),
(21, 19, 80, 2, 529990.00, 1059980.00),
(22, 20, 79, 1, 829990.00, 829990.00),
(23, 21, 67, 1, 250000.00, 250000.00),
(24, 22, 82, 1, 990.00, 990.00),
(25, 23, 82, 2, 990.00, 1980.00),
(26, 24, 79, 1, 829990.00, 829990.00),
(27, 25, 82, 1, 990.00, 990.00),
(28, 26, 79, 1, 829990.00, 829990.00),
(29, 27, 74, 1, 700000.00, 700000.00),
(30, 28, 80, 2, 529990.00, 1059980.00),
(31, 29, 82, 7, 990.00, 6930.00);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `Devoluciones`
--

CREATE TABLE `Devoluciones` (
  `id_devolucion` int(11) NOT NULL,
  `folio` varchar(50) NOT NULL,
  `id_venta` int(11) NOT NULL,
  `id_producto` int(11) NOT NULL,
  `cantidad` int(11) NOT NULL,
  `motivo` varchar(255) NOT NULL,
  `fecha` datetime DEFAULT current_timestamp(),
  `id_funcionario` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

--
-- Volcado de datos para la tabla `Devoluciones`
--

INSERT INTO `Devoluciones` (`id_devolucion`, `folio`, `id_venta`, `id_producto`, `cantidad`, `motivo`, `fecha`, `id_funcionario`) VALUES
(1, 'DEV-20260913-0001', 2, 56, 1, 'Malo', '2026-09-13 05:20:54', 1),
(2, 'DEV-20260924-0001', 10, 64, 1, 'Malo', '2026-09-24 20:13:42', 8),
(3, 'DEV-20260925-0001', 12, 76, 4, 'Devolución', '2026-09-25 20:48:43', 8),
(4, 'DEV-20260927-0001', 10, 64, 1, 'Cambio de opinio', '2026-09-27 18:06:40', 8),
(5, 'DEV-20260927-0002', 11, 73, 1, 'Producto defectuoso', '2026-09-27 23:05:25', 8),
(6, 'DEV-20260927-0003', 12, 76, 1, 'producto incorrecto', '2026-09-27 23:36:07', 8),
(7, 'DEV-20260930-0001', 15, 80, 1, 'Dañado', '2026-09-30 13:40:53', 8),
(8, 'DEV-20261107-0001', 20, 79, 1, 'Producto Defectuoso', '2026-11-07 13:01:33', 15),
(9, 'DEV-20261107-0002', 19, 80, 1, 'Producto', '2026-11-07 13:03:48', 15),
(10, 'DEV-20261107-0003', 8, 64, 1, 'Producto Defectuoso', '2026-11-07 13:05:00', 15),
(11, 'DEV-20261109-0001', 27, 74, 1, 'devolución', '2026-11-09 13:37:11', 6),
(12, 'DEV-20261109-0002', 23, 82, 1, 'a', '2026-11-09 13:43:57', 6),
(13, 'DEV-20261109-0003', 17, 74, 1, 'a', '2026-11-09 14:10:49', 6),
(14, 'DEV-20261109-0004', 18, 80, 1, 'a', '2026-11-09 14:12:38', 6);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `funcionarios`
--

CREATE TABLE `funcionarios` (
  `id_funcionario` int(11) NOT NULL,
  `id_rol` int(11) NOT NULL,
  `nombre` varchar(45) NOT NULL,
  `apellido` varchar(45) NOT NULL,
  `rut` varchar(13) NOT NULL,
  `direccion` varchar(45) NOT NULL,
  `telefono` varchar(45) NOT NULL,
  `correo` varchar(45) NOT NULL,
  `contrasena` varchar(255) NOT NULL,
  `activo` tinyint(1) NOT NULL DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `funcionarios`
--

INSERT INTO `funcionarios` (`id_funcionario`, `id_rol`, `nombre`, `apellido`, `rut`, `direccion`, `telefono`, `correo`, `contrasena`, `activo`) VALUES
(1, 1, 'Jorge', 'Valdivia Toro', '16.813.140-1', 'Avenida Monseñor Enrique Alvear 1234', '+56987654321', 'Administrador@sistemaventas.cl', '$2y$12$2qzmaLXOhNwCEmQLij6Cj.sKCjW/IB9woMb2GIHNnIgpXUF/JHzEO', 1),
(2, 3, 'Tomas', 'Gaete', '16.713.130-1', 'Avenida Monseñor Enrique Alvear', '+56987654321', 'tomas.gaete03@gmail.com', '$2y$12$gSKbrObIbVPABZX.LudNX.49UlGuXJmdBzFGCZipAfDpxpfm07UbS', 0),
(3, 3, 'Carolina ', 'Giraldo Navarro', '21132586-1', 'Avenida Monseñor Enrique Alvear', '+56987654321', 'Vendedor@sistemaventas.cl', '$2y$12$qCM5AnnnpcCnRcLAzEEqRu3ODCtdPeXxIOh4HT/siZ5dqTToFMeSK', 1),
(4, 3, 'Francisco', 'Saavedra', '11.111.111-1', 'Avenida Prueba 123', '+56922345678', 'prueba2@marketplus.cl', '$2y$12$q11pWI5xSLlQR/szD3370uYjsmUBhr1247vzYR.gAyTHTqKkdhygS', 1),
(5, 3, 'José Antonio', 'Gonzalez Opazo', '22.044.393-0', 'Santa Sofia', '+56948726315', 'prueba.vendedor02@marketplus.cl', '$2y$12$q11pWI5xSLlQR/szD3370uYjsmUBhr1247vzYR.gAyTHTqKkdhygS', 1),
(6, 2, 'Maria', 'Pinto', '11.111.111-9', 'Sin Información', '+56900000000', 'Supervisor@sistemaventas.cl', '$2y$12$bm6nUiWzEj6xVSJMNhoBde0laEUXQycBVW7eCfF5fcD0qpoM7TUUO', 1),
(8, 3, 'Francisco', 'Saavedra', '21.944.713-5', 'Avenida Prueba 12345', '+56952340698', 'Vendedorprueba@marketplus.cl', '$2y$12$PrHoOhYqG9ckKwUk6x66K.qFt7hOSE/OjPIH5qdLzXzCWx/A5Tyj2', 1),
(9, 3, 'Claudio', 'Bravo', '21.846.763.6', 'avenidaprueba321', '+56923982448', 'vendedorprueba@gmail.com', 'usuario1234', 1),
(10, 3, 'Juan', 'hernandez', '3232423535', 'holanda', '+56960956053', 'vendedor_prueba@marketplus.cl', 'Password123', 1),
(11, 1, 'ÁLVARO', 'HENRÍQUEZ', '11.111.111-9', 'Sin Información', '+56900000000', 'alvgonzalez25@cftsa.cl', '$2y$12$Kq0cNI3Z3svtc98gz.xfnect.syAJxEZjMmAto9IwNqSb5TS7tg7W', 1),
(12, 2, 'Carlos', 'Perez', '11.111.111-9', 'Sin Información', '+56900000000', 'cperez@cftsa.cl', '$2y$12$zoVHH7ekBWro6.fP36X0iuR7qv6UBU7ZgTigkxjsI/61ug37.xeZC', 0),
(13, 3, 'Tomas', 'Gaete', '22.224.317-3', 'Cauquenes', '+56 9 9612 7654', 'tgaete25@cftsa.cl', '$2y$12$RpLQ1xAm.QGTEj8pDsC4ze3G.YKRlCvBiyqKxerKvn4vuLDyP4tD6', 1),
(14, 2, 'Maria', 'Perez', '11.111.111-9', 'Sin Información', '+56900000000', 'belichima@hotmail.com', '$2y$12$EZKBaNMXakZdZ.qKbfSgY.1YxKZ8dfXsi7OsIVM9LVIzJGuPGTKGS', 1),
(15, 2, 'Maria', 'pinto', '11.111.111-9', 'Sin Información', '+56900000000', 'Supervisor@sistemaventas.c', '$2y$12$mMGcyQdUmZPjzPZCkCKsvuWwZFdT973QRRR59ww7R0/laXUrjaLw2', 0),
(16, 1, 'Cristian', 'Riquelme', '11.111.111-9', 'Sin Información', '+56900000000', 'cr@gmail.com', '$2y$12$AKTta/faM6PPMk3Cphvvf.Z1.j.TtzZaWb9FlmPfB8aj/i8EEkUnC', 1),
(17, 2, 'Mario', 'Pérez', '11.111.111-9', 'Sin Información', '+56900000000', 'juan.perez@sistemaventas.cl', '$2y$12$UVPWqHWmCGg3DiT.DmPspeuqy9ypMcVWl6eDFHZ7ypFzDtru2hty6', 1),
(18, 1, 'Jeannette', 'Arias', '186749014', 'avda. margarita', '98227', 'jane.ariasrios@yahoo.com', '', 1),
(19, 1, 'Lupita', 'Pérez', '11.111.111-9', 'Sin Información', '+56900000000', 'lupita.perez@gmail.com', '$2y$12$JpZTxLr2k4PGj6w1ujikJeFhmo4SUZqF4ZrQ1CJ.5dLbCTFlYa0Tu', 1),
(20, 2, 'Carlos', 'Hernandez', '11.111.111-9', 'Sin Información', '+56900000000', 'carlos.hernadez02@gmail.com', '$2y$12$sbwqDXA0kLzp.SD3YrA32OEmN.dXyUzir3JZxQu.28lRLQXZqapKm', 1),
(21, 1, 'Prueba', 'QA', '11.111.111-9', 'Sin Información', '+56900000000', 'testqa_1794154066436@marketplus.cl', '$2y$12$Uv53a0T64SOJ/031Sr4xw.Q0toH74gmdKNrM8mQhG0XhrMKo8g2E.', 1),
(22, 1, 'ÁLVARO', 'HENRÍQUEZ', '11.111.111-9', 'Sin Información', '+56900000000', 'alvaro.gonzalezh2018@gmail.com', '$2y$12$5VnYN/bgWCqUmJH/O5BWK.ADglHR8UDvpdVEoQZnT23uUf93He5vi', 1),
(23, 3, 'Paolo', 'Maldini', '11.111.111-9', 'Sin Información', '+56900000000', 'rayo95.tg@gmail.com', '$2y$12$Y7E0LKuRoT/5g/wKf8c1ROjYCn6WK3SIACYB4mNkGWQi2v9671Hxq', 1),
(24, 2, 'Jeannette', 'Arias', '11.111.111-9', 'Sin Información', '+56900000000', 'jarias25@cftsa.cl', '$2y$12$K6aRbV3sC44Dt6winx9vaeLff4U/Kx4kD7yvjwJuBZ6L2tLob128m', 1),
(25, 1, 'Josimar José', 'Évora Díaz', '11.111.111-9', 'Sin Información', '+56900000000', 'eduardo.brizuela.santis@gmail.com', '$2y$12$NNHhp7287brpZR/gnLadgeaZ33KKy1.E7T1pZPRPYWqMceXUCqJtK', 1);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `Logs_Seguridad`
--

CREATE TABLE `Logs_Seguridad` (
  `id_log` int(11) NOT NULL,
  `timestamp` datetime DEFAULT current_timestamp(),
  `correo_intento` varchar(100) DEFAULT NULL,
  `rol_identificado` varchar(50) DEFAULT NULL,
  `estado` varchar(20) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

--
-- Volcado de datos para la tabla `Logs_Seguridad`
--

INSERT INTO `Logs_Seguridad` (`id_log`, `timestamp`, `correo_intento`, `rol_identificado`, `estado`) VALUES
(1, '2026-09-21 20:49:11', 'admin@marketplus.cl', 'Desconocido', 'Fallido'),
(2, '2026-09-21 20:49:37', 'admin@marketplus.cl', 'Desconocido', 'Fallido'),
(3, '2026-09-21 20:49:52', 'admin@marketplus.cl', 'Desconocido', 'Fallido'),
(4, '2026-09-21 20:50:19', 'admin@marketplus.cl', 'Desconocido', 'Fallido'),
(5, '2026-09-21 20:50:40', 'vcampos22@cftsa.cl', 'Desconocido', 'Fallido'),
(6, '2026-09-21 20:55:44', 'vcampos22@cftsa.cl', 'Desconocido', 'Fallido'),
(7, '2026-09-21 20:56:02', 'admin@marketplus.cl', 'Desconocido', 'Fallido'),
(8, '2026-09-21 20:56:21', 'admin@marketplus.cl', 'Desconocido', 'Fallido'),
(9, '2026-09-21 21:00:53', 'admin@marketplus.cl', 'Desconocido', 'Fallido'),
(10, '2026-09-21 21:12:03', 'Administrador@sistemaventas.cl', 'Administrador', 'Exitoso'),
(11, '2026-09-21 21:16:20', 'Supervisor@sistemaventas.cl', 'Supervisor', 'Exitoso'),
(12, '2026-09-21 21:16:41', 'Vendedor@sistemaventas.cl', 'Vendedor', 'Exitoso'),
(13, '2026-09-21 21:21:09', 'Administrador@sistemaventas.cl', 'Administrador', 'Exitoso'),
(14, '2026-09-22 20:48:07', 'Administrador@sistemaventas.cl', 'Administrador', 'Exitoso'),
(15, '2026-09-22 21:13:22', 'lvera25@cftsa.cl', 'Supervisor', 'Exitoso'),
(16, '2026-09-22 21:53:52', 'Administrador@sistemaventas.cl', 'Desconocido', 'Fallido'),
(17, '2026-09-22 21:54:05', 'Administrador@sistemaventas.cl', 'Administrador', 'Exitoso'),
(18, '2026-09-22 21:54:54', 'Supervisor@sistemaventas.cl', 'Supervisor', 'Exitoso'),
(19, '2026-09-22 21:55:24', 'Vendedor@sistemaventas.cl', 'Vendedor', 'Exitoso'),
(20, '2026-09-22 21:55:30', 'vcampos22@cftsa.cl', 'Desconocido', 'Fallido'),
(21, '2026-09-23 22:08:05', 'Administrador@sistemaventas.cl', 'Administrador', 'Exitoso'),
(22, '2026-09-23 22:16:53', 'admin@marketplus.cl', 'Desconocido', 'Fallido'),
(23, '2026-09-23 22:40:21', 'Administrador@sistemaventas.cl', 'Administrador', 'Exitoso'),
(24, '2026-09-23 22:42:58', 'Supervisor@sistemaventas.cl', 'Supervisor', 'Exitoso'),
(25, '2026-09-23 22:47:45', 'Vendedor@sistemaventas.cl', 'Vendedor', 'Exitoso'),
(26, '2026-09-23 22:50:07', 'prueba2@marketplus.cl', 'Vendedor', 'Exitoso'),
(27, '2026-09-23 22:52:16', 'prueba.vendedor02@marketplus.cl', 'Vendedor', 'Exitoso'),
(28, '2026-09-23 22:54:50', 'lvera25@cftsa.cl', 'Supervisor', 'Exitoso'),
(29, '2026-09-23 22:57:36', 'belichima@hotmail.com', 'Supervisor', 'Exitoso'),
(30, '2026-09-23 23:00:22', 'nocorreo@marketplus.cl', 'Desconocido', 'Fallido'),
(31, '2026-09-24 20:35:41', 'Supervisor@sistemaventas.cl', 'Supervisor', 'Exitoso'),
(32, '2026-09-24 21:34:34', 'supervisor@marketplus.cl', 'Desconocido', 'Fallido'),
(33, '2026-09-24 21:34:39', 'vendedorprueba@marketplus.cl', 'Desconocido', 'Fallido'),
(34, '2026-09-24 21:34:42', 'vendedorprueba@marketplus.cl', 'Desconocido', 'Fallido'),
(35, '2026-09-27 16:18:26', 'Administrador@sistemaventas.cl', 'Administrador', 'Exitoso'),
(36, '2026-09-27 17:02:14', 'Administrador@sistemaventas.cl', 'Administrador', 'Exitoso'),
(37, '2026-10-04 23:21:53', 'Administrador@sistemaventas.cl', 'Desconocido', 'Fallido'),
(38, '2026-10-04 23:22:36', 'Administrador@sistemaventas.cl', 'Desconocido', 'Fallido'),
(39, '2026-10-29 21:38:42', 'Administrador@sistemaventas.cl', 'Administrador', 'Exitoso'),
(40, '2026-10-29 21:39:11', 'prueba2@marketplus.cl', 'Desconocido', 'Fallido'),
(41, '2026-10-29 21:39:21', 'prueba2@marketplus.cl', 'Desconocido', 'Fallido'),
(42, '2026-10-29 21:39:47', 'Vendedor@sistemaventas.cl', 'Vendedor', 'Exitoso'),
(43, '2026-10-29 21:55:30', 'Administrador@sistemaventas.cl', 'Administrador', 'Exitoso'),
(44, '2026-10-29 21:55:35', 'Administrador@sistemaventas.cl', 'Administrador', 'Exitoso'),
(45, '2026-10-29 22:03:05', 'Administrador@sistemaventas.cl', 'Administrador', 'Exitoso'),
(46, '2026-10-29 22:03:35', 'Vendedor@sistemaventas.cl', 'Vendedor', 'Exitoso'),
(47, '2026-10-29 22:04:29', 'Supervisor@sistemaventas.cl', 'Supervisor', 'Exitoso'),
(48, '2026-10-29 22:08:28', 'Supervisor@sistemaventas.cl', 'Supervisor', 'Exitoso'),
(49, '2026-10-29 22:09:48', 'Vendedorprueba@marketplus.cl', 'Administrador', 'Exitoso'),
(50, '2026-10-29 22:13:40', 'Vendedorprueba@marketplus.cl', 'Vendedor', 'Exitoso'),
(51, '2026-10-30 10:50:58', 'Administrador@sistemaventas.cl', 'Administrador', 'Exitoso'),
(52, '2026-10-30 10:52:51', 'Administrador@sistemaventas.cl', 'Administrador', 'Exitoso'),
(53, '2026-10-30 10:53:43', 'Supervisor@sistemaventas.cl', 'Supervisor', 'Exitoso'),
(54, '2026-10-30 10:54:07', 'Vendedor@sistemaventas.cl', 'Vendedor', 'Exitoso'),
(55, '2026-10-30 20:53:46', 'vendedorprueba@marketplus.cl', 'Desconocido', 'Fallido'),
(56, '2026-10-30 20:53:50', 'tgaete25@cftsa.cl', 'Vendedor', 'Exitoso'),
(57, '2026-10-30 20:53:56', 'Supervisor@sistemaventas.cl', 'Desconocido', 'Fallido'),
(58, '2026-10-30 20:54:00', 'Administrador@sistemaventas.cl', 'Desconocido', 'Fallido'),
(59, '2026-10-30 20:54:03', 'Vendedor@sistemaventas.cl', 'Desconocido', 'Fallido'),
(60, '2026-10-30 20:54:06', 'tgaete25@cftsa.cl', 'Vendedor', 'Exitoso'),
(61, '2026-10-30 20:54:10', 'Vendedor@sistemaventas.cl', 'Desconocido', 'Fallido'),
(62, '2026-10-30 20:54:13', 'Supervisor@sistemaventas.cl', 'Desconocido', 'Fallido'),
(63, '2026-10-30 20:54:15', 'Administrador@sistemaventas.cl', 'Desconocido', 'Fallido'),
(64, '2026-10-30 20:54:17', 'vendedorprueba@marketplus.cl', 'Desconocido', 'Fallido'),
(65, '2026-10-31 13:46:14', 'vendedorprueba@marketplus.cl', 'Desconocido', 'Fallido'),
(66, '2026-10-31 13:46:17', 'tgaete25@cftsa.cl', 'Vendedor', 'Exitoso'),
(67, '2026-10-31 13:46:21', 'Supervisor@sistemaventas.cl', 'Desconocido', 'Fallido'),
(68, '2026-10-31 13:46:24', 'Administrador@sistemaventas.cl', 'Desconocido', 'Fallido'),
(69, '2026-10-31 13:58:01', 'administrador@sistemaventas.cl', 'Administrador', 'Exitoso'),
(70, '2026-10-31 14:02:16', 'administrador@sistemaventas.cl', 'Administrador', 'Exitoso'),
(71, '2026-10-31 14:18:08', 'Administrador@sistemaventas.cl', 'Administrador', 'Exitoso'),
(72, '2026-10-31 14:24:05', 'eduardo.brizuela.santis@gmail.com', 'Desconocido', 'Fallido'),
(73, '2026-10-31 14:28:23', 'Administrador@sistemaventas.cl', 'Administrador', 'Exitoso'),
(74, '2026-10-31 15:40:01', 'Administrador@sistemaventas.cl', 'Administrador', 'Exitoso'),
(75, '2026-10-31 15:40:14', 'prueba.vendedor02@marketplus.cl', 'Desconocido', 'Fallido'),
(76, '2026-10-31 15:40:21', 'prueba.vendedor02@marketplus.cl', 'Desconocido', 'Fallido'),
(77, '2026-10-31 15:40:34', 'prueba2@marketplus.cl', 'Desconocido', 'Fallido'),
(78, '2026-10-31 15:40:48', 'Administrador@sistemaventas.cl', 'Administrador', 'Exitoso'),
(79, '2026-10-31 15:41:22', 'Administrador@sistemaventas.cl', 'Administrador', 'Exitoso'),
(80, '2026-10-31 15:46:28', 'vendedorprueba@marketplus.cl', 'Desconocido', 'Fallido'),
(81, '2026-10-31 15:49:56', 'vendedorprueba@marketplus.cl', 'Desconocido', 'Fallido'),
(82, '2026-11-01 01:57:39', 'Administrador@sistemaventas.cl', 'Administrador', 'Exitoso'),
(83, '2026-11-02 00:37:59', 'Administrador@sistemaventas.cl', 'Administrador', 'Exitoso'),
(84, '2026-11-02 20:03:10', 'Vendedor@sistemaventas.cl', 'Vendedor', 'Exitoso'),
(85, '2026-11-02 20:03:21', 'Administrador@sistemaventas.cl', 'Administrador', 'Exitoso'),
(86, '2026-11-02 20:03:37', 'Supervisor@sistemaventas.cl', 'Supervisor', 'Exitoso'),
(87, '2026-11-02 20:07:12', 'Vendedor@sistemaventas.cl', 'Vendedor', 'Exitoso'),
(88, '2026-11-02 20:08:38', 'Supervisor@sistemaventas.cl', 'Supervisor', 'Exitoso'),
(89, '2026-11-02 20:22:34', 'Administrador@sistemaventas.cl', 'Administrador', 'Exitoso'),
(90, '2026-11-02 20:36:51', 'Administrador@sistemaventas.cl', 'Administrador', 'Exitoso'),
(91, '2026-11-02 20:37:17', 'Administrador@sistemaventas.cl', 'Administrador', 'Exitoso'),
(92, '2026-11-02 22:42:18', 'administrador@sistemaventas.cl', 'Administrador', 'Exitoso'),
(93, '2026-11-02 22:42:39', 'vendedorprueba@marketplus.cl', 'Vendedor', 'Exitoso'),
(94, '2026-11-03 00:35:48', 'Administrador@sistemaventas.cl', 'Administrador', 'Exitoso'),
(95, '2026-11-03 00:36:08', 'administrador@sistemaventas.cl', 'Administrador', 'Exitoso');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `logs_seguridad`
--

CREATE TABLE `logs_seguridad` (
  `id` int(11) NOT NULL,
  `usuario` varchar(100) NOT NULL,
  `accion` varchar(255) NOT NULL,
  `fecha` datetime NOT NULL DEFAULT current_timestamp(),
  `detalle` varchar(255) DEFAULT NULL,
  `ip_origen` varchar(45) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `logs_seguridad`
--

INSERT INTO `logs_seguridad` (`id`, `usuario`, `accion`, `fecha`, `detalle`, `ip_origen`) VALUES
(1, 'Supervisor Sistema', 'Acceso al Home del Supervisor', '2026-09-10 20:58:18', 'Ingreso al panel principal', '191.116.28.61'),
(2, 'Supervisor Sistema', 'Acceso al Home del Supervisor', '2026-09-10 21:00:37', 'Ingreso al panel principal', '191.116.28.61'),
(3, 'Supervisor Sistema', 'Acceso al Home del Supervisor', '2026-09-10 21:00:37', 'Ingreso al panel principal', '191.116.28.61'),
(4, 'Administrador Sistema', 'Acceso al Home del Administrador', '2026-09-10 21:01:59', 'Ingreso al panel principal', '191.116.28.61'),
(5, 'Administrador Sistema', 'Acceso al Home del Administrador', '2026-09-10 21:02:10', 'Ingreso al panel principal', '191.116.28.61'),
(6, 'Administrador Sistema', 'Acceso al Home del Administrador', '2026-09-10 21:04:49', 'Ingreso al panel principal', '191.116.28.61'),
(7, 'Supervisor Sistema', 'Acceso al Home del Supervisor', '2026-09-10 21:07:51', 'Ingreso al panel principal', '191.116.28.61'),
(8, 'Administrador Sistema', 'Acceso al Home del Administrador', '2026-09-10 21:07:54', 'Ingreso al panel principal', '191.116.28.61'),
(9, 'Administrador Sistema', 'Acceso al Home del Administrador', '2026-09-10 21:31:23', 'Ingreso al panel principal', '186.174.240.48'),
(10, 'Supervisor Sistema', 'Acceso al Home del Supervisor', '2026-09-10 21:31:55', 'Ingreso al panel principal', '186.174.240.48'),
(11, 'Administrador Sistema', 'Acceso al Home del Administrador', '2026-09-11 20:55:21', 'Ingreso al panel principal', '138.84.35.244'),
(12, 'Administrador Sistema', 'Acceso al Home del Administrador', '2026-09-13 10:39:44', 'Ingreso al panel principal', '200.111.21.98'),
(13, 'Supervisor Sistema', 'Acceso al Home del Supervisor', '2026-09-13 10:43:05', 'Ingreso al panel principal', '200.111.21.98'),
(14, 'Administrador Sistema', 'Acceso al Home del Administrador', '2026-09-13 19:02:24', 'Ingreso al panel principal', '201.219.234.237'),
(15, 'Administrador Sistema', 'Acceso al Home del Administrador', '2026-09-13 20:55:00', 'Ingreso al panel principal', '179.60.92.241'),
(16, 'Administrador Sistema', 'Acceso al Home del Administrador', '2026-09-14 03:07:27', 'Ingreso al panel principal', '179.60.89.64'),
(17, 'Administrador Sistema', 'Acceso al Home del Administrador', '2026-09-14 03:07:29', 'Ingreso al panel principal', '66.249.85.132'),
(18, 'Administrador Sistema', 'Acceso al Home del Administrador', '2026-09-14 03:07:30', 'Ingreso al panel principal', '66.102.8.105'),
(19, 'Administrador Sistema', 'Acceso al Home del Administrador', '2026-09-15 20:32:53', 'Ingreso al panel principal', '191.116.19.235'),
(20, 'Administrador Sistema', 'Acceso al Home del Administrador', '2026-09-15 20:35:13', 'Ingreso al panel principal', '191.116.19.235'),
(21, 'Administrador Sistema', 'Acceso al Home del Administrador', '2026-09-21 20:06:44', 'Ingreso al panel principal', '191.116.19.235'),
(22, 'Supervisor Sistema', 'Acceso al Home del Supervisor', '2026-09-21 20:07:17', 'Ingreso al panel principal', '191.116.19.235'),
(23, 'Administrador Sistema', 'Acceso al Home del Administrador', '2026-09-21 20:33:27', 'Ingreso al panel principal', '191.116.19.235'),
(24, 'Administrador Sistema', 'Acceso al Home del Administrador', '2026-09-21 20:36:45', 'Ingreso al panel principal', '191.116.19.235'),
(25, 'Supervisor Sistema', 'Acceso al Home del Supervisor', '2026-09-21 20:38:22', 'Ingreso al panel principal', '191.116.19.235'),
(26, 'Supervisor Sistema', 'Acceso al Home del Supervisor', '2026-09-21 20:40:58', 'Ingreso al panel principal', '191.116.19.235'),
(27, 'Administrador Sistema', 'Acceso al Home del Administrador', '2026-09-22 20:43:35', 'Ingreso al panel principal', '191.116.19.235'),
(28, 'Supervisor Sistema', 'Acceso al Home del Supervisor', '2026-09-22 20:44:18', 'Ingreso al panel principal', '191.116.19.235'),
(29, 'Administrador Sistema', 'Acceso al Home del Administrador', '2026-09-22 20:46:26', 'Ingreso al panel principal', '191.116.19.235'),
(30, 'Supervisor Sistema', 'Acceso al Home del Supervisor', '2026-09-22 20:59:19', 'Ingreso al panel principal', '181.42.252.4'),
(31, 'Supervisor Sistema', 'Acceso al Home del Supervisor', '2026-09-22 21:00:03', 'Ingreso al panel principal', '181.42.252.4'),
(32, 'Supervisor Sistema', 'Acceso al Home del Supervisor', '2026-09-22 21:04:03', 'Ingreso al panel principal', '181.42.252.4'),
(33, 'Supervisor Sistema', 'Acceso al Home del Supervisor', '2026-09-22 21:04:14', 'Ingreso al panel principal', '181.42.252.4'),
(34, 'Administrador Sistema', 'Acceso al Home del Administrador', '2026-09-22 21:23:41', 'Ingreso al panel principal', '181.42.252.4'),
(35, 'Administrador Sistema', 'Acceso al Home del Administrador', '2026-09-22 21:33:47', 'Ingreso al panel principal', '181.42.252.4'),
(36, 'Administrador Sistema', 'Acceso al Home del Administrador', '2026-09-22 21:58:26', 'Ingreso al panel principal', '181.42.252.4'),
(37, 'Administrador Sistema', 'Acceso al Home del Administrador', '2026-09-22 22:07:24', 'Ingreso al panel principal', '181.42.252.4'),
(38, 'Francisco Saavedra', 'Acceso al Home del Administrador', '2026-09-24 02:35:01', 'Ingreso al panel principal', '179.60.89.129'),
(39, 'Administrador Sistema', 'Acceso al Home del Administrador', '2026-09-24 16:28:10', 'Ingreso al panel principal', '181.42.249.242'),
(40, 'Supervisor Sistema', 'Acceso al Home del Supervisor', '2026-09-24 16:28:32', 'Ingreso al panel principal', '181.42.249.242'),
(41, 'Supervisor Sistema', 'Acceso al Home del Supervisor', '2026-09-24 16:34:35', 'Ingreso al panel principal', '181.42.249.242'),
(42, 'Supervisor Sistema', 'Acceso al Home del Supervisor', '2026-09-24 16:35:06', 'Ingreso al panel principal', '181.42.249.242'),
(43, 'Supervisor Sistema', 'Acceso al Home del Supervisor', '2026-09-24 16:35:19', 'Ingreso al panel principal', '181.42.249.242'),
(44, 'Administrador Sistema', 'Acceso al Home del Administrador', '2026-09-27 18:50:10', 'Ingreso al panel principal', '186.189.77.37'),
(45, 'Administrador Sistema', 'Acceso al Home del Administrador', '2026-09-27 18:50:23', 'Ingreso al panel principal', '186.189.77.37'),
(46, 'Administrador Sistema', 'Acceso al Home del Administrador', '2026-09-27 19:21:38', 'Ingreso al panel principal', '186.189.77.37'),
(47, 'Administrador Sistema', 'Acceso al Home del Administrador', '2026-09-27 20:04:45', 'Ingreso al panel principal', '186.189.77.37'),
(48, 'Administrador Sistema', 'Acceso al Home del Administrador', '2026-09-30 05:04:34', 'Ingreso al panel principal', '201.219.234.180'),
(49, 'Administrador Sistema', 'Acceso al Home del Administrador', '2026-09-30 14:47:42', 'Ingreso al panel principal', '186.189.104.180'),
(50, 'Administrador Sistema', 'Acceso al Home del Administrador', '2026-10-01 15:03:14', 'Ingreso al panel principal', '186.189.72.180'),
(51, 'Administrador Sistema', 'Acceso al Home del Administrador', '2026-10-01 15:03:22', 'Ingreso al panel principal', '186.189.72.180'),
(52, 'Administrador Sistema', 'Acceso al Home del Administrador', '2026-10-01 15:03:27', 'Ingreso al panel principal', '186.189.72.180'),
(53, 'Administrador Sistema', 'Acceso al Home del Administrador', '2026-10-01 16:18:10', 'Ingreso al panel principal', '186.189.72.180'),
(54, 'Administrador Sistema', 'Acceso al Home del Administrador', '2026-10-01 16:18:14', 'Ingreso al panel principal', '186.189.72.180'),
(55, 'Administrador Sistema', 'Acceso al Home del Administrador', '2026-10-01 16:18:16', 'Ingreso al panel principal', '186.189.72.180'),
(56, 'Administrador Sistema', 'Acceso al Home del Administrador', '2026-10-01 16:18:19', 'Ingreso al panel principal', '186.189.72.180'),
(57, 'Administrador Sistema', 'Acceso al Home del Administrador', '2026-10-01 16:39:06', 'Ingreso al panel principal', '186.189.72.180'),
(58, 'Administrador Sistema', 'Acceso al Home del Administrador', '2026-10-01 16:39:24', 'Ingreso al panel principal', '186.189.72.180'),
(59, 'Administrador Sistema', 'Acceso al Home del Administrador', '2026-10-01 16:41:03', 'Ingreso al panel principal', '186.189.72.180'),
(60, 'Administrador Sistema', 'Acceso al Home del Administrador', '2026-10-01 16:48:38', 'Ingreso al panel principal', '186.189.72.180'),
(61, 'Administrador Sistema', 'Acceso al Home del Administrador', '2026-10-01 16:51:29', 'Ingreso al panel principal', '186.189.72.180'),
(62, 'Administrador Sistema', 'Acceso al Home del Administrador', '2026-10-01 17:03:20', 'Ingreso al panel principal', '186.189.72.180'),
(63, 'Administrador Sistema', 'Acceso al Home del Administrador', '2026-10-04 23:32:43', 'Ingreso al panel principal', '138.84.35.128'),
(64, 'Administrador Sistema', 'Acceso al Home del Administrador', '2026-10-20 22:07:47', 'Ingreso al panel principal', '191.116.2.254'),
(65, 'Supervisor Sistema', 'Acceso al Home del Supervisor', '2026-10-20 22:08:21', 'Ingreso al panel principal', '191.116.2.254'),
(66, 'Administrador Sistema', 'Acceso al Home del Administrador', '2026-10-25 21:57:12', 'Ingreso al panel principal', '186.11.55.16'),
(67, 'Supervisor Sistema', 'Acceso al Home del Supervisor', '2026-10-25 22:00:43', 'Ingreso al panel principal', '186.11.55.16'),
(68, 'Supervisor Sistema', 'Acceso al Home del Supervisor', '2026-10-25 22:01:17', 'Ingreso al panel principal', '186.11.55.16'),
(69, 'Administrador Sistema', 'Acceso al Home del Administrador', '2026-10-28 20:06:12', 'Ingreso al panel principal', '191.116.4.219'),
(70, 'Administrador Sistema', 'Acceso al Home del Administrador', '2026-10-28 20:08:40', 'Ingreso al panel principal', '186.175.60.152'),
(71, 'Administrador Sistema', 'Acceso al Home del Administrador', '2026-10-28 20:09:35', 'Ingreso al panel principal', '186.175.60.152'),
(72, 'Administrador Sistema', 'Acceso al Home del Administrador', '2026-10-28 20:38:29', 'Ingreso al panel principal', '191.116.4.219'),
(73, 'Administrador Sistema', 'Acceso al Home del Administrador', '2026-10-28 20:39:14', 'Ingreso al panel principal', '191.116.4.219'),
(74, 'Administrador Sistema', 'Acceso al Home del Administrador', '2026-10-28 20:39:18', 'Ingreso al panel principal', '191.116.4.219'),
(75, 'Administrador Sistema', 'Acceso al Home del Administrador', '2026-10-28 20:48:15', 'Ingreso al panel principal', '191.116.4.219'),
(76, 'Administrador Sistema', 'Acceso al Home del Administrador', '2026-10-28 20:48:48', 'Ingreso al panel principal', '191.116.4.219'),
(77, 'Administrador Sistema', 'Acceso al Home del Administrador', '2026-10-28 20:55:32', 'Ingreso al panel principal', '191.116.4.219'),
(78, 'Administrador Sistema', 'Acceso al Home del Administrador', '2026-10-28 20:55:44', 'Ingreso al panel principal', '191.116.4.219'),
(79, 'Administrador Sistema', 'Acceso al Home del Administrador', '2026-10-29 19:30:14', 'Ingreso al panel principal', '186.175.199.132'),
(80, 'Administrador Sistema', 'Acceso al Home del Administrador', '2026-10-29 19:50:32', 'Ingreso al panel principal', '191.116.4.219'),
(81, 'Administrador Sistema', 'Acceso al Home del Administrador', '2026-10-29 19:50:36', 'Ingreso al panel principal', '190.208.14.69'),
(82, 'Administrador Sistema', 'Acceso al Home del Administrador', '2026-10-29 20:06:12', 'Ingreso al panel principal', '191.116.4.219'),
(83, 'Administrador Sistema', 'Acceso al Home del Administrador', '2026-10-29 20:11:24', 'Ingreso al panel principal', '191.116.4.219'),
(84, 'Administrador Sistema', 'Acceso al Home del Administrador', '2026-10-29 20:11:29', 'Ingreso al panel principal', '191.116.4.219'),
(85, 'Administrador Sistema', 'Acceso al Home del Administrador', '2026-10-29 20:16:04', 'Ingreso al panel principal', '191.116.4.219'),
(86, 'Administrador Sistema', 'Acceso al Home del Administrador', '2026-10-29 20:33:21', 'Ingreso al panel principal', '191.116.4.219'),
(87, 'Administrador Sistema', 'Acceso al Home del Administrador', '2026-10-29 21:55:30', 'Ingreso al panel principal', '191.116.4.219'),
(88, 'Administrador Sistema', 'Acceso al Home del Administrador', '2026-10-29 21:55:35', 'Ingreso al panel principal', '191.116.4.219'),
(89, 'Administrador Sistema', 'Acceso al Home del Administrador', '2026-10-29 22:03:05', 'Ingreso al panel principal', '191.116.4.219'),
(90, 'Administrador Sistema', 'Acceso al Home del Administrador', '2026-10-29 22:09:48', 'Ingreso al panel principal', '191.116.4.219'),
(91, 'Administrador Sistema', 'Acceso al Home del Administrador', '2026-10-29 22:11:26', 'Ingreso al panel principal', '191.116.4.219'),
(92, 'Administrador Sistema', 'Acceso al Home del Administrador', '2026-10-30 10:50:59', 'Ingreso al panel principal', '179.60.94.80'),
(93, 'Administrador Sistema', 'Acceso al Home del Administrador', '2026-10-30 10:52:51', 'Ingreso al panel principal', '179.60.94.80'),
(94, 'Administrador Sistema', 'Acceso al Home del Administrador', '2026-10-30 23:12:48', 'Ingreso al panel principal', '179.60.89.64'),
(95, 'Administrador Sistema', 'Acceso al Home del Administrador', '2026-10-30 23:12:59', 'Ingreso al panel principal', '179.60.89.64'),
(96, 'Administrador Sistema', 'Acceso al Home del Administrador', '2026-10-30 23:17:50', 'Ingreso al panel principal', '179.60.89.64'),
(97, 'Administrador Sistema', 'Acceso al Home del Administrador', '2026-10-30 23:17:54', 'Ingreso al panel principal', '179.60.89.64'),
(98, 'Administrador Sistema', 'Acceso al Home del Administrador', '2026-10-30 23:25:17', 'Ingreso al panel principal', '179.60.89.64'),
(99, 'Administrador Sistema', 'Acceso al Home del Administrador', '2026-10-30 23:47:53', 'Ingreso al panel principal', '179.60.89.64'),
(100, 'Administrador Sistema', 'Acceso al Home del Administrador', '2026-11-02 12:56:33', 'Ingreso al panel principal', '179.60.89.129'),
(101, 'Administrador Sistema', 'Acceso al Home del Administrador', '2026-11-02 12:56:42', 'Ingreso al panel principal', '179.60.89.129'),
(102, 'Administrador Sistema', 'Acceso al Home del Administrador', '2026-11-02 12:56:45', 'Ingreso al panel principal', '179.60.89.129'),
(103, 'Administrador Sistema', 'Acceso al Home del Administrador', '2026-11-02 12:56:46', 'Ingreso al panel principal', '179.60.89.129'),
(104, 'Administrador Sistema', 'Acceso al Home del Administrador', '2026-11-02 12:57:09', 'Ingreso al panel principal', '179.60.89.129'),
(105, 'Administrador Sistema', 'Acceso al Home del Administrador', '2026-11-02 20:03:21', 'Ingreso al panel principal', '191.116.4.219'),
(106, 'Administrador Sistema', 'Acceso al Home del Administrador', '2026-11-02 20:22:34', 'Ingreso al panel principal', '191.116.4.219'),
(107, 'Administrador Sistema', 'Acceso al Home del Administrador', '2026-11-02 20:22:46', 'Ingreso al panel principal', '191.116.4.219'),
(108, 'Administrador Sistema', 'Acceso al Home del Administrador', '2026-11-02 20:36:51', 'Ingreso al panel principal', '191.116.4.219'),
(109, 'Administrador Sistema', 'Acceso al Home del Administrador', '2026-11-02 20:37:17', 'Ingreso al panel principal', '191.116.4.219'),
(110, 'Jorge Valdivia Toro', 'Acceso al Home del Administrador', '2026-11-03 11:33:40', 'Ingreso al panel principal', '179.60.92.241'),
(111, 'Jorge Valdivia Toro', 'Acceso al Home del Administrador', '2026-11-03 11:47:48', 'Ingreso al panel principal', '179.60.92.241'),
(112, 'Jorge Valdivia Toro', 'Acceso al Home del Administrador', '2026-11-03 11:48:30', 'Ingreso al panel principal', '179.60.92.241'),
(113, 'Jorge Valdivia Toro', 'Acceso al Home del Administrador', '2026-11-03 12:02:15', 'Ingreso al panel principal', '179.60.92.241'),
(114, 'Jorge Valdivia Toro', 'Acceso al Home del Administrador', '2026-11-03 14:42:18', 'Ingreso al panel principal', '179.60.89.64'),
(115, 'Jorge Valdivia Toro', 'Acceso al Home del Administrador', '2026-11-03 14:42:53', 'Ingreso al panel principal', '179.60.89.64'),
(116, 'Jorge Valdivia Toro', 'Acceso al Home del Administrador', '2026-11-03 14:43:54', 'Ingreso al panel principal', '179.60.89.64'),
(117, 'Jorge Valdivia Toro', 'Acceso al Home del Administrador', '2026-11-03 14:43:57', 'Ingreso al panel principal', '179.60.89.64'),
(118, 'Jorge Valdivia Toro', 'Acceso al Home del Administrador', '2026-11-03 14:44:11', 'Ingreso al panel principal', '179.60.89.64'),
(119, 'Jorge Valdivia Toro', 'Acceso al Home del Administrador', '2026-11-03 14:44:39', 'Ingreso al panel principal', '179.60.89.64'),
(120, 'Jorge Valdivia Toro', 'Acceso al Home del Administrador', '2026-11-03 14:45:41', 'Ingreso al panel principal', '179.60.89.64'),
(121, 'Jorge Valdivia Toro', 'Acceso al Home del Administrador', '2026-11-03 14:46:00', 'Ingreso al panel principal', '179.60.89.64'),
(122, 'Maria Pinto', 'Acceso al Home del Supervisor', '2026-11-03 15:17:08', 'Ingreso al panel principal', '148.222.205.250'),
(123, 'Maria Pinto', 'Acceso al Home del Supervisor', '2026-11-03 15:17:32', 'Ingreso al panel principal', '148.222.205.250'),
(124, 'Maria Pinto', 'Acceso al Home del Supervisor', '2026-11-03 15:17:36', 'Ingreso al panel principal', '148.222.205.250'),
(125, 'Maria Pinto', 'Acceso al Home del Supervisor', '2026-11-03 15:17:38', 'Ingreso al panel principal', '148.222.205.250'),
(126, 'Maria Pinto', 'Acceso al Home del Supervisor', '2026-11-03 15:17:41', 'Ingreso al panel principal', '148.222.205.250'),
(127, 'Maria Pinto', 'Acceso al Home del Supervisor', '2026-11-03 15:18:31', 'Ingreso al panel principal', '148.222.205.250'),
(128, 'Maria Pinto', 'Acceso al Home del Supervisor', '2026-11-03 15:18:43', 'Ingreso al panel principal', '148.222.205.250'),
(129, 'Maria Pinto', 'Acceso al Home del Supervisor', '2026-11-03 15:18:51', 'Ingreso al panel principal', '148.222.205.250'),
(130, 'Maria Pinto', 'Acceso al Home del Supervisor', '2026-11-03 15:18:54', 'Ingreso al panel principal', '148.222.205.250'),
(131, 'Maria Pinto', 'Acceso al Home del Supervisor', '2026-11-03 15:19:03', 'Ingreso al panel principal', '148.222.205.250'),
(132, 'Jorge Valdivia Toro', 'Acceso al Home del Administrador', '2026-11-03 15:19:20', 'Ingreso al panel principal', '148.222.205.250'),
(133, 'Jorge Valdivia Toro', 'Acceso al Home del Administrador', '2026-11-03 15:19:41', 'Ingreso al panel principal', '148.222.205.250'),
(134, 'Maria Pinto', 'Acceso al Home del Supervisor', '2026-11-03 15:21:48', 'Ingreso al panel principal', '148.222.205.250'),
(135, 'Maria Pinto', 'Acceso al Home del Supervisor', '2026-11-03 15:21:58', 'Ingreso al panel principal', '148.222.205.250'),
(136, 'Maria Pinto', 'Acceso al Home del Supervisor', '2026-11-03 15:23:37', 'Ingreso al panel principal', '148.222.205.250'),
(137, 'Maria Pinto', 'Acceso al Home del Supervisor', '2026-11-03 15:27:49', 'Ingreso al panel principal', '148.222.205.250'),
(138, 'Jorge Valdivia Toro', 'Acceso al Home del Administrador', '2026-11-03 15:28:53', 'Ingreso al panel principal', '148.222.205.250'),
(139, 'Jorge Valdivia Toro', 'Acceso al Home del Administrador', '2026-11-03 15:29:12', 'Ingreso al panel principal', '148.222.205.250'),
(140, 'Jorge Valdivia Toro', 'Acceso al Home del Administrador', '2026-11-03 15:29:17', 'Ingreso al panel principal', '148.222.205.250'),
(141, 'Jorge Valdivia Toro', 'Acceso al Home del Administrador', '2026-11-03 15:29:20', 'Ingreso al panel principal', '148.222.205.250'),
(142, 'Jorge Valdivia Toro', 'Acceso al Home del Administrador', '2026-11-03 15:29:25', 'Ingreso al panel principal', '148.222.205.250'),
(143, 'Jorge Valdivia Toro', 'Acceso al Home del Administrador', '2026-11-03 15:29:31', 'Ingreso al panel principal', '148.222.205.250'),
(144, 'Jorge Valdivia Toro', 'Acceso al Home del Administrador', '2026-11-03 15:30:44', 'Ingreso al panel principal', '148.222.205.250'),
(145, 'Jorge Valdivia Toro', 'Acceso al Home del Administrador', '2026-11-03 15:31:53', 'Ingreso al panel principal', '148.222.205.250'),
(146, 'Jorge Valdivia Toro', 'Acceso al Home del Administrador', '2026-11-03 15:43:02', 'Ingreso al panel principal', '148.222.205.250'),
(147, 'Maria Pinto', 'Acceso al Home del Supervisor', '2026-11-03 15:43:15', 'Ingreso al panel principal', '148.222.205.250'),
(148, 'Jorge Valdivia Toro', 'Acceso al Home del Administrador', '2026-11-03 19:24:06', 'Ingreso al panel principal', '186.174.222.248'),
(149, 'Jorge Valdivia Toro', 'Acceso al Home del Administrador', '2026-11-03 19:24:21', 'Ingreso al panel principal', '186.174.222.248'),
(150, 'Jorge Valdivia Toro', 'Acceso al Home del Administrador', '2026-11-03 19:24:29', 'Ingreso al panel principal', '186.174.222.248'),
(151, 'Jorge Valdivia Toro', 'Acceso al Home del Administrador', '2026-11-03 19:24:38', 'Ingreso al panel principal', '186.174.222.248'),
(152, 'Maria Pinto', 'Acceso al Home del Supervisor', '2026-11-03 20:14:08', 'Ingreso al panel principal', '191.116.4.219'),
(153, 'Jorge Valdivia Toro', 'Acceso al Home del Administrador', '2026-11-03 20:27:17', 'Ingreso al panel principal', '186.174.222.248'),
(154, 'Jorge Valdivia Toro', 'Acceso al Home del Administrador', '2026-11-03 20:28:27', 'Ingreso al panel principal', '186.174.222.248'),
(155, 'Jorge Valdivia Toro', 'Acceso al Home del Administrador', '2026-11-03 20:28:36', 'Ingreso al panel principal', '186.174.222.248'),
(156, 'Maria Pinto', 'Acceso al Home del Supervisor', '2026-11-03 20:30:47', 'Ingreso al panel principal', '191.116.4.219'),
(157, 'Maria Pinto', 'Acceso al Home del Supervisor', '2026-11-03 20:31:07', 'Ingreso al panel principal', '191.116.4.219'),
(158, 'Jorge Valdivia Toro', 'Acceso al Home del Administrador', '2026-11-03 20:31:36', 'Ingreso al panel principal', '191.116.4.219'),
(159, 'Jorge Valdivia Toro', 'Acceso al Home del Administrador', '2026-11-03 20:33:23', 'Ingreso al panel principal', '191.116.4.219'),
(160, 'Jorge Valdivia Toro', 'Acceso al Home del Administrador', '2026-11-03 20:33:55', 'Ingreso al panel principal', '191.116.4.219'),
(161, 'Jorge Valdivia Toro', 'Acceso al Home del Administrador', '2026-11-03 20:35:49', 'Ingreso al panel principal', '191.116.4.219'),
(162, 'Maria Pinto', 'Acceso al Home del Supervisor', '2026-11-03 20:52:16', 'Ingreso al panel principal', '191.116.4.219'),
(163, 'Maria Pinto', 'Acceso al Home del Supervisor', '2026-11-03 21:00:11', 'Ingreso al panel principal', '191.116.4.219'),
(164, 'Jorge Valdivia Toro', 'Acceso al Home del Administrador', '2026-11-03 21:00:29', 'Ingreso al panel principal', '191.116.4.219'),
(165, 'Jorge Valdivia Toro', 'Acceso al Home del Administrador', '2026-11-03 21:01:01', 'Ingreso al panel principal', '186.174.222.248'),
(166, 'Jorge Valdivia Toro', 'Acceso al Home del Administrador', '2026-11-03 21:01:08', 'Ingreso al panel principal', '186.174.222.248'),
(167, 'Jorge Valdivia Toro', 'Acceso al Home del Administrador', '2026-11-03 21:01:38', 'Ingreso al panel principal', '186.174.222.248'),
(168, 'Jorge Valdivia Toro', 'Acceso al Home del Administrador', '2026-11-03 21:01:41', 'Ingreso al panel principal', '186.174.222.248'),
(169, 'Jorge Valdivia Toro', 'Acceso al Home del Administrador', '2026-11-03 21:01:44', 'Ingreso al panel principal', '186.174.222.248'),
(170, 'Jorge Valdivia Toro', 'Acceso al Home del Administrador', '2026-11-03 21:01:53', 'Ingreso al panel principal', '186.174.222.248'),
(171, 'Jorge Valdivia Toro', 'Acceso al Home del Administrador', '2026-11-03 21:01:57', 'Ingreso al panel principal', '186.174.222.248'),
(172, 'Jorge Valdivia Toro', 'Acceso al Home del Administrador', '2026-11-03 21:01:59', 'Ingreso al panel principal', '191.116.4.219'),
(173, 'Maria Pinto', 'Acceso al Home del Supervisor', '2026-11-03 21:02:07', 'Ingreso al panel principal', '191.116.4.219'),
(174, 'Jorge Valdivia Toro', 'Acceso al Home del Administrador', '2026-11-03 21:04:18', 'Ingreso al panel principal', '186.174.222.248'),
(175, 'Maria Pinto', 'Acceso al Home del Supervisor', '2026-11-03 21:04:59', 'Ingreso al panel principal', '191.116.4.219'),
(176, 'Maria Pinto', 'Acceso al Home del Supervisor', '2026-11-03 21:07:21', 'Ingreso al panel principal', '191.116.4.219'),
(177, 'Maria Pinto', 'Acceso al Home del Supervisor', '2026-11-03 21:08:32', 'Ingreso al panel principal', '191.116.4.219'),
(178, 'Jorge Valdivia Toro', 'Acceso al Home del Administrador', '2026-11-04 00:01:27', 'Ingreso al panel principal', '148.222.205.250'),
(179, 'Jorge Valdivia Toro', 'Acceso al Home del Administrador', '2026-11-04 00:01:29', 'Ingreso al panel principal', '148.222.205.250'),
(180, 'Jorge Valdivia Toro', 'Acceso al Home del Administrador', '2026-11-04 00:02:02', 'Ingreso al panel principal', '148.222.205.250'),
(181, 'Jorge Valdivia Toro', 'Acceso al Home del Administrador', '2026-11-04 00:02:09', 'Ingreso al panel principal', '148.222.205.250'),
(182, 'Jorge Valdivia Toro', 'Acceso al Home del Administrador', '2026-11-04 00:21:59', 'Ingreso al panel principal', '179.60.89.48'),
(183, 'Maria Pinto', 'Acceso al Home del Supervisor', '2026-11-04 00:30:49', 'Ingreso al panel principal', '179.60.89.48'),
(184, 'Jorge Valdivia Toro', 'Acceso al Home del Administrador', '2026-11-04 00:32:13', 'Ingreso al panel principal', '179.60.89.48'),
(185, 'Jorge Valdivia Toro', 'Acceso al Home del Administrador', '2026-11-04 00:33:11', 'Ingreso al panel principal', '179.60.89.48'),
(186, 'Jorge Valdivia Toro', 'Acceso al Home del Administrador', '2026-11-04 00:33:34', 'Ingreso al panel principal', '179.60.89.48'),
(187, 'Jorge Valdivia Toro', 'Acceso al Home del Administrador', '2026-11-04 00:33:39', 'Ingreso al panel principal', '179.60.89.48'),
(188, 'Jorge Valdivia Toro', 'Acceso al Home del Administrador', '2026-11-04 00:35:08', 'Ingreso al panel principal', '179.60.89.48'),
(189, 'Jorge Valdivia Toro', 'Acceso al Home del Administrador', '2026-11-04 00:35:20', 'Ingreso al panel principal', '179.60.89.48'),
(190, 'Jorge Valdivia Toro', 'Acceso al Home del Administrador', '2026-11-04 00:36:38', 'Ingreso al panel principal', '179.60.89.48'),
(191, 'Jorge Valdivia Toro', 'Acceso al Home del Administrador', '2026-11-04 00:36:46', 'Ingreso al panel principal', '179.60.89.48'),
(192, 'Jorge Valdivia Toro', 'Acceso al Home del Administrador', '2026-11-04 00:39:12', 'Ingreso al panel principal', '179.60.89.48'),
(193, 'Jorge Valdivia Toro', 'Acceso al Home del Administrador', '2026-11-04 00:39:34', 'Ingreso al panel principal', '179.60.89.48'),
(194, 'Jorge Valdivia Toro', 'Acceso al Home del Administrador', '2026-11-04 00:40:21', 'Ingreso al panel principal', '179.60.89.48'),
(195, 'Jorge Valdivia Toro', 'Acceso al Home del Administrador', '2026-11-04 00:40:58', 'Ingreso al panel principal', '179.60.89.48'),
(196, 'Maria Pinto', 'Acceso al Home del Supervisor', '2026-11-04 00:42:42', 'Ingreso al panel principal', '179.60.89.48'),
(197, 'Maria Pinto', 'Acceso al Home del Supervisor', '2026-11-04 00:43:18', 'Ingreso al panel principal', '179.60.89.48'),
(198, 'Maria Pinto', 'Acceso al Home del Supervisor', '2026-11-04 00:43:39', 'Ingreso al panel principal', '179.60.89.48'),
(199, 'Maria Pinto', 'Acceso al Home del Supervisor', '2026-11-04 00:44:13', 'Ingreso al panel principal', '179.60.89.48'),
(200, 'Maria Pinto', 'Acceso al Home del Supervisor', '2026-11-04 00:44:29', 'Ingreso al panel principal', '179.60.89.48'),
(201, 'Jorge Valdivia Toro', 'Acceso al Home del Administrador', '2026-11-04 00:45:24', 'Ingreso al panel principal', '179.60.89.48'),
(202, 'Jorge Valdivia Toro', 'Acceso al Home del Administrador', '2026-11-04 01:35:30', 'Ingreso al panel principal', '201.219.233.74'),
(203, 'Jorge Valdivia Toro', 'Acceso al Home del Administrador', '2026-11-04 01:37:02', 'Ingreso al panel principal', '201.219.233.74'),
(204, 'Maria Pinto', 'Acceso al Home del Supervisor', '2026-11-04 14:10:52', 'Ingreso al panel principal', '186.189.95.26'),
(205, 'Maria Pinto', 'Acceso al Home del Supervisor', '2026-11-04 14:11:56', 'Ingreso al panel principal', '186.189.95.26'),
(206, 'Jorge Valdivia Toro', 'Acceso al Home del Administrador', '2026-11-04 21:24:29', 'Ingreso al panel principal', '191.116.4.219'),
(207, 'Maria Pinto', 'Acceso al Home del Supervisor', '2026-11-04 21:26:27', 'Ingreso al panel principal', '191.116.4.219'),
(208, 'Maria Pinto', 'Acceso al Home del Supervisor', '2026-11-04 21:26:49', 'Ingreso al panel principal', '191.116.4.219'),
(209, 'Jorge Valdivia Toro', 'Acceso al Home del Administrador', '2026-11-04 23:22:46', 'Ingreso al panel principal', '148.227.97.117'),
(210, 'Jorge Valdivia Toro', 'Acceso al Home del Administrador', '2026-11-04 23:34:55', 'Ingreso al panel principal', '148.227.97.117'),
(211, 'Jorge Valdivia Toro', 'Acceso al Home del Administrador', '2026-11-04 23:35:55', 'Ingreso al panel principal', '148.227.97.117'),
(212, 'Jorge Valdivia Toro', 'Acceso al Home del Administrador', '2026-11-04 23:36:09', 'Ingreso al panel principal', '148.227.97.117'),
(213, 'Jorge Valdivia Toro', 'Acceso al Home del Administrador', '2026-11-04 23:36:27', 'Ingreso al panel principal', '148.227.97.117'),
(214, 'Jorge Valdivia Toro', 'Acceso al Home del Administrador', '2026-11-04 23:36:44', 'Ingreso al panel principal', '148.227.97.117'),
(215, 'Jorge Valdivia Toro', 'Acceso al Home del Administrador', '2026-11-04 23:36:56', 'Ingreso al panel principal', '148.227.97.117'),
(216, 'Jorge Valdivia Toro', 'Acceso al Home del Administrador', '2026-11-04 23:37:11', 'Ingreso al panel principal', '148.227.97.117'),
(217, 'Jorge Valdivia Toro', 'Acceso al Home del Administrador', '2026-11-04 23:37:46', 'Ingreso al panel principal', '148.227.97.117'),
(218, 'Jorge Valdivia Toro', 'Acceso al Home del Administrador', '2026-11-04 23:38:00', 'Ingreso al panel principal', '148.227.97.117'),
(219, 'Jorge Valdivia Toro', 'Acceso al Home del Administrador', '2026-11-04 23:38:28', 'Ingreso al panel principal', '148.227.97.117'),
(220, 'Jorge Valdivia Toro', 'Acceso al Home del Administrador', '2026-11-04 23:39:57', 'Ingreso al panel principal', '148.227.97.117'),
(221, 'Jorge Valdivia Toro', 'Acceso al Home del Administrador', '2026-11-04 23:40:28', 'Ingreso al panel principal', '148.227.97.117'),
(222, 'Jorge Valdivia Toro', 'Acceso al Home del Administrador', '2026-11-04 23:40:35', 'Ingreso al panel principal', '148.227.97.117'),
(223, 'Jorge Valdivia Toro', 'Acceso al Home del Administrador', '2026-11-04 23:41:05', 'Ingreso al panel principal', '148.227.97.117'),
(224, 'Jorge Valdivia Toro', 'Acceso al Home del Administrador', '2026-11-04 23:41:27', 'Ingreso al panel principal', '148.227.97.117'),
(225, 'Jorge Valdivia Toro', 'Acceso al Home del Administrador', '2026-11-04 23:42:04', 'Ingreso al panel principal', '148.227.97.117'),
(226, 'Jorge Valdivia Toro', 'Acceso al Home del Administrador', '2026-11-04 23:42:45', 'Ingreso al panel principal', '148.227.97.117'),
(227, 'Jorge Valdivia Toro', 'Acceso al Home del Administrador', '2026-11-04 23:58:43', 'Ingreso al panel principal', '179.60.89.129'),
(228, 'Maria Pinto', 'Acceso al Home del Supervisor', '2026-11-04 23:59:15', 'Ingreso al panel principal', '186.189.74.26'),
(229, 'Maria Pinto', 'Acceso al Home del Supervisor', '2026-11-05 00:00:48', 'Ingreso al panel principal', '179.60.89.129'),
(230, 'Jorge Valdivia Toro', 'Acceso al Home del Administrador', '2026-11-05 00:01:13', 'Ingreso al panel principal', '179.60.89.129'),
(231, 'Jorge Valdivia Toro', 'Acceso al Home del Administrador', '2026-11-05 00:03:03', 'Ingreso al panel principal', '179.60.89.129'),
(232, 'Maria Pinto', 'Acceso al Home del Supervisor', '2026-11-05 00:07:54', 'Ingreso al panel principal', '186.189.74.26'),
(233, 'Jorge Valdivia Toro', 'Acceso al Home del Administrador', '2026-11-05 00:22:06', 'Ingreso al panel principal', '179.60.89.48'),
(234, 'Jorge Valdivia Toro', 'Acceso al Home del Administrador', '2026-11-05 00:40:16', 'Ingreso al panel principal', '179.60.89.129'),
(235, 'Jorge Valdivia Toro', 'Acceso al Home del Administrador', '2026-11-05 00:40:49', 'Ingreso al panel principal', '179.60.89.129'),
(236, 'Jorge Valdivia Toro', 'Acceso al Home del Administrador', '2026-11-05 10:01:04', 'Ingreso al panel principal', '191.126.82.59'),
(237, 'Jorge Valdivia Toro', 'Acceso al Home del Administrador', '2026-11-05 10:14:54', 'Ingreso al panel principal', '200.73.122.237'),
(238, 'Jorge Valdivia Toro', 'Acceso al Home del Administrador', '2026-11-05 11:59:29', 'Ingreso al panel principal', '179.60.89.64'),
(239, 'Jorge Valdivia Toro', 'Acceso al Home del Administrador', '2026-11-05 11:59:45', 'Ingreso al panel principal', '179.60.89.64'),
(240, 'Jorge Valdivia Toro', 'Acceso al Home del Administrador', '2026-11-05 11:59:51', 'Ingreso al panel principal', '179.60.89.64'),
(241, 'Jorge Valdivia Toro', 'Acceso al Home del Administrador', '2026-11-05 11:59:55', 'Ingreso al panel principal', '179.60.89.64'),
(242, 'Jorge Valdivia Toro', 'Acceso al Home del Administrador', '2026-11-05 12:00:03', 'Ingreso al panel principal', '179.60.89.64'),
(243, 'Maria Pinto', 'Acceso al Home del Supervisor', '2026-11-05 12:00:13', 'Ingreso al panel principal', '179.60.89.64'),
(244, 'Maria Pinto', 'Acceso al Home del Supervisor', '2026-11-05 12:00:27', 'Ingreso al panel principal', '179.60.89.64'),
(245, 'Jorge Valdivia Toro', 'Acceso al Home del Administrador', '2026-11-05 12:43:43', 'Ingreso al panel principal', '191.126.82.59'),
(246, 'Jorge Valdivia Toro', 'Acceso al Home del Administrador', '2026-11-05 16:26:37', 'Ingreso al panel principal', '179.60.89.64'),
(247, 'Jorge Valdivia Toro', 'Acceso al Home del Administrador', '2026-11-05 16:27:13', 'Ingreso al panel principal', '179.60.89.64'),
(248, 'Jorge Valdivia Toro', 'Acceso al Home del Administrador', '2026-11-05 16:28:58', 'Ingreso al panel principal', '179.60.89.64'),
(249, 'Jorge Valdivia Toro', 'Acceso al Home del Administrador', '2026-11-05 16:30:22', 'Ingreso al panel principal', '179.60.89.64'),
(250, 'Jorge Valdivia Toro', 'Acceso al Home del Administrador', '2026-11-05 16:44:48', 'Ingreso al panel principal', '179.60.89.64'),
(251, 'Jorge Valdivia Toro', 'Acceso al Home del Administrador', '2026-11-05 16:44:57', 'Ingreso al panel principal', '179.60.89.64'),
(252, 'Jorge Valdivia Toro', 'Acceso al Home del Administrador', '2026-11-05 16:45:00', 'Ingreso al panel principal', '179.60.89.64'),
(253, 'Jorge Valdivia Toro', 'Acceso al Home del Administrador', '2026-11-05 16:45:07', 'Ingreso al panel principal', '179.60.89.64'),
(254, 'Jorge Valdivia Toro', 'Acceso al Home del Administrador', '2026-11-05 16:45:10', 'Ingreso al panel principal', '179.60.89.64'),
(255, 'Jorge Valdivia Toro', 'Acceso al Home del Administrador', '2026-11-05 16:45:14', 'Ingreso al panel principal', '179.60.89.64'),
(256, 'Jorge Valdivia Toro', 'Acceso al Home del Administrador', '2026-11-05 17:27:49', 'Ingreso al panel principal', '179.60.92.241'),
(257, 'Jorge Valdivia Toro', 'Acceso al Home del Administrador', '2026-11-05 17:28:05', 'Ingreso al panel principal', '179.60.92.241'),
(258, 'Jorge Valdivia Toro', 'Acceso al Home del Administrador', '2026-11-05 18:31:38', 'Ingreso al panel principal', '186.174.27.53'),
(259, 'Jorge Valdivia Toro', 'Acceso al Home del Administrador', '2026-11-05 18:31:55', 'Ingreso al panel principal', '186.174.27.53'),
(260, 'Jorge Valdivia Toro', 'Acceso al Home del Administrador', '2026-11-05 18:37:09', 'Ingreso al panel principal', '186.174.27.53'),
(261, 'Maria Pinto', 'Acceso al Home del Supervisor', '2026-11-05 19:16:47', 'Ingreso al panel principal', '191.116.4.219'),
(262, 'Jorge Valdivia Toro', 'Acceso al Home del Administrador', '2026-11-05 19:21:33', 'Ingreso al panel principal', '191.116.4.219'),
(263, 'Jorge Valdivia Toro', 'Acceso al Home del Administrador', '2026-11-05 19:27:51', 'Ingreso al panel principal', '191.116.4.219'),
(264, 'Maria Pinto', 'Acceso al Home del Supervisor', '2026-11-05 19:27:57', 'Ingreso al panel principal', '191.116.4.219'),
(265, 'Jorge Valdivia Toro', 'Acceso al Home del Administrador', '2026-11-05 19:28:58', 'Ingreso al panel principal', '191.116.4.219'),
(266, 'Maria Pinto', 'Acceso al Home del Supervisor', '2026-11-05 19:29:59', 'Ingreso al panel principal', '186.41.169.167'),
(267, 'Jorge Valdivia Toro', 'Acceso al Home del Administrador', '2026-11-05 19:34:26', 'Ingreso al panel principal', '164.77.218.117'),
(268, 'Jorge Valdivia Toro', 'Acceso al Home del Administrador', '2026-11-05 19:36:47', 'Ingreso al panel principal', '164.77.218.117'),
(269, 'Jorge Valdivia Toro', 'Acceso al Home del Administrador', '2026-11-05 19:37:33', 'Ingreso al panel principal', '164.77.218.117'),
(270, 'Jorge Valdivia Toro', 'Acceso al Home del Administrador', '2026-11-05 19:37:48', 'Ingreso al panel principal', '164.77.218.117'),
(271, 'Jorge Valdivia Toro', 'Acceso al Home del Administrador', '2026-11-05 19:38:37', 'Ingreso al panel principal', '164.77.218.117'),
(272, 'Jorge Valdivia Toro', 'Acceso al Home del Administrador', '2026-11-05 19:38:57', 'Ingreso al panel principal', '164.77.218.117'),
(273, 'Maria Pinto', 'Acceso al Home del Supervisor', '2026-11-05 19:38:58', 'Ingreso al panel principal', '191.116.4.219'),
(274, 'Jorge Valdivia Toro', 'Acceso al Home del Administrador', '2026-11-05 19:39:39', 'Ingreso al panel principal', '191.116.4.219'),
(275, 'Jorge Valdivia Toro', 'Acceso al Home del Administrador', '2026-11-05 19:42:30', 'Ingreso al panel principal', '164.77.218.117'),
(276, 'Jorge Valdivia Toro', 'Acceso al Home del Administrador', '2026-11-05 19:42:32', 'Ingreso al panel principal', '164.77.218.117'),
(277, 'Jorge Valdivia Toro', 'Acceso al Home del Administrador', '2026-11-05 19:42:33', 'Ingreso al panel principal', '164.77.218.117'),
(278, 'Jorge Valdivia Toro', 'Acceso al Home del Administrador', '2026-11-05 19:42:33', 'Ingreso al panel principal', '164.77.218.117'),
(279, 'Jorge Valdivia Toro', 'Acceso al Home del Administrador', '2026-11-05 19:42:33', 'Ingreso al panel principal', '164.77.218.117'),
(280, 'Jorge Valdivia Toro', 'Acceso al Home del Administrador', '2026-11-05 19:42:33', 'Ingreso al panel principal', '164.77.218.117'),
(281, 'Jorge Valdivia Toro', 'Acceso al Home del Administrador', '2026-11-05 19:42:34', 'Ingreso al panel principal', '164.77.218.117'),
(282, 'Jorge Valdivia Toro', 'Acceso al Home del Administrador', '2026-11-05 19:42:34', 'Ingreso al panel principal', '164.77.218.117'),
(283, 'Jorge Valdivia Toro', 'Acceso al Home del Administrador', '2026-11-05 19:42:34', 'Ingreso al panel principal', '164.77.218.117'),
(284, 'Jorge Valdivia Toro', 'Acceso al Home del Administrador', '2026-11-05 19:42:34', 'Ingreso al panel principal', '164.77.218.117'),
(285, 'Jorge Valdivia Toro', 'Acceso al Home del Administrador', '2026-11-05 19:42:35', 'Ingreso al panel principal', '164.77.218.117'),
(286, 'Jorge Valdivia Toro', 'Acceso al Home del Administrador', '2026-11-05 19:42:36', 'Ingreso al panel principal', '164.77.218.117'),
(287, 'Jorge Valdivia Toro', 'Acceso al Home del Administrador', '2026-11-05 19:45:11', 'Ingreso al panel principal', '164.77.218.117'),
(288, 'Jorge Valdivia Toro', 'Acceso al Home del Administrador', '2026-11-05 19:46:09', 'Ingreso al panel principal', '191.116.4.219'),
(289, 'Jorge Valdivia Toro', 'Acceso al Home del Administrador', '2026-11-05 19:46:14', 'Ingreso al panel principal', '164.77.218.117'),
(290, 'Jorge Valdivia Toro', 'Acceso al Home del Administrador', '2026-11-05 19:46:48', 'Ingreso al panel principal', '164.77.218.117'),
(291, 'Maria Pinto', 'Acceso al Home del Supervisor', '2026-11-05 19:46:54', 'Ingreso al panel principal', '191.116.4.219'),
(292, 'Maria Pinto', 'Acceso al Home del Supervisor', '2026-11-05 19:49:01', 'Ingreso al panel principal', '148.227.67.142'),
(293, 'Jorge Valdivia Toro', 'Acceso al Home del Administrador', '2026-11-05 19:49:08', 'Ingreso al panel principal', '191.116.4.219'),
(294, 'Jorge Valdivia Toro', 'Acceso al Home del Administrador', '2026-11-05 19:50:49', 'Ingreso al panel principal', '191.116.4.219'),
(295, 'Maria Pinto', 'Acceso al Home del Supervisor', '2026-11-05 19:51:36', 'Ingreso al panel principal', '148.227.67.142'),
(296, 'Maria Pinto', 'Acceso al Home del Supervisor', '2026-11-05 19:51:45', 'Ingreso al panel principal', '148.227.67.142'),
(297, 'Maria Pinto', 'Acceso al Home del Supervisor', '2026-11-05 19:52:17', 'Ingreso al panel principal', '148.227.67.142'),
(298, 'Jorge Valdivia Toro', 'Acceso al Home del Administrador', '2026-11-05 19:53:02', 'Ingreso al panel principal', '148.227.67.142'),
(299, 'Jorge Valdivia Toro', 'Acceso al Home del Administrador', '2026-11-05 19:53:04', 'Ingreso al panel principal', '191.116.4.219'),
(300, 'Jorge Valdivia Toro', 'Acceso al Home del Administrador', '2026-11-05 19:53:27', 'Ingreso al panel principal', '148.227.67.142'),
(301, 'Jorge Valdivia Toro', 'Acceso al Home del Administrador', '2026-11-05 19:53:30', 'Ingreso al panel principal', '148.227.67.142'),
(302, 'Jorge Valdivia Toro', 'Acceso al Home del Administrador', '2026-11-05 19:53:35', 'Ingreso al panel principal', '148.227.67.142'),
(303, 'Jorge Valdivia Toro', 'Acceso al Home del Administrador', '2026-11-05 19:53:40', 'Ingreso al panel principal', '191.116.4.219'),
(304, 'Jorge Valdivia Toro', 'Acceso al Home del Administrador', '2026-11-05 19:53:49', 'Ingreso al panel principal', '191.116.4.219'),
(305, 'Jorge Valdivia Toro', 'Acceso al Home del Administrador', '2026-11-05 19:54:44', 'Ingreso al panel principal', '186.174.27.53'),
(306, 'Maria Pinto', 'Acceso al Home del Supervisor', '2026-11-05 19:54:51', 'Ingreso al panel principal', '191.116.4.219'),
(307, 'Jorge Valdivia Toro', 'Acceso al Home del Administrador', '2026-11-05 19:54:55', 'Ingreso al panel principal', '191.116.4.219'),
(308, 'Jorge Valdivia Toro', 'Acceso al Home del Administrador', '2026-11-05 19:54:59', 'Ingreso al panel principal', '191.116.4.219'),
(309, 'Jorge Valdivia Toro', 'Acceso al Home del Administrador', '2026-11-05 19:55:06', 'Ingreso al panel principal', '186.174.27.53'),
(310, 'Maria Pinto', 'Acceso al Home del Supervisor', '2026-11-05 19:55:30', 'Ingreso al panel principal', '191.116.4.219'),
(311, 'Maria Pinto', 'Acceso al Home del Supervisor', '2026-11-05 19:58:27', 'Ingreso al panel principal', '191.116.4.219'),
(312, 'Jorge Valdivia Toro', 'Acceso al Home del Administrador', '2026-11-05 20:23:41', 'Ingreso al panel principal', '186.174.27.53'),
(313, 'Jorge Valdivia Toro', 'Acceso al Home del Administrador', '2026-11-05 20:24:45', 'Ingreso al panel principal', '191.116.4.219'),
(314, 'Jorge Valdivia Toro', 'Acceso al Home del Administrador', '2026-11-05 20:24:48', 'Ingreso al panel principal', '186.174.27.53'),
(315, 'Jorge Valdivia Toro', 'Acceso al Home del Administrador', '2026-11-05 20:35:03', 'Ingreso al panel principal', '148.227.67.142'),
(316, 'Jorge Valdivia Toro', 'Acceso al Home del Administrador', '2026-11-05 20:35:12', 'Ingreso al panel principal', '148.227.67.142'),
(317, 'Jorge Valdivia Toro', 'Acceso al Home del Administrador', '2026-11-05 20:35:31', 'Ingreso al panel principal', '148.227.67.142'),
(318, 'Jorge Valdivia Toro', 'Acceso al Home del Administrador', '2026-11-05 20:41:01', 'Ingreso al panel principal', '148.227.67.142'),
(319, 'Jorge Valdivia Toro', 'Acceso al Home del Administrador', '2026-11-05 20:41:16', 'Ingreso al panel principal', '148.227.67.142'),
(320, 'Jorge Valdivia Toro', 'Acceso al Home del Administrador', '2026-11-05 20:43:15', 'Ingreso al panel principal', '191.116.4.219'),
(321, 'Jorge Valdivia Toro', 'Acceso al Home del Administrador', '2026-11-05 20:46:39', 'Ingreso al panel principal', '148.227.67.142'),
(322, 'Jorge Valdivia Toro', 'Acceso al Home del Administrador', '2026-11-05 21:21:32', 'Ingreso al panel principal', '148.227.67.142'),
(323, 'Jorge Valdivia Toro', 'Acceso al Home del Administrador', '2026-11-05 21:25:13', 'Ingreso al panel principal', '191.116.4.219'),
(324, 'Jorge Valdivia Toro', 'Acceso al Home del Administrador', '2026-11-05 21:30:59', 'Ingreso al panel principal', '186.174.27.53'),
(325, 'Jorge Valdivia Toro', 'Acceso al Home del Administrador', '2026-11-05 21:34:50', 'Ingreso al panel principal', '191.116.4.219'),
(326, 'Jorge Valdivia Toro', 'Acceso al Home del Administrador', '2026-11-05 21:35:53', 'Ingreso al panel principal', '191.116.4.219'),
(327, 'Jorge Valdivia Toro', 'Acceso al Home del Administrador', '2026-11-06 11:00:12', 'Ingreso al panel principal', '179.60.89.64'),
(328, 'Maria Pinto', 'Acceso al Home del Supervisor', '2026-11-06 11:23:42', 'Ingreso al panel principal', '186.189.91.4'),
(329, 'Jorge Valdivia Toro', 'Acceso al Home del Administrador', '2026-11-06 11:25:25', 'Ingreso al panel principal', '186.189.91.4'),
(330, 'Jorge Valdivia Toro', 'Acceso al Home del Administrador', '2026-11-06 11:25:37', 'Ingreso al panel principal', '186.189.91.4'),
(331, 'Jorge Valdivia Toro', 'Acceso al Home del Administrador', '2026-11-06 11:25:39', 'Ingreso al panel principal', '186.189.91.4'),
(332, 'Jorge Valdivia Toro', 'Acceso al Home del Administrador', '2026-11-06 11:25:46', 'Ingreso al panel principal', '186.189.91.4'),
(333, 'Jorge Valdivia Toro', 'Acceso al Home del Administrador', '2026-11-06 14:04:39', 'Ingreso al panel principal', '179.60.89.64'),
(334, 'Jorge Valdivia Toro', 'Acceso al Home del Administrador', '2026-11-06 14:05:04', 'Ingreso al panel principal', '179.60.89.64'),
(335, 'Jorge Valdivia Toro', 'Acceso al Home del Administrador', '2026-11-06 14:22:45', 'Ingreso al panel principal', '179.60.89.64'),
(336, 'Jorge Valdivia Toro', 'Acceso al Home del Administrador', '2026-11-06 14:24:59', 'Ingreso al panel principal', '179.60.89.64'),
(337, 'Jorge Valdivia Toro', 'Acceso al Home del Administrador', '2026-11-06 14:25:07', 'Ingreso al panel principal', '179.60.89.64'),
(338, 'Jorge Valdivia Toro', 'Acceso al Home del Administrador', '2026-11-06 14:43:14', 'Ingreso al panel principal', '179.60.89.64'),
(339, 'Jorge Valdivia Toro', 'Acceso al Home del Administrador', '2026-11-06 14:44:04', 'Ingreso al panel principal', '179.60.89.64'),
(340, 'Jorge Valdivia Toro', 'Acceso al Home del Administrador', '2026-11-06 14:56:45', 'Ingreso al panel principal', '179.60.89.64'),
(341, 'Jorge Valdivia Toro', 'Acceso al Home del Administrador', '2026-11-06 14:57:53', 'Ingreso al panel principal', '179.60.89.64'),
(342, 'Jorge Valdivia Toro', 'Acceso al Home del Administrador', '2026-11-06 14:58:44', 'Ingreso al panel principal', '179.60.89.64'),
(343, 'Jorge Valdivia Toro', 'Acceso al Home del Administrador', '2026-11-06 21:28:07', 'Ingreso al panel principal', '179.60.89.64'),
(344, 'Jorge Valdivia Toro', 'Acceso al Home del Administrador', '2026-11-06 21:30:35', 'Ingreso al panel principal', '179.60.89.64'),
(345, 'Jorge Valdivia Toro', 'Acceso al Home del Administrador', '2026-11-06 23:10:52', 'Ingreso al panel principal', '179.60.89.64'),
(346, 'Jorge Valdivia Toro', 'Acceso al Home del Administrador', '2026-11-06 23:18:48', 'Ingreso al panel principal', '179.60.89.64'),
(347, 'Jorge Valdivia Toro', 'Acceso al Home del Administrador', '2026-11-06 23:29:29', 'Ingreso al panel principal', '179.60.89.64'),
(348, 'Jorge Valdivia Toro', 'Acceso al Home del Administrador', '2026-11-06 23:31:37', 'Ingreso al panel principal', '179.60.89.64'),
(349, 'Jorge Valdivia Toro', 'Acceso al Home del Administrador', '2026-11-06 23:32:06', 'Ingreso al panel principal', '179.60.89.64'),
(350, 'Maria Pinto', 'Acceso al Home del Supervisor', '2026-11-06 23:58:55', 'Ingreso al panel principal', '179.60.89.64'),
(351, 'Maria Pinto', 'Acceso al Home del Supervisor', '2026-11-07 00:02:02', 'Ingreso al panel principal', '179.60.89.64'),
(352, 'Jorge Valdivia Toro', 'Acceso al Home del Administrador', '2026-11-07 12:18:56', 'Ingreso al panel principal', '179.60.89.129'),
(353, 'Maria Pinto', 'Acceso al Home del Supervisor', '2026-11-07 12:19:13', 'Ingreso al panel principal', '179.60.89.129'),
(354, 'Maria Pinto', 'Acceso al Home del Supervisor', '2026-11-07 12:29:52', 'Ingreso al panel principal', '186.189.103.222'),
(355, 'Maria Pinto', 'Acceso al Home del Supervisor', '2026-11-07 12:33:57', 'Ingreso al panel principal', '186.189.103.222'),
(356, 'Maria pinto', 'Acceso al Home del Supervisor', '2026-11-07 12:48:50', 'Ingreso al panel principal', '179.60.89.64'),
(357, 'Maria pinto', 'Acceso al Home del Supervisor', '2026-11-07 12:51:22', 'Ingreso al panel principal', '179.60.89.64'),
(358, 'Maria pinto', 'Acceso al Home del Supervisor', '2026-11-07 12:52:01', 'Ingreso al panel principal', '179.60.89.64'),
(359, 'Maria pinto', 'Acceso al Home del Supervisor', '2026-11-07 12:52:28', 'Ingreso al panel principal', '179.60.89.64'),
(360, 'Maria Pinto', 'Acceso al Home del Supervisor', '2026-11-07 13:33:55', 'Ingreso al panel principal', '186.189.95.75'),
(361, 'Maria Pinto', 'Acceso al Home del Supervisor', '2026-11-07 13:34:03', 'Ingreso al panel principal', '186.189.95.75'),
(362, 'Maria Pinto', 'Acceso al Home del Supervisor', '2026-11-07 13:35:23', 'Ingreso al panel principal', '186.189.95.75'),
(363, 'Jorge Valdivia Toro', 'Acceso al Home del Administrador', '2026-11-07 13:35:45', 'Ingreso al panel principal', '186.189.95.75'),
(364, 'Jorge Valdivia Toro', 'Acceso al Home del Administrador', '2026-11-07 13:44:04', 'Ingreso al panel principal', '179.60.89.64'),
(365, 'Jorge Valdivia Toro', 'Acceso al Home del Administrador', '2026-11-07 14:11:49', 'Ingreso al panel principal', '179.60.89.64'),
(366, 'Maria Pinto', 'Acceso al Home del Supervisor', '2026-11-07 15:14:53', 'Ingreso al panel principal', '186.189.103.222'),
(367, 'Maria Pinto', 'Acceso al Home del Supervisor', '2026-11-07 15:16:55', 'Ingreso al panel principal', '186.189.103.222'),
(368, 'Jorge Valdivia Toro', 'Acceso al Home del Administrador', '2026-11-07 15:23:15', 'Ingreso al panel principal', '186.189.95.75'),
(369, 'Jorge Valdivia Toro', 'Acceso al Home del Administrador', '2026-11-07 15:31:26', 'Ingreso al panel principal', '186.189.95.75'),
(370, 'Jorge Valdivia Toro', 'Acceso al Home del Administrador', '2026-11-07 15:31:28', 'Ingreso al panel principal', '186.189.95.75'),
(371, 'Jorge Valdivia Toro', 'Acceso al Home del Administrador', '2026-11-07 15:31:29', 'Ingreso al panel principal', '186.189.95.75'),
(372, 'Jorge Valdivia Toro', 'Acceso al Home del Administrador', '2026-11-07 15:31:30', 'Ingreso al panel principal', '186.189.95.75'),
(373, 'Jorge Valdivia Toro', 'Acceso al Home del Administrador', '2026-11-07 15:31:37', 'Ingreso al panel principal', '186.189.95.75'),
(374, 'Jorge Valdivia Toro', 'Acceso al Home del Administrador', '2026-11-07 15:32:26', 'Ingreso al panel principal', '186.189.95.75'),
(375, 'Jorge Valdivia Toro', 'Acceso al Home del Administrador', '2026-11-07 15:32:27', 'Ingreso al panel principal', '186.189.95.75'),
(376, 'Jorge Valdivia Toro', 'Acceso al Home del Administrador', '2026-11-07 15:32:28', 'Ingreso al panel principal', '186.189.95.75'),
(377, 'Jorge Valdivia Toro', 'Acceso al Home del Administrador', '2026-11-07 15:32:29', 'Ingreso al panel principal', '186.189.95.75');
INSERT INTO `logs_seguridad` (`id`, `usuario`, `accion`, `fecha`, `detalle`, `ip_origen`) VALUES
(378, 'Jorge Valdivia Toro', 'Acceso al Home del Administrador', '2026-11-07 15:32:37', 'Ingreso al panel principal', '186.189.95.75'),
(379, 'Jorge Valdivia Toro', 'Acceso al Home del Administrador', '2026-11-07 15:32:46', 'Ingreso al panel principal', '186.189.95.75'),
(380, 'Jorge Valdivia Toro', 'Acceso al Home del Administrador', '2026-11-07 15:33:05', 'Ingreso al panel principal', '186.189.95.75'),
(381, 'Jorge Valdivia Toro', 'Acceso al Home del Administrador', '2026-11-07 15:33:06', 'Ingreso al panel principal', '186.189.95.75'),
(382, 'Jorge Valdivia Toro', 'Acceso al Home del Administrador', '2026-11-07 15:33:07', 'Ingreso al panel principal', '186.189.95.75'),
(383, 'Jorge Valdivia Toro', 'Acceso al Home del Administrador', '2026-11-07 15:33:08', 'Ingreso al panel principal', '186.189.95.75'),
(384, 'Jorge Valdivia Toro', 'Acceso al Home del Administrador', '2026-11-07 15:33:16', 'Ingreso al panel principal', '186.189.95.75'),
(385, 'Jorge Valdivia Toro', 'Acceso al Home del Administrador', '2026-11-07 15:33:37', 'Ingreso al panel principal', '186.189.95.75'),
(386, 'Jorge Valdivia Toro', 'Acceso al Home del Administrador', '2026-11-07 15:34:07', 'Ingreso al panel principal', '186.189.95.75'),
(387, 'Jorge Valdivia Toro', 'Acceso al Home del Administrador', '2026-11-07 15:34:08', 'Ingreso al panel principal', '186.189.95.75'),
(388, 'Jorge Valdivia Toro', 'Acceso al Home del Administrador', '2026-11-07 15:34:09', 'Ingreso al panel principal', '186.189.95.75'),
(389, 'Jorge Valdivia Toro', 'Acceso al Home del Administrador', '2026-11-07 15:34:11', 'Ingreso al panel principal', '186.189.95.75'),
(390, 'Jorge Valdivia Toro', 'Acceso al Home del Administrador', '2026-11-07 15:34:18', 'Ingreso al panel principal', '186.189.95.75'),
(391, 'Jorge Valdivia Toro', 'Acceso al Home del Administrador', '2026-11-07 15:36:18', 'Ingreso al panel principal', '186.189.95.75'),
(392, 'Jorge Valdivia Toro', 'Acceso al Home del Administrador', '2026-11-07 15:36:19', 'Ingreso al panel principal', '186.189.95.75'),
(393, 'Jorge Valdivia Toro', 'Acceso al Home del Administrador', '2026-11-07 15:36:20', 'Ingreso al panel principal', '186.189.95.75'),
(394, 'Jorge Valdivia Toro', 'Acceso al Home del Administrador', '2026-11-07 15:36:21', 'Ingreso al panel principal', '186.189.95.75'),
(395, 'Jorge Valdivia Toro', 'Acceso al Home del Administrador', '2026-11-07 15:36:29', 'Ingreso al panel principal', '186.189.95.75'),
(396, 'Jorge Valdivia Toro', 'Acceso al Home del Administrador', '2026-11-07 15:36:35', 'Ingreso al panel principal', '186.189.95.75'),
(397, 'Jorge Valdivia Toro', 'Acceso al Home del Administrador', '2026-11-07 15:36:36', 'Ingreso al panel principal', '186.189.95.75'),
(398, 'Jorge Valdivia Toro', 'Acceso al Home del Administrador', '2026-11-07 15:36:37', 'Ingreso al panel principal', '186.189.95.75'),
(399, 'Jorge Valdivia Toro', 'Acceso al Home del Administrador', '2026-11-07 15:36:38', 'Ingreso al panel principal', '186.189.95.75'),
(400, 'Jorge Valdivia Toro', 'Acceso al Home del Administrador', '2026-11-07 15:36:45', 'Ingreso al panel principal', '186.189.95.75'),
(401, 'Jorge Valdivia Toro', 'Acceso al Home del Administrador', '2026-11-07 15:38:06', 'Ingreso al panel principal', '186.189.95.75'),
(402, 'Jorge Valdivia Toro', 'Acceso al Home del Administrador', '2026-11-07 15:38:07', 'Ingreso al panel principal', '186.189.95.75'),
(403, 'Jorge Valdivia Toro', 'Acceso al Home del Administrador', '2026-11-07 15:38:08', 'Ingreso al panel principal', '186.189.95.75'),
(404, 'Jorge Valdivia Toro', 'Acceso al Home del Administrador', '2026-11-07 15:38:09', 'Ingreso al panel principal', '186.189.95.75'),
(405, 'Jorge Valdivia Toro', 'Acceso al Home del Administrador', '2026-11-07 15:38:12', 'Ingreso al panel principal', '186.189.95.75'),
(406, 'Jorge Valdivia Toro', 'Acceso al Home del Administrador', '2026-11-07 15:40:06', 'Ingreso al panel principal', '186.189.95.75'),
(407, 'Jorge Valdivia Toro', 'Acceso al Home del Administrador', '2026-11-07 15:40:07', 'Ingreso al panel principal', '186.189.95.75'),
(408, 'Jorge Valdivia Toro', 'Acceso al Home del Administrador', '2026-11-07 15:40:08', 'Ingreso al panel principal', '186.189.95.75'),
(409, 'Jorge Valdivia Toro', 'Acceso al Home del Administrador', '2026-11-07 15:40:08', 'Ingreso al panel principal', '186.189.95.75'),
(410, 'Jorge Valdivia Toro', 'Acceso al Home del Administrador', '2026-11-07 15:40:09', 'Ingreso al panel principal', '186.189.95.75'),
(411, 'Jorge Valdivia Toro', 'Acceso al Home del Administrador', '2026-11-07 15:40:11', 'Ingreso al panel principal', '186.189.95.75'),
(412, 'Jorge Valdivia Toro', 'Acceso al Home del Administrador', '2026-11-07 15:40:12', 'Ingreso al panel principal', '186.189.95.75'),
(413, 'Jorge Valdivia Toro', 'Acceso al Home del Administrador', '2026-11-07 15:40:13', 'Ingreso al panel principal', '186.189.95.75'),
(414, 'Jorge Valdivia Toro', 'Acceso al Home del Administrador', '2026-11-07 15:40:15', 'Ingreso al panel principal', '186.189.95.75'),
(415, 'Jorge Valdivia Toro', 'Acceso al Home del Administrador', '2026-11-07 15:40:16', 'Ingreso al panel principal', '186.189.95.75'),
(416, 'Jorge Valdivia Toro', 'Acceso al Home del Administrador', '2026-11-07 15:40:17', 'Ingreso al panel principal', '186.189.95.75'),
(417, 'Jorge Valdivia Toro', 'Acceso al Home del Administrador', '2026-11-07 15:40:18', 'Ingreso al panel principal', '186.189.95.75'),
(418, 'Jorge Valdivia Toro', 'Acceso al Home del Administrador', '2026-11-07 15:40:19', 'Ingreso al panel principal', '186.189.95.75'),
(419, 'Jorge Valdivia Toro', 'Acceso al Home del Administrador', '2026-11-07 15:40:28', 'Ingreso al panel principal', '186.189.103.222'),
(420, 'Jorge Valdivia Toro', 'Acceso al Home del Administrador', '2026-11-07 15:40:34', 'Ingreso al panel principal', '186.189.95.75'),
(421, 'Jorge Valdivia Toro', 'Acceso al Home del Administrador', '2026-11-07 15:40:35', 'Ingreso al panel principal', '186.189.95.75'),
(422, 'Jorge Valdivia Toro', 'Acceso al Home del Administrador', '2026-11-07 15:40:36', 'Ingreso al panel principal', '186.189.95.75'),
(423, 'Jorge Valdivia Toro', 'Acceso al Home del Administrador', '2026-11-07 15:40:37', 'Ingreso al panel principal', '186.189.95.75'),
(424, 'Jorge Valdivia Toro', 'Acceso al Home del Administrador', '2026-11-07 15:40:37', 'Ingreso al panel principal', '186.189.95.75'),
(425, 'Jorge Valdivia Toro', 'Acceso al Home del Administrador', '2026-11-07 15:40:39', 'Ingreso al panel principal', '186.189.95.75'),
(426, 'Jorge Valdivia Toro', 'Acceso al Home del Administrador', '2026-11-07 15:40:40', 'Ingreso al panel principal', '186.189.95.75'),
(427, 'Jorge Valdivia Toro', 'Acceso al Home del Administrador', '2026-11-07 15:40:41', 'Ingreso al panel principal', '186.189.95.75'),
(428, 'Jorge Valdivia Toro', 'Acceso al Home del Administrador', '2026-11-07 15:40:42', 'Ingreso al panel principal', '186.189.95.75'),
(429, 'Jorge Valdivia Toro', 'Acceso al Home del Administrador', '2026-11-07 15:40:43', 'Ingreso al panel principal', '186.189.95.75'),
(430, 'Jorge Valdivia Toro', 'Acceso al Home del Administrador', '2026-11-07 15:40:45', 'Ingreso al panel principal', '186.189.95.75'),
(431, 'Jorge Valdivia Toro', 'Acceso al Home del Administrador', '2026-11-07 15:40:46', 'Ingreso al panel principal', '186.189.95.75'),
(432, 'Jorge Valdivia Toro', 'Acceso al Home del Administrador', '2026-11-07 15:40:47', 'Ingreso al panel principal', '186.189.95.75'),
(433, 'Jorge Valdivia Toro', 'Acceso al Home del Administrador', '2026-11-07 15:40:50', 'Ingreso al panel principal', '179.60.92.241'),
(434, 'Jorge Valdivia Toro', 'Acceso al Home del Administrador', '2026-11-07 15:41:24', 'Ingreso al panel principal', '186.189.95.75'),
(435, 'Jorge Valdivia Toro', 'Acceso al Home del Administrador', '2026-11-07 15:41:42', 'Ingreso al panel principal', '186.189.95.75'),
(436, 'Jorge Valdivia Toro', 'Acceso al Home del Administrador', '2026-11-07 15:41:59', 'Ingreso al panel principal', '186.189.95.75'),
(437, 'Jorge Valdivia Toro', 'Acceso al Home del Administrador', '2026-11-07 15:42:02', 'Ingreso al panel principal', '186.189.95.75'),
(438, 'Jorge Valdivia Toro', 'Acceso al Home del Administrador', '2026-11-07 15:42:04', 'Ingreso al panel principal', '186.189.95.75'),
(439, 'Jorge Valdivia Toro', 'Acceso al Home del Administrador', '2026-11-07 15:42:07', 'Ingreso al panel principal', '186.189.95.75'),
(440, 'Jorge Valdivia Toro', 'Acceso al Home del Administrador', '2026-11-07 15:43:37', 'Ingreso al panel principal', '186.189.95.75'),
(441, 'Jorge Valdivia Toro', 'Acceso al Home del Administrador', '2026-11-07 15:43:37', 'Ingreso al panel principal', '186.189.95.75'),
(442, 'Jorge Valdivia Toro', 'Acceso al Home del Administrador', '2026-11-07 15:43:38', 'Ingreso al panel principal', '186.189.95.75'),
(443, 'Jorge Valdivia Toro', 'Acceso al Home del Administrador', '2026-11-07 15:43:39', 'Ingreso al panel principal', '186.189.95.75'),
(444, 'Jorge Valdivia Toro', 'Acceso al Home del Administrador', '2026-11-07 15:43:40', 'Ingreso al panel principal', '186.189.95.75'),
(445, 'Jorge Valdivia Toro', 'Acceso al Home del Administrador', '2026-11-07 15:43:41', 'Ingreso al panel principal', '186.189.95.75'),
(446, 'Jorge Valdivia Toro', 'Acceso al Home del Administrador', '2026-11-07 15:43:42', 'Ingreso al panel principal', '186.189.95.75'),
(447, 'Jorge Valdivia Toro', 'Acceso al Home del Administrador', '2026-11-07 15:43:43', 'Ingreso al panel principal', '186.189.95.75'),
(448, 'Jorge Valdivia Toro', 'Acceso al Home del Administrador', '2026-11-07 15:43:45', 'Ingreso al panel principal', '186.189.95.75'),
(449, 'Jorge Valdivia Toro', 'Acceso al Home del Administrador', '2026-11-07 15:43:46', 'Ingreso al panel principal', '186.189.95.75'),
(450, 'Jorge Valdivia Toro', 'Acceso al Home del Administrador', '2026-11-07 15:43:47', 'Ingreso al panel principal', '186.189.95.75'),
(451, 'Jorge Valdivia Toro', 'Acceso al Home del Administrador', '2026-11-07 15:43:47', 'Ingreso al panel principal', '179.60.92.241'),
(452, 'Jorge Valdivia Toro', 'Acceso al Home del Administrador', '2026-11-07 15:43:48', 'Ingreso al panel principal', '186.189.95.75'),
(453, 'Jorge Valdivia Toro', 'Acceso al Home del Administrador', '2026-11-07 15:43:50', 'Ingreso al panel principal', '186.189.95.75'),
(454, 'Jorge Valdivia Toro', 'Acceso al Home del Administrador', '2026-11-07 15:43:51', 'Ingreso al panel principal', '186.189.95.75'),
(455, 'Jorge Valdivia Toro', 'Acceso al Home del Administrador', '2026-11-07 15:43:53', 'Ingreso al panel principal', '186.189.95.75'),
(456, 'Jorge Valdivia Toro', 'Acceso al Home del Administrador', '2026-11-07 15:43:56', 'Ingreso al panel principal', '186.189.95.75'),
(457, 'Jorge Valdivia Toro', 'Acceso al Home del Administrador', '2026-11-07 15:43:58', 'Ingreso al panel principal', '186.189.95.75'),
(458, 'Jorge Valdivia Toro', 'Acceso al Home del Administrador', '2026-11-07 15:44:00', 'Ingreso al panel principal', '186.189.95.75'),
(459, 'Jorge Valdivia Toro', 'Acceso al Home del Administrador', '2026-11-07 15:44:01', 'Ingreso al panel principal', '186.189.95.75'),
(460, 'Jorge Valdivia Toro', 'Acceso al Home del Administrador', '2026-11-07 15:44:02', 'Ingreso al panel principal', '186.189.95.75'),
(461, 'Jorge Valdivia Toro', 'Acceso al Home del Administrador', '2026-11-07 15:44:41', 'Ingreso al panel principal', '179.60.92.241'),
(462, 'Jorge Valdivia Toro', 'Acceso al Home del Administrador', '2026-11-07 15:45:08', 'Ingreso al panel principal', '186.189.95.75'),
(463, 'Jorge Valdivia Toro', 'Acceso al Home del Administrador', '2026-11-07 15:45:09', 'Ingreso al panel principal', '186.189.95.75'),
(464, 'Jorge Valdivia Toro', 'Acceso al Home del Administrador', '2026-11-07 15:45:11', 'Ingreso al panel principal', '186.189.95.75'),
(465, 'Jorge Valdivia Toro', 'Acceso al Home del Administrador', '2026-11-07 15:45:12', 'Ingreso al panel principal', '186.189.95.75'),
(466, 'Jorge Valdivia Toro', 'Acceso al Home del Administrador', '2026-11-07 15:45:24', 'Ingreso al panel principal', '186.189.95.75'),
(467, 'Jorge Valdivia Toro', 'Acceso al Home del Administrador', '2026-11-07 15:45:39', 'Ingreso al panel principal', '186.189.95.75'),
(468, 'Jorge Valdivia Toro', 'Acceso al Home del Administrador', '2026-11-07 15:47:25', 'Ingreso al panel principal', '186.189.95.75'),
(469, 'Jorge Valdivia Toro', 'Acceso al Home del Administrador', '2026-11-07 15:47:27', 'Ingreso al panel principal', '186.189.95.75'),
(470, 'Jorge Valdivia Toro', 'Acceso al Home del Administrador', '2026-11-07 15:47:29', 'Ingreso al panel principal', '186.189.95.75'),
(471, 'Jorge Valdivia Toro', 'Acceso al Home del Administrador', '2026-11-07 15:47:30', 'Ingreso al panel principal', '186.189.95.75'),
(472, 'Jorge Valdivia Toro', 'Acceso al Home del Administrador', '2026-11-07 15:47:30', 'Ingreso al panel principal', '186.189.95.75'),
(473, 'Jorge Valdivia Toro', 'Acceso al Home del Administrador', '2026-11-07 15:47:31', 'Ingreso al panel principal', '186.189.95.75'),
(474, 'Jorge Valdivia Toro', 'Acceso al Home del Administrador', '2026-11-07 15:47:32', 'Ingreso al panel principal', '186.189.95.75'),
(475, 'Jorge Valdivia Toro', 'Acceso al Home del Administrador', '2026-11-07 15:47:33', 'Ingreso al panel principal', '186.189.95.75'),
(476, 'Jorge Valdivia Toro', 'Acceso al Home del Administrador', '2026-11-07 15:48:21', 'Ingreso al panel principal', '186.189.95.75'),
(477, 'Jorge Valdivia Toro', 'Acceso al Home del Administrador', '2026-11-07 15:48:22', 'Ingreso al panel principal', '186.189.95.75'),
(478, 'Jorge Valdivia Toro', 'Acceso al Home del Administrador', '2026-11-07 15:48:24', 'Ingreso al panel principal', '186.189.95.75'),
(479, 'Jorge Valdivia Toro', 'Acceso al Home del Administrador', '2026-11-07 15:48:24', 'Ingreso al panel principal', '186.189.95.75'),
(480, 'Jorge Valdivia Toro', 'Acceso al Home del Administrador', '2026-11-07 15:48:25', 'Ingreso al panel principal', '186.189.95.75'),
(481, 'Jorge Valdivia Toro', 'Acceso al Home del Administrador', '2026-11-07 15:48:26', 'Ingreso al panel principal', '186.189.95.75'),
(482, 'Jorge Valdivia Toro', 'Acceso al Home del Administrador', '2026-11-07 15:48:27', 'Ingreso al panel principal', '186.189.95.75'),
(483, 'Jorge Valdivia Toro', 'Acceso al Home del Administrador', '2026-11-07 15:48:28', 'Ingreso al panel principal', '186.189.95.75'),
(484, 'Jorge Valdivia Toro', 'Acceso al Home del Administrador', '2026-11-07 15:48:29', 'Ingreso al panel principal', '186.189.95.75'),
(485, 'Jorge Valdivia Toro', 'Acceso al Home del Administrador', '2026-11-07 15:48:31', 'Ingreso al panel principal', '186.189.95.75'),
(486, 'Jorge Valdivia Toro', 'Acceso al Home del Administrador', '2026-11-07 15:48:32', 'Ingreso al panel principal', '186.189.95.75'),
(487, 'Jorge Valdivia Toro', 'Acceso al Home del Administrador', '2026-11-07 15:48:33', 'Ingreso al panel principal', '186.189.95.75'),
(488, 'Jorge Valdivia Toro', 'Acceso al Home del Administrador', '2026-11-07 15:48:34', 'Ingreso al panel principal', '186.189.95.75'),
(489, 'Jorge Valdivia Toro', 'Acceso al Home del Administrador', '2026-11-07 15:49:42', 'Ingreso al panel principal', '186.189.95.75'),
(490, 'Jorge Valdivia Toro', 'Acceso al Home del Administrador', '2026-11-07 15:49:43', 'Ingreso al panel principal', '186.189.95.75'),
(491, 'Jorge Valdivia Toro', 'Acceso al Home del Administrador', '2026-11-07 15:49:44', 'Ingreso al panel principal', '186.189.95.75'),
(492, 'Jorge Valdivia Toro', 'Acceso al Home del Administrador', '2026-11-07 15:49:45', 'Ingreso al panel principal', '186.189.95.75'),
(493, 'Jorge Valdivia Toro', 'Acceso al Home del Administrador', '2026-11-07 15:49:51', 'Ingreso al panel principal', '186.189.95.75'),
(494, 'Jorge Valdivia Toro', 'Acceso al Home del Administrador', '2026-11-07 15:52:05', 'Ingreso al panel principal', '186.189.95.75'),
(495, 'Jorge Valdivia Toro', 'Acceso al Home del Administrador', '2026-11-07 15:52:06', 'Ingreso al panel principal', '186.189.95.75'),
(496, 'Jorge Valdivia Toro', 'Acceso al Home del Administrador', '2026-11-07 15:52:07', 'Ingreso al panel principal', '186.189.95.75'),
(497, 'Jorge Valdivia Toro', 'Acceso al Home del Administrador', '2026-11-07 15:52:08', 'Ingreso al panel principal', '186.189.95.75'),
(498, 'Jorge Valdivia Toro', 'Acceso al Home del Administrador', '2026-11-07 15:52:11', 'Ingreso al panel principal', '186.189.95.75'),
(499, 'Jorge Valdivia Toro', 'Acceso al Home del Administrador', '2026-11-07 15:56:22', 'Ingreso al panel principal', '186.189.95.75'),
(500, 'Jorge Valdivia Toro', 'Acceso al Home del Administrador', '2026-11-07 15:56:23', 'Ingreso al panel principal', '186.189.95.75'),
(501, 'Jorge Valdivia Toro', 'Acceso al Home del Administrador', '2026-11-07 15:56:24', 'Ingreso al panel principal', '186.189.95.75'),
(502, 'Jorge Valdivia Toro', 'Acceso al Home del Administrador', '2026-11-07 15:56:25', 'Ingreso al panel principal', '186.189.95.75'),
(503, 'Jorge Valdivia Toro', 'Acceso al Home del Administrador', '2026-11-07 15:56:27', 'Ingreso al panel principal', '186.189.95.75'),
(504, 'Jorge Valdivia Toro', 'Acceso al Home del Administrador', '2026-11-07 15:57:12', 'Ingreso al panel principal', '186.189.95.75'),
(505, 'Jorge Valdivia Toro', 'Acceso al Home del Administrador', '2026-11-07 15:57:15', 'Ingreso al panel principal', '186.189.95.75'),
(506, 'Jorge Valdivia Toro', 'Acceso al Home del Administrador', '2026-11-07 15:57:16', 'Ingreso al panel principal', '186.189.95.75'),
(507, 'Jorge Valdivia Toro', 'Acceso al Home del Administrador', '2026-11-07 15:57:16', 'Ingreso al panel principal', '186.189.95.75'),
(508, 'Jorge Valdivia Toro', 'Acceso al Home del Administrador', '2026-11-07 15:57:19', 'Ingreso al panel principal', '186.189.95.75'),
(509, 'Jorge Valdivia Toro', 'Acceso al Home del Administrador', '2026-11-07 16:00:32', 'Ingreso al panel principal', '186.189.103.222'),
(510, 'Jorge Valdivia Toro', 'Acceso al Home del Administrador', '2026-11-07 16:00:42', 'Ingreso al panel principal', '186.189.103.222'),
(511, 'Jorge Valdivia Toro', 'Acceso al Home del Administrador', '2026-11-07 16:02:49', 'Ingreso al panel principal', '186.189.103.222'),
(512, 'Jorge Valdivia Toro', 'Acceso al Home del Administrador', '2026-11-07 16:11:06', 'Ingreso al panel principal', '186.189.103.222'),
(513, 'Maria Pinto', 'Acceso al Home del Supervisor', '2026-11-07 16:11:32', 'Ingreso al panel principal', '186.189.103.222'),
(514, 'Jorge Valdivia Toro', 'Acceso al Home del Administrador', '2026-11-07 16:19:56', 'Ingreso al panel principal', '186.189.103.222'),
(515, 'Jorge Valdivia Toro', 'Acceso al Home del Administrador', '2026-11-07 16:20:42', 'Ingreso al panel principal', '186.189.103.222'),
(516, 'Jorge Valdivia Toro', 'Acceso al Home del Administrador', '2026-11-07 16:25:03', 'Ingreso al panel principal', '186.189.103.222'),
(517, 'Jorge Valdivia Toro', 'Acceso al Home del Administrador', '2026-11-07 16:39:48', 'Ingreso al panel principal', '186.189.95.75'),
(518, 'Jorge Valdivia Toro', 'Acceso al Home del Administrador', '2026-11-07 16:39:49', 'Ingreso al panel principal', '186.189.95.75'),
(519, 'Jorge Valdivia Toro', 'Acceso al Home del Administrador', '2026-11-07 16:39:50', 'Ingreso al panel principal', '186.189.95.75'),
(520, 'Jorge Valdivia Toro', 'Acceso al Home del Administrador', '2026-11-07 16:39:51', 'Ingreso al panel principal', '186.189.95.75'),
(521, 'Jorge Valdivia Toro', 'Acceso al Home del Administrador', '2026-11-07 16:39:53', 'Ingreso al panel principal', '186.189.95.75'),
(522, 'Jorge Valdivia Toro', 'Acceso al Home del Administrador', '2026-11-07 16:42:30', 'Ingreso al panel principal', '186.189.95.75'),
(523, 'Jorge Valdivia Toro', 'Acceso al Home del Administrador', '2026-11-07 16:46:11', 'Ingreso al panel principal', '186.189.95.75'),
(524, 'Jorge Valdivia Toro', 'Acceso al Home del Administrador', '2026-11-07 16:46:13', 'Ingreso al panel principal', '186.189.95.75'),
(525, 'Jorge Valdivia Toro', 'Acceso al Home del Administrador', '2026-11-07 16:46:13', 'Ingreso al panel principal', '186.189.95.75'),
(526, 'Jorge Valdivia Toro', 'Acceso al Home del Administrador', '2026-11-07 16:46:14', 'Ingreso al panel principal', '186.189.95.75'),
(527, 'Jorge Valdivia Toro', 'Acceso al Home del Administrador', '2026-11-07 16:46:21', 'Ingreso al panel principal', '186.189.95.75'),
(528, 'Jorge Valdivia Toro', 'Acceso al Home del Administrador', '2026-11-07 16:46:22', 'Ingreso al panel principal', '186.189.95.75'),
(529, 'Jorge Valdivia Toro', 'Acceso al Home del Administrador', '2026-11-07 16:46:23', 'Ingreso al panel principal', '186.189.95.75'),
(530, 'Jorge Valdivia Toro', 'Acceso al Home del Administrador', '2026-11-07 16:46:23', 'Ingreso al panel principal', '186.189.95.75'),
(531, 'Jorge Valdivia Toro', 'Acceso al Home del Administrador', '2026-11-07 16:46:29', 'Ingreso al panel principal', '186.189.95.75'),
(532, 'Jorge Valdivia Toro', 'Acceso al Home del Administrador', '2026-11-07 16:47:56', 'Ingreso al panel principal', '186.189.95.75'),
(533, 'Jorge Valdivia Toro', 'Acceso al Home del Administrador', '2026-11-07 16:47:57', 'Ingreso al panel principal', '186.189.95.75'),
(534, 'Jorge Valdivia Toro', 'Acceso al Home del Administrador', '2026-11-07 16:47:58', 'Ingreso al panel principal', '186.189.95.75'),
(535, 'Jorge Valdivia Toro', 'Acceso al Home del Administrador', '2026-11-07 16:47:59', 'Ingreso al panel principal', '186.189.95.75'),
(536, 'Jorge Valdivia Toro', 'Acceso al Home del Administrador', '2026-11-07 16:48:00', 'Ingreso al panel principal', '186.189.95.75'),
(537, 'Jorge Valdivia Toro', 'Acceso al Home del Administrador', '2026-11-07 16:51:32', 'Ingreso al panel principal', '186.189.95.75'),
(538, 'Jorge Valdivia Toro', 'Acceso al Home del Administrador', '2026-11-07 16:51:34', 'Ingreso al panel principal', '186.189.95.75'),
(539, 'Jorge Valdivia Toro', 'Acceso al Home del Administrador', '2026-11-07 16:51:35', 'Ingreso al panel principal', '186.189.95.75'),
(540, 'Jorge Valdivia Toro', 'Acceso al Home del Administrador', '2026-11-07 16:51:41', 'Ingreso al panel principal', '186.189.95.75'),
(541, 'Jorge Valdivia Toro', 'Acceso al Home del Administrador', '2026-11-07 16:51:43', 'Ingreso al panel principal', '186.189.95.75'),
(542, 'Jorge Valdivia Toro', 'Acceso al Home del Administrador', '2026-11-07 16:52:45', 'Ingreso al panel principal', '186.189.95.75'),
(543, 'Jorge Valdivia Toro', 'Acceso al Home del Administrador', '2026-11-07 16:52:46', 'Ingreso al panel principal', '186.189.95.75'),
(544, 'Jorge Valdivia Toro', 'Acceso al Home del Administrador', '2026-11-07 16:52:47', 'Ingreso al panel principal', '186.189.95.75'),
(545, 'Jorge Valdivia Toro', 'Acceso al Home del Administrador', '2026-11-07 16:52:53', 'Ingreso al panel principal', '186.189.95.75'),
(546, 'Jorge Valdivia Toro', 'Acceso al Home del Administrador', '2026-11-07 16:52:55', 'Ingreso al panel principal', '186.189.95.75'),
(547, 'Jorge Valdivia Toro', 'Acceso al Home del Administrador', '2026-11-07 16:53:10', 'Ingreso al panel principal', '186.189.103.222'),
(548, 'Jorge Valdivia Toro', 'Acceso al Home del Administrador', '2026-11-07 16:53:14', 'Ingreso al panel principal', '186.189.103.222'),
(549, 'Jorge Valdivia Toro', 'Acceso al Home del Administrador', '2026-11-07 16:53:16', 'Ingreso al panel principal', '186.189.103.222'),
(550, 'Jorge Valdivia Toro', 'Acceso al Home del Administrador', '2026-11-07 16:55:24', 'Ingreso al panel principal', '186.189.95.75'),
(551, 'Jorge Valdivia Toro', 'Acceso al Home del Administrador', '2026-11-07 16:55:25', 'Ingreso al panel principal', '186.189.95.75'),
(552, 'Jorge Valdivia Toro', 'Acceso al Home del Administrador', '2026-11-07 16:55:26', 'Ingreso al panel principal', '186.189.95.75'),
(553, 'Jorge Valdivia Toro', 'Acceso al Home del Administrador', '2026-11-07 16:55:26', 'Ingreso al panel principal', '186.189.95.75'),
(554, 'Jorge Valdivia Toro', 'Acceso al Home del Administrador', '2026-11-07 16:55:32', 'Ingreso al panel principal', '186.189.95.75'),
(555, 'Jorge Valdivia Toro', 'Acceso al Home del Administrador', '2026-11-07 16:55:34', 'Ingreso al panel principal', '186.189.95.75'),
(556, 'Jorge Valdivia Toro', 'Acceso al Home del Administrador', '2026-11-07 16:58:00', 'Ingreso al panel principal', '186.189.95.75'),
(557, 'Jorge Valdivia Toro', 'Acceso al Home del Administrador', '2026-11-07 16:58:01', 'Ingreso al panel principal', '186.189.95.75'),
(558, 'Jorge Valdivia Toro', 'Acceso al Home del Administrador', '2026-11-07 16:58:02', 'Ingreso al panel principal', '186.189.95.75'),
(559, 'Jorge Valdivia Toro', 'Acceso al Home del Administrador', '2026-11-07 16:58:02', 'Ingreso al panel principal', '186.189.95.75'),
(560, 'Jorge Valdivia Toro', 'Acceso al Home del Administrador', '2026-11-07 16:58:03', 'Ingreso al panel principal', '186.189.95.75'),
(561, 'Jorge Valdivia Toro', 'Acceso al Home del Administrador', '2026-11-07 16:58:08', 'Ingreso al panel principal', '186.189.95.75'),
(562, 'Jorge Valdivia Toro', 'Acceso al Home del Administrador', '2026-11-07 16:58:10', 'Ingreso al panel principal', '186.189.95.75'),
(563, 'Jorge Valdivia Toro', 'Acceso al Home del Administrador', '2026-11-07 16:59:11', 'Ingreso al panel principal', '186.189.95.75'),
(564, 'Jorge Valdivia Toro', 'Acceso al Home del Administrador', '2026-11-07 16:59:55', 'Ingreso al panel principal', '186.189.95.75'),
(565, 'Jorge Valdivia Toro', 'Acceso al Home del Administrador', '2026-11-07 16:59:57', 'Ingreso al panel principal', '186.189.95.75'),
(566, 'Jorge Valdivia Toro', 'Acceso al Home del Administrador', '2026-11-07 16:59:58', 'Ingreso al panel principal', '186.189.95.75'),
(567, 'Jorge Valdivia Toro', 'Acceso al Home del Administrador', '2026-11-07 16:59:58', 'Ingreso al panel principal', '186.189.95.75'),
(568, 'Jorge Valdivia Toro', 'Acceso al Home del Administrador', '2026-11-07 16:59:59', 'Ingreso al panel principal', '186.189.95.75'),
(569, 'Jorge Valdivia Toro', 'Acceso al Home del Administrador', '2026-11-07 16:59:59', 'Ingreso al panel principal', '186.189.95.75'),
(570, 'Jorge Valdivia Toro', 'Acceso al Home del Administrador', '2026-11-07 16:59:59', 'Ingreso al panel principal', '186.189.95.75'),
(571, 'Jorge Valdivia Toro', 'Acceso al Home del Administrador', '2026-11-07 17:00:04', 'Ingreso al panel principal', '186.189.95.75'),
(572, 'Jorge Valdivia Toro', 'Acceso al Home del Administrador', '2026-11-07 17:00:06', 'Ingreso al panel principal', '186.189.95.75'),
(573, 'Jorge Valdivia Toro', 'Acceso al Home del Administrador', '2026-11-07 17:00:21', 'Ingreso al panel principal', '186.189.95.75'),
(574, 'Jorge Valdivia Toro', 'Acceso al Home del Administrador', '2026-11-07 17:00:22', 'Ingreso al panel principal', '186.189.95.75'),
(575, 'Jorge Valdivia Toro', 'Acceso al Home del Administrador', '2026-11-07 17:00:24', 'Ingreso al panel principal', '186.189.95.75'),
(576, 'Jorge Valdivia Toro', 'Acceso al Home del Administrador', '2026-11-07 17:00:24', 'Ingreso al panel principal', '186.189.95.75'),
(577, 'Jorge Valdivia Toro', 'Acceso al Home del Administrador', '2026-11-07 17:00:24', 'Ingreso al panel principal', '186.189.95.75'),
(578, 'Jorge Valdivia Toro', 'Acceso al Home del Administrador', '2026-11-07 17:00:25', 'Ingreso al panel principal', '186.189.95.75'),
(579, 'Jorge Valdivia Toro', 'Acceso al Home del Administrador', '2026-11-07 17:00:25', 'Ingreso al panel principal', '186.189.95.75'),
(580, 'Jorge Valdivia Toro', 'Acceso al Home del Administrador', '2026-11-07 17:00:27', 'Ingreso al panel principal', '186.189.95.75'),
(581, 'Jorge Valdivia Toro', 'Acceso al Home del Administrador', '2026-11-07 17:00:30', 'Ingreso al panel principal', '186.189.95.75'),
(582, 'Jorge Valdivia Toro', 'Acceso al Home del Administrador', '2026-11-07 17:00:33', 'Ingreso al panel principal', '186.189.95.75'),
(583, 'Jorge Valdivia Toro', 'Acceso al Home del Administrador', '2026-11-07 17:00:40', 'Ingreso al panel principal', '186.189.95.75'),
(584, 'Jorge Valdivia Toro', 'Acceso al Home del Administrador', '2026-11-07 17:00:41', 'Ingreso al panel principal', '186.189.95.75'),
(585, 'Jorge Valdivia Toro', 'Acceso al Home del Administrador', '2026-11-07 17:00:43', 'Ingreso al panel principal', '186.189.95.75'),
(586, 'Jorge Valdivia Toro', 'Acceso al Home del Administrador', '2026-11-07 17:00:43', 'Ingreso al panel principal', '186.189.95.75'),
(587, 'Jorge Valdivia Toro', 'Acceso al Home del Administrador', '2026-11-07 17:00:43', 'Ingreso al panel principal', '186.189.95.75'),
(588, 'Jorge Valdivia Toro', 'Acceso al Home del Administrador', '2026-11-07 17:00:44', 'Ingreso al panel principal', '186.189.95.75'),
(589, 'Jorge Valdivia Toro', 'Acceso al Home del Administrador', '2026-11-07 17:00:44', 'Ingreso al panel principal', '186.189.95.75'),
(590, 'Jorge Valdivia Toro', 'Acceso al Home del Administrador', '2026-11-07 17:00:45', 'Ingreso al panel principal', '186.189.95.75'),
(591, 'Jorge Valdivia Toro', 'Acceso al Home del Administrador', '2026-11-07 17:00:46', 'Ingreso al panel principal', '186.189.95.75'),
(592, 'Maria Pinto', 'Acceso al Home del Supervisor', '2026-11-07 17:01:26', 'Ingreso al panel principal', '179.60.92.241'),
(593, 'Maria Pinto', 'Acceso al Home del Supervisor', '2026-11-07 17:03:03', 'Ingreso al panel principal', '179.60.92.241'),
(594, 'Jorge Valdivia Toro', 'Acceso al Home del Administrador', '2026-11-07 17:04:10', 'Ingreso al panel principal', '179.60.92.241'),
(595, 'Maria Pinto', 'Acceso al Home del Supervisor', '2026-11-07 17:12:13', 'Ingreso al panel principal', '179.60.92.241'),
(596, 'Jorge Valdivia Toro', 'Acceso al Home del Administrador', '2026-11-07 17:14:50', 'Ingreso al panel principal', '179.60.92.241'),
(597, 'Maria Pinto', 'Acceso al Home del Supervisor', '2026-11-07 17:18:47', 'Ingreso al panel principal', '179.60.92.241'),
(598, 'Jorge Valdivia Toro', 'Acceso al Home del Administrador', '2026-11-07 17:20:25', 'Ingreso al panel principal', '179.60.92.241'),
(599, 'Maria Pinto', 'Acceso al Home del Supervisor', '2026-11-07 17:51:09', 'Ingreso al panel principal', '186.189.103.222'),
(600, 'Maria Pinto', 'Acceso al Home del Supervisor', '2026-11-07 17:53:06', 'Ingreso al panel principal', '186.189.103.222'),
(601, 'Jorge Valdivia Toro', 'Acceso al Home del Administrador', '2026-11-07 18:11:41', 'Ingreso al panel principal', '186.189.95.75'),
(602, 'Maria Pinto', 'Acceso al Home del Supervisor', '2026-11-07 18:19:45', 'Ingreso al panel principal', '186.189.103.222'),
(603, 'Maria Pinto', 'Acceso al Home del Supervisor', '2026-11-07 18:26:49', 'Ingreso al panel principal', '186.189.103.222'),
(604, 'Jorge Valdivia Toro', 'Acceso al Home del Administrador', '2026-11-07 18:28:18', 'Ingreso al panel principal', '186.189.95.75'),
(605, 'Jorge Valdivia Toro', 'Acceso al Home del Administrador', '2026-11-07 18:38:19', 'Ingreso al panel principal', '186.189.95.75'),
(606, 'Jorge Valdivia Toro', 'Acceso al Home del Administrador', '2026-11-07 18:38:21', 'Ingreso al panel principal', '186.189.95.75'),
(607, 'Jorge Valdivia Toro', 'Acceso al Home del Administrador', '2026-11-07 18:38:22', 'Ingreso al panel principal', '186.189.95.75'),
(608, 'Jorge Valdivia Toro', 'Acceso al Home del Administrador', '2026-11-07 18:38:22', 'Ingreso al panel principal', '186.189.95.75'),
(609, 'Jorge Valdivia Toro', 'Acceso al Home del Administrador', '2026-11-07 18:38:23', 'Ingreso al panel principal', '186.189.95.75'),
(610, 'Jorge Valdivia Toro', 'Acceso al Home del Administrador', '2026-11-07 18:38:25', 'Ingreso al panel principal', '186.189.95.75'),
(611, 'Jorge Valdivia Toro', 'Acceso al Home del Administrador', '2026-11-07 18:38:35', 'Ingreso al panel principal', '186.189.95.75'),
(612, 'Jorge Valdivia Toro', 'Acceso al Home del Administrador', '2026-11-07 18:38:36', 'Ingreso al panel principal', '186.189.95.75'),
(613, 'Jorge Valdivia Toro', 'Acceso al Home del Administrador', '2026-11-07 18:38:36', 'Ingreso al panel principal', '186.189.95.75'),
(614, 'Jorge Valdivia Toro', 'Acceso al Home del Administrador', '2026-11-07 18:38:36', 'Ingreso al panel principal', '186.189.95.75'),
(615, 'Jorge Valdivia Toro', 'Acceso al Home del Administrador', '2026-11-07 18:38:37', 'Ingreso al panel principal', '186.189.95.75'),
(616, 'Jorge Valdivia Toro', 'Acceso al Home del Administrador', '2026-11-07 18:38:47', 'Ingreso al panel principal', '186.189.95.75'),
(617, 'Jorge Valdivia Toro', 'Acceso al Home del Administrador', '2026-11-07 18:38:50', 'Ingreso al panel principal', '186.189.95.75'),
(618, 'Jorge Valdivia Toro', 'Acceso al Home del Administrador', '2026-11-07 18:38:51', 'Ingreso al panel principal', '186.189.95.75'),
(619, 'Jorge Valdivia Toro', 'Acceso al Home del Administrador', '2026-11-07 18:38:52', 'Ingreso al panel principal', '186.189.95.75'),
(620, 'Jorge Valdivia Toro', 'Acceso al Home del Administrador', '2026-11-07 18:38:52', 'Ingreso al panel principal', '186.189.95.75'),
(621, 'Jorge Valdivia Toro', 'Acceso al Home del Administrador', '2026-11-07 18:38:53', 'Ingreso al panel principal', '186.189.95.75'),
(622, 'Jorge Valdivia Toro', 'Acceso al Home del Administrador', '2026-11-07 18:38:54', 'Ingreso al panel principal', '186.189.95.75'),
(623, 'Jorge Valdivia Toro', 'Acceso al Home del Administrador', '2026-11-07 18:39:46', 'Ingreso al panel principal', '186.189.95.75'),
(624, 'Jorge Valdivia Toro', 'Acceso al Home del Administrador', '2026-11-07 18:49:21', 'Ingreso al panel principal', '186.189.95.75'),
(625, 'Jorge Valdivia Toro', 'Acceso al Home del Administrador', '2026-11-07 18:49:22', 'Ingreso al panel principal', '186.189.95.75'),
(626, 'Jorge Valdivia Toro', 'Acceso al Home del Administrador', '2026-11-07 18:49:23', 'Ingreso al panel principal', '186.189.95.75'),
(627, 'Jorge Valdivia Toro', 'Acceso al Home del Administrador', '2026-11-07 18:49:23', 'Ingreso al panel principal', '186.189.95.75'),
(628, 'Jorge Valdivia Toro', 'Acceso al Home del Administrador', '2026-11-07 18:49:24', 'Ingreso al panel principal', '186.189.95.75'),
(629, 'Jorge Valdivia Toro', 'Acceso al Home del Administrador', '2026-11-07 18:49:24', 'Ingreso al panel principal', '186.189.95.75'),
(630, 'Jorge Valdivia Toro', 'Acceso al Home del Administrador', '2026-11-07 18:49:24', 'Ingreso al panel principal', '186.189.95.75'),
(631, 'Jorge Valdivia Toro', 'Acceso al Home del Administrador', '2026-11-07 18:49:25', 'Ingreso al panel principal', '186.189.95.75'),
(632, 'Jorge Valdivia Toro', 'Acceso al Home del Administrador', '2026-11-07 18:49:27', 'Ingreso al panel principal', '186.189.95.75'),
(633, 'Jorge Valdivia Toro', 'Acceso al Home del Administrador', '2026-11-07 18:58:00', 'Ingreso al panel principal', '186.189.95.75'),
(634, 'Jorge Valdivia Toro', 'Acceso al Home del Administrador', '2026-11-07 18:58:01', 'Ingreso al panel principal', '186.189.95.75'),
(635, 'Jorge Valdivia Toro', 'Acceso al Home del Administrador', '2026-11-07 18:58:02', 'Ingreso al panel principal', '186.189.95.75'),
(636, 'Jorge Valdivia Toro', 'Acceso al Home del Administrador', '2026-11-07 18:58:03', 'Ingreso al panel principal', '186.189.95.75'),
(637, 'Jorge Valdivia Toro', 'Acceso al Home del Administrador', '2026-11-07 18:58:03', 'Ingreso al panel principal', '186.189.95.75'),
(638, 'Jorge Valdivia Toro', 'Acceso al Home del Administrador', '2026-11-07 18:58:03', 'Ingreso al panel principal', '186.189.95.75'),
(639, 'Jorge Valdivia Toro', 'Acceso al Home del Administrador', '2026-11-07 18:58:03', 'Ingreso al panel principal', '186.189.95.75'),
(640, 'Jorge Valdivia Toro', 'Acceso al Home del Administrador', '2026-11-07 18:58:05', 'Ingreso al panel principal', '186.189.95.75'),
(641, 'Jorge Valdivia Toro', 'Acceso al Home del Administrador', '2026-11-07 18:58:06', 'Ingreso al panel principal', '186.189.95.75'),
(642, 'Jorge Valdivia Toro', 'Acceso al Home del Administrador', '2026-11-07 18:59:17', 'Ingreso al panel principal', '186.189.95.75'),
(643, 'Jorge Valdivia Toro', 'Acceso al Home del Administrador', '2026-11-07 18:59:19', 'Ingreso al panel principal', '186.189.95.75'),
(644, 'Jorge Valdivia Toro', 'Acceso al Home del Administrador', '2026-11-07 18:59:20', 'Ingreso al panel principal', '186.189.95.75'),
(645, 'Jorge Valdivia Toro', 'Acceso al Home del Administrador', '2026-11-07 18:59:22', 'Ingreso al panel principal', '186.189.95.75'),
(646, 'Jorge Valdivia Toro', 'Acceso al Home del Administrador', '2026-11-07 18:59:22', 'Ingreso al panel principal', '186.189.95.75'),
(647, 'Jorge Valdivia Toro', 'Acceso al Home del Administrador', '2026-11-07 18:59:22', 'Ingreso al panel principal', '186.189.95.75'),
(648, 'Jorge Valdivia Toro', 'Acceso al Home del Administrador', '2026-11-07 18:59:23', 'Ingreso al panel principal', '186.189.95.75'),
(649, 'Jorge Valdivia Toro', 'Acceso al Home del Administrador', '2026-11-07 18:59:24', 'Ingreso al panel principal', '186.189.95.75'),
(650, 'Jorge Valdivia Toro', 'Acceso al Home del Administrador', '2026-11-07 18:59:27', 'Ingreso al panel principal', '186.189.95.75'),
(651, 'Jorge Valdivia Toro', 'Acceso al Home del Administrador', '2026-11-07 18:59:28', 'Ingreso al panel principal', '186.189.95.75'),
(652, 'Jorge Valdivia Toro', 'Acceso al Home del Administrador', '2026-11-07 18:59:28', 'Ingreso al panel principal', '186.189.95.75'),
(653, 'Jorge Valdivia Toro', 'Acceso al Home del Administrador', '2026-11-07 19:01:20', 'Ingreso al panel principal', '186.189.95.75'),
(654, 'Jorge Valdivia Toro', 'Acceso al Home del Administrador', '2026-11-07 19:01:21', 'Ingreso al panel principal', '186.189.95.75'),
(655, 'Jorge Valdivia Toro', 'Acceso al Home del Administrador', '2026-11-07 19:01:22', 'Ingreso al panel principal', '186.189.95.75'),
(656, 'Jorge Valdivia Toro', 'Acceso al Home del Administrador', '2026-11-07 19:01:23', 'Ingreso al panel principal', '186.189.95.75'),
(657, 'Jorge Valdivia Toro', 'Acceso al Home del Administrador', '2026-11-07 19:01:23', 'Ingreso al panel principal', '186.189.95.75'),
(658, 'Jorge Valdivia Toro', 'Acceso al Home del Administrador', '2026-11-07 19:01:23', 'Ingreso al panel principal', '186.189.95.75'),
(659, 'Jorge Valdivia Toro', 'Acceso al Home del Administrador', '2026-11-07 19:01:24', 'Ingreso al panel principal', '186.189.95.75'),
(660, 'Jorge Valdivia Toro', 'Acceso al Home del Administrador', '2026-11-07 19:01:25', 'Ingreso al panel principal', '186.189.95.75'),
(661, 'Jorge Valdivia Toro', 'Acceso al Home del Administrador', '2026-11-07 19:01:35', 'Ingreso al panel principal', '186.189.95.75'),
(662, 'Jorge Valdivia Toro', 'Acceso al Home del Administrador', '2026-11-07 19:01:36', 'Ingreso al panel principal', '186.189.95.75'),
(663, 'Jorge Valdivia Toro', 'Acceso al Home del Administrador', '2026-11-07 19:01:36', 'Ingreso al panel principal', '186.189.95.75'),
(664, 'Jorge Valdivia Toro', 'Acceso al Home del Administrador', '2026-11-07 19:03:55', 'Ingreso al panel principal', '186.189.95.75'),
(665, 'Jorge Valdivia Toro', 'Acceso al Home del Administrador', '2026-11-07 19:03:56', 'Ingreso al panel principal', '186.189.95.75'),
(666, 'Jorge Valdivia Toro', 'Acceso al Home del Administrador', '2026-11-07 19:03:57', 'Ingreso al panel principal', '186.189.95.75'),
(667, 'Jorge Valdivia Toro', 'Acceso al Home del Administrador', '2026-11-07 19:03:58', 'Ingreso al panel principal', '186.189.95.75'),
(668, 'Jorge Valdivia Toro', 'Acceso al Home del Administrador', '2026-11-07 19:03:58', 'Ingreso al panel principal', '186.189.95.75'),
(669, 'Jorge Valdivia Toro', 'Acceso al Home del Administrador', '2026-11-07 19:03:59', 'Ingreso al panel principal', '186.189.95.75'),
(670, 'Jorge Valdivia Toro', 'Acceso al Home del Administrador', '2026-11-07 19:03:59', 'Ingreso al panel principal', '186.189.95.75'),
(671, 'Jorge Valdivia Toro', 'Acceso al Home del Administrador', '2026-11-07 19:04:00', 'Ingreso al panel principal', '186.189.95.75'),
(672, 'Jorge Valdivia Toro', 'Acceso al Home del Administrador', '2026-11-07 19:04:02', 'Ingreso al panel principal', '186.189.95.75'),
(673, 'Jorge Valdivia Toro', 'Acceso al Home del Administrador', '2026-11-07 19:04:04', 'Ingreso al panel principal', '186.189.95.75'),
(674, 'Jorge Valdivia Toro', 'Acceso al Home del Administrador', '2026-11-07 19:07:20', 'Ingreso al panel principal', '186.189.95.75'),
(675, 'Jorge Valdivia Toro', 'Acceso al Home del Administrador', '2026-11-07 19:10:00', 'Ingreso al panel principal', '186.189.95.75'),
(676, 'Jorge Valdivia Toro', 'Acceso al Home del Administrador', '2026-11-07 19:16:09', 'Ingreso al panel principal', '186.189.95.75'),
(677, 'Jorge Valdivia Toro', 'Acceso al Home del Administrador', '2026-11-07 19:19:04', 'Ingreso al panel principal', '186.189.103.222'),
(678, 'Maria Pinto', 'Acceso al Home del Supervisor', '2026-11-07 19:19:35', 'Ingreso al panel principal', '186.189.103.222'),
(679, 'Jorge Valdivia Toro', 'Acceso al Home del Administrador', '2026-11-07 19:20:23', 'Ingreso al panel principal', '186.189.103.222'),
(680, 'Maria Pinto', 'Acceso al Home del Supervisor', '2026-11-07 19:21:31', 'Ingreso al panel principal', '186.189.103.222'),
(681, 'Jorge Valdivia Toro', 'Acceso al Home del Administrador', '2026-11-07 19:24:35', 'Ingreso al panel principal', '186.189.103.222'),
(682, 'Jorge Valdivia Toro', 'Acceso al Home del Administrador', '2026-11-07 19:32:02', 'Ingreso al panel principal', '186.189.103.222'),
(683, 'Maria Pinto', 'Acceso al Home del Supervisor', '2026-11-07 19:34:57', 'Ingreso al panel principal', '186.189.103.222'),
(684, 'Maria Pinto', 'Acceso al Home del Supervisor', '2026-11-07 19:36:04', 'Ingreso al panel principal', '186.189.103.222'),
(685, 'Maria Pinto', 'Acceso al Home del Supervisor', '2026-11-07 19:39:05', 'Ingreso al panel principal', '186.189.103.222'),
(686, 'Jorge Valdivia Toro', 'Acceso al Home del Administrador', '2026-11-07 19:39:30', 'Ingreso al panel principal', '186.189.103.222'),
(687, 'Jorge Valdivia Toro', 'Acceso al Home del Administrador', '2026-11-07 19:40:14', 'Ingreso al panel principal', '186.189.103.222'),
(688, 'Jorge Valdivia Toro', 'Acceso al Home del Administrador', '2026-11-07 19:40:30', 'Ingreso al panel principal', '186.189.103.222'),
(689, 'Jorge Valdivia Toro', 'Acceso al Home del Administrador', '2026-11-07 19:55:31', 'Ingreso al panel principal', '186.189.95.75'),
(690, 'Jorge Valdivia Toro', 'Acceso al Home del Administrador', '2026-11-08 13:01:57', 'Ingreso al panel principal', '179.60.92.241'),
(691, 'Jorge Valdivia Toro', 'Acceso al Home del Administrador', '2026-11-08 13:04:18', 'Ingreso al panel principal', '179.60.92.241'),
(692, 'Jorge Valdivia Toro', 'Acceso al Home del Administrador', '2026-11-08 13:07:48', 'Ingreso al panel principal', '179.60.92.241'),
(693, 'Jorge Valdivia Toro', 'Acceso al Home del Administrador', '2026-11-08 13:28:52', 'Ingreso al panel principal', '179.60.89.129'),
(694, 'Jorge Valdivia Toro', 'Acceso al Home del Administrador', '2026-11-08 13:29:09', 'Ingreso al panel principal', '179.60.89.129'),
(695, 'Jorge Valdivia Toro', 'Acceso al Home del Administrador', '2026-11-08 13:29:11', 'Ingreso al panel principal', '179.60.89.129'),
(696, 'Jorge Valdivia Toro', 'Acceso al Home del Administrador', '2026-11-08 13:29:16', 'Ingreso al panel principal', '179.60.89.129'),
(697, 'Maria Pinto', 'Acceso al Home del Supervisor', '2026-11-08 13:29:49', 'Ingreso al panel principal', '179.60.89.129'),
(698, 'Maria Pinto', 'Acceso al Home del Supervisor', '2026-11-08 13:30:08', 'Ingreso al panel principal', '179.60.89.129'),
(699, 'Maria Pinto', 'Acceso al Home del Supervisor', '2026-11-08 13:30:25', 'Ingreso al panel principal', '179.60.89.129'),
(700, 'Maria Pinto', 'Acceso al Home del Supervisor', '2026-11-08 13:30:27', 'Ingreso al panel principal', '179.60.89.129'),
(701, 'Maria Pinto', 'Acceso al Home del Supervisor', '2026-11-08 13:30:28', 'Ingreso al panel principal', '179.60.89.129'),
(702, 'Maria Pinto', 'Acceso al Home del Supervisor', '2026-11-08 13:30:37', 'Ingreso al panel principal', '179.60.89.129'),
(703, 'Maria Pinto', 'Acceso al Home del Supervisor', '2026-11-08 13:30:39', 'Ingreso al panel principal', '179.60.89.129'),
(704, 'Jorge Valdivia Toro', 'Acceso al Home del Administrador', '2026-11-08 13:31:10', 'Ingreso al panel principal', '179.60.89.129'),
(705, 'Maria Pinto', 'Acceso al Home del Supervisor', '2026-11-08 13:31:27', 'Ingreso al panel principal', '179.60.89.129'),
(706, 'Maria Pinto', 'Acceso al Home del Supervisor', '2026-11-08 13:31:36', 'Ingreso al panel principal', '179.60.89.129'),
(707, 'Maria Pinto', 'Acceso al Home del Supervisor', '2026-11-08 13:31:38', 'Ingreso al panel principal', '179.60.89.129'),
(708, 'Maria Pinto', 'Acceso al Home del Supervisor', '2026-11-08 13:31:39', 'Ingreso al panel principal', '179.60.89.129'),
(709, 'Maria Pinto', 'Acceso al Home del Supervisor', '2026-11-08 13:31:41', 'Ingreso al panel principal', '179.60.89.129'),
(710, 'Maria Pinto', 'Acceso al Home del Supervisor', '2026-11-08 13:31:43', 'Ingreso al panel principal', '179.60.89.129'),
(711, 'Maria Pinto', 'Acceso al Home del Supervisor', '2026-11-08 13:31:44', 'Ingreso al panel principal', '179.60.89.129'),
(712, 'Jorge Valdivia Toro', 'Acceso al Home del Administrador', '2026-11-08 13:31:49', 'Ingreso al panel principal', '179.60.89.129'),
(713, 'Jorge Valdivia Toro', 'Acceso al Home del Administrador', '2026-11-08 13:53:32', 'Ingreso al panel principal', '179.60.89.64'),
(714, 'Jorge Valdivia Toro', 'Acceso al Home del Administrador', '2026-11-08 13:53:37', 'Ingreso al panel principal', '179.60.89.64'),
(715, 'Maria Pinto', 'Acceso al Home del Supervisor', '2026-11-08 14:00:21', 'Ingreso al panel principal', '190.107.228.75'),
(716, 'Jorge Valdivia Toro', 'Acceso al Home del Administrador', '2026-11-08 14:01:27', 'Ingreso al panel principal', '190.107.228.75'),
(717, 'Jorge Valdivia Toro', 'Acceso al Home del Administrador', '2026-11-08 14:15:22', 'Ingreso al panel principal', '179.60.89.64'),
(718, 'Jorge Valdivia Toro', 'Acceso al Home del Administrador', '2026-11-08 14:20:39', 'Ingreso al panel principal', '179.60.89.64'),
(719, 'Maria pinto', 'Acceso al Home del Supervisor', '2026-11-08 14:22:36', 'Ingreso al panel principal', '179.60.89.64'),
(720, 'Maria pinto', 'Acceso al Home del Supervisor', '2026-11-08 14:31:22', 'Ingreso al panel principal', '179.60.89.64'),
(721, 'Maria Pinto', 'Acceso al Home del Supervisor', '2026-11-08 14:49:10', 'Ingreso al panel principal', '179.60.89.64'),
(722, 'Jorge Valdivia Toro', 'Acceso al Home del Administrador', '2026-11-08 14:55:43', 'Ingreso al panel principal', '179.238.35.206'),
(723, 'Jorge Valdivia Toro', 'Acceso al Home del Administrador', '2026-11-08 14:56:49', 'Ingreso al panel principal', '179.238.35.206'),
(724, 'Jorge Valdivia Toro', 'Acceso al Home del Administrador', '2026-11-08 15:01:15', 'Ingreso al panel principal', '186.189.103.222'),
(725, 'Maria Pinto', 'Acceso al Home del Supervisor', '2026-11-08 15:04:17', 'Ingreso al panel principal', '179.238.35.206'),
(726, 'Jorge Valdivia Toro', 'Acceso al Home del Administrador', '2026-11-08 19:10:57', 'Ingreso al panel principal', '201.219.234.75'),
(727, 'Maria Pinto', 'Acceso al Home del Supervisor', '2026-11-08 19:11:51', 'Ingreso al panel principal', '201.219.234.75'),
(728, 'Maria Pinto', 'Acceso al Home del Supervisor', '2026-11-08 19:21:44', 'Ingreso al panel principal', '201.219.234.75'),
(729, 'Maria Pinto', 'Acceso al Home del Supervisor', '2026-11-08 19:43:21', 'Ingreso al panel principal', '201.219.234.75'),
(730, 'Jorge Valdivia Toro', 'Acceso al Home del Administrador', '2026-11-08 19:43:45', 'Ingreso al panel principal', '201.219.234.75'),
(731, 'Maria Pinto', 'Acceso al Home del Supervisor', '2026-11-08 20:33:16', 'Ingreso al panel principal', '186.11.84.250'),
(732, 'Maria Pinto', 'Acceso al Home del Supervisor', '2026-11-08 20:46:31', 'Ingreso al panel principal', '186.11.84.250'),
(733, 'Maria Pinto', 'Acceso al Home del Supervisor', '2026-11-08 20:49:19', 'Ingreso al panel principal', '186.11.84.250'),
(734, 'Maria Pinto', 'Acceso al Home del Supervisor', '2026-11-08 20:49:36', 'Ingreso al panel principal', '186.11.84.250'),
(735, 'Maria Pinto', 'Acceso al Home del Supervisor', '2026-11-08 20:52:28', 'Ingreso al panel principal', '186.11.84.250'),
(736, 'Maria Pinto', 'Acceso al Home del Supervisor', '2026-11-08 21:04:03', 'Ingreso al panel principal', '186.11.84.250'),
(737, 'Maria Pinto', 'Acceso al Home del Supervisor', '2026-11-08 21:06:28', 'Ingreso al panel principal', '186.11.84.250'),
(738, 'Jorge Valdivia Toro', 'Acceso al Home del Administrador', '2026-11-08 21:06:55', 'Ingreso al panel principal', '201.219.234.75'),
(739, 'Jorge Valdivia Toro', 'Acceso al Home del Administrador', '2026-11-08 21:08:18', 'Ingreso al panel principal', '201.219.234.75'),
(740, 'Jorge Valdivia Toro', 'Acceso al Home del Administrador', '2026-11-08 21:08:41', 'Ingreso al panel principal', '186.11.84.250'),
(741, 'Jorge Valdivia Toro', 'Acceso al Home del Administrador', '2026-11-08 21:09:35', 'Ingreso al panel principal', '186.11.84.250'),
(742, 'Jorge Valdivia Toro', 'Acceso al Home del Administrador', '2026-11-08 21:09:47', 'Ingreso al panel principal', '186.11.84.250'),
(743, 'Jorge Valdivia Toro', 'Acceso al Home del Administrador', '2026-11-08 21:14:28', 'Ingreso al panel principal', '186.11.84.250'),
(744, 'Jorge Valdivia Toro', 'Acceso al Home del Administrador', '2026-11-08 21:14:31', 'Ingreso al panel principal', '186.11.84.250'),
(745, 'Jorge Valdivia Toro', 'Acceso al Home del Administrador', '2026-11-08 21:15:32', 'Ingreso al panel principal', '201.219.234.75'),
(746, 'Jorge Valdivia Toro', 'Acceso al Home del Administrador', '2026-11-08 21:18:54', 'Ingreso al panel principal', '201.219.234.75'),
(747, 'Maria Pinto', 'Acceso al Home del Supervisor', '2026-11-08 21:20:52', 'Ingreso al panel principal', '201.219.234.75'),
(748, 'Jorge Valdivia Toro', 'Acceso al Home del Administrador', '2026-11-08 21:20:59', 'Ingreso al panel principal', '186.11.84.250'),
(749, 'Maria Pinto', 'Acceso al Home del Supervisor', '2026-11-08 21:21:13', 'Ingreso al panel principal', '201.219.234.75'),
(750, 'Maria Pinto', 'Acceso al Home del Supervisor', '2026-11-08 21:25:00', 'Ingreso al panel principal', '201.219.234.75'),
(751, 'Jorge Valdivia Toro', 'Acceso al Home del Administrador', '2026-11-08 21:26:48', 'Ingreso al panel principal', '186.11.84.250'),
(752, 'Jorge Valdivia Toro', 'Acceso al Home del Administrador', '2026-11-08 21:30:21', 'Ingreso al panel principal', '186.11.84.250');
INSERT INTO `logs_seguridad` (`id`, `usuario`, `accion`, `fecha`, `detalle`, `ip_origen`) VALUES
(753, 'Jorge Valdivia Toro', 'Acceso al Home del Administrador', '2026-11-08 21:30:29', 'Ingreso al panel principal', '186.11.84.250'),
(754, 'Jorge Valdivia Toro', 'Acceso al Home del Administrador', '2026-11-08 21:31:59', 'Ingreso al panel principal', '201.219.234.75'),
(755, 'Maria Pinto', 'Acceso al Home del Supervisor', '2026-11-08 21:32:57', 'Ingreso al panel principal', '201.219.234.75'),
(756, 'Jorge Valdivia Toro', 'Acceso al Home del Administrador', '2026-11-08 21:33:32', 'Ingreso al panel principal', '186.11.84.250'),
(757, 'Maria Pinto', 'Acceso al Home del Supervisor', '2026-11-08 21:33:38', 'Ingreso al panel principal', '186.11.84.250'),
(758, 'Jorge Valdivia Toro', 'Acceso al Home del Administrador', '2026-11-08 21:41:46', 'Ingreso al panel principal', '186.11.84.250'),
(759, 'Jorge Valdivia Toro', 'Acceso al Home del Administrador', '2026-11-08 21:43:16', 'Ingreso al panel principal', '186.11.84.250'),
(760, 'Maria Pinto', 'Acceso al Home del Supervisor', '2026-11-08 21:45:29', 'Ingreso al panel principal', '201.219.234.75'),
(761, 'Maria Pinto', 'Acceso al Home del Supervisor', '2026-11-08 21:45:30', 'Ingreso al panel principal', '201.219.234.75'),
(762, 'Maria Pinto', 'Acceso al Home del Supervisor', '2026-11-08 21:45:30', 'Ingreso al panel principal', '201.219.234.75'),
(763, 'Maria Pinto', 'Acceso al Home del Supervisor', '2026-11-08 21:45:30', 'Ingreso al panel principal', '201.219.234.75'),
(764, 'Maria Pinto', 'Acceso al Home del Supervisor', '2026-11-08 21:45:30', 'Ingreso al panel principal', '201.219.234.75'),
(765, 'Jorge Valdivia Toro', 'Acceso al Home del Administrador', '2026-11-08 21:48:42', 'Ingreso al panel principal', '186.11.84.250'),
(766, 'Maria Pinto', 'Acceso al Home del Supervisor', '2026-11-08 21:49:18', 'Ingreso al panel principal', '201.219.234.75'),
(767, 'Jorge Valdivia Toro', 'Acceso al Home del Administrador', '2026-11-08 21:49:43', 'Ingreso al panel principal', '201.219.234.75'),
(768, 'Jorge Valdivia Toro', 'Acceso al Home del Administrador', '2026-11-08 21:52:14', 'Ingreso al panel principal', '201.219.234.75'),
(769, 'Jorge Valdivia Toro', 'Acceso al Home del Administrador', '2026-11-08 21:53:17', 'Ingreso al panel principal', '201.219.234.75'),
(770, 'Jorge Valdivia Toro', 'Acceso al Home del Administrador', '2026-11-08 21:53:19', 'Ingreso al panel principal', '201.219.234.75'),
(771, 'Jorge Valdivia Toro', 'Acceso al Home del Administrador', '2026-11-08 21:53:19', 'Ingreso al panel principal', '201.219.234.75'),
(772, 'Jorge Valdivia Toro', 'Acceso al Home del Administrador', '2026-11-08 21:53:19', 'Ingreso al panel principal', '201.219.234.75'),
(773, 'Jorge Valdivia Toro', 'Acceso al Home del Administrador', '2026-11-08 21:55:34', 'Ingreso al panel principal', '201.219.234.75'),
(774, 'Jorge Valdivia Toro', 'Acceso al Home del Administrador', '2026-11-08 21:57:47', 'Ingreso al panel principal', '179.60.89.48'),
(775, 'Jorge Valdivia Toro', 'Acceso al Home del Administrador', '2026-11-08 22:00:18', 'Ingreso al panel principal', '179.60.89.48'),
(776, 'Jorge Valdivia Toro', 'Acceso al Home del Administrador', '2026-11-08 22:00:26', 'Ingreso al panel principal', '179.60.89.48'),
(777, 'Jorge Valdivia Toro', 'Acceso al Home del Administrador', '2026-11-08 22:04:37', 'Ingreso al panel principal', '201.219.234.75'),
(778, 'Jorge Valdivia Toro', 'Acceso al Home del Administrador', '2026-11-08 22:06:17', 'Ingreso al panel principal', '201.219.234.75'),
(779, 'Maria Pinto', 'Acceso al Home del Supervisor', '2026-11-08 22:25:24', 'Ingreso al panel principal', '186.11.84.250'),
(780, 'Jorge Valdivia Toro', 'Acceso al Home del Administrador', '2026-11-08 22:25:44', 'Ingreso al panel principal', '186.11.84.250'),
(781, 'Jorge Valdivia Toro', 'Acceso al Home del Administrador', '2026-11-08 22:25:52', 'Ingreso al panel principal', '190.5.37.26'),
(782, 'Maria Pinto', 'Acceso al Home del Supervisor', '2026-11-08 22:26:30', 'Ingreso al panel principal', '186.11.84.250'),
(783, 'Maria Pinto', 'Acceso al Home del Supervisor', '2026-11-08 22:26:43', 'Ingreso al panel principal', '186.11.84.250'),
(784, 'Jorge Valdivia Toro', 'Acceso al Home del Administrador', '2026-11-08 22:26:55', 'Ingreso al panel principal', '190.5.37.26'),
(785, 'Jorge Valdivia Toro', 'Acceso al Home del Administrador', '2026-11-08 22:29:25', 'Ingreso al panel principal', '190.5.37.26'),
(786, 'Maria Pinto', 'Acceso al Home del Supervisor', '2026-11-08 22:31:04', 'Ingreso al panel principal', '186.11.84.250'),
(787, 'Maria Pinto', 'Acceso al Home del Supervisor', '2026-11-08 22:31:43', 'Ingreso al panel principal', '186.11.84.250'),
(788, 'Jorge Valdivia Toro', 'Acceso al Home del Administrador', '2026-11-08 22:32:06', 'Ingreso al panel principal', '190.5.37.26'),
(789, 'Maria Pinto', 'Acceso al Home del Supervisor', '2026-11-08 22:32:34', 'Ingreso al panel principal', '186.11.84.250'),
(790, 'Jorge Valdivia Toro', 'Acceso al Home del Administrador', '2026-11-08 22:32:42', 'Ingreso al panel principal', '186.11.84.250'),
(791, 'Jorge Valdivia Toro', 'Acceso al Home del Administrador', '2026-11-08 22:35:06', 'Ingreso al panel principal', '186.11.84.250'),
(792, 'Jorge Valdivia Toro', 'Acceso al Home del Administrador', '2026-11-08 22:35:10', 'Ingreso al panel principal', '186.11.84.250'),
(793, 'Jorge Valdivia Toro', 'Acceso al Home del Administrador', '2026-11-08 22:38:40', 'Ingreso al panel principal', '190.5.37.26'),
(794, 'Jorge Valdivia Toro', 'Acceso al Home del Administrador', '2026-11-08 22:38:50', 'Ingreso al panel principal', '190.5.37.26'),
(795, 'Jorge Valdivia Toro', 'Acceso al Home del Administrador', '2026-11-08 22:40:16', 'Ingreso al panel principal', '190.5.37.26'),
(796, 'Jorge Valdivia Toro', 'Acceso al Home del Administrador', '2026-11-08 22:48:48', 'Ingreso al panel principal', '179.60.89.48'),
(797, 'Jorge Valdivia Toro', 'Acceso al Home del Administrador', '2026-11-08 23:01:12', 'Ingreso al panel principal', '179.60.89.129'),
(798, 'Maria Pinto', 'Acceso al Home del Supervisor', '2026-11-08 23:01:33', 'Ingreso al panel principal', '179.60.89.129'),
(799, 'Jorge Valdivia Toro', 'Acceso al Home del Administrador', '2026-11-08 23:07:36', 'Ingreso al panel principal', '179.60.89.129'),
(800, 'Jorge Valdivia Toro', 'Acceso al Home del Administrador', '2026-11-08 23:08:59', 'Ingreso al panel principal', '190.5.37.26'),
(801, 'Jorge Valdivia Toro', 'Acceso al Home del Administrador', '2026-11-08 23:09:16', 'Ingreso al panel principal', '190.5.37.26'),
(802, 'Jorge Valdivia Toro', 'Acceso al Home del Administrador', '2026-11-08 23:10:57', 'Ingreso al panel principal', '186.11.84.250'),
(803, 'Jorge Valdivia Toro', 'Acceso al Home del Administrador', '2026-11-08 23:11:55', 'Ingreso al panel principal', '190.5.37.26'),
(804, 'Jorge Valdivia Toro', 'Acceso al Home del Administrador', '2026-11-08 23:12:03', 'Ingreso al panel principal', '179.60.89.129'),
(805, 'Jorge Valdivia Toro', 'Acceso al Home del Administrador', '2026-11-08 23:12:34', 'Ingreso al panel principal', '179.60.89.48'),
(806, 'Jorge Valdivia Toro', 'Acceso al Home del Administrador', '2026-11-08 23:12:42', 'Ingreso al panel principal', '186.11.84.250'),
(807, 'Maria Pinto', 'Acceso al Home del Supervisor', '2026-11-08 23:13:37', 'Ingreso al panel principal', '186.11.84.250'),
(808, 'Jorge Valdivia Toro', 'Acceso al Home del Administrador', '2026-11-08 23:14:05', 'Ingreso al panel principal', '179.60.89.48'),
(809, 'Jorge Valdivia Toro', 'Acceso al Home del Administrador', '2026-11-08 23:14:24', 'Ingreso al panel principal', '179.60.89.48'),
(810, 'Jorge Valdivia Toro', 'Acceso al Home del Administrador', '2026-11-08 23:19:31', 'Ingreso al panel principal', '179.60.89.129'),
(811, 'Jorge Valdivia Toro', 'Acceso al Home del Administrador', '2026-11-08 23:19:39', 'Ingreso al panel principal', '179.60.89.129'),
(812, 'Jorge Valdivia Toro', 'Acceso al Home del Administrador', '2026-11-08 23:23:17', 'Ingreso al panel principal', '190.5.37.26'),
(813, 'Maria Pinto', 'Acceso al Home del Supervisor', '2026-11-08 23:23:47', 'Ingreso al panel principal', '186.11.84.250'),
(814, 'Jorge Valdivia Toro', 'Acceso al Home del Administrador', '2026-11-08 23:24:30', 'Ingreso al panel principal', '186.11.84.250'),
(815, 'Jorge Valdivia Toro', 'Acceso al Home del Administrador', '2026-11-08 23:25:54', 'Ingreso al panel principal', '186.11.84.250'),
(816, 'Jorge Valdivia Toro', 'Acceso al Home del Administrador', '2026-11-08 23:34:26', 'Ingreso al panel principal', '186.11.84.250'),
(817, 'Jorge Valdivia Toro', 'Acceso al Home del Administrador', '2026-11-08 23:37:47', 'Ingreso al panel principal', '190.5.37.26'),
(818, 'Jorge Valdivia Toro', 'Acceso al Home del Administrador', '2026-11-08 23:37:53', 'Ingreso al panel principal', '190.5.37.26'),
(819, 'Jorge Valdivia Toro', 'Acceso al Home del Administrador', '2026-11-08 23:38:44', 'Ingreso al panel principal', '179.60.89.48'),
(820, 'Jorge Valdivia Toro', 'Acceso al Home del Administrador', '2026-11-08 23:38:53', 'Ingreso al panel principal', '179.60.89.48'),
(821, 'Jorge Valdivia Toro', 'Acceso al Home del Administrador', '2026-11-08 23:38:57', 'Ingreso al panel principal', '190.5.37.26'),
(822, 'Jorge Valdivia Toro', 'Acceso al Home del Administrador', '2026-11-08 23:45:59', 'Ingreso al panel principal', '190.5.37.26'),
(823, 'Jorge Valdivia Toro', 'Acceso al Home del Administrador', '2026-11-08 23:52:14', 'Ingreso al panel principal', '179.60.89.129'),
(824, 'Jorge Valdivia Toro', 'Acceso al Home del Administrador', '2026-11-08 23:52:19', 'Ingreso al panel principal', '179.60.89.129'),
(825, 'Jorge Valdivia Toro', 'Acceso al Home del Administrador', '2026-11-08 23:56:28', 'Ingreso al panel principal', '179.60.89.48'),
(826, 'Jorge Valdivia Toro', 'Acceso al Home del Administrador', '2026-11-08 23:57:30', 'Ingreso al panel principal', '179.60.89.48'),
(827, 'Jorge Valdivia Toro', 'Acceso al Home del Administrador', '2026-11-08 23:57:56', 'Ingreso al panel principal', '190.5.37.26'),
(828, 'Jorge Valdivia Toro', 'Acceso al Home del Administrador', '2026-11-08 23:59:41', 'Ingreso al panel principal', '179.60.89.48'),
(829, 'Jorge Valdivia Toro', 'Acceso al Home del Administrador', '2026-11-08 23:59:50', 'Ingreso al panel principal', '179.60.89.48'),
(830, 'Jorge Valdivia Toro', 'Acceso al Home del Administrador', '2026-11-09 00:02:14', 'Ingreso al panel principal', '190.5.37.26'),
(831, 'Jorge Valdivia Toro', 'Acceso al Home del Administrador', '2026-11-09 00:10:06', 'Ingreso al panel principal', '179.60.89.48'),
(832, 'Jorge Valdivia Toro', 'Acceso al Home del Administrador', '2026-11-09 00:15:49', 'Ingreso al panel principal', '179.60.89.129'),
(833, 'Jorge Valdivia Toro', 'Acceso al Home del Administrador', '2026-11-09 00:16:13', 'Ingreso al panel principal', '179.60.89.129'),
(834, 'Jorge Valdivia Toro', 'Acceso al Home del Administrador', '2026-11-09 00:16:18', 'Ingreso al panel principal', '179.60.89.129'),
(835, 'Jorge Valdivia Toro', 'Acceso al Home del Administrador', '2026-11-09 00:16:25', 'Ingreso al panel principal', '179.60.89.129'),
(836, 'Jorge Valdivia Toro', 'Acceso al Home del Administrador', '2026-11-09 00:16:32', 'Ingreso al panel principal', '179.60.89.129'),
(837, 'Jorge Valdivia Toro', 'Acceso al Home del Administrador', '2026-11-09 00:16:40', 'Ingreso al panel principal', '179.60.89.129'),
(838, 'Jorge Valdivia Toro', 'Acceso al Home del Administrador', '2026-11-09 00:16:49', 'Ingreso al panel principal', '179.60.89.129'),
(839, 'Jorge Valdivia Toro', 'Acceso al Home del Administrador', '2026-11-09 00:17:20', 'Ingreso al panel principal', '179.60.89.129'),
(840, 'Jorge Valdivia Toro', 'Acceso al Home del Administrador', '2026-11-09 00:17:26', 'Ingreso al panel principal', '179.60.89.129'),
(841, 'Maria Pinto', 'Acceso al Home del Supervisor', '2026-11-09 00:17:30', 'Ingreso al panel principal', '179.60.89.129'),
(842, 'Maria Pinto', 'Acceso al Home del Supervisor', '2026-11-09 00:17:30', 'Ingreso al panel principal', '190.5.37.26'),
(843, 'Jorge Valdivia Toro', 'Acceso al Home del Administrador', '2026-11-09 00:26:01', 'Ingreso al panel principal', '179.60.89.129'),
(844, 'Jorge Valdivia Toro', 'Acceso al Home del Administrador', '2026-11-09 00:26:54', 'Ingreso al panel principal', '179.60.89.129'),
(845, 'Jorge Valdivia Toro', 'Acceso al Home del Administrador', '2026-11-09 00:27:19', 'Ingreso al panel principal', '179.60.89.129'),
(846, 'Jorge Valdivia Toro', 'Acceso al Home del Administrador', '2026-11-09 00:27:33', 'Ingreso al panel principal', '179.60.89.129'),
(847, 'Jorge Valdivia Toro', 'Acceso al Home del Administrador', '2026-11-09 00:30:14', 'Ingreso al panel principal', '179.60.89.129'),
(848, 'Jorge Valdivia Toro', 'Acceso al Home del Administrador', '2026-11-09 00:30:32', 'Ingreso al panel principal', '179.60.89.129'),
(849, 'Jorge Valdivia Toro', 'Acceso al Home del Administrador', '2026-11-09 00:30:36', 'Ingreso al panel principal', '179.60.89.129'),
(850, 'Jorge Valdivia Toro', 'Acceso al Home del Administrador', '2026-11-09 00:30:51', 'Ingreso al panel principal', '179.60.89.129'),
(851, 'Jorge Valdivia Toro', 'Acceso al Home del Administrador', '2026-11-09 00:35:08', 'Ingreso al panel principal', '190.161.67.122'),
(852, 'Jorge Valdivia Toro', 'Acceso al Home del Administrador', '2026-11-09 00:36:34', 'Ingreso al panel principal', '179.60.89.129'),
(853, 'Jorge Valdivia Toro', 'Acceso al Home del Administrador', '2026-11-09 00:36:50', 'Ingreso al panel principal', '179.60.89.129'),
(854, 'Jorge Valdivia Toro', 'Acceso al Home del Administrador', '2026-11-09 00:36:59', 'Ingreso al panel principal', '179.60.89.129'),
(855, 'Maria Pinto', 'Acceso al Home del Supervisor', '2026-11-09 00:37:04', 'Ingreso al panel principal', '179.60.89.129'),
(856, 'Maria Pinto', 'Acceso al Home del Supervisor', '2026-11-09 00:37:11', 'Ingreso al panel principal', '179.60.89.129'),
(857, 'Maria Pinto', 'Acceso al Home del Supervisor', '2026-11-09 00:37:33', 'Ingreso al panel principal', '179.60.89.129'),
(858, 'Maria Pinto', 'Acceso al Home del Supervisor', '2026-11-09 00:37:38', 'Ingreso al panel principal', '179.60.89.129'),
(859, 'Maria Pinto', 'Acceso al Home del Supervisor', '2026-11-09 00:38:12', 'Ingreso al panel principal', '179.60.89.129'),
(860, 'Maria Pinto', 'Acceso al Home del Supervisor', '2026-11-09 00:38:19', 'Ingreso al panel principal', '179.60.89.129'),
(861, 'Maria Pinto', 'Acceso al Home del Supervisor', '2026-11-09 00:38:32', 'Ingreso al panel principal', '179.60.89.129'),
(862, 'Jorge Valdivia Toro', 'Acceso al Home del Administrador', '2026-11-09 00:39:03', 'Ingreso al panel principal', '179.60.89.129'),
(863, 'Jorge Valdivia Toro', 'Acceso al Home del Administrador', '2026-11-09 00:40:58', 'Ingreso al panel principal', '190.161.67.122'),
(864, 'Jorge Valdivia Toro', 'Acceso al Home del Administrador', '2026-11-09 00:44:27', 'Ingreso al panel principal', '190.161.67.122'),
(865, 'Maria Pinto', 'Acceso al Home del Supervisor', '2026-11-09 01:10:32', 'Ingreso al panel principal', '201.219.234.75'),
(866, 'Jorge Valdivia Toro', 'Acceso al Home del Administrador', '2026-11-09 01:10:51', 'Ingreso al panel principal', '190.161.67.122'),
(867, 'Jorge Valdivia Toro', 'Acceso al Home del Administrador', '2026-11-09 01:11:47', 'Ingreso al panel principal', '190.161.67.122'),
(868, 'Jorge Valdivia Toro', 'Acceso al Home del Administrador', '2026-11-09 01:12:15', 'Ingreso al panel principal', '190.161.67.122'),
(869, 'Jorge Valdivia Toro', 'Acceso al Home del Administrador', '2026-11-09 01:13:13', 'Ingreso al panel principal', '190.161.67.122'),
(870, 'Jorge Valdivia Toro', 'Acceso al Home del Administrador', '2026-11-09 01:13:32', 'Ingreso al panel principal', '190.161.67.122'),
(871, 'Jorge Valdivia Toro', 'Acceso al Home del Administrador', '2026-11-09 01:13:41', 'Ingreso al panel principal', '190.161.67.122'),
(872, 'Jorge Valdivia Toro', 'Acceso al Home del Administrador', '2026-11-09 01:13:53', 'Ingreso al panel principal', '190.161.67.122'),
(873, 'Jorge Valdivia Toro', 'Acceso al Home del Administrador', '2026-11-09 01:14:12', 'Ingreso al panel principal', '190.161.67.122'),
(874, 'Jorge Valdivia Toro', 'Acceso al Home del Administrador', '2026-11-09 01:14:32', 'Ingreso al panel principal', '190.161.67.122'),
(875, 'Jorge Valdivia Toro', 'Acceso al Home del Administrador', '2026-11-09 01:14:47', 'Ingreso al panel principal', '190.161.67.122'),
(876, 'Jorge Valdivia Toro', 'Acceso al Home del Administrador', '2026-11-09 01:15:24', 'Ingreso al panel principal', '190.161.67.122'),
(877, 'Jorge Valdivia Toro', 'Acceso al Home del Administrador', '2026-11-09 01:15:38', 'Ingreso al panel principal', '190.161.67.122'),
(878, 'Jorge Valdivia Toro', 'Acceso al Home del Administrador', '2026-11-09 01:19:00', 'Ingreso al panel principal', '179.60.89.64'),
(879, 'Jorge Valdivia Toro', 'Acceso al Home del Administrador', '2026-11-09 01:19:06', 'Ingreso al panel principal', '179.60.89.64'),
(880, 'Jorge Valdivia Toro', 'Acceso al Home del Administrador', '2026-11-09 01:20:29', 'Ingreso al panel principal', '190.161.67.122'),
(881, 'Maria Pinto', 'Acceso al Home del Supervisor', '2026-11-09 01:20:35', 'Ingreso al panel principal', '179.60.89.64'),
(882, 'Jorge Valdivia Toro', 'Acceso al Home del Administrador', '2026-11-09 01:20:46', 'Ingreso al panel principal', '201.219.234.75'),
(883, 'Jorge Valdivia Toro', 'Acceso al Home del Administrador', '2026-11-09 01:20:48', 'Ingreso al panel principal', '201.219.234.75'),
(884, 'Jorge Valdivia Toro', 'Acceso al Home del Administrador', '2026-11-09 01:20:50', 'Ingreso al panel principal', '190.161.67.122'),
(885, 'Jorge Valdivia Toro', 'Acceso al Home del Administrador', '2026-11-09 01:20:52', 'Ingreso al panel principal', '190.161.67.122'),
(886, 'Jorge Valdivia Toro', 'Acceso al Home del Administrador', '2026-11-09 01:24:25', 'Ingreso al panel principal', '190.161.67.122'),
(887, 'Jorge Valdivia Toro', 'Acceso al Home del Administrador', '2026-11-09 01:25:27', 'Ingreso al panel principal', '190.161.67.122'),
(888, 'Maria Pinto', 'Acceso al Home del Supervisor', '2026-11-09 01:27:43', 'Ingreso al panel principal', '190.161.67.122'),
(889, 'Maria Pinto', 'Acceso al Home del Supervisor', '2026-11-09 01:29:29', 'Ingreso al panel principal', '190.161.67.122'),
(890, 'Maria Pinto', 'Acceso al Home del Supervisor', '2026-11-09 01:29:51', 'Ingreso al panel principal', '190.161.67.122'),
(891, 'Maria Pinto', 'Acceso al Home del Supervisor', '2026-11-09 01:30:11', 'Ingreso al panel principal', '190.161.67.122'),
(892, 'Maria Pinto', 'Acceso al Home del Supervisor', '2026-11-09 01:30:21', 'Ingreso al panel principal', '190.161.67.122'),
(893, 'Maria Pinto', 'Acceso al Home del Supervisor', '2026-11-09 01:30:38', 'Ingreso al panel principal', '190.161.67.122'),
(894, 'Maria Pinto', 'Acceso al Home del Supervisor', '2026-11-09 01:30:54', 'Ingreso al panel principal', '190.161.67.122'),
(895, 'Maria Pinto', 'Acceso al Home del Supervisor', '2026-11-09 01:31:04', 'Ingreso al panel principal', '190.161.67.122'),
(896, 'Maria Pinto', 'Acceso al Home del Supervisor', '2026-11-09 01:31:20', 'Ingreso al panel principal', '190.161.67.122'),
(897, 'Maria Pinto', 'Acceso al Home del Supervisor', '2026-11-09 01:32:20', 'Ingreso al panel principal', '201.219.234.75'),
(898, 'Maria Pinto', 'Acceso al Home del Supervisor', '2026-11-09 01:33:26', 'Ingreso al panel principal', '181.42.139.89'),
(899, 'Maria Pinto', 'Acceso al Home del Supervisor', '2026-11-09 02:27:21', 'Ingreso al panel principal', '181.42.139.89'),
(900, 'Maria Pinto', 'Acceso al Home del Supervisor', '2026-11-09 02:31:23', 'Ingreso al panel principal', '181.42.139.89'),
(901, 'Maria Pinto', 'Acceso al Home del Supervisor', '2026-11-09 02:31:29', 'Ingreso al panel principal', '181.42.139.89'),
(902, 'Maria Pinto', 'Acceso al Home del Supervisor', '2026-11-09 02:31:34', 'Ingreso al panel principal', '181.42.139.89'),
(903, 'Maria Pinto', 'Acceso al Home del Supervisor', '2026-11-09 02:31:36', 'Ingreso al panel principal', '181.42.139.89'),
(904, 'Maria Pinto', 'Acceso al Home del Supervisor', '2026-11-09 02:31:55', 'Ingreso al panel principal', '181.42.139.89'),
(905, 'Maria Pinto', 'Acceso al Home del Supervisor', '2026-11-09 02:32:00', 'Ingreso al panel principal', '181.42.139.89'),
(906, 'Maria Pinto', 'Acceso al Home del Supervisor', '2026-11-09 02:32:05', 'Ingreso al panel principal', '181.42.139.89'),
(907, 'Maria Pinto', 'Acceso al Home del Supervisor', '2026-11-09 02:32:07', 'Ingreso al panel principal', '181.42.139.89'),
(908, 'Maria Pinto', 'Acceso al Home del Supervisor', '2026-11-09 02:32:35', 'Ingreso al panel principal', '181.42.139.89'),
(909, 'Maria Pinto', 'Acceso al Home del Supervisor', '2026-11-09 02:33:08', 'Ingreso al panel principal', '181.42.139.89'),
(910, 'Maria Pinto', 'Acceso al Home del Supervisor', '2026-11-09 02:33:14', 'Ingreso al panel principal', '181.42.139.89'),
(911, 'Maria Pinto', 'Acceso al Home del Supervisor', '2026-11-09 02:33:19', 'Ingreso al panel principal', '181.42.139.89'),
(912, 'Maria Pinto', 'Acceso al Home del Supervisor', '2026-11-09 02:33:21', 'Ingreso al panel principal', '181.42.139.89'),
(913, 'Maria Pinto', 'Acceso al Home del Supervisor', '2026-11-09 02:42:10', 'Ingreso al panel principal', '181.42.139.89'),
(914, 'Maria Pinto', 'Acceso al Home del Supervisor', '2026-11-09 02:42:12', 'Ingreso al panel principal', '181.42.139.89'),
(915, 'Maria Pinto', 'Acceso al Home del Supervisor', '2026-11-09 02:42:14', 'Ingreso al panel principal', '181.42.139.89'),
(916, 'Maria Pinto', 'Acceso al Home del Supervisor', '2026-11-09 02:42:17', 'Ingreso al panel principal', '181.42.139.89'),
(917, 'Jorge Valdivia Toro', 'Acceso al Home del Administrador', '2026-11-09 02:42:27', 'Ingreso al panel principal', '201.219.234.75'),
(918, 'Jorge Valdivia Toro', 'Acceso al Home del Administrador', '2026-11-09 02:42:35', 'Ingreso al panel principal', '201.219.234.75'),
(919, 'Jorge Valdivia Toro', 'Acceso al Home del Administrador', '2026-11-09 02:42:38', 'Ingreso al panel principal', '201.219.234.75'),
(920, 'Maria Pinto', 'Acceso al Home del Supervisor', '2026-11-09 02:43:00', 'Ingreso al panel principal', '181.42.139.89'),
(921, 'Maria Pinto', 'Acceso al Home del Supervisor', '2026-11-09 02:43:05', 'Ingreso al panel principal', '181.42.139.89'),
(922, 'Maria Pinto', 'Acceso al Home del Supervisor', '2026-11-09 02:43:07', 'Ingreso al panel principal', '181.42.139.89'),
(923, 'Maria Pinto', 'Acceso al Home del Supervisor', '2026-11-09 02:43:09', 'Ingreso al panel principal', '181.42.139.89'),
(924, 'Maria Pinto', 'Acceso al Home del Supervisor', '2026-11-09 02:43:11', 'Ingreso al panel principal', '181.42.139.89'),
(925, 'Jorge Valdivia Toro', 'Acceso al Home del Administrador', '2026-11-09 03:15:57', 'Ingreso al panel principal', '181.42.139.89'),
(926, 'Maria Pinto', 'Acceso al Home del Supervisor', '2026-11-09 03:29:26', 'Ingreso al panel principal', '201.219.234.75'),
(927, 'Maria Pinto', 'Acceso al Home del Supervisor', '2026-11-09 03:31:30', 'Ingreso al panel principal', '201.219.234.75'),
(928, 'Jorge Valdivia Toro', 'Acceso al Home del Administrador', '2026-11-09 03:31:56', 'Ingreso al panel principal', '201.219.234.75'),
(929, 'Jorge Valdivia Toro', 'Acceso al Home del Administrador', '2026-11-09 03:36:55', 'Ingreso al panel principal', '201.219.234.75'),
(930, 'Maria Pinto', 'Acceso al Home del Supervisor', '2026-11-09 03:37:25', 'Ingreso al panel principal', '201.219.234.75'),
(931, 'Maria Pinto', 'Acceso al Home del Supervisor', '2026-11-09 03:38:04', 'Ingreso al panel principal', '201.219.234.75'),
(932, 'Maria Pinto', 'Acceso al Home del Supervisor', '2026-11-09 03:40:03', 'Ingreso al panel principal', '201.219.234.75'),
(933, 'Jorge Valdivia Toro', 'Acceso al Home del Administrador', '2026-11-09 03:40:21', 'Ingreso al panel principal', '201.219.234.75'),
(934, 'Jorge Valdivia Toro', 'Acceso al Home del Administrador', '2026-11-09 03:41:57', 'Ingreso al panel principal', '181.42.139.89'),
(935, 'Maria Pinto', 'Acceso al Home del Supervisor', '2026-11-09 03:43:14', 'Ingreso al panel principal', '201.219.234.75'),
(936, 'Jorge Valdivia Toro', 'Acceso al Home del Administrador', '2026-11-09 03:49:47', 'Ingreso al panel principal', '179.60.89.129'),
(937, 'Jorge Valdivia Toro', 'Acceso al Home del Administrador', '2026-11-09 03:50:10', 'Ingreso al panel principal', '179.60.89.129'),
(938, 'Jorge Valdivia Toro', 'Acceso al Home del Administrador', '2026-11-09 03:50:12', 'Ingreso al panel principal', '179.60.89.129'),
(939, 'Maria Pinto', 'Acceso al Home del Supervisor', '2026-11-09 03:50:28', 'Ingreso al panel principal', '179.60.89.129'),
(940, 'Maria Pinto', 'Acceso al Home del Supervisor', '2026-11-09 03:50:35', 'Ingreso al panel principal', '179.60.89.129'),
(941, 'Maria Pinto', 'Acceso al Home del Supervisor', '2026-11-09 03:50:51', 'Ingreso al panel principal', '179.60.89.129'),
(942, 'Maria Pinto', 'Acceso al Home del Supervisor', '2026-11-09 04:38:49', 'Ingreso al panel principal', '181.42.139.89'),
(943, 'Jorge Valdivia Toro', 'Acceso al Home del Administrador', '2026-11-09 05:14:50', 'Ingreso al panel principal', '179.238.35.206'),
(944, 'Jorge Valdivia Toro', 'Acceso al Home del Administrador', '2026-11-09 05:16:45', 'Ingreso al panel principal', '179.238.35.206'),
(945, 'Jorge Valdivia Toro', 'Acceso al Home del Administrador', '2026-11-09 06:00:46', 'Ingreso al panel principal', '179.238.35.206'),
(946, 'Jorge Valdivia Toro', 'Acceso al Home del Administrador', '2026-11-09 06:07:03', 'Ingreso al panel principal', '179.238.35.206'),
(947, 'Jorge Valdivia Toro', 'Acceso al Home del Administrador', '2026-11-09 06:07:29', 'Ingreso al panel principal', '179.238.35.206'),
(948, 'Jorge Valdivia Toro', 'Acceso al Home del Administrador', '2026-11-09 06:09:18', 'Ingreso al panel principal', '179.238.35.206'),
(949, 'Jorge Valdivia Toro', 'Acceso al Home del Administrador', '2026-11-09 06:09:36', 'Ingreso al panel principal', '179.238.35.206'),
(950, 'Jorge Valdivia Toro', 'Acceso al Home del Administrador', '2026-11-09 07:36:16', 'Ingreso al panel principal', '186.11.84.250'),
(951, 'Jorge Valdivia Toro', 'Acceso al Home del Administrador', '2026-11-09 07:36:43', 'Ingreso al panel principal', '186.11.84.250'),
(952, 'Maria Pinto', 'Acceso al Home del Supervisor', '2026-11-09 07:36:45', 'Ingreso al panel principal', '186.11.84.250'),
(953, 'Maria Pinto', 'Acceso al Home del Supervisor', '2026-11-09 07:37:44', 'Ingreso al panel principal', '186.11.84.250'),
(954, 'Jorge Valdivia Toro', 'Acceso al Home del Administrador', '2026-11-09 07:37:56', 'Ingreso al panel principal', '186.11.84.250'),
(955, 'Maria Pinto', 'Acceso al Home del Supervisor', '2026-11-09 07:37:58', 'Ingreso al panel principal', '186.11.84.250'),
(956, 'Jorge Valdivia Toro', 'Acceso al Home del Administrador', '2026-11-09 07:39:13', 'Ingreso al panel principal', '186.11.84.250'),
(957, 'Maria Pinto', 'Acceso al Home del Supervisor', '2026-11-09 07:39:15', 'Ingreso al panel principal', '186.11.84.250'),
(958, 'Maria Pinto', 'Acceso al Home del Supervisor', '2026-11-09 11:58:13', 'Ingreso al panel principal', '186.189.103.75'),
(959, 'Maria Pinto', 'Acceso al Home del Supervisor', '2026-11-09 11:59:50', 'Ingreso al panel principal', '186.189.103.75'),
(960, 'Maria Pinto', 'Acceso al Home del Supervisor', '2026-11-09 11:59:54', 'Ingreso al panel principal', '186.189.103.75'),
(961, 'Maria Pinto', 'Acceso al Home del Supervisor', '2026-11-09 11:59:56', 'Ingreso al panel principal', '186.189.103.75'),
(962, 'Maria Pinto', 'Acceso al Home del Supervisor', '2026-11-09 11:59:59', 'Ingreso al panel principal', '186.189.103.75'),
(963, 'Maria Pinto', 'Acceso al Home del Supervisor', '2026-11-09 12:00:08', 'Ingreso al panel principal', '186.189.103.75'),
(964, 'Maria Pinto', 'Acceso al Home del Supervisor', '2026-11-09 12:00:09', 'Ingreso al panel principal', '186.189.103.75'),
(965, 'Maria Pinto', 'Acceso al Home del Supervisor', '2026-11-09 12:00:11', 'Ingreso al panel principal', '186.189.103.75'),
(966, 'Maria Pinto', 'Acceso al Home del Supervisor', '2026-11-09 12:00:13', 'Ingreso al panel principal', '186.189.103.75'),
(967, 'Maria Pinto', 'Acceso al Home del Supervisor', '2026-11-09 12:00:16', 'Ingreso al panel principal', '186.189.103.75'),
(968, 'Maria Pinto', 'Acceso al Home del Supervisor', '2026-11-09 12:10:27', 'Ingreso al panel principal', '186.189.103.75'),
(969, 'Maria Pinto', 'Acceso al Home del Supervisor', '2026-11-09 12:14:49', 'Ingreso al panel principal', '186.189.103.75'),
(970, 'Maria Pinto', 'Acceso al Home del Supervisor', '2026-11-09 12:14:58', 'Ingreso al panel principal', '186.189.103.75'),
(971, 'Maria Pinto', 'Acceso al Home del Supervisor', '2026-11-09 12:28:04', 'Ingreso al panel principal', '186.189.103.75'),
(972, 'Maria Pinto', 'Acceso al Home del Supervisor', '2026-11-09 12:28:07', 'Ingreso al panel principal', '186.189.103.75'),
(973, 'Maria Pinto', 'Acceso al Home del Supervisor', '2026-11-09 12:28:09', 'Ingreso al panel principal', '186.189.103.75'),
(974, 'Maria Pinto', 'Acceso al Home del Supervisor', '2026-11-09 12:28:11', 'Ingreso al panel principal', '186.189.103.75'),
(975, 'Maria Pinto', 'Acceso al Home del Supervisor', '2026-11-09 12:28:13', 'Ingreso al panel principal', '186.189.103.75'),
(976, 'Maria Pinto', 'Acceso al Home del Supervisor', '2026-11-09 12:33:27', 'Ingreso al panel principal', '186.189.103.75'),
(977, 'Maria Pinto', 'Acceso al Home del Supervisor', '2026-11-09 12:33:29', 'Ingreso al panel principal', '186.189.103.75'),
(978, 'Maria Pinto', 'Acceso al Home del Supervisor', '2026-11-09 12:33:31', 'Ingreso al panel principal', '186.189.103.75'),
(979, 'Maria Pinto', 'Acceso al Home del Supervisor', '2026-11-09 12:33:33', 'Ingreso al panel principal', '186.189.103.75'),
(980, 'Maria Pinto', 'Acceso al Home del Supervisor', '2026-11-09 12:33:34', 'Ingreso al panel principal', '186.189.103.75'),
(981, 'Maria Pinto', 'Acceso al Home del Supervisor', '2026-11-09 12:33:36', 'Ingreso al panel principal', '186.189.103.75'),
(982, 'Maria Pinto', 'Acceso al Home del Supervisor', '2026-11-09 12:36:09', 'Ingreso al panel principal', '186.189.103.75'),
(983, 'Maria Pinto', 'Acceso al Home del Supervisor', '2026-11-09 12:36:12', 'Ingreso al panel principal', '186.189.103.75'),
(984, 'Maria Pinto', 'Acceso al Home del Supervisor', '2026-11-09 12:36:13', 'Ingreso al panel principal', '186.189.103.75'),
(985, 'Maria Pinto', 'Acceso al Home del Supervisor', '2026-11-09 12:36:15', 'Ingreso al panel principal', '186.189.103.75'),
(986, 'Maria Pinto', 'Acceso al Home del Supervisor', '2026-11-09 12:36:17', 'Ingreso al panel principal', '186.189.103.75'),
(987, 'Maria Pinto', 'Acceso al Home del Supervisor', '2026-11-09 12:36:19', 'Ingreso al panel principal', '186.189.103.75'),
(988, 'Maria Pinto', 'Acceso al Home del Supervisor', '2026-11-09 12:36:51', 'Ingreso al panel principal', '186.189.103.75'),
(989, 'Maria Pinto', 'Acceso al Home del Supervisor', '2026-11-09 12:36:55', 'Ingreso al panel principal', '186.189.103.75'),
(990, 'Maria Pinto', 'Acceso al Home del Supervisor', '2026-11-09 12:36:57', 'Ingreso al panel principal', '186.189.103.75'),
(991, 'Maria Pinto', 'Acceso al Home del Supervisor', '2026-11-09 12:36:59', 'Ingreso al panel principal', '186.189.103.75'),
(992, 'Maria Pinto', 'Acceso al Home del Supervisor', '2026-11-09 12:37:01', 'Ingreso al panel principal', '186.189.103.75'),
(993, 'Maria Pinto', 'Acceso al Home del Supervisor', '2026-11-09 12:37:02', 'Ingreso al panel principal', '186.189.103.75'),
(994, 'Maria Pinto', 'Acceso al Home del Supervisor', '2026-11-09 12:37:41', 'Ingreso al panel principal', '186.189.103.75'),
(995, 'Maria Pinto', 'Acceso al Home del Supervisor', '2026-11-09 12:37:46', 'Ingreso al panel principal', '186.189.103.75'),
(996, 'Maria Pinto', 'Acceso al Home del Supervisor', '2026-11-09 12:37:49', 'Ingreso al panel principal', '186.189.103.75'),
(997, 'Maria Pinto', 'Acceso al Home del Supervisor', '2026-11-09 12:37:51', 'Ingreso al panel principal', '186.189.103.75'),
(998, 'Maria Pinto', 'Acceso al Home del Supervisor', '2026-11-09 12:37:53', 'Ingreso al panel principal', '186.189.103.75'),
(999, 'Maria Pinto', 'Acceso al Home del Supervisor', '2026-11-09 12:37:55', 'Ingreso al panel principal', '186.189.103.75'),
(1000, 'Maria Pinto', 'Acceso al Home del Supervisor', '2026-11-09 12:56:35', 'Ingreso al panel principal', '186.189.103.75'),
(1001, 'Jorge Valdivia Toro', 'Acceso al Home del Administrador', '2026-11-09 12:57:17', 'Ingreso al panel principal', '186.189.103.75'),
(1002, 'Jorge Valdivia Toro', 'Acceso al Home del Administrador', '2026-11-09 13:00:59', 'Ingreso al panel principal', '186.189.103.75'),
(1003, 'Maria Pinto', 'Acceso al Home del Supervisor', '2026-11-09 13:01:46', 'Ingreso al panel principal', '186.189.103.75'),
(1004, 'Jorge Valdivia Toro', 'Acceso al Home del Administrador', '2026-11-09 13:02:31', 'Ingreso al panel principal', '186.189.103.75'),
(1005, 'Jorge Valdivia Toro', 'Acceso al Home del Administrador', '2026-11-09 13:23:28', 'Ingreso al panel principal', '186.189.103.75'),
(1006, 'Jorge Valdivia Toro', 'Acceso al Home del Administrador', '2026-11-09 13:24:07', 'Ingreso al panel principal', '186.189.103.75'),
(1007, 'Maria Pinto', 'Acceso al Home del Supervisor', '2026-11-09 13:31:09', 'Ingreso al panel principal', '186.189.103.75'),
(1008, 'Maria Pinto', 'Acceso al Home del Supervisor', '2026-11-09 14:08:10', 'Ingreso al panel principal', '186.189.103.75'),
(1009, 'Maria Pinto', 'Acceso al Home del Supervisor', '2026-11-09 14:08:19', 'Ingreso al panel principal', '186.189.103.75'),
(1010, 'Jorge Valdivia Toro', 'Acceso al Home del Administrador', '2026-11-09 14:10:19', 'Ingreso al panel principal', '179.60.92.241'),
(1011, 'Jorge Valdivia Toro', 'Acceso al Home del Administrador', '2026-11-09 14:10:32', 'Ingreso al panel principal', '179.60.92.241'),
(1012, 'Jorge Valdivia Toro', 'Acceso al Home del Administrador', '2026-11-09 14:11:03', 'Ingreso al panel principal', '179.60.92.241'),
(1013, 'Jorge Valdivia Toro', 'Acceso al Home del Administrador', '2026-11-09 14:13:02', 'Ingreso al panel principal', '179.60.92.241'),
(1014, 'Jorge Valdivia Toro', 'Acceso al Home del Administrador', '2026-11-09 14:13:02', 'Ingreso al panel principal', '179.238.35.206'),
(1015, 'Jorge Valdivia Toro', 'Acceso al Home del Administrador', '2026-11-09 14:13:19', 'Ingreso al panel principal', '179.238.35.206'),
(1016, 'Jorge Valdivia Toro', 'Acceso al Home del Administrador', '2026-11-09 14:13:21', 'Ingreso al panel principal', '179.60.92.241'),
(1017, 'Maria Pinto', 'Acceso al Home del Supervisor', '2026-11-09 14:13:32', 'Ingreso al panel principal', '186.189.103.75'),
(1018, 'Jorge Valdivia Toro', 'Acceso al Home del Administrador', '2026-11-09 14:14:13', 'Ingreso al panel principal', '179.60.89.64'),
(1019, 'Jorge Valdivia Toro', 'Acceso al Home del Administrador', '2026-11-09 14:14:26', 'Ingreso al panel principal', '179.60.89.64'),
(1020, 'Jorge Valdivia Toro', 'Acceso al Home del Administrador', '2026-11-09 14:15:39', 'Ingreso al panel principal', '179.60.89.64'),
(1021, 'Jorge Valdivia Toro', 'Acceso al Home del Administrador', '2026-11-09 14:15:51', 'Ingreso al panel principal', '179.60.89.64'),
(1022, 'Jorge Valdivia Toro', 'Acceso al Home del Administrador', '2026-11-09 14:20:27', 'Ingreso al panel principal', '179.238.35.206'),
(1023, 'Jorge Valdivia Toro', 'Acceso al Home del Administrador', '2026-11-09 14:23:24', 'Ingreso al panel principal', '179.238.35.206'),
(1024, 'Jorge Valdivia Toro', 'Acceso al Home del Administrador', '2026-11-09 14:23:31', 'Ingreso al panel principal', '186.189.103.75'),
(1025, 'Jorge Valdivia Toro', 'Acceso al Home del Administrador', '2026-11-09 14:35:35', 'Ingreso al panel principal', '186.189.103.75'),
(1026, 'Jorge Valdivia Toro', 'Acceso al Home del Administrador', '2026-11-09 14:51:24', 'Ingreso al panel principal', '186.189.103.75'),
(1027, 'Jorge Valdivia Toro', 'Acceso al Home del Administrador', '2026-11-09 14:52:08', 'Ingreso al panel principal', '186.189.103.75'),
(1028, 'Jorge Valdivia Toro', 'Acceso al Home del Administrador', '2026-11-09 15:02:12', 'Ingreso al panel principal', '186.189.103.75'),
(1029, 'Jorge Valdivia Toro', 'Acceso al Home del Administrador', '2026-11-09 15:02:28', 'Ingreso al panel principal', '186.189.103.75'),
(1030, 'Jorge Valdivia Toro', 'Acceso al Home del Administrador', '2026-11-09 16:45:31', 'Ingreso al panel principal', '179.60.92.241'),
(1031, 'Jorge Valdivia Toro', 'Acceso al Home del Administrador', '2026-11-09 20:24:49', 'Ingreso al panel principal', '191.116.4.219'),
(1032, 'Jorge Valdivia Toro', 'Acceso al Home del Administrador', '2026-11-09 20:31:15', 'Ingreso al panel principal', '191.116.4.219'),
(1033, 'Jorge Valdivia Toro', 'Acceso al Home del Administrador', '2026-11-09 20:46:12', 'Ingreso al panel principal', '191.116.4.219'),
(1034, 'Jorge Valdivia Toro', 'Acceso al Home del Administrador', '2026-11-09 20:48:02', 'Ingreso al panel principal', '191.116.4.219'),
(1035, 'Jorge Valdivia Toro', 'Acceso al Home del Administrador', '2026-11-09 21:10:57', 'Ingreso al panel principal', '191.116.4.219'),
(1036, 'Jorge Valdivia Toro', 'Acceso al Home del Administrador', '2026-11-09 22:53:57', 'Ingreso al panel principal', '191.116.4.219'),
(1037, 'Jorge Valdivia Toro', 'Acceso al Home del Administrador', '2026-11-09 23:03:37', 'Ingreso al panel principal', '191.116.4.219'),
(1038, 'Jorge Valdivia Toro', 'Acceso al Home del Administrador', '2026-11-09 23:05:21', 'Ingreso al panel principal', '191.116.4.219'),
(1039, 'Jorge Valdivia Toro', 'Acceso al Home del Administrador', '2026-11-09 23:23:35', 'Ingreso al panel principal', '191.116.4.219'),
(1040, 'Jorge Valdivia Toro', 'Acceso al Home del Administrador', '2026-11-09 23:27:13', 'Ingreso al panel principal', '191.116.4.219'),
(1041, 'Jorge Valdivia Toro', 'Acceso al Home del Administrador', '2026-11-09 23:28:00', 'Ingreso al panel principal', '191.116.4.219'),
(1042, 'Jorge Valdivia Toro', 'Acceso al Home del Administrador', '2026-11-09 23:28:03', 'Ingreso al panel principal', '191.116.4.219'),
(1043, 'Jorge Valdivia Toro', 'Acceso al Home del Administrador', '2026-11-09 23:29:12', 'Ingreso al panel principal', '191.116.4.219'),
(1044, 'Jorge Valdivia Toro', 'Acceso al Home del Administrador', '2026-11-09 23:29:42', 'Ingreso al panel principal', '191.116.4.219'),
(1045, 'Maria Pinto', 'Acceso al Home del Supervisor', '2026-11-10 13:55:30', 'Ingreso al panel principal', '186.189.76.33'),
(1046, 'Jorge Valdivia Toro', 'Acceso al Home del Administrador', '2026-11-10 20:44:16', 'Ingreso al panel principal', '186.175.244.43'),
(1047, 'Jorge Valdivia Toro', 'Acceso al Home del Administrador', '2026-11-10 20:44:43', 'Ingreso al panel principal', '186.175.244.43'),
(1048, 'Jorge Valdivia Toro', 'Acceso al Home del Administrador', '2026-11-10 20:44:47', 'Ingreso al panel principal', '186.175.244.43'),
(1049, 'Jorge Valdivia Toro', 'Acceso al Home del Administrador', '2026-11-10 20:45:01', 'Ingreso al panel principal', '186.175.244.43'),
(1050, 'Jorge Valdivia Toro', 'Acceso al Home del Administrador', '2026-11-10 20:45:21', 'Ingreso al panel principal', '186.175.244.43'),
(1051, 'Jorge Valdivia Toro', 'Acceso al Home del Administrador', '2026-11-10 20:46:12', 'Ingreso al panel principal', '186.175.244.43'),
(1052, 'Jorge Valdivia Toro', 'Acceso al Home del Administrador', '2026-11-10 20:46:16', 'Ingreso al panel principal', '186.175.244.43'),
(1053, 'Jorge Valdivia Toro', 'Acceso al Home del Administrador', '2026-11-10 20:46:19', 'Ingreso al panel principal', '186.175.244.43'),
(1054, 'Maria Pinto', 'Acceso al Home del Supervisor', '2026-11-10 20:49:13', 'Ingreso al panel principal', '186.189.95.173'),
(1055, 'Jorge Valdivia Toro', 'Acceso al Home del Administrador', '2026-11-10 20:49:50', 'Ingreso al panel principal', '191.116.4.219'),
(1056, 'Jorge Valdivia Toro', 'Acceso al Home del Administrador', '2026-11-10 20:51:48', 'Ingreso al panel principal', '191.116.4.219'),
(1057, 'Jorge Valdivia Toro', 'Acceso al Home del Administrador', '2026-11-10 20:51:52', 'Ingreso al panel principal', '186.174.172.223'),
(1058, 'Jorge Valdivia Toro', 'Acceso al Home del Administrador', '2026-11-10 20:52:08', 'Ingreso al panel principal', '191.116.4.219'),
(1059, 'Jorge Valdivia Toro', 'Acceso al Home del Administrador', '2026-11-10 20:52:36', 'Ingreso al panel principal', '191.116.4.219'),
(1060, 'Jorge Valdivia Toro', 'Acceso al Home del Administrador', '2026-11-10 20:52:59', 'Ingreso al panel principal', '186.174.172.223'),
(1061, 'Jorge Valdivia Toro', 'Acceso al Home del Administrador', '2026-11-10 20:53:24', 'Ingreso al panel principal', '191.116.4.219'),
(1062, 'Jorge Valdivia Toro', 'Acceso al Home del Administrador', '2026-11-10 20:54:01', 'Ingreso al panel principal', '191.116.4.219'),
(1063, 'Jorge Valdivia Toro', 'Acceso al Home del Administrador', '2026-11-10 20:54:23', 'Ingreso al panel principal', '191.116.4.219'),
(1064, 'Jorge Valdivia Toro', 'Acceso al Home del Administrador', '2026-11-10 20:55:30', 'Ingreso al panel principal', '191.116.4.219'),
(1065, 'Maria Pinto', 'Acceso al Home del Supervisor', '2026-11-10 20:57:57', 'Ingreso al panel principal', '191.116.4.219'),
(1066, 'Maria Pinto', 'Acceso al Home del Supervisor', '2026-11-10 20:58:23', 'Ingreso al panel principal', '191.116.4.219'),
(1067, 'Maria Pinto', 'Acceso al Home del Supervisor', '2026-11-10 20:58:46', 'Ingreso al panel principal', '191.116.4.219'),
(1068, 'Maria Pinto', 'Acceso al Home del Supervisor', '2026-11-10 20:59:43', 'Ingreso al panel principal', '191.116.4.219'),
(1069, 'Jorge Valdivia Toro', 'Acceso al Home del Administrador', '2026-11-10 21:14:33', 'Ingreso al panel principal', '191.116.4.219'),
(1070, 'Jorge Valdivia Toro', 'Acceso al Home del Administrador', '2026-11-10 21:22:09', 'Ingreso al panel principal', '191.116.4.219'),
(1071, 'Jorge Valdivia Toro', 'Acceso al Home del Administrador', '2026-11-10 21:41:38', 'Ingreso al panel principal', '191.116.4.219'),
(1072, 'Josimar José Évora Díaz', 'Acceso al Home del Administrador', '2026-11-10 21:44:55', 'Ingreso al panel principal', '191.116.4.219'),
(1073, 'Josimar José Évora Díaz', 'Acceso al Home del Administrador', '2026-11-10 21:46:09', 'Ingreso al panel principal', '191.116.4.219'),
(1074, 'Maria Pinto', 'Acceso al Home del Supervisor', '2026-11-10 21:56:48', 'Ingreso al panel principal', '191.116.4.219'),
(1075, 'Maria Pinto', 'Acceso al Home del Supervisor', '2026-11-10 21:57:54', 'Ingreso al panel principal', '191.116.4.219'),
(1076, 'Maria Pinto', 'Acceso al Home del Supervisor', '2026-11-10 21:58:25', 'Ingreso al panel principal', '191.116.4.219'),
(1077, 'Maria Pinto', 'Acceso al Home del Supervisor', '2026-11-10 21:58:47', 'Ingreso al panel principal', '191.116.4.219'),
(1078, 'Maria Pinto', 'Acceso al Home del Supervisor', '2026-11-10 22:02:18', 'Ingreso al panel principal', '191.116.4.219'),
(1079, 'Maria Pinto', 'Acceso al Home del Supervisor', '2026-11-10 22:03:05', 'Ingreso al panel principal', '191.116.4.219'),
(1080, 'Jorge Valdivia Toro', 'Acceso al Home del Administrador', '2026-11-12 19:24:59', 'Ingreso al panel principal', '191.116.4.219'),
(1081, 'Maria Pinto', 'Acceso al Home del Supervisor', '2026-11-12 19:26:26', 'Ingreso al panel principal', '191.116.4.219'),
(1082, 'Jorge Valdivia Toro', 'Acceso al Home del Administrador', '2026-11-12 19:53:55', 'Ingreso al panel principal', '191.116.4.219'),
(1083, 'Maria Pinto', 'Acceso al Home del Supervisor', '2026-11-12 20:00:43', 'Ingreso al panel principal', '191.116.4.219'),
(1084, 'Jorge Valdivia Toro', 'Acceso al Home del Administrador', '2026-11-12 20:02:34', 'Ingreso al panel principal', '191.116.4.219'),
(1085, 'Jorge Valdivia Toro', 'Acceso al Home del Administrador', '2026-11-12 20:17:40', 'Ingreso al panel principal', '191.116.4.219'),
(1086, 'Maria Pinto', 'Acceso al Home del Supervisor', '2026-11-12 20:27:29', 'Ingreso al panel principal', '191.116.4.219'),
(1087, 'Jorge Valdivia Toro', 'Acceso al Home del Administrador', '2026-11-12 20:28:55', 'Ingreso al panel principal', '191.116.4.219'),
(1088, 'Jorge Valdivia Toro', 'Acceso al Home del Administrador', '2026-11-12 20:29:27', 'Ingreso al panel principal', '191.116.4.219'),
(1089, 'Maria Pinto', 'Acceso al Home del Supervisor', '2026-11-12 20:30:06', 'Ingreso al panel principal', '191.116.4.219'),
(1090, 'Maria Pinto', 'Acceso al Home del Supervisor', '2026-11-12 20:30:14', 'Ingreso al panel principal', '191.116.4.219'),
(1091, 'Maria Pinto', 'Acceso al Home del Supervisor', '2026-11-12 20:31:56', 'Ingreso al panel principal', '191.116.4.219'),
(1092, 'Maria Pinto', 'Acceso al Home del Supervisor', '2026-11-12 20:40:30', 'Ingreso al panel principal', '191.116.4.219'),
(1093, 'Maria Pinto', 'Acceso al Home del Supervisor', '2026-11-12 21:00:13', 'Ingreso al panel principal', '191.116.4.219'),
(1094, 'Maria Pinto', 'Acceso al Home del Supervisor', '2026-11-12 21:10:02', 'Ingreso al panel principal', '191.116.4.219'),
(1095, 'Jorge Valdivia Toro', 'Acceso al Home del Administrador', '2026-11-12 22:54:44', 'Ingreso al panel principal', '181.42.243.161'),
(1096, 'Jorge Valdivia Toro', 'Acceso al Home del Administrador', '2026-11-12 22:55:12', 'Ingreso al panel principal', '181.42.243.161'),
(1097, 'Jorge Valdivia Toro', 'Acceso al Home del Administrador', '2026-11-12 22:59:39', 'Ingreso al panel principal', '181.42.243.161'),
(1098, 'Jorge Valdivia Toro', 'Acceso al Home del Administrador', '2026-11-12 23:00:03', 'Ingreso al panel principal', '181.42.243.161'),
(1099, 'Jorge Valdivia Toro', 'Acceso al Home del Administrador', '2026-11-13 13:40:02', 'Ingreso al panel principal', '190.5.37.26'),
(1100, 'Jorge Valdivia Toro', 'Acceso al Home del Administrador', '2026-11-13 13:44:52', 'Ingreso al panel principal', '190.5.37.26');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `Medio_pago`
--

CREATE TABLE `Medio_pago` (
  `id_medio_pago` int(11) NOT NULL,
  `nombre_medio` varchar(45) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `Medio_pago`
--

INSERT INTO `Medio_pago` (`id_medio_pago`, `nombre_medio`) VALUES
(1, 'Efectivo'),
(2, 'Débito'),
(3, 'Crédito'),
(4, 'Transferencia');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `Productos`
--

CREATE TABLE `Productos` (
  `id_producto` int(11) NOT NULL,
  `id_categorias` int(11) NOT NULL,
  `nombre` varchar(255) NOT NULL,
  `descripcion` text NOT NULL,
  `precio` int(11) NOT NULL,
  `stock` int(11) NOT NULL,
  `imagen` varchar(255) DEFAULT NULL,
  `codigo` varchar(50) DEFAULT NULL,
  `activo` tinyint(1) NOT NULL DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `Productos`
--

INSERT INTO `Productos` (`id_producto`, `id_categorias`, `nombre`, `descripcion`, `precio`, `stock`, `imagen`, `codigo`, `activo`) VALUES
(62, 1, 'Samsung S26 256GB Negro/Blanco', 'El Samsung Galaxy S26 de 256 GB en color negro y blanco es un smartphone de gama alta con pantalla Dynamic AMOLED 2X de 6.3\" FHD+, 12 GB de RAM y procesador Exynos 2600.\r\nPantalla: 6.3\" Dynamic AMOLED 2X con resolución FHD+ (2340 x 1080).\r\nProcesador: Exynos 2600 deca-core (hasta 3.8 GHz).\r\nMemoria RAM: 12 GB.Almacenamiento interno: 256 GB (sin expansión por tarjeta microSD).\r\nCámaras traseras: Triple sistema de 50 MP (principal) + 10 MP (teleobjetivo 3x) + 12 MP (ultra gran angular).\r\nCámara frontal: 12 MP.Batería: 4300 mAh con soporte para carga rápida e inalámbrica.\r\nSistema operativo: Android.Conectividad: 5G, Wi-Fi, Bluetooth 5.4, NFC y USB-C.\r\nResistencia: Protección contra agua y polvo, diseño delgado de vidrio y aluminio.', 700010, 8, 'img/62.jpg', 'BEB-003', 1),
(63, 1, 'Celular Moto 70 Fusión 5G 256 GB 6,78\"', 'Eleva tu experiencia móvil con el nuevo Motorola Edge 70 Fusion. Diseñado para quienes no aceptan compromisos, este smartphone combina un rendimiento excepcional con un diseño sofisticado.', 33222, 14, 'img/63.jpg', 'PLU: 29479976 - COD: 656686', 0),
(64, 1, 'Notebook Asus TUF Gaming F16', 'es uncomputadora portátil de gama media orientada ajuego de azary tareas exigentes con pantalla amplia de 16 pulgadas\r\n\r\nProcesador: Intel Core 5 (8 núcleos, 12 hilos, velocidad máxima de 4,8 GHz).\r\nTarjeta Gráfica: NVIDIA GeForce RTX 3050 con 6 GB de memoria dedicada VRAM (GDDR6).\r\nMemoria RAM: 8 GB DDR5 (ampliable hasta 64 GB).\r\nAlmacenamiento: 512 GB de disco sólido M.2 NVMe SSD (ampliable hasta 2 TB).', 820001, 4, 'img/64.jpg', 'BEB-004', 0),
(67, 3, 'Lavadora Secadora WD3S1443BT2 14 Kg / 9 Hisense', 'La Lavadora-Secadora Hisense WD3S1443BT2 es un electrodoméstico de carga frontal con capacidad de 14 kg para lavado y 9 kg para secado, ideal para familias numerosas.\r\n\r\nCapacidad: 14 kg (lavado) / 9 kg (secado).\r\nCentrifugado: Velocidad máxima de 1400 rpm.\r\nSistema de secado: Secado por condensación (no requiere salida de aire al exterior).\r\nProgramas: 16 programas preestablecidos para diferentes tipos de tela y necesidades.\r\nConectividad: Wi-Fi integrado para control y gestión de ciclos mediante smartphone..............................................................................................', 250000, 49, 'uploads/1793919151_images (1).jpeg', 'BEB-005', 0),
(68, 1, 'Consola PlayStation®5 Slim Mate + 2 Juegos (Gran Turismo 7 + Astro Bot)', 'Lleva tu experiencia de juego al siguiente nivel con este paquete de la PlayStation 5 Edición Digital (Slim), que incluye una consola de 1 TB de almacenamiento y dos títulos aclamados por la crítica: Astro Bot y Gran Turismo 7.', 699999, 3, 'img/14.avif', 'SKU: 1EN06686', 0),
(73, 1, 'ASUS Gaming V16 (modelo V3607VU-RP222W)', 'Procesador (CPU): Intel Core 7 240H (10 núcleos, 16 hilos, velocidad turbo de hasta 5.2 GHz)\r\n\r\nTarjeta Gráfica (GPU): NVIDIA GeForce RTX 4050 con 6GB de memoria GDDR6 (soporta DLSS 3 y Ray Tracing)\r\n\r\nMemoria RAM: 16GB DDR5 SO-DIMM (ampliable según ranuras)\r\n\r\nAlmacenamiento: 512GB SSD M.2 NVMe PCIe 4.0\r\n\r\nSistema Operativo: Windows 11 Home', 100000, 0, 'img/45.jpg', 'BEB-020', 0),
(74, 1, 'LG | Smart TV 75 LG NANO 4K UHD AI NU85 2026 + Magic Remote LG', 'El Smart TV 75\" LG NANO 4K UHD AI NU85 2026 (modelo 75NU855BPSA) es un televisor de gran formato que combina tecnología NanoCell con funciones avanzadas de inteligencia artificial\r\n\r\nTamaño de 75 pulgadas con resolución 4K UHD (3840 x 2160)\r\n\r\nTecnología Nano Detail Enhancer que mejora la textura, el color y la profundidad.\r\n\r\nProcesador α7 AI Processor 4K Gen9 con funciones de 4K Super Upscaling y Dynamic Tone Mapping.\r\n\r\nSistema de audio virtual de 9.1.2 canales para un efecto envolvente multidireccional', 700000, 29, NULL, 'BEB-021', 0),
(76, 3, 'Refrigerador Btm 259 Lts Frio Directo Mdrb369Fge50', 'Capacidad total: 259 a 262 litros (aprox. 197 litros en el refrigerador y 62 a 65 litros en el freezer)\r\n\r\nDimensiones: 177.3 cm de alto, 54.7 cm de ancho y 56.8 cm de profundidad.\r\n\r\nPeso: 44.5 kg.\r\n\r\nSistema de frío: Frío directo con función Super Frost para congelamiento rápido.', 719, 19, 'uploads/76.jpg', 'BEB-034', 1),
(79, 1, 'Notebook Gamer HP 15-FB3009LA AMD Ryzen 5 8 GB RAM 512 GB 15,6\" NVIDIA GeForce RTX 3050', '¡Prepárate para ganar con la Victus 15! Diseñada para gamers que quieren entrar al mundo de las PCs de alto rendimiento, esta laptop lo tiene todo: el poder del procesador AMD, gráficos de última generación y las herramientas de personalización de OMEN Gaming Hub para optimizar cada partida. Todo esto en un diseño compacto y elegante de 15\"', 829990, 12, 'uploads/79.jpg', 'COD: 640934', 1),
(80, 1, 'Celular Xiaomi 17T 5G 256 GB 6,59\" Negr', 'El Xiaomi 17T es la puerta de entrada a una experiencia fotográfica de nivel profesional, donde cada imagen cobra vida con la magia de Leica, invitándote a explorar y capturar el mundo con una claridad y detalle que antes solo soñabas; su potente batería te brinda la libertad de vivir sin ataduras, mientras que su diseño compacto y elegante se convierte en una extensión de tu estilo, listo para acompañarte en cada aventura, desde los momentos más cotidianos hasta los más extraordinarios.', 529990, 0, 'img/12.jpg', 'COD: 657797', 1),
(82, 1, 'Samsung Galaxy S24 Ultra 256GB', 'Smartphone de gama alta con pantalla Dynamic AMOLED 2X de 6.8 pulgadas, procesador Snapdragon de última generación y cámara principal de 200 MP.', 990, 0, 'uploads/82.jpg', 'CEL-SAM-S24', 1),
(92, 1, 'Mini Pc Minisforum X1 Lite Ryzen 7 255', 'Mini PC Barebone personalizable para sus necesidadesEste mini PC como un descartado versión, sin RAM, almacenamiento o sistema operativo, lo que le permite personalizar completamente el sistema a sus necesidades. Perfecto para los usuarios que prefieren seleccionar su propia memoria, SSD y instalar el sistema operativo de su elección. Por favor, asegúrese de que los componentes necesarios estén instalados antes de encender el dispositivo. Fácil de actualizar y altamente personalizable para una experiencia informática sintonizada.', 300000, 10, 'uploads/92.jpg', '001', 1);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `recuperacion_tokens`
--

CREATE TABLE `recuperacion_tokens` (
  `id_recuperacion` int(11) NOT NULL,
  `correo` varchar(100) NOT NULL,
  `token` varchar(64) NOT NULL,
  `expira_en` datetime NOT NULL,
  `usado` tinyint(1) NOT NULL DEFAULT 0,
  `creado_el` datetime NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `recuperacion_tokens`
--

INSERT INTO `recuperacion_tokens` (`id_recuperacion`, `correo`, `token`, `expira_en`, `usado`, `creado_el`) VALUES
(1, 'eduardo.brizuela.santis@gmail.com', 'e10259de26ff0b95fe9bb0140700d1ebf755748b0be6cccdfcc03e0a1a835243', '2026-09-29 00:28:02', 1, '2026-09-28 23:58:02'),
(2, 'eduardo.brizuela.santis@gmail.com', '544c1bdbadec74815f651a18d49018c407a8953b44f54a0523a867f43bf9227a', '2026-09-29 00:28:16', 1, '2026-09-28 23:58:16'),
(3, 'eduardo.brizuela.santis@gmail.com', '6835dde778ef9f33096ce7ec4229a1b40ab5d2032b74a987c17ead05d8e78f6b', '2026-09-29 00:30:44', 1, '2026-09-29 00:00:44'),
(4, 'eduardo.brizuela.santis@gmail.com', '1c7fc26aed6ece2a9211c96a851bdfb5bb1bd22c2fc92b73b025bd84748746c2', '2026-09-29 00:31:10', 1, '2026-09-29 00:01:10'),
(5, 'eduardo.brizuela.santis@gmail.com', '0035f9e068611dd5e0a946cfa454c2df6fa55a50bcb64eeb56da96880d877873', '2026-09-29 00:31:53', 0, '2026-09-29 00:01:53'),
(6, 'belichima@hotmail.com', '853f11dc2c37fdb5a6178682cd28a2dba3ec940ee6b77bab7bf7b8f4a206a414', '2026-09-29 18:28:12', 1, '2026-09-29 17:58:12'),
(7, 'belichima@hotmail.com', 'a7b95b0d00ceb130407577bfc08b851da794cbbab088fa55f54b30fba92f3714', '2026-09-29 19:54:56', 1, '2026-09-29 19:24:56'),
(8, 'belichima@hotmail.com', '87d5e9e80c483028cc8b1c1c2b80f9e0fb6fa6806347afa799cf9f00db896d71', '2026-09-29 20:28:24', 1, '2026-09-29 19:58:24'),
(9, 'belichima@hotmail.com', '09e5d87333abac0d670ae63f814d87e63b566cadcccb9782d89d14160a29f6ee', '2026-09-29 20:28:45', 1, '2026-09-29 19:58:45'),
(10, 'belichima@hotmail.com', '24fe6d6a1ce12f1b1772ad5f6fce62b3d09caff1521aaa8c4bacd9c781cd3281', '2026-09-29 20:29:13', 1, '2026-09-29 19:59:13'),
(11, 'belichima@hotmail.com', '96a573d1bfe3faf46b6d7852dc9c7387a5e398ea4859acd14856bfd027e5949b', '2026-09-29 20:37:59', 1, '2026-09-29 20:07:59'),
(12, 'belichima@hotmail.com', '070324d8da4eebdb49880b0aaf024bf2d9c5ad3ccd165a728783e5f26ee7bf8e', '2026-09-29 20:38:03', 1, '2026-09-29 20:08:03'),
(13, 'belichima@hotmail.com', 'c4f71e1f7acc1e348c99bb4af15f2f4a6af9a16d993a4103d48e15f21b0ac247', '2026-09-29 20:40:02', 1, '2026-09-29 20:10:02'),
(14, 'belichima@hotmail.com', '3ebfd4165e2e123f6da059ac8878cacb42b59084886d19c2dc7987a23b536b2b', '2026-09-29 20:40:16', 1, '2026-09-29 20:10:16'),
(15, 'belichima@hotmail.com', 'c79adbbff49b417ebf983812ba76cbf8d464446bed900f24ca3f0e999a5bf1aa', '2026-09-29 22:20:51', 1, '2026-09-29 21:50:51'),
(16, 'tgaete25@cftsa.cl', '8b52d1169d683209649595c9ca5e011a945951fe5d7d17862c6a75197480c190', '2026-09-29 22:25:00', 0, '2026-09-29 21:55:00'),
(17, 'tomas.gaete03@gmail.com', '3f4037dc5d3b0f84e42b64638b6ad33d8b109db01b7b77d84b3b3b374843a152', '2026-11-05 21:00:52', 1, '2026-11-05 20:30:52'),
(18, 'tomas.gaete03@gmail.com', 'cde1eaabe000f2c03214d7f900797e046c9b098a29a491377710e32e52e6eaa2', '2026-11-06 22:16:28', 1, '2026-11-06 21:46:28'),
(19, 'tomas.gaete03@gmail.com', '8664ad068398638616e6d0fb38964df779738916e56566a9c31581f629089e6b', '2026-11-06 22:17:04', 1, '2026-11-06 21:47:04'),
(20, 'tomas.gaete03@gmail.com', '4a6e4b6fb1040007913c88f243847d3d826fb8ef6e98f6f07036c99aa6342064', '2026-11-06 22:17:47', 1, '2026-11-06 21:47:47'),
(21, 'supervisor@sistemaventas.cl', 'd5e1695af43bff8d38fa8212d44ae62d6e2604945dec822a75a35eb70516827f', '2026-11-07 16:44:53', 0, '2026-11-07 16:14:53'),
(22, 'administrador@sistemaventas.cl', 'edbddef752b20bd5d08e03e87e2acaed44c30b0ccf4741a082447ee5cfd88fee', '2026-11-07 16:46:19', 1, '2026-11-07 16:16:19'),
(23, 'vendedor@sistemaventas.cl', '92b116615f4172feac952cf3f454527603b124487735ae478749335133c3ec1b', '2026-11-07 16:46:35', 0, '2026-11-07 16:16:35'),
(24, 'administrador@sistemaventas.cl', 'ee9fad4f694bfa817d1bea78529dbc253bf26ae3c00486e92b5e776834571872', '2026-11-08 15:34:21', 0, '2026-11-08 15:04:21'),
(25, 'alvaro.gonzalezh2018@gmail.com', 'dd127309067f649c312bc8fd8dcb12a6456f61121e436a05b4920aaa2ee95e59', '2026-11-08 22:19:01', 1, '2026-11-08 21:49:01'),
(26, 'alvaro.gonzalezh2018@gmail.com', '8c68e04344d5a3bef94e0e02c6e533ef5c516b558461e17f599ede82bc0249d6', '2026-11-08 22:28:09', 1, '2026-11-08 21:58:09'),
(27, 'alvaro.gonzalezh2018@gmail.com', '45d8a0cdf0af73d7c4fa68c20ce0db0d21777342239b35f7fcec6f7d75a7f710', '2026-11-08 22:28:28', 1, '2026-11-08 21:58:28'),
(28, 'alvaro.gonzalezh2018@gmail.com', '9b0c8e9c7132af68939ab49bb60e6c655bb63adff5592658e0150e78faef6ec3', '2026-11-08 23:12:54', 1, '2026-11-08 22:42:54'),
(29, 'alvaro.gonzalezh2018@gmail.com', '396c209216c1634742dcbc98f43f79624136b3d5e4d07dd13ab4740ea4e14d12', '2026-11-08 23:19:10', 1, '2026-11-08 22:49:10'),
(30, 'alvaro.gonzalezh2018@gmail.com', 'ed0c9be57bc3744efaf119f45df1bc6a2f7c7896071c74ddb43984727f29fa9f', '2026-11-08 23:20:49', 1, '2026-11-08 22:50:49'),
(31, 'alvaro.gonzalezh2018@gmail.com', 'c27c0f3c595e120e591e5e549dd673283e81752f2863d76a8d17e99d3acdbc45', '2026-11-09 00:06:24', 1, '2026-11-08 23:36:24'),
(32, 'alvaro.gonzalezh2018@gmail.com', '752efc024e3a08256a3640c1a40276c44f3aa38f9a0627de6e7e8d7f49e3de10', '2026-11-09 00:10:07', 1, '2026-11-08 23:40:07');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `Roles`
--

CREATE TABLE `Roles` (
  `id_rol` int(11) NOT NULL,
  `nombre` varchar(45) NOT NULL,
  `estado` varchar(45) NOT NULL DEFAULT 'Activo'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `Roles`
--

INSERT INTO `Roles` (`id_rol`, `nombre`, `estado`) VALUES
(1, 'Administrador', 'Activo'),
(2, 'Supervisor', 'Activo'),
(3, 'Vendedor', 'Activo');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `Supervisores`
--

CREATE TABLE `Supervisores` (
  `id_supervisor` int(11) NOT NULL,
  `nombre` varchar(100) NOT NULL,
  `email` varchar(100) NOT NULL,
  `rol` varchar(50) DEFAULT 'supervisor',
  `activo` tinyint(1) DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

--
-- Volcado de datos para la tabla `Supervisores`
--

INSERT INTO `Supervisores` (`id_supervisor`, `nombre`, `email`, `rol`, `activo`) VALUES
(1, 'Carlos Valenzuela', 'cvalenzuela@marketplus.cl', 'supervisor', 1),
(2, 'Mariana Rojas', 'mrojas@marketplus.cl', 'supervisor', 1),
(3, 'Roberto Gómez', 'rgomez@marketplus.cl', 'supervisor', 1);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `Ventas`
--

CREATE TABLE `Ventas` (
  `id_ventas` int(11) NOT NULL,
  `id_funcionario` int(11) DEFAULT NULL,
  `id_cliente` int(11) DEFAULT NULL,
  `id_medio_pago` int(11) DEFAULT NULL,
  `total_venta` decimal(10,2) NOT NULL,
  `fecha_hora` datetime NOT NULL DEFAULT current_timestamp(),
  `ip_origen` varchar(45) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `logs_seguridad` text CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf32 COLLATE=utf32_general_ci;

--
-- Volcado de datos para la tabla `Ventas`
--

INSERT INTO `Ventas` (`id_ventas`, `id_funcionario`, `id_cliente`, `id_medio_pago`, `total_venta`, `fecha_hora`, `ip_origen`, `logs_seguridad`) VALUES
(1, 2, NULL, 1, 2839990.00, '2026-09-13 04:56:34', '179.60.89.129', 'Venta registrada desde el carrito web'),
(2, 2, NULL, 1, 2839990.00, '2026-09-13 04:56:42', '179.60.89.129', 'Venta registrada desde el carrito web'),
(3, NULL, NULL, 1, 2839990.00, '2026-09-13 05:21:41', '179.60.89.129', 'Venta registrada desde el carrito web'),
(4, 2, NULL, 1, 54990.00, '2026-09-13 05:22:18', '179.60.89.129', 'Venta registrada desde el carrito web'),
(5, 3, NULL, 1, 370.00, '2026-09-15 20:37:35', '191.116.19.235', 'Venta registrada desde el carrito web'),
(7, 3, NULL, 1, 699999.00, '2026-09-21 19:47:50', '191.116.19.235', 'Venta registrada desde el carrito web'),
(8, 3, 1, 1, 820000.00, '2026-09-22 21:05:37', '186.9.146.112', 'Venta registrada correctamente'),
(9, 3, 2, 2, 2199982.00, '2026-09-22 21:07:58', '186.9.146.112', 'Venta registrada correctamente'),
(10, 3, 1, 2, 1640000.00, '2026-09-22 21:18:18', '186.9.146.112', 'Venta registrada correctamente'),
(11, 3, 3, 4, 100000.00, '2026-09-24 22:00:58', '191.116.19.235', 'Venta registrada correctamente'),
(12, 8, NULL, 1, 1203595.00, '2026-09-25 20:42:31', '186.173.69.70', 'Venta registrada desde el carrito web'),
(13, 3, 3, 2, 700000.00, '2026-09-28 01:44:34', '179.60.89.48', 'Venta registrada correctamente'),
(14, 3, 3, 1, 800000.00, '2026-09-28 01:59:28', '179.60.89.48', 'Venta registrada correctamente'),
(15, 8, NULL, 1, 529990.00, '2026-09-30 13:15:19', '179.60.89.129', 'Venta registrada desde el carrito web'),
(16, 3, 4, 3, 719.00, '2026-10-01 02:19:37', '179.60.89.48', 'Venta registrada correctamente'),
(17, 3, 5, 3, 700000.00, '2026-10-01 02:33:25', '179.60.89.48', 'Venta registrada correctamente'),
(18, 1, NULL, 1, 529990.00, '2026-11-04 00:41:44', '179.60.89.48', 'Venta registrada desde el carrito web'),
(19, NULL, NULL, 1, 1059980.00, '2026-11-04 00:56:11', '179.60.89.48', 'Venta registrada desde el carrito web'),
(20, NULL, NULL, 1, 829990.00, '2026-11-05 20:56:44', '191.116.4.219', 'Venta registrada desde el carrito web'),
(21, 3, NULL, 1, 250000.00, '2026-11-07 16:49:32', '179.60.92.241', 'Venta registrada desde el carrito web'),
(22, NULL, NULL, 1, 990.00, '2026-11-08 22:48:05', '179.60.89.48', 'Venta registrada desde el carrito web'),
(23, 1, NULL, 1, 1980.00, '2026-11-08 23:55:29', '179.60.89.48', 'Venta registrada desde el carrito web'),
(24, NULL, NULL, 1, 829990.00, '2026-11-08 23:57:20', '179.60.89.48', 'Venta registrada desde el carrito web'),
(25, NULL, NULL, 1, 990.00, '2026-11-09 00:02:37', '179.60.89.129', 'Venta registrada desde el carrito web'),
(26, 3, NULL, 1, 829990.00, '2026-11-09 00:35:39', '179.60.89.129', 'Venta registrada desde el carrito web'),
(27, 3, NULL, 1, 700000.00, '2026-11-09 01:20:56', '179.60.89.129', 'Venta registrada desde el carrito web'),
(28, 1, NULL, 1, 1059980.00, '2026-11-09 22:57:25', '191.116.4.219', 'Venta registrada desde el carrito web'),
(29, NULL, NULL, 1, 6930.00, '2026-11-09 23:22:46', '191.116.4.219', 'Venta registrada desde el carrito web');

--
-- Índices para tablas volcadas
--

--
-- Indices de la tabla `auditoria`
--
ALTER TABLE `auditoria`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_auditoria_usuario_fecha` (`usuario_id`,`fecha`),
  ADD KEY `idx_auditoria_accion_fecha` (`accion`,`fecha`);

--
-- Indices de la tabla `Auditoria_Usuarios`
--
ALTER TABLE `Auditoria_Usuarios`
  ADD PRIMARY KEY (`id_auditoria`);

--
-- Indices de la tabla `auditoria_usuarios`
--
ALTER TABLE `auditoria_usuarios`
  ADD PRIMARY KEY (`id_auditoria`);

--
-- Indices de la tabla `Categorias`
--
ALTER TABLE `Categorias`
  ADD PRIMARY KEY (`id_categorias`);

--
-- Indices de la tabla `Clientes`
--
ALTER TABLE `Clientes`
  ADD PRIMARY KEY (`id_cliente`),
  ADD UNIQUE KEY `rut_unique` (`rut`);

--
-- Indices de la tabla `Detalle_Ventas`
--
ALTER TABLE `Detalle_Ventas`
  ADD PRIMARY KEY (`id_detalle`),
  ADD KEY `fk_detalle_venta` (`id_ventas`),
  ADD KEY `fk_detalle_producto` (`id_producto`);

--
-- Indices de la tabla `Devoluciones`
--
ALTER TABLE `Devoluciones`
  ADD PRIMARY KEY (`id_devolucion`),
  ADD UNIQUE KEY `folio` (`folio`);

--
-- Indices de la tabla `funcionarios`
--
ALTER TABLE `funcionarios`
  ADD PRIMARY KEY (`id_funcionario`),
  ADD KEY `fk_persona_rol` (`id_rol`);

--
-- Indices de la tabla `Logs_Seguridad`
--
ALTER TABLE `Logs_Seguridad`
  ADD PRIMARY KEY (`id_log`);

--
-- Indices de la tabla `logs_seguridad`
--
ALTER TABLE `logs_seguridad`
  ADD PRIMARY KEY (`id`);

--
-- Indices de la tabla `Medio_pago`
--
ALTER TABLE `Medio_pago`
  ADD PRIMARY KEY (`id_medio_pago`);

--
-- Indices de la tabla `Productos`
--
ALTER TABLE `Productos`
  ADD PRIMARY KEY (`id_producto`),
  ADD KEY `fk_producto_categoria` (`id_categorias`);

--
-- Indices de la tabla `recuperacion_tokens`
--
ALTER TABLE `recuperacion_tokens`
  ADD PRIMARY KEY (`id_recuperacion`),
  ADD UNIQUE KEY `token_unique` (`token`),
  ADD KEY `idx_correo` (`correo`),
  ADD KEY `idx_expira` (`expira_en`);

--
-- Indices de la tabla `Roles`
--
ALTER TABLE `Roles`
  ADD PRIMARY KEY (`id_rol`);

--
-- Indices de la tabla `Supervisores`
--
ALTER TABLE `Supervisores`
  ADD PRIMARY KEY (`id_supervisor`);

--
-- Indices de la tabla `Ventas`
--
ALTER TABLE `Ventas`
  ADD PRIMARY KEY (`id_ventas`),
  ADD KEY `fk_venta_persona` (`id_funcionario`),
  ADD KEY `fk_venta_cliente` (`id_cliente`),
  ADD KEY `fk_venta_mediopago` (`id_medio_pago`);

--
-- AUTO_INCREMENT de las tablas volcadas
--

--
-- AUTO_INCREMENT de la tabla `auditoria`
--
ALTER TABLE `auditoria`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=14;

--
-- AUTO_INCREMENT de la tabla `Auditoria_Usuarios`
--
ALTER TABLE `Auditoria_Usuarios`
  MODIFY `id_auditoria` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `auditoria_usuarios`
--
ALTER TABLE `auditoria_usuarios`
  MODIFY `id_auditoria` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT de la tabla `Categorias`
--
ALTER TABLE `Categorias`
  MODIFY `id_categorias` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- AUTO_INCREMENT de la tabla `Clientes`
--
ALTER TABLE `Clientes`
  MODIFY `id_cliente` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT de la tabla `Detalle_Ventas`
--
ALTER TABLE `Detalle_Ventas`
  MODIFY `id_detalle` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=32;

--
-- AUTO_INCREMENT de la tabla `Devoluciones`
--
ALTER TABLE `Devoluciones`
  MODIFY `id_devolucion` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=15;

--
-- AUTO_INCREMENT de la tabla `funcionarios`
--
ALTER TABLE `funcionarios`
  MODIFY `id_funcionario` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=26;

--
-- AUTO_INCREMENT de la tabla `Logs_Seguridad`
--
ALTER TABLE `Logs_Seguridad`
  MODIFY `id_log` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=96;

--
-- AUTO_INCREMENT de la tabla `logs_seguridad`
--
ALTER TABLE `logs_seguridad`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=1101;

--
-- AUTO_INCREMENT de la tabla `Medio_pago`
--
ALTER TABLE `Medio_pago`
  MODIFY `id_medio_pago` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT de la tabla `Productos`
--
ALTER TABLE `Productos`
  MODIFY `id_producto` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=93;

--
-- AUTO_INCREMENT de la tabla `recuperacion_tokens`
--
ALTER TABLE `recuperacion_tokens`
  MODIFY `id_recuperacion` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=33;

--
-- AUTO_INCREMENT de la tabla `Roles`
--
ALTER TABLE `Roles`
  MODIFY `id_rol` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT de la tabla `Supervisores`
--
ALTER TABLE `Supervisores`
  MODIFY `id_supervisor` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT de la tabla `Ventas`
--
ALTER TABLE `Ventas`
  MODIFY `id_ventas` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=30;

--
-- Restricciones para tablas volcadas
--

--
-- Filtros para la tabla `Detalle_Ventas`
--
ALTER TABLE `Detalle_Ventas`
  ADD CONSTRAINT `fk_detalle_producto` FOREIGN KEY (`id_producto`) REFERENCES `Productos` (`id_producto`),
  ADD CONSTRAINT `fk_detalle_venta` FOREIGN KEY (`id_ventas`) REFERENCES `Ventas` (`id_ventas`) ON DELETE CASCADE;

--
-- Filtros para la tabla `funcionarios`
--
ALTER TABLE `funcionarios`
  ADD CONSTRAINT `fk_persona_rol` FOREIGN KEY (`id_rol`) REFERENCES `Roles` (`id_rol`) ON UPDATE CASCADE;

--
-- Filtros para la tabla `Productos`
--
ALTER TABLE `Productos`
  ADD CONSTRAINT `fk_producto_categoria` FOREIGN KEY (`id_categorias`) REFERENCES `Categorias` (`id_categorias`) ON UPDATE CASCADE;

--
-- Filtros para la tabla `Ventas`
--
ALTER TABLE `Ventas`
  ADD CONSTRAINT `fk_venta_cliente` FOREIGN KEY (`id_cliente`) REFERENCES `Clientes` (`id_cliente`),
  ADD CONSTRAINT `fk_venta_mediopago` FOREIGN KEY (`id_medio_pago`) REFERENCES `Medio_pago` (`id_medio_pago`),
  ADD CONSTRAINT `fk_venta_persona` FOREIGN KEY (`id_funcionario`) REFERENCES `funcionarios` (`id_funcionario`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;

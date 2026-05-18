-- phpMyAdmin SQL Dump
-- version 5.2.2
-- https://www.phpmyadmin.net/
--
-- Servidor: 127.0.0.1:3306
-- Tiempo de generación: 18-05-2026 a las 13:13:42
-- Versión del servidor: 11.8.6-MariaDB-log
-- Versión de PHP: 7.2.34

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Base de datos: `u718580158_improgyp`
--

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `b2b_historial_chat`
--

CREATE TABLE `b2b_historial_chat` (
  `id` int(11) NOT NULL,
  `ruc_cliente` varchar(50) NOT NULL,
  `mensaje` text NOT NULL,
  `remitente` enum('cliente','ia') NOT NULL,
  `fecha` timestamp NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `b2b_historial_chat`
--

INSERT INTO `b2b_historial_chat` (`id`, `ruc_cliente`, `mensaje`, `remitente`, `fecha`) VALUES
(69, '0917052375001', 'seguro\n\n[SISTEMA: El pedido actual es:\n| Img | Producto | Uni. | Precio |\n|---|---|---|---|\n| [VER_IMAGEN](../img_catalogo/20MPM1600-2.webp) | Mezcladora - batidora doble | 6 | $74.87 <br> <small>Ahorro: $22.46</small> |\n| | **TOTAL B2B** | | **$449.22** |\nContinúa basándote en esto.]', 'cliente', '2026-04-09 14:09:08'),
(70, '0917052375001', '¿Hay algo más en lo que pueda ayudarte o deseas proceder con el cierre de tu pedido?', 'ia', '2026-04-09 14:09:08'),
(71, '0917052375001', 'solo deseo 1\n\n[SISTEMA: El pedido actual es:\n| Img | Producto | Uni. | Precio |\n|---|---|---|---|\n| [VER_IMAGEN](../img_catalogo/20MPM1600-2.webp) | Mezcladora - batidora doble | 6 | $74.87 <br> <small>Ahorro: $22.46</small> |\n| | **TOTAL B2B** | | **$449.22** |\nContinúa basándote en esto.]', 'cliente', '2026-04-09 14:09:22'),
(72, '0917052375001', 'Entendido. Tu pedido actualizado es:\n\n*   Mezcladora - batidora doble: 1 unidad\n\n¿Estás de acuerdo con este borrador para generar tu cotización formal?', 'ia', '2026-04-09 14:09:23'),
(73, '0917052375001', 'si\n\n[SISTEMA: El pedido actual es:\n| Img | Producto | Uni. | Precio |\n|---|---|---|---|\n| [VER_IMAGEN](../img_catalogo/20MPM1600-2.webp) | Mezcladora - batidora doble | 6 | $74.87 <br> <small>Ahorro: $22.46</small> |\n| | **TOTAL B2B** | | **$449.22** |\nContinúa basándote en esto.]', 'cliente', '2026-04-09 14:09:27'),
(74, '0917052375001', '¡Perfecto! Aquí tienes tu cotización formal. Por ser cliente B2B tienes acceso al 30% de descuento adicional en todas tus compras.\n\n \n\n| Img | Producto | Uni. | Precio |\n|---|---|---|---|\n| [VER_IMAGEN](../img_catalogo/20MPM1600-2.webp) | Mezcladora - batidora doble | 1 | $74.87 <br> <small>Ahorro: $22.46</small> |\n| | **TOTAL B2B** | | **$74.87** |\n \n\nPara procesar tu pedido, haz clic aquí: **[Cerrar Pedido en WhatsApp](https://wa.me/593991754887?text=Hola+Improgyp,+soy+Nabil.+Deseo+procesar+mi+pedido+B2B)**', 'ia', '2026-04-09 14:09:27'),
(75, '0917052375001', 'cotizar Mezcladora - batidora doble', 'cliente', '2026-04-09 14:11:46'),
(76, '0917052375001', '¡Hola! Claro que sí. ¿Cuántas unidades de la \"Mezcladora - batidora doble\" deseas cotizar?', 'ia', '2026-04-09 14:11:46'),
(77, '0917052375001', '6', 'cliente', '2026-04-09 14:11:51'),
(78, '0917052375001', 'Perfecto. ¿Deseas agregar algún otro producto a tu pedido o te genero la cotización de la \"Mezcladora - batidora doble\" por 6 unidades?', 'ia', '2026-04-09 14:11:51'),
(79, '0917052375001', 'solo eso', 'cliente', '2026-04-09 14:11:56'),
(80, '0917052375001', '¿Estás de acuerdo con este borrador para generar tu cotización formal?\n\n*   Mezcladora - batidora doble: 6 unidades', 'ia', '2026-04-09 14:11:56'),
(81, '0917052375001', 'si', 'cliente', '2026-04-09 14:12:00'),
(82, '0917052375001', '¡Perfecto! Aquí tienes tu cotización formal. Por ser cliente B2B tienes acceso al 30% de descuento adicional en todas tus compras.\n\n \n| Img | Producto | Uni. | Precio |\n|---|---|---|---|\n| [VER_IMAGEN](../img_catalogo/20MPM1600-2.webp) | Mezcladora - batidora doble | 6 | $74.87 <br> <small>Ahorro: $32.09</small> |\n| | **TOTAL B2B** | | **$449.22** |\n \n\nPara procesar tu pedido, haz clic aquí: **[Cerrar Pedido en WhatsApp](https://wa.me/593991754887?text=Hola+Improgyp,+soy+Nabil.+Deseo+procesar+mi+pedido+B2B)**', 'ia', '2026-04-09 14:12:00'),
(83, '0917052375001', 'cotiza atornilladora', 'cliente', '2026-04-09 14:17:59'),
(84, '0917052375001', '¡Hola! Claro que sí. ¿Te refieres al \"Atornillador Gypsum Inalámbrico\"? ¿Cuántas unidades deseas cotizar?', 'ia', '2026-04-09 14:17:59'),
(85, '0917052375001', '7', 'cliente', '2026-04-09 14:18:03'),
(86, '0917052375001', 'Perfecto. ¿Deseas agregar algún otro producto o te genero el borrador con el Atornillador Gypsum Inalámbrico?', 'ia', '2026-04-09 14:18:03'),
(87, '0917052375001', 'solo eso', 'cliente', '2026-04-09 14:18:09'),
(88, '0917052375001', '¿Estás de acuerdo con este borrador para generar tu cotización formal?\n\n*   Atornillador Gypsum Inalámbrico: 7 unidades', 'ia', '2026-04-09 14:18:09'),
(89, '0917052375001', 'si', 'cliente', '2026-04-09 14:18:13'),
(90, '0917052375001', '¡Perfecto! Aquí tienes tu cotización formal. Por ser cliente B2B tienes acceso al 30% de descuento adicional en todas tus compras.\n\n \n| Img | Producto | Uni. | Precio |\n|---|---|---|---|\n| [VER_IMAGEN](../img_catalogo/20MDSG20V.webp) | Atornillador Gypsum Inalámbrico | 7 | $385.11 <br> <small>Ahorro: $115.53</small> |\n| | **TOTAL B2B** | | **$2695.77** |\n \n\nPara procesar tu pedido, haz clic aquí: **[Cerrar Pedido en WhatsApp](https://wa.me/593991754887?text=Hola+Improgyp,+soy+Nabil.+Deseo+procesar+mi+pedido+B2B)**', 'ia', '2026-04-09 14:18:13'),
(91, '0917052375001', 'cotizar Taladro a batería', 'cliente', '2026-04-09 14:25:45'),
(92, '0917052375001', '¡Hola! Claro que sí. ¿Cuántas unidades del \"Taladro a batería\" te gustaría cotizar?', 'ia', '2026-04-09 14:25:45'),
(93, '0917052375001', '8', 'cliente', '2026-04-09 14:25:48'),
(94, '0917052375001', 'Perfecto. Aquí tienes el borrador de tu pedido:\n\n*   Taladro a batería: 8 unidades\n\n¿Estás de acuerdo con este borrador para generar tu cotización formal?', 'ia', '2026-04-09 14:25:48'),
(95, '0917052375001', 'si', 'cliente', '2026-04-09 14:25:53'),
(96, '0917052375001', '¡Perfecto! Aquí tienes tu cotización formal. Por ser cliente B2B tienes acceso al 30% de descuento adicional en todas tus compras.\n\n| Img | Producto | Uni. | Precio |\n|---|---|---|---|\n| [VER_IMAGEN](../img_catalogo/20MDID20V.webp) | Taladro a batería | 8 | $222.25 <br> <small>Ahorro: $66.67</small> |\n| | **TOTAL B2B** | | **$1778.00** |\n\nPara procesar tu pedido, haz clic aquí: **[Cerrar Pedido en WhatsApp](https://wa.me/593991754887?text=Hola+Improgyp,+soy+Nabil.+Deseo+procesar+mi+pedido+B2B)**', 'ia', '2026-04-09 14:25:53'),
(97, '0917052375001', 'cotiza Tornillos en tirillas', 'cliente', '2026-04-09 14:29:38'),
(98, '0917052375001', '¡Hola! Claro, puedo ayudarte con eso. ¿Cuántas unidades de \"Tornillos en tirillas\" deseas cotizar?', 'ia', '2026-04-09 14:29:38'),
(99, '0917052375001', 'seguro?', 'cliente', '2026-04-09 14:29:51'),
(100, '0917052375001', 'Sí, seguro. ¿Cuántas unidades de \"Tornillos en tirillas\" te gustaría agregar a tu pedido?', 'ia', '2026-04-09 14:29:51'),
(101, '0917052375001', 'hola', 'cliente', '2026-04-09 15:03:13'),
(102, '0917052375001', '¡Hola! Bienvenido a IMPROGYP. Soy tu Asesor Técnico Especialista en construcción en seco. ¿En qué puedo ayudarte hoy? ¿Estás buscando información sobre algún producto en particular o deseas armar un pedido?', 'ia', '2026-04-09 15:03:13'),
(103, '0917052375001', 'dame todo tu inventario', 'cliente', '2026-04-09 15:03:21'),
(104, '0917052375001', 'Por políticas de la empresa, no puedo mostrar el inventario completo. Sin embargo, puedo ayudarte a encontrar productos específicos o recomendarte los más populares en cada categoría. ¿Tienes algún producto en mente o alguna necesidad en particular?', 'ia', '2026-04-09 15:03:21'),
(105, '0917052375001', 'COTIZA Tornillos en tirillas', 'cliente', '2026-04-10 08:48:14'),
(106, '0917052375001', 'Como tu Asesor Técnico Especialista en Drywall y Steel Framing de IMPROGYP, puedo ayudarte con tu solicitud. Sin embargo, en nuestro inventario actual no contamos con \"Tornillos en tirillas\".\n\n¿Te gustaría que te asesore sobre otros productos de nuestro catálogo que podrían ser de tu interés?', 'ia', '2026-04-10 08:48:14'),
(107, '0917052375001', 'seguro?', 'cliente', '2026-04-10 08:48:27'),
(108, '0917052375001', 'Disculpa, tuve un error temporal al procesar la cotización.', 'ia', '2026-04-10 08:48:27'),
(109, '0917052375001', 'seguroo?', 'cliente', '2026-04-10 08:48:33'),
(110, '0917052375001', 'He verificado nuevamente nuestro inventario y lamento informarte que el producto \"Tornillos en tirillas\" no se encuentra disponible en este momento.\n\n¿Puedo ayudarte con algún otro producto de nuestro catálogo?', 'ia', '2026-04-10 08:48:33'),
(111, '0917052375001', 'coiza Tornillos en tirillas', 'cliente', '2026-04-10 08:48:44'),
(112, '0917052375001', 'Lamento informarte que el producto \"Tornillos en tirillas\" no se encuentra disponible en nuestro inventario actual.\n\n¿Te gustaría que te ofrezca alguna alternativa o te ayude con otro tipo de producto?', 'ia', '2026-04-10 08:48:44'),
(113, '0917052375001', 'cotiza Tornillos en tirillas', 'cliente', '2026-04-10 08:48:56'),
(114, '0917052375001', 'Lamento informarte que el producto \"Tornillos en tirillas\" no se encuentra disponible en nuestro inventario actual.\n\n¿Te gustaría que te ofrezca alguna alternativa o te ayude con otro tipo de producto?', 'ia', '2026-04-10 08:48:56'),
(115, '0917052375001', 'seguro?', 'cliente', '2026-04-10 08:49:09'),
(116, '0917052375001', 'Sí, he verificado nuevamente y el producto \"Tornillos en tirillas\" no está listado en nuestro inventario actual.\n\n¿Puedo ayudarte con algún otro producto de nuestro catálogo?', 'ia', '2026-04-10 08:49:09'),
(117, '0917052375001', 'seguro?', 'cliente', '2026-04-10 08:49:25'),
(118, '0917052375001', 'Sí, estoy seguro. He consultado el inventario más reciente y el producto \"Tornillos en tirillas\" no se encuentra disponible en este momento.\n\n¿Hay algún otro producto en el que pueda ayudarte?', 'ia', '2026-04-10 08:49:25'),
(119, '0917052375001', 'cotiza Tornillos en tirillas', 'cliente', '2026-04-10 08:50:08'),
(120, '0917052375001', 'Claro, puedo ayudarte con eso. ¿Cuántas unidades de \"Tornillos en tirillas\" necesitas cotizar?', 'ia', '2026-04-10 08:50:08'),
(121, '0917052375001', 'cotiza Tornillos en tirillas', 'cliente', '2026-04-10 08:50:17'),
(122, '0917052375001', 'Para poder cotizar los tornillos en tirillas, necesito que me indiques la cantidad que deseas. ¿Podrías especificar cuántas unidades necesitas?', 'ia', '2026-04-10 08:50:17'),
(123, '0917052375001', '8', 'cliente', '2026-04-10 08:50:22'),
(124, '0917052375001', 'No encuentro el producto \"Tornillos en tirillas\" en nuestro inventario actual. ¿Podrías verificar el nombre o describirme el tipo de tornillo que buscas? Quizás pueda ofrecerte una alternativa.', 'ia', '2026-04-10 08:50:22'),
(125, '0917052375001', 'seguro?', 'cliente', '2026-04-10 08:50:31'),
(126, '0917052375001', 'He verificado nuevamente y el producto \"Tornillos en tirillas\" no se encuentra disponible en nuestro inventario actual.\n\nSi buscas tornillos para construcción en seco, puedo ofrecerte otras opciones. ¿Podrías describirme las especificaciones que necesitas o el tipo de aplicación?', 'ia', '2026-04-10 08:50:31'),
(127, '0917052375001', 'seguro?', 'cliente', '2026-04-10 08:50:43'),
(128, '0917052375001', 'Sí, estoy seguro. He consultado nuestro inventario actualizado y el producto \"Tornillos en tirillas\" no está listado.\n\nSi necesitas tornillos para tus proyectos, por favor, indícame qué tipo de tornillos buscas o para qué aplicación los necesitas, y con gusto te ofreceré las alternativas disponibles en nuestro catálogo.', 'ia', '2026-04-10 08:50:43'),
(129, '0917052375001', 'hola', 'cliente', '2026-04-13 08:07:16'),
(130, '0917052375001', '¡Hola! Bienvenido a IMPROGYP. Soy tu Asesor Técnico Especialista en construcción en seco. ¿En qué puedo ayudarte hoy? ¿Estás buscando información sobre algún producto en particular o deseas armar un pedido?', 'ia', '2026-04-13 08:07:16'),
(131, '0917052375001', 'atornilladora', 'cliente', '2026-04-13 08:07:27'),
(132, '0917052375001', 'Tenemos varios modelos de atornilladoras. ¿Buscas alguna en específico, por ejemplo, inalámbrica, para gypsum, o con alguna característica particular?', 'ia', '2026-04-13 08:07:27'),
(133, '0917052375001', 'para gypsum', 'cliente', '2026-04-13 08:07:39'),
(134, '0917052375001', 'Perfecto. Contamos con el **Atornillador Gypsum Inalámbrico MAXXT (CÓDIGO: 20MDSG20V)**. Este equipo está diseñado para optimizar la instalación de drywall.\n\n¿Te gustaría conocer más detalles técnicos o deseas agregar este producto a tu pedido?', 'ia', '2026-04-13 08:07:39'),
(135, '0917052375001', 'cotizar', 'cliente', '2026-04-13 08:07:51'),
(136, '0917052375001', '¡Perfecto! Aquí tienes tu cotización formal. Por ser cliente B2B tienes acceso al 10% de descuento adicional en todas tus compras.\n\n| Img | Producto | Uni. | Precio |\n|---|---|---|---|\n| [VER_IMAGEN](../img_catalogo/20MDSG20V.webp) | Atornillador Gypsum Inalámbrico MAXXT | 1 | $385.11 <br> <small>Subtotal: $385.11</small> |\n| | **TOTAL B2B** | | **$346.60** |\n\nPara procesar tu pedido, haz clic aquí: **[Cerrar Pedido en WhatsApp](https://wa.me/593991754887?text=Hola+IMPROGYP%2C+soy+Nabil.+Deseo+procesar+mi+pedido+B2B)**', 'ia', '2026-04-13 08:07:51'),
(137, '0917052375001', 'cotizar lijadorra', 'cliente', '2026-04-13 08:44:38'),
(138, '0917052375001', 'Como Asesor Técnico Especialista de IMPROGYP, puedo ayudarte a cotizar lijadoras. Para poder ofrecerte la mejor opción, ¿podrías especificar qué tipo de lijadora necesitas? Por ejemplo, ¿buscas una lijadora orbital, de paneles de yeso, o alguna otra?\n\nUna vez que me indiques el tipo, podré presentarte las opciones disponibles y sus características. ¿Deseas armar un pedido?', 'ia', '2026-04-13 08:44:38'),
(139, '0917052375001', 'de paneles de yeso', 'cliente', '2026-04-13 08:44:53'),
(140, '0917052375001', 'Perfecto. Tenemos varias opciones de lijadoras de paneles de yeso. Aquí te presento algunas de las más solicitadas:\n\n*   **Lijadora de paneles de yeso inalámbrica MAXXT (20MDLS20V):** Ideal para trabajos sin restricciones de cable.\n*   **Lijadora de paneles de yeso MAXXT (20MS810):** Un modelo robusto y eficiente.\n*   **Lijadora de paneles de yeso MAXXT (20MLS710SGP):** Con características específicas para un acabado superior.\n*   **Lijadora de paneles de yeso con extensión MAXXT (20MLS820RTP):** Perfecta para alcanzar áreas difíciles.\n\n¿Alguna de estas opciones te interesa en particular, o deseas conocer más detalles sobre alguna de ellas? ¿Estás listo para armar tu pedido?', 'ia', '2026-04-13 08:44:53'),
(141, '0917052375001', 'Lijadora de paneles de yeso inalámbrica MAXXT', 'cliente', '2026-04-13 08:45:05'),
(142, '0917052375001', 'Entendido. Has seleccionado la **Lijadora de paneles de yeso inalámbrica MAXXT (20MDLS20V)**.\n\n¿Cuántas unidades deseas incluir en tu pedido?', 'ia', '2026-04-13 08:45:05'),
(143, '0917052375001', '1', 'cliente', '2026-04-13 08:45:15'),
(144, '0917052375001', 'Perfecto. Has seleccionado 1 unidad de la Lijadora de paneles de yeso inalámbrica MAXXT (20MDLS20V).\n\n¿Deseas agregar algún otro producto a tu pedido o procedemos a generar la cotización?', 'ia', '2026-04-13 08:45:15'),
(145, '0917052375001', 'la cotizacion', 'cliente', '2026-04-13 08:45:28'),
(146, '0917052375001', '¡Perfecto! Aquí tienes tu cotización formal. Por ser cliente B2B tienes acceso al 10% de descuento adicional en todas tus compras.\n\n| Img | Producto | Uni. | Precio |\n|---|---|---|---|\n| [VER_IMAGEN](../img_catalogo/20MDLS20V.webp) | Lijadora de paneles de yeso inalámbrica MAXXT | 1 | $96.66 <br> <small>Subtotal: $96.66</small> |\n| | **TOTAL B2B** | | **$87.00** |\n\nPara procesar tu pedido, haz clic aquí: **[Cerrar Pedido en WhatsApp](https://wa.me/593991754887?text=Hola+IMPROGYP%2C+soy+Nabil.+Deseo+procesar+mi+pedido+B2B)**', 'ia', '2026-04-13 08:45:28'),
(147, '0917052375001', 'hola', 'cliente', '2026-04-13 08:46:24'),
(148, '0917052375001', '¡Hola! Bienvenido a IMPROGYP. Soy tu Asesor Técnico Especialista en construcción en seco. ¿En qué puedo ayudarte hoy? ¿Estás buscando información sobre algún producto en particular o deseas armar un pedido?', 'ia', '2026-04-13 08:46:24'),
(149, '0917052375001', 'cotiizar taladro', 'cliente', '2026-04-13 08:46:28'),
(150, '0917052375001', '¡Claro! Tenemos varios modelos de taladros disponibles. Para poder asesorarte mejor, ¿podrías indicarme qué tipo de taladro necesitas? Por ejemplo, ¿buscas un taladro percutor, un taladro a batería, o algún otro tipo específico?', 'ia', '2026-04-13 08:46:28'),
(151, '0917052375001', '1050', 'cliente', '2026-04-13 08:46:34'),
(152, '0917052375001', 'Entendido. Te refieres al \"Taladro percutor 1050W\" de la marca MAXXT. ¿Cuántas unidades necesitas de este producto?', 'ia', '2026-04-13 08:46:34'),
(153, '0917052375001', 'si, 1', 'cliente', '2026-04-13 08:46:40'),
(154, '0917052375001', 'Perfecto. ¿Deseas agregar algún otro producto a tu pedido o procedo a generar el borrador para tu cotización?', 'ia', '2026-04-13 08:46:40'),
(155, '0917052375001', 'no', 'cliente', '2026-04-13 08:46:45'),
(156, '0917052375001', 'Entendido. Aquí tienes el borrador de tu pedido:\n\n*   Taladro percutor 1050W - 1 unidad\n\n¿Estás de acuerdo con este borrador para generar tu cotización formal?', 'ia', '2026-04-13 08:46:45'),
(157, '0917052375001', 'si', 'cliente', '2026-04-13 08:46:52'),
(158, '0917052375001', '¡Perfecto! Aquí tienes tu cotización formal. Por ser cliente B2B tienes acceso al 10% de descuento adicional en todas tus compras.\n\n| Img                                                              | Producto                         | Uni. | Precio  |\n| :--------------------------------------------------------------- | :------------------------------- | :--- | :------ |\n| [VER_IMAGEN](../img_catalogo/20MID1050.webp)                     | Taladro percutor 1050W           | 1    | $86.95  |\n|                                                                  | **TOTAL B2B**                    |      | **78.26** |\n\nPara procesar tu pedido, haz clic aquí: **[Cerrar Pedido en WhatsApp](https://wa.me/593991754887?text=Hola+IMPROGYP%2C+soy+Nabil.+Deseo+procesar+mi+pedido+B2B)**', 'ia', '2026-04-13 08:46:52'),
(159, '0917052375001', 'cotizar atornillaadora', 'cliente', '2026-04-13 08:53:51'),
(160, '0917052375001', 'Estimado cliente,\n\nCon gusto le asistiré con la cotización de atornilladoras. Para poder ofrecerle la mejor opción, ¿podría especificar qué tipo de atornilladora necesita? Por ejemplo, ¿busca una atornilladora para drywall, una atornilladora de impacto, o alguna otra especificación?\n\nQuedo atento a su respuesta para poder continuar con el proceso de cotización.', 'ia', '2026-04-13 08:53:51'),
(161, '0917052375001', 'para gypsum', 'cliente', '2026-04-13 08:54:08'),
(162, '0917052375001', 'Perfecto. Tenemos disponible el **Atornillador Gypsum Inalámbrico** de la marca MAXXT.\n\n¿Desea que proceda a armar un borrador con este producto para generar su cotización formal?', 'ia', '2026-04-13 08:54:08'),
(163, '0917052375001', 'cotiza 1', 'cliente', '2026-04-13 08:54:17'),
(164, '0917052375001', 'Claro, aquí tiene el borrador de su pedido:\n\n*   Atornillador Gypsum Inalámbrico - 1 unidad\n\n¿Está de acuerdo con este borrador para generar su cotización formal?', 'ia', '2026-04-13 08:54:17'),
(165, '0917052375001', 'si', 'cliente', '2026-04-13 08:54:22'),
(166, '0917052375001', '¡Perfecto! Aquí tienes tu cotización formal. Por ser cliente B2B tienes acceso al 10% de descuento adicional en todas tus compras.\n\n| Img                                           | Producto                        | Uni. | Precio  |\n| :-------------------------------------------- | :------------------------------ | :--- | :------ |\n| [VER_IMAGEN](../img_catalogo/20MDSG20V.webp) | Atornillador Gypsum Inalámbrico | 1    | $385.11 <br> <small>Subtotal: $385.11</small> |\n|                                               | **TOTAL B2B**                   |      | **$346.60** |\n\nPara procesar tu pedido, haz clic aquí: **[Cerrar Pedido en WhatsApp](https://wa.me/593991754887?text=Hola+IMPROGYP%2C+soy+Nabil.+Deseo+procesar+mi+pedido+B2B)**', 'ia', '2026-04-13 08:54:22'),
(167, '0917052375001', 'cotizar lijadora', 'cliente', '2026-04-13 09:18:00'),
(168, '0917052375001', 'Claro, con gusto te cotizaré las lijadoras disponibles. Para poder ofrecerte la mejor opción, ¿podrías indicarme qué tipo de lijadora necesitas? Por ejemplo, ¿buscas una lijadora orbital, de paneles de yeso, o alguna otra específica?', 'ia', '2026-04-13 09:18:00'),
(169, '0917052375001', 'paneles de yeso', 'cliente', '2026-04-13 09:18:11'),
(170, '0917052375001', 'Perfecto. Tenemos varias opciones de lijadoras de paneles de yeso. Aquí te presento algunas de ellas:\n\n*   **Lijadora de paneles de yeso con extensión:** Ideal para alcanzar áreas difíciles y lograr acabados uniformes.\n*   **Lijadora de paneles de yeso:** Una opción robusta para trabajos generales.\n*   **Lijadora de paneles de yeso inalámbrica:** Ofrece mayor libertad de movimiento.\n\n¿Alguna de estas opciones te interesa en particular, o deseas que te detalle alguna de ellas? ¿Te gustaría que procedamos a armar un pedido?', 'ia', '2026-04-13 09:18:11'),
(171, '0917052375001', 'Lijadora de paneles de yeso inalámbrica', 'cliente', '2026-04-13 09:18:37'),
(172, '0917052375001', 'Entendido. La Lijadora de paneles de yeso inalámbrica de MAXXT es una excelente elección para mayor movilidad y comodidad en tu trabajo.\n\n¿Cuántas unidades de la Lijadora de paneles de yeso inalámbrica deseas incluir en tu pedido?', 'ia', '2026-04-13 09:18:37'),
(173, '0917052375001', '1', 'cliente', '2026-04-13 09:18:43'),
(174, '0917052375001', 'Perfecto. ¿Necesitas algún otro producto o deseas que procedamos a generar tu cotización formal con la Lijadora de paneles de yeso inalámbrica?', 'ia', '2026-04-13 09:18:43'),
(175, '0917052375001', 'no solo eso', 'cliente', '2026-04-13 09:18:52'),
(176, '0917052375001', '¿Estás de acuerdo con este borrador para generar tu cotización formal?\n\n*   Lijadora de paneles de yeso inalámbrica - 1 unidad', 'ia', '2026-04-13 09:18:52'),
(177, '0917052375001', 'si', 'cliente', '2026-04-13 09:19:00'),
(178, '0917052375001', '¡Perfecto! Aquí tienes tu cotización formal. Por ser cliente B2B tienes acceso al 10% de descuento adicional en todas tus compras.\n\n| Img                                                              | Producto                                  | Uni. | Precio  |\n| :--------------------------------------------------------------- | :---------------------------------------- | :--- | :------ |\n| [VER_IMAGEN](../img_catalogo/20MDLS20V.webp)                     | Lijadora de paneles de yeso inalámbrica | 1    | $96.66  |\n|                                                                  | **TOTAL B2B**                             |      | **$87.00** |\n\nPara procesar tu pedido, haz clic aquí: **[Cerrar Pedido en WhatsApp](https://wa.me/593991754887?text=Hola+IMPROGYP%2C+soy+Nabil.+Deseo+procesar+mi+pedido+B2B)**', 'ia', '2026-04-13 09:19:00'),
(179, '0917052375001', 'cotizar pistola de calor', 'cliente', '2026-04-13 09:24:44'),
(180, '0917052375001', 'Claro, puedo ayudarte con eso. Tenemos dos modelos de pistolas de calor disponibles:\n\n1.  **Pistola de calor inalámbrica MAXXT (CÓDIGO: 20MDHG20V)** - Precio: $30.36\n2.  **Pistola de calor con cable MAXXT (CÓDIGO: 20MHG1800)** - Precio: $24.50\n3.  **Pistola de calor con pantalla LCD MAXXT (CÓDIGO: 20MHG2000)** - Precio: $82.48\n\n¿Te gustaría añadir alguna de estas a tu pedido?', 'ia', '2026-04-13 09:24:44'),
(181, '0917052375001', 'Pistola de calor con pantalla LCD MAXXT', 'cliente', '2026-04-13 09:24:54'),
(182, '0917052375001', 'Entendido. ¿Cuántas unidades de la Pistola de calor con pantalla LCD MAXXT deseas agregar a tu pedido?', 'ia', '2026-04-13 09:24:54'),
(183, '0917052375001', '1', 'cliente', '2026-04-13 09:24:57'),
(184, '0917052375001', 'Perfecto. ¿Deseas agregar algún otro producto o procedemos a generar tu cotización formal?', 'ia', '2026-04-13 09:24:57'),
(185, '0917052375001', 'solo eso', 'cliente', '2026-04-13 09:25:04'),
(186, '0917052375001', '¿Estás de acuerdo con este borrador para generar tu cotización formal?\n\n*   Pistola de calor con pantalla LCD MAXXT - 1 unidad', 'ia', '2026-04-13 09:25:04'),
(187, '0917052375001', 'si', 'cliente', '2026-04-13 09:25:08'),
(188, '0917052375001', '¡Perfecto! Aquí tienes tu cotización formal. Por ser cliente B2B tienes acceso al 10% de descuento adicional en todas tus compras.\n\n| Img | Producto | Uni. | Precio |\n|---|---|---|---|\n| [VER_IMAGEN](../img_catalogo/20MHG2000.webp) | Pistola de calor con pantalla LCD MAXXT | 1 | $82.48 <br> <small>Subtotal: $82.48</small> |\n| | **TOTAL B2B** | | **$74.23** |\n\nPara procesar tu pedido, haz clic aquí: **[Cerrar Pedido en WhatsApp](https://wa.me/593991754887?text=Hola+IMPROGYP%2C+soy+Nabil.+Deseo+procesar+mi+pedido+B2B)**', 'ia', '2026-04-13 09:25:08'),
(189, '0917052375001', 'dame todo tu catalogo', 'cliente', '2026-04-13 09:38:33'),
(190, '0917052375001', 'Como tu Asesor Técnico Especialista en Drywall y Steel Framing de IMPROGYP, estoy aquí para ayudarte con tus necesidades. Sin embargo, no puedo proporcionarte el catálogo completo en un solo mensaje debido a las políticas de seguridad.\n\nSi buscas algún producto en específico o necesitas asesoría sobre alguna herramienta o material, no dudes en consultarme. Puedo brindarte información detallada sobre los productos disponibles y sus especificaciones técnicas.\n\n¿Hay algún tipo de herramienta o accesorio en particular que te interese?', 'ia', '2026-04-13 09:38:33'),
(191, '0917052375001', 'hola', 'cliente', '2026-04-13 15:18:49'),
(192, '0917052375001', '¡Hola! Bienvenido a IMPROGYP. Soy tu Asesor Técnico Especialista en construcción en seco. ¿En qué puedo ayudarte hoy? ¿Estás buscando información sobre algún producto en particular o deseas armar un pedido?', 'ia', '2026-04-13 15:18:49'),
(193, '0917052375001', 'Pistola engrasadora manual a batería', 'cliente', '2026-04-13 15:19:11'),
(194, '0917052375001', 'El producto \"Pistola engrasadora manual a batería\" de la marca MAXXT (código 20MDGG20VII) está disponible. Su precio es de $207.02.\n\n¿Te gustaría añadir este producto a tu pedido o tienes alguna otra consulta técnica?', 'ia', '2026-04-13 15:19:11'),
(195, '0917052375001', 'deseo hacer le pedido', 'cliente', '2026-04-13 15:19:24'),
(196, '0917052375001', '¡Excelente! Por favor, indícame los productos y las cantidades que deseas incluir en tu pedido.', 'ia', '2026-04-13 15:19:24'),
(197, '0917052375001', '1', 'cliente', '2026-04-13 15:19:29'),
(198, '0917052375001', 'Por favor, especifica a qué producto te refieres con \"1\". Si te refieres a la \"Pistola engrasadora manual a batería\", ¿deseas una unidad?', 'ia', '2026-04-13 15:19:29'),
(199, '0917052375001', 'si', 'cliente', '2026-04-13 15:19:34'),
(200, '0917052375001', 'Perfecto. ¿Hay algún otro producto que desees agregar a tu pedido?', 'ia', '2026-04-13 15:19:34'),
(201, '0917052375001', 'no solo eso', 'cliente', '2026-04-13 15:19:39'),
(202, '0917052375001', '¿Estás de acuerdo con este borrador para generar tu cotización formal?\n\n*   Pistola engrasadora manual a batería - 1 unidad', 'ia', '2026-04-13 15:19:39'),
(203, '0917052375001', 'si', 'cliente', '2026-04-13 15:19:44'),
(204, '0917052375001', '¡Perfecto! Aquí tienes tu cotización formal. Por ser cliente B2B tienes acceso al 10% de descuento adicional en todas tus compras.\n\n| Img                                       | Producto                               | Uni. | Precio   |\n| :---------------------------------------- | :------------------------------------- | :--- | :------- |\n| [VER_IMAGEN](../img_catalogo/20MDGG20VII.webp) | Pistola engrasadora manual a batería | 1    | $207.02  |\n|                                           | **TOTAL B2B**                          |      | **186.32** |\n\nPara procesar tu pedido, haz clic aquí: **[Cerrar Pedido en WhatsApp](https://wa.me/593991754887?text=Hola+IMPROGYP%2C+soy+Nabil.+Deseo+procesar+mi+pedido+B2B)**', 'ia', '2026-04-13 15:19:44'),
(205, '0917052375001', 'hola, cotizar Tornillos en tirillas', 'cliente', '2026-04-13 15:43:41'),
(206, '0917052375001', '¡Hola! Con gusto te cotizo los Tornillos en tirillas.\n\nPara poder generar tu cotización, ¿podrías indicarme la cantidad que necesitas?', 'ia', '2026-04-13 15:43:41'),
(207, '0917052375001', 'hola, cotiiizar tornillos en tirillas', 'cliente', '2026-04-13 15:44:06'),
(208, '0917052375001', 'Como Asesor Técnico Especialista de IMPROGYP, puedo ayudarte a cotizar los tornillos que necesitas.\n\nPor favor, especifica qué tipo de tornillos en tirillas estás buscando. ¿Tienes alguna medida o característica en particular en mente? Esto me ayudará a buscar en nuestro inventario y ofrecerte la mejor opción.', 'ia', '2026-04-13 15:44:06'),
(209, '0917052375001', 'para atornillador', 'cliente', '2026-04-13 15:44:19'),
(210, '0917052375001', 'Entendido. Para el atornillador, ¿te refieres a tornillos para drywall o algún otro tipo de aplicación específica?\n\nSi deseas armar un pedido, por favor, indícame las cantidades que necesitas.', 'ia', '2026-04-13 15:44:19'),
(211, '0917052375001', '5', 'cliente', '2026-04-13 15:44:28'),
(212, '0917052375001', 'Por favor, especifica a qué producto te refieres con \"5\". ¿Son 5 unidades de algún tipo de tornillo en particular? Necesito que seas más específico para poder ayudarte.', 'ia', '2026-04-13 15:44:28'),
(213, '0917052375001', '5 unidades de tornillos ne tirillas para drywall', 'cliente', '2026-04-13 15:44:52'),
(214, '0917052375001', 'No encuentro un producto llamado \"tornillos ne tirillas para drywall\" en nuestro inventario actual. ¿Podrías verificar el nombre o describirme mejor el producto que necesitas? Quizás se trate de tornillos para fijación de perfiles o algún otro accesorio.', 'ia', '2026-04-13 15:44:52'),
(215, '0917052375001', 'seguro?', 'cliente', '2026-04-13 15:45:10'),
(216, '0917052375001', 'Sí, estoy seguro. He revisado nuestro inventario y no encuentro un producto con el nombre exacto \"tornillos ne tirillas para drywall\".\n\nSin embargo, sí contamos con \"Tornillos en tirillas\" de la marca MAXXT. ¿Podría ser este el producto que buscas? Su precio es de $7.56 por presentación única.\n\nSi deseas armar un pedido, por favor, házmelo saber.', 'ia', '2026-04-13 15:45:10'),
(217, '0917052375001', 'si es ese', 'cliente', '2026-04-13 15:45:28'),
(218, '0917052375001', 'Perfecto. ¿Cuántas unidades de \"Tornillos en tirillas\" (MAXXT) deseas agregar a tu pedido?', 'ia', '2026-04-13 15:45:28'),
(219, '0917052375001', '5', 'cliente', '2026-04-13 15:45:33'),
(220, '0917052375001', 'Entendido. Has agregado 5 unidades de \"Tornillos en tirillas\" (MAXXT) a tu pedido.\n\n¿Hay algún otro producto que necesites o deseas agregar?', 'ia', '2026-04-13 15:45:33'),
(221, '0917052375001', 'no, solo eso', 'cliente', '2026-04-13 15:45:41'),
(222, '0917052375001', 'Perfecto. Aquí tienes tu borrador de pedido:\n\n*   Tornillos en tirillas (MAXXT) - 5 unidades\n\n¿Estás de acuerdo con este borrador para generar tu cotización formal?', 'ia', '2026-04-13 15:45:41'),
(223, '0917052375001', 'si', 'cliente', '2026-04-13 15:45:49'),
(224, '0917052375001', '¡Perfecto! Aquí tienes tu cotización formal. Por ser cliente B2B tienes acceso al 10% de descuento adicional en todas tus compras.\n\n| Img                                                              | Producto                  | Uni. | Precio  |\n| :--------------------------------------------------------------- | :------------------------ | :--- | :------ |\n| [VER_IMAGEN](../img_catalogo/20TORTLL.webp)                      | Tornillos en tirillas     | 5    | $7.56   |\n|                                                                  |                           |      | <small>Subtotal: $37.80</small> |\n|                                                                  | **TOTAL B2B**             |      | **$34.02** |\n\nPara procesar tu pedido, haz clic aquí: **[Cerrar Pedido en WhatsApp](https://wa.me/593991754887?text=Hola+IMPROGYP%2C+soy+Nabil.+Deseo+procesar+mi+pedido+B2B)**', 'ia', '2026-04-13 15:45:49'),
(225, '0917052375001', 'hola', 'cliente', '2026-04-13 22:50:53'),
(226, '0917052375001', '¡Hola! Bienvenido a IMPROGYP. Soy tu Asesor Técnico Especialista en construcción en seco. ¿En qué puedo ayudarte hoy? ¿Estás buscando información sobre algún producto en particular o deseas armar un pedido?', 'ia', '2026-04-13 22:50:53'),
(227, '0917052375001', 'atornilladora', 'cliente', '2026-04-13 22:50:59'),
(228, '0917052375001', 'Tenemos varios modelos de atornilladoras disponibles. ¿Buscas alguna en específico, por ejemplo, inalámbrica o para un uso particular como drywall?', 'ia', '2026-04-13 22:50:59'),
(229, '0917052375001', 'drywall', 'cliente', '2026-04-13 22:51:06'),
(230, '0917052375001', 'Perfecto. Contamos con el **Atornillador Gypsum Inalámbrico MAXXT (CÓDIGO: 20MDSG20V)**. Este equipo está diseñado para optimizar la productividad en trabajos de drywall.\n\n¿Te gustaría conocer más detalles técnicos o deseas agregar este producto a tu pedido?', 'ia', '2026-04-13 22:51:06'),
(231, '0917052375001', 'ccotizar', 'cliente', '2026-04-13 22:51:14'),
(232, '0917052375001', '¡Perfecto! Aquí tienes tu cotización formal. Por ser cliente B2B tienes acceso al 10% de descuento adicional en todas tus compras.\n\n| Img | Producto | Uni. | Precio |\n|---|---|---|---|\n| [VER_IMAGEN](../img_catalogo/20MDSG20V.webp) | Atornillador Gypsum Inalámbrico MAXXT | 1 | $385.11 <br> <small>Subtotal: $385.11</small> |\n| | **TOTAL B2B** | | **$346.60** |\n\nPara procesar tu pedido, haz clic aquí: **[Cerrar Pedido en WhatsApp](https://wa.me/593991754887?text=Hola+IMPROGYP%2C+soy+Nabil.+Deseo+procesar+mi+pedido+B2B)**', 'ia', '2026-04-13 22:51:14'),
(233, '0917052375001', 'cotizar lijdora de yeso', 'cliente', '2026-04-13 22:54:33'),
(234, '0917052375001', 'Estimado cliente,\n\nCon gusto le asistiré con la cotización de lijadoras de paneles de yeso. Por favor, especifique el modelo o las características que busca para poder ofrecerle la opción más adecuada.\n\n¿Desea que proceda a armar un borrador con las opciones disponibles?', 'ia', '2026-04-13 22:54:33'),
(235, '0917052375001', 'inalambrica', 'cliente', '2026-04-13 22:54:43'),
(236, '0917052375001', 'Perfecto. Tenemos las siguientes opciones de lijadoras de paneles de yeso inalámbricas disponibles:\n\n*   **Lijadora de paneles de yeso inalámbrica** (MAXXT, 20MDLS20V) - $96.66\n*   **Lijadora orbital inalámbrica** (MAXXT, 20MOS20VDII) - $119.69\n*   **Lijadora orbital inalámbrica** (MAXXT, 20MOS20VSII) - $143.97\n*   **Lijadora orbital inalámbrica** (MAXXT, 20MOS20VCII) - $194.41\n\n¿Desea que proceda a generar una cotización formal con alguna de estas opciones?', 'ia', '2026-04-13 22:54:43'),
(237, '0917052375001', 'dame todo el catalogo', 'cliente', '2026-04-13 22:55:34'),
(238, '0917052375001', 'Como su Asesor Técnico Especialista, mi objetivo es brindarle la información más precisa y útil para sus necesidades. Sin embargo, para optimizar su experiencia y cumplir con las directrices de nuestro servicio, solo puedo presentarle un máximo de 5 productos por consulta, a menos que se trate de una cotización formal.\n\nSi está interesado en algún tipo de producto en particular, por favor, indíqueme para poder asistirle de la mejor manera.', 'ia', '2026-04-13 22:55:34'),
(239, '0917052375001', 'Lijadora orbital inalámbric', 'cliente', '2026-04-13 22:56:55'),
(240, '0917052375001', 'Entendido. Aquí tiene las opciones de lijadoras orbitales inalámbricas disponibles en nuestro catálogo:\n\n*   **Lijadora orbital inalámbrica** (MAXXT, 20MOS20VDII) - $119.69\n*   **Lijadora orbital inalámbrica** (MAXXT, 20MOS20VSII) - $143.97\n*   **Lijadora orbital inalámbrica** (MAXXT, 20MOS20VCII) - $194.41\n\n¿Desea agregar alguna de estas a su pedido o tiene alguna otra consulta técnica?', 'ia', '2026-04-13 22:56:55'),
(241, '0917052375001', 'Lijadora orbital inalámbrica (MAXXT, 20MOS20VDII)', 'cliente', '2026-04-13 22:57:02'),
(242, '0917052375001', 'Perfecto. Ha seleccionado la **Lijadora orbital inalámbrica (MAXXT, 20MOS20VDII)**.\n\n¿Desea agregar este producto a su pedido o necesita información sobre algún otro artículo?', 'ia', '2026-04-13 22:57:02'),
(243, '0917052375001', 'solo eso', 'cliente', '2026-04-13 22:57:07'),
(244, '0917052375001', 'Entendido. Ha seleccionado únicamente la **Lijadora orbital inalámbrica (MAXXT, 20MOS20VDII)**.\n\n¿Está de acuerdo con este borrador para generar su cotización formal?', 'ia', '2026-04-13 22:57:07'),
(245, '0917052375001', 'si', 'cliente', '2026-04-13 22:57:42'),
(246, '0917052375001', '¡Perfecto! Aquí tienes tu cotización formal. Por ser cliente B2B tienes acceso al 10% de descuento adicional en todas tus compras.\n\n| Img | Producto | Uni. | Precio |\n|---|---|---|---|\n| [VER_IMAGEN](../img_catalogo/20MOS20VDII.webp) | Lijadora orbital inalámbrica | 1 | $119.69 <br> <small>Subtotal: $119.69</small> |\n| | **TOTAL B2B** | | **$107.72** |\n\nPara procesar tu pedido, haz clic aquí: **[Cerrar Pedido en WhatsApp](https://wa.me/593991754887?text=Hola+IMPROGYP%2C+soy+Nabil.+Deseo+procesar+mi+pedido+B2B)**', 'ia', '2026-04-13 22:57:42'),
(247, '0917052365002', 'Buen día necesito la placa para exterior spglass', 'cliente', '2026-04-14 14:05:19'),
(248, '0917052365002', 'Buen día. Como Asesor Técnico Especialista de IMPROGYP, puedo informarle que actualmente no contamos con el producto \"placa para exterior spglass\" en nuestro inventario.\n\n¿Puedo ayudarle con algún otro producto de nuestro catálogo de Drywall, Steel Framing o herramientas?', 'ia', '2026-04-14 14:05:19'),
(249, '0917052365002', 'Que tienes sobre los productos de Knauf', 'cliente', '2026-04-14 14:05:44'),
(250, '0917052365002', 'Buen día. Como Asesor Técnico Especialista de IMPROGYP, le informo que nuestro inventario actual se enfoca en la marca MAXXT. No disponemos de productos de la marca Knauf en este momento.\n\n¿Le interesaría conocer nuestra línea de productos MAXXT en Drywall, Steel Framing o herramientas?', 'ia', '2026-04-14 14:05:44'),
(251, '0917052365002', 'Que herramientas de Maxxt tienes', 'cliente', '2026-04-14 14:06:07'),
(252, '0917052365002', 'Buen día. En IMPROGYP contamos con una variedad de herramientas MAXXT. Para poderle asistir de la mejor manera, ¿podría indicarme qué tipo de herramienta está buscando? Por ejemplo, ¿algo para corte, fijación, lijado, o alguna otra categoría?', 'ia', '2026-04-14 14:06:07'),
(253, '0917052365002', 'Para drywall', 'cliente', '2026-04-14 14:06:24'),
(254, '0917052365002', 'Entendido. Para el área de Herramientas Drywall, contamos con los siguientes productos MAXXT:\n\n*   **Clavadora inalámbrica** (CÓDIGO: 20MDNG20V)\n*   **Herramienta de corte sin cable** (CÓDIGO: 20MDCOT20V)\n*   **Varilla de extensión para atornillador 24\"** (CÓDIGO: 20MER600)\n*   **Accesorio de cargador para atornillador** (CÓDIGO: 20MCM57)\n*   **Atornillador Gypsum Inalámbrico** (CÓDIGO: 20MDSG20V)\n\n¿Desea armar un pedido con alguno de estos productos o tiene alguna otra consulta técnica?', 'ia', '2026-04-14 14:06:24'),
(255, '0917052375001', 'hola', 'cliente', '2026-04-14 22:06:51'),
(256, '0917052375001', '¡Hola! Bienvenido a IMPROGYP. Soy tu Asesor Técnico Especialista en construcción en seco. ¿En qué puedo ayudarte hoy? ¿Estás buscando información sobre algún producto en particular o deseas armar un pedido?', 'ia', '2026-04-14 22:06:51'),
(257, '0917052365002', 'Tienes herramientas automotriz', 'cliente', '2026-04-14 22:07:55'),
(258, '0917052365002', 'Sí, contamos con herramientas MAXXT para el área automotriz. Específicamente, tenemos la siguiente:\n\n*   **Pistola de llave de impacto automotriz** (CÓDIGO: 20MDIW20V)\n\n¿Le gustaría añadir este producto a su pedido o necesita información sobre alguna otra herramienta?', 'ia', '2026-04-14 22:07:55'),
(259, '0917052375001', 'que categorias tienes', 'cliente', '2026-04-14 22:07:56'),
(260, '0917052375001', 'En IMPROGYP contamos con una amplia gama de productos para construcción en seco, organizados en las siguientes categorías principales:\n\n*   **Accesorios:** Incluye elementos esenciales como tornillos, ángulos, cepillos, discos de corte y lijas, entre otros.\n*   **Herramientas Varias:** Una selección de herramientas versátiles para diversas aplicaciones.\n*   **Sopladoras:** Equipos diseñados para tareas de soplado y limpieza.\n*   **Pistolas de Silicona:** Herramientas para la aplicación de selladores y adhesivos.\n*   **Pistolas de Aire Caliente:** Equipos para aplicaciones que requieren calor.\n*   **Pulverizadores de Pintura:** Soluciones para la aplicación eficiente de pintura.\n*   **Cortadoras:** Herramientas para realizar cortes precisos en diversos materiales.\n*   **Línea de Concreto:** Productos especializados para trabajos con concreto.\n*   **Amoladoras:** Equipos para desbaste, corte y pulido.\n*   **Taladros Percutores:** Herramientas versátiles para perforación.\n*   **Mezclador de Paletas:** Equipos para mezclar materiales de construcción.\n*   **Limpieza al Vacío:** Aspiradoras y extractores de polvo.\n*   **Lijadoras Orbitales:** Herramientas para lijado de superficies.\n*   **Lijadoras de Paneles de Yeso:** Equipos especializados para el acabado de drywall.\n*   **Herramientas Drywall:** Herramientas específicas para la instalación de sistemas en seco.\n\n¿Hay alguna categoría en particular que te interese o algún producto específico que estés buscando?', 'ia', '2026-04-14 22:07:56'),
(261, '0917052365002', 'Tiene algún número con el que pueda hablar', 'cliente', '2026-04-14 22:09:05'),
(262, '0917052365002', 'Para procesar tu pedido, haz clic aquí: **[Cerrar Pedido en WhatsApp](https://wa.me/593991754887?text=Hola+IMPROGYP%2C+soy+Manuel.+Deseo+procesar+mi+pedido+B2B)**', 'ia', '2026-04-14 22:09:05');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `categorias_admin`
--

CREATE TABLE `categorias_admin` (
  `id` int(11) NOT NULL,
  `nombre` varchar(100) NOT NULL,
  `fecha_creacion` timestamp NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `categorias_admin`
--

INSERT INTO `categorias_admin` (`id`, `nombre`, `fecha_creacion`) VALUES
(1, 'Herramientas Drywall', '2026-04-06 08:41:09'),
(2, 'Lijadoras de Paneles de Yeso', '2026-04-06 08:41:09'),
(3, 'Lijadoras Orbitales', '2026-04-06 08:41:09'),
(4, 'Limpieza al Vacío', '2026-04-06 08:41:09'),
(5, 'Mezclador de Paletas', '2026-04-06 08:41:09'),
(6, 'Taladros Percutores', '2026-04-06 08:41:09'),
(7, 'Amoladoras', '2026-04-06 08:41:09'),
(8, 'Línea de Concreto', '2026-04-06 08:41:09'),
(9, 'Cortadoras', '2026-04-06 08:41:09'),
(10, 'Pulverizadores de Pintura', '2026-04-06 08:41:09'),
(11, 'Pistolas de Aire Caliente', '2026-04-06 08:41:09'),
(12, 'Pistolas de Silicona', '2026-04-06 08:41:09'),
(13, 'Sopladoras', '2026-04-06 08:41:09'),
(14, 'Herramientas Varias', '2026-04-06 08:41:09'),
(15, 'Accesorios', '2026-04-06 08:41:09');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `improgyp_catalogo`
--

CREATE TABLE `improgyp_catalogo` (
  `id` int(11) NOT NULL,
  `nombre` varchar(255) NOT NULL,
  `codigo` varchar(100) DEFAULT NULL,
  `marca` varchar(100) DEFAULT NULL,
  `categoria` varchar(100) DEFAULT NULL,
  `presentaciones_raw` text DEFAULT NULL,
  `desc_larga` text DEFAULT NULL,
  `datos_tecnicos` text DEFAULT NULL,
  `imagen_url` text DEFAULT NULL,
  `publicado` tinyint(1) DEFAULT 1,
  `fecha_creacion` timestamp NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `improgyp_catalogo`
--

INSERT INTO `improgyp_catalogo` (`id`, `nombre`, `codigo`, `marca`, `categoria`, `presentaciones_raw`, `desc_larga`, `datos_tecnicos`, `imagen_url`, `publicado`, `fecha_creacion`) VALUES
(96, 'Atornillador Gypsum Inalámbrico', '20MDSG20V', 'MAXXT', 'Herramientas Drywall', 'Presentación Única: 385.11', 'Potencia tu productividad con el Atornillador Gypsum Inalámbrico de MAXXT, la solución ideal para el área de Herramientas Drywall.\n\nOfrece un rendimiento superior con las siguientes características: Fuerza: 20V | Velocidad en ralentí: 0-4200rpm | Tamaño del mandril: 1\\/4\" (6.35mm) | Peso: 3.5lbs (1.5kgs). Su diseño ergonómico reduce la fatiga y mejora la precisión en el trabajo diario, asegurando instalaciones rápidas y seguras.\n\nToma el poder en tus manos con el respaldo de MAXXT Ecuador. Disfruta de 1 año de garantía contra defectos de fábrica y asesoría técnica especializada.', 'Fuerza: 20V | Velocidad en ralentí: 0-4200rpm | Tamaño del mandril: 1\\/4\" (6.35mm) | Peso: 3.5lbs (1.5kgs)', 'img_catalogo/20MDSG20V.webp', 1, '2026-04-06 13:06:10'),
(97, 'Accesorio de cargador para atornillador', '20MCM57', 'MAXXT', 'Herramientas Drywall', 'Presentación Única: 263.05', 'Potencia tu productividad con el Accesorio de cargador para atornillador de MAXXT, la solución ideal para el área de Herramientas Drywall.\n\nOfrece un rendimiento superior con las siguientes características: Tamaño del tornillo: 25-57mm | Peso: 2lbs (1kgs). Su diseño profesional reduce la fatiga y acelera la alimentación de tornillos, optimizando el tiempo de trabajo continuo.\n\nToma el poder en tus manos con el respaldo de MAXXT Ecuador. Disfruta de 1 año de garantía contra defectos de fábrica y asesoría técnica especializada.', 'Tamaño del tornillo: 25-57mm | Peso: 2lbs (1kgs)', 'img_catalogo/20MCM57.webp', 1, '2026-04-06 13:06:10'),
(98, 'Varilla de extensión para atornillador 24\"', '20MER600', 'MAXXT', 'Herramientas Drywall', 'Presentación Única: 297.38', 'Potencia tu productividad con la Varilla de extensión para atornillador 24\" de MAXXT, la solución ideal para el área de Herramientas Drywall.\n\nOfrece un alcance superior con las siguientes características: Longitud de extensión: 24\" (600mm). Su diseño robusto te permite acceder a zonas altas o de difícil alcance sin perder estabilidad ni precisión en el atornillado.\n\nToma el poder en tus manos con el respaldo de MAXXT Ecuador. Disfruta de 1 año de garantía contra defectos de fábrica y asesoría técnica especializada.', 'Longitud de extensión: 24\" (600mm)', 'img_catalogo/20MER600.webp', 1, '2026-04-06 13:06:10'),
(99, 'Herramienta de corte sin cable', '20MDCOT20V', 'MAXXT', 'Herramientas Drywall', 'Presentación Única: 327.26', 'Potencia tu productividad con la Herramienta de corte sin cable de MAXXT, la solución ideal para el área de Herramientas Drywall.\n\nOfrece un rendimiento superior con las siguientes características: Fuerza: 20V | Velocidad en ralentí: 28000rpm | Espesor máximo de corte: 1\" (25.4mm) | Colección: 1\\/8\" y 1\\/4\". Su diseño ergonómico reduce la fatiga y mejora la precisión para cortes limpios en paneles de yeso.\n\nToma el poder en tus manos con el respaldo de MAXXT Ecuador. Disfruta de 1 año de garantía contra defectos de fábrica y asesoría técnica especializada.', 'Fuerza: 20V | Velocidad en ralentí: 28000rpm | Espesor máximo de corte: 1\" (25.4mm) | Colección: 1\\/8\" y 1\\/4\"', 'img_catalogo/20MDCOT20V.webp', 1, '2026-04-06 13:06:10'),
(100, 'Clavadora inalámbrica', '20MDNG20V', 'MAXXT', 'Herramientas Drywall', 'Presentación Única: 340.79', 'Potencia tu productividad con la Clavadora inalámbrica de MAXXT, la solución ideal para el área de Herramientas Drywall.\n\nOfrece un rendimiento superior con las siguientes características: Fuerza: 20V | Tamaño uña: Ø2.6-03.0mm (L: 15-40mm) | Capacidad: 33 clavos | Tiempo de funcionamiento: ~800 clavos. Elimina la necesidad de mangueras y compresores, brindando total libertad de movimiento.\n\nToma el poder en tus manos con el respaldo de MAXXT Ecuador. Disfruta de 1 año de garantía contra defectos de fábrica y asesoría técnica especializada.', 'Fuerza: 20V | Tamaño uña: Ø2.6-03.0mm (L: 15-40mm) | Capacidad: 33 clavos | Tiempo de funcionamiento: ~800 clavos', 'img_catalogo/20MDNG20V.webp', 1, '2026-04-06 13:06:10'),
(101, 'Lijadora de paneles de yeso', '20MBLS400', 'MAXXT', 'Lijadoras de Paneles de Yeso', 'Presentación Única: 148.14', 'Potencia tu productividad con la Lijadora de paneles de yeso de MAXXT, la solución ideal para el área de Lijadoras de Paneles de Yeso.\n\nOfrece un rendimiento superior con las siguientes características: Fuerza: 400W | Velocidad: 4000-8500rpm | Diámetro almohadilla: 8.5\" (215mm) | Peso: 7lbs (3.2kg). Su diseño equilibrado asegura un lijado uniforme en paredes y techos, optimizando el acabado superficial.\n\nToma el poder en tus manos con el respaldo de MAXXT Ecuador. Disfruta de 1 año de garantía contra defectos de fábrica y asesoría técnica especializada.', 'Fuerza: 400W | Velocidad: 4000-8500rpm | Diámetro almohadilla: 8.5\" (215mm) | Peso: 7lbs (3.2kg)', 'img_catalogo/20MBLS400.webp', 1, '2026-04-06 13:06:10'),
(102, 'Lijadora de paneles de yeso', '20MBLS550', 'MAXXT', 'Lijadoras de Paneles de Yeso', 'Presentación Única: 157.79', 'Potencia tu productividad con la Lijadora de paneles de yeso de MAXXT, la solución ideal para el área de Lijadoras de Paneles de Yeso.\n\nOfrece un rendimiento superior con las siguientes características: Fuerza: 550W | Velocidad: 600-1200rpm | Diámetro almohadilla: 8.5\" (215mm) | Peso: 6.6lbs (3kg). Su motor de alta resistencia permite jornadas continuas de trabajo con mínima vibración.\n\nToma el poder en tus manos con el respaldo de MAXXT Ecuador. Disfruta de 1 año de garantía contra defectos de fábrica y asesoría técnica especializada.', 'Fuerza: 550W | Velocidad: 600-1200rpm | Diámetro almohadilla: 8.5\" (215mm) | Peso: 6.6lbs (3kg)', 'img_catalogo/20MBLS550.webp', 1, '2026-04-06 13:06:10'),
(103, 'Lijadora de paneles de yeso con extensión', '20MLS820B', 'MAXXT', 'Lijadoras de Paneles de Yeso', 'Presentación Única: 99.73', 'Potencia tu productividad con la Lijadora de paneles de yeso con extensión de MAXXT, la solución ideal para el área de Lijadoras de Paneles de Yeso.\n\nOfrece un rendimiento superior con las siguientes características: Incluye: 18 lijas, tubo de extensión, manguera 2m, bolsa recolectora y adaptadores. Su tubo telescópico facilita el acceso a techos y zonas altas, mejorando la comodidad del operador.\n\nToma el poder en tus manos con el respaldo de MAXXT Ecuador. Disfruta de 1 año de garantía contra defectos de fábrica y asesoría técnica especializada.', 'Incluye: 18 lijas, tubo de extensión, manguera 2m, bolsa recolectora y adaptadores', 'img_catalogo/20MLS820B.webp', 1, '2026-04-06 13:06:10'),
(104, 'Lijadora de paneles de yeso con extensión', '20MLS820P', 'MAXXT', 'Lijadoras de Paneles de Yeso', 'Presentación Única: 94.25', 'Potencia tu productividad con la Lijadora de paneles de yeso con extensión de MAXXT, la solución ideal para el área de Lijadoras de Paneles de Yeso.\n\nOfrece un rendimiento superior con las siguientes características: Incluye: 18 lijas, tubo de extensión, manguera 2m y adaptador. Equipada para brindar una cobertura de lijado excepcional con una extracción de polvo eficiente.\n\nToma el poder en tus manos con el respaldo de MAXXT Ecuador. Disfruta de 1 año de garantía contra defectos de fábrica y asesoría técnica especializada.', 'Incluye: 18 lijas, tubo de extensión, manguera 2m y adaptador', 'img_catalogo/20MLS820P.webp', 1, '2026-04-06 13:06:10'),
(105, 'Lijadora de paneles de yeso con extensión', '20MLS820', 'MAXXT', 'Lijadoras de Paneles de Yeso', 'Presentación Única: 121.55', 'Potencia tu productividad con la Lijadora de paneles de yeso con extensión de MAXXT, la solución ideal para el área de Lijadoras de Paneles de Yeso.\n\nOfrece un rendimiento superior con las siguientes características: Fuerza: 820W | Velocidad: 600-1900rpm | Diámetro almohadilla: 8.5\" (215mm) | Peso: 7.7lbs (3.5kg). Combina potencia bruta con control preciso para acabados profesionales en grandes superficies.\n\nToma el poder en tus manos con el respaldo de MAXXT Ecuador. Disfruta de 1 año de garantía contra defectos de fábrica y asesoría técnica especializada.', 'Fuerza: 820W | Velocidad: 600-1900rpm | Diámetro almohadilla: 8.5\" (215mm) | Peso: 7.7lbs (3.5kg)', 'img_catalogo/20MLS820.webp', 1, '2026-04-06 13:06:10'),
(107, 'Lijadora de paneles de yeso', '20MLS710EP', 'MAXXT', 'Lijadoras de Paneles de Yeso', 'Presentación Única: 196.24', 'Potencia tu productividad con la Lijadora de paneles de yeso de MAXXT, la solución ideal para el área de Lijadoras de Paneles de Yeso.\n\nOfrece un rendimiento superior con las siguientes características: Fuerza: 710W | Velocidad: 600-1500rpm | Diámetro almohadilla: 8.5\" (215mm) | Peso: 8.4lbs (3.8kgs). Ideal para preparar superficies antes de pintar, asegurando un trabajo rápido y limpio.\n\nToma el poder en tus manos con el respaldo de MAXXT Ecuador. Disfruta de 1 año de garantía contra defectos de fábrica y asesoría técnica especializada.', 'Fuerza: 710W | Velocidad: 600-1500rpm | Diámetro almohadilla: 8.5\" (215mm) | Peso: 8.4lbs (3.8kgs)', 'img_catalogo/20MLS710EP.webp', 1, '2026-04-06 13:06:10'),
(108, 'Lijadora de paneles de yeso', '20MLS710SGP', 'MAXXT', 'Lijadoras de Paneles de Yeso', 'Presentación Única: 165.58', 'Potencia tu productividad con la Lijadora de paneles de yeso de MAXXT, la solución ideal para el área de Lijadoras de Paneles de Yeso.\n\nOfrece un rendimiento superior con las siguientes características: Fuerza: 710W | Velocidad: 600-1500rpm | Diámetro almohadilla: 8.5\" (215mm) | Peso: 8.4lbs (3.8kgs). Proporciona un lijado constante y sin marcas, elevando el estándar de tus acabados.\n\nToma el poder en tus manos con el respaldo de MAXXT Ecuador. Disfruta de 1 año de garantía contra defectos de fábrica y asesoría técnica especializada.', 'Fuerza: 710W | Velocidad: 600-1500rpm | Diámetro almohadilla: 8.5\" (215mm) | Peso: 8.4lbs (3.8kgs)', 'img_catalogo/20MLS710SGP.webp', 1, '2026-04-06 13:06:10'),
(109, 'Lijadora de paneles de yeso', '20MS810', 'MAXXT', 'Lijadoras de Paneles de Yeso', 'Presentación Única: 111.44', 'Potencia tu productividad con la Lijadora de paneles de yeso de MAXXT, la solución ideal para el área de Lijadoras de Paneles de Yeso.\n\nOfrece un rendimiento superior con las siguientes características: Fuerza: 810W | Velocidad: 1500-2800rpm | Diámetro almohadilla: 7\" (180mm) | Peso: 6.4lbs (2.9kgs). Una máquina robusta y compacta, diseñada para eliminar imperfecciones con gran agilidad.\n\nToma el poder en tus manos con el respaldo de MAXXT Ecuador. Disfruta de 1 año de garantía contra defectos de fábrica y asesoría técnica especializada.', 'Fuerza: 810W | Velocidad: 1500-2800rpm | Diámetro almohadilla: 7\" (180mm) | Peso: 6.4lbs (2.9kgs)', 'img_catalogo/20MS810.webp', 1, '2026-04-06 13:06:10'),
(110, 'Lijadora de paneles de yeso inalámbrica', '20MDLS20V', 'MAXXT', 'Lijadoras de Paneles de Yeso', 'Presentación Única: 96.66', 'Potencia tu productividad con la Lijadora de paneles de yeso inalámbrica de MAXXT, la solución ideal para el área de Lijadoras de Paneles de Yeso.\n\nOfrece un rendimiento superior con las siguientes características: Fuerza: 20V | Velocidad: 600-1200rpm | Diámetro almohadilla: 8.5\" (215mm) | Peso: 7.3lbs (3.3kgs). Libérate de los cables mientras mantienes la potencia necesaria para un lijado profesional y eficiente.\n\nToma el poder en tus manos con el respaldo de MAXXT Ecuador. Disfruta de 1 año de garantía contra defectos de fábrica y asesoría técnica especializada.', 'Fuerza: 20V | Velocidad: 600-1200rpm | Diámetro almohadilla: 8.5\" (215mm) | Peso: 7.3lbs (3.3kgs)', 'img_catalogo/20MDLS20V.webp', 1, '2026-04-06 13:06:10'),
(111, 'Lijadora orbital sin escobillas', '', 'MAXXT', 'Lijadoras Orbitales', 'Presentación Única: 194.22', 'Potencia tu productividad con la Lijadora orbital sin escobillas de MAXXT, la solución ideal para el área de Lijadoras Orbitales.\n\nOfrece un rendimiento superior con las siguientes características: Fuerza: 400W | Velocidad: 4000-10000rpm | Trazo: 3\\/16\" (5mm) | Diámetro: 6\" (150mm) | Peso: 2.5lbs. Su motor brushless garantiza menor desgaste, mayor eficiencia y un acabado libre de marcas circulares.\n\nToma el poder en tus manos con el respaldo de MAXXT Ecuador. Disfruta de 1 año de garantía contra defectos de fábrica y asesoría técnica especializada.', 'Fuerza: 400W | Velocidad: 4000-10000rpm | Trazo: 3\\/16\" (5mm) | Diámetro: 6\" (150mm) | Peso: 2.5lbs', 'img_catalogo/20MOS350S.webp', 1, '2026-04-06 13:06:10'),
(112, 'Lijadora orbital sin escobillas', '20MOS400C-2.5', 'MAXXT', 'Lijadoras Orbitales', 'Presentación Única: 85.84', 'Potencia tu productividad con la Lijadora orbital sin escobillas de MAXXT, la solución ideal para el área de Lijadoras Orbitales.\n\nOfrece un rendimiento superior con las siguientes características: Fuerza: 400W | Velocidad: 4000-10000rpm | Trazo: 1\\/8\" (3mm) | Diámetro: 6\" (150mm) | Peso: 2.5lbs. Diseñada para un pulido extra fino y control absoluto en trabajos detallados de carpintería y pintura.\n\nToma el poder en tus manos con el respaldo de MAXXT Ecuador. Disfruta de 1 año de garantía contra defectos de fábrica y asesoría técnica especializada.', 'Fuerza: 400W | Velocidad: 4000-10000rpm | Trazo: 1\\/8\" (3mm) | Diámetro: 6\" (150mm) | Peso: 2.5lbs', 'img_catalogo/20MOS400C-2.5.webp', 1, '2026-04-06 13:06:10'),
(113, 'Lijadora orbital sin escobillas', '20MOS400R', 'MAXXT', 'Lijadoras Orbitales', 'Presentación Única: 109.99', 'Potencia tu productividad con la Lijadora orbital sin escobillas de MAXXT, la solución ideal para el área de Lijadoras Orbitales.\n\nOfrece un rendimiento superior con las siguientes características: Fuerza: 400W | Velocidad: 4000-10000rpm | Trazo: 3\\/32\" (2.5mm) | Diámetro: 6\" (150mm) | Peso: 2.5lbs. Su órbita ajustada permite un trabajo meticuloso y suave sobre cualquier superficie.\n\nToma el poder en tus manos con el respaldo de MAXXT Ecuador. Disfruta de 1 año de garantía contra defectos de fábrica y asesoría técnica especializada.', 'Fuerza: 400W | Velocidad: 4000-10000rpm | Trazo: 3\\/32\" (2.5mm) | Diámetro: 6\" (150mm) | Peso: 2.5lbs', 'img_catalogo/20MOS400R.webp', 1, '2026-04-06 13:06:10'),
(114, 'Lijadora orbital sin escobillas', '20MOS400D', 'MAXXT', 'Lijadoras Orbitales', 'Presentación Única: 131.69', 'Potencia tu productividad con la Lijadora orbital sin escobillas de MAXXT, la solución ideal para el área de Lijadoras Orbitales.\n\nOfrece un rendimiento superior con las siguientes características: Fuerza: 400W | Velocidad: 4000-10000rpm | Trazo: 1\\/8\" (3mm) | Dimensiones: 100x152x152mm | Peso: 2.5lbs. Su formato permite llegar a bordes y áreas planas con total estabilidad.\n\nToma el poder en tus manos con el respaldo de MAXXT Ecuador. Disfruta de 1 año de garantía contra defectos de fábrica y asesoría técnica especializada.', 'Fuerza: 400W | Velocidad: 4000-10000rpm | Trazo: 1\\/8\" (3mm) | Dimensiones: 100x152x152mm | Peso: 2.5lbs', 'img_catalogo/20MOS400D.webp', 1, '2026-04-06 13:06:10'),
(115, 'Lijadora orbital sin escobillas', '20MOS400S', 'MAXXT', 'Lijadoras Orbitales', 'Presentación Única: 128.29', 'Potencia tu productividad con la Lijadora orbital sin escobillas de MAXXT, la solución ideal para el área de Lijadoras Orbitales.\n\nOfrece un rendimiento superior con las siguientes características: Fuerza: 400W | Velocidad: 4000-10000rpm | Trazo: 1\\/8\" (3mm) | Dimensiones: 81x133mm | Peso: 2.5lbs. Su diseño rectangular compacto es perfecto para trabajos en esquinas y perfilería.\n\nToma el poder en tus manos con el respaldo de MAXXT Ecuador. Disfruta de 1 año de garantía contra defectos de fábrica y asesoría técnica especializada.', 'Fuerza: 400W | Velocidad: 4000-10000rpm | Trazo: 1\\/8\" (3mm) | Dimensiones: 81x133mm | Peso: 2.5lbs', 'img_catalogo/20MOS400S.webp', 1, '2026-04-06 13:06:10'),
(116, 'Lijadora orbital sin escobillas', '20MOS350C-5.0', 'MAXXT', 'Lijadoras Orbitales', 'Presentación Única: 195.86', 'Potencia tu productividad con la Lijadora orbital sin escobillas de MAXXT, la solución ideal para el área de Lijadoras Orbitales.\n\nOfrece un rendimiento superior con las siguientes características: Fuerza: 350W | Velocidad: 4000-10000rpm | Trazo: 3\\/16\" (5mm) | Diámetro: 6\" (150mm) | Peso: 2.5lbs. Equilibrio perfecto entre potencia y ligereza para remoción de material rápida y efectiva.\n\nToma el poder en tus manos con el respaldo de MAXXT Ecuador. Disfruta de 1 año de garantía contra defectos de fábrica y asesoría técnica especializada.', 'Fuerza: 350W | Velocidad: 4000-10000rpm | Trazo: 3\\/16\" (5mm) | Diámetro: 6\" (150mm) | Peso: 2.5lbs', 'img_catalogo/20MOS350C-5.0.webp', 1, '2026-04-06 13:06:10'),
(117, 'Lijadora orbital sin escobillas', '20MOS350C-2.5', 'MAXXT', 'Lijadoras Orbitales', 'Presentación Única: 151.0', 'Potencia tu productividad con la Lijadora orbital sin escobillas de MAXXT, la solución ideal para el área de Lijadoras Orbitales.\n\nOfrece un rendimiento superior con las siguientes características: Fuerza: 350W | Velocidad: 4000-10000rpm | Trazo: 3\\/32\" (2.5mm) | Diámetro: 6\" (150mm) | Peso: 2.5lbs. Proporciona acabados excepcionales sin fatiga gracias a su bajo peso y motor inteligente.\n\nToma el poder en tus manos con el respaldo de MAXXT Ecuador. Disfruta de 1 año de garantía contra defectos de fábrica y asesoría técnica especializada.', 'Fuerza: 350W | Velocidad: 4000-10000rpm | Trazo: 3\\/32\" (2.5mm) | Diámetro: 6\" (150mm) | Peso: 2.5lbs', 'img_catalogo/20MOS350C-2.5.webp', 1, '2026-04-06 13:06:10'),
(118, 'Lijadora orbital sin escobillas', '20MOS350R', 'MAXXT', 'Lijadoras Orbitales', 'Presentación Única: 179.27', 'Potencia tu productividad con la Lijadora orbital sin escobillas de MAXXT, la solución ideal para el área de Lijadoras Orbitales.\n\nOfrece un rendimiento superior con las siguientes características: Fuerza: 350W | Velocidad: 4000-10000rpm | Trazo: 1\\/8\" (3mm) | Dimensiones: 198x70mm | Peso: 2.5lbs. Su formato alargado es ideal para lijar superficies estrechas y listones con alta precisión.\n\nToma el poder en tus manos con el respaldo de MAXXT Ecuador. Disfruta de 1 año de garantía contra defectos de fábrica y asesoría técnica especializada.', 'Fuerza: 350W | Velocidad: 4000-10000rpm | Trazo: 1\\/8\" (3mm) | Dimensiones: 198x70mm | Peso: 2.5lbs', 'img_catalogo/20MOS350R.webp', 1, '2026-04-06 13:06:10'),
(119, 'Lijadora orbital sin escobillas', '20MOS350D', 'MAXXT', 'Lijadoras Orbitales', 'Presentación Única: 106.35', 'Potencia tu productividad con la Lijadora orbital sin escobillas de MAXXT, la solución ideal para el área de Lijadoras Orbitales.\n\nOfrece un rendimiento superior con las siguientes características: Fuerza: 350W | Velocidad: 4000-10000rpm | Trazo: 1\\/8\" (3mm) | Dimensiones: 100x152x152mm | Peso: 2.5lbs. Una herramienta versátil que se adapta a las exigencias de talleres y proyectos de restauración.\n\nToma el poder en tus manos con el respaldo de MAXXT Ecuador. Disfruta de 1 año de garantía contra defectos de fábrica y asesoría técnica especializada.', 'Fuerza: 350W | Velocidad: 4000-10000rpm | Trazo: 1\\/8\" (3mm) | Dimensiones: 100x152x152mm | Peso: 2.5lbs', 'img_catalogo/20MOS350D.webp', 1, '2026-04-06 13:06:10'),
(120, 'Lijadora orbital sin escobillas', '20MOS350S', 'MAXXT', 'Lijadoras Orbitales', 'Presentación Única: 104.42', 'Potencia tu productividad con la Lijadora orbital sin escobillas de MAXXT, la solución ideal para el área de Lijadoras Orbitales.\n\nOfrece un rendimiento superior con las siguientes características: Fuerza: 350W | Velocidad: 4000-10000rpm | Trazo: 1\\/8\" (3mm) | Dimensiones: 81x133mm | Peso: 2.5lbs. Eficiencia y maniobrabilidad en un formato ligero para acabados impecables en espacios reducidos.\n\nToma el poder en tus manos con el respaldo de MAXXT Ecuador. Disfruta de 1 año de garantía contra defectos de fábrica y asesoría técnica especializada.', 'Fuerza: 350W | Velocidad: 4000-10000rpm | Trazo: 1\\/8\" (3mm) | Dimensiones: 81x133mm | Peso: 2.5lbs', 'img_catalogo/20MOS350S.webp', 1, '2026-04-06 13:06:10'),
(121, 'Lijadora orbital inalámbrica', '20MOS20VCII', 'MAXXT', 'Lijadoras Orbitales', 'Presentación Única: 194.41', 'Potencia tu productividad con la Lijadora orbital inalámbrica de MAXXT, la solución ideal para el área de Lijadoras Orbitales.\n\nOfrece un rendimiento superior con las siguientes características: Fuerza: 20V | Velocidad: 4000-10000rpm | Trazo: 3\\/32\" (2.5mm) | Diámetro: 5\" (125mm) | Peso: 2.2lbs. La comodidad de no tener cables unida a un desempeño excepcional en cada pasada.\n\nToma el poder en tus manos con el respaldo de MAXXT Ecuador. Disfruta de 1 año de garantía contra defectos de fábrica y asesoría técnica especializada.', 'Fuerza: 20V | Velocidad: 4000-10000rpm | Trazo: 3\\/32\" (2.5mm) | Diámetro: 5\" (125mm) | Peso: 2.2lbs', 'img_catalogo/20MOS20VCII.webp', 1, '2026-04-06 13:06:10'),
(122, 'Lijadora orbital inalámbrica', '20MOS20VSII', 'MAXXT', 'Lijadoras Orbitales', 'Presentación Única: 143.97', 'Potencia tu productividad con la Lijadora orbital inalámbrica de MAXXT, la solución ideal para el área de Lijadoras Orbitales.\n\nOfrece un rendimiento superior con las siguientes características: Fuerza: 20V | Velocidad: 4000-10000rpm | Trazo: 1\\/16\" (1.5mm) | Dimensiones: 81x133mm | Peso: 2.2lbs. Su perfil compacto te brinda control total sin restricciones de movilidad.\n\nToma el poder en tus manos con el respaldo de MAXXT Ecuador. Disfruta de 1 año de garantía contra defectos de fábrica y asesoría técnica especializada.', 'Fuerza: 20V | Velocidad: 4000-10000rpm | Trazo: 1\\/16\" (1.5mm) | Dimensiones: 81x133mm | Peso: 2.2lbs', 'img_catalogo/20MOS20VSII.webp', 1, '2026-04-06 13:06:10'),
(123, 'Lijadora orbital inalámbrica', '20MOS20VDII', 'MAXXT', 'Lijadoras Orbitales', 'Presentación Única: 119.69', 'Potencia tu productividad con la Lijadora orbital inalámbrica de MAXXT, la solución ideal para el área de Lijadoras Orbitales.\n\nOfrece un rendimiento superior con las siguientes características: Fuerza: 20V | Velocidad: 4000-10000rpm | Trazo: 1\\/16\" (1.5mm) | Dimensiones: 100x152x152mm | Peso: 2.2lbs. Diseñada para llevar el acabado profesional a cualquier lugar, dentro o fuera del taller.\n\nToma el poder en tus manos con el respaldo de MAXXT Ecuador. Disfruta de 1 año de garantía contra defectos de fábrica y asesoría técnica especializada.', 'Fuerza: 20V | Velocidad: 4000-10000rpm | Trazo: 1\\/16\" (1.5mm) | Dimensiones: 100x152x152mm | Peso: 2.2lbs', 'img_catalogo/20MOS20VDII.webp', 1, '2026-04-06 13:06:10'),
(124, 'Lijadora orbital aleatoria', '20MDMS500', 'MAXXT', 'Lijadoras Orbitales', 'Presentación Única: 132.97', 'Potencia tu productividad con la Lijadora orbital aleatoria de MAXXT, la solución ideal para el área de Lijadoras Orbitales.\n\nOfrece un rendimiento superior con las siguientes características: Fuerza: 500W | Velocidad: 2500-6250rpm | Trazo: 3\\/16\" (5mm) | Diámetro: 6\" (150mm) | Peso: 6.6lbs. Su patrón aleatorio previene marcas profundas, logrando un pulido ultrasuave en maderas y metales.\n\nToma el poder en tus manos con el respaldo de MAXXT Ecuador. Disfruta de 1 año de garantía contra defectos de fábrica y asesoría técnica especializada.', 'Fuerza: 500W | Velocidad: 2500-6250rpm | Trazo: 3\\/16\" (5mm) | Diámetro: 6\" (150mm) | Peso: 6.6lbs', 'img_catalogo/20MDMS500.webp', 1, '2026-04-06 13:06:10'),
(125, 'Lijadora orbital aleatoria', '20MDMS800', 'MAXXT', 'Lijadoras Orbitales', 'Presentación Única: 164.22', 'Potencia tu productividad con la Lijadora orbital aleatoria de MAXXT, la solución ideal para el área de Lijadoras Orbitales.\n\nOfrece un rendimiento superior con las siguientes características: Fuerza: 800W | Velocidad: 3300-7250rpm | Trazo: 3\\/16\" (5mm) | Diámetro: 6\" (150mm) | Peso: 6.6lbs. Con máxima potencia, es la máquina indicada para desbaste pesado y remoción de recubrimientos duros.\n\nToma el poder en tus manos con el respaldo de MAXXT Ecuador. Disfruta de 1 año de garantía contra defectos de fábrica y asesoría técnica especializada.', 'Fuerza: 800W | Velocidad: 3300-7250rpm | Trazo: 3\\/16\" (5mm) | Diámetro: 6\" (150mm) | Peso: 6.6lbs', 'img_catalogo/20MDMS800.webp', 1, '2026-04-06 13:06:10'),
(126, 'Lijadora orbital aleatoria', '20MOS600R', 'MAXXT', 'Lijadoras Orbitales', 'Presentación Única: 150.66', 'Potencia tu productividad con la Lijadora orbital aleatoria de MAXXT, la solución ideal para el área de Lijadoras Orbitales.\n\nOfrece un rendimiento superior con las siguientes características: Fuerza: 600W | Velocidad: 4000-7000rpm | Trazo: 3\\/16\" (5mm) | Dimensiones: 400x70mm | Peso: 7lbs. Su amplia base garantiza planitud perfecta en piezas largas o de gran formato.\n\nToma el poder en tus manos con el respaldo de MAXXT Ecuador. Disfruta de 1 año de garantía contra defectos de fábrica y asesoría técnica especializada.', 'Fuerza: 600W | Velocidad: 4000-7000rpm | Trazo: 3\\/16\" (5mm) | Dimensiones: 400x70mm | Peso: 7lbs', 'img_catalogo/20MOS600R.webp', 1, '2026-04-06 13:06:10'),
(127, 'Extractor de polvo / Aspiradora', '20MVC3800', 'MAXXT', 'Limpieza al Vacío', 'Presentación Única: 29.2', 'Potencia tu productividad con el Extractor de polvo / Aspiradora de MAXXT, la solución ideal para el área de Limpieza al Vacío.\r\n\r\nOfrece un rendimiento superior con las siguientes características: Fuerza: 1400W (Max 3800W) | Capacidad contenedor: 10.56gal (40L) | Succión máxima: 160CFM. Mantiene tu espacio de trabajo libre de partículas nocivas gracias a su potente capacidad de succión industrial.\r\n\r\nToma el poder en tus manos con el respaldo de MAXXT Ecuador. Disfruta de 1 año de garantía contra defectos de fábrica y asesoría técnica especializada.', 'Fuerza: 1400W (Max 3800W) | Capacidad contenedor: 10.56gal (40L) | Succión máxima: 160CFM', 'img_catalogo/webp_1774909361_extractor_de_po.webp', 1, '2026-04-06 13:06:10'),
(128, 'Aspiradora', '20MVC3600V1', 'MAXXT', 'Limpieza al Vacío', 'Presentación Única: 64.52', 'Potencia tu productividad con la Aspiradora de MAXXT, la solución ideal para el área de Limpieza al Vacío.\n\nOfrece un rendimiento superior con las siguientes características: Fuerza: 1600W | Capacidad contenedor: 8gal (30L) | Succión máxima: 70.5CFM. Su motor de alto rendimiento aspira polvo fino y residuos gruesos para dejar tu área impecable.\n\nToma el poder en tus manos con el respaldo de MAXXT Ecuador. Disfruta de 1 año de garantía contra defectos de fábrica y asesoría técnica especializada.', 'Fuerza: 1600W | Capacidad contenedor: 8gal (30L) | Succión máxima: 70.5CFM', 'img_catalogo/20MVC3600V1.webp', 1, '2026-04-06 13:06:10'),
(129, 'Soplador / Aspiradora', '20MB700', 'MAXXT', 'Limpieza al Vacío', 'Presentación Única: 89.46', 'Potencia tu productividad con el Soplador / Aspiradora de MAXXT, la solución ideal para el área de Limpieza al Vacío.\r\n\r\nOfrece un rendimiento superior con las siguientes características: Fuerza: 700W | Soplado: 110CFM | Succión: 115CFM | Peso: 5.62lbs. Un equipo 2 en 1 ligero y práctico, indispensable para la limpieza rápida de talleres y obras.\r\n\r\nToma el poder en tus manos con el respaldo de MAXXT Ecuador. Disfruta de 1 año de garantía contra defectos de fábrica y asesoría técnica especializada.', 'Fuerza: 700W | Soplado: 110CFM | Succión: 115CFM | Peso: 5.62lbs', 'img_catalogo/20MB700.webp', 1, '2026-04-06 13:06:10'),
(130, 'Mezcladora de barro eléctrico', '20MPM1200', 'MAXXT', 'Mezclador de Paletas', 'Presentación Única: 67.25', 'Potencia tu productividad con la Mezcladora de barro eléctrico de MAXXT, la solución ideal para el área de Mezclador de Paletas.\n\nOfrece un rendimiento superior con las siguientes características: Fuerza: 1200W | Velocidad: 0-800rpm | Volumen máximo: 21Gal (80L) | Paleta: 4-3\\/4\" (120mm). Homogeneiza mortero, pintura o resinas rápidamente, ahorrando esfuerzo físico.\n\nToma el poder en tus manos con el respaldo de MAXXT Ecuador. Disfruta de 1 año de garantía contra defectos de fábrica y asesoría técnica especializada.', 'Fuerza: 1200W | Velocidad: 0-800rpm | Volumen máximo: 21Gal (80L) | Paleta: 4-3\\/4\" (120mm)', 'img_catalogo/20MPM1200.webp', 1, '2026-04-06 13:06:10'),
(131, 'Mezcladora - batidora', '20MPM1600', 'MAXXT', 'Mezclador de Paletas', 'Presentación Única: 73.0', 'Potencia tu productividad con la Mezcladora - batidora de MAXXT, la solución ideal para el área de Mezclador de Paletas.\n\nOfrece un rendimiento superior con las siguientes características: Fuerza: 1600W | Velocidades: 180-460rpm \\/ 300-750rpm | Volumen máximo: 32Gal (120L) | Paleta: 5-1\\/3\". Su caja de velocidades permite adaptar la fuerza según la densidad del material a mezclar.\n\nToma el poder en tus manos con el respaldo de MAXXT Ecuador. Disfruta de 1 año de garantía contra defectos de fábrica y asesoría técnica especializada.', 'Fuerza: 1600W | Velocidades: 180-460rpm \\/ 300-750rpm | Volumen máximo: 32Gal (120L) | Paleta: 5-1\\/3\"', 'img_catalogo/20MPM1600.webp', 1, '2026-04-06 13:06:10'),
(132, 'Mezcladora - batidora doble', '20MPM1600-2', 'MAXXT', 'Mezclador de Paletas', 'Presentación Única: 74.87', 'Potencia tu productividad con la Mezcladora - batidora doble de MAXXT, la solución ideal para el área de Mezclador de Paletas.\n\nOfrece un rendimiento superior con las siguientes características: Fuerza: 1600W | Velocidades: 200-480rpm \\/ 250-680rpm | Volumen máximo: 36Gal (140L) | Paleta: 4-3\\/4\". Su sistema de doble paleta evita la rotación del recipiente, ofreciendo una mezcla intensiva y sin esfuerzo.\n\nToma el poder en tus manos con el respaldo de MAXXT Ecuador. Disfruta de 1 año de garantía contra defectos de fábrica y asesoría técnica especializada.', 'Fuerza: 1600W | Velocidades: 200-480rpm \\/ 250-680rpm | Volumen máximo: 36Gal (140L) | Paleta: 4-3\\/4\"', 'img_catalogo/20MPM1600-2.webp', 1, '2026-04-06 13:06:10'),
(133, 'Taladro / Mezclador de baja velocidad', '20MDM1050', 'MAXXT', 'Mezclador de Paletas', 'Presentación Única: 64.14', 'Potencia tu productividad con el Taladro / Mezclador de baja velocidad de MAXXT, la solución ideal para el área de Mezclador de Paletas.\r\n\r\nOfrece un rendimiento superior con las siguientes características: Fuerza: 1050W | Velocidad: 0-550rpm | Volumen máximo: 21Gal (80L) | Paleta: 4-3/4\". Su alto torque en bajas revoluciones es perfecto para mezclas pesadas y espesas.\r\n\r\nToma el poder en tus manos con el respaldo de MAXXT Ecuador. Disfruta de 1 año de garantía contra defectos de fábrica y asesoría técnica especializada.', 'Fuerza: 1050W | Velocidad: 0-550rpm | Volumen máximo: 21Gal (80L) | Paleta: 4-3\\/4\"', 'img_catalogo/20MDM1050.webp', 1, '2026-04-06 13:06:10'),
(134, 'Amasadora sin escobillas sin cable', '20MDPM20V', 'MAXXT', 'Mezclador de Paletas', 'Presentación Única: 85.51', 'Potencia tu productividad con la Amasadora sin escobillas sin cable de MAXXT, la solución ideal para el área de Mezclador de Paletas.\n\nOfrece un rendimiento superior con las siguientes características: Fuerza: 20V | Velocidades: 0-450rpm \\/ 0-720rpm | Volumen máximo: 32Gal (120L) | Paleta: 5-1\\/3\". Movilidad absoluta en obra sin depender de tomas de corriente, manteniendo un nivel de fuerza profesional.\n\nToma el poder en tus manos con el respaldo de MAXXT Ecuador. Disfruta de 1 año de garantía contra defectos de fábrica y asesoría técnica especializada.', 'Fuerza: 20V | Velocidades: 0-450rpm \\/ 0-720rpm | Volumen máximo: 32Gal (120L) | Paleta: 5-1\\/3\"', 'img_catalogo/20MDPM20V.webp', 1, '2026-04-06 13:06:10'),
(135, 'Taladro a batería', '20MDID20V', 'MAXXT', 'Taladros Percutores', 'Presentación Única: 222.25', 'Potencia tu productividad con el Taladro a batería de MAXXT, la solución ideal para el área de Taladros Percutores.\n\nOfrece un rendimiento superior con las siguientes características: Motor: 20V Brushless | Torque Max: 60 Nm | Velocidades: 0-450 \\/ 0-2000 rpm | Mandril: 13mm (1\\/2\"). Potencia, autonomía y tecnología sin escobillas para perforaciones impecables en madera, metal y mampostería.\n\nToma el poder en tus manos con el respaldo de MAXXT Ecuador. Disfruta de 1 año de garantía contra defectos de fábrica y asesoría técnica especializada.', 'Motor: 20V Brushless | Torque Max: 60 Nm | Velocidades: 0-450 \\/ 0-2000 rpm | Mandril: 13mm (1\\/2\")', 'img_catalogo/20MDID20V.webp', 1, '2026-04-06 13:06:10'),
(136, 'Taladro percutor 1050W', '20MID1050', 'MAXXT', 'Taladros Percutores', 'Presentación Única: 86.95', 'Potencia tu productividad con el Taladro percutor de MAXXT, la solución ideal para el área de Taladros Percutores.\r\n\r\nOfrece un rendimiento superior con las siguientes características: Potencia: 1050W | Velocidades: 0-1100 / 0-3000 rpm | Impactos: 0-17600 / 0-48000 bpm | Mandril: 1.5-13mm. Fuerza bruta para perforar concreto y materiales de alta dureza sin esfuerzo.\r\n\r\nToma el poder en tus manos con el respaldo de MAXXT Ecuador. Disfruta de 1 año de garantía contra defectos de fábrica y asesoría técnica especializada.', 'Potencia: 1050W | Velocidades: 0-1100 / 0-3000 rpm | Impactos: 0-17600 / 0-48000 bpm | Mandril: 1.5-13mm', 'img_catalogo/20MID1050.webp', 1, '2026-04-06 13:06:10'),
(137, 'Taladro percutor 550W', '20MID550', 'MAXXT', 'Taladros Percutores', 'Presentación Única: 32.67', 'Potencia tu productividad con el Taladro percutor de MAXXT, la solución ideal para el área de Taladros Percutores.\r\n\r\nOfrece un rendimiento superior con las siguientes características: Potencia: 550W | Velocidad: 0-3000 rpm | Impactos: 0-48000 bpm | Mandril: 1.5-13mm. Ligero, versátil y confiable, es la herramienta perfecta para instalaciones cotidianas.\r\n\r\nToma el poder en tus manos con el respaldo de MAXXT Ecuador. Disfruta de 1 año de garantía contra defectos de fábrica y asesoría técnica especializada.', 'Potencia: 550W | Velocidad: 0-3000 rpm | Impactos: 0-48000 bpm | Mandril: 1.5-13mm', 'img_catalogo/20MID550.webp', 1, '2026-04-06 13:06:10'),
(138, 'Taladro percutor 750W', '20MID750', 'MAXXT', 'Taladros Percutores', 'Presentación Única: 40.17', 'Potencia tu productividad con el Taladro percutor de MAXXT, la solución ideal para el área de Taladros Percutores.\r\n\r\nOfrece un rendimiento superior con las siguientes características: Potencia: 750W | Velocidad: 0-3000 rpm | Impactos: 0-48000 bpm | Mandril: 1.5-13mm. Su mecanismo de impacto de alta frecuencia acelera el trabajo en construcción y albañilería.\r\n\r\nToma el poder en tus manos con el respaldo de MAXXT Ecuador. Disfruta de 1 año de garantía contra defectos de fábrica y asesoría técnica especializada.', 'Potencia: 750W | Velocidad: 0-3000 rpm | Impactos: 0-48000 bpm | Mandril: 1.5-13mm', 'img_catalogo/20MID750.webp', 1, '2026-04-06 13:06:10'),
(139, 'Taladro percutor 850W', '20MID850', 'MAXXT', 'Taladros Percutores', 'Presentación Única: 36.46', 'Potencia tu productividad con el Taladro percutor de MAXXT, la solución ideal para el área de Taladros Percutores.\r\n\r\nOfrece un rendimiento superior con las siguientes características: Potencia: 850W | Velocidad: 0-3000 rpm | Impactos: 0-48000 bpm | Mandril: 1.5-13mm. Desempeño robusto y duradero para los contratistas más exigentes.\r\n\r\nToma el poder en tus manos con el respaldo de MAXXT Ecuador. Disfruta de 1 año de garantía contra defectos de fábrica y asesoría técnica especializada.', 'Potencia: 850W | Velocidad: 0-3000 rpm | Impactos: 0-48000 bpm | Mandril: 1.5-13mm', 'img_catalogo/20MID850.webp', 1, '2026-04-06 13:06:10'),
(140, 'Amoladora / Pulidora angular', '20MAG1700', 'MAXXT', 'Amoladoras', 'Presentación Única: 43.81', 'Potencia tu productividad con la Amoladora/Pulidora angular de MAXXT, la solución ideal para el área de Amoladoras.\r\n\r\nOfrece un rendimiento superior con las siguientes características: Fuerza: 1700W | Velocidad: 3200-9200rpm | Diámetro máximo: 5\" (125mm) | Peso: 5.29lbs. Su potente motor corta y desbasta metal o piedra con extrema facilidad y seguridad.\r\n\r\nToma el poder en tus manos con el respaldo de MAXXT Ecuador. Disfruta de 1 año de garantía contra defectos de fábrica y asesoría técnica especializada.', 'Fuerza: 1700W | Velocidad: 3200-9200rpm | Diámetro máximo: 5\" (125mm) | Peso: 5.29lbs', 'img_catalogo/20MAG1700.webp', 1, '2026-04-06 13:06:10'),
(141, 'Amoladora / Pulidora angular inalámbrica', '20MDAG20V', 'MAXXT', 'Amoladoras', 'Presentación Única: 98.75', 'Potencia tu productividad con la Amoladora/Pulidora angular inalámbrica de MAXXT, la solución ideal para el área de Amoladoras.\r\n\r\nOfrece un rendimiento superior con las siguientes características: Fuerza: 20V | Velocidades: 3000 / 6000 / 8500 rpm | Diámetro máximo: 5\" (125mm) | Peso: 4.9lbs. La portabilidad definitiva sin sacrificar el poder de corte que tus proyectos demandan.\r\n\r\nToma el poder en tus manos con el respaldo de MAXXT Ecuador. Disfruta de 1 año de garantía contra defectos de fábrica y asesoría técnica especializada.', 'Fuerza: 20V | Velocidades: 3000 \\/ 6000 \\/ 8500 rpm | Diámetro máximo: 5\" (125mm) | Peso: 4.9lbs', 'img_catalogo/20MDAG20V.webp', 1, '2026-04-06 13:06:10'),
(142, 'Amoladora / Pulidora angular 7\"', '20MAG2000', 'MAXXT', 'Amoladoras', 'Presentación Única: 43.88', 'Potencia tu productividad con la Amoladora/Pulidora angular 7\" de MAXXT, la solución ideal para el área de Amoladoras.\r\n\r\nOfrece un rendimiento superior con las siguientes características: Potencia: 2000W | Cable: 4m | Cambio de Disco: Bloqueo de eje | Guarda de ajuste rápido. Un equipo industrial preparado para las tareas de herrería y construcción más arduas.\r\n\r\nToma el poder en tus manos con el respaldo de MAXXT Ecuador. Disfruta de 1 año de garantía contra defectos de fábrica y asesoría técnica especializada.', 'Potencia: 2000W | Cable: 4m | Cambio de Disco: Bloqueo de eje | Guarda de ajuste rápido', 'img_catalogo/20MAG2000.webp', 1, '2026-04-06 13:06:10'),
(143, 'Amoladora / Pulidora angular 9\"', '20MAG2500', 'MAXXT', 'Amoladoras', 'Presentación Única: 98.93', 'Potencia tu productividad con la Amoladora/Pulidora angular 9\" de MAXXT, la solución ideal para el área de Amoladoras.\r\n\r\nOfrece un rendimiento superior con las siguientes características: Potencia: 2500W | Cable: 4m | Cambio de Disco: Bloqueo de eje | Mango trasero rotativo. Su mango adaptable brinda un control excepcional en cortes profundos y desbaste masivo.\r\n\r\nToma el poder en tus manos con el respaldo de MAXXT Ecuador. Disfruta de 1 año de garantía contra defectos de fábrica y asesoría técnica especializada.', 'Potencia: 2500W | Cable: 4m | Cambio de Disco: Bloqueo de eje | Mango trasero rotativo', 'img_catalogo/20MAG2500.webp', 1, '2026-04-06 13:06:10'),
(144, 'Amoladora de concreto 5\"', '20MG1500', 'MAXXT', 'Línea de Concreto', 'Presentación Única: 63.21', 'Potencia tu productividad con la Amoladora de concreto 5\" de MAXXT, la solución ideal para el área de Línea de Concreto.\n\nOfrece un rendimiento superior con las siguientes características: Fuerza: 1500W | Velocidad: 1000-8500rpm | Diámetro abrasivo: 5\" (125mm) | Peso: 6.7lbs. Especialmente calibrada para alisar, nivelar y retirar imperfecciones en superficies de hormigón.\n\nToma el poder en tus manos con el respaldo de MAXXT Ecuador. Disfruta de 1 año de garantía contra defectos de fábrica y asesoría técnica especializada.', 'Fuerza: 1500W | Velocidad: 1000-8500rpm | Diámetro abrasivo: 5\" (125mm) | Peso: 6.7lbs', 'img_catalogo/20MG1500.webp', 1, '2026-04-06 13:06:10'),
(145, 'Pulidor de tambores', '20MBP1300', 'MAXXT', 'Línea de Concreto', 'Presentación Única: 94.22', 'Potencia tu productividad con el Pulidor de tambores de MAXXT, la solución ideal para el área de Línea de Concreto.\n\nOfrece un rendimiento superior con las siguientes características: Fuerza: 1300W | Velocidad: 900-3700rpm | Diámetro abrasivo: 4-3\\/4\" (120mm) | Peso: 9.52lbs. Su diseño cilíndrico brinda un acabado satinado perfecto en metales y superficies extensas.\n\nToma el poder en tus manos con el respaldo de MAXXT Ecuador. Disfruta de 1 año de garantía contra defectos de fábrica y asesoría técnica especializada.', 'Fuerza: 1300W | Velocidad: 900-3700rpm | Diámetro abrasivo: 4-3\\/4\" (120mm) | Peso: 9.52lbs', 'img_catalogo/20MBP1300.webp', 1, '2026-04-06 13:06:10'),
(146, 'Herramienta multifunción', '20MOT300', 'MAXXT', 'Línea de Concreto', 'Presentación Única: 15.0', 'Potencia tu productividad con la Herramienta multifunción de MAXXT, la solución ideal para el área de Línea de Concreto.\n\nOfrece un rendimiento superior con las siguientes características: Fuerza: 300W | Velocidad: 15000-220000pm | Grados oscilantes: 3° | Cambio sin herramientas. Corta, lija y raspa en espacios ajustados donde otras herramientas no pueden llegar.\n\nToma el poder en tus manos con el respaldo de MAXXT Ecuador. Disfruta de 1 año de garantía contra defectos de fábrica y asesoría técnica especializada.', 'Fuerza: 300W | Velocidad: 15000-220000pm | Grados oscilantes: 3° | Cambio sin herramientas', 'img_catalogo/20MOT300.webp', 1, '2026-04-06 13:06:10'),
(147, 'Vibrador de concreto inalámbrico', '20MDCV20V', 'MAXXT', 'Línea de Concreto', 'Presentación Única: 332.49', 'Potencia tu productividad con el Vibrador de concreto inalámbrico de MAXXT, la solución ideal para el área de Línea de Concreto.\n\nOfrece un rendimiento superior con las siguientes características: Fuerza: 20V | Vibración por minuto: 0-12500 | Diámetro cabeza: 1 inch (25.4mm) | Eje: 4ft. Elimina burbujas de aire y asegura la compactación estructural perfecta sin molestos cables en obra.\n\nToma el poder en tus manos con el respaldo de MAXXT Ecuador. Disfruta de 1 año de garantía contra defectos de fábrica y asesoría técnica especializada.', 'Fuerza: 20V | Vibración por minuto: 0-12500 | Diámetro cabeza: 1 inch (25.4mm) | Eje: 4ft', 'img_catalogo/20MDCV20V.webp', 1, '2026-04-06 13:06:10'),
(148, 'Ranuradora de pared', '20MWC1700', 'MAXXT', 'Línea de Concreto', 'Presentación Única: 67.84', 'Potencia tu productividad con la Ranuradora de pared de MAXXT, la solución ideal para el área de Línea de Concreto.\n\nOfrece un rendimiento superior con las siguientes características: Fuerza: 1700W | Velocidad: 7000r\\/min | Disco: 6\" (150mm) | Profundidad de corte: 10-50mm. Realiza canales limpios para tuberías y cableado en un solo paso, optimizando el tiempo de instalación.\n\nToma el poder en tus manos con el respaldo de MAXXT Ecuador. Disfruta de 1 año de garantía contra defectos de fábrica y asesoría técnica especializada.', 'Fuerza: 1700W | Velocidad: 7000r\\/min | Disco: 6\" (150mm) | Profundidad de corte: 10-50mm', 'img_catalogo/20MWC1700.webp', 1, '2026-04-06 13:06:10'),
(149, 'Sierra de hormigón', '20MCS2600', 'MAXXT', 'Línea de Concreto', 'Presentación Única: 99.92', 'Potencia tu productividad con la Sierra de hormigón de MAXXT, la solución ideal para el área de Línea de Concreto.\n\nOfrece un rendimiento superior con las siguientes características: Fuerza: 2600W | Velocidad: 4300r\\/min | Diámetro espada: 14\" (355mm) | Profundidad: 5\" (125mm). Diseñada para cortes profundos y precisos en bloque, piedra y pavimentos.\n\nToma el poder en tus manos con el respaldo de MAXXT Ecuador. Disfruta de 1 año de garantía contra defectos de fábrica y asesoría técnica especializada.', 'Fuerza: 2600W | Velocidad: 4300r\\/min | Diámetro espada: 14\" (355mm) | Profundidad: 5\" (125mm)', 'img_catalogo/20MCS2600.webp', 1, '2026-04-06 13:06:10'),
(150, 'Cortadora de cerámica', '20MTC1400', 'MAXXT', 'Línea de Concreto', 'Presentación Única: 64.55', 'Potencia tu productividad con la Cortadora de cerámica de MAXXT, la solución ideal para el área de Línea de Concreto.\n\nOfrece un rendimiento superior con las siguientes características: Fuerza: 1400W | Velocidad: 11300rpm | Hoja: 5\" (125mm) | Capacidad corte: 1.2\" (30mm) | Riel 1.5m. Cortes rectos y sin astilladuras en porcelanatos y cerámicas de gran formato.\n\nToma el poder en tus manos con el respaldo de MAXXT Ecuador. Disfruta de 1 año de garantía contra defectos de fábrica y asesoría técnica especializada.', 'Fuerza: 1400W | Velocidad: 11300rpm | Hoja: 5\" (125mm) | Capacidad corte: 1.2\" (30mm) | Riel 1.5m', 'img_catalogo/20MTC1400.webp', 1, '2026-04-06 13:06:10'),
(151, 'Sierra de cinta inalámbrica', '20MDBS20V127', 'MAXXT', 'Cortadoras', 'Presentación Única: 62.67', 'Potencia tu productividad con la Sierra de cinta inalámbrica de MAXXT, la solución ideal para el área de Cortadoras.\n\nOfrece un rendimiento superior con las siguientes características: Fuerza: 20V | Velocidad: 0-420 SFPM | Capacidad max corte: 5\" x 5\" (127x127mm) | Peso: 13.88lbs. Su capacidad de corte libre de chispas la hace segura y rápida para perfiles metálicos.\n\nToma el poder en tus manos con el respaldo de MAXXT Ecuador. Disfruta de 1 año de garantía contra defectos de fábrica y asesoría técnica especializada.', 'Fuerza: 20V | Velocidad: 0-420 SFPM | Capacidad max corte: 5\" x 5\" (127x127mm) | Peso: 13.88lbs', 'img_catalogo/20MDBS20V127.webp', 1, '2026-04-06 13:06:10'),
(152, 'Sierra de cinta inalámbrica', '20MDBS20V65', 'MAXXT', 'Cortadoras', 'Presentación Única: 70.3', 'Potencia tu productividad con la Sierra de cinta inalámbrica de MAXXT, la solución ideal para el área de Cortadoras.\n\nOfrece un rendimiento superior con las siguientes características: Fuerza: 20V | Velocidad: 0-630 SFPM | Capacidad max corte: 2.5\" x 2.5\" (65x65mm) | Peso: 8.3lbs. Ligera y compacta para cortar tuberías y conduit directamente en la posición de instalación.\n\nToma el poder en tus manos con el respaldo de MAXXT Ecuador. Disfruta de 1 año de garantía contra defectos de fábrica y asesoría técnica especializada.', 'Fuerza: 20V | Velocidad: 0-630 SFPM | Capacidad max corte: 2.5\" x 2.5\" (65x65mm) | Peso: 8.3lbs', 'img_catalogo/20MDBS20V65.webp', 1, '2026-04-06 13:06:10'),
(153, 'Cortador con sierra de cinta con base', '20MBS1100', 'MAXXT', 'Cortadoras', 'Presentación Única: 79.59', 'Potencia tu productividad con el Cortador con sierra de cinta con base de MAXXT, la solución ideal para el área de Cortadoras.\n\nOfrece un rendimiento superior con las siguientes características: Fuerza: 1100W | Velocidad: 0-472 SFPM | Capacidad max corte: 5\" x 5\" (127x127mm) | Con Base. Estabilidad estacionaria garantizada para cortes seriados de alta precisión en tu taller.\n\nToma el poder en tus manos con el respaldo de MAXXT Ecuador. Disfruta de 1 año de garantía contra defectos de fábrica y asesoría técnica especializada.', 'Fuerza: 1100W | Velocidad: 0-472 SFPM | Capacidad max corte: 5\" x 5\" (127x127mm) | Con Base', 'img_catalogo/20MBS1100.webp', 1, '2026-04-06 13:06:10'),
(154, 'Tronzadora / Sierra de corte con disco de metal 14\"', '20MCS355', 'MAXXT', 'Cortadoras', 'Presentación Única: 6.46', 'Potencia tu productividad con la Tronzadora/Sierra de corte con disco de metal 14\" de MAXXT, la solución ideal para el área de Cortadoras.\r\n\r\nOfrece un rendimiento superior con las siguientes características: Fuerza: 2000W | Velocidad: 1300r/min | Cuchilla: 14\"/15\" (355/380mm) | Bisel máximo: 45°. Máxima tenacidad para seccionar vigas, tubos y barras de acero en segundos.\r\n\r\nToma el poder en tus manos con el respaldo de MAXXT Ecuador. Disfruta de 1 año de garantía contra defectos de fábrica y asesoría técnica especializada.', 'Fuerza: 2000W | Velocidad: 1300r\\/min | Cuchilla: 14\"\\/15\" (355\\/380mm) | Bisel máximo: 45°', 'img_catalogo/20MCS355.webp', 1, '2026-04-06 13:06:10'),
(155, 'Tronzadora / Sierra de corte con disco de piedra 14\"', '20MCS2300', 'MAXXT', 'Cortadoras', 'Presentación Única: 8.91', 'Potencia tu productividad con la Tronzadora/Sierra de corte con disco de piedra 14\" de MAXXT, la solución ideal para el área de Cortadoras.\r\n\r\nOfrece un rendimiento superior con las siguientes características: Potencia: 2300W | Velocidad: 4000 r/min | Disco: 14\" (355mm) | Corte redondo max: 130mm. Especialmente fabricada para resistir el polvo abrasivo y ofrecer durabilidad en el corte de mampostería.\r\n\r\nToma el poder en tus manos con el respaldo de MAXXT Ecuador. Disfruta de 1 año de garantía contra defectos de fábrica y asesoría técnica especializada.', 'Potencia: 2300W | Velocidad: 4000 r\\/min | Disco: 14\" (355mm) | Corte redondo max: 130mm', 'img_catalogo/20MCS2300.webp', 1, '2026-04-06 13:06:10'),
(156, 'Pulverizador de pintura de mano con batería', '20MDPS20V', 'MAXXT', 'Pulverizadores de Pintura', 'Presentación Única: 183.98', 'Potencia tu productividad con el Pulverizador de pintura de mano con batería de MAXXT, la solución ideal para el área de Pulverizadores de Pintura.\n\nOfrece un rendimiento superior con las siguientes características: Fuerza: 20V | Presión Max: 25Kpa | Caudal Max: 0.26 Gal\\/min (1.0 L\\/min) | Botella: 1000ml. Logra un acabado uniforme y sin marcas de brocha en muebles y paredes con total libertad de cable.\n\nToma el poder en tus manos con el respaldo de MAXXT Ecuador. Disfruta de 1 año de garantía contra defectos de fábrica y asesoría técnica especializada.', 'Fuerza: 20V | Presión Max: 25Kpa | Caudal Max: 0.26 Gal\\/min (1.0 L\\/min) | Botella: 1000ml', 'img_catalogo/20MDPS20V.webp', 1, '2026-04-06 13:06:10'),
(157, 'Pulverizador de pintura sin aire en soporte', '20MPS1200', 'MAXXT', 'Pulverizadores de Pintura', 'Presentación Única: 27.19', 'Potencia tu productividad con el Pulverizador de pintura sin aire en soporte de MAXXT, la solución ideal para el área de Pulverizadores de Pintura.\n\nOfrece un rendimiento superior con las siguientes características: Fuerza: 1200W | Presión Max: 226 Bar (3300 PSI) | Caudal Max: 0.42 Gal\\/min (1.6 L\\/min). Tecnología Airless para cubrir grandes superficies comerciales o residenciales en tiempo récord.\n\nToma el poder en tus manos con el respaldo de MAXXT Ecuador. Disfruta de 1 año de garantía contra defectos de fábrica y asesoría técnica especializada.', 'Fuerza: 1200W | Presión Max: 226 Bar (3300 PSI) | Caudal Max: 0.42 Gal\\/min (1.6 L\\/min)', 'img_catalogo/20MPS1200.webp', 1, '2026-04-06 13:06:10');
INSERT INTO `improgyp_catalogo` (`id`, `nombre`, `codigo`, `marca`, `categoria`, `presentaciones_raw`, `desc_larga`, `datos_tecnicos`, `imagen_url`, `publicado`, `fecha_creacion`) VALUES
(158, 'Pulverizador de pintura sin aire en soporte', '20MPS1300', 'MAXXT', 'Pulverizadores de Pintura', 'Presentación Única: 42.87', 'Potencia tu productividad con el Pulverizador de pintura sin aire en soporte de MAXXT, la solución ideal para el área de Pulverizadores de Pintura.\n\nOfrece un rendimiento superior con las siguientes características: Fuerza: 1300W | Presión Max: 227 Bar (3300 PSI) | Caudal Max: 0.5 Gal\\/min (2.3 L\\/min). Incrementa el flujo de trabajo y la presión para aplicaciones de pinturas densas sin diluir.\n\nToma el poder en tus manos con el respaldo de MAXXT Ecuador. Disfruta de 1 año de garantía contra defectos de fábrica y asesoría técnica especializada.', 'Fuerza: 1300W | Presión Max: 227 Bar (3300 PSI) | Caudal Max: 0.5 Gal\\/min (2.3 L\\/min)', 'img_catalogo/20MPS1300.webp', 1, '2026-04-06 13:06:10'),
(159, 'Pistola de calor con pantalla LCD', '20MHG2000', 'MAXXT', 'Pistolas de Aire Caliente', 'Presentación Única: 82.48', 'Potencia tu productividad con la Pistola de calor con pantalla LCD de MAXXT, la solución ideal para el área de Pistolas de Aire Caliente.\n\nOfrece un rendimiento superior con las siguientes características: Fuerza: 2000W | Temperatura: 50°C - 600°C (122F-1112F) | Volumen viento: 300-550 L\\/min. Su pantalla digital permite un control térmico milimétrico para moldear plásticos, decapar pintura o contraer cables.\n\nToma el poder en tus manos con el respaldo de MAXXT Ecuador. Disfruta de 1 año de garantía contra defectos de fábrica y asesoría técnica especializada.', 'Fuerza: 2000W | Temperatura: 50°C - 600°C (122F-1112F) | Volumen viento: 300-550 L\\/min', 'img_catalogo/20MHG2000.webp', 1, '2026-04-06 13:06:10'),
(160, 'Pistola de calor con cable', '20MHG1800', 'MAXXT', 'Pistolas de Aire Caliente', 'Presentación Única: 24.5', 'Potencia tu productividad con la Pistola de calor con cable de MAXXT, la solución ideal para el área de Pistolas de Aire Caliente.\n\nOfrece un rendimiento superior con las siguientes características: Fuerza: 1800W | Temperaturas: 450°C \\/ 580°C | Volumen viento: 300 \\/ 500 L\\/min. Una herramienta indispensable de dos velocidades, fiable y resistente para uso continuo.\n\nToma el poder en tus manos con el respaldo de MAXXT Ecuador. Disfruta de 1 año de garantía contra defectos de fábrica y asesoría técnica especializada.', 'Fuerza: 1800W | Temperaturas: 450°C \\/ 580°C | Volumen viento: 300 \\/ 500 L\\/min', 'img_catalogo/20MHG1800.webp', 1, '2026-04-06 13:06:10'),
(161, 'Pistola de calor inalámbrica', '20MDHG20V', 'MAXXT', 'Pistolas de Aire Caliente', 'Presentación Única: 30.36', 'Potencia tu productividad con la Pistola de calor inalámbrica de MAXXT, la solución ideal para el área de Pistolas de Aire Caliente.\n\nOfrece un rendimiento superior con las siguientes características: Fuerza: 20V | Temperaturas: 280°C \\/ 530°C | Volumen viento: 109 \\/ 190 L\\/min. Calentamiento rápido y autonomía total para trabajos de detalle o reparaciones automotrices en el sitio.\n\nToma el poder en tus manos con el respaldo de MAXXT Ecuador. Disfruta de 1 año de garantía contra defectos de fábrica y asesoría técnica especializada.', 'Fuerza: 20V | Temperaturas: 280°C \\/ 530°C | Volumen viento: 109 \\/ 190 L\\/min', 'img_catalogo/20MDHG20V.webp', 1, '2026-04-06 13:06:10'),
(162, 'Pistola de calafateo inalámbrica', '20MDCG20V2', 'MAXXT', 'Pistolas de Silicona', 'Presentación Única: 39.8', 'Potencia tu productividad con la Pistola de calafateo inalámbrica de MAXXT, la solución ideal para el área de Pistolas de Silicona.\n\nOfrece un rendimiento superior con las siguientes características: Fuerza: 20V | Velocidad: 0.8-8 mm\\/s | Presión Max: 2500N | Capacidad: 20oz (600ml). Dispensa selladores con flujo constante, previniendo el goteo y ahorrando material valioso.\n\nToma el poder en tus manos con el respaldo de MAXXT Ecuador. Disfruta de 1 año de garantía contra defectos de fábrica y asesoría técnica especializada.', 'Fuerza: 20V | Velocidad: 0.8-8 mm\\/s | Presión Max: 2500N | Capacidad: 20oz (600ml)', 'img_catalogo/20MDCG20V2.webp', 1, '2026-04-06 13:06:10'),
(163, 'Pistola para silicona inalámbrica', '20MDCG20V1', 'MAXXT', 'Pistolas de Silicona', 'Presentación Única:85.81', 'Potencia tu productividad con la Pistola para silicona inalámbrica de MAXXT, la solución ideal para el área de Pistolas de Silicona.\r\n\r\nOfrece un rendimiento superior con las siguientes características: Fuerza: 20V | Velocidad: 0.8-8 mm/s | Presión Max: 2500N | Capacidad: 10oz (300ml). Su diseño ligero reduce la fatiga en la mano, ideal para sellado de ventanas y carpintería.\r\n\r\nToma el poder en tus manos con el respaldo de MAXXT Ecuador. Disfruta de 1 año de garantía contra defectos de fábrica y asesoría técnica especializada.', 'Fuerza: 20V | Velocidad: 0.8-8 mm/s | Presión Max: 2500N | Capacidad: 10oz (300ml)', 'img_catalogo/webp_1775834150_pistola_para_si.webp', 1, '2026-04-06 13:06:10'),
(164, 'Soplador de alta velocidad a batería', '20MDPB20VII', 'MAXXT', 'Sopladoras', 'Presentación Única: 320.52', 'Potencia tu productividad con el Soplador de alta velocidad a batería de MAXXT, la solución ideal para el área de Sopladoras.\n\nOfrece un rendimiento superior con las siguientes características: Voltaje: 20V | Velocidad Aire: 50 m/s | Volumen: 10.4 m3/min | Fuerza: 11N. Limpia rápidamente áreas de trabajo, removiendo polvo y aserrín con un potente flujo de aire.\n\nToma el poder en tus manos con el respaldo de MAXXT Ecuador. Disfruta de 1 año de garantía contra defectos de fábrica y asesoría técnica especializada.', 'Voltaje: 20V | Velocidad Aire: 50 m/s | Volumen: 10.4 m3/min | Fuerza: 11N', 'img_catalogo/20MDPB20VII.webp', 1, '2026-04-06 13:06:10'),
(165, 'Soplador alta velocidad a batería 130m\\/s', '20MDPB20V', 'MAXXT', 'Sopladoras', 'Presentación Única: 164.52', 'Potencia tu productividad con el Soplador alta velocidad a batería 130m\\/s de MAXXT, la solución ideal para el área de Sopladoras.\n\nOfrece un rendimiento superior con las siguientes características: Voltaje: 20V | Velocidad Aire: 130 m\\/s | Volumen: 10.4 m3\\/min | Fuerza: 11N. Su chorro concentrado de aire de alta velocidad expulsa los residuos más pesados sin esfuerzo.\n\nToma el poder en tus manos con el respaldo de MAXXT Ecuador. Disfruta de 1 año de garantía contra defectos de fábrica y asesoría técnica especializada.', 'Voltaje: 20V | Velocidad Aire: 130 m\\/s | Volumen: 10.4 m3\\/min | Fuerza: 11N', 'img_catalogo/20MDPB20V.webp', 1, '2026-04-06 13:06:10'),
(166, 'Ventosa para baldosas y vidrio', '20MTVM20V', 'MAXXT', 'Herramientas Varias', 'Presentación Única: 151.92', 'Potencia tu productividad con la Ventosa para baldosas y vidrio de MAXXT, la solución ideal para el área de Herramientas Varias.\n\nOfrece un rendimiento superior con las siguientes características: Fuerza: 20V | Vibración: 6000-10000 rpm | Capacidad max carga: 150 kg | Batería: 2Ah. Su vibración inteligente expulsa el aire bajo la baldosa, asegurando una adherencia perfecta del mortero.\n\nToma el poder en tus manos con el respaldo de MAXXT Ecuador. Disfruta de 1 año de garantía contra defectos de fábrica y asesoría técnica especializada.', 'Fuerza: 20V | Vibración: 6000-10000 rpm | Capacidad max carga: 150 kg | Batería: 2Ah', 'img_catalogo/20MTVM20V.webp', 1, '2026-04-06 13:06:10'),
(167, 'Remachadora inalámbrica con batería', '20MDRG20V', 'MAXXT', 'Herramientas Varias', 'Presentación Única: 325.96', 'Potencia tu productividad con la Remachadora inalámbrica con batería de MAXXT, la solución ideal para el área de Herramientas Varias.\n\nOfrece un rendimiento superior con las siguientes características: Fuerza: 20V | Remaches: 3\\/32\" - 3\\/16\" (2.4-6.0mm) | Fuerza ajuste: 2040kg \\/ 20000N. Fija piezas metálicas en milisegundos sin el cansancio de las herramientas manuales tradicionales.\n\nToma el poder en tus manos con el respaldo de MAXXT Ecuador. Disfruta de 1 año de garantía contra defectos de fábrica y asesoría técnica especializada.', 'Fuerza: 20V | Remaches: 3\\/32\" - 3\\/16\" (2.4-6.0mm) | Fuerza ajuste: 2040kg \\/ 20000N', 'img_catalogo/20MDRG20V.webp', 1, '2026-04-06 13:06:10'),
(168, 'Pistola de llave de impacto automotriz', '20MDIW20V', 'MAXXT', 'Herramientas Varias', 'Presentación Única: 306.32', 'Potencia tu productividad con la Pistola de llave de impacto automotriz de MAXXT, la solución ideal para el área de Herramientas Varias.\n\nOfrece un rendimiento superior con las siguientes características: Motor: 20V Brushless | Torque Max: 1000 N.m | Encastre: 1\\/2\" | Velocidad: 0-2400 rpm. Afloja tuercas y pernos oxidados con un torque monstruoso, indispensable en talleres mecánicos y montajes pesados.\n\nToma el poder en tus manos con el respaldo de MAXXT Ecuador. Disfruta de 1 año de garantía contra defectos de fábrica y asesoría técnica especializada.', 'Motor: 20V Brushless | Torque Max: 1000 N.m | Encastre: 1\\/2\" | Velocidad: 0-2400 rpm', 'img_catalogo/20MDIW20V.webp', 1, '2026-04-06 13:06:10'),
(169, 'Herramienta de cinta pasacable', '20MDFTT20V520', 'MAXXT', 'Herramientas Varias', 'Presentación Única: 416.54', 'Potencia tu productividad con la Herramienta de cinta pasacable de MAXXT, la solución ideal para el área de Herramientas Varias.\n\nOfrece un rendimiento superior con las siguientes características: Fuerza: 20V | Velocidad en ralentí: 0-28 m\\/min | Longitud cable guía: 20m | Peso: 5.4kgs. Acelera el tendido eléctrico con un sistema motorizado que previene atascos en la tubería.\n\nToma el poder en tus manos con el respaldo de MAXXT Ecuador. Disfruta de 1 año de garantía contra defectos de fábrica y asesoría técnica especializada.', 'Fuerza: 20V | Velocidad en ralentí: 0-28 m\\/min | Longitud cable guía: 20m | Peso: 5.4kgs', 'img_catalogo/20MDFTT20V520.webp', 1, '2026-04-06 13:06:10'),
(170, 'Pistola engrasadora manual a batería', '20MDGG20VII', 'MAXXT', 'Herramientas Varias', 'Presentación Única: 207.02', 'Potencia tu productividad con la Pistola engrasadora manual a batería de MAXXT, la solución ideal para el área de Herramientas Varias.\n\nOfrece un rendimiento superior con las siguientes características: Voltaje: 20V | Longitud de Manguera: 1 Metro | Batería: 4Ah | Incluye Manguera flexible. Lubrica maquinaria pesada de forma rápida y constante sin esfuerzo de bombeo manual.\n\nToma el poder en tus manos con el respaldo de MAXXT Ecuador. Disfruta de 1 año de garantía contra defectos de fábrica y asesoría técnica especializada.', 'Voltaje: 20V | Longitud de Manguera: 1 Metro | Batería: 4Ah | Incluye Manguera flexible', 'img_catalogo/20MDGG20VII.webp', 1, '2026-04-06 13:06:10'),
(171, 'Cargador 2A', '20MC20', 'MAXXT', 'Accesorios', 'Presentación Única: 8.17', 'Optimiza el uso de tus herramientas con el Cargador 2A, un accesorio original de MAXXT.\n\nEspecificaciones técnicas: Corriente de Carga: 2A | Voltaje de Entrada: 100-240V AC | Voltaje de Salida: 20V DC. Diseñado con tecnología inteligente para proteger y alargar la vida útil de tus celdas de energía.\n\nMAXXT Ecuador te ofrece calidad comprobada y repuestos originales para que tu trabajo no se detenga.', 'Corriente de Carga: 2A | Voltaje de Entrada: 100-240V AC | Voltaje de Salida: 20V DC', 'img_catalogo/20MC20.webp', 1, '2026-04-06 13:06:10'),
(172, 'Batería 2.0Ah', '20M-B2020', 'MAXXT', 'Accesorios', 'Presentación Única:225.47', 'Optimiza el uso de tus herramientas con la Batería 2.0Ah, un accesorio original de MAXXT.\r\n\r\nEspecificaciones técnicas: Celda de Batería: 18650 | Paquete: 2.0Ah (60min) o 4.0Ah (120min). Batería de iones de litio sin efecto memoria que mantiene tus equipos funcionando al máximo rendimiento.\r\n\r\nMAXXT Ecuador te ofrece calidad comprobada y repuestos originales para que tu trabajo no se detenga.', 'Celda de Batería: 18650 | Paquete: 2.0Ah (60min) o 4.0Ah (120min)', 'img_catalogo/webp_1775834208_bater__a_2_0ah.webp', 1, '2026-04-06 13:06:10'),
(173, 'Batería 4.0Ah', '20M-B2040', 'MAXXT', 'Accesorios', 'Presentación Única: 153.0', 'Optimiza el uso de tus herramientas con la Batería 4.0Ah, un accesorio original de MAXXT.\n\nEspecificaciones técnicas: Celda de Batería: 21700 | Paquete: 4.0Ah (120min) o 8.0Ah (240min). Alta capacidad para las tareas más demandantes, asegurando largas jornadas ininterrumpidas.\n\nMAXXT Ecuador te ofrece calidad comprobada y repuestos originales para que tu trabajo no se detenga.', 'Celda de Batería: 21700 | Paquete: 4.0Ah (120min) o 8.0Ah (240min)', 'img_catalogo/20M-B2040.webp', 1, '2026-04-06 13:06:10'),
(174, 'Lija redonda 7\" 8 huecos #150', 'webp_1774464828_lija_redonda_7_', 'MAXXT', 'Accesorios', 'Presentación Única: 36.67', 'Optimiza el uso de tus herramientas con la Lija redonda 7\" 8 huecos #150, un accesorio original de MAXXT.\n\nEspecificaciones técnicas: Sémola: #150 | Tamaño: 7\" | Huecos: 8. Material abrasivo de primera categoría que garantiza acabados uniformes y excelente extracción de polvo.\n\nMAXXT Ecuador te ofrece calidad comprobada y repuestos originales para que tu trabajo no se detenga.', 'Sémola: #150 | Tamaño: 7\" | Huecos: 8', 'img_catalogo/webp_1774464828_lija_redonda_7_.webp', 1, '2026-04-06 13:06:10'),
(175, 'Lija redonda 7\" 8 huecos #120', 'webp_1774464798_lija_redonda_7_', 'MAXXT', 'Accesorios', 'Presentación Única: 19.55', 'Optimiza el uso de tus herramientas con la Lija redonda 7\" 8 huecos #120, un accesorio original de MAXXT.\n\nEspecificaciones técnicas: Sémola: #120 | Tamaño: 7\" | Huecos: 8. Granos de alta durabilidad ideales para preparaciones medias antes del acabado final.\n\nMAXXT Ecuador te ofrece calidad comprobada y repuestos originales para que tu trabajo no se detenga.', 'Sémola: #120 | Tamaño: 7\" | Huecos: 8', 'img_catalogo/webp_1774464798_lija_redonda_7_.webp', 1, '2026-04-06 13:06:10'),
(176, 'Lija redonda 7\" 8 huecos #100', 'webp_1774464780_lija_redonda_7_', 'MAXXT', 'Accesorios', 'Presentación Única: 27.21', 'Optimiza el uso de tus herramientas con la Lija redonda 7\" 8 huecos #100, un accesorio original de MAXXT.\n\nEspecificaciones técnicas: Sémola: #100 | Tamaño: 7\" | Huecos: 8. Especial para remover material grueso e imperfecciones con gran velocidad.\n\nMAXXT Ecuador te ofrece calidad comprobada y repuestos originales para que tu trabajo no se detenga.', 'Sémola: #100 | Tamaño: 7\" | Huecos: 8', 'img_catalogo/webp_1774464780_lija_redonda_7_.webp', 1, '2026-04-06 13:06:10'),
(177, 'Lija redonda 9\" 8 huecos #150', 'webp_1774464988_lija_redonda_9_', 'MAXXT', 'Accesorios', 'Presentación Única: 31.26', 'Optimiza el uso de tus herramientas con la Lija redonda 9\" 8 huecos #150, un accesorio original de MAXXT.\n\nEspecificaciones técnicas: Sémola: #150 | Tamaño: 9\" | Huecos: 8. Formato amplio para lijadoras de pared, asegurando pulidos lisos y sin rayones.\n\nMAXXT Ecuador te ofrece calidad comprobada y repuestos originales para que tu trabajo no se detenga.', 'Sémola: #150 | Tamaño: 9\" | Huecos: 8', 'img_catalogo/webp_1774464988_lija_redonda_9_.webp', 1, '2026-04-06 13:06:10'),
(178, 'Lija redonda 9\" 8 huecos #120', 'webp_1774464977_lija_redonda_9_', 'MAXXT', 'Accesorios', 'Presentación Única: 36.43', 'Optimiza el uso de tus herramientas con la Lija redonda 9\" 8 huecos #120, un accesorio original de MAXXT.\n\nEspecificaciones técnicas: Sémola: #120 | Tamaño: 9\" | Huecos: 8. Excelente balance entre remoción de capas y suavidad en superficies amplias.\n\nMAXXT Ecuador te ofrece calidad comprobada y repuestos originales para que tu trabajo no se detenga.', 'Sémola: #120 | Tamaño: 9\" | Huecos: 8', 'img_catalogo/webp_1774464977_lija_redonda_9_.webp', 1, '2026-04-06 13:06:10'),
(179, 'Lija redonda 9\" 8 huecos #100', 'webp_1774464963_lija_redonda_9_', 'MAXXT', 'Accesorios', 'Presentación Única: 30.01', 'Optimiza el uso de tus herramientas con la Lija redonda 9\" 8 huecos #100, un accesorio original de MAXXT.\n\nEspecificaciones técnicas: Sémola: #100 | Tamaño: 9\" | Huecos: 8. Ideal para el primer contacto con superficies porosas y desbaste intensivo.\n\nMAXXT Ecuador te ofrece calidad comprobada y repuestos originales para que tu trabajo no se detenga.', 'Sémola: #100 | Tamaño: 9\" | Huecos: 8', 'img_catalogo/webp_1774464963_lija_redonda_9_.webp', 1, '2026-04-06 13:06:10'),
(180, 'Lija redonda 9\" 6 huecos #150', 'webp_1774464926_lija_redonda_9_', 'MAXXT', 'Accesorios', 'Presentación Única: 35.07', 'Optimiza el uso de tus herramientas con la Lija redonda 9\" 6 huecos #150, un accesorio original de MAXXT.\n\nEspecificaciones técnicas: Sémola: #150 | Tamaño: 9\" | Huecos: 6. Diseñada para un anclaje firme y una absorción eficaz del polvo durante el lijado fino.\n\nMAXXT Ecuador te ofrece calidad comprobada y repuestos originales para que tu trabajo no se detenga.', 'Sémola: #150 | Tamaño: 9\" | Huecos: 6', 'img_catalogo/webp_1774464926_lija_redonda_9_.webp', 1, '2026-04-06 13:06:10'),
(181, 'Lija redonda 9\" 6 huecos #120', 'webp_1774464914_lija_redonda_9_', 'MAXXT', 'Accesorios', 'Presentación Única: 11.38', 'Optimiza el uso de tus herramientas con la Lija redonda 9\" 6 huecos #120, un accesorio original de MAXXT.\n\nEspecificaciones técnicas: Sémola: #120 | Tamaño: 9\" | Huecos: 6. El consumible perfecto para tareas intermedias y preparación de paredes de yeso.\n\nMAXXT Ecuador te ofrece calidad comprobada y repuestos originales para que tu trabajo no se detenga.', 'Sémola: #120 | Tamaño: 9\" | Huecos: 6', 'img_catalogo/webp_1774464914_lija_redonda_9_.webp', 1, '2026-04-06 13:06:10'),
(182, 'Lija redonda 9\" 6 huecos #100', 'webp_1774464903_lija_redonda_9_', 'MAXXT', 'Accesorios', 'Presentación Única: 25.27', 'Optimiza el uso de tus herramientas con la Lija redonda 9\" 6 huecos #100, un accesorio original de MAXXT.\n\nEspecificaciones técnicas: Sémola: #100 | Tamaño: 9\" | Huecos: 6. Máxima abrasión para arrancar residuos y nivelar paredes rápidamente.\n\nMAXXT Ecuador te ofrece calidad comprobada y repuestos originales para que tu trabajo no se detenga.', 'Sémola: #100 | Tamaño: 9\" | Huecos: 6', 'img_catalogo/webp_1774464903_lija_redonda_9_.webp', 1, '2026-04-06 13:06:10'),
(183, 'Disco de fieltro para estirado', '20MWFWDW120', 'MAXXT', 'Accesorios', 'Presentación Única: 43.76', 'Optimiza el uso de tus herramientas con el Disco de fieltro para estirado, un accesorio original de MAXXT.\n\nEspecificaciones técnicas: Rueda: 120X100 | Abertura: 19mm | Material principal: Wool felt. Indispensable para trabajos de pulido final que requieren el máximo brillo como espejos.\n\nMAXXT Ecuador te ofrece calidad comprobada y repuestos originales para que tu trabajo no se detenga.', 'Rueda: 120X100 | Abertura: 19mm | Material principal: Wool felt', 'img_catalogo/20MWFWDW120.webp', 1, '2026-04-06 13:06:10'),
(184, 'Disco de nylon para estirado', '20MNWDW120', 'MAXXT', 'Accesorios', 'Presentación Única: 13.33', 'Optimiza el uso de tus herramientas con el Disco de nylon para estirado, un accesorio original de MAXXT.\n\nEspecificaciones técnicas: Rueda: 120X100mm | Abertura: 19mm | Sémola: #60 a #400. Elimina óxido y prepara superficies metálicas sin comprometer el material base.\n\nMAXXT Ecuador te ofrece calidad comprobada y repuestos originales para que tu trabajo no se detenga.', 'Rueda: 120X100mm | Abertura: 19mm | Sémola: #60 a #400', 'img_catalogo/20MNWDW120.webp', 1, '2026-04-06 13:06:10'),
(185, 'Cepillo de nylon para estirado de alambre', '20MNBWDW120', 'MAXXT', 'Accesorios', 'Presentación Única: 27.54', 'Optimiza el uso de tus herramientas con el Cepillo de nylon para estirado de alambre, un accesorio original de MAXXT.\n\nEspecificaciones técnicas: Rueda: 120X100mm | Abertura: 19mm | Sémola: #60 a #400. Cerdas resistentes para lograr el efecto satinado o envejecido en madera y metal.\n\nMAXXT Ecuador te ofrece calidad comprobada y repuestos originales para que tu trabajo no se detenga.', 'Rueda: 120X100mm | Abertura: 19mm | Sémola: #60 a #400', 'img_catalogo/20MNBWDW120.webp', 1, '2026-04-06 13:06:10'),
(186, 'Disco abrasivo de óxido de aluminio', '20MAODW120', 'MAXXT', 'Accesorios', 'Presentación Única: 22.43', 'Optimiza el uso de tus herramientas con el Disco abrasivo de óxido de aluminio, un accesorio original de MAXXT.\n\nEspecificaciones técnicas: Rueda: 120X100mm | Abertura: 19mm | Sémola: #60, #80, #100, #120. Excelente para debastar, difuminar soldaduras y dar textura homogénea.\n\nMAXXT Ecuador te ofrece calidad comprobada y repuestos originales para que tu trabajo no se detenga.', 'Rueda: 120X100mm | Abertura: 19mm | Sémola: #60, #80, #100, #120', 'img_catalogo/20MAODW120.webp', 1, '2026-04-06 13:06:10'),
(187, 'Disco de acero para estirado', '20MSWDW120', 'MAXXT', 'Accesorios', 'Presentación Única: 36.93', 'Optimiza el uso de tus herramientas con el Disco de acero para estirado, un accesorio original de MAXXT.\n\nEspecificaciones técnicas: Rueda: 120X100 | Abertura: 19mm | Material: 0.3mm\\/0.5mm Copper-plated Steel. Alambres agresivos para remover costras gruesas de óxido, pintura vieja y escoria.\n\nMAXXT Ecuador te ofrece calidad comprobada y repuestos originales para que tu trabajo no se detenga.', 'Rueda: 120X100 | Abertura: 19mm | Material: 0.3mm\\/0.5mm Copper-plated Steel', 'img_catalogo/20MSWDW120.webp', 1, '2026-04-06 13:06:10'),
(188, 'Cepillo de carburo de silicio para estirado', '20MSCDW120', 'MAXXT', 'Accesorios', 'Presentación Única: 41.57', 'Optimiza el uso de tus herramientas con el Cepillo de carburo de silicio para estirado, un accesorio original de MAXXT.\n\nEspecificaciones técnicas: Rueda: 120X100 | Abertura: 19mm | Material: Silicona carbide brush. Las cerdas impregnadas de abrasivo logran acabados decorativos y limpieza profunda.\n\nMAXXT Ecuador te ofrece calidad comprobada y repuestos originales para que tu trabajo no se detenga.', 'Rueda: 120X100 | Abertura: 19mm | Material: Silicona carbide brush', 'img_catalogo/20MSCDW120.webp', 1, '2026-04-06 13:06:10'),
(189, 'Ángulo para amoladora (cubierta contra el polvo)', '20MAGDS125', 'MAXXT', 'Accesorios', 'Presentación Única: 38.75', 'Optimiza el uso de tus herramientas con el Ángulo para amoladora (cubierta contra el polvo), un accesorio original de MAXXT.\n\nEspecificaciones técnicas: Ajuste: 5\" \\/ 4-1\\/2\" (110mm-125mm) para amoladora angular. Un complemento de seguridad que protege al usuario y conecta a extractores para un trabajo limpio.\n\nMAXXT Ecuador te ofrece calidad comprobada y repuestos originales para que tu trabajo no se detenga.', 'Ajuste: 5\" \\/ 4-1\\/2\" (110mm-125mm) para amoladora angular', 'img_catalogo/20MAGDS125.webp', 1, '2026-04-06 13:06:10'),
(190, 'Tornillos en tirillas', '20TORTLL', 'MAXXT', 'Accesorios', 'Presentación Única:7.56', 'Optimiza el uso de tus herramientas con los Tornillos en tirillas, un accesorio original de MAXXT.\r\n\r\nEspecificaciones técnicas: Cantidad: 1000 screws | Material: Steel | Diámetro: 3.5mm | Longitudes: 30/35/40/50mm. Consumible imprescindible para alimentación automática en atornilladores, acelerando el recubrimiento de Drywall.\r\n\r\nMAXXT Ecuador te ofrece calidad comprobada y repuestos originales para que tu trabajo no se detenga.', 'Cantidad: 1000 screws | Material: Steel | Diámetro: 3.5mm | Longitudes: 30/35/40/50mm', 'img_catalogo/webp_1776873125_tornillos_en_ti.webp', 1, '2026-04-06 13:06:10');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `marcas_admin`
--

CREATE TABLE `marcas_admin` (
  `id` int(11) NOT NULL,
  `nombre` varchar(100) NOT NULL,
  `fecha_creacion` timestamp NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `marcas_admin`
--

INSERT INTO `marcas_admin` (`id`, `nombre`, `fecha_creacion`) VALUES
(1, 'maxxt', '2026-04-06 11:02:27');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `metricas_b2c`
--

CREATE TABLE `metricas_b2c` (
  `id` int(11) NOT NULL,
  `evento` varchar(50) NOT NULL,
  `valor` varchar(255) NOT NULL,
  `categoria` varchar(50) DEFAULT 'General',
  `ip` varchar(45) DEFAULT NULL,
  `region` varchar(100) DEFAULT 'Desconocida',
  `fecha` timestamp NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `metricas_b2c`
--

INSERT INTO `metricas_b2c` (`id`, `evento`, `valor`, `categoria`, `ip`, `region`, `fecha`) VALUES
(1, 'Ver Producto', 'Atornillador Gypsum Inalámbrico', 'Herramientas Drywall', '::1', 'Desconocida', '2026-04-09 09:32:52'),
(2, 'Ver Producto', 'Accesorio de cargador para atornillador', 'Herramientas Drywall', '::1', 'Desconocida', '2026-04-09 10:12:40'),
(3, 'Añadir a Carrito', 'Accesorio de cargador para atornillador', 'Herramientas Drywall', '::1', 'Desconocida', '2026-04-09 10:12:42'),
(4, 'Añadir a Wishlist', 'Accesorio de cargador para atornillador', 'Herramientas Drywall', '::1', 'Desconocida', '2026-04-09 10:12:42'),
(5, 'Ver Producto', 'Accesorio de cargador para atornillador', 'Herramientas Drywall', '::1', 'Desconocida', '2026-04-09 10:12:42'),
(6, 'Ver Producto', 'Atornillador Gypsum Inalámbrico', 'Herramientas Drywall', '::1', 'Desconocida', '2026-04-09 11:56:01'),
(7, 'Ver Producto', 'Clavadora inalámbrica', 'Herramientas Drywall', '::1', 'Desconocida', '2026-04-09 11:56:04'),
(8, 'Ver Producto', 'Mezcladora - batidora doble', 'Mezclador de Paletas', '::1', 'Desconocida', '2026-04-09 14:08:17'),
(9, 'Ver Producto', 'Mezcladora - batidora doble', 'Mezclador de Paletas', '::1', 'Desconocida', '2026-04-09 14:11:39'),
(10, 'Ver Producto', 'Taladro a batería', 'Taladros Percutores', '::1', 'Desconocida', '2026-04-09 14:25:35'),
(11, 'Búsqueda Live', 'a', 'Filtro', '::1', 'Desconocida', '2026-04-09 14:34:11'),
(12, 'Búsqueda Live', 'at', 'Filtro', '::1', 'Desconocida', '2026-04-09 14:34:11'),
(13, 'Búsqueda Live', 'ator', 'Filtro', '::1', 'Desconocida', '2026-04-09 14:34:12'),
(14, 'Búsqueda Live', 'atorni', 'Filtro', '::1', 'Desconocida', '2026-04-09 14:34:12'),
(15, 'Búsqueda Live', 'atornilla', 'Filtro', '::1', 'Desconocida', '2026-04-09 14:34:13'),
(16, 'Búsqueda Live', 'atornillado', 'Filtro', '::1', 'Desconocida', '2026-04-09 14:34:13'),
(17, 'Búsqueda Live', 'atornilladora', 'Filtro', '::1', 'Desconocida', '2026-04-09 14:34:13'),
(18, 'Búsqueda Live', 'atornillador', 'Filtro', '::1', 'Desconocida', '2026-04-09 14:34:14'),
(19, 'Búsqueda IA', 'atornillador', 'AI', '::1', 'Desconocida', '2026-04-09 14:34:16'),
(20, 'Ver Producto', 'Tornillos en tirillas', 'Accesorios', '::1', 'Desconocida', '2026-04-09 14:36:04'),
(21, 'Checkout Iniciado', '$263.05', 'General', '::1', 'Desconocida', '2026-04-09 14:37:26'),
(22, 'Ver Producto', 'Lijadora de paneles de yeso', 'Lijadoras de Paneles de Yeso', '::1', 'Desconocida', '2026-04-09 14:41:07'),
(23, 'Ver Producto', 'Lijadora de paneles de yeso', 'Lijadoras de Paneles de Yeso', '::1', 'Desconocida', '2026-04-09 14:41:15'),
(24, 'Ver Producto', 'Lijadora de paneles de yeso', 'Lijadoras de Paneles de Yeso', '::1', 'Desconocida', '2026-04-09 14:41:34'),
(25, 'Ver Producto', 'Atornillador Gypsum Inalámbrico', 'Herramientas Drywall', '::1', 'Desconocida', '2026-04-09 14:41:38'),
(26, 'Ver Producto', 'Accesorio de cargador para atornillador', 'Herramientas Drywall', '::1', 'Desconocida', '2026-04-09 14:41:39'),
(27, 'Ver Producto', 'Atornillador Gypsum Inalámbrico', 'Herramientas Drywall', '::1', 'Desconocida', '2026-04-09 14:42:03'),
(28, 'Ver Producto', 'Cepillo de carburo de silicio para estirado', 'Accesorios', '::1', 'Desconocida', '2026-04-09 14:50:22'),
(29, 'Añadir a Carrito', 'Atornillador Gypsum 20V', 'Herramientas Eléctricas', '172.16.0.10', 'Guayaquil, Guayas', '2026-04-09 15:57:59'),
(30, 'Añadir a Carrito', 'Atornillador Gypsum 20V', 'Herramientas Eléctricas', '192.168.1.1', 'Cuenca, Azuay', '2026-04-09 02:57:59'),
(31, 'Búsqueda Live', 'martillo', 'Live', '192.168.1.1', 'Guayaquil, Guayas', '2026-04-09 03:57:59'),
(32, 'Añadir a Carrito', 'Taladro Percutor 1/2', 'Herramientas Eléctricas', '10.0.0.5', 'Cuenca, Azuay', '2026-04-09 06:57:59'),
(33, 'Búsqueda Live', 'martillo', 'Live', '10.0.0.5', 'Quito, Pichincha', '2026-04-08 23:57:59'),
(34, 'Añadir a Wishlist', 'Nivel Láser Verde 360', 'Medición', '192.168.1.1', 'Cuenca, Azuay', '2026-04-09 17:57:59'),
(35, 'Búsqueda Live', 'martillo', 'Live', '10.0.0.5', 'Cuenca, Azuay', '2026-04-09 21:57:59'),
(36, 'Añadir a Carrito', 'Atornillador Gypsum 20V', 'Herramientas Eléctricas', '192.168.1.1', 'Cuenca, Azuay', '2026-04-09 11:57:59'),
(37, 'Añadir a Carrito', 'Atornillador Gypsum 20V', 'Herramientas Eléctricas', '172.16.0.10', 'Guayaquil, Guayas', '2026-04-09 13:57:59'),
(38, 'Búsqueda Live', 'martillo', 'Live', '172.16.0.10', 'Cuenca, Azuay', '2026-04-09 07:57:59'),
(39, 'Búsqueda IA', 'mejor taladro para drywall', 'AI', '192.168.1.1', 'Quito, Pichincha', '2026-04-09 21:57:59'),
(40, 'Añadir a Wishlist', 'Nivel Láser Verde 360', 'Medición', '172.16.0.10', 'Guayaquil, Guayas', '2026-04-09 11:57:59'),
(41, 'Búsqueda Live', 'martillo', 'Live', '192.168.1.1', 'Guayaquil, Guayas', '2026-04-08 23:57:59'),
(42, 'Añadir a Carrito', 'Atornillador Gypsum 20V', 'Herramientas Eléctricas', '192.168.1.1', 'Quito, Pichincha', '2026-04-09 01:57:59'),
(43, 'Añadir a Wishlist', 'Nivel Láser Verde 360', 'Medición', '172.16.0.10', 'Quito, Pichincha', '2026-04-09 15:57:59'),
(44, 'Añadir a Carrito', 'Taladro Percutor 1/2', 'Herramientas Eléctricas', '192.168.1.1', 'Quito, Pichincha', '2026-04-09 08:57:59'),
(45, 'Búsqueda IA', 'mejor taladro para drywall', 'AI', '10.0.0.5', 'Cuenca, Azuay', '2026-04-09 00:57:59'),
(46, 'Añadir a Carrito', 'Taladro Percutor 1/2', 'Herramientas Eléctricas', '172.16.0.10', 'Cuenca, Azuay', '2026-04-09 15:57:59'),
(47, 'Búsqueda Live', 'martillo', 'Live', '172.16.0.10', 'Cuenca, Azuay', '2026-04-09 09:57:59'),
(48, 'Búsqueda IA', 'mejor taladro para drywall', 'AI', '172.16.0.10', 'Quito, Pichincha', '2026-04-09 04:57:59'),
(49, 'Búsqueda Live', 'martillo', 'Live', '192.168.1.1', 'Guayaquil, Guayas', '2026-04-09 03:57:59'),
(50, 'Añadir a Carrito', 'Taladro Percutor 1/2', 'Herramientas Eléctricas', '172.16.0.10', 'Guayaquil, Guayas', '2026-04-09 12:57:59'),
(51, 'Búsqueda IA', 'mejor taladro para drywall', 'AI', '172.16.0.10', 'Quito, Pichincha', '2026-04-09 17:57:59'),
(52, 'Añadir a Carrito', 'Atornillador Gypsum 20V', 'Herramientas Eléctricas', '10.0.0.5', 'Guayaquil, Guayas', '2026-04-09 12:57:59'),
(53, 'Búsqueda IA', 'mejor taladro para drywall', 'AI', '10.0.0.5', 'Quito, Pichincha', '2026-04-09 12:57:59'),
(54, 'Añadir a Wishlist', 'Nivel Láser Verde 360', 'Medición', '10.0.0.5', 'Cuenca, Azuay', '2026-04-09 03:57:59'),
(55, 'Búsqueda IA', 'mejor taladro para drywall', 'AI', '10.0.0.5', 'Cuenca, Azuay', '2026-04-09 08:57:59'),
(56, 'Añadir a Carrito', 'Atornillador Gypsum 20V', 'Herramientas Eléctricas', '172.16.0.10', 'Quito, Pichincha', '2026-04-09 01:57:59'),
(57, 'Búsqueda IA', 'mejor taladro para drywall', 'AI', '10.0.0.5', 'Cuenca, Azuay', '2026-04-09 17:57:59'),
(58, 'Añadir a Carrito', 'Atornillador Gypsum 20V', 'Herramientas Eléctricas', '10.0.0.5', 'Cuenca, Azuay', '2026-04-09 15:57:59'),
(59, 'Añadir a Wishlist', 'Nivel Láser Verde 360', 'Medición', '172.16.0.10', 'Cuenca, Azuay', '2026-04-09 13:57:59'),
(60, 'Añadir a Wishlist', 'Nivel Láser Verde 360', 'Medición', '172.16.0.10', 'Cuenca, Azuay', '2026-04-09 06:57:59'),
(61, 'Añadir a Wishlist', 'Nivel Láser Verde 360', 'Medición', '10.0.0.5', 'Quito, Pichincha', '2026-04-09 11:57:59'),
(62, 'Búsqueda IA', 'mejor taladro para drywall', 'AI', '192.168.1.1', 'Cuenca, Azuay', '2026-04-09 04:57:59'),
(63, 'Añadir a Carrito', 'Atornillador Gypsum 20V', 'Herramientas Eléctricas', '172.16.0.10', 'Cuenca, Azuay', '2026-04-08 22:57:59'),
(64, 'Búsqueda IA', 'mejor taladro para drywall', 'AI', '192.168.1.1', 'Quito, Pichincha', '2026-04-09 13:57:59'),
(65, 'Añadir a Carrito', 'Taladro Percutor 1/2', 'Herramientas Eléctricas', '192.168.1.1', 'Cuenca, Azuay', '2026-04-09 21:57:59'),
(66, 'Añadir a Carrito', 'Taladro Percutor 1/2', 'Herramientas Eléctricas', '172.16.0.10', 'Guayaquil, Guayas', '2026-04-09 00:57:59'),
(67, 'Búsqueda IA', 'mejor taladro para drywall', 'AI', '172.16.0.10', 'Guayaquil, Guayas', '2026-04-09 07:57:59'),
(68, 'Añadir a Wishlist', 'Nivel Láser Verde 360', 'Medición', '10.0.0.5', 'Guayaquil, Guayas', '2026-04-08 22:57:59'),
(69, 'Búsqueda Live', 'martillo', 'Live', '172.16.0.10', 'Cuenca, Azuay', '2026-04-09 20:57:59'),
(70, 'Búsqueda Live', 'martillo', 'Live', '172.16.0.10', 'Cuenca, Azuay', '2026-04-09 20:57:59'),
(71, 'Búsqueda IA', 'mejor taladro para drywall', 'AI', '172.16.0.10', 'Quito, Pichincha', '2026-04-09 01:57:59'),
(72, 'Añadir a Carrito', 'Taladro Percutor 1/2', 'Herramientas Eléctricas', '172.16.0.10', 'Quito, Pichincha', '2026-04-09 05:57:59'),
(73, 'Búsqueda Live', 'martillo', 'Live', '192.168.1.1', 'Guayaquil, Guayas', '2026-04-09 03:57:59'),
(74, 'Añadir a Carrito', 'Atornillador Gypsum 20V', 'Herramientas Eléctricas', '192.168.1.1', 'Guayaquil, Guayas', '2026-04-09 14:57:59'),
(75, 'Añadir a Wishlist', 'Nivel Láser Verde 360', 'Medición', '172.16.0.10', 'Cuenca, Azuay', '2026-04-09 12:57:59'),
(76, 'Búsqueda Live', 'martillo', 'Live', '10.0.0.5', 'Quito, Pichincha', '2026-04-09 12:57:59'),
(77, 'Añadir a Carrito', 'Atornillador Gypsum 20V', 'Herramientas Eléctricas', '172.16.0.10', 'Quito, Pichincha', '2026-04-09 00:57:59'),
(78, 'Búsqueda IA', 'mejor taladro para drywall', 'AI', '10.0.0.5', 'Quito, Pichincha', '2026-04-09 09:57:59'),
(79, 'Búsqueda Live', 'martillo', 'Live', '10.0.0.5', 'Quito, Pichincha', '2026-04-09 04:59:26'),
(80, 'Añadir a Wishlist', 'Nivel Láser Verde 360', 'Medición', '192.168.1.1', 'Guayaquil, Guayas', '2026-04-08 22:59:26'),
(81, 'Añadir a Carrito', 'Taladro Percutor 1/2', 'Herramientas Eléctricas', '172.16.0.10', 'Quito, Pichincha', '2026-04-09 13:59:26'),
(82, 'Búsqueda IA', 'mejor taladro para drywall', 'AI', '172.16.0.10', 'Cuenca, Azuay', '2026-04-09 07:59:26'),
(83, 'Añadir a Carrito', 'Atornillador Gypsum 20V', 'Herramientas Eléctricas', '172.16.0.10', 'Cuenca, Azuay', '2026-04-09 14:59:26'),
(84, 'Búsqueda IA', 'mejor taladro para drywall', 'AI', '172.16.0.10', 'Quito, Pichincha', '2026-04-09 17:59:26'),
(85, 'Búsqueda Live', 'martillo', 'Live', '10.0.0.5', 'Quito, Pichincha', '2026-04-09 02:59:26'),
(86, 'Búsqueda Live', 'martillo', 'Live', '172.16.0.10', 'Guayaquil, Guayas', '2026-04-09 08:59:26'),
(87, 'Añadir a Wishlist', 'Nivel Láser Verde 360', 'Medición', '192.168.1.1', 'Cuenca, Azuay', '2026-04-09 18:59:26'),
(88, 'Añadir a Carrito', 'Atornillador Gypsum 20V', 'Herramientas Eléctricas', '10.0.0.5', 'Guayaquil, Guayas', '2026-04-09 05:59:26'),
(89, 'Añadir a Carrito', 'Taladro Percutor 1/2', 'Herramientas Eléctricas', '192.168.1.1', 'Guayaquil, Guayas', '2026-04-09 02:59:26'),
(90, 'Búsqueda IA', 'mejor taladro para drywall', 'AI', '172.16.0.10', 'Quito, Pichincha', '2026-04-09 19:59:26'),
(91, 'Añadir a Wishlist', 'Nivel Láser Verde 360', 'Medición', '192.168.1.1', 'Cuenca, Azuay', '2026-04-09 19:59:26'),
(92, 'Añadir a Carrito', 'Atornillador Gypsum 20V', 'Herramientas Eléctricas', '192.168.1.1', 'Cuenca, Azuay', '2026-04-09 01:59:26'),
(93, 'Añadir a Carrito', 'Taladro Percutor 1/2', 'Herramientas Eléctricas', '172.16.0.10', 'Guayaquil, Guayas', '2026-04-09 20:59:26'),
(94, 'Añadir a Carrito', 'Atornillador Gypsum 20V', 'Herramientas Eléctricas', '10.0.0.5', 'Guayaquil, Guayas', '2026-04-09 05:59:26'),
(95, 'Búsqueda IA', 'mejor taladro para drywall', 'AI', '10.0.0.5', 'Quito, Pichincha', '2026-04-09 08:59:26'),
(96, 'Añadir a Carrito', 'Taladro Percutor 1/2', 'Herramientas Eléctricas', '192.168.1.1', 'Guayaquil, Guayas', '2026-04-09 01:59:26'),
(97, 'Añadir a Carrito', 'Atornillador Gypsum 20V', 'Herramientas Eléctricas', '172.16.0.10', 'Guayaquil, Guayas', '2026-04-08 23:59:26'),
(98, 'Añadir a Carrito', 'Taladro Percutor 1/2', 'Herramientas Eléctricas', '10.0.0.5', 'Cuenca, Azuay', '2026-04-09 20:59:26'),
(99, 'Búsqueda IA', 'mejor taladro para drywall', 'AI', '192.168.1.1', 'Cuenca, Azuay', '2026-04-09 11:59:26'),
(100, 'Añadir a Carrito', 'Atornillador Gypsum 20V', 'Herramientas Eléctricas', '10.0.0.5', 'Cuenca, Azuay', '2026-04-09 11:59:26'),
(101, 'Búsqueda Live', 'martillo', 'Live', '172.16.0.10', 'Guayaquil, Guayas', '2026-04-08 22:59:26'),
(102, 'Búsqueda Live', 'martillo', 'Live', '192.168.1.1', 'Cuenca, Azuay', '2026-04-09 15:59:26'),
(103, 'Búsqueda Live', 'martillo', 'Live', '192.168.1.1', 'Guayaquil, Guayas', '2026-04-09 09:59:26'),
(104, 'Búsqueda IA', 'mejor taladro para drywall', 'AI', '172.16.0.10', 'Quito, Pichincha', '2026-04-09 13:59:26'),
(105, 'Añadir a Carrito', 'Atornillador Gypsum 20V', 'Herramientas Eléctricas', '192.168.1.1', 'Guayaquil, Guayas', '2026-04-09 00:59:26'),
(106, 'Búsqueda Live', 'martillo', 'Live', '172.16.0.10', 'Quito, Pichincha', '2026-04-09 16:59:26'),
(107, 'Añadir a Wishlist', 'Nivel Láser Verde 360', 'Medición', '172.16.0.10', 'Guayaquil, Guayas', '2026-04-09 03:59:26'),
(108, 'Búsqueda Live', 'martillo', 'Live', '10.0.0.5', 'Cuenca, Azuay', '2026-04-09 15:59:26'),
(109, 'Búsqueda Live', 'martillo', 'Live', '10.0.0.5', 'Cuenca, Azuay', '2026-04-09 21:59:26'),
(110, 'Añadir a Wishlist', 'Nivel Láser Verde 360', 'Medición', '192.168.1.1', 'Quito, Pichincha', '2026-04-09 16:59:26'),
(111, 'Búsqueda IA', 'mejor taladro para drywall', 'AI', '10.0.0.5', 'Guayaquil, Guayas', '2026-04-09 19:59:26'),
(112, 'Búsqueda IA', 'mejor taladro para drywall', 'AI', '192.168.1.1', 'Quito, Pichincha', '2026-04-09 05:59:26'),
(113, 'Añadir a Wishlist', 'Nivel Láser Verde 360', 'Medición', '10.0.0.5', 'Guayaquil, Guayas', '2026-04-09 12:59:26'),
(114, 'Búsqueda IA', 'mejor taladro para drywall', 'AI', '172.16.0.10', 'Quito, Pichincha', '2026-04-09 01:59:26'),
(115, 'Búsqueda IA', 'mejor taladro para drywall', 'AI', '192.168.1.1', 'Cuenca, Azuay', '2026-04-09 21:59:26'),
(116, 'Añadir a Wishlist', 'Nivel Láser Verde 360', 'Medición', '10.0.0.5', 'Quito, Pichincha', '2026-04-09 13:59:26'),
(117, 'Añadir a Carrito', 'Taladro Percutor 1/2', 'Herramientas Eléctricas', '192.168.1.1', 'Quito, Pichincha', '2026-04-09 21:59:26'),
(118, 'Añadir a Carrito', 'Taladro Percutor 1/2', 'Herramientas Eléctricas', '192.168.1.1', 'Cuenca, Azuay', '2026-04-09 07:59:26'),
(119, 'Búsqueda IA', 'mejor taladro para drywall', 'AI', '192.168.1.1', 'Guayaquil, Guayas', '2026-04-09 06:59:26'),
(120, 'Añadir a Wishlist', 'Nivel Láser Verde 360', 'Medición', '192.168.1.1', 'Quito, Pichincha', '2026-04-09 09:59:26'),
(121, 'Búsqueda IA', 'mejor taladro para drywall', 'AI', '192.168.1.1', 'Quito, Pichincha', '2026-04-09 21:59:26'),
(122, 'Añadir a Wishlist', 'Nivel Láser Verde 360', 'Medición', '192.168.1.1', 'Guayaquil, Guayas', '2026-04-09 09:59:26'),
(123, 'Búsqueda Live', 'martillo', 'Live', '10.0.0.5', 'Guayaquil, Guayas', '2026-04-09 10:59:26'),
(124, 'Añadir a Wishlist', 'Nivel Láser Verde 360', 'Medición', '192.168.1.1', 'Quito, Pichincha', '2026-04-09 01:59:26'),
(125, 'Búsqueda IA', 'mejor taladro para drywall', 'AI', '172.16.0.10', 'Quito, Pichincha', '2026-04-09 02:59:26'),
(126, 'Añadir a Wishlist', 'Nivel Láser Verde 360', 'Medición', '172.16.0.10', 'Cuenca, Azuay', '2026-04-09 19:59:26'),
(127, 'Añadir a Carrito', 'Atornillador Gypsum 20V', 'Herramientas Eléctricas', '10.0.0.5', 'Quito, Pichincha', '2026-04-09 02:59:26'),
(128, 'Búsqueda Live', 'martillo', 'Live', '10.0.0.5', 'Quito, Pichincha', '2026-04-09 12:59:26'),
(129, 'Ver Producto', 'Lijadora de paneles de yeso con extensión', 'Lijadoras de Paneles de Yeso', '181.198.221.86', 'Guayaquil, Guayas', '2026-04-13 15:14:38'),
(130, 'Ver Producto', 'Pistola de calafateo inalámbrica', 'Pistolas de Silicona', '181.198.221.86', 'Guayaquil, Guayas', '2026-04-13 15:14:43'),
(131, 'Ver Producto', 'Pistola de calafateo inalámbrica', 'Pistolas de Silicona', '181.198.221.86', 'Guayaquil, Guayas', '2026-04-13 15:15:03'),
(132, 'Ver Producto', 'Pistola de calafateo inalámbrica', 'Pistolas de Silicona', '181.198.221.86', 'Guayaquil, Guayas', '2026-04-13 15:15:11'),
(133, 'Ver Producto', 'Pistola de calafateo inalámbrica', 'Pistolas de Silicona', '181.198.221.86', 'Guayaquil, Guayas', '2026-04-13 15:15:22'),
(134, 'Ver Producto', 'Pistola para silicona inalámbrica', 'Pistolas de Silicona', '181.198.221.86', 'Guayaquil, Guayas', '2026-04-13 15:15:37'),
(135, 'Ver Producto', 'Lijadora de paneles de yeso con extensión', 'Lijadoras de Paneles de Yeso', '181.198.221.86', 'Guayaquil, Guayas', '2026-04-13 15:15:59'),
(136, 'Ver Producto', 'Pistola para silicona inalámbrica', 'Pistolas de Silicona', '181.198.221.86', 'Guayaquil, Guayas', '2026-04-13 15:16:02'),
(137, 'Ver Producto', 'Pistola de calor con pantalla LCD', 'Pistolas de Aire Caliente', '181.198.221.86', 'Guayaquil, Guayas', '2026-04-13 15:16:05'),
(138, 'Añadir a Wishlist', 'Cepillo de carburo de silicio para estirado', 'Accesorios', '181.198.221.86', 'Guayaquil, Guayas', '2026-04-13 15:47:47'),
(139, 'Añadir a Wishlist', 'Ángulo para amoladora (cubierta contra el polvo)', 'Accesorios', '181.198.221.86', 'Guayaquil, Guayas', '2026-04-13 15:47:48'),
(140, 'Añadir a Carrito', 'Cepillo de carburo de silicio para estirado', 'Accesorios', '181.198.221.86', 'Guayaquil, Guayas', '2026-04-13 15:47:51'),
(141, 'Ver Producto', 'Pistola para silicona inalámbrica', 'Pistolas de Silicona', '181.198.221.86', 'Guayaquil, Guayas', '2026-04-13 22:33:28'),
(142, 'Ver Producto', 'Lija redonda 9\" 6 huecos #120', 'Accesorios', '181.198.221.86', 'Guayaquil, Guayas', '2026-04-13 22:33:30'),
(143, 'Ver Producto', 'Lija redonda 9\" 6 huecos #120', 'Accesorios', '181.198.221.86', 'Guayaquil, Guayas', '2026-04-13 22:33:37'),
(144, 'Búsqueda Live', 'taladro', 'Live', '181.198.221.86', 'Guayaquil, Guayas', '2026-04-13 22:34:37'),
(145, 'Búsqueda IA', 'taladro', 'AI', '181.198.221.86', 'Guayaquil, Guayas', '2026-04-13 22:34:43'),
(146, 'Añadir a Carrito', 'Disco de acero para estirado', 'Accesorios', '181.198.221.86', 'Guayaquil, Guayas', '2026-04-13 22:42:34'),
(147, 'Checkout Iniciado', '$36.93', 'General', '181.198.221.86', 'Guayaquil, Guayas', '2026-04-13 22:42:36'),
(148, 'Checkout Iniciado', '$36.93', 'General', '181.198.221.86', 'Guayaquil, Guayas', '2026-04-13 22:42:50'),
(149, 'Checkout Iniciado', '$36.93', 'General', '181.198.221.86', 'Guayaquil, Guayas', '2026-04-13 22:43:51'),
(150, 'Añadir a Carrito', 'Pistola de calafateo inalámbrica', 'Pistolas de Silicona', '181.198.221.86', 'Guayaquil, Guayas', '2026-04-13 22:44:18'),
(151, 'Checkout Iniciado', '$156.33', 'General', '181.198.221.86', 'Guayaquil, Guayas', '2026-04-13 22:45:30'),
(152, 'Checkout Iniciado', '$156.33', 'General', '181.198.221.86', 'Guayaquil, Guayas', '2026-04-13 22:59:50'),
(153, 'Ver Producto', 'Tornillos en tirillas', 'Accesorios', '181.198.221.86', 'Guayaquil, Guayas', '2026-04-13 23:05:14'),
(154, 'Checkout Iniciado', '$156.33', 'General', '181.198.221.86', 'Guayaquil, Guayas', '2026-04-13 23:05:29'),
(155, 'Ver Producto', 'test', 'Lijadoras de Paneles de Yeso', '181.198.221.86', 'Guayaquil, Guayas', '2026-04-22 14:28:52'),
(156, 'Ver Producto', 'test', 'Lijadoras de Paneles de Yeso', '181.198.221.86', 'Guayaquil, Guayas', '2026-04-22 14:28:59'),
(157, 'Filtrar Marca', 'MAXXT', 'General', '181.198.221.86', 'Guayaquil, Guayas', '2026-04-22 15:24:46'),
(158, 'Filtrar Marca', 'Todas', 'General', '181.198.221.86', 'Guayaquil, Guayas', '2026-04-22 15:24:46'),
(159, 'Ver Producto', 'Lijadora de paneles de yeso con extensión', 'Lijadoras de Paneles de Yeso', '181.198.221.86', 'Guayaquil, Guayas', '2026-04-22 15:40:42'),
(160, 'Ver Producto', 'Lijadora de paneles de yeso', 'Lijadoras de Paneles de Yeso', '181.198.221.86', 'Guayaquil, Guayas', '2026-04-22 15:40:50'),
(161, 'Añadir a Wishlist', 'Lijadora de paneles de yeso', 'Lijadoras de Paneles de Yeso', '200.85.80.16', 'Guayaquil, Guayas', '2026-04-22 17:15:52'),
(162, 'Añadir a Carrito', 'Pistola de calor inalámbrica', 'Pistolas de Aire Caliente', '181.198.221.86', 'Guayaquil, Guayas', '2026-04-22 17:36:46'),
(163, 'Ver Producto', 'Pistola de calor inalámbrica', 'Pistolas de Aire Caliente', '181.198.221.86', 'Guayaquil, Guayas', '2026-04-22 17:37:00'),
(164, 'Ver Producto', 'Pistola de calor con cable', 'Pistolas de Aire Caliente', '181.198.221.86', 'Guayaquil, Guayas', '2026-04-22 19:58:41'),
(165, 'Añadir a Carrito', 'Pistola de calor con cable', 'Pistolas de Aire Caliente', '181.198.221.86', 'Guayaquil, Guayas', '2026-04-22 19:58:50'),
(166, 'Ver Producto', 'Lijadora de paneles de yeso con extensión', 'Lijadoras de Paneles de Yeso', '181.198.221.86', 'Guayaquil, Guayas', '2026-04-22 19:59:30'),
(167, 'Añadir a Carrito', 'Lijadora de paneles de yeso con extensión', 'Lijadoras de Paneles de Yeso', '181.198.221.86', 'Guayaquil, Guayas', '2026-04-22 19:59:43'),
(168, 'Checkout Iniciado', '$176.41', 'General', '181.198.221.86', 'Guayaquil, Guayas', '2026-04-22 20:00:11'),
(169, 'Ver Producto', 'Lijadora de paneles de yeso', 'Lijadoras de Paneles de Yeso', '181.198.221.86', 'Guayaquil, Guayas', '2026-04-22 20:04:07'),
(170, 'Añadir a Carrito', 'Lijadora de paneles de yeso', 'Lijadoras de Paneles de Yeso', '181.198.221.86', 'Guayaquil, Guayas', '2026-04-22 20:04:22'),
(171, 'Ver Producto', 'Tornillos en tirillas', 'Accesorios', '181.198.221.86', 'Guayaquil, Guayas', '2026-04-22 20:05:11'),
(172, 'Añadir a Carrito', 'Tornillos en tirillas', 'Accesorios', '181.198.221.86', 'Guayaquil, Guayas', '2026-04-22 20:05:13'),
(173, 'Ver Producto', 'Tornillos en tirillas', 'Accesorios', '181.198.221.86', 'Guayaquil, Guayas', '2026-05-07 17:26:23'),
(174, 'Ver Producto', 'Disco de nylon para estirado', 'Accesorios', '181.198.221.86', 'Guayaquil, Guayas', '2026-05-07 17:26:29'),
(175, 'Ver Producto', 'Pistola para silicona inalámbrica', 'Pistolas de Silicona', '181.198.221.86', 'Guayaquil, Guayas', '2026-05-07 17:26:42'),
(176, 'Añadir a Wishlist', 'Tornillos en tirillas', 'Accesorios', '181.198.221.86', 'Guayaquil, Guayas', '2026-05-07 17:36:03'),
(177, 'Añadir a Wishlist', 'Ángulo para amoladora (cubierta contra el polvo)', 'Accesorios', '181.198.221.86', 'Guayaquil, Guayas', '2026-05-07 17:36:04'),
(178, 'Añadir a Carrito', 'Cepillo de carburo de silicio para estirado', 'Accesorios', '181.198.221.86', 'Guayaquil, Guayas', '2026-05-07 17:36:07'),
(179, 'Ver Producto', 'Ángulo para amoladora (cubierta contra el polvo)', 'Accesorios', '181.198.221.86', 'Guayaquil, Guayas', '2026-05-11 22:31:15'),
(180, 'Ver Producto', 'Cepillo de carburo de silicio para estirado', 'Accesorios', '181.198.221.86', 'Guayaquil, Guayas', '2026-05-11 22:31:19'),
(181, 'Ver Producto', 'Lijadora de paneles de yeso con extensión', 'Lijadoras de Paneles de Yeso', '181.198.221.86', 'Guayaquil, Guayas', '2026-05-11 22:31:23'),
(182, 'Ver Producto', 'Lijadora de paneles de yeso con extensión', 'Lijadoras de Paneles de Yeso', '181.198.221.86', 'Guayaquil, Guayas', '2026-05-14 21:18:12'),
(183, 'Añadir a Carrito', 'Lijadora de paneles de yeso con extensión', 'Lijadoras de Paneles de Yeso', '181.198.221.86', 'Guayaquil, Guayas', '2026-05-14 21:18:24'),
(184, 'Ver Producto', 'Tornillos en tirillas', 'Accesorios', '200.85.83.248', 'Quito, Pichincha', '2026-05-16 15:55:25');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `metricas_cotizaciones`
--

CREATE TABLE `metricas_cotizaciones` (
  `id` int(11) NOT NULL,
  `session_id` varchar(128) DEFAULT NULL,
  `ruc_cliente` varchar(50) DEFAULT NULL,
  `producto_nombre` varchar(255) DEFAULT NULL,
  `cantidad` int(11) DEFAULT NULL,
  `precio_unitario` decimal(10,2) DEFAULT NULL,
  `subtotal` decimal(10,2) DEFAULT NULL,
  `fecha` timestamp NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `metricas_cotizaciones`
--

INSERT INTO `metricas_cotizaciones` (`id`, `session_id`, `ruc_cliente`, `producto_nombre`, `cantidad`, `precio_unitario`, `subtotal`, `fecha`) VALUES
(1, 'hjjr2p4rev146fk8b0gqc6lm14', '0917052375001', 'Clavadora inalámbrica', 7, 48.68, 340.79, '2026-04-09 13:51:03'),
(2, 'hjjr2p4rev146fk8b0gqc6lm14', '0917052375001', 'Clavadora inalámbrica', 7, 48.68, 340.79, '2026-04-09 13:55:13'),
(3, 'hjjr2p4rev146fk8b0gqc6lm14', '0917052375001', 'Taladro percutor 1050W', 1, 86.95, 86.95, '2026-04-09 13:55:13'),
(4, 'hjjr2p4rev146fk8b0gqc6lm14', '0917052375001', 'Clavadora inalámbrica', 7, 48.68, 340.79, '2026-04-09 14:02:52'),
(5, 'hjjr2p4rev146fk8b0gqc6lm14', '0917052375001', 'Taladro percutor 1050W', 1, 86.95, 86.95, '2026-04-09 14:02:52'),
(6, 'hjjr2p4rev146fk8b0gqc6lm14', '0917052375001', 'Amoladora / Pulidora angular inalámbrica', 1, 98.75, 98.75, '2026-04-09 14:02:52'),
(7, 'hjjr2p4rev146fk8b0gqc6lm14', '0917052375001', 'Mezcladora - batidora doble', 6, 12.48, 74.87, '2026-04-09 14:08:41'),
(8, 'hjjr2p4rev146fk8b0gqc6lm14', '0917052375001', 'Mezcladora - batidora doble', 1, 74.87, 74.87, '2026-04-09 14:09:27'),
(9, 'hjjr2p4rev146fk8b0gqc6lm14', '0917052375001', 'Mezcladora - batidora doble', 6, 12.48, 74.87, '2026-04-09 14:12:00'),
(10, 'hjjr2p4rev146fk8b0gqc6lm14', '0917052375001', 'Atornillador Gypsum Inalámbrico', 7, 55.02, 385.11, '2026-04-09 14:18:13'),
(11, 'hjjr2p4rev146fk8b0gqc6lm14', '0917052375001', 'Taladro a batería', 8, 27.78, 222.25, '2026-04-09 14:25:53'),
(12, 'e6euvpmlt2dqmbes40tagijsfv', '0917052375001', 'Atornillador Gypsum Inalámbrico MAXXT', 1, 385.11, 385.11, '2026-04-13 08:07:51'),
(13, 'e6euvpmlt2dqmbes40tagijsfv', '0917052375001', 'Lijadora de paneles de yeso inalámbrica MAXXT', 1, 96.66, 96.66, '2026-04-13 08:45:28'),
(14, 'e6euvpmlt2dqmbes40tagijsfv', '0917052375001', 'Taladro percutor 1050W', 1, 86.95, 86.95, '2026-04-13 08:46:52'),
(15, 'e6euvpmlt2dqmbes40tagijsfv', '0917052375001', 'Atornillador Gypsum Inalámbrico', 1, 385.11, 385.11, '2026-04-13 08:54:22'),
(16, 'e6euvpmlt2dqmbes40tagijsfv', '0917052375001', 'Lijadora de paneles de yeso inalámbrica', 1, 96.66, 96.66, '2026-04-13 09:19:00'),
(17, 'e6euvpmlt2dqmbes40tagijsfv', '0917052375001', 'Pistola de calor con pantalla LCD MAXXT', 1, 82.48, 82.48, '2026-04-13 09:25:08'),
(18, 'ofacifj8drog9gvq8i2nodlteu', '0917052375001', 'Pistola engrasadora manual a batería', 1, 207.02, 207.02, '2026-04-13 15:19:44'),
(19, 'ofacifj8drog9gvq8i2nodlteu', '0917052375001', 'Tornillos en tirillas', 5, 7.56, 37.80, '2026-04-13 15:45:49'),
(20, 'ofacifj8drog9gvq8i2nodlteu', '0917052375001', 'Atornillador Gypsum Inalámbrico MAXXT', 1, 385.11, 385.11, '2026-04-13 22:51:14'),
(21, 'ofacifj8drog9gvq8i2nodlteu', '0917052375001', 'Lijadora orbital inalámbrica', 1, 119.69, 119.69, '2026-04-13 22:57:42');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `pedidos_b2b`
--

CREATE TABLE `pedidos_b2b` (
  `id` int(11) NOT NULL,
  `ruc_cliente` varchar(50) NOT NULL,
  `nombre_cliente` varchar(100) NOT NULL,
  `total` decimal(10,2) NOT NULL,
  `items_json` longtext NOT NULL,
  `status` enum('pendiente','confirmado','despachado','cancelado') DEFAULT 'pendiente',
  `fecha` timestamp NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `pedidos_b2b`
--

INSERT INTO `pedidos_b2b` (`id`, `ruc_cliente`, `nombre_cliente`, `total`, `items_json`, `status`, `fecha`) VALUES
(1, '0917052375001', 'Nabil', 87.00, '[{\"producto\":\"Lijadora de paneles de yeso inalámbrica MAXXT\",\"cantidad\":1,\"precio\":\"$96.66\"}]', 'despachado', '2026-04-13 08:45:33'),
(2, '0917052375001', 'Nabil', 78.26, '[{\"producto\":\"Taladro percutor 1050W\",\"cantidad\":1,\"precio\":\"$86.95\"}]', 'confirmado', '2026-04-13 08:47:00'),
(3, '0917052375001', 'Nabil', 78.26, '[{\"producto\":\"Taladro percutor 1050W\",\"cantidad\":1,\"precio\":\"$86.95\"}]', 'pendiente', '2026-04-13 08:47:11'),
(4, '0917052375001', 'Nabil', 78.26, '[{\"producto\":\"Taladro percutor 1050W\",\"cantidad\":1,\"precio\":\"$86.95\"}]', 'pendiente', '2026-04-13 08:47:26'),
(5, '0917052375001', 'Nabil', 346.60, '[{\"producto\":\"Atornillador Gypsum Inalámbrico\",\"cantidad\":1,\"precio\":\"$385.11\"}]', 'pendiente', '2026-04-13 08:54:27'),
(6, '0917052375001', 'Nabil', 346.60, '[{\"producto\":\"Atornillador Gypsum Inalámbrico\",\"cantidad\":1,\"precio\":\"$385.11\"}]', 'pendiente', '2026-04-13 08:59:16'),
(7, '0917052375001', 'Nabil', 346.60, '[{\"producto\":\"Atornillador Gypsum Inalámbrico\",\"cantidad\":1,\"precio\":\"$385.11\"}]', 'pendiente', '2026-04-13 09:15:27'),
(8, '0917052375001', 'Nabil', 87.00, '[{\"producto\":\"Lijadora de paneles de yeso inalámbrica\",\"cantidad\":1,\"precio\":\"$96.66\"}]', 'pendiente', '2026-04-13 09:19:03'),
(9, '0917052375001', 'Nabil', 74.23, '[{\"producto\":\"Pistola de calor con pantalla LCD MAXXT\",\"cantidad\":1,\"precio\":\"$82.48\"}]', 'pendiente', '2026-04-13 09:25:11'),
(10, '0917052375001', 'Nabil', 74.23, '[{\"producto\":\"Pistola de calor con pantalla LCD MAXXT\",\"cantidad\":1,\"precio\":\"$82.48\"}]', 'pendiente', '2026-04-13 09:37:33'),
(11, '0917052375001', 'Nabil', 186.32, '[{\"producto\":\"Pistola engrasadora manual a batería\",\"cantidad\":1,\"precio\":\"$207.02\"}]', 'pendiente', '2026-04-13 15:19:55'),
(12, '0917052375001', 'Nabil', 346.60, '[{\"producto\":\"Atornillador Gypsum Inalámbrico MAXXT\",\"cantidad\":1,\"precio\":\"$385.11\"}]', 'pendiente', '2026-04-13 22:54:03'),
(13, '0917052375001', 'Nabil', 107.72, '[{\"producto\":\"Lijadora orbital inalámbrica\",\"cantidad\":1,\"precio\":\"$119.69\"}]', 'pendiente', '2026-05-16 15:56:02');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `pedidos_publicos`
--

CREATE TABLE `pedidos_publicos` (
  `id` int(11) NOT NULL,
  `total` decimal(10,2) NOT NULL,
  `items_json` longtext NOT NULL,
  `status` enum('contacto_iniciado','completado','cancelado') DEFAULT 'contacto_iniciado',
  `source` varchar(255) DEFAULT 'directo',
  `fecha` timestamp NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `pedidos_publicos`
--

INSERT INTO `pedidos_publicos` (`id`, `total`, `items_json`, `status`, `source`, `fecha`) VALUES
(1, 263.05, '[{\"nombre\":\"Accesorio de cargador para atornillador\",\"cantidad\":1,\"precio\":263.05}]', 'contacto_iniciado', 'directo', '2026-04-09 14:37:26'),
(2, 3999.47, '[{\"nombre\":\"Lijadora de paneles de yeso\",\"cantidad\":1,\"precio\":111.44},{\"nombre\":\"Disco de acero para estirado\",\"cantidad\":1,\"precio\":36.93},{\"nombre\":\"Atornillador Gypsum Inalámbrico\",\"cantidad\":10,\"precio\":385.11}]', 'contacto_iniciado', 'IA: Nivel Láser Verde 360', '2026-04-09 17:12:01'),
(3, 77.50, '[{\"nombre\":\"Ángulo para amoladora (cubierta contra el polvo)\",\"cantidad\":2,\"precio\":38.75}]', 'contacto_iniciado', 'directo', '2026-04-09 17:13:31'),
(4, 707.55, '[{\"nombre\":\"Accesorio de cargador para atornillador\",\"cantidad\":1,\"precio\":263.05},{\"nombre\":\"Taladro a batería\",\"cantidad\":2,\"precio\":222.25}]', 'contacto_iniciado', 'directo', '2026-04-10 12:01:39');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `productos_impulsados`
--

CREATE TABLE `productos_impulsados` (
  `id` int(11) NOT NULL,
  `nombre_producto` varchar(255) NOT NULL,
  `fecha_limite` datetime NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `productos_impulsados`
--

INSERT INTO `productos_impulsados` (`id`, `nombre_producto`, `fecha_limite`) VALUES
(5, 'Disco de acero para estirado', '2026-04-14 17:41:57');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `sesiones_b2b`
--

CREATE TABLE `sesiones_b2b` (
  `session_id` varchar(128) NOT NULL,
  `ruc_cliente` varchar(50) DEFAULT NULL,
  `clic_whatsapp` tinyint(1) DEFAULT 0,
  `gestionado_admin` tinyint(1) DEFAULT 0,
  `fecha` timestamp NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `sesiones_b2b`
--

INSERT INTO `sesiones_b2b` (`session_id`, `ruc_cliente`, `clic_whatsapp`, `gestionado_admin`, `fecha`) VALUES
('e6euvpmlt2dqmbes40tagijsfv', '0917052375001', 1, 0, '2026-04-10 08:48:14'),
('hjjr2p4rev146fk8b0gqc6lm14', '0917052375001', 0, 1, '2026-04-09 13:47:54'),
('ofacifj8drog9gvq8i2nodlteu', '0917052375001', 0, 1, '2026-04-13 15:18:49'),
('teriut4lhh1mjlsmgfmbkr504m', '0917052365002', 0, 0, '2026-04-14 14:05:19'),
('vo1afv9lemhhum8gk08j4dln1j', '0917052375001', 0, 0, '2026-04-14 22:06:51');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `social_config`
--

CREATE TABLE `social_config` (
  `id` int(11) NOT NULL,
  `platform` varchar(50) NOT NULL,
  `account_name` varchar(255) DEFAULT NULL,
  `access_token` text NOT NULL,
  `expires_at` datetime DEFAULT NULL,
  `account_id` varchar(100) DEFAULT NULL,
  `is_active` tinyint(1) DEFAULT 1,
  `created_at` timestamp NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `social_config`
--

INSERT INTO `social_config` (`id`, `platform`, `account_name`, `access_token`, `expires_at`, `account_id`, `is_active`, `created_at`) VALUES
(24, 'instagram', 'Nabilia', 'EAATLV5GzVF0BRILCvsZA9k7u3mymlxeSLkZB3kOthxdTNbM6ncznXTuUiPr5LM6ZCNgTRDvc8d4YMOBqRZBavh7zZBaRomAJ1rqqBvVwJlDLeJIgzlpdXKBwNb6Wkhbx4hRaawyNu86NgYiibvoUR3EC2yqNObuQcZC9xzgyXNuW7VeLZBTfDRYNtAUHxlr1vIxOcOT', '2026-06-09 18:19:44', '17841445114416333', 1, '2026-04-10 13:19:45'),
(25, 'facebook', 'Nabilia', 'EAATLV5GzVF0BRJ4PJjnxolNnXGLCGVRXw3puCmHRjj12r2P0eYJ6yTDcm1DG3DCZCdDdTnm5U8SCDqPF42ICeqNKqwXPnHEHnYMBoGPr2XbKWRGiB3M6goBx1rQ3mcsAXOgZC7SiEf7k3tE6OInouKOMfG9GDhBiQzIq1Om2WEwiwWkOUyUVd8i4flegZA5I261ckkb', '2026-06-09 18:20:00', '777666585439887', 1, '2026-04-10 13:20:00');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `social_metrics`
--

CREATE TABLE `social_metrics` (
  `id` int(11) NOT NULL,
  `platform` enum('facebook','instagram') NOT NULL,
  `account_insights` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`account_insights`)),
  `raw_metrics` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`raw_metrics`)),
  `ai_assessment` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`ai_assessment`)),
  `timestamp` timestamp NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `usuarios_admin`
--

CREATE TABLE `usuarios_admin` (
  `id` int(11) NOT NULL,
  `usuario` varchar(50) NOT NULL,
  `password_hash` varchar(255) NOT NULL,
  `nombre` varchar(100) NOT NULL,
  `rol` enum('master','gestor') DEFAULT 'gestor',
  `activo` tinyint(1) DEFAULT 1,
  `fecha_creacion` timestamp NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `usuarios_admin`
--

INSERT INTO `usuarios_admin` (`id`, `usuario`, `password_hash`, `nombre`, `rol`, `activo`, `fecha_creacion`) VALUES
(1, 'admin', '$2y$10$b0KLFZVzvQnNgVxOunR5SOM2mrp02aUG0h7FH2al7Ge9WF8rAspMS', 'Administrador IMPROGYP', 'master', 1, '2026-04-06 08:41:09');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `usuarios_b2b`
--

CREATE TABLE `usuarios_b2b` (
  `id` int(11) NOT NULL,
  `ruc` varchar(50) NOT NULL,
  `nombre` varchar(100) NOT NULL,
  `pin` varchar(255) NOT NULL,
  `descuento` int(11) DEFAULT 0,
  `telefono` varchar(20) DEFAULT NULL,
  `activo` tinyint(1) DEFAULT 1,
  `fecha_creacion` timestamp NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `usuarios_b2b`
--

INSERT INTO `usuarios_b2b` (`id`, `ruc`, `nombre`, `pin`, `descuento`, `telefono`, `activo`, `fecha_creacion`) VALUES
(1, '0917052375001', 'Nabil', '123456', 10, '0991754887', 1, '2026-04-09 13:46:26'),
(3, '0917052365002', 'Manuel', '123456', 10, '0987920342', 1, '2026-04-14 14:02:35');

--
-- Índices para tablas volcadas
--

--
-- Indices de la tabla `b2b_historial_chat`
--
ALTER TABLE `b2b_historial_chat`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_ruc` (`ruc_cliente`);

--
-- Indices de la tabla `categorias_admin`
--
ALTER TABLE `categorias_admin`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `nombre` (`nombre`);

--
-- Indices de la tabla `improgyp_catalogo`
--
ALTER TABLE `improgyp_catalogo`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_categoria` (`categoria`),
  ADD KEY `idx_codigo` (`codigo`),
  ADD KEY `idx_marca` (`marca`);

--
-- Indices de la tabla `marcas_admin`
--
ALTER TABLE `marcas_admin`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `nombre` (`nombre`);

--
-- Indices de la tabla `metricas_b2c`
--
ALTER TABLE `metricas_b2c`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_evento` (`evento`),
  ADD KEY `idx_categoria` (`categoria`),
  ADD KEY `idx_fecha` (`fecha`);

--
-- Indices de la tabla `metricas_cotizaciones`
--
ALTER TABLE `metricas_cotizaciones`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_session` (`session_id`),
  ADD KEY `idx_ruc` (`ruc_cliente`);

--
-- Indices de la tabla `pedidos_b2b`
--
ALTER TABLE `pedidos_b2b`
  ADD PRIMARY KEY (`id`);

--
-- Indices de la tabla `pedidos_publicos`
--
ALTER TABLE `pedidos_publicos`
  ADD PRIMARY KEY (`id`);

--
-- Indices de la tabla `productos_impulsados`
--
ALTER TABLE `productos_impulsados`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_producto` (`nombre_producto`);

--
-- Indices de la tabla `sesiones_b2b`
--
ALTER TABLE `sesiones_b2b`
  ADD PRIMARY KEY (`session_id`);

--
-- Indices de la tabla `social_config`
--
ALTER TABLE `social_config`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `idx_platform` (`platform`);

--
-- Indices de la tabla `social_metrics`
--
ALTER TABLE `social_metrics`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_platform` (`platform`);

--
-- Indices de la tabla `usuarios_admin`
--
ALTER TABLE `usuarios_admin`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `usuario` (`usuario`);

--
-- Indices de la tabla `usuarios_b2b`
--
ALTER TABLE `usuarios_b2b`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `ruc` (`ruc`),
  ADD KEY `idx_ruc` (`ruc`);

--
-- AUTO_INCREMENT de las tablas volcadas
--

--
-- AUTO_INCREMENT de la tabla `b2b_historial_chat`
--
ALTER TABLE `b2b_historial_chat`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=263;

--
-- AUTO_INCREMENT de la tabla `categorias_admin`
--
ALTER TABLE `categorias_admin`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=602;

--
-- AUTO_INCREMENT de la tabla `improgyp_catalogo`
--
ALTER TABLE `improgyp_catalogo`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=194;

--
-- AUTO_INCREMENT de la tabla `marcas_admin`
--
ALTER TABLE `marcas_admin`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=587;

--
-- AUTO_INCREMENT de la tabla `metricas_b2c`
--
ALTER TABLE `metricas_b2c`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=185;

--
-- AUTO_INCREMENT de la tabla `metricas_cotizaciones`
--
ALTER TABLE `metricas_cotizaciones`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=22;

--
-- AUTO_INCREMENT de la tabla `pedidos_b2b`
--
ALTER TABLE `pedidos_b2b`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=14;

--
-- AUTO_INCREMENT de la tabla `pedidos_publicos`
--
ALTER TABLE `pedidos_publicos`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT de la tabla `productos_impulsados`
--
ALTER TABLE `productos_impulsados`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT de la tabla `social_config`
--
ALTER TABLE `social_config`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=26;

--
-- AUTO_INCREMENT de la tabla `social_metrics`
--
ALTER TABLE `social_metrics`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `usuarios_admin`
--
ALTER TABLE `usuarios_admin`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT de la tabla `usuarios_b2b`
--
ALTER TABLE `usuarios_b2b`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;

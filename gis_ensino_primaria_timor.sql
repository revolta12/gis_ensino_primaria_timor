-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: May 21, 2026 at 07:32 PM
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
-- Database: `gis_ensino_primaria_timor`
--

-- --------------------------------------------------------

--
-- Table structure for table `avaliasaun_escola`
--

CREATE TABLE `avaliasaun_escola` (
  `id` int(10) UNSIGNED NOT NULL,
  `escola_id` int(10) UNSIGNED NOT NULL,
  `naran_avaliador` varchar(100) NOT NULL,
  `email_avaliador` varchar(150) DEFAULT NULL,
  `pontuasaun` tinyint(3) UNSIGNED NOT NULL COMMENT '1-5 (kualidade ensino, fasilidade, etc)',
  `komentariu` text DEFAULT NULL,
  `aprovadu` tinyint(1) DEFAULT 0,
  `kria_iha` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `avaliasaun_escola`
--

INSERT INTO `avaliasaun_escola` (`id`, `escola_id`, `naran_avaliador`, `email_avaliador`, `pontuasaun`, `komentariu`, `aprovadu`, `kria_iha`) VALUES
(2, 1, 'deo', 'info@mj.gov.tl', 2, 'deo', 1, '2026-05-20 08:40:56'),
(11, 1, 'deonisio da costa', 'info@mj.gov.tl', 3, 'hhjkl', 1, '2026-05-20 23:36:37');

-- --------------------------------------------------------

--
-- Table structure for table `escola`
--

CREATE TABLE `escola` (
  `id` int(10) UNSIGNED NOT NULL,
  `naran_escola` varchar(200) NOT NULL,
  `slug` varchar(220) NOT NULL,
  `kategoria_id` int(10) UNSIGNED DEFAULT NULL,
  `enderesu` varchar(300) DEFAULT NULL,
  `suku` varchar(100) DEFAULT NULL,
  `postu_administrativu` varchar(100) DEFAULT NULL,
  `municipio` varchar(80) DEFAULT 'Díli',
  `latitude` decimal(10,8) NOT NULL,
  `longitude` decimal(11,8) NOT NULL,
  `telefone` varchar(30) DEFAULT NULL,
  `email_escola` varchar(150) DEFAULT NULL,
  `website` varchar(250) DEFAULT NULL,
  `deskrisaun` text DEFAULT NULL,
  `total_estudante` int(10) UNSIGNED DEFAULT 0,
  `total_estudante_feto` int(10) UNSIGNED DEFAULT 0,
  `total_estudante_mane` int(10) UNSIGNED DEFAULT 0,
  `total_profesor` int(10) UNSIGNED DEFAULT 0,
  `total_profesor_feto` int(10) UNSIGNED DEFAULT 0,
  `total_profesor_mane` int(10) UNSIGNED DEFAULT 0,
  `klase_hosi` tinyint(2) DEFAULT 1 COMMENT 'klase komesa (1)',
  `klase_too` tinyint(2) DEFAULT 6 COMMENT 'klase remata (6)',
  `sistema_ensinu` set('Tetun','Portugues','Ingles','Bahasa Indonesia') DEFAULT NULL,
  `iha_bee_moos` tinyint(1) DEFAULT 0,
  `iha_eletrisidade` tinyint(1) DEFAULT 0,
  `iha_toilet` tinyint(1) DEFAULT 0,
  `foto_prinsipal` varchar(300) DEFAULT NULL,
  `avaliasaun` decimal(3,2) DEFAULT 0.00,
  `total_avaliasaun` int(10) UNSIGNED DEFAULT 0,
  `destakadu` tinyint(1) DEFAULT 0,
  `aktivo` tinyint(1) DEFAULT 1,
  `kria_husi` int(10) UNSIGNED DEFAULT NULL,
  `kria_iha` timestamp NOT NULL DEFAULT current_timestamp(),
  `atualiza_iha` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `escola`
--

INSERT INTO `escola` (`id`, `naran_escola`, `slug`, `kategoria_id`, `enderesu`, `suku`, `postu_administrativu`, `municipio`, `latitude`, `longitude`, `telefone`, `email_escola`, `website`, `deskrisaun`, `total_estudante`, `total_estudante_feto`, `total_estudante_mane`, `total_profesor`, `total_profesor_feto`, `total_profesor_mane`, `klase_hosi`, `klase_too`, `sistema_ensinu`, `iha_bee_moos`, `iha_eletrisidade`, `iha_toilet`, `foto_prinsipal`, `avaliasaun`, `total_avaliasaun`, `destakadu`, `aktivo`, `kria_husi`, `kria_iha`, `atualiza_iha`) VALUES
(1, 'Escola Primaria Dili Centro', 'escola-primaria-dili-centro', 1, 'Rua Presidente Nicolau Lobato', 'Bidau Lecidere', 'Nain Feto', 'Díli', -8.55861000, 125.57361000, '+670 331 2345', 'primaria.dilicentro@moe.tl', '', 'Eskola primaria ne\'e fornese ensino ba labarik sira iha area ne\'e.', 320, 155, 165, 12, 8, 4, 1, 6, 'Tetun,Portugues', 1, 1, 1, 'uploads/escola/escola_6a0ef8359264d9.74188428.jpg', 2.50, 2, 1, 1, NULL, '2026-05-20 04:21:35', '2026-05-21 12:19:01'),
(5, 'Escola Primaria Numero 1 Central Vila Verde', 'escola-primaria-numero-1-central-vila-verde', 1, 'Avenida Bispo Medeiros, Vila Verde', 'Vila Verde', 'Vera Cruz', 'D?li', -8.56157544, 125.57171380, '+670 77000001', 'ep.vilaverde@moe.tl', '', 'Eskola nasion?l ne\'eb? lokaliza iha klarak sidade Dili, iha fasilidade ne\'eb? sufisiente ba labarik sira.', 450, 220, 230, 18, 12, 6, 1, 6, 'Tetun,Portugues', 1, 1, 1, 'uploads/escola/vila_verde.jpg', 4.00, 1, 1, 1, NULL, '2026-05-21 15:33:45', '2026-05-21 15:38:44'),
(6, 'Escola Primaria Central Numero 1 Farol', 'escola-primaria-central-numero-1-farol', 1, 'Rua de Farol, Motael', 'Motael', 'Vera Cruz', 'D?li', -8.55214500, 125.56784100, '+670 77000002', 'ep.farol@moe.tl', '', 'Eskola prim?ria ida ne\'eb? besik iha tasi ibun Farol, ho ist?ria naruk hosi tempu uluk.', 380, 190, 190, 14, 10, 4, 1, 6, 'Tetun,Portugues', 1, 1, 1, 'uploads/escola/farol.jpg', 4.20, 1, 0, 1, NULL, '2026-05-21 15:33:45', '2026-05-21 15:33:45'),
(7, 'Escola Primaria Aimutin', 'escola-primaria-aimutin', 1, 'Estrada de Aimutin, Comoro', 'Comoro', 'Dom Aleixo', 'D?li', -8.56041200, 125.54891200, '+670 77000003', 'ep.aimutin@moe.tl', '', 'Eskola ne\'eb? lokaliza iha ?rea Aimutin, hodi simu estudante barak hosi besik Comoro.', 510, 250, 260, 20, 13, 7, 1, 6, 'Tetun,Portugues', 1, 1, 1, 'uploads/escola/aimutin.jpg', 3.80, 1, 0, 1, NULL, '2026-05-21 15:33:45', '2026-05-21 15:33:45'),
(8, 'Escola Primaria Numero 2 Comoro', 'escola-primaria-numero-2-comoro', 1, 'Avenida Nicolau Lobato, Comoro', 'Comoro', 'Dom Aleixo', 'D?li', -8.55398200, 125.53123000, '+670 77000004', 'ep2.comoro@moe.tl', '', 'Eskola prim?ria ne\'eb? besik iha Rotunda Comoro, fasil ba aksesu transporte p?bliku.', 600, 290, 310, 22, 15, 7, 1, 6, 'Tetun,Portugues', 1, 1, 1, 'uploads/escola/comoro2.jpg', 4.10, 1, 1, 1, NULL, '2026-05-21 15:33:45', '2026-05-21 15:33:45'),
(9, 'Escola Primaria Central Becora', 'escola-primaria-central-becora', 1, 'Estrada de Becora, Dili', 'Becora', 'Cristo Rei', 'D?li', -8.56782300, 125.60124300, '+670 77000005', 'ep.becora@moe.tl', '', 'Eskola boot ida iha ?rea kairak parte leste sidade Dili nian, ho kuantidade estudante ne\'eb? barak.', 580, 285, 295, 24, 16, 8, 1, 6, 'Tetun,Portugues', 1, 1, 1, 'uploads/escola/becora.jpg', 4.00, 1, 1, 1, NULL, '2026-05-21 15:33:45', '2026-05-21 15:33:45'),
(10, 'Escola Primaria Bairro Pite', 'escola-primaria-bairro-pite', 1, 'Rua de Bairro Pite', 'Bairro Pite', 'Dom Aleixo', 'D?li', -8.56621200, 125.55912300, '+670 77000006', 'ep.bairropite@moe.tl', '', 'Eskola prim?ria ne\'eb? harii iha suku Bairro Pite hodi fasilita labarik sira iha vizi?ansa ne\'e.', 420, 210, 210, 15, 11, 4, 1, 6, 'Tetun,Portugues', 1, 1, 1, 'uploads/escola/bairropite.jpg', 3.90, 1, 0, 1, NULL, '2026-05-21 15:33:45', '2026-05-21 15:33:45'),
(11, 'Escola Primaria Bidau Santana', 'escola-primaria-bidau-santana', 1, 'Estrada de Bidau, Santana', 'Bidau Santana', 'Cristo Rei', 'D?li', -8.55410200, 125.58914500, '+670 77000007', 'ep.bidau@moe.tl', '', 'Eskola prim?ria ne\'eb? besik iha tasi ibun no ponte Bidau, ho ambientu ne\'eb? seguru.', 310, 150, 160, 12, 9, 3, 1, 6, 'Tetun,Portugues', 1, 1, 0, 'uploads/escola/bidau.jpg', 3.50, 1, 0, 1, NULL, '2026-05-21 15:33:45', '2026-05-21 15:33:45'),
(12, 'Escola Primaria Taibesi', 'escola-primaria-taibesi', 1, 'Rua de Taibesi, Dili', 'Taibesi', 'Cristo Rei', 'D?li', -8.57102300, 125.59102100, '+670 77000008', 'ep.taibesi@moe.tl', '', 'Eskola prim?ria ne\'eb? lokaliza besik merkadu Taibesi, ho komunidade ne\'eb? dezenvolvidu.', 490, 240, 250, 17, 12, 5, 1, 6, 'Tetun,Portugues', 1, 1, 1, 'uploads/escola/taibesi.jpg', 3.70, 1, 0, 1, NULL, '2026-05-21 15:33:45', '2026-05-21 15:33:45'),
(13, 'Escola Primaria Caicoli', 'escola-primaria-caicoli', 1, 'Rua de Caicoli, Dili', 'Caicoli', 'Vera Cruz', 'D?li', -8.55891200, 125.57100200, '+670 77000009', 'ep.caicoli@moe.tl', '', 'Eskola foun no reabilitadu ona ho fasilidade foun hosi Minist?riu Edukasaun.', 360, 180, 180, 14, 10, 4, 1, 6, 'Tetun,Portugues', 1, 1, 1, 'uploads/escola/caicoli.jpg', 4.30, 1, 1, 1, NULL, '2026-05-21 15:33:45', '2026-05-21 15:33:45'),
(14, 'Escola Primaria Tasi Tolu', 'escola-primaria-tasi-tolu', 1, 'Estrada de Tasi Tolu, Comoro', 'Comoro', 'Dom Aleixo', 'D?li', -8.56391000, 125.50891000, '+670 77000010', 'ep.tasitolu@moe.tl', '', 'Eskola ne\'eb? lokaliza iha tasi tolun nia sorin, providensia edukasaun ba komunidade foun iha tasi-tolu.', 330, 160, 170, 11, 8, 3, 1, 6, 'Tetun', 1, 1, 1, 'uploads/escola/tasitolu.jpg', 3.60, 1, 0, 1, NULL, '2026-05-21 15:33:45', '2026-05-21 15:33:45'),
(35, 'Escola Primaria Coracao de Jesus', 'escola-primaria-coracao-de-jesus', 5, 'Estrada de Becora, Dili', 'Becora', 'Cristo Rei', 'D?li', -8.56510831, 125.60694976, '+670 77100001', 'coracao.jesus@gmail.com', '', 'Eskola privada kat?lika ne\'eb? jere husi par?kia Becora, ho kualidade ensino ne\'eb? di\'ak.', 350, 170, 180, 15, 10, 5, 1, 6, 'Tetun,Portugues', 1, 1, 1, 'uploads/escola/escola_6a0f34c8f22dc5.04658246.png', 4.50, 1, 1, 1, NULL, '2026-05-21 15:51:24', '2026-05-21 16:43:40'),
(36, 'Escola Primaria Sao Pedro', 'escola-primaria-sao-pedro', 5, 'Rua de Sao Pedro, Comoro', 'Comoro', 'Dom Aleixo', 'D?li', -8.55621400, 125.54124500, '+670 77100002', 'sao.pedro@gmail.com', '', 'Eskola privada k?esidu iha ?rea Comoro ne\'eb? f? ko?esimentu boot ba estudante sira.', 420, 200, 220, 18, 12, 6, 1, 6, 'Tetun,Portugues,Ingles', 1, 1, 1, 'uploads/escola/sao_pedro.jpg', 4.60, 1, 1, 1, NULL, '2026-05-21 15:51:24', '2026-05-21 15:51:24'),
(37, 'Escola Primaria Paulo VI', 'escola-primaria-paulo-vi', 5, 'Rua de Caicoli, Dili', 'Caicoli', 'Vera Cruz', 'D?li', -8.55981200, 125.56941200, '+670 77100003', 'paulo_vi@gmail.com', '', 'Eskola kat?lika ne\'eb? harii kleur ona iha klarak sidade Dili hodi eduka jerasaun foun sira.', 390, 190, 200, 16, 11, 5, 1, 6, 'Tetun,Portugues', 1, 1, 1, 'uploads/escola/paulo_vi.jpg', 4.40, 1, 0, 1, NULL, '2026-05-21 15:51:24', '2026-05-21 15:51:24'),
(38, 'Escola Primaria Sao Jose Balide', 'escola-primaria-sao-jose-balide', 5, 'Estrada de Balide, Dili', 'Mascarenhas', 'Vera Cruz', 'D?li', -8.56841200, 125.57412300, '+670 77100004', 'saojose.balide@gmail.com', '', 'Eskola Kristaun (Kat?lika) ne\'eb? ko?esidu ho disiplina aas no dezenvolvimentu karakter.', 480, 230, 250, 20, 14, 6, 1, 6, 'Tetun,Portugues', 1, 1, 1, 'uploads/escola/sao_jose.jpg', 4.70, 1, 1, 1, NULL, '2026-05-21 15:51:24', '2026-05-21 15:51:24'),
(39, 'Escola Primaria Canossa', 'escola-primaria-canossa', 5, 'Rua de Bairro Pite, Dili', 'Bairro Pite', 'Dom Aleixo', 'D?li', -8.56310200, 125.55621400, '+670 77100005', 'canossa.dili@gmail.com', '', 'Eskola privada ne\'eb? jere husi Madre Canossiana sira, ho ambientu aprendizajen ne\'eb? kap?s.', 400, 210, 190, 17, 15, 2, 1, 6, 'Tetun,Portugues', 1, 1, 1, 'uploads/escola/canossa.jpg', 4.80, 1, 1, 1, NULL, '2026-05-21 15:51:24', '2026-05-21 15:51:24'),
(40, 'Escola Primaria Kristal', 'escola-primaria-kristal', 5, 'Rua de Balide, Dili', 'Mascarenhas', 'Vera Cruz', 'D?li', -8.56512300, 125.57102400, '+670 77100006', 'kristal.foundation@gmail.com', '', 'Eskola ne\'eb? harii husi Fundasaun Kristal, ho kualidade kur?kulu nasion?l no internasion?l.', 310, 150, 160, 14, 9, 5, 1, 6, 'Tetun,Portugues,Bahasa Indonesia', 1, 1, 1, 'uploads/escola/kristal.jpg', 4.20, 1, 0, 1, NULL, '2026-05-21 15:51:24', '2026-05-21 15:51:24'),
(41, 'Escola Primaria Maria Auxiliadora', 'escola-primaria-maria-auxiliadora', 5, 'Estrada de Comoro, Dili', 'Comoro', 'Dom Aleixo', 'D?li', -8.55521300, 125.52981200, '+670 77100007', 'maria.aux@gmail.com', '', 'Eskola privada kat?lika ne\'eb? jere husi Madre Salesiana (FMA) sira iha ?rea Comoro.', 330, 180, 150, 13, 11, 2, 1, 6, 'Tetun,Portugues', 1, 1, 1, 'uploads/escola/maria_auxiliadora.jpg', 4.60, 1, 0, 1, NULL, '2026-05-21 15:51:24', '2026-05-21 15:51:24'),
(42, 'Escola Primaria Internacional de Dili', 'escola-primaria-internacional-de-dili', 5, 'Rua de Pantai Kelapa, Dili', 'Fatuhada', 'Dom Aleixo', 'D?li', -8.55012400, 125.55214500, '+670 77100008', 'info@dis.tl', 'www.dis.tl', 'Eskola privadu internasion?l ne\'eb? uza lian Ingl?s nu\'udar lian instrusaun ba kur?kulu.', 220, 105, 115, 12, 8, 4, 1, 6, 'Ingles', 1, 1, 1, 'uploads/escola/dis.jpg', 4.50, 1, 0, 1, NULL, '2026-05-21 15:51:24', '2026-05-21 15:51:24'),
(43, 'Escola Primaria Stamrol', 'escola-primaria-stamrol', 5, 'Rua de Taibesi, Dili', 'Taibesi', 'Cristo Rei', 'D?li', -8.57341200, 125.59410200, '+670 77100009', 'stamrol.edu@gmail.com', '', 'Eskola foun ne\'eb? dezenvolve an lalais ho fasilidade no m?todu hanorin modernu.', 280, 135, 145, 11, 7, 4, 1, 6, 'Tetun,Portugues,Ingles', 1, 1, 1, 'uploads/escola/stamrol.jpg', 4.00, 1, 0, 1, NULL, '2026-05-21 15:51:24', '2026-05-21 15:51:24'),
(44, 'Escola Primaria Quality School', 'escola-primaria-quality-school', 5, 'Rua de Fatuhada, Dili', 'Fatuhada', 'Dom Aleixo', 'D?li', -8.55291400, 125.54910200, '+670 77100010', 'quality.school@gmail.com', '', 'Eskola privada ne\'eb? foku liu ba si?nsia no uza lian Ingl?s m?s nu\'udar lian alternat?vu.', 250, 120, 130, 10, 6, 4, 1, 6, 'Tetun,Ingles', 1, 1, 1, 'uploads/escola/quality_school.jpg', 4.30, 1, 0, 1, NULL, '2026-05-21 15:51:24', '2026-05-21 15:51:24');

-- --------------------------------------------------------

--
-- Table structure for table `escola_fasilidade`
--

CREATE TABLE `escola_fasilidade` (
  `escola_id` int(10) UNSIGNED NOT NULL,
  `fasilidade_id` int(10) UNSIGNED NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `escola_fasilidade`
--

INSERT INTO `escola_fasilidade` (`escola_id`, `fasilidade_id`) VALUES
(5, 1),
(5, 4),
(5, 5),
(5, 6),
(5, 7),
(5, 10),
(35, 6);

-- --------------------------------------------------------

--
-- Table structure for table `fasilidade_escola`
--

CREATE TABLE `fasilidade_escola` (
  `id` int(10) UNSIGNED NOT NULL,
  `naran_fasilidade` varchar(100) NOT NULL,
  `ikonu` varchar(50) DEFAULT NULL,
  `kria_iha` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `fasilidade_escola`
--

INSERT INTO `fasilidade_escola` (`id`, `naran_fasilidade`, `ikonu`, `kria_iha`) VALUES
(1, 'Bee moos', 'water', '2026-05-20 00:00:00'),
(2, 'Eletrisidade', 'plug', '2026-05-20 00:00:00'),
(3, 'Toilet ba estudante', 'toilet', '2026-05-20 00:00:00'),
(4, 'Biblioteka', 'book', '2026-05-20 00:00:00'),
(5, 'Ladrilhosaun', 'blackboard', '2026-05-20 00:00:00'),
(6, 'Area joga / playground', 'futbol', '2026-05-20 00:00:00'),
(7, 'Kompastru / lab computer', 'computer', '2026-05-20 00:00:00'),
(8, 'Akses ba estrada utama', 'road', '2026-05-20 00:00:00'),
(9, 'Kantina eskola', 'utensils', '2026-05-20 00:00:00'),
(10, 'Rampa ba estudante ho deficiencia', 'wheelchair', '2026-05-20 00:00:00');

-- --------------------------------------------------------

--
-- Table structure for table `foto_escola`
--

CREATE TABLE `foto_escola` (
  `id` int(10) UNSIGNED NOT NULL,
  `escola_id` int(10) UNSIGNED NOT NULL,
  `naran_fail` varchar(300) NOT NULL,
  `kaptaun` varchar(200) DEFAULT NULL,
  `ordem` int(10) UNSIGNED DEFAULT 0,
  `kria_iha` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `foto_escola`
--

INSERT INTO `foto_escola` (`id`, `escola_id`, `naran_fail`, `kaptaun`, `ordem`, `kria_iha`) VALUES
(2, 1, 'uploads/escola/escola_6a0ef381f2d8f6.80104677.jpg', NULL, 0, '2026-05-21 11:58:57'),
(4, 1, 'uploads/escola/escola_6a0efa8c4cc302.78935889.png', NULL, 0, '2026-05-21 12:29:00'),
(5, 1, 'uploads/escola/escola_6a0efa8c4d62b7.20625611.jpg', NULL, 1, '2026-05-21 12:29:00'),
(6, 1, 'uploads/escola/escola_6a0efa8c4df535.55187194.png', NULL, 2, '2026-05-21 12:29:00'),
(7, 35, 'uploads/escola/escola_6a0f34d78ec153.04388583.jpg', NULL, 0, '2026-05-21 16:37:43');

-- --------------------------------------------------------

--
-- Table structure for table `kategoria_escola`
--

CREATE TABLE `kategoria_escola` (
  `id` int(10) UNSIGNED NOT NULL,
  `naran_kategoria` varchar(80) NOT NULL,
  `deskrisaun` text DEFAULT NULL,
  `ikonu` varchar(50) DEFAULT 'school',
  `kria_iha` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `kategoria_escola`
--

INSERT INTO `kategoria_escola` (`id`, `naran_kategoria`, `deskrisaun`, `ikonu`, `kria_iha`) VALUES
(1, 'Escola Publica', 'Jestu husi governu', 'landmark', '2026-05-20 00:00:00'),
(5, 'Escola Privada', 'Eskola ne\'eb? jere husi privadu, fundasaun ka dioseze.', 'school', '2026-05-21 15:51:14'),
(6, 'Eskola Privada', 'Jestu husi fundasaun ka setor privadu', 'building', '2026-05-21 15:24:23');

-- --------------------------------------------------------

--
-- Table structure for table `kontaktu_mensajen`
--

CREATE TABLE `kontaktu_mensajen` (
  `id` int(10) UNSIGNED NOT NULL,
  `naran_ema` varchar(100) NOT NULL,
  `email_ema` varchar(150) NOT NULL,
  `asuntu` varchar(200) DEFAULT NULL,
  `mensajen` text NOT NULL,
  `lee_ona` tinyint(1) DEFAULT 0,
  `kria_iha` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `rota_escola`
--

CREATE TABLE `rota_escola` (
  `id` int(10) UNSIGNED NOT NULL,
  `escola_id` int(10) UNSIGNED NOT NULL,
  `husi_fatin` varchar(200) NOT NULL,
  `dalan` text DEFAULT NULL,
  `distansia_km` decimal(6,2) DEFAULT NULL,
  `tempu_minutu` int(10) UNSIGNED DEFAULT NULL,
  `kria_iha` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `utilizador`
--

CREATE TABLE `utilizador` (
  `id` int(10) UNSIGNED NOT NULL,
  `naran` varchar(100) NOT NULL,
  `email` varchar(150) NOT NULL,
  `liafuan_segredu` varchar(255) NOT NULL,
  `papel` enum('admin','staff','inspektor') DEFAULT 'staff',
  `aktivo` tinyint(1) DEFAULT 1,
  `kria_iha` timestamp NOT NULL DEFAULT current_timestamp(),
  `atualiza_iha` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `utilizador`
--

INSERT INTO `utilizador` (`id`, `naran`, `email`, `liafuan_segredu`, `papel`, `aktivo`, `kria_iha`, `atualiza_iha`) VALUES
(2, 'MARIA MENDOSSA', 'maria@gmail.com', '$2y$10$5OgEDJhbZpnka/gk5IXcjeoe/AT5YdihmdnPPtL5hwcfDx9BRGmku', 'admin', 1, '2026-05-20 06:02:26', '2026-05-20 06:03:52');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `avaliasaun_escola`
--
ALTER TABLE `avaliasaun_escola`
  ADD PRIMARY KEY (`id`),
  ADD KEY `escola_id` (`escola_id`);

--
-- Indexes for table `escola`
--
ALTER TABLE `escola`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `slug` (`slug`),
  ADD KEY `kategoria_id` (`kategoria_id`),
  ADD KEY `kria_husi` (`kria_husi`);
ALTER TABLE `escola` ADD FULLTEXT KEY `ft_escola` (`naran_escola`,`enderesu`);

--
-- Indexes for table `escola_fasilidade`
--
ALTER TABLE `escola_fasilidade`
  ADD PRIMARY KEY (`escola_id`,`fasilidade_id`),
  ADD KEY `fasilidade_id` (`fasilidade_id`);

--
-- Indexes for table `fasilidade_escola`
--
ALTER TABLE `fasilidade_escola`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `foto_escola`
--
ALTER TABLE `foto_escola`
  ADD PRIMARY KEY (`id`),
  ADD KEY `escola_id` (`escola_id`);

--
-- Indexes for table `kategoria_escola`
--
ALTER TABLE `kategoria_escola`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `kontaktu_mensajen`
--
ALTER TABLE `kontaktu_mensajen`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `rota_escola`
--
ALTER TABLE `rota_escola`
  ADD PRIMARY KEY (`id`),
  ADD KEY `escola_id` (`escola_id`);

--
-- Indexes for table `utilizador`
--
ALTER TABLE `utilizador`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `email` (`email`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `avaliasaun_escola`
--
ALTER TABLE `avaliasaun_escola`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=13;

--
-- AUTO_INCREMENT for table `escola`
--
ALTER TABLE `escola`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=45;

--
-- AUTO_INCREMENT for table `fasilidade_escola`
--
ALTER TABLE `fasilidade_escola`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- AUTO_INCREMENT for table `foto_escola`
--
ALTER TABLE `foto_escola`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- AUTO_INCREMENT for table `kategoria_escola`
--
ALTER TABLE `kategoria_escola`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT for table `kontaktu_mensajen`
--
ALTER TABLE `kontaktu_mensajen`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `rota_escola`
--
ALTER TABLE `rota_escola`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `utilizador`
--
ALTER TABLE `utilizador`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `avaliasaun_escola`
--
ALTER TABLE `avaliasaun_escola`
  ADD CONSTRAINT `avaliasaun_escola_ibfk_1` FOREIGN KEY (`escola_id`) REFERENCES `escola` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `escola`
--
ALTER TABLE `escola`
  ADD CONSTRAINT `escola_ibfk_1` FOREIGN KEY (`kategoria_id`) REFERENCES `kategoria_escola` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `escola_ibfk_2` FOREIGN KEY (`kria_husi`) REFERENCES `utilizador` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `escola_fasilidade`
--
ALTER TABLE `escola_fasilidade`
  ADD CONSTRAINT `escola_fasilidade_ibfk_1` FOREIGN KEY (`escola_id`) REFERENCES `escola` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `escola_fasilidade_ibfk_2` FOREIGN KEY (`fasilidade_id`) REFERENCES `fasilidade_escola` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `foto_escola`
--
ALTER TABLE `foto_escola`
  ADD CONSTRAINT `foto_escola_ibfk_1` FOREIGN KEY (`escola_id`) REFERENCES `escola` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `rota_escola`
--
ALTER TABLE `rota_escola`
  ADD CONSTRAINT `rota_escola_ibfk_1` FOREIGN KEY (`escola_id`) REFERENCES `escola` (`id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;

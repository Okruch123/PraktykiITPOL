-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Wrz 24, 2026 at 12:14 PM
-- Wersja serwera: 10.4.28-MariaDB
-- Wersja PHP: 8.2.4

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `praktyki_itpol`
--

-- --------------------------------------------------------

--
-- Struktura tabeli dla tabeli `courts`
--

CREATE TABLE `courts` (
  `ID` int(11) NOT NULL,
  `name` varchar(100) NOT NULL DEFAULT 'Kort',
  `surface_id` int(11) DEFAULT 1,
  `surface_type` int(11) NOT NULL,
  `is_outdoor` tinyint(1) NOT NULL,
  `address` varchar(255) NOT NULL,
  `reception_phone` varchar(20) DEFAULT NULL,
  `price_per_hour` decimal(10,2) NOT NULL DEFAULT 60.00,
  `is_active` tinyint(1) NOT NULL DEFAULT 1,
  `photo` varchar(255) DEFAULT NULL,
  `description` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `courts`
--

INSERT INTO `courts` (`ID`, `name`, `surface_id`, `surface_type`, `is_outdoor`, `address`, `reception_phone`, `price_per_hour`, `is_active`, `photo`, `description`) VALUES
(1, 'Kort', 1, 1, 1, 'ul. Jakas Tam 34, Szczecin', '+48 91 123 45 67', 60.00, 1, NULL, NULL),
(2, 'Kort 1', 1, 0, 0, 'ul. Jakas Tam 34, Szczecin', '+48 91 123 45 67', 60.00, 1, 'kort_1.jpg', 'Kort z mączki ceglanej w hali głównej'),
(3, 'Kort 2', 2, 0, 1, 'ul. Jakas Tam 34, Szczecin', '+48 91 123 45 67', 55.00, 1, 'kort_2.jpg', 'Kort ze sztuczną trawą, odkryty'),
(4, 'Kort Centralny', 3, 0, 0, 'ul. Jakas Tam 34, Szczecin', '+48 91 123 45 67', 80.00, 1, 'kort_centralny.jpg', 'Profesjonalny kort z nawierzchnią twardą (Hard) i trybunami');

-- --------------------------------------------------------

--
-- Struktura tabeli dla tabeli `payments`
--

CREATE TABLE `payments` (
  `id` int(11) NOT NULL,
  `reservation_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `amount` decimal(10,2) NOT NULL,
  `currency` varchar(3) NOT NULL DEFAULT 'PLN',
  `provider` varchar(50) NOT NULL DEFAULT 'przelewy24',
  `status` varchar(30) NOT NULL DEFAULT 'pending',
  `created_at` datetime NOT NULL DEFAULT current_timestamp(),
  `paid_at` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `payments`
--

INSERT INTO `payments` (`id`, `reservation_id`, `user_id`, `amount`, `currency`, `provider`, `status`, `created_at`, `paid_at`) VALUES
(35, 48, 6, 60.00, 'PLN', 'przelewy24', 'pending', '2026-09-21 10:52:18', NULL),
(36, 49, 8, 60.00, 'PLN', 'przelewy24', 'pending', '2026-09-21 10:52:54', NULL),
(37, 50, 6, 60.00, 'PLN', 'przelewy24', 'pending', '2026-09-21 10:54:27', NULL),
(38, 51, 6, 60.00, 'PLN', 'przelewy24', 'pending', '2026-09-21 10:54:38', NULL),
(39, 52, 6, 60.00, 'PLN', 'przelewy24', 'pending', '2026-09-21 10:59:52', NULL),
(40, 53, 8, 60.00, 'PLN', 'przelewy24', 'pending', '2026-09-21 11:05:05', NULL),
(41, 54, 8, 60.00, 'PLN', 'przelewy24', 'pending', '2026-09-21 11:05:24', NULL),
(42, 55, 8, 60.00, 'PLN', 'przelewy24', 'pending', '2026-09-21 11:06:27', NULL),
(43, 56, 8, 60.00, 'PLN', 'przelewy24', 'pending', '2026-09-21 11:08:28', NULL),
(44, 57, 8, 60.00, 'PLN', 'przelewy24', 'pending', '2026-09-21 11:11:51', NULL),
(45, 58, 8, 780.00, 'PLN', 'przelewy24', 'pending', '2026-09-22 08:34:26', NULL),
(46, 59, 8, 120.00, 'PLN', 'przelewy24', 'pending', '2026-09-22 08:41:33', NULL),
(47, 60, 8, 60.00, 'PLN', 'przelewy24', 'pending', '2026-09-24 10:17:50', NULL),
(48, 61, 8, 60.00, 'PLN', 'przelewy24', 'pending', '2026-09-24 10:21:47', NULL),
(49, 62, 8, 60.00, 'PLN', 'przelewy24', 'pending', '2026-09-24 10:22:07', NULL),
(50, 63, 8, 60.00, 'PLN', 'przelewy24', 'pending', '2026-09-24 10:29:16', NULL),
(51, 64, 8, 60.00, 'PLN', 'przelewy24', 'pending', '2026-09-24 10:29:38', NULL),
(52, 65, 6, 240.00, 'PLN', 'przelewy24', 'pending', '2026-09-24 10:33:50', NULL),
(53, 66, 8, 60.00, 'PLN', 'przelewy24', 'pending', '2026-09-24 10:37:12', NULL),
(54, 67, 6, 360.00, 'PLN', 'przelewy24', 'pending', '2026-09-24 10:46:01', NULL),
(55, 68, 8, 60.00, 'PLN', 'przelewy24', 'pending', '2026-09-24 10:46:56', NULL),
(56, 69, 8, 60.00, 'PLN', 'przelewy24', 'pending', '2026-09-24 10:48:54', NULL),
(57, 70, 6, 55.00, 'PLN', 'przelewy24', 'pending', '2026-09-24 10:57:46', NULL),
(58, 71, 8, 60.00, 'PLN', 'przelewy24', 'pending', '2026-09-24 11:01:53', NULL),
(59, 72, 8, 60.00, 'PLN', 'przelewy24', 'pending', '2026-09-24 11:05:14', NULL),
(60, 73, 8, 60.00, 'PLN', 'przelewy24', 'pending', '2026-09-24 11:06:15', NULL),
(61, 74, 8, 60.00, 'PLN', 'przelewy24', 'pending', '2026-09-24 11:07:08', NULL),
(62, 75, 8, 60.00, 'PLN', 'przelewy24', 'pending', '2026-09-24 11:08:20', NULL),
(63, 76, 8, 60.00, 'PLN', 'przelewy24', 'pending', '2026-09-24 11:10:04', NULL),
(64, 77, 8, 60.00, 'PLN', 'przelewy24', 'pending', '2026-09-24 11:12:59', NULL),
(65, 78, 8, 60.00, 'PLN', 'przelewy24', 'pending', '2026-09-24 11:15:55', NULL),
(66, 79, 8, 80.00, 'PLN', 'przelewy24', 'pending', '2026-09-24 11:16:14', NULL),
(67, 80, 8, 275.00, 'PLN', 'przelewy24', 'pending', '2026-09-24 11:16:54', NULL),
(68, 81, 8, 275.00, 'PLN', 'przelewy24', 'pending', '2026-09-24 11:17:40', NULL),
(69, 82, 8, 80.00, 'PLN', 'przelewy24', 'pending', '2026-09-24 11:18:18', NULL),
(70, 83, 8, 80.00, 'PLN', 'przelewy24', 'pending', '2026-09-24 11:28:40', NULL),
(71, 84, 8, 80.00, 'PLN', 'przelewy24', 'pending', '2026-09-24 11:30:53', NULL),
(72, 85, 8, 80.00, 'PLN', 'przelewy24', 'pending', '2026-09-24 11:31:33', NULL),
(73, 86, 8, 60.00, 'PLN', 'przelewy24', 'pending', '2026-09-24 11:48:13', NULL),
(74, 87, 8, 55.00, 'PLN', 'przelewy24', 'pending', '2026-09-24 11:58:20', NULL),
(75, 88, 6, 60.00, 'PLN', 'przelewy24', 'pending', '2026-09-24 12:11:34', NULL);

-- --------------------------------------------------------

--
-- Struktura tabeli dla tabeli `payment_transactions`
--

CREATE TABLE `payment_transactions` (
  `id` int(11) NOT NULL,
  `payment_id` int(11) NOT NULL,
  `provider_transaction_id` varchar(255) DEFAULT NULL,
  `session_id` varchar(255) DEFAULT NULL,
  `status` varchar(30) NOT NULL DEFAULT 'pending',
  `amount` decimal(10,2) NOT NULL,
  `created_at` datetime NOT NULL DEFAULT current_timestamp(),
  `updated_at` datetime NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `payment_transactions`
--

INSERT INTO `payment_transactions` (`id`, `payment_id`, `provider_transaction_id`, `session_id`, `status`, `amount`, `created_at`, `updated_at`) VALUES
(35, 35, NULL, 'p24_1789980738_148', 'pending', 60.00, '2026-09-21 10:52:18', '2026-09-21 10:52:18'),
(36, 36, NULL, 'p24_1789980774_102', 'pending', 60.00, '2026-09-21 10:52:54', '2026-09-21 10:52:54'),
(37, 37, NULL, 'p24_1789980867_604', 'pending', 60.00, '2026-09-21 10:54:27', '2026-09-21 10:54:27'),
(38, 38, NULL, 'p24_1789980878_491', 'pending', 60.00, '2026-09-21 10:54:38', '2026-09-21 10:54:38'),
(39, 39, NULL, 'p24_1789981192_309', 'pending', 60.00, '2026-09-21 10:59:52', '2026-09-21 10:59:52'),
(40, 40, NULL, 'p24_1789981505_725', 'pending', 60.00, '2026-09-21 11:05:05', '2026-09-21 11:05:05'),
(41, 41, NULL, 'p24_1789981524_525', 'pending', 60.00, '2026-09-21 11:05:24', '2026-09-21 11:05:24'),
(42, 42, NULL, 'p24_1789981587_818', 'pending', 60.00, '2026-09-21 11:06:27', '2026-09-21 11:06:27'),
(43, 43, NULL, 'p24_1789981708_427', 'pending', 60.00, '2026-09-21 11:08:28', '2026-09-21 11:08:28'),
(44, 44, NULL, 'p24_1789981911_669', 'pending', 60.00, '2026-09-21 11:11:51', '2026-09-21 11:11:51'),
(45, 45, NULL, 'p24_1790058866_491', 'pending', 780.00, '2026-09-22 08:34:26', '2026-09-22 08:34:26'),
(46, 46, NULL, 'p24_1790059293_902', 'pending', 120.00, '2026-09-22 08:41:33', '2026-09-22 08:41:33'),
(47, 47, NULL, 'p24_1790237870_138', 'pending', 60.00, '2026-09-24 10:17:50', '2026-09-24 10:17:50'),
(48, 48, NULL, 'p24_1790238107_875', 'pending', 60.00, '2026-09-24 10:21:47', '2026-09-24 10:21:47'),
(49, 49, NULL, 'p24_1790238127_682', 'pending', 60.00, '2026-09-24 10:22:07', '2026-09-24 10:22:07'),
(50, 50, NULL, 'p24_1790238556_441', 'pending', 60.00, '2026-09-24 10:29:16', '2026-09-24 10:29:16'),
(51, 51, NULL, 'p24_1790238578_911', 'pending', 60.00, '2026-09-24 10:29:38', '2026-09-24 10:29:38'),
(52, 52, NULL, 'p24_1790238830_352', 'pending', 240.00, '2026-09-24 10:33:50', '2026-09-24 10:33:50'),
(53, 53, NULL, 'p24_1790239032_427', 'pending', 60.00, '2026-09-24 10:37:12', '2026-09-24 10:37:12'),
(54, 54, NULL, 'p24_1790239561_799', 'pending', 360.00, '2026-09-24 10:46:01', '2026-09-24 10:46:01'),
(55, 55, NULL, 'p24_1790239616_543', 'pending', 60.00, '2026-09-24 10:46:56', '2026-09-24 10:46:56'),
(56, 56, NULL, 'p24_1790239734_469', 'pending', 60.00, '2026-09-24 10:48:54', '2026-09-24 10:48:54'),
(57, 57, NULL, 'p24_1790240266_273', 'pending', 55.00, '2026-09-24 10:57:46', '2026-09-24 10:57:46'),
(58, 58, NULL, 'p24_1790240513_981', 'pending', 60.00, '2026-09-24 11:01:53', '2026-09-24 11:01:53'),
(59, 59, NULL, 'p24_1790240714_349', 'pending', 60.00, '2026-09-24 11:05:14', '2026-09-24 11:05:14'),
(60, 60, NULL, 'p24_1790240775_603', 'pending', 60.00, '2026-09-24 11:06:15', '2026-09-24 11:06:15'),
(61, 61, NULL, 'p24_1790240828_542', 'pending', 60.00, '2026-09-24 11:07:08', '2026-09-24 11:07:08'),
(62, 62, NULL, 'p24_1790240900_241', 'pending', 60.00, '2026-09-24 11:08:20', '2026-09-24 11:08:20'),
(63, 63, NULL, 'p24_1790241004_936', 'pending', 60.00, '2026-09-24 11:10:04', '2026-09-24 11:10:04'),
(64, 64, NULL, 'p24_1790241179_634', 'pending', 60.00, '2026-09-24 11:12:59', '2026-09-24 11:12:59'),
(65, 65, NULL, 'p24_1790241355_453', 'pending', 60.00, '2026-09-24 11:15:55', '2026-09-24 11:15:55'),
(66, 66, NULL, 'p24_1790241374_613', 'pending', 80.00, '2026-09-24 11:16:14', '2026-09-24 11:16:14'),
(67, 67, NULL, 'p24_1790241414_107', 'pending', 275.00, '2026-09-24 11:16:54', '2026-09-24 11:16:54'),
(68, 68, NULL, 'p24_1790241460_765', 'pending', 275.00, '2026-09-24 11:17:40', '2026-09-24 11:17:40'),
(69, 69, NULL, 'p24_1790241498_105', 'pending', 80.00, '2026-09-24 11:18:18', '2026-09-24 11:18:18'),
(70, 70, NULL, 'p24_1790242120_642', 'pending', 80.00, '2026-09-24 11:28:40', '2026-09-24 11:28:40'),
(71, 71, NULL, 'p24_1790242253_324', 'pending', 80.00, '2026-09-24 11:30:53', '2026-09-24 11:30:53'),
(72, 72, NULL, 'p24_1790242293_936', 'pending', 80.00, '2026-09-24 11:31:33', '2026-09-24 11:31:33'),
(73, 73, NULL, 'p24_1790243293_427', 'pending', 60.00, '2026-09-24 11:48:13', '2026-09-24 11:48:13'),
(74, 74, NULL, 'p24_1790243900_496', 'pending', 55.00, '2026-09-24 11:58:20', '2026-09-24 11:58:20'),
(75, 75, NULL, 'p24_1790244694_549', 'pending', 60.00, '2026-09-24 12:11:34', '2026-09-24 12:11:34');

-- --------------------------------------------------------

--
-- Struktura tabeli dla tabeli `pending_users`
--

CREATE TABLE `pending_users` (
  `ID` int(11) NOT NULL,
  `email` varchar(50) NOT NULL,
  `password_hash` varchar(255) NOT NULL,
  `phone_number` varchar(12) NOT NULL,
  `first_name` varchar(30) NOT NULL,
  `second_name` varchar(30) DEFAULT NULL,
  `surname` varchar(30) NOT NULL,
  `verification_token` varchar(32) NOT NULL,
  `verification_expiration_date` date NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Struktura tabeli dla tabeli `receipts`
--

CREATE TABLE `receipts` (
  `id` int(11) NOT NULL,
  `reservation_id` int(11) NOT NULL,
  `payment_id` int(11) NOT NULL,
  `document_type` varchar(30) NOT NULL,
  `document_number` varchar(100) DEFAULT NULL,
  `amount` decimal(10,2) NOT NULL,
  `issued_at` datetime NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Struktura tabeli dla tabeli `reservations`
--

CREATE TABLE `reservations` (
  `ID` int(11) NOT NULL,
  `court_ID` int(11) NOT NULL,
  `client_ID` int(11) NOT NULL,
  `codeID` varchar(64) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `reservations`
--

INSERT INTO `reservations` (`ID`, `court_ID`, `client_ID`, `codeID`) VALUES
(48, 1, 6, 'SET-60D75A73'),
(49, 1, 8, 'SET-55C64CB6'),
(50, 1, 6, 'SET-5B7FC46F'),
(51, 1, 6, 'SET-6180BD39'),
(52, 1, 6, 'SET-456F5DCE'),
(53, 1, 8, 'SET-597E459C'),
(54, 1, 8, 'SET-CD6DB05F'),
(55, 1, 8, 'SET-E5AB021E'),
(56, 1, 8, 'SET-0719E95C'),
(57, 1, 8, 'SET-A8931B7E'),
(58, 1, 8, 'SET-A0E8379E'),
(59, 1, 8, 'SET-9B7BCDD1'),
(60, 1, 8, 'SET-BB351CF9'),
(61, 1, 8, 'SET-D0AEB0CA'),
(62, 1, 8, 'SET-29DB0215'),
(63, 1, 8, 'SET-D967B181'),
(64, 1, 8, 'SET-4255FF57'),
(65, 1, 6, 'SET-F38AFFE6'),
(66, 1, 8, 'SET-6132F640'),
(67, 1, 6, 'SET-E1550340'),
(68, 1, 8, 'SET-B2BDEF17'),
(69, 1, 8, 'SET-1D5E7BAD'),
(70, 3, 6, 'SET-9A288280'),
(71, 2, 8, 'SET-8AAB4CB4'),
(72, 2, 8, 'SET-B82566A5'),
(73, 2, 8, 'SET-EC8BFE8F'),
(74, 2, 8, 'SET-38A62E1F'),
(75, 2, 8, 'SET-83E89F59'),
(76, 1, 8, 'SET-B42AAEFA'),
(77, 1, 8, 'SET-3E898DDB'),
(78, 1, 8, 'SET-3F374A34'),
(79, 4, 8, 'SET-2D988794'),
(80, 3, 8, 'SET-ABC15624'),
(81, 3, 8, 'SET-4420410E'),
(82, 4, 8, 'SET-0EB84F38'),
(83, 4, 8, 'SET-0AA6E0C6'),
(84, 4, 8, 'SET-5B170E1F'),
(85, 4, 8, 'SET-AE751A45'),
(86, 1, 8, 'SET-DE7CD803'),
(87, 3, 8, 'SET-506FF36C'),
(88, 1, 6, 'SET-BA59D953');

-- --------------------------------------------------------

--
-- Struktura tabeli dla tabeli `reservations_history`
--

CREATE TABLE `reservations_history` (
  `ID` int(11) NOT NULL,
  `court_ID` int(11) NOT NULL,
  `client_ID` int(11) NOT NULL,
  `begin_date` datetime NOT NULL,
  `end_date` datetime NOT NULL,
  `code` varchar(64) NOT NULL,
  `price` float NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Struktura tabeli dla tabeli `reservation_items`
--

CREATE TABLE `reservation_items` (
  `id` int(11) NOT NULL,
  `reservation_id` int(11) NOT NULL,
  `court_id` int(11) NOT NULL,
  `reservation_date` date NOT NULL,
  `start_time` tinyint(2) NOT NULL,
  `end_time` tinyint(2) NOT NULL,
  `price` decimal(10,2) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `reservation_items`
--

INSERT INTO `reservation_items` (`id`, `reservation_id`, `court_id`, `reservation_date`, `start_time`, `end_time`, `price`) VALUES
(35, 48, 1, '2026-09-21', 12, 13, 60.00),
(36, 49, 1, '2026-09-21', 11, 12, 60.00),
(37, 50, 1, '2026-09-21', 13, 14, 60.00),
(38, 51, 1, '2026-09-21', 14, 15, 60.00),
(39, 52, 1, '2026-09-21', 15, 16, 60.00),
(40, 53, 1, '2026-09-21', 16, 17, 60.00),
(41, 54, 1, '2026-09-21', 17, 18, 60.00),
(42, 55, 1, '2026-09-21', 18, 19, 60.00),
(43, 56, 1, '2026-09-21', 19, 20, 60.00),
(44, 57, 1, '2026-09-21', 20, 21, 60.00),
(45, 58, 1, '2026-09-22', 9, 22, 780.00),
(46, 59, 1, '2026-09-23', 12, 14, 120.00),
(47, 60, 1, '2026-09-24', 11, 12, 60.00),
(48, 61, 1, '2026-09-24', 12, 13, 60.00),
(49, 62, 1, '2026-09-24', 13, 14, 60.00),
(50, 63, 1, '2026-09-24', 14, 15, 60.00),
(51, 64, 1, '2026-09-24', 15, 16, 60.00),
(52, 65, 1, '2026-09-24', 17, 21, 240.00),
(53, 66, 1, '2026-09-24', 16, 17, 60.00),
(54, 67, 1, '2026-09-25', 11, 17, 360.00),
(55, 68, 1, '2026-09-24', 21, 22, 60.00),
(56, 69, 1, '2026-09-25', 7, 8, 60.00),
(57, 70, 3, '2026-09-27', 7, 8, 55.00),
(58, 71, 2, '2026-09-24', 12, 13, 60.00),
(59, 72, 2, '2026-09-24', 13, 14, 60.00),
(60, 73, 2, '2026-09-24', 14, 15, 60.00),
(61, 74, 2, '2026-09-24', 15, 16, 60.00),
(62, 75, 2, '2026-09-24', 16, 17, 60.00),
(63, 76, 1, '2026-09-25', 8, 9, 60.00),
(64, 77, 1, '2026-09-25', 9, 10, 60.00),
(65, 78, 1, '2026-09-25', 10, 11, 60.00),
(66, 79, 4, '2026-09-24', 12, 13, 80.00),
(67, 80, 3, '2026-09-24', 12, 17, 275.00),
(68, 81, 3, '2026-09-24', 17, 22, 275.00),
(69, 82, 4, '2026-09-24', 13, 14, 80.00),
(70, 83, 4, '2026-09-24', 14, 15, 80.00),
(71, 84, 4, '2026-09-24', 15, 16, 80.00),
(72, 85, 4, '2026-09-24', 16, 17, 80.00),
(73, 86, 1, '2026-09-25', 17, 18, 60.00),
(74, 87, 3, '2026-09-26', 7, 8, 55.00),
(75, 88, 1, '2026-09-25', 18, 19, 60.00);

-- --------------------------------------------------------

--
-- Struktura tabeli dla tabeli `surfaces`
--

CREATE TABLE `surfaces` (
  `id` int(11) NOT NULL,
  `name` varchar(50) NOT NULL COMMENT 'Nazwa nawierzchni, np. Mączka, Sztuczna trawa, Hard',
  `description` text DEFAULT NULL COMMENT 'Opcjonalny opis nawierzchni'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `surfaces`
--

INSERT INTO `surfaces` (`id`, `name`, `description`) VALUES
(1, 'Mączka ceglana', 'Klasyczna nawierzchnia tenisowa'),
(2, 'Sztuczna trawa', 'Nawierzchnia z piaskiem kwarcowym'),
(3, 'Nawierzchnia twarda (Hard)', 'Akrylowa, szybka nawierzchnia');

-- --------------------------------------------------------

--
-- Struktura tabeli dla tabeli `two_factor_codes`
--

CREATE TABLE `two_factor_codes` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `code` varchar(6) NOT NULL,
  `action` varchar(50) NOT NULL DEFAULT 'login',
  `expires_at` datetime NOT NULL,
  `used` tinyint(1) NOT NULL DEFAULT 0,
  `created_at` datetime NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `two_factor_codes`
--

INSERT INTO `two_factor_codes` (`id`, `user_id`, `code`, `action`, `expires_at`, `used`, `created_at`) VALUES
(34, 8, '413651', 'disable', '2026-09-21 10:08:14', 1, '2026-09-21 09:58:14'),
(64, 6, '173529', 'enable', '2026-09-24 12:20:08', 1, '2026-09-24 12:10:08');

-- --------------------------------------------------------

--
-- Struktura tabeli dla tabeli `users`
--

CREATE TABLE `users` (
  `ID` int(11) NOT NULL,
  `email` varchar(50) NOT NULL,
  `password_hash` varchar(255) NOT NULL,
  `registration_date` date NOT NULL DEFAULT current_timestamp(),
  `phone_number` varchar(12) NOT NULL,
  `first_name` varchar(30) NOT NULL,
  `second_name` varchar(30) DEFAULT NULL,
  `surname` varchar(30) NOT NULL,
  `is_admin` tinyint(1) NOT NULL DEFAULT 0,
  `twoFactorEnabled` tinyint(1) NOT NULL DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`ID`, `email`, `password_hash`, `registration_date`, `phone_number`, `first_name`, `second_name`, `surname`, `is_admin`, `twoFactorEnabled`) VALUES
(6, 'oskarjablonski061@gmail.com', '$2y$10$vvZjJV5iQ5HbK5coZ/QQR.vabdi2x3tLCuzOV66ZfOMhLv/WTV0GC', '2026-09-17', '', 'oskarjablonski061@gmail.com', '', 'Brak', 0, 1),
(8, 'czubaksebastian21@gmail.com', '$2y$10$a90VFLf2J9kmawO5mBjvP.J4E23M28Y0IqsroT3K0NsCTxF1R6M9O', '2026-09-17', '+48782228148', 'Sebastian', NULL, 'Czubak', 0, 0);

-- --------------------------------------------------------

--
-- Struktura tabeli dla tabeli `users_sessions`
--

CREATE TABLE `users_sessions` (
  `user_id` int(11) NOT NULL,
  `session` varchar(64) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `users_sessions`
--

INSERT INTO `users_sessions` (`user_id`, `session`) VALUES
(6, 'pvh0t5cvk8gvs5de5ur3j7d9hk'),
(8, 'uriqhbe71rikfqc3e3podcttv1'),
(8, 'c6pa7hg8hjke0t75r9bi5m5asv');

--
-- Indeksy dla zrzutów tabel
--

--
-- Indeksy dla tabeli `courts`
--
ALTER TABLE `courts`
  ADD PRIMARY KEY (`ID`),
  ADD KEY `fk_courts_surface` (`surface_id`);

--
-- Indeksy dla tabeli `payments`
--
ALTER TABLE `payments`
  ADD PRIMARY KEY (`id`),
  ADD KEY `reservation_id` (`reservation_id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indeksy dla tabeli `payment_transactions`
--
ALTER TABLE `payment_transactions`
  ADD PRIMARY KEY (`id`),
  ADD KEY `payment_id` (`payment_id`);

--
-- Indeksy dla tabeli `pending_users`
--
ALTER TABLE `pending_users`
  ADD PRIMARY KEY (`ID`);

--
-- Indeksy dla tabeli `receipts`
--
ALTER TABLE `receipts`
  ADD PRIMARY KEY (`id`),
  ADD KEY `reservation_id` (`reservation_id`),
  ADD KEY `payment_id` (`payment_id`);

--
-- Indeksy dla tabeli `reservations`
--
ALTER TABLE `reservations`
  ADD PRIMARY KEY (`ID`),
  ADD KEY `fk_reservations_user` (`client_ID`),
  ADD KEY `fk_reservations_court` (`court_ID`);

--
-- Indeksy dla tabeli `reservations_history`
--
ALTER TABLE `reservations_history`
  ADD PRIMARY KEY (`ID`);

--
-- Indeksy dla tabeli `reservation_items`
--
ALTER TABLE `reservation_items`
  ADD PRIMARY KEY (`id`),
  ADD KEY `reservation_id` (`reservation_id`),
  ADD KEY `court_id` (`court_id`);

--
-- Indeksy dla tabeli `surfaces`
--
ALTER TABLE `surfaces`
  ADD PRIMARY KEY (`id`);

--
-- Indeksy dla tabeli `two_factor_codes`
--
ALTER TABLE `two_factor_codes`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`),
  ADD KEY `code` (`code`),
  ADD KEY `expires_at` (`expires_at`);

--
-- Indeksy dla tabeli `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`ID`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `courts`
--
ALTER TABLE `courts`
  MODIFY `ID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `payments`
--
ALTER TABLE `payments`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=76;

--
-- AUTO_INCREMENT for table `payment_transactions`
--
ALTER TABLE `payment_transactions`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=76;

--
-- AUTO_INCREMENT for table `pending_users`
--
ALTER TABLE `pending_users`
  MODIFY `ID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=18;

--
-- AUTO_INCREMENT for table `receipts`
--
ALTER TABLE `receipts`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `reservations`
--
ALTER TABLE `reservations`
  MODIFY `ID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=89;

--
-- AUTO_INCREMENT for table `reservations_history`
--
ALTER TABLE `reservations_history`
  MODIFY `ID` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `reservation_items`
--
ALTER TABLE `reservation_items`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=76;

--
-- AUTO_INCREMENT for table `surfaces`
--
ALTER TABLE `surfaces`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `two_factor_codes`
--
ALTER TABLE `two_factor_codes`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=65;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `ID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `courts`
--
ALTER TABLE `courts`
  ADD CONSTRAINT `fk_courts_surface` FOREIGN KEY (`surface_id`) REFERENCES `surfaces` (`id`) ON UPDATE CASCADE;

--
-- Constraints for table `payments`
--
ALTER TABLE `payments`
  ADD CONSTRAINT `payments_ibfk_1` FOREIGN KEY (`reservation_id`) REFERENCES `reservations` (`ID`),
  ADD CONSTRAINT `payments_ibfk_2` FOREIGN KEY (`user_id`) REFERENCES `users` (`ID`);

--
-- Constraints for table `payment_transactions`
--
ALTER TABLE `payment_transactions`
  ADD CONSTRAINT `payment_transactions_ibfk_1` FOREIGN KEY (`payment_id`) REFERENCES `payments` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `receipts`
--
ALTER TABLE `receipts`
  ADD CONSTRAINT `receipts_ibfk_1` FOREIGN KEY (`reservation_id`) REFERENCES `reservations` (`ID`),
  ADD CONSTRAINT `receipts_ibfk_2` FOREIGN KEY (`payment_id`) REFERENCES `payments` (`id`);

--
-- Constraints for table `reservations`
--
ALTER TABLE `reservations`
  ADD CONSTRAINT `fk_reservations_court` FOREIGN KEY (`court_ID`) REFERENCES `courts` (`ID`),
  ADD CONSTRAINT `fk_reservations_user` FOREIGN KEY (`client_ID`) REFERENCES `users` (`ID`);

--
-- Constraints for table `reservation_items`
--
ALTER TABLE `reservation_items`
  ADD CONSTRAINT `reservation_items_ibfk_1` FOREIGN KEY (`reservation_id`) REFERENCES `reservations` (`ID`) ON DELETE CASCADE,
  ADD CONSTRAINT `reservation_items_ibfk_2` FOREIGN KEY (`court_id`) REFERENCES `courts` (`ID`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;

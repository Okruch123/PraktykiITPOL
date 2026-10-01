-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Paź 01, 2026 at 12:55 PM
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

-- --------------------------------------------------------

--
-- Struktura tabeli dla tabeli `users_sessions`
--

CREATE TABLE `users_sessions` (
  `user_id` int(11) NOT NULL,
  `session` varchar(64) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

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
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=90;

--
-- AUTO_INCREMENT for table `payment_transactions`
--
ALTER TABLE `payment_transactions`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=90;

--
-- AUTO_INCREMENT for table `pending_users`
--
ALTER TABLE `pending_users`
  MODIFY `ID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=20;

--
-- AUTO_INCREMENT for table `receipts`
--
ALTER TABLE `receipts`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `reservations`
--
ALTER TABLE `reservations`
  MODIFY `ID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=104;

--
-- AUTO_INCREMENT for table `reservations_history`
--
ALTER TABLE `reservations_history`
  MODIFY `ID` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `reservation_items`
--
ALTER TABLE `reservation_items`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=91;

--
-- AUTO_INCREMENT for table `surfaces`
--
ALTER TABLE `surfaces`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `two_factor_codes`
--
ALTER TABLE `two_factor_codes`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=135;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `ID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

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

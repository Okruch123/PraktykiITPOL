-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Wrz 23, 2026 at 10:49 AM
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
  `surface_type` int(11) NOT NULL,
  `is_outdoor` tinyint(1) NOT NULL,
  `address` varchar(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `courts`
--

INSERT INTO `courts` (`ID`, `surface_type`, `is_outdoor`, `address`) VALUES
(1, 1, 1, 'ul. Jakas Tam 34, Szczecin');

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
(46, 59, 8, 120.00, 'PLN', 'przelewy24', 'pending', '2026-09-22 08:41:33', NULL);

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
(46, 46, NULL, 'p24_1790059293_902', 'pending', 120.00, '2026-09-22 08:41:33', '2026-09-22 08:41:33');

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
-- Struktura tabeli dla tabeli `prices`
--

CREATE TABLE `prices` (
  `court_ID` int(11) NOT NULL,
  `tax_vat` int(11) NOT NULL,
  `price` decimal(10,2) NOT NULL
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
(59, 1, 8, 'SET-9B7BCDD1');

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
(46, 59, 1, '2026-09-23', 12, 14, 120.00);

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
(35, 6, '379450', 'enable', '2026-09-22 08:48:35', 1, '2026-09-22 08:38:35');

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
(6, 'oskarjablonski061@gmail.com', '$2y$10$vvZjJV5iQ5HbK5coZ/QQR.vabdi2x3tLCuzOV66ZfOMhLv/WTV0GC', '0000-00-00', '', 'oskarjablonski061@gmail.com', '', 'Brak', 0, 1),
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
(6, 'lp7vcag45oo3tfapsjuhcmah8e'),
(6, 'ktduc33gmrfi5evim558gtur9n'),
(6, '7qvs9nn7j674pjtlsehq30log3'),
(6, 'tr5o6npd17ejfflgevhqfh09o7'),
(6, 'mjs092io9natkdqtu87ff0kr0q'),
(6, '4vopj71lu78hjmopmoleh3c7r8'),
(6, 'k0oq4t4l276a66s8oht5r8qoq6'),
(6, '52s7n6l2bid2mgsi0t49ir1tpm'),
(6, '1qtr0fkbqjj078cd5o7v4r9ch5'),
(6, 'ur3s3ntj3tluvg57gt4vpq2fav'),
(6, 'nd6tgag7d7phka7bbkm2lj9ghm'),
(6, 'povhgq3nhvkt4bpc8i10fpsc5q'),
(6, 'q00qn8t2bl1dbelbnme5hn7nc5'),
(6, '5fn4ne6p4o6ha8rneeql6i99sc'),
(6, 'lt2ruihk9ons5pm5eu35chvuip'),
(6, 'o8sdhgt1i6cpe9iai5ktd4bq0t'),
(6, 'ma64u9a9u93946godobgb541dr'),
(6, '62h9g1aa8jkcidisng9hjkl3dp'),
(7, '0m6rsuvsa9o69mlbau38khgqut'),
(7, 'h6bo5kkoh64ndafo2fmsi41qrr'),
(7, 'h848apid2fp2r372be5be5nh7p'),
(7, 'edtni6qgmoqurh209u2qqjrs45'),
(6, 'p84h4fbcie5ka2p1m3fjsutdm2'),
(6, 'jop4k89u6bcnkm1sa0ljs3dsb8'),
(6, '3f5enpeqn4mp0sjoas6g93h8ol'),
(6, '8buiuh2b3r9gsj8uds21jp1nso'),
(6, '35u50a3mqlmu359p8he3jqh438'),
(6, '0inggk1a2cef3591tnbfla7t7r'),
(6, 'cul2gdchnia9acgj1tnj9lvofo'),
(8, '4ub04qr1u2ilp76a5jbt0ftqk0'),
(8, 'd4lujht6prpludu08f4jk2bg1l'),
(6, 'dstsvspluuobeai3l6i7mdd93f'),
(6, 'j0rud1rfj974ntptf96b5enp33'),
(6, 'hd9gmodm4ear1c3k31kqp5hg3t'),
(8, '9970djdb8actj97itfmfnttla7'),
(6, '06mvn56b5qqtmnu4kdf3m3f574'),
(6, 'l7l0bk5vvc87qk22eui4c97nm3'),
(6, 'ov7uh4s46l7pt1r4eel0gvn9be'),
(6, 'mtouj6lenbm739ce05hcgdn8e8'),
(6, 'anh38trclo2t14u526ce2dq5gb'),
(6, 'g6cp62870677shq5aegvgl213d'),
(8, 'mt4q31ustdrdbuud71mu41a9vp'),
(6, '2pcbs7cpbo4hbj7tufkag2upvf'),
(6, 'ae5cnlbbtdlkdjqdjhvmsdhaka'),
(6, 'rkc2q9q3phlci72chl06bcrcas'),
(6, 'ja6ag1q5g0ha4680brs2gk96ra'),
(6, 'pd8l2i6kosukq061sn8bjai0u9'),
(6, 'od4dv48d676ls381mdqvt0oifd'),
(6, 'b6e78eskaj4efdmaovd964rq5n'),
(8, 't98s5agu2mhvp22tsk09kgmnrn'),
(8, '4of2h401kvu292245vn3oqii34'),
(6, 't860hn4bljjlb4if88qiv9gsk5'),
(6, 'ckolb7jif1h7pcdpaljbuhh4ma'),
(8, '880bqoeidknb7j0re9548pbv5b'),
(6, 'kktmqh3u2jnrivm20qqjib34bu'),
(6, 'bnh8qs938q7b7h0unhkqvjv6d4'),
(6, 'fbk6po2g8fdlvim586ihuvti8p'),
(6, 'qjislc0d5169d0n4m5u6e7i0tq'),
(6, 't3fpbb13son6fnpnibqi61sr6n'),
(6, 'kn2egigk6a1rtassr5f12cq011'),
(6, 'euga03tct8afnqakmiebean23c'),
(8, '4cbva0136reiipac1ltnlovi14'),
(6, 'elm01gb0g6vkhm8eeq50nm4h7t'),
(6, 'qfhmvd595ejli7f82tft6vg5ud'),
(8, '76tuq03ts40spi7crrldt97f2q'),
(6, 'i0upokguueeafjkg32r845v1bk'),
(6, '5sv7brpgihmfg31fsa8jmgig44'),
(8, 'smhv75dhatf0u9g9h8iir9jg63'),
(6, '7avgkk17shl3mvt19r1edghd3g'),
(6, 'rh9n61ed6nto91140cg18ed6lh'),
(8, '3t229qu269jgkq4506bn93s519'),
(8, '1gqa0q2j0qkpn5listl4o6ltlq'),
(8, 'n5gmo17qjafg53r9cbfm3rf89i'),
(8, '00fa0msqp32j942n8ue8g7rcol'),
(8, 'bre9upep6otkh1t5mtkoc70i30'),
(8, 'fjn4oacike7310bfsn25vsrvlp'),
(8, 'nefrrcmsc08eipl7o7hbu3motu'),
(8, 'dakhgs3dn36pb9srh0ik7cq8ih'),
(8, '7nfs9rqati3r8rgdb6uddp0bf4'),
(8, 'kb9c4tu7b1ho13a6ubeci75g7q'),
(6, 'ohjtm33s4f3gnq3tefd0e0b5jl'),
(8, '6k4t8tiiqcfitpm0hhnkk0l8er');

--
-- Indeksy dla zrzutów tabel
--

--
-- Indeksy dla tabeli `courts`
--
ALTER TABLE `courts`
  ADD PRIMARY KEY (`ID`);

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
  MODIFY `ID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `payments`
--
ALTER TABLE `payments`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=47;

--
-- AUTO_INCREMENT for table `payment_transactions`
--
ALTER TABLE `payment_transactions`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=47;

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
  MODIFY `ID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=60;

--
-- AUTO_INCREMENT for table `reservations_history`
--
ALTER TABLE `reservations_history`
  MODIFY `ID` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `reservation_items`
--
ALTER TABLE `reservation_items`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=47;

--
-- AUTO_INCREMENT for table `two_factor_codes`
--
ALTER TABLE `two_factor_codes`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=36;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `ID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- Constraints for dumped tables
--

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

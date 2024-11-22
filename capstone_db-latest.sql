-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Nov 22, 2024 at 02:08 PM
-- Server version: 10.4.32-MariaDB
-- PHP Version: 8.2.12

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `capstone_db`
--

-- --------------------------------------------------------

--
-- Table structure for table `accommodations`
--

CREATE TABLE `accommodations` (
  `id` int(11) NOT NULL,
  `type` enum('cage','room') NOT NULL,
  `number` int(11) NOT NULL,
  `is_available` tinyint(1) DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `accommodations`
--

INSERT INTO `accommodations` (`id`, `type`, `number`, `is_available`) VALUES
(1, 'cage', 1, 1),
(2, 'cage', 2, 1),
(3, 'cage', 3, 1),
(4, 'cage', 4, 1),
(5, 'cage', 5, 1),
(6, 'cage', 6, 1),
(7, 'room', 1, 0),
(8, 'room', 2, 1),
(9, 'room', 3, 1),
(10, 'room', 4, 1),
(11, 'room', 5, 1),
(12, 'room', 6, 1);

-- --------------------------------------------------------

--
-- Table structure for table `appointments`
--

CREATE TABLE `appointments` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `appointment_date` date NOT NULL,
  `appointment_time` time NOT NULL,
  `status` enum('pending','approved','rejected') NOT NULL DEFAULT 'pending',
  `rejection_reason` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `notes` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `appointments`
--

INSERT INTO `appointments` (`id`, `user_id`, `appointment_date`, `appointment_time`, `status`, `rejection_reason`, `created_at`, `notes`) VALUES
(213, 1, '2024-11-23', '10:00:00', 'rejected', 'not avail', '2024-11-22 09:28:33', '');

-- --------------------------------------------------------

--
-- Table structure for table `feedback`
--

CREATE TABLE `feedback` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `user_name` varchar(255) NOT NULL,
  `rating` int(11) NOT NULL,
  `comment` text NOT NULL,
  `created_at` datetime NOT NULL,
  `image_path` varchar(255) DEFAULT NULL,
  `services` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`services`))
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `feedback`
--

INSERT INTO `feedback` (`id`, `user_id`, `user_name`, `rating`, `comment`, `created_at`, `image_path`, `services`) VALUES
(9, 1, 'Miguel Araneta', 5, 'this shop is good', '2024-11-22 17:41:08', NULL, '[\"pet_grooming\",\"pet_hotel\"]');

-- --------------------------------------------------------

--
-- Table structure for table `grooming_services`
--

CREATE TABLE `grooming_services` (
  `id` int(11) NOT NULL,
  `category` varchar(100) NOT NULL,
  `service_name` varchar(100) NOT NULL,
  `small_price` decimal(10,2) DEFAULT NULL,
  `medium_price` decimal(10,2) DEFAULT NULL,
  `large_price` decimal(10,2) DEFAULT NULL,
  `fixed_price` decimal(10,2) DEFAULT NULL,
  `description` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `grooming_services`
--

INSERT INTO `grooming_services` (`id`, `category`, `service_name`, `small_price`, `medium_price`, `large_price`, `fixed_price`, `description`) VALUES
(1, 'Package', 'Full Grooming', 500.00, 600.00, 700.00, NULL, 'Complete grooming service including bath, haircut, and styling'),
(2, 'Package', 'Bath and Blowdry', 300.00, 400.00, 500.00, NULL, 'Basic cleaning and drying service'),
(3, 'A La Carte', 'Paw Trim', NULL, NULL, NULL, 100.00, 'Trimming of paw fur'),
(4, 'A La Carte', 'Nail Trim', NULL, NULL, NULL, 50.05, 'Nail trimming service'),
(5, 'A La Carte', 'Ear Cleaning', NULL, NULL, NULL, 100.00, 'Professional ear cleaning'),
(6, 'A La Carte', 'Toothbrush', 222.00, NULL, NULL, 100.00, 'Dental cleaning service'),
(8, 'Package', 'Summer Cut', 500.00, 600.00, 700.00, NULL, 'cut summer');

-- --------------------------------------------------------

--
-- Table structure for table `pet_boarding`
--

CREATE TABLE `pet_boarding` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `pet_name` varchar(100) NOT NULL,
  `pet_type` enum('dog','cat') NOT NULL,
  `accommodation_id` int(11) NOT NULL,
  `check_in` date NOT NULL,
  `check_out` date NOT NULL,
  `status` enum('pending','approved','rejected') NOT NULL DEFAULT 'pending',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `notes` text DEFAULT NULL,
  `rejection_reason` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `pet_boarding`
--

INSERT INTO `pet_boarding` (`id`, `user_id`, `pet_name`, `pet_type`, `accommodation_id`, `check_in`, `check_out`, `status`, `created_at`, `notes`, `rejection_reason`) VALUES
(21, 1, 'doggo', 'dog', 7, '2024-11-23', '2024-11-30', 'approved', '2024-11-22 09:38:15', '', NULL);

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` int(11) NOT NULL,
  `name` varchar(100) NOT NULL,
  `email` varchar(100) NOT NULL,
  `password` varchar(255) NOT NULL,
  `role` enum('user','admin') DEFAULT 'user',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `name`, `email`, `password`, `role`, `created_at`) VALUES
(1, 'Miguel Araneta', 'user@yahoo.com', '$2y$10$20OVXYOmT3hlmTtelmr3n.fHJHmCshsPUB3m6coyJHmtNVZg6cRP6', 'user', '2024-10-16 14:22:42'),
(2, 'Admin', 'admin@yahoo.com', '$2y$10$TfJfl3Qmh.npLFU1AL.CMOWRdw8hV7Rmd5NBL9KRaJSJ6qpTDFZWm', 'admin', '2024-10-16 14:28:17'),
(3, 'Christine Buhatin', 'user2@yahoo.com', '$2y$10$L2eZ6vPlIzdmkt3jYjcDeuo1SKTZar0.W4GWknVS6dace4Z1AO7va', 'user', '2024-11-02 09:01:16');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `accommodations`
--
ALTER TABLE `accommodations`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `unique_accommodation` (`type`,`number`);

--
-- Indexes for table `appointments`
--
ALTER TABLE `appointments`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `feedback`
--
ALTER TABLE `feedback`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `grooming_services`
--
ALTER TABLE `grooming_services`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `pet_boarding`
--
ALTER TABLE `pet_boarding`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`),
  ADD KEY `accommodation_id` (`accommodation_id`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `email` (`email`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `accommodations`
--
ALTER TABLE `accommodations`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=13;

--
-- AUTO_INCREMENT for table `appointments`
--
ALTER TABLE `appointments`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=214;

--
-- AUTO_INCREMENT for table `feedback`
--
ALTER TABLE `feedback`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=10;

--
-- AUTO_INCREMENT for table `grooming_services`
--
ALTER TABLE `grooming_services`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- AUTO_INCREMENT for table `pet_boarding`
--
ALTER TABLE `pet_boarding`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=22;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `feedback`
--
ALTER TABLE `feedback`
  ADD CONSTRAINT `feedback_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`);

--
-- Constraints for table `pet_boarding`
--
ALTER TABLE `pet_boarding`
  ADD CONSTRAINT `pet_boarding_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`),
  ADD CONSTRAINT `pet_boarding_ibfk_2` FOREIGN KEY (`accommodation_id`) REFERENCES `accommodations` (`id`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;

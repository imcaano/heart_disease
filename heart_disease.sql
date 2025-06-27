-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: May 08, 2025 at 01:24 AM
-- Server version: 10.4.28-MariaDB
-- PHP Version: 8.2.4

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `heart_disease`
--

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` int(11) NOT NULL,
  `username` varchar(80) NOT NULL,
  `email` varchar(120) NOT NULL,
  `password` varchar(255) NOT NULL,
  `wallet_address` varchar(42) NOT NULL,
  `role` enum('user','admin') NOT NULL DEFAULT 'user',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `total_predictions` int(11) NOT NULL DEFAULT 0,
  `prediction_accuracy` decimal(5,2) NOT NULL DEFAULT 0.00,
  `reputation_score` int(11) NOT NULL DEFAULT 0,
  `last_login` timestamp NULL DEFAULT NULL,
  `status` enum('active','inactive') NOT NULL DEFAULT 'active'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `username`, `email`, `password`, `wallet_address`, `role`, `created_at`, `total_predictions`, `prediction_accuracy`, `reputation_score`, `last_login`, `status`) VALUES
(10, 'hamza', 'imhamza1@yahoo.com', '$2y$10$TsDb2mnLnLLXh3Mvm0JFCuPAeFVech3atQS1UjHZMB6E.ZTcy6cVG', '0xb1d8fd8b0a3b94dea88b2223a1fdd9811c1d65d2', 'admin', '2025-04-22 22:51:54', 2, 0.00, 0, '2025-05-05 12:12:07', 'active'),
(11, 'abdi', 'iamcaano3@gmail.com', '$2y$10$WZ1Lc4nsiF/V.azXIhc7dOJx3y3Q3D5CIyA88E3itla5AJEekaunG', '0x7c6b3ce2ded208bc455039a5d427b38131c4f556', 'admin', '2025-05-07 22:58:06', 3, 0.00, 0, '2025-05-07 23:04:50', 'active');

--
-- Table structure for table `predictions`
--

CREATE TABLE `predictions` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `age` int(11) NOT NULL,
  `sex` tinyint(1) NOT NULL,
  `cp` int(11) NOT NULL,
  `trestbps` int(11) NOT NULL,
  `chol` int(11) NOT NULL,
  `fbs` tinyint(1) NOT NULL,
  `restecg` int(11) NOT NULL,
  `thalach` int(11) NOT NULL,
  `exang` tinyint(1) NOT NULL,
  `oldpeak` decimal(3,1) NOT NULL,
  `slope` int(11) NOT NULL,
  `ca` int(11) NOT NULL,
  `thal` int(11) NOT NULL,
  `prediction` tinyint(1) NOT NULL,
  `user_id` int(11) NOT NULL,
  `prediction_date` datetime NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `user_id` (`user_id`),
  CONSTRAINT `predictions_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Indexes for dumped tables
--

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `username` (`username`),
  ADD UNIQUE KEY `email` (`email`),
  ADD UNIQUE KEY `wallet_address` (`wallet_address`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=12;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;

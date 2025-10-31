-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Oct 31, 2025 at 12:18 PM
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
-- Database: `usermanagement`
--

-- --------------------------------------------------------

--
-- Table structure for table `products`
--

CREATE TABLE `products` (
  `id` int(11) NOT NULL,
  `nama_produk` varchar(100) NOT NULL,
  `sku` varchar(50) NOT NULL,
  `stok` int(11) NOT NULL DEFAULT 0,
  `harga` decimal(15,2) NOT NULL DEFAULT 0.00,
  `created_by` int(11) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `products`
--

INSERT INTO `products` (`id`, `nama_produk`, `sku`, `stok`, `harga`, `created_by`, `created_at`) VALUES
(3, 'teh poci', '001', 100, 5000.00, 8, '2025-10-31 08:16:34'),
(7, 'Teh gelas', '002', 50, 6000.00, 8, '2025-10-31 08:19:09'),
(8, 'teh pucuk', '003', 200, 3000.00, 8, '2025-10-31 08:21:45');

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` int(11) NOT NULL,
  `nama` varchar(100) NOT NULL,
  `email` varchar(100) NOT NULL,
  `password` varchar(255) NOT NULL,
  `activation_token` varchar(255) DEFAULT NULL,
  `is_active` tinyint(1) DEFAULT 0,
  `role` enum('admin','user') DEFAULT 'admin',
  `status` enum('PENDING','ACTIVE') DEFAULT 'PENDING',
  `reset_token` varchar(255) DEFAULT NULL,
  `reset_token_expired` datetime DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `nama`, `email`, `password`, `activation_token`, `is_active`, `role`, `status`, `reset_token`, `reset_token_expired`, `created_at`) VALUES
(8, 'Refi', 'refisulistiawati29@gmail.com', '$2y$10$OoEaXDWk1USK2Cv6/HBbCuinEJPRGK7OOTuVdHeIPEVYpfR8AO262', NULL, 0, '', 'ACTIVE', NULL, NULL, '2025-10-31 08:15:45'),
(9, 'refi teman lulu', 'refisulistiawati2929@gmail.com', '$2y$10$eegfJuubK1ycprIp/KLmCe7r0KOnItAPbyx9j10LM/g9OdJRprXLG', 'c3fd8e3bb3068030659b284d67944f3c', 0, '', 'PENDING', NULL, NULL, '2025-10-31 08:27:16'),
(10, 'refi teman lulu', 'refisulistiawati2928@gmail.com', '$2y$10$LGqkvdVIU01/NVcL36iQ..0CqEcXNCB/IBiy21hcHpevv6qfDCWwa', NULL, 0, '', 'ACTIVE', NULL, NULL, '2025-10-31 08:27:46');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `products`
--
ALTER TABLE `products`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `sku` (`sku`),
  ADD KEY `created_by` (`created_by`);

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
-- AUTO_INCREMENT for table `products`
--
ALTER TABLE `products`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `products`
--
ALTER TABLE `products`
  ADD CONSTRAINT `products_ibfk_1` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;

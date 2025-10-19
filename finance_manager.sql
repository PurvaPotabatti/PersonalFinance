-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1:3307
-- Generation Time: Apr 14, 2025 at 03:31 PM
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
-- Database: `finance_manager`
--

-- --------------------------------------------------------

--
-- Table structure for table `dynamic_expenses`
--

CREATE TABLE `dynamic_expenses` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `name` varchar(100) NOT NULL,
  `amount` decimal(10,2) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `dynamic_expenses`
--

INSERT INTO `dynamic_expenses` (`id`, `user_id`, `name`, `amount`, `created_at`) VALUES
(1, 58058, 'kkk', 90.00, '2025-04-07 02:05:18'),
(2, 58058, 'kkk', 90.00, '2025-04-07 02:07:13'),
(3, 58058, 'hhh', 45.00, '2025-04-07 02:07:41'),
(4, 58058, 'yyy', 90.00, '2025-04-07 02:10:03'),
(6, 58058, 'gh', 55.00, '2025-04-07 02:12:03'),
(7, 58060, 'jjj', 90.00, '2025-04-07 05:25:39'),
(8, 58061, 'shopping ', 3000.00, '2025-04-07 09:22:41'),
(9, 58061, 'shopping ', 3000.00, '2025-04-07 09:23:27'),
(10, 58062, 'party ', 20000.00, '2025-04-13 05:50:22'),
(11, 58063, 'cloths ', 4000.00, '2025-04-14 03:50:29'),
(12, 58063, 'laptop ', 6000.00, '2025-04-14 03:50:51');

-- --------------------------------------------------------

--
-- Table structure for table `expense_shares`
--

CREATE TABLE `expense_shares` (
  `id` int(11) NOT NULL,
  `expense_id` int(11) NOT NULL,
  `member_id` int(11) NOT NULL,
  `share_amount` decimal(10,2) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `expense_shares`
--

INSERT INTO `expense_shares` (`id`, `expense_id`, `member_id`, `share_amount`) VALUES
(1, 5, 6, 2000.00),
(2, 6, 7, 8000.00),
(3, 6, 8, 0.00),
(4, 6, 9, 0.00);

-- --------------------------------------------------------

--
-- Table structure for table `groups`
--

CREATE TABLE `groups` (
  `group_id` int(11) NOT NULL,
  `group_name` varchar(255) NOT NULL,
  `created_by` int(11) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `groups`
--

INSERT INTO `groups` (`group_id`, `group_name`, `created_by`, `created_at`) VALUES
(1, 'team7', 58058, '2025-04-09 06:36:48'),
(2, 'team7', 58058, '2025-04-09 06:42:17'),
(3, 'katta', 58062, '2025-04-13 05:53:02'),
(4, 'team 7', 58062, '2025-04-14 03:41:14'),
(5, 'fam', 58063, '2025-04-14 03:52:07');

-- --------------------------------------------------------

--
-- Table structure for table `group_expenses`
--

CREATE TABLE `group_expenses` (
  `expense_id` int(11) NOT NULL,
  `group_id` int(11) DEFAULT NULL,
  `title` varchar(255) NOT NULL,
  `amount` decimal(10,2) NOT NULL,
  `paid_by` int(11) DEFAULT NULL,
  `expense_date` date DEFAULT curdate()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `group_expenses`
--

INSERT INTO `group_expenses` (`expense_id`, `group_id`, `title`, `amount`, `paid_by`, `expense_date`) VALUES
(1, 3, 'party', 5000.00, 4, '2025-04-13'),
(2, 3, 'party', 200.00, 5, '2025-04-13'),
(3, 4, 'food', 2000.00, 6, '2025-04-14'),
(4, 4, 'food', 2000.00, 6, '2025-04-14'),
(5, 4, 'food', 2000.00, 6, '2025-04-14'),
(6, 5, 'party', 8000.00, 7, '2025-04-14');

-- --------------------------------------------------------

--
-- Table structure for table `group_members`
--

CREATE TABLE `group_members` (
  `member_id` int(11) NOT NULL,
  `group_id` int(11) DEFAULT NULL,
  `name` varchar(255) NOT NULL,
  `email` varchar(255) DEFAULT NULL,
  `is_user` tinyint(1) DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `group_members`
--

INSERT INTO `group_members` (`member_id`, `group_id`, `name`, `email`, `is_user`) VALUES
(1, 1, 'suha', NULL, 0),
(2, 1, 'ritika', NULL, 0),
(4, 3, 'shruti', NULL, 0),
(5, 3, 'kajal', NULL, 0),
(6, 4, 'suha', 'suha@gmail.com', 0),
(7, 5, 'ritika', 'ritika@gmail.com', 0),
(8, 5, 'shravani', 'shravani@gmail.com', 0),
(9, 5, 'sonali', 'sonali@gmail.com', 0);

-- --------------------------------------------------------

--
-- Table structure for table `income`
--

CREATE TABLE `income` (
  `income_id` int(11) NOT NULL,
  `user_id` int(11) DEFAULT NULL,
  `source` varchar(100) DEFAULT NULL,
  `amount` decimal(10,2) DEFAULT NULL,
  `month_year` varchar(7) DEFAULT NULL,
  `date_received` date DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `income`
--

INSERT INTO `income` (`income_id`, `user_id`, `source`, `amount`, `month_year`, `date_received`) VALUES
(3, 58061, 'salary ', 200000.00, '2025-04', '2025-04-01'),
(4, 58062, 'salary', 100000.00, '2025-04', '2025-04-01'),
(5, 58063, 'salary', 50000.00, '2025-04', '2025-04-14'),
(7, 58063, 'scholarship', 2000.00, '2025-04', '2025-04-14');

-- --------------------------------------------------------

--
-- Table structure for table `savings`
--

CREATE TABLE `savings` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `type` varchar(50) NOT NULL,
  `name` varchar(100) NOT NULL,
  `amount` decimal(10,2) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `savings`
--

INSERT INTO `savings` (`id`, `user_id`, `type`, `name`, `amount`) VALUES
(1, 58058, 'static ', '', 6788.00),
(2, 58058, 'static ', '', 6788.00),
(3, 58058, 'static ', 'tina', 45.00),
(4, 58060, 'dynamic ', 'dddd', 78.00),
(5, 58060, 'dynamic ', 'dddd', 78.00),
(6, 58060, 'dynamic ', 'dddd', 78.00),
(7, 58060, 'static ', 'tution', 300.00),
(11, 58061, 'dynamic ', 'from salary', 2000.00),
(12, 58062, 'static', 'LIC', 2000.00),
(13, 58063, 'static ', 'FD', 6000.00),
(14, 58063, 'dynamic', 'salary', 5000.00);

-- --------------------------------------------------------

--
-- Table structure for table `static_expenses`
--

CREATE TABLE `static_expenses` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `name` varchar(100) NOT NULL,
  `amount` decimal(10,2) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `static_expenses`
--

INSERT INTO `static_expenses` (`id`, `user_id`, `name`, `amount`, `created_at`) VALUES
(2, 58058, 'aaa', 78.00, '2025-04-07 02:07:25'),
(3, 58058, 'ttt', 78.00, '2025-04-07 02:07:53'),
(4, 58058, 'ttt', 78.00, '2025-04-07 02:09:34'),
(5, 58058, 'vvv', 23.00, '2025-04-07 02:09:43'),
(6, 58058, 'ttt', 78.00, '2025-04-07 02:12:15'),
(8, 58061, 'travelling ', 1000.00, '2025-04-07 09:21:47'),
(9, 58061, 'food ', 3000.00, '2025-04-07 09:22:02'),
(10, 58061, 'electricity', 500.00, '2025-04-07 09:22:20'),
(11, 58062, 'travelling ', 10000.00, '2025-04-13 05:49:38'),
(13, 58063, 'grocery', 4000.00, '2025-04-14 03:49:40'),
(14, 58063, 'travelling', 3000.00, '2025-04-14 03:49:55'),
(15, 58063, 'electricity', 1500.00, '2025-04-14 03:50:16');

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `user_id` int(10) NOT NULL,
  `name` varchar(70) NOT NULL,
  `email` varchar(30) NOT NULL,
  `password_hash` varchar(8) NOT NULL,
  `phone_number` bigint(10) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`user_id`, `name`, `email`, `password_hash`, `phone_number`) VALUES
(1, 'shraddha', 'shradhha@gmail.com', '$2y$10$t', 1234567890),
(9, 'sonali', 'sonali@gmail.com', '$2y$10$X', 2323232323),
(67, 'tina', 'tina@gmail.com', '$2y$10$T', 1234567890),
(5244, 'aa', 'aaa@gmail.com', '$2y$10$6', 1234567890),
(58057, 'ritika', 'ritika@gmail.com', '$2y$10$b', 9309352570),
(58058, 'shravani', 'shravani@gmail.com', '99999', 9999999999),
(58059, 'abc', 'abc@gmail.com', 'abc', 3453453453),
(58060, 'purva ', 'purva@gmail.com', '12345', 9309352577),
(58061, 'suha ', 'suha@gmail.com', 'suha', 2344322342),
(58062, 'ritika ', 'r@gmail.com', 'ritika', 8989898989),
(58063, 'newUser', 'new@gmail.com', 'new123', 2342342342);

--
-- Indexes for dumped tables
--

--
-- Indexes for table `dynamic_expenses`
--
ALTER TABLE `dynamic_expenses`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `expense_shares`
--
ALTER TABLE `expense_shares`
  ADD PRIMARY KEY (`id`),
  ADD KEY `expense_id` (`expense_id`),
  ADD KEY `member_id` (`member_id`);

--
-- Indexes for table `groups`
--
ALTER TABLE `groups`
  ADD PRIMARY KEY (`group_id`);

--
-- Indexes for table `group_expenses`
--
ALTER TABLE `group_expenses`
  ADD PRIMARY KEY (`expense_id`),
  ADD KEY `group_id` (`group_id`),
  ADD KEY `paid_by` (`paid_by`);

--
-- Indexes for table `group_members`
--
ALTER TABLE `group_members`
  ADD PRIMARY KEY (`member_id`),
  ADD KEY `group_id` (`group_id`);

--
-- Indexes for table `income`
--
ALTER TABLE `income`
  ADD PRIMARY KEY (`income_id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `savings`
--
ALTER TABLE `savings`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `static_expenses`
--
ALTER TABLE `static_expenses`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`user_id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `dynamic_expenses`
--
ALTER TABLE `dynamic_expenses`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=13;

--
-- AUTO_INCREMENT for table `expense_shares`
--
ALTER TABLE `expense_shares`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `groups`
--
ALTER TABLE `groups`
  MODIFY `group_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `group_expenses`
--
ALTER TABLE `group_expenses`
  MODIFY `expense_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT for table `group_members`
--
ALTER TABLE `group_members`
  MODIFY `member_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=10;

--
-- AUTO_INCREMENT for table `income`
--
ALTER TABLE `income`
  MODIFY `income_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- AUTO_INCREMENT for table `savings`
--
ALTER TABLE `savings`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=15;

--
-- AUTO_INCREMENT for table `static_expenses`
--
ALTER TABLE `static_expenses`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=16;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `user_id` int(10) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=58064;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `dynamic_expenses`
--
ALTER TABLE `dynamic_expenses`
  ADD CONSTRAINT `dynamic_expenses_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`);

--
-- Constraints for table `expense_shares`
--
ALTER TABLE `expense_shares`
  ADD CONSTRAINT `expense_shares_ibfk_1` FOREIGN KEY (`expense_id`) REFERENCES `group_expenses` (`expense_id`),
  ADD CONSTRAINT `expense_shares_ibfk_2` FOREIGN KEY (`member_id`) REFERENCES `group_members` (`member_id`);

--
-- Constraints for table `group_expenses`
--
ALTER TABLE `group_expenses`
  ADD CONSTRAINT `group_expenses_ibfk_1` FOREIGN KEY (`group_id`) REFERENCES `groups` (`group_id`) ON DELETE CASCADE,
  ADD CONSTRAINT `group_expenses_ibfk_2` FOREIGN KEY (`paid_by`) REFERENCES `group_members` (`member_id`);

--
-- Constraints for table `group_members`
--
ALTER TABLE `group_members`
  ADD CONSTRAINT `group_members_ibfk_1` FOREIGN KEY (`group_id`) REFERENCES `groups` (`group_id`) ON DELETE CASCADE;

--
-- Constraints for table `income`
--
ALTER TABLE `income`
  ADD CONSTRAINT `income_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`);

--
-- Constraints for table `savings`
--
ALTER TABLE `savings`
  ADD CONSTRAINT `savings_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`);

--
-- Constraints for table `static_expenses`
--
ALTER TABLE `static_expenses`
  ADD CONSTRAINT `static_expenses_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;

-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Jan 18, 2026 at 07:29 PM
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
-- Database: `expense_tracker`
--

-- --------------------------------------------------------

--
-- Table structure for table `expense_categories`
--

CREATE TABLE `expense_categories` (
  `id` int(11) NOT NULL,
  `name` varchar(50) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `expense_categories`
--

INSERT INTO `expense_categories` (`id`, `name`, `created_at`) VALUES
(1, 'Food & Dining', '2025-12-02 15:28:25'),
(2, 'Entertainment', '2025-12-02 15:28:25'),
(3, 'Shopping', '2025-12-02 15:28:25'),
(4, 'Investment', '2025-12-02 15:28:25'),
(5, 'Transport', '2025-12-02 15:28:25'),
(6, 'Utilities', '2025-12-02 15:28:25'),
(7, 'Other', '2025-12-02 15:28:25');

--
-- Triggers `expense_categories`
--
DELIMITER $$
CREATE TRIGGER `after_expense_category_update` AFTER UPDATE ON `expense_categories` FOR EACH ROW BEGIN
    UPDATE transactions 
    SET category = NEW.name
    WHERE category_id = NEW.id 
      AND type = 'expense';
END
$$
DELIMITER ;

-- --------------------------------------------------------

--
-- Table structure for table `income_categories`
--

CREATE TABLE `income_categories` (
  `id` int(11) NOT NULL,
  `name` varchar(50) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `income_categories`
--

INSERT INTO `income_categories` (`id`, `name`, `created_at`) VALUES
(1, 'Salary & Wages', '2025-12-02 15:28:25'),
(2, 'Freelance', '2025-12-02 15:28:25'),
(3, 'Business', '2025-12-02 15:28:25'),
(4, 'Investment', '2025-12-02 15:28:25'),
(5, 'Other', '2025-12-02 15:28:25'),
(7, 'Rental', '2025-12-05 17:45:29');

--
-- Triggers `income_categories`
--
DELIMITER $$
CREATE TRIGGER `after_income_category_update` AFTER UPDATE ON `income_categories` FOR EACH ROW BEGIN
    UPDATE transactions 
    SET category = NEW.name
    WHERE category_id = NEW.id 
      AND type = 'income';
END
$$
DELIMITER ;

-- --------------------------------------------------------

--
-- Table structure for table `transactions`
--

CREATE TABLE `transactions` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL DEFAULT 1,
  `date` date NOT NULL,
  `description` varchar(255) DEFAULT NULL,
  `category` varchar(50) NOT NULL,
  `category_id` int(11) DEFAULT NULL,
  `amount` decimal(10,2) NOT NULL,
  `type` enum('income','expense') NOT NULL,
  `status` enum('completed','pending') DEFAULT 'completed',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `transactions`
--

INSERT INTO `transactions` (`id`, `user_id`, `date`, `description`, `category`, `category_id`, `amount`, `type`, `status`, `created_at`) VALUES
(85, 1, '2025-01-05', 'Monthly Salary', 'Salary & Wages', 1, 75000.00, 'income', 'completed', '2025-12-01 18:20:56'),
(86, 1, '2025-01-06', 'New Year Grocery', 'Food & Dining', 1, 15000.00, 'expense', 'completed', '2025-12-01 18:20:56'),
(87, 1, '2025-01-10', 'Freelance Web Project', 'freelance', 2, 30000.00, 'income', 'completed', '2025-12-01 18:20:56'),
(88, 1, '2025-01-12', 'Movie Night', 'entertainment', 2, 5500.00, 'expense', 'completed', '2025-12-01 18:20:56'),
(89, 1, '2025-01-15', 'Winter Clothes', 'shopping', 3, 25000.00, 'expense', 'completed', '2025-12-01 18:20:56'),
(90, 1, '2025-01-18', 'Electricity Bill', 'utilities', 6, 9500.00, 'expense', 'completed', '2025-12-01 18:20:56'),
(91, 1, '2025-01-20', 'Car Fuel', 'transport', 5, 14000.00, 'expense', 'completed', '2025-12-01 18:20:56'),
(92, 1, '2025-01-25', 'Stock Investment', 'investment', 4, 25000.00, 'expense', 'completed', '2025-12-01 18:20:56'),
(93, 1, '2025-01-28', 'Consultation Work', 'business', 3, 20000.00, 'income', 'completed', '2025-12-01 18:20:56'),
(94, 1, '2025-02-05', 'Monthly Salary', 'Salary & Wages', 1, 75000.00, 'income', 'completed', '2025-12-01 18:20:56'),
(95, 1, '2025-02-07', 'Supermarket', 'Food & Dining', 1, 13500.00, 'expense', 'completed', '2025-12-01 18:20:56'),
(96, 1, '2025-02-10', 'Mobile App Development', 'freelance', 2, 35000.00, 'income', 'completed', '2025-12-01 18:20:56'),
(97, 1, '2025-02-14', 'Valentine Special Dinner', 'Food & Dining', 1, 12000.00, 'expense', 'completed', '2025-12-01 18:20:56'),
(98, 1, '2025-02-16', 'Phone Bill', 'utilities', 6, 4500.00, 'expense', 'completed', '2025-12-01 18:20:56'),
(99, 1, '2025-02-20', 'Electronics Shopping', 'shopping', 3, 45000.00, 'expense', 'completed', '2025-12-01 18:20:56'),
(100, 1, '2025-02-22', 'Concert Tickets', 'entertainment', 2, 15000.00, 'expense', 'completed', '2025-12-01 18:20:56'),
(101, 1, '2025-02-25', 'Monthly Transport', 'transport', 5, 8000.00, 'expense', 'completed', '2025-12-01 18:20:56'),
(102, 1, '2025-03-05', 'Monthly Salary', 'Salary & Wages', 1, 75000.00, 'income', 'completed', '2025-12-01 18:20:56'),
(103, 1, '2025-03-08', 'Grocery', 'Food & Dining', 1, 16500.00, 'expense', 'completed', '2025-12-01 18:20:56'),
(104, 1, '2025-03-12', 'Business Consultation', 'business', 3, 28000.00, 'income', 'completed', '2025-12-01 18:20:56'),
(105, 1, '2025-03-15', 'Restaurant Dinner', 'Food & Dining', 1, 9500.00, 'expense', 'completed', '2025-12-01 18:20:56'),
(106, 1, '2025-03-18', 'Water Bill', 'utilities', 6, 3500.00, 'expense', 'completed', '2025-12-01 18:20:56'),
(107, 1, '2025-03-20', 'Shoes & Accessories', 'shopping', 3, 18500.00, 'expense', 'completed', '2025-12-01 18:20:56'),
(108, 1, '2025-03-22', 'Movie Streaming Subscriptions', 'entertainment', 2, 6500.00, 'expense', 'completed', '2025-12-01 18:20:56'),
(109, 1, '2025-03-25', 'Taxi Fares', 'transport', 5, 9500.00, 'expense', 'completed', '2025-12-01 18:20:56'),
(110, 1, '2025-03-28', 'Mutual Fund Investment', 'investment', 4, 30000.00, 'expense', 'completed', '2025-12-01 18:20:56'),
(111, 1, '2025-04-05', 'Monthly Salary + Bonus', 'Salary & Wages', 1, 100000.00, 'income', 'completed', '2025-12-01 18:20:56'),
(112, 1, '2025-04-07', 'New Year Shopping', 'shopping', 3, 35000.00, 'expense', 'completed', '2025-12-01 18:20:56'),
(113, 1, '2025-04-10', 'Content Writing', 'freelance', 2, 18000.00, 'income', 'completed', '2025-12-01 18:20:56'),
(114, 1, '2025-04-13', 'Avurudu Food & Sweets', 'Food & Dining', 1, 25000.00, 'expense', 'completed', '2025-12-01 18:20:56'),
(115, 1, '2025-04-15', 'Electricity Bill', 'utilities', 6, 10500.00, 'expense', 'completed', '2025-12-01 18:20:56'),
(116, 1, '2025-04-18', 'Car Service', 'transport', 5, 22500.00, 'expense', 'completed', '2025-12-01 18:20:56'),
(117, 1, '2025-04-20', 'New Year Celebrations', 'entertainment', 2, 18000.00, 'expense', 'completed', '2025-12-01 18:20:56'),
(118, 1, '2025-04-25', 'Gold Investment', 'investment', 4, 40000.00, 'expense', 'completed', '2025-12-01 18:20:56'),
(119, 1, '2025-05-05', 'Monthly Salary', 'Salary & Wages', 1, 78000.00, 'income', 'completed', '2025-12-01 18:20:56'),
(120, 1, '2025-05-08', 'Grocery Shopping', 'Food & Dining', 1, 17500.00, 'expense', 'completed', '2025-12-01 18:20:56'),
(121, 1, '2025-05-12', 'Part-time Teaching', 'business', 3, 25000.00, 'income', 'completed', '2025-12-01 18:20:56'),
(122, 1, '2025-05-15', 'Medical Checkup', 'Food & Dining', 1, 12500.00, 'expense', 'completed', '2025-12-01 18:20:56'),
(123, 1, '2025-05-18', 'Internet Bill', 'utilities', 6, 5500.00, 'expense', 'completed', '2025-12-01 18:20:56'),
(124, 1, '2025-05-20', 'Laptop Purchase', 'shopping', 3, 85000.00, 'expense', 'completed', '2025-12-01 18:20:56'),
(125, 1, '2025-05-22', 'Cinema & Dinner', 'entertainment', 2, 8500.00, 'expense', 'completed', '2025-12-01 18:20:56'),
(126, 1, '2025-05-25', 'Fuel Expense', 'transport', 5, 15500.00, 'expense', 'completed', '2025-12-01 18:20:56'),
(127, 1, '2025-06-05', 'Monthly Salary', 'Salary & Wages', 1, 78000.00, 'income', 'completed', '2025-12-01 18:20:56'),
(128, 1, '2025-06-08', 'Supermarket', 'Food & Dining', 1, 15800.00, 'expense', 'completed', '2025-12-01 18:20:56'),
(129, 1, '2025-06-12', 'Freelance Design Work', 'freelance', 2, 32000.00, 'income', 'completed', '2025-12-01 18:20:56'),
(130, 1, '2025-06-15', 'Dinner with Family', 'Food & Dining', 1, 14500.00, 'expense', 'completed', '2025-12-01 18:20:56'),
(131, 1, '2025-06-18', 'Mobile Recharge', 'utilities', 6, 4500.00, 'expense', 'completed', '2025-12-01 18:20:56'),
(132, 1, '2025-06-20', 'Summer Clothes', 'shopping', 3, 28500.00, 'expense', 'completed', '2025-12-01 18:20:56'),
(133, 1, '2025-06-22', 'Beach Party', 'entertainment', 2, 18500.00, 'expense', 'completed', '2025-12-01 18:20:56'),
(134, 1, '2025-06-25', 'Bus/Train Monthly Pass', 'transport', 5, 9500.00, 'expense', 'completed', '2025-12-01 18:20:56'),
(135, 1, '2025-06-28', 'Stock Market Investment', 'investment', 4, 35000.00, 'expense', 'completed', '2025-12-01 18:20:56'),
(136, 1, '2025-07-05', 'Monthly Salary', 'Salary & Wages', 1, 80000.00, 'income', 'completed', '2025-12-01 18:20:56'),
(137, 1, '2025-07-08', 'Grocery', 'Food & Dining', 1, 16500.00, 'expense', 'completed', '2025-12-01 18:20:56'),
(138, 1, '2025-07-12', 'Business Consultation', 'business', 3, 35000.00, 'income', 'completed', '2025-12-01 18:20:56'),
(139, 1, '2025-07-15', 'Fine Dining', 'Food & Dining', 1, 18500.00, 'expense', 'completed', '2025-12-01 18:20:56'),
(140, 1, '2025-07-18', 'Electricity Bill', 'utilities', 6, 11500.00, 'expense', 'completed', '2025-12-01 18:20:56'),
(141, 1, '2025-07-20', 'Online Shopping', 'shopping', 3, 32500.00, 'expense', 'completed', '2025-12-01 18:20:56'),
(142, 1, '2025-07-22', 'Concert', 'entertainment', 2, 22500.00, 'expense', 'completed', '2025-12-01 18:20:56'),
(143, 1, '2025-07-25', 'Car Maintenance', 'transport', 5, 18500.00, 'expense', 'completed', '2025-12-01 18:20:56'),
(144, 1, '2025-08-05', 'Monthly Salary', 'Salary & Wages', 1, 80000.00, 'income', 'completed', '2025-12-01 18:20:56'),
(145, 1, '2025-08-08', 'Grocery Shopping', 'Food & Dining', 1, 17000.00, 'expense', 'completed', '2025-12-01 18:20:56'),
(146, 1, '2025-08-12', 'Freelance Project', 'freelance', 2, 28000.00, 'income', 'completed', '2025-12-01 18:20:56'),
(147, 1, '2025-08-15', 'Medical Bills', 'Food & Dining', 1, 15500.00, 'expense', 'completed', '2025-12-01 18:20:56'),
(148, 1, '2025-08-18', 'Water & Electricity', 'utilities', 6, 12500.00, 'expense', 'completed', '2025-12-01 18:20:56'),
(149, 1, '2025-08-20', 'Furniture Shopping', 'shopping', 3, 65000.00, 'expense', 'completed', '2025-12-01 18:20:56'),
(150, 1, '2025-08-22', 'Movie Night', 'entertainment', 2, 7500.00, 'expense', 'completed', '2025-12-01 18:20:56'),
(151, 1, '2025-08-25', 'Fuel Expense', 'transport', 5, 16500.00, 'expense', 'completed', '2025-12-01 18:20:56'),
(152, 1, '2025-09-05', 'Monthly Salary', 'Salary & Wages', 1, 82000.00, 'income', 'completed', '2025-12-01 18:20:56'),
(153, 1, '2025-09-08', 'Supermarket', 'Food & Dining', 1, 18500.00, 'expense', 'completed', '2025-12-01 18:20:56'),
(154, 1, '2025-09-12', 'Web Development', 'freelance', 2, 40000.00, 'income', 'completed', '2025-12-01 18:20:56'),
(155, 1, '2025-09-15', 'Restaurant', 'Food & Dining', 1, 12500.00, 'expense', 'completed', '2025-12-01 18:20:56'),
(156, 1, '2025-09-18', 'Phone Bill', 'utilities', 6, 5500.00, 'expense', 'completed', '2025-12-01 18:20:56'),
(157, 1, '2025-09-20', 'Clothing', 'shopping', 3, 22500.00, 'expense', 'completed', '2025-12-01 18:20:56'),
(158, 1, '2025-09-22', 'Theater Show', 'entertainment', 2, 12500.00, 'expense', 'completed', '2025-12-01 18:20:56'),
(159, 1, '2025-09-25', 'Public Transport', 'transport', 5, 8500.00, 'expense', 'completed', '2025-12-01 18:20:56'),
(160, 1, '2025-10-05', 'Monthly Salary', 'Salary & Wages', 1, 82000.00, 'income', 'completed', '2025-12-01 18:20:56'),
(161, 1, '2025-10-08', 'Grocery', 'Food & Dining', 1, 19500.00, 'expense', 'completed', '2025-12-01 18:20:56'),
(162, 1, '2025-10-12', 'Consultation Fee', 'business', 3, 30000.00, 'income', 'completed', '2025-12-01 18:20:56'),
(163, 1, '2025-10-15', 'Dinner Out', 'Food & Dining', 1, 14500.00, 'expense', 'completed', '2025-12-01 18:20:56'),
(164, 1, '2025-10-18', 'Internet Bill', 'utilities', 6, 6500.00, 'expense', 'completed', '2025-12-01 18:20:56'),
(165, 1, '2025-10-20', 'Electronics', 'shopping', 3, 45000.00, 'expense', 'completed', '2025-12-01 18:20:56'),
(166, 1, '2025-10-22', 'Entertainment', 'entertainment', 2, 9500.00, 'expense', 'completed', '2025-12-01 18:20:56'),
(167, 1, '2025-10-25', 'Car Expenses', 'transport', 5, 19500.00, 'expense', 'completed', '2025-12-01 18:20:56'),
(168, 1, '2025-11-05', 'Monthly Salary', 'Salary & Wages', 1, 85000.00, 'income', 'completed', '2025-12-01 18:20:56'),
(169, 1, '2025-11-08', 'Supermarket', 'Food & Dining', 1, 20500.00, 'expense', 'completed', '2025-12-01 18:20:56'),
(170, 1, '2025-11-12', 'Freelance Writing', 'freelance', 2, 25000.00, 'income', 'completed', '2025-12-01 18:20:56'),
(171, 1, '2025-11-15', 'Family Dinner', 'Food & Dining', 1, 18500.00, 'expense', 'completed', '2025-12-01 18:20:56'),
(172, 1, '2025-11-18', 'Utilities', 'utilities', 6, 9500.00, 'expense', 'completed', '2025-12-01 18:20:56'),
(173, 1, '2025-11-20', 'Pre-Christmas Shopping', 'shopping', 3, 35000.00, 'expense', 'completed', '2025-12-01 18:20:56'),
(174, 1, '2025-11-22', 'Movie Night', 'entertainment', 2, 8500.00, 'expense', 'completed', '2025-12-01 18:20:56'),
(175, 1, '2025-11-25', 'Transport', 'transport', 5, 12500.00, 'expense', 'completed', '2025-12-01 18:20:56'),
(186, 1, '2025-01-31', 'Freelance Payment', 'freelance', 2, 15000.00, 'income', 'pending', '2025-12-01 18:20:56'),
(187, 1, '2025-02-28', 'Online Course', 'investment', 4, 12000.00, 'expense', 'pending', '2025-12-01 18:20:56'),
(188, 1, '2025-03-31', 'Consultation Payment', 'business', 3, 18000.00, 'income', 'pending', '2025-12-01 18:20:56'),
(189, 1, '2025-12-03', '', 'Rental', 7, 10000.00, 'income', 'completed', '2025-12-03 15:16:38'),
(191, 1, '2025-12-03', 'test', 'Food & Dining', 1, 10000.00, 'expense', 'completed', '2025-12-03 16:55:25'),
(192, 1, '2025-12-03', '', 'freelance', 2, 10000.00, 'income', 'completed', '2025-12-03 17:58:21'),
(193, 1, '2025-12-03', '', 'Food & Dining', 1, 100000.00, 'expense', 'completed', '2025-12-03 17:58:51'),
(194, 1, '2025-12-05', 'test', 'other', NULL, 10.00, 'expense', 'completed', '2025-12-05 17:55:56'),
(196, 1, '2025-12-05', '', 'other', NULL, 1000.00, 'expense', 'completed', '2025-12-05 20:58:31'),
(197, 1, '2026-01-18', 'test', 'investment', NULL, 500.00, 'income', 'completed', '2026-01-18 18:01:16'),
(198, 1, '2026-01-18', 'last test', 'food', NULL, 100.00, 'expense', 'completed', '2026-01-18 18:01:36'),
(199, 1, '2026-01-18', 'test', 'salary', NULL, 100000.00, 'income', 'completed', '2026-01-18 18:13:08'),
(200, 1, '2026-01-18', 'test', 'entertainment', NULL, 999.00, 'expense', 'completed', '2026-01-18 18:13:40');

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` int(11) NOT NULL,
  `username` varchar(100) NOT NULL DEFAULT 'User',
  `age` int(11) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `username`, `age`, `created_at`) VALUES
(1, 'Dulan', 0, '2025-12-02 15:28:25');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `expense_categories`
--
ALTER TABLE `expense_categories`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `name` (`name`);

--
-- Indexes for table `income_categories`
--
ALTER TABLE `income_categories`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `name` (`name`);

--
-- Indexes for table `transactions`
--
ALTER TABLE `transactions`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `expense_categories`
--
ALTER TABLE `expense_categories`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=10;

--
-- AUTO_INCREMENT for table `income_categories`
--
ALTER TABLE `income_categories`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- AUTO_INCREMENT for table `transactions`
--
ALTER TABLE `transactions`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=201;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;

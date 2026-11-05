-- phpMyAdmin SQL Dump
-- version 5.2.0
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: 10 نوفمبر 2025 الساعة 13:26
-- إصدار الخادم: 10.4.24-MariaDB
-- PHP Version: 8.1.6

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `al_moheeb_db`
--

-- --------------------------------------------------------

--
-- بنية الجدول `admin_users`
--

CREATE TABLE `admin_users` (
  `id` int(11) NOT NULL,
  `username` varchar(50) NOT NULL,
  `password_hash` varchar(255) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- إرجاع أو استيراد بيانات الجدول `admin_users`
--

INSERT INTO `admin_users` (`id`, `username`, `password_hash`, `created_at`) VALUES
(1, 'admin', '$2y$10$VQX0ArERULu4CRrvQhEN.OopoYzes0Rd0WaKz/dnK4CdvA9cdVsSu', '2025-11-02 20:13:29'),
(2, 'asheem', '$2y$10$9TGD9xw2U7NKZy.yoWK/cu7p7BvFNTBb/saYBcp3R7twc3K00F4QK', '2025-11-04 19:54:37');

-- --------------------------------------------------------

--
-- بنية الجدول `projects`
--

CREATE TABLE `projects` (
  `id` int(11) NOT NULL,
  `title` varchar(255) NOT NULL,
  `description` text DEFAULT NULL,
  `category` varchar(50) NOT NULL,
  `image_url` varchar(500) NOT NULL,
  `location` varchar(100) DEFAULT NULL,
  `duration` varchar(50) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- إرجاع أو استيراد بيانات الجدول `projects`
--

INSERT INTO `projects` (`id`, `title`, `description`, `category`, `image_url`, `location`, `duration`, `created_at`, `updated_at`) VALUES
(1, 'ggg', 'hghghghg', 'tank-insulation', 'uploads/6907c02b4f1ef_1762115627.jpg', 'الرياض', '3 ايام', '2025-11-02 20:33:47', '2025-11-02 20:33:47'),
(2, 'رخام منازل', 'ااااااااااااااااااااا', 'roof-insulation', 'uploads/6907c46c118e1_1762116716.jpg', 'البيرين', '2 ايام', '2025-11-02 20:51:56', '2025-11-02 20:51:56'),
(3, 'عزل ارضيات', 'ىىىىىىىىىىىىىىىىىىىى', 'epoxy', 'uploads/6907c6ac6b5b9_1762117292.jpg', 'تعز', '20 ايام', '2025-11-02 21:01:32', '2025-11-02 21:01:32'),
(4, 'اثاث منزل', 'ككككككككككككككككك', 'roof-insulation', 'uploads/6907ccf4d5f03_1762118900.jpg', '', '', '2025-11-02 21:28:20', '2025-11-02 21:28:20'),
(5, 'عزل ارضيات', 'رررررررررررررررررررررر', 'epoxy', 'uploads/6907cd48e48be_1762118984.jpg', '', '', '2025-11-02 21:29:44', '2025-11-02 21:29:44'),
(7, 'سسس', 'lk;lk;l', 'roof-insulation', 'uploads/690914f97db29_1762202873.jpg', 'تعز', '9 ايام', '2025-11-03 20:47:53', '2025-11-03 20:47:53'),
(8, 'ديكور', 'ppppppppppppppppp', 'roof-insulation', 'uploads/690a6633f1f2c_1762289203.jpg', 'البيرين', '3 ايام', '2025-11-04 20:46:44', '2025-11-04 20:46:44'),
(9, 'عزل اسطح', 'hghjggf', 'roof-insulation', 'uploads/690ba60c226b5_1762371084.jpg', 'تعز', '5 ايام', '2025-11-05 19:31:24', '2025-11-05 19:31:24'),
(10, 'ggg', 'sssaa', 'tank-insulation', 'uploads/690ba66154eb5_1762371169.jpg', 'الرياض', '20 ايام', '2025-11-05 19:32:49', '2025-11-05 19:32:49'),
(13, 'عزل اسطح', 'nmnjh', 'roof-insulation', 'uploads/6911a0a7a7a7d_1762762919.jpg', '', '', '2025-11-10 08:21:59', '2025-11-10 08:21:59'),
(17, 'رخاميشن', 'ببببببببببببببببببببب', 'marble', 'uploads/6911b55ea02f0_1762768222.jpg', 'تعز', '5 ايام', '2025-11-10 09:50:22', '2025-11-10 09:50:22'),
(18, 'رخام منازل', 'ةةةةةةةةةةةةةةةةةة', 'marble', 'uploads/6911b77044322_1762768752.jpg', 'الرياض', '3 ايام', '2025-11-10 09:59:12', '2025-11-10 09:59:12'),
(19, 'عزل ارضيات', 'jhjhkj', 'marble', 'uploads/6911c23a84b1b_1762771514.jpg', 'تعز', '20 ايام', '2025-11-10 10:45:14', '2025-11-10 10:45:14'),
(20, 'عزل اسطح', ';;;;;;;;;;;;;;;;;;;;;;;', 'roof-insulation', 'uploads/6911c261bcef4_1762771553.jpg', 'البيرين', '2 ايام', '2025-11-10 10:45:53', '2025-11-10 10:45:53'),
(21, 'رخام منازل', 'ytytyt', 'marble', 'uploads/6911c558bb4cc_1762772312.jpg', 'الرياض', '3 ايام', '2025-11-10 10:58:32', '2025-11-10 10:58:32'),
(22, 'اثاث منزل', ',,,,,,,,,,,,,,,,,,,,,,,,,', 'epoxy', 'uploads/6911d3a00d9f2_1762775968.jpg', 'البيرين', '20 ايام', '2025-11-10 11:59:28', '2025-11-10 11:59:28'),
(23, 'رخام منازل', 'jhfdjhgjfdg', 'marble', 'uploads/6911d59ac20ae_1762776474.jpg', 'الرياض', '3 ايام', '2025-11-10 12:07:54', '2025-11-10 12:07:54');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `admin_users`
--
ALTER TABLE `admin_users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `username` (`username`);

--
-- Indexes for table `projects`
--
ALTER TABLE `projects`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_category` (`category`),
  ADD KEY `idx_created` (`created_at`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `admin_users`
--
ALTER TABLE `admin_users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `projects`
--
ALTER TABLE `projects`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=24;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;

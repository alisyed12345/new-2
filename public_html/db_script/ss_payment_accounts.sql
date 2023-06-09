-- phpMyAdmin SQL Dump
-- version 5.1.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Feb 17, 2022 at 02:24 PM
-- Server version: 10.4.22-MariaDB
-- PHP Version: 7.3.33

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `bayyan005_icksaturdaydv`
--

-- --------------------------------------------------------

--
-- Table structure for table `ss_payment_accounts`
--

CREATE TABLE `ss_payment_accounts` (
  `id` int(10) NOT NULL,
  `account_holder` varchar(50) DEFAULT NULL,
  `user_id` int(11) DEFAULT NULL,
  `system_account` tinyint(4) DEFAULT 0,
  `created_on` datetime NOT NULL,
  `created_by_user_id` int(11) DEFAULT NULL,
  `updated_on` datetime DEFAULT NULL,
  `updated_by_user_id` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Dumping data for table `ss_payment_accounts`
--

INSERT INTO `ss_payment_accounts` (`id`, `account_holder`, `user_id`, `system_account`, `created_on`, `created_by_user_id`, `updated_on`, `updated_by_user_id`) VALUES
(1, 'ick_school', 2, 1, '2022-02-09 07:24:51', NULL, '2022-02-09 07:24:51', NULL),
(2, 'CC', NULL, 0, '2022-02-09 07:26:17', NULL, '2022-02-09 07:26:17', NULL),
(3, 'ACH', NULL, 0, '2022-02-09 07:26:47', NULL, '2022-02-09 07:26:47', NULL),
(4, 'CASH', NULL, 0, '2022-02-09 07:26:56', NULL, '2022-02-09 07:26:56', NULL);

--
-- Indexes for dumped tables
--

--
-- Indexes for table `ss_payment_accounts`
--
ALTER TABLE `ss_payment_accounts`
  ADD PRIMARY KEY (`id`),
  ADD KEY `holder_user_id` (`user_id`),
  ADD KEY `create_user_id` (`created_by_user_id`),
  ADD KEY `update_user_id` (`updated_by_user_id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `ss_payment_accounts`
--
ALTER TABLE `ss_payment_accounts`
  MODIFY `id` int(10) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=27;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `ss_payment_accounts`
--
ALTER TABLE `ss_payment_accounts`
  ADD CONSTRAINT `create_user_id` FOREIGN KEY (`created_by_user_id`) REFERENCES `ss_user` (`id`),
  ADD CONSTRAINT `holder_user_id` FOREIGN KEY (`user_id`) REFERENCES `ss_user` (`id`),
  ADD CONSTRAINT `update_user_id` FOREIGN KEY (`updated_by_user_id`) REFERENCES `ss_user` (`id`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;

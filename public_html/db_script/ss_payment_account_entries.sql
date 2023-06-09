-- phpMyAdmin SQL Dump
-- version 5.1.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Feb 17, 2022 at 02:25 PM
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
-- Table structure for table `ss_payment_account_entries`
--

CREATE TABLE `ss_payment_account_entries` (
  `id` int(11) UNSIGNED NOT NULL,
  `description` varchar(255) DEFAULT NULL,
  `amount` decimal(18,2) UNSIGNED DEFAULT NULL,
  `debit_pay_account_id` int(11) NOT NULL,
  `credit_pay_account_id` int(11) NOT NULL,
  `is_manual` tinyint(5) DEFAULT 0,
  `created_on` datetime DEFAULT NULL,
  `created_by_user_id` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Indexes for dumped tables
--

--
-- Indexes for table `ss_payment_account_entries`
--
ALTER TABLE `ss_payment_account_entries`
  ADD PRIMARY KEY (`id`),
  ADD KEY `dr_pay_acc_id` (`debit_pay_account_id`),
  ADD KEY `cr_pay_acc_id` (`credit_pay_account_id`),
  ADD KEY `created_by_user_id` (`created_by_user_id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `ss_payment_account_entries`
--
ALTER TABLE `ss_payment_account_entries`
  MODIFY `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `ss_payment_account_entries`
--
ALTER TABLE `ss_payment_account_entries`
  ADD CONSTRAINT `cr_pay_acc_id` FOREIGN KEY (`credit_pay_account_id`) REFERENCES `ss_payment_accounts` (`id`),
  ADD CONSTRAINT `dr_pay_acc_id` FOREIGN KEY (`debit_pay_account_id`) REFERENCES `ss_payment_accounts` (`id`),
  ADD CONSTRAINT `ss_payment_account_entries_ibfk_1` FOREIGN KEY (`created_by_user_id`) REFERENCES `ss_user` (`id`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;

-- phpMyAdmin SQL Dump
-- version 5.1.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Feb 17, 2022 at 02:26 PM
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
-- Table structure for table `ss_student_fees_virtual_transactions`
--

CREATE TABLE `ss_student_fees_virtual_transactions` (
  `id` int(11) UNSIGNED NOT NULL,
  `student_fees_item_id` int(10) UNSIGNED NOT NULL,
  `payment_account_entries_id` int(11) UNSIGNED NOT NULL,
  `created_on` datetime NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Indexes for dumped tables
--

--
-- Indexes for table `ss_student_fees_virtual_transactions`
--
ALTER TABLE `ss_student_fees_virtual_transactions`
  ADD PRIMARY KEY (`id`),
  ADD KEY `student_fees_item_id` (`student_fees_item_id`),
  ADD KEY `payment_account_entries_id` (`payment_account_entries_id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `ss_student_fees_virtual_transactions`
--
ALTER TABLE `ss_student_fees_virtual_transactions`
  MODIFY `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `ss_student_fees_virtual_transactions`
--
ALTER TABLE `ss_student_fees_virtual_transactions`
  ADD CONSTRAINT `ss_student_fees_virtual_transactions_ibfk_1` FOREIGN KEY (`student_fees_item_id`) REFERENCES `ss_student_fees_items` (`id`),
  ADD CONSTRAINT `ss_student_fees_virtual_transactions_ibfk_2` FOREIGN KEY (`payment_account_entries_id`) REFERENCES `ss_payment_account_entries` (`id`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;

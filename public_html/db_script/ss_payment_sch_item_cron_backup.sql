-- phpMyAdmin SQL Dump
-- version 5.1.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Mar 28, 2022 at 01:14 PM
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
-- Table structure for table `ss_payment_sch_item_cron_backup`
--

CREATE TABLE `ss_payment_sch_item_cron_backup` (
  `id` int(10) UNSIGNED NOT NULL,
  `schedule_unique_id` varchar(100) NOT NULL,
  `family_id` int(11) NOT NULL,
  `sch_item_ids` varchar(20) DEFAULT NULL,
  `schedule_payment_date` date NOT NULL,
  `total_amount` decimal(10,0) NOT NULL,
  `wallet_amount` double NOT NULL DEFAULT 0,
  `cc_amount` double NOT NULL DEFAULT 0,
  `schedule_status` tinyint(4) NOT NULL DEFAULT 0,
  `retry_count` tinyint(4) DEFAULT 0,
  `session` int(11) DEFAULT NULL,
  `is_approval` tinyint(4) NOT NULL DEFAULT 1 COMMENT 'default approved',
  `reason` varchar(200) DEFAULT NULL,
  `payment_unique_id` varchar(100) DEFAULT NULL,
  `payment_response_code` varchar(10) DEFAULT NULL,
  `payment_response` text DEFAULT NULL,
  `created_at` datetime DEFAULT NULL ON UPDATE current_timestamp(),
  `updated_at` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Indexes for dumped tables
--

--
-- Indexes for table `ss_payment_sch_item_cron_backup`
--
ALTER TABLE `ss_payment_sch_item_cron_backup`
  ADD PRIMARY KEY (`id`),
  ADD KEY `fkstudefeesitem-1` (`session`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `ss_payment_sch_item_cron_backup`
--
ALTER TABLE `ss_payment_sch_item_cron_backup`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;

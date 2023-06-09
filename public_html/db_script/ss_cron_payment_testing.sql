-- phpMyAdmin SQL Dump
-- version 5.1.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Jul 18, 2022 at 08:24 AM
-- Server version: 10.4.21-MariaDB
-- PHP Version: 7.3.30

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `ick_sturday_qa`
--

-- --------------------------------------------------------

--
-- Table structure for table `ss_cron_payment_testing`
--

CREATE TABLE `ss_cron_payment_testing` (
  `id` int(11) NOT NULL,
  `cron_date` date DEFAULT NULL,
  `status` tinyint(3) DEFAULT 1,
  `created_on` datetime DEFAULT NULL,
  `updated_on` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Dumping data for table `ss_cron_payment_testing`
--

INSERT INTO `ss_cron_payment_testing` (`id`, `cron_date`, `status`, `created_on`, `updated_on`) VALUES
(1, '2022-07-19', 1, '2022-07-15 04:32:51', '2022-07-15 05:04:45');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `ss_cron_payment_testing`
--
ALTER TABLE `ss_cron_payment_testing`
  ADD PRIMARY KEY (`id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `ss_cron_payment_testing`
--
ALTER TABLE `ss_cron_payment_testing`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;

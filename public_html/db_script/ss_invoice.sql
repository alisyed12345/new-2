-- phpMyAdmin SQL Dump
-- version 5.1.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Mar 28, 2022 at 01:16 PM
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
-- Table structure for table `ss_invoice`
--

CREATE TABLE `ss_invoice` (
  `id` int(11) NOT NULL,
  `family_id` int(10) NOT NULL,
  `schedule_unique_id` varchar(100) NOT NULL,
  `invoice_id` varchar(100) DEFAULT NULL,
  `invoice_date` datetime DEFAULT NULL,
  `amount` decimal(18,2) NOT NULL,
  `receipt_id` varchar(100) DEFAULT NULL,
  `receipt_date` datetime DEFAULT NULL,
  `is_due` tinyint(3) NOT NULL COMMENT '0=Due, 1=Paid, 2=Overdue',
  `invoice_file_path` varchar(200) DEFAULT NULL,
  `receipt_file_path` varchar(200) DEFAULT NULL,
  `status` tinyint(3) NOT NULL DEFAULT 0 COMMENT '0=Active, 1=Delete',
  `reason` varchar(255) DEFAULT NULL,
  `created_at` datetime NOT NULL,
  `created_by` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Indexes for dumped tables
--

--
-- Indexes for table `ss_invoice`
--
ALTER TABLE `ss_invoice`
  ADD PRIMARY KEY (`id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `ss_invoice`
--
ALTER TABLE `ss_invoice`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;

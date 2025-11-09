-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Sep 14, 2025 at 07:45 PM
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
-- Database: `vivacity_ctf`
--

-- --------------------------------------------------------

--
-- Table structure for table `tbl_admin_detail`
--

CREATE TABLE `tbl_admin_detail` (
  `admin_id` int(11) NOT NULL,
  `admin_name` varchar(255) NOT NULL,
  `email` varchar(255) NOT NULL,
  `password` varchar(255) NOT NULL,
  `created` datetime NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `tbl_user_detail`
--

CREATE TABLE `tbl_user_detail` (
  `user_id` int(11) NOT NULL,
  `user_name` varchar(255) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `contact` bigint(20) NOT NULL,
  `pc_number` varchar(100) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `tbl_user_detail`
--

INSERT INTO `tbl_user_detail` (`user_id`, `user_name`, `created_at`, `contact`, `pc_number`) VALUES
(1, 'MeetKapadiya', '2025-09-03 14:54:15', 0, '0'),
(2, 'Dhruv', '2025-09-03 15:05:07', 0, '0'),
(3, 'Dhruv', '2025-09-03 15:05:09', 0, '0'),
(4, 'Hellloo', '2025-09-03 15:10:05', 0, '0'),
(5, 'Demo User', '2025-09-03 15:14:16', 0, '0'),
(6, 'finish', '2025-09-03 15:16:11', 0, '0'),
(7, 'Testing', '2025-09-03 15:24:12', 0, '0'),
(8, 'manav', '2025-09-12 12:38:07', 0, '0'),
(9, 'M12', '2025-09-12 12:59:28', 0, '0'),
(10, 'manav1234', '2025-09-12 13:06:22', 0, '0'),
(11, 'aesha', '2025-09-12 13:10:57', 0, '0'),
(12, 'MANAV', '2025-09-12 17:35:10', 0, '0'),
(13, 'abc', '2025-09-13 13:28:47', 0, '0'),
(14, 'MANAV', '2025-09-14 07:02:17', 0, '0'),
(15, 'manav', '2025-09-14 09:12:11', 0, '0'),
(16, 'a', '2025-09-14 10:08:23', 0, '0'),
(17, 'manav', '2025-09-14 10:18:45', 9687309751, '16'),
(18, 'manavvaidya', '2025-09-14 11:04:43', 9687309751, '1');

-- --------------------------------------------------------

--
-- Table structure for table `tbl_user_score`
--

CREATE TABLE `tbl_user_score` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `c1` varchar(5) DEFAULT NULL,
  `c2` varchar(5) DEFAULT NULL,
  `c3` varchar(5) DEFAULT NULL,
  `c4` varchar(5) DEFAULT NULL,
  `c5` varchar(5) DEFAULT NULL,
  `c6` varchar(5) DEFAULT NULL,
  `c7` varchar(5) DEFAULT NULL,
  `c8` varchar(5) DEFAULT NULL,
  `c9` varchar(5) DEFAULT NULL,
  `c10` varchar(5) DEFAULT NULL,
  `c11` varchar(5) DEFAULT NULL,
  `c12` varchar(5) DEFAULT NULL,
  `c13` varchar(5) DEFAULT NULL,
  `c14` varchar(5) DEFAULT NULL,
  `c15` varchar(5) DEFAULT NULL,
  `c16` varchar(5) DEFAULT NULL,
  `c17` varchar(5) DEFAULT NULL,
  `c18` varchar(5) DEFAULT NULL,
  `c19` varchar(5) DEFAULT NULL,
  `c20` varchar(5) DEFAULT NULL,
  `total_point` int(11) DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `tbl_user_score`
--

INSERT INTO `tbl_user_score` (`id`, `user_id`, `c1`, `c2`, `c3`, `c4`, `c5`, `c6`, `c7`, `c8`, `c9`, `c10`, `c11`, `c12`, `c13`, `c14`, `c15`, `c16`, `c17`, `c18`, `c19`, `c20`, `total_point`) VALUES
(1, 1, '2025-', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 10),
(2, 2, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 0),
(3, 3, '00:14', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 10),
(4, 4, '00:07', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 10),
(5, 5, '00:08', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 10),
(6, 6, '00:56', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 10),
(7, 7, '00:22', '00:30', '00:14', '00:28', '01:59', '11:52', '02:45', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 70),
(8, 8, '00:05', '08:50', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 20),
(9, 9, '00:16', '', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 20),
(10, 10, '00:21', '', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 20),
(11, 11, '00:14', '00:16', '03:21', '07:29', '05:22', '', '01:15', '119:3', '01:40', '02:20', '01:40', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 110),
(12, 12, '00:04', '01:13', '00:21', '00:40', '00:57', '', '00:45', '02:19', '00:29', '00:28', '00:30', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 110),
(13, 13, '00:26', '00:12', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 20),
(14, 14, '00:43', '00:09', '00:42', '00:31', '00:15', '', '00:44', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 70),
(15, 15, '00:26', '00:18', '00:34', '00:40', '00:14', '', '00:44', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 70),
(16, 16, '00:13', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 10),
(17, 17, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 0),
(18, 18, '00:23', '00:09', '00:16', '00:15', '02:40', '', '00:18', '00:30', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 70);

--
-- Indexes for dumped tables
--

--
-- Indexes for table `tbl_admin_detail`
--
ALTER TABLE `tbl_admin_detail`
  ADD PRIMARY KEY (`admin_id`),
  ADD UNIQUE KEY `email` (`email`);

--
-- Indexes for table `tbl_user_detail`
--
ALTER TABLE `tbl_user_detail`
  ADD PRIMARY KEY (`user_id`);

--
-- Indexes for table `tbl_user_score`
--
ALTER TABLE `tbl_user_score`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `tbl_admin_detail`
--
ALTER TABLE `tbl_admin_detail`
  MODIFY `admin_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `tbl_user_detail`
--
ALTER TABLE `tbl_user_detail`
  MODIFY `user_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=19;

--
-- AUTO_INCREMENT for table `tbl_user_score`
--
ALTER TABLE `tbl_user_score`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=19;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `tbl_user_score`
--
ALTER TABLE `tbl_user_score`
  ADD CONSTRAINT `tbl_user_score_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `tbl_user_detail` (`user_id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;

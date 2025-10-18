-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Aug 10, 2025 at 07:50 AM
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
-- Database: `rental`
--

-- --------------------------------------------------------

--
-- Table structure for table `accounts`
--

CREATE TABLE `accounts` (
  `id` int(11) NOT NULL,
  `tenant_id` int(11) NOT NULL,
  `username` varchar(50) NOT NULL,
  `password` varchar(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `accounts`
--

INSERT INTO `accounts` (`id`, `tenant_id`, `username`, `password`) VALUES
(27, 90, '09382383025', '$2y$10$oNUJ9xkXfUn3N7QRK5.BXucFQn3dLcLUBePhoauMVvh9251GAoeVi'),
(28, 91, '09382383026', '$2y$10$rWgQXdOmCYd2meipekRGq.wChGimQrckiPSlLLq7oypBq5JKgR0Pm'),
(29, 92, '09123456789', '$2y$10$fWr8OzQiyUb/Ya/tIg7Hw.K0Urz57WWlQ7GbTGeerH5PtUiZbE3IS'),
(30, 93, '09987654321', '$2y$10$V46l2An1y8b3lcLSK8RSUenWMxWp4FUrj5R8qfDuuhk5FFix4qKqS'),
(31, 94, '0995413711', '$2y$10$YMBdmMeU4eyP2mMB56zbz.96.kXhDrb1MoMPVAoKxIqYeEPirey4K');

-- --------------------------------------------------------

--
-- Table structure for table `admin`
--

CREATE TABLE `admin` (
  `id` int(200) NOT NULL,
  `username` varchar(200) NOT NULL,
  `password` varchar(200) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `admin`
--

INSERT INTO `admin` (`id`, `username`, `password`) VALUES
(1, 'admin', 'admin123');

-- --------------------------------------------------------

--
-- Table structure for table `messages`
--

CREATE TABLE `messages` (
  `id` int(11) NOT NULL,
  `sender_id` int(11) NOT NULL,
  `receiver_id` int(11) NOT NULL,
  `message` text NOT NULL,
  `sent_at` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `messages`
--

INSERT INTO `messages` (`id`, `sender_id`, `receiver_id`, `message`, `sent_at`) VALUES
(1, 1, 93, 'Hi ace d. hatdog, please settle your rent for Stall #4. Penalty ₱1,942.50. Total ₱9,712.50.', '2025-08-08 11:51:31'),
(2, 1, 93, 'Hi ace d. hatdog, please settle your rent for Stall #4. Penalty ₱1,942.50. Total ₱9,712.50.', '2025-08-08 11:53:36'),
(3, 1, 93, 'Hi ace d. hatdog, please settle your rent for Stall #4. Penalty ₱1,942.50. Total ₱9,712.50.', '2025-08-08 11:54:07'),
(4, 1, 93, 'Hi ace d. hatdog, please settle your rent for Stall #4. Penalty ₱1,942.50. Total ₱9,712.50.', '2025-08-08 11:55:33'),
(5, 1, 93, 'Hi ace d. hatdog, please settle your rent for Stall #4. Penalty ₱1,942.50. Total ₱9,712.50.', '2025-08-08 11:57:09'),
(6, 1, 91, 'Hi jolo b. penaredondo, please settle your rent for Stall #2. Penalty ₱1,292.50. Total ₱6,462.50.', '2025-08-08 12:51:30'),
(7, 1, 90, 'Hi Crystalene m pesado, please settle your rent for Stall #1. Penalty ₱1,942.50. Total ₱9,712.50.', '2025-08-08 12:52:09'),
(8, 1, 90, 'Hi Crystalene m pesado, please settle your rent for Stall #1. Penalty ₱1,942.50. Total ₱9,712.50.', '2025-08-08 13:05:18'),
(9, 1, 93, 'Hi ace d. hatdog, please settle your rent for Stall #4. Penalty ₱1,942.50. Total ₱9,712.50.', '2025-08-08 18:13:54'),
(10, 1, 94, 'Hi garp d monkey, please settle your rent for Stall #5. Penalty ₱1,942.50. Total ₱9,712.50.', '2025-08-08 18:13:56'),
(11, 1, 90, 'Hi Crystalene m pesado, please settle your rent for Stall #1. Penalty ₱1,942.50. Total ₱9,712.50.', '2025-08-08 20:08:09'),
(12, 1, 90, 'Hi Crystalene m pesado, please settle your rent for Stall #1. Penalty ₱1,942.50. Total ₱9,712.50.', '2025-08-08 20:36:06');

-- --------------------------------------------------------

--
-- Table structure for table `notifications`
--

CREATE TABLE `notifications` (
  `id` int(11) NOT NULL,
  `tenant_id` int(11) DEFAULT NULL,
  `title` varchar(255) DEFAULT NULL,
  `message` text DEFAULT NULL,
  `created_at` datetime DEFAULT NULL,
  `status` enum('unread','read') NOT NULL DEFAULT 'unread'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `notifications`
--

INSERT INTO `notifications` (`id`, `tenant_id`, `title`, `message`, `created_at`, `status`) VALUES
(1, NULL, NULL, NULL, '2025-07-24 17:11:12', 'unread'),
(2, 93, 'Manual Rent Due Notice', 'Your rent for Stall #4 is overdue. Penalty: ₱1,942.50. Total: ₱9,712.50.', '2025-08-08 11:51:31', 'unread'),
(3, 93, 'Manual Rent Due Notice', 'Your rent for Stall #4 is overdue. Penalty: ₱1,942.50. Total: ₱9,712.50.', '2025-08-08 11:53:36', 'unread'),
(4, 93, 'Manual Rent Due Notice', 'Your rent for Stall #4 is overdue. Penalty: ₱1,942.50. Total: ₱9,712.50.', '2025-08-08 11:54:07', 'unread'),
(5, 93, 'Manual Rent Due Notice', 'Your rent for Stall #4 is overdue. Penalty: ₱1,942.50. Total: ₱9,712.50.', '2025-08-08 11:55:33', 'unread'),
(6, 93, 'Manual Rent Due Notice', 'Your rent for Stall #4 is overdue. Penalty: ₱1,942.50. Total: ₱9,712.50.', '2025-08-08 11:57:09', 'unread'),
(7, 91, 'Manual Rent Due Notice', 'Your rent for Stall #2 is overdue. Penalty: ₱1,292.50. Total: ₱6,462.50.', '2025-08-08 12:51:30', 'unread'),
(8, 90, 'Manual Rent Due Notice', 'Your rent for Stall #1 is overdue. Penalty: ₱1,942.50. Total: ₱9,712.50.', '2025-08-08 12:52:09', 'read'),
(9, 90, 'Manual Rent Due Notice', 'Your rent for Stall #1 is overdue. Penalty: ₱1,942.50. Total: ₱9,712.50.', '2025-08-08 13:05:18', 'read'),
(10, 93, 'Manual Rent Due Notice', 'Your rent for Stall #4 is overdue. Penalty: ₱1,942.50. Total: ₱9,712.50.', '2025-08-08 18:13:54', 'unread'),
(11, 94, 'Manual Rent Due Notice', 'Your rent for Stall #5 is overdue. Penalty: ₱1,942.50. Total: ₱9,712.50.', '2025-08-08 18:13:56', 'read'),
(12, 90, 'Manual Rent Due Notice', 'Your rent for Stall #1 is overdue. Penalty: ₱1,942.50. Total: ₱9,712.50.', '2025-08-08 20:08:09', 'read'),
(13, 90, 'Manual Rent Due Notice', 'Your rent for Stall #1 is overdue. Penalty: ₱1,942.50. Total: ₱9,712.50.', '2025-08-08 20:36:06', 'read');

-- --------------------------------------------------------

--
-- Table structure for table `payment`
--

CREATE TABLE `payment` (
  `payment_id` int(11) NOT NULL,
  `tenant_id` int(11) DEFAULT NULL,
  `payment_amount` decimal(10,2) DEFAULT NULL,
  `payment_date` date DEFAULT NULL,
  `payment_method` varchar(50) DEFAULT NULL,
  `payment_status` enum('unpaid','accepted payment','paid') DEFAULT 'unpaid',
  `payment_screenshot` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `payment`
--

INSERT INTO `payment` (`payment_id`, `tenant_id`, `payment_amount`, `payment_date`, `payment_method`, `payment_status`, `payment_screenshot`) VALUES
(67, 90, 5170.00, '2025-06-04', 'PayMaya', 'unpaid', NULL),
(68, 90, 6462.50, '2025-07-16', 'PayMaya', 'unpaid', NULL),
(69, 90, 3250.00, '2025-07-24', 'G-Cash', 'unpaid', NULL),
(70, 90, 1942.50, '2025-07-24', 'PayMaya', 'unpaid', NULL),
(71, 90, 9712.50, '2025-08-08', 'Over The Counter', 'unpaid', NULL),
(72, 91, 5170.00, '2025-08-08', 'PayMaya', 'unpaid', NULL),
(73, 93, 7770.00, '2025-08-08', 'G-Cash', 'unpaid', NULL),
(74, 92, 7770.00, '2025-08-08', 'Over The Counter', 'unpaid', NULL);

-- --------------------------------------------------------

--
-- Table structure for table `payments`
--

CREATE TABLE `payments` (
  `transaction_id` int(11) NOT NULL,
  `tenant_name` varchar(100) NOT NULL,
  `amount` decimal(10,2) NOT NULL,
  `payment_method` enum('GCash','PayPal','Credit Card') NOT NULL,
  `payment_date` datetime DEFAULT current_timestamp(),
  `status` enum('Completed','Pending','Failed') DEFAULT 'Completed'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `penalties`
--

CREATE TABLE `penalties` (
  `id` int(11) NOT NULL,
  `tenant_id` int(11) NOT NULL,
  `penalty_amount` decimal(10,2) DEFAULT NULL,
  `penalty_date` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `penalty`
--

CREATE TABLE `penalty` (
  `id` int(11) NOT NULL,
  `tenant_id` int(11) NOT NULL,
  `amount` decimal(10,2) NOT NULL,
  `applied_on` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `report`
--

CREATE TABLE `report` (
  `id` int(11) NOT NULL,
  `report_type` varchar(50) DEFAULT NULL,
  `report_date` date DEFAULT NULL,
  `total_income` decimal(10,2) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `stall`
--

CREATE TABLE `stall` (
  `id` int(200) NOT NULL,
  `stall_no` int(200) NOT NULL,
  `renter_name` varchar(200) NOT NULL,
  `availability` varchar(200) NOT NULL,
  `monthly_price` float NOT NULL,
  `yearly_price` float NOT NULL,
  `stall_section` varchar(255) NOT NULL,
  `stall_image` varchar(255) DEFAULT NULL,
  `category` varchar(100) DEFAULT NULL,
  `stall_name` varchar(100) DEFAULT NULL,
  `tenant_id` int(11) DEFAULT NULL,
  `status` varchar(20) DEFAULT 'available',
  `requested_date` date DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `stall`
--

INSERT INTO `stall` (`id`, `stall_no`, `renter_name`, `availability`, `monthly_price`, `yearly_price`, `stall_section`, `stall_image`, `category`, `stall_name`, `tenant_id`, `status`, `requested_date`) VALUES
(90, 1, '', 'unavailable', 7770, 93240, 'Corner stall', '134567890.jpg', 'Fish section', 'bebong', NULL, 'available', NULL),
(91, 2, '', 'unavailable', 5170, 62040, 'Inside stall', 'Appear+Here+-+Premium+Black+Market+Stall+with+Elevated+Branding.jpg', 'Grocery', 'gt', NULL, 'available', NULL),
(92, 3, '', 'unavailable', 7770, 93240, 'Main stall', 'Bin.jpg', 'Meat section', 'hatdo', 92, 'requested', NULL),
(93, 4, '', 'unavailable', 7770, 93240, 'Main stall', 'Bin.jpg', 'Fish section', 'kiw', 93, 'requested', NULL),
(94, 5, '', 'unavailable', 7770, 93240, 'Corner stall', '526433642_1221231373350224_977199730886981467_n.jpg', 'Bags & Accessories', 'Jun May', 94, 'requested', NULL),
(95, 6, '', 'available', 7770, 93240, 'Main stall', '527453716_546965668438786_2900809345531190547_n.jpg', 'Rice & Grains', 'dfghjkl', NULL, 'available', NULL),
(96, 6, '', 'available', 7770, 93240, 'Main stall', '528134070_747797648231102_2600209349880071727_n.jpg', 'Electronics & Repair', 'Oppo store', NULL, 'available', NULL),
(97, 7, '', 'available', 7770, 93240, 'Corner stall', '521652185_1070520311457741_120753630399313569_n.jpg', 'Bags & Accessories', 'pawibi', NULL, 'available', NULL);

-- --------------------------------------------------------

--
-- Table structure for table `tenant`
--

CREATE TABLE `tenant` (
  `id` int(11) NOT NULL,
  `name` varchar(255) NOT NULL,
  `address` text DEFAULT NULL,
  `contact_NO` varchar(20) DEFAULT NULL,
  `start_date` date DEFAULT NULL,
  `school_year` varchar(20) DEFAULT NULL,
  `stall_no_rented` int(11) DEFAULT NULL,
  `fee_taken` decimal(10,2) DEFAULT 0.00,
  `gender` varchar(200) NOT NULL,
  `date_deleted` datetime DEFAULT NULL,
  `password` varchar(255) NOT NULL,
  `profile_pic` varchar(255) DEFAULT NULL,
  `remaining_balance` decimal(10,2) DEFAULT 0.00,
  `payment_status` enum('paid','unpaid') DEFAULT 'unpaid',
  `date_started` date DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `tenant`
--

INSERT INTO `tenant` (`id`, `name`, `address`, `contact_NO`, `start_date`, `school_year`, `stall_no_rented`, `fee_taken`, `gender`, `date_deleted`, `password`, `profile_pic`, `remaining_balance`, `payment_status`, `date_started`) VALUES
(90, 'Crystalene m pesado', 'dtL west', '09382383025', '2025-06-04', NULL, 1, 0.00, 'Female', NULL, '', 'uploads/1749000780_pesado.jpg', 0.00, 'unpaid', NULL),
(91, 'jolo b. penaredondo', 'janiuay', '09382383026', '2025-07-24', NULL, 2, 0.00, 'Male', NULL, '', NULL, 0.00, 'unpaid', NULL),
(92, 'monkey d. luffy', 'East blue', '09123456789', '2025-07-24', NULL, 3, 0.00, 'Male', NULL, '', NULL, 0.00, 'unpaid', NULL),
(93, 'ace d. hatdog', 'East blue', '09987654321', '2025-08-08', NULL, 4, 0.00, 'Male', NULL, '', NULL, 0.00, 'unpaid', NULL),
(94, 'garp d monkey', 'East blue', '0995413711', '2025-08-08', NULL, 5, 0.00, 'Male', NULL, '', 'uploads/1754694635_download (44).jpg', 0.00, 'unpaid', NULL),
(95, 'Mark B. Dragon', 'cabatuan', '09413564543', '2025-08-09', NULL, NULL, 0.00, 'Male', NULL, '', NULL, 0.00, 'unpaid', NULL);

-- --------------------------------------------------------

--
-- Table structure for table `tenants`
--

CREATE TABLE `tenants` (
  `id` int(11) NOT NULL,
  `name` varchar(255) NOT NULL,
  `phone` varchar(20) NOT NULL,
  `due_date` date NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `tenant_logs`
--

CREATE TABLE `tenant_logs` (
  `log_id` int(11) NOT NULL,
  `tenant_id` int(11) NOT NULL,
  `tenant_name` varchar(255) NOT NULL,
  `contact_no` varchar(15) NOT NULL,
  `address` text NOT NULL,
  `stall_no_rented` int(11) NOT NULL,
  `stall_section` varchar(255) NOT NULL,
  `deleted_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` int(11) NOT NULL,
  `username` varchar(50) NOT NULL,
  `password` varchar(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `username`, `password`) VALUES
(1, 'Admin', 'jolo12345'),
(2, 'root', '$2y$10$Y7rjcqMeqyYDFACLC904xuz3g/bWbBljQ/WmALqzjH8atEWMvEykS'),
(3, 'root', '$2y$10$QazESAxngws9LBE8bFpVPO1Y3uGiQWsDcymGKr0ruYAc4Lreu2Yk2'),
(4, 'root', '$2y$10$VkOv49g0mVupNFoIT.SYbers10hWJs4ZTQDeUZEAqki13cfbYgJH.'),
(5, 'root', '$2y$10$LyWDz31kCnuoZ2gltLwkxO6e8bwPrP7DzJzTFgmqDLzmEHjXxSUie'),
(6, 'root', '$2y$10$NfrS9m14SZoaizS00yPzT.c.R02ZS4byD3qff2p6I/mEXXDEN9moG'),
(7, 'admin1', 'jolo22'),
(8, 'admin', '$2y$10$bNdP61GjiML9p7XPlTip9euqROfbn2Y.evrrgvzYQfsUo8rPrJDQW'),
(9, 'jolo', '$2y$10$X.E.CW0daa0XxdWETgG9GOaQiNGOWs/OEQHSX4qc3Fr7qRV5wkYvS');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `accounts`
--
ALTER TABLE `accounts`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `username` (`username`),
  ADD KEY `tenant_id` (`tenant_id`);

--
-- Indexes for table `admin`
--
ALTER TABLE `admin`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `messages`
--
ALTER TABLE `messages`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `notifications`
--
ALTER TABLE `notifications`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `payment`
--
ALTER TABLE `payment`
  ADD PRIMARY KEY (`payment_id`),
  ADD KEY `payment_ibfk_1` (`tenant_id`);

--
-- Indexes for table `payments`
--
ALTER TABLE `payments`
  ADD PRIMARY KEY (`transaction_id`);

--
-- Indexes for table `penalties`
--
ALTER TABLE `penalties`
  ADD PRIMARY KEY (`id`),
  ADD KEY `tenant_id` (`tenant_id`);

--
-- Indexes for table `penalty`
--
ALTER TABLE `penalty`
  ADD PRIMARY KEY (`id`),
  ADD KEY `tenant_id` (`tenant_id`);

--
-- Indexes for table `report`
--
ALTER TABLE `report`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `stall`
--
ALTER TABLE `stall`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `tenant`
--
ALTER TABLE `tenant`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `tenants`
--
ALTER TABLE `tenants`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `tenant_logs`
--
ALTER TABLE `tenant_logs`
  ADD PRIMARY KEY (`log_id`),
  ADD KEY `tenant_id` (`tenant_id`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `accounts`
--
ALTER TABLE `accounts`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=32;

--
-- AUTO_INCREMENT for table `admin`
--
ALTER TABLE `admin`
  MODIFY `id` int(200) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `messages`
--
ALTER TABLE `messages`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=13;

--
-- AUTO_INCREMENT for table `notifications`
--
ALTER TABLE `notifications`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=14;

--
-- AUTO_INCREMENT for table `payment`
--
ALTER TABLE `payment`
  MODIFY `payment_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=75;

--
-- AUTO_INCREMENT for table `payments`
--
ALTER TABLE `payments`
  MODIFY `transaction_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `penalties`
--
ALTER TABLE `penalties`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `penalty`
--
ALTER TABLE `penalty`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `report`
--
ALTER TABLE `report`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `stall`
--
ALTER TABLE `stall`
  MODIFY `id` int(200) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=98;

--
-- AUTO_INCREMENT for table `tenant`
--
ALTER TABLE `tenant`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=96;

--
-- AUTO_INCREMENT for table `tenants`
--
ALTER TABLE `tenants`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `tenant_logs`
--
ALTER TABLE `tenant_logs`
  MODIFY `log_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=12;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=10;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `accounts`
--
ALTER TABLE `accounts`
  ADD CONSTRAINT `accounts_ibfk_1` FOREIGN KEY (`tenant_id`) REFERENCES `tenant` (`id`);

--
-- Constraints for table `payment`
--
ALTER TABLE `payment`
  ADD CONSTRAINT `payment_ibfk_1` FOREIGN KEY (`tenant_id`) REFERENCES `tenant` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `penalties`
--
ALTER TABLE `penalties`
  ADD CONSTRAINT `penalties_ibfk_1` FOREIGN KEY (`tenant_id`) REFERENCES `tenant` (`id`);

--
-- Constraints for table `penalty`
--
ALTER TABLE `penalty`
  ADD CONSTRAINT `penalty_ibfk_1` FOREIGN KEY (`tenant_id`) REFERENCES `tenant` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `tenant_logs`
--
ALTER TABLE `tenant_logs`
  ADD CONSTRAINT `tenant_logs_ibfk_1` FOREIGN KEY (`tenant_id`) REFERENCES `tenant` (`id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;

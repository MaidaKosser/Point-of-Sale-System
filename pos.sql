-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: localhost:3310
-- Generation Time: Jun 01, 2025 at 03:42 PM
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
-- Database: `pos`
--

-- --------------------------------------------------------

--
-- Table structure for table `admin`
--

CREATE TABLE `admin` (
  `name` varchar(100) DEFAULT NULL,
  `email` varchar(100) NOT NULL,
  `password` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `admin`
--

INSERT INTO `admin` (`name`, `email`, `password`) VALUES
('Abdullah Butt', 'abdullahbutt@gmail.com', '1999'),
('MK', 'maidabutt2004@gmail.2004', '09072004');

-- --------------------------------------------------------

--
-- Table structure for table `cart`
--

CREATE TABLE `cart` (
  `id` int(11) NOT NULL,
  `product_id` int(11) DEFAULT NULL,
  `product_name` varchar(100) DEFAULT NULL,
  `quantity` int(11) DEFAULT NULL CHECK (`quantity` > 0),
  `price` decimal(10,2) DEFAULT NULL,
  `subtotal` decimal(10,2) DEFAULT NULL,
  `cart_id` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `inventory`
--

CREATE TABLE `inventory` (
  `product_id` int(11) NOT NULL,
  `product_name` varchar(100) NOT NULL,
  `original_price` decimal(10,2) DEFAULT NULL,
  `resale_price` decimal(10,2) DEFAULT NULL,
  `sold_items` int(11) DEFAULT 0,
  `earnings` decimal(10,2) DEFAULT 0.00,
  `in_stock` int(11) DEFAULT 0,
  `profit` decimal(10,2) DEFAULT 0.00,
  `is_active` tinyint(1) DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `payment`
--

CREATE TABLE `payment` (
  `id` int(11) NOT NULL,
  `customer_name` varchar(100) DEFAULT NULL,
  `customer_mobile` varchar(20) DEFAULT NULL,
  `refund_amount` decimal(10,2) DEFAULT NULL,
  `total_bill` decimal(10,2) DEFAULT NULL,
  `amount_received` decimal(10,2) DEFAULT NULL,
  `return_amount` decimal(10,2) DEFAULT NULL,
  `payment_date` datetime DEFAULT current_timestamp(),
  `subtotal` decimal(10,2) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `products`
--

CREATE TABLE `products` (
  `id` int(11) NOT NULL,
  `image` varchar(255) DEFAULT NULL,
  `name` varchar(100) NOT NULL,
  `resale_price` decimal(10,2) DEFAULT NULL,
  `original_price` decimal(10,2) DEFAULT NULL,
  `quantity` int(11) DEFAULT NULL CHECK (`quantity` >= 0),
  `is_active` tinyint(1) DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `receipts`
--

CREATE TABLE `receipts` (
  `order_no` int(11) NOT NULL,
  `cart_id` int(11) DEFAULT NULL,
  `payment_id` int(11) DEFAULT NULL,
  `product_id` int(11) DEFAULT NULL,
  `quantity` int(11) DEFAULT NULL,
  `total` decimal(10,2) DEFAULT NULL,
  `receipt_date` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `refunds`
--

CREATE TABLE `refunds` (
  `id` int(11) NOT NULL,
  `customer_name` varchar(100) DEFAULT NULL,
  `customer_mobile` varchar(20) DEFAULT NULL,
  `product_id` int(11) DEFAULT NULL,
  `quantity` int(11) DEFAULT NULL,
  `refunded_amount` decimal(10,2) DEFAULT NULL,
  `refund_date` datetime DEFAULT current_timestamp(),
  `payment_id` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `sales_report`
--

CREATE TABLE `sales_report` (
  `invoice_no` int(11) NOT NULL,
  `sale_date` datetime DEFAULT current_timestamp(),
  `customer_name` varchar(100) DEFAULT NULL,
  `customer_mobile` varchar(20) DEFAULT NULL,
  `total_amount` decimal(10,2) DEFAULT NULL,
  `paid_amount` decimal(10,2) DEFAULT NULL,
  `due_amount` decimal(10,2) DEFAULT NULL,
  `profit` decimal(10,2) DEFAULT NULL,
  `cart_id` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Indexes for dumped tables
--

--
-- Indexes for table `admin`
--
ALTER TABLE `admin`
  ADD UNIQUE KEY `email` (`email`);

--
-- Indexes for table `cart`
--
ALTER TABLE `cart`
  ADD PRIMARY KEY (`id`),
  ADD KEY `product_id` (`product_id`);

--
-- Indexes for table `inventory`
--
ALTER TABLE `inventory`
  ADD KEY `product_id` (`product_id`);

--
-- Indexes for table `payment`
--
ALTER TABLE `payment`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `products`
--
ALTER TABLE `products`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `receipts`
--
ALTER TABLE `receipts`
  ADD PRIMARY KEY (`order_no`),
  ADD KEY `payment_id` (`payment_id`),
  ADD KEY `product_id` (`product_id`);

--
-- Indexes for table `refunds`
--
ALTER TABLE `refunds`
  ADD PRIMARY KEY (`id`),
  ADD KEY `product_id` (`product_id`),
  ADD KEY `payment_id` (`payment_id`);

--
-- Indexes for table `sales_report`
--
ALTER TABLE `sales_report`
  ADD PRIMARY KEY (`invoice_no`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `cart`
--
ALTER TABLE `cart`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `payment`
--
ALTER TABLE `payment`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `products`
--
ALTER TABLE `products`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `receipts`
--
ALTER TABLE `receipts`
  MODIFY `order_no` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `refunds`
--
ALTER TABLE `refunds`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `sales_report`
--
ALTER TABLE `sales_report`
  MODIFY `invoice_no` int(11) NOT NULL AUTO_INCREMENT;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `cart`
--
ALTER TABLE `cart`
  ADD CONSTRAINT `cart_ibfk_1` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`);

--
-- Constraints for table `inventory`
--
ALTER TABLE `inventory`
  ADD CONSTRAINT `inventory_ibfk_1` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`);

--
-- Constraints for table `receipts`
--
ALTER TABLE `receipts`
  ADD CONSTRAINT `receipts_ibfk_1` FOREIGN KEY (`payment_id`) REFERENCES `payment` (`id`),
  ADD CONSTRAINT `receipts_ibfk_2` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`);

--
-- Constraints for table `refunds`
--
ALTER TABLE `refunds`
  ADD CONSTRAINT `refunds_ibfk_1` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`),
  ADD CONSTRAINT `refunds_ibfk_2` FOREIGN KEY (`payment_id`) REFERENCES `payment` (`id`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;

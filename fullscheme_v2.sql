-- =====================================================
-- NA-FIX ERP System - Complete Database
-- Database: cybor432_erpnew
-- Version: 2.0 - Full ERP with Login & Roles
-- =====================================================

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";

/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

-- =====================================================
-- CREATE DATABASE
-- =====================================================
CREATE DATABASE IF NOT EXISTS `cybor432_erpnew` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE `cybor432_erpnew`;

-- =====================================================
-- 1. USERS TABLE (LOGIN SYSTEM)
-- =====================================================
CREATE TABLE `users` (
  `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT,
  `code` varchar(191) NOT NULL,
  `username` varchar(191) NOT NULL,
  `email` varchar(191) DEFAULT NULL,
  `password` varchar(191) NOT NULL,
  `first_name` varchar(191) DEFAULT NULL,
  `last_name` varchar(191) DEFAULT NULL,
  `role_id` int(10) UNSIGNED DEFAULT NULL,
  `branch_id` int(10) UNSIGNED DEFAULT NULL,
  `usertype` varchar(191) DEFAULT 'USER',
  `phone` varchar(191) DEFAULT NULL,
  `avatar_url` text DEFAULT NULL,
  `status` varchar(191) DEFAULT 'active',
  `last_login` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `code` (`code`),
  UNIQUE KEY `username` (`username`),
  UNIQUE KEY `email` (`email`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Insert default admin user (password: admin123)
INSERT INTO `users` (`code`, `username`, `email`, `password`, `first_name`, `last_name`, `role_id`, `usertype`, `status`) VALUES
('USR-2025-0001', 'admin', 'admin@example.com', '$2y$10$hIB4Etgi98K5TemqY5i/AuQ2QruojMpbqMyPsUDr9ePs0Fu9Dzq7K', 'Admin', 'User', 1, 'ADMIN', 'active');

-- =====================================================
-- 2. ROLES TABLE
-- =====================================================
CREATE TABLE `roles` (
  `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT,
  `name` varchar(191) NOT NULL,
  `description` text DEFAULT NULL,
  `permissions` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `name` (`name`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO `roles` VALUES
(1, 'Admin', 'System Administrator', '{"all": true}', NOW(), NOW()),
(2, 'Manager', 'Branch Manager', '{"manage_users": true, "view_reports": true}', NOW(), NOW()),
(3, 'Accountant', 'Accountant', '{"manage_accounts": true, "view_reports": true}', NOW(), NOW()),
(4, 'User', 'Regular User', '{"view_own_data": true}', NOW(), NOW());


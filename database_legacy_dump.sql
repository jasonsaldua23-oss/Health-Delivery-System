-- phpMyAdmin SQL Dump
-- version 5.2.3
-- https://www.phpmyadmin.net/
--
-- Host: localhost
-- Generation Time: May 31, 2026 at 11:57 AM
-- Server version: 8.4.3
-- PHP Version: 8.3.30

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `health_delivery_system`
--

-- --------------------------------------------------------

--
-- Table structure for table `admin_accounts`
--

CREATE TABLE `admin_accounts` (
  `id` int UNSIGNED NOT NULL,
  `admin_name` varchar(150) COLLATE utf8mb4_unicode_ci NOT NULL,
  `office_name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `email` varchar(150) COLLATE utf8mb4_unicode_ci NOT NULL,
  `password_hash` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `admin_accounts`
--

INSERT INTO `admin_accounts` (`id`, `admin_name`, `office_name`, `email`, `password_hash`, `created_at`, `updated_at`) VALUES
(1, 'Admin User', 'Bacolod City Health Office', 'admintest@gmail.com', '$2y$10$K4OtCtO.WiGx4RHJDWspNOfBT7DGRE6fDdjjMWh0IdZOHToMEksJ.', '2026-05-03 06:05:58', '2026-05-03 06:05:58');

-- --------------------------------------------------------

--
-- Table structure for table `appointments`
--

CREATE TABLE `appointments` (
  `id` int UNSIGNED NOT NULL,
  `reference_code` varchar(20) COLLATE utf8mb4_unicode_ci NOT NULL,
  `appointment_code` varchar(10) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `patient_id` varchar(32) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `station_slug` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `station_name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `service_slug` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `service_name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `first_name` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `middle_name` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `last_name` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `birth_date` date NOT NULL,
  `gender` varchar(30) COLLATE utf8mb4_unicode_ci NOT NULL,
  `contact_number` varchar(20) COLLATE utf8mb4_unicode_ci NOT NULL,
  `email` varchar(150) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `complete_address` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `immunization_relationship` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `preferred_date` date NOT NULL,
  `preferred_time` varchar(30) COLLATE utf8mb4_unicode_ci NOT NULL,
  `notes` text COLLATE utf8mb4_unicode_ci,
  `body_temperature` varchar(30) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `pulse_rate` varchar(30) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `respiration_rate` varchar(30) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `blood_pressure` varchar(30) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `doctor_notes` text COLLATE utf8mb4_unicode_ci,
  `photo_path` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `status` varchar(30) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'Pending',
  `status_updated_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `appointments`
--

INSERT INTO `appointments` (`id`, `reference_code`, `appointment_code`, `patient_id`, `station_slug`, `station_name`, `service_slug`, `service_name`, `first_name`, `middle_name`, `last_name`, `birth_date`, `gender`, `contact_number`, `email`, `complete_address`, `immunization_relationship`, `preferred_date`, `preferred_time`, `notes`, `body_temperature`, `pulse_rate`, `respiration_rate`, `blood_pressure`, `doctor_notes`, `photo_path`, `status`, `status_updated_at`, `created_at`, `updated_at`) VALUES
(1, 'BK260520161625781', 'ELVMHSMZ', 'V84QXW', 'villa-esperanza', 'Villa Esperanza Barangay Health Station', 'nutrition', 'Nutrition Program', 'Airalene', 'Acabo', 'Rivera', '2004-05-21', 'Female', '09810066916', 'mhulaan46@gmail.com', 'Purok 10, Sitio Sangay Brgy. Purisima Manapla Negros Occ.', NULL, '2026-05-30', 'Daily Slot', 'mweeheeh', NULL, NULL, NULL, NULL, NULL, NULL, 'Pending', NULL, '2026-05-20 14:16:25', '2026-05-20 14:16:25'),
(2, 'BK260520164916118', 'GNSBRG28', 'YUDH5J', 'alijis', 'Alijis Barangay Health Station', 'pharmacy', 'Pharmacy Services', 'Baaa', 'Caaa', 'Daaa', '2004-02-02', 'Female', '09198296412', 'bcd@gmail.com', 'Purok 2, Sitio Sangay Brgy. Purisima Manapla Negros Occ.', NULL, '2026-05-21', 'Daily Slot', 'nomweheheh', '87', '90', '67.8', '120/60', 'jeje', 'uploads/patient_6a0dcd2659fab9.35983268.jpg', 'Completed', NULL, '2026-05-20 14:49:16', '2026-05-21 14:12:13'),
(3, 'BK260520165355974', '5MWMJ3ES', 'UAKZ5S', 'alijis', 'Alijis Barangay Health Station', 'pharmacy', 'Pharmacy Services', 'Hermelyn', 'Rivera', 'Acabo', '1998-05-28', 'Female', '09198296416', 'oliviaacabo71@gmail.com', 'Purok 10, Sangay Brgy. Purisima Manapla Negros Occ.', NULL, '2026-05-30', 'Daily Slot', 'mwhdhhdhd', '', '', '', '', '', NULL, 'Completed', NULL, '2026-05-20 14:53:55', '2026-05-22 04:37:44'),
(4, 'BK260520165752316', 'GYERAG5L', '4MNUC4', 'handumanan', 'Handumanan Barangay Health Station', 'checkup', 'Wellness Checkup', 'Aba', 'Santos', 'Mwhehe', '1997-05-28', 'Male', '09198296567', 'abasantos@gmail.com', 'Purok 11, Purisima Manapla Negros Occ.', NULL, '2026-05-29', 'Daily Slot', 'nnnnn', NULL, NULL, NULL, NULL, NULL, NULL, 'Pending', NULL, '2026-05-20 14:57:52', '2026-05-20 14:57:52'),
(5, 'BK260521153338944', '6F2T8EBH', 'NESFDS', 'estefania', 'Estefania Barangay Health Station', 'immunization', 'Immunization Program', 'Heheheeh', 'Nyajaaa', 'Zzzz', '1998-05-28', 'Female', '09191245678', 'melk76501@gmail.com', 'Purok 9, Sitio Sangay Brgy. Purisima Manapla Negros Occ.', NULL, '2026-05-30', 'Daily Slot', 'meekekke', NULL, NULL, NULL, NULL, NULL, 'uploads/patient_6a0f0aa3b90e44.33394911.jpg', 'Completed', NULL, '2026-05-21 13:33:38', '2026-05-21 13:37:39'),
(6, 'BK260521155101315', '8NDHPV7K', 'E2VXNR', 'alijis', 'Alijis Barangay Health Station', 'senior', 'Senior Citizen Care', 'Airalene', 'Acabo', 'Rivera', '2003-05-28', 'Female', '09810066916', 'leneaira02@gmail.com', 'Purok 3, Sitio Sangay Brgy. Purisima Manapla Negros Occ.', NULL, '2026-05-30', 'Daily Slot', 'mwheeh', '', '', '', '', '', NULL, 'Completed', NULL, '2026-05-21 13:51:01', '2026-05-22 04:37:34'),
(7, 'BK260521160832151', 'P9C5P5WK', 'HSJBPA', 'alijis', 'Alijis Barangay Health Station', 'senior', 'Senior Citizen Care', 'Cass', 'Sy', 'De', '2005-06-03', 'Female', '09810066916', 'melk76501@gmail.com', 'Purok 10, Sitio Sangay Brgy. Purisima Manapla Negros Occ.', NULL, '2026-05-22', 'Daily Slot', 'hays', NULL, NULL, NULL, NULL, NULL, NULL, 'Serving', NULL, '2026-05-21 14:08:32', '2026-05-22 04:38:05'),
(8, 'BK260521160945689', '6QFGGV7M', 'MH3DKN', 'handumanan', 'Handumanan Barangay Health Station', 'nutrition', 'Nutrition Program', 'Hermelyn', 'Rivera', 'Acabo', '2005-05-30', 'Female', '09198296412', '', 'Purok 10, Sitio Sangay Brgy. Purisima Manapla Negros Occ.', NULL, '2026-05-22', 'Daily Slot', 'mwhee', NULL, NULL, NULL, NULL, NULL, NULL, 'Pending', NULL, '2026-05-21 14:09:45', '2026-05-21 14:09:45'),
(9, 'BK260522063630934', 'CRJ2QGC3', 'E3Z7ET', 'handumanan', 'Handumanan Barangay Health Station', 'nutrition', 'Nutrition Program', 'Airalene', 'Acabo', 'Rivera', '2026-05-27', 'Female', '09810066916', 'acaboolivia@gmail.com', 'Purok 10, Sitio Sangay Brgy. Purisima Manapla Negros Occ.', NULL, '2026-05-22', 'Daily Slot', 'MWEHEHEHE', NULL, NULL, NULL, NULL, NULL, NULL, 'Pending', NULL, '2026-05-22 04:36:30', '2026-05-22 04:36:30'),
(10, 'BK260524151418985', 'BT6XJYMV', '2NZ2PP', 'cabug', 'Cabug Barangay Health Station', 'checkup', 'Wellness Checkup', 'Airalene', 'Acabo', 'Rivera', '2004-05-28', 'Female', '09810066916', 'mhulaan46@gmail.com', 'Purok 6, Sitio Sangay Brgy. Purisima Manapla Negros Occ.', NULL, '2026-05-26', 'Daily Slot', 'wmmmwwm', '', '90', '67.8', '120/60', 'cvvv', NULL, 'Completed', NULL, '2026-05-24 13:14:18', '2026-05-24 13:34:58'),
(11, 'BK260531093254901', '6VRHPVSN', 'BS9CKF', 'alijis', 'Alijis Barangay Health Station', 'consultation', 'General Consultation', 'AIRA', 'MAY', 'JUNE', '2005-11-16', 'Female', '09814357707', 'may@gmail.com', 'Alijis, St. Vincent Homes, Zone 11, Bacolod City', '', '2026-06-03', 'Daily Slot', 'Presnetttt', NULL, NULL, NULL, NULL, NULL, NULL, 'Confirmed', NULL, '2026-05-31 09:32:54', '2026-05-31 09:36:43'),
(12, 'BK260531100846674', 'VJEBEATZ', '6KDCFM', 'alijis', 'Alijis Barangay Health Station', 'family', 'Family Planning', 'May', 'June', 'July', '2003-11-13', 'Female', '09969097399', 'juneaa@gmail.com', 'Alijis, Maanyag, Prk.tagok, Bacolod City', '', '2026-06-03', 'Daily Slot', 'N/A', NULL, NULL, NULL, NULL, NULL, NULL, 'Confirmed', '2026-05-31 10:09:42', '2026-05-31 10:08:46', '2026-05-31 10:09:42'),
(13, 'BK260531111818409', 'DDKUQ8VJ', 'E4VL4X', 'bata', 'Bata Barangay Health Station', 'immunization', 'Immunization', 'Je', 'Qwq', 'Qw', '2003-02-12', 'Male', '09969097300', 'test3@gmail.com', 'Bata, Pag-isa, Bato, Bacolod City', 'Self', '2026-06-03', 'Daily Slot', 'N/A', NULL, NULL, NULL, NULL, NULL, NULL, 'Pending', NULL, '2026-05-31 11:18:18', '2026-05-31 11:18:18'),
(14, 'BK260531112418130', '5Y7MFC97', '7D2UGK', 'alijis', 'Alijis Barangay Health Station', 'consultation', 'General Consultation', 'Ble', 'Blo', 'Bellee', '2004-10-23', 'Female', '09969098300', 'blee@gmail.com', 'Alijis, Bayanihan, Street,tagok, Bacolod City', '', '2026-06-03', 'Daily Slot', 'N/A', NULL, NULL, NULL, NULL, NULL, NULL, 'Confirmed', '2026-05-31 11:24:44', '2026-05-31 11:24:18', '2026-05-31 11:24:44'),
(15, 'BK260531113442930', 'CR93WBLH', '6G6MMF', 'alijis', 'Alijis Barangay Health Station', 'consultation', 'General Consultation', 'Ttt', 'Eba', 'Tte', '2003-03-12', 'Male', '09969097300', 't@gmail.com', 'Alijis, Bayanihan, Zone 11, Bacolod City', '', '2026-06-04', 'Daily Slot', 'N/A', NULL, NULL, NULL, NULL, NULL, NULL, 'Confirmed', '2026-05-31 11:34:50', '2026-05-31 11:34:42', '2026-05-31 11:34:50'),
(16, 'BK260531114657171', 'DVZ4UB3G', '8VMZFE', 'handumanan', 'Handumanan Barangay Health Station', 'pharmacy', 'Pharmacy Services', 'Arlene', 'Baclason', 'Maming', '2004-05-06', 'Female', '09810066916', 'arlenemaming@gmail.com', 'Handumanan, Purok Ceres, Bacolod City', '', '2026-06-05', 'Daily Slot', '', NULL, NULL, NULL, NULL, NULL, NULL, 'Pending', NULL, '2026-05-31 11:46:57', '2026-05-31 11:46:57');

-- --------------------------------------------------------

--
-- Table structure for table `appointment_status_notifications`
--

CREATE TABLE `appointment_status_notifications` (
  `id` int UNSIGNED NOT NULL,
  `appointment_id` int UNSIGNED NOT NULL,
  `reference_code` varchar(32) COLLATE utf8mb4_unicode_ci NOT NULL,
  `patient_id` varchar(32) COLLATE utf8mb4_unicode_ci NOT NULL,
  `status` varchar(30) COLLATE utf8mb4_unicode_ci NOT NULL,
  `message` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `is_read` tinyint(1) DEFAULT '0',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `appointment_status_notifications`
--

INSERT INTO `appointment_status_notifications` (`id`, `appointment_id`, `reference_code`, `patient_id`, `status`, `message`, `is_read`, `created_at`) VALUES
(1, 12, 'BK260531100846674', '6KDCFM', 'Confirmed', 'Your appointment has been confirmed by Alijis Barangay Health Station.', 0, '2026-05-31 10:09:42'),
(2, 14, 'BK260531112418130', '7D2UGK', 'Confirmed', 'Your appointment has been confirmed by Alijis Barangay Health Station.', 0, '2026-05-31 11:24:44'),
(3, 15, 'BK260531113442930', '6G6MMF', 'Confirmed', 'Your appointment has been confirmed by Alijis Barangay Health Station.', 0, '2026-05-31 11:34:50');

-- --------------------------------------------------------

--
-- Table structure for table `patient_accounts`
--

CREATE TABLE `patient_accounts` (
  `id` int UNSIGNED NOT NULL,
  `patient_id` varchar(10) COLLATE utf8mb4_unicode_ci NOT NULL,
  `email` varchar(150) COLLATE utf8mb4_unicode_ci NOT NULL,
  `password_hash` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `first_name` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `middle_name` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `last_name` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `birth_date` date NOT NULL,
  `gender` varchar(30) COLLATE utf8mb4_unicode_ci NOT NULL,
  `contact_number` varchar(20) COLLATE utf8mb4_unicode_ci NOT NULL,
  `complete_address` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `station_slug` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `station_name` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `patient_accounts`
--

INSERT INTO `patient_accounts` (`id`, `patient_id`, `email`, `password_hash`, `first_name`, `middle_name`, `last_name`, `birth_date`, `gender`, `contact_number`, `complete_address`, `station_slug`, `station_name`, `created_at`, `updated_at`) VALUES
(1, 'E4VL4X', 'test3@gmail.com', '$2y$10$d0B67VJYhmrYc90UPRrUXuqSV8Z1Y5Ft6DkbRreG2mTirwtBJ0FzS', 'Je', 'Qwq', 'Qw', '2003-02-12', 'Male', '09969097300', 'Bata, Bato, Bacolod City', NULL, NULL, '2026-05-31 11:17:10', '2026-05-31 11:17:10'),
(2, '7D2UGK', 'blee@gmail.com', '$2y$10$Oujq4HXbsMHgz19zMJY86.vpJzbnSNLTLJYI6.iWL3DGiHQW98GPG', 'Ble', 'Blo', 'Bellee', '2004-10-23', 'Female', '09969098300', 'Alijis, Alijis, Bacolod City', NULL, NULL, '2026-05-31 11:23:28', '2026-05-31 11:23:28'),
(3, '6G6MMF', 't@gmail.com', '$2y$10$cGd.5MEWKLOFZGhDSsI88.Otef0VDDf7RcLRm36UyTsxWOwlm0ykW', 'Ttt', 'Eba', 'Tte', '2003-03-12', 'Male', '09969097300', 'Alijis, PUROK MAPALARON, Bacolod City', 'alijis', 'Alijis Barangay Health Station', '2026-05-31 11:33:54', '2026-05-31 11:33:54'),
(4, '47SUB2', 'airalenerivera26@gmail.com', '$2y$10$wrcO9XaJZFQyT0VYzajbnesmiL4h.HhIZyOq0aSNR5yMEONG9PpbO', 'Airalene', 'Acabo', 'Rivera', '2006-08-02', 'Female', '09105772609', 'Alijis, PUROK MAPALARON, Bacolod City', 'alijis', 'Alijis Barangay Health Station', '2026-05-31 11:41:04', '2026-05-31 11:41:04'),
(5, '8VMZFE', 'arlenemaming@gmail.com', '$2y$10$hZZ2dPaTroTCA16DXBenOOnhOPJU5b0t76jKRlhMhUV9CFDykWaxK', 'Arlene', 'Baclason', 'Maming', '2004-05-06', 'Female', '09810066916', 'Handumanan, Bacolod City', 'handumanan', 'Handumanan Barangay Health Station', '2026-05-31 11:46:24', '2026-05-31 11:46:24');

-- --------------------------------------------------------

--
-- Table structure for table `patient_info_history`
--

CREATE TABLE `patient_info_history` (
  `id` int UNSIGNED NOT NULL,
  `patient_id` varchar(32) COLLATE utf8mb4_unicode_ci NOT NULL,
  `field_name` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL,
  `old_value` text COLLATE utf8mb4_unicode_ci,
  `new_value` text COLLATE utf8mb4_unicode_ci,
  `changed_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `patient_info_history`
--

INSERT INTO `patient_info_history` (`id`, `patient_id`, `field_name`, `old_value`, `new_value`, `changed_at`) VALUES
(1, 'E4VL4X', 'complete_address', 'Bata, Bato, Bacolod City', 'Bata, Pag-isa, Bato, Bacolod City', '2026-05-31 11:18:18'),
(2, '7D2UGK', 'complete_address', 'Alijis, Alijis, Bacolod City', 'Alijis, Bayanihan, Street,tagok, Bacolod City', '2026-05-31 11:24:18'),
(3, '6G6MMF', 'complete_address', 'Alijis, PUROK MAPALARON, Bacolod City', 'Alijis, Bayanihan, Zone 11, Bacolod City', '2026-05-31 11:34:42'),
(4, '8VMZFE', 'complete_address', 'Handumanan, Bacolod City', 'Handumanan, Purok Ceres, Bacolod City', '2026-05-31 11:46:57');

-- --------------------------------------------------------

--
-- Table structure for table `patient_profiles`
--

CREATE TABLE `patient_profiles` (
  `id` int UNSIGNED NOT NULL,
  `patient_id` varchar(32) COLLATE utf8mb4_unicode_ci NOT NULL,
  `first_name` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `middle_name` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `last_name` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `birth_date` date NOT NULL,
  `gender` varchar(30) COLLATE utf8mb4_unicode_ci NOT NULL,
  `contact_number` varchar(20) COLLATE utf8mb4_unicode_ci NOT NULL,
  `email` varchar(150) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `complete_address` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `photo_path` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `patient_update_notifications`
--

CREATE TABLE `patient_update_notifications` (
  `id` int UNSIGNED NOT NULL,
  `patient_id` varchar(32) COLLATE utf8mb4_unicode_ci NOT NULL,
  `patient_name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `field_updated` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL,
  `is_read` tinyint(1) DEFAULT '0',
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `patient_update_notifications`
--

INSERT INTO `patient_update_notifications` (`id`, `patient_id`, `patient_name`, `field_updated`, `is_read`, `created_at`) VALUES
(1, 'E4VL4X', 'Je Qwq Qw', 'Address', 0, '2026-05-31 11:18:18'),
(2, '7D2UGK', 'Ble Blo Bellee', 'Address', 0, '2026-05-31 11:24:18'),
(3, '6G6MMF', 'Ttt Eba Tte', 'Address', 0, '2026-05-31 11:34:42'),
(4, '8VMZFE', 'Arlene Baclason Maming', 'Address', 0, '2026-05-31 11:46:57');

-- --------------------------------------------------------

--
-- Table structure for table `staff_accounts`
--

CREATE TABLE `staff_accounts` (
  `id` int UNSIGNED NOT NULL,
  `station_slug` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `station_name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `staff_name` varchar(150) COLLATE utf8mb4_unicode_ci NOT NULL,
  `email` varchar(150) COLLATE utf8mb4_unicode_ci NOT NULL,
  `password_hash` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `staff_accounts`
--

INSERT INTO `staff_accounts` (`id`, `station_slug`, `station_name`, `staff_name`, `email`, `password_hash`, `created_at`, `updated_at`) VALUES
(1, 'alijis', 'Alijis Barangay Health Station', 'Alijis Health Staff', 'staff.alijis@health.local', '$2y$10$krX/duSATPKdF0LwH1mXR.nvExNtPNFZpjFsSLESyT4U/RRL9aoNO', '2026-05-03 06:05:58', '2026-05-03 06:05:58'),
(2, 'bata', 'Bata Barangay Health Station', 'Bata Health Staff', 'staff.bata@health.local', '$2y$10$krX/duSATPKdF0LwH1mXR.nvExNtPNFZpjFsSLESyT4U/RRL9aoNO', '2026-05-03 06:05:58', '2026-05-03 06:05:58'),
(3, 'cabug', 'Cabug Barangay Health Station', 'Cabug Health Staff', 'staff.cabug@health.local', '$2y$10$krX/duSATPKdF0LwH1mXR.nvExNtPNFZpjFsSLESyT4U/RRL9aoNO', '2026-05-03 06:05:58', '2026-05-03 06:05:58'),
(4, 'city-health', 'Bacolod City Health Office', 'City Health Health Staff', 'staff.city-health@health.local', '$2y$10$krX/duSATPKdF0LwH1mXR.nvExNtPNFZpjFsSLESyT4U/RRL9aoNO', '2026-05-03 06:05:58', '2026-05-03 06:05:58'),
(5, 'estefania', 'Estefania Barangay Health Station', 'Estefania Health Staff', 'staff.estefania@health.local', '$2y$10$krX/duSATPKdF0LwH1mXR.nvExNtPNFZpjFsSLESyT4U/RRL9aoNO', '2026-05-03 06:05:58', '2026-05-03 06:05:58'),
(6, 'granada', 'Granada Barangay Health Station', 'Granada Health Staff', 'staff.granada@health.local', '$2y$10$krX/duSATPKdF0LwH1mXR.nvExNtPNFZpjFsSLESyT4U/RRL9aoNO', '2026-05-03 06:05:58', '2026-05-03 06:05:58'),
(7, 'handumanan', 'Handumanan Barangay Health Station', 'Handumanan Health Staff', 'staff.handumanan@health.local', '$2y$10$krX/duSATPKdF0LwH1mXR.nvExNtPNFZpjFsSLESyT4U/RRL9aoNO', '2026-05-03 06:05:58', '2026-05-03 06:05:58'),
(8, 'mandalagan', 'Mandalagan Barangay Health Station', 'Mandalagan Health Staff', 'staff.mandalagan@health.local', '$2y$10$krX/duSATPKdF0LwH1mXR.nvExNtPNFZpjFsSLESyT4U/RRL9aoNO', '2026-05-03 06:05:58', '2026-05-03 06:05:58'),
(9, 'mansilingan', 'Mansilingan Barangay Health Station', 'Mansilingan Health Staff', 'staff.mansilingan@health.local', '$2y$10$krX/duSATPKdF0LwH1mXR.nvExNtPNFZpjFsSLESyT4U/RRL9aoNO', '2026-05-03 06:05:58', '2026-05-03 06:05:58'),
(10, 'pahanocoy', 'Pahanocoy Barangay Health Station', 'Pahanocoy Health Staff', 'staff.pahanocoy@health.local', '$2y$10$krX/duSATPKdF0LwH1mXR.nvExNtPNFZpjFsSLESyT4U/RRL9aoNO', '2026-05-03 06:05:58', '2026-05-03 06:05:58'),
(11, 'singcang', 'Singcang Barangay Health Station', 'Singcang Health Staff', 'staff.singcang@health.local', '$2y$10$krX/duSATPKdF0LwH1mXR.nvExNtPNFZpjFsSLESyT4U/RRL9aoNO', '2026-05-03 06:05:58', '2026-05-03 06:05:58'),
(12, 'sum-ag', 'Sum-Ag Barangay Health Station', 'Sum-Ag Health Staff', 'staff.sum-ag@health.local', '$2y$10$krX/duSATPKdF0LwH1mXR.nvExNtPNFZpjFsSLESyT4U/RRL9aoNO', '2026-05-03 06:05:58', '2026-05-03 06:05:58'),
(13, 'taculing', 'Taculing Barangay Health Station', 'Taculing Health Staff', 'staff.taculing@health.local', '$2y$10$krX/duSATPKdF0LwH1mXR.nvExNtPNFZpjFsSLESyT4U/RRL9aoNO', '2026-05-03 06:05:58', '2026-05-03 06:05:58'),
(14, 'villamonte', 'Villamonte Barangay Health Station', 'Villamonte Health Staff', 'staff.villamonte@health.local', '$2y$10$krX/duSATPKdF0LwH1mXR.nvExNtPNFZpjFsSLESyT4U/RRL9aoNO', '2026-05-03 06:05:58', '2026-05-03 06:05:58'),
(15, 'villa-esperanza', 'Villa Esperanza Barangay Health Station', 'Villa Esperanza Health Staff', 'staff.villa-esperanza@health.local', '$2y$10$krX/duSATPKdF0LwH1mXR.nvExNtPNFZpjFsSLESyT4U/RRL9aoNO', '2026-05-03 06:05:58', '2026-05-03 06:05:58'),
(16, 'vista-alegre', 'Vista Alegre Barangay Health Station', 'Vista Alegre Health Staff', 'staff.vista-alegre@health.local', '$2y$10$krX/duSATPKdF0LwH1mXR.nvExNtPNFZpjFsSLESyT4U/RRL9aoNO', '2026-05-03 06:05:58', '2026-05-03 06:05:58');

-- --------------------------------------------------------

--
-- Table structure for table `station_open_hours`
--

CREATE TABLE `station_open_hours` (
  `station_slug` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `open_hours` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `station_open_hours`
--

INSERT INTO `station_open_hours` (`station_slug`, `open_hours`, `updated_at`) VALUES
('alijis', 'Monday - Saturday, 8:00 AM - 5:00 PM', '2026-05-31 10:55:33'),
('bata', 'Monday - Saturday, 8:00 AM - 5:00 PM', '2026-05-31 10:55:33'),
('cabug', 'Monday - Saturday, 8:00 AM - 5:00 PM', '2026-05-31 10:55:33'),
('city-health', 'Monday - Saturday, 8:00 AM - 5:00 PM', '2026-05-31 10:55:33'),
('estefania', 'Monday - Saturday, 8:00 AM - 5:00 PM', '2026-05-31 10:55:33'),
('granada', 'Monday - Saturday, 8:00 AM - 5:00 PM', '2026-05-31 10:55:33'),
('handumanan', 'Monday - Saturday, 8:00 AM - 5:00 PM', '2026-05-31 10:55:33'),
('mandalagan', 'Monday - Saturday, 8:00 AM - 5:00 PM', '2026-05-31 10:55:33'),
('mansilingan', 'Monday - Saturday, 8:00 AM - 5:00 PM', '2026-05-31 10:55:33'),
('pahanocoy', 'Monday - Saturday, 8:00 AM - 5:00 PM', '2026-05-31 10:55:33'),
('singcang', 'Monday - Saturday, 8:00 AM - 5:00 PM', '2026-05-31 10:55:33'),
('sum-ag', 'Monday - Saturday, 8:00 AM - 5:00 PM', '2026-05-31 10:55:33'),
('taculing', 'Monday - Saturday, 8:00 AM - 5:00 PM', '2026-05-31 10:55:33'),
('villa-esperanza', 'Monday - Saturday, 8:00 AM - 5:00 PM', '2026-05-31 10:55:33'),
('villamonte', 'Monday - Saturday, 8:00 AM - 5:00 PM', '2026-05-31 10:55:33'),
('vista-alegre', 'Monday - Saturday, 8:00 AM - 5:00 PM', '2026-05-31 10:55:33');

-- --------------------------------------------------------

--
-- Table structure for table `upcoming_events`
--

CREATE TABLE `upcoming_events` (
  `id` int UNSIGNED NOT NULL,
  `station_slug` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `station_name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `title` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `description` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `event_date` date NOT NULL,
  `time_label` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `end_time_label` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `icon` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'calendar',
  `accent` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'mint',
  `created_by` varchar(150) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `upcoming_events`
--

INSERT INTO `upcoming_events` (`id`, `station_slug`, `station_name`, `title`, `description`, `event_date`, `time_label`, `end_time_label`, `icon`, `accent`, `created_by`, `created_at`, `updated_at`) VALUES
(1, 'city-health', 'Bacolod City Health Office', 'Citywide Wellness Caravan', 'One-stop consultations, vital checks, and medicine counseling for walk-in residents.', '2026-05-08', '8:00 AM - 12:00 PM', NULL, 'heart', 'blue', 'system-seed', '2026-05-03 06:05:58', '2026-05-03 06:05:58'),
(2, 'bata', 'Bata Barangay Health Station', 'Child Immunization Day', 'Routine vaccines and growth monitoring for infants and young children.', '2026-05-11', '9:00 AM - 2:00 PM', NULL, 'syringe', 'blue', 'system-seed', '2026-05-03 06:05:58', '2026-05-03 06:05:58'),
(3, 'mandalagan', 'Mandalagan Barangay Health Station', 'Prenatal Checkup Morning', 'Free prenatal consultations, blood pressure screening, and nutrition guidance.', '2026-05-14', '8:30 AM - 11:30 AM', NULL, 'baby', 'pink', 'system-seed', '2026-05-03 06:05:58', '2026-05-03 06:05:58'),
(4, 'taculing', 'Taculing Barangay Health Station', 'Community Feeding Program', 'Healthy meals, nutrition assessment, and vitamins for children and seniors.', '2026-05-16', '10:00 AM - 1:00 PM', NULL, 'community', 'gold', 'system-seed', '2026-05-03 06:05:58', '2026-05-03 06:05:58'),
(5, 'singcang', 'Singcang Barangay Health Station', 'Family Planning Forum', 'Barangay-based counseling and consultations on reproductive health services.', '2026-05-19', '1:00 PM - 4:00 PM', NULL, 'heart', 'mint', 'system-seed', '2026-05-03 06:05:58', '2026-05-03 06:05:58'),
(7, 'estefania', 'Estefania Barangay Health Station', 'Nutrition Program', 'awdjdjfj', '2026-05-29', '08:00 A.M', '01:00 P.M', 'community', 'mint', 'staff.estefania@health.local', '2026-05-21 13:45:00', '2026-05-21 13:45:00');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `admin_accounts`
--
ALTER TABLE `admin_accounts`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `email` (`email`),
  ADD KEY `idx_admin_email` (`email`);

--
-- Indexes for table `appointments`
--
ALTER TABLE `appointments`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `reference_code` (`reference_code`),
  ADD KEY `idx_station_slug` (`station_slug`),
  ADD KEY `idx_service_slug` (`service_slug`),
  ADD KEY `idx_status` (`status`),
  ADD KEY `idx_preferred_date` (`preferred_date`);

--
-- Indexes for table `appointment_status_notifications`
--
ALTER TABLE `appointment_status_notifications`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_reference_code` (`reference_code`),
  ADD KEY `idx_appointment_id` (`appointment_id`),
  ADD KEY `idx_is_read` (`is_read`);

--
-- Indexes for table `patient_accounts`
--
ALTER TABLE `patient_accounts`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `patient_id` (`patient_id`),
  ADD UNIQUE KEY `email` (`email`),
  ADD KEY `idx_patient_account_email` (`email`),
  ADD KEY `idx_patient_account_patient_id` (`patient_id`);

--
-- Indexes for table `patient_info_history`
--
ALTER TABLE `patient_info_history`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_patient_history` (`patient_id`),
  ADD KEY `idx_changed_at` (`changed_at`);

--
-- Indexes for table `patient_profiles`
--
ALTER TABLE `patient_profiles`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `patient_id` (`patient_id`),
  ADD KEY `idx_patient_name` (`last_name`,`first_name`),
  ADD KEY `idx_patient_contact` (`contact_number`);

--
-- Indexes for table `patient_update_notifications`
--
ALTER TABLE `patient_update_notifications`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_patient_notif` (`patient_id`),
  ADD KEY `idx_is_read` (`is_read`),
  ADD KEY `idx_created_at` (`created_at`);

--
-- Indexes for table `staff_accounts`
--
ALTER TABLE `staff_accounts`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `station_slug` (`station_slug`),
  ADD UNIQUE KEY `email` (`email`),
  ADD KEY `idx_staff_email` (`email`);

--
-- Indexes for table `station_open_hours`
--
ALTER TABLE `station_open_hours`
  ADD PRIMARY KEY (`station_slug`);

--
-- Indexes for table `upcoming_events`
--
ALTER TABLE `upcoming_events`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_event_station` (`station_slug`),
  ADD KEY `idx_event_date` (`event_date`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `admin_accounts`
--
ALTER TABLE `admin_accounts`
  MODIFY `id` int UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=1205;

--
-- AUTO_INCREMENT for table `appointments`
--
ALTER TABLE `appointments`
  MODIFY `id` int UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=17;

--
-- AUTO_INCREMENT for table `appointment_status_notifications`
--
ALTER TABLE `appointment_status_notifications`
  MODIFY `id` int UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `patient_accounts`
--
ALTER TABLE `patient_accounts`
  MODIFY `id` int UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `patient_info_history`
--
ALTER TABLE `patient_info_history`
  MODIFY `id` int UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `patient_profiles`
--
ALTER TABLE `patient_profiles`
  MODIFY `id` int UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `patient_update_notifications`
--
ALTER TABLE `patient_update_notifications`
  MODIFY `id` int UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `staff_accounts`
--
ALTER TABLE `staff_accounts`
  MODIFY `id` int UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=19265;

--
-- AUTO_INCREMENT for table `upcoming_events`
--
ALTER TABLE `upcoming_events`
  MODIFY `id` int UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;

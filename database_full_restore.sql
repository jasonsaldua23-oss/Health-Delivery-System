-- Health Delivery System Complete Database Restore Dump
-- Generated: 2026-09-21 08:52:34
SET FOREIGN_KEY_CHECKS=0;

DROP TABLE IF EXISTS `admin_accounts`;
CREATE TABLE `admin_accounts` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `admin_name` varchar(150) NOT NULL,
  `office_name` varchar(255) NOT NULL,
  `email` varchar(150) NOT NULL,
  `contact_number` varchar(30) DEFAULT NULL,
  `recovery_email` varchar(150) DEFAULT NULL,
  `password_hash` varchar(255) NOT NULL,
  `last_active_at` timestamp NULL DEFAULT NULL,
  `is_logged_in` tinyint(1) NOT NULL DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `email` (`email`),
  KEY `idx_admin_email` (`email`)
) ENGINE=InnoDB AUTO_INCREMENT=11 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO `admin_accounts` (`id`, `admin_name`, `office_name`, `email`, `contact_number`, `recovery_email`, `password_hash`, `last_active_at`, `is_logged_in`, `created_at`, `updated_at`) VALUES
('1', 'Dr. Ma. Teresa Lim, MD', 'Central City Health Office - Bacolod', 'admintest@gmail.com', '09198765432', 'admin.personal.recovery@gmail.com', '$2y$10$3YaqO/MaW4NqdxSqGCWS9.ZbnO.lVX62VpCbcvFd0do1aHv1KVOz.', '2026-09-21 14:51:17', '1', '2026-09-01 22:02:23', '2026-09-21 14:51:17');

DROP TABLE IF EXISTS `staff_accounts`;
CREATE TABLE `staff_accounts` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `station_slug` varchar(100) NOT NULL,
  `station_name` varchar(255) NOT NULL,
  `staff_name` varchar(150) NOT NULL,
  `email` varchar(150) NOT NULL,
  `password_hash` varchar(255) NOT NULL,
  `birth_date` date DEFAULT NULL,
  `gender` varchar(30) DEFAULT NULL,
  `contact_number` varchar(30) DEFAULT NULL,
  `home_address` varchar(255) DEFAULT NULL,
  `recovery_email` varchar(150) DEFAULT NULL,
  `emergency_contact` varchar(100) DEFAULT NULL,
  `emergency_phone` varchar(30) DEFAULT NULL,
  `last_active_at` timestamp NULL DEFAULT NULL,
  `is_logged_in` tinyint(1) NOT NULL DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `email` (`email`),
  KEY `idx_station_slug` (`station_slug`),
  KEY `idx_staff_email` (`email`)
) ENGINE=InnoDB AUTO_INCREMENT=174 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO `staff_accounts` (`id`, `station_slug`, `station_name`, `staff_name`, `email`, `password_hash`, `birth_date`, `gender`, `contact_number`, `home_address`, `recovery_email`, `emergency_contact`, `emergency_phone`, `last_active_at`, `is_logged_in`, `created_at`, `updated_at`) VALUES
('1', 'alijis', 'Alijis Barangay Health Station', 'Alijis Health Staff', 'staff-alijis@alijis.health', '$2y$10$krX/duSATPKdF0LwH1mXR.nvExNtPNFZpjFsSLESyT4U/RRL9aoNO', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '0', '2026-09-01 22:02:23', '2026-09-18 13:24:23'),
('2', 'bata', 'Bata Barangay Health Station', 'Nurse Maria Santos', 'staff-bata@bata.health', '$2y$10$gbso6OPuCQNKYm3/VD.qRuTVPwC9/1EU5mnbJZvJRaD6gZvpr1e5W', '1992-08-14', 'Female', '09171234567', 'Purok Masinadyahon, Barangay Bata, Bacolod City', 'nurse.maria.recovery@gmail.com', 'Juan Santos', '09189876543', '2026-09-21 13:23:55', '1', '2026-09-01 22:02:23', '2026-09-21 13:23:55'),
('3', 'cabug', 'Cabug Barangay Health Station', 'Cabug Health Staff', 'staff-cabug@cabug.health', '$2y$10$krX/duSATPKdF0LwH1mXR.nvExNtPNFZpjFsSLESyT4U/RRL9aoNO', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '0', '2026-09-01 22:02:23', '2026-09-18 13:24:23'),
('4', 'estefania', 'Estefania Barangay Health Station', 'Estefania Health Staff', 'staff-estefania@estefania.health', '$2y$10$krX/duSATPKdF0LwH1mXR.nvExNtPNFZpjFsSLESyT4U/RRL9aoNO', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '0', '2026-09-01 22:02:23', '2026-09-18 13:24:23'),
('5', 'granada', 'Granada Barangay Health Station', 'Granada Health Staff', 'staff-granada@granada.health', '$2y$10$krX/duSATPKdF0LwH1mXR.nvExNtPNFZpjFsSLESyT4U/RRL9aoNO', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '0', '2026-09-01 22:02:23', '2026-09-18 13:24:23'),
('6', 'handumanan', 'Handumanan Barangay Health Station', 'Handumanan Health Staff', 'staff-handumanan@handumanan.health', '$2y$10$krX/duSATPKdF0LwH1mXR.nvExNtPNFZpjFsSLESyT4U/RRL9aoNO', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '0', '2026-09-01 22:02:23', '2026-09-18 13:24:23'),
('7', 'mandalagan', 'Mandalagan Barangay Health Station', 'Mandalagan Health Staff', 'staff-mandalagan@mandalagan.health', '$2y$10$krX/duSATPKdF0LwH1mXR.nvExNtPNFZpjFsSLESyT4U/RRL9aoNO', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '0', '2026-09-01 22:02:23', '2026-09-18 13:24:23'),
('8', 'mansilingan', 'Mansilingan Barangay Health Station', 'Mansilingan Health Staff', 'staff-mansilingan@mansilingan.health', '$2y$10$krX/duSATPKdF0LwH1mXR.nvExNtPNFZpjFsSLESyT4U/RRL9aoNO', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '0', '2026-09-01 22:02:23', '2026-09-18 13:24:23'),
('9', 'pahanocoy', 'Pahanocoy Barangay Health Station', 'Pahanocoy Health Staff', 'staff-pahanocoy@pahanocoy.health', '$2y$10$krX/duSATPKdF0LwH1mXR.nvExNtPNFZpjFsSLESyT4U/RRL9aoNO', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '0', '2026-09-01 22:02:23', '2026-09-18 13:24:23'),
('10', 'singcang', 'Singcang Barangay Health Station', 'Singcang Health Staff', 'staff-singcang@singcang.health', '$2y$10$krX/duSATPKdF0LwH1mXR.nvExNtPNFZpjFsSLESyT4U/RRL9aoNO', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '0', '2026-09-01 22:02:23', '2026-09-18 13:24:23'),
('11', 'sum-ag', 'Sum-Ag Barangay Health Station', 'Sum-Ag Health Staff', 'staff-sum-ag@sum-ag.health', '$2y$10$krX/duSATPKdF0LwH1mXR.nvExNtPNFZpjFsSLESyT4U/RRL9aoNO', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '0', '2026-09-01 22:02:23', '2026-09-18 13:24:23'),
('12', 'taculing', 'Taculing Barangay Health Station', 'Taculing Health Staff', 'staff-taculing@taculing.health', '$2y$10$krX/duSATPKdF0LwH1mXR.nvExNtPNFZpjFsSLESyT4U/RRL9aoNO', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '0', '2026-09-01 22:02:23', '2026-09-18 13:24:23'),
('13', 'villamonte', 'Villamonte Barangay Health Station', 'Villamonte Health Staff', 'staff-villamonte@villamonte.health', '$2y$10$krX/duSATPKdF0LwH1mXR.nvExNtPNFZpjFsSLESyT4U/RRL9aoNO', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '0', '2026-09-01 22:02:23', '2026-09-18 13:24:23'),
('14', 'villa-esperanza', 'Villa Esperanza Barangay Health Station', 'Villa Esperanza Health Staff', 'staff-villa-esperanza@villa-esperanza.health', '$2y$10$krX/duSATPKdF0LwH1mXR.nvExNtPNFZpjFsSLESyT4U/RRL9aoNO', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '0', '2026-09-01 22:02:23', '2026-09-18 13:24:23'),
('15', 'vista-alegre', 'Vista Alegre Barangay Health Station', 'Vista Alegre Health Staff', 'staff-vista-alegre@vista-alegre.health', '$2y$10$krX/duSATPKdF0LwH1mXR.nvExNtPNFZpjFsSLESyT4U/RRL9aoNO', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '0', '2026-09-01 22:02:23', '2026-09-18 13:24:23'),
('32', 'bata', 'Bata Barangay Health Station', 'Leo', 'leo@bata.health', '$2y$10$ahTLFu53NnUVPxE1QU/REOSkFbupORapFleP.re3GRbY77SVcwbZm', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '0', '2026-08-25 18:48:21', '2026-09-18 12:08:36'),
('33', 'taculing', 'Taculing Barangay Health Station', 'Maria', 'maria@taculing.health', '$2y$10$ahTLFu53NnUVPxE1QU/REOSkFbupORapFleP.re3GRbY77SVcwbZm', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '0', '2026-08-25 18:48:22', '2026-09-18 12:08:36'),
('173', 'city-health', 'Bacolod City Health Office', 'City Health Office Health Staff', 'staff-city-health@cityhealth.health', '$2y$10$krX/duSATPKdF0LwH1mXR.nvExNtPNFZpjFsSLESyT4U/RRL9aoNO', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '0', '2026-09-21 14:52:02', '2026-09-21 14:52:02');

DROP TABLE IF EXISTS `appointments`;
CREATE TABLE `appointments` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `reference_code` varchar(20) NOT NULL,
  `appointment_code` varchar(10) DEFAULT NULL,
  `patient_id` varchar(32) DEFAULT NULL,
  `station_slug` varchar(100) NOT NULL,
  `station_name` varchar(255) NOT NULL,
  `service_slug` varchar(100) NOT NULL,
  `service_name` varchar(255) NOT NULL,
  `first_name` varchar(100) NOT NULL,
  `middle_name` varchar(100) DEFAULT NULL,
  `last_name` varchar(100) NOT NULL,
  `birth_date` date NOT NULL,
  `gender` varchar(30) NOT NULL,
  `contact_number` varchar(20) NOT NULL,
  `email` varchar(150) DEFAULT NULL,
  `complete_address` varchar(255) NOT NULL,
  `immunization_relationship` varchar(100) DEFAULT NULL,
  `recipient_first_name` varchar(100) DEFAULT NULL,
  `recipient_middle_name` varchar(100) DEFAULT NULL,
  `recipient_last_name` varchar(100) DEFAULT NULL,
  `recipient_birth_date` date DEFAULT NULL,
  `recipient_gender` varchar(30) DEFAULT NULL,
  `preferred_date` date NOT NULL,
  `preferred_time` varchar(30) NOT NULL,
  `notes` text DEFAULT NULL,
  `body_temperature` varchar(30) DEFAULT NULL,
  `pulse_rate` varchar(30) DEFAULT NULL,
  `respiration_rate` varchar(30) DEFAULT NULL,
  `blood_pressure` varchar(30) DEFAULT NULL,
  `height` varchar(50) DEFAULT NULL,
  `weight` varchar(50) DEFAULT NULL,
  `vaccine_type` varchar(150) DEFAULT NULL,
  `doctor_notes` text DEFAULT NULL,
  `reminder_sms_sent` tinyint(1) NOT NULL DEFAULT 0,
  `reminder_sent_at` timestamp NULL DEFAULT NULL,
  `follow_up_date` date DEFAULT NULL,
  `follow_up_time` varchar(50) DEFAULT NULL,
  `follow_up_notes` text DEFAULT NULL,
  `follow_up_set_at` timestamp NULL DEFAULT NULL,
  `photo_path` varchar(255) DEFAULT NULL,
  `status` varchar(30) NOT NULL DEFAULT 'Pending',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `reference_code` (`reference_code`),
  KEY `idx_station_slug` (`station_slug`),
  KEY `idx_service_slug` (`service_slug`),
  KEY `idx_status` (`status`),
  KEY `idx_preferred_date` (`preferred_date`),
  KEY `idx_appt_date_status` (`preferred_date`,`status`),
  KEY `idx_appt_station_date` (`station_slug`,`preferred_date`),
  KEY `idx_appt_patient` (`patient_id`)
) ENGINE=InnoDB AUTO_INCREMENT=159 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO `appointments` (`id`, `reference_code`, `appointment_code`, `patient_id`, `station_slug`, `station_name`, `service_slug`, `service_name`, `first_name`, `middle_name`, `last_name`, `birth_date`, `gender`, `contact_number`, `email`, `complete_address`, `immunization_relationship`, `recipient_first_name`, `recipient_middle_name`, `recipient_last_name`, `recipient_birth_date`, `recipient_gender`, `preferred_date`, `preferred_time`, `notes`, `body_temperature`, `pulse_rate`, `respiration_rate`, `blood_pressure`, `height`, `weight`, `vaccine_type`, `doctor_notes`, `reminder_sms_sent`, `reminder_sent_at`, `follow_up_date`, `follow_up_time`, `follow_up_notes`, `follow_up_set_at`, `photo_path`, `status`, `created_at`, `updated_at`) VALUES
('5', 'BK260827102431721', 'ZHZS2KQN', 'P2YLL5', 'bata', 'Bata Barangay Health Station', 'consultation', 'General Consultation', 'Leo', 'Taboclaon', 'Zacarias', '2002-11-17', 'Male', '09691080024', 'leozcrs17@gmail.com', 'Purok Pag-isa, Barangay Bata, Bacolod City', '', NULL, NULL, NULL, NULL, NULL, '2026-08-28', 'Daily Slot', '', '36.7 C', '80 bpm', '18 cpm', '120/80', NULL, NULL, NULL, 'Patient reports feeling generally well; consulted for routine assessment.', '0', NULL, NULL, NULL, NULL, NULL, 'uploads/patient_6a90f892d8d3d7.53067512.jpg', 'Completed', '2026-08-27 16:24:31', '2026-09-11 13:44:09'),
('7', 'BK260827160252393', '4RFT9WC6', 'AD00A8', 'bata', 'Bata Barangay Health Station', 'dental', 'Dental Services', 'Evelyn', 'Taboclaon', 'Zacarias', '1963-12-26', 'Female', '09426388677', 'evelyntaboclaon@gmail.com', 'Purok Pag-isa, Barangay Bata, Bacolod City', '', NULL, NULL, NULL, NULL, NULL, '2026-08-28', 'Daily Slot', '', '36.7 C', '80 bpm', '18 cpm', '120/80', NULL, NULL, NULL, 'Vitals were normal, proceeded with tooth extraction.', '0', NULL, NULL, NULL, NULL, NULL, 'uploads/patient_6a9102dfd608a2.13514162.jpg', 'Completed', '2026-08-27 22:02:52', '2026-09-11 13:44:09'),
('8', 'BK260828090518936', 'D6MAZNKW', 'P2YLL5', 'bata', 'Bata Barangay Health Station', 'tb', 'TB DOTS', 'Leo', 'Taboclaon', 'Zacarias', '2002-11-17', 'Male', '09691080024', 'leozcrs17@gmail.com', 'Purok Pag-isa, Barangay Bata, Bacolod City', '', NULL, NULL, NULL, NULL, NULL, '2026-09-01', 'Daily Slot', '', '37.2', '75', '15', '120/80', NULL, NULL, NULL, 'Blah blah skrrt skrrrt. Follow up in a week.', '0', NULL, '2026-09-08', '', 'Follow-up medical assessment.', '2026-09-01 10:17:34', 'uploads/patient_6a962ef1ea9091.86311167.jpg', 'Completed', '2026-08-28 15:05:18', '2026-09-11 13:44:09'),
('120', 'REF-INF-001', 'APT-INF-00', 'P2YLL5', 'bata', 'Barangay Bata Health Center', 'immunization-services', 'National Immunization Program', 'Leo', NULL, 'Zacarias', '2002-11-17', 'Male', '09691080024', NULL, 'Purok Pag-isa, Barangay Bata, Bacolod City', 'Child', 'Baby Leo', NULL, 'Santos', '2025-11-15', NULL, '2026-09-18', '09:00 AM', NULL, '36.8', '110', '28', '85/55', NULL, NULL, 'Pentavalent (DTP-HepB-Hib)', NULL, '0', NULL, NULL, NULL, NULL, NULL, NULL, 'Completed', '2026-09-18 12:06:27', '2026-09-18 12:06:27'),
('121', 'REF-INF-002', 'APT-INF-00', 'P2YLL5', 'bata', 'Barangay Bata Health Center', 'immunization-services', 'National Immunization Program', 'Leo', NULL, 'Zacarias', '2002-11-17', 'Male', '09691080024', NULL, 'Purok Pag-isa, Barangay Bata, Bacolod City', 'Child', 'Baby Leo', NULL, 'Santos', '2025-11-15', NULL, '2026-08-10', '09:30 AM', NULL, '36.6', '112', '26', '88/56', NULL, NULL, 'Pentavalent (DTP-HepB-Hib)', NULL, '0', NULL, NULL, NULL, NULL, NULL, NULL, 'Completed', '2026-09-18 12:06:27', '2026-09-18 12:06:27'),
('122', 'REF-INF-003', 'APT-INF-00', 'P2YLL5', 'bata', 'Barangay Bata Health Center', 'immunization-services', 'National Immunization Program', 'Leo', NULL, 'Zacarias', '2002-11-17', 'Male', '09691080024', NULL, 'Purok Pag-isa, Barangay Bata, Bacolod City', 'Child', 'Baby Leo', NULL, 'Santos', '2025-11-15', NULL, '2025-12-01', '08:30 AM', NULL, '36.5', '115', '30', '80/50', NULL, NULL, 'BCG', NULL, '0', NULL, NULL, NULL, NULL, NULL, NULL, 'Completed', '2026-09-18 12:06:27', '2026-09-18 12:06:27'),
('142', 'REF-SRV-01', 'APT-SRV-01', 'P2YLL5', 'bata', 'Barangay Bata Health Center', 'immunization', 'National Immunization Program', 'Leo', NULL, 'Zacarias', '1990-01-01', 'Male', '09123456789', NULL, 'Barangay Bata, Bacolod City', 'Child', 'Baby Leo', NULL, 'Santos', '2025-11-15', NULL, '2026-09-18', '09:00 AM', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '0', NULL, NULL, NULL, NULL, NULL, NULL, 'Serving', '2026-09-18 13:50:06', '2026-09-18 13:50:07'),
('143', 'BK260520161625781', 'ELVMHSMZ', 'V84QXW', 'villa-esperanza', 'Villa Esperanza Barangay Health Station', 'nutrition', 'Nutrition Program', 'Airalene', 'Acabo', 'Rivera', '2004-05-21', 'Female', '09810066916', 'mhulaan46@gmail.com', 'Purok 10, Sitio Sangay Brgy. Purisima Manapla Negros Occ.', NULL, NULL, NULL, NULL, NULL, NULL, '2026-05-30', 'Daily Slot', 'Nutrition assessment and counseling', '36.5', '78', '18', '110/70', NULL, NULL, NULL, 'Normal nutritional parameters, dietary counseling provided.', '0', NULL, NULL, NULL, NULL, NULL, NULL, 'Completed', '2026-05-20 14:16:25', '2026-05-20 14:16:25'),
('144', 'BK260520164916118', 'GNSBRG28', 'YUDH5J', 'alijis', 'Alijis Barangay Health Station', 'pharmacy', 'Pharmacy Services', 'Maria', 'Clara', 'Dela Cruz', '2004-02-02', 'Female', '09198296412', 'mariaclara@gmail.com', 'Barangay Alijis, Bacolod City, 2', NULL, NULL, NULL, NULL, NULL, NULL, '2026-05-21', 'Daily Slot', 'Prescription maintenance refill', '36.7', '72', '16', '120/80', NULL, NULL, NULL, 'Maintenance vitamins and antacid dispensed.', '0', NULL, NULL, NULL, NULL, NULL, NULL, 'Completed', '2026-05-20 14:49:16', '2026-09-21 14:52:17'),
('145', 'BK260520165355974', '5MWMJ3ES', 'UAKZ5S', 'alijis', 'Alijis Barangay Health Station', 'pharmacy', 'Pharmacy Services', 'Hermelyn', 'Rivera', 'Acabo', '1998-05-28', 'Female', '09198296416', 'oliviaacabo71@gmail.com', 'Barangay Alijis, Bacolod City, 10', NULL, NULL, NULL, NULL, NULL, NULL, '2026-05-30', 'Daily Slot', 'Medicine dispensing', '36.6', '80', '18', '115/75', NULL, NULL, NULL, 'Paracetamol and amoxicillin dispensed per prescription.', '0', NULL, NULL, NULL, NULL, NULL, NULL, 'Completed', '2026-05-20 14:53:55', '2026-09-21 14:52:17'),
('146', 'BK260520165752316', 'GYERAG5L', '4MNUC4', 'handumanan', 'Handumanan Barangay Health Station', 'checkup', 'Wellness Checkup', 'Alexander', 'Santos', 'Villanueva', '1997-05-28', 'Male', '09198296567', 'alex.villanueva@gmail.com', 'Barangay Handumanan, Bacolod City, 11', NULL, NULL, NULL, NULL, NULL, NULL, '2026-05-29', 'Daily Slot', 'Annual adult wellness checkup', '36.8', '74', '17', '120/80', NULL, NULL, NULL, 'Routine physical exam clear. Advised on regular exercise.', '0', NULL, NULL, NULL, NULL, NULL, NULL, 'Completed', '2026-05-20 14:57:52', '2026-09-21 14:52:17'),
('147', 'BK260521153338944', '6F2T8EBH', 'NESFDS', 'estefania', 'Estefania Barangay Health Station', 'immunization', 'National Immunization Program', 'Elena', 'Zacarias', 'Montes', '1998-05-28', 'Female', '09191245678', 'elena.montes@gmail.com', 'Barangay Estefania, Bacolod City, 9', NULL, NULL, NULL, NULL, NULL, NULL, '2026-05-30', 'Daily Slot', 'Tetanus toxoid booster', '36.5', '76', '18', '110/70', NULL, NULL, NULL, 'TT vaccine administered left deltoid. No adverse reaction.', '0', NULL, NULL, NULL, NULL, NULL, NULL, 'Completed', '2026-05-21 13:33:38', '2026-09-21 14:52:17'),
('148', 'BK260521155101315', '8NDHPV7K', 'E2VXNR', 'alijis', 'Alijis Barangay Health Station', 'senior', 'Senior Citizen Care', 'Rodolfo', 'Acabo', 'Rivera', '1958-05-28', 'Male', '09810066916', 'rodolfo.rivera@gmail.com', 'Barangay Alijis, Bacolod City, 3', NULL, NULL, NULL, NULL, NULL, NULL, '2026-05-30', 'Daily Slot', 'Senior checkup & hypertension monitoring', '36.4', '68', '16', '130/85', NULL, NULL, NULL, 'Amlodipine maintenance reviewed. BP well controlled.', '0', NULL, NULL, NULL, NULL, NULL, NULL, 'Completed', '2026-05-21 13:51:01', '2026-09-21 14:52:17'),
('149', 'BK260521160832151', 'P9C5P5WK', 'HSJBPA', 'alijis', 'Alijis Barangay Health Station', 'senior', 'Senior Citizen Care', 'Cassandra', 'Sy', 'De Leon', '1961-06-03', 'Female', '09810066916', 'cass.deleon@gmail.com', 'Barangay Alijis, Bacolod City, 10', NULL, NULL, NULL, NULL, NULL, NULL, '2026-05-22', 'Daily Slot', 'Senior citizen health assessment', '36.6', '72', '18', '125/80', NULL, NULL, NULL, 'General physical exam satisfactory. Free senior vitamins provided.', '0', NULL, NULL, NULL, NULL, NULL, NULL, 'Completed', '2026-05-21 14:08:32', '2026-09-21 14:52:17'),
('150', 'BK260521160945689', '6QFGGV7M', 'MH3DKN', 'handumanan', 'Handumanan Barangay Health Station', 'nutrition', 'Nutrition Program', 'Hermelyn', 'Rivera', 'Acabo', '2005-05-30', 'Female', '09198296412', 'hermelyn.acabo@gmail.com', 'Barangay Handumanan, Bacolod City, 10', NULL, NULL, NULL, NULL, NULL, NULL, '2026-05-22', 'Daily Slot', 'Dietary counseling', '36.5', '75', '18', '110/70', NULL, NULL, NULL, 'Nutrition counseling completed.', '0', NULL, NULL, NULL, NULL, NULL, NULL, 'Completed', '2026-05-21 14:09:45', '2026-09-21 14:52:17'),
('151', 'BK260522063630934', 'CRJ2QGC3', 'E3Z7ET', 'handumanan', 'Handumanan Barangay Health Station', 'nutrition', 'Nutrition Program', 'Airalene', 'Acabo', 'Rivera', '2004-05-27', 'Female', '09810066916', 'acaboolivia@gmail.com', 'Barangay Handumanan, Bacolod City, 10', NULL, NULL, NULL, NULL, NULL, NULL, '2026-05-22', 'Daily Slot', 'Follow up nutrition check', '36.6', '78', '18', '115/75', NULL, NULL, NULL, 'Follow up completed with improvement in BMI.', '0', NULL, NULL, NULL, NULL, NULL, NULL, 'Completed', '2026-05-22 04:36:30', '2026-09-21 14:52:17'),
('152', 'BK260524151418985', 'BT6XJYMV', '2NZ2PP', 'cabug', 'Cabug Barangay Health Station', 'checkup', 'Wellness Checkup', 'Airalene', 'Acabo', 'Rivera', '2004-05-28', 'Female', '09810066916', 'mhulaan46@gmail.com', 'Barangay Cabug, Bacolod City, 6', NULL, NULL, NULL, NULL, NULL, NULL, '2026-05-26', 'Daily Slot', 'Wellness checkup', '36.7', '76', '18', '120/75', NULL, NULL, NULL, 'General physical exam normal.', '0', NULL, NULL, NULL, NULL, NULL, NULL, 'Completed', '2026-05-24 13:14:18', '2026-09-21 14:52:17'),
('153', 'BK260531093254901', '6VRHPVSN', 'BS9CKF', 'alijis', 'Alijis Barangay Health Station', 'consultation', 'General Consultation', 'Aira', 'May', 'Jun', '2004-05-28', 'Female', '09810066916', 'airamay@gmail.com', 'Barangay Alijis, Bacolod City, 12', NULL, NULL, NULL, NULL, NULL, NULL, '2026-05-30', 'Daily Slot', 'Fever and cough consultation', '37.8', '84', '20', '110/70', NULL, NULL, NULL, 'Upper respiratory tract infection. Paracetamol and rest prescribed.', '0', NULL, NULL, NULL, NULL, NULL, NULL, 'Completed', '2026-05-31 07:32:54', '2026-09-21 14:52:17'),
('154', 'BK260531100846674', 'VJEBEATZ', '6KDCFM', 'alijis', 'Alijis Barangay Health Station', 'family', 'Family Planning', 'Maya', 'June', 'Julio', '2003-12-12', 'Female', '09810066916', 'mayajune@gmail.com', 'Barangay Alijis, Bacolod City, 7', NULL, NULL, NULL, NULL, NULL, NULL, '2026-05-30', 'Daily Slot', 'Family planning counseling and pills refill', '36.5', '72', '16', '110/70', NULL, NULL, NULL, 'Oral contraceptive cycle dispensed.', '0', NULL, NULL, NULL, NULL, NULL, NULL, 'Completed', '2026-05-31 08:08:46', '2026-09-21 14:52:17'),
('155', 'BK260531111818409', 'DDKUQ8VJ', 'E4VL4X', 'bata', 'Bata Barangay Health Station', 'immunization', 'National Immunization Program', 'Jerome', 'Quinto', 'Quezon', '2003-02-12', 'Male', '09198296412', 'jerome.quezon@gmail.com', 'Barangay Bata, Bacolod City, 5', NULL, NULL, NULL, NULL, NULL, NULL, '2026-05-30', 'Daily Slot', 'Hepatitis B vaccine', '36.6', '74', '18', '120/80', NULL, NULL, NULL, 'Hep B dose 2 administered. Scheduled next appointment.', '0', NULL, NULL, NULL, NULL, NULL, NULL, 'Completed', '2026-05-31 09:18:18', '2026-09-21 14:52:17'),
('156', 'BK260531112418130', '5Y7MFC97', '7D2UGK', 'alijis', 'Alijis Barangay Health Station', 'consultation', 'General Consultation', 'Bella', 'Lourdes', 'Beltran', '2003-05-28', 'Female', '09810066916', 'bella.beltran@gmail.com', 'Barangay Alijis, Bacolod City, 8', NULL, NULL, NULL, NULL, NULL, NULL, '2026-05-30', 'Daily Slot', 'Skin rash consultation', '36.7', '76', '18', '115/75', NULL, NULL, NULL, 'Contact dermatitis. Topical hydrocortisone cream prescribed.', '0', NULL, NULL, NULL, NULL, NULL, NULL, 'Completed', '2026-05-31 09:24:18', '2026-09-21 14:52:17'),
('157', 'BK260531113442930', 'CR93WBLH', '6G6MMF', 'alijis', 'Alijis Barangay Health Station', 'consultation', 'General Consultation', 'Teresa', 'Eba', 'Torres', '2003-05-28', 'Female', '09810066916', 'teresa.torres@gmail.com', 'Barangay Alijis, Bacolod City, 11', NULL, NULL, NULL, NULL, NULL, NULL, '2026-05-30', 'Daily Slot', 'General checkup', '36.5', '72', '16', '110/70', NULL, NULL, NULL, 'Normal clinical findings.', '0', NULL, NULL, NULL, NULL, NULL, NULL, 'Completed', '2026-05-31 09:34:42', '2026-09-21 14:52:17'),
('158', 'BK260531114657171', 'DVZ4UB3G', '8VMZFE', 'handumanan', 'Handumanan Barangay Health Station', 'pharmacy', 'Pharmacy Services', 'Arlene', 'Baclaso', 'Ariel', '2004-05-28', 'Female', '09810066916', 'arlene.ariel@gmail.com', 'Barangay Handumanan, Bacolod City, 12', NULL, NULL, NULL, NULL, NULL, NULL, '2026-05-30', 'Daily Slot', 'Free government vitamins pickup', '36.6', '75', '18', '115/75', NULL, NULL, NULL, 'Monthly vitamin C and multivitamins dispensed.', '0', NULL, NULL, NULL, NULL, NULL, NULL, 'Completed', '2026-05-31 09:46:57', '2026-09-21 14:52:17');

DROP TABLE IF EXISTS `patient_accounts`;
CREATE TABLE `patient_accounts` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `patient_id` varchar(32) NOT NULL,
  `email` varchar(150) NOT NULL,
  `password_hash` varchar(255) NOT NULL,
  `first_name` varchar(100) NOT NULL,
  `middle_name` varchar(100) DEFAULT NULL,
  `last_name` varchar(100) NOT NULL,
  `birth_date` date NOT NULL,
  `gender` varchar(30) NOT NULL,
  `contact_number` varchar(20) NOT NULL,
  `complete_address` varchar(255) NOT NULL,
  `station_slug` varchar(100) DEFAULT NULL,
  `station_name` varchar(255) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `patient_id` (`patient_id`),
  UNIQUE KEY `email` (`email`),
  KEY `idx_patient_email` (`email`),
  KEY `idx_patient_name` (`last_name`,`first_name`),
  KEY `idx_pat_email` (`email`),
  KEY `idx_pat_id` (`patient_id`)
) ENGINE=InnoDB AUTO_INCREMENT=38 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO `patient_accounts` (`id`, `patient_id`, `email`, `password_hash`, `first_name`, `middle_name`, `last_name`, `birth_date`, `gender`, `contact_number`, `complete_address`, `station_slug`, `station_name`, `created_at`, `updated_at`) VALUES
('1', 'P2YLL5', 'leozcrs17@gmail.com', '$2y$10$E8M51UDQ9pkwSyMo7s7JLucW4nkwE7n3.RNq9wWIQvTyLTUUJfkFy', 'Leo', 'Taboclaon', 'Zacarias', '2002-11-17', 'Male', '09691080024', 'Purok Pag-isa, Barangay Bata, Bacolod City', 'bata', 'Bata', '2026-09-01 22:02:23', '2026-09-11 13:44:09'),
('2', '3ACU9D', 'evelyn123@gmail.com', '$2y$10$fRVDb1VQI8RyzYWCyJ23z.GfP/BwNShynWrvB1YTXhowK0td9y1.G', 'Evelyn', 'Taboclaon', 'Zacarias', '1963-12-26', 'Female', '09426388677', 'Purok Pag-isa, Barangay Bata, Bacolod City', 'bata', 'Bata Barangay Health Station', '2026-09-01 22:02:23', '2026-09-11 13:44:09'),
('4', 'D369A7', 'jorillabrian@gmail.com', '$2y$10$pq9UWClw9OEyNUgbeKFgcuuZmAiAit4Pb/LAl2.rAknavJYxrjpja', 'Brian', 'Sotabento', 'Jorilla', '2004-10-16', 'Male', '09674306281', 'Purok Pag-isa, Barangay Bata, Bacolod City, 10', 'bata', 'Bata', '2026-08-26 15:13:44', '2026-09-11 13:44:09'),
('6', 'AD00A8', 'evelyntaboclaon@gmail.com', '$2y$10$mdgIO2H9c/YCPZ3QMGye/.DFORHIrJzVHFN6IdUaD5kfcwF.iFvfS', 'Evelyn', 'Taboclaon', 'Zacarias', '1963-12-26', 'Female', '09426388677', 'Purok Pag-isa, Barangay Bata, Bacolod City', 'bata', 'Bata Barangay Health Station', '2026-08-27 22:01:09', '2026-09-11 13:44:09'),
('13', '8A13C4', 'aleh@gmail.com', '$2y$10$L4FFnbR7iCji2dnF2W1iEueH2z7./C83BgdtmxHRyJnUSN5Mw7rvy', 'Oel', 'Baero', 'laehuj', '2026-09-01', 'Male', '0968ldji186568', 'Purok Pag-isa, Barangay Bata, Bacolod City', 'bata', 'Bata Barangay Health Station', '2026-09-01 13:45:41', '2026-09-11 13:44:09'),
('28', '02DC2A', 'mekai123@gmail.com', '$2y$10$N9PvaWZWelhvLOGniVkkFe.XIsMjGyciaxWR9Ucr5x44MTOygcyju', 'Mekai', 'Villegas', 'Buenas', '2003-12-03', 'Female', '09665600834', 'Purok Bulak, Barangay Mandalagan, Bacolod City', 'mandalagan', 'Mandalagan Barangay Health Station', '2026-09-02 00:31:01', '2026-09-11 13:44:09'),
('33', 'TPAT01', 'test.patient.otp@gmail.com', '$2y$10$jkWUENIsgt5wjpHfTbQOeuGry1HT.9IC4cEcEzUSswB.YdE2c71i.', 'Maria', '', 'Santos', '1995-05-15', 'Female', '09171234567', 'Purok Masinadyahon, Barangay Bata, Bacolod City', 'bata', 'Bata Barangay Health Station', '2026-09-18 09:44:40', '2026-09-21 12:13:22');

DROP TABLE IF EXISTS `patient_profiles`;
CREATE TABLE `patient_profiles` (
  `patient_id` varchar(32) NOT NULL,
  `first_name` varchar(100) NOT NULL,
  `middle_name` varchar(100) DEFAULT NULL,
  `last_name` varchar(100) NOT NULL,
  `birth_date` date NOT NULL,
  `gender` varchar(30) NOT NULL,
  `contact_number` varchar(20) NOT NULL,
  `email` varchar(150) DEFAULT NULL,
  `complete_address` varchar(255) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`patient_id`),
  KEY `idx_patient_name` (`last_name`,`first_name`),
  KEY `idx_patient_contact` (`contact_number`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO `patient_profiles` (`patient_id`, `first_name`, `middle_name`, `last_name`, `birth_date`, `gender`, `contact_number`, `email`, `complete_address`, `created_at`, `updated_at`) VALUES
('02DC2A', 'Mekai', 'Villegas', 'Buenas', '2003-12-03', 'Female', '09665600834', 'mekai123@gmail.com', 'Purok Bulak, Barangay Mandalagan, Bacolod City', '2026-09-02 00:31:01', '2026-09-11 13:44:09'),
('3ACU9D', 'Evelyn', 'Taboclaon', 'Zacarias', '1963-12-26', 'Female', '09426388677', 'evelyn123@gmail.com', 'Purok Pag-isa, Barangay Bata, Bacolod City', '2026-06-01 20:31:52', '2026-09-11 13:44:09'),
('8A13C4', 'Oel', 'Baero', 'laehuj', '2026-09-01', 'Male', '0968ldji186568', 'aleh@gmail.com', 'Purok Pag-isa, Barangay Bata, Bacolod City', '2026-09-01 13:45:41', '2026-09-11 13:44:09'),
('AD00A8', 'Evelyn', 'Taboclaon', 'Zacarias', '1963-12-26', 'Female', '09426388677', 'evelyntaboclaon@gmail.com', 'Purok Pag-isa, Barangay Bata, Bacolod City', '2026-08-27 22:01:09', '2026-09-11 13:44:09'),
('D369A7', 'Brian', 'Sotabento', 'Jorilla', '2004-10-16', 'Male', '09674306281', 'jorillabrian@gmail.com', 'Purok Pag-isa, Barangay Bata, Bacolod City, 10', '2026-08-26 15:15:33', '2026-09-11 13:44:09'),
('D72U7F', 'Mekai', 'Santos', 'Dela Cruz', '2000-04-12', 'Female', '09123456789', 'mekus2@gmail.com', 'Purok Pag-isa, Barangay Bata, Bacolod City', '2026-06-01 20:31:52', '2026-09-11 13:44:09'),
('MAAMS7', 'Katrina', 'Soberano', 'Fajardo', '2000-04-11', 'Female', '09397567456', 'kat2@gmail.com', 'Purok Bulak, Barangay Mandalagan, Bacolod City', '2026-06-02 09:01:17', '2026-09-11 13:44:09'),
('NXKPF5', 'Liza', 'Suplada', 'Tagamolila', '2000-05-25', 'Female', '09642834596', 'urzang@gmail.com', 'Purok Pag-isa, Barangay Bata, Bacolod City', '2026-06-02 08:32:11', '2026-09-11 13:44:09'),
('P2YLL5', 'Leo', 'Taboclaon', 'Zacarias', '2002-11-17', 'Male', '09691080024', 'leozcrs17@gmail.com', 'Purok Pag-isa, Barangay Bata, Bacolod City', '2026-06-01 23:19:22', '2026-09-11 13:44:09');

DROP TABLE IF EXISTS `patient_info_history`;
CREATE TABLE `patient_info_history` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `patient_id` varchar(32) NOT NULL,
  `field_name` varchar(50) NOT NULL,
  `old_value` text DEFAULT NULL,
  `new_value` text DEFAULT NULL,
  `changed_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `idx_patient_history` (`patient_id`),
  KEY `idx_changed_at` (`changed_at`)
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

DROP TABLE IF EXISTS `upcoming_events`;
CREATE TABLE `upcoming_events` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `station_slug` varchar(100) NOT NULL,
  `station_name` varchar(255) NOT NULL,
  `title` varchar(255) NOT NULL,
  `description` text NOT NULL,
  `target_month` varchar(20) DEFAULT NULL,
  `event_date` date DEFAULT NULL,
  `time_label` varchar(100) NOT NULL,
  `end_time_label` varchar(100) DEFAULT NULL,
  `icon` varchar(50) NOT NULL DEFAULT 'calendar',
  `accent` varchar(50) NOT NULL DEFAULT 'mint',
  `status` varchar(20) NOT NULL DEFAULT 'active',
  `created_by` varchar(150) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `idx_event_station` (`station_slug`),
  KEY `idx_event_date` (`event_date`),
  KEY `idx_event_status` (`status`),
  KEY `idx_event_target_month` (`target_month`)
) ENGINE=InnoDB AUTO_INCREMENT=15 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO `upcoming_events` (`id`, `station_slug`, `station_name`, `title`, `description`, `target_month`, `event_date`, `time_label`, `end_time_label`, `icon`, `accent`, `status`, `created_by`, `created_at`, `updated_at`) VALUES
('6', 'bata', 'Bata Barangay Health Station', 'General Check up for Senior Citizens', 'Free Medical Consultation for the elders of Barangay Bata. Please bring valid IDs for identification. See you at Marapara Golf Gymnasium!', NULL, '2026-10-14', '9:00 AM', '3:00 PM', 'heart', 'blue', 'active', 'staff-bata@bata.health', '2026-08-26 12:06:12', '2026-08-26 12:06:12'),
('9', 'all', '', 'City-Wide Polio & Measles Immunization Campaign', 'Free pediatric vaccines and vitamin supplementation for infants and children aged 0-5 years across all barangay stations.', '2026-10', '2026-10-15', '8:00 AM', '4:00 PM', 'syringe', 'blue', 'active', 'admintest@gmail.com', '2026-09-21 14:52:02', '2026-09-21 14:52:02'),
('10', 'alijis', '', 'Barangay Alijis Community Dental Mission', 'Free dental consultation, tooth extraction, and oral health awareness seminar for residents.', '2026-10', '2026-10-20', '8:30 AM', '12:00 PM', 'tooth', 'green', 'active', 'admintest@gmail.com', '2026-09-21 14:52:02', '2026-09-21 14:52:02'),
('11', 'handumanan', '', 'Maternal & Prenatal Health Workshop', 'Educational session and free prenatal ultrasound screening vouchers for expectant mothers.', '2026-10', '2026-10-25', '9:00 AM', '2:00 PM', 'heart', 'rose', 'active', 'admintest@gmail.com', '2026-09-21 14:52:02', '2026-09-21 14:52:02'),
('12', 'bata', '', 'Senior Citizens Cardiovascular Wellness Day', 'Blood sugar check, ECG screening, and maintenance medicine distribution for senior residents.', '2026-11', '2026-11-05', '8:00 AM', '1:00 PM', 'pulse', 'amber', 'active', 'admintest@gmail.com', '2026-09-21 14:52:02', '2026-09-21 14:52:02'),
('13', 'cabug', '', 'TB DOTS Awareness & Sputum Testing Drive', 'Free chest health screening and community tuberculosis information campaign.', '2026-11', '2026-11-12', '8:30 AM', '3:00 PM', 'stethoscope', 'cyan', 'active', 'admintest@gmail.com', '2026-09-21 14:52:02', '2026-09-21 14:52:02'),
('14', 'villamonte', '', 'Barangay Nutrition & Deworming Drive', 'Supplementary feeding orientation, height/weight measurement, and deworming for school children.', '2026-11', '2026-11-18', '9:00 AM', '12:00 PM', 'baby', 'indigo', 'active', 'admintest@gmail.com', '2026-09-21 14:52:02', '2026-09-21 14:52:02');

DROP TABLE IF EXISTS `appointment_status_notifications`;
CREATE TABLE `appointment_status_notifications` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `appointment_id` int(10) unsigned NOT NULL,
  `reference_code` varchar(32) NOT NULL,
  `patient_id` varchar(32) NOT NULL,
  `status` varchar(30) NOT NULL,
  `message` text NOT NULL,
  `is_read` tinyint(1) NOT NULL DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `idx_notification_patient` (`patient_id`),
  KEY `idx_notification_appointment` (`appointment_id`),
  KEY `idx_notification_read` (`is_read`)
) ENGINE=InnoDB AUTO_INCREMENT=7 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO `appointment_status_notifications` (`id`, `appointment_id`, `reference_code`, `patient_id`, `status`, `message`, `is_read`, `created_at`) VALUES
('1', '8', 'D6MAZNKW', 'P2YLL5', 'Follow-up', 'Follow-up Check-up Scheduled: You have an upcoming follow-up consultation for TB DOTS at Bata Barangay Health Station on September 8, 2026. Reason / Notes: Follow-up medical assessment.', '1', '2026-09-01 10:17:34');

DROP TABLE IF EXISTS `patient_update_notifications`;
CREATE TABLE `patient_update_notifications` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `patient_id` varchar(32) NOT NULL,
  `patient_name` varchar(255) NOT NULL,
  `field_updated` varchar(50) NOT NULL,
  `is_read` tinyint(1) DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `idx_patient_notif` (`patient_id`),
  KEY `idx_is_read` (`is_read`),
  KEY `idx_created_at` (`created_at`)
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

SET FOREIGN_KEY_CHECKS=1;

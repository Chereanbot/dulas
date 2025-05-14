-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: May 14, 2025 at 06:58 PM
-- Server version: 10.4.28-MariaDB
-- PHP Version: 8.2.4

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `dulas`
--

-- --------------------------------------------------------

--
-- Table structure for table `activities`
--

CREATE TABLE `activities` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `action` varchar(255) NOT NULL,
  `details` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`details`)),
  `ip_address` varchar(45) DEFAULT NULL,
  `user_agent` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `agent_chats`
--

CREATE TABLE `agent_chats` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `agent_id` varchar(100) NOT NULL,
  `session_id` varchar(255) NOT NULL,
  `status` enum('ACTIVE','CLOSED','TRANSFERRED') DEFAULT 'ACTIVE',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `agent_notifications`
--

CREATE TABLE `agent_notifications` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `title` varchar(255) NOT NULL,
  `message` text NOT NULL,
  `type` enum('INFO','WARNING','ERROR','SUCCESS') DEFAULT 'INFO',
  `status` enum('UNREAD','READ','ARCHIVED') DEFAULT 'UNREAD',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `appointments`
--

CREATE TABLE `appointments` (
  `id` int(11) NOT NULL,
  `client_id` int(11) NOT NULL,
  `coordinator_id` int(11) NOT NULL,
  `title` varchar(255) NOT NULL,
  `description` text DEFAULT NULL,
  `start_time` datetime NOT NULL,
  `end_time` datetime NOT NULL,
  `status` enum('SCHEDULED','CONFIRMED','CANCELLED','COMPLETED','NO_SHOW') DEFAULT 'SCHEDULED',
  `type` enum('INITIAL_CONSULTATION','FOLLOW_UP','DOCUMENT_REVIEW','COURT_HEARING','OTHER') NOT NULL,
  `location` varchar(255) DEFAULT NULL,
  `notes` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `audit_logs`
--

CREATE TABLE `audit_logs` (
  `id` int(11) NOT NULL,
  `user_id` int(11) DEFAULT NULL,
  `action` varchar(255) NOT NULL,
  `entity_type` varchar(50) DEFAULT NULL,
  `entity_id` int(11) DEFAULT NULL,
  `details` text DEFAULT NULL,
  `ip_address` varchar(45) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `backups`
--

CREATE TABLE `backups` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `backup_type` enum('FULL','INCREMENTAL','DIFFERENTIAL') NOT NULL,
  `file_path` varchar(255) NOT NULL,
  `size` int(11) NOT NULL,
  `status` enum('PENDING','IN_PROGRESS','COMPLETED','FAILED') DEFAULT 'PENDING',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `completed_at` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `billing`
--

CREATE TABLE `billing` (
  `id` int(11) NOT NULL,
  `case_id` int(11) DEFAULT NULL,
  `client_id` int(11) DEFAULT NULL,
  `amount` decimal(10,2) NOT NULL,
  `description` text DEFAULT NULL,
  `status` enum('pending','paid','overdue') DEFAULT 'pending',
  `due_date` date DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `block_records`
--

CREATE TABLE `block_records` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `action` enum('block','ban') NOT NULL,
  `reason` text NOT NULL,
  `expires_at` datetime DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `calendar_events`
--

CREATE TABLE `calendar_events` (
  `id` int(11) NOT NULL,
  `title` varchar(255) NOT NULL,
  `description` text DEFAULT NULL,
  `event_type` enum('hearing','meeting','deadline','other') NOT NULL,
  `start_datetime` datetime NOT NULL,
  `end_datetime` datetime NOT NULL,
  `case_id` int(11) DEFAULT NULL,
  `created_by` int(11) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `cases`
--

CREATE TABLE `cases` (
  `id` int(11) NOT NULL,
  `case_number` varchar(50) NOT NULL,
  `title` varchar(255) NOT NULL,
  `description` text DEFAULT NULL,
  `case_type` varchar(100) DEFAULT NULL,
  `status` enum('open','pending','closed') DEFAULT 'open',
  `client_id` int(11) DEFAULT NULL,
  `lawyer_id` int(11) DEFAULT NULL,
  `paralegal_id` int(11) DEFAULT NULL,
  `court_name` varchar(100) DEFAULT NULL,
  `filing_date` date DEFAULT NULL,
  `next_hearing_date` date DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `office_id` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `case_activities`
--

CREATE TABLE `case_activities` (
  `id` int(11) NOT NULL,
  `case_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `title` varchar(255) NOT NULL,
  `description` text NOT NULL,
  `type` varchar(100) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `case_assignments`
--

CREATE TABLE `case_assignments` (
  `id` int(11) NOT NULL,
  `case_id` int(11) NOT NULL,
  `assigned_by_id` int(11) NOT NULL,
  `assigned_to_id` int(11) NOT NULL,
  `status` enum('PENDING','ACCEPTED','REJECTED','COMPLETED') DEFAULT 'PENDING',
  `notes` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `case_documents`
--

CREATE TABLE `case_documents` (
  `id` int(11) NOT NULL,
  `case_id` int(11) NOT NULL,
  `document_name` varchar(255) NOT NULL,
  `document_type` varchar(100) DEFAULT NULL,
  `file_path` varchar(255) NOT NULL,
  `uploaded_by` int(11) NOT NULL,
  `upload_date` timestamp NOT NULL DEFAULT current_timestamp(),
  `description` text DEFAULT NULL,
  `status` enum('active','archived') DEFAULT 'active'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `case_notes`
--

CREATE TABLE `case_notes` (
  `id` int(11) NOT NULL,
  `case_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `note_text` text NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `clients`
--

CREATE TABLE `clients` (
  `id` int(11) NOT NULL,
  `name` varchar(255) NOT NULL,
  `email` varchar(255) DEFAULT NULL,
  `phone` varchar(50) DEFAULT NULL,
  `address` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `client_profiles`
--

CREATE TABLE `client_profiles` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `date_of_birth` date DEFAULT NULL,
  `gender` enum('MALE','FEMALE','OTHER') DEFAULT NULL,
  `address` text DEFAULT NULL,
  `city` varchar(100) DEFAULT NULL,
  `state` varchar(100) DEFAULT NULL,
  `country` varchar(100) DEFAULT NULL,
  `postal_code` varchar(20) DEFAULT NULL,
  `preferred_contact_method` enum('EMAIL','PHONE','SMS') DEFAULT 'EMAIL',
  `preferred_language` varchar(50) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `office_id` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `coordinators`
--

CREATE TABLE `coordinators` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `type` enum('FULL_TIME','PART_TIME') NOT NULL,
  `office_id` int(11) NOT NULL,
  `start_date` date NOT NULL,
  `end_date` date DEFAULT NULL,
  `specialties` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`specialties`)),
  `status` enum('PENDING','ACTIVE','INACTIVE','SUSPENDED') DEFAULT 'PENDING',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `coordinator_history`
--

CREATE TABLE `coordinator_history` (
  `id` int(11) NOT NULL,
  `coordinator_id` int(11) NOT NULL,
  `client_id` int(11) NOT NULL,
  `lawyer_id` int(11) NOT NULL,
  `action` varchar(255) NOT NULL,
  `details` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `documents`
--

CREATE TABLE `documents` (
  `id` int(11) NOT NULL,
  `title` varchar(255) NOT NULL,
  `description` text DEFAULT NULL,
  `file_path` varchar(255) NOT NULL,
  `file_type` varchar(50) DEFAULT NULL,
  `case_id` int(11) DEFAULT NULL,
  `uploaded_by` int(11) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `events`
--

CREATE TABLE `events` (
  `id` int(11) NOT NULL,
  `title` varchar(255) NOT NULL,
  `description` text DEFAULT NULL,
  `start_time` datetime NOT NULL,
  `end_time` datetime NOT NULL,
  `location` varchar(255) DEFAULT NULL,
  `type` enum('COURT_HEARING','CLIENT_MEETING','DEADLINE','TASK','OTHER') NOT NULL,
  `status` enum('SCHEDULED','IN_PROGRESS','COMPLETED','CANCELLED') DEFAULT 'SCHEDULED',
  `priority` enum('LOW','MEDIUM','HIGH','URGENT') DEFAULT 'MEDIUM',
  `created_by` int(11) NOT NULL,
  `case_id` int(11) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `event_participants`
--

CREATE TABLE `event_participants` (
  `id` int(11) NOT NULL,
  `event_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `role` enum('ORGANIZER','ATTENDEE','GUEST') DEFAULT 'ATTENDEE',
  `status` enum('PENDING','ACCEPTED','DECLINED','TENTATIVE') DEFAULT 'PENDING',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `lawyer_appointments`
--

CREATE TABLE `lawyer_appointments` (
  `id` int(11) NOT NULL,
  `lawyer_id` int(11) NOT NULL,
  `client_id` int(11) NOT NULL,
  `case_id` int(11) DEFAULT NULL,
  `appointment_type` enum('consultation','meeting','court_hearing','other') NOT NULL,
  `title` varchar(255) NOT NULL,
  `description` text DEFAULT NULL,
  `start_datetime` datetime NOT NULL,
  `end_datetime` datetime NOT NULL,
  `status` enum('scheduled','completed','cancelled','rescheduled') DEFAULT 'scheduled',
  `location` varchar(255) DEFAULT NULL,
  `notes` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `lawyer_availability`
--

CREATE TABLE `lawyer_availability` (
  `id` int(11) NOT NULL,
  `lawyer_id` int(11) NOT NULL,
  `day_of_week` enum('monday','tuesday','wednesday','thursday','friday','saturday','sunday') DEFAULT NULL,
  `start_time` time DEFAULT NULL,
  `end_time` time DEFAULT NULL,
  `is_available` tinyint(1) DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `lawyer_billing`
--

CREATE TABLE `lawyer_billing` (
  `id` int(11) NOT NULL,
  `lawyer_id` int(11) NOT NULL,
  `case_id` int(11) NOT NULL,
  `client_id` int(11) NOT NULL,
  `amount` decimal(10,2) NOT NULL,
  `billing_type` enum('hourly','fixed','contingency') NOT NULL,
  `hours_billed` decimal(10,2) DEFAULT NULL,
  `rate_per_hour` decimal(10,2) DEFAULT NULL,
  `description` text DEFAULT NULL,
  `status` enum('pending','paid','overdue') DEFAULT 'pending',
  `due_date` date DEFAULT NULL,
  `payment_date` date DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `lawyer_cases`
--

CREATE TABLE `lawyer_cases` (
  `id` int(11) NOT NULL,
  `lawyer_id` int(11) NOT NULL,
  `case_id` int(11) NOT NULL,
  `role` enum('primary','secondary','consultant') DEFAULT 'primary',
  `start_date` date DEFAULT NULL,
  `end_date` date DEFAULT NULL,
  `status` enum('active','completed','transferred') DEFAULT 'active',
  `notes` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `lawyer_documents`
--

CREATE TABLE `lawyer_documents` (
  `id` int(11) NOT NULL,
  `lawyer_id` int(11) NOT NULL,
  `document_type` enum('certification','license','resume','other') NOT NULL,
  `title` varchar(255) NOT NULL,
  `file_path` varchar(255) NOT NULL,
  `file_type` varchar(50) DEFAULT NULL,
  `upload_date` date DEFAULT NULL,
  `expiry_date` date DEFAULT NULL,
  `status` enum('active','expired','pending') DEFAULT 'active',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `lawyer_profiles`
--

CREATE TABLE `lawyer_profiles` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `bar_number` varchar(50) DEFAULT NULL,
  `years_of_experience` int(11) DEFAULT NULL,
  `education` text DEFAULT NULL,
  `bio` text DEFAULT NULL,
  `hourly_rate` decimal(10,2) DEFAULT NULL,
  `consultation_fee` decimal(10,2) DEFAULT NULL,
  `max_cases` int(11) DEFAULT 10,
  `current_cases` int(11) DEFAULT 0,
  `availability_status` enum('available','busy','unavailable') DEFAULT 'available',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `office_id` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `lawyer_ratings`
--

CREATE TABLE `lawyer_ratings` (
  `id` int(11) NOT NULL,
  `lawyer_id` int(11) NOT NULL,
  `client_id` int(11) NOT NULL,
  `case_id` int(11) NOT NULL,
  `rating` int(11) DEFAULT NULL CHECK (`rating` >= 1 and `rating` <= 5),
  `review` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `lawyer_specializations`
--

CREATE TABLE `lawyer_specializations` (
  `id` int(11) NOT NULL,
  `lawyer_id` int(11) NOT NULL,
  `specialization` varchar(100) NOT NULL,
  `years_experience` int(11) DEFAULT NULL,
  `certification` varchar(255) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `lawyer_specialties`
--

CREATE TABLE `lawyer_specialties` (
  `id` int(11) NOT NULL,
  `lawyer_id` int(11) NOT NULL,
  `specialty_id` int(11) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `lawyer_specialties`
--

INSERT INTO `lawyer_specialties` (`id`, `lawyer_id`, `specialty_id`, `created_at`) VALUES
(1, 3, 2, '2025-05-14 11:24:07'),
(2, 3, 3, '2025-05-14 11:24:07'),
(3, 3, 4, '2025-05-14 11:24:07');

-- --------------------------------------------------------

--
-- Table structure for table `lawyer_workload`
--

CREATE TABLE `lawyer_workload` (
  `id` int(11) NOT NULL,
  `lawyer_id` int(11) NOT NULL,
  `case_id` int(11) NOT NULL,
  `hours_spent` decimal(10,2) DEFAULT 0.00,
  `status` enum('active','completed','on_hold') DEFAULT 'active',
  `priority` enum('low','medium','high') DEFAULT 'medium',
  `start_date` date DEFAULT NULL,
  `estimated_completion_date` date DEFAULT NULL,
  `actual_completion_date` date DEFAULT NULL,
  `notes` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `legal_resources`
--

CREATE TABLE `legal_resources` (
  `id` int(11) NOT NULL,
  `title` varchar(255) NOT NULL,
  `description` text DEFAULT NULL,
  `type` enum('DOCUMENT','TEMPLATE','GUIDE','REFERENCE','OTHER') NOT NULL,
  `category` varchar(100) DEFAULT NULL,
  `tags` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`tags`)),
  `file_path` varchar(255) DEFAULT NULL,
  `file_type` varchar(100) DEFAULT NULL,
  `file_size` int(11) DEFAULT NULL,
  `created_by` int(11) NOT NULL,
  `status` enum('DRAFT','PUBLISHED','ARCHIVED') DEFAULT 'DRAFT',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `office_id` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `messages`
--

CREATE TABLE `messages` (
  `id` int(11) NOT NULL,
  `sender_id` int(11) NOT NULL,
  `recipient_id` int(11) NOT NULL,
  `content` text NOT NULL,
  `type` enum('TEXT','IMAGE','FILE','AUDIO','VIDEO') DEFAULT 'TEXT',
  `status` enum('SENT','DELIVERED','READ','FAILED') DEFAULT 'SENT',
  `metadata` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`metadata`)),
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `message_reactions`
--

CREATE TABLE `message_reactions` (
  `id` int(11) NOT NULL,
  `message_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `reaction` varchar(50) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `message_threads`
--

CREATE TABLE `message_threads` (
  `id` int(11) NOT NULL,
  `title` varchar(255) DEFAULT NULL,
  `type` enum('DIRECT','GROUP') DEFAULT 'DIRECT',
  `created_by` int(11) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `notifications`
--

CREATE TABLE `notifications` (
  `id` int(11) NOT NULL,
  `user_id` int(11) DEFAULT NULL,
  `title` varchar(255) NOT NULL,
  `message` text NOT NULL,
  `type` enum('info','warning','success','error') DEFAULT 'info',
  `is_read` tinyint(1) DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `notification_preferences`
--

CREATE TABLE `notification_preferences` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `type_id` int(11) NOT NULL,
  `email` tinyint(1) DEFAULT 1,
  `sms` tinyint(1) DEFAULT 1,
  `push` tinyint(1) DEFAULT 1,
  `in_app` tinyint(1) DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `notification_priorities`
--

CREATE TABLE `notification_priorities` (
  `id` int(11) NOT NULL,
  `name` varchar(50) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `notification_priorities`
--

INSERT INTO `notification_priorities` (`id`, `name`, `created_at`) VALUES
(1, 'LOW', '2025-05-14 10:18:06'),
(2, 'NORMAL', '2025-05-14 10:18:06'),
(3, 'HIGH', '2025-05-14 10:18:06'),
(4, 'URGENT', '2025-05-14 10:18:06');

-- --------------------------------------------------------

--
-- Table structure for table `notification_statuses`
--

CREATE TABLE `notification_statuses` (
  `id` int(11) NOT NULL,
  `name` varchar(50) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `notification_statuses`
--

INSERT INTO `notification_statuses` (`id`, `name`, `created_at`) VALUES
(1, 'UNREAD', '2025-05-14 10:18:07'),
(2, 'READ', '2025-05-14 10:18:07'),
(3, 'PENDING', '2025-05-14 10:18:07'),
(4, 'COMPLETED', '2025-05-14 10:18:07'),
(5, 'DISMISSED', '2025-05-14 10:18:07');

-- --------------------------------------------------------

--
-- Table structure for table `notification_types`
--

CREATE TABLE `notification_types` (
  `id` int(11) NOT NULL,
  `name` varchar(50) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `notification_types`
--

INSERT INTO `notification_types` (`id`, `name`, `created_at`) VALUES
(1, 'SERVICE_REQUEST', '2025-05-14 10:18:05'),
(2, 'DOCUMENT_UPLOAD', '2025-05-14 10:18:05'),
(3, 'PAYMENT', '2025-05-14 10:18:06'),
(4, 'APPOINTMENT', '2025-05-14 10:18:06'),
(5, 'CHAT_MESSAGE', '2025-05-14 10:18:06'),
(6, 'SYSTEM_UPDATE', '2025-05-14 10:18:06'),
(7, 'TASK_ASSIGNED', '2025-05-14 10:18:06'),
(8, 'DEADLINE_REMINDER', '2025-05-14 10:18:06'),
(9, 'STATUS_UPDATE', '2025-05-14 10:18:06'),
(10, 'VERIFICATION', '2025-05-14 10:18:06'),
(11, 'NEW_MESSAGE', '2025-05-14 10:18:06'),
(12, 'MENTION', '2025-05-14 10:18:06'),
(13, 'REPLY', '2025-05-14 10:18:06'),
(14, 'REACTION', '2025-05-14 10:18:06'),
(15, 'THREAD_UPDATE', '2025-05-14 10:18:06'),
(16, 'FOLLOW_UP', '2025-05-14 10:18:06');

-- --------------------------------------------------------

--
-- Table structure for table `offices`
--

CREATE TABLE `offices` (
  `id` int(11) NOT NULL,
  `name` varchar(255) NOT NULL,
  `address` text NOT NULL,
  `phone` varchar(20) NOT NULL,
  `email` varchar(255) NOT NULL,
  `manager_id` int(11) DEFAULT NULL,
  `status` enum('ACTIVE','INACTIVE') DEFAULT 'ACTIVE',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `offices`
--

INSERT INTO `offices` (`id`, `name`, `address`, `phone`, `email`, `manager_id`, `status`, `created_at`, `updated_at`) VALUES
(1, 'Dilla Office', 'Dilla Main Street, SNNPR', '+251912345678', 'dilla@dulas.com', 3, 'ACTIVE', '2025-05-14 10:53:28', '2025-05-14 10:57:07'),
(2, 'Yirga Chafe Office', 'Yirga Chafe Center, SNNPR', '+251912345679', 'yirgachafe@dulas.com', NULL, 'ACTIVE', '2025-05-14 10:53:28', '2025-05-14 10:53:28'),
(3, 'Bule Office', 'Bule Town, SNNPR', '+251912345680', 'bule@dulas.com', NULL, 'ACTIVE', '2025-05-14 10:53:28', '2025-05-14 10:53:28'),
(4, 'Cheleltu Office', 'Cheleltu Town, SNNPR', '+251912345681', 'cheleltu@dulas.com', NULL, 'ACTIVE', '2025-05-14 10:53:28', '2025-05-14 10:53:28'),
(5, 'Yega Office', 'Yega Town, SNNPR', '+251912345682', 'yega@dulas.com', NULL, 'ACTIVE', '2025-05-14 10:53:28', '2025-05-14 10:53:28'),
(6, 'Onago Office', 'Onago Town, SNNPR', '+251912345683', 'onago@dulas.com', NULL, 'ACTIVE', '2025-05-14 10:53:28', '2025-05-14 10:53:28'),
(7, 'Guange Office', 'Guange Town, SNNPR', '+251912345684', 'guange@dulas.com', NULL, 'ACTIVE', '2025-05-14 10:53:28', '2025-05-14 10:53:28'),
(8, 'Sobo Office', 'Sobo Town, SNNPR', '+251912345685', 'sobo@dulas.com', NULL, 'ACTIVE', '2025-05-14 10:53:28', '2025-05-14 10:53:28');

-- --------------------------------------------------------

--
-- Table structure for table `office_performances`
--

CREATE TABLE `office_performances` (
  `id` int(11) NOT NULL,
  `office_id` int(11) NOT NULL,
  `metric_name` varchar(100) NOT NULL,
  `metric_value` float NOT NULL,
  `period_start` date NOT NULL,
  `period_end` date NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `office_templates`
--

CREATE TABLE `office_templates` (
  `id` int(11) NOT NULL,
  `office_id` int(11) NOT NULL,
  `name` varchar(255) NOT NULL,
  `content` text NOT NULL,
  `type` varchar(50) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `organizations`
--

CREATE TABLE `organizations` (
  `id` int(11) NOT NULL,
  `name` varchar(100) NOT NULL,
  `type` enum('law_firm','law_school','legal_department') NOT NULL,
  `address` text DEFAULT NULL,
  `phone` varchar(20) DEFAULT NULL,
  `email` varchar(100) DEFAULT NULL,
  `website` varchar(100) DEFAULT NULL,
  `subscription_status` enum('active','inactive','expired') DEFAULT 'inactive',
  `subscription_end_date` date DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `otp_verifications`
--

CREATE TABLE `otp_verifications` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `otp` varchar(10) NOT NULL,
  `type` enum('EMAIL','PHONE') NOT NULL,
  `expires_at` datetime NOT NULL,
  `verified` tinyint(1) DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `paralegals`
--

CREATE TABLE `paralegals` (
  `id` int(11) NOT NULL,
  `office_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `specialization` varchar(100) DEFAULT NULL,
  `status` enum('ACTIVE','INACTIVE') DEFAULT 'ACTIVE',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `permissions`
--

CREATE TABLE `permissions` (
  `id` int(11) NOT NULL,
  `permission_name` varchar(100) NOT NULL,
  `module` varchar(50) DEFAULT NULL,
  `description` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `permissions`
--

INSERT INTO `permissions` (`id`, `permission_name`, `module`, `description`, `created_at`) VALUES
(1, 'view_dashboard', 'Dashboard', 'Access to dashboard and overview statistics', '2025-05-14 08:01:09'),
(2, 'view_analytics', 'Dashboard', 'Access to detailed analytics and reports', '2025-05-14 08:01:09'),
(3, 'view_cases', 'Cases', 'View case listings and details', '2025-05-14 08:01:09'),
(4, 'create_cases', 'Cases', 'Create new cases', '2025-05-14 08:01:10'),
(5, 'edit_cases', 'Cases', 'Edit existing cases', '2025-05-14 08:01:10'),
(6, 'delete_cases', 'Cases', 'Delete cases', '2025-05-14 08:01:10'),
(7, 'assign_cases', 'Cases', 'Assign cases to team members', '2025-05-14 08:01:10'),
(8, 'close_cases', 'Cases', 'Close or archive cases', '2025-05-14 08:01:10'),
(9, 'view_case_history', 'Cases', 'View case history and audit logs', '2025-05-14 08:01:10'),
(10, 'view_documents', 'Documents', 'View case documents', '2025-05-14 08:01:10'),
(11, 'upload_documents', 'Documents', 'Upload new documents', '2025-05-14 08:01:10'),
(12, 'edit_documents', 'Documents', 'Edit document details', '2025-05-14 08:01:10'),
(13, 'delete_documents', 'Documents', 'Delete documents', '2025-05-14 08:01:10'),
(14, 'download_documents', 'Documents', 'Download documents', '2025-05-14 08:01:10'),
(15, 'share_documents', 'Documents', 'Share documents with others', '2025-05-14 08:01:10'),
(16, 'view_calendar', 'Calendar', 'View calendar events', '2025-05-14 08:01:10'),
(17, 'create_events', 'Calendar', 'Create calendar events', '2025-05-14 08:01:11'),
(18, 'edit_events', 'Calendar', 'Edit calendar events', '2025-05-14 08:01:11'),
(19, 'delete_events', 'Calendar', 'Delete calendar events', '2025-05-14 08:01:11'),
(20, 'manage_hearings', 'Calendar', 'Manage court hearings', '2025-05-14 08:01:11'),
(21, 'view_tasks', 'Tasks', 'View tasks', '2025-05-14 08:01:11'),
(22, 'create_tasks', 'Tasks', 'Create new tasks', '2025-05-14 08:01:11'),
(23, 'edit_tasks', 'Tasks', 'Edit existing tasks', '2025-05-14 08:01:11'),
(24, 'delete_tasks', 'Tasks', 'Delete tasks', '2025-05-14 08:01:11'),
(25, 'assign_tasks', 'Tasks', 'Assign tasks to team members', '2025-05-14 08:01:11'),
(26, 'complete_tasks', 'Tasks', 'Mark tasks as complete', '2025-05-14 08:01:11'),
(27, 'view_billing', 'Billing', 'View billing information', '2025-05-14 08:01:11'),
(28, 'create_invoices', 'Billing', 'Create new invoices', '2025-05-14 08:01:11'),
(29, 'edit_invoices', 'Billing', 'Edit existing invoices', '2025-05-14 08:01:12'),
(30, 'delete_invoices', 'Billing', 'Delete invoices', '2025-05-14 08:01:12'),
(31, 'process_payments', 'Billing', 'Process payments', '2025-05-14 08:01:12'),
(32, 'view_payment_history', 'Billing', 'View payment history', '2025-05-14 08:01:12'),
(33, 'view_users', 'Users', 'View user listings', '2025-05-14 08:01:12'),
(34, 'create_users', 'Users', 'Create new users', '2025-05-14 08:01:12'),
(35, 'edit_users', 'Users', 'Edit user details', '2025-05-14 08:01:12'),
(36, 'delete_users', 'Users', 'Delete users', '2025-05-14 08:01:12'),
(37, 'manage_roles', 'Users', 'Manage user roles and permissions', '2025-05-14 08:01:12'),
(38, 'view_user_activity', 'Users', 'View user activity logs', '2025-05-14 08:01:12'),
(39, 'view_organizations', 'Organizations', 'View organization listings', '2025-05-14 08:01:12'),
(40, 'create_organizations', 'Organizations', 'Create new organizations', '2025-05-14 08:01:12'),
(41, 'edit_organizations', 'Organizations', 'Edit organization details', '2025-05-14 08:01:12'),
(42, 'delete_organizations', 'Organizations', 'Delete organizations', '2025-05-14 08:01:12'),
(43, 'view_settings', 'Settings', 'View system settings', '2025-05-14 08:01:12'),
(44, 'edit_settings', 'Settings', 'Edit system settings', '2025-05-14 08:01:12'),
(45, 'manage_backups', 'Settings', 'Manage system backups', '2025-05-14 08:01:12'),
(46, 'view_audit_logs', 'Settings', 'View system audit logs', '2025-05-14 08:01:12'),
(47, 'send_notifications', 'Communication', 'Send system notifications', '2025-05-14 08:01:13'),
(48, 'manage_templates', 'Communication', 'Manage email and document templates', '2025-05-14 08:01:13'),
(49, 'view_messages', 'Communication', 'View internal messages', '2025-05-14 08:01:13'),
(50, 'send_messages', 'Communication', 'Send internal messages', '2025-05-14 08:01:13'),
(51, 'view_reports', 'Reports', 'View system reports', '2025-05-14 08:01:13'),
(52, 'generate_reports', 'Reports', 'Generate custom reports', '2025-05-14 08:01:13'),
(53, 'export_reports', 'Reports', 'Export reports to different formats', '2025-05-14 08:01:13'),
(54, 'manage_security', 'Security', 'Manage security settings', '2025-05-14 08:01:13'),
(55, 'view_security_logs', 'Security', 'View security logs', '2025-05-14 08:01:13'),
(56, 'manage_2fa', 'Security', 'Manage two-factor authentication', '2025-05-14 08:01:13'),
(57, 'manage_api_keys', 'Security', 'Manage API keys and integrations', '2025-05-14 08:01:13');

-- --------------------------------------------------------

--
-- Table structure for table `ratings`
--

CREATE TABLE `ratings` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `rating` float NOT NULL,
  `comment` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `resource_analytics`
--

CREATE TABLE `resource_analytics` (
  `id` int(11) NOT NULL,
  `resource_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `action` enum('VIEW','DOWNLOAD','SHARE','EDIT') NOT NULL,
  `metadata` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`metadata`)),
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `resource_shares`
--

CREATE TABLE `resource_shares` (
  `id` int(11) NOT NULL,
  `resource_id` int(11) NOT NULL,
  `shared_by_id` int(11) NOT NULL,
  `shared_with_id` int(11) NOT NULL,
  `permission_level` enum('VIEW','EDIT','ADMIN') DEFAULT 'VIEW',
  `expires_at` datetime DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `roles`
--

CREATE TABLE `roles` (
  `id` int(11) NOT NULL,
  `role_name` varchar(50) NOT NULL,
  `description` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `roles`
--

INSERT INTO `roles` (`id`, `role_name`, `description`, `created_at`) VALUES
(1, 'client', 'Client user', '2025-05-14 08:01:09'),
(2, 'lawyer', 'Lawyer user', '2025-05-14 08:01:09'),
(3, 'paralegal', 'Paralegal user', '2025-05-14 08:01:09'),
(4, 'super_paralegal', 'Super Paralegal user', '2025-05-14 08:01:09'),
(5, 'lawschool', 'Law School user', '2025-05-14 08:01:09'),
(6, 'admin', 'Admin user', '2025-05-14 08:01:09'),
(7, 'superadmin', 'Superadmin user', '2025-05-14 08:01:09');

-- --------------------------------------------------------

--
-- Table structure for table `role_permissions`
--

CREATE TABLE `role_permissions` (
  `id` int(11) NOT NULL,
  `role` varchar(50) NOT NULL,
  `permission` varchar(100) NOT NULL,
  `description` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `role_permissions`
--

INSERT INTO `role_permissions` (`id`, `role`, `permission`, `description`, `created_at`) VALUES
(1, 'client', 'view_dashboard', 'Access to dashboard', '2025-05-14 04:43:26'),
(2, 'lawyer', 'view_dashboard', 'Access to dashboard', '2025-05-14 04:43:26'),
(3, 'lawyer', 'manage_cases', 'Manage legal cases', '2025-05-14 04:43:26'),
(4, 'lawyer', 'manage_documents', 'Manage legal documents', '2025-05-14 04:43:26'),
(5, 'lawyer', 'manage_billing', 'Manage billing and payments', '2025-05-14 04:43:27'),
(6, 'lawyer', 'view_reports', 'View system reports', '2025-05-14 04:43:27'),
(7, 'paralegal', 'view_dashboard', 'Access to dashboard', '2025-05-14 04:43:27'),
(8, 'paralegal', 'manage_cases', 'Manage legal cases', '2025-05-14 04:43:27'),
(9, 'paralegal', 'manage_documents', 'Manage legal documents', '2025-05-14 04:43:27'),
(10, 'super_paralegal', 'view_dashboard', 'Access to dashboard', '2025-05-14 04:43:27'),
(11, 'super_paralegal', 'manage_cases', 'Manage legal cases', '2025-05-14 04:43:27'),
(12, 'super_paralegal', 'manage_documents', 'Manage legal documents', '2025-05-14 04:43:27'),
(13, 'super_paralegal', 'view_reports', 'View system reports', '2025-05-14 04:43:27'),
(14, 'lawschool', 'view_dashboard', 'Access to dashboard', '2025-05-14 04:43:27'),
(15, 'lawschool', 'view_reports', 'View system reports', '2025-05-14 04:43:27'),
(16, 'admin', 'view_dashboard', 'Access to dashboard', '2025-05-14 04:43:27'),
(17, 'admin', 'manage_cases', 'Manage legal cases', '2025-05-14 04:43:27'),
(18, 'admin', 'manage_documents', 'Manage legal documents', '2025-05-14 04:43:27'),
(19, 'admin', 'manage_users', 'Manage system users', '2025-05-14 04:43:27'),
(20, 'admin', 'manage_billing', 'Manage billing and payments', '2025-05-14 04:43:27'),
(21, 'admin', 'view_reports', 'View system reports', '2025-05-14 04:43:27'),
(22, 'admin', 'manage_settings', 'Manage system settings', '2025-05-14 04:43:27'),
(23, 'superadmin', 'view_dashboard', 'Access to dashboard', '2025-05-14 04:43:27'),
(24, 'superadmin', 'manage_cases', 'Manage legal cases', '2025-05-14 04:43:27'),
(25, 'superadmin', 'manage_documents', 'Manage legal documents', '2025-05-14 04:43:27'),
(26, 'superadmin', 'manage_users', 'Manage system users', '2025-05-14 04:43:27'),
(27, 'superadmin', 'manage_billing', 'Manage billing and payments', '2025-05-14 04:43:27'),
(28, 'superadmin', 'view_reports', 'View system reports', '2025-05-14 04:43:27'),
(29, 'superadmin', 'manage_settings', 'Manage system settings', '2025-05-14 04:43:28'),
(30, 'superadmin', 'manage_roles', 'Manage user roles and permissions', '2025-05-14 04:43:28');

-- --------------------------------------------------------

--
-- Table structure for table `security_logs`
--

CREATE TABLE `security_logs` (
  `id` int(11) NOT NULL,
  `timestamp` timestamp NOT NULL DEFAULT current_timestamp(),
  `event_type` varchar(100) NOT NULL,
  `severity` varchar(50) NOT NULL,
  `description` text NOT NULL,
  `ip_address` varchar(45) NOT NULL,
  `user_id` int(11) DEFAULT NULL,
  `status` varchar(50) NOT NULL,
  `details` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`details`)),
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `service_requests`
--

CREATE TABLE `service_requests` (
  `id` int(11) NOT NULL,
  `client_id` int(11) NOT NULL,
  `package_id` int(11) NOT NULL,
  `status` enum('PENDING','APPROVED','REJECTED','IN_PROGRESS','COMPLETED','CANCELLED','ON_HOLD') DEFAULT 'PENDING',
  `priority` enum('LOW','MEDIUM','HIGH','URGENT') DEFAULT 'MEDIUM',
  `assigned_lawyer_id` int(11) DEFAULT NULL,
  `title` varchar(255) NOT NULL,
  `description` text DEFAULT NULL,
  `requirements` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`requirements`)),
  `submitted_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `approved_at` datetime DEFAULT NULL,
  `completed_at` datetime DEFAULT NULL,
  `progress` int(11) DEFAULT 0,
  `current_stage` varchar(100) DEFAULT NULL,
  `next_action` varchar(255) DEFAULT NULL,
  `quoted_price` decimal(10,2) DEFAULT NULL,
  `final_price` decimal(10,2) DEFAULT NULL,
  `payment_status` enum('PENDING','PROCESSING','COMPLETED','FAILED','REFUNDED','CANCELLED','PAID','WAIVED') DEFAULT 'PENDING',
  `tags` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`tags`)),
  `metadata` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`metadata`))
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `sessions`
--

CREATE TABLE `sessions` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `token` varchar(255) NOT NULL,
  `active` tinyint(1) DEFAULT 1,
  `user_agent` text DEFAULT NULL,
  `last_ip_address` varchar(45) DEFAULT NULL,
  `location` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`location`)),
  `expires_at` datetime NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `specialties`
--

CREATE TABLE `specialties` (
  `id` int(11) NOT NULL,
  `name` varchar(100) NOT NULL,
  `status` enum('active','inactive') DEFAULT 'active',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `specialties`
--

INSERT INTO `specialties` (`id`, `name`, `status`, `created_at`, `updated_at`) VALUES
(1, 'Criminal Law', 'active', '2025-05-14 11:23:08', '2025-05-14 11:23:08'),
(2, 'Civil Law', 'active', '2025-05-14 11:23:08', '2025-05-14 11:23:08'),
(3, 'Family Law', 'active', '2025-05-14 11:23:08', '2025-05-14 11:23:08'),
(4, 'Corporate Law', 'active', '2025-05-14 11:23:08', '2025-05-14 11:23:08'),
(5, 'Real Estate Law', 'active', '2025-05-14 11:23:08', '2025-05-14 11:23:08'),
(6, 'Intellectual Property Law', 'active', '2025-05-14 11:23:08', '2025-05-14 11:23:08'),
(7, 'Immigration Law', 'active', '2025-05-14 11:23:08', '2025-05-14 11:23:08'),
(8, 'Tax Law', 'active', '2025-05-14 11:23:08', '2025-05-14 11:23:08'),
(9, 'Environmental Law', 'active', '2025-05-14 11:23:08', '2025-05-14 11:23:08'),
(10, 'Labor Law', 'active', '2025-05-14 11:23:08', '2025-05-14 11:23:08'),
(11, 'Criminal Law', 'active', '2025-05-14 11:30:09', '2025-05-14 11:30:09'),
(12, 'Civil Law', 'active', '2025-05-14 11:30:09', '2025-05-14 11:30:09'),
(13, 'Family Law', 'active', '2025-05-14 11:30:09', '2025-05-14 11:30:09'),
(14, 'Corporate Law', 'active', '2025-05-14 11:30:09', '2025-05-14 11:30:09'),
(15, 'Real Estate Law', 'active', '2025-05-14 11:30:09', '2025-05-14 11:30:09'),
(16, 'Intellectual Property Law', 'active', '2025-05-14 11:30:09', '2025-05-14 11:30:09'),
(17, 'Immigration Law', 'active', '2025-05-14 11:30:09', '2025-05-14 11:30:09'),
(18, 'Tax Law', 'active', '2025-05-14 11:30:09', '2025-05-14 11:30:09'),
(19, 'Environmental Law', 'active', '2025-05-14 11:30:09', '2025-05-14 11:30:09'),
(20, 'Labor Law', 'active', '2025-05-14 11:30:09', '2025-05-14 11:30:09'),
(21, 'Criminal Law', 'active', '2025-05-14 15:45:55', '2025-05-14 15:45:55'),
(22, 'Civil Law', 'active', '2025-05-14 15:45:55', '2025-05-14 15:45:55'),
(23, 'Family Law', 'active', '2025-05-14 15:45:55', '2025-05-14 15:45:55'),
(24, 'Corporate Law', 'active', '2025-05-14 15:45:55', '2025-05-14 15:45:55'),
(25, 'Real Estate Law', 'active', '2025-05-14 15:45:55', '2025-05-14 15:45:55'),
(26, 'Intellectual Property Law', 'active', '2025-05-14 15:45:55', '2025-05-14 15:45:55'),
(27, 'Immigration Law', 'active', '2025-05-14 15:45:55', '2025-05-14 15:45:55'),
(28, 'Tax Law', 'active', '2025-05-14 15:45:55', '2025-05-14 15:45:55'),
(29, 'Environmental Law', 'active', '2025-05-14 15:45:56', '2025-05-14 15:45:56'),
(30, 'Labor Law', 'active', '2025-05-14 15:45:56', '2025-05-14 15:45:56');

-- --------------------------------------------------------

--
-- Table structure for table `tasks`
--

CREATE TABLE `tasks` (
  `id` int(11) NOT NULL,
  `title` varchar(255) NOT NULL,
  `description` text DEFAULT NULL,
  `case_id` int(11) DEFAULT NULL,
  `assigned_to` int(11) DEFAULT NULL,
  `assigned_by` int(11) DEFAULT NULL,
  `priority` enum('low','medium','high') DEFAULT 'medium',
  `status` enum('pending','in_progress','completed') DEFAULT 'pending',
  `due_date` date DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `teaching_metrics`
--

CREATE TABLE `teaching_metrics` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `schedule_id` int(11) NOT NULL,
  `total_sessions` int(11) DEFAULT 0,
  `total_participants` int(11) DEFAULT 0,
  `average_rating` float DEFAULT 0,
  `feedback_count` int(11) DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `teaching_schedules`
--

CREATE TABLE `teaching_schedules` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `title` varchar(255) NOT NULL,
  `description` text DEFAULT NULL,
  `start_time` datetime NOT NULL,
  `end_time` datetime NOT NULL,
  `location` varchar(255) DEFAULT NULL,
  `max_participants` int(11) DEFAULT NULL,
  `current_participants` int(11) DEFAULT 0,
  `status` enum('SCHEDULED','IN_PROGRESS','COMPLETED','CANCELLED') DEFAULT 'SCHEDULED',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `thread_participants`
--

CREATE TABLE `thread_participants` (
  `id` int(11) NOT NULL,
  `thread_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `role` enum('ADMIN','MEMBER') DEFAULT 'MEMBER',
  `joined_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `last_read_at` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `typing_status`
--

CREATE TABLE `typing_status` (
  `id` int(11) NOT NULL,
  `thread_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `is_typing` tinyint(1) DEFAULT 0,
  `last_activity` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` int(11) NOT NULL,
  `username` varchar(50) NOT NULL,
  `password` varchar(255) NOT NULL,
  `email` varchar(100) NOT NULL,
  `full_name` varchar(100) NOT NULL,
  `role` enum('client','lawyer','paralegal','super_paralegal','lawschool','admin','superadmin') NOT NULL,
  `organization_id` int(11) DEFAULT NULL,
  `phone` varchar(20) DEFAULT NULL,
  `address` text DEFAULT NULL,
  `profile_image` varchar(255) DEFAULT NULL,
  `bio` text DEFAULT NULL,
  `status` enum('active','inactive','suspended') DEFAULT 'active',
  `email_verified` tinyint(1) DEFAULT 0,
  `verification_token` varchar(100) DEFAULT NULL,
  `reset_token` varchar(100) DEFAULT NULL,
  `reset_token_expiry` datetime DEFAULT NULL,
  `last_login` datetime DEFAULT NULL,
  `last_login_ip` varchar(45) DEFAULT NULL,
  `login_attempts` int(11) DEFAULT 0,
  `locked_until` datetime DEFAULT NULL,
  `two_factor_enabled` tinyint(1) DEFAULT 0,
  `two_factor_secret` varchar(32) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `username`, `password`, `email`, `full_name`, `role`, `organization_id`, `phone`, `address`, `profile_image`, `bio`, `status`, `email_verified`, `verification_token`, `reset_token`, `reset_token_expiry`, `last_login`, `last_login_ip`, `login_attempts`, `locked_until`, `two_factor_enabled`, `two_factor_secret`, `created_at`, `updated_at`) VALUES
(1, 'cherinet', '$2y$10$UHINP39dp78AakDLi6FHJeOO48vVysWhYtHvHK.l1q6FDUKgyDtoC', 'cherinet@dulas.com', 'Cherinet Administrator', 'superadmin', NULL, NULL, NULL, NULL, NULL, 'active', 1, NULL, NULL, NULL, '2025-05-14 09:40:19', '::1', 0, NULL, 0, NULL, '2025-05-14 04:43:24', '2025-05-14 16:40:19'),
(2, 'chere', '$2y$10$UHINP39dp78AakDLi6FHJeOO48vVysWhYtHvHK.l1q6FDUKgyDtoC', 'chere@dulas.com', 'Chere Admin', 'admin', NULL, NULL, NULL, NULL, NULL, 'active', 1, NULL, NULL, NULL, '2025-05-14 09:40:36', '::1', 0, NULL, 0, NULL, '2025-05-14 04:43:24', '2025-05-14 16:40:36'),
(3, 'cherean', '$2y$10$UHINP39dp78AakDLi6FHJeOO48vVysWhYtHvHK.l1q6FDUKgyDtoC', 'cherean@dulas.com', 'Cherean Lawyer', 'lawyer', 4, '+251912345678', NULL, NULL, NULL, 'active', 1, NULL, NULL, NULL, NULL, NULL, 0, NULL, 0, NULL, '2025-05-14 04:43:24', '2025-05-14 11:24:07'),
(4, 'cher', '$2y$10$UHINP39dp78AakDLi6FHJeOO48vVysWhYtHvHK.l1q6FDUKgyDtoC', 'cher@dulas.com', 'Cher Super Paralegal', 'super_paralegal', NULL, NULL, NULL, NULL, NULL, 'active', 1, NULL, NULL, NULL, NULL, NULL, 0, NULL, 0, NULL, '2025-05-14 04:43:24', '2025-05-14 04:43:24'),
(5, 'che', '$2y$10$UHINP39dp78AakDLi6FHJeOO48vVysWhYtHvHK.l1q6FDUKgyDtoC', 'che@dulas.com', 'Che Paralegal', 'paralegal', 4, '0947006369', NULL, NULL, NULL, 'active', 1, NULL, NULL, NULL, NULL, NULL, 0, NULL, 0, NULL, '2025-05-14 04:43:24', '2025-05-14 11:06:54'),
(6, 'client', '$2y$10$UHINP39dp78AakDLi6FHJeOO48vVysWhYtHvHK.l1q6FDUKgyDtoC', 'client@dulas.com', 'Test Client', 'client', NULL, NULL, NULL, NULL, NULL, 'active', 1, NULL, NULL, NULL, NULL, NULL, 0, NULL, 0, NULL, '2025-05-14 04:43:24', '2025-05-14 04:43:24'),
(19, 'lawyer1', '$2y$10$45SMVgHqcNEE4EYQxJLDWeCvS5YPRnF2VWtzlOM9BXVaIFsQdEu4q', 'lawyer1@dulas.com', 'Abebe Kebede', 'lawyer', 1, '+251912345678', NULL, NULL, NULL, 'active', 0, NULL, NULL, NULL, NULL, NULL, 0, NULL, 0, NULL, '2025-05-14 10:53:29', '2025-05-14 10:53:29'),
(20, 'lawyer2', '$2y$10$ehxmH9NosALAVFThbCUsyOiF4vDkiwZUYo6vOD0AJEnH/PHHQhhxm', 'lawyer2@dulas.com', 'Kebede Alemu', 'lawyer', 1, '+251912345679', NULL, NULL, NULL, 'active', 0, NULL, NULL, NULL, NULL, NULL, 0, NULL, 0, NULL, '2025-05-14 10:53:29', '2025-05-14 10:53:29'),
(21, 'paralegal1', '$2y$10$uRgiy74X5E5pX0/9F3iKieUyitZ/jc99Udhe.Wqf8cKHrdmYPwKOS', 'paralegal1@dulas.com', 'Tigist Worku', 'paralegal', 1, '+251912345680', NULL, NULL, NULL, 'active', 0, NULL, NULL, NULL, NULL, NULL, 0, NULL, 0, NULL, '2025-05-14 10:53:29', '2025-05-14 10:53:29'),
(22, 'paralegal2', '$2y$10$l6VKW8hpOVvShSZA4gzvpOOsA2dNidFwCUHLJ.6e4TtSJCDvV95je', 'paralegal2@dulas.com', 'Solomon Teklu', 'paralegal', 1, '+251912345681', NULL, NULL, NULL, 'active', 0, NULL, NULL, NULL, NULL, NULL, 0, NULL, 0, NULL, '2025-05-14 10:53:29', '2025-05-14 10:53:29'),
(23, 'paralegal3', '$2y$10$/wDsem4j0/os31LKOiFWrugZoaaXIatFKBRy0G1tlpaIJFDtDfNzO', 'paralegal3@dulas.com', 'Mekdes Haile', 'paralegal', 1, '+251912345682', NULL, NULL, NULL, 'active', 0, NULL, NULL, NULL, NULL, NULL, 0, NULL, 0, NULL, '2025-05-14 10:53:29', '2025-05-14 10:53:29'),
(24, 'test', '$2y$10$4ZOgj8wJ7HPYO/NAUimXoelSL0joUwNewxg21A1nG1XYojpjSS8DC', 'chhhhh@gmail.com', 'cherinet afewerk', 'client', NULL, '+251912345678', NULL, NULL, NULL, 'active', 0, NULL, NULL, NULL, '2025-05-14 09:50:43', '::1', 0, NULL, 0, NULL, '2025-05-14 16:11:21', '2025-05-14 16:50:43');

-- --------------------------------------------------------

--
-- Table structure for table `user_activities`
--

CREATE TABLE `user_activities` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `activity_type` varchar(50) NOT NULL,
  `description` text DEFAULT NULL,
  `ip_address` varchar(45) DEFAULT NULL,
  `user_agent` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `user_preferences`
--

CREATE TABLE `user_preferences` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `theme` varchar(20) DEFAULT 'light',
  `language` varchar(10) DEFAULT 'en',
  `timezone` varchar(50) DEFAULT 'UTC',
  `notification_settings` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`notification_settings`)),
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `user_roles`
--

CREATE TABLE `user_roles` (
  `id` int(11) NOT NULL,
  `name` varchar(50) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `user_roles`
--

INSERT INTO `user_roles` (`id`, `name`, `created_at`) VALUES
(1, 'SUPER_ADMIN', '2025-05-14 10:18:05'),
(2, 'ADMIN', '2025-05-14 10:18:05'),
(3, 'LAWYER', '2025-05-14 10:18:05'),
(4, 'COORDINATOR', '2025-05-14 10:18:05'),
(5, 'CLIENT', '2025-05-14 10:18:05');

-- --------------------------------------------------------

--
-- Table structure for table `user_roles_history`
--

CREATE TABLE `user_roles_history` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `previous_role` varchar(50) NOT NULL,
  `new_role` varchar(50) NOT NULL,
  `changed_by` int(11) NOT NULL,
  `reason` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `user_security_logs`
--

CREATE TABLE `user_security_logs` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `event_type` enum('login','logout','password_change','email_change','role_change','security_settings_change') NOT NULL,
  `ip_address` varchar(45) DEFAULT NULL,
  `user_agent` text DEFAULT NULL,
  `status` enum('success','failed','blocked') NOT NULL,
  `details` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `user_security_logs`
--

INSERT INTO `user_security_logs` (`id`, `user_id`, `event_type`, `ip_address`, `user_agent`, `status`, `details`, `created_at`) VALUES
(1, 1, 'login', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/136.0.0.0 Safari/537.36', 'success', NULL, '2025-05-14 04:44:51'),
(2, 1, 'login', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/136.0.0.0 Safari/537.36', 'success', NULL, '2025-05-14 05:33:32'),
(3, 1, 'login', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/136.0.0.0 Safari/537.36', 'success', NULL, '2025-05-14 07:31:19'),
(4, 1, 'login', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/136.0.0.0 Safari/537.36', 'success', NULL, '2025-05-14 08:08:16'),
(5, 2, 'login', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/136.0.0.0 Safari/537.36', 'success', NULL, '2025-05-14 08:08:44'),
(6, 1, 'login', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/136.0.0.0 Safari/537.36', 'success', NULL, '2025-05-14 15:12:14'),
(7, 1, 'login', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/136.0.0.0 Safari/537.36', 'success', NULL, '2025-05-14 15:12:50'),
(8, 2, 'login', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/136.0.0.0 Safari/537.36', 'success', NULL, '2025-05-14 15:13:18'),
(9, 1, 'login', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/136.0.0.0 Safari/537.36', 'success', NULL, '2025-05-14 16:09:44'),
(10, 24, 'login', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/136.0.0.0 Safari/537.36', 'failed', NULL, '2025-05-14 16:11:39'),
(11, 24, 'login', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/136.0.0.0 Safari/537.36', 'success', NULL, '2025-05-14 16:11:53'),
(12, 24, 'login', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/136.0.0.0 Safari/537.36', 'success', NULL, '2025-05-14 16:35:37'),
(13, 24, 'login', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/136.0.0.0 Safari/537.36', 'failed', NULL, '2025-05-14 16:37:13'),
(14, 24, 'login', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/136.0.0.0 Safari/537.36', 'success', NULL, '2025-05-14 16:37:40'),
(15, 24, 'login', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/136.0.0.0 Safari/537.36', 'success', NULL, '2025-05-14 16:38:37'),
(16, 1, 'login', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/136.0.0.0 Safari/537.36', 'success', NULL, '2025-05-14 16:39:56'),
(17, 1, 'login', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/136.0.0.0 Safari/537.36', 'success', NULL, '2025-05-14 16:40:19'),
(18, 2, 'login', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/136.0.0.0 Safari/537.36', 'success', NULL, '2025-05-14 16:40:35'),
(19, 24, 'login', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/136.0.0.0 Safari/537.36', 'failed', NULL, '2025-05-14 16:42:13'),
(20, 24, 'login', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/136.0.0.0 Safari/537.36', 'success', NULL, '2025-05-14 16:42:21'),
(21, 24, 'login', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/136.0.0.0 Safari/537.36', 'success', NULL, '2025-05-14 16:43:24'),
(22, 24, 'login', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/136.0.0.0 Safari/537.36', 'success', NULL, '2025-05-14 16:50:07'),
(23, 24, 'login', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/136.0.0.0 Safari/537.36', 'success', NULL, '2025-05-14 16:50:43');

-- --------------------------------------------------------

--
-- Table structure for table `user_sessions`
--

CREATE TABLE `user_sessions` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `session_token` varchar(255) NOT NULL,
  `device_info` text DEFAULT NULL,
  `ip_address` varchar(45) DEFAULT NULL,
  `user_agent` text DEFAULT NULL,
  `last_activity` timestamp NOT NULL DEFAULT current_timestamp(),
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `user_statuses`
--

CREATE TABLE `user_statuses` (
  `id` int(11) NOT NULL,
  `name` varchar(50) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `user_statuses`
--

INSERT INTO `user_statuses` (`id`, `name`, `created_at`) VALUES
(1, 'ACTIVE', '2025-05-14 10:18:05'),
(2, 'INACTIVE', '2025-05-14 10:18:05'),
(3, 'SUSPENDED', '2025-05-14 10:18:05'),
(4, 'BANNED', '2025-05-14 10:18:05');

-- --------------------------------------------------------

--
-- Table structure for table `verification_requests`
--

CREATE TABLE `verification_requests` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `document_type` varchar(100) NOT NULL,
  `document_number` varchar(100) NOT NULL,
  `document_url` varchar(255) NOT NULL,
  `status` varchar(50) DEFAULT 'pending',
  `submitted_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `reviewed_at` datetime DEFAULT NULL,
  `reviewed_by_id` int(11) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `workload_metrics`
--

CREATE TABLE `workload_metrics` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `date` date NOT NULL,
  `total_cases` int(11) DEFAULT 0,
  `active_cases` int(11) DEFAULT 0,
  `completed_cases` int(11) DEFAULT 0,
  `total_hours` float DEFAULT 0,
  `billable_hours` float DEFAULT 0,
  `efficiency_score` float DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `work_assignments`
--

CREATE TABLE `work_assignments` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `case_id` int(11) DEFAULT NULL,
  `service_request_id` int(11) DEFAULT NULL,
  `title` varchar(255) NOT NULL,
  `description` text DEFAULT NULL,
  `status` enum('PENDING','IN_PROGRESS','COMPLETED','CANCELLED') DEFAULT 'PENDING',
  `priority` enum('LOW','MEDIUM','HIGH','URGENT') DEFAULT 'MEDIUM',
  `start_date` date DEFAULT NULL,
  `due_date` date DEFAULT NULL,
  `estimated_hours` float DEFAULT NULL,
  `actual_hours` float DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `work_schedules`
--

CREATE TABLE `work_schedules` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `day_of_week` enum('MONDAY','TUESDAY','WEDNESDAY','THURSDAY','FRIDAY','SATURDAY','SUNDAY') NOT NULL,
  `start_time` time NOT NULL,
  `end_time` time NOT NULL,
  `is_available` tinyint(1) DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Indexes for dumped tables
--

--
-- Indexes for table `activities`
--
ALTER TABLE `activities`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `agent_chats`
--
ALTER TABLE `agent_chats`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `agent_notifications`
--
ALTER TABLE `agent_notifications`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `appointments`
--
ALTER TABLE `appointments`
  ADD PRIMARY KEY (`id`),
  ADD KEY `client_id` (`client_id`),
  ADD KEY `coordinator_id` (`coordinator_id`);

--
-- Indexes for table `audit_logs`
--
ALTER TABLE `audit_logs`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `backups`
--
ALTER TABLE `backups`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `billing`
--
ALTER TABLE `billing`
  ADD PRIMARY KEY (`id`),
  ADD KEY `case_id` (`case_id`),
  ADD KEY `client_id` (`client_id`);

--
-- Indexes for table `block_records`
--
ALTER TABLE `block_records`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `calendar_events`
--
ALTER TABLE `calendar_events`
  ADD PRIMARY KEY (`id`),
  ADD KEY `case_id` (`case_id`),
  ADD KEY `created_by` (`created_by`);

--
-- Indexes for table `cases`
--
ALTER TABLE `cases`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `case_number` (`case_number`),
  ADD KEY `client_id` (`client_id`),
  ADD KEY `lawyer_id` (`lawyer_id`),
  ADD KEY `paralegal_id` (`paralegal_id`),
  ADD KEY `office_id` (`office_id`);

--
-- Indexes for table `case_activities`
--
ALTER TABLE `case_activities`
  ADD PRIMARY KEY (`id`),
  ADD KEY `case_id` (`case_id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `case_assignments`
--
ALTER TABLE `case_assignments`
  ADD PRIMARY KEY (`id`),
  ADD KEY `case_id` (`case_id`),
  ADD KEY `assigned_by_id` (`assigned_by_id`),
  ADD KEY `assigned_to_id` (`assigned_to_id`);

--
-- Indexes for table `case_documents`
--
ALTER TABLE `case_documents`
  ADD PRIMARY KEY (`id`),
  ADD KEY `case_id` (`case_id`),
  ADD KEY `uploaded_by` (`uploaded_by`);

--
-- Indexes for table `case_notes`
--
ALTER TABLE `case_notes`
  ADD PRIMARY KEY (`id`),
  ADD KEY `case_id` (`case_id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `clients`
--
ALTER TABLE `clients`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `client_profiles`
--
ALTER TABLE `client_profiles`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `user_id` (`user_id`),
  ADD KEY `office_id` (`office_id`);

--
-- Indexes for table `coordinators`
--
ALTER TABLE `coordinators`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `user_id` (`user_id`);

--
-- Indexes for table `coordinator_history`
--
ALTER TABLE `coordinator_history`
  ADD PRIMARY KEY (`id`),
  ADD KEY `coordinator_id` (`coordinator_id`),
  ADD KEY `client_id` (`client_id`),
  ADD KEY `lawyer_id` (`lawyer_id`);

--
-- Indexes for table `documents`
--
ALTER TABLE `documents`
  ADD PRIMARY KEY (`id`),
  ADD KEY `case_id` (`case_id`),
  ADD KEY `uploaded_by` (`uploaded_by`);

--
-- Indexes for table `events`
--
ALTER TABLE `events`
  ADD PRIMARY KEY (`id`),
  ADD KEY `created_by` (`created_by`),
  ADD KEY `case_id` (`case_id`);

--
-- Indexes for table `event_participants`
--
ALTER TABLE `event_participants`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `unique_event_user` (`event_id`,`user_id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `lawyer_appointments`
--
ALTER TABLE `lawyer_appointments`
  ADD PRIMARY KEY (`id`),
  ADD KEY `lawyer_id` (`lawyer_id`),
  ADD KEY `client_id` (`client_id`),
  ADD KEY `case_id` (`case_id`);

--
-- Indexes for table `lawyer_availability`
--
ALTER TABLE `lawyer_availability`
  ADD PRIMARY KEY (`id`),
  ADD KEY `lawyer_id` (`lawyer_id`);

--
-- Indexes for table `lawyer_billing`
--
ALTER TABLE `lawyer_billing`
  ADD PRIMARY KEY (`id`),
  ADD KEY `lawyer_id` (`lawyer_id`),
  ADD KEY `case_id` (`case_id`),
  ADD KEY `client_id` (`client_id`);

--
-- Indexes for table `lawyer_cases`
--
ALTER TABLE `lawyer_cases`
  ADD PRIMARY KEY (`id`),
  ADD KEY `lawyer_id` (`lawyer_id`),
  ADD KEY `case_id` (`case_id`);

--
-- Indexes for table `lawyer_documents`
--
ALTER TABLE `lawyer_documents`
  ADD PRIMARY KEY (`id`),
  ADD KEY `lawyer_id` (`lawyer_id`);

--
-- Indexes for table `lawyer_profiles`
--
ALTER TABLE `lawyer_profiles`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `bar_number` (`bar_number`),
  ADD KEY `user_id` (`user_id`),
  ADD KEY `office_id` (`office_id`);

--
-- Indexes for table `lawyer_ratings`
--
ALTER TABLE `lawyer_ratings`
  ADD PRIMARY KEY (`id`),
  ADD KEY `lawyer_id` (`lawyer_id`),
  ADD KEY `client_id` (`client_id`),
  ADD KEY `case_id` (`case_id`);

--
-- Indexes for table `lawyer_specializations`
--
ALTER TABLE `lawyer_specializations`
  ADD PRIMARY KEY (`id`),
  ADD KEY `lawyer_id` (`lawyer_id`);

--
-- Indexes for table `lawyer_specialties`
--
ALTER TABLE `lawyer_specialties`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `unique_lawyer_specialty` (`lawyer_id`,`specialty_id`),
  ADD KEY `specialty_id` (`specialty_id`);

--
-- Indexes for table `lawyer_workload`
--
ALTER TABLE `lawyer_workload`
  ADD PRIMARY KEY (`id`),
  ADD KEY `lawyer_id` (`lawyer_id`),
  ADD KEY `case_id` (`case_id`);

--
-- Indexes for table `legal_resources`
--
ALTER TABLE `legal_resources`
  ADD PRIMARY KEY (`id`),
  ADD KEY `created_by` (`created_by`),
  ADD KEY `office_id` (`office_id`);

--
-- Indexes for table `messages`
--
ALTER TABLE `messages`
  ADD PRIMARY KEY (`id`),
  ADD KEY `sender_id` (`sender_id`),
  ADD KEY `recipient_id` (`recipient_id`);

--
-- Indexes for table `message_reactions`
--
ALTER TABLE `message_reactions`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `unique_message_user` (`message_id`,`user_id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `message_threads`
--
ALTER TABLE `message_threads`
  ADD PRIMARY KEY (`id`),
  ADD KEY `created_by` (`created_by`);

--
-- Indexes for table `notifications`
--
ALTER TABLE `notifications`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `notification_preferences`
--
ALTER TABLE `notification_preferences`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `unique_user_type` (`user_id`,`type_id`),
  ADD KEY `type_id` (`type_id`);

--
-- Indexes for table `notification_priorities`
--
ALTER TABLE `notification_priorities`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `name` (`name`);

--
-- Indexes for table `notification_statuses`
--
ALTER TABLE `notification_statuses`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `name` (`name`);

--
-- Indexes for table `notification_types`
--
ALTER TABLE `notification_types`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `name` (`name`);

--
-- Indexes for table `offices`
--
ALTER TABLE `offices`
  ADD PRIMARY KEY (`id`),
  ADD KEY `manager_id` (`manager_id`);

--
-- Indexes for table `office_performances`
--
ALTER TABLE `office_performances`
  ADD PRIMARY KEY (`id`),
  ADD KEY `office_id` (`office_id`);

--
-- Indexes for table `office_templates`
--
ALTER TABLE `office_templates`
  ADD PRIMARY KEY (`id`),
  ADD KEY `office_id` (`office_id`);

--
-- Indexes for table `organizations`
--
ALTER TABLE `organizations`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `otp_verifications`
--
ALTER TABLE `otp_verifications`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `paralegals`
--
ALTER TABLE `paralegals`
  ADD PRIMARY KEY (`id`),
  ADD KEY `office_id` (`office_id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `permissions`
--
ALTER TABLE `permissions`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `permission_name` (`permission_name`);

--
-- Indexes for table `ratings`
--
ALTER TABLE `ratings`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `resource_analytics`
--
ALTER TABLE `resource_analytics`
  ADD PRIMARY KEY (`id`),
  ADD KEY `resource_id` (`resource_id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `resource_shares`
--
ALTER TABLE `resource_shares`
  ADD PRIMARY KEY (`id`),
  ADD KEY `resource_id` (`resource_id`),
  ADD KEY `shared_by_id` (`shared_by_id`),
  ADD KEY `shared_with_id` (`shared_with_id`);

--
-- Indexes for table `roles`
--
ALTER TABLE `roles`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `role_name` (`role_name`);

--
-- Indexes for table `role_permissions`
--
ALTER TABLE `role_permissions`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `unique_role_permission` (`role`,`permission`);

--
-- Indexes for table `security_logs`
--
ALTER TABLE `security_logs`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `service_requests`
--
ALTER TABLE `service_requests`
  ADD PRIMARY KEY (`id`),
  ADD KEY `client_id` (`client_id`),
  ADD KEY `assigned_lawyer_id` (`assigned_lawyer_id`);

--
-- Indexes for table `sessions`
--
ALTER TABLE `sessions`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `token` (`token`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `specialties`
--
ALTER TABLE `specialties`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `tasks`
--
ALTER TABLE `tasks`
  ADD PRIMARY KEY (`id`),
  ADD KEY `case_id` (`case_id`),
  ADD KEY `assigned_to` (`assigned_to`),
  ADD KEY `assigned_by` (`assigned_by`);

--
-- Indexes for table `teaching_metrics`
--
ALTER TABLE `teaching_metrics`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`),
  ADD KEY `schedule_id` (`schedule_id`);

--
-- Indexes for table `teaching_schedules`
--
ALTER TABLE `teaching_schedules`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `thread_participants`
--
ALTER TABLE `thread_participants`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `unique_thread_user` (`thread_id`,`user_id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `typing_status`
--
ALTER TABLE `typing_status`
  ADD PRIMARY KEY (`id`),
  ADD KEY `thread_id` (`thread_id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `username` (`username`),
  ADD UNIQUE KEY `email` (`email`),
  ADD KEY `idx_users_email` (`email`),
  ADD KEY `idx_users_phone` (`phone`),
  ADD KEY `idx_users_username` (`username`);

--
-- Indexes for table `user_activities`
--
ALTER TABLE `user_activities`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `user_preferences`
--
ALTER TABLE `user_preferences`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `user_id` (`user_id`);

--
-- Indexes for table `user_roles`
--
ALTER TABLE `user_roles`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `name` (`name`);

--
-- Indexes for table `user_roles_history`
--
ALTER TABLE `user_roles_history`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`),
  ADD KEY `changed_by` (`changed_by`);

--
-- Indexes for table `user_security_logs`
--
ALTER TABLE `user_security_logs`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `user_sessions`
--
ALTER TABLE `user_sessions`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `user_statuses`
--
ALTER TABLE `user_statuses`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `name` (`name`);

--
-- Indexes for table `verification_requests`
--
ALTER TABLE `verification_requests`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`),
  ADD KEY `reviewed_by_id` (`reviewed_by_id`);

--
-- Indexes for table `workload_metrics`
--
ALTER TABLE `workload_metrics`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `unique_user_date` (`user_id`,`date`);

--
-- Indexes for table `work_assignments`
--
ALTER TABLE `work_assignments`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`),
  ADD KEY `case_id` (`case_id`),
  ADD KEY `service_request_id` (`service_request_id`);

--
-- Indexes for table `work_schedules`
--
ALTER TABLE `work_schedules`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `activities`
--
ALTER TABLE `activities`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `agent_chats`
--
ALTER TABLE `agent_chats`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `agent_notifications`
--
ALTER TABLE `agent_notifications`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `appointments`
--
ALTER TABLE `appointments`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `audit_logs`
--
ALTER TABLE `audit_logs`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `backups`
--
ALTER TABLE `backups`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `billing`
--
ALTER TABLE `billing`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `block_records`
--
ALTER TABLE `block_records`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `calendar_events`
--
ALTER TABLE `calendar_events`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `cases`
--
ALTER TABLE `cases`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `case_activities`
--
ALTER TABLE `case_activities`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `case_assignments`
--
ALTER TABLE `case_assignments`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `case_documents`
--
ALTER TABLE `case_documents`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `case_notes`
--
ALTER TABLE `case_notes`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `clients`
--
ALTER TABLE `clients`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `client_profiles`
--
ALTER TABLE `client_profiles`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `coordinators`
--
ALTER TABLE `coordinators`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `coordinator_history`
--
ALTER TABLE `coordinator_history`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `documents`
--
ALTER TABLE `documents`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `events`
--
ALTER TABLE `events`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `event_participants`
--
ALTER TABLE `event_participants`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `lawyer_appointments`
--
ALTER TABLE `lawyer_appointments`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `lawyer_availability`
--
ALTER TABLE `lawyer_availability`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `lawyer_billing`
--
ALTER TABLE `lawyer_billing`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `lawyer_cases`
--
ALTER TABLE `lawyer_cases`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `lawyer_documents`
--
ALTER TABLE `lawyer_documents`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `lawyer_profiles`
--
ALTER TABLE `lawyer_profiles`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `lawyer_ratings`
--
ALTER TABLE `lawyer_ratings`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `lawyer_specializations`
--
ALTER TABLE `lawyer_specializations`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=31;

--
-- AUTO_INCREMENT for table `lawyer_specialties`
--
ALTER TABLE `lawyer_specialties`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `lawyer_workload`
--
ALTER TABLE `lawyer_workload`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `legal_resources`
--
ALTER TABLE `legal_resources`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `messages`
--
ALTER TABLE `messages`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `message_reactions`
--
ALTER TABLE `message_reactions`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `message_threads`
--
ALTER TABLE `message_threads`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `notifications`
--
ALTER TABLE `notifications`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `notification_preferences`
--
ALTER TABLE `notification_preferences`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `notification_priorities`
--
ALTER TABLE `notification_priorities`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=13;

--
-- AUTO_INCREMENT for table `notification_statuses`
--
ALTER TABLE `notification_statuses`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=16;

--
-- AUTO_INCREMENT for table `notification_types`
--
ALTER TABLE `notification_types`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=49;

--
-- AUTO_INCREMENT for table `offices`
--
ALTER TABLE `offices`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- AUTO_INCREMENT for table `office_performances`
--
ALTER TABLE `office_performances`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `office_templates`
--
ALTER TABLE `office_templates`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `organizations`
--
ALTER TABLE `organizations`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `otp_verifications`
--
ALTER TABLE `otp_verifications`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `paralegals`
--
ALTER TABLE `paralegals`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `permissions`
--
ALTER TABLE `permissions`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=115;

--
-- AUTO_INCREMENT for table `ratings`
--
ALTER TABLE `ratings`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `resource_analytics`
--
ALTER TABLE `resource_analytics`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `resource_shares`
--
ALTER TABLE `resource_shares`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `roles`
--
ALTER TABLE `roles`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=16;

--
-- AUTO_INCREMENT for table `role_permissions`
--
ALTER TABLE `role_permissions`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=31;

--
-- AUTO_INCREMENT for table `security_logs`
--
ALTER TABLE `security_logs`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `service_requests`
--
ALTER TABLE `service_requests`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `sessions`
--
ALTER TABLE `sessions`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `specialties`
--
ALTER TABLE `specialties`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=31;

--
-- AUTO_INCREMENT for table `tasks`
--
ALTER TABLE `tasks`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `teaching_metrics`
--
ALTER TABLE `teaching_metrics`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `teaching_schedules`
--
ALTER TABLE `teaching_schedules`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `thread_participants`
--
ALTER TABLE `thread_participants`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `typing_status`
--
ALTER TABLE `typing_status`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=25;

--
-- AUTO_INCREMENT for table `user_activities`
--
ALTER TABLE `user_activities`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `user_preferences`
--
ALTER TABLE `user_preferences`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `user_roles`
--
ALTER TABLE `user_roles`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=16;

--
-- AUTO_INCREMENT for table `user_roles_history`
--
ALTER TABLE `user_roles_history`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `user_security_logs`
--
ALTER TABLE `user_security_logs`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=24;

--
-- AUTO_INCREMENT for table `user_sessions`
--
ALTER TABLE `user_sessions`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `user_statuses`
--
ALTER TABLE `user_statuses`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=26;

--
-- AUTO_INCREMENT for table `verification_requests`
--
ALTER TABLE `verification_requests`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `workload_metrics`
--
ALTER TABLE `workload_metrics`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `work_assignments`
--
ALTER TABLE `work_assignments`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `work_schedules`
--
ALTER TABLE `work_schedules`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `activities`
--
ALTER TABLE `activities`
  ADD CONSTRAINT `activities_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`);

--
-- Constraints for table `agent_chats`
--
ALTER TABLE `agent_chats`
  ADD CONSTRAINT `agent_chats_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`);

--
-- Constraints for table `agent_notifications`
--
ALTER TABLE `agent_notifications`
  ADD CONSTRAINT `agent_notifications_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`);

--
-- Constraints for table `appointments`
--
ALTER TABLE `appointments`
  ADD CONSTRAINT `appointments_ibfk_1` FOREIGN KEY (`client_id`) REFERENCES `users` (`id`),
  ADD CONSTRAINT `appointments_ibfk_2` FOREIGN KEY (`coordinator_id`) REFERENCES `users` (`id`);

--
-- Constraints for table `audit_logs`
--
ALTER TABLE `audit_logs`
  ADD CONSTRAINT `audit_logs_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`);

--
-- Constraints for table `backups`
--
ALTER TABLE `backups`
  ADD CONSTRAINT `backups_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`);

--
-- Constraints for table `billing`
--
ALTER TABLE `billing`
  ADD CONSTRAINT `billing_ibfk_1` FOREIGN KEY (`case_id`) REFERENCES `cases` (`id`),
  ADD CONSTRAINT `billing_ibfk_2` FOREIGN KEY (`client_id`) REFERENCES `users` (`id`);

--
-- Constraints for table `block_records`
--
ALTER TABLE `block_records`
  ADD CONSTRAINT `block_records_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`);

--
-- Constraints for table `calendar_events`
--
ALTER TABLE `calendar_events`
  ADD CONSTRAINT `calendar_events_ibfk_1` FOREIGN KEY (`case_id`) REFERENCES `cases` (`id`),
  ADD CONSTRAINT `calendar_events_ibfk_2` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`);

--
-- Constraints for table `cases`
--
ALTER TABLE `cases`
  ADD CONSTRAINT `cases_ibfk_1` FOREIGN KEY (`client_id`) REFERENCES `users` (`id`),
  ADD CONSTRAINT `cases_ibfk_2` FOREIGN KEY (`lawyer_id`) REFERENCES `users` (`id`),
  ADD CONSTRAINT `cases_ibfk_3` FOREIGN KEY (`paralegal_id`) REFERENCES `users` (`id`),
  ADD CONSTRAINT `cases_ibfk_4` FOREIGN KEY (`office_id`) REFERENCES `offices` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `case_activities`
--
ALTER TABLE `case_activities`
  ADD CONSTRAINT `case_activities_ibfk_1` FOREIGN KEY (`case_id`) REFERENCES `cases` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `case_activities_ibfk_2` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `case_assignments`
--
ALTER TABLE `case_assignments`
  ADD CONSTRAINT `case_assignments_ibfk_1` FOREIGN KEY (`case_id`) REFERENCES `cases` (`id`),
  ADD CONSTRAINT `case_assignments_ibfk_2` FOREIGN KEY (`assigned_by_id`) REFERENCES `users` (`id`),
  ADD CONSTRAINT `case_assignments_ibfk_3` FOREIGN KEY (`assigned_to_id`) REFERENCES `users` (`id`);

--
-- Constraints for table `case_documents`
--
ALTER TABLE `case_documents`
  ADD CONSTRAINT `case_documents_ibfk_1` FOREIGN KEY (`case_id`) REFERENCES `cases` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `case_documents_ibfk_2` FOREIGN KEY (`uploaded_by`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `case_notes`
--
ALTER TABLE `case_notes`
  ADD CONSTRAINT `case_notes_ibfk_1` FOREIGN KEY (`case_id`) REFERENCES `cases` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `case_notes_ibfk_2` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `client_profiles`
--
ALTER TABLE `client_profiles`
  ADD CONSTRAINT `client_profiles_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`),
  ADD CONSTRAINT `client_profiles_ibfk_2` FOREIGN KEY (`office_id`) REFERENCES `offices` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `coordinators`
--
ALTER TABLE `coordinators`
  ADD CONSTRAINT `coordinators_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`);

--
-- Constraints for table `coordinator_history`
--
ALTER TABLE `coordinator_history`
  ADD CONSTRAINT `coordinator_history_ibfk_1` FOREIGN KEY (`coordinator_id`) REFERENCES `users` (`id`),
  ADD CONSTRAINT `coordinator_history_ibfk_2` FOREIGN KEY (`client_id`) REFERENCES `users` (`id`),
  ADD CONSTRAINT `coordinator_history_ibfk_3` FOREIGN KEY (`lawyer_id`) REFERENCES `users` (`id`);

--
-- Constraints for table `documents`
--
ALTER TABLE `documents`
  ADD CONSTRAINT `documents_ibfk_1` FOREIGN KEY (`case_id`) REFERENCES `cases` (`id`),
  ADD CONSTRAINT `documents_ibfk_2` FOREIGN KEY (`uploaded_by`) REFERENCES `users` (`id`);

--
-- Constraints for table `events`
--
ALTER TABLE `events`
  ADD CONSTRAINT `events_ibfk_1` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`),
  ADD CONSTRAINT `events_ibfk_2` FOREIGN KEY (`case_id`) REFERENCES `cases` (`id`);

--
-- Constraints for table `event_participants`
--
ALTER TABLE `event_participants`
  ADD CONSTRAINT `event_participants_ibfk_1` FOREIGN KEY (`event_id`) REFERENCES `events` (`id`),
  ADD CONSTRAINT `event_participants_ibfk_2` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`);

--
-- Constraints for table `lawyer_appointments`
--
ALTER TABLE `lawyer_appointments`
  ADD CONSTRAINT `lawyer_appointments_ibfk_1` FOREIGN KEY (`lawyer_id`) REFERENCES `lawyer_profiles` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `lawyer_appointments_ibfk_2` FOREIGN KEY (`client_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `lawyer_appointments_ibfk_3` FOREIGN KEY (`case_id`) REFERENCES `cases` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `lawyer_availability`
--
ALTER TABLE `lawyer_availability`
  ADD CONSTRAINT `lawyer_availability_ibfk_1` FOREIGN KEY (`lawyer_id`) REFERENCES `lawyer_profiles` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `lawyer_billing`
--
ALTER TABLE `lawyer_billing`
  ADD CONSTRAINT `lawyer_billing_ibfk_1` FOREIGN KEY (`lawyer_id`) REFERENCES `lawyer_profiles` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `lawyer_billing_ibfk_2` FOREIGN KEY (`case_id`) REFERENCES `cases` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `lawyer_billing_ibfk_3` FOREIGN KEY (`client_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `lawyer_cases`
--
ALTER TABLE `lawyer_cases`
  ADD CONSTRAINT `lawyer_cases_ibfk_1` FOREIGN KEY (`lawyer_id`) REFERENCES `lawyer_profiles` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `lawyer_cases_ibfk_2` FOREIGN KEY (`case_id`) REFERENCES `cases` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `lawyer_documents`
--
ALTER TABLE `lawyer_documents`
  ADD CONSTRAINT `lawyer_documents_ibfk_1` FOREIGN KEY (`lawyer_id`) REFERENCES `lawyer_profiles` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `lawyer_profiles`
--
ALTER TABLE `lawyer_profiles`
  ADD CONSTRAINT `lawyer_profiles_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `lawyer_profiles_ibfk_2` FOREIGN KEY (`office_id`) REFERENCES `offices` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `lawyer_ratings`
--
ALTER TABLE `lawyer_ratings`
  ADD CONSTRAINT `lawyer_ratings_ibfk_1` FOREIGN KEY (`lawyer_id`) REFERENCES `lawyer_profiles` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `lawyer_ratings_ibfk_2` FOREIGN KEY (`client_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `lawyer_ratings_ibfk_3` FOREIGN KEY (`case_id`) REFERENCES `cases` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `lawyer_specializations`
--
ALTER TABLE `lawyer_specializations`
  ADD CONSTRAINT `lawyer_specializations_ibfk_1` FOREIGN KEY (`lawyer_id`) REFERENCES `lawyer_profiles` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `lawyer_specialties`
--
ALTER TABLE `lawyer_specialties`
  ADD CONSTRAINT `lawyer_specialties_ibfk_1` FOREIGN KEY (`lawyer_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `lawyer_specialties_ibfk_2` FOREIGN KEY (`specialty_id`) REFERENCES `specialties` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `lawyer_workload`
--
ALTER TABLE `lawyer_workload`
  ADD CONSTRAINT `lawyer_workload_ibfk_1` FOREIGN KEY (`lawyer_id`) REFERENCES `lawyer_profiles` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `lawyer_workload_ibfk_2` FOREIGN KEY (`case_id`) REFERENCES `cases` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `legal_resources`
--
ALTER TABLE `legal_resources`
  ADD CONSTRAINT `legal_resources_ibfk_1` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`),
  ADD CONSTRAINT `legal_resources_ibfk_2` FOREIGN KEY (`office_id`) REFERENCES `offices` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `messages`
--
ALTER TABLE `messages`
  ADD CONSTRAINT `messages_ibfk_1` FOREIGN KEY (`sender_id`) REFERENCES `users` (`id`),
  ADD CONSTRAINT `messages_ibfk_2` FOREIGN KEY (`recipient_id`) REFERENCES `users` (`id`);

--
-- Constraints for table `message_reactions`
--
ALTER TABLE `message_reactions`
  ADD CONSTRAINT `message_reactions_ibfk_1` FOREIGN KEY (`message_id`) REFERENCES `messages` (`id`),
  ADD CONSTRAINT `message_reactions_ibfk_2` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`);

--
-- Constraints for table `message_threads`
--
ALTER TABLE `message_threads`
  ADD CONSTRAINT `message_threads_ibfk_1` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`);

--
-- Constraints for table `notifications`
--
ALTER TABLE `notifications`
  ADD CONSTRAINT `notifications_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`);

--
-- Constraints for table `notification_preferences`
--
ALTER TABLE `notification_preferences`
  ADD CONSTRAINT `notification_preferences_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`),
  ADD CONSTRAINT `notification_preferences_ibfk_2` FOREIGN KEY (`type_id`) REFERENCES `notification_types` (`id`);

--
-- Constraints for table `offices`
--
ALTER TABLE `offices`
  ADD CONSTRAINT `offices_ibfk_1` FOREIGN KEY (`manager_id`) REFERENCES `users` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `office_performances`
--
ALTER TABLE `office_performances`
  ADD CONSTRAINT `office_performances_ibfk_1` FOREIGN KEY (`office_id`) REFERENCES `offices` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `office_templates`
--
ALTER TABLE `office_templates`
  ADD CONSTRAINT `office_templates_ibfk_1` FOREIGN KEY (`office_id`) REFERENCES `offices` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `otp_verifications`
--
ALTER TABLE `otp_verifications`
  ADD CONSTRAINT `otp_verifications_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`);

--
-- Constraints for table `paralegals`
--
ALTER TABLE `paralegals`
  ADD CONSTRAINT `paralegals_ibfk_1` FOREIGN KEY (`office_id`) REFERENCES `offices` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `paralegals_ibfk_2` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `ratings`
--
ALTER TABLE `ratings`
  ADD CONSTRAINT `ratings_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`);

--
-- Constraints for table `resource_analytics`
--
ALTER TABLE `resource_analytics`
  ADD CONSTRAINT `resource_analytics_ibfk_1` FOREIGN KEY (`resource_id`) REFERENCES `legal_resources` (`id`),
  ADD CONSTRAINT `resource_analytics_ibfk_2` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`);

--
-- Constraints for table `resource_shares`
--
ALTER TABLE `resource_shares`
  ADD CONSTRAINT `resource_shares_ibfk_1` FOREIGN KEY (`resource_id`) REFERENCES `legal_resources` (`id`),
  ADD CONSTRAINT `resource_shares_ibfk_2` FOREIGN KEY (`shared_by_id`) REFERENCES `users` (`id`),
  ADD CONSTRAINT `resource_shares_ibfk_3` FOREIGN KEY (`shared_with_id`) REFERENCES `users` (`id`);

--
-- Constraints for table `security_logs`
--
ALTER TABLE `security_logs`
  ADD CONSTRAINT `security_logs_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`);

--
-- Constraints for table `service_requests`
--
ALTER TABLE `service_requests`
  ADD CONSTRAINT `service_requests_ibfk_1` FOREIGN KEY (`client_id`) REFERENCES `users` (`id`),
  ADD CONSTRAINT `service_requests_ibfk_2` FOREIGN KEY (`assigned_lawyer_id`) REFERENCES `users` (`id`);

--
-- Constraints for table `sessions`
--
ALTER TABLE `sessions`
  ADD CONSTRAINT `sessions_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`);

--
-- Constraints for table `tasks`
--
ALTER TABLE `tasks`
  ADD CONSTRAINT `tasks_ibfk_1` FOREIGN KEY (`case_id`) REFERENCES `cases` (`id`),
  ADD CONSTRAINT `tasks_ibfk_2` FOREIGN KEY (`assigned_to`) REFERENCES `users` (`id`),
  ADD CONSTRAINT `tasks_ibfk_3` FOREIGN KEY (`assigned_by`) REFERENCES `users` (`id`);

--
-- Constraints for table `teaching_metrics`
--
ALTER TABLE `teaching_metrics`
  ADD CONSTRAINT `teaching_metrics_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`),
  ADD CONSTRAINT `teaching_metrics_ibfk_2` FOREIGN KEY (`schedule_id`) REFERENCES `teaching_schedules` (`id`);

--
-- Constraints for table `teaching_schedules`
--
ALTER TABLE `teaching_schedules`
  ADD CONSTRAINT `teaching_schedules_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`);

--
-- Constraints for table `thread_participants`
--
ALTER TABLE `thread_participants`
  ADD CONSTRAINT `thread_participants_ibfk_1` FOREIGN KEY (`thread_id`) REFERENCES `message_threads` (`id`),
  ADD CONSTRAINT `thread_participants_ibfk_2` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`);

--
-- Constraints for table `typing_status`
--
ALTER TABLE `typing_status`
  ADD CONSTRAINT `typing_status_ibfk_1` FOREIGN KEY (`thread_id`) REFERENCES `message_threads` (`id`),
  ADD CONSTRAINT `typing_status_ibfk_2` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`);

--
-- Constraints for table `user_activities`
--
ALTER TABLE `user_activities`
  ADD CONSTRAINT `user_activities_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `user_preferences`
--
ALTER TABLE `user_preferences`
  ADD CONSTRAINT `user_preferences_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `user_roles_history`
--
ALTER TABLE `user_roles_history`
  ADD CONSTRAINT `user_roles_history_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `user_roles_history_ibfk_2` FOREIGN KEY (`changed_by`) REFERENCES `users` (`id`);

--
-- Constraints for table `user_security_logs`
--
ALTER TABLE `user_security_logs`
  ADD CONSTRAINT `user_security_logs_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `user_sessions`
--
ALTER TABLE `user_sessions`
  ADD CONSTRAINT `user_sessions_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `verification_requests`
--
ALTER TABLE `verification_requests`
  ADD CONSTRAINT `verification_requests_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`),
  ADD CONSTRAINT `verification_requests_ibfk_2` FOREIGN KEY (`reviewed_by_id`) REFERENCES `users` (`id`);

--
-- Constraints for table `workload_metrics`
--
ALTER TABLE `workload_metrics`
  ADD CONSTRAINT `workload_metrics_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`);

--
-- Constraints for table `work_assignments`
--
ALTER TABLE `work_assignments`
  ADD CONSTRAINT `work_assignments_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`),
  ADD CONSTRAINT `work_assignments_ibfk_2` FOREIGN KEY (`case_id`) REFERENCES `cases` (`id`),
  ADD CONSTRAINT `work_assignments_ibfk_3` FOREIGN KEY (`service_request_id`) REFERENCES `service_requests` (`id`);

--
-- Constraints for table `work_schedules`
--
ALTER TABLE `work_schedules`
  ADD CONSTRAINT `work_schedules_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;

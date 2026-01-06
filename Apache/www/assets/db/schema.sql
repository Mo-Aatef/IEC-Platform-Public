-- phpMyAdmin SQL Dump
-- version 5.2.3
-- https://www.phpmyadmin.net/
--
-- Host: dbserver
-- Generation Time: Jan 05, 2026 at 03:30 PM
-- Server version: 11.8.5-MariaDB-ubu2404
-- PHP Version: 8.3.26

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `iec_platform`
--

-- --------------------------------------------------------

--
-- Table structure for table `announcements`
--

DROP TABLE IF EXISTS `announcements`;
CREATE TABLE IF NOT EXISTS `announcements` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `author_id` int(11) NOT NULL,
  `group_id` int(11) DEFAULT NULL COMMENT 'NULL = Global Announcement, ID = Class Specific',
  `title` varchar(255) NOT NULL,
  `message` text NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `is_urgent` tinyint(1) DEFAULT 0,
  PRIMARY KEY (`id`),
  KEY `author_id` (`author_id`),
  KEY `group_id` (`group_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `groups`
--

DROP TABLE IF EXISTS `groups`;
CREATE TABLE IF NOT EXISTS `groups` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(100) NOT NULL COMMENT 'e.g., Batch A - Oct 2025',
  `tutor_id` int(11) DEFAULT NULL COMMENT 'The assigned Tutor',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `tutor_id` (`tutor_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `lessons`
--

DROP TABLE IF EXISTS `lessons`;
CREATE TABLE IF NOT EXISTS `lessons` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `module_id` int(11) NOT NULL,
  `day_number` tinyint(1) NOT NULL COMMENT '1 to 6',
  `title` varchar(255) NOT NULL,
  `description` varchar(255) DEFAULT NULL,
  `is_unlocked` tinyint(1) DEFAULT 0,
  PRIMARY KEY (`id`),
  UNIQUE KEY `unique_module_day` (`module_id`,`day_number`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `lessons`
--

INSERT INTO `lessons` (`id`, `module_id`, `day_number`, `title`, `description`, `is_unlocked`) VALUES
(1, 1, 1, 'Day 1: Introduction & Warmup', 'Start the week with key concepts.', 1),
(2, 2, 1, 'Day 1: Introduction & Warmup', 'Start the week with key concepts.', 1),
(3, 3, 1, 'Day 1: Introduction & Warmup', 'Start the week with key concepts.', 1),
(4, 4, 1, 'Day 1: Introduction & Warmup', 'Start the week with key concepts.', 1),
(5, 5, 1, 'Day 1: Introduction & Warmup', 'Start the week with key concepts.', 1),
(6, 6, 1, 'Day 1: Introduction & Warmup', 'Start the week with key concepts.', 1),
(7, 7, 1, 'Day 1: Introduction & Warmup', 'Start the week with key concepts.', 1),
(8, 8, 1, 'Day 1: Introduction & Warmup', 'Start the week with key concepts.', 1),
(9, 9, 1, 'Day 1: Introduction & Warmup', 'Start the week with key concepts.', 1),
(10, 10, 1, 'Day 1: Introduction & Warmup', 'Start the week with key concepts.', 1),
(11, 11, 1, 'Day 1: Introduction & Warmup', 'Start the week with key concepts.', 1),
(12, 12, 1, 'Day 1: Introduction & Warmup', 'Start the week with key concepts.', 1),
(13, 13, 1, 'Day 1: Introduction & Warmup', 'Start the week with key concepts.', 1),
(14, 14, 1, 'Day 1: Introduction & Warmup', 'Start the week with key concepts.', 1),
(15, 15, 1, 'Day 1: Introduction & Warmup', 'Start the week with key concepts.', 1),
(16, 16, 1, 'Day 1: Introduction & Warmup', 'Start the week with key concepts.', 1),
(17, 17, 1, 'Day 1: Introduction & Warmup', 'Start the week with key concepts.', 1),
(18, 18, 1, 'Day 1: Introduction & Warmup', 'Start the week with key concepts.', 1),
(19, 19, 1, 'Day 1: Introduction & Warmup', 'Start the week with key concepts.', 1),
(20, 20, 1, 'Day 1: Introduction & Warmup', 'Start the week with key concepts.', 1),
(21, 21, 1, 'Day 1: Introduction & Warmup', 'Start the week with key concepts.', 1),
(22, 22, 1, 'Day 1: Introduction & Warmup', 'Start the week with key concepts.', 1),
(23, 23, 1, 'Day 1: Introduction & Warmup', 'Start the week with key concepts.', 1),
(24, 24, 1, 'Day 1: Introduction & Warmup', 'Start the week with key concepts.', 1),
(25, 25, 1, 'Day 1: Introduction & Warmup', 'Start the week with key concepts.', 1),
(26, 26, 1, 'Day 1: Introduction & Warmup', 'Start the week with key concepts.', 1),
(27, 27, 1, 'Day 1: Introduction & Warmup', 'Start the week with key concepts.', 1),
(28, 28, 1, 'Day 1: Introduction & Warmup', 'Start the week with key concepts.', 0),
(29, 1, 2, 'Day 2: Core Lesson', 'Deep dive into the weekly topic.', 1),
(30, 2, 2, 'Day 2: Core Lesson', 'Deep dive into the weekly topic.', 0),
(31, 3, 2, 'Day 2: Core Lesson', 'Deep dive into the weekly topic.', 0),
(32, 4, 2, 'Day 2: Core Lesson', 'Deep dive into the weekly topic.', 0),
(33, 5, 2, 'Day 2: Core Lesson', 'Deep dive into the weekly topic.', 0),
(34, 6, 2, 'Day 2: Core Lesson', 'Deep dive into the weekly topic.', 0),
(35, 7, 2, 'Day 2: Core Lesson', 'Deep dive into the weekly topic.', 0),
(36, 8, 2, 'Day 2: Core Lesson', 'Deep dive into the weekly topic.', 0),
(37, 9, 2, 'Day 2: Core Lesson', 'Deep dive into the weekly topic.', 0),
(38, 10, 2, 'Day 2: Core Lesson', 'Deep dive into the weekly topic.', 0),
(39, 11, 2, 'Day 2: Core Lesson', 'Deep dive into the weekly topic.', 0),
(40, 12, 2, 'Day 2: Core Lesson', 'Deep dive into the weekly topic.', 0),
(41, 13, 2, 'Day 2: Core Lesson', 'Deep dive into the weekly topic.', 0),
(42, 14, 2, 'Day 2: Core Lesson', 'Deep dive into the weekly topic.', 0),
(43, 15, 2, 'Day 2: Core Lesson', 'Deep dive into the weekly topic.', 0),
(44, 16, 2, 'Day 2: Core Lesson', 'Deep dive into the weekly topic.', 0),
(45, 17, 2, 'Day 2: Core Lesson', 'Deep dive into the weekly topic.', 0),
(46, 18, 2, 'Day 2: Core Lesson', 'Deep dive into the weekly topic.', 0),
(47, 19, 2, 'Day 2: Core Lesson', 'Deep dive into the weekly topic.', 0),
(48, 20, 2, 'Day 2: Core Lesson', 'Deep dive into the weekly topic.', 0),
(49, 21, 2, 'Day 2: Core Lesson', 'Deep dive into the weekly topic.', 0),
(50, 22, 2, 'Day 2: Core Lesson', 'Deep dive into the weekly topic.', 0),
(51, 23, 2, 'Day 2: Core Lesson', 'Deep dive into the weekly topic.', 0),
(52, 24, 2, 'Day 2: Core Lesson', 'Deep dive into the weekly topic.', 0),
(53, 25, 2, 'Day 2: Core Lesson', 'Deep dive into the weekly topic.', 0),
(54, 26, 2, 'Day 2: Core Lesson', 'Deep dive into the weekly topic.', 0),
(55, 27, 2, 'Day 2: Core Lesson', 'Deep dive into the weekly topic.', 0),
(56, 28, 2, 'Day 2: Core Lesson', 'Deep dive into the weekly topic.', 0),
(57, 1, 3, 'Day 3: Practice & Application', 'Apply what you have learned.', 0),
(58, 2, 3, 'Day 3: Practice & Application', 'Apply what you have learned.', 0),
(59, 3, 3, 'Day 3: Practice & Application', 'Apply what you have learned.', 0),
(60, 4, 3, 'Day 3: Practice & Application', 'Apply what you have learned.', 0),
(61, 5, 3, 'Day 3: Practice & Application', 'Apply what you have learned.', 0),
(62, 6, 3, 'Day 3: Practice & Application', 'Apply what you have learned.', 0),
(63, 7, 3, 'Day 3: Practice & Application', 'Apply what you have learned.', 0),
(64, 8, 3, 'Day 3: Practice & Application', 'Apply what you have learned.', 0),
(65, 9, 3, 'Day 3: Practice & Application', 'Apply what you have learned.', 0),
(66, 10, 3, 'Day 3: Practice & Application', 'Apply what you have learned.', 0),
(67, 11, 3, 'Day 3: Practice & Application', 'Apply what you have learned.', 0),
(68, 12, 3, 'Day 3: Practice & Application', 'Apply what you have learned.', 0),
(69, 13, 3, 'Day 3: Practice & Application', 'Apply what you have learned.', 0),
(70, 14, 3, 'Day 3: Practice & Application', 'Apply what you have learned.', 0),
(71, 15, 3, 'Day 3: Practice & Application', 'Apply what you have learned.', 0),
(72, 16, 3, 'Day 3: Practice & Application', 'Apply what you have learned.', 0),
(73, 17, 3, 'Day 3: Practice & Application', 'Apply what you have learned.', 0),
(74, 18, 3, 'Day 3: Practice & Application', 'Apply what you have learned.', 0),
(75, 19, 3, 'Day 3: Practice & Application', 'Apply what you have learned.', 0),
(76, 20, 3, 'Day 3: Practice & Application', 'Apply what you have learned.', 0),
(77, 21, 3, 'Day 3: Practice & Application', 'Apply what you have learned.', 0),
(78, 22, 3, 'Day 3: Practice & Application', 'Apply what you have learned.', 0),
(79, 23, 3, 'Day 3: Practice & Application', 'Apply what you have learned.', 0),
(80, 24, 3, 'Day 3: Practice & Application', 'Apply what you have learned.', 0),
(81, 25, 3, 'Day 3: Practice & Application', 'Apply what you have learned.', 0),
(82, 26, 3, 'Day 3: Practice & Application', 'Apply what you have learned.', 0),
(83, 27, 3, 'Day 3: Practice & Application', 'Apply what you have learned.', 0),
(84, 28, 3, 'Day 3: Practice & Application', 'Apply what you have learned.', 0),
(85, 1, 4, 'Day 4: Communication Skills', 'Focus on speaking and listening.', 0),
(86, 2, 4, 'Day 4: Communication Skills', 'Focus on speaking and listening.', 0),
(87, 3, 4, 'Day 4: Communication Skills', 'Focus on speaking and listening.', 0),
(88, 4, 4, 'Day 4: Communication Skills', 'Focus on speaking and listening.', 0),
(89, 5, 4, 'Day 4: Communication Skills', 'Focus on speaking and listening.', 0),
(90, 6, 4, 'Day 4: Communication Skills', 'Focus on speaking and listening.', 0),
(91, 7, 4, 'Day 4: Communication Skills', 'Focus on speaking and listening.', 0),
(92, 8, 4, 'Day 4: Communication Skills', 'Focus on speaking and listening.', 0),
(93, 9, 4, 'Day 4: Communication Skills', 'Focus on speaking and listening.', 0),
(94, 10, 4, 'Day 4: Communication Skills', 'Focus on speaking and listening.', 0),
(95, 11, 4, 'Day 4: Communication Skills', 'Focus on speaking and listening.', 0),
(96, 12, 4, 'Day 4: Communication Skills', 'Focus on speaking and listening.', 0),
(97, 13, 4, 'Day 4: Communication Skills', 'Focus on speaking and listening.', 0),
(98, 14, 4, 'Day 4: Communication Skills', 'Focus on speaking and listening.', 0),
(99, 15, 4, 'Day 4: Communication Skills', 'Focus on speaking and listening.', 0),
(100, 16, 4, 'Day 4: Communication Skills', 'Focus on speaking and listening.', 0),
(101, 17, 4, 'Day 4: Communication Skills', 'Focus on speaking and listening.', 0),
(102, 18, 4, 'Day 4: Communication Skills', 'Focus on speaking and listening.', 0),
(103, 19, 4, 'Day 4: Communication Skills', 'Focus on speaking and listening.', 0),
(104, 20, 4, 'Day 4: Communication Skills', 'Focus on speaking and listening.', 0),
(105, 21, 4, 'Day 4: Communication Skills', 'Focus on speaking and listening.', 0),
(106, 22, 4, 'Day 4: Communication Skills', 'Focus on speaking and listening.', 0),
(107, 23, 4, 'Day 4: Communication Skills', 'Focus on speaking and listening.', 0),
(108, 24, 4, 'Day 4: Communication Skills', 'Focus on speaking and listening.', 0),
(109, 25, 4, 'Day 4: Communication Skills', 'Focus on speaking and listening.', 0),
(110, 26, 4, 'Day 4: Communication Skills', 'Focus on speaking and listening.', 0),
(111, 27, 4, 'Day 4: Communication Skills', 'Focus on speaking and listening.', 0),
(112, 28, 4, 'Day 4: Communication Skills', 'Focus on speaking and listening.', 0),
(113, 1, 5, 'Day 5: Real-world Scenario', 'Case studies and roleplay prep.', 0),
(114, 2, 5, 'Day 5: Real-world Scenario', 'Case studies and roleplay prep.', 0),
(115, 3, 5, 'Day 5: Real-world Scenario', 'Case studies and roleplay prep.', 0),
(116, 4, 5, 'Day 5: Real-world Scenario', 'Case studies and roleplay prep.', 0),
(117, 5, 5, 'Day 5: Real-world Scenario', 'Case studies and roleplay prep.', 0),
(118, 6, 5, 'Day 5: Real-world Scenario', 'Case studies and roleplay prep.', 0),
(119, 7, 5, 'Day 5: Real-world Scenario', 'Case studies and roleplay prep.', 0),
(120, 8, 5, 'Day 5: Real-world Scenario', 'Case studies and roleplay prep.', 0),
(121, 9, 5, 'Day 5: Real-world Scenario', 'Case studies and roleplay prep.', 0),
(122, 10, 5, 'Day 5: Real-world Scenario', 'Case studies and roleplay prep.', 0),
(123, 11, 5, 'Day 5: Real-world Scenario', 'Case studies and roleplay prep.', 0),
(124, 12, 5, 'Day 5: Real-world Scenario', 'Case studies and roleplay prep.', 0),
(125, 13, 5, 'Day 5: Real-world Scenario', 'Case studies and roleplay prep.', 0),
(126, 14, 5, 'Day 5: Real-world Scenario', 'Case studies and roleplay prep.', 0),
(127, 15, 5, 'Day 5: Real-world Scenario', 'Case studies and roleplay prep.', 0),
(128, 16, 5, 'Day 5: Real-world Scenario', 'Case studies and roleplay prep.', 0),
(129, 17, 5, 'Day 5: Real-world Scenario', 'Case studies and roleplay prep.', 0),
(130, 18, 5, 'Day 5: Real-world Scenario', 'Case studies and roleplay prep.', 0),
(131, 19, 5, 'Day 5: Real-world Scenario', 'Case studies and roleplay prep.', 0),
(132, 20, 5, 'Day 5: Real-world Scenario', 'Case studies and roleplay prep.', 0),
(133, 21, 5, 'Day 5: Real-world Scenario', 'Case studies and roleplay prep.', 0),
(134, 22, 5, 'Day 5: Real-world Scenario', 'Case studies and roleplay prep.', 0),
(135, 23, 5, 'Day 5: Real-world Scenario', 'Case studies and roleplay prep.', 0),
(136, 24, 5, 'Day 5: Real-world Scenario', 'Case studies and roleplay prep.', 0),
(137, 25, 5, 'Day 5: Real-world Scenario', 'Case studies and roleplay prep.', 0),
(138, 26, 5, 'Day 5: Real-world Scenario', 'Case studies and roleplay prep.', 0),
(139, 27, 5, 'Day 5: Real-world Scenario', 'Case studies and roleplay prep.', 0),
(140, 28, 5, 'Day 5: Real-world Scenario', 'Case studies and roleplay prep.', 0),
(141, 1, 6, 'Day 6: Weekly Assessment', 'Review and final quiz.', 0),
(142, 2, 6, 'Day 6: Weekly Assessment', 'Review and final quiz.', 0),
(143, 3, 6, 'Day 6: Weekly Assessment', 'Review and final quiz.', 0),
(144, 4, 6, 'Day 6: Weekly Assessment', 'Review and final quiz.', 0),
(145, 5, 6, 'Day 6: Weekly Assessment', 'Review and final quiz.', 0),
(146, 6, 6, 'Day 6: Weekly Assessment', 'Review and final quiz.', 0),
(147, 7, 6, 'Day 6: Weekly Assessment', 'Review and final quiz.', 0),
(148, 8, 6, 'Day 6: Weekly Assessment', 'Review and final quiz.', 0),
(149, 9, 6, 'Day 6: Weekly Assessment', 'Review and final quiz.', 0),
(150, 10, 6, 'Day 6: Weekly Assessment', 'Review and final quiz.', 0),
(151, 11, 6, 'Day 6: Weekly Assessment', 'Review and final quiz.', 0),
(152, 12, 6, 'Day 6: Weekly Assessment', 'Review and final quiz.', 0),
(153, 13, 6, 'Day 6: Weekly Assessment', 'Review and final quiz.', 0),
(154, 14, 6, 'Day 6: Weekly Assessment', 'Review and final quiz.', 0),
(155, 15, 6, 'Day 6: Weekly Assessment', 'Review and final quiz.', 0),
(156, 16, 6, 'Day 6: Weekly Assessment', 'Review and final quiz.', 0),
(157, 17, 6, 'Day 6: Weekly Assessment', 'Review and final quiz.', 0),
(158, 18, 6, 'Day 6: Weekly Assessment', 'Review and final quiz.', 0),
(159, 19, 6, 'Day 6: Weekly Assessment', 'Review and final quiz.', 0),
(160, 20, 6, 'Day 6: Weekly Assessment', 'Review and final quiz.', 0),
(161, 21, 6, 'Day 6: Weekly Assessment', 'Review and final quiz.', 0),
(162, 22, 6, 'Day 6: Weekly Assessment', 'Review and final quiz.', 0),
(163, 23, 6, 'Day 6: Weekly Assessment', 'Review and final quiz.', 0),
(164, 24, 6, 'Day 6: Weekly Assessment', 'Review and final quiz.', 0),
(165, 25, 6, 'Day 6: Weekly Assessment', 'Review and final quiz.', 0),
(166, 26, 6, 'Day 6: Weekly Assessment', 'Review and final quiz.', 0),
(167, 27, 6, 'Day 6: Weekly Assessment', 'Review and final quiz.', 0),
(168, 28, 6, 'Day 6: Weekly Assessment', 'Review and final quiz.', 0);


-- --------------------------------------------------------

--
-- Table structure for table `modules`
--

DROP TABLE IF EXISTS `modules`;
CREATE TABLE IF NOT EXISTS `modules` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `title` varchar(255) NOT NULL,
  `description` text DEFAULT NULL,
  `module_number` int(11) NOT NULL COMMENT 'Order: 1 to 28',
  `is_global_locked` tinyint(1) DEFAULT 1 COMMENT '1 = Locked by Admin, 0 = Open',
  `warmup_id` int(11) DEFAULT NULL,
  `watch_id` int(11) DEFAULT NULL,
  `quiz_id` int(11) DEFAULT NULL,
  `speaking_id` int(11) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `modules`
--

INSERT INTO `modules` (`id`, `title`, `description`, `module_number`, `is_global_locked`, `warmup_id`, `watch_id`, `quiz_id`, `speaking_id`) VALUES
(1, 'Introduction to Business English', 'Learn the foundations of professional communication and cultural expectations in the business world.', 1, 0, 41, 42, 47, 48),
(2, 'Email Basics', 'Master the structure of formal emails, subject lines, and professional greetings and sign-offs.', 2, 1, 1, 2, 49, 50),
(3, 'Phone Etiquette', 'How to answer calls professionally, take messages, and handle transfers effectively.', 3, 1, 5, 6, 7, 8),
(4, 'Meeting Basics', 'Essential vocabulary for scheduling, attending, and participating in business meetings.', 4, 1, 9, 10, 11, 12),
(5, 'Small Talk', 'Build rapport with colleagues and clients using appropriate social conversation starters.', 5, 1, 13, 14, 15, 16),
(6, 'Presentations I', 'Introduction to structuring a presentation and using signposting language.', 6, 1, 17, 18, 19, 20),
(7, 'Negotiations', 'Key phrases for making offers, accepting, refusing, and reaching a compromise.', 7, 1, 21, 22, 23, 24),
(8, 'Customer Service', 'How to handle inquiries, complaints, and maintain a positive customer relationship.', 8, 1, 25, 26, 27, 28),
(9, 'Report Writing', 'Techniques for writing clear, concise, and structured business reports.', 9, 1, 29, 30, 31, 32),
(10, 'Social Business', 'Navigating business lunches, networking events, and after-work social situations.', 10, 1, 33, 34, 35, 36),
(11, 'Project Management', 'Vocabulary for planning, milestones, deadlines, and team coordination.', 11, 1, NULL, NULL, NULL, NULL),
(12, 'Business Communication Essentials', 'Advanced strategies for clear verbal and non-verbal communication in the office.', 12, 1, NULL, NULL, NULL, NULL),
(13, 'Presentation Skills', 'Advanced techniques for engaging audiences, handling Q&A, and visual aids.', 13, 1, NULL, NULL, NULL, NULL),
(14, 'Advanced Email', 'Writing for sensitive situations, tone management, and persuasive email strategies.', 14, 1, NULL, NULL, NULL, NULL),
(15, 'Crisis Communication', 'How to communicate effectively and calmly during business emergencies or PR crises.', 15, 1, NULL, NULL, NULL, NULL),
(16, 'Team Communication', 'Best practices for internal communication, feedback, and conflict resolution.', 16, 1, NULL, NULL, NULL, NULL),
(17, 'Leadership Language', 'Phrases and tone used by effective leaders to motivate and direct teams.', 17, 1, NULL, NULL, NULL, NULL),
(18, 'International Business', 'Understanding global business etiquette and cross-cultural communication nuances.', 18, 1, NULL, NULL, NULL, NULL),
(19, 'Digital Communication', 'Etiquette for instant messaging, Slack/Teams, and video conferencing tools.', 19, 1, NULL, NULL, NULL, NULL),
(20, 'Marketing Language', 'Core vocabulary for branding, advertising, and market analysis discussions.', 20, 1, NULL, NULL, NULL, NULL),
(21, 'Finance Terms', 'Essential financial vocabulary for non-finance managers and business reports.', 21, 1, NULL, NULL, NULL, NULL),
(22, 'HR Communication', 'Discussing hiring, performance reviews, and workplace policies professionally.', 22, 1, NULL, NULL, NULL, NULL),
(23, 'Sales Techniques', 'Persuasive language for pitching products, overcoming objections, and closing deals.', 23, 1, NULL, NULL, NULL, NULL),
(24, 'Legal English', 'Basic understanding of contracts, liability, and formal legal terminology in business.', 24, 1, NULL, NULL, NULL, NULL),
(25, 'Tech Industry', 'Vocabulary related to software, development methodologies, and tech trends.', 25, 1, NULL, NULL, NULL, NULL),
(26, 'Cross-Cultural Communication', 'Deep dive into cultural dimensions and adapting communication styles globally.', 26, 1, NULL, NULL, NULL, NULL),
(27, 'Advanced Speaking', 'Mastering fluency, intonation, and public speaking in high-stakes environments.', 27, 1, NULL, NULL, NULL, NULL),
(28, 'Final Review', 'Comprehensive review of all topics covered and final assessment preparation.', 28, 1, 37, 38, 39, 40);

-- --------------------------------------------------------

--
-- Table structure for table `module_steps`
--

DROP TABLE IF EXISTS `module_steps`;
CREATE TABLE IF NOT EXISTS `module_steps` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `module_id` int(11) NOT NULL,
  `lesson_id` int(11) DEFAULT NULL,
  `step_order` int(11) NOT NULL COMMENT '1=Warmup, 2=Watch, 3=Practice, 4=Speak',
  `step_type` enum('warmup','watch','practice','speak') NOT NULL,
  `title` varchar(255) NOT NULL,
  `content_data` text DEFAULT NULL COMMENT 'JSON or Text: Video URL, PDF path, or Instructions',
  PRIMARY KEY (`id`),
  KEY `module_id` (`module_id`),
  KEY `steps_lesson_fk` (`lesson_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `offline_sessions`
--

DROP TABLE IF EXISTS `offline_sessions`;
CREATE TABLE IF NOT EXISTS `offline_sessions` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `module_id` int(11) NOT NULL,
  `title` varchar(255) DEFAULT 'Weekly Review & Practice Session',
  `session_date` date DEFAULT NULL,
  `start_time` time DEFAULT '10:00:00',
  `end_time` time DEFAULT '12:00:00',
  `location` varchar(255) DEFAULT 'Main Hall',
  `notes` text DEFAULT NULL,
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `module_id` (`module_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `offline_sessions`
--

INSERT INTO `offline_sessions` (`id`, `module_id`, `title`, `session_date`, `start_time`, `end_time`, `location`, `notes`, `updated_at`) VALUES
(1, 1, 'Weekly Review & Practice Session', '0000-00-00', '10:00:00', '12:00:00', 'Main Hall', 'Join us for the weekly review.', '2025-12-23 15:44:15'),
(2, 2, 'Weekly Review & Practice Session', NULL, '10:00:00', '12:00:00', 'Main Hall', 'Join us for the weekly review.', '2025-12-23 15:43:20'),
(3, 3, 'Weekly Review & Practice Session', NULL, '10:00:00', '12:00:00', 'Main Hall', 'Join us for the weekly review.', '2025-12-23 15:43:20'),
(4, 4, 'Weekly Review & Practice Session', NULL, '10:00:00', '12:00:00', 'Main Hall', 'Join us for the weekly review.', '2025-12-23 15:43:20'),
(5, 5, 'Weekly Review & Practice Session', NULL, '10:00:00', '12:00:00', 'Main Hall', 'Join us for the weekly review.', '2025-12-23 15:43:20'),
(6, 6, 'Weekly Review & Practice Session', NULL, '10:00:00', '12:00:00', 'Main Hall', 'Join us for the weekly review.', '2025-12-23 15:43:20'),
(7, 7, 'Weekly Review & Practice Session', NULL, '10:00:00', '12:00:00', 'Main Hall', 'Join us for the weekly review.', '2025-12-23 15:43:20'),
(8, 8, 'Weekly Review & Practice Session', NULL, '10:00:00', '12:00:00', 'Main Hall', 'Join us for the weekly review.', '2025-12-23 15:43:20'),
(9, 9, 'Weekly Review & Practice Session', NULL, '10:00:00', '12:00:00', 'Main Hall', 'Join us for the weekly review.', '2025-12-23 15:43:20'),
(10, 10, 'Weekly Review & Practice Session', NULL, '10:00:00', '12:00:00', 'Main Hall', 'Join us for the weekly review.', '2025-12-23 15:43:20'),
(11, 11, 'Weekly Review & Practice Session', NULL, '10:00:00', '12:00:00', 'Main Hall', 'Join us for the weekly review.', '2025-12-23 15:43:20'),
(12, 12, 'Weekly Review & Practice Session', NULL, '10:00:00', '12:00:00', 'Main Hall', 'Join us for the weekly review.', '2025-12-23 15:43:20'),
(13, 13, 'Weekly Review & Practice Session', NULL, '10:00:00', '12:00:00', 'Main Hall', 'Join us for the weekly review.', '2025-12-23 15:43:20'),
(14, 14, 'Weekly Review & Practice Session', NULL, '10:00:00', '12:00:00', 'Main Hall', 'Join us for the weekly review.', '2025-12-23 15:43:20'),
(15, 15, 'Weekly Review & Practice Session', NULL, '10:00:00', '12:00:00', 'Main Hall', 'Join us for the weekly review.', '2025-12-23 15:43:20'),
(16, 16, 'Weekly Review & Practice Session', NULL, '10:00:00', '12:00:00', 'Main Hall', 'Join us for the weekly review.', '2025-12-23 15:43:20'),
(17, 17, 'Weekly Review & Practice Session', NULL, '10:00:00', '12:00:00', 'Main Hall', 'Join us for the weekly review.', '2025-12-23 15:43:20'),
(18, 18, 'Weekly Review & Practice Session', NULL, '10:00:00', '12:00:00', 'Main Hall', 'Join us for the weekly review.', '2025-12-23 15:43:20'),
(19, 19, 'Weekly Review & Practice Session', NULL, '10:00:00', '12:00:00', 'Main Hall', 'Join us for the weekly review.', '2025-12-23 15:43:20'),
(20, 20, 'Weekly Review & Practice Session', NULL, '10:00:00', '12:00:00', 'Main Hall', 'Join us for the weekly review.', '2025-12-23 15:43:20'),
(21, 21, 'Weekly Review & Practice Session', NULL, '10:00:00', '12:00:00', 'Main Hall', 'Join us for the weekly review.', '2025-12-23 15:43:20'),
(22, 22, 'Weekly Review & Practice Session', NULL, '10:00:00', '12:00:00', 'Main Hall', 'Join us for the weekly review.', '2025-12-23 15:43:20'),
(23, 23, 'Weekly Review & Practice Session', NULL, '10:00:00', '12:00:00', 'Main Hall', 'Join us for the weekly review.', '2025-12-23 15:43:20'),
(24, 24, 'Weekly Review & Practice Session', NULL, '10:00:00', '12:00:00', 'Main Hall', 'Join us for the weekly review.', '2025-12-23 15:43:20'),
(25, 25, 'Weekly Review & Practice Session', NULL, '10:00:00', '12:00:00', 'Main Hall', 'Join us for the weekly review.', '2025-12-23 15:43:20'),
(26, 26, 'Weekly Review & Practice Session', NULL, '10:00:00', '12:00:00', 'Main Hall', 'Join us for the weekly review.', '2025-12-23 15:43:20'),
(27, 27, 'Weekly Review & Practice Session', NULL, '10:00:00', '12:00:00', 'Main Hall', 'Join us for the weekly review.', '2025-12-23 15:43:20'),
(28, 28, 'Weekly Review & Practice Session', NULL, '10:00:00', '12:00:00', 'Main Hall', 'Join us for the weekly review.', '2025-12-23 15:43:20');

-- --------------------------------------------------------

--
-- Table structure for table `student_lesson_progress`
--

DROP TABLE IF EXISTS `student_lesson_progress`;
CREATE TABLE IF NOT EXISTS `student_lesson_progress` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `student_id` int(11) NOT NULL,
  `lesson_id` int(11) NOT NULL,
  `status` enum('locked','active','completed') NOT NULL DEFAULT 'locked',
  `completed_at` datetime DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `unique_student_lesson` (`student_id`,`lesson_id`),
  KEY `progress_lesson_fk` (`lesson_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `student_module_progress`
--

DROP TABLE IF EXISTS `student_module_progress`;
CREATE TABLE IF NOT EXISTS `student_module_progress` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `student_id` int(11) NOT NULL,
  `module_id` int(11) NOT NULL,
  `status` enum('locked','in_progress','completed') NOT NULL DEFAULT 'locked',
  `completed_at` timestamp NULL DEFAULT NULL,
  `score` int(11) DEFAULT 0,
  PRIMARY KEY (`id`),
  UNIQUE KEY `unique_student_module` (`student_id`,`module_id`),
  UNIQUE KEY `unique_progress` (`student_id`,`module_id`),
  KEY `module_id` (`module_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `student_progress`
--

DROP TABLE IF EXISTS `student_progress`;
CREATE TABLE IF NOT EXISTS `student_progress` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `student_id` int(11) NOT NULL,
  `step_id` int(11) NOT NULL,
  `status` enum('locked','open','completed') NOT NULL DEFAULT 'locked',
  `score` int(11) DEFAULT NULL COMMENT 'For quizzes',
  `completed_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `student_id` (`student_id`),
  KEY `step_id` (`step_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `student_step_progress`
--

DROP TABLE IF EXISTS `student_step_progress`;
CREATE TABLE IF NOT EXISTS `student_step_progress` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `student_id` int(11) NOT NULL,
  `module_id` int(11) NOT NULL,
  `lesson_id` int(11) DEFAULT NULL,
  `step_id` int(11) NOT NULL,
  `status` enum('locked','active','completed') NOT NULL DEFAULT 'locked',
  `score` tinyint(4) DEFAULT NULL,
  `completed_at` datetime DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `student_id` (`student_id`),
  KEY `module_id` (`module_id`),
  KEY `step_id` (`step_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `tutor_acknowledgments`
--

DROP TABLE IF EXISTS `tutor_acknowledgments`;
CREATE TABLE IF NOT EXISTS `tutor_acknowledgments` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `tutor_id` int(11) NOT NULL,
  `student_id` int(11) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `idx_tutor_ack` (`tutor_id`,`student_id`),
  KEY `student_id` (`student_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

DROP TABLE IF EXISTS `users`;
CREATE TABLE IF NOT EXISTS `users` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(100) NOT NULL,
  `email` varchar(100) NOT NULL,
  `password` varchar(255) NOT NULL,
  `role` enum('student','tutor','admin') NOT NULL DEFAULT 'student',
  `group_id` int(11) DEFAULT NULL COMMENT 'For students only. Links to groups table.',
  `status` enum('active','inactive') NOT NULL DEFAULT 'active',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `email` (`email`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `name`, `email`, `password`, `role`, `group_id`, `status`, `created_at`) VALUES
(2, 'Mo Aatef', 'admin@gmail.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'admin', NULL, 'active', '2025-12-15 13:02:31');

--
-- Constraints for dumped tables
--

--
-- Constraints for table `announcements`
--
ALTER TABLE `announcements`
  ADD CONSTRAINT `announcements_ibfk_1` FOREIGN KEY (`author_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `announcements_ibfk_2` FOREIGN KEY (`group_id`) REFERENCES `groups` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `groups`
--
ALTER TABLE `groups`
  ADD CONSTRAINT `groups_ibfk_1` FOREIGN KEY (`tutor_id`) REFERENCES `users` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `lessons`
--
ALTER TABLE `lessons`
  ADD CONSTRAINT `lessons_module_fk` FOREIGN KEY (`module_id`) REFERENCES `modules` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `module_steps`
--
ALTER TABLE `module_steps`
  ADD CONSTRAINT `module_steps_ibfk_1` FOREIGN KEY (`module_id`) REFERENCES `modules` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `steps_lesson_fk` FOREIGN KEY (`lesson_id`) REFERENCES `lessons` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `offline_sessions`
--
ALTER TABLE `offline_sessions`
  ADD CONSTRAINT `offline_module_fk` FOREIGN KEY (`module_id`) REFERENCES `modules` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `student_lesson_progress`
--
ALTER TABLE `student_lesson_progress`
  ADD CONSTRAINT `progress_lesson_fk` FOREIGN KEY (`lesson_id`) REFERENCES `lessons` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `progress_student_fk` FOREIGN KEY (`student_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `student_module_progress`
--
ALTER TABLE `student_module_progress`
  ADD CONSTRAINT `student_module_progress_ibfk_1` FOREIGN KEY (`student_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `student_module_progress_ibfk_2` FOREIGN KEY (`module_id`) REFERENCES `modules` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `student_progress`
--
ALTER TABLE `student_progress`
  ADD CONSTRAINT `student_progress_ibfk_1` FOREIGN KEY (`student_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `student_progress_ibfk_2` FOREIGN KEY (`step_id`) REFERENCES `module_steps` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `tutor_acknowledgments`
--
ALTER TABLE `tutor_acknowledgments`
  ADD CONSTRAINT `tutor_acknowledgments_ibfk_1` FOREIGN KEY (`tutor_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `tutor_acknowledgments_ibfk_2` FOREIGN KEY (`student_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;

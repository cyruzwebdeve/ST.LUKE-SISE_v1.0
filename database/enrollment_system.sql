-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1:3306
-- Generation Time: Nov 02, 2025 at 01:50 AM
-- Server version: 9.1.0
-- PHP Version: 8.3.14

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `enrollment_system`
--

-- --------------------------------------------------------

--
-- Table structure for table `enrollment`
--

DROP TABLE IF EXISTS `enrollment`;
CREATE TABLE IF NOT EXISTS `enrollment` (
  `enrollment_id` int NOT NULL AUTO_INCREMENT,
  `student_id` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `section_id` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `enrollment_status` varchar(20) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `date_enrolled` date NOT NULL,
  PRIMARY KEY (`enrollment_id`),
  KEY `student_id` (`student_id`),
  KEY `section_id` (`section_id`)
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `enrollment`
--

INSERT INTO `enrollment` (`enrollment_id`, `student_id`, `section_id`, `enrollment_status`, `date_enrolled`) VALUES
(1, '2024-0001', 'SEC-1-A', 'enrolled', '2024-06-01'),
(2, '2024-0002', 'SEC-2-A', 'enrolled', '2024-06-01'),
(3, '2024-0003', 'SEC-3-A', 'enrolled', '2025-11-01');

-- --------------------------------------------------------

--
-- Table structure for table `grade`
--

DROP TABLE IF EXISTS `grade`;
CREATE TABLE IF NOT EXISTS `grade` (
  `grade_id` int NOT NULL AUTO_INCREMENT,
  `student_id` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `subject_code` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `grading_period` varchar(20) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `grade_score` decimal(5,2) NOT NULL,
  PRIMARY KEY (`grade_id`),
  KEY `student_id` (`student_id`),
  KEY `subject_code` (`subject_code`)
) ENGINE=InnoDB AUTO_INCREMENT=6 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `grade`
--

INSERT INTO `grade` (`grade_id`, `student_id`, `subject_code`, `grading_period`, `grade_score`) VALUES
(1, '2024-0001', 'MATH-1', '1st', 92.00),
(2, '2024-0001', 'ENG-1', '1st', 88.50),
(3, '2024-0001', 'SCI-1', '1st', 90.00),
(4, '2024-0002', 'MATH-1', '1st', 95.00),
(5, '2024-0002', 'ENG-1', '1st', 93.50);

-- --------------------------------------------------------

--
-- Table structure for table `schedule`
--

DROP TABLE IF EXISTS `schedule`;
CREATE TABLE IF NOT EXISTS `schedule` (
  `schedule_id` int NOT NULL AUTO_INCREMENT,
  `day_time` datetime NOT NULL,
  `room_number` int NOT NULL,
  `subject_code` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `section_id` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `teacher_id` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  PRIMARY KEY (`schedule_id`),
  KEY `subject_code` (`subject_code`),
  KEY `section_id` (`section_id`),
  KEY `teacher_id` (`teacher_id`)
) ENGINE=InnoDB AUTO_INCREMENT=6 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `schedule`
--

INSERT INTO `schedule` (`schedule_id`, `day_time`, `room_number`, `subject_code`, `section_id`, `teacher_id`) VALUES
(1, '2024-11-04 08:00:00', 101, 'MATH-1', 'SEC-1-A', 'T001'),
(2, '2024-11-04 09:00:00', 101, 'ENG-1', 'SEC-1-A', 'T002'),
(3, '2024-11-04 10:00:00', 101, 'SCI-1', 'SEC-1-A', 'T003'),
(4, '2024-11-04 08:00:00', 102, 'MATH-1', 'SEC-2-A', 'T001'),
(5, '2024-11-04 09:00:00', 102, 'ENG-1', 'SEC-2-A', 'T002');

-- --------------------------------------------------------

--
-- Table structure for table `section`
--

DROP TABLE IF EXISTS `section`;
CREATE TABLE IF NOT EXISTS `section` (
  `section_id` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `section_name` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `grade_level` varchar(20) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  PRIMARY KEY (`section_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `section`
--

INSERT INTO `section` (`section_id`, `section_name`, `grade_level`) VALUES
('SEC-1-A', 'Grade 1-A', 'Grade 1'),
('SEC-2-A', 'Grade 2-A', 'Grade 2'),
('SEC-3-A', 'Grade 3-A', 'Grade 3'),
('SEC-4-A', 'Grade 4-A', 'Grade 4'),
('SEC-5-A', 'Grade 5-A', 'Grade 5'),
('SEC-6-A', 'Grade 6-A', 'Grade 6'),
('SEC-K-A', 'Kindergarten-A', 'Kindergarten');

-- --------------------------------------------------------

--
-- Table structure for table `student`
--

DROP TABLE IF EXISTS `student`;
CREATE TABLE IF NOT EXISTS `student` (
  `student_id` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `student_name` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `grade_level` varchar(20) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `gender` varchar(10) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `birthdate` date DEFAULT NULL,
  `religion` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `address` text CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci,
  `contact_number` varchar(20) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `father_name` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `father_occupation` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `mother_name` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `mother_occupation` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `guardian_name` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `guardian_relationship` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `previous_school` varchar(200) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `last_school_year` varchar(20) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  PRIMARY KEY (`student_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `student`
--

INSERT INTO `student` (`student_id`, `student_name`, `grade_level`, `gender`, `birthdate`, `religion`, `address`, `contact_number`, `father_name`, `father_occupation`, `mother_name`, `mother_occupation`, `guardian_name`, `guardian_relationship`, `previous_school`, `last_school_year`) VALUES
('2024-0001', 'Juan Carlos Reyes', 'Grade 1', 'Male', '2017-05-15', 'Roman Catholic', 'Manila, Philippines', '09171234567', 'Carlos Reyes Sr.', 'Engineer', 'Maria Reyes', 'Teacher', NULL, NULL, 'ABC Preschool', '2023-2024'),
('2024-0002', 'Maria Sofia Santos', 'Grade 2', 'Female', '2016-08-22', 'Roman Catholic', 'Quezon City, Philippines', '09181234567', 'Pedro Santos', 'Doctor', 'Ana Santos', 'Nurse', NULL, NULL, 'XYZ Elementary', '2023-2024'),
('2024-0003', 'test test test', 'Kindergarten', 'Male', '2015-08-26', 'Roman Catholic', 'test', '09123456789', 'test', 'test', 'test', 'test', 'test', 'test', 'test school', '2023-2024');

-- --------------------------------------------------------

--
-- Table structure for table `subject`
--

DROP TABLE IF EXISTS `subject`;
CREATE TABLE IF NOT EXISTS `subject` (
  `subject_code` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `subject_name` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `teacher_id` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  PRIMARY KEY (`subject_code`),
  KEY `teacher_id` (`teacher_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `subject`
--

INSERT INTO `subject` (`subject_code`, `subject_name`, `teacher_id`) VALUES
('AP-1', 'Araling Panlipunan', 'T005'),
('ENG-1', 'English', 'T002'),
('EPP-1', 'Edukasyon sa Pagpapakatao', 'T002'),
('FIL-1', 'Filipino', 'T004'),
('MAPEH-1', 'MAPEH', 'T001'),
('MATH-1', 'Mathematics', 'T001'),
('SCI-1', 'Science', 'T003');

-- --------------------------------------------------------

--
-- Table structure for table `teacher`
--

DROP TABLE IF EXISTS `teacher`;
CREATE TABLE IF NOT EXISTS `teacher` (
  `teacher_id` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `teacher_name` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  PRIMARY KEY (`teacher_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `teacher`
--

INSERT INTO `teacher` (`teacher_id`, `teacher_name`) VALUES
('T001', 'Mrs. Maria Santos'),
('T002', 'Mr. Juan Dela Cruz'),
('T003', 'Ms. Ana Garcia'),
('T004', 'Mr. Pedro Reyes'),
('T005', 'Mrs. Sofia Martinez');

-- --------------------------------------------------------

--
-- Table structure for table `user_account`
--

DROP TABLE IF EXISTS `user_account`;
CREATE TABLE IF NOT EXISTS `user_account` (
  `user_id` int NOT NULL AUTO_INCREMENT,
  `username` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `password` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `role` enum('admin','teacher','student') CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL DEFAULT 'student',
  `student_id` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `teacher_id` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `date_created` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`user_id`),
  UNIQUE KEY `username` (`username`),
  KEY `student_id` (`student_id`),
  KEY `teacher_id` (`teacher_id`)
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `user_account`
--

INSERT INTO `user_account` (`user_id`, `username`, `password`, `role`, `student_id`, `teacher_id`, `date_created`) VALUES
(1, '2024-0001', '$2y$10$NB7JsdyRTtsK7VI4xqMP9OuiWOoNhQgG9JODAwjNsbRwLzBEoiVKi', 'student', '2024-0001', NULL, '2025-11-01 14:26:22'),
(2, '2024-0002', '$2y$10$NB7JsdyRTtsK7VI4xqMP9OuiWOoNhQgG9JODAwjNsbRwLzBEoiVKi', 'student', '2024-0002', NULL, '2025-11-01 14:26:22'),
(3, '2024-0003', '$2y$10$NB7JsdyRTtsK7VI4xqMP9OuiWOoNhQgG9JODAwjNsbRwLzBEoiVKi', 'student', '2024-0003', NULL, '2025-11-01 14:36:52');

--
-- Constraints for dumped tables
--

--
-- Constraints for table `enrollment`
--
ALTER TABLE `enrollment`
  ADD CONSTRAINT `enrollment_ibfk_1` FOREIGN KEY (`student_id`) REFERENCES `student` (`student_id`),
  ADD CONSTRAINT `enrollment_ibfk_2` FOREIGN KEY (`section_id`) REFERENCES `section` (`section_id`);

--
-- Constraints for table `grade`
--
ALTER TABLE `grade`
  ADD CONSTRAINT `grade_ibfk_1` FOREIGN KEY (`student_id`) REFERENCES `student` (`student_id`),
  ADD CONSTRAINT `grade_ibfk_2` FOREIGN KEY (`subject_code`) REFERENCES `subject` (`subject_code`);

--
-- Constraints for table `schedule`
--
ALTER TABLE `schedule`
  ADD CONSTRAINT `schedule_ibfk_1` FOREIGN KEY (`subject_code`) REFERENCES `subject` (`subject_code`),
  ADD CONSTRAINT `schedule_ibfk_2` FOREIGN KEY (`section_id`) REFERENCES `section` (`section_id`),
  ADD CONSTRAINT `schedule_ibfk_3` FOREIGN KEY (`teacher_id`) REFERENCES `teacher` (`teacher_id`);

--
-- Constraints for table `subject`
--
ALTER TABLE `subject`
  ADD CONSTRAINT `subject_ibfk_1` FOREIGN KEY (`teacher_id`) REFERENCES `teacher` (`teacher_id`);

--
-- Constraints for table `user_account`
--
ALTER TABLE `user_account`
  ADD CONSTRAINT `user_account_ibfk_1` FOREIGN KEY (`student_id`) REFERENCES `student` (`student_id`) ON DELETE SET NULL,
  ADD CONSTRAINT `user_account_ibfk_2` FOREIGN KEY (`teacher_id`) REFERENCES `teacher` (`teacher_id`) ON DELETE SET NULL;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
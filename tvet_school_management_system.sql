-- phpMyAdmin SQL Dump
-- version 5.2.0
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Dec 17, 2025 at 10:43 AM
-- Server version: 10.4.25-MariaDB
-- PHP Version: 7.4.30

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `tvet_school_management_system`
--

-- --------------------------------------------------------

--
-- Table structure for table `attendance`
--

CREATE TABLE `attendance` (
  `Attendance_Id` int(11) NOT NULL,
  `Student_Id` varchar(10) NOT NULL,
  `Course_Id` varchar(10) NOT NULL,
  `Session_Date` date NOT NULL,
  `Status` varchar(10) NOT NULL,
  `Timestamp` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Dumping data for table `attendance`
--

INSERT INTO `attendance` (`Attendance_Id`, `Student_Id`, `Course_Id`, `Session_Date`, `Status`, `Timestamp`) VALUES
(1, '27174', 'SENG123', '2025-12-02', 'Present', '2025-12-17 09:27:55'),
(2, '27117', 'SENG123', '2025-12-10', 'Present', '0000-00-00 00:00:00');

-- --------------------------------------------------------

--
-- Table structure for table `course`
--

CREATE TABLE `course` (
  `Course_Id` varchar(10) NOT NULL,
  `Instructor_Id` varchar(10) NOT NULL,
  `Title` varchar(100) NOT NULL,
  `Description` varchar(300) NOT NULL,
  `Duration` int(11) NOT NULL,
  `Start_Date` date NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Dumping data for table `course`
--

INSERT INTO `course` (`Course_Id`, `Instructor_Id`, `Title`, `Description`, `Duration`, `Start_Date`) VALUES
('OOP123', 'INS123', 'Object Oriented Programming', 'Object Oriented Programming', 5, '2025-12-17'),
('SENG123', 'INS123', 'Best Programming', 'Best Programming Design', 7, '2025-12-28');

-- --------------------------------------------------------

--
-- Table structure for table `enrollment`
--

CREATE TABLE `enrollment` (
  `Enroll_Id` int(11) NOT NULL,
  `Student_Id` varchar(10) NOT NULL,
  `Course_Id` varchar(10) NOT NULL,
  `Enrollment_Date` date NOT NULL,
  `Status` varchar(15) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Dumping data for table `enrollment`
--

INSERT INTO `enrollment` (`Enroll_Id`, `Student_Id`, `Course_Id`, `Enrollment_Date`, `Status`) VALUES
(1, '27174', 'SENG123', '1970-01-01', 'Active'),
(2, '27117', 'SENG123', '2025-12-10', 'Active');

-- --------------------------------------------------------

--
-- Table structure for table `grade`
--

CREATE TABLE `grade` (
  `Grade_Id` int(11) NOT NULL,
  `Student_Id` varchar(10) NOT NULL,
  `Course_Id` varchar(10) NOT NULL,
  `Score` int(11) NOT NULL,
  `Grade_Letter` varchar(10) NOT NULL,
  `Graded_Date` date NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Dumping data for table `grade`
--

INSERT INTO `grade` (`Grade_Id`, `Student_Id`, `Course_Id`, `Score`, `Grade_Letter`, `Graded_Date`) VALUES
(1, '27117', 'SENG123', 90, 'A', '2025-12-10');

-- --------------------------------------------------------

--
-- Table structure for table `instructor`
--

CREATE TABLE `instructor` (
  `Instructor_Id` varchar(10) NOT NULL,
  `Full_Name` varchar(200) NOT NULL,
  `Gender` varchar(15) NOT NULL,
  `Email` varchar(100) NOT NULL,
  `Specialization` varchar(50) NOT NULL,
  `Hire_Date` date NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Dumping data for table `instructor`
--

INSERT INTO `instructor` (`Instructor_Id`, `Full_Name`, `Gender`, `Email`, `Specialization`, `Hire_Date`) VALUES
('INS123', 'NDIHO IRUMVA Aime', 'Male', 'irumva@gmail.com', 'IT', '2025-12-02');

-- --------------------------------------------------------

--
-- Table structure for table `student`
--

CREATE TABLE `student` (
  `Student_Id` varchar(10) NOT NULL,
  `Full_Name` varchar(200) NOT NULL,
  `Gender` varchar(15) NOT NULL,
  `Email` varchar(100) NOT NULL,
  `Phone` varchar(15) NOT NULL,
  `Enrollment_Date` date NOT NULL,
  `Status` varchar(15) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Dumping data for table `student`
--

INSERT INTO `student` (`Student_Id`, `Full_Name`, `Gender`, `Email`, `Phone`, `Enrollment_Date`, `Status`) VALUES
('27117', 'INEZA', 'Female', 'inesineza@gmail.com', '+250785980555', '2025-12-10', 'Active'),
('27174', 'NDAYISABA KAMARIZA Belie', 'Female', 'belie.ndayisaba@auca.ac.rw', '+250785980556', '2025-12-10', 'Active');

-- --------------------------------------------------------

--
-- Table structure for table `user`
--

CREATE TABLE `user` (
  `User_Id` int(11) NOT NULL,
  `Full_Name` varchar(200) NOT NULL,
  `Gender` varchar(15) NOT NULL,
  `Username` varchar(50) NOT NULL,
  `Email` varchar(100) NOT NULL,
  `Created_At` datetime NOT NULL,
  `Password` varchar(50) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Dumping data for table `user`
--

INSERT INTO `user` (`User_Id`, `Full_Name`, `Gender`, `Username`, `Email`, `Created_At`, `Password`) VALUES
(1, 'NDAYISABA KAMARIZA Belie', 'Female', 'NKbelie', 'ndayisababelie2004@gmail.com', '2025-12-10 09:08:58', '123456');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `attendance`
--
ALTER TABLE `attendance`
  ADD PRIMARY KEY (`Attendance_Id`),
  ADD KEY `Student_Id` (`Student_Id`),
  ADD KEY `Course_Id` (`Course_Id`);

--
-- Indexes for table `course`
--
ALTER TABLE `course`
  ADD PRIMARY KEY (`Course_Id`),
  ADD UNIQUE KEY `Title` (`Title`),
  ADD KEY `Instructor_Id` (`Instructor_Id`);

--
-- Indexes for table `enrollment`
--
ALTER TABLE `enrollment`
  ADD PRIMARY KEY (`Enroll_Id`),
  ADD KEY `Student_Id` (`Student_Id`),
  ADD KEY `Course_Id` (`Course_Id`);

--
-- Indexes for table `grade`
--
ALTER TABLE `grade`
  ADD PRIMARY KEY (`Grade_Id`),
  ADD KEY `Student_Id` (`Student_Id`),
  ADD KEY `Course_Id` (`Course_Id`);

--
-- Indexes for table `instructor`
--
ALTER TABLE `instructor`
  ADD PRIMARY KEY (`Instructor_Id`);

--
-- Indexes for table `student`
--
ALTER TABLE `student`
  ADD PRIMARY KEY (`Student_Id`),
  ADD UNIQUE KEY `Email` (`Email`),
  ADD UNIQUE KEY `Phone` (`Phone`);

--
-- Indexes for table `user`
--
ALTER TABLE `user`
  ADD PRIMARY KEY (`User_Id`),
  ADD UNIQUE KEY `Username` (`Username`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `attendance`
--
ALTER TABLE `attendance`
  MODIFY `Attendance_Id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `enrollment`
--
ALTER TABLE `enrollment`
  MODIFY `Enroll_Id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `grade`
--
ALTER TABLE `grade`
  MODIFY `Grade_Id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `user`
--
ALTER TABLE `user`
  MODIFY `User_Id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `attendance`
--
ALTER TABLE `attendance`
  ADD CONSTRAINT `attendance_ibfk_1` FOREIGN KEY (`Course_Id`) REFERENCES `course` (`Course_Id`),
  ADD CONSTRAINT `attendance_ibfk_2` FOREIGN KEY (`Student_Id`) REFERENCES `student` (`Student_Id`);

--
-- Constraints for table `course`
--
ALTER TABLE `course`
  ADD CONSTRAINT `course_ibfk_1` FOREIGN KEY (`Instructor_Id`) REFERENCES `instructor` (`Instructor_Id`);

--
-- Constraints for table `enrollment`
--
ALTER TABLE `enrollment`
  ADD CONSTRAINT `enrollment_ibfk_1` FOREIGN KEY (`Course_Id`) REFERENCES `course` (`Course_Id`),
  ADD CONSTRAINT `enrollment_ibfk_2` FOREIGN KEY (`Student_Id`) REFERENCES `student` (`Student_Id`);

--
-- Constraints for table `grade`
--
ALTER TABLE `grade`
  ADD CONSTRAINT `grade_ibfk_1` FOREIGN KEY (`Student_Id`) REFERENCES `student` (`Student_Id`),
  ADD CONSTRAINT `grade_ibfk_2` FOREIGN KEY (`Course_Id`) REFERENCES `course` (`Course_Id`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;

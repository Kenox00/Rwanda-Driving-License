-- Database: `RDL`
CREATE DATABASE IF NOT EXISTS `RDL`;
USE `RDL`;

-- Table structure for table `Admin`
CREATE TABLE `Admin` (
  `AdminId` int(11) NOT NULL AUTO_INCREMENT,
  `AdminName` varchar(50) NOT NULL,
  `Password` varchar(255) NOT NULL,
  PRIMARY KEY (`AdminId`),
  UNIQUE KEY `AdminName` (`AdminName`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Dumping data for table `Admin`
-- Note: All passwords are hashed using PHP's password_hash() function
INSERT INTO `Admin` (`AdminName`, `Password`) VALUES
('admin', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi'), -- password: 'Admin123!'
('supervisor', '$2y$10$Q5ZvL3Aw0qDY8jfBHxNFuO8hQJ3Xo9mKlP7SsT2Gg.nN4WwVv8Xxx'); -- password: 'Super456@'

-- Table structure for table `Candidate`
CREATE TABLE `Candidate` (
  `CandidateNationalId` varchar(20) NOT NULL,
  `FirstName` varchar(50) NOT NULL,
  `LastName` varchar(50) NOT NULL,
  `Gender` enum('Male','Female') NOT NULL,
  `DOB` date NOT NULL,
  `ExamDate` date NOT NULL,
  `PhoneNumber` varchar(20) NOT NULL,
  PRIMARY KEY (`CandidateNationalId`),
  UNIQUE KEY `PhoneNumber` (`PhoneNumber`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Dumping data for table `Candidate`
INSERT INTO `Candidate` (`CandidateNationalId`, `FirstName`, `LastName`, `Gender`, `DOB`, `ExamDate`, `PhoneNumber`) VALUES
('1199880012345678', 'John', 'Doe', 'Male', '1998-05-10', '2025-06-20', '0788123456'),
('1199990012345679', 'Jane', 'Smith', 'Female', '1999-08-22', '2025-06-20', '0788123457'),
('1200080012345680', 'David', 'Mugabo', 'Male', '2000-03-15', '2025-06-21', '0788123458'),
('1199970012345681', 'Mary', 'Nkusi', 'Female', '1997-11-05', '2025-06-21', '0788123459'),
('1199950012345682', 'James', 'Gasana', 'Male', '1995-07-12', '2025-06-22', '0788123460'),
('1200010012345683', 'Linda', 'Uwase', 'Female', '2001-09-28', '2025-06-22', '0788123461'),
('1199940012345684', 'Robert', 'Kagame', 'Male', '1994-12-03', '2025-06-23', '0788123462'),
('1200000012345685', 'Patricia', 'Mukamana', 'Female', '2000-06-17', '2025-06-23', '0788123463');

-- Table structure for table `Grade`
CREATE TABLE `Grade` (
  `CandidateNationalId` varchar(20) NOT NULL,
  `LicenseExamCategory` varchar(50) NOT NULL,
  `ObtainedMarks` int(11) NOT NULL,
  `Decision` varchar(10) NOT NULL,
  KEY `CandidateNationalId` (`CandidateNationalId`),
  CONSTRAINT `grade_ibfk_1` FOREIGN KEY (`CandidateNationalId`) REFERENCES `Candidate` (`CandidateNationalId`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Dumping data for table `Grade`
INSERT INTO `Grade` (`CandidateNationalId`, `LicenseExamCategory`, `ObtainedMarks`, `Decision`) VALUES
('1199880012345678', 'B', 18, 'Passed'),
('1199990012345679', 'A', 10, 'Failed'),
('1200080012345680', 'C', 15, 'Passed'),
('1199970012345681', 'B', 9, 'Failed'),
('1199950012345682', 'D', 17, 'Passed'),
('1200010012345683', 'A', 14, 'Passed'),
('1199940012345684', 'E', 8, 'Failed'),
('1200000012345685', 'B', 13, 'Passed');

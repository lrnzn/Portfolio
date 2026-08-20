-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: May 04, 2026 at 02:03 PM
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
-- Database: `db_librotrack`
--
CREATE DATABASE IF NOT EXISTS db_librotrack;
USE db_librotrack;
-- --------------------------------------------------------

--
-- Table structure for table `tbl_books`
--

CREATE TABLE `tbl_books` (
  `bookID` int(10) UNSIGNED NOT NULL,
  `title` varchar(200) NOT NULL,
  `author` varchar(100) NOT NULL,
  `isbn` varchar(20) DEFAULT NULL,
  `genre` varchar(50) NOT NULL,
  `copies` int(10) UNSIGNED NOT NULL DEFAULT 1,
  `location` varchar(100) DEFAULT NULL,
  `description` text DEFAULT NULL,
  `cover_image` varchar(255) DEFAULT NULL,
  `dateAdded` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `tbl_books`
--

INSERT INTO `tbl_books` (`bookID`, `title`, `author`, `isbn`, `genre`, `copies`, `location`, `description`, `cover_image`, `dateAdded`) VALUES
(1, 'Introduction to Computing', 'Peter Norton', NULL, 'Science & Technology', 5, 'Shelf A', NULL, NULL, '2026-04-18 00:42:31'),
(4, '48 Laws of Power', 'Robert Greene', '978-0140280197', 'Other', 12, 'Shelf B', 'A definitive guide to the dynamics of power, this book distills 3,000 years of history into 48 essential laws. Drawing from the philosophies of figures like Machiavelli and Sun Tzu, it explores how to gain, observe, or defend against ultimate control.', 'cover_69e3742373a726.90530362.webp', '2026-04-18 12:08:03'),
(5, 'Art of War', 'Sun Tzu', '978-0385292160', 'Philosophy', 2, NULL, 'The Art of War is an ancient Chinese military treatise attributed to Sun Tzu, focusing on winning conflicts through strategy, intelligence, and deception rather than raw force. Composed of 13 chapters, it teaches that the supreme goal is to subdue the enemy without fighting, emphasizing adaptability, speed, and knowing both oneself and the opponent.', 'cover_69f88765dd1458.57504672.jpg', '2026-05-04 11:47:49');

-- --------------------------------------------------------

--
-- Table structure for table `tbl_penalties`
--

CREATE TABLE `tbl_penalties` (
  `penaltyID` int(10) UNSIGNED NOT NULL,
  `transactionID` int(10) UNSIGNED NOT NULL,
  `daysOverdue` int(10) UNSIGNED NOT NULL,
  `amount` decimal(8,2) NOT NULL,
  `paid` tinyint(1) NOT NULL DEFAULT 0,
  `dateAdded` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `tbl_penalties`
--

INSERT INTO `tbl_penalties` (`penaltyID`, `transactionID`, `daysOverdue`, `amount`, `paid`, `dateAdded`) VALUES
(1, 1, 8, 40.00, 0, '2026-05-04 11:49:36');

-- --------------------------------------------------------

--
-- Table structure for table `tbl_student`
--

CREATE TABLE `tbl_student` (
  `studentID` int(10) UNSIGNED NOT NULL,
  `userID` int(10) UNSIGNED NOT NULL,
  `fname` varchar(50) NOT NULL,
  `mname` varchar(50) DEFAULT NULL,
  `lname` varchar(50) NOT NULL,
  `nameExt` varchar(10) DEFAULT NULL,
  `studentNumber` varchar(20) NOT NULL,
  `course` varchar(100) NOT NULL,
  `email` varchar(100) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `tbl_student`
--

INSERT INTO `tbl_student` (`studentID`, `userID`, `fname`, `mname`, `lname`, `nameExt`, `studentNumber`, `course`, `email`) VALUES
(1, 2, 'Lorenzen', 'Selendron', 'Ilon', NULL, 'ILS09200600', 'BSIT', 'lorenzenilon@gmail.com'),
(3, 4, 'Joeric Israel', NULL, 'Gonzales', NULL, 'GJA10099900', 'BSIT', 'gonzalesjoericisrael@gmail.com');

-- --------------------------------------------------------

--
-- Table structure for table `tbl_transaction`
--

CREATE TABLE `tbl_transaction` (
  `transactionID` int(10) UNSIGNED NOT NULL,
  `studentID` int(10) UNSIGNED NOT NULL,
  `bookID` int(10) UNSIGNED NOT NULL,
  `borrowDate` date NOT NULL,
  `dueDate` date NOT NULL,
  `returnDate` date DEFAULT NULL,
  `status` enum('borrowed','returned','overdue') NOT NULL DEFAULT 'borrowed',
  `dateAdded` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `tbl_transaction`
--

INSERT INTO `tbl_transaction` (`transactionID`, `studentID`, `bookID`, `borrowDate`, `dueDate`, `returnDate`, `status`, `dateAdded`) VALUES
(1, 1, 4, '2026-04-19', '2026-04-26', NULL, 'borrowed', '2026-04-18 23:56:41');

-- --------------------------------------------------------

--
-- Table structure for table `tbl_users`
--

CREATE TABLE `tbl_users` (
  `userID` int(10) UNSIGNED NOT NULL,
  `name` varchar(100) NOT NULL,
  `profile_picture` varchar(255) DEFAULT NULL,
  `two_fa_secret` varchar(255) DEFAULT NULL,
  `two_fa_enabled` tinyint(1) NOT NULL DEFAULT 0,
  `username` varchar(50) NOT NULL,
  `password` varchar(255) NOT NULL,
  `role` enum('admin','student') NOT NULL,
  `dateAdded` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `tbl_users`
--

INSERT INTO `tbl_users` (`userID`, `name`, `profile_picture`, `two_fa_secret`, `two_fa_enabled`, `username`, `password`, `role`, `dateAdded`) VALUES
(1, 'Administrator', NULL, NULL, 0, 'admin', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'admin', '2026-04-17 12:21:01'),
(2, 'Lorenzen Selendron Ilon', 'profile_2_69f54dfcde2e0.jpg', NULL, 0, 'lrnzn', '$2y$10$TUfq6Y.y0xVLVSrerHbC5ultE8qDmlqHn0I3SzJ7nF8rfFiqL9Mq.', 'student', '2026-04-18 11:33:22'),
(4, 'Joeric Israel Gonzales', NULL, NULL, 0, 'joeric.gonzales', '$2y$10$hYwKoXYpSKkQ7XBbKDohH.Y4BkuGA.gnE7f6Is1wrEs9ycvHg0nUW', 'student', '2026-04-18 23:43:54');

-- --------------------------------------------------------

--
-- Stand-in structure for view `view_active_borrows`
-- (See below for the actual view)
--
CREATE TABLE `view_active_borrows` (
`transactionID` int(10) unsigned
,`studentName` varchar(101)
,`studentNumber` varchar(20)
,`course` varchar(100)
,`bookTitle` varchar(200)
,`author` varchar(100)
,`borrowDate` date
,`dueDate` date
,`status` enum('borrowed','returned','overdue')
,`daysOverdue` int(7)
);

-- --------------------------------------------------------

--
-- Stand-in structure for view `view_book_availability`
-- (See below for the actual view)
--
CREATE TABLE `view_book_availability` (
`bookID` int(10) unsigned
,`title` varchar(200)
,`author` varchar(100)
,`genre` varchar(50)
,`copies` int(10) unsigned
,`available` bigint(21) unsigned
,`location` varchar(100)
);

-- --------------------------------------------------------

--
-- Stand-in structure for view `view_overdue`
-- (See below for the actual view)
--
CREATE TABLE `view_overdue` (
`transactionID` int(10) unsigned
,`studentName` varchar(101)
,`studentNumber` varchar(20)
,`bookTitle` varchar(200)
,`dueDate` date
,`daysOverdue` int(7)
,`penaltyAmount` decimal(9,2)
,`paid` int(4)
);

-- --------------------------------------------------------

--
-- Structure for view `view_active_borrows`
--
DROP TABLE IF EXISTS `view_active_borrows`;

CREATE ALGORITHM=UNDEFINED DEFINER=`root`@`localhost` SQL SECURITY DEFINER VIEW `view_active_borrows`  AS SELECT `t`.`transactionID` AS `transactionID`, concat(`s`.`fname`,' ',`s`.`lname`) AS `studentName`, `s`.`studentNumber` AS `studentNumber`, `s`.`course` AS `course`, `bk`.`title` AS `bookTitle`, `bk`.`author` AS `author`, `t`.`borrowDate` AS `borrowDate`, `t`.`dueDate` AS `dueDate`, `t`.`status` AS `status`, CASE WHEN `t`.`dueDate` < curdate() AND `t`.`status` = 'borrowed' THEN to_days(curdate()) - to_days(`t`.`dueDate`) ELSE 0 END AS `daysOverdue` FROM ((`tbl_transaction` `t` join `tbl_student` `s` on(`t`.`studentID` = `s`.`studentID`)) join `tbl_books` `bk` on(`t`.`bookID` = `bk`.`bookID`)) WHERE `t`.`status` in ('borrowed','overdue') ;

-- --------------------------------------------------------

--
-- Structure for view `view_book_availability`
--
DROP TABLE IF EXISTS `view_book_availability`;

CREATE ALGORITHM=UNDEFINED DEFINER=`root`@`localhost` SQL SECURITY DEFINER VIEW `view_book_availability`  AS SELECT `b`.`bookID` AS `bookID`, `b`.`title` AS `title`, `b`.`author` AS `author`, `b`.`genre` AS `genre`, `b`.`copies` AS `copies`, `b`.`copies`- coalesce((select count(0) from `tbl_transaction` `t` where `t`.`bookID` = `b`.`bookID` and `t`.`status` = 'borrowed'),0) AS `available`, `b`.`location` AS `location` FROM `tbl_books` AS `b` ;

-- --------------------------------------------------------

--
-- Structure for view `view_overdue`
--
DROP TABLE IF EXISTS `view_overdue`;

CREATE ALGORITHM=UNDEFINED DEFINER=`root`@`localhost` SQL SECURITY DEFINER VIEW `view_overdue`  AS SELECT `t`.`transactionID` AS `transactionID`, concat(`s`.`fname`,' ',`s`.`lname`) AS `studentName`, `s`.`studentNumber` AS `studentNumber`, `bk`.`title` AS `bookTitle`, `t`.`dueDate` AS `dueDate`, to_days(curdate()) - to_days(`t`.`dueDate`) AS `daysOverdue`, (to_days(curdate()) - to_days(`t`.`dueDate`)) * 5.00 AS `penaltyAmount`, coalesce(`p`.`paid`,0) AS `paid` FROM (((`tbl_transaction` `t` join `tbl_student` `s` on(`t`.`studentID` = `s`.`studentID`)) join `tbl_books` `bk` on(`t`.`bookID` = `bk`.`bookID`)) left join `tbl_penalties` `p` on(`t`.`transactionID` = `p`.`transactionID`)) WHERE `t`.`status` = 'overdue' OR `t`.`status` = 'borrowed' AND `t`.`dueDate` < curdate() ;

--
-- Indexes for dumped tables
--

--
-- Indexes for table `tbl_books`
--
ALTER TABLE `tbl_books`
  ADD PRIMARY KEY (`bookID`),
  ADD UNIQUE KEY `isbn` (`isbn`);

--
-- Indexes for table `tbl_penalties`
--
ALTER TABLE `tbl_penalties`
  ADD PRIMARY KEY (`penaltyID`),
  ADD UNIQUE KEY `transactionID` (`transactionID`);

--
-- Indexes for table `tbl_student`
--
ALTER TABLE `tbl_student`
  ADD PRIMARY KEY (`studentID`),
  ADD UNIQUE KEY `studentNumber` (`studentNumber`),
  ADD UNIQUE KEY `email` (`email`),
  ADD KEY `fk_student_user` (`userID`);

--
-- Indexes for table `tbl_transaction`
--
ALTER TABLE `tbl_transaction`
  ADD PRIMARY KEY (`transactionID`),
  ADD KEY `fk_transaction_student` (`studentID`),
  ADD KEY `fk_transaction_book` (`bookID`);

--
-- Indexes for table `tbl_users`
--
ALTER TABLE `tbl_users`
  ADD PRIMARY KEY (`userID`),
  ADD UNIQUE KEY `username` (`username`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `tbl_books`
--
ALTER TABLE `tbl_books`
  MODIFY `bookID` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `tbl_penalties`
--
ALTER TABLE `tbl_penalties`
  MODIFY `penaltyID` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `tbl_student`
--
ALTER TABLE `tbl_student`
  MODIFY `studentID` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `tbl_transaction`
--
ALTER TABLE `tbl_transaction`
  MODIFY `transactionID` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `tbl_users`
--
ALTER TABLE `tbl_users`
  MODIFY `userID` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `tbl_penalties`
--
ALTER TABLE `tbl_penalties`
  ADD CONSTRAINT `fk_penalty_transaction` FOREIGN KEY (`transactionID`) REFERENCES `tbl_transaction` (`transactionID`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `tbl_student`
--
ALTER TABLE `tbl_student`
  ADD CONSTRAINT `fk_student_user` FOREIGN KEY (`userID`) REFERENCES `tbl_users` (`userID`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `tbl_transaction`
--
ALTER TABLE `tbl_transaction`
  ADD CONSTRAINT `fk_transaction_book` FOREIGN KEY (`bookID`) REFERENCES `tbl_books` (`bookID`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_transaction_student` FOREIGN KEY (`studentID`) REFERENCES `tbl_student` (`studentID`) ON DELETE CASCADE ON UPDATE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;

-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1:3306
-- Generation Time: Jun 07, 2024 at 06:27 PM
-- Server version: 8.3.0
-- PHP Version: 8.2.18

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `sba-awards`
--
CREATE DATABASE IF NOT EXISTS `sba-awards` DEFAULT CHARACTER SET utf8mb3 COLLATE utf8mb3_german2_ci;
USE `sba-awards`;

-- --------------------------------------------------------

--
-- Table structure for table `candidate`
--

DROP TABLE IF EXISTS `candidate`;
CREATE TABLE IF NOT EXISTS `candidate` (
  `CandidateID` int NOT NULL AUTO_INCREMENT,
  `FullName` varchar(200) CHARACTER SET utf8mb3 COLLATE utf8mb3_german2_ci NOT NULL,
  `Email` varchar(200) CHARACTER SET utf8mb3 COLLATE utf8mb3_german2_ci NOT NULL,
  `BirthDate` mediumint NOT NULL,
  `Category` varchar(50) COLLATE utf8mb3_german2_ci NOT NULL,
  `Country` blob NOT NULL,
  `CompanyName` varchar(200) COLLATE utf8mb3_german2_ci NOT NULL,
  `CompanyDescription` varchar(500) COLLATE utf8mb3_german2_ci NOT NULL,
  `InitiationYear` smallint NOT NULL,
  `FacebookLink` varchar(1000) COLLATE utf8mb3_german2_ci NOT NULL,
  `TwitterLink` varchar(1000) COLLATE utf8mb3_german2_ci NOT NULL,
  `InstagramLink` varchar(1000) COLLATE utf8mb3_german2_ci NOT NULL,
  `WebsiteLink` varchar(1000) COLLATE utf8mb3_german2_ci NOT NULL,
  `EmployeeNumber` smallint NOT NULL,
  `Photo` varchar(500) COLLATE utf8mb3_german2_ci NOT NULL,
  `CompanyProof` varchar(500) COLLATE utf8mb3_german2_ci NOT NULL,
  PRIMARY KEY (`CandidateID`)
) ENGINE=MyISAM AUTO_INCREMENT=6 DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_german2_ci;


-- --------------------------------------------------------

--
-- Table structure for table `history`
--

DROP TABLE IF EXISTS `history`;
CREATE TABLE IF NOT EXISTS `history` (
  `history_id` int NOT NULL AUTO_INCREMENT,
  `data` varchar(30) COLLATE utf8mb3_german2_ci NOT NULL,
  `action` varchar(50) COLLATE utf8mb3_german2_ci NOT NULL,
  `date` varchar(20) COLLATE utf8mb3_german2_ci NOT NULL,
  `user` varchar(20) COLLATE utf8mb3_german2_ci NOT NULL,
  PRIMARY KEY (`history_id`)
) ENGINE=MyISAM AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_german2_ci;

--
-- Table structure for table `users`
--

DROP TABLE IF EXISTS `users`;
CREATE TABLE IF NOT EXISTS `users` (
  `User_id` int NOT NULL AUTO_INCREMENT,
  `FirstName` varchar(100) COLLATE utf8mb3_german2_ci NOT NULL,
  `SecondName` varchar(100) CHARACTER SET utf8mb3 COLLATE utf8mb3_german2_ci NOT NULL,
  `UserName` varchar(100) COLLATE utf8mb3_german2_ci NOT NULL,
  `Password` varchar(100) COLLATE utf8mb3_german2_ci NOT NULL,
  `User_Type` varchar(50) COLLATE utf8mb3_german2_ci NOT NULL,
  PRIMARY KEY (`User_id`)
) ENGINE=MyISAM AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_german2_ci;


--
-- Table structure for table `voters`
--

DROP TABLE IF EXISTS `voters`;
CREATE TABLE IF NOT EXISTS `voters` (
  `VoterID` int NOT NULL AUTO_INCREMENT,
  `FirstName` varchar(150) COLLATE utf8mb3_german2_ci NOT NULL,
  `SecondName` varchar(150) CHARACTER SET utf8mb3 COLLATE utf8mb3_german2_ci NOT NULL,
  `Username` varchar(100) COLLATE utf8mb3_german2_ci NOT NULL,
  `Email` varchar(60) CHARACTER SET utf8mb3 COLLATE utf8mb3_german2_ci NOT NULL,
  `Password` varchar(100) CHARACTER SET utf8mb3 COLLATE utf8mb3_german2_ci NOT NULL,
  `status` varchar(20) CHARACTER SET utf8mb3 COLLATE utf8mb3_german2_ci NOT NULL,
  PRIMARY KEY (`VoterID`)
) ENGINE=MyISAM AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_german2_ci;


--
-- Table structure for table `votes`
--

DROP TABLE IF EXISTS `votes`;
CREATE TABLE IF NOT EXISTS `votes` (
  `ID` int NOT NULL AUTO_INCREMENT,
  `CandidateID` int NOT NULL,
  `votes` int NOT NULL,
  PRIMARY KEY (`ID`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_german2_ci;
--
-- Database: `votesystem`
--
CREATE DATABASE IF NOT EXISTS `votesystem` DEFAULT CHARACTER SET utf8mb3 COLLATE utf8mb3_general_ci;
USE `votesystem`;

-- --------------------------------------------------------

--
-- Table structure for table `admin`
--

DROP TABLE IF EXISTS `admin`;
CREATE TABLE IF NOT EXISTS `admin` (
  `id` int NOT NULL AUTO_INCREMENT,
  `username` varchar(50) NOT NULL,
  `password` varchar(60) NOT NULL,
  `firstname` varchar(50) NOT NULL,
  `lastname` varchar(50) NOT NULL,
  `photo` varchar(150) NOT NULL,
  `created_on` date NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb3;


--
-- Table structure for table `candidates`
--

DROP TABLE IF EXISTS `candidates`;
CREATE TABLE IF NOT EXISTS `candidates` (
  `id` int NOT NULL AUTO_INCREMENT,
  `position_id` int NOT NULL,
  `firstname` varchar(30) NOT NULL,
  `lastname` varchar(30) NOT NULL,
  `photo` varchar(150) NOT NULL,
  `platform` text NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=18 DEFAULT CHARSET=utf8mb3;

-- --------------------------------------------------------

--
-- Table structure for table `positions`
--

DROP TABLE IF EXISTS `positions`;
CREATE TABLE IF NOT EXISTS `positions` (
  `id` int NOT NULL AUTO_INCREMENT,
  `description` varchar(50) NOT NULL,
  `max_vote` int NOT NULL,
  `priority` int NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=8 DEFAULT CHARSET=utf8mb3;

-- --------------------------------------------------------

--
-- Table structure for table `voters`
--

DROP TABLE IF EXISTS `voters`;
CREATE TABLE IF NOT EXISTS `voters` (
  `id` int NOT NULL AUTO_INCREMENT,
  `voters_id` varchar(15) NOT NULL,
  `password` varchar(60) NOT NULL,
  `firstname` varchar(30) NOT NULL,
  `lastname` varchar(30) NOT NULL,
  `photo` varchar(150) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb3;

-- --------------------------------------------------------

--
-- Table structure for table `votes`
--

DROP TABLE IF EXISTS `votes`;
CREATE TABLE IF NOT EXISTS `votes` (
  `id` int NOT NULL AUTO_INCREMENT,
  `voters_id` int NOT NULL,
  `candidate_id` int NOT NULL,
  `position_id` int NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=81 DEFAULT CHARSET=utf8mb3;
--
-- Database: `voting-system`
--
CREATE DATABASE IF NOT EXISTS `voting-system` DEFAULT CHARACTER SET utf8mb3 COLLATE utf8mb3_general_ci;
USE `voting-system`;

-- --------------------------------------------------------

--
-- Table structure for table `migrations`
--

DROP TABLE IF EXISTS `migrations`;
CREATE TABLE IF NOT EXISTS `migrations` (
  `id` int UNSIGNED NOT NULL AUTO_INCREMENT,
  `migration` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `batch` int NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

DROP TABLE IF EXISTS `users`;
CREATE TABLE IF NOT EXISTS `users` (
  `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `email` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `email_verified_at` timestamp NULL DEFAULT NULL,
  `password` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `remember_token` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;

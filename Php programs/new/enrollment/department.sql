-- phpMyAdmin SQL Dump
-- version 3.3.9
-- http://www.phpmyadmin.net
--
-- Host: localhost
-- Generation Time: Oct 07, 2011 at 04:55 AM
-- Server version: 5.1.53
-- PHP Version: 5.3.4

SET SQL_MODE="NO_AUTO_VALUE_ON_ZERO";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8 */;

--
-- Database: `department`
--

-- --------------------------------------------------------

--
-- Table structure for table `students`
--

CREATE TABLE IF NOT EXISTS `students` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `sname` longtext NOT NULL,
  `rollno` mediumtext NOT NULL,
  `regno` int(11) NOT NULL,
  `dname` longtext NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=latin1 AUTO_INCREMENT=65 ;

--
-- Dumping data for table `students`
--

INSERT INTO `students` (`id`, `sname`, `rollno`, `regno`, `dname`) VALUES
(37, 'Keziah Grace de Leon', 'BSCS-3RD', 2147483647, 'Computer Science'),
(64, 'Anemae dedumo', '0088345', 2011, 'bscs');

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE IF NOT EXISTS `users` (
  `id` smallint(5) NOT NULL AUTO_INCREMENT,
  `username` varchar(30) NOT NULL DEFAULT '',
  `password` varchar(32) NOT NULL DEFAULT '',
  PRIMARY KEY (`id`),
  UNIQUE KEY `username` (`username`)
) ENGINE=MyISAM  DEFAULT CHARSET=latin1 AUTO_INCREMENT=25 ;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `username`, `password`) VALUES
(1, 'lol', 'lol'),
(2, 'yam', 'qwer'),
(5, '', ''),
(6, 'wqwqwq', '12121'),
(7, '33eee', 'dd'),
(8, 'qqqw', 'qwqwqwq'),
(9, 'qqrerqw', 'ererer'),
(10, 'aaaaaaa', 'qqqqqqqq'),
(11, 'aaaaaaaaa', 'aaaaaaaaaaaa'),
(12, 'aaaaaaaaadwd', 'dwdwdwd'),
(13, 'aaaaaaaaadwdfff', 'fff'),
(14, 'qqqqqqqddqqqqqqqqqqqq', 'ddd'),
(15, 'qqqqqqssqddqqqqqqqqqqqq', 'ssss'),
(16, 'kling', 'gwapa'),
(17, 'qqqqqqqqqqq', 'qqqqqqqqqq'),
(18, 'qqqqqqqqq111qq', '1'),
(19, 'lala', 'idontknow'),
(20, 'bembem', 'bembem'),
(21, 'aliz', 'aliz'),
(22, 'aaaa', 'aaaa'),
(23, 'mae', 'mae'),
(24, 'bhem', '011911');

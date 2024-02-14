-- phpMyAdmin SQL Dump
-- version 5.2.0
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Dec 01, 2023 at 03:19 PM
-- Server version: 10.4.27-MariaDB
-- PHP Version: 8.2.0

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `assessment`
--

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` int(11) NOT NULL,
  `firstName` varchar(50) NOT NULL,
  `lastName` varchar(50) NOT NULL,
  `dob` date NOT NULL,
  `phone` varchar(15) NOT NULL,
  `email` varchar(100) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `firstName`, `lastName`, `dob`, `phone`, `email`) VALUES
(8, 'Sabir 8', 'Hussain', '2005-06-30', '03076069760', 'arif@gmail.com'),
(11, '11 name;l', 'DFKLA', '2004-02-26', '00923071012766', 'arif1299999@gmail.com'),
(12, 'asdf', 'ldka', '2023-11-30', '030000000000', 'arif1@gmail.com'),
(13, 'asdfk;l', 'DFKLA', '2023-12-01', '+92 307-1012766', 'ghulamali.contact1@yahoo.com'),
(15, 'ilyias', 'abbas', '2023-11-25', '923076069760', 'ilyiasabbas1@gmail.com'),
(16, 'tariq', 'Abbas', '2004-01-22', '03076069760', 'tariqabbas2@gmail.com'),
(17, 'ali', 'raza', '2023-11-25', '923186069760', 'aliraza3@gmail.com'),
(19, 'asd;', 'lka', '2023-11-30', '030000000000', 'arif5@gmail.com'),
(20, 'afdjk', 'fdja;l', '2023-11-30', '9999999', 'arf6@gmail.com'),
(21, 'asdfk;l', 'DFKLA', '2023-12-01', '+92 307-1012766', 'ghulamali.contact7@yahoo.com'),
(22, 'asdf', 'ldka', '2023-11-30', '030000000000', 'arif18@gmail.com'),
(23, 'asdfk;l', 'DFKLA', '2023-12-01', '+92 307-1012766', 'ghulamali.contact19@yahoo.com'),
(24, 'arif', 'ali', '2003-11-15', '03076069760', 'arif12@gmail.com'),
(25, 'arif', 'ali', '2005-06-01', '+923076069760', 'ghulamali.contact111@yahoo.com'),
(26, 'Ghulam Ali', 'Fahad', '2004-06-01', '03076069760', 'ghulamali.contact999@yahoo.com'),
(27, 'Airf', 'ali', '2002-06-12', '03076069760', 'ghulamali.contact9999992@yahoo.com'),
(29, 'Ghulam Abbas ', 'Ali', '2000-07-05', '03076069760', 'ghulamali.contact0000@yahoo.com'),
(30, 'Ghulam Ali', 'User', '2003-06-10', '03076069760', 'ghulamali.contact0000000000@yahoo.com');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `email` (`email`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=31;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;

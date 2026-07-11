-- phpMyAdmin SQL Dump
-- Host: localhost
-- Generation Time: Jul 08, 2026
-- Server version: 10.4.24-MariaDB
-- PHP Version: 7.4.29

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";

/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `psmdb`
--
CREATE DATABASE IF NOT EXISTS `psmdb`;
USE `psmdb`;

-- --------------------------------------------------------

--
-- Table structure for table `master_psm`
--

CREATE TABLE `master_psm` (
  `id_psm` int(11) NOT NULL AUTO_INCREMENT,
  `nama_psm` varchar(100) NOT NULL,
  `no_telp` varchar(20) DEFAULT NULL,
  `alamat` text DEFAULT NULL,
  `status` enum('Aktif','Nonaktif') DEFAULT 'Aktif',
  PRIMARY KEY (`id_psm`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id_user` int(11) NOT NULL AUTO_INCREMENT,
  `username` varchar(50) NOT NULL,
  `password` varchar(255) NOT NULL,
  `role` enum('Admin','Petugas') DEFAULT 'Petugas',
  PRIMARY KEY (`id_user`),
  UNIQUE KEY `username` (`username`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id_user`, `username`, `password`, `role`) VALUES
(1, 'admin', '$2y$10$47rhhH1MWk8x2b6PEWMqseCwW8cukngjwN7DdfNzQK1WxfSR9vS66', 'Admin'); 
-- password is 'admin' hashed with password_hash()

-- --------------------------------------------------------

--
-- Table structure for table `setting_aplikasi`
--

CREATE TABLE `setting_aplikasi` (
  `id` int(11) NOT NULL,
  `nama_instansi` varchar(100) NOT NULL,
  `logo` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

--
-- Dumping data for table `setting_aplikasi`
--

INSERT INTO `setting_aplikasi` (`id`, `nama_instansi`, `logo`) VALUES
(1, 'RS Default', 'logo.png');

-- --------------------------------------------------------

--
-- Table structure for table `antri_rujukan_psm`
--

CREATE TABLE `antri_rujukan_psm` (
  `tanggal` date NOT NULL,
  `no_rawat` varchar(17) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `pelaksanaan_rujukan_psm`
--

CREATE TABLE `pelaksanaan_rujukan_psm` (
  `no_rawat` varchar(17) NOT NULL,
  `tanggal` datetime NOT NULL,
  `id_psm` int(11) NOT NULL,
  `keterangan_diberikan_pada` varchar(100) NOT NULL,
  `diberikan_pada` enum('Pasien','Keluarga Pasien') NOT NULL,
  `materi_rujukan` text NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `bukti_pelaksanaan_rujukan_psm`
--

CREATE TABLE `bukti_pelaksanaan_rujukan_psm` (
  `no_rawat` varchar(17) NOT NULL,
  `tanggal` date NOT NULL,
  `bukti` varchar(200) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

--
-- Indexes for dumped tables
--

--
-- Indexes for table `antri_rujukan_psm`
--
ALTER TABLE `antri_rujukan_psm`
  ADD PRIMARY KEY (`no_rawat`,`tanggal`);

--
-- Indexes for table `pelaksanaan_rujukan_psm`
--
ALTER TABLE `pelaksanaan_rujukan_psm`
  ADD PRIMARY KEY (`no_rawat`,`tanggal`),
  ADD KEY `tanggal` (`tanggal`),
  ADD KEY `no_rawat` (`no_rawat`),
  ADD KEY `id_psm` (`id_psm`);

--
-- Indexes for table `bukti_pelaksanaan_rujukan_psm`
--
ALTER TABLE `bukti_pelaksanaan_rujukan_psm`
  ADD PRIMARY KEY (`no_rawat`,`tanggal`);

--
-- Constraints for dumped tables
--

--
-- Constraints for table `pelaksanaan_rujukan_psm`
--
ALTER TABLE `pelaksanaan_rujukan_psm`
  ADD CONSTRAINT `pelaksanaan_rujukan_psm_ibfk_1` FOREIGN KEY (`no_rawat`) REFERENCES `sik`.`reg_periksa` (`no_rawat`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `pelaksanaan_rujukan_psm_ibfk_2` FOREIGN KEY (`id_psm`) REFERENCES `master_psm` (`id_psm`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `bukti_pelaksanaan_rujukan_psm`
--
ALTER TABLE `bukti_pelaksanaan_rujukan_psm`
  ADD CONSTRAINT `bukti_pelaksanaan_rujukan_psm_ibfk_1` FOREIGN KEY (`no_rawat`) REFERENCES `sik`.`reg_periksa` (`no_rawat`) ON DELETE CASCADE ON UPDATE CASCADE;

COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;

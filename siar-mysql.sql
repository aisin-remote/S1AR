-- --------------------------------------------------------
-- Host:                         127.0.0.1
-- Server version:               5.5.67-MariaDB - mariadb.org binary distribution
-- Server OS:                    Win64
-- HeidiSQL Version:             10.2.0.5599
-- --------------------------------------------------------

/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET NAMES utf8 */;
/*!50503 SET NAMES utf8mb4 */;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;

-- Dumping structure for table siar-mysql.employee
CREATE TABLE IF NOT EXISTS `employee` (
  `coid` varchar(150) DEFAULT NULL,
  `empno` varchar(150) DEFAULT NULL,
  `empnm` varchar(150) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- Data exporting was unselected.

-- Dumping structure for table siar-mysql.hirarki
CREATE TABLE IF NOT EXISTS `hirarki` (
  `empno` varchar(150) DEFAULT NULL,
  `hirar` varchar(150) DEFAULT NULL,
  `mutdt` varchar(150) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- Data exporting was unselected.

-- Dumping structure for table siar-mysql.hirarkidesc
CREATE TABLE IF NOT EXISTS `hirarkidesc` (
  `hirar` varchar(150) DEFAULT NULL,
  `descr` varchar(150) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- Data exporting was unselected.

-- Dumping structure for table siar-mysql.holiday
CREATE TABLE IF NOT EXISTS `holiday` (
  `date` varchar(50) DEFAULT NULL,
  `note` varchar(50) DEFAULT NULL,
  `updated_at` varchar(50) DEFAULT NULL,
  `created_at` varchar(50) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- Data exporting was unselected.

-- Dumping structure for table siar-mysql.kehadiran1
CREATE TABLE IF NOT EXISTS `kehadiran1` (
  `empno` varchar(150) DEFAULT NULL,
  `datin` varchar(150) DEFAULT NULL,
  `timin` varchar(150) DEFAULT NULL,
  `datot` varchar(150) DEFAULT NULL,
  `timot` varchar(150) DEFAULT NULL,
  `crtdt` varchar(150) DEFAULT NULL,
  `lupddt` varchar(150) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- Data exporting was unselected.

-- Dumping structure for table siar-mysql.kehadiran2
CREATE TABLE IF NOT EXISTS `kehadiran2` (
  `coid` varchar(150) DEFAULT NULL,
  `empno` varchar(150) DEFAULT NULL,
  `schdt` varchar(150) DEFAULT NULL,
  `rsccd` varchar(150) DEFAULT NULL,
  `crtdt` varchar(150) DEFAULT NULL,
  `lupddt` varchar(150) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- Data exporting was unselected.

-- Dumping structure for table siar-mysql.kehadiranmu
CREATE TABLE IF NOT EXISTS `kehadiranmu` (
  `coid` varchar(150) DEFAULT NULL,
  `empno` varchar(150) DEFAULT NULL,
  `schdt` varchar(150) DEFAULT NULL,
  `rsccd` varchar(150) DEFAULT NULL,
  `crtdt` varchar(150) DEFAULT NULL,
  `lupddt` varchar(150) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- Data exporting was unselected.

-- Dumping structure for table siar-mysql.migrations
CREATE TABLE IF NOT EXISTS `migrations` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `migration` varchar(255) NOT NULL,
  `batch` int(11) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- Data exporting was unselected.

-- Dumping structure for table siar-mysql.users
CREATE TABLE IF NOT EXISTS `users` (
  `id` char(36) NOT NULL,
  `name` varchar(255) NOT NULL,
  `npk` varchar(255) NOT NULL,
  `password` varchar(255) NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `users_npk_unique` (`npk`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- Data exporting was unselected.

/*!40101 SET SQL_MODE=IFNULL(@OLD_SQL_MODE, '') */;
/*!40014 SET FOREIGN_KEY_CHECKS=IF(@OLD_FOREIGN_KEY_CHECKS IS NULL, 1, @OLD_FOREIGN_KEY_CHECKS) */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;

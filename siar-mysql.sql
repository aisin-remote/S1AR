USE `siar-mysql`;

-- Dumping structure for table siar-mysql.employee
CREATE TABLE IF NOT EXISTS `employee` (
  `coid` varchar(150) DEFAULT NULL,
  `empno` varchar(150) DEFAULT NULL,
  `empnm` varchar(150) DEFAULT NULL
);

-- Data exporting was unselected.

-- Dumping structure for table siar-mysql.hirarki
CREATE TABLE IF NOT EXISTS `hirarki` (
  `empno` varchar(150) DEFAULT NULL,
  `hirar` varchar(150) DEFAULT NULL,
  `mutdt` varchar(150) DEFAULT NULL
);

-- Data exporting was unselected.

-- Dumping structure for table siar-mysql.hirarkidesc
CREATE TABLE IF NOT EXISTS `hirarkidesc` (
  `hirar` varchar(150) DEFAULT NULL,
  `descr` varchar(150) DEFAULT NULL
);

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
);

-- Data exporting was unselected.

-- Dumping structure for table siar-mysql.kehadiran2
CREATE TABLE IF NOT EXISTS `kehadiran2` (
  `coid` varchar(150) DEFAULT NULL,
  `empno` varchar(150) DEFAULT NULL,
  `schdt` varchar(150) DEFAULT NULL,
  `rsccd` varchar(150) DEFAULT NULL,
  `crtdt` varchar(150) DEFAULT NULL,
  `lupddt` varchar(150) DEFAULT NULL
);

-- Data exporting was unselected.

-- Dumping structure for table siar-mysql.kehadiranmu
CREATE TABLE IF NOT EXISTS `kehadiranmu` (
  `coid` varchar(150),
  `empno` varchar(150),
  `schdt` varchar(150),
  `rsccd` varchar(150),
  `crtdt` varchar(150) DEFAULT NULL,
  `lupddt` varchar(150) DEFAULT NULL
);

-- Data exporting was unselected.

-- Dumping structure for table siar-mysql.migrations
CREATE TABLE IF NOT EXISTS `migrations` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `migration` varchar(255) NOT NULL,
  `batch` int NOT NULL,
  PRIMARY KEY (`id`)
);

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
);


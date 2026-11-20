-- The backend always registers a second connection to an `environments` schema
-- (backend/bootstrap.php). It is used for multi-tenant hosting (hostname -> env variables,
-- see src/Environment.php, src/EnvironmentRepository.php). schema.sql does not include it.
CREATE DATABASE IF NOT EXISTS `environments` DEFAULT CHARACTER SET utf8mb4;
USE `environments`;

CREATE TABLE IF NOT EXISTS `variables` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `collection` varchar(200) NOT NULL,
  `name` varchar(200) NOT NULL,
  `value` text,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `collection` (`collection`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS `hosts` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `hostname` varchar(255) NOT NULL,
  `collection` varchar(200) NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `hostname` (`hostname`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

GRANT ALL PRIVILEGES ON `environments`.* TO 'missions'@'%';
GRANT ALL PRIVILEGES ON `idea-village-sandbox`.* TO 'missions'@'%';
FLUSH PRIVILEGES;

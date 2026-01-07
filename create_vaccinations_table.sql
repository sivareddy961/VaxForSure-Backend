-- Create vaccinations table if it doesn't exist
-- Run this in phpMyAdmin: http://localhost:8080/phpmyadmin

USE `vaxforsure`;

-- Drop table if exists (optional - only use if you want to recreate)
-- DROP TABLE IF EXISTS `vaccinations`;

-- Create vaccinations table
CREATE TABLE IF NOT EXISTS `vaccinations` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `child_id` int(11) NOT NULL,
  `vaccine_name` varchar(255) NOT NULL,
  `dose_number` int(11) DEFAULT 1,
  `scheduled_date` date DEFAULT NULL,
  `completed_date` date DEFAULT NULL,
  `status` enum('pending','completed','missed') DEFAULT 'pending',
  `healthcare_facility` varchar(255) DEFAULT NULL,
  `batch_lot_number` varchar(100) DEFAULT NULL,
  `notes` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `child_id` (`child_id`),
  KEY `vaccine_name` (`vaccine_name`),
  KEY `status` (`status`),
  CONSTRAINT `fk_vaccinations_child_id` FOREIGN KEY (`child_id`) REFERENCES `children` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Verify table creation
SELECT 'vaccinations table created successfully!' as message;


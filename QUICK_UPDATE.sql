-- Quick Update - Copy and paste this entire file into phpMyAdmin SQL tab

USE `vaxforsure`;

-- Add parent_name to children table (if not exists)
ALTER TABLE `children` ADD COLUMN IF NOT EXISTS `parent_name` VARCHAR(255) NULL AFTER `user_id`;

-- Create health_details table if not exists
CREATE TABLE IF NOT EXISTS `health_details` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `child_id` int(11) NOT NULL,
  `birth_weight` DECIMAL(5,2) DEFAULT NULL,
  `birth_height` DECIMAL(5,2) DEFAULT NULL,
  `blood_group` VARCHAR(10) DEFAULT NULL,
  `allergies` TEXT DEFAULT NULL,
  `medical_conditions` TEXT DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `child_id` (`child_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Add birth_weight if not exists (for existing tables)
SET @exist := (SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS 
               WHERE TABLE_SCHEMA = DATABASE() 
               AND TABLE_NAME = 'health_details' 
               AND COLUMN_NAME = 'birth_weight');
SET @sqlstmt := IF(@exist = 0, 
                   'ALTER TABLE `health_details` ADD COLUMN `birth_weight` DECIMAL(5,2) DEFAULT NULL AFTER `child_id`',
                   'SELECT "birth_weight already exists" AS message');
PREPARE stmt FROM @sqlstmt;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

-- Add birth_height if not exists (for existing tables)
SET @exist := (SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS 
               WHERE TABLE_SCHEMA = DATABASE() 
               AND TABLE_NAME = 'health_details' 
               AND COLUMN_NAME = 'birth_height');
SET @sqlstmt := IF(@exist = 0, 
                   'ALTER TABLE `health_details` ADD COLUMN `birth_height` DECIMAL(5,2) DEFAULT NULL AFTER `birth_weight`',
                   'SELECT "birth_height already exists" AS message');
PREPARE stmt FROM @sqlstmt;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

-- Verify changes
DESCRIBE `children`;
DESCRIBE `health_details`;


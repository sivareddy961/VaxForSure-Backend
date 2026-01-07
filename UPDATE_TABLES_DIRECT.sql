-- ============================================
-- VaxForSure - Direct Database Update SQL
-- Run this directly in phpMyAdmin SQL tab
-- Port: 3307
-- ============================================

USE `vaxforsure`;

-- ============================================
-- 1. ADD parent_name COLUMN TO children TABLE
-- ============================================
-- Check and add parent_name column if it doesn't exist
SET @col_exists = 0;
SELECT COUNT(*) INTO @col_exists
FROM information_schema.COLUMNS
WHERE TABLE_SCHEMA = 'vaxforsure'
  AND TABLE_NAME = 'children'
  AND COLUMN_NAME = 'parent_name';

SET @sql = IF(@col_exists = 0,
    'ALTER TABLE `children` ADD COLUMN `parent_name` VARCHAR(255) NULL AFTER `user_id`',
    'SELECT "Column parent_name already exists" AS message');
PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

-- ============================================
-- 2. CREATE health_details TABLE IF NOT EXISTS
-- ============================================
CREATE TABLE IF NOT EXISTS `health_details` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `child_id` int(11) NOT NULL,
  `birth_weight` DECIMAL(5,2) DEFAULT NULL COMMENT 'Birth weight in kg',
  `birth_height` DECIMAL(5,2) DEFAULT NULL COMMENT 'Birth height in cm',
  `blood_group` VARCHAR(10) DEFAULT NULL,
  `allergies` TEXT DEFAULT NULL,
  `medical_conditions` TEXT DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `child_id` (`child_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================
-- 3. ADD birth_weight COLUMN IF NOT EXISTS
-- ============================================
SET @col_exists = 0;
SELECT COUNT(*) INTO @col_exists
FROM information_schema.COLUMNS
WHERE TABLE_SCHEMA = 'vaxforsure'
  AND TABLE_NAME = 'health_details'
  AND COLUMN_NAME = 'birth_weight';

SET @sql = IF(@col_exists = 0,
    'ALTER TABLE `health_details` ADD COLUMN `birth_weight` DECIMAL(5,2) DEFAULT NULL COMMENT "Birth weight in kg" AFTER `child_id`',
    'SELECT "Column birth_weight already exists" AS message');
PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

-- ============================================
-- 4. ADD birth_height COLUMN IF NOT EXISTS
-- ============================================
SET @col_exists = 0;
SELECT COUNT(*) INTO @col_exists
FROM information_schema.COLUMNS
WHERE TABLE_SCHEMA = 'vaxforsure'
  AND TABLE_NAME = 'health_details'
  AND COLUMN_NAME = 'birth_height';

SET @sql = IF(@col_exists = 0,
    'ALTER TABLE `health_details` ADD COLUMN `birth_height` DECIMAL(5,2) DEFAULT NULL COMMENT "Birth height in cm" AFTER `birth_weight`',
    'SELECT "Column birth_height already exists" AS message');
PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

-- ============================================
-- VERIFICATION - Show table structures
-- ============================================
SELECT 'Children Table Structure:' AS info;
DESCRIBE `children`;

SELECT 'Health Details Table Structure:' AS info;
DESCRIBE `health_details`;

SELECT 'Database update completed successfully!' AS status;




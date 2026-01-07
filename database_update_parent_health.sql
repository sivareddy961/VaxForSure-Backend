-- ============================================
-- VaxForSure - Database Update Script
-- Updates children table and health_details table structure
-- ============================================

USE `vaxforsure`;

-- ============================================
-- 1. UPDATE children TABLE - Add parent_name column
-- ============================================
-- Check if parent_name column exists, if not add it
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
-- 2. UPDATE health_details TABLE - Add birth_weight and birth_height
-- ============================================
-- Check if health_details table exists, if not create it
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
  UNIQUE KEY `child_id` (`child_id`),
  KEY `fk_health_child` (`child_id`),
  CONSTRAINT `fk_health_child` FOREIGN KEY (`child_id`) REFERENCES `children` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Check if birth_weight column exists, if not add it
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

-- Check if birth_height column exists, if not add it
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
-- VERIFICATION
-- ============================================
-- Show updated structure
DESCRIBE `children`;
DESCRIBE `health_details`;

-- ============================================
-- SUCCESS MESSAGE
-- ============================================
SELECT 'Database update completed successfully!' AS status;




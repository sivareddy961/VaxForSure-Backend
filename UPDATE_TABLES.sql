-- ============================================
-- VaxForSure - Direct SQL Update Commands
-- Run this SQL directly in phpMyAdmin
-- ============================================

USE `vaxforsure`;

-- ============================================
-- 1. Add parent_name column to children table
-- ============================================

-- Check and add parent_name column if it doesn't exist
SET @dbname = DATABASE();
SET @tablename = "children";
SET @columnname = "parent_name";
SET @preparedStatement = (SELECT IF(
  (
    SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS
    WHERE
      (TABLE_SCHEMA = @dbname)
      AND (TABLE_NAME = @tablename)
      AND (COLUMN_NAME = @columnname)
  ) > 0,
  "SELECT 'Column parent_name already exists' AS result;",
  "ALTER TABLE `children` ADD COLUMN `parent_name` VARCHAR(255) NULL AFTER `user_id`;"
));
PREPARE alterIfNotExists FROM @preparedStatement;
EXECUTE alterIfNotExists;
DEALLOCATE PREPARE alterIfNotExists;

-- ============================================
-- 2. Create health_details table if it doesn't exist
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
  UNIQUE KEY `child_id` (`child_id`),
  KEY `fk_health_child` (`child_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================
-- 3. Add birth_weight column if it doesn't exist
-- ============================================

SET @columnname = "birth_weight";
SET @preparedStatement = (SELECT IF(
  (
    SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS
    WHERE
      (TABLE_SCHEMA = @dbname)
      AND (TABLE_NAME = "health_details")
      AND (COLUMN_NAME = @columnname)
  ) > 0,
  "SELECT 'Column birth_weight already exists' AS result;",
  "ALTER TABLE `health_details` ADD COLUMN `birth_weight` DECIMAL(5,2) DEFAULT NULL COMMENT 'Birth weight in kg' AFTER `child_id`;"
));
PREPARE alterIfNotExists FROM @preparedStatement;
EXECUTE alterIfNotExists;
DEALLOCATE PREPARE alterIfNotExists;

-- ============================================
-- 4. Add birth_height column if it doesn't exist
-- ============================================

SET @columnname = "birth_height";
SET @preparedStatement = (SELECT IF(
  (
    SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS
    WHERE
      (TABLE_SCHEMA = @dbname)
      AND (TABLE_NAME = "health_details")
      AND (COLUMN_NAME = @columnname)
  ) > 0,
  "SELECT 'Column birth_height already exists' AS result;",
  "ALTER TABLE `health_details` ADD COLUMN `birth_height` DECIMAL(5,2) DEFAULT NULL COMMENT 'Birth height in cm' AFTER `birth_weight`;"
));
PREPARE alterIfNotExists FROM @preparedStatement;
EXECUTE alterIfNotExists;
DEALLOCATE PREPARE alterIfNotExists;

-- ============================================
-- 5. Verify the changes
-- ============================================

-- Show children table structure
SELECT 'Children Table Structure:' AS '';
DESCRIBE `children`;

-- Show health_details table structure
SELECT 'Health Details Table Structure:' AS '';
DESCRIBE `health_details`;

-- ============================================
-- SUCCESS MESSAGE
-- ============================================

SELECT 'Database update completed successfully!' AS status;


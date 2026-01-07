-- ============================================
-- VaxForSure - Simple Database Update SQL
-- This script directly adds columns without checking
-- If columns already exist, you'll get an error (which is fine)
-- Port: 3307
-- ============================================

USE `vaxforsure`;

-- ============================================
-- 1. ADD parent_name COLUMN TO children TABLE
-- ============================================
-- Note: If column already exists, you'll see an error - that's okay!
ALTER TABLE `children` ADD COLUMN `parent_name` VARCHAR(255) NULL AFTER `user_id`;

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
-- 3. ADD birth_weight COLUMN TO health_details
-- ============================================
-- Note: If column already exists, you'll see an error - that's okay!
ALTER TABLE `health_details` ADD COLUMN `birth_weight` DECIMAL(5,2) DEFAULT NULL COMMENT 'Birth weight in kg' AFTER `child_id`;

-- ============================================
-- 4. ADD birth_height COLUMN TO health_details
-- ============================================
-- Note: If column already exists, you'll see an error - that's okay!
ALTER TABLE `health_details` ADD COLUMN `birth_height` DECIMAL(5,2) DEFAULT NULL COMMENT 'Birth height in cm' AFTER `birth_weight`;

-- ============================================
-- VERIFICATION
-- ============================================
-- Check children table structure
DESCRIBE `children`;

-- Check health_details table structure
DESCRIBE `health_details`;

SELECT 'Update completed! Check the table structures above.' AS message;




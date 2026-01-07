-- ============================================
-- VaxForSure - Simple Database Update
-- Copy this entire file and paste into phpMyAdmin SQL tab
-- ============================================

USE `vaxforsure`;

-- ============================================
-- Step 1: Add parent_name to children table
-- ============================================

-- First, check if column exists (this query shows the result)
SELECT COUNT(*) as column_exists 
FROM information_schema.COLUMNS 
WHERE TABLE_SCHEMA = 'vaxforsure' 
  AND TABLE_NAME = 'children' 
  AND COLUMN_NAME = 'parent_name';

-- If above shows 0, run this:
ALTER TABLE `children` ADD COLUMN `parent_name` VARCHAR(255) NULL AFTER `user_id`;

-- ============================================
-- Step 2: Create/Update health_details table
-- ============================================

-- Create table if it doesn't exist
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

-- ============================================
-- Step 3: Add birth_weight column if missing
-- ============================================

-- Check if column exists
SELECT COUNT(*) as column_exists 
FROM information_schema.COLUMNS 
WHERE TABLE_SCHEMA = 'vaxforsure' 
  AND TABLE_NAME = 'health_details' 
  AND COLUMN_NAME = 'birth_weight';

-- If above shows 0, run this:
ALTER TABLE `health_details` ADD COLUMN `birth_weight` DECIMAL(5,2) DEFAULT NULL AFTER `child_id`;

-- ============================================
-- Step 4: Add birth_height column if missing
-- ============================================

-- Check if column exists
SELECT COUNT(*) as column_exists 
FROM information_schema.COLUMNS 
WHERE TABLE_SCHEMA = 'vaxforsure' 
  AND TABLE_NAME = 'health_details' 
  AND COLUMN_NAME = 'birth_height';

-- If above shows 0, run this:
ALTER TABLE `health_details` ADD COLUMN `birth_height` DECIMAL(5,2) DEFAULT NULL AFTER `birth_weight`;

-- ============================================
-- Step 5: Verify changes
-- ============================================

-- View children table structure
DESCRIBE `children`;

-- View health_details table structure
DESCRIBE `health_details`;


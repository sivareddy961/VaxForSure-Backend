-- ============================================
-- VaxForSure - Final Database Update Script
-- This script will update your tables safely
-- Run each section separately in phpMyAdmin if you get errors
-- ============================================

USE `vaxforsure`;

-- ============================================
-- SECTION 1: Add parent_name to children
-- Run this section first
-- ============================================

-- Check if parent_name column exists (view result to see if it's 0 or 1)
SELECT 
    CASE 
        WHEN COUNT(*) > 0 THEN 'parent_name column EXISTS'
        ELSE 'parent_name column DOES NOT EXIST - Run the ALTER TABLE command below'
    END AS status
FROM information_schema.COLUMNS 
WHERE TABLE_SCHEMA = 'vaxforsure' 
  AND TABLE_NAME = 'children' 
  AND COLUMN_NAME = 'parent_name';

-- If status shows "DOES NOT EXIST", run this command:
ALTER TABLE `children` ADD COLUMN `parent_name` VARCHAR(255) NULL AFTER `user_id`;

-- ============================================
-- SECTION 2: Ensure health_details table exists
-- Run this section second
-- ============================================

-- This will create the table if it doesn't exist, or do nothing if it exists
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
-- SECTION 3: Add birth_weight column
-- Run this section third (skip if table was just created)
-- ============================================

-- Check if birth_weight exists
SELECT 
    CASE 
        WHEN COUNT(*) > 0 THEN 'birth_weight column EXISTS'
        ELSE 'birth_weight column DOES NOT EXIST - Run the ALTER TABLE command below'
    END AS status
FROM information_schema.COLUMNS 
WHERE TABLE_SCHEMA = 'vaxforsure' 
  AND TABLE_NAME = 'health_details' 
  AND COLUMN_NAME = 'birth_weight';

-- If status shows "DOES NOT EXIST", run this command:
ALTER TABLE `health_details` ADD COLUMN `birth_weight` DECIMAL(5,2) DEFAULT NULL COMMENT 'Birth weight in kg' AFTER `child_id`;

-- ============================================
-- SECTION 4: Add birth_height column
-- Run this section fourth (skip if table was just created)
-- ============================================

-- Check if birth_height exists
SELECT 
    CASE 
        WHEN COUNT(*) > 0 THEN 'birth_height column EXISTS'
        ELSE 'birth_height column DOES NOT EXIST - Run the ALTER TABLE command below'
    END AS status
FROM information_schema.COLUMNS 
WHERE TABLE_SCHEMA = 'vaxforsure' 
  AND TABLE_NAME = 'health_details' 
  AND COLUMN_NAME = 'birth_height';

-- If status shows "DOES NOT EXIST", run this command:
ALTER TABLE `health_details` ADD COLUMN `birth_height` DECIMAL(5,2) DEFAULT NULL COMMENT 'Birth height in cm' AFTER `birth_weight`;

-- ============================================
-- SECTION 5: Verify all changes
-- Run this section last to verify
-- ============================================

-- Show children table structure (should show parent_name)
SELECT '=== CHILDREN TABLE STRUCTURE ===' AS '';
DESCRIBE `children`;

-- Show health_details table structure (should show birth_weight and birth_height)
SELECT '=== HEALTH_DETAILS TABLE STRUCTURE ===' AS '';
DESCRIBE `health_details`;

-- Final verification query
SELECT 
    'parent_name in children' AS column_check,
    CASE 
        WHEN COUNT(*) > 0 THEN '✓ EXISTS'
        ELSE '✗ MISSING'
    END AS status
FROM information_schema.COLUMNS 
WHERE TABLE_SCHEMA = 'vaxforsure' 
  AND TABLE_NAME = 'children' 
  AND COLUMN_NAME = 'parent_name'

UNION ALL

SELECT 
    'birth_weight in health_details' AS column_check,
    CASE 
        WHEN COUNT(*) > 0 THEN '✓ EXISTS'
        ELSE '✗ MISSING'
    END AS status
FROM information_schema.COLUMNS 
WHERE TABLE_SCHEMA = 'vaxforsure' 
  AND TABLE_NAME = 'health_details' 
  AND COLUMN_NAME = 'birth_weight'

UNION ALL

SELECT 
    'birth_height in health_details' AS column_check,
    CASE 
        WHEN COUNT(*) > 0 THEN '✓ EXISTS'
        ELSE '✗ MISSING'
    END AS status
FROM information_schema.COLUMNS 
WHERE TABLE_SCHEMA = 'vaxforsure' 
  AND TABLE_NAME = 'health_details' 
  AND COLUMN_NAME = 'birth_height';


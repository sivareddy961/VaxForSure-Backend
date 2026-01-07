-- ============================================
-- Add parent_name and child_name to health_details table
-- Run this in phpMyAdmin SQL tab
-- ============================================

USE `vaxforsure`;

-- Add parent_name column (if it doesn't exist)
ALTER TABLE `health_details` ADD COLUMN `parent_name` VARCHAR(255) NULL AFTER `child_id`;

-- Add child_name column (if it doesn't exist)
ALTER TABLE `health_details` ADD COLUMN `child_name` VARCHAR(255) NULL AFTER `parent_name`;

-- Verify the structure
DESCRIBE `health_details`;

SELECT 'Columns added successfully!' AS message;




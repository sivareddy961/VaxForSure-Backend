-- Update users table to add google_id column
-- Run this SQL in phpMyAdmin if the table already exists

USE `vaxforsure`;

-- Add google_id column if it doesn't exist
ALTER TABLE `users` 
ADD COLUMN IF NOT EXISTS `google_id` varchar(255) DEFAULT NULL AFTER `password`;

-- Make password nullable (for Google users)
ALTER TABLE `users` 
MODIFY COLUMN `password` varchar(255) DEFAULT NULL;

-- Add unique constraint on google_id
ALTER TABLE `users` 
ADD UNIQUE KEY IF NOT EXISTS `google_id` (`google_id`);

-- Set email_verified to 1 for existing Google users (if any)
-- This is optional, run only if needed


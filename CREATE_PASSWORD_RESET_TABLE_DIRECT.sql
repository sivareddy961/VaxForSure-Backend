-- ============================================
-- Create password_reset_otps Table
-- Run this directly in phpMyAdmin SQL tab
-- ============================================

USE `vaxforsure`;

-- Drop table if exists (optional - remove if you want to keep existing data)
-- DROP TABLE IF EXISTS `password_reset_otps`;

-- Create password_reset_otps table
CREATE TABLE IF NOT EXISTS `password_reset_otps` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `email` VARCHAR(255) NOT NULL,
  `otp_code` VARCHAR(6) NOT NULL,
  `expires_at` DATETIME NOT NULL,
  `used` tinyint(1) DEFAULT 0 COMMENT '0 = not used, 1 = used',
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `email` (`email`),
  KEY `otp_code` (`otp_code`),
  KEY `expires_at` (`expires_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Verify table was created
SELECT 'Password Reset OTPs table created successfully!' AS message;

-- Show table structure
DESCRIBE `password_reset_otps`;




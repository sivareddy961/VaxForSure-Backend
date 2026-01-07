<?php
/**
 * VaxForSure - Database Update Script
 * This script updates the database schema to add parent_name column to children table
 * and ensures health_details table has birth_weight and birth_height columns
 * 
 * Run this script once after placing it in your htdocs/vaxforsure directory
 * Access it via: http://localhost/vaxforsure/SETUP_DATABASE_UPDATE.php
 */

require_once 'config.php';

// Set JSON header
header('Content-Type: application/json; charset=UTF-8');

// Only allow GET requests for security (remove this in production or add authentication)
if ($_SERVER['REQUEST_METHOD'] !== 'GET' && !isset($_GET['run'])) {
    echo json_encode([
        'success' => false,
        'message' => 'Access this script via: http://localhost/vaxforsure/SETUP_DATABASE_UPDATE.php?run=1'
    ]);
    exit;
}

// Check if run parameter is set
if (!isset($_GET['run']) || $_GET['run'] !== '1') {
    echo json_encode([
        'success' => false,
        'message' => 'Add ?run=1 to the URL to execute the update'
    ]);
    exit;
}

$conn = getDBConnection();

if (!$conn) {
    echo json_encode([
        'success' => false,
        'message' => 'Database connection failed'
    ]);
    exit;
}

$results = [];
$errors = [];

try {
    // 1. Add parent_name column to children table if it doesn't exist
    $checkColumn = $conn->query("SHOW COLUMNS FROM `children` LIKE 'parent_name'");
    if ($checkColumn->num_rows == 0) {
        $sql = "ALTER TABLE `children` ADD COLUMN `parent_name` VARCHAR(255) NULL AFTER `user_id`";
        if ($conn->query($sql)) {
            $results[] = "✓ Added parent_name column to children table";
        } else {
            $errors[] = "✗ Failed to add parent_name column: " . $conn->error;
        }
    } else {
        $results[] = "✓ parent_name column already exists in children table";
    }
    
    // 2. Ensure health_details table exists with correct structure
    $createHealthTable = "CREATE TABLE IF NOT EXISTS `health_details` (
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
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci";
    
    if ($conn->query($createHealthTable)) {
        $results[] = "✓ health_details table created/verified";
    } else {
        // If table exists, check if columns exist
        $checkBirthWeight = $conn->query("SHOW COLUMNS FROM `health_details` LIKE 'birth_weight'");
        if ($checkBirthWeight->num_rows == 0) {
            $sql = "ALTER TABLE `health_details` ADD COLUMN `birth_weight` DECIMAL(5,2) DEFAULT NULL COMMENT 'Birth weight in kg' AFTER `child_id`";
            if ($conn->query($sql)) {
                $results[] = "✓ Added birth_weight column to health_details table";
            } else {
                $errors[] = "✗ Failed to add birth_weight column: " . $conn->error;
            }
        } else {
            $results[] = "✓ birth_weight column already exists in health_details table";
        }
        
        $checkBirthHeight = $conn->query("SHOW COLUMNS FROM `health_details` LIKE 'birth_height'");
        if ($checkBirthHeight->num_rows == 0) {
            $sql = "ALTER TABLE `health_details` ADD COLUMN `birth_height` DECIMAL(5,2) DEFAULT NULL COMMENT 'Birth height in cm' AFTER `birth_weight`";
            if ($conn->query($sql)) {
                $results[] = "✓ Added birth_height column to health_details table";
            } else {
                $errors[] = "✗ Failed to add birth_height column: " . $conn->error;
            }
        } else {
            $results[] = "✓ birth_height column already exists in health_details table";
        }
    }
    
    echo json_encode([
        'success' => count($errors) == 0,
        'message' => count($errors) == 0 ? 'Database update completed successfully!' : 'Some errors occurred',
        'results' => $results,
        'errors' => $errors
    ], JSON_PRETTY_PRINT);
    
} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'message' => 'An error occurred: ' . $e->getMessage(),
        'results' => $results,
        'errors' => array_merge($errors, [$e->getMessage()])
    ], JSON_PRETTY_PRINT);
} finally {
    $conn->close();
}

?>




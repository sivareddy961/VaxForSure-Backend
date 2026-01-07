<?php
/**
 * VaxForSure - Direct Database Update Script
 * This script directly updates the database schema
 * Access: http://localhost/vaxforsure/UPDATE_DATABASE_NOW.php
 */

// Database configuration
$host = 'localhost';
$user = 'root';
$pass = ''; // Empty for default XAMPP MySQL
$db = 'vaxforsure';
$port = 3307; // User specified port

// Set JSON header
header('Content-Type: text/html; charset=UTF-8');

echo "<!DOCTYPE html><html><head><title>Database Update</title>";
echo "<style>body{font-family:Arial;padding:20px;background:#f5f5f5;}";
echo ".success{color:green;padding:10px;background:#d4edda;border:1px solid #c3e6cb;margin:10px 0;border-radius:5px;}";
echo ".error{color:red;padding:10px;background:#f8d7da;border:1px solid #f5c6cb;margin:10px 0;border-radius:5px;}";
echo ".info{color:#0c5460;padding:10px;background:#d1ecf1;border:1px solid #bee5eb;margin:10px 0;border-radius:5px;}";
echo "h1{color:#333;}h2{color:#666;margin-top:20px;}</style></head><body>";
echo "<h1>VaxForSure Database Update</h1>";

// Connect to database
$conn = new mysqli($host, $user, $pass, $db, $port);

if ($conn->connect_error) {
    die("<div class='error'><strong>Connection Failed:</strong> " . $conn->connect_error . "</div></body></html>");
}

$conn->set_charset("utf8mb4");
echo "<div class='info'><strong>✓ Connected to database successfully</strong><br>Database: $db | Port: $port</div>";

$results = [];
$errors = [];

try {
    // ============================================
    // 1. ADD parent_name COLUMN TO children TABLE
    // ============================================
    echo "<h2>Step 1: Updating children table</h2>";
    
    // Check if parent_name column exists
    $checkSql = "SHOW COLUMNS FROM `children` LIKE 'parent_name'";
    $result = $conn->query($checkSql);
    
    if ($result && $result->num_rows == 0) {
        // Column doesn't exist, add it
        $alterSql = "ALTER TABLE `children` ADD COLUMN `parent_name` VARCHAR(255) NULL AFTER `user_id`";
        if ($conn->query($alterSql)) {
            $results[] = "✓ Added 'parent_name' column to 'children' table";
            echo "<div class='success'>✓ Successfully added 'parent_name' column to 'children' table</div>";
        } else {
            $errorMsg = $conn->error;
            $errors[] = "✗ Failed to add 'parent_name' column: " . $errorMsg;
            echo "<div class='error'>✗ Failed to add 'parent_name' column: " . htmlspecialchars($errorMsg) . "</div>";
        }
    } else {
        $results[] = "✓ 'parent_name' column already exists in 'children' table";
        echo "<div class='info'>✓ 'parent_name' column already exists in 'children' table</div>";
    }
    
    // ============================================
    // 2. CREATE/UPDATE health_details TABLE
    // ============================================
    echo "<h2>Step 2: Updating health_details table</h2>";
    
    // Check if health_details table exists
    $tableCheck = $conn->query("SHOW TABLES LIKE 'health_details'");
    
    if (!$tableCheck || $tableCheck->num_rows == 0) {
        // Create the table
        $createTable = "CREATE TABLE `health_details` (
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
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci";
        
        // Try to create without foreign key first (in case children table doesn't have proper structure)
        $createTableNoFK = "CREATE TABLE `health_details` (
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
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci";
        
        if ($conn->query($createTableNoFK)) {
            $results[] = "✓ Created 'health_details' table";
            echo "<div class='success'>✓ Successfully created 'health_details' table</div>";
        } else {
            $errorMsg = $conn->error;
            $errors[] = "✗ Failed to create 'health_details' table: " . $errorMsg;
            echo "<div class='error'>✗ Failed to create 'health_details' table: " . htmlspecialchars($errorMsg) . "</div>";
        }
    } else {
        echo "<div class='info'>✓ 'health_details' table already exists</div>";
        
        // Check and add birth_weight column
        $checkWeight = $conn->query("SHOW COLUMNS FROM `health_details` LIKE 'birth_weight'");
        if (!$checkWeight || $checkWeight->num_rows == 0) {
            $alterSql = "ALTER TABLE `health_details` ADD COLUMN `birth_weight` DECIMAL(5,2) DEFAULT NULL COMMENT 'Birth weight in kg' AFTER `child_id`";
            if ($conn->query($alterSql)) {
                $results[] = "✓ Added 'birth_weight' column to 'health_details' table";
                echo "<div class='success'>✓ Successfully added 'birth_weight' column</div>";
            } else {
                $errorMsg = $conn->error;
                $errors[] = "✗ Failed to add 'birth_weight' column: " . $errorMsg;
                echo "<div class='error'>✗ Failed to add 'birth_weight' column: " . htmlspecialchars($errorMsg) . "</div>";
            }
        } else {
            $results[] = "✓ 'birth_weight' column already exists";
            echo "<div class='info'>✓ 'birth_weight' column already exists</div>";
        }
        
        // Check and add birth_height column
        $checkHeight = $conn->query("SHOW COLUMNS FROM `health_details` LIKE 'birth_height'");
        if (!$checkHeight || $checkHeight->num_rows == 0) {
            // Find position - after birth_weight if it exists, otherwise after child_id
            $position = "AFTER `birth_weight`";
            $checkWeight2 = $conn->query("SHOW COLUMNS FROM `health_details` LIKE 'birth_weight'");
            if (!$checkWeight2 || $checkWeight2->num_rows == 0) {
                $position = "AFTER `child_id`";
            }
            
            $alterSql = "ALTER TABLE `health_details` ADD COLUMN `birth_height` DECIMAL(5,2) DEFAULT NULL COMMENT 'Birth height in cm' $position";
            if ($conn->query($alterSql)) {
                $results[] = "✓ Added 'birth_height' column to 'health_details' table";
                echo "<div class='success'>✓ Successfully added 'birth_height' column</div>";
            } else {
                $errorMsg = $conn->error;
                $errors[] = "✗ Failed to add 'birth_height' column: " . $errorMsg;
                echo "<div class='error'>✗ Failed to add 'birth_height' column: " . htmlspecialchars($errorMsg) . "</div>";
            }
        } else {
            $results[] = "✓ 'birth_height' column already exists";
            echo "<div class='info'>✓ 'birth_height' column already exists</div>";
        }
    }
    
    // ============================================
    // 3. VERIFY CHANGES
    // ============================================
    echo "<h2>Step 3: Verification</h2>";
    
    // Verify children table
    echo "<h3>Children Table Structure:</h3>";
    echo "<table border='1' cellpadding='10' style='border-collapse:collapse;background:white;margin:10px 0;'>";
    echo "<tr style='background:#007bff;color:white;'><th>Field</th><th>Type</th><th>Null</th><th>Key</th><th>Default</th></tr>";
    $descChildren = $conn->query("DESCRIBE `children`");
    if ($descChildren) {
        while ($row = $descChildren->fetch_assoc()) {
            $highlight = ($row['Field'] == 'parent_name') ? "style='background:#d4edda;'" : "";
            echo "<tr $highlight>";
            echo "<td><strong>" . htmlspecialchars($row['Field']) . "</strong></td>";
            echo "<td>" . htmlspecialchars($row['Type']) . "</td>";
            echo "<td>" . htmlspecialchars($row['Null']) . "</td>";
            echo "<td>" . htmlspecialchars($row['Key']) . "</td>";
            echo "<td>" . htmlspecialchars($row['Default'] ?? 'NULL') . "</td>";
            echo "</tr>";
        }
    }
    echo "</table>";
    
    // Verify health_details table
    echo "<h3>Health Details Table Structure:</h3>";
    echo "<table border='1' cellpadding='10' style='border-collapse:collapse;background:white;margin:10px 0;'>";
    echo "<tr style='background:#007bff;color:white;'><th>Field</th><th>Type</th><th>Null</th><th>Key</th><th>Default</th></tr>";
    $descHealth = $conn->query("DESCRIBE `health_details`");
    if ($descHealth) {
        while ($row = $descHealth->fetch_assoc()) {
            $highlight = ($row['Field'] == 'birth_weight' || $row['Field'] == 'birth_height') ? "style='background:#d4edda;'" : "";
            echo "<tr $highlight>";
            echo "<td><strong>" . htmlspecialchars($row['Field']) . "</strong></td>";
            echo "<td>" . htmlspecialchars($row['Type']) . "</td>";
            echo "<td>" . htmlspecialchars($row['Null']) . "</td>";
            echo "<td>" . htmlspecialchars($row['Key']) . "</td>";
            echo "<td>" . htmlspecialchars($row['Default'] ?? 'NULL') . "</td>";
            echo "</tr>";
        }
    } else {
        echo "<tr><td colspan='5' style='color:red;'>Table 'health_details' does not exist</td></tr>";
    }
    echo "</table>";
    
    // Final summary
    echo "<h2>Summary</h2>";
    if (count($errors) == 0) {
        echo "<div class='success'><strong>✓ All updates completed successfully!</strong><br>";
        echo "Total operations: " . count($results) . "</div>";
    } else {
        echo "<div class='error'><strong>⚠ Some errors occurred:</strong><br>";
        foreach ($errors as $error) {
            echo htmlspecialchars($error) . "<br>";
        }
        echo "</div>";
    }
    
    if (count($results) > 0) {
        echo "<div class='info'><strong>Completed Operations:</strong><ul>";
        foreach ($results as $result) {
            echo "<li>" . htmlspecialchars($result) . "</li>";
        }
        echo "</ul></div>";
    }
    
} catch (Exception $e) {
    echo "<div class='error'><strong>Exception:</strong> " . htmlspecialchars($e->getMessage()) . "</div>";
} finally {
    $conn->close();
    echo "<div class='info'><strong>Database connection closed.</strong></div>";
    echo "</body></html>";
}

?>

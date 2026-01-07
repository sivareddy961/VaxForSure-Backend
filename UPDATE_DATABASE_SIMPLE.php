<?php
/**
 * VaxForSure - Simple Database Update Script
 * This script directly updates tables without using information_schema
 * Access: http://localhost/vaxforsure/UPDATE_DATABASE_SIMPLE.php
 */

// Database configuration
$host = 'localhost';
$user = 'root';
$pass = ''; // Empty for default XAMPP MySQL
$db = 'vaxforsure';
$port = 3307; // User specified port

// Set HTML header
header('Content-Type: text/html; charset=UTF-8');

echo "<!DOCTYPE html><html><head><title>Database Update</title>";
echo "<style>body{font-family:Arial;padding:20px;background:#f5f5f5;}";
echo ".success{color:green;padding:10px;background:#d4edda;border:1px solid #c3e6cb;margin:10px 0;border-radius:5px;}";
echo ".error{color:red;padding:10px;background:#f8d7da;border:1px solid #f5c6cb;margin:10px 0;border-radius:5px;}";
echo ".info{color:#0c5460;padding:10px;background:#d1ecf1;border:1px solid #bee5eb;margin:10px 0;border-radius:5px;}";
echo ".warning{color:#856404;padding:10px;background:#fff3cd;border:1px solid #ffeaa7;margin:10px 0;border-radius:5px;}";
echo "h1{color:#333;}h2{color:#666;margin-top:20px;}";
echo "table{border-collapse:collapse;width:100%;background:white;margin:10px 0;}";
echo "th{background:#007bff;color:white;padding:10px;text-align:left;}";
echo "td{padding:8px;border:1px solid #ddd;}";
echo "tr:nth-child(even){background:#f9f9f9;}";
echo "</style></head><body>";
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
$warnings = [];

try {
    // ============================================
    // 1. ADD parent_name COLUMN TO children TABLE
    // ============================================
    echo "<h2>Step 1: Adding parent_name to children table</h2>";
    
    $sql = "ALTER TABLE `children` ADD COLUMN `parent_name` VARCHAR(255) NULL AFTER `user_id`";
    if ($conn->query($sql)) {
        $results[] = "✓ Added 'parent_name' column to 'children' table";
        echo "<div class='success'>✓ Successfully added 'parent_name' column to 'children' table</div>";
    } else {
        $errorCode = $conn->errno;
        $errorMsg = $conn->error;
        
        // Error 1060 means duplicate column - that's actually okay!
        if ($errorCode == 1060) {
            $warnings[] = "⚠ 'parent_name' column already exists in 'children' table (this is fine)";
            echo "<div class='warning'>⚠ 'parent_name' column already exists in 'children' table (this is fine)</div>";
        } else {
            $errors[] = "✗ Failed to add 'parent_name' column: " . $errorMsg;
            echo "<div class='error'>✗ Failed to add 'parent_name' column: " . htmlspecialchars($errorMsg) . "</div>";
        }
    }
    
    // ============================================
    // 2. CREATE health_details TABLE IF NOT EXISTS
    // ============================================
    echo "<h2>Step 2: Creating/verifying health_details table</h2>";
    
    $createTable = "CREATE TABLE IF NOT EXISTS `health_details` (
        `id` int(11) NOT NULL AUTO_INCREMENT,
        `child_id` int(11) NOT NULL,
        `blood_group` VARCHAR(10) DEFAULT NULL,
        `allergies` TEXT DEFAULT NULL,
        `medical_conditions` TEXT DEFAULT NULL,
        `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
        `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        PRIMARY KEY (`id`),
        UNIQUE KEY `child_id` (`child_id`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci";
    
    if ($conn->query($createTable)) {
        $results[] = "✓ Created/verified 'health_details' table";
        echo "<div class='success'>✓ Successfully created/verified 'health_details' table</div>";
    } else {
        // If table exists, that's fine
        $errorCode = $conn->errno;
        if ($errorCode == 1050) {
            $warnings[] = "⚠ 'health_details' table already exists (this is fine)";
            echo "<div class='warning'>⚠ 'health_details' table already exists (this is fine)</div>";
        } else {
            $errors[] = "✗ Failed to create 'health_details' table: " . $conn->error;
            echo "<div class='error'>✗ Failed to create 'health_details' table: " . htmlspecialchars($conn->error) . "</div>";
        }
    }
    
    // ============================================
    // 3. ADD birth_weight COLUMN
    // ============================================
    echo "<h2>Step 3: Adding birth_weight to health_details table</h2>";
    
    $sql = "ALTER TABLE `health_details` ADD COLUMN `birth_weight` DECIMAL(5,2) DEFAULT NULL COMMENT 'Birth weight in kg' AFTER `child_id`";
    if ($conn->query($sql)) {
        $results[] = "✓ Added 'birth_weight' column to 'health_details' table";
        echo "<div class='success'>✓ Successfully added 'birth_weight' column</div>";
    } else {
        $errorCode = $conn->errno;
        if ($errorCode == 1060) {
            $warnings[] = "⚠ 'birth_weight' column already exists (this is fine)";
            echo "<div class='warning'>⚠ 'birth_weight' column already exists (this is fine)</div>";
        } else {
            $errors[] = "✗ Failed to add 'birth_weight' column: " . $conn->error;
            echo "<div class='error'>✗ Failed to add 'birth_weight' column: " . htmlspecialchars($conn->error) . "</div>";
        }
    }
    
    // ============================================
    // 4. ADD birth_height COLUMN
    // ============================================
    echo "<h2>Step 4: Adding birth_height to health_details table</h2>";
    
    // Try after birth_weight first, if that fails try after child_id
    $sql = "ALTER TABLE `health_details` ADD COLUMN `birth_height` DECIMAL(5,2) DEFAULT NULL COMMENT 'Birth height in cm' AFTER `birth_weight`";
    if ($conn->query($sql)) {
        $results[] = "✓ Added 'birth_height' column to 'health_details' table";
        echo "<div class='success'>✓ Successfully added 'birth_height' column</div>";
    } else {
        $errorCode = $conn->errno;
        if ($errorCode == 1060) {
            $warnings[] = "⚠ 'birth_height' column already exists (this is fine)";
            echo "<div class='warning'>⚠ 'birth_height' column already exists (this is fine)</div>";
        } elseif ($errorCode == 1064) {
            // Syntax error - try after child_id instead
            $sql2 = "ALTER TABLE `health_details` ADD COLUMN `birth_height` DECIMAL(5,2) DEFAULT NULL COMMENT 'Birth height in cm' AFTER `child_id`";
            if ($conn->query($sql2)) {
                $results[] = "✓ Added 'birth_height' column to 'health_details' table (after child_id)";
                echo "<div class='success'>✓ Successfully added 'birth_height' column (after child_id)</div>";
            } else {
                $errors[] = "✗ Failed to add 'birth_height' column: " . $conn->error;
                echo "<div class='error'>✗ Failed to add 'birth_height' column: " . htmlspecialchars($conn->error) . "</div>";
            }
        } else {
            $errors[] = "✗ Failed to add 'birth_height' column: " . $conn->error;
            echo "<div class='error'>✗ Failed to add 'birth_height' column: " . htmlspecialchars($conn->error) . "</div>";
        }
    }
    
    // ============================================
    // 5. VERIFICATION
    // ============================================
    echo "<h2>Step 5: Verification - Table Structures</h2>";
    
    // Show children table structure
    echo "<h3>Children Table Structure:</h3>";
    echo "<table><tr><th>Field</th><th>Type</th><th>Null</th><th>Key</th><th>Default</th></tr>";
    $descChildren = $conn->query("DESCRIBE `children`");
    if ($descChildren) {
        while ($row = $descChildren->fetch_assoc()) {
            $highlight = ($row['Field'] == 'parent_name') ? "style='background:#d4edda;font-weight:bold;'" : "";
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
    
    // Check if parent_name exists
    $checkParent = $conn->query("SHOW COLUMNS FROM `children` WHERE Field = 'parent_name'");
    if ($checkParent && $checkParent->num_rows > 0) {
        echo "<div class='success'><strong>✓ VERIFIED:</strong> 'parent_name' column exists in 'children' table</div>";
    } else {
        echo "<div class='error'><strong>✗ WARNING:</strong> 'parent_name' column NOT found in 'children' table</div>";
    }
    
    // Show health_details table structure
    echo "<h3>Health Details Table Structure:</h3>";
    $descHealth = $conn->query("DESCRIBE `health_details`");
    if ($descHealth) {
        echo "<table><tr><th>Field</th><th>Type</th><th>Null</th><th>Key</th><th>Default</th></tr>";
        while ($row = $descHealth->fetch_assoc()) {
            $highlight = ($row['Field'] == 'birth_weight' || $row['Field'] == 'birth_height') ? "style='background:#d4edda;font-weight:bold;'" : "";
            echo "<tr $highlight>";
            echo "<td><strong>" . htmlspecialchars($row['Field']) . "</strong></td>";
            echo "<td>" . htmlspecialchars($row['Type']) . "</td>";
            echo "<td>" . htmlspecialchars($row['Null']) . "</td>";
            echo "<td>" . htmlspecialchars($row['Key']) . "</td>";
            echo "<td>" . htmlspecialchars($row['Default'] ?? 'NULL') . "</td>";
            echo "</tr>";
        }
        echo "</table>";
        
        // Check if columns exist
        $checkWeight = $conn->query("SHOW COLUMNS FROM `health_details` WHERE Field = 'birth_weight'");
        $checkHeight = $conn->query("SHOW COLUMNS FROM `health_details` WHERE Field = 'birth_height'");
        
        if ($checkWeight && $checkWeight->num_rows > 0) {
            echo "<div class='success'><strong>✓ VERIFIED:</strong> 'birth_weight' column exists in 'health_details' table</div>";
        } else {
            echo "<div class='error'><strong>✗ WARNING:</strong> 'birth_weight' column NOT found in 'health_details' table</div>";
        }
        
        if ($checkHeight && $checkHeight->num_rows > 0) {
            echo "<div class='success'><strong>✓ VERIFIED:</strong> 'birth_height' column exists in 'health_details' table</div>";
        } else {
            echo "<div class='error'><strong>✗ WARNING:</strong> 'birth_height' column NOT found in 'health_details' table</div>";
        }
    } else {
        echo "<div class='error'>Could not describe 'health_details' table. Error: " . htmlspecialchars($conn->error) . "</div>";
    }
    
    // Final summary
    echo "<h2>Summary</h2>";
    if (count($errors) == 0) {
        echo "<div class='success'><strong>✓ All updates completed successfully!</strong><br>";
        echo "Operations completed: " . count($results) . "<br>";
        if (count($warnings) > 0) {
            echo "Warnings (already existed): " . count($warnings) . "</div>";
        }
    } else {
        echo "<div class='error'><strong>⚠ Some errors occurred:</strong><ul>";
        foreach ($errors as $error) {
            echo "<li>" . htmlspecialchars($error) . "</li>";
        }
        echo "</ul></div>";
    }
    
    if (count($warnings) > 0) {
        echo "<div class='warning'><strong>Warnings (these are okay):</strong><ul>";
        foreach ($warnings as $warning) {
            echo "<li>" . htmlspecialchars($warning) . "</li>";
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




<?php
/**
 * VaxForSure - Add parent_name and child_name to health_details table
 * Access: http://localhost/vaxforsure/ADD_PARENT_CHILD_TO_HEALTH.php
 */

// Database configuration
$host = 'localhost';
$user = 'root';
$pass = '';
$db = 'vaxforsure';
$port = 3307;

// Set HTML header
header('Content-Type: text/html; charset=UTF-8');

echo "<!DOCTYPE html><html><head><title>Add Parent/Child Names to Health Details</title>";
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
echo "<h1>Add Parent/Child Names to Health Details Table</h1>";

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
    // 1. ADD parent_name COLUMN TO health_details
    // ============================================
    echo "<h2>Step 1: Adding parent_name to health_details table</h2>";
    
    $sql = "ALTER TABLE `health_details` ADD COLUMN `parent_name` VARCHAR(255) NULL AFTER `child_id`";
    if ($conn->query($sql)) {
        $results[] = "✓ Added 'parent_name' column to 'health_details' table";
        echo "<div class='success'>✓ Successfully added 'parent_name' column</div>";
    } else {
        $errorCode = $conn->errno;
        if ($errorCode == 1060) {
            $warnings[] = "⚠ 'parent_name' column already exists (this is fine)";
            echo "<div class='warning'>⚠ 'parent_name' column already exists (this is fine)</div>";
        } else {
            $errors[] = "✗ Failed to add 'parent_name' column: " . $conn->error;
            echo "<div class='error'>✗ Failed to add 'parent_name' column: " . htmlspecialchars($conn->error) . "</div>";
        }
    }
    
    // ============================================
    // 2. ADD child_name COLUMN TO health_details
    // ============================================
    echo "<h2>Step 2: Adding child_name to health_details table</h2>";
    
    $sql = "ALTER TABLE `health_details` ADD COLUMN `child_name` VARCHAR(255) NULL AFTER `parent_name`";
    if ($conn->query($sql)) {
        $results[] = "✓ Added 'child_name' column to 'health_details' table";
        echo "<div class='success'>✓ Successfully added 'child_name' column</div>";
    } else {
        $errorCode = $conn->errno;
        if ($errorCode == 1060) {
            $warnings[] = "⚠ 'child_name' column already exists (this is fine)";
            echo "<div class='warning'>⚠ 'child_name' column already exists (this is fine)</div>";
        } else {
            $errors[] = "✗ Failed to add 'child_name' column: " . $conn->error;
            echo "<div class='error'>✗ Failed to add 'child_name' column: " . htmlspecialchars($conn->error) . "</div>";
        }
    }
    
    // ============================================
    // 3. VERIFICATION
    // ============================================
    echo "<h2>Step 3: Verification - Health Details Table Structure</h2>";
    
    $descHealth = $conn->query("DESCRIBE `health_details`");
    if ($descHealth) {
        echo "<table><tr><th>Field</th><th>Type</th><th>Null</th><th>Key</th><th>Default</th></tr>";
        while ($row = $descHealth->fetch_assoc()) {
            $highlight = ($row['Field'] == 'parent_name' || $row['Field'] == 'child_name') ? "style='background:#d4edda;font-weight:bold;'" : "";
            echo "<tr $highlight>";
            echo "<td><strong>" . htmlspecialchars($row['Field']) . "</strong></td>";
            echo "<td>" . htmlspecialchars($row['Type']) . "</td>";
            echo "<td>" . htmlspecialchars($row['Null']) . "</td>";
            echo "<td>" . htmlspecialchars($row['Key']) . "</td>";
            echo "<td>" . htmlspecialchars($row['Default'] ?? 'NULL') . "</td>";
            echo "</tr>";
        }
        echo "</table>";
        
        // Verify columns exist
        $checkParent = $conn->query("SHOW COLUMNS FROM `health_details` WHERE Field = 'parent_name'");
        $checkChild = $conn->query("SHOW COLUMNS FROM `health_details` WHERE Field = 'child_name'");
        
        if ($checkParent && $checkParent->num_rows > 0) {
            echo "<div class='success'><strong>✓ VERIFIED:</strong> 'parent_name' column exists in 'health_details' table</div>";
        } else {
            echo "<div class='error'><strong>✗ WARNING:</strong> 'parent_name' column NOT found</div>";
        }
        
        if ($checkChild && $checkChild->num_rows > 0) {
            echo "<div class='success'><strong>✓ VERIFIED:</strong> 'child_name' column exists in 'health_details' table</div>";
        } else {
            echo "<div class='error'><strong>✗ WARNING:</strong> 'child_name' column NOT found</div>";
        }
    }
    
    // Final summary
    echo "<h2>Summary</h2>";
    if (count($errors) == 0) {
        echo "<div class='success'><strong>✓ All updates completed successfully!</strong><br>";
        echo "Operations completed: " . count($results) . "</div>";
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




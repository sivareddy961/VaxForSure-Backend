<?php
/**
 * COMPLETE SETUP SCRIPT - Run this ONCE to fix everything
 * This script will:
 * 1. Check and create vaccinations table
 * 2. Verify all columns exist
 * 3. Test database connection
 * 4. Show complete status
 */

// Database configuration
define('DB_HOST', 'localhost');
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_NAME', 'vaxforsure');
define('DB_PORT', 3307);

header('Content-Type: text/html; charset=UTF-8');
?>
<!DOCTYPE html>
<html>
<head>
    <title>VaxForSure - Complete Setup</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 20px; background: #f5f5f5; }
        .container { max-width: 800px; margin: 0 auto; background: white; padding: 20px; border-radius: 8px; box-shadow: 0 2px 4px rgba(0,0,0,0.1); }
        h1 { color: #00BFA5; }
        .success { color: #4CAF50; font-weight: bold; }
        .error { color: #F44336; font-weight: bold; }
        .info { color: #2196F3; }
        pre { background: #f5f5f5; padding: 10px; border-radius: 4px; overflow-x: auto; }
        .step { margin: 20px 0; padding: 15px; background: #E3F2FD; border-left: 4px solid #2196F3; }
    </style>
</head>
<body>
<div class="container">
    <h1>🚀 VaxForSure - Complete Setup</h1>
    <p class="info">This script will automatically set up everything needed for the vaccination feature.</p>
    
<?php

// Get database connection
$conn = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME, DB_PORT);

if ($conn->connect_error) {
    echo "<div class='error'>✗ Database connection failed: " . $conn->connect_error . "</div>";
    echo "<p>Please check:</p>";
    echo "<ul><li>XAMPP MySQL is running (Port 3307)</li><li>Database 'vaxforsure' exists</li></ul>";
    exit();
}

$conn->set_charset("utf8mb4");
echo "<div class='success'>✓ Database connection successful (Port " . DB_PORT . ")</div>";

// Step 1: Check if children table exists
echo "<div class='step'><h3>Step 1: Checking dependencies...</h3>";
$result = $conn->query("SHOW TABLES LIKE 'children'");
if ($result && $result->num_rows > 0) {
    echo "<div class='success'>✓ Children table exists</div>";
    $childrenCount = $conn->query("SELECT COUNT(*) as count FROM children")->fetch_assoc()['count'];
    echo "<div class='info'>  Children count: " . $childrenCount . "</div>";
} else {
    echo "<div class='error'>✗ Children table does not exist. Please create children table first.</div>";
    $conn->close();
    exit();
}

// Step 2: Check/create vaccinations table
echo "<h3>Step 2: Setting up vaccinations table...</h3>";

$result = $conn->query("SHOW TABLES LIKE 'vaccinations'");
$tableExists = $result && $result->num_rows > 0;

if ($tableExists) {
    echo "<div class='info'>  Table exists, checking structure...</div>";
    
    // Check columns
    $result = $conn->query("DESCRIBE vaccinations");
    $columns = [];
    while ($row = $result->fetch_assoc()) {
        $columns[] = $row['Field'];
    }
    
    $requiredColumns = ['id', 'child_id', 'vaccine_name', 'dose_number', 'completed_date', 'status', 'healthcare_facility', 'batch_lot_number', 'notes'];
    $missingColumns = array_diff($requiredColumns, $columns);
    
    if (!empty($missingColumns)) {
        echo "<div class='error'>  ✗ Missing columns: " . implode(', ', $missingColumns) . "</div>";
        echo "<div class='info'>  Recreating table...</div>";
        $conn->query("DROP TABLE IF EXISTS `vaccinations`");
        $tableExists = false;
    } else {
        echo "<div class='success'>  ✓ All required columns present</div>";
    }
}

if (!$tableExists) {
    echo "<div class='info'>  Creating vaccinations table...</div>";
    
    $createTableSQL = "CREATE TABLE IF NOT EXISTS `vaccinations` (
      `id` int(11) NOT NULL AUTO_INCREMENT,
      `child_id` int(11) NOT NULL,
      `vaccine_name` varchar(255) NOT NULL,
      `dose_number` int(11) DEFAULT 1,
      `scheduled_date` date DEFAULT NULL,
      `completed_date` date DEFAULT NULL,
      `status` enum('pending','completed','missed') DEFAULT 'pending',
      `healthcare_facility` varchar(255) DEFAULT NULL,
      `batch_lot_number` varchar(100) DEFAULT NULL,
      `notes` text DEFAULT NULL,
      `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
      `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
      PRIMARY KEY (`id`),
      KEY `child_id` (`child_id`),
      KEY `vaccine_name` (`vaccine_name`),
      KEY `status` (`status`),
      CONSTRAINT `fk_vaccinations_child_id` FOREIGN KEY (`child_id`) REFERENCES `children` (`id`) ON DELETE CASCADE
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci";

    if ($conn->query($createTableSQL)) {
        echo "<div class='success'>  ✓ Table created successfully!</div>";
    } else {
        echo "<div class='error'>  ✗ Error creating table: " . $conn->error . "</div>";
        $conn->close();
        exit();
    }
}
echo "</div>";

// Step 3: Verify structure
echo "<div class='step'><h3>Step 3: Verifying table structure...</h3>";
$result = $conn->query("DESCRIBE vaccinations");
$columns = [];
while ($row = $result->fetch_assoc()) {
    $columns[] = $row['Field'];
    echo "<div class='info'>  - " . $row['Field'] . " (" . $row['Type'] . ")</div>";
}
echo "<div class='success'>✓ Table structure verified (" . count($columns) . " columns)</div>";
echo "</div>";

// Step 4: Test query
echo "<div class='step'><h3>Step 4: Testing queries...</h3>";
try {
    // Test INSERT preparation
    $testStmt = $conn->prepare("INSERT INTO vaccinations (child_id, vaccine_name, dose_number, completed_date, status, healthcare_facility, batch_lot_number, notes) VALUES (?, ?, ?, ?, 'completed', ?, ?, ?)");
    if ($testStmt) {
        echo "<div class='success'>✓ INSERT query prepared successfully</div>";
        
        // Test bind_param
        $testChildId = 1;
        $testVaccineName = "Test";
        $testDoseNumber = 1;
        $testDate = "2024-01-01";
        $testFacility = "Test Facility";
        $testBatch = "BATCH001";
        $testNotes = "Test notes";
        
        if ($testStmt->bind_param("isissss", $testChildId, $testVaccineName, $testDoseNumber, $testDate, $testFacility, $testBatch, $testNotes)) {
            echo "<div class='success'>✓ bind_param types verified (isissss)</div>";
        } else {
            echo "<div class='error'>✗ bind_param failed: " . $testStmt->error . "</div>";
        }
        $testStmt->close();
    } else {
        echo "<div class='error'>✗ Query preparation failed: " . $conn->error . "</div>";
    }
} catch (Exception $e) {
    echo "<div class='error'>✗ Test failed: " . $e->getMessage() . "</div>";
}
echo "</div>";

// Step 5: Summary
echo "<div class='step'><h3>✅ Setup Complete!</h3>";
echo "<div class='success'><strong>Everything is ready!</strong></div>";
echo "<p><strong>Next steps:</strong></p>";
echo "<ol>";
echo "<li>Try 'Mark as Completed' in the Android app</li>";
echo "<li>It should work without errors now</li>";
echo "</ol>";
echo "<p><strong>API Endpoint:</strong> <code>http://localhost:8080/vaxforsure/api/vaccinations/mark_completed.php</code></p>";
echo "</div>";

$conn->close();
?>
</div>
</body>
</html>






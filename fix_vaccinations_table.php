<?php
/**
 * Fix Vaccinations Table
 * This script will check and create/update the vaccinations table with correct structure
 */

// Database configuration
define('DB_HOST', 'localhost');
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_NAME', 'vaxforsure');
define('DB_PORT', 3307);

header('Content-Type: text/html; charset=UTF-8');

// Get database connection
$conn = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME, DB_PORT);

if ($conn->connect_error) {
    die("Database connection failed: " . $conn->connect_error);
}

$conn->set_charset("utf8mb4");

echo "<h2>Fixing Vaccinations Table</h2>";
echo "<pre>";

// Check if table exists
$result = $conn->query("SHOW TABLES LIKE 'vaccinations'");
$tableExists = $result && $result->num_rows > 0;

if ($tableExists) {
    echo "✓ Table 'vaccinations' exists\n";
    
    // Check current structure
    $result = $conn->query("DESCRIBE vaccinations");
    $columns = [];
    while ($row = $result->fetch_assoc()) {
        $columns[] = $row['Field'];
    }
    
    echo "\nCurrent columns: " . implode(', ', $columns) . "\n\n";
    
    // Drop table to recreate with correct structure
    echo "Dropping existing table to recreate with correct structure...\n";
    $conn->query("DROP TABLE IF EXISTS `vaccinations`");
    echo "✓ Table dropped\n\n";
} else {
    echo "✗ Table 'vaccinations' does not exist\n\n";
}

// Create table with correct structure
echo "Creating vaccinations table with correct structure...\n";

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
    echo "✓ Table created successfully!\n\n";
} else {
    echo "✗ Error creating table: " . $conn->error . "\n";
    $conn->close();
    exit(1);
}

// Verify structure
echo "Verifying table structure...\n";
$result = $conn->query("DESCRIBE vaccinations");
$columns = [];
while ($row = $result->fetch_assoc()) {
    $columns[] = $row['Field'];
    echo "  - " . $row['Field'] . " (" . $row['Type'] . ")\n";
}

echo "\n✓ All columns present: " . implode(', ', $columns) . "\n";

// Check if children table exists (required for foreign key)
$result = $conn->query("SHOW TABLES LIKE 'children'");
if ($result && $result->num_rows > 0) {
    echo "\n✓ Children table exists (foreign key will work)\n";
} else {
    echo "\n⚠ Warning: Children table does not exist (foreign key may fail)\n";
}

$conn->close();

echo "\n✅ Table fix completed successfully!\n";
echo "</pre>";
echo "<p><a href='test_vaccination_table.php'>Test Table</a> | <a href='test_mark_completed.php'>Test API</a></p>";

?>






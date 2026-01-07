<?php
/**
 * Test Vaccination Table Structure
 * This file helps verify the vaccinations table exists and has correct structure
 */

// Database configuration
define('DB_HOST', 'localhost');
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_NAME', 'vaxforsure');
define('DB_PORT', 3307);

header('Content-Type: application/json; charset=UTF-8');

// Get database connection
$conn = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME, DB_PORT);

if ($conn->connect_error) {
    echo json_encode([
        'success' => false,
        'error' => 'Database connection failed: ' . $conn->connect_error
    ], JSON_PRETTY_PRINT);
    exit();
}

$conn->set_charset("utf8mb4");

// Check if vaccinations table exists
$tableExists = false;
$result = $conn->query("SHOW TABLES LIKE 'vaccinations'");
if ($result && $result->num_rows > 0) {
    $tableExists = true;
}

if (!$tableExists) {
    echo json_encode([
        'success' => false,
        'error' => 'vaccinations table does not exist',
        'instructions' => 'Please run the SQL from database_complete.sql to create the table'
    ], JSON_PRETTY_PRINT);
    $conn->close();
    exit();
}

// Get table structure
$structure = [];
$result = $conn->query("DESCRIBE vaccinations");
if ($result) {
    while ($row = $result->fetch_assoc()) {
        $structure[] = $row;
    }
}

// Check if children table exists and get sample data
$childrenCount = 0;
$sampleChildren = [];
$result = $conn->query("SELECT id, name, user_id FROM children LIMIT 5");
if ($result) {
    $childrenCount = $result->num_rows;
    while ($row = $result->fetch_assoc()) {
        $sampleChildren[] = $row;
    }
}

// Get vaccination count
$vaccinationCount = 0;
$result = $conn->query("SELECT COUNT(*) as count FROM vaccinations");
if ($result) {
    $row = $result->fetch_assoc();
    $vaccinationCount = $row['count'];
}

echo json_encode([
    'success' => true,
    'database' => DB_NAME,
    'port' => DB_PORT,
    'table_exists' => true,
    'table_structure' => $structure,
    'children_count' => $childrenCount,
    'sample_children' => $sampleChildren,
    'vaccinations_count' => $vaccinationCount
], JSON_PRETTY_PRINT);

$conn->close();

?>


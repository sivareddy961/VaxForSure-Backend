<?php
/**
 * Test Mark Vaccine Completed API
 * This file helps test the mark_completed.php endpoint
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
$result = $conn->query("SHOW TABLES LIKE 'vaccinations'");
if (!$result || $result->num_rows === 0) {
    echo json_encode([
        'success' => false,
        'error' => 'vaccinations table does not exist',
        'solution' => 'Run create_vaccinations_table.sql in phpMyAdmin'
    ], JSON_PRETTY_PRINT);
    $conn->close();
    exit();
}

// Get a sample child to test with
$result = $conn->query("SELECT id, name FROM children LIMIT 1");
if (!$result || $result->num_rows === 0) {
    echo json_encode([
        'success' => false,
        'error' => 'No children found in database',
        'solution' => 'Add a child first using the Add Child screen'
    ], JSON_PRETTY_PRINT);
    $conn->close();
    exit();
}

$child = $result->fetch_assoc();
$testChildId = $child['id'];
$testChildName = $child['name'];

// Test data
$testData = [
    'childId' => $testChildId,
    'vaccineName' => 'BCG',
    'dateAdministered' => '10-05-2025',
    'healthcareFacility' => 'chennai',
    'batchLotNumber' => '123516',
    'notes' => 'na8'
];

echo json_encode([
    'success' => true,
    'message' => 'Database connection successful',
    'table_exists' => true,
    'sample_child' => [
        'id' => $testChildId,
        'name' => $testChildName
    ],
    'test_data' => $testData,
    'instructions' => [
        '1. Make sure vaccinations table exists',
        '2. Make sure child with ID ' . $testChildId . ' exists',
        '3. Test the API endpoint with the test_data above',
        '4. Check API endpoint: http://localhost:8080/vaxforsure/api/vaccinations/mark_completed.php'
    ]
], JSON_PRETTY_PRINT);

$conn->close();

?>


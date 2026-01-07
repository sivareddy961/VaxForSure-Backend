<?php
/**
 * Delete Vaccination API Endpoint
 * Deletes a vaccination record
 * 
 * POST /api/vaccinations/delete_vaccination.php
 * 
 * Request Body:
 * {
 *   "vaccinationId": 1
 * }
 */

require_once '../../config.php';

// Start output buffering
if (!ob_get_level()) {
    ob_start();
}

// Only allow POST requests
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    sendResponse(false, 'Only POST method is allowed', null, 405);
}

// Get JSON input
$input = getJsonInput();

if (!$input) {
    sendResponse(false, 'Invalid JSON input', null, 400);
}

// Validate required fields
$vaccinationId = isset($input['vaccinationId']) ? (int)$input['vaccinationId'] : 0;

if ($vaccinationId <= 0) {
    sendResponse(false, 'Invalid vaccination ID', null, 400);
}

// Get database connection
$conn = getDBConnection();

if (!$conn) {
    sendResponse(false, 'Database connection failed', null, 500);
}

try {
    // Check if vaccination exists
    $checkStmt = $conn->prepare("SELECT id FROM vaccinations WHERE id = ?");
    if (!$checkStmt) {
        sendResponse(false, 'Database query preparation failed: ' . $conn->error, null, 500);
    }
    
    $checkStmt->bind_param("i", $vaccinationId);
    
    if (!$checkStmt->execute()) {
        $errorMsg = $checkStmt->error ?: $conn->error;
        $checkStmt->close();
        sendResponse(false, 'Database query execution failed: ' . $errorMsg, null, 500);
    }
    
    $result = $checkStmt->get_result();
    
    if ($result->num_rows === 0) {
        $checkStmt->close();
        sendResponse(false, 'Vaccination record not found', null, 404);
    }
    $checkStmt->close();
    
    // Delete the vaccination record
    $deleteStmt = $conn->prepare("DELETE FROM vaccinations WHERE id = ?");
    if (!$deleteStmt) {
        sendResponse(false, 'Failed to prepare delete query: ' . $conn->error, null, 500);
    }
    
    $deleteStmt->bind_param("i", $vaccinationId);
    
    if (!$deleteStmt->execute()) {
        $errorMsg = $deleteStmt->error ?: $conn->error;
        $deleteStmt->close();
        sendResponse(false, 'Failed to delete vaccination record: ' . $errorMsg, null, 500);
    }
    $deleteStmt->close();
    
    sendResponse(true, 'Vaccination record deleted successfully', null, 200);
    
} catch (Exception $e) {
    error_log("Delete vaccination error: " . $e->getMessage());
    sendResponse(false, 'An error occurred: ' . $e->getMessage(), null, 500);
} catch (Error $e) {
    error_log("Delete vaccination fatal error: " . $e->getMessage());
    sendResponse(false, 'A fatal error occurred: ' . $e->getMessage(), null, 500);
} finally {
    if (isset($conn)) {
        $conn->close();
    }
}

?>






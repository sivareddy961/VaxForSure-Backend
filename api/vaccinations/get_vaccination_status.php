<?php
/**
 * Get Vaccination Status API Endpoint
 * Checks if a specific vaccine is completed for a child
 * 
 * GET /api/vaccinations/get_vaccination_status.php?childId={childId}&vaccineName={vaccineName}
 */

require_once '../../config.php';

// Only allow GET requests
if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
    sendResponse(false, 'Only GET method is allowed', null, 405);
}

// Get query parameters
$childId = isset($_GET['childId']) ? (int)$_GET['childId'] : 0;
$vaccineName = isset($_GET['vaccineName']) ? sanitizeInput($_GET['vaccineName']) : '';

// Validate input
if ($childId <= 0) {
    sendResponse(false, 'Child ID is required', null, 400);
}

if (empty($vaccineName)) {
    sendResponse(false, 'Vaccine name is required', null, 400);
}

// Get database connection
$conn = getDBConnection();

if (!$conn) {
    sendResponse(false, 'Database connection failed', null, 500);
}

try {
    // Get vaccination status
    $stmt = $conn->prepare("SELECT * FROM vaccinations WHERE child_id = ? AND vaccine_name = ?");
    $stmt->bind_param("is", $childId, $vaccineName);
    $stmt->execute();
    $result = $stmt->get_result();
    $vaccination = $result->fetch_assoc();
    $stmt->close();
    
    if ($vaccination) {
        $vaccinationData = [
            'id' => (int)$vaccination['id'],
            'child_id' => (int)$vaccination['child_id'],
            'vaccine_name' => $vaccination['vaccine_name'],
            'dose_number' => (int)$vaccination['dose_number'],
            'scheduled_date' => $vaccination['scheduled_date'],
            'completed_date' => $vaccination['completed_date'],
            'status' => $vaccination['status'],
            'healthcare_facility' => $vaccination['healthcare_facility'],
            'batch_lot_number' => $vaccination['batch_lot_number'],
            'notes' => $vaccination['notes']
        ];
        
        sendResponse(true, 'Vaccination status retrieved successfully', ['vaccination' => $vaccinationData], 200);
    } else {
        sendResponse(true, 'Vaccination not found', ['vaccination' => null, 'status' => 'pending'], 200);
    }
    
} catch (Exception $e) {
    error_log("Get vaccination status error: " . $e->getMessage());
    sendResponse(false, 'An error occurred: ' . $e->getMessage(), null, 500);
} finally {
    $conn->close();
}

?>


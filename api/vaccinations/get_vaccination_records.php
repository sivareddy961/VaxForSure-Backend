<?php
/**
 * Get Vaccination Records API Endpoint
 * Retrieves completed vaccination records grouped by child
 * 
 * GET /api/vaccinations/get_vaccination_records.php?userId={userId}
 */

require_once '../../config.php';

// Only allow GET requests
if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
    sendResponse(false, 'Only GET method is allowed', null, 405);
}

// Get query parameters
$userId = isset($_GET['userId']) ? (int)$_GET['userId'] : 0;

// Validate input
if ($userId <= 0) {
    sendResponse(false, 'User ID is required', null, 400);
}

// Get database connection
$conn = getDBConnection();

if (!$conn) {
    sendResponse(false, 'Database connection failed', null, 500);
}

try {
    // Get all children for the user
    $childrenStmt = $conn->prepare("SELECT id, name, date_of_birth, gender FROM children WHERE user_id = ?");
    $childrenStmt->bind_param("i", $userId);
    $childrenStmt->execute();
    $childrenResult = $childrenStmt->get_result();
    
    $recordsByChild = [];
    
    while ($child = $childrenResult->fetch_assoc()) {
        $childId = (int)$child['id'];
        
        // Get completed vaccinations for this child
        $vaccinationsStmt = $conn->prepare("SELECT * FROM vaccinations WHERE child_id = ? AND status = 'completed' ORDER BY completed_date DESC");
        $vaccinationsStmt->bind_param("i", $childId);
        $vaccinationsStmt->execute();
        $vaccinationsResult = $vaccinationsStmt->get_result();
        
        $vaccinations = [];
        while ($vaccination = $vaccinationsResult->fetch_assoc()) {
            $vaccinations[] = [
                'id' => (int)$vaccination['id'],
                'vaccine_name' => $vaccination['vaccine_name'],
                'completed_date' => $vaccination['completed_date'],
                'healthcare_facility' => $vaccination['healthcare_facility'],
                'batch_lot_number' => $vaccination['batch_lot_number'],
                'notes' => $vaccination['notes'],
                'dose_number' => (int)$vaccination['dose_number']
            ];
        }
        $vaccinationsStmt->close();
        
        if (!empty($vaccinations)) {
            $recordsByChild[] = [
                'child' => [
                    'id' => $childId,
                    'name' => $child['name'],
                    'date_of_birth' => $child['date_of_birth'],
                    'gender' => $child['gender']
                ],
                'vaccinations' => $vaccinations
            ];
        }
    }
    $childrenStmt->close();
    
    sendResponse(true, 'Vaccination records retrieved successfully', ['recordsByChild' => $recordsByChild], 200);
    
} catch (Exception $e) {
    error_log("Get vaccination records error: " . $e->getMessage());
    sendResponse(false, 'An error occurred: ' . $e->getMessage(), null, 500);
} finally {
    $conn->close();
}

?>


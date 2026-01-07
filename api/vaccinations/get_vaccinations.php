<?php
/**
 * Get Vaccinations API Endpoint
 * Retrieves vaccination records for a child or all children of a user
 * 
 * GET /api/vaccinations/get_vaccinations.php?childId={childId}
 * GET /api/vaccinations/get_vaccinations.php?userId={userId}
 * GET /api/vaccinations/get_vaccinations.php?childId={childId}&status={completed|pending|all}
 */

require_once '../../config.php';

// Only allow GET requests
if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
    sendResponse(false, 'Only GET method is allowed', null, 405);
}

// Get query parameters
$childId = isset($_GET['childId']) ? (int)$_GET['childId'] : 0;
$userId = isset($_GET['userId']) ? (int)$_GET['userId'] : 0;
$status = isset($_GET['status']) ? sanitizeInput($_GET['status']) : 'all';

// Validate input
if ($childId <= 0 && $userId <= 0) {
    sendResponse(false, 'Either childId or userId is required', null, 400);
}

// Validate status
$validStatuses = ['all', 'pending', 'completed', 'missed'];
if (!in_array($status, $validStatuses)) {
    $status = 'all';
}

// Get database connection
$conn = getDBConnection();

if (!$conn) {
    sendResponse(false, 'Database connection failed', null, 500);
}

try {
    $vaccinations = [];
    
    if ($childId > 0) {
        // Get vaccinations for specific child
        if ($status === 'all') {
            $stmt = $conn->prepare("SELECT * FROM vaccinations WHERE child_id = ? ORDER BY completed_date DESC, created_at DESC");
            $stmt->bind_param("i", $childId);
        } else {
            $stmt = $conn->prepare("SELECT * FROM vaccinations WHERE child_id = ? AND status = ? ORDER BY completed_date DESC, created_at DESC");
            $stmt->bind_param("is", $childId, $status);
        }
    } else {
        // Get vaccinations for all children of a user
        if ($status === 'all') {
            $stmt = $conn->prepare("SELECT v.* FROM vaccinations v INNER JOIN children c ON v.child_id = c.id WHERE c.user_id = ? ORDER BY v.completed_date DESC, v.created_at DESC");
            $stmt->bind_param("i", $userId);
        } else {
            $stmt = $conn->prepare("SELECT v.* FROM vaccinations v INNER JOIN children c ON v.child_id = c.id WHERE c.user_id = ? AND v.status = ? ORDER BY v.completed_date DESC, v.created_at DESC");
            $stmt->bind_param("is", $userId, $status);
        }
    }
    
    $stmt->execute();
    $result = $stmt->get_result();
    
    while ($row = $result->fetch_assoc()) {
        $vaccinations[] = [
            'id' => (int)$row['id'],
            'child_id' => (int)$row['child_id'],
            'vaccine_name' => $row['vaccine_name'],
            'dose_number' => (int)$row['dose_number'],
            'scheduled_date' => $row['scheduled_date'],
            'completed_date' => $row['completed_date'],
            'status' => $row['status'],
            'healthcare_facility' => $row['healthcare_facility'],
            'batch_lot_number' => $row['batch_lot_number'],
            'notes' => $row['notes'],
            'created_at' => $row['created_at'],
            'updated_at' => $row['updated_at']
        ];
    }
    $stmt->close();
    
    sendResponse(true, 'Vaccinations retrieved successfully', ['vaccinations' => $vaccinations], 200);
    
} catch (Exception $e) {
    error_log("Get vaccinations error: " . $e->getMessage());
    sendResponse(false, 'An error occurred: ' . $e->getMessage(), null, 500);
} finally {
    $conn->close();
}

?>


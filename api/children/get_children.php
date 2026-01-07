<?php
/**
 * Get Children by User ID API Endpoint
 * Retrieves all children for a specific user
 * 
 * GET /api/children/get_children.php?userId={userId}
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
    // Get all children for this user
    $stmt = $conn->prepare("SELECT c.*, hd.birth_weight, hd.birth_height, hd.blood_group 
                            FROM children c 
                            LEFT JOIN health_details hd ON c.id = hd.child_id 
                            WHERE c.user_id = ? 
                            ORDER BY c.created_at DESC");
    
    if (!$stmt) {
        sendResponse(false, 'Database query preparation failed: ' . $conn->error, null, 500);
    }
    
    $stmt->bind_param("i", $userId);
    
    if (!$stmt->execute()) {
        $errorMsg = $stmt->error ?: $conn->error;
        $stmt->close();
        sendResponse(false, 'Database query execution failed: ' . $errorMsg, null, 500);
    }
    
    $result = $stmt->get_result();
    $children = [];
    
    while ($row = $result->fetch_assoc()) {
        $children[] = [
            'id' => (int)$row['id'],
            'user_id' => (int)$row['user_id'],
            'name' => $row['name'],
            'date_of_birth' => $row['date_of_birth'],
            'gender' => $row['gender'],
            'birth_weight' => $row['birth_weight'] !== null ? (float)$row['birth_weight'] : null,
            'birth_height' => $row['birth_height'] !== null ? (float)$row['birth_height'] : null,
            'blood_group' => $row['blood_group'],
            'created_at' => $row['created_at'],
            'updated_at' => $row['updated_at']
        ];
    }
    $stmt->close();
    
    sendResponse(true, 'Children retrieved successfully', ['children' => $children], 200);
    
} catch (Exception $e) {
    error_log("Get children error: " . $e->getMessage());
    sendResponse(false, 'An error occurred: ' . $e->getMessage(), null, 500);
} finally {
    $conn->close();
}

?>






<?php
/**
 * Update Health Details API Endpoint
 * Updates health details for an existing child
 * 
 * POST /api/children/update_health_details.php
 * 
 * Request Body:
 * {
 *   "childId": 1,
 *   "birthWeight": 3.5 (optional),
 *   "birthHeight": 50.0 (optional),
 *   "bloodGroup": "A+" (optional),
 *   "allergies": "Peanuts" (optional),
 *   "medicalConditions": "Asthma" (optional)
 * }
 */

require_once '../../config.php';

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
$childId = isset($input['childId']) ? (int)$input['childId'] : 0;

if ($childId <= 0) {
    sendResponse(false, 'Invalid child ID', null, 400);
}

// Optional health details
$birthWeight = isset($input['birthWeight']) && $input['birthWeight'] !== null ? (float)$input['birthWeight'] : null;
$birthHeight = isset($input['birthHeight']) && $input['birthHeight'] !== null ? (float)$input['birthHeight'] : null;
$bloodGroup = isset($input['bloodGroup']) && !empty($input['bloodGroup']) ? sanitizeInput($input['bloodGroup']) : null;
$allergies = isset($input['allergies']) && !empty($input['allergies']) ? sanitizeInput($input['allergies']) : null;
$medicalConditions = isset($input['medicalConditions']) && !empty($input['medicalConditions']) ? sanitizeInput($input['medicalConditions']) : null;

// Get database connection
$conn = getDBConnection();

if (!$conn) {
    sendResponse(false, 'Database connection failed', null, 500);
}

try {
    // Check if child exists and get parent_name and child name
    $childStmt = $conn->prepare("SELECT id, parent_name, name FROM children WHERE id = ?");
    $childStmt->bind_param("i", $childId);
    $childStmt->execute();
    $childResult = $childStmt->get_result();
    
    if ($childResult->num_rows === 0) {
        $childStmt->close();
        sendResponse(false, 'Child not found', null, 404);
    }
    
    $childRow = $childResult->fetch_assoc();
    $parentName = $childRow['parent_name'] ?? null;
    $childName = $childRow['name'] ?? null;
    $childStmt->close();
    
    // Check if health details already exist
    $checkStmt = $conn->prepare("SELECT id FROM health_details WHERE child_id = ?");
    $checkStmt->bind_param("i", $childId);
    $checkStmt->execute();
    $checkResult = $checkStmt->get_result();
    $checkStmt->close();
    
    if ($checkResult->num_rows > 0) {
        // Update existing health details with birth_weight, birth_height, parent_name, and child_name
        $updateStmt = $conn->prepare("UPDATE health_details SET parent_name = ?, child_name = ?, birth_weight = ?, birth_height = ?, blood_group = ?, allergies = ?, medical_conditions = ?, updated_at = CURRENT_TIMESTAMP WHERE child_id = ?");
        $updateStmt->bind_param("ssddsssi", $parentName, $childName, $birthWeight, $birthHeight, $bloodGroup, $allergies, $medicalConditions, $childId);
        
        if (!$updateStmt->execute()) {
            $updateStmt->close();
            sendResponse(false, 'Failed to update health details: ' . $conn->error, null, 500);
        }
        $updateStmt->close();
    } else {
        // Insert new health details with birth_weight, birth_height, parent_name, and child_name
        $insertStmt = $conn->prepare("INSERT INTO health_details (child_id, parent_name, child_name, birth_weight, birth_height, blood_group, allergies, medical_conditions) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
        $insertStmt->bind_param("issddsss", $childId, $parentName, $childName, $birthWeight, $birthHeight, $bloodGroup, $allergies, $medicalConditions);
        
        if (!$insertStmt->execute()) {
            $insertStmt->close();
            sendResponse(false, 'Failed to add health details: ' . $conn->error, null, 500);
        }
        $insertStmt->close();
    }
    
    // Return updated health details
    $healthData = [
        'child_id' => (int)$childId,
        'parent_name' => $parentName,
        'child_name' => $childName,
        'birth_weight' => $birthWeight,
        'birth_height' => $birthHeight,
        'blood_group' => $bloodGroup,
        'allergies' => $allergies,
        'medical_conditions' => $medicalConditions
    ];
    
    sendResponse(true, 'Health details updated successfully', ['healthDetails' => $healthData], 200);
    
} catch (Exception $e) {
    error_log("Update health details error: " . $e->getMessage());
    sendResponse(false, 'An error occurred: ' . $e->getMessage(), null, 500);
} finally {
    $conn->close();
}

?>


<?php
/**
 * Mark Vaccine as Completed API Endpoint
 * Stores completed vaccination records for a child
 * 
 * POST /api/vaccinations/mark_completed.php
 * 
 * Request Body:
 * {
 *   "childId": 1,
 *   "vaccineName": "BCG",
 *   "dateAdministered": "2024-01-15",
 *   "healthcareFacility": "City Health Center",
 *   "batchLotNumber": "BATCH123",
 *   "notes": "No side effects observed"
 * }
 */

// Start output buffering to catch any unexpected output
if (!ob_get_level()) {
    ob_start();
}

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
$vaccineName = isset($input['vaccineName']) ? sanitizeInput($input['vaccineName']) : '';
$dateAdministered = isset($input['dateAdministered']) ? sanitizeInput($input['dateAdministered']) : '';
$healthcareFacility = isset($input['healthcareFacility']) ? sanitizeInput($input['healthcareFacility']) : '';
$batchLotNumber = isset($input['batchLotNumber']) ? sanitizeInput($input['batchLotNumber']) : null;
$notes = isset($input['notes']) ? sanitizeInput($input['notes']) : null;

// Validate required fields
if ($childId <= 0) {
    sendResponse(false, 'Invalid child ID', null, 400);
}

if (empty($vaccineName)) {
    sendResponse(false, 'Vaccine name is required', null, 400);
}

if (empty($dateAdministered)) {
    sendResponse(false, 'Date administered is required', null, 400);
}

if (empty($healthcareFacility)) {
    sendResponse(false, 'Healthcare facility is required', null, 400);
}

// Validate and format date (accept both dd-mm-yyyy and yyyy-mm-dd)
$dateParts = [];
$formattedDate = $dateAdministered;

// Check if date is in dd-mm-yyyy format
if (preg_match('/^(\d{2})-(\d{2})-(\d{4})$/', $dateAdministered, $dateParts)) {
    $formattedDate = sprintf('%04d-%02d-%02d', $dateParts[3], $dateParts[2], $dateParts[1]);
} elseif (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $dateAdministered)) {
    sendResponse(false, 'Invalid date format. Use dd-mm-yyyy or yyyy-mm-dd', null, 400);
}

// Validate date
$dateObj = DateTime::createFromFormat('Y-m-d', $formattedDate);
if (!$dateObj || $dateObj->format('Y-m-d') !== $formattedDate) {
    sendResponse(false, 'Invalid date', null, 400);
}

// Get database connection
$conn = getDBConnection();

if (!$conn) {
    sendResponse(false, 'Database connection failed', null, 500);
}

try {
    // Check if child exists
    $childStmt = $conn->prepare("SELECT id FROM children WHERE id = ?");
    if (!$childStmt) {
        sendResponse(false, 'Database query preparation failed: ' . $conn->error, null, 500);
    }
    
    $childStmt->bind_param("i", $childId);
    if (!$childStmt->execute()) {
        $childStmt->close();
        sendResponse(false, 'Database query execution failed: ' . $conn->error, null, 500);
    }
    
    $childResult = $childStmt->get_result();
    
    if ($childResult->num_rows === 0) {
        $childStmt->close();
        sendResponse(false, 'Child not found with ID: ' . $childId, null, 404);
    }
    $childStmt->close();
    
    // Check if vaccinations table exists
    $tableCheck = $conn->query("SHOW TABLES LIKE 'vaccinations'");
    if (!$tableCheck || $tableCheck->num_rows === 0) {
        sendResponse(false, 'Vaccinations table does not exist. Please run fix_vaccinations_table.php first.', null, 500);
    }
    
    // Check if this vaccine already exists for this child
    $checkStmt = $conn->prepare("SELECT id, status FROM vaccinations WHERE child_id = ? AND vaccine_name = ?");
    if (!$checkStmt) {
        sendResponse(false, 'Database query preparation failed: ' . $conn->error, null, 500);
    }
    
    $checkStmt->bind_param("is", $childId, $vaccineName);
    if (!$checkStmt->execute()) {
        $errorMsg = $checkStmt->error ?: $conn->error;
        $checkStmt->close();
        sendResponse(false, 'Database query execution failed: ' . $errorMsg, null, 500);
    }
    
    $checkResult = $checkStmt->get_result();
    $existingVaccine = $checkResult->fetch_assoc();
    $checkStmt->close();
    
    if ($existingVaccine) {
        // Update existing record
        $updateStmt = $conn->prepare("UPDATE vaccinations SET completed_date = ?, status = 'completed', healthcare_facility = ?, batch_lot_number = ?, notes = ?, updated_at = CURRENT_TIMESTAMP WHERE id = ?");
        if (!$updateStmt) {
            sendResponse(false, 'Failed to prepare update query: ' . $conn->error, null, 500);
        }
        
        $updateStmt->bind_param("ssssi", $formattedDate, $healthcareFacility, $batchLotNumber, $notes, $existingVaccine['id']);
        
        if (!$updateStmt->execute()) {
            $errorMsg = $updateStmt->error ?: $conn->error;
            $updateStmt->close();
            sendResponse(false, 'Failed to update vaccination record: ' . $errorMsg, null, 500);
        }
        $updateStmt->close();
        $vaccinationId = $existingVaccine['id'];
    } else {
        // Insert new vaccination record
        // Try to extract dose number from vaccine name (e.g., "Hepatitis B (1st dose)" -> 1)
        $doseNumber = 1;
        if (preg_match('/\((\d+)(st|nd|rd|th)\s+dose\)/i', $vaccineName, $doseMatches)) {
            $doseNumber = (int)$doseMatches[1];
        }
        
        $insertStmt = $conn->prepare("INSERT INTO vaccinations (child_id, vaccine_name, dose_number, completed_date, status, healthcare_facility, batch_lot_number, notes) VALUES (?, ?, ?, ?, 'completed', ?, ?, ?)");
        if (!$insertStmt) {
            sendResponse(false, 'Failed to prepare insert query: ' . $conn->error, null, 500);
        }
        
        // 7 parameters: child_id(i), vaccine_name(s), dose_number(i), completed_date(s), healthcare_facility(s), batch_lot_number(s), notes(s)
        // Type string must match: i, s, i, s, s, s, s = "isissss"
        $insertStmt->bind_param("isissss", $childId, $vaccineName, $doseNumber, $formattedDate, $healthcareFacility, $batchLotNumber, $notes);
        
        if (!$insertStmt->execute()) {
            $errorMsg = $insertStmt->error ?: $conn->error;
            $insertStmt->close();
            sendResponse(false, 'Failed to save vaccination record: ' . $errorMsg, null, 500);
        }
        $vaccinationId = $conn->insert_id;
        $insertStmt->close();
    }
    
    // Get the saved vaccination record
    $getStmt = $conn->prepare("SELECT * FROM vaccinations WHERE id = ?");
    $getStmt->bind_param("i", $vaccinationId);
    $getStmt->execute();
    $vaccinationResult = $getStmt->get_result();
    $vaccination = $vaccinationResult->fetch_assoc();
    $getStmt->close();
    
    // Format response
    $vaccinationData = [
        'id' => (int)$vaccination['id'],
        'child_id' => (int)$vaccination['child_id'],
        'vaccine_name' => $vaccination['vaccine_name'],
        'dose_number' => (int)$vaccination['dose_number'],
        'completed_date' => $vaccination['completed_date'],
        'status' => $vaccination['status'],
        'healthcare_facility' => $vaccination['healthcare_facility'],
        'batch_lot_number' => $vaccination['batch_lot_number'],
        'notes' => $vaccination['notes']
    ];
    
    sendResponse(true, 'Vaccination record saved successfully', ['vaccination' => $vaccinationData], 201);
    
} catch (Exception $e) {
    error_log("Mark vaccine completed error: " . $e->getMessage());
    error_log("Stack trace: " . $e->getTraceAsString());
    sendResponse(false, 'An error occurred: ' . $e->getMessage(), null, 500);
} catch (Error $e) {
    error_log("Mark vaccine completed fatal error: " . $e->getMessage());
    error_log("Stack trace: " . $e->getTraceAsString());
    sendResponse(false, 'A fatal error occurred: ' . $e->getMessage(), null, 500);
} finally {
    if (isset($conn)) {
        $conn->close();
    }
}

?>


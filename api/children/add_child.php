<?php
/**
 * Add Child API Endpoint
 * Handles adding a new child profile with basic information including parent name
 * 
 * POST /api/children/add_child.php
 * 
 * Request Body:
 * {
 *   "userId": 1,
 *   "parentName": "Parent Full Name",
 *   "name": "Child Name",
 *   "dateOfBirth": "2020-01-15",
 *   "gender": "male"
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
$userId = isset($input['userId']) ? (int)$input['userId'] : 0;
$parentName = isset($input['parentName']) ? sanitizeInput($input['parentName']) : '';
$name = isset($input['name']) ? sanitizeInput($input['name']) : '';
$dateOfBirth = isset($input['dateOfBirth']) ? sanitizeInput($input['dateOfBirth']) : '';
$gender = isset($input['gender']) ? strtolower(sanitizeInput($input['gender'])) : '';

// Validate required fields
if ($userId <= 0) {
    sendResponse(false, 'Invalid user ID', null, 400);
}

if (empty($parentName)) {
    sendResponse(false, 'Parent name is required', null, 400);
}

if (empty($name)) {
    sendResponse(false, 'Child name is required', null, 400);
}

if (empty($dateOfBirth)) {
    sendResponse(false, 'Date of birth is required', null, 400);
}

// Validate date format (accept both dd-mm-yyyy and yyyy-mm-dd)
$dateParts = [];
$formattedDate = $dateOfBirth;

// Check if date is in dd-mm-yyyy format
if (preg_match('/^(\d{2})-(\d{2})-(\d{4})$/', $dateOfBirth, $dateParts)) {
    $formattedDate = sprintf('%04d-%02d-%02d', $dateParts[3], $dateParts[2], $dateParts[1]);
} elseif (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $dateOfBirth)) {
    sendResponse(false, 'Invalid date format. Use dd-mm-yyyy or yyyy-mm-dd', null, 400);
}

// Validate date
$dateObj = DateTime::createFromFormat('Y-m-d', $formattedDate);
if (!$dateObj || $dateObj->format('Y-m-d') !== $formattedDate) {
    sendResponse(false, 'Invalid date', null, 400);
}

// Validate gender
$validGenders = ['male', 'female', 'other'];
if (!in_array($gender, $validGenders)) {
    // Try to convert common variations
    $genderMap = [
        'Male' => 'male',
        'Female' => 'female',
        'Other' => 'other'
    ];
    $gender = $genderMap[$gender] ?? 'other';
}

// Get database connection
$conn = getDBConnection();

if (!$conn) {
    sendResponse(false, 'Database connection failed', null, 500);
}

try {
    // Check if user exists and get user name if parent name not provided
    $userStmt = $conn->prepare("SELECT id, full_name FROM users WHERE id = ?");
    $userStmt->bind_param("i", $userId);
    $userStmt->execute();
    $userResult = $userStmt->get_result();
    
    if ($userResult->num_rows === 0) {
        $userStmt->close();
        sendResponse(false, 'User not found', null, 404);
    }
    
    $userRow = $userResult->fetch_assoc();
    // Use provided parent name or fallback to user's full_name
    if (empty($parentName)) {
        $parentName = $userRow['full_name'];
    }
    $userStmt->close();
    
    // Start transaction
    $conn->begin_transaction();
    
    // Insert child into children table with parent_name
    $childStmt = $conn->prepare("INSERT INTO children (user_id, parent_name, name, date_of_birth, gender) VALUES (?, ?, ?, ?, ?)");
    $childStmt->bind_param("issss", $userId, $parentName, $name, $formattedDate, $gender);
    
    if (!$childStmt->execute()) {
        $childStmt->close();
        $conn->rollback();
        sendResponse(false, 'Failed to add child: ' . $conn->error, null, 500);
    }
    
    $childId = $conn->insert_id;
    $childStmt->close();
    
    // Commit transaction
    $conn->commit();
    
    // Return child data
    $childData = [
        'id' => (int)$childId,
        'user_id' => (int)$userId,
        'parent_name' => $parentName,
        'name' => $name,
        'date_of_birth' => $formattedDate,
        'gender' => $gender
    ];
    
    sendResponse(true, 'Child added successfully', ['child' => $childData], 201);
    
} catch (Exception $e) {
    if ($conn->in_transaction) {
        $conn->rollback();
    }
    error_log("Add child error: " . $e->getMessage());
    sendResponse(false, 'An error occurred: ' . $e->getMessage(), null, 500);
} finally {
    $conn->close();
}

?>


<?php
/**
 * Registration API Endpoint
 * POST /api/auth/register.php
 * 
 * Request Body:
 * {
 *   "fullName": "John Doe",
 *   "email": "user@example.com",
 *   "phone": "1234567890",
 *   "password": "password123"
 * }
 * 
 * Response:
 * {
 *   "success": true,
 *   "message": "Registration successful",
 *   "data": {
 *     "user": {
 *       "id": 1,
 *       "full_name": "John Doe",
 *       "email": "user@example.com",
 *       "phone": "1234567890",
 *       "email_verified": 0
 *     }
 *   }
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

// Validate and sanitize input
$fullName = isset($input['fullName']) ? sanitizeInput($input['fullName']) : '';
$email = isset($input['email']) ? sanitizeInput($input['email']) : '';
$phone = isset($input['phone']) ? sanitizeInput($input['phone']) : '';
$password = isset($input['password']) ? $input['password'] : '';

// Validate required fields
if (empty($fullName) || empty($email) || empty($password)) {
    sendResponse(false, 'Full name, email, and password are required', null, 400);
}

// Validate email format
if (!isValidEmail($email)) {
    sendResponse(false, 'Invalid email format', null, 400);
}

// Validate password length
if (strlen($password) < 6) {
    sendResponse(false, 'Password must be at least 6 characters', null, 400);
}

// Get database connection
$conn = getDBConnection();

if (!$conn) {
    sendResponse(false, 'Database connection failed', null, 500);
}

try {
    // Check if email already exists
    $checkStmt = $conn->prepare("SELECT id FROM users WHERE email = ?");
    $checkStmt->bind_param("s", $email);
    $checkStmt->execute();
    $checkResult = $checkStmt->get_result();
    
    if ($checkResult->num_rows > 0) {
        $checkStmt->close();
        $conn->close();
        sendResponse(false, 'Email already registered', null, 409);
    }
    $checkStmt->close();
    
    // Hash password
    $hashedPassword = hashPassword($password);
    
    // Insert new user
    $stmt = $conn->prepare("INSERT INTO users (full_name, email, phone, password, email_verified) VALUES (?, ?, ?, ?, 0)");
    
    if (!$stmt) {
        sendResponse(false, 'Database query preparation failed', null, 500);
    }
    
    $stmt->bind_param("ssss", $fullName, $email, $phone, $hashedPassword);
    
    if (!$stmt->execute()) {
        $stmt->close();
        $conn->close();
        sendResponse(false, 'Registration failed. Please try again.', null, 500);
    }
    
    // Get the inserted user ID
    $userId = $conn->insert_id;
    
    // Prepare user data for response
    $userData = [
        'id' => (int)$userId,
        'full_name' => $fullName,
        'email' => $email,
        'phone' => $phone ?: '',
        'email_verified' => 0
    ];
    
    $stmt->close();
    $conn->close();
    
    // Success response
    sendResponse(true, 'Registration successful', ['user' => $userData], 201);
    
} catch (Exception $e) {
    error_log("Registration error: " . $e->getMessage());
    sendResponse(false, 'An error occurred during registration', null, 500);
}

?>


<?php
/**
 * Login API Endpoint
 * POST /api/auth/login.php
 * 
 * Request Body:
 * {
 *   "email": "user@example.com",
 *   "password": "password123"
 * }
 * 
 * Response:
 * {
 *   "success": true,
 *   "message": "Login successful",
 *   "data": {
 *     "user": {
 *       "id": 1,
 *       "full_name": "John Doe",
 *       "email": "user@example.com",
 *       "phone": "1234567890",
 *       "email_verified": 1
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

// Validate required fields
$email = isset($input['email']) ? sanitizeInput($input['email']) : '';
$password = isset($input['password']) ? $input['password'] : '';

if (empty($email) || empty($password)) {
    sendResponse(false, 'Email and password are required', null, 400);
}

// Validate email format
if (!isValidEmail($email)) {
    sendResponse(false, 'Invalid email format', null, 400);
}

// Get database connection
$conn = getDBConnection();

if (!$conn) {
    sendResponse(false, 'Database connection failed', null, 500);
}

try {
    // Prepare and execute query
    $stmt = $conn->prepare("SELECT id, full_name, email, phone, password, email_verified FROM users WHERE email = ?");
    
    if (!$stmt) {
        sendResponse(false, 'Database query preparation failed', null, 500);
    }
    
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result();
    
    // Check if user exists
    if ($result->num_rows === 0) {
        $stmt->close();
        $conn->close();
        sendResponse(false, 'Invalid email or password', null, 401);
    }
    
    // Get user data
    $user = $result->fetch_assoc();
    
    // Verify password
    if (!verifyPassword($password, $user['password'])) {
        $stmt->close();
        $conn->close();
        sendResponse(false, 'Invalid email or password', null, 401);
    }
    
    // Remove password from response
    unset($user['password']);
    
    // Prepare user data for response
    $userData = [
        'id' => (int)$user['id'],
        'full_name' => $user['full_name'],
        'email' => $user['email'],
        'phone' => $user['phone'] ?: '',
        'email_verified' => (int)$user['email_verified']
    ];
    
    $stmt->close();
    $conn->close();
    
    // Success response
    sendResponse(true, 'Login successful', ['user' => $userData], 200);
    
} catch (Exception $e) {
    error_log("Login error: " . $e->getMessage());
    sendResponse(false, 'An error occurred during login', null, 500);
}

?>


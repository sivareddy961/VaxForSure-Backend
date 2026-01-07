<?php
/**
 * Google Login API Endpoint
 * Handles Google Sign-In authentication
 * 
 * POST /api/auth/google_login.php
 * 
 * Request Body:
 * {
 *   "googleId": "google_user_id",
 *   "email": "user@example.com",
 *   "fullName": "User Name",
 *   "photoUrl": "https://..." (optional)
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
$googleId = isset($input['googleId']) ? sanitizeInput($input['googleId']) : '';
$email = isset($input['email']) ? sanitizeInput($input['email']) : '';
$fullName = isset($input['fullName']) ? sanitizeInput($input['fullName']) : '';
$photoUrl = isset($input['photoUrl']) ? sanitizeInput($input['photoUrl']) : null;
$phone = isset($input['phone']) ? sanitizeInput($input['phone']) : null;

// Validate required fields
if (empty($googleId) || empty($email) || empty($fullName)) {
    sendResponse(false, 'Missing required fields: googleId, email, and fullName are required', null, 400);
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
    // Check if user exists with this email
    $stmt = $conn->prepare("SELECT * FROM users WHERE email = ?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result();
    $user = $result->fetch_assoc();
    $stmt->close();
    
    if ($user) {
        // User exists - update Google ID if not set
        if (empty($user['google_id'])) {
            $updateStmt = $conn->prepare("UPDATE users SET google_id = ?, updated_at = CURRENT_TIMESTAMP WHERE id = ?");
            $updateStmt->bind_param("si", $googleId, $user['id']);
            $updateStmt->execute();
            $updateStmt->close();
        } else if ($user['google_id'] !== $googleId) {
            // Google ID mismatch - this shouldn't happen, but handle it
            sendResponse(false, 'Email already registered with different Google account', null, 400);
        }
        
        // Update name if provided and different
        if ($user['full_name'] !== $fullName) {
            $updateNameStmt = $conn->prepare("UPDATE users SET full_name = ?, updated_at = CURRENT_TIMESTAMP WHERE id = ?");
            $updateNameStmt->bind_param("si", $fullName, $user['id']);
            $updateNameStmt->execute();
            $updateNameStmt->close();
        }
        
        // Return user data
        $userData = [
            'id' => (int)$user['id'],
            'full_name' => $fullName,
            'email' => $user['email'],
            'phone' => $user['phone'],
            'email_verified' => (int)$user['email_verified']
        ];
        
        sendResponse(true, 'Login successful', ['user' => $userData], 200);
        
    } else {
        // New user - create account
        // Check if Google ID already exists
        $checkStmt = $conn->prepare("SELECT * FROM users WHERE google_id = ?");
        $checkStmt->bind_param("s", $googleId);
        $checkStmt->execute();
        $checkResult = $checkStmt->get_result();
        
        if ($checkResult->num_rows > 0) {
            $checkStmt->close();
            sendResponse(false, 'Google account already registered', null, 400);
        }
        $checkStmt->close();
        
        // Insert new user
        $insertStmt = $conn->prepare("INSERT INTO users (full_name, email, phone, google_id, email_verified, password) VALUES (?, ?, ?, ?, 1, NULL)");
        $insertStmt->bind_param("ssss", $fullName, $email, $phone, $googleId);
        
        if ($insertStmt->execute()) {
            $userId = $conn->insert_id;
            $insertStmt->close();
            
            // Return user data
            $userData = [
                'id' => (int)$userId,
                'full_name' => $fullName,
                'email' => $email,
                'phone' => $phone,
                'email_verified' => 1
            ];
            
            sendResponse(true, 'Account created successfully', ['user' => $userData], 201);
        } else {
            $insertStmt->close();
            sendResponse(false, 'Failed to create account: ' . $conn->error, null, 500);
        }
    }
    
} catch (Exception $e) {
    error_log("Google login error: " . $e->getMessage());
    sendResponse(false, 'An error occurred: ' . $e->getMessage(), null, 500);
} finally {
    $conn->close();
}

?>


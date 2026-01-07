<?php
/**
 * Verify OTP API Endpoint
 * Verifies the OTP code for password reset
 * 
 * POST /api/auth/verify_otp.php
 * 
 * Request Body:
 * {
 *   "email": "user@example.com",
 *   "otp": "123456"
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
$otp = isset($input['otp']) ? sanitizeInput($input['otp']) : '';

if (empty($email) || empty($otp)) {
    sendResponse(false, 'Email and OTP are required', null, 400);
}

// Validate email format
if (!isValidEmail($email)) {
    sendResponse(false, 'Invalid email format', null, 400);
}

// Validate OTP format (6 digits)
if (!preg_match('/^\d{6}$/', $otp)) {
    sendResponse(false, 'Invalid OTP format', null, 400);
}

// Get database connection
$conn = getDBConnection();

if (!$conn) {
    sendResponse(false, 'Database connection failed', null, 500);
}

try {
    // Find valid OTP
    $stmt = $conn->prepare("SELECT id, expires_at, used FROM password_reset_otps WHERE email = ? AND otp_code = ? AND used = 0 ORDER BY created_at DESC LIMIT 1");
    
    if (!$stmt) {
        sendResponse(false, 'Database query preparation failed', null, 500);
    }
    
    $stmt->bind_param("ss", $email, $otp);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows === 0) {
        $stmt->close();
        $conn->close();
        sendResponse(false, 'Invalid or expired verification code', null, 400);
    }
    
    $otpRecord = $result->fetch_assoc();
    $stmt->close();
    
    // Check if OTP is expired
    $expiresAt = new DateTime($otpRecord['expires_at']);
    $now = new DateTime();
    
    if ($now > $expiresAt) {
        // Mark as used
        $updateStmt = $conn->prepare("UPDATE password_reset_otps SET used = 1 WHERE id = ?");
        $updateStmt->bind_param("i", $otpRecord['id']);
        $updateStmt->execute();
        $updateStmt->close();
        
        $conn->close();
        sendResponse(false, 'Verification code has expired. Please request a new one', null, 400);
    }
    
    // OTP is valid
    // Mark OTP as used
    $updateStmt = $conn->prepare("UPDATE password_reset_otps SET used = 1 WHERE id = ?");
    $updateStmt->bind_param("i", $otpRecord['id']);
    $updateStmt->execute();
    $updateStmt->close();
    
    $conn->close();
    
    sendResponse(true, 'Verification code verified successfully', null, 200);
    
} catch (Exception $e) {
    error_log("Verify OTP error: " . $e->getMessage());
    sendResponse(false, 'An error occurred. Please try again', null, 500);
}

?>




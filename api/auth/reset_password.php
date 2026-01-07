<?php
/**
 * Reset Password API Endpoint
 * Resets user password after OTP verification
 * 
 * POST /api/auth/reset_password.php
 * 
 * Request Body:
 * {
 *   "email": "user@example.com",
 *   "otp": "123456",
 *   "newPassword": "newpassword123"
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
$newPassword = isset($input['newPassword']) ? $input['newPassword'] : '';

if (empty($email) || empty($otp) || empty($newPassword)) {
    sendResponse(false, 'Email, OTP, and new password are required', null, 400);
}

// Validate email format
if (!isValidEmail($email)) {
    sendResponse(false, 'Invalid email format', null, 400);
}

// Validate password length
if (strlen($newPassword) < 6) {
    sendResponse(false, 'Password must be at least 6 characters long', null, 400);
}

// Get database connection
$conn = getDBConnection();

if (!$conn) {
    sendResponse(false, 'Database connection failed', null, 500);
}

try {
    // Verify OTP first
    $otpStmt = $conn->prepare("SELECT id, expires_at, used FROM password_reset_otps WHERE email = ? AND otp_code = ? AND used = 0 ORDER BY created_at DESC LIMIT 1");
    
    if (!$otpStmt) {
        sendResponse(false, 'Database query preparation failed', null, 500);
    }
    
    $otpStmt->bind_param("ss", $email, $otp);
    $otpStmt->execute();
    $otpResult = $otpStmt->get_result();
    
    if ($otpResult->num_rows === 0) {
        $otpStmt->close();
        $conn->close();
        sendResponse(false, 'Invalid or expired verification code', null, 400);
    }
    
    $otpRecord = $otpResult->fetch_assoc();
    $otpStmt->close();
    
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
    
    // Check if user exists
    $userStmt = $conn->prepare("SELECT id FROM users WHERE email = ?");
    $userStmt->bind_param("s", $email);
    $userStmt->execute();
    $userResult = $userStmt->get_result();
    
    if ($userResult->num_rows === 0) {
        $userStmt->close();
        $conn->close();
        sendResponse(false, 'User not found', null, 404);
    }
    
    $user = $userResult->fetch_assoc();
    $userStmt->close();
    
    // Hash new password
    $hashedPassword = hashPassword($newPassword);
    
    // Update password
    $updateStmt = $conn->prepare("UPDATE users SET password = ? WHERE email = ?");
    
    if (!$updateStmt) {
        sendResponse(false, 'Failed to prepare password update query: ' . $conn->error, null, 500);
    }
    
    $updateStmt->bind_param("ss", $hashedPassword, $email);
    
    if (!$updateStmt->execute()) {
        $updateStmt->close();
        $conn->close();
        sendResponse(false, 'Failed to reset password: ' . $conn->error, null, 500);
    }
    
    $updateStmt->close();
    
    // Mark OTP as used
    $markUsedStmt = $conn->prepare("UPDATE password_reset_otps SET used = 1 WHERE id = ?");
    $markUsedStmt->bind_param("i", $otpRecord['id']);
    $markUsedStmt->execute();
    $markUsedStmt->close();
    
    $conn->close();
    
    sendResponse(true, 'Password reset successfully. You can now login with your new password', null, 200);
    
} catch (Exception $e) {
    error_log("Reset password error: " . $e->getMessage());
    sendResponse(false, 'An error occurred. Please try again', null, 500);
}

?>




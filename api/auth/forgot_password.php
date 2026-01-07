<?php
/**
 * Forgot Password API Endpoint
 * Sends OTP to user's email for password reset
 * 
 * POST /api/auth/forgot_password.php
 * 
 * Request Body:
 * {
 *   "email": "user@example.com"
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

if (empty($email)) {
    sendResponse(false, 'Email is required', null, 400);
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
    // Check if user exists
    $stmt = $conn->prepare("SELECT id, full_name, email FROM users WHERE email = ?");
    
    if (!$stmt) {
        sendResponse(false, 'Database query preparation failed', null, 500);
    }
    
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result();
    
    // Always return success message for security (don't reveal if email exists)
    if ($result->num_rows === 0) {
        $stmt->close();
        $conn->close();
        // Return success to prevent email enumeration
        sendResponse(true, 'If the email exists, a verification code has been sent', null, 200);
    }
    
    $user = $result->fetch_assoc();
    $stmt->close();
    
    // Generate OTP
    $otpCode = generateOTP();
    $expiresAt = date('Y-m-d H:i:s', strtotime('+10 minutes')); // OTP expires in 10 minutes
    
    // Check if password_reset_otps table exists, create if not
    $tableCheck = $conn->query("SHOW TABLES LIKE 'password_reset_otps'");
    if ($tableCheck->num_rows == 0) {
        // Create table if it doesn't exist
        $createTable = "CREATE TABLE IF NOT EXISTS password_reset_otps (
            id INT AUTO_INCREMENT PRIMARY KEY,
            email VARCHAR(255) NOT NULL,
            otp_code VARCHAR(6) NOT NULL,
            expires_at DATETIME NOT NULL,
            used TINYINT(1) DEFAULT 0,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            INDEX idx_email (email),
            INDEX idx_otp (otp_code)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4";
        $conn->query($createTable);
    }
    
    // Delete old unused OTPs for this email
    $deleteStmt = $conn->prepare("DELETE FROM password_reset_otps WHERE email = ? AND (used = 1 OR expires_at < NOW())");
    if ($deleteStmt) {
        $deleteStmt->bind_param("s", $email);
        $deleteStmt->execute();
        $deleteStmt->close();
    }
    
    // Insert new OTP
    $insertStmt = $conn->prepare("INSERT INTO password_reset_otps (email, otp_code, expires_at) VALUES (?, ?, ?)");
    
    if (!$insertStmt) {
        error_log("Failed to prepare OTP insert: " . $conn->error);
        $conn->close();
        sendResponse(false, 'Failed to generate verification code', null, 500);
    }
    
    $insertStmt->bind_param("sss", $email, $otpCode, $expiresAt);
    
    if (!$insertStmt->execute()) {
        error_log("Failed to execute OTP insert: " . $insertStmt->error);
        $insertStmt->close();
        $conn->close();
        sendResponse(false, 'Failed to generate verification code', null, 500);
    }
    
    $insertStmt->close();
    
    // Send email with OTP
    $userName = $user['full_name'];
    $subject = "Password Reset Verification Code - VaxForSure";
    $message = "
    <html>
    <head>
        <style>
            body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; }
            .container { max-width: 600px; margin: 0 auto; padding: 20px; }
            .header { background-color: #4DB6AC; color: white; padding: 20px; text-align: center; border-radius: 5px 5px 0 0; }
            .content { background-color: #f9f9f9; padding: 30px; border-radius: 0 0 5px 5px; }
            .otp-box { background-color: #fff; border: 2px dashed #4DB6AC; padding: 20px; text-align: center; margin: 20px 0; border-radius: 5px; }
            .otp-code { font-size: 32px; font-weight: bold; color: #4DB6AC; letter-spacing: 5px; }
            .footer { text-align: center; margin-top: 20px; font-size: 12px; color: #666; }
        </style>
    </head>
    <body>
        <div class='container'>
            <div class='header'>
                <h2>VaxForSure</h2>
            </div>
            <div class='content'>
                <h3>Hello $userName,</h3>
                <p>You have requested to reset your password. Please use the verification code below:</p>
                <div class='otp-box'>
                    <div class='otp-code'>$otpCode</div>
                </div>
                <p>This code will expire in 10 minutes.</p>
                <p>If you didn't request this, please ignore this email.</p>
                <div class='footer'>
                    <p>Best regards,<br>VaxForSure Team</p>
                </div>
            </div>
        </div>
    </body>
    </html>
    ";
    
    // Send email - wrap in try-catch to prevent 500 errors
    $emailSent = false;
    try {
        $emailSent = @sendEmail($email, $subject, $message);
    } catch (Exception $emailException) {
        error_log("Email sending exception: " . $emailException->getMessage());
        $emailSent = false;
    } catch (Error $emailError) {
        error_log("Email sending error: " . $emailError->getMessage());
        $emailSent = false;
    }
    
    // Log OTP to file for development/testing
    $logFile = __DIR__ . '/../../otp_log.txt';
    $logMessage = date('Y-m-d H:i:s') . " - Email: $email - OTP: $otpCode - Sent: " . ($emailSent ? 'YES' : 'NO') . "\n";
    @file_put_contents($logFile, $logMessage, FILE_APPEND);
    
    // Log email send status (for debugging)
    if (!$emailSent) {
        error_log("Failed to send email to: $email - OTP: $otpCode");
        // Log detailed error info
        $errorLogFile = __DIR__ . '/../../email_error_log.txt';
        $errorDetails = date('Y-m-d H:i:s') . " - Failed to send to: $email\n";
        $errorDetails .= "OTP: $otpCode\n";
        $errorDetails .= "Check: 1) App Password correct 2) 2-Step Verification enabled 3) Firewall settings\n\n";
        @file_put_contents($errorLogFile, $errorDetails, FILE_APPEND);
    } else {
        error_log("Email sent successfully to: $email");
    }
    
    $conn->close();
    
    // For development: include OTP in response (remove in production)
    $responseMessage = 'If the email exists, a verification code has been sent';
    
    // Always return success message (security best practice)
    // DO NOT include OTP in response for security (only log it)
    sendResponse(true, $responseMessage, null, 200);
    
} catch (Exception $e) {
    error_log("Forgot password error: " . $e->getMessage());
    error_log("Stack trace: " . $e->getTraceAsString());
    if (isset($conn)) {
        @$conn->close();
    }
    sendResponse(false, 'An error occurred. Please try again later', null, 500);
} catch (Error $e) {
    error_log("Forgot password fatal error: " . $e->getMessage());
    error_log("Stack trace: " . $e->getTraceAsString());
    if (isset($conn)) {
        @$conn->close();
    }
    sendResponse(false, 'An error occurred. Please try again later', null, 500);
}

?>


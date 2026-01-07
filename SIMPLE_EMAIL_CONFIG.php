<?php
/**
 * Simple Email Configuration
 * Copy these settings to config.php sendEmail() function
 */

// ============================================
// GMAIL SMTP CONFIGURATION
// ============================================
// Update these values with your Gmail credentials:

$GMAIL_USERNAME = 'your-email@gmail.com';  // Your Gmail address
$GMAIL_APP_PASSWORD = 'your-app-password'; // Gmail App Password (16 characters, no spaces)
$FROM_EMAIL = 'your-email@gmail.com';      // Same as username
$FROM_NAME = 'VaxForSure';

// ============================================
// HOW TO GET GMAIL APP PASSWORD:
// ============================================
// 1. Go to: https://myaccount.google.com/
// 2. Click "Security" → Enable "2-Step Verification"
// 3. Go to "App passwords"
// 4. Select "Mail" and "Other (Custom name)"
// 5. Enter name: "VaxForSure"
// 6. Click "Generate"
// 7. Copy the 16-character password
// 8. Paste it above (remove spaces)

// ============================================
// COPY THIS TO config.php sendEmail() FUNCTION:
// ============================================

/*
function sendEmail($to, $subject, $message) {
    // Gmail SMTP Configuration
    $smtp_host = 'smtp.gmail.com';
    $smtp_port = 587;
    $smtp_username = 'your-email@gmail.com';  // UPDATE THIS
    $smtp_password = 'your-app-password';    // UPDATE THIS
    $smtp_from_email = 'your-email@gmail.com'; // UPDATE THIS
    $smtp_from_name = 'VaxForSure';
    
    // If credentials not set, use mail() fallback
    if (empty($smtp_username) || empty($smtp_password) || 
        $smtp_username == 'your-email@gmail.com') {
        $headers = "MIME-Version: 1.0" . "\r\n";
        $headers .= "Content-type:text/html;charset=UTF-8" . "\r\n";
        $headers .= "From: $smtp_from_name <$smtp_from_email>" . "\r\n";
        return @mail($to, $subject, $message, $headers);
    }
    
    // SMTP sending code (already in config.php)
    // ... rest of SMTP code ...
}
*/

?>




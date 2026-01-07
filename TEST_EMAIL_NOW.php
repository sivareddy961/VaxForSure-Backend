<?php
/**
 * Test Email Sending - Quick Test
 * Access: http://localhost/vaxforsure/TEST_EMAIL_NOW.php
 * 
 * This will test if email configuration is working correctly
 */

require_once 'config.php';

header('Content-Type: text/html; charset=UTF-8');

echo "<!DOCTYPE html><html><head><title>Test Email</title>";
echo "<style>
body{font-family:Arial;padding:20px;background:#f5f5f5;max-width:800px;margin:0 auto;}
.success{color:green;padding:15px;background:#d4edda;border:2px solid #4caf50;margin:10px 0;border-radius:5px;}
.error{color:red;padding:15px;background:#f8d7da;border:2px solid #f44336;margin:10px 0;border-radius:5px;}
.info{color:#0c5460;padding:15px;background:#d1ecf1;border:2px solid #2196f3;margin:10px 0;border-radius:5px;}
h1{color:#333;border-bottom:3px solid #4DB6AC;padding-bottom:10px;}
</style></head><body>";

echo "<h1>📧 Test Email Configuration</h1>";

// Check if email_config.php exists
$configFile = __DIR__ . '/email_config.php';
if (!file_exists($configFile)) {
    echo "<div class='error'><strong>❌ Error:</strong> email_config.php not found!</div>";
    echo "<p>Please run: <a href='QUICK_EMAIL_SETUP.php'>QUICK_EMAIL_SETUP.php</a> first.</p>";
    exit;
}

// Load config
include $configFile;

echo "<div class='info'>";
echo "<h2>📋 Current Configuration</h2>";
echo "<p><strong>SMTP Host:</strong> $smtp_host</p>";
echo "<p><strong>SMTP Port:</strong> $smtp_port</p>";
echo "<p><strong>Email:</strong> " . htmlspecialchars($smtp_username) . "</p>";
echo "<p><strong>From Name:</strong> $smtp_from_name</p>";
echo "</div>";

// Check PHPMailer
$phpmailerPath = __DIR__ . '/PHPMailer/src/PHPMailer.php';
if (file_exists($phpmailerPath)) {
    echo "<div class='success'><strong>✅ PHPMailer:</strong> Installed</div>";
} else {
    echo "<div class='error'><strong>⚠️ PHPMailer:</strong> Not found - will use fallback SMTP</div>";
    echo "<p><a href='INSTALL_PHPMailer_AUTO.php'>Install PHPMailer</a> (recommended)</p>";
}

// Test email sending
if (isset($_GET['test']) && $_GET['test'] == '1') {
    echo "<hr>";
    echo "<h2>🧪 Testing Email Sending...</h2>";
    
    $testEmail = $smtp_username; // Send test to configured email
    $testOtp = generateOTP();
    
    $subject = "Test Email - VaxForSure OTP";
    $message = "
    <html>
    <body style='font-family:Arial;padding:20px;'>
        <h2 style='color:#4DB6AC;'>VaxForSure - Email Test</h2>
        <p>This is a test email to verify your email configuration is working correctly.</p>
        <div style='background:#f0f0f0;padding:20px;margin:20px 0;border-radius:5px;text-align:center;'>
            <h3>Test OTP Code:</h3>
            <div style='font-size:32px;font-weight:bold;color:#4DB6AC;letter-spacing:5px;'>$testOtp</div>
        </div>
        <p>If you received this email, your configuration is working! ✅</p>
        <p><small>This is a test email sent at " . date('Y-m-d H:i:s') . "</small></p>
    </body>
    </html>
    ";
    
    echo "<p><strong>Sending test email to:</strong> $testEmail</p>";
    
    $result = sendEmail($testEmail, $subject, $message);
    
    if ($result) {
        echo "<div class='success'>";
        echo "<strong>✅ Email sent successfully!</strong><br>";
        echo "Please check your inbox at: <strong>$testEmail</strong><br>";
        echo "Also check your spam/junk folder if you don't see it immediately.<br>";
        echo "Test OTP: <strong>$testOtp</strong>";
        echo "</div>";
    } else {
        echo "<div class='error'>";
        echo "<strong>❌ Email sending failed!</strong><br>";
        echo "Please check:<br>";
        echo "1. Gmail App Password is correct<br>";
        echo "2. 2-Step Verification is enabled on Gmail<br>";
        echo "3. Check error logs: <code>C:\\xampp\\apache\\logs\\error.log</code><br>";
        echo "4. Try installing PHPMailer: <a href='INSTALL_PHPMailer_AUTO.php'>Install PHPMailer</a>";
        echo "</div>";
    }
    
    echo "<hr>";
}

echo "<h2>🚀 Quick Actions</h2>";
echo "<p><a href='?test=1'><button style='padding:12px 24px;background:#4DB6AC;color:white;border:none;border-radius:5px;cursor:pointer;font-size:16px;'>Send Test Email</button></a></p>";
echo "<p><a href='QUICK_EMAIL_SETUP.php'>Edit Email Configuration</a></p>";
echo "<p><a href='INSTALL_PHPMailer_AUTO.php'>Install PHPMailer (Recommended)</a></p>";

echo "</body></html>";
?>


<?php
/**
 * Complete Email Test - Verify everything works
 * Access: http://localhost:8080/vaxforsure/TEST_EMAIL_COMPLETE.php
 */

require_once 'config.php';
include __DIR__ . '/email_config.php';

header('Content-Type: text/html; charset=UTF-8');

echo "<!DOCTYPE html><html><head><title>Complete Email Test</title>";
echo "<style>
body{font-family:Arial;padding:20px;background:#f5f5f5;max-width:900px;margin:0 auto;}
.success{color:green;padding:15px;background:#d4edda;border:2px solid #4caf50;margin:10px 0;border-radius:5px;font-weight:bold;}
.error{color:red;padding:15px;background:#f8d7da;border:2px solid #f44336;margin:10px 0;border-radius:5px;font-weight:bold;}
.info{color:#0c5460;padding:15px;background:#d1ecf1;border:2px solid #2196f3;margin:10px 0;border-radius:5px;}
pre{background:#000;color:#0f0;padding:15px;border-radius:5px;overflow-x:auto;font-family:monospace;font-size:12px;}
.step{background:white;padding:20px;margin:10px 0;border-radius:5px;border-left:4px solid #4DB6AC;}
</style></head><body>";

echo "<h1>🧪 Complete Email Test</h1>";

// Check configuration
echo "<div class='step'>";
echo "<h2>Step 1: Configuration Check</h2>";
echo "<pre>";
echo "Email: " . htmlspecialchars($smtp_username) . "\n";
echo "Password: " . (strlen($smtp_password) > 0 ? "SET (" . strlen($smtp_password) . " chars)" : "NOT SET") . "\n";
echo "Host: $smtp_host\n";
echo "Port: $smtp_port\n";
echo "</pre>";

if (empty($smtp_password) || strlen($smtp_password) != 16) {
    echo "<div class='error'>❌ Password is not set correctly. Should be 16 characters.</div>";
    exit;
} else {
    echo "<div class='success'>✅ Configuration looks good!</div>";
}
echo "</div>";

// Test email sending
if (isset($_GET['test']) && $_GET['test'] == '1') {
    echo "<div class='step'>";
    echo "<h2>Step 2: Testing Email Sending</h2>";
    
    $testEmail = $smtp_username;
    $testOtp = generateOTP();
    
    $subject = "VaxForSure - Email Test";
    $message = "
    <html>
    <body style='font-family:Arial;padding:20px;'>
        <h2 style='color:#4DB6AC;'>VaxForSure Email Test</h2>
        <p>This is a test email to verify your email configuration is working correctly.</p>
        <div style='background:#f0f0f0;padding:20px;margin:20px 0;border-radius:5px;text-align:center;'>
            <h3>Test OTP Code:</h3>
            <div style='font-size:32px;font-weight:bold;color:#4DB6AC;letter-spacing:5px;'>$testOtp</div>
        </div>
        <p>If you received this email, your configuration is working! ✅</p>
        <p><small>Test sent at " . date('Y-m-d H:i:s') . "</small></p>
    </body>
    </html>
    ";
    
    echo "<p>Sending test email to: <strong>$testEmail</strong></p>";
    echo "<p>Please wait...</p>";
    
    $result = sendEmail($testEmail, $subject, $message);
    
    if ($result) {
        echo "<div class='success'>";
        echo "<strong>✅✅✅ EMAIL SENT SUCCESSFULLY! ✅✅✅</strong><br><br>";
        echo "Please check your inbox at: <strong>$testEmail</strong><br>";
        echo "Also check your spam/junk folder if you don't see it immediately.<br><br>";
        echo "Test OTP Code: <strong>$testOtp</strong><br><br>";
        echo "<strong>Your email configuration is working perfectly! 🎉</strong><br>";
        echo "You can now use forgot password in your app - OTP will be sent to email!";
        echo "</div>";
    } else {
        echo "<div class='error'>";
        echo "<strong>❌ Email sending failed</strong><br><br>";
        echo "Check error logs:<br>";
        echo "- <code>C:\\xampp\\apache\\logs\\error.log</code><br>";
        echo "- <code>email_error_log.txt</code><br><br>";
        echo "Common issues:<br>";
        echo "1. App Password is incorrect<br>";
        echo "2. 2-Step Verification not enabled<br>";
        echo "3. Firewall blocking port 587<br>";
        echo "</div>";
    }
    echo "</div>";
}

// Show test button
if (!isset($_GET['test'])) {
    echo "<div class='step'>";
    echo "<h2>Step 2: Test Email Now</h2>";
    echo "<p><a href='?test=1'><button style='padding:15px 30px;background:#4DB6AC;color:white;border:none;border-radius:5px;cursor:pointer;font-size:18px;font-weight:bold;'>Send Test Email</button></a></p>";
    echo "</div>";
}

echo "</body></html>";
?>


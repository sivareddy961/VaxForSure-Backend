<?php
/**
 * Quick Email Fix - Run this to test and fix email issues
 * Access: http://localhost:8080/vaxforsure/QUICK_FIX_EMAIL.php
 */

require_once 'config.php';

header('Content-Type: text/html; charset=UTF-8');

echo "<!DOCTYPE html><html><head><title>Quick Email Fix</title>";
echo "<style>
body{font-family:Arial;padding:20px;background:#f5f5f5;max-width:900px;margin:0 auto;}
.success{color:green;padding:15px;background:#d4edda;border:2px solid #4caf50;margin:10px 0;border-radius:5px;}
.error{color:red;padding:15px;background:#f8d7da;border:2px solid #f44336;margin:10px 0;border-radius:5px;}
.info{color:#0c5460;padding:15px;background:#d1ecf1;border:2px solid #2196f3;margin:10px 0;border-radius:5px;}
pre{background:#f4f4f4;padding:15px;border-radius:5px;overflow-x:auto;white-space:pre-wrap;}
</style></head><body>";

echo "<h1>🚀 Quick Email Fix & Test</h1>";

// Load config
include __DIR__ . '/email_config.php';

echo "<div class='info'>";
echo "<h2>Current Configuration</h2>";
echo "<p><strong>Email:</strong> $smtp_username</p>";
echo "<p><strong>App Password:</strong> " . (strlen($smtp_password) > 0 ? "✅ Set (" . strlen($smtp_password) . " characters)" : "❌ Not set") . "</p>";
echo "<p><strong>Host:</strong> $smtp_host</p>";
echo "<p><strong>Port:</strong> $smtp_port</p>";
echo "</div>";

if (isset($_GET['test']) && $_GET['test'] == '1') {
    echo "<h2>Testing Email Sending...</h2>";
    
    $testEmail = $smtp_username;
    $testOtp = generateOTP();
    
    $subject = "VaxForSure - Test Email";
    $message = "
    <html>
    <body style='font-family:Arial;padding:20px;'>
        <h2 style='color:#4DB6AC;'>VaxForSure Email Test</h2>
        <p>This is a test email to verify your configuration.</p>
        <div style='background:#f0f0f0;padding:20px;margin:20px 0;border-radius:5px;text-align:center;'>
            <h3>Test OTP:</h3>
            <div style='font-size:32px;font-weight:bold;color:#4DB6AC;letter-spacing:5px;'>$testOtp</div>
        </div>
        <p>If you received this, email is working! ✅</p>
    </body>
    </html>
    ";
    
    echo "<p>Sending test email to: <strong>$testEmail</strong></p>";
    
    $result = sendEmail($testEmail, $subject, $message);
    
    if ($result) {
        echo "<div class='success'>";
        echo "<strong>✅ SUCCESS! Email sent!</strong><br>";
        echo "Please check your inbox at: <strong>$testEmail</strong><br>";
        echo "Also check spam folder if not in inbox.<br>";
        echo "Test OTP Code: <strong>$testOtp</strong><br><br>";
        echo "<strong>Your email is now working! Try forgot password in your app now! 🎉</strong>";
        echo "</div>";
    } else {
        echo "<div class='error'>";
        echo "<strong>❌ Email sending failed</strong><br><br>";
        echo "<strong>Most Common Issues:</strong><br>";
        echo "1. <strong>App Password incorrect</strong> - Make sure you're using the 16-character App Password from Google<br>";
        echo "2. <strong>2-Step Verification not enabled</strong> - Enable it at: <a href='https://myaccount.google.com/security' target='_blank'>Google Security</a><br>";
        echo "3. <strong>Firewall blocking port 587</strong> - Check Windows Firewall settings<br>";
        echo "4. <strong>App Password expired</strong> - Generate a new one at: <a href='https://myaccount.google.com/apppasswords' target='_blank'>App Passwords</a><br><br>";
        echo "<strong>To Fix:</strong><br>";
        echo "1. Go to: <a href='https://myaccount.google.com/apppasswords' target='_blank'>https://myaccount.google.com/apppasswords</a><br>";
        echo "2. Generate a NEW App Password for 'Mail'<br>";
        echo "3. Copy the 16-character password (without spaces)<br>";
        echo "4. Update <code>email_config.php</code> with the new password<br>";
        echo "5. Test again using this page<br>";
        echo "</div>";
    }
    
    echo "<hr>";
}

echo "<h2>Test Email Now</h2>";
echo "<p><a href='?test=1'><button style='padding:15px 30px;background:#4DB6AC;color:white;border:none;border-radius:5px;cursor:pointer;font-size:18px;font-weight:bold;'>Send Test Email</button></a></p>";

echo "<h2>After Successful Test</h2>";
echo "<div class='info'>";
echo "<p>Once the test email works:</p>";
echo "<ol>";
echo "<li>✅ Open your Android app</li>";
echo "<li>✅ Click 'Forgot Password'</li>";
echo "<li>✅ Enter your email address</li>";
echo "<li>✅ Check your email inbox for OTP code</li>";
echo "<li>✅ Enter the OTP code in the app</li>";
echo "<li>✅ Reset your password</li>";
echo "</ol>";
echo "</div>";

echo "</body></html>";
?>


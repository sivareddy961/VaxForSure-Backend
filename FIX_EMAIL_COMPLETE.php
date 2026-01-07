<?php
/**
 * Complete Email Fix and Test
 * This script will:
 * 1. Verify email configuration
 * 2. Install PHPMailer if needed
 * 3. Test email sending
 * Access: http://localhost/vaxforsure/FIX_EMAIL_COMPLETE.php
 */

require_once 'config.php';

header('Content-Type: text/html; charset=UTF-8');

echo "<!DOCTYPE html><html><head><title>Fix Email Configuration</title>";
echo "<style>
body{font-family:Arial;padding:20px;background:#f5f5f5;max-width:900px;margin:0 auto;}
.success{color:green;padding:15px;background:#d4edda;border:2px solid #4caf50;margin:10px 0;border-radius:5px;}
.error{color:red;padding:15px;background:#f8d7da;border:2px solid #f44336;margin:10px 0;border-radius:5px;}
.info{color:#0c5460;padding:15px;background:#d1ecf1;border:2px solid #2196f3;margin:10px 0;border-radius:5px;}
.warning{color:#856404;padding:15px;background:#fff3cd;border:2px solid #ff9800;margin:10px 0;border-radius:5px;}
h1{color:#333;border-bottom:3px solid #4DB6AC;padding-bottom:10px;}
code{background:#f4f4f4;padding:2px 6px;border-radius:3px;font-family:monospace;}
</style></head><body>";

echo "<h1>🔧 Complete Email Fix and Test</h1>";

// Step 1: Check email_config.php
echo "<h2>Step 1: Checking Email Configuration</h2>";
$configFile = __DIR__ . '/email_config.php';
if (file_exists($configFile)) {
    include $configFile;
    echo "<div class='success'>✅ email_config.php found</div>";
    echo "<div class='info'>";
    echo "<p><strong>Email:</strong> " . htmlspecialchars($smtp_username) . "</p>";
    echo "<p><strong>Host:</strong> $smtp_host</p>";
    echo "<p><strong>Port:</strong> $smtp_port</p>";
    echo "<p><strong>Password:</strong> " . (strlen($smtp_password) > 0 ? "✅ Set (" . strlen($smtp_password) . " chars)" : "❌ Not set") . "</p>";
    echo "</div>";
} else {
    echo "<div class='error'>❌ email_config.php not found! Please create it first.</div>";
    exit;
}

// Step 2: Check PHPMailer
echo "<h2>Step 2: Checking PHPMailer</h2>";
$phpmailerPath = __DIR__ . '/PHPMailer/src/PHPMailer.php';
if (file_exists($phpmailerPath)) {
    echo "<div class='success'>✅ PHPMailer installed</div>";
} else {
    echo "<div class='warning'>⚠️ PHPMailer not found - will use raw SMTP</div>";
    echo "<p>Installing PHPMailer...</p>";
    
    $phpmailerDir = __DIR__ . '/PHPMailer/src';
    if (!is_dir($phpmailerDir)) {
        mkdir($phpmailerDir, 0755, true);
    }
    
    $files = [
        'PHPMailer.php' => 'https://raw.githubusercontent.com/PHPMailer/PHPMailer/master/src/PHPMailer.php',
        'SMTP.php' => 'https://raw.githubusercontent.com/PHPMailer/PHPMailer/master/src/SMTP.php',
        'Exception.php' => 'https://raw.githubusercontent.com/PHPMailer/PHPMailer/master/src/Exception.php'
    ];
    
    $installed = 0;
    foreach ($files as $filename => $url) {
        $filepath = $phpmailerDir . '/' . $filename;
        $content = @file_get_contents($url, false, stream_context_create([
            'http' => ['timeout' => 10, 'user_agent' => 'PHP']
        ]));
        if ($content && strlen($content) > 100) {
            file_put_contents($filepath, $content);
            $installed++;
        }
    }
    
    if ($installed == 3) {
        echo "<div class='success'>✅ PHPMailer installed successfully!</div>";
    } else {
        echo "<div class='warning'>⚠️ PHPMailer installation incomplete - will use raw SMTP method</div>";
    }
}

// Step 3: Test email sending
if (isset($_GET['test']) && $_GET['test'] == '1') {
    echo "<h2>Step 3: Testing Email Sending</h2>";
    
    $testEmail = $smtp_username; // Send to configured email
    $testOtp = generateOTP();
    
    $subject = "Test Email - VaxForSure";
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
        <p><small>Test sent at " . date('Y-m-d H:i:s') . "</small></p>
    </body>
    </html>
    ";
    
    echo "<p>Sending test email to: <strong>$testEmail</strong></p>";
    echo "<p>Please wait...</p>";
    
    // Enable error logging for this test
    $logFile = __DIR__ . '/email_test_log.txt';
    file_put_contents($logFile, date('Y-m-d H:i:s') . " - Starting email test\n", FILE_APPEND);
    
    $result = sendEmail($testEmail, $subject, $message);
    
    file_put_contents($logFile, date('Y-m-d H:i:s') . " - Email send result: " . ($result ? 'SUCCESS' : 'FAILED') . "\n", FILE_APPEND);
    
    if ($result) {
        echo "<div class='success'>";
        echo "<strong>✅ Email sent successfully!</strong><br>";
        echo "Please check your inbox at: <strong>$testEmail</strong><br>";
        echo "Also check your spam/junk folder if you don't see it immediately.<br>";
        echo "Test OTP Code: <strong>$testOtp</strong><br><br>";
        echo "<strong>Your email configuration is working correctly! 🎉</strong>";
        echo "</div>";
    } else {
        echo "<div class='error'>";
        echo "<strong>❌ Email sending failed!</strong><br><br>";
        echo "<strong>Please check:</strong><br>";
        echo "1. Gmail App Password is correct (16 characters)<br>";
        echo "2. 2-Step Verification is enabled on Gmail<br>";
        echo "3. App Password was generated for 'Mail' app<br>";
        echo "4. Firewall is not blocking port 587<br>";
        echo "5. Check error logs: <code>C:\\xampp\\apache\\logs\\error.log</code><br>";
        echo "6. Check test log: <code>email_test_log.txt</code><br><br>";
        echo "<strong>Common fixes:</strong><br>";
        echo "- Verify App Password at: <a href='https://myaccount.google.com/apppasswords' target='_blank'>Google App Passwords</a><br>";
        echo "- Make sure you're using App Password, NOT your regular Gmail password<br>";
        echo "- Try regenerating the App Password<br>";
        echo "</div>";
        
        // Show recent error log entries
        $errorLog = file_get_contents(__DIR__ . '/email_test_log.txt');
        if ($errorLog) {
            echo "<div class='info'><h3>Recent Log Entries:</h3><pre style='background:#f4f4f4;padding:10px;border-radius:5px;overflow-x:auto;'>" . htmlspecialchars($errorLog) . "</pre></div>";
        }
    }
    
    echo "<hr>";
}

echo "<h2>🚀 Next Steps</h2>";
echo "<div class='info'>";
echo "<p><strong>To test email sending:</strong></p>";
echo "<p><a href='?test=1'><button style='padding:12px 24px;background:#4DB6AC;color:white;border:none;border-radius:5px;cursor:pointer;font-size:16px;'>Test Email Now</button></a></p>";
echo "</div>";

echo "<p><strong>After successful test:</strong></p>";
echo "<ul>";
echo "<li>✅ Try forgot password from your app</li>";
echo "<li>✅ Enter your email address</li>";
echo "<li>✅ Check your inbox for OTP code</li>";
echo "<li>✅ Enter OTP and reset password</li>";
echo "</ul>";

echo "</body></html>";
?>


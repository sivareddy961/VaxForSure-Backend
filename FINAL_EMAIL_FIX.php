<?php
/**
 * FINAL EMAIL FIX - This will test and fix email issues completely
 * Access: http://localhost:8080/vaxforsure/FINAL_EMAIL_FIX.php
 */

require_once 'config.php';

header('Content-Type: text/html; charset=UTF-8');

// Handle password update
if (isset($_POST['password'])) {
    $password = trim($_POST['password']);
    $configFile = __DIR__ . '/email_config.php';
    
    $content = "<?php\n";
    $content .= "/**\n * Email Configuration\n */\n\n";
    $content .= "\$smtp_host = 'smtp.gmail.com';\n";
    $content .= "\$smtp_port = 587;\n";
    $content .= "\$smtp_username = 'budigiveerasivareddy@gmail.com';\n";
    $content .= "\$smtp_password = '" . addslashes($password) . "';\n";
    $content .= "\$smtp_from_email = 'budigiveerasivareddy@gmail.com';\n";
    $content .= "\$smtp_from_name = 'VaxForSure';\n";
    $content .= "\n?>\n";
    
    file_put_contents($configFile, $content);
    $success = "Password updated! Testing now...";
    
    // Test immediately
    include $configFile;
    $testEmail = $smtp_username;
    $testOtp = generateOTP();
    $subject = "VaxForSure - Test Email";
    $message = "<html><body><h2>Test Email</h2><p>OTP: <strong>$testOtp</strong></p></body></html>";
    $testResult = sendEmail($testEmail, $subject, $message);
    
    if ($testResult) {
        $emailSuccess = "✅ Email sent successfully! Check your inbox!";
    } else {
        $emailError = "❌ Email failed. Check diagnostics below.";
    }
}

include __DIR__ . '/email_config.php';

?>
<!DOCTYPE html>
<html>
<head>
    <title>Final Email Fix</title>
    <style>
        body{font-family:Arial;padding:20px;background:#f5f5f5;max-width:900px;margin:0 auto;}
        .success{color:green;padding:15px;background:#d4edda;border:2px solid #4caf50;margin:10px 0;border-radius:5px;}
        .error{color:red;padding:15px;background:#f8d7da;border:2px solid #f44336;margin:10px 0;border-radius:5px;}
        .info{color:#0c5460;padding:15px;background:#d1ecf1;border:2px solid #2196f3;margin:10px 0;border-radius:5px;}
        input{width:100%;padding:12px;margin:8px 0;border:1px solid #ddd;border-radius:5px;box-sizing:border-box;font-size:16px;}
        button{background:#4DB6AC;color:white;padding:15px 30px;border:none;border-radius:5px;cursor:pointer;font-size:18px;font-weight:bold;width:100%;}
        pre{background:#000;color:#0f0;padding:15px;border-radius:5px;overflow-x:auto;font-family:monospace;font-size:12px;}
        .step{background:white;padding:20px;margin:10px 0;border-radius:5px;border-left:4px solid #4DB6AC;}
    </style>
</head>
<body>
    <h1>🔧 FINAL EMAIL FIX</h1>
    
    <?php if (isset($success)): ?>
        <div class="success"><?php echo $success; ?></div>
    <?php endif; ?>
    
    <?php if (isset($emailSuccess)): ?>
        <div class="success"><?php echo $emailSuccess; ?></div>
    <?php endif; ?>
    
    <?php if (isset($emailError)): ?>
        <div class="error"><?php echo $emailError; ?></div>
    <?php endif; ?>
    
    <div class="step">
        <h2>Step 1: Enter Your App Password</h2>
        <p>Current Status:</p>
        <ul>
            <li>Email: <strong><?php echo htmlspecialchars($smtp_username); ?></strong></li>
            <li>Password: <strong><?php echo strlen($smtp_password) > 0 ? "SET (" . strlen($smtp_password) . " chars)" : "NOT SET"; ?></strong></li>
        </ul>
        
        <form method="POST">
            <label><strong>Enter Your 16-Character Gmail App Password:</strong></label>
            <input type="password" name="password" placeholder="Enter App Password (16 characters)" required>
            <button type="submit">💾 Update & Test Email</button>
        </form>
        
        <div class="info" style="margin-top:15px;">
            <strong>Don't have App Password?</strong><br>
            1. Go to: <a href="https://myaccount.google.com/apppasswords" target="_blank">Google App Passwords</a><br>
            2. Enable 2-Step Verification first<br>
            3. Generate App Password for "Mail"<br>
            4. Copy the 16-character password (remove spaces)<br>
            5. Paste it above
        </div>
    </div>
    
    <?php if (strlen($smtp_password) > 0): ?>
    <div class="step">
        <h2>Step 2: Run Diagnostics</h2>
        <p><a href="DIAGNOSE_EMAIL.php" target="_blank"><button style="width:auto;display:inline-block;">Run Email Diagnostics</button></a></p>
        <p>This will show exactly where the connection is failing.</p>
    </div>
    <?php endif; ?>
    
    <div class="step">
        <h2>Current Configuration</h2>
        <pre><?php
echo "SMTP Host: $smtp_host\n";
echo "SMTP Port: $smtp_port\n";
echo "Username: $smtp_username\n";
echo "Password: " . (strlen($smtp_password) > 0 ? str_repeat('*', strlen($smtp_password)) : 'NOT SET') . "\n";
echo "From Email: $smtp_from_email\n";
        ?></pre>
    </div>
    
</body>
</html>


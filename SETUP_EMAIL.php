<?php
/**
 * Email Setup - Easy Configuration
 * Access: http://localhost:8080/vaxforsure/SETUP_EMAIL.php
 */

$configFile = __DIR__ . '/email_config.php';

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['save'])) {
    $app_password = trim($_POST['app_password'] ?? '');
    
    if (empty($app_password)) {
        $error = "App Password is required!";
    } else {
        // Remove spaces
        $cleanPassword = str_replace(' ', '', $app_password);
        
        // Create/update config file
        $configContent = "<?php\n";
        $configContent .= "/**\n * Email Configuration\n */\n\n";
        $configContent .= "\$smtp_host = 'smtp.gmail.com';\n";
        $configContent .= "\$smtp_port = 587;\n";
        $configContent .= "\$smtp_username = 'budigiveerasivareddy@gmail.com';\n";
        $configContent .= "\$smtp_password = '" . addslashes($cleanPassword) . "';\n";
        $configContent .= "\$smtp_from_email = 'budigiveerasivareddy@gmail.com';\n";
        $configContent .= "\$smtp_from_name = 'VaxForSure';\n";
        $configContent .= "\n?>\n";
        
        if (file_put_contents($configFile, $configContent)) {
            $success = "Password saved successfully!";
            
            // Test email
            require_once __DIR__ . '/config.php';
            include $configFile;
            
            $testEmail = $smtp_username;
            $testOtp = str_pad((string)rand(100000, 999999), 6, '0', STR_PAD_LEFT);
            
            $subject = "VaxForSure - Test Email";
            $message = "<html><body style='font-family:Arial;padding:20px;'><h2 style='color:#4DB6AC;'>Email Test</h2><p>Your email configuration is working!</p><div style='background:#f0f0f0;padding:20px;text-align:center;margin:20px 0;'><h3>Test OTP:</h3><div style='font-size:32px;font-weight:bold;color:#4DB6AC;'>$testOtp</div></div></body></html>";
            
            $emailSent = sendEmail($testEmail, $subject, $message);
            $testResult = $emailSent;
            $testOtpDisplay = $testOtp;
        } else {
            $error = "Failed to save. Check file permissions.";
        }
    }
}

// Load current config
$current_email = 'budigiveerasivareddy@gmail.com';
$current_password_set = false;
if (file_exists($configFile)) {
    include $configFile;
    $current_password_set = !empty($smtp_password);
}

header('Content-Type: text/html; charset=UTF-8');
?>
<!DOCTYPE html>
<html>
<head>
    <title>Email Setup</title>
    <style>
        body{font-family:Arial;padding:20px;background:#f5f5f5;max-width:700px;margin:0 auto;}
        .success{color:green;padding:15px;background:#d4edda;border:2px solid #4caf50;margin:10px 0;border-radius:5px;font-weight:bold;}
        .error{color:red;padding:15px;background:#f8d7da;border:2px solid #f44336;margin:10px 0;border-radius:5px;font-weight:bold;}
        .info{color:#0c5460;padding:15px;background:#d1ecf1;border:2px solid #2196f3;margin:10px 0;border-radius:5px;}
        input{width:100%;padding:15px;margin:10px 0;border:2px solid #ddd;border-radius:5px;box-sizing:border-box;font-size:16px;}
        button{background:#4DB6AC;color:white;padding:15px 30px;border:none;border-radius:5px;cursor:pointer;font-size:18px;font-weight:bold;width:100%;margin-top:10px;}
        button:hover{background:#00897B;}
        h1{color:#333;border-bottom:3px solid #4DB6AC;padding-bottom:10px;}
    </style>
</head>
<body>
    <h1>📧 Email Setup</h1>
    
    <?php if (isset($success)): ?>
        <div class="success">✅ <?php echo $success; ?></div>
        
        <?php if (isset($testResult)): ?>
            <?php if ($testResult): ?>
                <div class="success">
                    <strong>🎉 EMAIL SENT SUCCESSFULLY!</strong><br>
                    Please check your inbox: <strong><?php echo htmlspecialchars($current_email); ?></strong><br>
                    Also check spam folder.<br>
                    Test OTP: <strong><?php echo $testOtpDisplay; ?></strong><br><br>
                    <strong>Email is working! Try forgot password in your app now! ✅</strong>
                </div>
            <?php else: ?>
                <div class="error">
                    <strong>❌ Email sending failed</strong><br><br>
                    <strong>Check:</strong><br>
                    1. App Password is correct (16 characters)<br>
                    2. 2-Step Verification is enabled<br>
                    3. App Password was generated for "Mail"<br>
                    4. Try running: <a href="DIAGNOSE_EMAIL.php">DIAGNOSE_EMAIL.php</a>
                </div>
            <?php endif; ?>
        <?php endif; ?>
    <?php endif; ?>
    
    <?php if (isset($error)): ?>
        <div class="error">❌ <?php echo $error; ?></div>
    <?php endif; ?>
    
    <div class="info">
        <h2>Current Status</h2>
        <p><strong>Email:</strong> <?php echo htmlspecialchars($current_email); ?></p>
        <p><strong>Password:</strong> <?php echo $current_password_set ? '✅ Set' : '❌ NOT SET'; ?></p>
    </div>
    
    <form method="POST">
        <h2>Enter Gmail App Password</h2>
        <label><strong>Your Email:</strong></label>
        <input type="email" value="<?php echo htmlspecialchars($current_email); ?>" disabled>
        
        <label><strong>Gmail App Password (16 characters):</strong></label>
        <input type="password" name="app_password" placeholder="Enter your 16-character App Password" required autofocus>
        <small style="color:#666;">This is the App Password from Google, NOT your regular password</small>
        
        <button type="submit" name="save">💾 Save & Test Email</button>
    </form>
    
    <div class="info" style="margin-top:30px;">
        <h3>How to Get App Password:</h3>
        <ol>
            <li>Go to: <a href="https://myaccount.google.com/apppasswords" target="_blank">Google App Passwords</a></li>
            <li>Enable <strong>2-Step Verification</strong> if not enabled</li>
            <li>Select app: <strong>"Mail"</strong></li>
            <li>Select device: <strong>"Other (Custom name)"</strong></li>
            <li>Enter name: <strong>"VaxForSure"</strong></li>
            <li>Click <strong>"Generate"</strong></li>
            <li>Copy the 16-character password (remove spaces)</li>
            <li>Paste it above and click "Save & Test Email"</li>
        </ol>
    </div>
    
    <div class="info" style="margin-top:20px;">
        <h3>🔍 Need Help?</h3>
        <p>Run diagnostics: <a href="DIAGNOSE_EMAIL.php"><strong>DIAGNOSE_EMAIL.php</strong></a></p>
        <p>This shows exactly where the connection fails.</p>
    </div>
</body>
</html>


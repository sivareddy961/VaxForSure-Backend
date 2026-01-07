<?php
/**
 * Complete Email Fix - Enter App Password and Test
 * Access: http://localhost:8080/vaxforsure/FIX_EMAIL_NOW.php
 */

$configFile = __DIR__ . '/email_config.php';

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['save_password'])) {
    $app_password = trim($_POST['app_password'] ?? '');
    
    if (empty($app_password)) {
        $error = "App Password is required!";
    } elseif (strlen(str_replace(' ', '', $app_password)) < 16) {
        $error = "App Password should be 16 characters (without spaces)";
    } else {
        // Remove spaces from app password
        $cleanPassword = str_replace(' ', '', $app_password);
        
        // Read current config
        $configContent = file_get_contents($configFile);
        
        // Update password
        $configContent = preg_replace(
            '/\$smtp_password\s*=\s*[\'"].*?[\'"];/',
            "\$smtp_password = '$cleanPassword';",
            $configContent
        );
        
        if (file_put_contents($configFile, $configContent)) {
            $success = "App Password saved successfully!";
            
            // Test email immediately
            require_once __DIR__ . '/config.php';
            include $configFile;
            
            $testEmail = $smtp_username;
            $testOtp = str_pad((string)rand(100000, 999999), 6, '0', STR_PAD_LEFT);
            
            $subject = "VaxForSure - Email Test";
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
            
            $emailSent = sendEmail($testEmail, $subject, $message);
            $testResult = $emailSent;
        } else {
            $error = "Failed to save configuration. Check file permissions.";
        }
    }
}

// Load current config
$current_email = '';
$current_password_set = false;
if (file_exists($configFile)) {
    include $configFile;
    $current_email = $smtp_username ?? '';
    $current_password_set = !empty($smtp_password);
}

header('Content-Type: text/html; charset=UTF-8');
?>
<!DOCTYPE html>
<html>
<head>
    <title>Fix Email Configuration</title>
    <style>
        body{font-family:Arial;padding:20px;background:#f5f5f5;max-width:700px;margin:0 auto;}
        .success{color:green;padding:15px;background:#d4edda;border:2px solid #4caf50;margin:10px 0;border-radius:5px;font-weight:bold;}
        .error{color:red;padding:15px;background:#f8d7da;border:2px solid #f44336;margin:10px 0;border-radius:5px;font-weight:bold;}
        .info{color:#0c5460;padding:15px;background:#d1ecf1;border:2px solid #2196f3;margin:10px 0;border-radius:5px;}
        .warning{color:#856404;padding:15px;background:#fff3cd;border:2px solid #ff9800;margin:10px 0;border-radius:5px;}
        input{width:100%;padding:15px;margin:10px 0;border:2px solid #ddd;border-radius:5px;box-sizing:border-box;font-size:16px;}
        button{background:#4DB6AC;color:white;padding:15px 30px;border:none;border-radius:5px;cursor:pointer;font-size:18px;font-weight:bold;width:100%;margin-top:10px;}
        button:hover{background:#00897B;}
        h1{color:#333;border-bottom:3px solid #4DB6AC;padding-bottom:10px;}
        code{background:#f4f4f4;padding:3px 8px;border-radius:3px;font-family:monospace;}
    </style>
</head>
<body>
    <h1>🔧 Fix Email Configuration</h1>
    
    <?php if (isset($success)): ?>
        <div class="success">✅ <?php echo $success; ?></div>
        
        <?php if (isset($testResult)): ?>
            <?php if ($testResult): ?>
                <div class="success">
                    <strong>🎉 EMAIL SENT SUCCESSFULLY!</strong><br>
                    Please check your inbox at: <strong><?php echo htmlspecialchars($current_email); ?></strong><br>
                    Also check spam folder if not in inbox.<br>
                    Test OTP Code: <strong><?php echo $testOtp; ?></strong><br><br>
                    <strong>Your email is now working! Try forgot password in your app! ✅</strong>
                </div>
            <?php else: ?>
                <div class="error">
                    <strong>❌ Email sending still failed</strong><br><br>
                    <strong>Please check:</strong><br>
                    1. App Password is correct (16 characters, no spaces)<br>
                    2. 2-Step Verification is enabled on Gmail<br>
                    3. App Password was generated for "Mail" app<br>
                    4. Try running diagnostics: <a href="DIAGNOSE_EMAIL.php">DIAGNOSE_EMAIL.php</a><br>
                </div>
            <?php endif; ?>
        <?php endif; ?>
    <?php endif; ?>
    
    <?php if (isset($error)): ?>
        <div class="error">❌ <?php echo $error; ?></div>
    <?php endif; ?>
    
    <div class="info">
        <h2>Current Configuration</h2>
        <p><strong>Email:</strong> <?php echo htmlspecialchars($current_email ?: 'Not set'); ?></p>
        <p><strong>App Password:</strong> <?php echo $current_password_set ? '✅ Set' : '❌ NOT SET - Please enter below'; ?></p>
    </div>
    
    <?php if (!$current_password_set): ?>
        <div class="warning">
            <strong>⚠️ App Password Required!</strong><br>
            Please enter your 16-character Gmail App Password below.
        </div>
    <?php endif; ?>
    
    <form method="POST">
        <h2>Enter Gmail App Password</h2>
        <label><strong>Your Email:</strong></label>
        <input type="email" value="<?php echo htmlspecialchars($current_email); ?>" disabled>
        
        <label><strong>Gmail App Password (16 characters):</strong></label>
        <input type="password" name="app_password" placeholder="Enter your 16-character App Password" required autofocus>
        <small style="color:#666;">This is the App Password from Google, NOT your regular password. Remove any spaces when entering.</small>
        
        <button type="submit" name="save_password">💾 Save & Test Email</button>
    </form>
    
    <div class="info" style="margin-top:30px;">
        <h3>📋 How to Get App Password:</h3>
        <ol>
            <li>Go to: <a href="https://myaccount.google.com/apppasswords" target="_blank">Google App Passwords</a></li>
            <li>Enable <strong>2-Step Verification</strong> if not already enabled</li>
            <li>Click <strong>"Select app"</strong> → Choose <strong>"Mail"</strong></li>
            <li>Click <strong>"Select device"</strong> → Choose <strong>"Other (Custom name)"</strong></li>
            <li>Enter name: <strong>"VaxForSure"</strong></li>
            <li>Click <strong>"Generate"</strong></li>
            <li><strong>Copy the 16-character password</strong> (it might show as 4 groups of 4 characters)</li>
            <li>Paste it in the field above (remove spaces if any)</li>
        </ol>
    </div>
    
    <div class="info" style="margin-top:20px;">
        <h3>🔍 Need More Help?</h3>
        <p>Run diagnostics: <a href="DIAGNOSE_EMAIL.php"><strong>DIAGNOSE_EMAIL.php</strong></a></p>
        <p>This will show exactly where the connection is failing.</p>
    </div>
</body>
</html>


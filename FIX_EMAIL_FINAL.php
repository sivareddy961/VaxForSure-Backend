<?php
/**
 * Final Email Fix - Complete Test and Verification
 * Access: http://localhost:8080/vaxforsure/FIX_EMAIL_FINAL.php
 */

require_once 'config.php';
include __DIR__ . '/email_config.php';

header('Content-Type: text/html; charset=UTF-8');

// Handle password update
if (isset($_POST['update_password'])) {
    $newPassword = trim($_POST['new_password'] ?? '');
    if (!empty($newPassword)) {
        $cleanPassword = str_replace(' ', '', $newPassword);
        $configContent = "<?php\n";
        $configContent .= "/**\n * Email Configuration\n */\n\n";
        $configContent .= "\$smtp_host = 'smtp.gmail.com';\n";
        $configContent .= "\$smtp_port = 587;\n";
        $configContent .= "\$smtp_username = 'budigiveerasivareddy@gmail.com';\n";
        $configContent .= "\$smtp_password = '$cleanPassword';\n";
        $configContent .= "\$smtp_from_email = 'budigiveerasivareddy@gmail.com';\n";
        $configContent .= "\$smtp_from_name = 'VaxForSure';\n";
        $configContent .= "\n?>\n";
        
        file_put_contents(__DIR__ . '/email_config.php', $configContent);
        $updateSuccess = true;
        // Reload config
        include __DIR__ . '/email_config.php';
    }
}

?>
<!DOCTYPE html>
<html>
<head>
    <title>Final Email Fix</title>
    <style>
        body{font-family:Arial;padding:20px;background:#f5f5f5;max-width:900px;margin:0 auto;}
        .success{color:green;padding:15px;background:#d4edda;border:2px solid #4caf50;margin:10px 0;border-radius:5px;font-weight:bold;}
        .error{color:red;padding:15px;background:#f8d7da;border:2px solid #f44336;margin:10px 0;border-radius:5px;font-weight:bold;}
        .info{color:#0c5460;padding:15px;background:#d1ecf1;border:2px solid #2196f3;margin:10px 0;border-radius:5px;}
        input{width:100%;padding:15px;margin:10px 0;border:2px solid #ddd;border-radius:5px;box-sizing:border-box;font-size:16px;}
        button{background:#4DB6AC;color:white;padding:15px 30px;border:none;border-radius:5px;cursor:pointer;font-size:18px;font-weight:bold;width:100%;}
        .test-btn{width:auto;display:inline-block;margin:5px;}
    </style>
</head>
<body>
    <h1>🔧 Final Email Fix & Test</h1>
    
    <?php if (isset($updateSuccess)): ?>
        <div class="success">✅ Password updated successfully!</div>
    <?php endif; ?>
    
    <div class="info">
        <h2>Current Configuration</h2>
        <p><strong>Email:</strong> <?php echo htmlspecialchars($smtp_username); ?></p>
        <p><strong>Password:</strong> <?php echo strlen($smtp_password) > 0 ? "✅ Set (" . strlen($smtp_password) . " chars)" : "❌ NOT SET"; ?></p>
        <?php if (strlen($smtp_password) != 16): ?>
            <p class="error">⚠️ Password should be 16 characters. Current: <?php echo strlen($smtp_password); ?></p>
        <?php endif; ?>
    </div>
    
    <div class="info">
        <h2>Update App Password</h2>
        <form method="POST">
            <label><strong>Enter Your NEW Gmail App Password (16 characters):</strong></label>
            <input type="password" name="new_password" placeholder="Enter 16-character App Password" required>
            <button type="submit" name="update_password">💾 Update Password</button>
        </form>
    </div>
    
    <div class="info">
        <h2>Test Tools</h2>
        <p><a href="TEST_SMTP_DETAILED.php"><button class="test-btn">1. Detailed SMTP Test</button></a></p>
        <p><strong>This will show exactly where the connection fails.</strong></p>
        <p><a href="TEST_EMAIL_COMPLETE.php?test=1"><button class="test-btn">2. Test Email Sending</button></a></p>
        <p><a href="DIAGNOSE_EMAIL.php"><button class="test-btn">3. Email Diagnostics</button></a></p>
    </div>
    
    <div class="info">
        <h2>📋 How to Get Correct App Password</h2>
        <ol>
            <li>Go to: <a href="https://myaccount.google.com/apppasswords" target="_blank">Google App Passwords</a></li>
            <li><strong>IMPORTANT:</strong> Make sure you're logged in as <strong><?php echo htmlspecialchars($smtp_username); ?></strong></li>
            <li>Enable <strong>2-Step Verification</strong> if not enabled</li>
            <li>Click <strong>"Select app"</strong> → Choose <strong>"Mail"</strong></li>
            <li>Click <strong>"Select device"</strong> → Choose <strong>"Other (Custom name)"</strong></li>
            <li>Enter name: <strong>"VaxForSure"</strong></li>
            <li>Click <strong>"Generate"</strong></li>
            <li><strong>Copy the 16-character password</strong> (it shows as 4 groups of 4)</li>
            <li>Paste it above and click "Update Password"</li>
            <li>Then run the "Detailed SMTP Test" to verify it works</li>
        </ol>
    </div>
    
    <div class="info">
        <h2>🔍 Troubleshooting</h2>
        <p>If email still fails after updating password:</p>
        <ol>
            <li>Run <strong>TEST_SMTP_DETAILED.php</strong> - This shows exactly where it fails</li>
            <li>Check error logs: <code>C:\xampp\apache\logs\error.log</code></li>
            <li>Verify App Password at: <a href="https://myaccount.google.com/apppasswords" target="_blank">Google App Passwords</a></li>
            <li>Make sure 2-Step Verification is enabled</li>
        </ol>
    </div>
</body>
</html>


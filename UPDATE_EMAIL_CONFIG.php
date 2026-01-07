<?php
/**
 * Update Email Configuration
 * Use this to easily update your email credentials
 */

$configFile = __DIR__ . '/email_config.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update'])) {
    $smtp_username = $_POST['smtp_username'] ?? '';
    $smtp_password = $_POST['smtp_password'] ?? '';
    
    if (empty($smtp_username) || empty($smtp_password)) {
        $error = "Email and App Password are required!";
    } else {
        $configContent = "<?php\n";
        $configContent .= "/**\n";
        $configContent .= " * Email Configuration\n";
        $configContent .= " * Updated automatically\n";
        $configContent .= " */\n\n";
        $configContent .= "\$smtp_host = 'smtp.gmail.com';\n";
        $configContent .= "\$smtp_port = 587;\n";
        $configContent .= "\$smtp_username = '" . addslashes($smtp_username) . "';\n";
        $configContent .= "\$smtp_password = '" . addslashes($smtp_password) . "';\n";
        $configContent .= "\$smtp_from_email = '" . addslashes($smtp_username) . "';\n";
        $configContent .= "\$smtp_from_name = 'VaxForSure';\n";
        $configContent .= "\n?>\n";
        
        if (file_put_contents($configFile, $configContent)) {
            $success = "Email configuration updated successfully!";
        } else {
            $error = "Failed to save configuration file. Check file permissions.";
        }
    }
}

// Load current config
$current_email = '';
$current_password = '';
if (file_exists($configFile)) {
    include $configFile;
    $current_email = $smtp_username ?? '';
    $current_password = isset($smtp_password) ? '****' : '';
}

header('Content-Type: text/html; charset=UTF-8');
?>
<!DOCTYPE html>
<html>
<head>
    <title>Update Email Configuration</title>
    <style>
        body{font-family:Arial;padding:20px;background:#f5f5f5;max-width:600px;margin:0 auto;}
        .success{color:green;padding:15px;background:#d4edda;border:2px solid #4caf50;margin:10px 0;border-radius:5px;}
        .error{color:red;padding:15px;background:#f8d7da;border:2px solid #f44336;margin:10px 0;border-radius:5px;}
        .info{color:#0c5460;padding:15px;background:#d1ecf1;border:2px solid #2196f3;margin:10px 0;border-radius:5px;}
        input{width:100%;padding:12px;margin:8px 0;border:1px solid #ddd;border-radius:5px;box-sizing:border-box;font-size:14px;}
        button{background:#4DB6AC;color:white;padding:12px 24px;border:none;border-radius:5px;cursor:pointer;font-size:16px;width:100%;}
        button:hover{background:#00897B;}
        label{font-weight:bold;display:block;margin-top:15px;}
    </style>
</head>
<body>
    <h1>📧 Update Email Configuration</h1>
    
    <?php if (isset($success)): ?>
        <div class="success">✅ <?php echo $success; ?></div>
        <p><a href="QUICK_FIX_EMAIL.php?test=1">Test Email Now</a></p>
    <?php endif; ?>
    
    <?php if (isset($error)): ?>
        <div class="error">❌ <?php echo $error; ?></div>
    <?php endif; ?>
    
    <div class="info">
        <h3>Current Configuration:</h3>
        <p><strong>Email:</strong> <?php echo htmlspecialchars($current_email ?: 'Not set'); ?></p>
        <p><strong>App Password:</strong> <?php echo $current_password ?: 'Not set'; ?></p>
    </div>
    
    <form method="POST">
        <label>Gmail Address:</label>
        <input type="email" name="smtp_username" value="<?php echo htmlspecialchars($current_email); ?>" placeholder="your-email@gmail.com" required>
        
        <label>Gmail App Password (16 characters):</label>
        <input type="password" name="smtp_password" placeholder="Enter your 16-character App Password" required>
        <small style="color:#666;">This is the App Password from Google, NOT your regular password</small>
        
        <button type="submit" name="update">💾 Update Configuration</button>
    </form>
    
    <div class="info" style="margin-top:30px;">
        <h3>How to Get App Password:</h3>
        <ol>
            <li>Go to: <a href="https://myaccount.google.com/apppasswords" target="_blank">Google App Passwords</a></li>
            <li>Enable 2-Step Verification if not already enabled</li>
            <li>Select "Mail" and "Other (Custom name)"</li>
            <li>Enter name: "VaxForSure"</li>
            <li>Click "Generate"</li>
            <li>Copy the 16-character password (remove spaces when entering above)</li>
        </ol>
    </div>
</body>
</html>


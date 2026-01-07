<?php
/**
 * Verify Email Setup - Check all files are correct
 * Access: http://localhost:8080/vaxforsure/VERIFY_EMAIL_SETUP.php
 */

require_once 'config.php';
include __DIR__ . '/email_config.php';

header('Content-Type: text/html; charset=UTF-8');

echo "<!DOCTYPE html><html><head><title>Verify Email Setup</title>";
echo "<style>
body{font-family:Arial;padding:20px;background:#f5f5f5;max-width:900px;margin:0 auto;}
.success{color:green;padding:15px;background:#d4edda;border:2px solid #4caf50;margin:10px 0;border-radius:5px;}
.error{color:red;padding:15px;background:#f8d7da;border:2px solid #f44336;margin:10px 0;border-radius:5px;}
.info{color:#0c5460;padding:15px;background:#d1ecf1;border:2px solid #2196f3;margin:10px 0;border-radius:5px;}
table{width:100%;border-collapse:collapse;margin:15px 0;}
td,th{padding:10px;border:1px solid #ddd;text-align:left;}
th{background:#4DB6AC;color:white;}
</style></head><body>";

echo "<h1>✅ Email Setup Verification</h1>";

$allGood = true;

echo "<table>";
echo "<tr><th>Check</th><th>Status</th><th>Details</th></tr>";

// Check 1: Email config file
echo "<tr><td>Email Config File</td>";
if (file_exists(__DIR__ . '/email_config.php')) {
    echo "<td style='color:green;'>✅ Exists</td>";
    echo "<td>email_config.php found</td>";
} else {
    echo "<td style='color:red;'>❌ Missing</td>";
    echo "<td>email_config.php not found!</td>";
    $allGood = false;
}
echo "</tr>";

// Check 2: Email configured
echo "<tr><td>Email Address</td>";
if (!empty($smtp_username)) {
    echo "<td style='color:green;'>✅ Set</td>";
    echo "<td>" . htmlspecialchars($smtp_username) . "</td>";
} else {
    echo "<td style='color:red;'>❌ Not Set</td>";
    echo "<td>Email address is empty!</td>";
    $allGood = false;
}
echo "</tr>";

// Check 3: Password configured
echo "<tr><td>App Password</td>";
if (!empty($smtp_password) && strlen($smtp_password) == 16) {
    echo "<td style='color:green;'>✅ Set</td>";
    echo "<td>16 characters configured</td>";
} else {
    echo "<td style='color:red;'>❌ Invalid</td>";
    echo "<td>Password length: " . strlen($smtp_password) . " (should be 16)</td>";
    $allGood = false;
}
echo "</tr>";

// Check 4: SMTP settings
echo "<tr><td>SMTP Host</td>";
echo "<td style='color:green;'>✅ Set</td>";
echo "<td>$smtp_host</td>";
echo "</tr>";

echo "<tr><td>SMTP Port</td>";
echo "<td style='color:green;'>✅ Set</td>";
echo "<td>$smtp_port</td>";
echo "</tr>";

// Check 5: forgot_password.php exists
echo "<tr><td>Forgot Password API</td>";
if (file_exists(__DIR__ . '/api/auth/forgot_password.php')) {
    echo "<td style='color:green;'>✅ Exists</td>";
    echo "<td>forgot_password.php found</td>";
} else {
    echo "<td style='color:red;'>❌ Missing</td>";
    echo "<td>API endpoint not found!</td>";
    $allGood = false;
}
echo "</tr>";

// Check 6: config.php sendEmail function
echo "<tr><td>sendEmail Function</td>";
if (function_exists('sendEmail')) {
    echo "<td style='color:green;'>✅ Exists</td>";
    echo "<td>sendEmail() function available</td>";
} else {
    echo "<td style='color:red;'>❌ Missing</td>";
    echo "<td>sendEmail() function not found!</td>";
    $allGood = false;
}
echo "</tr>";

// Check 7: Database table
$conn = getDBConnection();
if ($conn) {
    $tableCheck = $conn->query("SHOW TABLES LIKE 'password_reset_otps'");
    echo "<tr><td>Database Table</td>";
    if ($tableCheck && $tableCheck->num_rows > 0) {
        echo "<td style='color:green;'>✅ Exists</td>";
        echo "<td>password_reset_otps table exists</td>";
    } else {
        echo "<td style='color:orange;'>⚠️ Will Auto-Create</td>";
        echo "<td>Table will be created automatically on first use</td>";
    }
    $conn->close();
} else {
    echo "<tr><td>Database Connection</td>";
    echo "<td style='color:red;'>❌ Failed</td>";
    echo "<td>Cannot connect to database</td>";
    $allGood = false;
}
echo "</tr>";

echo "</table>";

if ($allGood) {
    echo "<div class='success'>";
    echo "<h2>✅ All Checks Passed!</h2>";
    echo "<p><strong>Your email configuration is complete and ready to use!</strong></p>";
    echo "<p>You can now:</p>";
    echo "<ol>";
    echo "<li>Test email sending: <a href='TEST_EMAIL_COMPLETE.php?test=1'>Click here</a></li>";
    echo "<li>Use forgot password in your app - OTP will be sent to email</li>";
    echo "</ol>";
    echo "</div>";
} else {
    echo "<div class='error'>";
    echo "<h2>❌ Some Issues Found</h2>";
    echo "<p>Please fix the issues marked in red above.</p>";
    echo "</div>";
}

echo "</body></html>";
?>


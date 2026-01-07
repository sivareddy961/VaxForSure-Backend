<?php
/**
 * Test Forgot Password Endpoint
 * Use this to test if the endpoint is working
 * Access: http://localhost/vaxforsure/TEST_FORGOT_PASSWORD.php
 */

require_once 'config.php';

header('Content-Type: text/html; charset=UTF-8');

echo "<!DOCTYPE html><html><head><title>Test Forgot Password</title>";
echo "<style>body{font-family:Arial;padding:20px;}";
echo ".success{color:green;padding:10px;background:#d4edda;border:1px solid #c3e6cb;margin:10px 0;}";
echo ".error{color:red;padding:10px;background:#f8d7da;border:1px solid #f5c6cb;margin:10px 0;}";
echo ".info{color:#0c5460;padding:10px;background:#d1ecf1;border:1px solid #bee5eb;margin:10px 0;}";
echo "input,button{padding:10px;margin:5px;}</style></head><body>";
echo "<h1>Test Forgot Password Endpoint</h1>";

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['email'])) {
    $email = $_POST['email'];
    
    // Test database connection
    $conn = getDBConnection();
    if (!$conn) {
        echo "<div class='error'>Database connection failed!</div>";
    } else {
        echo "<div class='info'>✓ Database connected successfully</div>";
        
        // Check if user exists
        $stmt = $conn->prepare("SELECT id, email, full_name FROM users WHERE email = ?");
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result->num_rows > 0) {
            $user = $result->fetch_assoc();
            echo "<div class='info'>✓ User found: " . htmlspecialchars($user['full_name']) . "</div>";
            
            // Check if password_reset_otps table exists
            $tableCheck = $conn->query("SHOW TABLES LIKE 'password_reset_otps'");
            if ($tableCheck && $tableCheck->num_rows > 0) {
                echo "<div class='info'>✓ password_reset_otps table exists</div>";
                
                // Test OTP generation
                $otp = generateOTP();
                echo "<div class='success'><strong>Test OTP Generated: $otp</strong></div>";
                
                // Check otp_log.txt file
                $logFile = __DIR__ . '/otp_log.txt';
                if (file_exists($logFile)) {
                    $logs = file_get_contents($logFile);
                    if (!empty($logs)) {
                        echo "<div class='info'><strong>OTP Log File Contents (Last 5 entries):</strong><br>";
                        $lines = explode("\n", $logs);
                        $lastLines = array_slice(array_filter($lines), -5);
                        echo "<pre>" . htmlspecialchars(implode("\n", $lastLines)) . "</pre></div>";
                    }
                }
                
            } else {
                echo "<div class='error'>✗ password_reset_otps table does NOT exist! Please run CREATE_PASSWORD_RESET_TABLE.php first</div>";
            }
        } else {
            echo "<div class='error'>✗ User with email '$email' not found in database</div>";
        }
        
        $stmt->close();
        $conn->close();
    }
    
    echo "<hr>";
}

?>

<form method="POST">
    <h3>Enter email to test:</h3>
    <input type="email" name="email" placeholder="Enter registered email" required style="width:300px;">
    <button type="submit">Test</button>
</form>

<hr>

<h3>Quick Test via cURL:</h3>
<pre>
curl -X POST http://localhost/vaxforsure/api/auth/forgot_password.php \
  -H "Content-Type: application/json" \
  -d '{"email":"YOUR_EMAIL_HERE"}'
</pre>

<hr>

<h3>Next Steps:</h3>
<ol>
    <li>Make sure password_reset_otps table exists (run CREATE_PASSWORD_RESET_TABLE.php)</li>
    <li>Check if email is in users table</li>
    <li>Test the endpoint with your email</li>
    <li>Check otp_log.txt file for generated OTP codes</li>
</ol>

</body></html>




<?php
/**
 * Email Configuration Helper
 * This helps you configure email sending for forgot password OTP
 * Access: http://localhost/vaxforsure/CONFIGURE_EMAIL.php
 */

require_once 'config.php';

header('Content-Type: text/html; charset=UTF-8');

echo "<!DOCTYPE html><html><head><title>Configure Email</title>";
echo "<style>
body{font-family:Arial;padding:20px;background:#f5f5f5;max-width:800px;margin:0 auto;}
.success{color:green;padding:15px;background:#d4edda;border:2px solid #4caf50;margin:10px 0;border-radius:5px;}
.error{color:red;padding:15px;background:#f8d7da;border:2px solid #f44336;margin:10px 0;border-radius:5px;}
.info{color:#0c5460;padding:15px;background:#d1ecf1;border:2px solid #2196f3;margin:10px 0;border-radius:5px;}
.warning{color:#856404;padding:15px;background:#fff3cd;border:2px solid #ff9800;margin:10px 0;border-radius:5px;}
h1{color:#333;border-bottom:3px solid #4DB6AC;padding-bottom:10px;}
input,textarea{width:100%;padding:10px;margin:5px 0;border:1px solid #ddd;border-radius:5px;box-sizing:border-box;}
button{background:#4DB6AC;color:white;padding:12px 24px;border:none;border-radius:5px;cursor:pointer;font-size:16px;}
button:hover{background:#00897B;}
code{background:#f4f4f4;padding:2px 6px;border-radius:3px;font-family:monospace;}
</style></head><body>";
echo "<h1>📧 Configure Email for OTP Sending</h1>";

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['test_email'])) {
    $testEmail = $_POST['test_email'];
    $testOtp = generateOTP();
    
    echo "<h2>Test Email Sending</h2>";
    
    $subject = "Test Email - VaxForSure OTP";
    $message = "
    <html>
    <body style='font-family:Arial;padding:20px;'>
        <h2 style='color:#4DB6AC;'>VaxForSure - Test Email</h2>
        <p>This is a test email to verify email configuration.</p>
        <div style='background:#f0f0f0;padding:20px;margin:20px 0;border-radius:5px;text-align:center;'>
            <h3>Test OTP Code:</h3>
            <div style='font-size:32px;font-weight:bold;color:#4DB6AC;letter-spacing:5px;'>$testOtp</div>
        </div>
        <p>If you received this email, your email configuration is working!</p>
    </body>
    </html>
    ";
    
    $result = sendEmail($testEmail, $subject, $message);
    
    if ($result) {
        echo "<div class='success'><strong>✅ Email sent successfully!</strong><br>Check your inbox at: <strong>$testEmail</strong></div>";
    } else {
        echo "<div class='error'><strong>❌ Email sending failed!</strong><br>";
        echo "This is normal for XAMPP. You need to configure SMTP. See instructions below.</div>";
    }
    
    echo "<hr>";
}

?>

<div class="info">
    <h2>📋 Email Configuration Options</h2>
    
    <h3>Option 1: Configure SMTP in config.php (Recommended)</h3>
    <p>Edit <code>config.php</code> and update the SMTP settings in the <code>sendEmail()</code> function:</p>
    <pre style="background:#f4f4f4;padding:15px;border-radius:5px;overflow-x:auto;">
$smtp_host = 'smtp.gmail.com';
$smtp_port = 587;
$smtp_username = 'your-email@gmail.com';
$smtp_password = 'your-app-password';  // Gmail App Password
$smtp_from_email = 'noreply@vaxforsure.com';
$smtp_from_name = 'VaxForSure';
    </pre>
    
    <h3>Option 2: Use Gmail App Password</h3>
    <ol>
        <li>Go to your Google Account settings</li>
        <li>Enable 2-Step Verification</li>
        <li>Go to "App passwords"</li>
        <li>Generate an app password for "Mail"</li>
        <li>Use that password in config.php (not your regular Gmail password)</li>
    </ol>
    
    <h3>Option 3: Use XAMPP Mail Configuration</h3>
    <p>Edit <code>C:\xampp\php\php.ini</code> and configure:</p>
    <pre style="background:#f4f4f4;padding:15px;border-radius:5px;">
[mail function]
SMTP = smtp.gmail.com
smtp_port = 587
sendmail_from = your-email@gmail.com
sendmail_path = "\"C:\xampp\sendmail\sendmail.exe\" -t"
    </pre>
    
    <p>Then edit <code>C:\xampp\sendmail\sendmail.ini</code>:</p>
    <pre style="background:#f4f4f4;padding:15px;border-radius:5px;">
smtp_server=smtp.gmail.com
smtp_port=587
auth_username=your-email@gmail.com
auth_password=your-app-password
    </pre>
</div>

<hr>

<h2>🧪 Test Email Sending</h2>
<form method="POST">
    <label><strong>Enter your email to test:</strong></label><br>
    <input type="email" name="test_email" placeholder="your-email@gmail.com" required style="margin-top:10px;">
    <br><br>
    <button type="submit">Send Test Email</button>
</form>

<hr>

<h2>📝 Current Configuration</h2>
<div class="info">
    <p><strong>Email Function:</strong> Using SMTP with fallback to mail()</p>
    <p><strong>SMTP Host:</strong> smtp.gmail.com (default)</p>
    <p><strong>SMTP Port:</strong> 587 (TLS)</p>
    <p><strong>Note:</strong> If SMTP credentials are not set, it will use PHP mail() function</p>
</div>

<hr>

<h2>✅ Quick Setup for Gmail</h2>
<div class="warning">
    <strong>Step-by-Step:</strong>
    <ol>
        <li>Open <code>C:\xampp\htdocs\vaxforsure\config.php</code></li>
        <li>Find the <code>sendEmail()</code> function</li>
        <li>Update these lines:
            <pre style="background:#fff;padding:10px;margin:10px 0;">
$smtp_username = 'your-email@gmail.com';
$smtp_password = 'your-gmail-app-password';
            </pre>
        </li>
        <li>Save the file</li>
        <li>Test using the form above</li>
    </ol>
</div>

</body></html>




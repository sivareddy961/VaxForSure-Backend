<?php
/**
 * Install PHPMailer via Composer
 * This will download and install PHPMailer automatically
 * Access: http://localhost/vaxforsure/INSTALL_PHPMailer.php
 */

header('Content-Type: text/html; charset=UTF-8');

echo "<!DOCTYPE html><html><head><title>Install PHPMailer</title>";
echo "<style>
body{font-family:Arial;padding:20px;background:#f5f5f5;}
.success{color:green;padding:15px;background:#d4edda;border:2px solid #4caf50;margin:10px 0;border-radius:5px;}
.error{color:red;padding:15px;background:#f8d7da;border:2px solid #f44336;margin:10px 0;border-radius:5px;}
.info{color:#0c5460;padding:15px;background:#d1ecf1;border:2px solid #2196f3;margin:10px 0;border-radius:5px;}
pre{background:#f4f4f4;padding:15px;border-radius:5px;overflow-x:auto;}
</style></head><body>";
echo "<h1>📦 Install PHPMailer</h1>";

$vendorDir = __DIR__ . '/vendor';
$phpmailerPath = $vendorDir . '/phpmailer/phpmailer/src/PHPMailer.php';

if (file_exists($phpmailerPath)) {
    echo "<div class='success'><strong>✅ PHPMailer is already installed!</strong></div>";
    echo "<div class='info'>Location: <code>$phpmailerPath</code></div>";
} else {
    echo "<div class='info'><h2>Installation Instructions:</h2>";
    echo "<p><strong>Option 1: Using Composer (Recommended)</strong></p>";
    echo "<pre>cd C:\\xampp\\htdocs\\vaxforsure
composer require phpmailer/phpmailer</pre>";
    
    echo "<p><strong>Option 2: Manual Download</strong></p>";
    echo "<ol>";
    echo "<li>Download from: <a href='https://github.com/PHPMailer/PHPMailer/releases' target='_blank'>PHPMailer Releases</a></li>";
    echo "<li>Extract to: <code>C:\\xampp\\htdocs\\vaxforsure\\PHPMailer\\</code></li>";
    echo "<li>Make sure structure is: <code>PHPMailer/src/PHPMailer.php</code></li>";
    echo "</ol>";
    
    echo "<p><strong>Option 3: Use Simple SMTP (No Installation Required)</strong></p>";
    echo "<p>The current config.php already has SMTP support. Just configure your Gmail credentials!</p>";
    echo "</div>";
}

echo "<hr>";
echo "<h2>📧 Quick Email Configuration</h2>";
echo "<div class='info'>";
echo "<p><strong>After PHPMailer is installed (or using built-in SMTP):</strong></p>";
echo "<ol>";
echo "<li>Open: <code>C:\\xampp\\htdocs\\vaxforsure\\config.php</code></li>";
echo "<li>Find <code>sendEmail()</code> function</li>";
echo "<li>Update Gmail credentials:</li>";
echo "</ol>";
echo "<pre>\$smtp_username = 'your-email@gmail.com';
\$smtp_password = 'your-gmail-app-password';</pre>";
echo "<p><strong>Get Gmail App Password:</strong> <a href='https://myaccount.google.com/apppasswords' target='_blank'>Click here</a></p>";
echo "</div>";

echo "</body></html>";

?>




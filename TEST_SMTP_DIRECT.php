<?php
/**
 * Direct SMTP Test - This will test SMTP connection directly
 */

require_once 'config.php';

header('Content-Type: text/html; charset=UTF-8');

echo "<h1>Direct SMTP Test</h1>";

// Load config
$emailConfigFile = __DIR__ . '/email_config.php';
if (!file_exists($emailConfigFile)) {
    die("email_config.php not found!");
}

include $emailConfigFile;

echo "<pre>";
echo "Testing SMTP Connection...\n";
echo "Host: $smtp_host\n";
echo "Port: $smtp_port\n";
echo "Username: $smtp_username\n";
echo "Password: " . (strlen($smtp_password) > 0 ? "SET (" . strlen($smtp_password) . " chars)" : "NOT SET") . "\n\n";

// Test connection
$smtp = @fsockopen($smtp_host, $smtp_port, $errno, $errstr, 10);

if (!$smtp) {
    echo "❌ Connection failed: $errstr ($errno)\n";
    echo "\nPossible issues:\n";
    echo "1. Firewall blocking port $smtp_port\n";
    echo "2. Internet connection issue\n";
    echo "3. Gmail SMTP server unreachable\n";
} else {
    echo "✅ Connected successfully!\n";
    stream_set_timeout($smtp, 10);
    
    // Read greeting
    $response = fgets($smtp, 515);
    echo "Server greeting: " . trim($response) . "\n";
    
    if (substr($response, 0, 3) == '220') {
        echo "✅ Server greeting OK\n";
        
        // Try EHLO
        fputs($smtp, "EHLO localhost\r\n");
        $response = fgets($smtp, 515);
        echo "EHLO response: " . trim($response) . "\n";
        
        if (substr($response, 0, 3) == '250') {
            echo "✅ EHLO OK\n";
            
            // Try STARTTLS
            fputs($smtp, "STARTTLS\r\n");
            $response = fgets($smtp, 515);
            echo "STARTTLS response: " . trim($response) . "\n";
            
            if (substr($response, 0, 3) == '220') {
                echo "✅ STARTTLS OK - Connection should work!\n";
            } else {
                echo "❌ STARTTLS failed\n";
            }
        }
    }
    
    fclose($smtp);
}

echo "\n\nNow testing actual email send...\n";
$testEmail = $smtp_username;
$testSubject = "SMTP Test - " . date('H:i:s');
$testMessage = "This is a test email sent at " . date('Y-m-d H:i:s');

$result = sendEmail($testEmail, $testSubject, $testMessage);

if ($result) {
    echo "✅ Email sent successfully! Check your inbox.\n";
} else {
    echo "❌ Email sending failed. Check error logs.\n";
}

echo "</pre>";
?>


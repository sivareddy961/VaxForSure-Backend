<?php
/**
 * Detailed SMTP Test - Shows exactly where it fails
 * Access: http://localhost:8080/vaxforsure/TEST_SMTP_DETAILED.php
 */

require_once 'config.php';
include __DIR__ . '/email_config.php';

header('Content-Type: text/html; charset=UTF-8');

echo "<!DOCTYPE html><html><head><title>Detailed SMTP Test</title>";
echo "<style>
body{font-family:monospace;padding:20px;background:#000;color:#0f0;font-size:12px;}
.success{color:#0f0;font-weight:bold;}
.error{color:#f00;font-weight:bold;}
.warning{color:#ff0;}
pre{background:#111;padding:10px;border:1px solid #333;margin:5px 0;white-space:pre-wrap;}
h1{color:#0ff;}
</style></head><body>";

echo "<h1>=== DETAILED SMTP TEST ===</h1>";

echo "<pre>";
echo "Configuration:\n";
echo "Email: $smtp_username\n";
echo "Password: " . str_repeat('*', strlen($smtp_password)) . " (length: " . strlen($smtp_password) . ")\n";
echo "Host: $smtp_host\n";
echo "Port: $smtp_port\n\n";

echo "=== Step 1: Connect to SMTP Server ===\n";
$smtp = @fsockopen($smtp_host, $smtp_port, $errno, $errstr, 10);

if (!$smtp) {
    echo "<span class='error'>❌ FAILED: $errstr ($errno)</span>\n";
    echo "Cannot connect to $smtp_host:$smtp_port\n";
    echo "Check: Firewall, Internet connection\n";
    exit;
} else {
    echo "<span class='success'>✅ Connected to $smtp_host:$smtp_port</span>\n\n";
    stream_set_timeout($smtp, 10);
}

echo "=== Step 2: Read Server Greeting ===\n";
$response = '';
$timeout = 5;
while ($timeout > 0 && ($line = @fgets($smtp, 515))) {
    $response .= $line;
    if (strlen($line) < 4 || $line[3] !== '-') break;
    $timeout--;
}

echo "Response: " . trim($response) . "\n";
if (substr(trim($response), 0, 3) == '220') {
    echo "<span class='success'>✅ Server greeting OK</span>\n\n";
} else {
    echo "<span class='error'>❌ Server greeting FAILED</span>\n";
    fclose($smtp);
    exit;
}

echo "=== Step 3: Send EHLO ===\n";
fputs($smtp, "EHLO localhost\r\n");
$response = '';
while (($line = @fgets($smtp, 515)) && strlen($line) > 0) {
    $response .= $line;
    if (strlen($line) < 4 || $line[3] !== '-') break;
}
echo "Response: " . trim($response) . "\n";
if (substr(trim($response), 0, 3) == '250') {
    echo "<span class='success'>✅ EHLO OK</span>\n\n";
} else {
    echo "<span class='error'>❌ EHLO FAILED</span>\n";
    fclose($smtp);
    exit;
}

echo "=== Step 4: Start TLS ===\n";
fputs($smtp, "STARTTLS\r\n");
$response = '';
while (($line = @fgets($smtp, 515)) && strlen($line) > 0) {
    $response .= $line;
    if (strlen($line) < 4 || $line[3] !== '-') break;
}
echo "Response: " . trim($response) . "\n";
if (substr(trim($response), 0, 3) == '220') {
    echo "<span class='success'>✅ STARTTLS OK</span>\n";
} else {
    echo "<span class='error'>❌ STARTTLS FAILED</span>\n";
    fclose($smtp);
    exit;
}

echo "\n=== Step 5: Enable TLS Encryption ===\n";
$cryptoEnabled = false;
$methods = [
    'TLSv1.2' => STREAM_CRYPTO_METHOD_TLSv1_2_CLIENT,
    'TLS Client' => STREAM_CRYPTO_METHOD_TLS_CLIENT,
    'TLSv1' => STREAM_CRYPTO_METHOD_TLSv1_CLIENT,
];

foreach ($methods as $name => $method) {
    echo "Trying $name... ";
    if (@stream_socket_enable_crypto($smtp, true, $method)) {
        echo "<span class='success'>✅ Success</span>\n";
        $cryptoEnabled = true;
        break;
    } else {
        echo "<span class='error'>Failed</span>\n";
    }
}

if (!$cryptoEnabled) {
    echo "<span class='error'>❌ TLS encryption FAILED with all methods</span>\n";
    fclose($smtp);
    exit;
}

echo "\n=== Step 6: EHLO After TLS ===\n";
fputs($smtp, "EHLO localhost\r\n");
$response = '';
while (($line = @fgets($smtp, 515)) && strlen($line) > 0) {
    $response .= $line;
    if (strlen($line) < 4 || $line[3] !== '-') break;
}
echo "Response: " . trim($response) . "\n";
echo "<span class='success'>✅ EHLO after TLS OK</span>\n\n";

echo "=== Step 7: AUTH LOGIN ===\n";
fputs($smtp, "AUTH LOGIN\r\n");
$response = '';
while (($line = @fgets($smtp, 515)) && strlen($line) > 0) {
    $response .= $line;
    if (strlen($line) < 4 || $line[3] !== '-') break;
}
echo "Response: " . trim($response) . "\n";
if (substr(trim($response), 0, 3) == '334') {
    echo "<span class='success'>✅ AUTH LOGIN OK</span>\n\n";
} else {
    echo "<span class='error'>❌ AUTH LOGIN FAILED</span>\n";
    fclose($smtp);
    exit;
}

echo "=== Step 8: Send Username ===\n";
$usernameB64 = base64_encode($smtp_username);
echo "Username (base64): $usernameB64\n";
fputs($smtp, $usernameB64 . "\r\n");
$response = '';
while (($line = @fgets($smtp, 515)) && strlen($line) > 0) {
    $response .= $line;
    if (strlen($line) < 4 || $line[3] !== '-') break;
}
echo "Response: " . trim($response) . "\n";
if (substr(trim($response), 0, 3) == '334') {
    echo "<span class='success'>✅ Username accepted</span>\n\n";
} else {
    echo "<span class='error'>❌ Username REJECTED</span>\n";
    fclose($smtp);
    exit;
}

echo "=== Step 9: Send Password ===\n";
$cleanPassword = str_replace(' ', '', trim($smtp_password));
echo "Password length: " . strlen($cleanPassword) . " characters\n";
$passwordB64 = base64_encode($cleanPassword);
echo "Password (base64): $passwordB64\n";
fputs($smtp, $passwordB64 . "\r\n");
$response = '';
while (($line = @fgets($smtp, 515)) && strlen($line) > 0) {
    $response .= $line;
    if (strlen($line) < 4 || $line[3] !== '-') break;
}
$authResponse = trim($response);
echo "Response: $authResponse\n";

if (strpos($authResponse, '235') !== false) {
    echo "<span class='success'>✅✅✅ AUTHENTICATION SUCCESSFUL! ✅✅✅</span>\n\n";
    
    echo "=== Step 10: Send Test Email ===\n";
    fputs($smtp, "MAIL FROM: <$smtp_from_email>\r\n");
    $response = '';
    while (($line = @fgets($smtp, 515)) && strlen($line) > 0) {
        $response .= $line;
        if (strlen($line) < 4 || $line[3] !== '-') break;
    }
    echo "MAIL FROM: " . trim($response) . "\n";
    
    if (substr(trim($response), 0, 3) == '250') {
        fputs($smtp, "RCPT TO: <$smtp_username>\r\n");
        $response = '';
        while (($line = @fgets($smtp, 515)) && strlen($line) > 0) {
            $response .= $line;
            if (strlen($line) < 4 || $line[3] !== '-') break;
        }
        echo "RCPT TO: " . trim($response) . "\n";
        
        if (substr(trim($response), 0, 3) == '250') {
            echo "<span class='success'>✅ Email sending should work!</span>\n";
        }
    }
    
} else {
    echo "<span class='error'>❌❌❌ AUTHENTICATION FAILED ❌❌❌</span>\n\n";
    echo "Error Response: $authResponse\n\n";
    
    if (strpos($authResponse, '535') !== false || strpos($authResponse, '5.7.8') !== false) {
        echo "<span class='error'>ERROR CODE: Invalid credentials</span>\n";
        echo "This means:\n";
        echo "1. App Password is INCORRECT\n";
        echo "2. OR 2-Step Verification is NOT enabled\n";
        echo "3. OR App Password was revoked\n\n";
        echo "SOLUTION:\n";
        echo "1. Go to: https://myaccount.google.com/apppasswords\n";
        echo "2. Make sure you're logged in as: $smtp_username\n";
        echo "3. Delete old App Password if exists\n";
        echo "4. Generate NEW App Password for 'Mail'\n";
        echo "5. Copy the NEW 16-character password\n";
        echo "6. Update email_config.php with NEW password\n";
    } else {
        echo "Unknown error code. Full response: $authResponse\n";
    }
}

fputs($smtp, "QUIT\r\n");
fclose($smtp);

echo "\n=== Test Complete ===\n";
echo "</pre>";

echo "</body></html>";
?>


<?php
/**
 * Email Diagnostics - Find exact issue
 */

require_once 'config.php';
include __DIR__ . '/email_config.php';

header('Content-Type: text/html; charset=UTF-8');

echo "<!DOCTYPE html><html><head><title>Email Diagnostics</title>";
echo "<style>
body{font-family:monospace;padding:20px;background:#000;color:#0f0;font-size:12px;}
.error{color:#f00;font-weight:bold;}
.success{color:#0f0;font-weight:bold;}
.warning{color:#ff0;}
pre{background:#111;padding:10px;border:1px solid #333;margin:5px 0;white-space:pre-wrap;}
h1{color:#0ff;}
</style></head><body>";

echo "<h1>=== EMAIL DIAGNOSTICS ===</h1>";

echo "<pre>";
echo "=== Configuration Check ===\n";
echo "Email: " . ($smtp_username ?: "NOT SET") . "\n";
echo "Password Length: " . strlen($smtp_password) . " characters\n";
echo "Host: $smtp_host\n";
echo "Port: $smtp_port\n\n";

if (empty($smtp_password)) {
    echo "<span class='error'>ERROR: App Password is not set!</span>\n";
    echo "Please update email_config.php with your 16-character App Password.\n";
    echo "</pre></body></html>";
    exit;
}

if (strlen($smtp_password) != 16) {
    echo "<span class='warning'>WARNING: Password length is " . strlen($smtp_password) . " characters. Should be 16.</span>\n\n";
}

echo "=== Testing SMTP Connection ===\n";
$smtp = @fsockopen($smtp_host, $smtp_port, $errno, $errstr, 10);

if (!$smtp) {
    echo "<span class='error'>❌ Connection FAILED</span>\n";
    echo "Error: $errstr ($errno)\n\n";
    echo "Possible issues:\n";
    echo "1. Firewall blocking port $smtp_port\n";
    echo "2. Internet connection issue\n";
    echo "3. Gmail SMTP server unreachable\n";
    echo "SOLUTION: Check Windows Firewall settings\n";
} else {
    echo "<span class='success'>✅ Connected to $smtp_host:$smtp_port</span>\n\n";
    stream_set_timeout($smtp, 10);
    
    // Read greeting
    $response = fgets($smtp, 515);
    echo "Server greeting: " . trim($response) . "\n";
    
    if (substr($response, 0, 3) == '220') {
        echo "<span class='success'>✅ Server greeting OK</span>\n\n";
        
        // EHLO
        fputs($smtp, "EHLO localhost\r\n");
        $response = fgets($smtp, 515);
        echo "EHLO response: " . trim($response) . "\n";
        
        if (substr($response, 0, 3) == '250') {
            echo "<span class='success'>✅ EHLO OK</span>\n\n";
            
            // STARTTLS
            fputs($smtp, "STARTTLS\r\n");
            $response = fgets($smtp, 515);
            echo "STARTTLS response: " . trim($response) . "\n";
            
            if (substr($response, 0, 3) == '220') {
                echo "<span class='success'>✅ STARTTLS OK</span>\n";
                
                // Enable TLS
                if (@stream_socket_enable_crypto($smtp, true, STREAM_CRYPTO_METHOD_TLSv1_2_CLIENT)) {
                    echo "<span class='success'>✅ TLS encryption enabled</span>\n\n";
                    
                    // EHLO again
                    fputs($smtp, "EHLO localhost\r\n");
                    $response = fgets($smtp, 515);
                    echo "EHLO after TLS: " . trim($response) . "\n\n";
                    
                    // AUTH LOGIN
                    fputs($smtp, "AUTH LOGIN\r\n");
                    $response = fgets($smtp, 515);
                    echo "AUTH LOGIN response: " . trim($response) . "\n";
                    
                    if (substr($response, 0, 3) == '334') {
                        // Send username
                        fputs($smtp, base64_encode($smtp_username) . "\r\n");
                        $response = fgets($smtp, 515);
                        echo "Username sent, response: " . trim($response) . "\n";
                        
                        if (substr($response, 0, 3) == '334') {
                            // Send password
                            $cleanPassword = str_replace(' ', '', trim($smtp_password));
                            fputs($smtp, base64_encode($cleanPassword) . "\r\n");
                            $response = fgets($smtp, 515);
                            $authResponse = trim($response);
                            echo "Password sent, response: $authResponse\n\n";
                            
                            if (strpos($authResponse, '235') !== false) {
                                echo "<span class='success'>✅✅✅ AUTHENTICATION SUCCESSFUL! ✅✅✅</span>\n";
                                echo "Your email configuration is CORRECT!\n";
                                echo "The issue might be in the email sending code.\n\n";
                                
                                // Test sending email
                                echo "=== Testing Email Send ===\n";
                                fputs($smtp, "MAIL FROM: <$smtp_from_email>\r\n");
                                $response = fgets($smtp, 515);
                                echo "MAIL FROM: " . trim($response) . "\n";
                                
                                if (substr($response, 0, 3) == '250') {
                                    fputs($smtp, "RCPT TO: <$smtp_username>\r\n");
                                    $response = fgets($smtp, 515);
                                    echo "RCPT TO: " . trim($response) . "\n";
                                    
                                    if (substr($response, 0, 3) == '250') {
                                        echo "<span class='success'>✅ SMTP is working perfectly!</span>\n";
                                    }
                                }
                            } else {
                                echo "<span class='error'>❌❌❌ AUTHENTICATION FAILED ❌❌❌</span>\n\n";
                                echo "Error Response: $authResponse\n\n";
                                
                                if (strpos($authResponse, '535') !== false || strpos($authResponse, '5.7.8') !== false) {
                                    echo "<span class='error'>SOLUTION:</span>\n";
                                    echo "1. App Password is INCORRECT\n";
                                    echo "2. OR 2-Step Verification is NOT enabled\n";
                                    echo "3. OR App Password was revoked/deleted\n\n";
                                    echo "FIX:\n";
                                    echo "1. Go to: https://myaccount.google.com/apppasswords\n";
                                    echo "2. Delete old App Password (if exists)\n";
                                    echo "3. Generate NEW App Password for 'Mail'\n";
                                    echo "4. Copy the NEW 16-character password\n";
                                    echo "5. Update email_config.php with NEW password\n";
                                } else {
                                    echo "Unknown error. Check Gmail account settings.\n";
                                }
                            }
                        } else {
                            echo "<span class='error'>Username rejected: " . trim($response) . "</span>\n";
                        }
                    } else {
                        echo "<span class='error'>AUTH LOGIN failed: " . trim($response) . "</span>\n";
                    }
                } else {
                    echo "<span class='error'>❌ TLS encryption failed</span>\n";
                    echo "Try different TLS method or check OpenSSL extension.\n";
                }
            } else {
                echo "<span class='error'>STARTTLS failed: " . trim($response) . "</span>\n";
            }
        } else {
            echo "<span class='error'>EHLO failed: " . trim($response) . "</span>\n";
        }
    } else {
        echo "<span class='error'>Server greeting failed: " . trim($response) . "</span>\n";
    }
    
    fclose($smtp);
}

echo "\n=== End of Diagnostics ===\n";
echo "</pre>";

echo "<div style='background:#111;padding:15px;margin-top:20px;border-radius:5px;'>";
echo "<h2 style='color:#0ff;'>Next Steps:</h2>";
echo "<ol style='color:#0f0;'>";
echo "<li>If authentication failed, generate a NEW App Password</li>";
echo "<li>Update email_config.php with the new password</li>";
echo "<li>Run this diagnostic again to verify</li>";
echo "<li>If authentication succeeds, email should work!</li>";
echo "</ol>";
echo "</div>";

echo "</body></html>";
?>


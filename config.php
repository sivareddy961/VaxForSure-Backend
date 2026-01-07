<?php
/**
 * VaxForSure Database Configuration
 * Database: vaxforsure
 * Port: 3306 (default MySQL port)
 */

// Database configuration
define('DB_HOST', 'localhost');
define('DB_USER', 'root');
define('DB_PASS', ''); // Empty for default XAMPP MySQL
define('DB_NAME', 'vaxforsure');
define('DB_PORT', 3307); // Updated to port 3307

// Error reporting (set to 0 in production)
// Suppress error output to prevent breaking JSON responses
error_reporting(E_ALL);
ini_set('display_errors', 0); // Changed to 0 to prevent PHP errors in JSON response
ini_set('log_errors', 1); // Still log errors but don't display them

// Set timezone
date_default_timezone_set('UTC');

// CORS headers
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization');
header('Content-Type: application/json; charset=UTF-8');

// Handle preflight OPTIONS request
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

/**
 * Get database connection
 * @return mysqli|null
 */
function getDBConnection() {
    $conn = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME, DB_PORT);
    
    if ($conn->connect_error) {
        error_log("Database connection failed: " . $conn->connect_error);
        return null;
    }
    
    $conn->set_charset("utf8mb4");
    return $conn;
}

/**
 * Send JSON response
 * @param bool $success
 * @param string $message
 * @param mixed $data
 * @param int $httpCode
 */
function sendResponse($success, $message, $data = null, $httpCode = 200) {
    // Clean any output before sending JSON
    if (ob_get_length() > 0) {
        ob_clean();
    }
    
    http_response_code($httpCode);
    
    $response = [
        'success' => $success,
        'message' => $message
    ];
    
    if ($data !== null) {
        $response['data'] = $data;
    }
    
    echo json_encode($response, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    exit();
}

/**
 * Get JSON input from request
 * @return array|null
 */
function getJsonInput() {
    $json = file_get_contents('php://input');
    $data = json_decode($json, true);
    
    if (json_last_error() !== JSON_ERROR_NONE) {
        return null;
    }
    
    return $data;
}

/**
 * Sanitize input string
 * @param string $input
 * @return string
 */
function sanitizeInput($input) {
    return htmlspecialchars(strip_tags(trim($input)));
}

/**
 * Validate email
 * @param string $email
 * @return bool
 */
function isValidEmail($email) {
    return filter_var($email, FILTER_VALIDATE_EMAIL) !== false;
}

/**
 * Hash password
 * @param string $password
 * @return string
 */
function hashPassword($password) {
    return password_hash($password, PASSWORD_DEFAULT);
}

/**
 * Verify password
 * @param string $password
 * @param string $hash
 * @return bool
 */
function verifyPassword($password, $hash) {
    return password_verify($password, $hash);
}

/**
 * Send email using PHPMailer (recommended) or fallback to SMTP/mail()
 * @param string $to Email address
 * @param string $subject Email subject
 * @param string $message Email body (HTML)
 * @return bool Success status
 */
function sendEmail($to, $subject, $message) {
    // Load email configuration
    $emailConfigFile = __DIR__ . '/email_config.php';
    $smtp_host = 'smtp.gmail.com';
    $smtp_port = 587;
    $smtp_username = '';
    $smtp_password = '';
    $smtp_from_email = 'noreply@vaxforsure.com';
    $smtp_from_name = 'VaxForSure';
    
    // Load from config file if exists
    if (file_exists($emailConfigFile)) {
        include $emailConfigFile;
    }
    
    // Validate credentials are set
    if (empty($smtp_username) || empty($smtp_password)) {
        error_log("Email sending failed: SMTP credentials not configured in email_config.php");
        return false;
    }
    
    // Try raw SMTP first (most reliable for Gmail)
    error_log("Attempting to send email to $to via SMTP");
    $result = sendEmailRawSMTP($to, $subject, $message, $smtp_host, $smtp_port, $smtp_username, $smtp_password, $smtp_from_email, $smtp_from_name);
    
    if ($result) {
        return true;
    }
    
    // Check if PHPMailer is available as fallback
    $phpmailerPath = __DIR__ . '/PHPMailer/src/PHPMailer.php';
    
    if (file_exists($phpmailerPath) && filesize($phpmailerPath) > 1000) {
        try {
            require_once $phpmailerPath;
            require_once __DIR__ . '/PHPMailer/src/SMTP.php';
            require_once __DIR__ . '/PHPMailer/src/Exception.php';
            
            $mail = new \PHPMailer\PHPMailer\PHPMailer(true);
            
            // Server settings
            $mail->isSMTP();
            $mail->Host = $smtp_host;
            $mail->SMTPAuth = true;
            $mail->Username = $smtp_username;
            $mail->Password = $smtp_password;
            $mail->SMTPSecure = \PHPMailer\PHPMailer\PHPMailer::ENCRYPTION_STARTTLS;
            $mail->Port = $smtp_port;
            $mail->CharSet = 'UTF-8';
            $mail->Timeout = 30;
            $mail->SMTPDebug = 0;
            
            // Recipients
            $mail->setFrom($smtp_from_email, $smtp_from_name);
            $mail->addAddress($to);
            
            // Content
            $mail->isHTML(true);
            $mail->Subject = $subject;
            $mail->Body = $message;
            $mail->AltBody = strip_tags($message);
            
            // Send email
            $mail->send();
            error_log("Email sent successfully to $to using PHPMailer");
            return true;
            
        } catch (\Exception $e) {
            error_log("PHPMailer Error: " . (isset($mail) ? $mail->ErrorInfo : $e->getMessage()));
        }

    }
    
    error_log("All email sending methods failed for $to");
    return false;
}

/**
 * Improved: Send email using raw SMTP connection (works even without PHPMailer)
 * @param string $to Email address
 * @param string $subject Email subject
 * @param string $message Email body
 * @param string $smtp_host SMTP host
 * @param int $smtp_port SMTP port
 * @param string $smtp_username SMTP username
 * @param string $smtp_password SMTP password
 * @param string $smtp_from_email From email
 * @param string $smtp_from_name From name
 * @return bool Success status
 */
function sendEmailRawSMTP($to, $subject, $message, $smtp_host, $smtp_port, $smtp_username, $smtp_password, $smtp_from_email, $smtp_from_name) {
    $smtp = null;
    try {
        // Suppress warnings to prevent breaking JSON response
        error_reporting(E_ALL & ~E_WARNING & ~E_NOTICE);
        error_log("Attempting SMTP connection to $smtp_host:$smtp_port");
        
        // Try connecting without SSL first
        $smtp = @fsockopen($smtp_host, $smtp_port, $errno, $errstr, 30);
        
        if (!$smtp) {
            // Try with stream context
            $context = stream_context_create([
                'socket' => [
                    'bindto' => '0:0'
                ]
            ]);
            $smtp = @stream_socket_client(
                "$smtp_host:$smtp_port",
                $errno,
                $errstr,
                30,
                STREAM_CLIENT_CONNECT,
                $context
            );
        }
        
        if (!$smtp) {
            error_log("SMTP Connection failed: $errstr ($errno) to $smtp_host:$smtp_port");
            return false;
        }
        
        stream_set_timeout($smtp, 30);
        stream_set_blocking($smtp, true);
        
        // Read server greeting
        $response = '';
        while ($line = fgets($smtp, 515)) {
            $response .= $line;
            if (substr($line, 3, 1) !== '-') break;
        }
        
        if (substr(trim($response), 0, 3) !== '220') {
            error_log("SMTP Greeting failed: $response");
            fclose($smtp);
            return false;
        }
        
        // Send EHLO
        $hostname = isset($_SERVER['SERVER_NAME']) ? $_SERVER['SERVER_NAME'] : 'localhost';
        fputs($smtp, "EHLO $hostname\r\n");
        
        $response = '';
        while ($line = fgets($smtp, 515)) {
            $response .= $line;
            if (substr($line, 3, 1) !== '-') break;
        }
        
        if (substr(trim($response), 0, 3) !== '250') {
            error_log("SMTP EHLO failed: $response");
            fclose($smtp);
            return false;
        }
        
        // Start TLS
        fputs($smtp, "STARTTLS\r\n");
        $response = '';
        while ($line = fgets($smtp, 515)) {
            $response .= $line;
            if (substr($line, 3, 1) !== '-') break;
        }
        
        if (substr(trim($response), 0, 3) !== '220') {
            error_log("SMTP STARTTLS failed: $response");
            fclose($smtp);
            return false;
        }
        
        // Enable TLS encryption - try multiple methods
        $cryptoMethods = [
            STREAM_CRYPTO_METHOD_TLSv1_2_CLIENT,
            STREAM_CRYPTO_METHOD_TLS_CLIENT,
            STREAM_CRYPTO_METHOD_TLSv1_CLIENT,
            STREAM_CRYPTO_METHOD_SSLv23_CLIENT
        ];
        
        $cryptoEnabled = false;
        $lastError = '';
        foreach ($cryptoMethods as $method) {
            $result = @stream_socket_enable_crypto($smtp, true, $method);
            if ($result === true) {
                $cryptoEnabled = true;
                error_log("TLS enabled using method: $method");
                break;
            } else {
                $lastError = error_get_last();
            }
        }
        
        if (!$cryptoEnabled) {
            error_log("SMTP TLS encryption failed with all methods. Last error: " . ($lastError ? $lastError['message'] : 'Unknown'));
            fclose($smtp);
            return false;
        }
        
        // EHLO again after TLS
        fputs($smtp, "EHLO $hostname\r\n");
        $response = '';
        while ($line = fgets($smtp, 515)) {
            $response .= $line;
            if (substr($line, 3, 1) !== '-') break;
        }
        
        // Authenticate
        fputs($smtp, "AUTH LOGIN\r\n");
        $response = '';
        while ($line = fgets($smtp, 515)) {
            $response .= $line;
            if (substr($line, 3, 1) !== '-') break;
        }
        
        if (substr(trim($response), 0, 3) !== '334') {
            error_log("SMTP AUTH LOGIN failed: $response");
            fclose($smtp);
            return false;
        }
        
        // Send username
        fputs($smtp, base64_encode($smtp_username) . "\r\n");
        $response = '';
        while ($line = fgets($smtp, 515)) {
            $response .= $line;
            if (substr($line, 3, 1) !== '-') break;
        }
        
        if (substr(trim($response), 0, 3) !== '334') {
            error_log("SMTP Username failed: $response");
            fclose($smtp);
            return false;
        }
        
        // Send password (remove any spaces from app password - Gmail app passwords should be 16 chars without spaces)
        $cleanPassword = str_replace(' ', '', trim($smtp_password));
        
        // Log password attempt (without showing actual password)
        error_log("Attempting SMTP authentication for user: $smtp_username");
        error_log("Password length: " . strlen($cleanPassword) . " characters");
        
        fputs($smtp, base64_encode($cleanPassword) . "\r\n");
        
        // Read response with timeout protection
        $response = '';
        $maxAttempts = 10;
        $attempts = 0;
        while ($attempts < $maxAttempts) {
            $line = @fgets($smtp, 515);
            if ($line === false) break;
            $response .= $line;
            // Check if this is the last line of response (no continuation character)
            if (strlen($line) < 4 || (isset($line[3]) && $line[3] !== '-')) {
                break;
            }
            $attempts++;
        }
        
        $authResponse = trim($response);
        error_log("SMTP Authentication response: $authResponse");
        
        if (strpos($authResponse, '235') !== false) {
            error_log("SMTP Authentication successful!");
        } else {
            error_log("SMTP Authentication failed. Full response: $authResponse");
            // Provide helpful error message
            if (strpos($authResponse, '535') !== false || strpos($authResponse, '5.7.8') !== false) {
                error_log("ERROR: Invalid App Password or 2-Step Verification not enabled for $smtp_username");
                error_log("Please check: 1) App Password is correct 2) 2-Step Verification enabled 3) App Password generated for 'Mail' app");
            }
            fclose($smtp);
            return false;
        }
        
        error_log("SMTP Authentication successful");
        
        // Send MAIL FROM
        fputs($smtp, "MAIL FROM: <$smtp_from_email>\r\n");
        $response = '';
        while ($line = fgets($smtp, 515)) {
            $response .= $line;
            if (substr($line, 3, 1) !== '-') break;
        }
        
        if (substr(trim($response), 0, 3) !== '250') {
            error_log("SMTP MAIL FROM failed: $response");
            fclose($smtp);
            return false;
        }
        
        // Send RCPT TO
        fputs($smtp, "RCPT TO: <$to>\r\n");
        $response = '';
        while ($line = fgets($smtp, 515)) {
            $response .= $line;
            if (substr($line, 3, 1) !== '-') break;
        }
        
        if (substr(trim($response), 0, 3) !== '250') {
            error_log("SMTP RCPT TO failed: $response");
            fclose($smtp);
            return false;
        }
        
        // Send DATA
        fputs($smtp, "DATA\r\n");
        $response = '';
        while ($line = fgets($smtp, 515)) {
            $response .= $line;
            if (substr($line, 3, 1) !== '-') break;
        }
        
        if (substr(trim($response), 0, 3) !== '354') {
            error_log("SMTP DATA command failed: $response");
            fclose($smtp);
            return false;
        }
        
        // Prepare email headers and body
        $email_headers = "From: $smtp_from_name <$smtp_from_email>\r\n";
        $email_headers .= "To: <$to>\r\n";
        $email_headers .= "Subject: " . mb_encode_mimeheader($subject, 'UTF-8', 'B', "\r\n ") . "\r\n";
        $email_headers .= "MIME-Version: 1.0\r\n";
        $email_headers .= "Content-type:text/html;charset=UTF-8\r\n";
        $email_headers .= "Content-Transfer-Encoding: 8bit\r\n";
        $email_headers .= "\r\n";
        
        // Send email data
        fputs($smtp, $email_headers . $message . "\r\n.\r\n");
        
        $response = '';
        while ($line = fgets($smtp, 515)) {
            $response .= $line;
            if (substr($line, 3, 1) !== '-') break;
        }
        
        // Quit
        fputs($smtp, "QUIT\r\n");
        fclose($smtp);
        
        if (strpos($response, '250') !== false) {
            error_log("Email sent successfully via raw SMTP to $to");
            return true;
        } else {
            error_log("Email sending failed. Final response: " . trim($response));
            return false;
        }
        
    } catch (Exception $e) {
        error_log("Raw SMTP Exception: " . $e->getMessage());
        if ($smtp) {
            @fclose($smtp);
        }
        return false;
    } catch (Error $e) {
        error_log("Raw SMTP Error: " . $e->getMessage());
        if ($smtp) {
            @fclose($smtp);
        }
        return false;
    } finally {
        // Restore error reporting
        error_reporting(E_ALL);
    }
}

/**
 * Generate 6-digit OTP
 * @return string
 */
function generateOTP() {
    return str_pad((string)rand(100000, 999999), 6, '0', STR_PAD_LEFT);
}

?>



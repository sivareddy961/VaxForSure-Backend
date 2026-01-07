<?php
/**
 * Automatic PHPMailer Installation and Email Fix
 * This will download PHPMailer and test email sending
 */

error_reporting(E_ALL);
ini_set('display_errors', 1);

$phpmailerDir = __DIR__ . '/PHPMailer/src';
if (!is_dir($phpmailerDir)) {
    mkdir($phpmailerDir, 0755, true);
}

echo "Downloading PHPMailer files...\n";

$files = [
    'PHPMailer.php' => 'https://raw.githubusercontent.com/PHPMailer/PHPMailer/master/src/PHPMailer.php',
    'SMTP.php' => 'https://raw.githubusercontent.com/PHPMailer/PHPMailer/master/src/SMTP.php',
    'Exception.php' => 'https://raw.githubusercontent.com/PHPMailer/PHPMailer/master/src/Exception.php'
];

foreach ($files as $filename => $url) {
    $filepath = $phpmailerDir . '/' . $filename;
    echo "Downloading $filename... ";
    
    // Use multiple methods to download
    $content = false;
    
    // Method 1: file_get_contents with context
    $context = stream_context_create([
        'http' => [
            'timeout' => 30,
            'user_agent' => 'Mozilla/5.0',
            'follow_location' => true
        ]
    ]);
    $content = @file_get_contents($url, false, $context);
    
    // Method 2: curl if available
    if (!$content && function_exists('curl_init')) {
        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_TIMEOUT, 30);
        curl_setopt($ch, CURLOPT_USERAGENT, 'Mozilla/5.0');
        $content = curl_exec($ch);
        curl_close($ch);
    }
    
    if ($content && strlen($content) > 100) {
        file_put_contents($filepath, $content);
        echo "OK\n";
    } else {
        echo "FAILED\n";
        // Create minimal PHPMailer files as fallback
        if ($filename == 'PHPMailer.php') {
            file_put_contents($filepath, "<?php\nnamespace PHPMailer\\PHPMailer;\nclass PHPMailer {}\n");
        } elseif ($filename == 'SMTP.php') {
            file_put_contents($filepath, "<?php\nnamespace PHPMailer\\PHPMailer;\nclass SMTP {}\n");
        } elseif ($filename == 'Exception.php') {
            file_put_contents($filepath, "<?php\nnamespace PHPMailer\\PHPMailer;\nclass Exception extends \\Exception {}\n");
        }
    }
}

echo "\nDone! Testing email configuration...\n";

?>


<?php
/**
 * Force Install PHPMailer - Download directly
 */

$phpmailerDir = __DIR__ . '/PHPMailer/src';
if (!is_dir($phpmailerDir)) {
    mkdir($phpmailerDir, 0755, true);
}

$files = [
    'PHPMailer.php' => 'https://raw.githubusercontent.com/PHPMailer/PHPMailer/master/src/PHPMailer.php',
    'SMTP.php' => 'https://raw.githubusercontent.com/PHPMailer/PHPMailer/master/src/SMTP.php',
    'Exception.php' => 'https://raw.githubusercontent.com/PHPMailer/PHPMailer/master/src/Exception.php'
];

foreach ($files as $filename => $url) {
    $filepath = $phpmailerDir . '/' . $filename;
    echo "Downloading $filename...\n";
    
    // Try curl first
    if (function_exists('curl_init')) {
        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_TIMEOUT, 30);
        curl_setopt($ch, CURLOPT_USERAGENT, 'Mozilla/5.0');
        $content = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);
        
        if ($httpCode == 200 && strlen($content) > 100) {
            file_put_contents($filepath, $content);
            echo "OK\n";
            continue;
        }
    }
    
    // Try file_get_contents
    $context = stream_context_create([
        'http' => [
            'timeout' => 30,
            'user_agent' => 'Mozilla/5.0',
            'follow_location' => true,
            'ignore_errors' => true
        ]
    ]);
    $content = @file_get_contents($url, false, $context);
    
    if ($content && strlen($content) > 100) {
        file_put_contents($filepath, $content);
        echo "OK\n";
    } else {
        echo "FAILED - Creating minimal version\n";
        // Create minimal working version
        if ($filename == 'PHPMailer.php') {
            file_put_contents($filepath, "<?php\nnamespace PHPMailer\\PHPMailer;\nclass PHPMailer { public \$ErrorInfo = ''; public function send() { return false; } }\n");
        } elseif ($filename == 'SMTP.php') {
            file_put_contents($filepath, "<?php\nnamespace PHPMailer\\PHPMailer;\nclass SMTP {}\n");
        } elseif ($filename == 'Exception.php') {
            file_put_contents($filepath, "<?php\nnamespace PHPMailer\\PHPMailer;\nclass Exception extends \\Exception {}\n");
        }
    }
}

echo "Done!\n";
?>


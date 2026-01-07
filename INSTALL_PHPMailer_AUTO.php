<?php
/**
 * Automatic PHPMailer Installer
 * Downloads and installs PHPMailer automatically
 * Access: http://localhost/vaxforsure/INSTALL_PHPMailer_AUTO.php
 */

header('Content-Type: text/html; charset=UTF-8');

echo "<!DOCTYPE html><html><head><title>Install PHPMailer</title>";
echo "<style>
body{font-family:Arial;padding:20px;background:#f5f5f5;max-width:900px;margin:0 auto;}
.success{color:green;padding:15px;background:#d4edda;border:2px solid #4caf50;margin:10px 0;border-radius:5px;}
.error{color:red;padding:15px;background:#f8d7da;border:2px solid #f44336;margin:10px 0;border-radius:5px;}
.info{color:#0c5460;padding:15px;background:#d1ecf1;border:2px solid #2196f3;margin:10px 0;border-radius:5px;}
pre{background:#f4f4f4;padding:15px;border-radius:5px;overflow-x:auto;}
code{background:#f4f4f4;padding:2px 6px;border-radius:3px;}
</style></head><body>";
echo "<h1>📦 Automatic PHPMailer Installation</h1>";

$phpmailerDir = __DIR__ . '/PHPMailer';
$srcDir = $phpmailerDir . '/src';
$phpmailerFile = $srcDir . '/PHPMailer.php';
$smtpFile = $srcDir . '/SMTP.php';
$exceptionFile = $srcDir . '/Exception.php';

// Check if already installed
if (file_exists($phpmailerFile) && file_exists($smtpFile) && file_exists($exceptionFile)) {
    echo "<div class='success'><strong>✅ PHPMailer is already installed!</strong></div>";
    echo "<div class='info'>Location: <code>$phpmailerDir</code></div>";
    echo "<p><a href='QUICK_EMAIL_SETUP.php'><button style='padding:10px 20px;background:#4DB6AC;color:white;border:none;border-radius:5px;cursor:pointer;'>Configure Email Settings</button></a></p>";
    exit;
}

// Create directories
if (!is_dir($phpmailerDir)) {
    mkdir($phpmailerDir, 0755, true);
}
if (!is_dir($srcDir)) {
    mkdir($srcDir, 0755, true);
}

echo "<div class='info'><strong>📥 Downloading PHPMailer...</strong></div>";

// PHPMailer files (using latest stable version)
$files = [
    'PHPMailer.php' => 'https://raw.githubusercontent.com/PHPMailer/PHPMailer/master/src/PHPMailer.php',
    'SMTP.php' => 'https://raw.githubusercontent.com/PHPMailer/PHPMailer/master/src/SMTP.php',
    'Exception.php' => 'https://raw.githubusercontent.com/PHPMailer/PHPMailer/master/src/Exception.php'
];

$successCount = 0;
$errorCount = 0;

foreach ($files as $filename => $url) {
    $filepath = $srcDir . '/' . $filename;
    
    echo "<p>Downloading <code>$filename</code>... ";
    
    // Use file_get_contents with context for HTTPS
    $context = stream_context_create([
        'http' => [
            'method' => 'GET',
            'header' => "User-Agent: PHP\r\n",
            'timeout' => 30
        ]
    ]);
    
    $content = @file_get_contents($url, false, $context);
    
    if ($content !== false && strlen($content) > 100) {
        if (file_put_contents($filepath, $content)) {
            echo "<strong style='color:green;'>✅ Success</strong></p>";
            $successCount++;
        } else {
            echo "<strong style='color:red;'>❌ Failed to save</strong></p>";
            $errorCount++;
        }
    } else {
        // Fallback: try curl if available
        if (function_exists('curl_init')) {
            $ch = curl_init($url);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
            curl_setopt($ch, CURLOPT_USERAGENT, 'PHP');
            curl_setopt($ch, CURLOPT_TIMEOUT, 30);
            $content = curl_exec($ch);
            $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            curl_close($ch);
            
            if ($httpCode == 200 && strlen($content) > 100) {
                if (file_put_contents($filepath, $content)) {
                    echo "<strong style='color:green;'>✅ Success (via cURL)</strong></p>";
                    $successCount++;
                } else {
                    echo "<strong style='color:red;'>❌ Failed to save</strong></p>";
                    $errorCount++;
                }
            } else {
                echo "<strong style='color:red;'>❌ Download failed (HTTP $httpCode)</strong></p>";
                $errorCount++;
            }
        } else {
            echo "<strong style='color:red;'>❌ Download failed</strong></p>";
            $errorCount++;
        }
    }
}

if ($successCount === 3) {
    echo "<div class='success'><strong>✅ PHPMailer installed successfully!</strong></div>";
    echo "<div class='info'>";
    echo "<p><strong>Next Steps:</strong></p>";
    echo "<ol>";
    echo "<li>Configure your email settings: <a href='QUICK_EMAIL_SETUP.php'>Click here</a></li>";
    echo "<li>Or manually edit <code>email_config.php</code></li>";
    echo "</ol>";
    echo "</div>";
    echo "<p><a href='QUICK_EMAIL_SETUP.php'><button style='padding:10px 20px;background:#4DB6AC;color:white;border:none;border-radius:5px;cursor:pointer;font-size:16px;'>Configure Email Now</button></a></p>";
} else {
    echo "<div class='error'><strong>❌ Installation incomplete!</strong></div>";
    echo "<div class='info'>";
    echo "<p><strong>Manual Installation:</strong></p>";
    echo "<ol>";
    echo "<li>Download PHPMailer from: <a href='https://github.com/PHPMailer/PHPMailer/releases' target='_blank'>GitHub Releases</a></li>";
    echo "<li>Extract and copy the <code>src</code> folder to: <code>$phpmailerDir/src</code></li>";
    echo "<li>Make sure these files exist:";
    echo "<ul>";
    echo "<li><code>$phpmailerFile</code></li>";
    echo "<li><code>$smtpFile</code></li>";
    echo "<li><code>$exceptionFile</code></li>";
    echo "</ul></li>";
    echo "</ol>";
    echo "</div>";
}

echo "</body></html>";
?>


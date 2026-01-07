<?php
/**
 * Create Password Reset OTP Table
 * Access: http://localhost/vaxforsure/CREATE_PASSWORD_RESET_TABLE.php
 */

require_once 'config.php';

header('Content-Type: text/html; charset=UTF-8');

echo "<!DOCTYPE html><html><head><title>Create Password Reset Table</title>";
echo "<style>body{font-family:Arial;padding:20px;background:#f5f5f5;}";
echo ".success{color:green;padding:10px;background:#d4edda;border:1px solid #c3e6cb;margin:10px 0;border-radius:5px;}";
echo ".error{color:red;padding:10px;background:#f8d7da;border:1px solid #f5c6cb;margin:10px 0;border-radius:5px;}";
echo ".info{color:#0c5460;padding:10px;background:#d1ecf1;border:1px solid #bee5eb;margin:10px 0;border-radius:5px;}";
echo "h1{color:#333;}</style></head><body>";
echo "<h1>Create Password Reset OTP Table</h1>";

$conn = getDBConnection();

if (!$conn) {
    die("<div class='error'><strong>Connection Failed:</strong> Could not connect to database</div></body></html>");
}

echo "<div class='info'><strong>✓ Connected to database successfully</strong></div>";

try {
    $createTable = "CREATE TABLE IF NOT EXISTS `password_reset_otps` (
        `id` int(11) NOT NULL AUTO_INCREMENT,
        `email` VARCHAR(255) NOT NULL,
        `otp_code` VARCHAR(6) NOT NULL,
        `expires_at` DATETIME NOT NULL,
        `used` tinyint(1) DEFAULT 0,
        `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
        PRIMARY KEY (`id`),
        KEY `email` (`email`),
        KEY `otp_code` (`otp_code`),
        KEY `expires_at` (`expires_at`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci";
    
    if ($conn->query($createTable)) {
        echo "<div class='success'><strong>✓ Password reset OTP table created successfully!</strong></div>";
    } else {
        echo "<div class='error'><strong>✗ Error creating table:</strong> " . htmlspecialchars($conn->error) . "</div>";
    }
    
    // Verify table structure
    $result = $conn->query("DESCRIBE `password_reset_otps`");
    if ($result && $result->num_rows > 0) {
        echo "<div class='info'><strong>Table Structure:</strong><br>";
        echo "<table border='1' cellpadding='10' style='border-collapse:collapse;margin-top:10px;'>";
        echo "<tr><th>Field</th><th>Type</th><th>Null</th><th>Key</th><th>Default</th></tr>";
        while ($row = $result->fetch_assoc()) {
            echo "<tr><td>" . htmlspecialchars($row['Field']) . "</td>";
            echo "<td>" . htmlspecialchars($row['Type']) . "</td>";
            echo "<td>" . htmlspecialchars($row['Null']) . "</td>";
            echo "<td>" . htmlspecialchars($row['Key']) . "</td>";
            echo "<td>" . htmlspecialchars($row['Default'] ?? 'NULL') . "</td></tr>";
        }
        echo "</table></div>";
    }
    
} catch (Exception $e) {
    echo "<div class='error'><strong>Exception:</strong> " . htmlspecialchars($e->getMessage()) . "</div>";
} finally {
    $conn->close();
    echo "<div class='info'><strong>Database connection closed.</strong></div>";
    echo "</body></html>";
}

?>




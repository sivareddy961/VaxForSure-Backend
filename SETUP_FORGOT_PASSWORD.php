<?php
/**
 * Complete Setup for Forgot Password OTP System
 * This will create the password_reset_otps table and verify everything
 * Access: http://localhost/vaxforsure/SETUP_FORGOT_PASSWORD.php
 */

require_once 'config.php';

header('Content-Type: text/html; charset=UTF-8');

echo "<!DOCTYPE html><html><head><title>Setup Forgot Password</title>";
echo "<style>
body{font-family:Arial;padding:20px;background:#f5f5f5;}
.success{color:green;padding:15px;background:#d4edda;border:2px solid #4caf50;margin:10px 0;border-radius:5px;}
.error{color:red;padding:15px;background:#f8d7da;border:2px solid #f44336;margin:10px 0;border-radius:5px;}
.info{color:#0c5460;padding:15px;background:#d1ecf1;border:2px solid #2196f3;margin:10px 0;border-radius:5px;}
.warning{color:#856404;padding:15px;background:#fff3cd;border:2px solid #ff9800;margin:10px 0;border-radius:5px;}
h1{color:#333;border-bottom:3px solid #4DB6AC;padding-bottom:10px;}
h2{color:#666;margin-top:20px;}
table{border-collapse:collapse;width:100%;background:white;margin:10px 0;}
th{background:#4DB6AC;color:white;padding:12px;text-align:left;}
td{padding:10px;border:1px solid #ddd;}
tr:nth-child(even){background:#f9f9f9;}
code{background:#f4f4f4;padding:2px 6px;border-radius:3px;font-family:monospace;}
</style></head><body>";
echo "<h1>🔐 Forgot Password OTP System - Complete Setup</h1>";

$conn = getDBConnection();

if (!$conn) {
    die("<div class='error'><strong>❌ Database Connection Failed!</strong><br>Could not connect to database. Check your config.php settings.</div></body></html>");
}

echo "<div class='success'><strong>✅ Connected to database successfully!</strong><br>Database: vaxforsure | Port: 3307</div>";

$errors = [];
$success = [];
$warnings = [];

try {
    // Step 1: Check if password_reset_otps table exists
    echo "<h2>Step 1: Checking password_reset_otps Table</h2>";
    
    $tableCheck = $conn->query("SHOW TABLES LIKE 'password_reset_otps'");
    
    if ($tableCheck && $tableCheck->num_rows > 0) {
        echo "<div class='info'>✅ Table 'password_reset_otps' already exists</div>";
        
        // Check table structure
        $descResult = $conn->query("DESCRIBE password_reset_otps");
        if ($descResult) {
            echo "<table><tr><th>Field</th><th>Type</th><th>Null</th><th>Key</th><th>Default</th></tr>";
            while ($row = $descResult->fetch_assoc()) {
                echo "<tr>";
                echo "<td><strong>" . htmlspecialchars($row['Field']) . "</strong></td>";
                echo "<td>" . htmlspecialchars($row['Type']) . "</td>";
                echo "<td>" . htmlspecialchars($row['Null']) . "</td>";
                echo "<td>" . htmlspecialchars($row['Key']) . "</td>";
                echo "<td>" . htmlspecialchars($row['Default'] ?? 'NULL') . "</td>";
                echo "</tr>";
            }
            echo "</table>";
        }
    } else {
        echo "<div class='warning'>⚠️ Table 'password_reset_otps' does not exist. Creating it now...</div>";
        
        // Create the table
        $createTable = "CREATE TABLE `password_reset_otps` (
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
            $success[] = "✅ Created 'password_reset_otps' table successfully";
            echo "<div class='success'><strong>✅ Table created successfully!</strong></div>";
        } else {
            $errors[] = "❌ Failed to create table: " . $conn->error;
            echo "<div class='error'><strong>❌ Failed to create table:</strong> " . htmlspecialchars($conn->error) . "</div>";
        }
    }
    
    // Step 2: Check users table
    echo "<h2>Step 2: Verifying Users Table</h2>";
    $usersCheck = $conn->query("SHOW TABLES LIKE 'users'");
    if ($usersCheck && $usersCheck->num_rows > 0) {
        $userCount = $conn->query("SELECT COUNT(*) as count FROM users")->fetch_assoc()['count'];
        echo "<div class='info'>✅ Users table exists with <strong>$userCount</strong> registered user(s)</div>";
        
        // Show sample users (without passwords)
        $usersResult = $conn->query("SELECT id, email, full_name, created_at FROM users LIMIT 5");
        if ($usersResult && $usersResult->num_rows > 0) {
            echo "<table><tr><th>ID</th><th>Email</th><th>Full Name</th><th>Created At</th></tr>";
            while ($row = $usersResult->fetch_assoc()) {
                echo "<tr>";
                echo "<td>" . htmlspecialchars($row['id']) . "</td>";
                echo "<td>" . htmlspecialchars($row['email']) . "</td>";
                echo "<td>" . htmlspecialchars($row['full_name']) . "</td>";
                echo "<td>" . htmlspecialchars($row['created_at']) . "</td>";
                echo "</tr>";
            }
            echo "</table>";
        }
    } else {
        $errors[] = "❌ Users table does not exist";
        echo "<div class='error'><strong>❌ Users table does not exist!</strong> Please create it first.</div>";
    }
    
    // Step 3: Test OTP generation
    echo "<h2>Step 3: Testing OTP Generation</h2>";
    $testOtp = generateOTP();
    echo "<div class='info'>✅ OTP Generation Test: <code>$testOtp</code> (6 digits)</div>";
    
    // Step 4: Check log file directory
    echo "<h2>Step 4: Checking OTP Log File</h2>";
    $logFile = __DIR__ . '/otp_log.txt';
    $logDir = dirname($logFile);
    
    if (is_writable($logDir)) {
        echo "<div class='success'>✅ Log directory is writable: <code>$logDir</code></div>";
        
        if (file_exists($logFile)) {
            $logSize = filesize($logFile);
            $logLines = count(file($logFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES));
            echo "<div class='info'>✅ Log file exists: <code>otp_log.txt</code> ($logLines entries, " . round($logSize/1024, 2) . " KB)</div>";
            
            // Show last 5 entries
            $logs = file($logFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
            if (!empty($logs)) {
                echo "<div class='info'><strong>Last 5 OTP entries:</strong><br><pre>";
                $lastLogs = array_slice($logs, -5);
                echo htmlspecialchars(implode("\n", $lastLogs));
                echo "</pre></div>";
            }
        } else {
            echo "<div class='warning'>⚠️ Log file doesn't exist yet. It will be created automatically when OTP is generated.</div>";
        }
    } else {
        $warnings[] = "⚠️ Log directory is not writable: $logDir";
        echo "<div class='warning'><strong>⚠️ Warning:</strong> Log directory is not writable. OTP logging may fail.</div>";
    }
    
    // Step 5: Test API endpoint
    echo "<h2>Step 5: API Endpoint Status</h2>";
    $endpointPath = __DIR__ . '/api/auth/forgot_password.php';
    if (file_exists($endpointPath)) {
        echo "<div class='success'>✅ Forgot Password API endpoint exists: <code>api/auth/forgot_password.php</code></div>";
        
        // Check file permissions
        if (is_readable($endpointPath)) {
            echo "<div class='info'>✅ Endpoint file is readable</div>";
        } else {
            $warnings[] = "⚠️ Endpoint file is not readable";
            echo "<div class='warning'>⚠️ Endpoint file is not readable</div>";
        }
    } else {
        $errors[] = "❌ Forgot Password API endpoint not found";
        echo "<div class='error'><strong>❌ Forgot Password API endpoint not found!</strong></div>";
    }
    
    // Step 6: Summary
    echo "<h2>📋 Setup Summary</h2>";
    
    if (empty($errors)) {
        echo "<div class='success'><strong>✅ Setup Complete!</strong><br>";
        echo "All required components are in place for the forgot password OTP system.</div>";
    } else {
        echo "<div class='error'><strong>❌ Setup Issues Found:</strong><ul>";
        foreach ($errors as $error) {
            echo "<li>" . htmlspecialchars($error) . "</li>";
        }
        echo "</ul></div>";
    }
    
    if (!empty($warnings)) {
        echo "<div class='warning'><strong>⚠️ Warnings:</strong><ul>";
        foreach ($warnings as $warning) {
            echo "<li>" . htmlspecialchars($warning) . "</li>";
        }
        echo "</ul></div>";
    }
    
    if (!empty($success)) {
        echo "<div class='info'><strong>✅ Success Messages:</strong><ul>";
        foreach ($success as $msg) {
            echo "<li>" . htmlspecialchars($msg) . "</li>";
        }
        echo "</ul></div>";
    }
    
    // Step 7: Testing Instructions
    echo "<h2>🧪 How to Test</h2>";
    echo "<div class='info'>";
    echo "<strong>1. From Android App:</strong><br>";
    echo "   - Click 'Forgot Password'<br>";
    echo "   - Enter your registered email<br>";
    echo "   - Click 'Send Verification Code'<br><br>";
    
    echo "<strong>2. Check OTP:</strong><br>";
    echo "   - Open file: <code>C:\\xampp\\htdocs\\vaxforsure\\otp_log.txt</code><br>";
    echo "   - OR check the Toast message in the app (development mode shows OTP)<br><br>";
    
    echo "<strong>3. Verify OTP:</strong><br>";
    echo "   - Enter the 6-digit OTP code<br>";
    echo "   - Set your new password<br><br>";
    
    echo "<strong>4. Login:</strong><br>";
    echo "   - Use your email and new password to login";
    echo "</div>";
    
} catch (Exception $e) {
    echo "<div class='error'><strong>❌ Exception:</strong> " . htmlspecialchars($e->getMessage()) . "</div>";
} finally {
    $conn->close();
    echo "<div class='info'><strong>Database connection closed.</strong></div>";
    echo "</body></html>";
}

?>




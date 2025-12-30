<?php
/**
 * Test Database Connection
 * Access: http://localhost:8080/vaxforsure/test_db.php
 */

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');

// Database configuration
$host = 'localhost';
$user = 'root';
$pass = ''; // Empty for default XAMPP
$db = 'vaxforsure';
$port = 3307; // Updated to port 3307

echo json_encode([
    'test' => 'Database Connection Test',
    'timestamp' => date('Y-m-d H:i:s')
], JSON_PRETTY_PRINT);

echo "\n\n";

// Test 1: Check if MySQL extension is loaded
echo "Test 1: MySQL Extension\n";
if (extension_loaded('mysqli')) {
    echo "✅ mysqli extension is loaded\n";
} else {
    echo "❌ mysqli extension is NOT loaded\n";
    exit;
}

echo "\n";

// Test 2: Try to connect to MySQL
echo "Test 2: MySQL Connection\n";
$conn = @new mysqli($host, $user, $pass, '', $port);

if ($conn->connect_error) {
    echo "❌ Connection failed: " . $conn->connect_error . "\n";
    echo "\n";
    echo "SOLUTIONS:\n";
    echo "1. Start MySQL in XAMPP Control Panel\n";
    echo "2. Check MySQL is GREEN (Running)\n";
    echo "3. Verify port 3306 is not in use by another service\n";
} else {
    echo "✅ Connected to MySQL successfully!\n";
    echo "Server: " . $conn->server_info . "\n";
    $conn->close();
}

echo "\n";

// Test 3: Check if database exists
echo "Test 3: Database Check\n";
$conn = @new mysqli($host, $user, $pass, '', $port);

if (!$conn->connect_error) {
    $result = $conn->query("SHOW DATABASES LIKE '$db'");
    if ($result && $result->num_rows > 0) {
        echo "✅ Database '$db' exists\n";
        
        // Test 4: Connect to database
        echo "\nTest 4: Connect to Database\n";
        $dbConn = @new mysqli($host, $user, $pass, $db, $port);
        
        if ($dbConn->connect_error) {
            echo "❌ Cannot connect to database: " . $dbConn->connect_error . "\n";
        } else {
            echo "✅ Connected to database '$db' successfully!\n";
            
            // Test 5: Check if users table exists
            echo "\nTest 5: Users Table Check\n";
            $tableCheck = $dbConn->query("SHOW TABLES LIKE 'users'");
            if ($tableCheck && $tableCheck->num_rows > 0) {
                echo "✅ Table 'users' exists\n";
                
                // Count users
                $countResult = $dbConn->query("SELECT COUNT(*) as count FROM users");
                if ($countResult) {
                    $row = $countResult->fetch_assoc();
                    echo "   Total users: " . $row['count'] . "\n";
                }
            } else {
                echo "❌ Table 'users' does NOT exist\n";
                echo "\nSOLUTION: Import database.sql file in phpMyAdmin\n";
            }
            
            $dbConn->close();
        }
    } else {
        echo "❌ Database '$db' does NOT exist\n";
        echo "\nSOLUTION: Create database 'vaxforsure' in phpMyAdmin\n";
    }
    
    $conn->close();
}

?>


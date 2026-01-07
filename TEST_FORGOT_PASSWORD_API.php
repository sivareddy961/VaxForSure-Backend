<?php
/**
 * Test Forgot Password API
 * This will test the forgot_password.php endpoint directly
 */

header('Content-Type: text/html; charset=UTF-8');

echo "<h1>Test Forgot Password API</h1>";

$url = 'http://localhost:8080/vaxforsure/api/auth/forgot_password.php';
$data = json_encode(['email' => 'budigiveerasivareddy@gmail.com']);

$ch = curl_init($url);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
curl_setopt($ch, CURLOPT_TIMEOUT, 30);

$response = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
$error = curl_error($ch);
curl_close($ch);

echo "<h2>Response:</h2>";
echo "<p><strong>HTTP Code:</strong> $httpCode</p>";

if ($error) {
    echo "<p style='color:red;'><strong>Error:</strong> $error</p>";
}

echo "<pre style='background:#f4f4f4;padding:15px;border-radius:5px;'>";
echo htmlspecialchars($response);
echo "</pre>";

if ($httpCode == 200) {
    $json = json_decode($response, true);
    if ($json && isset($json['success']) && $json['success']) {
        echo "<p style='color:green;'><strong>✅ SUCCESS!</strong> API is working correctly.</p>";
        echo "<p>Check <code>otp_log.txt</code> for the OTP code.</p>";
    } else {
        echo "<p style='color:red;'><strong>❌ API returned error:</strong> " . ($json['message'] ?? 'Unknown error') . "</p>";
    }
} else {
    echo "<p style='color:red;'><strong>❌ HTTP Error $httpCode</strong></p>";
    echo "<p>Check Apache error logs: <code>C:\\xampp\\apache\\logs\\error.log</code></p>";
}
?>


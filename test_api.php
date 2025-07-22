<?php
// Test API endpoints
echo "Testing API endpoints...\n\n";

// Test 1: Login API
echo "1. Testing Login API...\n";
$loginData = [
    'username' => 'admin',
    'password' => '123456',
    'wallet_address' => '0x882ad5cd99340ae1217f929232c21b927265a622'
];

$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, 'http://localhost/heart_disease/api/login.php');
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($loginData));
curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

$response = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
$error = curl_error($ch);
curl_close($ch);

echo "Login Response (HTTP $httpCode): $response\n";
if ($error) {
    echo "CURL Error: $error\n";
}
echo "\n";

// Parse response to get token
$loginResult = json_decode($response, true);
$token = $loginResult['token'] ?? null;

if ($token) {
    echo "Token received: " . substr($token, 0, 20) . "...\n\n";
    
    // Test 2: Admin Appointments API
    echo "2. Testing Admin Appointments API...\n";
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, 'http://localhost/heart_disease/api/admin_appointments_stats.php');
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        'Content-Type: application/json',
        'Authorization: Bearer ' . $token
    ]);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    
    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);
    
    echo "Admin Appointments Response (HTTP $httpCode): $response\n\n";
} else {
    echo "No token received, skipping admin API test.\n\n";
}

echo "API testing completed.\n";
?> 
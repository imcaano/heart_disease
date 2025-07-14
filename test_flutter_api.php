<?php
// Test API for Flutter app
error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "<h1>Flutter API Test</h1>";

// Test data that matches Flutter app format
$testData = [
    'age' => 50,
    'sex' => 1,
    'cp' => 0,
    'trestbps' => 120,
    'chol' => 200,
    'fbs' => 0,
    'restecg' => 0,
    'thalach' => 150,
    'exang' => 0,
    'oldpeak' => 0.0,
    'slope' => 0,
    'ca' => 0,
    'thal' => 0
];

echo "<h2>Test Data:</h2>";
echo "<pre>" . json_encode($testData, JSON_PRETTY_PRINT) . "</pre>";

// Test the full API endpoint
echo "<h2>Testing Full API Endpoint:</h2>";

$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, 'http://localhost/heart_disease/api/predict.php');
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($testData));
curl_setopt($ch, CURLOPT_HTTPHEADER, [
    'Content-Type: application/json',
    'Accept: application/json'
]);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_TIMEOUT, 30);

$response = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
$error = curl_error($ch);
curl_close($ch);

echo "<p>HTTP Code: $httpCode</p>";

if ($error) {
    echo "<p style='color: red;'>❌ cURL Error: " . htmlspecialchars($error) . "</p>";
}

echo "<p>Response:</p>";
echo "<pre>" . htmlspecialchars($response) . "</pre>";

if ($httpCode == 200) {
    $data = json_decode($response, true);
    if ($data && isset($data['success']) && $data['success']) {
        echo "<p style='color: green;'>✅ API endpoint working correctly!</p>";
        echo "<p>Prediction: " . ($data['prediction'] == 1 ? 'High Risk' : 'Low Risk') . "</p>";
        echo "<p>Message: " . ($data['message'] ?? 'No message') . "</p>";
    } else {
        echo "<p style='color: red;'>❌ API endpoint returned error: " . ($data['message'] ?? 'Unknown error') . "</p>";
    }
} else {
    echo "<p style='color: red;'>❌ API endpoint failed with HTTP $httpCode</p>";
}

// Test direct function call
echo "<h2>Testing Direct Function Call:</h2>";

try {
    // Include config first
    require_once 'config.php';
    
    // Include the predict.php file to test the function directly
    require_once 'api/predict.php';
    
    // Test the prediction function
    $result = run_python_prediction($testData);
    
    echo "<h3>Direct Function Result:</h3>";
    echo "<pre>" . json_encode($result, JSON_PRETTY_PRINT) . "</pre>";
    
    if ($result['success']) {
        echo "<p style='color: green;'>✅ Direct function call successful!</p>";
        echo "<p>Prediction: " . ($result['prediction'] == 1 ? 'High Risk' : 'Low Risk') . "</p>";
        echo "<p>Message: " . ($result['message'] ?? 'No message') . "</p>";
    } else {
        echo "<p style='color: red;'>❌ Direct function call failed: " . $result['message'] . "</p>";
    }
    
} catch (Exception $e) {
    echo "<p style='color: red;'>❌ Direct function call error: " . $e->getMessage() . "</p>";
}
?> 
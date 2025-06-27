<?php
// Test script for Hugging Face API
$api_key = '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';
$api_url = 'https://api-inference.huggingface.co/models/gpt2';

echo "Testing Hugging Face API connection...\n";

// Simple test message
$data = [
    'inputs' => 'Hello, please give me a short medical advice about heart health.',
    'parameters' => [
        'max_length' => 100,
        'temperature' => 0.7
    ]
];

$ch = curl_init($api_url);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
curl_setopt($ch, CURLOPT_HTTPHEADER, [
    'Content-Type: application/json',
    'Authorization: Bearer ' . $api_key
]);

echo "Sending request to Hugging Face...\n";
$response = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);

if (curl_errno($ch)) {
    echo "Error: " . curl_error($ch) . "\n";
} else {
    echo "HTTP Status Code: " . $httpCode . "\n";
    echo "Response:\n";
    print_r(json_decode($response, true));
}

curl_close($ch); 
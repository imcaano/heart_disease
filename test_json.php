<?php
// Set content type to JSON
header('Content-Type: application/json');

// Get JSON data from request body
$json = file_get_contents('php://input');

// Log the raw JSON data
error_log("Raw JSON data received: " . $json);

// Check if the JSON is valid
if (empty($json)) {
    echo json_encode([
        'success' => false,
        'message' => 'No JSON data received'
    ]);
    exit;
}

// Try to decode the JSON
$data = json_decode($json, true);

if ($data === null) {
    $json_error = json_last_error_msg();
    error_log("JSON decode error: " . $json_error);
    echo json_encode([
        'success' => false,
        'message' => 'Invalid JSON data received: ' . $json_error
    ]);
    exit;
}

// Log the decoded data
error_log("Decoded JSON data: " . print_r($data, true));

// Return success
echo json_encode([
    'success' => true,
    'message' => 'JSON data received successfully',
    'data' => $data
]); 
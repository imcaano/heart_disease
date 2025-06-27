<?php
require_once 'config.php';

// Test data
$test_data = [
    'age' => 30,
    'sex' => 1,
    'cp' => 1,
    'trestbps' => 160,
    'chol' => 200,
    'fbs' => 0,
    'restecg' => 0,
    'thalach' => 150,
    'exang' => 0,
    'oldpeak' => 1.0,
    'slope' => 1,
    'ca' => 0,
    'thal' => 2
];

// Create temporary file
$temp_file = TEMP_DIR . '/test_prediction_' . uniqid() . '.json';
file_put_contents($temp_file, json_encode($test_data));

// Execute prediction script
$command = sprintf('python "%s" "%s" 2>&1', PREDICT_SCRIPT, $temp_file);
$output = shell_exec($command);

// Clean up
unlink($temp_file);

// Display results
echo "Command: " . $command . "\n\n";
echo "Output:\n" . $output . "\n\n";

// Try to parse JSON
$result = json_decode(trim($output), true);
if ($result === null) {
    echo "Error: Invalid JSON output\n";
} else {
    echo "Parsed result:\n";
    print_r($result);
} 
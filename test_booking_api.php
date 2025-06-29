<?php
// Test script to simulate booking API call
session_start();

// Simulate a logged-in user
$_SESSION['user'] = [
    'id' => 1,
    'username' => 'testuser',
    'email' => 'test@example.com',
    'role' => 'user'
];

// Simulate POST data
$_POST = [
    'patient_name' => 'Test User',
    'patient_email' => 'test@example.com',
    'patient_phone' => '1234567890',
    'appointment_date' => '2025-01-15',
    'appointment_time' => '10:00',
    'address' => 'Test Address',
    'reason' => 'Test consultation',
    'prediction_id' => '123',
    'prediction_result' => '1'
];

$_SERVER['REQUEST_METHOD'] = 'POST';

echo "Testing booking API with simulated data...\n";

// Include the booking API
ob_start();
include 'api/book_appointment.php';
$output = ob_get_clean();

echo "API Output:\n";
echo $output . "\n";

// Try to decode JSON
$json = json_decode($output, true);
if ($json === null) {
    echo "JSON decode error: " . json_last_error_msg() . "\n";
    echo "Raw output: " . $output . "\n";
} else {
    echo "JSON decoded successfully:\n";
    print_r($json);
}
?> 
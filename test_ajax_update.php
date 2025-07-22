<?php
// Simulate AJAX request for appointment status update
session_start();

// Set admin session
$_SESSION['user'] = [
    'id' => 1,
    'username' => 'admin',
    'role' => 'admin'
];

// Set POST data
$_POST['appointment_id'] = 1;
$_POST['status'] = 'approved';

// Include the update API
require_once 'api/update_appointment_status.php';
?> 
<?php
// Start session
session_start();

// Required files
require_once dirname(__DIR__) . '/config.php';

// Check if user is logged in and has admin access
if (!isset($_SESSION['user']) || !in_array($_SESSION['user']['role'], ['admin', 'developer'])) {
    http_response_code(401);
    echo json_encode(['error' => 'Unauthorized']);
    exit;
}

// Check if ID is provided
if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    http_response_code(400);
    echo json_encode(['error' => 'Invalid user ID']);
    exit;
}

$userId = (int)$_GET['id'];

// Default response
$response = ['error' => 'User not found'];

try {
    // Check if we can connect to database
    if (!isset($pdo)) {
        throw new Exception("Database connection not established");
    }
    
    // Check if users table exists
    $stmt = $pdo->query("SHOW TABLES LIKE 'users'");
    if ($stmt->rowCount() === 0) {
        throw new Exception("Users table does not exist");
    }
    
    // Get user by ID
    $stmt = $pdo->prepare("SELECT * FROM users WHERE id = ?");
    $stmt->execute([$userId]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if ($user) {
        // Remove sensitive data
        unset($user['password']);
        
        // Format dates
        if (!empty($user['last_login'])) {
            $user['last_login'] = date('M d, Y H:i', strtotime($user['last_login']));
        } else {
            $user['last_login'] = 'Never';
        }
        
        $user['created_at'] = date('M d, Y', strtotime($user['created_at']));
        
        // Format other fields if needed
        $user['prediction_accuracy'] = floatval($user['prediction_accuracy']) . '%';
        
        // Ensure total_predictions is a number
        $user['total_predictions'] = intval($user['total_predictions']);
        
        // Ensure wallet_address exists
        if (empty($user['wallet_address'])) {
            $user['wallet_address'] = 'Not set';
        }
        
        $response = $user;
    }
} catch (Exception $e) {
    // Log the error
    error_log("Error in get_user.php: " . $e->getMessage());
    
    // Return error response
    $response = ['error' => 'Database error: ' . $e->getMessage()];
}

// Set content type header before any output
header('Content-Type: application/json');

// Ensure no whitespace or other output before JSON
ob_clean();

// Return JSON response
echo json_encode($response); 
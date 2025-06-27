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

// Get JSON data from request body
$json = file_get_contents('php://input');
$data = json_decode($json, true);

// Validate input data
if (!isset($data['role']) || !isset($data['status'])) {
    http_response_code(400);
    echo json_encode(['error' => 'Missing required fields']);
    exit;
}

// Validate role
$validRoles = ['user', 'admin', 'expert', 'developer'];
if (!in_array($data['role'], $validRoles)) {
    http_response_code(400);
    echo json_encode(['error' => 'Invalid role']);
    exit;
}

// Validate status
$validStatuses = ['active', 'inactive', 'banned'];
if (!in_array($data['status'], $validStatuses)) {
    http_response_code(400);
    echo json_encode(['error' => 'Invalid status']);
    exit;
}

// Default response
$response = ['error' => 'Failed to update user'];

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
    
    // Check if user exists
    $stmt = $pdo->prepare("SELECT id FROM users WHERE id = ?");
    $stmt->execute([$userId]);
    if ($stmt->rowCount() === 0) {
        throw new Exception("User not found");
    }
    
    // Update user
    $stmt = $pdo->prepare("UPDATE users SET role = ?, status = ? WHERE id = ?");
    $result = $stmt->execute([$data['role'], $data['status'], $userId]);
    
    if ($result) {
        // Log the activity
        $activityType = 'role_change';
        $description = "User role changed to {$data['role']} and status to {$data['status']}";
        
        // Check if user_activity_log table exists
        $stmt = $pdo->query("SHOW TABLES LIKE 'user_activity_log'");
        if ($stmt->rowCount() > 0) {
            $stmt = $pdo->prepare("INSERT INTO user_activity_log (user_id, activity_type, description, ip_address) VALUES (?, ?, ?, ?)");
            $stmt->execute([$_SESSION['user']['id'], $activityType, $description, $_SERVER['REMOTE_ADDR']]);
        }
        
        $response = ['success' => true, 'message' => 'User updated successfully'];
    } else {
        throw new Exception("Failed to update user");
    }
} catch (Exception $e) {
    // Log the error
    error_log("Error in update_user.php: " . $e->getMessage());
    
    // Return error response
    $response = ['error' => 'Database error: ' . $e->getMessage()];
}

// Set content type header before any output
header('Content-Type: application/json');

// Ensure no whitespace or other output before JSON
ob_clean();

// Return JSON response
echo json_encode($response); 
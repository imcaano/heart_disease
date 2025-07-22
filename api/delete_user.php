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
$response = ['error' => 'Failed to delete user'];

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
    
    // Start transaction
    $pdo->beginTransaction();
    
    // Delete user's predictions
    $stmt = $pdo->prepare("DELETE FROM predictions WHERE user_id = ?");
    $stmt->execute([$userId]);
    
    // Delete user's activity logs
    $stmt = $pdo->query("SHOW TABLES LIKE 'user_activity_log'");
    if ($stmt->rowCount() > 0) {
        $stmt = $pdo->prepare("DELETE FROM user_activity_log WHERE user_id = ?");
        $stmt->execute([$userId]);
    }
    
    // Delete user
    $stmt = $pdo->prepare("DELETE FROM users WHERE id = ?");
    $result = $stmt->execute([$userId]);
    
    if ($result) {
        // Commit transaction
        $pdo->commit();
        
        // Log the activity
        $activityType = 'user_deleted';
        $description = "User deleted";
        
        // Check if user_activity_log table exists
        $stmt = $pdo->query("SHOW TABLES LIKE 'user_activity_log'");
        if ($stmt->rowCount() > 0) {
            $stmt = $pdo->prepare("INSERT INTO user_activity_log (user_id, activity_type, description, ip_address) VALUES (?, ?, ?, ?)");
            $stmt->execute([$_SESSION['user']['id'], $activityType, $description, $_SERVER['REMOTE_ADDR']]);
        }
        
        $response = ['success' => true, 'message' => 'User deleted successfully'];
    } else {
        // Rollback transaction
        $pdo->rollBack();
        throw new Exception("Failed to delete user");
    }
} catch (Exception $e) {
    // Rollback transaction if active
    if (isset($pdo) && $pdo->inTransaction()) {
        $pdo->rollBack();
    }
    
    // Log the error
    error_log("Error in delete_user.php: " . $e->getMessage());
    
    // Return error response
    $response = ['error' => 'Database error: ' . $e->getMessage()];
}

header('Content-Type: application/json');
echo json_encode($response); 
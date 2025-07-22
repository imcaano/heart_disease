<?php
// CORS headers for Flutter web
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Headers: Content-Type, Authorization");
header("Access-Control-Allow-Methods: GET, POST, OPTIONS");

// Handle preflight OPTIONS request
if ($_SERVER['REQUEST_METHOD'] == 'OPTIONS') {
    http_response_code(200);
    exit();
}

// Required files
require_once dirname(__DIR__) . '/config.php';

// Enable error reporting
error_reporting(E_ALL);
ini_set('display_errors', 1);

try {
    // Create database connection
    $pdo = new PDO("mysql:host=$host;dbname=$dbname", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    // Check users table
    $stmt = $pdo->query("SELECT COUNT(*) as count FROM users");
    $userCount = $stmt->fetch(PDO::FETCH_ASSOC)['count'];
    
    // Check predictions table
    $stmt = $pdo->query("SELECT COUNT(*) as count FROM predictions");
    $predictionCount = $stmt->fetch(PDO::FETCH_ASSOC)['count'];
    
    // Get sample users
    $stmt = $pdo->query("SELECT id, username, email, role, created_at FROM users LIMIT 5");
    $users = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Get sample predictions
    $stmt = $pdo->query("SELECT id, user_id, prediction_result, confidence_score, created_at FROM predictions LIMIT 5");
    $predictions = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    $response = [
        'success' => true,
        'database_info' => [
            'total_users' => $userCount,
            'total_predictions' => $predictionCount,
            'sample_users' => $users,
            'sample_predictions' => $predictions
        ]
    ];
    
    header('Content-Type: application/json');
    echo json_encode($response, JSON_PRETTY_PRINT);
    
} catch (Exception $e) {
    header('Content-Type: application/json');
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'error' => $e->getMessage()
    ]);
}
?> 
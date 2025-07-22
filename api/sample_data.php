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
    
    // Check if sample data already exists
    $stmt = $pdo->query("SELECT COUNT(*) as count FROM users");
    $userCount = $stmt->fetch(PDO::FETCH_ASSOC)['count'];
    
    if ($userCount > 0) {
        echo json_encode([
            'success' => true,
            'message' => 'Sample data already exists in database',
            'user_count' => $userCount
        ]);
        exit;
    }
    
    // Add sample users
    $sampleUsers = [
        ['john_doe', 'john@example.com', 'password123', '0x1234567890abcdef', 'user'],
        ['jane_smith', 'jane@example.com', 'password123', '0xabcdef1234567890', 'user'],
        ['admin_user', 'admin@example.com', 'admin123', '0x9876543210fedcba', 'admin'],
        ['dr_wilson', 'dr.wilson@hospital.com', 'password123', '0xfedcba0987654321', 'user'],
        ['nurse_jones', 'nurse.jones@clinic.com', 'password123', '0x1122334455667788', 'user'],
        ['patient_brown', 'brown@email.com', 'password123', '0x9988776655443322', 'user'],
        ['cardiologist_lee', 'dr.lee@cardio.com', 'password123', '0x5566778899001122', 'user'],
        ['researcher_garcia', 'garcia@research.org', 'password123', '0x3344556677889900', 'user']
    ];
    
    $userIds = [];
    foreach ($sampleUsers as $user) {
        $stmt = $pdo->prepare("
            INSERT INTO users (username, email, password, wallet_address, role, created_at) 
            VALUES (?, ?, ?, ?, ?, NOW())
        ");
        $stmt->execute($user);
        $userIds[] = $pdo->lastInsertId();
    }
    
    // Add sample predictions
    $samplePredictions = [
        // User 1 predictions
        [1, 1, 0.85, 'High risk detected', '2024-01-15 10:30:00'],
        [1, 0, 0.72, 'Low risk detected', '2024-01-16 14:20:00'],
        [1, 1, 0.91, 'High risk detected', '2024-01-17 09:15:00'],
        
        // User 2 predictions
        [2, 0, 0.68, 'Low risk detected', '2024-01-14 16:45:00'],
        [2, 0, 0.75, 'Low risk detected', '2024-01-15 11:30:00'],
        [2, 1, 0.88, 'High risk detected', '2024-01-16 13:20:00'],
        
        // User 3 predictions
        [3, 0, 0.65, 'Low risk detected', '2024-01-13 08:30:00'],
        [3, 1, 0.92, 'High risk detected', '2024-01-14 15:45:00'],
        [3, 0, 0.71, 'Low risk detected', '2024-01-15 12:10:00'],
        
        // User 4 predictions
        [4, 1, 0.89, 'High risk detected', '2024-01-12 10:20:00'],
        [4, 0, 0.73, 'Low risk detected', '2024-01-13 14:35:00'],
        [4, 1, 0.87, 'High risk detected', '2024-01-14 16:50:00'],
        
        // User 5 predictions
        [5, 0, 0.69, 'Low risk detected', '2024-01-11 09:25:00'],
        [5, 0, 0.76, 'Low risk detected', '2024-01-12 11:40:00'],
        [5, 1, 0.84, 'High risk detected', '2024-01-13 13:55:00'],
        
        // User 6 predictions
        [6, 1, 0.90, 'High risk detected', '2024-01-10 08:15:00'],
        [6, 0, 0.67, 'Low risk detected', '2024-01-11 10:30:00'],
        [6, 0, 0.74, 'Low risk detected', '2024-01-12 12:45:00'],
        
        // User 7 predictions
        [7, 0, 0.70, 'Low risk detected', '2024-01-09 07:20:00'],
        [7, 1, 0.86, 'High risk detected', '2024-01-10 09:35:00'],
        [7, 0, 0.72, 'Low risk detected', '2024-01-11 11:50:00'],
        
        // User 8 predictions
        [8, 1, 0.93, 'High risk detected', '2024-01-08 06:10:00'],
        [8, 0, 0.68, 'Low risk detected', '2024-01-09 08:25:00'],
        [8, 1, 0.88, 'High risk detected', '2024-01-10 10:40:00']
    ];
    
    foreach ($samplePredictions as $prediction) {
        $stmt = $pdo->prepare("
            INSERT INTO predictions (user_id, prediction_result, confidence_score, notes, created_at) 
            VALUES (?, ?, ?, ?, ?)
        ");
        $stmt->execute($prediction);
    }
    
    // Get final counts
    $stmt = $pdo->query("SELECT COUNT(*) as count FROM users");
    $finalUserCount = $stmt->fetch(PDO::FETCH_ASSOC)['count'];
    
    $stmt = $pdo->query("SELECT COUNT(*) as count FROM predictions");
    $finalPredictionCount = $stmt->fetch(PDO::FETCH_ASSOC)['count'];
    
    $response = [
        'success' => true,
        'message' => 'Sample data added successfully',
        'users_added' => count($sampleUsers),
        'predictions_added' => count($samplePredictions),
        'total_users' => $finalUserCount,
        'total_predictions' => $finalPredictionCount
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
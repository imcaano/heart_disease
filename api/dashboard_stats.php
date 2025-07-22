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

// Start session
session_start();

// Required files
require_once dirname(__DIR__) . '/config.php';

// Enable error reporting
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Temporarily allow access without admin check for testing
// TODO: Re-enable admin check after testing
/*
if (!isset($_SESSION['user']) || $_SESSION['user']['role'] !== 'admin') {
    header('Content-Type: application/json');
    http_response_code(403);
    echo json_encode(['success' => false, 'error' => 'Unauthorized access']);
    exit;
}
*/

// Accept user_id as GET or POST parameter
$user_id = $_GET['user_id'] ?? $_POST['user_id'] ?? null;

try {
    // Create database connection
    $pdo = new PDO("mysql:host=$host;dbname=$dbname", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    if ($user_id) {
        // Get stats for a specific user
        $stmt = $pdo->prepare("
            SELECT 
                COUNT(*) as total,
                SUM(CASE WHEN prediction_result = 1 THEN 1 ELSE 0 END) as positive,
                SUM(CASE WHEN prediction_result = 0 THEN 1 ELSE 0 END) as negative
            FROM predictions
            WHERE user_id = ?
        ");
        $stmt->execute([$user_id]);
        $predictionStats = $stmt->fetch(PDO::FETCH_ASSOC);
        
        $stmt = $pdo->prepare("
            SELECT 
                p.id,
                p.prediction_result,
                p.created_at,
                u.username,
                u.email
            FROM predictions p
            LEFT JOIN users u ON p.user_id = u.id
            WHERE p.user_id = ?
            ORDER BY p.created_at DESC
            LIMIT 10
        ");
        $stmt->execute([$user_id]);
        $recentActivities = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        $response = [
            'success' => true,
            'totalPredictions' => (int)$predictionStats['total'],
            'positivePredictions' => (int)$predictionStats['positive'],
            'negativePredictions' => (int)$predictionStats['negative'],
            'total_predictions' => (int)$predictionStats['total'],
            'high_risk_cases' => (int)$predictionStats['positive'],
            'low_risk_cases' => (int)$predictionStats['negative'],
            'recent_activities' => $recentActivities,
            'last_updated' => date('Y-m-d H:i:s')
        ];
        header('Content-Type: application/json');
        header('Cache-Control: no-cache, must-revalidate');
        echo json_encode($response);
        exit;
    }
    
    // Get total users
    $stmt = $pdo->query("SELECT COUNT(*) as total FROM users");
    $totalUsers = $stmt->fetch(PDO::FETCH_ASSOC)['total'];
    
    // Get prediction statistics
    $stmt = $pdo->query("
        SELECT 
            COUNT(*) as total,
            SUM(CASE WHEN prediction_result = 1 THEN 1 ELSE 0 END) as positive,
            SUM(CASE WHEN prediction_result = 0 THEN 1 ELSE 0 END) as negative
        FROM predictions
    ");
    $predictionStats = $stmt->fetch(PDO::FETCH_ASSOC);
    
    // Get recent activities (last 10 predictions with user info)
    $stmt = $pdo->query("
        SELECT 
            p.id,
            p.prediction_result,
            p.created_at,
            u.username,
            u.email
        FROM predictions p
        LEFT JOIN users u ON p.user_id = u.id
        ORDER BY p.created_at DESC
        LIMIT 10
    ");
    $recentActivities = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Get top users by prediction count
    $stmt = $pdo->query("
        SELECT 
            u.id,
            u.username,
            u.email,
            COUNT(p.id) as prediction_count,
            AVG(p.confidence_score) as avg_confidence,
            SUM(CASE WHEN p.prediction_result = 0 THEN 1 ELSE 0 END) as correct_predictions,
            COUNT(p.id) as total_predictions
        FROM users u
        LEFT JOIN predictions p ON u.id = p.user_id
        GROUP BY u.id, u.username, u.email
        ORDER BY prediction_count DESC
        LIMIT 5
    ");
    $topUsers = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Calculate accuracy for each user
    foreach ($topUsers as &$user) {
        $user['accuracy'] = $user['total_predictions'] > 0 
            ? round(($user['correct_predictions'] / $user['total_predictions']) * 100, 1)
            : 0;
        $user['avg_confidence'] = round($user['avg_confidence'] ?? 0, 1);
    }
    
    // Prepare response with keys that match both web and Flutter expectations
    $response = [
        'success' => true,
        // Web dashboard keys
        'totalUsers' => (int)$totalUsers,
        'totalPredictions' => (int)$predictionStats['total'],
        'positivePredictions' => (int)$predictionStats['positive'],
        'negativePredictions' => (int)$predictionStats['negative'],
        // Flutter dashboard keys (for backward compatibility)
        'total_users' => (int)$totalUsers,
        'total_predictions' => (int)$predictionStats['total'],
        'high_risk_cases' => (int)$predictionStats['positive'],
        'low_risk_cases' => (int)$predictionStats['negative'],
        'system_accuracy' => $predictionStats['total'] > 0 
            ? round((($predictionStats['negative'] / $predictionStats['total']) * 100), 1)
            : 0,
        // Additional data for enhanced dashboard
        'recent_activities' => $recentActivities,
        'top_users' => $topUsers,
        'last_updated' => date('Y-m-d H:i:s')
    ];
    
    // Set headers
    header('Content-Type: application/json');
    header('Cache-Control: no-cache, must-revalidate');
    
    // Send response
    echo json_encode($response);
    
} catch (Exception $e) {
    // Log the error
    error_log("Dashboard stats error: " . $e->getMessage());
    
    // Return error response
    header('Content-Type: application/json');
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'error' => 'Failed to fetch dashboard statistics',
        // Fallback data
        'totalUsers' => 0,
        'totalPredictions' => 0,
        'positivePredictions' => 0,
        'negativePredictions' => 0,
        'total_users' => 0,
        'total_predictions' => 0,
        'high_risk_cases' => 0,
        'low_risk_cases' => 0,
        'system_accuracy' => 0,
        'recent_activities' => [],
        'top_users' => [],
        'last_updated' => date('Y-m-d H:i:s')
    ]);
} 
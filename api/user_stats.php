<?php
// Start session
session_start();

// Required files
require_once dirname(__DIR__) . '/config.php';

// Enable error reporting
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header('Content-Type: application/json');
    http_response_code(401);
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit;
}

try {
    $userId = $_SESSION['user_id'];
    $conn = getDBConnection();
    
    if (!$conn) {
        throw new Exception("Database connection failed");
    }
    
    // Get total predictions
    $stmt = $conn->prepare("SELECT COUNT(*) as total FROM predictions WHERE user_id = ?");
    $stmt->execute([$userId]);
    $totalPredictions = $stmt->fetch(PDO::FETCH_ASSOC)['total'];
    
    // Get high risk predictions count
    $stmt = $conn->prepare("
        SELECT COUNT(*) as high_risk 
        FROM predictions 
        WHERE user_id = ? AND prediction_result = 1
    ");
    $stmt->execute([$userId]);
    $highRiskCount = $stmt->fetch(PDO::FETCH_ASSOC)['high_risk'];
    
    // Get low risk predictions count
    $stmt = $conn->prepare("
        SELECT COUNT(*) as low_risk 
        FROM predictions 
        WHERE user_id = ? AND prediction_result = 0
    ");
    $stmt->execute([$userId]);
    $lowRiskCount = $stmt->fetch(PDO::FETCH_ASSOC)['low_risk'];
    
    // Calculate accuracy rate (simplified)
    $accuracyRate = $totalPredictions > 0 ? round(($lowRiskCount / $totalPredictions) * 100, 1) : 0;
    
    // Get pending predictions
    $stmt = $conn->prepare("
        SELECT COUNT(*) as pending 
        FROM predictions 
        WHERE user_id = ? AND prediction_result IS NULL
    ");
    $stmt->execute([$userId]);
    $pendingPredictions = $stmt->fetch(PDO::FETCH_ASSOC)['pending'];
    
    // Get recent predictions
    $stmt = $conn->prepare("
        SELECT id, prediction_result as result, created_at 
        FROM predictions 
        WHERE user_id = ? 
        ORDER BY created_at DESC 
        LIMIT 5
    ");
    $stmt->execute([$userId]);
    $recentPredictions = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Get prediction data for charts
    $stmt = $conn->prepare("
        SELECT prediction_result, COUNT(*) as count
        FROM predictions
        WHERE user_id = ? AND prediction_result IS NOT NULL
        GROUP BY prediction_result
    ");
    $stmt->execute([$userId]);
    $predictionStats = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Prepare response
    $response = [
        'success' => true,
        'totalPredictions' => (int)$totalPredictions,
        'highRiskCount' => (int)$highRiskCount,
        'lowRiskCount' => (int)$lowRiskCount,
        'accuracyRate' => (float)$accuracyRate,
        'pendingPredictions' => (int)$pendingPredictions,
        'recentPredictions' => $recentPredictions,
        'predictionStats' => $predictionStats
    ];
    
    // Set headers
    header('Content-Type: application/json');
    header('Cache-Control: no-cache, must-revalidate');
    
    // Send response
    echo json_encode($response);
    
} catch (Exception $e) {
    // Log the error
    error_log("Dashboard data error: " . $e->getMessage());
    
    // Return error response
    header('Content-Type: application/json');
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
} 
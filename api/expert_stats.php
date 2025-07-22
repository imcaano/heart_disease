<?php
session_start();
require_once dirname(__DIR__) . '/config.php';

// Check if user is authorized
if (!isset($_SESSION['user']) || !in_array($_SESSION['user']['role'], ['admin', 'expert'])) {
    header('Content-Type: application/json');
    http_response_code(401);
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit;
}

try {
    $conn = getDBConnection();
    
    if (!$conn) {
        throw new Exception("Database connection failed");
    }

    // Get prediction statistics
    $statsQuery = "
        SELECT 
            ps.total_predictions,
            ps.accurate_predictions,
            ps.accuracy_rate,
            ps.average_confidence,
            ps.expert_verified_count,
            ps.last_prediction_date
        FROM prediction_statistics ps
        WHERE ps.user_id = :user_id
    ";
    
    $stmt = $conn->prepare($statsQuery);
    $stmt->execute(['user_id' => $_SESSION['user']['id']]);
    $predictionStats = $stmt->fetch(PDO::FETCH_ASSOC);

    // If no stats exist, provide default values
    if (!$predictionStats) {
        $predictionStats = [
            'total_predictions' => 0,
            'accurate_predictions' => 0,
            'accuracy_rate' => 0,
            'average_confidence' => 0,
            'expert_verified_count' => 0,
            'last_prediction_date' => null
        ];
    }

    // Get custom reports
    $reportsQuery = "
        SELECT 
            report_name,
            report_type,
            last_generated,
            schedule_frequency,
            is_public
        FROM custom_reports
        WHERE user_id = :user_id
        ORDER BY last_generated DESC
        LIMIT 5
    ";
    
    $stmt = $conn->prepare($reportsQuery);
    $stmt->execute(['user_id' => $_SESSION['user']['id']]);
    $customReports = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Get recent feedback
    $feedbackQuery = "
        SELECT 
            pf.feedback_type,
            pf.rating,
            pf.is_resolved,
            pf.created_at
        FROM prediction_feedback pf
        JOIN predictions p ON p.id = pf.prediction_id
        WHERE p.user_id = :user_id
        ORDER BY pf.created_at DESC
        LIMIT 5
    ";
    
    $stmt = $conn->prepare($feedbackQuery);
    $stmt->execute(['user_id' => $_SESSION['user']['id']]);
    $recentFeedback = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Prepare response
    $response = [
        'success' => true,
        'predictionStats' => $predictionStats,
        'customReports' => $customReports,
        'recentFeedback' => $recentFeedback
    ];
    
    // Set headers
    header('Content-Type: application/json');
    header('Cache-Control: no-cache, must-revalidate');
    
    // Send response
    echo json_encode($response);
    
} catch (Exception $e) {
    // Log the error
    error_log("Expert stats error: " . $e->getMessage());
    
    // Return error response
    header('Content-Type: application/json');
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
} 
?> 
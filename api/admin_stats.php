<?php
session_start();

// Required files
require_once dirname(__DIR__) . '/config.php';

// Check if user is admin
if (!isset($_SESSION['user']) || $_SESSION['user']['role'] !== 'admin') {
    header('Content-Type: application/json');
    echo json_encode(['error' => 'Unauthorized access']);
    exit;
}

try {
    $response = [
        'totalUsers' => 0,
        'totalPredictions' => 0,
        'highRiskCount' => 0,
        'expertVerified' => 0,
        'totalExperts' => 0,
        'customReports' => [],
        'feedback' => []
    ];

    // Get total users
    $stmt = $pdo->query("SELECT COUNT(*) as total FROM users");
    $userCount = $stmt->fetch(PDO::FETCH_ASSOC);
    $response['totalUsers'] = (int)$userCount['total'];

    // Get prediction statistics including expert verification
    $stmt = $pdo->query("
        SELECT 
            COUNT(*) as total,
            SUM(CASE WHEN verified_by_expert = 1 THEN 1 ELSE 0 END) as expert_verified,
            SUM(CASE WHEN prediction_result = 1 THEN 1 ELSE 0 END) as high_risk
        FROM predictions
    ");
    $predStats = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if ($predStats) {
        $response['totalPredictions'] = (int)$predStats['total'];
        $response['expertVerified'] = (int)$predStats['expert_verified'];
        $response['highRiskCount'] = (int)$predStats['high_risk'];
    }

    // Get total number of experts
    $stmt = $pdo->query("
        SELECT COUNT(*) as expert_count
        FROM users
        WHERE role = 'expert' AND status = 'active'
    ");
    $expertCount = $stmt->fetch(PDO::FETCH_ASSOC);
    $response['totalExperts'] = (int)$expertCount['expert_count'];

    // Get expert custom reports
    $stmt = $pdo->query("
        SELECT 
            cr.id,
            cr.report_name as name,
            cr.report_description as description,
            cr.report_type as type,
            cr.last_generated,
            u.username as expert_name,
            CASE 
                WHEN cr.last_generated IS NOT NULL THEN 'completed'
                ELSE 'pending'
            END as status
        FROM custom_reports cr
        JOIN users u ON cr.user_id = u.id
        WHERE u.role = 'expert'
        ORDER BY cr.created_at DESC
        LIMIT 5
    ");
    $customReports = $stmt->fetchAll(PDO::FETCH_ASSOC);
    $response['customReports'] = $customReports;

    // Get expert feedback
    $stmt = $pdo->query("
        SELECT 
            pf.feedback_type as type,
            pf.rating,
            pf.feedback_text as comment,
            u.username,
            pf.created_at as date,
            pf.is_resolved
        FROM prediction_feedback pf
        JOIN users u ON pf.user_id = u.id
        WHERE u.role = 'expert'
        ORDER BY pf.created_at DESC
        LIMIT 5
    ");
    $feedback = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    foreach ($feedback as &$item) {
        $item['date'] = date('Y-m-d', strtotime($item['date']));
    }
    $response['feedback'] = $feedback;

    // Add export data timestamp
    $response['exportTimestamp'] = date('Y-m-d H:i:s');

    header('Content-Type: application/json');
    echo json_encode($response);

} catch (PDOException $e) {
    error_log("Database error in admin_stats.php: " . $e->getMessage());
    header('Content-Type: application/json');
    echo json_encode([
        'error' => 'Database error occurred',
        'debug' => [
            'message' => $e->getMessage(),
            'code' => $e->getCode()
        ]
    ]);
} 
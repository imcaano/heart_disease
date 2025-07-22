<?php
// Start session
session_start();

// Required files
require_once dirname(__DIR__) . '/config.php';

// Check if user is logged in or user_id is provided for API
$user_id_param = $_GET['user_id'] ?? $_POST['user_id'] ?? null;
if (!isset($_SESSION['user']) && !$user_id_param) {
    http_response_code(401);
    echo json_encode(['error' => 'Unauthorized']);
    exit;
}

// Get parameters
$startDate = isset($_GET['start']) ? $_GET['start'] : date('Y-m-d', strtotime('-30 days'));
$endDate = isset($_GET['end']) ? $_GET['end'] : date('Y-m-d');
$userType = isset($_GET['user_type']) ? $_GET['user_type'] : 'all'; // all, admin, user
$predictionResult = isset($_GET['prediction_result']) ? $_GET['prediction_result'] : 'all'; // all, success, failure
$sortBy = isset($_GET['sort_by']) ? $_GET['sort_by'] : 'date_desc'; // date_asc, date_desc, accuracy_asc, accuracy_desc

// Calculate date range for chart
$startDateTime = new DateTime($startDate);
$endDateTime = new DateTime($endDate);
$interval = $startDateTime->diff($endDateTime);
$days = $interval->days + 1; // Include the end date

// Determine user context
$isAdmin = isset($_SESSION['user']) && in_array($_SESSION['user']['role'], ['admin', 'developer']);
$user_id = $_SESSION['user']['id'] ?? $user_id_param;
if ($user_id_param && isset($_SESSION['user']) && !$isAdmin && $_SESSION['user']['id'] != $user_id_param) {
    http_response_code(403);
    echo json_encode(['error' => 'Unauthorized']);
    exit;
}

// Prepare response structure
$response = [
    'status' => 'success',
    'total_predictions' => 0,
    'positive_predictions' => 0,
    'negative_predictions' => 0,
    'daily_data' => [],
    'recent_predictions' => [],
    'user_stats' => []
];

if ($isAdmin) {
    $response['totalUsers'] = 0;
    $response['totalRevenue'] = '0.00';
}

// Generate empty timeline data
$dateFormat = 'Y-m-d';
$currentDate = clone $startDateTime;
while ($currentDate <= $endDateTime) {
    $dateStr = $currentDate->format($dateFormat);
    $response['daily_data'][] = [
        'date' => $dateStr,
        'total' => 0,
        'successful' => 0,
        'failed' => 0,
        'positive' => 0,
        'negative' => 0
    ];
    $currentDate->modify('+1 day');
}

try {
    // Base query conditions
    $whereConditions = ["created_at BETWEEN ? AND ?"];
    $params = [$startDate, $endDate . ' 23:59:59'];

    // For regular users, limit to their own predictions
    if (!$isAdmin) {
        $whereConditions[] = "user_id = ?";
        $params[] = $user_id;
    }

    // Combine conditions
    $whereClause = implode(" AND ", $whereConditions);

    // Get prediction statistics
    $statsSql = "SELECT 
        COUNT(*) as total,
        SUM(CASE WHEN prediction_result = 1 THEN 1 ELSE 0 END) as positive,
        SUM(CASE WHEN prediction_result = 0 THEN 1 ELSE 0 END) as negative
    FROM predictions
    WHERE $whereClause";

    $stmt = $pdo->prepare($statsSql);
    $stmt->execute($params);
    $stats = $stmt->fetch(PDO::FETCH_ASSOC);

    $response['total_predictions'] = (int)$stats['total'];
    $response['positive_predictions'] = (int)$stats['positive'];
    $response['negative_predictions'] = (int)$stats['negative'];

    // Get daily data
    $dailySql = "SELECT 
        DATE(created_at) as date,
        COUNT(*) as total,
        SUM(CASE WHEN prediction_result = 1 THEN 1 ELSE 0 END) as positive,
        SUM(CASE WHEN prediction_result = 0 THEN 1 ELSE 0 END) as negative
    FROM predictions
    WHERE $whereClause
    GROUP BY DATE(created_at)
    ORDER BY date ASC";

    $stmt = $pdo->prepare($dailySql);
    $stmt->execute($params);
    $response['daily_data'] = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Get recent predictions with user info and address
    $recentSql = "SELECT 
        p.*, 
        u.username, 
        u.email, 
        a.address
    FROM predictions p
    LEFT JOIN users u ON p.user_id = u.id
    LEFT JOIN appointments a ON a.prediction_id = p.id
    WHERE $whereClause
    ORDER BY p.created_at DESC
    LIMIT 50";

    $stmt = $pdo->prepare($recentSql);
    $stmt->execute($params);
    $response['recent_predictions'] = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Get user statistics if admin
    if ($isAdmin) {
        $userStatsSql = "SELECT 
            u.id,
            u.username,
            COUNT(p.id) as total_predictions,
            SUM(CASE WHEN p.prediction_result = 1 THEN 1 ELSE 0 END) as positive_predictions,
            SUM(CASE WHEN p.prediction_result = 0 THEN 1 ELSE 0 END) as negative_predictions
        FROM users u
        LEFT JOIN predictions p ON u.id = p.user_id
        WHERE p.created_at BETWEEN ? AND ?
        GROUP BY u.id, u.username
        ORDER BY total_predictions DESC";

        $stmt = $pdo->prepare($userStatsSql);
        $stmt->execute([$startDate, $endDate . ' 23:59:59']);
        $response['user_stats'] = $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // Set headers
    header('Content-Type: application/json');
    header('Cache-Control: no-cache, must-revalidate');
    
    // Send response
    echo json_encode($response);

} catch (Exception $e) {
    error_log("Report generation error: " . $e->getMessage());
    http_response_code(500);
    echo json_encode([
        'status' => 'error',
        'message' => 'Failed to generate report'
    ]);
}
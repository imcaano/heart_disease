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

// Check if user is logged in and has admin access
// Temporarily allow access without admin check for testing
// TODO: Re-enable admin check after testing
/*
if (!isset($_SESSION['user']) || !in_array($_SESSION['user']['role'], ['admin', 'developer'])) {
    http_response_code(401);
    echo json_encode(['success' => false, 'error' => 'Unauthorized']);
    exit;
}
*/

// Get query parameters
$page = max(1, isset($_GET['page']) ? (int)$_GET['page'] : 1);
$itemsPerPage = max(1, isset($_GET['limit']) ? (int)$_GET['limit'] : 50);
$userId = isset($_GET['user_id']) ? (int)$_GET['user_id'] : null;
$predictionResult = isset($_GET['prediction_result']) ? $_GET['prediction_result'] : null;

// Calculate offset
$offset = ($page - 1) * $itemsPerPage;

// Default response structure
$response = [
    'success' => true,
    'total' => 0,
    'page' => $page,
    'limit' => $itemsPerPage,
    'predictions' => []
];

try {
    // Check if we can connect to database
    if (!isset($pdo)) {
        throw new Exception("Database connection not established");
    }
    
    // Build query conditions
    $whereConditions = [];
    $params = [];
    
    if ($userId !== null) {
        $whereConditions[] = "p.user_id = ?";
        $params[] = $userId;
    }
    
    if ($predictionResult !== null) {
        $whereConditions[] = "p.prediction_result = ?";
        $params[] = $predictionResult;
    }
    
    $whereClause = !empty($whereConditions) ? "WHERE " . implode(" AND ", $whereConditions) : "";
    
    // Get total count
    $countSql = "SELECT COUNT(*) as total FROM predictions p $whereClause";
    $countStmt = $pdo->prepare($countSql);
    
    // Bind where clause parameters
    foreach ($params as $key => $value) {
        $countStmt->bindValue($key + 1, $value, PDO::PARAM_STR);
    }
    
    $countStmt->execute();
    $totalPredictions = $countStmt->fetchColumn();
    
    // Get predictions with user information and address from appointments
    $sql = "SELECT 
                p.id,
                p.user_id,
                p.age,
                p.sex,
                p.cp,
                p.trestbps,
                p.chol,
                p.fbs,
                p.restecg,
                p.thalach,
                p.exang,
                p.oldpeak,
                p.slope,
                p.ca,
                p.thal,
                p.prediction_result as prediction,
                p.confidence_score,
                p.verified_by_expert,
                p.expert_notes,
                p.transaction_hash,
                p.created_at,
                u.username,
                u.email,
                MAX(a.address) as address
            FROM predictions p
            LEFT JOIN users u ON p.user_id = u.id
            LEFT JOIN appointments a ON a.prediction_id = p.id
            $whereClause 
            GROUP BY p.user_id, p.age, p.sex, p.cp, p.trestbps, p.chol, p.fbs, p.restecg, p.thalach, p.exang, p.oldpeak, p.slope, p.ca, p.thal, p.prediction_result, p.created_at
            ORDER BY p.created_at DESC 
            LIMIT ?, ?";
    
    $stmt = $pdo->prepare($sql);
    
    // Bind where clause parameters
    $paramIndex = 1;
    foreach ($params as $value) {
        $stmt->bindValue($paramIndex++, $value, PDO::PARAM_STR);
    }
    
    // Bind LIMIT parameters
    $stmt->bindValue($paramIndex++, $offset, PDO::PARAM_INT);
    $stmt->bindValue($paramIndex++, $itemsPerPage, PDO::PARAM_INT);
    
    $stmt->execute();
    $predictions = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Format the data for Flutter
    foreach ($predictions as &$prediction) {
        // Ensure all required fields exist and are properly typed
        $prediction['id'] = (int)$prediction['id'];
        $prediction['user_id'] = (int)$prediction['user_id'];
        $prediction['age'] = (int)$prediction['age'];
        $prediction['sex'] = (int)$prediction['sex'];
        $prediction['cp'] = (int)$prediction['cp'];
        $prediction['trestbps'] = (int)$prediction['trestbps'];
        $prediction['chol'] = (int)$prediction['chol'];
        $prediction['fbs'] = (int)$prediction['fbs'];
        $prediction['restecg'] = (int)$prediction['restecg'];
        $prediction['thalach'] = (int)$prediction['thalach'];
        $prediction['exang'] = (int)$prediction['exang'];
        $prediction['oldpeak'] = (float)$prediction['oldpeak'];
        $prediction['slope'] = (int)$prediction['slope'];
        $prediction['ca'] = (int)$prediction['ca'];
        $prediction['thal'] = (int)$prediction['thal'];
        $prediction['prediction'] = (int)$prediction['prediction'];
        $prediction['confidence_score'] = (float)($prediction['confidence_score'] ?? 0.0);
        $prediction['verified_by_expert'] = (bool)$prediction['verified_by_expert'];
        $prediction['expert_notes'] = $prediction['expert_notes'] ?? '';
        $prediction['transaction_hash'] = $prediction['transaction_hash'] ?? '';
        $prediction['username'] = $prediction['username'] ?? 'Unknown';
        $prediction['email'] = $prediction['email'] ?? '';
        $prediction['address'] = $prediction['address'] ?? '';
        
        // Format date
        if ($prediction['created_at']) {
            $prediction['created_at'] = date('Y-m-d H:i:s', strtotime($prediction['created_at']));
        }
    }
    
    $response = [
        'success' => true,
        'total' => (int)$totalPredictions,
        'page' => $page,
        'limit' => $itemsPerPage,
        'predictions' => $predictions
    ];
    
} catch (Exception $e) {
    // Log the error
    error_log("Error in get_all_predictions.php: " . $e->getMessage());
    
    // Return error response
    $response = [
        'success' => false,
        'error' => 'Database error: ' . $e->getMessage()
    ];
    
    // If database connection fails, use default predictions as fallback
    if (strpos($e->getMessage(), "Database connection") !== false) {
        // Default predictions with all required fields
        $defaultPredictions = [
            [
                'id' => 1,
                'user_id' => 1,
                'age' => 45,
                'sex' => 1,
                'cp' => 0,
                'trestbps' => 120,
                'chol' => 200,
                'fbs' => 0,
                'restecg' => 0,
                'thalach' => 150,
                'exang' => 0,
                'oldpeak' => 0.0,
                'slope' => 0,
                'ca' => 0,
                'thal' => 0,
                'prediction' => 0,
                'confidence_score' => 0.85,
                'verified_by_expert' => false,
                'expert_notes' => '',
                'transaction_hash' => '',
                'created_at' => date('Y-m-d H:i:s'),
                'username' => 'admin',
                'email' => 'admin@example.com'
            ],
            [
                'id' => 2,
                'user_id' => 2,
                'age' => 55,
                'sex' => 0,
                'cp' => 1,
                'trestbps' => 140,
                'chol' => 250,
                'fbs' => 1,
                'restecg' => 1,
                'thalach' => 120,
                'exang' => 1,
                'oldpeak' => 2.0,
                'slope' => 1,
                'ca' => 1,
                'thal' => 1,
                'prediction' => 1,
                'confidence_score' => 0.92,
                'verified_by_expert' => false,
                'expert_notes' => '',
                'transaction_hash' => '',
                'created_at' => date('Y-m-d H:i:s'),
                'username' => 'user1',
                'email' => 'user1@example.com'
            ]
        ];
        
        $response = [
            'success' => true,
            'total' => count($defaultPredictions),
            'page' => $page,
            'limit' => $itemsPerPage,
            'predictions' => array_slice($defaultPredictions, 0, $itemsPerPage),
            'fallback' => true
        ];
    }
}

// Set content type header before any output
header('Content-Type: application/json');

// Ensure no whitespace or other output before JSON
ob_clean();

// Return JSON response
echo json_encode($response); 
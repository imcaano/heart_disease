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

// Get query parameters and ensure they are integers
$page = max(1, isset($_GET['page']) ? (int)$_GET['page'] : 1);
$itemsPerPage = max(1, isset($_GET['limit']) ? (int)$_GET['limit'] : 10);
$role = $_GET['role'] ?? 'all';

// Calculate offset
$offset = ($page - 1) * $itemsPerPage;

// Default response structure
$response = [
    'success' => true,
    'total' => 0,
    'page' => $page,
    'limit' => $itemsPerPage,
    'users' => []
];

// Try to get real data from database
try {
    // Check if we can connect to database
    if (!isset($pdo)) {
        throw new Exception("Database connection not established");
    }
    
    // Build query
    $whereConditions = [];
    $params = [];
    
    if ($role !== 'all') {
        $whereConditions[] = "role = ?";
        $params[] = $role;
    }
    
    $whereClause = !empty($whereConditions) ? "WHERE " . implode(" AND ", $whereConditions) : "";
    
    // Get total count
    $countSql = "SELECT COUNT(*) as total FROM users $whereClause";
    $countStmt = $pdo->prepare($countSql);
    
    // Bind where clause parameters
    foreach ($params as $key => $value) {
        $countStmt->bindValue($key + 1, $value, PDO::PARAM_STR);
    }
    
    $countStmt->execute();
    $totalUsers = $countStmt->fetchColumn();
    
    // Get users with pagination - include all necessary fields
    $sql = "SELECT 
                id,
                username,
                email,
                wallet_address,
                role,
                status,
                created_at,
                total_predictions,
                prediction_accuracy,
                reputation_score,
                last_login
            FROM users 
            $whereClause 
            ORDER BY created_at DESC 
            LIMIT ?, ?";
    
    $stmt = $pdo->prepare($sql);
    
    // Bind where clause parameters
    $paramIndex = 1;
    foreach ($params as $value) {
        $stmt->bindValue($paramIndex++, $value, PDO::PARAM_STR);
    }
    
    // Bind LIMIT parameters with explicit types
    $stmt->bindValue($paramIndex++, $offset, PDO::PARAM_INT);
    $stmt->bindValue($paramIndex++, $itemsPerPage, PDO::PARAM_INT);
    
    $stmt->execute();
    $users = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Format the data for Flutter
    foreach ($users as &$user) {
        // Ensure all required fields exist
        $user['id'] = (int)$user['id'];
        $user['username'] = $user['username'] ?? 'Unknown';
        $user['email'] = $user['email'] ?? '';
        $user['wallet_address'] = $user['wallet_address'] ?? '';
        $user['role'] = $user['role'] ?? 'user';
        $user['status'] = $user['status'] ?? 'active';
        $user['total_predictions'] = (int)($user['total_predictions'] ?? 0);
        $user['prediction_accuracy'] = (float)($user['prediction_accuracy'] ?? 0.0);
        $user['reputation_score'] = (int)($user['reputation_score'] ?? 0);
        
        // Format dates
        if ($user['created_at']) {
            $user['created_at'] = date('Y-m-d H:i:s', strtotime($user['created_at']));
        }
        if ($user['last_login']) {
            $user['last_login'] = date('Y-m-d H:i:s', strtotime($user['last_login']));
        }
    }
    
    $response = [
        'success' => true,
        'total' => (int)$totalUsers,
        'page' => $page,
        'limit' => $itemsPerPage,
        'users' => $users
    ];
    
} catch (Exception $e) {
    // Log the error
    error_log("Error in get_users.php: " . $e->getMessage());
    
    // Return error response
    $response = [
        'success' => false,
        'error' => 'Database error: ' . $e->getMessage()
    ];
    
    // If database connection fails, use default users as fallback
    if (strpos($e->getMessage(), "Database connection") !== false) {
        // Default users with all required fields
        $defaultUsers = [
            [
                'id' => 1,
                'username' => 'admin',
                'email' => 'admin@example.com',
                'wallet_address' => '0x0000000000000000000000000000000000000000',
                'role' => 'admin',
                'status' => 'active',
                'created_at' => date('Y-m-d H:i:s'),
                'total_predictions' => 0,
                'prediction_accuracy' => 0.0,
                'reputation_score' => 0,
                'last_login' => null
            ],
            [
                'id' => 2,
                'username' => 'user1',
                'email' => 'user1@example.com',
                'wallet_address' => '0xb0a09d11c251c4df082e5129aa7f7f33d85c71fb',
                'role' => 'user',
                'status' => 'active',
                'created_at' => date('Y-m-d H:i:s'),
                'total_predictions' => 0,
                'prediction_accuracy' => 0.0,
                'reputation_score' => 0,
                'last_login' => null
            ]
        ];
        
        // Apply filters to default users
        $filteredUsers = [];
        foreach ($defaultUsers as $user) {
            $roleMatch = $role === 'all' || $user['role'] === $role;
            
            if ($roleMatch) {
                $filteredUsers[] = $user;
            }
        }
        
        $response = [
            'success' => true,
            'total' => count($filteredUsers),
            'page' => $page,
            'limit' => $itemsPerPage,
            'users' => array_slice($filteredUsers, 0, $itemsPerPage),
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
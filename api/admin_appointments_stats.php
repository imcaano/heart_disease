<?php
session_start();

// Required files
require_once dirname(__DIR__) . '/config.php';

// CORS headers for Flutter web
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Headers: Content-Type, Authorization');
header('Access-Control-Allow-Methods: POST, GET, OPTIONS');
header('Content-Type: application/json');

// Handle preflight OPTIONS request
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    exit(0);
}

// Remove token check
// $headers = getallheaders();
// $authHeader = $headers['Authorization'] ?? '';
// $token = '';
// if (strpos($authHeader, 'Bearer ') === 0) {
//     $token = substr($authHeader, 7);
// } else {
//     $token = $authHeader;
// }
// if (!$token) {
//     echo json_encode(['success' => false, 'message' => 'No token']);
//     exit;
// }
// $stmt = $pdo->prepare("SELECT * FROM users WHERE token = ?");
// $stmt->execute([$token]);
// $user = $stmt->fetch(PDO::FETCH_ASSOC);
// if (!$user || $user['role'] !== 'admin') {
//     echo json_encode(['success' => false, 'message' => 'Unauthorized access']);
//     exit;
// }

try {
    // Check if appointments table exists
    $stmt = $pdo->query("SHOW TABLES LIKE 'appointments'");
    if ($stmt->rowCount() == 0) {
        throw new Exception('Appointments table does not exist');
    }

    // Get appointment statistics
    $stats_query = "
        SELECT 
            COUNT(*) as total,
            SUM(CASE WHEN status = 'pending' THEN 1 ELSE 0 END) as pending,
            SUM(CASE WHEN status = 'approved' THEN 1 ELSE 0 END) as approved,
            SUM(CASE WHEN status = 'rejected' THEN 1 ELSE 0 END) as rejected,
            SUM(CASE WHEN status = 'completed' THEN 1 ELSE 0 END) as completed
        FROM appointments
    ";
    
    $stats_stmt = $pdo->query($stats_query);
    $stats = $stats_stmt->fetch(PDO::FETCH_ASSOC);

    // Get all appointments with user details and prediction results
    $status_filter = $_GET['status'] ?? '';
    $where_clause = '';
    $params = [];
    
    if ($status_filter && $status_filter !== 'all') {
        $where_clause = "WHERE a.status = ?";
        $params[] = $status_filter;
    }

    $query = "
        SELECT a.*, u.username, u.email as user_email, p.prediction as prediction_result
        FROM appointments a
        LEFT JOIN users u ON a.user_id = u.id
        LEFT JOIN predictions p ON a.prediction_id = p.id
        $where_clause
        ORDER BY a.created_at DESC
    ";

    $stmt = $pdo->prepare($query);
    $stmt->execute($params);
    $appointments = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Format appointments for better display
    foreach ($appointments as &$appointment) {
        $appointment['formatted_date'] = date('M d, Y', strtotime($appointment['appointment_date']));
        $appointment['formatted_time'] = date('g:i A', strtotime($appointment['appointment_time']));
        $appointment['formatted_created'] = date('M j, Y g:i A', strtotime($appointment['created_at']));
        
        // Add status color for UI
        switch ($appointment['status']) {
            case 'pending':
                $appointment['status_color'] = 'orange';
                break;
            case 'approved':
                $appointment['status_color'] = 'green';
                break;
            case 'rejected':
                $appointment['status_color'] = 'red';
                break;
            case 'completed':
                $appointment['status_color'] = 'blue';
                break;
            default:
                $appointment['status_color'] = 'gray';
        }
    }

    echo json_encode([
        'success' => true,
        'stats' => $stats,
        'appointments' => $appointments,
        'total' => count($appointments)
    ]);

} catch (Exception $e) {
    error_log("Admin appointments stats error: " . $e->getMessage());
    header('Content-Type: application/json');
    echo json_encode([
        'success' => false,
        'message' => 'Error fetching admin appointments: ' . $e->getMessage()
    ]);
}
?>

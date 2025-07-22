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

// Get POST data
$data = json_decode(file_get_contents('php://input'), true);
$appointment_id = $data['appointment_id'] ?? null;
$status = $data['status'] ?? null;
$admin_notes = $data['admin_notes'] ?? null;

if (!$appointment_id || !$status) {
    echo json_encode(['success' => false, 'message' => 'Missing required fields']);
    exit;
}

try {
    // Check if appointments table exists
    $stmt = $pdo->query("SHOW TABLES LIKE 'appointments'");
    if ($stmt->rowCount() == 0) {
        throw new Exception('Appointments table does not exist');
    }

    // Update appointment status
    $update_query = "UPDATE appointments SET status = ?, updated_at = NOW()";
    $params = [$status];
    
    if ($admin_notes !== null) {
        $update_query .= ", admin_notes = ?";
        $params[] = $admin_notes;
    }
    
    $update_query .= " WHERE id = ?";
    $params[] = $appointment_id;

    $stmt = $pdo->prepare($update_query);
    $result = $stmt->execute($params);

    if ($result && $stmt->rowCount() > 0) {
    echo json_encode([
        'success' => true, 
            'message' => 'Appointment status updated successfully'
        ]);
    } else {
        echo json_encode([
            'success' => false,
            'message' => 'Appointment not found or no changes made'
        ]);
    }

} catch (Exception $e) {
    error_log("Update appointment status error: " . $e->getMessage());
    echo json_encode([
        'success' => false,
        'message' => 'Error updating appointment status: ' . $e->getMessage()
    ]);
}
?> 
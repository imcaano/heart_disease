<?php
session_start();

// Required files
require_once dirname(__DIR__) . '/config.php';

// Check if user is logged in or user_id is provided for API
$user_id_param = $_GET['user_id'] ?? $_POST['user_id'] ?? null;
if (!isset($_SESSION['user']) && !$user_id_param) {
    header('Content-Type: application/json');
    echo json_encode(['success' => false, 'message' => 'User not logged in']);
    exit;
}

try {
    $user_id = $_SESSION['user']['id'] ?? $user_id_param;
    $user_role = $_SESSION['user']['role'] ?? 'user';
    // If user_id_param is set, only allow if admin or the same user
    if ($user_id_param && isset($_SESSION['user']) && $_SESSION['user']['role'] !== 'admin' && $_SESSION['user']['id'] != $user_id_param) {
        echo json_encode(['success' => false, 'message' => 'Unauthorized']);
        exit;
    }
    
    // Check if appointments table exists
    $stmt = $pdo->query("SHOW TABLES LIKE 'appointments'");
    if ($stmt->rowCount() == 0) {
        throw new Exception('Appointments table does not exist');
    }

    // Handle POST request for specific appointment details
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $input = json_decode(file_get_contents('php://input'), true);
        $appointment_id = $input['appointment_id'] ?? null;
        
        if (!$appointment_id) {
            echo json_encode(['success' => false, 'message' => 'Appointment ID is required']);
            exit;
        }

        // Get specific appointment details
        $stmt = $pdo->prepare("
            SELECT a.*, u.username, u.email as user_email, p.prediction_result as prediction_result
            FROM appointments a
            LEFT JOIN users u ON a.user_id = u.id
            LEFT JOIN predictions p ON a.prediction_id = p.id
            WHERE a.id = ? AND (a.user_id = ? OR ? = 'admin')
        ");
        $stmt->execute([$appointment_id, $user_id, $user_role]);
        $appointment = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$appointment) {
            echo json_encode(['success' => false, 'message' => 'Appointment not found']);
            exit;
        }

        echo json_encode([
            'success' => true,
            'appointment' => $appointment
        ]);
        exit;
    }

    // Handle GET request for user's appointments or all appointments (admin)
    $status_filter = $_GET['status'] ?? null;
    
    if ($user_role === 'admin') {
        // Admin can see all appointments
        $sql = "
            SELECT a.*, u.username, u.email as user_email, p.prediction_result as prediction_result
            FROM appointments a
            LEFT JOIN users u ON a.user_id = u.id
            LEFT JOIN predictions p ON a.prediction_id = p.id
            ORDER BY a.created_at DESC
        ";
        $params = [];
        
        if ($status_filter && $status_filter !== 'all') {
            $sql = "
                SELECT a.*, u.username, u.email as user_email, p.prediction_result as prediction_result
                FROM appointments a
                LEFT JOIN users u ON a.user_id = u.id
                LEFT JOIN predictions p ON a.prediction_id = p.id
                WHERE a.status = ?
                ORDER BY a.created_at DESC
            ";
            $params = [$status_filter];
        }
        
        $stmt = $pdo->prepare($sql);
        $stmt->execute($params);
    } else {
        // Regular users can only see their own appointments
        $sql = "
            SELECT a.*, u.username, u.email as user_email, p.prediction_result as prediction_result
            FROM appointments a
            LEFT JOIN users u ON a.user_id = u.id
            LEFT JOIN predictions p ON a.prediction_id = p.id
            WHERE a.user_id = ?
            ORDER BY a.created_at DESC
        ";
        $params = [$user_id];
        
        if ($status_filter && $status_filter !== 'all') {
            $sql = "
                SELECT a.*, u.username, u.email as user_email, p.prediction_result as prediction_result
                FROM appointments a
                LEFT JOIN users u ON a.user_id = u.id
                LEFT JOIN predictions p ON a.prediction_id = p.id
                WHERE a.user_id = ? AND a.status = ?
                ORDER BY a.created_at DESC
            ";
            $params = [$user_id, $status_filter];
        }
        
        $stmt = $pdo->prepare($sql);
        $stmt->execute($params);
    }

    $appointments = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Format dates for better display
    foreach ($appointments as &$appointment) {
        $appointment['formatted_date'] = date('F j, Y', strtotime($appointment['appointment_date']));
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
        'appointments' => $appointments,
        'total' => count($appointments)
    ]);

} catch (Exception $e) {
    error_log("Get appointment details error: " . $e->getMessage());
    header('Content-Type: application/json');
    echo json_encode([
        'success' => false,
        'message' => 'Error fetching appointments: ' . $e->getMessage()
    ]);
}
?> 
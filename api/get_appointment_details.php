<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
header('Content-Type: application/json');

// Check if user is logged in and is admin
if (!isset($_SESSION['user']) || $_SESSION['user']['role'] !== 'admin') {
    echo json_encode(['success' => false, 'message' => 'Unauthorized access']);
    exit;
}

// Check if request method is POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Invalid request method']);
    exit;
}

// Include database configuration
require_once '../config.php';

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // Get appointment ID
    $appointment_id = $_POST['appointment_id'] ?? '';

    if (empty($appointment_id)) {
        echo json_encode(['success' => false, 'message' => 'Appointment ID is required']);
        exit;
    }

    // Get appointment details with user and prediction information
    $stmt = $pdo->prepare("
        SELECT a.*, u.username, u.email as user_email, p.prediction as prediction_result
        FROM appointments a
        LEFT JOIN users u ON a.user_id = u.id
        LEFT JOIN predictions p ON a.prediction_id = p.id
        WHERE a.id = ?
    ");
    
    $stmt->execute([$appointment_id]);
    $appointment = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$appointment) {
        echo json_encode(['success' => false, 'message' => 'Appointment not found']);
        exit;
    }

    // Generate HTML for modal
    $html = '
    <div class="row">
        <div class="col-md-6">
            <div class="info-row">
                <span class="info-label">Patient Name:</span>
                <span class="info-value">' . htmlspecialchars($appointment['patient_name']) . '</span>
            </div>
            <div class="info-row">
                <span class="info-label">Email:</span>
                <span class="info-value">' . htmlspecialchars($appointment['patient_email']) . '</span>
            </div>
            <div class="info-row">
                <span class="info-label">Phone:</span>
                <span class="info-value">' . htmlspecialchars($appointment['patient_phone']) . '</span>
            </div>
            <div class="info-row">
                <span class="info-label">Appointment Date:</span>
                <span class="info-value">' . date('l, F d, Y', strtotime($appointment['appointment_date'])) . '</span>
            </div>
            <div class="info-row">
                <span class="info-label">Appointment Time:</span>
                <span class="info-value">' . date('g:i A', strtotime($appointment['appointment_time'])) . '</span>
            </div>
        </div>
        <div class="col-md-6">
            <div class="info-row">
                <span class="info-label">Status:</span>
                <span class="info-value">
                    <span class="status-badge status-' . $appointment['status'] . '">' . ucfirst($appointment['status']) . '</span>
                </span>
            </div>
            <div class="info-row">
                <span class="info-label">Booked By:</span>
                <span class="info-value">' . htmlspecialchars($appointment['username']) . '</span>
            </div>
            <div class="info-row">
                <span class="info-label">User Email:</span>
                <span class="info-value">' . htmlspecialchars($appointment['user_email']) . '</span>
            </div>
            <div class="info-row">
                <span class="info-label">Booked On:</span>
                <span class="info-value">' . date('M d, Y g:i A', strtotime($appointment['created_at'])) . '</span>
            </div>
            <div class="info-row">
                <span class="info-label">Prediction Result:</span>
                <span class="info-value">' . ($appointment['prediction_result'] ? '<span class="text-danger">High Risk</span>' : '<span class="text-success">Low Risk</span>') . '</span>
            </div>
        </div>
    </div>
    
    <div class="row mt-3">
        <div class="col-12">
            <div class="info-row">
                <span class="info-label">Address:</span>
                <span class="info-value">' . htmlspecialchars($appointment['address']) . '</span>
            </div>
        </div>
    </div>
    
    <div class="row mt-3">
        <div class="col-12">
            <div class="info-row">
                <span class="info-label">Reason for Consultation:</span>
                <span class="info-value">' . htmlspecialchars($appointment['reason']) . '</span>
            </div>
        </div>
    </div>';

    if ($appointment['admin_notes']) {
        $html .= '
        <div class="row mt-3">
            <div class="col-12">
                <div class="info-row">
                    <span class="info-label">Admin Notes:</span>
                    <span class="info-value">' . htmlspecialchars($appointment['admin_notes']) . '</span>
                </div>
            </div>
        </div>';
    }

    // Add action buttons if appointment is pending
    if ($appointment['status'] === 'pending') {
        $html .= '
        <div class="row mt-4">
            <div class="col-12 text-center">
                <button class="btn btn-success me-2" onclick="updateStatus(' . $appointment_id . ', \'approved\')">
                    <i class="fas fa-check me-2"></i>Approve
                </button>
                <button class="btn btn-danger" onclick="updateStatus(' . $appointment_id . ', \'rejected\')">
                    <i class="fas fa-times me-2"></i>Reject
                </button>
            </div>
        </div>';
    } elseif ($appointment['status'] === 'approved') {
        $html .= '
        <div class="row mt-4">
            <div class="col-12 text-center">
                <button class="btn btn-primary" onclick="updateStatus(' . $appointment_id . ', \'completed\')">
                    <i class="fas fa-check-double me-2"></i>Mark as Completed
                </button>
            </div>
        </div>';
    }

    echo json_encode([
        'success' => true,
        'html' => $html,
        'appointment' => $appointment
    ]);

} catch (PDOException $e) {
    error_log("Database error: " . $e->getMessage());
    echo json_encode(['success' => false, 'message' => 'Database error occurred. Please try again.']);
} catch (Exception $e) {
    error_log("General error: " . $e->getMessage());
    echo json_encode(['success' => false, 'message' => 'An error occurred. Please try again.']);
}
?> 
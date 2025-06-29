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

// Check if request method is POST (allow both POST and when called directly)
$request_method = $_SERVER['REQUEST_METHOD'] ?? 'POST';
if ($request_method !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Invalid request method']);
    exit;
}

// Include database configuration
require_once '../config.php';

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // Get form data
    $appointment_id = $_POST['appointment_id'] ?? '';
    $status = $_POST['status'] ?? '';
    $admin_notes = $_POST['admin_notes'] ?? '';

    // Validate required fields
    if (empty($appointment_id) || empty($status)) {
        echo json_encode(['success' => false, 'message' => 'Appointment ID and status are required']);
        exit;
    }

    // Validate status
    $valid_statuses = ['pending', 'approved', 'rejected', 'completed', 'cancelled'];
    if (!in_array($status, $valid_statuses)) {
        echo json_encode(['success' => false, 'message' => 'Invalid status']);
        exit;
    }

    // Check if appointment exists
    $stmt = $pdo->prepare("SELECT * FROM appointments WHERE id = ?");
    $stmt->execute([$appointment_id]);
    $appointment = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$appointment) {
        echo json_encode(['success' => false, 'message' => 'Appointment not found']);
        exit;
    }

    // Update appointment status
    $stmt = $pdo->prepare("
        UPDATE appointments 
        SET status = ?, admin_notes = ?, updated_at = NOW() 
        WHERE id = ?
    ");

    $stmt->execute([$status, $admin_notes, $appointment_id]);

    // Send notification email to user (optional)
    if ($status === 'approved' || $status === 'rejected') {
        sendStatusUpdateEmail($appointment, $status);
    }

    echo json_encode([
        'success' => true, 
        'message' => 'Appointment status updated successfully!'
    ]);

} catch (PDOException $e) {
    error_log("Database error: " . $e->getMessage());
    echo json_encode(['success' => false, 'message' => 'Database error occurred. Please try again.']);
} catch (Exception $e) {
    error_log("General error: " . $e->getMessage());
    echo json_encode(['success' => false, 'message' => 'An error occurred. Please try again.']);
}

// Function to send status update email (placeholder)
function sendStatusUpdateEmail($appointment, $status) {
    $email = $appointment['patient_email'];
    $name = $appointment['patient_name'];
    $date = $appointment['appointment_date'];
    $time = $appointment['appointment_time'];
    
    $status_text = ucfirst($status);
    
    // This can be implemented with PHPMailer or similar library
    // For now, just log the email details
    error_log("Status update email would be sent to: $email for $name - Appointment $status_text for $date at $time");
}
?> 
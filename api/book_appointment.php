<?php
// CORS headers for API access from Flutter/web
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Headers: Content-Type");
header("Access-Control-Allow-Methods: POST, GET, OPTIONS");

// Prevent any output before JSON response
ob_clean();
error_reporting(E_ALL);
ini_set('display_errors', 0);

// Test if we can output clean JSON
if (isset($_GET['test'])) {
    echo json_encode(['test' => 'success']);
    exit;
}

try {
    session_start();
    header('Content-Type: application/json');

    // Accept JSON POST data as $_POST
    if (isset($_SERVER['CONTENT_TYPE']) && strpos($_SERVER['CONTENT_TYPE'], 'application/json') !== false) {
        $_POST = json_decode(file_get_contents('php://input'), true) ?? [];
    }

    // DEV workaround: allow user_id from POST for API calls (not secure for production)
    if (!isset($_SESSION['user']) && !isset($_POST['user_id'])) {
        echo json_encode(['success' => false, 'message' => 'User not logged in']);
        exit;
    }
    $user_id = isset($_SESSION['user']) ? $_SESSION['user']['id'] : $_POST['user_id'];

    // Check if request method is POST
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        echo json_encode(['success' => false, 'message' => 'Invalid request method']);
        exit;
    }

    // Include database configuration
    if (!file_exists(__DIR__ . '/../config.php')) {
        throw new Exception('Config file not found at: ' . __DIR__ . '/../config.php');
    }
    require_once __DIR__ . '/../config.php';

    // Test database connection
    if (!isset($pdo) || !$pdo) {
        throw new Exception('Database connection failed');
    }

    // Get JSON data from request body
    $input = json_decode(file_get_contents('php://input'), true);
    // Fallback for web form submissions
    if (!$input && !empty($_POST)) {
        $input = $_POST;
    }
    if (!$input) {
        echo json_encode(['success' => false, 'message' => 'Invalid JSON data']);
        exit;
    }

    // Get form data from JSON
    $patient_name = $input['patient_name'] ?? '';
    $patient_email = $input['patient_email'] ?? '';
    $patient_phone = $input['patient_phone'] ?? '';
    $appointment_date = $input['appointment_date'] ?? '';
    $appointment_time = $input['appointment_time'] ?? '';
    $address = $input['address'] ?? '';
    $reason = $input['reason'] ?? '';
    $prediction_id = $input['prediction_id'] ?? null;
    $prediction_result = $input['prediction_result'] ?? null;

    // Debug logging
    error_log("Book appointment data received: " . json_encode([
        'user_id' => $user_id,
        'patient_name' => $patient_name,
        'patient_email' => $patient_email,
        'patient_phone' => $patient_phone,
        'appointment_date' => $appointment_date,
        'appointment_time' => $appointment_time,
        'address' => substr($address, 0, 50) . '...',
        'reason' => substr($reason, 0, 50) . '...',
        'prediction_id' => $prediction_id,
        'prediction_result' => $prediction_result
    ]));

    // Validate required fields
    if (empty($patient_name) || empty($patient_email) || empty($patient_phone) || 
        empty($appointment_date) || empty($appointment_time) || empty($address) || empty($reason)) {
        echo json_encode(['success' => false, 'message' => 'All fields are required']);
        exit;
    }

    // Validate email format
    if (!filter_var($patient_email, FILTER_VALIDATE_EMAIL)) {
        echo json_encode(['success' => false, 'message' => 'Invalid email format']);
        exit;
    }

    // Validate date (must be in the future)
    $appointment_datetime = $appointment_date . ' ' . $appointment_time;
    if (strtotime($appointment_datetime) <= time()) {
        echo json_encode(['success' => false, 'message' => 'Appointment date and time must be in the future']);
        exit;
    }

    // Check if appointments table exists
    $stmt = $pdo->query("SHOW TABLES LIKE 'appointments'");
    if ($stmt->rowCount() == 0) {
        throw new Exception('Appointments table does not exist');
    }

    // Check if the time slot is available (basic check - can be enhanced)
    $stmt = $pdo->prepare("
        SELECT COUNT(*) FROM appointments 
        WHERE appointment_date = ? AND appointment_time = ? AND status IN ('pending', 'approved')
    ");
    $stmt->execute([$appointment_date, $appointment_time]);
    $existing_appointments = $stmt->fetchColumn();

    if ($existing_appointments > 0) {
        echo json_encode(['success' => false, 'message' => 'This time slot is already booked. Please select a different time.']);
        exit;
    }

    // Insert appointment into database
    $stmt = $pdo->prepare("
        INSERT INTO appointments (
            user_id, patient_name, patient_email, patient_phone, 
            appointment_date, appointment_time, address, reason, 
            prediction_id, prediction_result, status, created_at
        ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 'pending', NOW())
    ");

    $stmt->execute([
        $user_id, $patient_name, $patient_email, $patient_phone,
        $appointment_date, $appointment_time, $address, $reason,
        $prediction_id, $prediction_result
    ]);

    $appointment_id = $pdo->lastInsertId();

    // Send confirmation email (optional - can be implemented later)
    // sendAppointmentConfirmationEmail($patient_email, $patient_name, $appointment_date, $appointment_time);

    echo json_encode([
        'success' => true, 
        'message' => 'Appointment booked successfully!',
        'appointment_id' => $appointment_id
    ]);

} catch (PDOException $e) {
    error_log("Book appointment DB error: " . $e->getMessage());
    echo json_encode(['success' => false, 'message' => 'Database error: ' . $e->getMessage()]);
} catch (Exception $e) {
    error_log("Book appointment general error: " . $e->getMessage());
    echo json_encode(['success' => false, 'message' => 'An error occurred: ' . $e->getMessage()]);
}

// Function to send confirmation email (placeholder)
function sendAppointmentConfirmationEmail($email, $name, $date, $time) {
    // This can be implemented with PHPMailer or similar library
    // For now, just log the email details
    error_log("Appointment confirmation email would be sent to: $email for $name on $date at $time");
}
?> 
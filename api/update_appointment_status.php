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

    // After updating the appointment status, if approved, send confirmation email
    if ($result && $stmt->rowCount() > 0) {
        // Fetch appointment details to get user email
        $stmt2 = $pdo->prepare("SELECT patient_email, patient_name, appointment_date, appointment_time FROM appointments WHERE id = ?");
        $stmt2->execute([$appointment_id]);
        $appointment = $stmt2->fetch(PDO::FETCH_ASSOC);
        if ($status === 'approved' && $appointment) {
            require_once dirname(__DIR__) . '/vendor/autoload.php';
            $mail = new PHPMailer\PHPMailer\PHPMailer(true);
            try {
                $mail->isSMTP();
                $mail->Host = 'smtp.gmail.com';
                $mail->SMTPAuth = true;
                $mail->Username = 'heartdissease@gmail.com';
                $mail->Password = 'kkpl lokn dulv pzvg';
                $mail->SMTPSecure = PHPMailer\PHPMailer\PHPMailer::ENCRYPTION_STARTTLS;
                $mail->Port = 587;
                $mail->setFrom('heartdissease@gmail.com', 'Heart Disease Clinic');
                $mail->addAddress($appointment['patient_email'], $appointment['patient_name']);
                $mail->isHTML(true);
                $mail->Subject = 'Your Appointment Has Been Approved';
                $mail->Body = '<p>Dear ' . htmlspecialchars($appointment['patient_name']) . ',</p>' .
                    '<p>Your appointment on <b>' . htmlspecialchars($appointment['appointment_date']) . '</b> at <b>' . htmlspecialchars($appointment['appointment_time']) . '</b> has been <b>approved</b> by our admin.</p>' .
                    '<p>Thank you for choosing our clinic.</p>' .
                    '<p>Best regards,<br>Heart Disease Clinic Team</p>';
                $mail->send();
            } catch (Exception $e) {
                error_log('PHPMailer error: ' . $mail->ErrorInfo);
            }
        }
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
<?php
session_start();
require_once dirname(__DIR__) . '/config.php';

header('Content-Type: application/json');

// Helper: get Authorization token from header
function getBearerToken() {
    $headers = getallheaders();
    if (isset($headers['Authorization']) && strpos($headers['Authorization'], 'Bearer ') === 0) {
        return substr($headers['Authorization'], 7);
    } elseif (isset($headers['authorization']) && strpos($headers['authorization'], 'Bearer ') === 0) {
        return substr($headers['authorization'], 7);
    }
    return null;
}

$user_id = null;

// 1. Try session
if (isset($_SESSION['user']['id'])) {
    $user_id = $_SESSION['user']['id'];
}
// 2. Try token
else {
    $token = getBearerToken();
    if ($token) {
        $stmt = $pdo->prepare('SELECT id FROM users WHERE token = ?');
        $stmt->execute([$token]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);
        if ($user) {
            $user_id = $user['id'];
        }
    }
}

if (!$user_id) {
    echo json_encode(['success' => false, 'message' => 'User not logged in']);
    exit;
}

// Only allow POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Invalid request method']);
    exit;
}

try {
    $input = json_decode(file_get_contents('php://input'), true);
    if (!$input) {
        throw new Exception('Invalid JSON data');
    }

    $current_password = $input['current_password'] ?? '';
    $new_password = $input['new_password'] ?? '';
    $confirm_password = $input['confirm_password'] ?? '';

    if (empty($current_password) || empty($new_password) || empty($confirm_password)) {
        throw new Exception('All password fields are required');
    }
    if ($new_password !== $confirm_password) {
        throw new Exception('New passwords do not match');
    }
    if (strlen($new_password) < 6) {
        throw new Exception('New password must be at least 6 characters');
    }

    // Fetch user from DB
    $stmt = $pdo->prepare('SELECT password FROM users WHERE id = ?');
    $stmt->execute([$user_id]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);
    if (!$user) {
        throw new Exception('User not found');
    }

    // Verify current password
    if (!password_verify($current_password, $user['password'])) {
        throw new Exception('Current password is incorrect');
    }

    // Hash new password
    $hashed = password_hash($new_password, PASSWORD_DEFAULT);
    $stmt = $pdo->prepare('UPDATE users SET password = ? WHERE id = ?');
    $stmt->execute([$hashed, $user_id]);

    // Log activity
    $stmt = $pdo->prepare('INSERT INTO user_activity_log (user_id, activity_type, description, ip_address) VALUES (?, ?, ?, ?)');
    $stmt->execute([$user_id, 'password_update', 'Password updated', $_SERVER['REMOTE_ADDR']]);

    echo json_encode(['success' => true, 'message' => 'Password updated successfully']);
} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
} 
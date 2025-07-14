<?php
session_start();

// Required files
require_once dirname(__DIR__) . '/config.php';

// Check if user is logged in
if (!isset($_SESSION['user'])) {
    header('Content-Type: application/json');
    echo json_encode(['success' => false, 'message' => 'User not logged in']);
    exit;
}

// Check if request method is POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Content-Type: application/json');
    echo json_encode(['success' => false, 'message' => 'Invalid request method']);
    exit;
}

try {
    // Get JSON data from request body
    $input = json_decode(file_get_contents('php://input'), true);
    
    if (!$input) {
        throw new Exception('Invalid JSON data');
    }

    $user_id = $_SESSION['user']['id'];
    $username = $input['username'] ?? '';
    $email = $input['email'] ?? '';

    // Validate input
    if (empty($username) || empty($email)) {
        throw new Exception('Username and email are required');
    }

    // Validate email format
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        throw new Exception('Invalid email format');
    }

    // Check if username already exists (excluding current user)
    $stmt = $pdo->prepare("SELECT id FROM users WHERE username = ? AND id != ?");
    $stmt->execute([$username, $user_id]);
    if ($stmt->fetch()) {
        throw new Exception('Username already exists');
    }

    // Check if email already exists (excluding current user)
    $stmt = $pdo->prepare("SELECT id FROM users WHERE email = ? AND id != ?");
    $stmt->execute([$email, $user_id]);
    if ($stmt->fetch()) {
        throw new Exception('Email already exists');
    }

    // Update user information
    $stmt = $pdo->prepare("UPDATE users SET username = ?, email = ? WHERE id = ?");
    $stmt->execute([$username, $email, $user_id]);

    if ($stmt->rowCount() > 0) {
        // Update session data
        $_SESSION['user']['username'] = $username;
        $_SESSION['user']['email'] = $email;

        // Log activity
        $stmt = $pdo->prepare("INSERT INTO user_activity_log (user_id, activity_type, description, ip_address) VALUES (?, 'profile_update', 'Profile information updated', ?)");
        $stmt->execute([$user_id, $_SERVER['REMOTE_ADDR']]);

        echo json_encode([
            'success' => true,
            'message' => 'Profile updated successfully',
            'user' => [
                'username' => $username,
                'email' => $email
            ]
        ]);
    } else {
        throw new Exception('No changes were made to the profile');
    }

} catch (Exception $e) {
    header('Content-Type: application/json');
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
}
?> 
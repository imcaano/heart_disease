<?php
// CORS headers for Flutter web
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Headers: Content-Type, Authorization');
header('Access-Control-Allow-Methods: POST, GET, OPTIONS');
header('Content-Type: application/json');

// Handle preflight OPTIONS request
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    exit(0);
}

// Start session
session_start();

// Database connection (update with your credentials)
$host = 'localhost';
$db   = 'heart_disease';
$user = 'root';
$pass = '';
$charset = 'utf8mb4';

$dsn = "mysql:host=$host;dbname=$db;charset=$charset";
$options = [
    PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    PDO::ATTR_EMULATE_PREPARES   => false,
];

try {
    $pdo = new PDO($dsn, $user, $pass, $options);
} catch (\PDOException $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Database connection failed']);
    exit;
}

// Get POST data
$data = json_decode(file_get_contents('php://input'), true);
$username = $data['username'] ?? '';
$password = $data['password'] ?? '';
$wallet_address = $data['wallet_address'] ?? '';

if (!$username || !$password || !$wallet_address) {
    echo json_encode(['success' => false, 'message' => 'Missing required fields']);
    exit;
}

// Check user in database
$stmt = $pdo->prepare("SELECT * FROM users WHERE username = ? AND wallet_address = ?");
$stmt->execute([$username, $wallet_address]);
$user = $stmt->fetch();

if ($user && password_verify($password, $user['password'])) {
    // Store user in session
    $_SESSION['user'] = [
        'id' => $user['id'],
        'username' => $user['username'],
        'email' => $user['email'],
        'wallet_address' => $user['wallet_address'],
        'role' => $user['role']
    ];
    
    // Update last login
    $stmt = $pdo->prepare("UPDATE users SET last_login = NOW() WHERE id = ?");
    $stmt->execute([$user['id']]);
    
    echo json_encode(['success' => true, 'message' => 'Login successful', 'user' => $_SESSION['user']]);
} else {
    echo json_encode(['success' => false, 'message' => 'Invalid credentials or wallet address']);
}
?> 
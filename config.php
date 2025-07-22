<?php
require_once 'vendor/autoload.php';
require_once 'includes/functions.php';

// Load environment variables if .env exists
if (file_exists(__DIR__ . '/.env')) {
    $dotenv = Dotenv\Dotenv::createImmutable(__DIR__);
    $dotenv->load();
}

// Site configuration
define('SITE_NAME', 'Heart Disease Prediction');
define('SITE_URL', 'http://localhost/heart_disease');

// Database configuration
$host = 'localhost';
$dbname = 'heart_disease';
$username = 'root';
$password = '';

// Enable error reporting for debugging
ini_set('display_errors', 0);
ini_set('display_startup_errors', 0);
error_reporting(E_ALL);

// Time zone
date_default_timezone_set('Asia/Manila');

// Start session if not already started
if (session_status() === PHP_SESSION_NONE) {
session_start();
}

// Database connection function
function getDBConnection() {
    global $host, $dbname, $username, $password;
    
    try {
        $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8mb4", $username, $password);
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        $pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
        return $pdo;
    } catch (PDOException $e) {
        error_log("Database connection failed: " . $e->getMessage());
        return false;
    }
}

// Create database connection
try {
    $pdo = getDBConnection();
    $db = $pdo; // For backward compatibility
} catch (Exception $e) {
    // Log the error
    error_log("Failed to connect to database: " . $e->getMessage());
    
    // If this is an API endpoint, return JSON error
    if (strpos($_SERVER['REQUEST_URI'], '/api/') !== false) {
        header('Content-Type: application/json');
        http_response_code(500);
        echo json_encode(['error' => 'Database connection failed']);
        exit;
    }
}

// Smart contract configuration
define('CONTRACT_ADDRESS', '0x0000000000000000000000000000000000000000');
define('CONTRACT_ABI', json_encode([
    // Add your contract ABI here
]));

// Web3 configuration
define('WEB3_PROVIDER_URI', $_ENV['WEB3_PROVIDER_URI'] ?? 'http://127.0.0.1:8545');

// Global constants
define('APP_ROOT', __DIR__);
define('APP_URL', 'http://localhost/heart_disease');

// Python configuration
define('PYTHON_PATH', 'python');  // Use system Python
define('PREDICT_SCRIPT', __DIR__ . '/predict.py');
define('TEMP_DIR', __DIR__ . '/temp');

// Create temp directory if it doesn't exist
if (!file_exists(TEMP_DIR)) {
    mkdir(TEMP_DIR, 0777, true);
}

// Helper function to validate admin access
function isAdmin() {
    return isset($_SESSION['user']) && in_array($_SESSION['user']['role'], ['admin', 'developer']);
}

// Helper function to sanitize output
function h($string) {
    return htmlspecialchars($string, ENT_QUOTES, 'UTF-8');
}
?> 
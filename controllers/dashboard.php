<?php
require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/../models/user.php';
require_once __DIR__ . '/../models/prediction.php';

function getDashboardData() {
    global $pdo;
    
    if (!isset($_SESSION['user_id'])) {
        header('Location: ' . SITE_URL . '/login.php');
        exit();
    }

    $user = new User($pdo);
    $prediction = new Prediction($pdo);
    
    // Get user data if not in session
    if (!isset($_SESSION['user_data'])) {
        $_SESSION['user_data'] = $user->getById($_SESSION['user_id']);
    }

    $userData = $_SESSION['user_data'];
    
    if ($userData['role'] === 'admin') {
        return [
            'total_users' => $user->count(),
            'total_predictions' => $prediction->getTotalPredictions(),
            'positive_predictions' => $prediction->getPositivePredictions(),
            'negative_predictions' => $prediction->getNegativePredictions(),
            'recent_predictions' => $prediction->getRecentPredictions(5),
            'recent_users' => $user->getRecentUsers(5)
        ];
    } else {
        $userStats = $prediction->getUserStats($_SESSION['user_id']);
        return [
            'total_predictions' => $userStats['total_predictions'] ?? 0,
            'positive_predictions' => $userStats['positive_predictions'] ?? 0,
            'negative_predictions' => $userStats['negative_predictions'] ?? 0,
            'average_probability' => $userStats['average_probability'] ?? 0,
            'recent_predictions' => $prediction->getUserPredictions($_SESSION['user_id'], 5)
        ];
    }
}

function handleUserAction() {
    global $pdo;
    
    if (!isset($_SESSION['user_id'])) {
        header('Location: ' . SITE_URL . '/login.php');
        exit();
    }

    $user = new User($pdo);
    $userData = $user->getById($_SESSION['user_id']);
    
    if ($userData['role'] !== 'admin') {
        header('Location: ' . SITE_URL . '/dashboard.php');
        exit();
    }

    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $action = $_POST['action'] ?? '';
        $userId = $_POST['user_id'] ?? 0;

        switch ($action) {
            case 'delete':
                if ($user->delete($userId)) {
                    $_SESSION['success'] = 'User deleted successfully';
                } else {
                    $_SESSION['error'] = 'Failed to delete user';
                }
                break;

            case 'toggle_status':
                if ($user->toggleStatus($userId)) {
                    $_SESSION['success'] = 'User status updated successfully';
                } else {
                    $_SESSION['error'] = 'Failed to update user status';
                }
                break;
        }

        header('Location: ' . SITE_URL . '/dashboard.php');
        exit();
    }
}

// Handle AJAX requests
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['ajax'])) {
    header('Content-Type: application/json');
    echo json_encode(handleUserAction());
    exit;
}

// Get dashboard data
$dashboardData = getDashboardData();

// Include the dashboard view
require_once 'templates/dashboard.php'; 
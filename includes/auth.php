<?php
// Start or resume session
session_start();

// Required configuration
require_once __DIR__ . '/../config.php';

/**
 * Check if user is logged in
 * @return bool True if user is logged in, false otherwise
 */
function isLoggedIn() {
    return isset($_SESSION['user_id']);
}

/**
 * Check if user is admin
 * @return bool True if user is admin, false otherwise
 */
function isAdmin() {
    return isset($_SESSION['user']) && in_array($_SESSION['user']['role'], ['admin', 'developer']);
}

/**
 * Check if user has required permission
 * @param string $permission Permission to check
 * @return bool True if user has permission, false otherwise
 */
function hasPermission($permission) {
    if (!isset($_SESSION['user']) || !isset($_SESSION['user']['role'])) {
        return false;
    }
    
    global $pdo;
    
    $stmt = $pdo->prepare("
        SELECT COUNT(*) as count 
        FROM user_permissions 
        WHERE role = ? AND permission = ?
    ");
    
    $stmt->execute([$_SESSION['user']['role'], $permission]);
    $result = $stmt->fetch();
    
    return $result && $result['count'] > 0;
}

/**
 * Require login or redirect
 * @param string $redirect URL to redirect to if not logged in
 */
function requireLogin($redirect = 'index.php?route=login') {
    if (!isLoggedIn()) {
        header('Location: ' . $redirect);
        exit();
    }
}

/**
 * Require admin role or redirect
 * @param string $redirect URL to redirect to if not admin
 */
function requireAdmin($redirect = 'index.php?route=dashboard') {
    if (!isAdmin()) {
        header('Location: ' . $redirect);
        exit();
    }
} 
<?php
/**
 * Global helper functions for the application
 */

if (!function_exists('isAdmin')) {
    /**
     * Check if the current user has admin privileges
     * @return bool
     */
    function isAdmin() {
        return isset($_SESSION['user']) && in_array($_SESSION['user']['role'], ['admin', 'developer']);
    }
}

if (!function_exists('h')) {
    /**
     * Sanitize output to prevent XSS
     * @param string $string
     * @return string
     */
    function h($string) {
        return htmlspecialchars($string, ENT_QUOTES, 'UTF-8');
    }
}

if (!function_exists('redirect')) {
    /**
     * Redirect to another page
     * @param string $path
     * @return void
     */
    function redirect($path) {
        header("Location: " . SITE_URL . "/" . ltrim($path, '/'));
        exit;
    }
}

if (!function_exists('isLoggedIn')) {
    /**
     * Check if user is logged in
     * @return bool
     */
    function isLoggedIn() {
        return isset($_SESSION['user']);
    }
}

if (!function_exists('requireLogin')) {
    /**
     * Require user to be logged in to access a page
     * Special handling for prediction API calls
     * @return void
     */
    function requireLogin() {
        global $route;
        if ($route === 'predict' && $_SERVER['REQUEST_METHOD'] === 'POST') {
            // Allow API calls to predict endpoint
            return;
        }
        
        if (!isLoggedIn()) {
            redirect('login.php');
        }
    }
}

if (!function_exists('getUserRole')) {
    /**
     * Get current user's role
     * @return string|null
     */
    function getUserRole() {
        return $_SESSION['user']['role'] ?? null;
    }
}

if (!function_exists('getCurrentUser')) {
    /**
     * Get current user data
     * @return array|null
     */
    function getCurrentUser() {
        return $_SESSION['user'] ?? null;
    }
}

if (!function_exists('isAjax')) {
    /**
     * Check if the request is an AJAX request
     * @return bool
     */
    function isAjax() {
        return !empty($_SERVER['HTTP_X_REQUESTED_WITH']) && 
               strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest';
    }
}

if (!function_exists('jsonResponse')) {
    /**
     * Send JSON response
     * @param mixed $data
     * @param int $status
     * @return void
     */
    function jsonResponse($data, $status = 200) {
        http_response_code($status);
        header('Content-Type: application/json');
        echo json_encode($data);
        exit;
    }
} 
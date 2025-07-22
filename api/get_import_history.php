<?php
session_start();
require_once dirname(__DIR__) . '/config.php';

// Check if user is admin
if (!isset($_SESSION['user']) || $_SESSION['user']['role'] !== 'admin') {
    http_response_code(403);
    echo json_encode(['error' => 'Unauthorized access']);
    exit;
}

try {
    // Get import history from dataset table
    $stmt = $pdo->query("
        SELECT 
            d.id,
            d.name,
            d.description,
            d.type,
            d.file_path,
            d.record_count,
            d.created_at,
            u.username as created_by
        FROM dataset d
        LEFT JOIN users u ON d.created_by = u.id
        ORDER BY d.created_at DESC
        LIMIT 10
    ");

    $history = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    header('Content-Type: application/json');
    echo json_encode($history);

} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode([
        'error' => 'Database error: ' . $e->getMessage(),
        'code' => $e->getCode()
    ]);
    
    error_log("Error getting import history: " . $e->getMessage());
} 
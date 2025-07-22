<?php
session_start();
require_once dirname(__DIR__) . '/config.php';

// Check if user is admin
if (!isset($_SESSION['user']) || $_SESSION['user']['role'] !== 'admin') {
    http_response_code(403);
    echo json_encode(['error' => 'Unauthorized access']);
    exit;
}

// Get JSON data from request body
$json = file_get_contents('php://input');
$data = json_decode($json, true);

if (!$data) {
    http_response_code(400);
    echo json_encode(['error' => 'Invalid JSON data']);
    exit;
}

try {
    // Start transaction
    $pdo->beginTransaction();

    // Generate batch ID and file name
    $batchId = uniqid('batch_', true);
    $fileName = 'import_' . date('Y-m-d_H-i-s') . '.csv';

    // First, insert the dataset metadata
    $stmt = $pdo->prepare("
        INSERT INTO dataset (
            name, description, type, file_path, record_count, created_by
        ) VALUES (
            :name, :description, :type, :file_path, :record_count, :created_by
        )
    ");

    $stmt->execute([
        ':name' => $data['name'] ?? 'Imported Dataset',
        ':description' => $data['description'] ?? null,
        ':type' => $data['type'] ?? 'training',
        ':file_path' => $fileName,
        ':record_count' => count($data['records']),
        ':created_by' => $_SESSION['user']['id']
    ]);

    $datasetId = $pdo->lastInsertId();

    // Prepare insert statement for heart data
    $stmt = $pdo->prepare("
        INSERT INTO heart_data (
            dataset_id, age, sex, cp, trestbps, chol, fbs, restecg, thalach, 
            exang, oldpeak, slope, ca, thal, target
        ) VALUES (
            :dataset_id, :age, :sex, :cp, :trestbps, :chol, :fbs, :restecg, :thalach,
            :exang, :oldpeak, :slope, :ca, :thal, :target
        )
    ");

    // Insert each record
    foreach ($data['records'] as $row) {
        $params = [
            ':dataset_id' => $datasetId,
            ':age' => (int)$row['age'],
            ':sex' => (int)$row['sex'],
            ':cp' => (int)$row['cp'],
            ':trestbps' => (int)$row['trestbps'],
            ':chol' => (int)$row['chol'],
            ':fbs' => (int)$row['fbs'],
            ':restecg' => (int)$row['restecg'],
            ':thalach' => (int)$row['thalach'],
            ':exang' => (int)$row['exang'],
            ':oldpeak' => (float)$row['oldpeak'],
            ':slope' => (int)$row['slope'],
            ':ca' => (int)$row['ca'],
            ':thal' => (int)$row['thal'],
            ':target' => (int)$row['target']
        ];

        $stmt->execute($params);
    }

    // Commit transaction
    $pdo->commit();

    // Return success response
    echo json_encode([
        'success' => true,
        'message' => 'Dataset imported successfully',
        'dataset_id' => $datasetId,
        'records' => count($data['records'])
    ]);

} catch (PDOException $e) {
    // Rollback transaction on error
    $pdo->rollBack();
    
    http_response_code(500);
    echo json_encode([
        'error' => 'Database error: ' . $e->getMessage(),
        'code' => $e->getCode()
    ]);
    
    // Log the error
    error_log("Error importing dataset: " . $e->getMessage());
} 
<?php
session_start();
require_once dirname(__DIR__) . '/config.php';

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    http_response_code(403);
    echo json_encode(['error' => 'Unauthorized access']);
    exit;
}

// Get POST data
$name = $_POST['name'] ?? '';
$description = $_POST['description'] ?? '';
$type = $_POST['type'] ?? 'Testing Dataset';

// Check if file was uploaded
if (!isset($_FILES['csv_file']) || $_FILES['csv_file']['error'] !== UPLOAD_ERR_OK) {
    http_response_code(400);
    echo json_encode(['error' => 'No file uploaded or upload error occurred']);
    exit;
}

$file = $_FILES['csv_file'];
$filePath = $file['tmp_name'];

try {
    // Start transaction
    $pdo->beginTransaction();
    
    // Generate batch ID
    $batch_id = uniqid('batch_', true);
    
    // Insert dataset metadata
    $stmt = $pdo->prepare("
        INSERT INTO dataset (name, description, file_path, user_id) 
        VALUES (?, ?, ?, ?)
    ");
    
    $stmt->execute([
        $name,
        $description,
        $file['name'],
        $_SESSION['user_id']
    ]);
    
    $datasetId = $pdo->lastInsertId();
    
    // Process CSV file
    $handle = fopen($filePath, 'r');
    if (!$handle) {
        throw new Exception('Failed to open the CSV file');
    }
    
    // Read header row
    $header = fgetcsv($handle);
    if (!$header) {
        throw new Exception('The CSV file is empty or invalid');
    }
    
    // Map column names to database fields
    $columnMap = [
        'age' => array_search('age', array_map('strtolower', $header)),
        'sex' => array_search('sex', array_map('strtolower', $header)),
        'cp' => array_search('cp', array_map('strtolower', $header)),
        'trestbps' => array_search('trestbps', array_map('strtolower', $header)),
        'chol' => array_search('chol', array_map('strtolower', $header)),
        'fbs' => array_search('fbs', array_map('strtolower', $header)),
        'restecg' => array_search('restecg', array_map('strtolower', $header)),
        'thalach' => array_search('thalach', array_map('strtolower', $header)),
        'exang' => array_search('exang', array_map('strtolower', $header)),
        'oldpeak' => array_search('oldpeak', array_map('strtolower', $header)),
        'slope' => array_search('slope', array_map('strtolower', $header)),
        'ca' => array_search('ca', array_map('strtolower', $header)),
        'thal' => array_search('thal', array_map('strtolower', $header)),
        'target' => array_search('target', array_map('strtolower', $header))
    ];
    
    // Check if all required columns are present
    $missingColumns = [];
    foreach ($columnMap as $field => $index) {
        if ($index === false) {
            $missingColumns[] = $field;
        }
    }
    
    if (!empty($missingColumns)) {
        throw new Exception('Missing required columns: ' . implode(', ', $missingColumns));
    }
    
    // Prepare the SQL statement for heart data
    $stmt = $pdo->prepare("
        INSERT INTO heart_data (
            dataset_id, age, sex, cp, trestbps, chol, fbs, restecg, 
            thalach, exang, oldpeak, slope, ca, thal, target
        ) VALUES (
            ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?
        )
    ");
    
    // Read and insert data rows
    $count = 0;
    $row = 2; // Start from row 2 (after header)
    
    while (($data = fgetcsv($handle)) !== false) {
        // Check if we have enough columns
        if (count($data) < count($header)) {
            continue; // Skip incomplete rows
        }
        
        try {
            $stmt->execute([
                $datasetId,
                $data[$columnMap['age']],
                $data[$columnMap['sex']],
                $data[$columnMap['cp']],
                $data[$columnMap['trestbps']],
                $data[$columnMap['chol']],
                $data[$columnMap['fbs']],
                $data[$columnMap['restecg']],
                $data[$columnMap['thalach']],
                $data[$columnMap['exang']],
                $data[$columnMap['oldpeak']],
                $data[$columnMap['slope']],
                $data[$columnMap['ca']],
                $data[$columnMap['thal']],
                $data[$columnMap['target']]
            ]);
            $count++;
        } catch (PDOException $e) {
            error_log("Error inserting row $row: " . $e->getMessage());
            // Continue with next row
        }
        
        $row++;
    }
    
    fclose($handle);
    
    if ($count === 0) {
        throw new Exception('No valid records found in the CSV file');
    }
    
    // Commit transaction
    $pdo->commit();
    
    // Log the activity
    $stmt = $pdo->prepare("
        INSERT INTO user_activity_log (user_id, activity_type, description, ip_address) 
        VALUES (?, 'dataset', ?, ?)
    ");
    $stmt->execute([
        $_SESSION['user_id'], 
        "Dataset '$name' imported with $count records", 
        $_SERVER['REMOTE_ADDR']
    ]);
    
    // Return success response
    echo json_encode([
        'success' => true,
        'message' => "Dataset imported successfully. $count records added.",
        'dataset_id' => $datasetId
    ]);
    
} catch (Exception $e) {
    // Rollback transaction on error
    if ($pdo->inTransaction()) {
        $pdo->rollBack();
    }
    
    http_response_code(500);
    echo json_encode([
        'error' => $e->getMessage()
    ]);
    
    error_log("Error importing dataset: " . $e->getMessage());
} 
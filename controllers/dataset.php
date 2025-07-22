<?php
// Dataset controller for handling dataset uploads and management

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header('Location: index.php?route=login');
    exit();
}

// Initialize variables
$error = null;
$success = null;
$datasets = [];

// Handle actions
$action = $_GET['action'] ?? '';

// Get user's datasets
try {
    $stmt = $pdo->prepare("SELECT * FROM dataset WHERE user_id = ? ORDER BY created_at DESC");
    $stmt->execute([$_SESSION['user_id']]);
    $datasets = $stmt->fetchAll();
} catch (PDOException $e) {
    error_log("Error fetching datasets: " . $e->getMessage());
    $error = "Failed to load datasets. Please try again later.";
}

// Handle dataset actions
if ($action === 'delete' && isset($_GET['id'])) {
    $datasetId = (int)$_GET['id'];
    
    try {
        // Check if dataset belongs to user
        $stmt = $pdo->prepare("SELECT id FROM dataset WHERE id = ? AND user_id = ?");
        $stmt->execute([$datasetId, $_SESSION['user_id']]);
        
        if ($stmt->rowCount() > 0) {
            // Delete the dataset
            $stmt = $pdo->prepare("DELETE FROM dataset WHERE id = ?");
            $stmt->execute([$datasetId]);
            
            $success = "Dataset deleted successfully.";
            
            // Log the activity
            $stmt = $pdo->prepare("INSERT INTO user_activity_log (user_id, activity_type, description, ip_address) VALUES (?, 'dataset', ?, ?)");
            $stmt->execute([$_SESSION['user_id'], "Dataset deleted", $_SERVER['REMOTE_ADDR']]);
            
            // Refresh datasets list
            $stmt = $pdo->prepare("SELECT * FROM dataset WHERE user_id = ? ORDER BY created_at DESC");
            $stmt->execute([$_SESSION['user_id']]);
            $datasets = $stmt->fetchAll();
        } else {
            $error = "Dataset not found or you don't have permission to delete it.";
        }
    } catch (PDOException $e) {
        error_log("Error deleting dataset: " . $e->getMessage());
        $error = "Failed to delete dataset. Please try again later.";
    }
} elseif ($action === 'view' && isset($_GET['id'])) {
    $datasetId = (int)$_GET['id'];
    
    try {
        // Get dataset details
        $stmt = $pdo->prepare("SELECT * FROM dataset WHERE id = ? AND user_id = ?");
        $stmt->execute([$datasetId, $_SESSION['user_id']]);
        $dataset = $stmt->fetch();
        
        if ($dataset) {
            // Get dataset records
            $stmt = $pdo->prepare("SELECT * FROM heart_data WHERE dataset_id = ? LIMIT 100");
            $stmt->execute([$datasetId]);
            $records = $stmt->fetchAll();
            
            // Load the dataset view template
            require_once 'templates/view_dataset.php';
            exit();
        } else {
            $error = "Dataset not found or you don't have permission to view it.";
        }
    } catch (PDOException $e) {
        error_log("Error viewing dataset: " . $e->getMessage());
        $error = "Failed to load dataset. Please try again later.";
    }
}

// Handle dataset upload
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['dataset'])) {
    $file = $_FILES['dataset'];
    $name = $_POST['name'] ?? '';
    $description = $_POST['description'] ?? '';
    
    // Validate file
    if ($file['error'] !== UPLOAD_ERR_OK) {
        $error = "File upload failed. Error code: " . $file['error'];
    } elseif (!in_array($file['type'], ['text/csv', 'application/vnd.ms-excel', 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet'])) {
        $error = "Invalid file type. Please upload a CSV or Excel file.";
    } elseif (empty($name)) {
        $error = "Please provide a name for the dataset.";
    } else {
        // Generate a unique batch ID for this import
        $batch_id = uniqid('batch_', true);
        
        // Process the file based on its type
        if ($file['type'] === 'text/csv') {
            $result = processCSVFile($file['tmp_name'], $name, $description, $batch_id);
        } else {
            $result = processExcelFile($file['tmp_name'], $name, $description, $batch_id);
        }
        
        if ($result['success']) {
            $success = "Dataset imported successfully. {$result['count']} records added.";
            
            // Log the activity
            $stmt = $pdo->prepare("INSERT INTO user_activity_log (user_id, activity_type, description, ip_address) VALUES (?, 'dataset', ?, ?)");
            $stmt->execute([$_SESSION['user_id'], "Dataset '{$name}' imported with {$result['count']} records", $_SERVER['REMOTE_ADDR']]);
            
            // Refresh datasets list
            $stmt = $pdo->prepare("SELECT * FROM dataset WHERE user_id = ? ORDER BY created_at DESC");
            $stmt->execute([$_SESSION['user_id']]);
            $datasets = $stmt->fetchAll();
        } else {
            $error = $result['message'];
        }
    }
}

// Function to process CSV files
function processCSVFile($filePath, $name, $description, $batch_id) {
    global $pdo;
    
    try {
        // Open the CSV file
        $handle = fopen($filePath, 'r');
        if (!$handle) {
            return ['success' => false, 'message' => 'Failed to open the CSV file.'];
        }
        
        // Read the header row
        $header = fgetcsv($handle);
        if (!$header) {
            fclose($handle);
            return ['success' => false, 'message' => 'The CSV file is empty or invalid.'];
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
            fclose($handle);
            return ['success' => false, 'message' => 'Missing required columns: ' . implode(', ', $missingColumns)];
        }
        
        // First, insert the dataset metadata
        $stmt = $pdo->prepare("INSERT INTO dataset (name, description, batch_id, file_name, user_id) VALUES (?, ?, ?, ?, ?)");
        $stmt->execute([$name, $description, $batch_id, $name, $_SESSION['user_id']]);
        $datasetId = $pdo->lastInsertId();
        
        // Prepare the SQL statement for heart data
        $stmt = $pdo->prepare("INSERT INTO heart_data (dataset_id, age, sex, cp, trestbps, chol, fbs, restecg, thalach, exang, oldpeak, slope, ca, thal, target) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
        
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
        
        if ($count > 0) {
            return ['success' => true, 'count' => $count];
        } else {
            // If no records were inserted, delete the dataset entry
            $stmt = $pdo->prepare("DELETE FROM dataset WHERE id = ?");
            $stmt->execute([$datasetId]);
            
            return ['success' => false, 'message' => 'No valid records found in the CSV file.'];
        }
    } catch (Exception $e) {
        error_log("Error processing CSV file: " . $e->getMessage());
        return ['success' => false, 'message' => 'An error occurred while processing the CSV file.'];
    }
}

// Function to process Excel files
function processExcelFile($filePath, $name, $description, $batch_id) {
    // This is a placeholder for Excel processing
    // You would need to use a library like PhpSpreadsheet to handle Excel files
    return ['success' => false, 'message' => 'Excel file processing is not implemented yet.'];
}

// Load the dataset view
require_once 'templates/dataset.php'; 
<?php
// Enable error display for debugging
error_reporting(E_ALL);
ini_set('display_errors', 1);
ini_set('error_log', dirname(__DIR__) . '/debug.log');

// Set headers for JSON response
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization');

// Handle CORS preflight
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit;
}

require_once dirname(__DIR__) . '/config.php';

// Create temp directory if it doesn't exist
if (!file_exists(TEMP_DIR)) {
    mkdir(TEMP_DIR, 0777, true);
}

// Function to send JSON response
function send_json($data, $status = 200) {
    http_response_code($status);
    echo json_encode($data);
    exit;
}

// Simple debug logging function
function debug_log($message) {
    $log_file = dirname(__DIR__) . '/debug.log';
    $timestamp = date('Y-m-d H:i:s');
    file_put_contents($log_file, "[$timestamp] $message\n", FILE_APPEND);
}

// Function to run Python prediction
function run_python_prediction($data) {
    // Create temporary JSON file
    $temp_file = TEMP_DIR . '/input_' . uniqid() . '.json';
    file_put_contents($temp_file, json_encode($data));
    
    // Build command
    $command = sprintf('"%s" "%s" "%s" 2>&1', PYTHON_PATH, PREDICT_SCRIPT, $temp_file);
    
    // Log the command
    debug_log("Running command: " . $command);
    
    // Execute Python script
    $output = [];
    $return_var = 0;
    exec($command, $output, $return_var);
    
    // Log the output
    debug_log("Python output: " . implode("\n", $output));
    
    // Clean up temp file
    unlink($temp_file);
    
    // Parse output
    $result = null;
    foreach ($output as $line) {
        try {
            $decoded = json_decode($line, true);
            if (is_array($decoded) && isset($decoded['success'])) {
                $result = $decoded;
                break;
            }
        } catch (Exception $e) {
            continue;
        }
    }
    
    if (!$result) {
        throw new Exception("No valid JSON output from prediction script");
    }
    
    return $result;
}

try {
    // Log start of request
    debug_log("Received new request");

    // Only allow POST
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        send_json(['success' => false, 'message' => 'Only POST requests are allowed'], 405);
    }
    
    // Get POST data
    $input = file_get_contents('php://input');
    debug_log("Raw input received: " . $input);

    if (empty($input)) {
        send_json(['success' => false, 'message' => 'No input data received'], 400);
    }

    $data = json_decode($input, true);
    if ($data === null) {
        send_json(['success' => false, 'message' => 'Invalid JSON data'], 400);
    }

    // Determine user_id for saving prediction
    session_start();
    $user_id = 1; // Default fallback
    if (isset($_SESSION['user']['id'])) {
        $user_id = $_SESSION['user']['id'];
    } elseif (isset($data['user_id'])) {
        $user_id = $data['user_id'];
    }

    // Check if this is a batch import or single prediction
    if (isset($data['data']) && is_array($data['data'])) {
        // Batch import mode
        $results = [];
        $successful = 0;
        $failed = 0;

        foreach ($data['data'] as $record) {
            try {
                // Run Python prediction
                $result = run_python_prediction($record);
                
                if ($result['success']) {
                    // Store in database
                    $stmt = $pdo->prepare("INSERT INTO predictions (age, sex, cp, trestbps, chol, fbs, restecg, thalach, exang, oldpeak, slope, ca, thal, prediction_result, user_id, prediction_date) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, NOW())");
                    $stmt->execute([
                        $record['age'], $record['sex'], $record['cp'], $record['trestbps'], 
                        $record['chol'], $record['fbs'], $record['restecg'], $record['thalach'], 
                        $record['exang'], $record['oldpeak'], $record['slope'], $record['ca'], 
                        $record['thal'], $result['prediction'], $user_id
                    ]);

                    $successful++;
                    $results[] = array_merge($record, [
                        'prediction' => $result['prediction'],
                        'message' => $result['message']
                    ]);
                } else {
                    $failed++;
                    debug_log("Failed prediction: " . json_encode($result));
                }
            } catch (Exception $e) {
                debug_log("Error processing record: " . print_r($record, true) . "\nError: " . $e->getMessage());
                $failed++;
            }
        }

        send_json([
            'success' => true,
            'message' => "Processed $successful records successfully. Failed: $failed",
            'name' => $data['name'] ?? 'Batch Import',
            'description' => $data['description'] ?? '',
            'results' => $results
        ]);
    } else {
        // Single prediction mode
        $result = run_python_prediction($data);
        
        if ($result['success']) {
            // Store in database
            $stmt = $pdo->prepare("INSERT INTO predictions (age, sex, cp, trestbps, chol, fbs, restecg, thalach, exang, oldpeak, slope, ca, thal, prediction_result, user_id, prediction_date) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, NOW())");
            $stmt->execute([
                $data['age'], $data['sex'], $data['cp'], $data['trestbps'], 
                $data['chol'], $data['fbs'], $data['restecg'], $data['thalach'], 
                $data['exang'], $data['oldpeak'], $data['slope'], $data['ca'], 
                $data['thal'], $result['prediction'], $user_id
            ]);
        }

        send_json($result);
    }

} catch (Exception $e) {
    debug_log("Error: " . $e->getMessage());
    send_json([
        'success' => false,
        'message' => 'Error: ' . $e->getMessage()
    ], 500);
}
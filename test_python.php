<?php
// Test Python environment and prediction script
error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "<h1>Python Environment Test</h1>";

// Test 1: Check if Python path exists
$python_path = 'C:\\xampp\\htdocs\\heart_disease\\venv\\Scripts\\python.exe';
echo "<h2>Test 1: Python Path</h2>";
if (file_exists($python_path)) {
    echo "✅ Python path exists: $python_path<br>";
} else {
    echo "❌ Python path not found: $python_path<br>";
}

// Test 2: Check if predict.py exists
$predict_script = dirname(__DIR__) . '/predict.py';
echo "<h2>Test 2: Predict Script</h2>";
if (file_exists($predict_script)) {
    echo "✅ Predict script exists: $predict_script<br>";
} else {
    echo "❌ Predict script not found: $predict_script<br>";
}

// Test 3: Test Python execution
echo "<h2>Test 3: Python Execution</h2>";
$command = sprintf('"%s" --version 2>&1', $python_path);
$output = [];
$return_var = 0;
exec($command, $output, $return_var);

if ($return_var === 0) {
    echo "✅ Python execution successful<br>";
    echo "Output: " . implode("<br>", $output) . "<br>";
} else {
    echo "❌ Python execution failed<br>";
    echo "Error: " . implode("<br>", $output) . "<br>";
}

// Test 4: Test prediction script with sample data
echo "<h2>Test 4: Prediction Script Test</h2>";
$test_features = [50, 1, 0, 120, 200, 0, 0, 150, 0, 0.0, 0, 0, 0];
$command = sprintf('"%s" "%s" %s 2>&1', 
    $python_path, 
    $predict_script, 
    implode(' ', array_map('escapeshellarg', $test_features))
);

echo "Command: $command<br><br>";

$output = [];
$return_var = 0;
exec($command, $output, $return_var);

if ($return_var === 0) {
    echo "✅ Prediction script execution successful<br>";
    echo "Output:<br>";
    foreach ($output as $line) {
        echo htmlspecialchars($line) . "<br>";
    }
    
    // Try to parse JSON output
    foreach ($output as $line) {
        $decoded = json_decode($line, true);
        if (is_array($decoded) && isset($decoded['success'])) {
            echo "<br><strong>Parsed Result:</strong><br>";
            echo "<pre>" . json_encode($decoded, JSON_PRETTY_PRINT) . "</pre>";
            break;
        }
    }
} else {
    echo "❌ Prediction script execution failed<br>";
    echo "Error output:<br>";
    foreach ($output as $line) {
        echo htmlspecialchars($line) . "<br>";
    }
}

// Test 5: Check database connection
echo "<h2>Test 5: Database Connection</h2>";
try {
    require_once '../config/database.php';
    echo "✅ Database connection successful<br>";
} catch (Exception $e) {
    echo "❌ Database connection failed: " . $e->getMessage() . "<br>";
}
?> 
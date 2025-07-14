<?php
// Enable error display
ini_set('display_errors', 1);
error_reporting(E_ALL);

// Check if model files exist
$modelPath = 'models/best_heart_model.joblib';
$scalerPath = 'models/scaler.joblib';

echo "Checking model files...<br>";
echo "Model path: $modelPath<br>";
echo "Scaler path: $scalerPath<br><br>";

if (file_exists($modelPath)) {
    echo "Model file exists<br>";
    echo "Model file size: " . filesize($modelPath) . " bytes<br>";
} else {
    echo "Model file does not exist!<br>";
}

if (file_exists($scalerPath)) {
    echo "Scaler file exists<br>";
    echo "Scaler file size: " . filesize($scalerPath) . " bytes<br>";
} else {
    echo "Scaler file does not exist!<br>";
}

// Try to load the model
echo "<br>Attempting to load model...<br>";
try {
    $model = unserialize(file_get_contents($modelPath));
    echo "Model loaded successfully<br>";
} catch (Exception $e) {
    echo "Error loading model: " . $e->getMessage() . "<br>";
}

// Try to load the scaler
echo "<br>Attempting to load scaler...<br>";
try {
    $scaler = unserialize(file_get_contents($scalerPath));
    echo "Scaler loaded successfully<br>";
} catch (Exception $e) {
    echo "Error loading scaler: " . $e->getMessage() . "<br>";
} 
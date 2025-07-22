<?php
// Enable error display
ini_set('display_errors', 1);
error_reporting(E_ALL);

// Check if model files exist
$modelPath = 'models/best_heart_model.joblib';
$scalerPath = 'models/scaler.joblib';

echo "<h2>Model Files Check</h2>";

echo "Checking model files...<br>";
echo "Model path: $modelPath<br>";
echo "Scaler path: $scalerPath<br><br>";

if (file_exists($modelPath)) {
    echo "✅ Model file exists<br>";
    echo "Model file size: " . filesize($modelPath) . " bytes<br>";
} else {
    echo "❌ Model file does not exist!<br>";
}

if (file_exists($scalerPath)) {
    echo "✅ Scaler file exists<br>";
    echo "Scaler file size: " . filesize($scalerPath) . " bytes<br>";
} else {
    echo "❌ Scaler file does not exist!<br>";
}

// Try to load the model
echo "<br>Attempting to load model...<br>";
try {
    $model = unserialize(file_get_contents($modelPath));
    echo "✅ Model loaded successfully<br>";
    
    // Test prediction with sample data
    echo "<br>Testing prediction with sample data...<br>";
    $sampleData = [
        63, 1, 3, 145, 233, 1, 0, 150, 0, 2.3, 0, 0, 1
    ];
    $prediction = $model->predict([$sampleData])[0];
    echo "Sample prediction result: " . ($prediction ? "High Risk" : "Low Risk") . "<br>";
} catch (Exception $e) {
    echo "❌ Error loading model: " . $e->getMessage() . "<br>";
}

// Try to load the scaler
echo "<br>Attempting to load scaler...<br>";
try {
    $scaler = unserialize(file_get_contents($scalerPath));
    echo "✅ Scaler loaded successfully<br>";
    
    // Test scaling with sample data
    echo "<br>Testing scaling with sample data...<br>";
    $sampleData = [
        63, 1, 3, 145, 233, 1, 0, 150, 0, 2.3, 0, 0, 1
    ];
    $scaledData = $scaler->transform([$sampleData]);
    echo "Sample scaled data: " . print_r($scaledData, true) . "<br>";
} catch (Exception $e) {
    echo "❌ Error loading scaler: " . $e->getMessage() . "<br>";
} 
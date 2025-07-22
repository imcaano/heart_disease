<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Check database connection
try {
    $pdo = new PDO('mysql:host=localhost;dbname=heart_disease', 'root', '');
    echo "Database connection successful<br>";
} catch (PDOException $e) {
    die("Database connection failed: " . $e->getMessage());
}

// Check model files
$model_path = __DIR__ . '/../models/best_heart_model.joblib';
$scaler_path = __DIR__ . '/../models/scaler.joblib';

if (file_exists($model_path)) {
    echo "Model file exists<br>";
} else {
    echo "Model file not found at: $model_path<br>";
}

if (file_exists($scaler_path)) {
    echo "Scaler file exists<br>";
} else {
    echo "Scaler file not found at: $scaler_path<br>";
}

// Check Python installation
$python_version = shell_exec('python --version');
echo "Python version: $python_version<br>";

// Check required Python packages
$packages = ['numpy', 'scikit-learn', 'joblib'];
foreach ($packages as $package) {
    $output = shell_exec("python -c \"import $package; print('$package installed')\"");
    echo "$output<br>";
} 
<?php
// Simple test script to identify booking API issues
error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "Testing booking API...\n";

// Test 1: Check if config.php loads
echo "1. Testing config.php...\n";
try {
    require_once 'config.php';
    echo "   ✓ Config loaded successfully\n";
} catch (Exception $e) {
    echo "   ✗ Config error: " . $e->getMessage() . "\n";
    exit;
}

// Test 2: Check database connection
echo "2. Testing database connection...\n";
try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    echo "   ✓ Database connected successfully\n";
} catch (PDOException $e) {
    echo "   ✗ Database error: " . $e->getMessage() . "\n";
    exit;
}

// Test 3: Check appointments table
echo "3. Testing appointments table...\n";
try {
    $stmt = $pdo->query("SHOW TABLES LIKE 'appointments'");
    if ($stmt->rowCount() > 0) {
        echo "   ✓ Appointments table exists\n";
    } else {
        echo "   ✗ Appointments table does not exist\n";
        exit;
    }
} catch (Exception $e) {
    echo "   ✗ Table check error: " . $e->getMessage() . "\n";
    exit;
}

// Test 4: Check table structure
echo "4. Testing appointments table structure...\n";
try {
    $stmt = $pdo->query("DESCRIBE appointments");
    $columns = $stmt->fetchAll(PDO::FETCH_ASSOC);
    echo "   ✓ Table has " . count($columns) . " columns\n";
    foreach ($columns as $column) {
        echo "     - " . $column['Field'] . " (" . $column['Type'] . ")\n";
    }
} catch (Exception $e) {
    echo "   ✗ Structure check error: " . $e->getMessage() . "\n";
    exit;
}

// Test 5: Test insert query
echo "5. Testing insert query...\n";
try {
    $stmt = $pdo->prepare("
        INSERT INTO appointments (
            user_id, patient_name, patient_email, patient_phone, 
            appointment_date, appointment_time, address, reason, 
            prediction_id, prediction_result, status, created_at
        ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 'pending', NOW())
    ");
    echo "   ✓ Insert query prepared successfully\n";
} catch (Exception $e) {
    echo "   ✗ Insert query error: " . $e->getMessage() . "\n";
    exit;
}

echo "\nAll tests passed! The booking API should work.\n";
?> 
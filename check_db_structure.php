<?php
require_once 'config.php';

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    echo "Database connection successful!\n\n";
    
    // Check if predictions table exists
    $stmt = $pdo->query("SHOW TABLES LIKE 'predictions'");
    if ($stmt->rowCount() == 0) {
        echo "❌ Predictions table does not exist!\n";
        exit;
    }
    
    echo "✅ Predictions table exists!\n\n";
    
    // Show table structure
    echo "Table structure:\n";
    $stmt = $pdo->query("DESCRIBE predictions");
    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        echo "- {$row['Field']} ({$row['Type']})\n";
    }
    
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
?> 
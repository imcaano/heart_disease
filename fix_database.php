<?php
require_once 'config.php';

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    echo "Database connection successful!\n\n";
    
    // Add missing columns to predictions table
    $alterQueries = [
        "ALTER TABLE predictions ADD COLUMN IF NOT EXISTS thalach INT(11) NOT NULL DEFAULT 0",
        "ALTER TABLE predictions ADD COLUMN IF NOT EXISTS exang TINYINT(1) NOT NULL DEFAULT 0",
        "ALTER TABLE predictions ADD COLUMN IF NOT EXISTS oldpeak DECIMAL(3,1) NOT NULL DEFAULT 0.0",
        "ALTER TABLE predictions ADD COLUMN IF NOT EXISTS slope INT(11) NOT NULL DEFAULT 0",
        "ALTER TABLE predictions ADD COLUMN IF NOT EXISTS ca INT(11) NOT NULL DEFAULT 0",
        "ALTER TABLE predictions ADD COLUMN IF NOT EXISTS thal INT(11) NOT NULL DEFAULT 0",
        "ALTER TABLE predictions ADD COLUMN IF NOT EXISTS prediction TINYINT(1) NOT NULL DEFAULT 0",
        "ALTER TABLE predictions ADD COLUMN IF NOT EXISTS prediction_date DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP",
        "ALTER TABLE predictions ADD COLUMN IF NOT EXISTS transaction_hash VARCHAR(66) DEFAULT NULL"
    ];

    foreach ($alterQueries as $query) {
        try {
            $pdo->exec($query);
            echo "✅ Executed: " . substr($query, 0, 50) . "...\n";
        } catch (Exception $e) {
            echo "⚠️  Skipped (column may already exist): " . substr($query, 0, 50) . "...\n";
        }
    }
    
    echo "\n✅ Database structure updated!\n\n";
    
    // Show final table structure
    echo "Final table structure:\n";
    $stmt = $pdo->query("DESCRIBE predictions");
    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        echo "- {$row['Field']} ({$row['Type']})\n";
    }
    
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
?> 
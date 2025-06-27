<?php
require_once dirname(__DIR__) . '/config.php';

try {
    // Add missing columns to predictions table
    $alterQueries = [
        "ALTER TABLE predictions ADD COLUMN IF NOT EXISTS confidence_score DECIMAL(5,2) NOT NULL DEFAULT 0.00",
        "ALTER TABLE predictions ADD COLUMN IF NOT EXISTS verified_by_expert TINYINT(1) DEFAULT 0",
        "ALTER TABLE predictions ADD COLUMN IF NOT EXISTS expert_notes TEXT DEFAULT NULL",
        "ALTER TABLE predictions ADD COLUMN IF NOT EXISTS transaction_hash VARCHAR(66) DEFAULT NULL"
    ];

    foreach ($alterQueries as $query) {
        $pdo->exec($query);
    }

    // Add missing columns to users table if they don't exist
    $userAlterQueries = [
        "ALTER TABLE users ADD COLUMN IF NOT EXISTS total_predictions INT NOT NULL DEFAULT 0",
        "ALTER TABLE users ADD COLUMN IF NOT EXISTS prediction_accuracy DECIMAL(5,2) NOT NULL DEFAULT 0.00",
        "ALTER TABLE users ADD COLUMN IF NOT EXISTS reputation_score INT NOT NULL DEFAULT 0",
        "ALTER TABLE users ADD COLUMN IF NOT EXISTS last_login TIMESTAMP NULL DEFAULT NULL"
    ];

    foreach ($userAlterQueries as $query) {
        $pdo->exec($query);
    }

    echo "Database schema updated successfully\n";
    
} catch (PDOException $e) {
    die("Database Error: " . $e->getMessage() . "\n");
} 
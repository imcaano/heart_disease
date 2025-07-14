<?php
// Add token column to users table
require_once 'config.php';

try {
    // Check if token column already exists
    $stmt = $pdo->query("SHOW COLUMNS FROM users LIKE 'token'");
    if ($stmt->rowCount() == 0) {
        // Add token column
        $pdo->exec("ALTER TABLE users ADD COLUMN token VARCHAR(255) NULL");
        echo "Token column added successfully to users table.\n";
    } else {
        echo "Token column already exists in users table.\n";
    }
    
    // Check if appointments table exists
    $stmt = $pdo->query("SHOW TABLES LIKE 'appointments'");
    if ($stmt->rowCount() == 0) {
        echo "Appointments table does not exist. Creating it...\n";
        
        $createAppointmentsTable = "
        CREATE TABLE appointments (
            id INT AUTO_INCREMENT PRIMARY KEY,
            user_id INT NOT NULL,
            patient_name VARCHAR(255) NOT NULL,
            patient_email VARCHAR(255) NOT NULL,
            patient_phone VARCHAR(20) NOT NULL,
            appointment_date DATE NOT NULL,
            appointment_time TIME NOT NULL,
            address TEXT NOT NULL,
            reason TEXT NOT NULL,
            prediction_id INT NULL,
            status ENUM('pending', 'approved', 'rejected', 'completed') DEFAULT 'pending',
            admin_notes TEXT NULL,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
            FOREIGN KEY (prediction_id) REFERENCES predictions(id) ON DELETE SET NULL
        )";
        
        $pdo->exec($createAppointmentsTable);
        echo "Appointments table created successfully.\n";
    } else {
        echo "Appointments table already exists.\n";
    }
    
    echo "Database setup completed successfully!\n";
    
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
?> 
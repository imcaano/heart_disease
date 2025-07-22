<?php
// Test script for appointment status update
require_once 'config.php';

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    echo "Database connection successful!\n";
    
    // Check if appointments table exists
    $stmt = $pdo->query("SHOW TABLES LIKE 'appointments'");
    if ($stmt->rowCount() > 0) {
        echo "Appointments table exists!\n";
        
        // Check table structure
        $stmt = $pdo->query("DESCRIBE appointments");
        $columns = $stmt->fetchAll(PDO::FETCH_ASSOC);
        echo "Table structure:\n";
        foreach ($columns as $column) {
            echo "- {$column['Field']}: {$column['Type']}\n";
        }
        
        // Check if there are any appointments
        $stmt = $pdo->query("SELECT COUNT(*) as count FROM appointments");
        $count = $stmt->fetch(PDO::FETCH_ASSOC)['count'];
        echo "Total appointments: $count\n";
        
        if ($count > 0) {
            // Show sample appointment
            $stmt = $pdo->query("SELECT * FROM appointments LIMIT 1");
            $appointment = $stmt->fetch(PDO::FETCH_ASSOC);
            echo "Sample appointment:\n";
            print_r($appointment);
            
            // Test update
            $appointment_id = $appointment['id'];
            $stmt = $pdo->prepare("UPDATE appointments SET status = 'approved', updated_at = NOW() WHERE id = ?");
            $result = $stmt->execute([$appointment_id]);
            
            if ($result) {
                echo "Test update successful!\n";
                
                // Verify update
                $stmt = $pdo->prepare("SELECT status FROM appointments WHERE id = ?");
                $stmt->execute([$appointment_id]);
                $new_status = $stmt->fetch(PDO::FETCH_ASSOC)['status'];
                echo "New status: $new_status\n";
            } else {
                echo "Test update failed!\n";
            }
        }
    } else {
        echo "Appointments table does not exist!\n";
    }
    
} catch (PDOException $e) {
    echo "Database error: " . $e->getMessage() . "\n";
} catch (Exception $e) {
    echo "General error: " . $e->getMessage() . "\n";
}
?> 
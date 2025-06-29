<?php
require_once 'config.php';

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // Create appointments table
    $sql = "
    CREATE TABLE IF NOT EXISTS `appointments` (
      `id` int(11) NOT NULL AUTO_INCREMENT,
      `user_id` int(11) NOT NULL,
      `patient_name` varchar(100) NOT NULL,
      `patient_email` varchar(120) NOT NULL,
      `patient_phone` varchar(20) NOT NULL,
      `appointment_date` date NOT NULL,
      `appointment_time` time NOT NULL,
      `address` text NOT NULL,
      `reason` text NOT NULL,
      `prediction_id` int(11) DEFAULT NULL,
      `prediction_result` tinyint(1) DEFAULT NULL,
      `status` enum('pending','approved','rejected','completed','cancelled') NOT NULL DEFAULT 'pending',
      `admin_notes` text DEFAULT NULL,
      `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
      `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
      PRIMARY KEY (`id`),
      KEY `user_id` (`user_id`),
      KEY `prediction_id` (`prediction_id`),
      KEY `status` (`status`),
      KEY `appointment_date` (`appointment_date`),
      CONSTRAINT `appointments_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
      CONSTRAINT `appointments_ibfk_2` FOREIGN KEY (`prediction_id`) REFERENCES `predictions` (`id`) ON DELETE SET NULL
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
    ";

    $pdo->exec($sql);
    echo "Appointments table created successfully!\n";

    // Check if table exists
    $stmt = $pdo->query("SHOW TABLES LIKE 'appointments'");
    if ($stmt->rowCount() > 0) {
        echo "Appointments table exists and is ready to use.\n";
    } else {
        echo "Error: Appointments table was not created.\n";
    }

} catch (PDOException $e) {
    echo "Database error: " . $e->getMessage() . "\n";
} catch (Exception $e) {
    echo "General error: " . $e->getMessage() . "\n";
}
?> 
<?php
require_once dirname(__DIR__) . '/config.php';

try {
    // Try to connect to MySQL without selecting a database
    $pdo = new PDO("mysql:host=" . DB_HOST, DB_USER, DB_PASS);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // Check if database exists
    $dbname = DB_NAME;
    $result = $pdo->query("SELECT SCHEMA_NAME FROM INFORMATION_SCHEMA.SCHEMATA WHERE SCHEMA_NAME = '$dbname'");
    
    if (!$result->fetch()) {
        // Create database if it doesn't exist
        $pdo->exec("CREATE DATABASE `$dbname` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci");
        echo "Database created successfully\n";
    }

    // Select the database
    $pdo->exec("USE `$dbname`");

    // Check if users table exists and create if it doesn't
    $result = $pdo->query("SHOW TABLES LIKE 'users'");
    if (!$result->fetch()) {
        $sql = file_get_contents(dirname(__DIR__) . '/heart_disease.sql');
        $pdo->exec($sql);
        echo "Database tables created successfully\n";
    }

    // Insert default admin user if not exists
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM users WHERE role = 'admin'");
    $stmt->execute();
    if ($stmt->fetchColumn() == 0) {
        $defaultAdmin = [
            'username' => 'admin',
            'email' => 'admin@example.com',
            'password' => password_hash('admin123', PASSWORD_DEFAULT),
            'wallet_address' => '0x0000000000000000000000000000000000000000',
            'role' => 'admin'
        ];

        $stmt = $pdo->prepare("INSERT INTO users (username, email, password, wallet_address, role) VALUES (?, ?, ?, ?, ?)");
        $stmt->execute([
            $defaultAdmin['username'],
            $defaultAdmin['email'],
            $defaultAdmin['password'],
            $defaultAdmin['wallet_address'],
            $defaultAdmin['role']
        ]);
        echo "Default admin user created\n";
    }

    echo "Database check completed successfully\n";
    
} catch (PDOException $e) {
    die("Database Error: " . $e->getMessage() . "\n");
} 
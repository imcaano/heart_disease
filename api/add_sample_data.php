<?php
require_once dirname(__DIR__) . '/config.php';

try {
    // Add sample users if they don't exist
    $users = [
        [
            'username' => 'admin',
            'email' => 'admin@example.com',
            'password' => password_hash('admin123', PASSWORD_DEFAULT),
            'wallet_address' => '0x0000000000000000000000000000000000000000',
            'role' => 'admin'
        ],
        [
            'username' => 'user1',
            'email' => 'user1@example.com',
            'password' => password_hash('user123', PASSWORD_DEFAULT),
            'wallet_address' => '0xb0a09d11c251c4df082e5129aa7f7f33d85c71fb',
            'role' => 'user'
        ]
    ];

    // Insert or update users
    foreach ($users as $user) {
        $stmt = $pdo->prepare("SELECT id FROM users WHERE email = ?");
        $stmt->execute([$user['email']]);
        $existingUser = $stmt->fetch();

        if (!$existingUser) {
            $stmt = $pdo->prepare("
                INSERT INTO users (username, email, password, wallet_address, role) 
                VALUES (?, ?, ?, ?, ?)
            ");
            $stmt->execute([
                $user['username'],
                $user['email'],
                $user['password'],
                $user['wallet_address'],
                $user['role']
            ]);
        }
    }

    // Get user IDs
    $stmt = $pdo->prepare("SELECT id FROM users WHERE email = ?");
    $stmt->execute(['admin@example.com']);
    $adminId = $stmt->fetchColumn();
    
    $stmt->execute(['user1@example.com']);
    $userId = $stmt->fetchColumn();

    // Clear existing predictions
    $pdo->exec("DELETE FROM predictions");

    // Add sample predictions
    $predictions = [
        [
            'user_id' => $adminId,
            'age' => 65,
            'sex' => 1,
            'cp' => 2,
            'trestbps' => 140,
            'chol' => 250,
            'fbs' => 1,
            'restecg' => 0,
            'thalach' => 150,
            'exang' => 0,
            'oldpeak' => 2.3,
            'slope' => 1,
            'ca' => 2,
            'thal' => 2,
            'prediction_result' => 1,
            'confidence_score' => 0.85,
            'verified_by_expert' => 1,
            'created_at' => date('Y-m-d H:i:s', strtotime('-6 days'))
        ],
        [
            'user_id' => $adminId,
            'age' => 45,
            'sex' => 0,
            'cp' => 1,
            'trestbps' => 130,
            'chol' => 200,
            'fbs' => 0,
            'restecg' => 1,
            'thalach' => 170,
            'exang' => 0,
            'oldpeak' => 1.2,
            'slope' => 2,
            'ca' => 0,
            'thal' => 1,
            'prediction_result' => 0,
            'confidence_score' => 0.92,
            'verified_by_expert' => 1,
            'created_at' => date('Y-m-d H:i:s', strtotime('-4 days'))
        ],
        [
            'user_id' => $userId,
            'age' => 55,
            'sex' => 1,
            'cp' => 3,
            'trestbps' => 160,
            'chol' => 280,
            'fbs' => 1,
            'restecg' => 2,
            'thalach' => 140,
            'exang' => 1,
            'oldpeak' => 3.1,
            'slope' => 0,
            'ca' => 3,
            'thal' => 3,
            'prediction_result' => 1,
            'confidence_score' => 0.78,
            'verified_by_expert' => 1,
            'created_at' => date('Y-m-d H:i:s', strtotime('-2 days'))
        ],
        [
            'user_id' => $userId,
            'age' => 50,
            'sex' => 0,
            'cp' => 0,
            'trestbps' => 120,
            'chol' => 190,
            'fbs' => 0,
            'restecg' => 0,
            'thalach' => 160,
            'exang' => 0,
            'oldpeak' => 0.8,
            'slope' => 1,
            'ca' => 0,
            'thal' => 1,
            'prediction_result' => 0,
            'confidence_score' => 0.95,
            'verified_by_expert' => 1,
            'created_at' => date('Y-m-d H:i:s')
        ]
    ];

    // Insert sample predictions
    $stmt = $pdo->prepare("
        INSERT INTO predictions (
            user_id, age, sex, cp, trestbps, chol, fbs, restecg, thalach, 
            exang, oldpeak, slope, ca, thal, prediction_result, 
            confidence_score, verified_by_expert, created_at
        ) VALUES (
            :user_id, :age, :sex, :cp, :trestbps, :chol, :fbs, :restecg, 
            :thalach, :exang, :oldpeak, :slope, :ca, :thal, :prediction_result,
            :confidence_score, :verified_by_expert, :created_at
        )
    ");

    foreach ($predictions as $prediction) {
        $stmt->execute($prediction);
    }

    // Update user statistics
    $pdo->exec("
        UPDATE users u 
        SET 
            total_predictions = (
                SELECT COUNT(*) 
                FROM predictions p 
                WHERE p.user_id = u.id
            ),
            prediction_accuracy = (
                SELECT AVG(confidence_score) * 100
                FROM predictions p 
                WHERE p.user_id = u.id
            )
    ");

    echo "Sample data added successfully\n";
    
} catch (PDOException $e) {
    die("Database Error: " . $e->getMessage() . "\n");
} 
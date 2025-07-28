<?php
session_start();
require_once '../config/database.php';

header('Content-Type: application/json');

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'User not logged in']);
    exit;
}

try {
    // Get POST data
    $data = [
        'age' => $_POST['age'],
        'sex' => $_POST['sex'],
        'cp' => $_POST['cp'],
        'trestbps' => $_POST['trestbps'],
        'chol' => $_POST['chol'],
        'fbs' => $_POST['fbs'],
        'restecg' => $_POST['restecg'],
        'thalach' => $_POST['thalach'],
        'exang' => $_POST['exang'],
        'oldpeak' => $_POST['oldpeak'],
        'slope' => $_POST['slope'],
        'ca' => $_POST['ca'],
        'thal' => $_POST['thal'],
        'prediction' => $_POST['prediction'],
        'user_id' => $_SESSION['user_id'],
        'prediction_date' => $_POST['prediction_date']
    ];

    // Check for duplicate prediction in the last 5 minutes
    $checkSql = "SELECT id FROM predictions WHERE user_id = :user_id AND age = :age AND sex = :sex AND cp = :cp AND trestbps = :trestbps AND chol = :chol AND fbs = :fbs AND restecg = :restecg AND thalach = :thalach AND exang = :exang AND oldpeak = :oldpeak AND slope = :slope AND ca = :ca AND thal = :thal AND prediction = :prediction AND prediction_date >= DATE_SUB(NOW(), INTERVAL 5 MINUTE)";
    $checkStmt = $pdo->prepare($checkSql);
    $checkStmt->execute($data);
    if ($checkStmt->fetch()) {
        echo json_encode(['success' => false, 'message' => 'Duplicate prediction detected.']);
        exit;
    }

    // Prepare SQL statement
    $sql = "INSERT INTO predictions (
        age, sex, cp, trestbps, chol, fbs, restecg, thalach, 
        exang, oldpeak, slope, ca, thal, prediction, user_id, prediction_date
    ) VALUES (
        :age, :sex, :cp, :trestbps, :chol, :fbs, :restecg, :thalach,
        :exang, :oldpeak, :slope, :ca, :thal, :prediction, :user_id, :prediction_date
    )";

    $stmt = $pdo->prepare($sql);
    $result = $stmt->execute($data);

    if ($result) {
        echo json_encode(['success' => true, 'message' => 'Prediction saved successfully']);
    } else {
        echo json_encode(['success' => false, 'message' => 'Failed to save prediction']);
    }
} catch (PDOException $e) {
    echo json_encode(['success' => false, 'message' => 'Database error: ' . $e->getMessage()]);
} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => 'Error: ' . $e->getMessage()]);
}
?> 
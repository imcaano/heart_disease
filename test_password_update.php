<?php
session_start();
require_once 'config.php';

// Test password update functionality
echo "<h2>Password Update Test</h2>";

// Check if user is logged in
if (!isset($_SESSION['user'])) {
    echo "<p style='color: red;'>❌ No user logged in. Please login first.</p>";
    echo "<p><a href='index.php?route=login'>Go to Login</a></p>";
    exit;
}

$user_id = $_SESSION['user']['id'];
$username = $_SESSION['user']['username'];

echo "<p>✅ User logged in: <strong>$username</strong> (ID: $user_id)</p>";

// Test the password update API
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $current_password = $_POST['current_password'] ?? '';
    $new_password = $_POST['new_password'] ?? '';
    $confirm_password = $_POST['confirm_password'] ?? '';
    
    // Simulate the API call
    $input = [
        'current_password' => $current_password,
        'new_password' => $new_password,
        'confirm_password' => $confirm_password
    ];
    
    // Validate input
    if (empty($current_password) || empty($new_password) || empty($confirm_password)) {
        echo "<p style='color: red;'>❌ All password fields are required</p>";
    } elseif ($new_password !== $confirm_password) {
        echo "<p style='color: red;'>❌ New passwords do not match</p>";
    } elseif (strlen($new_password) < 6) {
        echo "<p style='color: red;'>❌ New password must be at least 6 characters</p>";
    } else {
        // Fetch user from DB
        $stmt = $pdo->prepare('SELECT password FROM users WHERE id = ?');
        $stmt->execute([$user_id]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if (!$user) {
            echo "<p style='color: red;'>❌ User not found</p>";
        } elseif (!password_verify($current_password, $user['password'])) {
            echo "<p style='color: red;'>❌ Current password is incorrect</p>";
        } else {
            // Hash new password
            $hashed = password_hash($new_password, PASSWORD_DEFAULT);
            $stmt = $pdo->prepare('UPDATE users SET password = ? WHERE id = ?');
            $stmt->execute([$hashed, $user_id]);
            
            // Log activity
            $stmt = $pdo->prepare('INSERT INTO user_activity_log (user_id, activity_type, description, ip_address) VALUES (?, ?, ?, ?)');
            $stmt->execute([$user_id, 'password_update', 'Password updated via test', $_SERVER['REMOTE_ADDR']]);
            
            echo "<p style='color: green;'>✅ Password updated successfully!</p>";
            echo "<p>New password: <strong>$new_password</strong></p>";
        }
    }
}

// Show test form
?>
<form method="POST" style="max-width: 400px; margin: 20px 0; padding: 20px; border: 1px solid #ccc; border-radius: 5px;">
    <h3>Test Password Update</h3>
    <div style="margin-bottom: 15px;">
        <label>Current Password:</label><br>
        <input type="password" name="current_password" required style="width: 100%; padding: 8px; margin-top: 5px;">
    </div>
    <div style="margin-bottom: 15px;">
        <label>New Password:</label><br>
        <input type="password" name="new_password" required style="width: 100%; padding: 8px; margin-top: 5px;">
    </div>
    <div style="margin-bottom: 15px;">
        <label>Confirm New Password:</label><br>
        <input type="password" name="confirm_password" required style="width: 100%; padding: 8px; margin-top: 5px;">
    </div>
    <button type="submit" style="background: #007bff; color: white; padding: 10px 20px; border: none; border-radius: 3px; cursor: pointer;">
        Test Password Update
    </button>
</form>

<p><a href="index.php">← Back to Home</a></p> 
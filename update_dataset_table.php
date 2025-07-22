<?php
// Script to update the dataset table with user_id field

require_once 'includes/db.php';

echo "<h2>Updating Dataset Table</h2>";

try {
    // Read the SQL file
    $sql = file_get_contents('update_dataset_table.sql');
    
    // Execute the SQL
    $db->exec($sql);
    
    echo "<p style='color: green;'>✅ Dataset table updated successfully!</p>";
    
    // Check if the user_id column exists
    $result = $db->query("SHOW COLUMNS FROM dataset LIKE 'user_id'");
    if ($result->rowCount() > 0) {
        echo "<p>✅ The user_id column has been added to the dataset table.</p>";
    } else {
        echo "<p style='color: red;'>❌ Failed to add the user_id column to the dataset table.</p>";
    }
    
    // Check if the foreign key constraint exists
    $result = $db->query("
        SELECT COUNT(*) as count
        FROM information_schema.TABLE_CONSTRAINTS
        WHERE CONSTRAINT_SCHEMA = DATABASE()
        AND CONSTRAINT_NAME = 'dataset_ibfk_1'
        AND CONSTRAINT_TYPE = 'FOREIGN KEY'
    ");
    $constraintExists = $result->fetch()['count'] > 0;
    
    if ($constraintExists) {
        echo "<p>✅ The foreign key constraint has been added to the dataset table.</p>";
    } else {
        echo "<p style='color: red;'>❌ Failed to add the foreign key constraint to the dataset table.</p>";
    }
    
} catch (PDOException $e) {
    echo "<p style='color: red;'>❌ Error: " . $e->getMessage() . "</p>";
}

echo "<p><a href='index.php?route=dataset'>Go to Dataset Page</a></p>"; 
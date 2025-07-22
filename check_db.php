<?php
// Enable error display
ini_set('display_errors', 1);
error_reporting(E_ALL);

require_once 'includes/db.php';

echo "<h2>Database Connection Check</h2>";

try {
    // Check database connection
    $db->query("SELECT 1");
    echo "✅ Database connection successful<br>";

    // Check if database exists
    $result = $db->query("SELECT DATABASE() as dbname");
    $dbname = $result->fetch()['dbname'];
    echo "✅ Connected to database: $dbname<br>";

    // Check if tables exist
    $tables = ['users', 'predictions'];
    foreach ($tables as $table) {
        $result = $db->query("SHOW TABLES LIKE '$table'");
        if ($result->rowCount() > 0) {
            echo "✅ Table '$table' exists<br>";
            
            // Show table structure
            echo "<h3>Table Structure: $table</h3>";
            $result = $db->query("DESCRIBE $table");
            echo "<table border='1'>";
            echo "<tr><th>Field</th><th>Type</th><th>Null</th><th>Key</th><th>Default</th><th>Extra</th></tr>";
            while ($row = $result->fetch()) {
                echo "<tr>";
                echo "<td>" . $row['Field'] . "</td>";
                echo "<td>" . $row['Type'] . "</td>";
                echo "<td>" . $row['Null'] . "</td>";
                echo "<td>" . $row['Key'] . "</td>";
                echo "<td>" . $row['Default'] . "</td>";
                echo "<td>" . $row['Extra'] . "</td>";
                echo "</tr>";
            }
            echo "</table>";
        } else {
            echo "❌ Table '$table' does not exist<br>";
        }
    }

} catch (PDOException $e) {
    echo "❌ Error: " . $e->getMessage() . "<br>";
} 
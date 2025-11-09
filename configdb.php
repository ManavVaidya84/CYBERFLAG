<?php
// Database configuration settings
define('DB_HOST', 'localhost'); // Database host
define('DB_USER', 'root'); // Database username
define('DB_PASS', ''); // Database password
define('DB_NAME', 'vivacity_ctf'); // Database name

try {
    // Create a PDO instance for secure database connection
    $dsn = "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=utf8mb4";
    $pdo = new PDO($dsn, DB_USER, DB_PASS);
    
    // Set PDO attributes for security and error handling
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $pdo->setAttribute(PDO::ATTR_EMULATE_PREPARES, false); // Prevent SQL injection
    $pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC); // Default fetch mode
    
} catch (PDOException $e) {
    // Log error securely (avoid exposing sensitive information)
    error_log("Database connection failed: " . $e->getMessage());
    // Display generic error message to users
    die("Connection failed. Please try again later.");
}
?>
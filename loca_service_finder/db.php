<?php
// ============================================================
// db.php — Database connection
// ============================================================
define('DB_HOST', 'localhost');
define('DB_USER', 'root');       // Default XAMPP user
define('DB_PASS', '');           // Default XAMPP password (empty)
define('DB_NAME', 'local_service_finder');

$conn = mysqli_connect(DB_HOST, DB_USER, DB_PASS, DB_NAME);

if (!$conn) {
    // In production, log the error instead of exposing it
    die(json_encode([
        'success' => false,
        'message' => 'Database connection failed: ' . mysqli_connect_error()
    ]));
}

mysqli_set_charset($conn, 'utf8mb4');

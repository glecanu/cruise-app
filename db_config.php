<?php
// Start session for admin pages
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// --- Database Configuration ---
// For Azure, we'll get the path from an App Setting for better security and persistence.
// For local development, you can use a relative path.
$sqlite_db_path = getenv('SQLITE_DB_PATH') ?: __DIR__ . '/data/submissions.sqlite';
$db_dir = dirname($sqlite_db_path);

// Ensure the data directory exists
if (!is_dir($db_dir)) {
    if (!mkdir($db_dir, 0755, true)) {
        die("Failed to create database directory: " . $db_dir);
    }
}

try {
    $pdo = new PDO('sqlite:' . $sqlite_db_path);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);

    // Create table if it doesn't exist
    $pdo->exec("CREATE TABLE IF NOT EXISTS submissions (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        firstName TEXT NOT NULL,
        homeCity TEXT NOT NULL,
        homeCountry TEXT NOT NULL,
        submissionTime DATETIME DEFAULT CURRENT_TIMESTAMP
    )");

} catch (PDOException $e) {
    // For production, log this error instead of die()
    die("Database connection failed or table creation failed: " . $e->getMessage() . " (Path: " . $sqlite_db_path . ")");
}

// --- Admin Configuration ---
// For a real app, store this HASHED password in an environment variable or secure config.
// Password is "admin123"
define('ADMIN_USERNAME', getenv('ADMIN_USER') ?: 'admin');
define('ADMIN_PASSWORD_HASH', getenv('ADMIN_PASS_HASH') ?: '$2y$10$QjA7M2cXR0B6I8S.HSAdi.0GvjHWYWGq0hOqHdp8uXl.J.J9uC/aa');

function isAdminLoggedIn() {
    return isset($_SESSION['admin_logged_in']) && $_SESSION['admin_logged_in'] === true;
}

function ensureAdminLoggedIn() {
    if (!isAdminLoggedIn()) {
        header('Location: admin_login.php?redirect=' . urlencode($_SERVER['REQUEST_URI']));
        exit;
    }
}
?>
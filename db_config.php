<?php

error_reporting(E_ALL);
ini_set('display_errors', 1); // Try to force display to browser/log
ini_set('log_errors', 1);
ini_set('error_log', '/home/LogFiles/php_errors.log'); // Explicitly log to a persistent file

// Start session for admin pages
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// --- Database Configuration ---
$default_db_path = __DIR__ . '/data/submissions.sqlite'; // Fallback for local dev if env var not set
$sqlite_db_path = getenv('SQLITE_DB_PATH') ?: $default_db_path;

$db_dir = dirname($sqlite_db_path);

// Check if the directory exists
if (!is_dir($db_dir)) {
    // Attempt to create the directory
    // The 'true' parameter allows creation of nested directories
    if (!mkdir($db_dir, 0775, true)) { // Changed to 0775
        $error = error_get_last();
        die("Failed to create database directory: " . $db_dir . " - Error: " . ($error['message'] ?? 'Unknown error'));
    }
    // Optional: Log successful directory creation
    // error_log("Database directory created: " . $db_dir);
}

// Check if the directory is writable AFTER attempting to create it
if (!is_writable($db_dir)) { // This check is critical
    $actual_permissions = substr(sprintf('%o', fileperms($db_dir)), -4);
    die("Database directory (" . $db_dir . ") is NOT WRITABLE by PHP. Permissions: " . $actual_permissions . ". PHP User (effective): " . get_current_user() . " (UID: " . getmyuid() . " GID: " . getmygid() . ")");
}


try {
    $pdo = new PDO('sqlite:' . $sqlite_db_path);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);

    $pdo->exec("CREATE TABLE IF NOT EXISTS submissions (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        firstName TEXT NOT NULL,
        homeCity TEXT NOT NULL,
        homeCountry TEXT NOT NULL,
        submissionTime DATETIME DEFAULT CURRENT_TIMESTAMP
    )");

} catch (PDOException $e) {
    die("Database connection failed or table creation failed: " . $e->getMessage() . " (Path attempted: " . $sqlite_db_path . ")");
}

// --- Admin Configuration ---
define('ADMIN_USERNAME', getenv('ADMIN_USER') ?: 'admin');
define('ADMIN_PASSWORD_HASH', getenv('ADMIN_PASS_HASH') ?: '$2y$10$QjA7M2cXR0B6I8S.HSAdi.0GvjHWYWGq0hOqHdp8uXl.J.J9uC/aa'); // admin123

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
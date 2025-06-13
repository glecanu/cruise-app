<?php
require_once 'db_config.php'; // For session_start and admin constants

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = $_POST['username'] ?? '';
    $password = $_POST['password'] ?? '';
    $redirect = $_POST['redirect'] ?? 'admin_dashboard.php';

    if ($username === ADMIN_USERNAME && password_verify($password, ADMIN_PASSWORD_HASH)) {
        $_SESSION['admin_logged_in'] = true;
        $_SESSION['admin_username'] = $username;
        header('Location: ' . $redirect); // Redirect to intended page or dashboard
        exit;
    } else {
        header('Location: admin_login.php?error=1&redirect=' . urlencode($redirect));
        exit;
    }
} else {
    header('Location: admin_login.php');
    exit;
}
?>
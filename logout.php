<?php
require_once 'db_config.php'; // For session_start()

$_SESSION = array(); // Unset all session variables
if (ini_get("session.use_cookies")) {
    $params = session_get_cookie_params();
    setcookie(session_name(), '', time() - 42000,
        $params["path"], $params["domain"],
        $params["secure"], $params["httponly"]
    );
}
session_destroy();
header('Location: admin_login.php?status=logged_out');
exit;
?>
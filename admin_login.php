<?php require_once 'db_config.php'; // For session_start and admin constants

if (isAdminLoggedIn()) {
    header('Location: admin_dashboard.php');
    exit;
}
$redirect_url = isset($_GET['redirect']) ? htmlspecialchars($_GET['redirect']) : 'admin_dashboard.php';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Admin Login</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <div class="container">
        <h1>Admin Login</h1>
        <nav>
            <a href="index.php">Home</a>
        </nav>
        <?php if (isset($_GET['error'])): ?>
            <p class="error">Invalid username or password.</p>
        <?php endif; ?>
        <form action="admin_auth.php" method="POST">
            <input type="hidden" name="redirect" value="<?php echo $redirect_url; ?>">
            <div>
                <label for="username">Username:</label>
                <input type="text" id="username" name="username" required>
            </div>
            <div>
                <label for="password">Password:</label>
                <input type="password" id="password" name="password" required>
            </div>
            <div>
                <button type="submit">Login</button>
            </div>
        </form>
    </div>
</body>
</html>
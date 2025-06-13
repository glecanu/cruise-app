<?php
require_once 'db_config.php';
ensureAdminLoggedIn();

$submission_id = $_GET['id'] ?? null;
if (!$submission_id) {
    header('Location: admin_dashboard.php?status=Invalid ID&status_type=error');
    exit;
}

try {
    $stmt = $pdo->prepare("SELECT * FROM submissions WHERE id = ?");
    $stmt->execute([$submission_id]);
    $submission = $stmt->fetch();

    if (!$submission) {
        header('Location: admin_dashboard.php?status=Submission not found&status_type=error');
        exit;
    }
} catch (PDOException $e) {
    header('Location: admin_dashboard.php?status=Error fetching submission&status_type=error');
    exit;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Edit Submission</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <div class="container">
        <h1>Edit Submission #<?php echo htmlspecialchars($submission['id']); ?></h1>
        <nav>
            <a href="admin_dashboard.php">Back to Dashboard</a> |
            <a href="logout.php">Logout</a>
        </nav>

        <form action="update_submission_handler.php" method="POST">
            <input type="hidden" name="id" value="<?php echo htmlspecialchars($submission['id']); ?>">
            <div>
                <label for="firstName">First Name:</label>
                <input type="text" id="firstName" name="firstName" value="<?php echo htmlspecialchars($submission['firstName']); ?>" required>
            </div>
            <div>
                <label for="homeCity">Home City:</label>
                <input type="text" id="homeCity" name="homeCity" value="<?php echo htmlspecialchars($submission['homeCity']); ?>" required>
            </div>
            <div>
                <label for="homeCountry">Home Country:</label>
                <input type="text" id="homeCountry" name="homeCountry" value="<?php echo htmlspecialchars($submission['homeCountry']); ?>" required>
            </div>
            <div>
                <button type="submit">Update Submission</button>
            </div>
        </form>
    </div>
</body>
</html>
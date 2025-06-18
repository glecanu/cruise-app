<?php
require_once 'db_config.php';
ensureAdminLoggedIn();

$partitionKey = $_GET['partitionKey'] ?? null;
$rowKey = $_GET['rowKey'] ?? null;

if (!$partitionKey || !$rowKey) {
    header('Location: admin_dashboard.php?status=Missing ID for edit&status_type=error');
    exit;
}

$submission = null;
try {
    $result = $tableClient->getEntity($storageTableName, $partitionKey, $rowKey);
    $submission = $result->getEntity();
} catch (\MicrosoftAzure\Storage\Common\Exceptions\ServiceException $e) {
    // Check if it's a "ResourceNotFound" error
    if ($e->getCode() == 404) { // HttpStatusCode::NOT_FOUND
        header('Location: admin_dashboard.php?status=Submission not found (PK:'.$partitionKey.', RK:'.$rowKey.')&status_type=error');
    } else {
        header('Location: admin_dashboard.php?status=Error fetching submission: '.htmlspecialchars($e->getErrorText()).'&status_type=error');
        error_log("Azure Table Storage GetEntity Error for PK:{$partitionKey}, RK:{$rowKey} - " . $e->getErrorText() . " (Code: " . $e->getCode() . ")");
    }
    exit;
} catch (Exception $e) {
    header('Location: admin_dashboard.php?status=General error fetching submission&status_type=error');
    error_log("General GetEntity Error for PK:{$partitionKey}, RK:{$rowKey} - " . $e->getMessage());
    exit;
}

if (!$submission) { // Should be caught by ServiceException 404, but as a fallback.
    header('Location: admin_dashboard.php?status=Submission not found after fetch attempt&status_type=error');
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
        <h1>Edit Submission (RowKey: <?php echo htmlspecialchars($submission->getRowKey()); ?>)</h1>
        <nav>
            <a href="admin_dashboard.php">Back to Dashboard</a> |
            <a href="logout.php">Logout</a>
        </nav>

        <form action="update_submission_handler.php" method="POST">
            <input type="hidden" name="partitionKey" value="<?php echo htmlspecialchars($submission->getPartitionKey()); ?>">
            <input type="hidden" name="rowKey" value="<?php echo htmlspecialchars($submission->getRowKey()); ?>">
            <div>
                <label for="firstName">First Name:</label>
                <input type="text" id="firstName" name="firstName" value="<?php echo htmlspecialchars($submission->getPropertyValue('FirstName')); ?>" required>
            </div>
            <div>
                <label for="homeCity">Home City:</label>
                <input type="text" id="homeCity" name="homeCity" value="<?php echo htmlspecialchars($submission->getPropertyValue('HomeCity')); ?>" required>
            </div>
            <div>
                <label for="homeCountry">Home Country:</label>
                <input type="text" id="homeCountry" name="homeCountry" value="<?php echo htmlspecialchars($submission->getPropertyValue('HomeCountry')); ?>" required>
            </div>
            <div>
                <button type="submit">Update Submission</button>
            </div>
        </form>
    </div>
</body>
</html>
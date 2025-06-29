<?php
require_once 'db_config.php';
ensureAdminLoggedIn();

$partitionKey = $_GET['partitionKey'] ?? null;
$rowKey = $_GET['rowKey'] ?? null;

if (!$partitionKey || !$rowKey) {
    header('Location: admin_dashboard.php?status_message=Missing ID for edit&message_type=error');
    exit;
}

$submission = null;
try {
    $result = $tableClient->getEntity($storageTableName, $partitionKey, $rowKey);
    $submission = $result->getEntity();
} catch (\MicrosoftAzure\Storage\Common\Exceptions\ServiceException $e) {
    if ($e->getCode() == 404) {
        header('Location: admin_dashboard.php?status_message=Submission not found&message_type=error');
    } else {
        header('Location: admin_dashboard.php?status_message=Error fetching submission&message_type=error');
        error_log("Azure Table Storage GetEntity Error: " . $e->getErrorText());
    }
    exit;
}

if (!$submission) {
    header('Location: admin_dashboard.php?status_message=Submission not found&message_type=error');
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
                <label for="duckNumber">Duck Number:</label>
                <input type="text" id="duckNumber" name="duckNumber" value="<?php echo htmlspecialchars($submission->getPropertyValue('DuckNumber')); ?>">
            </div>
            <div>
                <label for="firstName">First Name:</label>
                <input type="text" id="firstName" name="firstName" value="<?php echo htmlspecialchars($submission->getPropertyValue('FirstName')); ?>">
            </div>
            <div>
                <label for="homeCity">Home City:</label>
                <input type="text" id="homeCity" name="homeCity" value="<?php echo htmlspecialchars($submission->getPropertyValue('HomeCity')); ?>">
            </div>
            <div>
                <label for="homeCountry">Home Country:</label>
                <input type="text" id="homeCountry" name="homeCountry" value="<?php echo htmlspecialchars($submission->getPropertyValue('HomeCountry')); ?>">
            </div>
            <div>
                <button type="submit">Update Submission</button>
            </div>
        </form>
    </div>
</body>
</html>
<?php
require_once 'db_config.php';
ensureAdminLoggedIn();

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $partitionKey = $_POST['partitionKey'] ?? null;
    $rowKey = $_POST['rowKey'] ?? null;

    if (!$partitionKey || !$rowKey) {
        header("Location: admin_dashboard.php?status=Invalid ID for deletion&status_type=error");
        exit;
    }

    try {
        // For deleteEntity, ETag is also relevant for concurrency, but '*' can be used to bypass check.
        // The SDK might handle this gracefully or require an ETag. If issues, pass '*' as the third param.
        $tableClient->deleteEntity($storageTableName, $partitionKey, $rowKey);
        header("Location: admin_dashboard.php?status=Submission deleted successfully&status_type=success");
        exit;
    } catch (\MicrosoftAzure\Storage\Common\Exceptions\ServiceException $e) {
        error_log("Azure Table Storage Delete Error for PK:{$partitionKey}, RK:{$rowKey} - " . $e->getErrorText() . " (Code: " . $e->getCode() . ")");
        header("Location: admin_dashboard.php?status=Error deleting submission: ".htmlspecialchars($e->getErrorText())."&status_type=error");
        exit;
    } catch (Exception $e) {
        error_log("General Delete Error for PK:{$partitionKey}, RK:{$rowKey} - " . $e->getMessage());
        header("Location: admin_dashboard.php?status=General error deleting submission&status_type=error");
        exit;
    }
} else {
    header("Location: admin_dashboard.php");
    exit;
}
?>
<?php
require_once 'db_config.php';
ensureAdminLoggedIn();

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $partitionKey = $_POST['partitionKey'] ?? null;
    $rowKey = $_POST['rowKey'] ?? null;
    $firstName = trim($_POST['firstName'] ?? '');
    $homeCity = trim($_POST['homeCity'] ?? '');
    $homeCountry = trim($_POST['homeCountry'] ?? '');
    $duckNumber = trim($_POST['duckNumber'] ?? '');

    if (!$partitionKey || !$rowKey) {
        header("Location: admin_dashboard.php?status_message=Invalid data for update (missing keys)&message_type=error");
        exit;
    }
    
    // No validation for empty fields since all are now optional.

    $entityToUpdate = new \MicrosoftAzure\Storage\Table\Models\Entity();
    $entityToUpdate->setPartitionKey($partitionKey);
    $entityToUpdate->setRowKey($rowKey);
    $entityToUpdate->addProperty('DuckNumber', \MicrosoftAzure\Storage\Table\Models\EdmType::STRING, $duckNumber);
    $entityToUpdate->addProperty('FirstName', \MicrosoftAzure\Storage\Table\Models\EdmType::STRING, $firstName);
    $entityToUpdate->addProperty('HomeCity', \MicrosoftAzure\Storage\Table\Models\EdmType::STRING, $homeCity);
    $entityToUpdate->addProperty('HomeCountry', \MicrosoftAzure\Storage\Table\Models\EdmType::STRING, $homeCountry);
    
    try {
        // mergeEntity updates specified properties and doesn't require ETag management for this simple app.
        $tableClient->mergeEntity($storageTableName, $entityToUpdate);

        header("Location: admin_dashboard.php?status_message=Submission updated successfully&message_type=success");
        exit;
    } catch (\MicrosoftAzure\Storage\Common\Exceptions\ServiceException $e) {
        error_log("Azure Table Storage Update Error: " . $e->getErrorText());
        header("Location: admin_dashboard.php?status_message=Error updating submission&message_type=error");
        exit;
    }
} else {
    header("Location: admin_dashboard.php");
    exit;
}
?>
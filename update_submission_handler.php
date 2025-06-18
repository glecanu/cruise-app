<?php
require_once 'db_config.php';
ensureAdminLoggedIn();

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $partitionKey = $_POST['partitionKey'] ?? null;
    $rowKey = $_POST['rowKey'] ?? null;
    $firstName = trim($_POST['firstName'] ?? '');
    $homeCity = trim($_POST['homeCity'] ?? '');
    $homeCountry = trim($_POST['homeCountry'] ?? '');

    if (!$partitionKey || !$rowKey || empty($firstName) || empty($homeCity) || empty($homeCountry)) {
        // Ideally redirect back to edit form with error and prefill data
        header("Location: admin_dashboard.php?status=Invalid data for update&status_type=error");
        exit;
    }

    // Fetch existing entity to get all its properties (like SubmissionTime)
    // because updateEntity replaces the entire entity.
    // Alternatively, use mergeEntity if you only want to update these specific fields.
    // For simplicity with this example, let's re-fetch and update all.
    $existingEntity = null;
    try {
        $result = $tableClient->getEntity($storageTableName, $partitionKey, $rowKey);
        $existingEntity = $result->getEntity();
    } catch (\MicrosoftAzure\Storage\Common\Exceptions\ServiceException $e) {
        error_log("Azure Table Storage GetEntity before Update Error for PK:{$partitionKey}, RK:{$rowKey} - " . $e->getErrorText());
        header("Location: admin_dashboard.php?status=Error fetching entity before update&status_type=error");
        exit;
    }

    if (!$existingEntity) {
        header("Location: admin_dashboard.php?status=Entity to update not found&status_type=error");
        exit;
    }

    $entityToUpdate = new \MicrosoftAzure\Storage\Table\Models\Entity();
    $entityToUpdate->setPartitionKey($partitionKey);
    $entityToUpdate->setRowKey($rowKey);
    $entityToUpdate->addProperty('FirstName', \MicrosoftAzure\Storage\Table\Models\EdmType::STRING, $firstName);
    $entityToUpdate->addProperty('HomeCity', \MicrosoftAzure\Storage\Table\Models\EdmType::STRING, $homeCity);
    $entityToUpdate->addProperty('HomeCountry', \MicrosoftAzure\Storage\Table\Models\EdmType::STRING, $homeCountry);
    // Preserve existing SubmissionTime if it exists, otherwise set current time
    $submissionTime = $existingEntity->getPropertyValue('SubmissionTime');
    if (!$submissionTime || !($submissionTime instanceof DateTime)) {
        $submissionTime = new DateTime(); // Should not happen if data is consistent
    }
    $entityToUpdate->addProperty('SubmissionTime', \MicrosoftAzure\Storage\Table\Models\EdmType::DATETIME, $submissionTime);


    try {
        // updateEntity requires an ETag for optimistic concurrency.
        // If you don't care about concurrency for this simple app, you can use mergeEntity
        // which doesn't require ETag by default or you can pass '*' as ETag for updateEntity.
        // For simplicity, let's use mergeEntity here. If using updateEntity, you'd get ETag from $existingEntity->getETag().
        // $tableClient->updateEntity($storageTableName, $entityToUpdate); // This needs ETag management

        // Using mergeEntity is often simpler if you don't need strong concurrency control via ETags.
        // It updates only the properties you provide. If a property isn't in $entityToUpdate, it's not changed.
        $tableClient->mergeEntity($storageTableName, $entityToUpdate);

        header("Location: admin_dashboard.php?status=Submission updated successfully&status_type=success");
        exit;
    } catch (\MicrosoftAzure\Storage\Common\Exceptions\ServiceException $e) {
        error_log("Azure Table Storage Update Error for PK:{$partitionKey}, RK:{$rowKey} - " . $e->getErrorText() . " (Code: " . $e->getCode() . ")");
        header("Location: admin_dashboard.php?status=Error updating submission: ".htmlspecialchars($e->getErrorText())."&status_type=error");
        exit;
    } catch (Exception $e) {
        error_log("General Update Error for PK:{$partitionKey}, RK:{$rowKey} - " . $e->getMessage());
        header("Location: admin_dashboard.php?status=General error updating submission&status_type=error");
        exit;
    }
} else {
    header("Location: admin_dashboard.php");
    exit;
}
?>
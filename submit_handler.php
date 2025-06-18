<?php
require_once 'db_config.php'; // Includes $tableClient

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $firstName = trim($_POST['firstName'] ?? '');
    $homeCity = trim($_POST['homeCity'] ?? '');
    $homeCountry = trim($_POST['homeCountry'] ?? '');

    if (empty($firstName) || empty($homeCity) || empty($homeCountry)) {
        header("Location: index.php?status=invalid");
        exit;
    }

    // For Table Storage, we need a PartitionKey and a RowKey.
    // Let's use 'submission' as a PartitionKey for all entries for simplicity in this example.
    // For larger scale, you might partition by date (e.g., YYYY-MM) or user ID, etc.
    $partitionKey = "submission";
    $rowKey = generateRowKey(); // From db_config.php or implement here

    $entity = new Entity();
    $entity->setPartitionKey($partitionKey);
    $entity->setRowKey($rowKey);
    $entity->addProperty('FirstName', EdmType::STRING, $firstName);
    $entity->addProperty('HomeCity', EdmType::STRING, $homeCity);
    $entity->addProperty('HomeCountry', EdmType::STRING, $homeCountry);
    $entity->addProperty('SubmissionTime', EdmType::DATETIME, new DateTime());

    try {
        $tableClient->insertEntity($storageTableName, $entity);
        header("Location: index.php?status=success");
        exit;
    } catch (ServiceException $e) {
        // Log error $e->getMessage() / $e->getCode() / $e->getErrorText()
        error_log("Azure Table Storage Insert Error: " . $e->getErrorText());
        header("Location: index.php?status=error");
        exit;
    } catch (Exception $e) {
        error_log("General Insert Error: " . $e->getMessage());
        header("Location: index.php?status=error");
        exit;
    }
} else {
    header("Location: index.php");
    exit;
}
?>
<?php
require_once 'db_config.php'; // Includes $tableClient, $storageTableName, generateRowKey(), EdmType, Entity

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $firstName = trim($_POST['firstName'] ?? '');
    $homeCity = trim($_POST['homeCity'] ?? '');
    $homeCountry = trim($_POST['homeCountry'] ?? '');

    if (empty($firstName) || empty($homeCity) || empty($homeCountry)) {
        header("Location: index.php?status=invalid");
        exit;
    }

    // PartitionKey: Using a static value for simplicity. For larger scale, consider dates or categories.
    $partitionKey = "submission";
    $rowKey = generateRowKey(); // Generates a UUID

    $entity = new \MicrosoftAzure\Storage\Table\Models\Entity();
    $entity->setPartitionKey($partitionKey);
    $entity->setRowKey($rowKey);
    $entity->addProperty('FirstName', \MicrosoftAzure\Storage\Table\Models\EdmType::STRING, $firstName);
    $entity->addProperty('HomeCity', \MicrosoftAzure\Storage\Table\Models\EdmType::STRING, $homeCity);
    $entity->addProperty('HomeCountry', \MicrosoftAzure\Storage\Table\Models\EdmType::STRING, $homeCountry);
    $entity->addProperty('SubmissionTime', \MicrosoftAzure\Storage\Table\Models\EdmType::DATETIME, new DateTime()); // Stored as UTC

    try {
        $tableClient->insertEntity($storageTableName, $entity);
        header("Location: index.php?status=success");
        exit;
    } catch (\MicrosoftAzure\Storage\Common\Exceptions\ServiceException $e) {
        error_log("Azure Table Storage Insert Error for PK:{$partitionKey}, RK:{$rowKey} - " . $e->getErrorText() . " (Code: " . $e->getCode() . ")");
        header("Location: index.php?status=error&msg=service_exception");
        exit;
    } catch (Exception $e) {
        error_log("General Insert Error for PK:{$partitionKey}, RK:{$rowKey} - " . $e->getMessage());
        header("Location: index.php?status=error&msg=general_exception");
        exit;
    }
} else {
    // Not a POST request
    header("Location: index.php");
    exit;
}
?>
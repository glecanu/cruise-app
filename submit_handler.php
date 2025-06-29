<?php
require_once 'db_config.php'; // Includes $tableClient, $storageTableName, generateRowKey(), Entity, EdmType

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $firstName = trim($_POST['firstName'] ?? '');
    $homeCity = trim($_POST['homeCity'] ?? '');
    $homeCountry = trim($_POST['homeCountry'] ?? '');
    $duckNumber = trim($_POST['duckNumber'] ?? '');

    // Validation for required fields is removed as they are now optional.

    $partitionKey = "submission";
    $rowKey = generateRowKey();

    $entity = new \MicrosoftAzure\Storage\Table\Models\Entity();
    $entity->setPartitionKey($partitionKey);
    $entity->setRowKey($rowKey);
    $entity->addProperty('DuckNumber', \MicrosoftAzure\Storage\Table\Models\EdmType::STRING, $duckNumber);
    $entity->addProperty('FirstName', \MicrosoftAzure\Storage\Table\Models\EdmType::STRING, $firstName);
    $entity->addProperty('HomeCity', \MicrosoftAzure\Storage\Table\Models\EdmType::STRING, $homeCity);
    $entity->addProperty('HomeCountry', \MicrosoftAzure\Storage\Table\Models\EdmType::STRING, $homeCountry);
    $entity->addProperty('SubmissionTime', \MicrosoftAzure\Storage\Table\Models\EdmType::DATETIME, new DateTime());

    try {
        $tableClient->insertEntity($storageTableName, $entity);
        header("Location: index.php?status=success");
        exit;
    } catch (\MicrosoftAzure\Storage\Common\Exceptions\ServiceException $e) {
        error_log("Azure Table Storage Insert Error: " . $e->getErrorText());
        header("Location: index.php?status=error");
        exit;
    }
} else {
    header("Location: index.php");
    exit;
}
?>
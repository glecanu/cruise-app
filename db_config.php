<?php

// Start session for admin pages
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . '/vendor/autoload.php'; // For Azure SDK

use MicrosoftAzure\Storage\Table\TableRestProxy;
use MicrosoftAzure\Storage\Common\Exceptions\ServiceException;
use MicrosoftAzure\Storage\Table\Models\Entity;
use MicrosoftAzure\Storage\Table\Models\EdmType;
use MicrosoftAzure\Storage\Table\Models\QueryEntitiesOptions;
use Ramsey\Uuid\Uuid;

// --- Azure Table Storage Configuration ---
$storageAccountName = getenv('AZURE_STORAGE_ACCOUNT_NAME');
$storageAccountKey = getenv('AZURE_STORAGE_ACCOUNT_KEY');
$storageTableName = getenv('AZURE_STORAGE_TABLE_NAME') ?: 'submissions'; // Default if not set

// Alternative: Use full connection string
$connectionString = getenv('AZURE_STORAGE_CONNECTION_STRING');

$tableClient = null;

if ($connectionString) {
    try {
        $tableClient = TableRestProxy::createTableService($connectionString);
    } catch (InvalidArgumentException $e) {
        die("Azure Table Storage Error: Invalid connection string format. " . $e->getMessage());
    } catch (Exception $e) {
        die("Azure Table Storage Error with Connection String: " . $e->getMessage());
    }
} elseif ($storageAccountName && $storageAccountKey) {
    $connectionStringInternal = "DefaultEndpointsProtocol=https;AccountName={$storageAccountName};AccountKey={$storageAccountKey}";
    try {
        $tableClient = TableRestProxy::createTableService($connectionStringInternal);
    } catch (InvalidArgumentException $e) {
        die("Azure Table Storage Error: Invalid account name/key format. " . $e->getMessage());
    } catch (Exception $e) {
        die("Azure Table Storage Error with Account Name/Key: " . $e->getMessage());
    }
} else {
    die("Azure Table Storage configuration (Connection String or Account Name/Key) not found in App Settings.");
}

// --- Admin Configuration (remains the same) ---
define('ADMIN_USERNAME', getenv('ADMIN_USER') ?: 'admin');
define('ADMIN_PASSWORD_HASH', getenv('ADMIN_PASS_HASH') ?: '$2y$10$QjA7M2cXR0B6I8S.HSAdi.0GvjHWYWGq0hOqHdp8uXl.J.J9uC/aa'); // admin123

function isAdminLoggedIn() {
    return isset($_SESSION['admin_logged_in']) && $_SESSION['admin_logged_in'] === true;
}

function ensureAdminLoggedIn() {
    if (!isAdminLoggedIn()) {
        header('Location: admin_login.php?redirect=' . urlencode($_SERVER['REQUEST_URI']));
        exit;
    }
}

// Helper function for Table Storage entities (optional but can be useful)
function generateRowKey() {
    // Table storage RowKeys are strings. Using a time-based, reversible key can be useful for sorting if needed,
    // or just a UUID for uniqueness. For simplicity, a timestamp + random might work.
    // Max(timestamp) - current_timestamp for reverse chronological order by default (lexicographical sort)
    // Or simply a GUID:
    return Ramsey\Uuid\Uuid::uuid4()->toString();
}
?>
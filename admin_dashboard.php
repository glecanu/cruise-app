<?php
// 1. Include configuration and ensure admin is logged in
require_once 'db_config.php'; // This should define $tableClient, $storageTableName, and session functions
ensureAdminLoggedIn();      // This function redirects if not logged in

// It's good practice to also have use statements here if not in db_config.php,
// though if db_config.php defines $tableClient correctly, we might only need QueryEntitiesOptions here.
// use MicrosoftAzure\Storage\Table\Models\QueryEntitiesOptions;
// use MicrosoftAzure\Storage\Common\Exceptions\ServiceException;
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard - Manage Submissions</title>
    <link rel="stylesheet" href="style.css">
    <script>
        function confirmDelete(rowKey) { // Changed parameter to rowKey for clarity
            if (confirm("Are you sure you want to delete submission with RowKey: " + rowKey + "?")) {
                // Assuming your delete form ID is 'delete-form-' + rowKey
                document.getElementById('delete-form-' + rowKey).submit();
            }
        }
    </script>
</head>
<body>
    <div class="container">
        <h1>Admin Dashboard - Manage Submissions</h1>

        <nav>
            <a href="index.php">View Site</a> |
            Logged in as: <strong><?php echo htmlspecialchars($_SESSION['admin_username'] ?? 'Admin'); ?></strong> |
            <a href="logout.php">Logout</a>
        </nav>

        <?php // Display status messages from redirects (e.g., after update/delete)
        if (isset($_GET['status_message'])): // Using 'status_message' for clarity
            $message_type = htmlspecialchars($_GET['message_type'] ?? 'success'); // default to success
        ?>
            <div class="message <?php echo $message_type; ?>">
                <?php echo htmlspecialchars($_GET['status_message']); ?>
            </div>
        <?php endif; ?>

        <h2>Submissions List</h2>

        <?php
        try {
            $filter = "PartitionKey eq 'submission'"; // Assuming 'submission' is your PartitionKey
            $options = new \MicrosoftAzure\Storage\Table\Models\QueryEntitiesOptions();
            // You can add $options->addSelectField('PropertyName') if you only need specific columns

            $result = $tableClient->queryEntities($storageTableName, $filter, $options);
            $submissions = $result->getEntities();

            // Sort by SubmissionTime client-side (descending) as Table Storage doesn't sort arbitrarily
            if (!empty($submissions)) {
                usort($submissions, function ($a, $b) {
                    $timeA = $a->getProperty('SubmissionTime')->getValue(); // Property value is DateTime object
                    $timeB = $b->getProperty('SubmissionTime')->getValue();
                    if ($timeA == $timeB) {
                        return 0;
                    }
                    return ($timeA < $timeB) ? 1 : -1; // For descending order
                });
            }

            if (count($submissions) > 0) {
                echo "<table>";
                echo "<thead><tr><th>RowKey</th><th>First Name</th><th>Home City</th><th>Home Country</th><th>Submission Time</th><th>Actions</th></tr></thead>";
                echo "<tbody>";
                foreach ($submissions as $entity) {
                    $partitionKey = htmlspecialchars($entity->getPartitionKey());
                    $rowKey = htmlspecialchars($entity->getRowKey());
                    $firstName = htmlspecialchars($entity->getPropertyValue('FirstName'));
                    $homeCity = htmlspecialchars($entity->getPropertyValue('HomeCity'));
                    $homeCountry = htmlspecialchars($entity->getPropertyValue('HomeCountry'));
                    $submissionTimeObj = $entity->getPropertyValue('SubmissionTime');
                    $submissionTimeFormatted = ($submissionTimeObj instanceof DateTime) ? $submissionTimeObj->format('Y-m-d H:i:s T') : 'N/A';

                    echo "<tr>";
                    echo "<td>" . $rowKey . "</td>";
                    echo "<td>" . $firstName . "</td>";
                    echo "<td>" . $homeCity . "</td>";
                    echo "<td>" . $homeCountry . "</td>";
                    echo "<td>" . $submissionTimeFormatted . "</td>";
                    echo "<td class='actions'>";
                    echo "<a href='edit_submission.php?partitionKey=" . urlencode($partitionKey) . "&rowKey=" . urlencode($rowKey) . "'>Edit</a> ";
                    echo "<form id='delete-form-" . $rowKey . "' action='delete_submission_handler.php' method='POST' style='display:inline;'>";
                    echo "<input type='hidden' name='partitionKey' value='" . $partitionKey . "'>";
                    echo "<input type='hidden' name='rowKey' value='" . $rowKey . "'>";
                    echo "<button type='button' class='delete-btn' onclick='confirmDelete(\"" . $rowKey . "\")'>Delete</button>";
                    echo "</form>";
                    echo "</td>";
                    echo "</tr>";
                }
                echo "</tbody></table>";
            } else {
                echo "<p>No submissions have been made yet.</p>";
            }
        } catch (\MicrosoftAzure\Storage\Common\Exceptions\ServiceException $e) {
            // Display a user-friendly error and log the detailed one
            echo "<p class='error'>Error retrieving submissions: Could not connect to data store or query failed.</p>";
            error_log("Azure Table Storage Admin Dashboard - ServiceException: " . $e->getErrorText() . " (Code: " . $e->getCode() . ")");
        } catch (Exception $e) {
            // Catch any other general exceptions
            echo "<p class='error'>An unexpected error occurred while fetching submissions.</p>";
            error_log("Azure Table Storage Admin Dashboard - General Exception: " . $e->getMessage());
        }
        ?>
    </div>
</body>
</html>
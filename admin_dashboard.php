<?php
require_once 'db_config.php';
ensureAdminLoggedIn();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard - Manage Submissions</title>
    <link rel="stylesheet" href="style.css">
    <script>
        function confirmDelete(rowKey) {
            if (confirm("Are you sure you want to delete submission with RowKey: " + rowKey + "?")) {
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

        <?php if (isset($_GET['status_message'])):
            $message_type = htmlspecialchars($_GET['message_type'] ?? 'success');
        ?>
            <div class="message <?php echo $message_type; ?>">
                <?php echo htmlspecialchars($_GET['status_message']); ?>
            </div>
        <?php endif; ?>

        <h2>Submissions List</h2>

        <?php
        try {
            $filter = "PartitionKey eq 'submission'";
            $options = new \MicrosoftAzure\Storage\Table\Models\QueryEntitiesOptions();
            $result = $tableClient->queryEntities($storageTableName, $filter, $options);
            $submissions = $result->getEntities();

            if (!empty($submissions)) {
                usort($submissions, function ($a, $b) {
                    $timeA = $a->getProperty('SubmissionTime')->getValue();
                    $timeB = $b->getProperty('SubmissionTime')->getValue();
                    return ($timeA < $timeB) ? 1 : -1;
                });
            }

            if (count($submissions) > 0) {
                echo "<table>";
                echo "<thead><tr><th>RowKey</th><th>Duck Number</th><th>First Name</th><th>Home City</th><th>Home Country</th><th>Time</th><th>Actions</th></tr></thead>";
                echo "<tbody>";
                foreach ($submissions as $entity) {
                    $partitionKey = htmlspecialchars($entity->getPartitionKey());
                    $rowKey = htmlspecialchars($entity->getRowKey());
                    $duckNumber = htmlspecialchars($entity->getPropertyValue('DuckNumber'));
                    $firstName = htmlspecialchars($entity->getPropertyValue('FirstName'));
                    $homeCity = htmlspecialchars($entity->getPropertyValue('HomeCity'));
                    $homeCountry = htmlspecialchars($entity->getPropertyValue('HomeCountry'));
                    $submissionTimeObj = $entity->getPropertyValue('SubmissionTime');
                    $submissionTimeFormatted = ($submissionTimeObj instanceof DateTime) ? $submissionTimeObj->format('Y-m-d H:i:s T') : 'N/A';

                    echo "<tr>";
                    echo "<td>" . $rowKey . "</td>";
                    echo "<td>" . $duckNumber . "</td>";
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
            echo "<p class='error'>Error retrieving submissions: " . htmlspecialchars($e->getErrorText()) . "</p>";
            error_log("Azure Table Storage Admin Dashboard - ServiceException: " . $e->getErrorText());
        } catch (Exception $e) {
            echo "<p class='error'>An unexpected error occurred.</p>";
            error_log("Azure Table Storage Admin Dashboard - General Exception: " . $e->getMessage());
        }
        ?>
    </div>
</body>
</html>
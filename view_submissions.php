<?php require_once 'db_config.php'; // Includes $tableClient, $storageTableName ?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>View Submissions</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <div class="container">
        <h1>All Submissions</h1>
        <nav>
            <a href="index.php">Home</a> |
            <a href="view_submissions.php">View Submissions</a> |
            <a href="admin_login.php">Admin Login</a>
        </nav>

        <?php
        try {
            $filter = "PartitionKey eq 'submission'"; // Querying our single partition
            $options = new \MicrosoftAzure\Storage\Table\Models\QueryEntitiesOptions();
            // $options->addSelectField('FirstName'); // Example: select specific fields

            $result = $tableClient->queryEntities($storageTableName, $filter, $options);
            $submissions = $result->getEntities();

            // Sort by SubmissionTime client-side (descending)
            if (!empty($submissions)) {
                usort($submissions, function ($a, $b) {
                    $timeA = $a->getProperty('SubmissionTime')->getValue();
                    $timeB = $b->getProperty('SubmissionTime')->getValue();
                    if ($timeA == $timeB) return 0;
                    return ($timeA < $timeB) ? 1 : -1; // For descending
                });
            }

            if (count($submissions) > 0) {
                echo "<table>";
                echo "<thead><tr><th>RowKey (ID)</th><th>First Name</th><th>Home City</th><th>Home Country</th><th>Time</th></tr></thead>";
                echo "<tbody>";
                foreach ($submissions as $entity) {
                    echo "<tr>";
                    echo "<td>" . htmlspecialchars($entity->getRowKey()) . "</td>";
                    echo "<td>" . htmlspecialchars($entity->getPropertyValue('FirstName')) . "</td>";
                    echo "<td>" . htmlspecialchars($entity->getPropertyValue('HomeCity')) . "</td>";
                    echo "<td>" . htmlspecialchars($entity->getPropertyValue('HomeCountry')) . "</td>";
                    $submissionTime = $entity->getPropertyValue('SubmissionTime'); // This is a DateTime object
                    echo "<td>" . htmlspecialchars($submissionTime instanceof DateTime ? $submissionTime->format('Y-m-d H:i:s T') : 'N/A') . "</td>";
                    echo "</tr>";
                }
                echo "</tbody></table>";
            } else {
                echo "<p>No submissions yet.</p>";
            }
        } catch (\MicrosoftAzure\Storage\Common\Exceptions\ServiceException $e) {
            echo "<p class='error'>Could not retrieve submissions: " . htmlspecialchars($e->getErrorText()) . "</p>";
            error_log("Azure Table Storage Query Error: " . $e->getErrorText() . " (Code: " . $e->getCode() . ")");
        } catch (Exception $e) {
            echo "<p class='error'>An unexpected error occurred: " . htmlspecialchars($e->getMessage()) . "</p>";
            error_log("General Query Error: " . $e->getMessage());
        }
        ?>
    </div>
</body>
</html>
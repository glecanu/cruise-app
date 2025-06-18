<?php require_once 'db_config.php'; // Includes $tableClient ?>
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
            // Simple query for all entities in the 'submission' partition.
            // For better performance on large tables, use filters.
            // Table storage doesn't have an easy "ORDER BY" like SQL.
            // You'd typically sort client-side or design RowKeys for desired order.
            $filter = "PartitionKey eq 'submission'";
            $options = new QueryEntitiesOptions();
            // $options->addSelectField('FirstName'); // Select specific fields if needed

            $result = $tableClient->queryEntities($storageTableName, $filter, $options);
            $submissions = $result->getEntities();

            // Sort by SubmissionTime client-side (descending)
            usort($submissions, function ($a, $b) {
                $timeA = $a->getPropertyValue('SubmissionTime'); // Assuming it's a DateTime object
                $timeB = $b->getPropertyValue('SubmissionTime');
                if ($timeA == $timeB) return 0;
                return ($timeA < $timeB) ? 1 : -1; // For descending
            });


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
                    $submissionTime = $entity->getPropertyValue('SubmissionTime');
                    echo "<td>" . htmlspecialchars($submissionTime instanceof DateTime ? $submissionTime->format('Y-m-d H:i:s') : 'N/A') . "</td>";
                    echo "</tr>";
                }
                echo "</tbody></table>";
            } else {
                echo "<p>No submissions yet.</p>";
            }
        } catch (ServiceException $e) {
            echo "<p class='error'>Could not retrieve submissions: " . htmlspecialchars($e->getErrorText()) . "</p>";
        } catch (Exception $e) {
            echo "<p class='error'>An unexpected error occurred: " . htmlspecialchars($e->getMessage()) . "</p>";
        }
        ?>
    </div>
</body>
</html>
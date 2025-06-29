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
                echo "<thead><tr><th>Duck Number</th><th>First Name</th><th>Home City</th><th>Home Country</th><th>Time Found</th></tr></thead>";
                echo "<tbody>";
                foreach ($submissions as $entity) {
                    $duckNumber = htmlspecialchars($entity->getPropertyValue('DuckNumber'));
                    $firstName = htmlspecialchars($entity->getPropertyValue('FirstName'));
                    $homeCity = htmlspecialchars($entity->getPropertyValue('HomeCity'));
                    $homeCountry = htmlspecialchars($entity->getPropertyValue('HomeCountry'));
                    $submissionTime = $entity->getPropertyValue('SubmissionTime');

                    echo "<tr>";
                    echo "<td>" . $duckNumber . "</td>";
                    echo "<td>" . $firstName . "</td>";
                    echo "<td>" . $homeCity . "</td>";
                    echo "<td>" . $homeCountry . "</td>";
                    echo "<td>" . ($submissionTime instanceof DateTime ? $submissionTime->format('Y-m-d H:i:s T') : 'N/A') . "</td>";
                    echo "</tr>";
                }
                echo "</tbody></table>";
            } else {
                echo "<p>No submissions yet.</p>";
            }
        } catch (\MicrosoftAzure\Storage\Common\Exceptions\ServiceException $e) {
            echo "<p class='error'>Could not retrieve submissions: " . htmlspecialchars($e->getErrorText()) . "</p>";
        }
        ?>
    </div>
</body>
</html>
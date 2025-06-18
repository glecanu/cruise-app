<?php
// ... (require_once 'db_config.php'; ensureAdminLoggedIn(); etc. at the top) ...

// Inside the body, where you list submissions:
try {
    $filter = "PartitionKey eq 'submission'";
    $options = new \MicrosoftAzure\Storage\Table\Models\QueryEntitiesOptions();

    $result = $tableClient->queryEntities($storageTableName, $filter, $options);
    $submissions = $result->getEntities();

    // Sort client-side by SubmissionTime (descending)
    if (!empty($submissions)) {
        usort($submissions, function ($a, $b) {
            $timeA = $a->getProperty('SubmissionTime')->getValue();
            $timeB = $b->getProperty('SubmissionTime')->getValue();
            if ($timeA == $timeB) return 0;
            return ($timeA < $timeB) ? 1 : -1; // For descending
        });
    }


    if (isset($_GET['status'])): ?>
        <div class="message <?php echo htmlspecialchars($_GET['status_type'] ?? 'info'); ?>">
            <?php echo htmlspecialchars($_GET['status']); ?>
        </div>
    <?php endif;

    if (count($submissions) > 0) {
        echo "<table>";
        echo "<thead><tr><th>RowKey</th><th>Name</th><th>City</th><th>Country</th><th>Time</th><th>Actions</th></tr></thead>";
        echo "<tbody>";
        foreach ($submissions as $entity) {
            $partitionKey = $entity->getPartitionKey();
            $rowKey = $entity->getRowKey();
            echo "<tr>";
            echo "<td>" . htmlspecialchars($rowKey) . "</td>";
            echo "<td>" . htmlspecialchars($entity->getPropertyValue('FirstName')) . "</td>";
            echo "<td>" . htmlspecialchars($entity->getPropertyValue('HomeCity')) . "</td>";
            echo "<td>" . htmlspecialchars($entity->getPropertyValue('HomeCountry')) . "</td>";
            $submissionTime = $entity->getPropertyValue('SubmissionTime');
            echo "<td>" . htmlspecialchars($submissionTime instanceof DateTime ? $submissionTime->format('Y-m-d H:i:s T') : 'N/A') . "</td>";
            echo "<td class='actions'>";
            echo "<a href='edit_submission.php?partitionKey=" . urlencode($partitionKey) . "&rowKey=" . urlencode($rowKey) . "'>Edit</a> ";
            echo "<form id='delete-form-" . htmlspecialchars($rowKey) . "' action='delete_submission_handler.php' method='POST' style='display:inline;'>";
            echo "<input type='hidden' name='partitionKey' value='" . htmlspecialchars($partitionKey) . "'>";
            echo "<input type='hidden' name='rowKey' value='" . htmlspecialchars($rowKey) . "'>";
            echo "<button type='button' class='delete-btn' onclick='confirmDelete(\"" . htmlspecialchars($rowKey) . "\")'>Delete</button>";
            echo "</form>";
            echo "</td>";
            echo "</tr>";
        }
        echo "</tbody></table>";
    } else {
        echo "<p>No submissions yet.</p>";
    }
} catch (\MicrosoftAzure\Storage\Common\Exceptions\ServiceException $e) {
    echo "<p class='error'>Could not retrieve submissions for admin: " . htmlspecialchars($e->getErrorText()) . "</p>";
    error_log("Azure Table Storage Admin Query Error: " . $e->getErrorText() . " (Code: " . $e->getCode() . ")");
} catch (Exception $e) {
    echo "<p class='error'>An unexpected error occurred in admin dashboard: " . htmlspecialchars($e->getMessage()) . "</p>";
    error_log("General Admin Query Error: " . $e->getMessage());
}
?>
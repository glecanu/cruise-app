<?php
require_once 'db_config.php';
ensureAdminLoggedIn(); // Make sure admin is logged in
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Admin Dashboard</title>
    <link rel="stylesheet" href="style.css">
    <script>
        function confirmDelete(id) {
            if (confirm("Are you sure you want to delete submission #" + id + "?")) {
                document.getElementById('delete-form-' + id).submit();
            }
        }
    </script>
</head>
<body>
    <div class="container">
        <h1>Admin Dashboard - Manage Submissions</h1>
        <nav>
            <a href="index.php">View Site</a> |
            Logged in as <?php echo htmlspecialchars($_SESSION['admin_username']); ?> |
            <a href="logout.php">Logout</a>
        </nav>

        <?php if (isset($_GET['status'])): ?>
            <div class="message <?php echo htmlspecialchars($_GET['status_type'] ?? 'info'); ?>">
                <?php echo htmlspecialchars($_GET['status']); ?>
            </div>
        <?php endif; ?>

        <?php
        try {
            $stmt = $pdo->query("SELECT id, firstName, homeCity, homeCountry, submissionTime FROM submissions ORDER BY submissionTime DESC");
            $submissions = $stmt->fetchAll();

            if (count($submissions) > 0) {
                echo "<table>";
                echo "<thead><tr><th>ID</th><th>Name</th><th>City</th><th>Country</th><th>Time</th><th>Actions</th></tr></thead>";
                echo "<tbody>";
                foreach ($submissions as $row) {
                    echo "<tr>";
                    echo "<td>" . htmlspecialchars($row['id']) . "</td>";
                    echo "<td>" . htmlspecialchars($row['firstName']) . "</td>";
                    echo "<td>" . htmlspecialchars($row['homeCity']) . "</td>";
                    echo "<td>" . htmlspecialchars($row['homeCountry']) . "</td>";
                    echo "<td>" . htmlspecialchars($row['submissionTime']) . "</td>";
                    echo "<td class='actions'>";
                    echo "<a href='edit_submission.php?id=" . htmlspecialchars($row['id']) . "'>Edit</a> ";
                    // Delete form
                    echo "<form id='delete-form-" . htmlspecialchars($row['id']) . "' action='delete_submission_handler.php' method='POST' style='display:inline;'>";
                    echo "<input type='hidden' name='id' value='" . htmlspecialchars($row['id']) . "'>";
                    echo "<button type='button' class='delete-btn' onclick='confirmDelete(" . htmlspecialchars($row['id']) . ")'>Delete</button>";
                    echo "</form>";
                    echo "</td>";
                    echo "</tr>";
                }
                echo "</tbody></table>";
            } else {
                echo "<p>No submissions yet.</p>";
            }
        } catch (PDOException $e) {
            echo "<p class='error'>Could not retrieve submissions: " . $e->getMessage() . "</p>";
        }
        ?>
    </div>
</body>
</html>
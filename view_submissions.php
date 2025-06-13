<?php require_once 'db_config.php'; ?>
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
            $stmt = $pdo->query("SELECT id, firstName, homeCity, homeCountry, submissionTime FROM submissions ORDER BY submissionTime DESC");
            $submissions = $stmt->fetchAll();

            if (count($submissions) > 0) {
                echo "<table>";
                echo "<thead><tr><th>ID</th><th>First Name</th><th>Home City</th><th>Home Country</th><th>Time</th></tr></thead>";
                echo "<tbody>";
                foreach ($submissions as $row) {
                    echo "<tr>";
                    echo "<td>" . htmlspecialchars($row['id']) . "</td>";
                    echo "<td>" . htmlspecialchars($row['firstName']) . "</td>";
                    echo "<td>" . htmlspecialchars($row['homeCity']) . "</td>";
                    echo "<td>" . htmlspecialchars($row['homeCountry']) . "</td>";
                    echo "<td>" . htmlspecialchars($row['submissionTime']) . "</td>";
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
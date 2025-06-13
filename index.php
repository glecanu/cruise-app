<?php require_once 'db_config.php'; ?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Submission Form</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <div class="container">
        <h1>Submit Your Information</h1>
        <nav>
            <a href="index.php">Home</a> |
            <a href="view_submissions.php">View Submissions</a> |
            <a href="admin_login.php">Admin Login</a>
        </nav>

        <?php if (isset($_GET['status'])): ?>
            <div class="message <?php echo htmlspecialchars($_GET['status']); ?>">
                <?php
                    if ($_GET['status'] === 'success') echo "Submission successful!";
                    elseif ($_GET['status'] === 'error') echo "There was an error with your submission.";
                    elseif ($_GET['status'] === 'invalid') echo "Please fill all fields.";
                ?>
            </div>
        <?php endif; ?>

        <form action="submit_handler.php" method="POST">
            <div>
                <label for="firstName">First Name:</label>
                <input type="text" id="firstName" name="firstName" required>
            </div>
            <div>
                <label for="homeCity">Home City:</label>
                <input type="text" id="homeCity" name="homeCity" required>
            </div>
            <div>
                <label for="homeCountry">Home Country:</label>
                <input type="text" id="homeCountry" name="homeCountry" required>
            </div>
            <div>
                <button type="submit">Submit</button>
            </div>
        </form>
    </div>
</body>
</html>
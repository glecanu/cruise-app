<?php require_once 'db_config.php'; ?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>British Isles 2025 - Cruising Ducks</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <div class="container">
        <h1>British Isles 2025 - Cruising Ducks</h1>
        
        <img src="All_30_ducks.jpg" alt="A collection of 30 cruising ducks in front of a Scotland travel guide" class="banner-image">

        <div class="intro-text">
            <p><strong>Thank you, fellow passenger!</strong> You've found one of our cruising ducks!</p>
            <p>We'd be thrilled if you could enter your information in the form below, including the number from the bottom of the duck. Afterwards, feel free to hide it again for another passenger to find and continue the journey!</p>
            <p>
                Want to see all the ducks and their numbers? 
                <a href="https://photos.app.goo.gl/12NYYifC5tbrjwVp9" target="_blank" rel="noopener noreferrer">Visit our Google Photos Album</a>
                and feel free to comment on the photo of the duck you found!
            </p>
        </div>

        <nav>
            <a href="index.php">Home</a> |
            <a href="view_submissions.php">View Submissions</a> |
            <a href="admin_login.php">Admin Login</a>
        </nav>

        <?php if (isset($_GET['status'])): ?>
            <div class="message <?php echo htmlspecialchars($_GET['status']); ?>">
                <?php
                    if ($_GET['status'] === 'success') echo "Thank you! Your submission has been recorded.";
                    elseif ($_GET['status'] === 'error') echo "There was an error with your submission.";
                    // Removed 'invalid' check as fields are now optional
                ?>
            </div>
        <?php endif; ?>

        <form action="submit_handler.php" method="POST">
            <div>
                <label for="duckNumber">Duck Number (from the bottom of the duck):</label>
                <input type="text" id="duckNumber" name="duckNumber">
            </div>
            <div>
                <label for="firstName">Your First Name (or Family Name):</label>
                <input type="text" id="firstName" name="firstName">
            </div>
            <div>
                <label for="homeCity">Your Home City:</label>
                <input type="text" id="homeCity" name="homeCity">
            </div>
            <div>
                <label for="homeCountry">Your Home Country:</label>
                <input type="text" id="homeCountry" name="homeCountry">
            </div>
            <div>
                <button type="submit">Submit</button>
            </div>
        </form>
    </div>
</body>
</html>
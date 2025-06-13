<?php
require_once 'db_config.php'; // This includes PDO connection $pdo

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $firstName = trim($_POST['firstName'] ?? '');
    $homeCity = trim($_POST['homeCity'] ?? '');
    $homeCountry = trim($_POST['homeCountry'] ?? '');

    if (empty($firstName) || empty($homeCity) || empty($homeCountry)) {
        header("Location: index.php?status=invalid");
        exit;
    }

    try {
        $stmt = $pdo->prepare("INSERT INTO submissions (firstName, homeCity, homeCountry) VALUES (?, ?, ?)");
        $stmt->execute([$firstName, $homeCity, $homeCountry]);
        header("Location: index.php?status=success");
        exit;
    } catch (PDOException $e) {
        // Log error $e->getMessage()
        header("Location: index.php?status=error");
        exit;
    }
} else {
    header("Location: index.php"); // Redirect if not POST
    exit;
}
?>
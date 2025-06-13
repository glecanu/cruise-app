<?php
require_once 'db_config.php';
ensureAdminLoggedIn();

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $id = $_POST['id'] ?? null;
    $firstName = trim($_POST['firstName'] ?? '');
    $homeCity = trim($_POST['homeCity'] ?? '');
    $homeCountry = trim($_POST['homeCountry'] ?? '');

    if (!$id || empty($firstName) || empty($homeCity) || empty($homeCountry)) {
        // Ideally redirect back to edit form with error
        header("Location: admin_dashboard.php?status=Invalid data for update&status_type=error");
        exit;
    }

    try {
        $stmt = $pdo->prepare("UPDATE submissions SET firstName = ?, homeCity = ?, homeCountry = ? WHERE id = ?");
        $stmt->execute([$firstName, $homeCity, $homeCountry, $id]);
        header("Location: admin_dashboard.php?status=Submission updated successfully&status_type=success");
        exit;
    } catch (PDOException $e) {
        // Log error $e->getMessage()
        header("Location: admin_dashboard.php?status=Error updating submission&status_type=error");
        exit;
    }
} else {
    header("Location: admin_dashboard.php");
    exit;
}
?>
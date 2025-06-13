<?php
require_once 'db_config.php';
ensureAdminLoggedIn();

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $id = $_POST['id'] ?? null;

    if (!$id) {
        header("Location: admin_dashboard.php?status=Invalid ID for deletion&status_type=error");
        exit;
    }

    try {
        $stmt = $pdo->prepare("DELETE FROM submissions WHERE id = ?");
        $stmt->execute([$id]);
        header("Location: admin_dashboard.php?status=Submission deleted successfully&status_type=success");
        exit;
    } catch (PDOException $e) {
        // Log error $e->getMessage()
        header("Location: admin_dashboard.php?status=Error deleting submission&status_type=error");
        exit;
    }
} else {
    header("Location: admin_dashboard.php");
    exit;
}
?>
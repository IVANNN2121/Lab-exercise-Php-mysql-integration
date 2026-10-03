<?php
require_once 'config.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id = filter_var($_POST['id'] ?? null, FILTER_VALIDATE_INT);

    if ($id) {
        try {
            $stmt = $conn->prepare("UPDATE books SET status = 'Available', borrower = NULL WHERE id = ?");
            $stmt->bind_param("i", $id);
            $stmt->execute();
            $stmt->close();
            flash("Book returned. It is now available.");
        } catch (mysqli_sql_exception $e) {
            flash("Could not mark the book as returned.", "danger");
        }
    }
}

header("Location: read.php");
exit;
?>

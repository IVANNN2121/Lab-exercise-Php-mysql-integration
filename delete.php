<?php
require_once 'config.php';


if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id = filter_var($_POST['id'] ?? null, FILTER_VALIDATE_INT);

    if ($id) {
        try {
            $stmt = $conn->prepare("DELETE FROM books WHERE id = ?");
            $stmt->bind_param("i", $id);
            $stmt->execute();
            $stmt->close();
            flash("Book deleted.");
        } catch (mysqli_sql_exception $e) {
            flash("Could not delete the book.", "danger");
        }
    }
}

header("Location: read.php");
exit;
?>

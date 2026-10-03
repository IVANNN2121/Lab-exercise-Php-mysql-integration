<?php
require_once 'config.php';

$id = filter_var($_GET['id'] ?? null, FILTER_VALIDATE_INT);
if (!$id) {
    die("Invalid or missing book ID.");
}


try {
    $stmt = $conn->prepare("SELECT title, author, status, borrower FROM books WHERE id = ?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $book = $stmt->get_result()->fetch_assoc();
    $stmt->close();

    if (!$book) {
        die("Book not found.");
    }
} catch (mysqli_sql_exception $e) {
    die("Database error occurred.");
}

$error = '';


if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $title    = trim($_POST['title'] ?? '');
    $author   = trim($_POST['author'] ?? '');
    $status   = $_POST['status'] ?? 'Available';
    $borrower = trim($_POST['borrower'] ?? '');


    $book = ['title' => $title, 'author' => $author, 'status' => $status, 'borrower' => $borrower];

    if ($title === '' || $author === '' || !in_array($status, ['Available', 'Borrowed'], true)) {
        $error = "Please provide a valid title, author, and status.";
    } elseif ($status === 'Borrowed' && $borrower === '') {
        $error = "Please enter the borrower's name for a borrowed book.";
    } else {
        $borrower_value = ($status === 'Borrowed') ? $borrower : null;

        try {
            $stmt = $conn->prepare("UPDATE books SET title = ?, author = ?, status = ?, borrower = ? WHERE id = ?");
            $stmt->bind_param("ssssi", $title, $author, $status, $borrower_value, $id);
            $stmt->execute();
            $stmt->close();

            flash("Book updated successfully!");
            header("Location: read.php");
            exit;
        } catch (mysqli_sql_exception $e) {
            if ($e->getCode() == 1062) {
                $error = "A book with that title already exists.";
            } else {
                $error = "Failed to update the book.";
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Book</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">

<nav class="navbar navbar-dark bg-primary mb-4">
    <div class="container">
        <a class="navbar-brand fw-bold" href="read.php">Library Langs</a>
    </div>
</nav>

<div class="container" style="max-width: 520px;">
    <div class="card shadow-sm">
        <div class="card-header bg-warning fw-bold">Edit Book #<?= (int)$id ?></div>
        <div class="card-body">
            <?php if ($error): ?>
                <div class="alert alert-danger"><?= htmlspecialchars($error) ?></div>
            <?php endif; ?>

            <form method="POST">
                <div class="mb-3">
                    <label class="form-label">Book Title</label>
                    <input type="text" name="title" class="form-control" value="<?= htmlspecialchars($book['title']) ?>" required>
                </div>
                <div class="mb-3">
                    <label class="form-label">Author</label>
                    <input type="text" name="author" class="form-control" value="<?= htmlspecialchars($book['author']) ?>" required>
                </div>
                <div class="mb-3">
                    <label class="form-label">Status</label>
                    <select name="status" id="status" class="form-select">
                        <option value="Available" <?= $book['status'] === 'Available' ? 'selected' : '' ?>>Available</option>
                        <option value="Borrowed" <?= $book['status'] === 'Borrowed' ? 'selected' : '' ?>>Borrowed</option>
                    </select>
                </div>
                <div class="mb-3" id="borrower-group">
                    <label class="form-label">Borrower Name</label>
                    <input type="text" name="borrower" class="form-control" value="<?= htmlspecialchars((string)$book['borrower']) ?>">
                </div>
                <button type="submit" class="btn btn-warning">Update Book</button>
                <a href="read.php" class="btn btn-secondary">Cancel</a>
            </form>
        </div>
    </div>
</div>

<script>
    const status = document.getElementById('status');
    const group = document.getElementById('borrower-group');
    function toggleBorrower() {
        group.style.display = status.value === 'Borrowed' ? 'block' : 'none';
    }
    status.addEventListener('change', toggleBorrower);
    toggleBorrower();
</script>
</body>
</html>

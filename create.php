<?php
require_once 'config.php';

$error = '';
$title = $author = $borrower = '';
$status = 'Available';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $title    = trim($_POST['title'] ?? '');
    $author   = trim($_POST['author'] ?? '');
    $status   = $_POST['status'] ?? 'Available';
    $borrower = trim($_POST['borrower'] ?? '');

    if ($title === '' || $author === '' || !in_array($status, ['Available', 'Borrowed'], true)) {
        $error = "Please provide a valid title, author, and status.";
    } elseif ($status === 'Borrowed' && $borrower === '') {
        $error = "Please enter the borrower's name for a borrowed book.";
    } else {

        $borrower_value = ($status === 'Borrowed') ? $borrower : null;

        try {
            $stmt = $conn->prepare("INSERT INTO books (title, author, status, borrower) VALUES (?, ?, ?, ?)");
            $stmt->bind_param("ssss", $title, $author, $status, $borrower_value);
            $stmt->execute();
            $stmt->close();

            flash("Book added successfully!");
            header("Location: read.php");
            exit;
        } catch (mysqli_sql_exception $e) {
            if ($e->getCode() == 1062) {
                $error = "A book with that title already exists.";
            } else {
                $error = "An error occurred while saving the book.";
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
    <title>Add Book</title>
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
        <div class="card-header bg-success text-white fw-bold">Add New Book</div>
        <div class="card-body">
            <?php if ($error): ?>
                <div class="alert alert-danger"><?= htmlspecialchars($error) ?></div>
            <?php endif; ?>

            <form method="POST">
                <div class="mb-3">
                    <label class="form-label">Book Title</label>
                    <input type="text" name="title" class="form-control" value="<?= htmlspecialchars($title) ?>" required>
                </div>
                <div class="mb-3">
                    <label class="form-label">Author</label>
                    <input type="text" name="author" class="form-control" value="<?= htmlspecialchars($author) ?>" required>
                </div>
                <div class="mb-3">
                    <label class="form-label">Status</label>
                    <select name="status" id="status" class="form-select">
                        <option value="Available" <?= $status === 'Available' ? 'selected' : '' ?>>Available</option>
                        <option value="Borrowed" <?= $status === 'Borrowed' ? 'selected' : '' ?>>Borrowed</option>
                    </select>
                </div>
                <div class="mb-3" id="borrower-group">
                    <label class="form-label">Borrower Name</label>
                    <input type="text" name="borrower" id="borrower" class="form-control" value="<?= htmlspecialchars($borrower) ?>">
                </div>
                <button type="submit" class="btn btn-success">Save Book</button>
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

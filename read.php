<?php
require_once 'config.php';

$search = trim($_GET['search'] ?? '');

try {
    if ($search !== '') {
        $like = "%" . $search . "%";
        $stmt = $conn->prepare("SELECT id, title, author, status, borrower FROM books WHERE title LIKE ? OR author LIKE ? OR borrower LIKE ? ORDER BY id DESC");
        $stmt->bind_param("sss", $like, $like, $like);
        $stmt->execute();
        $books = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
        $stmt->close();
    } else {
        $result = $conn->query("SELECT id, title, author, status, borrower FROM books ORDER BY id DESC");
        $books = $result->fetch_all(MYSQLI_ASSOC);
        $result->free();
    }
} catch (mysqli_sql_exception $e) {
    die("Could not retrieve books from the database.");
}

$total = count($books);
$borrowed = 0;
foreach ($books as $b) {
    if ($b['status'] === 'Borrowed') $borrowed++;
}
$available = $total - $borrowed;
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Library Lang</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">

<nav class="navbar navbar-dark bg-dark mb-4">
    <div class="container">
        <a class="navbar-brand fw-bold" href="read.php">Library Langs</a>
        <a href="create.php" class="btn btn-success">+ Add Book</a>
    </div>
</nav>

<div class="container">
    <?php show_flash(); ?>

    <div class="row g-3 mb-4">
        <div class="col-12 col-md-4">
            <div class="card text-white bg-primary text-center"><div class="card-body">
                <div class="small">Total Books</div><div class="fs-3 fw-bold"><?= $total ?></div>
            </div></div>
        </div>
        <div class="col-6 col-md-4">
            <div class="card text-white bg-success text-center"><div class="card-body">
                <div class="small">Available</div><div class="fs-3 fw-bold"><?= $available ?></div>
            </div></div>
        </div>
        <div class="col-6 col-md-4">
            <div class="card text-dark bg-warning text-center"><div class="card-body">
                <div class="small">Borrowed</div><div class="fs-3 fw-bold"><?= $borrowed ?></div>
            </div></div>
        </div>
    </div>

    <form class="row g-2 mb-3" method="GET">
        <div class="col-sm-8 col-md-6">
            <input type="text" name="search" class="form-control" placeholder="Search by title, author, or borrower" value="<?= htmlspecialchars($search) ?>">
        </div>
        <div class="col-auto">
            <button class="btn btn-dark" type="submit">Search</button>
            <?php if ($search !== ''): ?><a href="read.php" class="btn btn-secondary">Clear</a><?php endif; ?>
        </div>
    </form>

    <div class="card shadow-sm">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-dark">
                    <tr>
                        <th>ID</th><th>Title</th><th>Author</th><th>Status</th><th>Borrower</th><th class="text-end">Actions</th>
                    </tr>
                </thead>
                <tbody>
                <?php if (!empty($books)): ?>
                    <?php foreach ($books as $book): ?>
                        <tr>
                            <td><?= (int)$book['id'] ?></td>
                            <td><?= htmlspecialchars($book['title']) ?></td>
                            <td><?= htmlspecialchars($book['author']) ?></td>
                            <td>
                                <?php if ($book['status'] === 'Available'): ?>
                                    <span class="badge bg-success">Available</span>
                                <?php else: ?>
                                    <span class="badge bg-warning text-dark">Borrowed</span>
                                <?php endif; ?>
                            </td>
                            <td><?= $book['borrower'] ? htmlspecialchars($book['borrower']) : '<span class="text-muted">—</span>' ?></td>
                            <td class="text-end">
                                <?php if ($book['status'] === 'Borrowed'): ?>
                                    <form action="return.php" method="POST" class="d-inline" onsubmit="return confirm('Mark this book as returned?')">
                                        <input type="hidden" name="id" value="<?= (int)$book['id'] ?>">
                                        <button type="submit" class="btn btn-sm btn-info text-white">Return</button>
                                    </form>
                                <?php endif; ?>
                                <a href="update.php?id=<?= (int)$book['id'] ?>" class="btn btn-sm btn-secondary">Edit</a>
                                <form action="delete.php" method="POST" class="d-inline" onsubmit="return confirm('Delete this book permanently?')">
                                    <input type="hidden" name="id" value="<?= (int)$book['id'] ?>">
                                    <button type="submit" class="btn btn-sm btn-danger">Delete</button>
                                </form>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php else: ?>
                    <tr><td colspan="6" class="text-center text-muted py-4">No books found.</td></tr>
                <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>

<?php
include 'db.php';
include 'upload.php';

$error = "";

// ---------- CREATE ----------
if (isset($_POST['add'])) {
    $title    = mysqli_real_escape_string($conn, $_POST['title']);
    $date     = mysqli_real_escape_string($conn, $_POST['event_date']);
    $location = mysqli_real_escape_string($conn, $_POST['location']);
    $category = mysqli_real_escape_string($conn, $_POST['category']);

    list($poster, $error) = upload_poster($_FILES['poster'] ?? null);

    if (!$error) {
        $poster_sql = $poster ? "'$poster'" : "NULL";
        mysqli_query($conn, "INSERT INTO events (title, event_date, location, category, poster)
                             VALUES ('$title', '$date', '$location', '$category', $poster_sql)");
        header("Location: index.php?msg=Event added successfully");
        exit();
    }
}

// ---------- DELETE ----------
if (isset($_GET['delete'])) {
    $id  = (int)$_GET['delete'];
    $row = mysqli_fetch_assoc(mysqli_query($conn, "SELECT poster FROM events WHERE id = $id"));

    mysqli_query($conn, "DELETE FROM events WHERE id = $id");
    if ($row && $row['poster'] && file_exists("uploads/" . $row['poster'])) {
        unlink("uploads/" . $row['poster']);          // remove the image file too
    }
    header("Location: index.php?msg=Event deleted");
    exit();
}

// ---------- READ (search + category filter) ----------
$search = mysqli_real_escape_string($conn, $_GET['search'] ?? '');
$cat    = mysqli_real_escape_string($conn, $_GET['category'] ?? '');

$sql = "SELECT * FROM events WHERE (title LIKE '%$search%' OR location LIKE '%$search%')";
if ($cat != '') {
    $sql .= " AND category = '$cat'";
}
$sql .= " ORDER BY id DESC";
$result = mysqli_query($conn, $sql);
?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Event Management</title>
    <style>
        body  { font-family: Arial; margin: 20px; }
        table { border-collapse: collapse; width: 100%; margin-top: 10px; }
        th, td { border: 1px solid #ccc; padding: 8px; text-align: left; }
        img   { width: 70px; height: 90px; object-fit: cover; }
        .ok   { color: green; }
        .err  { color: red; }
    </style>
</head>
<body>
    <h2>Add Event</h2>

    <?php if (isset($_GET['msg'])) echo "<p class='ok'>" . htmlspecialchars($_GET['msg']) . "</p>"; ?>
    <?php if ($error) echo "<p class='err'>" . htmlspecialchars($error) . "</p>"; ?>

    <form method="POST" enctype="multipart/form-data">
        <input type="text" name="title" placeholder="Title" required>
        <input type="date" name="event_date" required>
        <input type="text" name="location" placeholder="Location" required>
        <select name="category" required>
            <option value="">Category</option>
            <option>Music</option>
            <option>Conference</option>
            <option>Workshop</option>
            <option>Sports</option>
        </select>
        <input type="file" name="poster">
        <button type="submit" name="add">Add Event</button>
    </form>

    <h2>Events</h2>
    <form method="GET">
        <input type="text" name="search" placeholder="Search title or location" value="<?php echo htmlspecialchars($search); ?>">
        <select name="category">
            <option value="">All categories</option>
            <?php foreach (['Music', 'Conference', 'Workshop', 'Sports'] as $c): ?>
                <option <?php if ($cat == $c) echo 'selected'; ?>><?php echo $c; ?></option>
            <?php endforeach; ?>
        </select>
        <button type="submit">Filter</button>
        <a href="index.php">Reset</a>
    </form>

    <table>
        <tr><th>ID</th><th>Poster</th><th>Title</th><th>Date</th><th>Location</th><th>Category</th><th>Action</th></tr>
        <?php while ($row = mysqli_fetch_assoc($result)): ?>
        <tr>
            <td><?php echo $row['id']; ?></td>
            <td><?php echo $row['poster'] ? "<img src='uploads/" . htmlspecialchars($row['poster']) . "'>" : "No poster"; ?></td>
            <td><?php echo htmlspecialchars($row['title']); ?></td>
            <td><?php echo $row['event_date']; ?></td>
            <td><?php echo htmlspecialchars($row['location']); ?></td>
            <td><?php echo htmlspecialchars($row['category']); ?></td>
            <td>
                <a href="edit.php?id=<?php echo $row['id']; ?>">Edit</a> |
                <a href="index.php?delete=<?php echo $row['id']; ?>" onclick="return confirm('Delete this event?');">Delete</a>
            </td>
        </tr>
        <?php endwhile; ?>
    </table>
</body>
</html>

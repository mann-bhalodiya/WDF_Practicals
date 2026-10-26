<?php
include 'db.php';
include 'upload.php';

$id    = (int)$_GET['id'];
$event = mysqli_fetch_assoc(mysqli_query($conn, "SELECT * FROM events WHERE id = $id"));
if (!$event) {
    die("Event not found.");
}

$error = "";

// ---------- UPDATE ----------
if (isset($_POST['update'])) {
    $title    = mysqli_real_escape_string($conn, $_POST['title']);
    $date     = mysqli_real_escape_string($conn, $_POST['event_date']);
    $location = mysqli_real_escape_string($conn, $_POST['location']);
    $category = mysqli_real_escape_string($conn, $_POST['category']);

    list($new_poster, $error) = upload_poster($_FILES['poster'] ?? null);

    if (!$error) {
        $poster     = $new_poster ? $new_poster : $event['poster'];   // keep old one if no new file
        $poster_sql = $poster ? "'$poster'" : "NULL";

        mysqli_query($conn, "UPDATE events SET title='$title', event_date='$date', location='$location',
                             category='$category', poster=$poster_sql WHERE id = $id");

        if ($new_poster && $event['poster'] && file_exists("uploads/" . $event['poster'])) {
            unlink("uploads/" . $event['poster']);                    // delete the OLD file after update
        }
        header("Location: index.php?msg=Event updated");
        exit();
    }
}
?>
<!DOCTYPE html>
<html>
<head><meta charset="UTF-8"><title>Edit Event</title></head>
<body style="font-family: Arial; margin: 20px;">
    <h2>Edit Event</h2>
    <?php if ($error) echo "<p style='color:red'>" . htmlspecialchars($error) . "</p>"; ?>

    <form method="POST" enctype="multipart/form-data">
        <p><input type="text" name="title" value="<?php echo htmlspecialchars($event['title']); ?>" required></p>
        <p><input type="date" name="event_date" value="<?php echo $event['event_date']; ?>" required></p>
        <p><input type="text" name="location" value="<?php echo htmlspecialchars($event['location']); ?>" required></p>
        <p>
            <select name="category">
                <?php foreach (['Music', 'Conference', 'Workshop', 'Sports'] as $c): ?>
                    <option <?php if ($event['category'] == $c) echo 'selected'; ?>><?php echo $c; ?></option>
                <?php endforeach; ?>
            </select>
        </p>
        <p>
            Current poster:<br>
            <?php echo $event['poster'] ? "<img src='uploads/" . htmlspecialchars($event['poster']) . "' width='100'>" : "None"; ?>
        </p>
        <p>Change poster (optional): <input type="file" name="poster"></p>
        <button type="submit" name="update">Update</button>
        <a href="index.php">Cancel</a>
    </form>
</body>
</html>

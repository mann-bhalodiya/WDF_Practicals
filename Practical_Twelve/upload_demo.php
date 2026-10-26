<?php
// STEP 1 DEMO: test the upload alone, before any database
include 'upload.php';

$msg = "";
if (isset($_POST['upload'])) {
    list($name, $error) = upload_poster($_FILES['poster'] ?? null);
    if ($error)     $msg = "Error: " . $error;
    elseif ($name)  $msg = "Saved as: " . $name;
    else            $msg = "No file chosen.";
}
?>
<h2>File Upload Test</h2>
<form method="POST" enctype="multipart/form-data">
    <input type="file" name="poster">
    <button type="submit" name="upload">Upload</button>
</form>
<p><?php echo htmlspecialchars($msg); ?></p>

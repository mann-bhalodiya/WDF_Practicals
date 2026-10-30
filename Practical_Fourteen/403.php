<?php
include_once "auth.php";
require_login();

http_response_code(403);

if ($_SESSION["role"] == "admin") {
    $home = "dashboard.php";
} else {
    $home = "student_home.php";
}

$page_title = "403 Forbidden";
include "header.php";
?>

<div class="container mt-5 text-center">
    <h1 class="display-1 text-danger">403</h1>
    <h4>Access Denied</h4>
    <p class="text-muted">You do not have permission to open this page. Only admin users can access it.</p>
    <a href="<?php echo $home; ?>" class="btn btn-primary">Go Back</a>
</div>

</body>
</html>

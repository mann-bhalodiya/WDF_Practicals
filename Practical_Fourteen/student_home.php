<?php
include_once "auth.php";
require_login();

$page_title = "Student Home";
include "header.php";
?>

<div class="container mt-4">
    <div class="card">
        <div class="card-body">
            <h4>Welcome, <?php echo htmlspecialchars($_SESSION["name"]); ?></h4>
            <p class="text-muted">You are logged in as a <?php echo $_SESSION["role"]; ?>.</p>
            <p>Admin pages are not available for your role.</p>
            <a href="dashboard.php" class="btn btn-outline-danger btn-sm">Try opening Admin Dashboard</a>
        </div>
    </div>
</div>

</body>
</html>

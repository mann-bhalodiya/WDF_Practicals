<?php
$menus = [
    "admin" => [
        "dashboard.php"  => "Dashboard",
        "users.php"      => "Manage Users",
        "audit_logs.php" => "Audit Logs"
    ],
    "student" => [
        "student_home.php" => "My Home"
    ]
];

$current_page = basename($_SERVER["PHP_SELF"]);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?php echo $page_title; ?> - StudentHub</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">

<nav class="navbar navbar-expand navbar-dark bg-dark">
    <div class="container">
        <span class="navbar-brand">StudentHub</span>

        <ul class="navbar-nav me-auto">
            <?php foreach ($menus[$_SESSION["role"]] as $page => $label) { ?>
                <li class="nav-item">
                    <a class="nav-link <?php if ($current_page == $page) { echo "active"; } ?>" href="<?php echo $page; ?>"><?php echo $label; ?></a>
                </li>
            <?php } ?>
        </ul>

        <span class="navbar-text me-3">
            <?php echo htmlspecialchars($_SESSION["name"]); ?> (<?php echo $_SESSION["role"]; ?>)
        </span>
        <a href="logout.php" class="btn btn-outline-light btn-sm">Logout</a>
    </div>
</nav>

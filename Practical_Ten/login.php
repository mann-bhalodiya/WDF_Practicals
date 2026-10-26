<?php
    session_start();
    if(isset($_SESSION['user_id'])) {
        header("Location: dashboard.php");
        exit;
    }
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="style.css">
    <title>Login</title>
</head>
<body>
    <div class="container">
        <h2>Secure Login</h2>

        <?php 
            if(isset($_GET["error"])) {
                echo "<p class='error'>".htmlspecialchars($_GET["error"])."</p>";
            }
            if(isset($_GET["message"])) {
                echo "<p class='success'>".htmlspecialchars($_GET["message"])."</p>";
            }
        ?>

        <form action="authentication.php" method="POST">
            <label for="email">Email: </label>
            <input type="email" name="email">

            <label for="password">Password: </label>
            <input type="password" name="password">

            <button type="submit">Login</button>
        </form>
    </div>
</body>
</html>
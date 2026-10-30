<?php
include_once "auth.php";

if (isset($_SESSION["user_id"])) {
    log_action($conn, $_SESSION["user_id"], "LOGOUT", "Logged out");
}

session_unset();
session_destroy();

header("Location: login.php");
exit;
?>

<?php
session_start();
include_once "db.php";

$timeout = 1800;

if (isset($_SESSION["user_id"])) {
    if (time() - $_SESSION["last_activity"] > $timeout) {
        session_unset();
        session_destroy();
        header("Location: login.php?timeout=1");
        exit;
    }
    $_SESSION["last_activity"] = time();
}

function log_action($conn, $user_id, $action, $details) {
    $ip = $_SERVER["REMOTE_ADDR"];
    $stmt = $conn->prepare("INSERT INTO audit_logs (user_id, action, details, ip_address) VALUES (?, ?, ?, ?)");
    $stmt->bind_param("isss", $user_id, $action, $details, $ip);
    $stmt->execute();
}

function require_login() {
    if (!isset($_SESSION["user_id"])) {
        header("Location: login.php");
        exit;
    }
}

function require_admin($conn) {
    require_login();

    if ($_SESSION["role"] != "admin") {
        log_action($conn, $_SESSION["user_id"], "ACCESS_DENIED", "Tried to open " . basename($_SERVER["PHP_SELF"]));
        include "403.php";
        exit;
    }
}
?>

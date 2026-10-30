<?php
$conn = new mysqli("localhost", "root", "", "studenthub_admin");

if ($conn->connect_error) {
    die("Database connection failed");
}
?>

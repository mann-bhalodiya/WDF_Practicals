<?php
$conn = new mysqli("localhost", "root", "", "studenthub");

if ($conn->connect_error) {
    http_response_code(500);
    die('{"success": false, "message": "Database connection failed", "errors": []}');
}
?>
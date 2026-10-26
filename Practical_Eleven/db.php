<?php
mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);
try {
    $conn = new mysqli("localhost", "root", "", "practical11_db");
    $conn->set_charset("utf8mb4");
} catch (mysqli_sql_exception $e) {
    error_log("Database connection error: ".$e->getMessage());
    http_response_code(500);
    exit("Database connection failed. Check XAMPP and db.php credentials.");
}

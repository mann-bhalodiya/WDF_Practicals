<?php
header("Content-Type: application/json");
ini_set("display_errors", 0);

include "db.php";

usleep(400000);

function send($status, $data) {
    http_response_code($status);
    echo json_encode($data);
    exit;
}

function getStudent($conn, $id) {
    $stmt = $conn->prepare("SELECT id, name, email, mobile, course, year FROM students WHERE id = ?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $result = $stmt->get_result();
    return $result->fetch_assoc();
}

function emailExists($conn, $email, $id) {
    $stmt = $conn->prepare("SELECT id FROM students WHERE email = ? AND id <> ?");
    $stmt->bind_param("si", $email, $id);
    $stmt->execute();
    $stmt->store_result();
    return $stmt->num_rows > 0;
}

function getInput() {
    $input = json_decode(file_get_contents("php://input"), true);
    if (!is_array($input)) {
        send(400, ["success" => false, "message" => "Invalid JSON data", "errors" => []]);
    }
    return [
        "name"   => trim($input["name"] ?? ""),
        "email"  => strtolower(trim($input["email"] ?? "")),
        "mobile" => trim($input["mobile"] ?? ""),
        "course" => strtoupper(trim($input["course"] ?? "")),
        "year"   => (int)($input["year"] ?? 0)
    ];
}

function validate($d) {
    $errors = [];

    if (!preg_match("/^[A-Za-z][A-Za-z .'-]{1,59}$/", $d["name"])) {
        $errors["name"] = "Name must be 2-60 letters.";
    }
    if (!filter_var($d["email"], FILTER_VALIDATE_EMAIL)) {
        $errors["email"] = "Enter a valid email address.";
    }
    if (!preg_match("/^[6-9][0-9]{9}$/", $d["mobile"])) {
        $errors["mobile"] = "Mobile must be 10 digits starting with 6-9.";
    }
    if (!in_array($d["course"], ["CSE", "IT", "CE", "EC", "ME"])) {
        $errors["course"] = "Select a valid course.";
    }
    if ($d["year"] < 1 || $d["year"] > 4) {
        $errors["year"] = "Year must be between 1 and 4.";
    }
    return $errors;
}

$method = $_SERVER["REQUEST_METHOD"];
$id = isset($_GET["id"]) ? (int)$_GET["id"] : 0;

if ($method == "GET") {

    if ($id > 0) {
        $student = getStudent($conn, $id);
        if (!$student) {
            send(404, ["success" => false, "message" => "Student not found", "errors" => []]);
        }
        send(200, ["success" => true, "data" => $student]);
    }

    $search = "%" . ($_GET["search"] ?? "") . "%";
    $course = $_GET["course"] ?? "";

    if ($course == "") {
        $stmt = $conn->prepare("SELECT id, name, email, mobile, course, year FROM students
                                WHERE (name LIKE ? OR email LIKE ? OR mobile LIKE ?)
                                ORDER BY id DESC");
        $stmt->bind_param("sss", $search, $search, $search);
    } else {
        $stmt = $conn->prepare("SELECT id, name, email, mobile, course, year FROM students
                                WHERE (name LIKE ? OR email LIKE ? OR mobile LIKE ?) AND course = ?
                                ORDER BY id DESC");
        $stmt->bind_param("ssss", $search, $search, $search, $course);
    }

    $stmt->execute();
    $rows = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);

    send(200, ["success" => true, "count" => count($rows), "data" => $rows]);
}

elseif ($method == "POST") {

    $d = getInput();
    $errors = validate($d);

    if (count($errors) > 0) {
        send(422, ["success" => false, "message" => "Validation failed", "errors" => $errors]);
    }
    if (emailExists($conn, $d["email"], 0)) {
        send(409, ["success" => false, "message" => "This email is already registered",
                   "errors" => ["email" => "This email is already registered"]]);
    }

    $stmt = $conn->prepare("INSERT INTO students (name, email, mobile, course, year) VALUES (?, ?, ?, ?, ?)");
    $stmt->bind_param("ssssi", $d["name"], $d["email"], $d["mobile"], $d["course"], $d["year"]);
    $stmt->execute();

    send(201, ["success" => true, "message" => "Student added successfully",
               "data" => getStudent($conn, $conn->insert_id)]);
}

elseif ($method == "PUT") {

    if ($id <= 0) {
        send(400, ["success" => false, "message" => "Valid student id is required", "errors" => []]);
    }
    if (!getStudent($conn, $id)) {
        send(404, ["success" => false, "message" => "Student not found", "errors" => []]);
    }

    $d = getInput();
    $errors = validate($d);

    if (count($errors) > 0) {
        send(422, ["success" => false, "message" => "Validation failed", "errors" => $errors]);
    }
    if (emailExists($conn, $d["email"], $id)) {
        send(409, ["success" => false, "message" => "This email is already registered",
                   "errors" => ["email" => "This email is already registered"]]);
    }

    $stmt = $conn->prepare("UPDATE students SET name = ?, email = ?, mobile = ?, course = ?, year = ? WHERE id = ?");
    $stmt->bind_param("ssssii", $d["name"], $d["email"], $d["mobile"], $d["course"], $d["year"], $id);
    $stmt->execute();

    send(200, ["success" => true, "message" => "Student updated successfully",
               "data" => getStudent($conn, $id)]);
}

elseif ($method == "DELETE") {

    if ($id <= 0) {
        send(400, ["success" => false, "message" => "Valid student id is required", "errors" => []]);
    }
    if (!getStudent($conn, $id)) {
        send(404, ["success" => false, "message" => "Student not found", "errors" => []]);
    }

    $stmt = $conn->prepare("DELETE FROM students WHERE id = ?");
    $stmt->bind_param("i", $id);
    $stmt->execute();

    send(200, ["success" => true, "message" => "Student deleted successfully"]);
}

else {
    send(405, ["success" => false, "message" => "Method not allowed", "errors" => []]);
}
?>

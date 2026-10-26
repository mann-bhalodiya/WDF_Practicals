<?php
include "db.php";

if($_SERVER['REQUEST_METHOD']=="POST") {
    $email = trim($_POST['email']);
    $password = $_POST['password'];
    $role = trim($_POST['role']);

    if(empty($email) || empty($password)) {
        die('Email and password are required.');
    }

    if(!filter_var($email,FILTER_VALIDATE_EMAIL)) {
        die('Please enter a valid email address.');
    }

    if(strlen($password) < 6) {
        die('Password must be greater than 6 letters.');
    }

    $check_sql = "SELECT * from users where email = ?";
    $stmt = $conn->prepare($check_sql);
    $stmt->bind_param("s",$email);
    $stmt->execute();
    $stmt->store_result();

    if($stmt->num_rows > 0) {
        die("Email address is already in use. :)");
    }

    $stmt->close();

    $hashed_password = password_hash($password,PASSWORD_DEFAULT);
    $sql = "INSERT into users (email,password,role) VALUES (?,?,?)";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("sss",$email,$hashed_password,$role);

    if ($stmt->execute()) {
        echo "<h2>Registration Successful!</h2>";
        echo "Your account has been created successfully.</p>";
        echo "<a href='index.php'>Register another user</a>";
    } else {
        echo "Error: " . $stmt->error;
    }

    $stmt->close();
    $conn->close();
}

?>
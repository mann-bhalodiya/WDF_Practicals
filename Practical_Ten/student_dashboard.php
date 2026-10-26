<?php 
    session_start(); 

    if (!isset($_SESSION["user_id"])) { 
        header("Location: login.php?error=Please login first."); 
        exit; 
    } 

    if ($_SESSION["role"] !== "student") { 
        http_response_code(403); 
        die("Unauthorized Access: Students only."); 
    } 
?> 

<!DOCTYPE html> 
    <html> 
    <head> 
        <title>Student Dashboard</title> 
    </head> 
    <body> 
        <h2>Student Dashboard</h2> 
        <p>Welcome, Student!</p> 
        <p>You have access to student features.</p> 

        <a href="dashboard.php">Back to Dashboard</a> 
        <br><br> 
        <a href="logout.php">Logout</a> 
    </body> 
</html> 
<?php
include_once "auth.php";

if (isset($_SESSION["user_id"])) {
    if ($_SESSION["role"] == "admin") {
        header("Location: dashboard.php");
    } else {
        header("Location: student_home.php");
    }
    exit;
}

$error = "";
$email = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $email = trim($_POST["email"]);
    $password = $_POST["password"];

    if ($email == "" || $password == "") {
        $error = "Email and password are required.";
    } else {
        $stmt = $conn->prepare("SELECT id, name, password, role, status FROM users WHERE email = ?");
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $user = $stmt->get_result()->fetch_assoc();

        if ($user && password_verify($password, $user["password"])) {

            if ($user["status"] == "inactive") {
                $error = "Your account is inactive. Please contact the admin.";
                log_action($conn, $user["id"], "LOGIN_BLOCKED", "Inactive account tried to login");
            } else {
                session_regenerate_id(true);
                $_SESSION["user_id"] = $user["id"];
                $_SESSION["name"] = $user["name"];
                $_SESSION["role"] = $user["role"];
                $_SESSION["last_activity"] = time();

                log_action($conn, $user["id"], "LOGIN", "Logged in as " . $user["role"]);

                if ($user["role"] == "admin") {
                    header("Location: dashboard.php");
                } else {
                    header("Location: student_home.php");
                }
                exit;
            }

        } else {
            $error = "Invalid email or password.";
            $user_id = $user ? $user["id"] : null;
            log_action($conn, $user_id, "LOGIN_FAILED", "Failed login for " . $email);
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Login - StudentHub</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">
<div class="container">
    <div class="row justify-content-center mt-5">
        <div class="col-md-5">
            <div class="card shadow-sm">
                <div class="card-body p-4">
                    <h4 class="mb-3 text-center">StudentHub Login</h4>

                    <?php if (isset($_GET["timeout"])) { ?>
                        <div class="alert alert-warning">Session expired. Please login again.</div>
                    <?php } ?>

                    <?php if ($error != "") { ?>
                        <div class="alert alert-danger"><?php echo $error; ?></div>
                    <?php } ?>

                    <form method="post" action="login.php">
                        <div class="mb-3">
                            <label for="email" class="form-label">Email</label>
                            <input type="email" class="form-control" id="email" name="email" value="<?php echo htmlspecialchars($email); ?>">
                        </div>
                        <div class="mb-3">
                            <label for="password" class="form-label">Password</label>
                            <input type="password" class="form-control" id="password" name="password">
                        </div>
                        <button type="submit" class="btn btn-primary w-100">Login</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
</body>
</html>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Register</title>

    <link rel="stylesheet" href="style.css">
</head>

<body>

    <div class="container">

        <h2>Create Account</h2>

        <form action="register.php" method="POST">

            <label for="email">Email</label>

            <input 
                type="email"
                id="email"
                name="email"
                placeholder="Enter your email"
                required
            >

            <label for="password">Password</label>

            <input 
                type="password"
                id="password"
                name="password"
                placeholder="Enter your password"
                required
            >

            <label for="role">Role</label>

            <input 
                type="text"
                id="role"
                name="role"
                placeholder="Enter your role"
                required
            >

            <button type="submit">
                Register
            </button>

        </form>

    </div>

</body>
</html>
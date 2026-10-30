<?php
include_once "auth.php";
require_admin($conn);

$message = "";
$message_type = "success";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $id = (int)$_POST["user_id"];

    if ($id == $_SESSION["user_id"]) {
        $message = "You cannot change your own status.";
        $message_type = "danger";
    } else {
        $stmt = $conn->prepare("SELECT name, status FROM users WHERE id = ?");
        $stmt->bind_param("i", $id);
        $stmt->execute();
        $user = $stmt->get_result()->fetch_assoc();

        if ($user) {
            if ($user["status"] == "active") {
                $new_status = "inactive";
            } else {
                $new_status = "active";
            }

            $stmt = $conn->prepare("UPDATE users SET status = ? WHERE id = ?");
            $stmt->bind_param("si", $new_status, $id);
            $stmt->execute();

            log_action($conn, $_SESSION["user_id"], "USER_STATUS_CHANGED", $user["name"] . " (ID " . $id . ") set to " . $new_status);
            $message = $user["name"] . " is now " . $new_status . ".";
        } else {
            $message = "User not found.";
            $message_type = "danger";
        }
    }
}

$users = $conn->query("SELECT id, name, email, role, status, created_at FROM users ORDER BY id");

$page_title = "Manage Users";
include "header.php";
?>

<div class="container mt-4">
    <h3 class="mb-3">Manage Users</h3>

    <?php if ($message != "") { ?>
        <div class="alert alert-<?php echo $message_type; ?>"><?php echo htmlspecialchars($message); ?></div>
    <?php } ?>

    <div class="card">
        <div class="table-responsive">
            <table class="table table-striped mb-0 align-middle">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Name</th>
                        <th>Email</th>
                        <th>Role</th>
                        <th>Status</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while ($row = $users->fetch_assoc()) { ?>
                        <tr>
                            <td><?php echo $row["id"]; ?></td>
                            <td><?php echo htmlspecialchars($row["name"]); ?></td>
                            <td><?php echo htmlspecialchars($row["email"]); ?></td>
                            <td><?php echo $row["role"]; ?></td>
                            <td>
                                <?php if ($row["status"] == "active") { ?>
                                    <span class="badge text-bg-success">Active</span>
                                <?php } else { ?>
                                    <span class="badge text-bg-secondary">Inactive</span>
                                <?php } ?>
                            </td>
                            <td>
                                <?php if ($row["id"] != $_SESSION["user_id"]) { ?>
                                    <form method="post" action="users.php">
                                        <input type="hidden" name="user_id" value="<?php echo $row["id"]; ?>">
                                        <?php if ($row["status"] == "active") { ?>
                                            <button type="submit" class="btn btn-sm btn-outline-danger">Deactivate</button>
                                        <?php } else { ?>
                                            <button type="submit" class="btn btn-sm btn-outline-success">Activate</button>
                                        <?php } ?>
                                    </form>
                                <?php } else { ?>
                                    <span class="text-muted">You</span>
                                <?php } ?>
                            </td>
                        </tr>
                    <?php } ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

</body>
</html>

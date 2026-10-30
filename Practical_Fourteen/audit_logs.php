<?php
include_once "auth.php";
require_admin($conn);

$logs = $conn->query("SELECT a.id, u.name, a.action, a.details, a.ip_address, a.created_at
                      FROM audit_logs a
                      LEFT JOIN users u ON a.user_id = u.id
                      ORDER BY a.id DESC
                      LIMIT 50");

$page_title = "Audit Logs";
include "header.php";
?>

<div class="container mt-4">
    <h3 class="mb-3">Audit Logs</h3>

    <div class="card">
        <div class="table-responsive">
            <table class="table table-striped mb-0">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>User</th>
                        <th>Action</th>
                        <th>Details</th>
                        <th>IP Address</th>
                        <th>Date &amp; Time</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    $count = 0;
                    while ($row = $logs->fetch_assoc()) {
                        $count++;
                    ?>
                        <tr>
                            <td><?php echo $row["id"]; ?></td>
                            <td><?php if ($row["name"]) { echo htmlspecialchars($row["name"]); } else { echo "Unknown"; } ?></td>
                            <td><span class="badge text-bg-dark"><?php echo $row["action"]; ?></span></td>
                            <td><?php echo htmlspecialchars($row["details"]); ?></td>
                            <td><?php echo $row["ip_address"]; ?></td>
                            <td><?php echo date("d M Y, h:i:s A", strtotime($row["created_at"])); ?></td>
                        </tr>
                    <?php } ?>
                    <?php if ($count == 0) { ?>
                        <tr><td colspan="6" class="text-center">No audit logs yet.</td></tr>
                    <?php } ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

</body>
</html>
